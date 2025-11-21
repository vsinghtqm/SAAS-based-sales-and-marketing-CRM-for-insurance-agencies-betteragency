<?php

namespace App\Lib\ApiProviders;

use App\Lib\EmailTools\EmailTools;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use App\Controller\Component\CommonComponent;
use App\Classes\Aws;
use App\Controller\UsersController;
use App\Controller\ContactsController;
use App\Classes\LegacyTokenExchange;


class EmailSender
{
	public static function sendEmail($objectData)
	{
		try{

			$emailSent = false;
			$message = '';
			$entity = [];
			//make sure they can email the person
			$myfile = fopen(ROOT."/logs/new_email_sender_error.log", "a") or die("Unable to open file!");
			$session = Router::getRequest()->getSession();

			$user = $session->read("Auth.User");

			//get the agency info from session
			$agencyTable = TableRegistry::getTableLocator()->get('Agency');
			$agency = $agencyTable->find()->where(['id' => $user['agency_id']])->first();

			//turn the entity value into an array p-12345 or c-12345
			//index 0 is the entity type (p = personal, c = commercial)
			//index 1 is the entity id
			$entity = explode('-', $objectData['entity']);
			if(count($entity) === 2){
				if($entity[0] === "p"){
					//handle personal contacts
					//EmailTools::writeToLog('Sending email to personal contact id ' . $entity[1]);

					$emailReadyToSend = true;

					//get the contact details
					$contactsTable = TableRegistry::getTableLocator()->get('Contacts');
					$contact = $contactsTable->find()->where(['id' => $entity[1]])->first();
					//if there is a contact, get the contact details
					if($contact) {
						$contactDetailsTable = TableRegistry::getTableLocator()->get('ContactDetails');
						$contactDetails = $contactDetailsTable->find()->where(['contact_id' => $entity[1]])->first();
						// we are commenting this because there are chances that many contacts does not have entry in contact_details table this case fails

						/*if ($contactDetails) {
							//EmailTools::writeToLog('Found contact details for contact id ' . $contact->id);
						} else {
							$emailReadyToSend = false;
							$message = 'Contact details not found';
							//EmailTools::writeToLog('Personal contact details for contact id ' . $entity[1] . 'not found');
						}*/
					}
					else{
						$emailReadyToSend = false;
						$message = 'Contact not found';
						//EmailTools::writeToLog('Personal contact id ' . $entity[1] . 'not found');
					}

					//make sure the user has permission to send to this contact
					if (EmailTools::userHasPermission($user, $contact)) {
						//EmailTools::writeToLog('User ' . $user->id . 'has permission to email contact id ' . $entity[1]);
					}
					else {
						$emailReadyToSend = false;
						$message = 'User does not have permission to email contact';
						//EmailTools::writeToLog('User ' . $user->id . ' does not have permission to email contact  ' . $entity[1]);
					}

					if($emailReadyToSend){
						//handle merging any merge fields in the email body
						$mergedEmailArr = EmailTools::mergeEmail($objectData, $contact, $contactDetails, $user, $agency);
						$mergedEmailArr = json_decode($mergedEmailArr,true);
                        $mergedEmailSubject = $mergedEmailArr['mergedEmailSubject'];
                        $mergedEmailBody = $mergedEmailArr['mergedEmailBody'];

						// $userTokensTable = TableRegistry::getTableLocator()->get('UserTokens');
						// $userToken = $userTokensTable->find()->where(['user_id' => $user['id'], 'status' => _ID_STATUS_ACTIVE])->first();
						// if (isset($userToken->nylas_status) && $userToken->nylas_status === _NYLAS_STATUS_NEEDS_REAUTHENTICATION) {
						// 	$baseUrl = rtrim(\Cake\Core\Configure::read('api_host', ''), '/');
						// 	$nylasConnectUrl = $baseUrl . "/v1/mailboxes/oauth-flow?token=" . LegacyTokenExchange::getTokenForUser($user['id']) . "&email=" . $user['email'];
						// 	$message =  'Your email integration is disconnected. Click here to reconnect and avoid disruptions.';
						// 	return (['status' => 530, 'message' => $message, 'data' => $objectData, 'contact' => $contact, 'contactDetails' => $contactDetails, 'user' => $user, 'agency' => $agency,'nylasConnectUrl'=> $nylasConnectUrl]);
						// }
						//determine email method (sendgrid or nylas)
						$emailMethod = EmailTools::determineEmailMethod($user);

						$message = [
							'toEmails' => $objectData['mail_to'],
							'ccEmails' => $objectData['mail_cc'],
							'bccEmails' => $objectData['mail_bcc'],
							'subject' => $mergedEmailSubject,
							'emailBody' => $mergedEmailBody,
							'attachments' => $objectData['attachments'],
							'emailMethod' => $emailMethod
						];
                        $userInfo = "Contact id is ". $contact['id'] ." agency id is ". $agency['id']. " and user id is:- ". $user['id'];
						switch ($emailMethod) {
							case 'nylas':
								//send email via nylas
								EmailTools::writeToLog('Sending email via nylas');
								EmailTools::sendEmailNylas($message, $contact, $agency, $user);
								$emailSent = true;
								$message = 'Email successfully sent via email sync';
								break;
							case 'sendgrid':
								//code goes here
								EmailTools::sendEmailSendgrid($message, $contact, $agency, $user);
								$emailSent = true;
								$message = 'Email successfully sent via bulk email';
								break;
							default:
								$message = 'Invalid email method';
								EmailTools::writeToLog('Invalid email method');
								break;
						}

						EmailTools::writeToLog('Email Message ' . json_encode($message));

					}
					else {
						$message = 'User does not have permission to email contact';
						EmailTools::writeToLog('User ' . $user['id'] . ' does not have permission to email contact  ' . $entity[1]);
					}
				}
				elseif($entity[0] === 'c'){
					//handle commercial contacts
					$message = 'Commercial contacts not yet supported';
				}
				else{
					$message =  'Invalid entity type passed';
				}
			}
			else {
				$message = 'Invalid entity information passed';
			}
		}
		catch (\Exception $e) {
			// Log error
			EmailTools::writeToLog('Error Sending Email - '.$e->getMessage()." : line number : ".$e->getLine());
			$message = $e;
		}
		return (['status' => $emailSent, 'message' => $message, 'data' => $objectData, 'contact' => $contact, 'contactDetails' => $contactDetails, 'user' => $user, 'agency' => $agency]);
	}



}