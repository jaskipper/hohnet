<?php
/**
 * Admin side posting handler
 *
 * Builds custom fields UI for post add/edit screen and handles value saving.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_FFM_Admin_Posting
 */
class Give_FFM_Admin_Posting extends Give_FFM_Render_Form {

	/**
	 * Give_FFM_Admin_Posting constructor.
	 */
	function __construct() {
		add_action( 'give_view_order_details_billing_after', array( $this, 'render_form' ) );
		add_action( 'give_update_edited_purchase', array( $this, 'save_meta' ) );
	}

	/**
	 * Render Form in Payment Transactions
	 * 
	 * @description: This renders the FFM fields for a specific donation transaction
	 *
	 * @param            $payment_id
	 * @param null       $post_id
	 * @param bool|false $preview
	 */
	function render_form( $payment_id, $post_id = null, $preview = false ) {
		
		$payment_meta  = give_get_payment_meta( $payment_id );
		$form_id       = $payment_meta['form_id'];
		$form_settings = get_post_meta( $form_id, 'give-form-fields_settings', true );

		list( $post_fields, $taxonomy_fields, $custom_fields ) = $this->get_input_fields( $form_id );

		//Sanity Check
		if ( empty( $custom_fields ) ) {
			return;
		}
		
		?>
		<div id="give-form-fields" class="postbox">
			<h3 class="hndle"><?php _e( 'Custom Form Fields', 'give-ffm' ); ?></h3>

			<div class="inside">
				<input type="hidden" name="ffm_field_data_update" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />
				<input type="hidden" name="ffm_field_data_form_id" value="<?php echo $form_id; ?>" />
				<table class="form-table ffm-fields-table">
					<tbody>
					<?php
					$this->render_items( $custom_fields, absint( $_GET['id'] ), 'post', $form_id, $form_settings );
					?>
					</tbody>
				</table>
				<?php $this->submit_button(); ?>
			</div>
		</div>
		<?php
		$this->scripts_styles();
	}

	/**
	 * Label
	 *
	 * @param     $attr
	 * @param int $post_id
	 */
	function label( $attr, $post_id = 0 ) {
		echo $attr['label'] . $this->required_mark( $attr );
	}

	/**
	 * Render Item Before
	 *
	 * @param     $form_field
	 * @param int $post_id
	 */
	function render_item_before( $form_field, $post_id = 0 ) {
		echo '<tr>';
		echo '<th><strong>';
		$this->label( $form_field );
		echo '</strong></th>';
		echo '<td>';
	}

	/**
	 * Render Item After
	 *
	 * @param     $attr
	 * @param int $post_id
	 */
	function render_item_after( $attr, $post_id = 0 ) {
		echo '</td>';
		echo '</tr>';
	}

	/**
	 * Scripts Styles
	 */
	function scripts_styles() {
		?>
		<script type="text/javascript">
			jQuery( function ( $ ) {
				var give_ffm = {
					init      : function () {
						$( '.ffm-fields-table' ).on( 'click', 'img.ffm-clone-field', this.cloneField );
						$( '.ffm-fields-table' ).on( 'click', 'img.ffm-remove-field', this.removeField );
					},
					cloneField: function ( e ) {
						e.preventDefault();

						var $div = $( this ).closest( 'tr' );
						var $clone = $div.clone();
						// console.log($clone);

						//clear the inputs
						$clone.find( 'input' ).val( '' );
						$clone.find( ':checked' ).attr( 'checked', '' );
						$div.after( $clone );
					},

					removeField: function () {
						//check if it's the only item
						var $parent = $( this ).closest( 'tr' );
						var items = $parent.siblings().andSelf().length;

						if ( items > 1 ) {
							$parent.remove();
						}
					}
				};

				give_ffm.init();
			} );
		</script>
		<?php
	}

	/**
	 * Save Meta
	 *
	 * @param $post_id
	 */
	function save_meta( $post_id ) {

		if ( ! isset( $_POST['ffm_field_data_update'] ) ) {
			return;
		}

		$form_id   = absint( $_POST['ffm_field_data_form_id'] );
		$form_vars = self::get_input_fields( $form_id );

		list( $post_vars, $tax_vars, $meta_vars ) = self::get_input_fields( $form_id );
		Give_FFM()->frontend_form_post->update_post_meta( $meta_vars, absint( $_GET['id'] ), $form_vars );
	}

	/**
	 * Submit Button
	 */
	function submit_button() {
		$form_settings['update_text'] = __( 'Update Fields', 'give-ffm' );
		?>
		<fieldset class="ffm-submit" style="padding-bottom:10px;">
			<div class="ffm-label">
				&nbsp;
			</div>

			<?php wp_nonce_field( 'ffm_field_data_update' ); ?>
			<div class="give-submit-wrap"><input type="hidden" name="ffm_field_data_update" value="ffm_field_data_update">
			<input type="submit" class="button button-primary" name="submit" value="<?php echo $form_settings['update_text']; ?>" /></div>
		</fieldset>
		<?php
	}
}