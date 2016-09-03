/**
 * Give Admin Recurring JS
 *
 * @description: Scripts function in admin form creation (single give_forms post) screen
 *
 */
var Give_Recurring_Vars;

jQuery( document ).ready( function ( $ ) {


	var Give_Admin_Recurring_Subscription = {

		/**
		 * Initialize
		 */
		init: function () {

			this.confirm_cancel();
			this.confirm_delete();

		},

		/**
		 * Toggle Set Recurring Fields
		 *
		 * @description:
		 */
		confirm_cancel: function () {

			$( 'input[name="give_cancel_subscription"]' ).on( 'click', function () {
				var response = confirm( Give_Recurring_Vars.confirm_cancel );
				//Cancel form submit if user rejects confirmation
				if ( response !== true ) {
					return false;
				}
			} );


		},

		confirm_delete: function () {

			$( '.give-delete-subscription' ).on( 'click', function ( e ) {

				if ( confirm( Give_Recurring_Vars.delete_subscription ) ) {
					return true;
				}

				return false;
			} );

		}
	};

	Give_Admin_Recurring_Subscription.init();


} );