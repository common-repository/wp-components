<?php
/*
Plugin Name: WP-Components
Plugin URI: http://www.cagintranet.com/archive/wp-components-plugin/
Description: Allows you to include static data into themes so it is easy for the webmaster to update. 
Version: 0.7
Author: Chris Cagle
Author URI: http://www.cagintranet.com/
*/

/*  Copyright 2008  Chris Cagle, Cagintranet Web Design  (email : admin@cagintranet.com)

		This program is free software; you can redistribute it and/or modify
		it under the terms of the GNU General Public License as published by
		the Free Software Foundation; either version 2 of the License, or
		(at your option) any later version.

		This program is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
		GNU General Public License for more details.

		You should have received a copy of the GNU General Public License
		along with this program; if not, write to the Free Software
		Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
		
		
		TODO LIST:
			@todo Add tinymce to textareas. This will be based on user's preference set in their profile. 
			@todo Get nonce to work properly. Fields are added, but it's not being verified.              
			@todo Should it be added to the 'Settings' admin page?                                        
			@todo Internationalize the plugin. 
			                                                           
    
		SYNTAX:
			<?php get_component('component_slug') ?>
			<?php if (function_exists('get_component')) { get_component('component_slug'); } ?>
		
*/



/********************************************************************
* PACKAGE VARIABLES
* @package wp-components
*
*/
	if ( !defined('WP_CONTENT_URL') )
	    define( 'WP_CONTENT_URL', $url . '/wp-content');
	if ( !defined('WP_CONTENT_DIR') )
	    define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
	$plugin = plugin_basename(__FILE__);
	$url = get_settings('siteurl');
	$wpcomponent_plugin_file = WP_CONTENT_DIR . '/wp-components/wp-components.php';
	$wpcomponent_ver = "0.7";



/********************************************************************
* Admin Panel: Register Hooks & Filters
* @package wp-components
*
*/
register_activation_hook(__FILE__,"wpcomponent_activate");
register_deactivation_hook(__FILE__,"wpcomponent_deactivate");
//add_action('admin_menu','wpcomponent_add_admin_page');
//add_action('admin_head', 'wpcomponent_add_header', 0);
add_action('admin_menu', 'wpcomponent_add_add_page');
add_filter( 'plugin_action_links_' . $plugin, 'wpcomponent_add_action_link' );
wpcomponent_undosave();



/********************************************************************
* Admin Panel: Activate Plugin
* @package wp-components
*
*/
function wpcomponent_activate () {
	add_option("wpcomponent_version", $wpcomponent_ver);
	add_option("wpcomponent_data", '');
	add_option("wpcomponent_data_bak", '');
}



/********************************************************************
* Admin Panel: Deactivate Plugin
* @package wp-components
*
*/
function wpcomponent_deactivate () {
	delete_option("wpcomponent_version");
}



/********************************************************************
* Admin Panel: Add Management Page to Database
* @package wp-components
*
*/
//function wpcomponent_add_admin_page () {
//  add_options_page(
//    'WP-Components',			//Title
//    'WP-Components',			//Sub-menu title
//    'manage_options',			//Security
//    __FILE__,					//File to open
//    'wpcomponent_options'				//Function to call
//  );  
//}

function wpcomponent_add_add_page() {
	$mypage = add_options_page( 'WP-Components', 'WP-Components', 9, __FILE__, 'wpcomponent_options' );
	add_action( "admin_print_scripts-$mypage", 'wpcomponent_add_header' );
}


/********************************************************************
* Admin Panel: Add Settings Link to Plugins Page
* @package wp-components
*
*/
function wpcomponent_add_action_link($links) {
	$settings_link = '<a href="options-general.php?page=wp-components/wp-components.php">' . __('Action') . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}



/********************************************************************
* Admin Panel: Add jQuery and CSS to Admin Header
* @package wp-components
* 
*/
function wpcomponent_add_header() {
  $css_file = WP_CONTENT_URL . '/plugins/wp-components/wpcomponent.css';
  $js_file = WP_CONTENT_URL . '/plugins/wp-components/wpcomponent.js';
  echo '<link rel="stylesheet" type="text/css" href="' . $css_file . '" />' . "\n";
  //wp_enqueue_script('jquery');
  echo '<script type="text/javascript" src="' . $js_file . '"></script>' . "\n";
}



