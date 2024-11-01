<?php
/*
Plugin Name: Redirect External Links(Back Links)
Plugin URI:  http://www.lemosys.com/
Description: Plugin will track all the back link from the sites, you can redirect as per your need.
Version: 2.2
Author: Kanhaiya
Author URI: http://www.lemosys.com/
Text Domain: kaddy_backlink
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires PHP: 5.7
*/
/*
Redirect External Links(Back Links) is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Redirect External Links(Back Links) is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Redirect External Links(Back Links). If not, see Redirect External Links(Back Links).
*/

define( 'KADDY_BACKLINK_URL', plugin_dir_url(__FILE__) );
define( 'KADDY_BACKLINK_PATH', plugin_dir_path(__FILE__) );
define( 'KADDY_BACKLINK_SLUG','kaddy_backlink' );
define('KADDY_TEXTDOMAIN', 'kaddy_backlins');

$plugin_dir = plugin_dir_url( __FILE__ ).'images/';

if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');
global $wpdbb_content_dir;

if(!function_exists('wp_get_current_user')){
	include(ABSPATH."wp-includes/pluggable.php") ; // Include pluggable.php for current user	
}
/*
 * Initialize Plugin settings
*/
/**
 * 
 */
if ( !class_exists( 'KaddyBackLinks' ) ) {
	class KaddyBackLinks {
		 /**
	     * BackLinks constructor.
	     *
	     * The main plugin actions registered for WordPress
	     */
	    public function __construct() {
	      //  add_action('init', array($this, 'kaddy_check_dependencies'));
	        $this->hooks();
	        $this->kaddy_include_files();
	    }

	    /**
	     * Initialize
	     */
		    public function hooks() {
				add_action('activate_plugin', array($this, 'kaddy_create_table'));
				add_action('deactivated_plugin', array($this, 'kaddy_drp_table'));
				add_action('admin_menu', array($this, 'kaddy_add_menu'));
		        add_action('wp_enqueue_scripts', array($this, 'kaddy_custom_style'));
		        add_action('wp_loaded', array($this, 'getRefer'));
		    }
	    /**
	     * @return plugin files
	    */
		    public function kaddy_include_files(){
		       require_once 'includes/kaddyfunc.php';
		    }

	    /**
	     *
	     * @return Enqueue admin panel required css/js
	     */
	      public function kaddy_custom_style(){
			wp_enqueue_style('kaddy');
			wp_enqueue_style('kaddy_custom_styles',KADDY_BACKLINK_URL.'css/kaddy_custom.css');
			wp_enqueue_style('kaddy_back_styles',KADDY_BACKLINK_URL.'css/kaddy_back_styels.css');
		  }
	      
	     /**
	     *
	     * Create required table
	     */
		public function kaddy_create_table() {
			global $wpdb;	
		    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		    $table_name = $wpdb->prefix . "track_back_link";
		    $sql = "CREATE TABLE IF NOT EXISTS ". $table_name ."(
		            id bigint(20) NOT NULL AUTO_INCREMENT,
		            refer_url text,
					redirect_url text,
		            status varchar(255), 
		            date timestamp Default CURRENT_TIMESTAMP,
					is_active tinyint(1),
		            PRIMARY KEY (id));";
		            dbDelta($sql);
		}

		/* Drop table while the deactivate table*/

		public function kaddy_drp_table() {
			global $wpdb;
		    $table_name = $wpdb->prefix . "track_back_link";
		    $wpdb->query("DROP TABLE IF EXISTS $table");
			include('uninstall.php');
		}
	    /*
	    * Get the refrence link and reditrect ,Insert Detail if not found
	    * Action wp_head
	    */
		public function getRefer(){
		    $referer="";
		    if (isset($_SERVER['HTTPS']) &&
		        ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
		        isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
		        $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
		        $protocol = 'https://';
		    } else {
		        $protocol = 'http://';
		    }
		     $referer = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			global $wpdb;
		    $table_name = $wpdb->prefix . "track_back_link";
			$refer_result=$wpdb->get_results( "SELECT * FROM $table_name where refer_url='$referer' and is_active=true" );
			
			if($refer_result){
			   echo  $redirect_url=$refer_result[0]->redirect_url;
				echo $status=$refer_result[0]->status;
				if($status=="301"){
					wp_redirect($redirect_url); exit;					
				}else if($status=="404"){
					wp_redirect($redirect_url); exit;
				}else if($status=="custom"){
					wp_redirect($redirect_url); exit;						
				}
			}else{
				if($referer !==""){
					//$wpdb->insert( $table_name, array( 'refer_url' => $referer, 'is_active' => 1 ) );
				}
			}
		 }
		/*
		  * Register menu for backlink list table
		  * Action admin_menu
		*/
	   
	   public function kaddy_add_menu(){
	   	 add_menu_page('Back Link', 'Back Link', 'manage_options', 'manage_link', 'kaddy_backlink_list',plugin_dir_url( __FILE__ ).'images/icon.png');
		 add_submenu_page('manage_link', 'Manage BackLink','Manage BackLink','manage_options','manage_link','kaddy_backlink_list');
		 add_submenu_page('manage_link', 'Add Link','Add Link','manage_options','edit_link','kaddy_edit_linkdetail');
	   } 
	}

	/*
	 * Starts our plugin class, easy!
	 */
	new KaddyBackLinks();
}