<?php
/**
 * The [give_subscriptions] Shortcode Generator class
 *
 * @package     Give
 * @subpackage  Admin
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

defined( 'ABSPATH' ) or exit;

//Check if Give_Shortcode_Generator exists
//@see: https://github.com/WordImpress/Give-Recurring-Donations/issues/175
if ( ! class_exists( 'Give_Shortcode_Generator' ) ) {
	return;
}

class Give_Shortcode_Subscriptions extends Give_Shortcode_Generator {

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->shortcode['label'] = __( 'Give Subscriptions', 'give-recurring' );

		parent::__construct( 'give_subscriptions' );
	}


}

new Give_Shortcode_Subscriptions;
