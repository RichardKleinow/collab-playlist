<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://r.com
 * @since      1.0.0
 *
 * @package    Collab_Playlist
 * @subpackage Collab_Playlist/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Collab_Playlist
 * @subpackage Collab_Playlist/includes
 * @author     R <R@r.com>
 */
class Collab_Playlist {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Collab_Playlist_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	protected $option_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'COLLAB_PLAYLIST_VERSION' ) ) {
			$this->version = COLLAB_PLAYLIST_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'collab-playlist';
		$this->option_name = 'collab_options';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Collab_Playlist_Loader. Orchestrates the hooks of the plugin.
	 * - Collab_Playlist_i18n. Defines internationalization functionality.
	 * - Collab_Playlist_Admin. Defines all hooks for the admin area.
	 * - Collab_Playlist_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-collab-playlist-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-collab-playlist-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-collab-playlist-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-collab-playlist-public.php';

		$this->loader = new Collab_Playlist_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Collab_Playlist_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Collab_Playlist_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Collab_Playlist_Admin( $this->get_plugin_name(), $this->get_version() );
		
        // Create Amdin Menu
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'register_menu' );
		// Add settings API
        $this->loader->add_action( 'admin_init', $plugin_admin, 'add_settings' );
        // add check for received auth code on admin page load
        $this->loader->add_action( 'admin_init', $plugin_admin, 'check_auth_code' );
		
		// add CSS
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		// add JS
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        // add ajax
        // reset settings
        $this->loader->add_action('wp_ajax_clear_settings', $plugin_admin, 'clear_settings');

	}


	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Collab_Playlist_Public( $this->get_plugin_name(), $this->get_version() );
        // Add CSS and JS
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        // add shortcode
		$this->loader->add_action( 'init', $plugin_public, 'register_shortcodes' );
        // add ajax
        // handle get Playlist
        $this->loader->add_action('wp_ajax_collab_spotify_load_playlist', $plugin_public, 'collab_spotify_load_playlist');
        $this->loader->add_action('wp_ajax_nopriv_collab_spotify_load_playlist', $plugin_public, 'collab_spotify_load_playlist');
        // handle track search
        $this->loader->add_action('wp_ajax_collab_spotify_track_search', $plugin_public, 'collab_spotify_track_search');
        $this->loader->add_action('wp_ajax_nopriv_collab_spotify_track_search', $plugin_public, 'collab_spotify_track_search');
        // handle further track search with provided link
        $this->loader->add_action('wp_ajax_collab_spotify_next_tracks', $plugin_public, 'collab_spotify_next_tracks');
        $this->loader->add_action('wp_ajax_nopriv_collab_spotify_next_tracks', $plugin_public, 'collab_spotify_next_tracks');
        // handle adding track to playlist
        $this->loader->add_action('wp_ajax_collab_spotify_add_to_playlist', $plugin_public, 'collab_spotify_add_to_playlist');
        $this->loader->add_action('wp_ajax_nopriv_collab_spotify_add_to_playlist', $plugin_public, 'collab_spotify_add_to_playlist');
    }

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Collab_Playlist_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}
	


	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
