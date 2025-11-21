<?php

namespace App\Lib\ApiProviders;

use App\Lib\NowCerts\NowCertsApi;
use App\Lib\PermissionsCheck;
use App\Lib\QuickTables\ContactsQuickTable;
use App\Model\Entity\Contact;
use App\Model\Entity\ContactOpportunity;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Http\Session;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use App\Classes\Aws;
use App\Classes\CommonFunctions;
use App\Classes\CoverageDetail;
use App\Lib\ContactOpportunityChangeLogging\ContactOpportunityChanger;
use App\Classes\FileLog;
use App\Classes\Policy;
use phpDocumentor\Reflection\Types\Null_;

class ContactOpportunities
{
	public static function getPoliciesForContact($contactId, $fields=null){
        $ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
		$ContactNotes = TableRegistry::getTableLocator()->get('ContactNotes');
		$Services = TableRegistry::getTableLocator()->get('Services');
        $ContactAcordForms = TableRegistry::getTableLocator()->get('ContactAcordForms');
        $ContactPolicyAttachments = TableRegistry::getTableLocator()->get('ContactPolicyAttachments');
        $AgencyInsuranceTypesLink = TableRegistry::getTableLocator()->get('AgencyInsuranceTypesLink');
        $ContactPolicyLostDetails = TableRegistry::getTableLocator()->get('ContactPolicyLostDetails');
		$Contacts = TableRegistry::getTableLocator()->get('Contacts');
		$allContactOpportunities = [];
		// get contact details 
        $contact = $Contacts->contactDetails($contactId);

			$ContactsAdditionalInsured = TableRegistry::getTableLocator()->get('ContactAdditionalInsuredPolicyRelation');   
            $mappedPolicies = $ContactsAdditionalInsured->getMappedPolicyWithPrimaryContact($contactId);
            if(count($mappedPolicies) > 0)
            {
                $primaryId = $mappedPolicies[0]['contact_id'];
                $contatOpportunityIds = array_column($mappedPolicies,'contact_opportunity_id');
                $contactAdditionalOpportunities = $ContactOpportunities->activeInactivePendingPolicyIds($contatOpportunityIds);
            }
           $allContactOpportunities = $ContactOpportunities->activeInactivePendingPolicyListing($contactId);
			if($contactAdditionalOpportunities)
			 {
				 foreach ($contactAdditionalOpportunities as $key => $value)
				 {
					 $contactAdditionalOpportunities[$key]['is_additional'] = _ID_STATUS_ACTIVE;
				 }
				 $allContactOpportunities = array_merge($allContactOpportunities, $contactAdditionalOpportunities);
			 }
		$ContactPolicyRenewal = TableRegistry::getTableLocator()->get('ContactPolicyRenewal');
		$session = Router::getRequest()->getSession();
		if(isset($allContactOpportunities) && !empty($allContactOpportunities))
		{
			foreach($allContactOpportunities as $contactOpportunity){
				if($contactOpportunity->status == _ID_STATUS_ACTIVE)
				{
					$return['Active']['CO-' . $contactOpportunity->id] = $contactOpportunity;
				}
				elseif ($contactOpportunity->status == _ID_STATUS_INACTIVE)
				{
					$return['InActive']['CO-' . $contactOpportunity->id] = $contactOpportunity;
				}
				elseif($contactOpportunity->status == _ID_STATUS_PENDING)
				{
					$return['Pending']['CO-' . $contactOpportunity->id] = $contactOpportunity;
				}
			}
		}
		$return['ContactOpportunities.getPoliciesForContact'] = [
            $contactId => $return
        ];
        return $return;
    }


	public static function getPolicyInformation($oppId, $fields=null){
       $oppId = str_replace("CO-","",$oppId);
        $session = Router::getRequest()->getSession();
		$login_agency_id = $session->read("Auth.User.agency_id");
		$agency = $session->read("Auth.agency_session_detail");
		$login_user_id = $session->read('Auth.User.user_id');
		$login_user_email = $session->read('Auth.User.email');
		$login_role_type= $session->read('Auth.User.role_type');
		$login_role_type_flag= $session->read('Auth.User.role_type_flag');
        $ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
		$ContactPolicyLostDetails = TableRegistry::getTableLocator()->get('ContactPolicyLostDetails');
		$ContactNotes = TableRegistry::getTableLocator()->get('ContactNotes');
		$Services = TableRegistry::getTableLocator()->get('Services');
        $ContactAcordForms = TableRegistry::getTableLocator()->get('ContactAcordForms');
        $ContactPolicyAttachments = TableRegistry::getTableLocator()->get('ContactPolicyAttachments');
		$ContactPolicyRenewal = TableRegistry::getTableLocator()->get('ContactPolicyRenewal');
        $AgencyInsuranceTypesLink = TableRegistry::getTableLocator()->get('AgencyInsuranceTypesLink');
		$ContactAdditionalInsuredPolicyRelation = TableRegistry::getTableLocator()->get('ContactAdditionalInsuredPolicyRelation');
		$Contacts = TableRegistry::getTableLocator()->get('Contacts');
		$IvansUserDetail = TableRegistry::getTableLocator()->get('IvansUserDetail');
		$checkIvansDetail = $IvansUserDetail->checkIvansAccessToAgecny($login_agency_id, $login_user_id);
		$nowcertsPushAgencyToken = TableRegistry::getTableLocator()->get('NowcertsPushAgencyToken');
		$nowCertsDetail = $nowcertsPushAgencyToken->checkAccessTokenByAgencyId($login_agency_id);
		if(empty($nowCertsDetail))
		{
			$nowCertsDetail['access_token'] = null;
			$nowCertsDetail['expires_in'] = null;
			$nowCertsDetail['refresh_token'] = null;
			$nowCertsDetail['token_issued_on'] = null;
			$nowCertsDetail['token_expires_on'] = null;
		} 
		else{
			$nowCertsDetail['access_token'] = $nowCertsDetail['access_token'];
			$nowCertsDetail['expires_in'] = $nowCertsDetail['expires_in'];
			$nowCertsDetail['refresh_token'] = $nowCertsDetail['refresh_token'];
			$nowCertsDetail['token_issued_on'] = $nowCertsDetail['token_issued_on'];
			$nowCertsDetail['token_expires_on'] = $nowCertsDetail['token_expires_on'];
		}
		$UsersTable =  	TableRegistry::getTableLocator()->get('Users');
        $contactOpportunities = $ContactOpportunities->getPolicyDetailsByOppid($oppId);
		if(isset($nowCertsDetail) && !empty($nowCertsDetail)){
			$contactOpportunity['nowCertsDetail'] = $nowCertsDetail;
		}
		if($login_role_type==_ID_AGENCY_USER && ($login_role_type_flag==_AGENCY_ROLE_PRODUCER || $login_role_type_flag==_AGENCY_ROLE_MARKETER || $login_role_type_flag==_AGENCY_ROLE_CSR)){
			$contactOpportunities['commission_amount'] = $contactOpportunities['commission_split'];
		}else{
			$contactOpportunities['commission_amount'] = $contactOpportunities['commission_amount'];
		}
		$canAccessAcord = ACCESS_ACORD_FALSE;
		if(isset($checkIvansDetail) && !empty($checkIvansDetail))
		{
			$IvansTokenDetail = TableRegistry::getTableLocator()->get('IvansAgencyTokenDetail');
			$ivansAgencyTokenDetail = $IvansTokenDetail->checkAgencyAcordAccess($login_agency_id);
			if((isset($ivansAgencyTokenDetail['is_accord_on']) && $ivansAgencyTokenDetail['is_accord_on'] == ACCESS_ACORD_TRUE )|| (isset($ivansAgencyTokenDetail['is_ivans_configured']) && $ivansAgencyTokenDetail['is_ivans_configured'] == ACCESS_ACORD_TRUE ))
			{
				$canAccessAcord = ACCESS_ACORD_TRUE;
			}

		}
		$contactOpportunity['policy'] = $contactOpportunities;
		$awsBucket = AWS_BUCKET_NAME;
		$contactOpportunity['Notes'] = $ContactNotes->getPolicyActiveNotesBeta($oppId);
		$contactOpportunity['Services'] = $Services->getAllServicesByOppIdBeta($oppId);
        $owner_name = '';
		$superAdminFlag = $session->read("Auth.User.loggedin_by_superadmin");
        if(!empty($contactOpportunities['user_id'])){
            $getuser = $UsersTable->userDetails($contactOpportunities['user_id']);
            if(!empty($getuser))
            {
                $owner_name =ucwords($getuser['first_name']);
                if(isset($getuser['last_name']) && !empty($getuser['last_name']))
                {
                    $owner_name = $owner_name." ".ucwords($getuser['last_name']);
                }
            }
        }
        $name ='';
        $email ='';
        if(!empty($contactOpportunities['contact_id'])){
        	$contact = $Contacts->contactDetails($contactOpportunities['contact_id']);
        	if(isset($contact) && !empty($contact)){
        		$name = ucwords($contact['first_name']);
				$email = $contact['email'];
        	}
        }
        $contactOpportunity['owner_name'] = $owner_name;
		$getAcordFormLists = $ContactAcordForms->getAcordFormsByOppId($oppId);
		
		$count = 0;
		$finalResponse =[];
		if(isset($getAcordFormLists) && !empty($getAcordFormLists))
		{
			//$contact_email_attachment_list = $contact['contact_attachments'];
			foreach ($getAcordFormLists as $key => $value)
			{
					$username = '--';
					if(is_array($value['user'])){
						$username = ucwords($value['user']['first_name']);
						if($value['user']['last_name'] != '')
						{
							$username = $username." ".ucwords($value['user']['last_name']);
						}
					}
                    $attach_date = '--';
                    if(isset($value['attach_date']) && !empty($value['attach_date'])){
						$attach_date = date('M d, Y', strtotime($value['attach_date']));
					}
					$created_date = '--';
					if(isset($value['created']) && !empty($value['created'])){
						$created_date = date('M d, Y', strtotime($value['created']));
					}
					if(isset($value["acord_id"]) && !empty($value["acord_id"]) && isset($value["acord_form_title"]) && !empty($value["acord_form_title"])){
						if($value['acord_form_display_name']!=''){
							$accord_form_name = $value['acord_form_display_name'];
						}else{
							$accord_form_name  = $value["acord_id"] . " " . $value["acord_form_title"];
						}
					}else{
							/* for previous form where accord title and accord number is not saved */
						$accord_form_name  = "ACORD form # " . $value["form_id"];
					}
					$form_id = '';
					if(!empty($value["form_id"])) {
						$form_id = $value["form_id"];
					} else if(!empty($value["original_form_id"])) {
						$form_id = $value["original_form_id"];
					}
					$finalResponse[$count]['id'] = $value['id'];
					$finalResponse[$count]['file'] = $accord_form_name;
					$finalResponse[$count]['user'] = $username;
					$finalResponse[$count]['attach_date'] = $attach_date;
					$finalResponse[$count]['date_added'] = $created_date;
					$finalResponse[$count]['form_id'] = $value['form_id'];
					$finalResponse[$count]['policy_guid'] = $value['policy_guid'];
					//$finalResponse[$count]['contact_email'] = base64_encode($email);
					//$finalResponse[$count]['contact_firstname'] = base64_encode($name);
					$finalResponse[$count]['acord_formname_humanized'] = base64_encode($value["acord_form_title"]);
					//$finalResponse[$count]['agent_signature'] = base64_encode($agency['company']);

					$viewUrl = _VIEW_FORM.$value["policy_guid"].'-'.$form_id.'?contact_email_address='.base64_encode($email).'&contact_firstname='.base64_encode($name).'&acord_formname_humanized='.base64_encode($value["acord_form_title"]).'&agent_signature='.base64_encode($agency['company']).'&LoginUser='.base64_encode($login_user_email).'&encoded=true';
					$finalResponse[$count]['view_url'] = $viewUrl;
                    $count++;
                }


            }
			//print_r($finalResponse); exit;
		$contactOpportunity['Acords'] = $finalResponse;
		$contactOpportunity['AcordStatus'] = $canAccessAcord;
		$info = [];
		$countAttachment = 0;
		$ContactPolicyAttachments = $ContactPolicyAttachments->getAttachmentDetailByPolicyIdBeta($oppId);
		foreach($ContactPolicyAttachments as $attachValue)
		{
			$attachmentGuid = $attachValue['contact_attachment']['attachment_guid'];
			$ext = substr(strtolower(strrchr($attachValue['name'], '.')), 1);
			$file = '';
				$businessId = $attachValue['contact_attachment']['contact_business_id'];
				$file_aws_key = $attachValue['contact_attachment']['file_aws_key'];

				$file_url_key = $attachValue['contact_attachment']['file_url'];

				if(isset($attachValue['id']) && !empty($attachValue['id']))
				{
				$id = $attachValue['id'];
				}
				if(isset($attachValue['created']) && !empty($attachValue['created']))
				{
				$created = $attachValue['created'];
				}

				if(!empty($businessId))
				{
				$folder_name = "business_";
				}else
				{
				$folder_name = "contact_";
				}

				$file_path_basic = WWW_ROOT.'uploads/'.$folder_name.$attachValue['contact_id'] .'/'. $attachValue['name'];

				$file_path_default = WWW_ROOT.'uploads/'. $attachValue['name'];
				$download_link ='';
				if(isset($file_aws_key) && !empty($file_aws_key))
				{

//					if(Aws::awsFileExists($file_aws_key))
//					{
								$file = $file_url_key;
								//temporary link set for downloads
								$bucketdata=['bucket'=>$awsBucket,
								'keyname'=>$file_aws_key];
								$download_link = (string)Aws::setPreAssignedUrl($bucketdata);
//					}

				}else if (file_exists($file_path_basic))
				{
				$file = SITEURL.'uploads/'.$folder_name.$attachValue['contact_id'] .'/'. $attachValue['name'];

				}else if (file_exists($file_path_default)) {
				$file = SITEURL.'uploads/'. $attachValue['name'];
				}

				if(isset($attachValue['display_name']) && !empty($attachValue['display_name'])){
				$display_name = $attachValue['display_name'];
				$final_display_name = strlen($display_name) > 10 ? substr($display_name,0,10)."..." : $display_name;
				}else{
					$display_name = $attachValue['name'];
				$final_display_name = strlen($display_name) > 10 ? substr($display_name,0,10)."..." : $display_name;
				}
				if(empty($download_link)){
				$download_link = $file;
				}
				if(isset($attachValue['user']['first_name']) && !empty($attachValue['user']['first_name']) && isset($attachValue['user']['last_name']) && !empty($attachValue['user']['last_name'])){
				$user_name = ucfirst($attachValue['user']['first_name']).' '.ucfirst($attachValue['user']['last_name']);
				}else{
				$user_name='';
				}

				if(isset($download_link) && !empty($download_link)){
				$download_link_new = $download_link;
				}
				$file_url = SITEURL.'attachments/previewAttachments?url='.$download_link;
				if(isset($file_url) && !empty($file_url)){
				$file_url_new = $file_url;
				}		
				$info[$countAttachment]['id'] = $id;
				$info[$countAttachment]['display_name'] = $display_name;
				$info[$countAttachment]['user_name'] = $user_name;
				$info[$countAttachment]['created'] = $created;
				$info[$countAttachment]['download_link'] = $download_link_new;
				$info[$countAttachment]['file_url_new'] = $file_url_new;
				$info[$countAttachment]['attachment_id'] = $attachValue['attachment_id'];
				$info[$countAttachment]['attachment_guid'] = $attachmentGuid;
			$countAttachment++;
		}
		$contactOpportunity['Attachments'] = $info;
		$contactOpportunity['Icon'] = $AgencyInsuranceTypesLink->getPolicyIconLInk($contactOpportunities['agency_id'],$contactOpportunities['insurance_type_id']);
		 $getAllRenewalSuccessEntry=[];
//        $getAllRenewalSuccessEntry = $ContactPolicyRenewal->getLatestRenewalSuccessByContactOppId($contactOpportunities['contact_id'],$oppId);
		$latestRenewalEntry = "";
		if(count($getAllRenewalSuccessEntry) >=2)
		{
			$contactOpportunity['renewal_data']  = $getAllRenewalSuccessEntry[0];
		}
		$contactOpportunity['ContactPolicyRenewal'] =  $getAllRenewalSuccessEntry;
		$contactOpportunity['lostPolicyDetails'] = $ContactPolicyLostDetails->getLostPolicyDetails($contactOpportunities['agency_id'],$oppId);
		$contactOpportunity['contactAdditionalInsured'] = $ContactAdditionalInsuredPolicyRelation->getAllContactAdditionalInsuredByOppId($contactOpportunities['contact_id'],$oppId);
		$contactOpportunity['ContactPolicyLostDetails'] = $ContactPolicyLostDetails->policyLostDetailsByOppID($oppId);
		$contactOpportunity['IvansUserDetail'] = $IvansUserDetail->checkIvansAccessToUser($login_agency_id,$login_user_id);
        $policy_guid = '';
        $allPolicies = ALL_POLICIES_BETA;
        if($contactOpportunities['ivans_policy_id']){
            $policy_guid = $contactOpportunities['ivans_policy_id'];
        }
        if(isset($contactOpportunity['IvansUserDetail']['ivans_agency_guid']) && !empty($contactOpportunity['IvansUserDetail']['ivans_agency_guid']) && !empty($policy_guid))
        {
            $ivans_agency_guid = $contactOpportunity['IvansUserDetail']['ivans_agency_guid'];
            if(isset($ivans_agency_guid) && !empty($ivans_agency_guid)){
//                 $requestData['API'] = _GET_POLICY_SUMMERY_BY_POLICY_ID;
//                 $requestData['request_param'] = array('policy_id'=>$policy_guid);
//                 $requestData['guid'] = $ivans_agency_guid;
//                 $requestData['agency_id'] = $login_agency_id;
//                 $policy_api_response = ContactOpportunities::getDataByApi($requestData);
//                if(isset($policy_api_response['output']['policies']['line_of_business_code_short']) && !empty($policy_api_response['output']['policies']['line_of_business_code_short'])){
//                $policy_name = $policy_api_response['output']['policies']['line_of_business_code_short'];
//                $file_name='';
//                foreach($allPolicies as $key => $value) {
//                    //print_r($key);
//                    if(in_array($policy_name, $value))
//                    {
//                        $file_name = str_replace(' ', '_', strtolower($key));
//                    }
//                }
//                if(empty($file_name)){
//                    $file_name = 'other';
//                }
//                $contactOpportunity['file_name'] = $file_name;
//            }
			// API to get commission for downloaded policies if not found in BA
			if(empty($contactOpportunities['commission_amount'])){
				 $requestData['API'] = _GET_COMMISSION_SUMMERY_BY_POLICY_NUMBER;
				 $requestData['request_param'] = array('policy_number'=>$contactOpportunities['policy_number']);
				 $requestData['guid'] = $ivans_agency_guid;
				 $requestData['agency_id'] = $login_agency_id;
				 $commission_api_response = ContactOpportunities::getDataByApi($requestData);
				 if(!empty($commission_api_response['output']['CommissionData'])){
					 if(!empty($commission_api_response['output']['CommissionData'][0]['final_commission_amount'])){
						 if($commission_api_response['output']['CommissionData'][0]['transaction_type_code'] != _IVANS_POLICY_STATUS_PCH){
							 $commission_amt =  abs($commission_api_response['output']['CommissionData'][0]['final_commission_amount']);
							 $updateOpportunities = $ContactOpportunities->updateAll(['commission_amount' =>$commission_amt],['id' => $contactOpportunities['id']]);
							 $contactPolicyRenewalData = $ContactPolicyRenewal->getContactPolicyRenewalByOpportunity($contactOpportunities['id']);
							 if($updateOpportunities){
								if(count($contactPolicyRenewalData) == _ID_SUCCESS)
								{
								   $updateRenewalOppo = $ContactPolicyRenewal->updateAll(['commission_amount' => $commission_amt], ['id' => $contactPolicyRenewalData[0]['id'], 'contact_opportunities_id' => $contactOpportunities['id']]);
								}
								else if(count($contactPolicyRenewalData) > _ID_SUCCESS)
								{
								   $contactPolicyRenewalData = $ContactPolicyRenewal->getLastRecordByOppId($contactOpportunities['id']);
								   if($contactPolicyRenewalData)
								   {
										$updateRenewalOppo = $ContactPolicyRenewal->updateAll(['commission_amount' => $commission_amt], ['id' => $contactPolicyRenewalData['id'], 'contact_opportunities_id' => $contactOpportunities['id']]);
								   }
								}
								  $contactOpportunities = $ContactOpportunities->getPolicyDetailsByOppid($oppId);
								  $contactOpportunity['policy'] = $contactOpportunities;
							 }
						 }
					 }
				 }
			}
            }
		}
		// primary insurred array
		$primaryInsured = [];
		$primaryInsuredArray = [];
		if(!empty($contactOpportunities['contact_id']))
		{
			$contactDetails = $Contacts->get($contactOpportunities['contact_id']);
			if(!empty($contactDetails))
			{
				$primaryInsured['id'] = $contactDetails->id;
				$primaryInsured['name'] = (isset($contactDetails->first_name) ? ucfirst($contactDetails->first_name) : '').' '.(isset($contactDetails->last_name) ? ucfirst($contactDetails->last_name) : '');
				$contactOpportunity['contactAddtiotnFullName'] = (isset($contactDetails->first_name) ? ucfirst($contactDetails->first_name) : '').' '.(isset($contactDetails->last_name) ? ucfirst($contactDetails->last_name) : '');
				$primaryInsured['contact_opportunity_id'] = $oppId;
				$primaryInsured['status'] = _ID_STATUS_ACTIVE;
				if(!empty($contactDetails->additional_insured_flag))
				{
					$primaryInsured['additionalInsuredFlag'] = $contactDetails->additional_insured_flag;
				}
				else
				{
					$primaryInsured['additionalInsuredFlag'] = '';
				}
				$primaryInsuredArray[] = $primaryInsured;
			}
			$contactOpportunity['insuredContactId'] = $contactOpportunities['contact_id'];
		}
		foreach($contactOpportunity['contactAdditionalInsured'] as $additionalInsured)
		{
			if(!empty($additionalInsured['additional_insured_contact_id']))
			{
				$primaryInsured['id'] = $additionalInsured['additional_insured_contact_id'];
			}
			$primaryInsured['additionalInsuredFlag'] = '';
			if($additionalInsured['contact']['additional_insured_flag'] == _ID_SUCCESS)
			{
				$primaryInsured['additionalInsuredFlag'] = $additionalInsured['contact']['additional_insured_flag'];
			} 
			else 
			{
				$primaryInsured['additionalInsuredFlag'] = '';
			}
			$primaryInsured['contact_opportunity_id'] = $additionalInsured['contact_opportunity_id'];
			$primaryInsured['status'] = $additionalInsured['status'];
			$primaryInsured['name'] = (isset($additionalInsured['contact']['first_name']) ? ucfirst($additionalInsured['contact']['first_name']) : '').' '.(isset($additionalInsured['contact']['last_name']) ? ucfirst($additionalInsured['contact']['last_name']) : '');
			$primaryInsuredArray[] = $primaryInsured;
		}
		$contactOpportunity['primaryInsuredData'] = $primaryInsuredArray;
		if($superAdminFlag != 1)
		{
			$contactOpportunity['super_admin_flag'] = true;
		}
        $return['ContactOpportunities.getPolicyInformation'] = [
            'CO-'.$oppId => $contactOpportunity
        ];
        return $return;
    }



