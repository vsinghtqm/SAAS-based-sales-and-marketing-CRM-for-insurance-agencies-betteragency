<?php
namespace App\Lib\ApiProviders;

use App\Lib\PermissionsCheck;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Http\Session;
use Cake\ORM\TableRegistry;
use App\Lib\QuickTables\ContactsQuickTable;
use App\Model\Entity\Contact;
use Cake\Routing\Router;


class Carriers
{
	
	public static function activeParentCarrierListing($contactId, $fields=null){
		
		$carriers = TableRegistry::getTableLocator()->get('Carriers');
		$result = $carriers->agencyActiveParentCarrierListing();
		$return['Carriers.activeParentCarrierListing'] = [
            $contactId => $result
        ];
        return $return;
	}
	
	public static function getCarriersOfPolicy($policyId){
		
		try {
			$myFile = fopen(ROOT."/logs/getCarriersOfPolicy.log", "a") or die("Unable to open file!"); 
			$session = Router::getRequest()->getSession(); 
			$loginAgencyId = $session->read("Auth.User.agency_id");
			$carriersPolicyTypesRelationship = TableRegistry::getTableLocator()->get('CarriersPolicyTypesRelationship');
			$data = $carriersPolicyTypesRelationship->agencyActiveParentCarrierListingAssociatedPolicy($policyId, $loginAgencyId);
			$result = [];

			if(isset($data) && !empty($data))
			{
				foreach($data as $val)
				{
					$result[] = $val['carrier'];
				}
			}

			$return['Carriers.getCarriersOfPolicy'] = [
				$policyId => $result
			];
        	return $return;
		} catch (\Exception $e) {
            $txt = date('Y-m-d H:i:s').' :: Carrier Listing Error - '.$e->getMessage();
			fwrite($myFile, $txt.PHP_EOL);
        }
		
	}

    public function getAllChildCarriersList($policy_id){
        $session = Router::getRequest()->getSession();
        $loginAgencyId = $session->read("Auth.User.agency_id");
        $agencyCarriersLink = TableRegistry::getTableLocator()->get('CarriersPolicyTypesRelationship');
        $carriers = TableRegistry::getTableLocator()->get('Carriers');
        $carriersList = $agencyCarriersLink->agencyActiveParentCarrierListingAssociatedPolicy($policy_id,$loginAgencyId);
            //array map function to return id from array of objects
            $carrierIds = array_map(function($obj) {
                return $obj['carrier']['id'];
                }, $carriersList);
        // query to get the all carriers having id and master_carrier_id is in $carrierIds
        $masterCarriers = [];
        if(isset($carrierIds) && $carrierIds != '' && count($carrierIds) > 0){
            $masterCarriers = $carriers->getMasterCarrierWithChildCarrier($carrierIds);
        }
        $return['Carriers.getCarriersOfPolicy'] = [
				$policy_id => $masterCarriers
			];
        return $return;
    }
	public static function getAllActiveCarriersOfAgency(){
		
		try {
			$myFile = fopen(ROOT."/logs/getAllActiveCarriersOfAgency.log", "a") or die("Unable to open file!"); 
			$session = Router::getRequest()->getSession(); 
			$loginAgencyId = $session->read("Auth.User.agency_id");
			$agencyCarriersLink = TableRegistry::getTableLocator()->get('AgencyCarriersLink');
			$activeCarrierListing = $agencyCarriersLink->getAllActiveCarriersOfAgency($loginAgencyId);

			$result = [];
			
			if(isset($activeCarrierListing) && !empty($activeCarrierListing))
			{
				foreach($activeCarrierListing as $val)
				{
					$result[] = $val['carrier'];
				}
			}

			$return['Carriers.getAllActiveCarriersOfAgency'] = [
				$loginAgencyId => $result
			];
        	return $return;
			
		} catch (\Exception $e) {
            $txt = date('Y-m-d H:i:s').' :: Carrier Listing Error - '.$e->getMessage();
			fwrite($myFile, $txt.PHP_EOL);
        }
		
	}
    public static function getParentCarrierId($objectData){

		try {
            $myFile = fopen(ROOT."/logs/getAllActiveCarriersOfAgency.log", "a") or die("Unable to open file!");
            fopen(ROOT."/logs/getCarriersOfPolicy.log", "a") or die("Unable to open file!");
			$session = Router::getRequest()->getSession();
			$loginAgencyId = $session->read("Auth.User.agency_id");
			$Carriers = TableRegistry::getTableLocator()->get('Carriers');
            if(!empty($objectData['selectedCarrierId']))
			{
				$carrierId = $objectData['selectedCarrierId'];
			}
			$data = $Carriers->getMasterCarrierIdWithChildCarrierId($carrierId);
			$result = [];

			if(isset($data) && !empty($data))
			{
				foreach($data as $val)
				{
					$result[] = $val['master_carrier_id'];
				}
			}

			$return['Carriers.getParentCarrierId'] = [
				$carrierId => $result
			];
        	return $return;
		} catch (\Exception $e) {
            $txt = date('Y-m-d H:i:s').' :: Carrier Listing Error - '.$e->getMessage();
			fwrite($myFile, $txt.PHP_EOL);
        }

	}

    public function getAllParentCarriersList($policyId)
    {
        $session = Router::getRequest()->getSession();
        $loginAgencyId = $session->read("Auth.User.agency_id");
        $agencyCarriersLink = TableRegistry::getTableLocator()->get('CarriersPolicyTypesRelationship');
        $carriers = TableRegistry::getTableLocator()->get('Carriers');
        $carriersList = $agencyCarriersLink->agencyActiveParentCarrierListingAssociatedPolicy($policyId, $loginAgencyId);
        $i = 0;
        foreach ($carriersList as $carrier)
        {
            $result[$i] = $carrier['carrier'];
            $i ++ ;
        }
        $return['Carriers.getCarriersOfPolicy'] = [
				$policyId => $result,
			];
        return $return;
    }

    //All child carriers of parent carrier
    public function getChildCarrierByParentId($objectData)
    {
        $session = Router::getRequest()->getSession();
        $loginAgencyId = $session->read("Auth.User.agency_id");
        $parentId = $objectData['selectedParentCarrierId'];
        $Carriers = TableRegistry::getTableLocator()->get('Carriers');
        $childCarries =$Carriers->childCarrierWithParentCarrierListing($parentId, $loginAgencyId);
        $return['Carriers.getChildCarrierByParentId'] = [
				$parentId => $childCarries,
			];
        return $return;
    }
	// get all parents id
	public function getAllParentsCarriersByAgency($agencyId)
	{
        $agencyIds = [0, $agencyId];
        $carriers = TableRegistry::getTableLocator()->get('Carriers');
        $carriersList = $carriers->getCarrierByAgencyIds($agencyIds);
        $return['Carriers.getAllParentsCarriersByAgency'] = [
            $agencyId => $carriersList,
        ];
        return $return;
	}
}