<?php
/**
 * Plugin Name: Give - Dwolla Gateway
 * Plugin URI: http://www.designwritebuild.com/give/dwolla/
 * Description: Accept donations in Give using your Dwolla merchant account.
 * Author: WordImpress
 * Author URI: http://givewp.com
 * Version: 1.1
 * Text Domain: give_dwolla
 * Domain Path: languages
 *
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

/**
 * Dwolla Licensing
 */
function give_add_dwolla_licensing() {
	if ( class_exists( 'Give_License' ) && is_admin() ) {
		$give_dwolla_license = new Give_License( __FILE__, 'Dwolla Gateway', '1.1', 'WordImpress', 'dwolla_license_key' );
	}
}

add_action( 'plugins_loaded', 'give_add_dwolla_licensing' );

/**
 * Dwolla i18n
 */
function give_dwolla_textdomain() {
	// Set filter for plugin's languages directory
	$give_lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
	$give_lang_dir = apply_filters( 'give_dwolla_languages_directory', $give_lang_dir );

	// Load the translations
	load_plugin_textdomain( 'give_dwolla', false, $give_lang_dir );
}

add_action( 'init', 'give_dwolla_textdomain' );

/**
 * Give Dwolla Includes
 */
function give_dwolla_includes() {

	//Admin Only
	if ( is_admin() ) {
		include( dirname( __FILE__ ) . '/includes/give-admin-notices.php' );
	}
}

add_action( 'plugins_loaded', 'give_dwolla_includes' );



/**
 *
 * @param $gateways
 *
 * @return mixed
 */
function give_dwolla_register_gateway( $gateways ) {
	$gateways['dwolla'] = array(
		'admin_label'    => 'Dwolla',
		'checkout_label' => apply_filters('give_dwolla_label_text', __( 'Dwolla Account', 'give_dwolla' ) )
	);

	return $gateways;
}

add_filter( 'give_payment_gateways', 'give_dwolla_register_gateway' );

/**
 * Dwolla Settings
 *
 * @param $settings
 *
 * @return array
 */
function give_dwolla_add_settings( $settings ) {

	$gateway_settings = array(
		array(
			'id'   => 'dwolla_settings',
			'name' => '<strong>' . __( 'Dwolla Settings', 'give_dwolla' ) . '</strong>',
			'desc' => '<hr>',
			'type' => 'give_title',
		),
		array(
			'id'   => 'dwolla_account_number',
			'name' => __( 'Dwolla ID', 'give_dwolla' ),
			'desc' => __( 'Enter your Dwolla ID (account number).', 'give_dwolla' ),
			'type' => 'text',
		),
		array(
			'id'   => 'dwolla_api_key',
			'name' => __( 'API Key', 'give_dwolla' ),
			'desc' => __( 'Enter your Dwolla application key.', 'give_dwolla' ),
			'type' => 'text',
		),
		array(
			'id'   => 'dwolla_api_secret',
			'name' => __( 'API Secret', 'give_dwolla' ),
			'desc' => __( 'Enter your Dwolla application secret.', 'give_dwolla' ),
			'type' => 'text',
		)
	);

	return array_merge( $settings, $gateway_settings );
}

add_filter( 'give_settings_gateways', 'give_dwolla_add_settings' );

/**
 * Remove CC form
 */
function give_dwolla_remove_cc_form() {

	global $give_options;

	if ( ! isset( $give_options['dwolla_api_key'] ) || ! isset( $give_options['dwolla_api_secret'] ) ) { ?>
		<div class="error"><?php _e( 'You must add your API key and secret before you can continue.', 'give_dwolla' ); ?></div>
		<?php return;
	}

}

add_action( 'give_dwolla_cc_form', 'give_dwolla_remove_cc_form' );


/**
 * Dwolla Donation Processing
 *
 * @description: Processes a Dwolla donation
 *
 * @param $purchase_data
 */
function give_dwolla_process_payment( $purchase_data ) {
	require 'dwolla/lib/dwolla.php';
	global $give_options;

	$purchase_summary = give_get_purchase_summary( $purchase_data );

	$payment_data = array(
		'price'           => $purchase_data['price'],
		'give_form_title' => $purchase_data['post_data']['give-form-title'],
		'give_form_id'    => intval( $purchase_data['post_data']['give-form-id'] ),
		'price_id'        => isset( $purchase_data['post_data']['give-price-id'] ) ? intval( $purchase_data['post_data']['give-price-id'] ) : '',
		'date'            => $purchase_data['date'],
		'user_email'      => $purchase_data['user_email'],
		'purchase_key'    => $purchase_data['purchase_key'],
		'currency'        => $give_options['currency'],
		'user_info'       => $purchase_data['user_info'],
		'status'          => 'pending',
		'gateway'         => 'dwolla'
	);

	$payment = give_insert_payment( $payment_data );

	$dwolla = new DwollaRestClient( $give_options['dwolla_api_key'], $give_options['dwolla_api_secret'], add_query_arg( 'payment-confirmation', 'dwolla', get_permalink( $give_options['success_page'] ) ) );

	if ( give_is_test_mode() ) {
		$dwolla->apiServerUrl = $dwolla::SANDBOX_SERVER;
	}
	$dwolla->startGatewaySession();

	$dwolla->addGatewayProduct( $payment_data['give_form_title'], $payment_data['price'], 1, $payment_data['give_form_id'] );

	$url = $dwolla->getGatewayURL( $give_options['dwolla_account_number'], $payment, 0, 0, 0, $purchase_summary, get_home_url() );

	wp_redirect( $url );
	exit;
}

add_action( 'give_gateway_dwolla', 'give_dwolla_process_payment' );

/**
 * Verify Payment
 *
 * @description Responsible for checking payment confimation and recording gateway errors (if any)
 */
function give_dwolla_verify_payment() {

	if ( isset( $_GET['payment-confirmation'] ) && $_GET['payment-confirmation'] == 'dwolla' ) {

		global $give_options;
		require 'dwolla/lib/dwolla.php';

		$dwolla = new DwollaRestClient( $give_options['dwolla_api_key'], $give_options['dwolla_api_secret'], add_query_arg( 'payment-confirmation', 'dwolla', get_permalink( $give_options['success_page'] ) ) );

		if ( isset( $_GET['orderId'] ) && isset( $_GET['status'] ) ) {
			if ( $_GET['status'] == 'Completed' ) {
				if ( $_GET['signature'] == hash_hmac( 'sha1', $_GET['checkoutId'] . '&' . $_GET['amount'], $give_options['dwolla_api_secret'] ) ) {
					give_update_payment_status( $_GET['orderId'], 'complete' );
				} else {
					give_record_gateway_error( __( 'Invalid Signature', 'give_dwolla' ), sprintf( __( 'The transaction signature could not be verified. POST Data: %s', 'give_dwolla' ), json_encode( $_GET ), $_GET['orderid'] ) );
				}
			} else {
				give_record_gateway_error( __( 'Transaction Failed', 'give_dwolla' ), sprintf( __( 'Transaction status did not return complete. POST Data: %s', 'give_dwolla' ), json_encode( $_GET ), $_GET['orderid'] ) );
			}
		}
	}
}

add_action( 'init', 'give_dwolla_verify_payment' );
