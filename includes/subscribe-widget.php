<?php
/**
 * Widget that can be used to subscribe to newsletters
 *
 * @since      1.0.0
 *
 * @package    Sendsmaily
 * @subpackage Sendsmaily/includes
 */

/**
 * Create a widget class.
 */
class Sendsmaily_Newsletter_Subscription_Widget extends WP_Widget {
	/**
	 * Basic settings of widget.
	 */
	function Sendsmaily_Newsletter_Subscription_Widget() {
		$widget_attributes = array(
			'description' => __( 'Sendsmaily newsletter subscription form', 'wp_sendsmaily' ),
		);
		$this->WP_Widget(
			'sendsmaily_subscription_widget',
			'Sendsmaily Newsletter Subscription',
			$widget_attributes
		);
	}
	/**
	 * Subclasses should over-ride this function to generate their widget code.
	 * @param array $args Display arguments including before_title, after_title, before_widget and after_widget.
	 * @param array $instance The settings for the particular instance of the widget.
	 */
	function widget( $args, $instance ) {
		global $wpdb;

		extract( $args, EXTR_SKIP );
		$text = empty( $instance['text'] ) ? ' ' : apply_filters( 'widget_title', $instance['text'] );
		if ( ! empty( $text ) ) {
			echo $text;
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
	}
	/**
	 * This function should check that $new_instance is set correctly. The newly
	 * calculated value of $instance should be returned. If "false" is returned,
	 * the instance won't be saved/updated.
	 * @param array $new_instance
	 * @param array $old_instance
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance[ 'text' ] = strip_tags( $new_instance['text'] );
		return $instance;
	}
	/**
	 * Widget form on widgets page in admin panel.
	 * @param array $instance
	 */
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'text' => '' ) );
		$text = strip_tags( $instance['text'] );
		echo '
		<p><label for="' . $this->get_field_id( 'text' ) . '">
			Title:
			<input class="widefat" id="' . $this->get_field_id( 'text' ) . '" name="' . $this->get_field_name( 'text' ) . '" type="text" value="' . attribute_escape( $text ) . '" />
		</label></p>';
	}
}
