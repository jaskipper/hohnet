<?php
/**
 * Form Field Manager Setup - Give_FFM_Setup
 *
 * @description : Script loading
 * @package     Give_FFM
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Give_FFM_Setup {

	private $suffix;

	public function __construct() {

		$this->suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue_scripts' ) );
	}

	/**
	 * Frontend Scripts
	 */
	public function frontend_enqueue_scripts() {

		//CSS
		wp_register_style( 'give_ffm_frontend_styles', GIVE_FFM_PLUGIN_URL . 'assets/css/give-ffm-frontend' . $this->suffix . '.css' );
		wp_enqueue_style( 'give_ffm_frontend_styles' );

		//JS
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'jquery-ui-slider' );
		wp_enqueue_script( 'plupload-handlers' );


		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {

			wp_register_script( 'give_jquery_ui_timepicker', GIVE_FFM_PLUGIN_URL . 'assets/js/plugins/jquery-ui-timepicker-addon' . $this->suffix . '.js', array( 'jquery-ui-datepicker' ) );
			wp_enqueue_script( 'give_jquery_ui_timepicker' );

			wp_register_script( 'give_ffm_frontend', GIVE_FFM_PLUGIN_URL . 'assets/js/frontend/give-ffm' . $this->suffix . '.js', array( 'jquery-ui-datepicker' ) );
			wp_enqueue_script( 'give_ffm_frontend' );

			wp_register_script( 'give_ffm_upload', GIVE_FFM_PLUGIN_URL . 'assets/js/plugins/give-ffm-upload.js', array(
				'jquery',
				'give_ffm_frontend',
				'plupload-handlers'
			) );
			wp_enqueue_script( 'give_ffm_upload' );

		} else {

			wp_register_script( 'give_ffm_frontend', GIVE_FFM_PLUGIN_URL . 'assets/js/frontend/give-ffm-frontend.min.js', array(
				'jquery',
				'jquery-ui-datepicker',
				'jquery-ui-slider'
			) );
			wp_enqueue_script( 'give_ffm_frontend' );

		}


		wp_localize_script( 'give_ffm_frontend', 'give_ffm_frontend', array(
			'ajaxurl'       => admin_url( 'admin-ajax.php' ),
			'error_message' => __( 'Please complete all required fields', 'give-ffm' ),
			'nonce'         => wp_create_nonce( 'ffm_nonce' ),
			'confirmMsg'    => __( 'Are you sure?', 'give-ffm' ),
			'plupload'      => array(
				'url'              => admin_url( 'admin-ajax.php' ) . '?nonce=' . wp_create_nonce( 'ffm_featured_img' ),
				'flash_swf_url'    => includes_url( 'js/plupload/plupload.flash.swf' ),
				'filters'          => array(
					array(
						'title'      => __( 'Allowed Files', 'give-ffm' ),
						'extensions' => '*'
					)
				),
				'multipart'        => true,
				'urlstream_upload' => true
			)
		) );

	}

	/**
	 * Admin Scripts
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {

		$current_screen = get_current_screen();

		//Only enqueue where necessary - Give Forms single CPT
		if ( $current_screen->post_type !== 'give_forms' ) {
			return;
		}

		//Unconcat scripts
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {

			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			wp_enqueue_script( 'suggest' );
			wp_enqueue_script( 'jquery-ui-slider' );

			wp_register_script( 'give_ffm_transition', GIVE_FFM_PLUGIN_URL . 'assets/js/plugins/transition.js', array( 'jquery' ) );
			wp_enqueue_script( 'give_ffm_transition' );

			wp_register_script( 'give_ffm_blockui', GIVE_FFM_PLUGIN_URL . 'assets/js/plugins/jquery-blockUI.js', array( 'jquery' ) );
			wp_enqueue_script( 'give_ffm_blockui' );

			wp_register_script( 'give_ffm_collapse', GIVE_FFM_PLUGIN_URL . 'assets/js/plugins/collapse.js', array( 'jquery' ) );
			wp_enqueue_script( 'give_ffm_collapse' );

			wp_register_script( 'give_jquery_ui_timepicker', GIVE_FFM_PLUGIN_URL . 'assets/js/plugins/jquery-ui-timepicker-addon' . $this->suffix . '.js', array( 'jquery-ui-datepicker' ) );
			wp_enqueue_script( 'give_jquery_ui_timepicker' );

			wp_register_script( 'give_ffm_formbuilder', GIVE_FFM_PLUGIN_URL . 'assets/js/admin/give-formbuilder.js', array( 'jquery' ) );
			wp_enqueue_script( 'give_ffm_formbuilder' );

			wp_register_script( 'give_ffm_upload', GIVE_FFM_PLUGIN_URL . 'assets/js/plugins/give-ffm-upload.js', array(
				'jquery',
				'give_ffm_formbuilder',
				'plupload-handlers'
			) );

			wp_enqueue_script( 'give_ffm_upload' );


		} else {

			//This one file contains all the goodies from above
			wp_register_script( 'give_ffm_formbuilder', GIVE_FFM_PLUGIN_URL . 'assets/js/admin/give-ffm-admin.min.js', array( 'jquery' ) );
			wp_enqueue_script( 'give_ffm_formbuilder' );

		}

		//AJAX vars
		wp_localize_script( 'give_ffm_formbuilder', 'give_ffm_formbuilder', array(
			'ajaxurl'       => admin_url( 'admin-ajax.php' ),
			'error_message' => __( 'Please fill out this required field', 'give-ffm' ),
			'nonce'         => wp_create_nonce( 'give_ffm_nonce' )
		) );

		wp_localize_script( 'give_ffm_formbuilder', 'give_ffm_frontend', array(
				 'confirmMsg' => __( 'Are you sure?', 'give-ffm' ),
				'nonce' => wp_create_nonce( 'ffm_nonce' ),
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'plupload' => array(
					 'url' => admin_url( 'admin-ajax.php' ) . '?nonce=' . wp_create_nonce( 'ffm_featured_img' ),
					'flash_swf_url' => includes_url( 'js/plupload/plupload.flash.swf' ),
					'filters' => array(
						 array(
							 'title' => __( 'Allowed Files' ),
							'extensions' => '*'
						)
					),
					'multipart' => true,
					'urlstream_upload' => true
				)
			) );
	}

	/**
	 * Admin Enqueue Styles
	 *
	 * @return void
	 */
	public function admin_enqueue_styles() {
		$current_screen = get_current_screen();

		if ( $current_screen->post_type !== 'give_forms' ) {
			return;
		}

		wp_register_style( 'give-ffm-formbuilder', GIVE_FFM_PLUGIN_URL . 'assets/css/give-ffm-backend' . $this->suffix . '.css' );
		wp_enqueue_style( 'give-ffm-formbuilder' );


	}

}
