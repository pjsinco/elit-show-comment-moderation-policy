<?php

/*
Plugin Name: Elit Show Comment Moderation Policy
Plugin URI:  
Description: After a reader posts a comment that hasn't been yet approved, open a modal that describes our comment moderation policy.
Version: 0.1.0
Author: Patrick Sinco
Author URI: https://github.com/pjsinco
License: GPL2
*/


// if this file is called directly, abort
if (!defined('WPINC')) {
  die;
}

/**
 * @uses Vex: http://github.hubspot.com/vex/docs/welcome/
 *
 * @see https://aftabmuni.wordpress.com/2016/06/01/
 *   session-based-flash-messages/
 * @see https://silvermapleweb.com/using-the-php-session-in-wordpress/
 * 
 */

function elit_start_session()
{
  if ( ! session_id() ) {
    session_start();
  }
}
add_action('init' , 'elit_start_session', 1);

function elit_end_session()
{
  session_destroy();
}
add_action('wp_logout' , 'elit_end_session', 1);
add_action('wp_login' , 'elit_end_session', 1);

function elit_enqueue_scripts_for_flash_message()
{
  
  if ( isset( $_SESSION['flash_message'] ) ) {

    wp_enqueue_style(
      'elit-story-gallery-styles',
      plugins_url( 'public/styles/elit-story-gallery.css', __FILE__ ),
      array(),
      filemtime( plugin_dir_path(__FILE__) . '/public/styles/elit-story-gallery.css' )
    );

    wp_enqueue_script(
      'vex-js',
      plugins_url( 'public/scripts/vex.combined.min.js', __FILE__ ),
      array(),
      false,
      true
    );

    wp_enqueue_style(
      'vex-style',
      plugins_url( 'public/styles/vex.css', __FILE__ ),
      array(),
      false
    );

    wp_enqueue_style(
      'vex-theme-style',
      plugins_url( 'public/styles/vex-theme-default.css', __FILE__ ),
      array( 'vex-style' ),
      false
    );
  }
}
add_action( 'wp_enqueue_scripts' , 'elit_enqueue_scripts_for_flash_message', 10 );


function elit_flash_moderation_message( $comment_id, $comment_approved )
{

  if ( $comment_approved === 1 ) {
    return;
  }

  $_SESSION['flash_message'] = get_flash_message();
}
add_action( 'comment_post' , 'elit_flash_moderation_message', 10, 3 );


function get_flash_message() 
{
  $flash_message  = '<h3>Thank you for your comment!</h3>';
  $flash_message .= 'Please note that all comments are moderated. ';
  $flash_message .= 'Review may take up to two business days.';
  $flash_message .= '<br><br>See the <a href="/comment-policy">';
  $flash_message .= 'comment policy</a> for details.';

  return $flash_message;
}

function elit_check_for_flash_message()
{

  if ( isset( $_SESSION['flash_message'] ) ) {

    $message = $_SESSION['flash_message'];

    $output  = '<script>' . PHP_EOL;
    $output .= 'vex.defaultOptions.className = "vex-theme-default"' . PHP_EOL;
    $output .= "vex.dialog.alert({ unsafeMessage: '$message' });" . PHP_EOL;
    $output .= '</script>' . PHP_EOL;

    echo $output;

    unset($_SESSION['flash_message']);
  }

}
add_action( 'wp_footer' , 'elit_check_for_flash_message', 100 );
