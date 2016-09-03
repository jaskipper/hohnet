<?php
/**
 * Stripe Preapproval Functions
 *
 * @package     Give
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-1.0.php GNU Public License
 * @since       1.1
 */


/**
 * PreApproval Admin Messages
 *
 * @since 1.1
 * @return void
 */
function give_stripe_admin_messages() {

	if ( isset( $_GET['give-message'] ) && 'preapproval-charged' == $_GET['give-message'] ) {
		add_settings_error( 'give-stripe-notices', 'give-stripe-preapproval-charged', __( 'The preapproved payment was successfully charged.', 'give-stripe' ), 'updated' );
	}
	if ( isset( $_GET['give-message'] ) && 'preapproval-failed' == $_GET['give-message'] ) {
		add_settings_error( 'give-stripe-notices', 'give-stripe-preapproval-charged', __( 'The preapproved payment failed to be charged. View order details for further details.', 'give-stripe' ), 'error' );
	}
	if ( isset( $_GET['give-message'] ) && 'preapproval-cancelled' == $_GET['give-message'] ) {
		add_settings_error( 'give-stripe-notices', 'give-stripe-preapproval-cancelled', __( 'The preapproved payment was successfully cancelled.', 'give-stripe' ), 'updated' );
	}

	settings_errors( 'give-stripe-notices' );
}

add_action( 'admin_notices', 'give_stripe_admin_messages' );

/**
 * Trigger preapproved payment charge
 *
 * @since 1.0
 * @return void
 */
