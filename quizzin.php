<?php
/*
Plugin Name: Quizzin
Plugin URI: http://www.bin-co.com/tools/wordpress/plugins/quizzin/
Description: Quizzin lets you add quizzes to your blog. This plugin is designed to be as easy to use as possible. Quizzes, questions and answers can be added from the admin side. This will appear in your post if you add a small HTML snippet in your post.
Version: 1.01.0
Author: Binny V A
Author URI: http://www.binnyva.com/
*/
require_once('wpframe.php');

/**
 * Add a new menu under Manage, visible for all users with template viewing level.
 */
add_action( 'admin_menu', 'quizzin_add_menu_links' );
function quizzin_add_menu_links() {
	global $wp_version;
	$view_level= 2;
	$page = 'edit.php';
	if($wp_version >= '2.7') $page = 'tools.php';
	
	add_submenu_page($page, t('Manage Quiz'), t('Manage Quiz'), $view_level, 'quizzin/quiz.php');
}

/// Initialize this plugin. Called by 'init' hook.
add_action('init', 'quizzin_init');
function quizzin_init() {
	load_plugin_textdomain('quizzin', 'wp-content/plugins' );
}

/// Add an option page for Quizzin
add_action('admin_menu', 'quizzin_option_page');
function quizzin_option_page() {
	add_options_page(t('Quizzin Settings'), t('Quizzin Settings'), 8, basename(__FILE__), 'quizzin_options');
}
function quizzin_options() {
	if ( function_exists('current_user_can') && !current_user_can('manage_options') ) die(t('Cheatin&#8217; uh?'));
	if (! user_can_access_admin_page()) wp_die( t('You do not have sufficient permissions to access this page.') );

	require(ABSPATH. '/wp-content/plugins/quizzin/options.php');
}

/**
 * This will scan all the content pages that wordpress outputs for our special code. If the code is found, it will replace the requested quiz.
 */
add_filter('the_content', 'quizzin_scan_content');
function quizzin_scan_content($body) {
	if(strpos($body, 'QUIZZIN') !== false) {
		if(preg_match('/(<!--|\[)\s*QUIZZIN\s*(\d+)\s*(\]|-->)/', $body, $matches)) {
			$quiz_id = $matches[2];
			
			if(is_numeric($quiz_id)) { // Basic validiation - more on the show_quiz.php file.
				ob_start();
				include(ABSPATH . 'wp-content/plugins/quizzin/show_quiz.php');
				$contents = ob_get_contents();
				ob_end_clean();
		
				$body = str_replace($matches[0], $contents, $body);
			}
		}
	}
	return $body;
}

add_action('activate_quizzin/quizzin.php','quizzin_activate');
function quizzin_activate() {
	global $wpdb;
	
	$database_version = '3';
	$installed_db = get_option('quizzin_db_version');
	// Initial options.
	add_option('quizzin_show_answers', 1);
	add_option('quizzin_single_page', 0);
	
	if($database_version != $installed_db) {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
		$sql = "CREATE TABLE wp_quiz_answer (
					ID int(11) unsigned NOT NULL auto_increment,
					question_id int(11) unsigned NOT NULL,
					answer varchar(255) NOT NULL,
					correct enum('0','1') NOT NULL default '0',
					sort_order int(3) NOT NULL default 0,
					PRIMARY KEY  (ID)
				);
				CREATE TABLE wp_quiz_question (
					ID int(11) unsigned NOT NULL auto_increment,
					quiz_id int(11) unsigned NOT NULL,
					question mediumtext NOT NULL,
					sort_order int(3) NOT NULL default 0,
					explanation mediumtext NOT NULL,
					PRIMARY KEY  (ID),
					KEY quiz_id (quiz_id)
				);
				CREATE TABLE wp_quiz_quiz (
					ID int(11) unsigned NOT NULL auto_increment,
					name varchar(50) NOT NULL,
					description mediumtext NOT NULL,
					final_screen mediumtext NOT NULL,
					added_on datetime NOT NULL,
					PRIMARY KEY  (ID)
				);";
		dbDelta($sql);
		update_option( "quizzin_db_version", $database_version );
	}
}
