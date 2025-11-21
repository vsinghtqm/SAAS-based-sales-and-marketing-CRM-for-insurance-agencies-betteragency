<?php

namespace App\Lib\ApiProviders;

use App\Lib\PermissionsCheck;
use App\Lib\QuickTables\CampaignRunningScheduleTable;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Http\Session;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router; 
use App\Controller\Component\CommonComponent;
use App\Classes\CommonFunctions;
use App\Classes\FileLog;

class ContactCampaigns
{
	public static function getActiveContactCampaigns($contactId)
    {
        try
		{
			$myfile = fopen(ROOT."/logs/vueActiveCampaign.log", "a") or die("Unable to open file!"); 
            $CampaignRunningScheduleTable = TableRegistry::getTableLocator()->get('CampaignRunningSchedule');
            $result = $CampaignRunningScheduleTable->getCampaignListingByContactID($contactId);
            $return['ContactCampaigns.getActiveContactCampaigns'] = [
                $contactId => $result
            ];
            return $return;
        }catch (\Exception $e) {
			
            $txt=date('Y-m-d H:i:s').' :: Campaign Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
	}
	
	public static function getCampaignHistoryContactCard($contact_id)
    {
		try
		{
			$myfile = fopen(ROOT."/logs/vueActiveCampaign.log", "a") or die("Unable to open file!"); 
            $session = Router::getRequest()->getSession(); 
            $login_agency_id= $session->read("Auth.User.agency_id");
            $time_zone = CommonComponent::getTimeZone($login_agency_id);
            $CampaignRunningScheduleTable = TableRegistry::getTableLocator()->get('CampaignRunningSchedule');
            $campaigns = $CampaignRunningScheduleTable->getCampaignHistoryListingByContactID($contact_id);
        
            $return['ContactCampaigns.getCampaignHistoryContactCard'] = [
                $contact_id => $campaigns
            ];
        
            return $return;
        }catch (\Exception $e) {
			
            $txt=date('Y-m-d H:i:s').' :: Active Campaign Lising Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
	}
	
	public static function getUpcomingCampaignsContactCard($contact_id)
    {
        try
		{
			$myfile = fopen(ROOT."/logs/vueUmcomingCampaign.log", "a") or die("Unable to open file!");
            $session = Router::getRequest()->getSession(); 
            $login_agency_id= $session->read("Auth.User.agency_id");
            $todayDate=date("Y-m-d");
            $no_records_found=_ID_FAILED;
            $before_days=60;//two months        
            $history_list= "";
        
            $AgencyTable = TableRegistry::getTableLocator()->get('Agency');
            $AgencyCampaignMasterTable = TableRegistry::getTableLocator()->get('AgencyCampaignMaster');
            $ContactPolicyRenewalTable = TableRegistry::getTableLocator()->get('ContactPolicyRenewal');
            $ContactOpportunitiesTable = TableRegistry::getTableLocator()->get('ContactOpportunities');
            $ContactCrossSellPolicyTable = TableRegistry::getTableLocator()->get('ContactCrossSellPolicy');
            $CampaignRunningScheduleTable = TableRegistry::getTableLocator()->get('CampaignRunningSchedule');
            
            $ContactsTable = TableRegistry::getTableLocator()->get('Contacts');
            $agencyDetail = $AgencyTable->agencyDetails($login_agency_id);
            $contactDetails = $ContactsTable->contactDetails($contact_id);
            if(isset($agencyDetail) && !empty($agencyDetail) && isset($contactDetails) && !empty($contactDetails))
            {
                $campaignRunningSchedule=$CampaignRunningScheduleTable->find('all')->where(['contact_id'=>$contactDetails['id'],'status'=>_RUN_SCHEDULE_STATUS_ACTIVE,'contact_business_id is null'])->toArray();
                $contact_active_campaigns = [];
                foreach ($campaignRunningSchedule as $key => $value)
                {
                    $contact_active_campaigns[]=$value['campaign_id'];
                }

                //get all active campagins ids, if not active then list upcoming campagins
                //case 1 : if applicable for birthday campagin   
                if(!empty($contactDetails['birth_date']) && ($contactDetails['running_birthday_campaign_year']==null || $contactDetails['running_birthday_campaign_year'] != date('Y')))
                {
                    $coming_birth_date=date('Y').'-'.date('m',strtotime($contactDetails['birth_date'])).'-'.date('d',strtotime($contactDetails['birth_date']));  
                    if($contactDetails['lead_type']==_CONTACT_TYPE_LEAD)
                    {                        
                        $prospect_birthday_campaign_detail = $AgencyCampaignMasterTable->checkCampaignExist(null,null, $login_agency_id,_PROSPECT_BIRTHDAY_STAGE_INITIATE,_CAMPAIGN_TYPE_PROSPECT_BIRTHDAY);

                        if(!empty($prospect_birthday_campaign_detail['initiate_days']))
                        {
                            if($prospect_birthday_campaign_detail['before_after']==1){
                                $campagin_trigger_date=date('Y-m-d', strtotime($coming_birth_date. ' - '.$prospect_birthday_campaign_detail['initiate_days'].' days'));
                            }
                            else if($prospect_birthday_campaign_detail['before_after']==2){
                                $campagin_trigger_date=date('Y-m-d', strtotime($coming_birth_date. ' + '.$prospect_birthday_campaign_detail['initiate_days'].' days'));
                            }
                        }else{
                            $campagin_trigger_date=$coming_birth_date;
                        }

                        $date1 = date_create($todayDate);
                        $date2 = date_create($campagin_trigger_date);
                        // //difference between two dates
                        $diff = date_diff($date1,$date2);
                        $days_count=$diff->format("%a");                  
                        if(isset($prospect_birthday_campaign_detail['name']) && !empty($prospect_birthday_campaign_detail['name']) && isset($campagin_trigger_date) && !empty($campagin_trigger_date) && ($days_count > 0 && $days_count <=$before_days) && !in_array($prospect_birthday_campaign_detail['id'], $contact_active_campaigns))
                        {
                            $campagin_type_name=getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_PROSPECT_BIRTHDAY);
                            $date_time = date('M d, Y', strtotime($campagin_trigger_date));                        
                            $history_list .='<tr data-conact-id = "'.$contact_id.'" data-birth-date="'.date('Y',strtotime($coming_birth_date)).'" class="upcoming_campaign" id="'.$prospect_birthday_campaign_detail['id'].'"><td>' . ucwords($prospect_birthday_campaign_detail['name']) . '</td><td>'.$campagin_type_name.'</td><td>'.$date_time.'</td></tr>';
                            $no_records_found=_ID_SUCCESS;
                        }

                    }
                    else if($contactDetails['lead_type']==_CONTACT_TYPE_CLIENT)
                    {                          
                        $client_birthday_campaign_detail = $AgencyCampaignMasterTable ->checkCampaignExist(null,null, $login_agency_id,_CLIENT_BIRTHDAY_STAGE_INITIATE,_CAMPAIGN_TYPE_CLIENT_BIRTHDAY);

                        if(!empty($client_birthday_campaign_detail['initiate_days']))
                        {

                        if($client_birthday_campaign_detail['before_after']==1){
                                $campagin_trigger_date=date('Y-m-d', strtotime($coming_birth_date. ' - '.$client_birthday_campaign_detail['initiate_days'].' days'));
                            }
                            else if($client_birthday_campaign_detail['before_after']==2){
                                $campagin_trigger_date=date('Y-m-d', strtotime($coming_birth_date. ' + '.$client_birthday_campaign_detail['initiate_days'].' days'));
                                
                            }
                        }else{
                            $campagin_trigger_date=$coming_birth_date;
                        }

                        $date1 = date_create($todayDate);
                        $date2 = date_create($campagin_trigger_date);
                        // //difference between two dates
                        $diff = date_diff($date1,$date2);
                        $days_count=$diff->format("%a");                
                        if(isset($client_birthday_campaign_detail['name']) && !empty($client_birthday_campaign_detail['name']) && isset($campagin_trigger_date) && !empty($campagin_trigger_date) && ($days_count > 0 && $days_count <=$before_days) && !in_array($client_birthday_campaign_detail['id'], $contact_active_campaigns))
                        {
                            $campagin_type_name=getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_CLIENT_BIRTHDAY);
                            $date_time=date('M d, Y', strtotime($campagin_trigger_date));
                            $history_list .='<tr data-conact-id = "'.$contact_id.'" data-birth-date="'.date('Y',strtotime($coming_birth_date)).'" id="'.$client_birthday_campaign_detail['id'].'" class="upcoming_campaign"><td>' . ucwords($client_birthday_campaign_detail['name']) . '</td><td>'.$campagin_type_name.'</td><td>'.$date_time.'</td></tr>';
                                $no_records_found=_ID_SUCCESS;
                        }
                    }               

                }
                //

                //case 2 : if applicable for policy renewal campagin
                $days_before_renewal=0;
                if(!empty($agencyDetail['days_before_renewal'])){
                    $days_before_renewal=$agencyDetail['days_before_renewal'];
                }            
                $before_days_plus_days_before_renewal=$before_days+$days_before_renewal;
                $contactsPolicyRenewals = $ContactPolicyRenewalTable->getUpcomingPolicyRenewalByContactID($contactDetails['id'],$before_days_plus_days_before_renewal); 
                $renewal_campaign_detail=$AgencyCampaignMasterTable ->checkCampaignExist(null,null,$login_agency_id,_RENEWAL_STAGE_INITIATE,_CAMPAIGN_TYPE_RENEWAL);
                $annual_review_trigger_start_flag = false;
                $annual_review_trigger_opp="";
                if($agencyDetail['annual_review_trigger_personal'] == _ANNUAL_REVIEW_TRIGGER_EARLIEST_POLICY)
                {
                    $annual_review_trigger_opp = $ContactOpportunitiesTable->getContactEarliestActivePolicyReviewTrigger($contactDetails['id']);

                }
                else if($agencyDetail['annual_review_trigger_personal'] == _ANNUAL_REVIEW_TRIGGER_POLICY_TYPE)
                {
                    $annual_review_trigger_opp = $ContactOpportunitiesTable->getContactActivePolicyReviewTrigger($contactDetails['id'],$agencyDetail['personal_trigger_policy_type']);
                    if(empty($annual_review_trigger_opp))
                    {
                        $annual_review_trigger_opp = $ContactOpportunitiesTable->getContactEarliestActivePolicyReviewTrigger($contactDetails['id']);
                    }
                }
                else if($agencyDetail['annual_review_trigger_personal'] == _ANNUAL_REVIEW_TRIGGER_LARGEST_PERMIUM_POLICY)
                {
                    $annual_review_trigger_opp = $ContactOpportunitiesTable->getContactLargestPremiumActivePolicyReviewTrigger($contactDetails['id']);
                }  
                if(isset($contactsPolicyRenewals) && !empty($contactsPolicyRenewals) && $contactDetails['renewal_campaign_start_flag']==_STATUS_TRUE)
                {  
                    $campagin_type_name=getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_RENEWAL);             
                    foreach ($contactsPolicyRenewals as $contactsPolicyRenewal) 
                    {  
                        $annual_review_trigger_start_flag=false;
                        if((isset($annual_review_trigger_opp) && !empty($annual_review_trigger_opp) && $annual_review_trigger_opp['id'] == $contactsPolicyRenewal['contact_opportunities_id']) || $agencyDetail['annual_review_trigger_personal'] == _ANNUAL_REVIEW_TRIGGER_SEPRATELY_EVERY_POLICY)
                        {
                            $annual_review_trigger_start_flag = true;
                        }
                        $difference=0;
                        if($contactDetails['policy_review_trigger_date'] != NULL)
                        {
                            $datetime1 = new \DateTime(date('Y-m-d',strtotime($contactDetails['policy_review_trigger_date'])));
                            $datetime2 = new \DateTime(date('Y-m-d'));
                            $difference = $datetime1->diff($datetime2);
                            $difference = (($difference->y) * 12) + ($difference->m);
                        }
                        if(($annual_review_trigger_start_flag && ($contactDetails['policy_review_trigger_date']==NULL || $difference>=12)) || $agencyDetail['annual_review_trigger_personal'] == _ANNUAL_REVIEW_TRIGGER_SEPRATELY_EVERY_POLICY)
                        {              
                            $campagin_trigger_date=date('Y-m-d', strtotime($contactsPolicyRenewal['new_renewal_date']. ' - '.$days_before_renewal.' days'));
                            $date1 = date_create($todayDate);
                            $date2 = date_create($campagin_trigger_date);
                            // //difference between two dates
                            $diff = date_diff($date1,$date2);
                            $days_count=$diff->format("%a");                   
                            if(isset($renewal_campaign_detail['name']) && !empty($renewal_campaign_detail['name']) && isset($contactsPolicyRenewal['policy_name']) && !empty($contactsPolicyRenewal['policy_name']) && isset($campagin_trigger_date) && !empty($campagin_trigger_date) && ($days_count > 0 && $days_count <=$before_days) && !in_array($renewal_campaign_detail['id'], $contact_active_campaigns))
                            {                   
                                $date_time=date('M d, Y', strtotime($campagin_trigger_date));
                                $history_list .='<tr data-conact-id = "'.$contact_id.'" data-birth-date="" id="'.$renewal_campaign_detail['id'].'" class="upcoming_campaign"><td>' . ucwords($renewal_campaign_detail['name']) . ' - '.$contactsPolicyRenewal['policy_name'].'</td><td>'.$campagin_type_name.'</td><td> '.$date_time.'</td></tr>';
                                $no_records_found=_ID_SUCCESS;
                            }
                        }
                    }

                }

                //case 3 : if applicable for cross sell campagin
                $getComingCrossSellPolicy = $ContactCrossSellPolicyTable->getCrossSellPoliciesByContactId($contactDetails['id']);
                if(isset($getComingCrossSellPolicy->cross_sell_policy_id) && !empty($getComingCrossSellPolicy->cross_sell_policy_id) && $contactDetails['id'] != 486099)
                {
                    $crossSellPolicyId = $getComingCrossSellPolicy->cross_sell_policy_id;
                    $cross_sell_campaign_detail=$AgencyCampaignMasterTable ->checkCampaignExist(null,$crossSellPolicyId,$login_agency_id,_CROSS_SELL_STAGE_INITIATE,_CAMPAIGN_TYPE_CROSS_SELL);
                    $campagin_type_name=getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_CROSS_SELL);

                    if(!empty($cross_sell_campaign_detail))
                    { 
                        $days_after_policy_won=0;
                        if(!empty($cross_sell_campaign_detail['initiate_days'])){
                            $days_after_policy_won=$cross_sell_campaign_detail['initiate_days'];
                        }            
                        $before_days_plus_days_after_policy_won=$before_days+$days_after_policy_won;
                        $crossSellContactsOpportunities = $ContactOpportunitiesTable->getAllCrossSellContactsOpportunitiesByContact($contactDetails['id'],$before_days_plus_days_after_policy_won);

                        //echo 'days_after_policy_won='.$days_after_policy_won;
                        //echo 'before_days_plus_days_after_policy_won='.$before_days_plus_days_after_policy_won;
                        if(isset($crossSellContactsOpportunities) && !empty($crossSellContactsOpportunities))
                        {//echo 'llllxxx';die;
                            foreach ($crossSellContactsOpportunities as $crossSellContactsOpportunity)
                            { 
                                $campagin_trigger_date=date('Y-m-d', strtotime($crossSellContactsOpportunity['won_date']. ' + '.$days_after_policy_won.' days'));

                                $date1 = date_create($todayDate);
                                $date2 = date_create($campagin_trigger_date);
                                // //difference between two dates
                                $diff = date_diff($date1,$date2);
                                $days_count=$diff->format("%a");
                                //echo 'days diff='.$days_count;
                                //echo '<pre>';
                                //print_r($crossSellContactsOpportunity['won_date']);
                                if(isset($cross_sell_campaign_detail['name']) && !empty($cross_sell_campaign_detail['name']) && isset($campagin_trigger_date) && !empty($campagin_trigger_date) && ($days_count > 0 && $days_count <=$before_days) && !in_array($cross_sell_campaign_detail['id'], $contact_active_campaigns))
                                {
                            
                                    $date_time=date('M d, Y', strtotime($campagin_trigger_date));
                                    $history_list .='<tr data-conact-id = "'.$contact_id.'" data-birth-date=""  id="'.$cross_sell_campaign_detail['id'].'" class="upcoming_campaign"><td>' . ucwords($cross_sell_campaign_detail['name']) . '</td><td>'.$campagin_type_name.'</td><td>'.$date_time.'</td></tr>';
                                    $no_records_found=_ID_SUCCESS;

                                }
                                break;
                            }
                        } 
                    }
                }
                //

                //case 4 : if applicable for LTN campagin
                $contactStagesArray=array();//store all contacts x-date difference is less than 11 month
                $contactDetailsArray=array();   
                $nurture_contacts = $ContactOpportunitiesTable->getAllNurtureContactListByContact($contactDetails['id']);
                //echo '<pre>';
                //print_r($nurture_contacts);die;
                foreach ($nurture_contacts as $nurture_contact) 
                {
                    if(!empty($nurture_contact['contact']['expiration_date']) && $nurture_contact['contact']['long_term_nurture_stop_status'] == _STATUS_FALSE)
                    { 
                        $new_expiration_date=date('Y-m-d', strtotime($nurture_contact['contact']['expiration_date']. ' - '.$before_days.' days'));
                        $new_expiration_date=date('Y-m-d', strtotime($new_expiration_date. ' - 11 months'));

                        //echo "new_expiration_date=".$new_expiration_date;
                        //long term nurture get month diff count
                        $month_count=count(CommonComponent::get_months($todayDate, $new_expiration_date))-1;
                        //echo 'month_count='.$month_count;
                        $date1 = date_create($todayDate);
                        $date2 = date_create($new_expiration_date);
                        // //difference between two dates
                        $diff = date_diff($date1,$date2);
                        $days_count=$diff->format("%a");
                        //echo 'days diff='.$days_count; 
                        if($days_count > 0 && $days_count <=$before_days)
                        {                     
                            $contactStagesArray[$nurture_contact['contact_id']][]=$nurture_contact['pipeline_stage'];
                            $campagin_trigger_date = date('Y-m-d', strtotime($nurture_contact['contact']['expiration_date']. ' - 11 months'));
                            $contactDetailsArray[$nurture_contact['contact_id']]['campagin_trigger_date']=$campagin_trigger_date;
                        } 
                    }

                }

                $contact_id_final=0;//check if contact is only lost then store contact ids
                $pipeline_stages=array(_PIPELINE_STAGE_NEW_LEAD,_PIPELINE_STAGE_APPOINTMENT_SCHEDULED,_PIPELINE_STAGE_WORKING,_PIPELINE_STAGE_QUOTE_READY,_PIPELINE_STAGE_QUOTE_SENT,_PIPELINE_STAGE_WON);
                if(!empty($contactStagesArray)){
                    foreach ($contactStagesArray as $key => $value) {  

                        if(!empty($contactStagesArray[$key])){
                            $status=true;
                            foreach ($contactStagesArray[$key] as $key2 => $value2) {
                                if(in_array($value2, $pipeline_stages)){
                                    $status=false;
                                }                            
                            }
                            if($status){
                                $contact_id_final=$key;
                            }
                        }
                    }
                }


                //if contact ids are not empty run campaign
                if(!empty($contact_id_final))
                {
                    $campagin_type_name=getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_LONG_TERM_NURTURE);                
                    $ltn_campaign_detail=$AgencyCampaignMasterTable ->checkCampaignExist(null,null,$login_agency_id,_LONG_TERM_NURTURE_X_DATE_MONTH_MAX,_CAMPAIGN_TYPE_LONG_TERM_NURTURE);
                    if(!empty($ltn_campaign_detail))
                    {
                        $campagin_trigger_date=$contactDetailsArray[$contact_id_final]['campagin_trigger_date'];
                        $date1 = date_create($todayDate);
                        $date2 = date_create($campagin_trigger_date);
                        // //difference between two dates
                        $diff = date_diff($date1,$date2);
                        $days_count=$diff->format("%a");
                        //echo 'days diff='.$days_count;
                        //echo  'days campagin_trigger_date='.$campagin_trigger_date;
                        // echo '<pre>';
                        //print_r($ltn_campaign_detail);
                        if(isset($ltn_campaign_detail['name']) && !empty($ltn_campaign_detail['name']) && isset($campagin_trigger_date) && !empty($campagin_trigger_date) && ($days_count > 0 && $days_count <=$before_days) && !in_array($ltn_campaign_detail['id'], $contact_active_campaigns))
                        {
                            $campagin_type_name=getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_LONG_TERM_NURTURE);
                            $date_time=date('M d, Y', strtotime($campagin_trigger_date));
                            $history_list .='<tr data-conact-id = "'.$contact_id.'" data-birth-date=""  id="'.$ltn_campaign_detail['id'].'" class="upcoming_campaign"><td>'.$ltn_campaign_detail['name'].'</td><td>'.$campagin_type_name.'</td><td>'.$date_time.'</td></tr>';
                            $no_records_found=_ID_SUCCESS;
                        }
                    }
                }

            }
            
            if($no_records_found==_ID_FAILED)
            {
                $history_list .='<tr><td colspan="8"><p class="text-center" style=" color:rgb(58 53 65 / 87%);text-transform: none !important;">No records found!</p></td></tr>';
            }
        
            $return['ContactCampaigns.getUpcomingCampaignsContactCard'] = [
                $contact_id => json_encode(array('status' => _ID_SUCCESS,'list'=>  $history_list))
            ];
            return $return;
        }catch (\Exception $e) {
			
            $txt=date('Y-m-d H:i:s').' :: Umcoming Campaign Lising Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
	}
	
	
	
	
	public static function getPausedCampaignListingContactCard($contact_id)
    {
		
		try
		{
			$myfile = fopen(ROOT."/logs/vuePausedListing.log", "a") or die("Unable to open file!"); 
            $CampaignRunningScheduleTable = TableRegistry::getTableLocator()->get('CampaignRunningSchedule');
            $contact_paused_campaigns = $CampaignRunningScheduleTable->getPausedCampaignByContactID($contact_id);
        
            $pausedCampaignsListing = '';
            
            if(isset($contact_paused_campaigns) && !empty($contact_paused_campaigns))
            {
                $count_campaigns = 1;
                foreach ($contact_paused_campaigns as $paused_campaigns) {
                    $days_paused=0;
                    
                    if(isset($paused_campaigns['pause_date']) && !empty($paused_campaigns['pause_date']))
                    {
                        $pause_date = new \DateTime($paused_campaigns['pause_date']);
                        $current_date = new \DateTime(date('Y-m-d H:i:s'));
                        $days_paused = $current_date->diff($pause_date)->format("%a");
                    }

                    //campaign progress
                    $campaign_progress=0;
                    if(isset($paused_campaigns['pause_date']) && !empty($paused_campaigns['pause_date']))
                    {
                        $pause_date = new \DateTime($paused_campaigns['pause_date']);
                        $campaign_start_date = new \DateTime($paused_campaigns['created']);
                        $campaign_progress = $campaign_start_date->diff($pause_date)->format("%a");
                    }
                    
                    $campaign_id=$paused_campaigns['id'];
                    $campagin_name=$paused_campaigns['agency_campaign_master']['name'];
                    $type="";
                    //echo '<pre>';
                    //print_r($associated_campaigns);die;
                    $referral_partner_img="";
                    //client referrer stage
                    $client_referrer_stage_text="";
                    if(isset($paused_campaigns['client_referrer_id']) && !empty($paused_campaigns['client_referrer_id']))
                    {
                    $client_referrer_stage_text='(Referrer Thank You)';
                    }
                    $getEntityDef=getEntityDef($err,_CAMPAIGN_TYPE,$paused_campaigns['agency_campaign_master']['type']);
                    $type=$getEntityDef;
                    
                    $pausedCampaignsListing.='<tr>
                    <td>'.ucfirst($campagin_name).$client_referrer_stage_text.'</td>
                    <td>'.$type.'</td>
                    <td>'.$days_paused.' Days</td>
                    <td>'.$campaign_progress.' Days</td>
                    <td><button type="button" class="v-btn v-btn--is-elevated v-btn--has-bg v-size--default success" onclick="contactResumeCampaign('.$campaign_id.','. $contact_id .')"><span class="mdi mdi-play"></span>Resume</td>
                    <td><a class="ml-auto btn btn-preview v-btn " title="Preview Upcoming Messages"   @ v-on:click.native=="showPausedCampaigns('.$campaign_id.','.$contact_id.')"><i style="color:white;" class="fas fa-eye plus-icon-css"></i>&nbsp;PREVIEW </a></td>
                    
                    </tr>';
                }
                
            }
            else
            {
                $pausedCampaignsListing.='<tr><td><p class="emply-msg text-center">No records found!</p></td></tr>';
            }
        
            $return['ContactCampaigns.getPausedCampaignListingContactCard'] = [
                $contact_id => json_encode(array('status' => _ID_SUCCESS,'list'=>  $pausedCampaignsListing))
            ];
            return $return;
        }catch (\Exception $e) {
			
            $txt=date('Y-m-d H:i:s').' :: Paused Campaign Lising Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
	
	}
	
	
	 /*
     * Paused Campaigns : get paused campaigns listing
     */
    public static function showPausedCampaigns($pausedCampaignId)
    {
      
        $session = Router::getRequest()->getSession(); 
        $AgencyTable = TableRegistry::getTableLocator()->get('Agency');
		$AgencyCampaignMasterTable = TableRegistry::getTableLocator()->get('AgencyCampaignMaster');
		$CampaignRunningScheduleTable = TableRegistry::getTableLocator()->get('CampaignRunningSchedule');
        $UsStatesTable = TableRegistry::getTableLocator()->get('UsStates');
        $AgencySmsTemplatesTable = TableRegistry::getTableLocator()->get('AgencySmsTemplates');
        $PhoneNumbersOptInOutStatusTable = TableRegistry::getTableLocator()->get('PhoneNumbersOptInOutStatus');
        $AgencyEmailTemplatesTable = TableRegistry::getTableLocator()->get('AgencyEmailTemplates');
        $AgencyTaskTemplatesTable = TableRegistry::getTableLocator()->get('AgencyTaskTemplates');
        $AgencyTransitionTemplatesTable = TableRegistry::getTableLocator()->get('AgencyTransitionTemplates');
        
        $CampaignRunningEmailSmsScheduleTable = TableRegistry::getTableLocator()->get('CampaignRunningEmailSmsSchedule');
		$ContactsTable = TableRegistry::getTableLocator()->get('Contacts');
       
		$login_agency_id= $session->read("Auth.User.agency_id");
        $campaign_id = $pausedCampaignId;
        
      
        $contact_id="";
        
        $campaign_id=$pausedCampaignId;
        
        $getCampaignDetailById = $CampaignRunningScheduleTable->getCampaignRunningScheduleByIdAnyStatus($campaign_id);
        if(isset($getCampaignDetailById) && !empty($getCampaignDetailById))
        {
            $contact_id = $getCampaignDetailById->contact_id;
        }

        
       
        $campaignRunningEmailSmsSchedule = $CampaignRunningEmailSmsScheduleTable->getPausedCampaignRunningScheduleListByContactID($campaign_id,$contact_id); 
        $contact_detail = $ContactsTable->contactDetails($contact_id);   
        
        
        $associatedCampaignsListing ='';
        $messages='';
        if(isset($campaignRunningEmailSmsSchedule) && !empty($campaignRunningEmailSmsSchedule))
        {
            $session = Router::getRequest()->getSession();
            $usersTimezone = 'America/Phoenix';
            $agency_time_zone = $session->read('Auth.User.agency_session_detail.time_zone');
            if(isset($agency_time_zone) && $agency_time_zone != ''){
                $usersTimezone = $agency_time_zone;
            }else {
                $agency_state_id = $session->read('Auth.User.agency_session_detail.us_state_id');
                if (isset($agency_state_id) && $agency_state_id != '') {
                    $stateDetail = $UsStatesTable->stateDetail($agency_state_id);
                    if (isset($stateDetail) && !empty($stateDetail)) {
                        $usersTimezone = $stateDetail->time_zone;
                    }
                }
            }
            foreach ($campaignRunningEmailSmsSchedule as $runningEmailSmsSchedule)
            {
                $template_id = $runningEmailSmsSchedule['campaign_email_sms_templates_id'];
                $template_type = $runningEmailSmsSchedule['type'];
                $executionTime = $runningEmailSmsSchedule['execution_time'];


                if($template_type == 1)
                {
                    $smsTemplates = $AgencySmsTemplatesTable->getSmsTemplatesDetailById($template_id);
                    
                    $scheduleDate = CommonComponent::convertUtcToEmployeeTimeZone($usersTimezone,$executionTime);
                    
                    $dateTimeCampaignRunningSchedule = date('M d, Y h:i A', strtotime($scheduleDate));
                    
                    if(isset($smsTemplates) && !empty($smsTemplates))
                    {
                        $associatedCampaignsListing.='
                        <li>
                            <span class="time">'.$dateTimeCampaignRunningSchedule.'</span>
                            <span class="dot">
                                <i class="fas fa-sms" style="cursor:pointer;font-size: 18px;"></i>
                            </span>
                            <div class="content">
                                <div id = "campaign-sms-view-mode_'.$smsTemplates['id'].'">
                                    <div class = "row">
                                        <h3 class="subtitle col-lg-8">'.ucfirst($smsTemplates['title']).'</h3>';
                                        
                                        $associatedCampaignsListing.='
                                    </div>
                                    <p>'.$smsTemplates['content'].'</p>
                                </div>
                            </div>
                        </li>';
                    }
                }
                else if($template_type == 2)
                {
                    $emailTemplates = $AgencyEmailTemplatesTable->getEmailTemplatesDetailById($template_id);
                    $scheduleDate = CommonComponent::convertUtcToEmployeeTimeZone($usersTimezone,$executionTime);
                    $dateTimeCampaignRunningSchedule = date('M d, Y h:i A', strtotime($scheduleDate));
                    
                    if(isset($emailTemplates) && !empty($emailTemplates)){
                        $associatedCampaignsListing.=
                        '<li>
                                <span class="time">'.$dateTimeCampaignRunningSchedule.'</span>
                                <span class="dot">
                                    <i class="fas fa-envelope" style="cursor:pointer;font-size: 18px;"></i>
                                </span>
                                <div class="content">
                                    <div id = "campaign-email-view-mode_'.$emailTemplates['id'].'">
                                        <div class = "row">
                                            <h3 class="subtitle col-lg-8">'.ucfirst($emailTemplates['title']).'</h3>                                    
                                        </div>
                                        <p><strong>'.strip_tags($emailTemplates['subject']).'</strong></p>
                                        <p>'.strip_tags($emailTemplates['content']).'</p>
                                    </div>
                                </div>
                            </li>';
                    }
                }
                else if($template_type == _CAMPAIGN_TASK)
                {
                    $taskTemplates = $AgencyTaskTemplatesTable->getTaskTemplatesDetailById($template_id);
                    
                    $scheduleDate = CommonComponent::convertUtcToEmployeeTimeZone($usersTimezone,$executionTime);
                    $dateTimeCampaignRunningSchedule = date('M d, Y h:i A', strtotime($scheduleDate));
                    
                    if(isset($taskTemplates) && !empty($taskTemplates)){
                        $associatedCampaignsListing.='
                            <li>
                                <span class="time">'.$dateTimeCampaignRunningSchedule.'</span>
                                <span class="dot">
                                    <i class="fa fa-tasks" style="cursor:pointer;font-size: 18px;"></i>
                                </span>
                                <div class="content">
                                    <div id = "campaign-task-view-mode_'.$taskTemplates['id'].'">
                                        <div class = "row">
                                            <h3 class="subtitle col-lg-8">'.ucfirst($taskTemplates['title']).'</h3>
                                            
                                        </div>
                                        <p>'.$taskTemplates['description'].'</p>
                                    </div>
                                </div>
                            </li>';
                    }
                }
                else if($template_type == _CAMPAIGN_TYPE_TRANSITION)
                {
                    $transitionTemplates = $AgencyTransitionTemplatesTable->getTransitionTemplatesDetailById($template_id);
                    
                    $scheduleDate = CommonComponent::convertUtcToEmployeeTimeZone($usersTimezone,$executionTime);
                    $dateTimeCampaignRunningSchedule = date('M d, Y h:i A', strtotime($scheduleDate));
                    //campaign transtion list
                    if(isset($this->request->data['contact_business_id']) && !empty($this->request->data['contact_business_id']))
                    {
                        $agency_new_lead_campaign_list = $AgencyCampaignMasterTable->getAllActiveAgencyCampaignsByType($login_agency_id,_CAMPAIGN_TYPE_NEW_LEAD,_LINE_COMMERCIAL);
                        $agency_pipeline_campaign_list = $AgencyCampaignMasterTable->getAllActiveAgencyCampaignsByType($login_agency_id,_CAMPAIGN_TYPE_PIPELINE,_LINE_COMMERCIAL);
                    }
                    else
                    {
                        $agency_new_lead_campaign_list = $AgencyCampaignMasterTable->getAllActiveAgencyCampaignsByType($login_agency_id,_CAMPAIGN_TYPE_NEW_LEAD);
                        $agency_pipeline_campaign_list = $AgencyCampaignMasterTable->getAllActiveAgencyCampaignsByType($login_agency_id,_CAMPAIGN_TYPE_PIPELINE);
                    }
                    $campaign_transition_arr = [];
                    foreach ($agency_new_lead_campaign_list as $key => $value) {
                                $campaign_transition_arr[$value['id']] = $value['name'];
                        }
                        foreach ($agency_pipeline_campaign_list as $key => $value) {
                                $campaign_transition_arr[$value['id']] = $value['name'];
                        }
                    if(isset($transitionTemplates) && !empty($transitionTemplates)){
                        $associatedCampaignsListing.='
                            <li>
                                <span class="time">'.$dateTimeCampaignRunningSchedule.'</span>
                                <span class="dot">
                                    <i class="fas fa-exchange-alt" style="cursor:pointer;font-size: 18px;"></i>
                                </span>
                                <div class="content">
                                    <div id = "campaign-task-view-mode_'.$transitionTemplates['id'].'">
                                        <div class = "row">
                                            <h3 class="subtitle col-lg-8">'.ucfirst($transitionTemplates['title']).'</h3>
                                            
                                        </div>
                                        <p>'.$campaign_transition_arr[$transitionTemplates['transition_campaign_id']].'</p>
                                    </div>
                                </div>
                            </li>';
                    }
                }
            }
            //echo json_encode(array('status' => _ID_SUCCESS,'listing' => $associatedCampaignsListing));

            $return['ContactCampaigns.showPausedCampaigns'] = [
                $pausedCampaignId => json_encode(array('status' => _ID_SUCCESS,'listing' => $associatedCampaignsListing))
            ];
            return $return;
        }
        else
        {
            $messages .= 'No Messages';
            //echo json_encode(array('status' => _ID_FAILED,'messages' => $messages));
            $return['ContactCampaigns.showPausedCampaigns'] = [
                $pausedCampaignId => json_encode(array('status' => _ID_SUCCESS,'messages' => $messages))
            ];
            return $return;
        }
    }

    public static function getPausedCampaignListingContactCardNew($contactId){
		
		
        $CampaignRunningScheduleTable = TableRegistry::getTableLocator()->get('CampaignRunningSchedule');
        $result = $CampaignRunningScheduleTable->getPausedCampaignByContactID($contactId);
    
        $return['ContactCampaigns.getPausedCampaignListingContactCardNew'] = [
            $contactId => $result
        ];
        return $return;
    
    }

    public static function showRunningScheduleCampaigns($activeCampaignId)
    {
        $session = Router::getRequest()->getSession(); 
        $AgencyTable = TableRegistry::getTableLocator()->get('Agency');
		$AgencyCampaignMasterTable = TableRegistry::getTableLocator()->get('AgencyCampaignMaster');
		$CampaignRunningScheduleTable = TableRegistry::getTableLocator()->get('CampaignRunningSchedule');
        $UsStatesTable = TableRegistry::getTableLocator()->get('UsStates');
        $AgencySmsTemplatesTable = TableRegistry::getTableLocator()->get('AgencySmsTemplates');
        $PhoneNumbersOptInOutStatusTable = TableRegistry::getTableLocator()->get('PhoneNumbersOptInOutStatus');
        $AgencyEmailTemplatesTable = TableRegistry::getTableLocator()->get('AgencyEmailTemplates');
        $AgencyTaskTemplatesTable = TableRegistry::getTableLocator()->get('AgencyTaskTemplates');
        $AgencyTransitionTemplatesTable = TableRegistry::getTableLocator()->get('AgencyTransitionTemplates');
        
        $CampaignRunningEmailSmsScheduleTable = TableRegistry::getTableLocator()->get('CampaignRunningEmailSmsSchedule');
		$ContactsTable = TableRegistry::getTableLocator()->get('Contacts');
       
		$login_agency_id= $session->read("Auth.User.agency_id");

        
      
        $contact_id="";
        
        $campaign_id=$activeCampaignId;
        $getCampaignDetailById = $CampaignRunningScheduleTable->getCampaignRunningScheduleByIdAnyStatus($campaign_id);
        if(isset($getCampaignDetailById) && !empty($getCampaignDetailById))
        {
            $contact_id = $getCampaignDetailById->contact_id;
        }
        $agencyDetail   = $AgencyTable->agencyDetails($login_agency_id);
       
        $campaignRunningEmailSmsSchedule = $CampaignRunningEmailSmsScheduleTable->getCampaignRunningScheduleListByContactID($campaign_id,$contact_id); 
        $contact_detail = $ContactsTable->contactDetails($contact_id); 
		$contact_business_id="''";			
        
        
        
        //print_r($campaignRunningEmailSmsSchedule);die;
        $associatedCampaignsListing ='';
        $messages='';
        if(isset($campaignRunningEmailSmsSchedule) && !empty($campaignRunningEmailSmsSchedule))
        {
            $CampaignList = [];
            $templateCount = 0;
            foreach ($campaignRunningEmailSmsSchedule as $runningEmailSmsSchedule)
            {
                $template_id = $runningEmailSmsSchedule['campaign_email_sms_templates_id'];
                $template_type = $runningEmailSmsSchedule['type'];
                $executionTime = $runningEmailSmsSchedule['execution_time'];
                
                $usersTimezone='America/Phoenix';
               

                if(!empty($agencyDetail['us_state_id']))
                {
                  $stateDetail = $UsStatesTable->stateDetail($agencyDetail['us_state_id']);
                }        
                if(isset($agencyDetail['time_zone']) && !empty($agencyDetail['time_zone']))
                {
                  $usersTimezone =  $agencyDetail['time_zone'];
                }
                else if(isset($stateDetail) && !empty($stateDetail))
                {
                  $usersTimezone =  $stateDetail->time_zone;
                }

                if($template_type == 1)
                {
                    $smsTemplates = $AgencySmsTemplatesTable->getSmsTemplatesDetailById($template_id);
                    $optInOutStatus = $PhoneNumbersOptInOutStatusTable->checkOptInOptOutStatus($login_agency_id,$contact_detail['phone']);
                   
                    $scheduleDate = CommonComponent::convertUtcToEmployeeTimeZone($usersTimezone,$executionTime);
                    
                    $dateTimeCampaignRunningSchedule = date('M d, Y h:i A', strtotime($scheduleDate));
                    
                    if(isset($smsTemplates) && !empty($smsTemplates))
                    {
                        $checkCampaignType = $CampaignRunningScheduleTable->checkOptInCampaign($login_agency_id,$campaign_id);

                        $CampaignList[$templateCount]['type'] = 1;
                        $CampaignList[$templateCount]['campaignScheduledTime'] = $dateTimeCampaignRunningSchedule;
                        $CampaignList[$templateCount]['templateId'] = $smsTemplates['id'];
                        $CampaignList[$templateCount]['templateTitle'] = ucfirst($smsTemplates['title']);
                        $CampaignList[$templateCount]['runningEmailSmsScheduledId'] = $runningEmailSmsSchedule['id'];
                        $CampaignList[$templateCount]['optInOutStatus'] = (isset($optInOutStatus) && $optInOutStatus != '') ? $optInOutStatus['status'] : null;
                        $CampaignList[$templateCount]['campaignType'] = ($checkCampaignType['agency_campaign_master']['type']) ? $checkCampaignType['agency_campaign_master']['type'] : null;
                        $CampaignList[$templateCount]['login_agency_id'] = $login_agency_id;
                        $CampaignList[$templateCount]['contact_id'] = $contact_id;
                        $CampaignList[$templateCount]['template_id'] = $template_id;
                        $CampaignList[$templateCount]['campaign_id'] = $campaign_id;
                        $CampaignList[$templateCount]['contact_business_id'] = $contact_business_id;
                        $CampaignList[$templateCount]['subject'] = null;
                        $CampaignList[$templateCount]['content'] = strip_tags($smsTemplates['content']);

                    }
                }
                else if($template_type == 2)
                {
                    $emailTemplates = $AgencyEmailTemplatesTable->getEmailTemplatesDetailById($template_id);
                    $scheduleDate = CommonComponent::convertUtcToEmployeeTimeZone($usersTimezone,$executionTime);
                    $dateTimeCampaignRunningSchedule = date('M d, Y h:i A', strtotime($scheduleDate));
                    
                    if(isset($emailTemplates) && !empty($emailTemplates)){

                        $CampaignList[$templateCount]['type'] = 2;
                        $CampaignList[$templateCount]['campaignScheduledTime'] = $dateTimeCampaignRunningSchedule;
                        $CampaignList[$templateCount]['templateId'] = $emailTemplates['id'];
                        $CampaignList[$templateCount]['templateTitle'] = ucfirst($emailTemplates['title']);
                        $CampaignList[$templateCount]['runningEmailSmsScheduledId'] = $runningEmailSmsSchedule['id'];
                        $CampaignList[$templateCount]['optInOutStatus'] = null;
                        $CampaignList[$templateCount]['campaignType'] = null;
                        $CampaignList[$templateCount]['login_agency_id'] = $login_agency_id;
                        $CampaignList[$templateCount]['contact_id'] = $contact_id;
                        $CampaignList[$templateCount]['template_id'] = $template_id;
                        $CampaignList[$templateCount]['campaign_id'] = $campaign_id;
                        $CampaignList[$templateCount]['contact_business_id'] = $contact_business_id;
                        $CampaignList[$templateCount]['subject'] = strip_tags($emailTemplates['subject']);
                        $CampaignList[$templateCount]['content'] = strip_tags($emailTemplates['content']);

                       
                    }
                }
                else if($template_type == _CAMPAIGN_TASK)
                {
                    $taskTemplates = $AgencyTaskTemplatesTable->getTaskTemplatesDetailById($template_id);
                    
                    $scheduleDate = CommonComponent::convertUtcToEmployeeTimeZone($usersTimezone,$executionTime);
                    $dateTimeCampaignRunningSchedule = date('M d, Y h:i A', strtotime($scheduleDate));
                    
                    if(isset($taskTemplates) && !empty($taskTemplates)){

                        $CampaignList[$templateCount]['type'] = _CAMPAIGN_TASK;
                        $CampaignList[$templateCount]['campaignScheduledTime'] = $dateTimeCampaignRunningSchedule;
                        $CampaignList[$templateCount]['templateId'] = $taskTemplates['id'];
                        $CampaignList[$templateCount]['templateTitle'] = ucfirst($taskTemplates['title']);
                        $CampaignList[$templateCount]['runningEmailSmsScheduledId'] = $runningEmailSmsSchedule['id'];
                        $CampaignList[$templateCount]['optInOutStatus'] = null;
                        $CampaignList[$templateCount]['campaignType'] = null;
                        $CampaignList[$templateCount]['login_agency_id'] = $login_agency_id;
                        $CampaignList[$templateCount]['contact_id'] = $contact_id;
                        $CampaignList[$templateCount]['template_id'] = $template_id;
                        $CampaignList[$templateCount]['campaign_id'] = $campaign_id;
                        $CampaignList[$templateCount]['contact_business_id'] = $contact_business_id;
                        $CampaignList[$templateCount]['subject'] = '';
                        $CampaignList[$templateCount]['content'] = $taskTemplates['description'];

                        
                    }
                }
                else if($template_type == _CAMPAIGN_TYPE_TRANSITION)
                {
                    $transitionTemplates = $AgencyTransitionTemplatesTable->getTransitionTemplatesDetailById($template_id);
                    
                    $scheduleDate = CommonComponent::convertUtcToEmployeeTimeZone($usersTimezone,$executionTime);
                    $dateTimeCampaignRunningSchedule = date('M d, Y h:i A', strtotime($scheduleDate));
                    //campaign transtion list
                    if(isset($getCampaignDetailById['contact_business_id']) && !empty($getCampaignDetailById['contact_business_id']))
                    {
                        $agency_new_lead_campaign_list = $AgencyCampaignMasterTable->getAllActiveAgencyCampaignsByType($login_agency_id,_CAMPAIGN_TYPE_NEW_LEAD,_LINE_COMMERCIAL);
                        $agency_pipeline_campaign_list = $AgencyCampaignMasterTable->getAllActiveAgencyCampaignsByType($login_agency_id,_CAMPAIGN_TYPE_PIPELINE,_LINE_COMMERCIAL);
                    }
                    else
                    {

                        $agency_new_lead_campaign_list = $AgencyCampaignMasterTable->getAllActiveAgencyCampaignsByType($login_agency_id,_CAMPAIGN_TYPE_NEW_LEAD);
                        $agency_pipeline_campaign_list = $AgencyCampaignMasterTable->getAllActiveAgencyCampaignsByType($login_agency_id,_CAMPAIGN_TYPE_PIPELINE);
                    }
                    $campaign_transition_arr = [];
                    foreach ($agency_new_lead_campaign_list as $key => $value) {
                                $campaign_transition_arr[$value['id']] = $value['name'];
                        }
                        foreach ($agency_pipeline_campaign_list as $key => $value) {
                                $campaign_transition_arr[$value['id']] = $value['name'];
                        }
                    if(isset($transitionTemplates) && !empty($transitionTemplates)){

                        $CampaignList[$templateCount]['type'] = _CAMPAIGN_TYPE_TRANSITION;
                        $CampaignList[$templateCount]['campaignScheduledTime'] = $dateTimeCampaignRunningSchedule;
                        $CampaignList[$templateCount]['templateId'] = $transitionTemplates['id'];
                        $CampaignList[$templateCount]['templateTitle'] = ucfirst($transitionTemplates['title']);
                        $CampaignList[$templateCount]['runningEmailSmsScheduledId'] = $runningEmailSmsSchedule['id'];
                        $CampaignList[$templateCount]['optInOutStatus'] = null;
                        $CampaignList[$templateCount]['campaignType'] = null;
                        $CampaignList[$templateCount]['login_agency_id'] = $login_agency_id;
                        $CampaignList[$templateCount]['contact_id'] = $contact_id;
                        $CampaignList[$templateCount]['template_id'] = $template_id;
                        $CampaignList[$templateCount]['campaign_id'] = $campaign_id;
                        $CampaignList[$templateCount]['contact_business_id'] = $contact_business_id;
                        $CampaignList[$templateCount]['transitionTemplateId'] = $campaign_transition_arr[$transitionTemplates['transition_campaign_id']];

                        
                    }
                }
                $templateCount++;
            }
           // echo json_encode(array('status' => _ID_SUCCESS,'listing' => $associatedCampaignsListing,'is_sms_subscribe'=>$contact_detail['is_sms_subscribe']));
            
            $return['ContactCampaigns.showRunningScheduleCampaigns'] = [
                $activeCampaignId => json_encode(array('status' => _ID_SUCCESS,'listing' => $associatedCampaignsListing,'is_sms_subscribe'=>$contact_detail['is_sms_subscribe'],'list' => $CampaignList))
            ];
            return $return;
           
        }
        else
        {
            $messages .= 'No Upcoming Messages';
           // echo json_encode(array('status' => _ID_FAILED,'messages' => $messages));

            $return['ContactCampaigns.showRunningScheduleCampaigns'] = [
                $activeCampaignId => json_encode(array('status' => _ID_SUCCESS,'messages' => $messages))
            ];
            return $return;
        }
    }
	
	public static function contactPauseCampaign($campaign_running_schedule_id)
    {
		$response =[];
        try
		{
			$myfile = fopen(ROOT."/logs/vuecontactPauseCampaign.log", "a") or die("Unable to open file!");

            $UsStatesTable = TableRegistry::getTableLocator()->get('UsStates');
            $users = TableRegistry::getTableLocator()->get('Users');
            $session = Router::getRequest()->getSession();
            $usersTimezone = 'America/Phoenix';
            $agencyTimeZone = $session->read('Auth.User.agency_session_detail.time_zone');
            $loginUserId = $session->read('Auth.User.id');
            if(isset($agencyTimeZone) && $agencyTimeZone != '')
            {
                $usersTimezone = $agencyTimeZone;
            }
            else
            {
                $agencyStateId = $session->read('Auth.User.agency_session_detail.us_state_id');
                if (isset($agencyStateId) && $agencyStateId != '')
                {
                    $stateDetail = $UsStatesTable->stateDetail($agencyStateId);
                    if (isset($stateDetail) && !empty($stateDetail))
                    {
                        $usersTimezone = $stateDetail->time_zone;
                    }
                }
            }
			
			if(!empty($campaign_running_schedule_id))
			{
				$CampaignRunningScheduleTable = TableRegistry::getTableLocator()->get('CampaignRunningSchedule');
				$agencyCampaignDetail = TableRegistry::getTableLocator()->get('AgencyCampaignMaster');
                $currentCampaign = $CampaignRunningScheduleTable->get($campaign_running_schedule_id);
                $campaign = $agencyCampaignDetail->get($currentCampaign['campaign_id']);
				$CampaignRunningEmailSmsScheduleTable = TableRegistry::getTableLocator()->get('CampaignRunningEmailSmsSchedule');
				//update campaign_running_schedule status to pause
				$CampaignRunningScheduleTable->updateAll(['status'=>_CAMPAIGN_STATUS_PAUSE,'pause_date'=>date('Y-m-d H:i:s')],['id'=>$campaign_running_schedule_id]);
				$CampaignRunningEmailSmsScheduleTable->updateAll(['status'=>_CAMPAIGN_STATUS_PAUSE],['campaign_running_schedule_id'=>$campaign_running_schedule_id,'status in'=>[_EMAIL_SMS_SENT_STATUS_PENDING,_EMAIL_SMS_SENT_STATUS_PROCESSING]]);
				//update campaign_running_email_sms_schedule status to pause
                $campaignStartDateTime = CommonFunctions::convertUtcToEmployeeTimeZone($usersTimezone,date("M d, Y H:i:s"));
                $campaignStartDate = date('M d, Y', strtotime($campaignStartDateTime));
                $campaignStartTime = date('h:i:s a', strtotime($campaignStartDateTime));
                if($loginUserId)
                {
                   $userData = $users->get($loginUserId);
                   $firstName = ($userData->first_name) ? ucwords($userData->first_name) : '';
                   $lastName = ($userData->last_name) ? ucwords($userData->last_name) : '';
                   $userName = $firstName .' '. $lastName;
                }
                else
                {
                    $userName = "Better Agency";
                }
                $message = $userName." paused the \"". ucwords($campaign['name']) ."\" campaign on ". $campaignStartDate ." at ". $campaignStartTime . ".";
                $contactLogsArray['contact_id'] = $currentCampaign['contact_id'];
                $contactLogsArray['contact_business_id'] = $currentCampaign['contact_business_id'];
                $contactLogsArray['user_id'] = $loginUserId;
                $contactLogsArray['platform'] = _PLATFORM_TYPE_SYSTEM;
                $contactLogsArray['message'] = $message;
                CommonFunctions::insertContactLogsOnUpdate($contactLogsArray);
				
				$response =  json_encode(["status"=>_ID_SUCCESS]);
			}
			else
			{
				
				$response =  json_encode(["status"=>_ID_FAILED, "msg"=> 'Warning, Something went wrong try again.' ]);
				
			}
			
		}
		catch (\Exception $e) {
			
            $txt=date('Y-m-d H:i:s').' :: Paused Campaign Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
			$response =  json_encode(["status"=>_ID_FAILED, "msg"=> 'Warning, Something went wrong try again.' ]);
        }
		
		return $response;
	}
	
	public static function contactResumeCampaign($campaign_running_schedule_id)
    {
		$response =[];
        try
		{
			$myfile = fopen(ROOT."/logs/vuecontactResumeCampaign.log", "a") or die("Unable to open file!");

            $UsStatesTable = TableRegistry::getTableLocator()->get('UsStates');
            $users = TableRegistry::getTableLocator()->get('Users');
            $session = Router::getRequest()->getSession();
            $usersTimezone = 'America/Phoenix';
            $agencyTimeZone = $session->read('Auth.User.agency_session_detail.time_zone');
            $loginUserId = $session->read('Auth.User.id');
            if(isset($agencyTimeZone) && $agencyTimeZone != '')
            {
                $usersTimezone = $agencyTimeZone;
            }
            else
            {
                $agencyStateId = $session->read('Auth.User.agency_session_detail.us_state_id');
                if (isset($agencyStateId) && $agencyStateId != '')
                {
                    $stateDetail = $UsStatesTable->stateDetail($agencyStateId);
                    if (isset($stateDetail) && !empty($stateDetail))
                    {
                        $usersTimezone = $stateDetail->time_zone;
                    }
                }
            }
			if(!empty($campaign_running_schedule_id))
			{
				$CampaignRunningScheduleTable = TableRegistry::getTableLocator()->get('CampaignRunningSchedule');
                $agencyCampaignDetail = TableRegistry::getTableLocator()->get('AgencyCampaignMaster');
                $campaign_running_schedule_detail = $CampaignRunningScheduleTable->getCRSByIdWithCampaign($campaign_running_schedule_id);
                $campaign = $agencyCampaignDetail->get($campaign_running_schedule_detail['campaign_id']);
                if(isset($campaign_running_schedule_detail) && !empty($campaign_running_schedule_detail))
				{
					CommonFunctions::resumeCampaign($campaign_running_schedule_id);
                    $campaignStartDateTime = CommonFunctions::convertUtcToEmployeeTimeZone($usersTimezone,date("M d, Y H:i:s"));
                    $campaignStartDate = date('M d, Y', strtotime($campaignStartDateTime));
                    $campaignStartTime = date('h:i:s a', strtotime($campaignStartDateTime));
                    if($loginUserId)
                    {
                       $userData = $users->get($loginUserId);
                       $firstName = ($userData->first_name) ? ucwords($userData->first_name) : '';
                       $lastName = ($userData->last_name) ? ucwords($userData->last_name) : '';
                       $userName = $firstName .' '. $lastName;
                    }
                    else
                    {
                        $userName = "Better Agency";
                    }
                    $message = $userName." resumed the \"". ucwords($campaign['name']) ."\" campaign on ". $campaignStartDate ." at ". $campaignStartTime . ".";
                    $contactLogsArray['contact_id'] = $campaign_running_schedule_detail['contact_id'];
                    $contactLogsArray['contact_business_id'] = $campaign_running_schedule_detail['contact_business_id'];
                    $contactLogsArray['user_id'] = $loginUserId;
                    $contactLogsArray['platform'] = _PLATFORM_TYPE_SYSTEM;
                    $contactLogsArray['message'] = $message;
                    CommonFunctions::insertContactLogsOnUpdate($contactLogsArray);
					$response =  json_encode(["status"=>_ID_SUCCESS]);
				}
				else
				{
					 $response =  json_encode(["status"=>_ID_FAILED]);
				}
			}
			else
			{
				
				$response =  json_encode(["status"=>_ID_FAILED, "msg"=> 'Warning, Something went wrong try again.' ]);
				
			}
			
		}
		catch (\Exception $e) {
			
            $txt=date('Y-m-d H:i:s').' :: Resume Campaign Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
			$response =  json_encode(["status"=>_ID_FAILED, "msg"=> 'Warning, Something went wrong try again.' ]);
        }
		
		return $response;
	}
	
	 /**
    *This function is uset to stop the upcoming campaign
    **/
    public function contactStopUpcomingCampaign($data)
    {
		$response =[];
        
        if(!empty($data['contact_id']))
        {
           $contact_id =  $data['contact_id'];
        }
        
        if(!empty($data['agency_campaign_id']))
        {
           $agency_campaign_id =  $data['agency_campaign_id'];
        }
        if(!empty($data['coming_birth_year']))
        {
           $coming_birth_year =  $data['coming_birth_year'];
        }
        if(isset($contact_id) && !empty($contact_id) && isset($agency_campaign_id) && !empty($agency_campaign_id))
        {
            if(CommonFunctions::contactStopUpcomingCampaign($contact_id,null,$agency_campaign_id,$coming_birth_year))
            {
               $response = json_encode(["status"=>_ID_SUCCESS]);
            }
			else{
			
				$response = json_encode(["status"=>_ID_FAILED]);
			}

        }
		else
		{
			
			$response = json_encode(["status"=>_ID_FAILED]);
		}
		return $response;
    }

    public function getAllAvailableCampaignsContactCard($contact_id)
    { 
        $session = Router::getRequest()->getSession(); 
        $login_agency_id = $session->read('Auth.User.agency_id');
        $contacts = TableRegistry::getTableLocator()->get('Contacts');
        $contactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
        $phoneNumbersOptInOutStatus = TableRegistry::getTableLocator()->get('PhoneNumbersOptInOutStatus');
        $campaignRunningSchedule = TableRegistry::getTableLocator()->get('CampaignRunningSchedule');
        $campaignGroup = TableRegistry::getTableLocator()->get('CampaignGroup');
        $agencyCampaignMaster = TableRegistry::getTableLocator()->get('AgencyCampaignMaster');
        $campaignRunningSchedule = TableRegistry::getTableLocator()->get('CampaignRunningSchedule');
        $campaignRunningSchedule = TableRegistry::getTableLocator()->get('CampaignRunningSchedule');


        $list_array = array();

		$contact_opportunities = $contactOpportunities->find('all')->where(['contact_id'=>$contact_id,'contact_business_id IS NULL'])->contain(['InsuranceTypes'])->toArray();
		$contactDetail = $contacts->contactDetails($contact_id);
		$checkOptOut=array();
		$contactPhone = '';
		if(isset($contactDetail['phone']) && !empty($contactDetail['phone'])){
			$contactPhone=$contactDetail['phone'];
		}
		$checkOptOut = $phoneNumbersOptInOutStatus->checkOptInOptOutStatus($login_agency_id,$contactPhone);


        $contact_opportunities_insurance_type_pipeline = [];
        $contact_opportunities_insurance_type_renewal = [];
        $contact_opportunities_insurance_type_cross_sell = [];

        foreach ($contact_opportunities as $key => $value)
        {
            if($value['pipeline_stage']==_PIPELINE_STAGE_WON)
            {
                $contact_opportunities_insurance_type_renewal[]=$value['insurance_type_id'];
                $contact_opportunities_insurance_type_cross_sell[]=$value['insurance_type_id'];
            }
        }

        //contact active campaigns

        $campaignRunningSchedule=$campaignRunningSchedule->find('all')->where(['contact_id'=>$contact_id,'status'=>_RUN_SCHEDULE_STATUS_ACTIVE])->toArray();


        $contact_active_campaigns = [];
        foreach ($campaignRunningSchedule as $key => $value)
        {
            $contact_active_campaigns[]=$value['campaign_id'];
        }

        //if prospect list pipeline campaign

        if($contactDetail['lead_type'] ==_CONTACT_TYPE_LEAD)
        {

			$campaign_group_with_campaign_data_nn = [];
			$all_active_campaign_arr_nn=array();

			$campagin_group_ids_arr=array(_NEW_LEAD_RELATED_CAMPAIGNS,_SALES_PIPELINE_RELATED_CAMPAIGNS,_CROSS_SELL_RELATED_CAMPAIGNS,_SERVICE_PIPELINE_RELATED_CAMPAIGNS,_POST_PIPELINE_RELATED_CAMPAIGNS,_OPT_IN_PHASE_RELATED_CAMPAIGNS, _CUSTOM_RELATED_CAMPAIGN);




			$all_active_campaign_group_arr = $campaignGroup->getAllActiveCampaignGroup();

			$all_active_campaign_arr = $agencyCampaignMaster->find('all')->where(['agency_id' => $login_agency_id, 'type IN'=>[_CAMPAIGN_TYPE_PIPELINE,_CAMPAIGN_TYPE_CLIENT_WELCOME,_CAMPAIGN_TYPE_CARRIER_INSOLVENCY,_CAMPAIGN_TYPE_LOAN_OFFICER_HOME_CONDO_PROPERTY,_CAMPAIGN_TYPE_SMS_OPT_IN,_CAMPAIGN_TYPE_PENDING_CANCELLATION, _CAMPAIGN_TYPE_CUSTOM_CAMPAIGN],'turn_on_off' => _ID_STATUS_ACTIVE ,'status' => _ID_STATUS_ACTIVE,'personal_commercial_line'=>_LINE_PERSONAL])->hydrate(false)->toArray();

			$new_lead_campaigns = $agencyCampaignMaster->find('all')->where(['agency_id' => $login_agency_id, 'type'=>_CAMPAIGN_TYPE_NEW_LEAD,'turn_on_off' => _ID_STATUS_ACTIVE ,'status' => _ID_STATUS_ACTIVE,'personal_commercial_line'=>_LINE_PERSONAL])->hydrate(false)->toArray();

			//general new lead campaign
			$general_new_lead_campaigns = $agencyCampaignMaster->find('all')->where(['agency_id' => $login_agency_id, 'type'=>_CAMPAIGN_TYPE_GENERAL_NEW_LEAD,'turn_on_off' => _ID_STATUS_ACTIVE ,'status' => _ID_STATUS_ACTIVE,'personal_commercial_line'=>_LINE_PERSONAL])->hydrate(false)->toArray();


			if(isset($general_new_lead_campaigns) && !empty($general_new_lead_campaigns))
			{
				foreach($general_new_lead_campaigns as $general_new_lead_campaign){
					array_push($new_lead_campaigns,$general_new_lead_campaign);
				}
			}


			if(isset($new_lead_campaigns))
			{
				foreach ($new_lead_campaigns as $value) {
				$all_active_campaign_arr_nn[$value['campaign_group_id']][$value['id']]['id'] = $value['id'];
				$all_active_campaign_arr_nn[$value['campaign_group_id']][$value['id']]['name'] = $value['name'];
				$all_active_campaign_arr_nn[$value['campaign_group_id']][$value['id']]['personal_commercial_line'] = $value['personal_commercial_line'];
				$all_active_campaign_arr_nn[$value['campaign_group_id']][$value['id']]['type'] = $value['type'];
				}

			}


			foreach ($all_active_campaign_arr as $value) {
			$all_active_campaign_arr_nn[$value['campaign_group_id']][$value['id']]['id'] = $value['id'];
			$all_active_campaign_arr_nn[$value['campaign_group_id']][$value['id']]['name'] = $value['name'];
			$all_active_campaign_arr_nn[$value['campaign_group_id']][$value['id']]['personal_commercial_line'] = $value['personal_commercial_line'];
			$all_active_campaign_arr_nn[$value['campaign_group_id']][$value['id']]['type'] = $value['type'];
			}


			foreach ($all_active_campaign_group_arr as $all_active_campaign_group)
			{
				if(in_array($all_active_campaign_group->id, $campagin_group_ids_arr))
				{
					$campaign_group_with_campaign_data_nn[$all_active_campaign_group->id]['group_info'] = $all_active_campaign_group->group_name;
					if(isset($all_active_campaign_arr_nn[$all_active_campaign_group->id])){
						$campaign_group_with_campaign_data_nn[$all_active_campaign_group->id]['campaign_info'] = $all_active_campaign_arr_nn[$all_active_campaign_group->id];
					}
				}
			}



			if(isset($campaign_group_with_campaign_data_nn) && !empty($campaign_group_with_campaign_data_nn)){


                foreach ($campaign_group_with_campaign_data_nn as $key => $campaign_group_with_campaign)
                {

                    if(isset($campaign_group_with_campaign['campaign_info']) && !empty($campaign_group_with_campaign['campaign_info']))
                    {

    					foreach ($campaign_group_with_campaign['campaign_info'] as $key1 => $campaign) {
                            $new_pipeline_stage_id=$pipeline_stage_id;
                            if($campaign['type'] == _CAMPAIGN_TYPE_PIPELINE)
                            {

                                $agencyCampaignDetailStage = $agencyCampaignMaster->agencyCampaignAndPipelineStageMasterDetail($campaign['id']);
                                if(!empty($agencyCampaignDetailStage) && isset($agencyCampaignDetailStage->agency_campaign_pipeline_stage_master['pipeline_stage_id']) && !empty($agencyCampaignDetailStage->agency_campaign_pipeline_stage_master['pipeline_stage_id']))
                                {
                                    $new_pipeline_stage_id = $agencyCampaignDetailStage->agency_campaign_pipeline_stage_master['pipeline_stage_id'];
                                }
                            }

                            else
                            {

                                $new_pipeline_stage_id = $pipeline_stage_id;
                            }


                           if($campaign['type'] == 1 && $contactDetail['lead_source_type']== null)
                           {
                                $newgroupArray = [];
                           }else{
                               $campaignGroupName = "";
                               if($key == _CUSTOM_RELATED_CAMPAIGN)
                               {
                                   $campaignGroupName = 'Custom Personal Campaigns';
                               }
                               else
                               {
                                   $campaignGroupName = $campaign_group_with_campaign['group_info'];
                               }
                                $newgroupArray[$campaignGroupName][$key1] = [
                                    'type' => $campaign['type'],
                                    'campaign_name' => ucfirst($campaign['name']),
                                    'campaign_id' => $campaign['id'],
                                    'contact_id' => $contact_id,
                                    'pipeline_stage' => $new_pipeline_stage_id,
                                    'lead_source_type' => $contactDetail['lead_source_type']
                                ];
                           }
    					}
                    }

                }

			}


        }

        //if client list cross sell & renewal campaign
        if($contactDetail['lead_type']==_CONTACT_TYPE_CLIENT)
        {


			$campaign_group_with_campaign_data_nn = [];
			$all_active_campaign_arr_nn=array();

			$campagin_group_ids_arr=array(_SALES_PIPELINE_RELATED_CAMPAIGNS,_CROSS_SELL_RELATED_CAMPAIGNS,_SERVICE_PIPELINE_RELATED_CAMPAIGNS,_POST_PIPELINE_RELATED_CAMPAIGNS,_OPT_IN_PHASE_RELATED_CAMPAIGNS, _CUSTOM_RELATED_CAMPAIGN);




			$all_active_campaign_group_arr = $campaignGroup->getAllActiveCampaignGroup();

			$all_active_campaign_arr = $agencyCampaignMaster->find('all')->where(['agency_id' => $login_agency_id, 'type IN'=>[_CAMPAIGN_TYPE_PIPELINE,_CAMPAIGN_TYPE_CROSS_SELL,_CAMPAIGN_TYPE_CLIENT_WELCOME,_CAMPAIGN_TYPE_CARRIER_INSOLVENCY,_CAMPAIGN_TYPE_SERVICE_PIPELINE,_CAMPAIGN_TYPE_LOAN_OFFICER_HOME_CONDO_PROPERTY,_CAMPAIGN_TYPE_WIN_BACK_LOST_CLIENTS,_CAMPAIGN_TYPE_SMS_OPT_IN,_CAMPAIGN_TYPE_PENDING_CANCELLATION, _CAMPAIGN_TYPE_CUSTOM_CAMPAIGN],'turn_on_off' => _ID_STATUS_ACTIVE ,'status' => _ID_STATUS_ACTIVE,'personal_commercial_line'=>_LINE_PERSONAL])->hydrate(false)->toArray();

//			$new_lead_campaigns = $agencyCampaignMaster->find('all')->where(['agency_id' => $login_agency_id, 'type'=>_CAMPAIGN_TYPE_NEW_LEAD,'turn_on_off' => _ID_STATUS_ACTIVE ,'status' => _ID_STATUS_ACTIVE,'source_type_id is not null','personal_commercial_line'=>_LINE_PERSONAL])->hydrate(false)->toArray();

			//general new lead campaign
//			$general_new_lead_campaigns = $agencyCampaignMaster->find('all')->where(['agency_id' => $login_agency_id, 'type'=>_CAMPAIGN_TYPE_GENERAL_NEW_LEAD,'turn_on_off' => _ID_STATUS_ACTIVE ,'status' => _ID_STATUS_ACTIVE,'personal_commercial_line'=>_LINE_PERSONAL])->hydrate(false)->toArray();

            $new_lead_campaigns = $agencyCampaignMaster->find('all')->where(['agency_id' => $login_agency_id, 'type' => _CAMPAIGN_TYPE_NEW_LEAD, 'turn_on_off' => _ID_STATUS_ACTIVE, 'status' => _ID_STATUS_ACTIVE, 'personal_commercial_line' => _LINE_PERSONAL])->hydrate(false)->toArray();
            //general new lead campaign
			$general_new_lead_campaigns = $agencyCampaignMaster->find('all')->where(['agency_id' => $login_agency_id, 'type' => _CAMPAIGN_TYPE_GENERAL_NEW_LEAD, 'turn_on_off' => _ID_STATUS_ACTIVE, 'status' => _ID_STATUS_ACTIVE, 'personal_commercial_line' => _LINE_PERSONAL])->hydrate(false)->toArray();

            if(isset($general_new_lead_campaigns) && !empty($general_new_lead_campaigns))
			{
				foreach($general_new_lead_campaigns as $general_new_lead_campaign){
					array_push($new_lead_campaigns,$general_new_lead_campaign);
				}
			}
			if(isset($new_lead_campaigns))
			{
				foreach ($new_lead_campaigns as $value)
                {
				$all_active_campaign_arr_nn[$value['campaign_group_id']][$value['id']]['id'] = $value['id'];
				$all_active_campaign_arr_nn[$value['campaign_group_id']][$value['id']]['name'] = $value['name'];
				$all_active_campaign_arr_nn[$value['campaign_group_id']][$value['id']]['personal_commercial_line'] = $value['personal_commercial_line'];
				$all_active_campaign_arr_nn[$value['campaign_group_id']][$value['id']]['type'] = $value['type'];
				}

			}

			foreach ($all_active_campaign_arr as $value) {
			$all_active_campaign_arr_nn[$value['campaign_group_id']][$value['id']]['id'] = $value['id'];
			$all_active_campaign_arr_nn[$value['campaign_group_id']][$value['id']]['name'] = $value['name'];
			$all_active_campaign_arr_nn[$value['campaign_group_id']][$value['id']]['personal_commercial_line'] = $value['personal_commercial_line'];
			$all_active_campaign_arr_nn[$value['campaign_group_id']][$value['id']]['type'] = $value['type'];
			}
			foreach ($all_active_campaign_group_arr as $all_active_campaign_group)
			{
				if(in_array($all_active_campaign_group->id, $campagin_group_ids_arr))
				{
					$campaign_group_with_campaign_data_nn[$all_active_campaign_group->id]['group_info'] = $all_active_campaign_group->group_name;
					if(isset($all_active_campaign_arr_nn[$all_active_campaign_group->id])){
						$campaign_group_with_campaign_data_nn[$all_active_campaign_group->id]['campaign_info'] = $all_active_campaign_arr_nn[$all_active_campaign_group->id];
					}
				}
			}

			if(isset($campaign_group_with_campaign_data_nn) && !empty($campaign_group_with_campaign_data_nn)){


                foreach ($campaign_group_with_campaign_data_nn as $key => $campaign_group_with_campaign)
                {


                    if(isset($campaign_group_with_campaign['campaign_info']) && !empty($campaign_group_with_campaign['campaign_info']))
                    {

    					foreach ($campaign_group_with_campaign['campaign_info'] as $key1 => $campaign) {
                            $new_pipeline_stage_id=$pipeline_stage_id;
                            if($campaign['type'] == _CAMPAIGN_TYPE_PIPELINE || $campaign['type'] == _CAMPAIGN_TYPE_SERVICE_PIPELINE)
                            {
                                $agencyCampaignDetailStage = $agencyCampaignMaster->agencyCampaignAndPipelineStageMasterDetail($campaign['id']);
                                if(!empty($agencyCampaignDetailStage) && isset($agencyCampaignDetailStage->agency_campaign_pipeline_stage_master['pipeline_stage_id']) && !empty($agencyCampaignDetailStage->agency_campaign_pipeline_stage_master['pipeline_stage_id']))
                                {
                                    $new_pipeline_stage_id = $agencyCampaignDetailStage->agency_campaign_pipeline_stage_master['pipeline_stage_id'];
                                }
                            }
                            else
                            {
                                $new_pipeline_stage_id = $pipeline_stage_id;
                            }
                            $campaignGroupName = "";
                            if($key == _CUSTOM_RELATED_CAMPAIGN)
                            {
                                $campaignGroupName = 'Custom Personal Campaigns';
                            }
                            else
                            {
                                $campaignGroupName = $campaign_group_with_campaign['group_info'];
                            }
                            $newgroupArray[$campaignGroupName][$key1] = [
                                'type' => $campaign['type'],
                                'campaign_name' => ucfirst($campaign['name']),
                                'campaign_id' => $campaign['id'],
                                'contact_id' => $contact_id,
                                'pipeline_stage' => $new_pipeline_stage_id,
                            ];
    					}

                    }

                }

			}

        }

        $return['ContactCampaigns.getAllAvailableCampaignsContactCard'] = [
            $contact_id => json_encode(array('status' => _ID_SUCCESS,'campaigns' => $newgroupArray,'lead_source_type'=>$contactDetail['lead_source_type'],'campaignType'=>_ID_SUCCESS))
        ];
        return $return;

    }

    /**
     * Contact Card Start Campaign - start the campaign
     */
    public function startCampaignContactCard($objectData)
    {   
        $session = Router::getRequest()->getSession(); 
        $login_agency_id = $session->read('Auth.User.agency_id');
        $login_user_id = $session->read('Auth.User.user_id');

        $AgencyCampaignMaster = TableRegistry::getTableLocator()->get('AgencyCampaignMaster');
        $Contacts = TableRegistry::getTableLocator()->get('Contacts');
        $ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
        $ContactPolicyRenewal = TableRegistry::getTableLocator()->get('ContactPolicyRenewal');
        $ContactAppointments = TableRegistry::getTableLocator()->get('ContactAppointments');
        $PhoneNumbersOptInOutStatus = TableRegistry::getTableLocator()->get('PhoneNumbersOptInOutStatus');
        $TeamUserLinks = TableRegistry::getTableLocator()->get('TeamUserLinks');
        if($objectData)
        {
            $start_campaign_id = $objectData['campaign_id'];
            $contact_id = $objectData['contact_id'];
            $pipeline_stage_id = $objectData['pipeline_stage_id'];
            $campaigntype = $objectData['campaign_type'];

            $campaignDetail = $AgencyCampaignMaster->getActiveAgencyCampaignDetail($start_campaign_id);
            $Contacts->updateAll(['order_by_recent'=>date('Y-m-d H:i:s')],['id'=>$contact_id]);
            if(!empty($start_campaign_id))
            {
                if($campaigntype == _CAMPAIGN_TYPE_RENEWAL && !empty($campaignDetail))
                {

                    $contactOpportunityDetails = $ContactOpportunities->getContactOpportunityDetailsByContactIdAndPolicyId($contact_id,$campaignDetail->insurance_type_id);


                    if(!empty($contactOpportunityDetails['id']))
                    {
                        $contactsPolicyRenewals = $ContactPolicyRenewal->getContactPolicyRenewalByContactAndOpportunity($contact_id,$contactOpportunityDetails['id']);
                        if(isset($contactsPolicyRenewals) && !empty($contactsPolicyRenewals))
                        {
                            foreach ($contactsPolicyRenewals as $contactsPolicyRenewal)
                            {
                                $checkInitiatedRenewal = $ContactPolicyRenewal->checkInitiatedRenewal($contact_id,$contactOpportunityDetails['id']);
                                if(empty($checkInitiatedRenewal))
                                {
                                    $insurance_type_id = $contactOpportunityDetails['insurance_type_id'];
                                    $agency_id = $login_agency_id;
                                    $term_length=$contactOpportunityDetails['term_length'];
                                    $new_renewal_date = date('Y-m-d', strtotime("+".$term_length." months", strtotime($contactsPolicyRenewal['renewal_date'])));
                                    $contactPolicyRenewal = $ContactPolicyRenewal->newEntity();
                                    $info = [];
                                    $info['agency_id'] = $contactsPolicyRenewal->agency_id;
                                    $info['contact_id'] = $contactsPolicyRenewal->contact_id;
                                    $info['contact_opportunities_id'] = $contactsPolicyRenewal->contact_opportunities_id;
                                    $info['renewal_date'] = $new_renewal_date;
                                    $contactPolicyRenewal = $ContactPolicyRenewal->patchEntity($contactPolicyRenewal, $info);
                                    $ContactPolicyRenewal->save($contactPolicyRenewal);
                                    $message = 'in function startCampaignContactCard, $info = ' . json_encode($info) . ' contact_opportunities_id '. $contactsPolicyRenewal->contact_opportunities_id . 'for  contact id ' . $contactsPolicyRenewal->contact_id . ' and agency is ' . $contactsPolicyRenewal->agency_id;
                                    FileLog::writeLog("contact_policy_renewal_dup_entries", $message);
                                    if(!empty($insurance_type_id))
                                    {
                                        $ContactPolicyRenewal->updateAll(['campaign_initiate_flag'=>_RENEWAL_CAMPAIGN_INITIATED],['id'=>$contactsPolicyRenewal->id]);

                                            $checkCampaignExist=$AgencyCampaignMaster->checkCampaignExist(null,$insurance_type_id,$agency_id,_RENEWAL_STAGE_INITIATE,_CAMPAIGN_TYPE_RENEWAL);


                                        if(!empty($checkCampaignExist))
                                        {
                                            $start_campaign_id=$checkCampaignExist['id'];


                                                $campaign_result = CommonFunctions::startCampaign($contact_id, $start_campaign_id,_RENEWAL_STAGE_INITIATE, null, null, null, null, null, $login_user_id);

                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                elseif ($campaigntype == _CAMPAIGN_TYPE_CROSS_SELL)
                {

                        $campaign_result = CommonFunctions::startCampaign($contact_id, $start_campaign_id,_CROSS_SELL_STAGE_INITIATE, null, null, null, null, null, $login_user_id);

                }
                elseif ($campaigntype == _CAMPAIGN_TYPE_CLIENT_WELCOME)
                {

                        $Contacts->updateAll(['is_token_expire'=>_STATUS_FALSE],['id'=>$contact_id]);
                        $campaign_result = CommonFunctions::startCampaign($contact_id, $start_campaign_id,_CLIENT_WELCOME_STAGE_INITIATE, null, null, null, null, null, $login_user_id);


                }
                elseif ($campaigntype == _CAMPAIGN_TYPE_PIPELINE)
                {
                    $agencyCampaignDetail = $AgencyCampaignMaster->agencyCampaignAndPipelineStageMasterDetail($start_campaign_id);
                    //update the pipeline stage id
                    if(!empty($agencyCampaignDetail) && isset($agencyCampaignDetail->agency_campaign_pipeline_stage_master['pipeline_stage_id']) && !empty($agencyCampaignDetail->agency_campaign_pipeline_stage_master['pipeline_stage_id']))
                    {

                            $opportunityToUpdatePipeline = $ContactOpportunities->getContactOpportunitiesByContactIdForPipelineCampaign($contact_id);


                        if($agencyCampaignDetail->agency_campaign_pipeline_stage_master['pipeline_stage_id'] == _PIPELINE_CAMPAIGN_STAGE_QUOTE_SENT_VIDEO_PROPOSAL)
                        {
                            $ContactOpportunities->updateAll(['pipeline_stage' =>_PIPELINE_STAGE_QUOTE_SENT],['id' => $opportunityToUpdatePipeline['id']]);
                        }
                        else
                        {

                            $result = $ContactOpportunities->updateAll(['pipeline_stage' =>$agencyCampaignDetail->agency_campaign_pipeline_stage_master['pipeline_stage_id']],['id' => $opportunityToUpdatePipeline['id']]);


                        }

                            if($agencyCampaignDetail->agency_campaign_pipeline_stage_master['pipeline_stage_id'] == _PIPELINE_STAGE_APPOINTMENT_SCHEDULED)
                            {
                                $contact_appointment_date="";
                                $contact_appointment = $ContactAppointments->getLatestAppointmentsByContactId($contact_id);
                                if(isset($contact_appointment) && !empty($contact_appointment))
                                {
                                   $contact_appointment_date =  $contact_appointment['appointment_date'];
                                }
                                $campaign_result = CommonFunctions::startBeforeAfterTypeCampaign($contact_id, $start_campaign_id,$agencyCampaignDetail->agency_campaign_pipeline_stage_master['pipeline_stage_id'],null,null,null,null,null,$contact_appointment_date, $login_user_id);
                            }
                            else
                            {
                                $campaign_result = CommonFunctions::startCampaign($contact_id, $start_campaign_id,$agencyCampaignDetail->agency_campaign_pipeline_stage_master['pipeline_stage_id'], null, null, null, null, null, $login_user_id);
                            }

                    }
                }
                else if($campaigntype == _CAMPAIGN_TYPE_NEW_LEAD)
                {

                        $opportunityToUpdatePipeline = $Contacts->getContactPendingOppForNewLeadCampaign($contact_id,$campaignDetail['insurance_type_id'],['id','lead_source_type']);

                    if(empty($opportunityToUpdatePipeline))
                    {
                        $response = json_encode(['status'=>_STATUS_FALSE,'message'=>'Policy Type does not match campaign selected.','start_campaign_id' => $start_campaign_id]);
                        return $response;
                    }
                    if(!empty($opportunityToUpdatePipeline) && !empty($campaignDetail['source_type_id']) && $opportunityToUpdatePipeline['lead_source_type'] != $campaignDetail['source_type_id'])
                    {
                        $response = json_encode(['status'=>_STATUS_FALSE,'message'=>'Lead Source does not match campaign selected.']);
                        return $response;
                    }
                    if(!empty($opportunityToUpdatePipeline) && empty($campaignDetail['source_type_id']) && strtolower($opportunityToUpdatePipeline['lead_source']['name']) != 'referral partner')
                    {
                        $response = json_encode(['status'=>_STATUS_FALSE,'message'=>'Lead Source does not match campaign selected.','start_campaign_id' => $start_campaign_id]);
                        return $response;
                    }
                    if(isset($opportunityToUpdatePipeline) && !empty($opportunityToUpdatePipeline))
                    {

                        $campaign_result = CommonFunctions::startCampaign($contact_id, $start_campaign_id,_PIPELINE_STAGE_NEW_LEAD, null, null, null, null, null, $login_user_id);

                    }
                    else
                    {
                        $response = json_encode(['status'=>_STATUS_FALSE,'message'=>'Policy Type does not match campaign selected.','start_campaign_id' => $start_campaign_id]);
                        return $response;
                    }
                }
				else if($campaigntype == _CAMPAIGN_TYPE_GENERAL_NEW_LEAD)
                {

                    $campaign_result = CommonFunctions::startCampaign($contact_id, $start_campaign_id,_PIPELINE_STAGE_NEW_LEAD, null, null, null, null, null, $login_user_id);

                }
                else if($campaigntype == _CAMPAIGN_TYPE_LONG_TERM_NURTURE)
                {

                    CommonFunctions::startLtnCampaign($contact_id);

                }
                else if($campaigntype == _CAMPAIGN_TYPE_X_DATE)
                {

                    CommonFunctions::startXdateCampaign($contact_id);


                }
                else if($campaigntype == _CAMPAIGN_TYPE_CARRIER_INSOLVENCY)
                {

                    CommonFunctions::carrierInsolvencyFuntionality($login_agency_id,$start_campaign_id,$contact_id, null, null, $login_user_id);


                }
				elseif ($campaigntype == _CAMPAIGN_TYPE_LOAN_OFFICER_HOME_CONDO_PROPERTY)
                {

                    $campaign_result = CommonFunctions::startCampaign($contact_id, $start_campaign_id,_CAMPAIGN_TYPE_LOAN_OFFICER_HOME_CONDO_PROPERTY_STAGE_INITIATE, null, null, null, null, null, $login_user_id);

                }
                else if($campaigntype == _CAMPAIGN_TYPE_WIN_BACK_LOST_CLIENTS)
                {

                    $Contacts->updateAll(['winback_x_date' => date('Y-m-d',strtotime('+60 days'))],['id' => $contact_id]);
                        CommonFunctions::startWinBackCampaign($contact_id, $login_user_id);


                }
				elseif ($campaigntype == _CAMPAIGN_TYPE_SMS_OPT_IN)
                {

                    $contact_detail = $Contacts->getContactDetails($login_agency_id,$contact_id);
                    $phoneNumber = '';
                    if(isset($contact_detail) && !empty($contact_detail)){
                        $phoneNumber = $contact_detail['phone'];
                        if($phoneNumber != ''){

                            $checkOptInOut = $PhoneNumbersOptInOutStatus->checkOptInOptOutStatus($login_agency_id,$phoneNumber);
                            if($checkOptInOut['status'] == _ID_FAILED){

                            $smsServiceCount = $checkOptInOut['result']['opt_in_count']+1;
                            $smsServiceDate = date("Y-m-d",strtotime($checkOptInOut['result']['opt_in_date']));
                            $current_date = date("Y-m-d");

                            if($smsServiceCount< _SMS_SERVICE_COUNT &&  $smsServiceDate!=$current_date){

                                $campaign_result = CommonFunctions::startCampaign($contact_id, $start_campaign_id,_SMS_OPT_IN_STAGE, null, null, null, null, null, $login_user_id);

                            }else{
                                $response = json_encode(array('status' => _ID_FAILED,'code'=>_CAMPAIGN_TYPE_SMS_OPT_IN,'start_campaign_id' => $start_campaign_id));
                                return $response;
                            }

                            }else{
                                $response = json_encode(array('status' => _ID_FAILED,'start_campaign_id' => $start_campaign_id));
                                return $response;
                            }
                        }
                        else{
                            $response = json_encode(array('status' => _ID_FAILED, 'message'=> "Phone number is required",'start_campaign_id' => $start_campaign_id));
                            return $response;
                        }
                    }else{
                        $response = json_encode(array('status' => _ID_FAILED,'start_campaign_id' => $start_campaign_id));
                        return $response;
                    }



                }
                else if($campaigntype == _CAMPAIGN_TYPE_PENDING_CANCELLATION)
                {
                    $assign_owner_id="";
                    $personal_commercial = _LINE_PERSONAL;  
                    $contact = $Contacts->contactDetails($contact_id);

                    $sale_owner_id="";
                    if(isset($contact) && !empty($contact))
                    {
                        $sale_owner_id = $contact['user_id'];//get this id from contact table
                    }

                    $round_robin_service_arr = CommonFunctions::getServiceUserIDbyRoundRobin($contact['agency_id'], $sale_owner_id,$personal_commercial);
                    //print_r($round_robin_service_arr); exit;
                    if(isset($round_robin_service_arr['user_id_to_return']) && !empty($round_robin_service_arr['user_id_to_return'])){
                        $assign_owner_id=$round_robin_service_arr['user_id_to_return'];
                    }

                    //camapign created then update lead for assign owner
                    if(isset($round_robin_service_arr['team_user_link_id']) && !empty($round_robin_service_arr['team_user_link_id'])){

                            $team_user_link_id=$round_robin_service_arr['team_user_link_id'];
                            $teamUserLinkDetails=$TeamUserLinks->teamUserLinkDetails($team_user_link_id);
                            if(isset($teamUserLinkDetails) && !empty($teamUserLinkDetails))
                                $current_lead=$teamUserLinkDetails['current_lead']+1;
                                $TeamUserLinks->updateAll(['current_lead'=>$current_lead],['id'=>$team_user_link_id]);

                    }
                    //

                    $future_date="";
                    if(isset($objectData['con_cancellation_date']) && !empty($objectData['con_cancellation_date']) && isset($objectData['con_cancellation_time']) && !empty($objectData['con_cancellation_time']) && isset($objectData['con_cancellation_time']))
                    {
                        $appointment_date_time=date("Y-m-d H:i:s",strtotime($objectData['con_cancellation_date'].' '.$objectData['con_cancellation_time']));

                        $campaign_result = CommonFunctions::startBeforeTypeCampaign($contact_id, $start_campaign_id,_PENDING_CANCELLATION_STAGE_INITIATE,null,$assign_owner_id,null,null,null,$appointment_date_time, $login_user_id);

                    }
                }
                elseif ($campaigntype == _CAMPAIGN_TYPE_SERVICE_PIPELINE)
                {   
                    $assign_owner_id="";
                    $personal_commercial = _LINE_PERSONAL;  
                    $contact = $Contacts->contactDetails($contact_id);

                    $sale_owner_id="";
                    if(isset($contact) && !empty($contact))
                    {
                        $sale_owner_id=$contact['user_id'];//get this id from contact table
                    }

                    $round_robin_service_arr = CommonFunctions::getServiceUserIDbyRoundRobin($contact['agency_id'], $sale_owner_id,$personal_commercial);
                    //print_r($round_robin_service_arr); exit;
                    if(isset($round_robin_service_arr['user_id_to_return']) && !empty($round_robin_service_arr['user_id_to_return'])){
                        $assign_owner_id=$round_robin_service_arr['user_id_to_return'];
                    }

                    //camapign created then update lead for assign owner
                    if(isset($round_robin_service_arr['team_user_link_id']) && !empty($round_robin_service_arr['team_user_link_id'])){

                            $team_user_link_id=$round_robin_service_arr['team_user_link_id'];
                            $teamUserLinkDetails=$TeamUserLinks->teamUserLinkDetails($team_user_link_id);
                            if(isset($teamUserLinkDetails) && !empty($teamUserLinkDetails))
                                $current_lead=$teamUserLinkDetails['current_lead']+1;
                                $TeamUserLinks->updateAll(['current_lead'=>$current_lead],['id'=>$team_user_link_id]);

                    }
                    //

                    $campaign_result = CommonFunctions::startCampaign($contact_id, $start_campaign_id, $pipeline_stage_id, null, $assign_owner_id, null, null, null, $login_user_id);
                }
                else if($campaigntype == _CAMPAIGN_TYPE_CUSTOM_CAMPAIGN)
                {
                    $campaign_result = CommonFunctions::startCampaign($contact_id, $start_campaign_id,_PIPELINE_STAGE_NEW_LEAD, null, null, null, null, null, $login_user_id);
                }
                if($campaign_result['status'] == _ID_SUCCESS)
                {
                    $response = json_encode(array('status' => $campaign_result['status'],'message'=> $campaign_result['message'], 'start_campaign_id' => $start_campaign_id));
                }
                else if($campaign_result['status'] == _ID_FAILED)
                {
                    $response = json_encode(array('status' => _ID_FAILED, 'message' => $campaign_result['message'], 'start_campaign_id' => $start_campaign_id));
                    // $response = json_encode(array('status' => _ID_SUCCESS, 'start_campaign_id' => $start_campaign_id));
                }
                return $response;
            }
        }
    }

    /**
     * save appointment date time
    */
    public function saveAppointmentDateContactCard($objectData){

        $ContactAppointments = TableRegistry::getTableLocator()->get('ContactAppointments');
        if(isset($objectData['con_appointment_date']) && !empty($objectData['con_appointment_date']) && isset($objectData['con_appointment_time']) && !empty($objectData['con_appointment_time']))
        {
            $appointment_date_time=date("Y-m-d H:i:s",strtotime($objectData['con_appointment_date'].' '.$objectData['con_appointment_time']));
            $contact_appointment_arr = [];

            $contact_appointment_arr['contact_id'] = $objectData['contact_id'];

            $contact_appointment_arr['appointment_date'] = $appointment_date_time;
            $contact_appointment = $ContactAppointments->newEntity();
            $contact_appointment = $ContactAppointments->patchEntity($contact_appointment,$contact_appointment_arr);
            $contact_appointment = $ContactAppointments->save($contact_appointment);
            $response =  json_encode(array('status' => _ID_SUCCESS));
        }else{
            $response = json_encode(array('status' => _ID_FAILED));
        }
        return $response;

    }
    
    public function checkCampaignScheduler($contact_id){
        $campaignRunningScheduleTable = TableRegistry::getTableLocator()->get('CampaignRunningSchedule');
        $agencyCampaignMasterTable = TableRegistry::getTableLocator()->get('AgencyCampaignMaster');
        $campaignRunningEmailSmsScheduleTable = TableRegistry::getTableLocator()->get('CampaignRunningEmailSmsSchedule');
        //mark the running schedule completed if no scheduled email/sms is there
        $runnngCampaignScheduleDetails = $campaignRunningScheduleTable->getAllActiveCampaignByContactID($contact_id);
        if(isset($runnngCampaignScheduleDetails) && !empty($runnngCampaignScheduleDetails))
        {
            foreach ($runnngCampaignScheduleDetails as $runnngCampaignScheduleDetail) {
                $campaign_detail_check_type = $agencyCampaignMasterTable->agencyCampaignDetail($runnngCampaignScheduleDetail['campaign_id']);
                $getPendingRunningEmailSmsSchedule = $campaignRunningEmailSmsScheduleTable->getPendingCampaignRunningSchedule($runnngCampaignScheduleDetail['id']);
                if(empty($getPendingRunningEmailSmsSchedule))
                {
                    if($campaign_detail_check_type->type == _CAMPAIGN_TYPE_X_DATE)
                    {
                        if($runnngCampaignScheduleDetail['pipeline_stage_id'] == _LEAD_NURTURE_X_DATE_DAYS_TYPE_TEN)
                        {
                            $campaignRunningScheduleTable->updateAll(['status'=>_RUN_SCHEDULE_STATUS_COMPLETED],['id'=>$runnngCampaignScheduleDetail['id']]);
                        }
                    }
                    else if($campaign_detail_check_type->type == _CAMPAIGN_TYPE_LONG_TERM_NURTURE)
                    {
                        if($runnngCampaignScheduleDetail['pipeline_stage_id'] == _LONG_TERM_NURTURE_STAGE_3_MONTH)
                        {
                            $campaignRunningScheduleTable->updateAll(['status'=>_RUN_SCHEDULE_STATUS_COMPLETED],['id'=>$runnngCampaignScheduleDetail['id']]);
                        }
                    }
                    else
                    {
                        $campaignRunningScheduleTable->updateAll(['status'=>_RUN_SCHEDULE_STATUS_COMPLETED],['id'=>$runnngCampaignScheduleDetail['id']]);
                    }

                }
            }
        }
        $return['ContactCampaigns.checkCampaignScheduler'] = [
            $contact_id => array('status' => _ID_SUCCESS)
        ];
    
        return $return;
    }

   /**
     * stop running campaign for contact
    */
    public function stopCampaignContactCard($objectData){

            $UsStatesTable = TableRegistry::getTableLocator()->get('UsStates');
            $users = TableRegistry::getTableLocator()->get('Users');
            $session = Router::getRequest()->getSession();
            $usersTimezone = 'America/Phoenix';
            $agencyTimeZone = $session->read('Auth.User.agency_session_detail.time_zone');
            $loginUserId = $session->read('Auth.User.id');
            if(isset($agencyTimeZone) && $agencyTimeZone != '')
            {
                $usersTimezone = $agencyTimeZone;
            }
            else
            {
                $agencyStateId = $session->read('Auth.User.agency_session_detail.us_state_id');
                if (isset($agencyStateId) && $agencyStateId != '')
                {
                    $stateDetail = $UsStatesTable->stateDetail($agencyStateId);
                    if (isset($stateDetail) && !empty($stateDetail))
                    {
                        $usersTimezone = $stateDetail->time_zone;
                    }
                }
            }
        $CampaignRunningSchedule = TableRegistry::getTableLocator()->get('CampaignRunningSchedule');
        $AgencyCampaignMaster = TableRegistry::getTableLocator()->get('AgencyCampaignMaster');
        $Contacts = TableRegistry::getTableLocator()->get('Contacts');
        $CampaignRunningEmailSmsSchedule = TableRegistry::getTableLocator()->get('CampaignRunningEmailSmsSchedule');
        if(isset($objectData['campaign_running_schedule_id']) && !empty($objectData['campaign_running_schedule_id']))
        {
            $campaignRunningScheduleArray = $CampaignRunningSchedule->getCampaignRunningScheduleById($objectData['campaign_running_schedule_id']);
            if(isset($campaignRunningScheduleArray) && !empty($campaignRunningScheduleArray))
            {
                foreach ($campaignRunningScheduleArray as $campaignRunningSchedule)
                {
                    $campaignDetail = $AgencyCampaignMaster->agencyCampaignDetail($campaignRunningSchedule['campaign_id']);
                    if(isset($campaignDetail) && !empty($campaignDetail))
                    {
                        if($campaignDetail->type == _CAMPAIGN_TYPE_X_DATE)
                        {
                            $Contacts->updateAll(['x_date_stop_status'=>_STATUS_TRUE, 'xdate_campaign_start_status' => _ID_FAILED],['id'=>$campaignRunningSchedule['contact_id']]);
                        }
                        if($campaignDetail->type == _CAMPAIGN_TYPE_LONG_TERM_NURTURE)
                        {
                            $Contacts->updateAll(['long_term_nurture_stop_status'=>_STATUS_TRUE],['id'=>$campaignRunningSchedule['contact_id']]);
                        }
                    }
                    $CampaignRunningSchedule->updateAll(['status'=>_RUN_SCHEDULE_STATUS_INACTIVE],['id'=>$campaignRunningSchedule['id'],'status'=>_RUN_SCHEDULE_STATUS_ACTIVE]);
                    $CampaignRunningEmailSmsSchedule->updateAll(['status'=>_EMAIL_SMS_SENT_STATUS_CANCELLED],['campaign_running_schedule_id'=>$campaignRunningSchedule['id'],'status'=>_EMAIL_SMS_SENT_STATUS_PENDING]);
                    $campaignStartDateTime = CommonFunctions::convertUtcToEmployeeTimeZone($usersTimezone,date("M d, Y H:i:s"));
                    $campaignStartDate = date('M d, Y', strtotime($campaignStartDateTime));
                    $campaignStartTime = date('h:i:s a', strtotime($campaignStartDateTime));
                    if($loginUserId)
                    {
                       $userData = $users->get($loginUserId);
                       $firstName = ($userData->first_name) ? ucwords($userData->first_name) : '';
                       $lastName = ($userData->last_name) ? ucwords($userData->last_name) : '';
                       $userName = $firstName .' '. $lastName;
                    }
                    else
                    {
                        $userName = "Better Agency";
                    }
                    $message = $userName." stopped the \"". ucwords($campaignDetail['name']) ."\" campaign on ". $campaignStartDate ." at ". $campaignStartTime . ".";
                    $contactLogsArray['contact_id'] = $campaignRunningSchedule['contact_id'];
                    $contactLogsArray['contact_business_id'] = $campaignRunningSchedule['contact_business_id'];
                    $contactLogsArray['user_id'] = $loginUserId;
                    $contactLogsArray['platform'] = _PLATFORM_TYPE_SYSTEM;
                    $contactLogsArray['message'] = $message;
                    CommonFunctions::insertContactLogsOnUpdate($contactLogsArray);
                }
                $response =  json_encode(['status' => _ID_SUCCESS,'campaign_running_schedule_id' => $objectData['campaign_running_schedule_id']]);
            }
            else
            {
                $response = json_encode(['status' => _ID_FAILED]);
            }
        }
        else
        {
            $response = json_encode(['status' => _ID_FAILED]);
        }
        return $response;
    }

    public function sendRunningCampaignEmailSmsTaskTemplate($objectData){
        $CampaignRunningSchedule = TableRegistry::getTableLocator()->get('CampaignRunningSchedule');
        $AgencyCampaignMaster = TableRegistry::getTableLocator()->get('AgencyCampaignMaster');
        $Contacts = TableRegistry::getTableLocator()->get('Contacts');
        $AgencyEmailTemplatesTable = TableRegistry::getTableLocator()->get('AgencyEmailTemplates');
        $AgencySmsTemplatesTable = TableRegistry::getTableLocator()->get('AgencySmsTemplates');
        $CampaignRunningEmailSmsSchedule = TableRegistry::getTableLocator()->get('CampaignRunningEmailSmsSchedule');
        $AgencyTaskTemplatesTable = TableRegistry::getTableLocator()->get('AgencyTaskTemplates');
        $AgencyTransitionTemplatesTable = TableRegistry::getTableLocator()->get('AgencyTransitionTemplates');

        $agency_id    = $objectData['agency_id'];
        $template_id   = $objectData['template_id'];
        $scheduledEmailSmsID = $objectData['scheduledEmailSmsID'];
        $scheduledEmailSmsType= $objectData['scheduledEmailSmsType'];
		$campaign_id= $objectData['campaign_id'];
        $contact_business_id = "";
        $contact_id="";
        //$userDetail = $this->Users->userDetails($contactDetail['user_id']);
        //$mail_from      =   $userDetail['email'];
        $mailTo="";//$mailTo        =   $contactDetail['email'];
        $phone="";//$phone=$contactDetail['phone'];
        $contactDetail="";
        $businessDetail="";
        $user_id="";
        $campaign_running_schedule_detail = "";
        if(isset($campaign_id) && !empty($campaign_id))
        {
            $campaign_running_schedule_detail = $CampaignRunningSchedule->getCampaignRunningScheduleByIdAnyStatus($campaign_id);
        }

        if(isset($objectData['contact_id']) && !empty($objectData['contact_id']))
        {
            $contact_id   = $objectData['contact_id'];
            $contactDetail = $Contacts->contactDetails($contact_id);
            $user_id=$contactDetail['user_id'];
            $Contacts->updateAll(['order_by_recent'=>date('Y-m-d H:i:s')],['id'=>$contact_id]);
        }

        $toName="";//$toName = $contactDetail['first_name'].' '.$contactDetail['last_name'];
        if($scheduledEmailSmsType == _CAMPAIGN_EMAIL)
        {
            FileLog::writeLog('sendRunningCampaignEmailSmsTaskTemplate', 'The flagType is :'.$scheduledEmailSmsType);
            $campaignEmailTemplate = $AgencyEmailTemplatesTable->get($template_id);
            $emailResponse = CommonFunctions::sendCampaignEmail($template_id, $mailTo,  $campaignEmailTemplate->subject, $campaignEmailTemplate->content, $toName,$agency_id,$user_id,$contact_id,null,null,$campaign_id);
            if($emailResponse)
            {
                $CampaignRunningEmailSmsSchedule->updateAll(['status' => _EMAIL_SMS_SENT_STATUS_COMPLETED],['id' => $scheduledEmailSmsID] );
                echo json_encode(array('status' => _ID_SUCCESS,'message'=>''));
            }
            else{
                echo json_encode(array('status' => _ID_FAILED,'message'=>''));
            }

        }
        else if($scheduledEmailSmsType == _CAMPAIGN_SMS)
        {
            $campaignSmsTemplate = $AgencySmsTemplatesTable->get($template_id);
            $smsResponse = CommonFunctions::sendCampaignSms($template_id, $phone, $toName, $agency_id,$user_id,$campaignSmsTemplate->content,$contact_id,null,null);
         // echo "<pre>";print_r($smsResponse);die("dfd");
            if(isset($smsResponse) && !empty($smsResponse) && $smsResponse['status'] == _ID_SUCCESS)
            {
                $CampaignRunningEmailSmsSchedule->updateAll(['status' => _EMAIL_SMS_SENT_STATUS_COMPLETED],['id' => $scheduledEmailSmsID]);
                echo json_encode(array('status' => _ID_SUCCESS,'message'=>''));
            }
            else{
                $message = $smsResponse['meassage'];
                echo json_encode(array('status' => _ID_FAILED,'message'=>substr($message, strrpos($message, ':') + 1),'code'=>'452'));
            }


        }
        else if($scheduledEmailSmsType == _CAMPAIGN_TASK)
        {
            $campaignTaskTemplate = $AgencyTaskTemplatesTable->get($template_id);
            CommonFunctions::createCampaignTask($template_id, $agency_id,$user_id,$contact_id,null,$contact_business_id,$campaign_running_schedule_detail->id);
            $CampaignRunningEmailSmsSchedule->updateAll(['status' => _EMAIL_SMS_SENT_STATUS_COMPLETED],['id' => $scheduledEmailSmsID]);
            echo json_encode(array('status' => _ID_SUCCESS,'message'=>''));
        }
        else if($scheduledEmailSmsType == _CAMPAIGN_TYPE_TRANSITION)
        {
            $campaignTransitoinTemplate = $AgencyTransitionTemplatesTable->transitionTemplatesDetail($template_id);
            $transition_campaign_and_pipline_stage = $AgencyCampaignMaster->agencyCampaignAndPipelineStageMasterDetail($campaignTransitoinTemplate['transition_campaign_id']);
            if(isset($transition_campaign_and_pipline_stage) && !empty($transition_campaign_and_pipline_stage))
            {
                CommonFunctions::startCampaign($contact_id, $campaignTransitoinTemplate['transition_campaign_id'], $transition_campaign_and_pipline_stage->agency_campaign_pipeline_stage_master['pipeline_stage_id'],null,null,null,null,null);
                if(isset($campaign_running_schedule_detail) && !empty($campaign_running_schedule_detail))
                {
                    CommonFunctions::moveContactOppOnTransition($contact_id,$contact_business_id,$campaign_running_schedule_detail['pipeline_stage_id'],$transition_campaign_and_pipline_stage->agency_campaign_pipeline_stage_master['pipeline_stage_id']);
                }
            }
            $CampaignRunningEmailSmsSchedule->updateAll(['status' => _EMAIL_SMS_SENT_STATUS_COMPLETED],['id' => $scheduledEmailSmsID]);

            echo json_encode(array('status' => _ID_SUCCESS,'message'=>''));
        }
        $pendingCampaignTempalteList = $CampaignRunningEmailSmsSchedule->getCampaignRunningScheduleListByContactID($campaign_id,$contact_id);
        if(empty($pendingCampaignTempalteList)){
            $CampaignRunningSchedule->updateAll(['status' => _RUN_SCHEDULE_STATUS_COMPLETED],['id' => $campaign_id] );
        }
        die;
    }

    /**
     * Cancel currenmt running campaign
     */
    public function cancelRunningCampaignEmailSmsTaskTemplate($objectData)
    {

        $Contacts = TableRegistry::getTableLocator()->get('Contacts');
        $CampaignRunningEmailSmsSchedule = TableRegistry::getTableLocator()->get('CampaignRunningEmailSmsSchedule');
        $CampaignRunningSchedule = TableRegistry::getTableLocator()->get('CampaignRunningSchedule');

        $agency_id    = $objectData['agency_id'];
        $contact_id   = $objectData['contact_id'];
        $scheduledEmailSmsID = $objectData['scheduledEmailSmsID'];
        $scheduledEmailSmsType= $objectData['scheduledEmailSmsType'];
        $campaignId = $objectData['campaign_id'];
        $Contacts->updateAll(['order_by_recent'=>date('Y-m-d H:i:s')],['id'=>$contact_id]);
        if(!empty($scheduledEmailSmsID))
        {
            $CampaignRunningEmailSmsSchedule->updateAll(['status' => _EMAIL_SMS_SENT_STATUS_CANCELLED],['id' => $scheduledEmailSmsID] );
            $pendingList = $CampaignRunningEmailSmsSchedule->getCampaignRunningScheduleListByContactID($campaignId,$contact_id);
            if(empty($pendingList)){
              $CampaignRunningSchedule->updateAll(['status' => _RUN_SCHEDULE_STATUS_COMPLETED],['id' => $campaignId] );
            }
            echo json_encode(array('status' => _ID_SUCCESS,'message'=>'Cancelled'));
        }
        else{
            echo json_encode(array('status' => _ID_FAILED,'message'=>''));
        }
        die;
    }
}
?>
	