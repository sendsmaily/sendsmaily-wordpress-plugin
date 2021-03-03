<?php
/**
 * Defines the widget functionality of the plugin.
 *
 * @since      3.0.0
 * @package    Smaily
 * @subpackage Smaily/includes
 */
class Smaily_For_WP_Widget extends WP_Widget {

	/**
	 * Array of autoresponders.
	 *
	 * @since  3.0.0
	 * @access private
	 * @var    array    $autoresponders Used to populate the autoresponder <select> field.
	 */
	private $autoresponders;

	/**
	 * Handler for storing/retrieving data via Options API.
	 *
	 * @since  3.0.0
	 * @access private
	 * @var    Smaily_For_WP_Options $options Handler for Options API.
	 */
	private $options;

	/**
	 * Sets up a new instance of the widget.
	 *
	 * @since 3.0.0
	 * @param Smaily_For_WP_Options $options     Reference to options handler class.
	 * @param Smaily_For_WP_Admin   $admin_model Reference to admin class.
	 */
	public function __construct( Smaily_For_WP_Options $options, Smaily_For_WP_Admin $admin_model ) {
		$widget_ops = array( 'description' => __( 'Smaily newsletter subscription form', 'smaily-for-wp' ) );
		parent::__construct( 'smaily_subscription_widget', __( 'Smaily Newsletter Subscription', 'smaily-for-wp' ), $widget_ops );

		$this->options        = $options;
		$this->autoresponders = $admin_model->get_autoresponders();
	}

	/**
	 * Outputs the content for the current widget instance.
	 *
	 * @since 3.0.0
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance Settings for the current Search widget instance.
	 */
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		$show_name     = isset( $instance['show_name'] ) ? $instance['show_name'] : false;
		$success_url   = isset( $instance['success_url'] ) ? $instance['success_url'] : '';
		$failure_url   = isset( $instance['failure_url'] ) ? $instance['failure_url'] : '';
		$autoresponder = isset( $instance['autoresponder'] ) ? $instance['autoresponder'] : '';

		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		// Load configuration data.
		$api_credentials = $this->options->get_api_credentials();
		$form_options    = $this->options->get_form_options();
		// Data to be assigned to template.
		$config                     = array();
		$config['domain']           = $api_credentials['subdomain'];
		$config['form']             = $form_options['form'];
		$config['is_advanced']      = $form_options['is_advanced'];
		$config['show_name']        = $show_name;
		$config['success_url']      = $success_url;
		$config['failure_url']      = $failure_url;
		$config['autoresponder_id'] = $autoresponder;

		// Create admin template.
		$file     = $config['is_advanced'] === '1' ? 'advanced.php' : 'basic.php';
		$template = new Smaily_For_WP_Template( 'public/partials/smaily-for-wp-public-' . $file );
		$template->assign( $config );
		// Display responses on Smaily subscription form.
		$form_has_response  = false;
		$form_is_successful = false;
		$response_message   = null;

		if ( ! $this->options->has_credentials() ) {
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
		echo $template->render();

		echo $args['after_widget'];
	}

	/**
	 * This function should check that $new_instance is set correctly. The newly
	 * calculated value of $instance should be returned. If "false" is returned,
	 * the instance won't be saved/updated.
	 *
	 * @since  3.0.0
	 * @param  array $new_instance New instance.
	 * @param  array $old_instance Old instance.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                = $old_instance;
		$instance['title']       = esc_textarea( $new_instance['title'] );
		$instance['show_name']   = isset( $new_instance['show_name'] ) ? (bool) $new_instance['show_name'] : false;
		$instance['success_url'] = esc_url( $new_instance['success_url'] );
		$instance['failure_url'] = esc_url( $new_instance['failure_url'] );
		$instance['autoresponder'] = sanitize_text_field( $new_instance['autoresponder'] );

		return $instance;
	}

	/**
	 * Widget form on widgets page in admin panel.
	 *
	 * @since  3.0.0
	 * @param  array $instance Widget fields array.
	 */
	public function form( $instance ) {
		$instance = wp_parse_args(
			(array) $instance,
			array(
				'title'         => '',
				'show_name'     => isset( $instance['show_name'] ) ? (bool) $instance['show_name'] : false,
				'success_url'   => '',
				'failure_url'   => '',
				'autoresponder' => '',
			)
		);

		// Widget title.
		$title_id          = esc_attr( $this->get_field_id( 'title' ) );
		$title_name        = esc_attr( $this->get_field_name( 'title' ) );
		$instance['title'] = esc_attr( $instance['title'] );
		echo '<p>
			<label for="' . $title_id . '">' . __( 'Title', 'smaily-for-wp' ) . ':</label>
			<input class="widefat" id="' . $title_id . '" name="' . $title_name . '" type="text" value="' . $instance['title'] . '" />
		</p>';

		// Display checkbox for name field.
		$show_name_id          = esc_attr( $this->get_field_id( 'show_name' ) );
		$show_name_name        = esc_attr( $this->get_field_name( 'show_name' ) );
		$instance['show_name'] = esc_attr( $instance['show_name'] );
		echo '<p>
			<input class="checkbox" id="' . $show_name_id . '" name="' . $show_name_name . '" type="checkbox"' . ( $instance['show_name'] ? 'checked' : '' ) . ' />
			<label for="' . $show_name_id . '">' . __( 'Display name field?', 'smaily-for-wp' ) . '</label>' .
		'</p>';
		// Display inputs for success/failure URLs.
		$success_url_id          = esc_attr( $this->get_field_id( 'success_url' ) );
		$success_url             = esc_attr( $this->get_field_name( 'success_url' ) );
		$instance['success_url'] = esc_attr( $instance['success_url'] );
		echo '<p>
			<label for="' . $success_url_id . '">' . __( 'Success URL', 'smaily-for-wp' ) . ':</label>
			<input id="' . $success_url_id . '" name="' . $success_url . '" type="text" value="' . $instance['success_url'] . '" />
		</p>';

		$failure_url_id          = esc_attr( $this->get_field_id( 'failure_url' ) );
		$failure_url             = esc_attr( $this->get_field_name( 'failure_url' ) );
		$instance['failure_url'] = esc_attr( $instance['failure_url'] );
		echo '<p>
			<label for="' . $failure_url_id . '">' . __( 'Failure URL', 'smaily-for-wp' ) . ':</label>
			<input id="' . $failure_url_id . '" name="' . $failure_url . '" type="text" value="' . $instance['failure_url'] . '" />
		</p>';
		// Display autoresponder select menu.
		$autoresponder_id          = esc_attr( $this->get_field_id( 'autoresponder' ) );
		$autoresponder             = esc_attr( $this->get_field_name( 'autoresponder' ) );
		$instance['autoresponder'] = esc_attr( $instance['autoresponder'] );
		echo '<p>
			<label for="' . $autoresponder_id . '">' . esc_html__( 'Autoresponders', 'smaily-for-wp' ) . ':</label>
			<select id="' . $autoresponder_id . '" name="' . $autoresponder . '">
			<option value="">' . esc_html__( 'No autoresponder', 'smaily-for-wp' ) . '</option>';
		foreach ( $this->autoresponders as $id => $title ) {
			echo '<option value="' . esc_attr( $id ) . '"' . selected( $instance['autoresponder'], $id, false ) . '>' . esc_attr( $title ) . '</option>';
		}
		echo '</select></p>';
	}
}
