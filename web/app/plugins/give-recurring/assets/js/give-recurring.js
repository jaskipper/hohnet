/**
 * Give Frontend Recurring JS
 *
 * @description: Scripts function in frontend form
 *
 */
var Give_Recurring_Vars;

jQuery(document).ready(function ($) {

    var doc = $(document);

    var Give_Recurring = {

        /**
         * Initialize
         */
        init: function () {

            Give_Recurring.confirm_subscription_cancellation();
            Give_Recurring.conditional_account_creation();

        },

        /**
         * Toggle Set Recurring Fields
         *
         * @description:
         */
        conditional_account_creation: function () {

            //Only w/o Email Access Enabled
            if (Give_Recurring_Vars.email_access == 'on') {
                return false;
            }

            //On Page Load: When page loads loop through each form
            $('form[id^=give-form].give-recurring-form').each(function () {
                Give_Recurring.toggle_register_login_fields_onload($(this));
            });

            //On Gateway Load
            doc.on('give_gateway_loaded', function (ev, response, form_id) {
                Give_Recurring.toggle_register_login_fields_onload($('.give-recurring-form#' + form_id));
            });

            //When a level is clicked then toggle account creation based on whether it's recurring or not
            $('.give-donation-level-btn, .give-radio-input-level, .give-select-level > option, .give-recurring-donors-choice > input').on('click touchend', function () {
                Give_Recurring.toggle_register_login_fields_onclick($(this));
            });


        },

        /**
         * Toggle Register Login Fields on Load
         *
         * @param form
         */
        toggle_register_login_fields_onload: function (form) {

            var selected_level = form.find('.give-donation-levels-wrap .give-default-level');

            var admin_choice = form.find('.give-recurring-admin-choice');

            //No action needed for admin choice
            if(admin_choice.length > 0) {
                return false;
            }
            
            //Check for select option
            if (selected_level.length == 0) {
                selected_level = form.find('.give-select-level > .give-default-level');
            }

            var donors_choice = form.find('.give-recurring-donors-choice input');
            var login_fieldset = form.find('.give-login-account-wrap').hide();
            var register_fieldset = form.find('[id^=give-register-account-fields]').hide();
            var hidden_register = form.find('[name=give-purchase-var]');

            //If recurring show register/login fields
            if (selected_level.hasClass('give-recurring-level') || donors_choice.prop('checked')) {
                login_fieldset.show();
                register_fieldset.show();
                hidden_register.val('needs-to-register');
            } else {
                login_fieldset.hide();
                register_fieldset.hide();
                hidden_register.val('');
            }

        },


        /**
         * Toggle Register Login Fields on Click
         *
         * @param level
         */
        toggle_register_login_fields_onclick: function (level) {

            var form = level.parents('form[id^=give-form]');

            //Is Form Recurring? If not, bail
            if (!form.hasClass('give-recurring-form')) {
                return;
            }

            var login_fieldset = form.find('.give-login-account-wrap').hide();
            var register_fieldset = form.find('[id^=give-register-account-fields]').hide();
            var hidden_register = form.find('[name=give-purchase-var]');

            //Is this a recurring level or is donor's choice recurring checkbox checked
            if (level.hasClass('give-recurring-level') || level.prop('checked')) {
                login_fieldset.show();
                register_fieldset.show();
                hidden_register.val('needs-to-register');
            } else {
                login_fieldset.hide();
                register_fieldset.hide();
                hidden_register.val('');
            }
        },

        /**
         * Confirm Cancellation
         *
         * @description:
         */
        confirm_subscription_cancellation: function () {

            $('.give-cancel-subscription').on('click touchend', function (e) {
                var response = confirm(Give_Recurring_Vars.messages.confirm_cancel);
                //Cancel form submit if user rejects confirmation
                if (response !== true) {
                    return false;
                }
            });

        }


    };

    Give_Recurring.init();


});