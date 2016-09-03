<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Give_Recurring_PayPal extends Give_Recurring_Gateway {

	private $api_endpoint;
	protected $username;
	protected $password;
	protected $signature;

	/**
	 * Get things started
	 *
	 * @access      public
	 * @since       1.0
	 * @return      void
	 */
	public function init() {

		$this->id = 'paypal';

		$this->offsite = true;

		if ( give_is_test_mode() ) {
			$this->api_endpoint = 'https://api-3t.sandbox.paypal.com/nvp';
		} else {
			$this->api_endpoint = 'https://api-3t.paypal.com/nvp';
		}

		$creds = $this->get_paypal_standard_api_credentials();

		$this->username  = $creds['username'];
		$this->password  = $creds['password'];
		$this->signature = $creds['signature'];


		// Process PayPal subscription sign ups
		add_action( 'give_paypal_subscr_signup', array( $this, 'process_paypal_subscr_signup' ) );

		// Process PayPal subscription payments
		add_action( 'give_paypal_subscr_payment', array( $this, 'process_paypal_subscr_payment' ) );

		// Process PayPal subscription cancellations
		add_action( 'give_paypal_subscr_cancel', array( $this, 'process_paypal_subscr_cancel' ) );

		// Process PayPal subscription end of term notices
		add_action( 'give_paypal_subscr_eot', array( $this, 'process_paypal_subscr_eot' ) );

		//Validate PayPal Times Serverside
		add_action( 'save_post', array( $this, 'validate_paypal_recurring_times' ) );

		//Manual cancellation action
		add_action( 'give_recurring_cancel_' . $this->id . '_subscription', array(
			$this,
			'cancel_paypal_standard'
		), 10, 2 );

		//Add settings
		add_filter( 'give_settings_gateways', array( $this, 'add_settings' ) );

	}

	/**
	 * Retrieve PayPal API credentials
	 *
	 * @access      public
	 * @since       1.0
	 */
	public function get_paypal_standard_api_credentials() {

		$prefix = 'live_';

		if ( give_is_test_mode() ) {
			$prefix = 'test_';
		}

		$creds = array(
			'username'  => give_get_option( $prefix . 'paypal_standard_api_username' ),
			'password'  => give_get_option( $prefix . 'paypal_standard_api_password' ),
			'signature' => give_get_option( $prefix . 'paypal_standard_api_signature' )
		);

		return apply_filters( 'give_recurring_get_paypal_standard_api_credentials', $creds );
	}

	/**
	 * Create temporary profile IDs that we can reference during IPN processing
	 *
	 * @access      public
	 * @since       1.0
	 * @return      void
	 */
	public function create_payment_profiles() {

		// This is a temporary ID used to look it up later during IPN processing
		$this->subscriptions['profile_id'] = 'paypal-' . $this->purchase_data['purchase_key'];

	}

	/**
	 * Validate Fields
	 *
	 * Initial field validation before ever creating profiles or customers
	 *
	 * @access      public
	 * @since       1.0
	 *
	 * @param $data
	 * @param $posted
	 *
	 * @return      void
	 */
	public function validate_fields( $data, $posted ) {

		if ( ! give_get_option( 'paypal_email', false ) ) {

			give_set_error( 'give_recurring_paypal_email_missing', __( 'Please enter your PayPal email address.', 'give-recurring' ) );

		}

	}

	/**
	 * Setup PayPal arguments and redirect to PayPal
	 *
	 * @see         : https://developer.paypal.com/docs/classic/paypal-payments-standard/integration-guide/Appx_websitestandard_htmlvariables/
	 *
	 * @access      public
	 * @since       1.0
	 * @return      void
	 */
	public function complete_signup() {

		// Get the success url
		$return_url = add_query_arg( array(
			'payment-confirmation' => 'paypal',
			'payment-id'           => $this->payment_id
		), give_get_success_page_uri() );

		// Get the PayPal redirect uri
		$paypal_redirect = trailingslashit( give_get_paypal_redirect() ) . '?';

		// Setup PayPal arguments
		$paypal_args = array(
			'business'      => give_get_option( 'paypal_email', false ),
			'email'         => $this->purchase_data['user_email'],
			'first_name'    => $this->purchase_data['user_info']['first_name'],
			'last_name'     => $this->purchase_data['user_info']['last_name'],
			'invoice'       => $this->purchase_data['purchase_key'],
			'no_shipping'   => '1',
			'shipping'      => '0',
			'no_note'       => '1',
			'currency_code' => give_get_currency(),
			'charset'       => get_bloginfo( 'charset' ),
			'custom'        => $this->payment_id,
			'rm'            => '2',
			'return'        => $return_url,
			'cancel_return' => give_get_failed_transaction_uri( '?payment-id=' . $this->payment_id ),
			'notify_url'    => add_query_arg( 'give-listener', 'IPN', home_url( 'index.php' ) ),
			'page_style'    => give_get_paypal_page_style(),
			'cbt'           => get_bloginfo( 'name' ),
			'bn'            => 'givewp_SP',
			'sra'           => '1',
			'src'           => '1',
			'cmd'           => '_xclick-subscriptions'
		);

		if ( ! empty( $this->purchase_data['user_info']['address'] ) ) {
			$paypal_args['address1'] = $this->purchase_data['user_info']['address']['line1'];
			$paypal_args['address2'] = $this->purchase_data['user_info']['address']['line2'];
			$paypal_args['city']     = $this->purchase_data['user_info']['address']['city'];
			$paypal_args['country']  = $this->purchase_data['user_info']['address']['country'];
		}

		// Set the recurring amount
		$paypal_args['a3'] = $this->subscriptions['recurring_amount'];

		// Set purchase description
		$paypal_args['item_name'] = give_recurring_subscription_title( $this->purchase_data );

		// Set the recurring period
		switch ( $this->subscriptions['period'] ) {
			case 'day' :
				$paypal_args['t3'] = 'D';
				break;
			case 'week' :
				$paypal_args['t3'] = 'W';
				break;
			case 'month' :
				$paypal_args['t3'] = 'M';
				break;
			case 'year' :
				$paypal_args['t3'] = 'Y';
				break;
		}

		// One period unit (every week, every month, etc)
		$paypal_args['p3'] = $this->subscriptions['frequency'];
		$paypal_args['p1'] = $this->subscriptions['frequency'];

		if ( $this->subscriptions['bill_times'] > 1 ) {

			// Make sure it's not over the max of 52
			$this->subscriptions['bill_times'] = $this->subscriptions['bill_times'] <= 52 ? absint( $this->subscriptions['bill_times'] ) : 52;

			$paypal_args['srt'] = $this->subscriptions['bill_times'];

		}


		$paypal_args = apply_filters( 'give_recurring_paypal_args', $paypal_args, $this->purchase_data );

		// Build query
		$paypal_redirect .= http_build_query( $paypal_args );

		// Fix for some sites that encode the entities
		$paypal_redirect = str_replace( '&amp;', '&', $paypal_redirect );

		// Redirect to PayPal
		wp_redirect( $paypal_redirect );

		exit;
	}

	/**
	 * Processes the "signup" IPN notice
	 *
	 * @param $ipn_data
	 *
	 * @return void
	 *
	 */
	public function process_paypal_subscr_signup( $ipn_data ) {

		$parent_payment_id = absint( $ipn_data['custom'] );

		if ( empty( $parent_payment_id ) ) {
			return;
		}

		//Check for payment
		if ( ! give_get_payment_by( 'id', $parent_payment_id ) ) {
			return;
		}

		// Record PayPal subscription ID
		if ( isset( $ipn_data['subscr_id'] ) ) {
			give_insert_payment_note( $parent_payment_id, sprintf( __( 'PayPal Subscription ID: %s', 'give-recurring' ), $ipn_data['subscr_id'] ) );
		}

		// Retrieve pending subscription from database and update it's status to active and set proper profile ID
		$subscription = new Give_Subscription( 'paypal-' . $ipn_data['invoice'], true );
		$subscription->update( array( 'profile_id' => $ipn_data['subscr_id'], 'status' => 'active' ) );
	}


	/**
	 * Processes the recurring payments as they come in
	 *
	 * @since  1.0
	 * @return mixed|void
	 */
	public function process_paypal_subscr_payment( $ipn_data ) {

		//Sanity check
		if ( ! isset( $ipn_data['txn_type'] ) || 'subscr_payment' !== $ipn_data['txn_type'] ) {
			return false;
		}

		$parent_payment_id = absint( $ipn_data['custom'] );

		//must pass a parent payment ID
		if ( empty( $parent_payment_id ) ) {
			return false;
		}

		$payment = give_get_payment_by( 'id', $parent_payment_id );

		//Is this the first parent payment?
		if ( ! $payment || 'pending' == $payment->post_status ) {
			//Set transaction ID
			give_set_payment_transaction_id( $parent_payment_id, $ipn_data['txn_id'] );
			//Update it as published
			give_update_payment_status( $parent_payment_id, 'publish' );

			//No need to do any of the rest here; that would result in a blank subscription payment, when this is the 1st payment of the subscription ;)
			return false;
		}

		$subscription = new Give_Subscription( $ipn_data['subscr_id'], true );

		//We need a subscription to continue
		if ( 0 === $subscription->id ) {
			return false;
		}

		// Is this payment already recorded?
		if ( give_get_purchase_id_by_transaction_id( $ipn_data['txn_id'] ) ) {
			return false; // Payment already recorded
		}

		$currency_code = strtolower( $ipn_data['mc_currency'] );

		// verify details
		if ( $currency_code != strtolower( give_get_currency() ) ) {
			// the currency code is invalid
			give_record_gateway_error( __( 'IPN Error', 'give-recurring' ), sprintf( __( 'Invalid currency in IPN response. IPN data: ', 'give-recurring' ), json_encode( $ipn_data ) ) );

			return false;
		}

		$args = array(
			'amount'         => $ipn_data['mc_gross'],
			'transaction_id' => $ipn_data['txn_id']
		);

		$subscription->add_payment( $args );
		$subscription->renew();

	}

	/**
	 * Processes the "end of term (eot)" IPN notice
	 *
	 * @since  1.0
	 * @return void
	 */
	public function process_paypal_subscr_eot( $ipn_data ) {

		if ( ! isset( $ipn_data['txn_type'] ) || 'subscr_eot' !== $ipn_data['txn_type'] ) {
			return;
		}

		$subscription = new Give_Subscription( $ipn_data['subscr_id'], true );
		$subscription->complete();

	}


	/**
	 * Processes the "cancel" IPN notice
	 *
	 * @since  1.0
	 *
	 * @param $ipn_data
	 */
	public function process_paypal_subscr_cancel( $ipn_data ) {

		if ( ! isset( $ipn_data['txn_type'] ) || 'subscr_cancel' !== $ipn_data['txn_type'] ) {
			return;
		}

		$subscription = new Give_Subscription( $ipn_data['subscr_id'], true );
		$subscription->cancel();

	}


	/**
	 * Can Cancel
	 *
	 * @param $ret
	 * @param $subscription
	 *
	 * @return bool
	 */
	public function can_cancel( $ret, $subscription ) {

		//Check whether the user has added the necessary API keys to cancel
		if ( empty( $this->username ) || empty( $this->password ) || empty( $this->signature ) ) {
			return false;
		}

		return $subscription->gateway === $this->id && ! empty( $subscription->profile_id );
	}


	/**
	 * Cancel PayPal Subscription
	 *
	 * @description: Performs an Express Checkout NVP API operation as passed in $api_method. Although the PayPal Standard API provides no facility for cancelling a subscription, the PayPal; Express Checkout NVP API can be used.
	 *
	 * @param $subscription
	 * @param $valid
	 *
	 * @return bool
	 */
	public function cancel_paypal_standard( $subscription, $valid ) {

		if ( empty( $valid ) ) {
			return false;
		}

		//Use PayPal Pro gateway method to handle the cancellation
		$paypalpro = new Give_Recurring_PayPal_Website_Payments_Pro();
		$paypalpro->init();

		//Cancel with PayPal
		$args = array(
			'USER'      => $this->username,
			'PWD'       => $this->password,
			'SIGNATURE' => $this->signature,
			'METHOD'    => 'ManageRecurringPaymentsProfileStatus',
			'PROFILEID' => $subscription->profile_id,
			'VERSION'   => '121',
			'ACTION'    => 'Cancel',
		);

		$response = $paypalpro->make_paypal_api_request( $args );
		$ret      = false;

		if ( false !== $response ) {

			switch ( strtolower( $response['ACK'] ) ) {
				case 'success' :
					$ret = true;
					break;
				case 'failure' :
					$error = '<p>' . __( 'PayPal subscription cancellation failed.', 'give_recurring' ) . '</p>';
					if ( isset( $response['L_LONGMESSAGE0'] ) && ! empty( $response['L_LONGMESSAGE0'] ) ) {
						$error .= '<p>' . __( 'Error message:', 'give_recurring' ) . ' ' . $response['L_LONGMESSAGE0'] . '</p>';
						$error .= '<p>' . __( 'Error code:', 'give_recurring' ) . ' ' . $response['L_ERRORCODE0'] . '</p>';
					}
					wp_die( __( 'Error: ', 'give-recurring' ) . $error, __( 'PayPal Cancellation Error', 'give_recurring' ), array( 'response' => 403 ) );

					break;
			}
		}

		return $ret;

	}


	/**
	 * Register Recurring PayPal Standard Additional settings
	 *
	 * @description  Adds the PayPal Standard settings to the Payment Gateways section (CMB2) that are required in order to cancel subscriptions on site using PayPal Standard
	 *
	 * @access       public
	 * @since        1.0
	 * @return      array
	 */
	public function add_settings( $settings ) {

		$give_pp_standard_settings = array(
			array(
				'id'   => 'paypal_standard_recurring_description',
				'name' => '&nbsp;',
				'desc' => '<p class="give-recurring-description give-paypal-description">' . sprintf( __( 'The following API keys are required in order to process PayPal Standard subscriptions cancellations on site. %1$sClick here%2$s to learn more about PayPal Standard\'s recurring capabilities and requirements.', 'give-recurring' ), '<a href="https://givewp.com/documentation/add-ons/recurring-donations/supported-payment-gateways/paypal-standard/" target="_blank" class="new-window">', '</a>' ) . '</p>',
				'type' => 'give_description',
			),
			array(
				'id'   => 'live_paypal_standard_api_username',
				'name' => __( 'Live API Username', 'give-recurring' ),
				'desc' => __( 'Enter your live API username', 'give-recurring' ),
				'type' => 'text',
				'size' => 'regular'
			),
			array(
				'id'   => 'live_paypal_standard_api_password',
				'name' => __( 'Live API Password', 'give-recurring' ),
				'desc' => __( 'Enter your live API password', 'give-recurring' ),
				'type' => 'text',
			),
			array(
				'id'   => 'live_paypal_standard_api_signature',
				'name' => __( 'Live API Signature', 'give-recurring' ),
				'desc' => __( 'Enter your live API signature', 'give-recurring' ),
				'type' => 'text',
			),
			array(
				'id'   => 'test_paypal_standard_api_username',
				'name' => __( 'Test API Username', 'give-recurring' ),
				'desc' => __( 'Enter your test API username', 'give-recurring' ),
				'type' => 'text',
			),
			array(
				'id'   => 'test_paypal_standard_api_password',
				'name' => __( 'Test API Password', 'give-recurring' ),
				'desc' => __( 'Enter your test API password', 'give-recurring' ),
				'type' => 'text',
			),
			array(
				'id'   => 'test_paypal_standard_api_signature',
				'name' => __( 'Test API Signature', 'give-recurring' ),
				'desc' => __( 'Enter your test API signature', 'give-recurring' ),
				'type' => 'text',
			)
		);

		return give_settings_array_insert(
			$settings,
			'paypal_page_style',
			$give_pp_standard_settings
		);

	}

	/**
	 * Validate PayPal Recurring Donation
	 *
	 * @description: Additional server side validation for PayPal Standard recurring
	 *
	 * @param int $form_id
	 *
	 * @return mixed
	 */
	function validate_paypal_recurring_times( $form_id = 0 ) {

		global $post;
		$recurring_option = isset( $_REQUEST['_give_recurring'] ) ? $_REQUEST['_give_recurring'] : 'no';
		$set_or_multi     = isset( $_REQUEST['_give_price_option'] ) ? $_REQUEST['_give_price_option'] : '';

		//Sanity Checks
		if ( ! class_exists( 'Give_Recurring' ) ) {
			return $form_id;
		}
		if ( $recurring_option == 'no' ) {
			return $form_id;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
			return $form_id;
		}
		if ( isset( $post->post_type ) && $post->post_type == 'revision' ) {
			return $form_id;
		}
		if ( ! isset( $post->post_type ) || $post->post_type != 'give_forms' ) {
			return $form_id;
		}
		if ( ! current_user_can( 'edit_give_forms', $form_id ) ) {
			return $form_id;
		}

		//Is this gateway active
		if ( ! give_is_gateway_active( $this->id ) ) {
			return $form_id;
		}

		if ( $set_or_multi === 'multi' && $recurring_option == 'yes_admin' ) {

			$prices = isset( $_REQUEST['_give_donation_levels'] ) ? $_REQUEST['_give_donation_levels'] : array( '' );
			foreach ( $prices as $price_id => $price ) {
				$time = isset( $price['_give_times'] ) ? $price['_give_times'] : 0;
				//PayPal download allow times of "1" or above "52"
				//https://developer.paypal.com/docs/classic/paypal-payments-standard/integration-guide/Appx_websitestandard_htmlvariables/
				if ( $time == 1 || $time >= 53 ) {
					wp_die( __( 'PayPal Standard requires recurring times to be more than 1. Please specify a time with a minimum value of 2 and a maximum value of 52.', 'give-recurring' ), __( 'Error', 'give-recurring' ), array( 'response' => 400 ) );
				}

			}

		} else {
			if ( Give_Recurring()->is_recurring( $form_id ) ) {

				$time = isset( $_REQUEST['_give_times'] ) ? $_REQUEST['_give_times'] : 0;

				if ( $time == 1 || $time >= 53 ) {
					wp_die( __( 'PayPal Standard requires recurring times to be more than 1. Please specify a time with a minimum value of 2 and a maximum value of 52.', 'give-recurring' ), __( 'Error', 'give-recurring' ), array( 'response' => 400 ) );
				}
			}
		}

		return $form_id;
	}

}

new Give_Recurring_PayPal;