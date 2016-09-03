<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Recurring_Gateway
 */
class Give_Recurring_Gateway {

	public $id;
	public $subscriptions = array();
	public $purchase_data = array();
	public $offsite = false;
	public $user_id = 0;
	public $payment_id = 0;

	/**
	 * Get things started
	 *
	 * @access      public
	 * @since       1.0
	 */
	public function __construct() {

		$this->init();

		add_action( 'give_checkout_error_checks', array( $this, 'checkout_errors' ), 0, 2 );
		add_action( 'give_gateway_' . $this->id, array( $this, 'process_checkout' ), 0 );
		add_action( 'init', array( $this, 'process_webhooks' ), 9 );
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ), 10 );
		add_action( 'give_cancel_subscription', array( $this, 'process_cancellation' ) );
		add_filter( 'give_subscription_can_cancel_' . $this->id . '_subscription', array(
			$this,
			'can_cancel'
		), 10, 2 );
	}

	/**
	 * Setup gateway ID and possibly load API libraries
	 *
	 * @access      public
	 * @since       1.0
	 * @return      void
	 */
	public function init() {

		$this->id = '';

	}

	/**
	 * Enqueue necessary scripts.
	 *
	 * @access      public
	 * @since       1.0
	 * @return      void
	 */
	public function scripts() {
	}

	/**
	 * Validate checkout fields
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

		/*

		if( true ) {
			give_set_error( 'error_id_here', __( 'Error message here', 'give-recurring' ) );
		}

		*/

	}

	/**
	 * Creates subscription payment profiles and sets the IDs so they can be stored
	 *
	 * @access      public
	 * @since       1.0
	 */
	public function create_payment_profiles() {

		// Creates a payment profile and then sets the profile ID
		$this->subscriptions['profile_id'] = '1234';


	}

	/**
	 * Finishes the signup process by redirecting to the success page or to an off-site payment page
	 *
	 * @access      public
	 * @since       1.0
	 * @return      void
	 */
	public function complete_signup() {

		wp_redirect( give_get_success_page_url() );
		exit;

	}

	/**
	 * Processes webhooks from the payment processor
	 *
	 * @access      public
	 * @since       1.0
	 * @return      void
	 */
	public function process_webhooks() {

		// set webhook URL to: home_url( 'index.php?give-listener=' . $this->id );

		if ( empty( $_GET['give-listener'] ) || $this->id !== $_GET['give-listener'] ) {
			return;
		}

		// process webhooks here

	}

	/****************************************************************
	 * Below methods should not be extended except in rare cases
	 ***************************************************************/

	/**
	 *
	 * Processes the recurring donation form and sends sets up the subscription data for hand-off to the gateway
	 *
	 * @param $purchase_data
	 *
	 * @access      public
	 * @since       1.0
	 * @return      void
	 *
	 */
	public function process_checkout( $purchase_data ) {

		if ( ! Give_Recurring()->is_purchase_recurring( $purchase_data ) ) {
			return; // Not a recurring purchase so bail
		}

		if ( ! wp_verify_nonce( $purchase_data['gateway_nonce'], 'give-gateway' ) ) {
			wp_die( __( 'Nonce verification has failed', 'give' ), __( 'Error', 'give-recurring' ), array( 'response' => 403 ) );
		}

		$email_access_option = give_get_option( 'email_access' );

		//Sanity check: Ensure either account creation when email access is not enabled
		if ( $purchase_data['user_info']['id'] < 1 && $email_access_option !== 'on' ) {
			give_set_error( 'give_recurring_logged_in', __( 'You must log in or create an account for subscription donations.', 'give-recurring' ) );
		}

		// Initial validation
		do_action( 'give_recurring_process_checkout', $purchase_data, $this );

		$errors = give_get_errors();

		if ( $errors ) {

			give_send_back_to_checkout( '?payment-mode=' . $this->id );

		}

		$this->purchase_data = $purchase_data;
		$this->user_id       = $purchase_data['user_info']['id'];

		$this->subscriptions = array(
			'id'               => $this->purchase_data['post_data']['give-form-id'],
			'name'             => $this->purchase_data['post_data']['give-form-title'],
			'price_id'         => isset( $this->purchase_data['post_data']['give-price-id'] ) ? $this->purchase_data['post_data']['give-price-id'] : '',
			'initial_amount'   => $this->purchase_data['price'], //add fee here in future
			'recurring_amount' => $this->purchase_data['price'],
			'period'           => $this->purchase_data['period'],
			'frequency'        => 1,
			// Hard-coded to 1 for now but here in case we offer it later. Example: charge every 3 weeks
			'bill_times'       => $this->purchase_data['times'],
			'signup_fee'       => '', //Coming soon
			'profile_id'       => '',
			// Profile ID for this subscription - This is set by the payment gateway
		);

		// Create subscription payment profiles in the gateway
		$this->create_payment_profiles();

		// Look for errors after trying to create payment profiles
		$errors = give_get_errors();

		if ( $errors ) {
			give_send_back_to_checkout( '?payment-mode=' . $this->id );
		}

		// Record the subscriptions and finish up
		$this->record_signup();

		// Finish the signup process. Gateways can perform off-site redirects here if necessary
		$this->complete_signup();

	}

	/**
	 * Records purchased subscriptions in the database and creates an give_payment record
	 *
	 * @access      public
	 * @since       1.0
	 * @return      void
	 */
	public function record_signup() {

		$payment_data = array(
			'price'           => $this->purchase_data['price'],
			'give_form_title' => $this->purchase_data['post_data']['give-form-title'],
			'give_form_id'    => intval( $this->purchase_data['post_data']['give-form-id'] ),
			'date'            => $this->purchase_data['date'],
			'user_email'      => $this->purchase_data['user_email'],
			'purchase_key'    => $this->purchase_data['purchase_key'],
			'currency'        => give_get_currency(),
			'user_info'       => $this->purchase_data['user_info'],
			'status'          => 'pending'
		);

		// Record the pending payment
		$this->payment_id = give_insert_payment( $payment_data );

		if ( ! $this->offsite ) {
			// Offsite payments get verified via a webhook so are completed in webhooks()
			give_update_payment_status( $this->payment_id, 'publish' );
		}

		// Set subscription_payment
		give_update_payment_meta( $this->payment_id, '_give_subscription_payment', true );

		// Now create the subscription record
		$customer_id = give_get_payment_customer_id( $this->payment_id );
		$subscriber  = new Give_Recurring_Subscriber( $customer_id );
		$args        = array(
			'product_id'        => $this->subscriptions['id'],
			'parent_payment_id' => $this->payment_id,
			'status'            => $this->offsite ? 'pending' : 'active',
			'period'            => $this->subscriptions['period'],
			'initial_amount'    => $this->subscriptions['initial_amount'],
			'recurring_amount'  => $this->subscriptions['recurring_amount'],
			'bill_times'        => $this->subscriptions['bill_times'],
			'expiration'        => $subscriber->get_new_expiration( $this->subscriptions['id'], $this->subscriptions['price_id'] ),
			'profile_id'        => $this->subscriptions['profile_id'],
		);

		$subscriber->add_subscription( $args );


	}

	/**
	 * Triggers the validate_fields() method for the gateway during checkout submission
	 *
	 * This should not be extended
	 *
	 * @access      public
	 * @since       1.0
	 * @return      void
	 */
	public function checkout_errors( $data, $posted ) {

		if ( $this->id !== $posted['give-gateway'] ) {
			return;
		}

		$this->validate_fields( $data, $posted );

	}

	/**
	 * Cancels a subscription
	 *
	 * @access      public
	 *
	 * @param $subscription
	 * @param $valid
	 */
	public function cancel( $subscription, $valid ) {
	}

	/**
	 * Can Cancel
	 *
	 * @param $ret
	 * @param $subscription
	 *
	 * @return mixed
	 */
	public function can_cancel( $ret, $subscription ) {
		return $ret;
	}


	/**
	 * Handles cancellation requests for a subscription
	 *
	 * This should not be extended
	 *
	 * @access      public
	 * @since       1.0
	 * @return      void
	 *
	 * @param $data
	 */
	public function process_cancellation( $data ) {

		if ( empty( $data['sub_id'] ) ) {
			return;
		}

		//Sanity check: If subscriber is not logged in and email access is not enabled nor active
		if ( ! is_user_logged_in() && Give_Recurring()->subscriber_has_email_access() == false ) {
			return;
		}

		if ( ! wp_verify_nonce( $data['_wpnonce'], 'give-recurring-cancel' ) ) {

			wp_die( __( 'Error', 'give-recurring' ), __( 'Nonce verification failed', 'give-recurring' ), array( 'response' => 403 ) );
		}

		$data['sub_id'] = absint( $data['sub_id'] );
		$subscription   = new Give_Subscription( $data['sub_id'] );

		if ( ! $subscription->can_cancel() ) {
			//@TODO: Need a better way to present errors than wp_die
			wp_die( __( 'Error', 'give-recurring' ), __( 'This subscription cannot be cancelled', 'give-recurring' ), array( 'response' => 403 ) );
		}

		try {

			do_action( 'give_recurring_cancel_' . $subscription->gateway . '_subscription', $subscription, true );

			$subscription->cancel();

			if ( is_admin() ) {

				wp_redirect( admin_url( 'edit.php?post_type=give_forms&page=give-subscriptions&give-message=cancelled&id=' . $subscription->id ) );
				exit;

			} else {

				wp_redirect( remove_query_arg( array(
					'_wpnonce',
					'give_action',
					'sub_id'
				), add_query_arg( array( 'give-message' => 'cancelled' ) ) ) );
				exit;

			}

		} catch ( Exception $e ) {
			wp_die( __( 'Error', 'give-recurring' ), $e->getMessage(), array( 'response' => 403 ) );
		}

	}

}