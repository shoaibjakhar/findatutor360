<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';
header('Access-Control-Allow-Origin: *');
/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 */
class Service_login extends REST_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
		
		$this->load->database();
		$this->load->library(array('ion_auth','form_validation'));
		$this->load->helper(array('url','language','date','string'));
		
		$this->load->helper('global_helper');
		$this->load->config('ion_auth');
		
		
		$this->load->model('base_model');
		$this->load->model('student/student_model');
		$this->load->model('tutor/tutor_model');
		
		
		$this->load->model('ion_auth_model');
		$this->load->helper('security');
		$this->phrases = $this->config->item('words');
		/* Loading Language Files */
		$map = $this->config->item('languages');
		if (is_array($this->response->lang)) {
			
			$this->load->language('auth', $map[$this->response->lang[1]]);
			$this->load->language('ion_auth', $map[$this->response->lang[1]]);
		}
        

        // Configure limits on our controller methods
        // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        $this->methods['user_get']['limit'] = 500; // 500 requests per hour per user/key
        $this->methods['user_post']['limit'] = 100; // 100 requests per hour per user/key
        $this->methods['user_delete']['limit'] = 50; // 50 requests per hour per user/key
		//$this->authenticateUser($this->post('email'),$this->post('password'));
    }
	
	function authenticateUser($identity = NULL, $password = NULL)
	{		
		if($identity == ""  || $password == "") {
		
           $this->response(array('status' => 'Please provide Identity and Password'), 400);
        }
		
		//$remember = (bool) $this->post('remember');

		return $this->ion_auth->login($identity, $password, false);
	}
	/*
	*	Login Function 
	*	Returns Data
	*	John Peter @ 02-03-2016
	*/
	function login_post()
	{
		$postdata 	= file_get_contents("php://input");
		$request 	= json_decode($postdata);

		$email 		= $request->email;
		$password 	= $request->password;		

		$dashboard_data =  array();

        
		if ($this->ion_auth->login($email, $password, false))
		{
			//if the login is successfulid
			$user_id 	= $this->ion_auth->get_user_id();
			
			$data 		= $this->base_model->fetch_records_from('users',array('id'=>$user_id));

			$user_role_check  = $data[0]->user_belongs_group;

			if ($user_role_check == 2 || $user_role_check==3)
			{
			if(!empty($data))
			{
				if(!empty($request->device_id) && $data[0]->device_id != $request->device_id)
				{
					$update_data=array();
					$update_data['device_id'] = $request->device_id;
					
					$this->base_model->update_operation($update_data,'users',array('id'=>$data[0]->id));
					unset($update_data);
				}
				
				$update_data=array();
				$update_data['is_online']='yes';
				$this->base_model->update_operation($update_data,'users',array('id'=>$data[0]->id));
				unset($update_data);
				
				$data = $this->base_model->fetch_records_from('users',array('id'=>$user_id));

				
	 			$user_role  = $data[0]->user_belongs_group;
	 			
	 			if($user_id>0){
	 			 	if ($user_role == 2) {
	 					$dashboard_data = $this->student_model->get_student_dashboard_data($user_id);
	 			 	}else if($user_role == 3){
	 					$dashboard_data = $this->tutor_model->get_tutor_dashboard_data($user_id);
	 				}
	 			}

			}
		 }else{
		 	$data 		=	array();
			$response 	= 	strip_tags($this->ion_auth->errors());
			$status		=	0;
			$this->serviceResponse($data1,$response,$status);
	}
			$response 	= $user_id;
			$status 	=	1;
		}
		else
		{
			$data 		=	array();
			$response 	= 	strip_tags($this->ion_auth->errors());
			$status		=	0;	
			
		}
		$data1 = array(
					'data'=>$data[0],
					'dashboard_data'=>$dashboard_data
					);
		// var_dump($data.$data[0]);	
		$this->serviceResponse($data1,$response,$status);
	}
	/*
	*	Get Login data by User Id Function 
	*	Returns Data
	*/
	function get_user_details_by_id_post()
	{
		$postdata 	= file_get_contents("php://input");
		$request 	= json_decode($postdata);

		$user_id 		= $request->user_id;
		$dashboard_data =  array();

        
		//if ($this->ion_auth->login($email, $password, false))
		//{
			//if the login is successfulid
		//	$user_id 	= $this->ion_auth->get_user_id();
			
			$data 		= $this->base_model->fetch_records_from('users',array('id'=>$user_id));
			
			if(!empty($data))
			{
				/*if(!empty($request->device_id) && $data[0]->device_id != $request->device_id)
				{
					$update_data=array();
					$update_data['device_id'] = $request->device_id;
					
					$this->base_model->update_operation($update_data,'users',array('id'=>$data[0]->id));
					unset($update_data);
				}*/
				
				/*$update_data=array();
				$update_data['is_online']='yes';
				$this->base_model->update_operation($update_data,'users',array('id'=>$data[0]->id));
				unset($update_data);*/
				
				$data = $this->base_model->fetch_records_from('users',array('id'=>$user_id));

				
	 			$user_role  = $data[0]->user_belongs_group;
	 			
	 			if($user_id>0){
	 			 	if ($user_role == 2) {
	 					$dashboard_data = $this->student_model->get_student_dashboard_data($user_id);
	 			 	}else if($user_role == 3){
	 					$dashboard_data = $this->tutor_model->get_tutor_dashboard_data($user_id);
	 				}
	 			}

			}
			$response 	= "Login Success";
			$status 	=	1;
		/*}
		else
		{
			$data 		=	array();
			$response 	= 	strip_tags($this->ion_auth->errors());
			$status		=	0;	
			
		}*/
		$data1 = array(
					'data'=>$data[0],
					'dashboard_data'=>$dashboard_data
					);
		$this->serviceResponse($data1,$response,$status);
	}
	
	/**
	* METHOD FOR SIGNUP FOR  USER
	* SUCCESS MESSAGE OR ERROR MESSAGES WILL BE RETURNED
	*/
	function register_post()
    { 
					
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);

		$email    = $request->email;  		
		
		$user = $this->base_model->get_query_result("SELECT * FROM ".TBL_USERS." where email='".$email."'");			
		

		if (empty($user))
		{
			
			$password 	= $request->password;
			$additional_data = array();

			//Prepare User related data
			$first_name = ucfirst(strtolower($request->firstName));
			$last_name = ucfirst(strtolower($request->lastName));
			$username =  $first_name.' '.$last_name;

			$slug = prepare_slug($username, 'slug', 'users');

			$user_belongs_group = $request->group;

			
			$additional_data = array(
				'username' => $username,
				'slug' => $slug,
				'first_name' 	=> $first_name,
				'last_name'  	=> $last_name,
				'pin_code'   	=> $request->pincode,
				'phone_code'   	=> $request->phoneCode,
				'phone'     	=> $request->phone,
				'user_belongs_group' => $user_belongs_group,
				'device_id' => $request->device_id
			);
			$group = array($user_belongs_group);
			
		
			
    		$id = $this->ion_auth->register($email, $password, $email, $additional_data,$group);
    			
    			if ($id)
    			{
    				// send notification to all users
    				$type = 'student';
    				if($user_belongs_group==3){
    					$type = 'tutor';
    				}
    				
    				$this->sendNotificationToAll($type);
    			
    				$response =	strip_tags($this->ion_auth->messages());
    				$status = 1;
    			}
    			else
    			{
    				$response = strip_tags($this->ion_auth->errors(), 1);
    				$status		= 0;		
    			}
    		}
    		else
    		{
    			$response 	= get_languageword('Email already exists.');
    			$status		= 0;	
    		}
    		
    		$this->serviceResponse(array(),$response,$status);
    }


    // SOCIAL LOGIN

    function social_login_post()
    {
    	$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);

		$data = array();
		$response='';
		$status='';
		
		$email    = $request->email;  
			

		$data 	= $this->base_model->fetch_records_from('users',array('email'=>$email));

		if (empty($data)) {

			$status = 0;
			$response = 'New User';
			$this->serviceResponse(array(),$response,$status);
		}

		
		if (!empty($data)) {
			
			$dashboard_data = array();
			$user_id  = $data[0]->id;
			$user_role  = $data[0]->user_belongs_group;

		 	if($user_id>0){

			 	if ($user_role == 2) {
					$dashboard_data = $this->student_model->get_student_dashboard_data($user_id);
			 	}else if($user_role == 3){
					$dashboard_data = $this->tutor_model->get_tutor_dashboard_data($user_id);
				}
			}
		}

		$data1 = array(
					'data'=>$data[0],
					'dashboard_data'=>$dashboard_data
					);

		if(!empty($data1))
		{
			$status = 1;
			$response = 'Already user registered';
			$this->serviceResponse($data1,$response,$status);
		}
		else
		{
			$status = 0;
			$response = 'New User';
			$this->serviceResponse($data1,$response,$status);
		}
    }
	
    // SEND NOTIFICATION TO ALL USERS 

    function sendNotificationToAll($type='student')
    {
    	if($this->config->item('site_settings')->one_signal_push_notifications=='yes'){
    		 $results = $this->db->query('SELECT types.* FROM '.$this->db->dbprefix(TBL_SETTINGS_TYPES).' sf INNER JOIN '.$this->db->dbprefix(TBL_SETTINGS_TYPES).' types on sf.type_id = types.parent_id WHERE sf.type_slug="One_Signal_Push_Notifications"')->result();
    		 if(!empty(results)){
    		 	if($type=='student'){
    			$message = get_languageword('new student is added');
    			$title = get_languageword('new registration');
    			$result = $this->base_model->get_query_result('SELECT device_id FROM '.TBL_PREFIX.'users WHERE user_belongs_group=2 and active=1');
	    		}else{
	    			$message = get_languageword('new tutor is added');
	    			$title = get_languageword('new registration');
	    			$result = $this->base_model->get_query_result('SELECT device_id FROM '.TBL_PREFIX.'users WHERE user_belongs_group=3  and active=1');
	    		}

	    		$device_ids = array();
	    		if(!empty($result)){
					foreach ($result as $key => $value) {
						array_push($device_ids,$value->device_id);
					}
				}
				$this->load->library('OneSignalPush');
				$message = array(
						  "en" => $message,
						  "title" => $title,
						  "icon" => "myicon",
						  "sound"=>"default"
						);
						
			$data = array(
						"body" => $message,
						"title" => $title,
					);
					
					
			$gcpm = new OneSignalPush();
			$gcpm->setDevices($device_ids);
			$res = $gcpm->send($message,$data);
			return $res;

    		 }else{
    		 	return false;
    		 }

    	}else{
    		return false;
    	}
    }

	/**
	* FORGOT PASSWORD THROUGH THE SMS
	* @author JOHN PETER
	* @return ARRAY
	*/
	function forgot_password_sms_post()
	{
		$this->form_validation->set_rules('phone', $this->phrases['phone'], 'xss_clean|numeric|required');
			$this->form_validation->set_error_delimiters('<div class="error">', '</div>');
			if($this->form_validation->run()==true){
				$mobile_no = $this->input->post('phone');
				$user_details = $this->base_model->fetch_records_from(TBL_USERS,array('phone'=>$mobile_no));
				if(!empty($user_details)){
					
					if($this->config->item('site_settings')->sms_notifications=='Yes'){
						
						$sms_details = $this->base_model->fetch_records_from(TBL_SMS_TEMPLATES,array('subject'=>'forgot_password_otp'));
						if(!empty($sms_details)){
							$content = strip_tags($sms_details[0]->sms_template);
							$content     	= str_replace("__OTP__", $this->input->post('otp_string'),$content);							
							$res = sendSMS($mobile_no,$content);
							if($res['result'] == 1)
							{
								$data = $user_details[0];
								$response = $res['message'];
								$status = 1;
							}
							else
							{	
								$data = array();
								$response = $res['message'];
								$status = 0;
							}
						}else{
							$data = array();
							$response	=	(isset($this->phrases['SMS notification template is not active,please contact admin'])) ? $this->phrases['SMS notification template is not active,please contact admin'] : 'SMS Notification Template is not active,Please Contact Admin';
							
							$status = 0;
						}
						
					}else{
							$data = array();
							$response	=	(isset($this->phrases['SMS notification is not enable,please contact admin'])) ? $this->phrases['SMS notification is not enable,please contact admin'] : 'SMS Notification is not Enable,Please Contact Admin';
							
							$status = 0;
					}
										
				}else{
					$data 		= array();
					$response	=	(isset($this->phrases['mobile number does not exists'])) ? $this->phrases['mobile number does not exists'] : 'Mobile Number does not exists';
					
					$status 	= 0;
				}
				
			}else{
				$data 		= array();
				$response 	= strip_tags(validation_errors());
				$status 	= 0;
			}
		$this->serviceResponse($data,$response,$status);
	}
	
	/**
	* RESET PASSWORD
	* @author JOHN PETER
	* @return 
	*/
	
	function reset_password_post()
	{
		$user_id    = $this->input->post('id');
		$user 		= $this->base_model->fetch_records_from(TBL_USERS,array('id'=>$user_id));
				
		if (!empty($user)) {

			$this->form_validation->set_rules('new_password', 
			$this->lang->line('reset_password_validation_new_password_label') , 
			'required|matches[new_confirm]');
			$this->form_validation->set_rules('new_confirm', 
			$this->lang->line('reset_password_validation_new_password_confirm_label') , 
			'required');
			$this->form_validation->set_error_delimiters('<div class="error">', '</div>');
			if ($this->form_validation->run() 	== false	) {
					$response 	= strip_tags(validation_errors());
					$status 	= 0;		
				
			}
			else {
				
					$identity 					= $user[0]->{$this->config->item('identity', 
					'ion_auth') };
					$change 					= $this->ion_auth->reset_password(
					$identity, $this->input->post('new_password'));
					if ($change) {
						$response 	= strip_tags($this->ion_auth->messages());
						$status 	= 1;
					}
					else {
						$response = strip_tags($this->ion_auth->errors());
						$status = 0;						 
					}
		
			}
		}
		else {
			$response = 'User Not Found';
			$status = 0;
		}
		
		$this->serviceResponse(array(),$response,$status);
	}
	
	/**
	* Forgot Password
	* @author Navaneetha
	* @return boolean
	*/
	function forgot_password_post()
	{
		// setting validation rules by checking wheather identity is username or email
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$email = $request->email;
		$identity = $this->ion_auth->where('email',$email)->users()->row();

		if(empty($identity)) {

    		$response = get_languageword('Forgot password email not found');
    		$status = 0;
    		$this->serviceResponse(array(),$response,$status);	
		}

		// run the forgotten password method to email an activation code to the user
		$forgotten = $this->ion_auth->forgotten_password($email);

		if($forgotten)
		{
			// if there were no errors
			$response = get_languageword($this->ion_auth->messages());
			$status = 1;
			// confirmation page here instead of the login page
		}
		else
		{
			$response = get_languageword($this->ion_auth->errors());
			$status = 0;
		}
		
		$this->serviceResponse(array(),$response,$status);
	}
	
	// ACTIVE ACCOUNT ON SUCCESSFULLY OTP ENTERED
	
	function account_activate_post()
	{
		$email = $this->post('email');
		$data = array();
		$status = 0;
		$user = $this->base_model->fetch_records_from(TBL_USERS,array('email'=>$email));
		if(!empty($user)){
			$activation = $this->ion_auth->activate($user[0]->id,$user[0]->activation_code);
			if ($activation)
			{
				$data = $user;
				$response = $this->ion_auth->messages();
				$status = 1;
			}
			else
			{
				$response = strip_tags($this->ion_auth->errors());
				
			}
		}else{
			$response = 'Invalid Email Id';
			
		}
		$this->serviceResponse($data,$response,$status);
	}
	
	
	function get_terms_conditions_post()
	{
		$data = array();
		$data=$this->base_model->get_page_terms_and_conditions();
		$this->serviceResponse($data,'Terms and Conditions',1);
	}
	
	function get_about_us_post()
	{
		$data = array();
		$data=$this->base_model->get_page_about_us();
		$this->serviceResponse($data,'About us',1);
	}
	
	function contact_post()
	{
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		
		$data = array();
		$response='';
		$status=0;
		
		$first_name = $request->first_name;
		$last_name  = $request->last_name;
		$email 		= $request->email;
		$subjct 	= $request->subject;
		$msgg 		= $request->message;
		
		//Send conatct query details to Admin Email
		//Email Alert to Admin - Start
		//Get Contact Query Email Template
		$email_tpl = $this->base_model->fetch_records_from('email_templates', array('template_status' => 'Active', 'email_template_id' => '16'));
		
		$from 	= $email;
		$to 	= get_system_settings('Portal_Email');
		$sub 	= get_languageword("Contact Query Received");
		
		$msg 	= '<p>
					'.get_languageword('Hello Admin, ').',</p>
					<p>
					'.get_languageword('You got contact query Below are the details').'</p>
					<p>
					<strong>'.get_languageword('first_name').':</strong> '.$first_name.'</p>
					<p>
					<strong>'.get_languageword('last_name').':</strong> '.$last_name.'</p>
					<p>
					<strong>'.get_languageword('email').':</strong> '.$email.'</p>
					<p>
					<strong>'.get_languageword('subject').':</strong> '.$subjct.'</p>
					<p>
					<strong>'.get_languageword('message').':</strong> '.$msgg.'</p>
					<p>
					&nbsp;</p>
					';
		$msg 	.= "<p>".get_languageword('Thank you')."</p>";
		
		
		if(!empty($email_tpl)) {

		$email_tpl = $email_tpl[0];


			if(!empty($email_tpl->from_email)) {

				$from = $email_tpl->from_email;

			}

			if(!empty($email_tpl->template_subject)) {

				$sub = $email_tpl->template_subject;

			}

			if(!empty($email_tpl->template_content)) {

				$msg = "";
				$original_vars  = array($first_name, $last_name, $email, $subjct, $msgg,);
				$temp_vars		= array('___FIRST_NAME___', '___LAST_NAME___', '___EMAIL___', '___SUBJECT___', '___MESSAGE___');
				$msg = str_replace($temp_vars, $original_vars, $email_tpl->template_content);

			}

		}
		
		
		if(sendEmail($from, $to, $sub, $msg)) {
			$response = get_languageword('Your contact request sent successfully');
			$status=1;
		} else {
			$response = get_languageword('Your contact request not sent due to some technical issue Please contact us after some time Thankyou.');
			$status=0;
		}
		$this->serviceResponse($data,$response,$status);
	}
	
	// Common Response Method 
	 
	 function serviceResponse($data=array(),$response,$status=0)
	 {
	 		$data = array('data'=>$data);
			$response = array('message'=>$response,'status'=>$status);
			$result = array();
			array_push($result,$data);
			array_push($result,array('response'=>$response));
			$this->response(json_decode(json_encode ($result), true), 200);	
	 }
}
