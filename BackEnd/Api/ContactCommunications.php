<?php

namespace App\Lib\ApiProviders;

use App\Classes\TagsRemove;
use App\Lib\PermissionsCheck;
use App\Lib\QuickTables\ContactCommunicationsQuickTable;
use App\Lib\QuickTables\ContactsQuickTable;
use App\Model\Entity\Contact;
use App\Model\Entity\ContactCommunication;
use Cake\Http\Exception\UnauthorizedException;
use Google\Exception;
use App\Classes\CommonFunctions;
use Cake\ORM\TableRegistry;
use App\Classes\Aws;
use Cake\Routing\Router;

class ContactCommunications
{
    public static function getCommunicationsForContact($contactId, $details){
        $type = null;
        $lastCommunicationsDateTime = null;

        $return = [
            'ContactCommunication' => []
        ];

        if(isset($details['type'])){
            $type = $details['type'];
        }

        if(isset($details['lastCommunicationsDateTime'])){
            $lastCommunicationsDateTime = $details['lastCommunicationsDateTime'];
        }

        if(!PermissionsCheck::currentUserHasReadPermission(ContactsQuickTable::findById($contactId))) {
            throw new UnauthorizedException();
        }

        /** @var ContactCommunication[] $contactCommunications */
        $contactCommunications = ContactCommunicationsQuickTable::findAllBy(['contact_id' => $contactId,'ContactCommunications.status !='=>_ID_STATUS_DELETED], ['Users','ContactCommunicationsMedia']);

        foreach($contactCommunications as $contactCommunication){
            $return['ContactCommunication'][$contactCommunication->id] = $contactCommunication;
        }
        $return['ContactCommunications.getCommunicationsForContact'] = [
            $contactId => array_keys($return['ContactCommunication'])
        ];

        return $return;
    }


