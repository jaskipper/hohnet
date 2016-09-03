<?php
/**
 * Scripts
 *
 * @description Registers js scripts and css styles
 *
 * @package     Give PDF Receipts
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Load scripts
 */
function give_pdf_receipts_load_admin_scripts($hook) {
	$js_dir = GIVE_PDF_PLUGIN_URL . 'assets/js/';
	$css_dir = GIVE_PDF_PLUGIN_URL . 'assets/css/';

	//Only load on PDF Receipts Tab
	if($hook !== 'give_forms_page_give-settings' || ! isset( $_GET['tab'] ) || $_GET['tab'] !== 'pdf_receipts' ) {
		return;
	}

	//Editor Styles
	add_filter( 'mce_css', 'give_pdf_receipts_filter_mce_css' );

	//CSS
	wp_register_style('give_admin_pdf_receipt_css', $css_dir . 'admin-style.css', false, GIVE_VERSION);
	wp_enqueue_style( 'give_admin_pdf_receipt_css');

	//JS
	wp_register_script( 'give_admin_pdf_receipt_js', $js_dir . 'admin-forms.js', array( 'jquery' ), GIVE_VERSION, false );

	// Localize the script with new data
	$ajax_data = array(
		'not_saved' => __( 'You haven\'t saved the template yet. Are you sure you want to proceed?', 'give_pdf' ),
		'template_customized' => __( 'Please provide a unique receipt template name for this PDF receipt in order to apply your changes.', 'give_pdf' )
	);
	wp_localize_script( 'give_admin_pdf_receipt_js', 'give_pdf_vars', $ajax_data );

	wp_enqueue_script( 'give_admin_pdf_receipt_js' );



}

add_action( 'admin_enqueue_scripts', 'give_pdf_receipts_load_admin_scripts' );



function give_pdf_receipts_filter_mce_css( $mce_css ) {

	$mce_css .= ', ' . GIVE_PDF_PLUGIN_URL . 'assets/css/admin-pdf-tinymce.css';

	return $mce_css;

}