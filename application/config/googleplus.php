<?php

defined('BASEPATH') OR exit('No direct script access allowed');

$CI =& get_instance();
$CI->load->database();

$results = $CI->db->query('SELECT * FROM '.$CI->db->dbprefix(TBL_SETTINGS_FIELDS).' sf INNER JOIN '.$CI->db->dbprefix(TBL_SETTINGS_TYPES).' types on sf.type_id = types.type_id WHERE type_slug="SYSTEM_SETTINGS"')->result();


$site_settings = array();
foreach($results as $r) {
	$site_settings[strtolower($r->field_key)] =  $r->field_output_value;
}

$config['googleplus']['application_name'] = 'Tutors';
$config['googleplus']['client_id']        = $site_settings['google_client_id'];//482732847835-9rgvo4d0hfndkesfukan5e64ukahm9h5.apps.googleusercontent.com;
$config['googleplus']['client_secret']    = $site_settings['google_client_secret'];//'q6Z7OKQsK95mIqVh6lJyhNT6';
$config['googleplus']['redirect_uri']     = site_url().'Googlelogin/';
$config['googleplus']['api_key']          = 'AIzaSyD7B0622zB-ihKDyDSYLvhGncNJOm9009A';//'tutors-225107';
$config['googleplus']['scopes']           = array();