	public function deletePolicy($oppId)
	{
		try
        {
            $myfile = fopen(ROOT."/logs/policy_error.log", "a") or die("Unable to open file!");
			$session = Router::getRequest()->getSession();
			$login_agency_id = $session->read("Auth.User.agency_id");
			$login_user_id = $session->read('Auth.User.user_id');
			$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
			$opportunity_id = str_replace("CO-","",$oppId);
			if(isset($opportunity_id) && !empty($opportunity_id))
			{

				$contact_opportunity_detail = $ContactOpportunities->contactOpportunityDetail($opportunity_id);
				$previousValues = $contact_opportunity_detail;
				if(isset($contact_opportunity_detail) && !empty($contact_opportunity_detail))
				{

					$updateOpportunities = $ContactOpportunities->updateAll(['status' =>_ID_STATUS_DELETED],['id' => $opportunity_id]);
					if($contact_opportunity_detail['pipeline_stage'] == _PIPELINE_STAGE_WON)
					{
							if($contact_opportunity_detail['primary_flag'] == _ID_STATUS_ACTIVE)
							{

								//get another active policy for business
								$active_policy_to_mark_primary = $ContactOpportunities->getConActivePolicyToMarkPrimaryAfterDeleteOrInactive($contact_opportunity_detail['contact_id']);

								if(isset($active_policy_to_mark_primary) && !empty($active_policy_to_mark_primary))
								{
									$ContactOpportunities->updateAll(['primary_flag'=>_ID_FAILED],['id'=>$opportunity_id]);
									$ContactOpportunities->updateAll(['primary_flag' =>_ID_STATUS_ACTIVE],['id' => $active_policy_to_mark_primary['id']]);
								}
							}
					}
					else if($contact_opportunity_detail['pipeline_stage'] == _PIPELINE_STAGE_LOST)
					{
						if($contact_opportunity_detail['lost_primary_flag'] == _ID_STATUS_ACTIVE)
						{

							//get another lost policy for business
							$lost_policy_to_mark_primary = $ContactOpportunities->getConLostPolicyToMarkPrimaryAfterDeleteOrInactive($contact_opportunity_detail['contact_id']);

							if(isset($lost_policy_to_mark_primary) && !empty($lost_policy_to_mark_primary))
							{
								$ContactOpportunities->updateAll(['lost_primary_flag'=>_ID_FAILED],['id'=>$opportunity_id]);
								$ContactOpportunities->updateAll(['lost_primary_flag' =>_ID_STATUS_ACTIVE],['id' => $lost_policy_to_mark_primary['id']]);
							}
						}
					}
					else
					{
						if($contact_opportunity_detail['primary_flag_all_stages'] == _ID_STATUS_ACTIVE)
						{

								//get another pending policy for business
								$pending_policy_to_mark_primary = $ContactOpportunities->getConPendingPolicyToMarkPrimaryAfterDeleteOrInactive($contact_opportunity_detail['contact_id'],$contact_opportunity_detail['pipeline_stage']);

							if(isset($pending_policy_to_mark_primary) && !empty($pending_policy_to_mark_primary))
							{
								$ContactOpportunities->updateAll(['primary_flag_all_stages'=>_ID_FAILED],['id'=>$opportunity_id]);
								$ContactOpportunities->updateAll(['primary_flag_all_stages' =>_ID_STATUS_ACTIVE],['id' => $pending_policy_to_mark_primary['id']]);
							}
						}
					}


					if($updateOpportunities){
						$opportunityDetail = $ContactOpportunities->contactOpportunityDetail($opportunity_id);
						$newValues = $opportunityDetail;
						$changesDetailsArr = CommonFunctions::createContactOpportunitiesLogsArr($previousValues,$newValues);
						$policyArr = [];
						$policyArr['agency_id'] = $login_agency_id;
						$policyArr['user_id'] = $login_user_id;
						$policyArr['contact_opportunity_id'] = $opportunityDetail['id'];
						$policyArr['type'] = _POLICY_LOG_TYPE_CHANGE;
						$policyArr['change_details'] = $changesDetailsArr;
						$policyArr['triggered'] = 'User deleted policy manually';
						$policyArr['platform'] = _PLATFORM_TYPE_SYSTEM;
						$policyArr['event_details'] = "deletePolicy";
						ContactOpportunityChanger::applyPolicyChanges($opportunityDetail['id'],$policyArr);
						CommonFunctions::saveDeletedPolicyLogForContact($contact_opportunity_detail);
						CommonFunctions::savePolicyLogForContact($opportunity_id);
						$response = json_encode(array('status' => _ID_SUCCESS));
					}else{
						$response = json_encode(array('status' => _ID_FAILED));
					}

				}else{
					$response = json_encode(array('status' => _ID_FAILED));
				}
			}else{
				$response = json_encode(array('status' => _ID_FAILED));
			}


		}catch (\Exception $e) {

				$txt=date('Y-m-d H:i:s').' :: Policy not deleted- '.$e->getMessage();
				fwrite($myfile,$txt.PHP_EOL);
		}
		return $response;
	}

	public static function getInsuranceTypes($contact_id)
	{
		$session = Router::getRequest()->getSession();
		$login_user_id = $session->read("Auth.User.user_id");
		$login_agency_id = $session->read("Auth.User.agency_id");
		//echo $login_agency_id;die("sdfds");
		$insuranceTypes = TableRegistry::getTableLocator()->get('InsuranceTypes');
		$getPersonalPolicyListing = $insuranceTypes->insuranceListByAgencyIdPersonal($login_agency_id);
		$return['ContactOpportunities.getInsuranceTypes'] = [
            $contact_id=> $getPersonalPolicyListing
        ];
        return $return;
		//echo "<pre>";print_r($getPersonalPolicyListing);die("sdfsd");
	}

