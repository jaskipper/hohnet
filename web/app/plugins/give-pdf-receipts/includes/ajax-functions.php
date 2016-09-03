<?php
/**
 * AJAX Functions
 *
 * Process the AJAX actions.
 *
 * @package Give - PDF Receipts
 * @since   2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get template data by template id
 */
function get_builder_content() {

	$template_id = $_POST['template_id'];
	$post = get_post( $template_id );

	echo json_encode( array(
		'post_title'   => $post->post_title,
		'post_content' => $post->post_content) );

	wp_die();
}

add_action( 'wp_ajax_get_builder_content', 'get_builder_content' );