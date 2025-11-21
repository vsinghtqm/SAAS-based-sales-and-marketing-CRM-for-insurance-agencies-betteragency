<?php

namespace App\Lib\ApiProviders;

use App\Lib\PermissionsCheck;
use App\Lib\QuickTables\ContactsQuickTable;
use App\Model\Entity\Contact;
use App\Model\Entity\Tasks;
use App\Model\Entity\ContactNotes;
use App\Model\Entity\ContactCommunications;
use Cake\Http\Exception\UnauthorizedException;
use Google\Exception;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use App\Classes\CommonFunctions;
class ContactOverviews
{
    public static function getContactOverview($contactId, $fields=null){
		
		$result = [];
		$response = [];
		$TaskTable = TableRegistry::getTableLocator()->get('Tasks');
		$ContactNotesTable = TableRegistry::getTableLocator()->get('ContactNotes');
		$ContactCommunicationTable = TableRegistry::getTableLocator()->get('ContactCommunications');
		$UsStates = TableRegistry::getTableLocator()->get('UsStates');		
		$session = Router::getRequest()->getSession(); 
		$login_agency_id = $session->read('Auth.User.agency_id');
        $login_user_id= $session->read("Auth.User.user_id");
		// $contactCommunicationsMedia = TableRegistry::getTableLocator()->get('ContactCommunicationsMedia');
		$Agency = TableRegistry::getTableLocator()->get('Agency');		
		$agencyDetail = $Agency->agencyDetails($login_agency_id);
		$ContactOppTable = TableRegistry::getTableLocator()->get('ContactOpportunities');
        $currentDate = date('Y-m-d');
        $lastMonthDate = date('Y-m-d', strtotime("-6 months", strtotime($currentDate)));
		if(isset($agencyDetail['video_proposal_link']) && !empty($agencyDetail['video_proposal_link']))
		{
			$video_proposal_link = $agencyDetail['video_proposal_link'];
		}
		$agency_logo = '';
		if(isset($agencyDetail['headshot']) && !empty($agencyDetail['headshot']))
		{
			$agency_logo = $agencyDetail['headshot'];
		}
		 $usersTimezone = 'America/Phoenix';
          $agency_time_zone = $session->read('Auth.User.agency_session_detail.time_zone');
          if(isset($agency_time_zone) && $agency_time_zone != ''){
             $usersTimezone = $agency_time_zone;
          }else {
            $agency_state_id = $session->read('Auth.User.agency_session_detail.us_state_id');
            if (isset($agency_state_id) && $agency_state_id != '') {
               $stateDetail = $UsStates->stateDetail($agency_state_id);
                    if (isset($stateDetail) && !empty($stateDetail)) {
                        $usersTimezone = $stateDetail->time_zone;
                    }
                }
            }
		$response['overviews'] = $TaskTable->find()->where(['contact_id' => $contactId, 'date(Tasks.created) <=' => $currentDate, 'date(Tasks.created) >=' => $lastMonthDate])
		->select(['ud.first_name','ud.last_name','cd.first_name','cd.last_name','user_id','title','tp.opportunity_id','priority','task_category_id','tc.name','Tasks.id','Tasks.agency_id','Tasks.status','Tasks.created','Tasks.due_date','description',"dueDateCategory"=>"DATE_FORMAT(Tasks.due_date,'%M %e,%Y')","dueTimeCategory"=>"DATE_FORMAT(Tasks.due_date,'%h:%i %p')"])->where(['Tasks.status !=' => 3])
		->join([ 
            "table" => "users",
            "alias" => "ud",
            "type" => "INNER",
            "conditions" => "Tasks.user_id = ud.id"
        ])
		->join([
            "table" => "contacts",
            "alias" => "cd",
            "type" => "INNER",
            "conditions" => "Tasks.contact_id = cd.id"
        ])->join([
			"table" => "task_policies_link",
			"alias" => "tp",
			"type" => "LEFT",
			"conditions" => "Tasks.id = tp.task_id"
		])->join([
			"table" => "task_categories",
			"alias" => "tc",
			"type" => "LEFT",
			"conditions" => "Tasks.task_category_id = tc.id"
		])->order(['Tasks.created' => 'DESC'])
		->hydrate(false)->toArray();
		$i = 0;	
		foreach($response as $key=> $data)
		{
			foreach (array_unique(array_column($response[$key], 'created')) as $date) {
				foreach (array_filter($response[$key], function($v) use ($date) { 
				return $v['created'] == $date; 
				}) as $overview) {
					$time_zone = CommonFunctions::getShortCodeTimeZone($login_agency_id);
					$overview['time_zone'] = $time_zone;	
					$month = date("F",strtotime($date));
					$year = date("Y",strtotime($date));
					$month_year = $month."-".$year;	
					$agencyTime = date('h:i a',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($overview['created'])))));
					$dateMonth = date('M d, Y',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d',strtotime($overview['created'])))));
					if(isset($overview['tp']['opportunity_id']) && !empty($overview['tp']['opportunity_id']))
					{
						$result2 = $ContactOppTable->find()->where(['id' =>$overview['tp']['opportunity_id']])
						->select(['policy_number','insurance_type_id'])->hydrate(false)->first();
						if(isset($result2['policy_number']) && $result2['policy_number'] != '')
						{

							$overview['policy_number'] = 'Policy #'.$result2['policy_number'];
						}
					}else{
						$overview['policy_number'] = '';
					}
					if(isset($overview['tc']['name']) && !empty($overview['tc']['name']))
					{
						$overview['task_category_name'] = $overview['tc']['name'];
					}else{
						$overview['task_category_name'] = '';
					}
					$result[$month_year][$key][$i]['agencyTime'] = $agencyTime;
					$result[$month_year][$key][$i]['dateMonth'] = $dateMonth;
					$result[$month_year][$key][$i]['type'] = 'task';
					$result[$month_year][$key][$i]['id'] = $overview['id'];
					$result[$month_year][$key][$i]['ud'] = $overview['ud'];
					$result[$month_year][$key][$i]['dueDateCategory'] = $overview['dueDateCategory'];
					$result[$month_year][$key][$i]['description'] = $overview['description'];
					$result[$month_year][$key][$i]['title'] = $overview['title'];
					$result[$month_year][$key][$i]['priority'] = $overview['priority'];
					$result[$month_year][$key][$i]['task_category_id'] = $overview['task_category_id'];
					$result[$month_year][$key][$i]['task_category_name'] = $overview['task_category_name'];
					$result[$month_year][$key][$i]['created'] = $overview['created'];
					$result[$month_year][$key][$i]['due_date'] = $overview['due_date'];
					$result[$month_year][$key][$i]['cd'] = $overview['cd'];
					$result[$month_year][$key][$i]['time_zone'] = $overview['time_zone'];
					$result[$month_year][$key][$i]['policy_number'] = $overview['policy_number'];
					$result[$month_year][$key][$i]['noteDate'] = '';
					$result[$month_year][$key][$i]['contacts'] = '';
					$result[$month_year][$key][$i]['note'] = '';
					$result[$month_year][$key][$i]['contact_notes_attachments'] = '';
					$result[$month_year][$key][$i]['user'] = '';
					$result[$month_year][$key][$i]['contact_communications_media'] = '';
					$result[$month_year][$key][$i]['subject'] = '';
					$result[$month_year][$key][$i]['message'] = '';
					$result[$month_year][$key][$i]['created_datetime'] = strtotime($overview['created']);
					$result[$month_year][$key][$i]['in_out'] = '';
                    $result[$month_year][$key][$i]['status'] = $overview['status'];
					$i++;	
				}
		
			}
			
		}
	
		// As per requirement in 12121 add status chcek in overview tab for notes
		$unpinnedNotes = $ContactNotesTable->find('all')->contain(['Users','ContactNoteTypes','Contacts','ContactNotesAttachments'])
		->where(['ContactNotes.contact_id' => $contactId, 'ContactNotes.status !=' => _ID_STATUS_DELETED, 'date(ContactNotes.created) <=' => $currentDate, 'date(ContactNotes.created) >=' => $lastMonthDate, 'ContactNotes.pinned' => _UNPIN])
		->select(['Users.first_name','Users.last_name','ContactNotes.agency_id','ContactNotes.id', 'ContactNotes.pinned','ContactNotes.note',"ContactNotes.created","noteDate"=>"DATE_FORMAT(ContactNotes.created,'%M %e,%Y')","noteTime"=>"DATE_FORMAT(ContactNotes.created,'%h:%i %p')","ContactNoteTypes.note_type","ContactNotes.opportunity_id"])->order(['ContactNotes.created' => 'DESC'])
		->hydrate(false)->toArray();

		$pinnedNotes = $ContactNotesTable->find('all')->contain(['Users','ContactNoteTypes','Contacts','ContactNotesAttachments'])
		->where(['ContactNotes.contact_id' => $contactId, 'ContactNotes.status !=' => _ID_STATUS_DELETED, 'ContactNotes.pinned' => _PIN])
		->select(['Users.first_name','Users.last_name','ContactNotes.agency_id','ContactNotes.id', 'ContactNotes.pinned','ContactNotes.note',"ContactNotes.created","noteDate"=>"DATE_FORMAT(ContactNotes.created,'%M %e,%Y')","noteTime"=>"DATE_FORMAT(ContactNotes.created,'%h:%i %p')","ContactNoteTypes.note_type","ContactNotes.opportunity_id"])->order(['ContactNotes.created' => 'DESC'])
		->hydrate(false)->toArray();

		$response['overviews'] = array_merge($unpinnedNotes, $pinnedNotes);
		$j = 0;	
		$result1 = array();
		foreach($response as $key=> $data)
		{
			foreach (array_unique(array_column($response[$key], 'created')) as $date) {
				foreach (array_filter($response[$key], function($v) use ($date) {
				    return strtotime($v['created']) == strtotime($date);
				}) as $overview) {
					$time_zone = CommonFunctions::getShortCodeTimeZone($login_agency_id);
					$overview['time_zone'] = $time_zone;
					$overview['policy_number'] = '';
					if(isset($overview['opportunity_id']) && !empty($overview['opportunity_id']))
					{
						$result2 = $ContactOppTable->find()->where(['id' =>$overview['opportunity_id']])
						->select(['policy_number','insurance_type_id'])->hydrate(false)->first();
						if(isset($result2['policy_number']) && $result2['policy_number'] != '')
						{

							$overview['policy_number'] = 'Policy #'.$result2['policy_number'].',';
						}
					}
					$month = date("F",strtotime($date));
					$year = date("Y",strtotime($date));
					$month_year = $month."-".$year;
					$agencyTime = date('h:i a',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($overview['created'])))));
					$dateMonth = date('M d, Y',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($overview['created'])))));
					$result1[$month_year][$key][$j]['agencyTime'] = $agencyTime;
					$result1[$month_year][$key][$j]['dateMonth'] = $dateMonth;					
					$result1[$month_year][$key][$j]['type'] = 'notes';
					$result1[$month_year][$key][$j]['id'] = $overview['id'];
					$result1[$month_year][$key][$j]['pinned'] = $overview['pinned'];
					$result1[$month_year][$key][$j]['user'] = $overview['user'];
					$result1[$month_year][$key][$j]['ud'] = '';
					$result1[$month_year][$key][$j]['dueDateCategory'] = '';
					$result1[$month_year][$key][$j]['description'] = '';
					$result1[$month_year][$key][$j]['title'] = $overview['title'];
					$result1[$month_year][$key][$j]['created'] = $overview['created'];
					$result1[$month_year][$key][$j]['due_date'] = '';
					$result1[$month_year][$key][$j]['noteDate'] = $overview['noteDate'];
					$result1[$month_year][$key][$j]['contacts'] = '';
					$result1[$month_year][$key][$j]['cd'] = '';
					$result1[$month_year][$key][$j]['time_zone'] = $overview['time_zone'];
					$result1[$month_year][$key][$j]['note'] = $overview['note'];
					$result1[$month_year][$key][$j]['contact_notes_attachments'] = $overview['contact_notes_attachments'];
					$result1[$month_year][$key][$j]['contact_communications_media'] = '';
					$result1[$month_year][$key][$j]['subject'] = '';
					$result1[$month_year][$key][$j]['message'] = '';
					$result1[$month_year][$key][$j]['created_datetime'] = strtotime($overview['created']);
					$result1[$month_year][$key][$j]['in_out'] = '';
					$result1[$month_year][$key][$j]['contact_note_type'] = $overview['contact_note_type'];
					$result1[$month_year][$key][$j]['policy_number'] = $overview['policy_number'];
					$j++;	
				}
		
			}
		}
		
		$response['overviews'] = $ContactCommunicationTable->find('all')
		->select([
                'ContactCommunications.id',
                'ContactCommunications.message',
                'ContactCommunications.mail_subject',
                'ContactCommunications.in_out',
                'ContactCommunications.communication_type',
                'ContactCommunications.sent_status',
                'ContactCommunications.sent_remark',
                'ContactCommunications.parent_communication_id',
                'ContactCommunications.created',
                'Contacts.first_name',
                'Contacts.last_name',
                'Users.first_name',
                'Users.last_name',
                'Users.headshot'
        ])
		->contain(['Users','ContactCommunicationsMedia','Contacts'])
		->where([
			'ContactCommunications.contact_id' => $contactId,
			'ContactCommunications.communication_type IN' => [1,2],
			'ContactCommunications.status !=' => _ID_STATUS_DELETED,
            'date(ContactCommunications.created) <=' => $currentDate,
            'date(ContactCommunications.created) >=' => $lastMonthDate])
		->order(['ContactCommunications.created' => 'DESC'])
		->hydrate(false)->toArray();

		$k = 0;
        $n = 0;
		$result2 = array();
        $result4 = array();

        $response['overviews'] = array_map(
            function($item) {
                $item['created'] = date_format($item['created'], 'Y-m-d H:i:s');
                return $item;
            },
        $response['overviews']);

		foreach($response as $key=> $data)
		{
			foreach (array_unique(array_column($response[$key], 'created')) as $date) {
				foreach (array_filter($response[$key], function($v) use ($date) {
				return $v['created'] == $date;
				}) as $overview)
                {
                    $time_zone = CommonFunctions::getShortCodeTimeZone($login_agency_id);
                    $overview['time_zone'] = $time_zone;
                    $month = date("F",strtotime($date));
                    $year = date("Y",strtotime($date));
                    $month_year = $month."-".$year;
                    $agencyTime = date('h:i a',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($overview['created'])))));
                    $dateMonth = date('M d, Y',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($overview['created'])))));
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
                    if($overview['communication_type'] == 1)
                    {
                       $result2[$month_year][$key][$k]['agencyTime'] = $agencyTime;
                        $result2[$month_year][$key][$k]['dateMonth'] = $dateMonth;
                        $atachment_count  = count($overview['contact_communications_media']);
                        $result2[$month_year][$key][$k]['type'] = 'email';
                        $result2[$month_year][$key][$k]['id'] = $overview['id'];
//					$result2[$month_year][$key][$k]['user'] = $overview['user'];
                        $result2[$month_year][$key][$k]['user']['first_name'] = $overview['user']['first_name'];
                        $result2[$month_year][$key][$k]['user']['last_name'] = $overview['user']['last_name'];
                        $result2[$month_year][$key][$k]['ud'] = '';
                        $result2[$month_year][$key][$k]['dueDateCategory'] = '';
                        $result2[$month_year][$key][$k]['description'] = '';
                        $result2[$month_year][$key][$k]['title'] = '';
                        $result2[$month_year][$key][$k]['created'] = $overview['created'];
                        $result2[$month_year][$key][$k]['due_date'] = '';
                        $result2[$month_year][$key][$k]['noteDate'] = '';
//					$result2[$month_year][$key][$k]['contacts'] = $overview['contact'];
                        $result2[$month_year][$key][$k]['contacts']['first_name'] = $overview['contact']['first_name'];
                        $result2[$month_year][$key][$k]['contacts']['last_name'] = $overview['contact']['last_name'];
                        $result2[$month_year][$key][$k]['cd'] = '';
                        $result2[$month_year][$key][$k]['time_zone'] = $overview['time_zone'];
                        $result2[$month_year][$key][$k]['note'] = '';
                        $result2[$month_year][$key][$k]['contact_notes_attachments'] = '';
                        $result2[$month_year][$key][$k]['contact_communications_media'] = $overview['contact_communications_media'];
                        $result2[$month_year][$key][$k]['subject'] = $overview['mail_subject'];
                        $result2[$month_year][$key][$k]['message'] = $overview['message'];
                        $result2[$month_year][$key][$k]['created_datetime'] = strtotime($overview['created']);
                        $result2[$month_year][$key][$k]['communication_id'] = '';
                        $result2[$month_year][$key][$k]['in_out'] = $overview['in_out'];
                        $result2[$month_year][$key][$k]['sent_status'] = $overview['sent_status'];
                        $result2[$month_year][$key][$k]['sent_remark'] = $overview['sent_remark'];
                        $result2[$month_year][$key][$k]['attachment_count'] = $atachment_count;
                        if(isset($overview['parent_communication_id']) && $overview['parent_communication_id'] != '')
                        {
                            $result2[$month_year][$key][$k]['parent_communication_id'] = $overview['parent_communication_id'];
                        }
                        else
                        {
                            $result2[$month_year][$key][$k]['parent_communication_id'] = '';
                        }
                        $k++;
                    }
                    else if($overview['communication_type'] == 2)
                    {
                        $time_zone = CommonFunctions::getShortCodeTimeZone($login_agency_id);
                        $overview['time_zone'] = $time_zone;
                        $month = date("F",strtotime($date));
                        $year = date("Y",strtotime($date));
                        $month_year = $month."-".$year;
                        $agencyTime = date('h:i a',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($overview['created'])))));
                        $dateMonth = date('M d, Y',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($overview['created'])))));
                        $result4[$month_year][$key][$n]['agencyTime'] = $agencyTime;
                        $result4[$month_year][$key][$n]['dateMonth'] = $dateMonth;
                        $atachment_count  = count($overview['contact_communications_media']);
                        $result4[$month_year][$key][$n]['type'] = 'sms';
                        $result4[$month_year][$key][$n]['id'] = $overview['id'];
    //					$result4[$month_year][$key][$n]['user'] = $overview['user'];
                        $result4[$month_year][$key][$n]['user']['first_name'] = $overview['user']['first_name'];
                        $result4[$month_year][$key][$n]['user']['last_name'] = $overview['user']['last_name'];
                        $result4[$month_year][$key][$n]['ud'] = '';
                        $result4[$month_year][$key][$n]['dueDateCategory'] = '';
                        $result4[$month_year][$key][$n]['description'] = '';
                        $result4[$month_year][$key][$n]['title'] = '';
                        $result4[$month_year][$key][$n]['created'] = $overview['created'];
                        $result4[$month_year][$key][$n]['due_date'] = '';
                        $result4[$month_year][$key][$n]['noteDate'] = '';
    //					$result4[$month_year][$key][$n]['contacts'] = $overview['contact'];
                        $result4[$month_year][$key][$n]['contacts']['first_name'] = $overview['contact']['first_name'];
                        $result4[$month_year][$key][$n]['contacts']['last_name'] = $overview['contact']['last_name'];
                        $result4[$month_year][$key][$n]['cd'] = '';
                        $result4[$month_year][$key][$n]['time_zone'] = $overview['time_zone'];
                        $result4[$month_year][$key][$n]['note'] = '';
                        $result4[$month_year][$key][$n]['contact_notes_attachments'] = '';
                        $result4[$month_year][$key][$n]['contact_communications_media'] = $overview['contact_communications_media'];
                        $result4[$month_year][$key][$n]['subject'] = $overview['mail_subject'];
                        $result4[$month_year][$key][$n]['message'] = $overview['message'];
                        $result4[$month_year][$key][$n]['created_datetime'] = strtotime($overview['created']);
                        $result4[$month_year][$key][$n]['communication_id'] = '';
                        $result4[$month_year][$key][$n]['in_out'] = $overview['in_out'];
                        $result4[$month_year][$key][$n]['sent_status'] = $overview['sent_status'];
                        $result4[$month_year][$key][$n]['attachment_count'] = $atachment_count;
                        $n++;


                    }
				}

			}

		}
		$newArray = array_merge_recursive($result,$result1,$result2,$result4);
		uksort($newArray, function($a1, $a2) {
		$time1 = strtotime($a1);
		$time2 = strtotime($a2);

		return $time2 - $time1;
		});
		foreach($newArray as $key=>$value)
		{
			$sArray = $value['overviews'];
			array_multisort(array_column($sArray, 'created_datetime'), SORT_DESC, $sArray);
			$newArray[$key]['overviews'] = $sArray;
            $newArray[$key]['previousDate'] = $lastMonthDate;
        }

		$return['ContactOverviews.getContactOverview'] = [
            $contactId => $newArray
        ];
		return $return;

	}


