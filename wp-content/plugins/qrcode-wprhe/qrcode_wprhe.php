<?php
/* 

Plugin Name: qrcode_wprhe
Plugin URI: http://www.free-qr-code.net/qr-code-wordpress-plugin.html
Version: 1.2.5
Author: Rene Hermenau
Author URI: http://www.free-qr-code.net
Description: qrcode wordpress plugin to generate individual and URL relating qr codes within your wordpress articles


How to use it:

 * Use the shortcode [qrcode] within your content to generate the current URL
  
 * Use the shortcode [qrcode content="CONTENT" size="80" alt="ALT_TEXT" class="CLASS_NAME"] to generate a indivdual qr code with the content of CONTENT, 
   the SIZE in pixels e.g. 80, the ALT_TEXT and the CLASS_NAME you want to use
 
 * It`s not neccessary to give any parameters!
   
 * Possible parameters are: alt, size, class, credit, shadow 
 
 * If you don`t give any parameter like 'alt' or 'size', the standard parameters are:
    alt = "Scan the QR Code"
    size = 120
    class=""
    credit = true
    shadow = true

* The credit option gives a really small but nice looking image link on bottom of the qrcode. If you don´t like it or don´t want to give me any credits you can deactivate it with 'credit = false'. But i will be glad if you let that small link where it is :D'
 
* See http://www.free-qr-code.net/qr-code-wordpress-plugin.html for more info
* I am very thankful if you give me credit and a backlink to http://www.free-qr-code.net
 
*/


/* Shortcode  function */
  function qrcode_wprhe_shortcode($atts) {
    extract(shortcode_atts(array(
                'content' => '',
                'alt' => '',
                'size' => '',
                'align' => '',
		'class' => '',
                'credit' => '',
                'shadow' => ''
                    ), $atts));

    $current_uri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '';

    if (empty($content) && $content !== 0) {
        $content = urlencode($current_uri);
    } else {
        $content = urlencode(strip_tags(trim($content)));
    }
	
	
	
	if (empty($alt) && $alt !==0) {
	  $alt="Scan the QR Code";
	} else {
	  $alt = strip_tags(trim($alt));
        }
        
        if (empty($size) && $size !==0) {
	  $size = "120";
	} else {
	  $size = strip_tags(trim($size));
	}
        
         if (empty($align) && $align !==0) {
	  $align = "";
	} else {
	  $align = strip_tags(trim($align));
	}
       
         if (empty($class) && $class !==0) {
	  $class = "";
	} else {
	  $class = strip_tags(trim($class));
	}
        
        if (empty($credit) && $credit !=='false' or $credit == 'true') {
	  $credit_footer='<br><a href="http://www.free-qr-code.net" target="blank" style="display:block;"><img src="' . plugins_url( 'made_with_love.png', __FILE__ ) . '" border="0" alt="QR Code Generator"></a></div>';
	} else {
	  $credit_footer = "</div>";
        }
	
        if (empty($shadow) && $shadow =! false or $shadow == 'true') {
	  $preoutput = '<div style="text-align:center;width:' . $size . 'px;">';
	} else {
	  $preoutput = '<div style="text-align:center;width:' . $size . 'px;">';
	}
        
    $output = "";
    $image = 'https://chart.googleapis.com/chart?chs=' . $size . 'x' . $size . '&cht=qr&chld=H|1&chl=' . $content;
    if ($align == "right") {
        $align = ' align="right"';
    }
    if ($align == "left") {
        $align = ' align="left"';
    }
    if ($class != "") {
        $class = ' class="' . $class . '"';
    }

    $output = $preoutput . '<img id="qr_code_generator_wprhe" src="' . $image . '" alt="' . $alt . '" width="' . $size . '" height="' . $size . '"' . $align . $class . ' />';
    
	
    return $output . $credit_footer;
  }

  /* check if its necessary to embed js or css file into wp_head*/
add_action('wp_print_styles', 'add_my_styles', 100);
function add_my_styles() {
wp_register_style( 'wprhe_style', '/wprhe_qrcode_style.css');
wp_enqueue_style( 'wprhe_style' );
}
add_action('init', 'add_my_styles');
  
  /* Add the style */
  add_action( 'wp_print_styles', 'wp_enqueue_style' );
  
/* Add the Shortcode */
  add_shortcode('qrcode', 'qrcode_wprhe_shortcode');

?>