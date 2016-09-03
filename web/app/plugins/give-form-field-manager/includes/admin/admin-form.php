<?php
/**
 * Admin Form UI Builder - Give_FFM_Admin_Form
 *
 * @package     Give_FFM
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_FFM_Admin_Form
 *
 * Admin Form UI Builder
 */
class Give_FFM_Admin_Form {

	/**
	 * @var string
	 */
	private $form_data_key = 'give-form-fields';

	/**
	 * @var string
	 */
	private $form_settings_key = 'give-form-fields_settings';

	/**
	 * Add necessary actions and filters
	 */
	function __construct() {
		add_filter( 'post_updated_messages', array( $this, 'form_updated_message' ) );
		add_action( 'save_post', array( $this, 'save_form_meta' ), 1, 2 );

		// meta boxes
		add_action( 'add_meta_boxes_give_forms', array( $this, 'add_meta_boxes' ) );

		// ajax actions for post forms
		add_action( 'wp_ajax_give-form-fields_dump', array( $this, 'form_dump' ) );
		add_action( 'wp_ajax_give-form-fields_add_el', array( $this, 'ajax_post_add_element' ) );
	}

	/**
	 * Returns the default form fields available with the Give Form
	 *
	 * @return array
	 */
	function get_default_form_fields() {
		$data = array(
			1 => array(
				'input_type'  => 'email',
				'template'    => 'give_email',
				'required'    => 'yes',
				'label'       => 'Email',
				'name'        => 'give_email',
				'is_meta'     => 'no',
				'help'        => 'We will send the purchase receipt to this address.',
				'css'         => '',
				'placeholder' => '',
				'default'     => '',
				'size'        => '40'
			),
			2 => array(
				'input_type'  => 'text',
				'template'    => 'give_first',
				'required'    => 'yes',
				'label'       => 'First Name',
				'name'        => 'give_first',
				'is_meta'     => 'no',
				'help'        => 'We will use this to personalize your account experience.',
				'css'         => '',
				'placeholder' => '',
				'default'     => '',
				'size'        => '40'
			),
			3 => array(
				'input_type'  => 'text',
				'template'    => 'give_last',
				'required'    => 'yes',
				'label'       => 'Last Name',
				'name'        => 'give_last',
				'is_meta'     => 'no',
				'help'        => 'We will use this as well to personalize your account experience.',
				'css'         => '',
				'placeholder' => '',
				'default'     => '',
				'size'        => '40'
			)
		);

		return $data;
	}

	/**
	 *  Checks if we are in admin edit post or new post
	 *
	 * @return  boolean
	 */
	function is_edit_page( $new_edit = null ) {
		global $pagenow;

		if ( ! is_admin() ) {
			return false;
		}

		if ( $new_edit == 'edit' ) {
			return in_array( $pagenow, array( 'post.php' ) );
		} elseif ( $new_edit == 'new' ) {
			return in_array( $pagenow, array( 'post-new.php' ) );
		} else {
			return in_array( $pagenow, array( 'post.php', 'post-new.php' ) );
		}
	}

	/**
	 * Form Updated Message
	 *
	 * @param $messages
	 *
	 * @return mixed
	 */
	function form_updated_message( $messages ) {
		$message = array(
			0  => '',
			1  => __( 'Donation form fields updated!', 'give-ffm' ),
			2  => __( 'Custom field updated.', 'give-ffm' ),
			3  => __( 'Custom field deleted.', 'give-ffm' ),
			4  => __( 'Donation form updated.', 'give-ffm' ),
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Form restored to revision from %s', 'give-ffm' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => __( 'Donation form published.', 'give-ffm' ),
			7  => __( 'Donation form fields saved!', 'give-ffm' ),
			8  => __( 'Donation form submitted.', 'give-ffm' ),
			9  => '',
			10 => __( 'Donation form draft updated.', 'give-ffm' ),
		);

		$messages['give-form-fields'] = $message;

		return $messages;
	}