	public static function getEmailData($contactId,$details=null)
    {
        try
		{

			$session = Router::getRequest()->getSession();
            $login_user_id= $session->read("Auth.User.user_id");
            $login_role_id = $session->read('Auth.User.role_id');
            $login_role_type= $session->read('Auth.User.role_type');
            $login_role_type_flag = $session->read('Auth.User.role_type_flag');
			$myfile = fopen(ROOT."/logs/vueEmailListing.log", "a") or die("Unable to open file!");
            $type = null;
            $lastCommunicationsDateTime = null;
            $result =[];
            $result2 =[];
            $ContactCommunicationTable = TableRegistry::getTableLocator()->get('ContactCommunications');
			$login_agency_id = $session->read('Auth.User.agency_id');
			$Agency = TableRegistry::getTableLocator()->get('Agency');
			$agencyDetail = $Agency->agencyDetails($login_agency_id);
            $ContactCommunicationsLogs = TableRegistry::getTableLocator()->get('ContactCommunicationLogs');
			$usersTimezone='America/Phoenix';

            $limit = 50;
            $offset = 0;
			$agency_time_zone = $session->read('Auth.User.agency_session_detail.time_zone');
            if(isset($agency_time_zone) && $agency_time_zone != ''){
                $usersTimezone = $agency_time_zone;
            }else {
                $agency_state_id = $session->read('Auth.User.agency_session_detail.us_state_id');
                if (isset($agency_state_id) && $agency_state_id != '') {
                    $UsStates = TableRegistry::getTableLocator()->get('UsStates');
                    $stateDetail = $UsStates->stateDetail($agency_state_id);
                    if (isset($stateDetail) && !empty($stateDetail)) {
                        $usersTimezone = $stateDetail->time_zone;
                    }
                }
            }
            if(isset($details['type'])){
                $type = $details['type'];
            }
			$contactCommunicationsMedia = TableRegistry::getTableLocator()->get('ContactCommunicationsMedia');
            if(isset($details['lastCommunicationsDateTime'])){
                $lastCommunicationsDateTime = $details['lastCommunicationsDateTime'];
            }

            if(!PermissionsCheck::currentUserHasReadPermission(ContactsQuickTable::findById($contactId))) {
                throw new UnauthorizedException();
            }

            $response['email_details'] = $ContactCommunicationTable->find('all')
            ->select([
                'ContactCommunications.id',
                'ContactCommunications.message',
                'ContactCommunications.mail_subject',
                'ContactCommunications.in_out',
                'ContactCommunications.sent_status',
                'ContactCommunications.sent_remark',
                'ContactCommunications.parent_communication_id',              
                'ContactCommunications.created',                
                'ContactCommunications.modified',
                'ContactCommunications.status',
                'Contacts.first_name',
                'Contacts.last_name',
                'Users.first_name',
                'Users.last_name',
                'Users.headshot',
                'communication_logs_user.first_name', // User table alias
                'communication_logs_user.last_name', // User table alias
                'communication_logs_user.headshot', // User table alias
                'contact_communication_logs.user_id',
                'contact_communication_logs.email_type',
                'contact_communication_logs.contact_id',
                'contact_communication_logs.created'
            ])
            ->contain(['Users','Contacts'])
            ->contain(['ContactCommunicationsMedia'])
            ->leftJoin(["contact_communication_logs"],['contact_communication_logs.contact_communication_id = ContactCommunications.id'])
            ->leftJoin(["communication_logs_user" => "users"],['contact_communication_logs.user_id = communication_logs_user.id'])
			->where(['ContactCommunications.contact_id' => $contactId,'ContactCommunications.communication_type'=>1,])
                ->limit($limit)
                ->offset($offset)
                ->order(['ContactCommunications.created' => 'DESC'])
			->hydrate(false)->toArray();

            $prev_month_year = '';
            $old_month = '';
            $old_key = '';
            $deletedEmailkey = '';
            $activeEmail = true;
            $video_proposal_link = '';
            if(isset($agencyDetail['video_proposal_link']) && !empty($agencyDetail['video_proposal_link']))
            {
                $video_proposal_link = $agencyDetail['video_proposal_link'];
            }
            $agency_logo = '';
            if(isset($agencyDetail['headshot']) && !empty($agencyDetail['headshot']))
            {
                $agency_logo = $agencyDetail['headshot'];
            }
            $i = 0;
            $response['email_details'] = array_map(
                function($item) {
                    $item['created'] = date_format($item['created'], 'Y-m-d H:i:s');
                    return $item;
                },
            $response['email_details']);

            $time_zone = CommonFunctions::getShortCodeTimeZone($login_agency_id);
            $old_delete_key = '';
            foreach($response as $key=> $data)
			{
                $emailDeleted = 0;
                $deletedEmail = array();
                $j = 0;
                $x = 0;

				foreach (array_unique(array_column($response[$key], 'created')) as $date) 
                {
					foreach (array_filter($response[$key], function($v) use ($date) 
                    {
					    return $v['created'] == $date; 
					}) as $overview) 
                    {
                        $totalEmail = count($response[$key]);
						$overview['time_zone'] = $time_zone;
						$month = date("F",strtotime($date));
						$year = date("Y",strtotime($date));
						$month_year = $month."-".$year;		
						$agencyTime = date('h:i a',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($overview['created'])))));
						$dateMonth = date('M d, Y',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($overview['created'])))));
                        $monthis = date('m-y',strtotime($overview['created']));
						$result[$month_year][$key][$i]['agencyTime'] = $agencyTime;
						$result[$month_year][$key][$i]['dateMonth'] = $dateMonth;
						$attachment_count  = count($overview['contact_communications_media']);
						$result[$month_year][$key][$i]['id'] = $overview['id'];
                        $result[$month_year][$key][$i]['user']['first_name'] = $overview['user']['first_name'];
                        $result[$month_year][$key][$i]['user']['last_name'] = $overview['user']['last_name'];
                        $result[$month_year][$key][$i]['user_type'] = $login_role_type;
						$result[$month_year][$key][$i]['user_type_flag'] = $login_role_type_flag;
						$result[$month_year][$key][$i]['contact_communications_media'] = $overview['contact_communications_media'];
                        $result[$month_year][$key][$i]['created'] = $overview['created'];
                        if($overview['status'] == _ID_STATUS_DELETED && $overview['contact_communication_logs']['user_id'] != null)
                        {
                            $deletedEmailkey = $overview['contact_communication_logs']['user_id'].'-'.strtotime(date('Y-m-d',strtotime($overview['contact_communication_logs']['created']))).'-'.$monthis.'-'.$overview['contact_communication_logs']['email_type'].'-'.$x;
                            if($old_delete_key != '' && $old_delete_key != $deletedEmailkey && !empty($deletedEmail))
                            {
                                $result[$old_month][$old_key][$i-1]['deleted_email'] = $deletedEmail;
                                $result[$old_month][$old_key][$i-1]['deleted_email_key'] = $deletedEmailkey;
                                $deletedEmail = [];
                            }
                            $old_month = $month_year;
                            $old_key = $key;
                            $old_delete_key = $deletedEmailkey;
                            $overview['contact_communication_logs']['created'] = date('M d, Y', strtotime($overview['contact_communication_logs']['created']));
                            if (array_key_exists($deletedEmailkey,$deletedEmail) &&  $activeEmail == false)
                            {
                                $deletedEmail[$deletedEmailkey]['contact_communication_logs'][$j] = $overview['contact_communication_logs'];
                                $deletedEmail[$deletedEmailkey]['contact_communication_logs'][$j]['communication_logs_user'] = $overview['communication_logs_user'];
                            }
                            else
                            {
                                $j = 0;
                                $activeEmail = false;
                                $deletedEmail[$deletedEmailkey]['contact_communication_logs'][$j] = $overview['contact_communication_logs'];
                                $deletedEmail[$deletedEmailkey]['contact_communication_logs'][$j]['communication_logs_user'] = $overview['communication_logs_user'];
                            }

                            $activeEmail = false;
                            if(($totalEmail-1) == $i)
                            {
                                $result[$month_year][$key][$i]['deleted_email'] = $deletedEmail;
                                $result[$month_year][$key][$i]['deleted_email_key'] = $deletedEmailkey;
                            }
                            else
                            {
                                 $result[$month_year][$key][$i]['deleted_email'] = array();
                                 $result[$month_year][$key][$i]['deleted_email_key'] = '';
                            }
                            $j++;
                        }
                        else
                        {
                            $result[$month_year][$key][$i]['deleted_email'] = array();
                            $result[$month_year][$key][$i]['deleted_email_key'] = '';
                            if(($totalEmail-1) == $i)
                            {
                                $result[$month_year][$key][$i]['deleted_email'] = $deletedEmail;
                                $result[$month_year][$key][$i]['deleted_email_key'] = $deletedEmailkey;
                            }
                            if(isset($deletedEmail) && !empty($deletedEmail) && $i > 0)
                            {
                                $result[$prev_month_year][$key][$i-1]['deleted_email'] = $deletedEmail;
                                $result[$prev_month_year][$key][$i-1]['deleted_email_key'] = $deletedEmailkey;
                            }
                            $activeEmail = true;
                            $x++;
                            $deletedEmail = array();
                            $j = 0;
                        }
                        $prev_month_year = $month_year;
                        $result[$month_year][$key][$i]['status'] = $overview['status'];
						$overview['message'] = TagsRemove::formatTags(htmlspecialchars_decode($overview['message']));
                        if (stripos($overview['message'], '{agency.proposalLink}') !== false)
                        {
                            if(isset($video_proposal_link)  && !empty($video_proposal_link) ){
                                $overview['message'] = str_ireplace('{agency.proposalLink}', $video_proposal_link, $overview['message']);
                            }else{
                                $overview['message'] = str_ireplace('{agency.proposalLink}', '', $overview['message']);
                            }

                        }
                        if (stripos($overview['message'], '{agency.logo}') !== false)
                        {
                            if(isset($agency_logo)  && !empty($agency_logo) )
                            {
                                $overview['message'] = str_ireplace('{agency.logo}', '<img src="'.$agency_logo.'" style="width:50px;height:50px;">', $overview['message']);
                            }else{
                                $overview['message'] = str_ireplace('{agency.logo}', '', $overview['message']);
                            }

                        }

                        if (stripos($overview['message'], '{user.logo}') !== false)
                        {
                            $user_logo = "";
                            if(isset($overview['user']['headshot']) && !empty($overview['user']['headshot']))
                            {
                                $user_logo = $overview['user']['headshot'];
                            }
                            if(isset($user_logo)  && !empty($user_logo) ){
                                $overview['message'] = str_ireplace('{user.logo}', '<img src="'.$user_logo.'" style="width:50px;height:50px;">', $overview['message']);
                            }else{
                                $overview['message'] = str_ireplace('{user.logo}', '', $overview['message']);
                            }
                        }

						$result[$month_year][$key][$i]['message'] = $overview['message'];
						$result[$month_year][$key][$i]['mail_subject'] = $overview['mail_subject'];
						$result[$month_year][$key][$i]['time_zone'] = $overview['time_zone'];
						$result[$month_year][$key][$i]['communication_id'] = '';
						$result[$month_year][$key][$i]['created_datetime'] = strtotime($overview['created']);
						$result[$month_year][$key][$i]['in_out'] = $overview['in_out'];
						$result[$month_year][$key][$i]['sent_status'] = $overview['sent_status'];
						$result[$month_year][$key][$i]['sent_remark'] = $overview['sent_remark'];
                        $result[$month_year][$key][$i]['contact']['first_name'] = $overview['contact']['first_name'];
                        $result[$month_year][$key][$i]['contact']['last_name'] = $overview['contact']['last_name'];
						$result[$month_year][$key][$i]['attachment_count'] = $attachment_count;
                        if(isset($overview['parent_communication_id']) && $overview['parent_communication_id'] != '')
                        {
                            $result[$month_year][$key][$i]['parent_communication_id'] = $overview['parent_communication_id'];
                        }
                        else
                        {
                            $result[$month_year][$key][$i]['parent_communication_id'] = '';
                        }
						$i++;
					}
				}
			}
			
			$newArray = $result;
			uksort($newArray, function($a1, $a2) {
			$time1 = strtotime($a1);
			$time2 = strtotime($a2);

			return $time2 - $time1;
			});
			foreach($newArray as $key=>$value)
			{
				$sArray = $value['email_details'];
				array_multisort(array_column($sArray, 'created_datetime'), SORT_DESC, $sArray);
				$newArray[$key]['email_details'] = $sArray;
			}
			
            $return['ContactCommunications.getEmailData'] = [
                $contactId => $newArray
            ];

            return $return;
        }catch (\Exception $e) {
			
            $txt=date('Y-m-d H:i:s').' :: Email Lising Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
    }



    public static function getSmsData($contactId,$details=null)
    {
        try
		{
			$myfile = fopen(ROOT."/logs/vueSmsListing.log", "a") or die("Unable to open file!");
            $type = null;
            $session = Router::getRequest()->getSession();
            $lastCommunicationsDateTime = null;

            $return = [
                'ContactCommunication' => []
            ];
            $login_agency_id = $session->read('Auth.User.agency_id');
			$Agency = TableRegistry::getTableLocator()->get('Agency');		
			$agencyDetail = $Agency->agencyDetails($login_agency_id);
			$usersTimezone='America/Phoenix';
			$UsStates = TableRegistry::getTableLocator()->get('UsStates');
			$stateDetail = $UsStates->stateDetail($agencyDetail['us_state_id']);
            // $login_user_id= $session->read("Auth.User.user_id");
            // $Users = TableRegistry::getTableLocator()->get('users');
		    // $userDetail = $Users->get($login_user_id);
			if(isset($agencyDetail['time_zone']) && !empty($agencyDetail['time_zone']))
			{
				$usersTimezone =  $agencyDetail['time_zone'];
			}
			else if(isset($stateDetail) && !empty($stateDetail))
			{
				$usersTimezone =  $stateDetail->time_zone;
			}
            if(isset($details['type'])){
                $type = $details['type'];
            }
            if(isset($agencyDetail['video_proposal_link']) && !empty($agencyDetail['video_proposal_link']))
            {
                $video_proposal_link = $agencyDetail['video_proposal_link'];
            }
            // $agency_logo = '';
            // if(isset($agencyDetail['headshot']) && !empty($agencyDetail['headshot']))
            // {
            //     $agency_logo = $agencyDetail['headshot'];
            // }
            // $user_logo = "";
            // if(isset($userDetail['headshot']) && !empty($userDetail['headshot']))
            // {
            //     $user_logo = $userDetail['headshot'];
            // }

            

            if(isset($details['lastCommunicationsDateTime'])){
                $lastCommunicationsDateTime = $details['lastCommunicationsDateTime'];
            }

            if(!PermissionsCheck::currentUserHasReadPermission(ContactsQuickTable::findById($contactId))) {
                throw new UnauthorizedException();
            }
            $limit =50;
            $offset = 0;
            /** @var ContactCommunication[] $contactCommunications */
            $contactCommunications = ContactCommunicationsQuickTable::findAllBy(['contact_id' => $contactId,'communication_type'=>_COMMUNICATION_TYPE_SMS,'ContactCommunications.status !='=>_ID_STATUS_DELETED], ['Users','ContactCommunicationsMedia','CommunicationReply'],['ContactCommunications.created' => 'DESC'],$limit,$offset);
            
        
            $communication_date_for_created = '';
           
            $communicationData = [];
            $previousValue = null;
            foreach($contactCommunications as $key=>$contactCommunication){
                
                if(date('Y-m-d',strtotime($contactCommunication['created'])) != date('Y-m-d',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d',strtotime(  $contactCommunication['created']))))))
                {
                   
                    $communication_date_for_created = CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d',strtotime($contactCommunication['created'])));

                     $communication_date_stamp = CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($contactCommunication['created'])));

                     
                    $contactCommunication['sms_date'] =  date('l M d, Y',strtotime($communication_date_stamp));
                    if (stripos($contactCommunication['message'], '{agency.proposalLink}') !== false)
					{
						if(isset($video_proposal_link)  && !empty($video_proposal_link) ){
							$contactCommunication['message'] = str_ireplace('{agency.proposalLink}', $video_proposal_link, $contactCommunication['message']);
						}else{
							$contactCommunication['message'] = str_ireplace('{agency.proposalLink}', '', $contactCommunication['message']);
						}

					}
                    $attachmentList = $contactCommunication['contact_communications_media'];
                    // dd($attachmentList);
                    if($attachmentList){
                        foreach($attachmentList as $value){
                            if(isset($contactCommunication['contact_business_id']) && !empty($contactCommunication['contact_business_id']))
                            {
                                $file_path_basic = WWW_ROOT.'uploads/business_sms_'.$contactCommunication['contact_business_id'] .'/'. $value['url'];
                            }
                            else if(isset($contactCommunication['contact_id']) && !empty($contactCommunication['contact_id']))
                            {
                                $file_path_basic = WWW_ROOT.'uploads/contact_sms_'.$contactId .'/'. $value['url'];
                            }
                            //print_r($file_path_basic);die;
                            $file_path_default = WWW_ROOT.'uploads/'. $value['url'];
            
                            $file = "";
                            $download_link = "";
                            if(isset($value['file_aws_key']) && !empty($value['file_aws_key']))
                            {
                                $awsBucket = AWS_BUCKET_NAME;
                                if(Aws::awsFileExists($value['file_aws_key']))
                                {
                                    $file = $value['file_url'];
                                    //temporary link set for downloads
                                    $bucketdata=['bucket'=>$awsBucket,
                                    'keyname'=>$value['file_aws_key']];
                                    $download_link = (string)Aws::setPreAssignedUrl($bucketdata);
                                    $file = SITEURL.'s3/view?type=communication_media&id='.$value['id'];
                                }
                            }
                            elseif (file_exists($file_path_basic))
                            {
                                if(isset($contactCommunication['contact_business_id']) && !empty($contactCommunication['contact_business_id']))
                                {
                                    $file = SITEURL.'uploads/business_sms_'.$contactCommunication['contact_business_id'] .'/'. $value['url'];
                                }
                                else if(isset($contactCommunication['contact_id']) && !empty($contactCommunication['contact_id']))
                                {
                                    $file = SITEURL.'uploads/contact_sms_'.$contactId .'/'. $value['url'];
                                }
                            }
                            elseif (file_exists($file_path_default)) {
                               $file = SITEURL.'uploads/'. $value['url'];
                            }
                            elseif($value['media_type'] == _MEDIA_TYPE_SMS && empty($value['file_aws_key'])){
                                $file  = isset($value['url']) ? $value['url'] : '';
                            }
                            $value['url'] = $file;
                        }
                    }
					// if (stripos($contactCommunication['message'], '{agency.logo}') !== false)
					// {
					// 	if(isset($agency_logo)  && !empty($agency_logo) )
					// 	{
					// 		$contactCommunication['message'] = str_ireplace('{agency.logo}', '<img src="'.$agency_logo.'" style="width:50px;height:50px;">', $contactCommunication['message']);
					// 	}else{
					// 		$contactCommunication['message'] = str_ireplace('{agency.logo}', '', $contactCommunication['message']);
					// 	}

					// }


					// if (stripos($contactCommunication['message'], '{user.logo}') !== false)
					// {
					// 	if(isset($user_logo)  && !empty($user_logo) ){
					// 		$contactCommunication['message'] = str_ireplace('{user.logo}', '<img src="'.$user_logo.'" style="width:50px;height:50px;">', $contactCommunication['message']);
					// 	}else{
					// 		$contactCommunication['message'] = str_ireplace('{user.logo}', '', $contactCommunication['message']);
					// 	}
					// }
                        
                        
             
                }
              
                $smsTime = date('h:i a',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone,  date('Y-m-d H:i:s',strtotime($contactCommunication['created'])))));
                $contactCommunication['created'] = $smsTime;

                foreach($contactCommunication['communication_reply'] as $reply)
                {
                    $replyTime = date('h:i a',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($reply['created'])))));
                    $reply['created'] = $replyTime;
                }
                $return['ContactCommunication'][$contactCommunication['id']] = $contactCommunication;
              
                
            }
            
            //echo "<pre>";print_r($return);die("dfd");
            $return['ContactCommunications.getSmsData'] = [
                $contactId => $return['ContactCommunication']
            ];

            return $return;
        }catch (\Exception $e) {
			
            $txt=date('Y-m-d H:i:s').' :: Sms Lising Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
    }

    //get email attachment

    public static function getEmailAttachments($communication_id)
    {
        try
		{
			$myfile = fopen(ROOT."/logs/vueEmailListing.log", "a") or die("Unable to open file!");
            $awsBucket = AWS_BUCKET_NAME;
            $contactCommunications = TableRegistry::getTableLocator()->get('ContactCommunications');
            $contactCommunicationsMedia = TableRegistry::getTableLocator()->get('ContactCommunicationsMedia');

            if(isset($communication_id) && !empty($communication_id))
            {
                
                
                $contact_communication_detail = $contactCommunications->getCommunicationDetail($communication_id);
                $listAttachments = $contactCommunicationsMedia->getListAttachments($communication_id);
                $attachment_list = '';
                $count=1;
                foreach ($listAttachments  as $key => $value)
                { 
                    if(isset($contact_communication_detail['contact_business_id']) && !empty($contact_communication_detail['contact_business_id']))
                    {
                        $file_path_basic = WWW_ROOT.'uploads/business_'.$contact_communication_detail['contact_business_id'] .'/'. $value['url'];
                    }
                    else if(isset($contact_communication_detail['contact_id']) && !empty($contact_communication_detail['contact_id']))
                    {
                        $file_path_basic = WWW_ROOT.'uploads/contact_'.$contact_id .'/'. $value['url'];
                    }
                    //print_r($file_path_basic);die;
                    $file_path_default = WWW_ROOT.'uploads/'. $value['url'];

                    $file = "";
                    $download_link = "";
                    if(isset($value['file_aws_key']) && !empty($value['file_aws_key']))
                    {
                        
                        if(Aws::awsFileExists($value['file_aws_key']))
                        {
                            $file = $value['file_url'];
                            //temporary link set for downloads
                            $bucketdata=['bucket'=>$awsBucket,
                            'keyname'=>$value['file_aws_key']];
                            $download_link = (string)Aws::setPreAssignedUrl($bucketdata);
                        }	
                        
                    }				
                    else if (file_exists($file_path_basic))
                    {
                        if(isset($contact_communication_detail['contact_business_id']) && !empty($contact_communication_detail['contact_business_id']))
                        {
                            $file = SITEURL.'uploads/business_'.$contact_communication_detail['contact_business_id'] .'/'. $value['url'];
                        }
                        else if(isset($contact_communication_detail['contact_id']) && !empty($contact_communication_detail['contact_id']))
                        {
                            $file = SITEURL.'uploads/contact_'.$contact_communication_detail['contact_id'] .'/'. $value['url'];
                        }
                    }
                    else if(file_exists($file_path_default)) {
                    $file = SITEURL.'uploads/'. $value['url'];
                    }
                    // manual email attachment starts 
                    else if(isset($contact_communication_detail['contact_business_id']) && !empty($contact_communication_detail['contact_business_id']))
                    {
                        $file = SITEURL.'uploads/business_email_'.$contact_communication_detail['contact_business_id'] .'/'. $value['url'];
                    }
                else if(isset($contact_communication_detail['contact_id']) && !empty($contact_communication_detail['contact_id']))
                    {
                        $file = SITEURL.'uploads/contact_email_'.$contact_communication_detail['contact_id'] .'/'. $value['url'];
                    }
                // manual email attachment ends
                    if(empty($download_link)){
                        $download_link = $file;
                    }
                    $attachment_list .= '
                            <tr id="contact_attach_'.$value['id'].'">
                            <td>'.$count.'</td>
                            <td><a style="color:#e16123" href="'.$file.'" target="_blank">'.$value['name'].'</a></td>
                            <td style="padding: 12px 0px 0px;">
                            <span class="attach-download-icon" style="cursor: pointer;">
                                <a href="'.$download_link.'" download> 
                                    <i class="fa fa-download text-info" aria-hidden="true"></i>
                                </a>
                            </span>
                            </td>
                        </tr>';
                    $count++;
                        
                }
            
                $return['ContactCommunications.getEmailAttachments'] = [
                    $communication_id => json_encode(array('status' => _ID_SUCCESS,'attachment_list'=>$attachment_list))
                ];
                return $return;
            }
        }catch (\Exception $e) {
			
            $txt=date('Y-m-d H:i:s').' :: Email Attchment Lising Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
    }

    //sms attachment list

    public static function getSmsAttachments($communication_id)
    {
        try
		{
			$myfile = fopen(ROOT."/logs/vueSmsListing.log", "a") or die("Unable to open file!");
            $awsBucket = AWS_BUCKET_NAME;
            $contactCommunications = TableRegistry::getTableLocator()->get('ContactCommunications');
            $contactCommunicationsMedia = TableRegistry::getTableLocator()->get('ContactCommunicationsMedia');

            if(isset($communication_id) && !empty($communication_id))
            {
                
            
                $contact_communication_detail = $contactCommunications->getCommunicationDetail($communication_id);
                $listAttachments = $contactCommunicationsMedia->getListAttachmentsSms($communication_id);
                $attachment_list = '';
                $count=1;
                foreach ($listAttachments  as $key => $value)
                { 
                    if(isset($contact_communication_detail['contact_business_id']) && !empty($contact_communication_detail['contact_business_id']))
                    {
                        $file_path_basic = WWW_ROOT.'uploads/business_sms_'.$contact_communication_detail['contact_business_id'] .'/'. $value['url'];
                    }
                    else if(isset($contact_communication_detail['contact_id']) && !empty($contact_communication_detail['contact_id']))
                    {
                        $file_path_basic = WWW_ROOT.'uploads/contact_sms_'.$contact_communication_detail['contact_id'] .'/'. $value['url'];
                    }
                    //print_r($file_path_basic);die;
                    $file_path_default = WWW_ROOT.'uploads/'. $value['url'];

                    $file = "";
                    $download_link = "";
                    if(isset($value['file_aws_key']) && !empty($value['file_aws_key']))
                    {
                        //$file = $value['file_url'];
                        //$download_link = $this->getAwsLink($value['file_aws_key']);
                        if(Aws::awsFileExists($value['file_aws_key']))
                        {
                            $file = $value['file_url'];
                            //temporary link set for downloads
                            $bucketdata=['bucket'=>$awsBucket,
                            'keyname'=>$value['file_aws_key']];
                            $download_link = (string)Aws::setPreAssignedUrl($bucketdata);
                        }	
                        
                    }
                    elseif (file_exists($file_path_basic))
                    {
                        if(isset($contact_communication_detail['contact_business_id']) && !empty($contact_communication_detail['contact_business_id']))
                        {
                            $file = SITEURL.'uploads/business_sms_'.$contact_communication_detail['contact_business_id'] .'/'. $value['url'];
                        }
                        else if(isset($contact_communication_detail['contact_id']) && !empty($contact_communication_detail['contact_id']))
                        {
                            $file = SITEURL.'uploads/contact_sms_'.$contact_communication_detail['contact_id'] .'/'. $value['url'];
                        }
                    }
                    elseif (file_exists($file_path_default)) {
                    $file = SITEURL.'uploads/'. $value['url'];
                    }
                    if(empty($download_link)){
                        $download_link = $file;
                    }
                    $attachment_list .= '
                            <tr id="contact_attach_'.$value['id'].'">
                            <td>'.$count.'</td>
                            <td><a style="color:#e16123" href="'.$file.'" target="_blank">'.$value['name'].'</a></td>
                            <td style="padding: 12px 0px 0px;">
                            <span class="attach-download-icon" style="cursor: pointer;">
                                <a href="'.$download_link.'" download> 
                                    <i class="fa fa-download text-info" aria-hidden="true"></i>
                                </a>
                            </span>
                            </td>
                        </tr>';
                    $count++;
                        
                }
            // echo json_encode(array('status' => _ID_SUCCESS,'attachment_list'=>$attachment_list));
                $return['ContactCommunications.getSmsAttachments'] = [
                    $communication_id => json_encode(array('status' => _ID_SUCCESS,'attachment_list'=>$attachment_list))
                ];
                return $return;
            }
        }catch (\Exception $e) {
			
            $txt=date('Y-m-d H:i:s').' :: Sms Attchment Lising Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }

    }

	public function getContactsAllEmails($contact_id)
    {
        // print_r($contact_id);die;

        try
        {
            $myfile = fopen(ROOT."/logs/vueNewSendEmail.log", "a") or die("Unable to open file!");
            if(!empty($contact_id) )
            {
                $return = [
                    'emails_list_to' => [],
                    'emails_list_from' => []
                ];
                $i = 0;
                if(!empty($contact_id))
                {
                    $session = Router::getRequest()->getSession();               
                    $login_user_id= $session->read("Auth.User.user_id");               
                    $user = TableRegistry::getTableLocator()->get('Users');
                    $user= $user->userDetails($login_user_id);
                    $user['full_name'] =  $user['first_name'].' '.$user['last_name'];
                    $contactData = TableRegistry::getTableLocator()->get('Contacts');
                    $getContactDetails = $contactData->find('all')->where(['id' => $contact_id])->hydrate(false)->toArray();                  
                    $return['emails_list_from']['id'] = $user['id'];
                    $return['emails_list_from']['signature'] = $user['signature'];
                    $return['emails_list_from']['email'] = $user['email'];
                    $return['emails_list_from']['full_name'] = $user['full_name'];
                    foreach($getContactDetails as $getContactDetail){                       
                        if(isset($getContactDetail) && !empty($getContactDetail)){                            
                            if(isset($getContactDetail['email']) && !empty($getContactDetail['email']))
                            {
                                $return['emails_list_to'][$i]['id'] = $getContactDetail['id'];
                                $return['emails_list_to'][$i]['name'] = $getContactDetail['first_name'].' '.$getContactDetail['last_name'];
                                $return['emails_list_to'][$i]['email'] = $getContactDetail['email'];
                                $i++;
                            }
                        }
                    }
                    $ContactEmailsData = TableRegistry::getTableLocator()->get('ContactEmails');
                    $getAllEmailArrs = $ContactEmailsData->find('all')->where(['contact_id'=>$contact_id, 'status' => _ID_STATUS_ACTIVE])->hydrate(false)->toArray();
                    if(isset($getAllEmailArrs) && !empty($getAllEmailArrs)){
                        foreach($getAllEmailArrs as $getAllEmailArr){
                            $getEntityDefDefault=getEntityDef($err,_EMAIL_TYPE,$getContactDetail['email_type']);
                                $return['emails_list_to'][$i]['email'] = $getAllEmailArr['email'];
                                $return['emails_list_to'][$i]['name'] = $getAllEmailArr['email'];
                                $i++;
                            
                        }
                    }
                }
                $return['ContactCommunications.getContactsAllEmails'] = [
                    $contact_id =>  $return
                ];
                return $return;
            }
        }
        catch (\Exception $e) {
            $txt=date('Y-m-d H:i:s').' :: Send Email Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
    }

    /**
     * Send Email
     *
     * @param form serialize data.
     */
    public static function sendEmail($objectData)
    {
        //echo "<pre>";print_r($objectData);die("Sfsdf");
        try
		{
			$myfile = fopen(ROOT."/logs/emailsmserror.log", "a") or die("Unable to open file!");
            $session = Router::getRequest()->getSession(); 
            $login_agency_id= $session->read("Auth.User.agency_id");
            $Agency = TableRegistry::getTableLocator()->get('Agency');
            $Users = TableRegistry::getTableLocator()->get('Users');
            $ContactBusiness = TableRegistry::getTableLocator()->get('ContactBusiness');
            $BusinessPrimaryContact = TableRegistry::getTableLocator()->get('BusinessPrimaryContact');
            $BusinessCommunication = TableRegistry::getTableLocator()->get('BusinessCommunication');
            $UsStates = TableRegistry::getTableLocator()->get('UsStates');
            $Contacts = TableRegistry::getTableLocator()->get('Contacts');
            $ContactCustomFields = TableRegistry::getTableLocator()->get('ContactCustomFields');
            $ReferralPartnerUserContacts = TableRegistry::getTableLocator()->get('ReferralPartnerUserContacts');
            $ReferredContact = TableRegistry::getTableLocator()->get('ReferredContact');
            $ContactPolicyLostDetails = TableRegistry::getTableLocator()->get('ContactPolicyLostDetails');
            $UserTokens = TableRegistry::getTableLocator()->get('UserTokens');
            $ContactCommunicationsMedia = TableRegistry::getTableLocator()->get('ContactCommunicationsMedia');
            $ContactCommunications = TableRegistry::getTableLocator()->get('ContactCommunications');

            if($objectData['mail_to'] == $objectData['contact_id_n']){
                $contactDetail = $Contacts->find('all')->where(['id' => $objectData['contact_id_n']])->hydrate(false)->first();  

                $contactEmail = ($contactDetail['email']) ? $contactDetail['email']:'';
               
            }else{
                $contactEmail = $objectData['mail_to'];
            }

            $login_user_id  =   $session->read('Auth.User.user_id');
            $id             =   $login_agency_id;
            $agencyDetails   =   $Agency->agencyDetails($id);
            $mail_from      =   $objectData['mail_from'];
            $mail_to        =   $contactEmail;//$objectData['mail_to'];
            $mail_subject   =   $objectData['mail_subject'];
            $mail_message   =   $objectData['mail_message'];
            $inputed_attachments = $objectData['inputed_attachments'];
            $mail_from_name =   $agencyDetails['company'];
            if(!empty($mail_from)){
                $userDetailArr = $Users->userDetailByEmail($mail_from,$id);
                if(!empty($userDetailArr['id'])){
                    if(!empty($userDetailArr['first_name']) || !empty($userDetailArr['last_name'])){
                        $mail_from_name=$userDetailArr['first_name']." ".$userDetailArr['last_name'];;
                    }
                }
            }

            $emailCode      =   _DEFAULT;
            $toEmail        =   $mail_to;
            $fromEmail      =   $mail_from;
            $fromName       =   ucwords(trim($mail_from_name));
            $SITE_URL       =   Router::url('/', true);

            $inputed_attachments_email = $objectData['inputed_attachments_email'];// added by priya
            $business_id_n = "";
            if(isset($objectData['business_id_n']) && !empty($objectData['business_id_n'])){
                $business_id_n = $objectData['business_id_n'];
            }
            $send_to_email_number = "";
            if(isset($mail_to) && !empty($mail_to)){
                $send_to_email_number = $mail_to;
            }

            //commercial merge fields
            $business_detail="";
            if(isset($business_id_n) && !empty($business_id_n))
            {
                $business_detail = $ContactBusiness->getContactBusiness($business_id_n);
            }
            $business_primary_contact = "";
            $business_name = "";
            $business_dba = "";
            if(isset($business_detail) && !empty($business_detail))
            {
                $business_primary_contact = $BusinessPrimaryContact->getActiveBusinessPrimaryContact($business_detail['id']);
                $business_name = $business_detail['name'];
                $business_dba = $business_detail['dba'];
            }
            $business_primary_contact_first_name="";
            $business_primary_contact_last_name="";
            $business_primary_contact_preferred_name="";
            $business_primary_contact_primary_email = "";
            if(isset($business_primary_contact) && !empty($business_primary_contact))
            {
                $business_primary_contact_primary_email_detail = $BusinessCommunication->getActivePrimaryContactPrimaryEmail($business_primary_contact['id']);
                if(isset($business_primary_contact_primary_email_detail['email_phone']) && !empty($business_primary_contact_primary_email_detail['email_phone']))
                {
                    $business_primary_contact_primary_email = $business_primary_contact_primary_email_detail['email_phone'];
                }
                $business_primary_contact_first_name = $business_primary_contact['first_name'];
                $business_primary_contact_last_name = $business_primary_contact['last_name'];
                //preferred name given preferenece to first name
                $business_primary_contact_preferred_name = $business_primary_contact['preferred_name'];
                if(!empty($business_primary_contact_preferred_name)){
                    $business_primary_contact_first_name = $business_primary_contact_preferred_name;
                }
            }
            $business_primary_contact_primary_phone = "";
            if(isset($business_primary_contact) && !empty($business_primary_contact))
            {
                $business_primary_contact_primary_phone_detail = $BusinessCommunication->getActivePrimaryContactPrimaryPhone($business_primary_contact['id']);
                if(isset($business_primary_contact_primary_phone_detail['email_phone']) && !empty($business_primary_contact_primary_phone_detail['email_phone']))
                {
                    $business_primary_contact_primary_phone = $business_primary_contact_primary_phone_detail['email_phone'];
                }
            }
            //


            if(isset($agencyDetails) && !empty($agencyDetails))
            {
                $agencyName = !empty($agencyDetails['company'])?$agencyDetails['company']:"";
                $agencyReviewLink = !empty($agencyDetails['google_my_business_link'])?$agencyDetails['google_my_business_link']:"";
                $agencyStreetAddress = !empty($agencyDetails['address'])?$agencyDetails['address']:"";
                $agencyCity = !empty($agencyDetails['city'])?$agencyDetails['city']:"";
                $agencyStateId = !empty($agencyDetails['us_state_id'])?$agencyDetails['us_state_id']:"";
                $agencyState = "";
                if(!empty($agencyStateId))
                {
                    $agencyStateDetail = $UsStates->get($agencyStateId);
                    $agencyState = $agencyStateDetail->name;
                }
                $agencyEmail = !empty($agencyDetails['email'])?$agencyDetails['email']:"";
                $agencyPhone = !empty($agencyDetails['phone'])?$agencyDetails['phone']:"";
                $agencyLicense = !empty($agencyDetails['license'])?$agencyDetails['license']:"";
                $agencyProposalLink = "";
                if(isset($agencyDetails['allow_video_proposal_version']) && $agencyDetails['allow_video_proposal_version']==_QUOTE_SENT_VIDEO_PROPOSAL && !empty($agencyDetails['video_proposal_link'])){
                    $agencyProposalLink = $agencyDetails['video_proposal_link'];
                // $agencyProposalLink = '<a href='.$link.' target="_blank"><img src="'.$SITE_URL.'img/video_icon2.png" style="width:50px;height:50px;"></a>';
                }
            }

            $contact_id_n   =   $objectData['contact_id_n'];
            $contactDetails="";
            if(isset($contact_id_n) && !empty($contact_id_n))
            {
                $contactDetails = $Contacts->contactDetailsAdditionalInfo($contact_id_n);
            }
            $contactName="";
            $contactEmail="";
            $contactPhone="";
            $contactFirstName="";
            $contactMiddleName="";
            $contactLastName="";
            $contactBirthdate="";
            $contactAddress1="";
            $contactAddress2="";
            $contactCity="";
            $contactState="";
            $contactZip="";
            $maritalStatus="";
            $spouse_first_name = "";
            $spouse_last_name = "";
            $driver_license_number = "";
            if(isset($contactDetails) && !empty($contactDetails))
            {
                $contactName =$contactDetails['first_name'].' '.$contactDetails['middle_name'].' '.$contactDetails['last_name'];
                $contactEmail = !empty($contactDetails['email'])?$contactDetails['email']:"";
                $contactPhone = !empty($contactDetails['phone'])?$contactDetails['phone']:"";
                $contactFirstName = !empty($contactDetails['first_name'])?$contactDetails['first_name']:"";
                // $contactFirstName = (!empty($contactDetails['preferred_name'])) ? $contactDetails['preferred_name'] : ((!empty($contactDetails['first_name'])) ? $contactDetails['first_name'] : '');
                $contactMiddleName = !empty($contactDetails['middle_name'])?$contactDetails['middle_name']:"";
                $contactLastName = !empty($contactDetails['last_name'])?$contactDetails['last_name']:"";
                $contactPreferredName = !empty($contactDetails['preferred_name'])?$contactDetails['preferred_name']:"";
                $contactBirthdate = !empty($contactDetails['birth_date'])?date('M d, Y',strtotime($contactDetails['birth_date'])):"";
                $contactAddress1=!empty($contactDetails['address'])?$contactDetails['address']:"";
                $contactAddress2=!empty($contactDetails['address_line_2'])?$contactDetails['address_line_2']:"";
                $contactCity=!empty($contactDetails['city'])?$contactDetails['city']:"";
                $contactState = "";
                if(isset($contactDetails['state_id']) && !empty($contactDetails['state_id'])){
                    $contactStateNameById = $UsStates->stateNameById($contactDetails['state_id']);
                    if(isset($contactStateNameById['name']) && !empty($contactStateNameById['name'])){
                        $contactState = $contactStateNameById['name'];
                    }
                }
                $contactZip=!empty($contactDetails['zip'])?$contactDetails['zip']:"";
                $maritalStatus = "";
                if(isset($contactDetails['marital_status']) && !empty($contactDetails['marital_status'])){
                    $getEntityDef=getEntityDef($err,_MARITAL_STATUS,$contactDetails['marital_status']);
                    $maritalStatus = $getEntityDef;
                }
                if(isset($contactDetails['contact_details']) && !empty($contactDetails['contact_details']))
                {
                    if(isset($contactDetails['contact_details'][0]['spouse_first_name']) && !empty($contactDetails['contact_details'][0]['spouse_first_name'])){
                        $spouse_first_name = $contactDetails['contact_details'][0]['spouse_first_name'];
                    }
                    if(isset($contactDetails['contact_details'][0]['spouse_last_name']) && !empty($contactDetails['contact_details'][0]['spouse_last_name'])){
                        $spouse_last_name = $contactDetails['contact_details'][0]['spouse_last_name'];
                    }
                    if(isset($contactDetails['contact_details'][0]['driver_license_number']) && !empty($contactDetails['contact_details'][0]['driver_license_number']))
                    {
                        $driver_license_number = $contactDetails['contact_details'][0]['driver_license_number'];
                        if(isset($contactDetails['contact_details'][0]['license_state_id']) && !empty($contactDetails['contact_details'][0]['license_state_id'])){
                            if($contactDetails['contact_details'][0]['license_state_id'] == _LICENSE_STATE_OTHER && $contactDetails['contact_details'][0]['other_state_name'] != "")
                            {
                                $driver_license_number  = $driver_license_number.' | '.$contactDetails['contact_details'][0]['other_state_name'];
                            }
                            elseif ($contactDetails['contact_details'][0]['license_state_id'] == _LICENSE_STATE_INTL)
                            {
                            $driver_license_number  = $driver_license_number.' | INTL';
                            }
                            elseif ($contactDetails['contact_details'][0]['license_state_id'] == _LICENSE_STATE_NONE)
                            {
                                $driver_license_number  = $driver_license_number;
                            }
                            else
                            {
                                $contactlicenseNameById = $UsStates->stateNameById($contactDetails['contact_details'][0]['license_state_id']);
                                if(isset($contactlicenseNameById['name']) && !empty($contactlicenseNameById['name'])){
                                    $driver_license_number  = $driver_license_number.' | '.$contactlicenseNameById['name'];
                                }
                            }
                        }
                    }
                }
            }

            $userDetails=$Users->userDetails($login_user_id);
            if(isset($userDetails) && !empty($userDetails))
            {
                $agentName = $userDetails['first_name'].' '.$userDetails['last_name'];
                $agentEmail = !empty($userDetails['email'])?$userDetails['email']:"";
                $agentPhone = !empty($userDetails['phone'])?$userDetails['phone']:"";
                $agentFirstName = !empty($userDetails['first_name'])?$userDetails['first_name']:"";
                $agentLastName = !empty($userDetails['last_name'])?$userDetails['last_name']:"";
                $agent_calendar_link = !empty($userDetails['calendar_link'])?$userDetails['calendar_link']:"";
            }

            $contactCustomFields="";
            if(isset($contact_id_n) && !empty($contact_id_n))
            {
                $contactCustomFields = $ContactCustomFields->getAllCustomFieldsByContact($contact_id_n);
            }
            $custom_fields_array = [];
            if(isset($contactCustomFields) && !empty($contactCustomFields))
            {
                foreach ($contactCustomFields as $key => $value)
                {

                    if(isset($value['field_value']) && !empty($value['field_value']) && $value['field_value'] != '--'){
                        $field_value = $value['field_value'];
                    }else{
                        $field_value = "";
                    }
                    $custom_fields_array['{custom'.'.'.$value['field_label'].'}'] = $field_value;

                }
            }


            if (stripos($mail_message, 'http://{agent.calendarLink}') !== false)
            {
                $mail_message = str_ireplace('http://{agent.calendarLink}', '{agent.calendarLink}', $mail_message);
            }
            if (stripos($mail_message, 'http://{agency.proposalLink}') !== false)
            {
                $mail_message = str_ireplace('http://{agency.proposalLink}', '{agency.proposalLink}', $mail_message);
            }
            if (stripos($mail_message, 'http://{agency.reviewlink}') !== false)
            {
                $mail_message = str_ireplace('http://{agency.reviewlink}', '{agency.reviewlink}', $mail_message);
            }
            //client referral link
            $client_referral_link="";
            if(isset($agencyDetails) && !empty($agencyDetails) && isset($userDetails) && !empty($userDetails) && isset($contactDetails) && !empty($contactDetails))
            {
                $client_referral_link = _SITE_PROTOCOLE.$agencyDetails['sub_domain'].'.'._BETTER_REFERRAL_DOMAIN.'/clientreferral/'.base64_encode($userDetails['id']).'/'.base64_encode($contactDetails['id']);
            }

            //referral partner merge fields
            //contact referral partner detail
            $referral_partner_user_email = "";
            $contact_referral_partner_user_detail="";
            if(isset($contact_id_n) && !empty($contact_id_n))
            {
                $contact_referral_partner_user_detail = $ReferralPartnerUserContacts->getContactReferralPartnerUserDetailWithPrimaryEmail($contact_id_n);
            }
            if(isset($contact_referral_partner_user_detail['referral_partner_user']['referral_partner_user_email_phone'][0]['email_phone']) && !empty($contact_referral_partner_user_detail['referral_partner_user']['referral_partner_user_email_phone'][0]['email_phone']))
            {
                $referral_partner_user_email = $contact_referral_partner_user_detail['referral_partner_user']['referral_partner_user_email_phone'][0]['email_phone'];
            }
            $referral_partner_user_id="";
            $referral_partner_user_do_not_contact=0;
            $referral_partner_user_first_name="";
            $referral_partner_user_last_name="";
            $referral_partner_company="";
            if(isset($contact_referral_partner_user_detail['referral_partner_user']) && !empty($contact_referral_partner_user_detail['referral_partner_user']))
            {
                $referral_partner_user_id = $contact_referral_partner_user_detail['referral_partner_user']['id'];
                $referral_partner_user_do_not_contact = $contact_referral_partner_user_detail['referral_partner_user']['do_not_contact'];
                if(isset($contact_referral_partner_user_detail['referral_partner_user']['first_name']) && !empty($contact_referral_partner_user_detail['referral_partner_user']['first_name']))
                {
                    $referral_partner_user_first_name = $contact_referral_partner_user_detail['referral_partner_user']['first_name'];
                }
                if(isset($contact_referral_partner_user_detail['referral_partner_user']['last_name']) && !empty($contact_referral_partner_user_detail['referral_partner_user']['last_name']))
                {
                    $referral_partner_user_last_name = $contact_referral_partner_user_detail['referral_partner_user']['last_name'];
                }
                if(isset($contact_referral_partner_user_detail['referral_partner']['company']) && !empty($contact_referral_partner_user_detail['referral_partner']['company']))
                {
                    $referral_partner_company = $contact_referral_partner_user_detail['referral_partner']['company'];
                }
            }

            //contact referral partner phone detail
            $referral_partner_user_phone = "";
            $contact_referral_partner_user_detail_phone="";
            if(isset($contact_id_n) && !empty($contact_id_n))
            {
                $contact_referral_partner_user_detail_phone = $ReferralPartnerUserContacts->getContactReferralPartnerUserDetailWithPrimaryPhone($contact_id_n);
            }
            if(isset($contact_referral_partner_user_detail_phone['referral_partner_user']['referral_partner_user_email_phone'][0]['email_phone']) && !empty($contact_referral_partner_user_detail_phone['referral_partner_user']['referral_partner_user_email_phone'][0]['email_phone']))
            {
                $referral_partner_user_phone = $contact_referral_partner_user_detail_phone['referral_partner_user']['referral_partner_user_email_phone'][0]['email_phone'];
            }

            //client referral merge fields
            $client_referral_first_name = "";
            $client_referral_last_name = "";
            $client_referral_email = "";
            $client_referral_phone = "";
            $client_referrer_details="";
            if(isset($contact_id_n) && !empty($contact_id_n))
            {
                $client_referrer_details = $ReferredContact->getReferrerContactByReferralContactId($contact_id_n);
            }
            if(isset($client_referrer_details) && !empty($client_referrer_details) && isset($client_referrer_details['contact']) && !empty($client_referrer_details['contact']))
            {
                if(isset($client_referrer_details['contact']['first_name']) && !empty($client_referrer_details['contact']['first_name']))
                {
                    $client_referral_first_name = $client_referrer_details['contact']['first_name'];
                }
                if(isset($client_referrer_details['contact']['last_name']) && !empty($client_referrer_details['contact']['last_name']))
                {
                    $client_referral_last_name = $client_referrer_details['contact']['last_name'];
                }
                if(isset($client_referrer_details['contact']['email']) && !empty($client_referrer_details['contact']['email']))
                {
                    $client_referral_email = $client_referrer_details['contact']['email'];
                }
                if(isset($client_referrer_details['contact']['phone']) && !empty($client_referrer_details['contact']['phone']))
                {
                    $client_referral_phone = $client_referrer_details['contact']['phone'];
                }
            }
            else if(isset($client_referrer_details) && !empty($client_referrer_details))
            {
                if(isset($client_referrer_details['referrer_first_name']) && !empty($client_referrer_details['referrer_first_name']))
                {
                    $client_referral_first_name = $client_referrer_details['referrer_first_name'];
                }
                if(isset($client_referrer_details['referrer_last_name']) && !empty($client_referrer_details['referrer_last_name']))
                {
                    $client_referral_last_name = $client_referrer_details['referrer_last_name'];
                }
                if(isset($client_referrer_details['referrer_email']) && !empty($client_referrer_details['referrer_email']))
                {
                    $client_referral_email = $client_referrer_details['referrer_email'];
                }
                if(isset($client_referrer_details['referrer_phone']) && !empty($client_referrer_details['referrer_phone']))
                {
                    $client_referral_phone = $client_referrer_details['referrer_phone'];
                }
            }
            // added lost carrier for last policy cancelled
            $lost_carrier = '';
            $lost_carrier_data = $ContactPolicyLostDetails->getContactLostPolicyCarrier($contact_id_n);
            if(isset($lost_carrier_data) && !empty($lost_carrier_data)){
                $lost_carrier = $lost_carrier_data['carrier_name'];
            }
            $mergeFieldArray = array(
                '{agency.name}' => ucwords($agencyName),
                '{agency.streetAddress}' => $agencyStreetAddress,
                '{agency.reviewlink}' => $agencyReviewLink,
                '{agency.city}' => $agencyCity,
                '{agency.state}' => $agencyState,
                '{agency.email}' => $agencyEmail,
                '{agency.phone}' => $agencyPhone,
                '{agency.license}' => $agencyLicense,
                '{agent.name}' => ucwords($agentName),
                '{agent.email}' => $agentEmail,
                '{agent.phone}' => $agentPhone,
                '{contact.name}' => $contactName,
                '{contact.email}' => $contactEmail,
                '{contact.phone}' => $contactPhone,
                '{contact.firstName}' => $contactFirstName,
                '{contact.middleName}' => $contactMiddleName,
                '{contact.lastName}' => $contactLastName,
                '{contact.preferredName}' => $contactPreferredName,
                '{agent.firstName}' => $agentFirstName,
                '{agent.lastName}' => $agentLastName,
                '{contact.firstName}' => $contactFirstName,
                '{agent.calendarLink}'=>$agent_calendar_link,
                '{contact.birthdate}'=>$contactBirthdate,
                '{agency.proposalLink}'=>$agencyProposalLink,
                '{contact.address1}'=>$contactAddress1,
                '{contact.address2}'=>$contactAddress2,
                '{contact.city}'=>$contactCity,
                '{contact.state}'=>$contactState,
                '{contact.zip}'=>$contactZip,
                '{contact.maritalStatus}'=>$maritalStatus,
                '{contact.spouseFirstName}'=>$spouse_first_name,
                '{contact.spouseLastName}'=>$spouse_last_name,
                '{contact.driversLicenseNumber}'=>$driver_license_number,
                '{clientReferralLink}'=>$client_referral_link,
                '{referralpartner.email}'=>$referral_partner_user_email,
                '{referralpartner.phone}'=>$referral_partner_user_phone,
                '{referralpartner.firstName}'=>$referral_partner_user_first_name,
                '{referralpartner.lastName}'=>$referral_partner_user_last_name,
                '{referralpartner.company}'=>$referral_partner_company,
                '{clientreferrer.firstName}'=>$client_referral_first_name,
                '{clientreferrer.lastName}'=>$client_referral_last_name,
                '{clientreferrer.email}'=>$client_referral_email,
                '{clientreferrer.phone}'=>$client_referral_phone,
                '{commercial.contactEmail}'=>$business_primary_contact_primary_email,
                '{commercial.contactPhone}'=>$business_primary_contact_primary_phone,
                '{commercial.contactFirstName}'=>$business_primary_contact_first_name,
                '{commercial.contactLastName}'=>$business_primary_contact_last_name,
                '{commercial.businessName}'=>$business_name,
                '{commercial.dba}'=>$business_dba,
                '{contact.lostCarrier}'=>$lost_carrier,
            );

            $user_logo = "";
            if(isset($login_user_id) && !empty($login_user_id))
            {
                $user = $Users->find()->where(['id' => $login_user_id])->first();
                if(!empty($user['headshot'])){
                    $user_logo = $user['headshot'];
                }
            }

            $agency_logo = "";
            if(!empty($agencyDetails['headshot'])){
                $agency_logo = $agencyDetails['headshot'];
            }

            if (stripos($mail_message, '{agency.logo}') !== false)
            {
                if(isset($agency_logo)  && !empty($agency_logo) ){
                    $mail_message = str_ireplace('{agency.logo}', '<img src="'.$agency_logo.'" style="width:50px;height:50px;">', $mail_message);
                }else{
                    $mail_message = str_ireplace('{agency.logo}', '', $mail_message);
                }

            }


            if (stripos($mail_message, '{user.logo}') !== false)
            {
                if(isset($user_logo)  && !empty($user_logo) ){
                    $mail_message = str_ireplace('{user.logo}', '<img src="'.$user_logo.'" style="width:50px;height:50px;">', $mail_message);
                }else{
                    $mail_message = str_ireplace('{user.logo}', '', $mail_message);
                }

            }

            if (stripos($mail_message, '{agency.proposalLink}') !== false)
            {
                if(isset($agencyProposalLink)  && !empty($agencyProposalLink) ){
                    $mail_message = str_ireplace('{agency.proposalLink}', $agencyProposalLink, $mail_message);
                }else{
                    $mail_message = str_ireplace('{agency.proposalLink}', '', $mail_message);
                }

            }

            $finalContentArray = array_merge($custom_fields_array,$mergeFieldArray);
            if (is_array($finalContentArray) && !empty($finalContentArray)) {
                foreach ($finalContentArray as $key => $value) {
                    if($key != '{agency.proposalLink}'){
                        $mail_message = str_ireplace($key, $value, $mail_message);
                    }
                        $mail_subject = str_ireplace($key, $value, $mail_subject);
                }
            }

            if(isset($mail_to) && isset($mail_message)){

                //update order by recent
                $recent_date = date('Y-m-d H:i:s');
                if(isset($contact_id_n) && !empty($contact_id_n))
                {
                    $Contacts->updateAll(['order_by_recent' => $recent_date,'last_contacted_date' => $recent_date],['id' => $contact_id_n]);
                }
                if(isset($business_id_n) && !empty($business_id_n))
                {
                    $ContactBusiness->updateAll(['order_by_recent' => $recent_date],['id' => $business_id_n]);
                }

                $type = _COMMUNICATION_TYPE_EMAIL;
                $mesagereply = $mail_message;
                $mesage = $mail_message;
                $date   = date('Y-m-d H:i:s');



                $communicationData =  CommonFunctions::saveCommunicationData($id, $login_user_id, $contact_id_n, $type, $mail_subject,$mesage,'','','','',$business_id_n,$send_to_email_number);//entry in contact_communication
                $contactToken="";


                $contentArray = array(
                    '{MAIL_SUBJECT}' => $mail_subject,
                    '{MAIL_CONTENT}' => $mail_message,
                    '{SITE_URL}' => $SITE_URL,
                );

                $attachents_info_mail = [];
               
                if(isset($inputed_attachments) && !empty($inputed_attachments))
                {

                    $attachents_info_mail = json_decode($inputed_attachments,true);
                    //echo "<pre>";print_r($attachents_info_mail);die("dsfs");

                }

                // Manual Email Attachments ends
                //check for user token table nylas status
                $user_token_details = $UserTokens->getActiveUserTokenDetailByUserId($login_user_id);
                $nylas_status = false;
                if(isset($user_token_details['nylas_status']) && !empty($user_token_details['nylas_status']) && ($user_token_details['nylas_status'] == _NYLAS_STATUS_AUTHENTICATED ))
                {
                    $nylas_status = true;
                }
                $gridMail = "";
                if($nylas_status)
                {	
	                //send email by nylas code start here
	                if(!empty($communicationData))
	                {
	                    $gridMail = $ContactCommunications->updateAll(['sent_status_nylas' => _NYLAS_EMAIL_STATUS_QUEUED],['id' => $communicationData->id]);

	                }
                }
                else
                {
                   
                    //send email by send grid
                    $gridMail = CommonFunctions::sendGridMail($emailCode, $toEmail, $fromEmail, $fromName, $contentArray, '','',$attachents_info_mail,'',$login_agency_id);
                }

               // echo "<pre>";print_r($gridMail); die("sdfkdjkfkls");

                //$messageID_data =   $gridMail['Mail_message_ID'];
                //$mailId         =   explode(':',$messageID_data);
                //$sentMailId     =   $mailId[1];

                if($gridMail){
                    if(!empty($communicationData))
                    {
                        $ContactCommunications->updateAll(['sent_status' => _SENT_STATUS_DELIVERED],['id' => $communicationData->id]);//update sent status to delivered

                        if(isset($attachents_info_mail) && !empty($attachents_info_mail))
                        {
                            foreach ($attachents_info_mail as $key => $value)
                            {
                                $arr_media_details=[];
                                $arr_media_details['communication_id']=$communicationData->id;
                                $arr_media_details['url']=$value['url'];
                                $arr_media_details['name']=$value['name'];
                                if(isset($value['file_url']) && !empty($value['file_url']) && isset($value['file_aws_key']) && !empty($value['file_aws_key'])){
                                    $arr_media_details['file_url']=$value['file_url'];
                                    $arr_media_details['file_aws_key']=$value['file_aws_key'];
                                }
                                $arr_media_details['media_type']=_MEDIA_TYPE_EMAIL;
                                $contact_media_data = $ContactCommunicationsMedia->newEntity();
                                $contact_media_data = $ContactCommunicationsMedia->patchEntity($contact_media_data,$arr_media_details);
                                $ContactCommunicationsMedia->save($contact_media_data);
                            }
                        }
                    }
                    if(isset($gridMail['status']) && !empty($gridMail['status']))
                    {
                        $response =  json_encode(array('status' => $gridMail['status'],'message'=>$gridMail['meassage']));
                    }else{
                        $response = json_encode(array('status' => _ID_SUCCESS,'message'=>'Email Sent Successfully!'));	
                    }
                }
            }
            else{
                $response = json_encode(array('status' => _ID_FAILED,'message'=>''));
            }

           return $response;
        }catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: Sms Attchment Lising Error- '.$e->getMessage()."line number".$e->getLine();
            fwrite($myfile,$txt.PHP_EOL);
        }

    }

    public static function getAllEmailAttachmentLists($contactId)
    {
        // $this->loadmodel('ContactBusiness');
		// $this->loadmodel('Users');
		// $this->loadmodel('Contacts');

        $session = Router::getRequest()->getSession(); 
        $login_agency_id = $session->read("Auth.User.agency_id"); 
        $login_user_id = $session->read('Auth.User.user_id');
        $login_role_type = $session->read('Auth.User.role_type');
        $login_role_type_flag = $session->read('Auth.User.role_type_flag');

        $Contacts = TableRegistry::getTableLocator()->get('Contacts');
        $ContactAttachments = TableRegistry::getTableLocator()->get('ContactAttachments');
        $Users = TableRegistry::getTableLocator()->get('Users');
        $ContactBusiness = TableRegistry::getTableLocator()->get('ContactBusiness');
        $awsBucket = AWS_BUCKET_NAME;
        if(isset($contactId) && !empty($contactId))
        {
            $id = "";
            $contact_business_id = "";
            $contact="";
            // if(!empty($this->request->query('id')))
            // {
                $id = $contactId;
                // $contact = $Contacts->get($id,['contain'=>['ContactAttachments']]); 
                $attachmentArray = $ContactAttachments->getAttachment($id);
            // }
            // else if(!empty($this->request->query('contact_business_id')))
            // {
            //     $contact_business_id = $this->request->query('contact_business_id');
            //     $contact = $this->ContactBusiness->get($contact_business_id,['contain'=>['ContactAttachments']]);
            // }
            $contact_sms_attachment_list = '';
            if(isset($attachmentArray) && !empty($attachmentArray))
            {
                
                foreach ($attachmentArray as $key => $value)
                {
                    $ext = substr(strtolower(strrchr($value['name'], '.')), 1);
                    
                        $file_path_basic = WWW_ROOT.'uploads/contact_'.$id .'/'. $value['name'];
                    
                        $file_path_default = WWW_ROOT.'uploads/'. $value['name'];

                    $file = "";
					$download_link = "";
                    if(isset($value['file_aws_key']) && !empty($value['file_aws_key']))
                    {
                        $file = $value['file_url'];
                        $download_link =  $file;

                        // if(Aws::awsFileExists($value['file_aws_key']))
                        // {
						// 	$file = $value['file_url'];

                        //     if(Aws::awsFileExists($value['file_aws_key']))
                        //     {
                        //         $file = $value['file_url'];
                        //         //temporary link set for downloads
                        //         $bucketdata=['bucket'=>$awsBucket,
                        //         'keyname'=>$value['file_aws_key']];
                        //         $download_link = (string)Aws::setPreAssignedUrl($bucketdata);
                        //     }
						// 	//$download_link =  $this->getAwsLink($value['file_aws_key']);
                        // }
                    }
					elseif (file_exists($file_path_basic))
                    {
                       
                        $file = SITEURL.'uploads/contact_'.$id .'/'. $value['name'];
                       
                    }
                    elseif (file_exists($file_path_default)) {
                       $file = SITEURL.'uploads/'. $value['name'];
                    }
                    
					if(empty($download_link)){
						$download_link = $file;
					}
                    $fileSize = $value['file_size'];
                    if(isset($fileSize) && !empty($fileSize)){
                        $file_size_covert = CommonFunctions::convert_filesize($fileSize);    
                    }else{
                        $file_size_covert = "--";
                    }
                    $created_file_upload = date('M d, Y',strtotime($value['created'])); 
                    $attachment_user_id = $value['user_id']; 
                    if(isset($attachment_user_id) && !empty($attachment_user_id))
                    {
                        $attachment_user_id = $attachment_user_id;
                    }
                    else
                    {
                        $attachment_user_id = $login_user_id;
                    }
                    $userDetailsForAttachments = $Users->userDetails($attachment_user_id);

                    $attachement_user_name = [];
                    if(isset($userDetailsForAttachments['first_name']) || !empty($userDetailsForAttachments['first_name']))
                    {
                        $attachement_user_name .=$userDetailsForAttachments['first_name'];
                    }
                    if(isset($userDetailsForAttachments['last_name']) || !empty($userDetailsForAttachments['last_name']))
                    {
                        $attachement_user_name  .=' '.$userDetailsForAttachments['last_name'];
                    }
                    if(isset($value['display_name']) && !empty($value['display_name'])){
                        $display_name = $value['display_name'];
                    }else{
                        $display_name = $value['name'];
                    }
					$fromEmail = "'fromChatHistory'";
                    if($value['status'] == _ID_STATUS_ACTIVE)
                    {

                        $contact_sms_attachment_lists['contact_attachment_lists'][] = [
                            'id' => $value['id'],
                            'display_name' => $display_name,
                            'file_size_convert' => $file_size_covert,
                            'attachment_user_name' => ucfirst($attachement_user_name),

                        ];
                    }
                    
                }
            }
            $response =  json_encode(array('status' => _ID_SUCCESS,'contact_sms_attachment_list'=>$contact_sms_attachment_lists));
            
            $return['ContactCommunications.getAllEmailAttachmentLists'] = [
                $contactId =>  $response
            ];
            return $return;
        }
    }

    public static function appendAttachments($objectData)
    {
        //echo "<pre>";print_r($objectData);die("dfd");
        $session = Router::getRequest()->getSession(); 
        $login_agency_id = $session->read("Auth.User.agency_id"); 
        $login_user_id = $session->read('Auth.User.user_id');
        $attachment_ids = [];
		$totalFileSize = 0;
		$maxFileCount = 10;
		$maxFileSize = 16 * 1024 * 1024;
        $unUploadedAttachments = [];
        $ContactAttachments = TableRegistry::getTableLocator()->get('ContactAttachments');

        if(isset($objectData['attachment_arr']) && !empty($objectData['attachment_arr']))
        {
            $attachment_arr = explode(',',$objectData['attachment_arr']);
            $attachmentsDetails = $ContactAttachments->getMultipleAttachmentDetails($attachment_arr);
            $appended_attachments_listings = [];

            $totalFileCount = count($attachment_arr);
			if ($totalFileCount > $maxFileCount) {
				return json_encode(array('status' => _ID_FAILED,'message'=>'Maximum 10 files allowed'));
			}
            $filesSize = array_column($attachmentsDetails, 'file_size');
            $totalFileSize = array_sum($filesSize);
			if ($totalFileSize > $maxFileSize) {
				return json_encode(array('status' => _ID_FAILED,'message'=>'File size exceeded 16 MB'));
			}

             foreach ($attachmentsDetails as $key => $contact_attachment_details)
             {
                 if($contact_attachment_details['file_size'] > 0)
                 {
                     if(isset($contact_attachment_details['name']) && !empty($contact_attachment_details['name']))
                     {
                         $appended_attachments_listings['append_attachment_list'][] = [
                             'display_name' => $contact_attachment_details['display_name'],
                             'upload_id' => $contact_attachment_details['id']
                         ];

                         $attachents_info =array();
                         $file_content_type = CommonFunctions::get_mime_type($contact_attachment_details['file_url']);
                         $attachents_info['uploadId'] = $contact_attachment_details['id'];
                         $attachents_info['url'] = $contact_attachment_details['file_url'];
                         $attachents_info['name'] = $contact_attachment_details['display_name'];
                         $attachents_info['file_url'] = $contact_attachment_details['file_url'];
                         $file_aws_key = $contact_attachment_details['file_aws_key'];
                         if(isset($file_aws_key) && !empty($file_aws_key)){
                             $attachents_info['file_aws_key'] = $file_aws_key;
                         }
                         $attachents_info['file_content_type'] = $file_content_type;
                         $attachents_info_mail[] = $attachents_info;

                     }
                 }
                 else
                 {
                     $unUploadedAttachments[] = $contact_attachment_details['display_name'];
                 }

             }

        }
        $response =  json_encode(array('status' => _ID_SUCCESS,'appended_attachments_listing'=> $appended_attachments_listings, 'attachents_info_mail' => $attachents_info_mail, 'unUploadedAttachments' => $unUploadedAttachments));
        return $response;
    }

    public static function getEmailAttachmentData($objectData)
    {
        $session = Router::getRequest()->getSession();
        $login_agency_id = $session->read('Auth.User.agency_id');
       //echo "<pre>";print_r($objectData);die("Sdfsdf");
		$inputed_attachments = $objectData['inputed_attachments'];
        
		$inputed_attachments_email = $objectData['inputed_attachments_email'];
		$inputed_attachments_sms = $objectData['inputed_sms_attachments'];
        $contact_id_n = $objectData['contact_id_n'];
        $awsBucket = AWS_BUCKET_NAME;
		$ContactAttachments = TableRegistry::getTableLocator()->get('ContactAttachments');
        $ContactEmailAttachments = TableRegistry::getTableLocator()->get('ContactEmailAttachments');
        $ContactSmsAttachments = TableRegistry::getTableLocator()->get('ContactSmsAttachments');
		$attachents_info_mail = [];
            if(isset($inputed_attachments) && !empty($inputed_attachments))
            { 
                //echo "<pre>";print_r($inputed_attachments);die("dsfds");
                // $inputed_attachments_array = explode(",",$inputed_attachments);
               
                foreach ($inputed_attachments as $key => $value)
                {
                    $attachmentDetail = $ContactAttachments->getAttachmentDetail($value);
                    $file_display_name = $attachmentDetail['display_name'];
                    $file_name = $attachmentDetail['name'];
					if(isset($contact_id_n) && !empty($contact_id_n) && isset($business_id_n) && !empty($business_id_n))
                    {
                        $file_path_basic = WWW_ROOT.'uploads/business_'.$business_id_n.'/'. $file_name;
                    }
                    else if(isset($contact_id_n) && !empty($contact_id_n))
                    {
                        $file_path_basic = WWW_ROOT.'uploads/contact_'.$contact_id_n.'/'. $file_name;
                    }
                    else if(isset($business_id_n) && !empty($business_id_n))
                    {
                        $file_path_basic = WWW_ROOT.'uploads/business_'.$business_id_n.'/'. $file_name;
                    }

                    $file_path_default = WWW_ROOT.'uploads/'. $file_name;

                    $file = "";
					
                    if(isset($attachmentDetail['file_aws_key']) && !empty($attachmentDetail['file_aws_key']))
                    {
                        if(Aws::awsFileExists($attachmentDetail['file_aws_key']))
                        {
							$file = $attachmentDetail['file_url'];
                            if (!preg_match('/(\.jpg|\.png|\.bmp|\.pdf|\.xls|\.jpeg|\.doc|\.docx|\.eml|\.pst|\.ost|\.wav|\.mp3|\.mp4|\.m4a|\.txt|\.csv|\.xlsx)$/i', strtolower($file_display_name))) {
								if(isset($attachmentDetail['file_url']) && $attachmentDetail['file_url'] != '')
								{
									$fileInfo = pathinfo($attachmentDetail['file_url']);
									if(isset($fileInfo['extension']) && $fileInfo['extension'] != '')
									{
										$file_display_name = $file_display_name . '.' .strtolower($fileInfo['extension']);
									}
								}
								
							}
							if(isset($contact_id_n) && !empty($contact_id_n) && isset($business_id_n) && !empty($business_id_n))
							{
								$file_name = strtolower('business_'.$business_id_n.'_attach_'.date('Y-m-d').'_'.time().'_'.$file_display_name);
							}
							else if(isset($contact_id_n) && !empty($contact_id_n))
							{
								$file_name = strtolower('contact_'.$contact_id_n.'_attach_'.date('Y-m-d').'_'.time().'_'.$file_display_name);
							}
							else if(isset($business_id_n) && !empty($business_id_n))
							{
								$file_name = strtolower('business_'.$business_id_n.'_attach_'.date('Y-m-d').'_'.time().'_'.$file_display_name);
							}
							$file_name=str_replace(" ", "_", $file_name);
							//aws file upload starts
							$keyname = $login_agency_id.'/'.$contact_id_n;
							$bucketdata=['bucket'=>$awsBucket,
							'keyname'=>$keyname,
							'filepath'=>$file,
							'filename'=> $file_name];

							$fileMoveRes = Aws::copyFile($bucketdata);
							$file_aws_key = $keyname.'/'.$file_name;
							$file_url = $fileMoveRes;
							//aws file upload ends
                        }
                    }
					elseif (file_exists($file_path_basic))
                    {
                        if(isset($contact_id_n) && !empty($contact_id_n) && isset($business_id_n) && !empty($business_id_n))
                        {
                            $file = SITEURL.'uploads/business_'.$business_id_n.'/'. $file_name;
                        }
                        else if(isset($contact_id_n) && !empty($contact_id_n))
                        {
                            $file = SITEURL.'uploads/contact_'.$contact_id_n.'/'. $file_name;
                        }
                        else if(isset($business_id_n) && !empty($business_id_n))
                        {
                            $file = SITEURL.'uploads/business_'.$business_id_n.'/'. $file_name;
                        }
                    }
                    elseif (file_exists($file_path_default)) {
                       $file = SITEURL.'uploads/'. $file_name;
                    }
                    $attachents_info = array();
                    $file_content_type = CommonFunctions::get_mime_type($file);
                    $attachents_info['uploadId']=$value;
                    $attachents_info['url']=$file_name;
                    $attachents_info['name']=$file_display_name;
					if(isset($file_url) && !empty($file_url) && isset($file_aws_key) && !empty($file_aws_key)){
						$attachents_info['file_url']=$file_url;
						$attachents_info['file_aws_key']=$file_aws_key;
					}else{
						$attachents_info['file_url']=$file;
					}
                    $attachents_info['file_content_type']=$file_content_type;
                    $attachents_info_mail[] = $attachents_info;
					
                }
            }
			 if(isset($inputed_attachments_email) && !empty($inputed_attachments_email))
            {
                //$inputed_attachments_array = explode(",",$inputed_attachments_email);
				
                foreach ($inputed_attachments_email as $key => $value)
                {
                    $attachmentDetail = $ContactEmailAttachments->getAttachmentDetail($value);
					
                    $file_display_name = $attachmentDetail['display_name'];
                    $file_name = $attachmentDetail['name'];
					$file_aws_key = $attachmentDetail['file_aws_key'];
                    $file = "";
					if(isset($attachmentDetail['file_aws_key']) && !empty($attachmentDetail['file_aws_key']))
                    {
                        if(Aws::awsFileExists($attachmentDetail['file_aws_key']))
                        {
							$file = $attachmentDetail['file_url'];
                        }
                    }
                    $attachents_info = array();
                    $file_content_type = CommonFunctions::get_mime_type($file);
                    $attachents_info['uploadId']=$value;
                    $attachents_info['url']=$file_name;
                    $attachents_info['name']=$file_display_name;
                    $attachents_info['file_url']=$file;
					if(isset($file_aws_key) && !empty($file_aws_key)){
						$attachents_info['file_aws_key']=$file_aws_key;
					}
                    $attachents_info['file_content_type']=$file_content_type;
                    $attachents_info_mail[] = $attachents_info;
                }
				
            }
			 if(isset($inputed_attachments_sms) && !empty($inputed_attachments_sms))
            { 
                //$inputed_attachments_array = explode(",",$inputed_attachments_sms);
				
                foreach ($inputed_attachments_sms as $key => $value)
                {
                    $attachmentDetail = $ContactSmsAttachments->getAttachmentDetail($value);
					
                    $file_display_name = $attachmentDetail['display_name'];
                    $file_name = $attachmentDetail['name'];
					$file_aws_key = $attachmentDetail['file_aws_key'];
                    $file = "";
					if(isset($attachmentDetail['file_aws_key']) && !empty($attachmentDetail['file_aws_key']))
                    {
                        if(Aws::awsFileExists($attachmentDetail['file_aws_key']))
                        {
							$file = $attachmentDetail['file_url'];
                        }
                    }
                    $attachents_info = array();
                    $file_content_type = CommonFunctions::get_mime_type($file);
                    $attachents_info['uploadId']=$value;
                    $attachents_info['url']=$file_name;
                    $attachents_info['name']=$file_display_name;
                    $attachents_info['file_url']=$file;
					if(isset($file_aws_key) && !empty($file_aws_key)){
						$attachents_info['file_aws_key']=$file_aws_key;
					}
                    $attachents_info['file_content_type']=$file_content_type;
                    $attachents_info_mail[] = $attachents_info;
                }
				
            }
			$response =  json_encode(array('status'=>_ID_SUCCESS,'attachents_info_mail' => $attachents_info_mail,'message'=>''));
            return $response;
    }
	
	public function getCommunication($communication_id){
        $session = Router::getRequest()->getSession();
        $ContactCommunications = TableRegistry::getTableLocator()->get('ContactCommunications');
		$getCommunicationDetail = $ContactCommunications->getCommunicationDetail($communication_id);
        $contactId = $getCommunicationDetail->contact_id;
        $getCommunicationDetail['contact_email'] = $getCommunicationDetail['contact']['email'];
        $attachmentList = $getCommunicationDetail['contact_communications_media'];
        if($attachmentList){
            foreach($attachmentList as $value){
                if(isset($getCommunicationDetail['contact_business_id']) && !empty($getCommunicationDetail['contact_business_id']))
                {
                    $file_path_basic = WWW_ROOT.'uploads/business_sms_'.$getCommunicationDetail['contact_business_id'] .'/'. $value['url'];
                }
                else if(isset($getCommunicationDetail['contact_id']) && !empty($getCommunicationDetail['contact_id']))
                {
                    $file_path_basic = WWW_ROOT.'uploads/contact_sms_'.$contactId .'/'. $value['url'];
                }
                //print_r($file_path_basic);die;
                $file_path_default = WWW_ROOT.'uploads/'. $value['url'];

                $file = "";
                $download_link = "";
                if(isset($value['file_aws_key']) && !empty($value['file_aws_key']))
                {
                    $awsBucket = AWS_BUCKET_NAME;
                    if(Aws::awsFileExists($value['file_aws_key']))
                    {
                        $file = $value['file_url'];
                        //temporary link set for downloads
                        $bucketdata=['bucket'=>$awsBucket,
                        'keyname'=>$value['file_aws_key']];
                        $download_link = (string)Aws::setPreAssignedUrl($bucketdata);
                        $file = SITEURL.'s3/view?type=communication_media&id='.$value['id'];
                    }
                }
                elseif (file_exists($file_path_basic))
                {
                    if(isset($contactCommunication['contact_business_id']) && !empty($contactCommunication['contact_business_id']))
                    {
                        $file = SITEURL.'uploads/business_sms_'.$contactCommunication['contact_business_id'] .'/'. $value['url'];
                    }
                    else if(isset($contactCommunication['contact_id']) && !empty($contactCommunication['contact_id']))
                    {
                        $file = SITEURL.'uploads/contact_sms_'.$contactId .'/'. $value['url'];
                    }
                }
                elseif (file_exists($file_path_default)) {
                   $file = SITEURL.'uploads/'. $value['url'];
                }
                elseif($value['media_type'] == _MEDIA_TYPE_SMS && empty($value['file_aws_key'])){
                    $file  = isset($value['url']) ? $value['url'] : '';
                }
                $value['url'] = $file;
            }
        }


		$session = Router::getRequest()->getSession(); 
		$login_agency_id = $session->read('Auth.User.agency_id');
		$Agency = TableRegistry::getTableLocator()->get('Agency');		
		$agencyDetail = $Agency->agencyDetails($login_agency_id);
		$usersTimezone='America/Phoenix';
		$UsStates = TableRegistry::getTableLocator()->get('UsStates');
		$stateDetail = $UsStates->stateDetail($agencyDetail['us_state_id']);
        $login_user_id= $session->read("Auth.User.user_id");
        $Users = TableRegistry::getTableLocator()->get('Users');
        $userDetail = $Users->get($login_user_id);
		if(isset($agencyDetail['time_zone']) && !empty($agencyDetail['time_zone']))
		{
			$usersTimezone =  $agencyDetail['time_zone'];
		}
		else if(isset($stateDetail) && !empty($stateDetail))
		{
			$usersTimezone =  $stateDetail->time_zone;
		}
        $video_proposal_link = '';
        if(isset($agencyDetail['video_proposal_link']) && !empty($agencyDetail['video_proposal_link']))
        {
            $video_proposal_link = $agencyDetail['video_proposal_link'];
        }
        $agency_logo = '';
        if(isset($agencyDetail['headshot']) && !empty($agencyDetail['headshot']))
        {
            $agency_logo = $agencyDetail['headshot'];
        }
        $user_logo = "";
        if(isset($userDetail['headshot']) && !empty($userDetail['headshot']))
        {
            $user_logo = $userDetail['headshot'];
        }
        $time_zone = CommonFunctions::getShortCodeTimeZone($login_agency_id);
		$attachment_count = count($getCommunicationDetail['contact_communications_media']);
		$agencyTime = date('h:i a',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($getCommunicationDetail['created'])))));
		$dateMonth = date('M d, Y',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($getCommunicationDetail['created'])))));
		$getCommunicationDetail['agencyTime'] = $agencyTime;
		$getCommunicationDetail['dateMonth'] = $dateMonth;
		$getCommunicationDetail['time_zone'] = $time_zone;
		$getCommunicationDetail['attachment_count'] = $attachment_count;
        if (stripos($getCommunicationDetail['message'], '{agency.proposalLink}') !== false)
        {
            if(isset($video_proposal_link)  && !empty($video_proposal_link) ){
                $getCommunicationDetail['message'] = str_ireplace('{agency.proposalLink}', $video_proposal_link, $getCommunicationDetail['message']);
            }else{
                $getCommunicationDetail['message'] = str_ireplace('{agency.proposalLink}', '', $getCommunicationDetail['message']);
            }

        }
        if (stripos($getCommunicationDetail['message'], '{agency.logo}') !== false)
        {
            if(isset($agency_logo)  && !empty($agency_logo) )
            {
                $getCommunicationDetail['message'] = str_ireplace('{agency.logo}', '<img src="'.$agency_logo.'" style="width:50px;height:50px;">', $getCommunicationDetail['message']);
            }else{
                $getCommunicationDetail['message'] = str_ireplace('{agency.logo}', '', $getCommunicationDetail['message']);
            }

        }


        if (stripos($getCommunicationDetail['message'], '{user.logo}') !== false)
        {
            if(isset($user_logo)  && !empty($user_logo) ){
                $getCommunicationDetail['message'] = str_ireplace('{user.logo}', '<img src="'.$user_logo.'" style="width:50px;height:50px;">', $getCommunicationDetail['message']);
            }else{
                $getCommunicationDetail['message'] = str_ireplace('{user.logo}', '', $getCommunicationDetail['message']);
            }
        }
		$return['ContactCommunications.getCommunication'] = [
			$communication_id => $getCommunicationDetail
		];
		
		return $return;
	}
	public function getCommunicationReply($communication_id){
		$CommunicationReply = TableRegistry::getTableLocator()->get('CommunicationReply');
		$ContactCommunicationsMedia = TableRegistry::getTableLocator()->get('ContactCommunicationsMedia');
		$getCommunicationDetail = $CommunicationReply->getCommunicationReplyDetail($communication_id);
		$session = Router::getRequest()->getSession(); 
		$login_agency_id = $session->read('Auth.User.agency_id');
		$Agency = TableRegistry::getTableLocator()->get('Agency');		
		$agencyDetail = $Agency->agencyDetails($login_agency_id);
		$usersTimezone='America/Phoenix';
		$UsStates = TableRegistry::getTableLocator()->get('UsStates');
		$stateDetail = $UsStates->stateDetail($agencyDetail['us_state_id']);
		if(isset($agencyDetail['time_zone']) && !empty($agencyDetail['time_zone']))
		{
			$usersTimezone =  $agencyDetail['time_zone'];
		}
		else if(isset($stateDetail) && !empty($stateDetail))
		{
			$usersTimezone =  $stateDetail->time_zone;
		}
		// echo '<pre>';
		// print_r($getCommunicationDetail);
		// die();
		$result = array();
		if(isset($getCommunicationDetail) && !empty($getCommunicationDetail))
		{
			//$listAttachments = $ContactCommunicationsMedia->getListAttachments($getCommunicationDetail['communication_id']);
			
			$attachment_count = count($getCommunicationDetail['contact_communications_media']);
			$time_zone = CommonFunctions::getShortCodeTimeZone($getCommunicationDetail['contact__communication']['agency_id']);
			$result['mail_subject'] = $getCommunicationDetail['contact__communication']['mail_subject'];
			$result['message'] = $getCommunicationDetail['content_reply'];
			$result['user'] = $getCommunicationDetail['Users'];
			$result['contact'] = $getCommunicationDetail['Contacts'];
			$result['created'] = $getCommunicationDetail['created'];
			$result['time_zone'] = $time_zone;
			$result['in_out'] = $getCommunicationDetail['in_out'];
			$result['attachment_count'] = $attachment_count;
			$result['contact_communications_media'] = $getCommunicationDetail['contact_communications_media'];
			$agencyTime = date('h:i a',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($getCommunicationDetail['created'])))));
			$dateMonth = date('M d, Y',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($getCommunicationDetail['created'])))));
			$result['agencyTime'] = $agencyTime;
			$result['dateMonth'] = $dateMonth;
			
		}
		
		$return['ContactCommunications.getCommunicationReply'] = [
            $communication_id => $result
        ];
        
        return $return;
	}
	
	public function getContactEmails($contact_id)
    {
			$emails_list = array();     
			$email = array();	
			$contact_id = $contact_id;
			$Contacts = TableRegistry::getTableLocator()->get('Contacts');
			$getContactDetail = $Contacts->get($contact_id);
			if(isset($getContactDetail) && !empty($getContactDetail)){
				if(isset($getContactDetail['email']) && !empty($getContactDetail['email']))
				{
					$email['id'] = $getContactDetail['email'];
					$email['name'] = $getContactDetail['first_name']. ' ' . $getContactDetail['last_name'];
					array_push($emails_list,$email);
				}
			}
			$ContactEmails = TableRegistry::getTableLocator()->get('ContactEmails');
			$getAllEmailArr = $ContactEmails->getAllEmailsByContact($contact_id);					
			if(isset($getAllEmailArr) && !empty($getAllEmailArr)){
				foreach ($getAllEmailArr as $key => $value) {		
				$email = array();		
				$email['id'] = $value['email'];
				$email['name'] = $value['email'];
				array_push($emails_list,$email);
				}
			}
		 $return['ContactCommunications.getContactEmails'] = [
			$contact_id => $emails_list
		];       
		return $return;
    }
	
	public function sendReplyEmail($objectData)
    {
         // pr($objectData); die;
		$session = Router::getRequest()->getSession(); 
        $login_user_id  =   $session->read('Auth.User.user_id');
        $login_agency_id    =   $session->read('Auth.User.agency_id');		
		$Agency = TableRegistry::getTableLocator()->get('Agency');
		$Users = TableRegistry::getTableLocator()->get('Users');
		$UsStates = TableRegistry::getTableLocator()->get('UsStates');
		$Contacts = TableRegistry::getTableLocator()->get('Contacts');
		$ContactCustomFields = TableRegistry::getTableLocator()->get('ContactCustomFields');
		$ReferralPartnerUserContacts = TableRegistry::getTableLocator()->get('ReferralPartnerUserContacts');
		$ReferredContact = TableRegistry::getTableLocator()->get('ReferredContact');
		$ContactPolicyLostDetails = TableRegistry::getTableLocator()->get('ContactPolicyLostDetails');
		$UserTokens = TableRegistry::getTableLocator()->get('UserTokens');
		$ContactCommunications = TableRegistry::getTableLocator()->get('ContactCommunications');
		$CommunicationReply = TableRegistry::getTableLocator()->get('CommunicationReply');
		$ContactCommunicationsMedia = TableRegistry::getTableLocator()->get('ContactCommunicationsMedia');
		
		$agencyDetails   =   $Agency->agencyDetails($login_agency_id);
        $mail_from      =   $objectData['reply_mail_from'];
        $mail_to        =   $objectData['reply_mail_to'];
        $mail_subject   =   $objectData['reply_mail_subject'];
        $mail_message   =   $objectData['reply_mail_message'];
        $masgid         =   $objectData['masgid'];
        $comm_type      =   $objectData['comm_type'];
        $comm_in_out      =   $objectData['communication_in_out'];
        //$mail_from_name =   $this->request->data['reply_mail_fromname'];
        $loggedUserDetail = $Users->userDetails($login_user_id);
        $mail_from_name = $loggedUserDetail['first_name']." ".$loggedUserDetail['last_name'];
        $emailCode      =   _DEFAULT;
        $toEmail        =   $mail_to;
        $fromEmail      =   $mail_from;
        $fromName       =   $mail_from_name;
        $SITE_URL       =   Router::url('/', true);
		$inputed_attachments = $objectData['inputed_attachments'];
		
        if(isset($agencyDetails) && !empty($agencyDetails))
        {
            $agencyName = !empty($agencyDetails['company'])?$agencyDetails['company']:"";
            $agencyStreetAddress = !empty($agencyDetails['address'])?$agencyDetails['address']:"";
             $agencyReviewLink = !empty($agencyDetails['google_my_business_link'])?$agencyDetails['google_my_business_link']:"";
            $agencyCity = !empty($agencyDetails['city'])?$agencyDetails['city']:"";
            $agencyStateId = !empty($agencyDetails['us_state_id'])?$agencyDetails['us_state_id']:"";
            $agencyState = "";
            if(!empty($agencyStateId))
            {
                $agencyStateDetail = $UsStates->get($agencyStateId);
                $agencyState = $agencyStateDetail->name;
            }
            $agencyEmail = !empty($agencyDetails['email'])?$agencyDetails['email']:"";
            $agencyPhone = !empty($agencyDetails['phone'])?$agencyDetails['phone']:"";
            $agencyLicense = !empty($agencyDetails['license'])?$agencyDetails['license']:"";
            $agencyProposalLink = "";
            if(isset($agencyDetails['allow_video_proposal_version']) && $agencyDetails['allow_video_proposal_version']==_QUOTE_SENT_VIDEO_PROPOSAL && !empty($agencyDetails['video_proposal_link'])){
                $agencyProposalLink = $agencyDetails['video_proposal_link'];
                //$agencyProposalLink = '<a href='.$link.' target="_blank"><img src="'.$SITE_URL.'img/video_icon2.png" style="width:50px;height:50px;"></a>';
            }
        }

        $contact_id_n   =   $objectData['contact_id_n'];
        $contactDetails = $Contacts->contactDetailsAdditionalInfo($contact_id_n);
        if(isset($contactDetails) && !empty($contactDetails))
        {
            $contactName =$contactDetails['first_name'].' '.$contactDetails['middle_name'].' '.$contactDetails['last_name'];
            $contactEmail = !empty($contactDetails['email'])?$contactDetails['email']:"";
            $contactPhone = !empty($contactDetails['phone'])?$contactDetails['phone']:"";
            //$contactFirstName = !empty($contactDetails['first_name'])?$contactDetails['first_name']:"";
            $contactFirstName = (!empty($contactDetails['preferred_name'])) ? $contactDetails['preferred_name'] : ((!empty($contactDetails['first_name'])) ? $contactDetails['first_name'] : '');
            $contactMiddleName = !empty($contactDetails['middle_name'])?$contactDetails['middle_name']:"";
            $contactLastName = !empty($contactDetails['last_name'])?$contactDetails['last_name']:"";
            $contactBirthdate = !empty($contactDetails['birth_date'])?date('M d, Y',strtotime($contactDetails['birth_date'])):"";
            $contactAddress1=!empty($contactDetails['address'])?$contactDetails['address']:"";
            $contactAddress2=!empty($contactDetails['address_line_2'])?$contactDetails['address_line_2']:"";
            $contactCity=!empty($contactDetails['city'])?$contactDetails['city']:"";
            $contactState = "";
            if(isset($contactDetails['state_id']) && !empty($contactDetails['state_id'])){
                $contactStateNameById = $UsStates->stateNameById($contactDetails['state_id']);
                if(isset($contactStateNameById['name']) && !empty($contactStateNameById['name'])){
                    $contactState = $contactStateNameById['name'];
                }
            }
            $contactZip=!empty($contactDetails['zip'])?$contactDetails['zip']:"";
            $maritalStatus = "";
            if(isset($contactDetails['marital_status']) && !empty($contactDetails['marital_status'])){
                $getEntityDef=getEntityDef($err,_MARITAL_STATUS,$contactDetails['marital_status']);
                $maritalStatus = $getEntityDef;
            }
            $spouse_first_name = "";
            $spouse_last_name = "";
            $driver_license_number = "";
            if(isset($contactDetails['contact_details']) && !empty($contactDetails['contact_details']))
            {
                if(isset($contactDetails['contact_details'][0]['spouse_first_name']) && !empty($contactDetails['contact_details'][0]['spouse_first_name'])){
                    $spouse_first_name = $contactDetails['contact_details'][0]['spouse_first_name'];
                }
                if(isset($contactDetails['contact_details'][0]['spouse_last_name']) && !empty($contactDetails['contact_details'][0]['spouse_last_name'])){
                    $spouse_last_name = $contactDetails['contact_details'][0]['spouse_last_name'];
                }
                if(isset($contactDetails['contact_details'][0]['driver_license_number']) && !empty($contactDetails['contact_details'][0]['driver_license_number']))
                {
                    $driver_license_number = $contactDetails['contact_details'][0]['driver_license_number'];
                    if(isset($contactDetails['contact_details'][0]['license_state_id']) && !empty($contactDetails['contact_details'][0]['license_state_id'])){
                        if($contactDetails['contact_details'][0]['license_state_id'] == _LICENSE_STATE_OTHER && $contactDetails['contact_details'][0]['other_state_name'] != "")
                        {
                            $driver_license_number  = $driver_license_number.' | '.$contactDetails['contact_details'][0]['other_state_name'];
                        }
                        elseif ($contactDetails['contact_details'][0]['license_state_id'] == _LICENSE_STATE_INTL)
                        {
                           $driver_license_number  = $driver_license_number.' | INTL';
                        }
                        elseif ($contactDetails['contact_details'][0]['license_state_id'] == _LICENSE_STATE_NONE)
                        {
                            $driver_license_number  = $driver_license_number;
                        }
                        else
                        {
                            $contactlicenseNameById = $UsStates->stateNameById($contactDetails['contact_details'][0]['license_state_id']);
                            if(isset($contactlicenseNameById['name']) && !empty($contactlicenseNameById['name'])){
                                $driver_license_number  = $driver_license_number.' | '.$contactlicenseNameById['name'];
                            }
                        }
                    }
                }
            }
        }


        $userDetails=$Users->userDetails($login_user_id);

        if(isset($userDetails) && !empty($userDetails))
        {
            $agentName = $userDetails['first_name'].' '.$userDetails['last_name'];
            $agentEmail = !empty($userDetails['email'])?$userDetails['email']:"";
            $agentPhone = !empty($userDetails['phone'])?$userDetails['phone']:"";
            $agentFirstName = !empty($userDetails['first_name'])?$userDetails['first_name']:"";
            $agentLastName = !empty($userDetails['last_name'])?$userDetails['last_name']:"";
            $agent_calendar_link = !empty($userDetails['calendar_link'])?$userDetails['calendar_link']:"";
        }


         $contactCustomFields = $ContactCustomFields->getAllCustomFieldsByContact($contact_id_n);
        $custom_fields_array = [];
        if(isset($contactCustomFields) && !empty($contactCustomFields))
        {
            foreach ($contactCustomFields as $key => $value)
            {
                // if(isset($value['contact_custom_fields']) && !empty($value['contact_custom_fields'])){
                //     foreach ($value['contact_custom_fields'] as $key => $value)
                //     {
                        if(isset($value['field_value']) && !empty($value['field_value']) && $value['field_value'] != '--'){
                            $field_value = $value['field_value'];
                        }else{
                            $field_value = "";
                        }
                        $custom_fields_array['{custom'.'.'.$value['field_label'].'}'] = $field_value;
                    //}
                //}
            }
        }

        if (stripos($mail_message, 'http://{agent.calendarLink}') !== false)
        {
            $mail_message = str_ireplace('http://{agent.calendarLink}', '{agent.calendarLink}', $mail_message);
        }

        if (stripos($mail_message, 'http://{agency.proposalLink}') !== false)
        {
            $mail_message = str_ireplace('http://{agency.proposalLink}', '{agency.proposalLink}', $mail_message);
        }
         if (stripos($mail_message, 'http://{agency.reviewlink}') !== false)
        {
            $mail_message = str_ireplace('http://{agency.reviewlink}', '{agency.reviewlink}', $mail_message);
        }


        //client referral link
        $client_referral_link="";
        if(isset($agencyDetails) && !empty($agencyDetails) && isset($userDetails) && !empty($userDetails) && isset($contactDetails) && !empty($contactDetails))
        {
            $client_referral_link = _SITE_PROTOCOLE.$agencyDetails['sub_domain'].'.'._BETTER_REFERRAL_DOMAIN.'/clientreferral/'.base64_encode($userDetails['id']).'/'.base64_encode($contactDetails['id']);
        }

        //referral partner merge fields
        //contact referral partner detail
        $referral_partner_user_email = "";
        $contact_referral_partner_user_detail = $ReferralPartnerUserContacts->getContactReferralPartnerUserDetailWithPrimaryEmail($contact_id_n);
        if(isset($contact_referral_partner_user_detail['referral_partner_user']['referral_partner_user_email_phone'][0]['email_phone']) && !empty($contact_referral_partner_user_detail['referral_partner_user']['referral_partner_user_email_phone'][0]['email_phone']))
        {
            $referral_partner_user_email = $contact_referral_partner_user_detail['referral_partner_user']['referral_partner_user_email_phone'][0]['email_phone'];
        }
        $referral_partner_user_id="";
        $referral_partner_user_do_not_contact=0;
        $referral_partner_user_first_name="";
        $referral_partner_user_last_name="";
        $referral_partner_company="";
        if(isset($contact_referral_partner_user_detail['referral_partner_user']) && !empty($contact_referral_partner_user_detail['referral_partner_user']))
        {
            $referral_partner_user_id = $contact_referral_partner_user_detail['referral_partner_user']['id'];
            $referral_partner_user_do_not_contact = $contact_referral_partner_user_detail['referral_partner_user']['do_not_contact'];
            if(isset($contact_referral_partner_user_detail['referral_partner_user']['first_name']) && !empty($contact_referral_partner_user_detail['referral_partner_user']['first_name']))
            {
                $referral_partner_user_first_name = $contact_referral_partner_user_detail['referral_partner_user']['first_name'];
            }
            if(isset($contact_referral_partner_user_detail['referral_partner_user']['last_name']) && !empty($contact_referral_partner_user_detail['referral_partner_user']['last_name']))
            {
                $referral_partner_user_last_name = $contact_referral_partner_user_detail['referral_partner_user']['last_name'];
            }
            if(isset($contact_referral_partner_user_detail['referral_partner']['company']) && !empty($contact_referral_partner_user_detail['referral_partner']['company']))
            {
                $referral_partner_company = $contact_referral_partner_user_detail['referral_partner']['company'];
            }
        }

        //contact referral partner phone detail
        $referral_partner_user_phone = "";
        $contact_referral_partner_user_detail_phone = $ReferralPartnerUserContacts->getContactReferralPartnerUserDetailWithPrimaryPhone($contact_id_n);
        if(isset($contact_referral_partner_user_detail_phone['referral_partner_user']['referral_partner_user_email_phone'][0]['email_phone']) && !empty($contact_referral_partner_user_detail_phone['referral_partner_user']['referral_partner_user_email_phone'][0]['email_phone']))
        {
            $referral_partner_user_phone = $contact_referral_partner_user_detail_phone['referral_partner_user']['referral_partner_user_email_phone'][0]['email_phone'];
        }

        //client referral merge fields
        $client_referral_first_name = "";
        $client_referral_last_name = "";
        $client_referral_email = "";
        $client_referral_phone = "";
        $client_referrer_details = $ReferredContact->getReferrerContactByReferralContactId($contact_id_n);
        if(isset($client_referrer_details) && !empty($client_referrer_details) && isset($client_referrer_details['contact']) && !empty($client_referrer_details['contact']))
        {
            if(isset($client_referrer_details['contact']['first_name']) && !empty($client_referrer_details['contact']['first_name']))
            {
                $client_referral_first_name = $client_referrer_details['contact']['first_name'];
            }
            if(isset($client_referrer_details['contact']['last_name']) && !empty($client_referrer_details['contact']['last_name']))
            {
                $client_referral_last_name = $client_referrer_details['contact']['last_name'];
            }
            if(isset($client_referrer_details['contact']['email']) && !empty($client_referrer_details['contact']['email']))
            {
                $client_referral_email = $client_referrer_details['contact']['email'];
            }
            if(isset($client_referrer_details['contact']['phone']) && !empty($client_referrer_details['contact']['phone']))
            {
                $client_referral_phone = $client_referrer_details['contact']['phone'];
            }
        }
        else if(isset($client_referrer_details) && !empty($client_referrer_details))
        {
            if(isset($client_referrer_details['referrer_first_name']) && !empty($client_referrer_details['referrer_first_name']))
            {
                $client_referral_first_name = $client_referrer_details['referrer_first_name'];
            }
            if(isset($client_referrer_details['referrer_last_name']) && !empty($client_referrer_details['referrer_last_name']))
            {
                $client_referral_last_name = $client_referrer_details['referrer_last_name'];
            }
            if(isset($client_referrer_details['referrer_email']) && !empty($client_referrer_details['referrer_email']))
            {
                $client_referral_email = $client_referrer_details['referrer_email'];
            }
            if(isset($client_referrer_details['referrer_phone']) && !empty($client_referrer_details['referrer_phone']))
            {
                $client_referral_phone = $client_referrer_details['referrer_phone'];
            }
        }
		// added lost carrier for last policy cancelled
		$lost_carrier = '';
		$lost_carrier_data = $ContactPolicyLostDetails->getContactLostPolicyCarrier($contact_id_n);
		if(isset($lost_carrier_data) && !empty($lost_carrier_data)){
			$lost_carrier = $lost_carrier_data['carrier_name'];
		}
		
        $mergeFieldArray = array(
            '{agency.name}' => ucwords($agencyName),
            '{agency.streetAddress}' => $agencyStreetAddress,
            '{agency.reviewlink}' => $agencyReviewLink,
            '{agency.city}' => $agencyCity,
            '{agency.state}' => $agencyState,
            '{agency.email}' => $agencyEmail,
            '{agency.phone}' => $agencyPhone,
            '{agency.license}' => $agencyLicense,
            '{agent.name}' => ucwords($agentName),
            '{agent.email}' => $agentEmail,
            '{agent.phone}' => $agentPhone,
            '{contact.name}' => $contactName,
            '{contact.email}' => $contactEmail,
            '{contact.phone}' => $contactPhone,
            '{contact.firstName}' => $contactFirstName,
			'{contact.middleName}' => $contactMiddleName,
            '{contact.lastName}' => $contactLastName,
            '{agent.firstName}' => $agentFirstName,
            '{agent.lastName}' => $agentLastName,
            '{contact.firstName}' => $contactFirstName,
            '{agent.calendarLink}'=>$agent_calendar_link,
            '{contact.birthdate}'=>$contactBirthdate,
            '{agency.proposalLink}'=>$agencyProposalLink,
            '{contact.address1}'=>$contactAddress1,
            '{contact.address2}'=>$contactAddress2,
            '{contact.city}'=>$contactCity,
            '{contact.state}'=>$contactState,
            '{contact.zip}'=>$contactZip,
            '{contact.maritalStatus}'=>$maritalStatus,
            '{contact.spouseFirstName}'=>$spouse_first_name,
            '{contact.spouseLastName}'=>$spouse_last_name,
            '{contact.driversLicenseNumber}'=>$driver_license_number,
            '{clientReferralLink}'=>$client_referral_link,
            '{referralpartner.email}'=>$referral_partner_user_email,
            '{referralpartner.phone}'=>$referral_partner_user_phone,
            '{referralpartner.firstName}'=>$referral_partner_user_first_name,
            '{referralpartner.lastName}'=>$referral_partner_user_last_name,
            '{referralpartner.company}'=>$referral_partner_company,
            '{clientreferrer.firstName}'=>$client_referral_first_name,
            '{clientreferrer.lastName}'=>$client_referral_last_name,
            '{clientreferrer.email}'=>$client_referral_email,
            '{clientreferrer.phone}'=>$client_referral_phone,
			'{contact.lostCarrier}'=>$lost_carrier,
        );

        $user_logo = "";
        if(!empty($loggedUserDetail['headshot'])){
            $user_logo = $loggedUserDetail['headshot'];
        }

        $agency_logo = "";
        if(!empty($agencyDetails['headshot'])){
            $agency_logo = $agencyDetails['headshot'];
        }

        if (stripos($mail_message, '{agency.logo}') !== false)
        {
            if(isset($agency_logo)  && !empty($agency_logo) ){
                $mail_message = str_ireplace('{agency.logo}', '<img src="'.$agency_logo.'" style="width:50px;height:50px;">', $mail_message);
            }else{
                $mail_message = str_ireplace('{agency.logo}', '', $mail_message);
            }

        }
        if (stripos($mail_message, '{user.logo}') !== false)
        {
            if(isset($user_logo)  && !empty($user_logo) ){
                $mail_message = str_ireplace('{user.logo}', '<img src="'.$user_logo.'" style="width:50px;height:50px;">', $mail_message);
            }else{
                $mail_message = str_ireplace('{user.logo}', '', $mail_message);
            }
        }

        if (stripos($mail_message, '{agency.proposalLink}') !== false)
        {
            if(isset($agencyProposalLink)  && !empty($agencyProposalLink) ){
                $mail_message = str_ireplace('{agency.proposalLink}', $agencyProposalLink, $mail_message);
            }else{
                $mail_message = str_ireplace('{agency.proposalLink}', '', $mail_message);
            }

        }

       $finalContentArray = array_merge($custom_fields_array,$mergeFieldArray);
        if (is_array($finalContentArray) && !empty($finalContentArray)) {
            foreach ($finalContentArray as $key => $value) {
                //if($key != '{agency.proposalLink}'){
                    $mail_message = str_ireplace($key, $value, $mail_message);
                    $reply_mail_message = str_ireplace($key, $value, $mail_message);
                //}
                $mail_subject = str_ireplace($key, $value, $mail_subject);
            }
        }
        if(isset($mail_to) && isset($mail_message)){
            $type = _COMMUNICATION_TYPE_EMAIL;
            $mesagereply = $reply_mail_message;
            $date   = date('Y-m-d H:i:s');
            // $communicationReplyData =  CommonFunctions::CommunicationReplyData($comm_type, $masgid , $toEmail, $mail_from, $fromName, $mail_subject,$mesagereply,$comm_in_out);
            $communicationReplyData = CommonFunctions::saveCommunicationReplyData($login_agency_id,$login_user_id,$contact_id_n,$comm_type, $masgid , $toEmail, $mail_from, $fromName, $mail_subject,$mesagereply,$comm_in_out);



            $contentArray = array(
                '{MAIL_SUBJECT}' => $mail_subject,
                '{MAIL_CONTENT}' => $mail_message,
                '{SITE_URL}' => $SITE_URL,
            );
			$attachents_info_mail = [];		   
			if(isset($inputed_attachments) && !empty($inputed_attachments))
			{

				$attachents_info_mail = json_decode($inputed_attachments,true);
				//echo "<pre>";print_r($attachents_info_mail);die("dsfs");

			}
            $user_token_details = $UserTokens->getActiveUserTokenDetailByUserId($login_user_id);
            $nylas_status = false;
            if(isset($user_token_details['nylas_status']) && !empty($user_token_details['nylas_status']) && ($user_token_details['nylas_status'] == _NYLAS_STATUS_AUTHENTICATED ))
            {
                $nylas_status = true;
            }
            $gridMail = "";
			if($nylas_status)
            {
                //send email by nylas code start here
                if(!empty($communicationReplyData))
                {
                     $gridMail = $ContactCommunications->updateAll(['sent_status_nylas' => _NYLAS_EMAIL_STATUS_QUEUED],['id' => $communicationReplyData->id]);
//                    $gridMail = _ID_SUCCESS;
                }
            }
            else
            {
                $gridMail = CommonFunctions::sendGridMail($emailCode, $toEmail, $fromEmail, $fromName, $contentArray,'','',$attachents_info_mail);
            }
            if($gridMail){
                if(!empty($communicationReplyData))
                {
                    $ContactCommunications->updateAll(['sent_status' => _SENT_STATUS_DELIVERED],['id' => $communicationReplyData->id]);//update sent status to delivered
					if(isset($attachents_info_mail) && !empty($attachents_info_mail))
					{
						foreach ($attachents_info_mail as $key => $value)
						{
							$arr_media_details=[];
							$arr_media_details['communication_id']=$communicationReplyData->id;
							$arr_media_details['url']=$value['url'];
							$arr_media_details['name']=$value['name'];
							if(isset($value['file_url']) && !empty($value['file_url']) && isset($value['file_aws_key']) && !empty($value['file_aws_key'])){
								$arr_media_details['file_url']=$value['file_url'];
								$arr_media_details['file_aws_key']=$value['file_aws_key'];
							}
							$arr_media_details['media_type']=_MEDIA_TYPE_EMAIL;
							$contact_media_data = $ContactCommunicationsMedia->newEntity();
							$contact_media_data = $ContactCommunicationsMedia->patchEntity($contact_media_data,$arr_media_details);
							$ContactCommunicationsMedia->save($contact_media_data);
						}
					}
                }
                if(isset($gridMail['status']) && !empty($gridMail['status']))
				{
					$response =  json_encode(array('status' => $gridMail['status'],'message'=>$gridMail['meassage']));
				}else{
					$response = json_encode(array('status' => _ID_SUCCESS,'message'=>'Email Sent Successfully!'));	
				}
            }
        }
        else{
            $response = json_encode(array('status' => _ID_FAILED));
        }
		return $response;
    }


      public static function loadMoreEmailData($objectData)
    {
        try
		{
			$session = Router::getRequest()->getSession();
            $login_user_id= $session->read("Auth.User.user_id");
            $type = null;
            $lastCommunicationsDateTime = null;
            $result =[];
            $result2 =[];
            $ContactCommunicationTable = TableRegistry::getTableLocator()->get('ContactCommunications');
			$login_agency_id = $session->read('Auth.User.agency_id');
			$Agency = TableRegistry::getTableLocator()->get('Agency');
			$agencyDetail = $Agency->agencyDetails($login_agency_id);
			$usersTimezone='America/Phoenix';
			$UsStates = TableRegistry::getTableLocator()->get('UsStates');
			$stateDetail = $UsStates->stateDetail($agencyDetail['us_state_id']);
			if(isset($agencyDetail['time_zone']) && !empty($agencyDetail['time_zone']))
			{
				$usersTimezone =  $agencyDetail['time_zone'];
			}
			else if(isset($stateDetail) && !empty($stateDetail))
			{
				$usersTimezone =  $stateDetail->time_zone;
			}
            if(isset($details['type'])){
                $type = $details['type'];
            }
//			$contactCommunicationsMedia = TableRegistry::getTableLocator()->get('ContactCommunicationsMedia');

            if(isset($details['lastCommunicationsDateTime'])){
                $lastCommunicationsDateTime = $details['lastCommunicationsDateTime'];
            }

            if(isset($objectData) && !empty($objectData))
		    {

                $contactId = $objectData['contact_id'];
                $limit = $objectData['limit'];
                $offSet = $objectData['offSet'];

                if(!PermissionsCheck::currentUserHasReadPermission(ContactsQuickTable::findById($contactId))) {
                    throw new UnauthorizedException();
                }

//                $CommunicationReply = TableRegistry::getTableLocator()->get('CommunicationReply');
//                $response['email_details'] = $ContactCommunicationTable->find('all')->contain(['Users','ContactCommunicationsMedia','Contacts'])
//                ->where(['ContactCommunications.contact_id' => $contactId,'ContactCommunications.communication_type'=>1])->limit($limit)->offset($offSet)->order(['ContactCommunications.created' => 'DESC'])
//                ->hydrate(false)->toArray();
                $response['email_details'] = $ContactCommunicationTable->find('all')
                    ->select([
                        'ContactCommunications.id',
                        'ContactCommunications.message',
                        'ContactCommunications.mail_subject',
                        'ContactCommunications.in_out',
                        'ContactCommunications.parent_communication_id',
                        'ContactCommunications.created',
                        'Contacts.first_name',
                        'Contacts.last_name',
                        'Users.first_name',
                        'Users.last_name',
                        'Users.headshot'
                    ])
                    ->contain(['Users','Contacts','ContactCommunicationsMedia'])
                    ->where(['ContactCommunications.contact_id' => $contactId,
                        'ContactCommunications.communication_type' => 1,
                        'ContactCommunications.status' => 1
                    ])
                    ->limit($limit)
                    ->offset($offSet)
                    ->order(['ContactCommunications.created' => 'DESC'])
                    ->hydrate(false)->toArray();

                $i = 0;
                foreach($response as $key=> $data)
                {
                    foreach (array_unique(array_column($response[$key], 'created')) as $date) {
                        foreach (array_filter($response[$key], function($v) use ($date) {
                        return $v['created'] == $date;
                        }) as $overview) {
                            $time_zone = CommonFunctions::getShortCodeTimeZone($overview['agency_id']);
                            $overview['time_zone'] = $time_zone;
                            $month = date("F",strtotime($date));
                            $year = date("Y",strtotime($date));
                            $month_year = $month."-".$year;
                            $agencyTime = date('h:i a',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($overview['created'])))));
                            $dateMonth = date('F d, Y',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($overview['created'])))));
                            $result[$month_year][$key][$i]['agencyTime'] = $agencyTime;
                            $result[$month_year][$key][$i]['dateMonth'] = $dateMonth;
                            $atachment_count  = count($overview['contact_communications_media']);
                            $result[$month_year][$key][$i]['id'] = $overview['id'];
