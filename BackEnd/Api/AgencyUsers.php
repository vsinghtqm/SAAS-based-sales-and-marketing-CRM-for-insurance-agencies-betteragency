<?php

namespace App\Lib\ApiProviders;

use App\Lib\PermissionsCheck;
use App\Lib\QuickTables\ContactsQuickTable;
use App\Model\Entity\Agency;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Http\Session;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

class AgencyUsers
{
	public static function getUsers($contactId, $fields=null)
    {
        try
		{
			$myfile = fopen(ROOT."/logs/vueOverviewListing.log", "a") or die("Unable to open file!");
            $return = [
                'Users' => []
            ];
            $session = Router::getRequest()->getSession();
            $login_user_id= $session->read("Auth.User.user_id");
            $login_agency_id= $session->read("Auth.User.agency_id");
            $agency = TableRegistry::getTableLocator()->get('Agency');
            $UserLinks = TableRegistry::getTableLocator()->get('UserLinks');
            $users =  $UserLinks->agencyUsersListWithAgency($login_agency_id);
            //$users = $agency->agencyUsers($login_agency_id);
            $i = 0;
            foreach($users as $user){
                $return['Users'][$i]['id'] = $user['user']['id'];
                $return['Users'][$i]['name'] = ucfirst($user['user']['first_name']).' '.ucfirst($user['user']['last_name']);
                $i++;
            }
            $return['AgencyUsers.getUsers'] = [
                $contactId => $return['Users']
            ];
            return $return;
        }catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: Overview Lising Error- '.$e->getMessage();
			//fwrite($myfile,$txt.PHP_EOL);
        }
	}

    public static function getOwners($contactId, $fields)
    {
        try
		{
			$myfile = fopen(ROOT."/logs/vueOverviewListing.log", "a") or die("Unable to open file!");
            $return = [
                'Owners' => []
            ];
            $session = Router::getRequest()->getSession();
            $login_user_id= $session->read("Auth.User.user_id");
            $login_agency_id= $session->read("Auth.User.agency_id");
            $UserLinks = TableRegistry::getTableLocator()->get('UserLinks');
            $users =  $UserLinks->agencyUsersListWithAgency($login_agency_id);
            $i = 0;
            foreach($users as $user){
                $return['Owners'][$i]['id'] = $user['user']['id'];
                $return['Owners'][$i]['name'] = ucfirst($user['user']['first_name']).' '.ucfirst($user['user']['last_name']);
                $i++;
            }
            $return['Owners'][$i]['id'] = 'service_team';
            $return['Owners'][$i]['name'] = "Service Team";
            $return['Owners'][$i+1]['id'] = '';
            $return['Owners'][$i+1]['name'] = "No current owner for this opportunity";
            $return['AgencyUsers.getOwners'] = [
                $contactId => $return['Owners']
            ];
            return $return;
        }catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: Overview Lising Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
	}

    public static function getAgency($agencyId, $fields)
    {
        try
		{
			$myfile = fopen(ROOT."/logs/vueOverviewListing.log", "a") or die("Unable to open file!");
            $session = Router::getRequest()->getSession();
            $login_user_id= $session->read("Auth.User.user_id");
            $login_agency_id= $session->read("Auth.User.agency_id");
            $agency = TableRegistry::getTableLocator()->get('Agency');
            $agencyDetails = $agency->agencyDetails($login_agency_id);
            $return['AgencyUsers.getAgency'] = [
                $agencyId => $agencyDetails
            ];
            return $return;
        }catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: Overview Lising Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
	}

    public static function getAgencyLeadSources($contactId, $fields)
    {
        $session = Router::getRequest()->getSession();
		$login_agency_id = $session->read('Auth.User.agency_id');
        $LeadSourceTable = TableRegistry::getTableLocator()->get('LeadSource');
        $lead_source_arr = $LeadSourceTable->activeLeadSourceListByAgencyId($login_agency_id);
        $lead_sources = array();
        if(isset($lead_source_arr) && !empty($lead_source_arr)){
            $i=0;
            foreach($lead_source_arr as $lead_source){
                $lead_sources[$i]['value'] = $lead_source['id'];
                $lead_sources[$i]['name'] = $lead_source['name'];
                $i++;
            }
        }
        $return['AgencyUsers.getAgencyLeadSources'] = [
            $contactId => $lead_sources
        ];
        return $return;
	}
    

}
?>
