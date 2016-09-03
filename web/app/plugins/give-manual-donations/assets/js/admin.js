/*!
 * Give Manual Donations Admin JS
 *
 * @package:     Give_Manual_Donations
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2016, WordImpress
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

var give_md_vars;

jQuery(document).ready(function ($) {

    var form = $('#give_md_create_payment');
    var new_customer_btn = $('.give-payment-new-customer');
    var notice_wrap = $('#give-forms-table-notice-wrap');

    //Show/hide buttons
    new_customer_btn.on('click', function () {
        $(this).hide();
    });
    $('.give-payment-new-customer-cancel').on('click', function () {
        new_customer_btn.show();
    });

    /**
     * Form Submit
     */
    form.on('submit', function (e) {
        return give_md_validation();
    });

    /**
     * Validation
     *
     * @returns {boolean}
     */
    function give_md_validation() {

        //Empty any errors if present
        $('.give_md_errors').empty();
        var passed = false;

        //AJAX validate & submit
        $.ajax({
            type: "POST",
            url: ajaxurl,
            async: false,
            data: {
                action: 'give_md_validate_submission',
                fields: form.serialize()
            },
            dataType: "json",
            success: function (response) {

                //Error happened
                if (response !== 'success') {

                    //Loop through errors and output
                    $.each(response.error_messages, function (key, value) {
                        //Show errors
                        $('.give_md_errors').append('<div class="error"><p>' + value + '</p></div>');
                    });

                    //Scrolling to top
                    $('html, body').scrollTop(0);
                    //Not Passed validation
                    passed = false;
                }
                //Passed validation
                else {
                    //Pass it as true
                    passed = true;
                }

            }
        }).fail(function (data) {

            passed = false;

            if (window.console && window.console.log) {
                console.log(data);
            }
        });

        return passed;

    }


    /**
     * Recurring Messages
     *
     * @description: Outputs appropriate notification messages for admin according the the type of recurring enabled donation form
     * @param response
     */
    function give_md_recurring_messages(response) {

        //Add Subscription Information
        if (response.recurring_enabled && response.recurring_type == 'yes_donor') {

            notice_wrap.html('<div class="notice notice-warning"><p><input type="checkbox" id="confirm-subscription" name="confirm_subscription" value="1" /> <label for="confirm-subscription">' + response.subscription_text + '</label></p></div>');

        } else if (response.recurring_enabled && response.recurring_type == 'yes_admin') {
            notice_wrap.html('<div class="notice notice-success"><p>' + response.subscription_text + '</p></div><input type="hidden" id="confirm-subscription" name="confirm_subscription" value="1" />');
        } else {
            notice_wrap.empty();
        }
    }

    /**
     * Form Dropdown Change
     */
    form.on('change', '.md-forms', function () {

        var selected_form = $('option:selected', this).val();
        var form_table = $('.give-transaction-form-table');
        var notice_wrap = $('#give-forms-table-notice-wrap');


        if (parseInt(selected_form) != 0) {

            var give_md_nonce = $('#give_create_payment_nonce').val();

            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    action: 'give_md_check_for_variations',
                    form_id: selected_form,
                    nonce: give_md_nonce
                },
                dataType: "json",
                success: function (response) {

                    //Add Donation Level Dropdown if Applicable
                    if (typeof response.html !== 'undefined') {
                        $('.form-price-option-wrap').html(response.html);
                    } else {
                        $('.form-price-option-wrap').html('n/a');
                    }

                    give_md_recurring_messages(response);

                    //Add Donation Amount
                    $('input[name="forms[amount]"]').val(response.amount);
                }
            }).fail(function (data) {
                if (window.console && window.console.log) {
                    console.log(data);
                }
            });
        } else {
            $('.form-price-option-wrap').html('n/a');
            notice_wrap.empty();
        }
    });

    /**
     * Price Variation Change
     */
    form.on('change', '.give-md-price-select', function () {

        var price_id = $('option:selected', this).val();
        var give_md_nonce = $('#give_create_payment_nonce').val();
        var form_id = $('select[name="forms[id]"]').val();


        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: {
                action: 'give_md_variation_change',
                form_id: form_id,
                price_id: price_id,
                nonce: give_md_nonce
            },
            dataType: "json",
            success: function (response) {

                $('input[name="forms[amount]"]').val(response.amount);

                give_md_recurring_messages(response);

            }
        }).fail(function (data) {
            if (window.console && window.console.log) {
                console.log(data);
            }
            notice_wrap.empty();
        });

    });

    /**
     * Initialize the Datepicker
     */
    if ($('.form-table .give_datepicker').length > 0) {

        var datepicker_el = $('.give_datepicker');

        var date_format = give_md_vars.date_format;

        datepicker_el.datetimepicker({
            changeMonth: true,
            changeYear: true,
            yearRange: '2000:2050',
            dateFormat: date_format,
            defaultDate: new Date(),
            timeInput: true,
            timeFormat: "hh:mm tt",
            showHour: false,
            showMinute: false
        });

        datepicker_el.datepicker('setDate', new Date());

    }


});