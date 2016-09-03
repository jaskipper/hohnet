<?php
/**
 * Plugin Name:        Give - Recurring Donations
 * Plugin URI:         http://givewp.com/addons/give-recurring-donations
 * Description:        Adds support for recurring donations to the Give Donations plugin.
 * Author:             WordImpress
 * Author URI:         http://wordimpress.com
 * Contributors:       WordImpress
 * Version:            1.1.1
 * GitHub Plugin URI:  https://github.com/WordImpress/Give-Recurring-Donations
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'GIVE_RECURRING_VERSION' ) ) {
	define( 'GIVE_RECURRING_VERSION', '1.1.1' );
}

if ( ! defined( 'GIVE_RECURRING_PLUGIN_DIR' ) ) {
	define( 'GIVE_RECURRING_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'GIVE_RECURRING_PLUGIN_URL' ) ) {
	define( 'GIVE_RECURRING_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'GIVE_RECURRING_PLUGIN_FILE' ) ) {
	define( 'GIVE_RECURRING_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'GIVE_RECURRING_PLUGIN_BASENAME' ) ) {
	define( 'GIVE_RECURRING_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

//Licensing
function give_add_recurring_licensing() {
	if ( class_exists( 'Give_License' ) && is_admin() ) {
		$give_recurring_license = new Give_License( __FILE__, 'Recurring Donations', GIVE_RECURRING_VERSION, 'WordImpress', 'recurring_license_key' );
	}
}

add_action( 'plugins_loaded', 'give_add_recurring_licensing' );

// Remove Stripe
function give_remove_stripe_event_listener() {
	remove_action( 'init', 'give_stripe_event_listener' );
}

add_action( 'plugins_loaded', 'give_remove_stripe_event_listener', 99 );

/**
 * Class Give_Recurring
 */
final class Give_Recurring {

	/** Singleton *************************************************************/

	static $plugin_path;
	static $plugin_dir;

	/**
	 * Give_Recurring instance
	 *
	 * @var Give_Recurring The one true Give_Recurring
	 */
	private static $instance;

	/**
	 * Give_Recurring Emails Object
	 *
	 * @var object
	 * @since 1.0
	 */
	public $emails;

	/**
	 * Give_Recurring Cron Object
	 *
	 * @var object
	 * @since 1.0
	 */
	public $cron;

	/**
	 * Main Give_Recurring Instance
	 *
	 * Insures that only one instance of Give_Recurring exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since     v1.0
	 * @staticvar array $instance
	 * @uses      Give_Recurring::setup_globals() Setup the globals needed
	 * @uses      Give_Recurring::includes() Include the required files
	 * @uses      Give_Recurring::setup_actions() Setup the hooks and actions
	 * @see       Give()
	 * @return The one true Give_Recurring
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Give_Recurring;

			self::$plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
			self::$plugin_dir  = untrailingslashit( plugin_dir_url( __FILE__ ) );

			self::$instance->init();
		}

		return self::$instance;
	}


	/**
	 * Get things started
	 *
	 * Sets up globals, loads text domain, loads includes, inits actions and filters, starts customer class
	 *
	 * @since v1.0
	 */

	public function init() {

		self::includes_global();

		self::load_textdomain();

		if ( is_admin() ) {
			self::includes_admin();
		}

		self::actions();
		self::filters();

		//		$subscribers_api     = new Give_Subscriptions_API();
		self::$instance->emails = new Give_Recurring_Emails();
		self::$instance->cron   = new Give_Recurring_Cron();

	}


