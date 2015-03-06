<?php

class competition_form_plugin {
	
	
    function __construct() {

		$this->competition_id = $this->competition_id();
		$this->competition_name = $this->competition_name();		
   
    }	
	
	public function competition_name() {
	
		$comp = get_post( $this->competition_id );
		
		if( $comp ) {
		
			return $comp->post_name;
			
		} else {
		
			return '';
			
		}
	
	}

	public function get_competition_meta_keys() {
	
	   global $wpdb;
		
		$sql = "SELECT
				{$wpdb->prefix}competition_entries_meta.meta_key
				FROM
				{$wpdb->prefix}competition_entries
				INNER JOIN {$wpdb->prefix}competition_entries_meta ON {$wpdb->prefix}competition_entries.id = {$wpdb->prefix}competition_entries_meta.entry_id
				WHERE {$wpdb->prefix}competition_entries.comp_id = %s
				GROUP BY meta_key
				";
		$sql = 	$wpdb->prepare( $sql, $this->competition_id );
		$cols = $wpdb->get_results( $sql , 'ARRAY_A' );	
		
		return $cols;	
	
	}

	
	public function competition_id(){
	
		if( isset( $_GET['competition_id'] ) && $_GET['competition_id'] != '' ) {
			$competition_id = $_GET['competition_id'];
		} else {
			$competition_id = 'None';
		}
		
		if( $competition_id == 'None' ) {
					
			// get a comp ID
			$args = array(
				'numberposts' => 1,
				'orderby' => 'post_date',
				'order' => 'DESC',
				'post_type' => 'competition_form',
				);			
			$comp = wp_get_recent_posts( $args );	
			
			if( $comp ) {
				$competition_id = $comp[0]['ID'];
			} else {
				$competition_id = false;
			}
		
		}	
		
		return $competition_id;
	}

	public function total_items() {
	
		global $wpdb; 	
		
		$data_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$wpdb->prefix}competition_entries WHERE comp_id = %s " , $this->competition_id ) );
		
		return $data_count;
		
	}	
	
	public function competition_meta_keys_options() {
		
		$keys = $this->get_competition_meta_keys( );
		
		$options = '';
		foreach( $keys as $key ) {
			$options.= "<option value='{$key['meta_key']}'>{$key['meta_key']}</option>";
		}
		
		return $options;		
	
	}
	
	public function contact7_form_select_options() {
		
		// show forms that are not already assigned to a competition
		
		// get ids of contact forms being used already
		$args = array(
			'post_type' => 'competition_form',
			'post_status' => 'any',
			'posts_per_page' => -1,
			'offset' => 0,
			'orderby' => 'ID',
			'order' => 'ASC' 
		);

		$q = new WP_Query();
		$posts = $q->query( $args );

		$not_in = array();
		
		if( $posts ) {

			foreach ( (array)$posts as $post ) {
				$not_in[] = $post->post_content;
			}
			
		} 

		unset( $args );
		
		$args = array(
			'post_type' => 'wpcf7_contact_form',
			'post_status' => 'any',
			'posts_per_page' => -1,
			'post__not_in' =>$not_in,
			'offset' => 0,
			'orderby' => 'ID',
			'order' => 'ASC' 
		);

		$q = new WP_Query();
		$posts = $q->query( $args );

		$options = '<option value="">Select...</option>';
		
		if( $posts ) {

			foreach ( (array)$posts as $post ) {
				$options .= "<option value='{$post->ID}' >Name: \"{$post->post_name}\" ID:{$post->ID}</option>";
			}
			
		} else {
		
			$options = "<option value=''>No Available Contact Forms. Please set one up using Contact Form 7 plugin</option>";
		
		}
		
		return $options;
		
	}	
	
	public function competition_entry_options(){
	
		$args = array(
			'post_type' => 'competition_form',
			'post_status' => 'any',
			'posts_per_page' => -1,
			'offset' => 0,
			'orderby' => 'ID',
			'order' => 'ASC' 
		);

		$q = new WP_Query();
		$posts = $q->query( $args );

		$options = '';
		
		if( $posts ) {

			foreach ( (array)$posts as $post ) {
				$options .= "<option value='{$post->ID}' >{$post->post_name}</option>";
			}
			
		} else {
		
			$options = "<option value=''>No Competitions set up. Please set one up first using Contact Form 7 plugin</option>";
		
		}
		
		return $options;
	
	}


}

