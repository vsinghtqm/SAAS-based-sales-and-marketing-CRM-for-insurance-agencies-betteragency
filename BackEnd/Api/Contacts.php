<?php

namespace App\Lib\ApiProviders;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use App\Classes\CommonFunctions;
use App\Classes\Ivans;
use App\Classes\TwilioSms;

use App\Lib\QuickTables\ContactDetailsQuickTable;
use App\Lib\QuickTables\ContactsQuickTable;
use App\Lib\QuickTables\ModelQuickTable;
use App\Lib\QuickTables\Virtual_ContactNoteTypesQuickTable;
use App\Lib\QuickTables\UsersQuickTable;
use App\Lib\QuickTables\PhoneNumbersOptInOutStatusQuickTable;
use App\Lib\QuickTables\ReferralPartnerUserContactQuickTable;
use App\Lib\QuickTables\Virtual_ContactSummariesQuickTable;
use App\Lib\QuickTables\ContactsMalingAddressQuickTable;
use Cake\Http\Exception\UnauthorizedException;
use App\Lib\BetterAgency\ObjectFetcher;
use App\Classes\FileLog;
use App\Classes\SlackNotifications;
use Cake\Controller\ComponentRegistry;
use App\Controller\Component\IvansComponent;
use App\Lib\NowCerts\NowCertsApi;



class Contacts
{


