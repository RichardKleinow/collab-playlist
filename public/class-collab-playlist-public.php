<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://r.com
 * @since      1.0.0
 *
 * @package    Collab_Playlist
 * @subpackage Collab_Playlist/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Collab_Playlist
 * @subpackage Collab_Playlist/public
 * @author     R <R@r.com>
 */
class Collab_Playlist_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}


    /**
     * Register the shortcodes for the plugin
     *  
     * 
     * @since    1.0.0
     */
    public function register_shortcodes() {
		add_shortcode( 'collab-spotify', array($this,'shortcode_collab_spotify'));

	}

    /**
     * Log to Console
     *
     * @since    1.0.0
     */
    public function console_log($data) {
        $output = $data;
        if (is_array($output)) {
            $output = implode(',', $output);
        }
        echo "<script>console.log('Console Log: " . $output . "' );</script>";
    }



    /**
     * Shortcode for the Spotify search
     *
     * @since    1.0.0
     */
	public function shortcode_collab_spotify() {
		ob_start(); // Start output buffering to capture the HTML output
		require (plugin_dir_path( __FILE__ ) . 'html/shortcode_layout.php');
		return ob_get_clean(); // Return the buffered content

	}
    
    /**
     * Get a fresh access token from Spotify
     *
     * @since    1.0.0
     */
    public function collab_spotify_get_fresh_access_token() {
        $options = get_option('collab_options');
        $accessToken = $options['collab_spotify_access_token'];
        $refreshToken = $options['collab_spotify_refresh_token'];
        $client_id = $options['collab_spotify_id'];    
        $client_secret = $options['collab_spotify_secret'];
        $endpoint = 'https://accounts.spotify.com/api/token';
        $credentials = base64_encode("$client_id:$client_secret");
        
        $args = array(
            'method' => 'POST',
            'httpversion' => '1.1',
            'headers' => array(
                'Authorization' => "q $credentials",
                'Content-Type' => 'application/x-www-form-urlencoded',
            ),
            'body' => array(
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
            )
        );
    
        $response = wp_remote_post($endpoint, $args);
        if (is_wp_error($response)) {
            // Handle error; refresh token might be invalid or expired
            $error = new WP_Error( '002', 'Error response from API.','');
            wp_send_json_error($error,500);
            return false;
        } 
        $body = json_decode(wp_remote_retrieve_body($response), true);
        //$error = new WP_Error('000', $body);
        //$error = new WP_Error( '009', 'aT_old: ' . $accessToken . ", aT_new: " . $body['access_token'] . ", rT_old: " . $refreshToken . ", rt_new: " . $body['refresh_token'],'');
        //wp_send_json_error($error,500);

        if (isset($body['access_token'])) {
            $options['collab_spotify_access_token'] = $body['access_token'];
            // Only update refresh token if new one is provided
            if (isset($body['refresh_token'])) {
                $options['collab_spotify_refresh_token'] = $body['refresh_token'];
            }
            update_option('collab_options', $options, true);
            //$error = new WP_Error( '009', $body['scope'] .  ", " . $body['access_token'] . " , " . $options['collab_spotify_pl_id'],'');
            //wp_send_json_error($error,500);
            return true;
        } else {
            $error = new WP_Error('003', 'Spotify API Error: ' . $body['error'] . ': ' . $body['error_description']);
            wp_send_json_error($error, 500);
            return false;
        }
    }


    public function collab_spotify_load_playlist() {
         // Check for nonce for security
         check_ajax_referer('collab_spotify_nonce', 'nonce');

         // Use the access token to make a search request to Spotify
         if ($this->collab_spotify_get_fresh_access_token()){
             // Make the search request to Spotify
             $response = $this->spotify_playlist_load_playlist();
             // Return the search results to the front-end
             wp_send_json_success($response);
         } else {
             $error = new WP_Error( '001', 'Failed to get fresh access token','');
             wp_send_json_error($error,500);
         }
         wp_die();
    }

    /**
     * Search Spotify for tracks wrapper function
     *
     * @since    1.0.0
     */
    public function collab_spotify_track_search() {
        // Static variable to hold cumulative data
        static $mergedData = ['items' => []]; 

        // Check for nonce for security
        check_ajax_referer('collab_spotify_nonce', 'nonce');

        // Use the access token to make a search request to Spotify
        if ($this->collab_spotify_get_fresh_access_token()){
            // Get the search query from the front-end and sanitize it
            $search_query = sanitize_text_field($_POST['query']);
            // Make the search request to Spotify
            $response = $this->spotify_search_call_tracks($search_query);
            // Return the search results to the front-end
            wp_send_json_success($response);
            
        } else {
            $error = new WP_Error( '001', 'Failed to get fresh access token','');
            wp_send_json_error($error,500);
        }
        wp_die();
    }

    /**
     * Search Spotify for next tracks after limit is reached wrapper function
     *
     * @since    1.0.0
     */
    public function collab_spotify_next_tracks() {
        // Check for nonce for security
        check_ajax_referer('collab_spotify_nonce', 'nonce');
        // Use the access token to make a search request to Spotify
        if ($this->collab_spotify_get_fresh_access_token()){
            // Get the search query from the front-end and sanitize it
            $search_query = sanitize_text_field($_POST['query']);
            // Make the search request to Spotify
            $response = $this->spotify_search_next_tracks($search_query);
            // Return the search results to the front-end
            wp_send_json_success($response);
        } else {
            $error = new WP_Error( '001', 'Failed to get fresh access token','');
            wp_send_json_error($error,500);
        }
        wp_die();
    }


    /**
     * Add track to Spotify playlist wrapper function
     *
     * @since    1.0.0
     */
    public function collab_spotify_add_to_playlist() {
        // Check for nonce for security
        check_ajax_referer('collab_spotify_nonce', 'nonce');
        // Use the access token to make a search request to Spotify
        if ($this->collab_spotify_get_fresh_access_token()){
            // Get the trackId
            $trackId = $_POST['query'];
            // Make the add request to Spotify
            $response = $this->spotify_playlist_add_track($trackId);
            // Return results to the front-end
            if (isset($response['error'])) {
                //$options = get_option('collab_options');
                //$error = new WP_Error( '004', $trackId);
                //$error = new WP_Error( '004', $response);
                $error = new WP_Error( '004', $response['error']['message']);
                wp_send_json_error($error,500);
            } else {
                wp_send_json_success($response);
            }
            
        } else {
            $error = new WP_Error( '001', 'Failed to get fresh access token','');
            wp_send_json_error($error,500);
        }
        wp_die();
    }
   
    /**
     * Load Spotify playlist
     *
     * @since    1.0.0
     */
    public function spotify_playlist_load_playlist() {
        // Get token
        $options = get_option('collab_options');
        $access_token = $options['collab_spotify_access_token'];
        $playlist_id = $options['collab_spotify_pl_id'];

        // Create url and headers
        $url_tracks = "https://api.spotify.com/v1/playlists/" . $playlist_id . "/tracks?offset=0";
        
        // Get tracks
        $tracks = $this->spotify_playlist_fetch_merge($url_tracks,$access_token);
        $playlist['tracks'] = $tracks;

        // Get Playlist Meta Informations
        $url = "https://api.spotify.com/v1/playlists/" . $playlist_id;
        $args = [
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
                'Accept' => 'application/json'
            ]
        ];
        $response = wp_remote_get($url, $args);
        $body = json_decode(wp_remote_retrieve_body($response), true);
        if(isset($body['images'])){
            $playlist['img'] = $body['images'];
        } else {
            $playlist['img'] = '';
        }

        // Get Playlist Name
        if(isset($body['name'])){
            $playlist['name'] = $body['name'];
        } else {
            $playlist['name'] = '';
        }

        // Get Playlist Link
        if(isset($body['external_urls']['spotify'])){
            $playlist['link'] = $body['external_urls']['spotify'];
        } else {
            $playlist['link'] = '';
        }

        // Return Array with Tracks and Image
        return $playlist;
    }


    /**
     * Fetch and merge Spotify playlist data
     *
     * @since    1.0.0
     */
    public function spotify_playlist_fetch_merge($url,$access_token) {

        // Static variable to hold cumulative data
        static $mergedData = []; 
        if (is_null($url)) {
            return $mergedData;
        }
    
        $args = [
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
                'Accept' => 'application/json'
            ]
        ];

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            return $mergedData;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        // merge items
        if (!empty($body['items'])) {
            $mergedData = array_merge($body['items'],$this->spotify_playlist_fetch_merge($body['next'], $access_token));
        }

 
        return $mergedData;
        
    }



    /**
     * Search Spotify for tracks
     *
     * @since    1.0.0
     */
    public function spotify_search_call_tracks($query) {
        // Get token
        $options = get_option('collab_options');
        $access_token = $options['collab_spotify_access_token'];

        // Create url and headers
        $url = "https://api.spotify.com/v1/search?type=track&q=" . urlencode($query);
        $args = array(
            'headers' => array('Authorization' => 'Bearer ' . $access_token)
        );

        // Get request
        $response = wp_remote_get($url, $args);
        // Return response or empty array
        $body = [];
        if (!is_wp_error($response)) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
        
        }
        return $body;
    }

    /**
     * Search Spotify for next tracks after limit is reached
     *
     * @since    1.0.0
     */
    public function spotify_search_next_tracks($url) {
        // Get token
        $options = get_option('collab_options');
        $access_token = $options['collab_spotify_access_token'];

        // Check if URL is empty
        if (empty($url)) {
            return [];
        }

        // Create headers
        $args = array(
            'headers' => array('Authorization' => 'Bearer ' . $access_token)
        );

        // Get request
        $response = wp_remote_get($url, $args);
        // Return response or empty array
        $body = [];
        if (!is_wp_error($response)) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
        
        }
        return $body;
    }


    /**
     * Add track to Spotify playlist
     *
     * @since    1.0.0
     */
    public function spotify_playlist_add_track($trackId) {
        // Get token
        $options = get_option('collab_options');
        $access_token = $options['collab_spotify_access_token'];
        $playlist_id = $options['collab_spotify_pl_id'];

        // Create url and headers
        $url = "https://api.spotify.com/v1/playlists/" . $playlist_id . "/tracks";
        $args = array(
            'method' => 'POST',
            'httpversion' => '1.1',
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json'
            ),
            'body' => array(
                'uris' => array(
                    'spotify:track:' . $trackId
                ),
                'position' => 0
            ),
        );

        // Get request
        $response = wp_remote_request($url, $args);
        // Return response or empty array
        $body = [];
        if (!is_wp_error($response)) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
        }
        return $body;
    }

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Collab_Playlist_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Collab_Playlist_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style('bootstrap','https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', array(), '5.3.3', 'all');
        wp_enqueue_style('local-theme', get_stylesheet_uri(), array('bootstrap'), '1.0.0', 'all');
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/collab-playlist-public.css', array(), $this->version, 'all' );
        
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Collab_Playlist_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Collab_Playlist_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// Register JS
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/collab-playlist-public.js', array( 'jquery' ), $this->version, false );
        // Localize nonce to create safe AJAX request to backend
        wp_localize_script($this->plugin_name, 'CollabSpotifyAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('collab_spotify_nonce'),
        ));


	}

}
