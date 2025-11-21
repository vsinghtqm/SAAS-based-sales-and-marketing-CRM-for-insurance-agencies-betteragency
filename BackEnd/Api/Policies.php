<?php

namespace App\Lib\ApiProviders;

use App\Lib\PermissionsCheck;
use App\Lib\QuickTables\ContactsQuickTable;
use App\Model\Entity\Contact;
use App\Model\Entity\ContactOpportunity;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Http\Session;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

class Policies
{
    public static function getPoliciesForContact($contactId, $fields){
        $return = [
            'Policy' => []
        ];
        $hasPermissions = PermissionsCheck::currentUserHasReadPermission(ContactsQuickTable::findById($contactId));

        if(!$hasPermissions){
            throw new UnauthorizedException("Not authorized to view policies for contact: " . $contactId);
        }
        $ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
        $ContactMultipolicy = TableRegistry::getTableLocator()->get('ContactMultipolicy');
		$ContactNotes = TableRegistry::getTableLocator()->get('ContactNotes');       	
		$Services = TableRegistry::getTableLocator()->get('Services');       	
        $ContactAcordForms = TableRegistry::getTableLocator()->get('ContactAcordForms');       	
        $ContactPolicyAttachments = TableRegistry::getTableLocator()->get('ContactPolicyAttachments');       	
        $AgencyInsuranceTypesLink = TableRegistry::getTableLocator()->get('AgencyInsuranceTypesLink');       	
        $contactOpportunities = $ContactOpportunities->getMultiPolicyListing($contactId);
        $contactMultipolicy = $ContactMultipolicy->getContactMultiPolicyListingByContactId($contactId);
        foreach($contactOpportunities as $contactOpportunity){			
            $return['Policy']['CO-' . $contactOpportunity->id] = $contactOpportunity;
			$return['Policy']['CO-' . $contactOpportunity->id]['Icon'] = $AgencyInsuranceTypesLink->getPolicyIconLInk($contactOpportunity->agency_id,$contactOpportunity->insurance_type_id);
        }
        foreach($contactMultipolicy as $contactMultipolicy){
            $return['Policy']['CM-' . $contactMultipolicy->id] = $contactMultipolicy;
        }
		
        $return['Policies.getPoliciesForContact'] = [
            $contactId => array_keys($return['Policy'])
        ];
        return $return;
    }
	
	 public static function getActivePoliciesCountForContact($contactId, $fields=null){
        $return = [
            'Policy' => []
        ];
        $hasPermissions = PermissionsCheck::currentUserHasReadPermission(ContactsQuickTable::findById($contactId));

        if(!$hasPermissions){
            throw new UnauthorizedException("Not authorized to view policies for contact: " . $contactId);
        }

        $ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
        $ContactsAdditionalInsured = TableRegistry::getTableLocator()->get('ContactAdditionalInsuredPolicyRelation');
        
        // condition added for secondary contact policy count and policy premium
        $Contacts = TableRegistry::getTableLocator()->get('Contacts');
		// get contact details
        $contact = $Contacts->contactDetails($contactId);
        //all opportunities premium get
        $mappedPolicies = $ContactsAdditionalInsured->getMappedPolicyWithPrimaryContact($contactId);
        if(count($mappedPolicies) > 0)
        {
            $primaryId = $mappedPolicies[0]['contact_id'];
            $contatOpportunityIds = array_column($mappedPolicies,'contact_opportunity_id');
            $additionalPolicyCount = $ContactOpportunities->getActivePolicyCount($primaryId, $contatOpportunityIds);
        }
        $policyCount = $ContactOpportunities->getActivePolicyCount($contactId);
        if($additionalPolicyCount)
        {
            foreach ($policyCount as $key => $value)
            {
                $policyCount[$key] = $value + $additionalPolicyCount[$key];
            }

        }


         $return['Policy']['active_count'] = $policyCount['count'];
         $return['Policy']['active_sum'] = $policyCount['sum'];
         $return['Policies.getActivePoliciesCountForContact'] = [
            $contactId => ($return['Policy'])
        ];
        return $return;
    }

	
	public function getPoliciesByContactId($contactId, $fields){
        $return = [
            'Policy' => []
        ];

        //Do we have cached permissions for this contact?
        $permissions = false;
        if(PermissionsCheck::getCachedPermissions(get_class(new Contact()), $contactId) !== null){
            $permissions = PermissionsCheck::getCachedPermissions(get_class(new Contact()), $contactId);
        } else {
            $contact = ContactsQuickTable::findById($contactId);

            if(empty($contact)){
                $permissions = false;
            } else {
                $permissions = PermissionsCheck::currentUserHasReadPermission($contact);
            }

            if(!$permissions){
                throw new UnauthorizedException("User cannot read contact: $contactId");
            }
        }
        $ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
        $ContactMultipolicy = TableRegistry::getTableLocator()->get('ContactMultipolicy');

        $contactOpportunities = $ContactOpportunities->getMultiPolicyListing($contactId);
        $contactMultipolicy = $ContactMultipolicy->getContactMultiPolicyListingByContactId($contactId);
        foreach($contactOpportunities as $contactOpportunity){
            $return['single_policy'] = $contactOpportunity;
        }
        foreach($contactMultipolicy as $contactMultipolicy){
            $return['multi_policy'] = $contactMultipolicy;
        }
        $return['Policies.getPoliciesForContact'] = [
            $contactId => $return['Policy']
        ];
        return $return;
    }
	
