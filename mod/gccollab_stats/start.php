<?php

elgg_register_event_handler('init', 'system', 'gccollab_stats_init', 0);

function gccollab_stats_init() {
	elgg_register_page_handler('stats', 'stats_page_handler');
	elgg_register_plugin_hook_handler('public_pages', 'walled_garden', 'gccollab_stats_public_page');

	elgg_ws_expose_function(
        "member.stats",
        "get_member_data",
        array(
        	"type" => array('type' => 'string', 'required' => true),
        	"lang" => array('type' => 'string', 'required' => false, 'default' => 'en')
        ),
        'Exposes member data for use with dashboard',
        'GET',
        false,
        false
	);

	elgg_ws_expose_function(
        "site.stats",
        "get_site_data",
        array(
        	"type" => array('type' => 'string', 'required' => true),
        	"lang" => array('type' => 'string', 'required' => false, 'default' => 'en')
        ),
        'Exposes site data for use with dashboard',
        'GET',
        false,
        false
	);
}

function gccollab_stats_public_page($hook, $handler, $return, $params){
	$pages = array('stats');
	return array_merge($pages, $return);
}

function stats_page_handler($page) {
	$base = elgg_get_plugins_path() . 'gccollab_stats/pages/gccollab_stats';
	require_once "$base/index.php";
	return true;
}

