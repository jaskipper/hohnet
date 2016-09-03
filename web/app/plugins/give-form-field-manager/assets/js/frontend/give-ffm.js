;
(function ( $ ) {
	var Give_FFM_Form = {
		init: function () {
			// clone and remove repeated field
			$( 'body' ).on( 'click', '.give-form span.ffm-clone-field', this.cloneField );
			$( 'body' ).on( 'click', '.give-form span.ffm-remove-field', this.removeField );


			$( 'body' ).on( 'submit', 'form.give-form', this.formSubmit );
			$( 'form.give-form' ).ajaxSuccess( this.resetForm );

			this.revealFields();

		},

		resetForm: function ( e ) {

			//wysiwyg tinymce rich editor
			$( this ).find( 'textarea.rich-editor' ).each( function () {
				var editor_id = $( this ).attr( 'name' );
				tinyMCE.execCommand( 'mceFocus', false, editor_id );
				tinyMCE.execCommand( 'mceRemoveEditor', false, editor_id );
				tinyMCE.execCommand( 'mceAddEditor', false, editor_id );
			} );

		},

		/**
		 * Reveal Fields
		 * @description When you create form fields and want them hidden until the user makes the donation decision and clicks "Donate Now" via the Reveal Upon Click option.
		 * @see: https://github.com/WordImpress/Give-Form-Field-Manager/issues/59
		 */
		revealFields: function () {

			//Hide fieldset so it's revealed
			$( '.give-display-reveal' ).each( function () {

				var reveal_btn = $( this ).find( '.give-btn-reveal' );
				var fieldset = reveal_btn.nextAll( '#give-ffm-section' );
				fieldset.hide();

				//Attach click handler to the button and this element too;
				reveal_btn.on( 'click', function () {
					fieldset.slideDown();
				} );

			} );

		},

		cloneField: function ( e ) {
			e.preventDefault();

			var $div = $( this ).closest( 'tr' );
			var $clone = $div.clone();
			// console.log($clone);

			//clear the inputs
			$clone.find( 'input' ).val( '' );
			$clone.find( ':checked' ).attr( 'checked', '' );
			$div.after( $clone );
		},

		removeField: function () {
			//check if it's the only item
			var $parent = $( this ).closest( 'tr' );
			var items = $parent.siblings().andSelf().length;

			if ( items > 1 ) {
				$parent.remove();
			}
		},


		formSubmit: function ( e ) {

			var form = $( this ),
				submitButton = form.find( 'input[type=submit]' ),
				form_data = Give_FFM_Form.validateForm( form );

			if ( form_data ) {
				return true;
			} else {
				// Prevent the form from submissing is there are errors
				e.preventDefault();
			}

		},

		validateForm: function ( self ) {

			var temp,
				temp_val = '',
				error = false,
				error_items = [];

			// remove all initial errors if any
			Give_FFM_Form.removeErrors( self );
			Give_FFM_Form.removeErrorNotice( self );

			// ===== Validate: Text and Textarea ========
			var required = self.find( '[data-required="yes"]' );

			required.each( function ( i, item ) {
				// temp_val = $.trim($(item).val());

				// console.log( $(item).data('type') );
				var data_type = $( item ).data( 'type' );
				val = '';
				//console.log( data_type );

				switch ( data_type ) {
					case 'rich':
						var name = $( item ).data( 'id' );
						val = $.trim( tinyMCE.get( name ).getContent() );

						if ( val === '' ) {
							error = true;

							// make it warn color
							Give_FFM_Form.markError( item );
						}
						break;

					case 'textarea':
					case 'text':
						val = $.trim( $( item ).val() );

						if ( val === '' ) {
							error = true;

							// make it warn color
							Give_FFM_Form.markError( item );
						}
						break;

					case 'select':
						val = $( item ).val();

						// console.log(val);
						if ( !val || val === '-1' ) {
							error = true;

							// make it warn color
							Give_FFM_Form.markError( item );
						}
						break;

					case 'multiselect':
						val = $( item ).val();

						if ( val === null || val.length === 0 ) {
							error = true;

							// make it warn color
							Give_FFM_Form.markError( item );
						}
						break;

					case 'checkbox':

						var length = $( item ).parent().find( 'input:checked' ).length;

						if ( !length ) {
							error = true;

							// make it warn color
							Give_FFM_Form.markError( item );
						}
						break;


					case 'radio':

						var length = $( item ).parent().find( 'input:checked' ).length;

						if ( !length ) {
							error = true;

							// make it warn color
							Give_FFM_Form.markError( item );
						}
						break;

					case 'file':
						var length = $( item ).next( 'ul' ).children().length;

						if ( !length ) {
							error = true;

							// make it warn color
							Give_FFM_Form.markError( item );
						}
						break;

					case 'email':
						var val = $( item ).val();

						if ( val !== '' ) {
							//run the validation
							if ( !Give_FFM_Form.isValidEmail( val ) ) {
								error = true;

								Give_FFM_Form.markError( item );
							}
						}
						break;


					case 'url':
						var val = $( item ).val();

						if ( val !== '' ) {
							//run the validation
							if ( !Give_FFM_Form.isValidURL( val ) ) {
								error = true;

								Give_FFM_Form.markError( item );
							}
						}
						break;

				}


			} );

			// if already some error found, bail out
			if ( error ) {
				// add error notice
				Give_FFM_Form.addErrorNotice( self );
				return false;
			}

			var form_data = self.serialize(),
				rich_texts = [];

			// grab rich texts from tinyMCE
			$( '.ffm-rich-validation' ).each( function ( index, item ) {
				temp = $( item ).data( 'id' );
				var val = $.trim( tinyMCE.get( temp ).getContent() );

				rich_texts.push( temp + '=' + encodeURIComponent( val ) );
			} );

			// append them to the form var
			form_data = form_data + '&' + rich_texts.join( '&' );
			return form_data;
		},

		addErrorNotice: function ( form ) {
			$( form ).find( '#give-purchase-button' ).attr( "disabled", false );

			$( form ).find( '#give_purchase_submit' ).prepend( '<div class="give_errors"><p class="give_error">' + give_ffm_frontend.error_message + '</p></div>' );
		},

		removeErrorNotice: function ( form ) {
			$( form ).find( '.ffm-error.give_errors' ).remove();
		},

		markError: function ( item ) {
			$( item ).closest( '.form-row' ).addClass( 'give-has-error' );
			$( item ).focus();
		},

		removeErrors: function ( item ) {
			$( item ).find( '.give-has-error' ).removeClass( 'give-has-error' );
		},

		isValidEmail: function ( email ) {
			var pattern = new RegExp( /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i );
			return pattern.test( email );
		},

		isValidURL: function ( url ) {
			var urlregex = new RegExp( "^(http:\/\/www.|https:\/\/www.|ftp:\/\/www.|www.|http:\/\/|https:\/\/){1}([0-9A-Za-z]+\.)" );
			return urlregex.test( url );
		}
	};

	$( function () {
		Give_FFM_Form.init();
	} );

})( jQuery );