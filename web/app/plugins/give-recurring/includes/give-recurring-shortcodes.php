<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Recurring Shortcodes
 *
 * Adds additional recurring specific shortcodes as well as hooking into existing EDD core shortcodes to add additional subscription functionality
 *
 * @since  1.0
 */
class Give_Recurring_Shortcodes {


	/**
	 * Get things started
	 */
	function __construct() {

		//Give Recurring template files work
		add_filter( 'give_template_paths', array( $this, 'add_template_stack' ) );

		// Show recurring details on the [give_receipt]
		add_action( 'give_payment_receipt_after_table', array( $this, 'subscription_receipt' ), 10, 2 );

		//Adds the [give_subscriptions] shortcode for display subscription information
		add_shortcode( 'give_subscriptions', array( $this, 'give_subscriptions' ) );

	}

	/**
	 * Adds our templates dir to the Give template stack
	 *
	 * @since 1.0
	 *
	 * @param $paths
	 *
	 * @return mixed
	 */
	public function add_template_stack( $paths ) {

		$paths[50] = GIVE_RECURRING_PLUGIN_DIR . 'templates/';

		return $paths;

	}

	/**
	 * Subscription Receipt
	 *
	 * @description: Displays the recurring details on the [give_receipt]
	 *
	 * @since      1.0
	 *
	 * @param $payment
	 * @param $receipt_args
	 *
	 * @return mixed
	 */
	public function subscription_receipt( $payment, $receipt_args ) {

		ob_start();

		give_get_template_part( 'shortcode', 'subscription-receipt' );

		echo ob_get_clean();

	}


	/**
	 * Sets up the process of verifying the saving of the updated payment method
	 *
	 * @since  x.x
	 * @return void
	 */
	public function verify_profile_update_setup() {

		if ( ! is_user_logged_in() ) {
			wp_die( __( 'Invalid User ID' ) );
		}

		$user_id = get_current_user_id();

		$this->verify_profile_update_action( $user_id );

	}


	/**
	 * Verify and fire the hook to update a recurring payment method
	 *
	 * @since  x.x
	 *
	 * @param  int $user_id The User ID to update
	 *
	 * @return void
	 */
	private function verify_profile_update_action( $user_id ) {

		$passed_nonce = isset( $_POST['give_recurring_update_nonce'] ) ? $_POST['give_recurring_update_nonce'] : false;

		if ( false === $passed_nonce || ! isset( $_POST['_wp_http_referer'] ) ) {
			wp_die( __( 'Invalid Payment Update', 'give-recurring' ) );
		}

		$verified = wp_verify_nonce( $passed_nonce, 'update-payment' );

		if ( 1 !== $verified || (int) $user_id !== (int) get_current_user_id() ) {
			wp_die( __( 'Unable to verify payment update. Please try again later.', 'give-recurring' ) );
		}

		do_action( 'give_recurring_process_profile_card_update', $user_id, $verified );

	}


	/**
	 * Subscriptions
	 *
	 * @description: Provides users with an historical overview of their purchased subscriptions
	 * @since      1.0
	 */
	public function give_subscriptions() {

		ob_start();

		give_get_template_part( 'shortcode', 'subscriptions' );

		return ob_get_clean();

	}



}

new Give_Recurring_Shortcodes();