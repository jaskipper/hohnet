<?php
/**
 * Form Field Manager Install
 *
 * @package     Give_FFM
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Give_FFM_Install {

	public function init() {
		$db_version = get_option( 'give_ffm_version' );

		if ( ! $db_version ) {
			update_option( 'give_ffm_version', GIVE_FFM_VERSION );
		} else {
			return;
		}
	}

	/**
	 * Update To
	 */
	public function update_to() {
		$version = get_option( 'give_ffm_version', '1.0.0' );

		switch ( $version ) {
			case '1.0':
				break;
			default:
				// clean
				break;
		}
	}
}