<?php
class Virtual_class extends MY_Controller{
	
	var $redirect_url;
	var $bbb_config;
		
	function __construct()
	{		
		parent::__construct();
		$this->load->model('home_model');
		$this->data['my_profile'] = getUserRec();
		if ( ! $this->ion_auth->logged_in() ) {
			safe_redirect('auth/login', get_languageword('Wrong operation. Please login to access this feature.'), 'error');
		}
		$this->redirect_url = 'enquiries/approved'; // Student
		if( $this->ion_auth->is_tutor() ) {
			$this->redirect_url = 'student-enquiries/approved'; // Tutor
		}
		
		$field_values = $this->db->get_where('system_settings_fields',array('type_id' => BIGBLUEBUTTON))->result();
		$this->bbb_config = array();
		if( ! empty( $field_values ) ) {
			foreach($field_values as $value) {
				if( $value->field_output_value != '') {
					$this->bbb_config[ $value->field_key ] = $value->field_output_value;
				}
			}
		}
		
		if ( empty( $this->bbb_config ) ) {
			safe_redirect($this->redirect_url, get_languageword('Bigbluebutton not configured correctly. Please contact administrator.'), 'error');
		}
	}

	public function index()
	{
		safe_redirect($this->redirect_url, get_languageword('Wrong operation. Please select a meeting to start.'), 'error');
	}
	
	public function create_meeting($meeting_id='',$meeting_name='',$dueration=0)
	{
		$this->load->library('bigbluebutton', $this->bbb_config);
		if($meeting_id==''){
			$meeting_id = guid();	
		}
		
		if($meeting_name==''){
			$meeting_name = $this->config->item('site_settings')->site_name;	
		}
		// Instatiate the BBB class:
		$bbb = new BigBlueButton($this->bbb_config);

		//echo "<pre>yo boy..."; print_r($bbb); die();

		/* ___________ CREATE MEETING w/ OPTIONS ______ */
		/* 
		*/
		$logoutUrl = URL_STUDENT_ENQUIRIES . '/running';
		if( $this->ion_auth->is_tutor() ) { // Tutor
			$logoutUrl = URL_TUTOR_STUDENT_ENQUIRIES . '/running';
		}
		$creationParams = array(
			'meetingId' => $meeting_id, 					// REQUIRED
			'meetingName' => $meeting_name, 	// REQUIRED
			'attendeePw' => 'ap', 					// Match this value in getJoinMeetingURL() to join as attendee.
			'moderatorPw' => 'mp', 					// Match this value in getJoinMeetingURL() to join as moderator.
			'welcomeMsg' => '', 					// ''= use default. Change to customize.
			'dialNumber' => '', 					// The main number to call into. Optional.
			'voiceBridge' => '', 					// PIN to join voice. Optional.
			'webVoice' => '', 						// Alphanumeric to join voice. Optional.
			'logoutUrl' => $logoutUrl, 						// Default in bigbluebutton.properties. Optional.
			'maxParticipants' => '-1', 				// Optional. -1 = unlimitted. Not supported in BBB. [number]
			'record' => 'false', 					// New. 'true' will tell BBB to record the meeting.
			'duration' => $dueration, 						// Default = 0 which means no set duration in minutes. [number]
			//'meta_category' => '', 				// Use to pass additional info to BBB server. See API docs.
		);

		// Create the meeting and get back a response:
		$itsAllGood = true;
		try{
			$result = $bbb->createMeetingWithXmlResponseArray($creationParams);
			
			}
		catch(Exception $e){
			echo 'Caught exception: ', $e->getMessage(), "\n";
			$itsAllGood = false;
		}

		if($itsAllGood == true){
			if($result == null){
				echo "Failed to get any response. Maybe we can't contact the BBB server.";
			}	
			else{ 
				// We got an XML response, so let's see what it says:
	
				if($result['returncode'] == 'SUCCESS'){
					// Then do stuff ...
					//echo "<p>Meeting succesfullly created.</p>";
					return $meeting_id;
				}
				else{
					//echo "<p>Meeting creation failed.</p>";
					return FALSE;
				}
			}
		}
	}
	
