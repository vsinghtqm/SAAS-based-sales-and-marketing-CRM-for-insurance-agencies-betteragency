<?php

namespace App\Lib\ApiProviders;

use App\Lib\PermissionsCheck;
use App\Lib\QuickTables\ContactsQuickTable;
use App\Model\Entity\Contact;
use App\Model\Entity\ContactOpportunity;
use App\Model\Entity\ContactNotesAttachment;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Http\Session;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router; 
use App\Classes\Aws;
use App\Classes\CommonFunctions;
class NotesListing
{

	public static function getNotes($objectData, $fields = null)
	{
		try
		{
			$myfile = fopen(ROOT."/logs/vuenotelisting.log", "a") or die("Unable to open file!");
			$session = Router::getRequest()->getSession(); 
			$login_agency_id = $session->read('Auth.User.agency_id');
			$Agency = TableRegistry::getTableLocator()->get('Agency');
			$UsStates = TableRegistry::getTableLocator()->get('UsStates');
			$agencyDetail = $Agency->agencyDetails($login_agency_id);
			$usersTimezone = 'America/Phoenix';
			$stateDetail = $UsStates->stateDetail($agencyDetail['us_state_id']);
			if (isset($agencyDetail['time_zone']) && !empty($agencyDetail['time_zone'])) {
				$usersTimezone = $agencyDetail['time_zone'];
			} else if (isset($stateDetail) && !empty($stateDetail)) {
				$usersTimezone = $stateDetail->time_zone;
			}
			$keyword = '';
			$offset = 0;
			$limit = 0;
			if (is_array($objectData)) {
				if (!empty($objectData['keyword'])) {
					$keyword = $objectData['keyword'];
				}
				if (!empty($objectData['offset'])) {
					$offset = $objectData['offset'];
				}
				if (!empty($objectData['limit'])) {
					$limit = $objectData['limit'];
				}
				$contactId = $objectData['contact_id'];
			} else {
				$contactId = $objectData;
			}
			$result = [];
			$getNotes = [];    
			$getNotes = TableRegistry::getTableLocator()->get('ContactNotes');
			$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');        
			$getNotes = $getNotes->searchContactNotesVue($login_agency_id, $contactId, $keyword, $offset, $limit);    
	
			foreach (array_unique(array_column($getNotes, 'noteDate')) as $date) {
				foreach (array_filter($getNotes, function($v) use ($date) { 
					return $v['noteDate'] == $date; 
				}) as $notes) {
					$time_zone = CommonFunctions::getShortCodeTimeZone($notes['agency_id']);
					$policy_number = $ContactOpportunities->contactOpportunityDetail($notes['opportunity_id']);
					if (isset($policy_number) && !empty($policy_number)) {
						if (isset($policy_number['policy_number']) && $policy_number['policy_number'] != '') {
							$policy_number = $policy_number['policy_number'];
							$notes['policy_number'] = 'Policy #' . $policy_number;
						}
					} else {
						$notes['policy_number'] = '';
					}    
					if ($notes['pinned'] == _PIN) {
						$notes['pinned_active'] = true;
					}
	
					$agencyTime = date('h:i a', strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s', strtotime($notes['created'])))));
					$dateMonth = date('M d, Y', strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s', strtotime($notes['created'])))));
					$notes['agencyTime'] = $agencyTime;
					$notes['dateMonth'] = $dateMonth;
					$notes['time_zone'] = $time_zone;
					$month = date("F", strtotime($date));
					$year = date("Y", strtotime($date));
					$month_year = $month . "-" . $year;
					$result[$month_year][] = $notes;
				}
			}
	
			// PHP logic to segregate pinned messages
			$finalResult = ["Pinned" => []];
	
			foreach ($result as $month => $notes) {
				foreach ($notes as $index => $note) {
					if ($note['pinned'] === 2) {
						$finalResult['Pinned'][] = $note;
						unset($result[$month][$index]);
					}
				}
				$result[$month] = array_values($result[$month]);
			}
	
			// Sort result by dateMonth
			uksort($result, function ($a, $b) use ($result) {
				$firstDate = strtotime($result[$a][0]['dateMonth']);
				$secondDate = strtotime($result[$b][0]['dateMonth']);
				return $secondDate - $firstDate;
			});
	
			// Merge pinned notes on top
			$finalResult = array_merge($finalResult, $result);
			$return['NotesListing.getNotes'] = [
				$contactId => $finalResult
			];
			return $return;
		} catch (\Exception $e) {
			$txt = date('Y-m-d H:i:s') . ' :: Note Lisiting Error- ' . $e->getMessage();
			fwrite($myfile, $txt . PHP_EOL);
		}
	}
	
	public static function getNoteAttachments($note_id)
	{
		try
		{
			$myfile = fopen(ROOT."/logs/vuenoteattachmentlisting.log", "a") or die("Unable to open file!");
			$awsBucket = AWS_BUCKET_NAME;
			$session = Router::getRequest()->getSession(); 
			$ContactsTable = TableRegistry::getTableLocator()->get('Contacts');
			$contactAttachments = TableRegistry::getTableLocator()->get('ContactAttachments');
			$contactNotesAttachments = TableRegistry::getTableLocator()->get('ContactNotesAttachments');
			$users = TableRegistry::getTableLocator()->get('Users');					
			$contact =$attachmentArray= array();
			$login_user_id = $session->read('Auth.User.user_id');
			$login_agency_id= $session->read("Auth.User.agency_id");
			$login_role_type = $session->read('Auth.User.role_type');
			$login_role_type_flag = $session->read('Auth.User.role_type_flag');
			$file = $folder_name= "";
			$contact_notes_attachment_list = '';
			if (isset($note_id) && !empty($note_id)) 
			{
			
				$attachmentArray = $contactNotesAttachments->find('all')->where(['note_id' =>$note_id])->toList();
				
				if (isset($attachmentArray )&& !empty($attachmentArray)) {

					foreach ($attachmentArray as $key => $value) {

						$ext = substr(strtolower(strrchr($value['name'], '.')), 1);
						$business_id='';
						if(isset($value['attachment_id']) && !empty($value['attachment_id'])){
							$businessId =$contactAttachments->get($value['attachment_id']);
							$business_id = $businessId['contact_business_id'];
						}else{
							$business_id = $value['contact_business_id'];
						}
						if(isset($business_id) && !empty($business_id)){
							$attach_id = $id;
							$folder_name = "business_";
						}else{
							$folder_name = "contact_";
							$attach_id = $id;
						}
						$pos = strrpos($value['name'], '/uploads/');
						$name = $pos === false ? SITEURL . 'uploads/'.$folder_name . $attach_id . '/' .$value['name'] : $value['name'];
						
						$download_link = "";
						if(isset($value['file_aws_key']) && !empty($value['file_aws_key']))
						{
							// $file = $value['file_url'];
							//$download_link = $this->getAwsLink($value['file_aws_key']);
							if(Aws::awsFileExists($value['file_aws_key']))
							{ 
								$file = $value['file_url'];
								//temporary link set for downloads
								$bucketdata=['bucket'=>$awsBucket,
								'keyname'=>$value['file_aws_key']];
								$download_link = (string)Aws::setPreAssignedUrl($bucketdata);
							}	
							
						}else{
							$file =$name ;
						}
						if(empty($download_link)){
							$download_link = $file;
						}
						$fileSize = $value['file_size'];
						
						$created_file_upload = date('M d, Y', strtotime($value['created']));

						if (isset($value['display_name']) && !empty($value['display_name'])) {
							$display_name = $value['display_name'];
							$final_display_name = strlen($display_name) > 10 ? substr($display_name,0,10)."..." : $display_name;
						} else {
							$display_name = $value['name'];
							$final_display_name = strlen($display_name) > 10 ? substr($display_name,0,10)."..." : $display_name;
						}
						
						$attachment_user_id = $value['user_id']; 
						if(isset($attachment_user_id) && !empty($attachment_user_id))
						{
							$attachment_user_id = $attachment_user_id;
						}
						else
						{
							$attachment_user_id = $login_user_id;
						}
						
						$userDetailsForAttachments = $users->userDetails($attachment_user_id);

						$attachement_user_name = '';
						if(isset($userDetailsForAttachments['first_name']) || !empty($userDetailsForAttachments['first_name']))
						{
							$attachement_user_name .=$userDetailsForAttachments['first_name'];
						}
						if(isset($userDetailsForAttachments['last_name']) || !empty($userDetailsForAttachments['last_name']))
						{
							$attachement_user_name  .=' '.$userDetailsForAttachments['last_name'];
						}
						if ($value['status'] == _ID_STATUS_ACTIVE) {
							$hideClass = $note_id == '' ? '' : " " . 'd-none';
							$disabledCheckBox =$note_id == '' ? '' : 'disabled';
							$file_url = SITEURL.'s3/view?type=note&id='.$value['id']; 
							$contact_notes_attachment_list .= '<tr class="contact_notes_attach_' . $value['id'] . '"><td><input type="checkbox" class="checkboxes" value="' . $value['id'] . '" onclick="disableCheckAll(); " ' . $disabledCheckBox . '></td><td><div class="hover-div hover-div-email table-hover-class attach-file-name-div">
									<p class="capitalizeText view_file_row" id="file_name_view_' . $value['id'] . '">' . $final_display_name . '</p>
									<div class="edit-icon-hover edit-icon-hover-email" id="edit_name_icon_' . $value['id'] . '">
									</div>
									<input type="text" id="file_name_text_' . $value['id'] . '" value="' . $display_name . '" style="display:none;" class="input_text form-control valid" aria-invalid="false">
									<input type="text" id="attachment_file_name_text_' . $value['id'] . '" value="' . $value['name'] . '" style="display:none;" class="input_text form-control valid" aria-invalid="false">
										<div class="update_file_name_icons">
											<i id="file_save_' . $value['id'] . '" class="fa fa-check text-success save_file" aria-hidden="true" style="cursor:pointer;display:none;"
												onclick="saveFileName(\'' . $value['id'] . '\',\'email\')"></i>
											<i id="file_cancel_' . $value['id'] . '" class="fa fa-times text-danger save_file"  aria-hidden="true" style="cursor:pointer;display:none;"
											onclick="cancelEditFileName(\'' . $value['id'] . '\',\'email\')"></i>
										<div>
										</div>
								</td>
							
								<td class="attachement_user_name_'.$value['id'].'">'.ucfirst($attachement_user_name).'</td>
								<td class="created_file_upload_'.$value['id'].'">' . $created_file_upload . '</td>
								<td style="padding: 20px 0px;">
								<span style="cursor: pointer;" delete-attachment-clipboard-target="'.$value['id'].'" class="col-sm-1' . $hideClass . '" onclick="deleteNotesAttachments(' . $value['id'] . ',' . $id . ')" ><i class="fa fa-trash text-danger"></i></span><span style="cursor: pointer;" class="col-sm-1" ><a style="color:#e16123" view-attachment-clipboard-target="'.$value['id'].'"  href="' . $file_url . '" target="_blank"><i class="fa fa-eye" aria-hidden="true"></i></a></span><span style="cursor: pointer;" class="col-sm-1"><a download-attachment-clipboard-target="'.$value['id'].'" href="' . $download_link . '" download><i class="fa fa-download text-info" aria-hidden="true"></i></a>
								</span>
								</td>
							</tr>';
						}
					}
				}
			
			}
			
			$return['NotesListing.getNoteAttachments'] = [
				$note_id => json_encode(array('status' => _ID_SUCCESS, 'contact_notes_attachment_list' => $contact_notes_attachment_list))
			];
			return $return;
		}catch (\Exception $e) {
			
            $txt=date('Y-m-d H:i:s').' :: Note Attchment Lising Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
	}

	public static function getNoteType($contact_id)
	{
		$session = Router::getRequest()->getSession(); 
		$login_agency_id= $session->read("Auth.User.agency_id");
		$ContactNoteTypes = TableRegistry::getTableLocator()->get('ContactNoteTypes');
		$contactNoteTypes = $ContactNoteTypes->noteTypesListByAgencyId($login_agency_id);
		$return['NotesListing.getNoteType'] = [
			$contact_id => $contactNoteTypes
		];
		return $return;
		
	}

	public static function getPolicesNoteType($contact_id)
	{
		$session = Router::getRequest()->getSession(); 
		$login_agency_id = $session->read("Auth.User.agency_id");
		$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
		$Contacts = TableRegistry::getTableLocator()->get('Contacts');
		$ContactsAdditionalInsured = TableRegistry::getTableLocator()->get('ContactAdditionalInsuredPolicyRelation');
		// get contact details 
		$contact = $Contacts->contactDetails($contact_id);
		if($contact['additional_insured_flag'] == _ID_STATUS_ACTIVE)
		{
			$mappedPolicies = $ContactsAdditionalInsured->getMappedPolicyWithPrimaryContact($contact_id);
			if(count($mappedPolicies) > 0)
			{
				$primaryId = $mappedPolicies[0]['contact_id'];
				$contatOpportunityIds = array_column($mappedPolicies,'contact_opportunity_id');
				$getPolicyListing = $ContactOpportunities->getMultiPolicyListing($primaryId,'',$contatOpportunityIds);
			}
		}
		else
		{
			//all opportunities get
			$getPolicyListing = $ContactOpportunities->getMultiPolicyListing($contact_id);
		}
		$get_policy_types_for_notes_listing = [];
		if (isset($getPolicyListing) && !empty($getPolicyListing)) {
			$insurance_type_name ='';
			foreach ($getPolicyListing as $key => $value) {

				// if (!empty($value['hawksoft_policy_title'])) {
				// 	$insurance_type_name = $value['hawksoft_policy_title'];
				// } 
				if (!empty($value['insurance_type']['type'])) {
					$insurance_type_name = $value['insurance_type']['type'];
				}
				$policy_number_note_list = !empty($value['policy_number']) ?  " - " . $value['policy_number'] : '';
				$opportunity_id_note_list = !empty($value['id']) ? "-" . $value['id'] : '';

				$insurance_type_id_note_list = isset($value['insurance_type']['id']) ? $value['insurance_type']['id'] : '';
				if(isset($policy_number_note_list) && $policy_number_note_list != '')
				{

					$policy_opprtunityId_note_list = $insurance_type_name . $policy_number_note_list;
				}
				else
				{
					$policy_opprtunityId_note_list = $value['insurance_type']['type'];
				}
				if(!empty($insurance_type_id_note_list))
				{
					
					$get_policy_types_for_notes_listing[] = [
						"id" => $insurance_type_id_note_list . $opportunity_id_note_list,
						"name" => $policy_opprtunityId_note_list,
						"policy_number" =>  $policy_number_note_list,
						"policy_type" => $insurance_type_name
					];
				}

			}
		}
		
		$return['NotesListing.getPolicesNoteType'] = [
			$contact_id => $get_policy_types_for_notes_listing
		];
		return $return;
	}

	public static function getSingleNote($note_id)
	{
		$session = Router::getRequest()->getSession(); 
		$login_agency_id = $session->read("Auth.User.agency_id");
		$ContactNotes = TableRegistry::getTableLocator()->get('ContactNotes');
		$contactNoteTypes = $ContactNotes->getNoteByNoteId($note_id,$login_agency_id);
		$contactNoteTypes['new_note'] = strip_tags($contactNoteTypes['note']); 
		$contactNoteTypes['note_type'] = $contactNoteTypes['insurance_type_id'] .'-'. $contactNoteTypes['opportunity_id'];
		$contactNoteTypes['new_note'] = strip_tags($contactNoteTypes['note']);
		$return['NotesListing.getSingleNote'] = [
			$note_id => $contactNoteTypes
		];
		return $return;
		
	}

	/**
     * Add Contact Notes
     *
     */

    public static function addContactNotes($objectData)
    {
		try
		{
			$myfile = fopen(ROOT."/logs/vuenoteupdate.log", "a") or die("Unable to open file!");
			$session = Router::getRequest()->getSession(); 
			$login_user_id = $session->read('Auth.User.user_id');
			$login_agency_id = $session->read('Auth.User.agency_id');
			$Contacts = TableRegistry::getTableLocator()->get('Contacts');
			$ContactNotes = TableRegistry::getTableLocator()->get('ContactNotes');

			$info=array();

			if(isset($objectData) && !empty($objectData)){
				$id = $objectData['note_id'];
				$contact_note_types = $ContactNotes->get($id, [
					'contain' => []
				]);

				$note_type_id = $objectData['note_type_id'];
				
				$policy_type_id = "";
				if(isset($objectData['policy_type_id']) && !empty($objectData['policy_type_id']))
				{
					$policy_type_id = $objectData['policy_type_id'];
				}
				$policy_type_id = !empty($policy_type_id) ? explode('-', $policy_type_id) : '';
				$new_note_type_list="";
				
				$arr=[];
				$arr['note_type_id'] = $note_type_id;
				$arr['insurance_type_id'] = $policy_type_id[0];
				$arr['opportunity_id'] = $policy_type_id[1];
				$contact_note_types = $ContactNotes->patchEntity($contact_note_types,$arr);
				if($contact_note_types = $ContactNotes->save($contact_note_types))
				{
					$response = json_encode(array('status' => _ID_SUCCESS));
				}else{
					$response = json_encode(array('status' => _ID_FAILED));
				}

				return $response;
			}
		}catch (\Exception $e) {
			
			$txt=date('Y-m-d H:i:s').' :: Note updating Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
		}
        
    }
	public function getNoteTypeList($contactId, $fields){
		$session = Router::getRequest()->getSession(); 
		$login_agency_id = $session->read("Auth.User.agency_id");
		$ContactNoteTypes = TableRegistry::getTableLocator()->get('ContactNoteTypes');
		$noteTypes = $ContactNoteTypes->getnoteTypesListByAgencyId($login_agency_id);
		$return['NotesListing.getNoteTypeList'] = [
				$contactId => $noteTypes
			];
		return $return;
	}
	public function getNotesPolicyTypes($contactId, $fields)
	{
		$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
		$ContactsAdditionalInsured = TableRegistry::getTableLocator()->get('ContactAdditionalInsuredPolicyRelation'); 
		$Contacts = TableRegistry::getTableLocator()->get('Contacts'); 
        // get contact details 
        $contact = $Contacts->contactDetails($contactId);

        if($contact['additional_insured_flag'] == _ID_STATUS_ACTIVE)
        {
            $mappedPolicies = $ContactsAdditionalInsured->getMappedPolicyWithPrimaryContact($contactId);
            if(count($mappedPolicies) > 0)
            {
                $primaryId = $mappedPolicies[0]['contact_id'];
                $contatOpportunityIds = array_column($mappedPolicies,'contact_opportunity_id');
				$getMultiPolicyListingActive = $ContactOpportunities->getMultiPolicyActiveListing($primaryId,$contatOpportunityIds);
        		$getMultiPolicyListingInactive = $ContactOpportunities->getMultiPolicyInActiveListing($primaryId, $contatOpportunityIds);
			}
        }
        else
        {
            //all opportunities get
            $getMultiPolicyListingActive = $ContactOpportunities->getMultiPolicyActiveListing($contactId);
        	$getMultiPolicyListingInactive = $ContactOpportunities->getMultiPolicyInActiveListing($contactId);
        }

		$policyTypes = array();
		if (isset($getMultiPolicyListingActive) && !empty($getMultiPolicyListingActive)) {
			$insurance_type_name ='';
			array_push($policyTypes,array('header'=>'Active policies'));		
			foreach ($getMultiPolicyListingActive as $key => $value) {

				if (!empty($value['insurance_type']['type'])) {
					$insurance_type_name = $value['insurance_type']['type'];
				}
				$policy_number_note_list = !empty($value['policy_number']) ? " - " . $value['policy_number'] : '';
				$opportunity_id_note_list = !empty($value['id']) ? "-" . $value['id'] : '';
				$insurance_type_id_note_list = isset($value['insurance_type']['id']) ? $value['insurance_type']['id'] : '';
				$policy_opprtunityId_note_list = $insurance_type_name . $policy_number_note_list;   
				array_push($policyTypes,array('id'=>$insurance_type_id_note_list.'-'.$value['id'],'name'=>$policy_opprtunityId_note_list,"policy_number" =>  $policy_number_note_list,"policy_type" => $insurance_type_name));
			}			
		}
		if (isset($getMultiPolicyListingInactive) && !empty($getMultiPolicyListingInactive)) {
			$insurance_type_name ='';
			array_push($policyTypes,array('header'=>'Inactive policies'));		
			foreach ($getMultiPolicyListingInactive as $key => $value) {

				// if (!empty($value['hawksoft_policy_title'])) {
				// 	$insurance_type_name = $value['hawksoft_policy_title'];
				// } 
				if (!empty($value['insurance_type']['type'])) {
					$insurance_type_name = $value['insurance_type']['type'];
				}
				$policy_number_note_list = !empty($value['policy_number']) ? " - " . $value['policy_number'] : '';
				$opportunity_id_note_list = !empty($value['id']) ? "-" . $value['id'] : '';
				$insurance_type_id_note_list = isset($value['insurance_type']['id']) ? $value['insurance_type']['id'] : '';
				$policy_opprtunityId_note_list = $insurance_type_name . $policy_number_note_list;			
				array_push($policyTypes,array('id'=>$insurance_type_id_note_list.'-'.$value['id'],'name'=>$policy_opprtunityId_note_list,"policy_number" =>  $policy_number_note_list,"policy_type" => $insurance_type_name));
			}			
		}
		$return['NotesListing.getNotesPolicyTypes'] = [
				$contactId => $policyTypes
		];
		return $return;	
	}
	
	public function saveContactNotes($objectData)
    {
		try{
		$myfile = fopen(ROOT."/logs/vuenoteadd.log", "a") or die("Unable to open file!");	
        $session = Router::getRequest()->getSession(); 
		$login_user_id = $session->read('Auth.User.user_id');
		$login_agency_id = $session->read('Auth.User.agency_id');
		$Contacts = TableRegistry::getTableLocator()->get('Contacts');
		$ContactNotes = TableRegistry::getTableLocator()->get('ContactNotes');
		$ContactNoteTypes = TableRegistry::getTableLocator()->get('ContactNoteTypes');
		$info=array();
		if(isset($objectData) && !empty($objectData))
		{
			if(isset($objectData['note_type_id']) && !empty($objectData['note_type_id']))
			{
				$note_type_id = $objectData['note_type_id'];
			}
			if(isset($objectData['policy_type_id']) && !empty($objectData['policy_type_id']))
			{
				$policy_type_id = $objectData['policy_type_id'];
				$policy_type_id = !empty($policy_type_id) ? explode('-', $policy_type_id) : '';
			}
			if(isset($objectData['new_note_type']) && !empty($objectData['new_note_type']))
			{
				$new_note_type = $objectData['new_note_type'];
			}
			$contactnotes=$ContactNotes->newEntity();
			//if note type choosen other
			if($note_type_id==_NOTE_OTHER && !empty($new_note_type)){
				//1. save new note type in master note type table
				$contact_note_types = $ContactNoteTypes->newEntity();
				$arr=[];
				$arr['note_type']=$new_note_type;
				$arr['agency_id']=$login_agency_id;
				$contact_note_types = $ContactNoteTypes->patchEntity($contact_note_types,$arr);
				if ($contact_note_types=$ContactNoteTypes->save($contact_note_types)) {
					$contact_note_types->id;
					$info['note_type_id']=$contact_note_types->id;
				}
			}else{
				$info['note_type_id']=$note_type_id;
			}
			$info['agency_id']      =   $login_agency_id;
			$info['user_id']        =   $login_user_id;
			$info['added_date']     =   date('Y-m-d H:i:s');
		}
        
        if(isset($objectData['contact_id']) && !empty($objectData['contact_id']))
        {
            $info['contact_id']     =   $objectData['contact_id'];
        }
		if(isset($objectData['contact_business_id']) && !empty($objectData['contact_business_id']))
        {
            $info['contact_business_id']     =   $objectData['contact_business_id'];
        }
		if(isset($objectData['note_text']) && !empty($objectData['note_text']))
        {
            $info['note']     =   $objectData['note_text'];
        }
        if (!empty($policy_type_id)) {
            $info['insurance_type_id'] = $policy_type_id[0];
            $info['opportunity_id'] = $policy_type_id[1];
        }
        $contactnotes  =  $ContactNotes->patchEntity($contactnotes, $info);
        if($notes_id = $ContactNotes->save($contactnotes))
        {
            if(isset($objectData['contact_id']) && !empty($objectData['contact_id']))
            {
                $Contacts->updateAll(['order_by_recent'=>date('Y-m-d H:i:s')],['id'=>$objectData['contact_id']]);
            }
            $return = json_encode(array('status' => _ID_SUCCESS,'note_id' => $notes_id->id ));
        }
        else {
            $return = json_encode(array('status' => _ID_FAILED));
        }
        return $return;
    }catch (\Exception $e) {			
			$txt=date('Y-m-d H:i:s').' :: contact id'.$objectData['contact_id'].'Note Saving Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
		}
	}

	public static function getNotesByOpp($opportunity_id, $fields)
	{
		try
		{ 
			$myfile = fopen(ROOT."/logs/vuenotelisting.log", "a") or die("Unable to open file!");
			$session = Router::getRequest()->getSession(); 
			$login_agency_id = $session->read('Auth.User.agency_id');
			$Agency = TableRegistry::getTableLocator()->get('Agency');
			$UsStates = TableRegistry::getTableLocator()->get('UsStates');
			$agencyDetail = $Agency->agencyDetails($login_agency_id);
			$usersTimezone='America/Phoenix';
			$stateDetail = $UsStates->stateDetail($agencyDetail['us_state_id']);
			if(isset($agencyDetail['time_zone']) && !empty($agencyDetail['time_zone']))
			{
				$usersTimezone =  $agencyDetail['time_zone'];
			}
			else if(isset($stateDetail) && !empty($stateDetail))
			{
			   $usersTimezone =  $stateDetail->time_zone;
			}
			$getNotes = [];	
			$getNotes = TableRegistry::getTableLocator()->get('ContactNotes');
			$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
			$getNotes = $getNotes->find('all')->contain(['Users','ContactNoteTypes','Contacts','ContactNotesAttachments'])
						->where(['ContactNotes.opportunity_id' => $opportunity_id,'ContactNotes.status !=' => _ID_STATUS_DELETED])
						->select(['Users.first_name','Users.last_name','ContactNotes.agency_id','ContactNotes.note','ContactNotes.id',"ContactNotes.created","noteDate"=>"DATE_FORMAT(ContactNotes.created,'%M %e,%Y')","noteTime"=>"DATE_FORMAT(ContactNotes.created,'%h:%i %p')","ContactNoteTypes.note_type","ContactNotes.opportunity_id"])->order(['ContactNotes.created' => 'DESC'])->hydrate(false)->toArray();
			foreach (array_unique(array_column($getNotes, 'noteDate')) as $date) {
				foreach (array_filter($getNotes, function($v) use ($date) { 
				return $v['noteDate'] == $date; 
				}) as $notes) {
					$time_zone = CommonFunctions::getShortCodeTimeZone($notes['agency_id']);
					$policy_number = $ContactOpportunities->contactOpportunityDetail($opportunity_id);
					if(isset($policy_number) && !empty($policy_number))
					{
						$policy_number = $policy_number['policy_number'];
						$notes['policy_number'] = $policy_number;
					}else{
						$notes['policy_number'] = '';
					}					

					$agencyTime = date('h:i a',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($notes['created'])))));
					$dateMonth = date('M d, Y',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($notes['created'])))));
					$notes['agencyTime'] = $agencyTime;
					$notes['dateMonth'] = $dateMonth;
					$notes['time_zone'] = $time_zone;
					$month = date("M",strtotime($date));
					$year = date("Y",strtotime($date));
					$month_year = $month."-".$year;
					$result[$month_year][] = $notes;
				}
			
			}			
			
			$return['NotesListing.getNotesByOpp'] = [
				$opportunity_id => $result
			];
			return $return;
		}catch (\Exception $e) {
			
            $txt=date('Y-m-d H:i:s').' :: Note Lisiting Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }

    }

	public static function getNotesByOppDatabase($data)
	{
		try
		{ 
			$myfile = fopen(ROOT."/logs/vuenotelisting.log", "a") or die("Unable to open file!");
			$session = Router::getRequest()->getSession(); 
			$login_agency_id = $session->read('Auth.User.agency_id');
			$Agency = TableRegistry::getTableLocator()->get('Agency');
			$UsStates = TableRegistry::getTableLocator()->get('UsStates');
			$agencyDetail = $Agency->agencyDetails($login_agency_id);
			$usersTimezone='America/Phoenix';
			$stateDetail = $UsStates->stateDetail($agencyDetail['us_state_id']);
			$result = [];
			if(isset($agencyDetail['time_zone']) && !empty($agencyDetail['time_zone']))
			{
				$usersTimezone =  $agencyDetail['time_zone'];
			}
			else if(isset($stateDetail) && !empty($stateDetail))
			{
			   $usersTimezone =  $stateDetail->time_zone;
			}
			$getNotes = [];	
			$getNotes = TableRegistry::getTableLocator()->get('ContactNotes');
			$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
			$getNotes = $getNotes->find('all')->contain(['Users','ContactNoteTypes','Contacts','ContactNotesAttachments'])
						->where(['ContactNotes.opportunity_id' => $data['opportunity_id'],'ContactNotes.status !=' => _ID_STATUS_DELETED])
						->select(['Users.first_name','Users.last_name','ContactNotes.agency_id','ContactNotes.note','ContactNotes.id',"ContactNotes.created","noteDate"=>"DATE_FORMAT(ContactNotes.created,'%M %e,%Y')","noteTime"=>"DATE_FORMAT(ContactNotes.created,'%h:%i %p')","ContactNoteTypes.note_type","ContactNotes.opportunity_id"])->order(['ContactNotes.created' => 'DESC'])->hydrate(false)->toArray();
			foreach (array_unique(array_column($getNotes, 'noteDate')) as $date) {
				foreach (array_filter($getNotes, function($v) use ($date) { 
				return $v['noteDate'] == $date; 
				}) as $notes) {
					$time_zone = CommonFunctions::getShortCodeTimeZone($notes['agency_id']);
					$policy_number = $ContactOpportunities->contactOpportunityDetail($notes['opportunity_id']);
					if(isset($policy_number) && !empty($policy_number))
					{
						if(isset($policy_number['policy_number']) && $policy_number['policy_number'] != '')
						{

							$policy_number = $policy_number['policy_number'];
							$notes['policy_number'] = '#'.$policy_number;
						}
					}else{
						$notes['policy_number'] = '';
					}					

					$agencyTime = date('h:i a',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($notes['created'])))));
					$dateMonth = date('M d, Y',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($notes['created'])))));
					$notes['agencyTime'] = $agencyTime;
					$notes['dateMonth'] = $dateMonth;
					$notes['time_zone'] = $time_zone;
					$month = date("M",strtotime($date));
					$year = date("Y",strtotime($date));
					$month_year = $month."-".$year;
					$result[$month_year][] = $notes;
				}
			
			}			
			// if(isset($result) && !empty($result))
			// {
			// 	$response =  json_encode(array('status' => _ID_SUCCESS,'noteData'=>$result));
				
			// }
			// else
			// {
			// 	$response =  json_encode(array('status' => _ID_FAILED,'noteData'=>''));
			// }
			$response =  json_encode(array('status' => _ID_SUCCESS,'noteData'=>$result));
        	return $response;
		}catch (\Exception $e) {
			
            $txt=date('Y-m-d H:i:s').' :: Note Lisiting Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }

    }

	public static function getSingleNoteDatabase($data)
	{
		$session = Router::getRequest()->getSession(); 
		$login_agency_id = $session->read("Auth.User.agency_id");
		$ContactNotes = TableRegistry::getTableLocator()->get('ContactNotes');
		$contactNoteTypes = $ContactNotes->getNoteByNoteId($data['note_id'],$login_agency_id);
		if($contactNoteTypes)
		{
			$contactNoteTypes['new_note'] = strip_tags($contactNoteTypes['note']); 
			$contactNoteTypes['note_type'] = $contactNoteTypes['insurance_type_id'] .'-'. $contactNoteTypes['opportunity_id'];
			$contactNoteTypes['new_note'] = strip_tags($contactNoteTypes['note']);
		}
		$response =  json_encode(array('status' => _ID_SUCCESS,'noteData'=>$contactNoteTypes));
        return $response;
	}

	/**
     * Toggle pinned Contact Notes
     *
     */

	public static function togglePinNotes($objectData)
	{
		try
		{
			$myFile = fopen(ROOT."/logs/vueTogglePinnedNotes.log", "a") or die("Unable to open file!");
			$session = Router::getRequest()->getSession(); 
			$login_user_id = $session->read('Auth.User.user_id');
			$login_agency_id = $session->read('Auth.User.agency_id');
			$ContactNotes = TableRegistry::getTableLocator()->get('ContactNotes');

			if(isset($objectData) && !empty($objectData)){
				$noteId = $objectData['contact_note_id'];
				$contactId = $objectData['contact_id'];
				$pinnedNotes = $ContactNotes->getPinnedNotesForPersonal($contactId, $login_agency_id);
				$contactNotes = $ContactNotes->get($noteId);

				if(isset($contactNotes) && !empty($contactNotes))
				{
					if($contactNotes['pinned'] == _PIN)
					{
						$update = $ContactNotes->updateAll(['pinned'=> _UNPIN],['id'=> $noteId, 'agency_id' => $login_agency_id]);
						$response = json_encode(['status' => _ID_SUCCESS, 'noteId' => $noteId]);
					}
					else if($contactNotes['pinned'] == _UNPIN && count($pinnedNotes) < 3)
					{
						$update = $ContactNotes->updateAll(['pinned'=> _PIN],['id'=> $noteId, 'agency_id' => $login_agency_id]);
						$response = json_encode(['status' => _ID_SUCCESS, 'noteId' => $noteId]);
					}
					else
					{
						$response = json_encode(['status' => _ID_FAILED]);
					}
				}

				return $response;
			}
		}catch (\Exception $e) {
			
			$txt = date('Y-m-d H:i:s').' :: Note updating Error- '.$e->getMessage();
			fwrite($myFile, $txt.PHP_EOL);
		}
		
	}


	public static function getNotesNext($contactIdNext, $fields)
	{
		try
		{ 
			$myfile = fopen(ROOT."/logs/vuenotelisting.log", "a") or die("Unable to open file!");
			$session = Router::getRequest()->getSession(); 
			$login_agency_id = $session->read('Auth.User.agency_id');
			$Agency = TableRegistry::getTableLocator()->get('Agency');
			$UsStates = TableRegistry::getTableLocator()->get('UsStates');
			$agencyDetail = $Agency->agencyDetails($login_agency_id);
			$usersTimezone = 'America/Phoenix';
			$stateDetail = $UsStates->stateDetail($agencyDetail['us_state_id']);
			if (isset($agencyDetail['time_zone']) && !empty($agencyDetail['time_zone'])) {
				$usersTimezone = $agencyDetail['time_zone'];
			} else if (isset($stateDetail) && !empty($stateDetail)) {
				$usersTimezone = $stateDetail->time_zone;
			}
	
			$contactId = explode("_", $contactIdNext)[0];
	
			$getNotes = [];    
			$getNotes = TableRegistry::getTableLocator()->get('ContactNotes');
			$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
			$getNotes = $getNotes->find('all')->contain(['Users','ContactNoteTypes','Contacts','ContactNotesAttachments'])
						->where(['ContactNotes.contact_id' => $contactId,'ContactNotes.status !=' => _ID_STATUS_DELETED])
						->select(['Users.first_name','Users.last_name','ContactNotes.agency_id','ContactNotes.note','ContactNotes.id', 'ContactNotes.pinned',"ContactNotes.created","noteDate"=>"DATE_FORMAT(ContactNotes.created,'%M %e,%Y')","noteTime"=>"DATE_FORMAT(ContactNotes.created,'%h:%i %p')","ContactNoteTypes.note_type","ContactNotes.opportunity_id"])->order(['ContactNotes.pinned' => 'DESC', 'ContactNotes.created' => 'DESC'])->hydrate(false)->toArray();
			
			$result = [];
			foreach (array_unique(array_column($getNotes, 'noteDate')) as $date) {
				foreach (array_filter($getNotes, function($v) use ($date) { 
					return $v['noteDate'] == $date; 
				}) as $notes) {
					$time_zone = CommonFunctions::getShortCodeTimeZone($notes['agency_id']);
					$policy_number = $ContactOpportunities->contactOpportunityDetail($notes['opportunity_id']);
					if (isset($policy_number) && !empty($policy_number)) {
						if (isset($policy_number['policy_number']) && $policy_number['policy_number'] != '') {
							$policy_number = $policy_number['policy_number'];
							$notes['policy_number'] = 'Policy #' . $policy_number;
						}
					} else {
						$notes['policy_number'] = '';
					}    
					
					if ($notes['pinned'] == _PIN) {
						$notes['pinned_active'] = true;
					}
	
					$agencyTime = date('h:i a', strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s', strtotime($notes['created'])))));
					$dateMonth = date('M d, Y', strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s', strtotime($notes['created'])))));
					$notes['agencyTime'] = $agencyTime;
					$notes['dateMonth'] = $dateMonth;
					$notes['time_zone'] = $time_zone;
					$month = date("F", strtotime($date));
					$year = date("Y", strtotime($date));
					$month_year = $month . "-" . $year;
					$result[$month_year][] = $notes;
				}
			}
	
			// PHP logic to segregate pinned messages
			$finalResult = ["Pinned" => []];
	
			foreach ($result as $month => $notes) {
				foreach ($notes as $index => $note) {
					if ($note['pinned'] === 2) {
						$finalResult['Pinned'][] = $note;
						unset($result[$month][$index]);
					}
				}
				$result[$month] = array_values($result[$month]);
			}
	
			// Sort result by dateMonth
			uksort($result, function ($a, $b) use ($result) {
				$firstDate = strtotime($result[$a][0]['dateMonth']);
				$secondDate = strtotime($result[$b][0]['dateMonth']);
				return $secondDate - $firstDate;
			});
	
			// Merge pinned notes on top
			$finalResult = array_merge($finalResult, $result);
	
			$return['NotesListing.getNotesNext'] = [
				$contactIdNext => $finalResult
			];
			return $return;
		} catch (\Exception $e) {
			$txt = date('Y-m-d H:i:s') . ' :: Note Lisiting Error- ' . $e->getMessage();
			fwrite($myfile, $txt . PHP_EOL);
		}
	}



	public static function searchContactNotes($objectData){
		
        $searchArr=array();
        if(isset($objectData['contact_id']) && !empty($objectData['contact_id'])){
            $searchArr['contact_id']=$objectData['contact_id'];
        }
		
        else if(isset($objectData['contact_business_id']) && !empty($objectData['contact_business_id']))
        {
            $searchArr['contact_business_id']=$objectData['contact_business_id'];
        }
        // if(isset($this->request->data['note_type_id']) && !empty($this->request->data['note_type_id'])){
        //     $searchArr['note_type_id']=$this->request->data['note_type_id'];
        // }
        // if(isset($this->request->data['insurance_type_id']) && !empty($this->request->data['insurance_type_id'])){
        //     $searchArr['insurance_type_id']=$this->request->data['insurance_type_id'];
        // }
		$offset = 0;
        if(isset($objectData['keyword']) && !empty($objectData['keyword'])){
           $keyword = $objectData['keyword'];
        }
		try
		{ 
			$myfile = fopen(ROOT."/logs/vueSearchnotelisting.log", "a") or die("Unable to open file!");
			$session = Router::getRequest()->getSession(); 
			$login_agency_id = $session->read('Auth.User.agency_id');
			$Agency = TableRegistry::getTableLocator()->get('Agency');
			$UsStates = TableRegistry::getTableLocator()->get('UsStates');
			$agencyDetail = $Agency->agencyDetails($login_agency_id);
			$usersTimezone='America/Phoenix';
			$stateDetail = $UsStates->stateDetail($agencyDetail['us_state_id']);
			if(isset($agencyDetail['time_zone']) && !empty($agencyDetail['time_zone']))
			{
				$usersTimezone =  $agencyDetail['time_zone'];
			}
			else if(isset($stateDetail) && !empty($stateDetail))
			{
			   $usersTimezone =  $stateDetail->time_zone;
			}
			$getNotes = [];	
			$getNotes = TableRegistry::getTableLocator()->get('ContactNotes');
			$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
			$getNotes = $getNotes->searchContactNotesVue($login_agency_id, $searchArr['contact_id'], $keyword, $offset);			
			// echo '<pre>';
			// print_r($getNotes);
			// die();
			foreach (array_unique(array_column($getNotes, 'noteDate')) as $date) {
				foreach (array_filter($getNotes, function($v) use ($date) { 
				return $v['noteDate'] == $date; 
				}) as $notes) {
					$time_zone = CommonFunctions::getShortCodeTimeZone($notes['agency_id']);
					$policy_number = $ContactOpportunities->contactOpportunityDetail($notes['opportunity_id']);
					if(isset($policy_number) && !empty($policy_number))
					{
						if(isset($policy_number['policy_number']) && $policy_number['policy_number'] != '')
						{
							$policy_number = $policy_number['policy_number'];
							$notes['policy_number'] = 'Policy #'.$policy_number;
						}
					}
					else
					{
						$notes['policy_number'] = '';
					}	
					if($notes['pinned'] == _PIN)
					{
						$notes['pinned_active'] = true;
					}

					$agencyTime = date('h:i a',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($notes['created'])))));
					$dateMonth = date('M d, Y',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($notes['created'])))));
					$notes['agencyTime'] = $agencyTime;
					$notes['dateMonth'] = $dateMonth;
					$notes['time_zone'] = $time_zone;
					$month = date("F",strtotime($date));
					$year = date("Y",strtotime($date));
					$month_year = $month."-".$year;
					$result[$month_year][] = $notes;
				}
			
			}			
			
			$return['NotesListing.searchContactNotes'] = [
				$objectData['contact_id'] => $result
			];
			return $return;
		}catch (\Exception $e) {
			
            $txt=date('Y-m-d H:i:s').' :: Note Search Lisiting Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
    }

}