	public static function saveNewPolicy($objectData)
	{
			try
        {

			$myfile = fopen(ROOT."/logs/policyerror.log", "a") or die("Unable to open file!");
			$session = Router::getRequest()->getSession();
            $login_user_id = $session->read("Auth.User.user_id");
            $login_agency_id = $session->read("Auth.User.agency_id");
			$login_role_type = $session->read('Auth.User.role_type');
			$login_role_type_flag = $session->read('Auth.User.role_type_flag');
			$login_permissions = $session->read('Auth.User.permissions');
			$login_permissions_arr  =   explode(",",$login_permissions);

            $ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
			$Contacts = TableRegistry::getTableLocator()->get('Contacts');
			$InsuranceTypes = TableRegistry::getTableLocator()->get('InsuranceTypes');
			$AgencyCampaignMaster = TableRegistry::getTableLocator()->get('AgencyCampaignMaster');
			$ContactPolicyRenewal = TableRegistry::getTableLocator()->get('ContactPolicyRenewal');
			$CampaignRunningSchedule = TableRegistry::getTableLocator()->get('CampaignRunningSchedule');
			$CampaignRunningEmailSmsSchedule = TableRegistry::getTableLocator()->get('CampaignRunningEmailSmsSchedule');
			$contactDetail="";
			$assign_owner_id="";
			$can_start_client_welcome_campaign_flag=0;
			$usersTimezone='America/Phoenix';

			//echo "<pre>";print_r($objectData);die("fsdf");
			if(isset($objectData['contact_id']) && !empty($objectData['contact_id']))
			{
				$contactDetail = $Contacts->contactDetails($objectData['contact_id']);
				$assign_owner_id = $contactDetail['user_id'];
			}

			if(empty($assign_owner_id))
			{
				$assign_owner_id = $login_user_id;
			}
			$contact_opportunities_info = [];
			if(isset($objectData['insurance_type_id']) && !empty($objectData['insurance_type_id']))
			{
				$contact_opportunities_info['insurance_type_id'] = $objectData['insurance_type_id'];
			}

			$insurance_type_details = $InsuranceTypes->insuranceDetails($$objectData['insurance_type_id']);

			if(isset($objectData['policy_number']) && !empty($objectData['policy_number']))
			{
				$contact_opportunities_info['policy_number'] = $objectData['policy_number'];
			}

			if(isset($objectData['effective_date']) && !empty($objectData['effective_date']))
			{
				$contact_opportunities_info['effective_date'] = date('Y-m-d',strtotime($objectData['effective_date']));
			}
			// term length adjustment starts
			$totaltermlength = 0;
			if(isset($objectData['term_length']) && !empty($objectData['term_length']))
			{
				if($objectData['term_length'] == 'other'){
					if(isset($objectData['term_length_period']) && !empty($objectData['term_length_period']))
					{
						$contact_opportunities_info['term_length_period'] = 2;
						$totaltermlength =  $objectData['term_length_period']*12;
					}
					else{
						$contact_opportunities_info['term_length_period'] = 1;
						$totaltermlength =0;
					}
					if(isset($objectData['term_length_text']) && !empty($objectData['term_length_text']))
					{
						$totaltermlength =  $totaltermlength+$objectData['term_length_text'];
					}
					else{
						$totaltermlength =$totaltermlength;
					}
					if($totaltermlength !=0){
						$contact_opportunities_info['term_length'] = $totaltermlength;
					}

				}else{
					$contact_opportunities_info['term_length']=$objectData['term_length'];
				}
			}


			// term length adjustment ends
			if(isset($objectData['carrier_id']) && !empty($objectData['carrier_id']))
			{
				$contact_opportunities_info['carrier_id']=$objectData['carrier_id'];
			}
			if(isset($objectData['premium_amount_won']) && !empty($objectData['premium_amount_won']))
			{
				$contact_opportunities_info['premium_amount']=CommonFunctions::Replacetext($objectData['premium_amount_won']);
			}

			if(isset($objectData['mga_id']) && !empty($objectData['mga_id']))
			{
				$contact_opportunities_info['mga_id'] = $objectData['mga_id'];
			}

			if($login_role_type==_ID_AGENCY_ADMIN || $login_role_type_flag == _AGENCY_ROLE_ADMIN || $login_role_type_flag == _AGENCY_ROLE_MANAGER || in_array(76, $login_permissions_arr)){
				if(!empty($objectData['commission_amount']))
				{
					$contact_opportunities_info['commission_amount']=CommonFunctions::Replacetext($objectData['commission_amount']);
				}
				if(!empty($objectData['commission_amount_split_display']))
				{
					$contact_opportunities_info['commission_split']=CommonFunctions::Replacetext($objectData['commission_amount_split_display']);
				}
			}else{

				if(!empty($objectData['commission_amount_agency']))
				{
					$contact_opportunities_info['commission_amount'] = CommonFunctions::Replacetext($objectData['commission_amount_agency']);
				}
				if(!empty($objectData['commission_amount_split_display']))
				{
					$contact_opportunities_info['commission_split'] = CommonFunctions::Replacetext($objectData['commission_amount_split_display']);
				}

			}
			$contact_opportunities_info['agency_id'] = $login_agency_id;
			$contact_opportunities_info['user_id'] = $assign_owner_id;
			$contact_opportunities_info['contact_id'] = $objectData['contact_id'];

			$pipeline_stage = '';
			if(isset($objectData['new_opportunity_pipeline_stage']) && !empty($objectData['new_opportunity_pipeline_stage']) && !empty($objectData['add_new_opportunity']) && $objectData['add_new_opportunity']==_ID_STATUS_ACTIVE)
			{
				$contact_opportunities_info['pipeline_stage']=$objectData['new_opportunity_pipeline_stage'];
				if($objectData['new_opportunity_pipeline_stage'] ==_PIPELINE_STAGE_WON){
					$contact_opportunities_info['status']=_ID_STATUS_ACTIVE;
					$contact_opportunities_info['won_date']=date('Y-m-d');
					$pipeline_stage = _PIPELINE_STAGE_WON;
					 if(date('Y-m-d' ,strtotime($contact_opportunities_info['effective_date'])) <= date('Y-m-d')){
                    $contact_opportunities_info['pending_sub_status']='';
                    $contact_opportunities_info['active_sub_status']=_ID_ACTIVE_SUB_STATUS_ACTIVE;
					}elseif(date('Y-m-d' ,strtotime($contact_opportunities_info['effective_date'])) > date('Y-m-d'))
					{
						$contact_opportunities_info['pending_sub_status']='';
						$contact_opportunities_info['active_sub_status'] = _ID_ACTIVE_SUB_STATUS_FUTURE_EFFECTIVE;
					}
				}
				else if($objectData['new_opportunity_pipeline_stage'] ==_PIPELINE_STAGE_LOST){
					$contact_opportunities_info['lost_date']=date('Y-m-d');
					$contact_opportunities_info['status']=_ID_STATUS_INACTIVE;
                	$contact_opportunities_info['pending_sub_status']=_ID_PENDING_SUB_STATUS_LOST;
					$contact_opportunities_info['active_sub_status'] = NULL;
				}
				else if($objectData['new_opportunity_pipeline_stage'] ==_PIPELINE_STAGE_QUOTE_SENT){
					$contact_opportunities_info['status']=_ID_STATUS_PENDING;
					$contact_opportunities_info['quote_sent_date']=date('Y-m-d');
				}
				else{
					$contact_opportunities_info['status']=_ID_STATUS_PENDING;
					if($objectData['new_opportunity_pipeline_stage'] ==_PIPELINE_STAGE_NEW_LEAD || $objectData['new_opportunity_pipeline_stage'] ==_PIPELINE_STAGE_WORKING || $objectData['new_opportunity_pipeline_stage'] ==_PIPELINE_STAGE_APPOINTMENT_SCHEDULED){
						$contact_opportunities_info['pending_sub_status']=_ID_PENDING_SUB_STATUS_NEW_LEAD;
					}elseif($pipeline_stage ==_PIPELINE_STAGE_QUOTE_READY){
						$contact_opportunities_info['pending_sub_status']=_ID_PENDING_SUB_STATUS_QUOTED;
					}
				}
			}
			else
			{
				$pipeline_stage = _PIPELINE_STAGE_WON;
				$contact_opportunities_info['pipeline_stage']=_PIPELINE_STAGE_WON;
				$contact_opportunities_info['status']=_ID_STATUS_ACTIVE;
				$contact_opportunities_info['active_sub_status']=_ID_ACTIVE_SUB_STATUS_ACTIVE;
				$contact_opportunities_info['won_date']=date('Y-m-d');
			}
			//code to start the client welcome campagin start
			if(isset($pipeline_stage) && !empty($pipeline_stage) && $pipeline_stage == _PIPELINE_STAGE_WON)
			{


				if($contactDetail['client_welcome_campaign_flag']==_STATUS_FALSE)
				{
					$active_inactive_contact_opp = $ContactOpportunities->getAllActiveCancelledOpportunitiesByContactId($contactDetail['id']);
					if(empty($active_inactive_contact_opp))
					{
					$checkCampaignExist = $AgencyCampaignMaster->checkCampaignExist(null,null,$login_agency_id,_CLIENT_WELCOME_STAGE_INITIATE,_CAMPAIGN_TYPE_CLIENT_WELCOME);
					if(!empty($checkCampaignExist)){
						$can_start_client_welcome_campaign_flag=1;
					}
					}
				}

				//code to start the client welcome campagin end
				// status depends on effective date and term length
				if(isset($objectData['effective_date']) && !empty($objectData['effective_date']) ||
				isset($objectData['term_length']) && !empty($objectData['term_length']) )
				{

					if(isset($objectData['effective_date']) && !empty($objectData['effective_date'])){
						$effectiveDate = $objectData['effective_date'];
					}else{
						$effectiveDate = date('Y-m-d',strtotime(CommonFunctions::convertUtcToEmployeeTimeZone($usersTimezone,date("Y-m-d"))));
					}


					$statusTermLength = 0;
					if($objectData['term_length'] == 'other'){
						if(isset($objectData['term_length_period']) && !empty($objectData['term_length_period']))
						{
							$statusTermLength =  $objectData['term_length_period']*12;
						}
						else{
							$statusTermLength =0;
						}
						if(isset($objectData['term_length_text']) && !empty($objectData['term_length_text']))
						{
							$statusTermLength =  $statusTermLength+$objectData['term_length_text'];
						}
						else{
							$statusTermLength =$statusTermLength;
						}
					}else{
						if(isset($objectData['term_length']) && !empty($objectData['term_length'])){
							$statusTermLength = $objectData['term_length'];
						}else{
							$statusTermLength = 0;
						}
					}
					$currentDate = date('Y-m-d',strtotime(CommonFunctions::convertUtcToEmployeeTimeZone($usersTimezone,date("Y-m-d"))));
					$effectiveDate = date('Y-m-d', strtotime("+".$statusTermLength." months", strtotime($effectiveDate)));
					if($currentDate > $effectiveDate){
						$contact_opportunities_info['status'] = _ID_STATUS_INACTIVE;
					}else{
						$contact_opportunities_info['status']=_ID_STATUS_ACTIVE;
					}
				}
			}


			if($objectData['is_existing_business']==_ID_STATUS_ACTIVE){
				$contact_opportunities_info['hawksoft_renewal_status']=_ID_STATUS_ACTIVE;
			}
			//echo "<pre>";print_r($contact_opportunities_info);die("fsdf");
			//platform update
			$contact_opportunities_info['platform'] = _PLATFORM_TYPE_SYSTEM;
			//
			$contact_opportunities = $ContactOpportunities->newEntity();
			$contact_opportunities = $ContactOpportunities->patchEntity($contact_opportunities, $contact_opportunities_info);
			if($contact_opportunities = $ContactOpportunities->save($contact_opportunities))
			{
				// save logs when new policy is created.
				CommonFunctions :: saveNewPolicyLogForContact( $contact_opportunities->id );
				//make primary_flag_all_stages true

				if(isset($contact_opportunities->contact_id) && !empty($contact_opportunities->contact_id))
				{
					$ContactOpportunities->updateAll(['primary_flag_all_stages'=>_ID_STATUS_PENDING],['contact_id' => $contact_opportunities->contact_id,'pipeline_stage'=>$contact_opportunities->pipeline_stage,'agency_id'=>$login_agency_id,'contact_business_id IS NULL']);
					$ContactOpportunities->updateAll(['primary_flag_all_stages'=>_ID_STATUS_ACTIVE],['id' => $contact_opportunities->id]);
				}
				//end

				// update primary_flag
				if(isset($objectData['new_opportunity_pipeline_stage']) && !empty($objectData['new_opportunity_pipeline_stage']) && !empty($objectData['add_new_opportunity']))
				{
					if($objectData['new_opportunity_pipeline_stage'] !=_PIPELINE_STAGE_WON){

						$ContactOpportunities->updateAll(['primary_flag_all_stages'=>_ID_FAILED],['contact_id'=>$contact_opportunities->contact_id,'pipeline_stage'=>$objectData['new_opportunity_pipeline_stage'],'contact_business_id IS NULL']);


						$ContactOpportunities->updateAll(['primary_flag_all_stages'=>_ID_SUCCESS],['id'=>$contact_opportunities->id]);
					}

				}

				// create Post Binding Obligations  Tasks
				if($contact_opportunities->pipeline_stage==_PIPELINE_STAGE_WON){

					$post_Obligations = [];
					$post_Obligations['contact_id'] =$contact_opportunities->contact_id ;
					$post_Obligations['contact_business_id'] =$contact_opportunities->contact_business_id ;
					$post_Obligations['agency_id'] =$contact_opportunities->agency_id ;
					$post_Obligations['user_id'] =$contact_opportunities->user_id ;
					$post_Obligations['insurance_type_id'] =$contact_opportunities->insurance_type_id ;
					$post_Obligations['carrier_id'] =$contact_opportunities->carrier_id ;
					$post_Obligations['effective_date'] =$contact_opportunities->effective_date ;
					$post_Obligations['policy_number'] =$contact_opportunities->policy_number;
					$post_Obligations['opportunity_id'] =$contact_opportunities->id;
					CommonFunctions::addPostObligationTask($post_Obligations);

				}// end Post Binding Obligations  Tasks

				//save stage move date
				$ContactOpportunities->updateAll(['date_stage_moved' =>date('Y-m-d H:i:s'),'sort_order'=>$contact_opportunities->id],['id' => $contact_opportunities->id]);
				//below code is used to insert a one entry in ContactPolicyRenewal table
				if(isset($pipeline_stage) && !empty($pipeline_stage) && $pipeline_stage == _PIPELINE_STAGE_WON)
				{

					//set lead type client

					$Contacts->updateAll(['lead_type' => _CONTACT_TYPE_CLIENT],['id' => $contact_opportunities->contact_id]);
					// set client since date
					if($contactDetail['client_since'] == null && $contact_opportunities['effective_date'] != null){
						$Contacts->updateAll(['client_since' => date('Y-m-d',strtotime($contact_opportunities['effective_date']))], ['id' => $contact_opportunities->contact_id, 'client_since is null']);
					}
					//code to make the first opportunity primary start here
					$ContactOpportunities->updateAll(['primary_flag'=>_ID_FAILED],['contact_id'=>$contact_opportunities->contact_id,'contact_business_id is null']);


					$ContactOpportunities->updateAll(['primary_flag'=>_ID_SUCCESS],['id'=>$contact_opportunities->id]);
					//


					//save commission_split_percentage
					CommonFunctions::updateOwnerCommisionSplitPercentage($contact_opportunities->id);

					//check for entry in contact policy renewal table
					$contactPolicyRenewalDetail = $ContactPolicyRenewal->getContactPolicyRenewalByContactAndOpportunity($contact_opportunities['contact_id'],$contact_opportunities['id']);
					if(empty($contactPolicyRenewalDetail))
					{
						$data = [];
						$data['agency_id']=$contact_opportunities['agency_id'];
						$data['contact_id']=$contact_opportunities['contact_id'];
						$data['contact_opportunities_id']=$contact_opportunities['id'];
						$data['renewal_date']=$contact_opportunities['effective_date'];
						$data['renewal_amount']=$contact_opportunities['premium_amount'];
						$data['amount_received_date']=date('Y-m-d');
						$data['stage']=_RENEWAL_STAGE_SUCCESS;
						$data['term_length']=$contact_opportunities['term_length'];
						$data['term_length_period']=$contact_opportunities['term_length_period'];
						$data['carrier_id']=$contact_opportunities['carrier_id'];
						$data['premium_amount']=$contact_opportunities['premium_amount'];
						$data['commission_amount']=$contact_opportunities['commission_amount'];
						$data['policy_number']=$contact_opportunities['policy_number'];
						$data['commission_split']=$contact_opportunities['commission_split'];
						$data['commission_split_percentage']=$contact_opportunities['commission_split_percentage'];
						$contact_policy_renewal = $ContactPolicyRenewal->newEntity();
						$contact_policy_renewal = $ContactPolicyRenewal->patchEntity($contact_policy_renewal, $data);
						$contact_policy_renewal = $ContactPolicyRenewal->save($contact_policy_renewal);
						$message = 'in function saveNewPolicy, $info = ' . json_encode($data) . ' contact_opportunities_id '. $contact_opportunities['id'] . 'for  contact id ' . $contact_opportunities['contact_id'] . ' and agency is ' . $contact_opportunities['agency_id'];
						FileLog::writeLog("contact_policy_renewal_dup_entries", $message);
					}
					//create renewal entry if renewal criteria matches
					CommonFunctions::createRenewalServiceEntry($contact_opportunities);
					//stop the pipeline campaign if running

					$campaignRunningSchedules = $CampaignRunningSchedule->getRunningCampaignListingByContactID($contact_opportunities['contact_id']);

					//print_r($campaignRunningSchedules);die;
					if(!empty($campaignRunningSchedules))
					{
						foreach ($campaignRunningSchedules as $campaignRunningSchedule)
						{
							//print_r($campaign['type']);die;
							if($campaignRunningSchedule['agency_campaign_master']['type'] == _CAMPAIGN_TYPE_PIPELINE || $campaignRunningSchedule['agency_campaign_master']['type'] == _CAMPAIGN_TYPE_NEW_LEAD || $campaignRunningSchedule['agency_campaign_master']['type'] == _CAMPAIGN_TYPE_GENERAL_NEW_LEAD)
							{
								$CampaignRunningEmailSmsSchedule->updateCampaignRunningEmailSmsSchedule(['status' => _EMAIL_SMS_SENT_STATUS_CANCELLED],['campaign_running_schedule_id' => $campaignRunningSchedule['id'],'status'=>_EMAIL_SMS_SENT_STATUS_PENDING]);
								$CampaignRunningSchedule->updateCampaignRunningSchedule(['status' => _RUNNING_CAMPAIGN_STATUS_COMPLETED],['id'=>$campaignRunningSchedule['id'],'status'=>_RUNNING_CAMPAIGN_STATUS_ACTIVE]);
							}
						}
					}

				}
				$Contacts->updateAll(['order_by_recent'=>date('Y-m-d H:i:s')],['id'=>$objectData['contact_id']]);

				// $new_policy_html = '<div class="policy-name"><p><a href="javascript:void(0)" onclick="showPolicyDetails('.$this->request->data['contact_id_n'].','.$contact_opportunities->id.')">'.ucfirst($insurance_type_details['type']).'</a></p><p class="days-left">Active</p></div>';

				if(isset($objectData['insurance_type_id']) && !empty($objectData['insurance_type_id']))
				{
					//if cross sell campaign is running then stop it
					$checkCampaignExist = $AgencyCampaignMaster->checkCampaignExist(null,$objectData['insurance_type_id'],$login_agency_id,_CROSS_SELL_STAGE_INITIATE,_CAMPAIGN_TYPE_CROSS_SELL);
					if(!empty($checkCampaignExist)){
						$campaignRunningSchedule = $CampaignRunningSchedule->getCampaignDetailsByContactIDAndCampaignId($objectData['contact_id'],$checkCampaignExist['id']);
						if(isset($campaignRunningSchedule) && !empty($campaignRunningSchedule))
						{
							$CampaignRunningSchedule->updateAll(['status'=>_RUN_SCHEDULE_STATUS_INACTIVE],['id'=>$campaignRunningSchedule['id'],'status'=>_RUN_SCHEDULE_STATUS_ACTIVE]);
							$CampaignRunningEmailSmsSchedule->updateAll(['status'=>_EMAIL_SMS_SENT_STATUS_CANCELLED],['   campaign_running_schedule_id'=>$campaignRunningSchedule['id'],'status'=>_EMAIL_SMS_SENT_STATUS_PENDING]);
						}
					}
				}
				// check x-date campaign exit 
				$checkXdateCampaignExist = $CampaignRunningSchedule->getXDateCampaignActiveContact($contact_opportunities['contact_id'], null, _CAMPAIGN_TYPE_LONG_TERM_NURTURE);
				if(empty($checkXdateCampaignExist))
				{
					$checkXdateCampaignExist = $CampaignRunningSchedule->getXDateCampaignActiveContact($contact_opportunities['contact_id'],null, _CAMPAIGN_TYPE_X_DATE);
				}
				$xDateCampaignId = '';
				if(!empty($checkXdateCampaignExist) && $pipeline_stage != _PIPELINE_STAGE_LOST)
				{
					$puaseXdateCampaign = CommonFunctions :: xdateCampaignStop($checkXdateCampaignExist);
					if($puaseXdateCampaign['status'] == _ID_SUCCESS)
                	{
						$xDateCampaignId = $puaseXdateCampaign['campaign_running_schedule_id'];
                    	FileLog::writeLog("stop_x_date_campaign", "Stop x date successfully contact id:- " . $contact_opportunities['contact_business_id']);
                	}
				}
				NowCertsApi::updateIntoNowcerts($contact_opportunities['contact_id'], null, $contact_opportunities['id'], null);
				$response = json_encode(array('status' => _ID_SUCCESS, 'pipeline_stage'=>$pipeline_stage, 'saved_opp_id'=>$contact_opportunities['id'], 'x_date_campaign_id' => $xDateCampaignId));
			}else{
				$response = json_encode(array('status' => _ID_FAILED));
			}
			return $response;
		}catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: Policy save Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }

	}

	public static function getPoliciesContact($contactId, $fields=null){

        $ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
		$ContactNotes = TableRegistry::getTableLocator()->get('ContactNotes');
		$Services = TableRegistry::getTableLocator()->get('Services');
        $ContactAcordForms = TableRegistry::getTableLocator()->get('ContactAcordForms');
        $ContactPolicyAttachments = TableRegistry::getTableLocator()->get('ContactPolicyAttachments');
        $AgencyInsuranceTypesLink = TableRegistry::getTableLocator()->get('AgencyInsuranceTypesLink');
        $contactOpportunitiesActive = $ContactOpportunities->getMultiPolicyActiveListing($contactId);
        $contactOpportunitiesInactive = $ContactOpportunities->getMultiPolicyInActiveListing($contactId);
        $contactOpportunitiesPending = $ContactOpportunities->getMultiPolicyPendingListing($contactId);
		if(isset($contactOpportunitiesActive) && !empty($contactOpportunitiesActive))
		{
			foreach($contactOpportunitiesActive as $contactOpportunity){
            $return['Active']['CO-' . $contactOpportunity->id] = $contactOpportunity;
			$return['Active']['CO-' . $contactOpportunity->id]['Notes'] = $ContactNotes->getPolicyActiveNotes($contactOpportunity->id);
			$return['Active']['CO-' . $contactOpportunity->id]['Services'] = $Services->getAllServicesByOppId($contactOpportunity->id);
			$return['Active']['CO-' . $contactOpportunity->id]['Acords'] = $ContactAcordForms->getAcordFormsByOppId($contactOpportunity->id);
			$return['Active']['CO-' . $contactOpportunity->id]['Attachments'] = $ContactPolicyAttachments->getAttachmentDetailByPolicyId($contactOpportunity->id);
			$return['Active']['CO-' . $contactOpportunity->id]['Icon'] = $AgencyInsuranceTypesLink->getPolicyIconLInk($contactOpportunity->agency_id,$contactOpportunity->insurance_type_id);
			}
		}

		if(isset($contactOpportunitiesInactive) && !empty($contactOpportunitiesInactive))
		{
			foreach($contactOpportunitiesInactive as $contactOpportunity){
            $return['InActive']['CO-' . $contactOpportunity->id] = $contactOpportunity;
			$return['InActive']['CO-' . $contactOpportunity->id]['Notes'] = $ContactNotes->getPolicyActiveNotes($contactOpportunity->id);
			$return['InActive']['CO-' . $contactOpportunity->id]['Services'] = $Services->getAllServicesByOppId($contactOpportunity->id);
			$return['InActive']['CO-' . $contactOpportunity->id]['Acords'] = $ContactAcordForms->getAcordFormsByOppId($contactOpportunity->id);
			$return['InActive']['CO-' . $contactOpportunity->id]['Attachments'] = $ContactPolicyAttachments->getAttachmentDetailByPolicyId($contactOpportunity->id);
			$return['InActive']['CO-' . $contactOpportunity->id]['Icon'] = $AgencyInsuranceTypesLink->getPolicyIconLInk($contactOpportunity->agency_id,$contactOpportunity->insurance_type_id);
			}
		}
		if(isset($contactOpportunitiesPending) && !empty($contactOpportunitiesPending))
		{
			foreach($contactOpportunitiesPending as $contactOpportunity){
            $return['Pending']['CO-' . $contactOpportunity->id] = $contactOpportunity;
			$return['Pending']['CO-' . $contactOpportunity->id]['Notes'] = $ContactNotes->getPolicyActiveNotes($contactOpportunity->id);
			$return['Pending']['CO-' . $contactOpportunity->id]['Services'] = $Services->getAllServicesByOppId($contactOpportunity->id);
			$return['Pending']['CO-' . $contactOpportunity->id]['Acords'] = $ContactAcordForms->getAcordFormsByOppId($contactOpportunity->id);
			$return['Pending']['CO-' . $contactOpportunity->id]['Attachments'] = $ContactPolicyAttachments->getAttachmentDetailByPolicyId($contactOpportunity->id);
			$return['Pending']['CO-' . $contactOpportunity->id]['Icon'] = $AgencyInsuranceTypesLink->getPolicyIconLInk($contactOpportunity->agency_id,$contactOpportunity->insurance_type_id);
			}
		}
		$return['ContactOpportunities.getPoliciesContact'] = [
            $contactId => $return
        ];

        return $return;

    }

	/**
     * Business Commission Details
     *
     */
    public function getCommisionAmount($objectData)
    {
		$session = Router::getRequest()->getSession();
		$login_user_id = $session->read("Auth.User.user_id");
		$login_agency_id = $session->read("Auth.User.agency_id");

		$login_role_type = $session->read('Auth.User.role_type');
		$login_role_type_flag = $session->read('Auth.User.role_type_flag');

		$insurance_type_id = $objectData['insurance_type_id'];
		$carrier_id = $objectData['carrier_id'];
		$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
		$Roles = TableRegistry::getTableLocator()->get('Roles');
		$UserLinks = TableRegistry::getTableLocator()->get('UserLinks');
		$Users = TableRegistry::getTableLocator()->get('Users');
		$AgencyBusinessCommission = TableRegistry::getTableLocator()->get('AgencyBusinessCommission');
		$Contacts = TableRegistry::getTableLocator()->get('Contacts');
      	//get user commission split percentage

		if(isset($objectData['contact_id']) && !empty($objectData['contact_id']))
		{
			$contactDetail = $Contacts->find('all')->where(['id'=>$objectData['contact_id']])->first();
			if(isset($contactDetail['user_id']) && !empty($contactDetail['user_id']))
			{
				$owner_id = $contactDetail['user_id'];
			}else{
				$owner_id=$login_user_id;
			}
		}else{
			$owner_id=$login_user_id;
		}



		//get owner role type
		$userLinksArr = $UserLinks->find('all')->where(['user_id'=>$owner_id])->first();
		$owner_role_type=$userLinksArr['role_type'];
		//get role_type_flag if user is agency staff
		$owner_role_type_flag=0;
		if($userLinksArr['role_type']==_ID_AGENCY_USER){
			$roleDetails=$Roles->roleDetails($userLinksArr['role_id']);
			$owner_role_type_flag=$roleDetails['role_type_flag'];
		}

		$userDetails=$Users->userDetails($owner_id);
		$commission_split_percentage=0;
		if(isset($userDetails['new_business_comm_percentage']) && !empty($userDetails['new_business_comm_percentage'])){
			$commission_split_percentage=$userDetails['new_business_comm_percentage'];
		}

      	//get business commission
        $commission_new="";
        $commission_agency="";
        $commission_split="";
        if(!empty($insurance_type_id) && !empty($carrier_id))
		{
          	$businessCommissionDetails=$AgencyBusinessCommission->businessCommissionDetails($login_agency_id,$insurance_type_id,$carrier_id);
          	//
          	if(isset($businessCommissionDetails['commission_new']) && !empty($businessCommissionDetails['commission_new'])){

            	$commission_new=$businessCommissionDetails['commission_new'];
            	$commission_agency=$businessCommissionDetails['commission_new'];//agency commission
				if($login_role_type==_ID_AGENCY_USER && ($login_role_type_flag==_AGENCY_ROLE_PRODUCER || $login_role_type_flag==_AGENCY_ROLE_MARKETER || $login_role_type_flag==_AGENCY_ROLE_CSR)){

					if(!empty($businessCommissionDetails['commission_new']) && !empty($commission_split_percentage)){
						$commission_new=($businessCommissionDetails['commission_new']*$commission_split_percentage)/100;//producer commission
					}
				}

				//commission split
				if(!empty($businessCommissionDetails['commission_new']) && !empty($commission_split_percentage)){
					$commission_split=($businessCommissionDetails['commission_new']*$commission_split_percentage)/100;//producer commission
				}
          	}
        }

      	$response =  json_encode(array('status' => _ID_SUCCESS,'commission_new' => $commission_new,'commission_agency' => $commission_agency,'commission_split' => $commission_split));
      	return $response;
  	}

	public static function getPolicyTypesByContactId($contactId, $fields=null)
	{
		$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
		$virtualFields =
           ([
           'opp_insurance_type_id' => "CONCAT(ContactOpportunities.insurance_type_id, '-', ContactOpportunities.id)",
           'policy_number'=>"ContactOpportunities.policy_number",
           'id'=>"ContactOpportunities.id",
           'type'=>"insurance_types.type",
		   "opp_id"=> "CONCAT('CO-',ContactOpportunities.id)",
		   "insurance_type_id"=> "ContactOpportunities.insurance_type_id",
           ]
       );
       $result= $ContactOpportunities->find()->select($virtualFields)->leftJoin(['insurance_types'],['ContactOpportunities.insurance_type_id=insurance_types.id'])->where(['ContactOpportunities.contact_id' => $contactId,'insurance_types.id IS NOT NULL'])->hydrate(false)->toArray();
		$return['ContactOpportunities.getPolicyTypesByContactId'] = [
            $contactId => $result
        ];

        return $return;
	}

	//This function is used in ba-policy_card while edit policy or save coverage data and in ba-pipeline-stage while edit policy or add new quote
	public static function  updatePolicyDetails($objectData)
	{

		try
		{
			$totaltermlength = 0;
			$term_length = "--";
			$currentDate = date('Y-m-d');
			$usersTimezone='America/Phoenix';
			$session = Router::getRequest()->getSession();
			$login_user_id= $session->read("Auth.User.user_id");
			$login_agency_id= $session->read("Auth.User.agency_id");
			$login_role_type= $session->read('Auth.User.role_type');
			$login_role_type_flag= $session->read('Auth.User.role_type_flag');
			$opportunity_id = isset($objectData['opp_id']) && $objectData['opp_id']!==null ? str_replace("CO-","",$objectData['opp_id']) :null;
			$primary_contact_id = isset($objectData['contact_id']) && $objectData['contact_id']!==null ?  $objectData['contact_id'] : null;
			if(isset($objectData['premium_amount']) && !empty($objectData['premium_amount']) && $objectData['premium_amount'] != "--")
			{
				$objectData['premium_amount'] = str_replace("$","",$objectData['premium_amount']);
			}
			if(isset($objectData['lost_policy_premium_text']) && !empty($objectData['lost_policy_premium_text']) && $objectData['lost_policy_premium_text'] != "--")
			{
				$objectData['lost_policy_premium_text'] = str_replace("$","",$objectData['lost_policy_premium_text']);
			}
			// dd($objectData);
			$insurance_type_id = $objectData['insurance_type_id'];
			$carrier_id = $objectData['carrier_id'];
			$commission_data=[
				'insurance_type_id' => $insurance_type_id,
				'carrier_id' => $carrier_id,
			];
			$commission_details = ContactOpportunities::getCommisionAmount($commission_data);
			$commission_amt = '';
			if(!empty($commission_details)){
				$decoded_commission_details  = json_decode($commission_details);
				$commission_amt = ($objectData['premium_amount']*$decoded_commission_details->commission_agency)/100; 
			}

			$data =[];
			$response =[];
			$data['agency_id'] = $login_agency_id;
			$contactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
			$policyInsuredLogs = TableRegistry::getTableLocator()->get('PolicyInsuredLogs');
			$carriers = TableRegistry::getTableLocator()->get('Carriers');
			$contactAdditionalInsuredPolicyRelation = TableRegistry::getTableLocator()->get('ContactAdditionalInsuredPolicyRelation');
			if(isset($objectData['term_length']) && !empty($objectData['term_length']) && $objectData['term_length'] !=='--' && $objectData['term_length'] !== 0)
			{
				  if($objectData['term_length'] == 'other')
				  {
					if(isset($objectData['term_length_period']) && !empty($objectData['term_length_period']))
					{
						$data['term_length_period'] = 2;
						$totaltermlength =  $objectData['term_length_period']*12;
					}
					else
					{
						$data['term_length_period'] = 1;
						$totaltermlength = 0;
					}
					if(isset($objectData['term_length_text']) && !empty($objectData['term_length_text']))
					{
						$totaltermlength =  $totaltermlength+$objectData['term_length_text'];

					}
					else
					{
						$totaltermlength =$totaltermlength;

					}
					if($totaltermlength !=0)
					{
						$data['term_length'] = $totaltermlength;
						$term_length = $totaltermlength;
					}

				}
				else
				{
					$data['term_length'] = $objectData['term_length'];
					$term_length = $objectData['term_length'];
					$data['term_length_period'] = 1;
				}


			}else{
				$data['term_length'] = null;
				$data['term_length_period'] = 1;
			}

			$objectData['assigned_owner'] = $objectData['user_id']; // quick fix
			if(isset($objectData['assigned_owner']) && !empty($objectData['assigned_owner']))
			{
				if(isset($objectData['assigned_owner']) && !empty($objectData['assigned_owner']) && $objectData['assigned_owner'] == 'sales_team')
				{
					$personal_commercial = _POLICY_LINE_PERSONAL;
					if(isset($objectData['contact_business_id']) && !empty($objectData['contact_business_id']))
					{
						$personal_commercial = _POLICY_LINE_COMMERCIAL;
					}
						$sales_team_owner_id = CommonFunctions::getSalesUserIDToAssignDefault($login_agency_id,_AGENCY_TEAM_SALES,$personal_commercial);

					if(isset($sales_team_owner_id['user_id_to_return']) && !empty($sales_team_owner_id['user_id_to_return']))
					{

						$assign_sales_owner_id = $sales_team_owner_id['user_id_to_return'];
					}
					else
					{
						$round_robin_sales_arr = CommonFunctions::getSalesUserIDbyRoundRobinDefault($login_agency_id,$personal_commercial);

						if(isset($round_robin_sales_arr['user_id_to_return']) && !empty($round_robin_sales_arr['user_id_to_return'])){
							$sales_owner_id=$round_robin_sales_arr['user_id_to_return'];
							$assign_sales_owner_id = $sales_owner_id;
						}
					}

				}
				else if(isset($objectData['assigned_owner']) && !empty($objectData['assigned_owner']) && $objectData['assigned_owner'] != 'sales_team')
				{
					$assign_sales_owner_id = $objectData['assigned_owner'];
				}


				if(!empty($assign_sales_owner_id))
				{
					$data['user_id'] = $assign_sales_owner_id;
				}
				else
				{
					$data['user_id'] = $login_user_id;

				}
			}else{
				$data['user_id'] = $login_user_id;
			}
			if($objectData['primary_insured_id']['id'] != $objectData['contact_id'] && !empty($objectData['primary_insured_id']['id']))
			{
				$data['contact_id'] = $objectData['primary_insured_id']['id'];
				$checksecondaryContact = $contactAdditionalInsuredPolicyRelation->contactAdditionalToSwap($objectData['contact_id'], $objectData['primary_insured_id']['id'], $opportunity_id);
				if($checksecondaryContact)
				{
					$policyInsuredData = $policyInsuredLogs->newEntity();
					$insuredData['primary_insured_id'] = $objectData['contact_id'];
					$insuredData['agency_id'] = $login_agency_id;
					$insuredData['additional_insured_id'] = $objectData['primary_insured_id']['id'];
					$insuredData['contact_opportunity_id'] =  $opportunity_id;
					$policyInsuredData = $policyInsuredLogs->patchEntity($policyInsuredData, $insuredData);
					$policyInsuredData = $policyInsuredLogs->save($policyInsuredData);
				}
			}
			else if(isset($objectData['contact_id']) && !empty($objectData['contact_id']))
			{
			 	$data['contact_id'] = $objectData['contact_id'];
			}
			if(isset($objectData['contact_business_id']) && !empty($objectData['contact_business_id']))
			{
				$data['contact_business_id'] = $objectData['contact_business_id'];
			}
			$premium_amount="--";
			if(isset($objectData['premium_amount']) && !empty($objectData['premium_amount']) && $objectData['premium_amount'] != "--")
			{
				$premium_amount = $objectData['premium_amount'];
				$data['premium_amount'] = CommonFunctions::Replacetext($objectData['premium_amount']);
			}else{
				if(isset($objectData['premium_amount']) && $objectData['premium_amount'] == 0){
					$premium_amount = $objectData['premium_amount'];
					$data['premium_amount'] = CommonFunctions::Replacetext($objectData['premium_amount']);
				}else{
					$data['premium_amount'] =  null;
				}
			}

			// $commission_amount="--";
			// if(isset($objectData['commission_amount']) && !empty($objectData['commission_amount']) && $objectData['commission_amount'] != "--")
			// {
				// $commission_amount = $commission_amount['commission_amount'];
				//save agency and split commission
				if($login_role_type==_ID_AGENCY_USER && ($login_role_type_flag==_AGENCY_ROLE_PRODUCER || $login_role_type_flag==_AGENCY_ROLE_MARKETER || $login_role_type_flag==_AGENCY_ROLE_CSR))
				{
					$data['commission_split'] = $commission_amt;
				}
				else
				{
					$data['commission_amount'] = $commission_amt;
				}

			// } 

			$effective_date="--";
			if(isset($objectData['effective_date']) && !empty($objectData['effective_date']) && $objectData['effective_date'] != "--" && $objectData['effective_date'] != '1970-01-01')
			{
				$objectData['effective_date'] = date('Y-m-d',strtotime($objectData['effective_date']));
				$newExpDate = str_replace('-', '/', $objectData['effective_date']);
				$data['effective_date'] = date("Y-m-d",strtotime($newExpDate));
				if(isset($objectData['renewal_id']) && !empty($objectData['renewal_id']))
				{
					$data['renewal_date'] = date("Y-m-d",strtotime($newExpDate));
				}
			}else{
				$data['effective_date'] = NULL;
				if(isset($objectData['renewal_id']) && !empty($objectData['renewal_id']))
				{
					$data['renewal_date'] = NULL;
				}
			}

			$carrier_id="--";
			if(isset($objectData['carrier_id']) && !empty($objectData['carrier_id']) && $objectData['carrier_id'] != "--")
			{
				$carrier_id = $objectData['carrier_id'];
				$data['carrier_id'] = $objectData['carrier_id'];
			}else{
				$data['carrier_id'] = null;
			}

			$policy_number="--";
			if(isset($objectData['policy_number']) && !empty($objectData['policy_number']) && $objectData['policy_number'] != "--")
			{
				$policy_number = trim($objectData['policy_number']);
				$data['policy_number'] = trim($objectData['policy_number']);
			}else{
				$data['policy_number'] = null;
			}
			/**New Parameter add email name phone number*/

			if(isset($objectData['ud_name']) && !empty($objectData['ud_name']) && $objectData['ud_name'] != "--")
			{
				$data['ud_name'] = trim($objectData['ud_name']);
			}
			else
			{
				$data['ud_name'] = null;
			}

			if(isset($objectData['ud_phone_number']) && !empty($objectData['ud_phone_number']) && $objectData['ud_phone_number'] != "--")
			{
				$data['ud_phone_number'] = trim($objectData['ud_phone_number']);
			}
			else
			{
				$data['ud_phone_number'] = null;
			}

			if(isset($objectData['ud_email']) && !empty($objectData['ud_email']) && $objectData['ud_email'] != "--")
			{
				$data['ud_email'] = trim($objectData['ud_email']);
			}
			else
			{
				$data['ud_email'] = null;
			}

			$rewrite_status=0;
			if(isset($objectData['rewrite_status']))
			{
				$rewrite_status = $objectData['rewrite_status'];
				$data['rewrite_status'] = $objectData['rewrite_status'];
			}
			/* policy type edit starts*/
			$policy_name="--";
			if(isset($objectData['insurance_type_id']) && !empty($objectData['insurance_type_id']) && $objectData['insurance_type_id'] != "--")
			{
				$policy_name = $objectData['insurance_type_id'];
				$data['insurance_type_id'] = $policy_name;
			}else{
				$data['insurance_type_id'] = null;
			}

			$old_insurance_type_id = "--";
			if(isset($objectData['old_insurance_type_id']) && !empty($objectData['old_insurance_type_id']) && $objectData['old_insurance_type_id'] != "--")
			{
				$old_insurance_type_id = $objectData['old_insurance_type_id'];
				$data['old_insurance_type_id'] = $old_insurance_type_id;
			}

			if(isset($objectData['carrier_id'])&&!empty($objectData['carrier_id']))
			{

					$carrier_details = $carriers->getCarrierNameById($carrier_id,$login_agency_id);
					$carrier_name = $carrier_details['name'];
			}
			else
			{
					$carrier_name = '--';
			}


			$policy_status="0";
			if(isset($objectData['status']) && !empty($objectData['status']) && $objectData['status'] != "0")
			{
				$policy_status = $objectData['status'];
				if($policy_status == _ID_STATUS_ACTIVE)
				{
					$data['pipeline_stage'] = _PIPELINE_STAGE_WON;
					if(date('Y-m-d' ,strtotime($objectData['effective_date'])) <= date('Y-m-d')){
						$data['pending_sub_status']='';
						$data['active_sub_status']=_ID_ACTIVE_SUB_STATUS_ACTIVE;
					}elseif(date('Y-m-d' ,strtotime($objectData['effective_date'])) > date('Y-m-d'))
					{
						$data['pending_sub_status']='';
						$data['active_sub_status'] = _ID_ACTIVE_SUB_STATUS_FUTURE_EFFECTIVE;
					}
				}
				$data['status'] = $objectData['status'];
			}else{
				$data['status'] = _ID_STATUS_PENDING;
			}

			if(isset($objectData['pipeline_stage_id']) && !empty($objectData['pipeline_stage_id'])){
				$pipeline_stage_id = $objectData['pipeline_stage_id'];

				if($pipeline_stage_id ==_PIPELINE_STAGE_WON){
					$data['status'] = _ID_STATUS_ACTIVE;
					$data['won_date']=date('Y-m-d');
					$pipeline_stage = _PIPELINE_STAGE_WON;
					if(date('Y-m-d' ,strtotime($objectData['effective_date'])) <= date('Y-m-d')){
						$data['pending_sub_status'] = '';
						$data['active_sub_status'] = _ID_ACTIVE_SUB_STATUS_ACTIVE;
					}elseif(date('Y-m-d' ,strtotime($objectData['effective_date'])) > date('Y-m-d'))
					{
						$data['pending_sub_status'] = '';
						$data['active_sub_status'] = _ID_ACTIVE_SUB_STATUS_FUTURE_EFFECTIVE;
					}
				}
				else if($pipeline_stage_id ==_PIPELINE_STAGE_LOST){
					$data['lost_date']=date('Y-m-d');
					$data['status'] = _ID_STATUS_INACTIVE;
                	$data['pending_sub_status'] = _ID_PENDING_SUB_STATUS_LOST;
					$data['active_sub_status'] = NULL;
				}
				else if($pipeline_stage_id ==_PIPELINE_STAGE_QUOTE_SENT){
					$data['quote_sent_date'] = date('Y-m-d');
					$data['status'] = _ID_STATUS_PENDING;
					$data['pending_sub_status']=_ID_PENDING_SUB_STATUS_QUOTED;
				}else{
					$data['status'] = _ID_STATUS_PENDING;
					 if($pipeline_stage_id ==_PIPELINE_STAGE_NEW_LEAD || $pipeline_stage_id ==_PIPELINE_STAGE_WORKING || $pipeline_stage_id ==_PIPELINE_STAGE_APPOINTMENT_SCHEDULED){
						$data['pending_sub_status'] = _ID_PENDING_SUB_STATUS_NEW_LEAD;
					}elseif($pipeline_stage_id ==_PIPELINE_STAGE_QUOTE_READY){
						$data['status'] = _ID_STATUS_PENDING;
						$data['pending_sub_status'] = _ID_PENDING_SUB_STATUS_QUOTED;
					}
				}
				$data['pipeline_stage']= $pipeline_stage_id;
			}


			$hawksoft_renewal_status="0";
			if(isset($objectData['hawksoft_renewal_status']))
			{
				$hawksoft_renewal_status = $objectData['hawksoft_renewal_status'];
				$data['hawksoft_renewal_status']=$hawksoft_renewal_status;
			}

			if(isset($objectData['active_sub_status']) && !empty($objectData['active_sub_status']) && $objectData['active_sub_status'] != "0")
			{
				$data['active_sub_status'] = $objectData['active_sub_status'];
			}
			if(isset($objectData['inactive_sub_status']) && !empty($objectData['inactive_sub_status']) && $objectData['inactive_sub_status'] != "0")
			{
				$data['inactive_sub_status'] = $objectData['inactive_sub_status'];
			}
			if(isset($objectData['pending_sub_status']) && !empty($objectData['pending_sub_status']) && $objectData['pending_sub_status'] != "0")
			{
				$data['pending_sub_status'] = $objectData['pending_sub_status'];
			}
			if((isset($objectData['effective_date']) && !empty($objectData['effective_date']) || isset($objectData['term_length']) && !empty($objectData['term_length'])) && $data['status'] == _ID_STATUS_ACTIVE)
			{

				if(isset($objectData['effective_date']) && !empty($objectData['effective_date']) && $objectData['effective_date'] != '1970-01-01'){
					$effectiveDate = $objectData['effective_date'];
				}else{
					$effectiveDate = date('Y-m-d',strtotime(CommonFunctions::convertUtcToEmployeeTimeZone($usersTimezone,date("Y-m-d"))));
				}


				$statusTermLength = 0;

				if(strtolower($objectData['term_length']) == 'other'){
					if(isset($objectData['term_length_period']) && !empty($objectData['term_length_period']))
					{

						$statusTermLength =  $objectData['term_length_period']*12;
					}
					else{

						$statusTermLength =0;
					}
					if(isset($objectData['term_length_text']) && !empty($objectData['term_length_text']))
					{
						$statusTermLength =  $statusTermLength+$objectData['term_length_text'];
					}
					else{
						$statusTermLength =$statusTermLength;
					}


				}else{
					if(isset($objectData['term_length']) && !empty($objectData['term_length'])){
						$statusTermLength = $objectData['term_length'];
					}else{
						$statusTermLength = 0;
					}

				}

				$currentDate = date('Y-m-d',strtotime(CommonFunctions::convertUtcToEmployeeTimeZone($usersTimezone,date("Y-m-d"))));

				$effectiveDate = date('Y-m-d', strtotime("+".$statusTermLength." months", strtotime($effectiveDate)));

			   $todayActiveStatus = 0;
				if($currentDate > $effectiveDate){

					$data['status'] = _ID_STATUS_INACTIVE;
					$todayActiveStatus = _ID_STATUS_INACTIVE;

				}else{

					$data['status']=_ID_STATUS_ACTIVE;
					$todayActiveStatus = _ID_STATUS_ACTIVE;
				}

			}
			$owner_id = '';
			if(isset($objectData['user_id']) && !empty($objectData['user_id']))
			{
				$owner_id = $objectData['user_id'];
			}

			$data['mga_id'] = null;
			if(isset($objectData['mga_id']) && !empty($objectData['mga_id'])){
				$data['mga_id'] = $objectData['mga_id'];
			}
			$contactAdditionalInsuredPolicyRelation = TableRegistry::getTableLocator()->get('ContactAdditionalInsuredPolicyRelation');
			if(isset($opportunity_id) && !empty($opportunity_id)){
				$oppDetails = $contactOpportunities->get($opportunity_id);
				$oldOpportunityData = [];
				if(!empty($oppDetails->nowcert_policy_database_id))
				{
					$oldOpportunityData['oldPremium'] = $oppDetails->premium_amount;
					$oldOpportunityData['oldAgencyCommissionValue'] = $oppDetails->commission_amount;
				}
				$primary_contact_id = $oppDetails['contact_id'];
			}
			$findEmptyValue = true;
			if($objectData['primary_insured_id']['id'] != $objectData['contact_id'] && !empty($objectData['primary_insured_id']) && !empty($objectData['primary_insured_id']['id']))
			{

				$findEmptyValue = in_array((int)$objectData['contact_id'], $objectData['additional_insured_contact']);
				$checksecondaryContact = $contactAdditionalInsuredPolicyRelation->contactAdditionalToSwap($objectData['contact_id'], $objectData['primary_insured_id']['id'], $opportunity_id);
				if($checksecondaryContact)
				{
					$switchContact = $contactAdditionalInsuredPolicyRelation->updateAll(['contact_id' => $objectData['primary_insured_id']['id'], 'additional_insured_contact_id' => $objectData['contact_id']], ['id' => $checksecondaryContact['id'], 'previous_opportunity_id' => $opportunity_id]);
				}
			}
			if(isset($objectData['additional_insured_contact']) && !empty($objectData['additional_insured_contact']) && $objectData['additional_insured_contact'] != "--")
			{
				if($objectData['primary_insured_id']['id'] != $objectData['contact_id'] && !empty($objectData['primary_insured_id'] && !empty($objectData['primary_insured_id']['id'])))
				{
					$contactAdditionalInsuredPolicyRelation->updateAll(['contact_opportunity_id' => null], ['contact_id' => $objectData['primary_insured_id']['id'], 'contact_opportunity_id' => $opportunity_id]);
				}
				$result = $contactAdditionalInsuredPolicyRelation->updateAll(['contact_opportunity_id' => null], ['contact_id'=>$primary_contact_id,'contact_opportunity_id'=>$opportunity_id]);
				foreach ( $objectData['additional_insured_contact'] as $contact_insured_id) {
					$insured_contact_id = $contact_insured_id;
					if($findEmptyValue == false)
					{
						$contactAdditionalInsuredPolicyRelation->updateAll(['contact_opportunity_id' => null], ['additional_insured_contact_id' => (int)$objectData['contact_id'], 'previous_opportunity_id' => $opportunity_id]);
					}
					if($insured_contact_id != $primary_contact_id){
						if(!empty($insured_contact_id))
						{
							$checkOpportunity = $contactAdditionalInsuredPolicyRelation->getAdditionalContactsHavingNullOpp($primary_contact_id, $insured_contact_id, null);
							if(empty($checkOpportunity) && empty($objectData['primary_insured_id']) && empty($objectData['primary_insured_id']['id']))
							{
								$checkOpportunity = $contactAdditionalInsuredPolicyRelation->getAdditionalContactsHavingNullOpp($insured_contact_id, $primary_contact_id, null);
								if(!empty($checkOpportunity))
								{
									$switchContact = $contactAdditionalInsuredPolicyRelation->updateAll(['contact_id' => $primary_contact_id, 'additional_insured_contact_id' => $insured_contact_id], ['id' => $checkOpportunity->id]);
								}
							}
							if(!empty($checkOpportunity) && empty($checkOpportunity->contact_opportunity_id) && empty($checkOpportunity->previous_opportunity_id))
							{
								$result = $contactAdditionalInsuredPolicyRelation->updateAll(['contact_opportunity_id' => $opportunity_id, 'previous_opportunity_id' => $opportunity_id], ['id' => $checkOpportunity->id]);
							}
							else
							{
								$checkOpportunity = $contactAdditionalInsuredPolicyRelation->getAdditionalContactsHavingNullOpp($primary_contact_id, $insured_contact_id, $opportunity_id);
								if(!empty($checkOpportunity) && empty($checkOpportunity->contact_opportunity_id) && !empty('previous_opportunity_id'))
								{
									if($objectData['primary_insured_id']['id'] != $objectData['contact_id'] && !empty($objectData['primary_insured_id'] && !empty($objectData['primary_insured_id']['id'])))
									{
										$result = $contactAdditionalInsuredPolicyRelation->updateAll(['contact_opportunity_id' => null], ['id' => $checkOpportunity->id, 'previous_opportunity_id' => $opportunity_id]);
									}else
									{
										$result = $contactAdditionalInsuredPolicyRelation->updateAll(['contact_opportunity_id' => $opportunity_id], ['id' => $checkOpportunity->id, 'previous_opportunity_id' => $opportunity_id]);
									}
								}
								else
								{
									$additional_insured_data = $contactAdditionalInsuredPolicyRelation->newEntity();
									$additional_data['additional_insured_contact_id'] = $insured_contact_id;
									$additional_data['contact_id'] = $primary_contact_id;
									$additional_data['contact_opportunity_id'] = $opportunity_id;
									$additional_data['previous_opportunity_id'] = $opportunity_id;
									$additional_insured_data = $contactAdditionalInsuredPolicyRelation->patchEntity($additional_insured_data, $additional_data);
									$contactAdditionalInsuredPolicyRelation->save($additional_insured_data);
								}
							}
						}
					} 
					else
					{  
						$checkEmptyOpportunity = $contactAdditionalInsuredPolicyRelation->getAdditionalContactsHavingNullOpp($objectData['primary_insured_id']['id'], $insured_contact_id, $opportunity_id);
							if(empty($checkEmptyOpportunity->contact_opportunity_id))
							{
								$checkOpportunity = $contactAdditionalInsuredPolicyRelation->getContactsPreviesOppoId($primary_contact_id, $insured_contact_id, $opportunity_id);
								if(!empty($checkOpportunity))
								{
									$contactAdditionalInsuredPolicyRelation->updateAll(['contact_opportunity_id' => null], ['id' => $checkOpportunity->id]);
								}
								else
								{
									$contactAdditionalInsuredPolicyRelation = $contactAdditionalInsuredPolicyRelation->updateAll(['contact_opportunity_id' => $opportunity_id], ['id' => $checkEmptyOpportunity['id'], 'contact_id' => $checkEmptyOpportunity['contact_id'], 'previous_opportunity_id', $opportunity_id]);
								}
							}
							else
							{
								if($findEmptyValue == false)
								{
									$contactAdditionalInsuredPolicyRelation = $contactAdditionalInsuredPolicyRelation->updateAll(['contact_opportunity_id' => null], ['additional_insured_contact_id' => $insured_contact_id, 'previous_opportunity_id' => $opportunity_id]);
								}
								else if($findEmptyValue == true && $insured_contact_id ==  $primary_contact_id)
								{
									$contactAdditionalInsuredPolicyRelation = $contactAdditionalInsuredPolicyRelation->updateAll(['contact_opportunity_id' => $opportunity_id], ['additional_insured_contact_id' => $insured_contact_id, 'previous_opportunity_id' => $opportunity_id]);
								}
							}
					}

				}
//				$insured_contact_id = $objectData['additional_insured_contact'];
//
//				$checkEmptyOpportunity = $contactAdditionalInsuredPolicyRelation->getSecondryContactsHavingNullOpp($primary_contact_id,$insured_contact_id);
//				if(!empty($checkEmptyOpportunity)){
//					$DeleteEmptyEntry = $contactAdditionalInsuredPolicyRelation->delete($checkEmptyOpportunity);
//				}
//
//				$check_additional_insured_data = $contactAdditionalInsuredPolicyRelation->getContactAdditionalInsuredByOppId($primary_contact_id,$opportunity_id);
//				$previousInsuredContactId = $check_additional_insured_data['additional_insured_contact_id'];
//				if(!empty($check_additional_insured_data)){
//					$additional_data['additional_insured_contact_id'] = $insured_contact_id;
//					$additional_insured_data = $contactAdditionalInsuredPolicyRelation->patchEntity($check_additional_insured_data,$additional_data);
//					if($contactAdditionalInsuredPolicyRelation->save($additional_insured_data)){
//						$countTotalRecord = $contactAdditionalInsuredPolicyRelation->getsecondaryContactCount($primary_contact_id,$previousInsuredContactId);
//						if($countTotalRecord == 0){
//							$additional_insured_data = $contactAdditionalInsuredPolicyRelation->newEntity();
//							$additional_data['additional_insured_contact_id'] = $previousInsuredContactId;
//							$additional_data['contact_id'] = $primary_contact_id;
//							$additional_insured_data = $contactAdditionalInsuredPolicyRelation->patchEntity($additional_insured_data,$additional_data);
//							$contactAdditionalInsuredPolicyRelation->save($additional_insured_data);
//						}
//					}
//
//				} else{

//					$additional_insured_data = $contactAdditionalInsuredPolicyRelation->newEntity();
//					$additional_data['additional_insured_contact_id'] = $insured_contact_id;
//					$additional_data['contact_id'] = $primary_contact_id;
//					$additional_data['contact_opportunity_id'] = $opportunity_id;
//					$additional_insured_data = $contactAdditionalInsuredPolicyRelation->patchEntity($additional_insured_data,$additional_data);
//					$contactAdditionalInsuredPolicyRelation->save($additional_insured_data);
//				}
			}else{
				$check_additional_insured_data = $contactAdditionalInsuredPolicyRelation->getAllContactAdditionalInsuredByOppId($primary_contact_id, $opportunity_id);
				if($check_additional_insured_data){
					foreach($check_additional_insured_data as $additionalInsuredData)
					{
						if(!empty($additionalInsuredData->contact_opportunity_id) && $additionalInsuredData->previous_opportunity_id == $opportunity_id)
						$result = $contactAdditionalInsuredPolicyRelation->updateAll(['contact_opportunity_id' => null], ['id' => $additionalInsuredData['id'], 'contact_opportunity_id' => $opportunity_id]);
					}
					// $contactAdditionalInsuredPolicyRelation->updateAll(['contact_opportunity_id' => null], ['contact_id'=>$primary_contact_id,'contact_opportunity_id'=>$opportunity_id]);
//					$additional_data['contact_opportunity_id'] = null;
//					$additional_insured_data = $contactAdditionalInsuredPolicyRelation->patchEntity($check_additional_insured_data,$additional_data);
//					$contactAdditionalInsuredPolicyRelation->save($additional_insured_data);
				}
			}

			$oldOpportunityDetail ='';
			$previousValues = '';
			//old opp details
			if($opportunity_id != null)
			{
				$oldOpportunityDetail = $contactOpportunities->get($opportunity_id);
				$previousValues = $oldOpportunityDetail->toArray();
			}

				//assign owner id
				if($oldOpportunityDetail != '')
				{
					if(isset($owner_id) && !empty($owner_id) && $owner_id != $oldOpportunityDetail->user_id)
					{

						$contactOpportunities->updateAll(['user_id'=>$owner_id],['id'=>$oldOpportunityDetail->id]);
						$note_policy_change_owner_arr = array();
						$note_policy_change_owner_arr['login_agency_id'] = $login_agency_id;
						$note_policy_change_owner_arr['login_user_id'] = $login_user_id;
						$note_policy_change_owner_arr['contact_id'] = $oldOpportunityDetail->contact_id;
						$note_policy_change_owner_arr['opportunity_id'] = $opportunity_id;
						$note_policy_change_owner_arr['old_user_id'] = $oldOpportunityDetail->user_id;
						$note_policy_change_owner_arr['new_user_id'] = $owner_id;
						$note_policy_change_owner_arr['insurance_type_id'] = $oldOpportunityDetail->insurance_type_id;
						CommonFunctions::saveNotePolicyOwnerChange($note_policy_change_owner_arr);
					}
					//policy type change
					if(isset($policy_name) && $policy_name!= '--' && $policy_name!=$oldOpportunityDetail->insurance_type_id)
					{

						$note_policy_type_change_arr = array();
						$note_policy_type_change_arr['login_agency_id'] = $login_agency_id;
						$note_policy_type_change_arr['login_user_id'] = $login_user_id;
						$note_policy_type_change_arr['contact_id'] = $oldOpportunityDetail->contact_id;
						$note_policy_type_change_arr['opportunity_id'] = $opportunity_id;
						$note_policy_type_change_arr['old_insurance_type_id'] = $oldOpportunityDetail->insurance_type_id;
						$note_policy_type_change_arr['new_insurance_type_id'] = $policy_name;
						CommonFunctions::saveNotePolicyTypeChange($note_policy_type_change_arr);

						$updateInsuranceType = $contactOpportunities->updateAll(['insurance_type_id'=>$policy_name],['id'=>$oldOpportunityDetail->id]);
						if($updateInsuranceType)
						{
							$renewalPipeline = TableRegistry::getTableLocator()->get('RenewalPipeline');
							$checkOppoToRenewal = $renewalPipeline->checkAlreadyExistRenewalIDNotDeleted($oldOpportunityDetail->contact_id, $oldOpportunityDetail->id, null);
							if(!empty($checkOppoToRenewal))
							{
								$renewalPipeline->updateAll(['insurance_type_id' => $policy_name], ['id' => $checkOppoToRenewal['id'], 'contact_id' => $oldOpportunityDetail->contact_id, 'opportunity_id' => $oldOpportunityDetail->id]);
							}
						}

					}
				}
				if(!empty($objectData['policy_title']) && $objectData['policy_title'] != "--")
				{
					$data['sales_title'] = $objectData['policy_title'];
				}
				//
				$contactPolicyRenewal = TableRegistry::getTableLocator()->get('ContactPolicyRenewal');
				//renewal code start
				$opportunityDetail = '';
				if(isset($objectData['renewal_id']) && !empty($objectData['renewal_id']))
				{

					$opportunityDetail = $contactPolicyRenewal->get($objectData['renewal_id']);
					$opportunityDetail = $contactPolicyRenewal->patchEntity($opportunityDetail,$data);
					$opportunityDetail = $contactPolicyRenewal->save($opportunityDetail);
					//this is to update policy status, if renewed policy status is set to active.
					if(isset($todayActiveStatus) && isset($policy_status) && $todayActiveStatus == _ID_STATUS_ACTIVE && $policy_status == _ID_STATUS_ACTIVE){

						$contactOpportunities->updateAll(['status'=>_ID_STATUS_ACTIVE,'platform_update'=>_PLATFORM_TYPE_SYSTEM],['id'=>$opportunity_id,'agency_id'=>$login_agency_id]);
					}
				}
				else
				{   //regular opp add/update code start

					if($opportunity_id != null)
					{
						$opportunityDetail = $contactOpportunities->get($opportunity_id);
					}else{
						$opportunityDetail = $contactOpportunities->newEntity();
					}
					$opportunityDetail = $contactOpportunities->patchEntity($opportunityDetail,$data);
					$opportunityDetail = $contactOpportunities->save($opportunityDetail);
					if(isset($previousValues) && !empty($previousValues))
					{
						$newValues = $opportunityDetail->toArray();
						$changesDetailsArr = CommonFunctions::createContactOpportunitiesLogsArr($previousValues,$newValues);
						$policyArr = [];
						$policyArr['agency_id'] = $login_agency_id;
						$policyArr['user_id'] = $login_user_id;
						$policyArr['contact_opportunity_id'] = $opportunityDetail->id;
						$policyArr['type'] = _POLICY_LOG_TYPE_CHANGE;
						$policyArr['change_details'] = $changesDetailsArr;
						$policyArr['triggered'] = 'User edited policy manually';
						$policyArr['platform'] = _PLATFORM_TYPE_SYSTEM;
						$policyArr['event_details'] = "updatePolicyDetails";
						ContactOpportunityChanger::applyPolicyChanges($opportunityDetail->id,$policyArr);
					}

					if(isset($opportunityDetail->contact_id) && !empty($opportunityDetail->contact_id))
					{
					  $contactOpportunities->updateAll(['primary_flag_all_stages'=>_ID_STATUS_PENDING],['contact_id' => $opportunityDetail->contact_id,'pipeline_stage'=>$opportunityDetail->pipeline_stage,'agency_id'=>$login_agency_id,'contact_business_id IS NULL']);
					  $contactOpportunities->updateAll(['primary_flag_all_stages'=>_ID_STATUS_ACTIVE],['id' => $opportunityDetail->id]);
					}
					//end

					// set client since 
					
				}
			$contacts = TableRegistry::getTableLocator()->get('Contacts');
			$contactPolicyLostReasons = TableRegistry::getTableLocator()->get('ContactPolicyLostReasons');
			$contactPolicyLostCarriers = TableRegistry::getTableLocator()->get('ContactPolicyLostCarriers');
			$contactPolicyLostDetails = TableRegistry::getTableLocator()->get('ContactPolicyLostDetails');
			// change policy type ends
			if($policy_status == _ID_STATUS_ACTIVE)
			{
				//code to make the first opportunity primary start here
				if(isset($opportunityDetail) && !empty($opportunityDetail))
				{
					
					$contacts->updateAll(['lead_type' => _CONTACT_TYPE_CLIENT],['id' => $opportunityDetail['contact_id'],'lead_type' => _CONTACT_TYPE_LEAD,'agency_id' => $login_agency_id]);
					// set client since
					if($opportunityDetail['effective_date'] != null ){
						$contacts->updateAll(['client_since' => date('Y-m-d', strtotime($opportunityDetail['effective_date']))],['id' => $opportunityDetail['contact_id'], 'lead_type' => _CONTACT_TYPE_CLIENT, 'agency_id' => $login_agency_id, 'client_since is null']);
					}
					
			}
					//update won date if emty and opp is active
				if(empty($opportunityDetail['won_date']))
				{
					$contactOpportunities->updateAll(['won_date'=>date('Y-m-d')],['id'=>$opportunityDetail['id']]);

				}
					//set primary flag
				if($opportunityDetail['is_imported']==_ID_FAILED){
					$contactOpportunities->updateAll(['primary_flag'=>_ID_FAILED],['contact_id'=>$opportunityDetail['contact_id']]);
					$contactOpportunities->updateAll(['primary_flag'=>_ID_SUCCESS],['id'=>$opportunityDetail['id']]);
				}

			}

			if($opportunityDetail){

				if(isset($opportunityDetail->pipeline_stage) && !empty($opportunityDetail->pipeline_stage) && $opportunityDetail->pipeline_stage == _PIPELINE_STAGE_WON && empty($objectData['renewal_id']))
				{
					//check for entry in contact policy renewal table
					$contactPolicyRenewalDetail = $contactPolicyRenewal->getContactPolicyRenewalByContactAndOpportunitySuccess($opportunityDetail->contact_id,$opportunityDetail->id);
					//make first entry insert/update in contact policy renewal
					  $data = [];
					  $data['renewal_date']=$opportunityDetail->effective_date;
					  $data['renewal_amount']=$opportunityDetail->premium_amount;
					  $data['term_length']=$opportunityDetail->term_length;
					  $data['term_length_period']=$opportunityDetail->term_length_period;
					  $data['carrier_id']=$opportunityDetail->carrier_id;
					  $data['premium_amount']=$opportunityDetail->premium_amount;
					  $data['commission_amount']=$opportunityDetail->commission_amount;
					  $data['policy_number']=$opportunityDetail->policy_number;
					  $data['commission_split']=$opportunityDetail->commission_split;
					  $data['commission_split_percentage']=$opportunityDetail->commission_split_percentage;
					  if(empty($contactPolicyRenewalDetail))
					  { $data['agency_id']=$opportunityDetail->agency_id;
						$data['contact_id']=$opportunityDetail->contact_id;
						$data['contact_opportunities_id']=$opportunityDetail->id;
						$data['amount_received_date']=date('Y-m-d');
						$data['stage']=_RENEWAL_STAGE_SUCCESS;
						$contactPolicyRenewalDetail = $contactPolicyRenewal->newEntity();
					  }
					  else
					  {
						$contactPolicyRenewalDetail = $contactPolicyRenewal->get($contactPolicyRenewalDetail['id']);
					  }
					  $contactPolicyRenewalDetail = $contactPolicyRenewal->patchEntity($contactPolicyRenewalDetail, $data);
					  $contactPolicyRenewal->save($contactPolicyRenewalDetail);

				  }
				  //
				//lost policy reasons details
				//check for entry in lost policy details table
				//ContactPolicyLostDetails data insert/update only when opp status is cancel or future cancel
				$lostPolicyDetail  = '';
				if($opportunity_id !== null){
					$lostPolicyDetail = $contactPolicyLostDetails->find('all')->where(['ContactPolicyLostDetails.agency_id'=>$login_agency_id,'ContactPolicyLostDetails.contact_opportunities_id'=>$opportunity_id,'ContactPolicyLostDetails.lost_type'=>CONTACT_LOST_TYPE_POLICY,'ContactPolicyLostDetails.ivans_renewed_status'=>_ID_FAILED])->first();
				}


				//future cancellation
				$future_cancellation_flag=_ID_STATUS_PENDING;
				$cancellation_date_cc="";
				if(isset($lostPolicyDetail['cancellation_date']) && !empty($lostPolicyDetail['cancellation_date'])){
					$cancellation_date_cc=$lostPolicyDetail['cancellation_date'];
				}
				if(empty($objectData['multi_policy_flag']) && $opportunityDetail->status != _ID_STATUS_INACTIVE && strtotime($cancellation_date_cc) >= strtotime($currentDate))
				{
					$future_cancellation_flag=_ID_STATUS_ACTIVE;
				}


				$lost_policy_reason_id = 0;
				$lost_policy_carrier_id = 0;
				$lost_premium_amount = 0;
				$lost_term_length = '';
				$lost_cancellation_date = '';

				if($opportunityDetail->status == _ID_STATUS_INACTIVE || $future_cancellation_flag==_ID_STATUS_ACTIVE)
				{
					if(isset($objectData['lost_policy_reason_text']) && !empty($objectData['lost_policy_reason_text']))
					{
						$data = [];
						$lost_policy_reason_id = $objectData['lost_policy_reason_text'];
						if($lost_policy_reason_id == _LOST_POLICY_REASONS_OTHER && isset($objectData['lost_policy_reason_other_text']) && !empty($objectData['lost_policy_reason_other_text']))
						{
							// if type = other insert reason
							$checkReason = $contactPolicyLostReasons->checkReason($objectData['lost_policy_reason_other_text'],$login_agency_id);
							if(isset($checkReason) && !empty($checkReason)){
								$lost_policy_reason_id = $checkReason['id'];
							}else{
							$data['name'] = $objectData['lost_policy_reason_other_text'];
							$data['agency_id'] = $login_agency_id;
							$data['user_id'] = $login_user_id;
							$lostPolicyReasonDetail = $contactPolicyLostReasons->newEntity();
							$lostPolicyReasonDetail = $contactPolicyLostReasons->patchEntity($lostPolicyReasonDetail,$data);
							$lostPolicyReasonDetail = $contactPolicyLostReasons->save($lostPolicyReasonDetail);
							$lost_policy_reason_id = $lostPolicyReasonDetail->id;
							}
						}elseif($lost_policy_reason_id == _LOST_POLICY_REASONS_UNDERWRITING ||$lost_policy_reason_id == _LOST_POLICY_REASONS_INSURED ||$lost_policy_reason_id == _LOST_POLICY_REASONS_NON_RENEWAL && isset($objectData['lost_policy_detail_sub_reasones']) && !empty($objectData['lost_policy_detail_sub_reasones'])){
							$lost_policy_reason_id = $objectData['lost_policy_detail_sub_reasones'];
						}
					}
					if(isset($objectData['lost_policy_new_carrier_text']) && !empty($objectData['lost_policy_new_carrier_text'])){

						$data = [];
						$lost_policy_carrier_id= $objectData['lost_policy_new_carrier_text'];

						if($lost_policy_carrier_id == _LOST_POLICY_TO_INDEPENDENT && isset($objectData['lost_policy_new_carrier_other_text']) && !empty($objectData['lost_policy_new_carrier_other_text']))
						{
							// if type = other insert carrier
							$checkCarrier = $contactPolicyLostCarriers->checkCarrier($objectData['lost_policy_new_carrier_other_text'],$login_agency_id);

							if(isset($checkCarrier) && !empty($checkCarrier)){
								$lost_policy_carrier_id = $checkCarrier['id'];
							}else{
							$data['name'] = $objectData['lost_policy_new_carrier_other_text'];
							$data['agency_id']=$login_agency_id;
							$data['user_id']=$login_user_id;
							$lostPolicyCarrierDetail = $contactPolicyLostCarriers->newEntity();
							$lostPolicyCarrierDetail = $contactPolicyLostCarriers->patchEntity($lostPolicyCarrierDetail,$data);
							$lostPolicyCarrierDetail = $contactPolicyLostCarriers->save($lostPolicyCarrierDetail);
							$lost_policy_carrier_id = $lostPolicyCarrierDetail->id;
							}

						}

					}
					else if(isset($objectData['lost_policy_new_carrier_direct_text']) && !empty($objectData['lost_policy_new_carrier_direct_text']))
					{
						$data = [];
						$lost_policy_carrier_id = $objectData['lost_policy_new_carrier_direct_text'];
						if($lost_policy_carrier_id == _LOST_POLICY_TO_DIRECT_OTHER && isset($objectData['lost_policy_new_carrier_direct_other_text']) && !empty($objectData['lost_policy_new_carrier_direct_other_text']))
						{
							// if type = other insert carrier
							$checkCarrier = $contactPolicyLostCarriers->checkCarrierByMasterId($objectData['lost_policy_new_carrier_direct_other_text'],$login_agency_id,_LOST_POLICY_TO_DIRECT);
							if(isset($checkCarrier) && !empty($checkCarrier))
							{
								$lost_policy_carrier_id = $checkCarrier['id'];
							}
							else
							{
								$data['name'] = $objectData['lost_policy_new_carrier_direct_other_text'];
								$data['agency_id']=$login_agency_id;
								$data['user_id']=$login_user_id;
								$data['master_carrier_id'] = _LOST_POLICY_TO_DIRECT;
								$lostPolicyCarrierDirectDetail = $contactPolicyLostCarriers->newEntity();
								$lostPolicyCarrierDirectDetail = $contactPolicyLostCarriers->patchEntity($lostPolicyCarrierDirectDetail,$data);
								$lostPolicyCarrierDirectDetail = $contactPolicyLostCarriers->save($lostPolicyCarrierDirectDetail);
								$lost_policy_carrier_id = $lostPolicyCarrierDirectDetail->id;
							}
						}
					}
					else if(isset($objectData['lost_policy_new_carrier_captive_text']) && !empty($objectData['lost_policy_new_carrier_captive_text']))
					{
						$data = [];
						$lost_policy_carrier_id = $objectData['lost_policy_new_carrier_captive_text'];
						if($lost_policy_carrier_id == _LOST_POLICY_TO_CAPTIVE_OTHER && isset($objectData['lost_policy_new_carrier_captive_other_text']) && !empty($objectData['lost_policy_new_carrier_captive_other_text']))
						{
							// if type = other insert carrier
							$checkCarrier = $contactPolicyLostCarriers->checkCarrierByMasterId($objectData['lost_policy_new_carrier_captive_other_text'],$login_agency_id,_LOST_POLICY_TO_CAPTIVE);
							if(isset($checkCarrier) && !empty($checkCarrier)){
								$lost_policy_carrier_id = $checkCarrier['id'];
							}else{
							$data['name'] = $objectData['lost_policy_new_carrier_captive_other_text'];
							$data['agency_id']=$login_agency_id;
							$data['user_id'] = $login_user_id;
							$data['master_carrier_id'] = _LOST_POLICY_TO_CAPTIVE;
							$lostPolicyCarrierCaptiveDetail = $contactPolicyLostCarriers->newEntity();
							$lostPolicyCarrierCaptiveDetail = $contactPolicyLostCarriers->patchEntity($lostPolicyCarrierCaptiveDetail,$data);
							$lostPolicyCarrierCaptiveDetail = $contactPolicyLostCarriers->save($lostPolicyCarrierCaptiveDetail);
							$lost_policy_carrier_id = $lostPolicyCarrierCaptiveDetail->id;
							}
						}
					}
					if(isset($objectData['lost_policy_premium_text']) && !empty($objectData['lost_policy_premium_text']))
					{
						$lost_premium_amount = $objectData['lost_policy_premium_text'];
					}else{

						if(isset($objectData['lost_policy_premium_text']) && $objectData['lost_policy_premium_text']==0)
						{

							$lost_premium_amount = $objectData['lost_policy_premium_text'];
						}
					}
					if(isset($objectData['lost_policy_cancellation_date_text']) && !empty($objectData['lost_policy_cancellation_date_text'])){
						$lost_cancellation_date=date("Y-m-d",strtotime($objectData['lost_policy_cancellation_date_text']));
					}
					if(isset($objectData['lost_policy_termlength_text']) && !empty($objectData['lost_policy_termlength_text'])){
						$lost_term_length = 0;
						if($objectData['lost_policy_termlength_text'] == 'other'){
							if(isset($objectData['lost_policy_term_length_period']) && !empty($objectData['lost_policy_term_length_period']))
							{
								$lost_term_length =  $objectData['lost_policy_term_length_period']*12;
							}
							else
							{
								$lost_term_length =0;
							}
							if(isset($objectData['lost_policy_term_length_text']) && !empty($objectData['lost_policy_term_length_text']))
							{
								$lost_term_length =  $lost_term_length+$objectData['lost_policy_term_length_text'];
							}
							else
							{
								$lost_term_length = $lost_term_length;
							}

						}
						else
						{
							$lost_term_length = $objectData['lost_policy_termlength_text'];
						}
					}
					$term_length = 12;
					if(isset($lost_term_length) && !empty($lost_term_length))
					{
						$term_length = $lost_term_length;
					}
					$policy_x_date = '';
					$policy_cancellation_date = date('Y-m-d');
					if(isset($lost_cancellation_date) && !empty($lost_cancellation_date))
					{
						$policy_cancellation_date = $lost_cancellation_date;
						$policy_x_date = date('Y-m-d',strtotime("+ ".$term_length." months",strtotime($policy_cancellation_date)));
					}
					if(isset($objectData['lost_policy_x_date_text']) && !empty($objectData['lost_policy_x_date_text']))
					{
						$policy_x_date = date("Y-m-d",strtotime($objectData['lost_policy_x_date_text']));
					}
					$data = [];
					$data['agency_id'] = $login_agency_id;
					$data['user_id'] = $login_user_id;
					$data['contact_opportunities_id'] = $opportunity_id;
					$data['lost_policy_reason_id'] = $lost_policy_reason_id;
					$data['lost_policy_carrier_id'] = $lost_policy_carrier_id;
					$data['premium_amount'] = $lost_premium_amount;
					$data['term_length'] = $lost_term_length;
					$data['cancellation_date'] = $lost_cancellation_date;
					$data['policy_x_date'] = $policy_x_date;
					if(empty($lostPolicyDetail))
					{
						$lostPolicyDetail = $contactPolicyLostDetails->newEntity();
						$lostPolicyDetail = $contactPolicyLostDetails->patchEntity($lostPolicyDetail,$data);
						$lostPolicyDetail = $contactPolicyLostDetails->save($lostPolicyDetail);
					}
					else
					{
						$lostPolicyDetail = $contactPolicyLostDetails->get($lostPolicyDetail['id']);
						$lostPolicyDetail = $contactPolicyLostDetails->patchEntity($lostPolicyDetail,$data);
						$lostPolicyDetail = $contactPolicyLostDetails->save($lostPolicyDetail);
					}

				}
			}
			$contactOpportunitiesdata = $contactOpportunities->getPolicyDetailsByOppid($opportunityDetail['id']);
			if(!empty($contactOpportunitiesdata['effective_date']) && $contactOpportunitiesdata['pipeline_stage'] == _PIPELINE_STAGE_WON && $contactOpportunitiesdata['status'] == _ID_STATUS_PENDING)
			{
				$result = $contactOpportunities->updateAll(['status' => _ID_STATUS_ACTIVE], ['id' => $contactOpportunitiesdata['id']]);
			}else if(empty($contactOpportunitiesdata['effective_date']) && $contactOpportunitiesdata['pipeline_stage'] == _PIPELINE_STAGE_WON && $contactOpportunitiesdata['status'] == _ID_STATUS_ACTIVE)
			{
				$result = $contactOpportunities->updateAll(['status' => _ID_STATUS_PENDING], ['id' => $contactOpportunitiesdata['id']]);
			}
			NowCertsApi::updateIntoNowcerts($contactOpportunitiesdata['contact_id'], null, $opportunityDetail['id'], $oldOpportunityData);
			$message = 'Edits saved successfully!';
			if($opportunity_id == null)
			{
				$message =  'Policy added successfully!';
			}

			$response = json_encode(array('message_status' => _ID_SUCCESS,'message' => $message,'saved_opp_id'=> $opportunityDetail['id'], 'policyCloseModal' => $findEmptyValue, 'opportunityDetail'=> $contactOpportunitiesdata));
		}catch (\Exception $e) {
			$message = 'Edits not successfully saved!';
			if($opportunity_id == null)
			{
				$message =  'Policy not added successfully!';
			}

            $txt=date('Y-m-d H:i:s').' :: update Policy Lising Error- '.$e->getMessage();
			$response = json_encode(array('message_status' => _ID_FAILED,'message' => 'Policy edits not successfully saved!','saved_opp_id'=> $opportunityDetail['id'],'error'=>$txt));
        }


        return $response;

	}

	/**
     * contact Policies to link with attachment
     *
     */
    public function getPoliciesToLinkAttachment($contact_id)
    {
		try
		{
			$myfile = fopen(ROOT."/logs/vueAttachment.log", "a") or die("Unable to open file!");
			$session = Router::getRequest()->getSession();
			$login_agency_id= $session->read("Auth.User.agency_id");
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
					$policy_number_note_list = !empty($value['policy_number']) ? " - " . $value['policy_number'] : '';
					$opportunity_id_note_list = !empty($value['id']) ? "-" . $value['id'] : '';

					$insurance_type_id_note_list = isset($value['insurance_type']['id']) ? $value['insurance_type']['id'] : '';
					$policy_opprtunityId_note_list = ($insurance_type_name . $policy_number_note_list);
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
			//echo "<pre>";print_r($get_policy_types_for_notes_listing);die("Sdfsd");
			$return['ContactOpportunities.getPoliciesToLinkAttachment'] = [
				$contact_id => $get_policy_types_for_notes_listing
			];
			return $return;
		}catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: Policies Lising Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
	}


	public function savePolicyAttachment($objectData)
    {

		$attachment = $info = [];
		$add=0;
		$display_name = $message ='';
		$ContactAttachments = TableRegistry::getTableLocator()->get('ContactAttachments');
		$ContactPolicyAttachments = TableRegistry::getTableLocator()->get('ContactPolicyAttachments');
		if (!empty($objectData))
        {
				$explode_policy_type_id = (isset($objectData['policy_type_id'])&& (!empty($objectData['policy_type_id']))) ? explode("-",$objectData['policy_type_id']) : '';

				$contact_opprtunity_id = $explode_policy_type_id!= '' ? $explode_policy_type_id[0] : '';
				$insurance_type_id =  $explode_policy_type_id!= '' ? $explode_policy_type_id[1] : '';

				if(!empty($objectData['attachment_id'])){

					$attachment = $ContactAttachments->get($objectData['attachment_id']);
				}

				if(isset($attachment) && !empty($attachment)){

					$display_name = (isset($attachment->display_name) && (!empty($attachment->display_name))) ? $attachment->display_name : $attachment->name;

					$info['attachment_id'] = $attachment->id ;
					$info['contact_id'] =$attachment->contact_id  ;
					$info['name'] = $attachment->name ;
					$info['display_name'] = $display_name ;
					$info['file_size'] =  $attachment->file_size  ;
					$info['user_id'] = $attachment->user_id  ;
					$info['policy_id'] = $insurance_type_id ;
					$info['status'] = $attachment->status ;
				}
				if(isset($info) && !empty($info)){

					$attachment = $ContactPolicyAttachments->newEntity();
					$policyAttachment = $ContactPolicyAttachments->patchEntity($attachment, $info);
					$saveAttachment  = $ContactPolicyAttachments->save($policyAttachment);
				    $add = !empty($saveAttachment->id) ? 1 :0 ;
				}

		}
		if($add==1){

			$response = json_encode(array('status' => _ID_SUCCESS));
		}else{

			$response = json_encode(array('status' => _ID_FAILED));
		}
		return $response;

	}


	public function getPoliciesToAssociateWithTask($contact_id)
	{
		try
		{
			$myfile = fopen(ROOT."/logs/vueAttachment.log", "a") or die("Unable to open file!");
			$session = Router::getRequest()->getSession();
			$login_agency_id= $session->read("Auth.User.agency_id");
			$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
			$ContactPolicyRenewal = TableRegistry::getTableLocator()->get('ContactPolicyRenewal');
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
					$allOpportunities = $ContactOpportunities->getAllOpportunities($primaryId,$login_agency_id,$contatOpportunityIds);
				}
			}
			else
			{
				//all opportunities get
				$allOpportunities = $ContactOpportunities->getAllOpportunities($contact_id,$login_agency_id);
			}
			if (isset($allOpportunities) && !empty($allOpportunities)) {
				$insurance_type_name ='';
				foreach ($allOpportunities as $key => $value) {
					if (!empty($value['insurance_type']['type'])) {
						$insurance_type_name = $value['insurance_type']['type'];
					}
					$policy_number = !empty($value['policy_number']) ? " - " . $value['policy_number'] : '';
					$opportunity_id = !empty($value['id']) ? "-" . $value['id'] : '';

					$insurance_type_id = isset($value['insurance_type']['id']) ? $value['insurance_type']['id'] : '';
					$policy_opprtunityId = ($insurance_type_name . $policy_number);
					if(!empty($insurance_type_id))
					{
						if($value['status'] == 1)
						{
							$policies[] = [
								"id" => $insurance_type_id . $opportunity_id,
								"name" => $policy_opprtunityId,
								"policy_number" =>  $policy_number,
								"policy_type" => $insurance_type_name
							];
						}
						if($value['status'] == 2)
						{
							$policies[] = [
								"id" => $insurance_type_id . $opportunity_id,
								"name" => $policy_opprtunityId,
								"policy_number" =>  $policy_number,
								"policy_type" => $insurance_type_name
							];
						}

						if($value['status'] == 0)
						{
							$policies[] = [
								"id" => $insurance_type_id . $opportunity_id,
								"name" => $policy_opprtunityId,
								"policy_number" =>  $policy_number,
								"policy_type" => $insurance_type_name
							];
						}

					}

				}

			}
			$return['ContactOpportunities.getPoliciesToAssociateWithTask'] = [
				$contact_id => $policies
			];
			return $return;
		}catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: Policies Lising Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
	}
		public function AllPolicyDetails($objectData){
		ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '-1');
		$requestData = array();
		$policy_number = trim($objectData['policy_number']);
		$opportunity_id = $objectData['opportunity_id'];
		$opportunity_id = str_replace("CO-","",$objectData['opportunity_id']);
		$session = Router::getRequest()->getSession();
		$login_agency_id= $session->read("Auth.User.agency_id");
		$user_id =  $session->read('Auth.User.user_id');
		$requestData['user_id'] = $user_id;
		$requestData['agency_id'] = $login_agency_id;
		$requestData['guid'] = '';
		if(!empty($objectData['user_id']) && empty($requestData['user_id'])) 
		{
			$requestData['user_id'] = $objectData['user_id'];
		}
		if(!empty($objectData['agency_id']))
		{
			$requestData['agency_id'] = $objectData['agency_id'];
		}		
		$apiTypes = array(_RATING_DETAILS,_OTHER_DETAILS,_COVERAGE_DETAILS);
		$policyDetails = [];
		$ivans_policy_id = trim($objectData['ivans_policy_id']);
		$ivans_policy_guid = '';
		$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
		$policy_detail = $ContactOpportunities->contactOpportunityDetail($opportunity_id);
		if(isset($policy_detail) && !empty($policy_detail)){
			$contact_business_id = $policy_detail['contact_business_id'];
			$contact_id =  $policy_detail['contact_id'];
			$policy_number = $policy_detail['policy_number'];
		}

		if(isset($contact_business_id) && !empty($contact_business_id)){
			$opportunity_by_policy_number = $ContactOpportunities->getBusinessByPolicyNumber($login_agency_id,$policy_number);
		}else{
			$opportunity_by_policy_number = $ContactOpportunities->getContactByPolicyNumber($login_agency_id,$policy_number);
		}

		if(isset($ivans_policy_id) && !empty($ivans_policy_id)){
			$ivans_policy_guid = $ivans_policy_id;
		}else{
			if(isset($opportunity_by_policy_number) && !empty($opportunity_by_policy_number)){
			 $ivans_policy_guid =  $opportunity_by_policy_number['ivans_policy_id'];
			}else{
				$ivans_policy_guid = $policy_detail['ivans_policy_id'];
			}
		}
		
		if(isset($opportunity_by_policy_number) && !empty($opportunity_by_policy_number)){
			if(isset($opportunity_by_policy_number->umbrella_agency_guid) && !empty($opportunity_by_policy_number->umbrella_agency_guid))
			{
				$requestData['guid'] = $opportunity_by_policy_number->umbrella_agency_guid;
			}
		}

		// if(isset($opportunity_by_policy_number) && !empty($opportunity_by_policy_number)){
		// 	$ivans_policy_guid =  $opportunity_by_policy_number['_matchingData']['ContactPolicyRenewal']['ivans_policy_id'];
		// 	if(isset($opportunity_by_policy_number->umbrella_agency_guid) && !empty($opportunity_by_policy_number->umbrella_agency_guid))
		// 	{
		// 		$requestData['guid'] = $opportunity_by_policy_number->umbrella_agency_guid;
		// 	}
		// }else{
		// 	if(empty($ivans_policy_guid))
		// 	{
		// 		$ivans_policy_guid = $policy_detail['ivans_policy_id'];
		// 	}
		// }
		$allPolicies = ALL_POLICIES_BETA;

		if((isset($policy_number)&& !empty($policy_number)) || (isset($ivans_policy_guid) && !empty($ivans_policy_guid))){
			$policy_number_filter = preg_replace('/[^A-Za-z0-9\-]/', '', $policy_number);
				$policy_number_fltr = str_replace('-','',$policy_number_filter);
				$requestData['request_param'] = array('policy_number'=>$policy_number_fltr);
			foreach($apiTypes  as $apiType)
			{
				if($apiType ==_RATING_DETAILS){
					$requestData['API'] = _RATING_DETAILS_BY_POLICY_ID;
					$requestData['request_param'] = array('policy_id'=>$ivans_policy_guid);
				}
				else if($apiType ==_OTHER_DETAILS){
					$requestData['API'] = _OTHER_DETAILS_BY_POLICY_ID;
					$requestData['request_param'] = array('policy_id'=>$ivans_policy_guid);
				}
				else if($apiType ==_COVERAGE_DETAILS){
					$requestData['API'] = _COVERAGE_DETAILS_BY_POLICY_ID;
					$requestData['request_param'] = array('policy_id'=>$ivans_policy_guid);
				}
				$policy_data = ContactOpportunities::getDataByApi($requestData);
				if(isset($policy_data['httpCode']) && $policy_data['httpCode'] != 200){
					if($apiType ==_RATING_DETAILS){
						$requestData['API'] = _RATING_DETAILS_API;
						$requestData['request_param'] = array('policy_number'=>$policy_number_fltr);
						}
						else if($apiType ==_OTHER_DETAILS){
						$requestData['API'] = _OTHER_DETAILS_API;
						$requestData['request_param'] = array('policy_number'=>$policy_number_fltr);
						}
						else if($apiType ==_COVERAGE_DETAILS){
						$requestData['API'] = _COVERAGE_DETAILS_API;
						$requestData['request_param'] = array('policy_number'=>$policy_number_fltr);
						}
						unset($requestData['request_param']);
						$requestData['request_param'] = array('policy_number'=>$policy_number);
						$policy_data = ContactOpportunities::getDataByApi($requestData);
				}
				if($apiType ==_COVERAGE_DETAILS){
					$result = array();
					if(isset($policy_data['output']['Coverage Details']) && !empty($policy_data['output']['Coverage Details'])){
						foreach ($policy_data['output']['Coverage Details'] as $element) {
						if(empty($element['coverage_code']['Code description']) && !empty($element['coverage_code']['value'])){
							$result[$element['coverage_code']['value']][] = $element;
						}else{
							$result[$element['coverage_code']['Code description']][] = $element;
						}
						}
						$policyDetails['coverage_details'] = array('policy_data' => $policy_data['output']['Coverage Details'],'grouped_coverage_details'=>$result);
					}else{
						$message = 'Policy Number Not Found';
						return array('status' => _ID_FAILED,'message' => $message);
					}
				}else if($apiType ==_RATING_DETAILS){
					if(isset($policy_data['output']['Rating Detail']['line_of_business_code']) && !empty($policy_data['output']['Rating Detail']['line_of_business_code'])){
						$policy_name = $policy_data['output']['Rating Detail']['line_of_business_code'];
					}
					$policyDetails['rating_details'] = $policy_data['output']['Rating Detail'];
				}else{
					$policyDetails['other_details'] =  $policy_data['output']['Other Details'];
				}
			}
			return $policyDetails;
		}
		else{
			$message = 'Policy Number Not Found';
			return array('status' => _ID_FAILED,'message' => $message);
		}
	}

	public function getDataByApi($requestData=null){
		$IvansUserDetail = TableRegistry::getTableLocator()->get('IvansUserDetail');
		$IvansAgencyTokenDetail = TableRegistry::getTableLocator()->get('IvansAgencyTokenDetail');
		$tokenRequestReturn = array(
				   'httpCode' => '',
				   'output' => ''
					);
		if(isset($requestData['agency_id']) && !empty($requestData['agency_id']) && isset($requestData['user_id']) && !empty($requestData['user_id']))
		{
			$checkIvansUserExist = $IvansUserDetail->checkIvansAccessToUser($requestData['agency_id'],$requestData['user_id']);
		}
		if(isset($requestData['guid']) && !empty($requestData['guid'])){
			$checkIvansUserExist = $IvansUserDetail->find('All')->select(['id','user_email','agency_id'])->where(['ivans_agency_guid' => $requestData['guid'],'ivan_status'=>_ID_SUCCESS])->first();
		}

		if(isset($checkIvansUserExist) && !empty($checkIvansUserExist)){
		$agency_id = $checkIvansUserExist['agency_id'];
		$checkIvansAgencyExist = $IvansAgencyTokenDetail->find('All')->select(['id','agency_token'])->where(['agency_id' => $agency_id,'status'=>_ID_SUCCESS])->first();

		if(isset($checkIvansAgencyExist) && !empty($checkIvansAgencyExist)){
		$data = 'email_agency_staff='.$checkIvansUserExist->user_email.'&agency_token='.$checkIvansAgencyExist->agency_token;
		$url = _AUTH_API;
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_URL, $url);
		$headers = array(
		'Content-Type: application/x-www-form-urlencoded'
		);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_VERBOSE, true);
		//for debug only!
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$output=curl_exec($curl);
		$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		$responseArr = json_decode($output,true);
		$tokenRequestReturn['httpCode'] = $httpCode;
		$tokenRequestReturn['output'] = json_decode($output,true);
		if($tokenRequestReturn['httpCode'] ==200){
			$updatUserDetailsToken = $IvansUserDetail->updateAll(['api_auth_token'=>$tokenRequestReturn['output']['token']],['user_email'=>$checkIvansUserExist['user_email']]);
			if($updatUserDetailsToken){
			$data = http_build_query($requestData['request_param']);
			$url = 	$requestData['API'];
			$curl = curl_init($url);
			curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_HEADER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_HTTPHEADER => array(
			'Authorization: Bearer '.$tokenRequestReturn['output']['token'],
			'Accept: application/json',
			'Content-Type: application/x-www-form-urlencoded'
			),
			));
			$output=curl_exec($curl);
			$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$responseArr = json_decode($output,true);
			$tokenRequestReturn['httpCode'] = $httpCode;
			$tokenRequestReturn['output'] = json_decode($output,true);

			return $tokenRequestReturn;
			}else{
				$output = array('error'=>'Failed to update auth api token');
				$tokenRequestReturn['httpCode'] = 405;
				$tokenRequestReturn['output'] = $output;
				return $tokenRequestReturn;
			}
		}else{
			return $tokenRequestReturn;
		}
		}else{
			$output = array('error'=>'Agency Not Found');
			$tokenRequestReturn['httpCode'] = 405;
			$tokenRequestReturn['output'] = $output;
			return $tokenRequestReturn;
		}
	}
	else{
			$output = array('error'=>'User Not Found');
			$tokenRequestReturn['httpCode'] = 405;
			$tokenRequestReturn['output'] = $output;
			return $tokenRequestReturn;
	}

}

	public static function getPolicyTypesByBusinessId($businessId, $fields=null)
	{
		$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
		$virtualFields =
           ([
           'opp_insurance_type_id' => "CONCAT(ContactOpportunities.insurance_type_id, '-', ContactOpportunities.id)",
           'policy_number'=>"ContactOpportunities.policy_number",
           'id'=>"ContactOpportunities.id",
           'type'=>"insurance_types.type",
		   "opp_id"=> "CONCAT('CO-',ContactOpportunities.id)",
		   "insurance_type_id"=> "ContactOpportunities.insurance_type_id",
           ]
       );
       $result= $ContactOpportunities->find()->select($virtualFields)->leftJoin(['insurance_types'],['ContactOpportunities.insurance_type_id=insurance_types.id'])->where(['ContactOpportunities.contact_business_id' => $businessId,'insurance_types.id IS NOT NULL'])->hydrate(false)->toArray();
		$return['ContactOpportunities.getPolicyTypesByBusinessId'] = [
            $businessId => $result
        ];

        return $return;
	}

	public function getPoliciesToAssociateWithSalesPipeLinesTask($opportunityId)
	{
		try
		{
			$myfile = fopen(ROOT."/logs/vueAttachment.log", "a") or die("Unable to open file!");
			$session = Router::getRequest()->getSession();
			$login_agency_id= $session->read("Auth.User.agency_id");
			$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
			$ContactPolicyRenewal = TableRegistry::getTableLocator()->get('ContactPolicyRenewal');
			$opportunities = $ContactOpportunities->getInsuranceTypeByOpportunitiesId($opportunityId,$login_agency_id);


			if (isset($opportunities) && !empty($opportunities)) {
				$insurance_type_name ='';
				$insurance_type_name = $opportunities['insurance_type']['type'];
				$policy_number = $opportunities['policy_number'];
				if(!empty($insurance_type_name) && !empty($opportunities['policy_number']))
				{
					$policy_opprtunityId = ($insurance_type_name.'-'.$opportunities['policy_number']);
				}else{
					if(!empty($insurance_type_name))
					{
						$policy_opprtunityId = $insurance_type_name;
					}
					else if(!empty($opportunities['policy_number'])){
						$policy_opprtunityId = $insurance_type_name.' - '.$opportunities['policy_number'];
					}
				}

				$insurance_type_id = isset($opportunities['insurance_type']['id']) ? $opportunities['insurance_type']['id'] : '';
				$opportunity_id = !empty($opportunities['id']) ? "-" . $opportunities['id'] : '';
				if(!empty($policy_opprtunityId))
				{
					$policies[] = [
						"id" => $insurance_type_id . $opportunity_id,
						"name" => $policy_opprtunityId,
						"policy_number" =>  $policy_number,
						"policy_type" => $insurance_type_name
					];
				}

			}
			$return['ContactOpportunities.getPoliciesToAssociateWithSalesPipeLinesTask'] = [
				$opportunityId => $policies
			];
			return $return;
		}catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: Policies Lising Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
	}

	//get opperunty
	public function getAttachmentPolicy($objectData){
		$ContactPolicyAttachments = TableRegistry::getTableLocator()->get('ContactPolicyAttachments');
		$awsBucket = AWS_BUCKET_NAME;
		if(!empty($objectData['opd_id'])){
				$oppId = str_replace("CO-","",$objectData['opd_id']);
				$info = [];
				$countAttachment = 0;
				$ContactPolicyAttachments = $ContactPolicyAttachments->getAttachmentDetailByPolicyId($oppId);
				foreach($ContactPolicyAttachments as $attachValue)
				{
					$ext = substr(strtolower(strrchr($attachValue['name'], '.')), 1);
					$file = '';
						$businessId = $attachValue['contact_attachment']['contact_business_id'];
						$file_aws_key = $attachValue['contact_attachment']['file_aws_key'];

						$file_url_key = $attachValue['contact_attachment']['file_url'];

						if(isset($attachValue['id']) && !empty($attachValue['id']))
						{
						$id = $attachValue['id'];
						}
						if(isset($attachValue['created']) && !empty($attachValue['created']))
						{
						$created = $attachValue['created'];
						}

						if(!empty($businessId))
						{
						$folder_name = "business_";
						}else
						{
						$folder_name = "contact_";
						}

						$file_path_basic = WWW_ROOT.'uploads/'.$folder_name.$attachValue['contact_id'] .'/'. $attachValue['name'];

						$file_path_default = WWW_ROOT.'uploads/'. $attachValue['name'];
						$download_link ='';
						if(isset($file_aws_key) && !empty($file_aws_key))
						{

							if(Aws::awsFileExists($file_aws_key))
							{
										$file = $file_url_key;
										//temporary link set for downloads
										$bucketdata=['bucket'=>$awsBucket,
										'keyname'=>$file_aws_key];
										$download_link = (string)Aws::setPreAssignedUrl($bucketdata);
							}

						}else if (file_exists($file_path_basic))
						{
						$file = SITEURL.'uploads/'.$folder_name.$attachValue['contact_id'] .'/'. $attachValue['name'];

						}else if (file_exists($file_path_default)) {
						$file = SITEURL.'uploads/'. $attachValue['name'];
						}

						if(isset($attachValue['display_name']) && !empty($attachValue['display_name'])){
						$display_name = $attachValue['display_name'];
						$final_display_name = strlen($display_name) > 10 ? substr($display_name,0,10)."..." : $display_name;
						}else{
							$display_name = $attachValue['name'];
						$final_display_name = strlen($display_name) > 10 ? substr($display_name,0,10)."..." : $display_name;
						}
						if(empty($download_link)){
						$download_link = $file;
						}
						if(isset($attachValue->user->first_name) && !empty($attachValue->user->first_name) && isset($attachValue->user->last_name) && !empty($attachValue->user->last_name)){
						$user_name = ucfirst($attachValue->user->first_name).' '.ucfirst($attachValue->user->last_name);
						}else{
						$user_name='';
						}

						if(isset($download_link) && !empty($download_link)){
						$download_link_new = $download_link;
						}
						$file_url = SITEURL.'attachments/previewAttachments?url='.$download_link;
						if(isset($file_url) && !empty($file_url)){
						$file_url_new = $file_url;
						}
						$info[$countAttachment]['id'] = $id;
						$info[$countAttachment]['display_name'] = $display_name;
						$info[$countAttachment]['user_name'] = $user_name;
						$info[$countAttachment]['created'] = $created;
						$info[$countAttachment]['download_link'] = $download_link_new;
						$info[$countAttachment]['file_url_new'] = $file_url_new;
					$countAttachment++;
				}
				$return['ContactOpportunities.getAttachmentPolicy'] = [
					'CO-'.$oppId => $info
				];
				return $return;
		}
	}

    public function getCoverageDetails($objectData)
    {
        $session = Router::getRequest()->getSession();
        $agencyId = $session->read("Auth.User.agency_id");
        $policyCoverageDetail = TableRegistry::getTableLocator()->get('PolicyCoverageDetails');
        $oppId = '';
        $insuranceTypeId = '';
        $coverageDetailFields = '';
        $coverageData = '';
        if(isset($objectData) && !empty($objectData))
        {
            if(isset($objectData['opp_id']) && !empty($objectData['opp_id']))
            {
                $oppId =  str_replace("CO-", "", $objectData['opp_id']);
            }
            if(isset($objectData['insurance_type_id']) && !empty($objectData['insurance_type_id']))
            {
                $insuranceTypeId = $objectData['insurance_type_id'];
            }

        }
		$coverageDetailFields = CoverageDetail::getPolicyFields($insuranceTypeId);
        $coverageDetails = $policyCoverageDetail->getCoverageDetailsByOppId($agencyId,$oppId);
        $coverageData = [
            'coverageFields' => $coverageDetailFields,
            'coverageDetails' => $coverageDetails
        ];
         $response = json_encode(array('status' => _ID_SUCCESS,'data' => $coverageData));
		return $response;
    }

	public function saveCoverageDetails($objectData)
    {
        $session = Router::getRequest()->getSession();
        $agencyId = $session->read("Auth.User.agency_id");
        $policyCoverageDetail = TableRegistry::getTableLocator()->get('PolicyCoverageDetails');
        $policyCoverageDetailHistory = TableRegistry::getTableLocator()->get('PolicyCoverageDetailHistory');
        if(isset($objectData) && !empty($objectData))
        {
            if(!empty($objectData['opp_id'])) {
                $oppId = str_replace("CO-", "", $objectData['opp_id']);
            }
            if(!empty($objectData['coverageData'])) {
                $coverageData = json_encode($objectData['coverageData']);
            }
            $getCoverageDetails = $policyCoverageDetail->getCoverageDetailsByOppId($agencyId,$oppId);
            if(!empty($getCoverageDetails))
            {
                $previousCoveragedata = [];
                $previousCoveragedata['policy_coverage_detail_id'] = $getCoverageDetails['id'];
                $previousCoveragedata['coverage_detail'] = $getCoverageDetails['coverage_detail'];;
                $previousCoverage = $policyCoverageDetailHistory->newEntity();
                $coverageHistory = $policyCoverageDetailHistory->patchEntity($previousCoverage,$previousCoveragedata);
                if($policyCoverageDetailHistory->save($coverageHistory))
                {
                    $data = [];
                    $data['agency_id'] = $agencyId;
                    $data['opportunity_id'] = $oppId;
                    $data['coverage_detail'] = $coverageData;
//                    $coverageDetail = $policyCoverageDetail->get($getCoverageDetails['id']);
                    $coverageDetails = $policyCoverageDetail->patchEntity($getCoverageDetails, $data);
                    if($policyCoverageDetail->save($coverageDetails))
                    {
                        $response = json_encode(array('status' => _ID_SUCCESS));
                    }
                    else
                    {
                        $response = json_encode(array('status' => _ID_FAILED));
                    }
                }
            }
            else
            {
                $data = [];
                $data['agency_id'] = $agencyId;
                $data['opportunity_id'] = $oppId;
                $data['coverage_detail'] = $coverageData;
                $coverageDetail = $policyCoverageDetail->newEntity();
                $coverageDetails = $policyCoverageDetail->patchEntity($coverageDetail, $data);
                if($policyCoverageDetail->save($coverageDetails))
                {
                    $response = json_encode(array('status' => _ID_SUCCESS));
                }
                else
                {
                    $response = json_encode(array('status' => _ID_FAILED));
                }
            }
            return $response;
        }

    }
	public function getIvansPolicyDetails($oppId)
	{
		$oppId = str_replace("CO-","",$oppId);
		$session = Router::getRequest()->getSession();
		$login_agency_id = $session->read("Auth.User.agency_id");
		$login_user_id = $session->read('Auth.User.user_id');
		$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
		$IvansUserDetail = TableRegistry::getTableLocator()->get('IvansUserDetail');
		$contactOpportunities = $ContactOpportunities->getPolicyDetailsByOppid($oppId);
		$ivansDetails = $IvansUserDetail->checkIvansAccessToUser($login_agency_id,$login_user_id);
        $policy_guid = '';
        $allPolicies = ALL_POLICIES_BETA;
        if($contactOpportunities['ivans_policy_id']){
            $policy_guid = $contactOpportunities['ivans_policy_id'];
        }
		if($contactOpportunities['policy_number']){
            $policy_number = $contactOpportunities['policy_number'];
        }
		if($contactOpportunities['ivans_policy_status']){
            $policy_status = $contactOpportunities['ivans_policy_status'];
        }
        if(isset($ivansDetails['ivans_agency_guid']) && !empty($ivansDetails['ivans_agency_guid']) && (!empty($policy_guid) || !empty($policy_status)))
        {
            $ivans_agency_guid = $ivansDetails['ivans_agency_guid'];
            if(isset($ivans_agency_guid) && !empty($ivans_agency_guid))
			{
				
				 if(!empty($policy_guid)) {
					 $requestData['API'] = _GET_POLICY_SUMMERY_BY_POLICY_ID;
					 $requestData['request_param'] = array('policy_id'=>$policy_guid);
				 } else {
					 $requestData['API'] = _GET_POLICY_SUMMERY_BY_POLICY_NUMBER;
					 unset($requestData['request_param']);
					 $requestData['request_param'] = array('policy_number'=>$policy_number);
				 }
				
				 $requestData['guid'] = $ivans_agency_guid;
				 $requestData['agency_id'] = $login_agency_id;
				 $policy_api_response = ContactOpportunities::getDataByApi($requestData);
				if(isset($policy_api_response['output']['policies']['line_of_business_code_short']) && !empty($policy_api_response['output']['policies']['line_of_business_code_short']))
				{
					$policy_name = $policy_api_response['output']['policies']['line_of_business_code_short'];
					$file_name='';
					foreach($allPolicies as $key => $value) {
						//print_r($key);
						if(in_array($policy_name, $value))
						{
							$file_name = str_replace(' ', '_', strtolower($key));
						}
					}
					if(empty($file_name)){
						$file_name = 'other';
					}
					$contactOpportunity['file_name'] = $file_name;
				}
            }
        }
		$return['ContactOpportunities.getIvansPolicyDetails'] = [
            'CO-'.$oppId => $contactOpportunity
        ];
        return $return;

	}

	// get policy for view table
	public function getAllPoliciesTableView($objectData){
		try{

			$session = Router :: getRequest()->getSession();
			$loginAgencyId = $session->read("Auth.User.agency_id");
			$searchArr = array();
			if(isset($objectData['contact_id']) && !empty($objectData['contact_id']))
			{
            	$searchArr['contact_id'] = $objectData['contact_id'];
        	}
			if(isset($objectData['keyword']) && !empty($objectData['keyword']))
			{
           		$searchArr['keyword'] = $objectData['keyword'];
        	}
			if(!empty($objectData['sort_by']) && !empty($objectData['sort_type']))
			{
				$searchArr['sort_by'] = $objectData['sort_by'];
				$searchArr['sort_type'] = $objectData['sort_type'];
			}
			if(!empty($loginAgencyId) && !empty($loginAgencyId))
			{
				$searchArr['login_agency_id'] = $loginAgencyId;
			}

			$ContactOpportunities = TableRegistry :: getTableLocator()->get('ContactOpportunities'); //table set
			$Contacts = TableRegistry::getTableLocator()->get('Contacts');

			// get contact details 
			$contact = $Contacts->contactDetails($objectData['contact_id']);
			if($contact['additional_insured_flag'] == _ID_STATUS_ACTIVE)
			{
				$ContactsAdditionalInsured = TableRegistry::getTableLocator()->get('ContactAdditionalInsuredPolicyRelation');   
				$mappedPolicies = $ContactsAdditionalInsured->getMappedPolicyWithPrimaryContact($objectData['contact_id']);
				if(count($mappedPolicies) > 0)
				{
					$searchArr['contact_primary_id'] = $mappedPolicies[0]['contact_id'];
					$contatOpportunityIds = array_column($mappedPolicies,'contact_opportunity_id');
					$searchArr['contact_opportunity_ids'] = $contatOpportunityIds;
					$getPolicyTableView = $ContactOpportunities->getPolicyListingTableView($searchArr);
				}
			}
			else
			{
				$getPolicyTableView = $ContactOpportunities->getPolicyListingTableView($searchArr);
			}
			$return['ContactOpportunities.getAllPoliciesTableView'] = [
				$objectData['contact_id'] => $getPolicyTableView
			];

			return $return;

		}catch(\Exception $e){
			$txt=date('Y-m-d H:i:s').' :: all policy not get for view table- '.$e->getMessage();
			FileLog :: writeLog("policies_able_view_error", $txt);
		}
	}
	//export all policies by contact_id
	public function exportPoliciesTableView($objectData){
		$ContactOpportunities = TableRegistry :: getTableLocator()->get('ContactOpportunities');
		$ContactPolicyLostDetails = TableRegistry :: getTableLocator()->get('ContactPolicyLostDetails');
		$session = Router :: getRequest()->getSession();
		$loginAgencyId = $session->read("Auth.User.agency_id");

		// get policy all for view table 
		$objectData['login_agency_id'] = $loginAgencyId;
		$ContactOpportunities = TableRegistry :: getTableLocator()->get('ContactOpportunities'); //table set
		$Contacts = TableRegistry::getTableLocator()->get('Contacts');

		// get contact details 
		$contact = $Contacts->contactDetails($objectData['contact_id']);
		if($contact['additional_insured_flag'] == _ID_STATUS_ACTIVE)
		{
			$ContactsAdditionalInsured = TableRegistry::getTableLocator()->get('ContactAdditionalInsuredPolicyRelation');   
			$mappedPolicies = $ContactsAdditionalInsured->getMappedPolicyWithPrimaryContact($objectData['contact_id']);
			if(count($mappedPolicies) > 0)
			{
				$objectData['contact_primary_id'] = $mappedPolicies[0]['contact_id'];
				$contatOpportunityIds = array_column($mappedPolicies,'contact_opportunity_id');
				$objectData['contact_opportunity_ids'] = $contatOpportunityIds;
				$contactOpportunities = $ContactOpportunities->getPolicyListingTableView($objectData);
			}
		}
		else
		{
			$contactOpportunities = $ContactOpportunities->getPolicyListingTableView($objectData);
		}

		$objPHPExcel = new \PHPExcel();
		$usersTimezone='America/Phoenix';
		$policiesColArray = array('0' => 'policy title',
			                      '1' => 'status',
								  '2' => 'LINE OF BUSINESS',
								  '3' => 'POLICY NUMBER',
								  '4' => 'CARRIER',
								  '5' => 'EFFECTIVE DATE',
								  '6' => 'TERM',
								  '7' => 'PREMIUM'
								 );
        $col = 'A';
    	$rowcol = 1;
        $celobj = $objPHPExcel->setActiveSheetIndex(0);

        foreach($policiesColArray as $headers=>$header){

            $celobj->setCellValue($col . "" . $rowcol, $header);
            $col++;

        }

		$row = 2;

		foreach($contactOpportunities as $key => $value)
		{

			//status set
			$policyStatus = '';
			if($value['status'] == _ID_STATUS_PENDING)
            {
                //check policy is future cancellation or not
				$policyStatus = '';
                $cancellation_date = "";
                $policy_lost_details = $ContactPolicyLostDetails->policyLostDetailsByOppID($value['id']);
                if(isset($policy_lost_details) && !empty($policy_lost_details['cancellation_date']))
                {
                    $cancellation_date=$policy_lost_details['cancellation_date'];
                }

                if(isset($cancellation_date) && !empty($cancellation_date) && $value['status'] != _ID_STATUS_INACTIVE && date('Y-m-d', strtotime($cancellation_date)) >= date('Y-m-d', strtotime(CommonFunctions :: convertUtcToEmployeeTimeZone($usersTimezone, date("Y-m-d H:i:s")))))
                {
                    $policyStatus = 'Future Cancellation';
                }
                else
				{
                    $policyStatus = 'Pending';
                }
            } 
			else if($value['status'] == _ID_STATUS_ACTIVE)
            {
				 //check policy is future cancellation or not
                $cancellation_date = "";
				$policyStatus = "";
                $policy_lost_details = $ContactPolicyLostDetails->policyLostDetailsByOppID($value['id']);
                if(isset($policy_lost_details) && !empty($policy_lost_details['cancellation_date']))
                {
					$cancellation_date = date('M d, Y', strtotime($policy_lost_details['cancellation_date']));
                }
                //
				$policy_renwed_cancellation_date = "";
				$cancelledPolicydetailsByIvans = $ContactPolicyLostDetails->getCancelledPoliciesByRenewalFromIvans($value['id']);
				if(isset($cancelledPolicydetailsByIvans) && !empty($cancelledPolicydetailsByIvans['cancellation_date']))
				{
					$policy_renwed_cancellation_date = $cancelledPolicydetailsByIvans['cancellation_date'];
				}
				if(isset($policy_renwed_cancellation_date) && !empty($policy_renwed_cancellation_date) && $value['status'] != _ID_STATUS_INACTIVE && date('Y-m-d', strtotime($cancellation_date)) >= date('Y-m-d', strtotime(CommonFunctions :: convertUtcToEmployeeTimeZone($usersTimezone, date("Y-m-d H:i:s")))))
				{
					$policyStatus = 'Active';
				}
                else if(isset($cancellation_date) && !empty($cancellation_date) && $value['status'] != _ID_STATUS_INACTIVE && date('Y-m-d', strtotime($cancellation_date))>=date('Y-m-d', strtotime(CommonFunctions :: convertUtcToEmployeeTimeZone($usersTimezone, date("Y-m-d H:i:s")))))
                {
                    $policyStatus = 'Future Cancellation';
                }
                else if(isset($value['effective_date']) && !empty($value['effective_date']) && date('Y-m-d', strtotime($value['effective_date']))>date('Y-m-d', strtotime(CommonFunctions :: convertUtcToEmployeeTimeZone($usersTimezone, date("Y-m-d H:i:s")))))
                {
                    $policyStatus = 'Future Effective';
                }
                else
                {
                	$policyStatus = 'Active';
                }

            }
            else if($value['status'] == _ID_STATUS_CANCELLED || $value['status'] == _ID_STATUS_INACTIVE)
			{
                $policyStatus = 'Inactive';
            }

			$policyNumber = '';
			$policyNumber = ($value['policy_number'])?$value['policy_number']:'';
			if(is_numeric($policyNumber))
			{
                $policyFirstChar = substr($policyNumber, 0, 1);
                if($policyFirstChar==0)
				{
                    $policyNumber = "\t$policyNumber";
                }
				else
				{
                    $policyNumber = "\t$policyNumber";
                }
            }

			$carrier_name = '';
			if(isset($value['carrier']['master_carrier_id']) && !empty($value['carrier']['master_carrier_id']))
            {
                $carrier_name = $value['carrier']['parent_name'];
            }
            else
            {
                $carrier_name = $value['carrier']['name'];
            }

			// effective_date set
			$effective_date = '';
			if(isset($value['effective_date']) && !empty($value['effective_date'])){
				$effective_date = date('M d, Y', strtotime($value['effective_date']));
			}
			$termLength = '';
			if(!empty($value['term_length'])){
				$termLength = $value['term_length'] . 'M';
			}

			$premiumAmt = '';
			if(!empty($value['premium_amount']))
			{
				$premiumAmt = '$' . number_format($value['premium_amount'],2);
			}
			$policyTitle = '';
			if(!empty(trim($value['sales_title']))){
				$policyTitle = $value['sales_title'];
			}
			else{
				$policyTitle = $value['insurance_type']['type'];
			}
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue('A' . $row, $policyTitle)
						->setCellValue('B' . $row, $policyStatus)
						->setCellValue('C' . $row, $value['insurance_type']['type'])
						->setCellValue('D' . $row, $policyNumber)
						->setCellValue('E' . $row, $carrier_name)
						->setCellValue('F' . $row, $effective_date)
						->setCellValue('G' . $row, $termLength)
						->setCellValue('H' . $row, $premiumAmt);

			$row++;
			$objPHPExcel->getActiveSheet()
    					->getStyle('C:C'+$row)
    					->getAlignment()
    					->setHorizontal(\PHPExcel_Style_Alignment :: HORIZONTAL_CENTER);
		}

		$objWriter = \PHPExcel_IOFactory :: createWriter($objPHPExcel, 'CSV');

		ob_start();
		$objWriter->save("php://output");
		$xlsData = ob_get_contents();
		ob_end_clean();

		$response =  array(
			'op' => 'ok',
			'file' => "data:application/vnd.ms-excel;base64," . base64_encode(urldecode($xlsData))
		);

		$return['ContactOpportunities.exportPoliciesTableView'] = [
				$objectData['contact_id'] => $response
		];

		return $return;
	} 

	public function getSecondaryContactPolicies($secondaryContactId)
	{
		try
		{
			$contactAdditionalInsuredPolicyRelation = TableRegistry::getTableLocator()->get('ContactAdditionalInsuredPolicyRelation');
			$primaryContact = $contactAdditionalInsuredPolicyRelation->getPrimaryContactByContactId($secondaryContactId);
			$contactId = $primaryContact['contact_id'];
			$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
			$allContactOpportunities = $ContactOpportunities->activeInactivePendingPolicyListing($contactId);
			$i = 0;
			$activeFlag = _ID_STATUS_ACTIVE;
			$inActiveFlag = _ID_STATUS_INACTIVE;
			$activePolicies = array_filter($allContactOpportunities, function ($items) use ($activeFlag) {
                return ($items['status'] == $activeFlag);
             });
			 $inActivePolicies = array_filter($allContactOpportunities, function ($items) use ($inActiveFlag) {
                return ($items['status'] == $inActiveFlag);
             });
			 $allPolicies = array();
			 $allPolicies = array_merge($allPolicies,$activePolicies);
			 $allPolicies = array_merge($allPolicies,$inActivePolicies);

			foreach($allPolicies as $opportunityData)
			{
				$date = date('M d, Y',strtotime($opportunityData['effective_date']));
				if($opportunityData['status'] == 1)
				{
					$policies[$i] = [
						"id" => $opportunityData['id'],
						"policyType" => ucfirst($opportunityData['insurance_type']['type']).' - '.$opportunityData['policy_number'].' - Effective '.$date,
						"status" => $opportunityData['status']
					];
				}
				if($opportunityData['status'] == 2)
				{
					$policies[$i] = [
						"id" => $opportunityData['id'],
						"policyType" => ucfirst($opportunityData['insurance_type']['type']).' - '.$opportunityData['policy_number'].' - Effective '.$date,
						"status" => $opportunityData['status']
					];
				}
				$i++;
			}

			$return['ContactOpportunities.getSecondaryContactPolicies'] = [
				$secondaryContactId => $policies
			];
			return $return;
        }
		catch (\Exception $e)
		{
            $txt=date('Y-m-d H:i:s').' :: Map policy Error- '.$e->getMessage();
        }
	}

	public function saveSecondaryContactPolicies($secondaryContactDetails)
	{
		try
		{
			$policyId = $secondaryContactDetails[0];
			$secondaryContactId = $secondaryContactDetails[1];
			$contactAdditionalInsuredPolicyRelation = TableRegistry::getTableLocator()->get('ContactAdditionalInsuredPolicyRelation');
			$primaryContact = $contactAdditionalInsuredPolicyRelation->getPrimaryContactByContactId($secondaryContactId);
			$contactId = $primaryContact['contact_id'];
			$data = [];
			$data['contact_id'] =  $contactId;
			$data['additional_insured_contact_id'] = $secondaryContactId;
			$data['contact_opportunity_id'] = $policyId;
			$data['previous_opportunity_id'] = $policyId;
			$data['is_policy_relation'] = _ID_SUCCESS;
			if(isset($contactAdditionalInsuredPolicyRelation) && !empty($contactAdditionalInsuredPolicyRelation))
			{
				$checkNotEmptyMapPolicy = $contactAdditionalInsuredPolicyRelation->contactAdditionalNOTEmptyMap($contactId, $secondaryContactId, $policyId);
				if(!empty($checkNotEmptyMapPolicy))
				{
					$response = json_encode(array('status' => _ID_SUCCESS));
					return $response;
				}
				else
				{
					$checkEmptyMapPolicy = $contactAdditionalInsuredPolicyRelation->contactAdditionalEmptyMap($contactId, $secondaryContactId);
					if(!empty($checkEmptyMapPolicy))
					{
						if($contactAdditionalInsuredPolicyRelation->updateAll(['contact_opportunity_id' => $policyId],['id' => $checkEmptyMapPolicy['id'],'additional_insured_contact_id' => $secondaryContactId]))
						{
							$response = json_encode(array('status' => _ID_SUCCESS));
						}
						else
						{
							$response = json_encode(array('status' => _ID_FAILED));
						}
						return $response;
					}
					else
					{
						$contactAdditionalInsuredData = $contactAdditionalInsuredPolicyRelation->newEntity();
						$contactAdditionalInsuredDatas = $contactAdditionalInsuredPolicyRelation->patchEntity($contactAdditionalInsuredData,$data);
						if($contactAdditionalInsuredPolicyRelation->save($contactAdditionalInsuredDatas))
						{
							$response = json_encode(array('status' => _ID_SUCCESS));
						}
						else
						{
							$response = json_encode(array('status' => _ID_FAILED));
						}
						return $response;
					}

				}
			}
		}
		catch (\Exception $e)
		{
			$txt=date('Y-m-d H:i:s').' :: Map policy Error- '.$e->getMessage();
		}
	}

