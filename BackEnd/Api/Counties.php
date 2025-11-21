<?php

namespace App\Lib\ApiProviders;

use App\Lib\PermissionsCheck;
use App\Lib\QuickTables\ContactsQuickTable;
use App\Model\Entity\Contact;
use App\Model\Entity\County;
use App\Model\Table\CountiesTable;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Http\Session;
use Cake\ORM\TableRegistry;

class Counties
{
	/** @var CountiesTable $counties */
	public static function getAllCounties(){
		$counties = TableRegistry::getTableLocator()->get('counties');
		$result = $counties->getAllCounties();
		return $result;

	}
	public static function getCountyById($countyId)
    {
		$counties = TableRegistry::getTableLocator()->get('counties');
		$result = $counties->getCountyById($countyId);
        return $result;
	}

	public static function getCountyInfoByName($countyName)
	{
		$counties = TableRegistry::getTableLocator()->get('counties');
		$result = $counties->countyIdByName($countyName);
		return $result;
	}

	public static function getCountiesByStateId($stateId)
	{
		$counties = TableRegistry::getTableLocator()->get('counties');
		$result = $counties->getCountiesByStateId($stateId);
		return $result;
	}
	
}
?>