<?php
/**
 * Register our settings tab
 *
 * @param $tabs
 *
 * @return mixed
 */
function give_recurring_settings_tab( $tabs ) {

	$tabs['recurring'] = __( 'Recurring Donations', 'give-recurring' );

	return $tabs;
}

add_filter( 'give_settings_tabs', 'give_recurring_settings_tab' );


/**
 * Register Give Recurring Global Settings
 *
 * @since  1.0
 * @return array
 */

function give_recurring_settings( $settings ) {

	if ( ! is_admin() || ! isset( $_GET['tab'] ) || $_GET['tab'] !== 'recurring' ) {
		return $settings;
	}

	$recurring_settings = array(
		'id'         => 'options_page',
		'give_title' => __( 'Recurring Donations Settings', 'give-recurring' ),
		'show_on'    => array( 'key' => 'options-page', 'value' => array( 'recurring', ), ),
		'fields'     => apply_filters( 'give_settings_recurring', array(
				array(
					'name' => __( '&nbsp;', 'give-recurring' ),
					'desc' => __( '', 'give-recurring' ),
					'id'   => 'give_recurring_welcome',
					'type' => 'recurring_welcome'
				),
				//------------------------------
				// General Recurring Settings
				//------------------------------
				array(
					'name' => __( 'General Settings', 'give' ),
					'desc' => '<hr>',
					'type' => 'give_title',
					'id'   => 'give_recurring_title_general_settings'
				),
				array(
					'name'    => __( 'Subscriptions Page', 'give' ),
					'desc'    => sprintf( __( 'This is the page donors can access to manage their subscriptions. The %1$s[give_subscriptions]%2$s shortcode should be on this page.', 'give-recurring' ), '<code>', '</code>' ),
					'id'      => 'subscriptions_page',
					'type'    => 'select',
					'options' => give_cmb2_get_post_options( array(
						'post_type'   => 'page',
						'numberposts' => - 1
					) ),
				),
				//------------------------------
				// Subscription Payment Receipt Email
				//------------------------------
				array(
					'name' => __( 'Subscription Receipt Email', 'give' ),
					'desc' => '<hr>',
					'type' => 'give_title',
					'id'   => 'give_recurring_title_receipt_email'
				),
				array(
					'name' => __( 'Activate Subscription Receipt', 'give-recurring' ),
					'id'   => 'enable_subscription_receipt_email',
					'desc' => sprintf( __( 'Check this option if you would like donors to receive an email when a subscription donation payment has been received. Note: some payment gateways like Stripe and Authorize.net may also send out an email depending on your gateway settings.', 'give-recurring' ), '<strong>', '</strong>' ),
					'type' => 'checkbox'
				),
				array(
					'name'    => __( 'Subscription Receipt Subject', 'give' ),
					'id'      => 'subscription_notification_subject',
					'desc'    => __( 'Enter the subject line for the donation receipt email', 'give' ),
					'type'    => 'text',
					'default' => __( 'Subscription Donation Receipt', 'give' )
				),
				array(
					'name'    => __( 'Subscription Donation Receipt', 'give' ),
					'id'      => 'subscription_receipt_message',
					'desc'    => __( 'Enter the email message that is sent to users after completing a successful subscription donation. HTML is accepted. Available template tags:', 'give' ) . '<br/>' . give_get_emails_tags_list() . ' ' . Give_Recurring()->emails->get_subscription_email_tags(),
					'type'    => 'wysiwyg',
					'default' => __( "Dear", "give" ) . " {name},\n\n" . __( "Thank you for your donation and continued support. Your generosity is appreciated! Here are your donation details:", "give" ) . "\n\n<strong>Donation:</strong> {donation} - {price}\n<strong>Payment ID:</strong> {payment_id} \n<strong>Payment Method:</strong> {payment_method}\n<strong>Date:</strong> {date}\n\nSincerely,\n{sitename}"
				),

				//------------------------------
				// Cancellation Email
				//------------------------------
				array(
					'name' => __( 'Subscription Cancelled Email', 'give' ),
					'id'   => 'give_recurring_title_cancel_email',
					'desc' => '<hr>',
					'type' => 'give_title'
				),
				array(
					'name' => __( 'Activate Cancelled Email', 'give-recurring' ),
					'id'   => 'enable_subscription_cancelled_email',
					'desc' => sprintf( __( 'Check this option if you would like donors to receive an email when a subscription has been cancelled. The email will send when either the donor or admin cancels the subscription.', 'give-recurring' ), '<strong>', '</strong>' ),
					'type' => 'checkbox'
				),
				array(
					'name'    => __( 'Subscription Cancelled Subject', 'give' ),
					'id'      => 'subscription_cancelled_subject',
					'desc'    => __( 'Enter the subject line of the email sent when a subscription is cancelled.', 'give' ),
					'type'    => 'text',
					'default' => __( 'Subscription Donation Cancelled', 'give' )
				),
				array(
					'name'    => __( 'Subscription Cancelled Message', 'give' ),
					'id'      => 'subscription_cancelled_message',
					'desc'    => __( 'Enter the email message that is sent to users when a subscription is cancelled. HTML is accepted. Available template tags:', 'give' ) . Give_Recurring()->emails->get_cancelled_email_tags() . '<br/>',
					'type'    => 'wysiwyg',
					'default' => __( 'Dear', 'give-recurring' ) . " {name},\n\n" . __( "Your subscription for {donation} has been successfully cancelled. Here are the subscription details for your records:\n\n<strong>Subscription:</strong> {donation} - {price}\n<strong>Subscription Frequency:</strong> {subscription_frequency} \n<strong>Completed Donations:</strong> {subscriptions_completed} \n<strong>Payment Method:</strong> {payment_method}\n<strong>Cancellation Date:</strong> {cancellation_date}\n\nSincerely,\n{sitename}", "give" )
				),


				//				array(
				//					'name' => __( 'Renewal Reminders Email(s)', 'give' ),
				//					'desc' => '<hr>',
				//					'type' => 'give_title',
				//					'id'   => 'give_recurring_title_renewals'
				//				),
				//				array(
				//					'name' => __( 'Activate Renewal Reminders', 'give-recurring' ),
				//					'id'   => 'recurring_send_renewal_reminders',
				//					'desc' => sprintf( __( 'Check this option if you would like donors to receive one or more email reminders when their subscription is approaching %1$srenewal%2$s.', 'give-recurring' ), '<strong>', '</strong>' ),
				//					'type' => 'checkbox'
				//				),
				//				array(
				//					'name' => __( 'Renewal Reminders', 'give-recurring' ),
				//					'desc' => __( 'Configure the subscription renewal notice emails', 'give-recurring' ),
				//					'id'   => 'recurring_renewal_reminders',
				//					'type' => 'renewal_reminders'
				//				),
				//				array(
				//					'name' => __( 'Expiration Reminders Email(s)', 'give' ),
				//					'desc' => '<hr>',
				//					'type' => 'give_title',
				//					'id'   => 'give_recurring_title_expiration'
				//				),
				//				array(
				//					'name' => __( 'Activate Expiration Reminders', 'give-recurring' ),
				//					'id'   => 'recurring_send_expiration_reminders',
				//					'desc' => sprintf( __( 'Check this option if you would like donors to receive one or more email reminders when their subscription is approaching the %1$sexpiration%2$s date.', 'give-recurring' ), '<strong>', '</strong>' ),
				//					'type' => 'checkbox'
				//				),
				//				array(
				//					'name' => __( 'Expiration Reminders', 'give-recurring' ),
				//					'desc' => __( 'Configure the subscription expiration notice emails', 'give-recurring' ),
				//					'id'   => 'recurring_expiration_reminders',
				//					'type' => 'expiration_reminders'
				//				)
			)
		),
	);

	return array_merge( $settings, $recurring_settings );

}

