<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
class User extends CI_Model
{
	function __construct() 
	{
		$this->tableName = 'users';
		$this->primaryKey = 'id';
		// $this->load->helper(array('url','language','date','string'));
	}
	public function checkUser($data = array())
	{
		$this->db->select(array($this->primaryKey,'user_belongs_group'));
		$this->db->from($this->db->dbprefix($this->tableName));
		$this->db->where(array('email'=>$data['email']));
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
					'first_name' 			=> $data['first_name'],
                    'last_name'  			=> $data['last_name'],
                    'username'				=> $username,
                    'phone'  			    => null,
                    'phone_code'            => null,
                    'pin_code'              => null,
                    'active'                => 1,
                    'created_on'  	        => time(),
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
		return $userID?$userID:FALSE;
    }
}
?>