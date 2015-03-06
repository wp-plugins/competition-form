<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

	$competition = new competition_form_plugin();

?>
	<div class="wrap">
	
		<h2>Add New Competition</h2>

		<hr/>	
		
		<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" > 

			<div class="form-field form-required">
				<label for="comp_form_name">Competition Name</label><br/>
				<input id="comp_form_name" type="text" size="40" style="width:320px;" width="350px" value="" name="comp_form_name"></input>
			</div>	

			<br/>
			
			<div class="form-field form-required">
				<label for="comp_form_contact_form">Contact Form 7 Select</label><br/>
				<select name="comp_form_contact_form">
					<?php echo $competition->contact7_form_select_options();?>
				</select>
			</div>
			
			<p class="submit">
				<input id="submit" class="button button-primary" type="submit" value="Add" name="submit"></input>
			</p>
			
			<input type="hidden" name="action" value="add_competition">
				
		</form>
		
	</div>