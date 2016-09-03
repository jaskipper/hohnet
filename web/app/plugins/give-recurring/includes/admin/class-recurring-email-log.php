<?php
/**
 * Give Recurring Email Log
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Give_Recurring_Email_Log extends WP_List_Table {

	private $per_page = 30;

	public $subscription;

	function __construct() {

		//Set parent defaults
		parent::__construct( array(
			'singular' => 'recurring_email_notice',  //singular name of the listed records
			'plural'   => 'recurring_email_notices', //plural name of the listed records
			'ajax'     => false              //does this table support ajax?
		) );


	}


	/**
	 * Setup columns
	 *
	 * @access      public
	 * @since       1.0
	 * @return      array
	 */
	function get_columns() {

		$columns = array(
			'email_type'    => __( 'Email Type', 'give-recurring' ),
			'subject'       => __( 'Email Subject', 'give-recurring' ),
			'recipient'     => __( 'Email Recipient', 'give-recurring' ),
			'date'          => __( 'Date Sent', 'give-recurring' ),
			'donation_form' => __( 'Donation Form', 'give-recurring' ),
		);

		return $columns;
	}

	/**
	 * Output the type of email sent in a column
	 *
	 * @access      public
	 * @since       1.0
	 * @return      string
	 */
	function column_email_type( $item ) {

		$subscription_id    = get_post_meta( $item->ID, '_give_recurring_email_log_subscription_id', true );
		$this->subscription = new Give_Subscription( $subscription_id );

		$category = get_the_terms( $item, 'give_log_type' );

		if ( isset( $category[0]->name ) ) {
			return $this->get_pretty_log_category( $category[0]->name );
		}

		return false;

	}

	/**
	 * Output the subject column
	 *
	 * @access      public
	 * @since       1.0
	 * @return      string
	 */
	function column_subject( $item ) {

		$subject = get_post_meta( $item->ID, '_give_recurring_email_subject', true );

		return ! empty( $subject ) ? $subject : __( 'No subject found.', 'give-recurring' );

	}

	/**
	 * Display Tablenav (extended)
	 *
	 * @description: Display the table navigation above or below the table even when no items in the logs, so nav doesn't disappear
	 *
	 * @see: https://github.com/WordImpress/Give/issues/564
	 *
	 * @since 1.1
	 * @access protected
	 *
	 * @param string $which
	 */
	protected function display_tablenav( $which ) {

		if ( 'top' === $which ) {
			wp_nonce_field( 'bulk-' . $this->_args['plural'] );
		}
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">

			<div class="alignleft actions bulkactions">
				<?php $this->bulk_actions( $which ); ?>
			</div>
			<?php
			$this->extra_tablenav( $which );
			$this->pagination( $which );
			?>

			<br class="clear"/>
		</div>
		<?php
	}

	/**
	 *
	 * Output the recipient column
	 *
	 * @access      public
	 * @since       1.0
	 *
	 * @param $item
	 *
	 * @return      string - the title of the donation form used in the subscription
	 */
	function column_recipient( $item ) {

		$title = isset( $this->subscription->customer->name ) ? $this->subscription->customer->name : '';
		$title .= isset( $this->subscription->customer->email ) ? '&nbsp;&mdash;&nbsp;' . $this->subscription->customer->email : '';

		return $title;
	}

	/**
	 * Output the date column
	 *
	 * @access      public
	 * @since       1.0
	 */
	function column_date( $item ) {
		return $item->post_date;
	}

	/**
	 *
	 * Output the donation form column
	 *
	 * @access      public
	 * @since       1.0
	 *
	 * @param $item
	 *
	 * @return string
	 */
	function column_donation_form( $item ) {

		$form_id = give_get_payment_form_id( $this->subscription->parent_payment_id );

		return get_the_title( $form_id );

	}

	/**
	 * Retrieve the current page number
	 *
	 * @access      public
	 * @since       1.0
	 * @return      int
	 */
	function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}

	/**
	 * Outputs the log views
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	function bulk_actions( $which = '' ) {
		// These aren't really bulk actions but this outputs the markup in the right place
		give_log_views();
	}

	/**
	 * Retrieve the current page number
	 *
	 * @access      public
	 * @since       1.0
	 * @return      int
	 */
	function count_total_items() {

		$args = array(
			'post_type' => 'give_recur_email_log',
			'fields'    => 'ids',
			'nopaging'  => true,
			'tax_query' => array(
				array(
					'taxonomy' => 'give_log_type',
					'terms'    => array( 'renewal_notice', 'expiration_notice', 'subscription_receipt' ),
					'field'    => 'slug'
				)
			)
		);

		$query = new WP_Query( $args );
		if ( $query->have_posts() ) {
			return $query->post_count;
		}

		return 0;
	}

	/**
	 * Query database for license data and prepare it for the table
	 *
	 * @access      public
	 * @since       1.0
	 * @return      array
	 */
	function logs_data() {

		$license_args = array(
			'post_type'      => 'give_recur_email_log',
			'post_status'    => array( 'publish', 'future' ),
			'posts_per_page' => $this->per_page,
			'paged'          => $this->get_paged(),
			'tax_query'      => array(
				array(
					'taxonomy' => 'give_log_type',
					'terms'    => array( 'renewal_notice', 'cancelled_notice', 'expiration_notice', 'subscription_receipt' ),
					'field'    => 'slug'
				)
			)
		);

		return get_posts( $license_args );

	}

	/**
	 * Sets up the list table items
	 *
	 * @access      public
	 * @since       1.0
	 * @return      void
	 */
	function prepare_items() {

		/**
		 * First, lets decide how many records per page to show
		 */
		$columns = $this->get_columns();

		$this->_column_headers = array( $columns, array(), array() );

		$this->items = $this->logs_data();

		$total_items = $this->count_total_items();

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $this->per_page,
			'total_pages' => ceil( $total_items / $this->per_page )
		) );

	}

	/**
	 * Get Pretty Log Category
	 *
	 * @param $category
	 *
	 * @return mixed|void
	 */
	function get_pretty_log_category( $category ) {

		$pretty_cat = '';

		switch ( $category ) {

			case 'renewal_notice':
				$pretty_cat = __( 'Renewal Notice', 'give-recurring' );
				break;
			case 'expiration_notice':
				$pretty_cat = __( 'Expiration Notice', 'give-recurring' );
				break;
			case 'cancelled_notice':
				$pretty_cat = __( 'Cancellation Notice', 'give-recurring' );
				break;
			case 'subscription_receipt':
				$pretty_cat = __( 'Subscription Receipt', 'give-recurring' );
				break;

		}

		return apply_filters( 'give_recurring_email_log_cat_pretty_name', $pretty_cat );

	}

}