	/**
	 * Load global files
	 *
	 * @since  1.0
	 * @return void
	 */
	private function includes_global() {

		$files = array(
			'give-subscriptions-db.php',
			'give-subscription.php',
			'give-recurring-post-types.php',
			'give-recurring-shortcodes.php',
			'give-recurring-subscriber.php',
			'give-recurring-template.php',
			'give-recurring-helpers.php',
			'give-recurring-scripts.php',
			'gateways/give-recurring-gateway.php',
			'give-recurring-emails.php',
			'give-recurring-renewals.php',
			'give-recurring-expirations.php',
			'give-recurring-cron.php'
		);

		//Fancy way of requiring files
		foreach ( $files as $file ) {
			require( sprintf( '%s/includes/%s', self::$plugin_path, $file ) );
		}

		//Get the gateways
		foreach ( give_get_payment_gateways() as $gateway_id => $gateway ) {

			if ( file_exists( sprintf( '%s/includes/gateways/give-recurring-%s.php', self::$plugin_path, $gateway_id ) ) ) {
				require( sprintf( '%s/includes/gateways/give-recurring-%s.php', self::$plugin_path, $gateway_id ) );
			}
		}
	}

	/**
	 * Load admin files
	 *
	 * @since  1.0
	 * @return void
	 */
	private function includes_admin() {
		$files = array(
			'customers.php',
			'class-subscriptions-list-table.php',
			'class-recurring-reports.php',
			'class-admin-notices.php',
			'class-shortcode-generator.php',
			'subscriptions.php',
			'subscriptions-details.php',
			'metabox.php',
			'settings.php',
			'settings-recurring-emails.php'
		);

		//fancy way of requiring files
		foreach ( $files as $file ) {
			require( sprintf( '%s/includes/admin/%s', self::$plugin_path, $file ) );
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
		$give_lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
		$give_lang_dir = apply_filters( 'give_recurring_languages_directory', $give_lang_dir );


		// Traditional WordPress plugin locale filter
		$locale = apply_filters( 'plugin_locale', get_locale(), 'give-recurring' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'give-recurring', $locale );

		// Setup paths to current locale file
		$mofile_local  = $give_lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/give-recurring/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/give-recurring folder
			load_textdomain( 'give-recurring', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/give-recurring/languages/ folder
			load_textdomain( 'give-recurring', $mofile_local );
		} else {
			// Load the default language files
			load_plugin_textdomain( 'give-recurring', false, $give_lang_dir );
		}

	}


	/**
	 * Add our actions
	 *
	 * @since  1.0
	 * @return void
	 */
	private function actions() {

		add_action( 'admin_menu', array( $this, 'subscriptions_list' ), 10 );

		// Register our post stati
		add_action( 'wp_loaded', array( $this, 'register_post_statuses' ) );

		add_action( 'give_purchase_form_before_register_login', array(
			$this,
			'maybe_show_register_login_forms'
		), 1, 1 );

		//Ensure AJAX gets appropriate login / register fields on cancel
		add_action( 'wp_ajax_nopriv_give_cancel_login', array(
			$this,
			'maybe_show_register_login_forms'
		), 1, 1 );
		add_action( 'wp_ajax_nopriv_give_checkout_register', array(
			$this,
			'maybe_show_register_login_forms'
		), 1, 1 );

		// Tell Give to include subscription payments in Payment History
		add_action( 'give_pre_get_payments', array( $this, 'enable_child_payments' ), 100 );

		// Modify the gateway data before it goes to the gateway
		add_filter( 'give_purchase_data_before_gateway', array( $this, 'modify_purchase_data' ), 10, 2 );

		//Add a settings link
		add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );

	}


	/**
	 * Add our filters
	 *
	 * @since  1.0
	 * @return void
	 */
	private function filters() {

		// Register our new payment statuses
		add_filter( 'give_payment_statuses', array( $this, 'register_recurring_statuses' ) );

		// Set the payment stati
		add_filter( 'give_is_payment_complete', array( $this, 'is_payment_complete' ), 10, 3 );

		// Show the Cancelled and Subscription status links in Payment History
		add_filter( 'give_payments_table_views', array( $this, 'payments_view' ) );

		// Include subscription payments in the calculation of earnings
		add_filter( 'give_get_total_earnings_args', array( $this, 'earnings_query' ) );
		add_filter( 'give_get_earnings_by_date_args', array( $this, 'earnings_query' ) );
		add_filter( 'give_get_sales_by_date_args', array( $this, 'earnings_query' ) );
		add_filter( 'give_stats_earnings_args', array( $this, 'earnings_query' ) );
		add_filter( 'give_get_sales_by_date_args', array( $this, 'earnings_query' ) );

		add_filter( 'give_get_users_purchases_args', array( $this, 'has_purchased_query' ) );

		// Allow PDF Invoices to be downloaded for subscription payments
		add_filter( 'give_pdfi_is_invoice_link_allowed', array( $this, 'is_invoice_allowed' ), 10, 2 );

	}

