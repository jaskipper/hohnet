<?php
/**
 * Dwolla Admin Notices
 *
 * @since 1.0
 */

add_action( 'admin_notices', 'give_dwolla_activation_admin_notice' );


function give_dwolla_activation_admin_notice() {
	//Get current user
	global $current_user;
	$user_id = $current_user->ID;

	//Get the current page to add the notice to
	global $pagenow;

	//Make sure we're on the plugins page.
	if ( $pagenow == 'plugins.php' ) {

		// If the user hasn't already dismissed our alert,
		// Output the activation banner
		if ( ! get_user_meta( $user_id, 'give_dwolla_activation_ignore_notice' ) ) { ?>

			<!-- * I output inline styles here
				 * because there's no reason to keep these
				 * enqueued after the alert is dismissed. -->
			<style>
				div.give-addon-alert.updated {
					padding: 1em 2em;
					position: relative;
					border-color: #66BB6A;
				}

				div.give-addon-alert img {
					max-width: 50px;
					position: relative;
					top: 1em;
				}

				div.give-addon-alert h3 {
					display: inline;
					position: relative;
					top: -20px;
					left: 20px;
					font-size: 24px;
					font-weight: 300;
				}

				div.give-addon-alert h3 span {
					font-weight: 900;
					color: #66BB6A;
				}

				div.give-addon-alert .alert-actions {
					position: relative;
					left: 70px;
					top: -10px;
				}

				div.give-addon-alert a {
					color: #66BB6A;
					margin-right: 2em;
				}

				div.give-addon-alert .alert-actions a {
					text-decoration: underline;
				}

				div.give-addon-alert .alert-actions a:hover {
					color: #555555;
				}

				div.give-addon-alert .alert-actions a span {
					text-decoration: none;
					margin-right: 5px;
				}

				div.give-addon-alert .dismiss {
					position: absolute;
					right: 0;
					transform: translateY(25%);
					height: 100%;
				}

			</style>

			<!-- * Now we output the HTML
				 * of the banner 			-->

			<div class="updated give-addon-alert">
				<!-- Your Logo -->
				<img src="<?php echo GIVE_PLUGIN_URL; ?>assets/images/svg/give-icon-full-circle.svg" class="give-logo" />

				<!-- Your Message -->
				<h3><?php _e( 'Thanks for installing Give\'s <span>Dwolla Payment Gateway</span> Add-on!', 'give-dwolla' ); ?></h3>

				<!-- The Dismiss Button -->
				<?php printf( __( '<a href="%1$s" class="dismiss"><span class="dashicons dashicons-dismiss"></span></a>', 'give-dwolla' ), '?give_dwolla_notice_ignore=0' ); ?>

				<!-- * Now we output a few "actions"
					 * that the user can take from here -->

				<div class="alert-actions">

					<!-- Point them to your settings page -->

					<a href="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways' ); ?>">
						<span class="dashicons dashicons-admin-settings"></span><?php _e( 'Go to Settings', 'give-dwolla' ); ?>
					</a>

					<!-- Show them how to configure the Addon -->
					<a href="https://givewp.com/documentation/add-ons/dwolla-gateway/" target="_blank">
						<span class="dashicons dashicons-media-text"></span><?php _e( 'DOCS: How to Configure your Dwolla Add-on', 'give-dwolla' ); ?>
					</a>

					<!-- Let them signup for plugin updates -->
					<a href="https://givewp.com/support/forum/add-ons/dwolla-gateway/" target="_blank">
						<span class="dashicons dashicons-sos"></span><?php _e( 'Get Support', 'give-dwolla' ); ?>
					</a>
				</div>
			</div>
			<?php
		}
	}
}

/* This is the action that allows
 * the user to dismiss the banner
 * it basically sets a tag to their
 * user meta data
 */
add_action( 'admin_init', 'give_dwolla_notice_ignore' );

function give_dwolla_notice_ignore() {
	//Get the global user
	global $current_user;
	$user_id = $current_user->ID;

	/* If user clicks to ignore the notice,
	 * add that to their user meta
	 * the banner then checks whether this tag
	 * exists already or not.
	 * See here: http://codex.wordpress.org/Function_Reference/add_user_meta
	 */
	if ( isset( $_GET['give_dwolla_notice_ignore'] ) && '0' == $_GET['give_dwolla_notice_ignore'] ) {
		add_user_meta( $user_id, 'give_dwolla_activation_ignore_notice', 'true', true );
	}
}