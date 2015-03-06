<?php

add_action( 'admin_post_select_winner', 'prefix_admin_select_winner' );
function prefix_admin_select_winner() {
    // Handle request	
	global $wpdb; 
	
	$query_data = array();

	$query_data['competition_id'] = $_POST['competition_id'];
	
	// dates filter
	$date_where = '';	
	if( $_POST['date_from'] != '' ) {	
	
		$date_where .= " AND {$wpdb->prefix}competition_entries.date >= %s ";
		
		$query_data['date_from'] = date_format( date_create( $_POST['date_from'] ), 'Y-m-d 00:00:00');
	
	} 
	if( $_POST['date_to'] != '' ) {
		
		$date_where .= " AND {$wpdb->prefix}competition_entries.date <= %s ";
		
		$query_data['date_to'] = date_format( date_create( $_POST['date_to'] ), 'Y-m-d 23:59:59');
		
	}
	
	// criteria filter
	if( $_POST['meta_value'] != '' ) {
		
		if( $_POST['operator'] == "equal" ) {
		
			$where = " AND {$wpdb->prefix}competition_entries_meta.meta_key = %s AND {$wpdb->prefix}competition_entries_meta.meta_value = %s ";
		
		} else {
		
			$where = " AND {$wpdb->prefix}competition_entries_meta.meta_key = %s AND {$wpdb->prefix}competition_entries_meta.meta_value != %s ";

		}
		
		$query_data['meta_key'] = $_POST['meta_key'];
		$query_data['meta_value'] = $_POST['meta_value'];
	
	} else {
	
		$where = '';
		
	}
	
	// query
	$sql = "SELECT
			{$wpdb->prefix}competition_entries_meta.entry_id
			FROM
			{$wpdb->prefix}competition_entries
			INNER JOIN {$wpdb->prefix}competition_entries_meta ON {$wpdb->prefix}competition_entries.id = {$wpdb->prefix}competition_entries_meta.entry_id
			WHERE
			{$wpdb->prefix}competition_entries.comp_id = %d
			AND
			wp_competition_entries.winner is null
			{$date_where}
			{$where}			
			ORDER BY RAND()
			LIMIT 1
			";
	$sql = 	$wpdb->prepare( $sql, $query_data );				
	$rows = $wpdb->get_results( $sql , 'ARRAY_A' );

	// save winner
	$wpdb->update( 
		"{$wpdb->prefix}competition_entries",
		array( 'winner' => 1, 'winner_date' => date('Y-m-d H:i:s') ),
		array( 'id' => $rows[0]['entry_id'] )
	);

	wp_safe_redirect( admin_url( "admin.php?page=winners&competition_id={$query_data['competition_id']}" ) );
	
}

add_action( 'admin_post_add_competition', 'prefix_admin_add_competition' );
function prefix_admin_add_competition() {
    	
	$comp = array();
	
	$comp['post_name'] = wp_strip_all_tags( $_POST['comp_form_name'] );
	$comp['post_content'] = $_POST['comp_form_contact_form'];
	$comp['post_type'] = 'competition_form';
	$comp['post_status'] = 'private';
	
	if( $comp['post_content'] != '' ) {
	
		wp_insert_post( $comp );	
		
	}
	
	wp_safe_redirect( admin_url( 'admin.php?page=competitions' ) );
	
}

add_action( 'admin_post_delete_competition_entry', 'prefix_admin_delete_competition_entry' );
function prefix_admin_delete_competition_entry() {
   
	global $wpdb; 		
	
	$entry_id = $_GET['entry_id'];	
	
	$wpdb->delete( "{$wpdb->prefix}competition_entries", array( 'id'=>$entry_id ) );
	$wpdb->delete( "{$wpdb->prefix}competition_entries_meta", array( 'entry_id'=>$entry_id ) );	
	
	$paged = sanitize_text_field( $_GET['paged'] );
	$competition_id = sanitize_text_field( $_GET['competition_id'] );
	
	wp_safe_redirect( admin_url( "admin.php?page=entries&competition_id={$competition_id}&paged={$paged}" ) );
	
}

add_action( 'admin_post_delete_competition', 'prefix_admin_delete_competition' );
function prefix_admin_delete_competition() {
   
	global $wpdb; 
	
	$query_data['competition_id'] = $_GET['competition_id'];
	
	$sql = "DELETE
		{$wpdb->prefix}competition_entries, {$wpdb->prefix}competition_entries_meta
		FROM
		{$wpdb->prefix}competition_entries
		LEFT JOIN {$wpdb->prefix}competition_entries_meta ON {$wpdb->prefix}competition_entries.id = {$wpdb->prefix}competition_entries_meta.entry_id
		WHERE
		{$wpdb->prefix}competition_entries.comp_id = %d	
	";
	$sql = $wpdb->prepare( $sql, $query_data );
	$wpdb->query( $sql );	
	
	wp_delete_post( $query_data['competition_id'], true );	
	
	wp_safe_redirect( admin_url( "admin.php?page=competitions" ) );
	
}

