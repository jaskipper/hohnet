<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Give_Recurring_Stripe extends Give_Recurring_Gateway {

	private $secret_key;
	private $public_key;

	/**
	 * Get Stripe Started
	 */
	public function init() {

		$this->id = 'stripe';

		if ( ! class_exists( 'Stripe' ) && defined( 'GIVE_STRIPE_PLUGIN_DIR' ) ) {
			require_once GIVE_STRIPE_PLUGIN_DIR . '/Stripe/Stripe.php';
		}

		if ( give_is_test_mode() ) {
			$prefix = 'test_';
		} else {
			$prefix = 'live_';
		}

		$this->secret_key = give_get_option( $prefix . 'secret_key', '' );
		$this->public_key = give_get_option( $prefix . 'publishable_key', '' );

		if ( class_exists( 'Stripe' ) ) {

			Stripe::setApiKey( $this->secret_key );

		}

		add_action( 'give_recurring_cancel_stripe_subscription', array( $this, 'cancel_stripe_subscription' ), 10, 2 );

		//		add_action('give_subscription_post_renew', array($this, ))

	}

	/**
	 * Initial field validation before ever creating profiles or customers
	 *
	 * @access      public
	 * @since       1.0
	 * @return      void
	 */
	public function validate_fields( $data, $posted ) {

		if ( ! class_exists( 'Stripe' ) ) {

			give_set_error( 'give_recurring_stripe_missing', __( 'The Stripe payment gateway does not appear to be activated.', 'give-recurring' ) );
		}

		if ( empty( $this->public_key ) ) {

			give_set_error( 'give_recurring_stripe_public_missing', __( 'The Stripe publishable key must be entered in settings.', 'give-recurring' ) );
		}

		if ( empty( $this->secret_key ) ) {

			give_set_error( 'give_recurring_stripe_public_missing', __( 'The Stripe secret key must be entered in settings.', 'give-recurring' ) );
		}
	}

	/**
	 * Create Payment Profiles
	 *
	 * @description: Setup customers and plans in Stripe for the signup
	 *
	 * @return bool|Stripe_Subscription
	 */
	public function create_payment_profiles() {

		$source  = ! empty( $_POST['give_stripe_token'] ) ? $_POST['give_stripe_token'] : $this->generate_source_dictionary();
		$email   = $this->purchase_data['user_email'];
		$plan_id = $this->get_or_create_stripe_plan( $this->subscriptions );

		if ( false === $plan_id ) {

			give_set_error( 'give_recurring_stripe_plan_error', __( 'The subscription plan in Stripe could not be created, please try again.', 'give-recurring' ) );

			return false;
		}

		$stripe_customer = $this->get_or_create_stripe_customer( $this->user_id, $email );

		if ( false === $stripe_customer || empty( $stripe_customer ) ) {

			give_set_error( 'give_recurring_stripe_customer_error', __( 'The customer account in Stripe could not be created, please try again.', 'give-recurring' ) );

			return false;
		}

		return $this->subscribe_customer_to_plan( $stripe_customer, $source, $plan_id );
	}

	/**
	 * Subscribes a Stripe Customer to a plan
	 *
	 * @param  Stripe_Customer $stripe_customer
	 * @param  string|array $source
	 * @param  string $plan_id
	 *
	 * @return bool|Stripe_Subscription
	 */
	public function subscribe_customer_to_plan( $stripe_customer, $source, $plan_id ) {

		if ( $stripe_customer instanceof Stripe_Customer ) {

			try {

				$args = array(
					'source' => $source,
					'plan'   => $plan_id
				);

				$subscription                      = $stripe_customer->subscriptions->create( $args );
				$this->subscriptions['profile_id'] = $subscription->id;

				return $subscription;

			} catch ( Exception $e ) {

				give_set_error( 'give_recurring_stripe_error', $e->getMessage() );

				return false;
			}
		}

		return false;
	}

	/**
	 * Generates source dictonary, used for testing purpose only
	 *
	 * @param  array $card_info
	 *
	 * @return array
	 */
	public function generate_source_dictionary( $card_info = array() ) {

		if ( empty( $card_info ) ) {
			$card_info = $this->purchase_data['card_info'];
		}

		$card_info = array_map( 'trim', $card_info );
		$card_info = array_map( 'strip_tags', $card_info );

		return array(
			'object'    => 'card',
			'exp_month' => $card_info['card_exp_month'],
			'exp_year'  => $card_info['card_exp_year'],
			'number'    => $card_info['card_number'],
			'cvc'       => $card_info['card_cvc'],
			'name'      => $card_info['card_name']
		);
	}

	/**
	 * Process Stripe Webhooks
	 *
	 * @description Processes webhooks from the payment processor
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

		// retrieve the request's body and parse it as JSON
		$body       = @file_get_contents( 'php://input' );
		$event_json = json_decode( $body );

		if ( isset( $event_json->id ) ) {

			$result = $this->process_stripe_event( $event_json->id );

			if ( false == $result ) {
				$message = __( 'Something went wrong with processing the payment gateway event', 'give-recurring' );
			} else {
				$message = sprintf( __( 'Processed event : %s', 'give-recurring' ), $result );
			}

		} else {
			$message = __( 'Invalid Request', 'give-recurring' );
		}

		status_header( 200 );
		exit( $message );
	}

	/**
	 * Process a Stripe Event
	 *
	 * @param  string $event_id
	 *
	 * @return void|object
	 */
	public function process_stripe_event( $event_id ) {

		try {

			$stripe_event = Stripe_Event::retrieve( $event_id );

			switch ( $stripe_event->type ) {

				case 'invoice.payment_succeeded':
					$this->process_invoice_payment_succeeded_event( $stripe_event );
					break;
				case 'customer.subscription.deleted':
					$this->process_customer_subscription_deleted( $stripe_event );
					break;
				case 'charge.refunded':
					$this->process_charge_refunded_event( $stripe_event );
					break;
			}

			do_action( 'give_recurring_stripe_event_' . $stripe_event->type, $stripe_event );

			return $stripe_event->type;

		} catch ( Exception $e ) {

			return false;

		}
	}

	/**
	 * Processes invoice.payment_succeeded event
	 *
	 * @param  Stripe_Event $stripe_event
	 *
	 * @return bool|Give_Subscription
	 */
	public function process_invoice_payment_succeeded_event( $stripe_event ) {

		if ( $stripe_event instanceof Stripe_Event ) {

			if ( 'invoice.payment_succeeded' != $stripe_event->type ) {
				return false;
			}

			$invoice = $stripe_event->data->object;

			// Make sure we have an invoice object
			if ( 'invoice' == $invoice->object ) {

				$customer                = $invoice->customer;
				$subscription_profile_id = $invoice->subscription;
				$subscription            = new Give_Subscription( $subscription_profile_id, true );

				// Check for subscription ID
				if ( 0 === $subscription->id ) {
					return false;
				}

				$total_payments = intval( $subscription->get_total_payments() );
				$bill_times     = intval( $subscription->bill_times );

				// If subscription is ongoing or bill_times is less than total payments
				if ( $bill_times == 0 || $total_payments < $bill_times ) {

					// Houston, we have a new invoice payment for a subscription
					$amount         = $this->cents_to_dollars( $invoice->total );
					$transaction_id = $invoice->charge;

					// Look to see if we have set the transaction ID on the parent payment yet
					if ( ! give_get_payment_transaction_id( $subscription->parent_payment_id ) ) {

						// Ahlan wa sahlan, this is your first signup
						give_set_payment_transaction_id( $subscription->parent_payment_id, $transaction_id );

					} else {

						// Habibi, we have a renewal..!
						$subscription->add_payment( compact( 'amount', 'transaction_id' ) );
						$subscription->renew();
						//Check if this subscription is complete
						$this->is_subscription_completed( $subscription, $total_payments, $bill_times );

					}

				}

				return $subscription;

			}
		}

		return false;
	}

	/**
	 * Process customer.subscription.deleted event posted to webhooks
	 *
	 * @param  Stripe_Event $stripe_event
	 *
	 * @return bool
	 */
	public function process_customer_subscription_deleted( $stripe_event ) {

		if ( $stripe_event instanceof Stripe_Event ) {

			//Sanity Check
			if ( 'customer.subscription.deleted' != $stripe_event->type ) {
				return false;
			}

			$subscription = $stripe_event->data->object;

			if ( 'subscription' == $subscription->object ) {

				$profile_id   = $subscription->id;
				$subscription = new Give_Subscription( $profile_id, true );

				//Sanity Check: Don't cancel already completed subscriptions or empty subscription objects
				if ( empty ( $subscription ) || $subscription->status == 'completed' ) {

					return false;

				} else {
					//Cancel the sub
					$subscription->cancel();

					return true;
				}

			}
		}

		return false;
	}

	/**
	 * Process charge.refunded Stripe_Event
	 *
	 * @param  $stripe_event Stripe_Event
	 *
	 * @return bool
	 */
	public function process_charge_refunded_event( $stripe_event ) {

		global $wpdb;

		if ( $stripe_event instanceof Stripe_Event ) {

			if ( 'charge.refunded' != $stripe_event->type ) {
				return false;
			}

			$charge = $stripe_event->data->object;

			if ( 'charge' == $charge->object && $charge->refunded ) {

				$payment_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_give_payment_transaction_id' AND meta_value = %s LIMIT 1", $charge->id ) );

				if ( $payment_id ) {

					give_update_payment_status( $payment_id, 'refunded' );
					give_insert_payment_note( $payment_id, __( 'Charge refunded in Stripe.', 'give-recurring' ) );

					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Converts Cents to Dollars
	 *
	 * @param  string $cents
	 *
	 * @return string
	 */
	public function cents_to_dollars( $cents ) {
		return ( $cents / 100 );
	}

	/**
	 * Converts Dollars to Cents
	 *
	 * @param  string $dollars
	 *
	 * @return string
	 */
	public function dollars_to_cents( $dollars ) {
		return ( $dollars * 100 );
	}

	/**
	 * Get Stripe Customer
	 *
	 * @param  string $user_id
	 * @param  string $user_email
	 *
	 * @return bool|Stripe_Customer
	 */
	public function get_or_create_stripe_customer( $user_id, $user_email ) {

		$recurring_customer_id = $this->get_stripe_recurring_customer_id( $user_id );

		//Still no recurring customer, so create it
		if ( empty ( $recurring_customer_id ) ) {

			// We do not have Stripe Customer for this email, so lets create it
			$stripe_customer = $this->create_stripe_customer( $user_id, $user_email );

		} else {
			//We found a Stripe customer ID, retrieve it
			try {
				$stripe_customer = Stripe_Customer::retrieve( $recurring_customer_id );
			} catch ( Exception $e ) {
				$stripe_customer = false;
			}
		}

		//If this customer has been deleted, recreate them
		if ( isset( $stripe_customer->deleted ) && $stripe_customer->deleted ) {
			$stripe_customer = $this->create_stripe_customer( $user_id, $user_email );
		}

		return $stripe_customer;

	}

	/**
	 * Create a stripe customer using Stripe API
	 *
	 * @param  int $user_id
	 * @param  string $user_email
	 *
	 * @return bool|Stripe_Customer
	 */
	private function create_stripe_customer( $user_id, $user_email ) {

		try {

			// Create a customer first so we can retrieve them later for future payments
			$customer = Stripe_Customer::create( array(
					'description' => $user_email,
					'email'       => $user_email,
				)
			);

			if ( is_object( $customer ) && isset( $customer->id ) ) {
				//Stripe Main Gateway: Update users' meta with the customer ID from Stripe
				update_user_meta( $user_id, give_stripe_get_customer_key(), $customer->id );
				//Recurring: Also store in recurring
				$subscriber = new Give_Recurring_Subscriber( $user_id );
				$subscriber->set_recurring_customer_id( $this->id, $customer->id );
			}

		} catch ( Exception $e ) {

			$customer = false;

		}

		return $customer;

	}

	/**
	 * Gets a stripe plan if it exists otherwise creates a new one
	 *
	 * @param  array $subscription The subscription array set at process_checkout before creating payment profiles
	 * @param  string $return if value 'id' is passed it returns plan ID instead of Stripe_Plan
	 *
	 * @return string|Stripe_Plan
	 */
	public function get_or_create_stripe_plan( $subscription, $return = 'id' ) {

		$stripe_plan_name = give_recurring_generate_subscription_name( $subscription['id'], $subscription['price_id'] );

		$stripe_plan_id = $this->generate_stripe_plan_id( $stripe_plan_name, $subscription['recurring_amount'], $subscription['period'] );

		try {
			// Check if the plan exists already
			$stripe_plan = Stripe_Plan::retrieve( $stripe_plan_id );

		} catch ( Exception $e ) {

			// The plan does not exist, please create a new plan
			$args = array(
				'amount'               => $this->dollars_to_cents( $subscription['recurring_amount'] ),
				'interval'             => $subscription['period'],
				'interval_count'       => $subscription['frequency'],
				'name'                 => $stripe_plan_name,
				'currency'             => give_get_currency(),
				'id'                   => $stripe_plan_id,
				'statement_descriptor' => html_entity_decode( substr( $stripe_plan_name, 0, 22 ), ENT_COMPAT, 'UTF-8' ),
			);

			$stripe_plan = $this->create_stripe_plan( $args );
		}

		if ( 'id' == $return ) {
			return $stripe_plan->id;
		} else {
			return $stripe_plan;
		}
	}

	/**
	 * Generates a plan ID to be used with stripe
	 *
	 * @param  string $subscription_name Name of the subscription generated from give_recurring_generate_subscription_name
	 * @param  string $recurring_amount Recurring amount specified in the form
	 * @param  string $period Can be either 'day', 'week', 'month' or 'year'. Set from form
	 *
	 * @return string
	 */
	public function generate_stripe_plan_id( $subscription_name, $recurring_amount, $period ) {
		$subscription_name = sanitize_title( $subscription_name );

		return sanitize_key( $subscription_name . '_' . $recurring_amount . '_' . $period );
	}

	/**
	 * Creates a Stripe Plan using API
	 *
	 * @param  array $args
	 *
	 * @return bool|Stripe_Plan
	 */
	private function create_stripe_plan( $args = array() ) {

		try {

			$stripe_plan = Stripe_Plan::create( $args );

		} catch ( Exception $e ) {

			$stripe_plan = false;

		}

		return $stripe_plan;
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

		if ( $subscription->gateway === $this->id && ! empty( $subscription->profile_id ) ) {
			$ret = true;
		}

		return $ret;
	}


	/**
	 * Is Subscription Completed?
	 *
	 * @description: After a sub renewal comes in from Stripe we check to see if total_payments is greather than or equal to bill_times; if it is, we cancel the stripe sub for the customer
	 *
	 * @param $subscription
	 * @param $total_payments
	 * @param $bill_times
	 *
	 * @return bool
	 */
	public function is_subscription_completed( $subscription, $total_payments, $bill_times ) {

		if ( $total_payments >= $bill_times && $bill_times != 0 ) {
			//Cancel subscription in stripe if the subscription has run its course
			$this->cancel_stripe_subscription( $subscription, true );
			//Complete the subscription w/ the Give_Subscriptions class
			$subscription->complete();

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Cancels a stripe Subscription
	 *
	 * @param  Give_Subscription $subscription
	 * @param  bool $valid
	 *
	 * @return bool
	 */
	public function cancel_stripe_subscription( $subscription, $valid ) {

		if ( empty( $valid ) ) {
			return false;
		}

		try {

			$stripe_customer_id = $this->get_stripe_recurring_customer_id( $subscription->customer->user_id );

			if ( ! empty( $stripe_customer_id ) ) {

				$stripe_customer = Stripe_Customer::retrieve( $stripe_customer_id );
				$stripe_customer->subscriptions->retrieve( $subscription->profile_id )->cancel();

				return true;
			}

			return false;

		} catch ( Exception $e ) {

			return false;

		}
	}

	/**
	 * Stripe Recurring Customer ID
	 *
	 * @description: The Stripe gateway stores it's own customer_id so this method first checks for that, if it exists. If it does it will return that value. If it doesn't it will return the recurring gateway value
	 *
	 * @param $user_id
	 *
	 * @return string
	 */
	private function get_stripe_recurring_customer_id( $user_id ) {

		$customer_id = '';
		$subscriber  = new Give_Recurring_Subscriber( $user_id, true );

		//First check user meta to see if they have made a previous donation w/ Stripe via non-recurring donation so we don't create a duplicate Stripe customer for recurring
		if ( function_exists( 'give_stripe_get_customer_key' ) ) {
			$customer_id = get_user_meta( $user_id, give_stripe_get_customer_key(), true );
		}

		//If no data found check the subscribers profile to see if there's a recurring ID already
		if ( empty( $customer_id ) ) {
			$customer_id = $subscriber->get_recurring_customer_id( $this->id );
		}

		return $customer_id;

	}

}

new Give_Recurring_Stripe;