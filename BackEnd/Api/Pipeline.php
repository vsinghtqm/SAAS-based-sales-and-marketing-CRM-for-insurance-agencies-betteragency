<?php

namespace App\Lib\ApiProviders;
use App\Lib\NowCerts\NowCertsApi;
use App\Lib\PermissionsCheck;
use App\Lib\ApiProviders\ContactOpportunities;
use Cake\Http\Exception\UnauthorizedException;
use Cake\ORM\TableRegistry;
use App\Classes\CommonFunctions;
use Google\Exception;
use Cake\Routing\Router;
use App\Classes\FileLog;
use App\Lib\ContactOpportunityChangeLogging\ContactOpportunityChanger;
use App\Lib\ApiProviders\ContactCampaigns;

class Pipeline
{
	public static function pipelineStages($agencyId, $fields,$searchArr=null){

		$return = [];
		$pipeline = [];
		try
		{
		  $myfile = fopen(ROOT."/logs/vuePipeline.log", "a") or die("Unable to open file!");
		  $session = Router::getRequest()->getSession();
		  $login_agency_id = (int)$agencyId;
		  $login_user_id = $session->read("Auth.User.user_id");
		  $login_role_type = $session->read('Auth.User.role_type');
		  $login_role_type_flag = $session->read('Auth.User.role_type_flag');
		  $login_permissions = $session->read('Auth.User.permissions');
		  $login_permissions_arr = explode(",",$login_permissions);
		  $searchData = [];
		  $searchData['permissions_arr']=$login_permissions_arr;
//          $searchData['sort_by']= 1;
		  $show_data = 'default';
		  $ContactOpportunitiesTable = TableRegistry::getTableLocator()->get('ContactOpportunities');
		  $ContactsTable = TableRegistry::getTableLocator()->get('Contacts');
		  $Tasks = TableRegistry::getTableLocator()->get('Tasks');
          $Agency = TableRegistry::getTableLocator()->get('Agency');
          $UsStates = TableRegistry::getTableLocator()->get('UsStates');

		  //get all leads stage wise

		  //maintain search owner filter if available
			if(isset($searchArr) && !empty($searchArr))
			{
				$searchData = $searchArr;
			}
		  	else if(isset($searchData['permissions_arr']) && (in_array(71, $searchData['permissions_arr']) && $login_role_type_flag!=_AGENCY_ROLE_ADMIN ))
			{
			  $searchData['user_id']=$login_user_id;
			}

		   //new lead
		   $contacts_new_lead = $ContactOpportunitiesTable->opportunitiesByPipelineStageVue(_PIPELINE_STAGE_NEW_LEAD,$login_agency_id,$login_user_id,$login_role_type,$login_role_type_flag,$searchData,'',_PAGE_TYPE_DEFAULT,$show_data);
		  $contactNewleadInsuranceTypes = '';
		   $contactsNewLeadArray =[];
			if(isset($contacts_new_lead) && !empty($contacts_new_lead))
			{
				$i = 0;
				foreach($contacts_new_lead['data'] as $key => $data)
				{

					if(isset($data['contact_busines']) && isset($data['contact_busines']['id']) && !empty($data['contact_busines']) && $data['contact_busines'] != '' && $data['contact_busines'] != null)
					{
						$contactNewleadInsuranceTypes = $ContactOpportunitiesTable->getActiveOpportunitiesByStageContactIdVue(null,_PIPELINE_STAGE_NEW_LEAD,$login_agency_id,null,$data['contact_busines']['id']);
					}else{
						$contactNewleadInsuranceTypes = $ContactOpportunitiesTable->getActiveOpportunitiesByStageContactIdVue($data['contact']['id'],_PIPELINE_STAGE_NEW_LEAD,$login_agency_id,null,null);
					}

					$insurance_type = array();
                    $TaskOfOpp = array();
					 if(isset($contactNewleadInsuranceTypes) && !empty($contactNewleadInsuranceTypes))
					{
						$insuranceTypes = array();
						$opportunity_ids = array();
                        $salesTitle = array();

						foreach($contactNewleadInsuranceTypes as $key1 => $type)
						{
							$insurance_type = $type['insurance_type']['type'];
							array_push($insuranceTypes,$type['insurance_type']['type']);
							array_push($opportunity_ids,$type['id']);
                            if(isset($type['sales_title']) && $type['sales_title'] != '')
                            {
                                array_push($salesTitle,$type['sales_title']);
                            }
                            if(isset($type['tasks']) && $type['tasks'] != '')
                            {
                                $TaskOfOpp = $type['tasks'][0];
                            }

						}

						$contactsNewLeadArray[$i]['countContactInsuranceTypes'] = count($insuranceTypes);
						$insuranceTypes =	count($insuranceTypes) >1 ? implode(", ",$insuranceTypes) : $insuranceTypes[0];
						$contactsNewLeadArray[$i]['type'] = $insuranceTypes;
						$contactsNewLeadArray[$i]['opportunity_ids'] = $opportunity_ids;
                        $contactsNewLeadArray[$i]['sales_title'] = $salesTitle;

					}

//                    $TaskOfOpp = array();
//                    $Task = $Tasks->getTaskByOppId($contactNewleadInsuranceTypes[0]['id']);
//                    if(isset($Task) && !empty($Task))
//                    {
//                        $TaskOfOpp = $Task[0];
//                    }
					$contactsNewLeadArray[$i]['data'] = $data;
					$opportunity_id = $contactNewleadInsuranceTypes[0]['id'];
					$contactsNewLeadArray[$i]['data']['opportunity_id'] = $opportunity_id;
                    $contactsNewLeadArray[$i]['data']['Tasks'] = $TaskOfOpp;
					$contactsNewLeadArray[$i]['pipeline_stage'] = _PIPELINE_STAGE_NEW_LEAD;

				   $i++;
				}
			}

			$pipeline['contacts_new_lead'] = $contactsNewLeadArray;
			$pipeline['contacts_new_lead_count'] = $contacts_new_lead['count'];

			//projected value
			$pipeline['projected_value_new_lead'] = $ContactOpportunitiesTable->getProjectedValueByPipelineStageNewVue(_PIPELINE_STAGE_NEW_LEAD,$login_agency_id,$login_user_id,$login_role_type,$login_role_type_flag,$searchData);
			//new lead end here


			//appointment
			$contacts_appointment_scheduled = $ContactOpportunitiesTable->opportunitiesByPipelineStageVue(_PIPELINE_STAGE_APPOINTMENT_SCHEDULED,$login_agency_id,$login_user_id,$login_role_type,$login_role_type_flag,$searchData,'',_PAGE_TYPE_DEFAULT,$show_data);


			$contactAppointmentInsuranceTypes = '';
			$contactsAppointmentArray =[];
			if(isset($contacts_appointment_scheduled) && !empty($contacts_appointment_scheduled))
			{
				$i = 0;
				foreach($contacts_appointment_scheduled['data'] as $key => $data)
				{
					if(isset($data['contact_busines']) && isset($data['contact_busines']['id']) && !empty($data['contact_busines']) && $data['contact_busines'] != '' && $data['contact_busines'] != null)
					{
						$contactAppointmentInsuranceTypes = $ContactOpportunitiesTable->getActiveOpportunitiesByStageContactIdVue(null,_PIPELINE_STAGE_APPOINTMENT_SCHEDULED,$login_agency_id,null,$data['contact_busines']['id']);
					}else{
						$contactAppointmentInsuranceTypes = $ContactOpportunitiesTable->getActiveOpportunitiesByStageContactIdVue($data['contact']['id'],_PIPELINE_STAGE_APPOINTMENT_SCHEDULED,$login_agency_id,null,null);
					}
                    $TaskOfOpp = array();
					 if(isset($contactAppointmentInsuranceTypes) && !empty($contactAppointmentInsuranceTypes))
					{
						$insuranceTypes = array();
						$$opportunity_ids = array();
                        $salesTitle = array();
						foreach($contactAppointmentInsuranceTypes as $key1 => $type)
						{
								$insurance_type = $type['insurance_type']['type'];
								array_push($insuranceTypes,$type['insurance_type']['type']);
								array_push($opportunity_ids,$type['id']);
                                if(isset($type['sales_title']) && $type['sales_title'] != '') {
                                    array_push($salesTitle, $type['sales_title']);
                                }
                                if(isset($type['tasks']) && $type['tasks'] != '')
                                {
                                    $TaskOfOpp = $type['tasks'][0];
                                }

						}
						$contactsAppointmentArray[$i]['countContactInsuranceTypes'] = count($insuranceTypes);
						$insuranceTypes =	count($insuranceTypes) >1 ? implode(", ",$insuranceTypes) : $insuranceTypes[0];
						$contactsAppointmentArray[$i]['type'] = $insuranceTypes;
						$contactsAppointmentArray[$i]['opportunity_ids'] = $opportunity_ids;
                        $contactsAppointmentArray[$i]['sales_title'] =  $salesTitle;
					}
//                    $TaskOfOpp = array();
//                    $Task = $Tasks->getTaskByOppId($contactAppointmentInsuranceTypes[0]['id']);
//                    if(isset($Task) && !empty($Task))
//                    {
//                        $TaskOfOpp = $Task[0];
//                    }
					$contactsAppointmentArray[$i]['data'] = $data;
					$opportunity_id = $contactAppointmentInsuranceTypes[0]['id'];
					$contactsAppointmentArray[$i]['data']['opportunity_id'] = $opportunity_id;
                    $contactsAppointmentArray[$i]['data']['Tasks'] = $TaskOfOpp;
					$contactsAppointmentArray[$i]['pipeline_stage'] = _PIPELINE_STAGE_APPOINTMENT_SCHEDULED;
					$i++;
				}
			}

			$pipeline['contacts_appointment_scheduled'] = $contactsAppointmentArray;
			$pipeline['contacts_appointment_scheduled_count'] = $contacts_appointment_scheduled['count'];
			//projected value
			$pipeline['projected_value_appointment_scheduled'] = $ContactOpportunitiesTable->getProjectedValueByPipelineStageNewVue(_PIPELINE_STAGE_APPOINTMENT_SCHEDULED,$login_agency_id,$login_user_id,$login_role_type,$login_role_type_flag,$searchData);
			// end here


		//working
			$contacts_working = $ContactOpportunitiesTable->opportunitiesByPipelineStageVue(_PIPELINE_STAGE_WORKING,$login_agency_id,$login_user_id,$login_role_type,$login_role_type_flag,$searchData,'',_PAGE_TYPE_DEFAULT,$show_data);

			$contactWorkingInsuranceTypes = '';
			$contactsWorkingArray =[];
			if(isset($contacts_working) && !empty($contacts_working))
			{
				$i = 0;
				foreach($contacts_working['data'] as $key => $data)
				{
					if(isset($data['contact_busines']) && isset($data['contact_busines']['id']) && !empty($data['contact_busines']) && $data['contact_busines'] != '' && $data['contact_busines'] != null)
					{
						$contactWorkingInsuranceTypes = $ContactOpportunitiesTable->getActiveOpportunitiesByStageContactIdVue(null,_PIPELINE_STAGE_WORKING,$login_agency_id,null,$data['contact_busines']['id']);
					}else{

						 $contactWorkingInsuranceTypes = $ContactOpportunitiesTable->getActiveOpportunitiesByStageContactIdVue($data['contact']['id'],_PIPELINE_STAGE_WORKING,$login_agency_id,null,null);

					}
                    $TaskOfOpp = array();
					if(isset($contactWorkingInsuranceTypes) && !empty($contactWorkingInsuranceTypes))
					{
						$insuranceTypes = array();
						$opportunity_ids = array();
						$salesTitle = array();
						foreach($contactWorkingInsuranceTypes as $key1 => $type)
						{
								$insurance_type = $type['insurance_type']['type'];
								array_push($insuranceTypes,$type['insurance_type']['type']);
								array_push($opportunity_ids,$type['id']);
								if(isset($type['sales_title']) && $type['sales_title'] != '') {
                                    array_push($salesTitle, $type['sales_title']);
                                }
                                if(isset($type['tasks']) && $type['tasks'] != '')
                                {
                                    $TaskOfOpp = $type['tasks'][0];
                                }
						}
						$contactsWorkingArray[$i]['countContactInsuranceTypes'] = count($insuranceTypes);
						$insuranceTypes =	count($insuranceTypes) >1 ? implode(", ",$insuranceTypes) : $insuranceTypes[0];
						$contactsWorkingArray[$i]['type'] = $insuranceTypes;
						$contactsWorkingArray[$i]['opportunity_ids'] = $opportunity_ids;
						$contactsWorkingArray[$i]['sales_title'] =  $salesTitle;
					}
//                    $TaskOfOpp = array();
//                    $Task = $Tasks->getTaskByOppId($contactWorkingInsuranceTypes[0]['id']);
//                    if(isset($Task) && !empty($Task))
//                    {
//                        $TaskOfOpp = $Task[0];
//                    }
                    $contactsWorkingArray[$i]['data'] = $data;
                    $opportunity_id = $contactWorkingInsuranceTypes[0]['id'];
                    $contactsWorkingArray[$i]['data']['opportunity_id'] = $opportunity_id;
                    $contactsWorkingArray[$i]['data']['Tasks'] = $TaskOfOpp;
                    $contactsWorkingArray[$i]['pipeline_stage'] = _PIPELINE_STAGE_WORKING;
					$i++;

				}
			}

			$pipeline['contacts_working'] = $contactsWorkingArray;
			$pipeline['contacts_working_count'] = $contacts_working['count'];
			//projected value
			$pipeline['projected_value_working'] = $ContactOpportunitiesTable->getProjectedValueByPipelineStageNewVue(_PIPELINE_STAGE_WORKING,$login_agency_id,$login_user_id,$login_role_type,$login_role_type_flag,$searchData);

			// end here



			//quoting
			$contacts_quoting = $ContactOpportunitiesTable->opportunitiesByPipelineStageVue(_PIPELINE_STAGE_QUOTE_READY,$login_agency_id,$login_user_id,$login_role_type,$login_role_type_flag,$searchData,'',_PAGE_TYPE_DEFAULT,$show_data);

			$contactQuotingInsuranceTypes = '';
			$contactsQuotingArray =[];
			if(isset($contacts_quoting) && !empty($contacts_quoting))
			{
				$i = 0;
				foreach($contacts_quoting['data'] as $key => $data)
				{
					if(isset($data['contact_busines']) && isset($data['contact_busines']['id']) && !empty($data['contact_busines']) && $data['contact_busines'] != '' && $data['contact_busines'] != null)
					{
						$contactQuotingInsuranceTypes = $ContactOpportunitiesTable->getActiveOpportunitiesByStageContactIdVue(null,_PIPELINE_STAGE_QUOTE_READY,$login_agency_id,null,$data['contact_busines']['id']);
					}else{
						$contactQuotingInsuranceTypes = $ContactOpportunitiesTable->getActiveOpportunitiesByStageContactIdVue($data['contact']['id'],_PIPELINE_STAGE_QUOTE_READY,$login_agency_id,null,null);
					}
                    $TaskOfOpp = array();
					if(isset($contactQuotingInsuranceTypes) && !empty($contactQuotingInsuranceTypes))
					{
						$insuranceTypes = array();
						$opportunity_ids = array();
						$salesTitle =  array();
						foreach($contactQuotingInsuranceTypes as $key1 => $type)
						{
							$insurance_type = $type['insurance_type']['type'];
							array_push($insuranceTypes,$type['insurance_type']['type']);
							array_push($opportunity_ids,$type['id']);
							if(isset($type['sales_title']) && $type['sales_title'] != '') {
								array_push($salesTitle, $type['sales_title']);
							}
                            if(isset($type['tasks']) && $type['tasks'] != '')
                            {
                                $TaskOfOpp = $type['tasks'][0];
                            }
						}

						$contactsQuotingArray[$i]['countContactInsuranceTypes'] = count($insuranceTypes);
						$insuranceTypes =	count($insuranceTypes) >1 ? implode(", ",$insuranceTypes) : $insuranceTypes[0];
						$contactsQuotingArray[$i]['type'] = $insuranceTypes;
						$contactsQuotingArray[$i]['opportunity_ids'] = $opportunity_ids;
						$contactsQuotingArray[$i]['sales_title'] =  $salesTitle;
					}
//                    $TaskOfOpp = array();
//                    $Task = $Tasks->getTaskByOppId($contactQuotingInsuranceTypes[0]['id']);
//                    if(isset($Task) && !empty($Task))
//                    {
//                        $TaskOfOpp = $Task[0];
//                    }
                    $contactsQuotingArray[$i]['data'] = $data;
                    $opportunity_id = $contactQuotingInsuranceTypes[0]['id'];
                    $contactsQuotingArray[$i]['data']['opportunity_id'] = $opportunity_id;
                    $contactsQuotingArray[$i]['data']['Tasks'] = $TaskOfOpp;
                    $contactsQuotingArray[$i]['pipeline_stage'] = _PIPELINE_STAGE_QUOTE_READY;
					$i++;
				}
			}

			$pipeline['contacts_quoting'] = $contactsQuotingArray;
			$pipeline['contacts_quoting_count'] = $contacts_quoting['count'];
			$pipeline['projected_value_quoting'] = $ContactOpportunitiesTable->getProjectedValueByPipelineStageNewVue(_PIPELINE_STAGE_QUOTE_READY,$login_agency_id,$login_user_id,$login_role_type,$login_role_type_flag,$searchData);
			// end here

			//quote sent
			$contacts_quote_sent = $ContactOpportunitiesTable->opportunitiesByPipelineStageVue(_PIPELINE_STAGE_QUOTE_SENT,$login_agency_id,$login_user_id,$login_role_type,$login_role_type_flag,$searchData,'',_PAGE_TYPE_DEFAULT,$show_data);

			$contactQuoteSentInsuranceTypes = '';
			$contactsQuoteSentArray =[];
			if(isset($contacts_quote_sent) && !empty($contacts_quote_sent))
			{
				$i = 0;
				foreach($contacts_quote_sent['data'] as $key => $data)
				{
					if(isset($data['contact_busines']) && isset($data['contact_busines']['id']) && !empty($data['contact_busines']) && $data['contact_busines'] != '' && $data['contact_busines'] != null)
					{
						$contactQuoteSentInsuranceTypes = $ContactOpportunitiesTable->getActiveOpportunitiesByStageContactIdVue(null,_PIPELINE_STAGE_QUOTE_SENT,$login_agency_id,null,$data['contact_busines']['id']);
					}else{
						$contactQuoteSentInsuranceTypes = $ContactOpportunitiesTable->getActiveOpportunitiesByStageContactIdVue($data['contact']['id'],_PIPELINE_STAGE_QUOTE_SENT,$login_agency_id,null,null);
					}
                    $TaskOfOpp = array();
					if(isset($contactQuoteSentInsuranceTypes) && !empty($contactQuoteSentInsuranceTypes))
					{
						$insuranceTypes = array();
						$opportunity_ids = array();
                        $salesTitle = array();
						foreach($contactQuoteSentInsuranceTypes as $key1 => $type)
						{
							$insurance_type = $type['insurance_type']['type'];
							array_push($insuranceTypes,$type['insurance_type']['type']);
							array_push($opportunity_ids,$type['id']);
							if(isset($type['sales_title']) && $type['sales_title'] != '') {
								array_push($salesTitle, $type['sales_title']);
							}
                            if(isset($type['tasks']) && $type['tasks'] != '')
                            {
                                $TaskOfOpp = $type['tasks'][0];
                            }

						}
						$contactsQuoteSentArray[$i]['countContactInsuranceTypes'] = count($insuranceTypes);
						$insuranceTypes =	count($insuranceTypes) >1 ? implode(", ",$insuranceTypes) : $insuranceTypes[0];
						$contactsQuoteSentArray[$i]['type'] = $insuranceTypes;
						$contactsQuoteSentArray[$i]['opportunity_ids'] = $opportunity_ids;
						$contactsQuoteSentArray[$i]['sales_title'] =  $salesTitle;
					}
//                    $TaskOfOpp = array();
//                    $Task = $Tasks->getTaskByOppId($contactQuoteSentInsuranceTypes[0]['id']);
//                    if(isset($Task) && !empty($Task))
//                    {
//                        $TaskOfOpp = $Task[0];
//                    }
                    $contactsQuoteSentArray[$i]['data'] = $data;
                    $opportunity_id = $contactQuoteSentInsuranceTypes[0]['id'];
                    $contactsQuoteSentArray[$i]['data']['opportunity_id'] = $opportunity_id;
                    $contactsQuoteSentArray[$i]['data']['Tasks'] = $TaskOfOpp;
                    $contactsQuoteSentArray[$i]['pipeline_stage'] = _PIPELINE_STAGE_QUOTE_SENT;
					$i++;
				}
			}

			$pipeline['contacts_quote_sent'] = $contactsQuoteSentArray;
			$pipeline['contacts_quote_sent_count'] = $contacts_quote_sent['count'];

			//projected value
			$pipeline['projected_value_quote_sent'] = $ContactOpportunitiesTable->getProjectedValueByPipelineStageNewVue(_PIPELINE_STAGE_QUOTE_SENT,$login_agency_id,$login_user_id,$login_role_type,$login_role_type_flag,$searchData);
			// end here


			//lost
			$contacts_lost = $ContactOpportunitiesTable->opportunitiesByPipelineStageVue(_PIPELINE_STAGE_LOST,$login_agency_id,$login_user_id,$login_role_type,$login_role_type_flag,$searchData,'',_PAGE_TYPE_DEFAULT,$show_data);

			$contactLostInsuranceTypes = '';
			$contactsLostArray =[];
			if(isset($contacts_lost) && !empty($contacts_lost))
			{
				$i = 0;
				foreach($contacts_lost['data'] as $key => $data)
				{
					if($data['contact_busines'] != '' && $data['contact_busines'] != null)
					{
						$contactLostInsuranceTypes = $ContactOpportunitiesTable->getlostOpportunitiesByStageContactIdVue(null,_PIPELINE_STAGE_LOST,$login_agency_id,null,$data['contact_busines']['id']);
					}else{
						$contactLostInsuranceTypes = $ContactOpportunitiesTable->getlostOpportunitiesByStageContactIdVue($data['contact']['id'],_PIPELINE_STAGE_LOST,$login_agency_id,null,null);
					}
                    $TaskOfOpp = array();
					if(isset($contactLostInsuranceTypes) && !empty($contactLostInsuranceTypes))
					{
						$insuranceTypes = array();
						$opportunity_ids = array();
                        $salesTitle = array();
						foreach($contactLostInsuranceTypes as $key1 => $type)
						{
							$insurance_type = $type['insurance_type']['type'];
							array_push($insuranceTypes,$type['insurance_type']['type']);
							array_push($opportunity_ids,$type['id']);
							if(isset($type['sales_title']) && $type['sales_title'] != '') {
								array_push($salesTitle, $type['sales_title']);
							}
                            if(isset($type['tasks']) && $type['tasks'] != '')
                            {
                                $TaskOfOpp = $type['tasks'][0];
                            }
						}
						$contactsLostArray[$i]['countContactInsuranceTypes'] = count($insuranceTypes);
						$insuranceTypes =	count($insuranceTypes) >1 ? implode(", ",$insuranceTypes) : $insuranceTypes[0];
						$contactsLostArray[$i]['type'] = $insuranceTypes;
						$contactsLostArray[$i]['opportunity_ids'] = $opportunity_ids;
						$contactsLostArray[$i]['sales_title'] =  $salesTitle;
					}
//                    $TaskOfOpp = array();
//                    $Task = $Tasks->getTaskByOppId($contactLostInsuranceTypes[0]['id']);
//                    if(isset($Task) && !empty($Task))
//                    {
//                        $TaskOfOpp = $Task[0];
//                    }
                    $contactsLostArray[$i]['data'] = $data;
                    $opportunity_id = $contactLostInsuranceTypes[0]['id'];
                    $contactsLostArray[$i]['data']['opportunity_id'] = $opportunity_id;
                    $contactsLostArray[$i]['data']['Tasks'] = $TaskOfOpp;
                    $contactsLostArray[$i]['pipeline_stage'] = _PIPELINE_STAGE_LOST;
					$i++;

				}
			}
			$pipeline['contacts_lost'] = $contactsLostArray;
			$pipeline['contacts_lost_count'] = $contacts_lost['count'];
			// end here

			//lost
			$contacts_won = $ContactOpportunitiesTable->opportunitiesByPipelineStageVue(_PIPELINE_STAGE_WON,$login_agency_id,$login_user_id,$login_role_type,$login_role_type_flag,$searchData,'',_PAGE_TYPE_DEFAULT,$show_data);

			$contactWonInsuranceTypes = '';
			$contactsWonArray =[];
			if(isset($contacts_won) && !empty($contacts_won))
			{
				$i = 0;
				foreach($contacts_won['data'] as $key => $data)
				{
					if($data['contact_busines'] != '' && $data['contact_busines'] != null)
					{
						$contactWonInsuranceTypes = $ContactOpportunitiesTable->getActiveOpportunitiesByStageContactIdVue(null,_PIPELINE_STAGE_WON,$login_agency_id,null,$data['contact_busines']['id']);
					}else{
						$contactWonInsuranceTypes = $ContactOpportunitiesTable->getActiveOpportunitiesByStageContactIdVue($data['contact']['id'],_PIPELINE_STAGE_WON,$login_agency_id,null,null);
					}
                    $TaskOfOpp = array();
					if(isset($contactWonInsuranceTypes) && !empty($contactWonInsuranceTypes))
					{
						$insuranceTypes = array();
						$opportunity_ids = array();
                        $salesTitle = array();
						foreach($contactWonInsuranceTypes as $key1 => $type)
						{
							$insurance_type = $type['insurance_type']['type'];
							array_push($insuranceTypes,$type['insurance_type']['type']);
							array_push($opportunity_ids,$type['id']);
							if(isset($type['sales_title']) && $type['sales_title'] != '') {
								array_push($salesTitle, $type['sales_title']);
							}
                            if(isset($type['tasks']) && $type['tasks'] != '')
                            {
                                $TaskOfOpp = $type['tasks'][0];
                            }
						}
						$contactsWonArray[$i]['countContactInsuranceTypes'] = count($insuranceTypes);
						$insuranceTypes =	count($insuranceTypes) >1 ? implode(", ",$insuranceTypes) : $insuranceTypes[0];
						$contactsWonArray[$i]['type'] = $insuranceTypes;
						$contactsWonArray[$i]['opportunity_ids'] = $opportunity_ids;
						$contactsWonArray[$i]['sales_title'] =  $salesTitle;

					}
//                    $TaskOfOpp = array();
//                    $Task = $Tasks->getTaskByOppId($contactWonInsuranceTypes[0]['id']);
//                    if(isset($Task) && !empty($Task))
//                    {
//                        $TaskOfOpp = $Task[0];
//                    }
                    $contactsWonArray[$i]['data'] = $data;
                    $opportunity_id = $contactWonInsuranceTypes[0]['id'];
                    $contactsWonArray[$i]['data']['opportunity_id'] = $opportunity_id;
                    $contactsWonArray[$i]['data']['Tasks'] = $TaskOfOpp;
                    $contactsWonArray[$i]['pipeline_stage'] = _PIPELINE_STAGE_WON;
					$i++;
				}
			}

            //agency details
            $agencyDetails = $Agency->agencyDetails($login_agency_id);
            $usersTimezone = 'America/Phoenix';
            if(!empty($agencyDetails['us_state_id']))
            {
              $stateDetail = $UsStates->stateDetail($agencyDetails['us_state_id']);
            }
            if(isset($agencyDetails['time_zone']) && !empty($agencyDetails['time_zone']))
            {
              $usersTimezone =  $agencyDetails['time_zone'];
            }
            else if(isset($stateDetail) && !empty($stateDetail))
            {
              $usersTimezone =  $stateDetail->time_zone;
            }

            $currentDate = CommonFunctions::convertUtcToEmployeeTimeZone($usersTimezone,date("Y-m-d"));

            $pipeline['currentDate'] = $currentDate;

			$pipeline['contacts_won'] = $contactsWonArray;
			$pipeline['contacts_won_count'] = $contacts_won['count'];
			$return['Pipeline.pipelineStages'] = [
				$agencyId => $pipeline
			];
		 }catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: Campaign Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
		return $return;

    }


	public static function miniOpportunity($objectData)
    {

      	ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '-1');

		$session = Router::getRequest()->getSession();
		$login_agency_id = $session->read("Auth.User.agency_id");
		$login_user_id = $session->read("Auth.User.user_id");
		$login_role_type = $session->read('Auth.User.role_type');
		$login_first_name = $session->read('Auth.User.first_name');
		$login_last_name = $session->read('Auth.User.last_name');
		$login_role_type_flag = $session->read('Auth.User.role_type_flag');
		$login_permissions = $session->read('Auth.User.permissions');
		$login_permissions_arr = explode(",",$login_permissions);


        // $allow_video_proposal_status=$this->request->query('allow_video_proposal_status');
        // $quote_type_video_proposal=$this->request->query('quote_type_video_proposal');
		$UserLinks = TableRegistry::getTableLocator()->get('UserLinks');
		$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
		$Users = TableRegistry::getTableLocator()->get('Users');
		$Contacts = TableRegistry::getTableLocator()->get('Contacts');
		$LeadSource = TableRegistry::getTableLocator()->get('LeadSource');
		$CampaignRunningSchedule = TableRegistry::getTableLocator()->get('CampaignRunningSchedule');
		$ContactBusiness = TableRegistry::getTableLocator()->get('ContactBusiness');

        $agency_users_list= $UserLinks->agencyUsersListWithAgency($login_agency_id);//owner list