function get_member_data($type, $lang) {
	$data = array();
	ini_set("memory_limit", -1);
	elgg_set_ignore_access(true);

	if ($type === 'all') {
		$users = elgg_get_entities(array(
			'type' => 'user',
			'limit' => 0
		));

		if ($lang == 'fr'){
			$users_types = array('federal' => 'féderal', 'academic' => 'milieu universitaire', 'student' => 'étudiant', 'provincial' => 'provincial', 'municipal' => 'municipale', 'international' => 'international', 'ngo' => 'ngo', 'community' => 'collectivité', 'business' => 'entreprise', 'media' => 'média', 'retired' => 'retraité(e)', 'other' => 'autre');

			foreach($users as $key => $obj){
				$data[$users_types[$obj->user_type]] = isset( $data[$users_types[$obj->user_type]] ) ? $data[$users_types[$obj->user_type]] + 1 : 1;
			}
		} else {
			foreach($users as $key => $obj){
				$data[$obj->user_type] = isset( $data[$obj->user_type] ) ? $data[$obj->user_type] + 1 : 1;
			}
		}
	} else if ($type === 'federal') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'metadata_name_value_pairs' => array(
				array('name' => 'user_type', 'value' => 'federal'),
			),
			'limit' => 0
		));

		if ($lang == 'fr'){
			$deptObj = elgg_get_entities(array(
			   	'type' => 'object',
			   	'subtype' => 'federal_departments',
			));
			$depts = get_entity($deptObj[0]->guid);
			$federal_departments = json_decode($depts->federal_departments_fr, true);

			foreach($users as $key => $obj){
				$data[$federal_departments[$obj->federal]] = isset( $data[$federal_departments[$obj->federal]] ) ? $data[$federal_departments[$obj->federal]] + 1 : 1;
			}
		} else {
			foreach($users as $key => $obj){
				$data[$obj->federal] = isset( $data[$obj->federal] ) ? $data[$obj->federal] + 1 : 1;
			}
		}
	} else if ($type === 'academic') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'metadata_name_value_pairs' => array(
				array('name' => 'user_type', 'value' => 'academic'),
			),
			'limit' => 0
		));
		foreach($users as $key => $obj){
			$data[$obj->institution]['total'] = isset( $data[$obj->institution]['total'] ) ? $data[$obj->institution]['total'] + 1 : 1;
			if($obj->university){
				$data[$obj->institution][$obj->university] = isset( $data[$obj->institution][$obj->university] ) ? $data[$obj->institution][$obj->university] + 1 : 1;
			}
			if($obj->college){
				$data[$obj->institution][$obj->college] = isset( $data[$obj->institution][$obj->college] ) ? $data[$obj->institution][$obj->college] + 1 : 1;
			}
		}
	} else if ($type === 'student') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'metadata_name_value_pairs' => array(
				array('name' => 'user_type', 'value' => 'student'),
			),
			'limit' => 0
		));
		foreach($users as $key => $obj){
			$data[$obj->institution]['total'] = isset( $data[$obj->institution]['total'] ) ? $data[$obj->institution]['total'] + 1 : 1;
			if($obj->university){
				$data[$obj->institution][$obj->university] = isset( $data[$obj->institution][$obj->university] ) ? $data[$obj->institution][$obj->university] + 1 : 1;
			}
			if($obj->college){
				$data[$obj->institution][$obj->college] = isset( $data[$obj->institution][$obj->college] ) ? $data[$obj->institution][$obj->college] + 1 : 1;
			}
			if($obj->highschool){
				$data[$obj->institution][$obj->highschool] = isset( $data[$obj->institution][$obj->highschool] ) ? $data[$obj->institution][$obj->highschool] + 1 : 1;
			}
		}
	} else if ($type === 'university') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'metadata_name_value_pairs' => array(
				array('name' => 'user_type', 'value' => 'academic'),
				array('name' => 'institution', 'value' => 'university'),
			),
			'limit' => 0
		));
		foreach($users as $key => $obj){
			$data['total'] = isset( $data['total'] ) ? $data['total'] + 1 : 1;
			$data[$obj->university] = isset( $data[$obj->university] ) ? $data[$obj->university] + 1 : 1;
		}
	} else if ($type === 'college') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'metadata_name_value_pairs' => array(
				array('name' => 'user_type', 'value' => 'academic'),
				array('name' => 'institution', 'value' => 'college'),
			),
			'limit' => 0
		));
		foreach($users as $key => $obj){
			$data['total'] = isset( $data['total'] ) ? $data['total'] + 1 : 1;
			$data[$obj->college] = isset( $data[$obj->college] ) ? $data[$obj->college] + 1 : 1;
		}
	}  else if ($type === 'highschool') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'metadata_name_value_pairs' => array(
				array('name' => 'user_type', 'value' => 'student'),
				array('name' => 'institution', 'value' => 'highschool'),
			),
			'limit' => 0
		));
		foreach($users as $key => $obj){
			$data['total'] = isset( $data['total'] ) ? $data['total'] + 1 : 1;
			$data[$obj->highschool] = isset( $data[$obj->highschool] ) ? $data[$obj->highschool] + 1 : 1;
		}
	} else if ($type === 'provincial') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'metadata_name_value_pairs' => array(
				array('name' => 'user_type', 'value' => 'provincial'),
			),
			'limit' => 0
		));

		if ($lang == 'fr'){
			$provObj = elgg_get_entities(array(
			   	'type' => 'object',
			   	'subtype' => 'provinces',
			));
			$provs = get_entity($provObj[0]->guid);
			$provincial_departments = json_decode($provs->provinces_fr, true);

			$minObj = elgg_get_entities(array(
			   	'type' => 'object',
			   	'subtype' => 'ministries',
			));
			$mins = get_entity($minObj[0]->guid);
			$ministries = json_decode($mins->ministries_fr, true);

			foreach($users as $key => $obj){
				$data[$provincial_departments[$obj->provincial]]['total'] = isset( $data[$provincial_departments[$obj->provincial]]['total'] ) ? $data[$provincial_departments[$obj->provincial]]['total'] + 1 : 1;
				$data[$provincial_departments[$obj->provincial]][$ministries[$obj->provincial][$obj->ministry]] = isset( $data[$provincial_departments[$obj->provincial]][$ministries[$obj->provincial][$obj->ministry]] ) ? $data[$provincial_departments[$obj->provincial]][$ministries[$obj->provincial][$obj->ministry]] + 1 : 1;
			}
		} else {
			foreach($users as $key => $obj){
				$data[$obj->provincial]['total'] = isset( $data[$obj->provincial]['total'] ) ? $data[$obj->provincial]['total'] + 1 : 1;
				$data[$obj->provincial][$obj->ministry] = isset( $data[$obj->provincial][$obj->ministry] ) ? $data[$obj->provincial][$obj->ministry] + 1 : 1;
			}
		}
	} else if ($type === 'municipal') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'metadata_name_value_pairs' => array(
				array('name' => 'user_type', 'value' => 'municipal')
			),
			'limit' => 0
		));
		foreach($users as $key => $obj){
			$data['total'] = isset( $data['total'] ) ? $data['total'] + 1 : 1;
			$data[$obj->municipal] = isset( $data[$obj->municipal] ) ? $data[$obj->municipal] + 1 : 1;
		}
	} else if ($type === 'international') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'metadata_name_value_pairs' => array(
				array('name' => 'user_type', 'value' => 'international')
			),
			'limit' => 0
		));
		foreach($users as $key => $obj){
			$data['total'] = isset( $data['total'] ) ? $data['total'] + 1 : 1;
			$data[$obj->international] = isset( $data[$obj->international] ) ? $data[$obj->international] + 1 : 1;
		}
	} else if ($type === 'ngo') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'metadata_name_value_pairs' => array(
				array('name' => 'user_type', 'value' => 'ngo')
			),
			'limit' => 0
		));
		foreach($users as $key => $obj){
			$data['total'] = isset( $data['total'] ) ? $data['total'] + 1 : 1;
			$data[$obj->ngo] = isset( $data[$obj->ngo] ) ? $data[$obj->ngo] + 1 : 1;
		}
	} else if ($type === 'community') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'metadata_name_value_pairs' => array(
				array('name' => 'user_type', 'value' => 'community')
			),
			'limit' => 0
		));
		foreach($users as $key => $obj){
			$data['total'] = isset( $data['total'] ) ? $data['total'] + 1 : 1;
			$data[$obj->community] = isset( $data[$obj->community] ) ? $data[$obj->community] + 1 : 1;
		}
	} else if ($type === 'business') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'metadata_name_value_pairs' => array(
				array('name' => 'user_type', 'value' => 'business')
			),
			'limit' => 0
		));
		foreach($users as $key => $obj){
			$data['total'] = isset( $data['total'] ) ? $data['total'] + 1 : 1;
			$data[$obj->business] = isset( $data[$obj->business] ) ? $data[$obj->business] + 1 : 1;
		}
	} else if ($type === 'media') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'metadata_name_value_pairs' => array(
				array('name' => 'user_type', 'value' => 'media')
			),
			'limit' => 0
		));
		foreach($users as $key => $obj){
			$data['total'] = isset( $data['total'] ) ? $data['total'] + 1 : 1;
			$data[$obj->media] = isset( $data[$obj->media] ) ? $data[$obj->media] + 1 : 1;
		}
	} else if ($type === 'retired') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'metadata_name_value_pairs' => array(
				array('name' => 'user_type', 'value' => 'retired')
			),
			'limit' => 0
		));
		foreach($users as $key => $obj){
			$data['total'] = isset( $data['total'] ) ? $data['total'] + 1 : 1;
			$data[$obj->retired] = isset( $data[$obj->retired] ) ? $data[$obj->retired] + 1 : 1;
		}
	} else if ($type === 'other') {
		$users = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'metadata_name_value_pairs' => array(
				array('name' => 'user_type', 'value' => 'other')
			),
			'limit' => 0
		));
		foreach($users as $key => $obj){
			$data['total'] = isset( $data['total'] ) ? $data['total'] + 1 : 1;
			$data[$obj->other] = isset( $data[$obj->other] ) ? $data[$obj->other] + 1 : 1;
		}
	} 
    return $data;
}

