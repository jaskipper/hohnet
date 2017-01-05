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
    var rellax = new Rellax('.rellax');
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
                }
            }
        });
    });
    /* eslint-enable */
  },
};
