<?php
/**
 * Plugin Name: Give - Stripe Gateway
 * Plugin URI: http://wordimpress.com/
 * Description: Adds the Stripe.com payment gateway to the available Give payment methods.
 * Author: WordImpress
 * Author URI: http://wordimpress.com
 * Contributors: WordImpress
 * Version: 1.3.1
 * GitHub Plugin URI: https://github.com/WordImpress/Give-Stripe
 */

if ( ! defined( 'GIVE_STRIPE_PLUGIN_FILE' ) ) {
	define( 'GIVE_STRIPE_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'GIVE_STRIPE_PLUGIN_DIR' ) ) {
	define( 'GIVE_STRIPE_PLUGIN_DIR', dirname( GIVE_STRIPE_PLUGIN_FILE ) );
}
if ( ! defined( 'GIVE_STRIPE_PLUGIN_URL' ) ) {
	define( 'GIVE_STRIPE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'GIVE_STRIPE_VERSION' ) ) {
	define( 'GIVE_STRIPE_VERSION', '1.3.1' );
}
if ( ! defined( 'GIVE_STRIPE_BASENAME' ) ) {
	define( 'GIVE_STRIPE_BASENAME', plugin_basename( __FILE__ ) );
}

//Licensing
function give_add_stripe_licensing() {
	if ( class_exists( 'Give_License' ) && is_admin() ) {
		$give_stripe_license = new Give_License( __FILE__, 'Stripe Gateway', GIVE_STRIPE_VERSION, 'Devin Walker', 'stripe_license_key' );
	}
}

add_action( 'plugins_loaded', 'give_add_stripe_licensing' );


//Upgrades / Activation hooks
if ( file_exists( dirname( __FILE__ ) . '/includes/give-stripe-upgrades.php' ) ) {
	include( dirname( __FILE__ ) . '/includes/give-stripe-upgrades.php' );
}

/**
 * Internationalization
 *
 * @access      public
 * @since       1.0
 * @return      void
 */
function give_stripe_textdomain() {
	load_plugin_textdomain( 'give-stripe', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action( 'init', 'give_stripe_textdomain' );

/**
 * Register our payment gateway
 *
 * @access      public
 * @since       1.0
 * @return      array
 */

function give_stripe_register_gateway( $gateways ) {
	// Format: ID => Name
	$gateways['stripe'] = array(
		'admin_label'    => 'Stripe',
		'checkout_label' => apply_filters( 'give_stripe_label_text', __( 'Credit Card', 'give-stripe' ) ),
		'supports'       => array(
			'buy_now'
		)
	);

	return $gateways;
}

add_filter( 'give_payment_gateways', 'give_stripe_register_gateway' );


/**
 * Give Stripe Includes
 */
function give_stripe_includes() {
	//Helpers :)
	include( dirname( __FILE__ ) . '/includes/give-stripe-helpers.php' );

	//PreApproval Functionality
	include( dirname( __FILE__ ) . '/includes/give-stripe-preapproval.php' );

	//Processing Functionality
	include( dirname( __FILE__ ) . '/includes/give-stripe-processing.php' );

	//Admin Only
	if ( is_admin() ) {

		include( dirname( __FILE__ ) . '/includes/give-stripe-admin.php' );

		include( dirname( __FILE__ ) . '/includes/give-stripe-activation.php' );

	}
}

add_action( 'plugins_loaded', 'give_stripe_includes' );


/**
 * Load Frontend javascript
 *
 * @access      public
 * @since       1.0
 *
 * @param bool $override
 *
 * @return      void
 */
function give_stripe_js( $override = false ) {
	global $give_options;

	if ( isset( $give_options['stripe_js_fallback'] ) ) {
		return;
	} // in fallback mode

	$publishable_key = null;

	//Which mode are we in?
	if ( give_is_test_mode() ) {
		$publishable_key = isset( $give_options['test_publishable_key'] ) ? trim( $give_options['test_publishable_key'] ) : '';
	} else {
		$publishable_key = isset( $give_options['live_publishable_key'] ) ? trim( $give_options['live_publishable_key'] ) : '';
	}


	if ( give_is_gateway_active( 'stripe' ) ) {

		wp_register_script( 'stripe-js', 'https://js.stripe.com/v2/', array( 'jquery' ) );
		wp_enqueue_script( 'stripe-js' );

		wp_register_script( 'give-stripe-js', GIVE_STRIPE_PLUGIN_URL . 'assets/js/give-stripe.js', array(
			'jquery',
			'stripe-js'
		), GIVE_STRIPE_VERSION );
		wp_enqueue_script( 'give-stripe-js' );

		$stripe_vars = array(
			'publishable_key' => $publishable_key,
			'give_version'    => get_option( 'give_version' ),
		);

		wp_localize_script( 'give-stripe-js', 'give_stripe_vars', $stripe_vars );

	}

}

add_action( 'wp_enqueue_scripts', 'give_stripe_js', 100 );

/**
 * Load Admin javascript
 *
 * @access      admin
 * @since       1.0
 * @return      void
 *
 * @param $hook
 */
function give_stripe_admin_js( $hook ) {

	global $post_type;

	if ( $post_type !== 'give_forms' ) {
		return;
	}

	wp_enqueue_script( 'give-stripe-admin-forms-js', GIVE_STRIPE_PLUGIN_URL . 'assets/js/give-stripe-admin.js', 'jquery', GIVE_STRIPE_VERSION );
	//Localize strings & variables for JS
	wp_localize_script( 'give-stripe-admin-forms-js', 'give_admin_stripe_vars', array(
		'give_version' => GIVE_VERSION,
	) );

}

add_action( 'admin_enqueue_scripts', 'give_stripe_admin_js', 100 );


/**
 * Load Transaction-specific admin javascript
 *
 * @description Allows the user to refund non-recurring purchases
 *
 * @access      public
 * @since       1.0
 * @return      void
 */
function give_stripe_admin_payment_js( $payment_id = 0 ) {

	if ( 'stripe' !== give_get_payment_gateway( $payment_id ) ) {
		return;
	}
	?>
	<script type="text/javascript">
		jQuery( document ).ready( function ( $ ) {
			$( 'select[name=give-payment-status]' ).change( function () {

				if ( 'refunded' == $( this ).val() && $( '.give-recurring-notice' ).length === 0 ) {

					$( this ).parent().parent().append( '<p class="give-stripe-refund"><input type="checkbox" id="give_refund_in_stripe" name="give_refund_in_stripe" value="1"/><label for="give_refund_in_stripe"><?php _e( 'Refund Charge in Stripe?', 'give-recurring' ); ?></label></p>' );

				} else if ( 'refunded' == $( this ).val() && $( '.give-recurring-notice' ).length !== 0 ) {

					$( this ).parent().parent().append( '<p class="give-stripe-refund"><?php _e( 'Recurring donations must be cancelled from within Stripe.', 'give-recurring' ); ?></p>' );


				} else {
					$( '.give-stripe-refund' ).remove();

				}

			} );
		} );
	</script>
	<?php

}

add_action( 'give_view_order_details_before', 'give_stripe_admin_payment_js', 100 );
