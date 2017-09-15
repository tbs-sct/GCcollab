<?php
/*
 * Exposes API endpoints for logging in a user
 */

elgg_ws_expose_function(
	"login.user",
	"login_user",
	array(
		"user" => array('type' => 'string', 'required' => true),
		"password" => array('type' => 'string', 'required' => true),
		"lang" => array('type' => 'string', 'required' => false, 'default' => "en")
	),
	'Logs in a user based on user email',
	'POST',
	false,
	false
);

elgg_ws_expose_function(
	"login.userforchat",
	"login_user_for_chat",
	array(
		"user" => array('type' => 'string', 'required' => true),
		"key" => array('type' => 'string', 'required' => true),
		"lang" => array('type' => 'string', 'required' => false, 'default' => "en")
	),
	'Logs in a user based on user id for using chat',
	'POST',
	false,
	false
);

elgg_ws_expose_function(
	"login.userfordocs",
	"login_user_for_docs",
	array(
		"user" => array('type' => 'string', 'required' => true),
		"key" => array('type' => 'string', 'required' => true),
		"guid" => array('type' => 'int', 'required' => true),
		"lang" => array('type' => 'string', 'required' => false, 'default' => "en")
	),
	'Logs in a user based on user id for using Docs',
	'POST',
	false,
	false
);

elgg_ws_expose_function(
	"login.userforurl",
	"login_user_for_url",
	array(
		"user" => array('type' => 'string', 'required' => true),
		"key" => array('type' => 'string', 'required' => true),
		"url" => array('type' => 'string', 'required' => true),
		"lang" => array('type' => 'string', 'required' => false, 'default' => "en")
	),
	'Logs in a user based on user id and url',
	'POST',
	false,
	false
);

function login_user( $user, $password, $lang ){
	$user_entity = get_user_by_email($user);
	$username = $user_entity[0]->username;
	$access = elgg_authenticate($username, $password);

	if( true === $access ){
		return true;
	} else {
		return "Invalid user.";
	}
}

function login_user_for_chat( $user, $key, $lang ){
	$response = file_get_contents('https://api.gctools.ca/login.ashx?action=login&email=' . $user . '&key=' . $key);
	$json = json_decode($response);

	if( $json->GCcollabAccess ){
		$email = get_user_by_email($user)[0];

		if( $email ){
			login($email);
			forward('cometchat/cometchat_embedded.php');
		}
	} else {
		return "Invalid user key.";
	}
}

function login_user_for_docs( $user, $key, $guid, $lang ){
	$response = file_get_contents('https://api.gctools.ca/login.ashx?action=login&email=' . $user . '&key=' . $key);
	$json = json_decode($response);

	if( $json->GCcollabAccess ){
		$email = get_user_by_email($user)[0];

		if( $email ){
			login($email);
			$docObj = new ElggPad($guid);
			forward($docObj->getPadPath());
		}
	} else {
		return "Invalid user key.";
	}
}

function login_user_for_url( $user, $key, $url, $lang ){
	$response = file_get_contents('https://api.gctools.ca/login.ashx?action=login&email=' . $user . '&key=' . $key);
	$json = json_decode($response);

	if( $json->GCcollabAccess ){
		$email = get_user_by_email($user)[0];

		if( $email ){
			login($email);
			forward($url);
		}
	} else {
		return "Invalid user key.";
	}
}
