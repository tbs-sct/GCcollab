<?php
/**
 * Elgg add action
 *
 * @package Elgg
 * @subpackage Core
 */

elgg_make_sticky_form('useradd');

// Get variables
$username = get_input('username');
$password = get_input('password', null, false);
$password2 = get_input('password2', null, false);
$email = get_input('email');
$name = get_input('name');

$user_type = get_input('user_type');
$federal = get_input('federal');
$institution = get_input('institution');
$university = get_input('university');
$college = get_input('college');
$provincial = get_input('provincial');
$ministry = get_input('ministry');
$municipal = get_input('municipal');
$international = get_input('international');
$ngo = get_input('ngo');
$community = get_input('community');
$business = get_input('business');
$media = get_input('media');
$retired = get_input('retired');
$other = get_input('other');

$admin = get_input('admin');
if (is_array($admin)) {
	$admin = $admin[0];
}
$sendemail = get_input('sendemail');
if (is_array($sendemail)) {
	$sendemail = $sendemail[0];
}

// no blank fields
if ($username == '' || $password == '' || $password2 == '' || $email == '' || $name == '') {
	register_error(elgg_echo('register:fields'));
	forward(REFERER);
}

if (strcmp($password, $password2) != 0) {
	register_error(elgg_echo('RegistrationException:PasswordMismatch'));
	forward(REFERER);
}

// For now, just try and register the user
try {
	$guid = register_user($username, $password, $name, $email, TRUE);

	if ($guid) {
		$new_user = get_entity($guid);
		if ($new_user && $admin && elgg_is_admin_logged_in()) {
			$new_user->makeAdmin();
		}

		elgg_clear_sticky_form('useradd');

		$new_user->admin_created = TRUE;
		// @todo ugh, saving a guid as metadata!
		$new_user->created_by_guid = elgg_get_logged_in_user_guid();

		if($user_type){ $new_user->user_type = $user_type; }
		if($federal){ $new_user->federal = $federal; }
		if($institution){ $new_user->institution = $institution; }
		if($university){ $new_user->university = $university; }
		if($college){ $new_user->college = $college; }
		if($provincial){ $new_user->provincial = $provincial; }
		if($ministry){ $new_user->ministry = $ministry; }
		if($municipal){ $new_user->municipal = $municipal; }
		if($international){ $new_user->international = $international; }
		if($ngo){ $new_user->ngo = $ngo; }
		if($community){ $new_user->community = $community; }
		if($business){ $new_user->business = $business; }
		if($media){ $new_user->media = $media; }
		if($retired){ $new_user->retired = $retired; }
		if($other){ $new_user->other = $other; }

		$subject = elgg_echo('useradd:subject', array(), $new_user->language);
		$body = elgg_echo('useradd:body', array(
			$name,
			elgg_get_site_entity()->name,
			elgg_get_site_entity()->url,
			$username,
			$password,
		), $new_user->language);


		if (elgg_is_active_plugin('cp_notifications')) {
			$message = array(
				'cp_user_name' => $name,
				'cp_msg_type' => 'cp_useradd',
				'cp_site_name' => elgg_get_site_entity()->name,
				'cp_site_url' => elgg_get_site_entity()->url,
				'cp_username' => $username,
				'cp_password' => $password,
				'cp_user' => $new_user, 
			);
			if($sendemail) {
				$result = elgg_trigger_plugin_hook('cp_overwrite_notification','all',$message);
			}
		} else {
			if($sendemail) {
				notify_user($new_user->guid, elgg_get_site_entity()->guid, $subject, $body);
			}
		}

		system_message(elgg_echo("adduser:ok", array(elgg_get_site_entity()->name)));
	} else {
		register_error(elgg_echo("adduser:bad"));
	}
} catch (RegistrationException $r) {
	register_error($r->getMessage());
}

forward(REFERER);
