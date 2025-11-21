<?php

namespace App\Lib\ApiProviders;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use App\Classes\CommonFunctions;


class ContactBusiness
{	
    public function getContactBusiness($contactBusinessId)
    {
        try
        {
            ini_set('memory_limit', '-1');
            ini_set('max_execution_time', '-1');
            $session = Router::getRequest()->getSession(); 
		    $login_user_id = $session->read("Auth.User.user_id");
            $login_agency_id = $session->read("Auth.User.agency_id");
            $login_role_type = $session->read('Auth.User.role_type');
            $login_role_type_flag =  $session->read('Auth.User.role_type_flag'); 

            $ContactBusiness = TableRegistry::getTableLocator()->get('ContactBusiness');
            $UsStates = TableRegistry::getTableLocator()->get('UsStates');
            $Users = TableRegistry::getTableLocator()->get('Users');
            $CampaignRunningSchedule = TableRegistry::getTableLocator()->get('CampaignRunningSchedule');
            $AgencyCampaignMaster = TableRegistry::getTableLocator()->get('AgencyCampaignMaster');
            $CampaignRunningEmailSmsSchedule = TableRegistry::getTableLocator()->get('CampaignRunningEmailSmsSchedule');
            $Agency = TableRegistry::getTableLocator()->get('Agency');
            $BusinessPrimaryContact = TableRegistry::getTableLocator()->get('BusinessPrimaryContact');
            $BusinessCommunication = TableRegistry::getTableLocator()->get('BusinessCommunication');
            $BusinessAddress = TableRegistry::getTableLocator()->get('BusinessAddress');

            $states = $UsStates->getAllStates();
            $business_id = $contactBusinessId;
            $userDetails = $Users->userDetails($login_user_id);        
            $business = $ContactBusiness->getContactBusiness($business_id);
            $result = array();

            // print_r($business);
            // die("nsdsmmdsdd");
            if(!isset($business) && empty($business))
            {
                throw new Exception("Invalid Data");
                
            }else{
                $result['contact_business_id'] =  $business['id'];
                $result['name'] =  $business['name'];
                $result['lead_source_type'] =  $business['lead_source_type'];
                $result['created'] =  $business['created'];
            }
            $source_type_id=0;
            // if(!empty($businessDetails['lead_source_type'])){
            //     $result['lead_source_type'] = $businessDetails['lead_source_type'];
            // }
            //mark the running schedule completed if no scheduled email/sms is there
            $runnngCampaignScheduleDetails = $CampaignRunningSchedule->getAllActiveCampaignByBusinessID($business_id,['id','campaign_id','pipeline_stage_id']); 
            if(isset($runnngCampaignScheduleDetails) && !empty($runnngCampaignScheduleDetails))
            {
                foreach ($runnngCampaignScheduleDetails as $runnngCampaignScheduleDetail) {
                    $campaign_detail_check_type = $AgencyCampaignMaster->agencyCampaignDetail($runnngCampaignScheduleDetail['campaign_id']);
                    $getPendingRunningEmailSmsSchedule = $CampaignRunningEmailSmsSchedule->getPendingCampaignRunningSchedule($runnngCampaignScheduleDetail['id']);  
                    if(empty($getPendingRunningEmailSmsSchedule))
                    {
                        if($campaign_detail_check_type->type == _CAMPAIGN_TYPE_X_DATE)
                        {
                            if($runnngCampaignScheduleDetail['pipeline_stage_id'] == _LEAD_NURTURE_X_DATE_DAYS_TYPE_TEN)
                            {
                                $CampaignRunningSchedule->updateAll(['status'=>_RUN_SCHEDULE_STATUS_COMPLETED],['id'=>$runnngCampaignScheduleDetail['id']]);
                            }
                        }
                        else if($campaign_detail_check_type->type == _CAMPAIGN_TYPE_LONG_TERM_NURTURE)
                        {
                            if($runnngCampaignScheduleDetail['pipeline_stage_id'] == _LONG_TERM_NURTURE_STAGE_3_MONTH)
                            {
                                $CampaignRunningSchedule->updateAll(['status'=>_RUN_SCHEDULE_STATUS_COMPLETED],['id'=>$runnngCampaignScheduleDetail['id']]);
                            }
                        }
                        else
                        {
                            $CampaignRunningSchedule->updateAll(['status'=>_RUN_SCHEDULE_STATUS_COMPLETED],['id'=>$runnngCampaignScheduleDetail['id']]);
                        }
                        
                    }
                }
            }
            //get agency details
            $agencyDetails = $Agency->agencyDetails($login_agency_id);
            $agent_name=$agencyDetails['company'];
                 
            $business_primary_contact = $BusinessPrimaryContact->getActiveBusinessPrimaryContact($business_id);
            $primary_contact_primary_phone = "";
            $primary_contact_primary_phone_detail = $BusinessCommunication->getActivePrimaryContactPrimaryPhone($business_primary_contact['id']);
            if(isset($primary_contact_primary_phone_detail['email_phone']) && !empty($primary_contact_primary_phone_detail['email_phone']))
            {
                $primary_contact_primary_phone = CommonFunctions::format_phone_us($primary_contact_primary_phone_detail['email_phone']);
            }
            $result['phone'] =  $primary_contact_primary_phone;

            $primary_contact_primary_email = "";
            $primary_contact_primary_email_detail = $BusinessCommunication->getActivePrimaryContactPrimaryEmail($business_primary_contact['id']);
            if(isset($primary_contact_primary_email_detail['email_phone']) && !empty($primary_contact_primary_email_detail['email_phone']))
            {
                $primary_contact_primary_email = $primary_contact_primary_email_detail['email_phone'];
            }
            $result['email'] =  $primary_contact_primary_email;
            $business_addresses = "";
            $business_primary_mailing_same_or_not = _MAILING_ADDRESS_SAME;
            $primary_business_address = [];
            $mailing_business_address = [];
            $additional_business_address = [];
            if(isset($business) && !empty($business))
            {
                $business_addresses = $BusinessAddress->getAllActiveBusinessAddress($business_id);
                //print_r($business_addresses);die;
                if(isset($business_addresses) && !empty($business_addresses))
                {
                    foreach ($business_addresses as $business_address) 
                    {
                        if($business_address['primary_flag'] == _STATUS_TRUE)
                        {
                            $primary_business_address = $business_address;
                        }
                        else
                        {
                            $additional_business_address[] = $business_address;
                        }
                    }
                    //print_r($additional_business_address);die;
                    if(isset($primary_business_address) && !empty($primary_business_address) && isset($mailing_business_address) && !empty($mailing_business_address) && $primary_business_address['id'] == $mailing_business_address['id'])
                    {
                        $mailing_business_address = "";
                    }
                    if(isset($mailing_business_address) && !empty($mailing_business_address))
                    {
                        $business_primary_mailing_same_or_not = _MAILING_ADDRESS_DIFFERENT;
                    }
                }
            }
            // echo '<pre>';
            // print_r($result);
            // die();
            $return['ContactBusiness.getContactBusiness'] = [
                $business_id => $result
            ];
            return $return;
           
        }
        catch (\Exception $ex) {
            $message = $ex->getMessage();
            echo json_encode(array('status' => _ID_FAILED,'message'=>$message));
             die;
        }

        
    }
    
}