/********************************************************************
* Admin Panel: Create Admin Page
* @package wp-components
* @uses wpcomponent_update_options() 	{internal function}
*       wpcomponent_form()						{internal function}
*
*/
function wpcomponent_options () {
	if (function_exists(wp_verify_nonce)) {
		$valid_nonce = wp_verify_nonce($_REQUEST['_wpnonce'], 'wpc-nonce-form');
	} else {
		$valid_once = true;
	}
	echo '<div class="wrap"><div id="icon-plugins" class="icon32"><br /></div><h2>WP-Components</h2>';
	if (isset($_REQUEST['submit']) && $valid_nonce) {
		wpcomponent_update_options();
	}
	wpcomponent_form();
	echo '</div>';	
}



/********************************************************************
* Admin Panel: Display Update Notification
* @package wp-components
*
*/
function wpcomponent_update_notify($updated = false) {
	if ($updated) {
		$nonce = wp_create_nonce( 'wpc-nonce-undo' );
		echo '<div id="message" class="updated fade">';
		echo '<p><b>'. __('Success') .':</b> '. __('Your components have been updated') . '! <a href="'. $_SERVER['PHP_SELF'] .'?page=wp-components/wp-components.php&undo=true&_wpnonce='. $nonce .'">'. __('Undo') . '</a></p>';
		echo '</div>';
	} else {
		echo '<div id="message" class="error fade">';
		echo '<p><b>'. __('Error') . ':</b> '. __('Unable to update your components') . '.</p>';
		echo '</div>';
	}
}



/********************************************************************
* Admin Panel: Clean Up String Function
* @package wp-components
*
*/
function wpc_clean_meup($text)  { 
	$text= strip_tags(strtolower($text)); 
	$code_entities_match = array(' ','--','&quot;','!','@','#','$','%','^','&','*','(',')','_','+','{','}','|',':','"','<','>','?','[',']','\\',';',"'",',','.','/','*','+','~','`','='); 
	$code_entities_replace = array('-','-','','','','','','','','','','','','','','','','','','','','','','','',''); 
	$text = str_replace($code_entities_match, $code_entities_replace, $text); 
	$text = urlencode($text);
	return $text; 
} 



/********************************************************************
* Admin Panel: Prep String for mySQL Insert
* @package wp-components
*
*/
function mysql_prep($value){ 
    $value = htmlspecialchars($value);
    return $value; 
} 



/********************************************************************
* Admin Panel: Function to Sort Components
* @package wp-components
* @taken-from http://codingforums.com/showthread.php?t=71904
*
*/
function wpcomponent_sortarray($array, $index, $order='asc', $natsort=FALSE, $case_sensitive=FALSE) { 
	if(is_array($array) && count($array)>0) { 
	   foreach(array_keys($array) as $key)  
	      $temp[$key]=$array[$key][$index]; 
	      if(!$natsort) {
	      	($order=='asc')? asort($temp) : arsort($temp);
	      } else { 
	      	($case_sensitive)? natsort($temp) : natcasesort($temp); 
	      	if($order!='asc') {
	         	$temp=array_reverse($temp,TRUE);
	        } 
	   		} 
				foreach(array_keys($temp) as $key)  
					(is_numeric($key))? $sorted[]=$array[$key] : $sorted[$key]=$array[$key]; 
				return $sorted; 
	} 
	return $array; 
}  



/********************************************************************
* Admin Panel: Undo the Last Save Action
* @package wp-components
* 
*/
function wpcomponent_undosave() {
	if (function_exists(wp_verify_nonce)) {
		$valid_nonce = wp_verify_nonce($_REQUEST['_wpnonce'], 'wpc-nonce-undo');
	} else {
		$valid_nonce = true;
	}
	if (isset($_REQUEST['undo']) && $valid_nonce) {
		$restored_data = get_option('wpcomponent_data_bak');
		$undo_data = get_option('wpcomponent_data');
		update_option('wpcomponent_data_bak', $undo_data);
		update_option('wpcomponent_data', $restored_data);
		$re_url = $_SERVER['PHP_SELF'] .'?page=wp-components/wp-components.php';
		header('Location: '. $_SERVER['PHP_SELF'] .'?page=wp-components/wp-components.php');
		//wp_redirect($re_url);
		$updated = true;
		wpcomponent_update_notify(true);
	} 
}	



