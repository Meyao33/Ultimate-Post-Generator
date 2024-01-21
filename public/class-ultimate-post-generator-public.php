<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://alvarooropesa.com
 * @since      1.0.0
 *
 * @package    Ultimate_Post_Generator
 * @subpackage Ultimate_Post_Generator/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Ultimate_Post_Generator
 * @subpackage Ultimate_Post_Generator/public
 * @author     Alvaro Oropesa <alvarovisiondesing@gmail.com>
 */
class Ultimate_Post_Generator_Public {

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
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ultimate_Post_Generator_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ultimate_Post_Generator_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ultimate-post-generator-public.css', array(), $this->version, 'all' );

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
		 * defined in Ultimate_Post_Generator_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ultimate_Post_Generator_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ultimate-post-generator-public.js', array( 'jquery' ), $this->version, false );

	}

	public function var_dump_the_content($content) {
		if (is_single()) {
			return $content . esc_html__('Mr lova lova');
		}
		return $content;
	}

	/**
	 * Generate the public face output
	 */
	public function add_the_shortcode() {
		// Register shortcode
		add_shortcode('ultimate_post_generator_display', array($this, 'display_public_view'));
	}
	public function display_public_view() {
		// Check if this is the admin area, if so, return early
		if (is_admin()) {
			return;
		}
	
		// Check if the user is logged in and has the 'manage_options' capability
		if (is_user_logged_in() && current_user_can('manage_options')) {
			ob_start();
			include_once plugin_dir_path( __FILE__ ) . 'partials/ultimate-post-generator-public-display.php';
			return ob_get_clean();
		} else {
			echo '<p>You do not have sufficient permissions to access this page.</p>';
			// Optionally, you can include a login link here
			echo '<a href="' . wp_login_url(get_permalink()) . '">Login</a>';
		}
	}
	
	

}
