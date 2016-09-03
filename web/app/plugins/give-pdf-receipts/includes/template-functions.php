<?php
/**
 * Template Functions
 *
 * @description: All the template functions for the PDF receipt when they are being built or generated.
 *
 * @package Give PDF Receipts
 * @since   1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Settings
 *
 * Gets the settings for PDF Receipts plugin if they exist.
 *
 * @since 1.0
 *
 * @param object $give_pdf PDF receipt object
 * @param string $setting Setting name
 *
 * @return string Returns option if it exists.
 */
function give_pdf_get_settings( $give_pdf, $setting ) {
	global $give_options;

	$give_pdf_payment = get_post( $_GET['transaction_id'] );

	if ( 'name' == $setting ) {
		if ( isset( $give_options['give_pdf_name'] ) ) {
			return $give_options['give_pdf_name'];
		}
	}

	if ( 'addr_line1' == $setting ) {
		if ( isset( $give_options['give_pdf_address_line1'] ) ) {
			return $give_options['give_pdf_address_line1'];
		}
	}

	if ( 'addr_line2' == $setting ) {
		if ( isset( $give_options['give_pdf_address_line2'] ) ) {
			return $give_options['give_pdf_address_line2'];
		}
	}

	if ( 'city_state_zip' == $setting ) {
		if ( isset( $give_options['give_pdf_address_city_state_zip'] ) ) {
			return $give_options['give_pdf_address_city_state_zip'];
		}
	}

	if ( 'email' == $setting ) {
		if ( isset( $give_options['give_pdf_email_address'] ) ) {
			return $give_options['give_pdf_email_address'];
		}
	}

	if ( 'notes' == $setting ) {
		if ( isset( $give_options['give_pdf_additional_notes'] ) && ! empty( $give_options['give_pdf_additional_notes'] ) ) {
			$give_pdf_additional_notes = $give_options['give_pdf_additional_notes'];
			$give_pdf_additional_notes = str_replace( '{page}', 'Page' . $give_pdf->getPage(), $give_pdf_additional_notes );
			$give_pdf_additional_notes = str_replace( '{sitename}', get_bloginfo( 'name' ), $give_pdf_additional_notes );
			$give_pdf_additional_notes = str_replace( '{today}', date_i18n( get_option( 'date_format' ), time() ), $give_pdf_additional_notes );
			$give_pdf_additional_notes = str_replace( '{date}', date_i18n( get_option( 'date_format' ), strtotime( $give_pdf_payment->post_date ) ), $give_pdf_additional_notes );
			$give_pdf_additional_notes = str_replace( '{receipt_id}', give_pdf_get_payment_number( $give_pdf_payment->ID ), $give_pdf_additional_notes );
			$give_pdf_additional_notes = strip_tags( $give_pdf_additional_notes );
			$give_pdf_additional_notes = stripslashes_deep( html_entity_decode( $give_pdf_additional_notes, ENT_COMPAT, 'UTF-8' ) );

			return $give_pdf_additional_notes;
		}
	}

	return '';
}

/**
 * Calculate Line Heights
 *
 * Calculates the line heights for the 'To' block
 *
 * @since 1.0
 *
 * @param string $setting Setting name.
 *
 * @return string Returns line height.
 */
function give_pdf_calculate_line_height( $setting ) {
	global $give_options;

	if ( empty( $setting ) ) {
		return 0;
	} else {
		return 6;
	}
}

/**
 *
 * Retrieve the payment number
 *
 * @description If sequential order numbers are enabled, this returns the order numbered
 *
 * @since       1.0
 *
 * @param int $payment_id
 *
 * @return int|string
 */
function give_pdf_get_payment_number( $payment_id = 0 ) {
	if ( function_exists( 'give_get_payment_number' ) ) {
		return give_get_payment_number( $payment_id );
	} else {
		return $payment_id;
	}
}

/**
 * Create html content by template
 *
 * @param string $template_content Template content
 * @param WP_Post $give_pdf_payment
 * @param string $give_pdf_payment_method
 * @param string $give_pdf_payment_status
 * @param array $give_pdf_payment_meta
 * @param array $give_pdf_buyer_info
 * @param string $give_pdf_payment_date
 * @param int $transaction_id
 * @param string $receipt_link
 *
 * @return string Returns html content
 */