    public function updateContact($responseData)
    {
        try
        {
		$myfile = fopen(ROOT."/logs/contactrail.log", "a") or die("Unable to open file!");
        $session = Router::getRequest()->getSession();
		$login_user_id= $session->read("Auth.User.user_id");
        $login_agency_id= $session->read("Auth.User.agency_id");
        $contacts = TableRegistry::getTableLocator()->get('Contacts');
        $contactsMailingAddress = TableRegistry::getTableLocator()->get('ContactsMailingAddress');
        $ContactEmails = TableRegistry::getTableLocator()->get('ContactEmails');
        $ContactPhoneNumbers = TableRegistry::getTableLocator()->get('ContactPhoneNumbers');
		$response  = [];
        $contact_logs_array = [];
		$objectData = $responseData;
		// echo "<pre>";print_r($objectData);die("Sdfsdf");
        if(isset($objectData) && !empty($objectData)){
            $id = $objectData['id'];
            $contact = $contacts->get($id, [
                'contain' => []
            ]);
	        $contact_logs_array['first_name'] = $contact->first_name;
	        $contact_logs_array['last_name'] = $contact->last_name;
	        $contact_logs_array['email'] = $contact->email;
	        $contact_logs_array['phone'] = $contact->phone;
	        $contact_logs_array['message'] = 'Contact updated by User ID: '. $login_user_id . ' | Contact info before update saved with log: ';
	        $contact_logs_array['message'] .= 'Name: '. $contact->first_name . ' ' . $contact->last_name . ' | ';
	        $contact_logs_array['message'] .= 'Email: '. $contact->email . ' | ';
	        $contact_logs_array['message'] .= 'Phone: '. $contact->phone;
            $phoneArr = [];
            //echo "<pre>";print_r($contact);die("Sdfsdf");

            $data['phone'] = trim($objectData['phone']);
            $data['phone_number_type'] = $objectData['phone_number_type'];
            $data['do_not_contact'] = $objectData['do_not_contact'];
            $data['email'] = trim($objectData['email']);
            $data['email_type'] = $objectData['email_type'];
            $data['best_time_to_reach'] = $objectData['best_time_to_reach'];
            $data['address'] = $objectData['address'];
            $data['address_line_2'] = $objectData['address_line_2'];
            $data['city'] = $objectData['city'];
            $data['state_id'] = $objectData['state_id'];
            $data['county_id'] = $objectData['county_id'];
            $data['zip'] = $objectData['zip'];
            $data['phone_ext'] = $objectData['phone_ext'];
            if(isset($responseData['mailing_address_type']) && $responseData['mailing_address_type'] == '') {
                $data['mailing_address_type'] = _MAILING_ADDRESS_SAME;
            }else{
                $data['mailing_address_type'] = _MAILING_ADDRESS_DIFFERENT ;
            }
            $mailing_address_arr['mailing_address_1'] = $responseData['mailing_address_1'];
            $mailing_address_arr['mailing_address_2'] = $responseData['mailing_address_2'];
            $mailing_address_arr['mailing_city'] = $responseData['mailing_city'];
            $mailing_address_arr['mailing_state_id'] = $responseData['mailing_state_id'];
            $mailing_address_arr['mailing_county_id'] = $responseData['mailing_county_id'];
            $mailing_address_arr['mailing_zip'] = $responseData['mailing_zip'];
			 // Do Not Contact
			if(isset($objectData['do_not_contact']) && !empty($objectData['do_not_contact']))
			{
				$data['do_not_contact']= _ID_STATUS_ACTIVE;
			}else{
				$data['do_not_contact'] = _ID_FAILED;
			}
            if(isset($objectData['client_since']) && !empty($objectData['client_since'])){
                $data['client_since'] = date('Y-m-d', strtotime($objectData['client_since']));
            }
            //add entry in contact logs
            /*if(strtolower($data['email']) != strtolower($contact->email)){
                $contact_logs_array['email'] = strtolower($contact->email);
            }*/



			$checkRecordExist = _ID_SUCCESS;
			 $emailArr[0] = strtolower($data['email']);
			 //email validation

			/* Comment this validation as per requirement in 10488
			  if(isset($data['email']) && !empty($data['email']))
			{
				$checkEmail=$contacts->checkExistingContactEmailExist($data['email'],$login_agency_id,$id);
				if(!empty($checkEmail))
				{
					$checkRecordExist = _ID_FAILED;
					$response =  json_encode(array('message_status' => _ID_FAILED,'message' => $data['email'].' already exist. Try with another email.'));
				}


			}*/
			//phone validation
			if(isset($data['phone']) && !empty($data['phone']))
			{
			// Comment this validation as per requirement in 10488
                $patterns = array('/\-/', '/\(/', '/\)/', '/\+/', '/\ /');
                $data['phone'] = preg_replace($patterns, '', $data['phone']);
                $phoneArr[0] =$data['phone'];
				//$phone_validate =  CommonFunctions::validate_mobile($data['phone']);
				//if($phone_validate == _ID_FAILED)
				//{
				//	$checkRecordExist = _ID_FAILED;
				//	$response = json_encode(array('message_status' => _ID_FAILED,'message' => 'Please enter valid phone number.'));
				//}
				//else
				//{

				//	$checkPhone = $contacts->checkExistingContactPhoneExist($data['phone'],$login_agency_id,$id);

				//	if(!empty($checkPhone))
				//	{
				//		$checkRecordExist = _ID_FAILED;
				//		$response = json_encode(array('message_status' => _ID_FAILED,'message' => 'Phone already exist. Try with another phone.'));
				//	}else{

                        $optoutData = [];
                        $optoutData['agency_id'] = $login_agency_id;
                        $optoutData['user_id'] = $login_user_id;
                        $optoutData['phone_number'] = $data['phone'];
                        $optoutData['contact_id'] = $id;
                        $optoutData['platform'] = _PLATFORM_TYPE_SYSTEM;
                        if(isset($responseData['sms_permission']))
                        {
                            if($responseData['sms_permission'] == true)
                            {
                                $optoutData['status'] = _ID_SUCCESS;
                            }
                            elseif($responseData['sms_permission'] == false)
                            {
                                $optoutData['status'] = _ID_FAILED;
    
                            }                            
                        }
                        CommonFunctions::saveSmsOptInOutStatus($optoutData);

                        $today_date_time = date('Y-m-d H:i:s');

                        if(isset($contact) && !empty($contact))
                        {
                            $contactPreviousPhoneNumber = $contact['phone'];
							$contactPreviousEmail = $contact['email'];
							$contactPreviousEmailType = $contact['email_type'];
                            $contactPreviousPhoneNumberType = $contact['phone_number_type'];

                            if(isset($contact['previous_phone_numbers']) && !empty($contact['previous_phone_numbers']))
                            {
                                $data['previous_phone_numbers'] = $contact['previous_phone_numbers'].','.$contactPreviousPhoneNumber.' type_'.$contactPreviousPhoneNumberType.' '.$today_date_time;
                            }
                            else
                            {
                                 $data['previous_phone_numbers'] = $contactPreviousPhoneNumber.' type_'.$contactPreviousPhoneNumberType.' '.$today_date_time;
                            }
                        }
                        /*if($data['phone'] != $contact->phone){
                            $contact_logs_array['phone'] = $contact->phone;
                        }*/
                    //}
				//}
			}

            $contact = $contacts->patchEntity($contact, $data);

			if($checkRecordExist == _ID_SUCCESS){
				if($contact = $contacts->save($contact)){

                    if(isset($contactPreviousPhoneNumber) && !empty($contactPreviousPhoneNumber))
                    {
                        if(isset($objectData['phoneNumberId']) && !empty($objectData['phoneNumberId']))
                        {
                            $contactPhoneNumbersUpdate = [];
                            $contactPhoneNumbersUpdate['phone_number_value'] = $contactPreviousPhoneNumber;
                            $contactPhoneNumbersUpdate['phone_number_type'] = $contactPreviousPhoneNumberType;
                            $ContactPhoneNumbers->updateAll($contactPhoneNumbersUpdate, ['id' => $objectData['phoneNumberId']]);
                        }
                    }

					if(isset($contactPreviousEmail) && !empty($contactPreviousEmail))
                    {
                        if(isset($objectData['emailId']) && !empty($objectData['emailId']))
                        {
                            $contactEmailUpdates = [];
                            $contactEmailUpdates['email'] = $contactPreviousEmail;
                            $contactEmailUpdates['email_type'] = $contactPreviousEmailType;
                            $ContactEmails->updateAll($contactEmailUpdates, ['id' => $objectData['emailId']]);
                        }
                    }
					if(!array_filter($mailing_address_arr) && !empty($responseData['remove_mailing_address'])){
						$mailAddress =  CommonFunctions::CheckMailingAddress($id);
						if($mailAddress > 0)
						{
							$contactsMailingAddress->updateAll(['status' =>_ID_STATUS_DELETED],['contact_id' => $id]);
						}
					}
					else
					{
						if(isset($mailing_address_arr) && !empty($mailing_address_arr)){
							if(isset($responseData['mailing_address_id']) && !empty($responseData['mailing_address_id']))
							{
								$mailingAddressDetails = $contactsMailingAddress->get($responseData['mailing_address_id']);
								$mailingAddress = $contactsMailingAddress->patchEntity($mailingAddressDetails, $mailing_address_arr);
								$contactsMailingAddress->save($mailingAddress);
							}else{
                                $getmailingAddress = $contactsMailingAddress->getMailingAddressByContactId($id);
                                if(count($getmailingAddress) == 0){
                                    $mailing_address_arr['contact_id']=$id;
                                    $mailingAddressDetails = $contactsMailingAddress->newEntity();
                                    $mailingAddress = $contactsMailingAddress->patchEntity($mailingAddressDetails,$mailing_address_arr);
                                    $contactsMailingAddress = $contactsMailingAddress->save($mailingAddress);
                                }
							}
						}
					}
                    if(isset($objectData['additional_emails']) && !empty($objectData['additional_emails']))
                    {
                             $contact_email_value = $objectData['additional_emails'];
                             foreach ($contact_email_value as $key => $emailValue)
                             {
                                 $contactEmails=[];
                                 $checkAdditionalEmail=$ContactEmails->checkExistingAdditionalContactEmail($id,$emailValue['mail']);
                                 $checkMail = strtolower($emailValue['mail']);
                                 if(in_array($checkMail, $emailArr))
                                 {
                                    $checkRecordExist = _ID_FAILED;
					                $response =  json_encode(array('message_status' => _ID_FAILED,'message' => $checkMail.' already exist. Try with another email.'));
                                    return $response;
                                 }
                                 else
                                 {
                                     $emailArr[$key+1] = strtolower($emailValue['mail']);
                                 }
                                if(empty($checkAdditionalEmail))
                                {
                                    if(isset($emailValue['id']) && $emailValue['id'] != '')
                                    {
                                        $additionalEmails = $ContactEmails->get($emailValue['id']);
                                        $contactEmails['email'] = strtolower($emailValue['mail']);
                                        $contactEmails['email_type'] = $emailValue['mailType'];
                                        $contactEmailNumbers = $ContactEmails->patchEntity($additionalEmails,$contactEmails);
                                        $contactEmailNumbers = $ContactEmails->save($contactEmailNumbers);

                                    }
                                    else
                                    {
                                        $contactEmails['email'] = strtolower($emailValue['mail']);
                                        $contactEmails['email_type'] = $emailValue['mailType'];
                                        $contactEmails['contact_id'] = $id;
                                        $contactEmailNumbers = $ContactEmails->newEntity();
                                        $contactEmailNumbers = $ContactEmails->patchEntity($contactEmailNumbers,$contactEmails);
                                        $contactEmailNumbers = $ContactEmails->save($contactEmailNumbers);
                                    }
                                }
                                else
                                {
                                    $contactEmails['email'] = strtolower($emailValue['mail']);
                                    $contactEmails['email_type'] = $emailValue['mailType'];
                                    $contactEmailNumbers = $ContactEmails->patchEntity($checkAdditionalEmail,$contactEmails);
                                    $contactEmailNumbers = $ContactEmails->save($contactEmailNumbers);
                                }
                             }
                    }
                    if (isset($contact_logs_array) && !empty($contact_logs_array)) {
                        $contact_logs_array['contact_id'] = $contact->id;
                        $contact_logs_array['agency_id'] = $contact->agency_id;
                        $contact_logs_array['user_id'] = $contact->user_id;
                        $contact_logs_array['platform'] = _PLATFORM_TYPE_SYSTEM;
						//$contact_logs_array['message'] = 'Contact updated by Contacts.updateContact';
                        CommonFunctions::insertContactLogsOnUpdate($contact_logs_array);
                    }
                    $response = json_encode(array('message_status' => _ID_SUCCESS,'message' => 'Saved Successfully'));
				}
			}
        }

        //save multiple phone numbers
        if(isset($objectData['other_phone_numbers']) && !empty($objectData['other_phone_numbers']))
        {
            foreach ($objectData['other_phone_numbers'] as $key => $value)
            {
                if (!empty($value["phoneNumber"])) {

                    $patterns = array('/\-/', '/\(/', '/\)/', '/\+/', '/\ /');
                    $value["phoneNumber"] = preg_replace($patterns, '', $value["phoneNumber"]);
                    if(in_array($value["phoneNumber"], $phoneArr))
                    {
                        $checkRecordExist = _ID_FAILED;
						$response = json_encode(array('message_status' => _ID_FAILED,'message' => $value["phoneNumber"].' already exist. Try with another phone.'));
						return $response;
                    }
                    else
                    {
                        $phoneArr[$key+1] = $value["phoneNumber"];
                    }
                    $contactPhones = [];
                    $checkPhoneExist = $ContactPhoneNumbers->checkPhoneNumberExist($id, $value["phoneNumber"]);
                    if(empty($checkPhoneExist))
                    {
                        if(isset($value['id']) && $value['id'] != '')
                        {
                            $contactPhoneNumbers = $ContactPhoneNumbers->get($value['id']);
                            $additional_phone_ext = '';
                            if (!empty($value["ext"])) {
                                $additional_phone_ext = $value["ext"];
                            }
                            $contactPhones['phone_number_type'] = $value["numberType"];
                            $contactPhones['phone_number_value'] = $value["phoneNumber"];
                            $contactPhones['contact_id'] = $objectData['id'];
                            $contactPhones['phone_ext'] = $additional_phone_ext;
                            $contactPhoneNumbers = $ContactPhoneNumbers->patchEntity($contactPhoneNumbers, $contactPhones);
                            $contactPhoneNumbers = $ContactPhoneNumbers->save($contactPhoneNumbers);
                        }
                        else
                        {
                            $additional_phone_ext = '';
                            if (!empty($value["ext"])) {
                                $additional_phone_ext = $value["ext"];
                            }
                            $contactPhones['phone_number_type'] = $value["numberType"];
                            $contactPhones['phone_number_value'] = $value["phoneNumber"];
                            $contactPhones['contact_id'] = $objectData['id'];
                            $contactPhones['phone_ext'] = $additional_phone_ext;
                            $contactPhoneNumbers = $ContactPhoneNumbers->newEntity();
                            $contactPhoneNumbers = $ContactPhoneNumbers->patchEntity($contactPhoneNumbers, $contactPhones);
                            $contactPhoneNumbers = $ContactPhoneNumbers->save($contactPhoneNumbers);
                        }
                    }
                    else
                    {
                        if($checkPhoneExist['contact_id'] == $id)
                        {
                            $additional_phone_ext = '';
                            if (!empty($value["ext"])) {
                                $additional_phone_ext = $value["ext"];
                            }
                            $contactPhones['phone_number_type'] = $value["numberType"];
                            $contactPhones['phone_number_value'] = $value["phoneNumber"];
                            $contactPhones['phone_ext'] = $additional_phone_ext;
                            $contactPhoneNumbers = $ContactPhoneNumbers->patchEntity($checkPhoneExist, $contactPhones);
                            $contactPhoneNumbers = $ContactPhoneNumbers->save($contactPhoneNumbers);
                        }
                    }

                    // additional contact phone opt in out
                    if (isset($contactPhones['phone_number_value']) && !empty($contactPhones['phone_number_value'])) {
                        $optoutData = [];
                        $optoutData['agency_id'] = $login_agency_id;
                        $optoutData['user_id'] = $login_user_id;
                        $optoutData['phone_number'] = $contactPhones['phone_number_value'];
                        $optoutData['contact_id'] = $id;
                        $optoutData['platform'] = _PLATFORM_TYPE_SYSTEM;

                        if (isset($value['smsPermission']) && $value['smsPermission'] == _ID_STATUS_ACTIVE) {
                            $optoutData['status'] = _ID_SUCCESS;
                        } else {
                            $optoutData['status'] = _ID_FAILED;
                        }
                        CommonFunctions::saveSmsOptInOutStatus($optoutData);
                    }
                }
            }
        }
        //end
        //nowcerts update contacts details
            NowCertsApi::updateIntoNowcerts($contact->id, null, null, null);
		return $response;

        }catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: Contact update Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }

    }

    /**
     * To check the entered email or phone is associated with another contact or not.
     * @param $objectData
     */
    public function checkExistEmailAndPhone($objectData){

        $session = Router::getRequest()->getSession();
		$login_user_id= $session->read("Auth.User.user_id");
        $login_agency_id= $session->read("Auth.User.agency_id");
        $contacts = TableRegistry::getTableLocator()->get('Contacts');
        $id = $objectData['id'];
         $isExistPhone = _ID_FAILED;
         $isExistEmail = _ID_FAILED;
        if(isset($objectData['email']) && !empty($objectData['email']))
        {
            $checkEmail= $contacts->checkExistingContactEmailExist($objectData['email'],$login_agency_id,$id);

            if(!empty($checkEmail))
            {
                $isExistEmail = _ID_SUCCESS;
            }
        }
        if(isset($objectData['phone']) && !empty($objectData['phone'])) {
            $patterns = array('/\-/', '/\(/', '/\)/', '/\+/', '/\ /');
            $data['phone'] = preg_replace($patterns, '', $objectData['phone']);
            $phone_validate = CommonFunctions::validate_mobile($data['phone']);
            if ($phone_validate == _ID_FAILED) {
                return json_encode(array('message_status' => _ID_FAILED, 'message' => 'Please enter valid phone number.'));
            } else {
                $checkPhone = $contacts->checkExistingContactPhoneExist($data['phone'], $login_agency_id, $id);
                if (!empty($checkPhone)) {
                    $isExistPhone = _ID_SUCCESS;
                }
            }
        }
        if($isExistPhone == _ID_SUCCESS && $isExistEmail == _ID_SUCCESS)
        {
            $status = _ID_FAILED; // Code 409 For Duplicate Record
            $message = 'There is already one contact with same email/phone';
        }
        elseif($isExistPhone == _ID_FAILED && $isExistEmail == _ID_SUCCESS)
        {
            $status = _ID_FAILED;
            $message = 'There is already one contact with same email';
        }
        elseif($isExistPhone == _ID_SUCCESS && $isExistEmail == _ID_FAILED)
        {
             $status = _ID_FAILED;
             $message = 'There is already one contact with same phone';
        }
        else{
            $status = _ID_SUCCESS;
            $message = 'No Record Found';
        }
         $response = json_encode(array('message_status' => $status, 'message' => $message));
         return $response;
    }

	public function savePersonalDetials($objectData)
    {
		try
        {
            $myfile = fopen(ROOT."/logs/contactrail.log", "a") or die("Unable to open file!");
            $session = Router::getRequest()->getSession();
            $login_user_id= $session->read("Auth.User.user_id");
            $login_agency_id= $session->read("Auth.User.agency_id");
            $contacts = TableRegistry::getTableLocator()->get('Contacts');
            $contactDetails = TableRegistry::getTableLocator()->get('ContactDetails');
			$ContactCustomFields = TableRegistry::getTableLocator()->get('ContactCustomFields');
			$ReferralPartnerUserContacts = TableRegistry::getTableLocator()->get('ReferralPartnerUserContacts');
            if(isset($objectData) && !empty($objectData)){
                $id = $objectData['id'];
                $contact = $contacts->get($id, [
                    'contain' => []
                ]);

                $data['birth_date'] = $objectData['birth_date'];
                $data['marital_status'] = $objectData['marital_status'];
                $data['occupation'] = $objectData['occupation'];
                $data['owns_rent'] = $objectData['own_rent'];
                $data['expiration_date'] = $objectData['expiration_date'];
                $data['lead_source_type'] = $objectData['lead_source_type'];
                $data['user_id'] = $objectData['user_id'];
                $contact_details['driver_license_number'] = $objectData['driver_license_number'];
                $contact_details['license_state_id'] = $objectData['license_state_id'];
                $contact_details['social_security_number'] = $objectData['social_security_number'];

                $contact = $contacts->patchEntity($contact, $data);
                if($contacts->save($contact)){
                    if(isset($contact_details) && !empty($contact_details)){
                        if(isset($objectData['contact_detail_id']) && !empty($objectData['contact_detail_id']))
                        {
                            $contactDetail = $contactDetails->get($objectData['contact_detail_id']);
                            $contactLicenceDetails = $contactDetails->patchEntity($contactDetail, $contact_details);
                            $contactDetails->save($contactLicenceDetails);

                        }else{
                            $contact_details['contact_id']=$id;
                            $contactDetail = $contactDetails->newEntity();
                            $contactLicenceDetails = $contactDetails->patchEntity($contactDetail,$contact_details);
                            $contactDetails->save($contactLicenceDetails);

                        }
                    }
					if(isset($objectData['custom_fields']) && !empty($objectData['custom_fields']))
					{

						$response = CommonFunctions::saveContactCustomFields($objectData);
					}

					if(isset($objectData['custom_update_fields']) && !empty($objectData['custom_update_fields']))
					{
						Contacts::updateCustomFields($objectData['custom_update_fields']);

					}
					$LeadSourceTable = TableRegistry::getTableLocator()->get('LeadSource');
        			$lead_source_name = $LeadSourceTable->leadNameByLeadId($contact['lead_source_type'],$login_agency_id);
					$ReferralPartnerUserContact = $ReferralPartnerUserContacts->getReferralPartnerUserContactByContactId($id);
					if(isset($lead_source_name) && !empty($lead_source_name && $lead_source_name['name'] != 'Referral Partner')){												
						if(!empty($ReferralPartnerUserContact) && count($ReferralPartnerUserContact)>0)
						{
							$ReferralPartnerUserContacts->updateAll(['status'=>_ID_STATUS_DELETED],['contact_id'=> $contact['id'],'status'=>_ID_STATUS_ACTIVE]);
						}
						
					}
					if(isset($objectData['referal_partner_select']) && !empty($objectData['referal_partner_select']))
					{
						
						$referal_partner_ids = $objectData['referal_partner_select'];
						$referralIdsArr = explode(",",$referal_partner_ids);
						if(count($referralIdsArr) == 2){
							$referral_partner_id = $referralIdsArr[0];
							$referal_partner_user_id = $referralIdsArr[1];
							$referralNameId =  $ReferralPartnerUserContact['id'];
							$referralDataArr['agency_id'] = $contact['agency_id'];
							$referralDataArr['user_id'] = $contact['user_id'];
							$referralDataArr['referral_partner_id'] = $referral_partner_id;
							$referralDataArr['referral_partner_user_id'] = $referal_partner_user_id;
							$referralDataArr['contact_id'] = $contact['id'];								
						}
						if(!empty($ReferralPartnerUserContact) && count($ReferralPartnerUserContact)>0)
						{
							$ReferralPartnerDetails = $ReferralPartnerUserContacts->updateAll($referralDataArr,['id'=>$referralNameId]);
						}else{
							$ReferralPartnerDetails = $ReferralPartnerUserContacts->newEntity();
							$ReferralPartnerNameDetails=$ReferralPartnerUserContacts->patchEntity($ReferralPartnerDetails,$referralDataArr);
							$ReferralPartnerArrDetails = $ReferralPartnerUserContacts->save($ReferralPartnerNameDetails);
						}
					}
                    //nowcerts update contacts details
                    NowCertsApi::updateIntoNowcerts($contact->id, null, null, null);
                    return $contacts;
                }
            }
        }catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: Contact update Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }

    }

	public function getSecondaryContacts($contactId)
    {
        try
        {
            $myfile = fopen(ROOT."/logs/contactrail.log", "a") or die("Unable to open file!");
            $session = Router::getRequest()->getSession();
            $login_user_id= $session->read("Auth.User.user_id");
            $login_agency_id= $session->read("Auth.User.agency_id");
            $contacts = TableRegistry::getTableLocator()->get('Contacts');
            $secondaryContacts = TableRegistry::getTableLocator()->get('ContactAdditionalInsuredPolicyRelation');
            // $personalContacts = $secondaryContacts->getinsuredByContactId($contactId);
            $contactData = $contacts->get($contactId);
            $personalContacts = $secondaryContacts->getLinkedPrimaryContacts($contactId, _ID_STATUS_ACTIVE);
            $personalContactsSec = $secondaryContacts->getLinkedOtherPrimaryContacts($contactId, _ID_STATUS_ACTIVE);
            if(empty($personalContacts))
            {
                $personalContacts = $personalContactsSec;
            }
            else if(!empty($personalContactsSec) && !empty($personalContacts))
            {
                
              $personalContacts = array_merge($personalContacts, $personalContactsSec);   
            }
            if($contactData->additional_insured_flag == 1)
            {
                $personalContacts = $secondaryContacts->getLinkedPrimaryContactsToSecondary($contactId, _ID_STATUS_ACTIVE);
            }
            $secondaryContactArr = [];
            $personalContactIds = [];
                $primaryContacts = $secondaryContacts->getLinkedSecondaryContacts($contactId, _ID_STATUS_ACTIVE);
                $primaryContactsSec = $secondaryContacts->getLinkedOtherSecondaryContacts($contactId, _ID_STATUS_ACTIVE);
                if(empty($primaryContacts))
                {
                    $primaryContacts = $primaryContactsSec;
                }
                else if(!empty($primaryContactsSec) && !empty($primaryContacts))
                {
                   
                   $primaryContacts = array_merge($primaryContacts, $primaryContactsSec);
                }
                foreach ($primaryContacts as $secondary){
                if($secondary['status'] == _ID_SUCCESS)
                {
                    $isSearchVal = 0;
                    $searchVal = array_search($secondary['contact']['id'], $personalContactIds);
                    if($searchVal !== false) {
                        $isSearchVal = 1;
                    }
                    array_push($personalContactIds, $secondary['contact']['id']);
                    if($isSearchVal == 0){
                        $secondary['contact']['name'] = (isset($secondary['contact']['first_name']) ? ucfirst($secondary['contact']['first_name']) : '').' '.(isset($secondary['contact']['last_name']) ? ucfirst($secondary['contact']['last_name']) : '');
                        $secondaryContactArr[] = $secondary;
                    }
                }
    
                }
            foreach ($personalContacts as $secondary){
                if($secondary['status'] == _ID_SUCCESS)
                {
                    $isSearchVal = 0;
                    $searchVal = array_search($secondary['contact']['id'], $personalContactIds);
                    if($searchVal !== false) {
                        $isSearchVal = 1;
                    }
                    array_push($personalContactIds, $secondary['contact']['id']);
                    if($isSearchVal == 0){
                        $secondary['contact']['name'] = (isset($secondary['contact']['first_name']) ? ucfirst($secondary['contact']['first_name']) : '').' '.(isset($secondary['contact']['last_name']) ? ucfirst($secondary['contact']['last_name']) : '');
                        $secondaryContactArr[] = $secondary;
                    }
                }

            }
            $return['Contacts.getSecondaryContacts'] = [
                $contactId => $secondaryContactArr
            ];
            return $return;

        }catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: Secondary Contact Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
    }

    public function getSecondaryContactsPolicy($objectData)
    {
        try
        {   
            $session = Router::getRequest()->getSession();
            $login_user_id= $session->read("Auth.User.user_id");
            $login_agency_id= $session->read("Auth.User.agency_id");
            $contacts = TableRegistry::getTableLocator()->get('Contacts');
            $secondaryContacts = TableRegistry::getTableLocator()->get('ContactAdditionalInsuredPolicyRelation');
            // $personalContacts = $secondaryContacts->getinsuredByContactId($contactId);
            $contactId = $objectData['contact_id'];
            $contactData = $contacts->get($contactId);
            $personalContacts = $secondaryContacts->getLinkedPrimaryContacts($contactId);
            $personalContactsSec = $secondaryContacts->getLinkedOtherPrimaryContacts($contactId);
            if(empty($personalContacts))
            {
                $personalContacts = $personalContactsSec;
            }
            else if(!empty($personalContacts) && !empty($personalContactsSec))
            {
                $personalContacts = array_merge($personalContacts, $personalContactsSec);
            }
            if($contactData->additional_insured_flag == _ID_SUCCESS)
            {
                $personalContacts = $secondaryContacts->getLinkedPrimaryContactsToSecondary($contactId);
            }
            $secondaryContactArr = [];
            $personalContactIds = [];
                $primaryContacts = $secondaryContacts->getLinkedSecondaryContacts($contactId);
                $primaryContactsSec = $secondaryContacts->getLinkedOtherSecondaryContacts($contactId);
                if(empty($primaryContacts))
                {
                    $primaryContacts = $primaryContactsSec;
                }
                else if(!empty($primaryContactsSec) && !empty($primaryContacts))
                {
                    $primaryContacts = array_merge($primaryContacts, $primaryContactsSec);
                }
                $secondaryArray = [];
                $secondaryArray['additionalInsuredFlag'] = "";
                foreach ($primaryContacts as $secondary){
                  if(($secondary['contact_opportunity_id'] != null && $secondary['status'] != _ID_STATUS_INACTIVE)|| $secondary['status'] == _ID_SUCCESS)
                  {
                    if($secondary['additional_insured_contact_id'] == $contactId)
                    {
                        $secondaryArray['additional_insured_contact_id'] = $secondary['contact_id'];
                    }
                    else
                    {
                        $secondaryArray['additional_insured_contact_id'] = $secondary['additional_insured_contact_id'];
                    }
                    if(!empty($secondary['contact_opportunity_id']))
                    {
                        $secondaryArray['contact_opportunity_id'] = $secondary['contact_opportunity_id'];
                    }
                    if($secondary['contact']['additional_insured_flag'] == _ID_STATUS_ACTIVE){
                        $secondaryArray['additionalInsuredFlag'] = _ID_STATUS_ACTIVE;
                    }
                    if(!empty($secondary['status']))
                    {
                        $secondaryArray['status'] = $secondary['status'];
                    }
                    $secondaryArray['name'] = (isset($secondary['contact']['first_name']) ? ucfirst($secondary['contact']['first_name']) : '').' '.(isset($secondary['contact']['last_name']) ? ucfirst($secondary['contact']['last_name']) : '');
                    $secondaryArray['name'] = trim($secondaryArray['name']);
                    $secondaryContactArr[] = $secondaryArray;
                  }
                }
            foreach ($personalContacts as $secondary){
                $secondaryArray['additionalInsuredFlag'] = "";
                if(($secondary['contact_opportunity_id'] != null && $secondary['status'] != _ID_STATUS_INACTIVE)|| $secondary['status'] == _ID_SUCCESS)
                {
                    if($secondary['additional_insured_contact_id'] == $contactId)
                    {
                        $secondaryArray['additional_insured_contact_id'] = $secondary['contact_id'];
                    }
                    else
                    {
                        $secondaryArray['additional_insured_contact_id'] = $secondary['additional_insured_contact_id'];
                    }
                    if(!empty($secondary['contact_opportunity_id']))
                    {
                        $secondaryArray['contact_opportunity_id'] = $secondary['contact_opportunity_id'];
                    }
                    if(!empty($secondary['status']))
                    {
                        $secondaryArray['status'] = $secondary['status'];
                    }
                    $secondaryArray['name'] = (isset($secondary['contact']['first_name']) ? ucfirst($secondary['contact']['first_name']) : '').' '.(isset($secondary['contact']['last_name']) ? ucfirst($secondary['contact']['last_name']) : '');
                    $secondaryArray['name'] = trim($secondaryArray['name']);
                    $secondaryContactArr[] = $secondaryArray;
                }
            }
            $return['Contacts.getSecondaryContactsPolicy'] = [
                $contactId => $secondaryContactArr
            ];
            return $return;

        }catch (\Exception $e) {
            $txt = date('Y-m-d H:i:s').' :: Secondary Contact Error- '.$e->getMessage();
            FileLog::writeLog("SecondaryContactErrorLog", $txt);
        }
    }
    public function getCommercialLinkContacts($contactId)
    {
        try
        {
            $myfile = fopen(ROOT."/logs/contactrail.log", "a") or die("Unable to open file!");

            $businessContact = TableRegistry::getTableLocator()->get('BusinessLinkedContact');
            $commercialContact = $businessContact->getBusinessLinkedContactByContactId($contactId);
            $return['Contacts.getCommercialLinkContacts'] = [
                $contactId => $commercialContact
            ];
            return $return;
        }catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: Commercial link Contact Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
    }


    public function getSecodaryContact($objectData)
    {
        try
        {
            $myfile = fopen(ROOT."/logs/contactrail.log", "a") or die("Unable to open file!");
			$session = Router::getRequest()->getSession();
			$login_user_id= $session->read("Auth.User.user_id");
			$login_agency_id= $session->read("Auth.User.agency_id");
			$data = [];
			if($objectData['type'] == 1){
				$Contacts = TableRegistry::getTableLocator()->get('Contacts');
				$secondaryContacts = TableRegistry::getTableLocator()->get('ContactAdditionalInsuredPolicyRelation');
                $checkAdditionalFlage = $Contacts->get($objectData['id'], [
                    'fields' => ['additional_insured_flag']
                ]);
                $objectData['additionalInsuredFlag'] = null;
                if($checkAdditionalFlage->additional_insured_flag != null && $checkAdditionalFlage->additional_insured_flag == _ID_STATUS_ACTIVE)
                {
                    $objectData['additionalInsuredFlag'] = _ID_STATUS_ACTIVE;
                }
				$contactId = $objectData['id'];
			    // $getAdditionalInsureds = $secondaryContacts->find()->where(['contact_id' => $objectData['id'],'status' => _ID_STATUS_ACTIVE])->toArray();
                $getAdditionalInsureds = $secondaryContacts->getLinkedPrimaryContacts($objectData['id'], _ID_STATUS_ACTIVE);
				$additionalInsuredIds="";
				if(!empty($getAdditionalInsureds)){
					$additionalInsuredIds = array_column($getAdditionalInsureds,'additional_insured_contact_id');
					$objectData['additionalInsuredIds'] = $additionalInsuredIds;
				}

				$contacts_list = $Contacts->getSecodaryContact($objectData,$login_agency_id);
				// echo "<pre>";
				// print_r($contacts_list);die;
				$i = 0;
				foreach($contacts_list as $contact){
                    if($contact['id'] != $objectData['id']) {
                        $name = '';
                        if (!empty($contact['first_name'])) {
                            $name = ucfirst($contact['first_name']);
                        }
                        if (!empty($contact['middle_name'])) {
                            $name = ucfirst($contact['first_name']) . " " . ucfirst($contact['middle_name']);
                        }
                        if (!empty($contact['last_name'])) {
                            $name = ucfirst($contact['first_name']) . " " . ucfirst($contact['last_name']);
                        }
                        if (!empty($contact['middle_name']) && !empty($contact['last_name'])) {
                            $name = ucfirst($contact['first_name']) . " " . ucfirst($contact['middle_name']) . " " . ucfirst($contact['last_name']);
                        } else {
                            $name = ucfirst($contact['first_name']) . " " . ucfirst($contact['middle_name']) . " " . ucfirst($contact['last_name']);
                        }
                        $name = trim($name);
                        if (!empty($name)) {
                            $data[$i]['id'] = $contact['id'];
                            $data[$i]['name'] = $name;
                            $i++;
                        }
                    }
				}
			}
			elseif($objectData['type'] == 2){
				$contactBusiness = TableRegistry::getTableLocator()->get('ContactBusiness');
				$contactId = $objectData['id'];
                $business_name = trim($objectData['name']);
                $getAllBusiness = $contactBusiness->checkBusinessByBusinessName($business_name,$login_agency_id);

                if(isset($getAllBusiness) && !empty($getAllBusiness))
                {
					$i = 0;
					foreach($getAllBusiness as $business){
						$name = '';
						if(!empty($business['name'])){
							$name = ucfirst($business['name']);
						} if(!empty($business['dba'])){
							$name = ucfirst($business['name'])." AKA ".ucfirst($business['dba']);
						}
						$name = trim($name);
						if(!empty($name))
						{
							$data[$i]['id'] = $business['id'];
							$data[$i]['name'] = $name;
							$i++;
						}

					}
                }

			}
            $return['Contacts.getSecodaryContact'] = [
                $contactId => $data
            ];
            return $return;			
		}catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: Commercial link Contact Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }


    }


	public function saveSecodaryContact($objectData){
		try
        {
            $myfile = fopen(ROOT."/logs/contactrail.log", "a") or die("Unable to open file!");
			$session = Router::getRequest()->getSession();
            $login_user_id= $session->read("Auth.User.user_id");
            $login_agency_id= $session->read("Auth.User.agency_id");
            $contacts = TableRegistry::getTableLocator()->get('Contacts');
            // $contactDetails = TableRegistry::getTableLocator()->get('ContactDetails');
			$secondaryContacts = TableRegistry::getTableLocator()->get('ContactAdditionalInsuredPolicyRelation');
            if(isset($objectData) && !empty($objectData)){
                $id = $objectData['id'];
                $contactId = '';
				$secondaryContact = $secondaryContacts->find()->where(['contact_id'=>$id, 'additional_insured_contact_id'=>$objectData['secondaryContactId'], 'status !=' => _ID_STATUS_INACTIVE])->hydrate(false)->first();
                $contactDetails = $contacts->get($objectData['secondaryContactId']);
                $contactId = $objectData['secondaryContactId'];
                if(empty($secondaryContact))
                {
                    $secondaryContact = $secondaryContacts->find()->where(['contact_id' => $objectData['secondaryContactId'], 'additional_insured_contact_id'=>$id, 'status !=' => _ID_STATUS_INACTIVE])->hydrate(false)->first();
                    $contactDetails = $contacts->get($id);
                    $contactId = $id;
                }
				if(isset($secondaryContact) && !empty($secondaryContact) && $secondaryContact['status'] == _ID_SUCCESS)
				{
					$response = json_encode(array('status' => _ID_FAILED, 'message' => 'This contact is already linked.'));
				}
                else if(!empty($secondaryContact) && $secondaryContact['status'] == _ID_STATUS_DELETED){
                    if($contactDetails->status == _ID_STATUS_INACTIVE)
                    {
                        $updateContactLeadType = $contacts->updateAll(['status' => _ID_STATUS_INACTIVE], ['id' => $contactId]);
                    }
                    else
                    {
                        $updateContactLeadType = $contacts->updateAll(['status' => _ID_STATUS_ACTIVE, 'lead_type' => $contactDetails->lead_type], ['id' => $contactId]);
                    }
                    $secondaryContactData = $secondaryContacts->get($secondaryContact['id']);
                    $secondaryContactValue['relationship_with_contact'] = $objectData['relationwithcontact'];
                    $secondaryContactValue['insured_contact_status'] = $objectData['secondarystatus'];
                    $secondaryContactValue['status'] = _ID_STATUS_ACTIVE;
                    $secondaryContact = $secondaryContacts->patchEntity($secondaryContactData, $secondaryContactValue);
                    $updateSecondaryContact = $secondaryContacts->save($secondaryContact);
                    if($updateSecondaryContact)
                    {   $secondaryContacts->updateAll(['status' => _ID_STATUS_ACTIVE], ['contact_id' => $secondaryContactData->contact_id, 'additional_insured_contact_id' => $secondaryContactData->additional_insured_contact_id, 'status' => _ID_STATUS_DELETED]);
                        $secondaryContactSec = $secondaryContacts->find()->where(['contact_id' => $objectData['secondaryContactId'], 'additional_insured_contact_id'=>$id])->hydrate(false)->first();
                        if(!empty($secondaryContactSec))
                        {
                            $secondaryContactData = $secondaryContacts->get($secondaryContactSec['id']);
                            $secondaryContactValue['relationship_with_contact'] = $objectData['relationwithcontact'];
                            $secondaryContactValue['insured_contact_status'] = $objectData['secondarystatus'];
                            $secondaryContactValue['status'] = _ID_STATUS_ACTIVE;
                            $secondaryContact = $secondaryContacts->patchEntity($secondaryContactData, $secondaryContactValue);
                            $updateSecondaryContact = $secondaryContacts->save($secondaryContact);
                            $secondaryContacts->updateAll(['status' => _ID_STATUS_ACTIVE], ['contact_id' => $secondaryContactData->contact_id, 'additional_insured_contact_id' => $secondaryContactData->additional_insured_contact_id, 'status' => _ID_STATUS_DELETED]);
                        }
                        $response = json_encode(array('status' => _ID_SUCCESS,'message'=>'Record saved successfully'));
                    }
                }
                else
                {
					$secondary_data['contact_id'] = $id;
					$secondary_data['additional_insured_contact_id'] = $objectData['secondaryContactId'];
					$secondary_data['relationship_with_contact'] = $objectData['relationwithcontact'];
					$secondary_data['insured_contact_status'] = $objectData['secondarystatus'];
					$secondaryContact = $secondaryContacts->newEntity();
					$secondary_contact = $secondaryContacts->patchEntity($secondaryContact,$secondary_data);                      
					if($secondary_contact = $secondaryContacts->save($secondary_contact))
                    {
                        $prospectContact = $contacts->get($id, [
                        'contain' => []
                        ]);
                        $linkedContacts = $contacts->get($secondary_contact->additional_insured_contact_id);
                        if($linkedContacts->lead_type == _CONTACT_TYPE_CLIENT)
                        {
                          $result = $contacts->updateAll(['lead_type' => _CONTACT_TYPE_CLIENT], ['id' => $id]);
                        }
                        if($prospectContact['lead_type'] == _CONTACT_TYPE_CLIENT)
                        {
                            $contacts->updateAll(['lead_type' => _CONTACT_TYPE_CLIENT], ['id' => $secondary_contact->additional_insured_contact_id]);
                        }
						$response = json_encode(array('status' => _ID_SUCCESS,'message'=>'Record saved successfully'));
					}
				}

				return $response;
            }
		}catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: Secondary contact Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
	}


    public function getCommercialContact($objectData)
    {
        try
        {
            $myfile = fopen(ROOT."/logs/contactrail.log", "a") or die("Unable to open file!");
			$session = Router::getRequest()->getSession();
			$login_user_id = $session->read("Auth.User.user_id");
			$login_agency_id = $session->read("Auth.User.agency_id");
			$contactBusiness = TableRegistry::getTableLocator()->get('ContactBusiness');

            if(isset($objectData) && !empty($objectData)){
                $contactId = $objectData['id'];
                $business_name = trim($objectData['name']);
                $getAllBusiness = $contactBusiness->checkBusinessByBusinessName($business_name,$login_agency_id);

                if(isset($getAllBusiness) && !empty($getAllBusiness))
                {
                    $response = json_encode(array('status' => _ID_SUCCESS,'business_id'=>$getAllBusiness['id']));
                }else{
                    $response = json_encode(array('status' => _ID_FAILED));
                }

            }
			return $response;

		}catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: Commercial link Contact Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
    }


    public function saveCommercialContact($objectData)
    {
        try
        {
            $myfile = fopen(ROOT."/logs/contactrail.log", "a") or die("Unable to open file!");
			$session = Router::getRequest()->getSession();
			$login_user_id = $session->read("Auth.User.user_id");
			$login_agency_id = $session->read("Auth.User.agency_id");
            $businessLinkedContact = TableRegistry::getTableLocator()->get('BusinessLinkedContact');
            $contactBusniness = TableRegistry::getTableLocator()->get('ContactBusiness');
            $contacts = TableRegistry::getTableLocator()->get('Contacts');

            if(isset($objectData['business_id']) && !empty($objectData['business_id']) && isset($objectData['contact_id']) && !empty($objectData['contact_id']))
            {
                //check for already enty in link table
                $check_link_entry = $businessLinkedContact->checkBusinessContactLink($objectData['business_id'],$objectData['contact_id']);

                if(empty($check_link_entry))
                {
					$link_data['contact_business_id'] = $objectData['business_id'];
					$link_data['contact_id'] = $objectData['contact_id'];
					// $link_data['business_role_type'] = $objectData['business_role_type'];
					$link_data['relationship_type'] = $objectData['business_role_type'];
                    $business_contact_link = $businessLinkedContact->newEntity();
                    $business_contact_link = $businessLinkedContact->patchEntity($business_contact_link,$link_data);
                    $business_contact_link = $businessLinkedContact->save($business_contact_link);
					if($business_contact_link)
                    {
                        $prospectContact = $contacts->get($objectData['contact_id'], [
                        'contain' => []
                        ]);

                        if($prospectContact['lead_type'] == _CONTACT_TYPE_CLIENT)
                        {
                            $contactBusniness->updateAll(['lead_type' => _CONTACT_TYPE_CLIENT],['id' => $business_contact_link->contact_business_id]);
                        }
						$response = json_encode(array('status' => _ID_SUCCESS));
					}else{
						$response = json_encode(array('status' => _ID_FAILED,'message'=>'Something went wrong.'));
					}
                }else{
					$response = json_encode(array('status' => _ID_FAILED,'message'=>'This company name already linked with this contact.'));
				}
				return $response;
            }
        }catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: Commercial link Contact Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
    }

	public function getBusinessStructure($contactId){
		try
        {
            $myfile = fopen(ROOT."/logs/contactrail.log", "a") or die("Unable to open file!");
			$session = Router::getRequest()->getSession();
			$login_user_id = $session->read("Auth.User.user_id");
			$login_agency_id = $session->read("Auth.User.agency_id");
            $businessStructureType = TableRegistry::getTableLocator()->get('BusinessStructureType');

			$businessSturctures = $businessStructureType->find()->select(['id','business_type'])->where(['status'=>_ID_STATUS_ACTIVE])->hydrate(false)->toArray();
			$return['Contacts.getBusinessStructure'] = [
                $contactId => $businessSturctures
            ];
            return $return;
		}catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: Commercial link Contact Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
	}

	public function saveMainContact($objectData)
	{
		try
        {
            $myfile = fopen(ROOT."/logs/contactrail.log", "a") or die("Unable to open file!");
			$session = Router::getRequest()->getSession();
			$login_user_id = $session->read("Auth.User.user_id");
			$login_agency_id = $session->read("Auth.User.agency_id");
			$contacts = TableRegistry::getTableLocator()->get('Contacts');
			$additionalInsuredContact = TableRegistry::getTableLocator()->get('ContactAdditionalInsuredPolicyRelation');
            $contact_logs_array = [];
			if(empty($objectData['first_name']) ||  empty($objectData['last_name']))
			{
				$response = json_encode(array('status' => _ID_FAILED));
			}
			else if(isset($objectData) && !empty($objectData))
			{

				$id = $objectData['id'];
                $contact = $contacts->get($id, [
                    'contain' => []
                ]);
				$contact_data['first_name'] = $objectData['first_name'];
				$contact_data['middle_name'] = $objectData['middle_name'];
				$contact_data['last_name'] = $objectData['last_name'];
				$contact_data['suffix'] = $objectData['suffix'];
				$contact_data['preferred_name'] = $objectData['preferred_name'];

                if(!empty($objectData['first_name']) && $objectData['first_name'] != '--')
                {                  
                    // first name logs
                    if(strtolower($objectData['first_name']) != strtolower($contact->first_name)){
                        $contact_logs_array['first_name'] = $contact->first_name;
                    }
                }
                elseif(empty($objectData['first_name']) && isset($objectData['first_name']))
                {
                    $contact_data['first_name'] = NULL;
                    // first name logs
                    if(strtolower($objectData['first_name']) != strtolower($contact->first_name))
                    {
                        $contact_logs_array['first_name'] = $contact->first_name;
                    }
                }


                if(!empty($objectData['last_name']) && $objectData['last_name'] != '--')
                {                  
                    // last name logs
                    if(strtolower($objectData['last_name']) != strtolower($contact->last_name)){
                        $contact_logs_array['last_name'] = $contact->last_name;
                    }
                }
                elseif(empty($objectData['last_name']) && isset($objectData['last_name']))
                {
                    $contact_data['last_name'] = NULL;
                    // first name logs
                    if(strtolower($objectData['last_name']) != strtolower($contact->last_name))
                    {
                        $contact_logs_array['last_name'] = $contact->last_name;
                    }
                }

				$contact = $contacts->patchEntity($contact, $contact_data);
                if($contacts->save($contact)) {
                    $secondaryContactUpdateArr = [];
                    if($objectData['insured_relation_id'])
                    {
                        $secondaryContactUpdateArr['relationship_with_contact'] = $objectData['insured_relation_id'];
                    }
                    if($objectData['insured_status_id'])
                    {
                        $secondaryContactUpdateArr['insured_contact_status'] = $objectData['insured_status_id'];
                    }
                    if(count($secondaryContactUpdateArr) > 0 && $objectData['primaryContactId'])
                    {
                        $additionalInsuredContact->updateAll($secondaryContactUpdateArr,['contact_id' => $objectData['primaryContactId'],'additional_insured_contact_id' => $objectData['id'],'status'=> _ID_STATUS_ACTIVE]);                     
                    }
                    if (isset($contact_logs_array) && !empty($contact_logs_array)) {
                        $contact_logs_array['contact_id'] = $contact->id;
                        $contact_logs_array['agency_id'] = $contact->agency_id;
                        $contact_logs_array['user_id'] = $contact->user_id;
                        $contact_logs_array['platform'] = _PLATFORM_TYPE_SYSTEM;
	                    $contact_logs_array['message'] = 'Contact updated by Contacts.saveMainContact';
                        CommonFunctions::insertContactLogsOnUpdate($contact_logs_array);
                    }
                    //nowcerts update contact
                    NowCertsApi::updateIntoNowcerts($contact->id, null, null, null);
                    $response = json_encode(array('status' => _ID_SUCCESS));
                }
            }
			return $response;

		}catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: Commercial link Contact Error:- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
	}



	/*
    *funciton to get list of all contact numbers
    */
    public function getContactsAllNumbers($contactId)
    {
        try
        {
			//$myfile = fopen(ROOT."/logs/vueCommunication.log", "a") or die("Unable to open file!");
            $Contacts = TableRegistry::getTableLocator()->get('Contacts');
			$ContactPhoneNumbers = TableRegistry::getTableLocator()->get('ContactPhoneNumbers');
			$ContactBusiness = TableRegistry::getTableLocator()->get('ContactBusiness');
			$send_sms_numbers = [];
			$current_timestamp = "";
			if(!empty($contactId))
			{
				$contact_id = $contactId;
				$getContactDetail = $Contacts->get($contact_id);
				if(isset($getContactDetail) && !empty($getContactDetail)){
					if(isset($getContactDetail['phone']) && !empty($getContactDetail['phone']))
					{
                        // isset($getContactDetail['phone_number_type']) && !empty($getContactDetail['phone_number_type'])  remove because some contacts do not have the phone number type #12793
						if($getContactDetail['phone_number_type'] != _ID_LANDLINE){
							$getEntityDefDefault=getEntityDef($err,_PHONE_NUMBER_TYPE,$getContactDetail['phone_number_type']);
							$phone_number_type_default = $getEntityDefDefault;
							$phoneNumberWithFormat = CommonFunctions::format_phone_us($getContactDetail['phone']);
							if(isset($getContactDetail['phone_ext']) && !empty($getContactDetail['phone_ext'])){
								$phoneNumberWithFormat = $phoneNumberWithFormat.", Ext. ".$getContactDetail['phone_ext'];
							}
							//$send_sms_numbers .= '<option value="'.$phoneNumberWithFormat.'">Personal > '.$phone_number_type_default.' - '.$phoneNumberWithFormat.'</option>';
							$send_sms_numbers[0]['id'] = $phoneNumberWithFormat;
							$send_sms_numbers[0]['name'] = $phone_number_type_default.' - '.$phoneNumberWithFormat;
						}
					}
				}
				$getAllPhoneNumbersArr = $ContactPhoneNumbers->getAllPhoneNumbers($contact_id);
				if(isset($getAllPhoneNumbersArr) && !empty($getAllPhoneNumbersArr)){

					if(!empty($send_sms_numbers))
					{
						$i = 1;
					}else
					{
						$i = 0;
					}
					foreach ($getAllPhoneNumbersArr as $phonenumbers) {
						if($phonenumbers['phone_number_type'] != _ID_LANDLINE){
							$getEntityDefAdditional=getEntityDef($err,_PHONE_NUMBER_TYPE,$phonenumbers['phone_number_type']);
							$phone_number_type_additional = $getEntityDefAdditional;
							$phone = CommonFunctions::format_phone_us($phonenumbers['phone_number_value']);
                            $send_sms_numbers[$i]['id'] = $phone;
                            if(isset($phonenumbers['phone_ext']) && !empty($phonenumbers['phone_ext'])){
                                $phone = $phone.", Ext. ".$phonenumbers['phone_ext'];
                            }
                            $send_sms_numbers[$i]['name'] = $phone_number_type_additional.' - '.$phone;
                            if(strtolower($phone_number_type_additional) == 'mobile')
                            {
                                $send_sms_numbers[$i]['type'] = 'Cell';
                            }
                            else
                            {
                                $send_sms_numbers[$i]['type'] =  $phone_number_type_additional;
                            }
						}
						$i++;
					}
				}
			}

			$contactBusiness = $ContactBusiness->getActiveBusinessByContactIdWithContact($contact_id);


			$current_timestamp = date('Y-m-d H:i:s');
			if(empty($send_sms_numbers)){
				$send_sms_numbers[0]['id'] = '';
				$send_sms_numbers[0]['name'] = 'Contact Requires Valid Mobile Number';
			}
			$return['Contacts.getContactsAllNumbers'] = [
                $contactId => $send_sms_numbers
            ];
            return $return;
        }catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: Communication Lising Error- '.$e->getMessage();
			//fwrite($myfile,$txt.PHP_EOL);
        }
    }

	public static function getAcordForms($contactId)
    {
        try
		{
			//$myfile = fopen(ROOT."/logs/vueAttachment.log", "a") or die("Unable to open file!");
			$session = Router::getRequest()->getSession();
			$login_user_id = $session->read("Auth.User.user_id");
            $login_user_email = $session->read("Auth.User.email");
			$login_role_type = $session->read('Auth.User.role_type');
			$login_role_type_flag = $session->read('Auth.User.role_type_flag');
			$login_permissions = $session->read('Auth.User.permissions');
			$login_permissions_arr = explode(",",$login_permissions);
			$where_cond = array();
			$contact_id = '';
            $ContactAcordForms = TableRegistry::getTableLocator()->get('ContactAcordForms');
            $IvansUserDetail = TableRegistry::getTableLocator()->get('IvansUserDetail');
            $IvansAgencyTokenDetail = TableRegistry::getTableLocator()->get('IvansAgencyTokenDetail');
            $AgencyTable = TableRegistry::getTableLocator()->get('Agency');
            $UsersTable = TableRegistry::getTableLocator()->get('Users');
			$Contacts = TableRegistry::getTableLocator()->get('Contacts');
            $login_agency_id= $session->read("Auth.User.agency_id");
			$contactId = $contactId;
			$agency = $AgencyTable->agencyDetails($login_agency_id);
            //Ivanscheck
            $checkIvansStatus = 0;
            $chkAgencyIvansAccess = $IvansUserDetail->checkIvansAccessToAgecny($login_agency_id, $login_user_id);
		    $chkAgencyAcordsAccess = $IvansAgencyTokenDetail->checkAgencyAcordAccess($login_agency_id);
            if(!empty($chkAgencyIvansAccess)) {
                if((isset($chkAgencyAcordsAccess['is_ivans_configured']) && $chkAgencyAcordsAccess['is_ivans_configured'] === _ID_STATUS_ACTIVE) || (isset($chkAgencyAcordsAccess['is_accord_on']) && $chkAgencyAcordsAccess['is_accord_on'] === _ID_STATUS_ACTIVE)){
                    $checkIvansStatus = 1;
                }
            }
            if(!empty($contactId)) {
                $contact_id = $contactId;
                $id = $contact_id;
				$contact = $Contacts->contactDetails($contact_id);
				$name = ucwords($contact['first_name']);
				$email = $contact['email'];
				$getAcordFormLists = $ContactAcordForms->find('All')->select(['id','user_id','policy_guid','form_id','created_by','created','acord_id','acord_form_title','acord_form_display_name','original_form_id'])->where(['contact_id'=>$contact_id,'business_id IS NULL','status'=>_ID_STATUS_ACTIVE])->order(['created' =>'DESC'])->hydrate(false)->toArray();
            }
            
            $count = 0;
            $finalResponse =[];
            if(isset($getAcordFormLists) && !empty($getAcordFormLists))
            {
                //$contact_email_attachment_list = $contact['contact_attachments'];
                foreach ($getAcordFormLists as $key => $value)
                {
					$username = '--';
					if(!empty($value['user_id'])){
						$getuser = $UsersTable->userDetails($value['user_id']);
						if(!empty($getuser))
						{
							$username =ucwords($getuser['first_name']);
							if(isset($getuser['last_name']) && !empty($getuser['last_name']))
							{
								$username = $username." ".ucwords($getuser['last_name']);
							}
						}
					}
					$created_date = '--';
					if(isset($value['created']) && !empty($value['created'])){
						$created_date = date('M d, Y', strtotime($value['created']));
					}
					if(isset($value["acord_form_display_name"]) && !empty($value["acord_form_display_name"]))
					{
						$accord_form_name  = $value["acord_form_display_name"];
					}
					elseif(isset($value["acord_id"]) && !empty($value["acord_id"]) && isset($value["acord_form_title"]) && !empty($value["acord_form_title"])){
						$accord_form_name  = $value["acord_id"] . " " . $value["acord_form_title"];
					}
					else{
						/* for previous form where accord title and accord number is not saved */
						$accord_form_name  = "ACORD form # " . $value["form_id"];
					}
                    $form_id = '';
					if(!empty($value["form_id"]))
					{
						$form_id = $value["form_id"];
					}
					else if(!empty($value["original_form_id"]))
					{
						$form_id = $value["original_form_id"];
					}
					$finalResponse[$count]['id'] = $value['id'];
					$finalResponse[$count]['file'] = $accord_form_name;
					$finalResponse[$count]['user'] = $username;
					$finalResponse[$count]['date_added'] = $created_date;
					$finalResponse[$count]['form_id'] = $value['form_id'];
					$finalResponse[$count]['policy_guid'] = $value['policy_guid'];
					$finalResponse[$count]['contact_email'] = base64_encode($email);
					$finalResponse[$count]['contact_firstname'] = base64_encode($name);
					$finalResponse[$count]['acord_formname_humanized'] = base64_encode($name);
					$finalResponse[$count]['agent_signature'] = base64_encode($agency['company']);

					$viewUrl = _VIEW_FORM.$value["policy_guid"].'-'.$form_id.'?contact_email_address='.base64_encode($email).'&contact_firstname='.base64_encode($name).'&acord_formname_humanized='.base64_encode($value["acord_form_title"]).'&agent_signature='.base64_encode($agency['company']).'&LoginUser='.base64_encode($login_user_email).'&encoded=true';
                    $finalResponse[$count]['view_url'] = $viewUrl;
                    $count++;
                }


            }
            $finalResponse['checkIvansStatus'] = $checkIvansStatus;
            $return['Contacts.getAcordForms'] = [
                $contactId => $finalResponse
            ];

            return $return;
        }catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: Attachment Lising Error- '.$e->getMessage();
			//fwrite($myfile,$txt.PHP_EOL);
        }

	}

	public function getAcordFormsList($contact_id)
    {
		try
		{
			$myfile = fopen(ROOT."/logs/vueAttachment.log", "a") or die("Unable to open file!");
			$session = Router::getRequest()->getSession();
			$login_user_id = $session->read("Auth.User.user_id");
			$login_role_type = $session->read('Auth.User.role_type');
			$login_role_type_flag = $session->read('Auth.User.role_type_flag');
			$login_permissions = $session->read('Auth.User.permissions');
			$login_permissions_arr = explode(",",$login_permissions);
            $ContactAcordForms = TableRegistry::getTableLocator()->get('ContactAcordForms');
            $AgencyTable = TableRegistry::getTableLocator()->get('Agency');
            $UsersTable = TableRegistry::getTableLocator()->get('Users');
			$Contacts = TableRegistry::getTableLocator()->get('Contacts');
			$IvansAgencyTokenDetail = TableRegistry::getTableLocator()->get('IvansAgencyTokenDetail');
            $login_agency_id= $session->read("Auth.User.agency_id");
			$acord_forms = [];
			$acord_form = '';
			// if(isset($objectData['acord']) && !empty($objectData['acord'])){
			// $acord_form =$objectData['acord'];
			// }
			// $type = '';
			// if(isset($objectData['type']) && !empty($objectData['type'])){
			// $type =$objectData['type'];
			// }
			$getIvansAgencyDetails = $IvansAgencyTokenDetail->find('All')->select(['id','agency_token'])->where(['agency_id' => $login_agency_id,'status'=>_ID_SUCCESS])->hydrate(false)->first();
            $agency_token = '';
			if(isset($getIvansAgencyDetails) && !empty($getIvansAgencyDetails)){
				$agency_token = $getIvansAgencyDetails['agency_token'];
			}
			//$agency_token = 'ae98412c-6574-4bf7-ac49-446c0dd631f6';
			$headers = array(
				'agency_token: '.$agency_token,
				);
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => _SUPPORTED_ACORD_FORM_API,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_HTTPHEADER => $headers,
			));

			$response = curl_exec($curl);
			$response = json_decode($response);
			curl_close($curl);
			if(!empty($response) && isset($response->data->form_unique_identifier)){
				// $acordFormList = $response->data->form_unique_identifier->acord_application_forms;
				$acordFormList = array_merge($response->data->form_unique_identifier->acord_certificates,$response->data->form_unique_identifier->acord_application_forms);

				// $matches = array_filter($acordFormList, function($var) use ($acord_form) {
				// return preg_match("/\b".$acord_form."\b/i", $var);
				// });
				// echo '<pre>';
				// print_r($matches);
				// die();
				//$acordCertificates = _ACORD_CERTIFACTES;
				if(!empty($acordFormList)){
					natsort($acordFormList);
					if(isset($acordFormList) && !empty($acordFormList)){
						$i = 0;
						foreach($acordFormList as $key => $acord_form){
							$acord_forms[$i]['id'] = $key;
							$acord_forms[$i]['name'] = str_replace('Acord', '', ucwords(strtolower($acord_form)));
							$acord_forms[$i]['acord_name'] = $acord_form;
							$i++;
						}
					}
				}
			}
            //print_r($acord_forms); exit;
			$return['Contacts.getAcordFormsList'] = [
				$contact_id => $acord_forms
			];
			return $return;
		}catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: Policies Lising Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
	}

	public function nextAcordForm($objectData)
    {
            $myfile = fopen(ROOT."/logs/beta_acord_json.log", "a") or die("Unable to open file!");
			$session = Router::getRequest()->getSession();
			$login_user_id = $session->read("Auth.User.user_id");
			$login_role_type = $session->read('Auth.User.role_type');
			$login_role_type_flag = $session->read('Auth.User.role_type_flag');
			$login_permissions = $session->read('Auth.User.permissions');
			$login_permissions_arr = explode(",",$login_permissions);
			$login_agency_id= $session->read("Auth.User.agency_id");
			$Contacts = TableRegistry::getTableLocator()->get('Contacts');
			$AgencyTable = TableRegistry::getTableLocator()->get('Agency');
			$contact_id = $objectData['contact_id'];
			$contact = $Contacts->contactDetails($contact_id);
			$name = ucwords($contact['first_name']);
			$email = $contact['email'];
			$agency = $AgencyTable->agencyDetails($login_agency_id);
            $ContactAcordForms = TableRegistry::getTableLocator()->get('ContactAcordForms');
			$IvansAgencyTokenDetail = TableRegistry::getTableLocator()->get('IvansAgencyTokenDetail');
			$IvansUserDetail = TableRegistry::getTableLocator()->get('IvansUserDetail');
			$Users = TableRegistry::getTableLocator()->get('Users');
			$ContactPolicyRenewal = TableRegistry::getTableLocator()->get('ContactPolicyRenewal');
			$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
			$Carriers = TableRegistry::getTableLocator()->get('Carriers');
			$UsStates = TableRegistry::getTableLocator()->get('UsStates');
            $IvansComponent = new IvansComponent(new ComponentRegistry(), []);
			$getIvansAgencyDetails = $IvansAgencyTokenDetail->find('All')->select(['id','agency_token'])->where(['agency_id' => $login_agency_id])->first();
			$agency_token = '';
			$create_type = '';
            $naic_code = '';
			if(isset($getIvansAgencyDetails) && !empty($getIvansAgencyDetails)){
				$agency_token = $getIvansAgencyDetails['agency_token'];
			}
			//$agency_token = 'cce2591e-3c7f-438a-97fc-41cb17a88330';
			$checkIvansUserExist = $IvansUserDetail->find('All')->select(['id','user_email','ivans_agency_id','ivans_agency_guid'])->where(['user_id' => $login_user_id,'agency_id'=>$login_agency_id,'ivan_status'=>_ID_SUCCESS])->first();
			$user_email = '';
			if(isset($checkIvansUserExist['user_email']) && !empty($checkIvansUserExist['user_email'])){
				$user_email = $checkIvansUserExist['user_email'];
			}
			$ivans_agency_id = '';
			if(isset($checkIvansUserExist['ivans_agency_id']) && !empty($checkIvansUserExist['ivans_agency_id'])){
				$ivans_agency_id = $checkIvansUserExist['ivans_agency_id'];
			}
			$ivans_agency_guid = '';
			if(isset($checkIvansUserExist['ivans_agency_guid']) && !empty($checkIvansUserExist['ivans_agency_guid'])){
				$ivans_agency_guid = $checkIvansUserExist['ivans_agency_guid'];
			}
			$userDetails = $Users->userDetails($login_user_id);
			$user_phone = '';
			$user_name = '';
			if(isset($userDetails) && !empty($userDetails))
			{
				$user_name = ucwords($userDetails['first_name']);
				if(isset($userDetails['last_name']) && !empty($userDetails['last_name']))
				{
					$user_name = $user_name.' '.ucwords($userDetails['last_name']);
				}
				if(isset($userDetails['phone']) && !empty($userDetails['phone']))
				{
					$user_phone = $userDetails['phone'];
				}
			}
			//$user_email = 'mailto:paramjot.singh@webners.com';
			if(!empty($agency_token) && !empty($user_email)){
				$query_string = '';
			if(isset($objectData['contact_id']) && !empty($objectData['contact_id'])){
				$contact_id = $objectData['contact_id'];
				$query_string .= '?contact_id='.$contact_id;
				$contact  = $Contacts->contactDetails($contact_id);
				//   echo"<pre>";print_r($contact); die;
			}

			$policy_details = array();
			if(!empty($objectData['acord_form'])){
				$acord_form = $objectData['acord_form'];
			}
           else if(!empty($objectData['form_id']['name'])){
                $acord_form = 'ACORD'.' '.trim($objectData['form_id']['name']);
             }

			$getAllRenewalSuccessEntry=[];

			if(isset($objectData['opportunity_id']) && !empty($objectData['opportunity_id'])){

				$opportunity_id = $objectData['opportunity_id'];
				$query_string .= '&opportunity_id='.$opportunity_id;
				// $chkRenewalEntry = $this->ContactPolicyRenewal->getContactPolicyRenewalByContactAndOpportunity($contact_id,$opportunity_id);
				$chkRenewalEntry = $ContactPolicyRenewal->getLatestRenewalSuccessByOppId($opportunity_id);

				if(count($chkRenewalEntry)>=2){
					$getAllRenewalSuccessEntry = $chkRenewalEntry[0];
				}else{
					//$opportunityDetail = $this->ContactOpportunities->get($opportunity_id);
					$opportunityDetail = $ContactOpportunities->getOpportunityByOppId($opportunity_id);
				}
				$effective_date = '';
				$expiration_date = '';
				$policy_number = "";
				if(!empty($getAllRenewalSuccessEntry) && !empty($getAllRenewalSuccessEntry['renewal_date']))
				{
					$effective_date=date('m/d/Y',strtotime($getAllRenewalSuccessEntry['renewal_date']));
					if(isset($getAllRenewalSuccessEntry['term_length']) && !empty($getAllRenewalSuccessEntry['term_length'])){
						$term_length = $getAllRenewalSuccessEntry['term_length'];
						$expiration_date = date('m/d/Y',strtotime("+ ".$term_length." months",strtotime($getAllRenewalSuccessEntry['effective_date'])));
					}
				}
				else if(isset($opportunityDetail['effective_date']) && !empty($opportunityDetail['effective_date']))
				{
					$effective_date =   date('m/d/Y', strtotime($opportunityDetail['effective_date']));
					if(isset($opportunityDetail['term_length']) && !empty($opportunityDetail['term_length'])){
						$term_length = $opportunityDetail['term_length'];
						$expiration_date = date('m/d/Y',strtotime("+ ".$term_length." months",strtotime($opportunityDetail['effective_date'])));

					}
				}
				if(isset($getAllRenewalSuccessEntry['policy_number']) && !empty($getAllRenewalSuccessEntry['policy_number']))
				{
					$policy_number = $getAllRenewalSuccessEntry['policy_number'];
				}
				else if(isset($opportunityDetail['policy_number']) && !empty($opportunityDetail['policy_number']))
				{
					$policy_number = $opportunityDetail['policy_number'];
				}
				if(isset($opportunityDetail['insurance_type']['type']) && !empty($opportunityDetail['insurance_type']['type']))
				{
					$policy_type = $opportunityDetail['insurance_type']['type'];
				}
				$lob_code = "";
				if(isset($opportunityDetail['insurance_type']['type']) && !empty($opportunityDetail['insurance_type']['type']))
				{
					$lob_code = $opportunityDetail['insurance_type']['lob_code'];
				}
				$contact_name= "";
				if(isset($contact) && !empty($contact))
				{
					$contact_name =ucwords($contact['first_name']);
					if(isset($contact['last_name']) && !empty($contact['last_name']))
					{
						$contact_name = $contact_name." ".ucwords($contact['last_name']);
					}
				}else{

					if(!empty($business['name']))
					{
						$contact_name =ucwords($business['name']);
					}
				}
				$carrier_name = '';
				if(isset($opportunityDetail['carrier_id']) && !empty($opportunityDetail['carrier_id']))
				{
					$carrier_id = $opportunityDetail['carrier_id'];
					$getCarrierDetails = $Carriers->carrierDetail($carrier_id);
					if(isset($getCarrierDetails) && !empty($getCarrierDetails))
					{
						$carrier_name = $getCarrierDetails['name'];
						$naic_code = $getCarrierDetails['naic_code'];
					}
				}
                $premium = "";
                if(isset($opportunityDetail['premium_amount']) && !empty($opportunityDetail['premium_amount']))
                {
                    $premium = $opportunityDetail['premium_amount'];
                }
				if(in_array($acord_form,_ACORD_CERTIFACTES))
				{
					$create_type = 'Certificate';
					if($policy_type == 'General Liability Claims Made' || $policy_type == 'Genl Liability' || $policy_type == 'General Liability Claims Made' || $policy_type == 'General' || $lob_code=='GCM'|| $lob_code=='CGL')
					{
						$policy_details = array(
						"policy_number_for_general_liability"=>$policy_number,
						"effective_date_for_general_liability" =>$effective_date,
						"expiration_date_for_general_liability"=>$expiration_date,

						"policy_number" =>$policy_number,
						"effective_date"=> $effective_date,
						"expiration_date"=> $expiration_date,
						"naic_code_A"=> $naic_code,
						"naic_code_B"=> "",
						"naic_code_C"=> "",
						"naic_code_D"=> "",
						"naic_code_E"=> "",
						"naic_code_F"=> "",
						"insurer_name_A"=>$carrier_name,
						"insurer_name_B"=> "",
						"insurer_name_C"=> "",
						"insurer_name_D"=> "",
						"insurer_name_E"=> "",
						"insurer_name_F"=> ""
						);
					}
					else if($policy_type == 'Workers Comp Marine' || $policy_type == 'Workers Compensation Participating' || $policy_type == 'Workers Compensation'|| $policy_type == 'Workplace Violence' || $lob_code == 'WORKP' || $lob_code == 'WCMA' || $lob_code == 'WORK' || $lob_code == 'WORKV')
					{

						$policy_details = array(
						"policy_number_for_workers_compensation_and_employers_liability"=>$policy_number,
						"effective_date_for_workers_compensation_and_employers_liability" =>$effective_date,
						"expiration_date_for_workers_compensation_and_employers_liability"=>$expiration_date,

						"policy_number" =>$policy_number,
						"effective_date"=> $effective_date,
						"expiration_date"=> $expiration_date,
						"naic_code_A"=> $naic_code,
						"naic_code_B"=> "",
						"naic_code_C"=> "",
						"naic_code_D"=> "",
						"naic_code_E"=> "",
						"naic_code_F"=> "",
						"insurer_name_A"=>$carrier_name,
						"insurer_name_B"=> "",
						"insurer_name_C"=> "",
						"insurer_name_D"=> "",
						"insurer_name_E"=> "",
						"insurer_name_F"=> ""
						);
					}
					else if($policy_type == 'Auto (Commercial)' || $policy_type == 'Auto (Personal)' || $policy_type == 'Automobile - Personal' || $policy_type == 'Auto'|| $policy_type == 'Automobile - Business' || $lob_code == 'AUTOP' || $lob_code == 'AUTOC'|| $lob_code == 'AUTOB')
					{
						$policy_details = array(
						"policy_number_for_automobile_liability"=>$policy_number,
						"effective_date_for_automobile_liability" =>$effective_date,
						"expiration_date_for_automobile_liability"=>$expiration_date,

						"policy_number" =>$policy_number,
						"effective_date"=> $effective_date,
						"expiration_date"=> $expiration_date,
						"naic_code_A"=> $naic_code,
						"naic_code_B"=> "",
						"naic_code_C"=> "",
						"naic_code_D"=> "",
						"naic_code_E"=> "",
						"naic_code_F"=> "",
						"insurer_name_A"=>$carrier_name,
						"insurer_name_B"=> "",
						"insurer_name_C"=> "",
						"insurer_name_D"=> "",
						"insurer_name_E"=> "",
						"insurer_name_F"=> ""
						);
					}

					else if($policy_type == 'Excess' || $policy_type == 'Excess Liability' || $policy_type == 'Excess Management Liability' || $lob_code == 'EMGLI' || $lob_code == 'EXLIA')
					{
						$policy_details = array(
						"policy_number_for_excess_liability"=>$policy_number,
						"effective_date_for_excess_liability" =>$effective_date,
						"expiration_date_for_excess_liability"=>$expiration_date,

						"policy_number" =>$policy_number,
						"effective_date"=> $effective_date,
						"expiration_date"=> $expiration_date,
						"naic_code_A"=> $naic_code,
						"naic_code_B"=> "",
						"naic_code_C"=> "",
						"naic_code_D"=> "",
						"naic_code_E"=> "",
						"naic_code_F"=> "",
						"insurer_name_A"=>$carrier_name,
						"insurer_name_B"=> "",
						"insurer_name_C"=> "",
						"insurer_name_D"=> "",
						"insurer_name_E"=> "",
						"insurer_name_F"=> ""
						);

					}else{
						$policy_details = array(
						"policy_number_for_general_liability" => "",
						"effective_date_for_general_liability" => "",
						"expiration_date_for_general_liability" => "",
						"policy_number_for_automobile_liability" => "",
						"effective_date_for_automobile_liability" => "",
						"expiration_date_for_automobile_liability" => "",
						"policy_number_for_workers_compensation_and_employers_liability" => "",
						"effective_date_for_workers_compensation_and_employers_liability" => "",
						"expiration_date_for_workers_compensation_and_employers_liability" => "",
						"policy_number_for_excess_liability" => "",
						"effective_date_for_excess_liability" => "",
						"expiration_date_for_excess_liability" => "",
						"policy_number_for_other_policy" => $policy_number,
						"effective_date_for_other_policy" => $effective_date,
						"expiration_date_for_other_policy" => $expiration_date,
						"other_policy_description" => "",

						"policy_number" =>$policy_number,
						"effective_date"=> $effective_date,
						"expiration_date"=> $expiration_date,
						"naic_code_A"=> $naic_code,
						"naic_code_B"=> "",
						"naic_code_C"=> "",
						"naic_code_D"=> "",
						"naic_code_E"=> "",
						"naic_code_F"=> "",
						"insurer_name_A"=>$carrier_name,
						"insurer_name_B"=> "",
						"insurer_name_C"=> "",
						"insurer_name_D"=> "",
						"insurer_name_E"=> "",
						"insurer_name_F"=> ""
						);
					}


				}else{
						$policy_details = array(
						"policy_number" => $policy_number,
						"billing_account_number" => "",
						"effective_date"=> $effective_date,
						"expiration_date" => $expiration_date,
						"payment_direct_bilI_indicator" => "",
						"payment_producer_bill_indicator" => "",
						"mail_to_producer_indicator" => "",
						"mail_to_named_insured_indicator" => "",
						"payment_payment_schedule_code" => "",

						"naic_code_A"=> $naic_code,
						"naic_code_B"=> "",
						"naic_code_C"=> "",
						"naic_code_D"=> "",
						"naic_code_E"=> "",
						"naic_code_F"=> "",
						"insurer_name_A"=>$carrier_name,
						"insurer_name_B"=> "",
						"insurer_name_C"=> "",
						"insurer_name_D"=> "",
						"insurer_name_E"=> "",
						"insurer_name_F"=> ""
						);
				}
			}
            //get and set receipt number
            $receiptData = [];
            if($contact_id != '') {
                $result = $ContactAcordForms->find('all')->select(['id', 'created', 'receipt_number'])->where(['contact_id' => $contact_id])->order(['id' => 'desc'])->first();
                $receiptData = $IvansComponent->getReceiptNumber($result);
                if($receiptData['receipt_data'] != '') {
                    $query_string .= '&receipt_number='.$receiptData['receipt_data'];
                }
            }
			$callback_url = _CALL_BACKURL_ACORD.$query_string;
			$agency_details = array();
            $addressTwo = '';
			if(isset($agency) && !empty($agency))
			{
				if(!empty($agency['us_state_id']))
				{
				$stateDetail = $UsStates->stateDetail($agency['us_state_id']);
				$agency_state_name = $stateDetail->short_name;
                $usersTimezone='America/Phoenix';
				}
                if(isset($agency['time_zone']) && !empty($agency['time_zone']))
                {
                    $usersTimezone =  $agency['time_zone'];
                }
                else if(isset($stateDetail) && !empty($stateDetail))
                {
                    $usersTimezone =  $stateDetail->time_zone;
                }
                if($agency['city'] != '' && $agency['zip'] != '') {
                    $addressTwo = $agency['city'] . ', ' . $agency['zip'];
                }

			}
            $current_date = date('m/d/Y',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s'))));
			$contact_details = array();
			$email_template_keys = array();
			if(isset($contact) && !empty($contact))
			{
				$fullname =ucwords($contact['first_name']);
				if(isset($contact['last_name']) && !empty($contact['last_name']))
				{
					$fullname = $fullname." ".ucwords($contact['last_name']);
				}
				if(!empty($contact['state_id']))
				{
				$stateDetail = $UsStates->stateDetail($contact['state_id']);
				$state_name = $stateDetail->short_name;
				}
				$contact_details =array(
					"fullName" => $fullname,
					"address_city_name" => $contact['city'],
					"address_county_name" => "US",
					"address_state_code" => $state_name,
					"address_postal_code" => $contact['zip'],
					"phone_number" => $contact['phone']
				);
				$email_template_keys = array(
					"contact_email_address"=> $contact['email'],
					"contact_firstname"=> ucwords($contact['first_name']),
					"acord_formname_humanized"=> $acord_form,
					"agent_signature"=> $agency['company']
				);
				$producer_contact_person_details=array(
					"producer_contact_person_fullName"=> $user_name,
					"producer_contact_person_phoneNumber"=> $agency['phone'],
					"producer_faxNumber"=> "",
					"producer_contact_person_email_address"=> $user_email
				);
                if($acord_form == 'ACORD 001 Receipt') {
                    $agency_details = array(
                        "full_name" => $agency['company'],
                        "full_name_A" => $agency['company'],
                        "producer_email_address_A" => $agency['email'],
                        "producer_phone_number_A" => $agency['phone'],
                        "producer_website_A" => $agency['default_schedule_link'],
                        "mailing_address_line_one" => $agency['address'],
                        "mailing_address_line_two" => "",
                        "address_line_one" => $agency['address'],
                        "address_line_two" => $addressTwo,
                        "mailing_address_city_name" => $agency['city'],
                        "mailing_address_state_code" => $agency_state_name,
                        "mailing_address_postal_code" => $agency['zip'],
                        "receipt_number_A" => $receiptData['receipt_number'],
                        "receipt_date_A" => $current_date,
                        "named_insured_contact_primary_email_address_A" => $contact['email'],
                        "line_of_business" => $policy_type,
                        "premium" => $premium
                    );
                } else {
                    $agency_details = array(
                        "full_name" => $agency['company'],
                        "full_name_A" => $agency['company'],
                        "mailing_address_line_one" => $agency['address'],
                        "mailing_address_line_two" => "",
                        "address_line_one" => $contact['address'],
                        "address_line_two" => $contact['address_line_2'],
                        "mailing_address_city_name" => $agency['city'],
                        "mailing_address_state_code" => $agency_state_name,
                        "mailing_address_postal_code" => $agency['zip']
                    );
                }
			}
            $acordAccessToken = Ivans::getAcordAccessToken();

			if(isset($objectData['opportunity_id']) && !empty($objectData['opportunity_id'])){
				$opportunity_id = $objectData['opportunity_id'];
				$getPolicyGuid = $ContactOpportunities->getPolicyGuid($opportunity_id,$login_agency_id);
				// echo '<pre>';
				// print_r($getPolicyGuid);
				// die();
					if(isset($getPolicyGuid->ivans_policy_id) &&!empty($getPolicyGuid->ivans_policy_id))
					{
                        $ivans_account_id = '';
                        $requestData = array();
                        $requestData['API'] = _GET_POLICY_SUMMERY_BY_POLICY_ID;
                        $requestData['request_param'] = array('policy_id'=>$getPolicyGuid->ivans_policy_id);
                        $requestData['guid'] = $ivans_agency_guid;
                        $requestData['agency_id'] = $login_agency_id;
                        $policy_api_response = CommonFunctions::getDataByApi($requestData);
                        if(isset($policy_api_response['output']['policies']['account']['guid']) && !empty($policy_api_response['output']['policies']['account']['guid']))
                        {
                            $ivans_account_id = $policy_api_response['output']['policies']['account']['guid'];
                            $Contacts->updateAll(['ivans_contact_guid'=>$ivans_account_id],['id'=>$contact_id]);

                        }

						// for already downloaded policy generate certificate
						$policy_guid = $getPolicyGuid->ivans_policy_id;
						$policyData = array();
						if($create_type =='Certificate'){
							$policyData = array(
							"Agency_Identity_Id" => $ivans_agency_id,
							"saving_type"=> "policy",
							"Agency_token" => $agency_token,
							"Staff_Email" => $user_email,
							"form_name" => $acord_form,
							"policy_type" => 'p',
							"account_id" => $ivans_account_id,
							"return_URL" => $callback_url,
							"Policy_GUID" => $policy_guid,
							"email_body_template_keys" => $email_template_keys
							);
							if($create_type == 'Certificate'){
								$url =  _REQUEST_POLICY_CERTIFICATE;
							}else{
								$url = _ACORD_FORM_POLICY_FILL;
							}
							$response = json_encode(array('status' => _ID_SUCCESS, 'policyData' => $policyData, 'policy_guid' => _ID_SUCCESS, 'create_type' => $create_type, 'url' => $url, 'AccessToken' => $acordAccessToken));
                            $txt = date('Y-m-d H:i:s').' :: JSON Data for certificate'.$response;
                            fwrite($myfile,$txt.PHP_EOL);
						}else{
							// for already downloaded policy create application form
							$policyData = array(
								"Agency_Identity_Id" => $ivans_agency_id,
								"saving_type"=> "policy",
								"Agency_token" => $agency_token,
								"Staff_Email" => $user_email,
								"Form_unique_identifier" => $acord_form,
								"return_URL" => $callback_url,
								"Policy_GUID" => $policy_guid,
								"email_body_template_keys" => $email_template_keys
							);
							if($create_type == 'Certificate'){
								$url =  _REQUEST_POLICY_CERTIFICATE;
							}else{
								$url = _ACORD_FORM_POLICY_FILL;
							}
							$response = json_encode(array('status' => _ID_SUCCESS, 'policyData' => $policyData, 'policy_guid' => _ID_SUCCESS, 'create_type' => $create_type, 'url' => $url, 'AccessToken' => $acordAccessToken));
                            $txt = date('Y-m-d H:i:s').' :: JSON Data for application form'.$response;
                            fwrite($myfile,$txt.PHP_EOL);
						}
					}else{
						$policyData = array(
							"Agency_Identity_Id" => $ivans_agency_id,
							"saving_type"=> "policy",
							"Agency_token" => $agency_token,
							"Staff_Email" => $user_email,
							"Form_unique_identifier" => $acord_form,
							"return_URL" => $callback_url,
							"agency" => $agency_details,
							"contact" => $contact_details,
							"policy" => $policy_details,
							"email_body_template_keys" => $email_template_keys,
							"producer_contact_person_details" => $producer_contact_person_details
							);
							$response = json_encode(array('status' => _ID_SUCCESS, 'policyData' => $policyData, 'policy_guid'=>_ID_FAILED, 'create_type'=>$create_type,'url'=>_ACORD_APPLICATION_FORM_FILL, 'AccessToken' => $acordAccessToken));
                            $txt = date('Y-m-d H:i:s').' :: JSON Data when empty GUID'.$response;
                            fwrite($myfile,$txt.PHP_EOL);
					}
			}else{
					$policyData = array(
							"Agency_Identity_Id" => $ivans_agency_id,
							"saving_type"=> "contact",
							"Agency_token" => $agency_token,
							"Staff_Email" => $user_email,
							"Form_unique_identifier" => $acord_form,
							"return_URL" => $callback_url,
							"agency" => $agency_details,
							"contact" => $contact_details,
							//"policy" => $policy_details,
							"email_body_template_keys" => $email_template_keys,
							"producer_contact_person_details" => $producer_contact_person_details
							);
							$response = json_encode(array('status' => _ID_SUCCESS, 'policyData'=>$policyData, 'policy_guid'=>_ID_FAILED, 'create_type'=>$create_type,'url'=>_ACORD_APPLICATION_FORM_FILL, 'AccessToken' => $acordAccessToken));
                            $txt = date('Y-m-d H:i:s').' :: JSON Data when empty opportunity id'.$response;
                            fwrite($myfile,$txt.PHP_EOL);
			}
		}else{
				$response = json_encode(array('status' => _ID_FAILED));
			}
			return $response;
			die();

	}
	public function mapAcordFormToPolicy($objectData)
	{
		$session = Router::getRequest()->getSession();
		$login_user_id = $session->read("Auth.User.user_id");
		$login_role_type = $session->read('Auth.User.role_type');
		$login_role_type_flag = $session->read('Auth.User.role_type_flag');
		$login_permissions = $session->read('Auth.User.permissions');
		$login_permissions_arr = explode(",",$login_permissions);
		$ContactAcordForms = TableRegistry::getTableLocator()->get('ContactAcordForms');
		$contact_id = '';
		if(isset($objectData['contact_id']) && !empty($objectData['contact_id'])){
		  $contact_id =$objectData['contact_id'];
		}
		$acord_form_id = '';
		if(isset($objectData['acord_form_id']) && !empty($objectData['acord_form_id'])){
		  $acord_form_id =$objectData['acord_form_id'];
		}
		$opportunity_id = '';
        $attachDate = '';
		if(isset($objectData['opportunity_id']) && !empty($objectData['opportunity_id'])){
			if($objectData['opportunity_id'] == 'Null')
			{
				$opportunity_id = '';
				$ContactAcordForms->updateAll(['opportunity_id'=>$opportunity_id],['id'=>$acord_form_id]);
				$response =  json_encode(array('status' => _ID_SUCCESS, 'id'=>$acord_form_id));
				return $response;
			}else
			{
				$explode_policy_type_id = (isset($objectData['opportunity_id'])&& (!empty($objectData['opportunity_id']))) ? explode("-",$objectData['opportunity_id']) : '';
                    $attachDate = date("Y-m-d H:i:s");
				$contact_opprtunity_id = $explode_policy_type_id!= '' ? $explode_policy_type_id[0] : '';
				$opportunity_id =  $explode_policy_type_id!= '' ? $explode_policy_type_id[1] : '';
			}

		}
		if(!empty($acord_form_id) && !empty($opportunity_id) && !empty($attachDate))
		{
			$ContactAcordForms->updateAll(['opportunity_id'=>$opportunity_id,'attach_date'=>$attachDate],['id'=>$acord_form_id]);
            $response =  json_encode(array('status' => _ID_SUCCESS));
		}else{
			$response =  json_encode(array('status' => _ID_FAILED));
		}
		return $response;
	}


	public function downloadAcordApplication($objectData){
		$session = Router::getRequest()->getSession();
		$login_user_id = $session->read("Auth.User.user_id");
		$login_role_type = $session->read('Auth.User.role_type');
		$login_role_type_flag = $session->read('Auth.User.role_type_flag');
		$login_permissions = $session->read('Auth.User.permissions');
		$login_permissions_arr = explode(",",$login_permissions);
		$login_agency_id= $session->read("Auth.User.agency_id");
		$IvansAgencyTokenDetail = TableRegistry::getTableLocator()->get('IvansAgencyTokenDetail');
		if(isset($objectData['form_id']) && !empty($objectData['form_id']))
		{
			$form_id = $objectData['form_id'];
		}
		$getIvansAgencyDetails = $IvansAgencyTokenDetail->find('All')->select(['id','agency_token'])->where(['agency_id' => $login_agency_id,'status'=>_ID_SUCCESS])->first();
		$agency_token = '';
		if(isset($getIvansAgencyDetails) && !empty($getIvansAgencyDetails)){
			$agency_token = $getIvansAgencyDetails['agency_token'];
		}
		//$agency_token = 'ae98412c-6574-4bf7-ac49-446c0dd631f6';
		$headers = array(
			'agency_token: '.$agency_token,
			);
		$post_fields = array('form_id' => $form_id);
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => _DOWNLOAD_ACORD_FORM_API,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => $post_fields,
		CURLOPT_HTTPHEADER => $headers,
		));

		$response = curl_exec($curl);
		curl_close($curl);
		$response_array = json_decode($response);
		$file_name = 'Acord_form_#'.$form_id.'.pdf';
		$dir_name = 'Acord_forms';
		if (!is_dir('uploads/'.$dir_name))
		{
			mkdir('uploads/'.$dir_name);
			chmod('uploads/'.$dir_name, 0777);
		}else{
			chmod('uploads/'.$dir_name, 0777);
		}
		$file_path =  WWW_ROOT  .'uploads/'.$dir_name.'/'.$file_name;
		$acord_form_content = base64_decode($response_array->data);
		file_put_contents($file_path,$acord_form_content);
		if (file_exists($file_path)) {
		header('Content-Description: File Transfer');
		header('Content-Type: application/pdf');
		header('Content-Disposition: attachment; filename="'.basename($file_path).'"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file_path));
		// $response = json_encode(array('status' => _ID_SUCCESS, 'view_url' => $file_path ));
		// return $response;
		readfile($file_path);
		exit;
		}

	}

	// post duplicate acord forms
	public function duplicateAccordForm($objectData){
        $myfile = fopen(ROOT."/logs/beta_acord_json.log", "a") or die("Unable to open file!");
		$session = Router::getRequest()->getSession();
		$login_user_id = $session->read("Auth.User.user_id");
		$login_role_type = $session->read('Auth.User.role_type');
		$login_role_type_flag = $session->read('Auth.User.role_type_flag');
		$login_permissions = $session->read('Auth.User.permissions');
		$login_permissions_arr = explode(",",$login_permissions);
		$login_agency_id= $session->read("Auth.User.agency_id");
		$IvansAgencyTokenDetail = TableRegistry::getTableLocator()->get('IvansAgencyTokenDetail');
		$IvansUserDetail = TableRegistry::getTableLocator()->get('IvansUserDetail');
		$Contacts  = TableRegistry::getTableLocator()->get('Contacts');
		$ContactAcordForms = TableRegistry::getTableLocator()->get('ContactAcordForms');
		$Agency = TableRegistry::getTableLocator()->get('Agency');
		$getIvansAgencyDetails = $IvansAgencyTokenDetail->getAgencyToken($login_agency_id);
		$agency_token = '';
		if(isset($getIvansAgencyDetails) && !empty($getIvansAgencyDetails)){
			$agency_token = $getIvansAgencyDetails['agency_token'];
		}
		$checkIvansUserExist = $IvansUserDetail->checkIvansAccessToUser($login_agency_id,$login_user_id);
		$user_email = '';
		if(isset($checkIvansUserExist['user_email']) && !empty($checkIvansUserExist['user_email'])){
			$user_email = $checkIvansUserExist['user_email'];
		}
		$query_string = '';
		if(isset($objectData['contact_id']) && !empty($objectData['contact_id']))
		{
			$contact_id = $objectData['contact_id'];
			$contact = $Contacts->contactDetails($contact_id);
			$query_string .= '?contact_id='.$contact_id;
		}
		if(isset($objectData['acord_form_id']) && !empty($objectData['acord_form_id']))
		{
			$acord_form_id = $objectData['acord_form_id'];
			$accord_form_detail = $ContactAcordForms->getAcordFormById($acord_form_id);
		}
		$acordAccessToken = Ivans::getAcordAccessToken();
		
		$callback_url = _CALL_BACKURL_ACORD.$query_string;
		$agency = $Agency->agencyDetails($login_agency_id);
		if(!empty($user_email) && !empty($agency_token) && !empty($accord_form_detail)){
			$accord_form = $accord_form_detail['acord_form_title'];
			$form_token = $accord_form_detail['form_id'];

			$email = $contact['email'];
			$name = $contact['first_name'];


			$email_template_keys = array(
				"contact_email_address" => $email,
				"contact_firstname" => $name,
				"acord_formname_humanized" => $accord_form,
				"agent_signature" => $agency['company']
			);
			$postArr = array(
				"agency_token" => $agency_token,
				"saving_type" => "contact",
				"staff_email" => $user_email,
				"form_token" => $form_token ,
				"return_url" => $callback_url,
				"email_body_template_keys"=>$email_template_keys
			);
			//$PolicyData = json_encode($postArr);
			$response = json_encode(array('status' => _ID_SUCCESS,'PostData' => $postArr,'response' => 'Duplicate acord form created', 'url'=> _DUPLICATE_ACORD_FORM, 'AccessToken' => $acordAccessToken));
            
            $txt = date('Y-m-d H:i:s').' :: user_id'.$login_user_id.'contact_id'.$contact_id.'response JSON : '.$response;
            fwrite($myfile,$txt.PHP_EOL);
		}
		else{
			$response = json_encode(array('status' => 403,'response' => 'User email or Agency id is required','PostData' => array()));
            $txt = date('Y-m-d H:i:s').' :: user_id'.$login_user_id.'contact_id'.$contact_id.'response JSON : '.$response;
            fwrite($myfile,$txt.PHP_EOL);
		}
		return $response;die;
	}

	public function getAutocompleteAcordFormsList($objectData)
	{
		$session = Router::getRequest()->getSession();
			$login_user_id = $session->read("Auth.User.user_id");
			$login_role_type = $session->read('Auth.User.role_type');
			$login_role_type_flag = $session->read('Auth.User.role_type_flag');
			$login_permissions = $session->read('Auth.User.permissions');
			$login_permissions_arr = explode(",",$login_permissions);
            $ContactAcordForms = TableRegistry::getTableLocator()->get('ContactAcordForms');
            $AgencyTable = TableRegistry::getTableLocator()->get('Agency');
            $UsersTable = TableRegistry::getTableLocator()->get('Users');
			$Contacts = TableRegistry::getTableLocator()->get('Contacts');
			$IvansAgencyTokenDetail = TableRegistry::getTableLocator()->get('IvansAgencyTokenDetail');
            $login_agency_id= $session->read("Auth.User.agency_id");
			$acord_forms = [];
			$acord_form = '';
			if(isset($objectData['acord']) && !empty($objectData['acord'])){
				$acord_form =$objectData['acord'];
			}
			$getIvansAgencyDetails = $IvansAgencyTokenDetail->find('All')->select(['id','agency_token'])->where(['agency_id' => $login_agency_id,'status'=>_ID_SUCCESS])->first();
			$agency_token = '';
			if(isset($getIvansAgencyDetails) && !empty($getIvansAgencyDetails)){
				$agency_token = $getIvansAgencyDetails['agency_token'];
			}
			//$agency_token = 'ae98412c-6574-4bf7-ac49-446c0dd631f6';
			$headers = array(
				'agency_token: '.$agency_token,
				);
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => _SUPPORTED_ACORD_FORM_API,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_HTTPHEADER => $headers,
			));

			$response = curl_exec($curl);
			$response = json_decode($response);
			curl_close($curl);
			if(!empty($response) && isset($response->data->form_unique_identifier)){
				$acordFormList = $response->data->form_unique_identifier->acord_application_forms;
				$acordFormList = array_merge($response->data->form_unique_identifier->acord_certificates,$response->data->form_unique_identifier->acord_application_forms);

				// $matches = array_filter($acordFormList, function($var) use ($acord_form) {
				// return preg_match("/\b".$acord_form."\b/i", $var);
				// });
                $matches = array_filter($acordFormList, function($var) use ($acord_form) {
	            return preg_match("/".$acord_form."/i", $var);
	            });
				// echo '<pre>';
				// print_r($matches);
				// die();
				//$acordCertificates = _ACORD_CERTIFACTES;
				if(!empty($matches)){
					natsort($matches);
					if(isset($matches) && !empty($matches)){
						$i = 0;
						foreach($matches as $key => $acord_form){
							$acord_forms[$i]['id'] = $key;
							$acord_forms[$i]['name'] = str_replace('Acord', '', ucwords(strtolower($acord_form)));
							$i++;
						}
					}
				}
				$response = json_encode(array('status' => _ID_SUCCESS, 'acord_forms' => $acord_forms ));
			}else{
				$response = json_encode(array('status' => _ID_FAILED, 'acord_forms' => [] ));
			}
			return $response;

	}

	public function getPolicyTypes($contactId, $fields)
	{
		$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
		$getMultiPolicyListingActive = $ContactOpportunities->getMultiPolicyActiveListing($contactId);
        $getMultiPolicyListingInactive = $ContactOpportunities->getMultiPolicyInActiveListing($contactId);
		$getMultiPolicyListingPending = $ContactOpportunities->getMultiPolicyPendingListing($contactId);
		$policyTypes = array();
		if (isset($getMultiPolicyListingActive) && !empty($getMultiPolicyListingActive)) {
			$insurance_type_name ='';
			array_push($policyTypes,array('header'=>'Active policies'));
			foreach ($getMultiPolicyListingActive as $key => $value) {

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
		if (isset($getMultiPolicyListingPending) && !empty($getMultiPolicyListingPending)) {
			$insurance_type_name ='';
			array_push($policyTypes,array('header'=>'Pending policies'));
			foreach ($getMultiPolicyListingPending as $key => $value) {

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
		$return['Contacts.getPolicyTypes'] = [
				$contactId => $policyTypes
		];
		return $return;
	}

	public static function updateCustomFields($updatedCustoomFieldData){

		$response = [];
		$infoCustomFields = [];

		try{

			$ContactCustomFields = TableRegistry::getTableLocator()->get('ContactCustomFields');
			foreach($updatedCustoomFieldData as $customFieldKey => $updatedCustoomField)
			{
				$ContactCustomFields->updateAll(['field_value'=>$updatedCustoomField['field_value']],['id' => $updatedCustoomField['id']]);
				$response = json_encode(array('message_status' => _ID_SUCCESS));
			}

		}catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: updateCustomFields  Error- '.$e->getMessage();
			$response = json_encode(array('message_status' => _ID_FAILED,'error' => $txt));
        }

		return $response;
	}

	public function deleteAcord($acordId)
	{
		$ContactAcord = TableRegistry::getTableLocator()->get('ContactAcordForms');
		$acordDetail = $ContactAcord->get($acordId);
		if(isset($acordDetail) && !empty($acordDetail))
		{
			if($ContactAcord->updateAll(['status'=>_ID_STATUS_INACTIVE],['id' => $acordId]))
			{
				$response = json_encode(array('status' => _ID_SUCCESS,'message' => 'Acord deleted successfully!'));
			}
			else
			{
				$response = json_encode(array('status' => _ID_FAILED));
			}
		}
		return $response;
	}

	public function updateAcordName($updatedData)
	{
		$ContactAcord = TableRegistry::getTableLocator()->get('ContactAcordForms');

		$acordId = $updatedData['acord_id'];
		$acordName =  trim($updatedData['name']);
		$acord = $ContactAcord->get($acordId);
		$accordArr = [];
		$accordArr['acord_form_display_name'] = $acordName;
		$acord = $ContactAcord->patchEntity($acord,$accordArr);
		if($ContactAcord->save($acord))
		{
			$response = json_encode(array('status' => _ID_SUCCESS,'message' => 'Acord renamed successfully!'));
		}
		else
		{
			$response = json_encode(array('status' => _ID_FAILED,'message'=>'Something went wrong!'));
		}
		return $response;
	}

    public function getLogsListing($contactId, $fields = null){
        try{
            $contactLogs = TableRegistry::getTableLocator()->get('ContactLogs');
            $session = Router::getRequest()->getSession();
			$login_user_id = $session->read("Auth.User.user_id");
			$login_role_type = $session->read('Auth.User.role_type');
			$login_role_type_flag = $session->read('Auth.User.role_type_flag');
			$login_permissions = $session->read('Auth.User.permissions');
			$login_permissions_arr = explode(",",$login_permissions);
			$where_cond = array();
			$contact_id = '';
            $limit = 20;
            $offset = 0;
            $ContactAcordForms = TableRegistry::getTableLocator()->get('ContactAcordForms');
            $AgencyTable = TableRegistry::getTableLocator()->get('Agency');
            $UsersTable = TableRegistry::getTableLocator()->get('Users');
			$Contacts = TableRegistry::getTableLocator()->get('Contacts');
            $login_agency_id= $session->read("Auth.User.agency_id");
            $agency = $AgencyTable->agencyDetails($login_agency_id);

            if(!empty($contactId))
            {
                $contact_logs_arr = $contactLogs->getLogList($contactId,$limit,$offset);
            }
            if(isset($contact_logs_arr) && !empty($contact_logs_arr))
            {
                $count = 0;
                foreach ($contact_logs_arr as $key => $value)
                {
                    $date = CommonFunctions::get_time_ago(strtotime($value['created']));
                    $platform = $value['platform'];
                    $logo = '<img src="'.SITEURL.'/img/better.png" style="width:30px;">';
                    if($platform == 5){
                        $logo = '<img src="'.SITEURL.'/img/hawksoft.png" style="width:30px;">';
                    }else if($platform == 9){
                        $logo = '<img src="'.SITEURL.'/img/ezlynx.png" style="width:30px;">';
                    }
                    $first_name = "";
                    $last_name = "";
                    $email = "";
                    $phone = "";
                    $fullName = "";
                    if(isset($value['first_name']) && !empty($value['first_name'])){
                        $first_name = $value['first_name'];
                        $fullName .= " ".$first_name;
                    }
                    if(isset($value['last_name']) && !empty($value['last_name'])){
                        $last_name = $value['last_name'];
                        $fullName .= " ".$last_name;
                    }
                    if(isset($value['email']) && !empty($value['email'])){
                        $email = $value['email'];
                    }
                    if(isset($value['phone']) && !empty($value['phone'])){
                        $phone = $value['phone'];
                    }
                    $log_message="";
                    if(isset($value['message']) && !empty($value['message'])){
                        $log_message = $value['message'];
                    }

					$finalResponse[$count]['id'] = $value['id'];
					$finalResponse[$count]['logDetail'] = $log_message;
					$finalResponse[$count]['first_name'] = $first_name;
					$finalResponse[$count]['last_name'] = $last_name;
					$finalResponse[$count]['email'] = $email;
					$finalResponse[$count]['phone'] = $phone;
					$finalResponse[$count]['date'] = $date;
                    $count++;
                }


            }

            $return['Contacts.getLogsListing'] = [
                $contactId => $finalResponse
            ];

            return $return;

        }catch (\Exception $e) {
            $txt=date('Y-m-d H:i:s').' :: Logs Lising Error-- '.$e->getMessage();
			//fwrite($myfile,$txt.PHP_EOL);
        }
    }

    // get opt in out logs data
    public function getOptPhoneLogsListing($contactId){
        try {
            $contactLogs = TableRegistry::getTableLocator()->get('ContactLogs');
            $session = Router::getRequest()->getSession();
            $login_user_id = $session->read("Auth.User.user_id");
            $login_role_type_flag = $session->read('Auth.User.role_type_flag');
            $login_role_type = $session->read('Auth.User.role_type');
            $login_permissions = $session->read('Auth.User.permissions');
            $login_permissions_arr = explode(",", $login_permissions);
            $where_cond = array();
            $contact_id = '';
            $AgencyTable = TableRegistry::getTableLocator()->get('Agency');
            $ContactAcordForms = TableRegistry::getTableLocator()->get('ContactAcordForms');
            $UsersTable = TableRegistry::getTableLocator()->get('Users');
            $PhoneNumbersOptInOutLogs = TableRegistry::getTableLocator()->get('PhoneNumbersOptInOutLogs');
            $Contacts = TableRegistry::getTableLocator()->get('Contacts');
            $login_agency_id = $session->read("Auth.User.agency_id");
            if (!empty($contactId)) {
                $contact = $Contacts->contactDetails($contactId);
                if(isset($contact['phone']) && $contact['phone'] != ''){
                    $opt_logs_arr = $PhoneNumbersOptInOutLogs->optInLogsList($login_agency_id,$contactId);
                }
            }
            if(isset($opt_logs_arr) && !empty($opt_logs_arr)) {
                $count = 0;
                foreach ($opt_logs_arr as $opt_logs) {
                    $date = CommonFunctions::get_time_ago(strtotime($opt_logs['created']));
                    $phone = "";
                    if (isset($opt_logs['phone_number']) && !empty($opt_logs['phone_number'])) {
                        $phone = $opt_logs['phone_number'];
                    }
                    $status = 'Opt Out';
                    if (isset($opt_logs['status']) && !empty($opt_logs['status'])) {
                        if ($opt_logs['status'] == 1) {
                            $status = "Opt In";
                        }
                    }
                    $msg = "";
                    if (isset($opt_logs['message']) && !empty($opt_logs['message'])) {
                        $msg = $opt_logs['message'];
                    }
                    $finalResponse[$count]['id'] = $opt_logs['id'];
                    $finalResponse[$count]['phone'] = $phone;
                    $finalResponse[$count]['status'] = $status;
                    $finalResponse[$count]['message'] = strip_tags($msg);
                    $finalResponse[$count]['date'] = $date;
                    $count++;
                }
            }
            $return['Contacts.getOptPhoneLogsListing'] = [
                $contactId => $finalResponse
            ];
            return $return;
        }catch (\Exception $e) {
            $txt=date('Y-m-d H:i:s').' :: Logs Lising Error-- '.$e->getMessage();
			//fwrite($myfile,$txt.PHP_EOL);
        }
    }

    // Load More logs data
    public static function loadMoreLogsData($objectData)
    {
        try
		{
			$session = Router::getRequest()->getSession();
            $login_user_id= $session->read("Auth.User.user_id");
            $finalResponse =[];
			$login_agency_id = $session->read('Auth.User.agency_id');
			$Agency = TableRegistry::getTableLocator()->get('Agency');
			$agencyDetail = $Agency->agencyDetails($login_agency_id);
			$contactLogs = TableRegistry::getTableLocator()->get('ContactLogs');
            if(isset($objectData) && !empty($objectData))
		    {
                $contactId = $objectData['contact_id'];
                $limit = $objectData['limit'];
                $offSet = $objectData['offSet'];
                $loadMoreLogData = $contactLogs->getLogList($contactId,$limit,$offSet);
                if(isset($loadMoreLogData) && !empty($loadMoreLogData))
                {
                    $count = $offSet;
                    foreach ($loadMoreLogData as $key => $value)
                    {
                        $date = CommonFunctions::get_time_ago(strtotime($value['created']));
                        $platform = $value['platform'];
                        $logo = '<img src="'.SITEURL.'/img/better.png" style="width:30px;">';
                        if($platform == 5){
                            $logo = '<img src="'.SITEURL.'/img/hawksoft.png" style="width:30px;">';
                        }else if($platform == 9){
                            $logo = '<img src="'.SITEURL.'/img/ezlynx.png" style="width:30px;">';
                        }
                        $first_name = "";
                        $last_name = "";
                        $email = "";
                        $phone = "";
                        $fullName = "";
                        if(isset($value['first_name']) && !empty($value['first_name'])){
                            $first_name = $value['first_name'];
                            $fullName .= " ".$first_name;
                        }
                        if(isset($value['last_name']) && !empty($value['last_name'])){
                            $last_name = $value['last_name'];
                            $fullName .= " ".$last_name;
                        }
                        if(isset($value['email']) && !empty($value['email'])){
                            $email = $value['email'];
                        }
                        if(isset($value['phone']) && !empty($value['phone'])){
                            $phone = $value['phone'];
                        }
                        $log_message="";
                        if(isset($value['message']) && !empty($value['message'])){
                            $log_message = $value['message'];
                        }
                        $finalResponse[$count]['id'] = $value['id'];
                        $finalResponse[$count]['logDetail'] = $log_message;
                        $finalResponse[$count]['first_name'] = $first_name;
                        $finalResponse[$count]['last_name'] = $last_name;
                        $finalResponse[$count]['email'] = $email;
                        $finalResponse[$count]['phone'] = $phone;
                        $finalResponse[$count]['date'] = $date;
                        $count++;
                    }
                }
                $return = json_encode(array('status' => _ID_SUCCESS, 'data' => $finalResponse));
            }else{
                $return = json_encode(array('status' => _ID_FAILED));
            }
            return $return;
        }catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: Email Lising Error- '.$e->getMessage();
			$return = json_encode(array('status' => _ID_FAILED, 'error'=>$txt));
        }
    }

    //this function is used to get the list of additional emails...
    public  function getAdditionalEmails($contactId)
    {
		$ContactEmails = TableRegistry::getTableLocator()->get('ContactEmails');
		$additionalEmails = [];
        if(isset($contactId) && $contactId !== '')
        {
            $getAllEmailArr = $ContactEmails->getAllActiveEmailsByContact($contactId);
            if(isset($getAllEmailArr) && !empty($getAllEmailArr))
            {
                $i = 1;
                foreach ($getAllEmailArr as $contactEmails) {
                    if (isset($contactEmails['email']) && $contactEmails['email'] !== '')
                    {
                        $additionalEmails[$i]['id'] = $contactEmails['id'];
                        $additionalEmails[$i]['email'] = $contactEmails['email'];
                        $additionalEmails[$i]['type'] = $contactEmails['email_type'];
                    }
                    $i++;
                }
            }
        }
		$return['Contacts.getAdditionalEmails'] = [
			 $contactId => $additionalEmails
		];
		return $return;
    }

	public  function getContactAttachments($contactId)
    {
		$ContactAttachments = TableRegistry::getTableLocator()->get('ContactAttachments');
		$attachmentList = [];
        if($contactId)
        {
            $getAllAttachments = $ContactAttachments->getContactAttachmentList($contactId);
            if(isset($getAllAttachments))
            {
                foreach ($getAllAttachments as $attachment) {
                    if (isset($attachment['name']) && $attachment['email'] !== '')
                    {
                        $attachmentList[] = $attachment;
                    }
                }
            }
        }
		$return['Contacts.getContactAttachments'] = [
			 $contactId => $attachmentList
		];
		return $return;
    }

     //Contact Card : code to delete additional emails
    public function deleteAdditionalEmails($emailId){
        $ContactEmails = TableRegistry::getTableLocator()->get('ContactEmails');
         if(isset($emailId) && !empty($emailId))
         {
             $updateEmail = $ContactEmails->updateAll(['status' =>_ID_STATUS_DELETED],['id' => $emailId]);

             if(isset($updateEmail) && !empty($updateEmail)){
                $response =  json_encode(array('status' => _ID_SUCCESS));
             }else{
                $response = json_encode(array('status' => _ID_FAILED,'message'=>"Something went wrong!. Unable to delete email."));
             }
             return $response;
        }
    }
	public function deletePolicyAttachments($objectData) {
		$ContactPolicyAttachments =	TableRegistry::getTableLocator()->get('ContactPolicyAttachments');
        $attachmentDetails = $ContactPolicyAttachments->get($objectData['id']);
        if(isset($attachmentDetails) && !empty($attachmentDetails))
        {
            if($ContactPolicyAttachments->deleteAll(['id'=>$objectData['id']]))
            {
                $response = json_encode(array('status' => _ID_SUCCESS,'attach_id'=>$objectData['id']));
            }
            else
            {
				$response  = json_encode(array('status' => _ID_FAILED));
            }
           return $response;
        }


	}
    public function saveNewSecondaryContact($objectData)
    {
        $session = Router::getRequest()->getSession();
		$login_user_id = $session->read("Auth.User.user_id");
		$login_agency_id= $session->read("Auth.User.agency_id");
        $Contacts = TableRegistry::getTableLocator()->get('Contacts');
        $ContactPhoneNumbers = TableRegistry::getTableLocator()->get('ContactPhoneNumbers');
        $ContactEmails = TableRegistry::getTableLocator()->get('ContactEmails');
        $ContactAdditionalInsuredPolicyRelation = TableRegistry::getTableLocator()->get('ContactAdditionalInsuredPolicyRelation');
        $additional_data = array();
        $primary_contact_id = '';
        $insured_contact_id = '';
        $do_not_contact = 0;
        $is_existing_contact = $objectData['is_existing_contact'];

        //SEcondary_contact_selected_id is the id of secondary contact. This is done after changing the dropdown to autocomplete.
        $secondaryPolicies = !empty($objectData['selectedPolicies']) ? $objectData['selectedPolicies'] : [];
         if(!empty($secondaryPolicies)){
          $secondaryPolicies =explode(",",$secondaryPolicies);
        }
        $primary_contact_lead_type = "";
        if (!empty($objectData['primary_contact_id'])) {
             $primary_contact_id = $objectData['primary_contact_id'];
             //primary contact details
             $primary_contact_details = $Contacts->contactDetails($primary_contact_id);
             //set secondary contact lead type as per primary contact
             if($primary_contact_details['lead_type'] == _CONTACT_TYPE_CLIENT){
                $primary_contact_lead_type =  _CONTACT_TYPE_CLIENT;
             }else{
                $primary_contact_lead_type = _CONTACT_TYPE_LEAD;
             }
             //
             $additional_data['contact_id'] = $primary_contact_id;
        }

        if (!empty($objectData['secondary_contact_relationship_select'])) {
             $additional_data['relationship_with_contact'] = $objectData['secondary_contact_relationship_select'];
        }
        if (!empty($objectData['secondary_contact_status_select'])) {
             $additional_data['insured_contact_status'] = $objectData['secondary_contact_status_select'];
        }
        if (!empty($objectData['secondary_do_not_contact'])) {
            $do_not_contact=$objectData['secondary_do_not_contact'];
            if($do_not_contact == 1){
                $additional_data['do_not_contact'] = 1;
               // $data['do_not_contact'] = 1;
            }
        }

        if (!empty($objectData['secondary_contact_select'])) {
            $objectData['secondary_contact_select'] =  $objectData['secondary_contact_selected_id'];
             $insured_contact_id = $objectData['secondary_contact_select'];
        }

        if($is_existing_contact == 1)
        {
           //$data['additional_insured_flag']=_STATUS_TRUE;

           $contact_text_phone = $objectData['secondary_contact_phone'];
           $patterns = array('/\-/','/\(/','/\)/','/\+/','/\ /');
           $phone = preg_replace($patterns,'',$contact_text_phone);
           if(!empty($objectData['secondary_email_type_select'])){
                $data['email_type'] = $objectData['secondary_email_type_select'];
           }
           $data['email'] = $objectData['secondary_contact_emails'];
           $data['phone'] = $phone;
           if(!empty($secondaryPolicies)){
             //$data['lead_type']= _CONTACT_TYPE_CLIENT;
           }

          //echo "<pre>"; print_r($data); exit;
           $data['lead_type'] = $primary_contact_lead_type;
		   if(!empty($objectData['best_time_to_reach'])){
			$data['best_time_to_reach'] = $objectData['best_time_to_reach'];
		   }
           $contact =$Contacts->get($insured_contact_id);
           $contact =$Contacts->patchEntity($contact, $data);
            $Contacts->save($contact);
           $insured_contact_id = $contact->id;
        }
        else
        {
            //new contact
            $data = [];

            /********* Added by Ajay Gupta ***************/

            if(!empty($objectData['is_address_same'])){
                $data['mailing_address_type'] = $objectData['is_address_same'];

            }

            if(!empty($objectData['secondary_contact_address_one'])){
                $data['address'] = $objectData['secondary_contact_address_one'];
            }

            if(!empty($objectData['secondary_contact_address_two'])){
                $data['address_line_2'] = $objectData['secondary_contact_address_two'];
            }

            if(!empty($objectData['secondary_contact_city'])){
                $data['city'] = $objectData['secondary_contact_city'];
            }

            if(!empty($objectData['secondary_contact_state'])){
                $data['state_id'] = $objectData['secondary_contact_state'];
            }
            if(!empty($objectData['secondary_contact_zip'])){
                $data['zip'] = $objectData['secondary_contact_zip'];
            }
            /************* End Code Ajay Gupta *************/
            if(!empty($objectData['seccontact_text_birth']))
            {
                $contact_dob = $objectData['seccontact_text_birth'];
                $contact_dob = !empty($contact_dob) ? date("Y-m-d",strtotime($contact_dob)) : "";
                $data['birth_date'] = $contact_dob;
            }

            $data['additional_insured_flag']=_STATUS_TRUE;
            $data['lead_type'] = $primary_contact_lead_type;

            //Additional Insured Flag Will Always Be True If Contact Is Added As Secondary Contact
            if($do_not_contact == 1){
                $data['do_not_contact'] = 1;
            }
            if(!empty($objectData['secondary_contact_phone'])){
                $secondary_contact_phone = $objectData['secondary_contact_phone'];
                $patterns = array('/\-/','/\(/','/\)/','/\+/','/\ /');
                $phone = preg_replace($patterns,'',$secondary_contact_phone);
                $data['phone'] = $phone;
            }

            if(!empty($objectData['secondary_email_type_select'])){
                $data['email_type'] = $objectData['secondary_email_type_select'];
            }
            if(!empty($objectData['secondary_contact_emails'])){
                 $data['email'] = $objectData['secondary_contact_emails'];
            }
            if(!empty($objectData['secondary_contact_first_name'])){
                 $data['first_name'] = $objectData['secondary_contact_first_name'];
            }
            if(!empty($objectData['secondary_contact_last_name'])){
                 $data['last_name'] = $objectData['secondary_contact_last_name'];
            }
			if(!empty($objectData['secondary_contact_middle_name'])){
				$data['middle_name'] = $objectData['secondary_contact_middle_name'];
		   	}

			if(!empty($objectData['secondary_contact_preferred_name'])){
				$data['preferred_name'] = $objectData['secondary_contact_preferred_name'];
		   	}
			if(!empty($objectData['best_time_to_reach'])){
				$data['best_time_to_reach'] = $objectData['best_time_to_reach'];
			}
			if(!empty($objectData['secondary_contact_suffix'])){
				$data['suffix'] = $objectData['secondary_contact_suffix'];
			}
            $data['agency_id'] = $login_agency_id;
            $data['user_id'] = $login_user_id;
            $result_client_add = $Contacts->newEntity();
            $result_client_add = $Contacts->patchEntity($result_client_add,$data);
            $result_client_add = $Contacts->save($result_client_add);
            $client_id = $result_client_add->id;
            $additional_data['additional_insured_data'] = $client_id;
            $insured_contact_id = $client_id;
        }

        if(!empty($objectData['phone_number_value']))
        {
                $contact_phone_number_type = $objectData['phone_number_type'];
                $contact_phone_number_value = $objectData['phone_number_value'];
                //$updatePhoneNumber = $this->ContactPhoneNumbers->updateAll(['status' =>_ID_STATUS_DELETED],['contact_id' => $insured_contact_id]);
                //if($updatePhoneNumber){
                    for( $i = 0; $i < count($contact_phone_number_value); $i++)
                    {
                        if(!empty($contact_phone_number_value[$i]))
                        {
                            $contactPhones=[];
                            $contactPhones['phone_number_type'] = $contact_phone_number_type[$i];
                            $patterns = array('/\-/','/\(/','/\)/','/\+/','/\ /');
                            $contactPhones['phone_number_value'] = preg_replace($patterns,'',$contact_phone_number_value[$i]);
                            $contactPhones['contact_id'] = (int) $insured_contact_id;
                            $contactPhoneNumbers = $ContactPhoneNumbers->newEntity();
                            $contactPhoneNumbers = $ContactPhoneNumbers->patchEntity($contactPhoneNumbers,$contactPhones);
                            $ContactPhoneNumbers->save($contactPhoneNumbers);

                        }
                    }
                //}
        }
        //end
        if(!empty($objectData['contact_email_value']))
        {
            /*$contact_phone_number_type = $objectData['phone_number_type'];*/
            $contact_email_value = $objectData['contact_email_value'];
            $contact_email_type = $objectData['contact_email_type'];
            //$updateEmails = $this->ContactEmails->updateAll(['status' =>_ID_STATUS_DELETED],['contact_id' => $insured_contact_id]);
            for( $i = 0; $i < count($contact_email_value); $i++)
            {
                if(!empty($contact_email_value[$i]))
                {
                    $emails = [];
                    $emails['email'] = $contact_email_value[$i];
                    $emails['email_type'] = $contact_email_type[$i];
                    $emails['contact_id'] = (int) $insured_contact_id;
                    $contactEmailNumbers = $ContactEmails->newEntity();
                    $contactEmailNumbers = $ContactEmails->patchEntity($contactEmailNumbers,$emails);
                    $contactEmailNumbers = $ContactEmails->save($contactEmailNumbers);
                }
            }
        }
       //Update Policies
        if(!empty($secondaryPolicies))
        {
            /* If Secondary User Already Exist Without Policy. Delete It */
             $checkEmptyOpportunity = $ContactAdditionalInsuredPolicyRelation->find()->where(['contact_id'=>$primary_contact_id,'additional_insured_contact_id'=>$insured_contact_id,'contact_opportunity_id IS'=>NULL])->first();
             if(!empty($checkEmptyOpportunity)){
               $DeleteEmptyEntry = $ContactAdditionalInsuredPolicyRelation->delete($checkEmptyOpportunity);
             }


          foreach($secondaryPolicies as $policyId){
             $checkData = $ContactAdditionalInsuredPolicyRelation->find()->where(['contact_opportunity_id'=>$policyId,'contact_id'=>$primary_contact_id,'additional_insured_contact_id'=>$insured_contact_id,'status'=>_ID_STATUS_ACTIVE])->toArray();
             if(empty($checkData)){
               $additional_data['contact_opportunity_id'] = $policyId;
               $additional_insured_data = $ContactAdditionalInsuredPolicyRelation->newEntity();
               $additional_data['additional_insured_contact_id'] = $insured_contact_id;
               $additional_insured_data = $ContactAdditionalInsuredPolicyRelation->patchEntity($additional_insured_data,$additional_data);
               $ContactAdditionalInsuredPolicyRelation->save($additional_insured_data);

             }
          }


        }
        else
        {
           $additional_data['contact_opportunity_id'] = '';
           $additional_insured_data = $ContactAdditionalInsuredPolicyRelation->newEntity();
           $additional_data['additional_insured_contact_id']=$insured_contact_id;
           $additional_insured_data = $ContactAdditionalInsuredPolicyRelation->patchEntity($additional_insured_data,$additional_data);
           $ContactAdditionalInsuredPolicyRelation->save($additional_insured_data);


        }

       $successData =[];
       $successData['response'] = 200;
       $successData['message'] = "success";
       return json_encode($successData);

    }
	public function verifyemailForPrimary($objectData) {
        $session = Router::getRequest()->getSession();
		$login_user_id = $session->read("Auth.User.user_id");
		$login_agency_id= $session->read("Auth.User.agency_id");
        $email = $objectData['secondary_contact_emails'];
		$Contacts = TableRegistry::getTableLocator()->get('Contacts');
        if(isset($objectData['primaryContactId']) && !empty($objectData['primaryContactId'])){
            $user = $Contacts->find()->select(['Contacts.id'])->where(['Contacts.agency_id' =>$login_agency_id, 'Contacts.email' => strtolower($email),'Contacts.id !=' => $objectData['primaryContactId'],'Contacts.status !='=>_ID_STATUS_DELETED])->first();
          }
        if (!empty($user)) {
            $successData['response'] = false;
        } else {
            $successData['response'] = true;
        }
		return json_encode($successData);
    }

     /**
     *get contact additional phone numbers
     */
    public function getContactAdditionalNumber($contactId)
    {
        try
        {
			//$myfile = fopen(ROOT."/logs/vueCommunication.log", "a") or die("Unable to open file!");
            $session = Router::getRequest()->getSession();
            $login_agency_id= $session->read("Auth.User.agency_id");
            $Contacts = TableRegistry::getTableLocator()->get('Contacts');
			$ContactPhoneNumbers = TableRegistry::getTableLocator()->get('ContactPhoneNumbers');
			$PhoneNumbersOptInOutStatus = TableRegistry::getTableLocator()->get('PhoneNumbersOptInOutStatus');
			$contactNumbers = [];
           
			if(!empty($contactId))
			{
				$contact_id = $contactId;

				$getAllPhoneNumbersArr = $ContactPhoneNumbers->getAllPhoneNumbers($contact_id);
                $i = 0;
				if(isset($getAllPhoneNumbersArr) && !empty($getAllPhoneNumbersArr)){

					foreach ($getAllPhoneNumbersArr as $phonenumbers) {
                            //if($phonenumbers['phone_number_type'] != _ID_LANDLINE){
                            $phone_opt_in_out= 0;

                            $phone = CommonFunctions::format_phone_us($phonenumbers['phone_number_value']);
                            $phone_opt_in_status = $PhoneNumbersOptInOutStatus->getAllOptInOptOutPhone($login_agency_id,$phone);
                            if($phone_opt_in_status['status'] == _STATUS_TRUE)
                            {
                                $phone_opt_in_out = 1;
                            }

                            $contactNumbers[$i]['id'] = $phonenumbers['id'];
							$contactNumbers[$i]['phoneNumber'] = $phone;
                            $contactNumbers[$i]['numberType'] = $phonenumbers['phone_number_type'];
                            $contactNumbers[$i]['ext'] = $phonenumbers['phone_ext'];
                            $contactNumbers[$i]['is_sms_enable'] = $phone_opt_in_out;
                            $getEntityDefAdditional = getEntityDef($err,_PHONE_NUMBER_TYPE,$phonenumbers['phone_number_type']);
                            
                            if(strtolower($getEntityDefAdditional) == 'mobile')
                            {
                                $contactNumbers[$i]['numberTypeText'] = 'Cell';
                            }else{
                                $contactNumbers[$i]['numberTypeText'] = $getEntityDefAdditional;
                            }
                        //}
						$i++;
					}
				}
			}
			$return['Contacts.getContactAdditionalNumber'] = [
                $contactId => $contactNumbers
            ];
            return $return;
        }catch (\Exception $e) {

            $txt = date('Y-m-d H:i:s').' :: getContactAdditionalNumber Error- '.$e->getMessage();
			//fwrite($myfile,$txt.PHP_EOL);
        }
    }


    /**
     *mark contact additional phone deleted.
     */
    public function markContactPhoneDeleted($objectData)
    {
        $ContactPhoneNumbers = TableRegistry::getTableLocator()->get('ContactPhoneNumbers');

		if($ContactPhoneNumbers->updateAll([['status' => _ID_STATUS_DELETED]], ['id' => $objectData['phoneNumberId']]))
		{
			$response = json_encode(array('status' => _ID_SUCCESS,'message' => 'Phone Number deleted Successfully!'));
		}
		else
		{
			$response = json_encode(array('status' => _ID_FAILED,'message'=>"Something went wrong!. Don't able to delete phone number."));
		}
		return $response;
    }

    public function getContactPhoneNumbersOptInOutStatus($contactId)
    {
        $session = Router::getRequest()->getSession();
        $login_agency_id= $session->read("Auth.User.agency_id");
        $PhoneNumbersOptInOutStatus = TableRegistry::getTableLocator()->get('PhoneNumbersOptInOutStatus');
        $Contacts = TableRegistry::getTableLocator()->get('Contacts');
		$response = _ID_FAILED;
        if($contactId)
        {
            $ContacDetails = $Contacts->get($contactId);
            if(isset($ContacDetails['phone']) && $ContacDetails['phone'] != '')
            {
                $phoneNumberValue = $ContacDetails['phone'];
                $phoneOptInStatus = $PhoneNumbersOptInOutStatus->getAllOptInOptOutPhone($login_agency_id,$phoneNumberValue);
                if($phoneOptInStatus['status'] == _STATUS_TRUE)
                {
                    $response = _ID_SUCCESS;
                }
                else
                {
                    $response = _ID_FAILED;
                }

			}
		}
		$return['Contacts.getContactPhoneNumbersOptInOutStatus'] = [
            $contactId => $response
        ];
        return $return;

    }
	public function deleteSecondaryContact($data)
    {  
		$Contacts = TableRegistry::getTableLocator()->get('Contacts');
		$ContactAdditionalInsuredPolicyRelation = TableRegistry::getTableLocator()->get('ContactAdditionalInsuredPolicyRelation');
		$id= '';
		$additionalInsuredContactId ='';
        $primaryContactId = '';
        if($data['id'] != '')
		{
			$id = $data['id'];
		}
        if($data['additionalInsuredContactId'] != '')
		{
			$additionalInsuredContactId = $data['additionalInsuredContactId'];
		}
        if($data['primaryContactId'] != '')
		{
			$primaryContactId = $data['primaryContactId'];
		}
        $contact = $Contacts->get($additionalInsuredContactId);
        if (isset($contact) && !empty($contact))
        {   
			if(isset($primaryContactId) && $primaryContactId != '')
			{
                $contactAdditionalUpdate = $ContactAdditionalInsuredPolicyRelation->updateAll(['status' => _ID_STATUS_DELETED],['contact_id' => $primaryContactId, 'additional_insured_contact_id' => $additionalInsuredContactId, 'status' => _ID_STATUS_ACTIVE]);
                $contactAdditionalUpdateData = $ContactAdditionalInsuredPolicyRelation->updateAll(['status' => _ID_STATUS_DELETED],['contact_id' => $additionalInsuredContactId, 'additional_insured_contact_id' => $primaryContactId, 'status' => _ID_STATUS_ACTIVE]);
				if($contactAdditionalUpdate == _STATUS_TRUE)
                {
                    $response = json_encode(array('status' => _ID_SUCCESS,'message' => 'Secondary contact remove successfully!'));
                } 
                else if(isset($id) && $id != '')
                { 
                    $contactAdditionalUpdate = $ContactAdditionalInsuredPolicyRelation->updateAll(['status' => _ID_STATUS_DELETED], ['contact_id' => $id, 'additional_insured_contact_id' => $additionalInsuredContactId, 'status' => _ID_STATUS_ACTIVE]);
                    $response = json_encode(array('status' => _ID_SUCCESS,'message' => 'Secondary contact remove successfully!'));
                }
                if($primaryContactId == $additionalInsuredContactId)
                {
                    $contact = $Contacts->get($id);
                    if($contact->additional_insured_flag == _ID_SUCCESS)
                    {
                        $personalContacts = $ContactAdditionalInsuredPolicyRelation->getLinkedPrimaryContacts($id, _ID_STATUS_ACTIVE);
                        $personalContactsSec = $ContactAdditionalInsuredPolicyRelation->getLinkedOtherPrimaryContacts($id, _ID_STATUS_ACTIVE);
                        if(empty($personalContacts))
                        {
                            $personalContacts = $personalContactsSec;
                        }
                        else if(!empty($personalContacts) && !empty($personalContactsSec))
                        {
                            $personalContacts = array_merge($personalContacts, $personalContactsSec);
                        }
                        if(empty($personalContacts))
                        {
                            $personalContacts = $ContactAdditionalInsuredPolicyRelation->getLinkedPrimaryContactsToSecondary($id, _ID_STATUS_ACTIVE);
                        }
                    }
                    if(count($personalContacts) == _ID_FAILED)
			        {
				        $Contacts->updateAll(['additional_insured_flag' => NULL],['id' => $id]);
			        }   
                }
                else
                {
                    $contact = $Contacts->get($primaryContactId);
                    if($contact->additional_insured_flag == _ID_SUCCESS)
                    {
                        $personalContacts = $ContactAdditionalInsuredPolicyRelation->getLinkedPrimaryContacts($primaryContactId, _ID_STATUS_ACTIVE);
                        $personalContactsSec = $ContactAdditionalInsuredPolicyRelation->getLinkedOtherPrimaryContacts($primaryContactId, _ID_STATUS_ACTIVE);
                        if(empty($personalContacts))
                        {
                            $personalContacts = $personalContactsSec;
                        }
                        else if(!empty($personalContacts) && !empty($personalContactsSec))
                        {
                            $personalContacts = array_merge($personalContacts, $personalContactsSec);
                        }
                        if(empty($personalContacts))
                        {
                            $personalContacts = $ContactAdditionalInsuredPolicyRelation->getLinkedPrimaryContactsToSecondary($primaryContactId, _ID_STATUS_ACTIVE);
                        }
                    }
                    if(count($personalContacts) == _ID_FAILED)
			        {
				        $Contacts->updateAll(['additional_insured_flag' => NULL],['id' => $primaryContactId]);
			        }    
                }
			}
			else
			{
				$response = json_encode(array('status' => _ID_FAILED));
			}
        }else{
		   $response = json_encode(array('status' => _ID_FAILED));
        }
		return $response;
    }

	//--------- SMS reply in beta ----------
	public function sendReplySms($objectData)
    {
		$contact_id_n   =   $objectData['contact_id_n'];
        $pre_record_id  =   $objectData['pre_record_id'];
		$session = Router::getRequest()->getSession();
		$login_agency_id = $session->read('Auth.User.agency_id');
		$Agency = TableRegistry::getTableLocator()->get('Agency');
		$agentArr = $Agency->agencyDetails($login_agency_id);
        $id             =   $agentArr['id'];
		$login_user_id= $session->read("Auth.User.user_id");
		$Users = TableRegistry::getTableLocator()->get('Users');
		$userDetail = $Users->userDetails($login_user_id);
		$username = (isset($userDetail['first_name']) ? $userDetail['first_name'] :'').' '.(isset($userDetail['last_name']) ? $userDetail['last_name'] :'');
		$UsStates = TableRegistry::getTableLocator()->get('UsStates');
		$Contacts = TableRegistry::getTableLocator()->get('Contacts');
		$ContactCustomFields = TableRegistry::getTableLocator()->get('ContactCustomFields');
		$ReferralPartnerUserContacts = TableRegistry::getTableLocator()->get('ReferralPartnerUserContacts');
		$ReferredContact = TableRegistry::getTableLocator()->get('ReferredContact');
		$ContactPolicyLostDetails = TableRegistry::getTableLocator()->get('ContactPolicyLostDetails');
		$PhoneNumbersOptInOutStatus = TableRegistry::getTableLocator()->get('PhoneNumbersOptInOutStatus');
		$CommunicationReply = TableRegistry::getTableLocator()->get('CommunicationReply');
        $ContactCommunications  = TableRegistry::getTableLocator()->get('ContactCommunications');


		
        $smsto    = $objectData['reply_sms_number'];
        $smstext  = addslashes($objectData['reply_sms_text']);
        $textSms  = $objectData['reply_sms_text'];
        //$smsto    =  '+91-9034233202';
        // $smstext  =  'test sms';

        if(isset($agentArr) && !empty($agentArr))
        {
            $agencyName = !empty($agentArr['company'])?$agentArr['company']:"";
            $agencyStreetAddress = !empty($agentArr['address'])?$agentArr['address']:"";
            $agencyReviewLink = !empty($agentArr['google_my_business_link'])?$agentArr['google_my_business_link']:"";
            $agencyCity = !empty($agentArr['city'])?$agentArr['city']:"";
            $agencyStateId = !empty($agentArr['us_state_id'])?$agentArr['us_state_id']:"";
            $agencyState = "";
            if(!empty($agencyStateId))
            {
                $agencyStateDetail = $UsStates->get($agencyStateId);
                $agencyState = $agencyStateDetail->name;
            }
            $agencyEmail = !empty($agentArr['email'])?$agentArr['email']:"";
            $agencyPhone = !empty($agentArr['phone'])?$agentArr['phone']:"";
            $agencyLicense = !empty($agentArr['license'])?$agentArr['license']:"";
            $agencyProposalLink = "";
            if(isset($agentArr['allow_video_proposal_version']) && $agentArr['allow_video_proposal_version']==_QUOTE_SENT_VIDEO_PROPOSAL && !empty($agentArr['video_proposal_link'])){
                $agencyProposalLink = $agentArr['video_proposal_link'];
            }
        }



        if(isset($userDetail) && !empty($userDetail))
        {
            $agentName = $userDetail['first_name'].' '.$userDetail['last_name'];
            $agentEmail = !empty($userDetail['email'])?$userDetail['email']:"";
            $agentPhone = !empty($userDetail['phone'])?$userDetail['phone']:"";
            $agentFirstName = !empty($userDetail['first_name'])?$userDetail['first_name']:"";
            $agentLastName = !empty($userDetail['last_name'])?$userDetail['last_name']:"";
            $agent_calendar_link = !empty($userDetail['calendar_link'])?$userDetail['calendar_link']:"";
        }



        $contactDetails = $Contacts->contactDetailsAdditionalInfo($contact_id_n);
        if(isset($contactDetails) && !empty($contactDetails))
        {
            $contactName =$contactDetails['first_name'].' '.$contactDetails['middle_name'].' '.$contactDetails['last_name'];
            $contactEmail = !empty($contactDetails['email'])?$contactDetails['email']:"";
            $contactPhone = !empty($contactDetails['phone'])?$contactDetails['phone']:"";
            $contactFirstName = !empty($contactDetails['first_name'])?$contactDetails['first_name']:"";
            //$contactFirstName = (!empty($contactDetails['preferred_name'])) ? $contactDetails['preferred_name'] : ((!empty($contactDetails['first_name'])) ? $contactDetails['first_name'] : '');
            $contactMiddleName = !empty($contactDetails['middle_name'])?$contactDetails['middle_name']:"";
            $contactLastName = !empty($contactDetails['last_name'])?$contactDetails['last_name']:"";
            $contactPreferredName = !empty($contactDetails['preferred_name'])?$contactDetails['preferred_name']:"";
            $contactBirthdate = !empty($contactDetails['birth_date'])?date('M d, Y',strtotime($contactDetails['birth_date'])):"";
            $contactAddress1=!empty($contactDetails['address'])?$contactDetails['address']:"";
            $contactAddress2=!empty($contactDetails['address_line_2'])?$contactDetails['address_line_2']:"";
            $contactCity=!empty($contactDetails['city'])?$contactDetails['city']:"";
            $contactState = "";
            if(isset($contactDetails['state_id']) && !empty($contactDetails['state_id'])){
                $contactStateNameById = $UsStates->stateNameById($contactDetails['state_id']);
                if(isset($contactStateNameById['name']) && !empty($contactStateNameById['name'])){
                    $contactState = $contactStateNameById['name'];
                }
            }
            $contactZip=!empty($contactDetails['zip'])?$contactDetails['zip']:"";
            $maritalStatus = "";
            if(isset($contactDetails['marital_status']) && !empty($contactDetails['marital_status'])){
                $getEntityDef=getEntityDef($err,_MARITAL_STATUS,$contactDetails['marital_status']);
                $maritalStatus = $getEntityDef;
            }
            $spouse_first_name = "";
            $spouse_last_name = "";
            $driver_license_number = "";
            if(isset($contactDetails['contact_details']) && !empty($contactDetails['contact_details']))
            {
                if(isset($contactDetails['contact_details'][0]['spouse_first_name']) && !empty($contactDetails['contact_details'][0]['spouse_first_name'])){
                    $spouse_first_name = $contactDetails['contact_details'][0]['spouse_first_name'];
                }
                if(isset($contactDetails['contact_details'][0]['spouse_last_name']) && !empty($contactDetails['contact_details'][0]['spouse_last_name'])){
                    $spouse_last_name = $contactDetails['contact_details'][0]['spouse_last_name'];
                }
                if(isset($contactDetails['contact_details'][0]['driver_license_number']) && !empty($contactDetails['contact_details'][0]['driver_license_number']))
                {
                    $driver_license_number = $contactDetails['contact_details'][0]['driver_license_number'];
                    if(isset($contactDetails['contact_details'][0]['license_state_id']) && !empty($contactDetails['contact_details'][0]['license_state_id'])){
                        if($contactDetails['contact_details'][0]['license_state_id'] == _LICENSE_STATE_OTHER && $contactDetails['contact_details'][0]['other_state_name'] != "")
                        {
                            $driver_license_number  = $driver_license_number.' | '.$contactDetails['contact_details'][0]['other_state_name'];
                        }
                        elseif ($contactDetails['contact_details'][0]['license_state_id'] == _LICENSE_STATE_INTL)
                        {
                           $driver_license_number  = $driver_license_number.' | INTL';
                        }
                        elseif ($contactDetails['contact_details'][0]['license_state_id'] == _LICENSE_STATE_NONE)
                        {
                            $driver_license_number  = $driver_license_number;
                        }
                        else
                        {
                            $contactlicenseNameById = $UsStates->stateNameById($contactDetails['contact_details'][0]['license_state_id']);
                            if(isset($contactlicenseNameById['name']) && !empty($contactlicenseNameById['name'])){
                                $driver_license_number  = $driver_license_number.' | '.$contactlicenseNameById['name'];
                            }
                        }
                    }
                }
            }
        }



        $contactCustomFields = $ContactCustomFields->getAllCustomFieldsByContact($contact_id_n);
        $custom_fields_array = [];
        if(isset($contactCustomFields) && !empty($contactCustomFields))
        {
            foreach ($contactCustomFields as $key => $value)
            {
                // if(isset($value['contact_custom_fields']) && !empty($value['contact_custom_fields'])){
                //     foreach ($value['contact_custom_fields'] as $key => $value)
                //     {
                        if(isset($value['field_value']) && !empty($value['field_value']) && $value['field_value'] != '--'){
                            $field_value = $value['field_value'];
                        }else{
                            $field_value = "";
                        }
                        $custom_fields_array['{custom'.'.'.$value['field_label'].'}'] = $field_value;
                    //}
                //}
            }
        }

        //client referral link
        $client_referral_link="";
        if(isset($agentArr) && !empty($agentArr) && isset($userDetail) && !empty($userDetail) && isset($contactDetails) && !empty($contactDetails))
        {
            $client_referral_link = _SITE_PROTOCOLE.$agentArr['sub_domain'].'.'._BETTER_REFERRAL_DOMAIN.'/clientreferral/'.base64_encode($userDetail['id']).'/'.base64_encode($contactDetails['id']);
        }
        //referral partner merge fields
        //contact referral partner detail
        $referral_partner_user_email = "";
        $contact_referral_partner_user_detail = $ReferralPartnerUserContacts->getContactReferralPartnerUserDetailWithPrimaryEmail($contact_id_n);
        if(isset($contact_referral_partner_user_detail['referral_partner_user']['referral_partner_user_email_phone'][0]['email_phone']) && !empty($contact_referral_partner_user_detail['referral_partner_user']['referral_partner_user_email_phone'][0]['email_phone']))
        {
            $referral_partner_user_email = $contact_referral_partner_user_detail['referral_partner_user']['referral_partner_user_email_phone'][0]['email_phone'];
        }
        $referral_partner_user_id="";
        $referral_partner_user_do_not_contact=0;
        $referral_partner_user_first_name="";
        $referral_partner_user_last_name="";
        $referral_partner_company="";
        if(isset($contact_referral_partner_user_detail['referral_partner_user']) && !empty($contact_referral_partner_user_detail['referral_partner_user']))
        {
            $referral_partner_user_id = $contact_referral_partner_user_detail['referral_partner_user']['id'];
            $referral_partner_user_do_not_contact = $contact_referral_partner_user_detail['referral_partner_user']['do_not_contact'];
            if(isset($contact_referral_partner_user_detail['referral_partner_user']['first_name']) && !empty($contact_referral_partner_user_detail['referral_partner_user']['first_name']))
            {
                $referral_partner_user_first_name = $contact_referral_partner_user_detail['referral_partner_user']['first_name'];
            }
            if(isset($contact_referral_partner_user_detail['referral_partner_user']['last_name']) && !empty($contact_referral_partner_user_detail['referral_partner_user']['last_name']))
            {
                $referral_partner_user_last_name = $contact_referral_partner_user_detail['referral_partner_user']['last_name'];
            }
            if(isset($contact_referral_partner_user_detail['referral_partner']['company']) && !empty($contact_referral_partner_user_detail['referral_partner']['company']))
            {
                $referral_partner_company = $contact_referral_partner_user_detail['referral_partner']['company'];
            }
        }

        //contact referral partner phone detail
        $referral_partner_user_phone = "";
        $contact_referral_partner_user_detail_phone = $ReferralPartnerUserContacts->getContactReferralPartnerUserDetailWithPrimaryPhone($contact_id_n);
        if(isset($contact_referral_partner_user_detail_phone['referral_partner_user']['referral_partner_user_email_phone'][0]['email_phone']) && !empty($contact_referral_partner_user_detail_phone['referral_partner_user']['referral_partner_user_email_phone'][0]['email_phone']))
        {
            $referral_partner_user_phone = $contact_referral_partner_user_detail_phone['referral_partner_user']['referral_partner_user_email_phone'][0]['email_phone'];
        }

        //client referral merge fields
        $client_referral_first_name = "";
        $client_referral_last_name = "";
        $client_referral_email = "";
        $client_referral_phone = "";
        $client_referrer_details = $ReferredContact->getReferrerContactByReferralContactId($contact_id_n);
        if(isset($client_referrer_details) && !empty($client_referrer_details) && isset($client_referrer_details['contact']) && !empty($client_referrer_details['contact']))
        {
            if(isset($client_referrer_details['contact']['first_name']) && !empty($client_referrer_details['contact']['first_name']))
            {
                $client_referral_first_name = $client_referrer_details['contact']['first_name'];
            }
            if(isset($client_referrer_details['contact']['last_name']) && !empty($client_referrer_details['contact']['last_name']))
            {
                $client_referral_last_name = $client_referrer_details['contact']['last_name'];
            }
            if(isset($client_referrer_details['contact']['email']) && !empty($client_referrer_details['contact']['email']))
            {
                $client_referral_email = $client_referrer_details['contact']['email'];
            }
            if(isset($client_referrer_details['contact']['phone']) && !empty($client_referrer_details['contact']['phone']))
            {
                $client_referral_phone = $client_referrer_details['contact']['phone'];
            }
        }
        else if(isset($client_referrer_details) && !empty($client_referrer_details))
        {
            if(isset($client_referrer_details['referrer_first_name']) && !empty($client_referrer_details['referrer_first_name']))
            {
                $client_referral_first_name = $client_referrer_details['referrer_first_name'];
            }
            if(isset($client_referrer_details['referrer_last_name']) && !empty($client_referrer_details['referrer_last_name']))
            {
                $client_referral_last_name = $client_referrer_details['referrer_last_name'];
            }
            if(isset($client_referrer_details['referrer_email']) && !empty($client_referrer_details['referrer_email']))
            {
                $client_referral_email = $client_referrer_details['referrer_email'];
            }
            if(isset($client_referrer_details['referrer_phone']) && !empty($client_referrer_details['referrer_phone']))
            {
                $client_referral_phone = $client_referrer_details['referrer_phone'];
            }
        }
		// added lost carrier for last policy cancelled
		$lost_carrier = '';
		$lost_carrier_data = $ContactPolicyLostDetails->getContactLostPolicyCarrier($contact_id_n);
		if(isset($lost_carrier_data) && !empty($lost_carrier_data)){
			$lost_carrier = $lost_carrier_data['carrier_name'];
		}
        $mergeFieldArray = array(
            '{agency.name}' => ucwords($agencyName),
            '{agency.streetAddress}' => $agencyStreetAddress,
             '{agency.reviewlink}' => $agencyReviewLink,
            '{agency.city}' => $agencyCity,
            '{agency.state}' => $agencyState,
            '{agency.email}' => $agencyEmail,
            '{agency.phone}' => $agencyPhone,
            '{agency.license}' => $agencyLicense,
            '{agent.name}' => ucwords($agentName),
            '{agent.email}' => $agentEmail,
            '{agent.phone}' => $agentPhone,
            '{contact.name}' => $contactName,
            '{contact.email}' => $contactEmail,
            '{contact.phone}' => $contactPhone,
            '{contact.firstName}' => $contactFirstName,
			'{contact.middleName}' => $contactMiddleName,
            '{contact.lastName}' => $contactLastName,
            '{contact.preferredName}' => $contactPreferredName,
            '{agent.firstName}' => $agentFirstName,
            '{agent.lastName}' => $agentLastName,
            '{contact.firstName}' => $contactFirstName,
            '{agent.calendarLink}'=>$agent_calendar_link,
            '{contact.birthdate}'=>$contactBirthdate,
            '{agency.proposalLink}'=>$agencyProposalLink,
            '{contact.address1}'=>$contactAddress1,
            '{contact.address2}'=>$contactAddress2,
            '{contact.city}'=>$contactCity,
            '{contact.state}'=>$contactState,
            '{contact.zip}'=>$contactZip,
            '{contact.maritalStatus}'=>$maritalStatus,
            '{contact.spouseFirstName}'=>$spouse_first_name,
            '{contact.spouseLastName}'=>$spouse_last_name,
            '{contact.driversLicenseNumber}'=>$driver_license_number,
            '{clientReferralLink}'=>$client_referral_link,
            '{referralpartner.email}'=>$referral_partner_user_email,
            '{referralpartner.phone}'=>$referral_partner_user_phone,
            '{referralpartner.firstName}'=>$referral_partner_user_first_name,
            '{referralpartner.lastName}'=>$referral_partner_user_last_name,
            '{referralpartner.company}'=>$referral_partner_company,
            '{clientreferrer.firstName}'=>$client_referral_first_name,
            '{clientreferrer.lastName}'=>$client_referral_last_name,
            '{clientreferrer.email}'=>$client_referral_email,
            '{clientreferrer.phone}'=>$client_referral_phone,
			'{contact.lostCarrier}'=>$lost_carrier,
        );
        $finalContentArray = array_merge($custom_fields_array,$mergeFieldArray);
        if (is_array($finalContentArray) && !empty($finalContentArray)) {
            foreach ($finalContentArray as $key => $value) {
                if($key != '{agency.proposalLink}'){
                    $smstext = str_ireplace($key, $value, $smstext);
                    $textSms = str_ireplace($key, $value, $textSms);
                }
            }
        }

        if(isset($smsto) && isset($smstext)){
            $type = _COMMUNICATION_TYPE_SMS;
            $date = date('Y-m-d H:i:s');
            $subject        =   '';
            $toEmail        =   '';
            $mail_from      =   '';
            $fromName       =   $username;
            $mesagereply    =   '';

            if (stripos($smstext, '{agency.proposalLink}') !== false)
            {
                if(isset($agencyProposalLink)  && !empty($agencyProposalLink) ){
                    $smstext = str_ireplace('{agency.proposalLink}', $agencyProposalLink, $smstext);
                }else{
                    $smstext = str_ireplace('{agency.proposalLink}', '', $smstext);
                }

            }
            // Check Opt in status

            $additionalDetails = [
                'agencyId' => $id,
                'contact_id' => $contact_id_n,
                'sms_enabled' => $agentArr['sms_enabled']
            ];

			$optStatus = $PhoneNumbersOptInOutStatus->checkOptInOptOutStatus($id,$smsto);
            if($optStatus['status']==true){
            //if($contactDetails['is_sms_subscribe'] == _ID_STATUS_ACTIVE){
//                $communicationReplyData =  CommonFunctions::SmsCommunicationReplyData($id, $login_user_id, $pre_record_id , $contact_id_n, $type, $toEmail, $mail_from, $fromName, $subject,$textSms,$mesagereply,_COMMUNICATION_TYPE_OUT);
                $communicationReplyData =  CommonFunctions::saveSmsCommunicationReplyData($id, $login_user_id, $pre_record_id , $contact_id_n, $type, $toEmail, $mail_from, $fromName, $subject,$textSms,$mesagereply,_COMMUNICATION_TYPE_OUT, null, $smsto);
                $sendSms = TwilioSms::sendSms($smsto, $userDetail['phone'], $smstext,$agentArr['subaccount_sid'], $additionalDetails);
                if($sendSms){
                    $meassage=$sendSms['meassage'];
                    if($sendSms['getCode']==21211){
                        $meassage="The 'To' number is not a valid phone number.";
                    }
                    else
                    {
                        if(!empty($communicationReplyData) && $sendSms['status'])
                        {
                            $data = $ContactCommunications->updateAll(['sent_status' => _SENT_STATUS_DELIVERED, 'message_id' => $sendSms['messageServiceSid']],['id' => $communicationReplyData->id]);//update sent status to delivered
                        }
                        else
                        {
                            $communicationId = $communicationReplyData->id ?? '';
                            SlackNotifications::sendSmsError(" Contacts.php : sendReplySms Error :- agency_id = ". $id ." contact_id = ". $contact_id_n ." communication_id = ". $communicationId ." status :-". $sendSms['status'] . " message :- ".$sendSms['meassage'] ."getCode :-". $sendSms['getCode']);
                        }
                    }
                    echo json_encode(array('status' => $sendSms['status'],'message'=>$meassage));

                }
            }else{
                $meassage="SMS Services has stopped by this User";
                echo json_encode(array('status' => _ID_FAILED,'message'=>$meassage));
            }

        }
        else{
            echo json_encode(array('status' => _ID_FAILED,'message'=>''));
        }
        die;
    }

	public function getAgencyPersonalCustomFields($agencyId)
    {
        try
        {
            $AgencyCustomFields = TableRegistry::getTableLocator()->get('AgencyCustomFields');
			$allAgencyCustomFields = $AgencyCustomFields->customFieldsByPersonalCommercialAgencyId($agencyId,_POLICY_LINE_PERSONAL);
            $return['Contacts.getAgencyPersonalCustomFields'] = [
                $agencyId => $allAgencyCustomFields
            ];
            return $return;
        }
		catch (\Exception $e) 
		{

        }
    }


	// ---------- get agency all sms templates 
	public function getAgencyOneOffTemplatesByType($templateData){
        $session = Router::getRequest()->getSession();
		$login_agency_id = $session->read('Auth.User.agency_id');
        $login_agency_id=$session->read('Auth.User.agency_id');
        $AgencyOneOffTemplates = TableRegistry::getTableLocator()->get('AgencyOneOffTemplates');
        if(isset($templateData['template_type']) && !empty($templateData['template_type']))
        {
            $template_type = $templateData['template_type'];
            $agencyOneOffTemplates = $AgencyOneOffTemplates->getAgencyOneOffTemplatesByType($login_agency_id,$template_type);
            $agencyOneOffTemplatesListing = [];
            if(isset($agencyOneOffTemplates) && !empty($agencyOneOffTemplates))
            {
                $count=1;
                foreach ($agencyOneOffTemplates as $masterTemplate)
                {
                    $template_content = $masterTemplate['description'];
                    if(isset($template_content) && !empty($template_content)){
                        $description =  strip_tags($template_content);
                        $description = substr($description, 0, 20).'....';
                        if (stripos($description, '{user.logo}') !== false) {
                            $description = str_ireplace('{user.logo}', '', $description);
                        }
                        if (stripos($description, '{user.logo}') !== false) {
                            $description = str_ireplace('{user.logo}', '', $description);
                        }

                        if (stripos($description, '{agency.proposalLink}') !== false) {
                            $description = str_ireplace('{agency.proposalLink}', '', $description);
                        }
                    }else{
                        $description = 'N/A';
                    }

                    $agencyOneOffTemplatesListing[$count]['count'] = $count;
                    $agencyOneOffTemplatesListing[$count]['id'] = $masterTemplate['id'];
                    $agencyOneOffTemplatesListing[$count]['title'] = $masterTemplate['title'];
                    $agencyOneOffTemplatesListing[$count]['description'] = $description;
                    $agencyOneOffTemplatesListing[$count]['url'] = SITEURL.'agency-one-off-templates/edit/'.$masterTemplate['id'];
					$agencyOneOffTemplatesListing[$count]['template_type'] = $masterTemplate['template_type'];
					$agencyOneOffTemplatesListing[$count]['content'] = $template_content;
					$agencyOneOffTemplatesListing[$count]['subject'] = $masterTemplate['subject'];


                    $count++;
                } 
            }
            echo json_encode(array('status' => _ID_SUCCESS,'list'=>$agencyOneOffTemplatesListing)); 
        }
        else{

            echo json_encode(array('status' => _ID_FAILED));
        }
        die();

    }

	public static function getContactDetailsById($objectData)
    {
        $session = Router::getRequest()->getSession();
		$login_agency_id = $session->read('Auth.User.agency_id');
		$object = null;
		$objectMap = [
			'Contact' => ContactsQuickTable::class,
			'ContactDetails' => ContactDetailsQuickTable::class,
			'Virtual_ContactSummary' => Virtual_ContactSummariesQuickTable::class,
			'Virtual_PolicySummary' => Virtual_PolicySummariesQuickTable::class,
			'User' => UsersQuickTable::class,
			'PhoneNumbersOptInOutStatus' => PhoneNumbersOptInOutStatusQuickTable::class,
			'ReferralPartnerUserContact' => ReferralPartnerUserContactQuickTable::class,
			'ContactsMailingAddress' => ContactsMalingAddressQuickTable::class,
			'ContactNoteTypes' => Virtual_ContactNoteTypesQuickTable::class,

		];
		if(isset($objectMap[$objectData['objectName']])){

            try{
                $QuickTable = $objectMap[$objectData['objectName']];
                $object = $QuickTable::findById($objectData['objectId']);
                $objectAsArray=[];
                if($object != null) 
                {
                    $objectAsArray = is_array($object) ? $object : $object->toArray();
                    $NowcertsPushAgencyTokenTable = TableRegistry::getTableLocator()->get('NowcertsPushAgencyToken');
                    $nowcertsDetails = $NowcertsPushAgencyTokenTable->checkAccessTokenByAgencyId($login_agency_id);
                    if(!empty($nowcertsDetails))
                    {
                        $objectAsArray['nowcerts_data']['access_token'] = $nowcertsDetails['access_token'];
                        $objectAsArray['nowcerts_data']['expires_in'] = $nowcertsDetails['expires_in'];
                        $objectAsArray['nowcerts_data']['refresh_token'] = $nowcertsDetails['refresh_token'];
                        $objectAsArray['nowcerts_data']['token_issued_on'] = $nowcertsDetails['token_issued_on'];
                        $objectAsArray['nowcerts_data']['token_expires_on'] = $nowcertsDetails['token_expires_on'];
                    }
                    else 
                    {
                        $objectAsArray['nowcerts_data']['access_token'] = null;
                        $objectAsArray['nowcerts_data']['expires_in'] = null;
                        $objectAsArray['nowcerts_data']['refresh_token'] = null;
                        $objectAsArray['nowcerts_data']['token_issued_on'] = null;
                        $objectAsArray['nowcerts_data']['token_expires_on'] = null;
                    }
                    
                    $return[$objectData['objectName']] = [
                        $objectData['objectId'] => $objectAsArray
                    ];
                   
                    return $return;
                    
                }
                else 
                {
                    return null;
                }
            }catch (\Exception $e) {
                return date('Y-m-d H:i:s').' :: Logs Lising Error-- '.$e->getMessage();
            }
        }
	
    }

    public function deleteContact($contactId)
    {

        $session = Router::getRequest()->getSession();
	    $login_agency_id=$session->read('Auth.User.agency_id');
		$logged_in_user = $session->read("Auth.User");
		$Contacts = TableRegistry::getTableLocator()->get('Contacts');
        $ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
        $Tasks = TableRegistry::getTableLocator()->get('Tasks');
        $ContactAdditionalInsuredPolicyRelation = TableRegistry::getTableLocator()->get('ContactAdditionalInsuredPolicyRelation');
        $CampaignRunningSchedule = TableRegistry::getTableLocator()->get('CampaignRunningSchedule');
        $CampaignRunningEmailSmsSchedule = TableRegistry::getTableLocator()->get('CampaignRunningEmailSmsSchedule');
        $ContactCommunications = TableRegistry::getTableLocator()->get('ContactCommunications');
        $ContactNotes = TableRegistry::getTableLocator()->get('ContactNotes');
        $ContactCrossSellPolicy = TableRegistry::getTableLocator()->get('ContactCrossSellPolicy');
        $Services = TableRegistry::getTableLocator()->get('Services');
        $RenewalPipeline = TableRegistry::getTableLocator()->get('RenewalPipeline');
        $ContactCustomFields = TableRegistry::getTableLocator()->get('ContactCustomFields');
        $ContactLogs = TableRegistry::getTableLocator()->get('ContactLogs');
		$response = _ID_FAILED;
        if($contactId)
        {
            $contact = $Contacts->get($contactId);
            if ($contact) {
				 $Contacts->updateAll(['original_status_before_deletion' => $contact->status],['id' => $contactId,'agency_id' => $login_agency_id]);
                $Contacts->updateAll(['status' => _ID_STATUS_DELETED,'manual_deleted_date' => date('Y-m-d H:i:s'),'manual_deleted_flag' => _STATUS_TRUE],['id' => $contactId,'agency_id' => $login_agency_id]);
                //copy contact opp and task original status to new field
                $allNonDeletedOpp = $ContactOpportunities->getAllNonDeletedOppByContactId($contactId);
                if(isset($allNonDeletedOpp) && !empty($allNonDeletedOpp))
                {
                    foreach ($allNonDeletedOpp as $opp)
                    {
                        $ContactOpportunities->updateAll(['original_status_before_deletion' => $opp['status']],['id' => $opp['id'],'agency_id' => $login_agency_id]);
                    }
                }
                $allNonDeletedTasks = $Tasks->getAllNonDeletedTasksByContactId($contactId);
                if(isset($allNonDeletedTasks) && !empty($allNonDeletedTasks))
                {
                    foreach ($allNonDeletedTasks as $task)
                    {
                        $Tasks->updateAll(['original_status_before_deletion' => $task['status']],['id' => $task['id'],'agency_id' => $login_agency_id]);
                    }
                }
                //
                $secondaryContactsId = $ContactAdditionalInsuredPolicyRelation->getAllInsuredContactsbyContactId($contactId);
                $secondaryArray=[];
                if(isset($secondaryContactsId) && !empty($secondaryContactsId))
                {
                    foreach ($secondaryContactsId as $secondaryContactId)
                    {
    
                        array_push($secondaryArray,$secondaryContactId['additional_insured_contact_id']);
                    }
                }
                if(isset($secondaryArray) && !empty($secondaryArray))
                {
                    $Contacts->updateAll(['additional_insured_flag' => NULL,'status' => _ID_STATUS_DELETED],['id IN' => $secondaryArray,'agency_id' => $login_agency_id]);
                }
                // update additional insured relationship status to delete
                $ContactAdditionalInsuredPolicyRelation->updateAll(['status'=>_ID_STATUS_DELETED],['contact_id' => $contactId,'status !=' =>_ID_STATUS_DELETED]);
    
                //$this->ContactBusiness->updateAll(['status'=>_ID_STATUS_DELETED],['contact_id'=>$id,'status !=' =>_ID_STATUS_DELETED]);
    
                $ContactOpportunities->updateAll(['status' => _ID_STATUS_DELETED],['contact_id' => $contactId,'status !=' =>_ID_STATUS_DELETED,'contact_business_id is null','agency_id' => $login_agency_id]);
    
                $CampaignRunningSchedule->updateAll(['status' => _RUN_SCHEDULE_STATUS_INACTIVE],['contact_id' => $contactId,'status IN' => [_RUN_SCHEDULE_STATUS_ACTIVE,_CAMPAIGN_STATUS_PAUSE],'contact_business_id is null','agency_id' => $login_agency_id]);
                $CampaignRunningEmailSmsSchedule->updateAll(['status' => _EMAIL_SMS_SENT_STATUS_CANCELLED],['contact_id' => $contactId,'status IN' => [_EMAIL_SMS_SENT_STATUS_PENDING,_CAMPAIGN_STATUS_PAUSE],'contact_business_id is null']);
    
    
                $ContactCommunications->updateAll(['status' => _ID_STATUS_DELETED],['contact_id' => $contactId,'status !=' =>_ID_STATUS_DELETED,'contact_business_id is null','agency_id' => $login_agency_id]);
                $ContactCustomFields->updateAll(['status' => _ID_STATUS_DELETED],['contact_id' => $contactId,'status !=' =>_ID_STATUS_DELETED,'contact_business_id is null','agency_id' => $login_agency_id]);
                $ContactNotes->updateAll(['status' => _ID_STATUS_DELETED],['contact_id' => $contactId,'status !=' =>_ID_STATUS_DELETED,'contact_business_id is null','agency_id' => $login_agency_id]);
                $Tasks->updateAll(['status' => _ID_STATUS_DELETED],['contact_id' => $contactId,'status !=' =>_ID_STATUS_DELETED,'contact_business_id is null','agency_id' => $login_agency_id]);
				$ContactCrossSellPolicy->updateAll(['status' => _ID_STATUS_DELETED],['contact_id' => $contactId,'status !=' =>_ID_STATUS_DELETED,'contact_business_id is null']);
                $Services->updateAll(['status' => _ID_STATUS_DELETED],['contact_id' => $contactId,'status !=' =>_ID_STATUS_DELETED,'contact_business_id is null','agency_id' => $login_agency_id]);
                $RenewalPipeline->updateAll(['status' => _ID_STATUS_DELETED],['contact_id' => $contactId,'status !=' =>_ID_STATUS_DELETED,'contact_business_id is null','agency_id' => $login_agency_id]);
                $response = ['status' => _ID_SUCCESS];
	            $log_details = [];
	            $log_details['platform'] = _PLATFORM_TYPE_SYSTEM;
	            $log_details['agency_id'] = $contact['agency_id'];
	            $log_details['user_id'] = $logged_in_user['id'];
	            $log_details['contact_id'] = $contactId;
	            $log_details['first_name'] = $contact['first_name'];
	            $log_details['last_name'] = $contact['last_name'];
	            $log_details['email'] = $contact['email'];
	            $log_details['phone'] = $contact['phone'];
	            $log_details['message'] = "Contact was deleted by ". $logged_in_user['first_name']. " " . $logged_in_user['last_name'] . ".";
				$contact_log = $ContactLogs->newEntity($log_details);
	            $ContactLogs->save($contact_log);
            } 
            else 
            {
                $response = ['status' => _ID_FAILED];
            }
		}
		$return['Contacts.deleteContact'] = [
            $contactId => $response
        ];
        return $return;

    }
    public function verifyemail($objectData){

        $session = Router::getRequest()->getSession();
        $login_agency_id = $session->read("Auth.User.agency_id");
        $Contacts = TableRegistry::getTableLocator()->get('Contacts');
        $contactsData = array();
        if(isset($objectData['contact_email']) && isset($objectData['contact_email']) !=''){
            $contactsData = $Contacts->getContactByEmailID($objectData['contact_id'],$objectData['contact_email'],$login_agency_id);
        }

        if(!empty($contactsData)){
            $response['contact_status'] = _ID_FAILED;
        }else{
            $response['contact_status'] = _ID_SUCCESS;
        }
        return json_encode($response);
    }
    public function verifyPhoneNumber($objectData){

        $session = Router::getRequest()->getSession();
        $login_agency_id = $session->read("Auth.User.agency_id");
        $Contacts = TableRegistry::getTableLocator()->get('Contacts');
        $contactsData = array();
        if(isset($objectData['contact_phone']) && isset($objectData['contact_phone']) !=''){
            $patterns = array('/\-/','/\(/','/\)/','/\+/','/\ /');
            $phone = preg_replace($patterns,'',$objectData['contact_phone']);
            $contactsData = $Contacts->getContactByPhoneID($objectData['contact_id'],$phone,$login_agency_id);
        }
        if(!empty($contactsData)){
            $response['contact_status'] = _ID_FAILED;
        }else{
            $response['contact_status'] = _ID_SUCCESS;
        }
        return json_encode($response);
    }
    public function getSecondaryContactDetail($contactId)
    {        
        try
        {
            $ContactsAdditionalInsured = TableRegistry::getTableLocator()->get('ContactAdditionalInsuredPolicyRelation');
            $ContactsTable = TableRegistry::getTableLocator()->get('Contacts');
            $secondaryContactData = array();
            $secondaryContactData = $ContactsAdditionalInsured->getLinkedPrimaryContacts($contactId, _ID_STATUS_ACTIVE);
            $contactData = $ContactsTable->get($contactId);
            $personalContactsSec = $ContactsAdditionalInsured->getLinkedOtherPrimaryContacts($contactId, _ID_STATUS_ACTIVE);
            if(!empty($secondaryContactData))
            {
                $secondaryContactData = array_merge($secondaryContactData, $personalContactsSec);
            }
            else if(!empty($personalContactsSec))
            {
                $secondaryContactData = $personalContactsSec;
            }
            if(empty($secondaryContactData) && $contactData->additional_insured_flag == 1)
            {
                $secondaryContactData = $ContactsAdditionalInsured->getLinkedPrimaryContactsToSecondary($contactId, _ID_STATUS_ACTIVE);
            }
            $i = 0;
            $personalContactIds = array();
            foreach($secondaryContactData as $contact){
                if($contact['status'] == _ID_SUCCESS)
                {
                    $isSearchVal = 0;
                    $searchVal = array_search($contact['contact']['id'],$personalContactIds);
                    if($searchVal !== false){
                        $isSearchVal = 1;
                    }
                    array_push($personalContactIds,$contact['contact']['id']);
                    if($isSearchVal == 0){
                            $result['primary_contact_ids'][$i]['contact_id'] = $contact['contact']['id'];
                            $result['primary_contact_ids'][$i]['contact']['first_name'] = $contact['contact']['first_name'];
                            $result['primary_contact_ids'][$i]['contact']['middle_name'] = $contact['contact']['middle_name'];
                            $result['primary_contact_ids'][$i]['contact']['last_name'] = $contact['contact']['last_name'];
                            $result['primary_contact_ids'][$i]['additional_insured_contact_id'] = $contact['additional_insured_contact_id'];
                            $result['primary_contact_ids'][$i]['relationship_with_contact'] = $contact['relationship_with_contact'];
                            $result['primary_contact_ids'][$i]['status'] = $contact['status'];
                            $i++;
                    }    
                }
            }
            $return['Contacts.getSecondaryContactDetail'] = [
                    $contactId => $result['primary_contact_ids']
                ];
                return $return;
        }
		catch (\Exception $e) 
		{
            return date('Y-m-d H:i:s').' :: Get Secondary Contact Detail Error-- '.$e->getMessage();
        }
    }
    public function getActivePoliciesAndSecondaryContact($contactId)
    {
        $session = Router::getRequest()->getSession();
        $login_agency_id = $session->read("Auth.User.agency_id");
        $ContactsAdditionalInsured = TableRegistry::getTableLocator()->get('ContactAdditionalInsuredPolicyRelation');
        $ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
        $contactsTable = TableRegistry::getTableLocator()->get('Contacts');
        $contactOpportunitiesData = $ContactOpportunities->getAllOpportunityByContactId($contactId);
        $opportunitiesData = $ContactOpportunities->getActiveOpportunitiesByContactId($login_agency_id, null, $contactId);
        $contactDetails = $contactsTable->get($contactId);
        $primaryLinkedContactData = $ContactsAdditionalInsured->getLinkedPrimaryContacts($contactId, _ID_STATUS_ACTIVE);
        $personalContactsSec = $ContactsAdditionalInsured->getLinkedOtherPrimaryContacts($contactId, _ID_STATUS_ACTIVE);
        if(empty($primaryLinkedContactData))
        {
            $primaryLinkedContactData = $personalContactsSec;
        }
        else if(!empty($primaryLinkedContactData) && !empty($personalContactsSec))
        {
            $primaryLinkedContactData = array_merge($primaryLinkedContactData, $personalContactsSec);
        }
        if(empty($primaryLinkedContactData) && $contactDetails->additional_insured_flag == 1)
        {
            $primaryLinkedContactData = $ContactsAdditionalInsured->getLinkedPrimaryContactsToSecondary($contactId, _ID_STATUS_ACTIVE);
        }
        if($contactDetails->additional_insured_flag != _ID_SUCCESS)
        {
            $secondaryContactData = $ContactsAdditionalInsured->getLinkedSecondaryContacts($contactId, _ID_STATUS_ACTIVE);
            if(empty($secondaryContactData))
            {
                $secondaryContactData = $ContactsAdditionalInsured->getLinkedOtherSecondaryContacts($contactId, _ID_STATUS_ACTIVE);
            }
        }
        //contactOpportunities
        $inactiveOpportunities = [];
        $activeOpportunities = [];
        $inactiveSubStatus = [];
        $inactiveOpportunities = array_filter($contactOpportunitiesData, function($item) {
            return $item['status'] == _ID_STATUS_INACTIVE && $item['inactive_sub_status'] != _ID_INACTIVE_SUB_STATUS_CANCELLED;
        });
        $pendingOpportunities = array_filter($opportunitiesData, function($item) {
            return $item['status'] == _ID_STATUS_PENDING;
        });
        $activeOpportunities = array_filter($contactOpportunitiesData, function($item) {
            return $item['status'] == _ID_STATUS_ACTIVE && $item['pipeline_stage'] == _PIPELINE_STAGE_WON;
        });
        $inactiveSubStatus = array_filter($contactOpportunitiesData, function($item) {
            return $item['status'] == _ID_STATUS_INACTIVE && $item['inactive_sub_status'] == _ID_INACTIVE_SUB_STATUS_CANCELLED;
        });
        $response = [];
        $response['insuredPolicyCheck'] = true;
        $response['linkedContactChecked'] = true;
        $response['secondaryContactChecked'] = true;
        if((count($activeOpportunities) != 0 && $contactDetails->additional_insured_flag != _ID_SUCCESS) || (count($inactiveOpportunities) != 0 && $contactDetails->additional_insured_flag != _ID_SUCCESS)  || (count($inactiveSubStatus) != 0 && $contactDetails->additional_insured_flag != _ID_SUCCESS))
        {
            $response['insuredPolicyCheck'] = false;
        }
        if(count($primaryLinkedContactData) <= _ID_FAILED)
        {
            $response['linkedContactChecked'] = false;
        }
        if(count($secondaryContactData) > _ID_FAILED)
        {
            $response['secondaryContactChecked'] = false;
        }
        $inactiveContact = [];
        $activeContact = [];
        $inactiveContact = array_filter($primaryLinkedContactData, function($item) {
            return $item['contact']['status'] == _ID_STATUS_INACTIVE;
        });
        $activeContact = array_filter($primaryLinkedContactData, function($item) {
            return $item['contact']['status'] == _ID_STATUS_ACTIVE && $item['contact']['lead_type'] == _CONTACT_TYPE_CLIENT;
        });
        // no link and no opportunities and label update to prospect
        if(empty($inactiveContact) && empty($activeContact) && empty($inactiveOpportunities) && $response['linkedContactChecked'] == false && empty($activeOpportunities) && empty($inactiveSubStatus))
        { 
            if(empty($inactiveContact) && $contactDetails->status == _ID_STATUS_INACTIVE)
            {
                $updateContactLabel = $contactsTable->updateAll(['status' => _ID_STATUS_ACTIVE], ['id' => $contactId]);
            }
            $updateContactLabel = $contactsTable->updateAll(['lead_type' => _CONTACT_TYPE_LEAD], ['id' => $contactId]);
            if($contactDetails->additional_insured_flag == _ID_SUCCESS)
            {
                $contactsTable->updateAll(['additional_insured_flag' => null], ['id' => $contactId]);
            }  
        }
        else if(empty($inactiveContact) && empty($activeContact) && empty($inactiveOpportunities) && $response['linkedContactChecked'] == false && empty($activeOpportunities) && !empty($inactiveSubStatus))
        {
            // cancel policy prospect lebel set
            $updateContactLabel = $contactsTable->updateAll(['status' => _ID_STATUS_INACTIVE], ['id' => $contactId]);
            if($contactDetails->additional_insured_flag == _ID_SUCCESS)
            {
                $contactsTable->updateAll(['additional_insured_flag' => null], ['id' => $contactId]);
            }  
        }
        else if(empty($activeContact) && !empty($inactiveOpportunities) && !empty($inactiveSubStatus) && empty($activeOpportunities) && empty($pendingOpportunities))
        { 
            //update to inactive contacts
            $updateContactLabel = $contactsTable->updateAll(['status' => _ID_STATUS_INACTIVE], ['id' => $contactId]);
        }
        else if(!empty($inactiveSubStatus) && empty($activeOpportunities) && !empty($pendingOpportunities))
        { 
            //update to inactive contacts
            $updateContactLabel = $contactsTable->updateAll(['status' => _ID_STATUS_INACTIVE], ['id' => $contactId]);
        }
        else if(!empty($inactiveContact) && empty($activeContact) && empty($activeOpportunities) && empty($pendingOpportunities))
        {
            //update to inactive contacts check link intactive contacts
            $updateContactLabel = $contactsTable->updateAll(['status' => _ID_STATUS_INACTIVE], ['id' => $contactId]);
        }
        else if(count($activeContact) != _ID_FAILED || !empty($activeOpportunities))
        {   
            // update client and status active 
            $updateContactLabel = $contactsTable->updateAll(['lead_type' => _CONTACT_TYPE_CLIENT, 'status' => _ID_STATUS_ACTIVE], ['id' => $contactId]);
        }
        $return['Contacts.getActivePoliciesAndSecondaryContact'] = [
            $contactId => $response
        ];
        return $return;
    }
    public function switchSecondaryContact($objectData)
    {
        $session = Router::getRequest()->getSession();
        $loginAgencyId = $session->read("Auth.User.agency_id");
        $loginUserId = $session->read("Auth.User.user_id");
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $contactsTable = TableRegistry::getTableLocator()->get('Contacts');
        $contactLogsTable = TableRegistry::getTableLocator()->get('ContactLogs');
        $ContactsAdditionalInsured = TableRegistry::getTableLocator()->get('ContactAdditionalInsuredPolicyRelation');
        $secondaryContactData = $ContactsAdditionalInsured->getinsuredByContactId($objectData['contactId']);
        $contactDetails = $contactsTable->get($objectData['contactId'], ['contain' => [
            'Users' => function ($q) {
                return $q->select(['Users.first_name', 'Users.last_name']);
            },]]);
        $userDetails = $usersTable->userDetails($loginUserId);
        $userName = $userDetails['first_name'] . ' ' . $userDetails['last_name'];
        $contactUpdate = 0;
        $response['status'] = _ID_FAILED;
        if($contactDetails && $contactDetails->agency_id == $loginAgencyId)
        {
            if ($contactDetails->additional_insured_flag == _ID_FAILED || $contactDetails->additional_insured_flag == null) {
                $contactUpdate = $contactsTable->updateAll(['additional_insured_flag' => _ID_SUCCESS], ['id' => $objectData['contactId'], 'agency_id' => $loginAgencyId]);
                $txt = ucwords($userName) . " changed the Contact Level from “Primary” to “Secondary” on " . date('M d, Y') . ' at ' . date('g:i a');
                $contactLogs = [];
                if($contactUpdate)
                {
                    $contactLogsData = $contactLogsTable->newEntity();
                    $contactLogs['contact_id'] = $contactDetails['id'];
                    $contactLogs['agency_id'] = $contactDetails['agency_id'];
                    $contactLogs['user_id'] = $contactDetails['user_id'];
                    $contactLogs['message'] = $txt;
                    $contactLogsData = $contactLogsTable->patchEntity($contactLogsData, $contactLogs);
                    $contactLogsData = $contactLogsTable->save($contactLogsData);
                    $response['status'] = _ID_SUCCESS;
                }
                else
                {
                    $response['status'] = _ID_FAILED;
                }
            }
        }
        return json_encode($response);
    }

    public function switchContactToPrimary($objectData)
    {
        $session = Router::getRequest()->getSession();
        $loginAgencyId = $session->read("Auth.User.agency_id");
        $loginUserId = $session->read("Auth.User.user_id");
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $contactsTable = TableRegistry::getTableLocator()->get('Contacts');
        $ContactsAdditionalInsured = TableRegistry::getTableLocator()->get('ContactAdditionalInsuredPolicyRelation');
        $getPrimaryContacts = $ContactsAdditionalInsured->getLinkedPrimaryContactsToSecondary($objectData['contactId'], _ID_STATUS_ACTIVE);
        $getPrimaryContactsSec = $ContactsAdditionalInsured->getLinkedPrimaryContacts($objectData['contactId'], _ID_STATUS_ACTIVE);
        if(!empty($getPrimaryContacts) && !empty($getPrimaryContactsSec))
        {
            $getPrimaryContacts = array_merge($getPrimaryContacts, $getPrimaryContactsSec);
        }
        else if(!empty($getPrimaryContactsSec))
        {
            $getPrimaryContacts = $getPrimaryContactsSec;
        }
        $contactLogsTable = TableRegistry::getTableLocator()->get('ContactLogs');
        $contactDetails = $contactsTable->get($objectData['contactId'], ['contain' => [
            'Users' => function ($q) {
                return $q->select(['Users.first_name', 'Users.last_name']);
            },]]);
        $userDetails = $usersTable->userDetails($loginUserId);
        $userName = $userDetails['first_name'] . ' ' . $userDetails['last_name'];
        $contactUpdate = 0;
        $response['status'] = _ID_FAILED;
        $inactiveContact = [];
        $activeContact = [];

        // for contact lebing update
        $inactiveContact = array_filter($getPrimaryContacts, function($item) {
            return $item['contact']['status'] == _ID_STATUS_INACTIVE;
        });
        $activeContact = array_filter($getPrimaryContacts, function($item) {
            return $item['contact']['status'] == _ID_STATUS_ACTIVE && $item['contact']['lead_type'] == _CONTACT_TYPE_CLIENT;
        });
        // end filter
        if($contactDetails && $contactDetails->agency_id == $loginAgencyId)
        { 
            if ($contactDetails->additional_insured_flag == _ID_SUCCESS) {
                foreach($getPrimaryContacts as $primaryContact)
                {   
                    if($objectData['contactId'] == $primaryContact['contact_id'])
                    {
                        $contactData = $contactsTable->get($primaryContact['additional_insured_contact_id']);
                    }
                    else
                    {
                        $contactData = $contactsTable->get($primaryContact['contact_id']);
                    }
                    
                    //update label inactive contact
                    if($contactData->status == _ID_STATUS_INACTIVE && empty($activeContact))
                    {
                        if($contactDetails->status == _ID_STATUS_INACTIVE)
                        {
                            $contactUpdate = $contactsTable->updateAll(['status' => _ID_STATUS_INACTIVE], ['id' => $objectData['contactId'], 'agency_id' => $loginAgencyId]);
                        }
                    }
                    else
                    {   
                        // update lebel active
                        if($contactDetails->status == _ID_STATUS_INACTIVE && empty($activeContact))
                        {
                            $contactUpdate = $contactsTable->updateAll(['status' => _ID_STATUS_ACTIVE], ['id' => $objectData['contactId'], 'agency_id' => $loginAgencyId]);
                        }
                        //update leebel client
                        if($contactData->lead_type == _CONTACT_TYPE_CLIENT && !empty($activeContact))
                        {
                            $contactUpdate = $contactsTable->updateAll(['lead_type' => _CONTACT_TYPE_CLIENT], ['id' => $objectData['contactId'], 'agency_id' => $loginAgencyId]);
                        }
                        else if(empty($activeContact) && empty($inactiveContact))
                        { //update leebel prospect
                            $contactUpdate = $contactsTable->updateAll(['lead_type' => _CONTACT_TYPE_LEAD], ['id' => $objectData['contactId'], 'agency_id' => $loginAgencyId]);
                        }
                    }
                }
                $contactUpdate = $contactsTable->updateAll(['additional_insured_flag' => NULL], ['id' => $objectData['contactId'], 'agency_id' => $loginAgencyId]);
                $txt = ucwords($userName) . " changed the Contact Level from “Secondary” to “Primary” on " . date('M d, Y') . ' at ' . date('g:i a');
                $contactLogs = [];
                if($contactUpdate)
                {
                    $contactLogsData = $contactLogsTable->newEntity();
                    $contactLogs['contact_id'] = $contactDetails['id'];
                    $contactLogs['agency_id'] = $contactDetails['agency_id'];
                    $contactLogs['user_id'] = $contactDetails['user_id'];
                    $contactLogs['message'] = $txt;
                    $contactLogsData = $contactLogsTable->patchEntity($contactLogsData, $contactLogs);
                    $contactLogsData = $contactLogsTable->save($contactLogsData);
                    $response['status'] = _ID_SUCCESS;
                }
                else
                {
                    $response['status'] = _ID_FAILED;
                }
            }
        }
        else
        {
            $response['status'] = _ID_FAILED;
        }
        return json_encode($response);
    }
    public function getNowcertsData($contactId)
    {
        try
        {
            $session = Router::getRequest()->getSession();
            $login_agency_id= $session->read("Auth.User.agency_id");
            $nowcertsPushAgencyToken = TableRegistry::getTableLocator()->get('NowcertsPushAgencyToken');
            $nowCertsDetail = $nowcertsPushAgencyToken->checkAccessTokenByAgencyId($login_agency_id);
            $nowCertsData = [];
            if(!empty($nowCertsDetail))
            {
                $nowCertsData['nowCerts'] = $nowCertsDetail;
            }
            $return['Contacts.getNowcertsData'] = [
                $contactId => $nowCertsData
            ];
            return $return;  
        }
        catch (\Exception $e) {
            $txt = date('Y-m-d H:i:s').' :: getNowcertsData Error- '.$e->getMessage();
        }
    }
    public function updateXdateCampaignStatus($objectData)
    {
        $session = Router::getRequest()->getSession();
        $loginAgencyId= $session->read("Auth.User.agency_id");
        $contactsTable = TableRegistry::getTableLocator()->get('Contacts');
        if(!empty($objectData))
        {
            if($objectData['xdate_campaign_start_status'] == false)
            {
                $contactUpdate = $contactsTable->updateAll(['xdate_campaign_start_status' => _ID_FAILED], ['id' => $objectData['contact_id'], 'agency_id' => $loginAgencyId]);
            }
           else if($objectData['xdate_campaign_start_status'] == true)
            {
                $contactUpdate = $contactsTable->updateAll(['xdate_campaign_start_status' => _ID_SUCCESS], ['id' => $objectData['contact_id'], 'agency_id' => $loginAgencyId]);
            }
            if($contactUpdate)
            {
                $response['status'] = _ID_SUCCESS;
            }
            else
            {
                $response['status'] = _ID_FAILED;
            }
            return json_encode($response);
        }
    }
}