//	reinstate policy
	public function reinstateCancelledPolicy($objectData)
	{
		if(!empty($objectData['opp_id'])) {
			$oppId = str_replace("CO-", "", $objectData['opp_id']);
		}
		$contactId = $objectData['contact_id'];
		$session = Router :: getRequest()->getSession();
		$login_agency_id = $session->read("Auth.User.agency_id");
		$login_user_id = $session->read('Auth.User.user_id');
		$response = Policy::reinstatePolicy($oppId, $login_user_id);
		return json_encode($response);

	}

	public static function getAllPolicyTypes($objectData)
	{
		$session = Router::getRequest()->getSession();
        $agencyId = $session->read("Auth.User.agency_id");
		$personalCommercial = _PERSONAL_CONTACT;
		if(isset($objectData['contactId']) && !empty($objectData['contactId']))
		{
			$contactId = $objectData['contactId'];
		}
		if(isset($objectData['selectedParentCarrierId']) && !empty($objectData['selectedParentCarrierId']))
		{
			$carrierId = $objectData['selectedParentCarrierId'];
		}
		$insuranceTypes = [];
		$insuranceLists = [];
		$InsuranceTypesTable = TableRegistry::getTableLocator()->get('InsuranceTypes');
		$insuranceTypesArr = $InsuranceTypesTable->insuranceListByAgencyIdAndCarrierId($carrierId, $agencyId, $personalCommercial);
		if(!empty($insuranceTypesArr))
		{
			foreach ($insuranceTypesArr as $insuranceTypesPersonal)
			{
				$insuranceTypes['id'] = (int)$insuranceTypesPersonal['id'];
				$insuranceTypes['type'] = $insuranceTypesPersonal['type'];
				array_push($insuranceLists, $insuranceTypes);
			}
		}

		$return['ContactOpportunities.getAllPolicyTypes'] = [
            $contactId => $insuranceLists
        ];

        return $return;
	}

}
?>