function give_stripe_process_preapproved_charge() {

	if ( empty( $_GET['nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_GET['nonce'], 'give-stripe-process-preapproval' ) ) {
		return;
	}

	$payment_id = absint( $_GET['payment_id'] );
	$charge     = give_stripe_charge_preapproved( $payment_id );

	if ( $charge ) {
		wp_redirect( esc_url_raw( add_query_arg( array( 'give-message' => 'preapproval-charged' ), admin_url( 'edit.php?post_type=give_forms&page=give-payment-history' ) ) ) );
		exit;
	} else {
		wp_redirect( esc_url_raw( add_query_arg( array( 'give-message' => 'preapproval-failed' ), admin_url( 'edit.php?post_type=give_forms&page=give-payment-history' ) ) ) );
		exit;
	}

}

add_action( 'give_charge_stripe_preapproval', 'give_stripe_process_preapproved_charge' );


/**
 * Cancel a preapproved payment
 *
 * @since 1.0
 * @return void
 */
function give_stripe_process_preapproved_cancel() {


	if ( empty( $_GET['nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_GET['nonce'], 'give-stripe-process-preapproval' ) ) {
		return;
	}

	$payment_id  = absint( $_GET['payment_id'] );
	$customer_id = get_post_meta( $payment_id, '_give_stripe_customer_id', true );

	if ( empty( $customer_id ) || empty( $payment_id ) ) {
		return;
	}

	if ( 'preapproval' !== get_post_status( $payment_id ) ) {
		return;
	}

	if ( ! class_exists( 'Stripe' ) ) {
		require_once GIVE_STRIPE_PLUGIN_DIR . '/Stripe/Stripe.php';
	}

	give_insert_payment_note( $payment_id, __( 'Preapproval cancelled', 'give-stripe' ) );
	give_update_payment_status( $payment_id, 'cancelled' );
	delete_post_meta( $payment_id, '_give_stripe_customer_id' );

	wp_redirect( esc_url_raw( add_query_arg( array( 'give-message' => 'preapproval-cancelled' ), admin_url( 'edit.php?post_type=give_forms&page=give-payment-history' ) ) ) );
	exit;
}

add_action( 'give_cancel_stripe_preapproval', 'give_stripe_process_preapproved_cancel' );


/**
 * Charge a preapproved payment
 *
 * @since 1.0
 *
 * @param int $payment_id
 *
 * @return bool
 */
function give_stripe_charge_preapproved( $payment_id = 0 ) {

	global $give_options;

	if ( empty( $payment_id ) ) {
		return false;
	}

	$customer_id = get_post_meta( $payment_id, '_give_stripe_customer_id', true );

	if ( empty( $customer_id ) || empty( $payment_id ) ) {
		return false;
	}

	if ( 'preapproval' !== get_post_status( $payment_id ) ) {
		return false;
	}

	if ( ! class_exists( 'Stripe' ) ) {
		require_once GIVE_STRIPE_PLUGIN_DIR . '/Stripe/Stripe.php';
	}


	$secret_key = give_is_test_mode() ? trim( $give_options['test_secret_key'] ) : trim( $give_options['live_secret_key'] );

	Stripe::setApiKey( $secret_key );

	//Statement Descriptor
	$purchase_data        = give_get_payment_meta( $payment_id );
	$form_title           = isset( $purchase_data['form_title'] ) ? $purchase_data['form_title'] : __( 'Untitled donation form', 'give-stripe' );
	$statement_descriptor = give_get_stripe_statement_descriptor( $purchase_data );

	//Currency
	if ( give_stripe_is_zero_decimal_currency() ) {
		$amount = give_get_payment_amount( $payment_id );
	} else {
		$amount = give_get_payment_amount( $payment_id ) * 100;
	}

	//Charge it
	try {

		$charge = Stripe_Charge::create( array(
				'amount'               => $amount,
				'currency'             => give_get_currency(),
				'customer'             => $customer_id,
				'description'          => sprintf( __( 'Preapproved charge for donation %s made on the "%s" form from %s', 'give-stripe' ), give_get_payment_key( $payment_id ), $form_title, home_url() ),
				'statement_descriptor' => $statement_descriptor,
			)
		);

	}
	catch ( Stripe_CardError $e ) {

		$body = $e->getJsonBody();
		$err  = $body['error'];

		$error_message = isset( $err['message'] ) ? $err['message'] : __( 'There was an error processing this charge', 'give-stripe' );

	}
	catch ( Stripe_ApiConnectionError $e ) {

		$body = $e->getJsonBody();
		$err  = $body['error'];

		$error_message = isset( $err['message'] ) ? $err['message'] : __( 'There was an error processing this charge', 'give-stripe' );

	}
	catch ( Stripe_InvalidRequestError $e ) {

		$body = $e->getJsonBody();
		$err  = $body['error'];

		$error_message = isset( $err['message'] ) ? $err['message'] : __( 'There was an error processing this charge', 'give-stripe' );

	}
	catch ( Stripe_ApiError $e ) {

		$body = $e->getJsonBody();
		$err  = $body['error'];

		$error_message = isset( $err['message'] ) ? $err['message'] : __( 'There was an error processing this charge', 'give-stripe' );
	}
	catch ( Stripe_AuthenticationError $e ) {

		$body = $e->getJsonBody();
		$err  = $body['error'];

		$error_message = isset( $err['message'] ) ? $err['message'] : __( 'There was an error processing this charge', 'give-stripe' );

	}
	catch ( Stripe_Error $e ) {

		$body = $e->getJsonBody();
		$err  = $body['error'];

		$error_message = isset( $err['message'] ) ? $err['message'] : __( 'There was an error processing this charge', 'give-stripe' );

	}
	catch ( Exception $e ) {

		// some sort of other error
		$body = $e->getJsonBody();
		$err  = $body['error'];

		$error_message = isset( $err['message'] ) ? $err['message'] : __( 'There was an error processing this charge', 'give-stripe' );

	}

	//Charge Success
	if ( ! empty( $charge ) ) {

		give_insert_payment_note( $payment_id, 'Stripe Charge ID: ' . $charge->id );
		give_update_payment_status( $payment_id, 'publish' );
		delete_post_meta( $payment_id, '_give_stripe_customer_id' );

		return true;

	} else {

		//Error :(
		give_insert_payment_note( $payment_id, $error_message );

		return false;
	}
}


/**
 * Register payment statuses for PreApproval
 *
 * @since 1.0
 * @return void
 */
function give_stripe_register_post_statuses() {
	register_post_status( 'preapproval', array(
		'label'                     => _x( 'Preapproved', 'Preapproved payment', 'give-stripe' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'give-stripe' )
	) );
	register_post_status( 'cancelled', array(
		'label'                     => _x( 'Cancelled', 'Cancelled payment', 'give-stripe' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'give-stripe' )
	) );
}

add_action( 'init', 'give_stripe_register_post_statuses', 110 );


/**
 * Register our new payment status labels for Give Stripe
 *
 * @since 1.0
 * @return array
 */
function give_stripe_payment_status_labels( $statuses ) {
	$statuses['preapproval'] = __( 'Preapproved', 'give-stripe' );
	$statuses['cancelled']   = __( 'Cancelled', 'give-stripe' );

	return $statuses;
}

add_filter( 'give_payment_statuses', 'give_stripe_payment_status_labels' );


/**
 * Display the Preapprove column label
 *
 * @since 1.0
 * @return array
 */
function give_stripe_payments_column( $columns ) {

	global $give_options;

	if ( isset( $give_options['stripe_preapprove_only'] ) ) {
		$columns['preapproval'] = __( 'Preapproval', 'give-stripe' );
	}

	return $columns;
}

add_filter( 'give_payments_table_columns', 'give_stripe_payments_column' );


/**
 * Show the Process / Cancel buttons for preapproved payments
 *
 * @param $value
 * @param $payment_id
 * @param $column_name
 *
 * @return string
 */
function give_stripe_payments_column_data( $value, $payment_id, $column_name ) {

	$gateway = give_get_payment_gateway( $payment_id );

	if ( $column_name == 'preapproval' && $gateway == 'stripe' ) {

		$status      = get_post_status( $payment_id );
		$customer_id = get_post_meta( $payment_id, '_give_stripe_customer_id', true );

		if ( give_is_payment_complete( $payment_id ) ) {
			return __( 'Complete', 'give-stripe' );
		} elseif ( $status == 'cancelled' ) {
			return __( 'Cancelled', 'give-stripe' );
		}

		if ( ! $customer_id ) {
			return $value;
		}

		$nonce = wp_create_nonce( 'give-stripe-process-preapproval' );

		$preapproval_args = array(
			'payment_id'  => $payment_id,
			'nonce'       => $nonce,
			'give-action' => 'charge_stripe_preapproval'
		);
		$cancel_args      = array(
			'preapproval_key' => $customer_id,
			'payment_id'      => $payment_id,
			'nonce'           => $nonce,
			'give-action'     => 'cancel_stripe_preapproval'
		);

		if ( 'preapproval' === $status ) {
			$value = '<a href="' . esc_url( add_query_arg( $preapproval_args, admin_url( 'edit.php?post_type=give_forms&page=give-payment-history' ) ) ) . '" class="button-secondary button button-small" style="width: 120px; margin: 0 0 3px; text-align:center;">' . __( 'Process Payment', 'give-stripe' ) . '</a>&nbsp;';
			$value .= '<a href="' . esc_url( add_query_arg( $cancel_args, admin_url( 'edit.php?post_type=give_forms&page=give-payment-history' ) ) ) . '" class="button-secondary button button-small" style="width: 120px; margin: 0; text-align:center;">' . __( 'Cancel Preapproval', 'give-stripe' ) . '</a>';
		}
	}

	return $value;
}

add_filter( 'give_payments_table_column', 'give_stripe_payments_column_data', 9, 3 );


/**
 * Send Preapproved Donation Admin Notice
 *
 * @description Sends a notice to site admins about the pending donation
 *
 * @since       1.3
 *
 * @param int $payment_id
 *
 * @return void
 *
 */
function give_stripe_preapproval_send_admin_notice( $payment_id = 0 ) {

	/* Send an email notification to the admin */
	$admin_email = give_get_admin_notice_emails();
	$user_info   = give_get_payment_meta_user_info( $payment_id );

	if ( isset( $user_info['id'] ) && $user_info['id'] > 0 ) {
		$user_data = get_userdata( $user_info['id'] );
		$name      = $user_data->display_name;
	} elseif ( isset( $user_info['first_name'] ) && isset( $user_info['last_name'] ) ) {
		$name = $user_info['first_name'] . ' ' . $user_info['last_name'];
	} else {
		$name = $user_info['email'];
	}

	$amount = give_currency_filter( give_format_amount( give_get_payment_amount( $payment_id ) ) );

	$admin_subject = apply_filters( 'give_stripe_admin_donation_notification_subject', __( 'New Pending Donation', 'give-stripe' ), $payment_id );

	$admin_message = __( 'Hello Admin,', 'give-stripe' ) . "\n\n" . __( 'A Stripe donation has been made which requires your approval.', 'give-stripe' ) . "\n\n";

	$order_url = '<a href="' . admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-order-details&id=' . $payment_id ) . '">';

	//Message
	$admin_message .= '<strong>' . __( 'Donor: ', 'give-stripe' ) . '</strong>' . html_entity_decode( $name, ENT_COMPAT, 'UTF-8' ) . "\n";
	$admin_message .= '<strong>' . __( 'Amount: ', 'give-stripe' ) . '</strong>' . html_entity_decode( $amount, ENT_COMPAT, 'UTF-8' ) . "\n\n";
	$admin_message .= sprintf( __( '%sClick Here to View Donation Details%s', 'give-stripe' ), $order_url, ' &raquo;</a>' ) . "\n\n";
	$admin_message = apply_filters( 'give_stripe_admin_donation_notification', $admin_message, $payment_id );
	$admin_headers = apply_filters( 'give_stripe_admin_donation_notification_headers', array(), $payment_id );

	//Check for Give Core email
	if ( class_exists( 'Give_Emails' ) ) {
		$sent = Give()->emails->send( $admin_email, $admin_subject, $admin_message );
	} else {
		$sent = wp_mail( $admin_email, $admin_subject, $admin_message, $admin_headers );
	}

	//Record email sent in log
	if ( $sent ) {
		give_insert_payment_note( $payment_id, __( 'Preapproval payment admin email notice sent to: ', 'give-stripe' ) . implode( ',', $admin_email ) );
	}


}


/**
 * Send Preapproval Notice
 *
 * @description Sends a notice to the donor with stripe instructions; can be customized per form
 *
 * @param int $payment_id
 *
 * @since       1.0
 * @return void
 */
function give_stripe_send_preapproval_notice( $payment_id = 0 ) {

	$payment_data = give_get_payment_meta( $payment_id );
	
	//Customize email content depending on whether the single form has been customized
	$email_content = give_get_option( 'global_stripe_donation_email' );

	$from_name = give_get_option( 'from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	$from_name = apply_filters( 'give_purchase_from_name', $from_name, $payment_id, $payment_data );

	$from_email = give_get_option( 'from_email', get_bloginfo( 'admin_email' ) );
	$from_email = apply_filters( 'give_purchase_from_address', $from_email, $payment_id, $payment_data );

	$to_email = give_get_payment_user_email( $payment_id );

	$subject = give_get_option( 'stripe_donation_subject', __( 'Thank You for Your Donation', 'give' ) );

	$subject = apply_filters( 'give_stripe_donation_subject', wp_strip_all_tags( $subject ), $payment_id );
	$subject = give_do_email_tags( $subject, $payment_id );

	$attachments = apply_filters( 'give_stripe_donation_attachments', array(), $payment_id, $payment_data );
	$message     = give_do_email_tags( $email_content, $payment_id );

	$emails = Give()->emails;

	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_email', $from_email );
	$emails->__set( 'heading', __( '', 'give' ) );

	$headers = apply_filters( 'give_receipt_headers', $emails->get_headers(), $payment_id, $payment_data );
	$emails->__set( 'headers', $headers );

	$emails->send( $to_email, $subject, $message, $attachments );

}