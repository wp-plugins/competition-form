<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
	
	

   $competition_entries_table = new competition_entries_table();
   $competition_entries_table->prepare_items( 30 );		
	   



?>
	<div class="wrap">

		<h2>Entries</h2>
		
		<hr/>	
		
		<h4>Select Competition</h4>
		
		<select id="comp_select">
			<?php echo $competition_entries_table->competition_entry_options; ?>
		</select>
		
		<script>
		jQuery(document).ready( function() {
			// current comp
			var current = '<?php echo $competition_entries_table->competition_id;?>';
			jQuery('#comp_select option').each( function() {				
				if( jQuery(this).val() == current ) {
					jQuery('#comp_select').val( current );
				}
			});
			
			
			jQuery('#comp_select').change( function() {
				var comp = jQuery(this).val();
				
				if( window.location.href.toLowerCase().indexOf("competition_id=") >= 0 ) {
					var url = window.location.href.replace("competition_id="+current, "competition_id="+comp);		
				} else {
					var url = window.location.href+"&competition_id="+comp;
				}
				
				window.location.href=url;

			});
		});
		</script>
		
		<a href="<?php echo admin_url( "admin-post.php?action=export_competition_entries&competition_id={$competition_entries_table->competition_id}&paged={$competition_entries_table->current_page}" );?>" class="button button-primary right">Export All Entries to csv</a>

		<?php $competition_entries_table->display();?>
	
	</div>
