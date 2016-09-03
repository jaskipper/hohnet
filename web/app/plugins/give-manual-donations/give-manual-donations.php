<?php
/**
 * Plugin Name: Give - Manual Donations
 * Plugin URI: https://givewp.com/addons/manual-donations/
 * Description: Provides an admin interface for manually creating donation transactions in Give
 * Version: 1.0
 * Author: WordImpress
 * Author URI:  https://wordimpress.com
 * Text Domain: give-manual-donations
 * Domain Path: languages
 */

/**
 * Class Give_Manual_Donations
 */
class Give_Manual_Donations {

	/**
	 * @var
	 */
	private static $instance;

	/**
	 * Get active object instance
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @static
	 * @return object
	 */
	public static function get_instance() {

		if ( ! self::$instance ) {
			self::$instance = new Give_Manual_Donations();
		}

		return self::$instance;
	}

	/**
	 * Class constructor.  Includes constants, includes and init method.
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		define( 'GIVE_MD_PRODUCT_NAME', 'Manual Donations' );

		define( 'GIVE_MD_VERSION', '1.0' );

		// Plugin Folder URL
		if ( ! defined( 'GIVE_MD_PLUGIN_URL' ) ) {
			define( 'GIVE_MD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		$this->init();

	}


	/**
	 * Run action and filter hooks.
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return void
	 */
	private function init() {

		if ( ! function_exists( 'give_price' ) ) {
			return; // Give not present
		}

		if ( version_compare( GIVE_VERSION, '1.5.2', '<' ) ) {

			add_action( 'admin_notices', array( $this, 'give_version_notice' ) );

			return;
		}

		// internationalization
		add_action( 'init', array( $this, 'textdomain' ) );

		// add a crreate payment button to the top of the Transaction History page
		add_action( 'give_payments_page_top', array( $this, 'create_payment_button' ) );

		// register the Create Payment submenu
		add_action( 'admin_menu', array( $this, 'submenu' ) );

		// load scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );
		add_filter( 'give_load_admin_scripts', array( $this, 'register_admin_page' ), 10, 2 );

		// check for donation form price variations via ajax
		add_action( 'wp_ajax_give_md_check_for_variations', array( $this, 'check_for_variations' ) );
		add_action( 'wp_ajax_give_md_variation_change', array( $this, 'variation_change' ) );
		add_action( 'wp_ajax_give_md_validate_submission', array( $this, 'validate_donation' ) );

		// process payment creation
		add_action( 'give_create_payment', array( $this, 'create_payment' ) );

		// show payment created notice
		add_action( 'admin_notices', array( $this, 'payment_created_notice' ), 1 );

		// auto updater
		if ( class_exists( 'Give_License' ) ) {
			new Give_License( __FILE__, GIVE_MD_PRODUCT_NAME, GIVE_MD_VERSION, 'WordImpress' );
		}

		//Pretty "manual_donation" Label
		add_filter( 'give_gateway_admin_label', array( $this, 'manual_donation_gateway_label' ), 10, 2 );

		// Add 'Transaction' to the New menu of the admin bar
		add_action( 'admin_bar_menu', array( $this, 'modify_admin_bar' ), 999 );

	}

	/**
	 * Give Version Notice
	 */
	public function give_version_notice() {
		echo '<div class="error"><p>' . __( 'Your version of Give is below the minimum version requirement for the Manual Donations Add-on. Please update to version 1.5.2 or later.', 'give-manual-donations' ) . '</p></div>';
	}

	/**
	 * Textdomain
	 */
	public static function textdomain() {

		// Set filter for plugin's languages directory
		$lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
		$lang_dir = apply_filters( 'give_manual_donations_lang_directory', $lang_dir );

		// Load the translations
		load_plugin_textdomain( 'give-manual-donations', false, $lang_dir );

	}

