<?php
/**
 * Plugin Name:    Give - Form Field Manager
 * Plugin URI:     http://givewp.com/addons/give-form-fields-manager
 * Description:    Easily add and control Give's form fields using an easy-to-use interface
 * Author:         WordImpress
 * Author URL:     http://givewp.com/
 * Version:        1.1.1
 * Text Domain: give-ffm
 * Domain Path: /languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Form_Fields_Manager
 */
final class Give_Form_Fields_Manager {

	/** Singleton *************************************************************/

	/**
	 * @var Give_Form_Fields_Manager The one true Give_Form_Fields_Manager
	 * @since 1.0
	 */
	private static $instance;

	public $id = 'give-ffm';
	public $basename;

	// Setup objects for each class
	public $admin_form;

	/**
	 * Main Give_Form_Fields_Manager Instance
	 *
	 * Insures that only one instance of Give_Form_Fields_Manager exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since     1.0
	 * @staticvar array $instance
	 * @return The one true Give_Form_Fields_Manager
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Give_Form_Fields_Manager ) ) {
			self::$instance = new Give_Form_Fields_Manager;
			self::$instance->define_globals();
			self::$instance->load_textdomain();
			self::$instance->includes();
			self::$instance->setup();

			// Setup Instances
			self::$instance->render_form        = new Give_FFM_Render_Form;
			self::$instance->setup              = new Give_FFM_Setup;
			self::$instance->upload             = new Give_FFM_Upload;
			self::$instance->frontend_form_post = new Give_FFM_Frontend_Form;

			if ( is_admin() ) {
				self::$instance->admin_form    = new Give_FFM_Admin_Form;
				self::$instance->admin_posting = new Give_FFM_Admin_Posting;
			}
		}

		return self::$instance;
	}

	/**
	 * Defines all the globally used constants
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function define_globals() {
		$this->title    = __( 'Form Field Manager', 'give-ffm' );
		$this->file     = __FILE__;
		$this->basename = apply_filters( 'give_ffm_plugin_basename', plugin_basename( $this->file ) );

		// Plugin Name
		if ( ! defined( 'GIVE_FFM_PRODUCT_NAME' ) ) {
			define( 'GIVE_FFM_PRODUCT_NAME', 'Form Field Manager' );
		}

		// Plugin Version
		if ( ! defined( 'GIVE_FFM_VERSION' ) ) {
			define( 'GIVE_FFM_VERSION', '1.1.1' );
		}

		// Plugin Root File
		if ( ! defined( 'GIVE_FFM_PLUGIN_FILE' ) ) {
			define( 'GIVE_FFM_PLUGIN_FILE', __FILE__ );
		}

		// Plugin Folder Path
		if ( ! defined( 'GIVE_FFM_PLUGIN_DIR' ) ) {
			define( 'GIVE_FFM_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . basename( dirname( __FILE__ ) ) . '/' );
		}

		// Plugin Folder URL
		if ( ! defined( 'GIVE_FFM_PLUGIN_URL' ) ) {
			define( 'GIVE_FFM_PLUGIN_URL', plugin_dir_url( GIVE_FFM_PLUGIN_FILE ) );
		}

		if ( class_exists( 'Give_License' ) && is_admin() ) {
			$give_ffm_license = new Give_License( __FILE__, GIVE_FFM_PRODUCT_NAME, GIVE_FFM_VERSION, 'WordImpress' );
		}
	}

	/**
	 * Loads the plugin language files
	 *
	 * @since  v1.0
	 * @access private
	 * @uses   dirname()
	 * @uses   plugin_basename()
	 * @uses   apply_filters()
	 * @uses   load_textdomain()
	 * @uses   get_locale()
	 * @uses   load_plugin_textdomain()
	 */
	private function load_textdomain() {

		// Set filter for plugin's languages directory
		$give_lang_dir = apply_filters( 'give_languages_directory', dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		// Traditional WordPress plugin locale filter
		$locale = apply_filters( 'plugin_locale', get_locale(), 'give-ffm' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'give-ffm', $locale );

		// Setup paths to current locale file
		$mofile_local  = $give_lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/give-ffm/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/give-ffm folder
			load_textdomain( 'give-ffm', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/give-ffm/languages/ folder
			load_textdomain( 'give-ffm', $mofile_local );
		} else {
			// Load the default language files
			load_plugin_textdomain( 'give-ffm', false, $give_lang_dir );
		}
	}

	/**
	 * Include all files
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function includes() {
		self::includes_general();
		self::includes_admin();
	}

	/**
	 * Load general files
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function includes_general() {
		$files = array(
			'class-setup.php',
			'class-render-form.php',
			'class-frontend-form.php',
			'class-upload.php',
			'class-emails.php',
			'functions.php'
		);

		foreach ( $files as $file ) {
			require( sprintf( '%s/includes/%s', untrailingslashit( GIVE_FFM_PLUGIN_DIR ), $file ) );
		}
	}

	/**
	 * Load admin files
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function includes_admin() {
		if ( is_admin() ) {
			$files = array(
				'admin-form.php',
				'admin-posting.php',
				'admin-template.php'
			);

			foreach ( $files as $file ) {
				require( sprintf( '%s/includes/admin/%s', untrailingslashit( GIVE_FFM_PLUGIN_DIR ), $file ) );
			}
		}
	}

	/**
	 * Setup FFM, loading scripts, styles and meta info
	 *
	 * @since 1.0
	 * @return void
	 */
	private function setup() {
		$this->setup = new Give_FFM_Setup();
		do_action( 'give_ffm_setup_actions' );
	}

}

/**
 * The main function responsible for returning the one true Give_Form_Fields_Manager
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $give_ffm = Give_FFM(); ?>
 *
 * @since 1.0
 * @return object The one true Give_Form_Fields_Manager Instance
 */

function Give_FFM() {

	if ( ! class_exists( 'Give' ) ) {
		return false;
	}

	return Give_Form_Fields_Manager::instance();
}

add_action( 'plugins_loaded', 'Give_FFM' );

/**
 * Give FFM Activation Banner
 *
 * @description: Includes and initializes the activation banner class; only runs in WP admin
 * @hook       admin_init
 */
function give_ffm_activation_banner() {

	//Check for Give
	if ( defined( 'GIVE_PLUGIN_FILE' ) ) {
		$give_plugin_basename = plugin_basename( GIVE_PLUGIN_FILE );
		$is_give_active       = is_plugin_active( $give_plugin_basename );
	} else {
		$is_give_active = false;
	}

	//Check to see if Give is activated, if it isn't deactivate and show a banner
	if ( is_admin() && current_user_can( 'activate_plugins' ) && ! $is_give_active ) {
		add_action( 'admin_notices', 'give_ffm_child_plugin_notice' );

		//Don't let this plugin activate
		deactivate_plugins( plugin_basename( __FILE__ ) );

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		return false;

	}

	$give_version = defined( 'GIVE_VERSION' ) ? GIVE_VERSION : '';

	//Is the Give core up-to-date with this Add-ons requirements?
	if ( version_compare( $give_version, '1.3.1', '<' ) ) {

		add_action( 'admin_notices', 'give_ffm_older_version_notice' );

		//Don't let this plugin activate
		deactivate_plugins( plugin_basename( __FILE__ ) );

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		return false;
	}

	//Check for activation banner inclusion
	if ( ! class_exists( 'Give_Addon_Activation_Banner' ) && file_exists( GIVE_PLUGIN_DIR . 'includes/admin/class-addon-activation-banner.php' ) ) {
		include GIVE_PLUGIN_DIR . 'includes/admin/class-addon-activation-banner.php';
	}

	//Only runs on admin
	$args = array(
		'file'              => __FILE__,
		//Directory path to the main plugin file
		'name'              => __( 'Form Field Manager', 'give-stripe' ),
		//name of the Add-on
		'version'           => GIVE_FFM_VERSION,
		//The most current version
		'documentation_url' => 'https://givewp.com/documentation/add-ons/form-field-manager/',
		'support_url'       => 'https://givewp.com/support/',
		//Location of Add-on settings page, leave blank to hide
		'testing'           => false,
		//Never leave as "true" in production!!!
	);

	new Give_Addon_Activation_Banner( $args );

	return false;

}

add_action( 'admin_init', 'give_ffm_activation_banner' );

/**
 * FFM Notice for No Core Activation
 */
function give_ffm_child_plugin_notice() {

	echo '<div class="error"><p>' . sprintf( __( '%sActivation Error:%s We noticed Give is not active. Please activate Give in order to use Form Field Manager.', 'give-recurring' ), '<strong>', '</strong>' ) . '</p></div>';
}

/**
 * FFM Notice for Older version
 */
function give_ffm_older_version_notice() {

	echo '<div class="error"><p>' . sprintf( __( '%sActivation Error:%s The Form Field Manager Add-on requires Give version 1.3.1 or higher. Please upgrade the Give plugin to activate this Add-on.', 'give-recurring' ), '<strong>', '</strong>' ) . '</p></div>';

}
