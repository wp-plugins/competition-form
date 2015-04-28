<?php


	$competition = new competition_form_plugin();
	

?>

<div class="wrap">

	<h2>Select a Winner</h2>
	
	<hr/>
	
	<h3>Competition Name: <?php echo $competition->competition_name;?></h3>

	<h4>Criteria</h4>
	
	<p>Leave blank if there is no criteria for the winner. Or use the fields to add criteria</p>
	
	<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" >
	
		<select name="meta_key">
			<?php echo $competition->competition_meta_keys_options();?>
		</select>
		
		<select name="operator" >
			<option value="equal">Equals</option>
			<option value="not_euqal">Does Not Equal</option>
		</select>
		
		<input style="width:300px;" type="text" placeholder="Enter the criteria for this field" name="meta_value" >
		
		<br/>
		<br/>
		
		<h4>Entry Dates</h4>
		<p>Leave blank if there are no date conditions. Or select dates to pick a winner who entered between the dates.
		<p>Start: </p>
		<input type="text" name="date_from" class="datepicker">
		<p>End: </p>
		<input type="text" name="date_to" class="datepicker">

		
		<p class="submit">
			<input id="submit" class="button button-primary" type="submit" value="Pick Me a Winner at Random" name="submit"></input>
		</p>		
		
		<input type="hidden" name="action" value="select_winner">
		
		<input type="hidden" name="competition_id" value="<?php echo $competition->competition_id;?>">
		
	</form>
	
</div>


<script>
jQuery(document).ready( function() {			
	// date picker
	jQuery(document).ready(function() {
		jQuery('.datepicker').datepicker();
	});		
});	
</script>