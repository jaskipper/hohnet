<?php
/**
 * The Recurring Subscriber Class
 *
 * @description Includes methods for setting users as customers, setting their status, expiration, etc.
 *
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Give_Recurring_Subscriber extends Give_Customer {

	private $subs_db;

	/**
	 * Get us started
	 *
	 * @param bool|false $_id_or_email
	 * @param bool|false $by_user_id
	 */
	function __construct( $_id_or_email = false, $by_user_id = false ) {
		parent::__construct( $_id_or_email, $by_user_id );
		$this->subs_db = new Give_Subscriptions_DB;
	}

	/**
	 * Has Active Product Subscription
	 *
	 * @param int $form_id
	 *
	 * @return mixed|void
	 */
	public function has_active_product_subscription( $form_id = 0 ) {

		$ret  = false;
		$subs = $this->get_subscriptions( $form_id );

		if ( $subs ) {

			foreach ( $subs as $sub ) {

				if ( $sub->is_active() ) {
					$ret = true;
					break;
				}

			}

		}

		return apply_filters( 'give_recurring_has_active_product_subscription', $ret, $form_id, $this );
	}

	/**
	 * Has Product Subscription
	 *
	 * @param int $form_id
	 *
	 * @return mixed|void
	 */
	public function has_product_subscription( $form_id = 0 ) {

		$ret  = false;
		$subs = $this->get_subscriptions( $form_id );
		$ret  = ! empty( $subs );

		return apply_filters( 'give_recurring_has_product_subscription', $ret, $form_id, $this );
	}

	/**
	 * Has Active Subscription
	 *
	 * @return mixed|void
	 */
	public function has_active_subscription() {

		$ret  = false;
		$subs = $this->get_subscriptions();
		if ( $subs ) {
			foreach ( $subs as $sub ) {

				if ( $this->is_subscription_active( $sub ) || ( ! $this->is_subscription_expired( $sub ) && $this->is_subscription_cancelled( $sub ) ) ) {
					$ret = true;
				}

			}
		}

		return apply_filters( 'give_recurring_has_active_subscription', $ret, $this );
	}

	/**
	 * Get Subscription by Profile ID
	 *
	 * @param string $profile_id
	 *
	 * @return bool|Give_Subscription
	 */
	public function get_subscription_by_profile_id( $profile_id = '' ) {

		if ( empty( $profile_id ) ) {
			return false;
		}

		return new Give_Subscription( $profile_id, true );

	}

	/**
	 * Add Subscription
	 *
	 * @param array $args
	 *
	 * @return bool|int
	 */
	public function add_subscription( $args = array() ) {

		$args = wp_parse_args( $args, $this->subs_db->get_column_defaults() );

		if ( empty( $args['product_id'] ) ) {
			return false;
		}

		if ( ! empty( $this->user_id ) ) {

			$this->set_as_subscriber();
		}

		$args['customer_id'] = $this->id;

		$subscription = new Give_Subscription();

		return $subscription->create( $args );

	}

	/**
	 * Add Payment
	 *
	 * @since 1.0
	 *
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function add_payment( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'subscription_id' => 0,
			'amount'          => '0.00',
			'transaction_id'  => '',
		) );

		if ( empty( $args['subscription_id'] ) ) {
			return false;
		}

		$subscription = new Give_Subscription( $args['subscription_id'] );

		if ( empty( $subscription ) ) {
			return false;
		}

		unset( $args['subscription_id'] );

		return $subscription->add_payment( $args );

	}

	/**
	 * Get Subscription
	 *
	 * @param int $subscription_id
	 *
	 * @return object
	 */
	public function get_subscription( $subscription_id = 0 ) {
		return new Give_Recurring( $subscription_id );
	}


	/**
	 * Get Subscription from Profile ID
	 *
	 * @param string $profile_id
	 *
	 * @return object|WP_Error
	 */
	public function get_subscription_from_profile_id( $profile_id = '' ) {

		$sub = $this->subs_db->get_by( 'profile_id', $profile_id );

		if ( $sub->customer_id != $this->id ) {
			return new WP_Error( 'invalid_profile_id', __( 'The specified profile ID does not belong to the given customer', 'give-recurring' ) );
		}

		return $sub;
	}

	/**
	 * Get Subscriptions
	 *
	 * @param int $form_id
	 *
	 * @param int $form_id
	 * @param array $statuses
	 *
	 * @return array|bool|mixed|null|object
	 */
	public function get_subscriptions( $form_id = 0, $statuses = array() ) {

		$subs = array();

		$args = array(
			'customer_id' => $this->id,
			'number'      => - 1
		);

		if ( ! empty( $statuses ) ) {
			$args['status'] = $statuses;
		}

		if ( ! empty( $form_id ) ) {
			$args['product_id'] = $form_id;
		}

		foreach ( (array) $this->subs_db->get_subscriptions( $args ) as $subscription ) {
			$subs[] = new Give_Subscription( $subscription->id );
		}

		return $subs;
	}

	/**
	 * Get All Payments
	 *
	 * @description Returns ALL of the subscribers `give_subscription` status payments count regardless of subscription
	 *
	 * @return int
	 */
	public function get_all_subscription_payments() {

		//Get all subscribers payments
		$payments = give_get_payments( array( 'post__in' => explode( ',', $this->payment_ids ) ) );

		$count = 0;
		foreach ( $payments as $payment ) {

			//Remove all non-give_subscription from count
			if ( $payment->post_status !== 'give_subscription' ) {
				break;
			}
			$count ++;
		}

		return apply_filters( 'get_all_subscription_payments', intval( $count ) );

	}

	/**
	 * Set as Subscriber
	 *
	 * @description Set a user as a subscriber
	 *
	 * @return void
	 */
	public function set_as_subscriber() {

		$user = new WP_User( $this->user_id );

		if ( $user ) {
			$user->add_role( 'give_subscriber' );
			do_action( 'give_recurring_set_as_subscriber', $this->user_id );
		}

	}

	/**
	 * Get New Expiration
	 *
	 * @description Calculate a new expiration date
	 *
	 * @param int $form_id
	 * @param null $price_id
	 *
	 * @return bool|string
	 */
	public function get_new_expiration( $form_id = 0, $price_id = null ) {

		if ( give_has_variable_prices( $form_id ) ) {

			$period = Give_Recurring::get_period( $form_id, $price_id );

		} else {

			$period = Give_Recurring::get_period( $form_id );

		}

		return date( 'Y-m-d H:i:s', strtotime( '+ 1 ' . $period . ' 23:59:59' ) );


	}


	/**
	 * Get Recurring Customer ID
	 *
	 * @description Get a recurring customer ID
	 *
	 * @since       1.0
	 *
	 * @param  $gateway      string The gateway to get the customer ID for
	 *
	 * @return string
	 */
	public function get_recurring_customer_id( $gateway ) {

		$meta = get_user_meta( $this->user_id, '_give_recurring_id', true );

		$value = isset( $meta[ $gateway ] ) ? $meta[ $gateway ] : '';

		return $value;

	}

	/**
	 * Store a recurring customer ID in array
	 *
	 * @description: Sets a customer ID per gateway as needed; for instance, Stripe you create a customer and then subscribe them to a plan. The customer ID is stored here.
	 *
	 * @since      1.0
	 *
	 * @param  $gateway      string The gateway to set the customer ID for
	 * @param  $recurring_id string The recurring profile ID to set
	 *
	 * @return bool
	 */
	public function set_recurring_customer_id( $gateway, $recurring_id = '' ) {

		$current_value = get_user_meta( $this->user_id, '_give_recurring_id', true );
		$recurring_id  = apply_filters( 'give_recurring_set_customer_id', $recurring_id, $this->user_id );

		if ( empty( $current_value ) || ! is_array( $current_value ) ) {
			$current_value = array();
		}

		$current_value[ $gateway ] = $recurring_id;

		return update_user_meta( $this->user_id, '_give_recurring_id', $current_value );

	}
	
}