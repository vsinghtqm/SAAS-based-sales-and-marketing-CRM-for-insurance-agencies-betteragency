<?php
namespace App\Lib\ApiProviders;

use Cake\ORM\TableRegistry;

class Mga
{
	
	public static function activeMgaListing($contactId, $fields=null){
		
		$mga = TableRegistry::getTableLocator()->get('Mga');
		$result = $mga->activeMgaListing();
		$return['Mga.activeMgaListing'] = [
            $contactId => $result
        ];
        return $return;
	}
	
	
	
	
}