	/**
	 * Add meta boxes to form builders
	 *
	 * @return void
	 */
	function add_meta_boxes() {
		global $post;
		add_meta_box( 'ffm-metabox-editor', __( 'Form Field Manager', 'give-ffm' ), array(
			$this,
			'metabox_post_form'
		), 'give_forms', 'normal', 'high' );
	}

	/**
	 * Metabox Post Form
	 *
	 * @param $post
	 */
	function metabox_post_form( $post ) {
		?>
		<div class="tab-content">
			<div id="ffm-metabox" class="group">
				<?php $this->edit_form_area(); ?>
			</div>
			<?php do_action( 'ffm_post_form_tab_content' ); ?>
		</div>
		<?php
	}

	/**
	 * Form elements for post form builder
	 *
	 * @return void
	 */
	function form_elements_post() {
		$title = __( 'Click to add to the editor', 'give-ffm' );
		?>
		<div class="ffm-loading"><span class="give-icon give-icon-loading give-icon-spinner2 fa-spin"></span></div>
		<div class="give-form-fields-buttons">
			<?php do_action( 'give_form_field_buttons_before' ); ?>
			<button class="ffm-button button" data-name="custom_text" data-type="text" title="<?php echo $title; ?>"><?php _e( 'Text', 'give-ffm' ); ?></button>
			<button class="ffm-button button" data-name="custom_textarea" data-type="textarea" title="<?php echo $title; ?>"><?php _e( 'Textarea', 'give-ffm' ); ?></button>
			<button class="ffm-button button" data-name="custom_select" data-type="select" title="<?php echo $title; ?>"><?php _e( 'Dropdown', 'give-ffm' ); ?></button>
			<button class="ffm-button button" data-name="custom_date" data-type="date" title="<?php echo $title; ?>"><?php _e( 'Date', 'give-ffm' ); ?></button>
			<button class="ffm-button button" data-name="custom_radio" data-type="radio" title="<?php echo $title; ?>"><?php _e( 'Radio', 'give-ffm' ); ?></button>
			<button class="ffm-button button" data-name="custom_checkbox" data-type="checkbox" title="<?php echo $title; ?>"><?php _e( 'Checkbox', 'give-ffm' ); ?></button>
			<button class="ffm-button button" data-name="custom_email" data-type="email" title="<?php echo $title; ?>"><?php _e( 'Email', 'give-ffm' ); ?></button>
			<button class="ffm-button button" data-name="custom_file" data-type="file" title="<?php echo $title; ?>"><?php _e( 'File Upload', 'give-ffm' ); ?></button>
			<button class="ffm-button button" data-name="custom_url" data-type="url" title="<?php echo $title; ?>"><?php _e( 'URL', 'give-ffm' ); ?></button>
			<button class="ffm-button button" data-name="custom_multiselect" data-type="multiselect" title="<?php echo $title; ?>"><?php _e( 'Multi Select', 'give-ffm' ); ?></button>
			<button class="ffm-button button" data-name="custom_repeater" data-type="repeat" title="<?php echo $title; ?>"><?php _e( 'Repeat Field', 'give-ffm' ); ?></button>
			<button class="ffm-button button" data-name="custom_html" data-type="html" title="<?php echo $title; ?>"><?php _e( 'HTML', 'give-ffm' ); ?></button>
			<button class="ffm-button button" data-name="custom_section" data-type="section" title="<?php echo $title; ?>"><?php _e( 'Section', 'give-ffm' ); ?></button>
			<button class="ffm-button button" data-name="action_hook" data-type="action" title="<?php echo $title; ?>"><?php _e( 'Do Action', 'give-ffm' ); ?></button>
			<?php do_action( 'give_form_field_buttons_after' ); ?>
		</div>
		<?php
	}

	/**
	 * Saves the form settings
	 *
	 * @param int $post_id
	 * @param object $post
	 *
	 * @return int|void
	 */
	function save_form_meta( $post_id, $post ) {

		if ( ! isset( $_POST['give-form-fields_editor'] ) ) {
			return $post->ID;
		}

		if ( ! wp_verify_nonce( $_POST['give-form-fields_editor'], plugin_basename( __FILE__ ) ) ) {
			return $post->ID;
		}

		// Is the user allowed to edit the post or page?
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return $post->ID;
		}