//                            $result[$month_year][$key][$i]['user'] = $overview['user'];
                            $result[$month_year][$key][$i]['user']['first_name'] = $overview['user']['first_name'];
                            $result[$month_year][$key][$i]['user']['last_name'] = $overview['user']['last_name'];
                            $result[$month_year][$key][$i]['contact_communications_media'] = $overview['contact_communications_media'];
                            $result[$month_year][$key][$i]['created'] = $overview['created'];
                            $result[$month_year][$key][$i]['message'] = $overview['message'];
                            $result[$month_year][$key][$i]['mail_subject'] = $overview['mail_subject'];
                            $result[$month_year][$key][$i]['time_zone'] = $overview['time_zone'];
                            $result[$month_year][$key][$i]['communication_id'] = '';
                            $result[$month_year][$key][$i]['created_datetime'] = strtotime($overview['created']);
                            $result[$month_year][$key][$i]['in_out'] = $overview['in_out'];
//                          $result[$month_year][$key][$i]['contact'] = $overview['contact'];
                            $result[$month_year][$key][$i]['contact']['first_name'] = $overview['contact']['first_name'];
                            $result[$month_year][$key][$i]['contact']['last_name'] = $overview['contact']['last_name'];
                            $result[$month_year][$key][$i]['attachment_count'] = $atachment_count;
                            if(isset($overview['parent_communication_id']) && $overview['parent_communication_id'] != '')
                            {
                                $result[$month_year][$key][$i]['parent_communication_id'] = $overview['parent_communication_id'];
                            }
                            else
                            {
                                $result[$month_year][$key][$i]['parent_communication_id'] = '';
                            }
                            $i++;
                        }

                    }

                }