	/**
	 * Adds 'Transaction' to the Admin Bar's 'NEW' menu
	 *
	 * @since  1.0
	 *
	 * @param $wp_admin_bar object The global WP_Admin_Bar object
	 *
	 * @return void
	 */
	function modify_admin_bar( $wp_admin_bar ) {
		$args = array(
			'id'     => 'give-md-new-payment',
			'title'  => __( 'Transaction', 'give-manual-donations' ),
			'parent' => 'new-content',
			'href'   => esc_url( add_query_arg( 'page', 'give-manual-donation', admin_url( 'options.php' ) ) ),
		);

		$wp_admin_bar->add_menu( $args );
	}

	/**
	 * Create Transaction Button
	 */
	public static function create_payment_button() { ?>

		<p id="give_create_payment_go">

			<a href="<?php echo esc_url( add_query_arg( 'page', 'give-manual-donation', admin_url( 'options.php' ) ) ); ?>" class="page-title-action"><?php _e( 'Create Transaction', 'give-manual-donations' ); ?></a>
		</p>

		<style>
			.updated {
				clear: both;
			}

			#give_create_payment_go {
				float: left;
				margin: 0 0 40px;
				position: relative;
				top: 17px;
			}

			.wrap > h2:first-child, .wrap + h2, .postbox .inside h2, .wrap h1 {
				float: left;
			}

