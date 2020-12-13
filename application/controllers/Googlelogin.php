<?php
/**
 * DOVBSV2
 * 
 * An online cab booking system in codeigneter framework
 * 
 * This content is released under the Codecanyon Market License (CML)
 * 
 * Copyright (c) 2017 - 2018, Codecakes
 *
 * @category  Googlelogin
 * @package   DOVBSV2
 * @author    DOVBSV2 <digitalvidhya4u@gmail.com>
 * @copyright 2017 - 2018, DOVBSV2
 * @license   http://opensource.org/licenses/MIT    MIT License
 * @link      http://codeigniter.com
 * @since     Version 1.0.0
 */
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * CodeIgniter User_authentication Class
 * 
 * Googlelogin.
 *
 * @category  Googlelogin
 * @package   DOVBSV2
 * @author    DOVBSV2 <digitalvidhya4u@gmail.com>
 * @copyright 2017 - 2018, DOVBSV2
 * @license   http://opensource.org/licenses/MIT    MIT License
 * @link      http://codeigniter.com
 */
class Googlelogin extends MY_Controller {

    /**
    | -----------------------------------------------------
    | PRODUCT NAME:     DIGI VEHICLE BOOKING SYSTEM (DVBSV2)
    | -----------------------------------------------------
    | AUTHOR:           DIGITAL VIDHYA TEAM
    | -----------------------------------------------------
    | EMAIL:            digitalvidhya4u@gmail.com
    | -----------------------------------------------------
    | COPYRIGHTS:       RESERVED BY DIGITAL VIDHYA
    | -----------------------------------------------------
    | WEBSITE:          http://digitalvidhya.com
    |                   http://codecanyon.net/user/digitalvidhya
    | -----------------------------------------------------
    |
    | MODULE:           Googlelogin
    | -----------------------------------------------------
    | This is Googlelogin module controller file.
    | -----------------------------------------------------
    **/
    function __construct()
    {
        parent::__construct();
       
        $this->load->helper('string');

        $this->load->library('ion_auth');

        $this->load->model('ion_auth_model');
        
    }

    /**
     * Googlelogin
     *
     *
     * @return boolean
    **/ 
	public function index()
	{
        
		if($this->session->userdata('login') == true){
			redirect(URL_AUTH_INDEX);
		}
		
		if (isset($_GET['code'])) {
			
			$this->googleplus->getAuthenticate();
            
			$this->session->set_userdata('login',true);
            
			$this->session->set_userdata('user_profile',$this->googleplus->getUserInfo());
            
            $userProfile=array();
            
            $userProfile = $this->googleplus->getUserInfo();
            
          
            
            $data=array();
            
            $data['oauth_provider'] = 'googleplus';
            $data['username']       = $userProfile['given_name'].$userProfile['family_name'];
            $data['oauth_uid']      = $userProfile['id'];
            $data['first_name']     = $userProfile['given_name'];
            $data['last_name']      = $userProfile['family_name'];
            $data['email']          = $userProfile['email'];
            
            
            $this->db->select(array('id','user_belongs_group'));
            $this->db->from(TBL_USERS);
            $this->db->where(array('email'=> $data['email']));
            $prevQuery = $this->db->get();

            $prevCheck = $prevQuery->num_rows();
        
            
            if($prevCheck > 0)
            {
                $prevResult = $prevQuery->row_array();

                $user_group = $prevResult['user_belongs_group'];
                
                if ($user_group!=GRP_USER) {
                    $this->prepare_flashmessage('Social logins only for student', 2);
                    redirect(URL_AUTH_LOGIN);
                }

                $userID = $prevResult['id'];
            }
            else
            {
                $username 	= $data['username'];
                $password 	= random_string('alnum', 5);
                $email 		= $data['email']; 
                
                $slug = prepare_slug($username, 'slug', 'users');
                
                $additional_data = array(
                        'first_name'            => $data['first_name'],
                        'last_name'             => $data['last_name'],
                        'username'              => $username,
                        'phone'                 => null,
                        'phone_code'            => null,
                        'pin_code'              => null,
                        'active'                => 1,
                        'created_on'            => time(),
                        'slug'                  => $slug,
                        'user_belongs_group'    => GRP_USER,
                        'is_social_login'       => 'yes'
                        );

                $group = array(2);
                $registered_by = $data['oauth_provider'];
                
                $userID = $this->ion_auth->register($username, $password, $email, $additional_data,$group,$registered_by);

                 if ($userID) {
                    //send notification to admin
                    $type = get_languageword('student');
                    $content=get_languageword("new_student_registered");
                    $link = SITEURL.'auth/index/2/read/'.$userID;

                    $not_data = array();
                    $not_data['user_id']    = $userID;
                    $not_data['title']      = get_languageword('new_user_registered');
                    $not_data['content']    = $content;
                    $not_data['datetime']   = date('Y-m-d H:i:s');
                    $not_data['admin_read'] = 0;
                    $not_data['page_link']  = $link;
                    $not_data['table_name'] = "users";
                    $not_data['primary_key_column'] = "id";
                    $not_data['primary_key_value']  = $userID;


                    $this->base_model->insert_operation($not_data,'notifications'); 
                    unset($not_data);
                } 
                   
            }
            
          
             // Check user data insert or update status
            if (!empty($userID)) {
                
                $password   = random_string('alnum', 5);

                if ($this->ion_auth->login($data['email'], $password, 1, true)) {
                 
                    $this->prepare_flashmessage('Loggedin Successfully', 0);
                    
                    redirect(URL_AUTH_INDEX);
             
                } else {
                    
                    $this->session->sess_destroy();
                    $this->googleplus->revokeToken();
                    $this->prepare_flashmessage($this->ion_auth->errors(), 1);
                    
                    redirect(URL_AUTH_LOGIN); 

                }
            } 
            else 
            {
                $this->prepare_flashmessage("Unable to login", 1);
                $this->session->sess_destroy();
                $this->googleplus->revokeToken();
                redirect(URL_AUTH_LOGIN);
            }
        
            
			redirect(URL_AUTH_LOGIN);
			
		} 
			
		redirect(URL_AUTH_LOGIN);
		
	}
	
	public function profile()
    {
		if($this->session->userdata('login') != true){
			redirect('');
		}
		$contents['user_profile'] = $this->session->userdata('user_profile');
		$this->load->view('profile',$contents);
	}
	
	public function logout()
    {
		$this->session->sess_destroy();
		$this->googleplus->revokeToken();
		redirect('');
	}
	
}
