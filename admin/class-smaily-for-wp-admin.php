<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      3.0.0
 * @package    Smaily_For_WP
 * @subpackage Smaily_For_WP/admin
 */
class Smaily_For_WP_Admin {

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
	 * Handler for storing/retrieving data via Options API.
	 *
	 * @since  3.0.0
	 * @access private
	 * @var    Smaily_For_WP_Options Handler for WordPress Options API.
	 */
	private $options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 3.0.0
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name    = $plugin_name;
		$this->version        = $version;
		$this->options = new Smaily_For_WP_Options();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 3.0.0
	 */
	public function enqueue_styles() {
		wp_register_style( $this->plugin_name, SMLY4WP_PLUGIN_URL . '/admin/css/smaily-for-wp-admin.css', array(), $this->version, 'all' );
		// Only enqueue in module page.
		$screen = get_current_screen();
		if ( isset( $screen->base ) && $screen->base === 'toplevel_page_sendsmaily-wordpress-plugin' ) {
			wp_enqueue_style( $this->plugin_name );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 3.0.0
	 */
	public function enqueue_scripts() {
		wp_register_script( $this->plugin_name, SMLY4WP_PLUGIN_URL . '/admin/js/smaily-for-wp-admin.js', array( 'jquery' ), $this->version, false );
		// Only enqueue in module page.
		$screen = get_current_screen();
		if ( isset( $screen->base ) && $screen->base === 'toplevel_page_sendsmaily-wordpress-plugin' ) {
			wp_enqueue_script( $this->plugin_name );
			wp_localize_script( $this->plugin_name, $this->plugin_name, array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		}
	}

	/**
	 * Render admin page.
	 *
	 * @since 3.0.0
	 */
	public function smaily_admin_render() {
		// Load configuration data.
		$has_credentials = $this->options->has_credentials();
		$signup_form     = $this->options->get_form_options()['form'];

		// Create admin template.
		$template = $this->generate_admin_template( 'page.php', $has_credentials, $signup_form );

		// Add menu elements.
		add_menu_page( 'smaily', 'Smaily', 'manage_options', SMLY4WP_PLUGIN_PATH, '', SMLY4WP_PLUGIN_URL . '/gfx/icon.png' );
		add_submenu_page( 'smaily', 'Newsletter subscription form', 'Form', 'manage_options', SMLY4WP_PLUGIN_PATH, array( $template, 'dispatch' ) );
	}

	/**
	 * Load subscribe widget.
	 *
	 * @since 3.0.0
	 */
	public function smaily_subscription_widget_init() {
		$widget = new Smaily_For_WP_Widget( $this );
		register_widget( $widget );
	}

	/**
	 * Function is run when user performs action which is handled Ajax.
	 *
	 * @since 3.0.0
	 */
	public function smaily_admin_save() {
		// Allow only posted data.
		if ( empty( $_POST ) ) {
			wp_die( 'Must be post method.' );
		}

		// Parse form data out of the serialization.
		$form_data = array();
		parse_str( $_POST['form_data'], $form_data );

		// Validate posted operation.
		if ( ! isset( $form_data['op'] ) ) {
			wp_die( 'No action or API key set.' );
		}

		$valid_operations = array( 'validateApiKey', 'removeApiKey', 'resetForm', 'save' );
		$form_data['op']  = in_array( $form_data['op'], $valid_operations, true ) ? $form_data['op'] : '';

		if ( $form_data['op'] === '' ) {
			wp_die( 'No valid operation submitted.' );
		}

		$refresh = ( isset( $form_data['refresh'] ) && (int) $form_data['refresh'] === 1 );
		$result  = array(
			'message' => '',
			'error'   => true,
			'content' => '',

		);
		// Switch to action.
		switch ( $form_data['op'] ) {
			case 'validateApiKey':
				$result = array_merge( $result, $this->validate_api_key( $form_data ) );
				break;
			case 'removeApiKey':
				$result = array_merge( $result, $this->remove_api_key() );
				break;
			case 'resetForm':
				$result = array_merge( $result, $this->reset_form() );
				break;
			case 'save':
				$result = array_merge( $result, $this->save( $form_data ) );
				break;
		}

		if ( $refresh && $result['error'] === false ) {
			$has_credentials   = $this->options->has_credentials();
			$signup_form       = $this->options->get_form_options()['form'];
			$result['content'] = $this->generate_admin_template( 'form.php', $has_credentials, $signup_form )->render();
		}

		echo wp_json_encode( $result );
		wp_die();
	}

	/**
	 * Function is run when user submits Smaily API credentials.
	 *
	 * @since  3.0.0
	 * @access private
	 * @param  array $form_data Posted form data (unserialized).
	 * @return array Response of operation.
	 */
	private function validate_api_key( $form_data ) {
		// Get and sanitize request params.
		$params = array(
			'subdomain' => isset( $form_data['subdomain'] ) ? sanitize_text_field( $form_data['subdomain'] ) : '',
			'username'  => isset( $form_data['username'] ) ? sanitize_text_field( $form_data['username'] ) : '',
			'password'  => isset( $form_data['password'] ) ? sanitize_text_field( $form_data['password'] ) : '',
		);

		$params['subdomain'] = $this->normalize_subdomain( $params['subdomain'] );

		// Show error messages to user if no data is entered to form.
		if ( $params['subdomain'] === '' ) {
			return array(
				'message' => __( 'Please enter subdomain!', 'smaily-for-wp' ),
			);
		} elseif ( $params['username'] === '' ) {
			return array(
				'message' => __( 'Please enter username!', 'smaily-for-wp' ),
			);
		} elseif ( $params['password'] === '' ) {
			return array(
				'message' => __( 'Please enter password!', 'smaily-for-wp' ),
			);
		}

		// Validate credentials with get request.
		$rqst = ( new Smaily_For_WP_Request() )
			->auth( $params['username'], $params['password'] )
			->setUrl( 'https://' . $params['subdomain'] . '.sendsmaily.net/api/workflows.php?trigger_type=form_submitted' )
			->get();

		// Error handilng.
		$code = isset( $rqst['code'] ) ? $rqst['code'] : '';
		if ( $code !== 200 ) {
			if ( $code === 401 ) {
				// If wrong credentials.
				return array(
					'message' => __( 'Wrong credentials', 'smaily-for-wp' ),
				);
			} elseif ( $code === 404 ) {
				// If wrong subdomain.
				return array(
					'message' => __( 'Error in subdomain', 'smaily-for-wp' ),
				);
			} elseif ( array_key_exists( 'error', $rqst ) ) {
				// If there is WordPress error message.
				return array(
					'message' => $rqst['error'],
				);
			}
			// If not determined error.
			return array(
				'message' => __( 'Something went wrong with request to Smaily', 'smaily-for-wp' ),
			);
		}
		// Insert item to database.
		$this->options->update_api_credentials( $params );

		// Return response.
		return array(
			'error'   => false,
			'message' => __( 'Credentials validated.', 'smaily-for-wp' ),
		);
	}

	/**
	 * Function is run when user removes saved API key.
	 *
	 * @since  3.0.0
	 * @access private
	 * @return array Response of operation.
	 */
	private function remove_api_key() {
		// Delete contents of config.
		$this->options->update_api_credentials( array() );

		// Return response.
		return array(
			'error'   => false,
			'message' => __( 'Credentials removed.', 'smaily-for-wp' ),
		);
	}

	/**
	 * Function is run when user regenerates signup form.
	 *
	 * @since  3.0.0
	 * @access private
	 * @return array Response of operation.
	 */
	private function reset_form() {
		$subdomain = $this->options->get_api_credentials()['subdomain'];
		$template  = $this->generate_signup_template( 'advanced.php', $subdomain );

		// Return response.
		return array(
			'message' => __( 'Newsletter subscription form reset to default.', 'smaily-for-wp' ),
			'error'   => false,
			'content' => $template->render(),
		);
	}

	/**
	 * Function is run when user presses save button.
	 *
	 * @since  3.0.0
	 * @access private
	 * @param  array $form_data Posted form data (deserialized).
	 * @return array Response of operation.
	 */
	private function save( $form_data ) {
		// Get parameters.
		$is_advanced = ( isset( $form_data['is_advanced'] ) && ! empty( $form_data['is_advanced'] ) ) ? '1' : '0';
		$form        = ( isset( $form_data['form'] ) && is_string( $form_data['form'] ) ) ? $form_data['form'] : '';

		// Generate new form (if empty).
		if ( empty( $form ) ) {
			// Load configuration data.
			$subdomain = $this->options->get_api_credentials()['subdomain'];
			$form      = $this->options->get_form_options()['form'];

			$template = $this->generate_signup_template( 'advanced.php', $subdomain, $form );
			// Render template.
			$form = trim( $template->render() );
		}

		$this->options->update_form_options(
			array(
				'is_advanced' => $is_advanced,
				'form'        => $form,
			)
		);

		// Return response.
		return array(
			'error'   => false,
			'message' => __( 'Changes saved.', 'smaily-for-wp' ),
		);
	}


	/**
	 * Generate admin area template and assign required variables via function parameters.
	 *
	 * @since  3.0.0
	 * @access private
	 * @param  string $template_name            Name of template file to use, without any prefixes (e.g form.php).
	 * @param  bool   $has_credentials          User has saved valid credentials? Yes/No.
	 * @param  string $newsletter_form          HTML of newsletter subscription form.
	 * @return Smaily_For_WP_Template $template Template of admin form.
	 */
	private function generate_admin_template( $template_name, $has_credentials, $newsletter_form ) {
		// Generate form contents.
		$template = new Smaily_For_WP_Template( 'admin/partials/smaily-for-wp-admin-' . $template_name );

		$template->assign(
			array(
				'has_credentials' => $has_credentials,
				'form'            => $newsletter_form,
			)
		);

		return $template;
	}

	/**
	 * Generate newsletter signup template and assign required variables via function parameters.
	 *
	 * @since  3.0.0
	 * @access private
	 * @param  string $template_name            Name of template file to use, without any prefixes (e.g advanced.php).
	 * @param  string $subdomain                Smaily API subdomain.
	 * @param  string $newsletter_form          HTML of newsletter subscription form.
	 * @return Smaily_For_WP_Template $template Template of admin form.
	 */
	private function generate_signup_template( $template_name, $subdomain, $newsletter_form = '' ) {
		// Generate form contents.
		$template = new Smaily_For_WP_Template( 'public/partials/smaily-for-wp-public-' . $template_name );

		$template->assign(
			array(
				'domain' => $subdomain,
				'form'   => $newsletter_form,
			)
		);
		return $template;
	}

	/**
	 * Normalize subdomain into the bare necessity.
	 *
	 * @since  3.0.0
	 * @access private
	 * @param  string $subdomain Messy subdomain, e.g http://demo.sendsmaily.net
	 * @return string Clean subdomain, e.g demo
	 */
	private function normalize_subdomain( $subdomain ) {
		// Normalize subdomain.
		// First, try to parse as full URL. If that fails, try to parse as subdomain.sendsmaily.net, and
		// if all else fails, then clean up subdomain and pass as is.
		if ( filter_var( $subdomain, FILTER_VALIDATE_URL ) ) {
			$url       = wp_parse_url( $subdomain );
			$parts     = explode( '.', $url['host'] );
			$subdomain = count( $parts ) >= 3 ? $parts[0] : '';
		} elseif ( preg_match( '/^[^\.]+\.sendsmaily\.net$/', $subdomain ) ) {
			$parts     = explode( '.', $subdomain );
			$subdomain = $parts[0];
		}

		return preg_replace( '/[^a-zA-Z0-9]+/', '', $subdomain );
	}

	/**
	 * Make a request to Smaily asking for autoresponders.
	 * Request is authenticated via saved credentials.
	 *
	 * @return array $autoresponder_list List of autoresponders in format [id => title].
	 */
	public function get_autoresponders() {
		// Load configuration data.
		$api_credentials = $this->options->get_api_credentials();

		if ( ! $this->options->has_credentials( $api_credentials ) ) {
			return array();
		}

		$result = ( new Smaily_For_WP_Request() )
			->setUrl( 'https://' . $api_credentials['subdomain'] . '.sendsmaily.net/api/workflows.php?trigger_type=form_submitted' )
			->auth( $api_credentials['username'], $api_credentials['password'] )
			->get();

		if ( empty( $result['body'] ) ) {
			return array();
		}

		$autoresponder_list = array();
		foreach ( $result['body'] as $autoresponder ) {
			$id                        = $autoresponder['id'];
			$title                     = $autoresponder['title'];
			$autoresponder_list[ $id ] = $title;
		}
		return $autoresponder_list;
	}
}
