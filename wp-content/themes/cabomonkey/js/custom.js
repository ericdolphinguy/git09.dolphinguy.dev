jQuery(document).ready(function($){
    $('.menu-toggle').click(function(){
        $('body,#site-navigation').removeClass('toggled-2');
        $('body,#site-navigation').toggleClass('toggled');
    });
    $('.menu-toggle-2').click(function(){
        $('body,#site-navigation').removeClass('toggled-1');
        $('body,#site-navigation').toggleClass('toggled-2');
    });

    $(window).resize(function(){
        var width = $(this).width();

        if ( width <= 1230 ) {
            $('.offer').each(function(i,e){
                var coe = $('> .image .boxes .box-2 > div', e).detach();
                $('.ticket-box .tripadvisor', e).append(coe);

                var img = $('> .image .boxes .box-1 > img', e).detach();
                $('.ticket-box .coupon_image', e).append(img);
            });
        }
    });

    $(window).trigger('resize');
});