add_action( 'admin_post_renounce_competition_winner', 'prefix_admin_renounce_competition_winner' );
function prefix_admin_renounce_competition_winner() {
   
	global $wpdb; 
	
	$wpdb->update( 
		"{$wpdb->prefix}competition_entries",
		array( 'winner' => 'NULL', 'winner_date' => 'NULL' ),
		array( 'id' => $_GET['entry_id'] )
	);	
	
	$competition_id = sanitize_text_field( $_GET['competition_id'] );
	
	wp_safe_redirect( admin_url( "admin.php?page=winners&competition_id={$competition_id}" ) );
	
}

add_action( 'admin_post_export_competition_entries', 'prefix_admin_export_competition_entries' );
function prefix_admin_export_competition_entries() {	
	
	competition_csv_export();
	
}

function competition_csv_export() { 

    $competition_entries = new competition_entries_table();
    $competition_entries->prepare_items( 9999999 );	

    $result = array();
	
	$result = $competition_entries->table_data();
	
	// remove delete
	
	foreach( $result as $key=>$val ) {
		unset( $result[$key]['delete'] );
	}

	reset( $result );
	
	$compname = str_replace( array( "/", '\\' ), "", $result[ key($result) ]['comp'] );
	
    $filename = "Entries-{$compname}.csv";

    // Tells the browser to expect a CSV file and bring up the
    // save dialog in the browser
    header( 'Content-Type: text/csv; charset=utf-8' );
    header( 'Content-Disposition: attachment;filename='.$filename);

    $fp = fopen('php://output', 'w');

    // Get the first record
    $hrow = $result[ key($result) ];
	ksort($hrow);
    // Extracts the keys of the first record and writes them
    // to the output buffer in CSV format
    fputcsv($fp, array_keys($hrow));

    // Then, write every record 
    foreach ($result as $data) {
		ksort($data);
        fputcsv($fp, $data);
    }

    // Close the output buffer
    fclose($fp);
	
}

// custom post type
add_action( 'init', 'create_post_type_competition_form' );
function create_post_type_competition_form() {
  register_post_type( 'competition_form',
    array(
      'labels' => array(
        'name' => __( 'Competition Forms', 'competition-form' ),
        'singular_name' => __( 'Competition Forms', 'competition-form' )
      ),
	  'rewrite' => false,
    )
  );
}


/* comp entry hook */
add_action('wpcf7_before_send_mail', 'competition_form_entry' );
function competition_form_entry() {
   
	global $wpdb;   
   
    $submission = WPCF7_Submission::get_instance();	
   
    $posted_data = $submission->get_posted_data();
		
	// check if form id is set up as comp	
	$sql = " SELECT ID FROM {$wpdb->prefix}posts WHERE post_content= %d AND post_type='competition_form' ORDER BY post_date DESC LIMIT 1 ";
	$sql = $wpdb->prepare( $sql , $posted_data['_wpcf7'] );
	$comp_id = $wpdb->get_var( $sql );
	
	if( $comp_id != '' ) {
		
		// save comp entry to comp tables
		
		// first check email (if one exists) that not already entered for this comp_id
		
		// get name of email field by looking for email short code in content of contact form
		$form = get_post( $posted_data['_wpcf7'] );		
		preg_match_all("/\[email([^\]]*)\]/", $form->post_content, $shortcodes);
		$email_keys = explode( " ", $shortcodes[1][0] );	
		$email_key = $email_keys[1];
		
		if( $email_key != '' ) {
		
			$sql = "SELECT
				{$wpdb->prefix}competition_entries.comp_id,
				{$wpdb->prefix}competition_entries_meta.meta_value
				FROM
				{$wpdb->prefix}competition_entries_meta
				INNER JOIN {$wpdb->prefix}competition_entries ON {$wpdb->prefix}competition_entries_meta.entry_id = {$wpdb->prefix}competition_entries.id
				WHERE 
				{$wpdb->prefix}competition_entries_meta.meta_value = %s	
				AND 
				{$wpdb->prefix}competition_entries.comp_id = %d
			";		
			$sql = $wpdb->prepare( $sql , array( $posted_data[ $email_key ], $comp_id ) );
			$test = $wpdb->get_row( $sql );
			
			if( $test->meta_value == '' ) {
			
				// not already entered				
				save_competition_entry( $posted_data, $comp_id );				
				
			}
			
		} else {
		
			// no email to look up unique value so save entry anyway
			save_competition_entry( $posted_data, $comp_id );		
		
		}
		
	}
	
}

