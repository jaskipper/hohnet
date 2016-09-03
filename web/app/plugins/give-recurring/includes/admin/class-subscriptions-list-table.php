<?php
/**
 * Subscription List Table Class
 *
 * @package     Give Recurring
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Give Subscriptions List Table Class
 *
 * @access      private
 */
class Give_Subscription_Reports_Table extends WP_List_Table {

	/**
	 * Give_Subscription Object
	 *
	 * @since       1.0
	 */

	public $subscription;

	/**
	 * Number of results to show per page
	 *
	 * @since       1.0
	 */

	public $per_page = 30;

	/**
	 * Get things started
	 *
	 * @access      private
	 * @since       1.0
	 */
	function __construct() {
		global $status, $page;

		// Set parent defaults
		parent::__construct( array(
			'singular' => 'subscription',
			'plural'   => 'subscriptions',
			'ajax'     => false
		) );


	}

	/**
	 * Retrieve the table columns
	 *
	 * @access      private
	 * @since       1.0
	 * @return      array
	 */
	function get_columns() {
		$columns = array(
			'customer_id'      => __( 'Donor', 'give-recurring' ),
			'actions'          => __( 'Details', 'give-recurring' ),
			'status'           => __( 'Status', 'give-recurring' ),
			'recurring_amount' => __( 'Amount', 'give-recurring' ),
			'period'           => __( 'Period', 'give-recurring' ),
			'bill_times'       => __( 'Progress', 'give-recurring' ),
			//			'initial_amount'    => __( 'Initial Amount', 'give-recurring' ),
			'start'            => __( 'Start', 'give-recurring' ),
			'end'              => __( 'End', 'give-recurring' ),
			'profile_id'       => __( 'Recurring ID', 'give-recurring' ),
			//			'parent_payment_id' => __( 'Payment', 'give-recurring' ),
			'product_id'       => give_get_forms_label_singular(),
		);

		return apply_filters( 'give_report_subscription_columns', $columns );
	}

	/**
	 * Render most columns
	 *
	 * @param object $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	function column_default( $item, $column_name ) {
		return $item->$column_name;
	}

	/**
	 * Customer column
	 *
	 * @param $item
	 *
	 * @return string
	 */
	function column_customer_id( $item ) {

		$this->subscription = new Give_Subscription( $item->id );
		$subscriber         = new Give_Recurring_Subscriber( $item->customer_id );

		return '<a href="' . esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . $subscriber->id ) ) . '">' . $subscriber->name . '</a>';
	}


	/**
	 * Status column
	 *
	 * @access      private
	 *
	 * @param $item
	 *
	 * @since       1.0
	 * @return      string
	 */
	function column_status( $item ) {
		return give_recurring_get_pretty_subscription_status( $this->subscription->get_status() );
	}

	/**
	 * Period column
	 *
	 * @access      private
	 *
	 * @param $item
	 *
	 * @since       1.0
	 * @return      string
	 */
	function column_period( $item ) {
		return give_recurring_pretty_subscription_frequency( $item->period );
	}

	/**
	 * Billing Times column
	 *
	 * @access      private
	 *
	 * @param $item
	 *
	 * @since       1.0
	 * @return      string
	 */
	function column_bill_times( $item ) {
		return $this->subscription->get_subscription_progress();
	}


	/**
	 * Initial amount column
	 *
	 * @access      private
	 *
	 * @param $item
	 *
	 * @since       1.0
	 * @return      string
	 */
	function column_initial_amount( $item ) {
		return give_currency_filter( give_sanitize_amount( $item->initial_amount ) );
	}

	/**
	 * Recurring amount column
	 *
	 * @access      private
	 * @since       1.0
	 *
	 * @param $item
	 *
	 * @return string
	 */
	function column_recurring_amount( $item ) {
		return give_currency_filter( give_sanitize_amount( $item->recurring_amount ) );
	}


	/**
	 * Payment column
	 *
	 * @access      private
	 *
	 * @param $item
	 *
	 * @since       1.0
	 * @return      string
	 */
	function column_parent_payment_id( $item ) {
		return '<a href="' . esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-order-details&id=' . $item->parent_payment_id ) ) . '">' . give_get_payment_number( $item->parent_payment_id ) . '</a>';
	}

	/**
	 * Product ID column
	 *
	 * @access      private
	 *
	 * @param $item
	 *
	 * @since       1.0
	 * @return      string
	 */
	function column_product_id( $item ) {
		return '<a href="' . esc_url( admin_url( 'post.php?action=edit&post=' . $item->product_id ) ) . '">' . get_the_title( $item->product_id ) . '</a>';
	}

	/**
	 * Start Column
	 *
	 * @access      private
	 *
	 * @param $item
	 *
	 * @since       1.0
	 * @return      string
	 */
	function column_start( $item ) {

		$expiration_timestamp = strtotime( $item->created );

		return date( 'n/j/Y', $expiration_timestamp );

	}

	/**
	 * Start Column
	 *
	 * @access      private
	 *
	 * @param $item
	 *
	 * @since       1.0
	 * @return      string
	 */
	function column_end( $item ) {

		//Calculate subscription end date
		$bill_times = $item->bill_times;
		if ( $bill_times == 0 ) {
			return __( 'Until cancelled', 'give-recurring' );
		} else {
			return date( 'n/j/Y', $this->subscription->get_subscription_end_time() );
		}
	}

	/**
	 * Render the edit column
	 *
	 * @access      private
	 *
	 * @param $item
	 *
	 * @since       1.0
	 * @return      string
	 */
	function column_actions( $item ) {
		return '<a href="' . esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-subscriptions&id=' . $item->id ) ) . '" title="' . esc_attr( __( 'View or edit subscription', 'give-recurring' ) ) . '" class="button button-small">' . __( 'View / Edit', 'give-recurring' ) . '</a>';
	}


	/**
	 * Retrieve the current page number
	 *
	 * @access      private
	 * @since       1.0
	 * @return      int
	 */
	function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}

	/**
	 * Setup the final data for the table
	 *
	 * @access      private
	 * @since       1.0
	 * @uses        $this->_column_headers
	 * @uses        $this->items
	 * @uses        $this->get_columns()
	 * @uses        $this->get_sortable_columns()
	 * @uses        $this->get_pagenum()
	 * @uses        $this->set_pagination_args()
	 * @return      array
	 */
	function prepare_items() {

		$columns = $this->get_columns();

		$hidden = array(); // No hidden columns

		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$current_page = $this->get_pagenum();

		$db = new Give_Subscriptions_DB;

		$this->items = $db->get_subscriptions( array(
			'number' => $this->per_page,
			'offset' => $this->per_page * ( $this->get_paged() - 1 )
		) );

		$total_items = $db->count();

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $this->per_page,
			'total_pages' => ceil( $total_items / $this->per_page )
		) );
	}
}