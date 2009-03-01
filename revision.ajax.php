<?php

define('DOING_AJAX', true);

$root = dirname(dirname(dirname(dirname(__FILE__))));
if (!file_exists($root.'/wp-load.php')) exit;
		
require_once($root.'/wp-load.php');

if (!is_user_logged_in()) exit;
if(!is_numeric($_GET['id'])) exit;


$revision_delete = $wpdb->get_var("SELECT meta_value FROM $wpdb->postmeta WHERE meta_key='_revision_delete' LIMIT 1");
$rd_ex = explode(';', $revision_delete);

$rd_date =  date('Y-m-d H:i:s', mktime(date("H"), date("i"), date("s"), date("m"), date("d")-$rd_ex[0], date("Y")));

$all_posts = $wpdb->get_results("SELECT p.id, p.post_parent FROM $wpdb->posts p 
								 INNER JOIN $wpdb->posts pp ON pp.id = p.post_parent AND pp.post_status = 'publish' 
								 WHERE p.post_modified <= '$rd_date' 
								 ORDER BY p.post_parent, p.post_modified");

foreach($all_posts as $revision) {
	if($revision->post_parent == $post->ID) continue;
	
	if($last_parent != $revision->post_parent && $rd_ex[1] == 1){
		$last_parent = $revision->post_parent;
		continue;
	}
	$wpdb->query("DELETE FROM $wpdb->posts WHERE id = $revision->id AND post_type = 'revision'");

	$last_parent = $revision->post_parent;
}
$wpdb->query("OPTIMIZE TABLE $wpdb->posts");

?>