<?php
/**
 * Settings
 *
 * @description Registers all the settings required for the plugin.
 *
 * @package     Give PDF Receipts
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'give_et_logo_settings' ) ) :
	/**
	 * Logo Settings
	 *
	 * Registers the settings to enable logo upload to be displayed on the receipt
	 *
	 * @since 1.0
	 *
	 * @param array $settings Array of pre-defined settings
	 *
	 * @return array Merged array with new settings
	 */
	function give_pdf_logo_settings( $settings ) {
		$logo_settings = array(
			array(
				'name' => __( 'Receipt Logo', 'give_pdf' ),
				'id'   => 'give_pdf_email_logo',
				'desc' => __( 'Upload or choose a logo to be displayed at the top of the email.', 'give_pdf' ),
				'type' => 'file'
			)
		);

		return array_merge( $logo_settings, $settings );
	}

	//	add_filter( 'give_settings_emails', 'give_pdf_logo_settings' );
endif;

/**
 * Add PDF Receipts Tab
 *
 * @param $tabs
 */
function give_pdf_receipts_tab( $tabs ) {

	$tabs['pdf_receipts'] = __( 'PDF Receipts', 'give_pdf' );

	return $tabs;
}

add_filter( 'give_settings_tabs', 'give_pdf_receipts_tab', 10, 1 );

/**
 * Generate pdf preview
 */
function give_pdf_receipts_template_preview() {

	//Sanity Checks
	if ( ! isset ( $_GET['give_pdf_receipts_action'] ) || 'preview_pdf' !== $_GET['give_pdf_receipts_action'] ) {
		return;
	}

	//Admin's only
	if ( ! is_admin() ) {
		return;
	}

	// Enable images in pdf
	define( 'DOMPDF_ENABLE_REMOTE', true );
	// Include dompdf library
	include_once( GIVE_PDF_PLUGIN_DIR . '/lib/dompdf/dompdf_config.inc.php' );

	$template_id = give_get_option( 'give_pdf_receipt_template' );

	if ( $template_id ) {

		$template = get_post( $template_id );

		//Sample PDF
		$sample_give_pdf_buyer_info = array(
			'address' => array(
				'line1'   => '123 Sample Road',
				'line2'   => '',
				'city'    => 'San Diego',
				'state'   => 'California',
				'country' => 'US',
				'zip'     => '92101',
			)
		);

		// Create pdf document
		$dompdf = new DOMPDF();
		$dompdf->set_paper( apply_filters( 'give_dompdf_paper_size', array( 0, 0, 595.28, 841.89 ) ) );

		//Output w/ dummy data
		$html = give_pdf_get_compile_html(
			$template->post_content,
			'',
			'Test Donation',
			'Completed',
			'',
			$sample_give_pdf_buyer_info,
			'',
			'97124709170941720971',
			'http://sample.com/receipt-url-example/'
		);

		$dompdf->load_html( $html );
		$dompdf->render();
		$dompdf->stream(
			'Receipt-Preview.pdf',
			array( 'Attachment' => false )
		);

		wp_die();
	}

	return;
}

add_action( 'admin_init', 'give_pdf_receipts_template_preview' );

/**
 * Pdf preview button
 *
 * @param $field_object
 * @param $escaped_value
 * @param $object_id
 * @param $object_type
 * @param $field_type_object
 */
function give_pdf_receipts_preview_button_callback( $field_object ) {
	ob_start();
	?>
	<a href="<?php echo esc_url( add_query_arg( array( 'give_pdf_receipts_action' => 'preview_pdf' ), admin_url() ) ); ?>" class="button-secondary" target="_blank" title="<?php _e( 'Preview PDF', 'give_pdf' ); ?> "><?php _e( 'Preview PDF', 'give_pdf' ); ?></a>
	<p class="cmb2-metabox-description"><?php echo $field_object->args['desc']; ?></p>
	<?php
	echo ob_get_clean();
}

