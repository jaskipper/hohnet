<?php
/**
 * Recurring Customer Subscription List
 *
 * @param $customer
 */
function give_recurring_customer_subscriptions_list( $customer ) {

	$subscriber    = new Give_Recurring_Subscriber( $customer->id );
	$subscriptions = $subscriber->get_subscriptions();

	if ( ! $subscriptions ) {
		return;
	}
	?>
	<h3><?php _e( 'Subscriptions', 'give-recurring' ); ?></h3>
	<table class="wp-list-table widefat striped downloads">
		<thead>
		<tr>
			<th><?php echo give_get_forms_label_singular(); ?></th>
			<th><?php _e( 'Amount', 'give-recurring' ); ?></th>
			<th><?php _e( 'Actions', 'give-recurring' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ( $subscriptions as $subscription ) : ?>
			<tr>
				<td>
					<a href="<?php echo esc_url( admin_url( 'post.php?action=edit&post=' . $subscription->product_id ) ); ?>"><?php echo get_the_title( $subscription->product_id ); ?></a>
				</td>
				<td><?php printf( _x( '%s every %s', 'Example: $10 every month', 'give-recurring' ), give_currency_filter( give_sanitize_amount( $subscription->amount ) ), $subscription->period ); ?></td>
				<td>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-subscriptions&id=' . $subscription->id ) ); ?>"><?php _e( 'View Details', 'give-recurring' ); ?></a>

			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php
}

add_action( 'give_customer_after_tables', 'give_recurring_customer_subscriptions_list' );


/**
 * Customizes the Donor's "Completed Donations" text
 *
 * @description When you view a single donor's profile there is a stat that displays "Completed Donations"; this adjusts that using a filter to include the total number of subscription donations as well
 *
 * @param $text
 * @param $customer
 *
 * @return bool
 */
function give_recurring_display_donors_subscriptions( $text, $customer ) {

	$subscriber = new Give_Recurring_Subscriber( $customer->email );

	//Sanity check: Check if this donor is a subscriber & $subscriber->payment_ids
	if ( ! $subscriber->has_product_subscription() || empty( $subscriber->payment_ids ) ) {
		echo $text;
		return false;
	}

	$count = $subscriber->get_all_subscription_payments();

	$text = $text . ', ' . $count . ' ' . _n( 'Subscription Donation', 'Subscription Donations', $count, 'give-recurring' );

	echo apply_filters( 'give_recurring_display_donors_subscriptions', $text );


}

add_filter( 'give_donor_completed_donations', 'give_recurring_display_donors_subscriptions', 10, 2 );

/**
 * Add Subscription to "Donations" columns
 *
 * @description: Within the Donations > Donors list table there is a "Donations" column that needs to properly count `give_subscription` status payments
 *
 * @param $value
 * @param $item_id
 *
 * @return mixed|string|void
 */
function give_recurring_add_subscriptions_to_donations_column( $value, $item_id ) {

	$subscriber = new Give_Recurring_Subscriber( $item_id, true );

	//Sanity check: Non-subscriber
	if ( $subscriber->id == 0 ) {
		return $value;
	}

	$subscription_payments = $subscriber->get_all_subscription_payments();
	$donor = new Give_Customer( $item_id, true );

	$value = '<a href="' .
	         admin_url( '/edit.php?post_type=give_forms&page=give-payment-history&user=' . urlencode( $donor->email )
	         ) . '">' . ( $donor->purchase_count + $subscription_payments ) . '</a>';

	return apply_filters( 'add_subscriptions_num_purchases', $value );

}

add_filter( 'give_report_column_num_purchases', 'give_recurring_add_subscriptions_to_donations_column', 10, 2 );