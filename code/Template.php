<?php
/**
 * This file is part of Sendsmaily Wordpress plugin.
 *
 * Sendsmaily Wordpress plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Sendsmaily Wordpress plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Sendsmaily Wordpress plugin.  If not, see <http://www.gnu.org/licenses/>.
 */

class Wp_Sendsmaily_Template
{
	/**
	 * @var template name
	 */
	protected $_template = '';

	/**
	 * @var template variables
	 */
	protected $_vars = '';

	/**
	 * constructor
	 * @param string $file [optional]
	 * @return string
	 */
	public function __construct( $file = null ) {
		$this->_template = $file;
	}

	/**
	 * render partial html
	 * @param string $template
	 * @param array $params
	 * @return string
	 */
	public function partial( $template, $params = array() ) {
		$template = new self( $template );
		$template->assign( $params );
		return $template->render();
	}

	/**
	 * render template
	 * @return string|bool
	 * @throws Exception
	 */
	public function render() {
		$file_name = BP . DS . $this->_template;

		// check for template file
		if ( empty( $this->_template ) or ! file_exists( $file_name ) or ! is_readable( $file_name ) ) {
			throw new Exception( 'Could not find template "' . $file_name . '"! Please check for file existance.' );
			return false;
		}

		// output template
		ob_start();
		include( $file_name );
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	/**
	 * dispatch template
	 * @return void
	 */
	public function dispatch() {
		echo $this->render();
	}

	/**
	 * assign template variables
	 * @param string|array $name
	 * @param object $value [optional]
	 * @return Sendsmaily_Subscribe_Abstract
	 */
	public function assign( $name, $value = null ) {
		if ( is_array( $name ) and ! empty( $name ) and empty( $value ) ) {
			foreach ( $name as $key => $value ) {
				$this->_vars[ $key ] = $value;
			}
		} elseif ( is_string( $name ) and ! empty( $name ) and ! empty( $value ) ) {
			$this->_vars[ $name ] = $value;
		}

		return $this;
	}

	/**
	 * get all assigned variables
	 * @return array
	 */
	public function getVars() {
		return $this->_vars;
	}

	/**
	 * return assigned var value
	 * @param string $name
	 * @return string|object|void
	 */
	public function __get( $name ) {
		if ( ! is_string( $name ) or empty( $name ) or ! isset( $this->_vars[ $name ] ) ) {
			return '';
		}

		return $this->_vars[ $name ];
	}

	/**
	 * assign single param
	 * @param string $name
	 * @param object $value
	 * @return void
	 */
	public function __set( $name, $value ) {
		$this->_vars[ $name ] = $value;
	}
}