	public function get_attendee_url($meeting_id='', $user_name='')
	{
		
		if($meeting_id=='') {
			return	get_languageword('meeting_id_can_not_be_empty');
		}
		if($user_name == '') {
			$user_name = "Attendee ". $this->config->item('site_settings')->site_name;
		}
		$this->load->library('bigbluebutton', $this->bbb_config);	
				
		// Instatiate the BBB class:
		$bbb = new BigBlueButton($this->bbb_config);

		/* ___________ JOIN MEETING w/ OPTIONS ______ */
		/* Determine the meeting to join via meetingId and join it.
		*/

		$joinParams = array(
			'meetingId' => $meeting_id, 			// REQUIRED - We have to know which meeting to join.
			'username' => $user_name,	// REQUIRED - The user display name that will show in the BBB meeting.
			'password' => 'ap',				// REQUIRED - Must match either attendee or moderator pass for meeting.
			'createTime' => '',				// OPTIONAL - string
			'userId' => '',					// OPTIONAL - string
			'webVoiceConf' => ''			// OPTIONAL - string
		);

		// Get the URL to join meeting:
		$itsAllGood = true;
		try {$result = $bbb->getJoinMeetingURL($joinParams);}
			catch (Exception $e) {
				echo 'Caught exception: ', $e->getMessage(), "\n";
				$itsAllGood = false;
			}

		if ($itsAllGood == true) {
			//Output results to see what we're getting:
//			print_r($result);
			return $result;
		}
	}
	
