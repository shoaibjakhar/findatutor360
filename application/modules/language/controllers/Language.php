<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Language extends MY_Controller 
{
	function __construct()
	{
		parent::__construct();
		$this->load->library(array('session'));
		$this->load->library(array('ion_auth','form_validation', 'grocery_CRUD'));
		if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin())
		{
			redirect(SITEURL, 'refresh');
		}
		$this->load->model('language_model');
		$this->data['statistics'] = $this->language_model->getLanguageStatistics();
	}
	
	function isAdmin()
	{
		$group = array('admin');
		if (!$this->ion_auth->in_group($group)) {
			$this->prepare_flashmessage(get_languageword('MSG_NO_ENTRY'),2);
			redirect(getUserType());
		}
	}

	function checkduplicatelanguage()
	{
		$this->isAdmin();
		$ret = clean_text(strtolower($this->input->post('title')));		
		if (!$this->db->field_exists($ret, $this->db->dbprefix('languagewords'))) {
		  return true;
		} else {
		  $this->form_validation->set_message('checkduplicatelanguage', get_languageword('language_already_exists'));
		  return false;
		}
	}	
	function addlanguage($id = '')
	{
		$this->isAdmin();
		$this->data['id'] = $id;
		$condition = array();
		$condition['term_id'] = $id;
		
		if( $this->input->post( 'submitbutt' ) )
		{
			$this->load->library('form_validation');
			$this->form_validation->set_rules('title',get_languageword('language'),'trim|required|callback_checkduplicatelanguage');
			
			if($this->form_validation->run()!=false)
			{
				$language = clean_text(strtolower($this->input->post('title')));
				$this->load->dbforge();	
				if( $this->input->post('id') == '' )
				{
					$fields = array(
						$language => array(
							'type' => 'LONGTEXT',
							'COLLATE' => 'utf8_general_ci',
						)
					);
					$this->dbforge->add_column(TBL_LANGUAGEWORDS, $fields);
					$this->prepare_flashmessage(get_languageword('MSG_LANGUAGE_ADDED'), 0);					
				}
				else
				{
					$fields = array(
							$id => array(
									'name' => $language,
									'type' => 'LONGTEXT',
							),
					);
					$this->dbforge->modify_column(TBL_LANGUAGEWORDS, $fields);
					$this->prepare_flashmessage(get_languageword('MSG_LANGUAGE_UPDATED'), 0);					
				}	
				//
				$langs = '';
				$languages = $this->db->list_fields('languagewords');
				
				foreach($languages as $l)
				{
					if($l=='lang_id' || $l=='lang_key' || $l=='phrase_for')
						continue;
					else
						$langs = $langs.$l.',';
				}
				$langs = substr($langs, 0, -1);
				$this->base_model->run_query("UPDATE ".TBL_PREFIX.TBL_SETTINGS_FIELDS." SET field_type_values = '$langs' WHERE type_id=1 AND field_key='Default_Language'" );
				//
				redirect(URL_LANGUAGE_INDEX);				
			}
			else
			{
				$this->data['message'] = prepare_message(validation_errors(),1);
			}
			
		}		
		$this->data['activemenu'] = 'language';
		$this->data['activesubmenu'] = 'add';
		$this->data['pagetitle'] = get_languageword('add_language');
		$this->data['details'] = $this->db->list_fields('languagewords');
		$this->data['content'] = 'addlanguage';
		$this->_render_page('template/admin/admin-template', $this->data);
	}
	function deletelanguage($id)
	{
		$this->isAdmin();
		$this->load->dbforge();
		$this->dbforge->drop_column(TBL_LANGUAGEWORDS, urldecode($id));
		//
		$langs = '';
		$languages = $this->db->list_fields('languagewords');
		
		foreach($languages as $l)
		{
			if($l=='lang_id' || $l=='lang_key' || $l=='phrase_for')
				continue;
			else
				$langs = $langs.$l.',';
		}
		$langs = substr($langs, 0, -1);
		$this->base_model->run_query("UPDATE ".TBL_PREFIX.TBL_SETTINGS_FIELDS." SET field_type_values = '$langs' WHERE type_id=1 AND field_key='Default_Language'" );
		//
		
		$this->prepare_flashmessage(get_languageword('MSG_LANGUAGE_DELETED'), 0);
		redirect(URL_LANGUAGE_INDEX);
	}
	
	


	/*public function _unique_code_no()
	{
	    $CI =& get_instance();
	    $CI->form_validation->set_message('_unique_code_no', 'the language key and phrase for must be unique');
	    $lang_key=$CI->input->post('lang_key');
	    $phrase_for=$CI->input->post('phrase_for');
	    $query=$CI->db->query("select 1 from pre_languagewords where lang_key=$lang_key and phrase_for=$phrase_for");
	    if ($query->num_rows==0){
	        return TRUE;
	    }else{
	        return FALSE;
	    }
	}*/
		
	
	/*************************************************
	04-12-2018 - USEFUL - STANDARD METHODS-BELOW
	**************************************************/


	/*
	List all languages - languagewords - to edit all phrases at a time
	 */
	function index()
	{		
		$this->isAdmin();
		$this->data['message'] 		= $this->session->flashdata('message');	
		$this->data['records'] 		= $this->db->list_fields('languagewords');
		
		$this->data['content']		 	= 'index';
		$this->data['activemenu'] 		= 'language';
		$this->data['activesubmenu'] 	= 'view';
		$this->data['helptext'] 		= array();
		$this->data['pagetitle'] 		= get_languageword('update_language_strings');
		$this->_render_page('template/admin/admin-template', $this->data);		
	}


	/*
	Add,Edit,Delete - language in
	languages, languagewords , site settings-language options-
	Method
	 */
	function languages()
	{	
		$this->isAdmin();		
		$crud = new grocery_CRUD();
		$crud->unset_jquery(); //As we are using admin lte we need to unset default jQuery
		$crud->set_table($this->db->dbprefix(TBL_LANGUAGES));
		$crud->set_subject(get_languageword('language'));
		$crud->columns('id','name','status');
		$crud->required_fields(array('name', 'status'));
		// $crud->unique_fields('name','code');
		$state = $crud->getState();

		$mthd 	= $this->uri->segment(3);
		$p_key 	= $this->uri->segment(4);

		if ($this->input->post('name')!="" && $mthd=="insert_validation") {

			//languagewords table
			$language = clean_text(strtolower($this->input->post('name')));
			$languages = $this->db->list_fields('languagewords');
			
			//check column exist in langaugewords table
			$add_column=true;	
			foreach($languages as $l)
			{
				if($l===$language)
					$add_column=false;
			}

			if ($add_column) {
				$this->load->dbforge();	
				$fields = array(
					$language => array(
						'type' => 'LONGTEXT',
						'COLLATE' => 'utf8_general_ci',
					)
				);
				$this->dbforge->add_column(TBL_LANGUAGEWORDS, $fields);


				//site settings
				$this->update_site_languages();
			}

		} elseif ($this->input->post('name')!="" && $p_key>0 && $mthd=="update_validation") {

			//get language_name before edit - check that in languagewords table
			//if found-modify column name else add new column
			$languge_record = $this->base_model->get_query_result("SELECT * FROM ".TBL_PREFIX.TBL_LANGUAGES." WHERE id=".$p_key." ");

			if (!empty($languge_record)) {
				$languge_record = $languge_record[0];


				$before_edit_language = $languge_record->name;
				//check this column exist in languagewords table-
				$languages = $this->db->list_fields('languagewords');
				
				$check_for_existed = clean_text(strtolower($before_edit_language));
				$exist_column=false;	
				foreach($languages as $l)
				{
					if($l===$check_for_existed)
						$exist_column=true;
				}



				//if not exist - add new one
				$after_edit_language = clean_text(strtolower($this->input->post('name')));
				if (!$exist_column) {
					//add new column
					
					$language = clean_text(strtolower($before_edit_language));
					$this->load->dbforge();	
					$fields = array(
						$language => array(
							'type' => 'LONGTEXT',
							'COLLATE' => 'utf8_general_ci',
						)
					);
					$this->dbforge->add_column(TBL_LANGUAGEWORDS, $fields);

				} else {
					//edit existed column
					$language = clean_text(strtolower($after_edit_language));

					$check_for_existed = clean_text(strtolower($before_edit_language));

					if ($before_edit_language !== $after_edit_language) {
						//if column name changes - 
						
						$this->load->dbforge();
						$fields = array(
							$check_for_existed => array(
									'name' => $language,
									'type' => 'LONGTEXT',
									'COLLATE' => 'utf8_general_ci'
								),
						);
						$this->dbforge->modify_column(TBL_LANGUAGEWORDS, $fields);

					} elseif ($before_edit_language === $after_edit_language) {
						//nothing-to change in languagewords table
						
					}
				}
				//update language options in site settings
				$this->update_site_languages();
			}

		} elseif ($mthd=="delete" && $p_key>0) {

			$languge_record = $this->base_model->get_query_result("SELECT * FROM ".TBL_PREFIX.TBL_LANGUAGES." WHERE id=".$p_key." ");

			
			if (!empty($languge_record)) {
				$languge_record = $languge_record[0];

				$before_delete_language = $languge_record->name;
				$language = clean_text(strtolower($before_delete_language));

				$this->load->dbforge();
				$this->dbforge->drop_column(TBL_LANGUAGEWORDS, $language);

				$this->update_site_languages();
			}
		} 
		
		$output = $crud->render();
		$this->data['activemenu'] 		= 'language';
		$this->data['activesubmenu'] 	= 'languages';
		if($state != 'list')
		{
			$this->data['maintitle_link'] = base_url().'language/languages';
		}
		$this->data['maintitle'] = get_languageword('language_settings');
		$this->data['pagetitle'] = get_languageword('Tutoring_Languages');
		$this->data['grocery_output'] = $output;
		$this->data['grocery'] = TRUE;
		$this->grocery_output($this->data);
	}

	
	function update_site_languages()
	{
		$this->load->dbforge();
		//site settings
		$langs = '';
		
		$languages = $this->db->query("SHOW columns FROM pre_languagewords")->result();
		if (!empty($languages)) {
			foreach ($languages as $l)
			{
				if ($l->Field=='lang_id' || $l->Field=='lang_key' || $l->Field=='phrase_for')
					continue;
				else
					$langs = $langs.$l->Field.',';
			}
		}
		$langs = substr($langs, 0, -1);
		$this->base_model->run_query("UPDATE ".TBL_PREFIX.TBL_SETTINGS_FIELDS." SET field_type_values = '$langs' WHERE type_id=1 AND field_key='Default_Language'" );

		return;
	}

	/**
	* This function fecilitate to enter language words
	* @param string $language
	* @return void
	*/
	function addlanguagephrases( $language = "" )
	{
		$this->isAdmin();
		if(empty($language)) {
			$this->prepare_flashmessage(get_languageword('Please select language'), 1);
			redirect(URL_LANGUAGE_INDEX);
		}

		if (!empty($language)) {
			
			$query  = "SELECT tds.column_name FROM information_schema.columns tds WHERE table_name='".TBL_PREFIX."languagewords' AND tds.column_name='".$language."' "; 
			
			$record = $this->db->query($query)->result();
			
			if(empty($record))
			{
				$this->prepare_flashmessage(get_languageword('Invalid Operation'), 1);
				redirect(URL_LANGUAGE_INDEX);
			}
		}

		if($this->input->post()) {
			$words = $this->input->post('word');

			$id = ($this->input->post('id')) ? $this->input->post('id') : $language;
			foreach($words as $key => $val) {
				$key = str_replace('___', ' ', $key);
				if(!empty($key) && !is_numeric($key) && !empty($val))
					$this->base_model->update_operation( array($id => $val), 'languagewords', array('lang_key' => $key) );
			}
			$this->prepare_flashmessage(get_languageword('success_phrases_updated_successfuly'), 0);
			redirect(URL_LANGUAGE_INDEX);
		}
		
		$this->data['activemenu'] 		= 'language';
		$this->data['activesubmenu'] 	= 'view';
		$this->data['pagetitle'] 		= get_languageword('update_language_strings');
		$this->data['id'] 				= $language;
		$this->data['languagewords'] 	= $this->base_model->fetch_records_from('languagewords', array(), '*', 'lang_key');
		$this->data['content'] 			= 'addlanguagephrases';								   
		$this->_render_page('template/admin/admin-template', $this->data);
	}


	/*** Displays the Phrases Page**/
	function phrases()
	{	
		$this->isAdmin();		
		$crud = new grocery_CRUD();
		$crud->unset_jquery(); //As we are using admin lte we need to unset default jQuery
		$crud->set_table($this->db->dbprefix(TBL_LANGUAGEWORDS));
		$crud->set_subject(get_languageword('phrase'));
		$crud->columns('lang_id','lang_key','phrase_for','english');
		$crud->required_fields(array('lang_key', 'english'));
		// $crud->unique_fields('lang_key');
		$state = $crud->getState();

		$crud->unset_texteditor('*');

		$output = $crud->render();
		$this->data['activemenu'] = 'language';
		$this->data['activesubmenu'] = 'phrases';
		if($state != 'list')
		{
		$this->data['maintitle_link'] = base_url().'language/phrases';
		}
		$this->data['maintitle'] = get_languageword('language_settings');
		$this->data['pagetitle'] = get_languageword('view_phrases');
		$this->data['grocery_output'] = $output;
		$this->data['grocery'] = TRUE;
		$this->grocery_output($this->data);
	}
}