function save_competition_entry( $posted_data, $comp_id ) {
	
	global $wpdb;
	
	$wpdb->insert( 
				"{$wpdb->prefix}competition_entries", 
				array(
					'date' => date( 'Y-m-d H:i:s' ),
					'ip' => $_SERVER['REMOTE_ADDR'],
					'comp_id' => $comp_id,
				)
	);
	
	// get entry_id
	$entry_id = $wpdb->insert_id;
	
	// save posted data
	foreach( $posted_data as $posted_key => $posted_value ) {
	
		if( substr( $posted_key , 0, 1) != '_' ) {
			
			if( is_array( $posted_value ) ) {
				
				$posted_value = $posted_value[0];
			
			}	

			$wpdb->insert( 
						"{$wpdb->prefix}competition_entries_meta", 
						array(
							'meta_key' => $posted_key,
							'meta_value' => $posted_value,
							'entry_id' => $entry_id,
						)
			);			
			
		}
	
	}	
	
	
}

// help text
function comp_pages_add_help_tab( ) {
	
	$help_text = array();
	
	$help_text['toplevel_page_competitions'] = "<p>This screen let's you view all competitions that are set up. Competition entry is collected via a Contact Form 7 form on your website</p>
								<p>On this screen you can:</p>								
								<p><strong>Add New</strong> - You can set up a new competition. You need to ensure that you have already set up the form in the Contact Form 7 plugin</p>
								<p><strong>Delete</strong> - Deletes a competition (note that all entries will also be deleted).</p>
								<p>You can also see how many entries, and winners there are</p>
									";
	$help_text['competitions_page_add_new'] = "<p>This screen let's you set up a new Competition. To do this, you need to have a Contact From 7 form set up to use to collect entries</p>
							<p>Enter a Competition name, and choose the relevant Contact Form 7, then click add</p>	
								";
	$help_text['competitions_page_entries'] = "<p>This screen lists all of your entries to your competitions.</p>
							<p>On this screen you can:</p>
							<p>Use the select box to view different competitions</p>
							<p>Export all entries to a csv document</p>
							<p><strong>Delete</strong> - Deletes an entry</p>
							";
	$help_text['competitions_page_winners'] = "<p>This screen lists competition winners</p>
							<p>It also has a button to take you to the Pick a Winner screen</p>
							<p>Each competition can have multiple winners</p>
							<p><strong>Renounce Winner</strong> - Click this to revert the winning entry from a winner to a non-winner. It will not delete the entry</p>
							";
	$help_text['admin_page_select_winner'] = "<p>This screen lets you pick a winner at random</p>
									<p>Use the criteria options to add a criteria based on data collected via the competition form, or leave blank if there is no criteria</p>
									<p>Use the date pickers to select dates that entries to the competition are valid, or leave blank if dates aren't required.</p>
							";
			
	$screen = get_current_screen();

	if( isset( $help_text[ $screen->base ] ) ) {
	
		$screen->add_help_tab( array( 
		   'id' => 'comp_help',            
		   'title' => 'Overview',      //unique visible title for the tab
		   'content' => $help_text[ $screen->base ],  //actual help text
		) );			
		
	}
	
	
}


// create table function
function competition_table_install () {

	global $wpdb;
	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	$charset_collate = $wpdb->get_charset_collate();
	
	$table_name = $wpdb->prefix . "competition_entries"; 
	$sql = "CREATE TABLE {$table_name} (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `ip` text,
	  `comp_id` int(11) NOT NULL,
	  `date` datetime DEFAULT NULL,
	  `winner` int(1) NULL,
	  `winner_date` datetime DEFAULT NULL,
	  PRIMARY KEY (`id`)
	) {$charset_collate};";	
	dbDelta( $sql ); 
	
	$table_name = $wpdb->prefix . "competition_entries_meta"; 
	$sql = "CREATE TABLE {$table_name} (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `meta_key` text NOT NULL,
	  `meta_value` varchar(255) DEFAULT NULL,
	  `entry_id` int(11) NOT NULL,
	  PRIMARY KEY (`id`)
	) {$charset_collate};";	
	dbDelta( $sql ); 		
   
}
