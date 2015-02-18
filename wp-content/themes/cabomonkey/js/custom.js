jQuery(document).ready(function($){
    $('.menu-toggle').click(function(){
        $('body,#site-navigation').removeClass('toggled-2');
        $('body,#site-navigation').toggleClass('toggled');
    });
    $('.menu-toggle-2').click(function(){
        $('body,#site-navigation').removeClass('toggled-1');
        $('body,#site-navigation').toggleClass('toggled-2');
    });
});

jQuery(window).load(function(){
    $ = jQuery;

    $('.offer').each(function(i,e){
        var coe = $('> .image .boxes .box-2 > div', e).clone( true, true );
        $('.ticket-box .tripadvisor', e).append(coe);

        var img = $('> .image .boxes .box-1 > img', e).clone( true, true );
        $('.ticket-box .coupon_image', e).append(img);
    });
});