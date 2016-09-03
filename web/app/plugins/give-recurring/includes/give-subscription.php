<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * The Subscription Class
 *
 * @since  1.0
 */
class Give_Subscription {

	private $subs_db;

	public $id = 0;
	public $customer_id = 0;
	public $period = '';
	public $initial_amount = '';
	public $recurring_amount = '';
	public $bill_times = 0;
	public $parent_payment_id = 0;
	public $product_id = 0;
	public $created = '0000-00-00 00:00:00';
	public $expiration = '0000-00-00 00:00:00';
	public $status = 'pending';
	public $profile_id = '';
	public $gateway = '';
	public $customer;

	/**
	 * Give_Subscription constructor.
	 *
	 * @param int $_id_or_object Subscription ID or Object
	 * @param string $_by_profile_id
	 */
	function __construct( $_id_or_object = 0, $_by_profile_id = '' ) {

		$this->subs_db = new Give_Subscriptions_DB;

		if ( $_by_profile_id ) {

			$_id = $this->subs_db->get_column_by( 'id', 'profile_id', $_id_or_object );

			if ( empty( $_id ) ) {
				return false;
			}

			$_id_or_object = $_id;

		}

		return $this->setup_subscription( $_id_or_object );
	}

	/**
	 * Setup the subscription object
	 *
	 * @param int $id_or_object
	 *
	 * @return $this|bool
	 */
	private function setup_subscription( $id_or_object = 0 ) {

		if ( empty( $id_or_object ) ) {
			return false;
		}
		if ( is_numeric( $id_or_object ) ) {

			$sub = $this->subs_db->get( $id_or_object );

		} elseif ( is_object( $id_or_object ) ) {

			$sub = $id_or_object;

		}

		if ( empty( $sub ) ) {
			return false;
		}

		foreach ( $sub as $key => $value ) {
			$this->$key = $value;
		}

		$this->customer = new Give_Customer( $this->customer_id );
		$this->gateway  = give_get_payment_gateway( $this->parent_payment_id );

		return $this;
	}

	/**
	 * Magic __get function to dispatch a call to retrieve a private property
	 *
	 * @param $key
	 *
	 * @return mixed|WP_Error
	 */
	public function __get( $key ) {

		if ( method_exists( $this, 'get_' . $key ) ) {

			return call_user_func( array( $this, 'get_' . $key ) );

		} else {

			return new WP_Error( 'give-subscription-invalid-property', sprintf( __( 'Can\'t get property %s', 'give-recurring' ), $key ) );

		}

	}

	/**
	 * Creates a subscription
	 *
	 * @since  1.0
	 *
	 * @param  array $data Array of attributes for a subscription
	 *
	 * @return mixed  false if data isn't passed and class not instantiated for creation
	 */
	public function create( $data = array() ) {

		if ( $this->id != 0 ) {
			return false;
		}

		$defaults = array(
			'customer_id'       => 0,
			'period'            => '',
			'initial_amount'    => '',
			'recurring_amount'  => '',
			'bill_times'        => 0,
			'parent_payment_id' => 0,
			'product_id'        => 0,
			'created'           => '',
			'expiration'        => '',
			'status'            => '',
			'profile_id'        => '',
		);

		$args = wp_parse_args( $data, $defaults );

		do_action( 'give_subscription_pre_create', $args );

		$id = $this->subs_db->insert( $args, 'subscription' );

		do_action( 'give_subscription_post_create', $id, $args );

		return $this->setup_subscription( $id );

	}

	/**
	 * Update
	 *
	 * @description
	 *
	 * @param $args
	 *
	 * @return bool
	 */
	public function update( $args ) {
		return $this->subs_db->update( $this->id, $args );
	}

	/**
	 * Delete the subscription
	 *
	 * @return bool
	 */
	public function delete() {
		return $this->subs_db->delete( $this->id );
	}

	/**
	 * Get Original Payment ID
	 *
	 * @return int
	 */
	public function get_original_payment_id() {

		return $this->parent_payment_id;

	}

	/**
	 * Get Child Payments
	 *
	 * @return array
	 */
	public function get_child_payments() {

		$payments = get_posts( array(
			'post_parent'    => (int) $this->parent_payment_id,
			'posts_per_page' => '999',
			'post_status'    => 'any',
			'post_type'      => 'give_payment'
		) );

		return $payments;

	}


	/**
	 * Get Total Payments
	 *
	 * @description Returns the total number of times a subscription has been paid including the initial payment (that's the +1)
	 *
	 * @return int
	 */
	public function get_total_payments() {
		return count( $this->get_child_payments() ) + 1;
	}