public function getContactOverviewNext($contactIdNext, $fields){

		$result = [];
		$response = [];
		$TaskTable = TableRegistry::getTableLocator()->get('Tasks');
		$ContactNotesTable = TableRegistry::getTableLocator()->get('ContactNotes');
		$ContactCommunicationTable = TableRegistry::getTableLocator()->get('ContactCommunications');
		$UsStates = TableRegistry::getTableLocator()->get('UsStates');
		$session = Router::getRequest()->getSession();
		$login_agency_id = $session->read('Auth.User.agency_id');
        $login_user_id= $session->read("Auth.User.user_id");
		// $contactCommunicationsMedia = TableRegistry::getTableLocator()->get('ContactCommunicationsMedia');
		$Agency = TableRegistry::getTableLocator()->get('Agency');
		// $agencyDetail = $Agency->agencyDetails($login_agency_id);
		$ContactOppTable = TableRegistry::getTableLocator()->get('ContactOpportunities');
		// $usersTimezone='America/Phoenix';
		// $stateDetail = $UsStates->stateDetail($agencyDetail['us_state_id']);
		// if(isset($agencyDetail['time_zone']) && !empty($agencyDetail['time_zone']))
		// {
		// 	$usersTimezone =  $agencyDetail['time_zone'];
		// }
		// else if(isset($stateDetail) && !empty($stateDetail))
		// {
		//    $usersTimezone =  $stateDetail->time_zone;
		// }

		$usersTimezone = 'America/Phoenix';
          $agency_time_zone = $session->read('Auth.User.agency_session_detail.time_zone');
          if(isset($agency_time_zone) && $agency_time_zone != ''){
             $usersTimezone = $agency_time_zone;
          }else {
            $agency_state_id = $session->read('Auth.User.agency_session_detail.us_state_id');
            if (isset($agency_state_id) && $agency_state_id != '') {
               $stateDetail = $UsStates->stateDetail($agency_state_id);
                    if (isset($stateDetail) && !empty($stateDetail)) {
                        $usersTimezone = $stateDetail->time_zone;
                    }
                }
            }

		$contactId = explode("_", $contactIdNext)[0];
		$currentDate = date('Y-m-d');
        $lastMonthDate = date('Y-m-d', strtotime("-6 months", strtotime($currentDate)));

		$response['overviews'] = $TaskTable->find()->where(['contact_id' => $contactId, 'date(Tasks.created) <=' => $currentDate, 'date(Tasks.created) >=' => $lastMonthDate])
		->select(['ud.first_name','ud.last_name','cd.first_name','cd.last_name','user_id','title','tp.opportunity_id','priority','task_category_id','tc.name','Tasks.id','Tasks.agency_id','Tasks.status','Tasks.created','Tasks.due_date','description',"dueDateCategory"=>"DATE_FORMAT(Tasks.due_date,'%M %e,%Y')","dueTimeCategory"=>"DATE_FORMAT(Tasks.due_date,'%h:%i %p')"])->where(['Tasks.status !=' => 3])
		->join([
            "table" => "users",
            "alias" => "ud",
            "type" => "INNER",
            "conditions" => "Tasks.user_id = ud.id"
        ])
		->join([
            "table" => "contacts",
            "alias" => "cd",
            "type" => "INNER",
            "conditions" => "Tasks.contact_id = cd.id"
        ])->join([
			"table" => "task_policies_link",
			"alias" => "tp",
			"type" => "LEFT",
			"conditions" => "Tasks.id = tp.task_id"
		])->join([
			"table" => "task_categories",
			"alias" => "tc",
			"type" => "LEFT",
			"conditions" => "Tasks.task_category_id = tc.id"
		])->order(['Tasks.created' => 'DESC'])
		->hydrate(false)->toArray();

		$i = 0;
		foreach($response as $key=> $data)
		{
			foreach (array_unique(array_column($response[$key], 'created')) as $date) {
				foreach (array_filter($response[$key], function($v) use ($date) {
				return $v['created'] == $date;
				}) as $overview) {
					$time_zone = CommonFunctions::getShortCodeTimeZone($login_agency_id);
					$overview['time_zone'] = $time_zone;
					$month = date("F",strtotime($date));
					$year = date("Y",strtotime($date));
					$month_year = $month."-".$year;
					$agencyTime = date('h:i a',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($overview['created'])))));
					$dateMonth = date('M d, Y',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d',strtotime($overview['created'])))));
					if(isset($overview['tp']['opportunity_id']) && !empty($overview['tp']['opportunity_id']))
					{
						$result2 = $ContactOppTable->find()->where(['id' =>$overview['tp']['opportunity_id']])
						->select(['policy_number','insurance_type_id'])->hydrate(false)->first();
						if(isset($result2['policy_number']) && $result2['policy_number'] != '')
						{

							$overview['policy_number'] = 'Policy #'.$result2['policy_number'].',';
						}
					}else{
						$overview['policy_number'] = '';
					}
					if(isset($overview['tc']['name']) && !empty($overview['tc']['name']))
					{
						$overview['task_category_name'] = $overview['tc']['name'];
					}else{
						$overview['task_category_name'] = '';
					}
					$result[$month_year][$key][$i]['agencyTime'] = $agencyTime;
					$result[$month_year][$key][$i]['dateMonth'] = $dateMonth;
					$result[$month_year][$key][$i]['type'] = 'task';
					$result[$month_year][$key][$i]['id'] = $overview['id'];
					$result[$month_year][$key][$i]['ud'] = $overview['ud'];
					$result[$month_year][$key][$i]['dueDateCategory'] = $overview['dueDateCategory'];
					$result[$month_year][$key][$i]['description'] = $overview['description'];
					$result[$month_year][$key][$i]['title'] = $overview['title'];
					$result[$month_year][$key][$i]['priority'] = $overview['priority'];
					$result[$month_year][$key][$i]['task_category_id'] = $overview['task_category_id'];
					$result[$month_year][$key][$i]['task_category_name'] = $overview['task_category_name'];
					$result[$month_year][$key][$i]['created'] = $overview['created'];
					$result[$month_year][$key][$i]['due_date'] = $overview['due_date'];
					$result[$month_year][$key][$i]['cd'] = $overview['cd'];
					$result[$month_year][$key][$i]['time_zone'] = $overview['time_zone'];
					$result[$month_year][$key][$i]['policy_number'] = $overview['policy_number'];
					$result[$month_year][$key][$i]['noteDate'] = '';
					$result[$month_year][$key][$i]['contacts'] = '';
					$result[$month_year][$key][$i]['note'] = '';
					$result[$month_year][$key][$i]['contact_notes_attachments'] = '';
					$result[$month_year][$key][$i]['user'] = '';
					$result[$month_year][$key][$i]['contact_communications_media'] = '';
					$result[$month_year][$key][$i]['subject'] = '';
					$result[$month_year][$key][$i]['message'] = '';
					$result[$month_year][$key][$i]['created_datetime'] = strtotime($overview['created']);
					$result[$month_year][$key][$i]['in_out'] = '';
                    $result[$month_year][$key][$i]['status'] = $overview['status'];
					$i++;
				}

			}

		}


		$unpinnedNotes = $ContactNotesTable->find('all')->contain(['Users','ContactNoteTypes','Contacts','ContactNotesAttachments'])
		->where(['ContactNotes.contact_id' => $contactId, 'ContactNotes.status !=' => _ID_STATUS_DELETED, 'date(ContactNotes.created) <=' => $currentDate, 'date(ContactNotes.created) >=' => $lastMonthDate, 'ContactNotes.pinned' => _UNPIN])
		->select(['Users.first_name','Users.last_name','ContactNotes.agency_id','ContactNotes.id', 'ContactNotes.pinned','ContactNotes.note',"ContactNotes.created","noteDate"=>"DATE_FORMAT(ContactNotes.created,'%M %e,%Y')","noteTime"=>"DATE_FORMAT(ContactNotes.created,'%h:%i %p')","ContactNoteTypes.note_type","ContactNotes.opportunity_id"])->order(['ContactNotes.created' => 'DESC'])
		->hydrate(false)->toArray();

		$pinnedNotes = $ContactNotesTable->find('all')->contain(['Users','ContactNoteTypes','Contacts','ContactNotesAttachments'])
		->where(['ContactNotes.contact_id' => $contactId, 'ContactNotes.status !=' => _ID_STATUS_DELETED, 'ContactNotes.pinned' => _PIN])
		->select(['Users.first_name','Users.last_name','ContactNotes.agency_id','ContactNotes.id', 'ContactNotes.pinned','ContactNotes.note',"ContactNotes.created","noteDate"=>"DATE_FORMAT(ContactNotes.created,'%M %e,%Y')","noteTime"=>"DATE_FORMAT(ContactNotes.created,'%h:%i %p')","ContactNoteTypes.note_type","ContactNotes.opportunity_id"])->order(['ContactNotes.created' => 'DESC'])
		->hydrate(false)->toArray();

		$response['overviews'] = array_merge($unpinnedNotes, $pinnedNotes);
		$j = 0;
		$result1 = array();
		foreach($response as $key=> $data)
		{

			foreach (array_unique(array_column($response[$key], 'noteDate')) as $date) {
				foreach (array_filter($response[$key], function($v) use ($date) {
				return $v['noteDate'] == $date;
				}) as $overview) {
					$time_zone = CommonFunctions::getShortCodeTimeZone($login_agency_id);
					$overview['time_zone'] = $time_zone;
					$overview['policy_number'] = '';
					if(isset($overview['opportunity_id']) && !empty($overview['opportunity_id']))
					{
						$result2 = $ContactOppTable->find()->where(['id' =>$overview['opportunity_id']])
						->select(['policy_number','insurance_type_id'])->hydrate(false)->first();
						if(isset($result2['policy_number']) && $result2['policy_number'] != '')
						{

							$overview['policy_number'] = 'Policy #'.$result2['policy_number'].',';
						}
					}
					$month = date("F",strtotime($date));
					$year = date("Y",strtotime($date));
					$month_year = $month."-".$year;
					$agencyTime = date('h:i a',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($overview['created'])))));
					$dateMonth = date('M d, Y',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($overview['created'])))));
					$result1[$month_year][$key][$j]['agencyTime'] = $agencyTime;
					$result1[$month_year][$key][$j]['dateMonth'] = $dateMonth;
					$result1[$month_year][$key][$j]['type'] = 'notes';
					$result1[$month_year][$key][$j]['id'] = $overview['id'];
					$result1[$month_year][$key][$j]['pinned'] = $overview['pinned'];
					$result1[$month_year][$key][$j]['user'] = $overview['user'];
					$result1[$month_year][$key][$j]['ud'] = '';
					$result1[$month_year][$key][$j]['dueDateCategory'] = '';
					$result1[$month_year][$key][$j]['description'] = '';
					$result1[$month_year][$key][$j]['title'] = $overview['title'];
					$result1[$month_year][$key][$j]['created'] = $overview['created'];
					$result1[$month_year][$key][$j]['due_date'] = '';
					$result1[$month_year][$key][$j]['noteDate'] = $overview['noteDate'];
					$result1[$month_year][$key][$j]['contacts'] = '';
					$result1[$month_year][$key][$j]['cd'] = '';
					$result1[$month_year][$key][$j]['time_zone'] = $overview['time_zone'];
					$result1[$month_year][$key][$j]['note'] = $overview['note'];
					$result1[$month_year][$key][$j]['contact_notes_attachments'] = $overview['contact_notes_attachments'];
					$result1[$month_year][$key][$j]['contact_communications_media'] = '';
					$result1[$month_year][$key][$j]['subject'] = '';
					$result1[$month_year][$key][$j]['message'] = '';
					$result1[$month_year][$key][$j]['created_datetime'] = strtotime($overview['created']);
					$result1[$month_year][$key][$j]['in_out'] = '';
					$result1[$month_year][$key][$j]['contact_note_type'] = $overview['contact_note_type'];
					$result1[$month_year][$key][$j]['policy_number'] = $overview['policy_number'];
					$j++;
				}

			}

		}

		$response['overviews'] = $ContactCommunicationTable->find('all')
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
		->contain(['Users','ContactCommunicationsMedia','Contacts'])
		->where(['ContactCommunications.contact_id' => $contactId,
		        'ContactCommunications.communication_type' => 1, 'date(ContactCommunications.created) <=' => $currentDate, 'date(ContactCommunications.created) >=' => $lastMonthDate])
		->order(['ContactCommunications.created' => 'DESC'])
		->hydrate(false)->toArray();


		$k = 0;
		$result2 = array();
		foreach($response as $key=> $data)
		{
			foreach (array_unique(array_column($response[$key], 'created')) as $date) {
				foreach (array_filter($response[$key], function($v) use ($date) {
				return $v['created'] == $date;
				}) as $overview) {
					$time_zone = CommonFunctions::getShortCodeTimeZone($login_agency_id);
					$overview['time_zone'] = $time_zone;
					$month = date("F",strtotime($date));
					$year = date("Y",strtotime($date));
					$month_year = $month."-".$year;
					$agencyTime = date('h:i a',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($overview['created'])))));
					$dateMonth = date('M d, Y',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($overview['created'])))));
					$result2[$month_year][$key][$k]['agencyTime'] = $agencyTime;
					$result2[$month_year][$key][$k]['dateMonth'] = $dateMonth;
					$atachment_count  = count($overview['contact_communications_media']);
					$result2[$month_year][$key][$k]['type'] = 'email';
					$result2[$month_year][$key][$k]['id'] = $overview['id'];
					//$result2[$month_year][$key][$k]['user'] = $overview['user'];
					$result2[$month_year][$key][$k]['user']['first_name'] = $overview['user']['first_name'];
					$result2[$month_year][$key][$k]['user']['last_name'] = $overview['user']['last_name'];
					$result2[$month_year][$key][$k]['ud'] = '';
					$result2[$month_year][$key][$k]['dueDateCategory'] = '';
					$result2[$month_year][$key][$k]['description'] = '';
					$result2[$month_year][$key][$k]['title'] = '';
					$result2[$month_year][$key][$k]['created'] = $overview['created'];
					$result2[$month_year][$key][$k]['due_date'] = '';
					$result2[$month_year][$key][$k]['noteDate'] = '';
					$result2[$month_year][$key][$k]['contacts'] = $overview['contact'];
					$result2[$month_year][$key][$k]['contacts']['first_name'] = $overview['contact']['first_name'];
					$result2[$month_year][$key][$k]['contacts']['last_name'] = $overview['contact']['last_name'];
					$result2[$month_year][$key][$k]['cd'] = '';
					$result2[$month_year][$key][$k]['time_zone'] = $overview['time_zone'];
					$result2[$month_year][$key][$k]['note'] = '';
					$result2[$month_year][$key][$k]['contact_notes_attachments'] = '';
					$result2[$month_year][$key][$k]['contact_communications_media'] = $overview['contact_communications_media'];
					$result2[$month_year][$key][$k]['subject'] = $overview['mail_subject'];
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
					$result2[$month_year][$key][$k]['message'] = $overview['message'];
					$result2[$month_year][$key][$k]['created_datetime'] = strtotime($overview['created']);
					$result2[$month_year][$key][$k]['communication_id'] = '';
					$result2[$month_year][$key][$k]['in_out'] = $overview['in_out'];
					$result2[$month_year][$key][$k]['attachment_count'] = $atachment_count;
                    if(isset($overview['parent_communication_id']) && $overview['parent_communication_id'] != '')
                    {
                        $result2[$month_year][$key][$k]['parent_communication_id'] = $overview['parent_communication_id'];
                    }
                    else
                    {
                        $result2[$month_year][$key][$k]['parent_communication_id'] = '';
                    }

					$k++;
				}

			}

		}

		$response['overviews'] = $ContactCommunicationTable->find('all')
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
		->contain(['Users','ContactCommunicationsMedia','Contacts'])
		->where(['ContactCommunications.contact_id' => $contactId,
		        'ContactCommunications.communication_type'=>2, 'date(ContactCommunications.created) <=' => $currentDate, 'date(ContactCommunications.created) >=' => $lastMonthDate])
		->order(['ContactCommunications.created' => 'DESC'])
		->hydrate(false)->toArray();


		$n = 0;
		$result4 = array();
		foreach($response as $key=> $data)
		{
			foreach (array_unique(array_column($response[$key], 'created')) as $date) {
				foreach (array_filter($response[$key], function($v) use ($date) {
				return $v['created'] == $date;
				}) as $overview) {
					$time_zone = CommonFunctions::getShortCodeTimeZone($login_agency_id);
					$overview['time_zone'] = $time_zone;
					$month = date("F",strtotime($date));
					$year = date("Y",strtotime($date));
					$month_year = $month."-".$year;
					$agencyTime = date('h:i a',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($overview['created'])))));
					$dateMonth = date('M d, Y',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($overview['created'])))));
					$result4[$month_year][$key][$n]['agencyTime'] = $agencyTime;
					$result4[$month_year][$key][$n]['dateMonth'] = $dateMonth;
					$atachment_count  = count($overview['contact_communications_media']);
					$result4[$month_year][$key][$n]['type'] = 'sms';
					$result4[$month_year][$key][$n]['id'] = $overview['id'];
