<?php

/*
 * Plugin Name: Competition Form
 * Plugin URI: 
 * Description: Create a simple competition entry form combined with Contact Form 7
 * Version:  1.1
 * Author: RaiserWeb
 * Author URI: http://www.raiserweb.com
 * Developer: RaiserWeb
 * Developer URI: http://www.raiserweb.com
 * Text Domain: raiserweb
 * License: GPLv2
 *
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License, version 2, as
 *  published by the Free Software Foundation.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
 
 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// install table on activation
register_activation_hook( __FILE__, 'competition_table_install' ); 
 
 
// include plugin files
include( 'functions.php' );
include( 'class/competition_class.php' );
include( 'class/table_classes.php' );
/* required plugin */
include( 'required_plugin/required-plugins.php' );



if( is_admin() ) { 
	
	// js and styles
	add_action('admin_enqueue_scripts', 'enqueue_date_picker_comp');
	function enqueue_date_picker_comp(){
		wp_enqueue_script(	'field-date-js', 'ft.js', array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'), time(), true );
		wp_enqueue_style('jquery-ui-datepicker');
	}

	// admin pages and help
	add_action( 'admin_menu', 'competition_form_admin_menu' );	
	function competition_form_admin_menu() {
	
		$comp_pages['competitions'] = add_menu_page( 'Competitions', 'Competitions', 'manage_options', 'competitions', 'competition_competitions', '', 50 );
		$comp_pages['add_new'] = add_submenu_page( 'competitions', 'Add New', 'Add New', 'manage_options', 'add_new', 'competition_add_new' );
		$comp_pages['entries'] = add_submenu_page( 'competitions', 'Entries', 'Entries', 'manage_options', 'entries', 'competition_entries' );
		$comp_pages['winners'] = add_submenu_page( 'competitions', 'Winners', 'Winners', 'manage_options', 'winners', 'competition_winners' );
		$comp_pages['select_winner'] = add_submenu_page( '', 'Select Winner', 'Select Winner', 'manage_options', 'select_winner', 'competition_select_winner' );		
		
		// add help
		foreach( $comp_pages as $comp_page ) {
		
			add_action('load-'.$comp_page, 'comp_pages_add_help_tab');	
		
		}
		
	}

	// admin screens
	function competition_add_new() {		
		include( 'admin-pages/competition_add_new.php' );
	}

	function competition_select_winner() {		
		include( 'admin-pages/competition_select_winner.php' );
	}
	
	function competition_entries() {		
		include( 'admin-pages/competition_entries.php' );
	}
	
	function competition_winners() {		
		include( 'admin-pages/competition_winners.php' );
	}	
	
	function competition_competitions() {
		include( 'admin-pages/competition_competitions.php' );
	}
	

	
}


