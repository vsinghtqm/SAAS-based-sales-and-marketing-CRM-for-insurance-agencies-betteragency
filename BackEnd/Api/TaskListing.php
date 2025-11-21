<?php

namespace App\Lib\ApiProviders;

use App\Classes\FileLog;
use Cake\Http\Session;
use App\Model\Entity\Task;
use App\Model\Entity\User;
use Cake\ORM\TableRegistry;
use App\Lib\PermissionsCheck;
use App\Model\Entity\ContactNoteTypes;
use Cake\ORM\Locator\LocatorAwareTrait;
use App\Lib\QuickTables\ContactsQuickTable;
use Cake\Http\Exception\UnauthorizedException;
use App\Classes\CommonFunctions;
use Cake\Routing\Router;
use App\Classes\SystemNotification;
use DOMDocument;

class TaskListing
{


	public static function getTaskListing($contactId, $fields=null)
	{
		try
		{
			$myfile = fopen(ROOT."/logs/vuetasklisting.log", "a") or die("Unable to open file!");
			$session = Router::getRequest()->getSession(); 
			$login_agency_id = $session->read('Auth.User.agency_id');
			$Agency = TableRegistry::getTableLocator()->get('Agency');
			$UsStates = TableRegistry::getTableLocator()->get('UsStates');
			$agencyDetail = $Agency->agencyDetails($login_agency_id);
			$usersTimezone='America/Phoenix';
			$stateDetail = $UsStates->stateDetail($agencyDetail['us_state_id']);
			if(isset($agencyDetail['time_zone']) && !empty($agencyDetail['time_zone']))
			{
				$usersTimezone =  $agencyDetail['time_zone'];
			}
			else if(isset($stateDetail) && !empty($stateDetail))
			{
			   $usersTimezone =  $stateDetail->time_zone;
			}
			$ContactOppTable = TableRegistry::getTableLocator()->get('ContactOpportunities');
			$ContactNoteTypeTable = TableRegistry::getTableLocator()->get('Tasks');
		
			$results = $ContactNoteTypeTable->find()->where(['contact_id' => $contactId,'Tasks.status'=>2])
			->select(['a.first_name','a.last_name','ac.first_name','ac.last_name','user_id','title','tp.opportunity_id','priority','task_category_id','tc.name','Tasks.agency_id','Tasks.due_date','description','Tasks.id',"Tasks.created","dueDateCategory"=>"DATE_FORMAT(Tasks.due_date,'%b %d,%Y')","dueTimeCategory"=>"DATE_FORMAT(Tasks.due_date,'%h:%i %p')"])
			->join([ 
				"table" => "users",
				"alias" => "a",
				"type" => "INNER",
				"conditions" => "Tasks.user_id = a.id"
			])
			->join([
				"table" => "contacts",
				"alias" => "ac",
				"type" => "INNER",
				"conditions" => "Tasks.contact_id = ac.id"
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
			])->contain(['TaskNotes'])->order(['Tasks.id' => 'DESC'])
			->hydrate(false)->toArray();
			foreach($results as $key=>$value)
			{
				$time_zone = CommonFunctions::getShortCodeTimeZone($value['agency_id']);
				$agencyTime = date('h:i a',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($value['created'])))));
				$dateMonth = date('M d, Y',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d',strtotime($value['created'])))));
				$value['due_date_time'] = $agencyTime;
				$value['due_date_category'] = $dateMonth;
				$value['time_zone'] = $time_zone;
                $value['message_count'] = count($value['task_notes']);
				$result[] = $value;
				if(isset($value['tp']['opportunity_id']) && !empty($value['tp']['opportunity_id']))
				{
					$result2 = $ContactOppTable->find()->where(['id' =>$value['tp']['opportunity_id']])
					->select(['policy_number','insurance_type_id'])->hydrate(false)->first();
					if(isset($result2['policy_number']) && $result2['policy_number'] != '')
					{

						$result[$key]['policy_number'] = 'Policy #'.$result2['policy_number'];
					}
				}else 
				{
					$result[$key]['policy_number'] = '';
				}	
			}
			 
