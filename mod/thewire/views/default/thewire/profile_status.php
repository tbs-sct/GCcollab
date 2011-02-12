<?php
/**
 * Latest wire post on profile page
 */
 
$owner = $vars['entity']->guid;
$url_to_wire = elgg_get_site_url() . "pg/thewire/owner/" . $vars['entity']->username;

//grab the user's latest from the wire
$params = array(
	'types' => 'object',
	'subtypes' => 'thewire',
	'owner_guid' => $owner,
	'limit' => 1,
);
$latest_wire = elgg_get_entities($params);

if ($latest_wire && count($latest_wire) > 0) {
	$latest_wire = $latest_wire[0];
	$content = thewire_filter($latest_wire->description);
	$time = "<p class='entity-subtext'> (" . elgg_view_friendly_time($latest_wire->time_created) . ")</p>";

	echo "<div class='wire_post'><div class='wire_post_contents clearfix radius8'>";
	echo $content;
	if ($owner == elgg_get_logged_in_user_guid()) {
		$text = elgg_echo('thewire:update');
		echo "<a class='elgg-button-action update small' href=\"{$url_to_wire}\">$text</a>";
	}
	echo $time;
	echo "</div></div>";
}
