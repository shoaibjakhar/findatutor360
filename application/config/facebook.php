<?php defined('BASEPATH') OR exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
|  Facebook API Configuration
| -------------------------------------------------------------------
|
| To get an facebook app details you have to create a Facebook app
| at Facebook developers panel (https://developers.facebook.com)
|
|  facebook_app_id               string   Your Facebook App ID.
|  facebook_app_secret           string   Your Facebook App Secret.
|  facebook_login_type           string   Set login type. (web, js, canvas)
|  facebook_login_redirect_url   string   URL to redirect back to after login. (do not include base URL)
|  facebook_logout_redirect_url  string   URL to redirect back to after logout. (do not include base URL)
|  facebook_permissions          array    Your required permissions.
|  facebook_graph_version        string   Specify Facebook Graph version. Eg v2.6
|  facebook_auth_on_load         boolean  Set to TRUE to check for valid access token on every page load.
*/
$CI =& get_instance();
$CI->load->database();

$results = $CI->db->query('SELECT * FROM '.$CI->db->dbprefix(TBL_SETTINGS_FIELDS).' sf INNER JOIN '.$CI->db->dbprefix(TBL_SETTINGS_TYPES).' types on sf.type_id = types.type_id WHERE type_slug="SYSTEM_SETTINGS"')->result();


$site_settings = array();
foreach($results as $r) {
	$site_settings[strtolower($r->field_key)] =  $r->field_output_value;
}

$config['facebook_app_id']              = $site_settings['facebook_app_id'];//'344408669716679';
$config['facebook_app_secret']          = $site_settings['facebook_app_secret'];//'14bec376317600a8ba070df8d5a2f8fe';
$config['facebook_login_type']          = 'web';
$config['facebook_login_redirect_url']  = 'user_authentication_facebook';
$config['facebook_logout_redirect_url'] = 'user_authentication_facebook/logout';
$config['facebook_permissions']         = array('email');
$config['facebook_graph_version']       = 'v2.6';
$config['facebook_auth_on_load']        = TRUE;