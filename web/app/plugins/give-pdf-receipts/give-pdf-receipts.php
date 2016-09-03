<?php
/**
 * Plugin Name: Give - PDF Receipts
 * Plugin URL: http://givewp.com/addons/pdf-receipts/
 * Description: Creates PDF Receipts for each donation that is downloadable via email and donation history
 * Author: WordImpress
 * Author URI: http://wordimpress.com
 * Contributors: wordimpress
 * Version: 2.0.3
 * Requires at least: 4.0
 * Tested up to: 4.5
 *
 * Text Domain: give_pdf
 * Domain Path: languages
 *
 * Copyright 2016 WordImpress
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Give_PDF_Receipts' ) ) :

	/**
	 * Give_PDF_Receipts Class
	 *
	 * @package Give_PDF_Receipts
	 * @since   1.0
	 */
	final class Give_PDF_Receipts {
		/**
		 * Holds the instance
		 *
		 * Ensures that only one instance of Give_PDF_Receipts exists in memory at any one
		 * time and it also prevents needing to define globals all over the place.
		 *
		 * TL;DR This is a static property property that holds the singleton instance.
		 *
		 * @var object
		 * @static
		 */
		private static $instance;

		/**
		 * Boolean whether or not to use the singleton, comes in handy
		 * when doing testing
		 *
		 * @var bool
		 * @static
		 */
		public static $testing = false;

		/**
		 * Holds the version number
		 *
		 * @var string
		 */
		public $version = '2.0.3';

		/**
		 * File
		 *
		 * @var string
		 */
		private $file = '';

		/**
		 * Basename
		 *
		 * @var string
		 */
		private $basename = '';

		/**
		 * Get the instance and store the class inside it. This plugin utilises
		 * the PHP singleton design pattern.
		 *
		 * @since     1.0
		 * @static
		 * @staticvar array $instance
		 * @access    public
		 * @see       give_pdf_receipts();
		 * @uses      Give_PDF_Receipts::includes() Loads all the classes
		 * @uses      Give_PDF_Receipts::hooks() Setup hooks and actions
		 * @return object self::$instance Instance
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Give_PDF_Receipts ) || self::$testing ) {
				self::$instance = new Give_PDF_Receipts();
				self::$instance->setup_globals();
				self::$instance->includes();
				self::$instance->hooks();
				self::$instance->licensing();
			}

			return self::$instance;
		}

		/**
		 * Throw error on object clone
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object therefore, we don't want the object to be cloned.
		 *
		 * @since  1.0
		 * @access protected
		 * @return void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'give' ), '1.6' );
		}

		/**
		 * Disable unserializing of the class
		 *
		 * @since  1.0
		 * @access protected
		 * @return void
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'give' ), '1.6' );
		}

		/*--------------------------------------------*
		 * Constructor
		 *--------------------------------------------*/

		/**
		 * Constructor Function
		 *
		 * @since  1.0
		 * @access protected
		 * @see    Give_PDF_Receipts::init()
		 */
		public function __construct() {
			self::$instance = $this;

			add_action( 'init', array( $this, 'init' ), - 1 );
		}

		/**
		 * Reset the instance of the class
		 *
		 * @since  1.0
		 * @access public
		 * @static
		 */
		public static function reset() {
			self::$instance = null;
		}

		/**
		 * Setup Globals
		 */
		public function setup_globals() {

			$this->file     = __FILE__;
			$this->basename = apply_filters( 'give_pdf_plugin_basename', plugin_basename( $this->file ) );

			/**
			 * Define Plugin Directory
			 *
			 * @since 1.0
			 */
			if ( ! defined( 'GIVE_PDF_PLUGIN_DIR' ) ) {
				define( 'GIVE_PDF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}

			/**
			 * Define Plugin URL
			 *
			 * @since 1.0
			 */
			if ( ! defined( 'GIVE_PDF_PLUGIN_URL' ) ) {
				define( 'GIVE_PDF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			}

			/**
			 * Define Plugin File Name
			 *
			 * @since 1.0
			 */
			if ( ! defined( 'GIVE_PDF_PLUGIN_FILE' ) ) {
				define( 'GIVE_PDF_PLUGIN_FILE', __FILE__ );
			}

			/**
			 * Software Licensing
			 *
			 * Integrates the plugin with the EDD Software Licensing
			 *
			 * @since 1.0
			 */
			if ( ! defined( 'GIVE_PDF_STORE_URL' ) ) {
				define( 'GIVE_PDF_STORE_URL', 'https://givewp.com' );
			}

			if ( ! defined( 'GIVE_PDF_ITEM_NAME' ) ) {
				define( 'GIVE_PDF_ITEM_NAME', 'PDF Receipts' );
			}
		}

		/**
		 * Function fired on init
		 *
		 * This function is called on WordPress 'init'. It's triggered from the
		 * constructor function.
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @uses   Give_PDF_Receipts::load_plugin_textdomain()
		 *
		 * @return void
		 */
		public function init() {
			do_action( 'give_pdf_before_init' );

			$this->load_plugin_textdomain();

			do_action( 'give_pdf_after_init' );
		}

		/**
		 * Includes
		 *
		 * @since  1.0
		 * @access private
		 * @return void
		 */
		private function includes() {
			require_once( GIVE_PDF_PLUGIN_DIR . 'includes/templates/template-blue-stripe.php' );
			require_once( GIVE_PDF_PLUGIN_DIR . 'includes/templates/template-colors.php' );
			require_once( GIVE_PDF_PLUGIN_DIR . 'includes/templates/template-default.php' );
			require_once( GIVE_PDF_PLUGIN_DIR . 'includes/templates/template-lines.php' );
			require_once( GIVE_PDF_PLUGIN_DIR . 'includes/templates/template-minimal.php' );
			require_once( GIVE_PDF_PLUGIN_DIR . 'includes/templates/template-traditional.php' );

			do_action( 'give_pdf_load_templates' );

			require_once( GIVE_PDF_PLUGIN_DIR . 'includes/email-template-tag.php' );
			require_once( GIVE_PDF_PLUGIN_DIR . 'includes/email-templates.php' );
			require_once( GIVE_PDF_PLUGIN_DIR . 'includes/i18n.php' );
			require_once( GIVE_PDF_PLUGIN_DIR . 'includes/settings.php' );
			require_once( GIVE_PDF_PLUGIN_DIR . 'includes/template-functions.php' );
			require_once( GIVE_PDF_PLUGIN_DIR . 'includes/scripts.php' );
			require_once( GIVE_PDF_PLUGIN_DIR . 'includes/ajax-functions.php' );
			require_once( GIVE_PDF_PLUGIN_DIR . 'includes/plugin-compatibility.php' );
		}

		/**
		 * Hooks
		 */
		public function hooks() {

			add_action( 'admin_init', array( $this, 'check_plugin_requirements' ) );
			add_action( 'admin_notices', array( 'Give_PDF_Receipts', 'admin_activation_notice' ) );
			add_action( 'give_purchase_history_header_after', array( $this, 'purchase_history_header' ) );
			add_action( 'init', array( $this, 'verify_receipt_link' ), 10 );
			add_action( 'give_purchase_history_row_end', array( $this, 'purchase_history_link' ), 10, 2 );
			add_action( 'give_generate_pdf_receipt', array( $this, 'generate_pdf_receipt' ) );
			add_action( 'give_payment_receipt_after', array( $this, 'receipt_shortcode_link' ), 10 );
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
			add_filter( 'give_payment_row_actions', array( $this, 'receipt_link' ), 10, 2 );
			add_filter( 'plugin_action_links', array( $this, 'give_plugin_action_links' ), 10, 2 );

		}

		/**
		 * Implement Give Licensing
		 */
		private function licensing() {
			if ( class_exists( 'Give_License' ) && is_admin() ) {
				$give_pdf_license = new Give_License( __FILE__, 'PDF Receipts', $this->version, 'WordImpress' );
			}
		}

		/**
		 * Load Plugin Text Domain
		 *
		 * Looks for the plugin translation files in certain directories and loads
		 * them to allow the plugin to be localised
		 *
		 * @since  1.0
		 * @access public
		 * @return bool True on success, false on failure
		 */
		public function load_plugin_textdomain() {
			// Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale', get_locale(), 'give_pdf' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'give_pdf', $locale );

			// Setup paths to current locale file
			$mofile_local = trailingslashit( plugin_dir_path( __FILE__ ) . 'languages' ) . $mofile;

			if ( file_exists( $mofile_local ) ) {
				// Look in the /wp-content/plugins/give-pdf-receipts/languages/ folder
				load_textdomain( 'give_pdf', $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'give_pdf', false, trailingslashit( plugin_dir_path( __FILE__ ) . 'languages' ) );
			}

			return false;
		}

		/**
		 * Show top notification
		 */
		public static function admin_activation_notice() {
			if ( $notices = get_option( 'give_pdf_install_admin_notices' ) ) {
				foreach ( $notices as $notice ) {
					echo "<div class='updated'><p>$notice</p></div>";
				}
				delete_option( 'give_pdf_install_admin_notices' );
			}
		}

		/**
		 * Insert default template
		 *
		 * @param string $template_name Name of template
		 * @param string $filename Filename of the template
		 */
		private static function add_default_template( $template_name, $filename ) {

			if ( ! file_exists( plugin_dir_path( __FILE__ ) . 'templates/' . $filename ) ) {
				return;
			}

			ob_start();

			include( plugin_dir_path( __FILE__ ) . 'templates/' . $filename );

			$content = ob_get_clean();

			// Replace placeholders
			$content = str_replace( '%assets_url%', get_site_url() . '/wp-content/plugins/Give-PDF-Receipts/assets', $content );

			$template = array(
				'post_title'     => $template_name,
				'post_content'   => $content,
				'post_type'      => 'Give_PDF_Template',
				'ping_status'    => 'closed',
				'comment_status' => 'closed'
			);

			// Insert template
			$post_id = wp_insert_post( $template );

			//Flag this post as a template so we delete these only on deactivation
			add_post_meta( $post_id, '_give_pdf_receipts_template', true );

			//Set default option if not set already
			$set_template = give_get_option( 'give_pdf_receipt_template' );
			if ( ! $set_template ) {
				give_update_option( 'give_pdf_receipt_template', $post_id );
				give_update_option( 'give_pdf_receipt_template_name', get_the_title( $post_id ) );
			}

		}

		/**
		 * Check Plugin Requirements
		 *
		 * @return bool
		 */
		public static function check_plugin_requirements() {

			//Check for Give - if not active, deactivate/bail
			if ( ! class_exists( 'Give' ) ) {

				$basename = plugin_basename( __FILE__ );
				if ( is_plugin_active( $basename ) ) {
					deactivate_plugins( $basename );
					if ( isset( $_GET['activate'] ) ) {
						unset( $_GET['activate'] );
					}
				}
				return false;
			}

			//Check for PHP version - if not 5.3+, deactivate/bail w/ message
			if ( version_compare( phpversion(), '5.3', '<' ) ) {

				// php version isn't high enough
				$basename = plugin_basename( __FILE__ );

				if ( is_plugin_active( $basename ) ) {
					deactivate_plugins( $basename );
					if ( isset( $_GET['activate'] ) ) {
						unset( $_GET['activate'] );
					}

					echo '<div class="updated"><p>' . sprintf( __( '%1$sActivation Error:%2$s The Give PDF Receipts Add-on requires PHP version 5.3 or newer in order to activate. We recommend PHP 5.5 for best performance and stability. Please contact your host to upgrade your server to PHP 5.3+ in order to use the plugin.', 'give' ), '<strong>', '</strong>' ) . '</p></div>';

				}

				return false;
			}

			return true;

		}

		/**
		 * Activation Checks
		 *
		 * This function is fired when the activation hook is called by WordPress and checks for compatibilities
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @return bool|mixed
		 */
		public static function activation_checks() {

			//Clear any notices
			delete_option( 'give_pdf_install_admin_notices' );

			if(self::check_plugin_requirements()){
				//Now that is passes move to activation
				self::activation();
			};
			
		}

		/**
		 * Activation function fires when the plugin is activated.
		 *
		 * This function is fired when the activation hook is called by WordPress,
		 * it flushes the rewrite rules and disables the plugin if Give isn't active
		 * and throws an error.
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @return mixed
		 */
		public static function activation() {

			// Add admin notice
			$notices   = get_option( 'give_pdf_install_admin_notices', array() );
			$notices[] = sprintf( __( '%1$sGive PDF Receipts:%2$s Thank you for activating the Give PDF Receipts Add-on. If you are a first time user please take a minute to review the %3$splugin documentation%4$s and remember to %5$sactivate your license%6$s. If you have any remaining questions or concerns please %7$scontact priority support%6$s.', 'give' ), '<strong>', '</strong>', '<a href="https://givewp.com/documentation/add-ons/pdf-receipts/" target="_blank">', '</a>', '<a href="' . admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=licenses' ) . '">', '</a>', '<a href="https://givewp.com/support/" target="_blank">' );

			update_option( 'give_pdf_install_admin_notices', $notices );

			// Define Give_PDF_Template post type
			$template_args = array(
				'labels'      => array(
					'name'          => 'Give_PDF_Template',
					'singular_name' => 'Give_PDF_Template'
				),
				'public'      => false,
				'has_archive' => false,
			);

			// Register Give_PDF_Template post type
			register_post_type( 'give_pdf_template', $template_args );

			// Insert default templates
			Give_PDF_Receipts::add_default_template( 'Receipt Template 1', 'receipt-1.php' );

			Give_PDF_Receipts::add_default_template( 'Receipt Template 2', 'receipt-2.php' );

			Give_PDF_Receipts::add_default_template( 'Receipt Template 3', 'receipt-3.php' );

			Give_PDF_Receipts::add_default_template( 'Receipt Template 4', 'receipt-4.php' );

			//Flush rewrite rules because we created a new CPT
			flush_rewrite_rules();


		}

		/**
		 * Deactivation function
		 *
		 * @description: Delete all default templates from database
		 *
		 * @since  1.0
		 * @access public
		 *
		 * @return void
		 */
		public static function deactivation() {

			$args = array(
				'post_type'      => 'Give_PDF_Template',
				'post_status'    => array( 'draft', 'publish' ),
				'posts_per_page' => - 1,
				'meta_key'       => '_give_pdf_receipts_template'
			);

			$posts = get_posts( $args );

			foreach ( $posts as $post ) {
				wp_delete_post( $post->ID, true );
			}
		}

		/**
		 * Handles the displaying of any notices in the admin area
		 *
		 * @since  1.0
		 * @access public
		 * @return mixed
		 */
		public function admin_notices() {

			if ( self::check_plugin_requirements() ) {
				return false;
			}

			$is_give_settings = ( isset( $_GET['page'] ) && $_GET['page'] == 'give-settings' ) ? true : false;

			if ( ! is_plugin_active( 'give/give.php' ) ) {
				echo '<div class="error"><p>' . sprintf( __( '%sActivation Error%s: You must install the %sGive%s core plugin for the PDF Receipts Add-on to activate.', 'give_pdf' ), '<strong>', '</strong>', '<a href="https://givewp.com" title="Visit the Give website" target="_blank">', '</a>' ) . '</p></div>';

				return false;
			}

			$pdf_templates = give_get_option( 'give_pdf_templates' );

			if ( ! isset ( $pdf_templates ) && ! $is_give_settings ) {
				echo '<div class="updated"><p>' . sprintf( __( 'Please visit the %sPDF Receipt Settings%s to configure the plugin. Currently the settings have not been configured correctly therefore you may have issues when trying to generate receipts.', 'give_pdf' ), '<a href="edit.php?post_type=give_forms&page=give-settings&tab=pdf_receipts">', '</a>' ) . '</p></div>';
			}
		}

		/**
		 * Creates Link to Download Receipt
		 *
		 * Creates a link on the Payment History admin page for each payment to
		 * allow the ability to download an receipt for that payment
		 *
		 * @since 1.0
		 *
		 * @param array $row_actions All the row actions on the Payment History page
		 * @param object $give_pdf_payment Payment object containing all the payment data
		 *
		 * @return array Modified row actions with Download Receipt link
		 */
		public function receipt_link( $row_actions, $give_pdf_payment ) {
			$row_actions_pdf_receipt_link = array();

			$give_pdf_generate_receipt_nonce = wp_create_nonce( 'give_pdf_generate_receipt' );

			if ( $this->is_receipt_link_allowed( $give_pdf_payment->ID ) ) {
				$row_actions_pdf_receipt_link = array(
					'receipt' => '<a href="' . esc_url( add_query_arg( array(
							'give-action'    => 'generate_pdf_receipt',
							'transaction_id' => $give_pdf_payment->ID,
							'_wpnonce'       => $give_pdf_generate_receipt_nonce
						) ) ) . '">' . __( 'Download Receipt', 'give_pdf' ) . '</a>',
				);
			}

			return array_merge( $row_actions, $row_actions_pdf_receipt_link );
		}

		/**
		 * Donation History Page Table Heading
		 *
		 * Appends to the table header (<thead>) on the Purchase History page for the
		 * Receipt column to be displayed
		 *
		 * @since 1.0
		 */
		function purchase_history_header() {
			echo '<th class="give_receipt">' . __( 'Receipt', 'give_pdf' ) . '</th>';
		}

		/**
		 * Outputs the Receipt link
		 *
		 * Adds the receipt link to the [purchase_history] shortcode underneath the
		 * previously created Receipt header
		 *
		 * @since 1.0
		 *
		 * @param int $post_id Payment post ID
		 * @param array $purchase_data All the purchase data
		 */
		function purchase_history_link( $post_id, $purchase_data ) {
			if ( ! $this->is_receipt_link_allowed( $post_id ) ) {
				echo '<td>-</td>';

				return;
			}

			echo '<td class="give_receipt"><a class="give_receipt_link" title="' . __( 'Download Receipt', 'give_pdf' ) . '" href="' . esc_url( give_pdf_receipts()->get_pdf_receipt_url( $post_id ) ) . '">' . __( 'Download Receipt', 'give_pdf' ) . '</td>';
		}

		/**
		 * Receipt Shortcode Receipt Link
		 *
		 * Adds the receipt link to the [give_receipt] shortcode
		 *
		 * @since 1.0.4
		 *
		 * @param object $payment All the payment data
		 */
		public function receipt_shortcode_link( $payment ) {
			if ( ! $this->is_receipt_link_allowed( $payment->ID ) ) {
				return;
			}
			$purchase_data = give_get_payment_meta( $payment->ID );
			?>
			<tr>
				<td><strong><?php _e( 'Receipt', 'give_pdf' ); ?>:</strong></td>
				<td>
					<a class="give_receipt_link" title="<?php _e( 'Download Receipt', 'give_pdf' ); ?>" href="<?php echo esc_url( give_pdf_receipts()->get_pdf_receipt_url( $payment->ID ) ); ?>"><?php _e( 'Download Receipt', 'give_pdf' ); ?></a>
				</td>
			</tr>
			<?php
		}

		/**
		 * Gets the Receipt URL
		 *
		 * Generates an receipt URL and adds the necessary query arguments
		 *
		 * @since 1.0
		 *
		 * @param int $post_id Payment post ID
		 * @param array $purchase_data All the purchase data
		 *
		 * @return string $receipt Receipt URL
		 */
		public function get_pdf_receipt_url( $payment_id ) {

			$give_pdf_params = array(
				'transaction_id' => $payment_id,
				'email'          => urlencode( give_get_payment_user_email( $payment_id ) ),
				'purchase_key'   => give_get_payment_key( $payment_id ),
			);

			$receipt = esc_url( add_query_arg( $give_pdf_params, home_url() ) );

			return $receipt;
		}

		/**
		 * Plugins row action links
		 *
		 * @param array $links already defined action links
		 * @param string $file plugin file path and name being processed
		 *
		 * @return array $links
		 */
		function give_plugin_action_links( $links, $file ) {

			$settings_link = '<a href="' . admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=pdf_receipts' ) . '">' . esc_html__( 'Settings', 'give_pdf' ) . '</a>';

			if ( $file == 'give-pdf-receipts/give-pdf-receipts.php' ) {
				array_unshift( $links, $settings_link );
			}

			return $links;

		}

		/**
		 * Verify Receipt Link
		 *
		 * Verifies the receipt link submitted from the front-end
		 *
		 * @since 1.0
		 */
		public function verify_receipt_link() {

			if ( isset( $_GET['transaction_id'] ) && isset( $_GET['email'] ) && isset( $_GET['purchase_key'] ) ) {
				if ( ! $this->is_receipt_link_allowed( $_GET['transaction_id'] ) ) {
					return;
				}

				$key   = $_GET['purchase_key'];
				$email = $_GET['email'];

				$meta_query = array(
					'relation' => 'AND',
					array(
						'key'   => '_give_payment_purchase_key',
						'value' => $key
					),
					array(
						'key'   => '_give_payment_user_email',
						'value' => $email
					)
				);

				$payments = get_posts( array(
					'meta_query' => $meta_query,
					'post_type'  => 'give_payment'
				) );

				if ( $payments ) {
					give_pdf_receipts()->generate_pdf_receipt();
				} else {
					wp_die( __( 'The receipt that you requested was not found.', 'give_pdf' ), __( 'Receipt Not Found', 'give_pdf' ) );
				}
			}
		}

		/**
		 * Generate PDF Receipt
		 *
		 * Loads and stores all of the data for the payment.  The HTML2PDF class is
		 * instantiated and do_action() is used to call the receipt template which goes
		 * ahead and renders the receipt.
		 *
		 * @since 1.0
		 * @uses  HTML2PDF
		 * @uses  wp_is_mobile()
		 */
		public function generate_pdf_receipt() {

			global $give_options;

			//Sanity check: need transaction ID
			if ( ! isset( $_GET['transaction_id'] ) ) {
				return;
			}

			if ( ! $this->is_receipt_link_allowed( $_GET['transaction_id'] ) ) {
				return;
			}

			do_action( 'give_pdf_generate_receipt', $_GET['transaction_id'] );

			$give_pdf_payment      = get_post( $_GET['transaction_id'] );
			$give_pdf_payment_meta = give_get_payment_meta( $_GET['transaction_id'] );

			// If url parameters has _wpnonce=give_pdf_generate_receipt
			$give_pdf_receipt_nonce = isset( $_GET['_wpnonce'] ) ? $_GET['_wpnonce'] : null;
			$is_nonce               = is_admin() && wp_verify_nonce( $give_pdf_receipt_nonce, 'give_pdf_generate_receipt' );

			if ( $is_nonce ) {
				$give_pdf_buyer_info      = maybe_unserialize( $give_pdf_payment_meta['user_info'] );
				$give_pdf_payment_gateway = get_post_meta( $give_pdf_payment->ID, '_give_payment_gateway', true );
			} else {
				$give_pdf_buyer_info      = give_get_payment_meta_user_info( $give_pdf_payment->ID );
				$give_pdf_payment_gateway = give_get_payment_gateway( $give_pdf_payment->ID );
			}
			$give_pdf_payment_method = give_get_gateway_admin_label( $give_pdf_payment_gateway );

			$company_name = isset( $give_options['give_pdf_company_name'] ) ? $give_options['give_pdf_company_name'] : '';

			$give_pdf_payment_date   = date_i18n( get_option( 'date_format' ), strtotime( $give_pdf_payment->post_date ) );
			$give_pdf_payment_status = give_get_payment_status( $give_pdf_payment, true );

			// WPML Support
			if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
				$lang = get_post_meta( $_GET['transaction_id'], 'wpml_language', true );
				if ( ! empty( $lang ) ) {
					global $sitepress;
					$sitepress->switch_lang( $lang );
				}
			}

			// If generation method is custom pdf template
			if ( isset( $give_options['give_pdf_generation_method'] )
			     && $give_options['give_pdf_generation_method'] == 'custom_pdf_builder'
			) {

				// Enable images in pdf
				define( 'DOMPDF_ENABLE_REMOTE', true );
				// Include dompdf library
				include_once( GIVE_PDF_PLUGIN_DIR . '/lib/dompdf/dompdf_config.inc.php' );

				$template_id = $give_options['give_pdf_receipt_template'];
				$template    = get_post( $template_id );

				// Create pdf document
				$dompdf = new DOMPDF();
				$dompdf->set_paper( apply_filters( 'give_dompdf_paper_size', array( 0, 0, 595.28, 841.89 ) ) );
				$html = give_pdf_get_compile_html(
					$template->post_content,
					$give_pdf_payment,
					$give_pdf_payment_method,
					$give_pdf_payment_status,
					$give_pdf_payment_meta,
					$give_pdf_buyer_info,
					$give_pdf_payment_date,
					$_GET['transaction_id'],
					$this->get_pdf_receipt_url( $give_pdf_payment->ID )
				);
				$dompdf->load_html( $html );
				$dompdf->render();
				$dompdf->stream(
					apply_filters( 'give_pdf_receipt_filename_prefix', 'Receipt-' ) . give_pdf_get_payment_number( $give_pdf_payment->ID ) . '.pdf',
					array( 'Attachment' => ! wp_is_mobile() )
				);

			} // Generate pdf using legacy TCPDF default template
			else {
				include_once( GIVE_PDF_PLUGIN_DIR . '/lib/tcpdf/tcpdf.php' );
				include_once( GIVE_PDF_PLUGIN_DIR . '/includes/class-give-tcpdf.php' );

				$give_pdf = new Give_PDF_Receipt( 'P', 'mm', 'A4', true, 'UTF-8', false );

				$give_pdf->SetDisplayMode( 'real' );
				$give_pdf->setJPEGQuality( 100 );

				$give_pdf->SetTitle( __( ( $is_nonce ? 'Receipt ' : 'Donation Receipt' ) . give_pdf_get_payment_number( $give_pdf_payment->ID ), 'give_pdf' ) );
				$give_pdf->SetCreator( __( 'Give' ) );
				$give_pdf->SetAuthor( get_option( 'blogname' ) );

				$address_line_2_line_height = isset( $give_options['give_pdf_address_line2'] ) ? 6 : 0;

				if ( ! isset( $give_options['give_pdf_templates'] ) ) {
					$give_options['give_pdf_templates'] = 'default';
				}

				do_action( 'give_pdf_template_' . $give_options['give_pdf_templates'], $give_pdf, $give_pdf_payment, $give_pdf_payment_meta, $give_pdf_buyer_info, $give_pdf_payment_gateway, $give_pdf_payment_method, $address_line_2_line_height, $company_name, $give_pdf_payment_date, $give_pdf_payment_status );

				if ( ob_get_length() ) {
					if ( $is_nonce ) {
						ob_clean();
					}
					ob_end_clean();
				}

				$give_pdf->Output( apply_filters( 'give_pdf_receipt_filename_prefix', 'Receipt-' ) . give_pdf_get_payment_number( $give_pdf_payment->ID ) . '.pdf', wp_is_mobile() ? 'I' : 'D' );
			}

			die(); // Stop the rest of the page from processsing and being sent to the browser
		}

		/**
		 * Check is receipt link is allowed
		 *
		 * @since  1.0
		 * @access private
		 * @global    $give_options
		 *
		 * @param int $id Payment ID to verify total
		 *
		 * @return bool
		 */
		public function is_receipt_link_allowed( $id = null ) {

			$ret = true;

			if ( ! give_is_payment_complete( $id ) ) {
				$ret = false;
			}

			return apply_filters( 'give_pdf_is_receipt_link_allowed', $ret, $id );
		}

	} //End Give_PDF_Receipts Class

	/**
	 * Loads a single instance of EDD PDF Receipts
	 *
	 * This follows the PHP singleton design pattern.
	 *
	 * Use this function like you would a global variable, except without needing
	 * to declare the global.
	 *
	 * @example <?php $give_pdf_receipts = give_pdf_receipts(); ?>
	 *
	 * @since   1.0
	 *
	 * @see     Give_PDF_Receipts::get_instance()
	 *
	 * @return object Returns an instance of the Give_PDF_Receipts class
	 */
	function give_pdf_receipts() {
		return Give_PDF_Receipts::get_instance();
	}

	/**
	 * The activation hook is called outside of the singleton because WordPress does not
	 * register the call from within the class hence, needs to be called outside and the
	 * function also needs to be static.
	 */
	register_activation_hook( __FILE__, array( 'Give_PDF_Receipts', 'activation_checks' ) );


	/**
	 * Loads plugin after all the others have loaded and have registered their
	 * hooks and filters
	 */
	add_action( 'plugins_loaded', 'give_pdf_receipts', apply_filters( 'give_pdf_action_priority', 10 ) );

	register_deactivation_hook( __FILE__, array( 'Give_PDF_Receipts', 'deactivation' ) );


endif;

