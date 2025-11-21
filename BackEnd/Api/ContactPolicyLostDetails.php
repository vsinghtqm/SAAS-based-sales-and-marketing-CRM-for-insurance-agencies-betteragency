<?php

namespace App\Lib\ApiProviders;

use App\Lib\NowCerts\NowCertsApi;
use App\Lib\PermissionsCheck;
use App\Lib\QuickTables\ContactsQuickTable;
use App\Model\Entity\Contact;
use App\Model\Entity\UsStates;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Http\Session;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use App\Classes\CommonFunctions;

class ContactPolicyLostDetails
{
	public static function getLostReasons($agency_id,$fields=null)
    {		
		$ContactPolicyLostReasons = TableRegistry::getTableLocator()->get('ContactPolicyLostReasons');
		$ContactPolicyLostReasons = $ContactPolicyLostReasons->getLostPolicyReasosns($agency_id,$reason_type = _POLICY_CANCEL_REASON_TYPE);
		$newArray = [];
		$i = 0;
		foreach ($ContactPolicyLostReasons as $key => $value)
		{
			if($value['name'] == 'Underwriting Cancellation' || $value['name'] == 'Underwriting Reasons')
			{
				$newArray[$i]['id'] = $value['id'];
				$newArray[$i]['name'] = 'Underwriting Cancellation';
			}
			
			elseif($value['name'] == 'Insured Cancellation' || $value['name'] == "Insured's Request")
			{
				$newArray[$i]['id'] = $value['id'];
				$newArray[$i]['name'] = 'Insured Cancellation';
			}
			elseif($value['name'] == 'Non-pay' || $value['name'] == 'Non-Payment')
			{
				$newArray[$i]['id'] = $value['id'];
				$newArray[$i]['name'] = 'Non-Pay';
			}
			else
			{
				$newArray[$i]['id'] = $value['id'];
				$newArray[$i]['name'] = $value['name'];
			}
			$i++;
		}
		$return['ContactPolicyLostDetails.getLostReasons'] = [ $agency_id => $newArray ];
        return $return;    
	}
	public static function getLostCarriers($agency_id,$fields=null)
    {		
		$ContactPolicyLostCarriers = TableRegistry::getTableLocator()->get('ContactPolicyLostCarriers');
		$lostpolicycarriers = $ContactPolicyLostCarriers->combinedMasterPlusAgencyCarriers($agency_id);
		$return['ContactPolicyLostDetails.getLostCarriers'] = [
            $agency_id => $lostpolicycarriers
        ];		
        return $return;    
	}
	public static function getLostSubReasons($login_agency_id,$fields)
    {		
		$reason_master_id = _LOST_POLICY_REASONS_UNDERWRITING;
		$ContactPolicyLostReasons = TableRegistry::getTableLocator()->get('ContactPolicyLostReasons');		
		$lostpolicysubreasons = $ContactPolicyLostReasons->combinedMasterPlusAgencyReasonsByMasterId($login_agency_id,$reason_master_id);
		$return['ContactPolicyLostDetails.getLostSubReasons'] = [
            $login_agency_id => $lostpolicysubreasons
        ];		
        return $return;    
	}
	public static function getLostDirectCarriers($agency_id,$fields=null)
    {		
		$ContactPolicyLostCarriers = TableRegistry::getTableLocator()->get('ContactPolicyLostCarriers');		
		$lostpolicycarriersDirect=$ContactPolicyLostCarriers->combinedMasterPlusAgencyCarriersDirect($agency_id);
		$return['ContactPolicyLostDetails.getLostDirectCarriers'] = [
            $agency_id => $lostpolicycarriersDirect
        ];		
        return $return;    
	}
	public static function getLostCaptiveCarriers($agency_id,$fields=null)
    {		
		$ContactPolicyLostCarriers = TableRegistry::getTableLocator()->get('ContactPolicyLostCarriers');	
		$lostpolicycarriersCaptive = $ContactPolicyLostCarriers->combinedMasterPlusAgencyCarriersCaptive($agency_id);				
		$return['ContactPolicyLostDetails.getLostCaptiveCarriers'] = [
            $agency_id => $lostpolicycarriersCaptive
        ];		
        return $return;    
	}
	public static function getLostPolicyDetails($opportunity_id,$fields)
    {
		$session = Router::getRequest()->getSession(); 
		$login_agency_id = $session->read("Auth.User.agency_id");
		$opportunity_id = $opportunity_id;
		$opportunity_id = explode('CO-',$opportunity_id);
		$opportunity_id = $opportunity_id[1];
		$ContactPolicyLostDetails = TableRegistry::getTableLocator()->get('ContactPolicyLostDetails');
		$policyLostDetails = $ContactPolicyLostDetails->getLostPolicyDetails($login_agency_id,$opportunity_id);
		$return['ContactPolicyLostDetails.getLostPolicyDetails'] = [
            $login_agency_id => $policyLostDetails
        ];		
        return $return;
	}
	