/**
 * Add Settings
 *
 * Adds the new settings for the plugin
 *
 * @since 1.0
 *
 * @param array $settings Array of pre-defined settings
 *
 * @return array Merged array with new settings
 */
function give_pdf_add_settings( $settings ) {

	if ( ! isset( $_GET['tab'] ) || $_GET['tab'] !== 'pdf_receipts' ) {
		return $settings;
	}

	//Remove any excess editor styles
	remove_editor_styles();

	// Register cmb2 type of pdf preview button
	add_action( 'cmb2_render_pdf_receipts_preview_button', 'give_pdf_receipts_preview_button_callback', 10, 1 );

	// Get template
	$template_id = isset( $GLOBALS['give_pdf_receipt_template_id'] ) ? $GLOBALS['give_pdf_receipt_template_id'] : give_get_option( 'give_pdf_receipt_template' );
	$template    = get_post( $template_id );

	$post_title   = '';
	$post_content = '';
	if ( $template ) {
		$post_title   = $template->post_title;
		$post_content = $template->post_content;
	}

	do_action( 'give_pdf_receipts_pre_settings' );

	$pdf_settings = array(
		/**
		 * PDF Settings
		 */
		'id'         => 'pdf_receipts',
		'give_title' => __( 'PDF Receipt Settings', 'give_pdf' ),
		'show_on'    => array( 'key' => 'options-page', 'value' => array( 'give_settings', ), ),
		'fields'     => apply_filters( 'give_settings_pdf_receipts', array(
				array(
					'name' => '<strong>' . __( 'PDF Receipt Settings', 'give_pdf' ) . '</strong>',
					'desc' => '<hr>',
					'id'   => 'give_pdf_settings',
					'type' => 'give_title'
				),
				array(
					'id'      => 'give_pdf_generation_method',
					'name'    => __( 'Generation Method', 'give_pdf' ),
					'desc'    => __( 'Choose the method you would like to generate PDF receipts. The Custom PDF Builder allows you to customize your own templates using a rich editor that allows for custom text, images and HTML to be easily inserted. The Set PDF Templates option will generate PDFs using preconfigured templates.', 'give_pdf' ),
					'type'    => 'select',
					'options' => array(
						'set_pdf_templates'  => __( 'Set PDF Templates', 'give_pdf' ),
						'custom_pdf_builder' => __( 'Custom PDF Builder', 'give_pdf' )
					),
				),
				array(
					'id'   => 'give_pdf_preview_template',
					'name' => __( 'Preview Template', 'give_pdf' ),
					'desc' => __( 'Click the button above to preview how the PDF will appear to donors. Be sure to save your changes prior to previewing. Please note that sample data will be added in the place of your template tags.', 'give_pdf' ),
					'type' => 'pdf_receipts_preview_button'
				),
				array(
					'id'        => 'give_pdf_receipt_template',
					'name'      => __( 'Receipt Template', 'give_pdf' ),
					'desc'      => __( 'Please select a template for your PDF Receipts or create a new custom template using your own HTML and CSS. The selected template is viewable in the PDF Builder field below.', 'give_pdf' ),
					'type'      => 'select',
					'options'   => give_pdf_receipt_cmb2_get_template_options( array(
						'create_new' => __( 'Create new', 'give_pdf' )
					) ),
					'default'   => $template_id,
					'escape_cb' => function () {
					}
				),
				array(
					'id'   => 'give_pdf_receipt_template_name',
					'name' => __( 'Template Name', 'give_pdf' ),
					'desc' => __( 'Please provide your customized receipt template a unique name.', 'give_pdf' ),

					'type'    => 'text',
					'default' => $post_title
				),
				array(
					'id'      => 'give_pdf_builder',
					'name'    => __( 'PDF Builder', 'give_pdf' ),
					'desc'    =>
						__( 'Available template tags:', 'give_pdf' ) . '<br />'
						. '<code>{donation_name}</code>: ' . _x( 'The name of completed donation form', 'An explanation of the {donation_name} template tag', 'give_pdf' ) . '<br />' .
						'<code>{first_name}</code>: ' . _x( 'The donor\'s first name', 'An explanation of the {first_name} template tag', 'give_pdf' ) . '<br />' .
						'<code>{full_name}</code>: ' . _x( 'The donor\'s full name, first and last', 'An explanation of the {full_name} template tag', 'give_pdf' ) . '<br />' .
						'<code>{username}</code>: ' . _x( 'The donor\'s user name on the site, if they registered an account', 'An explanation of the {username} template tag', 'give_pdf' ) . '<br />' .
						'<code>{user_email}</code>: ' . _x( 'The donor\'s email address', 'An explanation of the {user_email} template tag', 'give_pdf' ) . '<br />' .
						'<code>{billing_address}</code>: ' . _x( 'The donor\'s billing address', 'An explanation of the {billing_address} template tag', 'give_pdf' ) . '<br />' .
						'<code>{date}</code>: ' . _x( 'The date of the donation', 'An explanation of the {date} template tag', 'give_pdf' ) . '<br />' .
						'<code>{price}</code>: ' . _x( 'The total price of the donation', 'An explanation of the {price} template tag', 'give_pdf' ) . '<br />' .
						'<code>{payment_id}</code>: ' . _x( 'The unique ID number for this donation', 'An explanation of the {payment_id} template tag', 'give_pdf' ) . '<br />' .
						'<code>{receipt_id}</code>: ' . _x( 'The unique ID number for this donation receipt', 'An explanation of the {receipt_id} template tag', 'give_pdf' ) . '<br />' .
						'<code>{payment_method}</code>: ' . _x( 'The method of payment used for this donation', 'An explanation of the {payment_method} template tag', 'give_pdf' ) . '<br />' .
						'<code>{sitename}</code>: ' . _x( 'Your site name', 'An explanation of the {sitename} template tag', 'give_pdf' ) . '<br />' .
						'<code>{receipt_link}</code>: ' . _x( 'Adds a link so users can view their receipt directly on your website if they are unable to view it in the browser correctly', 'An explanation of the {receipt_link} template tag', 'give_pdf' ) . '<br />' .
						'<code>{transaction_id}</code>: ' . _x( 'The donation transaction ID', 'An explanation of the {transaction_id} template tag', 'give_pdf' ) . '<br />' .
						'<code>{transaction_key}</code>: ' . _x( 'The donation transaction key', 'An explanation of the {transaction_key} template tag', 'give_pdf' ) . '<br />' .
						'<code>{payment_status}</code>: ' . _x( 'Status of the donation', 'An explanation of the {payment_status} template tag', 'give_pdf' ) . '<br />',
					'type'    => 'wysiwyg',
					'default' => $post_content
				),
				array(
					'id'      => 'give_pdf_templates',
					'name'    => __( 'Receipt Template', 'give_pdf' ),
					'desc'    => __( 'Please select a template for your PDF Receipts. This template will be used for all Give PDF Receipts.', 'give_pdf' ),
					'type'    => 'select',
					'options' => apply_filters( 'give_pdf_templates_list', array(
						'default'     => __( 'Default', 'give_pdf' ),
						'blue_stripe' => __( 'Blue Stripe', 'give_pdf' ),
						'lines'       => __( 'Lines', 'give_pdf' ),
						'minimal'     => __( 'Minimal', 'give_pdf' ),
						'traditional' => __( 'Traditional', 'give_pdf' ),
						'blue'        => __( 'Blue', 'give_pdf' ),
						'green'       => __( 'Green', 'give_pdf' ),
						'orange'      => __( 'Orange', 'give_pdf' ),
						'pink'        => __( 'Pink', 'give_pdf' ),
						'purple'      => __( 'Purple', 'give_pdf' ),
						'red'         => __( 'Red', 'give_pdf' ),
						'yellow'      => __( 'Yellow', 'give_pdf' )
					) )
				),
				array(
					'id'   => 'give_pdf_logo_upload',
					'name' => __( 'Logo Upload', 'give_pdf' ),
					'desc' => __( 'Upload your logo here which will show up on the receipt. If the logo is greater than 90px in height, it will not be shown. On the Traditional template, if the logo is greater than 80px in height, it will not be shown. Also note that the logo will be output at 96 dpi.', 'give_pdf' ),
					'type' => 'file'
				),
				array(
					'id'      => 'give_pdf_company_name',
					'name'    => __( 'Company Name', 'give_pdf' ),
					'desc'    => __( 'Enter the company name that will be shown on the receipt.', 'give_pdf' ),
					'type'    => 'text',
					'size'    => 'regular',
					'default' => ''
				),
				array(
					'id'      => 'give_pdf_name',
					'name'    => __( 'Name', 'give_pdf' ),
					'desc'    => __( 'Enter the name that will be shown on the receipt.', 'give_pdf' ),
					'type'    => 'text',
					'default' => ''
				),
				array(
					'id'      => 'give_pdf_address_line1',
					'name'    => __( 'Address Line 1', 'give_pdf' ),
					'desc'    => __( 'Enter the first address line that will appear on the receipt.', 'give_pdf' ),
					'type'    => 'text',
					'default' => ''
				),
				array(
					'id'      => 'give_pdf_address_line2',
					'name'    => __( 'Address Line 2', 'give_pdf' ),
					'desc'    => __( 'Enter the second address line that will appear on the receipt.', 'give_pdf' ),
					'type'    => 'text',
					'default' => ''
				),
				array(
					'id'      => 'give_pdf_address_city_state_zip',
					'name'    => __( 'City, State and Zip Code', 'give_pdf' ),
					'desc'    => __( 'Enter the city, state and zip code that will appear on the receipt.', 'give_pdf' ),
					'type'    => 'text',
					'default' => ''
				),
				array(
					'id'      => 'give_pdf_email_address',
					'name'    => __( 'Email Address', 'give_pdf' ),
					'desc'    => __( 'Enter the email address that will appear on the receipt.', 'give_pdf' ),
					'type'    => 'text',
					'default' => get_option( 'admin_email' )
				),
				array(
					'id'   => 'give_pdf_url',
					'name' => __( 'Show website address?', 'give_pdf' ),
					'desc' => __( 'Check this box if you would like your website address to be shown.', 'give_pdf' ),
					'type' => 'checkbox'
				),
				array(
					'id'   => 'give_pdf_header_message',
					'name' => __( 'Header Message', 'give_pdf' ),
					'desc' => __( 'Enter the message you would like to be shown on the header of the receipt. Please note that the header will not show up on the Blue Stripe and Traditional template.', 'give_pdf' ),
					'type' => 'text',
				),
				array(
					'id'   => 'give_pdf_footer_message',
					'name' => __( 'Footer Message', 'give_pdf' ),
					'desc' => __( 'Enter the message you would like to be shown on the footer of the receipt.', 'give_pdf' ),
					'type' => 'text',
				),
				array(
					'id'   => 'give_pdf_additional_notes',
					'name' => __( 'Additional Notes', 'give_pdf' ),
					'desc' => __( 'Enter any messages you would to be displayed at the end of the receipt. Only plain text is currently supported. Any HTML will not be shown on the receipt.', 'give_pdf' ) . __( 'The following template tags will work for the Header and Footer message as well as the Additional Notes:', 'give_pdf' ) . '<br />' . __( '{page} - Page Number', 'give_pdf' ) . '<br />' . __( '{sitename} - Site Name', 'give_pdf' ) . '<br />' . __( '{today} - Date of Receipt Generation', 'give_pdf' ) . '<br />' . __( '{date} - Receipt Date', 'give_pdf' ) . '<br />' . __( '{receipt_id} - Receipt ID', 'give_pdf' ),
					'type' => 'textarea'
				),
				array(
					'id'   => 'give_pdf_enable_char_support',
					'name' => __( 'Characters not displaying correctly?', 'give_pdf' ),
					'desc' => __( 'Check to enable the Droid Sans Full font replacing Open Sans/Helvetica/Times. Enable this option if you have characters which do not display correctly (e.g. Greek characters, Japanese, Mandarin, etc.)', 'give_pdf' ),
					'type' => 'checkbox',
				)
			)
		)
	);

	return array_merge( $settings, $pdf_settings );

}