	public function getContactPolicyTypes($contactId, $fields){
		$return = [
            'Policy' => []
        ];
		$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
		$return['Policies.getContactPolicyTypes'] = [
            $contactId => $ContactOpportunities->getAllPolicyTypeByContactId($contactId)
        ];
  
		return $return;
	}

    public static function getAllPoliciesForContact($contactId, $fields=null){
       
        $hasPermissions = PermissionsCheck::currentUserHasReadPermission(ContactsQuickTable::findById($contactId));
        if(!$hasPermissions){
            throw new UnauthorizedException("Not authorized to view policies for contact: " . $contactId);
        }
        $ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
        $ContactMultipolicy = TableRegistry::getTableLocator()->get('ContactMultipolicy');
		$ContactNotes = TableRegistry::getTableLocator()->get('ContactNotes');       	
		$Services = TableRegistry::getTableLocator()->get('Services');       	
        $ContactAcordForms = TableRegistry::getTableLocator()->get('ContactAcordForms');       	
        $ContactPolicyAttachments = TableRegistry::getTableLocator()->get('ContactPolicyAttachments');       	
        $AgencyInsuranceTypesLink = TableRegistry::getTableLocator()->get('AgencyInsuranceTypesLink');
        $Contacts = TableRegistry::getTableLocator()->get('Contacts');
        $CampaignRunningSchedule = TableRegistry::getTableLocator()->get('CampaignRunningSchedule');
        $CampaignRunningEmailSmsSchedule = TableRegistry::getTableLocator()->get('CampaignRunningEmailSmsSchedule');
        $ContactsAdditionalInsured = TableRegistry::getTableLocator()->get('ContactAdditionalInsuredPolicyRelation');   

        // get contact details 
        $contact = $Contacts->contactDetails($contactId);
        $contactOpportunities = [];
        //all opportunities get
         $mappedPolicies = $ContactsAdditionalInsured->getMappedPolicyWithPrimaryContact($contactId);
         if(count($mappedPolicies) > 0)
         {
             $primaryId = $mappedPolicies[0]['contact_id'];
             $contatOpportunityIds = array_column($mappedPolicies,'contact_opportunity_id');
            //  $contactAdditionalOpportunities = $ContactOpportunities->getMultiPolicyListing($primaryId,'',$contatOpportunityIds);
             $contactAdditionalOpportunities = $ContactOpportunities->activeInactivePendingPolicyIds($contatOpportunityIds);
         }
         $contactOpportunities = $ContactOpportunities->getMultiPolicyListing($contactId);
         if($contactAdditionalOpportunities)
         {
             foreach ($contactAdditionalOpportunities as $key => $value)
             {
                 $contactAdditionalOpportunities[$key]['is_additional'] = _ID_STATUS_ACTIVE;
             }
             $contactOpportunities = array_merge($contactOpportunities, $contactAdditionalOpportunities);
         }
        $countTotal = 0;
        $countInactiveCancelled = 0;
        $countCancelled = 0;
        $pendingOpportunityCount = 0;
        $activePolicyCount = 0;
        if(isset($contactOpportunities) && !empty($contactOpportunities)){
            foreach($contactOpportunities as $contact_policy){
                if($contact_policy['status'] == _ID_STATUS_ACTIVE && $contact_policy['pipeline_stage'] == _PIPELINE_STAGE_WON)
                {
                    $activePolicyCount++;
                }
                if($contact_policy['status'] == _ID_STATUS_CANCELLED || ($contact_policy['status'] == _ID_STATUS_INACTIVE && $contact_policy['inactive_sub_status'] !=  _ID_STATUS_CANCELLED)){
                    $countInactiveCancelled++;
                }
                if($contact_policy['inactive_sub_status'] == _ID_STATUS_CANCELLED && $contact_policy['status'] == _ID_STATUS_INACTIVE){
                    $countCancelled++;
                }
                if($contact_policy['status'] == _ID_STATUS_PENDING)
                {
                    $pendingOpportunityCount++;
                }
                $countTotal++;
                if(empty($contact_policy['effective_date']) && $contact_policy['pipeline_stage'] == _PIPELINE_STAGE_WON && $contact_policy['status'] == _ID_STATUS_ACTIVE)
                {
                    $ContactOpportunities->updateAll(['status' => _ID_STATUS_PENDING], ['id' => $contact_policy['id']]);
                }
            }
        }
        if($countInactiveCancelled > 0 && ($countCancelled==$countTotal)){
            if($contact['status'] != _ID_STATUS_INACTIVE){
                $Contacts->updateAll(['status' => _ID_STATUS_INACTIVE],['id' => $contactId]);
                $contact['status'] = _ID_STATUS_INACTIVE;
            }
        }
        if(($countCancelled > _ID_FAILED && $countInactiveCancelled > _ID_FAILED && $activePolicyCount == _ID_FAILED) || ($pendingOpportunityCount > _ID_FAILED && $countCancelled > _ID_FAILED && $activePolicyCount == _ID_FAILED))
        {
            $Contacts->updateAll(['status' => _ID_STATUS_INACTIVE], ['id' => $contactId]);
        }
        if($countInactiveCancelled > _ID_FAILED && $pendingOpportunityCount > _ID_FAILED && $countCancelled == _ID_FAILED && $activePolicyCount == _ID_FAILED)
        {
          $result = $Contacts->updateAll(['status' => _ID_STATUS_ACTIVE, 'lead_type' => _CONTACT_TYPE_LEAD], ['id' => (int)$contactId]);
        }
        if($countTotal > $countInactiveCancelled && $activePolicyCount != _ID_FAILED){
            if($contact['status'] != _ID_STATUS_ACTIVE){
                $Contacts->updateAll(['status' =>_ID_STATUS_ACTIVE,'winback_x_date'=>NULL,'winback_x_date_day_diff_type'=>0,'winback_start_status'=>0,'winback_scheduler_pick_status'=>_STATUS_FALSE],['id' => $contactId]);
                $campaignRunningSchedules = $CampaignRunningSchedule->getRunningCampaignListingByContactID($contactId);
                if(!empty($campaignRunningSchedules))
                {
                    foreach ($campaignRunningSchedules as $campaignRunningSchedule)
                    {
                        if($campaignRunningSchedule['agency_campaign_master']['type'] == _CAMPAIGN_TYPE_WIN_BACK_LOST_CLIENTS)
                        {
                        $CampaignRunningEmailSmsSchedule->updateCampaignRunningEmailSmsSchedule(['status' => _EMAIL_SMS_SENT_STATUS_CANCELLED],['campaign_running_schedule_id' => $campaignRunningSchedule['id'],'status IN'=>[_EMAIL_SMS_SENT_STATUS_PENDING,_EMAIL_SMS_SENT_STATUS_PROCESSING]]);
                        $CampaignRunningSchedule->updateCampaignRunningSchedule(['status' => _RUNNING_CAMPAIGN_STATUS_COMPLETED],['id'=>$campaignRunningSchedule['id'],'status'=>_RUNNING_CAMPAIGN_STATUS_ACTIVE]);
                        }
                    }


                }
                $contact['status'] = _ID_STATUS_ACTIVE;
            }
        }

       // echo "<pre>";print_r($contactOpportunities);die("Dfdsdfs");
        $return['Policies.getAllPoliciesForContact'] = [
            $contactId => $contactOpportunities
        ];
        return $return;
    }

