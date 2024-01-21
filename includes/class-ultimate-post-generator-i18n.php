<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://alvarooropesa.com
 * @since      1.0.0
 *
 * @package    Ultimate_Post_Generator
 * @subpackage Ultimate_Post_Generator/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Ultimate_Post_Generator
 * @subpackage Ultimate_Post_Generator/includes
 * @author     Alvaro Oropesa <alvarovisiondesing@gmail.com>
 */
class Ultimate_Post_Generator_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'ultimate-post-generator',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