function get_site_data($type, $lang) {
	$data = array();
	ini_set("memory_limit", -1);
	elgg_set_ignore_access(true);

	if ($type === 'wireposts') {
		$wireposts = elgg_get_entities(array(
			'type' => 'object',
			'subtype' => 'thewire',
			'limit' => 0
		));

		foreach($wireposts as $key => $obj){
			$user = get_user($obj->owner_guid);
			if($obj->time_created && $user instanceof ElggUser){
				$data[] = array($obj->time_created, "", "");
			}
		}
	} else if ($type === 'blogposts') {
		$blogposts = elgg_get_entities(array(
			'type' => 'object',
			'subtype' => 'blog',
			'limit' => 0
		));

		foreach($blogposts as $key => $obj){
			$user = get_user($obj->owner_guid);
			if($obj->time_created && $user instanceof ElggUser){
				$data[] = array($obj->time_created, "", "", "");
			}
		}
	} else if ($type === 'comments') {
		$comments = elgg_get_entities(array(
			'type' => 'object',
			'subtype' => 'comment',
			'limit' => 0
		));

		foreach($comments as $key => $obj){
			$user = get_user($obj->owner_guid);
			if($obj->time_created && $user instanceof ElggUser){
				$data[] = array($obj->time_created, "", "");
			}
		}
	} else if ($type === 'groupscreated') {
		$groupscreated = elgg_get_entities(array(
			'type' => 'group',
			'limit' => 0
		));

		foreach($groupscreated as $key => $obj){
			$user = get_user($obj->owner_guid);
			if($obj->time_created && $user instanceof ElggUser){
				$data[] = array($obj->time_created, "", "", "");
			}
		}
	} else if ($type === 'groupsjoined') {
		$dbprefix = elgg_get_config('dbprefix');
		$query = "SELECT * FROM {$dbprefix}entity_relationships WHERE relationship = 'member'";
		$groupsjoined = get_data($query);

		foreach($groupsjoined as $key => $obj){
			$user = get_user($obj->guid_one);
			$group = get_entity($obj->guid_two);
			if($obj->time_created && $user instanceof ElggUser && $group instanceof ElggGroup){
				$data[] = array($obj->time_created, "", "");
			}
		}
	} else if ($type === 'likes') {
		$likes = elgg_get_annotations(array(
			'annotation_names' => array('likes'),
			'limit' => 0
		));

		foreach($likes as $key => $obj){
			$entity = get_entity($obj->entity_guid);
			$user = get_user($obj->owner_guid);
			$user_liked = ($entity->title != "" ? $entity->title : ($entity->name != "" ? $entity->name : $entity->description));
			if($obj->time_created && $user instanceof ElggUser){
				$data[] = array($obj->time_created, "", "");
			}
		}
	} else if ($type === 'messages') {
		$messages = elgg_get_entities(array(
			'type' => 'object',
			'subtype' => 'messages',
			'limit' => 0
		));

		foreach($messages as $key => $obj){
			if($obj->fromId && $obj->fromId !== 1){
				$user = get_user($obj->owner_guid);
				if($obj->time_created && $user instanceof ElggUser){
					$data[] = array($obj->time_created, "", "");
				}
			}
		}
	} else if ($type === 'optins') {
		$optin_types = array(
			"opt_in_missions" => "missions:micro_mission",
			"opt_in_missionCreate" => "missions:micro_mission",
			"opt_in_swap" => "missions:job_swap",
			"opt_in_mentored" => "missions:mentoring",
			"opt_in_mentoring" => "missions:mentoring",
			"opt_in_shadowed" => "missions:job_shadowing",
			"opt_in_shadowing" => "missions:job_shadowing",
			"opt_in_jobshare" => "missions:job_sharing",
			"opt_in_pcSeek" => "missions:peer_coaching",
			"opt_in_pcCreate" => "missions:peer_coaching",
			"opt_in_ssSeek" => "missions:skill_sharing",
			"opt_in_ssCreate" => "missions:skill_sharing",
			"opt_in_rotation" => "missions:job_rotation",
			"opt_in_assignSeek" => "missions:assignment",
			"opt_in_assignCreate" => "missions:assignment",
			"opt_in_deploySeek" => "missions:deployment",
			"opt_in_deployCreate" => "missions:deployment",
			"opt_in_casual_seek" => "missions:casual",
			"opt_in_casual_create" => "missions:casual",
			"opt_in_student_seek" => "missions:student",
			"opt_in_student_create" => "missions:student"
		);

		$map = array();
		foreach ($optin_types as $optin_type => $index) {
			$map[$optin_type] = elgg_get_metastring_id($optin_type);
		}

		$wheres = "";
		foreach ($optin_types as $optin_type => $index) {
			$wheres .= "(m.name_id='{$map[$optin_type]}' AND m.value_id<>'')";
			if( $optin_type != end(array_keys($optin_types)) ){
        		$wheres .= " OR ";
			}
		}

		$db_prefix = elgg_get_config('dbprefix');
		$optins = elgg_get_entities_from_metadata(array(
			'type' => 'user',
			'limit' => 0,
			'joins' => array("JOIN {$db_prefix}metadata m on e.guid = m.owner_guid"),
			'wheres' => array($wheres)
		));
		foreach($optins as $key => $obj){
			foreach ($optin_types as $optin_type => $index) {
				if( $obj->$optin_type == 'gcconnex_profile:opt:yes' ){
					$string = elgg_echo($index, $lang);
					if(stripos($optin_type, 'create') !== false){
						$string .= " (" . elgg_echo("missions:offering", $lang) . ")";
					}
					if(stripos($optin_type, 'seek') !== false){
						$string .= " (" . elgg_echo("missions:seeking", $lang) . ")";
					}
					$data[$string]++;
				}
			}
		}
	} 
    return $data;
}