        if(!empty($objectData)){
			$opportunity_id = $objectData['opportunity_id'];
          	$contactpipelinelink = $ContactOpportunities->get($opportunity_id, [ ]);
			$contactDetails = $Contacts->contactDetails($contactpipelinelink['contact_id']);
			//get opp owner name
			$owner_name = "";
			$opp_owner_id = "";
			$ownerDetails = $Users->userDetails($contactpipelinelink['user_id']);
			if(isset($ownerDetails['first_name']) || !empty($ownerDetails['first_name']))
			{
				$owner_name .= ucfirst($ownerDetails['first_name']);
				$opp_owner_id = $ownerDetails['id'];
			}
			if(isset($ownerDetails['last_name']) || !empty($ownerDetails['last_name']))
			{
				$owner_name  .=' '.ucfirst($ownerDetails['last_name']);
			}
      		$businessId='';
			$contact_owner_name="";
			$contact_owner_id = "";
			$lead_source_name = '';
			$campaign_name="";
			$stop_campaign_id="";
			$start_campaign_id="";
            $salesTitle = "";

			if(isset($contactpipelinelink['contact_business_id']) && !empty($contactpipelinelink['contact_business_id'])){
				$businessId = $contactpipelinelink['contact_business_id'];
				$business = $ContactBusiness->getContactBusiness($businessId);
				$contactOwnerDetails = $Users->userDetails($business['user_id']);
				if(isset($contactOwnerDetails['first_name']) || !empty($contactOwnerDetails['first_name']))
				{
					$contact_owner_name .= ucfirst($contactOwnerDetails['first_name']);
					$contact_owner_id = $contactOwnerDetails['id'];
				}
				if(isset($contactOwnerDetails['last_name']) || !empty($contactOwnerDetails['last_name']))
				{
					$contact_owner_name  .=' '.ucfirst($contactOwnerDetails['last_name']);
				}
				if(isset($business['lead_source_type']) && $business['lead_source_type'])
				{
					$leadSourceDetail = $LeadSource->leadSourceDetails($business['lead_source_type']);
					$lead_source_name = $leadSourceDetail['name'];
				}
				$campaignDetails = $CampaignRunningSchedule->getRunningCampaignListingByContactIDNew(null, $contactpipelinelink['contact_business_id'], $objectData['pipeline_stage_id']);

				if(isset($campaignDetails[0]['agency_campaign_master']) && !empty($campaignDetails[0]['agency_campaign_master']))
				{
					$stop_campaign_id = $campaignDetails[0]['agency_campaign_master']['id'];
					$running_campaign_id = $campaignDetails[0]['id'];
					$campaign_name = $campaignDetails[0]['agency_campaign_master']['name'];
					$campaign_type = $campaignDetails[0]['agency_campaign_master']['type'];
				}

			}
			else if(isset($contactpipelinelink['contact_id']) && !empty($contactpipelinelink['contact_id']))
			{
				$contactDetails = $Contacts->contactDetails($contactpipelinelink['contact_id']);
				$oppOwnerID = ($contactpipelinelink['user_id'])?$contactpipelinelink['user_id']:'';

				//get contact owner name
				$contactOwnerDetails = $Users->userDetails($contactDetails['user_id']);
				if(isset($contactOwnerDetails['first_name']) || !empty($contactOwnerDetails['first_name']))
				{
					$contact_owner_name .= ucfirst($contactOwnerDetails['first_name']);
					$contact_owner_id = $contactOwnerDetails['id'];
				}
				if(isset($contactOwnerDetails['last_name']) || !empty($contactOwnerDetails['last_name']))
				{
					$contact_owner_name  .=' '.ucfirst($contactOwnerDetails['last_name']);
				}

				if(isset($contactDetails['lead_source_type']) && $contactDetails['lead_source_type'])
				{
					$leadSourceDetail = $LeadSource->leadSourceDetails($contactDetails['lead_source_type']);
					$lead_source_name = $leadSourceDetail['name'];
				}

				$campaignDetails = $CampaignRunningSchedule->getRunningCampaignListingByContactIDNew($contactpipelinelink['contact_id'], null, $objectData['pipeline_stage_id']);

				if(isset($campaignDetails[0]['agency_campaign_master']) && !empty($campaignDetails[0]['agency_campaign_master'])) {
					$stop_campaign_id = $campaignDetails[0]['agency_campaign_master']['id'];
					$running_campaign_id = $campaignDetails[0]['id'];
					$campaign_name = $campaignDetails[0]['agency_campaign_master']['name'];
					$campaign_type = $campaignDetails[0]['agency_campaign_master']['type'];

				}
			}

			$return['contact_owner'] = $contact_owner_name;
			$return['opp_owner'] = $owner_name;
			$return['lead_source_name'] = $lead_source_name;
			$return['lead_source_type'] = $contactDetails['lead_source_type'];
			$return['campaign_name'] = $campaign_name;
			$return['opp_owner_id'] = $opp_owner_id;
			$return['contact_owner_id'] = $contact_owner_id;
			$return['creation_date'] = $contactpipelinelink['created'];
			$return['campaign_id'] = $stop_campaign_id;
			$return['campaign_running_id'] = $running_campaign_id;
            $return['sales_title'] = $contactpipelinelink['sales_title'];
            $return['campaign_type'] = $campaign_type;
			// echo "<pre>";print_r($return);die("Dfdsf");
			$return['Pipeline.miniOpportunity'] = [
                $opportunity_id => $return
            ];
            return $return;
    	}
	}



	public static function getFilterData($agencyId, $fields){

		$return = [];
		try
		{
			$myfile = fopen(ROOT."/logs/vuePipeline.log", "a") or die("Unable to open file!");
			$session = Router::getRequest()->getSession();
			$login_agency_id = $session->read('Auth.User.agency_id');
			$login_user_id= $session->read("Auth.User.user_id");
			$login_role_type = 		$session->read('Auth.User.role_type');
			$login_first_name = 		$session->read('Auth.User.first_name');
			$login_last_name = 		$session->read('Auth.User.last_name');
			$login_role_type_flag = 	$session->read('Auth.User.role_type_flag');
			$login_permissions      = $session->read('Auth.User.permissions');
			$login_permissions_arr  =   explode(",",$login_permissions);

			$UsersTable = TableRegistry::getTableLocator()->get('Users');
			$ContactOpportunitiesTable = TableRegistry::getTableLocator()->get('ContactOpportunities');
			$InsuranceTypesTable = TableRegistry::getTableLocator()->get('InsuranceTypes');
			$ReferralPartnerUserTable = TableRegistry::getTableLocator()->get('ReferralPartnerUser');
			$LeadSourceTable = TableRegistry::getTableLocator()->get('LeadSource');
			$CarriersTable = TableRegistry::getTableLocator()->get('Carriers');
			$TagsTable = TableRegistry::getTableLocator()->get('Tags');
			$AgencyCampaignMasterTable = TableRegistry::getTableLocator()->get('AgencyCampaignMaster');
			$UserLinksTable = TableRegistry::getTableLocator()->get('UserLinks');
			$ContactNoteTypesTable = TableRegistry::getTableLocator()->get('ContactNoteTypes');
			$AgencyTable = TableRegistry::getTableLocator()->get('Agency');
			$UsStatesTable = TableRegistry::getTableLocator()->get('UsStates');
			$TaskCategoriesTable = TableRegistry::getTableLocator()->get('TaskCategories');
			$BusinessStructureTypeTable = TableRegistry::getTableLocator()->get('BusinessStructureType');
			$userDetails = $UsersTable->userDetails($login_user_id);
			$betaOptInStatus = $userDetails['beta_opt_in'];

			$agency_users_list = $UserLinksTable->agencyUsersListWithAgency($login_agency_id);
			$users = array();
			$deal_owners = array();
			$filter_data = array();
			$i= 0;
			foreach($agency_users_list as $user){
				$users[$i]['id'] = $user['user']['id'];
				$users[$i]['name'] = ucfirst($user['user']['first_name']) .' '.ucfirst($user['user']['last_name']);
				$i++;
			}
			$contact_users = $users;
			$deal_owners = $users;
			$users[$i]['id'] = 'sales_team';
			$users[$i]['name'] = 'Sales Team';
			$insurance_personal = array();
			$insurance_types = array();
			$insurance_types_personal_arr = $InsuranceTypesTable->insuranceListByAgencyIdPersonal($login_agency_id);
			if(isset($insurance_types_personal_arr) && !empty($insurance_types_personal_arr))
			{
				array_push($insurance_types,array('header'=>'Personal policies'));
				foreach ($insurance_types_personal_arr as $insurance_types_personal) {
					$insurance_personal['id'] = $insurance_types_personal['id'];
					$insurance_personal['name'] = $insurance_types_personal['type'];
					array_push($insurance_types,$insurance_personal);
				}

			}

			$insurance_types_commercial_arr = $InsuranceTypesTable->insuranceListByAgencyIdCommercial($login_agency_id);
			$insurance_commercial = array();
			if(isset($insurance_types_commercial_arr) && !empty($insurance_types_commercial_arr))
			{
				array_push($insurance_types,array('header'=>'Commercial policies'));
				  foreach ($insurance_types_commercial_arr as $insurance_types_commercial) {
					$insurance_commercial['id'] = $insurance_types_commercial['id'];
					$insurance_commercial['name'] = $insurance_types_commercial['type'];
					array_push($insurance_types,$insurance_commercial);
				}
			}

			$lead_source_arr = $LeadSourceTable->activeLeadSourceListByAgencyId($login_agency_id);
			$lead_sources = array();
			if(isset($lead_source_arr) && !empty($lead_source_arr)){
				$i=0;
				foreach($lead_source_arr as $lead_source){
					$lead_sources[$i]['id'] = $lead_source['id'];
					$lead_sources[$i]['name'] = $lead_source['name'];
					$i++;
				}
			}

			$agency_tags = $TagsTable->agencyTags($login_agency_id);
			$tags = array();
			if(isset($agency_tags) && !empty($agency_tags)){
				$i=0;
				foreach($agency_tags as $tag){
					$tags[$i]['id'] = $tag['id'];
					$tags[$i]['name'] = $tag['name'];
					$i++;
				}
			}
			$referral_partner = array();
			$allReferralPartnerList=$ReferralPartnerUserTable->getAllReferralPartnerUsers($login_agency_id);
			if(isset($allReferralPartnerList) && !empty($allReferralPartnerList))
			{
				$i=0;
				foreach ($allReferralPartnerList as $referralPartner) {
					$referral_partner_id = $referralPartner['referral_partner_id'];
					$referralName = $referralPartner['first_name'];
					if(isset($referralPartner['last_name']) && !empty($referralPartner['last_name'])){
						$referralName .= " ".$referralPartner['last_name'];
					}
					$referral_partner[$i]['id'] = $referral_partner_id.','.$referralPartner['id'];
					$referral_partner[$i]['name'] = $referralName;
					$i++;
				}
			}
			$filter_data['users'] = $contact_users;
			$filter_data['opportunity_users'] = $users;
			$filter_data['insurance_types'] = $insurance_types;
			$filter_data['lead_source_arr'] = $lead_sources;
			$filter_data['tags'] = $tags;
			$filter_data['referral_partner'] = $referral_partner;
			$filter_data['deal_owners'] = $deal_owners;


			$return['Pipeline.getFilterData'] = [
				$agencyId => $filter_data
			];

		}catch (\Exception $e) {
            $txt=date('Y-m-d H:i:s').' :: Sales pipeline Filter/Search Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
		return $return;

	}

	public static function updateContactDetails($objectData)
	{
		//echo "<pre>";print_r($objectData);die("dfds");
		try
        {
            $myfile = fopen(ROOT."/logs/contactrail.log", "a") or die("Unable to open file!");
            $session = Router::getRequest()->getSession();
            $login_user_id= $session->read("Auth.User.user_id");
            $login_agency_id= $session->read("Auth.User.agency_id");
            $contacts = TableRegistry::getTableLocator()->get('Contacts');
            $contactDetails = TableRegistry::getTableLocator()->get('ContactDetails');
			$AgencyCampaignMaster = TableRegistry::getTableLocator()->get('AgencyCampaignMaster');
			$UserLinks = TableRegistry::getTableLocator()->get('UserLinks');
			$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
			$Users = TableRegistry::getTableLocator()->get('Users');
			$LeadSource = TableRegistry::getTableLocator()->get('LeadSource');
			$CampaignRunningSchedule = TableRegistry::getTableLocator()->get('CampaignRunningSchedule');
			$BusinessCommunication = TableRegistry::getTableLocator()->get('BusinessCommunication');
			$ContactBusiness = TableRegistry::getTableLocator()->get('ContactBusiness');
			$TeamUserLinks = TableRegistry::getTableLocator()->get('TeamUserLinks');
			$BusinessId = '';
			$id = '';
            if(isset($objectData) && !empty($objectData)){
				if($objectData['contact_id'] != '')
				{
					$id = $objectData['contact_id'];
					$contact = $contacts->get($id, [
						'contain' => []
					]);

					if(isset($objectData['phone']) && !empty($objectData['phone'])){
						$patterns = array('/\-/','/\(/','/\)/','/\+/','/\ /');
						$phone = preg_replace($patterns,'',$objectData['phone']);
						$data['phone'] = trim($phone);
					}

					$data['first_name'] = $objectData['contact_first_name'];
					$data['last_name'] = $objectData['contact_last_name'];
					$data['email'] = trim($objectData['email']);
					$data['lead_source_type'] = $objectData['lead_source_type'];
                    if(is_array($objectData['user_id']) && isset($objectData['user_id']['id']) && !empty($objectData['user_id']['id'])){
                        $data['user_id'] = $objectData['user_id']['id'];
                    }else if(!is_array($objectData['user_id'])){
                        $data['user_id'] = $objectData['user_id'];
                    }
				
					if(isset($objectData['opp_owner_id']) && !empty($objectData['opp_owner_id']) && $objectData['opp_owner_id']['id'] == 'sales_team')
					{	
						$sales_team_owner_id = CommonFunctions::getSalesUserIDToAssignDefault($login_agency_id,_AGENCY_TEAM_SALES,1);
					
						if(isset($sales_team_owner_id['user_id_to_return']) && !empty($sales_team_owner_id['user_id_to_return']))
						{

							$assign_sales_owner_id = $sales_team_owner_id['user_id_to_return'];
						} else
						{
							$round_robin_sales_arr = CommonFunctions::getSalesUserIDbyRoundRobinDefault($login_agency_id,1);

							if(isset($round_robin_sales_arr['user_id_to_return']) && !empty($round_robin_sales_arr['user_id_to_return'])){
								$sales_owner_id=$round_robin_sales_arr['user_id_to_return'];
								$assign_sales_owner_id = $sales_owner_id;
							}
						}

					}else if(isset($objectData['opp_owner_id']) && !empty($objectData['opp_owner_id']) && $objectData['opp_owner_id'] != 'sales_team')
					{
						$assign_sales_owner_id = $objectData['opp_owner_id']['id'];
					}
					if(!empty($assign_sales_owner_id))
					{
						$oppdata['user_id'] = $assign_sales_owner_id;
					}
					//echo "<pre>";print_r($data);die("dfsdfsd");
					$contact = $contacts->patchEntity($contact, $data);
					$contact = $contacts->save($contact);
				}else
				{
					$BusinessId = $objectData['contact_business_id'];
					$business = $ContactBusiness->get($BusinessId, [
						'contain' => []
					]);
					$businessPhone = '';
					if(isset($objectData['phone']) && !empty($objectData['phone'])){
						$patterns = array('/\-/','/\(/','/\)/','/\+/','/\ /');
						$phone = preg_replace($patterns,'',$objectData['phone']);
						//$data['phone'] = $phone;
						$businessPhone = trim($phone);
					}

					$data['name'] = $objectData['business_name'];
					$data['lead_source_type'] = $objectData['lead_source_type'];
					if(is_array($objectData['user_id']) && isset($objectData['user_id']['id']) && !empty($objectData['user_id']['id'])){
                        $data['user_id'] = $objectData['user_id']['id'];
                    }else if(!is_array($objectData['user_id'])){
                        $data['user_id'] = $objectData['user_id'];
                    }
					$assign_sales_owner_id='';

					if(isset($objectData['opp_owner_id']) && !empty($objectData['opp_owner_id']) && $objectData['opp_owner_id']['id'] == 'sales_team')
					{

						$sales_team_owner_id = CommonFunctions::getSalesUserIDToAssignDefault($login_agency_id,_AGENCY_TEAM_SALES,2);

						if(isset($sales_team_owner_id['user_id_to_return']) && !empty($sales_team_owner_id['user_id_to_return']))
						{

							$assign_sales_owner_id = $sales_team_owner_id['user_id_to_return'];
						} else
						{
							$round_robin_sales_arr = CommonFunctions::getSalesUserIDbyRoundRobinDefault($login_agency_id,2);

							if(isset($round_robin_sales_arr['user_id_to_return']) && !empty($round_robin_sales_arr['user_id_to_return'])){
								$sales_owner_id=$round_robin_sales_arr['user_id_to_return'];
								$assign_sales_owner_id = $sales_owner_id;
							}
						}

					}else if(isset($objectData['opp_owner_id']) && !empty($objectData['opp_owner_id']) && $objectData['opp_owner_id'] != 'sales_team')
					{
						$assign_sales_owner_id = $objectData['opp_owner_id']['id'];
					}
					if(!empty($assign_sales_owner_id))
					{
						$oppdata['user_id'] = $assign_sales_owner_id;
					}
					$business = $ContactBusiness->patchEntity($business, $data);
					$business = $ContactBusiness->save($business);
					$business_communication_email = $BusinessCommunication->getActivePrimaryContactEmail($BusinessId);
					//echo "<pre>";print_r($business_communication_email);die("dfsdfsd");
					if(isset($business_communication_email) && !empty($business_communication_email)){
						$updateBusinessEmail = $BusinessCommunication->updateAll(['email_phone'=>$objectData['email']],['id'=>$business_communication_email->id]);

					}else{
						 //$business_additional_contact_phone_arr['contact_id'] = $contact_id;
						 if(isset($objectData['email'])&& !empty($objectData['email']))
						 {
							$business_additional_contact_phone_arr = [];
							$business_additional_contact_phone_arr['contact_business_id'] = $BusinessId;
							$business_additional_contact_phone_arr['business_communication_type'] = _EMAIL;
							$business_additional_contact_phone_arr['primary_flag'] = _STATUS_TRUE;
							$business_additional_contact_phone_arr['email_phone'] = trim($objectData['email']);
							$primary_contact_phone = $BusinessCommunication->newEntity();
							$primary_contact_phone = $BusinessCommunication->patchEntity($primary_contact_phone, $business_additional_contact_phone_arr);
							$BusinessCommunication->save($primary_contact_phone);
						 }
					}
					$business_communication_phone = $BusinessCommunication->getActivePrimaryContactPhone($BusinessId);
					if(isset($business_communication_phone) && !empty($business_communication_phone)){

						$updateBusinessPhone = $BusinessCommunication->updateAll(['email_phone'=>$businessPhone],['id'=>$business_communication_phone->id]);
					}else{
						if(isset($objectData['phone']) && !empty($objectData['phone']))
						{
							$business_additional_contact_email_arr = [];
							$business_additional_contact_email_arr['contact_business_id'] = $BusinessId;
							$business_additional_contact_email_arr['business_communication_type'] = _PHONE;
							$business_additional_contact_email_arr['primary_flag'] = _STATUS_TRUE;
							$business_additional_contact_phone_arr['email_phone'] = trim($objectData['email']);
							$primary_contact_email = $BusinessCommunication->newEntity();
							$primary_contact_email = $BusinessCommunication->patchEntity($primary_contact_email, $business_additional_contact_email_arr);
							$BusinessCommunication->save($primary_contact_email);
						}
					}

				}
				if(isset($objectData['sales_title']))
				{
					$oppdata['sales_title'] = trim($objectData['sales_title']);
				}

                // if($contact || ($business && $updateBusinessEmail && $updateBusinessPhone))
				// {
					//echo "<pre>";print_r($contact);die("dfds");
					$opportunity = $ContactOpportunities->get($objectData['opportunity_id'], [
						'contain' => []
					]);

                    $miniOpportunityData = [
						'opportunity_id' => $objectData['opportunity_id'],
					    'pipeline_stage_id' => $opportunity['pipeline_stage'],
					];

                    if(!empty($oppdata)){
                        $opportunity = $ContactOpportunities->patchEntity($opportunity, $oppdata);
                        $oppResult = $ContactOpportunities->save($opportunity);
                    }
					if(isset($objectData['sales_campaign']) && !empty($objectData['sales_campaign'])){
						$agencyCampaignDetail = $AgencyCampaignMaster->agencyCampaignAndPipelineStageMasterDetail($objectData['sales_campaign']);
						//update the pipeline stage id
						if(!empty($agencyCampaignDetail) && isset($agencyCampaignDetail->agency_campaign_pipeline_stage_master['pipeline_stage_id']) && !empty($agencyCampaignDetail->agency_campaign_pipeline_stage_master['pipeline_stage_id']))
						{
							if($opportunity->pipeline_stage != _PIPELINE_STAGE_WON)
							{
								$ContactOpportunities->updateAll(['pipeline_stage' =>$agencyCampaignDetail->agency_campaign_pipeline_stage_master['pipeline_stage_id']],['id' => $objectData['opportunity_id']]);
							}
							if($agencyCampaignDetail->type == _CAMPAIGN_TYPE_PIPELINE && $agencyCampaignDetail->agency_campaign_pipeline_stage_master['pipeline_stage_id'] == _PIPELINE_STAGE_APPOINTMENT_SCHEDULED)
							{
									$appointment_date_con = '';
									if(!empty($BusinessId))
									{
										$campaign_result = CommonFunctions::startBeforeAfterTypeCampaign(null, $objectData['sales_campaign'], $agencyCampaignDetail->agency_campaign_pipeline_stage_master['pipeline_stage_id'], null, null, null, null, $BusinessId, $appointment_date_con);
									}else
									{
										$campaign_result = CommonFunctions::startBeforeAfterTypeCampaign($id, $objectData['sales_campaign'], $agencyCampaignDetail->agency_campaign_pipeline_stage_master['pipeline_stage_id'], null, null, null, null, null, $appointment_date_con);
									}
							}
							else
							{	
								if(!empty($BusinessId))
								{
									$campaign_result = CommonFunctions::startCampaign(null, $objectData['sales_campaign'], $agencyCampaignDetail->agency_campaign_pipeline_stage_master['pipeline_stage_id'], null, null, null, null, $BusinessId);

								}else
								{
									$campaign_result = CommonFunctions::startCampaign($id, $objectData['sales_campaign'], $agencyCampaignDetail->agency_campaign_pipeline_stage_master['pipeline_stage_id']);
								}
							}
						}
					}
					else if(empty($objectData['sales_campaign']) && !empty($objectData['campaign_running_id']))
					{
						$contactCampaignsClass = new ContactCampaigns();
						$objectArray = ['campaign_running_schedule_id' => $objectData['campaign_running_id']];
						$campaign_result = $contactCampaignsClass->stopCampaignContactCard($objectArray);
					}
					if(isset($oppResult) && !empty($oppResult)){
						if(isset($round_robin_sales_arr['team_user_link_id']) && !empty($round_robin_sales_arr['team_user_link_id'])){
							$team_user_link_id=$round_robin_sales_arr['team_user_link_id'];
							$teamUserLinkDetails=$TeamUserLinks->teamUserLinkDetails($team_user_link_id);
							if(isset($teamUserLinkDetails) && !empty($teamUserLinkDetails))
								$current_lead=$teamUserLinkDetails['current_lead']+1;
								$TeamUserLinks->updateAll(['current_lead'=>$current_lead],['id'=>$team_user_link_id]);
						}
						elseif(isset($sales_team_owner_id['team_user_link_id']) && !empty($sales_team_owner_id['team_user_link_id']))
						{
								$team_user_link_id=$sales_team_owner_id['team_user_link_id'];
								$teamUserLinkDetails=$TeamUserLinks->teamUserLinkDetails($team_user_link_id);
								if(isset($teamUserLinkDetails) && !empty($teamUserLinkDetails))
								$current_lead=$teamUserLinkDetails['current_lead']+1;
								$TeamUserLinks->updateAll(['current_lead'=>$current_lead],['id'=>$team_user_link_id]);
						}
						//get contact owner name
						$contact_owner_name="";
						$contact_owner_id = "";
						if($objectData['contact_id'] != '')
						{
							$contactOwnerDetails = $Users->userDetails($contact->user_id);
						}else
						{
							$contactOwnerDetails = $Users->userDetails($business->user_id);
						}

						if(isset($contactOwnerDetails['first_name']) || !empty($contactOwnerDetails['first_name']))
						{
							$contact_owner_name .= $contactOwnerDetails['first_name'];
							$contact_owner_id = $contactOwnerDetails['id'];
						}
						if(isset($contactOwnerDetails['last_name']) || !empty($contactOwnerDetails['last_name']))
						{
							$contact_owner_name  .=' '.$contactOwnerDetails['last_name'];
						}

						//get opp owner name
						$owner_name = "";
						$opp_owner_id = "";
						$ownerDetails = $Users->userDetails($oppResult->user_id);
						if(isset($ownerDetails['first_name']) || !empty($ownerDetails['first_name']))
						{
							$owner_name .= $ownerDetails['first_name'];
							$opp_owner_id = $ownerDetails['id'];
						}
						if(isset($ownerDetails['last_name']) || !empty($ownerDetails['last_name']))
						{
							$owner_name  .=' '.$ownerDetails['last_name'];
						}

					}
					//echo "<pre>";print_r($oppResult);die("dsfdsf");
					$lead_source_name = '';
					if(isset($contact->lead_source_type) && $contact->lead_source_type)
					{
						$leadSourceDetail = $LeadSource->leadSourceDetails($contact->lead_source_type);
						$lead_source_name = $leadSourceDetail['name'];
					}
					if(isset($business->lead_source_type) && $business->lead_source_type)
					{
						$leadSourceDetail = $LeadSource->leadSourceDetails($business->lead_source_type);
						$lead_source_name = $leadSourceDetail['name'];
					}
					$campaign_name="";
					$stop_campaign_id="";
					$start_campaign_id="";
					$campaignDetails = $CampaignRunningSchedule->getCampaignDetailsByContactID($id);
					if(isset($campaignDetails['campaign_master']) && !empty($campaignDetails['campaign_master']))
					{
					$stop_campaign_id = $campaignDetails['campaign_master']['id'];
					$campaign_name = $campaignDetails['campaign_master']['name'];

					}
					$oppreturn = Pipeline::miniOpportunity($miniOpportunityData);
					$return['Contact'] = [
						$contact->id => $contact
					];

					$return['Pipeline.miniOpportunity'] = [
						$objectData['opportunity_id'] => $oppreturn
					];
                    return $return;

                // }
            }
        }catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: Contact update Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
	}

	public static function deleteOpportunity($objectData)
	{
        $session = Router::getRequest()->getSession();
		$loginUserId= $session->read("Auth.User.user_id");
		$loginAgencyId= $session->read("Auth.User.agency_id");
		$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
		if(isset($objectData['opportunity_id']) && !empty($objectData['opportunity_id']))
		{
			$opportunity_detail = $ContactOpportunities->contactOpportunityDetail($objectData['opportunity_id']);
            $previousValues = $opportunity_detail;
			if(isset($opportunity_detail) && !empty($opportunity_detail))
			{
				$ContactOpportunities->updateAll(['status'=>_ID_STATUS_DELETED],['id'=>$opportunity_detail['id']]);
				//echo json_encode(array('status' => _ID_SUCCESS));die;
				CommonFunctions::savePolicyLogForContact($opportunity_detail['id']);

                $opportunityDetail = $ContactOpportunities->contactOpportunityDetail($opportunity_detail['id']);
                $newValues = $opportunityDetail;
                $changesDetailsArr = CommonFunctions::createContactOpportunitiesLogsArr($previousValues, $newValues);
                $policyArr = [];
                $policyArr['agency_id'] = $loginAgencyId;
                $policyArr['user_id'] = $loginUserId;
                $policyArr['contact_opportunity_id'] = $opportunityDetail['id'];
                $policyArr['type'] = _POLICY_LOG_TYPE_CHANGE;
                $policyArr['change_details'] = $changesDetailsArr;
                $policyArr['triggered'] = 'User deleted policy manually';
                $policyArr['platform'] = _PLATFORM_TYPE_SYSTEM;
                $policyArr['event_details'] = "deleteOpportunity";
                ContactOpportunityChanger::applyPolicyChanges($opportunityDetail['id'],$policyArr);

				$response['status'] = _ID_SUCCESS;
			}
		}else{
			$response['status'] = _ID_FAILED;
		}
		$return['Pipeline.deleteOpportunity'] = [
			$objectData['opportunity_id'] => $response
		];
		return $return;
		//echo json_encode(array('status' => _ID_FAILED));
	}

	public static function getAllAvailableCampaignsForSales($objectData)
    {
		ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '-1');
        $session = Router::getRequest()->getSession();
		$login_user_id= $session->read("Auth.User.user_id");
		$login_agency_id= $session->read("Auth.User.agency_id");
        $lead_type = $objectData['lead_type'];
        $pipeline_stage_id = $objectData['pipeline_stage_id'];
		$AgencyCampaignMaster = TableRegistry::getTableLocator()->get('AgencyCampaignMaster');
        $list='';
		$campaign_list = array();
        $line = _LINE_PERSONAL;
		if(!empty($objectData['line']))
        {
            $line = $objectData['line'];
        }
        if($lead_type == _CONTACT_TYPE_LEAD && $pipeline_stage_id == _PIPELINE_STAGE_NEW_LEAD)
        {
            // Launch General New Lead Campaign
            $general_new_lead_campaign = $AgencyCampaignMaster->getCampaignsByGeneralType($login_agency_id,_CAMPAIGN_TYPE_GENERAL_NEW_LEAD,$line);
            if(isset($general_new_lead_campaign) && !empty($general_new_lead_campaign)){
				array_push($campaign_list,array('id'=>$general_new_lead_campaign['id'],'name'=>$general_new_lead_campaign['name']));
            }
        }


        if($pipeline_stage_id != _PIPELINE_STAGE_NEW_LEAD)
        {
            if($pipeline_stage_id==_PIPELINE_STAGE_NONE)
            {

				array_push($campaign_list,array('id'=>'','name'=>"Don't Start Long Term Nurture/X-Date  Campaign"));
                $resultLTN = $AgencyCampaignMaster->getCampaignsByGeneralType($login_agency_id,_CAMPAIGN_TYPE_LONG_TERM_NURTURE,$line);
                if(!empty($resultLTN)){
					array_push($campaign_list,array('id'=>$resultLTN['id'],'name'=>$resultLTN['name']));
                }
                $resultXdate = $AgencyCampaignMaster->getCampaignsByGeneralType($login_agency_id,_CAMPAIGN_TYPE_X_DATE,$line);
                if(!empty($resultXdate)){
					array_push($campaign_list,array('id'=>$resultXdate['id'],'name'=>$resultXdate['name']));
                }

            }else{
                $result = $AgencyCampaignMaster->agencyCampaignMasterDetailByPipelineStageId($login_agency_id,$pipeline_stage_id,_CAMPAIGN_TYPE_PIPELINE,$line);
                if(!empty($result)){
						array_push($campaign_list,array('id'=>$result['id'],'name'=>$result['name']));

                    }
				array_push($campaign_list,array('id'=>'','name'=>"Don't Start ".$result['name']." Campaign"));
            }
        }

        else{
            $getEntityDef=getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_NEW_LEAD);
			array_push($campaign_list,array('id'=>'','name'=>"Don't Start ".$getEntityDef." Campaign"));
        }

         //add personal or commercial campaigns to client campaign drop down
        $crossSellCampaigns=$AgencyCampaignMaster->getAllActiveAgencyCampaignsByType($login_agency_id,_CAMPAIGN_TYPE_CROSS_SELL,$line);
        $client_welcome_campaigns = $AgencyCampaignMaster->getAllActiveAgencyCampaignsByType($login_agency_id,_CAMPAIGN_TYPE_CLIENT_WELCOME,$line);
        $service_pipeline_campaigns = $AgencyCampaignMaster->agencyCampaignMasterDetailByPipelineStageId($login_agency_id,_SERVICE_PIPELINE_CAMPAIGN_NEW_SERVICE_REQUEST,_CAMPAIGN_TYPE_SERVICE_PIPELINE,$line);

        $winBackCampaigns = $AgencyCampaignMaster->getAllActiveAgencyCampaignsByType($login_agency_id,_CAMPAIGN_TYPE_WIN_BACK_LOST_CLIENTS,$line);

        $winbackCampaignList = [];
		if(!empty($winBackCampaigns))
		{
			foreach ($winBackCampaigns as $winBackCampaign)
        	{
			  array_push($winbackCampaignList, array('id' => $winBackCampaign['id'], 'name' => $winBackCampaign['name']));
        	}
		}

        $clientCampaignList = [];
		if(!empty($crossSellCampaigns))
		{
			foreach ($crossSellCampaigns as $crossSellCampaign)
        	{
			  array_push($clientCampaignList, array('id' => $crossSellCampaign['id'], 'name' => $crossSellCampaign['name']));
        	}
		}
        
		if(!empty($client_welcome_campaigns))
		{
			foreach ($client_welcome_campaigns as $client_welcome_campaign)
			{
			  array_push($clientCampaignList,array('id' => $client_welcome_campaign['id'], 'name' => $client_welcome_campaign['name']));
			}
		}

        if(!empty($service_pipeline_campaigns))
        {
		   array_push($clientCampaignList, array('id' => $service_pipeline_campaigns['id'], 'name' => $service_pipeline_campaigns['name']));
        }

		$response =  json_encode(array('status' => _ID_SUCCESS, 'winback_campaign_list' => $winbackCampaignList, 'client_campaign_list' => $clientCampaignList, 'campaign_array_list' => $campaign_list));
		return $response;

    }

	public static function getSalesAttachments($opportunity_id)
	{
		$SaleAttachments = TableRegistry::getTableLocator()->get('SaleAttachments');
		$attachments = $SaleAttachments->getAttachmentByPolicyId($opportunity_id);
		$return['Pipeline.getSalesAttachments'] = [
			$opportunity_id => $attachments
		];
		return $return;
	}

	//search function starts
	public static function getFilterdPipelineData($objectData){
		$session = Router::getRequest()->getSession();
		$login_agency_id = $session->read('Auth.User.agency_id');
		$login_user_id= $session->read("Auth.User.user_id");
		$login_role_type = 		$session->read('Auth.User.role_type');
		$login_first_name = 		$session->read('Auth.User.first_name');
		$login_last_name = 		$session->read('Auth.User.last_name');
		$login_role_type_flag = 	$session->read('Auth.User.role_type_flag');
		$login_permissions      = $session->read('Auth.User.permissions');
		$login_permissions_arr  =   explode(",",$login_permissions);
		$data = [];

		$UsersTable = TableRegistry::getTableLocator()->get('Users');
		$ContactOpportunitiesTable = TableRegistry::getTableLocator()->get('ContactOpportunities');
		$ContactsTable = TableRegistry::getTableLocator()->get('Contacts');
		$InsuranceTypesTable = TableRegistry::getTableLocator()->get('InsuranceTypes');
		$ReferralPartnerUserTable = TableRegistry::getTableLocator()->get('ReferralPartnerUser');
		$LeadSourceTable = TableRegistry::getTableLocator()->get('LeadSource');
		$CarriersTable = TableRegistry::getTableLocator()->get('Carriers');
		$TagsTable = TableRegistry::getTableLocator()->get('Tags');
		$AgencyCampaignMasterTable = TableRegistry::getTableLocator()->get('AgencyCampaignMaster');
		$UserLinksTable = TableRegistry::getTableLocator()->get('UserLinks');
		$ContactNoteTypesTable = TableRegistry::getTableLocator()->get('ContactNoteTypes');
		$AgencyTable = TableRegistry::getTableLocator()->get('Agency');
		$UsStatesTable = TableRegistry::getTableLocator()->get('UsStates');
		$TaskCategoriesTable = TableRegistry::getTableLocator()->get('TaskCategories');
		$BusinessStructureTypeTable = TableRegistry::getTableLocator()->get('BusinessStructureType');
		$userDetails = $UsersTable->userDetails($login_user_id);
		$betaOptInStatus = $userDetails['beta_opt_in'];

		$searchArr=array();
		$searchArr['permissions_arr']=$login_permissions_arr;
		if(isset($objectData['keyword']) && !empty($objectData['keyword'])){
			$keyword=strtolower($objectData['keyword']);
			$patterns = array('/\-/','/\(/','/\)/','/\+/');
			$keyword = preg_replace($patterns,'',$keyword);
			$searchArr['keyword']=$keyword;
		}
		if(isset($objectData['tag_id']) && !empty($objectData['tag_id'])){
			$searchArr['tag_id']=$objectData['tag_id'];
		}
		if(isset($objectData['user_id']) && !empty($objectData['user_id'])){
			$searchArr['user_id']=$objectData['user_id'];
		}
		if(isset($objectData['user_id']) && !empty($objectData['user_id'])){
			$searchArr['user_id']=$objectData['user_id'];
		}else{
			if(isset($login_permissions_arr) && in_array(71, $login_permissions_arr))
			{
				$searchArr['user_id']="";
			}
		}
		if(isset($objectData['insurance_type_id']) && !empty($objectData['insurance_type_id'])){
			$searchArr['insurance_type_id']=$objectData['insurance_type_id'];
		}
		if(isset($objectData['lead_source_id']) && !empty($objectData['lead_source_id'])){
			$searchArr['lead_source'] = $objectData['lead_source_id'];
		}
        if(isset($objectData['all_stage_count']) && !empty($objectData['all_stage_count'])){
			$searchArr['all_stage_count'] = $objectData['all_stage_count'];
		}
		if(isset($objectData['search_referal_partner_selected']) && !empty($objectData['search_referal_partner_selected'])){
			$referal_partner_ids = $objectData['search_referal_partner_selected'];
			$referralIdsArr = explode(",",$referal_partner_ids);
			$referal_partner_user_id = $referralIdsArr[1];
			$searchArr['search_referal_partner_selected']=$referal_partner_user_id;
		}
		$sort_by ="";
		if(isset($objectData['sort_by']) && !empty($objectData['sort_by'])){
			$searchArr['sort_by'] = $objectData['sort_by'];
			$sort_by = $objectData['sort_by'];
		}
		if(isset($objectData['lead_rewrite_ids']) && !empty($objectData['lead_rewrite_ids'])){
		    $searchArr['leads_rewrites']=$objectData['lead_rewrite_ids'];
		}
		if(isset($objectData['contact_or_business_type']) && !empty($objectData['contact_or_business_type'])){
		    $searchArr['personal_commercial_type']=$objectData['contact_or_business_type'][0]['id'];
		}
		if(isset($objectData['offset']) && !empty($objectData['offset'])){
		    $searchArr['offset']=$objectData['offset'];
		}
		if(isset($objectData['stage_type']) && !empty($objectData['stage_type'])){
		    $searchArr['stage_type']=$objectData['stage_type'];
		}
		$isSearch="";
		if($login_role_type_flag==_AGENCY_ROLE_MANAGER){
		   $isSearch='search';
		}
		$show_data = '';
		  if(empty($objectData['keyword']) && empty($objectData['tag_id']) && empty($objectData['user_id']) && empty($objectData['insurance_type_id']) && empty($objectData['lead_source_id'])){
			  $show_data = 'default';
		 }else if(!empty($objectData['keyword'])){
			$show_data = 'search';
		}
		//$contacts_new_lead = $ContactsTable->opportunitiesByPipelineStage(_PIPELINE_STAGE_NEW_LEAD,$login_agency_id,$login_user_id,$login_role_type,$login_role_type_flag,$searchArr,'',_PAGE_TYPE_DEFAULT,$show_data);
		if(isset($searchArr['offset']) && !empty($searchArr['offset']) && $searchArr['offset'] > 0)
		{
			$filtered_data = Pipeline::pipelineStagesDataWithLimit($login_agency_id,'',$searchArr);
		}else{
			$filtered_data = Pipeline::pipelineStagesData($login_agency_id,'',$searchArr);
		}

		return $filtered_data;
	}
	public static function pipelineStagesDataWithLimit($agencyId, $fields,$searchArr=null){

		$return = [];
		$pipeline = [];
		$myfile = fopen(ROOT."/logs/vuePipeline.log", "a") or die("Unable to open file!");
		$session = Router::getRequest()->getSession();
		$login_agency_id = (int)$agencyId;
		$login_user_id = $session->read("Auth.User.user_id");
		$login_role_type = $session->read('Auth.User.role_type');
		$login_role_type_flag = $session->read('Auth.User.role_type_flag');
		$login_permissions = $session->read('Auth.User.permissions');
		$login_permissions_arr = explode(",",$login_permissions);
		$searchData = [];
		$searchData['permissions_arr']=$login_permissions_arr;
		$show_data = 'default';
		$ContactOpportunitiesTable = TableRegistry::getTableLocator()->get('ContactOpportunities');
		$ContactsTable = TableRegistry::getTableLocator()->get('Contacts');
        $Tasks = TableRegistry::getTableLocator()->get('Tasks');
		if(isset($searchArr) && !empty($searchArr))
		{
			$searchData = $searchArr;
		}
		else if(isset($searchData['permissions_arr']) && (in_array(71, $searchData['permissions_arr']) && $login_role_type_flag!=_AGENCY_ROLE_ADMIN ))
		{
			$searchData['user_id']=$login_user_id;
		}
		$pipelineData = $ContactOpportunitiesTable->opportunitiesByPipelineStageVue($searchData['stage_type'],$login_agency_id,$login_user_id,$login_role_type,$login_role_type_flag,$searchData,'',_PAGE_TYPE_DEFAULT,$show_data);
		$contactPipelineStageInsuranceTypes = '';
		$contactsPipelineStageArray =[];
			if(isset($pipelineData) && !empty($pipelineData))
			{
				$i = 0;
				foreach($pipelineData['data'] as $key => $data)
				{
					if($data['contact_busines'] != '' && $data['contact_busines'] != null)
					{
						$contactPipelineStageInsuranceTypes = $ContactOpportunitiesTable->getActiveOpportunitiesByStageContactIdVue(null,$searchData['stage_type'],$login_agency_id,null,$data['contact_busines']['id']);
					}else{
						$contactPipelineStageInsuranceTypes = $ContactOpportunitiesTable->getActiveOpportunitiesByStageContactIdVue($data['contact']['id'],$searchData['stage_type'],$login_agency_id,null,null);
					}
					//$contactPipelineStageInsuranceTypes =  $ContactOpportunitiesTable->getActiveOpportunitiesByStageContactIdVue($data['id'],$searchData['stage_type'],$login_agency_id,null,null);
                    $TaskOfOpp = array();
					if(isset($contactPipelineStageInsuranceTypes) && !empty($contactPipelineStageInsuranceTypes))
					{
						$insuranceTypes = array();
						foreach($contactPipelineStageInsuranceTypes as $key1 => $type)
						{
							$insurance_type = $type['insurance_type']['type'];
							array_push($insuranceTypes,$type['insurance_type']['type']);
                            if(isset($type['tasks']) && $type['tasks'] != '')
                            {
                                $nearestTask = Pipeline::findNearestDueDateTask($type['tasks'], $login_agency_id);
                                $TaskOfOpp = $nearestTask;
                            }
						}
						$contactsPipelineStageArray[$i]['countContactInsuranceTypes'] = count($insuranceTypes);
						$insuranceTypes =	count($insuranceTypes) >1 ? implode(", ",$insuranceTypes) : $insuranceTypes[0];
						$contactsPipelineStageArray[$i]['type'] = $insuranceTypes;
					}
//                    $TaskOfOpp = array();
//                    $Task = $Tasks->getTaskByOppId($contactPipelineStageInsuranceTypes['data'][0]['id']);
//                    if(isset($Task) && !empty($Task))
//                    {
//                        $TaskOfOpp = $Task[0];
//                    }
					$contactsPipelineStageArray[$i]['data'] = $data;
					$opportunity_id = $contactPipelineStageInsuranceTypes[0]['id'];
					$contactsPipelineStageArray[$i]['data']['opportunity_id'] = $opportunity_id;
                    $contactsPipelineStageArray[$i]['data']['Tasks'] = $TaskOfOpp;
					$i++;
				}
			}

			$pipeline['pipelineData'] = $contactsPipelineStageArray;
			$return['Pipeline.pipelineStagesWithLimit'] = [
				$agencyId => $pipeline
			];
			return $return;
	}

	public static function savePipelineStage($objectData)
		{
		ini_set('memory_limit', '-1');
		ini_set('max_execution_time', '-1');
		$session = Router::getRequest()->getSession();
		$login_agency_id = $login_agency_id = $session->read('Auth.User.agency_id');
		$login_user_id= $session->read("Auth.User.user_id");
		$login_role_type = $session->read('Auth.User.role_type');
		$login_role_type_flag = $session->read('Auth.User.role_type_flag');
		$login_permissions = $session->read('Auth.User.permissions');
		$login_permissions_arr = explode(",",$login_permissions);

		$Users = TableRegistry::getTableLocator()->get('Users');
		$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
		$Contacts = TableRegistry::getTableLocator()->get('Contacts');
		$ContactBusiness = TableRegistry::getTableLocator()->get('ContactBusiness');
		$AgencyCampaignMaster = TableRegistry::getTableLocator()->get('AgencyCampaignMaster');
		$Agency = TableRegistry::getTableLocator()->get('Agency');
		$ContactPolicyRenewal = TableRegistry::getTableLocator()->get('ContactPolicyRenewal');
		$ContactMultipolicy = TableRegistry::getTableLocator()->get('ContactMultipolicy');
		$CampaignRunningSchedule = TableRegistry::getTableLocator()->get('CampaignRunningSchedule');
		$CampaignRunningEmailSmsSchedule = TableRegistry::getTableLocator()->get('CampaignRunningEmailSmsSchedule');
		$Tasks = TableRegistry::getTableLocator()->get('Tasks');
		$TaskNotes = TableRegistry::getTableLocator()->get('TaskNotes');
        $opportunity_id=$objectData['opportunity_id'];
		$pipeline_stage=$objectData['pipeline_stage'];

        $usStates = TableRegistry::getTableLocator()->get('UsStates');
        $pipelineStages = TableRegistry::getTableLocator()->get('PipelineStages');
        $stage = $pipelineStages->getStageDetail($login_agency_id, $pipeline_stage);
        $newStage = ucwords($stage['stage_name']);
        $usersTimezone = 'America/Phoenix';
        $agencyTimeZone = $session->read('Auth.User.agency_session_detail.time_zone');
        if(isset($agencyTimeZone) && $agencyTimeZone != '')
        {
            $usersTimezone = $agencyTimeZone;
        }
        else
        {
            $agency_state_id = $session->read('Auth.User.agency_session_detail.us_state_id');
            if (isset($agency_state_id) && $agency_state_id != '')
            {
                $stateDetail = $usStates->stateDetail($agency_state_id);
                if (isset($stateDetail) && !empty($stateDetail))
                {
                    $usersTimezone = $stateDetail->time_zone;
                }
            }
        }


		$opportunity_array=$objectData['opp_array'];
		$current_pipeline_stage=$objectData['current_pipeline_stage'];
		$contact_id="";
		if(!empty($objectData['contact_id']))
		{
		  $contact_id=$objectData['contact_id'];
		}
		if(!empty($objectData['contact_business_id']))
		{
		  $contact_business_id=$objectData['contact_business_id'];
		}

		$getLastInsertedRow = $ContactOpportunities->find('all')->order(['id' =>'desc'])->limit(1)->hydrate(false)->first();
		if(isset($getLastInsertedRow) && !empty($getLastInsertedRow))
		{
		  $oppMaxId= $getLastInsertedRow['id'];
		  $count_li_of_ul = $oppMaxId;
		}

		$searchArr = [];
		if(!empty($objectData['searchArr']['user_id']))
		{
		  $searchArr['user_id'] = $objectData['searchArr']['user_id'];
		}
		if(isset($objectData['searchArr']['keyword']) && !empty($objectData['searchArr']['keyword'])){
		  $keyword=strtolower($objectData['searchArr']['keyword']);
		  $patterns = array('/\-/','/\(/','/\)/','/\+/');
		  $keyword = preg_replace($patterns,'',$keyword);
		  $searchArr['keyword']=$keyword;
		}
		if(isset($objectData['searchArr']['tag_id']) && !empty($objectData['searchArr']['tag_id']))
		{
		  $searchArr['tag_id']=$objectData['searchArr']['tag_id'];
		}
		if(isset($objectData['searchArr']['insurance_type_id']) && !empty($objectData['searchArr']['insurance_type_id']))
		{
		  $searchArr['insurance_type_id']=$objectData['searchArr']['insurance_type_id'];
		}

		$lead_source ="";
		if(isset($objectData['searchArr']['lead_source_id']) && !empty($objectData['searchArr']['lead_source_id']))
		{
		  $lead_source = $objectData['searchArr']['lead_source_id'];
		}
		$opp_type ="";
		if(isset($objectData['searchArr']['leads_rewrites']) && !empty($objectData['searchArr']['leads_rewrites'])){
		  $opp_type = $objectData['searchArr']['leads_rewrites'];
		  $searchArr['leads_rewrites']=$opp_type;
		}

		if(isset($objectData['searchArr']['contact_or_business_type']) && !empty($objectData['searchArr']['contact_or_business_type']))
		{
		  $personal_commercial_type = $objectData['searchArr']['contact_or_business_type'];
		  $searchArr['personal_commercial_type']=$personal_commercial_type[0]['id'];
		}
		//main opp details
		$sales_detail='';
		if(isset($opportunity_id) && !empty($opportunity_id))
		{
			$main_contact_opportunitie = $ContactOpportunities->get($opportunity_id);
			if(isset($main_contact_opportunitie) && !empty($main_contact_opportunitie))
			{
                $current_pipeline_stage = $main_contact_opportunitie['pipeline_stage'];
			  $sales_detail=$main_contact_opportunitie['sales_detail'];

			  $contactDetail='';
			  $businessDetail='';
			  if(isset($main_contact_opportunitie['contact_id']) && !empty($main_contact_opportunitie['contact_id']) && empty($main_contact_opportunitie['contact_business_id']))
			  {
				$ContactOpportunities->updateAll(['move_to_won' => _ID_STATUS_PENDING],['contact_id' => $main_contact_opportunitie['contact_id'],'pipeline_stage'=>_PIPELINE_STAGE_WON,'agency_id'=>$login_agency_id,'contact_business_id IS NULL']);
				$contactDetail = $Contacts->contactDetails($main_contact_opportunitie['contact_id']);
			  }
			  if(isset($main_contact_opportunitie['contact_business_id']) && !empty($main_contact_opportunitie['contact_business_id']))
			  {
				$ContactOpportunities->updateAll(['move_to_won' => _ID_STATUS_PENDING],['contact_business_id' => $main_contact_opportunitie['contact_business_id'],'pipeline_stage'=>_PIPELINE_STAGE_WON,'agency_id'=>$login_agency_id]);
				$businessDetail = $ContactBusiness->getContactBusiness($main_contact_opportunitie['contact_business_id']);
			  }
			}
		}


		//get all opportunities by contact id and current stage wise
		$all_contact_opportunities = $ContactOpportunities->getAllOpportunitiesByContactId($contact_id,$current_pipeline_stage,$login_agency_id,$contact_business_id);
		if(isset($all_contact_opportunities) && !empty($all_contact_opportunities))
		{
		  //all opp loop start here
		  foreach($all_contact_opportunities as $key=>$oppValue)
		  {
			$contact_opportunities = $ContactOpportunities->get($oppValue['id']);
			$check_appointment_stage = $contact_opportunities['pipeline_stage'];
			$info=array();
			$info['pipeline_stage']=$pipeline_stage;
			if($pipeline_stage==_PIPELINE_STAGE_WON)
			{
			  $info['won_date']=date('Y-m-d');
			  $info['status']=_ID_STATUS_ACTIVE;
			  if(empty($oppValue['effective_date']))
			  {
				 $info['status'] = _ID_STATUS_PENDING;
			  }
			  if(isset($current_pipeline_stage) && !empty($current_pipeline_stage)){
			  $info['move_to_won']=_ID_STATUS_ACTIVE;
			  }
			  //if policy won make contact as a client
			  if(isset($contact_opportunities['contact_business_id']) && !empty($contact_opportunities['contact_business_id']))
			  {
				$ContactBusiness->updateAll(['lead_type' => _CONTACT_TYPE_CLIENT],['id' => $contact_opportunities['contact_business_id']]);
			  }
			  else
			  {
				$Contacts->updateAll(['lead_type' => _CONTACT_TYPE_CLIENT],['id' => $contact_opportunities->contact_id]);
					//set client since 
					if($contactDetail['client_since'] == null &&  $main_contact_opportunitie['effective_date'] != null){
						$Contacts->updateAll(['client_since' => date('Y-m-d', strtotime($main_contact_opportunitie['effective_date']))], ['id' => $contact_opportunities->contact_id, 'client_since is null']);
					}
				
			  }
			  if(isset($businessDetail) && !empty($businessDetail))
			  {
				if($businessDetail['client_welcome_campaign_flag']==_STATUS_FALSE)
				{
					$active_inactive_business_opp = $ContactOpportunities->getAllActiveCancelledOpportunitiesByBusinessId($businessDetail['id']);
					if(empty($active_inactive_business_opp))
					{
						$checkCampaignExist=$AgencyCampaignMaster->checkCampaignExist(null,null,$login_agency_id,_CLIENT_WELCOME_STAGE_INITIATE,_CAMPAIGN_TYPE_CLIENT_WELCOME,null,_LINE_COMMERCIAL);
						if(!empty($checkCampaignExist)){
						  $start_campaign_id_welcome=$checkCampaignExist['id'];
						  $campaign_result = CommonFunctions::startCampaign(null, $start_campaign_id_welcome,_CLIENT_WELCOME_STAGE_INITIATE,null,null,null,null,$businessDetail['id']);
						  $ContactBusiness->updateAll(['client_welcome_campaign_flag'=>_STATUS_TRUE],['id'=>$businessDetail['id']]);
						}
					}
				}
			  }
			  else
			  {
				if($contactDetail['client_welcome_campaign_flag']==_STATUS_FALSE)
				{
					$active_inactive_contact_opp = $ContactOpportunities->getAllActiveCancelledOpportunitiesByContactId($contactDetail['id']);
					//print_r($active_inactive_contact_opp);die;
					if(empty($active_inactive_contact_opp))
					{
					  $checkCampaignExist=$AgencyCampaignMaster->checkCampaignExist(null,null,$login_agency_id,_CLIENT_WELCOME_STAGE_INITIATE,_CAMPAIGN_TYPE_CLIENT_WELCOME);
					  if(!empty($checkCampaignExist)){
						 $start_campaign_id_welcome=$checkCampaignExist['id'];
						$campaign_result = CommonFunctions::startCampaign($contactDetail['id'], $start_campaign_id_welcome,_CLIENT_WELCOME_STAGE_INITIATE);
						$Contacts->updateAll(['client_welcome_campaign_flag'=>_STATUS_TRUE],['id'=>$contactDetail['id']]);
					  }
					}
				}
			  }
			  //code to start the client welcome campagin end

			  // create Post Binding Obligations  Tasks
			  $post_Obligations = [];
			  $post_Obligations['contact_id'] =$contact_opportunities->contact_id;
			  $post_Obligations['contact_business_id'] =$contact_opportunities->contact_business_id ;
			  $post_Obligations['agency_id'] =$contact_opportunities->agency_id ;
			  $post_Obligations['user_id'] =$contact_opportunities->user_id ;
			  $post_Obligations['insurance_type_id'] =$contact_opportunities->insurance_type_id ;
			  $post_Obligations['carrier_id'] =$contact_opportunities->carrier_id ;
			  $post_Obligations['effective_date'] =$contact_opportunities->effective_date ;
			  $post_Obligations['policy_number'] =$contact_opportunities->policy_number;
			  $post_Obligations['opportunity_id'] =$contact_opportunities->id;
			  CommonFunctions::addPostObligationTask($post_Obligations);
			  // end Post Binding Obligations  Tasks

			   // update pending_sub_status and active_sub_status
				if(date('Y-m-d' ,strtotime($contact_opportunities['effective_date'])) <= date('Y-m-d')){
				$info['pending_sub_status'] = NULL;
				$info['active_sub_status'] = _ID_ACTIVE_SUB_STATUS_ACTIVE;
				}elseif(date('Y-m-d' ,strtotime($contact_opportunities['effective_date'])) > date('Y-m-d'))
				{
					$info['pending_sub_status'] = NULL;
					$info['active_sub_status'] = _ID_ACTIVE_SUB_STATUS_FUTURE_EFFECTIVE;
				}

			}
			else if($pipeline_stage==_PIPELINE_STAGE_LOST){
			  $info['lost_date']=date('Y-m-d');
			  $info['status'] = _ID_STATUS_INACTIVE;
          	  $info['pending_sub_status'] = _ID_PENDING_SUB_STATUS_LOST;
			  $info['active_sub_status'] = NULL;
			}
			else if($pipeline_stage==_PIPELINE_STAGE_APPOINTMENT_SCHEDULED){
			  //$info['appointment_call_status']=_APPOINTMENT_CALL_STATUS_PENDING;
			  $info['status']=_ID_STATUS_PENDING;
			  $info['pending_sub_status']=_ID_PENDING_SUB_STATUS_NEW_LEAD;
			}
			else if($pipeline_stage==_PIPELINE_STAGE_QUOTE_SENT){
			  $info['quote_sent_date']=date('Y-m-d');
			  $info['pending_sub_status'] = _ID_PENDING_SUB_STATUS_QUOTED;
			}
			else
			{
			  $info['status']=_ID_STATUS_PENDING;
			   if($pipeline_stage == _PIPELINE_STAGE_NEW_LEAD || $pipeline_stage == _PIPELINE_STAGE_WORKING){
				$info['pending_sub_status'] = _ID_PENDING_SUB_STATUS_NEW_LEAD;
			   }elseif($pipeline_stage ==_PIPELINE_STAGE_QUOTE_READY){
					$info['pending_sub_status'] = _ID_PENDING_SUB_STATUS_QUOTED;
			   }
			}
			$info['date_stage_moved']=date('Y-m-d H:i:s');

			$contact_opportunities = $ContactOpportunities->patchEntity($contact_opportunities, $info);
			if($savedContactOpportunity=$ContactOpportunities->save($contact_opportunities))
			{

				//make primary_flag_all_stages true
				if(isset($savedContactOpportunity->contact_business_id) && !empty($savedContactOpportunity->contact_business_id))
				{
				  $ContactOpportunities->updateAll(['primary_flag_all_stages'=>_ID_STATUS_PENDING],['contact_business_id' => $savedContactOpportunity->contact_business_id,'pipeline_stage'=>$pipeline_stage,'agency_id'=>$login_agency_id]);
				  $ContactOpportunities->updateAll(['primary_flag_all_stages'=>_ID_STATUS_ACTIVE],['id' => $savedContactOpportunity->id]);
				}
				else if(isset($savedContactOpportunity->contact_id) && !empty($savedContactOpportunity->contact_id))
				{
				  $ContactOpportunities->updateAll(['primary_flag_all_stages'=>_ID_STATUS_PENDING],['contact_id' => $savedContactOpportunity->contact_id,'pipeline_stage'=>$pipeline_stage,'agency_id'=>$login_agency_id,'contact_business_id IS NULL']);
				  $ContactOpportunities->updateAll(['primary_flag_all_stages'=>_ID_STATUS_ACTIVE],['id' => $savedContactOpportunity->id]);
				}
				//end

				if(!empty($count_li_of_ul))
				{
				  foreach ($opportunity_array as $key => $value)
				  {
					//update sort order on the basis of count
					$ContactOpportunities->updateAll(['sort_order' =>$count_li_of_ul],['id' => $value]);
					$count_li_of_ul--;
				  }
				}

				//mark appointment task completed
				if($check_appointment_stage == _PIPELINE_STAGE_APPOINTMENT_SCHEDULED)
				{
				  if(isset($contact_opportunities['appointment_task_id']) && !empty($contact_opportunities['appointment_task_id']))
				  {
					$task_arr = [];
					$task_arr['status'] = _TASK_STATUS_COMPLETE;
					$taskForUser = $Tasks->get($contact_opportunities['appointment_task_id']);
					$taskForUser = $Tasks->patchEntity($taskForUser, $task_arr);
					$Tasks->save($taskForUser);
					}
				}

				if($pipeline_stage==_PIPELINE_STAGE_WON)
				{
				  //check for entry in contact policy renewal table
				  $contactPolicyRenewalDetail = $ContactPolicyRenewal->getContactPolicyRenewalByOpportunitySuccess($savedContactOpportunity->id);
				  //make first entry insert/update in contact policy renewal
				  $contact_policy_renewal_data = [];
				  $contact_policy_renewal_data['renewal_date']=$savedContactOpportunity->effective_date;
				  $contact_policy_renewal_data['renewal_amount']=$savedContactOpportunity->premium_amount;
				  $contact_policy_renewal_data['term_length']=$savedContactOpportunity->term_length;
				  $contact_policy_renewal_data['term_length_period']=$savedContactOpportunity->term_length_period;
				  $contact_policy_renewal_data['carrier_id']=$savedContactOpportunity->carrier_id;
				  $contact_policy_renewal_data['premium_amount']= CommonFunctions::Replacetext($savedContactOpportunity->premium_amount);
				  $contact_policy_renewal_data['commission_amount']=$savedContactOpportunity->commission_amount;
				  $contact_policy_renewal_data['policy_number']=$savedContactOpportunity->policy_number;
				  $contact_policy_renewal_data['commission_split']=$savedContactOpportunity->commission_split;
				  $contact_policy_renewal_data['commission_split_percentage']=$savedContactOpportunity->commission_split_percentage;
				  if(empty($contactPolicyRenewalDetail))
				  {
					$contact_policy_renewal_data['agency_id']=$savedContactOpportunity->agency_id;
					$contact_policy_renewal_data['contact_id']=$savedContactOpportunity->contact_id;
					$contact_policy_renewal_data['contact_opportunities_id']=$savedContactOpportunity->id;
					$contact_policy_renewal_data['amount_received_date']=date('Y-m-d');
					$contact_policy_renewal_data['stage']=_RENEWAL_STAGE_SUCCESS;
					$contactPolicyRenewalDetail = $ContactPolicyRenewal->newEntity();
					$message = 'in function savePipelineStage, $info = ' . json_encode($contact_policy_renewal_data) . ' contact_opportunities_id '. $savedContactOpportunity->id . 'for  contact id ' . $savedContactOpportunity->contact_id . ' and agency is ' . $savedContactOpportunity->agency_id;
					FileLog::writeLog("contact_policy_renewal_dup_entries", $message);
				  }
				  else
				  {
					$contactPolicyRenewalDetail = $ContactPolicyRenewal->get($contactPolicyRenewalDetail['id']);
				  }
				  $contactPolicyRenewalDetail = $ContactPolicyRenewal->patchEntity($contactPolicyRenewalDetail, $contact_policy_renewal_data);
				  $ContactPolicyRenewal->save($contactPolicyRenewalDetail);

				  //code to make the first opportunity primary end here
				  if(isset($savedContactOpportunity->contact_business_id) && !empty($savedContactOpportunity->contact_business_id))
				  {

					$ContactOpportunities->updateAll(['primary_flag'=>_ID_FAILED],['contact_business_id'=>$contact_opportunities->contact_business_id]);
					$ContactOpportunities->updateAll(['primary_flag'=>_ID_SUCCESS],['id'=>$savedContactOpportunity->id]);
					$opp_from_multipolicy = $ContactMultipolicy->getActiveContactMultiPolicyByBusinessId($contact_opportunities->contact_business_id);
				  }
				  else
				  {

					$checkwonRewrite = $ContactOpportunities->checkwonrewriteStatus($contact_opportunities->contact_id);
					$checkwonRewriteZero = $ContactOpportunities->checkwonrewriteStatuswithzero($contact_opportunities->contact_id);

					if($checkwonRewriteZero>1){
					  $ContactOpportunities->updateAll(['primary_flag'=>_ID_FAILED],['contact_id'=>$contact_opportunities->contact_id,'contact_business_id IS NULL']);
					  $ContactOpportunities->updateAll(['primary_flag'=>_ID_SUCCESS],['id'=>$savedContactOpportunity->id]);
					}
					else if($checkwonRewrite>1){
					  $ContactOpportunities->updateAll(['primary_flag'=>_ID_FAILED],['contact_id'=>$contact_opportunities->contact_id,'contact_business_id IS NULL']);
					  $ContactOpportunities->updateAll(['primary_flag'=>_ID_SUCCESS],['id'=>$savedContactOpportunity->id]);
					}else{
					  $ContactOpportunities->updateAll(['primary_flag'=>_ID_SUCCESS],['id'=>$savedContactOpportunity->id]);
					}
					$opp_from_multipolicy = $ContactMultipolicy->getActiveContactMultiPolicyByContactId($contact_opportunities->contact_id);

				  }

				  //create new opportunities from multipolicy table

				  if(isset($opp_from_multipolicy) && !empty($opp_from_multipolicy))
				  {
					foreach ($opp_from_multipolicy as $new_opp)
					{
					  $new_opp_from_multipolicy = $ContactOpportunities->newEntity();
					  $new_opp_data_arr = [];
					  if(isset($contact_opportunities->contact_business_id) && !empty($contact_opportunities->contact_business_id))
					  {
						$new_opp_data_arr['contact_business_id']=$new_opp['contact_business_id'];
					  }
					  else
					  {
						$new_opp_data_arr['contact_id']=$contact_opportunities->contact_id;
					  }
					  $new_opp_data_arr['agency_id']=$contact_opportunities->agency_id;
					  if(!empty($new_opp['user_id'])){
					  $new_opp_data_arr['user_id']=$new_opp['user_id'];
					  }else{
					  $new_opp_data_arr['user_id']=$contact_opportunities->user_id;
					  }
					  if(isset($current_pipeline_stage) && !empty($current_pipeline_stage)){
						$new_opp_data_arr['move_to_won']=_ID_STATUS_ACTIVE;
						}
					  $new_opp_data_arr['pipeline_stage']=$pipeline_stage;
					  $new_opp_data_arr['won_date']=date('Y-m-d');
					  $new_opp_data_arr['status']=_ID_STATUS_ACTIVE;
					  $new_opp_data_arr['insurance_type_id']=$new_opp['insurance_type_id'];

					  $new_opp_data_arr['premium_amount']= CommonFunctions::Replacetext($new_opp['premium_amount']);
					  $new_opp_data_arr['carrier_id']=$new_opp['carrier_type'];
					  $new_opp_data_arr['term_length']=$new_opp['term_length'];
					  $new_opp_data_arr['effective_date']=$new_opp['effective_date'];
					  $new_opp_from_multipolicy = $ContactOpportunities->patchEntity($new_opp_from_multipolicy,$new_opp_data_arr);
					  if($new_opp_from_multipolicy = $ContactOpportunities->save($new_opp_from_multipolicy))
					  {
					  $ContactOpportunities->updateAll(['date_stage_moved' =>date('Y-m-d H:i:s'),'sort_order' =>$new_opp_from_multipolicy->id],['id' => $new_opp_from_multipolicy->id]);
					  $ContactMultipolicy->updateAll(['status'=>_ID_STATUS_MULTIPOLICY_OPP_CREATED],['id'=>$new_opp['id']]);

						  //check for entry in contact multi policy renewal table
						  $contactMultiPolicyRenewalDetail = $ContactPolicyRenewal->getContactPolicyRenewalByOpportunitySuccess($new_opp_from_multipolicy->contact_id,$new_opp_from_multipolicy->id);
						  //make first entry insert/update in contact policy renewal
						  $contact_multi_policy_renewal_data = [];
						  $contact_multi_policy_renewal_data['renewal_date']=$new_opp_from_multipolicy->effective_date;
						  $contact_multi_policy_renewal_data['renewal_amount']=$new_opp_from_multipolicy->premium_amount;
						  $contact_multi_policy_renewal_data['term_length']=$new_opp_from_multipolicy->term_length;
						  $contact_multi_policy_renewal_data['term_length_period']=$new_opp_from_multipolicy->term_length_period;
						  $contact_multi_policy_renewal_data['carrier_id']=$new_opp_from_multipolicy->carrier_id;
						  $contact_multi_policy_renewal_data['premium_amount']= CommonFunctions::Replacetext($new_opp_from_multipolicy->premium_amount);
						  $contact_multi_policy_renewal_data['commission_amount']=$new_opp_from_multipolicy->commission_amount;
						  $contact_multi_policy_renewal_data['policy_number']=$new_opp_from_multipolicy->policy_number;
						  $contact_multi_policy_renewal_data['commission_split']=$new_opp_from_multipolicy->commission_split;
						  $contact_multi_policy_renewal_data['commission_split_percentage']=$new_opp_from_multipolicy->commission_split_percentage;
						  if(empty($contactMultiPolicyRenewalDetail))
						  {
							$contact_multi_policy_renewal_data['agency_id']=$new_opp_from_multipolicy->agency_id;
							$contact_multi_policy_renewal_data['contact_id']=$new_opp_from_multipolicy->contact_id;
							$contact_multi_policy_renewal_data['contact_opportunities_id']=$new_opp_from_multipolicy->id;
							$contact_multi_policy_renewal_data['amount_received_date']=date('Y-m-d');
							$contact_multi_policy_renewal_data['stage']=_RENEWAL_STAGE_SUCCESS;
							$contactMultiPolicyRenewalDetail = $ContactPolicyRenewal->newEntity();
							$message = 'in function savePipelineStage, $info = ' . json_encode($contact_multi_policy_renewal_data) . ' contact_opportunities_id '. $new_opp_from_multipolicy->id . 'for  contact id ' . $new_opp_from_multipolicy->contact_id . ' and agency is ' . $new_opp_from_multipolicy->agency_id;
							FileLog::writeLog("contact_policy_renewal_dup_entries", $message);
						  }
						  else
						  {
							$contactMultiPolicyRenewalDetail = $ContactPolicyRenewal->get($contactMultiPolicyRenewalDetail['id']);
						  }
						  $contactMultiPolicyRenewalDetail = $ContactPolicyRenewal->patchEntity($contactMultiPolicyRenewalDetail, $contact_multi_policy_renewal_data);
						  $ContactPolicyRenewal->save($contactMultiPolicyRenewalDetail);
					  }

					}
				  }


				  // create Post Binding Obligations  Tasks
				  if(isset($savedContactOpportunity->contact_business_id) && !empty($savedContactOpportunity->contact_business_id))
				  {
					$post_Obligations['contact_business_id'] =$savedContactOpportunity->contact_business_id;
				  }
				  else
				  {
					$post_Obligations['contact_id'] =$savedContactOpportunity->contact_id;
				  }
				  $post_Obligations['agency_id'] =$savedContactOpportunity->agency_id ;
				  $post_Obligations['user_id'] =$savedContactOpportunity->user_id ;
				  $post_Obligations['insurance_type_id'] =$savedContactOpportunity->insurance_type_id ;
				  $post_Obligations['carrier_id'] =$savedContactOpportunity->carrier_id ;
				  $post_Obligations['effective_date'] =$savedContactOpportunity->effective_date ;
				  $post_Obligations['policy_number'] =$savedContactOpportunity->policy_number;
				  $post_Obligations['opportunity_id'] = $savedContactOpportunity->id;
				  CommonFunctions::addPostObligationTask($post_Obligations);
				  // end Post Binding Obligations  Tasks
			  }

			  // lost opps expansion starts
			  if($pipeline_stage==_PIPELINE_STAGE_LOST)
			  {
				 //create new opportunities from multipolicy table as loss
				if(isset($contact_opportunities->contact_business_id) && !empty($contact_opportunities->contact_business_id))
				{
				  $opp_from_multipolicy = $ContactMultipolicy->getActiveContactMultiPolicyByBusinessId($contact_opportunities->contact_business_id);
				}
				else
				{
				  $opp_from_multipolicy = $ContactMultipolicy->getActiveContactMultiPolicyByContactId($contact_opportunities->contact_id);
				}
				  if(isset($opp_from_multipolicy) && !empty($opp_from_multipolicy))
				  {
					foreach ($opp_from_multipolicy as $new_opp)
					{
					  $new_opp_from_multipolicy = $ContactOpportunities->newEntity();
					  $new_opp_data_arr = [];
					  if(isset($contact_opportunities->contact_business_id) && !empty($contact_opportunities->contact_business_id))
					  {
						$new_opp_data_arr['contact_business_id']=$new_opp['contact_business_id'];
					  }
					  else
					  {
						$new_opp_data_arr['contact_id']=$contact_opportunities->contact_id;
					  }
					  $new_opp_data_arr['agency_id']=$contact_opportunities->agency_id;
					  $new_opp_data_arr['user_id']=$contact_opportunities->user_id;
					  $new_opp_data_arr['pipeline_stage']=$pipeline_stage;
					  $new_opp_data_arr['lost_date']=date('Y-m-d');
					  $new_opp_data_arr['status']=_ID_STATUS_PENDING;
					  $new_opp_data_arr['insurance_type_id']=$new_opp['insurance_type_id'];
					  $new_opp_from_multipolicy = $ContactOpportunities->patchEntity($new_opp_from_multipolicy,$new_opp_data_arr);
					  if($new_opp_from_multipolicy = $ContactOpportunities->save($new_opp_from_multipolicy))
					  {
						$ContactOpportunities->updateAll(['date_stage_moved' =>date('Y-m-d H:i:s'),'sort_order' =>$new_opp_from_multipolicy->id],['id' => $new_opp_from_multipolicy->id]);
						$ContactMultipolicy->updateAll(['status'=>_ID_STATUS_MULTIPOLICY_OPP_CREATED],['id'=>$new_opp['id']]);
					  }

					}
				  }
				  if(isset($savedContactOpportunity->contact_business_id) && !empty($savedContactOpportunity->contact_business_id))
				  {
					$ContactOpportunities->updateAll(['lost_primary_flag'=>_ID_FAILED],['contact_business_id'=>$contact_opportunities->contact_business_id]);
					$ContactOpportunities->updateAll(['lost_primary_flag'=>_ID_SUCCESS],['id'=>$savedContactOpportunity->id]);
				  }
				  else
				  {
					$ContactOpportunities->updateAll(['lost_primary_flag'=>_ID_FAILED],['contact_id'=>$contact_opportunities->contact_id,'contact_business_id IS NULL']);
					$ContactOpportunities->updateAll(['lost_primary_flag'=>_ID_SUCCESS],['id'=>$savedContactOpportunity->id]);
				  }
			  }



			}//save opp end

			}//opp for loop end



			  //new code to run only same type of campaign once
			  if(isset($main_contact_opportunitie['contact_business_id']) && !empty($main_contact_opportunitie['contact_business_id']))
			  {
				$campaignRunningSchedules = $CampaignRunningSchedule->getRunningCampaignListingByBusinessId($main_contact_opportunitie['contact_business_id']);
			  }
			  else
			  {
				$campaignRunningSchedules = $CampaignRunningSchedule->getRunningCampaignListingByContactID($main_contact_opportunitie['contact_id']);
			  }
			  $checkExitsAnyOpportunities = $ContactOpportunities->getActiveOpportunitiesByContactId($login_agency_id, $contact_business_id, $contact_id);
			  if(!empty($campaignRunningSchedules))
			  {
				  foreach ($campaignRunningSchedules as $campaignRunningSchedule)
				  {
					  if($campaignRunningSchedule['agency_campaign_master']['type'] == _CAMPAIGN_TYPE_PIPELINE || $campaignRunningSchedule['agency_campaign_master']['type'] == _CAMPAIGN_TYPE_NEW_LEAD || $campaignRunningSchedule['agency_campaign_master']['type'] == _CAMPAIGN_TYPE_GENERAL_NEW_LEAD || $campaignRunningSchedule['agency_campaign_master']['type'] == _CAMPAIGN_TYPE_LOAN_OFFICER_HOME_CONDO_PROPERTY || $campaignRunningSchedule['agency_campaign_master']['type'] == _CAMPAIGN_TYPE_CLIENT_WELCOME)
					  {
						if(empty($checkExitsAnyOpportunities) & count($checkExitsAnyOpportunities) == _ID_FAILED && $campaignRunningSchedule['agency_campaign_master']['type'] == _CAMPAIGN_TYPE_CLIENT_WELCOME)
						{
							$CampaignRunningEmailSmsSchedule->updateCampaignRunningEmailSmsSchedule(['status' => _EMAIL_SMS_SENT_STATUS_CANCELLED],['campaign_running_schedule_id' => $campaignRunningSchedule['id'],'status'=>_EMAIL_SMS_SENT_STATUS_PENDING]);
							$CampaignRunningSchedule->updateCampaignRunningSchedule(['status' => _RUNNING_CAMPAIGN_STATUS_COMPLETED],['id'=>$campaignRunningSchedule['id'],'status'=>_RUNNING_CAMPAIGN_STATUS_ACTIVE]);	
						}
						else if($campaignRunningSchedule['agency_campaign_master']['type'] != _CAMPAIGN_TYPE_CLIENT_WELCOME)
						{
							$CampaignRunningEmailSmsSchedule->updateCampaignRunningEmailSmsSchedule(['status' => _EMAIL_SMS_SENT_STATUS_CANCELLED],['campaign_running_schedule_id' => $campaignRunningSchedule['id'],'status'=>_EMAIL_SMS_SENT_STATUS_PENDING]);
							$CampaignRunningSchedule->updateCampaignRunningSchedule(['status' => _RUNNING_CAMPAIGN_STATUS_COMPLETED],['id'=>$campaignRunningSchedule['id'],'status'=>_RUNNING_CAMPAIGN_STATUS_ACTIVE]);
						}

						// $CampaignRunningEmailSmsSchedule->updateCampaignRunningEmailSmsSchedule(['status' => _EMAIL_SMS_SENT_STATUS_CANCELLED],['campaign_running_schedule_id' => $campaignRunningSchedule['id'],'status'=>_EMAIL_SMS_SENT_STATUS_PENDING]);
						// $CampaignRunningSchedule->updateCampaignRunningSchedule(['status' => _RUNNING_CAMPAIGN_STATUS_COMPLETED],['id'=>$campaignRunningSchedule['id'],'status'=>_RUNNING_CAMPAIGN_STATUS_ACTIVE]);

                        $loginUserId = $login_user_id;
                        $campaignStartDateTime = CommonFunctions::convertUtcToEmployeeTimeZone($usersTimezone,date("M d, Y H:i:s"));
                        $campaignStartDate = date('M d, Y', strtotime($campaignStartDateTime));
                        $campaignStartTime = date('h:i:s a', strtotime($campaignStartDateTime));
                        if($loginUserId)
                        {
                           $userData = $Users->get($loginUserId);
                           $firstName = ($userData->first_name) ? ucwords($userData->first_name) : '';
                           $lastName = ($userData->last_name) ? ucwords($userData->last_name) : '';
                           $userName = $firstName .' '. $lastName;
                        }
                        else
                        {
                            $userName = "Better Agency";
                        }
                        $message = "Better Agency stopped the \"" . ucwords($campaignRunningSchedule['agency_campaign_master']['name']) ."\" campaign because \"" . $userName . "\" moved an opportunity to the \"" . $newStage . "\" stage on ". $campaignStartDate ." at ". $campaignStartTime .".";
                        $contactLogsArray['contact_id'] = $contact_id;
                        $contactLogsArray['contact_business_id'] = $contact_business_id;
                        $contactLogsArray['user_id'] = $loginUserId;
                        $contactLogsArray['platform'] = _PLATFORM_TYPE_SYSTEM;
                        $contactLogsArray['message'] = $message;
                        CommonFunctions::insertContactLogsOnUpdate($contactLogsArray);
					  }
					}
				  }
				   /* update campaign task from salespipline*/
				  if(isset($main_contact_opportunitie['contact_business_id']) && !empty($main_contact_opportunitie['contact_business_id']))
				  {
					$campaignRunningSchedules = $CampaignRunningSchedule->getTaskByAllCampaignBusinessId($main_contact_opportunitie['contact_id'],$main_contact_opportunitie['contact_business_id']);
				  }
				  else{
					  $campaignTasks = $CampaignRunningSchedule->getTaskByAllCampaignContactId($main_contact_opportunitie['contact_id']);
				  }
				  if(!empty($campaignTasks) && isset($campaignTasks)){
					 foreach($campaignTasks as $camptsk){
					  if($camptsk['agency_campaign_master']['type']== _CAMPAIGN_TYPE_PIPELINE){
						  $Tasks->updateAll(['status'=>_TASK_STATUS_COMPLETE],['campaign_running_schedule_id'=>$camptsk['id']]);
						  $tskId = $camptsk['tasks']['id'];
						  $taskNote = $TaskNotes->newEntity();
						  $notes =  [];
						  $notes['task_id'] = $tskId;
						  $notes['user_id'] = $login_user_id;
						  $notes['description'] = "It was completed automatically due to pipeline stage move";
						  $taskNote = $TaskNotes->patchEntity($taskNote,$notes);
						  $taskNote = $TaskNotes->save($taskNote);
						}
					  }
				  }
			  //first close all previous same type campaign then run
			  //"Lost" stage for "Pipeline Related" campaigns
			//   if($pipeline_stage==_PIPELINE_STAGE_LOST)
			//   {
			// 	if(isset($main_contact_opportunitie['contact_business_id']) && !empty($main_contact_opportunitie['contact_business_id']))
			// 	{
			// 	  //start of long term and x date campaign if applicable
			// 	  //start the long term nurture campaign if applicable
			// 	  $nurture_contact = $ContactOpportunities->getAllNurtureContactByBusinessId($main_contact_opportunitie['contact_business_id']);
			// 	  $pipeline_stages_to_check=array(_PIPELINE_STAGE_NEW_LEAD,_PIPELINE_STAGE_APPOINTMENT_SCHEDULED,_PIPELINE_STAGE_WORKING,_PIPELINE_STAGE_QUOTE_READY,_PIPELINE_STAGE_QUOTE_SENT,_PIPELINE_STAGE_WON);
			// 	  if(isset($nurture_contact) && !empty($nurture_contact) && !in_array($nurture_contact['contact_opportunities']['pipeline_stage'], $pipeline_stages_to_check))
			// 	  {
			// 		CommonFunctions::startLtnCampaignBusiness($main_contact_opportunitie['contact_business_id']);
			// 		CommonFunctions::startXdateCampaignBusiness($main_contact_opportunitie['contact_business_id']);
			// 	  }
			// 	}
			// 	else
			// 	{
			// 	  //start of long term and x date campaign if applicable
			// 	  //start the long term nurture campaign if applicable
			// 	  $nurture_contact = $ContactOpportunities->getAllNurtureContactByContactId($main_contact_opportunitie['contact_id']);
			// 	  $pipeline_stages_to_check=array(_PIPELINE_STAGE_NEW_LEAD,_PIPELINE_STAGE_APPOINTMENT_SCHEDULED,_PIPELINE_STAGE_WORKING,_PIPELINE_STAGE_QUOTE_READY,_PIPELINE_STAGE_QUOTE_SENT,_PIPELINE_STAGE_WON);
			// 	  if(isset($nurture_contact) && !empty($nurture_contact) && !in_array($nurture_contact['contact_opportunities']['pipeline_stage'], $pipeline_stages_to_check))
			// 	  {
			// 		CommonFunctions::startLtnCampaign($main_contact_opportunitie['contact_id']);
			// 		CommonFunctions::startXdateCampaign($main_contact_opportunitie['contact_id']);
			// 	  }
			// 	}

			//   }
			  //

			  //if policy move in won stage check if long term nurture/x-date campagin running stop
			  if($pipeline_stage==_PIPELINE_STAGE_WON)
			  {
				if(!empty($campaignRunningSchedules))
				{
				  foreach ($campaignRunningSchedules as $campaignRunningSchedule)
				  {
					  if($campaignRunningSchedule['agency_campaign_master']['type'] == _CAMPAIGN_TYPE_LONG_TERM_NURTURE || $campaignRunningSchedule['agency_campaign_master']['type'] == _CAMPAIGN_TYPE_X_DATE)
					  {
						$CampaignRunningEmailSmsSchedule->updateCampaignRunningEmailSmsSchedule(['status' => _EMAIL_SMS_SENT_STATUS_CANCELLED],['campaign_running_schedule_id' => $campaignRunningSchedule['id'],'status'=>_EMAIL_SMS_SENT_STATUS_PENDING]);
						$CampaignRunningSchedule->updateCampaignRunningSchedule(['status' => _RUNNING_CAMPAIGN_STATUS_COMPLETED],['id'=>$campaignRunningSchedule['id'],'status'=>_RUNNING_CAMPAIGN_STATUS_ACTIVE]);
					  }
				  }

				}
			  }
			  //



		  }//if opp exist end block

		$filtered_data = Pipeline::pipelineStages($login_agency_id,'',$searchArr);
        NowCertsApi::updateIntoNowcerts($contact_id, $contact_business_id, $opportunity_id, null);
		 return  $filtered_data;
		}

	/**
     * save appointment date time
    */
    public function saveAppointmentDate($objectData){
		ini_set('memory_limit', '-1');
		ini_set('max_execution_time', '-1');
		$session = Router::getRequest()->getSession();
		$login_agency_id = $login_agency_id = $session->read('Auth.User.agency_id');
		$login_user_id= $session->read("Auth.User.user_id");
		$login_role_type = $session->read('Auth.User.role_type');
		$login_role_type_flag = $session->read('Auth.User.role_type_flag');
		$login_permissions = $session->read('Auth.User.permissions');
		$login_permissions_arr = explode(",",$login_permissions);

		$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
		$Contacts = TableRegistry::getTableLocator()->get('Contacts');
		$Tasks = TableRegistry::getTableLocator()->get('Tasks');
		$ContactAppointments = TableRegistry::getTableLocator()->get('ContactAppointments');

        if(isset($objectData['appointment_date']) && !empty($objectData['appointment_date']) && isset($objectData['appointment_time']) && !empty($objectData['appointment_time']) && isset($objectData['appointment_opportunity_id']) && !empty($objectData['appointment_opportunity_id']))
        {
          $contact_opportunities = $ContactOpportunities->get($objectData['appointment_opportunity_id']);
          //print_r($contact_opportunities['appointment_task_id']);die;
          $appointment_date_time=date("Y-m-d H:i:s",strtotime($objectData['appointment_date'].' '.$objectData['appointment_time']));
          $info=array();
          $info['appointment_date']=$appointment_date_time;
          $info['platform_update'] = _PLATFORM_TYPE_SYSTEM;
          $contact_opportunities = $ContactOpportunities->patchEntity($contact_opportunities, $info);
          if($savedContactOpportunity=$ContactOpportunities->save($contact_opportunities)){

			//make primary_flag_all_stages true
			if(isset($savedContactOpportunity->contact_business_id) && !empty($savedContactOpportunity->contact_business_id))
			{
			$ContactOpportunities->updateAll(['primary_flag_all_stages'=>_ID_STATUS_PENDING],['contact_business_id' => $savedContactOpportunity->contact_business_id,'pipeline_stage'=>$savedContactOpportunity->pipeline_stage,'agency_id'=>$savedContactOpportunity->agency_id]);
			$ContactOpportunities->updateAll(['primary_flag_all_stages'=>_ID_STATUS_ACTIVE],['id' => $savedContactOpportunity->id]);
			}
			else if(isset($savedContactOpportunity->contact_id) && !empty($savedContactOpportunity->contact_id))
			{
			$ContactOpportunities->updateAll(['primary_flag_all_stages'=>_ID_STATUS_PENDING],['contact_id' => $savedContactOpportunity->contact_id,'pipeline_stage'=>$savedContactOpportunity->pipeline_stage,'agency_id'=>$savedContactOpportunity->agency_id,'contact_business_id IS NULL']);
			$ContactOpportunities->updateAll(['primary_flag_all_stages'=>_ID_STATUS_ACTIVE],['id' => $savedContactOpportunity->id]);
			}
			//end

            //create appointment task
            $contactDetails = $Contacts->contactDetails($contact_opportunities['contact_id']);
            $task_arr = [];
            $task_arr['agency_id'] = $contact_opportunities['agency_id'];
            $task_arr['user_id'] = $contact_opportunities['user_id'];
            $task_arr['contact_id'] = $contact_opportunities['contact_id'];
            $contact_first_name = "Contact";
            if(isset($contactDetails['first_name']) && !empty($contactDetails['first_name']))
            {
              $contact_first_name = $contactDetails['first_name'];
            }

            $task_arr['title'] = "Appointment with". " ".$contact_first_name;
            $task_arr['description'] = "Appointment with". " ".$contact_first_name;
            $task_arr['due_date'] = $appointment_date_time;
            $task_arr['status'] = _TASK_STATUS_UNCOMPLETE;
            if(isset($contact_opportunities['appointment_task_id']) && !empty($contact_opportunities['appointment_task_id']))
            {
                $taskForUser = $Tasks->get($contact_opportunities['appointment_task_id']);
            }
            else
            {
              $taskForUser = $Tasks->newEntity();
            }
            $taskForUser = $Tasks->patchEntity($taskForUser, $task_arr);
            $saved_task = $Tasks->save($taskForUser);
            //print($saved_task->id);die;
            if(isset($saved_task) && !empty($saved_task) && (!isset($contact_opportunities['appointment_task_id']) && empty($contact_opportunities['appointment_task_id'])))
            {
              $ContactOpportunities->updateAll(['appointment_task_id'=>$saved_task['id']],['id'=>$savedContactOpportunity['id']]);
            }
            //save the appointment in contact_appointment table
            $contact_appointment_arr = [];
            if(isset($contact_opportunities['contact_business_id']) && !empty($contact_opportunities['contact_business_id']))
            {
              $contact_appointment_arr['contact_business_id'] = $contact_opportunities['contact_business_id'];
            }
            else
            {
              $contact_appointment_arr['contact_id'] = $contact_opportunities['contact_id'];
            }
            $contact_appointment_arr['appointment_date'] = $appointment_date_time;
            $contact_appointment = $ContactAppointments->newEntity();
            $contact_appointment = $ContactAppointments->patchEntity($contact_appointment,$contact_appointment_arr);
            $contact_appointment = $ContactAppointments->save($contact_appointment);
			$return['Pipeline.saveAppointmentDate'] = [
			'status' => _ID_SUCCESS
			];
			return $return;
          }
          else
          {
			$return['Pipeline.saveAppointmentDate'] = [
			'status' => _ID_FAILED
			];
            return $return;
          }
      }else{
			$return['Pipeline.saveAppointmentDate'] = [
			'status' => _ID_FAILED
			];
            return $return;
		}
    }

	//this function is used to set the order on move of lead in same stage
    public function updateOpportunitySortOrder($objectData)
    {
		$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
        if(!empty($objectData['opp_array']))
        {
          $opportunity_array=$objectData['opp_array'];

          $getLastInsertedRow = $ContactOpportunities->find('all')->order(['id' =>'desc'])->limit(1)->hydrate(false)->first();
          if(isset($getLastInsertedRow) && !empty($getLastInsertedRow))
          {
            $oppMaxId= $getLastInsertedRow['id'];
            $count_li_of_ul = $oppMaxId;

            foreach ($opportunity_array as $key => $value)
              {
                //update sort order on the basis of count
                $ContactOpportunities->updateAll(['sort_order' =>$count_li_of_ul],['id' => $value]);
                $count_li_of_ul--;
              }

          }
        }

        return array('status' => _ID_SUCCESS);
    }

	public static function previewCampaign($opp_id){

		try
		{
			$session = Router::getRequest()->getSession();

			$AgencyTable = TableRegistry::getTableLocator()->get('Agency');
			$AgencyCampaignMasterTable = TableRegistry::getTableLocator()->get('AgencyCampaignMaster');
			$CampaignRunningScheduleTable = TableRegistry::getTableLocator()->get('CampaignRunningSchedule');
			$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
			$CampaignRunningEmailSmsScheduleTable = TableRegistry::getTableLocator()->get('CampaignRunningEmailSmsSchedule');
			$UsStatesTable = TableRegistry::getTableLocator()->get('UsStates');
			$AgencySmsTemplatesTable = TableRegistry::getTableLocator()->get('AgencySmsTemplates');
			$PhoneNumbersOptInOutStatusTable = TableRegistry::getTableLocator()->get('PhoneNumbersOptInOutStatus');
			$AgencyEmailTemplatesTable = TableRegistry::getTableLocator()->get('AgencyEmailTemplates');
			$AgencyTaskTemplatesTable = TableRegistry::getTableLocator()->get('AgencyTaskTemplates');
			$AgencyTransitionTemplatesTable = TableRegistry::getTableLocator()->get('AgencyTransitionTemplates');
			$ContactsTable = TableRegistry::getTableLocator()->get('Contacts');
			$AgencyCampaignMasterTable = TableRegistry::getTableLocator()->get('AgencyCampaignMaster');
			$AgencyCampaignPipelineStageMasterTable = TableRegistry::getTableLocator()->get('AgencyCampaignPipelineStageMaster');
			$AgencyCampaignAttachmentsTable = TableRegistry::getTableLocator()->get('AgencyCampaignAttachments');

			$login_agency_id= $session->read("Auth.User.agency_id");

			$opportunity_ids = explode('-',$opp_id);
			$opportunity_id = $opportunity_ids[0];
			$pipeline_stage = $opportunity_ids[1];
			$contactPipelineLink = $ContactOpportunities->get($opportunity_id, [ ]);
			if(isset($contactPipelineLink['contact_business_id']) && !empty($contactPipelineLink['contact_business_id'])){
				$campaignDetails = $AgencyCampaignMasterTable->checkCampaignExist(null,null,$login_agency_id,$pipeline_stage,_CAMPAIGN_TYPE_PIPELINE,null,_LINE_COMMERCIAL);
			}else{
				$campaignDetails = $AgencyCampaignMasterTable->checkCampaignExist(null,null,$login_agency_id,$pipeline_stage,_CAMPAIGN_TYPE_PIPELINE,null,_LINE_PERSONAL);
			}

			$id  =  $campaignDetails['id'];

			$pipeline_stages_arr = [];
			$emailSmsTaskTemplateArray = [];
			$taskTempaleAttachmentsArray =$agencyAttachmentTemplateIds=[];
			$agencyCampaignMaster = $AgencyCampaignMasterTable->get($id,['contain'=>['AgencyCampaignReferralPartnerType']]);
			$pipeliestageData = $AgencyCampaignPipelineStageMasterTable->getPipelineStages($id);
		if(isset($pipeliestageData) && !empty($pipeliestageData))
			{
				foreach ($pipeliestageData as $key => $value)
				{
					$getEntityDef = "";
					if ($agencyCampaignMaster->type == _CAMPAIGN_TYPE_PIPELINE) {
						$getEntityDef = getEntityDef($err, _PIPELINE_CAMPAIGN_STAGE, $value);
					}
					$pipeline_stages_arr[$value] = $getEntityDef;

					$emailTemplates = $AgencyEmailTemplatesTable->getEmailTemplets($value,$id);
					if(isset($emailTemplates) && !empty($emailTemplates))
					{
						$emailSmsTaskTemplateArray = array_merge($emailSmsTaskTemplateArray,$emailTemplates);
					}
					$smsTemplates = $AgencySmsTemplatesTable->getSmsTemplets($value,$id);

					if(isset($smsTemplates) && !empty($smsTemplates))
					{
						$emailSmsTaskTemplateArray = array_merge($emailSmsTaskTemplateArray,$smsTemplates);
					}
					$taskTemplates = $AgencyTaskTemplatesTable->getTaskTemplets($value,$id);
					if(isset($taskTemplates) && !empty($taskTemplates))
					{
						$emailSmsTaskTemplateArray = array_merge($emailSmsTaskTemplateArray,$taskTemplates);
							// Task Template attachments(Added by monika)
					if(isset($taskTemplates) && !empty($taskTemplates)){
						$agencyAttachmentTemplateId =[];
						foreach($taskTemplates as $taskTemplateValue){
							$agencyAttachmentTemplateId[] =isset($taskTemplateValue['id']) && !empty($taskTemplateValue['id']) ? $taskTemplateValue['id'] : '';
						}
						$taskAttachments = $AgencyCampaignAttachmentsTable->getAttachmentDetail($id,$agencyAttachmentTemplateId);
					}

					if(isset($taskAttachments) && !empty($taskAttachments))
					{
						$taskTempaleAttachmentsArray = array_merge($taskTempaleAttachmentsArray,$taskAttachments);

					}
					$taskTempaleAttachmentsCount = count($taskTempaleAttachmentsArray);

					$attachmentTemplateIds = $AgencyCampaignAttachmentsTable->getAgencyTemplateIds($id,$agencyAttachmentTemplateId);

					if(isset($attachmentTemplateIds) && !empty($attachmentTemplateIds))
					{
						$agencyAttachmentTemplateIds = array_merge($agencyAttachmentTemplateIds,$attachmentTemplateIds);

					}
					}

					$transitionTemplates = $AgencyTransitionTemplatesTable->getTransitionTemplates($value,$id);
					if(isset($transitionTemplates) && !empty($transitionTemplates))
					{
						$emailSmsTaskTemplateArray = array_merge($emailSmsTaskTemplateArray,$transitionTemplates);
					}

				}
				$emailSmsTaskTemplate = [];
				$templateTotalNumberOfSeconds = 0;
				$templateTotalNumberOfDays = 0;
				$emailSmsTaskTemplateBefore = [];
				$emailSmsTaskTemplateAfter = [];
				$emailSmsTaskTemplateArrayBefore = [];
				$emailSmsTaskTemplateArrayAfter = [];
				$sortingValue = 0.001;
				foreach ($emailSmsTaskTemplateArray as $key => $row)
				{
					if($agencyCampaignMaster['before_after'] == _BEFORE_AFTER)
					{
						if($row['before_after'] == _BEFORE)
						{
							$emailSmsTaskTemplateArrayBefore[] = $row;
						}
						else
						{
							$emailSmsTaskTemplateArrayAfter[] = $row;
						}
					}
					else
					{
						if($row['delay_time_in_sec']>0)
						{
							$emailSmsTaskTemplate[$key] = $row['delay_time_in_sec'];
							$templateTotalNumberOfSeconds = $templateTotalNumberOfSeconds + $row['delay_time_in_sec'];
						}
						else
						{
							$sort_seconds = 0;
							if(isset($row['delay_minutes']) && !empty($row['delay_minutes']))
							{
								$minutes_to_seconds = strtotime($row['delay_minutes'].' minute', 0);
								$sort_seconds = $sort_seconds + $minutes_to_seconds;
							}
							if(isset($row['delay_hours']) && !empty($row['delay_hours']))
							{
								$hours_to_seconds = strtotime($row['delay_hours'].' hour', 0);
								$sort_seconds = $sort_seconds + $hours_to_seconds;
							}
							if(isset($row['delay_days']) && !empty($row['delay_days']))
							{
								$days_to_seconds = strtotime($row['delay_days'].' day', 0);
								$sort_seconds = $sort_seconds + $days_to_seconds;
							}
							if(isset($row['delay_months']) && !empty($row['delay_months']))
							{
								$months_to_seconds = strtotime($row['delay_months'].' month', 0);
								$sort_seconds = $sort_seconds + $months_to_seconds;
							}
							$emailSmsTaskTemplate[$key] = $sort_seconds + $sortingValue;
							$sortingValue = $sortingValue + 0.001;
							$templateTotalNumberOfSeconds = $templateTotalNumberOfSeconds + $sort_seconds;
						}
					}
				}
				//print_r($emailSmsTaskTemplate);die;
				if($agencyCampaignMaster['before_after'] == _BEFORE)
				{
					array_multisort($emailSmsTaskTemplate, SORT_DESC, $emailSmsTaskTemplateArray);
					//code for after type campaign
					foreach ($emailSmsTaskTemplate as $after_campaign_key => $after_campaign_value)
					{
						if((int)$after_campaign_value == 0)
						{
							$campaign_arr_to_copy = $emailSmsTaskTemplateArray[$after_campaign_key];
							unset($emailSmsTaskTemplateArray[$after_campaign_key]);
							array_unshift($emailSmsTaskTemplateArray, $campaign_arr_to_copy);
						}
					}
				}
				else if($agencyCampaignMaster['before_after'] == _BEFORE_AFTER)
				{
					foreach ($emailSmsTaskTemplateArrayBefore as $key => $row)
					{
						if($row['delay_time_in_sec']>0)
						{
							$emailSmsTaskTemplateBefore[$key] = $row['delay_time_in_sec'];
						}
						else
						{
							$sort_seconds = 0;
							if(isset($row['delay_minutes']) && !empty($row['delay_minutes']))
							{
								$minutes_to_seconds = strtotime($row['delay_minutes'].' minute', 0);
								$sort_seconds = $sort_seconds + $minutes_to_seconds;
							}
							if(isset($row['delay_hours']) && !empty($row['delay_hours']))
							{
								$hours_to_seconds = strtotime($row['delay_hours'].' hour', 0);
								$sort_seconds = $sort_seconds + $hours_to_seconds;
							}
							if(isset($row['delay_days']) && !empty($row['delay_days']))
							{
								$days_to_seconds = strtotime($row['delay_days'].' day', 0);
								$sort_seconds = $sort_seconds + $days_to_seconds;
							}
							if(isset($row['delay_months']) && !empty($row['delay_months']))
							{
								$months_to_seconds = strtotime($row['delay_months'].' month', 0);
								$sort_seconds = $sort_seconds + $months_to_seconds;
							}
							$emailSmsTaskTemplateBefore[$key] = $sort_seconds;
						}
					}
					foreach ($emailSmsTaskTemplateArrayAfter as $key => $row)
					{
						if($row['delay_time_in_sec']>0)
						{
							$emailSmsTaskTemplateAfter[$key] = $row['delay_time_in_sec'];
							$templateTotalNumberOfSeconds = $templateTotalNumberOfSeconds + $row['delay_time_in_sec'];
						}
						else
						{
							$sort_seconds = 0;
							if(isset($row['delay_minutes']) && !empty($row['delay_minutes']))
							{
								$minutes_to_seconds = strtotime($row['delay_minutes'].' minute', 0);
								$sort_seconds = $sort_seconds + $minutes_to_seconds;
							}
							if(isset($row['delay_hours']) && !empty($row['delay_hours']))
							{
								$hours_to_seconds = strtotime($row['delay_hours'].' hour', 0);
								$sort_seconds = $sort_seconds + $hours_to_seconds;
							}
							if(isset($row['delay_days']) && !empty($row['delay_days']))
							{
								$days_to_seconds = strtotime($row['delay_days'].' day', 0);
								$sort_seconds = $sort_seconds + $days_to_seconds;
							}
							if(isset($row['delay_months']) && !empty($row['delay_months']))
							{
								$months_to_seconds = strtotime($row['delay_months'].' month', 0);
								$sort_seconds = $sort_seconds + $months_to_seconds;
							}
							$emailSmsTaskTemplateAfter[$key] = $sort_seconds;
							$templateTotalNumberOfSeconds = $templateTotalNumberOfSeconds + $sort_seconds;
						}
					}
					//print_r($emailSmsTaskTemplateArrayBefore);die;
					array_multisort($emailSmsTaskTemplateBefore, SORT_DESC, $emailSmsTaskTemplateArrayBefore);
					//print_r($emailSmsTaskTemplateArrayBefore);die;
					array_multisort($emailSmsTaskTemplateAfter, SORT_ASC, $emailSmsTaskTemplateArrayAfter);
					$emailSmsTaskTemplateArray = array_merge($emailSmsTaskTemplateArrayBefore, $emailSmsTaskTemplateArrayAfter);
					//print_r($emailSmsTaskTemplateArray);die;
					$emailSmsTaskTemplate = array_merge($emailSmsTaskTemplateBefore, $emailSmsTaskTemplateAfter);
					foreach ($emailSmsTaskTemplate as $after_before_campaign_key => $after_before_campaign_value)
					{
						if((int)$after_before_campaign_value == 0)
						{
							$campaign_arr_to_copy_after_before = $emailSmsTaskTemplateArray[$after_before_campaign_key];
							unset($emailSmsTaskTemplateArray[$after_before_campaign_key]);
							array_unshift($emailSmsTaskTemplateArray, $campaign_arr_to_copy_after_before);
						}
					}
					//print_r($emailSmsTaskTemplateArray);die;
				}
				else
				{
					array_multisort($emailSmsTaskTemplate, SORT_ASC, $emailSmsTaskTemplateArray);
				}

				$templateTotalNumberOfDays = intval(intval($templateTotalNumberOfSeconds) / (3600*24));
				$html = '';

				foreach ($pipeline_stages_arr as $pipeline_key => $pipeline_value)
				{
					$html .= '<div class="card">';
					$cardShowClass = "";
					$html .= '<div id="'.$pipeline_key.'" >
					<div class="card-body-working-campign">
					<div><ul class="list-unstyled">';
						if(!empty($emailSmsTaskTemplateArray))
						{
							$before_after_text="";
							if($agencyCampaignMaster['before_after'] == _BEFORE)
							{
								$before_after_text = "Before";
							}
						//foreach ($emailSmsTaskTemplateArray as $emailSmsTaskTemplates)
						//{
							$filterBy = $pipeline_key;
							$emailSmsTaskData = array_filter($emailSmsTaskTemplateArray, function ($var) use ($filterBy) {
								return ($var['pipeline_stage_id'] == $filterBy);
							});

							if(!empty($emailSmsTaskData))
							{
								foreach ($emailSmsTaskData as $key => $value) {

									if($agencyCampaignMaster['before_after'] == _BEFORE_AFTER)
									{
										if($value['before_after'] == _BEFORE)
										{
											$before_after_text = "Before";
										}
										else
										{
											$before_after_text = "After";
										}
									}
									$fromTimedb = date("H:i:s", strtotime($value['from_time']));
									$toTimedb = date("H:i:s", strtotime($value['to_time']));
									$before_after_db = $value['before_after'];
									$timeNumber = "0";
									$timeText = "";
									$delay = "";
									$months = 0;
									 $days = 0;
									 $hours = 0;
									 $minutes = 0;

									if(isset($value['delay_time_in_sec']) && !empty($value['delay_time_in_sec']) && $value['delay_time_in_sec']>0)
									{
										$days = intval(intval($value['delay_time_in_sec']) / (3600*24));
										if($days> 0){
											$delay = $delay.$days.' Days <br>';
											$timeNumber = $days;
											$timeText = "day";
										}
										/*** get the hours ***/
										$hours = (intval($value['delay_time_in_sec']) / 3600) % 24;
										if($hours > 0){
											$delay = $delay.$hours.' Hours <br>';
											$timeNumber = $hours;
											$timeText = "hour";
										}
										/*** get the minutes ***/
										$minutes = (intval($value['delay_time_in_sec']) / 60) % 60;
										if($minutes> 0){
											$delay = $delay.$minutes.' Minutes <br>';
											$timeNumber = $minutes;
											$timeText = "minute";
										}
									}
									else
									{
										if(!empty($value['delay_months']))
										{
											$months = intval(intval($value['delay_months']));
											if($months> 0)
											{
												$delay = $delay.$months.' Months '.$before_after_text.' <br>';
											}
										}
										if(!empty($value['delay_days']))
										{
											$days = intval(intval($value['delay_days']));
											if($days> 0)
											{
												$delay = $delay.$days.' Days '.$before_after_text.' <br>';
											}
										}
										if(!empty($value['delay_hours']))
										{
											$hours = intval(intval($value['delay_hours']));
											if($hours> 0)
											{
												$delay = $delay.$hours.' Hours '.$before_after_text.' <br>';
											}
										}
										if(!empty($value['delay_minutes']))
										{
											$minutes = intval(intval($value['delay_minutes']));
											if($minutes> 0)
											{
												$delay = $delay.$minutes.' Minutes '.$before_after_text.' <br>';
											}
										}
									}
									if($days == 0 && $hours == 0 && $minutes == 0 && $months == 0)
									{
										$delay = "Immediately";
									}

									if($value['template_type'] == _CAMPAIGN_SMS)
									{
									   $html .='<li style = "">
												<span class="time">'.$delay.'</span>
												<span class="dot"><i style="font-size:20px;" class="mdi mdi-message-processing"></i></span>
												<div class="content">
													<div>
														<div class = "row">
															<h3 class="subtitle col-lg-10">'.$value['title'].'</h3>

														 </div>
															 <p>'.$value['content'].'&nbsp;</p>
													</div>

												 </div>
											</li>';

									}



									if($value['template_type'] == _CAMPAIGN_EMAIL){
										$html .= '<li>
										<span class="time">'.$delay.'</span>
										<span class="dot"><i class="fas fa-envelope" style="cursor:pointer;font-size: 18px;"></i></span>
										<div class="content">
											<div id = "campaign-email-view-mode_'.$value['id'].'">
												<div class = "row">
												 <h3 class="subtitle col-lg-10">'.$value['title'].'</h3>
												 <h3 class="subtitle col-lg-10">'.$value['subject'].'</h3>

												</div>
												<p>'.$value['content'].'</p>
											</div>

										</div>
										</li></ul>';
									}

									if($value['template_type'] == _CAMPAIGN_TASK){

										$html .= '<li>
												<span class="time">'.$delay.'</span>
												<span class="dot"><i class="fa fa-tasks" style="cursor:pointer;font-size: 18px;"></i></span>
												<div class="content">
													<div id = "campaign-task-view-mode_'.$value['id'].'">
														<div class = "row">
															<h3 class="subtitle col-lg-10">'.$value['title'].'</h3>
														</div>
														<p>'.$value['description'].'</p>
													</div>

												</div>
												</li>';
									}

										if($value['template_type'] == _CAMPAIGN_TYPE_TRANSITION){
											$html .=   '<li>
															<span class="time">'.$delay.'></span>
															<span class="dot"><i class="fas fa-exchange-alt" style="cursor:pointer;font-size: 18px;"></i></span>
															<div class="content">
															<div>
															<div class = "row">
																<h3 class="subtitle col-lg-10">'.$value['title'].'</h3>
															</div>
															<p>'.$campaign_transition_arr[$value['transition_campaign_id']].'</p></div></div>
														</li>';
										}

							}
						}
					}

				}
				$return['Pipeline.previewCampaign'] = [$opp_id => array('status' => _ID_SUCCESS,'listing' =>$html,'campaign_id'=>$id)];
			}
			else
			{
				$return['Pipeline.previewCampaign'] = [$opp_id => array('status' => _ID_SUCCESS,'listing' => '','campaign_id'=>$id)];
			}
		}
		catch(\Exception $e)
		{
			$txt= date('Y-m-d H:i:s').' :: prviewCampaign Error- '.$e->getMessage();
			$return['Pipeline.previewCampaign'] = [$opp_id =>array('status'=>_ID_FAILED,'error'=>$txt,'listing' => '','campaign_id'=>'')];

		}
			return $return;
	}

    /**
     * Start Stop Campaign
     *
     */
    public static function startStopCampaign($objectData)
    {

		$response = [];
		try
		{
            $session = Router::getRequest()->getSession();
				$agency_id= $session->read("Auth.User.agency_id");
                $login_user_id = $session->read('Auth.User.user_id');
				$contact_id=$objectData['contact_id'];
				$contact_business_id=$objectData['contact_business_id'];
				$opportunity_id=$objectData['opportunity_id'];
				$stop_campaign_id=$objectData['stop_campaign_id'];
				$start_campaign_id=$objectData['start_campaign_id'];
				$type=$objectData['type'];
				$campaign_pipeline_stage=$objectData['campaign_pipeline_stage'];

				$AgencyCampaignMaster = TableRegistry::getTableLocator()->get('AgencyCampaignMaster');
				$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
				$CampaignRunningSchedule = TableRegistry::getTableLocator()->get('CampaignRunningSchedule');
				$Tasks = TableRegistry::getTableLocator()->get('Tasks');
				$TaskNotes = TableRegistry::getTableLocator()->get('TaskNotes');
				$ReferralPartnerUserContacts = TableRegistry::getTableLocator()->get('ReferralPartnerUserContacts');
				$CampaignRunningEmailSmsSchedule = TableRegistry::getTableLocator()->get('CampaignRunningEmailSmsSchedule');
				$contactBusiness = TableRegistry::getTableLocator()->get('ContactBusiness');
				$contacts = TableRegistry::getTableLocator()->get('Contacts');
                if($objectData['is_cross_sell']){
                    $start_campaign_id = $objectData['start_cross_sell_campaign_id'];
                     $campaign_pipeline_stage = 1;
                }else{
                   $start_campaign_id = $objectData['start_campaign_id'];
                }
				//check active opportunities
				$activeOppData = [];
				if(!empty($contact_business_id))
				{
					$activeOppData = $ContactOpportunities->getActiveOpportunitiesByContactId($agency_id, $contact_business_id);
				}
				else if(!empty($contact_id))
				{
					$activeOppData = $ContactOpportunities->getActiveOpportunitiesByContactId($agency_id, null, $contact_id);
				}
			  //if type true start campaign else stop
			  if(isset($start_campaign_id) && !empty($start_campaign_id) && $type==_CAMPAIGN_START){
				  $agencyCampaignDetail = $AgencyCampaignMaster->agencyCampaignDetail($start_campaign_id);
				  $opportunity_detail = $ContactOpportunities->contactOpportunityDetail($opportunity_id);
				  //new code to run only same type of campaign once
				  if(isset($opportunity_detail['contact_business_id']) && !empty($opportunity_detail['contact_business_id']))
				  {
					$campaignRunningSchedules = $CampaignRunningSchedule->getRunningCampaignListingByBusinessId($opportunity_detail['contact_business_id']);
				  }
				  else
				  {
					$campaignRunningSchedules = $CampaignRunningSchedule->getRunningCampaignListingByContactID($opportunity_detail['contact_id']);
				  }
				  //print_r($campaignRunningSchedules);die;
				  if(!empty($campaignRunningSchedules))
				  {
					  foreach ($campaignRunningSchedules as $campaignRunningSchedule)
					  {
						  //print_r($campaign['type']);die;
						  if($campaignRunningSchedule['agency_campaign_master']['type'] == _CAMPAIGN_TYPE_PIPELINE || $campaignRunningSchedule['agency_campaign_master']['type'] == CAMPAIGN_TYPE_NEW_LEAD || $campaignRunningSchedule['agency_campaign_master']['type'] == CAMPAIGN_TYPE_GENERAL_NEW_LEAD || $campaignRunningSchedule['agency_campaign_master']['type'] == CAMPAIGN_TYPE_LOAN_OFFICER_HOME_CONDO_PROPERTY)
						  {
							  $CampaignRunningEmailSmsSchedule->updateCampaignRunningEmailSmsSchedule(['status' => _EMAIL_SMS_SENT_STATUS_CANCELLED],['campaign_running_schedule_id' => $campaignRunningSchedule['id'],'status'=>_EMAIL_SMS_SENT_STATUS_PENDING]);
							  $CampaignRunningSchedule->updateCampaignRunningSchedule(['status' => _RUNNING_CAMPAIGN_STATUS_COMPLETED],['id'=>$campaignRunningSchedule['id'],'status'=>_RUNNING_CAMPAIGN_STATUS_ACTIVE]);
							  $TaskDetail = $Tasks->getTaskDetailByCampaignRunningScheduleId($campaignRunningSchedule['id']);
							  $Tasks->updateAll(['status'=>_TASK_STATUS_COMPLETE],['campaign_running_schedule_id'=>$campaignRunningSchedule['id']]);
							  if(isset($TaskDetail) && !empty($TaskDetail)){
								foreach($TaskDetail as $tskdtl){
								  $tskId = $tskdtl['id'];
								  $taskNote = $TaskNotes->newEntity();
								  $notes =  [];
								  $notes['task_id'] = $tskId;
								  $notes['user_id'] = $login_user_id;
								  $notes['description'] = "It was completed automatically due to pipeline stage move";
								  $taskNote = $TaskNotes->patchEntity($taskNote,$notes);
								  $taskNote = $TaskNotes->save($taskNote);
								}

							  }
						  }
					  }


				  }
				  //
                  $contactXdate = '';
				  if(isset($opportunity_detail['contact_business_id']) && !empty($opportunity_detail['contact_business_id']))
				  {
					if($agencyCampaignDetail['type'] == _CAMPAIGN_TYPE_LONG_TERM_NURTURE)
					{
						if(count($activeOppData) < _ID_SUCCESS)
						{
							if(isset($opportunity_detail['effective_date']) && !empty($opportunity_detail['effective_date'])){
								$contactBusiness->updateAll(['expiration_date' => $opportunity_detail['effective_date']],['id' => $opportunity_detail['contact_business_id']]);
							}else{
								$contactXdate = date('Y-m-d', strtotime(date('Y-m-d') . ' +12 months'));
								$contactBusiness->updateAll(['expiration_date' => $contactXdate], ['id' => $opportunity_detail['contact_business_id']]);
							}
							$campaign_result = CommonFunctions::startLtnCampaignBusiness($opportunity_detail['contact_business_id'], $login_user_id);
						}
					}
					else if($agencyCampaignDetail['type'] == _CAMPAIGN_TYPE_X_DATE)
					{
						if(count($activeOppData) < _ID_SUCCESS)
						{
							if(isset($opportunity_detail['effective_date']) && !empty($opportunity_detail['effective_date'])){
								$contactBusiness->updateAll(['expiration_date' => $opportunity_detail['effective_date']],['id' => $opportunity_detail['contact_business_id']]);
							}else{
								$contactXdate = date('Y-m-d', strtotime(date('Y-m-d') . ' +12 months'));
								$contactBusiness->updateAll(['expiration_date' => $contactXdate], ['id' => $opportunity_detail['contact_business_id']]);
							}
							$campaign_result = CommonFunctions::startXdateCampaignBusiness($opportunity_detail['contact_business_id'], $login_user_id);
						}
					}
					else if($agencyCampaignDetail['type'] == _CAMPAIGN_TYPE_PIPELINE && $campaign_pipeline_stage == _PIPELINE_STAGE_APPOINTMENT_SCHEDULED)
					{
						if(isset($opportunity_detail['contact_business_id']) && !empty($opportunity_detail['contact_business_id']))
						{
							$campaign_result = CommonFunctions::startBeforeAfterTypeCampaign(null, $start_campaign_id,$campaign_pipeline_stage,null,$opportunity_detail['user_id'],null,null,$opportunity_detail['contact_business_id'],$opportunity_detail['appointment_date']);
						}else{
							$campaign_result = CommonFunctions::startBeforeAfterTypeCampaign($contact_id, $start_campaign_id,$campaign_pipeline_stage,null,$opportunity_detail['user_id'],null,null,null,$opportunity_detail['appointment_date']);
						}

					}
					else if($agencyCampaignDetail['type'] == _CAMPAIGN_TYPE_PIPELINE && $campaign_pipeline_stage == _PIPELINE_STAGE_QUOTE_SENT && $objectData['video_proposal_status'] == _QUOTE_SENT_VIDEO_PROPOSAL)
					{
					  $campaign_result = Pipeline::startVideoProposalCampaign($objectData);
					}
					else
					{
					  $campaign_result = CommonFunctions::startCampaign(null, $start_campaign_id,$campaign_pipeline_stage,null,$opportunity_detail['user_id'],null,null,$opportunity_detail['contact_business_id']);
					}
				  }
				  else
				  {
					if($agencyCampaignDetail['type'] == _CAMPAIGN_TYPE_LONG_TERM_NURTURE)
					{	
						if(count($activeOppData) < 1)
						{
							if(isset($opportunity_detail['effective_date']) && !empty($opportunity_detail['effective_date'])){
								$contacts->updateAll(['expiration_date' => $opportunity_detail['effective_date']],['id' => $opportunity_detail['contact_id']]);
							}else{
								$contactXdate = date('Y-m-d', strtotime(date('Y-m-d') . ' +12 months'));
								$contacts->updateAll(['expiration_date' => $contactXdate], ['id' => $opportunity_detail['contact_id']]);
							}
							$campaign_result = CommonFunctions::startLtnCampaign($opportunity_detail['contact_id'], $login_user_id);
						}
					}
					else if($agencyCampaignDetail['type'] == _CAMPAIGN_TYPE_X_DATE)
					{
						if(count($activeOppData) < _ID_SUCCESS)
						{
							if(isset($opportunity_detail['effective_date']) && !empty($opportunity_detail['effective_date'])){
								$contacts->updateAll(['expiration_date' => $opportunity_detail['effective_date'], 'xdate_campaign_start_status' => _ID_SUCCESS],['id' => $opportunity_detail['contact_id']]);
							}else{
								$contactXdate = date('Y-m-d', strtotime(date('Y-m-d') . ' +12 months'));
								$contacts->updateAll(['expiration_date' => $contactXdate, 'xdate_campaign_start_status' => _ID_SUCCESS], ['id' => $opportunity_detail['contact_id']]);
							}
							$campaign_result = CommonFunctions::startXdateCampaign($opportunity_detail['contact_id'], $login_user_id);
						}
					}
					else if($agencyCampaignDetail['type'] == _CAMPAIGN_TYPE_PIPELINE && $campaign_pipeline_stage == _PIPELINE_STAGE_APPOINTMENT_SCHEDULED)
					{
					  $campaign_result = CommonFunctions::startBeforeAfterTypeCampaign($contact_id, $start_campaign_id,$campaign_pipeline_stage,null,$opportunity_detail['user_id'],null,null,null,$opportunity_detail['appointment_date'], $login_user_id);
					}
					else if($agencyCampaignDetail['type'] == _CAMPAIGN_TYPE_PIPELINE && $campaign_pipeline_stage == _PIPELINE_STAGE_QUOTE_SENT && $objectData['video_proposal_status'] == _QUOTE_SENT_VIDEO_PROPOSAL)
					{
					  $campaign_result = Pipeline::startVideoProposalCampaign($objectData);
					}
					else
					{

					  $campaign_result = CommonFunctions::startCampaign($contact_id, $start_campaign_id,$campaign_pipeline_stage,null,$opportunity_detail['user_id'],null,null,null, $login_user_id);

					}
				  }
				  if(isset($contact_id) && !empty($contact_id))
				  {
					$referral_partner_user_contact_detail = $ReferralPartnerUserContacts->getReferralPartnerUserContactByContactId($contact_id);
					if(isset($referral_partner_user_contact_detail) && !empty($referral_partner_user_contact_detail) && $referral_partner_user_contact_detail['insurance_type_id'] == $opportunity_detail['insurance_type_id'])
					{
					  if($agencyCampaignDetail['type'] == _CAMPAIGN_TYPE_PIPELINE && $campaign_pipeline_stage == _PIPELINE_STAGE_APPOINTMENT_SCHEDULED)
					  {
						$campaign_result_referral_partner = CommonFunctions::startCampaign($contact_id, $start_campaign_id,_PIPELINE_STAGE_REFERRAL_PARTNER_NEW_LEAD,null,$opportunity_detail['user_id'],$referral_partner_user_contact_detail['referral_partner_user_id'],null,null, $login_user_id);
					  }
					  else
					  {
						$campaign_result_referral_partner = CommonFunctions::startCampaign($contact_id, $start_campaign_id,_PIPELINE_STAGE_REFERRAL_PARTNER_NEW_LEAD,null,$opportunity_detail['user_id'],$referral_partner_user_contact_detail['referral_partner_user_id'], null, null, $login_user_id);
					  }
					}


				  }

				  if(isset($campaign_result['status']) && $campaign_result['status'] == _ID_SUCCESS)
				  {
					$response = array('status' => _ID_SUCCESS,'type' => CAMPAIGN_START);
				  }
                  else if(isset($campaign_result['status']) && $campaign_result['status'] == 5)
                  {
                      $response = array('status' => 5,'type' => CAMPAIGN_START);
                  }
				  else if(count($activeOppData) >= _ID_SUCCESS)
				  {
					$response = array('status' => _CAMPAIGN_TYPE_X_DATE);
				  }
				  else if($campaign_result['status'] == _ID_FAILED && !empty($campaign_result['day_count']))
				  {
					$response = array('status' => _ID_FAILED, 'day_count' => $campaign_result['day_count']);
				  }
				  else
				  {
					$response = array('status' => _ID_FAILED);
				  }
			  }
			  else if(isset($stop_campaign_id) && !empty($stop_campaign_id) && !empty($contact_id) && $type==_CAMPAIGN_STOP){

				 $campaign_result = CommonFunctions::stopCampaign($contact_id);
				 if(isset($campaign_result['status']) && $campaign_result['status'] == _ID_SUCCESS)
				  {
					$response =  array('status' => _ID_SUCCESS,'type' => CAMPAIGN_STOP);
				  }
				  else
				  {
					$response = array('status' => _ID_FAILED);
				  }

			  }
			  else{
				  $response = array('status' => _ID_FAILED);

			  }

		}
		catch(\Exception $e)
		{

            $txt= date('Y-m-d H:i:s').' :: startStopCampaign Error- '.$e->getMessage();
			$response = array('status'=>_ID_FAILED,'error'=>$txt);
        }
		return $response ;

    }
	public static function getPipelineCampaignId($objectData){
		$response =[];
		try
		{
			$session = Router::getRequest()->getSession();
			$AgencyCampaignMasterTable = TableRegistry::getTableLocator()->get('AgencyCampaignMaster');
			$login_agency_id= $session->read("Auth.User.agency_id");
			$opportunity_id = $objectData['opportunity_id'];
			$pipeline_stage = $objectData['pipeline_stage'];
			$stop_campaign_id = '';
			$CampaignRunningSchedule = TableRegistry::getTableLocator()->get('CampaignRunningSchedule');
			if(isset($objectData['contact_id']) && !empty($objectData['contact_id']))
			{
				$campaignDetails = $AgencyCampaignMasterTable->checkCampaignExist(null,null,$login_agency_id,$pipeline_stage,_CAMPAIGN_TYPE_PIPELINE,null,_LINE_PERSONAL);
				$campaignRunningDetails = $CampaignRunningSchedule->getRunningCampaignListingByContactID($objectData['contact_id']);
				if(isset($campaignRunningDetails[0]['agency_campaign_master']) && !empty($campaignRunningDetails[0]['agency_campaign_master']))
				{
					$stop_campaign_id = $campaignRunningDetails[0]['agency_campaign_master']['id'];
					$campaign_name = $campaignRunningDetails[0]['agency_campaign_master']['name'];
				}

			}else if(isset($objectData['contact_business_id']) && !empty($objectData['contact_business_id'])){
				$campaignDetails = $AgencyCampaignMasterTable->checkCampaignExist(null,null,$login_agency_id,$pipeline_stage,_CAMPAIGN_TYPE_PIPELINE,null,_LINE_COMMERCIAL);
				$campaignRunningDetails = $CampaignRunningSchedule->getRunningCampaignListingByBusinessId($objectData['contact_business_id']);
				if(isset($campaignRunningDetails[0]['agency_campaign_master']) && !empty($campaignRunningDetails[0]['agency_campaign_master']))
				{
					$stop_campaign_id = $campaignRunningDetails[0]['agency_campaign_master']['id'];
					$campaign_name = $campaignRunningDetails[0]['agency_campaign_master']['name'];
				}
			}
			if(isset($campaignDetails) && !empty($campaignDetails))
			{
				$id  =  $campaignDetails['id'];
				$response = array('status'=>_ID_SUCCESS,'campaign_id'=>$id,'stop_campaign_id'=>$stop_campaign_id);
			}else{
				$response = array('status'=>_ID_FAILED);
			}
		}
		catch(\Exception $e) {

            $txt= date('Y-m-d H:i:s').' :: getPipelineCampaignId Error- '.$e->getMessage();
			$response = array('status'=>_ID_FAILED,'error'=>$txt);
        }

		return $response;
	}

	  /**
     * save appointment call status
    */
    public static function saveAppointmentCallStatus($objectData){
	  $response  = [];
	  try
	  {
		  $ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
		  $AgencyCampaignMaster = TableRegistry::getTableLocator()->get('AgencyCampaignMaster');
		  $session = Router::getRequest()->getSession();
		  $login_agency_id= $session->read("Auth.User.agency_id");
		  if(isset($objectData['appointment_call_status']) && isset($objectData['appointment_opportunity_id']) && !empty($objectData['appointment_opportunity_id']))
		  {

			 $contact_opportunities = $ContactOpportunities->get($objectData['appointment_opportunity_id']);
			  $info=array();
			  $info['appointment_call_status']= $objectData['appointment_call_status'];
			  $info['platform_update'] = _PLATFORM_TYPE_SYSTEM;
			  $contact_opportunities = $ContactOpportunities->patchEntity($contact_opportunities, $info);
			  if($opp_detail = $ContactOpportunities->save($contact_opportunities)){

				//make primary_flag_all_stages true
				if(isset($opp_detail->contact_business_id) && !empty($opp_detail->contact_business_id))
				{
				$ContactOpportunities->updateAll(['primary_flag_all_stages'=>_ID_STATUS_PENDING],['contact_business_id' => $opp_detail->contact_business_id,'pipeline_stage'=>$opp_detail->pipeline_stage,'agency_id'=>$login_agency_id]);
				$ContactOpportunities->updateAll(['primary_flag_all_stages'=>_ID_STATUS_ACTIVE],['id' => $opp_detail->id]);
				}
				else if(isset($opp_detail->contact_id) && !empty($opp_detail->contact_id))
				{
					$ContactOpportunities->updateAll(['primary_flag_all_stages'=>_ID_STATUS_PENDING],['contact_id' => $opp_detail->contact_id,'pipeline_stage'=>$opp_detail->pipeline_stage,'agency_id'=>$login_agency_id,'contact_business_id IS NULL']);
					$ContactOpportunities->updateAll(['primary_flag_all_stages'=>_ID_STATUS_ACTIVE],['id' => $opp_detail->id]);
				}
				//end

				if(isset($objectData['appointment_missed_campaign']))
				{
				  if($objectData['appointment_missed_campaign'])
				  {
					$checkCampaignExist=$AgencyCampaignMaster->checkCampaignExist(null,null, $login_agency_id,_MISSED_APPOINTMENT_FOLLOW_UP_STAGE_INITIATE,_CAMPAIGN_TYPE_MISSED_APPOINTMENT_FOLLOW_UP);
					if(!empty($checkCampaignExist)){
						$start_campaign_id=$checkCampaignExist['id'];
						$campaign_result = CommonFunctions::startCampaign($opp_detail->contact_id, $start_campaign_id,_MISSED_APPOINTMENT_FOLLOW_UP_STAGE_INITIATE);
					}
				  }
				}
				$response =  json_encode(array('status' => _ID_SUCCESS));
			  }
			  else
			  {
				 $response = json_encode(array('status' => _ID_FAILED));
			  }
		  }
		  else
		  {
			   $response = json_encode(array('status' => _ID_FAILED));
		  }
		}
		catch(\Exception $e) {

            $txt= date('Y-m-d H:i:s').' :: getPipelineCampaignId Error- '.$e->getMessage();
			$response = array('status'=>_ID_FAILED,'error'=>$txt);
        }
		return $response;
    }

	/**
     * this function is used to get all agency cross sell campaigns
    */
    public function getAllAgencyCrossSellCampaigns($objectData){
		ini_set('memory_limit', '-1');
		ini_set('max_execution_time', '-1');

		$AgencyCampaignMaster = TableRegistry::getTableLocator()->get('AgencyCampaignMaster');
		$return = [];
		$crossSellCampaignsList = "";
        if(isset($objectData['agency_id']) && !empty($objectData['agency_id']))
        {
          $crossSellCampaignsList = $AgencyCampaignMaster->getAllActiveAgencyCampaignsByType($objectData['agency_id'],_CAMPAIGN_TYPE_CROSS_SELL,$objectData['personal_commercial_line']);
          $return['Pipeline.getAllAgencyCrossSellCampaigns'] = [
			$objectData['agency_id'] => $crossSellCampaignsList
		  ];
		}
		return $return;
    }

