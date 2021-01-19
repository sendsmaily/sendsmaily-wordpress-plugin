<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @since      3.0.0
 * @package    Smaily_For_WP
 * @subpackage Smaily_For_WP/public
 */
class Smaily_For_WP_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since  3.0.0
	 * @access private
	 * @var    string  $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since  3.0.0
	 * @access private
	 * @var    string  $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 3.0.0
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register all shortcodes present in the function.
	 *
	 * @since  3.0.0
	 * @return void
	 */
	public function add_shortcodes() {
		add_shortcode( 'smaily_for_wp_newsletter_form', array( $this, 'smaily_shortcode_render' ) );
	}

	/**
	 * Render Smaily form using shortcode.
	 *
	 * @param  array $atts Shortcode attributes.
	 * @return string
	 */
	private function smaily_shortcode_render( $atts ) {
		global $wpdb;

		// Load configuration data.
		$table_name = esc_sql( $wpdb->prefix . 'smaily_config' );
		$config = (array) $wpdb->get_row( "SELECT * FROM `$table_name` LIMIT 1" );

		// Parse attributes out of shortcode tag.
		$shortcode_atts = shortcode_atts(
			array(
				'success_url'      => get_site_url(),
				'failure_url'      => get_site_url(),
				'show_name'        => false,
				'autoresponder_id' => '',
			),
			$atts
		);
		$config['success_url']      = $shortcode_atts['success_url'];
		$config['failure_url']      = $shortcode_atts['failure_url'];
		$config['show_name']        = $shortcode_atts['show_name'];
		$config['autoresponder_id'] = $shortcode_atts['autoresponder_id'];

		// Create admin template.
		$file     = ( isset( $config['is_advanced'] ) && '1' === $config['is_advanced'] ) ? 'advanced.php' : 'basic.php';
		$template = new Smaily_For_WP_Template( 'public/partials/smaily-for-wp-public-' . $file );
		$template->assign( $config );
		// Display responses on Smaily subscription form.
		$form_has_response  = false;
		$form_is_successful = false;
		$response_message   = null;

		if ( ! isset( $config['api_credentials'] ) || empty( $config['api_credentials'] ) ) {
			$form_has_response = true;
			$response_message  = __( 'Smaily credentials not validated. Subscription form will not work!', 'smaily-for-wp' );
		} elseif ( isset( $_GET['code'] ) && (int) $_GET['code'] === 101 ) {
			$form_is_successful = true;
		} elseif ( isset( $_GET['code'] ) || ! empty( $_GET['code'] ) ) {
			$form_has_response = true;
			switch ( (int) $_GET['code'] ) {
				case 201:
					$response_message = __( 'Form was not submitted using POST method.', 'smaily-for-wp' );
					break;
				case 204:
					$response_message = __( 'Input does not contain a recognizable email address.', 'smaily-for-wp' );
					break;
				default:
					$response_message = __( 'Could not add to subscriber list for an unknown reason. Probably something in Smaily.', 'smaily-for-wp' );
					break;
			}
		}

		$template->assign(
			array(
				'form_has_response'  => $form_has_response,
				'response_message'   => $response_message,
				'form_is_successful' => $form_is_successful,
			)
		);
		// Render template.
		return $template->render();
	}
}
