<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


    $competition_competitions_table = new competition_competitions_table();
    $competition_competitions_table->prepare_items();		
		

?>

	<div class="wrap">
	
		<h2>Competitions
		
		<a class="add-new-h2" href="<?php echo admin_url( 'admin.php?page=add_new' ); ?>"  >Add New</a>
		
		</h2>
		
		<hr/>			

		<?php $competition_competitions_table->display();?>

	</div>
	
	<script>
	function competition_delete( comp_id ) {
		
		if (confirm('Are you sure? Deleteing this competition will delete all entries')) {
		
			// delete comp
			window.location.href = '<?php echo admin_url( "admin-post.php?action=delete_competition" );?>&competition_id='+comp_id;
			
		} 
		
	}
	</script>