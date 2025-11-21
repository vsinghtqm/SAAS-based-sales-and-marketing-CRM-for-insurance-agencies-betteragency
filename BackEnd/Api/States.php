<?php

namespace App\Lib\ApiProviders;

use App\Lib\PermissionsCheck;
use App\Lib\QuickTables\ContactsQuickTable;
use App\Model\Entity\Contact;
use App\Model\Entity\UsStates;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Http\Session;
use Cake\ORM\TableRegistry;

class States
{
	public static function getStates($contactId, $fields=null)
    {
      
		$UsStates = TableRegistry::getTableLocator()->get('UsStates');
		$result = $UsStates->find('all')->select(['id','name'])->order(['name'=>'ASC'])->toArray();
		$return['States.getStates'] = [
            $contactId => $result
        ];
        return $return;
	}
	
	
}
?>