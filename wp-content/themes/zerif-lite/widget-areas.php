<?php
/**
 * Created by PhpStorm.
 * User: sergii
 * Date: 12.08.15
 * Time: 19:55
 */

// Register the extra Footer Aside
$myfuter = array(
    'name' => 'Main Footer Aside',
    'id' => 'footer-aside',
    'description' => __('A widget area in the footer, above the subsidiary
?asides.', 'thematic'),
    'before_widget' => '<aside id="myfooter" class="widget myfooter">',

    'after_widget' => '</aside>',

    'before_title' => '<h1 class="widget-title">',

    'after_title' => '</h1>', );
function wicked_footer_aside() {
    global $myfuter;
    register_sidebar($myfuter
    );
}
add_action('init', 'wicked_footer_aside');
// Add footer Sidebar Area
function add_wicked_footer_aside() {
    global $myfuter;
    if (true) {
        echo $myfuter['before_widget'];
        dynamic_sidebar('footer-aside');
        echo $myfuter['after_widget'];
    }
}
//add_action('get_footer','add_wicked_footer_aside', 10);