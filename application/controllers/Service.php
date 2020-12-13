<?php defined('BASEPATH') OR exit('No direct script access allowed');

 /* 
    | -----------------------------------------------------
    | PRODUCT NAME:     MENORAH TUTORS SYSTEM
    | -----------------------------------------------------
    | AUTHOR:           DIGITAL VIDHYA TEAM
    | -----------------------------------------------------
    | EMAIL:            digitalvidhya4u@gmail.com
    | -----------------------------------------------------
    | COPYRIGHTS:       RESERVED BY DIGITAL VIDHYA
    | -----------------------------------------------------
    | DATE:				05-06-2017
    | ------------------------------------------------------
    | WEBSITE:          http://digitalvidhya.com
    |                   http://codecanyon.net/user/digitalvidhya
    | -----------------------------------------------------
    |
    | MODULE:             REST CONTROLLER
    | -----------------------------------------------------
    | This is Orders module controller file.
    | -----------------------------------------------------
    */
require APPPATH.'/libraries/REST_Controller.php';
header('Access-Control-Allow-Origin: *');

class Service extends REST_Controller 
{
	function __construct()
    {
        // Construct our parent class
        parent::__construct();
		 
		$this->load->database();
		$this->load->library(array('ion_auth','form_validation'));
		$this->load->helper(array('url','language','global_helper'));
		$this->load->model(array('base_model', 'home_model','service_model'));
		$this->load->model(array('tutor/tutor_model','student/student_model'));
		
		$this->load->model('ion_auth_model');
		
		$this->load->helper('security');
		
		/* Loading Language Files */
		$map = $this->config->item('languages');
		if (is_array($this->response->lang)) {
			$this->load->language('auth', $map[$this->response->lang[1]]);
			$this->load->language('ion_auth', $map[$this->response->lang[1]]);
		}
        
        /* Configure limits on our controller methods. Ensure
		you have created the 'limits' table and enabled 'limits'
        within application/config/rest.php	*/
        $this->methods['user_get']['limit'] = 500; //500 requests per hour per user/key
        $this->methods['user_post']['limit'] = 100; //100 requests per hour per user/key
        $this->methods['user_delete']['limit'] = 50; //50 requests per hour per user/key
		
		/* Check Whether User logged in and is a member */
		// $this->phrases = $this->config->item('words');	
    }

	 //User Authentication
    function authenticateUser($identity = NULL, $password = NULL)
    {
        if ($identity == "" || $password == "") {
            $this->response(array(
                'status' => 'Please provide Email and Password'
            ), 400);
        }
        
        return $this->ion_auth->login($identity, $password, false);
    }
	
    // GET CATEGORIES
	function get_categories_post()
	{

		$postdata 	= file_get_contents("php://input");
		$request	=	json_decode($postdata);
		$from_limit = $request->from_limit;
		$to_limit = 20;
		$categories = $this->base_model->get_query_result('SELECT * FROM '.TBL_CATEGORIES.' WHERE is_parent = "1" AND status = "1" LIMIT '.$from_limit.','.$to_limit.'');
		$this->serviceResponse($categories,'Categories',1);
	}

	// GET COURSES
	function get_courses_post()
	{
		$postdata = file_get_contents("php://input");
    	$request = json_decode($postdata);

		$category_id = $request->category_id;
		$courses = $this->base_model->fetch_records_from('categories',array('categories'=>$category_id));

		$this->serviceResponse($courses,'Courses',1);
	}

	// GET ALL COURSES