function give_pdf_get_compile_html( $template_content, $give_pdf_payment, $give_pdf_payment_method, $give_pdf_payment_status, $give_pdf_payment_meta, $give_pdf_buyer_info, $give_pdf_payment_date, $transaction_id, $receipt_link ) {

	$payment_id           = isset( $give_pdf_payment->ID ) ? $give_pdf_payment->ID : '';
	$give_pdf_total_price = ! empty( $payment_id ) ? html_entity_decode( give_currency_filter( give_format_amount( give_get_payment_amount( $payment_id ) ) ), ENT_COMPAT, 'UTF-8' ) : '$2.00';
	$user_info            = isset( $give_pdf_buyer_info['id'] ) ? get_userdata( $give_pdf_buyer_info['id'] ) : '';

	$billing_address = '';
	if ( ! empty( $give_pdf_buyer_info['address'] ) ) {
		$billing_address .= $give_pdf_buyer_info['address']['line1'] . '<br/> ';
		if ( ! empty( $give_pdf_buyer_info['address']['line2'] ) ) {
			$billing_address .= $give_pdf_buyer_info['address']['line2'] . '<br/>  ';
		}
		$billing_address .= $give_pdf_buyer_info['address']['city'] . ',  ' . $give_pdf_buyer_info['address']['state'] . ' ' . $give_pdf_buyer_info['address']['zip'] . '<br/>  ';
		if ( ! empty( $give_pdf_buyer_info['address']['country'] ) ) {
			$countries = give_get_country_list();
			$country   = isset( $countries[ $give_pdf_buyer_info['address']['country'] ] ) ? $countries[ $give_pdf_buyer_info['address']['country'] ] : $give_pdf_buyer_info['address']['country'];
			$billing_address .= $country;
		}
	}

	$receipt_id            = isset( $give_pdf_payment->ID ) ? give_pdf_get_payment_number( $give_pdf_payment->ID ) : '123456789';
	$transaction_key       = isset( $give_pdf_payment_meta['key'] ) ? $give_pdf_payment_meta['key'] : '90120939030939239';
	$payment_id            = isset( $give_pdf_payment->ID ) ? $give_pdf_payment->ID : '123456789';
	$full_name             = ( isset( $give_pdf_buyer_info['first_name'] ) && isset( $give_pdf_buyer_info['last_name'] ) ) ? $give_pdf_buyer_info['first_name'] . ' ' . $give_pdf_buyer_info['last_name'] : 'John Doe';
	$give_pdf_payment_date = ! empty( $give_pdf_payment_date ) ? $give_pdf_payment_date : current_time( get_option( 'date_format' ) );
	$transaction_id        = isset( $transaction_id ) ? $transaction_id : '123456789';
	$user_email            = isset( $give_pdf_buyer_info['email'] ) ? $give_pdf_buyer_info['email'] : 'my.email@email.com';
	$username              = isset( $user_info->user_login ) ? $user_info->user_login : __( 'No Username Found', 'give_pdf' );

	// Replace tags
	$template_content = str_replace( '{donation_name}', isset( $give_pdf_payment_meta['form_title'] ) ? $give_pdf_payment_meta['form_title'] : __( 'Untitled Donation Form', 'give_pdf' ), $template_content );
	$template_content = str_replace( '{first_name}', isset( $give_pdf_buyer_info['first_name'] ) ? $give_pdf_buyer_info['first_name'] : 'John', $template_content );
	$template_content = str_replace( '{full_name}', $full_name, $template_content );
	$template_content = str_replace( '{username}', $username, $template_content );
	$template_content = str_replace( '{user_email}', $user_email, $template_content );
	$template_content = str_replace( '{billing_address}', $billing_address, $template_content );
	$template_content = str_replace( '{date}', $give_pdf_payment_date, $template_content );
	$template_content = str_replace( '{price}', $give_pdf_total_price, $template_content );
	$template_content = str_replace( '{payment_id}', $payment_id, $template_content );
	$template_content = str_replace( '{receipt_id}', $receipt_id, $template_content );
	$template_content = str_replace( '{payment_method}', $give_pdf_payment_method, $template_content );
	$template_content = str_replace( '{sitename}', get_bloginfo( 'name' ), $template_content );
	$template_content = str_replace( '{receipt_link}', $receipt_link, $template_content );
	$template_content = str_replace( '{transaction_id}', $transaction_id, $template_content );
	$template_content = str_replace( '{transaction_key}', $transaction_key, $template_content );
	$template_content = str_replace( '{payment_status}', $give_pdf_payment_status, $template_content );

	//Wrap in proper HTML5 template tags
	$template_content = apply_filters( 'give_pdf_header', '<!DOCTYPE html>
			<html lang="en">
			  <head>
			    <meta charset="utf-8">
			    <title>Example 1</title>
			    <style>html, body{margin: 0; padding: 0; }</style>
			  </head>
			  <body>' ) . $template_content . apply_filters( 'give_pdf_footer', '</body></html>' );

	return apply_filters( 'give_pdf_get_template_content', $template_content );

}