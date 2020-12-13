<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
class Service_Model extends CI_Model  
{
	var $numrows;

	function __construct()
	{
		parent::__construct();
	}

	function get_student_profile($student_slug = "",$student_lead_id= "",$user_id="")
    {
        if(empty($student_slug))
            return NULL;

        $CI =& get_instance();

        $student_id = $this->get_uid_by_slug($student_slug);

        if(empty($student_id))
            return NULL;

        $student_info_query = "SELECT * FROM ".TBL_USERS." WHERE id=".$student_id." AND active=1 AND visibility_in_search='1' AND availability_status='1' 
                  AND is_profile_update=1 ";

        $student_details = $this->db->query($student_info_query)->result();

        if(!empty($student_details)) {

            if($student_lead_id > 0) {

                $lead_info_query = "SELECT sl.*, l.location_name, pl.location_name AS parent_location_name, c.name AS course_name, t.teaching_type FROM ".TBL_STUDENT_LEADS." sl INNER JOIN ".TBL_LOCATIONS." l ON l.id=sl.location_id INNER JOIN ".TBL_LOCATIONS." pl ON pl.id=l.parent_location_id INNER JOIN ".TBL_CATEGORIES." c ON c.id=sl.course_id INNER JOIN ".TBL_TEACHING_TYPES." t ON t.id=sl.teaching_type_id WHERE sl.id=".$student_lead_id." AND sl.status='Opened' ";

                $lead_details = $this->db->query($lead_info_query)->result();

                if(!empty($lead_details) && !$this->ion_auth->is_admin()) {

                    $credits_required_for_viewing_lead = get_system_settings('credits_for_viewing_lead');

                    if($credits_required_for_viewing_lead > 0) {

                        
                            $viewer_id = $user_id;
                        

                        if(!$this->is_already_viewed_the_lead($viewer_id, 'student_leads', $student_lead_id)) {

                            $viewer_credits = get_user_credits($viewer_id);

                            if($viewer_credits >= $credits_required_for_viewing_lead) {

                                //Log Credits transaction data & update user net credits - Start
                                $log_data = array(
                                                'user_id' => $viewer_id,
                                                'credits' => $credits_required_for_viewing_lead,
                                                'per_credit_value' => get_system_settings('per_credit_value'),
                                                'action'  => 'debited',
                                                'purpose' => get_languageword('For viewing lead ').' "'.$lead_details[0]->title_of_requirement.'" '.get_languageword('of Student').' "'.$student_details[0]->username.'"',
                                                'date_of_action ' => date('Y-m-d H:i:s'),
                                                'reference_table' => 'student_leads',
                                                'reference_id' => $student_lead_id,
                                            );

                                log_user_credits_transaction($log_data);

                                update_user_credits($viewer_id, $credits_required_for_viewing_lead, 'debit');
                                //Log Credits transaction data & update user net credits - End

                                //Update Lead View Count
                                $this->update_lead_view_count($student_lead_id);

                            } else {

                                $hlink = '#';
                                if($this->ion_auth->is_tutor())
                                    $hlink = URL_TUTOR_LIST_PACKAGES;
                                else if($this->ion_auth->is_institute())
                                    $hlink = URL_INSTITUTE_LIST_PACKAGES;

                                $CI->prepare_flashmessage(get_languageword('you_don\'t_have_enough_credits_to_view_the_lead_details. Please')." <a href='".$hlink."'><strong>".get_languageword('_get_credits_here.')."</strong></a> ", 2);
                                return redirect(URL_HOME_SEARCH_STUDENT_LEADS);
                            }
                        }
                    }

                }


                $student_details[0]->lead_details = $lead_details;
            }

             //Student's Gallery
            $student_gallery_query = "SELECT image_title, image_name FROM ".$this->db->dbprefix('gallery')." WHERE user_id=".$student_id." AND image_status='Active' ORDER BY image_order ASC";
            $student_details[0]->student_gallery = $this->db->query($student_gallery_query)->result();

            return $student_details;

        } else return array();

    }


    function is_already_viewed_the_lead($user_id = "", $reference_table = "", $reference_id = "")
    {
        if(empty($user_id) || empty($reference_table) || empty($reference_id))
            return FALSE;

        $is_exist = $this->db->select('id')->get_where(TBL_USER_CREDIT_TRANSACTIONS, array('user_id' => $user_id, 'reference_table' => $reference_table, 'reference_id' => $reference_id))->row();
        if(count($is_exist) > 0)
            return TRUE;
        else
            return FALSE;
    }


    function update_lead_view_count($lead_id = "")
    {
        if(empty($lead_id))
            return NULL;

        $query = "UPDATE ".TBL_STUDENT_LEADS." SET no_of_views=no_of_views+1 WHERE id=".$lead_id." ";

        $this->db->query($query);

        return $this->db->affected_rows();
    }

     function get_uid_by_slug($uslug = "")
    {
        if(empty($uslug))
            return NULL;

        $row = $this->db->select('id')->get_where($this->db->dbprefix('users'), array('slug' => $uslug))->row();

        return (!empty($row)) ? $row->id : '';
    }
}