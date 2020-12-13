<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pay extends MY_Controller 
{

	function __construct()
	{
		parent::__construct();
		$this->load->library(array('session'));
		$this->load->library(array('ion_auth','form_validation'));

		$group = array('admin', 'tutor', 'student','institute');
		if (!$this->ion_auth->in_group($group)) {
			$this->prepare_flashmessage(get_languageword('MSG_NO_ENTRY'),2);
		}
	}



	/** Displays the Index Page**/
	function index()
	{

		$sc_id 		= $this->input->post('sc_id');
		$gateway_id = $this->input->post('gateway_id');

		if(!($sc_id > 0)) {

			$this->prepare_flashmessage(get_languageword('Invalid_Request'), 1);
			redirect(URL_HOME_BUY_COURSES);
		}

		$selling_course_slug = $this->base_model->fetch_value('tutor_selling_courses', 'slug', array('sc_id' => $sc_id));

		
		if(!($gateway_id > 0)) {

			$this->prepare_flashmessage(get_languageword('Please_select_payment_gateway'), 2);
			redirect(URL_HOME_CHECKOUT.'/'.$selling_course_slug);
		}


		if(!$this->ion_auth->logged_in()) {

			$this->session->set_userdata('req_from', 'buy_course');
			$this->session->set_userdata('selling_course_slug', $selling_course_slug);
			$this->prepare_flashmessage(get_languageword('please_login_to_continue'), 2);
			redirect(URL_AUTH_LOGIN);
		}



		$user_id = $this->ion_auth->get_user_id();
		

		$record = get_tutor_sellingcourse_info($sc_id);

		if(empty($record)) {
			$this->prepare_flashmessage(get_languageword('No Details Found'), 2);
			redirect(URL_HOME_BUY_COURSES);

		}

		$gateway_details=$this->base_model->get_payment_gateways(' AND st2.type_id = '.$gateway_id);


		if(empty($gateway_details)) {

			$this->prepare_flashmessage(get_languageword('Payment Gateway Details Not Found'), 2);
			redirect(URL_HOME_CHECKOUT.'/'.$selling_course_slug);
		}
		
		$total_amount 					= $record->course_price;
		$admin_commission_percentage 	= $record->admin_commission_percentage;
		$admin_commission_val 			= number_format(($total_amount * ($admin_commission_percentage / 100)), 2);


		$input_data['sc_id']    	    = $sc_id;
		$input_data['tutor_id']    	    = $record->tutor_id;
		$input_data['user_id']          = $user_id;
		$input_data['total_amount']     = $total_amount;
		$input_data['admin_commission_percentage']  = $admin_commission_percentage;
		$input_data['admin_commission_val']     	= $admin_commission_val;

		$input_data['max_downloads']    = $record->max_downloads;

        $input_data['payment_gateway_id']   = $gateway_id;
        $input_data['paid_date']     		= date('Y-m-d H:i:s');
        $input_data['last_modified']     	= date('Y-m-d H:i:s');


        $course_title = $record->course_title;

		$this->session->set_userdata('is_valid_request', 1);
		$this->session->set_userdata('course_purchase_data', $input_data);
		$this->session->set_userdata('selling_course_slug', $selling_course_slug);
		$this->session->set_userdata('selling_course_det', $record);
		$this->session->set_userdata('gateway_details', $gateway_details);



		$field_values = $this->db->get_where('system_settings_fields',array('type_id' => $gateway_id))->result();

		$user_details = $this->base_model->fetch_records_from('users', array('id' => $user_id));


		if($gateway_details[0]->type_id == PAYPAL_PAYMENT_GATEWAY ) //Paypal Settings
		{
			//Paypal Payment
			$config['return'] 				= base_url().'pay/payment_success';
			$config['cancel_return'] 		= base_url().'pay/payment_cancel';
			$config['production'] 			= true;
			$config['currency_code'] 		= 'USD';	

			foreach($field_values as $value) {

				if($value->field_key == 'Paypal_Email') {
					$config['business'] = $value->field_output_value;
				}
				if($value->field_key == 'Account_Type' && $value->field_output_value == 'sandbox') {
					$config['production'] = false;
				}
				if($value->field_key == 'Currency_Code') {
					$config['currency_code'] = $value->field_output_value;
				}
				if($value->field_key == 'Header_Logo' && $value->field_output_value != '' && file_exists(URL_PUBLIC_UPLOADS.'settings/thumbs/'.$value->field_output_value)) {
					$config['cpp_header_image'] = URL_PUBLIC_UPLOADS2.'settings/thumbs/'.$value->field_output_value;
				}
			}					

			$this->load->library('paypal', $config);
			$this->paypal->__initialize($config);

			$this->paypal->add($course_title, $total_amount);
			$this->paypal->pay(); /*Process the payment*/

		}
		elseif($gateway_details[0]->type_id == PAYU_PAYMENT_GATEWAY ) //Payu Settings
		{
			$payuparams = array();
			$MERCHANT_KEY = $SALT = $account_type = '';
			$PAYU_BASE_URL = 'https://test.payu.in';
			foreach($field_values as $value) {
				if($value->field_key == 'Account_TypeLIveSandbox') {
					$account_type = $value->field_output_value;
				}
			}

			foreach($field_values as $value) {						

				if($account_type == 'Sandbox')
				{
					if($value->field_key == 'Sandbox_Merchant_Key') {
						$payuparams['key'] = $value->field_output_value;
					}
					if($value->field_key == 'Sandbox_Salt') {
						$payuparams['salt'] = $value->field_output_value;
					}
					if($value->field_key == 'Test_URL') {
						$payuparams['action'] = $value->field_output_value;
					}								
				}
				else
				{
					if($value->field_key == 'Live_Merchant_Key') {
						$payuparams['key'] = $value->field_output_value;
					}
					if($value->field_key == 'Live_Salt') {
						$payuparams['salt'] = $value->field_output_value;
					}
					if($value->field_key == 'Live_URL') {
						$payuparams['action'] = $value->field_output_value;
					}
				}
			}

			$payuparams['surl'] = base_url() . 'pay/payment_success';
			$payuparams['furl'] = base_url() . 'pay/payment_cancel';

			$payuparams['service_provider'] = 'payu_paisa';
			$payuparams['productinfo'] 		= $course_title;
			$payuparams['amount'] 			= $total_amount;

			if(!empty($user_details))
			{
				$payuparams['firstname'] 	= $user_details[0]->first_name;
				$payuparams['lastname'] 	= $user_details[0]->last_name;
				$payuparams['email'] 		= $user_details[0]->email;
				$payuparams['phone'] 		= $user_details[0]->phone;
			}

			$this->load->helper('payu');					
			echo call_payu( $payuparams );
			die();
		}
		elseif ( $gateway_details[0]->type_id == TWOCHECKOUT_PAYMENT_GATEWAY )
		{

			$this->load->helper('2check-payment');
			$config = array();
			$url = 'https://www.2checkout.com/checkout/purchase';

			foreach($field_values as $value) {

				if($value->field_key == '2check_is_demo') {
					if ( strip_tags( trim( $value->field_output_value ) ) == 'yes' ) {
						$url = 'https://sandbox.2checkout.com/checkout/purchase';
						$config['demo'] =	'Y';
					}
				}
				if($value->field_key == '2check_seller_id') {
						$config['sid'] = $value->field_output_value;
				}
			}

			$config['mode'] = '2CO';
			$config['currency_code'] 	= get_system_settings('Currency_Code');
			$config['li_0_type'] 		= 'product'; // Always Lower Case, ‘product’, ‘shipping’, ‘tax’ or ‘coupon’, defaults to ‘product’
			$config['li_0_name'] 		= $course_title;
			$config['li_0_price'] 		= $total_amount;
			$config['li_0_quantity'] 	= 1;
			$config['li_0_tangible'] 	= 'N'; // If( is_virtual || is_downloadable ) Then it is 'Y'

			if(!empty($user_details))
			{
				$config['first_name'] 		= $user_details[0]->first_name;
				$config['last_name'] 		= $user_details[0]->last_name;
				$config['street_address'] 	= $user_details[0]->land_mark;
				$config['street_address2'] 	= $user_details[0]->land_mark;
				$config['city'] 			= $user_details[0]->city;
				$config['state'] 			= '';
				$config['zip'] 				= $user_details[0]->pin_code;
				$config['country'] 			= $user_details[0]->country;
				$config['email'] 			= $user_details[0]->email;
				$config['phone'] 			= $user_details[0]->phone;
			}

			$config['return_url']			= base_url() . 'pay/payment_success';
			$config['x_receipt_link_url'] 	= base_url() . 'pay/payment_success';
			twocheck_redirect( $url, $config );

		}
		elseif($gateway_details[0]->type_id == RAZORPAY_PAYMENT_GATEWAY )
		{
			$url = URL_HOME_CHECKOUT . '/'.$selling_course_slug.'/'.RAZORPAY_PAYMENT_GATEWAY;
			redirect( $url );
		}
		else
		{
			$this->prepare_flashmessage("Please contact us for fully implementation of this Payment Gateway", 2);
			redirect(URL_HOME_CHECKOUT.'/'.$selling_course_slug);
		}
	}




	function payment_success()
	{

		$success = 0;

		if($this->session->userdata('course_purchase_data') && $this->session->userdata('is_valid_request')) {

			$input_data 		= $this->session->userdata('course_purchase_data');
			$selling_course_slug= $this->session->userdata('selling_course_slug');
			$record				= $this->session->userdata('selling_course_det');
			$gateway_details 	= $this->session->userdata('gateway_details');

			if($gateway_details[0]->type_id == PAYPAL_PAYMENT_GATEWAY && $this->input->post()) {

				$input_data['paid_date']      	= $this->input->post('payment_date');
				$input_data['transaction_id']   = $this->input->post('txn_id');
				$input_data['paid_amount']   	= $this->input->post('mc_gross');
				$input_data['payer_id']      	= $this->input->post('payer_id');
				$input_data['payer_email']      = $this->input->post('payer_email');
				$input_data['payer_name']      	= $this->input->post('first_name')." ".$this->input->post('last_name');
				$input_data['payment_status']   = "Completed";//$this->input->post('payment_status'); Uncomment this for live

				if($input_data['payment_status'] == "Completed")
					$success = 1;

			}
			else if($gateway_details[0]->type_id == PAYU_PAYMENT_GATEWAY && $this->input->post()) {

				$status=$_POST["status"];
				$firstname=$_POST["firstname"];
				$amount=$_POST["amount"];
				$txnid=$_POST["txnid"];
				$posted_hash=$_POST["hash"];
				$key=$_POST["key"];
				$productinfo=$_POST["productinfo"];
				$email=$_POST["email"];


				$salt = '';
				$field_values = $this->db->get_where('system_settings_fields',array('type_id' => 27))->result();
				$account_type = '';

				foreach($field_values as $value) {
					if($value->field_key == 'Account_TypeLIveSandbox') {
						$account_type = $value->field_output_value;
					}
				}

				foreach($field_values as $index => $value) {
					if($account_type == 'Sandbox')
					{
						if($value->field_key == 'Sandbox_Salt') {
							$salt = $value->field_output_value;
						}
					}
					else
					{
						if($value->field_key == 'Live_Salt') {
							$salt = $value->field_output_value;
						}
					}				
				}


				if (isset($_POST["additionalCharges"])) {

					$additionalCharges=$_POST["additionalCharges"];

					$retHashSeq = $additionalCharges.'|'.$salt.'|'.$status.'||||||||||'.$email.'|'.$firstname.'|'.$productinfo.'|'.$amount.'|'.$txnid.'|'.$key;

				}
				else {

					$retHashSeq = $salt.'|'.$status.'|||||||||||'.$email.'|'.$firstname.'|'.$productinfo.'|'.$amount.'|'.$txnid.'|'.$key;
				}

				$hash = hash("sha512", $retHashSeq);

				if ($hash == $posted_hash) {

					$input_data['paid_date']      	= $this->input->post('addedon');
					$input_data['transaction_id']   = $this->input->post('txnid');
					$input_data['paid_amount']   	= $this->input->post('amount');
					$input_data['payer_email']      = $this->input->post('email');
					$input_data['payer_name']      	= $this->input->post('firstname')." ".$this->input->post('lastname');

					if($status == 'success') {

						$input_data['payment_status'] = "Completed";
						$success = 1;

					}

				}

			}
			else if($gateway_details[0]->type_id == TWOCHECKOUT_PAYMENT_GATEWAY && $this->input->post('invoice_id')) {

				neatPrint($_POST);

				$input_data['paid_date']      	= date('Y-m-d H:i:s');
				$input_data['transaction_id']   = $this->input->post('invoice_id');
				$input_data['paid_amount']   	= $input_data['total_amount'];
				$input_data['payer_email']      = $this->session->userdata('email');
				$input_data['payer_name']      	= $this->session->userdata('first_name')." ".$this->session->userdata('last_name');
				$input_data['payment_status']   = "Completed";

				if($input_data['payment_status'] == "Completed")
					$success = 1;

			}
			else if($gateway_details[0]->type_id == RAZORPAY_PAYMENT_GATEWAY && $this->input->post('razorpay_payment_id')) {


				$input_data['paid_date']      	= date('Y-m-d H:i:s');
				$input_data['transaction_id']   = $this->input->post('razorpay_payment_id');
				$input_data['paid_amount']   	= $input_data['total_amount'];
				$input_data['payer_email']      = $this->session->userdata('email');
				$input_data['payer_name']      	= $this->session->userdata('first_name')." ".$this->session->userdata('last_name');
				$input_data['payment_status']   = "Completed";

				if($input_data['payment_status'] == "Completed")
					$success = 1;

			}
			else {

				$this->prepare_flashmessage("Invalid Operation", 1);
				redirect(URL_HOME_BUY_COURSE.'/'.$selling_course_slug);
			}



			if($success == 1) {

				$purchase_id = $this->base_model->insert_operation($input_data, 'course_purchases');

				if($purchase_id > 0) {

					//05-12-2018 start
					//admin notification
					$purchased_rcrd = $this->base_model->get_query_result("SELECT c.*,s.course_name,t.username FROM pre_course_purchases c INNER JOIN pre_tutor_selling_courses s ON c.sc_id=s.sc_id INNER JOIN pre_users t ON c.tutor_id=t.id WHERE c.purchase_id=".$purchase_id." ");

					if (!empty($purchased_rcrd)) {

						$purchased_rcrd = $purchased_rcrd[0];

						$data = array();
						$data['user_id'] 	= $purchased_rcrd->user_id;
						$data['title'] 		= get_languageword('student_purchased_course')." ".$purchased_rcrd->course_name." of Tutor ".$purchased_rcrd->username;
						$data['content'] 	= "Student ".$this->session->userdata('first_name')." ".$this->session->userdata('last_name')." has been purchased course ".$purchased_rcrd->course_name." of Tutor ".$purchased_rcrd->username." ";

						$data['datetime']   = date('Y-m-d H:i:s');
						$data['admin_read'] = 0;
						$data['page_link']  = SITEURL."admin/view-purchased-courses";
						$data['table_name'] = "course_purchases";
						$data['primary_key_column'] = "purchase_id";
						$data['primary_key_value']  = $purchased_rcrd->purchase_id;

						
						$this->base_model->insert_operation($data,'notifications');	
						unset($data);


						//send email to student
						$tutor_rec 		= getUserRec($input_data['tutor_id']);
						$user_rec 		= getUserRec($purchased_rcrd->user_id);
						$currency = $this->config->item('site_settings')->currency_symbol;
						$email_tpl 		= $this->base_model->fetch_records_from('email_templates', array('template_status' => 'Active', 'email_template_id' => '23'));
						if(!empty($email_tpl)) {

							$email_tpl = $email_tpl[0];
						

							if(!empty($email_tpl->from_email)) {
								$from = $email_tpl->from_email;
							} else {
								$from 	= get_system_settings('Portal_Email');
							}
							$to 	= $user_rec->email;


							if(!empty($email_tpl->template_subject)) {
								$sub = $email_tpl->template_subject;
							} else {
								$sub = get_languageword("Course_Purchased");
							}

							if (!empty($email_tpl->template_content)) {

								$logo_img='<img src="'.get_site_logo().'" class="img-responsive" width="120px" height="50px">';

								$site_title = $this->config->item('site_settings')->site_title;

								$original_vars  = array($logo_img, $site_title, $user_rec->username, $purchased_rcrd->course_name, date('Y-m-d'), $purchased_rcrd->course_name, $tutor_rec->username, $currency.$purchased_rcrd->paid_amount, $purchased_rcrd->transaction_id, $purchased_rcrd->payer_id, $purchased_rcrd->payer_email, $purchased_rcrd->payer_name, $purchased_rcrd->payment_status);

								$temp_vars		= array('__SITE_LOGO__', '__SITE_TITLE__', '__USER__NAME__', '__COURSE_NAME__', '__PURCHASED_DATE__', '__COURSE_NAME__', '__TUTOR_NAME__', '__PAID_AMOUNT__', '__TRANSACTION_ID__', '__PAYER ID__', '__PAYER_EMAIL__', '__PAYER_NAME__', '__PAYMENT_STATUS__');

								$msg = str_replace($temp_vars, $original_vars, $email_tpl->template_content);

								sendEmail($from, $to, $sub, $msg);
							}
						} //send email to student end



						//send email to tutor
						$email_tpl 		= $this->base_model->fetch_records_from('email_templates', array('template_status' => 'Active', 'email_template_id' => '24'));
						if(!empty($email_tpl)) {

							$email_tpl = $email_tpl[0];
						

							if(!empty($email_tpl->from_email)) {
								$from = $email_tpl->from_email;
							} else {
								$from 	= get_system_settings('Portal_Email');
							}
							$to 	= $tutor_rec->email;


							if(!empty($email_tpl->template_subject)) {
								$sub = $email_tpl->template_subject;
							} else {
								$sub = get_languageword("Student_Purchased_Course");
							}

							if (!empty($email_tpl->template_content)) {

								$logo_img='<img src="'.get_site_logo().'" class="img-responsive" width="120px" height="50px">';

								$site_title = $this->config->item('site_settings')->site_title;

								$original_vars  = array($logo_img, $site_title, $tutor_rec->username, $user_rec->username, $purchased_rcrd->course_name, date('Y-m-d'), $purchased_rcrd->course_name, $currency.$purchased_rcrd->total_amount, $purchased_rcrd->admin_commission_percentage, $purchased_rcrd->admin_commission_val, $currency.$purchased_rcrd->paid_amount, $purchased_rcrd->transaction_id, $purchased_rcrd->payer_id, $purchased_rcrd->payer_email, $purchased_rcrd->payer_name, $purchased_rcrd->payment_status, $purchased_rcrd->status_of_payment_to_tutor);

								$temp_vars		= array('__SITE_LOGO__', '__SITE_TITLE__', '__TUTOR__NAME__', '__STUDENT_NAME__', '__COURSE_NAME__', '__PURCHASED_DATE__', '__COURSE_NAME__', '__TOTAL_AMOUNT__', '__PERCENT__', '__VALUE__', '__PAID_AMOUNT__', '__TRANSACTION_ID__', '__PAYER ID__', '__PAYER_EMAIL__', '__PAYER_NAME__', '__PAYMENT_STATUS__', '__PAYMENT_TUTOR__');

								$msg = str_replace($temp_vars, $original_vars, $email_tpl->template_content);

								sendEmail($from, $to, $sub, $msg);
							}
						}
						//send email to tutor end


						//send email to admin
						$email_tpl 		= $this->base_model->fetch_records_from('email_templates', array('template_status' => 'Active', 'email_template_id' => '25'));
						if(!empty($email_tpl)) {

							$email_tpl = $email_tpl[0];
						

							if(!empty($email_tpl->from_email)) {
								$from = $email_tpl->from_email;
							} else {
								$from 	= get_system_settings('Portal_Email');
							}
							$to 	= get_system_settings('Portal_Email');


							if(!empty($email_tpl->template_subject)) {
								$sub = $email_tpl->template_subject;
							} else {
								$sub = get_languageword("Student_Purchased_Course");
							}

							if (!empty($email_tpl->template_content)) {

								$logo_img='<img src="'.get_site_logo().'" class="img-responsive" width="120px" height="50px">';

								$site_title = $this->config->item('site_settings')->site_title;

								$original_vars  = array($logo_img, $site_title, $user_rec->username, $purchased_rcrd->course_name, date('Y-m-d'), $purchased_rcrd->course_name, $tutor_rec->username, $currency.$purchased_rcrd->total_amount, $purchased_rcrd->admin_commission_percentage, $purchased_rcrd->admin_commission_val, $currency.$purchased_rcrd->paid_amount, $purchased_rcrd->transaction_id, $purchased_rcrd->payer_id, $purchased_rcrd->payer_email, $purchased_rcrd->payer_name, $purchased_rcrd->payment_status, $purchased_rcrd->status_of_payment_to_tutor);

								$temp_vars		= array('__SITE_LOGO__', '__SITE_TITLE__', '__STUDENT_NAME__', '__COURSE_NAME__','__TUTOR_NAME__','__PURCHASED_DATE__', '__COURSE_NAME__', '__TOTAL_AMOUNT__', '__PERCENT__', '__VALUE__', '__PAID_AMOUNT__', '__TRANSACTION_ID__', '__PAYER ID__', '__PAYER_EMAIL__', '__PAYER_NAME__', '__PAYMENT_STATUS__', '__PAYMENT_TUTOR__');

								$msg = str_replace($temp_vars, $original_vars, $email_tpl->template_content);

								sendEmail($from, $to, $sub, $msg);
							}
						}
						//send email to admin end
					}

					if($gateway_details[0]->type_id == CREDITS_PAYMENT){

					
						// DEBIT THE CREDITS FROM STUDENT && CREDIT THE 

						//Log Credits transaction data & update user net credits - Start
						$student_id = $this->ion_auth->get_user_id();
						
						$log_data = array(
										'user_id' => $student_id,
										'credits' => $input_data['course_credits'],
										'per_credit_value' => $input_data['per_credit_value'],
										'action'  => 'debited',
										'purpose' => 'Purchased Course "'.$record->course_name.'" and Purchased Id is '.$purchase_id,
										'date_of_action	' => date('Y-m-d H:i:s'),
										'reference_table' => 'course_purchases',
										'reference_id' => $purchase_id,
									);
						
						log_user_credits_transaction($log_data);

						update_user_credits($student_id, $input_data['course_credits'], 'debit');
						//Log Credits transaction data & update user net credits - End
						$tutor_acquired_credits = $input_data['course_credits'] - $input_data['admin_commission_val'];

						//Log Credits transaction data & update user net credits - Start
						$log_data = array(
										'user_id' => $input_data['tutor_id'],
										'credits' => $tutor_acquired_credits,
										'per_credit_value' => $input_data['per_credit_value'],
										'action'  => 'credited',
										'purpose' => 'Credits added for the purchase of course "'.$record->course_name.'" ',
										'date_of_action	' => date('Y-m-d H:i:s'),
										'reference_table' => 'course_purchases',
										'reference_id' => $purchase_id,
									);

						log_user_credits_transaction($log_data);

						update_user_credits($input_data['tutor_id'], $tutor_acquired_credits, 'credit');
					}

					//05-12-2018 end
					


					if(!empty($record->sellingcourse_curriculum)) {

						//Create Zip with all attachments
						$this->load->library('zip');

						$dir 	= $purchase_id.'_'.$input_data['user_id'];

						$data 	= array();

						$sno 	= 1;

						foreach ($record->sellingcourse_curriculum as $key => $value) {
							
							if($value->source_type == "file") {

								$name 		= $sno.'.'.$value->title.'.'.$value->file_ext;
								$content 	= file_get_contents(URL_PUBLIC_UPLOADS.'course_curriculum_files/'.$value->file_name);

								$data[$name] = $content;

								$this->zip->add_data($data);

							} else {

								$name 		= $sno.'.'.$value->title.'.txt';
								$content 	= $value->file_name;

								$data[$name] = $content;

								$this->zip->add_data($data);

							}

							$sno++;
						}

						$this->zip->archive(URL_PUBLIC_UPLOADS.'course_curriculum_files/'.$dir.'.zip');

					}


					$this->load->model('student/student_model');
					$this->student_model->update_tutor_course_purchases($record->tutor_id, $record->sc_id);


		            //Email Alert to User - Start

		            //Email Alert to User - End


					$this->session->unset_userdata('is_valid_request');
					$this->session->unset_userdata('course_purchase_data');
					$this->session->unset_userdata('selling_course_slug');
					$this->session->unset_userdata('selling_course_det');
					$this->session->unset_userdata('gateway_details');

		            $this->prepare_flashmessage("You purchased Course Successfully", 0);
		            redirect(URL_HOME_BUY_COURSE.'/'.$selling_course_slug);

				} else {

					$this->prepare_flashmessage("Purchase Data Not Saved", 2);
		            redirect(URL_HOME_BUY_COURSE.'/'.$selling_course_slug);
				}

			} else {

				$this->prepare_flashmessage("Purchase Data not saved due to some technical issue. Please contact Admin", 2);
		        redirect(URL_HOME_BUY_COURSE.'/'.$selling_course_slug);
			}

		} else {

			$this->prepare_flashmessage("Invalid Operation", 1);
			redirect(URL_HOME_BUY_COURSES);
		}

	}

	function payment_cancel()
	{
		$this->session->unset_userdata('is_valid_request');
		$this->session->unset_userdata('course_purchase_data');
		$this->session->unset_userdata('selling_course_slug');
		$this->session->unset_userdata('selling_course_det');
		$this->session->unset_userdata('gateway_details');

		$this->prepare_flashmessage('You have cancelled your transaction', 2);
		redirect(URL_HOME_BUY_COURSES);
	}


	//process stripe
	function get_additional_content() {

		if ( $this->input->is_ajax_request() ) {

			$sc_id 		= $_POST['sc_id'];


			if(!($sc_id > 0)) {
				$this->prepare_flashmessage(get_languageword('Invalid_Request'), 1);
				redirect(URL_HOME_BUY_COURSES);
			}
			$selling_course_slug = $this->base_model->fetch_value('tutor_selling_courses', 'slug', array('sc_id' => $sc_id));


			$final_cost = $_POST['final_cost'];
			$name 		= $_POST['name'];
			$content = '';
			$gateway_id = STRIPE_PAYMENT_GATEWAY;

			if(!($gateway_id > 0)) {

				$this->prepare_flashmessage(get_languageword('Please_select_payment_gateway'), 2);
				redirect(URL_HOME_CHECKOUT.'/'.$selling_course_slug);
			}

			if(!$this->ion_auth->logged_in()) {

				$this->session->set_userdata('req_from', 'buy_course');
				$this->session->set_userdata('selling_course_slug', $selling_course_slug);
				$this->prepare_flashmessage(get_languageword('please_login_to_continue'), 2);
				redirect(URL_AUTH_LOGIN);
			}

			$user_id=$this->ion_auth->get_user_id();
			$record = get_tutor_sellingcourse_info($sc_id);
			if(empty($record)) {

				$this->prepare_flashmessage(get_languageword('No Details Found'), 2);
				redirect(URL_HOME_BUY_COURSES);
			}

			$gateway_details=$this->base_model->get_payment_gateways(' AND st2.type_id = '.$gateway_id);
			if(empty($gateway_details)) {
				$this->prepare_flashmessage(get_languageword('Payment Gateway Details Not Found'), 2);
				redirect(URL_HOME_CHECKOUT.'/'.$selling_course_slug);
			}

			$total_amount 					= $record->course_price;
			$admin_commission_percentage 	= $record->admin_commission_percentage;
			$admin_commission_val 			= number_format(($total_amount * ($admin_commission_percentage / 100)), 2);


			$input_data['sc_id']    	    = $sc_id;
			$input_data['tutor_id']    	    = $record->tutor_id;
			$input_data['user_id']          = $user_id;
			$input_data['total_amount']     = $total_amount;
			$input_data['admin_commission_percentage']  = $admin_commission_percentage;
			$input_data['admin_commission_val']     	= $admin_commission_val;

			$input_data['max_downloads']    = $record->max_downloads;

	        $input_data['payment_gateway_id']   = $gateway_id;
	        $input_data['paid_date']     		= date('Y-m-d H:i:s');
	        $input_data['last_modified']     	= date('Y-m-d H:i:s');


	        $course_title = $record->course_title;

	        $this->session->set_userdata('is_valid_request', 1);
			$this->session->set_userdata('course_purchase_data', $input_data);
			$this->session->set_userdata('selling_course_slug', $selling_course_slug);
			$this->session->set_userdata('selling_course_det', $record);
			$this->session->set_userdata('gateway_details', $gateway_details);

			if ( $gateway_id == 41 ) { // Stripe
				$user_info = $this->base_model->get_user_details($user_id);

				$field_values = $this->db->get_where('system_settings_fields',array('type_id' => $gateway_id))->result();


				$key = 'pk_test_H8R3tFH4RiyF0VGzTcXwl8NF';
				$stripe_test_mode = FALSE;
				foreach($field_values as $value) {
					if($value->field_key == 'stripe_test_mode') {
						if ( strip_tags( trim( $value->field_output_value ) ) == 'yes' ) {
							$stripe_test_mode = TRUE;
						}
					}
				}
				foreach($field_values as $value) {
					if( $value->field_key == 'stripe_key_test_publishable' && $stripe_test_mode == TRUE ) {
						$key = $value->field_output_value;
					} elseif ( $value->field_key == 'stripe_key_live_publishable' && $stripe_test_mode == FALSE ) {
						$key = $value->field_output_value;
					}
				}
				ob_start();
				?>
				<form action="<?php echo site_url('pay/process_stripe/'.$sc_id);?>" method="POST" id="frm_stripe">
				  <script
					src="https://checkout.stripe.com/v2/checkout.js" class="stripe-button" id="stripe_script"
					data-key="<?php echo $key;?>"
					data-amount="<?php echo $final_cost;?>"
					data-name="<?php echo $name;?>"
					data-description="<?php echo $name;?>"
					data-image="<?php  if(isset($this->config->item('site_settings')->logo) && $this->config->item('site_settings')->logo != '') echo substr(URL_PUBLIC_UPLOADS_SETTINGS.''.$this->config->item('site_settings')->logo, 5); else echo substr(URL_FRONT_IMAGES.'Logo.png', 5);?>"
					data-package_id="<?php echo $sc_id;?>"
					data-currency="<?php echo get_system_settings('Currency_Code');?>"
					data-email="<?php echo $user_info[0]->email;?>"
					data-locale="auto"
					>
				  </script>
				</form>
				<?php
				$content = ob_get_clean();
			}
			echo $content;
		}
	}

	//stripe payment for purchase course
	function process_stripe($sc_id)
	{
		$user_id = $this->ion_auth->get_user_id();
		$success = 0;

		if ( isset( $_POST['stripeToken'] ) && $user_id != '' ) {

			if($this->session->userdata('course_purchase_data') && $this->session->userdata('is_valid_request')) {

				$sc_id = $this->uri->segment(3);
				$gateway_id = STRIPE_PAYMENT_GATEWAY;
				$token  = $_POST['stripeToken'];


				$gateway_details = $this->base_model->get_payment_gateways(' AND st2.type_id = '.$gateway_id);
				$record 		= get_tutor_sellingcourse_info($sc_id);//selling course record
				$user_details = $this->base_model->get_user_details( $user_id );
				$user_info = $user_details[0];


				if (count($gateway_details) > 0 && count($record) > 0 && ! empty( $user_info )) {

					$field_values = $this->db->get_where('system_settings_fields',array('type_id' => $gateway_id))->result();

					$total_amount 	= $record->course_price;

					$stripeEmail = $_POST['stripeEmail'];

					$config = array('stripe_test_mode' => 'yes', 'stripe_key_test_secret' => 'sk_test_FHxf1NsgaWbAFAGny5zJELqU', 'stripe_key_live_secret' => 'pk_live_wPo6I0iKgXrs9mrk08cfwzc4', 'stripe_verify_ssl' => TRUE); // Default Values


					foreach($field_values as $value) {
						$config[ $value->field_key ] = $value->field_output_value;
					}				
					$this->load->library( 'stripe', $config );
					$customer = $this->stripe->customer_create( $token, $stripeEmail );
					$customer = json_decode( $customer ); // We are receiving data in JSON format so we need to decode it!

					
					if ( isset( $customer->error ) ) {
						
						$this->prepare_flashmessage(get_languageword('Payment failed : '). ": <strong>" . $customer->error->message . "</strong>", 1);
						redirect(URL_HOME_BUY_COURSE.'/'.$selling_course_slug);

					} else {

						
						$charge = $this->stripe->charge_customer( $total_amount, $customer->id, $record->course_title, get_system_settings('Currency_Code') ); // $amount, $customer_id, $desc

						$input_data 		= $this->session->userdata('course_purchase_data');
						$selling_course_slug= $this->session->userdata('selling_course_slug');
						$record				= $this->session->userdata('selling_course_det');
						$gateway_details 	= $this->session->userdata('gateway_details');

				
						$input_data['paid_date']      	= date('Y-m-d H:i:s');
						$input_data['transaction_id']   = $token;
						$input_data['paid_amount']   	= $record->course_price;
						$input_data['payer_id']      	= $customer->id;
						$input_data['payer_email']      = $stripeEmail;
						$input_data['payer_name']      	= $user_info->first_name.' '.$user_info->last_name;
						$input_data['payment_status']   = "Completed";//$this->input->post('payment_status'); Uncomment this for live

						if($input_data['payment_status'] == "Completed")
							$success = 1;
					}

				} else {
					$this->prepare_flashmessage("Invalid Operation", 1);
					redirect(URL_HOME_BUY_COURSE.'/'.$selling_course_slug);
				}




				if ($success == 1) {

					$purchase_id = $this->base_model->insert_operation($input_data, 'course_purchases');



					if($purchase_id > 0) {

						//05-12-2018 start
						//admin notification
						$purchased_rcrd = $this->base_model->get_query_result("SELECT c.*,s.course_name,t.username FROM pre_course_purchases c INNER JOIN pre_tutor_selling_courses s ON c.sc_id=s.sc_id INNER JOIN pre_users t ON c.tutor_id=t.id WHERE c.purchase_id=".$purchase_id." ");

						if (!empty($purchased_rcrd)) {

							$purchased_rcrd = $purchased_rcrd[0];

							$data = array();
							$data['user_id'] 	= $purchased_rcrd->user_id;
							$data['title'] 		= get_languageword('student_purchased_course')." ".$purchased_rcrd->course_name." of Tutor ".$purchased_rcrd->username;
							$data['content'] 	= "Student ".$this->session->userdata('first_name')." ".$this->session->userdata('last_name')." has been purchased course ".$purchased_rcrd->course_name." of Tutor ".$purchased_rcrd->username." ";

							$data['datetime']   = date('Y-m-d H:i:s');
							$data['admin_read'] = 0;
							$data['page_link']  = SITEURL."admin/view-purchased-courses";
							$data['table_name'] = "course_purchases";
							$data['primary_key_column'] = "purchase_id";
							$data['primary_key_value']  = $purchased_rcrd->purchase_id;

							
							$this->base_model->insert_operation($data,'notifications');	
							unset($data);


							//send email to student
							$tutor_rec 		= getUserRec($input_data['tutor_id']);
							$user_rec 		= getUserRec($purchased_rcrd->user_id);
							$currency = $this->config->item('site_settings')->currency_symbol;
							$email_tpl 		= $this->base_model->fetch_records_from('email_templates', array('template_status' => 'Active', 'email_template_id' => '23'));
							if(!empty($email_tpl)) {

								$email_tpl = $email_tpl[0];
							

								if(!empty($email_tpl->from_email)) {
									$from = $email_tpl->from_email;
								} else {
									$from 	= get_system_settings('Portal_Email');
								}
								$to 	= $user_rec->email;


								if(!empty($email_tpl->template_subject)) {
									$sub = $email_tpl->template_subject;
								} else {
									$sub = get_languageword("Course_Purchased");
								}

								if (!empty($email_tpl->template_content)) {

									$logo_img='<img src="'.get_site_logo().'" class="img-responsive" width="120px" height="50px">';

									$site_title = $this->config->item('site_settings')->site_title;

									$original_vars  = array($logo_img, $site_title, $user_rec->username, $purchased_rcrd->course_name, date('Y-m-d'), $purchased_rcrd->course_name, $tutor_rec->username, $currency.$purchased_rcrd->paid_amount, $purchased_rcrd->transaction_id, $purchased_rcrd->payer_id, $purchased_rcrd->payer_email, $purchased_rcrd->payer_name, $purchased_rcrd->payment_status);

									$temp_vars		= array('__SITE_LOGO__', '__SITE_TITLE__', '__USER__NAME__', '__COURSE_NAME__', '__PURCHASED_DATE__', '__COURSE_NAME__', '__TUTOR_NAME__', '__PAID_AMOUNT__', '__TRANSACTION_ID__', '__PAYER ID__', '__PAYER_EMAIL__', '__PAYER_NAME__', '__PAYMENT_STATUS__');

									$msg = str_replace($temp_vars, $original_vars, $email_tpl->template_content);

									sendEmail($from, $to, $sub, $msg);
								}
							} //send email to student end



							//send email to tutor
							$email_tpl 		= $this->base_model->fetch_records_from('email_templates', array('template_status' => 'Active', 'email_template_id' => '24'));
							if(!empty($email_tpl)) {

								$email_tpl = $email_tpl[0];
							

								if(!empty($email_tpl->from_email)) {
									$from = $email_tpl->from_email;
								} else {
									$from 	= get_system_settings('Portal_Email');
								}
								$to 	= $tutor_rec->email;


								if(!empty($email_tpl->template_subject)) {
									$sub = $email_tpl->template_subject;
								} else {
									$sub = get_languageword("Student_Purchased_Course");
								}

								if (!empty($email_tpl->template_content)) {

									$logo_img='<img src="'.get_site_logo().'" class="img-responsive" width="120px" height="50px">';

									$site_title = $this->config->item('site_settings')->site_title;

									$original_vars  = array($logo_img, $site_title, $tutor_rec->username, $user_rec->username, $purchased_rcrd->course_name, date('Y-m-d'), $purchased_rcrd->course_name, $currency.$purchased_rcrd->total_amount, $purchased_rcrd->admin_commission_percentage, $purchased_rcrd->admin_commission_val, $currency.$purchased_rcrd->paid_amount, $purchased_rcrd->transaction_id, $purchased_rcrd->payer_id, $purchased_rcrd->payer_email, $purchased_rcrd->payer_name, $purchased_rcrd->payment_status, $purchased_rcrd->status_of_payment_to_tutor);

									$temp_vars		= array('__SITE_LOGO__', '__SITE_TITLE__', '__TUTOR__NAME__', '__STUDENT_NAME__', '__COURSE_NAME__', '__PURCHASED_DATE__', '__COURSE_NAME__', '__TOTAL_AMOUNT__', '__PERCENT__', '__VALUE__', '__PAID_AMOUNT__', '__TRANSACTION_ID__', '__PAYER ID__', '__PAYER_EMAIL__', '__PAYER_NAME__', '__PAYMENT_STATUS__', '__PAYMENT_TUTOR__');

									$msg = str_replace($temp_vars, $original_vars, $email_tpl->template_content);

									sendEmail($from, $to, $sub, $msg);
								}
							}
							//send email to tutor end


							//send email to admin
							$email_tpl 		= $this->base_model->fetch_records_from('email_templates', array('template_status' => 'Active', 'email_template_id' => '25'));
							if(!empty($email_tpl)) {

								$email_tpl = $email_tpl[0];
							

								if(!empty($email_tpl->from_email)) {
									$from = $email_tpl->from_email;
								} else {
									$from 	= get_system_settings('Portal_Email');
								}
								$to 	= get_system_settings('Portal_Email');


								if(!empty($email_tpl->template_subject)) {
									$sub = $email_tpl->template_subject;
								} else {
									$sub = get_languageword("Student_Purchased_Course");
								}

								if (!empty($email_tpl->template_content)) {

									$logo_img='<img src="'.get_site_logo().'" class="img-responsive" width="120px" height="50px">';

									$site_title = $this->config->item('site_settings')->site_title;

									$original_vars  = array($logo_img, $site_title, $user_rec->username, $purchased_rcrd->course_name, date('Y-m-d'), $purchased_rcrd->course_name, $tutor_rec->username, $currency.$purchased_rcrd->total_amount, $purchased_rcrd->admin_commission_percentage, $purchased_rcrd->admin_commission_val, $currency.$purchased_rcrd->paid_amount, $purchased_rcrd->transaction_id, $purchased_rcrd->payer_id, $purchased_rcrd->payer_email, $purchased_rcrd->payer_name, $purchased_rcrd->payment_status, $purchased_rcrd->status_of_payment_to_tutor);

									$temp_vars		= array('__SITE_LOGO__', '__SITE_TITLE__', '__STUDENT_NAME__', '__COURSE_NAME__','__TUTOR_NAME__','__PURCHASED_DATE__', '__COURSE_NAME__', '__TOTAL_AMOUNT__', '__PERCENT__', '__VALUE__', '__PAID_AMOUNT__', '__TRANSACTION_ID__', '__PAYER ID__', '__PAYER_EMAIL__', '__PAYER_NAME__', '__PAYMENT_STATUS__', '__PAYMENT_TUTOR__');

									$msg = str_replace($temp_vars, $original_vars, $email_tpl->template_content);

									sendEmail($from, $to, $sub, $msg);
								}
							}
							//send email to admin end
						}

						if($gateway_details[0]->type_id == CREDITS_PAYMENT){

						
							// DEBIT THE CREDITS FROM STUDENT && CREDIT THE 

							//Log Credits transaction data & update user net credits - Start
							$student_id = $this->ion_auth->get_user_id();
							
							$log_data = array(
											'user_id' => $student_id,
											'credits' => $input_data['course_credits'],
											'per_credit_value' => $input_data['per_credit_value'],
											'action'  => 'debited',
											'purpose' => 'Purchased Course "'.$record->course_name.'" and Purchased Id is '.$purchase_id,
											'date_of_action	' => date('Y-m-d H:i:s'),
											'reference_table' => 'course_purchases',
											'reference_id' => $purchase_id,
										);
							
							log_user_credits_transaction($log_data);

							update_user_credits($student_id, $input_data['course_credits'], 'debit');
							//Log Credits transaction data & update user net credits - End
							$tutor_acquired_credits = $input_data['course_credits'] - $input_data['admin_commission_val'];

							//Log Credits transaction data & update user net credits - Start
							$log_data = array(
											'user_id' => $input_data['tutor_id'],
											'credits' => $tutor_acquired_credits,
											'per_credit_value' => $input_data['per_credit_value'],
											'action'  => 'credited',
											'purpose' => 'Credits added for the purchase of course "'.$record->course_name.'" ',
											'date_of_action	' => date('Y-m-d H:i:s'),
											'reference_table' => 'course_purchases',
											'reference_id' => $purchase_id,
										);

							log_user_credits_transaction($log_data);

							update_user_credits($input_data['tutor_id'], $tutor_acquired_credits, 'credit');
						}

						//05-12-2018 end
						


						if(!empty($record->sellingcourse_curriculum)) {

							//Create Zip with all attachments
							$this->load->library('zip');

							$dir 	= $purchase_id.'_'.$input_data['user_id'];

							$data 	= array();

							$sno 	= 1;

							foreach ($record->sellingcourse_curriculum as $key => $value) {
								
								if($value->source_type == "file") {

									$name 		= $sno.'.'.$value->title.'.'.$value->file_ext;
									$content 	= file_get_contents(URL_PUBLIC_UPLOADS.'course_curriculum_files/'.$value->file_name);

									$data[$name] = $content;

									$this->zip->add_data($data);

								} else {

									$name 		= $sno.'.'.$value->title.'.txt';
									$content 	= $value->file_name;

									$data[$name] = $content;

									$this->zip->add_data($data);

								}

								$sno++;
							}

							$this->zip->archive(URL_PUBLIC_UPLOADS.'course_curriculum_files/'.$dir.'.zip');

						}


						$this->load->model('student/student_model');
						$this->student_model->update_tutor_course_purchases($record->tutor_id, $record->sc_id);


			            //Email Alert to User - Start

			            //Email Alert to User - End


						$this->session->unset_userdata('is_valid_request');
						$this->session->unset_userdata('course_purchase_data');
						$this->session->unset_userdata('selling_course_slug');
						$this->session->unset_userdata('selling_course_det');
						$this->session->unset_userdata('gateway_details');
						
			            $this->prepare_flashmessage("You purchased Course Successfully", 0);
			            redirect(URL_HOME_BUY_COURSE.'/'.$selling_course_slug);

					} else {

						$this->prepare_flashmessage("Purchase Data Not Saved", 2);
			            redirect(URL_HOME_BUY_COURSE.'/'.$selling_course_slug);
					}

				} else {

					$this->prepare_flashmessage("Purchase Data not saved due to some technical issue. Please contact Admin", 2);
			        redirect(URL_HOME_BUY_COURSE.'/'.$selling_course_slug);
				}

			} else {
				$this->prepare_flashmessage("Invalid Operation", 1);
				redirect(URL_HOME_BUY_COURSE.'/'.$selling_course_slug);
			}
		}
		 else {

			$this->prepare_flashmessage("Bad Request", 1);
			redirect(URL_HOME_BUY_COURSE.'/'.$selling_course_slug);
		}
	}
	
}