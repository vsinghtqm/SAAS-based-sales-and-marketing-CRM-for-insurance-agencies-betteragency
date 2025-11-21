<?php

namespace App\Lib\ApiProviders;

use App\Lib\PermissionsCheck;
use App\Lib\QuickTables\ContactsQuickTable;
use App\Model\Entity\Contact;
use App\Model\Entity\UsStates;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Http\Session;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use App\Classes\LegacyTokenExchange;

class Users
{
	public static function getUsers($agencyId, $fields=null)
    {
      
		$UserLinks = TableRegistry::getTableLocator()->get('UserLinks');
		$UserLinks = $UserLinks->agencyUsersListWithAgencyActive($agencyId);
		$return['Users.getUsers'] = [
            $agencyId => $UserLinks
        ];
        return $return;
	}

	public static function getUser($contactId)
    {
		$session = Router::getRequest()->getSession(); 
		$login_user_id= $session->read("Auth.User.user_id");
		$login_agency_id= $session->read("Auth.User.agency_id");

		$Users = TableRegistry::getTableLocator()->get('Users');
		$User = $Users->activeUserDetail($login_user_id);
		$return['Users.getUser'] = [
            $contactId => $User
        ];
        return $return;
	}
	public static function getLeadSourceName($leadSourceTypeId)
    {
		$session = Router::getRequest()->getSession(); 
		$login_user_id= $session->read("Auth.User.user_id");
		$login_agency_id= $session->read("Auth.User.agency_id");

		$LeadSource = TableRegistry::getTableLocator()->get('LeadSource');
		$leadSourceType = $LeadSource->find('all')->where(['id'=>$leadSourceTypeId,'status'=>_ID_STATUS_ACTIVE])->order(['id'=>'DESC'])->first();
       
		$return['Users.getLeadSourceName'] = [
            $leadSourceTypeId => $leadSourceType
        ];
        return $return;
	}
	public static function redirectToEmailConfigUrlFromBeta($contactId)
    {
		$session = Router::getRequest()->getSession(); 
		$loginUserId= $session->read("Auth.User.user_id");
		$loginUserEmail= $session->read("Auth.User.email");
        $baseUrl = rtrim(\Cake\Core\Configure::read('api_host', ''), '/');
        $url = $baseUrl . "/v1/mailboxes/oauth-flow?token=" . LegacyTokenExchange::getTokenForUser($loginUserId) . "&email=" . $loginUserEmail;
		$return['Users.redirectToEmailConfigUrlFromBeta'] = [
            $contactId => $url
        ];
		return $return;
    }
}
?>