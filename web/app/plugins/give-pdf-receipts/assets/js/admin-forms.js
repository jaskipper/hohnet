/**
 * Give PDF Receipts Admin Settings JS
 */

jQuery.noConflict();
var give_pdf_vars;
(function ($) {

    $(function () {

        // Save default template id
        var template_id_prev = $('#give_pdf_receipt_template').val();

        // If template title changed
        var is_template_name_change = false;

        //Flag when template name input changed
        $('#give_pdf_receipt_template_name').on('input', function () {
            is_template_name_change = true;
        });

        /**
         * If template content changed this function show the confirm window
         *
         * @param template_id Template id
         * @param callback Callback function
         */
        function tinymce_change_confirm(template_id, callback) {

            // If pdf title or content changed
            if (is_template_name_change || tinyMCE.get('give_pdf_builder').isDirty()) {
                if (confirm(give_pdf_vars.not_saved)) {
                    template_id_prev = template_id;
                    callback();

                    tinyMCE.get('give_pdf_builder').isNotDirty = 1;
                    is_template_name_change = false;
                    // Return previous selected option
                } else {
                    $('#give_pdf_receipt_template').val(template_id_prev);
                }
            } else {
                template_id_prev = template_id;
                callback();
            }
        }

        /**
         * Set template title and content
         *
         * @param template_id
         */
        function load_template_fields(template_id) {
            // Get template data
            $.post(ajaxurl, {
                action: 'get_builder_content',
                template_id: template_id
            }, function (data) {
                $('#give_pdf_receipt_template_name').val(data.post_title);

                //Focus on Template Name Field if selecting a "Receipt Template"
                var template_name = $('#give_pdf_receipt_template option:selected').text();

                if (template_name.indexOf('Receipt Template') >= 0) {
                    $('#give_pdf_receipt_template_name').val('').focus();
                }

                tinyMCE.get('give_pdf_builder').setContent(data.post_content);

            }, 'json');
        }

        /**
         * Reset template title and content
         */
        function reset_template_fields() {
            $('#give_pdf_receipt_template_name').val('');
            tinyMCE.get('give_pdf_builder').setContent('');
        }

        /**
         * On Template Change
         *
         * @description: Prompt to save or output a new template
         */
        $('#give_pdf_receipt_template').on('change', function () {

            var template_id = $('#give_pdf_receipt_template').val();

            // Selecting existing template
            if (template_id != 'create_new') {
                tinymce_change_confirm(template_id, function () {
                    load_template_fields(template_id);
                    tinyMCE.get('give_pdf_builder').isNotDirty = 1;
                });
                // Reset fields if create new template
            } else {
                tinymce_change_confirm(template_id, function () {
                    reset_template_fields();
                });
            }

        });

        /**
         * Ensure Template is Saved w/ Unique Name upon Settings Saved
         */
        $('form#options_page').on('submit', function (e) {

            //Sanity Check: Only when "Custom PDF Builder" option is enabled
            if ($('#give_pdf_generation_method').val() !== 'custom_pdf_builder') {
                return true;
            }

            var template_name = $('#give_pdf_receipt_template_name').val();

            //If the PDF Builder has not been saved before and is not trying to save over an existing template
            if (tinyMCE.get('give_pdf_builder').isDirty() && is_template_name_change == false && $('#give_pdf_receipt_template:contains(' + template_name + ')').length == 0 || template_name.indexOf('Receipt Template') >= 0 || template_name.length === 0) {
                //Prevent saving of form
                e.preventDefault();
                //Alert user of the issue
                alert(give_pdf_vars.template_customized);
                $('#give_pdf_receipt_template_name').val('').focus();
                return false;

            }

        });

        /**
         * Switch generation method
         */
        $('#give_pdf_generation_method').on('change', function () {
            var generation_method_val = $('#give_pdf_generation_method option:selected').val();
            switch (generation_method_val) {
                case 'custom_pdf_builder':
                    // Hide default template fields
                    $('.cmb2-id-give-pdf-templates, .cmb2-id-give-pdf-logo-upload, .cmb2-id-give-pdf-company-name, .cmb2-id-give-pdf-name, .cmb2-id-give-pdf-address-line1, .cmb2-id-give-pdf-address-line2, .cmb2-id-give-pdf-address-city-state-zip, .cmb2-id-give-pdf-email-address, .cmb2-id-give-pdf-url, .cmb2-id-give-pdf-additional-notes, .cmb2-id-give-pdf-enable-char-support, .cmb2-id-give-pdf-header-message, .cmb2-id-give-pdf-footer-message').hide();
                    // Show custom template fields
                    $('.cmb2-id-give-pdf-preview-template, .cmb2-id-give-pdf-receipt-template, .cmb2-id-give-pdf-receipt-template-name, .cmb2-id-give-pdf-builder').show();
                    break;
                case 'set_pdf_templates':
                    // Show default template fields
                    $('.cmb2-id-give-pdf-templates, .cmb2-id-give-pdf-logo-upload, .cmb2-id-give-pdf-company-name, .cmb2-id-give-pdf-name, .cmb2-id-give-pdf-address-line1, .cmb2-id-give-pdf-address-line2, .cmb2-id-give-pdf-address-city-state-zip, .cmb2-id-give-pdf-email-address, .cmb2-id-give-pdf-url, .cmb2-id-give-pdf-additional-notes, .cmb2-id-give-pdf-enable-char-support, .cmb2-id-give-pdf-header-message, .cmb2-id-give-pdf-footer-message').show();
                    // Hide custom template fields
                    $('.cmb2-id-give-pdf-preview-template, .cmb2-id-give-pdf-receipt-template, .cmb2-id-give-pdf-receipt-template-name, .cmb2-id-give-pdf-builder').hide();
                    break;
            }
        }).change();


        /**
         * Validation: Ensure the "Template Name" field is filled out
         */
        $('#give_pdf_receipt_template_name').on('focusout', function () {

            if ($(this).val() == '') {
                $(this).css('border', '1px solid red');
            } else {
                $(this).css('border', '1px solid #ddd');
            }

        });


    });

})(jQuery);