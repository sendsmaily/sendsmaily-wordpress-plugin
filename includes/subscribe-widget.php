<?php
/**
 * Widget that can be used to subscribe to newsletters
 *
 * @package    Sendsmaily
 * @subpackage Sendsmaily/includes
 */

/**
 * Create a widget class.
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

		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		// Load configuration data.
		$table_name = $wpdb->prefix . 'sendsmaily_config';
		$config = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `$table_name` LIMIT 1" ) );
		// Create admin template.
		require_once( BP . DS . 'code' . DS . 'Template.php' );
		$file = '1' === $config->is_advanced ? 'advanced.phtml' : 'basic.phtml';
		$template = new Wp_Sendsmaily_Template( 'html' . DS . 'form' . DS . $file );
		$template->assign( (array) $config );
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
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}
	/**
	 * Widget form on widgets page in admin panel.
	 * @param array $instance Widget fields array.
	 * @return void
	 */
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title_id = esc_attr( $this->get_field_id( 'title' ) );
		$title_name = esc_attr( $this->get_field_name( 'title' ) );
		$instance['title'] = esc_attr( $instance['title'] );
		echo '<p>
			<label for="' . $title_id . '">
				Title:
				<input class="widefat" id="' . $title_id . '" name="' . $title_name . '" type="text" value="' . $instance['title'] . '" />
			</label>' .
		'</p>';
	}
}