add_filter( 'give_registered_settings', 'give_recurring_settings' );


/**
 * Recurring Welcome
 *
 * @description Displays a welcome message with links and other relevant information
 *
 * @since       1.0
 *
 * @return      void
 */
function give_recurring_recurring_welcome() {

	ob_start(); ?>
	<div class="recurring-welcome-wrap">

		<?php
		//Check if license is active
		if ( give_recurring_license_status() != 'valid' ) {
			echo '<div class="give-recurring-no-license updated error"><p>' . sprintf( __( '%1$sImportant:%2$s It appears your license is inactive for the Recurring Donations Add-on. Activating your license is important for receiving updates and support. %3$sClick here to activate your license%4$s', 'give-recurring' ), '<strong>', '</strong>', '<a href="' . admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=licenses' ) . '">', ' &raquo;</a>' ) . '</p></div>';
		}

		?>

		<div class="recurring-two-columns recurring-column-1 clearfix">

			<div class="welcome-blurb">
				<h3>
					<span class="dashicons dashicons-update"></span> <?php _e( 'Give - Recurring Donations Add-on', 'give-recurring' ); ?>
				</h3>

				<p><?php _e( 'Welcome to the Give Recurring Donations Add-on. This Add-on allows you create donations forms like never before with support for daily, weekly, monthly, and annual subscriptions. As well, you can customize the number of times you\'d like to run the subscription, for example "Give $20 a Month for a Year", and also give the donor the choice whether or not make their donation recurring.', 'give-recurring' ) ?></p>

			</div>

			<?php
			//Active license = Good
			if ( give_recurring_license_status() == 'valid' ) {
				echo '<p class="give-recurring-notice notice">' . __( 'Your license is active and you are receiving support and updates', 'give-recurring' ) . '</p>';
			}
			?>

		</div>

		<div class="recurring-two-columns recurring-column-2">

			<div class="recurring-docs-list">

				<h4><?php _e( 'Recurring Donations Documentation', 'give-recurring' ); ?></h4>
				<p><?php _e( 'The following articles will help you get started accepting recurring donations. Please read and test thoroughly prior to going live. If you have any questions or trouble along the way, we are here to help.', 'give-recurring' ); ?></p>

				<a href="https://givewp.com/documentation/add-ons/recurring-donations/" target="_blank" class="recurring-main-link">Recurring Donations</a>
				<?php echo give_recurring_docs_get_feed(); ?>

			</div>

		</div>


	</div>
	<?php
	echo ob_get_clean();
}

add_action( 'cmb2_render_recurring_welcome', 'give_recurring_recurring_welcome', 10, 5 );


/**
 * Recurring Docs Get Feed
 *
 * Gets the documentation feed for recurring.
 *
 * @since 1.0
 * @return string $cache
 */
function give_recurring_docs_get_feed() {

	$recurring_docs_debug = false; //set to true to debug
	$cache                = get_transient( 'give_recurring_docs_feed' );

	if ( $cache === false || $recurring_docs_debug === true && WP_DEBUG === true ) {
		$feed = wp_remote_get( 'https://givewp.com/downloads/feed/recurring-docs-feed.php', array( 'sslverify' => false ) );

		if ( ! is_wp_error( $feed ) ) {
			if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
				$cache = wp_remote_retrieve_body( $feed );
				set_transient( 'give_recurring_docs_feed', $cache, 3600 );
			}
		} else {
			$cache = '<div class="give-recurring-notice give-recurring-notice-issue notice">' . __( 'There was an error retrieving the Give documentation list from the server. Please try again later.', 'give' ) . '</div>';
		}
	}

	return $cache;

}