<?php 

/*
Plugin Name: Unique ID
Plugin URI: 
Description: generate a unique numeric id for username filed and replace it with user's username
Author: MehrdadEP
Version: 1.0.0
Tags:username, username generator
Author URI: https://github.com/mehrdadep
Text Domain: unique-id
Licence: GPLv2
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}

/**
* load text domain
*/
function unique_id_load_textdomain()
{
  load_plugin_textdomain('unique-id', false, plugin_basename(dirname(__FILE__)) . '/language');
}
add_action('plugins_loaded', 'unique_id_load_textdomain');

/*
 * register activation hooks
 */
function unique_id_activation()
{
  unique_id_default_options();
}
register_activation_hook(__FILE__, 'unique_id_activation');

/*
 * register deactivation hooks
 */
function unique_id_deactivation()
{
  delete_option('unique_id_initial_base');
  delete_option('unique_id_last_code');
  delete_option('unique_id_last_id');
}
register_deactivation_hook(__FILE__, 'unique_id_deactivation');

/**
* set default base code in activation
 */
function unique_id_default_options()
{
  if (false === get_option('unique_id_initial_base')) {
    add_option('unique_id_initial_base', unique_id_genrate_initial_base());
  }
}

/**
* this needs jalali plugin to be activated
* for georgian date use date() instead
* @author MehrdadEP
 */
function unique_id_genrate_initial_base()
{
  $year = ztjalali_english_num(jdate('y'));
  $month = ztjalali_english_num(jdate('m'));
  $base_code = $year . $month;
  return $base_code;
}

/**
* create an option page for unique id
 */
function unique_id_register_options_page()
{
  add_menu_page(
    __('Set Base Code', 'unique-id'),
    __('Unique ID', 'unique-id'),
    'manage_options',
    'unique-id',
    'unique_id_options_page',
    'dashicons-smiley',
    5
  );
}
add_action('admin_menu', 'unique_id_register_options_page');

/**
* register settings
 */
function unique_id_register_settings()
{
  register_setting('unique_id_options_group', 'unique_id_initial_base');
}
add_action('admin_init', 'unique_id_register_settings');

/**
* option page content
* contains setting a base code and generating a new user_login manually
* @author MehrdadEP
 */
function unique_id_options_page()
{
  ?>
  <div class='wp-admin aligncenter'>
  <?php screen_icon(); ?>
  <h2><?php echo __('Unique ID Setting', 'unique-id'); ?></h2>
  <hr/>
  <form method='post'action="options.php">
  <?php settings_fields('unique_id_options_group'); ?>
  <h3>
  <?php echo __('Set base 4 digit code', 'unique-id'); ?>
  </h3>
  <table>
  <tr>
  <td><label for='unique_id_options_group'>
  <?php echo __('Base Code', 'unique-id'); ?>
  </label>
  </td>
  <td>
  <input required onkeyup='uniqueIdCheckForInput()' type='text' 
  id='unique_id_initial_base' name='unique_id_initial_base'
   value='<?php echo get_option('unique_id_initial_base') ?>' />
   </td>
  </tr>
  </table>
  <p id='unique_id_message'></p>
  <?php submit_button(); ?>
  </form>
  <hr/>
  <h3><?php echo __('Generate New Code', 'unique-id'); ?></h3>
  <br/>
  <?php unique_id_generate_link() ?>
  </div>
  <?php

}

/**
* create a link to generate new user_login using AJAX call
* @author MehrdadEP
 */
function unique_id_generate_link()
{
  if (is_admin()) {
    ?>
      <?php echo __('Last Generated ID', 'unique-id') . ': ' . get_option('unique_id_last_id'); ?>
      <br/>  <br/>
  <button class='button button-primary' id='unique_id_new_request' onclick='unique_id_get_new_id()'>
  <?php echo __('Generate', 'unique-id'); ?>
  </button>
  <br/>
  <br/>
  <?php echo __('Code: ', 'unique-id'); ?>
  <input readonly type='text' name='unique_id_new_result' id='unique_id_new_result' />
  <?php

}
}

/**
* generate new user_login with ajax and json response
* @author MehrdadEP
*/
function unique_id_generate_new_id()
{
  $new_code = array();
  $new_code['id'] = unique_id_genrate_new_id();
  echo json_encode($new_code);
  exit();
}
add_action('wp_ajax_unique_id_generate_new_id', 'unique_id_generate_new_id');

/**
 * Register and enqueue a js in the WordPress admin.
 */
function unique_id_enqueue_admin_script($hook)
{
  wp_enqueue_script('unique_id_script', plugin_dir_url(__FILE__) . 'admin/js/app.js', array(), '1.0');
}
add_action('admin_enqueue_scripts', 'unique_id_enqueue_admin_script');

/**
* @return string last 6 digit generated code
* @author MehrdadEP
*/
function unique_id_get_last_code()
{
  if (false === get_option('unique_id_last_code')) {
    add_option('unique_id_last_code', 1);
  } else {
    $last_code = (int)get_option('unique_id_last_code');
    $last_code++;
    if ($last_code > 999999) {
      $last_code = 1;
      $base = (int)get_option('unique_id_initial_base');
      $base++;
      update_option('unique_id_initial_base', $base);
    }
    update_option('unique_id_last_code', $last_code);
  }
  return get_option('unique_id_last_code');
}

/**
* convert int last code to string
* @return string
* @author MehrdadEP
*/
function to_six_digit_string($int)
{
  $result = '';
  $to_str = (string)$int;
  $len = strlen($to_str);
  switch ($len) {
    case 1:
      $result = '00000' . $to_str;
      break;
    case 2:
      $result = '0000' . $to_str;
      break;
    case 3:
      $result = '000' . $to_str;
      break;
    case 4:
      $result = '00' . $to_str;
      break;
    case 5:
      $result = '0' . $to_str;
      break;
    case 6:
      $result = '' . $to_str;
      break;
  }
  return $result;
}

/**
* generate new user_login and update options
* @return string new user_login
* @author MehrdadEP
 */
function unique_id_genrate_new_id()
{
  $new_code = to_six_digit_string(unique_id_get_last_code());
  $base = get_option('unique_id_initial_base');
  $new_id = $base . $new_code;
  if (false === get_option('unique_id_last_id')) {
    add_option('unique_id_last_id', $new_id);
  } else {
    update_option('unique_id_last_id', $new_id);
  }

  return $new_id;
}

/**
* change user_login value on registration
* @author MehrdadEP
*/
function unique_id_callback_function($login)
{
  return unique_id_genrate_new_id();
}
add_filter('pre_user_login', 'unique_id_callback_function');
