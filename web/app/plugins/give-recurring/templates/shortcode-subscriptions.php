<?php
/**
 *  Give Template File for [give_subscriptions] shortcode
 *
 * @description: Place this template file within your theme directory under /my-theme/give/ - For more information see: https://givewp.com/documentation/
 *
 * @copyright  : http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since      : 1.0
 */

$email_access = give_get_option( 'email_access' );

//For logged in users only
if ( is_user_logged_in() || Give()->session->get_session_expiration() || Give_Recurring()->subscriber_has_email_access() ) {

	//Get subscription
	$db = new Give_Subscriptions_DB();

	if ( ! empty( get_current_user_id() ) ) {
		//pull by user_id
		$subscriber = new Give_Recurring_Subscriber( get_current_user_id(), true );
	} elseif ( Give()->session->get_session_expiration() ) {
		//pull by email
		$subscriber_email = maybe_unserialize( Give()->session->get( 'give_purchase' ) );
		$subscriber_email = isset( $subscriber_email['user_email'] ) ? $subscriber_email['user_email'] : '';
		$subscriber       = new Give_Recurring_Subscriber( $subscriber_email, false );
	} else {
		//pull by email access
		$subscriber = new Give_Recurring_Subscriber( Give()->email_access->token_email, false );
	}

	//Sanity Check: Subscribers only
	if ( $subscriber->id <= 0 ) {
		give_output_error( __( 'You have not made any subscription donations.', 'give-recurring' ) );

		return false;
	}

	$subscriptions = $subscriber->get_subscriptions( 0, array( 'active', 'expired', 'completed', 'cancelled' ) );

	//If cancelled Show message
	if ( isset( $_GET['give-message'] ) && $_GET['give-message'] == 'cancelled' ) {

		echo '<div class="give_error give_success" id="give_error_test_mode"><p><strong>' . __( 'Notice', 'give' ) . '</strong>: ' . apply_filters( 'give_recurring_subscription_cancelled_message', __( 'Your donation subscription has successfully been cancelled.', 'give' ) ) . '</p></div>';

	}

	if ( $subscriptions ) {
		do_action( 'give_before_purchase_history' ); ?>
		<table id="give_user_history" class="give-table">
			<thead>
			<tr class="give_purchase_row">
				<?php do_action( 'give_recurring_history_header_before' ); ?>

				<th><?php _e( 'Subscription', 'give-recurring' ); ?></th>
				<th><?php _e( 'Status', 'give-recurring' ); ?></th>
				<th><?php _e( 'Renewal Date', 'give-recurring' ); ?></th>
				<th><?php _e( 'Billing Cycle', 'give-recurring' ); ?></th>
				<th><?php _e( 'Progress', 'give-recurring' ); ?></th>
				<!--				<th>--><?php //_e( 'Profile ID', 'give-recurring' );
				?><!--</th>-->
				<th><?php _e( 'Start', 'give-recurring' ); ?></th>
				<th><?php _e( 'End', 'give-recurring' ); ?></th>
				<th><?php _e( 'Actions', 'give-recurring' ); ?></th>

				<?php do_action( 'give_recurring_history_header_after' ); ?>
			</tr>
			</thead>
			<?php foreach ( $subscriptions as $subscription ) :

				$frequency = give_recurring_pretty_subscription_frequency( $subscription->period );
				$renewal_date = ! empty( $subscription->expiration ) ? date_i18n( get_option( 'date_format' ), strtotime( $subscription->expiration ) ) : __( 'N/A', 'give-recurring' );
				$sub = new Give_Subscription( $subscription->id );
				?>
				<tr>
					<?php do_action( 'give_recurring_history_row_start', $subscription ); ?>
					<td><?php echo get_the_title( $subscription->product_id ); ?></td>
					<td><?php echo ucfirst( $subscription->status ); ?></td>
					<td><?php echo $renewal_date; ?></td>
					<td><?php echo give_currency_filter( give_format_amount( $subscription->recurring_amount ) ) . ' / ' . $frequency; ?></td>
					<td><?php echo get_times_billed_text( $subscription ); ?></td>
					<td><?php
						//Start
						echo date_i18n( get_option( 'date_format' ), strtotime( $subscription->created ) );
						?></td>
					<!--					<td>--><?php //echo $subscription->profile_id;
					?><!--</td>-->
					<td><?php
						//End
						if ( $subscription->bill_times == 0 ) {
							echo __( 'Until cancelled', 'give-recurring' );
						} else {
							echo date_i18n( get_option( 'date_format' ), $sub->get_subscription_end_time() );
						};
						?></td>
					<td>
						<a href="<?php echo esc_url( add_query_arg( 'payment_key', give_get_payment_key( $subscription->parent_payment_id ), give_get_success_page_uri() ) ); ?>"><?php _e( 'View Invoice', 'give-reccuring' ); ?></a>
						<?php if ( $subscription->can_cancel() ) : ?>
							&nbsp;|&nbsp;
							<a href="<?php echo esc_url( $subscription->get_cancel_url() ); ?>" class="give-cancel-subscription"><?php _e( 'Cancel', 'give-recurring' ); ?></a>
						<?php endif; ?>

					</td>
					<?php do_action( 'give_recurring_history_row_end', $subscription ); ?>

				</tr>
			<?php endforeach; ?>
		</table>

		<?php do_action( 'give_after_recurring_history' ); ?>

	<?php }  //endif $subscriptions ?>

<?php } elseif ( $email_access == 'on' && ! Give_Recurring()->subscriber_has_email_access() ) {

	//Email Access Enabled & no valid token
	ob_start();

	give_get_template_part( 'email-login-form' );

	echo ob_get_clean();

} else {

	give_output_error( __( 'You must be logged in to view your subscriptions.', 'give-recurring' ) );

	echo give_login_form( give_get_current_page_url() );

} ?>

