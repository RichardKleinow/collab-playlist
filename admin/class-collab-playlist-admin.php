<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://r.com
 * @since      1.0.0
 *
 * @package    Collab_Playlist
 * @subpackage Collab_Playlist/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Collab_Playlist
 * @subpackage Collab_Playlist/admin
 * @author     R <R@r.com>
 */
class Collab_Playlist_Admin {

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
	
	
	public function adminHello() {
		include 'partials/collab-playlist-admin-display.php';
	}

	public function add_settings() {
		// Register a new setting for admin page.
		register_setting( $this->plugin_name, 'collab_options' );
		add_settings_section('collab_options_general',
							'Plugin Settings',
							array( $this,'collab_options_general_cb' ),
							$this->plugin_name
			);

		add_settings_field( 'collab_spotify_id',
							'Spotify Client ID',
							array( $this, 'collab_options_spotify_id_cb' ),
							$this->plugin_name,
							'collab_options_general'
			);

        add_settings_field( 'collab_spotify_secret',
            'Spotify Client Secret',
            array( $this, 'collab_options_spotify_secret_cb' ),
            $this->plugin_name,
            'collab_options_general'
        );

        add_settings_field( 'collab_spotify_pl_name',
                            'Playlist Name',
                            array( $this, 'collab_options_spotify_pl_cb' ),
                            $this->plugin_name,
                            'collab_options_general'
            );


        add_settings_field( 'collab_spotify_pl_id',
            null,
            array( $this, 'collab_options_spotify_hide' ),
            $this->plugin_name,
            'collab_options_general'
            );
        
        add_settings_field( 'collab_spotify_access_token',
            null,
            array( $this, 'collab_options_spotify_hide' ),
            $this->plugin_name,
            'collab_options_general'
            );

        
        add_settings_field( 'collab_spotify_refresh_token',
            null,
            array( $this, 'collab_options_spotify_hide' ),
            $this->plugin_name,
            'collab_options_general'
            );

		}

    
    // HTML for Client ID
    public function collab_options_spotify_id_cb() {
        $options = get_option('collab_options');
        $val = isset($options['collab_spotify_id']) ? $options['collab_spotify_id'] : '';
        echo '<input type="text" name="collab_options[collab_spotify_id]" id="collab_spotify_id" value="' . esc_attr($val) . '">';
    }

    // HTML for Client Secret
    public function collab_options_spotify_secret_cb() {
        $options = get_option('collab_options');
        $val = isset($options['collab_spotify_secret']) ? $options['collab_spotify_secret'] : '';
        echo '<input type="text" name="collab_options[collab_spotify_secret]" id="collab_spotify_secret" value="' . esc_attr($val) . '">';
    }


    // HTML for Playlist Name
    public function collab_options_spotify_pl_cb() {
        $options = get_option('collab_options'); 
        $val = isset($options['collab_spotify_pl_name']) ? $options['collab_spotify_pl_name'] : '';
        echo '<input type="text" name="collab_options[collab_spotify_pl_name]" id="collab_spotify_pl_name" value="' . esc_attr($val) . '">';
    }

    // html for hidden fields
    public function collab_options_spotify_hide() {
        return;
    }

	// html for general settings text
	public function collab_options_general_cb() {
		echo '<p>Authenticate your Spotify playlist by entering the details below:</p>';
	}


	/*
    public function add_cors_http_header() {
		header("Access-Control-Allow-Origin: *");
		return;
	}
    */


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

    public function clear_settings() {
        check_ajax_referer('clear_settings_nonce_action', 'nonce');
    
        // Clear the settings
        update_option('collab_options', array());
    
        wp_send_json_success();
    }


    public function check_auth_code() {
        if (isset($_GET['code'])) {
            $options = get_option('collab_options');

            $authCode = $_GET['code'];

            // exchange access code for token
            if($this->exchange_code_for_token($authCode)) {
                $this->admin_banner_success('Spotify connected!');
                // identify playlist
                if($this->identify_playlist()) {
                    $this->admin_banner_success('Spotify playlist successfully identified!');
                    //$appendix = "/" . sprintf('wp-admin/admin.php?page=%s', $_GET['page']);
                    //echo "<script>window.history.pushState({}, document.title,'" . $appendix . "' );</script>"; // Reload Page and Clear Code from URL

                } else {
                    $this->admin_banner_failure('Playlist not found');
                }
            } else {
                $this->admin_banner_failure('Spotify connection failed');
            }
         }
    }