			#give-payments-filter {
				clear: both;
				margin: 30px 0 0;
			}

		</style>
		<?php
	}

	/**
	 * Register Admin Page
	 *
	 * @description: Makes Give recognize this as an admin page and include admin scripts
	 *
	 * @param $found
	 * @param $hook
	 *
	 * @return bool
	 */
	public static function register_admin_page( $found, $hook ) {
		if ( 'admin_page_give-manual-donation' == $hook ) {
			$found = true;
		}

		return $found;
	}

	/**
	 * Submenuedit_give_payments
	 *
	 * @description: Responsible for registering / adding the donation transaction creation screen
	 */
	public static function submenu() {
		global $give_create_payment_page;
		$give_create_payment_page = add_submenu_page( 'options.php', __( 'Create Donation Transaction', 'give-manual-donations' ), __( 'Create Donation Transaction', 'give-manual-donations' ), 'edit_give_payments', 'give-manual-donation', array(
			__CLASS__,
			'payment_creation_form'
		) );
	}

	/**
	 * Load Scripts
	 *
	 * @param $hook
	 */
	public function load_scripts( $hook ) {

		if ( 'admin_page_give-manual-donation' != $hook ) {
			return;
		}

		wp_enqueue_script( 'jquery-ui-datepicker' );

		wp_register_script( 'give_md_timepicker_js', GIVE_MD_PLUGIN_URL . 'assets/js/jquery-ui-timepicker-addon.min.js', array(
			'jquery',
			'jquery-ui-datepicker'
		) );
		wp_enqueue_script( 'give_md_timepicker_js' );


		wp_register_script( 'give_md_admin_js', GIVE_MD_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery' ) );
		wp_enqueue_script( 'give_md_admin_js' );


		$date_format = get_option( 'date_format' );

		//Localize / PHP to AJAX vars
		$localize_md = apply_filters( 'give_md_admin_script_vars', array(
			'ajaxurl'     => give_get_ajax_url(),
			'decimals'    => give_currency_decimal_filter(),
			'date_format' => $this->dateformat_php_to_jqueryui( $date_format )
		) );
		wp_localize_script( 'give_md_admin_js', 'give_md_vars', $localize_md );

		//CSS
		wp_register_style( 'give_md_timepicker_css', GIVE_MD_PLUGIN_URL . 'assets/css/jquery-ui-timepicker-addon.min.css' );
		wp_enqueue_style( 'give_md_timepicker_css' );

		wp_register_style( 'give_md_admin_css', GIVE_MD_PLUGIN_URL . 'assets/css/admin.css' );
		wp_enqueue_style( 'give_md_admin_css' );

		wp_enqueue_style( 'jquery-ui-css', GIVE_PLUGIN_URL . 'assets/css/jquery-ui-fresh.css' );

		add_filter( 'give_is_admin_page', '__return_true' );

	}

	/**
	 * Transaction Creation Form
	 */
	public static function payment_creation_form() { ?>
		<div class="wrap">

			<h2><?php _e( 'New Donation Transaction', 'give-manual-donations' ); ?></h2>

			<div class="give_md_errors"></div>
			<form id="give_md_create_payment" method="post">
				<table class="form-table" id="give-customer-details">
					<tbody id="give-md-table-body">
					<tr class="form-field give-md-form-wrap">
						<th scope="row" valign="top">
							<label><?php echo give_get_forms_label_singular(); ?></label>
						</th>
						<td class="give-md-forms">
							<div id="give_file_fields" class="give_meta_table_wrap">
								<table class="widefat give-transaction-form-table" style="width: auto;" cellpadding="0" cellspacing="0">
									<thead>
									<tr>
										<th style="padding: 10px;"><?php echo give_get_forms_label_singular(); ?></th>
										<th style="padding: 10px;"><?php _e( 'Donation Level', 'give-manual-donations' ); ?></th>
										<th style="padding: 10px; width: 150px;"><?php echo __( 'Amount', 'give-manual-donations' ) . ' (' . give_currency_symbol() . ')'; ?></th>
									</tr>
									</thead>
									<tbody>
									<tr class="">
										<td>
											<?php
											echo Give()->html->forms_dropdown( array(
												'name'     => 'forms[id]',
												'id'       => 'forms',
												'class'    => 'md-forms',
												'multiple' => false,
												'chosen'   => true
											) );
											?>
										</td>
										<td class="form-price-option-wrap"><?php _e( 'n/a', 'give-manual-donations' ); ?></td>
										<td>
											<input type="number" class="give-md-amount" name="forms[amount]" value="" min="0" placeholder="<?php esc_attr_e( 'Item price', 'give-manual-donations' ); ?>"/>
										</td>

									</tr>
									</tbody>
								</table>
								<div id="give-forms-table-notice-wrap"></div>
								<p class="description"><?php _e( 'Select a donation form for this transaction. You may specify a custom amount by editing the amount field.', 'give-manual-donations' ); ?></p>
							</div>
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row" valign="top">
							<label for="give-md-user"><?php _e( 'Donor', 'give-manual-donations' ); ?></label>
						</th>
						<td class="give-md-email give-clearfix">
							<div class="customer-info">
								<?php echo Give()->html->donor_dropdown( array( 'name' => 'customer' ) ); ?>
							</div>
							<div class="create-new-customer">
								<a href="#new" class="give-payment-new-customer button" title="<?php _e( 'New Donor', 'give-manual-donations' ); ?>"><?php _e( 'New Donor', 'give-manual-donations' ); ?></a>
							</div>
							<div class="new-customer" style="display: none">
								<a href="#cancel" class="give-payment-new-customer-cancel button" title="<?php _e( 'Existing Donor', 'give-manual-donations' ); ?>"><?php _e( 'Select Existing Donor', 'give-manual-donations' ); ?></a>
							</div>
							<p class="description"><?php _e( 'Select a donor to attach this donation to or create a new donor.', 'give-manual-donations' ) ?></p>
						</td>
					</tr>
					<tr class="form-field new-customer" style="display: none">
						<th scope="row" valign="top">
							<label for="give-md-user"><?php _e( 'Donor Email', 'give-manual-donations' ); ?></label>
						</th>
						<td class="give-md-email">
							<input type="text" class="small-text" id="give-md-email" name="email" style="width: 180px;"/>
							<p class="description"><?php _e( 'Enter the email address of the donor.', 'give-manual-donations' ); ?></p>
						</td>
					</tr>
					<tr class="form-field new-customer" style="display: none">
						<th scope="row" valign="top">
							<label for="give-md-last"><?php _e( 'Donor First Name', 'give-manual-donations' ); ?></label>
						</th>
						<td class="give-md-last">
							<input type="text" class="small-text" id="give-md-last" name="first" style="width: 180px;"/>
							<p class="description"><?php _e( 'Enter the first name of the donor (optional).', 'give-manual-donations' ); ?></p>
						</td>
					</tr>
					<tr class="form-field new-customer" style="display: none">
						<th scope="row" valign="top">
							<label for="give-md-last"><?php _e( 'Donor Last Name', 'give-manual-donations' ); ?></label>
						</th>
						<td class="give-md-last">
							<input type="text" class="small-text" id="give-md-last" name="last" style="width: 180px;"/>
							<p class="description"><?php _e( 'Enter the last name of the donor (optional).', 'give-manual-donations' ); ?></p>
						</td>
					</tr>

					<tr class="form-field">
						<th scope="row" valign="top">
							<?php _e( 'Transaction Status', 'give-manual-donations' ); ?>
						</th>
						<td class="give-md-status">
							<?php echo Give()->html->select( array(
								'name'             => 'status',
								'options'          => give_get_payment_statuses(),
								'selected'         => 'publish',
								'show_option_all'  => false,
								'show_option_none' => false
							) ); ?>
							<label for="give-md-status" class="description"><?php _e( 'Select the status of this transaction.', 'give-manual-donations' ); ?></label>
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row" valign="top">
							<label for="give-md-payment-method"><?php _e( 'Payment Method', 'give-manual-donations' ); ?></label>
						</th>
						<td class="give-md-gateways">
							<select name="gateway" id="give-md-payment-method">
								<option value="manual_donation"><?php esc_html_e( 'Manual Transaction', 'give-manual-donations' ); ?></option>
								<?php foreach ( give_get_payment_gateways() as $gateway_id => $gateway ) : ?>
									<option value="<?php echo esc_attr( $gateway_id ); ?>"><?php echo esc_html( $gateway['admin_label'] ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php _e( 'Select the payment method used.', 'give-manual-donations' ); ?></p>
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row" valign="top">
							<label for="give-md-date"><?php _e( 'Date', 'give-manual-donations' ); ?></label>
						</th>
						<td class="give-md-forms">
							<input type="text" class="small-text give_datepicker" id="give-md-date" name="date" style="width: 180px;"/>
							<p class="description"><?php _e( 'Enter the donation date, or leave blank for today\'s date.', 'give-manual-donations' ); ?></p>
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row" valign="top">
							<?php _e( 'Send Donor Receipt', 'give-manual-donations' ); ?>
						</th>
						<td class="give-md-receipt">
							<label for="give-md-receipt">
								<input type="checkbox" id="give-md-receipt" name="receipt" style="width: auto;" value="1"/>
								<?php _e( 'Send the donation receipt to the donor?', 'give-manual-donations' ); ?>
							</label>
							<p class="description"><?php _e( 'When this option is enabled the donor will receive an email receipt.', 'give-manual-donations' ); ?></p>
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row" valign="top">
							<?php _e( 'Send Admin Notification', 'give-manual-donations' ); ?>
						</th>
						<td class="give-md-admin-receipt">
							<label for="give-md-admin-receipt">
								<input type="checkbox" id="give-md-admin-receipt" name="receipt_admin" style="width: auto;" value="1"/>
								<?php _e( 'Send a new donation notification to the admins?', 'give-manual-donations' ); ?>
							</label>
							<p class="description"><?php _e( 'When this option is enabled the emails set in your settings will receive notification of a new donation.', 'give-manual-donations' ); ?></p>
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row" valign="top">
							<label for="give-md-nore"><?php _e( 'Note', 'give-manual-donations' ); ?></label>
						</th>
						<td class="give-md-forms">
							<textarea class="give_note" id="give-md-date" name="note"></textarea>
							<p class="description"><?php _e( 'Add an optional note to this donation.', 'give-manual-donations' ); ?></p>
						</td>
					</tr>
					</tbody>
				</table>
				<?php wp_nonce_field( 'give_create_payment_nonce', 'give_create_payment_nonce' ); ?>
				<input type="hidden" name="give-gateway" value="manual_donations"/>
				<input type="hidden" name="give-action" value="create_payment"/>
				<?php submit_button( __( 'Create Transaction', 'give-manual-donations' ) ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Check for Variations
	 *
	 * @description: Called via AJAX
	 */
	public function check_for_variations() {

		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'give_create_payment_nonce' ) ) {

			$form_id  = absint( $_POST['form_id'] );
			$price_id = 0;

			$response = array();

			if ( give_has_variable_prices( $form_id ) ) {

				$prices                  = give_get_variable_prices( $form_id );
				$response['price_array'] = $prices;
				$html                    = '';
				if ( $prices ) {
					$html = '<select name="forms[price_id]" class="give-md-price-select">';
					foreach ( $prices as $key => $price ) {

						$level_text = ! empty( $price['_give_text'] ) ? esc_html( $price['_give_text'] ) : give_currency_filter( give_format_amount( $price['_give_amount'] ) );
						$price_id   = ! empty( $price['_give_id']['level_id'] ) ? intval( $price['_give_id']['level_id'] ) : 0;

						$html .= '<option value="' . $price_id . '">' . $level_text . '</option>';

						if ( ! isset( $response['amount'] ) ) {
							$response['amount'] = give_format_amount( $price['_give_amount'] );
						}
					}
					$html .= '</select>';

				}
				$response['html'] = $html;


			} else {

				$response['amount'] = give_get_form_price( $form_id );

			}

			$response = $this->recurring_check_on_form_change( $response, $form_id, $price_id );

			//Send Response
			echo json_encode( $response );
			exit;

		}

	}

	/**
	 * Variation Change
	 */
	public function variation_change() {

		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'give_create_payment_nonce' ) ) {

			$form_id  = absint( $_POST['form_id'] );
			$price_id = isset( $_POST['price_id'] ) ? $_POST['price_id'] : '';
			$response = array();

			$response['amount'] = give_format_amount( give_get_price_option_amount( $form_id, $price_id ) );

			$response = $this->recurring_check_on_form_change( $response, $form_id, $price_id );

			echo json_encode( $response );
			exit;
		}


	}

	/**
	 * Check for Recurring Option
	 *
	 * @param $response
	 * @param $form_id
	 * @param $price_id
	 *
	 * @return array
	 */
	public function recurring_check_on_form_change( $response, $form_id, $price_id = 0 ) {

		//Check if Recurring Enabled
		if ( $this->check_for_recurring() && give_is_form_recurring( $form_id ) ) {

			$response['recurring_enabled'] = true;
			$recurring_type                = get_post_meta( $form_id, '_give_recurring', true );
			$response['recurring_type']    = $recurring_type;

			//Checks for:
			//a) if recurring type is donor's choice
			// - allows admin to choose whether is sub transaction regardless of type (set or multi)
			//b) if is admin's choice & NOT multi-level
			// - not being multi-level means it's always going to be recurring
			//c) if it is multi-level we check the first variation to see if it's recurring
			if ( $recurring_type == 'yes_donor' ) {

				$response['subscription_text'] = __( 'Is this a subscription donation? This donation form is set up as donor\'s choice for a recurring donation; checking this option will make this a donation subscription transaction.', 'give-manual-transactions' );

			} elseif ( $recurring_type == 'yes_admin' && ! give_has_variable_prices( $form_id ) ) {

				$response['subscription_text'] = __( 'This is the first transaction for a donation subscription because this form is set up as recurring admin choice.', 'give-manual-transactions' );

			} elseif ( $recurring_type == 'yes_admin' && give_has_variable_prices( $form_id ) ) {

				$prices = isset( $prices ) ? give_get_variable_prices( $form_id ) : array();

				//If empty price ID check first price ID
				if ( empty( $price_id ) ) {
					$price_id = isset( $prices[0]['_give_id']['level_id'] ) ? intval( $prices[0]['_give_id']['level_id'] ) : 1;
				}

				if ( Give_Recurring()->is_recurring( $form_id, $price_id ) ) {
					$response['subscription_text'] = __( 'This is the first transaction for a donation subscription because this form is set up as recurring admin choice.', 'give-manual-transactions' );
				} else {
					$response['recurring_enabled'] = false;
				}

			} else {
				$response['subscription_text'] = '';
			}

		} else {
			$response['recurring_enabled'] = false;
			$response['recurring_type']    = false;
		}

		return $response;

	}

	/**
	 * Validate Submission Requirements via AJAX
	 */
	public function validate_donation() {

		$response                   = '';
		$response['error_messages'] = array();

		//Set $data from serialized form vals from AJAX
		$fields = isset( $_POST['fields'] ) ? $_POST['fields'] : null;
		parse_str( $fields, $data );

		if ( empty( $data ) ) {
			$response['error_messages'][] = __( 'An AJAX error occurred. Please contact support.', 'give-manual-donations' );
		}

		//Check for valid donation form ID
		if ( $data['forms']['id'] == 0 ) {
			$response['error_messages'][] = sprintf( __( 'Please select at least one %s to add to this donation.', 'give-manual-donations' ), give_get_forms_label_singular() );
		}


		//Check for an assigned donor
		$user = $this->get_user( $data );
		if ( null == $user ) {
			$response['error_messages'][] = __( 'Please select a donor or create a new one.', 'give-manual-donations' );
		}


		if ( empty( $response['error_messages'] ) ) {
			echo json_encode( 'success' );
		} else {
			echo json_encode( $response );
		}

		wp_die();

	}

	/**
	 * Create Donation Payment
	 *
	 * @param $data
	 */
	public function create_payment( $data ) {

		//Security check
		if ( ! wp_verify_nonce( $data['give_create_payment_nonce'], 'give_create_payment_nonce' ) ) {
			wp_die( __( 'Uh oh, security nonce failure. Please contact support.', 'give-manual-donations' ) );
		}

		//Verify Form ID
		if ( $data['forms']['id'] == 0 ) {
			wp_die( sprintf( __( 'Please select at least one %s to add to this donation.', 'give-manual-donations' ), give_get_forms_label_singular() ) );
		}

		//Prevent emails from sending normally
		add_action( 'give_complete_purchase', array( $this, 'remove_email_capability' ), 1, 1 );

		$payment = new Give_Payment();


		//Create customer
		$user       = $this->get_user( $data );
		$customer   = new Give_Customer( $user, false );
		$by_user_id = false;
		$user_id    = ( $by_user_id == true ) ? $user : 0;
		$email      = ( $by_user_id == false ) ? $user : '';
		$first      = isset( $data['first'] ) ? sanitize_text_field( $data['first'] ) : '';
		$last       = isset( $data['last'] ) ? sanitize_text_field( $data['last'] ) : '';

		if ( ! $customer->id > 0 ) {

			$user = ( $by_user_id == false ) ? get_user_by( 'email', $user ) : get_user_by( 'id', $user );
			if ( $user ) {
				$user_id = $user->ID;
				$email   = $user->user_email;
			}

			$customer->create( array(
				'email'   => $email,
				'name'    => $first . ' ' . $last,
				'user_id' => $user_id
			) );

		} else {
			$email = $customer->email;
		}


		//Setup payment
		$payment->customer_id = $customer->id;
		$payment->user_id     = $user_id;
		$payment->first_name  = $first;
		$payment->last_name   = $last;
		$payment->email       = $email;
		$payment->mode        = give_is_test_mode() ? 'test' : 'live';

		// Make sure the user info data is set
		$payment->user_info = array(
			'first_name' => $first,
			'last_name'  => $last,
			'id'         => $user_id,
			'email'      => $email,
		);

		$total = ! empty( $data['forms']['amount'] ) ? give_sanitize_amount( $data['forms']['amount'] ) : 0;

		//Add donation
		$payment->form_title = get_the_title( $data['forms']['id'] );
		$payment->form_id    = isset( $data['forms']['id'] ) ? $data['forms']['id'] : null;
		$payment->price_id   = isset( $data['forms']['price_id'] ) ? $data['forms']['price_id'] : null;
		$payment->total      = $total;
		$payment->date       = $this->payment_date( $data );
		$payment->status     = 'pending';
		$payment->currency   = give_get_currency();
		$payment->gateway    = sanitize_text_field( $_POST['gateway'] );

		//Save the transaction
		$payment->save();

		if ( isset( $_POST['status'] ) && 'pending' !== $_POST['status'] ) {
			$payment->status = $_POST['status'];
			$payment->save();
		}

		//Is this form recurring enabled?
		if ( $this->check_for_recurring() && isset( $_POST['confirm_subscription'] ) && ! empty( $_POST['confirm_subscription'] ) ) {
			$this->create_subscription( $payment, $customer );
		}

		//Add a note
		if ( isset( $data['note'] ) && ! empty( $data['note'] ) ) {
			$payment->add_note( $data['note'] );
		}

		//Handle email receipt to donor
		if ( isset( $data['receipt'] ) && $data['receipt'] == '1' ) {
			give_email_donation_receipt( $payment->ID, false ); //false to prevent admin email
		}

		//Handle donation notification email to admins
		if ( isset( $data['receipt_admin'] ) && $data['receipt_admin'] == '1' && ! give_admin_notices_disabled( $payment->ID ) ) {
			do_action( 'give_admin_sale_notice', $payment->ID, $payment->get_meta() );
		}

		wp_redirect( admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&give-message=payment_created' ) );
		exit;

	}

	/**
	 * Create Subscription
	 *
	 * @description: Creates a subscription for transactions made on recurring forms
	 *
	 * @param $payment
	 * @param $customer
	 */
	private function create_subscription( $payment, $customer ) {

		//Check if form is recurring
		if ( $this->check_for_recurring() && give_is_form_recurring( $payment->form_id ) ) {

			//Create new subscription & set donor as subscriber
			// Now create the subscription record
			$subscriber = new Give_Recurring_Subscriber( $customer->id );

			//Get Subscription Period for Form
			if ( give_has_variable_prices( $payment->form_id ) ) {
				$period = Give_Recurring()->get_period( $payment->form_id, $payment->price_id );
			} else {
				$period = Give_Recurring()->get_period( $payment->form_id );
			}

			//Get Bill Time for Subscription
			if ( give_has_variable_prices( $payment->form_id ) ) {
				$bill_times = Give_Recurring()->get_times( $payment->form_id, $payment->price_id );
			} else {
				$bill_times = Give_Recurring()->get_times( $payment->form_id );
			}

			$args = array(
				'product_id'        => $payment->form_id,
				'parent_payment_id' => $payment->ID,
				'status'            => 'active',
				'period'            => $period,
				'initial_amount'    => $payment->total,
				'recurring_amount'  => $payment->total,
				'bill_times'        => $bill_times,
				'expiration'        => $subscriber->get_new_expiration( $payment->form_id, $payment->price_id ),
				'profile_id'        => md5( $payment->key . $payment->form_id ),
				'gateway'           => give_get_payment_gateway( $payment->ID )
			);

			$subscriber->add_subscription( $args );

		}

	}

	/**
	 * Payment Date
	 *
	 * @param $data
	 *
	 * @return bool|string
	 */
	private function payment_date( $data ) {

		//Donation date
		$date = ! empty( $data['date'] ) ? date( 'Y-m-d H:i:s', strtotime( strip_tags( trim( $data['date'] ) ) ) ) : false;
		if ( ! $date ) {
			$date = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
		}

		if ( strtotime( $date, time() ) > time() ) {
			$date = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
		}

		return apply_filters( 'give_md_payment_date', $date );
	}

	/**
	 * Get User Helper
	 *
	 * @param $data
	 *
	 * @return null|string
	 */
	public function get_user( $data ) {

		if ( ! empty( $data['email'] ) ) {
			$user = strip_tags( trim( $data['email'] ) );
		} elseif ( empty( $data['email'] ) && ! empty( $data['customer'] ) ) {
			$user = strip_tags( trim( $data['customer'] ) );
		} else {
			$user = null;
		}

		return $user;
	}

	/**
	 * Remove Email Capability
	 *
	 * @description:
	 */
	public function remove_email_capability() {
		//Prevent normal emails
		remove_action( 'give_complete_purchase', 'give_trigger_donation_receipt', 999, 1 );
	}

	/**
	 * Payment Created Notice
	 */
	public static function payment_created_notice() {
		if ( isset( $_GET['give-message'] ) && $_GET['give-message'] == 'payment_created' && current_user_can( 'view_give_reports' ) ) {
			add_settings_error( 'give-notices', 'give-payment-created', __( 'The payment has been created.', 'give-manual-donations' ), 'updated' );
		}
	}

	/**
	 * PHP Dateformat to jQuery UI format
	 *
	 * @description: Matches each symbol of PHP date format standard with jQuery equivalent codeword
	 *
	 * @author Tristan Jahier
	 * @see http://stackoverflow.com/questions/16702398/convert-a-php-date-format-to-a-jqueryui-datepicker-date-format
	 *
	 * @param $php_format
	 *
	 * @return string
	 */
	function dateformat_php_to_jqueryui( $php_format ) {
		$SYMBOLS_MATCHING = array(
			// Day
			'd' => 'dd',
			'D' => 'D',
			'j' => 'd',
			'l' => 'DD',
			'N' => '',
			'S' => '',
			'w' => '',
			'z' => 'o',
			// Week
			'W' => '',
			// Month
			'F' => 'MM',
			'm' => 'mm',
			'M' => 'M',
			'n' => 'm',
			't' => '',
			// Year
			'L' => '',
			'o' => '',
			'Y' => 'yy',
			'y' => 'y',
			// Time
			'a' => '',
			'A' => '',
			'B' => '',
			'g' => '',
			'G' => '',
			'h' => '',
			'H' => '',
			'i' => '',
			's' => '',
			'u' => ''
		);
		$jqueryui_format  = "";
		$escaping         = false;
		for ( $i = 0; $i < strlen( $php_format ); $i ++ ) {
			$char = $php_format[ $i ];
			if ( $char === '\\' ) // PHP date format escaping character
			{
				$i ++;
				if ( $escaping ) {
					$jqueryui_format .= $php_format[ $i ];
				} else {
					$jqueryui_format .= '\'' . $php_format[ $i ];
				}
				$escaping = true;
			} else {
				if ( $escaping ) {
					$jqueryui_format .= "'";
					$escaping = false;
				}
				if ( isset( $SYMBOLS_MATCHING[ $char ] ) ) {
					$jqueryui_format .= $SYMBOLS_MATCHING[ $char ];
				} else {
					$jqueryui_format .= $char;
				}
			}
		}

		return $jqueryui_format;
	}

	/**
	 * Manual Donation Gateway Label
	 *
	 * @description: Provides a pretty label for donations created using the "Manual Donation" gateway option
	 * @see https://github.com/WordImpress/give-manual-donations/issues/11
	 *
	 * @param $label
	 * @param $gateway
	 */
	public function manual_donation_gateway_label( $label, $gateway ) {

		if ( $label == 'manual_donation' ) {
			$label = __( 'Manual Transaction', 'give-md-' );
		}

		return $label;

	}


	/**
	 * Check that Recurring Add-on is Enabled Helper
	 *
	 * @description: Checks for the Recurring Add-on
	 *
	 * @return bool
	 */
	public function check_for_recurring() {

		//Is this form recurring enabled?
		if ( function_exists( 'give_is_form_recurring' ) ) {
			return true;
		} else {
			return false;
		}
	}

}

/**
 * Get it Started
 */
function give_load_manual_purchases() {
	$GLOBALS['give_manual_donations'] = new Give_Manual_Donations();
}

add_action( 'plugins_loaded', 'give_load_manual_purchases' );