<?php
/*
Plugin Name: revision delete
Plugin URI: http://www.zauberpage.de/wordpress-plugin-revision-delete-english.html
Description: delete automatic revisions from publish posts
Version: 0.1
Author: Maik Schindler
Author URI: http://www.zauberpage.de
*/

add_action('plugins_loaded', 'msRevisionDeleteInstall');
add_action('admin_menu', 'msRevisionDeleteAdminMenu');



function msRevisionDeleteInstall()
{
	global $wpdb;
	
	if($_REQUEST['activate'] == true){
		$checkedTable = $wpdb->get_var("SELECT meta_value FROM $wpdb->postmeta WHERE meta_key='_revision_delete' LIMIT 1");
		if(!$checkedTable)
			$wpdb->query("INSERT INTO $wpdb->postmeta SET meta_key = '_revision_delete', meta_value = '7;0'");
	}elseif($_REQUEST['action'] == 'deactivate' && $_REQUEST['plugin'] == 'revision-delete/revision.config.php'){
		$wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key = '_revision_delete'");
	}
}



function msRevisionDeleteAdminMenu()
{
	if (function_exists('add_options_page')) {
		add_options_page(__('revision delete!'), __('revision delete!'), 1, __FILE__, 'msRevisionDeleteConfiguration');
		add_action('edit_form_advanced', 'msRevisionDeleteAjax', 15);
	}
}


function msRevisionDeleteConfiguration()
{
	global $wpdb;
	
	if($_REQUEST['submit'] ==  'Update revision delete!' && $_REQUEST['page'] == 'revision-delete/revision.config.php'){
		
		if(is_numeric($_REQUEST['day'])) $rd_day = $_REQUEST['day']; else $rd_day = 7;
		if($_REQUEST['last'] == 1) $rd_last = 1; else $rd_last = 0;
		
		$wpdb->query("UPDATE $wpdb->postmeta SET meta_value = '".mysql_real_escape_string($rd_day.';'.$rd_last)."' WHERE meta_key = '_revision_delete'");	
		
	}
	
	$revision_delete = $wpdb->get_var("SELECT meta_value FROM $wpdb->postmeta WHERE meta_key='_revision_delete' LIMIT 1");
	$rd_ex = explode(';', $revision_delete);
	
	$string = '
	<div class="wrap">
		<h2>revision delete!</h2>
		<b>this plugin delete automatic old revisions from published posts</b><br />
		Plugin address from <a href="http://www.zauberpage.de/wordpress-plugin-revision-delete-english.html">revision delete!</a><br />
		Homepage form author <a href="http://www.zauberpage.de">www.zauberpage.de</a><br /><br /><br />
	</div>
	<form method="post" action="">	
		<div class="wrap">
			<h2>Configuration revision delete!</h2>
				Remove old revision from published posts than older <input type="text" name="day" value="'.$rd_ex[0].'" size="2" /> day(s)<br />
				<input type="checkbox" name="last" value="1" '.($rd_ex[1] == 1 ? 'checked="checked"' : '' ).' /> but not deleted last revision!<br />
				<br />
				<div class="wrap submit">
					<input name="submit" value="Update revision delete!" type="submit">
					<input name="reset" value="Reset" type="reset">
				</div>
		</div>
		<div class="wrap">
			<h2>Uninstall revision delete!</h2>
			You must only remove this plugin in the plugin page.<br />
			When you remove this plugin delete all configuration settings in your databases.<br />
			<b>But</b> u can not restore old revisions.
		</div>
	</form>';
	
	echo $string;
}


function msRevisionDeleteAjax()
{
	global $post;	
	
	if($post->ID) $id = $post->ID; else $id = 0;
	
	echo '<script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery.get("'.get_option('siteurl').'/wp-content/plugins/revision-delete/revision.ajax.php?id='.$id.'");
			});
		</script>';

}
?>