		//Update form field placement
		if ( isset( $_POST['_give_ffm_placement'] ) ) {
			update_post_meta( $post->ID, '_give_ffm_placement', $_POST['_give_ffm_placement'] );
		}

		//Update ffm input
		if ( isset( $_POST['ffm_input'] ) ) {
			update_post_meta( $post->ID, $this->form_data_key, $_POST['ffm_input'] );
		} else {
			//No form fields, set as empty meta so no blank fields leftover
			update_post_meta( $post->ID, $this->form_data_key, '' );
		}


	}

	/**
	 * Edit form elements area for post
	 *
	 * @global object $post
	 * @global string $pagenow
	 */
	function edit_form_area() {
		global $post, $pagenow, $current_screen;

		if ( $current_screen->post_type == 'give_forms' && $current_screen->action == 'add' ) {
			$form_inputs = '';
		} else {
			$form_inputs = get_post_meta( $post->ID, $this->form_data_key, true );
		} ?>

		<div class="form-edit-area-header">
			<?php $this->form_elements_post(); ?>

			<input type="hidden" name="give-form-fields_editor" id="give-form-fields_editor" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>"/>

			<div class="form-edit-header-info">

				<p class="cmb2-metabox-description ffm-instruction-tip"><?php echo __( 'Click on a field above to add it to the donation form. ', 'give-ffm' ); ?></p>

				<div class="form-field-options-wrap">

					<label for="field-placement"><?php _e( 'Form Field Location', 'give-ffm' ); ?>
						<span class="give-tooltip give-icon give-icon-question" data-tooltip="<?php _e( 'Where would you like the fields to display on the form? Note, if you do not accept credit cards the fields will not display in those locations if set.', 'give-ffm' ); ?>"></span></label>

					<select id="field-placement" name="_give_ffm_placement">
						<?php
						$default_value = get_post_meta( $post->ID, '_give_ffm_placement', true );

						//Setup available FFM positions
						$position_array = array(
							array(
								'value'  => 'give_checkout_form_top',
								'option' => __( 'Above all fields', 'give-ffm' )
							),
							array(
								'value'  => 'give_checkout_form_bottom',
								'option' => __( 'Below all fields', 'give-ffm' )
							),
							array(
								'value'  => 'give_before_donation_levels',
								'option' => __( 'Above donation fields', 'give-ffm' )
							),
							array(
								'value'  => 'give_after_donation_levels',
								'option' => __( 'Below donation fields', 'give-ffm' )
							),
							array(
								'value'  => 'give_payment_mode_top',
								'option' => __( 'Above payment options', 'give-ffm' )
							),
							array(
								'value'  => 'give_payment_mode_bottom',
								'option' => __( 'Below payment options', 'give-ffm' )
							),
							array(
								'value'  => 'give_purchase_form_before_personal_info',
								'option' => __( 'Above personal info fields', 'give-ffm' )
							),
							array(
								'value'  => 'give_purchase_form_after_personal_info',
								'option' => __( 'Below personal info fields', 'give-ffm' )
							),
							array(
								'value'  => 'give_purchase_form_before_cc_form',
								'option' => __( 'Above credit card fields', 'give-ffm' )
							),
							array(
								'value'  => 'give_purchase_form_after_cc_form',
								'option' => __( 'Below credit card fields', 'give-ffm' )
							),
						);

						foreach ( $position_array as $key => $value ) {

							$selected = '';

							if ( $value['value'] == $default_value ) {
								$selected = 'selected="selected"';
							}

							echo '<option value="' . $value['value'] . '" ' . $selected . ' >' . $value['option'] . '</option>';

						}
						?>
					</select>

				</div>

				<button class="button button-small ffm-collapse"><?php _e( 'Toggle Fields', 'give-ffm' ); ?></button>

				<p class="cmb2-metabox-description ffm-help-tip"><?php echo sprintf( __( 'Need help? Check out the %1$sdocumentation%3$s and %2$ssupport%3$s.', 'give-ffm' ), '<a href="https://givewp.com/documentation/add-ons/form-field-manager/" target="_blank">', '<a href="http://givewp.com/support/form-field-manager/" target="_blank" class="new-window">', '</a>' ); ?></p>


			</div>
		</div>
		<?php
		//No Fields
		if ( empty( $form_inputs ) ) { ?>
			<div class="ffm-no-fields">
				<span class="give-icon dashicons-give"></span>

				<p><?php _e( 'This donation form has no custom form fields.', 'give-ffm' ); ?></p>
			</div>
		<?php } ?>
		<ul id="give-form-fields-editor" class="give-form-fields-editor unstyled">

			<?php
			if ( $form_inputs ) {
				$count = 0;
				foreach ( $form_inputs as $order => $input_field ) {
					$name               = ucwords( str_replace( '_', ' ', $input_field['template'] ) );
					$ffm_admin_template = new Give_FFM_Admin_Template();
					call_user_func_array( array( $ffm_admin_template, $input_field['template'] ), array(
						$count,
						$name,
						$input_field
					) );
					$count ++;
				}
			}
			?>
		</ul>

		<?php
	}

	/**
	 * Ajax Callback handler for inserting fields in forms
	 *
	 * @return void
	 */
	function ajax_post_add_element() {

		$name     = $_POST['name'];
		$type     = $_POST['type'];
		$field_id = $_POST['order'];

		switch ( $name ) {
			case 'custom_text':
				Give_FFM_Admin_Template::text_field( $field_id, __( 'Custom field: Text', 'give-ffm' ) );
				break;

			case 'custom_textarea':
				Give_FFM_Admin_Template::textarea_field( $field_id, __( 'Custom field: Textarea', 'give-ffm' ) );
				break;

			case 'custom_section':
				Give_FFM_Admin_Template::section_field( $field_id, __( 'Custom field: Section', 'give-ffm' ) );
				break;

			case 'custom_select':
				Give_FFM_Admin_Template::dropdown_field( $field_id, __( 'Custom field: Select', 'give-ffm' ) );
				break;

			case 'custom_multiselect':
				Give_FFM_Admin_Template::multiple_select( $field_id, __( 'Custom field: Multiselect', 'give-ffm' ) );
				break;

			case 'custom_radio':
				Give_FFM_Admin_Template::radio_field( $field_id, __( 'Custom field: Radio', 'give-ffm' ) );
				break;

			case 'custom_checkbox':
				Give_FFM_Admin_Template::checkbox_field( $field_id, __( 'Custom field: Checkbox', 'give-ffm' ) );
				break;

			case 'custom_file':
				Give_FFM_Admin_Template::file_upload( $field_id, __( 'Custom field: File Upload', 'give-ffm' ) );
				break;

			case 'custom_url':
				Give_FFM_Admin_Template::website_url( $field_id, __( 'Custom field: URL', 'give-ffm' ) );
				break;

			case 'custom_email':
				Give_FFM_Admin_Template::email_address( $field_id, __( 'Custom field: E-Mail', 'give-ffm' ) );
				break;

			case 'custom_repeater':
				Give_FFM_Admin_Template::repeat_field( $field_id, __( 'Custom field: Repeat Field', 'give-ffm' ) );
				break;

			case 'custom_html':
				Give_FFM_Admin_Template::custom_html( $field_id, __( 'HTML', 'give-ffm' ) );
				break;

			case 'action_hook':
				Give_FFM_Admin_Template::action_hook( $field_id, __( 'Action Hook', 'give-ffm' ) );
				break;

			case 'custom_date':
				Give_FFM_Admin_Template::date_field( $field_id, __( 'Custom Field: Date', 'give-ffm' ) );
				break;

			case 'give_email':
				Give_FFM_Admin_Template::give_email( $field_id, __( 'Email', 'give-ffm' ) );
				break;

			default:
				do_action( 'ffm_admin_field_' . $name, $type, $field_id );
				break;
		}

		exit;
	}

}
