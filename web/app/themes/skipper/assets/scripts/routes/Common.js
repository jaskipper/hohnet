export default {
  init() {
    // JavaScript to be fired on all pages
    /* eslint-disable */

    /* eslint-enable */
  },
  finalize() {
    // JavaScript to be fired on all pages, after page specific JS is fired
    /* eslint-disable */
    AOS.init({duration: 1000, easing: 'ease-out-back'});
    $(function () {
      $('[data-toggle="tooltip"]').tooltip()
    })
    // Facebook Share Dialog
    $('#facebookShare').click(function(e) {
      e.preventDefault();
      var shareurl = $(this).attr('href');
      FB.ui({
        method: 'share',
        mobile_iframe: true,
        href: shareurl,
      }, function(response){});
    });
    // Mailchimp
    $('form.subscribe').submit(function(e) {
        e.preventDefault();
        var postdata = $(this).serialize();
        var myformparent = $(this).parent();
        $.ajax({
            type: 'POST',
            url: '/app/themes/skipper/src/lib/Mailchimp/subscribe.php',
            data: postdata,
            dataType: 'json',
            success: function(json) {
                if(json.valid == 0) {
                    $('.success-message', myformparent).hide();
                    $('.error-message', myformparent).hide();
                    $('.error-message', myformparent).html(json.message);
                    $('.error-message', myformparent).fadeIn('fast', function(){
                        myformparent.addClass('animated shake').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
                            myformparent.removeClass('animated shake');
                        });
                    });
                }
                else {
                    $('.error-message', myformparent).hide();
                    $('.success-message', myformparent).hide();
                    $('.btn', myformparent).hide();
                    $('.success-message', myformparent).html(json.message);
                    $('.success-message', myformparent).fadeIn('fast', function(){
                      //$('.top-content', this).backstretch("resize");
                        myformparent.find("input[type=text], textarea").val("");
                    });
                    // Set Cookie to not show dialog box for a year (needs Skipper's exitmodal function)
                    Cookies.set('exitmodalshown', true, { expires: 365 } );
                    modalshown = Cookies.get('exitmodalshown');
                }
            }
        });
    });
    /* eslint-enable */
  },
};