//					$result4[$month_year][$key][$n]['user'] = $overview['user'];
					$result4[$month_year][$key][$n]['user']['first_name'] = $overview['user']['first_name'];
					$result4[$month_year][$key][$n]['user']['last_name'] = $overview['user']['last_name'];
					$result4[$month_year][$key][$n]['ud'] = '';
					$result4[$month_year][$key][$n]['dueDateCategory'] = '';
					$result4[$month_year][$key][$n]['description'] = '';
					$result4[$month_year][$key][$n]['title'] = '';
					$result4[$month_year][$key][$n]['created'] = $overview['created'];
					$result4[$month_year][$key][$n]['due_date'] = '';
					$result4[$month_year][$key][$n]['noteDate'] = '';
//					$result4[$month_year][$key][$n]['contacts'] = $overview['contact'];
					$result4[$month_year][$key][$n]['contacts']['first_name'] = $overview['contact']['first_name'];
					$result4[$month_year][$key][$n]['contacts']['last_name'] = $overview['contact']['last_name'];
					$result4[$month_year][$key][$n]['cd'] = '';
					$result4[$month_year][$key][$n]['time_zone'] = $overview['time_zone'];
					$result4[$month_year][$key][$n]['note'] = '';
					$result4[$month_year][$key][$n]['contact_notes_attachments'] = '';
					$result4[$month_year][$key][$n]['contact_communications_media'] = $overview['contact_communications_media'];
					$result4[$month_year][$key][$n]['subject'] = $overview['mail_subject'];
					if (stripos($overview['message'], '{agency.proposalLink}') !== false)
					{
						if(isset($video_proposal_link)  && !empty($video_proposal_link) ){
							$overview['message'] = str_ireplace('{agency.proposalLink}', $video_proposal_link, $overview['message']);
						}else{
							$overview['message'] = str_ireplace('{agency.proposalLink}', '', $overview['message']);
						}

					}

					$result4[$month_year][$key][$n]['message'] = $overview['message'];
					$result4[$month_year][$key][$n]['created_datetime'] = strtotime($overview['created']);
					$result4[$month_year][$key][$n]['communication_id'] = '';
					$result4[$month_year][$key][$n]['in_out'] = $overview['in_out'];
					$result4[$month_year][$key][$n]['attachment_count'] = $atachment_count;

					$n++;
				}

			}

		}
		$newArray = array_merge_recursive($result,$result1,$result2,$result4);

		foreach ($newArray as $month => &$monthData) {
			if (isset($monthData['overviews']) && is_array($monthData['overviews'])) {
				$createdDatetime = array_column($monthData['overviews'], 'created_datetime');
				array_multisort($createdDatetime, SORT_DESC, $monthData['overviews']);
			}
		}

		uksort($newArray, function($a1, $a2) {
		$time1 = strtotime($a1);
		$time2 = strtotime($a2);

		return $time2 - $time1;
		});
		foreach($newArray as $key=>$value)
		{
			$sArray = $value['overviews'];
			array_multisort(array_column($sArray, 'created_datetime'), SORT_DESC, $sArray);
			$newArray[$key]['overviews'] = $sArray;
		}

		$return['ContactOverviews.getContactOverviewNext'] = [
            $contactIdNext => $newArray
        ];

		return $return;

	}

}