    public static function getAllOpportunitiesByContactIdStage($objectData)
    {
        $session = Router::getRequest()->getSession(); 
        $login_agency_id = $session->read("Auth.User.agency_id"); 
        $login_user_id = $session->read('Auth.User.user_id');
        $login_role_type = $session->read('Auth.User.role_type');
        $login_role_type_flag = $session->read('Auth.User.role_type_flag');
        $movedToWonStage = 0;
        if(isset($objectData['movedToWonStage']) && !empty($objectData['movedToWonStage']) && $objectData['movedToWonStage'] == _ID_SUCCESS)
        {
            $movedToWonStage = 1;
        }
        $ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
        //$opp_details = $ContactOpportunities->getAllActiveOpportunitiesByContactId($objectData['contact_id'],$objectData['pipeline_stage_id'],$objectData['business_id']); 
        if(isset($objectData['business_id']) && !empty($objectData['business_id'])){
            $opp_details = $ContactOpportunities->getActiveOpportunitiesByContactIdVue(null,$objectData['pipeline_stage_id'],$login_agency_id,$movedToWonStage,$objectData['business_id']);
        // echo "<pre>";print_r($opp_details);die("dfd");
        }else{
            $opp_details = $ContactOpportunities->getActiveOpportunitiesByContactIdVue($objectData['contact_id'],$objectData['pipeline_stage_id'],$login_agency_id,$movedToWonStage);
            // echo "<pre>";print_r($opp_details);die("dfd");
        }
        
        if(isset($opp_details) && !empty($opp_details))
        {
            $response =  json_encode(array('status' => _ID_SUCCESS,'opportunities'=>$opp_details));
            
        }else{
            $response =  json_encode(array('status' => _ID_FAILED,'opportunities'=>''));
        }
        return $response;
       
        
       
    }

}