			$return['TaskListing.getTaskListing'] = [
					$contactId => $result
			];
			return $return;
		}catch (\Exception $e) {
			
            $txt=date('Y-m-d H:i:s').' :: taskLising Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }

	}

	public function sortList($contactId)
	{
		$data = [ "snooze" => getEntityset(_SNOOZE_TIME) ];
		$return['TaskListing.sortList'] = [
			$contactId => $data
		];
		return $return;
	}

	public function getPriorityList($contactId)
	{
		$data = [ "priority" => getEntityset(_TASK_PRIORITY) ];
		$return['TaskListing.getPriorityList'] = [
			$contactId => $data
		];
		return $return;
	}

	public static function getTaskCategory($contactId)
	{
		$result = [];
		$taskCategory = TableRegistry::getTableLocator()->get('TaskCategories');
		$results = $taskCategory->allActiveTaskCategoriesList();
		$resultcount = count($results);
		for($i=0;$i<$resultcount;$i++)
		{ 
			$result[$i]['id'] = $results[$i]['id'];
			$result[$i]['name'] = $results[$i]['name'];
		}
		$return['TaskListing.getTaskCategory'] = [
			$contactId => $result
		];
        return $return;
		


	}
 
	public function getOwnerList($contactId)
	{
		$taskCategory = TableRegistry::getTableLocator()->get('Users');
		$result = $taskCategory->find()->select(['id','first_name','last_name'])
		->order(['id' => 'ASC'])
		->hydrate(false)->toArray();
		
		$return['TaskListing.getOwnerList'] = [
			$contactId => $result
		];
        return $return;
	}

	public function getAttachListing($contactId)
	{
		$attachList = TableRegistry::getTableLocator()->get('TaskAttachments');
		$result = $attachList->find()
					->where(['task_id'=>$contactId])
					->select(['name','a.first_name','a.last_name','created'])
					->join([
						"table" => "Users",
						"alias" => "a",
						"type" => "INNER",
						"conditions" => "TaskAttachments.user_id = a.id"
					])
					->hydrate(false)->toArray();
		$return['TaskListing.getAttachListing'] = [
			$contactId => $result
		];
		return $return;

	}

	public function getTaskAttachments($task_id)
	{
		try
		{
			$myfile = fopen(ROOT."/logs/vuetaskattachmentlisting.log", "a") or die("Unable to open file!");
			$attachList = TableRegistry::getTableLocator()->get('TaskAttachments');
			$tasks = TableRegistry::getTableLocator()->get('Tasks');
			$contacts = TableRegistry::getTableLocator()->get('Contacts');
			$taskAttachments ='';
			$taskDetails ='';
			$contactDetails='';

			if(isset($task_id) && !empty($task_id)){
				$taskAttachments = $attachList->getAttachmentDetail($task_id);
				
				$taskDetails = $tasks->get($task_id);
				if(isset($taskDetails->contact_id) && !empty($taskDetails->contact_id))
				{
					$contactDetails = $contacts->get($taskDetails->contact_id);
				}
			}
			
			$return['TaskListing.getTaskAttachments'] = [
				$task_id => json_encode(array('status' => _ID_SUCCESS, 'taskAttachments' => $taskAttachments,'taskDetails'=>$taskDetails,'contactDetails'=>$contactDetails ))
			];
			return $return;
		}catch (\Exception $e) {
			
            $txt=date('Y-m-d H:i:s').' :: Task Attchment Lising Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
		

	}

	public function getTemplateListing($contactId)
	{
		$attachList = TableRegistry::getTableLocator()->get('AgencyOneOffTemplates');
		$result = $attachList->getAgencyOneOffTemplatesByType(97,1);

		$return['TaskListing.getTemplateListing'] = [
			$contactId => $result
		];
		return $return;

	}

	public function getEmails($contactId)
	{
		$emailList = TableRegistry::getTableLocator()->get('ContactEmails');
		$result = $emailList->getAllEmailsByContact($contactId);

		$return['TaskListing.getEmails'] = [
			$contactId => $result
		];
		return $return;
	}

	public static function getSingleTask($taskId)
	{
		$session = Router::getRequest()->getSession(); 
		$login_agency_id = $session->read("Auth.User.agency_id");
		$Agency = TableRegistry::getTableLocator()->get('Agency');
		$UsStates = TableRegistry::getTableLocator()->get('UsStates');
		$agencyDetail = $Agency->agencyDetails($login_agency_id);
		$usersTimezone='America/Phoenix';
		$stateDetail = $UsStates->stateDetail($agencyDetail['us_state_id']);
		if(isset($agencyDetail['time_zone']) && !empty($agencyDetail['time_zone']))
		{
			$usersTimezone =  $agencyDetail['time_zone'];
		}
		else if(isset($stateDetail) && !empty($stateDetail))
		{
			$usersTimezone =  $stateDetail->time_zone;
		}
		$Tasks = TableRegistry::getTableLocator()->get('Tasks');
		$TaskPoliciesLink = TableRegistry::getTableLocator()->get('TaskPoliciesLink');
		$result = $Tasks->taskDetails($taskId);
		$linkPolicy = $TaskPoliciesLink->find('all')->where(['task_id'=>$taskId,'agency_id'=>$login_agency_id])->order(['id'=>'DESC'])->first();
		$time_zone = CommonFunctions::getShortCodeTimeZone($result['agency_id']);
		$agencyTime = date("h:i a",strtotime($result['due_date']));
		$dateMonth = date('M d, Y',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($result['due_date'])))));
		$result['agencyTime'] = $agencyTime;
		$result['dateMonth'] = $dateMonth;
		$result['time_zone'] = $time_zone;
		$result['task_description'] = $result['description'];
		$result['link_policy_id'] = $linkPolicy['opportunity_id'];

		// $html = $result['description'];
		//dd($html);
        // $dom = new \DOMDocument();
        // $dom->loadHTML($html, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
		// $xpath = new \DOMXPath($dom);
		// $elements = $xpath->query('//ul[@class="checklist_span"]/..');
		
		// //$taskDescriptionsUlLists = $dom->getElementsByTagName('ul');
		// $taskDescriptionsLi = $dom->getElementsByTagName('li');
		// $checkListHtml ='';
		
		// if(count($taskDescriptionsLi) >0 )
		// {
			
		// 	foreach($taskDescriptionsLi as $key1=> $taskDescription)
		// 	{
		// 			$liClass = $taskDescription->getAttribute('class');
					
		// 				if($liClass == 'checklist_inactive')
		// 				{
							
		// 					$domElement =  $dom->createElement('ul');
		// 					$domAttribute = $dom->createAttribute('data-checked');
		// 					$domAttribute->value = 'false';
		// 					$domElement->appendChild($domAttribute);
						
		// 				}
		// 				else if($liClass == 'checklist_active')
		// 				{
							
		// 					$domElement =  $dom->createElement('ul');
		// 					$domAttribute = $dom->createAttribute('data-checked');
		// 					$domAttribute->value = 'true';
		// 					$domElement->appendChild($domAttribute);
						
		// 				} 
					
		// 				$checkListHtml = $dom->saveHTML();
						
		// 	}
		// }
		$return['TaskListing.getSingleTask'] = [
			$taskId => $result
		];
		return $return;
		
	}

	public static function getPolicesNoteType($contact_id)
	{
		$session = Router::getRequest()->getSession(); 
		$login_agency_id = $session->read("Auth.User.agency_id");
		$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
		$Contacts = TableRegistry::getTableLocator()->get('Contacts');
		$ContactsAdditionalInsured = TableRegistry::getTableLocator()->get('ContactAdditionalInsuredPolicyRelation');
		// get contact details 
		$contact = $Contacts->contactDetails($contact_id);
		if($contact['additional_insured_flag'] == _ID_STATUS_ACTIVE)
		{
			$mappedPolicies = $ContactsAdditionalInsured->getMappedPolicyWithPrimaryContact($contact_id);
			if(count($mappedPolicies) > 0)
			{
				$primaryId = $mappedPolicies[0]['contact_id'];
				$contatOpportunityIds = array_column($mappedPolicies,'contact_opportunity_id');
				$getPolicyListing = $ContactOpportunities->getMultiPolicyListing($primaryId,'',$contatOpportunityIds);
			}
		}
		else
		{
			//all opportunities get
			$getPolicyListing = $ContactOpportunities->getMultiPolicyListing($contact_id);
		}
		
		$get_policy_types_for_notes_listing = [];
		if (isset($getPolicyListing) && !empty($getPolicyListing)) {
			$insurance_type_name ='';
			foreach ($getPolicyListing as $key => $value) {

				if (!empty($value['insurance_type']['type'])) {
					$insurance_type_name = $value['insurance_type']['type'];
				}
				$policy_number_note_list = !empty($value['policy_number']) ? " - ". $value['policy_number'] : '';
				$opportunity_id_note_list = !empty($value['id']) ?  $value['id'] : '';
                if(isset($policy_number_note_list) && $policy_number_note_list != '')
				{

					$policy_id_number = $insurance_type_name . $policy_number_note_list;
				}
				else
				{
					$policy_id_number = $insurance_type_name;
				}

				if(!empty($opportunity_id_note_list))
				{
					
					$get_policy_types_for_notes_listing[] = [
						"id" => $opportunity_id_note_list,
						"name" => $policy_id_number,
						"policy_number" =>  $policy_number_note_list,
						"policy_type" => $insurance_type_name
					];
				}

			}
		}
		
		$return['TaskListing.getPolicesNoteType'] = [
			$contact_id => $get_policy_types_for_notes_listing
		];
		return $return;
	}

	public static function addTask($objectData)
	{
		
		$session = Router::getRequest()->getSession(); 
		$login_user_id = $session->read('Auth.User.user_id');
        $login_agency_id = $session->read('Auth.User.agency_id');
        $login_role_type = $session->read('Auth.User.role_type');
        $login_role_type_flag = $session->read('Auth.User.role_type_flag');
        $login_first_name = $session->read('Auth.User.first_name');
        $login_last_name = $session->read('Auth.User.last_name');
        $login_permissions = $session->read('Auth.User.permissions');
        $login_permissions_arr = explode(",",$login_permissions);
		$Tasks = TableRegistry::getTableLocator()->get('Tasks');
		$TaskChecklists = TableRegistry::getTableLocator()->get('TaskChecklists');
		$TaskPoliciesLink = TableRegistry::getTableLocator()->get('TaskPoliciesLink');
		$Contacts = TableRegistry::getTableLocator()->get('Contacts');
		$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
		$TaskCategories = TableRegistry::getTableLocator()->get('TaskCategories');
		$Users = TableRegistry::getTableLocator()->get('Users');
        $id = $objectData['task_id_n'];
        $task = $Tasks->get($id, [
                    'contain' => []
                ]);
        $previous_due_date = date('Y-m-d H:i',strtotime($task->due_date));
        $data=array();
        $data_task = [];
        $data['title'] = $objectData['title'];
        $data_task['description'] = $objectData['description'];
		$data['priority'] = $objectData['priority'];
        $associated_user_id = '';
        //Write logs while user edit task.
        $browser = $_SERVER['HTTP_USER_AGENT'];
        FileLog::writeLog("EditTaskLogs", "TaskListing.addTask :: Browser Details :- " . $browser . "\n Task detail before edit :-  " . json_encode($task) . "\n Task details to update :- " . json_encode($objectData) ."\n");

        if(isset($objectData['associated_owner']) && !empty($objectData['associated_owner']))
        {
            $data['user_id'] = $objectData['associated_owner'];
            $associated_user_id = $data['user_id'];
            $old_user_id = $task['user_id'];
            
            if($old_user_id != $associated_user_id)
            {
                $previous_user_id = $task['previous_user_id'];
                 if(isset($previous_user_id) && !empty($previous_user_id)){
                    $data['previous_user_id'] = $old_user_id .','.$previous_user_id;
                 }else{
                    $data['previous_user_id'] = $old_user_id;
                 }
                // sending notification to old user                                       
                $notification_id = _NOTIFICATION_UNASSIGN_TASK;   
                $notification_data['task_title'] = $data['title'];      
                $sendNotificationToOldUser = SystemNotification::sendNotificationToOldUser($login_agency_id,$old_user_id, null, $notification_id,$notification_data);

                // sending notification to new user
                $taskGoToLink = SITEURL.'tasks/?task_id='.$task['id'];
                $notification_data['link'] = $taskGoToLink;
                $new_user_id = $data['user_id'];                    
                $notification_type_id = _ID_NOTIFICATION_NEW_TASK_ASSIGN;
                $sendNotificationToNewUser = SystemNotification::sendNotification($login_agency_id,$new_user_id, null, $notification_type_id,$notification_data);
            }
        }

		$dueDate = date('Y-m-d',strtotime($objectData['due_date']));
		$dueTime = date('H:i',strtotime($objectData['due_time'])) ;
		$due_date_time = $dueDate." ".$dueTime;
        $data['due_date'] = $due_date_time;

        $task_category_id = '';
        if(isset($objectData['task_category_id']) && $objectData['task_category_id']!=''){
            $data['task_category_id'] = $objectData['task_category_id'];
            $task_category_id = $data['task_category_id'];
        }
        
		
		$checklistHtml = $data_task['description'];
		$dom = new \DOMDocument();
		$dom->loadHTML($checklistHtml, LIBXML_HTML_NODEFDTD);
		
		$taskDescriptionsUlLists = $dom->getElementsByTagName('ul');
		$checkListsHtml ='';
		if(count($taskDescriptionsUlLists) > 0)
		{
				
			foreach($taskDescriptionsUlLists as $key=> $taskDescriptionsUl)
			{
				$ulClass = $taskDescriptionsUl->getAttribute('data-checked');
				$taskDescriptionsUl->setAttribute('class','checklist_span');
				$taskDescriptionsUl->setAttribute('id','checklist');
				if($ulClass !== '')
				{
				
					$taskDescriptionsLi = $taskDescriptionsUl->getElementsByTagName('li');
				
					if(count($taskDescriptionsLi) > 0)
					{
						foreach($taskDescriptionsLi as $key=> $taskDescription)
						{ 
							if($ulClass ==  'true')
							{
								
								$taskDescription->setAttribute('class','checklist_active');
							}
							else if($ulClass ==  'false')
							{
								$taskDescription->setAttribute('class',_CHECKLIST_LI_INACTIVE_CLASS);
							}
							
							$taskDescription->setAttribute('onclick','liclick(this)');
							$checkListsHtml = $dom->saveHTML();	
						}
					}
				}else{
					$checkListsHtml = null;
				}
				
			}
		}else{
			$checkListsHtml = $data_task['description'];
		}
		
		if($checkListsHtml == null)
		{
			$checkListsHtml = $data_task['description'];
		}else{
			$checkListsHtml = $checkListsHtml;
		}
		
		$data_task['description'] = $checkListsHtml;
		if(strpos($data_task['description'], '<html><body>') !== false){
			$replaceHtmlTag = str_replace('<html><body>', '', $data_task['description']);
			$checkLists = str_replace('</html></body>', '', $replaceHtmlTag);
			$data_task['description'] = $checkLists;
		}else{
			$data_task['description'] = $checkListsHtml;
		}

        //check description should not be empty.
        $descriptionData = strip_tags($data_task['description']);
        if($descriptionData != '')
        {
            $data['description'] = $data_task['description'];
        }
	
	
        //   checklist Code  end here

		// check checklist exist in desc or not added by monika
		$taskdesc = htmlspecialchars_decode($checkLists);
		$count_total =$count_inactive=$count_active= 0;
		$checkListStatus =0;
		// if string contains the word
		
		if(strpos($taskdesc, _CHECKLIST_UL_CLASS) !== false){
			$checkListStatus = 1;
			if(strpos($taskdesc, _CHECKLIST_LI_INACTIVE_CLASS) !== false){
				$count_inactive = substr_count($taskdesc,_CHECKLIST_LI_INACTIVE_CLASS);
			}if(strpos($taskdesc, _CHECKLIST_LI_ACTIVE_CLASS) !== false){
				$count_active = substr_count($taskdesc,_CHECKLIST_LI_ACTIVE_CLASS);
			}

		}else{
			$checkListStatus = 0;
		}
		$count_total = $count_inactive + $count_active;
		$checklistInfo = [];
        if($count_total == $count_active && ($count_total!==0 && $count_active !==0))
        {
           
            $checklistInfo['status'] = _ID_SUCCESS;
            
            $data['status'] = _ID_SUCCESS;
        }
        else
        {
              $checklistInfo['status'] = _ID_STATUS_PENDING;
              $data['status'] = _ID_STATUS_INACTIVE;
        }

		// end here by monika
        $task = $Tasks->patchEntity($task, $data);
        if ($task = $Tasks->save($task))
        {
            //Write logs if task description is saved empty or null.
            $savedDescription = strip_tags($task->description);
            if (trim($savedDescription) == '')
            {
                FileLog::writeLog("EmptyTaskDescriptionLogs", 'TaskListing.addTask :: ' . json_encode($objectData));
            }
            //$contact_associated_owner_name = '';
            $contact_deal_owner_name = '';
            if(isset($objectData['associated_owner']) && !empty($objectData['associated_owner']))
            {
                $ownerDetails = $Users->userDetails($data['user_id']);
                if(isset($ownerDetails['first_name']) || !empty($ownerDetails['first_name']))
                {
                    $contact_deal_owner_name .=$ownerDetails['first_name'];
                }
                if(isset($ownerDetails['last_name']) || !empty($ownerDetails['last_name']))
                {
                    $contact_deal_owner_name  .=' '.$ownerDetails['last_name'];
                }
            }

            $task_category_name = '';
            if(isset($objectData['task_category_id']) && !empty($objectData['task_category_id']))
            {
                $taskCategoryDetails = $TaskCategories->TaskCategoriesById($objectData['task_category_id']);
                if(isset($taskCategoryDetails) && !empty($taskCategoryDetails))
                {
                    $task_category_name =$taskCategoryDetails['name'];
                }
               
            }
			// add the checklist added by monika
			if($checkListStatus==1){
				
				$checklistInfo['task_id']= $task->id;
				$checklistInfo['description']= $data['description'];
				$checklistInfo['count_total'] =$count_total;
				$checklistInfo['agency_id'] =$task->agency_id;
				$checklistInfo['count_completed'] =$count_active;
				$checklistInfo['completed'] =   (_TASK_CHECKLIST_RATIO/$count_total)*$count_active; // Formula for calculate percentage of checked checklist
			
				
				$countChecklist='';
				$countChecklist = $TaskChecklists->find()->where(['task_id'=>$task->id])->count();
				if($countChecklist>0){
					$TaskChecklists->updateAll(['status'=>$checklistInfo['status'],'completed'=>$checklistInfo['completed'],'count_total'=>$count_total,'count_completed'=>$count_active],['task_id'=>$task->id]);

				}else{
					$checklistData = $TaskChecklists->newEntity();
					$checklistData = $TaskChecklists->patchEntity($checklistData,$checklistInfo);
					$TaskChecklists->save($checklistData);
				}
			}
			// end here

            $task_notes = "";
           	$taskPolicyDetails = $TaskPoliciesLink->find('all')->where(['task_id'=>$id])->toArray();
            if(isset($objectData['assign_policies']) && !empty($objectData['assign_policies']))
            {                
                if(!empty($taskPolicyDetails))
                { 
                    $TaskPoliciesLink->deleteAll(['task_id'=>$id]);                       
                }
                $policies = $objectData['assign_policies'];
                
				$taskPolicy = $TaskPoliciesLink->newEntity();
				$policy =  [];
				$policy['task_id'] = $id;
				$policy['opportunity_id'] = $policies;//$oppId;
				$policy['agency_id'] = $login_agency_id;
				$policy['status'] = "1";
				$taskPolicy = $TaskPoliciesLink->patchEntity($taskPolicy,$policy);
				$taskNote = $TaskPoliciesLink->save($taskPolicy);
                
            }else
            {
                $TaskPoliciesLink->deleteAll(['task_id'=>$id]);
            }
			$saveType = '';
			if(isset($objectData['taskType']) && $objectData['taskType'] == _TASK_STATUS_COMPLETE)
			{
				$Tasks->updateAll(['status'=>_TASK_STATUS_COMPLETE],['id'=>$task->id]);
				$saveType = _TASK_STATUS_COMPLETE;
			}

			$taskListing ='';
			if(isset($objectData['oppt_id']) && !empty($objectData['oppt_id']))
			{
				$taskListing = TaskListing::getTaskListingByOpp($objectData['oppt_id'],null);
			}
			$response =  json_encode(array('status' => _ID_SUCCESS,'taskListing'=>$taskListing,'taskSaveType'=>$saveType));

        }
        else{
            $response =  json_encode(array('status' => _ID_FAILED));
        }
       
        return $response;
	}


	public static function getCompletedTaskListing($contactId, $fields=null)
	{
		try
		{
			$myfile = fopen(ROOT."/logs/vuetasklisting.log", "a") or die("Unable to open file!");

            $session = Router::getRequest()->getSession();
			$login_agency_id = $session->read('Auth.User.agency_id');
			$Agency = TableRegistry::getTableLocator()->get('Agency');
			$UsStates = TableRegistry::getTableLocator()->get('UsStates');
			$agencyDetail = $Agency->agencyDetails($login_agency_id);
			$usersTimezone='America/Phoenix';
			$stateDetail = $UsStates->stateDetail($agencyDetail['us_state_id']);
			if(isset($agencyDetail['time_zone']) && !empty($agencyDetail['time_zone']))
			{
				$usersTimezone =  $agencyDetail['time_zone'];
			}
			else if(isset($stateDetail) && !empty($stateDetail))
			{
			   $usersTimezone =  $stateDetail->time_zone;
			}

			$ContactNoteTypeTable = TableRegistry::getTableLocator()->get('Tasks');
			$ContactOppTable = TableRegistry::getTableLocator()->get('ContactOpportunities');

			$results = $ContactNoteTypeTable->find()->where(['contact_id' => $contactId,'Tasks.status'=> 1])
			->select(['a.first_name','a.last_name','ac.first_name','ac.last_name','tp.opportunity_id','user_id','title','Tasks.agency_id','Tasks.due_date','priority','task_category_id','tc.name','description','Tasks.id',"dueDateCategory"=>"DATE_FORMAT(Tasks.due_date,'%b %d,%Y')","dueTimeCategory"=>"DATE_FORMAT(Tasks.due_date,'%h:%i %p')"])
			->join([ 
				"table" => "users",
				"alias" => "a",
				"type" => "INNER",
				"conditions" => "Tasks.user_id = a.id"
			])
			->join([
				"table" => "contacts",
				"alias" => "ac",
				"type" => "INNER",
				"conditions" => "Tasks.contact_id = ac.id"
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
			])->contain(['TaskNotes'])->order(['Tasks.modified' => 'DESC'])
			->hydrate(false);
		
			foreach($results as $key=>$value)
			{

                $time_zone = CommonFunctions::getShortCodeTimeZone($value['agency_id']);
				$agencyTime = date('h:i a',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($value['created'])))));
				$dateMonth = date('M d, Y',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d',strtotime($value['created'])))));
				$value['due_date_time'] = $agencyTime;
				$value['due_date_category'] = $dateMonth;
				$value['time_zone'] = $time_zone;
                $value['message_count'] = count($value['task_notes']);
				$result[] = $value;
				if(isset($value['tp']['opportunity_id']) && !empty($value['tp']['opportunity_id']))
				{
					$result2 = $ContactOppTable->find()->where(['id' =>$value['tp']['opportunity_id']])
					->select(['policy_number'])->hydrate(false)->first();
					$result[$key]['policy_number'] = 'Policy '.$result2['policy_number'];
				}else 
				{
					$result[$key]['policy_number'] = '';
				}		
			}


			$return['TaskListing.getCompletedTaskListing'] = [
					$contactId => $result
			];
			return $return;
		}catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: taskLising Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }

	}

	public static function searchUpcomingTask($objectData)
	{
		try
		{
			$myfile = fopen(ROOT."/logs/vuetasklisting.log", "a") or die("Unable to open file!");
            $session = Router::getRequest()->getSession();
			$login_agency_id = $session->read('Auth.User.agency_id');
			$Agency = TableRegistry::getTableLocator()->get('Agency');
			$UsStates = TableRegistry::getTableLocator()->get('UsStates');

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

			$ContactNoteTypeTable = TableRegistry::getTableLocator()->get('Tasks');
			$ContactOppTable = TableRegistry::getTableLocator()->get('ContactOpportunities');
			$keyword = $objectData['keyword'];
			$results = $ContactNoteTypeTable->find()->where(['contact_id' => $objectData['contact_id'],'Tasks.status'=> 2,
			[
				"OR"=>[
					'Tasks.title like' => "$keyword%",
				    'Tasks.description like' => "%$keyword%"
				]
			]
			])
			->select(['a.first_name','a.last_name','ac.first_name','ac.last_name','tp.opportunity_id','user_id','title','Tasks.agency_id','Tasks.due_date','priority','task_category_id','tc.name','description','Tasks.id',"dueDateCategory"=>"DATE_FORMAT(Tasks.due_date,'%b %d,%Y')","dueTimeCategory"=>"DATE_FORMAT(Tasks.due_date,'%h:%i %p')"])
			->join([ 
				"table" => "users",
				"alias" => "a",
				"type" => "INNER",
				"conditions" => "Tasks.user_id = a.id"
			])
			->join([
				"table" => "contacts",
				"alias" => "ac",
				"type" => "INNER",
				"conditions" => "Tasks.contact_id = ac.id"
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
			])->contain(['TaskNotes'])->order(['Tasks.id' => 'DESC'])
			->hydrate(false)->toArray();
			foreach($results as $key=>$value)
			{
                $time_zone = CommonFunctions::getShortCodeTimeZone($value['agency_id']);
				$agencyTime = date('h:i a',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($value['created'])))));
				$dateMonth = date('M d, Y',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d',strtotime($value['created'])))));
				$value['due_date_time'] = $agencyTime;
				$value['due_date_category'] = $dateMonth;
				$value['time_zone'] = $time_zone;
				$value['message_count'] = count($value['task_notes']);
				$result[] = $value;

				if(isset($value['tp']['opportunity_id']) && !empty($value['tp']['opportunity_id']))
				{
					$result2 = $ContactOppTable->find()->where(['id' =>$value['tp']['opportunity_id']])
					->select(['policy_number'])->hydrate(false)->first();
					$result[$key]['policy_number'] = 'Policy '.$result2['policy_number'].',';
				}else 
				{
					$result[$key]['policy_number'] = '';
				}		
			}

			$return['TaskListing.searchUpcomingTask'] = [
				$objectData['contact_id'] => $result
			];
			return $return;
		}catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: taskLising Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }

	}

	public static function searchCompletedTask($objectData)
	{
		try
		{
			$myfile = fopen(ROOT."/logs/vuetasklisting.log", "a") or die("Unable to open file!");

			$ContactNoteTypeTable = TableRegistry::getTableLocator()->get('Tasks');
			$ContactOppTable = TableRegistry::getTableLocator()->get('ContactOpportunities');
			$keyword = $objectData['keyword'];
			$results = $ContactNoteTypeTable->find()->where(['contact_id' => $objectData['contact_id'],'Tasks.status'=> 1,
			
			[
				"OR"=>[
					'Tasks.title like' => "$keyword%",
				    'Tasks.description like' => "%$keyword%"
				]
			]
			])
			->select(['a.first_name','a.last_name','ac.first_name','ac.last_name','tp.opportunity_id','user_id','title','Tasks.agency_id','Tasks.due_date','priority','task_category_id','tc.name','description','Tasks.id',"dueDateCategory"=>"DATE_FORMAT(Tasks.due_date,'%b %d,%Y')","dueTimeCategory"=>"DATE_FORMAT(Tasks.due_date,'%h:%i %p')"])
			->join([ 
				"table" => "users",
				"alias" => "a",
				"type" => "INNER",
				"conditions" => "Tasks.user_id = a.id"
			])
			->join([
				"table" => "contacts",
				"alias" => "ac",
				"type" => "INNER",
				"conditions" => "Tasks.contact_id = ac.id"
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
			])->contain('TaskNotes')->order(['Tasks.id' => 'DESC'])
			->hydrate(false)->toArray();
			foreach($results as $key=>$value)
			{
				$time_zone = CommonFunctions::getShortCodeTimeZone($value['agency_id']);
                $value['time_zone'] = $time_zone;
                $value['message_count'] = count($value['task_notes']);
				$result[] = $value;

				if(isset($value['tp']['opportunity_id']) && !empty($value['tp']['opportunity_id']))
				{
					$result2 = $ContactOppTable->find()->where(['id' =>$value['tp']['opportunity_id']])
					->select(['policy_number'])->hydrate(false)->first();
					$result[$key]['policy_number'] = 'Policy '.$result2['policy_number'].',';
				}else 
				{
					$result[$key]['policy_number'] = '';
				}		
			}

			$return['TaskListing.searchUpCompletedTask'] = [
				$objectData['contact_id'] => $result
			];
			return $return;
		}catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: taskLising Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }

	}

	public static function getTaskListingByOpp($opportunity_id, $fields)
	{
		try
		{
			$myfile = fopen(ROOT."/logs/vuetasklisting.log", "a") or die("Unable to open file!");
			$session = Router::getRequest()->getSession(); 
			$login_agency_id = $session->read('Auth.User.agency_id');
			$Agency = TableRegistry::getTableLocator()->get('Agency');
			$UsStates = TableRegistry::getTableLocator()->get('UsStates');
			$agencyDetail = $Agency->agencyDetails($login_agency_id);
			$usersTimezone='America/Phoenix';
			$stateDetail = $UsStates->stateDetail($agencyDetail['us_state_id']);
			if(isset($agencyDetail['time_zone']) && !empty($agencyDetail['time_zone']))
			{
				$usersTimezone =  $agencyDetail['time_zone'];
			}
			else if(isset($stateDetail) && !empty($stateDetail))
			{
			   $usersTimezone =  $stateDetail->time_zone;
			}
			$ContactOppTable = TableRegistry::getTableLocator()->get('ContactOpportunities');
			$ContactNoteTypeTable = TableRegistry::getTableLocator()->get('Tasks');
		
			$results = $ContactNoteTypeTable->find()->where(['Tasks.opportunity_id' => $opportunity_id])
			->select(['a.first_name','a.last_name','ac.first_name','ac.last_name','user_id','title','tp.opportunity_id','priority','task_category_id','tc.name','Tasks.agency_id','Tasks.due_date', 'Tasks.status','description','Tasks.id',"dueDateCategory"=>"DATE_FORMAT(Tasks.due_date,'%b %d,%Y')","dueTimeCategory"=>"DATE_FORMAT(Tasks.due_date,'%h:%i %p')"])
			->join([ 
				"table" => "users",
				"alias" => "a",
				"type" => "INNER",
				"conditions" => "Tasks.user_id = a.id"
			])
			->join([
				"table" => "contacts",
				"alias" => "ac",
				"type" => "LEFT",
				"conditions" => "Tasks.contact_id = ac.id"
			])->join([
				"table" => "contact_business",
				"alias" => "cb",
				"type" => "LEFT",
				"conditions" => "Tasks.contact_business_id = cb.id"
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
			])->order(['Tasks.id' => 'DESC'])
			->hydrate(false)->toArray();
			foreach($results as $key=>$value)
			{
				$time_zone = CommonFunctions::getShortCodeTimeZone($value['agency_id']);
				$agencyTime = date('h:i a',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($value['due_date'])))));
				$dateMonth = date('M d, Y',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d',strtotime($value['due_date'])))));
				$value['due_date_time'] = $agencyTime;
				$value['due_date_category'] = $dateMonth;
				$value['time_zone'] = $time_zone;
				$result[] = $value;
				if(isset($value['tp']['opportunity_id']) && !empty($value['tp']['opportunity_id']))
				{
					$result2 = $ContactOppTable->find()->where(['id' =>$value['tp']['opportunity_id']])
					->select(['policy_number','insurance_type_id'])->hydrate(false)->first();
					if(isset($result2['policy_number']) && $result2['policy_number'] != '')
					{

						$result[$key]['policy_number'] = 'Policy #'.$result2['policy_number'].',';
					}
				}else 
				{
					$result2 = $ContactOppTable->find()->where(['id' =>$opportunity_id])->select(['policy_number'])->hydrate(false)->first();
					if(isset($result2['policy_number']) && !empty($result2['policy_number']))
					{
						$result[$key]['policy_number'] = 'Policy '.$result2['policy_number'];
					}else{
						$result[$key]['policy_number'] = '';
					}
					
				}	
			}
		
			$return['TaskListing.getTaskListingByOpp'] = [
					$opportunity_id => $result
			];
			return $return;
		}catch (\Exception $e) {
			
            $txt=date('Y-m-d H:i:s').' :: taskLising Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }

	}

	public static function systemBellNotification($objectData)
	{
		$response = [];
		try
		{
			$sendNotification = '';
			$recipient_id=0;
			$session = Router::getRequest()->getSession(); 
			$login_agency_id = $session->read('Auth.User.agency_id');
			$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
			if(isset($objectData['assigned_owner']) && !empty($objectData['assigned_owner'])){
				$recipient_id = $objectData['assigned_owner'];
			}
			if(isset($recipient_id) && !empty($recipient_id))
			{
				$task_id = null;
				if(isset($objecData['task_id']) && !empty($objecData['task_id'])){
					$task_id = $objecData['task_id'];
				}
				$taskGoToLink = SITEURL.'tasks/?task_id='.$task_id;
				$notification_data['link']= $taskGoToLink;
				$notification_type_id = _ID_NOTIFICATION_NEW_TASK_ASSIGN;
				$sendNotification = SystemNotification::sendNotification($login_agency_id,$recipient_id, null, $notification_type_id,$notification_data);
			
			}
			$taskListing = '';
			if(isset($objectData['oppt_id']) && !empty($objectData['oppt_id']))
			{
				$opp_details = $ContactOpportunities->get($objectData['oppt_id']);
				if(isset($opp_details['contact_business_id']) && !empty($opp_details['contact_business_id']))
				{
					$taskListing = TaskListing::getBusinessTaskListingByOpp($opp_details['id'],null);
					$response = json_encode(array('status' => _ID_SUCCESS,'error_msg'=>'','taskListing'=>$taskListing,'contact_business_id'=>$opp_details['contact_business_id']));
				}else{
					$taskListing = TaskListing::getTaskListingByOpp($opp_details['id'],null);
					$response = json_encode(array('status' => _ID_SUCCESS,'error_msg'=>'','taskListing'=>$taskListing,'contact_id'=>$opp_details['contact_id']));
				}	
			}
		}catch (\Exception $e) {
			
            $txt = date('Y-m-d H:i:s').' :: systemBellNotification Error- '.$e->getMessage();
			$response = json_encode(array('status' => _ID_FAILED,'error_msg'=>$txt));
        }
		return $response;
	}


	public static function getBusinessTaskListingByOpp($opportunity_id, $fields)
	{
		try
		{
			$myfile = fopen(ROOT."/logs/vuetasklisting.log", "a") or die("Unable to open file!");
			$session = Router::getRequest()->getSession(); 
			$login_agency_id = $session->read('Auth.User.agency_id');
			$Agency = TableRegistry::getTableLocator()->get('Agency');
			$UsStates = TableRegistry::getTableLocator()->get('UsStates');
			$agencyDetail = $Agency->agencyDetails($login_agency_id);
			$usersTimezone='America/Phoenix';
			$stateDetail = $UsStates->stateDetail($agencyDetail['us_state_id']);
			if(isset($agencyDetail['time_zone']) && !empty($agencyDetail['time_zone']))
			{
				$usersTimezone =  $agencyDetail['time_zone'];
			}
			else if(isset($stateDetail) && !empty($stateDetail))
			{
			   $usersTimezone =  $stateDetail->time_zone;
			}
			$ContactOppTable = TableRegistry::getTableLocator()->get('ContactOpportunities');
			$ContactNoteTypeTable = TableRegistry::getTableLocator()->get('Tasks');
		
			$results = $ContactNoteTypeTable->find()->where(['Tasks.opportunity_id' => $opportunity_id])
			->select(['a.first_name','a.last_name','cb.name','user_id','title','tp.opportunity_id','priority','task_category_id','tc.name','Tasks.agency_id','Tasks.due_date', 'Tasks.status', 'description','Tasks.id',"dueDateCategory"=>"DATE_FORMAT(Tasks.due_date,'%b %d,%Y')","dueTimeCategory"=>"DATE_FORMAT(Tasks.due_date,'%h:%i %p')"])
			->join([ 
				"table" => "users",
				"alias" => "a",
				"type" => "INNER",
				"conditions" => "Tasks.user_id = a.id"
			])
			->join([
				"table" => "task_policies_link",
				"alias" => "tp",
				"type" => "LEFT",
				"conditions" => "Tasks.id = tp.task_id"
			])->join([
				"table" => "contact_business",
				"alias" => "cb",
				"type" => "INNER",
				"conditions" => "Tasks.contact_business_id = cb.id"
			])->join([
				"table" => "task_categories",
				"alias" => "tc",
				"type" => "LEFT",
				"conditions" => "Tasks.task_category_id = tc.id"
			])->order(['Tasks.id' => 'DESC'])
			->hydrate(false)->toArray();
			foreach($results as $key=>$value)
			{
				$time_zone = CommonFunctions::getShortCodeTimeZone($value['agency_id']);
				$agencyTime = date('h:i a',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($value['due_date'])))));
				$dateMonth = date('M d, Y',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d',strtotime($value['due_date'])))));
				$value['due_date_time'] = $agencyTime;
				$value['due_date_category'] = $dateMonth;
				$value['time_zone'] = $time_zone;
				$result[] = $value;
				if(isset($value['tp']['opportunity_id']) && !empty($value['tp']['opportunity_id']))
				{
					$result2 = $ContactOppTable->find()->where(['id' =>$value['tp']['opportunity_id']])
					->select(['policy_number'])->hydrate(false)->first();
					$result[$key]['policy_number'] = 'Policy '.$result2['policy_number'].',';
				}else 
				{
					$result[$key]['policy_number'] = '';
				}	
			}
		
			$return['TaskListing.getBusinessTaskListingByOpp'] = [
					$opportunity_id => $result
			];
			return $return;
		}catch (\Exception $e) {
			
            $txt=date('Y-m-d H:i:s').' :: taskLising Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }

	}
	public static function getPolicesNoteTypeBusiness($contact_business_id)
	{
		$session = Router::getRequest()->getSession(); 
		$login_agency_id = $session->read("Auth.User.agency_id");
		$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');

		$getPolicyListing = $ContactOpportunities->getMultiPolicyListingBusiness($contact_business_id);
		$get_policy_types_for_notes_listing = [];
		if (isset($getPolicyListing) && !empty($getPolicyListing)) {
			$insurance_type_name ='';
			foreach ($getPolicyListing as $key => $value) {

				// if (!empty($value['hawksoft_policy_title'])) {
				// 	$insurance_type_name = $value['hawksoft_policy_title'];
				// } 
				if (!empty($value['insurance_type']['type'])) {
					$insurance_type_name = $value['insurance_type']['type'];
				}
				$policy_number_note_list = !empty($value['policy_number']) ? " - ". $value['policy_number'] : '';
				$opportunity_id_note_list = !empty($value['id']) ?  $value['id'] : '';

				
				$policy_opprtunityId_note_list = $insurance_type_name.$policy_number_note_list;
				if(!empty($opportunity_id_note_list))
				{
					
					$get_policy_types_for_notes_listing[] = [
						"id" => $opportunity_id_note_list,
						"name" => $policy_opprtunityId_note_list,
						"policy_number" =>  $policy_number_note_list,
						"policy_type" => $insurance_type_name
					];
				}

			}
		}
		
		$return['TaskListing.getPolicesNoteTypeBusiness'] = [
			$contact_business_id => $get_policy_types_for_notes_listing
		];
		return $return;
	}
	public function contactCardUpdateTaskStatus($objectData)
    {
        $response = [];
		$session = Router::getRequest()->getSession(); 
		$login_agency_id = $session->read("Auth.User.agency_id");
        $login_user_id=$session->read('Auth.User.user_id');
        $id=$objectData['task_id'];
        $task_status = $objectData['task_status'];
		$Tasks = TableRegistry::getTableLocator()->get('Tasks');
		$TaskChecklists = TableRegistry::getTableLocator()->get('TaskChecklists');
		$ContactCommunications = TableRegistry::getTableLocator()->get('ContactCommunications');
        $task = $Tasks->get($id, ['contain' => []]);
        $taskListing = [];
        try
        {
            if(!empty($task))
            {
                $data=array();
                // Checklist condition Added By monika
                $checklistAdd =_ID_FAILED;
                $existCheckList='';
                $existCheckList  = $TaskChecklists->find()->where(['task_id'=>$id])->first();
                    if($task_status == _TASK_STATUS_COMPLETE)
                    {
                        $data['status']= _TASK_STATUS_COMPLETE;
                    }
                    else
                    {
                        $data['status']= _TASK_STATUS_UNCOMPLETE;
                    }

                    $checklistHtml = $task['description'];
                    $dom = new \DOMDocument();
                    $dom->loadHTML($checklistHtml, LIBXML_HTML_NODEFDTD);

                    $taskDescriptionsUlLists = $dom->getElementsByTagName('ul');

                    $checkListsHtml ='';
                    if(count($taskDescriptionsUlLists) > 0)
                    {

                            $taskDescriptionsLi = $dom->getElementsByTagName('li');

                            if(count($taskDescriptionsLi) > 0)
                            {
                                 $checkListsHtml .= '<ul data-checked="false" class="checklist_span" id="checklist">';
                                foreach($taskDescriptionsLi as $key=> $taskDescription)
                                {
                                     $taskDescription->setAttribute('class',_CHECKLIST_LI_ACTIVE_CLASS);
                                     $checkListsHtml .= $dom->saveHTML($taskDescription);
                                }
                                $checkListsHtml .= '</ul>';
                            }


                    }
                    else
                    {
                        $checkListsHtml = $task['description'];
                    }

                    $data['description'] = $checkListsHtml;
                    $task['description'] =  $checkListsHtml;

                    $task = $Tasks->patchEntity($task, $data);
                    if ($Tasks->save($task))
                    {
                        //Code to add the task in communication listing when task mark completed
                        if(isset($task['contact_id']) && !empty($task['contact_id'])){
                        $type = _COMMUNICATION_TYPE_TASK;
                        $subject    =   $task['title'];
                        $message     =   $task['description'];
                        $contact_id = $task['contact_id'];

                        // change pending task into delete status
                        $ContactCommunications->updateAll(['status'=>_ID_STATUS_DELETED],['agency_id'=>$login_agency_id,'communication_type'=>$type,'task_id' => $task->id,'task_status'=>_TASK_STATUS_UNCOMPLETE]);

                        CommonFunctions::saveCommunicationData($login_agency_id, $task->user_id, $contact_id, $type, $subject, $message,null,null,$task->id,_TASK_STATUS_COMPLETE);
                        }
                        if(isset($objectData['opp_id']) && !empty($objectData['opp_id'])){
                            $taskListing = TaskListing::getTaskListingByOpp($objectData['opp_id'],null);
                        }
                        $response =  json_encode(array('status' => _ID_SUCCESS,'id' => $id,'task_status'=>$task['status'], 'taskListing' => $taskListing));
                    }
                    else
                    {
                        	$response =  json_encode(array('status' => _ID_FAILED,'msg'=>'Something went wrong try again.'));
                    }


            }
            else
            {
                   	$response =  json_encode(array('status' => _ID_FAILED,'msg'=>'Something went wrong try again.'));
            }
        }
        catch (\Exception $e) {

			$txt = date('Y-m-d H:i:s').' :: contactCardUpdateTaskStatus Error- '.$e->getMessage();
			$response =  json_encode(array('status' => _ID_FAILED,'error'=>$txt));
		}
		 return $response;

    }
	public function getAllTaskCategory($contactId){
		try
		{
			$myfile = fopen(ROOT."/logs/vueOverviewListing.log", "a") or die("Unable to open file!"); 
            $return = [
                'TaskListing' => []
            ];
            $session = Router::getRequest()->getSession(); 
         
            $taskCategories = TableRegistry::getTableLocator()->get('TaskCategories');
			$taskCategoryList = $taskCategories->allActiveTaskCategoriesList();
            $i = 0;
            foreach($taskCategoryList as $taskCategory){
                $data['TaskCategory'][$i]['id'] = $taskCategory['id'];
                $data['TaskCategory'][$i]['name'] = $taskCategory['name'];
                $i++;
            }
            $return['TaskListing.getAllTaskCategory'] = [
                $contactId => $data['TaskCategory']
            ];
            return $return;
        }catch (\Exception $e) {
			
            $txt=date('Y-m-d H:i:s').' :: Overview Lising Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
	}



	
	public static function setChecklistStatus($objectData){
	
		try
		{
			if(isset($objectData['task_id_n']) && !empty($objectData['task_id_n']))
			{
				$response = [];
				$taskChecklists = TableRegistry::getTableLocator()->get('TaskChecklists');
				$tasks = TableRegistry::getTableLocator()->get('Tasks');
				$add= 0;
				$checklistDesc ='';
				$checkLists = $checklistResponse =$checkListData=[];
				$task_id = $objectData['task_id_n'];
				$totalCheckboxes = $objectData['totalCheckboxes'];
				$numberOfChecked = $objectData['numberOfChecked'];
				$numberNotChecked = $objectData['numberNotChecked'];
				// // Update description
				$session = Router::getRequest()->getSession(); 
				$login_agency_id = $session->read("Auth.User.agency_id");
				$login_user_id=$session->read('Auth.User.user_id');
				$checklistHtml = $objectData['desc'];
				$dom = new \DOMDocument();
				$dom->loadHTML($checklistHtml);
				$xpath = new \DOMXPath($dom);	
				$inactiveClassCount = $xpath->query('//li[@class="checklist_inactive"]')->length;
				$activeClassCount = $xpath->query('//li[@class="checklist_active"]')->length;
				$taskDescBody = $dom->getElementsByTagName('body');
				$taskDescriptionsUlLists = $dom->getElementsByTagName('ul');
				$taskDescriptionsLi = $dom->getElementsByTagName('li');
				$checkListsHtml ='';
				$chekListActiveFlag = 0;
				$chekListInactiveFlag = 0;
				$k=0;
				$j=0;
				// $taskDescription = "";
				$nodes = $taskDescBody->item(0)->childNodes;
				foreach($nodes as $node)
				{
					if($node->nodeName == "ul")
					{
						$newDom = new \DOMDocument();
						$ul = $node->childNodes;
						if(count($node->childNodes) > 0)
						{
							foreach($ul as $li)
							{
								$domElement = $newDom->createElement('ul', $dom->saveHTML($li));
								$domElement->setAttribute('id','checklist');
								if($li->getAttribute("class") == "checklist_active")
								{
									$domElement->setAttribute('data-checked','true');
								}
	
								if($li->getAttribute("class") == "checklist_inactive")
								{
									$domElement->setAttribute('data-checked','false');
								}
								$domElement->setAttribute('class','checklist_span');
								$newDom->appendChild($domElement);
							}
							// $node->nodeValue = htmlspecialchars_decode($newDom->saveHTML());
							$checkListsHtml .= htmlspecialchars_decode($newDom->saveHTML());
						}
						
					}else{
						$checkListsHtml .= $dom->saveHTML($node);
					}
				}

				if($totalCheckboxes == $numberOfChecked)
				{
					$ChecklistStatus = _ID_SUCCESS;
					$status = _ID_SUCCESS;
				}
				else
				{
					$ChecklistStatus = _ID_STATUS_PENDING;
					$status = _ID_STATUS_INACTIVE;
				}
				$checklistDesc = $tasks->updateAll(['description'=>htmlspecialchars_decode($checkListsHtml),'status'=>$status],['id'=>$task_id]);
				$completed = _TASK_CHECKLIST_RATIO/$totalCheckboxes;
				if($totalCheckboxes>0){
					$existCheckList  = $taskChecklists->find()->where(['task_id'=>$task_id])->first();
					$checkLists['task_id'] =   $task_id;
					$checkLists['agency_id'] =   $login_agency_id;
					$checkLists['count_total'] =   $totalCheckboxes;
					$checkLists['count_completed'] = $numberOfChecked;
					$checkLists['status'] =    $ChecklistStatus;
					$checkLists['completed'] =   (_TASK_CHECKLIST_RATIO/$totalCheckboxes)*$numberOfChecked; // Formula for calculate percentage of checked checklist
					$checklistResponse =[];
					if(!empty($existCheckList))
					{
							$checklistResponse = $taskChecklists->updateAll(['completed'=>$checkLists['completed'],'count_total'=>$checkLists['count_total'],'count_completed'=>$checkLists['count_completed']],['task_id'=>$task_id]);
						
					}else
					{
						$checklist = $taskChecklists->newEntity();
						$checklist = $taskChecklists->patchEntity($checklist,$checkLists);
						$checklistResponse = $taskChecklists->save($checklist);
					}

					$checkListData  = $taskChecklists->find()->where(['task_id'=>$task_id])->first();
					$completedStatus  = isset($checkListData->completed) && !empty($checkListData->completed) ? $checkListData->completed : 0.00;
					$checklistCountRatio='';
					$checklistCountRatio = array('count_total'=>$checkListData->count_total,'count_completed'=>$checkListData->count_completed);
					
					if ($checklistResponse) 
					{
						$response =  json_encode(array('status' => _ID_SUCCESS,'completedStatus'=>$completedStatus,'checklistCountRatio'=>$checklistCountRatio,'count_total'=>$checkListData->count_total,'count_completed'=>$checkListData->count_completed));
						
					}

				}else
				{
					$response =  json_encode(array('status' => _ID_FAILED,'completedStatus'=>'','checklistCountRatio'=>'','count_total'=>'','count_completed'=>''));
				}
			}
		}
		catch (\Exception $e) {
		
			$txt = date('Y-m-d H:i:s').' :: setChecklistStatus Error- '.$e->getMessage();
			$response =  json_encode(array('status' => _ID_FAILED,'error'=>$txt));
		}
	
		 return $response;
	}

	public static function getBusinessTaskListingByOppNew($opportunity_id)
	{
		try
		{
			$myfile = fopen(ROOT."/logs/vuetasklisting.log", "a") or die("Unable to open file!");
			$session = Router::getRequest()->getSession();
			$login_agency_id = $session->read('Auth.User.agency_id');
			$Agency = TableRegistry::getTableLocator()->get('Agency');
			$UsStates = TableRegistry::getTableLocator()->get('UsStates');
			$agencyDetail = $Agency->agencyDetails($login_agency_id);
			$usersTimezone='America/Phoenix';
			$stateDetail = $UsStates->stateDetail($agencyDetail['us_state_id']);
			if(isset($agencyDetail['time_zone']) && !empty($agencyDetail['time_zone']))
			{
				$usersTimezone =  $agencyDetail['time_zone'];
			}
			else if(isset($stateDetail) && !empty($stateDetail))
			{
			   $usersTimezone =  $stateDetail->time_zone;
			}
			$ContactOppTable = TableRegistry::getTableLocator()->get('ContactOpportunities');
			$ContactNoteTypeTable = TableRegistry::getTableLocator()->get('Tasks');

			$results = $ContactNoteTypeTable->find()->where(['Tasks.opportunity_id' => $opportunity_id])
			->select(['a.first_name','a.last_name','cb.name','user_id','title','tp.opportunity_id','priority','task_category_id','tc.name','Tasks.agency_id','Tasks.due_date', 'Tasks.status', 'description','Tasks.id',"dueDateCategory"=>"DATE_FORMAT(Tasks.due_date,'%b %d,%Y')","dueTimeCategory"=>"DATE_FORMAT(Tasks.due_date,'%h:%i %p')"])
			->join([
				"table" => "users",
				"alias" => "a",
				"type" => "INNER",
				"conditions" => "Tasks.user_id = a.id"
			])
			->join([
				"table" => "task_policies_link",
				"alias" => "tp",
				"type" => "LEFT",
				"conditions" => "Tasks.id = tp.task_id"
			])->join([
				"table" => "contact_business",
				"alias" => "cb",
				"type" => "INNER",
				"conditions" => "Tasks.contact_business_id = cb.id"
			])->join([
				"table" => "task_categories",
				"alias" => "tc",
				"type" => "LEFT",
				"conditions" => "Tasks.task_category_id = tc.id"
			])->order(['Tasks.id' => 'DESC'])
			->hydrate(false)->toArray();
			foreach($results as $key=>$value)
			{
				$time_zone = CommonFunctions::getShortCodeTimeZone($value['agency_id']);
				$agencyTime = date('h:i a',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($value['due_date'])))));
				$dateMonth = date('M d, Y',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d',strtotime($value['due_date'])))));
				$value['due_date_time'] = $agencyTime;
				$value['due_date_category'] = $dateMonth;
				$value['time_zone'] = $time_zone;
				$result[] = $value;
				if(isset($value['tp']['opportunity_id']) && !empty($value['tp']['opportunity_id']))
				{
					$result2 = $ContactOppTable->find()->where(['id' =>$value['tp']['opportunity_id']])
					->select(['policy_number'])->hydrate(false)->first();
					$result[$key]['policy_number'] = 'Policy '.$result2['policy_number'].',';
				}else
				{
					$result[$key]['policy_number'] = '';
				}
			}
			$return['TaskListing.getBusinessTaskListingByOpp'] = [
					$opportunity_id => $result
			];
			return $return;
		}catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: taskLising Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }

	}

    public static function getTaskListingByOppNew($opportunity_id)
	{
		try
		{
			$myfile = fopen(ROOT."/logs/vuetasklisting.log", "a") or die("Unable to open file!");
			$session = Router::getRequest()->getSession();
			$login_agency_id = $session->read('Auth.User.agency_id');
			$Agency = TableRegistry::getTableLocator()->get('Agency');
			$UsStates = TableRegistry::getTableLocator()->get('UsStates');
			$agencyDetail = $Agency->agencyDetails($login_agency_id);
			$usersTimezone='America/Phoenix';
			$stateDetail = $UsStates->stateDetail($agencyDetail['us_state_id']);
			if(isset($agencyDetail['time_zone']) && !empty($agencyDetail['time_zone']))
			{
				$usersTimezone =  $agencyDetail['time_zone'];
			}
			else if(isset($stateDetail) && !empty($stateDetail))
			{
			   $usersTimezone =  $stateDetail->time_zone;
			}
			$ContactOppTable = TableRegistry::getTableLocator()->get('ContactOpportunities');
			$ContactNoteTypeTable = TableRegistry::getTableLocator()->get('Tasks');

			$results = $ContactNoteTypeTable->find()->where(['Tasks.opportunity_id' => $opportunity_id])
			->select(['a.first_name','a.last_name','ac.first_name','ac.last_name','user_id','title','tp.opportunity_id','priority','task_category_id','tc.name','Tasks.agency_id','Tasks.due_date', 'Tasks.status','description','Tasks.id',"dueDateCategory"=>"DATE_FORMAT(Tasks.due_date,'%b %d,%Y')","dueTimeCategory"=>"DATE_FORMAT(Tasks.due_date,'%h:%i %p')"])
			->join([
				"table" => "users",
				"alias" => "a",
				"type" => "INNER",
				"conditions" => "Tasks.user_id = a.id"
			])
			->join([
				"table" => "contacts",
				"alias" => "ac",
				"type" => "LEFT",
				"conditions" => "Tasks.contact_id = ac.id"
			])->join([
				"table" => "contact_business",
				"alias" => "cb",
				"type" => "LEFT",
				"conditions" => "Tasks.contact_business_id = cb.id"
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
			])->order(['Tasks.id' => 'DESC'])
			->hydrate(false)->toArray();
			foreach($results as $key=>$value)
			{
				$time_zone = CommonFunctions::getShortCodeTimeZone($value['agency_id']);
				$agencyTime = date('h:i a',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($value['due_date'])))));
				$dateMonth = date('M d, Y',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d',strtotime($value['due_date'])))));
				$value['due_date_time'] = $agencyTime;
				$value['due_date_category'] = $dateMonth;
				$value['time_zone'] = $time_zone;
				$result[] = $value;
				if(isset($value['tp']['opportunity_id']) && !empty($value['tp']['opportunity_id']))
				{
					$result2 = $ContactOppTable->find()->where(['id' =>$value['tp']['opportunity_id']])
					->select(['policy_number','insurance_type_id'])->hydrate(false)->first();
					if(isset($result2['policy_number']) && $result2['policy_number'] != '')
					{

						$result[$key]['policy_number'] = 'Policy #'.$result2['policy_number'].',';
					}
				}else
				{
					$result2 = $ContactOppTable->find()->where(['id' =>$opportunity_id])->select(['policy_number'])->hydrate(false)->first();
					if(isset($result2['policy_number']) && !empty($result2['policy_number']))
					{
						$result[$key]['policy_number'] = 'Policy '.$result2['policy_number'];
					}else{
						$result[$key]['policy_number'] = '';
					}

				}
			}
			$return['TaskListing.getTaskListingByOpp'] = [
					$opportunity_id => $result
			];
			return $return;
		}catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: taskLising Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }

	}


	public function getTaskNotesListing($taskId)
    {
        $session = Router::getRequest()->getSession();	
		$Tasks = TableRegistry::getTableLocator()->get('Tasks');
		$TaskNotes = TableRegistry::getTableLocator()->get('TaskNotes');
		$UsStates = TableRegistry::getTableLocator()->get('UsStates');  
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

		$taskDetail = $Tasks->getTaskNotesDetails($taskId);
		$taskNotes = $TaskNotes->getNoteDetails($taskId);
        foreach ($taskNotes as $key => $value)
        {
            $dateMonth = date('Y-m-d H:i:s',strtotime(CommonFunctions::convertUtcDateTimeToUserTimeZone($usersTimezone, date('Y-m-d H:i:s',strtotime($value['created'])))));
            $taskNotes[$key]['date_time'] = $dateMonth;
        }
		$taskDetail['task_notes'] = $taskNotes;
		$return['TaskListing.getTaskNotesListing'] = [
			$taskId => $taskDetail
		];
		return $return;
	}
    

    public function addTaskNotes($objectData)
    {
        $session = Router::getRequest()->getSession();
		$login_user_id = $session->read('Auth.User.user_id');
        $TaskNotes = TableRegistry::getTableLocator()->get('TaskNotes');
        $data = [];
        $notes = $TaskNotes->newEntity();
        $data['task_id'] = $objectData['taskId'];
        $data['user_id'] = $login_user_id;
        $data['description'] = $objectData['description'];
        $saveTaskNote = $TaskNotes->patchEntity($notes,$data);
        if($taskNote = $TaskNotes->save($saveTaskNote))
        {
            $response =  json_encode(array('status' => _ID_SUCCESS));
        }
        else
        {
            $response =  json_encode(array('status' => _ID_FAILED));
        }
        return $response;

    }

	//    <--- delete Tasks --->
    public function deleteTask($taskId)
    {
        $session = Router::getRequest()->getSession();
        $login_agency_id = $session->read("Auth.User.agency_id");
        $login_user_id = $session->read("Auth.User.user_id");
        $tasks = TableRegistry::getTableLocator()->get('tasks');
        $taskAttachments = TableRegistry::getTableLocator()->get('TaskAttachments');

        if($taskId)
        {
            $task = $tasks->get($taskId);
            if ($task)
            {
               $taskInfo = $tasks->updateAll(['status' => _ID_STATUS_DELETED],['Tasks.id' => $taskId, 'agency_id' => $login_agency_id]);
			   if($taskInfo)
			   {
					$taskAttachedData = $taskAttachments->find('all')->where(['TaskAttachments.task_id' => $taskId])->toArray();
					if($taskAttachedData)
					{
						$taskAttachments->updateAll(['status' => _ID_STATUS_INACTIVE],['TaskAttachments.task_id' => $taskId]);
					}
			   }
                
				$response =  json_encode(array('status' => _ID_SUCCESS));
            }
            else
            {
               	$response =  json_encode(array('status' => _ID_FAILED));
            }
		}
        return $response;
    }
	
}

?>