add_filter( 'give_registered_settings', 'give_pdf_add_settings' );


/**
 * Save pdf template
 *
 * @description: Save template data only when generation method is custom_pdf_builder
 */

// Save template data only when generation method is custom_pdf_builder
if ( isset( $_POST['give_pdf_generation_method'] ) && $_POST['give_pdf_generation_method'] == 'custom_pdf_builder' ) {
	add_action( 'admin_init', 'give_pdf_receipts_save_template' );
}

function give_pdf_receipts_save_template() {

	//Sanity Check: Ensure we're only using custom_pdf_builder
	if ( isset( $_POST['give_pdf_generation_method'] ) && $_POST['give_pdf_generation_method'] !== 'custom_pdf_builder' ) {
		return;
	}

	// Get request values
	$template_id      = isset( $_POST['give_pdf_receipt_template'] ) ? $_POST['give_pdf_receipt_template'] : '';
	$template_name    = isset( $_POST['give_pdf_receipt_template_name'] ) ? $_POST['give_pdf_receipt_template_name'] : '';
	$template_content = isset( $_POST['give_pdf_builder'] ) ? $_POST['give_pdf_builder'] : '';

	//Sanity check: Template name can't be empty
	if ( empty( $template_id ) ) {
		return;
	}

	$existing_template = get_page_by_title( $template_name, OBJECT, 'Give_PDF_Template' );

	$post = array(
		'post_title'     => $template_name,
		'post_content'   => $template_content,
		'post_type'      => 'Give_PDF_Template',
		'ping_status'    => 'closed',
		'comment_status' => 'closed',
		'post_status'    => 'publish'
	);

	// Add or update template
	if ( $template_id == 'create_new' || empty( $existing_template ) ) {
		$id = wp_insert_post( $post );
		// Save inserted template id
		$GLOBALS['give_pdf_receipt_template_id'] = $id;
	} else {

		//Existing template, update the post
		$template = get_post( $template_id );

		// Disable modify default templates
		switch ( $template->post_status ) {
			case 'draft':
				// Create new template when title modified
				if ( $template->post_title != $template_name ) {
					$template_id = wp_insert_post( $post );
				}
				break;
			case 'publish':
				$post['ID'] = $template_id;
				wp_update_post( $post );
				break;
		}
		$GLOBALS['give_pdf_receipt_template_id'] = $template_id;
	}

}

