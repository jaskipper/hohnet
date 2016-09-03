<?php
/**
 * Subscriptions
 */


/**
 * Handles subscription update
 *
 * @access      public
 * @since       2.4
 * @return      void
 */
function give_recurring_process_subscription_update() {

	if ( empty( $_POST['sub_id'] ) ) {
		return;
	}

	if ( empty( $_POST['give_update_subscription'] ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_give_payments' ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_POST['give-recurring-update-nonce'], 'give-recurring-update' ) ) {
		wp_die( __( 'Error', 'give-recurring' ), __( 'Nonce verification failed', 'give-recurring' ), array( 'response' => 403 ) );
	}

	$expiration   = date( 'Y-m-d 23:59:59', strtotime( $_POST['expiration'] ) );
	$subscription = new Give_Subscription( absint( $_POST['sub_id'] ) );
	$subscription->update( array( 'status' => sanitize_text_field( $_POST['status'] ), 'expiration' => $expiration ) );

	wp_redirect( admin_url( 'edit.php?post_type=give_forms&page=give-subscriptions&give-message=updated&id=' . $subscription->id ) );
	exit;

}

add_action( 'admin_init', 'give_recurring_process_subscription_update', 1 );

/**
 * Handles subscription deletion
 *
 * @access      public
 * @return      void
 */
function give_recurring_process_subscription_deletion() {

	if ( empty( $_POST['sub_id'] ) ) {
		return;
	}

	if ( empty( $_POST['give_delete_subscription'] ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_give_payments' ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_POST['give-recurring-update-nonce'], 'give-recurring-update' ) ) {
		wp_die( __( 'Error: Nonce verification failed', 'give-recurring' ), __( 'Nonce verification failed', 'give-recurring' ), array( 'response' => 403 ) );
	}

	$subscription = new Give_Subscription( absint( $_POST['sub_id'] ) );

	delete_post_meta( $subscription->parent_payment_id, '_give_subscription_payment' );

	$subscription->delete();

	wp_redirect( admin_url( 'edit.php?post_type=give_forms&page=give-subscriptions&give-message=deleted' ) );
	exit;

}

add_action( 'admin_init', 'give_recurring_process_subscription_deletion', 2 );