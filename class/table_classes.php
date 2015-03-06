<?php


if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class competition_entries_table extends WP_List_Table {


    public function prepare_items( $per_page )
    {
		
		$this->competition = new competition_form_plugin();
		$this->competition_id = $this->competition->competition_id();
		$this->competition_entry_options = $this->competition->competition_entry_options();
		$this->current_page = $this->get_pagenum();
		$this->per_page = $per_page;
		
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();		

        $data = $this->table_data();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;			
		
		// pagination
		$total_items = $this->competition->total_items();

		 $this->set_pagination_args( 
			array(
				'total_items' => $total_items,                  
				'per_page'    => $this->per_page,                   
			) 
		 );	
		
    }
	
	public function get_pagenum() {
		
		if( isset( $_GET['paged'] ) ) {
			return $_GET['paged'];
		} else {
			return 1;
		}
		
	}
	

	
    public function get_columns()  {
       
	   global $wpdb;
		
		$cols = $this->competition->get_competition_meta_keys( );
		
		$columns[ 'comp' ] = "Competition";
		$columns[ 'date' ] = "Entry Date";		
		
		$this->col_count = 1;
		foreach( $cols as $col ) {
			$columns[$col['meta_key']] = $col['meta_key'];
			$this->col_count++;
		}
		
		$columns[ 'delete' ] = "Delete";	
		
        return $columns;
    }	
	
    public function column_default( $item, $column_name )
    {
        return $item[ $column_name ];
    }		
	
    public function get_hidden_columns()
    {
        return array();
    }
	
    public function get_sortable_columns()
    {
        return array();
    }
	

    public function table_data()
    {
        
		global $wpdb;  	
		
		$data = array();

		$start = ( ( $this->current_page-1) * $this->per_page ) * $this->col_count;
		$end = $this->per_page * $this->col_count;
		
		$sql = "SELECT
				{$wpdb->prefix}competition_entries.comp_id,
				{$wpdb->prefix}competition_entries_meta.entry_id,
				{$wpdb->prefix}competition_entries.date,
				{$wpdb->prefix}competition_entries_meta.meta_key,
				{$wpdb->prefix}competition_entries_meta.meta_value,
				{$wpdb->prefix}posts.post_name
				FROM
				{$wpdb->prefix}competition_entries
				INNER JOIN {$wpdb->prefix}competition_entries_meta ON {$wpdb->prefix}competition_entries.id = {$wpdb->prefix}competition_entries_meta.entry_id
				INNER JOIN {$wpdb->prefix}posts ON {$wpdb->prefix}competition_entries.comp_id ={$wpdb->prefix}posts.ID
				WHERE {$wpdb->prefix}competition_entries.comp_id = %d 
				ORDER BY {$wpdb->prefix}competition_entries_meta.entry_id
				LIMIT {$start}, {$end}
				";
		$sql = 	$wpdb->prepare( $sql, $this->competition_id );
		$rows = $wpdb->get_results( $sql , 'ARRAY_A' );
		
		foreach( $rows as $row ) {
		
			$data[ $row['entry_id'] ][ $row['meta_key'] ] = $row['meta_value'];
			
			$data[ $row['entry_id'] ][ 'date' ] = date_format( date_create( $row['date'] ), 'd-m-Y H:i:s');
			
			$data[ $row['entry_id'] ][ 'comp' ] = $row['post_name'];
			
			$data[ $row['entry_id'] ][ 'delete' ] = "<a href='".admin_url( "admin-post.php?action=delete_competition_entry&entry_id={$row['entry_id']}&competition_id={$this->competition_id}&paged={$this->current_page}" )."'>Delete</a>";
		
		}
	
		return $data;	
	}
	
	
	
	public function column_id($item)
	{
		return $item['id'];
	}	
	
	
}



class competition_winners_table extends WP_List_Table {


    public function prepare_items( )
    {
		
		$this->competition = new competition_form_plugin();
		$this->competition_id = $this->competition->competition_id();
		$this->competition_entry_options = $this->competition->competition_entry_options();
		
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();		

        $data = $this->table_data();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;			
		
		
    }
	
	
	
	
    public function get_columns()  {
       
	   global $wpdb;
		
		$cols = $this->competition->get_competition_meta_keys( );
		
		$columns[ 'comp' ] = "Competition";
		$columns[ 'date' ] = "Entry Date";	
		$columns[ 'winner_date' ] = "Winner Date";
		
		$count = 1;
		foreach( $cols as $col ) {
			$columns[$col['meta_key']] = $col['meta_key'];
			$this->col_count = $count;
			$count++;
		}
		
		$columns[ 'remove' ] = "Renounce Winner";	
		
        return $columns;
    }	
	
    public function column_default( $item, $column_name )
    {


        return $item[ $column_name ];


    }		
	
    public function get_hidden_columns()
    {
        return array();
    }
	
    public function get_sortable_columns()
    {
        return array();
    }
	

