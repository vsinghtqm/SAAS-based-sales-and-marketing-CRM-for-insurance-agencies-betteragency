<?php

namespace App\Lib\ApiProviders;

use App\Lib\PermissionsCheck;
use App\Lib\QuickTables\ContactsQuickTable;
use App\Model\Entity\Contact;
use App\Model\Entity\ContactNoteTypes;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Http\Session;
use Cake\ORM\TableRegistry;

class NoteTypes
{
	public static function getMasterNoteType($contactId, $fields){
		$ContactNoteTypeTable = TableRegistry::getTableLocator()->get('ContactNoteTypes');
		$result = $ContactNoteTypeTable->find()->select(['id', 'note_type'])->limit(10)->hydrate(false)->toArray();
		$return['NoteTypes.getMasterNoteType'] = [
            $contactId => $result
        ];
        return $return;
	}
	
	
}
	?>