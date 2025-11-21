<?php

namespace App\Lib\ApiProviders;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use App\Classes\CommonFunctions;

use App\Lib\QuickTables\ContactDetailsQuickTable;
use App\Lib\QuickTables\ContactsQuickTable;
use App\Lib\QuickTables\ContactBusinessQuickTable;
use App\Lib\QuickTables\ContactAttachmentsQuickTable;
use App\Lib\QuickTables\UsersQuickTable;
use Cake\Http\Exception\UnauthorizedException;
use App\Lib\BetterAgency\ObjectFetcher;


class FileUpload
{
	public function uploadAttachment($request)
	{
		$contactId = $request['contactId'];
		$fileInfo = $request['fileInfo'];
		//upload to s3
		//save contact attachment
		//explode universal id on - p = contact, c = commercial
		return json_encode($fileInfo);
	}

}