    public function admin_banner_failure($str) {
        echo sprintf('
        <div class="notice notice-error is-dismissible"> 
            <p><strong>%s</strong></p>
            <button type="button" class="notice-dismiss">
            </button>
        </div>', $str);
    }


    public function admin_banner_success($str) {
        echo sprintf('
        <div class="notice notice-success is-dismissible"> 
            <p><strong>%s</strong></p>
            <button type="button" class="notice-dismiss">
            </button>
        </div>', $str);
    }


    public function lower_rm_whitespace($str) {
        return strtolower(str_replace(" ","", $str));
    }

    public function identify_playlist() {
        $options = get_option('collab_options');
        $playlistId = $options['collab_spotify_pl_id'];
        $accessToken = $options['collab_spotify_access_token'];
        $playlistName = $this->lower_rm_whitespace($options['collab_spotify_pl_name']);
        $endpoint = 'https://api.spotify.com/v1/me/playlists';
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $accessToken
            )
        );
        
        // send get request
        $response = wp_remote_get($endpoint, $args);
        if (is_wp_error($response)) {
            $this->console_log($response->get_error_message());
            return false;
        }

        // decode the response
        $body = json_decode(wp_remote_retrieve_body($response),true);
        foreach ($body['items'] as $playlist) {
            $name = $this->lower_rm_whitespace($playlist['name']);
            if ($name == $playlistName) {
                $playlistId = $playlist['id'];
                break;
            }
        }

        if ($playlistId) {
            $options['collab_spotify_pl_id'] = $playlistId;
            update_option('collab_options', $options);
            $this->console_log('Playlist identified!');
            return true;
        } else {
            $this->console_log('Playlist not found');
            return false;
        }
    }

    public function exchange_code_for_token($authCode) {
        $options = get_option('collab_options');
        $client_id = $options['collab_spotify_id'];
        $client_secret = $options['collab_spotify_secret'];
        $redirect_uri = admin_url(sprintf('admin.php?page=%s', $_GET['page']));

        $endpoint = 'https://accounts.spotify.com/api/token';
        $credentials = base64_encode($client_id . ':' . $client_secret);

        $args = array(
            'method' => 'POST',
            'httpversion' => '1.1',
            'headers' => array(
                'Authorization' => 'Basic ' . $credentials,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ),
            'body' => array(
                'grant_type' => 'authorization_code',
                'code' => $authCode,
                'redirect_uri' => $redirect_uri
            ),
            'json' => true
        );

        $response = wp_remote_post($endpoint, $args);
        if (is_wp_error($response)) {
            $this->console_log($response->get_error_message());
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response),true);
        if (isset($body['access_token'])) {
            $options['collab_spotify_access_token'] = $body['access_token'];
            //update_option('collab_options', $options);
            $options['collab_spotify_refresh_token'] = $body['refresh_token'];
            update_option('collab_options', $options, true);
            $this->console_log('Token exchange successful!');
            $this->console_log("Scopes are: " . $body['scope']);
            $this->console_log($body['access_token']);
            $this->console_log($body);
            return true;
        } else {
            $this->console_log('Token exchange failed');
            $this->console_log($body['error']);
            return false;
        }
    
    }
	
	
	public function register_menu() {
		add_menu_page(
		   'Collab Playlist Title',
		   'Collab Playlist Settings',
		   'manage_options',
		   'collab_playlist',
		   [ &$this, 'adminHello' ],
		   'dashicons-playlist-audio'
		);
	}


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/collab-playlist-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/collab-playlist-admin.js', array( 'jquery' ), $this->version, false );
        wp_localize_script($this->plugin_name, 'CollabSpotifyAjaxAdmin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('clear_settings_nonce_action'),
        ));
	}

}
