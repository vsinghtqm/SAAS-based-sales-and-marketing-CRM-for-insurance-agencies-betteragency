<?php

namespace App\Lib\ApiProviders;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use App\Classes\CommonFunctions;


class Services
{
	public function saveServiceTicket($objectData){
		try
        {
            $myfile = fopen(ROOT."/logs/contactrail.log", "a") or die("Unable to open file!"); 
			$session = Router::getRequest()->getSession(); 
            $login_user_id = $session->read("Auth.User.user_id");
            $login_agency_id = $session->read("Auth.User.agency_id");
			$login_first_name = $session->read('Auth.User.first_name');
			$login_last_name = $session->read('Auth.User.last_name');
			$user_name=$login_first_name.' '.$login_last_name;
			$agency = TableRegistry::getTableLocator()->get('Agency');
			$services = TableRegistry::getTableLocator()->get('Services');
			$ContactBusiness = TableRegistry::getTableLocator()->get('ContactBusiness');
			$Contacts = TableRegistry::getTableLocator()->get('Contacts');
			$contactOpportunities = TableRegistry::getTableLocator()->get('ContactOpportunities');
			$AgencyCampaignMaster = TableRegistry::getTableLocator()->get('AgencyCampaignMaster');
			$TeamUserLinks = TableRegistry::getTableLocator()->get('TeamUserLinks');
			$ContactCommunications = TableRegistry::getTableLocator()->get('ContactCommunications');
			$users = TableRegistry::getTableLocator()->get('Users');
			$agency_admin_user_id = $agency->agencyUsers($login_agency_id);
            if(isset($objectData) && !empty($objectData)){
                if($objectData['serviceClaim'] == _SERVICE_PIPELINE_STAGE_NEW_SERVICE_REQUEST)
				{
					$oppId = explode('-', $objectData['opportunity_id']);
					$opportunity_id = isset($objectData['opportunity_id'])&& !empty($objectData['opportunity_id']) ? $oppId[1] : '';
					$contact_id = isset($objectData['contact_id'])&& !empty($objectData['contact_id']) ? $objectData['contact_id'] : '';
        			$contact_business_id = isset($objectData['contact_business_id'])&& !empty($objectData['contact_business_id']) ? $objectData['contact_business_id'] : '';
					// Create service 
					$ticket_number = $services->serviceTicketNumber(array('login_agency_id' => $login_agency_id));
					$personal_commercial = _LINE_PERSONAL;
					if(isset($opportunity_id) && !empty($opportunity_id)){
						$ContactOpportunitiesDetail = $contactOpportunities->get($opportunity_id);
						if(!empty($ContactOpportunitiesDetail)&& isset($ContactOpportunitiesDetail) && isset($ContactOpportunitiesDetail['contact_business_id'])&& !empty($ContactOpportunitiesDetail['contact_business_id'])){
							$personal_commercial = _LINE_COMMERCIAL;
						}
					}
					if((isset($contact_id) && !empty($contact_id)) || (isset($contact_business_id) && !empty($contact_business_id))){

						$contactDetails=$policy_number=$insurance_type_id="";
						if(isset($contact_business_id) && !empty($contact_business_id))
						{
							$personal_commercial = _LINE_COMMERCIAL;
							$contactDetails = $ContactBusiness->get($contact_business_id);
						}
						else
						{
							$contactDetails = $Contacts->get($contact_id);
						}
						//round robin code start
							//get assign service owner id by round robin
							//if  assign service owner id empty pass login user id
							$service_owner_id=0;
							$sale_owner_id = $contactDetails->user_id; //get this id from contact table
							$round_robin_service_arr='';
							// check if owner id is posted then assign posted owner id otherwise assign owner id by round robin
							$assign_service_owner_id = isset($objectData['assigned_owner']) && !empty($objectData['assigned_owner']) ? $objectData['assigned_owner'] : null;
							// end here
							if(isset($objectData['assigned_owner']) && !empty($objectData['assigned_owner']) && $objectData['assigned_owner'] == 'service_team')
							{
								
								$service_team_owner_id = CommonFunctions::getServiceUserIDToAssign($login_agency_id,_AGENCY_TEAM_SERVICE,$personal_commercial);
								if(isset($service_team_owner_id['user_id_to_return']) && !empty($service_team_owner_id['user_id_to_return']))
								{
			
									$assign_service_owner_id = $service_team_owner_id['user_id_to_return'];
									$assign_bell_owner_id =  $service_team_owner_id['user_id_to_return'];
								} else
								{
									  $round_robin_service_arr = CommonFunctions::getServiceUserIDbyRoundRobin($login_agency_id, $sale_owner_id,$personal_commercial);
									if(isset($round_robin_service_arr['user_id_to_return']) && !empty($round_robin_service_arr['user_id_to_return'])){
										$service_owner_id = $round_robin_service_arr['user_id_to_return'];
										$assign_service_owner_id = $service_owner_id;
										$assign_bell_owner_id =   $service_owner_id;
									}
								}
			
							}
							elseif(isset($objectData['assigned_owner']) && !empty($objectData['assigned_owner']) && $objectData['assigned_owner'] != 'service_team')
							{
									$round_robin_service_arr = CommonFunctions::getServiceUserIDbyRoundRobin($login_agency_id, $objectData['assigned_owner'],$personal_commercial);
									if(isset($round_robin_service_arr['user_id_to_return']) && !empty($round_robin_service_arr['user_id_to_return'])){
										$service_owner_id = $round_robin_service_arr['user_id_to_return'];
										$assign_service_owner_id = $service_owner_id;
										$assign_bell_owner_id =   $service_owner_id;
									}
			
							}
							//
							
							if(empty($assign_service_owner_id)){
									$round_robin_service_arr = CommonFunctions::getServiceUserIDbyRoundRobin($login_agency_id, $sale_owner_id,$personal_commercial);
									if(isset($round_robin_service_arr['user_id_to_return']) && !empty($round_robin_service_arr['user_id_to_return'])){
										$service_owner_id = $round_robin_service_arr['user_id_to_return'];
									   // $assign_service_owner_id = $service_owner_id;
										$assign_bell_owner_id = $service_owner_id;
									}
								}
			
							//$assign_bell_owner_id = isset($objectData['assigned_owner']) && $objectData['assigned_owner']=='service_team' ? $service_team_owner_id['user_id_to_return'] : $service_owner_id;
			
			
						if(isset($opportunity_id) && !empty($opportunity_id)){
							
							$opportunityDetail = $contactOpportunities->contactOpportunityDetailWithContactPolicyDetails($opportunity_id);
							
							if(isset($opportunityDetail) && !empty($opportunityDetail)){
			
								if(isset($opportunityDetail['policy_number']) && !empty($opportunityDetail['policy_number'])){
									$policy_number = $opportunityDetail['policy_number'];
								}
								if(isset($opportunityDetail['insurance_type_id']) && !empty($opportunityDetail['insurance_type_id'])){
									$insurance_type_id = $opportunityDetail['insurance_type_id'];
								}
								if(isset($objectData['start_campaign']) && $objectData['start_campaign']==_ID_SUCCESS && !empty($assign_service_owner_id)){
									if(isset($opportunityDetail['contact_business_id']) && !empty($opportunityDetail['contact_business_id'])){
										// Start commercial Campaign
										$checkCampaignExist = $AgencyCampaignMaster->checkCampaignExist(null,null,$login_agency_id,_SERVICE_PIPELINE_STAGE_NEW_SERVICE_REQUEST,_CAMPAIGN_TYPE_SERVICE_PIPELINE,null,_LINE_COMMERCIAL);
											if(!empty($checkCampaignExist)){
												$start_campaign_id = $checkCampaignExist['id'];
												$service_pipeline_campaign_result = CommonFunctions::startCampaign(null,$start_campaign_id,_SERVICE_PIPELINE_STAGE_NEW_SERVICE_REQUEST,null,$assign_service_owner_id,null,null,$opportunityDetail['contact_business_id']);
											}
									}else{
										// Start personal Campaign
										 $checkCampaignExist = $AgencyCampaignMaster->checkCampaignExist(null,null,$login_agency_id,_SERVICE_PIPELINE_STAGE_NEW_SERVICE_REQUEST,_CAMPAIGN_TYPE_SERVICE_PIPELINE);
											if(!empty($checkCampaignExist)){
												$start_campaign_id = $checkCampaignExist['id'];
												$service_pipeline_campaign_result = CommonFunctions::startCampaign($objectData['contact_id'],$start_campaign_id,_SERVICE_PIPELINE_STAGE_NEW_SERVICE_REQUEST,null,$assign_service_owner_id);
											}
									}
								}
							}else{
									 echo json_encode(array('status' => _ID_FAILED));
							}
						}else{
			
							// Start personal Campaign
							if(isset($objectData['start_campaign']) && $objectData['start_campaign']==_ID_SUCCESS && !empty($assign_service_owner_id)){
								if(isset($contact_business_id) && !empty($contact_business_id))
								{
									$checkCampaignExist = $AgencyCampaignMaster->checkCampaignExist(null,null,$login_agency_id,_SERVICE_PIPELINE_STAGE_NEW_SERVICE_REQUEST,_CAMPAIGN_TYPE_SERVICE_PIPELINE,null,_LINE_COMMERCIAL);
									if(!empty($checkCampaignExist)){
										$start_campaign_id=$checkCampaignExist['id'];
										$service_pipeline_campaign_result = CommonFunctions::startCampaign($contact_id,$start_campaign_id,_SERVICE_PIPELINE_STAGE_NEW_SERVICE_REQUEST,null,$assign_service_owner_id);
										$service_pipeline_campaign_result = CommonFunctions::startCampaign(null,$start_campaign_id,_SERVICE_PIPELINE_STAGE_NEW_SERVICE_REQUEST,null,$assign_service_owner_id,null,null,$contact_business_id);
									}
								}
								else
								{
									$checkCampaignExist = $AgencyCampaignMaster->checkCampaignExist(null,null,$login_agency_id,_SERVICE_PIPELINE_STAGE_NEW_SERVICE_REQUEST,_CAMPAIGN_TYPE_SERVICE_PIPELINE);
									if(!empty($checkCampaignExist)){
										$start_campaign_id = $checkCampaignExist['id'];
										$service_pipeline_campaign_result = CommonFunctions::startCampaign($contact_id,$start_campaign_id,_SERVICE_PIPELINE_STAGE_NEW_SERVICE_REQUEST,null,$assign_service_owner_id);
									}
								}
			
							}
						}
						
			
						$service = $services->newEntity();
						$info=[];
						$info['agency_id']=$login_agency_id;
						$info['user_id']=$assign_service_owner_id;
						if(isset($contact_business_id) && !empty($contact_business_id))
						{
							$info['contact_business_id']=$contact_business_id;
						}
						else
						{
							$info['contact_id']=$objectData['contact_id'];
						}
						$info['insurance_type_id'] = $insurance_type_id;
						$info['policy_number'] = $policy_number;
						$info['title'] = $objectData['ticketTitle'];//$objectData['service_request_service_title'];
						$info['description'] = $objectData['ticketNote'];
						$info['stage'] = _SERVICE_PIPELINE_STAGE_NEW_SERVICE_REQUEST;
						$info['type'] = _SERVICE_PIPELINE_TYPE_NEW_SERVICE_REQUEST;
						$info['start_campaign'] = isset($objectData['start_campaign'])&& !empty($objectData['start_campaign']) ? $objectData['start_campaign'] :'';
						$info['date_stage_moved'] = date('Y-m-d H:i:s');
						$info['ticket_number'] = isset($ticket_number) && !empty($ticket_number) ? $ticket_number :'';
						$info['opportunity_id'] =    isset($opportunity_id) && !empty($opportunity_id) ? $opportunity_id :'';
						$info['service_request_type_id'] = isset($objectData['service_type_id'])&& !empty($objectData['service_type_id']) ? $objectData['service_type_id'] : '';
						$info['carrier_id'] = isset($objectData['carrier_id'])&& !empty($objectData['carrier_id']) ? $objectData['carrier_id'] : '';
						//$info['campaign_initiate']=  _SERVICE_PIPELINE_STAGE_NEW_SERVICE_REQUEST;
						$service = $services->patchEntity($service, $info);
						
						if ($service = $services->save($service)){
							//service created then update lead for assign service owner
							if(isset($round_robin_service_arr['team_user_link_id']) && !empty($round_robin_service_arr['team_user_link_id'])){
								$team_user_link_id = $round_robin_service_arr['team_user_link_id'];
								$teamUserLinkDetails = $TeamUserLinks->teamUserLinkDetails($team_user_link_id);
								if(isset($teamUserLinkDetails) && !empty($teamUserLinkDetails))
									$current_lead = $teamUserLinkDetails['current_lead']+1;
									$TeamUserLinks->updateAll(['current_lead'=>$current_lead],['id'=>$team_user_link_id]);
							}
							elseif(isset($service_team_owner_id['team_user_link_id']) && !empty($service_team_owner_id['team_user_link_id']))
							{
									$team_user_link_id = $service_team_owner_id['team_user_link_id'];
									$teamUserLinkDetails = $TeamUserLinks->teamUserLinkDetails($team_user_link_id);
									if(isset($teamUserLinkDetails) && !empty($teamUserLinkDetails))
									$current_lead = $teamUserLinkDetails['current_lead']+1;
									$TeamUserLinks->updateAll(['current_lead'=>$current_lead],['id'=>$team_user_link_id]);
							}
							//
								
								//send system notification
								$recipient_id=0;
								if(isset($service->user_id) && !empty($service->user_id)){
									$recipient_id = $service->user_id;
								}
								if(isset($recipient_id) && !empty($recipient_id))
								{
									$notification_type_id = _ID_NOTIFICATION_NEW_SERVICE_REQUEST_ASSIGN;
									$sendNotification = CommonFunctions::sendNotification($login_agency_id,$recipient_id, null, $notification_type_id);
								}
			
								$contact_name="";
								if(isset($contact_business_id) && !empty($contact_business_id))
								{
									$contact_name = $contactDetails->name;
								}
								else
								{
									if(isset( $contactDetails->first_name) && !empty( $contactDetails->first_name)){
										$contact_name = ucwords($contactDetails->first_name);
									}
									if(isset( $contactDetails->middle_name) && !empty( $contactDetails->middle_name)){
										$contact_name = $contact_name." ".ucwords($contactDetails->middle_name);
									}
									if(isset( $contactDetails->last_name) && !empty( $contactDetails->last_name)){
										$contact_name = $contact_name." ".ucwords($contactDetails->last_name);
									}
								}
								// bell notification
								$contactCommuniction = $ContactCommunications->newEntity();
								$communication = [];
								$communication['service_notification_id'] = $service->id;
								$communication['user_id'] = $assign_bell_owner_id;
								$communication['agency_id'] = $login_agency_id;
								$communication['contact_id'] = $objectData['contact_id'];
								$communication['communication_type'] = _ID_SUCCESS;
								$communication['mail_subject'] = 'Service Ticket';
								$communication['in_out'] = _ID_SUCCESS;
								$communication['read_status'] = _SERVICE_READ_STATUS_BELL_NOTTIFICATION;
								$communication['message'] = 'A new service opportunity has been created for <b>'.$contact_name.'</b>.';
								$contactCommuniction = $ContactCommunications->patchEntity($contactCommuniction, $communication);
								$ContactCommunications->save($contactCommuniction);
								// Add service/contact/policy Attachments
								//print_r($_FILES);
								///print_r($objectData); exit;
								 isset($objectData['service_attachments']) && !empty($objectData['service_attachments'][0]['name']) ? $this->uploadServiceAttachments(['objectData'=>$objectData,'service'=>$service]) : '';
								 $response = json_encode(array('status' => _ID_SUCCESS,'message'=>'Record saved successfully'));
						}else{
								echo json_encode(array('status' => _ID_FAILED));die;
						}
			
					}else{ 
						$agency_admin_user_id = $agency->agencyUsers($login_agency_id);
						$bell_owner_id ='';
						$assign_bell_owner_id='';
						$round_robin_service_arr='';
						$assign_service_owner_id = isset($objectData['assigned_owner']) && !empty($objectData['assigned_owner']) ? $objectData['assigned_owner'] : null;
			
						if(isset($objectData['assigned_owner']) && !empty($objectData['assigned_owner']) && $objectData['assigned_owner'] == 'service_team')
							{
								$service_team_owner_id = CommonFunctions::getServiceUserIDToAssign($login_agency_id,_AGENCY_TEAM_SERVICE,$personal_commercial);
								if(isset($service_team_owner_id['user_id_to_return']) && !empty($service_team_owner_id['user_id_to_return']))
								{
									$assign_service_owner_id = $service_team_owner_id['user_id_to_return'];
									$assign_bell_owner_id = $service_team_owner_id['user_id_to_return'];
								}else
								{
									$round_robin_service_arr = CommonFunctions::getServiceUserIDbyRoundRobin($login_agency_id, $agency_admin_user_id->user_id,$personal_commercial);
									if(isset($round_robin_service_arr['user_id_to_return']) && !empty($round_robin_service_arr['user_id_to_return'])){
										$bell_owner_id = $round_robin_service_arr['user_id_to_return'];
										$assign_service_owner_id = $bell_owner_id;
										$assign_bell_owner_id = $bell_owner_id;
									}
								}
			
							}
							if(isset($objectData['assigned_owner']) && !empty($objectData['assigned_owner']) && $objectData['assigned_owner'] != 'service_team')
							{
			
								$round_robin_service_arr = CommonFunctions::getServiceUserIDbyRoundRobin($login_agency_id, $objectData['assigned_owner'],$personal_commercial);
								if(isset($round_robin_service_arr['user_id_to_return']) && !empty($round_robin_service_arr['user_id_to_return'])){
									$bell_owner_id = $round_robin_service_arr['user_id_to_return'];
									$assign_service_owner_id = $bell_owner_id;
									$assign_bell_owner_id = $bell_owner_id;
								}
			
							}
							//
			
							if(empty($assign_service_owner_id)){
								$round_robin_service_arr = CommonFunctions::getServiceUserIDbyRoundRobin($login_agency_id, $agency_admin_user_id->user_id,$personal_commercial);
								if(isset($round_robin_service_arr['user_id_to_return']) && !empty($round_robin_service_arr['user_id_to_return'])){
									$bell_owner_id = $round_robin_service_arr['user_id_to_return'];
									////$assign_service_owner_id = $bell_owner_id;
									$assign_bell_owner_id = $bell_owner_id;
								}
							}
						   // $assign_bell_owner_id = isset($objectData['assigned_owner']) && $objectData['assigned_owner']=='service_team' ? $service_team_owner_id['user_id_to_return'] : $bell_owner_id;
			
						$userDetails ="";
						$userDetails = $users->get($agency_admin_user_id->user_id);
						$first_name="";
						$last_name ="";
			
						if(isset($userDetails->first_name) && !empty($userDetails->first_name)){
							$first_name= ucwords($userDetails->first_name);
						}if(isset($userDetails->last_name) && !empty($userDetails->last_name)){
							$last_name =" ".ucwords($userDetails->last_name);
						}
			
						$service = $services->newEntity();
						$info=[];
						$info['agency_id']=$login_agency_id;
						$info['user_id']=$assign_service_owner_id;
					   // $info['title']=$objectData['service_request_service_title'];
						$info['description']=$objectData['service_request_service_details'];
						$info['stage']=_SERVICE_PIPELINE_STAGE_IN_UNASSIGNED_SERVICE_REQUEST;
						$info['type']=_SERVICE_PIPELINE_TYPE_NEW_SERVICE_REQUEST;
						$info['start_campaign'] = _ID_FAILED;
						$info['ticket_number'] = isset($ticket_number) && !empty($ticket_number) ? $ticket_number :'';
						//$info['campaign_initiate']= _SERVICE_PIPELINE_STAGE_NEW_SERVICE_REQUEST;
						$info['date_stage_moved'] = date('Y-m-d H:i:s');
						$info['carrier_id'] = isset($objectData['carrier_id'])&& !empty($objectData['carrier_id']) ? $objectData['carrier_id'] : '';
						$service = $services->patchEntity($service, $info);
			
						if ($service = $services->save($service)){
			
							//service created then update lead for assign service owner
							if(isset($round_robin_service_arr['team_user_link_id']) && !empty($round_robin_service_arr['team_user_link_id'])){
			
								$team_user_link_id = $round_robin_service_arr['team_user_link_id'];
								$teamUserLinkDetails = $TeamUserLinks->teamUserLinkDetails($team_user_link_id);
								if(isset($teamUserLinkDetails) && !empty($teamUserLinkDetails))
									$current_lead = $teamUserLinkDetails['current_lead']+1;
									$TeamUserLinks->updateAll(['current_lead'=>$current_lead],['id'=>$team_user_link_id]);
							}
							elseif(isset($service_team_owner_id['team_user_link_id']) && !empty($service_team_owner_id['team_user_link_id']))
							{
								$team_user_link_id = $service_team_owner_id['team_user_link_id'];
								$teamUserLinkDetails = $TeamUserLinks->teamUserLinkDetails($team_user_link_id);
								if(isset($teamUserLinkDetails) && !empty($teamUserLinkDetails))
									$current_lead = $teamUserLinkDetails['current_lead']+1;
									$TeamUserLinks->updateAll(['current_lead'=>$current_lead],['id'=>$team_user_link_id]);
							}
			
							//send system notification
							$recipient_id=0;
							if(isset($service->user_id) && !empty($service->user_id)){
								$recipient_id=$service->user_id;
							}
							if(isset($recipient_id) && !empty($recipient_id))
							{
								$notification_type_id=_ID_NOTIFICATION_NEW_SERVICE_REQUEST_ASSIGN;
								$sendNotification = CommonFunctions::sendNotification($login_agency_id,$recipient_id, null, $notification_type_id);
							}
							//
			
								// bell notification
								$contactCommuniction = $ContactCommunications->newEntity();
								$communication = [];
								$communication['service_notification_id'] = $service->id;
								$communication['user_id'] = $assign_bell_owner_id ;
								$communication['agency_id'] = $login_agency_id;
								$communication['contact_id'] = $objectData['contact_id'];
								$communication['communication_type'] = _ID_SUCCESS;
								$communication['mail_subject'] = 'Service Ticket';
								$communication['in_out'] = _ID_SUCCESS;
								$communication['read_status'] = _SERVICE_READ_STATUS_BELL_NOTTIFICATION;
								$communication['message'] = 'A new unassigned service opportunity has been created. Click here to view';
								$contactCommuniction = $ContactCommunications->patchEntity($contactCommuniction, $communication);
								$ContactCommunications->save($contactCommuniction);
			
							// Add service/contact/policy Attachments
							isset($objectData['service_attachments']) && !empty($objectData['service_attachments'][0]['name']) ? $this->uploadServiceAttachments(['objectData'=>$objectData,'service'=>$service]) : '';
			
							$response = json_encode(array('status' => _ID_SUCCESS,'message'=>'Record saved successfully'));
						}else{
							echo json_encode(array('status' => _ID_FAILED));die;
						}
					}
				}
				else
				{
					$claim_initiated_by ='';
					$claim_initiated_by = $objectData['claim_initiated_by'];
					$agency_admin_user_id = $agency->agencyUsers($login_agency_id);
					$oppId = explode('-', $objectData['opportunity_id']);
					$opportunity_id = isset($objectData['opportunity_id'])&& !empty($objectData['opportunity_id']) ? $oppId[1] : '';
					$contact_business_id = $contact_name=$policy_number=$insurance_type_id="";
					if(isset($objectData['contact_business_id']) && !empty($objectData['contact_business_id']))
					{
						$contact_business_id = $objectData['contact_business_id'];
					}
					$contact_id = isset($objectData['contact_id'])&& !empty($objectData['contact_id']) ? $objectData['contact_id'] : '';

						if(!empty($claim_initiated_by))
						{
							// Create service Ticket by monika
								$ticket_number = $services->serviceTicketNumber(array('login_agency_id' => $login_agency_id));
						
							// end here

							if($claim_initiated_by ==_CLAIM_INITIATED_BY_AGENCY_STAFF){
								$campaign_initiate = _SERVICE_PIPELINE_CAMPAIGN_CLAIM_AGENCY_INITIATED;
								$reported_by = "Agency";
							}
							else if($claim_initiated_by==_CLAIM_INITIATED_BY_CLAIMANT){
								$campaign_initiate=_SERVICE_PIPELINE_CAMPAIGN_CLAIM_CLAIMANT_INITIATED;
								$reported_by = "Claimant";
							}
							$personal_commercial = _LINE_PERSONAL;
							if(isset($opportunity_id) && !empty($opportunity_id)){
								$ContactOpportunitiesDetail = $contactOpportunities->get($opportunity_id);
								if(!empty($ContactOpportunitiesDetail) && isset($ContactOpportunitiesDetail) && !empty($ContactOpportunitiesDetail['contact_business_id'] && isset($ContactOpportunitiesDetail['contact_business_id']))){

									$personal_commercial = _LINE_COMMERCIAL;
								}
							}
							if((isset($contact_id) && !empty($contact_id)) || (isset($contact_business_id) && !empty($contact_business_id))){

								//round robin code start
								//get assign service owner id by round robin
								//if  assign service owner id empty pass login user id
									$contactDetails="";
									if(isset($contact_business_id) && !empty($contact_business_id))
									{
										$personal_commercial = _LINE_COMMERCIAL;
										$contactDetails = $ContactBusiness->get($contact_business_id);
									}
									else
									{
										$contactDetails = $Contacts->get($objectData['contact_id']);
									}
									$claim_owner_id = 0;
									$bell_owner_id = $assign_bell_owner_id='';
									$sale_owner_id = '';

									$sale_owner_id = $contactDetails['user_id'];//get this id from contact table

									//assigned_owner id added by monika
										$assign_claim_owner_id = isset($objectData['assigned_owner']) && !empty($objectData['assigned_owner']) ? $objectData['assigned_owner'] : null;
									// end here by monika

									if(empty($assign_claim_owner_id)){

										$round_robin_service_arr = CommonFunctions::getClaimUserIDbyRoundRobin($login_agency_id, $sale_owner_id,$personal_commercial);
										if(isset($round_robin_service_arr['user_id_to_return']) && !empty($round_robin_service_arr['user_id_to_return'])){
											$bell_owner_id=$round_robin_service_arr['user_id_to_return'];
										}
									}


									// bell notification user id
									$assign_bell_owner_id = isset($objectData['assigned_owner']) && !empty($objectData['assigned_owner']) ? $objectData['assigned_owner'] : $bell_owner_id;


								if(isset($opportunity_id) && !empty($opportunity_id)){

									$contact_opportunity_detail = $contactOpportunities->contactOpportunityDetail($opportunity_id);

									$opportunityDetail = $contactOpportunities->contactOpportunityDetailWithContactPolicyDetails($opportunity_id);

									if(isset($contact_opportunity_detail) && !empty($contact_opportunity_detail)){
										if(isset($objectData['start_campaign']) && $objectData['start_campaign']==_ID_SUCCESS && !empty($assign_claim_owner_id)){
											if(isset($opportunityDetail['contact_business_id']) && !empty($opportunityDetail['contact_business_id'])){
												// Start commercial Campaign

												$checkCampaignExist = $AgencyCampaignMaster->checkCampaignExist(null,null,$login_agency_id,$campaign_initiate,_CAMPAIGN_TYPE_SERVICE_PIPELINE,null,_LINE_COMMERCIAL);
													if(!empty($checkCampaignExist)){
														$start_campaign_id = $checkCampaignExist['id'];
														$campaign_result = CommonFunctions::startCampaign(null, $start_campaign_id,$campaign_initiate,$contact_opportunity_detail['id'],$assign_claim_owner_id,null,null,$opportunityDetail['contact_business_id']);
													}

											}else{
												// Start personal Campaign
												$checkCampaignExist = $AgencyCampaignMaster->checkCampaignExist(null,null,$login_agency_id,$campaign_initiate,_CAMPAIGN_TYPE_SERVICE_PIPELINE);
												if(!empty($checkCampaignExist)){
													$start_campaign_id = $checkCampaignExist['id'];
													$campaign_result = CommonFunctions::startCampaign($objectData['contact_id'], $start_campaign_id,$campaign_initiate,$contact_opportunity_detail['id'],$assign_claim_owner_id);
												}
											}
										}
										//when claim submit create service request
										if(isset($contact_opportunity_detail) && !empty($contact_opportunity_detail))
										{
											if(isset($contact_business_id) && !empty($contact_business_id))
											{
												if(isset($opportunityDetail['contact_business']['name']) && !empty(isset($opportunityDetail['contact_business']['name']))){
													$contact_name .=$opportunityDetail['contact_business']['name'];
												}
											}
											else
											{
												if(isset($opportunityDetail['contact']['first_name']) && !empty(isset($opportunityDetail['contact']['first_name']))){
													$contact_name .=$opportunityDetail['contact']['first_name'];
												}
												if(isset($opportunityDetail['contact']['middle_name']) && !empty(isset($opportunityDetail['contact']['middle_name']))){
													$contact_name .=' '.$opportunityDetail['contact']['middle_name'];
												}
												if(isset($opportunityDetail['contact']['last_name']) && !empty(isset($opportunityDetail['contact']['last_name']))){
													$contact_name .=' '.$opportunityDetail['contact']['last_name'];
												}
											}

											if(isset($opportunityDetail['policy_number']) && !empty($opportunityDetail['policy_number'])){
												$policy_number=$opportunityDetail['policy_number'];
											}
											if(isset($opportunityDetail['insurance_type_id']) && !empty($opportunityDetail['insurance_type_id'])){
												$insurance_type_id=$opportunityDetail['insurance_type_id'];
											}
										}

									}else{
										echo json_encode(array('status' => _ID_FAILED));
									}

								}else{

									// personal
									if(isset($objectData['start_campaign']) && $objectData['start_campaign']==_ID_SUCCESS && !empty($assign_claim_owner_id)){
										// Start personal Campaign
										if(isset($contact_business_id) && !empty($contact_business_id))
										{
											$checkCampaignExist = $AgencyCampaignMaster->checkCampaignExist(null,null,$login_agency_id,$campaign_initiate,_CAMPAIGN_TYPE_SERVICE_PIPELINE,null,_LINE_COMMERCIAL);
											if(!empty($checkCampaignExist)){
												$start_campaign_id = $checkCampaignExist['id'];
												$campaign_result = CommonFunctions::startCampaign(null, $start_campaign_id,$campaign_initiate,null,$assign_claim_owner_id,null,null,$contact_business_id);
											}
										}
										else
										{
										$checkCampaignExist = $AgencyCampaignMaster->checkCampaignExist(null,null,$login_agency_id,$campaign_initiate,_CAMPAIGN_TYPE_SERVICE_PIPELINE);
											if(!empty($checkCampaignExist)){
												$start_campaign_id = $checkCampaignExist['id'];
												$campaign_result = CommonFunctions::startCampaign($objectData['contact_id'], $start_campaign_id,$campaign_initiate,null,$assign_claim_owner_id);
											}
										}

									}
								}
									$loss_date="";
									$claim_reported_date=isset($objectData['reported_date'])&& !empty($objectData['reported_date']) ? $objectData['reported_date'] : date('Y-m-d H:i:s');
									$claim_reported_date = str_replace('-', '/', $claim_reported_date);
									$reported_date=date("Y-m-d",strtotime($claim_reported_date));
									$claim_loss_date=isset($objectData['loss_date'])&& !empty($objectData['loss_date']) ? $objectData['loss_date'] :'';
									$loss_date = "";
									if(!empty($claim_loss_date)){
										$claim_loss_date = str_replace('-', '/', $claim_loss_date);
										$loss_date=date("Y-m-d",strtotime($claim_loss_date));
									}

									$service = $services->newEntity();
									$info=[];
									$info['agency_id'] = $login_agency_id;
									$info['user_id'] = $assign_claim_owner_id;
									if(isset($contact_business_id) && !empty($contact_business_id))
									{
										$info['contact_business_id'] = $contact_business_id;
									}
									else
									{
										$info['contact_id'] = $objectData['contact_id'];
									}
									$info['insurance_type_id'] = $insurance_type_id;
									$info['policy_number'] = $policy_number;
									$info['title'] = $objectData['ticketTitle'];//$objectData['service_request_service_title'];
									$info['description'] = $objectData['ticketNote'];  //$info['title']='Claim Request';
									$info['stage'] = _SERVICE_PIPELINE_STAGE_NEW_SERVICE_REQUEST;
									$info['type'] = _SERVICE_PIPELINE_TYPE_CLAIM;
									$info['start_campaign'] = isset($objectData['start_campaign'])&& !empty($objectData['start_campaign']) ? $objectData['start_campaign'] :'';
									$info['date_stage_moved'] = date('Y-m-d H:i:s');
									$info['ticket_number'] = isset($ticket_number) && !empty($ticket_number) ? $ticket_number :'';
									$info['campaign_initiate' ]=  $claim_initiated_by;
									$info['opportunity_id'] =  $opportunity_id;
									$info['carrier_id'] = isset($objectData['carrier_id'])&& !empty($objectData['carrier_id']) ? $objectData['carrier_id'] : '';

									$info['claim_reported_date'] = $reported_date;
									$info['claim_loss_date'] = $loss_date;
									$info['claim_type_of_loss'] = isset($objectData['type_of_loss'])&& !empty($objectData['type_of_loss']) ? $objectData['type_of_loss']:'';
									if(isset($objectData['date_of_loss_text']) && !empty($objectData['date_of_loss_text'])){
									$info['date_of_loss'] =  date('Y-m-d',strtotime($objectData['date_of_loss_text']));
									}
									if(isset($objectData['date_of_loss_text_service_center']) && !empty($objectData['date_of_loss_text_service_center'])){
									$info['date_of_loss'] =  date('Y-m-d',strtotime($objectData['date_of_loss_text_service_center']));
									}
									$service = $services->patchEntity($service, $info);
									if ($service = $services->save($service))
									{

										//service created then update lead for assign service owner
										if(isset($round_robin_service_arr['team_user_link_id']) && !empty($round_robin_service_arr['team_user_link_id'])){

												$team_user_link_id = $round_robin_service_arr['team_user_link_id'];
												$teamUserLinkDetails = $TeamUserLinks->teamUserLinkDetails($team_user_link_id);
												if(isset($teamUserLinkDetails) && !empty($teamUserLinkDetails))
													$current_lead = $teamUserLinkDetails['current_lead']+1;
													$TeamUserLinks->updateAll(['current_lead'=>$current_lead],['id'=>$team_user_link_id]);

										}
										//


										//send system notification
										$recipient_id = 0;
										if(isset($service->user_id) && !empty($service->user_id)){
											$recipient_id = $service->user_id;
										}
										if(isset($recipient_id) && !empty($recipient_id))
										{
											$notification_type_id = _ID_NOTIFICATION_NEW_CLAIM_REQUEST_ASSIGN;
											$sendNotification = CommonFunctions::sendNotification($login_agency_id,$recipient_id, null, $notification_type_id);
										}
										//

										$contact_name="";
										if(isset($contact_business_id) && !empty($contact_business_id))
										{
											$contact_name = $contactDetails->name;
										}
										else
										{
											if(isset( $contactDetails->first_name) && !empty( $contactDetails->first_name)){
												$contact_name = ucwords($contactDetails->first_name);
											}
											if(isset( $contactDetails->middle_name) && !empty( $contactDetails->middle_name)){
												$contact_name = $contact_name." ".ucwords($contactDetails->middle_name);
											}
											if(isset( $contactDetails->last_name) && !empty( $contactDetails->last_name)){
												$contact_name = $contact_name." ".ucwords($contactDetails->last_name);
											}
										}

										// bell notification
										$contactCommuniction = $ContactCommunications->newEntity();
										$communication = [];
										$communication['service_notification_id'] = $service->id;
										$communication['user_id'] = $assign_bell_owner_id;
										$communication['agency_id'] = $login_agency_id;
										$communication['contact_id'] = $objectData['contact_id'];
										$communication['communication_type'] = _ID_SUCCESS;
										$communication['mail_subject'] = 'Service Ticket';
										$communication['in_out'] = _ID_SUCCESS;
										$communication['read_status'] = _SERVICE_READ_STATUS_BELL_NOTTIFICATION;
										$communication['message'] = 'A new service opportunity has been created for <b>'.$contact_name.'</b>.';
										$contactCommuniction = $ContactCommunications->patchEntity($contactCommuniction, $communication);
										$ContactCommunications->save($contactCommuniction);

										// Add Attachments for service/contact/policy
									isset($objectData['service_attachments']) && !empty($objectData['service_attachments'][0]['name']) ? $this->uploadServiceAttachments(['objectData'=>$objectData,'service'=>$service]) : '';
									$response = json_encode(array('status' => _ID_SUCCESS,'message'=>'Record saved successfully'));
								}else{
									echo json_encode(array('status' => _ID_FAILED));die;
								}
							}
							else{
								$agency_admin_user_id = $agency->agencyUsers($login_agency_id);
								$assign_bell_owner_id=$round_robin_service_arr='';

								// if assigned owner id exist then assigned owner id will go and if not exist then agency admin user id will go
								$assign_claim_owner_id = isset($objectData['assigned_owner']) && !empty($objectData['assigned_owner']) ? $objectData['assigned_owner'] : null;


								if(empty($assign_claim_owner_id)){
									// Round Robin pass agency admin user id
									$round_robin_service_arr = CommonFunctions::getClaimUserIDbyRoundRobin($login_agency_id, $agency_admin_user_id->user_id,$personal_commercial);
									if(isset($round_robin_service_arr['user_id_to_return']) && !empty($round_robin_service_arr['user_id_to_return'])){
										$bell_owner_id = $round_robin_service_arr['user_id_to_return'];
									}
								}
								//

								$assign_bell_owner_id =  isset($objectData['assigned_owner']) && !empty($objectData['assigned_owner']) ? $objectData['assigned_owner'] :$bell_owner_id;


								$userDetails ="";
								$userDetails = $users->get($agency_admin_user_id->user_id);
								$first_name="";
								$last_name ="";

								if(isset($userDetails->first_name) && !empty($userDetails->first_name)){
									$first_name = ucwords($userDetails->first_name);
								}if(isset($userDetails->last_name) && !empty($userDetails->last_name)){
									$last_name =" ".ucwords($userDetails->last_name);
								}

								$claim_reported_date = isset($objectData['reported_date'])&& !empty($objectData['reported_date']) ? $objectData['reported_date'] : date('Y-m-d H:i:s');
								$claim_reported_date = str_replace('-', '/', $claim_reported_date);
								$reported_date = date("Y-m-d",strtotime($claim_reported_date));
								$claim_loss_date = isset($objectData['loss_date'])&& !empty($objectData['loss_date']) ? $objectData['loss_date'] :'';
								if(!empty($claim_loss_date)){
									$claim_loss_date = str_replace('-', '/', $claim_loss_date);
									$loss_date=date("Y-m-d",strtotime($claim_loss_date));
								}


								$service = $services->newEntity();
								$info=[];
								$info['agency_id'] = $login_agency_id;
								$info['user_id'] = $assign_claim_owner_id;
								$info['contact_id'] = $objectData['contact_id'];
								if(isset($objectData['ticketTitle']) && !empty($objectData['ticketTitle']))
								{
								$info['title'] = $objectData['service_ticketTitlerequest_service_title'];
								}
								$info['description'] = $objectData['ticketNote'];
								$info['stage'] = _SERVICE_PIPELINE_STAGE_IN_UNASSIGNED_SERVICE_REQUEST;
								$info['type'] = _SERVICE_PIPELINE_TYPE_CLAIM;
								$info['start_campaign'] = _ID_FAILED;
								$info['date_stage_moved'] = date('Y-m-d H:i:s');
								$info['ticket_number'] = isset($ticket_number) && !empty($ticket_number) ? $ticket_number :'';
								$info['campaign_initiate'] =  $claim_initiated_by;
								$info['carrier_id'] = isset($objectData['carrier_id'])&& !empty($objectData['carrier_id']) ? $objectData['carrier_id'] : '';
								$info['claim_reported_date'] = $reported_date;
								$info['claim_loss_date'] = $loss_date;
								$info['claim_type_of_loss'] = isset($objectData['type_of_loss'])&& !empty($objectData['type_of_loss']) ? $objectData['type_of_loss']:'';
								if(isset($objectData['date_of_loss_text_service_center']) && !empty($objectData['date_of_loss_text_service_center'])){
									$info['date_of_loss'] =  date('Y-m-d',strtotime($objectData['date_of_loss_text_service_center']));
									}

								$service = $services->patchEntity($service, $info);
								if ($service = $services->save($service))
								{

								//service created then update lead for assign service owner
								if(isset($round_robin_service_arr['team_user_link_id']) && !empty($round_robin_service_arr['team_user_link_id'])){

									$team_user_link_id = $round_robin_service_arr['team_user_link_id'];
									$teamUserLinkDetails = $TeamUserLinks->teamUserLinkDetails($team_user_link_id);
									if(isset($teamUserLinkDetails) && !empty($teamUserLinkDetails))
										$current_lead = $teamUserLinkDetails['current_lead']+1;
										$TeamUserLinks->updateAll(['current_lead'=>$current_lead],['id'=>$team_user_link_id]);

								}
								//

								//send system notification
									$recipient_id = 0;
									if(isset($service->user_id) && !empty($service->user_id)){
										$recipient_id = $service->user_id;
									}
									if(isset($recipient_id) && !empty($recipient_id))
									{
										$notification_type_id = _ID_NOTIFICATION_NEW_CLAIM_REQUEST_ASSIGN;
										$sendNotification = CommonFunctions::sendNotification($login_agency_id,$recipient_id, null, $notification_type_id);
									}
									//

									// bell notification
									$contactCommuniction = $ContactCommunications->newEntity();
									$communication = [];
									$communication['service_notification_id'] = $service->id;
									$communication['user_id'] = $assign_bell_owner_id;
									$communication['agency_id'] = $login_agency_id;
									$communication['contact_id'] = $objectData['contact_id'];
									$communication['communication_type'] = _ID_SUCCESS;
									$communication['in_out'] = _ID_SUCCESS;
									$communication['read_status'] = _SERVICE_READ_STATUS_BELL_NOTTIFICATION;
									$communication['mail_subject'] = 'Service Ticket';
									$communication['message'] = 'A new unassigned service opportunity has been created. Click here to view';
									$contactCommuniction = $ContactCommunications->patchEntity($contactCommuniction, $communication);
									$ContactCommunications->save($contactCommuniction);

									// Add Attachments for service/contact/policy
									isset($objectData['service_attachments']) && !empty($objectData['service_attachments'][0]['name']) ? $this->uploadServiceAttachments(['objectData'=>$objectData,'service'=>$service]) : '';
									$response = json_encode(array('status' => _ID_SUCCESS,'message'=>'Record saved successfully'));
								}else{
									echo json_encode(array('status' => _ID_FAILED));die;
								}
							}
						}else{
						echo json_encode(array('status' => _ID_FAILED));
							die;
						}
				}

				return $response;
				
                
            }
		}catch (\Exception $e) {
			
            $txt=date('Y-m-d H:i:s').' :: Secondary contact Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
	}



	/*****************************
	 * DESC : Service/Policy/contact Attachments
	 * CREATED BY : Nishant
	 * CREATED On : 10-11-2022
	*****************************/

	public function uploadServiceAttachments($objectData){
		if(isset($objectData['objectData']['service_attachments']) && !empty($objectData['objectData']['service_attachments'])){

            $this->loadComponent('Aws');
            $this->Aws->bucket = AWS_BUCKET_NAME;
			$uploadedFiles = [];
			$nonUploadedFiles = [];
			$user_id = $this->request->session()->read('Auth.User.user_id');
            $login_agency_id=$this->request->session()->read('Auth.User.agency_id');
			  if(isset($objectData['service']['contact_business_id']) && !empty($objectData['service']['contact_business_id']))
				{
					$business_id = $objectData['service']['contact_business_id'];
					$service_folder_name = _SERVICE_BUSINESS_TICKET_FOLDER_NAME;
					$service_sub_folder = _SERVICE_BUSINESS_TICKET_SUBFOLDER_NAME;
					$contact_folder_name = _CONTACT_BUSINESS_FOLDER_NAME;
					$contact_file_name = _CONTACT_BUSINESS_FILE_NAME;
				}
				else
				{
					$service_folder_name = _SERVICE_TICKET_FOLDER_NAME;
					$service_sub_folder = _SERVICE_TICKET_SUBFOLDER_NAME;
					$contact_folder_name = _CONTACT_FOLDER_NAME;
					$contact_file_name = _CONTACT_FILE_NAME;
				}

				foreach($objectData['objectData']['service_attachments'] as $key => $value)

				{
					$fileSize=$value['size'];
					$ext = substr(strtolower(strrchr($value['name'], '.')), 1);
					$service_file_name = strtolower($service_sub_folder.$user_id.'_attach_'.date('Y-m-d').'_'.time().'_'.$value['name']);
					$service_display_name = strtolower($value['name']);
					$service_file_name=str_replace(" ", "_", $service_file_name);
					if(in_array($ext,_EXTENSIONS_ALLOWED))
					//if($ext == 'pdf' || $ext == 'xls' || $ext == 'jpeg' || $ext == 'png' || $ext == 'doc' || $ext == 'docx' || $ext == 'jpg' || $ext == 'eml')
					{

						if($fileSize <= 64000000){

							$dir_name =$service_sub_folder.$user_id;

							if (!is_dir(WWW_ROOT . 'uploads/'.$service_folder_name))
							{
								mkdir('uploads/'.$service_folder_name);
								chmod('uploads/'.$service_folder_name, 0777);
								mkdir('uploads/'.$service_folder_name.'/'.$dir_name);
								chmod('uploads/'.$service_folder_name.'/'.$dir_name, 0777);
							}elseif(!file_exists(WWW_ROOT . 'uploads/'.$service_folder_name.'/'.$dir_name)){
								mkdir('uploads/'.$service_folder_name.'/'.$dir_name);
								chmod('uploads/'.$service_folder_name.'/'.$dir_name, 0777);

							}

							$service_file_path_to_upload = WWW_ROOT .'uploads/'.$service_folder_name.$service_sub_folder.$user_id.'/' . $service_file_name;
							$serviceFileMoveRes = move_uploaded_file($value['tmp_name'], $service_file_path_to_upload);
                            
                            $keyname = $login_agency_id . '/' . $user_id;

							$bucketdata=['bucket'=>$this->Aws->bucket,
							'keyname'=>$keyname,
							'filepath'=>$service_file_path_to_upload,
							'filename'=> $service_file_name];
							$fileMoveResUrl = $this->Aws->uploadFile($bucketdata);
                            $file_aws_key = $keyname . '/' . $service_file_name;
							$file_url = $fileMoveResUrl;
							if(isset($fileMoveResUrl) && !empty($fileMoveResUrl))
							{ 
                                

                                // $delete_sub_folder = WWW_ROOT .'uploads/'.$service_folder_name.$dir_name;
                                // array_map('unlink', glob("$delete_sub_folder/*.*"));
                                // rmdir($delete_sub_folder);
                               // array_map('unlink', glob("$delete_sub_folder/*.*"));
								$attachemntInfo=$serviceAttachmentInfo=[];
								$attachemntInfo['contact_business_id']= isset($business_id) && !empty($business_id)? $business_id : null;
								$attachemntInfo['contact_id'] = $objectData['objectData']['contact_id'];
								$attachemntInfo['display_name'] = $service_display_name;
								$attachemntInfo['user_id'] = $user_id;
								$attachemntInfo['status'] = _ID_STATUS_ACTIVE;
								$attachemntInfo['file_size'] = $fileSize;
                                $attachemntInfo['file_url'] = $file_url;
								$attachemntInfo['file_aws_key'] = $file_aws_key;
								$serviceAttachmentInfo = $attachemntInfo;
								$serviceAttachmentInfo['name'] = $service_file_name;
								$serviceAttachmentInfo['service_id'] = $objectData['service']['id'];
								//$serviceAttachmentInfo['policy_id'] = isset($objectData['opportunity_id']) && !empty($objectData['opportunity_id']) ?$objectData['opportunity_id'] : '';
								// save service attachments
								$uploadfile = $this->ServiceAttachments->newEntity();
								$uploadfile = $this->ServiceAttachments->patchEntity($uploadfile, $serviceAttachmentInfo);

								if($servicesAttachments = $this->ServiceAttachments->save($uploadfile)) {

									if((isset($objectData['objectData']['contact_id']) && !empty($objectData['objectData']['contact_id'])) || (isset($objectData['objectData']['contact_business_id']) && !empty($objectData['objectData']['contact_business_id']))){
										// save contact attachments
                                        if(isset($objectData['contact_business_id']) && !empty($objectData['contact_business_id']))
                                        {
                                            $contact_dir_name =$contact_folder_name."_".$objectData['contact_business_id'];
                                            $contactFileName = str_replace($service_sub_folder.$servicesAttachments['user_id'],$contact_file_name.$objectData['contact_business_id'],$service_file_name);
                                            $contact_file_path_to_upload =  WWW_ROOT  .'uploads/'.$contact_folder_name.'_'.$objectData['contact_business_id'].'/' . $contactFileName;
                                        }
                                        else
                                        {
                                            $contact_dir_name =$contact_folder_name."_".$objectData['objectData']['contact_id'];
                                            $contactFileName = str_replace($service_sub_folder.$servicesAttachments['user_id'],$contact_file_name.$objectData['objectData']['contact_id'],$service_file_name);
                                            $contact_file_path_to_upload =  WWW_ROOT  .'uploads/'.$contact_folder_name.'_'.$objectData['objectData']['contact_id'].'/' . $contactFileName;
                                        }

										$contact_display_name = $service_display_name;
										//$contactFileName=str_replace(" ", "_", $contactFileName);

										if (!is_dir('uploads/'.$contact_dir_name))
										{
											mkdir('uploads/'.$contact_dir_name);
											chmod('uploads/'.$contact_dir_name, 0777);
										}else{
											chmod('uploads/'.$contact_dir_name, 0777);
										}



										if(file_exists($service_file_path_to_upload)){
											$contactFileMoveRes = copy( $service_file_path_to_upload, $contact_file_path_to_upload);
                                            $file_name = strtolower('contact_' . $contact_id . '_attach_' . date('Y-m-d') . '_' .time() . '_' . $value['name']);
								
										    $file_name=str_replace(" ", "_", $file_name);
								
                                            $keyname = $login_agency_id . '/' . $contact_id;
                                            $contactbucketdata=['bucket'=>$this->Aws->bucket,
                                            'keyname'=>$keyname,
                                            'filepath'=>$service_file_path_to_upload,
                                            'filename'=> $file_name];

										    $contactfileMoveResUrl = $this->Aws->uploadFile($contactbucketdata);

										}
										if(isset($contactfileMoveResUrl) && !empty($contactfileMoveResUrl))
										{ 
											$contactAttachemntInfo=[];
											$contactAttachemntInfo = $attachemntInfo;
											$contactAttachemntInfo['name']=$contactFileName;
											// save contacts attachment
											$contactUploadfile = $this->ContactAttachments->newEntity();
											$contactUploadfile = $this->ContactAttachments->patchEntity($contactUploadfile, $contactAttachemntInfo);

											if($contactAttachment = $this->ContactAttachments->save($contactUploadfile)){
                                                unlink($service_file_path_to_upload);
                                                unlink($contact_file_path_to_upload);
												if(isset($objectData['objectData']['opportunity_id']) && !empty($objectData['objectData']['opportunity_id'])){
													$policyAttachemntInfo=[];
													$policyAttachemntInfo = $attachemntInfo;
													$policyAttachemntInfo['name']=$contactFileName;
													$policyAttachemntInfo['attachment_id'] = $contactAttachment->id;
													$policyAttachemntInfo['policy_id'] = isset($objectData['objectData']['opportunity_id'])&& !empty($objectData['objectData']['opportunity_id']) ? $objectData['objectData']['opportunity_id'] : '';

													// Save policy Attachments
													$policyUploadfile = $this->ContactPolicyAttachments->newEntity();
													$policyUploadfile = $this->ContactPolicyAttachments->patchEntity($policyUploadfile, $policyAttachemntInfo);
													$this->ContactPolicyAttachments->save($policyUploadfile);
												}

											}
										}
								    }
								}
							}
						}


					}


				}
		}else{
			 echo json_encode(array('status' => _ID_FAILED));die;

		}

	}
	public function getAllServiceTypes($contactId){
		try
		{
			$myfile = fopen(ROOT."/logs/vueOverviewListing.log", "a") or die("Unable to open file!"); 
            $return = [
                'Service' => []
            ];
            $session = Router::getRequest()->getSession(); 
            $login_user_id= $session->read("Auth.User.user_id");
            $login_agency_id= $session->read("Auth.User.agency_id");
            $ServiceRequestTypes = TableRegistry::getTableLocator()->get('ServiceRequestTypes');
			$ServiceRequestTypesListing =  $ServiceRequestTypes->getAllServiceTypes();
            $i = 0;
            foreach($ServiceRequestTypesListing as $services){
                $data['ServiceType'][$i]['id'] = $services['id'];
                $data['ServiceType'][$i]['name'] = $services['name'];
                $i++;
            }
            $return['Services.getAllServiceTypes'] = [
                $contactId => $data['ServiceType']
            ];
            return $return;
        }catch (\Exception $e) {
			
            $txt=date('Y-m-d H:i:s').' :: Overview Lising Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }
	}
}