	/**
	 * Modify Purchase Data
	 *
	 * @description Modify the data sent to payment gateways
	 *
	 * @since       1.0
	 *
	 * @param $purchase_data
	 * @param $valid_data
	 *
	 * @return mixed
	 */
	public function modify_purchase_data( $purchase_data, $valid_data ) {

		if ( isset( $purchase_data['post_data'] ) ) {
			$form_id  = isset( $purchase_data['post_data']['give-form-id'] ) ? $purchase_data['post_data']['give-form-id'] : 0;
			$price_id = isset( $purchase_data['post_data']['give-price-id'] ) ? $purchase_data['post_data']['give-price-id'] : 0;
		} else {
			$form_id  = isset( $purchase_data['form_id'] ) ? $purchase_data['form_id'] : 0;
			$price_id = isset( $purchase_data['price_id'] ) ? $purchase_data['price_id'] : 0;
		}

		$is_recurring = $this->is_purchase_recurring( $purchase_data );

		//is this even recurring?
		if ( ! $is_recurring ) {
			//nope, bounce out
			return $purchase_data;
		} else if ( empty( $form_id ) ) {
			//@TODO: Log transaction error here (no form ID)
			return $purchase_data;
		}

		//Add times and period to purchase data
		$set_or_multi   = get_post_meta( $form_id, '_give_price_option', true );
		$recurring_type = get_post_meta( $form_id, '_give_recurring', true );

		//Multi-level admin chosen recurring
		if ( give_has_variable_prices( $form_id ) && $set_or_multi == 'multi' && $recurring_type == 'yes_admin' ) {

			$purchase_data['period'] = Give_Recurring::get_period( $form_id, $price_id );
			$purchase_data['times']  = Give_Recurring::get_times( $form_id, $price_id );

		} else {

			//single & multilevel basic
			$purchase_data['period'] = get_post_meta( $form_id, '_give_period', true );
			$purchase_data['times']  = get_post_meta( $form_id, '_give_times', true );

		}

		return $purchase_data;
	}