/********************************************************************
* Admin Panel: Update Plugin Options
* @package wp-components
* @uses wpc_clean_meup() 				{internal function}
*       wpcomponent_form()				{internal function}
*       wpcomponent_sortarray()			{internal function}
*
*/
function wpcomponent_update_options() {
	$updated = false;
	$value = $_POST['wpcomponent_val'];
	$slug = $_POST['wpcomponent_slug'];
	$title = $_POST['wpcomponent_title'];
	$ids = $_POST['wpcomponent_id'];
	$comp_array = array();
	$count = 0;
	if (empty($ids)) {
		$dataout = '';
	} else {
		foreach ($ids as $id) {
			if ( $slug[$count] == null ) { $slug[$count] = wpc_clean_meup($title[$count]); }
			$comp_array[] = array($slug[$count],wpc_clean_meup($title[$count]),mysql_prep($value[$count]));
			$count++;
		}
		$data = wpcomponent_sortarray($comp_array, 1);
		$dataout = serialize($data);
	}
	$bakdata = get_option('wpcomponent_data');
	update_option('wpcomponent_data_bak', $bakdata); // a little backup insurance
	update_option('wpcomponent_data', $dataout);
	$updated = true;
	wpcomponent_update_notify(true);
}



/********************************************************************
* Admin Panel: Create Add/Delete/Edit Component Form
* @package wp-components
*
*/
function wpcomponent_form() {
	$components = unserialize(get_option('wpcomponent_data'));
	$count = 0;
	echo '<form class="components" method="post" >' . "\n";
	echo '<p><input type="submit" id="submit" name="submit" class="button-secondary button-primary" value="Save Changes" /> &nbsp;&nbsp; <a class="button rbutton" href="#" onClick="addFormField(); return false;">Add New Component</a></p>' . "\n";
	echo '<div id="divTxt"></div>' . "\n\n";
		/* 
		* 
		* Variable Explainations
		* $component[0] is the component slug (used in template tag)
		* $component[1] is the title 
		* $component[2] is the body text (value)
		* $count is arbitrary. It can change depending additions or subtractions to array
		* 
		*/
	if (empty($components)) { 
		$count = 0;
	} else {
		foreach ($components as $component) {
				echo "<div id='section-".@$count."' class='eachsection'><h3>Component:&nbsp;&nbsp;<em>". @$component[1] ."</em> <a href='#' class='collapse' id='collapse-".@$count."' onClick=\"collapseComp('".@$count."'); return false;\">&mdash;</a> <a href='#' class='expand' id='expand-".@$count."' onClick=\"expandComp('".@$count."'); return false;\">+</a></h3>" . "\n";
				echo "<div id='inside-".@$count."'><p class='form-field'><label>Title:</label><br />" . "\n";
				echo "<input type='text' class='wpc_input' name='wpcomponent_title[]' value='". @$component[1] ."' /></p>" . "\n";
				echo "<p class='form-field'><label>Body:</label><br />" . "\n";
				echo "<textarea rows='5' class='wpc_textarea tinymce'  name='wpcomponent_val[]'>". stripslashes(html_entity_decode(@$component[2], ENT_QUOTES)) ."</textarea>" . "\n";
				echo "<input type='hidden' name='wpcomponent_slug[]' value='". @$component[0] ."' />" . "\n";
				echo "<input type='hidden' name='wpcomponent_id[]' value='". @$count ."' /></p>" . "\n";
				echo "<p><small>Usage: <b>&lt;?php get_component('".@$component[0]."'); ?&gt;</b>&nbsp;&nbsp;&nbsp;&nbsp;" . "\n";
				?>
				<a href='#' class='deletion delete' onclick="if ( confirm('You are about to delete the <?php echo @$component[1]; ?> component') ) { removeFormField('#section-<?php echo @$count; ?>')}return false; ">delete</a></small></p></div>
				<?php
				echo "</div>\n"; 
				$count++;
		}
	}
	echo '<input type="hidden" id="wpcomponent_id" value="'. @$count .'" />' . "\n";
	wp_nonce_field('wpc-nonce-form');    
	echo '<p><input type="submit" id="submit" name="submit" class="button-secondary button-primary" value="Save Changes" /> &nbsp;&nbsp; <a class="button rbutton" href="#" onClick="addFormField(); return false;">Add New Component</a></p>' . "\n";
	echo '</form>' . "\n\n";
	echo '<p style="margin:40px 0;" >For more information on this plugin, please visit <a href="http://www.cagintranet.com/archive/wp-components-plugin/">http://www.cagintranet.com/archive/wp-components-plugin/</a></p>' . "\n";
}



/********************************************************************
* Theme: The Template Tag
* @package wp-components
*
*/
function get_component($id) {
	$components = get_option('wpcomponent_data');
	$components = unserialize($components);
	if ($components != null) { 
		foreach ($components as $component) {
			if ($id == $component[0]) { 
				echo stripslashes(html_entity_decode($component[2]));
			}
		}
	}
}



/********************************************************************
* END OF PLUGIN
********************************************************************/
?>