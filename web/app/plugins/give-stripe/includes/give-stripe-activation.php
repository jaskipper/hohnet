<?php
/**
 *  Give Stripe Gateway Activation
 *
 * @description: When the Add-on activates show a banner, check for Give Core, and do other things
 * @copyright  : http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since      : 1.3
 */


/**
 * Give Stripe Activation Banner
 *
 * @description: Includes and initializes the activation banner class; only runs in WP admin
 * @hook       admin_init
 */
function give_stripe_activation_banner() {

	if ( defined( 'GIVE_PLUGIN_FILE' ) ) {
		$give_plugin_basename = plugin_basename( GIVE_PLUGIN_FILE );
		$is_give_active       = is_plugin_active( $give_plugin_basename );
	} else {
		$is_give_active = false;
	}

	//Check to see if Give is activated, if it isn't deactivate and show a banner
	if ( is_admin() && current_user_can( 'activate_plugins' ) && ! $is_give_active ) {

		add_action( 'admin_notices', 'give_stripe_child_plugin_notice' );

		//Don't let this plugin activate
		deactivate_plugins( GIVE_STRIPE_BASENAME );

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		return false;

	}

	//Sanity Check for activation banner inclusion
	if ( ! class_exists( 'Give_Addon_Activation_Banner' ) && file_exists( GIVE_PLUGIN_DIR . 'includes/admin/class-addon-activation-banner.php' ) ) {
		include GIVE_PLUGIN_DIR . 'includes/admin/class-addon-activation-banner.php';
	} else {
		//bail if class & file not found
		return false;
	}

	//Only runs on admin
	$args = array(
		'file'              => __FILE__,
		//Directory path to the main plugin file
		'name'              => __( 'Stripe Gateway', 'give-stripe' ),
		//name of the Add-on
		'version'           => GIVE_STRIPE_VERSION,
		//The most current version
		'documentation_url' => 'https://givewp.com/documentation/add-ons/stripe-gateway/',
		'support_url'       => 'https://givewp.com/support/',
		'settings_url'      => admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways' ),
		//Location of Add-on settings page, leave blank to hide
		'testing'           => false,
		//Never leave as "true" in production!!!
	);

	new Give_Addon_Activation_Banner( $args );

	return false;
}

add_action( 'admin_init', 'give_stripe_activation_banner' );


/**
 * Notice for No Core Activation
 */
function give_stripe_child_plugin_notice() {

	echo '<div class="error"><p>' . __( '<strong>Activation Error:</strong> We noticed Give is not active. Please activate Give in order to use the Stripe Gateway.', 'give-stripe' ) . '</p></div>';
}