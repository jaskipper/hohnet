<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Subscriptions DB Class
 *
 * @since  1.0
 */
class Give_Subscriptions_DB extends Give_DB {

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function __construct() {

		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'give_subscriptions';
		$this->primary_key = 'id';
		$this->version     = '1.0';

	}

	/**
	 * Get columns and formats
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function get_columns() {
		return array(
			'id'                => '%d',
			'customer_id'       => '%d',
			'period'            => '%s',
			'initial_amount'    => '%s',
			'recurring_amount'  => '%s',
			'bill_times'        => '%d',
			'parent_payment_id' => '%d',
			'product_id'        => '%d',
			'created'           => '%s',
			'expiration'        => '%s',
			'status'            => '%s',
			'profile_id'        => '%s',
		);
	}

	/**
	 * Get default column values
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function get_column_defaults() {
		return array(
			'customer_id'       => 0,
			'period'            => '',
			'initial_amount'    => '',
			'recurring_amount'  => '',
			'bill_times'        => 0,
			'parent_payment_id' => 0,
			'product_id'        => 0,
			'created'           => date( 'Y-m-d H:i:s' ),
			'expiration'        => date( 'Y-m-d H:i:s' ),
			'status'            => '',
			'profile_id'        => '',
		);
	}

	protected function generate_where_clause( $args = array() ) {

		$where = ' WHERE 1=1';

		// specific customers
		if ( ! empty( $args['id'] ) ) {

			if ( is_array( $args['id'] ) ) {
				$ids = implode( ',', array_map( 'intval', $args['id'] ) );
			} else {
				$ids = intval( $args['id'] );
			}

			$where .= " AND `id` IN( {$ids} ) ";
		}

		// Specific products
		if ( ! empty( $args['product_id'] ) ) {

			if ( is_array( $args['product_id'] ) ) {
				$product_ids = implode( ',', array_map( 'intval', $args['product_id'] ) );
			} else {
				$product_ids = intval( $args['product_id'] );
			}

			$where .= " AND `product_id` IN( {$product_ids} ) ";
		}

		// Specific parent payments
		if ( ! empty( $args['parent_payment_id'] ) ) {

			if ( is_array( $args['parent_payment_id'] ) ) {
				$parent_payment_ids = implode( ',', array_map( 'intval', $args['parent_payment_id'] ) );
			} else {
				$parent_payment_ids = intval( $args['parent_payment_id'] );
			}

			$where .= " AND `parent_payment_id` IN( {$parent_payment_ids} ) ";
		}

		// Subscriptions for specific customers
		if ( ! empty( $args['customer_id'] ) ) {

			if ( is_array( $args['customer_id'] ) ) {
				$customer_ids = implode( ',', array_map( 'intval', $args['customer_id'] ) );
			} else {
				$customer_ids = intval( $args['customer_id'] );
			}

			$where .= " AND `customer_id` IN( {$customer_ids} ) ";
		}

		// Subscriptions for specific profile IDs
		if ( ! empty( $args['profile_id'] ) ) {

			if ( is_array( $args['profile_id'] ) ) {
				$profile_ids = implode( ',', array_map( 'intval', $args['profile_id'] ) );
			} else {
				$profile_ids = intval( $args['profile_id'] );
			}

			$where .= " AND `profile_id` IN( {$profile_ids} ) ";
		}

		// Subscriptions for specific statuses
		if ( ! empty( $args['status'] ) ) {

			if ( is_array( $args['status'] ) ) {
				$statuses = implode( '\',\'', array_map( 'esc_sql', $args['status'] ) );
			} else {
				$statuses = esc_sql( $args['status'] );
			}

			$where .= " AND `status` IN( '{$statuses}' ) ";
		}

		// Subscriptions created for a specific date or in a date range
		if ( ! empty( $args['date'] ) ) {

			if ( is_array( $args['date'] ) ) {

				if ( ! empty( $args['date']['start'] ) ) {
					$start = date( 'Y-m-d H:i:s', strtotime( $args['date']['start'] ) );
					$where .= " AND `date_created` >= '{$start}'";
				}

				if ( ! empty( $args['date']['end'] ) ) {
					$end = date( 'Y-m-d H:i:s', strtotime( $args['date']['end'] ) );
					$where .= " AND `date_created` <= '{$end}'";
				}

			} else {

				$year  = date( 'Y', strtotime( $args['date'] ) );
				$month = date( 'm', strtotime( $args['date'] ) );
				$day   = date( 'd', strtotime( $args['date'] ) );

				$where .= " AND $year = YEAR ( date_created ) AND $month = MONTH ( date_created ) AND $day = DAY ( date_created )";
			}
		}

		// Subscriptions with a specific expiration date or in an expiration date range
		if ( ! empty( $args['expiration'] ) ) {

			if ( is_array( $args['expiration'] ) ) {

				if ( ! empty( $args['expiration']['start'] ) ) {
					$start = date( 'Y-m-d H:i:s', strtotime( $args['expiration']['start'] ) );
					$where .= " AND `expiration` >= '{$start}'";
				}

				if ( ! empty( $args['expiration']['end'] ) ) {
					$end = date( 'Y-m-d H:i:s', strtotime( $args['expiration']['end'] ) );
					$where .= " AND `expiration` <= '{$end}'";
				}

			} else {

				$year  = date( 'Y', strtotime( $args['expiration'] ) );
				$month = date( 'm', strtotime( $args['expiration'] ) );
				$day   = date( 'd', strtotime( $args['expiration'] ) );

				$where .= " AND $year = YEAR ( expiration ) AND $month = MONTH ( expiration ) AND $day = DAY ( expiration )";
			}
		}

		return $where;
	}

	protected function generate_cache_key( $prefix, $args ) {
		return md5( $prefix . serialize( $args ) );
	}

	/**
	 * Retrieve all subscriptions for a customer
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function get_subscriptions( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'number'      => 20,
			'offset'      => 0,
			'customer_id' => 0,
			'orderby'     => 'id',
			'order'       => 'DESC'
		);

		$args = wp_parse_args( $args, $defaults );

		if ( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}


		$args['orderby'] = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? 'id' : $args['orderby'];

		if ( 'amount' == $args['orderby'] ) {
			$args['orderby'] = 'amount+0';
		}

		$where           = $this->generate_where_clause( $args );
		$cache_key       = $this->generate_cache_key( 'give_subscriptions', $args );
		$subscriptions   = wp_cache_get( $cache_key, 'subscriptions' );
		$args['orderby'] = esc_sql( $args['orderby'] );
		$args['order']   = esc_sql( $args['order'] );

		if ( $subscriptions === false ) {
			$query         = $wpdb->prepare( "SELECT * FROM  $this->table_name $where ORDER BY {$args['orderby']} {$args['order']} LIMIT %d,%d;", absint( $args['offset'] ), absint( $args['number'] ) );
			$subscriptions = $wpdb->get_results( $query );
			wp_cache_set( $cache_key, $subscriptions, 'subscriptions', 3600 );
		}

		return $subscriptions;
	}

	/**
	 * Count the total number of subscriptions in the database
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function count( $args = array() ) {

		global $wpdb;

		$where     = $this->generate_where_clause( $args );
		$cache_key = $this->generate_cache_key( 'give_subscriptions_count', $args );
		$count     = wp_cache_get( $cache_key, 'subscriptions' );

		if ( $count === false ) {
			$count = $wpdb->get_var( "SELECT COUNT($this->primary_key) FROM " . $this->table_name . "{$where};" );
			wp_cache_set( $cache_key, $count, 'subscriptions', 3600 );
		}

		return absint( $count );
	}

	/**
	 * Get Renewing Subscriptions
	 *
	 * @param string $period
	 *
	 * @return array|bool|mixed|null|object
	 */
	public function get_renewing_subscriptions( $period = '+1month' ) {

		global $wpdb;

		$args = array(
			'number'     => 99999,
			'status'     => 'active',
			'offset'     => 0,
			'orderby'    => 'id',
			'order'      => 'DESC',
			'expiration' => array(
				'start' => date( 'Y-m-d H:i:s', strtotime( $period . ' midnight' ) ),
				'end'   => date( 'Y-m-d H:i:s', strtotime( $period . ' midnight' ) + ( DAY_IN_SECONDS - 1 ) )
			),
		);

		$where         = $this->generate_where_clause( $args );
		$cache_key     = $this->generate_cache_key( 'give_renewing_subscriptions', $args );
		$subscriptions = wp_cache_get( $cache_key, 'subscriptions' );

		$where .= ' AND `bill_times` != 0';
		$where .= ' AND ( SELECT COUNT(ID) FROM ' . $wpdb->prefix . 'posts WHERE `post_parent` = ' . $this->table_name . '.`parent_payment_id` OR `ID` = ' . $this->table_name . '.`parent_payment_id` ) + 1 < `bill_times`';

		if ( false === $subscriptions ) {
			$query         = $wpdb->prepare( "SELECT * FROM  $this->table_name $where ORDER BY {$args['orderby']} {$args['order']} LIMIT %d,%d;", absint( $args['offset'] ), absint( $args['number'] ) );
			$subscriptions = $wpdb->get_results( $query );
			wp_cache_set( $cache_key, $subscriptions, 'subscriptions', 3600 );
		}

		return $subscriptions;
	}

