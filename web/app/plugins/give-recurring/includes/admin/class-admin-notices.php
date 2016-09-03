<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Give_Recurring_Admin_Notices {

	public function __construct() {
		$this->init();
	}

	public function init() {
		add_action( 'admin_notices', array( $this, 'notices' ) );
	}

	public function notices() {

		if ( ! give_is_admin_page( 'give-subscriptions' ) ) {
			return;
		}

		if ( empty( $_GET['give-message'] ) ) {
			return;
		}

		$type    = 'updated';
		$message = '';

		switch ( strtolower( $_GET['give-message'] ) ) {

			case 'updated' :

				$message = __( 'Subscription successfully updated', 'give-recurring' );

				break;

			case 'deleted' :

				$message = __( 'Subscription successfully deleted', 'give-recurring' );

				break;

			case 'cancelled' :

				$message = __( 'Subscription successfully cancelled', 'give-recurring' );

				break;

		}
		if ( ! empty( $message ) ) {
			echo '<div class="' . esc_attr( $type ) . '"><p>' . $message . '</p></div>';
		}
	}

}

$give_recurring_admin_notices = new Give_Recurring_Admin_Notices;