/*
	*This function is used to get the preview of any campaign by id
	*/
	public static function previewCampaignById($campaignId){

		try
		{
			$session = Router::getRequest()->getSession();
			$AgencySmsTemplatesTable = TableRegistry::getTableLocator()->get('AgencySmsTemplates');
			$AgencyEmailTemplatesTable = TableRegistry::getTableLocator()->get('AgencyEmailTemplates');
			$AgencyTaskTemplatesTable = TableRegistry::getTableLocator()->get('AgencyTaskTemplates');
			$AgencyTransitionTemplatesTable = TableRegistry::getTableLocator()->get('AgencyTransitionTemplates');
			$AgencyCampaignMasterTable = TableRegistry::getTableLocator()->get('AgencyCampaignMaster');
			$AgencyCampaignPipelineStageMasterTable = TableRegistry::getTableLocator()->get('AgencyCampaignPipelineStageMaster');
			$AgencyCampaignAttachmentsTable = TableRegistry::getTableLocator()->get('AgencyCampaignAttachments');
			$id  =  $campaignId;
			$pipeline_stages_arr = [];
			$emailSmsTaskTemplateArray = [];
			$taskTempaleAttachmentsArray =$agencyAttachmentTemplateIds=[];
			$agencyCampaignMaster = $AgencyCampaignMasterTable->get($id,['contain'=>['AgencyCampaignReferralPartnerType']]);
			$pipeliestageData = $AgencyCampaignPipelineStageMasterTable->getPipelineStages($id);
			if(isset($pipeliestageData) && !empty($pipeliestageData))
			{
				foreach ($pipeliestageData as $key => $value)
				{
					$getEntityDef = "";
					if ($agencyCampaignMaster->type == _CAMPAIGN_TYPE_PIPELINE) {
						$getEntityDef = getEntityDef($err, _PIPELINE_CAMPAIGN_STAGE, $value);
					}
					$pipeline_stages_arr[$value] = $getEntityDef;

					$emailTemplates = $AgencyEmailTemplatesTable->getEmailTemplets($value,$id);
					if(isset($emailTemplates) && !empty($emailTemplates))
					{
						$emailSmsTaskTemplateArray = array_merge($emailSmsTaskTemplateArray,$emailTemplates);
					}
					$smsTemplates = $AgencySmsTemplatesTable->getSmsTemplets($value,$id);

					if(isset($smsTemplates) && !empty($smsTemplates))
					{
						$emailSmsTaskTemplateArray = array_merge($emailSmsTaskTemplateArray,$smsTemplates);
					}
					$taskTemplates = $AgencyTaskTemplatesTable->getTaskTemplets($value,$id);
					if(isset($taskTemplates) && !empty($taskTemplates))
					{
						$emailSmsTaskTemplateArray = array_merge($emailSmsTaskTemplateArray,$taskTemplates);
							// Task Template attachments(Added by monika)
					if(isset($taskTemplates) && !empty($taskTemplates)){
						$agencyAttachmentTemplateId =[];
						foreach($taskTemplates as $taskTemplateValue){
							$agencyAttachmentTemplateId[] =isset($taskTemplateValue['id']) && !empty($taskTemplateValue['id']) ? $taskTemplateValue['id'] : '';
						}
						$taskAttachments = $AgencyCampaignAttachmentsTable->getAttachmentDetail($id,$agencyAttachmentTemplateId);
					}

					if(isset($taskAttachments) && !empty($taskAttachments))
					{
						$taskTempaleAttachmentsArray = array_merge($taskTempaleAttachmentsArray,$taskAttachments);

					}
					$taskTempaleAttachmentsCount = count($taskTempaleAttachmentsArray);

					$attachmentTemplateIds = $AgencyCampaignAttachmentsTable->getAgencyTemplateIds($id,$agencyAttachmentTemplateId);

					if(isset($attachmentTemplateIds) && !empty($attachmentTemplateIds))
					{
						$agencyAttachmentTemplateIds = array_merge($agencyAttachmentTemplateIds,$attachmentTemplateIds);

					}
					}

					$transitionTemplates = $AgencyTransitionTemplatesTable->getTransitionTemplates($value,$id);
					if(isset($transitionTemplates) && !empty($transitionTemplates))
					{
						$emailSmsTaskTemplateArray = array_merge($emailSmsTaskTemplateArray,$transitionTemplates);
					}

				}
				$emailSmsTaskTemplate = [];
				$templateTotalNumberOfSeconds = 0;
				$templateTotalNumberOfDays = 0;
				$emailSmsTaskTemplateBefore = [];
				$emailSmsTaskTemplateAfter = [];
				$emailSmsTaskTemplateArrayBefore = [];
				$emailSmsTaskTemplateArrayAfter = [];
				$sortingValue = 0.001;
				foreach ($emailSmsTaskTemplateArray as $key => $row)
				{
					if($agencyCampaignMaster['before_after'] == _BEFORE_AFTER)
					{
						if($row['before_after'] == _BEFORE)
						{
							$emailSmsTaskTemplateArrayBefore[] = $row;
						}
						else
						{
							$emailSmsTaskTemplateArrayAfter[] = $row;
						}
					}
					else
					{
						if($row['delay_time_in_sec']>0)
						{
							$emailSmsTaskTemplate[$key] = $row['delay_time_in_sec'];
							$templateTotalNumberOfSeconds = $templateTotalNumberOfSeconds + $row['delay_time_in_sec'];
						}
						else
						{
							$sort_seconds = 0;
							if(isset($row['delay_minutes']) && !empty($row['delay_minutes']))
							{
								$minutes_to_seconds = strtotime($row['delay_minutes'].' minute', 0);
								$sort_seconds = $sort_seconds + $minutes_to_seconds;
							}
							if(isset($row['delay_hours']) && !empty($row['delay_hours']))
							{
								$hours_to_seconds = strtotime($row['delay_hours'].' hour', 0);
								$sort_seconds = $sort_seconds + $hours_to_seconds;
							}
							if(isset($row['delay_days']) && !empty($row['delay_days']))
							{
								$days_to_seconds = strtotime($row['delay_days'].' day', 0);
								$sort_seconds = $sort_seconds + $days_to_seconds;
							}
							if(isset($row['delay_months']) && !empty($row['delay_months']))
							{
								$months_to_seconds = strtotime($row['delay_months'].' month', 0);
								$sort_seconds = $sort_seconds + $months_to_seconds;
							}
							$emailSmsTaskTemplate[$key] = $sort_seconds + $sortingValue;
							$sortingValue = $sortingValue + 0.001;
							$templateTotalNumberOfSeconds = $templateTotalNumberOfSeconds + $sort_seconds;
						}
					}
				}
				//print_r($emailSmsTaskTemplate);die;
				if($agencyCampaignMaster['before_after'] == _BEFORE)
				{
					array_multisort($emailSmsTaskTemplate, SORT_DESC, $emailSmsTaskTemplateArray);
					//code for after type campaign
					foreach ($emailSmsTaskTemplate as $after_campaign_key => $after_campaign_value)
					{
						if((int)$after_campaign_value == 0)
						{
							$campaign_arr_to_copy = $emailSmsTaskTemplateArray[$after_campaign_key];
							unset($emailSmsTaskTemplateArray[$after_campaign_key]);
							array_unshift($emailSmsTaskTemplateArray, $campaign_arr_to_copy);
						}
					}
				}
				else if($agencyCampaignMaster['before_after'] == _BEFORE_AFTER)
				{
					foreach ($emailSmsTaskTemplateArrayBefore as $key => $row)
					{
						if($row['delay_time_in_sec']>0)
						{
							$emailSmsTaskTemplateBefore[$key] = $row['delay_time_in_sec'];
						}
						else
						{
							$sort_seconds = 0;
							if(isset($row['delay_minutes']) && !empty($row['delay_minutes']))
							{
								$minutes_to_seconds = strtotime($row['delay_minutes'].' minute', 0);
								$sort_seconds = $sort_seconds + $minutes_to_seconds;
							}
							if(isset($row['delay_hours']) && !empty($row['delay_hours']))
							{
								$hours_to_seconds = strtotime($row['delay_hours'].' hour', 0);
								$sort_seconds = $sort_seconds + $hours_to_seconds;
							}
							if(isset($row['delay_days']) && !empty($row['delay_days']))
							{
								$days_to_seconds = strtotime($row['delay_days'].' day', 0);
								$sort_seconds = $sort_seconds + $days_to_seconds;
							}
							if(isset($row['delay_months']) && !empty($row['delay_months']))
							{
								$months_to_seconds = strtotime($row['delay_months'].' month', 0);
								$sort_seconds = $sort_seconds + $months_to_seconds;
							}
							$emailSmsTaskTemplateBefore[$key] = $sort_seconds;
						}
					}
					foreach ($emailSmsTaskTemplateArrayAfter as $key => $row)
					{
						if($row['delay_time_in_sec']>0)
						{
							$emailSmsTaskTemplateAfter[$key] = $row['delay_time_in_sec'];
							$templateTotalNumberOfSeconds = $templateTotalNumberOfSeconds + $row['delay_time_in_sec'];
						}
						else
						{
							$sort_seconds = 0;
							if(isset($row['delay_minutes']) && !empty($row['delay_minutes']))
							{
								$minutes_to_seconds = strtotime($row['delay_minutes'].' minute', 0);
								$sort_seconds = $sort_seconds + $minutes_to_seconds;
							}
							if(isset($row['delay_hours']) && !empty($row['delay_hours']))
							{
								$hours_to_seconds = strtotime($row['delay_hours'].' hour', 0);
								$sort_seconds = $sort_seconds + $hours_to_seconds;
							}
							if(isset($row['delay_days']) && !empty($row['delay_days']))
							{
								$days_to_seconds = strtotime($row['delay_days'].' day', 0);
								$sort_seconds = $sort_seconds + $days_to_seconds;
							}
							if(isset($row['delay_months']) && !empty($row['delay_months']))
							{
								$months_to_seconds = strtotime($row['delay_months'].' month', 0);
								$sort_seconds = $sort_seconds + $months_to_seconds;
							}
							$emailSmsTaskTemplateAfter[$key] = $sort_seconds;
							$templateTotalNumberOfSeconds = $templateTotalNumberOfSeconds + $sort_seconds;
						}
					}
					//print_r($emailSmsTaskTemplateArrayBefore);die;
					array_multisort($emailSmsTaskTemplateBefore, SORT_DESC, $emailSmsTaskTemplateArrayBefore);
					//print_r($emailSmsTaskTemplateArrayBefore);die;
					array_multisort($emailSmsTaskTemplateAfter, SORT_ASC, $emailSmsTaskTemplateArrayAfter);
					$emailSmsTaskTemplateArray = array_merge($emailSmsTaskTemplateArrayBefore, $emailSmsTaskTemplateArrayAfter);
					//print_r($emailSmsTaskTemplateArray);die;
					$emailSmsTaskTemplate = array_merge($emailSmsTaskTemplateBefore, $emailSmsTaskTemplateAfter);
					foreach ($emailSmsTaskTemplate as $after_before_campaign_key => $after_before_campaign_value)
					{
						if((int)$after_before_campaign_value == 0)
						{
							$campaign_arr_to_copy_after_before = $emailSmsTaskTemplateArray[$after_before_campaign_key];
							unset($emailSmsTaskTemplateArray[$after_before_campaign_key]);
							array_unshift($emailSmsTaskTemplateArray, $campaign_arr_to_copy_after_before);
						}
					}
					//print_r($emailSmsTaskTemplateArray);die;
				}
				else
				{
					array_multisort($emailSmsTaskTemplate, SORT_ASC, $emailSmsTaskTemplateArray);
				}

				$templateTotalNumberOfDays = intval(intval($templateTotalNumberOfSeconds) / (3600*24));
				$html = '';

				foreach ($pipeline_stages_arr as $pipeline_key => $pipeline_value)
				{
					$html .= '<div class="card">';
					$cardShowClass = "";
					$html .= '<div id="'.$pipeline_key.'" >
					<div class="card-body-working-campign">
					<div><ul class="list-unstyled">';
						if(!empty($emailSmsTaskTemplateArray))
						{
							$before_after_text="";
							if($agencyCampaignMaster['before_after'] == _BEFORE)
							{
								$before_after_text = "Before";
							}
						//foreach ($emailSmsTaskTemplateArray as $emailSmsTaskTemplates)
						//{
							$filterBy = $pipeline_key;
							$emailSmsTaskData = array_filter($emailSmsTaskTemplateArray, function ($var) use ($filterBy) {
								return ($var['pipeline_stage_id'] == $filterBy);
							});

							if(!empty($emailSmsTaskData))
							{
								foreach ($emailSmsTaskData as $key => $value) {

									if($agencyCampaignMaster['before_after'] == _BEFORE_AFTER)
									{
										if($value['before_after'] == _BEFORE)
										{
											$before_after_text = "Before";
										}
										else
										{
											$before_after_text = "After";
										}
									}
									$fromTimedb = date("H:i:s", strtotime($value['from_time']));
									$toTimedb = date("H:i:s", strtotime($value['to_time']));
									$before_after_db = $value['before_after'];
									$timeNumber = "0";
									$timeText = "";
									$delay = "";
									$months = 0;
									 $days = 0;
									 $hours = 0;
									 $minutes = 0;

									if(isset($value['delay_time_in_sec']) && !empty($value['delay_time_in_sec']) && $value['delay_time_in_sec']>0)
									{
										$days = intval(intval($value['delay_time_in_sec']) / (3600*24));
										if($days> 0){
											$delay = $delay.$days.' Days <br>';
											$timeNumber = $days;
											$timeText = "day";
										}
										/*** get the hours ***/
										$hours = (intval($value['delay_time_in_sec']) / 3600) % 24;
										if($hours > 0){
											$delay = $delay.$hours.' Hours <br>';
											$timeNumber = $hours;
											$timeText = "hour";
										}
										/*** get the minutes ***/
										$minutes = (intval($value['delay_time_in_sec']) / 60) % 60;
										if($minutes> 0){
											$delay = $delay.$minutes.' Minutes <br>';
											$timeNumber = $minutes;
											$timeText = "minute";
										}
									}
									else
									{
										if(!empty($value['delay_months']))
										{
											$months = intval(intval($value['delay_months']));
											if($months> 0)
											{
												$delay = $delay.$months.' Months '.$before_after_text.' <br>';
											}
										}
										if(!empty($value['delay_days']))
										{
											$days = intval(intval($value['delay_days']));
											if($days> 0)
											{
												$delay = $delay.$days.' Days '.$before_after_text.' <br>';
											}
										}
										if(!empty($value['delay_hours']))
										{
											$hours = intval(intval($value['delay_hours']));
											if($hours> 0)
											{
												$delay = $delay.$hours.' Hours '.$before_after_text.' <br>';
											}
										}
										if(!empty($value['delay_minutes']))
										{
											$minutes = intval(intval($value['delay_minutes']));
											if($minutes> 0)
											{
												$delay = $delay.$minutes.' Minutes '.$before_after_text.' <br>';
											}
										}
									}
									if($days == 0 && $hours == 0 && $minutes == 0 && $months == 0)
									{
										$delay = "Immediately";
									}

									if($value['template_type'] == _CAMPAIGN_SMS)
									{
									   $html .='<li style = "">
												<span class="time">'.$delay.'</span>
												<span class="dot"><i style="font-size:20px;" class="mdi mdi-message-processing"></i></span>
												<div class="content">
													<div>
														<div class = "row">
															<h3 class="subtitle col-lg-10">'.$value['title'].'</h3>

														 </div>
															 <p>'.$value['content'].'&nbsp;</p>
													</div>

												 </div>
											</li>';

									}



									if($value['template_type'] == _CAMPAIGN_EMAIL){
										$html .= '<li>
										<span class="time">'.$delay.'</span>
										<span class="dot"><i class="fas fa-envelope" style="cursor:pointer;font-size: 18px;"></i></span>
										<div class="content">
											<div id = "campaign-email-view-mode_'.$value['id'].'">
												<div class = "row">
												 <h3 class="subtitle col-lg-10">'.$value['title'].'</h3>
												 <h3 class="subtitle col-lg-10">'.$value['subject'].'</h3>

												</div>
												<p>'.$value['content'].'</p>
											</div>

										</div>
										</li></ul>';
									}

									if($value['template_type'] == _CAMPAIGN_TASK){

										$html .= '<li>
												<span class="time">'.$delay.'</span>
												<span class="dot"><i class="fa fa-tasks" style="cursor:pointer;font-size: 18px;"></i></span>
												<div class="content">
													<div id = "campaign-task-view-mode_'.$value['id'].'">
														<div class = "row">
															<h3 class="subtitle col-lg-10">'.$value['title'].'</h3>
														</div>
														<p>'.$value['description'].'</p>
													</div>

												</div>
												</li>';
									}

										if($value['template_type'] == _CAMPAIGN_TYPE_TRANSITION){
											$html .=   '<li>
															<span class="time">'.$delay.'></span>
															<span class="dot"><i class="fas fa-exchange-alt" style="cursor:pointer;font-size: 18px;"></i></span>
															<div class="content">
															<div>
															<div class = "row">
																<h3 class="subtitle col-lg-10">'.$value['title'].'</h3>
															</div>
															<p>'.$campaign_transition_arr[$value['transition_campaign_id']].'</p></div></div>
														</li>';
										}

							}
						}
					}

				}
				$return['Pipeline.previewCampaignById'] = [$id => array('status' => _ID_SUCCESS,'listing' =>$html,'campaign_id'=>$id, 'campaign_name' => ucwords($agencyCampaignMaster['name']))];
			}
			else
			{
				$return['Pipeline.previewCampaignById'] = [$id => array('status' => _ID_SUCCESS,'listing' => '','campaign_id'=>$id, 'campaign_name' => ucwords($agencyCampaignMaster['name']))];
			}
			return $return;
		}
		catch(\Exception $e)
		{
			$txt= date('Y-m-d H:i:s').' :: prviewCampaign Error- '.$e->getMessage();
			$return['Pipeline.previewCampaignById'] =[$id => array('status'=>_ID_FAILED,'error'=>$txt,'listing' => '','campaign_id'=>'')];

		}
	}

	 public static function getAppoinmentDateTime($opp_id){

		try
		{
			$session = Router::getRequest()->getSession();
			$login_agency_id= $session->read("Auth.User.agency_id");
			$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
			$ContactAppointments = TableRegistry::getTableLocator()->get('ContactAppointments');

			$contact_opportunities = $ContactOpportunities->get($opp_id);

			$contact_appointment_date="";
			$contact_appointment_time="";
			if(isset($contact_opportunities) && !empty($contact_opportunities))
			{
				if(isset($contact_opportunities['contact_business_id']) && !empty($contact_opportunities['contact_business_id'])){
					$contact_appointment = $ContactAppointments->getLatestAppointmentsByBusinessId($contact_opportunities['contact_business_id']);
				}
				else{
					$contact_appointment = $ContactAppointments->getLatestAppointmentsByContactId($contact_opportunities['contact_id']);
				}
			}
			if(isset($contact_appointment['appointment_date']) && !empty($contact_appointment['appointment_date']))
			{
				$appointment_date = date("m/d/Y h:i a",strtotime($contact_appointment['appointment_date']));
				$contact_appointment_datetime =  explode(' ',$appointment_date);
				$contact_appointment_date = $contact_appointment_datetime[0];
				$contact_appointment_time = $contact_appointment_datetime[1].' '.strtoupper($contact_appointment_datetime[2]) ;
			}
			$return['Pipeline.getAppoinmentDateTime'] = [
					$opp_id => array('appointment_date'=>$contact_appointment_date,'appointment_time'=>$contact_appointment_time)
				];

		}
		catch(\Exception $e) {

            $txt= date('Y-m-d H:i:s').' :: getPipelineCampaignId Error- '.$e->getMessage();
			$return['Pipeline.getAppoinmentDateTime'] = [
					$opp_id => array('appointment_date'=>'','appointment_time'=>'','status'=>_ID_FAILED,'error'=>$txt)];
        }

		return $return;
	}


	/**
     * start Video Proposal status
    */
    public static function startVideoProposalCampaign($objectData)
    {
		$response = [];
		try
		{
			$session = Router::getRequest()->getSession();
			$login_agency_id= $session->read("Auth.User.agency_id");
			$login_user_id= $session->read('Auth.User.user_id');
			$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
			$AgencyCampaignMaster = TableRegistry::getTableLocator()->get('AgencyCampaignMaster');

			$contact_id = "";
			if(isset($objectData['contact_id']) && !empty($objectData['contact_id'])){
			  $contact_id = $objectData['contact_id'];
			}
			$opp_id="";
			$opp_detail="";
			if(isset($objectData['opportunity_id']) && !empty($objectData['opportunity_id'])){
			  $opp_id = $objectData['opportunity_id'];
			  $opp_detail = $ContactOpportunities->contactOpportunityDetail($opp_id);
			}

			$pipeline_stage = '';
			if(!empty($objectData['video_proposal_status']) && $objectData['video_proposal_status'] == _QUOTE_SENT_VIDEO_PROPOSAL)
			{
			  $pipeline_stage = _PIPELINE_CAMPAIGN_STAGE_QUOTE_SENT_VIDEO_PROPOSAL;
			}else{
			  $pipeline_stage = _PIPELINE_STAGE_QUOTE_SENT;
			}

			if(isset($pipeline_stage) && !empty($pipeline_stage)){
			  if(isset($opp_detail) && !empty($opp_detail) && isset($opp_detail['contact_business_id']) && !empty($opp_detail['contact_business_id']))
			  {
				$checkCampaignExist=$AgencyCampaignMaster->checkCampaignExist(null,null,$login_agency_id,$pipeline_stage,_CAMPAIGN_TYPE_PIPELINE,null,_LINE_COMMERCIAL);
				  if(isset($checkCampaignExist) && !empty($checkCampaignExist)){
					$start_campaign_id=$checkCampaignExist['id'];
					$campaign_result = CommonFunctions::startCampaign($contact_id, $start_campaign_id,$pipeline_stage,null,$opp_detail['user_id'],null,null,$opp_detail['contact_business_id'], $login_user_id);
				  }
			  }
			  else
			  {
				$checkCampaignExist=$AgencyCampaignMaster->checkCampaignExist(null,null,$login_agency_id,$pipeline_stage,_CAMPAIGN_TYPE_PIPELINE);
				  if(isset($checkCampaignExist) && !empty($checkCampaignExist)){

					$start_campaign_id=$checkCampaignExist['id'];
					$campaign_result = CommonFunctions::startCampaign($contact_id, $start_campaign_id,$pipeline_stage,null,$opp_detail['user_id'], null, null, null, $login_user_id);
				  }
			  }
			}

			if(isset($campaign_result['status']) && $campaign_result['status'] == _ID_SUCCESS)
			{
			  $response = (array('status' => _ID_SUCCESS,'type' => _CAMPAIGN_START));
			}
			else
			{
			    $response = json_encode(array('status' => _ID_FAILED));
			}
		}
		catch(\Exception $e) {

             $response = date('Y-m-d H:i:s').' :: startVideoProposalCampaign Error- '.$e->getMessage();

        }
		return $response;

    }

	/**
	 * Delete Sale Attchement
	 */
	public function deletSalesAttchment($attchmentId)
    {

		try
        {
            $myfile = fopen(ROOT."/logs/attachment_error.log", "a") or die("Unable to open file!");
			$SaleAttachments = TableRegistry::getTableLocator()->get('SaleAttachments');
			$attachmentDetails = $SaleAttachments->get($attchmentId);
			if(isset($attachmentDetails) && !empty($attachmentDetails))
			{
				if($SaleAttachments->deleteAll(['id'=>$attachmentDetails->id]))
				{
					return $response =  json_encode(array('status' => _ID_SUCCESS,'attachment_id'=>$attchmentId));die;
				}
				else
				{
					return $response = json_encode(array('status' => _ID_FAILED)); die;
				}

			}
			else
			{
					return $response = json_encode(array('status' => _ID_FAILED)); die;
			}
		}catch (\Exception $e) {

				$txt=date('Y-m-d H:i:s').' :: Attachment not deleted with attchment id- '.$attchmentId. $e->getMessage();
				fwrite($myfile,$txt.PHP_EOL);
		}

    }

	public static function getLostCampaignList($objectData){
		$session = Router::getRequest()->getSession();
		$AgencyCampaignMasterTable = TableRegistry::getTableLocator()->get('AgencyCampaignMaster');
		$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
		$login_agency_id= $session->read("Auth.User.agency_id");
		$opportunity_id = $objectData['opportunity_id'];
		$result = $ContactOpportunities->get($objectData['opportunity_id']);
		if($result['contact_id'] && !empty($result['contact_id'])){
			$line = _LINE_PERSONAL;
		}
		else if($result['contact_business_id'] && !empty($result['contact_business_id'])){
			$line = _LINE_COMMERCIAL;
		}

		$pipeline_stage = $objectData['pipeline_stage'];

		$campaignList = [];

		$campaignLost = $AgencyCampaignMasterTable->checkCampaignExist(null, null, $login_agency_id, 8, _CAMPAIGN_TYPE_PIPELINE, null, $line);
		$campaignLongTermNature = $AgencyCampaignMasterTable->checkCampaignExist(null, null, $login_agency_id, 1, _CAMPAIGN_TYPE_LONG_TERM_NURTURE, null, $line);
		$campaignXDate = $AgencyCampaignMasterTable->checkCampaignExist(null, null, $login_agency_id, 1, _CAMPAIGN_TYPE_X_DATE, null, $line);

		if(isset($campaignLost) && !empty($campaignLost)){
			array_push($campaignList, $campaignLost);
		}
		if(isset($campaignLongTermNature) && !empty($campaignLongTermNature)){
			array_push($campaignList, $campaignLongTermNature);
		}
		if(isset($campaignXDate) && !empty($campaignXDate)){
			array_push($campaignList, $campaignXDate);
		}

		if(count($campaignList) >= 0)
		{
			return  array('status'=>_ID_SUCCESS, 'campaignList'=> $campaignList);
		}else{
			return  array('status'=>_ID_FAILED);
		}
	}


	public static function validateEffectiveDate($opp_id){

		$session = Router::getRequest()->getSession();
		$login_agency_id = $session->read("Auth.User.agency_id");
		$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
		$AgencyCampaignMasterTable = TableRegistry::getTableLocator()->get('AgencyCampaignMaster');
		$line = _LINE_PERSONAL;
		$campaignXDate = $AgencyCampaignMasterTable->checkCampaignExist(null, null, $login_agency_id, 1, _CAMPAIGN_TYPE_X_DATE, null, $line);
		$result = $ContactOpportunities->get($opp_id);

		$data['effective_date'] = $result->effective_date;
		$data['campaign_id'] = $campaignXDate->id;

		return $data;
	}

	public static function splitPolicyOpportunity($id){

		$contactOpportunityTable = TableRegistry::getTableLocator()->get('ContactOpportunities');
		$ContactOpportunity = $contactOpportunityTable->newEntity();
		$opportunityInfo = [];
		$opportunity_detail = $contactOpportunityTable->get($id);
		if(isset($opportunity_detail)){
			$opportunityInfo['agency_id'] = $opportunity_detail['agency_id'];
			$opportunityInfo['user_id'] = $opportunity_detail['user_id'];
			$opportunityInfo['contact_id'] = $opportunity_detail['contact_id'];
			$opportunityInfo['contact_business_id'] = $opportunity_detail['contact_business_id'];
			$opportunityInfo['insurance_type_id'] = $opportunity_detail['insurance_type_id'];
			$opportunityInfo['policy_number'] = $opportunity_detail['policy_number'];
			$opportunityInfo['premium_amount'] = $opportunity_detail['premium_amount'];
			$opportunityInfo['term_length'] = $opportunity_detail['term_length'];
			$opportunityInfo['carrier_id'] = $opportunity_detail['carrier_id'];
			$opportunityInfo['mga_id'] = $opportunity_detail['mga_id'];
			$opportunityInfo['sales_title'] = $opportunity_detail['sales_title'];
			$opportunityInfo['effective_date'] = $opportunity_detail['effective_date'];
			
			$ContactOpportunity = $contactOpportunityTable->patchEntity($ContactOpportunity, $opportunityInfo);
			// dd($opportunityInfo);

			if($ContactOpportunity = $contactOpportunityTable->save($ContactOpportunity)){
				$contactOpportunityTable->updateAll(['status' => _ID_STATUS_DELETED],['id' => $id]);
				$response = array('status' => _ID_SUCCESS);
			}else{
				$response = array('status' => _ID_FAILED);
			}

		}else{
			$response = array('status' => _ID_FAILED);
		}

		return $response;
	}

	public static function getAutocompleteContactsList($objectData){

        $session = Router::getRequest()->getSession();
		$login_agency_id = $session->read('Auth.User.agency_id');
		$login_user_id= $session->read("Auth.User.user_id");
		$login_role_type = 		$session->read('Auth.User.role_type');
		$login_first_name = 		$session->read('Auth.User.first_name');
		$login_last_name = 		$session->read('Auth.User.last_name');
		$login_role_type_flag = 	$session->read('Auth.User.role_type_flag');
		$login_permissions      = $session->read('Auth.User.permissions');
		$login_permissions_arr  =   explode(",",$login_permissions);
		$Contacts = TableRegistry::getTableLocator()->get('Contacts');
		$ContactBusiness = TableRegistry::getTableLocator()->get('ContactBusiness');
		$personal_commercial = $objectData['personal_commercial'];
        $contact_type="";
		$data = [];
		$data[0]['id'] =   'new_contact';
		$data[0]['name'] = 'Create New Contact';
		$i=1;
		$keyword =  $objectData['keyword'];
        if($personal_commercial==_PERSONAL_CONTACT){
            $contacts_list = $Contacts->searchAutocompleteContactsForSalesPipeline($keyword,$login_agency_id,$contact_type,$login_user_id,$login_role_type,$login_role_type_flag,$search_arr);
          //  echo "<pre>";print_r($contacts_list);die("Dfd");
            $contacts_list = array_map("unserialize", array_unique(array_map("serialize", $contacts_list)));

            foreach($contacts_list as $contact){
				$name = '';
				if(!empty($contact->first_name)){
                    $name = trim($contact->first_name);
                } if(!empty($contact->middle_name)){
                    $name = trim($contact->first_name)." ".trim($contact->middle_name);
                }if(!empty($contact->last_name)){
                    $name = trim($contact->first_name)." ".trim($contact->last_name);
                }if(!empty($contact->middle_name) && !empty($contact->last_name)){
                    $name = trim($contact->first_name)." ".trim($contact->middle_name)." ".trim($contact->last_name);
                }else{
                    $name = trim($contact->first_name)." ".trim($contact->middle_name)." ".trim($contact->last_name);
                }
				$name = trim($name);
				$name = trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $name)));
                if(!empty($name))
				{
					$data[$i]['id'] =    $contact->id;
					$data[$i]['name'] = $name;
					$i++;
				}

            }

        }else{
            $business_list = $ContactBusiness->searchAutocompleteBusiness($keyword,$login_agency_id,$contact_type,$login_user_id,$login_role_type,$login_role_type_flag,$search_arr);
            $business_list = array_map("unserialize", array_unique(array_map("serialize", $business_list)));
          //echo "<pre>";print_r($business_list);die();
            foreach($business_list as $contact){
				$name = '';
                if($contact['personal_or_commercial'] == _COMMERCIAL_CONTACT)
                {
					if(!empty(trim($contact['name'])))
					{
						$data[$i]['id'] =    $contact->id;
						$data[$i]['contact_id'] =    $contact->contact_id;
						$data[$i]['name'] =  !empty($contact['name']) ? $contact['name'] : "";
						if(isset($contact['dba']) && !empty($contact['dba']))
						{
							$data[$i]['name'] = $data[$i]['name']." - AKA ".$contact['dba'];
						}
						$i++;
					}

                }


            }
        }
		return $data;
  }


  public function saveSalesOpportunityDetails($objectData){

	ini_set('memory_limit', '-1');
	ini_set('max_execution_time', '-1');
	$session = Router::getRequest()->getSession();
	$login_agency_id = $session->read('Auth.User.agency_id');
	$login_user_id= $session->read("Auth.User.user_id");
	$login_role_type = 		$session->read('Auth.User.role_type');
	$login_role_type_flag = 	$session->read('Auth.User.role_type_flag');
	$contact_opportunities_info=[];
	$personal_commercial='';

	$Contacts = TableRegistry::getTableLocator()->get('Contacts');
	$PhoneNumbersOptInOutStatus = TableRegistry::getTableLocator()->get('PhoneNumbersOptInOutStatus');
	$ContactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
	$ContactPolicyRenewal = TableRegistry::getTableLocator()->get('ContactPolicyRenewal');
	$AgencyCampaignMaster = TableRegistry::getTableLocator()->get('AgencyCampaignMaster');
	$ContactAppointments = TableRegistry::getTableLocator()->get('ContactAppointments');
	$ContactBusiness = TableRegistry::getTableLocator()->get('ContactBusiness');
	$BusinessPrimaryContact = TableRegistry::getTableLocator()->get('BusinessPrimaryContact');
	$BusinessCommunication = TableRegistry::getTableLocator()->get('BusinessCommunication');
	$BusinessLinkedContact = TableRegistry::getTableLocator()->get('BusinessLinkedContact');
	$TeamUserLinks = TableRegistry::getTableLocator()->get('TeamUserLinks');
	$CampaignRunningSchedule = TableRegistry::getTableLocator()->get('CampaignRunningSchedule');

	$usersTimezone='America/Phoenix';

	if(isset($objectData['personal_or_commercial']) && !empty($objectData['personal_or_commercial'])){
		$personal_commercial = $objectData['personal_or_commercial'];

	}
	$contact_id='';
	if(isset($objectData['contact_id']) && !empty($objectData['contact_id'])){
		$contact_id = $objectData['contact_id'];
	}
	$business_id='';
		if(isset($objectData['business_id']) && !empty($objectData['business_id'])){
			$business_id = $objectData['business_id'];

		}
	$policy_id = '';
	if(isset($objectData['policy_id']) && !empty($objectData['policy_id'])){
		$policy_id = $objectData['policy_id'];
	}

	$pipeline_stage_id = '';
	if(isset($objectData['pipeline_stage_id']) && !empty($objectData['pipeline_stage_id'])){
		$pipeline_stage_id = $objectData['pipeline_stage_id'];

		if($pipeline_stage_id ==_PIPELINE_STAGE_WON){
			$contact_opportunities_info['status']=_ID_STATUS_ACTIVE;
			$contact_opportunities_info['won_date']=date('Y-m-d');
			$pipeline_stage = _PIPELINE_STAGE_WON;
			if($this->request->data['effective_date'] <= CommonFunctions::convertUtcToEmployeeTimeZone($usersTimezone,date("Y-m-d"))){
				$contact_opportunities_info['pending_sub_status'] = '';
				$contact_opportunities_info['active_sub_status'] = _ID_ACTIVE_SUB_STATUS_ACTIVE;
			}elseif($this->request->data['effective_date'] > CommonFunctions::convertUtcToEmployeeTimeZone($usersTimezone,date("Y-m-d")))
			{
				$contact_opportunities_info['pending_sub_status'] = '';
				$contact_opportunities_info['active_sub_status'] = _ID_ACTIVE_SUB_STATUS_FUTURE_EFFECTIVE;
			}
		}
		else if($pipeline_stage_id ==_PIPELINE_STAGE_LOST){
		    $contact_opportunities_info['status'] = _ID_STATUS_INACTIVE;
			$contact_opportunities_info['lost_date']=date('Y-m-d');
			$contact_opportunities_info['pending_sub_status'] = _ID_PENDING_SUB_STATUS_LOST;
			$contact_opportunities_info['active_sub_status'] = NULL;
		}
		else if($pipeline_stage_id ==_PIPELINE_STAGE_QUOTE_SENT){
		    $contact_opportunities_info['status']=_ID_STATUS_PENDING;
			$contact_opportunities_info['quote_sent_date']=date('Y-m-d');
			$contact_opportunities_info['pending_sub_status']=_ID_PENDING_SUB_STATUS_QUOTED;
			$contact_opportunities_info['active_sub_status'] = NULL;
		}else{
			$contact_opportunities_info['status']=_ID_STATUS_PENDING;
			if($pipeline_stage_id ==_PIPELINE_STAGE_NEW_LEAD || $pipeline_stage_id ==_PIPELINE_STAGE_APPOINTMENT_SCHEDULED || $pipeline_stage_id ==_PIPELINE_STAGE_WORKING){
			$contact_opportunities_info['pending_sub_status']=_ID_PENDING_SUB_STATUS_NEW_LEAD;
			$contact_opportunities_info['active_sub_status'] = NULL;

			}elseif($pipeline_stage_id ==_PIPELINE_STAGE_QUOTE_READY){
			$contact_opportunities_info['pending_sub_status']=_ID_PENDING_SUB_STATUS_QUOTED;
			$contact_opportunities_info['active_sub_status'] = NULL;
			}
		}

	}

	$assign_sales_owner_id='';
	if(isset($objectData['assigned_owner']) && !empty($objectData['assigned_owner']) && $objectData['assigned_owner'] == 'sales_team')
	{

		$sales_team_owner_id = CommonFunctions::getSalesUserIDToAssignDefault($login_agency_id,_AGENCY_TEAM_SALES,$personal_commercial);

		if(isset($sales_team_owner_id['user_id_to_return']) && !empty($sales_team_owner_id['user_id_to_return']))
		{

			$assign_sales_owner_id = $sales_team_owner_id['user_id_to_return'];
		} else
		{
			$round_robin_sales_arr = CommonFunctions::getSalesUserIDbyRoundRobinDefault($login_agency_id,$personal_commercial);

			if(isset($round_robin_sales_arr['user_id_to_return']) && !empty($round_robin_sales_arr['user_id_to_return'])){
				$sales_owner_id=$round_robin_sales_arr['user_id_to_return'];
				$assign_sales_owner_id = $sales_owner_id;
			}
		}

	}else if(isset($objectData['assigned_owner']) && !empty($objectData['assigned_owner']) && $objectData['assigned_owner'] != 'sales_team')
	{
		$assign_sales_owner_id = $objectData['assigned_owner'];
	}

	$effectiveDate = '';


	$sales_policy_term_length = '';
	$totaltermlength = 0;
	if(isset($objectData['sales_policy_term_length']) && !empty($objectData['sales_policy_term_length']) || isset($objectData['effective_date']) && !empty($objectData['effective_date'])){

		if(isset($objectData['effective_date']) && !empty($objectData['effective_date'])){
			$effectiveDate = $objectData['effective_date'];
		}
		// else{
		// 	$effectiveDate = date('Y-m-d',strtotime(CommonFunctions::convertUtcToEmployeeTimeZone($usersTimezone,date("Y-m-d"))));
		// }

		$sales_policy_term_length = $objectData['sales_policy_term_length'];

		if($objectData['sales_policy_term_length'] == 'other'){

			if(isset($objectData['sales_term_length_period']) && !empty($objectData['sales_term_length_period'])){

			   $contact_opportunities_info['term_length_period'] = 2;
			   $totaltermlength =  (int) $objectData['sales_term_length_period']*12;

			}else{

				$contact_opportunities_info['term_length_period'] = 1;
				$totaltermlength =  0;
			}

			if(isset($objectData['sales_term_length_text'])){

			   $totaltermlength =  $totaltermlength+$objectData['sales_term_length_text'];

			}else{
				$totaltermlength =  $totaltermlength;
			}
			if($totaltermlength !=0)
			{
				$contact_opportunities_info['term_length'] = $totaltermlength;
			}
		}else{
			$contact_opportunities_info['term_length']= $sales_policy_term_length;
			$contact_opportunities_info['term_length_period']= 1;
		}

		//$effectiveDate = date('Y-m-d', strtotime("+".$totaltermlength." months", strtotime($effectiveDate)));

	}
if(isset($objectData['contact_type']) && !empty($objectData['contact_type']) && $objectData['contact_type'] == _CONTACT_TYPE_INACTIVE_CLIENT)
  {
	  $contact_opportunities_info['status'] = _ID_STATUS_INACTIVE;
	  $contact_opportunities_info['inactive_sub_status'] = _ID_INACTIVE_SUB_STATUS_CANCELLED;
  }


	$premium_amount = '';
	if(isset($objectData['premium_amount']) && !empty($objectData['premium_amount'])){
		$premium_amount = $objectData['premium_amount'];
	}

	$commission_data = [
		'insurance_type_id' => $objectData['policy_id'],
		'carrier_id' => $objectData['carrier_id'],
		'premium' => $objectData['premium_amount'],
		'contact_id' => $objectData['contact_id']
	];

	//To get the commission amount and update it with policy
	$commission_details = ContactOpportunities::getCommisionAmount($commission_data);
	$commission_amount = '';
	if(!empty($commission_details)){
		$decoded_commission_details  = json_decode($commission_details);
		$commission_amount = ($objectData['premium_amount']*$decoded_commission_details->commission_agency)/100; 
	}

	$carrier_id = '';
	if(isset($objectData['carrier_id']) && !empty($objectData['carrier_id'])){
		$carrier_id = $objectData['carrier_id'];
	}

	$sales_policy_number = '';
	if(isset($objectData['sales_policy_number']) && !empty($objectData['sales_policy_number'])){
		$sales_policy_number = trim($objectData['sales_policy_number']);
	}

	$rewrite_status = 0;
	if(isset($objectData['sales_rewrite']) && !empty($objectData['sales_rewrite'])){
		$sales_rewrite = $objectData['sales_rewrite'];
		if($sales_rewrite=='on'){
			$rewrite_status = 1;
		}
	}

	if(isset($objectData['create_new_contact']) && !empty($objectData['create_new_contact']))
	{
		  $personal_or_commercial = _PERSONAL_CONTACT;
		  if(isset($objectData['personal_or_commercial']) && !empty($objectData['personal_or_commercial'])){
				$personal_or_commercial = $objectData['personal_or_commercial'];
			}
		  if($personal_or_commercial == _PERSONAL_CONTACT)
		  {
			$contact = $Contacts->newEntity();
			$info = [];
			if(isset($objectData['contact_first_name']) && !empty($objectData['contact_first_name']))
			{
				$info['first_name'] = $objectData['contact_first_name'];
			}

			if(isset($objectData['contact_middle_name']) && !empty($objectData['contact_middle_name']))
			{
				$info['middle_name'] = $objectData['contact_middle_name'];
			}

			if(isset($objectData['contact_last_name']) && !empty($objectData['contact_last_name']))
			{
				$info['last_name'] = $objectData['contact_last_name'];
			}

			if(isset($objectData['preferred_name']) && !empty($objectData['preferred_name']))
			{
				$info['preferred_name'] = $objectData['preferred_name'];
			}

			if(isset($objectData['contact_email']) && !empty($objectData['contact_email']))
			{
				$info['email'] = trim($objectData['contact_email']);
			}

			if(isset($objectData['contact_phone']) && !empty($objectData['contact_phone']))
			{
				$phone = $objectData['contact_phone'];
				$patterns = array('/\-/','/\(/','/\)/','/\+/','/\ /');
				$phone = preg_replace($patterns,'',$phone);
				$info['phone']=trim($phone);
			}

			//address line 1
			if(isset($objectData['address_line_1']) && !empty($objectData['address_line_1']))
			{
				$info['address'] = $objectData['address_line_1'];
			}

			//address line 2
			if(isset($objectData['address_line_2']) && !empty($objectData['address_line_2']))
			{
				$info['address_line_2'] = $objectData['address_line_2'];
			}

			if(isset($objectData['city']) && !empty($objectData['city']))
			{
				$info['city'] = $objectData['city'];
			}


			if(isset($objectData['state']) && !empty($objectData['state']))
			{
				$info['state_id']=$objectData['state'];
			}


			if(isset($objectData['zip']) && !empty($objectData['zip']))
			{
				$info['zip']=$objectData['zip'];
			}
			// best time to reach
			if(isset($objectData['select_bestTimeToReach']) && !empty($objectData['select_bestTimeToReach']))
			{
				$info['best_time_to_reach']=$objectData['select_bestTimeToReach'];
			}

			if(isset($objectData['select_ownsOrRent']) && !empty($objectData['select_ownsOrRent']))
			{
				$info['owns_rent']=$objectData['select_ownsOrRent'];
			}


			if(!empty($objectData['birth_date'])){
				$newdatetime = str_replace('-', '/', $objectData['birth_date']);
				$info['birth_date']=date("Y-m-d",strtotime($newdatetime));
			}

			$info['agency_id']=  $login_agency_id;

			if(isset($assign_sales_owner_id) && !empty($assign_sales_owner_id))
			{
				$info['user_id'] =  $assign_sales_owner_id;
			}else{
				$info['user_id'] =  $login_user_id;
			}
			//for inactive clients
			if(isset($objectData['contact_type']) && !empty($objectData['contact_type']))
			{
				if($objectData['contact_type'] == _CONTACT_TYPE_LEAD)
				{
					$info['lead_type']=_CONTACT_TYPE_LEAD;
				}
				elseif($objectData['contact_type'] == _CONTACT_TYPE_CLIENT)
				{
					$info['lead_type']=_CONTACT_TYPE_CLIENT;
				}else
				{
					$info['lead_type'] = _CONTACT_TYPE_CLIENT;
                    $info['status'] = _ID_STATUS_INACTIVE;
				}
			}
			if(!empty($objectData['lead_source_id']))
			{
				$lead_source_type_id=$objectData['lead_source_id'];
				$info['lead_source_type']=$lead_source_type_id;

			}
			$contact = $Contacts->patchEntity($contact, $info);
			if ($contact=$Contacts->save($contact))
			{
			// logs to check the inserted record start
                $txt = date('Y-m-d H:i:s').' :: Contact added from beta pipeline Contact Id: '.$contact->id . ' Agency Id: '.$contact->agency_id .' User Id: '.$contact->user_id;
                FileLog::writeLog("InsertedContactLog", $txt);
            // logs to check the inserted record end
                $contact_id = $contact->id;
				if(isset($phone) && !empty($phone))
				{
					//insert OPT IN status and log
					$optoutData = [];
					$updateoptoutData=[];
					$optoutData['agency_id'] = $login_agency_id;
					$optoutData['user_id'] = $info['user_id'];
					$optoutData['phone_number'] = $phone;
					$optoutData['contact_id'] = $contact->id;

					if(isset($objectData['sms_opt_in_check']) && !empty($objectData['sms_opt_in_check']) && $objectData['sms_opt_in_check']==_ID_STATUS_TURNON)
					{

						$optoutData['status'] = _ID_SUCCESS;
						$optoutData['platform'] = _PLATFORM_TYPE_SYSTEM;
						CommonFunctions::saveSmsOptInLog($optoutData);
						$update_opt_in_out_status = _ID_SUCCESS;

					}else{

						$optoutData['status'] = _ID_FAILED;
						$update_opt_in_out_status = _ID_FAILED;
					}

					$checkNumberExistOrNot = $PhoneNumbersOptInOutStatus->checkOptInOptOutStatus($login_agency_id,$phone);

					if(isset($checkNumberExistOrNot['result']) && !empty($checkNumberExistOrNot['result'])){
						$contactOptOutData = $PhoneNumbersOptInOutStatus->get($checkNumberExistOrNot['result']['id']);

						if($checkNumberExistOrNot['status']==_ID_FAILED){
							$updateoptoutData['status'] = $update_opt_in_out_status;
						}

						if(empty($checkNumberExistOrNot['result']['contact_id'])){
							$updateoptoutData['contact_id'] = $contact->id;
						}
						if(isset($contactOptOutData) && !empty($contactOptOutData)){
						$contactOptOutData = $PhoneNumbersOptInOutStatus->patchEntity($contactOptOutData,$updateoptoutData);
							$PhoneNumbersOptInOutStatus->save($contactOptOutData);
						}

					}else{
						$contactOptOutData = $PhoneNumbersOptInOutStatus->newEntity();
						$contactOptOutData = $PhoneNumbersOptInOutStatus->patchEntity($contactOptOutData,$optoutData);
						$PhoneNumbersOptInOutStatus->save($contactOptOutData);
					}

				}

			}
		  } else if($personal_or_commercial == _COMMERCIAL_CONTACT)
		  {
			  $contact_business_id="";
			  if(isset($objectData['business_name']) && !empty($objectData['business_name']))
			  {
				  $business_details =array();
				  $business_details['business_name']=$objectData['business_name'];
				  if($objectData['contact_type'] == _CONTACT_TYPE_LEAD)
				  {
						  $business_details['lead_type']=_CONTACT_TYPE_LEAD;
				  }
				  else
				  {
						  $business_details['lead_type']=_CONTACT_TYPE_CLIENT;
				  }
				  $business_details['agency_id']=  $login_agency_id;

				  if(isset($assign_sales_owner_id) && !empty($assign_sales_owner_id))
				  {
					  $business_details['user_id'] = $assign_sales_owner_id;
				  }else{
					  $business_details['user_id'] =  $login_user_id;
				  }

				  $subscribe_token = CommonFunctions::generateRandomString(40);
				  $business_details['subscribe_token'] = $subscribe_token;

				  if(!empty($objectData['expiration_date'])){
					  $newExpDate = str_replace('-', '/', $objectData['expiration_date']);
					  $business_details['expiration_date']=date("Y-m-d",strtotime($newExpDate));
				  }

				  //Add winback X date
				  if(isset($objectData['winback_X_date']) && !empty($objectData['winback_X_date'])){
					  //$dateTime = str_replace('-', '/', $this->request->data['winback_x_date']);
					  $business_details['winback_x_date']=date("Y-m-d",strtotime($objectData['winback_X_date']));
				  }
				  if(isset($objectData['business_dba']) && !empty($objectData['business_dba']))
				  {
					  $business_details['business_dba']=$objectData['business_dba'];
				  }
				  if(isset($objectData['business_structure']) && !empty($objectData['business_structure']))
				  {
					  $business_details['business_structure']=$objectData['business_structure'];
				  }
				  //for inactive clients
				  if($objectData['contact_type'] == _CONTACT_TYPE_INACTIVE_CLIENT)
				  {
					  $business_details['status'] = _ID_STATUS_INACTIVE;
				  }
					if(!empty($objectData['lead_source_id']))
					{
					$business_details['lead_source_type']=$objectData['lead_source_id'];
					}

				  $saved_business_detail = CommonFunctions::addNewBusiness(null,$business_details);
				  if($saved_business_detail)
				  {

					  $business_id=$saved_business_detail->id;

					  $ContactBusiness->updateAll(['order_by_recent' => date('Y-m-d H:i:s'),'lead_type' => $business_details['lead_type']],['id'=>$business_id]);
					  //create dummy business contact

					if($objectData['link_personal_contact'] == _STATUS_TRUE)
					  {
						  $link_contact_id="";
						  if($objectData['link_existing_contact'] == _STATUS_TRUE)
						  {
							  if(isset($objectData['selected_contact_id']) && !empty($objectData['selected_contact_id']))
							  {
								  $link_contact_id = $objectData['selected_contact_id'];
							  }
						  }
						  else
						  {
							  //create business primary contact
							  $business_additional_contact_arr = [];
							  $business_additional_contact_phone_arr = [];
							  $business_additional_contact_email_arr = [];
							  if(isset($objectData['primary_contact_first_name']) && !empty($objectData['primary_contact_first_name']))
							  {
								  $business_additional_contact_arr['first_name'] = trim($objectData['primary_contact_first_name']);
							  }
							  if(isset($objectData['primary_contact_last_name']) && !empty($objectData['primary_contact_last_name']))
							  {
								  $business_additional_contact_arr['last_name'] = trim($objectData['primary_contact_last_name']);
							  }
							  if(isset($objectData['primary_contact_middle_name']) && !empty($objectData['primary_contact_middle_name']))
							  {
								  $business_additional_contact_arr['middle_name'] = trim($objectData['primary_contact_middle_name']);
							  }
							  if(isset($objectData['primary_contact_preferred_name']) && !empty($objectData['primary_contact_preferred_name']))
							  {
								  $business_additional_contact_arr['preferred_name'] = trim($objectData['primary_contact_preferred_name']);
							  }
							  if(isset($objectData['primary_contact_phone']) && !empty($objectData['primary_contact_phone']))
							  {
								  $primary_contact_phone = $objectData['primary_contact_phone'];
								  $patterns = array('/\-/','/\(/','/\)/','/\+/','/\ /');
								  $primary_contact_phone = preg_replace($patterns,'',$primary_contact_phone);
								  $business_additional_contact_phone_arr['email_phone'] = $primary_contact_phone;
								  $business_additional_contact_phone_arr['email_phone_type'] = $objectData['primary_contact_phone_number_type'];
							  }
							  if(isset($objectData['primary_contact_email']) && !empty($objectData['primary_contact_email']))
							  {
								  $business_additional_contact_email_arr['email_phone'] = trim($objectData['primary_contact_email']);
							  }
							  $business_additional_contact_arr['primary_flag'] = _STATUS_TRUE;

							  //$business_additional_contact_arr['contact_id'] = $contact_id;
							  $business_additional_contact_arr['contact_business_id'] = $business_id;
							  //contact business entry
							  $business_primary_contact = $BusinessPrimaryContact->newEntity();
							  $business_primary_contact = $BusinessPrimaryContact->patchEntity($business_primary_contact, $business_additional_contact_arr);
							  if($business_primary_contact = $BusinessPrimaryContact->save($business_primary_contact))
							  {
									  //$business_additional_contact_phone_arr['contact_id'] = $contact_id;
									  $business_additional_contact_phone_arr['contact_business_id'] = $business_id;
									  $business_additional_contact_phone_arr['business_primary_contact_id'] = $business_primary_contact->id;
									  $business_additional_contact_phone_arr['business_communication_type'] = _PHONE;
									  $business_additional_contact_phone_arr['primary_flag'] = _STATUS_TRUE;
									  $primary_contact_phone = $BusinessCommunication->newEntity();
									  $primary_contact_phone = $BusinessCommunication->patchEntity($primary_contact_phone, $business_additional_contact_phone_arr);
									  $BusinessCommunication->save($primary_contact_phone);
									  //$business_additional_contact_email_arr['contact_id'] = $contact_id;
									  $business_additional_contact_email_arr['contact_business_id'] = $business_id;
									  $business_additional_contact_email_arr['business_primary_contact_id'] = $business_primary_contact->id;
									  $business_additional_contact_email_arr['business_communication_type'] = _EMAIL;
									  $business_additional_contact_email_arr['primary_flag'] = _STATUS_TRUE;
									  $primary_contact_email = $BusinessCommunication->newEntity();
									  $primary_contact_email = $BusinessCommunication->patchEntity($primary_contact_email, $business_additional_contact_email_arr);
									  $BusinessCommunication->save($primary_contact_email);
							  }
						  }
					  } else {
						//create business primary contact even personal contact linking option is disabled
						if(isset($objectData['primary_contact_email']) && !empty($objectData['primary_contact_email']))
						{
							$business_additional_contact_email_arr['email_phone'] = trim($objectData['primary_contact_email']);
						}
						$business_additional_contact_arr['primary_flag'] = _STATUS_TRUE;

						$business_additional_contact_arr['contact_business_id'] = $business_id;
						//contact business entry
						$business_primary_contact = $BusinessPrimaryContact->newEntity();
						$business_primary_contact = $BusinessPrimaryContact->patchEntity($business_primary_contact, $business_additional_contact_arr);
						if($business_primary_contact = $BusinessPrimaryContact->save($business_primary_contact))
						{
							FileLog::writeLog("businessDetails", "Here is the details ".json_encode($business_primary_contact));
							$business_additional_contact_phone_arr['contact_business_id'] = $business_id;
							$business_additional_contact_phone_arr['business_primary_contact_id'] = $business_primary_contact->id;
							$business_additional_contact_phone_arr['business_communication_type'] = _PHONE;
							$business_additional_contact_phone_arr['primary_flag'] = _STATUS_TRUE;
							$primary_contact_phone = $BusinessCommunication->newEntity();
							$primary_contact_phone = $BusinessCommunication->patchEntity($primary_contact_phone, $business_additional_contact_phone_arr);
							$BusinessCommunication->save($primary_contact_phone);
							$business_additional_contact_email_arr['contact_business_id'] = $business_id;
							$business_additional_contact_email_arr['business_primary_contact_id'] = $business_primary_contact->id;
							$business_additional_contact_email_arr['business_communication_type'] = _EMAIL;
							$business_additional_contact_email_arr['primary_flag'] = _STATUS_TRUE;
							$primary_contact_email = $BusinessCommunication->newEntity();
							$primary_contact_email = $BusinessCommunication->patchEntity($primary_contact_email, $business_additional_contact_email_arr);
							$BusinessCommunication->save($primary_contact_email);
						}
					  }

					  //save business and contact linking
					  if(isset($link_contact_id) && !empty($link_contact_id) && isset($business_id) && !empty($business_id))
					  {
						  // $business_linked_contact_arr=[];
						  $business_linked_contact_arr['contact_id'] = $link_contact_id;
						  $business_linked_contact_arr['contact_business_id'] = $business_id;
						  $business_linked_contact_arr['primary_flag'] = _STATUS_TRUE;
						  $business_contact_link = $BusinessLinkedContact->newEntity();
						  $business_contact_link = $BusinessLinkedContact->patchEntity($business_contact_link,$business_linked_contact_arr);
						  $business_contact_link = $BusinessLinkedContact->save($business_contact_link);
					  }



					  //save business primary address
					  $business_details['business_address_primary_flag'] = _STATUS_TRUE;
					  $business_details['business_address_mailing_flag'] = _STATUS_TRUE;
					  if(isset($objectData['business_address_line1']) && !empty($objectData['business_address_line1']))
					  {
						  $business_details['business_primary_address_line1'] = $objectData['business_address_line1'];
					  }
					  if(isset($objectData['business_address_line2']) && !empty($objectData['business_address_line2']))
					  {
						  $business_details['business_primary_address_line2'] = $objectData['business_address_line2'];
					  }
					  if(isset($objectData['business_city']) && !empty($objectData['business_city']))
					  {
						  $business_details['business_primary_address_city'] = $objectData['business_city'];
					  }
					  if(isset($objectData['business_state']) && !empty($objectData['business_state']))
					  {
						  $business_details['business_primary_address_state_id'] = $objectData['business_state'];
					  }
					  if(isset($objectData['business_zip']) && !empty($objectData['business_zip']))
					  {
						  $business_details['business_primary_address_zip'] = $objectData['business_zip'];
					  }
					  CommonFunctions::saveBusinessAddress(null,$business_id,$business_details);



				  }
			  }

		  }
	}

		$contact_opportunities_info['agency_id']=$login_agency_id;
		if(isset($business_id) && !empty($business_id))
		{
		$contact_opportunities_info['contact_business_id']=$business_id;
		}
		else
		{
		$contact_opportunities_info['contact_id']=$contact_id;
		}
		$contact_opportunities_info['insurance_type_id']=$policy_id;
		$contact_opportunities_info['pipeline_stage']=$pipeline_stage_id;
		$contact_opportunities_info['carrier_id']=$carrier_id;

		if(!empty($assign_sales_owner_id))
		{
		$contact_opportunities_info['user_id'] = $assign_sales_owner_id;
		}
		else
		{
		$contact_opportunities_info['user_id'] = $login_user_id;

		}
		$contact_opportunities_info['effective_date']=$effectiveDate;
		$contact_opportunities_info['premium_amount']=$premium_amount;
		$contact_opportunities_info['commission_amount'] = $commission_amount;
		$contact_opportunities_info['policy_number']=$sales_policy_number;
		$contact_opportunities_info['rewrite_status']=$rewrite_status;
		//$contact_opportunities_info['status']=_ID_STATUS_ACTIVE;
		$contact_opportunities_info['platform'] = _PLATFORM_TYPE_SYSTEM;
		$contact_opportunities_info['date_stage_moved'] = date('Y-m-d H:i:s');
		$contact_opportunities_info['primary_flag'] = _ID_STATUS_ACTIVE;
		if (isset($objectData['appointment_time']) && !empty($objectData['appointment_time']) && $objectData['pipeline_stage_id'] == _PIPELINE_STAGE_APPOINTMENT_SCHEDULED) {
			$appointment_date_con = date("Y-m-d H:i:s", strtotime($objectData['appointment_date'] . ' ' . $objectData['appointment_time']));
			$contact_opportunities_info['appointment_date'] = $appointment_date_con;
		}
        if(isset($objectData['sales_title']) && $objectData['sales_title'] !== '')
        {
            $contact_opportunities_info['sales_title'] = $objectData['sales_title'];
        }

		$contact_opportunities = $ContactOpportunities->newEntity();
		$contact_opportunities = $ContactOpportunities->patchEntity($contact_opportunities, $contact_opportunities_info);
		if($contact_opportunities = $ContactOpportunities->save($contact_opportunities)){
			// save logs when new policy is created.
			CommonFunctions :: saveNewPolicyLogForContact( $contact_opportunities->id );

			if(isset($round_robin_sales_arr['team_user_link_id']) && !empty($round_robin_sales_arr['team_user_link_id'])){
				$team_user_link_id=$round_robin_sales_arr['team_user_link_id'];
				$teamUserLinkDetails=$TeamUserLinks->teamUserLinkDetails($team_user_link_id);
				if(isset($teamUserLinkDetails) && !empty($teamUserLinkDetails))
					$current_lead=$teamUserLinkDetails['current_lead']+1;
					$TeamUserLinks->updateAll(['current_lead'=>$current_lead],['id'=>$team_user_link_id]);
			}
			elseif(isset($sales_team_owner_id['team_user_link_id']) && !empty($sales_team_owner_id['team_user_link_id']))
			{
					$team_user_link_id=$sales_team_owner_id['team_user_link_id'];
					$teamUserLinkDetails=$TeamUserLinks->teamUserLinkDetails($team_user_link_id);
					if(isset($teamUserLinkDetails) && !empty($teamUserLinkDetails))
					$current_lead=$teamUserLinkDetails['current_lead']+1;
					$TeamUserLinks->updateAll(['current_lead'=>$current_lead],['id'=>$team_user_link_id]);
			}

	$ContactOpportunities->updateAll(['sort_order' =>$contact_opportunities->id],['id' => $contact_opportunities->id]);
	$previousLeadSourceId = $Contacts->find()->select(['lead_source_type'])->where(['id' => $contact_opportunities->contact_id])->hydrate(false)->first();

	//make primary_flag_all_stages true
	if(isset($contact_opportunities->contact_business_id) && !empty($contact_opportunities->contact_business_id))
	{
		$ContactOpportunities->updateAll(['primary_flag_all_stages'=>_ID_STATUS_PENDING],['contact_business_id' => $contact_opportunities->contact_business_id,'pipeline_stage'=>$contact_opportunities->pipeline_stage,'agency_id'=>$login_agency_id]);
		$ContactOpportunities->updateAll(['primary_flag_all_stages'=>_ID_STATUS_ACTIVE],['id' => $contact_opportunities->id]);
		$checkXdateCampaignExist = $CampaignRunningSchedule->getXDateCampaignActiveContact(null, $contact_opportunities->contact_business_id, _CAMPAIGN_TYPE_LONG_TERM_NURTURE);
		if(empty($checkXdateCampaignExist))
		{
		  $checkXdateCampaignExist = $CampaignRunningSchedule->getXDateCampaignActiveContact(null, $contact_opportunities->contact_business_id, _CAMPAIGN_TYPE_X_DATE);
		}
	}
	else if(isset($contact_opportunities->contact_id) && !empty($contact_opportunities->contact_id))
	{
		$ContactOpportunities->updateAll(['primary_flag_all_stages'=>_ID_STATUS_PENDING],['contact_id' => $contact_opportunities->contact_id,'pipeline_stage'=>$contact_opportunities->pipeline_stage,'agency_id'=>$login_agency_id,'contact_business_id IS NULL']);
		$ContactOpportunities->updateAll(['primary_flag_all_stages'=>_ID_STATUS_ACTIVE],['id' => $contact_opportunities->id]);
		$checkXdateCampaignExist = $CampaignRunningSchedule->getXDateCampaignActiveContact($contact_opportunities->contact_id, null, _CAMPAIGN_TYPE_LONG_TERM_NURTURE);
		if(empty($checkXdateCampaignExist))
		{
		  $checkXdateCampaignExist = $CampaignRunningSchedule->getXDateCampaignActiveContact($contact_opportunities->contact_id,null, _CAMPAIGN_TYPE_X_DATE);
		}
	}
	if(!empty($checkXdateCampaignExist) && $pipeline_stage != _PIPELINE_STAGE_LOST)
	{
		$puaseXdateCampaign = CommonFunctions :: xdateCampaignStop($checkXdateCampaignExist);
		if($puaseXdateCampaign['status'] == _ID_SUCCESS)
       	{
           	FileLog::writeLog("stop_x_date_capaign", "Stop x date successfully contact id:- " . $contact_opportunities['contact_business_id']);
      	}
	}
	//end
	//Referral pertner case for existing contact //
	if(!isset($objectData['create_new_contact']) && empty($objectData['create_new_contact']))
	{
		if(!empty($objectData['lead_source_id']) && !empty($previousLeadSourceId) && $objectData['lead_source_id'] != $previousLeadSourceId['lead_source_type'])
		{
			$Contacts->updateAll(['lead_source_type' => $objectData['lead_source_id']],['id' => $contact_opportunities->contact_id]);
		}

	}
			if($pipeline_stage_id ==_PIPELINE_STAGE_WON){


				//check for entry in contact policy renewal table
				$contactPolicyRenewalDetail = $ContactPolicyRenewal->getContactPolicyRenewalByContactAndOpportunity($contact_opportunities['contact_id'],$contact_opportunities['id']);
				if(empty($contactPolicyRenewalDetail))
				{

					$data = [];
					$data['agency_id']=$contact_opportunities['agency_id'];
					$data['contact_id']=$contact_opportunities['contact_id'];
					$data['contact_opportunities_id']=$contact_opportunities['id'];
					$data['renewal_date']=$contact_opportunities['effective_date'];
					$data['renewal_amount']=$contact_opportunities['premium_amount'];
					$data['amount_received_date']=date('Y-m-d');
					$data['stage']=_RENEWAL_STAGE_SUCCESS;
					$data['term_length']=$contact_opportunities['term_length'];
					$data['term_length_period']=$contact_opportunities['term_length_period'];
					$data['carrier_id']=$contact_opportunities['carrier_id'];
					$data['premium_amount']=$contact_opportunities['premium_amount'];
					$data['commission_amount']=$contact_opportunities['commission_amount'];
					$data['policy_number']=$contact_opportunities['policy_number'];
					$data['commission_split']=$contact_opportunities['commission_split'];
					$data['commission_split_percentage']=$contact_opportunities['commission_split_percentage'];
					$contact_policy_renewal = $ContactPolicyRenewal->newEntity();
					$contact_policy_renewal = $ContactPolicyRenewal->patchEntity($contact_policy_renewal, $data);
					$contact_policy_renewal = $ContactPolicyRenewal->save($contact_policy_renewal);
					$message = 'in function saveSalesOpportunityDetails, $info = ' . json_encode($data) . ' contact_opportunities_id '. $contact_opportunities['id'] . 'for  contact id ' . $contact_opportunities['contact_id'] . ' and agency is ' . $contact_opportunities['agency_id'];
					FileLog::writeLog("contact_policy_renewal_dup_entries", $message);
				}
					$post_Obligations = [];
					$post_Obligations['contact_id'] =$contact_opportunities['contact_id'];
					$post_Obligations['contact_business_id'] =$contact_opportunities['contact_business_id'];
					$post_Obligations['agency_id'] =$contact_opportunities['agency_id'];
					$post_Obligations['user_id'] = $contact_opportunities['user_id'];


					$post_Obligations['insurance_type_id'] =$contact_opportunities['insurance_type_id'];
					$post_Obligations['carrier_id'] =$contact_opportunities['carrier_id'];
					$post_Obligations['effective_date'] =$contact_opportunities['effective_date'];
					$post_Obligations['policy_number'] =$contact_opportunities['policy_number'];
					$post_Obligations['opportunity_id'] =$contact_opportunities['id'];
					CommonFunctions::addPostObligationTask($post_Obligations);

			}

			//if active campain is chosen then start that campaign otherwise old code
			if(isset($objectData['sales_campaign']) && !empty($objectData['sales_campaign'])){
				$agencyCampaignDetail = $AgencyCampaignMaster->agencyCampaignAndPipelineStageMasterDetail($objectData['sales_campaign']);

				//update the pipeline stage id
				if(!empty($agencyCampaignDetail) && isset($agencyCampaignDetail->agency_campaign_pipeline_stage_master['pipeline_stage_id']) && !empty($agencyCampaignDetail->agency_campaign_pipeline_stage_master['pipeline_stage_id']))
				{

					$ContactOpportunities->updateAll(['pipeline_stage' =>$agencyCampaignDetail->agency_campaign_pipeline_stage_master['pipeline_stage_id']],['id' => $contact_opportunities->id]);

					if(isset($contact_opportunities->contact_business_id) && !empty($contact_opportunities->contact_business_id))
					{
						$campaign_result = CommonFunctions::startCampaign(null, $objectData['sales_campaign'],$agencyCampaignDetail->agency_campaign_pipeline_stage_master['pipeline_stage_id'],null,$contact_opportunities['user_id'],null,null,$contact_opportunities->contact_business_id, $login_user_id);
					}
					else
					{
						if($agencyCampaignDetail->type==_CAMPAIGN_TYPE_PIPELINE && $agencyCampaignDetail->agency_campaign_pipeline_stage_master['pipeline_stage_id'] == _PIPELINE_STAGE_APPOINTMENT_SCHEDULED)
						{
							$appointment_date_con='';
							if(!empty($objectData['appointment_date']) && !empty($objectData['appointment_time']))
							{
								$appointment_date_con = date("Y-m-d H:i:s",strtotime($objectData['appointment_date'].' '.$objectData['appointment_time']));
								$contact_appointment_arr = [];
								$contact_appointment_arr['contact_id'] = $contact_id;
								$contact_appointment_arr['contact_business_id'] = $business_id;
								$contact_appointment_arr['appointment_date'] = $appointment_date_con;
								$contact_appointment = $ContactAppointments->newEntity();
								$contact_appointment = $ContactAppointments->patchEntity($contact_appointment,$contact_appointment_arr);
								$contact_appointment = $ContactAppointments->save($contact_appointment);
							}
							$campaign_result = CommonFunctions::startBeforeAfterTypeCampaign($contact_id, $objectData['sales_campaign'],$agencyCampaignDetail->agency_campaign_pipeline_stage_master['pipeline_stage_id'],null,$contact_opportunities['user_id'],null,null,null,$appointment_date_con, $login_user_id);
						}
						else
						{
							$campaign_result = CommonFunctions::startCampaign($contact_id, $objectData['sales_campaign'],$agencyCampaignDetail->agency_campaign_pipeline_stage_master['pipeline_stage_id'],null,$contact_opportunities['user_id'], null, null, null, $login_user_id);
						}
					}
				}
			}
            NowCertsApi::updateIntoNowcerts($contact_id, $business_id,  $contact_opportunities->id, null);
			$response =  json_encode(array('status' => _ID_SUCCESS, 'opp_id'=> $contact_opportunities->id));
		}else{
			$response =  json_encode(array('status' => _ID_FAILED));
		}
		return $response;
	}


	/**
    *function to get all active business structure type
    **/
    public function getAllActiveBusinessStructure($agencyId)
    {
		$BusinessStructureType = TableRegistry::getTableLocator()->get('BusinessStructureType');
		$businessStructure = $BusinessStructureType->getAllActiveBusinessStructure();
		$structure = array();
		if(isset($businessStructure) && !empty($businessStructure)){
			$i=0;
			foreach($businessStructure as $bstructure){
				$structure[$i]['id'] = $bstructure['id'];
				$structure[$i]['name'] = $bstructure['business_type'];
				$i++;
			}
		}
		$return['Pipeline.getAllActiveBusinessStructure'] = [
			$agencyId => $structure
		];
		return $return;
    }


	public function searchContactByNameEmailPhone($objectData){
        $keyword = $objectData['key'];
        $data =[];
		$i=0;
        $session = Router::getRequest()->getSession();
		$login_agency_id = $session->read('Auth.User.agency_id');
		$login_user_id= $session->read("Auth.User.user_id");
		$login_role_type = 		$session->read('Auth.User.role_type');
		$login_first_name = 		$session->read('Auth.User.first_name');
		$login_last_name = 		$session->read('Auth.User.last_name');
		$login_role_type_flag = 	$session->read('Auth.User.role_type_flag');
		$login_permissions      = $session->read('Auth.User.permissions');
		$login_permissions_arr  =   explode(",",$login_permissions);
        $search_data = [] ;
        $search_data['permissions_arr'] = $login_permissions_arr;
		$Contacts = TableRegistry::getTableLocator()->get('Contacts');
        $contact_type="";
        $contacts_list = $Contacts->searchContactForBusinessLinking($keyword,$login_agency_id,$contact_type,$login_user_id,$login_role_type,$login_role_type_flag,$search_data);
        //echo "<pre>"; print_r($contacts_list); exit;
        if(!empty($contacts_list)){
            foreach($contacts_list as $contact){
				// $data[$i] = '<p @click="getSearchContactId('.$contact->id.')">'.$contact->first_name.' '.$contact->last_name.'</p>';
                $data[$i]['id'] =    $contact->id;
                $data[$i]['name'] =  !empty($contact->last_name) ? trim($contact->first_name)." ".trim($contact->last_name) : trim($contact->first_name);
                $data[$i]['email'] = !empty($contact->email) ? $contact->email : "";
                $data[$i]['contact_no'] = !empty($contact->phone) ? CommonFunctions::format_phone_us($contact->phone) : "";
                $data[$i]['value'] =SITEURL.'contacts?id='.$contact->id;
                $i++;
            }
        }
        $response =  json_encode(array('status' => _ID_SUCCESS,'list'=>  $data));
		return $response;

    }


	/**
     * Verify email if already exist in contact table
     */
    public function verifyemail($objectData) {
		$session = Router::getRequest()->getSession();
		$login_agency_id = $session->read('Auth.User.agency_id');
		$Contacts = TableRegistry::getTableLocator()->get('Contacts');
        if(isset($objectData['email']) && !empty($objectData['email']))
        {
            $email = $objectData['email'];
        }
        if(isset($objectData['primary_contact_email']) && !empty($objectData['primary_contact_email']))
        {
            $email = $objectData['primary_contact_email'];
        }

		$user = $Contacts->checkEmailExist($email,$login_agency_id);
        if (!empty($user)) {
            $response =  json_encode(array('status' => _ID_FAILED));
        } else {
			$response =  json_encode(array('status' => _ID_SUCCESS));
        }
		return $response;
    }

	public static function getInsuraceTyperPersonalCommercial($objectData){

        $session = Router::getRequest()->getSession();
		$login_agency_id = $session->read('Auth.User.agency_id');
		$InsuranceTypes = TableRegistry::getTableLocator()->get('InsuranceTypes');
		$personal_commercial = $objectData['personal_commercial'];
		$insurance_types = [];
		$i= 0;
        if($personal_commercial==_PERSONAL_CONTACT){

			$insurance_types_personal_arr = $InsuranceTypes->insuranceListByAgencyIdPersonal($login_agency_id);

        }else{

			$insurance_types_commercial_arr = $InsuranceTypes->insuranceListByAgencyIdCommercial($login_agency_id);
		}
		if(isset($insurance_types_personal_arr) && !empty($insurance_types_personal_arr))
		{	if(isset($objectData['page']) && $objectData['page'] === 'edit')
			{
				foreach ($insurance_types_personal_arr as $insurance_types_personal) {
					$insurance_types[$i]['insurance_type_id'] = (int)$insurance_types_personal['id'];
					$insurance_types[$i]['type'] = $insurance_types_personal['type'];
					$i++;
				}
			}else{
				foreach ($insurance_types_personal_arr as $insurance_types_personal) {
					$insurance_types[$i]['id'] = (int)$insurance_types_personal['id'];
					$insurance_types[$i]['name'] = $insurance_types_personal['type'];
					$i++;
				}
			}


		}

		if(isset($insurance_types_commercial_arr) && !empty($insurance_types_commercial_arr))
		{
			if(isset($objectData['page']) && $objectData['page'] === 'edit')
			{
				foreach ($insurance_types_commercial_arr as $insurance_types_commercial) {
					$insurance_types[$i]['insurance_type_id'] = (int)$insurance_types_commercial['id'];
					$insurance_types[$i]['type'] = $insurance_types_commercial['type'];
					$i++;
				}
			}else{
				foreach ($insurance_types_commercial_arr as $insurance_types_commercial) {
					$insurance_types[$i]['id'] = (int)$insurance_types_commercial['id'];
					$insurance_types[$i]['name'] = $insurance_types_commercial['type'];
					$i++;
				}
			}

		}
		return $insurance_types;
  }



  public static function getAllAvailableCampaignsArrayForSales($objectData)
    {
		ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '-1');
        $session = Router::getRequest()->getSession();
		$login_user_id= $session->read("Auth.User.user_id");
		$login_agency_id= $session->read("Auth.User.agency_id");
        $lead_type = $objectData['lead_type'];
        $pipeline_stage_id = $objectData['pipeline_stage_id'];
		$AgencyCampaignMaster = TableRegistry::getTableLocator()->get('AgencyCampaignMaster');
		$i=0;
        $list=[];
        $line = _LINE_PERSONAL;
		if(!empty($objectData['line']))
        {
            $line = $objectData['line'];
        }
		$policy_id = $lead_source_id =  '';
		if(!empty($objectData['policy_id']))
        {
            $policy_id = $objectData['policy_id'];
        }
		if(!empty($objectData['lead_source_id']))
        {
            $lead_source_id = $objectData['lead_source_id'];
        }
        if($lead_type == _CONTACT_TYPE_LEAD && $pipeline_stage_id == _PIPELINE_STAGE_NEW_LEAD)
        {
            // Launch General New Lead Campaign
            $general_new_lead_campaign = $AgencyCampaignMaster->getCampaignsByGeneralType($login_agency_id,_CAMPAIGN_TYPE_GENERAL_NEW_LEAD,$line);
            if(isset($general_new_lead_campaign) && !empty($general_new_lead_campaign)){
               // $list .='<option value="'.$general_new_lead_campaign['id'].'">'.$general_new_lead_campaign['name'].'</option>';
			   $list[0]['id'] = $general_new_lead_campaign['id'];
			   $list[0]['name'] = $general_new_lead_campaign['name'];
			   $i = 1;
            }

        }
		if(!empty($policy_id) && !empty($lead_source_id) && $lead_type == _CONTACT_TYPE_LEAD && $pipeline_stage_id == _PIPELINE_STAGE_NEW_LEAD)
		{
            $result = $AgencyCampaignMaster->getCampaignByPolicyIdLeadSourceId($login_agency_id, $policy_id, $lead_source_id, $line);
            if(!empty($result))
			{
				$list[1]['id'] = $result['id'];
				$list[1]['name'] = $result['name'];
				$i = 2;
            }
        } 
		if($lead_type == _CONTACT_TYPE_LEAD && $pipeline_stage_id != _PIPELINE_STAGE_NEW_LEAD)
        {
            if($pipeline_stage_id==_PIPELINE_STAGE_NONE)
            {

                $list[0]['id'] = '';
				$list[0]['name'] = "Don't Start Long Term Nurture/X-Date  Campaign";
                $resultLTN = $AgencyCampaignMaster->getCampaignsByGeneralType($login_agency_id,_CAMPAIGN_TYPE_LONG_TERM_NURTURE,$line);
                if(!empty($resultLTN)){
                    $list[1]['id'] = $resultLTN['id'];
					$list[1]['name'] = $resultLTN['name'];
					$i = 1;
				}
                $resultXdate = $AgencyCampaignMaster->getCampaignsByGeneralType($login_agency_id,_CAMPAIGN_TYPE_X_DATE,$line);
                if(!empty($resultXdate)){
                   // $list .='<option value="'.$resultXdate['id'].'">'.$resultXdate['name'].'</option>';
					$list[$i + 1]['id'] = $resultLTN['id'];
					$list[$i + 1]['name'] = $resultLTN['name'];
                }

            }else{

                $result = $AgencyCampaignMaster->agencyCampaignMasterDetailByPipelineStageId($login_agency_id,$pipeline_stage_id,_CAMPAIGN_TYPE_PIPELINE,$line);
                if(!empty($result)){
						$list[0]['id'] = $result['id'];
						$list[0]['name'] = $result['name'];
                        $i = 1;
                    }
				$list[$i]['id'] = '';
				$list[$i]['name'] = "Don't Start ".$result['name']." Campaign";
            }
        }

        else{
            $getEntityDef=getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_NEW_LEAD);
			$list[$i]['id'] = "";
			$list[$i]['name'] = "Don't Start ".$getEntityDef." Campaign";
        }

        // if(!empty($policy_id_arr) && $lead_type == _CONTACT_TYPE_CLIENT)
        // {
        //     $list='<option value="">Select Campaign</option>';
        //     foreach ($policy_id_arr as $key => $value) {
        //         $result = $this->AgencyCampaignMaster->find()->where(['agency_id' => $login_agency_id,'insurance_type_id' => $value,'source_type_id IS NULL','type'=>_CAMPAIGN_TYPE_RENEWAL,'turn_on_off' => _ID_STATUS_ACTIVE ,'status' => _ID_STATUS_ACTIVE,'personal_commercial_line'=>$line])->hydrate(false)->first();
        //             if(!empty($result)){
        //                 $list .='<option value="'.$result['id'].'" selected>'.$result['name'].'</option>';

        //         }

        //       }
        // }

         //add personal or commercial campaigns to client campaign drop down
        $crossSellCampaigns=$AgencyCampaignMaster->getAllActiveAgencyCampaignsByType($login_agency_id,_CAMPAIGN_TYPE_CROSS_SELL,$line);
        $client_welcome_campaigns = $AgencyCampaignMaster->getAllActiveAgencyCampaignsByType($login_agency_id,_CAMPAIGN_TYPE_CLIENT_WELCOME,$line);
        $service_pipeline_campaigns = $AgencyCampaignMaster->agencyCampaignMasterDetailByPipelineStageId($login_agency_id,_SERVICE_PIPELINE_CAMPAIGN_NEW_SERVICE_REQUEST,_CAMPAIGN_TYPE_SERVICE_PIPELINE,$line);

        $winBackCampaigns = $AgencyCampaignMaster->getAllActiveAgencyCampaignsByType($login_agency_id,_CAMPAIGN_TYPE_WIN_BACK_LOST_CLIENTS,$line);

        $winback_campaign_list=[];
		$j = 0;
        foreach ($winBackCampaigns as $winBackCampaign)
        {
			$winback_campaign_list[$j]['id'] = $winBackCampaign['id'];
			$winback_campaign_list[$j]['name'] = $winBackCampaign['name'];
			$j++;
        }

        $client_campaign_list=[];
		$a = 0;
        foreach ($crossSellCampaigns as $crossSellCampaign)
        {
			$client_campaign_list[$a]['id'] = $crossSellCampaign['id'];
			$client_campaign_list[$a]['name'] = $crossSellCampaign['name'];
			$a++;

		}
        foreach ($client_welcome_campaigns as $client_welcome_campaign)
        {
            //  $client_campaign_list .= "<option value=".$client_welcome_campaign['id'].">".$client_welcome_campaign['name']."</option>";
			 $client_campaign_list[$a]['id'] = $client_welcome_campaign['id'];
			 $client_campaign_list[$a]['name'] = $client_welcome_campaign['name'];
			 $a++;
		}

        if(!empty($service_pipeline_campaigns))
        {
            //$client_campaign_list .='<option value="'.$service_pipeline_campaigns['id'].'">'.$service_pipeline_campaigns['name'].'</option>';
			$client_campaign_list[$a]['id'] = $service_pipeline_campaigns['id'];
			$client_campaign_list[$a]['name'] = $service_pipeline_campaigns['name'];
        }

        $response =  array('status' => _ID_SUCCESS,'list'=>  $list,'client_campaign_list'=>$client_campaign_list,'winback_list'=>$winback_campaign_list);
        $response['list'] = $list;
		$response['client_campaign_list'] = $client_campaign_list;
		$response['winback_list'] = $winback_list;
		//print_r($response); exit;
		return $response;

    }

	public static function getInsuranceTypePersonalCommercialOfCarrier($objectData){

        $session = Router::getRequest()->getSession();
		$login_agency_id = $session->read('Auth.User.agency_id');
		$InsuranceTypes = TableRegistry::getTableLocator()->get('InsuranceTypes');
		$personal_commercial = $objectData['personal_commercial'];
		$carrierId = $objectData['carrierId'];
		$insurance_types = [
			0 => [
				"id" => -1,
				"name" => "No data available"
			]
		];
		$i= 0;

		$insurance_types_arr = $InsuranceTypes->insuranceListByAgencyIdAndCarrierId($carrierId, $login_agency_id, $personal_commercial);

		if(isset($insurance_types_arr) && !empty($insurance_types_arr))
		{
			if(isset($objectData['page']) && $objectData['page'] === 'edit')
			{
				foreach ($insurance_types_arr as $insurance_types_personal) {
					$insurance_types[$i]['insurance_type_id'] = $insurance_types_personal['id'];
					$insurance_types[$i]['type'] = $insurance_types_personal['type'];
					$i++;
				}
			}else{
				foreach ($insurance_types_arr as $insurance_types_personal) {
					$insurance_types[$i]['id'] = $insurance_types_personal['id'];
					$insurance_types[$i]['name'] = $insurance_types_personal['type'];
					$i++;
				}
			}

		}

		return $insurance_types;
  	}

	  public static function updateAttachment($postData)
	  {
		  try
		  {
			  $myfile = fopen(ROOT."/logs/updateAttachment.log", "a") or die("Unable to open file!"); 
			  if(!empty($postData['attachement_id']) && !empty($postData['name'])){
				  $attachement_id=$postData['attachement_id'];
				  $name=addslashes($postData['name']);
				  $salesAttachment = TableRegistry::getTableLocator()->get('SaleAttachments');
				  $salesData = TableRegistry::getTableLocator()->get('SaleAttachments')->find('all')->where(['id' => $postData['attachement_id']])->first();
				  //check file name has extenstion or not, if not then add it
				  if (!preg_match('/(\.jpg|\.png|\.bmp|\.pdf|\.xls|\.jpeg|\.doc|\.docx|\.eml|\.pst|\.ost|\.wav|\.mp3|\.mp4|\.m4a|\.xlsx)$/i', strtolower($name))) {
					  if(isset($salesData['file_url']) && $salesData['file_url'] != '')
					  {
						  $fileInfo = pathinfo($salesData['file_url']);
						  if(isset($fileInfo['extension']) && $fileInfo['extension'] != '')
						  {
							  $name = $name . '.' .strtolower($fileInfo['extension']);
						  }
					  }

				  } 
				  //
				  $updateArray=array();
				  $updateArray['display_name']=$name;
				  $SaleAttachments = $salesAttachment->patchEntity($salesData, $updateArray);
				  $finalResponse =[];           
				  if($salesAttachment->save($SaleAttachments)){               
					  $response1 = [
						  'status'=>_ID_SUCCESS,  
						  'salesAttachment' =>$salesAttachment,                      
					  ];  
				  } else {
					  $response1 = [
						  'status'=>_ID_FAILED,
						  'salesAttachment' =>$salesAttachment,         
					  ]; 
				  }
				  return $response1;
			  }
		  }
		  catch (\Exception $e) {
		  $txt=date('Y-m-d H:i:s').' :: Attachment update Error- '.$e->getMessage();
		  fwrite($myfile,$txt.PHP_EOL);
		  }
	  }