	public function get_moderator_url($meeting_id='', $user_name='')
	{
		if($meeting_id=='') {
			return	get_languageword('meeting_id_can_not_be_empty');
		}
		if($user_name == '') {
			$user_name = "Moderator ". $this->config->item('site_settings')->site_name;
		}
		$this->load->library('bigbluebutton', $this->bbb_config);	

		// Instatiate the BBB class:
		$bbb = new BigBlueButton($this->bbb_config);

		/* ___________ JOIN MEETING w/ OPTIONS ______ */
		/* Determine the meeting to join via meetingId and join it.
		*/

		$joinParams = array(
			'meetingId' => $meeting_id, 				// REQUIRED - We have to know which meeting to join.
			'username' => $user_name,		// REQUIRED - The user display name that will show in the BBB meeting.
			'password' => 'mp',					// REQUIRED - Must match either attendee or moderator pass for meeting.
			'createTime' => '',					// OPTIONAL - string
			'userId' => '',						// OPTIONAL - string
			'webVoiceConf' => ''				// OPTIONAL - string
		);

		// Get the URL to join meeting:
		$itsAllGood = true;
		try {$result = $bbb->getJoinMeetingURL($joinParams);}
			catch (Exception $e) {
				echo 'Caught exception: ', $e->getMessage(), "\n";
				$itsAllGood = false;
			}

		if ($itsAllGood == true) {
			//Output results to see what we're getting:
			//print_r($result);
			return $result;
		}		
	}
	
	
	/**
	* 
	* @type INT $booking_id
	* @param INT $type 1--Student, 2--TUTOR
	* 
	* @return boolean TRUE/FALSE
	*/
	public function init( $booking_id )
	{
		if( empty( $booking_id ) ) {
			safe_redirect($this->redirect_url, get_languageword('Wrong operation. Please select a meeting to start.'), 'error');
		}
		$user = $this->ion_auth->user()->row();
		$user_id = $this->ion_auth->get_user_id();
		//Verify the booking details before initiate the meeting
		$where['booking_id'] = $booking_id;
		$where['date_for_training'] = date('Y-m-d');
		$where['status'] = 'Approved';
		$now = date('Y-m-d');
		
		if( $this->ion_auth->is_tutor() ) { // Tutor
			$query = 'SELECT b.*, course.name course_name FROM ' . TBL_BOOKINGS . ' b 
			INNER JOIN ' . TBL_USERS . ' st ON b.student_id = st.id ' . 
			'INNER JOIN ' . TBL_USERS . ' tu ON b.tutor_id = tu.id ' . 
			'INNER JOIN ' . TBL_CATEGORIES . ' course ON b.course_id = course.id ' .
			'WHERE (b.status = "session_initiated" OR b.status="running") AND "'.$now.'" BETWEEN start_date AND end_date AND b.tutor_id = ' . $user_id .' AND booking_id = ' . $booking_id;			
		} else { // Student
			$query = 'SELECT b.*, course.name course_name FROM ' . TBL_BOOKINGS . ' b 
			INNER JOIN ' . TBL_USERS . ' st ON b.student_id = st.id ' . 
			'INNER JOIN ' . TBL_USERS . ' tu ON b.tutor_id = tu.id ' .
			'INNER JOIN ' . TBL_CATEGORIES . ' course ON b.course_id = course.id ' .
			'WHERE (b.status = "session_initiated" OR b.status="running") AND "'.$now.'" BETWEEN start_date AND end_date AND b.student_id = ' . $user_id .' AND booking_id = ' . $booking_id;
			
			$up_data = array();
			$up_data['prev_status'] = 'session_initiated';
			$up_data['status'] 		= 'running';
			$up_data['updated_at'] 		= date('Y-m-d H:i:s');
			$up_data['updated_by'] 		= $user_id;
			$this->base_model->update_operation($up_data, 'bookings', array('booking_id' => $booking_id));
		}
		
		$booking_details = $this->db->query( $query )->result();
		
		$meeting = FALSE;
		
		if(count($booking_details)>0) {
			$meeting = TRUE;
			$booking_details = $booking_details[0];
		}
		
		if($meeting==FALSE) {
			safe_redirect($this->redirect_url, get_languageword('Invalid Meeting Details'), 'error');
		}
		$meetingName = $booking_details->course_name;
		
		
		
		$this->load->library('bigbluebutton', $this->bbb_config);
		// Instatiate the BBB class:
		$bbb = new BigBlueButton($this->bbb_config);
		
		
		
		$meetingInfo = $bbb->getMeetingInfoWithXmlResponseArray(array('meetingId' => $booking_id, 'password' => 'mp'));
		
		$meeting_id = $booking_id;
		if($meetingInfo) {
			if( $meetingInfo['returncode']->__toString() != 'SUCCESS' ) {
				if( $this->ion_auth->is_tutor() ) {
					$meeting_id = $this->create_meeting($booking_id,$meetingName);
				} else {					
					$message = get_languageword('Meeting Not yet started. Please contact your tutor.');
					// $message = $meetingInfo['message']->__toString();
					$redirect_url = 'enquiries/running'; // Student
					safe_redirect($redirect_url, $message, 'error');
				}
			} else {
				$meeting_id = $meetingInfo['meetingId']->__toString();
			}
		} else {
			// No meetings are running with given meeting ID, Then let us create metting
			if( $this->ion_auth->is_tutor() ) {
				if ( $meetingInfo ) {
					$meeting_id = $this->create_meeting($booking_id,$meetingName);
				} else {
					safe_redirect($this->redirect_url, get_languageword('Something went wrong. Contact administrator. May be BBB Server not ready OR BBB Server URL Wrong'), 'error');
				}
				
			} else {
				safe_redirect($this->redirect_url, get_languageword('Meeting Not yet started. Please contact your tutor.'), 'error');
			}
		}
			
		$url = '';
		if( $this->ion_auth->is_student() ) {
			//Get Attendee URL
			$url = $this->get_attendee_url($meeting_id,$user->username);					
		}	
		if( $this->ion_auth->is_tutor() ) {
			$url = $this->get_moderator_url($meeting_id,$user->username);					
		}
		$template_type = '';
		if( $this->ion_auth->is_student() ) {
			$template_type = 'template/site/student-template';
		}
		if( $this->ion_auth->is_tutor() ) {
			$template_type = 'template/site/tutor-template';
		}
		
		// echo "Meetingid: ".$meeting_id; die();
		
		
		$this->data['url'] 						= $url;
		$this->data['meeting_name'] 			= $meetingName;
		$this->data['meeting_id'] 				= $meeting_id;
		$this->data['active_class'] 			= "enquiries";
		$this->data['title'] 					= get_languageword('Initiate Meeting : ') . $meetingName;
		$this->data['content'] 					= 'join-meeting';
		$this->_render_page($template_type, $this->data);
	}
}
// End of Virtual Class