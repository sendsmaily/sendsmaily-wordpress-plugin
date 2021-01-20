<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      3.0.0
 * @package    Smaily_For_WP
 * @subpackage Smaily_For_WP/includes
 */
class Smaily_For_WP {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since  3.0.0
	 * @access protected
	 * @var    Smaily_For_WP_Loader  $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since  3.0.0
	 * @access protected
	 * @var    string    $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since  3.0.0
	 * @access protected
	 * @var    string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		$this->version = SMLY4WP_PLUGIN_VERSION;
		$this->plugin_name = 'smaily_for_wp';
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Smaily_For_WP_Admin.    Defines all hooks for the admin area.
	 * - Smaily_For_WP_i18n.     Defines internationalization functionality.
	 * - Smaily_For_WP_Loader.   Orchestrates the hooks of the plugin.
	 * - Smaily_For_WP_Request.  Defines the request making functionality.
	 * - Smaily_For_WP_Template. Defines the templating making functionality.
	 * - Smaily_For_WP_Widget.   Defines the widget functionality.
	 * - Smaily_For_WP_Public.   Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since  3.0.0
	 * @access private
	 */
	private function load_dependencies() {
		require_once SMLY4WP_PLUGIN_PATH . '/admin/class-smaily-for-wp-admin.php';
		require_once SMLY4WP_PLUGIN_PATH . '/includes/class-smaily-for-wp-i18n.php';
		require_once SMLY4WP_PLUGIN_PATH . '/includes/class-smaily-for-wp-loader.php';
		require_once SMLY4WP_PLUGIN_PATH . '/includes/class-smaily-for-wp-request.php';
		require_once SMLY4WP_PLUGIN_PATH . '/includes/class-smaily-for-wp-template.php';
		require_once SMLY4WP_PLUGIN_PATH . '/includes/class-smaily-for-wp-widget.php';
		require_once SMLY4WP_PLUGIN_PATH . '/public/class-smaily-for-wp-public.php';
		$this->loader = new Smaily_For_WP_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Smaily_For_WP_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since  3.0.0
	 * @access private
	 */
	private function set_locale() {
		$plugin_i18n = new Smaily_For_WP_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since  3.0.0
	 * @access private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Smaily_For_WP_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_ajax_smaily_admin_save', $plugin_admin, 'smaily_admin_save' );
		$this->loader->add_action( 'widgets_init', $plugin_admin, 'smaily_subscription_widget_init' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'smaily_admin_render' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since  3.0.0
	 * @access private
	 */
	private function define_public_hooks() {
		$plugin_public = new Smaily_For_WP_Public( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'init', $plugin_public, 'add_shortcodes' );
	}

	/**
	 * Install database structure (on activation).
	 *
	 * @since 3.0.0
	 */
	public static function activate() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset_collate = $wpdb->get_charset_collate();

		// Create database table - settings.
		$table_name = esc_sql( $wpdb->prefix . 'smaily_config' );
		$sql        = "CREATE TABLE $table_name (
			api_credentials VARCHAR(128) NOT NULL,
			domain VARCHAR(255) NOT NULL,
			autoresponder INT(16) NOT NULL,
			form TEXT NOT NULL,
			is_advanced TINYINT(1) NOT NULL,
			PRIMARY KEY  (api_credentials)
		) $charset_collate;";
		dbDelta( $sql );

		// Create database table - autoresponders.
		$table_name = esc_sql( $wpdb->prefix . 'smaily_autoresponders' );
		$sql        = "CREATE TABLE $table_name (
					id int(16) NOT NULL,
					title varchar(255) NOT NULL,
					PRIMARY KEY  (id)
				) $charset_collate;";
		dbDelta( $sql );
	}

	/**
	 * Clean up plugin's database entities.
	 *
	 * @since 3.0.0
	 */
	public static function uninstall() {
		global $wpdb;
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}smaily_autoresponders" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}smaily_config" );
		delete_option( 'widget_smaily_subscription_widget' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 3.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since  3.0.0
	 * @return string The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since  3.0.0
	 * @return Smaily_For_WP_Loader Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since  3.0.0
	 * @return string The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