	/**
	 * Get Expiring Subscriptions
	 *
	 * @param string $period
	 *
	 * @return array|bool|mixed|null|object
	 */
	public function get_expiring_subscriptions( $period = '+1month' ) {

		global $wpdb;

		$args = array(
			'number'     => 99999,
			'status'     => 'active',
			'offset'     => 0,
			'orderby'    => 'id',
			'order'      => 'DESC',
			'expiration' => array(
				'start' => date( 'Y-m-d H:i:s', strtotime( $period . ' midnight' ) ),
				'end'   => date( 'Y-m-d H:i:s', strtotime( $period . ' midnight' ) + ( DAY_IN_SECONDS - 1 ) )
			),
		);

		$where         = $this->generate_where_clause( $args );
		$cache_key     = $this->generate_cache_key( 'give_expiring_subscriptions', $args );
		$subscriptions = wp_cache_get( $cache_key, 'subscriptions' );

		$where .= ' AND `bill_times` != 0';
		$where .= ' AND ( SELECT COUNT(ID) FROM ' . $wpdb->prefix . 'posts WHERE `post_parent` = ' . $this->table_name . '.`parent_payment_id` OR `ID` = ' . $this->table_name . '.`parent_payment_id` ) + 1 >= `bill_times`';

		if ( false === $subscriptions ) {
			$query         = $wpdb->prepare( "SELECT * FROM  $this->table_name $where ORDER BY {$args['orderby']} {$args['order']} LIMIT %d,%d;", absint( $args['offset'] ), absint( $args['number'] ) );
			$subscriptions = $wpdb->get_results( $query );
			wp_cache_set( $cache_key, $subscriptions, 'subscriptions', 3600 );
		}

		return $subscriptions;
	}

	/**
	 * Create the table
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function create_table() {

		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE " . $this->table_name . " (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		customer_id bigint(20) NOT NULL,
		period varchar(20) NOT NULL,
		initial_amount mediumtext NOT NULL,
		recurring_amount mediumtext NOT NULL,
		bill_times bigint(20) NOT NULL,
		parent_payment_id bigint(20) NOT NULL,
		product_id bigint(20) NOT NULL,
		created datetime NOT NULL,
		expiration datetime NOT NULL,
		status varchar(20) NOT NULL,
		profile_id varchar(60) NOT NULL,
		PRIMARY KEY  (id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}

}