//                $limit = 10;
//                $offset = 0;
//                $res['email_details'] = $CommunicationReply->allCommunicationRepliesWithLimit($login_user_id, null, $contactId, $limit, $offSet);
//                $j = 0;
//                foreach($response as $key=> $data)
//                {
//                    foreach (array_unique(array_column($res[$key], 'created')) as $date) {
//                        foreach (array_filter($res[$key], function($v) use ($date) {
//                        return $v['created'] == $date;
//                        }) as $overview) {
//                            $time_zone = CommonFunctions::getShortCodeTimeZone($login_agency_id);
//                            $overview['time_zone'] = $time_zone;
//                            $month = date("F",strtotime($date));
//                            $year = date("Y",strtotime($date));
//                            $month_year = $month."-".$year;
//                            $agencyTime = date('h:i a',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($overview['created'])))));
//                            $dateMonth = date('F d, Y',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($overview['created'])))));
//                            $result2[$month_year][$key][$j]['agencyTime'] = $agencyTime;
//                            $result2[$month_year][$key][$j]['dateMonth'] = $dateMonth;
//                            $listAttachments = $contactCommunicationsMedia->getListAttachments($overview['communication_id']);
//                            $atachment_count  = count($overview['contact_communications_media']);
//                            $result2[$month_year][$key][$j]['contact_communications_media'] = $overview['contact_communications_media'];
//                            $result2[$month_year][$key][$j]['id'] = $overview['id'];
//                            $result2[$month_year][$key][$j]['user'] = $overview['Users'];
//                            $result2[$month_year][$key][$j]['created'] = $overview['created'];
//                            $result2[$month_year][$key][$j]['message'] = $overview['content_reply'];
//                            $result2[$month_year][$key][$j]['mail_subject'] = $overview['subject_reply'];
//                            $result2[$month_year][$key][$j]['time_zone'] = $overview['time_zone'];
//                            $result2[$month_year][$key][$j]['communication_id'] = $overview['communication_id'];
//                            $result2[$month_year][$key][$j]['created_datetime'] = strtotime($overview['created']);
//                            $result2[$month_year][$key][$j]['contact'] = $overview['Contacts'];
//                            $result2[$month_year][$key][$j]['in_out'] = $overview['in_out'];
//                            $result2[$month_year][$key][$j]['attachment_count'] = $atachment_count;
//                            $j++;
//                        }
//
//                    }
//                }


                $newArray = $result;
                uksort($newArray, function($a1, $a2) {
                    $time1 = strtotime($a1);
                    $time2 = strtotime($a2);

                    return $time2 - $time1;
                });
                foreach($newArray as $key=>$value)
                {
                    $sArray = $value['email_details'];
                    array_multisort(array_column($sArray, 'created_datetime'), SORT_DESC, $sArray);
                    $newArray[$key]['email_details'] = $sArray;
                }

                $return = json_encode(array('status' => _ID_SUCCESS, 'data' => $newArray));
            }else{
                $return = json_encode(array('status' => _ID_FAILED));
            }
            return $return;
        }catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: Email Lising Error- '.$e->getMessage();
			$return = json_encode(array('status' => _ID_FAILED, 'error'=>$txt));
        }
    }


    public static function loadMoreSmsData($objectData)
    {
       
        try
		{
			$myfile = fopen(ROOT."/logs/vueSmsListing.log", "a") or die("Unable to open file!");
            $type = null;
            $session = Router::getRequest()->getSession();
            $lastCommunicationsDateTime = null;
           
            $response = [
                'ContactCommunication' => []
            ];
            if(isset($objectData) && !empty($objectData)) {
                $login_agency_id = $session->read('Auth.User.agency_id');
                $Agency = TableRegistry::getTableLocator()->get('Agency');
                $agencyDetail = $Agency->agencyDetails($login_agency_id);
                $usersTimezone = 'America/Phoenix';
                $UsStates = TableRegistry::getTableLocator()->get('UsStates');
                $stateDetail = $UsStates->stateDetail($agencyDetail['us_state_id']);
                if (isset($agencyDetail['time_zone']) && !empty($agencyDetail['time_zone'])) {
                    $usersTimezone = $agencyDetail['time_zone'];
                } else if (isset($stateDetail) && !empty($stateDetail)) {
                    $usersTimezone = $stateDetail->time_zone;
                }
                if (isset($details['type'])) {
                    $type = $details['type'];
                }

                $contactId = $objectData['contact_id'];
                $limit = $objectData['limit'];
                $offset = $objectData['offset'];

                if (isset($details['lastCommunicationsDateTime'])) {
                    $lastCommunicationsDateTime = $details['lastCommunicationsDateTime'];
                }

                if (!PermissionsCheck::currentUserHasReadPermission(ContactsQuickTable::findById($contactId))) {
                    throw new UnauthorizedException();
                }

                /** @var ContactCommunication[] $contactCommunications */
                $contactCommunications = ContactCommunicationsQuickTable::findAllBy(['contact_id' => $contactId, 'communication_type' => _COMMUNICATION_TYPE_SMS, 'ContactCommunications.status !=' => _ID_STATUS_DELETED], ['Users', 'ContactCommunicationsMedia','CommunicationReply'], ['ContactCommunications.created' => 'DESC'], $limit, $offset);

           
                $communication_date_for_created = '';
                $communicationData = [];
                $previousValue = null;
                foreach ($contactCommunications as $key => $contactCommunication) {

                   
                    
                    if(date('Y-m-d',strtotime($contactCommunication['created'])) != date('Y-m-d',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d',strtotime(  $contactCommunication['created']))))))
                    {
                        $communication_date_for_created = CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d',strtotime($contactCommunication['created'])));

                        $communication_date_stamp = date('h:i a',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($contactCommunication['created'])))));
                        
                        $contactCommunication['sms_date'] =  date('l M d, Y',strtotime($communication_date_stamp));
                       
                    }
                 
                    $smsTime = date('h:i a', strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s', strtotime($contactCommunication['created'])))));
                    $contactCommunication['created'] = $smsTime;

                    foreach($contactCommunication['communication_reply'] as $reply)
                    {
                        $replyTime = date('h:i a',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($reply['created'])))));
                        $reply['created'] = $replyTime;
                    }

                   
                    $response['ContactCommunication'][$contactCommunication['id']] = $communicationData;
                }

                //echo "<pre>";print_r($return);die("dfd");
                $response['ContactCommunications.getSmsData'] = [
                    $contactId => $response['ContactCommunication']
                ];
                 $return = json_encode(array('status' => _ID_SUCCESS, 'data' => $response));
            }
            else
            {
                 $return = json_encode(array('status' => _ID_FAILED));
            }
           
               return $return;
        }catch (\Exception $e) {
			
            $txt=date('Y-m-d H:i:s').' :: Sms Lising Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
    }

    public function getAgencyPersonalCustomFields($contactId)
    {
        try
        {
            $session = Router::getRequest()->getSession();
            $login_agency_id = $session->read('Auth.User.agency_id');

            $AgencyCustomFields = TableRegistry::getTableLocator()->get('AgencyCustomFields');
			$allAgencyCustomFields = $AgencyCustomFields->customFieldsByPersonalCommercialAgencyId($login_agency_id,_POLICY_LINE_PERSONAL);
            $return['ContactCommunications.getAgencyPersonalCustomFields'] = [
                $contactId => $allAgencyCustomFields
            ];
            return $return;
        }
		catch (\Exception $e) 
		{

        }
    }

