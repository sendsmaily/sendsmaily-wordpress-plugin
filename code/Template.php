<?php

class Wp_Sendsmaily_Template {
	/**
	 * Template name.
	 *
	 * @var string
	 */
	protected $_template = '';

	/**
	 * Template variables.
	 *
	 * @var array
	 */
	protected $_vars = array();

	/**
	 * Constructor.
	 *
	 * @param string $file Input file.
	 */
	public function __construct( $file = null ) {
		$this->_template = $file;
	}

	/**
	 * Render partial html.
	 *
	 * @param string $template Template.
	 * @param array  $params Template params.
	 * @return string
	 */
	public function partial( $template, $params = array() ) {
		$template = new self( $template );
		$template->assign( $params );
		return $template->render();
	}

	/**
	 * Render template.
	 *
	 * @return string|bool
	 * @throws Exception Exeption.
	 */
	public function render() {
		$file_name = BP . DS . $this->_template;
		// Check for template file.
		if ( empty( $this->_template ) || ! file_exists( $file_name ) || ! is_readable( $file_name ) ) {
			throw new Exception( 'Could not find template "' . $file_name . '"! Please check for file existence.' );
			return false;
		}

		// Output template.
		ob_start();
		include( $file_name );
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	/**
	 * Dispatch template.
	 *
	 * @return void
	 */
	public function dispatch() {
		echo $this->render();
	}

	/**
	 * Assign template variables.
	 *
	 * @param string|array $name Name.
	 * @param object       $value [optional].
	 * @return Sendsmaily_Subscribe_Abstract
	 */
	public function assign( $name, $value = null ) {
		if ( is_array( $name ) && ! empty( $name ) && empty( $value ) ) {
			foreach ( $name as $key => $value ) {
				$this->_vars[ $key ] = $value;
			}
		} elseif ( is_string( $name ) && ! empty( $name ) && ! empty( $value ) ) {
			$this->_vars[ $name ] = $value;
		}

		return $this;
	}

	/**
	 * Get all assigned variables.
	 *
	 * @return array
	 */
	public function getVars() {
		return $this->_vars;
	}

	/**
	 * Return assigned var value.
	 *
	 * @param string $name Name.
	 * @return string|object|void
	 */
	public function __get( $name ) {
		if ( ! is_string( $name ) || empty( $name ) || ! isset( $this->_vars[ $name ] ) ) {
			return '';
		}

		return $this->_vars[ $name ];
	}

	/**
	 * Assign single param.
	 *
	 * @param string $name Name.
	 * @param object $value Value.
	 * @return void
	 */
	public function __set( $name, $value ) {
		$this->_vars[ $name ] = $value;
	}
}