/**
 * Remove Template Fields
 *
 * @description: Applies to DOM2PDF saving functionality
 */
function give_pdf_receipts_remove_template_fields() {

	// If created new template then need save template id
	$template_id = isset( $_POST['give_pdf_receipt_template_id'] ) ? $_POST['give_pdf_receipt_template_id'] : '';

	//Template ID
	if ( ! empty( $template_id ) ) {
		give_update_option( 'give_pdf_receipt_template', $_POST['give_pdf_receipt_template_id'] );
	}

}

add_action( 'cmb2_save_options-page_fields', 'give_pdf_receipts_remove_template_fields', 10, 3 );


/**
 * Get all templates
 *
 * @param array $list_items Templates list
 *
 * @return array
 */
function give_pdf_receipt_cmb2_get_template_options( $list_items ) {

	$posts = get_posts( array(
			'post_type'      => 'Give_PDF_Template',
			'post_status'    => array( 'draft', 'publish' ),
			'posts_per_page' => - 1
		)
	);
	foreach ( $posts as $post ) {
		$list_items[ $post->ID ] = $post->post_title;
	}

	return $list_items;
}

/**
 * Text Callback
 *
 * Renders text fields.
 *
 * @since       1.0
 */
function give_pdfi_plain_text_callback( $args ) {
	echo $args['desc'];
}