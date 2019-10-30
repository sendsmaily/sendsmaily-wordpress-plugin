<?php
/**
 * Widget that can be used to subscribe to newsletters
 *
 * @package    Smaily
 * @subpackage Smaily/includes
 */

/**
 * Create a class for the widget.
 */
class Smaily_Newsletter_Subscription_Widget extends WP_Widget {

	/**
	 * Sets up a new instance of the widget.
	 */
	public function __construct() {
		$widget_ops = array( 'description' => __( 'Smaily newsletter subscription form', 'wp_smaily' ) );
		parent::__construct( 'smaily_subscription_widget', __( 'Smaily Newsletter Subscription', 'wp_smaily' ), $widget_ops );
	}

	/**
	 * Outputs the content for the current widget instance.
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance Settings for the current Search widget instance.
	 */
	public function widget( $args, $instance ) {
		global $wpdb;

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		$show_name = isset( $instance['show_name'] ) ? $instance['show_name'] : false;

		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		// Load configuration data.
		$table_name          = esc_sql( $wpdb->prefix . 'smaily_config' );
		$config              = (array) $wpdb->get_row( "SELECT * FROM `$table_name` LIMIT 1" );
		$config['show_name'] = $show_name;
		// Create admin template.
		require_once( SMLY4WP_PLUGIN_PATH . '/code/Template.php' );
		$file     = ( isset( $config['is_advanced'] ) &&  '1' === $config['is_advanced'] ) ? 'advanced.php' : 'basic.php';
		$template = new Smaily_Plugin_Template( 'html/form/' . $file );
		$template->assign( $config );
		// Smaily form error logic for no JavaScript.
		$form_has_error = false;
		$error_message = null;

		if ( isset( $_GET['smaily_form_error'] ) && !empty( $_GET['smaily_form_error'] ) ) {
			$form_has_error = true;
			$error_message = sanitize_text_field( $_GET['smaily_form_error'] );
		} elseif ( !isset( $config['api_credentials'] ) || empty( $config['api_credentials'] ) ) {
			$form_has_error = true;
			$error_message = __( 'Smaily credentials not validated. Subscription form will not work!', 'wp_smaily' );
		}
		$template->assign( array(
			'form_has_error' => $form_has_error,
			'error_message' => $error_message,
		) );
		// Render template.
		echo $template->render();

		echo $args['after_widget'];
	}
	/**
	 * This function should check that $new_instance is set correctly. The newly
	 * calculated value of $instance should be returned. If "false" is returned,
	 * the instance won't be saved/updated.
	 *
	 * @param array $new_instance New instance.
	 * @param array $old_instance Old instance.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = esc_textarea( $new_instance['title'] );
		$instance['show_name'] = isset( $new_instance['show_name'] ) ? (bool) $new_instance['show_name'] : false;
		return $instance;
	}
	/**
	 * Widget form on widgets page in admin panel.
	 *
	 * @param array $instance Widget fields array.
	 * @return void
	 */
	public function form( $instance ) {
		$instance = wp_parse_args(
			(array) $instance,
			array(
				'title'     => '',
				'show_name' => isset( $instance['show_name'] ) ? (bool) $instance['show_name'] : false,
			)
		);

		// Widget title.
		$title_id          = esc_attr( $this->get_field_id( 'title' ) );
		$title_name        = esc_attr( $this->get_field_name( 'title' ) );
		$instance['title'] = esc_attr( $instance['title'] );
		echo '<p>
			<label for="' . $title_id . '">' . __( 'Title', 'wp_smaily' ) . ':</label>
			<input class="widefat" id="' . $title_id . '" name="' . $title_name . '" type="text" value="' . $instance['title'] . '" />
		</p>';

		// Display checkbox for name field.
		$show_name_id          = esc_attr( $this->get_field_id( 'show_name' ) );
		$show_name_name        = esc_attr( $this->get_field_name( 'show_name' ) );
		$instance['show_name'] = esc_attr( $instance['show_name'] );
		echo '<p>
			<input class="checkbox" id="' . $show_name_id . '" name="' . $show_name_name . '" type="checkbox"' . ( $instance['show_name'] ? 'checked' : '' ) . ' />
			<label for="' . $show_name_id . '">' . __( 'Display name field?', 'wp_smaily' ) . '</label>' .
		'</p>';

	}
}