	/**
	 * Get Lifetime Value
	 *
	 * @return mixed|void
	 */
	public function get_lifetime_value() {

		$amount   = give_get_payment_amount( $this->parent_payment_id );
		$children = $this->get_child_payments();

		if ( $children ) {

			foreach ( $children as $child ) {
				$amount += give_get_payment_amount( $child->ID );
			}
		}

		return $amount;

	}

	/**
	 * Add Payment
	 *
	 * @param array $args
	 */
	public function add_payment( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'amount'         => '',
			'transaction_id' => ''
		) );

		if ( $this->payment_exists( $args['transaction_id'] ) ) {
			return;
		}

		$form_id = give_get_payment_form_id( absint( $this->parent_payment_id ) );

		// setup the payment data
		$payment_data = array(
			'parent'          => $this->parent_payment_id,
			'price'           => $args['amount'],
			'give_form_title' => get_the_title( $form_id ),
			'give_form_id'    => intval( $form_id ),
			'user_email'      => give_get_payment_user_email( $this->parent_payment_id ),
			'purchase_key'    => get_post_meta( $this->parent_payment_id, '_give_payment_purchase_key', true ),
			'currency'        => give_get_payment_currency_code( $this->parent_payment_id ),
			'user_info'       => give_get_payment_meta_user_info( $this->parent_payment_id ),
			'status'          => 'give_subscription',
			'gateway'         => give_get_payment_gateway( $this->parent_payment_id )
		);

		// record the subscription payment
		$payment       = give_insert_payment( $payment_data );
		$creation_date = get_post_field( 'post_date', $payment, 'raw' );
		$price_id      = give_get_price_id( $form_id, $args['amount'] );

		//Increment earnings only when in LIVE mode
		if ( ! give_is_test_mode() ) {
			// increase the earnings for the form in the subscription
			give_increase_earnings( $form_id, $args['amount'] );
			// increase the purchase count for this form also
			give_increase_purchase_count( $form_id );
			//Record sale in log
			give_record_sale_in_log( $form_id, $payment, $price_id, $creation_date );
		}

		// Record transaction ID
		if ( ! empty( $args['transaction_id'] ) ) {

			if ( function_exists( 'give_set_payment_transaction_id' ) ) {
				give_set_payment_transaction_id( $payment, $args['transaction_id'] );
			}
		}

		do_action( 'give_recurring_add_subscription_payment', $payment, $this );
		do_action( 'give_recurring_record_payment', $payment, $this->parent_payment_id, $args['amount'], $args['transaction_id'] );

	}

	/**
	 * Renew Payment
	 *
	 * @description : This method is responsible for renewing a subscription (not adding payments) and will check the expiration date, whether the subscription is active, run hooks, set notes, and update the subscription status as necessary. If the subscription has reached the bill times the subscription will be completed
	 *
	 * @since       1.0
	 */
	public function renew() {

		$expires = $this->get_expiration_time();

		// Determine what date to use as the start for the new expiration calculation
		if ( $expires > current_time( 'timestamp' ) && $this->is_active() ) {
			$base_date = $expires;
		} else {
			$base_date = current_time( 'timestamp' );
		}

		$last_day   = cal_days_in_month( CAL_GREGORIAN, date( 'n', $base_date ), date( 'Y', $base_date ) );
		$expiration = date( 'Y-m-d H:i:s', strtotime( '+1 ' . $this->period . ' 23:59:59', $base_date ) );

		if ( date( 'j', $base_date ) == $last_day && 'day' != $this->period ) {
			$expiration = date( 'Y-m-d H:i:s', strtotime( $expiration . ' +2 days' ) );
		}

		$expiration = apply_filters( 'give_subscription_renewal_expiration', $expiration, $this->id, $this );

		do_action( 'give_subscription_pre_renew', $this->id, $expiration, $this );

		$status = 'active';

		$times_billed = $this->get_total_payments();

		//Complete subscription if applicable
		if ( $this->bill_times > 0 && $times_billed >= $this->bill_times ) {
			$this->complete();
			$status = 'completed';
		}

		$args = array(
			'expiration' => $expiration,
			'status'     => $status
		);

		//Update the subscription
		if ( $this->subs_db->update( $this->id, $args ) ) {

			$note = sprintf( __( 'Subscription #%$1s %2$s', 'give-recurring' ), $this->id, $status );
			$this->customer->add_note( $note );

		}

		do_action( 'give_subscription_post_renew', $this->id, $expiration, $this );
		do_action( 'give_recurring_set_subscription_status', $this->id, $status, $this );

	}

	/**
	 * Subscription Complete
	 */
	public function complete() {

		$args = array(
			'status' => 'completed'
		);

		if ( $this->subs_db->update( $this->id, $args ) ) {

			do_action( 'give_subscription_completed', $this->id, $this );

		}

	}

	/**
	 * Subscription Expire
	 */
	public function expire() {

		$args = array(
			'status' => 'expired'
		);

		if ( $this->subs_db->update( $this->id, $args ) ) {

			do_action( 'give_subscription_expired', $this->id, $this );

		}

	}

	/**
	 * Subscription Cancelled
	 */
	public function cancel() {

		$args = array(
			'status' => 'cancelled'
		);

		if ( $this->subs_db->update( $this->id, $args ) ) {

			give_update_payment_status( $this->parent_payment_id, 'cancelled' );

			do_action( 'give_subscription_cancelled', $this->id, $this );

		}

	}

	/**
	 * Can Cancel
	 *
	 * @description: This method is filtered by payment gateways in order to return true on subscriptions that can be cancelled with a profile ID through the merchant processor
	 *
	 * @return mixed|void
	 */
	public function can_cancel() {

		//These subscription status cannot be cancelled
		if ( in_array( $this->status, array( 'completed', 'cancelled', 'pending', 'expired' ) ) ) {
			return false;
		}

		$ret = apply_filters( 'give_subscription_can_cancel_' . $this->gateway . '_subscription', false, $this );

		return $ret;

	}

	/**
	 * Get Cancel URL
	 *
	 * @return mixed|void
	 */
	public function get_cancel_url() {

		$url = wp_nonce_url( add_query_arg( array(
			'give_action' => 'cancel_subscription',
			'sub_id'      => $this->id
		) ), 'give-recurring-cancel' );

		return apply_filters( 'give_subscription_cancel_url', $url, $this );
	}

	/**
	 * Is Active
	 *
	 * @return mixed|void
	 */
	public function is_active() {

		$ret = false;

		if ( ! $this->is_expired() && ( $this->status == 'active' || $this->status == 'cancelled' ) ) {
			$ret = true;
		}

		return apply_filters( 'give_subscription_is_active', $ret, $this->id, $this );

	}

	/**
	 * Is Complete
	 *
	 * @return mixed|void
	 */
	public function is_complete() {

		$ret = false;

		if ( $this->status == 'completed' ) {
			$ret = true;
		}

		return apply_filters( 'give_subscription_is_complete', $ret, $this->id, $this );

	}

	/**
	 * Is Expired
	 *
	 * @return mixed|void
	 */
	public function is_expired() {

		$ret        = false;
		$expiration = $this->get_expiration_time();

		if ( $expiration && strtotime( 'NOW', current_time( 'timestamp' ) ) > $expiration ) {
			$ret = true;
		}

		if ( $expiration == 'none' ) {
			$ret = false;
		}

		return apply_filters( 'give_subscription_is_expired', $ret, $this->id, $this );

	}

	/**
	 * Retrieves the expiration date
	 *
	 * @return string
	 */
	public function get_expiration() {
		return $this->expiration;
	}

	/**
	 * Get Expiration Time
	 *
	 * @return int
	 */
	public function get_expiration_time() {
		return strtotime( $this->expiration, current_time( 'timestamp' ) );
	}

	/**
	 * Retrieves the subscription status
	 *
	 * @return int
	 */
	public function get_status() {

		return $this->status;

	}

	/**
	 * Get Subscription Progress
	 *
	 * @description: Returns the subscription progress compared to `bill_times` such as "1/3" or "1/∞"
	 *
	 * @return int
	 */
	public function get_subscription_progress() {

		return $this->get_total_payments() . ' / ' . ( ( $this->bill_times == 0 ) ? '&#8734;' : $this->bill_times );

	}

	/**
	 * Get Subscription End Date
	 *
	 * @return int
	 */
	public function get_subscription_end_time() {

		$bill_times = intval( $this->bill_times );

		//Date out = the end of the subscription
		//Subtract 1 due to initial donation being counted
		$date_out = '+' . ( $bill_times - 1 ) . ' ' . $this->period;

		return strtotime( $date_out, strtotime( $this->created ) );

	}

	/**
	 * Payment Exists
	 *
	 * @param string $txn_id transaction ID
	 *
	 * @return bool
	 */
	public function payment_exists( $txn_id = '' ) {
		global $wpdb;

		if ( empty( $txn_id ) ) {
			return false;
		}

		$txn_id = esc_sql( $txn_id );

		$purchase = $wpdb->get_var( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_give_payment_transaction_id' AND meta_value = '{$txn_id}' LIMIT 1" );

		if ( $purchase != null ) {
			return true;
		}

		return false;
	}

}