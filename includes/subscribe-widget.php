<?php
/**
 * Widget that can be used to subscribe to newsletters
 *
 * @package    Sendsmaily
 * @subpackage Sendsmaily/includes
 */

/**
 * Create a class for the widget.
 */
class Sendsmaily_Newsletter_Subscription_Widget extends WP_Widget {

	/**
	 * Sets up a new instance of the widget.
	 */
	public function __construct() {
		$widget_ops = array( 'description' => __( 'Sendsmaily newsletter subscription form', 'wp_sendsmaily' ) );
		parent::__construct( 'sendsmaily_subscription_widget', __( 'Sendsmaily Newsletter Subscription', 'wp_sendsmaily' ), $widget_ops );
	}

	/**
	 * Outputs the content for the current widget instance.
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance Settings for the current Search widget instance.
	 */
	function widget( $args, $instance ) {
		global $wpdb;

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		$show_name = isset( $instance['show_name'] ) ? $instance['show_name'] : false;

		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		// Load configuration data.
		$table_name = esc_sql( $wpdb->prefix . 'sendsmaily_config' );
		$config = (array) $wpdb->get_row( "SELECT * FROM `$table_name` LIMIT 1" );
		$config['show_name'] = $show_name;
		// Create admin template.
		require_once( BP . DS . 'code' . DS . 'Template.php' );
		$file = '1' === $config->is_advanced ? 'advanced.phtml' : 'basic.phtml';
		$template = new Wp_Sendsmaily_Template( 'html' . DS . 'form' . DS . $file );
		$template->assign( $config );
		// Render template.
		echo $template->render();

		echo $args['after_widget'];
	}
	/**
	 * This function should check that $new_instance is set correctly. The newly
	 * calculated value of $instance should be returned. If "false" is returned,
	 * the instance won't be saved/updated.
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = esc_textarea( $new_instance['title'] );
		$instance['show_name'] = isset( $new_instance['show_name'] ) ? (bool) $new_instance['show_name'] : false;
		return $instance;
	}
	/**
	 * Widget form on widgets page in admin panel.
	 * @param array $instance Widget fields array.
	 * @return void
	 */
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array(
			'title' => '',
			'show_name' => isset( $instance['show_name'] ) ? (bool) $instance['show_name'] : false,
		) );

		// Widget title.
		$title_id = esc_attr( $this->get_field_id( 'title' ) );
		$title_name = esc_attr( $this->get_field_name( 'title' ) );
		$instance['title'] = esc_attr( $instance['title'] );
		echo '<p>
			<label for="' . $title_id . '">Title:</label>
			<input class="widefat" id="' . $title_id . '" name="' . $title_name . '" type="text" value="' . $instance['title'] . '" />
		</p>';

		// Display checkbox for name field.
		$show_name_id = esc_attr( $this->get_field_id( 'show_name' ) );
		$show_name_name = esc_attr( $this->get_field_name( 'show_name' ) );
		$instance['show_name'] = esc_attr( $instance['show_name'] );
		echo '<p>
			<input class="checkbox" id="' . $show_name_id . '" name="' . $show_name_name . '" type="checkbox"' . ($instance['show_name'] ? 'checked' : '') . ' />
			<label for="' . $show_name_id . '">Display name field?</label>' .
		'</p>';

	}
}