    public function table_data()
    {
        
		global $wpdb;  	
		
		$data = array();

		
		$sql = "SELECT
				{$wpdb->prefix}competition_entries.comp_id,
				{$wpdb->prefix}competition_entries_meta.entry_id,
				{$wpdb->prefix}competition_entries.date,
				{$wpdb->prefix}competition_entries.winner_date,
				{$wpdb->prefix}competition_entries_meta.meta_key,
				{$wpdb->prefix}competition_entries_meta.meta_value,
				{$wpdb->prefix}posts.post_name
				FROM
				{$wpdb->prefix}competition_entries
				INNER JOIN {$wpdb->prefix}competition_entries_meta ON {$wpdb->prefix}competition_entries.id = {$wpdb->prefix}competition_entries_meta.entry_id
				INNER JOIN {$wpdb->prefix}posts ON {$wpdb->prefix}competition_entries.comp_id ={$wpdb->prefix}posts.ID
				WHERE {$wpdb->prefix}competition_entries.comp_id = %d 
				AND {$wpdb->prefix}competition_entries.winner = 1
				ORDER BY {$wpdb->prefix}competition_entries_meta.entry_id

				";
		$sql = 	$wpdb->prepare( $sql, $this->competition_id );
		$rows = $wpdb->get_results( $sql , 'ARRAY_A' );
		
		foreach( $rows as $row ) {
		
			$data[ $row['entry_id'] ][ $row['meta_key'] ] = $row['meta_value'];
			
			$data[ $row['entry_id'] ][ 'date' ] = date_format( date_create( $row['date'] ), 'd-m-Y H:i:s');
			
			$data[ $row['entry_id'] ][ 'comp' ] = $row['post_name'];
			
			$data[ $row['entry_id'] ][ 'winner_date' ] = date_format( date_create( $row['winner_date'] ), 'd-m-Y H:i:s');
			
			$data[ $row['entry_id'] ][ 'remove' ] = "<a href='".admin_url( "admin-post.php?action=renounce_competition_winner&entry_id={$row['entry_id']}&competition_id={$this->competition_id}" )."'>Renounce</a>";
			
		}
	
		return $data;	
	}
	
	
	
	public function column_id($item)
	{
		return $item['id'];
	}	
	
	
}




class competition_competitions_table extends WP_List_Table {

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data = $this->table_data();

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }
	
	
    public function get_columns()
    {
        $columns = array(
            'post_name'   => 'Competition Name',
            'post_date'   => 'Date Set Up',
			'cf7_shortcode' => 'Contact Form 7 Shortcode',
			'entry_count'=> 'Entry Count',
			'winner_count' => 'Winner Count',
			'delete' => 'Delete',

        );

        return $columns;
    }	
	
    public function column_default( $item, $column_name ) {
	
		if( $column_name == "" ) {
			return $item[ $column_name ];
		} elseif( $column_name == "" ) {
			return $item[ $column_name ];
		} else {
			return $item[ $column_name ];
		}

    }		
	
    public function get_hidden_columns()
    {
        return array();
    }
	
    public function get_sortable_columns()
    {
        return array();
    }


    private function table_data()
    {
		
		 global $wpdb;
		
		// first get contact form 7 data
		$args = array(
			'post_type' => 'wpcf7_contact_form',
			'post_status' => 'any',
			'posts_per_page' => -1,
			'offset' => 0,
			'orderby' => 'ID',
			'order' => 'ASC' 
		);

		$q = new WP_Query();
		$posts = $q->query( $args );
		
		$cf7_data = array();
		
		if( $posts ) {
			foreach ( (array)$posts as $post ) {
				$cf7_data[ $post->ID ]['shortcode'] = "[contact-form-7 id=\"{$post->ID}\" title=\"{$post->post_name}\"]";
			}		
		}
		
		unset( $args );
		
		// now get count of entrants per comp
		$sql = "
		SELECT
		Count( {$wpdb->prefix}competition_entries.id ) as count,
		{$wpdb->prefix}competition_entries.comp_id
		FROM
		{$wpdb->prefix}competition_entries
		GROUP BY 
		{$wpdb->prefix}competition_entries.comp_id				
		";
		$counts_result = $wpdb->get_results( $sql , 'ARRAY_A' );
		$entry_counts = array();
		foreach( $counts_result as $count ) {
			$entry_counts[ $count['comp_id'] ] = $count['count'];
		}

		// now get count of winners per comp
		$sql = "
		SELECT
		Count( {$wpdb->prefix}competition_entries.id ) as count,
		{$wpdb->prefix}competition_entries.comp_id
		FROM
		{$wpdb->prefix}competition_entries
		WHERE 
		{$wpdb->prefix}competition_entries.winner = 1
		GROUP BY 
		{$wpdb->prefix}competition_entries.comp_id				
		";
		$counts_result = $wpdb->get_results( $sql , 'ARRAY_A' );
		$winner_counts = array();
		foreach( $counts_result as $count ) {
			$winner_counts[ $count['comp_id'] ] = $count['count'];
		}		
	
        // now get competitions
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

		$data = array();

		foreach ( (array) $posts as $post ) {
		
			$view_entry_url = admin_url( "admin.php?page=entries&competition_id={$post->ID}" );
			$view_winner_url = admin_url( "admin.php?page=winners&competition_id={$post->ID}" );
			
			if( isset( $cf7_data[ $post->post_content ]['shortcode'] ) ) {
				$cf7_shortcode = $cf7_data[ $post->post_content ]['shortcode'];
			} else {
				$cf7_shortcode = 'Form no longer exists';
			}
			
			if( !isset( $winner_counts[ $post->ID ] ) ) {
				$winner_counts[ $post->ID ] = 0;
			} 
			if( !isset( $entry_counts[ $post->ID ] ) ) {
				$entry_counts[ $post->ID ] = 0;
			} 
			
			$data[] = array(
						'post_name'=>$post->post_name,
						'post_date'=>date_format( date_create( $post->post_date ), 'd-m-Y'),
						'cf7_shortcode'=>$cf7_shortcode,
						'entry_count'=>"<a href='{$view_entry_url}'>".$entry_counts[ $post->ID ]."</a>",
						'winner_count'=>"<a href='{$view_winner_url}'>".$winner_counts[ $post->ID ]."</a>",
						'delete'=>"<a href='#' onClick='competition_delete({$post->ID});return false;' >Delete</a>",
					);
		}
	
		return $data;	
	}
	
	
	
	public function column_id($item)
	{
		return $item['id'];
	}	
	
	

	
	
}