//    public static function pipelineStagesReload($agencyId){
//
//		$return = [];
//		$pipeline = [];
//		try
//		{
//		  $myfile = fopen(ROOT."/logs/vuePipeline.log", "a") or die("Unable to open file!");
//		  $session = Router::getRequest()->getSession();
//		  $login_agency_id = (int)$agencyId;
//		  $login_user_id = $session->read("Auth.User.user_id");
//		  $login_role_type = $session->read('Auth.User.role_type');
//		  $login_role_type_flag = $session->read('Auth.User.role_type_flag');
//		  $login_permissions = $session->read('Auth.User.permissions');
//		  $login_permissions_arr = explode(",",$login_permissions);
//		  $searchData = [];
//		  $searchData['permissions_arr']=$login_permissions_arr;
//		  $show_data = 'default';
//		  $ContactOpportunitiesTable = TableRegistry::getTableLocator()->get('ContactOpportunities');
//		  $ContactsTable = TableRegistry::getTableLocator()->get('Contacts');
//		  $Tasks = TableRegistry::getTableLocator()->get('Tasks');
//          $Agency = TableRegistry::getTableLocator()->get('Agency');
//          $UsStates = TableRegistry::getTableLocator()->get('UsStates');
//
//		  //get all leads stage wise
//
//		  //maintain search owner filter if available
//			if(isset($searchArr) && !empty($searchArr))
//			{
//				$searchData = $searchArr;
//			}
//		  	else if(isset($searchData['permissions_arr']) && (in_array(71, $searchData['permissions_arr']) && $login_role_type_flag!=_AGENCY_ROLE_ADMIN ))
//			{
//			  $searchData['user_id']=$login_user_id;
//			}
//
//		   //new lead
//		   $contacts_new_lead = $ContactOpportunitiesTable->opportunitiesByPipelineStageVue(_PIPELINE_STAGE_NEW_LEAD,$login_agency_id,$login_user_id,$login_role_type,$login_role_type_flag,$searchData,'',_PAGE_TYPE_DEFAULT,$show_data);
//		  $contactNewleadInsuranceTypes = '';
//		   $contactsNewLeadArray =[];
//			if(isset($contacts_new_lead) && !empty($contacts_new_lead))
//			{
//				$i = 0;
//				foreach($contacts_new_lead['data'] as $key => $data)
//				{
//
//					if(isset($data['contact_busines']) && isset($data['contact_busines']['id']) && !empty($data['contact_busines']) && $data['contact_busines'] != '' && $data['contact_busines'] != null)
//					{
//						$contactNewleadInsuranceTypes = $ContactOpportunitiesTable->getActiveOpportunitiesByStageContactIdVue(null,_PIPELINE_STAGE_NEW_LEAD,$login_agency_id,null,$data['contact_busines']['id']);
//					}else{
//						$contactNewleadInsuranceTypes = $ContactOpportunitiesTable->getActiveOpportunitiesByStageContactIdVue($data['contact']['id'],_PIPELINE_STAGE_NEW_LEAD,$login_agency_id,null,null);
//					}
//
//					$insurance_type = array();
//					 if(isset($contactNewleadInsuranceTypes) && !empty($contactNewleadInsuranceTypes))
//					{
//						$insuranceTypes = array();
//						$opportunity_ids = array();
//                        $salesTitle = array();
//						foreach($contactNewleadInsuranceTypes as $key1 => $type)
//						{
//							$insurance_type = $type['insurance_type']['type'];
//							array_push($insuranceTypes,$type['insurance_type']['type']);
//							array_push($opportunity_ids,$type['id']);
//                            if(isset($type['sales_title']) && $type['sales_title'] != '')
//                            {
//                                array_push($salesTitle,$type['sales_title']);
//                            }
//						}
//
//						$contactsNewLeadArray[$i]['countContactInsuranceTypes'] = count($insuranceTypes);
//						$insuranceTypes =	count($insuranceTypes) >1 ? implode(", ",$insuranceTypes) : $insuranceTypes[0];
//						$contactsNewLeadArray[$i]['type'] = $insuranceTypes;
//						$contactsNewLeadArray[$i]['opportunity_ids'] = $opportunity_ids;
//                        $contactsNewLeadArray[$i]['sales_title'] = $salesTitle;
//
//					}
//
//                    $TaskOfOpp = array();
//                    $Task = $Tasks->getTaskByOppId($contactNewleadInsuranceTypes[0]['id']);
//                    if(isset($Task) && !empty($Task))
//                    {
//                        $TaskOfOpp = $Task[0];
//                    }
//					$contactsNewLeadArray[$i]['data'] = $data;
//					$opportunity_id = $contactNewleadInsuranceTypes[0]['id'];
//					$contactsNewLeadArray[$i]['data']['opportunity_id'] = $opportunity_id;
//                    $contactsNewLeadArray[$i]['data']['Tasks'] = $TaskOfOpp;
//					$contactsNewLeadArray[$i]['pipeline_stage'] = _PIPELINE_STAGE_NEW_LEAD;
//
//				   $i++;
//				}
//			}
//
//			$pipeline['contacts_new_lead'] = $contactsNewLeadArray;
//			$pipeline['contacts_new_lead_count'] = $contacts_new_lead['count'];
//
//			//projected value
//			$pipeline['projected_value_new_lead'] = $ContactOpportunitiesTable->getProjectedValueByPipelineStageNewVue(_PIPELINE_STAGE_NEW_LEAD,$login_agency_id,$login_user_id,$login_role_type,$login_role_type_flag,$searchData);
//			//new lead end here
//
//
//			//appointment
//			$contacts_appointment_scheduled = $ContactOpportunitiesTable->opportunitiesByPipelineStageVue(_PIPELINE_STAGE_APPOINTMENT_SCHEDULED,$login_agency_id,$login_user_id,$login_role_type,$login_role_type_flag,$searchData,'',_PAGE_TYPE_DEFAULT,$show_data);
//
//
//			$contactAppointmentInsuranceTypes = '';
//			$contactsAppointmentArray =[];
//			if(isset($contacts_appointment_scheduled) && !empty($contacts_appointment_scheduled))
//			{
//				$i = 0;
//				foreach($contacts_appointment_scheduled['data'] as $key => $data)
//				{
//					if(isset($data['contact_busines']) && isset($data['contact_busines']['id']) && !empty($data['contact_busines']) && $data['contact_busines'] != '' && $data['contact_busines'] != null)
//					{
//						$contactAppointmentInsuranceTypes = $ContactOpportunitiesTable->getActiveOpportunitiesByStageContactIdVue(null,_PIPELINE_STAGE_APPOINTMENT_SCHEDULED,$login_agency_id,null,$data['contact_busines']['id']);
//					}else{
//						$contactAppointmentInsuranceTypes = $ContactOpportunitiesTable->getActiveOpportunitiesByStageContactIdVue($data['contact']['id'],_PIPELINE_STAGE_APPOINTMENT_SCHEDULED,$login_agency_id,null,null);
//					}
//
//					 if(isset($contactAppointmentInsuranceTypes) && !empty($contactAppointmentInsuranceTypes))
//					{
//						$insuranceTypes = array();
//						$$opportunity_ids = array();
//                        $salesTitle = array();
//						foreach($contactAppointmentInsuranceTypes as $key1 => $type)
//						{
//								$insurance_type = $type['insurance_type']['type'];
//								array_push($insuranceTypes,$type['insurance_type']['type']);
//								array_push($opportunity_ids,$type['id']);
//                                if(isset($type['sales_title']) && $type['sales_title'] != '') {
//                                    array_push($salesTitle, $type['sales_title']);
//                                }
//
//						}
//						$contactsAppointmentArray[$i]['countContactInsuranceTypes'] = count($insuranceTypes);
//						$insuranceTypes =	count($insuranceTypes) >1 ? implode(", ",$insuranceTypes) : $insuranceTypes[0];
//						$contactsAppointmentArray[$i]['type'] = $insuranceTypes;
//						$contactsAppointmentArray[$i]['opportunity_ids'] = $opportunity_ids;
//                        $contactsAppointmentArray[$i]['sales_title'] =  $salesTitle;
//					}
//                    $TaskOfOpp = array();
//                    $Task = $Tasks->getTaskByOppId($contactAppointmentInsuranceTypes[0]['id']);
//                    if(isset($Task) && !empty($Task))
//                    {
//                        $TaskOfOpp = $Task[0];
//                    }
//					$contactsAppointmentArray[$i]['data'] = $data;
//					$opportunity_id = $contactAppointmentInsuranceTypes[0]['id'];
//					$contactsAppointmentArray[$i]['data']['opportunity_id'] = $opportunity_id;
//                    $contactsAppointmentArray[$i]['data']['Tasks'] = $TaskOfOpp;
//					$contactsAppointmentArray[$i]['pipeline_stage'] = _PIPELINE_STAGE_APPOINTMENT_SCHEDULED;
//					$i++;
//				}
//			}
//
//			$pipeline['contacts_appointment_scheduled'] = $contactsAppointmentArray;
//			$pipeline['contacts_appointment_scheduled_count'] = $contacts_appointment_scheduled['count'];
//			//projected value
//			$pipeline['projected_value_appointment_scheduled'] = $ContactOpportunitiesTable->getProjectedValueByPipelineStageNewVue(_PIPELINE_STAGE_APPOINTMENT_SCHEDULED,$login_agency_id,$login_user_id,$login_role_type,$login_role_type_flag,$searchData);
//			// end here
//
//
//		//working
//			$contacts_working = $ContactOpportunitiesTable->opportunitiesByPipelineStageVue(_PIPELINE_STAGE_WORKING,$login_agency_id,$login_user_id,$login_role_type,$login_role_type_flag,$searchData,'',_PAGE_TYPE_DEFAULT,$show_data);
//
//			$contactWorkingInsuranceTypes = '';
//			$contactsWorkingArray =[];
//			if(isset($contacts_working) && !empty($contacts_working))
//			{
//				$i = 0;
//				foreach($contacts_working['data'] as $key => $data)
//				{
//					if(isset($data['contact_busines']) && isset($data['contact_busines']['id']) && !empty($data['contact_busines']) && $data['contact_busines'] != '' && $data['contact_busines'] != null)
//					{
//						$contactWorkingInsuranceTypes = $ContactOpportunitiesTable->getActiveOpportunitiesByStageContactIdVue(null,_PIPELINE_STAGE_WORKING,$login_agency_id,null,$data['contact_busines']['id']);
//					}else{
//
//						 $contactWorkingInsuranceTypes = $ContactOpportunitiesTable->getActiveOpportunitiesByStageContactIdVue($data['contact']['id'],_PIPELINE_STAGE_WORKING,$login_agency_id,null,null);
//
//					}
//
//					if(isset($contactWorkingInsuranceTypes) && !empty($contactWorkingInsuranceTypes))
//					{
//						$insuranceTypes = array();
//						$opportunity_ids = array();
//						$salesTitle = array();
//						foreach($contactWorkingInsuranceTypes as $key1 => $type)
//						{
//								$insurance_type = $type['insurance_type']['type'];
//								array_push($insuranceTypes,$type['insurance_type']['type']);
//								array_push($opportunity_ids,$type['id']);
//								if(isset($type['sales_title']) && $type['sales_title'] != '') {
//                                    array_push($salesTitle, $type['sales_title']);
//                                }
//						}
//						$contactsWorkingArray[$i]['countContactInsuranceTypes'] = count($insuranceTypes);
//						$insuranceTypes =	count($insuranceTypes) >1 ? implode(", ",$insuranceTypes) : $insuranceTypes[0];
//						$contactsWorkingArray[$i]['type'] = $insuranceTypes;
//						$contactsWorkingArray[$i]['opportunity_ids'] = $opportunity_ids;
//						$contactsWorkingArray[$i]['sales_title'] =  $salesTitle;
//					}
//                    $TaskOfOpp = array();
//                    $Task = $Tasks->getTaskByOppId($contactWorkingInsuranceTypes[0]['id']);
//                    if(isset($Task) && !empty($Task))
//                    {
//                        $TaskOfOpp = $Task[0];
//                    }
//                    $contactsWorkingArray[$i]['data'] = $data;
//                    $opportunity_id = $contactWorkingInsuranceTypes[0]['id'];
//                    $contactsWorkingArray[$i]['data']['opportunity_id'] = $opportunity_id;
//                    $contactsWorkingArray[$i]['data']['Tasks'] = $TaskOfOpp;
//                    $contactsWorkingArray[$i]['pipeline_stage'] = _PIPELINE_STAGE_WORKING;
//					$i++;
//
//				}
//			}
//
//			$pipeline['contacts_working'] = $contactsWorkingArray;
//			$pipeline['contacts_working_count'] = $contacts_working['count'];
//			//projected value
//			$pipeline['projected_value_working'] = $ContactOpportunitiesTable->getProjectedValueByPipelineStageNewVue(_PIPELINE_STAGE_WORKING,$login_agency_id,$login_user_id,$login_role_type,$login_role_type_flag,$searchData);
//
//			// end here
//
//
//
//			//quoting
//			$contacts_quoting = $ContactOpportunitiesTable->opportunitiesByPipelineStageVue(_PIPELINE_STAGE_QUOTE_READY,$login_agency_id,$login_user_id,$login_role_type,$login_role_type_flag,$searchData,'',_PAGE_TYPE_DEFAULT,$show_data);
//
//			$contactQuotingInsuranceTypes = '';
//			$contactsQuotingArray =[];
//			if(isset($contacts_quoting) && !empty($contacts_quoting))
//			{
//				$i = 0;
//				foreach($contacts_quoting['data'] as $key => $data)
//				{
//					if(isset($data['contact_busines']) && isset($data['contact_busines']['id']) && !empty($data['contact_busines']) && $data['contact_busines'] != '' && $data['contact_busines'] != null)
//					{
//						$contactQuotingInsuranceTypes = $ContactOpportunitiesTable->getActiveOpportunitiesByStageContactIdVue(null,_PIPELINE_STAGE_QUOTE_READY,$login_agency_id,null,$data['contact_busines']['id']);
//					}else{
//						$contactQuotingInsuranceTypes = $ContactOpportunitiesTable->getActiveOpportunitiesByStageContactIdVue($data['contact']['id'],_PIPELINE_STAGE_QUOTE_READY,$login_agency_id,null,null);
//					}
//
//					if(isset($contactQuotingInsuranceTypes) && !empty($contactQuotingInsuranceTypes))
//					{
//						$insuranceTypes = array();
//						$opportunity_ids = array();
//						$salesTitle =  array();
//						foreach($contactQuotingInsuranceTypes as $key1 => $type)
//						{
//							$insurance_type = $type['insurance_type']['type'];
//							array_push($insuranceTypes,$type['insurance_type']['type']);
//							array_push($opportunity_ids,$type['id']);
//							if(isset($type['sales_title']) && $type['sales_title'] != '') {
//								array_push($salesTitle, $type['sales_title']);
//							}
//						}
//
//						$contactsQuotingArray[$i]['countContactInsuranceTypes'] = count($insuranceTypes);
//						$insuranceTypes =	count($insuranceTypes) >1 ? implode(", ",$insuranceTypes) : $insuranceTypes[0];
//						$contactsQuotingArray[$i]['type'] = $insuranceTypes;
//						$contactsQuotingArray[$i]['opportunity_ids'] = $opportunity_ids;
//						$contactsQuotingArray[$i]['sales_title'] =  $salesTitle;
//					}
//                    $TaskOfOpp = array();
//                    $Task = $Tasks->getTaskByOppId($contactQuotingInsuranceTypes[0]['id']);
//                    if(isset($Task) && !empty($Task))
//                    {
//                        $TaskOfOpp = $Task[0];
//                    }
//                    $contactsQuotingArray[$i]['data'] = $data;
//                    $opportunity_id = $contactQuotingInsuranceTypes[0]['id'];
//                    $contactsQuotingArray[$i]['data']['opportunity_id'] = $opportunity_id;
//                    $contactsQuotingArray[$i]['data']['Tasks'] = $TaskOfOpp;
//                    $contactsQuotingArray[$i]['pipeline_stage'] = _PIPELINE_STAGE_QUOTE_READY;
//					$i++;
//				}
//			}
//
//			$pipeline['contacts_quoting'] = $contactsQuotingArray;
//			$pipeline['contacts_quoting_count'] = $contacts_quoting['count'];
//			$pipeline['projected_value_quoting'] = $ContactOpportunitiesTable->getProjectedValueByPipelineStageNewVue(_PIPELINE_STAGE_QUOTE_READY,$login_agency_id,$login_user_id,$login_role_type,$login_role_type_flag,$searchData);
//			// end here
//
//			//quote sent
//			$contacts_quote_sent = $ContactOpportunitiesTable->opportunitiesByPipelineStageVue(_PIPELINE_STAGE_QUOTE_SENT,$login_agency_id,$login_user_id,$login_role_type,$login_role_type_flag,$searchData,'',_PAGE_TYPE_DEFAULT,$show_data);
//
//			$contactQuoteSentInsuranceTypes = '';
//			$contactsQuoteSentArray =[];
//			if(isset($contacts_quote_sent) && !empty($contacts_quote_sent))
//			{
//				$i = 0;
//				foreach($contacts_quote_sent['data'] as $key => $data)
//				{
//					if(isset($data['contact_busines']) && isset($data['contact_busines']['id']) && !empty($data['contact_busines']) && $data['contact_busines'] != '' && $data['contact_busines'] != null)
//					{
//						$contactQuoteSentInsuranceTypes = $ContactOpportunitiesTable->getActiveOpportunitiesByStageContactIdVue(null,_PIPELINE_STAGE_QUOTE_SENT,$login_agency_id,null,$data['contact_busines']['id']);
//					}else{
//						$contactQuoteSentInsuranceTypes = $ContactOpportunitiesTable->getActiveOpportunitiesByStageContactIdVue($data['contact']['id'],_PIPELINE_STAGE_QUOTE_SENT,$login_agency_id,null,null);
//					}
//
//					if(isset($contactQuoteSentInsuranceTypes) && !empty($contactQuoteSentInsuranceTypes))
//					{
//						$insuranceTypes = array();
//						$opportunity_ids = array();
//                        $salesTitle = array();
//						foreach($contactQuoteSentInsuranceTypes as $key1 => $type)
//						{
//							$insurance_type = $type['insurance_type']['type'];
//							array_push($insuranceTypes,$type['insurance_type']['type']);
//							array_push($opportunity_ids,$type['id']);
//							if(isset($type['sales_title']) && $type['sales_title'] != '') {
//								array_push($salesTitle, $type['sales_title']);
//							}
//
//						}
//						$contactsQuoteSentArray[$i]['countContactInsuranceTypes'] = count($insuranceTypes);
//						$insuranceTypes =	count($insuranceTypes) >1 ? implode(", ",$insuranceTypes) : $insuranceTypes[0];
//						$contactsQuoteSentArray[$i]['type'] = $insuranceTypes;
//						$contactsQuoteSentArray[$i]['opportunity_ids'] = $opportunity_ids;
//						$contactsQuoteSentArray[$i]['sales_title'] =  $salesTitle;
//					}
//                    $TaskOfOpp = array();
//                    $Task = $Tasks->getTaskByOppId($contactQuoteSentInsuranceTypes[0]['id']);
//                    if(isset($Task) && !empty($Task))
//                    {
//                        $TaskOfOpp = $Task[0];
//                    }
//                    $contactsQuoteSentArray[$i]['data'] = $data;
//                    $opportunity_id = $contactQuoteSentInsuranceTypes[0]['id'];
//                    $contactsQuoteSentArray[$i]['data']['opportunity_id'] = $opportunity_id;
//                    $contactsQuoteSentArray[$i]['data']['Tasks'] = $TaskOfOpp;
//                    $contactsQuoteSentArray[$i]['pipeline_stage'] = _PIPELINE_STAGE_QUOTE_SENT;
//					$i++;
//				}
//			}
//
//			$pipeline['contacts_quote_sent'] = $contactsQuoteSentArray;
//			$pipeline['contacts_quote_sent_count'] = $contacts_quote_sent['count'];
//
//			//projected value
//			$pipeline['projected_value_quote_sent'] = $ContactOpportunitiesTable->getProjectedValueByPipelineStageNewVue(_PIPELINE_STAGE_QUOTE_SENT,$login_agency_id,$login_user_id,$login_role_type,$login_role_type_flag,$searchData);
//			// end here
//
//
//			//lost
//			$contacts_lost = $ContactOpportunitiesTable->opportunitiesByPipelineStageVue(_PIPELINE_STAGE_LOST,$login_agency_id,$login_user_id,$login_role_type,$login_role_type_flag,$searchData,'',_PAGE_TYPE_DEFAULT,$show_data);
//
//			$contactLostInsuranceTypes = '';
//			$contactsLostArray =[];
//			if(isset($contacts_lost) && !empty($contacts_lost))
//			{
//				$i = 0;
//				foreach($contacts_lost['data'] as $key => $data)
//				{
//					if($data['contact_busines'] != '' && $data['contact_busines'] != null)
//					{
//						$contactLostInsuranceTypes = $ContactOpportunitiesTable->getlostOpportunitiesByStageContactIdVue(null,_PIPELINE_STAGE_LOST,$login_agency_id,null,$data['contact_busines']['id']);
//					}else{
//						$contactLostInsuranceTypes = $ContactOpportunitiesTable->getlostOpportunitiesByStageContactIdVue($data['contact']['id'],_PIPELINE_STAGE_LOST,$login_agency_id,null,null);
//					}
//					if(isset($contactLostInsuranceTypes) && !empty($contactLostInsuranceTypes))
//					{
//						$insuranceTypes = array();
//						$opportunity_ids = array();
//                        $salesTitle = array();
//						foreach($contactLostInsuranceTypes as $key1 => $type)
//						{
//							$insurance_type = $type['insurance_type']['type'];
//							array_push($insuranceTypes,$type['insurance_type']['type']);
//							array_push($opportunity_ids,$type['id']);
//							if(isset($type['sales_title']) && $type['sales_title'] != '') {
//								array_push($salesTitle, $type['sales_title']);
//							}
//						}
//						$contactsLostArray[$i]['countContactInsuranceTypes'] = count($insuranceTypes);
//						$insuranceTypes =	count($insuranceTypes) >1 ? implode(", ",$insuranceTypes) : $insuranceTypes[0];
//						$contactsLostArray[$i]['type'] = $insuranceTypes;
//						$contactsLostArray[$i]['opportunity_ids'] = $opportunity_ids;
//						$contactsLostArray[$i]['sales_title'] =  $salesTitle;
//					}
//                    $TaskOfOpp = array();
//                    $Task = $Tasks->getTaskByOppId($contactLostInsuranceTypes[0]['id']);
//                    if(isset($Task) && !empty($Task))
//                    {
//                        $TaskOfOpp = $Task[0];
//                    }
//                    $contactsLostArray[$i]['data'] = $data;
//                    $opportunity_id = $contactLostInsuranceTypes[0]['id'];
//                    $contactsLostArray[$i]['data']['opportunity_id'] = $opportunity_id;
//                    $contactsLostArray[$i]['data']['Tasks'] = $TaskOfOpp;
//                    $contactsLostArray[$i]['pipeline_stage'] = _PIPELINE_STAGE_LOST;
//					$i++;
//
//				}
//			}
//			$pipeline['contacts_lost'] = $contactsLostArray;
//			$pipeline['contacts_lost_count'] = $contacts_lost['count'];
//			// end here
//
//			//lost
//			$contacts_won = $ContactOpportunitiesTable->opportunitiesByPipelineStageVue(_PIPELINE_STAGE_WON,$login_agency_id,$login_user_id,$login_role_type,$login_role_type_flag,$searchData,'',_PAGE_TYPE_DEFAULT,$show_data);
//
//			$contactWonInsuranceTypes = '';
//			$contactsWonArray =[];
//			if(isset($contacts_won) && !empty($contacts_won))
//			{
//				$i = 0;
//				foreach($contacts_won['data'] as $key => $data)
//				{
//					if($data['contact_busines'] != '' && $data['contact_busines'] != null)
//					{
//						$contactWonInsuranceTypes = $ContactOpportunitiesTable->getActiveOpportunitiesByStageContactIdVue(null,_PIPELINE_STAGE_WON,$login_agency_id,null,$data['contact_busines']['id']);
//					}else{
//						$contactWonInsuranceTypes = $ContactOpportunitiesTable->getActiveOpportunitiesByStageContactIdVue($data['contact']['id'],_PIPELINE_STAGE_WON,$login_agency_id,null,null);
//					}
//					if(isset($contactWonInsuranceTypes) && !empty($contactWonInsuranceTypes))
//					{
//						$insuranceTypes = array();
//						$opportunity_ids = array();
//                        $salesTitle = array();
//						foreach($contactWonInsuranceTypes as $key1 => $type)
//						{
//							$insurance_type = $type['insurance_type']['type'];
//							array_push($insuranceTypes,$type['insurance_type']['type']);
//							array_push($opportunity_ids,$type['id']);
//							if(isset($type['sales_title']) && $type['sales_title'] != '') {
//								array_push($salesTitle, $type['sales_title']);
//							}
//						}
//						$contactsWonArray[$i]['countContactInsuranceTypes'] = count($insuranceTypes);
//						$insuranceTypes =	count($insuranceTypes) >1 ? implode(", ",$insuranceTypes) : $insuranceTypes[0];
//						$contactsWonArray[$i]['type'] = $insuranceTypes;
//						$contactsWonArray[$i]['opportunity_ids'] = $opportunity_ids;
//						$contactsWonArray[$i]['sales_title'] =  $salesTitle;
//
//					}
//                    $TaskOfOpp = array();
//                    $Task = $Tasks->getTaskByOppId($contactWonInsuranceTypes[0]['id']);
//                    if(isset($Task) && !empty($Task))
//                    {
//                        $TaskOfOpp = $Task[0];
//                    }
//                    $contactsWonArray[$i]['data'] = $data;
//                    $opportunity_id = $contactWonInsuranceTypes[0]['id'];
//                    $contactsWonArray[$i]['data']['opportunity_id'] = $opportunity_id;
//                    $contactsWonArray[$i]['data']['Tasks'] = $TaskOfOpp;
//                    $contactsWonArray[$i]['pipeline_stage'] = _PIPELINE_STAGE_WON;
//					$i++;
//				}
//			}
//
//            //agency details
//            $agencyDetails = $Agency->agencyDetails($login_agency_id);
//            $usersTimezone = 'America/Phoenix';
//            if(!empty($agencyDetails['us_state_id']))
//            {
//              $stateDetail = $UsStates->stateDetail($agencyDetails['us_state_id']);
//            }
//            if(isset($agencyDetails['time_zone']) && !empty($agencyDetails['time_zone']))
//            {
//              $usersTimezone =  $agencyDetails['time_zone'];
//            }
//            else if(isset($stateDetail) && !empty($stateDetail))
//            {
//              $usersTimezone =  $stateDetail->time_zone;
//            }
//
//            $currentDate = CommonFunctions::convertUtcToEmployeeTimeZone($usersTimezone,date("Y-m-d"));
//
//            $pipeline['currentDate'] = $currentDate;
//
//			$pipeline['contacts_won'] = $contactsWonArray;
//			$pipeline['contacts_won_count'] = $contacts_won['count'];
////			$return['Pipeline.pipelineStages'] = [
////				$agencyId => $pipeline
////			];
//            return json_encode(['status' => _ID_SUCCESS, 'data' => $pipeline]);
//		 }catch (\Exception $e) {
//
//            $txt=date('Y-m-d H:i:s').' :: Campaign Error- '.$e->getMessage();
//			fwrite($myfile,$txt.PHP_EOL);
//        }
//
//    }

    public function getUserActivePipelineStage($objectData)
    {
        $session = Router::getRequest()->getSession();
        $loginAgencyId = $session->read("Auth.User.agency_id");
        $loginUserId = $session->read("Auth.User.user_id");
        $PipelineStage = TableRegistry::getTableLocator()->get('PipelineStages');
        $pipeLineStagesdata = $PipelineStage->getActivePipelineStagesList($loginAgencyId, $loginUserId);
        $return['Pipeline.getUserActivePipelineStage'] = [
			$loginAgencyId => $pipeLineStagesdata
		  ];
        return $return;
    }
    public function getUserActiveInactivePipelineStage($objectData)
    {
		$session = Router::getRequest()->getSession();
        $loginAgencyId = $session->read("Auth.User.agency_id");
        $loginUserId = $session->read("Auth.User.user_id");
        $loginRoleType = $session->read('Auth.User.role_type');
		$loginRoleTypeFlag = 	$session->read('Auth.User.role_type_flag');
        $loginPermissions = $session->read('Auth.User.permissions');
        $loginPermissionsArr = explode(",",$loginPermissions);
        $pipeline = [];
        $searchData = '';
        $ContactOpportunitiesTable = TableRegistry::getTableLocator()->get('ContactOpportunities');
        $PipelineStage = TableRegistry::getTableLocator()->get('PipelineStages');
        $pipeLineStagesdata = $PipelineStage->getPipelineStagesList($loginAgencyId, $loginUserId);
        $pipeline['role_type'] = $loginRoleType;
        $pipeline['role_type_flag'] = $loginRoleTypeFlag;
        foreach ($pipeLineStagesdata as $i => $stageData)
        {
            $pipeLineStagesdata[$i]['checkbox_value'] = $stageData['status'] == 1 ? true : false;
            $pipeLineStageData = $ContactOpportunitiesTable->opportunitiesCountByPipelineStageVue($stageData['stage_number'], $loginAgencyId, $loginUserId, $loginRoleType, $loginRoleTypeFlag, $searchData, '', _PAGE_TYPE_DEFAULT);
            $pipeline['cards_in_stage'][$stageData['stage_number']] = $pipeLineStageData['count'];
        }
        $pipeline['stage_list'] = $pipeLineStagesdata;
        $return['Pipeline.getUserActiveInactivePipelineStage'] = [
			$loginAgencyId => $pipeline
        ];
        return $return;
    }
    public static function pipelineStagesdata($agencyId, $fields = null,$searchArr = null)
    {
        $return = [];
		$pipeline = [];
        try
		{
            $myfile = fopen(ROOT."/logs/vuePipeline.log", "a") or die("Unable to open file!");
            $session = Router::getRequest()->getSession();
            $loginAgencyId = (int)$agencyId;
            $loginUserId = $session->read("Auth.User.user_id");
            $loginRoleType = $session->read('Auth.User.role_type');
            $loginRoleTypeFlag = $session->read('Auth.User.role_type_flag');
            $loginPermissions = $session->read('Auth.User.permissions');
            $loginPermissionsArr = explode(",", $loginPermissions);
            $searchData = [];
            $searchData['permissions_arr'] = $loginPermissionsArr;
            $showData = 'default';
            $ContactOpportunitiesTable = TableRegistry::getTableLocator()->get('ContactOpportunities');
            $ContactsTable = TableRegistry::getTableLocator()->get('Contacts');
            $Tasks = TableRegistry::getTableLocator()->get('Tasks');
            $Agency = TableRegistry::getTableLocator()->get('Agency');
            $UsStates = TableRegistry::getTableLocator()->get('UsStates');
            if(isset($searchArr) && !empty($searchArr))
			{
				$searchData = $searchArr;
			}
            else if(isset($searchData['permissions_arr']) && (in_array(71, $searchData['permissions_arr']) && $loginRoleTypeFlag != _AGENCY_ROLE_ADMIN ))
			{
			  $searchData['user_id'] = $loginUserId;
			}
            $PipelineStage = TableRegistry::getTableLocator()->get('PipelineStages');
            if(isset($searchArr['all_stage_count']) && !empty($searchArr['all_stage_count']))
            {
                $pipeLineStagesdata = $PipelineStage->getPipelineStagesList($loginAgencyId, $loginUserId);
            }
            else
            {
                $pipeLineStagesdata = $PipelineStage->getActivePipelineStagesList($loginAgencyId, $loginUserId);
            }
            $stage = 0;
            foreach ($pipeLineStagesdata as $stageData)
            {
                $pipeLineStageData = $ContactOpportunitiesTable->opportunitiesByPipelineStageVue($stageData['stage_number'], $loginAgencyId, $loginUserId, $loginRoleType, $loginRoleTypeFlag, $searchData, '', _PAGE_TYPE_DEFAULT, $showData);
                $contactsCardArray = [];
                if(isset($pipeLineStageData) && !empty($pipeLineStageData))
                {
                    $i = 0;
                    $pipeline['stage_number'][$stage] = $stageData['stage_number'];
                    $premiumAmout = 0;
                    $premium = 0;
                    foreach($pipeLineStageData['data'] as $key => $data)
                    {
                        if($stageData['stage_number'] == _PIPELINE_STAGE_LOST)
                        {
                            if($data['contact_busines'] != '' && $data['contact_busines'] != null)
                            {
                                $contactInsuranceTypes = $ContactOpportunitiesTable->getlostOpportunitiesByStageContactIdVue(null, _PIPELINE_STAGE_LOST, $loginAgencyId, null, $data['contact_busines']['id']);
                            }
                            else
                            {
                                $contactInsuranceTypes = $ContactOpportunitiesTable->getlostOpportunitiesByStageContactIdVue($data['contact']['id'], _PIPELINE_STAGE_LOST, $loginAgencyId, null, null);
                            }
                        }
                        else
                        {
                            if(isset($data['contact_busines']) && isset($data['contact_busines']['id']) && !empty($data['contact_busines']) && $data['contact_busines'] != '' && $data['contact_busines'] != null)
                            {
                                $contactInsuranceTypes = $ContactOpportunitiesTable->getActiveOpportunitiesByStageContactIdVue(null,  $stageData['stage_number'], $loginAgencyId, null, $data['contact_busines']['id']);
                            }
                            else
                            {
                                $contactInsuranceTypes = $ContactOpportunitiesTable->getActiveOpportunitiesByStageContactIdVue($data['contact']['id'],  $stageData['stage_number'], $loginAgencyId, null, null);
                            }
                        }
                        $insuranceType = array();
                        $TaskOfOpp = array();
                        if(isset($contactInsuranceTypes) && !empty($contactInsuranceTypes))
                        {
                            $insuranceTypes = array();
                            $opportunityIds = array();
                            $salesTitle = array();
                            foreach($contactInsuranceTypes as $key1 => $type)
                            {
                                $insuranceType = $type['insurance_type']['type'];
                                array_push($insuranceTypes, $type['insurance_type']['type']);
                                array_push($opportunityIds, $type['id']);
                                if(isset($type['sales_title']) && $type['sales_title'] != '')
                                {
                                    array_push($salesTitle, $type['sales_title']);
                                }
                                if(isset($type['tasks']) && $type['tasks'] != '')
                                {
                                    $nearestTask = Pipeline::findNearestDueDateTask($type['tasks'], $loginAgencyId);
                                    $TaskOfOpp = $nearestTask;
                                }
                            }
                            $contactsCardArray[$i]['countContactInsuranceTypes'] = count($insuranceTypes);
                            $insuranceTypes =	count($insuranceTypes) >1 ? implode(", ", $insuranceTypes) : $insuranceTypes[0];
                            $contactsCardArray[$i]['type'] = $insuranceTypes;
                            $contactsCardArray[$i]['opportunity_ids'] = $opportunityIds;
                            $contactsCardArray[$i]['sales_title'] = $salesTitle;
                        }
                        $contactsCardArray[$i]['data'] = $data;
                        $opportunityId = $contactInsuranceTypes[0]['id'];
                        $contactsCardArray[$i]['data']['opportunity_id'] = $opportunityId;
                        $contactsCardArray[$i]['data']['Tasks'] = $TaskOfOpp;
                        $contactsCardArray[$i]['pipeline_stage'] = _PIPELINE_STAGE_NEW_LEAD;
                        $i++;
                    }
                }
                if($stageData['stage_number'] == _PIPELINE_STAGE_WON || $stageData['stage_number'] == _PIPELINE_STAGE_LOST)
                {
                    $premium = 0;

                    $premium = array_reduce(array_map(function($item) {
                        return isset($item['premiumAmt']) ? (float) $item['premiumAmt'] : 0.0;
                    }, $pipeLineStageData['totalPremiumArr']),

                    function($carry, $item) {
                        return $carry + $item;
                    },
                    0);
                    $premiumAmout = $premium;
                }
                else
                {
                    $premiumAmt = $ContactOpportunitiesTable->getProjectedValueByPipelineStageNewVue($stageData['stage_number'], $loginAgencyId, $loginUserId, $loginRoleType, $loginRoleTypeFlag, $searchData);
                    $premiumAmout = $premiumAmt['projected_value'];
                }
                $searchData['allStagePremiumCount'] = true;
                $pipeline['pipeline_stage_'.$stageData['stage_number']] = $contactsCardArray;
                $pipeline['contacts_count_stage_'.$stageData['stage_number']] = $pipeLineStageData['count'];
                $pipeline['cards_in_stage'][$stageData['stage_number']] = $pipeLineStageData['count'];
                $pipeline['projected_value_stage_'.$stageData['stage_number']] = $premiumAmout;
                $stage++;
            }
            //agency details
            $agencyDetails = $Agency->agencyDetails($loginAgencyId);
            $usersTimezone = 'America/Phoenix';
            if(!empty($agencyDetails['us_state_id']))
            {
              $stateDetail = $UsStates->stateDetail($agencyDetails['us_state_id']);
            }
            if(isset($agencyDetails['time_zone']) && !empty($agencyDetails['time_zone']))
            {
              $usersTimezone =  $agencyDetails['time_zone'];
            }
            else if(isset($stateDetail) && !empty($stateDetail))
            {
              $usersTimezone =  $stateDetail->time_zone;
            }
            $currentDate = CommonFunctions::convertUtcToEmployeeTimeZone($usersTimezone, date("Y-m-d"));
            $pipeline['currentDate'] = $currentDate;
			$return['Pipeline.pipelineStagesdata'] = [
				$agencyId => $pipeline
			];
        }catch (\Exception $e) {
            $txt=date('Y-m-d H:i:s').' :: Campaign Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
		return $return;
    }

    // Function to find the task with the nearest upcoming due date
    public static function findNearestDueDateTask($data, $loginAgencyId) {
        $nearestDueDate = null;
        $nearestTask = null;
        $Agency = TableRegistry::getTableLocator()->get('Agency');
        $UsStates = TableRegistry::getTableLocator()->get('UsStates');

        //agency details
        $agencyDetails = $Agency->agencyDetails($loginAgencyId);

        $usersTimezone = 'America/Phoenix';
        if(!empty($agencyDetails['us_state_id']))
        {
          $stateDetail = $UsStates->stateDetail($agencyDetails['us_state_id']);
        }
        if(isset($agencyDetails['time_zone']) && !empty($agencyDetails['time_zone']))
        {
          $usersTimezone =  $agencyDetails['time_zone'];
        }
        else if(isset($stateDetail) && !empty($stateDetail))
        {
          $usersTimezone =  $stateDetail->time_zone;
        }
        $today = CommonFunctions::convertUtcToEmployeeTimeZone($usersTimezone,date("Y-m-d"));
        foreach ($data as $task) {
            $dueDate = date('Y-m-d H:i:s', strtotime($task['due_date']));


            if($nearestDueDate === null)
            {
                $nearestDueDate = $dueDate;
                $nearestTask = $task;
            }
            if (($dueDate >=  $today && $dueDate <= $nearestDueDate)) {
                $nearestDueDate = $dueDate;
                $nearestTask = $task;
            }
        }
        return $nearestTask;
}
    //changes status of pipeline stage:
    public function changePipelineStageStatus($objectData)
    {
        $session = Router::getRequest()->getSession();
        $loginAgencyId = $session->read("Auth.User.agency_id");
        $loginUserId = $session->read("Auth.User.user_id");
        $stageId = $objectData['stageId'];
        if($objectData['stageStatus'])
        {
            $status = _ID_STATUS_ACTIVE;
        }
        else
        {
            $status = _ID_STATUS_INACTIVE;
        }
        $PipelineStage = TableRegistry::getTableLocator()->get('PipelineStages');
        $updatePipelineStage = $PipelineStage->updateAll(['status' => $status], ['id' => $stageId, 'user_id' => $loginUserId, 'agency_id' => $loginAgencyId]);
        return $updatePipelineStage;
    }

    // save custom user data:-------
    public function saveUserCustomStage($objectData)
    {
        try
        {
            $session = Router::getRequest()->getSession();
            $loginAgencyId = $session->read("Auth.User.agency_id");
            $loginUserId = $session->read("Auth.User.user_id");
            $stageName = trim($objectData['stageName']);
			$stageName = ucfirst($stageName);
            $PipelineStage = TableRegistry::getTableLocator()->get('PipelineStages');
            $agencyDetail = TableRegistry::getTableLocator()->get('Agency');
            $lastStageNumberArr = $PipelineStage->getLastStageNumber($loginUserId, $loginAgencyId);

            $lastStageNumber = $lastStageNumberArr['stage_number'];

            $agencyDetails = $agencyDetail->activeAgencyDetails($loginAgencyId);
            $agencylastStagenumber = $PipelineStage->getLastStageNumber($agencyDetails['user_id'], $agencyDetails['id']);
            $agencyLastStage = $agencylastStagenumber['stage_number'];
            if(!$agencyLastStage)
            {
                $agencyLastStage = 8;
            }
            $newStageData = [
                'agency_id' => $loginAgencyId,
                'user_id' => $agencyDetails['user_id'],
                'stage_number' => $agencyLastStage+1,
                'stage_order' => CUSTOM_STAGE_ORDER,
                'stage_name' => $stageName,
                'stage_color' => '#538ECB',
                'stage_type' => CUSTOM_STAGE,
                'stage_added_by' => $loginUserId
            ];
            $stage = $PipelineStage->newEntity();
            $patchStage = $PipelineStage->patchEntity($stage, $newStageData);
            if($saveAgencyStage = $PipelineStage->save($patchStage))
            {
                $agencyAllStages = $PipelineStage->getPipelineStagesList($loginAgencyId, $agencyDetails['user_id']);
                if($agencyAllStages)
                {
                    foreach ($agencyAllStages as $stage)
                    {
                        $previousOrder = $stage['stage_order'];
                        $newStageOrder = $previousOrder+1;
                        if($stage['id'] != $saveAgencyStage['id'])
                        {
                            $updateStageOrder = $PipelineStage->updateAll(['stage_order' => $newStageOrder], ['id' => $stage['id'], 'user_id' => $agencyDetails['user_id'], 'agency_id' => $loginAgencyId]);
                        }
                    }
                }
                if($loginUserId != $agencyDetails['user_id'])
                {
                    $stage = $PipelineStage->newEntity();
                    $newStageData['user_id'] = $loginUserId;
                    $newStageData['stage_number'] =  $lastStageNumber+1;
                    $patchStage = $PipelineStage->patchEntity($stage, $newStageData);
                    if($saveUserStage = $PipelineStage->save($patchStage))
                    {
                        $allStages = $PipelineStage->getPipelineStagesList($loginAgencyId, $loginUserId);
                        if($allStages)
                        {
                            foreach ($allStages as $stage)
                            {
                                $previousOrder = $stage['stage_order'];
                                $newStageOrder = $previousOrder+1;
                                if($stage['id'] != $saveUserStage['id'])
                                {
                                    $updateStageOrder = $PipelineStage->updateAll(['stage_order' => $newStageOrder], ['id' => $stage['id'], 'user_id' => $loginUserId, 'agency_id' => $loginAgencyId]);
                                }
                            }
                        }
                    }
                }
            }
            $msg = ['status' => _ID_SUCCESS];
            return $msg;
            }
            catch (\Exception $e)
            {

            }
    }

    public function saveStageOrderOnDrop($objectData)
    {
        $session = Router::getRequest()->getSession();
        $loginAgencyId = $session->read("Auth.User.agency_id");
        $loginUserId = $session->read("Auth.User.user_id");
        $PipelineStage = TableRegistry::getTableLocator()->get('PipelineStages');
        $agencyDetail = TableRegistry::getTableLocator()->get('Agency');
        $agencyDetails = $agencyDetail->activeAgencyDetails($loginAgencyId);
        $allStages = $objectData;
        $order = 1;
        foreach ($allStages as $stageData)
        {
            $PipelineStage->updateAll(['stage_order' => $order], ['id' => $stageData['id'], 'user_id' => $loginUserId, 'agency_id' => $loginAgencyId]);
            $order++;
        }
        if($loginUserId != $agencyDetails['user_id'])
        {
            $stageList = $PipelineStage->getPipelineStagesList($loginAgencyId, $loginUserId);
            foreach ($stageList as $stage)
            {
                $PipelineStage->updateAll(['stage_order' => $stage['stage_order']], ['user_id' => $agencyDetails['user_id'], 'agency_id' => $loginAgencyId, 'stage_number' => $stage['stage_number']]);
            }
        }
        $msg = ['status' => _ID_SUCCESS];
        return $msg;
    }
    public function getUserCardCountInStage($objectData)
    {
        $return = [];
		$pipeline = [];
		try
		{
		  $session = Router::getRequest()->getSession();
		  $loginAgencyId = $session->read("Auth.User.agency_id");
		  $loginUserId = $session->read("Auth.User.user_id");
		  $loginRoleType = $session->read('Auth.User.role_type');
		  $loginRoleTypeFlag = $session->read('Auth.User.role_type_flag');
		  $ContactOpportunitiesTable = TableRegistry::getTableLocator()->get('ContactOpportunities');
          $PipelineStage = TableRegistry::getTableLocator()->get('PipelineStages');
          $pipeLineStagesdata = $PipelineStage->getPipelineStagesList($loginAgencyId, $loginUserId);
          $stage = 0;
              foreach ($pipeLineStagesdata as $stageData)
              {
                  $pipeLineStageData = $ContactOpportunitiesTable->opportunitiesByPipelineStageVue($stageData['stage_number'], $loginAgencyId, $loginUserId, $loginRoleType, $loginRoleTypeFlag, $searchData, '', _PAGE_TYPE_DEFAULT);
                  $contactsCardArray = [];
                  $pipeline['cards_in_stage_'.$stageData['stage_number']] = $pipeLineStageData['count'];
              }
			$return['Pipeline.getUserCardCountInStage'] = [
				$agencyId => $pipeline
			];
              return $return;
		 }catch (\Exception $e) {

            $txt=date('Y-m-d H:i:s').' :: Campaign Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
		return $return;
    }

    //update stage name:-
    public function updateStageName($objectData)
    {
        $session = Router::getRequest()->getSession();
        $loginAgencyId = $session->read("Auth.User.agency_id");
        $loginUserId = $session->read("Auth.User.user_id");
        $stageId = $objectData['stage_id'];
        $stageName = $objectData['stage_name'];
        $stageNumber = $objectData['stage_number'];
        $PipelineStage = TableRegistry::getTableLocator()->get('PipelineStages');

        $editStage = $PipelineStage->getStageByStageOwner($loginAgencyId, $loginUserId, $stageId, $stageNumber);
        if($editStage) {
            $editStages = $PipelineStage->updateAll(['stage_name' => $stageName], ['stage_number' => $editStage->stage_number, 'agency_id' => $editStage->agency_id, 'stage_added_by' => $editStage->stage_added_by]);
              $msg = ['status' => _ID_SUCCESS, 'message' => 'Stage name Updated Successfully.'];
        }
        else
        {
            $msg = ['status' => _ID_FAILED, 'message' => 'Stage name is not Updated Successfully.'];
        }
        return $msg;
    }
    //    delete custom stage:-
    public function deleteCustomStage($objectData)
    {
        $session = Router::getRequest()->getSession();
        $loginAgencyId = $session->read("Auth.User.agency_id");
        $loginUserId = $session->read("Auth.User.user_id");
        $stageId = $objectData['stage_id'];
        $stageNumber = $objectData['stage_number'];
        $PipelineStage = TableRegistry::getTableLocator()->get('PipelineStages');
        $deleteStage = $PipelineStage->getStageByStageOwner($loginAgencyId, $loginUserId, $stageId, $stageNumber);
        if($deleteStage)
        {
             $deleteAllStages = $PipelineStage->updateAll(['status' => _ID_STATUS_DELETED], ['stage_number' => $deleteStage->stage_number, 'agency_id' => $deleteStage->agency_id, 'stage_added_by' => $deleteStage->stage_added_by]);
             $msg = ['status' => _ID_SUCCESS, 'message' => 'Stage deleted successfully.'];
        }
        else
        {
             $msg = ['status' => _ID_FAILED, 'message' => 'Stage is not deleted.'];
        }
         return $msg;
    }

}