	function get_all_courses_post()
	{
		$postdata 	= file_get_contents("php://input");
		$request	=	json_decode($postdata);
		$from_limit = $request->from_limit;
		$to_limit = 20;
		$categories = $this->base_model->get_query_result('SELECT * FROM '.TBL_CATEGORIES.' WHERE is_parent = "0" AND status = "1" LIMIT '.$from_limit.','.$to_limit.'');
		$this->serviceResponse($categories,'Courses',1);


		/*$courses = $this->base_model->fetch_records_from('categories',array('is_parent'=>0));
		$this->serviceResponse($courses,'Courses',1);	*/
	}
	function get_all_tutors_post()
	{
		$postdata 	= file_get_contents("php://input");
		$request	=	json_decode($postdata);
		$from_limit = $request->from_limit;
		$to_limit = 20;
		$type = 3;
		$categories = $this->base_model->get_query_result('SELECT * FROM '.TBL_USERS.' WHERE user_belongs_group="3" AND active="1" LIMIT '.$from_limit.','.$to_limit.'');
		$this->serviceResponse($categories,'Tutors',1);


		/*$courses = $this->base_model->fetch_records_from('categories',array('is_parent'=>0));
		$this->serviceResponse($courses,'Courses',1);	*/
	}
	function get_all_leads_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);
		$from_limit = $request->from_limit;
		// $from_limit = 0;
		// 
		$to_limit = 10;
		$data=array();
		
		//$student_id = 213;
		// $student_id = '161';//$request->user_id;
		
		$data = $this->base_model->get_query_result("SELECT sl.*,tt.teaching_type,l.location_name,c.name as course_name FROM pre_student_leads sl INNER JOIN pre_teaching_types tt ON sl.teaching_type_id=tt.id INNER JOIN pre_locations l ON sl.location_id=l.id INNER JOIN pre_categories c ON sl.course_id=c.id ORDER BY sl.id DESC LIMIT ".$from_limit.",".$to_limit."");

		
		$this->serviceResponse($data,'Student Leads',1);
	}

	// GET LEADS
	function check_viewed_lead_post()
	{
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$user_id = $request->user_id;
		// $user_id = $this->post('user_id');
		$lead_id = $request->lead_id;
		// $lead_id = $this->post('lead_id');

		if($this->home_model->is_already_viewed_the_lead($user_id,'student_leads',$lead_id)){
			$response = get_languageword('Tutor already viewed the lead');
			$status   = 1;
		}else{
			$response = get_languageword('Tutor not viewed the lead');
			$status   = 0;
		}
		

		$this->serviceResponse(array(),$response,$status);
	}

	//GET TEACHING TYPES
	function get_teaching_types_post()
	{
		$teaching_types = $this->base_model->fetch_records_from('teaching_types',array('status'=>1));

		$this->serviceResponse($teaching_types,'Teaching types',1);
	}



	// GET PACKAGES
	function get_packages_post()
	{
		// Package for : Tutor/Student
		$package_for = $this->post('package_for');
		$packages = $this->base_model->fetch_records_from('packages',array('package_for'=>$package_for,'status'=>'Active'));

		$this->serviceResponse($packages,'Packages',1);
	}
	
	function profile_upload_post()
	{
		// UPLOAD IMAGE
		$image 	= $_FILES['userfile']['name'];
		$user_id = $this->post('user_id');
		//Upload User Photo
		if (!empty($_FILES['userfile']['name'])) 
		{
			//$ext = pathinfo($image, PATHINFO_EXTENSION);
			$ext = pathinfo($_FILES['userfile']['name'], PATHINFO_EXTENSION);
			$file_name = $user_id.'.'.$ext;
			$config['upload_path'] 		= './assets/uploads/profiles/';
			$config['allowed_types'] 	= '*';
			$config['overwrite'] 		= true;
			$config['file_name']        = $file_name;
			$this->load->library('upload', $config);
			
			if($this->upload->do_upload())
			{
				$inputdata['photo']		= $file_name;
				//$this->create_thumbnail($config['upload_path'].$config['file_name'],'./assets/uploads/profiles/thumbs/'.$config['file_name'], 200, 200);
				$this->base_model->update_operation($inputdata, 'users', array('id' => $user_id));
				$result = $this->base_model->fetch_records_from('users',array('id'=>$user_id));
				//$result = array();
				$response = 'Image uploaded successfully';
				$status = 1;
			}else{
				$data = $this->upload->display_errors();
				$result = array();
				$response = 'Image upload failed';
				$status = 0;
				$this->serviceResponse($data,$response,$status);
			}
		}else{
			$result = array();
			$response = 'Please select image';
			$status = 0;
		}
			$this->serviceResponse($result,$response,$status);
		
	}

	// ADD TUTOR COURSE

	function add_tutor_course_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);

		//check  for the course already having 
		$course_id 			= $request->course_id;
		$user_id 			= $request->user_id;
		$tutor_course_id 	= $request->id;

		$input_data['tutor_id'] 		= $request->user_id;
		$input_data['course_id'] 		= $request->course_id;
		$input_data['duration_value'] 	= $request->duration_value;
		$input_data['duration_type'] 	= $request->duration_type;
		$input_data['fee'] 				= $request->fee;
		$input_data['content'] 			= $request->content;
		$input_data['time_slots'] 		= $request->time_slots;
		$input_data['days_off'] 		= $request->days_off;
		$input_data['status'] 			= 1;
		$input_data['sort_order'] 		= $request->sort_order;
		$input_data['created_at'] 		= date('Y-m-d h:i:s');

		if($tutor_course_id!=''){

			$where['id'] = $tutor_course_id;
			if($this->base_model->update_operation($input_data,'tutor_courses',$where)){
				$response = get_languageword('course updated successfully');
				$status = 1;
			}else{
				$response = get_languageword('course not updated');
				$status = 0;
			}
		}else{
		
			$result = $this->base_model->fetch_records_from('tutor_courses',array('tutor_id'=>$user_id,'course_id'=>$course_id,'status'=>'Active'));
			
			
			if(empty($result)){

				if($this->base_model->insert_operation($input_data,'tutor_courses')){
					$response = get_languageword('course added successfully');
					$status = 1;
				}else{
					$response = get_languageword('course not added');
					$status = 0;
				}

			}else{
				$response = get_languageword('course already added');
				$status   = 0;
			}

		}

		$this->serviceResponse(array(),$response,$status);
	}

	// GET BOOKINGS OF TUTORS

	function get_tutor_bookings_post()
	{
		$postdata 	= file_get_contents("php://input");
		$request	=	json_decode($postdata);
		$user_id 	= $request->user_id;
		$from_limit = $request->from_limit;
		$booking_type = $request->type;
       
        $status_condition = '';
		if($booking_type!='all'){
			$status_condition = ' AND b.status="'.$booking_type.'"';
		}

		//$from_limit = 0;
		$to_limit = 20;

		// $user_id 	= $this->post('user_id');

		$bookings 	= $this->base_model->get_query_result('SELECT b.*,u.username as student_name,u.phone,u.email,u.device_id,u.photo as student_photo,c.name as course_name FROM '.TBL_BOOKINGS.' b,'.TBL_USERS.' u,'.TBL_CATEGORIES.' c WHERE b.student_id=u.id AND b.course_id=c.id AND  b.tutor_id="'.$user_id.'" '.$status_condition.' LIMIT '.$from_limit.','.$to_limit.'');
		
		
		$this->serviceResponse($bookings,'Tutor Bookings',1); 
	}

	function get_tutor_blogs_post()
	{
		$postdata 	= file_get_contents("php://input");
		$request	=	json_decode($postdata);
		// $user_id 	= $this->post('user_id');

		
		//$languages = $this->base_model->fetch_records_from(TBL_BLOGS,array('status'=>'Active'));
		$from_limit = $request->from_limit;
		$to_limit	= 20;
		$blogs = $this->base_model->get_query_result('SELECT b.*,u.username as author_name,u.photo as author_photo FROM '.TBL_BLOGS.' b,'.TBL_PREFIX.'users u where u.id=b.tutor_id and  b.admin_approved = "Yes" LIMIT '.$from_limit.','.$to_limit.''); 
		
		//$blogs = $this->base_model->get_query_result("SELECT * FROM '.TBL_BLOGS.'");
		
		$this->serviceResponse($blogs,'Tutor Blogs',1); 
	}

	
	function booking_status_post()
	{
		$postdata 	= file_get_contents("php://input");
		$request  	= json_decode($postdata);
		$booking_id = $request->booking_id;
		$user_id	= $request->user_id;
		// $booking_id = $this->post('booking_id');
		// $user_id	= $this->post('user_id');
		// $device_id = $request->device_id;

		$booking_det = $this->base_model->fetch_records_from('bookings', array('booking_id' => $booking_id));
		if(!empty($booking_det)) {

				$booking_det = $booking_det[0];

				if($booking_det->tutor_id != $user_id) {

					$response = get_languageword('not_authorized');
					$status   = 0; 	
					$this->serviceResponse(array(),$response,$status);
    			
				}

				
				$booking_status = $booking_det->status;
				$updated_by = getUserType($booking_det->updated_by);

				// if(in_array($booking_status, array('pending', 'approved', 'session_initiated', 'running', 'completed'))) {
				// 		$crud->required_fields(array('status'));
				// }

				if($booking_status == "pending") {

					$status_array = array('approved' => get_languageword('approve'), 'cancelled_before_course_started' => get_languageword('cancel'));
				}

				if($booking_status == "approved") {
					//echo $booking_status; die();
					$status_array = array('cancelled_before_course_started' => get_languageword('cancel'));

					$today = date('Y-m-d');

					if((strtotime($booking_det->start_date) <= strtotime($today)) && (strtotime($today) <= strtotime($booking_det->end_date))) {

						// echo "Inner Time"; 

						$cur_time 	= (float)date('H.i');
						$time_slot 	= str_replace(':', '.', $booking_det->time_slot);
						$time 	  	= explode('-', str_replace(' ', '', $time_slot));
						$start_time = date('H:i', strtotime(number_format($time[0],2)));
						$end_time   = date('H:i', strtotime(number_format($time[1],2)));

						$certain_mins_before_start_time = (float)date('H.i', strtotime($start_time.' -'.$this->config->item('site_settings')->enable_initiate_session_option_before_mins.' minutes'));

						$certain_mins_before_end_time 	= (float)date('H.i', strtotime($end_time.' -'.$this->config->item('site_settings')->enable_course_completed_option_before_mins.' minutes'));
						// echo "<pre>";
						// echo $cur_time.'<br>';
						// echo $certain_mins_before_start_time.'<br>';
						// echo $certain_mins_before_end_time.'<br>';
						if($cur_time <= $certain_mins_before_start_time && $cur_time <= $certain_mins_before_end_time) {
							// echo "Condiion sat"; die();
							$status_array = array('session_initiated' => get_languageword('initiate_session'), 'cancelled_before_course_started' => get_languageword('cancel'));
						}
						// else{
						// 	echo "Not satis"; die();
						// }
					}

					

				}

				if($booking_status == "session_initiated") {

					$status_array = array('cancelled_before_course_started' => get_languageword('cancel'));
					// $crud->field_type('status', 'dropdown', $status);
				}

				if($booking_status == "running") {

					$status_array = array('cancelled_when_course_running' => get_languageword('cancel'));

					$today = date('Y-m-d');

					if(strtotime($today) >= strtotime($booking_det->start_date)) {

						$status_array = array('completed' => get_languageword('course_completed'), 'cancelled_when_course_running' => get_languageword('cancel'));
					}

					// $crud->field_type('status', 'dropdown', $status);

				}

				if($booking_status == "completed") {

					$status_array = array('called_for_admin_intervention' => get_languageword('claim_for_admin_intervention'));

					// $crud->field_type('status', 'dropdown', $status);

				}

				if($booking_status == "called_for_admin_intervention" && $updated_by == "student") {

					if($booking_det->prev_status == "pending")
						$status_array['approved'] = get_languageword('approve');
					else if($booking_det->prev_status == "approved")
						$status_array['cancelled_before_course_started'] = get_languageword('cancel');
					else if($booking_det->prev_status == "running") {
						$status_array['running'] = get_languageword('continue_course');
						$status_array['cancelled_when_course_running'] = get_languageword('cancel');
					}
					else if($booking_det->prev_status == "cancelled_when_course_running") {
						$status_array['running'] = get_languageword('continue_course');
					}
					else if($booking_det->prev_status == "completed") {
						$status_array['running'] = get_languageword('continue_course');
						$status_array['cancelled_when_course_running'] = get_languageword('cancel');
					}

					// $crud->required_fields(array('status'));
					// $crud->field_type('status', 'dropdown', $status);

				} else if($booking_status == "called_for_admin_intervention" && ($updated_by == "tutor"  || $updated_by == "admin")) {
					$status_array = array();
					// $crud->edit_fields('status_desc', 'updated_at');
				}


				if($booking_status == "cancelled_when_course_running" && $updated_by == "student") {

					// $crud->required_fields(array('status'));

					$status_array = array('called_for_admin_intervention' => get_languageword('claim_for_admin_intervention'));

					// $crud->field_type('status', 'dropdown', $status);
				}


				if($booking_status == "cancelled_after_course_completed" && $updated_by == "student") {

					// $crud->required_fields(array('status'));

					$status_array = array('called_for_admin_intervention' => get_languageword('claim_for_admin_intervention'));

					// $crud->field_type('status', 'dropdown', $status);
				}

				if($booking_status == "closed" || $booking_status == "cancelled_before_course_started" || ($booking_status == "cancelled_when_course_running" && $updated_by == "tutor") || ($booking_status == "cancelled_after_course_completed" && $updated_by == "tutor")) {

					// $crud->edit_fields('status_desc', 'updated_at');
					$status_array =  array();

				}
					$data = array();
				if(!empty($status_array)){
					foreach ($status_array as $key => $value) {
						$tmp = array(
							'status_key'=>$key,
							'status_value'=>$value
						);

						array_push($data, $tmp);
					}
				}
				$this->serviceResponse($data,'Status fields',1);

			}else{
				$response = get_languageword('Booking details not found');
				$status = 0;
				$this->serviceResponse(array(),$response,$status);
			}


	}

	// UPDATE BOOKING STATUS

	function update_booking_status_post()
	{
		$postdata 		= file_get_contents("php://input");
		$request 		= json_decode($postdata);
		$booking_status = $request->status;
		$prev_status 	= $request->prev_status;
		$updated_by 	= $request->updated_by;
		$updated_at 	= date('Y-m-d H:i:s');
		$booking_id		= $request->booking_id;
		$device_id      = $request->device_id;
		

		$update_data['status'] 		= $booking_status;
		$update_data['prev_status'] = $prev_status;
		$update_data['updated_by'] 	= $updated_by;
		$update_data['updated_at'] 	= $updated_at;
		$where['booking_id']		= $booking_id;

		if($this->base_model->update_operation($update_data,'bookings',$where)){
			$message = get_languageword('booking status has been changed to ').$booking_status;
			if($device_id!=''){
				$this->sendPushNotification($device_id,get_languageword('booking status'),$message);
			}
			
			$response = 'booking status updated successfully';
			$status   = 1;
		}else{
			$response = 'unable to update status';
			$status   = 0;
		}

		$this->serviceResponse(array(),$response,$status);
	}

 	
	//GET TUTOR COURSES  OLD //
	// function get_tutor_courses_post()
	// {
	// 	$postdata 	= file_get_contents("php://input");
	// 	$request	=	json_decode($postdata);
	// 	$user_id 	= $request->user_id;
		

	// 	if ($request->from_limit=='no_limit') {

	// 		$from_limit = 0;
	// 		$to_limit = 1000;

	// 	} else {

	// 		$from_limit = $request->from_limit;
	// 		$to_limit = 20;
	// 	}
		
	// 	// $user_id 	= $this->post('user_id');

	// 	$courses 	= $this->base_model->get_query_result('SELECT tc.*,c.name as course_name,c.image as course_image,c.slug as course_slug FROM '.TBL_TUTOR_COURSES.' tc,'.TBL_CATEGORIES.' c WHERE tc.course_id=c.id AND tc.tutor_id="'.$user_id.'" AND tc.status=1 AND c.status=1 ORDER BY tc.sort_order ASC LIMIT '.$from_limit.','.$to_limit.''); 
		
	// 	$this->serviceResponse($courses,'Tutor Courses',1); 
	// }


	//GET TUTOR COURSES  NEW DATE -> 15-04-2020 //
	function get_tutor_courses_post()
	{
		$postdata 	= file_get_contents("php://input");
		$request	=	json_decode($postdata);
		$user_id 	= $request->user_id;

			$from_limit = 0;
			$to_limit = 1000;
	
		// $user_id 	= $this->post('user_id');

		$courses 	= $this->base_model->get_query_result('SELECT tc.*,c.name as course_name,c.image as course_image,c.slug as course_slug FROM '.TBL_TUTOR_COURSES.' tc,'.TBL_CATEGORIES.' c WHERE tc.course_id=c.id AND tc.tutor_id="'.$user_id.'" AND tc.status=1 AND c.status=1 ORDER BY tc.sort_order ASC LIMIT '.$from_limit.','.$to_limit.''); 
		
		$this->serviceResponse($courses,'Tutor Courses',1); 
	}

	
	
	
	//STUDENT send message to tutor
	function stu_send_message_post()
	{
		$postdata 	= file_get_contents("php://input");
		$request	=	json_decode($postdata);
		
		$data = array();
		$response='';
		$status=0;
		
		$student_id = $request->student_id;
		$tutor_id = $request->tutor_id;
		
		$credits_for_sending_message = $this->config->item('site_settings')->credits_for_sending_message;
		
		//Check Whether student is premium user or not
		if(!is_premium($student_id)) 
		{
			$response = get_languageword('please_become_premium_member_to_send_message_to_tutor');
			$status=0;
			$this->serviceResponse($data,$response,$status); 
		}
		
		
		//Check If student has sufficient credits to send message to tutor
		if(!is_eligible_to_make_booking($student_id, $credits_for_sending_message)) 
		{
			$response = get_languageword("you_do_not_have_enough_credits_to_send_message_to_the_tutor_Please_get_required_credits_here");
			$status=0;
			$this->serviceResponse($data,$response,$status);
		}
		
		$course_name = $this->base_model->fetch_value('categories', 'name', array('id' => $request->course_id));
		
		
		$inputdata['from_user_id'] 	= $student_id;
		$inputdata['name'] 			= $request->name;
		$inputdata['course_slug']	= $course_name;
		$inputdata['email'] 		= $request->email;
		$inputdata['phone'] 		= $request->phone;
		$inputdata['message'] 		= $request->message;
		
		$to_user_type   = "Tutor";
		$inputdata['to_user_id']   = $request->tutor_id;
		
		$inputdata['created_at']	= date('Y-m-d H:i:s');
		$inputdata['updated_at']	= $inputdata['created_at'];
		
		$ref = $this->base_model->insert_operation($inputdata, 'messages');
		
		if($ref) 
		{
			//Send message details to Tutor Email
			//Email Alert to Tutor - Start
			//Get Send Message Email Template
			
			$email_tpl = $this->base_model->fetch_records_from('email_templates', array('template_status' => 'Active', 'email_template_id' => '17'));
			
			$tutor_rec = getUserRec($inputdata['to_user_id']);
			
			$from 	= $inputdata['email'];
			$to 	= $tutor_rec->email;
			$sub 	= get_languageword("Message Received From Student");
			
			$msg 	= '<p>
						'.get_languageword('Hi ').$tutor_rec->username.',</p>
					<p>
						'.get_languageword('You got a message from Student Below are the details').'</p>
					<p>
						<strong>'.get_languageword('name').':</strong> '.$inputdata['name'].'</p>
					<p>
						<strong>'.get_languageword('email').':</strong> '.$inputdata['email'].'</p>
					<p>
						<strong>'.get_languageword('phone').':</strong> '.$inputdata['phone'].'</p>
					<p>
						<strong>'.get_languageword('course_seeking').':</strong> '.$inputdata['course_slug'].'</p>
					<p>
						<strong>'.get_languageword('message').':</strong> '.$inputdata['message'].'</p>
					<p>
						&nbsp;</p>
					';
			$msg 	.= "<p>".get_languageword('Thank you')."</p>";
			
			if(!empty($email_tpl)) 
			{
				$email_tpl = $email_tpl[0];
				
				if(!empty($email_tpl->from_email)) 
				{
					$from = $email_tpl->from_email;
				}
				
				if(!empty($email_tpl->template_subject)) 
				{
					$sub = $email_tpl->template_subject.get_languageword(' Student');
				}
				
				if(!empty($email_tpl->template_content)) 
				{
					$msg = "";
					$original_vars  = array($tutor_rec->username, get_languageword('Student'), $inputdata['name'], $inputdata['email'], $inputdata['phone'], $inputdata['course_slug'], $inputdata['message']);
					$temp_vars		= array('___TO_NAME___','___USER_TYPE___','___NAME___', '___EMAIL___', '___PHONE___', '___COURSE___', '___MESSAGE___');
					$msg = str_replace($temp_vars, $original_vars, $email_tpl->template_content);
				}
			}
			
			if(sendEmail($from, $to, $sub, $msg)) 
			{
				//Log Credits transaction data & update user net credits - Start
				
				$per_credit_value = $this->config->item('site_settings')->per_credit_value;
				
				$log_data = array(
					'user_id' => $inputdata['from_user_id'],
					'credits' => $credits_for_sending_message,
					'per_credit_value' => $per_credit_value,
					'action'  => 'debited',
					'purpose' => 'For Sending Message To Tutor "'.$tutor_rec->username.'" ',
					'date_of_action	' => date('Y-m-d H:i:s'),
					'reference_table' => 'messages',
					'reference_id' => $ref,
				);

				log_user_credits_transaction($log_data);

				update_user_credits($inputdata['from_user_id'], $credits_for_sending_message, 'debit');
				//Log Credits transaction data & update user net credits - End
				
				$response = get_languageword('Your message sent to Tutor successfully');
				$status=1;
				
				$data = $this->base_model->fetch_records_from('users',array('id'=>$student_id));
				$this->serviceResponse($data,$response,$status);
			} 
			else 
			{
				$response = get_languageword('Your message not sent due to some technical issue Please send message after some time Thankyou');
				$status=0;
				$this->serviceResponse($data,$response,$status);
			}
		} 
		else 
		{
			$response = get_languageword("message_not_sent_please_try_again");
			$status=0;
			$this->serviceResponse($data,$response,$status);
		}
	}
	
	//Get Tutor Reviews
	function get_tutor_reviews_post()
	{
		$postdata 	= file_get_contents("php://input");
		$request	= json_decode($postdata);
		$tutor_id 	= $request->tutor_id;
		
		$data 	= array();
		$response='';
		$status=0;
		
		$query = "select  u.username as student_name,u.gender,u.photo,c.name as course,tr.rating,tr.comments,tr.created_at as posted_on from ".$this->db->dbprefix('tutor_reviews')." tr inner join ".$this->db->dbprefix('users')." u on tr.student_id = u.id inner join ".$this->db->dbprefix('categories')." c on tr.course_id=c.id where tr.tutor_id=".$tutor_id." and tr.status='Approved' ORDER by rating DESC ";//limit 0,5
     
		$data = $this->base_model->get_query_result($query);
		if(empty($data)) 
		{
			$response='No Reviews';
			$status=0;
		}
		else
		{
			$response='Tutor Reviews';
			$status=1;
		}
		$this->serviceResponse($data,$response,$status);
	}
	
	//DELETE TUTOR COURSE
	function delete_tutor_course_post()
	{
		$postdata 	= file_get_contents("php://input");
		$request	= json_decode($postdata);
		$user_id 	= $request->user_id;
		$id 		= $request->id;
		
		if ($this->base_model->delete_record_new('tutor_courses', array('tutor_id' => $user_id,'id'=>$id))) 
		{
			$status	  = 1;
			$response = get_languageword('deleted_successfully');
		}
		else
		{
			$status	  = 0;
			$response = get_languageword('not_deleted');
		}
		$this->serviceResponse(array(),$response,$status); 
	}
	
	//TUTOR MANAGE LOCATIONS
	function get_tutor_locations_post()
	{
		$postdata 	= file_get_contents("php://input");
		$request	= json_decode($postdata);
		$tutor_id 	= $request->user_id;
		// $tutor_id   = $this->post('user_id');
		
		// $locations = array();
		
		$parentLocationDetails = $this->db->select('id AS parentLocation_id, location_name AS parentLocation_name')->get_where($this->db->dbprefix( 'locations' ), array('parent_location_id' => 0, 'status' => 'Active'))->result();
		
		// foreach ($variable as $key => $value) {
		// 	# code...
		// }

		foreach($parentLocationDetails as $index => $p) 
		{
			$query = "SELECT l . * ,(
					SELECT count( * )
					FROM ".$this->db->dbprefix( 'tutor_locations' )." tl,
					 ".$this->db->dbprefix( 'users' )." u,
					 ".$this->db->dbprefix( 'users_groups' )." ug
					WHERE (tl.location_id = l.id OR 
					u.location_id = l.id) 
					AND ug.group_id = 3
					AND ug.user_id = u.id
					AND u.id = tl.tutor_id
					AND u.active = 1
					AND tl.status = '1'
					) AS no_of_tutors,(SELECT tc.location_id  from ".$this->db->dbprefix('tutor_locations')." tc WHERE tc.tutor_id=".$tutor_id." AND tc.location_id=l.id AND tc.status=1) as checked_id
					FROM ".$this->db->dbprefix( 'locations' )." l
					WHERE l.parent_location_id = ".$p->parentLocation_id."
					AND l.status = 'active'";
			
			$childLocations = $this->db->query($query)->result();

			$parentLocationDetails[$index]->sub_locations = $childLocations;
			
			// $locations[$p->parentLocation_name] = $childLocations;		
		}
		$response = 'Tutor Locations';
		$this->serviceResponse($parentLocationDetails,$response,0);
	}
	
	// UPDATE TUTOR LOCATIONS

	function update_tutor_locations_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);

		$user_id 	= $request->user_id;
		$locations 	= json_decode($request->locations);
		$update_data = array();
		$get_existing_locations  = $this->base_model->fetch_records_from('tutor_locations',array('tutor_id'=>$user_id));
		if(!empty($locations)){

			foreach ($locations as  $value) {
				$temp = array(
					'tutor_id'=>$user_id,
					'location_id'=>$value->location_id,
					'created_at'=>date('Y-m-d h:i:s')
				);

				array_push($update_data, $temp);
			}
		}

		if(!empty($get_existing_locations)){
			$where['tutor_id'] = $user_id;
			$this->base_model->delete_record_new('tutor_locations',$where);
			if(!empty($update_data)){
				$this->db->insert_batch('tutor_locations',$update_data);

			} 

		}else{
			if(!empty($update_data)){
				$this->db->insert_batch('tutor_locations',$update_data);
			} 
		}


		$response = get_languageword('Tutor locations updated successfully');
		

		$this->serviceResponse(array(),$response,1);
	}

	// TUTORS TEACHING 

	function get_tutors_teaching_types_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);
		$user_id  = $request->user_id;
		// $user_id =  $this->post('user_id');

		$teaching_types = $this->base_model->get_query_result("SELECT tt.id, tt.teaching_type,CASE WHEN EXISTS (SELECT ttt.teaching_type_id FROM pre_tutor_teaching_types ttt WHERE ttt.teaching_type_id = tt.id AND ttt.tutor_id='".$user_id."') THEN true ELSE false END AS checked  FROM pre_teaching_types tt WHERE tt.status=1");

		$this->serviceResponse($teaching_types,'teaching types',1);
	}

	// UPDATE TUTOR TEACHING TYPES

	function update_tutor_teaching_types_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);

		$user_id 	= $request->user_id;
		$types 	= json_decode($request->types);
		$update_data = array();
		$get_existing_teaching_types  = $this->base_model->fetch_records_from('tutor_teaching_types',array('tutor_id'=>$user_id));
		if(!empty($types)){

			foreach ($types as  $value) {
				$temp = array(
					'tutor_id'=>$user_id,
					'teaching_type_id'=>$value->teaching_id,
					'created_at'=>date('Y-m-d h:i:s')
				);

				array_push($update_data, $temp);
			}
		}

		if(!empty($get_existing_teaching_types)){
			$where['tutor_id'] = $user_id;
			$this->base_model->delete_record_new('tutor_teaching_types',$where);
			if(!empty($update_data)){
				$this->db->insert_batch('tutor_teaching_types',$update_data);

			} 

		}else{
			if(!empty($update_data)){
				$this->db->insert_batch('tutor_teaching_types',$update_data);
			} 
		}


		$response = get_languageword('Tutor teaching types updated successfully');
		

		$this->serviceResponse(array(),$response,1);
	}

	// Manage Profile
    function edit_profile_post()
    {
        if (!$this->post('first_name') || !$this->post('last_name') || !$this->post('phone') || !$this->post('address') || !$this->post('city') || !$this->post('landmark') || !$this->post('id')) {
            $response = 'All fields are mandatory';
			$this->serviceResponse($data = array(),$response,0);
        }
		
        $user_id  = $this->post('id');
        $username = strtolower($this->post('first_name')) . ' ' . strtolower($this->post('last_name'));
        $update_data     = array(
            'first_name' => $this->post('first_name'),
            'last_name' => $this->post('last_name'),
            'username' => $username,
            'address' => $this->post('address'),
            'phone' => $this->post('phone'),
            'city' => $this->post('city'),
            // 'state' => $this->post('state'),
            // 'pincode' => $this->post('pincode'),
            'landmark' => $this->post('landmark'),
            'updated_on' => date('Y-m-d')
        );
        $where['id'] = $this->post('id');
        if ($this->base_model->update_operation($update_data,TBL_USERS,$where)) {
            $response = 'Updated successfully';
            $this->serviceResponse($data = array(),$response,1);
        } else {
            $response = $this->ion_auth->errors();
            $this->serviceResponse($data = array(),$response,0);
        }
    }
	


    /*** Get Site Setting Details ****/
    function get_site_settings_post()
    {

    	/*$results =  $this->base_model->get_query_result('SELECT * FROM pre_system_settings_fields sf INNER JOIN pre_system_settings_types types on sf.type_id = types.type_id WHERE types.type_slug="SYSTEM_SETTINGS" || types.type_slug="PUSH_NOTIFICATION_SETTINGS"');

			$site_settings = array();
			foreach($results as $r) {
				$site_settings[strtolower($r->field_key)] =  $r->field_output_value;
			}


		$this->serviceResponse($site_settings,'Site Settings',1);*/
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);

		$response='Site Details';


		$results =  $this->base_model->get_query_result('SELECT * FROM pre_system_settings_fields sf INNER JOIN pre_system_settings_types types on sf.type_id = types.type_id WHERE types.type_slug="SYSTEM_SETTINGS" || types.type_slug="PUSH_NOTIFICATION_SETTINGS"');

		$site_settings = array();
		foreach($results as $r) {
			$site_settings[strtolower($r->field_key)] =  $r->field_output_value;
		}
		

        if (!empty($site_settings)) 
		{
            $site_settings	=	$site_settings;
			$response		=	'Site details found'; 
			

			//languages
			$language_types = array();

			//student dashboard data
			//$dashboard_data = [];
		
			$data = array();
			
			$query  = "SELECT tds.column_name,c.code FROM information_schema.columns tds,pre_languages c WHERE table_name='".TBL_PREFIX."languagewords' AND tds.column_name!= 'lang_id' AND tds.column_name!= 'phrase_for' AND tds.column_name!= 'lang_key' AND c.name=tds.column_name "; 
			
			$languages = $this->db->query($query)->result();

			
			if(!empty($languages))
			{
				foreach($languages as $lang):
					
					$language='';
					$language=$lang->column_name;
					
					$data['language_name'] = $lang->column_name;
					$data['language_code'] = $lang->code;
					
					
					$languagewords = "select lang_key,$language from ".TBL_PREFIX."languagewords where phrase_for='App'";
					$languagewords = $this->db->query($languagewords)->result();
					
					$language_strings = array();
					foreach($languagewords as $key=>$val):
						$language_strings[str_replace("_", " ", $val->lang_key)] = $val->$language;
					endforeach;
					$data['language_strings'] = $language_strings;
					
					array_push($language_types,$data);
					unset($data);
				endforeach;
			}



			
	 		$user_id 	= $request->user_id;
	 		$user_role  = $request->user_role;
	 		// $user_id = $this->post('user_id');
	 		// $user_role = $this->post('user_role');
	 		if($user_id>0){
	 			 if ($user_role == 2) {
	 				$dashboard_data = $this->student_model->get_student_dashboard_data($user_id);
	 			 } else if($user_role == 3){
	 				$dashboard_data = $this->tutor_model->get_tutor_dashboard_data($user_id);
	 			}
	 		}
	 		

	 		


        } 
		else 
		{
            $site_settings	=	array();
			$response		=	'Site details not found'; 
        }

		$data = array(
					'site_settings'=>$site_settings,
					'language_types'=>$language_types
					// 'dashboard_data'=>$dashboard_data
					);
		$this->serviceResponse($data,$response,1);
    }
	
	function test_settings_get()
	{
		$results =  $this->base_model->get_query_result('SELECT * FROM pre_system_settings_fields sf INNER JOIN pre_system_settings_types types on sf.type_id = types.type_id WHERE types.type_slug="SYSTEM_SETTINGS" || types.type_slug="PUSH_NOTIFICATION_SETTINGS"');

			$site_settings = array();
			foreach($results as $r) {
				$site_settings[strtolower($r->field_key)] =  $r->field_output_value;
			}


		$this->serviceResponse($site_settings,'Site Settings',1);
	}
    
    // GET PHONE CODES

    function get_phone_codes_post()
    {
    	$countries = $this->base_model->fetch_records_from('country');
		
    	$country_opts = array();

		foreach ($countries as $key => $value) {
			
			$temp = array(
					'id'=>$value->phonecode,
					'name'=>$value->nicename." +".$value->phonecode
				);
			array_push($country_opts, $temp);
		}
		array_push($country_opts, array('' => get_languageword('select_Phone_Code')));
		// $country_opts = array('' => get_languageword('select_Phone_Code'));

		$this->serviceResponse($country_opts,'Country Codes',1);
    }
	
	/**
	* Change Password
	* @author John Peter
	* @return
	*/
	/* function change_password_post()
	{
					
		$identity 							= $this->post('email');
		$change 							= $this->ion_auth->change_password(
		$identity, $this->post('current_password') , $this->post('new_password'));
		if ($change) {

				$response = strip_tags($this->ion_auth->messages());
				$status = 1;
		}
		else {
				
				$response = strip_tags($this->ion_auth->errors());
				$status = 0;
		}
		
		$this->serviceResponse($data = array(),$response,$status);
		
	} */
	
    
  
	// Dynamic Pages
	
	function pages_post()
	{
		$page_details	= $this->base_model->fetch_records_from('pages',array('status'=>'Active'));
		
		if(count($page_details)>0){
			$page_details = $page_details;
			$response		=	'Page Detials found';
		}else{
			$page_details = array();
			$response		=	'Page Detials found empty';
		}
		$this->serviceResponse($page_details,$response,1);
	}
	
	function get_languages_post()
	{
		$languages = $this->base_model->fetch_records_from(TBL_LANGUAGES,array('status'=>'Active'));
		if(count($languages)>0){
			$languages = $languages;
		}else{
			$languages = array();
		}
		$this->serviceResponse($languages,'Languages',1);
	}
	
	function get_language_strings_post()
	{
		$language_id = $this->post('language_id');
		if($language_id!='' || !is_numeric($language_id)){
			$language_id = 1;	
		}
		$phrases 	 = $this->base_model->run_query(
		"SELECT p.id,p.text, ml.text as existing_text FROM " . DBPREFIX.TBL_PHRASES
		 . " p," . DBPREFIX.TBL_MULTI_LANG . " ml WHERE ml.phrase_id=p.id 
		AND ml.lang_id=" . $language_id." AND p.phrase_type='app' AND ml.phrase_type='app'");
		$option_data = array(
            
        );
		if(count($phrases)>0){
			 foreach ($phrases as $key => $val) {
				$option_data[$val->text] = $val->existing_text;
			}
		}
		
		$this->serviceResponse($option_data,$this->phrases['could not found the record'],1);
		
	}

	/* SEARCH STUDENT LEADS */
    function search_student_leads_post()
	{
		$postdata = file_get_contents("php://input");

		$request = json_decode($postdata);
		$from_limit = $request->from_limit;
		$to_limit = 10;
		/* $course_slug = '';
		if(!empty($request->course_slug)){
			$course_slug 	= $request->course_slug;
		}

		$location_slug = '';
		if(!empty($request->location_slug)){
			$location_slug 			= $request->location_slug;
		}

		$teaching_type_slug = '';
		if(!empty($request->teaching_type_slug)){
			$teaching_type_slug 	= $request->teaching_type_slug;	
		}
		
		if(!empty($course_slug[0]) && $course_slug[0] == "by_location")
			$course_slug = '';
		if(!empty($course_slug[0]) && $course_slug[0] == "by_teaching_type") {
			$teaching_type_slug = $location_slug;
			$course_slug   = '';
			$location_slug = '';
		}

		$course_slug 	= str_replace('_', '-', $course_slug);
		$location_slug 	= str_replace('_', '-', $location_slug);
		$teaching_type_slug = str_replace('_', '-', $teaching_type_slug);

		$params = array('course_slug' 	=> $course_slug, 
						'location_slug' => $location_slug, 
						'teaching_type_slug' => $teaching_type_slug);

		$data = $this->home_model->get_student_leads($params); */
		
		$data = array();
		
		$course_cond 		= "";
        $location_cond 		= "";
        $teaching_type_cond = "";
		
		if(isset($request->courses))
		{
			$courses = $request->courses;
			if(!empty($courses))
			{
				$courses=implode(",",$courses);
				$course_cond = " AND sl.course_id IN (".$courses.") ";
			}
		}
		
		if(isset($request->locations))
		{
			$locations = $request->locations;
			if(!empty($locations))
			{
				$locations=implode(",",$locations);
				$location_cond = " AND sl.location_id IN (".$locations.") ";
			}
		}
		
		if(isset($request->teaching_types))
		{
			$teaching_types = $request->teaching_types;
			if(!empty($teaching_types))
			{
				$teaching_types=implode(",",$teaching_types);
				$teaching_type_cond = " AND sl.teaching_type_id IN (".$teaching_types.") ";
			}
		}
		
		$query = "SELECT u.*, sl.*, sl.id AS lead_id FROM ".TBL_USERS." u INNER JOIN ".TBL_USERS_GROUPS." ug ON ug.user_id=u.id INNER JOIN ".TBL_STUDENT_LEADS." sl ON sl.user_id=u.id WHERE u.active=1 AND u.visibility_in_search='1' AND u.availability_status='1' AND u.is_profile_update=1 AND ug.group_id=2 AND sl.status='opened' ".$course_cond." ".$location_cond." ".$teaching_type_cond." ORDER BY sl.id DESC LIMIT ".$from_limit.",".$to_limit." ";
		
		$data = $this->base_model->get_query_result($query);
		
		$this->serviceResponse($data,'Leads',1);
		
	}

	// LEAD DETAILS 

	function lead_details_post()
	{
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$student_lead_id 	= $request->lead_id;
		$student_slug 		= $request->slug;
		$stduent_details 	= $this->service_model->get_student_profile($student_slug,$student_lead_id,'105');

		$this->serviceResponse($stduent_details,'Lead details',1);
	}
 
 	function leads_filter_data_post()
 	{
 		/* //Course Options
		$courses = $this->home_model->get_courses();
		// $course_opts[''] = get_languageword('select');
		$show_records_count_in_search_filters = strip_tags($this->config->item('site_settings')->show_records_count_in_search_filters);
		$avail_records_cnt = "";
		$course_opts = array();
		if(!empty($courses)) {
			foreach ($courses as $key => $value) {
				if($show_records_count_in_search_filters == "Yes") {

					$avail_records_cnt = " (".count($this->home_model->get_student_leads(array('course_slug'=>$value->slug))).")";
				}

					$temp = array(
						'slug'=>$value->id,
						'name'=>$value->name.$avail_records_cnt
					);
				array_push($course_opts, $temp );	
			}
		}
		
		// courses options
		 $data['course_opts'] = $course_opts;
		// $data->course_opts = $course_opts;

 		//Location Options
		$locations = $this->home_model->get_locations(array('child' => true));
		 $location_opts = array();
		if(!empty($locations)) {
			foreach ($locations as $key => $value) {
				if($show_records_count_in_search_filters == "Yes") {

					$avail_records_cnt = " (".count($this->home_model->get_student_leads(array('location_slug'=>$value->slug))).")";
				}

				$temp = array(
						'slug'=>$value->slug,
						'name'=>$value->location_name.$avail_records_cnt
					);
				array_push($location_opts, $temp);	
				// $location_opts[$value->slug] = $value->location_name.$avail_records_cnt;
			}
		}
		$data['location_opts'] = $location_opts;
		// $data->location_opts = $location_opts;

 		//Teaching type Options
		$teaching_types = $this->home_model->get_teaching_types();
		 $teaching_type_opts = array();
		foreach ($teaching_types as $key => $value) {
			if($show_records_count_in_search_filters == "Yes") {

				$avail_records_cnt = " (".count($this->home_model->get_student_leads(array('teaching_type_slug'=>$value->slug))).")";
			}

			$temp = array(
						'slug'=>$value->slug,
						'name'=>$value->teaching_type.$avail_records_cnt
					);
				array_push($teaching_type_opts, $temp);
			// $teaching_type_opts[$value->slug] = $value->teaching_type.$avail_records_cnt;
		}

		$data['teaching_type_opts'] = $teaching_type_opts;
		// $data->teaching_type_opts = $teaching_type_opts

		// for($i=0;$i<=1;$)

		// $data = array(
		// 		'course_opts'=>$course_opts,
		// 		'teaching_type_opts'=>$teaching_type_opts,
		// 		'location_opts'=>$location_opts
		// 	);
		$this->serviceResponse($data,'Leads filter data',1); */
		
		$data=array();
		
		$postdata 	= file_get_contents("php://input");
 		$request 	= json_decode($postdata);
		
		//courses
		$query = "SELECT courses.id,courses.name,(SELECT COUNT(DISTINCT(sl.id)) FROM ".TBL_PREFIX."student_leads sl WHERE sl.course_id=courses.id AND sl.status='Opened') as avail_records_cnt  FROM ".TBL_CATEGORIES." courses WHERE courses.is_parent=0 AND courses.status=1";
		$courses = $this->base_model->get_query_result($query);
				
		
		//locations
		$query = "SELECT l.id,l.location_name,(SELECT COUNT(DISTINCT(sl.id)) FROM ".TBL_PREFIX."student_leads sl WHERE sl.location_id=l.id AND sl.status='Opened') as avail_records_cnt FROM ".TBL_LOCATIONS." l WHERE (l.status='Active' OR l.status=1) AND l.parent_location_id != 0 ORDER BY l.sort_order ASC ";	
		$locations=$this->base_model->get_query_result($query);
		
		
			
		//teaching types
		$query = "SELECT t.id,t.teaching_type,(SELECT COUNT(DISTINCT(sl.id)) FROM ".TBL_PREFIX."student_leads sl WHERE sl.teaching_type_id=t.id AND sl.status='Opened') as avail_records_cnt FROM ".TBL_TEACHING_TYPES." t WHERE t.status=1 ORDER BY t.sort_order ASC ";	
		$teaching_types=$this->base_model->get_query_result($query);
		
		$data = array($courses,$locations,$teaching_types);
		
		$this->serviceResponse($data,'Leads Filter Data',1);
 	}

 	// TUTOR DASHBOARD COUNT

 	function tutor_dashboard_post()
 	{
 		$postdata 	= file_get_contents("php://input");
 		$request 	= json_decode($postdata);
 		$user_id 	= $request->user_id;
 		// $user_id = $this->post('user_id');
 		$tutor_dashboard_data = $this->tutor_model->get_tutor_dashboard_data($user_id);
		
		$this->serviceResponse($tutor_dashboard_data,'tutor dashboard data',1);
 	}

 	// Tutor pending messages list
 	function get_tutor_chat_students_list_post()
 	{
 		$postdata 	= file_get_contents("php://input");
 		$request 	= json_decode($postdata);
 		$user_id 	= $request->user_id;
 		$result = array();
 		
 		$type = $request->type;
		if($type=='student'){
			$query = "SELECT DISTINCT from_id,u.username,u.email,u.photo,u.device_id,(SELECT count(*) FROM ".TBL_CHAT." cc WHERE cc.to_id=".$user_id." and cc.student_status=0 and c.from_id=cc.from_id) as messages_count,(SELECT message from ".TBL_CHAT." m where m.from_id=c.from_id order by m.id desc LIMIT 1) as message from ".TBL_CHAT." c,".TBL_USERS." u where c.to_id=".$user_id." and c.student_status=0 and u.id=c.from_id";

		}else{
			$query = "SELECT DISTINCT from_id,u.username,u.email,u.photo,u.device_id,(SELECT count(*) FROM ".TBL_CHAT." cc WHERE cc.to_id=".$user_id." and cc.tutor_status=0 and c.from_id=cc.from_id) as messages_count,(SELECT message from ".TBL_CHAT." m where m.from_id=c.from_id order by m.id desc LIMIT 1) as message from ".TBL_CHAT." c,".TBL_USERS." u where c.to_id=".$user_id." and c.tutor_status=0 and u.id=c.from_id";
		}

 		$data =  $this->base_model->get_query_result($query);
 		if(empty($data)) 
		{
			$response='tutor chat students list';
			$status=0;
		}
		else
		{
			
			$response='tutor chat students list';
			$status=1;
		}

		$this->serviceResponse($data,$response,$status);
 	}

 	// UPDATE MESSAGES READ STATUS

 	function update_chat_status_post(){
 		$postdata = file_get_contents("php://input");
 		$request  = json_decode($postdata);

 		/*$from_id = $request->from_id;
 		$to_id = $request->to_id;*/
 		
 		$type = $request->type;
		if($type=='student'){
			$from_id = $request->tutor_id;
			$to_id   = $request->student_id;
			$update_data['student_status'] = '1';

		}else{
			$from_id = $request->student_id;
			$to_id   = $request->tutor_id;
			$update_data['tutor_status'] = '1';
		}

 		//$update_data['status'] = 1;

 		$where = array('from_id'=>$from_id,'to_id'=>$to_id);


 		if($this->base_model->update_operation($update_data,'chat',$where)){
 			$response = 'status changed successfully';
 			$status = 1;
 		}else{
 			$response = 'status not changed successfully';
 			$status = 0;
 		}

 		$this->serviceResponse(array(),$response,$status);
 	}

 	function get_student_questions_tutors_list_post()
 	{
 		$postdata = file_get_contents("php://input");
 		$request  = json_decode($postdata);
 		$student_id = $request->user_id;
 		$from_limit = $request->from_limit;

 		// $student_id = $this->post('user_id');
 		// $from_limit = $this->post('from_limit');

 		
		$query = "SELECT q.question_id,q.question_text,u.id as user_id,u.username,u.photo,u.device_id,c.name,(select count(*) from ".TBL_BOOKING_ANSWERS." where question_id=q.question_id and student_status='0') as converation_count FROM ".TBL_BOOKING_QUESTIONS." q,".TBL_USERS." u,".TBL_BOOKINGS." b,".TBL_CATEGORIES." c  WHERE q.booking_id IN (SELECT booking_id from ".TBL_BOOKINGS." where student_id=".$student_id.") AND q.question_id IN (SELECT question_id from ".TBL_BOOKING_ANSWERS.") AND b.tutor_id=u.id AND b.booking_id=q.booking_id AND b.course_id=c.id GROUP by q.question_id LIMIT ".$from_limit.",10";
		 		
 		$data =  $this->base_model->get_query_result($query);
 		if(empty($data)) 
		{
			$response='student questions list';
			$status=0;
		}
		else
		{			
			$response='student questions list';
			$status=1;
		}

		$this->serviceResponse($data,$response,$status);

 	}

 	function get_tutor_questions_students_list_post()
 	{
 		$postdata = file_get_contents("php://input");
 		$request  = json_decode($postdata);
 		$tutor_id = $request->tutor_id;

 		$query = "SELECT q.question_id,q.question_text,u.id as user_id,u.username,u.photo,u.device_id,c.name FROM ".TBL_BOOKING_QUESTIONS." q,".TBL_USERS." u,".TBL_BOOKINGS." b,".TBL_CATEGORIES." c  WHERE q.booking_id IN (SELECT booking_id from ".TBL_BOOKINGS." where tutor_id=".$tutor_id.") AND (q.question_id NOT IN (SELECT question_id from ".TBL_BOOKING_ANSWERS.") || q.question_id IN (SELECT question_id from ".TBL_BOOKING_ANSWERS." WHERE tutor_status='0' and to_id=".$tutor_id." and from_id=u.id)) AND b.booking_id=q.booking_id AND u.id=b.student_id AND b.course_id=c.id GROUP by q.question_id";
 		//$this->serviceResponse(array(),$query,1);
 		$data =  $this->base_model->get_query_result($query);
 		if(empty($data)) 
		{
			$response='tutor questions students list';
			$status=0;
		}
		else
		{			
			$response='tutor questions students list';
			$status=1;
		}

		$this->serviceResponse($data,$response,$status);

 	}
	
	//STUDENT
	/***
	*student dashboard
	*return
	***/
	function student_dashboard_post()
 	{
 		$postdata 	= file_get_contents("php://input");
 		$request 	= json_decode($postdata);
 		$user_id 	= $request->user_id;
 		// $user_id = $this->post('user_id');
 		$student_dashboard_data = $this->student_model->get_student_dashboard_data($user_id);
		
		$this->serviceResponse($student_dashboard_data,'student dashboard data',1);
 	}
		
	/***
	**STUDENT BOOKINGS
	**return 
	***/
	function get_student_bookings_post()
	{
		$postdata 	= file_get_contents("php://input");
		$request	=	json_decode($postdata);
		$user_id 	= $request->user_id;
		$booking_type = $request->type;
		// $user_id 	= $this->post('user_id');
		$from_limit = $request->from_limit;
		$status_condition = '';
		if($booking_type!='all'){
			$status_condition = ' AND b.status="'.$booking_type.'"';
		}

		$to_limit = 10;
		
		$bookings 	= $this->base_model->get_query_result('SELECT b.*,u.username as tutor_name,u.photo as tutor_photo,u.phone,u.email,u.device_id,c.name as course_name FROM '.TBL_BOOKINGS.' b,'.TBL_USERS.' u,'.TBL_CATEGORIES.' c WHERE b.tutor_id=u.id AND b.course_id=c.id AND b.student_id="'.$user_id.'" '.$status_condition.' LIMIT '.$from_limit.','.$to_limit.''); 
		
		$this->serviceResponse($bookings,'Student Bookings',1); 
	}
	
	/***
	**GET STUDENT BOOKING STATUS
	**
	***/
	function get_student_booking_status_post()
	{
		$postdata 	= file_get_contents("php://input");
		$request  	= json_decode($postdata);
		$booking_id = $request->booking_id;
		$user_id	= $request->user_id;
		
		$booking_det = $this->base_model->fetch_records_from('bookings', array('booking_id' => $booking_id));
		if(!empty($booking_det)) 
		{
			$booking_det = $booking_det[0];

			if($booking_det->student_id != $user_id) 
			{
				$response = get_languageword('not_authorized');
				$status   = 0; 	
				$this->serviceResponse(array(),$response,$status);
			}

			$booking_status = $booking_det->status;
			$updated_by = getUserType($booking_det->updated_by);

			if($booking_status == "pending") 
			{
				$status_array = array('cancelled_before_course_started' => get_languageword('cancel'), 'called_for_admin_intervention' => get_languageword('claim_for_admin_intervention'));
			}

			if($booking_status == "approved") 
			{
				$status_array = array('cancelled_before_course_started' => get_languageword('cancel'), 'called_for_admin_intervention' => get_languageword('claim_for_admin_intervention'));
			}
				
			if($booking_status == "session_initiated") 
			{
				$status_array = array('running' => get_languageword('start_course'),
				'cancelled_before_course_started'=>get_languageword('cancel'),
				'called_for_admin_intervention'=>get_languageword('claim_for_admin_intervention')
				);
			}

			if($booking_status == "running") 
			{
				$status_array = array('cancelled_when_course_running' => get_languageword('cancel'),
				'called_for_admin_intervention'=>get_languageword('claim_for_admin_intervention'));
			}

			if($booking_status == "completed") 
			{
				$status_array = array('closed' => get_languageword('close'),
				'called_for_admin_intervention'=>get_languageword('claim_for_admin_intervention'));
			}

			$data = array();
			if(!empty($status_array)){
				foreach ($status_array as $key => $value) {
					$tmp = array(
						'status_key'=>$key,
						'status_value'=>$value
					);

					array_push($data, $tmp);
				}
			}
			$this->serviceResponse($data,'Status fields',1);
		} 
		else 
		{
			$response = get_languageword('Booking details not found');
			$status = 0;
			$this->serviceResponse(array(),$response,$status);
		}
	}
	
	/***
	**Get Languages - Profile
	***/
	function get_known_languages_post()
	{
		$lng_opts = $this->db->get_where('languages',array('status' => 'Active'))->result();
		// $options = array();
		// if(!empty($lng_opts))
		// {
		// 	foreach($lng_opts as $row):
		// 		$options[$row->name] = $row->name;
		// 	endforeach;
		// }
		$response='Languages';
		$this->serviceResponse($lng_opts,$response,0);
	}
	
	/***
	*UPDATE STUDENT PERSONAL INFO
	***/
	function update_stpersonal_details_post()
	{
		$postdata 	= file_get_contents("php://input");
		$request  	= json_decode($postdata);
		
		$user_details=array();
		
		$response='';
		$status=0;
		
		$user_data  = $request->userData;
		$user_id 	= $user_data->id;
		
		$first_name = ucfirst(strtolower($user_data->first_name));
		$last_name 	= ucfirst(strtolower($user_data->last_name));
		$username 	=  $first_name.' '.$last_name;
		
		$prev_username = $this->base_model->fetch_value('users', 'username', array('id' => $user_id));
		
		//If user updates the username
		if($prev_username != $username) {
			$slug = prepare_slug($username, 'slug', 'users');
			$inputdata['slug'] =  $slug;
		}
		
		$inputdata['first_name']= $first_name;
		$inputdata['last_name'] = $last_name;
		$inputdata['username'] 	= $username;
		$inputdata['gender'] 	= $user_data->gender;
		$inputdata['dob'] 		= date('Y-m-d',strtotime($user_data->dob));
		$inputdata['website'] 	= $user_data->website;
		$inputdata['facebook'] 	= $user_data->facebook;
		$inputdata['twitter'] 	= $user_data->twitter;
		$inputdata['linkedin'] 	= $user_data->linkedin;
		$inputdata['is_profile_update'] 	= 1;
		
		//student parameters
		$inputdata['skype'] 	= $user_data->skype;
		//student parameters
		
		//tutor parameters
		$inputdata['paypal_email'] 		= $user_data->paypal_email;
		$inputdata['bank_ac_details'] 	= $user_data->bank_ac_details;
		//tutor parameters
		
		$language_of_teaching = json_decode($request->language_of_teaching);
		if(!empty($language_of_teaching))
		$inputdata['language_of_teaching'] = implode(',',$language_of_teaching);
	
		if($this->base_model->update_operation($inputdata,'users', array('id' => $user_id)))
		{
			$user_details = getUserRec($user_id);
			$response = get_languageword('updated_successfully');
			$status   = 1;
		}
		else
		{
			$response = get_languageword('not_updated');
			$status   = 0;
		}
		$this->serviceResponse($user_details,$response,$status);
	}
	
	/***
	*UPDATE STUDENT PROFILE INFO
	***/
	function update_stprofile_details_post()
	{
		$postdata 	= file_get_contents("php://input");
		$request  	= json_decode($postdata);
		
		$user_details=array();
		
		$response='';
		$status=0;
		
		$inputdata['profile'] 				= $request->profile;
		$inputdata['profile_page_title'] 	= $request->profile_page_title;
		$inputdata['seo_keywords'] 			= $request->seo_keywords;
		$inputdata['meta_desc'] 			= $request->meta_desc;
		$inputdata['willing_to_travel'] 	= $request->willing_to_travel;
		$inputdata['qualification'] 		= $request->qualification;
		$inputdata['own_vehicle'] 			= $request->own_vehicle;
		$inputdata['is_profile_update'] 	= 1;
		
		//tutor 
		if(isset($request->user_type) && $request->user_type=='tutor')
		{
			$inputdata['experience_desc'] 		= $request->experience_desc;
			$inputdata['teaching_experience'] 	= $request->teaching_experience;
			$inputdata['duration_of_experience'] = $request->duration_of_experience;
		}
		
		
		$user_id = $request->user_id;
		
		if($this->base_model->update_operation($inputdata,'users', array('id' => $user_id)))
		{
			$user_details = getUserRec($user_id);
			$response = get_languageword('updated_successfully');
			$status   = 1;
		}
		else
		{
			$response = get_languageword('not_updated');
			$status   = 0;
		}
		$this->serviceResponse($user_details,$response,$status);
	}
	
	/***
	*UPDATE TUTOR MANAGE PRIVACY SETTINGS
	***/
	function update_privacy_settings_post()
	{
		$postdata 	= file_get_contents("php://input");
		$request  	= json_decode($postdata);
		
		$user_details=array();
		
		$response='';
		$status=0;
		
		$inputdata['free_demo'] 			= $request->free_demo;
		$inputdata['visibility_in_search'] 	= $request->visibility_in_search;
		$inputdata['show_contact'] 			= $request->show_contact;
		$inputdata['availability_status'] 	= $request->availability_status;
		$inputdata['is_profile_update'] 	= 1;
		
		$user_id = $request->user_id;
		
		if($this->base_model->update_operation($inputdata,'users', array('id' => $user_id)))
		{
			$user_details = getUserRec($user_id);
			$response = get_languageword('updated_successfully');
			$status   = 1;
		}
		else
		{
			$response = get_languageword('not_updated');
			$status   = 0;
		}
		$this->serviceResponse($user_details,$response,$status);
	}
	
	/***
	**Get Countries-My Address
	***/
	function countries_post()
	{
		$countries = $this->base_model->fetch_records_from('country');
		/* $countries_opts = array('' => get_languageword('select_country'));
		if(!empty($countries))
		{
			foreach($countries as $country)
			{
				$countries_opts[$country->phonecode.'_'.$country->nicename]  = $country->nicename." +".$country->phonecode;
			}
		} */
		
		$response='Countries';
		$this->serviceResponse($countries,$response,0);
	}
	
	/***
	*UPDATE STUDENT CONTACT INFO
	***/
	function update_stcontact_details_post()
	{
		$postdata 	= file_get_contents("php://input");
		$request  	= json_decode($postdata);
		
		$user_details=array();
		
		$response='';
		$status=0;
		
		$inputdata['city'] 		= $request->city;
		$inputdata['land_mark'] = $request->land_mark;
		
		// $code_country = explode('_', $request->country);

		$inputdata['country'] 	 = $request->country;
		// $inputdata['phone_code'] = $code_country[0];

		$inputdata['pin_code'] 	= $request->pin_code;
		$inputdata['phone'] 	= $request->phone;	
		$inputdata['is_profile_update'] 	= 1;
		
		if($request->academic_class=='yes')
		$inputdata['academic_class'] 	 = 'yes';
		else
		$inputdata['academic_class'] 	 = 'no';

	
		if($request->non_academic_class=='yes')
		$inputdata['non_academic_class'] = 'yes';
		else
		$inputdata['non_academic_class'] = 'no';	
		
		$user_id = $request->user_id;
		
		if($this->base_model->update_operation($inputdata,'users', array('id' => $user_id)))
		{
			$user_details = getUserRec($user_id);
			$response = get_languageword('updated_successfully');
			$status   = 1;
		}
		else
		{
			$response = get_languageword('not_updated');
			$status   = 0;
		}
		$this->serviceResponse($user_details,$response,$status);
	}
	
	//GET CHAT HISTORY
	function get_chat_history_post()
	{
		$postdata 	= file_get_contents("php://input");
		$request  	= json_decode($postdata);
		
		$from_id 	= $request->sender_id;
		$to_id 	 	= $request->reciever_id;
		
		$from_limit = $request->from_limit;
		$to_limit	= $request->to_limit;
		
		$data = $this->base_model->get_query_result("SELECT * FROM ".$this->db->dbprefix('chat')." WHERE (from_id=".$from_id." AND to_id=".$to_id.") OR (from_id=".$to_id." AND to_id=".$from_id.") ORDER BY id DESC LIMIT ".$from_limit.",".$to_limit." ");
		
		$this->serviceResponse($data,'Chat History',1);
	}
	
	//SEND MESSAGE
	function send_message_post()
	{
		$postdata 	= file_get_contents("php://input");
		$request  	= json_decode($postdata);
		
		$data = array();
		$status=0;
		$response='';
		
		if($request->sender_id > 0 && $request->reciever_id  >0)
		{
			$data['from_id'] = $request->sender_id;
			$data['to_id']	 = $request->reciever_id;
			$data['message'] = $request->message;

			$data['datetime'] = date('Y-m-d H:i:s');
			$device_id = $request->device_id;
			if($request->type=='tutor'){
				$data['student_status'] 	= '0';
				$data['tutor_status'] 	 	= '1';
			}else{
				$data['tutor_status'] 	 	= '0';
				$data['student_status'] 	= '1';
			}
			$chat_id=$this->base_model->insert_operation_id($data,'chat');
			if($chat_id)
			{
				$data = $this->base_model->fetch_records_from('chat',array('id'=>$chat_id));
				if($device_id!=''){
					$this->sendPushNotification($device_id,'Chat Message',$request->message);	
				}
				
				
				$response='Message sent successfully';
				$status=1;
			}
			else
			{
				$response='Message not sent';
				$status=0;
			}
			$this->serviceResponse($data,$response,$status);
		}
		else
		{
			$response='Invalid Operation';
			$status=0;
			$this->serviceResponse(array(),$response,$status);
		}
	}
	
	//GET STUDENT TEACHING TYPES
	function get_student_teaching_types_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);
		$user_id  = $request->user_id;
		
		$teaching_types = $this->base_model->get_query_result("SELECT tt.id, tt.teaching_type,CASE WHEN EXISTS (SELECT stt.teaching_type_id FROM pre_student_prefferd_teaching_types stt WHERE stt.teaching_type_id = tt.id AND stt.student_id=".$user_id.") THEN true ELSE false END AS checked  FROM pre_teaching_types tt WHERE tt.status=1");

		$this->serviceResponse($teaching_types,'teaching types',1);
	}
	
	//GET TUTORS FOR SELECTED COURSE
	function get_course_tutors_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);
		$course_id= $request->course_id;
		
		$data = array();
		
		$adm_approval_cond="";
		if(strcasecmp(get_system_settings('need_admin_for_tutor'), 'yes') == 0) 
		{
        	$adm_approval_cond .= " AND u.admin_approved = 'Yes' ";
        }
		
		
		$query = "SELECT u.* FROM ".TBL_USERS." u INNER JOIN ".TBL_USERS_GROUPS." ug ON ug.user_id=u.id INNER JOIN ".TBL_TUTOR_COURSES." tc ON tc.tutor_id=u.id AND tc.course_id=".$course_id." AND tc.status=1 WHERE u.active=1 AND u.visibility_in_search='1' AND u.is_profile_update=1 AND (u.parent_id=0 OR u.parent_id='') AND ug.group_id=3 ".$adm_approval_cond." GROUP BY u.id ORDER BY u.net_credits DESC ";
		//AND u.admin_approved='No'
		
		$data = $this->base_model->get_query_result($query);
		$this->serviceResponse($data,'course tutors',1);
	}
	
	//FIND TUTORS -
	function get_tutors_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);
		
		$student_id = $request->student_id;
		$from_limit = $request->from_limit;
		$to_limit = 20;
		$course_tbl_join		= "";
        $course_cond 			= "";
		if(isset($request->courses))
		{
			$courses = $request->courses;
			if(!empty($courses))
			{
				$courses=implode(",",$courses);
				$course_tbl_join = " INNER JOIN ".TBL_TUTOR_COURSES." tc ON tc.tutor_id=u.id ";
				$course_cond = " AND tc.course_id IN (".$courses.") AND tc.status=1 ";
			}
		}
		
		$location_tbl_join 		= "";
		$location_cond 			= "";
		if(isset($request->locations))
		{
			$locations = $request->locations;
			if(!empty($locations))
			{
				$locations=implode(",",$locations);
				$location_tbl_join = " INNER JOIN ".TBL_TUTOR_LOCATIONS." tl ON tl.tutor_id=u.id ";
				$location_cond = " AND tl.location_id IN (".$locations.")";
			}
		}
		
		$teaching_type_tbl_join = "";
		$teaching_type_cond 	= "";
		if(isset($request->teaching_types))
		{
			$teaching_types = $request->teaching_types;
			if(!empty($teaching_types))
			{
				$teaching_types=implode(",",$teaching_types);
				$teaching_type_tbl_join = " INNER JOIN ".TBL_TUTOR_TEACHING_TYPES." tt ON tt.tutor_id=u.id ";
				$teaching_type_cond = " AND tt.teaching_type_id IN (".$teaching_types.") ";
			}
		}
		 
		
		
		$data = array();
		
		$adm_approval_cond="";
		if(strcasecmp(get_system_settings('need_admin_for_tutor'), 'Yes') == 0) 
		{
        	$adm_approval_cond .= " AND u.admin_approved = 'Yes' ";
        }
		
		
		$query = "SELECT u.*,f.fav_id FROM ".TBL_USERS." u INNER JOIN ".TBL_USERS_GROUPS." ug ON ug.user_id=u.id ".$course_tbl_join." ".$location_tbl_join." ".$teaching_type_tbl_join." LEFT JOIN ".TBL_PREFIX."student_fav_tutors f ON u.id=f.tutor_id AND f.student_id=".$student_id." WHERE u.active=1 AND u.visibility_in_search='1' AND u.is_profile_update=1 AND (u.parent_id=0 OR u.parent_id='') AND ug.group_id=3 ".$course_cond." ".$location_cond." ".$teaching_type_cond." ".$adm_approval_cond." GROUP BY u.id ORDER BY u.net_credits DESC LIMIT ".$from_limit.",".$to_limit."";
		
		$data = $this->base_model->get_query_result($query);
		$this->serviceResponse($data,'Tutors',1);
	}
	
	//STUDENT add/remove his fav tutor
	function add_fav_tutor_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);
		
		$data 		= array();
		
		$response='';
		$status=0;
		
		$student_id = $request->student_id;
		$tutor_id   = $request->tutor_id;
		
		$fav_id = $request->fav_id;
		if($fav_id > 0)
		{
			if($this->base_model->delete_record_new('student_fav_tutors', array('fav_id' => $fav_id)))
			{
				$response='Tutor removed from your favourites';
				$status=1;
			}
			else
			{
				$response='Tutor not removed from your favourites';
				$status=0;
			}
		} 
		else
		{
			$inputdata = array();
			$inputdata['student_id'] = $student_id;
			$inputdata['tutor_id'] 	 = $tutor_id;
			if($this->base_model->insert_operation($inputdata,'student_fav_tutors'))
			{
				$response='Tutor added to your favourites';
				$status=1;
			}
			else
			{
				$response='Tutor not added to your favourites';
				$status=0;
			}
		}
		
		$course_tbl_join		= "";
        $course_cond 			= "";
		if(isset($request->courses))
		{
			$courses = $request->courses;
			if(!empty($courses))
			{
				$courses=implode(",",$courses);
				$course_tbl_join = " INNER JOIN ".TBL_TUTOR_COURSES." tc ON tc.tutor_id=u.id ";
				$course_cond = " AND tc.course_id IN (".$courses.") AND tc.status=1 ";
			}
		}
		
		$location_tbl_join 		= "";
		$location_cond 			= "";
		if(isset($request->locations))
		{
			$locations = $request->locations;
			if(!empty($locations))
			{
				$locations=implode(",",$locations);
				$location_tbl_join = " INNER JOIN ".TBL_TUTOR_LOCATIONS." tl ON tl.tutor_id=u.id ";
				$location_cond = " AND tl.location_id IN (".$locations.") ";
			}
		}
		
		$teaching_type_tbl_join = "";
		$teaching_type_cond 	= "";
		if(isset($request->teaching_types))
		{
			$teaching_types = $request->teaching_types;
			if(!empty($teaching_types))
			{
				$teaching_types=implode(",",$teaching_types);
				$teaching_type_tbl_join = " INNER JOIN ".TBL_TUTOR_TEACHING_TYPES." tt ON tt.tutor_id=u.id ";
				$teaching_type_cond = " AND tt.teaching_type_id IN (".$teaching_types.") ";
			}
		}
		
		$adm_approval_cond="";
		if(strcasecmp(get_system_settings('need_admin_for_tutor'), 'yes') == 0) 
		{
        	$adm_approval_cond .= " AND u.admin_approved = 'Yes' ";
        }
		
		$query = "SELECT u.*,f.fav_id FROM ".TBL_USERS." u INNER JOIN ".TBL_USERS_GROUPS." ug ON ug.user_id=u.id ".$course_tbl_join." ".$location_tbl_join." ".$teaching_type_tbl_join." LEFT JOIN ".TBL_PREFIX."student_fav_tutors f ON u.id=f.tutor_id AND f.student_id=".$student_id." WHERE u.active=1 AND u.visibility_in_search='1' AND u.is_profile_update=1 AND (u.parent_id=0 OR u.parent_id='') AND ug.group_id=3 ".$course_cond." ".$location_cond." ".$teaching_type_cond." ".$adm_approval_cond." GROUP BY u.id ORDER BY u.net_credits DESC ";
		
		$data = $this->base_model->get_query_result($query);
		$this->serviceResponse($data,$response,$status);
	}
	
	//GET student favourite tutors
	function get_student_fav_tutors_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);
		$student_id = $request->student_id;
		
		$data = array();
		$response='';
		$status=0;
		
		$query = "SELECT f.*,u.id,u.photo,u.availability_status,u.username,u.free_demo,u.teaching_experience,u.duration_of_experience,u.qualification from ".TBL_PREFIX."student_fav_tutors f INNER JOIN ".TBL_PREFIX."users u ON f.tutor_id=u.id WHERE f.student_id=".$student_id."";
		
		$data = $this->base_model->get_query_result($query);
		if(!empty($data))
		{
			$response = 'Your favourite tutors';
			$status=1;
		}
		else
		{
			$response = 'No favourite tutors available';
			$status=0;
		}
		$this->serviceResponse($data,$response,$status);
	}
	
	
	function remove_fav_tutor_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);
		
		$student_id = $request->student_id;
		$fav_id		= $request->fav_id;
		
		$data = array();
		$response='';
		$status=0;
		
		if($this->base_model->delete_record_new('student_fav_tutors', array('fav_id' => $fav_id)))
		{
			$response = 'Tutor removed from your favourites';
			$status=1;
		}
		else
		{
			$response = 'Tutor not removed from your favourites';
			$status=0;
		}
		
		$query = "SELECT f.*,u.id,u.photo,u.availability_status,u.username,u.free_demo,u.teaching_experience,u.duration_of_experience,u.qualification from ".TBL_PREFIX."student_fav_tutors f INNER JOIN ".TBL_PREFIX."users u ON f.tutor_id=u.id WHERE f.student_id=".$student_id."";
		
		$data = $this->base_model->get_query_result($query);
		$this->serviceResponse($data,$response,$status);
	}
	
	//GET TUTOR Profile
	function get_tutor_profile_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);
		$tutor_id = $request->tutor_id;
		
		$data = array();
		$response='';
		$status=0;
		
		/* $adm_approval_cond="";
		if(strcasecmp(get_system_settings('need_admin_for_tutor'), 'yes') == 0) {
        	$adm_approval_cond = ' AND u.admin_approved = "Yes" ';
        } */
		
		$tutor_info_query = "SELECT u.* FROM ".$this->db->dbprefix('users')." u WHERE u.id=".$tutor_id." ";

    	$tutor_details = $this->base_model->get_query_result($tutor_info_query);
		
		
		//Tutoring Courses
    	$tutor_courses_query = "SELECT GROUP_CONCAT(' ', courses.name) AS tutoring_courses FROM ".$this->db->dbprefix('tutor_courses')." tc INNER JOIN ".$this->db->dbprefix('categories')." courses ON courses.id=tc.course_id WHERE tc.tutor_id=".$tutor_id." AND tc.status=1 AND courses.status=1 ORDER BY tc.sort_order ASC";
    	$tutor_details[0]->tutoring_courses = $this->db->query($tutor_courses_query)->row()->tutoring_courses;
		
		//Tutoring Locations
    	$tutor_locations_query = "SELECT GROUP_CONCAT(' ', l.location_name) AS tutoring_locations FROM ".$this->db->dbprefix('tutor_locations')." tl INNER JOIN ".$this->db->dbprefix('locations')." l ON l.id=tl.location_id WHERE tl.tutor_id=".$tutor_id." AND tl.status=1 AND l.status=1 ORDER BY tl.sort_order ASC";
    	$tutor_details[0]->tutoring_locations = $this->db->query($tutor_locations_query)->row()->tutoring_locations;

    	$tutor_locations_query2 = "SELECT l.id,l.slug,l.location_name AS tutoring_locations FROM ".$this->db->dbprefix('tutor_locations')." tl INNER JOIN ".$this->db->dbprefix('locations')." l ON l.id=tl.location_id WHERE tl.tutor_id=".$tutor_id." AND tl.status=1 AND l.status=1 ORDER BY tl.sort_order ASC";
    	$tutor_details[0]->tutoring_locations_data = $this->base_model->get_query_result($tutor_locations_query2);



		
		//Tutor's Gallery
        $tutor_gallery_query = "SELECT image_title, image_name FROM ".$this->db->dbprefix('gallery')." WHERE user_id=".$tutor_id." AND image_status='Active' ORDER BY image_order ASC";
        $tutor_details[0]->tutor_gallery = $this->db->query($tutor_gallery_query)->result();
		
		//Tutor Experience
        $tutor_experience_query = "SELECT company, role, description, from_date, to_date FROM ".$this->db->dbprefix('users_experience')." WHERE user_id=".$tutor_id." ";
        $tutor_details[0]->tutor_experience = $this->db->query($tutor_experience_query)->result();
		
		
		
		$no_of_comments = tutorCommentNumber($tutor_id);
		$tutor_details[0]->no_of_reviews = $no_of_comments;
		
		$tutor_rating = tutorAvgRatingValue($tutor_id);
		$tutor_details[0]->tutor_rating = $tutor_rating;

    	$this->serviceResponse($tutor_details,'Tutor Details',1);
	}
	
	//UPDATE STUDENT TEACHING TYPES
	function update_stteaching_types_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);
		$student_id = $request->student_id;
		
		$teaching_types = $request->teaching_types;
		
		$result=array();
		$data = array();
		$response='';
		$status=0;
		
		foreach($teaching_types as $teaching) 
		{
			$update_data=array();
			$update_data['teaching_type_id'] = $teaching->id;
			$update_data['student_id'] = $student_id;
			$update_data['created_at'] = date('Y-m-d H:i:s');
			array_push($data,$update_data);
			unset($update_data);
		}
		
		$existing_teaching = $this->base_model->fetch_records_from('student_prefferd_teaching_types',array('student_id'=>$student_id));
		if(!empty($existing_teaching))
		{
			$this->base_model->delete_record_new('student_prefferd_teaching_types', array('student_id' => $student_id));
		}
		
		if(!empty($data))
		{
			if($this->db->insert_batch( 'pre_student_prefferd_teaching_types',$data))
			{
				$response = 'teaching types updated';
				$status=1;
			}
			else
			{
				$response = 'teaching types not updated';
				$status=0;
			}
		}
		$this->serviceResponse($result,$response,$status);
	}
	
	//STUDENT MANAGE LOCATIONS
	function get_student_locations_post()
	{
		$postdata 	= file_get_contents("php://input");
		$request	= json_decode($postdata);
		$student_id = $request->user_id;
		
		$parentLocationDetails = $this->db->select('id AS parentLocation_id, location_name AS parentLocation_name')->get_where($this->db->dbprefix( 'locations' ), array('parent_location_id' => 0, 'status' => 'Active'))->result();
		
		foreach($parentLocationDetails as $index => $p) {
		
			$query = "SELECT l.*,(SELECT count(*) FROM ".$this->db->dbprefix('student_locations')." tl,".$this->db->dbprefix( 'users' )." u,".$this->db->dbprefix( 'users_groups' )." ug WHERE (tl.location_id = l.id OR u.location_id = l.id) AND ug.group_id = 2 AND ug.user_id = u.id AND u.id = tl.student_id AND u.active = 1 AND tl.status = '1') AS no_of_student,(SELECT sl.location_id  from ".$this->db->dbprefix('student_locations')." sl WHERE sl.student_id=".$student_id." AND sl.location_id=l.id AND sl.status=1) as checked_id FROM ".$this->db->dbprefix( 'locations' )." l WHERE l.parent_location_id = ".$p->parentLocation_id." AND l.status = 'active'";
			
			$childLocations = $this->db->query($query)->result();
			
			// $locations[$p->parentLocation_name] = $childLocations;
			
			$parentLocationDetails[$index]->sub_locations = $childLocations;		
		}
		
		$response = 'Student Locations';
		$this->serviceResponse($parentLocationDetails,$response,1);
	}
	
	// UPDATE STUDENT LOCATIONS
	function update_student_locations_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);

		$user_id 	= $request->user_id;
		$locations 	= $request->locations;
		
		$response='';
		$status=0;
		
		$update_data = array();
		
		if(!empty($locations)){
			foreach ($locations as  $value) 
			{
				$temp=array();
				$temp = array(
					'student_id'=>$user_id,
					'location_id'=>$value->location_id,
					'created_at'=>date('Y-m-d h:i:s')
				);
				array_push($update_data,$temp);
				unset($temp);
			}
		}
		
		$get_existing_locations  = $this->base_model->fetch_records_from('student_locations',array('student_id'=>$user_id));

		if(!empty($get_existing_locations))
		{
			$where['student_id'] = $user_id;
			$this->base_model->delete_record_new('student_locations',$where);
		}
		
		
		if(!empty($update_data))
		{
			if($this->db->insert_batch('pre_student_locations',$update_data))
			{
				$response = 'Student locations updated';
				$status=1;
			}
			else
			{
				$response = 'Student locations not updated';
				$status=0;
			}
		} 
		$this->serviceResponse(array(),$response,$status);
	}
	
	//STUDENT MANAGE COURSES
	function get_student_courses_post()
	{
		$postdata 	= file_get_contents("php://input");
		$request	= json_decode($postdata);
		$student_id = $request->user_id;
		
		
		$result=array();
		
		$categories = $this->db->query("SELECT id AS parent_course_id,name AS parent_course_name FROM ".TBL_CATEGORIES." WHERE is_parent=1 AND status=1 ")->result();
		
		if(!empty($categories))
		{
			foreach($categories as $index=>$parent):
			
			$query = "SELECT c.id as child_course_id,c.name as child_course_name,(SELECT sc.course_id from ".$this->db->dbprefix('student_preffered_courses')." sc WHERE sc.student_id=".$student_id." AND sc.course_id=c.id AND sc.status=1) as checked_id FROM ".TBL_CATEGORIES." c INNER JOIN ".TBL_COURSE_CATEGORIES." cc on c.id=cc.course_id AND cc.category_id=".$parent->parent_course_id." WHERE c.is_parent=0 AND c.status=1";
			
			$courses = $this->base_model->get_query_result($query);
			
			$categories[$index]->courses = $courses;
			endforeach;
		}
		$result = $categories;
		
		$response = 'Student Courses';
		$this->serviceResponse($result,$response,1);
	}
	
	// UPDATE STUDENT COURSES
	function update_student_courses_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);

		$user_id 	= $request->user_id;
		$courses 	= $request->courses;
		
		$response='';
		$status=0;
		
		$update_data = array();
		
		if(!empty($courses)){
			foreach ($courses as  $value) 
			{
				$temp=array();
				$temp = array(
					'student_id'=>$user_id,
					'course_id'=>$value->course_id,
					'status'=>1,
					'created_at'=>date('Y-m-d h:i:s')
				);
				array_push($update_data,$temp);
				unset($temp);
			}
		}
		
		$get_existing_courses = $this->base_model->fetch_records_from('student_preffered_courses',array('student_id'=>$user_id));

		if(!empty($get_existing_courses))
		{
			$where['student_id'] = $user_id;
			$this->base_model->delete_record_new('student_preffered_courses',$where);
		}
		
		
		if(!empty($update_data))
		{
			if($this->db->insert_batch('pre_student_preffered_courses',$update_data))
			{
				$response = 'Student courses updated';
				$status=1;
			}
			else
			{
				$response = 'Student courses not updated';
				$status=0;
			}
		} 
		$this->serviceResponse(array(),$response,$status);
	}
	
	
	//STUDENT - GET TUTORS FILTER DATA
	function get_filter_data_post()
	{
		$postdata 	= file_get_contents("php://input");
		$request	= json_decode($postdata);
		
		$data=array();

		//courses
		$query = "SELECT courses.id,courses.name,(SELECT COUNT(DISTINCT(tc.id)) FROM ".TBL_PREFIX."tutor_courses tc WHERE tc.course_id=courses.id ) as avail_records_cnt  FROM ".TBL_CATEGORIES." courses WHERE courses.is_parent=0 AND courses.status=1";
		$courses = $this->base_model->get_query_result($query);
				
		
		//locations
		$query = "SELECT l.id,l.location_name,(SELECT COUNT(DISTINCT(tl.id)) FROM ".TBL_PREFIX."tutor_locations tl WHERE tl.location_id=l.id) as avail_records_cnt FROM ".TBL_LOCATIONS." l WHERE (l.status='Active' OR l.status=1) AND l.parent_location_id != 0 ORDER BY l.sort_order ASC ";	
		$locations=$this->base_model->get_query_result($query);
		
		
			
		//teaching types
		$query = "SELECT t.id,t.teaching_type,(SELECT COUNT(DISTINCT(tt.id)) FROM ".TBL_PREFIX."tutor_teaching_types tt WHERE tt.teaching_type_id=t.id) as avail_records_cnt FROM ".TBL_TEACHING_TYPES." t WHERE t.status=1 ORDER BY t.sort_order ASC ";	
		$teaching_types=$this->base_model->get_query_result($query);
		
		$data = array($courses,$locations,$teaching_types);
			
		$response = 'Filter Data';
		$this->serviceResponse($data,$response,1);
	}
	
	
	//SEND MESSAGE 
	function send_lead_message_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);

		$data=array();
		$response='';
		$status=0;
		
		$student_id 	= $request->student_id;
		$student_slug 	= $request->student_slug;
		
		$lead_id 		= $request->lead_id;
		
		$tutor_id 	= $request->tutor_id;
		
		$student_details = $this->home_model->get_student_profile_app($student_slug,$lead_id,$tutor_id);
		
		
		if(!is_array($student_details) || empty($student_details)) 
		{
			$response=$student_details;
			$this->serviceResponse(array(),$response,$status);
		}
		
		$inputdata=array();
		$inputdata['from_user_id']=$tutor_id;
		$credits_for_sending_message = $this->config->item('site_settings')->credits_for_sending_message;
		
		//Check Whether tutor is premium user or not
		if(!is_premium($inputdata['from_user_id'])) 
		{
			$response=get_languageword('please_become_premium_member_to_send_message_to_student');
			$this->serviceResponse(array(),$response,$status);
		}
		
		
		$inputdata['name'] 			= $request->firstName;
		$inputdata['course_slug']	= $student_details[0]->lead_details[0]->course_name;
		$inputdata['email'] 		= $request->email;
		$inputdata['phone'] 		= $request->phone;
		$inputdata['message'] 		= $request->msg;
		
		$to_user_type   			= 'student';		
		$inputdata['to_user_id']    = $student_id;				
		$inputdata['created_at']	= date('Y-m-d H:i:s');
		$inputdata['updated_at']	= $inputdata['created_at'];
		
		$ref = $this->base_model->insert_operation($inputdata, 'messages');
		
		if($ref) 
		{
			//Get Send Message Email Template
			$email_tpl = $this->base_model->fetch_records_from('email_templates', array('template_status' => 'Active', 'email_template_id' => '17'));
			
			$student_rec = getUserRec($inputdata['to_user_id']);

			$from 	= $inputdata['email'];
			$to 	= $student_rec->email;
			$sub 	= get_languageword("Message Received From ")." ".get_languageword('tutor');
			
			$msg='';
			$msg.= '<p>'.get_languageword('Hi ').$student_rec->username.',</p><p>'.get_languageword('You got a message from Tutor Below are the details').'</p><p>
			<strong>'.get_languageword('name').':</strong> '.$inputdata['name'].'</p><p><strong>'.get_languageword('email').':</strong> '.$inputdata['email'].'</p><p><strong>'.get_languageword('phone').':</strong> '.$inputdata['phone'].'</p><p><strong>'.get_languageword('message').':</strong> '.$inputdata['message'].'</p><p>&nbsp;</p>';
			$msg .= "<p>".get_languageword('Thank you')."</p>";
			
			if(!empty($email_tpl)) 
			{
				$email_tpl = $email_tpl[0];
				if(!empty($email_tpl->from_email)) 
				{
					$from = $email_tpl->from_email;
				}
				if(!empty($email_tpl->template_subject)) 
				{
					$sub = $email_tpl->template_subject." ".get_languageword('Tutor');
				}
				if(!empty($email_tpl->template_content)) 
				{
					$msg = "";
					$original_vars  = array($student_rec->username, get_languageword('Tutor'), $inputdata['name'], $inputdata['email'], $inputdata['phone'], $inputdata['course_slug'], $inputdata['message']);
					$temp_vars		= array('___TO_NAME___','___USER_TYPE___','___NAME___', '___EMAIL___', '___PHONE___', '___COURSE___', '___MESSAGE___');
					$msg = str_replace($temp_vars, $original_vars, $email_tpl->template_content);
				}
			}
			
			if(sendEmail($from, $to, $sub, $msg)) 
			{
				//Log Credits transaction data & update user net credits - Start
				$per_credit_value = $this->config->item('site_settings')->per_credit_value;
				
				$log_data = array(
					'user_id' => $inputdata['from_user_id'],
					'credits' => $credits_for_sending_message,
					'per_credit_value' => $per_credit_value,
					'action'  => 'debited',
					'purpose' => 'For Sending Message To Student "'.$student_slug.'" ',
					'date_of_action	' => date('Y-m-d H:i:s'),
					'reference_table' => 'messages',
					'reference_id' => $ref,
				);
				
				log_user_credits_transaction($log_data);

				update_user_credits($inputdata['from_user_id'], $credits_for_sending_message, 'debit');
				//Log Credits transaction data & update user net credits - End
				$response = get_languageword('Your message sent to Student successfully');
				$status=1;
				
				$data = $this->base_model->fetch_records_from('users',array('id'=>$tutor_id));
				$this->serviceResponse($data,$response,$status);
			}
			else
			{
				$response=get_languageword('Your message not sent due to some technical issue Please send message after some time Thankyou');
				$this->serviceResponse(array(),$response,$status);
			}
		}
		else
		{
			$response=get_languageword('Your message not sent due to some technical issue Please send message after some time Thankyou');
			$this->serviceResponse(array(),$response,$status);
		}
	}
	
	//TUTOR VIEW LEAD FIRST TIME
	function tutor_view_lead_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);

		$data=array();
		$response='';
		
		$student_id 	= $request->student_id;
		$student_slug 	= $request->student_slug;
		
		$lead_id 		= $request->lead_id;
		
		$tutor_id 	= $request->tutor_id;
		$result = $this->home_model->view_lead_app($student_slug,$lead_id,$tutor_id);
		if($result->status==true)
		{
			$data = $this->base_model->fetch_records_from('users',array('id'=>$tutor_id));
			$response=$result->message;
			$status=1;
		}
		else
		{
			$response=$result->message;
			$status=0;
		}
		$this->serviceResponse($data,$response,$status);
	}
	
	//GET STUDENT CREDIT TRANSACTION History
	function get_stcredit_trans_history_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);

		$data=array();
		
		$student_id = $request->user_id;
		
		$data = $this->base_model->fetch_records_from('user_credit_transactions',array('user_id'=>$student_id));
		
		$this->serviceResponse($data,'Credit Transaction History',1);
	}
	
	//GET STUDENT COURSE PURCHASES
	function get_stcourse_purchases_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);

		$data=array();
		
		$student_id = $request->user_id;
		
		$data = $this->base_model->get_query_result("SELECT cp.purchase_id,cp.max_downloads,cp.total_downloads,cp.paid_date,c.course_title,u.username as tutor_name FROM ".$this->db->dbprefix('course_purchases')."  cp INNER JOIN ".$this->db->dbprefix('tutor_selling_courses')." c ON cp.sc_id=c.sc_id AND cp.tutor_id=c.tutor_id INNER JOIN ".$this->db->dbprefix('users')." u ON cp.tutor_id=u.id WHERE cp.user_id=".$student_id." ");
		
		$this->serviceResponse($data,'Course Purchases',1);
	}
	
	//GET STUDENT LEADS
	function get_student_leads_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);
		$from_limit = $request->from_limit;
		// $from_limit = 0;
		// 
		$to_limit = 10;
		$data=array();
		
		$student_id = $request->user_id;
		// $student_id = '161';//$request->user_id;
		
		$data = $this->base_model->get_query_result("SELECT sl.*,tt.teaching_type,l.location_name,c.name as course_name FROM ".TBL_PREFIX."student_leads sl INNER JOIN ".TBL_PREFIX."teaching_types tt ON sl.teaching_type_id=tt.id INNER JOIN ".TBL_PREFIX."locations l ON sl.location_id=l.id INNER JOIN ".TBL_PREFIX."categories c ON sl.course_id=c.id WHERE sl.user_id=".$student_id." ORDER BY sl.id DESC LIMIT ".$from_limit.",".$to_limit." ");
		
		$this->serviceResponse($data,'Student Leads',1);
	}
	
	//Student update his lead status
	function update_student_lead_status_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);

		$data=array();
		$response='';
		$status=0;
		
		$student_id 	= $request->user_id;
		$lead_id 		= $request->lead_id;
		$lead_status 	= $request->lead_status;
		
		$update_data  = array();
		$update_data['status']  	= $lead_status;
		$update_data['updated_at'] = date('Y-m-d H:i:s'); 
		
		if($this->base_model->update_operation($update_data,'student_leads',array('id'=>$lead_id)))
		{
			$response='Lead status updated';
			$status=1;
		}
		else
		{
			$response='Lead status not updated';
			$status=0;
		}
		$this->serviceResponse($data,$response,$status);
	}
	
	//GET DATA FOR STUDENT TO POST LEAD
	function get_post_lead_data_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);

		$data=array();
		
		
		//location options
		$locations = $this->home_model->get_locations($params = array('child' => 1));
		
		//Course Options
		$courses = $this->home_model->get_courses();
		
		//Teaching type Options
		$teaching_types = $this->base_model->fetch_records_from('teaching_types');
		
		$data['locations'] 		= $locations;
		$data['courses'] 		= $courses;
		$data['teaching_types'] = $teaching_types;
		
		$this->serviceResponse($data,'required data for post Lead',0);
	}
	
	function post_lead_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);

		$data=array();
		$response='';
		$status=0;
		
		$lead_data=$request->lead_data;
		
		$input_data=array();
		$input_data['user_id'] 		= $request->user_id;
		
		$input_data['location_id']  = $lead_data->location_id;
		$input_data['course_id']    = $lead_data->course_id;
		$input_data['teaching_type_id']		= $lead_data->teaching_type_id;
		$input_data['priority_of_requirement']= $lead_data->priority_of_requirement;
		$input_data['present_status']		= $lead_data->present_status;
		$input_data['duration_needed']		= $lead_data->duration_needed;
		$input_data['budget']				= $lead_data->budget;
		if($lead_data->budget != '')
		{
			$input_data['budget_type']		= $lead_data->budget_type;
		}
		$input_data['title_of_requirement']	= $lead_data->title_of_requirement;
		$input_data['requirement_details']	= $lead_data->requirement_details;
		$input_data['created_at']=date("Y-m-d H:i:s");
		$input_data['updated_at']=$input_data['created_at'];
		
		
		$is_duplicate = $this->base_model->fetch_records_from('student_leads', array('location_id' => $input_data['location_id'], 'course_id' => $input_data['course_id'], 'teaching_type_id' => $input_data['teaching_type_id'], 'budget' => $input_data['budget'], 'budget_type' => $input_data['budget_type'], 'priority_of_requirement' => $input_data['priority_of_requirement'], 'status' => 'Opened','user_id'=>$input_data['user_id']));
		
		if(count($is_duplicate) > 0) 
		{
			$response='You have already posted this lead';
			$this->serviceResponse($data,$response,0);
		}
		
		if($this->base_model->insert_operation($input_data, 'student_leads'))
		{
			$response='Lead posted successfully';
			$status=1;
		}
		else
		{
			$response='Lead not posted';
			$status=0;
		}
		$this->serviceResponse($data,$response,$status);
	}
	
	function change_password_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);

		$data=array();
		$response='';
		$status=0;
		
		$password_data = $request->data;
		
		$current_password 	  = $password_data->current_password;
		$new_password 		  = $password_data->new_password;
		$new_confirm_password = $password_data->new_confirm_password;
		
		$identity = $request->email;
		$change = $this->ion_auth->change_password($identity, $current_password, $new_password);
		
		if($change)
		{
			$response='Password changed successfully';
			$status=1;
		}
		else
		{
			$response = strip_tags($this->ion_auth->errors());
			$status=0;
		}	
		$this->serviceResponse($data,$response,$status);
	}
	
	//BOOKINGS-QUESTIONNING 
	//GET QUESTIONS
	function get_booking_questions_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);
		
		// $request = (object) $this->post();
		$data=array();
		$response='';
		$status=0;
		
		$booking_id = $request->booking_id;
		
		$student_id = $request->student_id;
		$student_record = getUserRec($student_id);
		$img = URL_UPLOADS_PROFILES_STUDENT_MALE_DEFAULT_IMAGE;
		
		if($student_record->photo != '' && file_exists('assets/uploads/profiles/thumbs/'.$student_record->photo))
		{
			$img = base_url()."assets/uploads/profiles/thumbs/".$student_record->photo;
		}
		
		$records_from = $request->records_from;
		$limit = $request->limit;
		
		$query = "SELECT q.*,(SELECT COUNT(*) FROM ".TBL_PREFIX."booking_answers a WHERE q.question_id=a.question_id ) as chat_count FROM ".TBL_PREFIX."booking_questions q WHERE q.booking_id=".$booking_id." ORDER BY q.question_id DESC LIMIT ".$records_from.",".$limit." ";
		
		$data = $this->base_model->get_query_result($query);
		
		if(!empty($data))
		{
			
			//for student service start
			if(isset($request->tutor_id))
			{
				$tutor_id = $request->tutor_id;
				
				$tutor_record = getUserRec($tutor_id);
				$tutor_img = URL_UPLOADS_PROFILES_TUTOR_MALE_DEFAULT_IMAGE;
				
				if($tutor_record->photo != '' && file_exists('assets/uploads/profiles/thumbs/'.$tutor_record->photo))
				{
					$tutor_img = base_url()."assets/uploads/profiles/thumbs/".$tutor_record->photo;
				}
			}
			else
			{
				$tutor_img='';
			}
			
			//for student service end
		
			foreach($data as $r):
				$r->imageFullUrl = $img;
				$r->tutorImageFullUrl = $tutor_img;
			endforeach;
			
			$response='Questions found';
			$status=1;
		}
		else
		{
			$response='No questions found';
			$status=0;
		}
		$this->serviceResponse($data,$response,$status);
	}
	
	//BOOKINGS-QUESTIONNING 
	//GET QUESTION - CONVERSATION
	function get_question_conversation_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);
		// $request = (object) $this->post();
		$data=array();
		$response='';
		$status=0;
		
		$question_id = $request->question_id;
		
		//$student_id  = $request->student_id;
		//$tutor_id 	 = $request->tutor_id;
		
		$from_limit = $request->from_limit;
		$to_limit   = $request->to_limit;
		
		$query = "SELECT a.*,f.photo as from_image,t.photo as to_image FROM ".TBL_PREFIX."booking_answers a INNER JOIN ".TBL_PREFIX."users f ON a.from_id=f.id INNER JOIN ".TBL_PREFIX."users t ON a.to_id=t.id WHERE a.question_id=".$question_id." ORDER BY a.answer_id DESC LIMIT ".$from_limit.",".$to_limit." ";
		
		$data = $this->base_model->get_query_result($query);
		
		if(!empty($data))
		{
			$response='Conversations found';
			$status=1;
		}
		else
		{
			$response='No Conversations found';
			$status=0;
		}
		$this->serviceResponse($data,$response,$status);
	}
	
	//BOOKINGS-QUESTIONNING 
	//REPLY TO QUESTION
	function answer_to_question_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);
		// $request = (object) $this->post();
		$data=array();
		$response='';
		$status=0;
		
		$temp = array();
		$temp['question_id'] = $request->question_id;
		$temp['from_id'] 	 = $request->sender_id;
		$temp['to_id'] 		 = $request->receiver_id;
		$temp['message'] 	 = $request->message;
		if($request->type=='tutor'){
			$temp['student_status'] 	= '0';
			$temp['tutor_status'] 	 	= '1';
		}else{
			$temp['tutor_status'] 	 	= '0';
			$temp['student_status'] 	= '1';
		}
		
		$temp['datetime'] 	 = date('Y-m-d H:i:s');
		$device_id = $request->device_id;
		$answer_id = $this->base_model->insert_operation_id($temp,'booking_answers');
		
		if($answer_id)
		{
			$img='';
			$user = getUserRec($request->sender_id);
			if(!empty($user))
			{
				if($user->user_belongs_group==2) 
				{
					$img = URL_UPLOADS_PROFILES_STUDENT_MALE_DEFAULT_IMAGE;
				}
				else
				{
					$img = URL_UPLOADS_PROFILES_TUTOR_MALE_DEFAULT_IMAGE;
				}
				
				if($user->photo != '' && file_exists('assets/uploads/profiles/thumbs/'.$user->photo))
				{
					$img = base_url()."assets/uploads/profiles/thumbs/".$user->photo;
				}
			}
			$query = "SELECT a.* FROM ".TBL_PREFIX."booking_answers a WHERE a.answer_id=".$answer_id." ";
		
			$data = $this->base_model->get_query_result($query);
			
			if(!empty($data))
			{
				$data[0]->img = $img;

			}
			if($device_id!=''){
				$this->sendPushNotification($device_id,'Answer',$request->message);
			}
			
			$response='answer posted successfully';
			$status=1;
		}
		else
		{
			$response='Answer not posted';
			$status=0;
		}
		$this->serviceResponse($data,$response,$status);
	}

	// UPDATE STATUS IN ANSWERS WHEN STUDENT VIEWS THE ANSWER FROM THE TUTOR
	function update_answer_status_post(){
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);

		$question_id = $request->question_id;
		$type = $request->type;
		if($type=='student'){
			$from_id = $request->tutor_id;
			$to_id   = $request->student_id;
			$update_data['student_status'] = '1';

		}else{
			$from_id = $request->student_id;
			$to_id   = $request->tutor_id;
			$update_data['tutor_status'] = '1';
		}
		
		$where = array('question_id'=>$question_id,'from_id'=>$from_id,'to_id'=>$to_id);
		
		if($this->base_model->update_operation($update_data,'booking_answers',$where)){
			$response = 'viewed status changed successfully';
			$status  = 1;
		}else{
			$response = 'viewed status not changed successfully';
			$status  = 0;
		}  

		$this->serviceResponse(array(),$response,$status);
	}
	
	//BOOKINGS-QUESTIONNING 
	//POST QUESTION
	function add_question_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);
		// $request = (object) $this->post();
		$data=array();
		$response='';
		$status=0;
		
		$temp = array();
		$temp['booking_id'] 	= $request->booking_id;
		$temp['question_text']	= $request->question_text;
		$temp['datetime']		= date('Y-m-d H:i:s');
		
		$question_id = $this->base_model->insert_operation_id($temp,'booking_questions');
		
		if($question_id)
		{
			
			$student_id = $request->student_id;
			$student_record = getUserRec($student_id);
			$img = URL_UPLOADS_PROFILES_STUDENT_MALE_DEFAULT_IMAGE;
			
			if($student_record->photo != '' && file_exists('assets/uploads/profiles/thumbs/'.$student_record->photo))
			{
				$img = base_url()."assets/uploads/profiles/thumbs/".$student_record->photo;
			}
		
			$query = "SELECT q.*,(SELECT COUNT(*) FROM ".TBL_PREFIX."booking_answers a WHERE q.question_id=a.question_id ) as chat_count FROM ".TBL_PREFIX."booking_questions q WHERE q.question_id=".$question_id." ";
		
			$data = $this->base_model->get_query_result($query);
			
			if(!empty($data))
			{
				
				//for student service start
			if(isset($request->tutor_id))
			{
				$tutor_id = $request->tutor_id;

				$device_id = '';
				
				$tutor_record = getUserRec($tutor_id);
				$tutor_img = URL_UPLOADS_PROFILES_TUTOR_MALE_DEFAULT_IMAGE;
				$device_id = $tutor_record->device_id;
				if($tutor_record->photo != '' && file_exists('assets/uploads/profiles/thumbs/'.$tutor_record->photo))
				{

					$tutor_img = base_url()."assets/uploads/profiles/thumbs/".$tutor_record->photo;
				}
			}
			else
			{
				$tutor_img='';
			}
			
			//for student service end
			
				foreach($data as $d):
					$d->imageFullUrl = $img;
					$d->tutorImageFullUrl = $tutor_img;
				endforeach;
			}
			if($device_id!=''){
				$this->sendPushNotification($device_id,'Question',$request->question_text);	
			}
			
		
			$response='answer posted successfully';
			$status=1;
		}
		else
		{
			$response='Answer not posted';
			$status=0;
		}
		$this->serviceResponse($data,$response,$status);
	}
	
	
	//from peter service
	function credit_conversion_request_post()
	{
		$postdata 	= file_get_contents("php://input");
		$request 	= json_decode($postdata);
		$user_id    = $request->user_id;
		// $user_id 	= $this->post('user_id');
		$admin_money_transactions  = $this->base_model->fetch_records_from('admin_money_transactions',array('user_id'=>$user_id));
		$this->serviceResponse($admin_money_transactions,'Convesrion Request',1);

	}
	
	// BOOK TUTOR

	function book_tutor_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);

		$student_id 		= $request->student_id;
		$tutor_id   		= $request->tutor_id;
		$tutor_slug   		= $request->tutor_slug;
		$course_slug		= $request->course_slug;
		$course_id			= $request->course_id;



		//Check Whether student is premium user or not
		if(!is_premium($student_id)) {

			$response = get_languageword('please_become_premium_member_to_book_tutor');
			$this->serviceResponse(array(),$response,0);

		}

		 $course_details = $this->home_model->get_tutor_course_details($course_slug, $tutor_id);

		 // $this->serviceResponse($course_details,$student_id,1);
		
		//Check whether Tutor teaches the course or not
		if(empty($course_details)) {

			$response = get_languageword('no_course_details_found');
			$this->serviceResponse(array(),$response,0);

		}

		
		$course_id 				= $course_details->course_id;
		$fee 					= $course_details->fee;


		//Check If student has sufficient credits to book tutor
		if(!is_eligible_to_make_booking($student_id, $fee)) {

			$response = get_languageword("you_do_not_have_enough_credits_to_book_the_tutor_Please_get_required_credits_here");
			$this->serviceResponse(array(),$response,0);
		}

		// $this->serviceResponse($course_details,$student_id,1);

		$start_date  			= date('Y-m-d', strtotime($request->start_date));
		$time_slot   			= $request->time_slot;

		//Check If student already booked the tutor on the same slot and it is not yet approved by tutor
		if($this->home_model->is_already_booked_the_tutor($student_id, $tutor_id, $course_id, $start_date, $time_slot)) {

			$response = get_languageword('you_already_booked_this_tutor_and_your_course_not_yet_completed');
			$this->serviceResponse(array(),$response,0);
		}

		//Check If selected time-slot is available
		if(empty($course_details->time_slots) || !$this->home_model->is_time_slot_avail($tutor_id, $course_id, $start_date, $time_slot)) {

			$response = get_languageword('time_slot_not_available');
			$this->serviceResponse(array(),$response,0);
		}


		$content 				= $course_details->content;
		$duration_value 		= $course_details->duration_value;
		$duration_type 			= $course_details->duration_type;
		$per_credit_value 		= $course_details->per_credit_value;
		$days_off 				= $course_details->days_off;

		$preferred_location 	= ($request->teaching_type == "willing-to-travel") ? $request->prefered_location : $request->teaching_type;
		$message   				= $request->message;

		if($duration_type == "hours") {

			$formatted  = str_replace(':', '.', $time_slot);
			$time 	    = explode('-', str_replace(' ', '', $formatted));

			$start_time = number_format($time[0],2);
			$end_time   = number_format($time[1],2);

			$total_time = $end_time - $start_time;

			if($total_time >= 1) {

				$days = round($duration_value / $total_time);

			} else {

				$total_time = (int)(explode('.', number_format($total_time,2))[1]);
				$days = round($duration_value / ($total_time/60));
			}

			$end_date = date("Y-m-d", strtotime($start_date.'+'.$days.' days'));

		} else {

			$end_date = date("Y-m-d", strtotime($start_date.'+'.$duration_value.' '.$duration_type));
		}

		$end_date = date("Y-m-d", strtotime($end_date.'-1 days'));

		$admin_commission   	= get_system_settings('admin_commission_for_a_booking');
		$admin_commission_val   = round($fee * ($admin_commission / 100));

		$created_at   		= date('Y-m-d H:i:s');
		$updated_at   		= $created_at;
		$updated_by   		= $student_id;


		$inputdata	=	array(
								'student_id'			=> $student_id,
								'tutor_id'				=> $tutor_id,
								'course_id'				=> $course_id,
								'content'				=> $content,
								'duration_value'		=> $duration_value,
								'duration_type'			=> $duration_type,
								'fee'					=> $fee,
								'per_credit_value'		=> $per_credit_value,
								'start_date'			=> $start_date,
								'end_date'				=> $end_date,
								'time_slot'				=> $time_slot,
								'days_off'				=> $days_off,
								'preferred_location'	=> $preferred_location,
								'message'				=> $message,
								'admin_commission'		=> $admin_commission,
								'admin_commission_val'	=> $admin_commission_val,
								'created_at'			=> $created_at,
								'updated_at'			=> $updated_at,
								'updated_by'			=> $updated_by
							);

		$ref = $this->base_model->insert_operation($inputdata, 'bookings');

		if($ref > 0) {

			//Log Credits transaction data & update user net credits - Start
			$log_data = array(
							'user_id' => $student_id,
							'credits' => $fee,
							'per_credit_value' => $per_credit_value,
							'action'  => 'debited',
							'purpose' => 'Slot booked with the Tutor "'.$tutor_slug.'" and Booking Id is '.$ref,
							'date_of_action	' => date('Y-m-d H:i:s'),
							'reference_table' => 'bookings',
							'reference_id' => $ref,
						);

			log_user_credits_transaction($log_data);

			update_user_credits($student_id, $fee, 'debit');
			//Log Credits transaction data & update user net credits - End


			//Email Alert to Tutor - Start
				//Get Tutor Booking Success Email Template
				$email_tpl = $this->base_model->fetch_records_from('email_templates', array('template_status' => 'Active', 'email_template_id' => '5'));

				if(!empty($email_tpl)) {

					$email_tpl = $email_tpl[0];

					$student_rec = getUserRec($student_id);
					$tutor_rec 	 = getUserRec($tutor_id);


					if(!empty($email_tpl->from_email)) {

						$from = $email_tpl->from_email;

					} else {

						$from 	= get_system_settings('Portal_Email');
					}

					$to 	= $tutor_rec->email;

					if(!empty($email_tpl->template_subject)) {

						$sub = $email_tpl->template_subject;

					} else {

						$sub = get_languageword("Booking Request From Student");
					}

					if(!empty($email_tpl->template_content)) {

						$original_vars  = array($tutor_rec->username, $student_rec->username, $course_slug, $start_date." & ".$time_slot, $preferred_location, '<a href="'.URL_AUTH_LOGIN.'">'.get_languageword('Login Here').'</a>');
						$temp_vars		= array('___TUTOR_NAME___', '___STUDENT_NAME___', '___COURSE_NAME___', '___DATE_TIME___', '___LOCATION___', '___LOGINLINK___');
						$msg = str_replace($temp_vars, $original_vars, $email_tpl->template_content);

					} else {

						$msg = get_languageword('please')." <a href='".URL_AUTH_LOGIN."'> ".get_languageword('Login Here')."</a> ".get_languageword('to view the booking details');
						$msg .= "<p>".get_languageword('Thank you')."</p>";
					}

					sendEmail($from, $to, $sub, $msg);
				}
			//Email Alert to Tutor - End

			$response = get_languageword('your_slot_with_the_tutor_booked_successfully_Once_tutor_approved_your_booking and_initiated_the_session_you_can_start_the_course_on_the_booked_date');
			$this->serviceResponse(array(),$response,1);

		} else {

			$response = get_languageword('your_slot_with_the_tutor_not_booked_you_can_send_message_to_the_tutor');
			$this->serviceResponse(array(),$response,1);

		}

	}
	
	// REVIEWS OF TUTOR

	function reviews_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);

		$tutor_id  = $request->tutor_id;
		// $tutor_id  = $this->post('tutor_id');

		$reviews = $this->base_model->get_query_result("SELECT u.username,u.photo,c.name,r.* FROM ".TBL_PREFIX."tutor_reviews r,".TBL_PREFIX."users u,".TBL_PREFIX."categories c,".TBL_PREFIX."bookings b WHERE r.tutor_id=".$tutor_id." and r.status='Approved' and r.student_id=u.id and r.booking_id=b.booking_id and b.course_id=c.id");
		$this->serviceResponse($reviews,'Reviews',1);
	}

	// TUTOR EXPERIENCE 

	function tutor_experience_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);

		$user_id = $request->user_id;

		$experiences = $this->base_model->fetch_records_from('users_experience',array('user_id'=>$user_id));
		$this->serviceResponse($experiences,'Tutor Experience',1);  
	}
	
	// ADD OR EDIT EXPERIENCE

	function add_experience_post()
	{
		$postdata 		= file_get_contents("php://input");
		$request  		= json_decode($postdata);
		$user_id 		= $request->user_id;
		$record_id 		= $request->record_id;
		$company 		= $request->company;
		$role 			= $request->role;
		$description 	= $request->description;
		$from_date 		= $request->from_date;
		$to_date 		= $request->to_date;
		
		$inputdata['user_id'] 		= $user_id;
		$inputdata['company'] 		= $company;
		$inputdata['role'] 			= $role;
		$inputdata['description'] 	= $description;
		$inputdata['from_date'] 	= $from_date;
		$inputdata['to_date'] 		= $to_date;
		$inputdata['updated_at'] 	= date('Y-m-d h:i:s');
		
		if($record_id!=''){
			
			$where['record_id'] =   $record_id;

			if($this->base_model->update_operation($inputdata, 'users_experience',$where)){
					$response = get_languageword('user experience updated successfully');
					$status = 1;
			}else{
				$response = get_languageword('user experience not updated');
					$status = 0;
			}
		}else{

			$inputdata['created_at'] 	= date('Y-m-d h:i:s');
			if($this->base_model->insert_operation($inputdata, 'users_experience')){
				$response = get_languageword('user experience added successfully');
					$status = 1;
			}else{
				$response = get_languageword('user experience not added');
					$status = 0;
			}
		}

		$this->serviceResponse(array(),$response,$status);

	}
	
	
	function get_certificates_tutors_post()
	{
		$postdata = file_get_contents("php://input");
		$request  = json_decode($postdata);
		$user_id = $request->user_id;
		$certificates = $this->base_model->fetch_records_from('certificates', array('certificate_for' => 'tutors', 'status' => 'Active'));
		$user_uploads = $this->base_model->fetch_records_from('users_certificates', array('user_id' => $user_id));
		$user_uploads_arr = array();
		if(!empty($user_uploads))
		{  
			// foreach($certificates as $index=>$value){
				foreach($user_uploads as $up)
				{
				
				// if($up->admin_certificate_id==$value->certificate_id){
					$user_uploads_arr[$up->admin_certificate_id] = base_url().'assets/uploads/certificates/'.$up->certificate_name;
					//$user_uploads_arr[$up->admin_certificate_id] = $up->certificate_name;
				// }
			// }
		  }
		}
		
		 $data = array(
			'certificates'=>$certificates,
			'user_uploads_arr'=>$user_uploads_arr
		); 
		
		$this->serviceResponse($data,'Certificate',1);
	}
	
	
	function certificates_upload_post()
	{
		
		if(!empty($_FILES['userfile']['name']))
		{
		
			$tmpFilePath = $_FILES['userfile']['tmp_name'];
			$ext = pathinfo($_FILES['userfile']['name'], PATHINFO_EXTENSION);
			$user_id = $this->post('user_id');
			$i = $this->post('certificate_id');
			if($i==0){
				$new_name = $user_id.'_'.str_replace(' ','_',rand().'_'.$_FILES['userfile']['name']);
				$user_image['certificate_type']	= 'other';
			}else{
				$new_name = $user_id.'_'.$i.'.'.$ext;
				$user_image['certificate_type']		= 'admin';
			}
			
			
			
			
			$filePath = './assets/uploads/certificates/'.$new_name;
			if(move_uploaded_file($tmpFilePath, $filePath))
			{
				/* if(in_array(strtolower($ext), array('jpg', 'png', 'gif', 'jpeg')))
				{
					$this->create_thumbnail($filePath,'./assets/uploads/certificates/thumbs/','40','40');
				} */
				
				$user_image['admin_certificate_id'] = $i;
				$user_image['user_id']				= $user_id;
				if(isset($this->config->item('site_settings')->need_admin_for_tutor) && $this->config->item('site_settings')->need_admin_for_tutor == 'yes')
				$user_image['admin_status']			= 'Pending';
				else
				$user_image['admin_status']			= 'Approved';
				
				$user_image['certificate_name']		= $new_name;
				$user_image['file_type']		= $ext;
				
				$existed = $this->base_model->fetch_records_from('users_certificates',
											array('admin_certificate_id'=>$i,
											'user_id'=>$user_id,'certificate_type'=>'admin'));
				if(count($existed)>0)
				{
					$whr['user_certificate_id']			= $existed[0]->user_certificate_id;
					$this->base_model->update_operation($user_image,'users_certificates',$whr);
				}
				else 
				{
				$this->base_model->insert_operation($user_image,'users_certificates');	
				}
				$data = array('path'=>base_url().'/assets/uploads/certificates/'.$new_name);
				$response = 'uploaded successfully';
				$status = 1;
			}else{
				$data = array('path'=>'');
				$response = 'Unable to upload';
				$status = 0;
			}
		}
				
			
			$this->serviceResponse($data,$response,$status);
	}
	
	// TEST FILE UPLOAD
	
	function test_file_upload_post()
	{
			$response = 'Saved '.$_FILES['userfile']['name'];
			$status = 1;
			$image 	= $_FILES['userfile']['name'];
			if(!empty($_FILES['userfile']['name'])){
						
					$ext = pathinfo($image, PATHINFO_EXTENSION);			
					//$file_name = rand() .'.'. $ext;
					$file_name = 'peterJohn' .'.'. $ext;
					$config['upload_path'] 		= 'assets/uploads/profiles/';//URL_PUBLIC_UPLOADS_PROFILES;
					$config['allowed_types'] 	= '*';
					$config['overwrite'] 		= true;
					$config['file_name']        = $file_name; 
					
					$this->load->library('upload', $config);
					$this->upload->initialize($config);
					
					if ($this->upload->do_upload()) {
						
						//$update_data['photo'] = $file_name;
						//$where['id'] 		= $id;
						//$this->base_model->update_operation($update_data,'users', $where);
						
						$status = 1;
						$response = 'User Details with image is uploaded';	
					}
					else {
						
						$status = 0;
						$response = strip_tags($this->upload->display_errors());	
					}
			}
			
			$this->serviceResponse(array('path'=>URL_PUBLIC_UPLOADS_PROFILES),$response,$status);
	}
	
	// send push notifications

	function sendPushNotification($device_id,$title,$message)
	{
		if($device_id=='' || $title=='' || $message==''){
			return false;
		}

		// SEND PUSH NOTIFICATION IF IT IS ENABLE
		if($this->config->item('site_settings')->one_signal_push_notifications=='yes'){
						
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
					
			$this->load->library('OneSignalPush');	
			$gcpm = new OneSignalPush();
			$gcpm->setDevices($device_id);
			$res = $gcpm->send($message,$data);
			return $res;
		}else{
			return false;
		}
	}

	function test_course_post()
	{
		//$postdata = file_get_contents("php://input");
    	//$request = json_decode($postdata);

		// $category_id = $request->category_id;
		$category_id = $this->post('category_id');
		$courses = $this->base_model->fetch_records_from('categories',array('categories'=>$category_id));

		$this->serviceResponse($courses,'Courses',1);
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
