<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://alvarooropesa.com
 * @since      1.0.0
 *
 * @package    Ultimate_Post_Generator
 * @subpackage Ultimate_Post_Generator/includes
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
 * @package    Ultimate_Post_Generator
 * @subpackage Ultimate_Post_Generator/includes
 * @author     Alvaro Oropesa <alvarovisiondesing@gmail.com>
 */
class Ultimate_Post_Generator {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Ultimate_Post_Generator_Loader    $loader    Maintains and registers all hooks for the plugin.
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
		if ( defined( 'ULTIMATE_POST_GENERATOR_VERSION' ) ) {
			$this->version = ULTIMATE_POST_GENERATOR_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'ultimate-post-generator';

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
	 * - Ultimate_Post_Generator_Loader. Orchestrates the hooks of the plugin.
	 * - Ultimate_Post_Generator_i18n. Defines internationalization functionality.
	 * - Ultimate_Post_Generator_Admin. Defines all hooks for the admin area.
	 * - Ultimate_Post_Generator_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ultimate-post-generator-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ultimate-post-generator-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ultimate-post-generator-admin.php';

		/**
		 * The class responsible for connecting to OpenAI API.
		 */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ultimate-post-generator-api-call.php';


		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ultimate-post-generator-public.php';

		$this->loader = new Ultimate_Post_Generator_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Ultimate_Post_Generator_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Ultimate_Post_Generator_i18n();

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

		$plugin_admin = new Ultimate_Post_Generator_Admin( $this->get_plugin_name(), $this->get_version() );
		// $api_call_instance = new Ultimate_Post_Generator_API_Call();
		// $open_call = $api_call_instance->chat_gpt_interface_page();
		/** Actions */
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'addAdminMenu');
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		
		// AJAX actions
		$this->loader->add_action('wp_ajax_get_openai_response', $plugin_admin, 'open_ai_ajax_call');
		// Hook for AJAX (both logged in and non-logged in users)
		$this->loader->add_action('wp_ajax_upg_save_draft_post', $plugin_admin, 'handle_save_draft_ajax');
		$this->loader->add_action('wp_ajax_nopriv_upg_save_draft_post', $plugin_admin, 'handle_save_draft_ajax');
		// AJAX Save propmt
		$this->loader->add_action('wp_ajax_upg_save_custom_prompt', $plugin_admin, 'saveCustomPrompt');
		// AJAX Delete prompt
		$this->loader->add_action('wp_ajax_upg_delete_custom_prompt', $plugin_admin, 'deleteCustomPrompt');
		// AJAX Update/Edit prompt
		$this->loader->add_action('wp_ajax_upg_update_custom_prompt', $plugin_admin, 'updateCustomPrompt');

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Ultimate_Post_Generator_Public( $this->get_plugin_name(), $this->get_version() );

		/** Actions */
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'init', $plugin_public, 'add_the_shortcode' );

		/** Filters */
		$this->loader->add_filter( 'the_content', $plugin_public, 'var_dump_the_content');

		
		
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
	 * @return    Ultimate_Post_Generator_Loader    Orchestrates the hooks of the plugin.
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