	public function saveLostPolicyDetails($objectData)
	{
		$session = Router::getRequest()->getSession();
		$login_agency_id = $session->read("Auth.User.agency_id");
        $login_user_id =  $session->read('Auth.User.user_id');
        $login_role_type= $session->read('Auth.User.role_type');
        $login_role_type_flag = $session->read('Auth.User.role_type_flag');
		$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
		$AgencyCampaignMaster = TableRegistry::getTableLocator()->get('AgencyCampaignMaster');
		$ContactPolicyLostDetails = TableRegistry::getTableLocator()->get('ContactPolicyLostDetails');
		$ContactPolicyLostReasons = TableRegistry::getTableLocator()->get('ContactPolicyLostReasons');
		$ContactPolicyLostCarriers = TableRegistry::getTableLocator()->get('ContactPolicyLostCarriers');
		$renewalPipeline = TableRegistry::getTableLocator()->get('RenewalPipeline');
		$Contacts = TableRegistry::getTableLocator()->get('Contacts');
		if(isset($objectData['opportunity_id']) && !empty($objectData['opportunity_id']))
		{
			$opportunity_id = $objectData['opportunity_id'];
			$opportunity_id = explode('CO-',$opportunity_id);
			$opportunity_id = $opportunity_id[1];
		}
        $currentDate = date("Y-m-d");
		$cancellation_date_cc = date('Y-m-d');
        if(isset($objectData['canceldate']) && !empty($objectData['canceldate'])){
            $cancellation_date_cc=date("Y-m-d",strtotime($objectData['canceldate']));
        }
		//opp detail
        $contact_opportunity_detail = $ContactOpportunities->contactOpportunityDetail($opportunity_id);
		// echo '<pre>';
		// print_r($contact_opportunity_detail);
		// die();
        //cancellation date is lower and equal to current date - case 2
        $ContactOpportunities->updateAll(['cancellation_date'=> $cancellation_date_cc], ['id' => $opportunity_id]);
        if(strtotime($cancellation_date_cc) <= strtotime($currentDate))
        {
            $updateOpportunities = $ContactOpportunities->updateAll(['status' => _ID_STATUS_INACTIVE, 'inactive_sub_status' => _ID_INACTIVE_SUB_STATUS_CANCELLED], ['id' => $opportunity_id]);

            if($contact_opportunity_detail['pipeline_stage'] == _PIPELINE_STAGE_WON)
            {
                if($contact_opportunity_detail['primary_flag'] == _ID_STATUS_ACTIVE)
                {
                    //update primary flag of another active policy
                    if(isset($contact_opportunity_detail['contact_business_id']) && !empty($contact_opportunity_detail['contact_business_id']))
                    {
                        //get another active policy for business
                        $active_policy_to_mark_primary = $ContactOpportunities->getBusActivePolicyToMarkPrimaryAfterDeleteOrInactive($contact_opportunity_detail['contact_business_id']);
                    }
                    else
                    {
                        //get another active policy for business
                        $active_policy_to_mark_primary = $ContactOpportunities->getConActivePolicyToMarkPrimaryAfterDeleteOrInactive($contact_opportunity_detail['contact_id']);
                    }
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
                    //update primary flag of another lost policy
                    if(isset($contact_opportunity_detail['contact_business_id']) && !empty($contact_opportunity_detail['contact_business_id']))
                    {
                        //get another lost policy for business
                        $lost_policy_to_mark_primary = $ContactOpportunities->getBusLostPolicyToMarkPrimaryAfterDeleteOrInactive($contact_opportunity_detail['contact_business_id']);
                    }
                    else
                    {
                        //get another lost policy for business
                        $lost_policy_to_mark_primary = $ContactOpportunities->getConLostPolicyToMarkPrimaryAfterDeleteOrInactive($contact_opportunity_detail['contact_id']);
                    }
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
                    //update primary flag of another pending policy
                    if(isset($contact_opportunity_detail['contact_business_id']) && !empty($contact_opportunity_detail['contact_business_id']))
                    {
                        //get another pending policy for business
                        $pending_policy_to_mark_primary = $ContactOpportunities->getBusPendingPolicyToMarkPrimaryAfterDeleteOrInactive($contact_opportunity_detail['contact_business_id'],$contact_opportunity_detail['pipeline_stage']);
                    }
                    else
                    {
                        //get another pending policy for business
                        $pending_policy_to_mark_primary = $ContactOpportunities->getConPendingPolicyToMarkPrimaryAfterDeleteOrInactive($contact_opportunity_detail['contact_id'],$contact_opportunity_detail['pipeline_stage']);
                    }
                    if(isset($pending_policy_to_mark_primary) && !empty($pending_policy_to_mark_primary))
                    {
                        $ContactOpportunities->updateAll(['primary_flag_all_stages'=>_ID_FAILED],['id'=>$opportunity_id]);
                        $ContactOpportunities->updateAll(['primary_flag_all_stages' =>_ID_STATUS_ACTIVE],['id' => $pending_policy_to_mark_primary['id']]);
                    }
                }
            }
			CommonFunctions::saveCancelledPolicyLogForContact($opportunity_id);
			CommonFunctions::savePolicyLogForContact($opportunity_id);
            NowCertsApi::updateIntoNowcerts($contact_opportunity_detail['contact_id'],null, $opportunity_id, null);
            if($updateOpportunities){
                $checkCampaignExist=$AgencyCampaignMaster->checkCampaignExist(null,null,$login_agency_id,_POLICY_CANCELLED_STAGE_INITIATE,_CAMPAIGN_TYPE_POLICY_CANCELLED);
                if(!empty($checkCampaignExist)){
                    $start_campaign_id=$checkCampaignExist['id'];
                    $campaign_result = CommonFunctions::startCampaign($contact_opportunity_detail['contact_id'], $start_campaign_id,_POLICY_CANCELLED_STAGE_INITIATE,$contact_opportunity_detail['id']);
                }
            }
        }
        //end
		
		//save cancellation note  - case 4
        $note_policy_cancel_arr=array();
        $note_policy_cancel_arr['login_agency_id']=$login_agency_id;
        $note_policy_cancel_arr['login_user_id']=$login_user_id;
        $note_policy_cancel_arr['opportunity_id']=$contact_opportunity_detail['id'];
        $note_policy_cancel_arr['contact_id']=$contact_opportunity_detail['contact_id'];
        $note_policy_cancel_arr['insurance_type_id']=$contact_opportunity_detail['insurance_type_id'];
        $note_policy_cancel_arr['policy_number']=$contact_opportunity_detail['policy_number'];
        $note_policy_cancel_arr['cancellation_date_cc']=$cancellation_date_cc;
        CommonFunctions::saveNotePolicyCancel($note_policy_cancel_arr);
        //end
		
		//lost policy reasons details
		//check for entry in lost policy details table
		$lostPolicyDetail = $ContactPolicyLostDetails->find('all')->where(['ContactPolicyLostDetails.agency_id' => $login_agency_id, 'ContactPolicyLostDetails.contact_opportunities_id' => $opportunity_id, 'ContactPolicyLostDetails.lost_type' => CONTACT_LOST_TYPE_POLICY, 'ContactPolicyLostDetails.ivans_renewed_status' => _ID_FAILED, 'ContactPolicyLostDetails.status' => _ID_STATUS_ACTIVE])->first();
		$lost_policy_reason_id = 0;
		$lost_policy_carrier_id = 0;
		$lost_premium_amount = 0;
		$lost_term_length = '';
		$lost_cancellation_date = '';
		if(isset($objectData['selectedLostReason']) && !empty($objectData['selectedLostReason'])){
			$data = [];
			$lost_policy_reason_id=$objectData['selectedLostReason'];
			if($lost_policy_reason_id == _LOST_POLICY_REASONS_OTHER && isset($objectData['selectedotherLostReason']) && !empty($objectData['selectedotherLostReason'])){
				// if type = other insert reason
				$checkReason = $ContactPolicyLostReasons->checkReason($objectData['selectedotherLostReason'],$login_agency_id);
				if(isset($checkReason) && !empty($checkReason)){
					$lost_policy_reason_id = $checkReason['id'];
				}else{
				$data['name'] = $objectData['selectedotherLostReason'];
				$data['agency_id']=$login_agency_id;
				$data['user_id']=$login_user_id;
				$lostPolicyReasonDetail = $ContactPolicyLostReasons->newEntity();
				$lostPolicyReasonDetail = $ContactPolicyLostReasons->patchEntity($lostPolicyReasonDetail,$data);
				$lostPolicyReasonDetail = $ContactPolicyLostReasons->save($lostPolicyReasonDetail);
				$lost_policy_reason_id = $lostPolicyReasonDetail->id;
				}
			}elseif(($lost_policy_reason_id == _LOST_POLICY_REASONS_UNDERWRITING || $lost_policy_reason_id == _LOST_POLICY_REASONS_INSURED || $lost_policy_reason_id == _LOST_POLICY_REASONS_NON_RENEWAL) && isset($objectData['lostPolicySubReasons']) && !empty($objectData['lostPolicySubReasons'])){
                $lost_policy_reason_id = $objectData['selectedLostsubReason'];
            }

		}
		if(isset($objectData['selectedLostCarrier']) && !empty($objectData['selectedLostCarrier'])){
			$data = [];
			$lost_policy_carrier_id=$objectData['selectedLostCarrier'];
			if($lost_policy_carrier_id == _LOST_POLICY_TO_INDEPENDENT && isset($objectData['selectedOtherCarrier']) && !empty($objectData['selectedOtherCarrier'])){
				// if type = other insert carrier
				$checkCarrier = $ContactPolicyLostCarriers->checkCarrier($objectData['selectedOtherCarrier'],$login_agency_id);
				if(isset($checkCarrier) && !empty($checkCarrier)){
					$lost_policy_carrier_id = $checkCarrier['id'];
				}else{
				$data['name'] = $objectData['selectedOtherCarrier'];
				$data['agency_id']=$login_agency_id;
				$data['user_id']=$login_user_id;
				$lostPolicyCarrierDetail = $ContactPolicyLostCarriers->newEntity();
				$lostPolicyCarrierDetail = $ContactPolicyLostCarriers->patchEntity($lostPolicyCarrierDetail,$data);
				$lostPolicyCarrierDetail = $ContactPolicyLostCarriers->save($lostPolicyCarrierDetail);
				$lost_policy_carrier_id = $lostPolicyCarrierDetail->id;
				}
			}
		}
		if(isset($objectData['selectedLostDirectCarrier']) && !empty($objectData['selectedLostDirectCarrier'])){
			$data = [];
			$lost_policy_carrier_id=$objectData['selectedLostDirectCarrier'];
			if($lost_policy_carrier_id == _LOST_POLICY_TO_DIRECT_OTHER && isset($objectData['selectedOtherDirectCarrier']) && !empty($objectData['selectedOtherDirectCarrier'])){
				// if type = other insert carrier
				$checkCarrier = $ContactPolicyLostCarriers->checkCarrierByMasterId($objectData['selectedOtherDirectCarrier'],$login_agency_id,_LOST_POLICY_TO_DIRECT);
				if(isset($checkCarrier) && !empty($checkCarrier)){
					$lost_policy_carrier_id = $checkCarrier['id'];
				}else{
				$data['name'] = $objectData['selectedOtherDirectCarrier'];
				$data['agency_id']=$login_agency_id;
				$data['user_id']=$login_user_id;
				$data['master_carrier_id'] = _LOST_POLICY_TO_DIRECT;
				$lostPolicyCarrierDirectDetail = $ContactPolicyLostCarriers->newEntity();
				$lostPolicyCarrierDirectDetail = $ContactPolicyLostCarriers->patchEntity($lostPolicyCarrierDirectDetail,$data);
				$lostPolicyCarrierDirectDetail = $ContactPolicyLostCarriers->save($lostPolicyCarrierDirectDetail);
				$lost_policy_carrier_id = $lostPolicyCarrierDirectDetail->id;
				}
			}
		}
		
		if(isset($objectData['selectedLostCaptiveCarrier']) && !empty($objectData['selectedLostCaptiveCarrier'])){
			$data = [];
			$lost_policy_carrier_id=$objectData['selectedLostCaptiveCarrier'];
			if($lost_policy_carrier_id == _LOST_POLICY_TO_CAPTIVE_OTHER && isset($objectData['selectedOtherCaptiveCarrier']) && !empty($objectData['selectedOtherCaptiveCarrier'])){
				// if type = other insert carrier
				$checkCarrier = $ContactPolicyLostCarriers->checkCarrierByMasterId($objectData['selectedOtherCaptiveCarrier'],$login_agency_id,_LOST_POLICY_TO_CAPTIVE);
				if(isset($checkCarrier) && !empty($checkCarrier)){
					$lost_policy_carrier_id = $checkCarrier['id'];
				}else{
				$data['name'] = $objectData['selectedOtherCaptiveCarrier'];
				$data['agency_id']=$login_agency_id;
				$data['user_id']=$login_user_id;
				$data['master_carrier_id'] = _LOST_POLICY_TO_CAPTIVE;
				$lostPolicyCarrierCaptiveDetail = $ContactPolicyLostCarriers->newEntity();
				$lostPolicyCarrierCaptiveDetail = $ContactPolicyLostCarriers->patchEntity($lostPolicyCarrierCaptiveDetail,$data);
				$lostPolicyCarrierCaptiveDetail = $ContactPolicyLostCarriers->save($lostPolicyCarrierCaptiveDetail);
				$lost_policy_carrier_id = $lostPolicyCarrierCaptiveDetail->id;
				}
			}
		}
		if(isset($objectData['selectedPremium']) && !empty($objectData['selectedPremium'])){
			$lost_premium_amount=$objectData['selectedPremium'];
		}
		if(isset($objectData['canceldate']) && !empty($objectData['canceldate'])){
			$lost_cancellation_date=date("Y-m-d",strtotime($objectData['canceldate']));
		}
		if(isset($objectData['selectedTermLength']) && !empty($objectData['selectedTermLength'])){
			$lost_term_length = 0;
			if($objectData['selectedTermLength'] == 'Other'){
				if(isset($objectData['selectedTermLengthYear']) && !empty($objectData['selectedTermLengthYear']))
				{
					$lost_term_length =  $objectData['selectedTermLengthYear']*12;
				}
				else{
					$lost_term_length =0;
				}
				if(isset($objectData['selectedTermLengthMonth']) && !empty($objectData['selectedTermLengthMonth']))
				{
					$lost_term_length =  $lost_term_length+$objectData['selectedTermLengthMonth'];
				}
				else{
					$lost_term_length =$lost_term_length;
				}

			}else{
				$lost_term_length=$objectData['selectedTermLength'];
			}
		}

		$data = [];
		$data['agency_id'] = $login_agency_id;
		$data['user_id'] = $login_user_id;
		$data['contact_opportunities_id'] = $opportunity_id;
		$data['lost_policy_reason_id'] = isset($lost_policy_reason_id) ? $lost_policy_reason_id : 0;
		$data['lost_policy_carrier_id'] = $lost_policy_carrier_id;
		$data['premium_amount'] = $lost_premium_amount;
		$data['term_length'] = $lost_term_length;
		$data['cancellation_date'] = $lost_cancellation_date;
		$term_length = 12;
		if(isset($lost_term_length) && !empty($lost_term_length))
		{
			$term_length = $lost_term_length;
		}
		$policy_cancellation_date = date('Y-m-d');
		if(isset($lost_cancellation_date) && !empty($lost_cancellation_date))
		{
			$policy_cancellation_date = $lost_cancellation_date;
		}
		$policy_x_date = date('Y-m-d',strtotime("+ ".$term_length." months",strtotime($policy_cancellation_date)));
		$data['policy_x_date']=$policy_x_date;
		$data['running_scheduler_flag'] = _ID_STATUS_PENDING;

         //save lost details
		if(empty($lostPolicyDetail)){
			$lostPolicyDetail = $ContactPolicyLostDetails->newEntity();
			$lostPolicyDetail = $ContactPolicyLostDetails->patchEntity($lostPolicyDetail,$data);
			$lostPolicyDetail = $ContactPolicyLostDetails->save($lostPolicyDetail);
		}else{
			$lostPolicyDetail = $ContactPolicyLostDetails->get($lostPolicyDetail['id']);
			$lostPolicyDetail = $ContactPolicyLostDetails->patchEntity($lostPolicyDetail,$data);
			$lostPolicyDetail = $ContactPolicyLostDetails->save($lostPolicyDetail);
		}
		$winback_x_date = date('Y-m-d',strtotime("+ ".$term_length." months",strtotime($policy_cancellation_date)));
		$lostPolicyDetails = $ContactPolicyLostDetails->find('all')->contain(['ContactOpportunities'])->where(['ContactPolicyLostDetails.agency_id'=>$login_agency_id,'ContactPolicyLostDetails.contact_opportunities_id'=>$opportunity_id,'ContactPolicyLostDetails.lost_type'=>CONTACT_LOST_TYPE_POLICY,'ContactPolicyLostDetails.ivans_renewed_status'=>_ID_FAILED])->first();
		//update last_policy_cancelled_date in contacts table
		if(isset($lostPolicyDetails['cancellation_date']) && !empty($lostPolicyDetails['cancellation_date'])){
			if(isset($lostPolicyDetails['contact_opportunity']) && !empty($lostPolicyDetails['contact_opportunity'])){
				$contact_id = $lostPolicyDetails['contact_opportunity']['contact_id'];
				$updateContact = $Contacts->updateAll(['last_policy_cancelled_date'=>$lostPolicyDetails['cancellation_date'],'winback_x_date'=>$lostPolicyDetails['policy_x_date']],['id' => $contact_id]);
			}
		}else{
				$getids = $ContactOpportunities->get($opportunity_id);
				$contact_id = $getids->contact_id;
				$updateContact = $Contacts->updateAll(['last_policy_cancelled_date'=>date('Y-m-d'),'winback_x_date'=>$winback_x_date],['id' => $contact_id]);
			}
		echo json_encode(array('status' => _ID_SUCCESS));
		die;
	}
		
}
?>