	/**
	 * Registers the cancelled post status
	 *
	 * @since  1.0
	 * @return void
	 */
	public function register_post_statuses() {

		register_post_status( 'give_subscription', array(
			'label'                     => _x( 'Subscription', 'Subscription payment status', 'give-recurring' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Subscription <span class="count">(%s)</span>', 'Subscription <span class="count">(%s)</span>', 'give-recurring' )
		) );
	}

	/**
	 * Register our Subscriptions submenu
	 *
	 * @since  1.0
	 * @return void
	 */
	public function subscriptions_list() {
		add_submenu_page(
			'edit.php?post_type=give_forms',
			__( 'Subscriptions', 'give-recurring' ),
			__( 'Subscriptions', 'give' ),
			'view_give_reports',
			'give-subscriptions',
			'give_subscriptions_page'
		);
	}

	/**
	 * Is Payment Complete
	 *
	 * @description: Returns true or false depending on payment status
	 *
	 * @since      1.0
	 * @return array
	 */
	public function is_payment_complete( $ret, $payment_id, $status ) {

		if ( $status == 'cancelled' ) {

			$ret = true;

		} elseif ( 'give_subscription' == $status ) {

			$parent = get_post_field( 'post_parent', $payment_id );
			if ( give_is_payment_complete( $parent ) ) {
				$ret = true;
			}

		}

		return $ret;
	}


	/**
	 * Register Recurring Statuses
	 *
	 * @description Tells Give about our new payment status
	 *
	 * @param $stati
	 *
	 * @return mixed
	 */

	public function register_recurring_statuses( $stati ) {
		$stati['give_subscription'] = __( 'Subscription', 'give-recurring' );
		$stati['cancelled']         = __( 'Cancelled', 'give-recurring' );

		return $stati;
	}


	/**
	 * Payments View
	 *
	 * @description Displays the cancelled payments filter link
	 *
	 * @since       1.0
	 *
	 * @param $views
	 *
	 * @return array
	 */
	public function payments_view( $views ) {
		$base          = admin_url( 'edit.php?post_type=give_forms&page=give-payment-history' );
		$payment_count = wp_count_posts( 'give_payment' );
		$current       = isset( $_GET['status'] ) ? $_GET['status'] : '';

		$subscription_count         = '&nbsp;<span class="count">(' . $payment_count->give_subscription . ')</span>';
		$views['give_subscription'] = sprintf(
			'<a href="%s"%s>%s</a>',
			esc_url( add_query_arg( 'status', 'give_subscription', $base ) ),
			$current === 'give_subscription' ? ' class="current"' : '',
			__( 'Subscription Donation', 'give-recurring' ) . $subscription_count
		);

		$cancelled_count    = '&nbsp;<span class="count">(' . $payment_count->cancelled . ')</span>';
		$views['cancelled'] = sprintf(
			'<a href="%s"%s>%s</a>',
			esc_url( add_query_arg( 'status', 'cancelled', $base ) ),
			$current === 'cancelled' ? ' class="current"' : '',
			__( 'Cancelled', 'give-recurring' ) . $cancelled_count
		);

		return $views;
	}


	/**
	 * Set up the time period IDs and labels
	 *
	 * @since  1.0
	 * @return array
	 */
	static function periods() {
		$periods = array(
			'day'   => _x( 'Daily', 'Billing period', 'give-recurring' ),
			'week'  => _x( 'Weekly', 'Billing period', 'give-recurring' ),
			'month' => _x( 'Monthly', 'Billing period', 'give-recurring' ),
			'year'  => _x( 'Yearly', 'Billing period', 'give-recurring' ),
		);

		$periods = apply_filters( 'give_recurring_periods', $periods );

		return $periods;
	}


	/**
	 * Get Period
	 *
	 * @description Get the time period for a variable priced donation
	 *
	 * @since       1.0
	 *
	 * @param      $form_id
	 * @param      $price_id
	 *
	 * @return bool|string
	 */
	public static function get_period( $form_id, $price_id = 0 ) {

		//is this a single or multi-level form?
		if ( give_has_variable_prices( $form_id ) ) {

			$levels = maybe_unserialize( get_post_meta( $form_id, '_give_donation_levels', true ) );

			foreach ( $levels as $price ) {

				//check that this indeed the recurring price
				if ( $price_id == $price['_give_id']['level_id'] && isset( $price['_give_recurring'] ) && $price['_give_recurring'] == 'yes' && isset( $price['_give_period'] ) ) {

					return $price['_give_period'];

				}

			}

		} else {

			$period = get_post_meta( $form_id, '_give_period', true );

			if ( $period ) {
				return $period;
			}
		}


		return 'never';
	}


	/**
	 * Get Times
	 *
	 * @description Get the number of times a price ID recurs
	 *
	 * @since       1.0
	 *
	 * @param      $form_id
	 * @param      $price_id
	 *
	 * @return int
	 */
	public static function get_times( $form_id, $price_id = 0 ) {

		//is this a single or multi-level form?
		if ( give_has_variable_prices( $form_id ) ) {

			$levels = maybe_unserialize( get_post_meta( $form_id, '_give_donation_levels', true ) );

			foreach ( $levels as $price ) {

				//check that this indeed the recurring price
				if ( $price_id == $price['_give_id']['level_id'] && isset( $price['_give_recurring'] ) && $price['_give_recurring'] == 'yes' && isset( $price['_give_times'] ) ) {

					return intval( $price['_give_times'] );

				}

			}

		} else {

			$times = get_post_meta( $form_id, '_give_times', true );

			if ( $times ) {
				return $times;
			}
		}

		return 0;

	}

	/**
	 * Get the signup fee a price ID
	 *
	 * @param      $price_id
	 * @param null $form_id
	 *
	 * @return float|int
	 */
	static function get_signup_fee( $price_id, $form_id = null ) {
		global $post;

		if ( empty( $form_id ) && is_object( $post ) ) {
			$form_id = $post->ID;
		}

		$prices = get_post_meta( $form_id, '_give_variable_prices', true );

		if ( isset( $prices[ $price_id ]['signup_fee'] ) ) {
			return floatval( $prices[ $price_id ]['signup_fee'] );
		}

		return 0;
	}

	/**
	 *
	 * Get the number of times a single-price product recurs
	 *
	 * @since  1.0
	 * @return int
	 *
	 * @param $form_id
	 *
	 * @return int|mixed
	 */
	static function get_times_single( $form_id ) {

		$times = get_post_meta( $form_id, '_give_times', true );

		if ( $times ) {
			return $times;
		}

		return 0;
	}


	/**
	 * Get the signup fee of a single-price donation
	 *
	 * @param $form_id
	 *
	 * @return int|mixed
	 */
	static function get_signup_fee_single( $form_id ) {

		$signup_fee = get_post_meta( $form_id, '_give_signup_fee', true );

		if ( $signup_fee ) {
			return $signup_fee;
		}

		return 0;
	}


	/**
	 * Is Donation Form Recurring?
	 *
	 * @description Check if a donation is recurring
	 *
	 * @since       1.0
	 *
	 * @param int $form_id
	 * @param int $price_id
	 *
	 * @return bool
	 */
	public static function is_recurring( $form_id, $price_id = 0 ) {

		$is_recurring     = false;
		$levels           = maybe_unserialize( get_post_meta( $form_id, '_give_donation_levels', true ) );
		$set_or_multi     = get_post_meta( $form_id, '_give_price_option', true );
		$period           = self::get_period( $form_id, $price_id );
		$recurring_option = get_post_meta( $form_id, '_give_recurring', true );

		//Admin Choice:
		//is this a single or multi-level form?
		if ( give_has_variable_prices( $form_id ) && $set_or_multi == 'multi' && $recurring_option !== 'no' ) {

			//loop through levels and see if this is recurring
			foreach ( $levels as $price ) {

				//check that this price is indeed recurring:
				if ( $price_id == $price['_give_id']['level_id'] && isset( $price['_give_recurring'] ) && $price['_give_recurring'] == 'yes' && $period != 'never' ) {

					$is_recurring = true;

				} //checking for ANY recurring level - empty $price_id param
				elseif ( empty( $price_id ) && $price['_give_recurring'] == 'yes' ) {

					$is_recurring = true;

				}
			}
		} else if ( $recurring_option !== 'no' ) {

			//Single level donation form
			$is_recurring = true;

		}


		return $is_recurring;
	}


	/**
	 * Is Purchase (Donation) Recurring?
	 *
	 * @description Determines if a donation is a recurring donation; should be used only at time of making the donation. Use Give_Recurring_Subscriber->has_product_subscription() to determine after subscription is made if it is in fact recurring
	 *
	 * @since       1.0
	 *
	 * @param array $purchase_data
	 *
	 * @return bool
	 */
	public function is_purchase_recurring( $purchase_data ) {

		//Ensure we have proper vars set
		if ( isset( $purchase_data['post_data'] ) ) {
			$form_id  = isset( $purchase_data['post_data']['give-form-id'] ) ? $purchase_data['post_data']['give-form-id'] : 0;
			$price_id = isset( $purchase_data['post_data']['give-price-id'] ) ? $purchase_data['post_data']['give-price-id'] : 0;
		} else {
			//fallback
			$form_id  = isset( $purchase_data['form_id'] ) ? $purchase_data['form_id'] : 0;
			$price_id = isset( $purchase_data['price_id'] ) ? $purchase_data['price_id'] : 0;
		}

		//Check for donor's choice option
		$user_choice       = isset( $purchase_data['post_data']['give-recurring-period'] ) ? $purchase_data['post_data']['give-recurring-period'] : '';
		$recurring_enabled = get_post_meta( $form_id, '_give_recurring', true );

		//If not empty this is a recurring donation (checkbox is checked)
		if ( ! empty( $user_choice ) ) {
			return true;
		} elseif ( empty( $user_choice ) && $recurring_enabled == 'yes_donor' ) {
			//User only wants to give once
			return false;
		}

		//Admin choice: check fields
		if ( give_has_variable_prices( $form_id ) ) {

			//get default selected price ID
			return self::is_recurring( $form_id, $price_id );

		} else {

			//Set level
			return self::is_recurring( $form_id );

		}


	}


	/**
	 * Make sure subscription payments get included in earning reports
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public function earnings_query( $args ) {
		$args['post_status'] = array( 'publish', 'revoked', 'cancelled', 'give_subscription' );

		return $args;
	}


	/**
	 * Make sure subscription payments get included in has user purchased query
	 *
	 * @since  1.0
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public function has_purchased_query( $args ) {
		$args['status'] = array( 'publish', 'revoked', 'cancelled', 'give_subscription' );

		return $args;
	}

	/**
	 * Tells Give to include child payments in queries
	 *
	 * @since  1.0
	 *
	 * @param $query
	 *
	 * @return void
	 */
	public function enable_child_payments( $query ) {
		$query->__set( 'post_parent', null );
	}

	/**
	 * Instruct Give PDF Receipts that subscription payments are eligible for Invoices
	 *
	 * @since  1.0
	 * @return bool
	 */
	public function is_invoice_allowed( $ret, $payment_id ) {

		$payment_status = get_post_status( $payment_id );

		if ( 'give_subscription' == $payment_status ) {

			$parent = get_post_field( 'post_parent', $payment_id );
			if ( give_is_payment_complete( $parent ) ) {
				$ret = true;
			}

		}

		return $ret;
	}

	/**
	 * Get User ID from customer recurring ID
	 *
	 * @param string $recurring_id
	 *
	 * @return int|null|string
	 */
	public function get_user_id_by_recurring_customer_id( $recurring_id = '' ) {

		global $wpdb;

		$user_id = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = '_give_recurring_id' AND meta_value = %s LIMIT 1", $recurring_id ) );

		if ( $user_id != null ) {
			return $user_id;
		}

		return 0;

	}

	/**
	 * Maybe Show Register and Login Forms
	 *
	 * @description
	 *
	 * @param $form_id
	 *
	 * @return bool
	 */
	public function maybe_show_register_login_forms( $form_id ) {

		//If user is logged in then no worries, move on
		if ( is_user_logged_in() ) {
			return false;
		} elseif ( self::is_recurring( $form_id ) ) {

			add_filter( 'give_logged_in_only', array( $this, 'require_login_forms_filter' ), 10, 2 );
			add_filter( 'give_show_register_form', array( $this, 'show_register_form' ), 1, 2 );

		}

		return false;

	}

	/**
	 * Require Login Forms Filter
	 *
	 * @description: Hides the "(optional)" content from the create and login account fieldsets
	 *
	 * @return bool
	 */
	public function require_login_forms_filter( $value, $form_id ) {

		$email_access = give_get_option( 'email_access' );

		if ( give_is_form_recurring( $form_id ) && empty( $email_access ) ) {
			//Update form's logged in only meta to ensure no login is required
			update_post_meta( $form_id, '_give_logged_in_only', '' );

			return true;
		} else {
			return $value;
		}

	}

	/**
	 * Show Registration Form
	 *
	 * @description: Filter the give_show_register_form to return both login and registration fields for recurring donations if email access not enabled; if enabled, then it will respect donation form's settings
	 *
	 * @param $value
	 * @param $form_id
	 *
	 * @return string
	 */
	public function show_register_form( $value, $form_id ) {

		$email_access = give_get_option( 'email_access' );

		if ( give_is_form_recurring( $form_id ) && empty( $email_access ) ) {
			return 'both';
		} else {
			return $value;
		}

	}

	/**
	 * Does Subscriber have email access
	 *
	 * @since 1.1
	 *
	 * @return bool
	 */
	public function subscriber_has_email_access() {

		//Initialize because this is hooked upon init
		if ( class_exists( 'Give_Email_Access' ) ) {
			$email_access = new Give_Email_Access();
			$email_access->init();
			$email_access_option  = give_get_option( 'email_access' );
			$email_access_granted = ( ! empty( $email_access->token_exists ) && $email_access_option == 'on' );
		} else {
			$email_access_granted = false;
		}

		return $email_access_granted;
	}

	/**
	 * Plugins row action links
	 *
	 * @param array $links already defined action links
	 * @param string $file plugin file path and name being processed
	 *
	 * @return array $links
	 */
	function plugin_action_links( $links, $file ) {

		$settings_link = '<a href="' . admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=recurring' ) . '">' . esc_html__( 'Settings', 'give-recurring' ) . '</a>';

		if ( $file == GIVE_RECURRING_PLUGIN_BASENAME ) {
			array_unshift( $links, $settings_link );
		}

		return $links;

	}

}

/**
 * The main function responsible for returning the one true Give_Recurring Instance
 * to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $recurring = Give_Recurring(); ?>
 *
 * @since v1.0
 *
 * @return mixed one true Give_Recurring Instance
 */

function Give_Recurring() {

	if ( ! class_exists( 'Give' ) ) {
		return false;
	}

	return Give_Recurring::instance();
}

add_action( 'init', 'Give_Recurring', 1 );


/**
 * Install hook
 *
 * @since 1.0
 */
function give_recurring_install() {

	Give_Recurring();

	$db = new Give_Subscriptions_DB;
	@$db->create_table();

	add_role( 'give_subscriber', __( 'Give Subscriber', 'give-recurring' ), array( 'read' ) );

	add_option( 'give_recurring_version', GIVE_RECURRING_VERSION, '', false );

}

register_activation_hook( __FILE__, 'give_recurring_install' );


/**
 * Give Authorize.net Activation Banner
 *
 * @description: Includes and initializes the activation banner class; only runs in WP admin
 * @hook       admin_init
 */
function give_recurring_activation_banner() {

	//Check for Give
	if ( defined( 'GIVE_PLUGIN_FILE' ) ) {
		$give_plugin_basename = plugin_basename( GIVE_PLUGIN_FILE );
		$is_give_active       = is_plugin_active( $give_plugin_basename );
	} else {
		$is_give_active = false;
	}

	//Check to see if Give is activated, if it isn't deactivate and show a banner
	if ( is_admin() && current_user_can( 'activate_plugins' ) && ! $is_give_active ) {

		add_action( 'admin_notices', 'give_recurring_child_plugin_notice' );

		//Don't let this plugin activate
		deactivate_plugins( plugin_basename( __FILE__ ) );

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		return false;

	}

	//Check minimum Give version for Recurring
	//@TODO: Update minimum to 1.3.* for release
	if ( defined( 'GIVE_VERSION' ) && version_compare( GIVE_VERSION, '1.3.4', '<' ) ) {

		add_action( 'admin_notices', 'give_recurring_min_version_notice' );

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
		'name'              => __( 'Recurring Donations', 'give-recurring' ),
		//name of the Add-on
		'version'           => GIVE_RECURRING_VERSION,
		//The most current version
		'documentation_url' => 'https://givewp.com/documentation/add-ons/recurring-donations/',
		'support_url'       => 'https://givewp.com/support/',
		//Location of Add-on settings page, leave blank to hide
		'testing'           => false,
		//Never leave as "true" in production!!!
	);

	new Give_Addon_Activation_Banner( $args );

	return false;

}

add_action( 'admin_init', 'give_recurring_activation_banner' );

/**
 * Notice for No Core Activation
 */
function give_recurring_child_plugin_notice() {

	echo '<div class="error"><p>' . sprintf( __( '%sActivation Error:%s We noticed Give is not active. Please activate Give in order to use the Recurring Donations Add-on.', 'give-recurring' ), '<strong>', '</strong>' ) . '</p></div>';
}

/**
 * Notice for No Core Activation
 */
function give_recurring_min_version_notice() {

	echo '<div class="error"><p>' . sprintf( __( '%sActivation Error:%s We noticed Give is not up to date. Please update Give in order to use the Recurring Donations Add-on.', 'give-recurring' ), '<strong>', '</strong>' ) . '</p></div>';

}