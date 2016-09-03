/*global $:false */
jQuery(document).ready(function($) {
    'use strict';
    $('.nav.navbar-nav').onePageNav({
        currentClass: 'active',
        changeHash: false,
        scrollSpeed: 1300,
        scrollOffset: 65,
        scrollThreshold: 0.3,
        filter: ':not(.no-scroll)'
    });

    $('.btn').addClass('hvr-grow-shadow');

    function twitterSize() {
        var newsheight = $('.fp-blogentries').height();
        //console.log('newsheight='+newsheight);
        $('#twitter-widget-0').height(newsheight);
    }
    twitterSize();
    /*var stickyNavTop = $('#masthead').offset().top;
    var stickyNav = function() {
        var scrollTop = $(window).scrollTop();
        if (scrollTop > stickyNavTop) {
            $('#masthead').addClass('sticky');
            $('.navbar-brand').addClass('sticky2');
        } else {
            $('#masthead').removeClass('sticky');
            $('.navbar-brand').removeClass('sticky2');
        }
    };*/

    function ShowHide(id, visibility, displaystyle) {
        obj = document.getElementsByTagName("div");
        obj[id].style.visibility = visibility;
        obj[id].style.display = displaystyle;
    }

    function headersetup() {
        var adminbarheight = $('#wpadminbar').height();
        var headerheight = $('.site-header').height();
        if (adminbarheight > 0) {
            $('.site-header').css('top', (adminbarheight) + 'px');
        }
        $('#default-starter').css('padding-top', headerheight + 'px');
    }

    function teamboxbouncein() {
        var windowsize = $(window).width();
        console.log('window width: ' + windowsize);
        $('.animatedParent .team-box').each(function() {
            $(this).removeClass(
                'fadeInLeft slow fadeInUp fadeInDown fadeInRight'
            );
        });
        if (windowsize >= 992) {
            $('.animatedParent:nth-of-type(1) .team-box').addClass(
                'animated fadeInLeft slow');
            $('.animatedParent:nth-of-type(2) .team-box').addClass(
                'animated fadeInUp slow');
            $('.animatedParent:nth-of-type(3) .team-box').addClass(
                'animated fadeInUp slow');
            $('.animatedParent:nth-of-type(4) .team-box').addClass(
                'animated fadeInRight slow');
        } else if (windowsize >= 768 || windowsize <= 991) {
            $('.animatedParent:nth-of-type(odd) .team-box').addClass(
                'animated fadeInLeft slow');
            $('.animatedParent:nth-of-type(even) .team-box').addClass(
                'animated fadeInRight slow');
        } else {
            $('.animatedParent .team-box').addClass(
                'animated bounceInLeft slow');
        }
        $('#carousel-main .item:nth-of-type(1) h2').addClass(
            'bounceInRight');
        $('#carousel-main .item:nth-of-type(1) .lead').addClass(
            'bounceInLeft');
        $('#carousel-main .item:nth-of-type(2) h2').addClass(
            'bounceInUp');
        $('#carousel-main .item:nth-of-type(2) .lead').addClass(
            'bounceInDown');
        $('#carousel-main .item:nth-of-type(3) h2').addClass(
            'bounceInRight');
        $('#carousel-main .item:nth-of-type(3) .lead').addClass(
            'bounceInLeft');
        //$('#carousel-main .item:nth-of-type(3) .carousel-caption').addClass('animated growIn go delay-500');
        $('#carousel-main .item:nth-of-type(4) h2').addClass(
            'bounceInUp');
        $('#carousel-main .item:nth-of-type(4) .lead').addClass(
            'bounceInDown');
        $('#carousel-main .item:nth-of-type(5) h2').addClass(
            'growIn');
        $('#carousel-main .item:nth-of-type(5) .lead').addClass(
            'growIn');
    }
    $('.carousel').carousel({
        interval: 10000,
        autostart: true
    })
    $('.navbar-collapse a').click(function(e) {
        $('.navbar-collapse').collapse('toggle');
    });
    //new WOW().init();
    headersetup();
    teamboxbouncein();
    //stickyNav();
    $(".imgLiquidFill").imgLiquid();
    $('.footer-sm a, .footer-links a').webuiPopover({
        animation: 'fade',
        trigger: 'hover',
        style: 'inverse'
    });

    $('form.subscribe').submit(function(e) {
        e.preventDefault();
        var postdata = $(this).serialize();
        var myformparent = $(this).parent();
        $.ajax({
            type: 'POST',
            url: '/app/themes/starter/lib/subscribe.php',
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

    $(window).resize(function() {
        headersetup();
        teamboxbouncein();
        twitterSize();
    })
    $(window).scroll(function() {
        //stickyNav();
        headersetup();
        //Scroll to Top
        if ($(this).scrollTop() >= 200) { // If page is scrolled more than 50px
            $('#return-to-top').css('opacity', '1'); // Fade in the arrow
        } else {
            $('#return-to-top').css('opacity', '0'); // Else fade out the arrow
        }
    });
    $(function() {
        $("[rel='tooltip']").tooltip();
    });
});
