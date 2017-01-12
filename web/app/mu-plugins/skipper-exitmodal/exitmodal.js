var modalshown = Cookies.get('exitmodalshown');
jQuery(document).mouseleave(function() {
  //console.log('mouse leave');
  if (typeof modalshown === 'undefined') {
     // Show the exit popup
     jQuery('#skipper-exitmodal').modal('show');
     Cookies.set('exitmodalshown', true );
     //console.log('inside');
     modalshown = Cookies.get('exitmodalshown');
   }
});

/*   Add the following to your form.subscribe call to set your cookie to a year if the form is successfully submitted.
    Cookies.set('exitmodalshown', true, { expires: 365 } );
    modalshown = Cookies.get('exitmodalshown');
*/
jQuery(document).ready(function(){

  jQuery("#setexitcookie").click( function() {
    Cookies.set('exitmodalshown', true, { expires: 365 } );
    modalshown = Cookies.get('exitmodalshown');
    jQuery('#skipper-exitmodal').modal('hide');
  });

});