//    <--- delete emails --->
    public function deleteEmail($emailId)
    {
        $session = Router::getRequest()->getSession();
        $login_agency_id = $session->read("Auth.User.agency_id");
        $login_user_id = $session->read("Auth.User.user_id");
        $Contacts = TableRegistry::getTableLocator()->get('Contacts');
        $ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
        $CampaignRunningEmailSmsSchedule = TableRegistry::getTableLocator()->get('CampaignRunningEmailSmsSchedule');
        $ContactCommunications = TableRegistry::getTableLocator()->get('ContactCommunications');
        $ContactCommunicationsLogs = TableRegistry::getTableLocator()->get('ContactCommunicationLogs');

		$response = [];
        if($emailId)
        {
            $email = $ContactCommunications->get($emailId);
            if ($email)
            {
                $ContactCommunications->updateAll(['status' => _ID_STATUS_DELETED],['ContactCommunications.id' => $emailId, 'status !=' => _ID_STATUS_DELETED, 'agency_id' => $login_agency_id]);
                $arrEmailDetails['agency_id'] = $login_agency_id;
                $arrEmailDetails['user_id'] = $login_user_id;
                $arrEmailDetails['contact_id'] = $email->contact_id;
                $arrEmailDetails['email_type'] = ($email['campaign_running_schedule_id']) ? _ID_CAMPAIGN_EMAIL : _ID_SYNCED_EMAIL;
                $arrEmailDetails['contact_communication_id'] = $emailId;
                $arrEmailDetails['status'] = _ID_STATUS_ACTIVE;
                $emaildata = $ContactCommunicationsLogs->newEntity();
                $emaildata = $ContactCommunicationsLogs->patchEntity($emaildata,$arrEmailDetails);
                $emaildata = $ContactCommunicationsLogs->save($emaildata);

                $emailInfo = $ContactCommunicationsLogs->find('all')
                ->contain(['Users', 'Contacts', 'ContactCommunications'])
                ->select(['Users.first_name', 'Users.last_name', 'ContactCommunicationLogs.user_id','ContactCommunicationLogs.email_type', 'ContactCommunicationLogs.contact_id', 'ContactCommunicationLogs.created' ])
                ->where(['ContactCommunicationLogs.contact_communication_id' => $emailId,])->hydrate(false)->toArray();
                $response['status'] = _ID_SUCCESS;
                $response['data'] = $emailInfo;
                $return['ContactCommunications.deleteEmail'] = [
                    $emailId => $response
                ];
            }
            else
            {
                $response['status'] = _ID_SUCCESS;
                $return['ContactCommunications.deleteEmail'] = [
                    $emailId => $response
                ];
            }
		}
		$return['ContactCommunications.deleteEmail'] = [
            $emailId => $response
        ];
        return $return;
    }
}
