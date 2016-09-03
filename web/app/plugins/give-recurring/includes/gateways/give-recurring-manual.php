<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Give_Recurring_Manual_Payments extends Give_Recurring_Gateway {

	public function init() {

		$this->id = 'manual';

		add_action( 'give_recurring_cancel_manual_subscription', array( $this, 'cancel' ), 10, 2 );

	}

	/**
	 * Create Payment Profiles
	 */
	public function create_payment_profiles() {

		$this->subscriptions['profile_id'] = md5( $this->purchase_data['purchase_key'] . $this->subscriptions['id'] );

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
	 * Cancels a subscription
	 *
	 * @description: Since this is manual gateway we don't have to do anything...
	 *
	 * @param $subscription
	 * @param $valid
	 */
	public function cancel( $subscription, $valid ) {


	}

}

new Give_Recurring_Manual_Payments;