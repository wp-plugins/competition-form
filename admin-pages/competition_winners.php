<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
	
	

   $competition_winners_table = new competition_winners_table();
   $competition_winners_table->prepare_items( );	

?>

<div class="wrap">
	
	<h2>Competition Winners</h2>
	
	<hr/>
	
	<select id="comp_select">
		<?php echo $competition_winners_table->competition_entry_options; ?>
	</select>	
	
	<a href="<?php echo admin_url( "admin.php?page=select_winner&competition_id={$competition_winners_table->competition_id}" ); ?>" class="button button-primary" >Select Winner</a>
	
		<script>
		jQuery(document).ready( function() {
		
			// current comp
			var current = '<?php echo $competition_winners_table->competition_id;?>';
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
		
		<a href="<?php echo admin_url( "admin-post.php?action=export_competition_entries_winners&competition_id={$competition_entries_table->competition_id}&paged={$competition_entries_table->current_page}" );?>" class="button button-primary right">Export All Winners to csv</a>
		
		<?php $competition_winners_table->display();?>
		
	
	

</div>