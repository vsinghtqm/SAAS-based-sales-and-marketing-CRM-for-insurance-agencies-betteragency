<?php

namespace App\Lib\ApiProviders;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;


/**
 * Class ReferralPartner
 */
class ReferralPartner
{
    /**
     * @param mixed $contactId
     * 
     * To show the referral partner list
     */
    public function getReferralPartnerList($contactId){
        try{
            $session = Router::getRequest()->getSession();
            $login_agency_id= $session->read("Auth.User.agency_id");
            $ReferralPartnerUser = TableRegistry::getTableLocator()->get('ReferralPartnerUser');
            $allReferralPartnerList = $ReferralPartnerUser->getAllReferralPartnerUsers($login_agency_id);
            if(isset($allReferralPartnerList) && !empty($allReferralPartnerList)){
                $i=0;
                foreach($allReferralPartnerList as $referral){
                    $list[$i]['id'] = $referral['referral_partner_id'].','.$referral['id'];
                    $list[$i]['name'] = $referral['first_name'].' '. $referral['last_name'];
                    $i++;
                }
            }

            $return['ReferralPartner.getReferralPartnerList'] = [
                $contactId => $list
            ];
            return $return;


        }catch(\Exception $e){
            $myfile = fopen(ROOT."/logs/betaContactLogs.log", "a") or die("Unable to open file!");
            $txt=date('Y-m-d H:i:s').' :: Referral Partner List Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
        
    }
    /**
     * @param mixed $contactId
     * 
     * To show the selected referral partner of contact
     */
    public function getReferralPartnerByContactId($contactId){
        try{
            $session = Router::getRequest()->getSession();
            $login_agency_id= $session->read("Auth.User.agency_id");
            $ReferralPartnerUserContacts = TableRegistry::getTableLocator()->get('ReferralPartnerUserContacts');
            $existingReferralPartnerUserContact = $ReferralPartnerUserContacts->getReferralPartnerUserContactNameByContactId($contactId);
            $referral_partner_name = "";
            if(isset($existingReferralPartnerUserContact) && !empty($existingReferralPartnerUserContact))
            {
                if(!empty($existingReferralPartnerUserContact['referral_partner_user']['first_name']))
                {
                    $referral_partner_name = $existingReferralPartnerUserContact['referral_partner_user']['first_name'];
                }
                if(!empty($existingReferralPartnerUserContact['referral_partner_user']['last_name']))
                {
                    $referral_partner_name = $referral_partner_name.' '.$existingReferralPartnerUserContact['referral_partner_user']['last_name'];
                }
            }
            $responseData = [];
            $responseData['referral_partner_id'] = $existingReferralPartnerUserContact['referral_partner_id'];
            $responseData['referral_partner_user_id'] = $existingReferralPartnerUserContact['referral_partner_user_id'];
            $responseData['referral_partner_name'] = $referral_partner_name;
             $return['ReferralPartner.getReferralPartnerByContactId'] = [
                $contactId => $responseData
            ];
            return $return;

            
        }catch(\Exception $e){
            $myfile = fopen(ROOT."/logs/betaContactLogs.log", "a") or die("Unable to open file!");
            $txt=date('Y-m-d H:i:s').' :: Contact Referral Partner Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
            

    }

}