jQuery(document).ready(function($){
    $('.menu-toggle').click(function(){
        $('body,#site-navigation').toggleClass('toggled');
    });
    $('.menu-toggle-2').click(function(){
        $('body').toggleClass('toggled-2');
    });
});