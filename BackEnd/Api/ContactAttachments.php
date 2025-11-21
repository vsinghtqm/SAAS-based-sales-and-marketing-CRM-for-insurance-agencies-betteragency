<?php

namespace App\Lib\ApiProviders;

use App\Lib\PermissionsCheck;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Http\Session;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router; 
use App\Controller\Component\CommonComponent;
use App\Classes\Aws;

class ContactAttachments

{

	public static function getAttachments($objectData)
    {
        try
		{
			$myfile = fopen(ROOT."/logs/vueAttachment.log", "a") or die("Unable to open file!"); 

            $awsBucket = AWS_BUCKET_NAME;
            $ContactAdditionalInsuredPolicyRelationTable = TableRegistry::getTableLocator()->get('ContactAdditionalInsuredPolicyRelation');
            $ContactAttachmentsTable = TableRegistry::getTableLocator()->get('ContactAttachments');
            $UsersTable = TableRegistry::getTableLocator()->get('Users');
            $session = Router::getRequest()->getSession(); 
            $login_agency_id= $session->read("Auth.User.agency_id");
            $folder_name="";
            $id="";
            $contact_id="";
            $list = [];
            $response  = [];
            $contactId = $objectData['contactId'];
            if(!empty($contactId)) {
                $folder_name = "contact";
                $contact_id = $contactId;
                $id = $contact_id;
                $getprimaryContact = $ContactAdditionalInsuredPolicyRelationTable->find('all')->select(['contact_id'])->where(['ContactAdditionalInsuredPolicyRelation.additional_insured_contact_id'=>$contact_id,'ContactAdditionalInsuredPolicyRelation.status'=>_ID_STATUS_ACTIVE])->hydrate(false)->first();
                if(!empty($getprimaryContact['contact_id']) && isset($getprimaryContact['contact_id'])){               
                    $contact_id = $getprimaryContact['contact_id'];
                }
                // dd($contact_id);
                $getScondryContact = $ContactAdditionalInsuredPolicyRelationTable->find('all')->select(['additional_insured_contact_id'])->where(['ContactAdditionalInsuredPolicyRelation.contact_id'=>$contact_id,'ContactAdditionalInsuredPolicyRelation.status'=>_ID_STATUS_ACTIVE])->hydrate(false)->toArray();
                if(isset($getScondryContact)&&!empty($getScondryContact)){
                    $secondaryIds = array_column($getScondryContact,'additional_insured_contact_id');
                    array_push($secondaryIds,$contact_id);
                    $contactids = $secondaryIds;
                }
                if(!empty($contactids)){
                    $attachment_arr  = $ContactAttachmentsTable->find('all')->where(['ContactAttachments.contact_id IN' => $contactids,'ContactAttachments.status'=>_ID_STATUS_ACTIVE,'ContactAttachments.contact_business_id is null'])->order(['ContactAttachments.created' => 'desc'])->hydrate(false)->toarray();
                                
                }else{
                    $attachment_arr  = $ContactAttachmentsTable->find('all')->where(['ContactAttachments.contact_id' => $contact_id,'ContactAttachments.status'=>_ID_STATUS_ACTIVE,'ContactAttachments.contact_business_id is null'])->order(['ContactAttachments.created' => 'desc'])->hydrate(false)->toarray();
                } 
            }
        
            $attachment_list = '';
            $attach_id= '';
            $count = 0;
            $finalResponse =[];
            if(isset($attachment_arr) && !empty($attachment_arr))
            {
                //$contact_email_attachment_list = $contact['contact_attachments'];
                foreach ($attachment_arr as $key => $value)
                {
                    
                    $file = '';
                    $folder_name = "contact";
                    $attach_id = $id;
                    $attachmentGuid = $value['attachment_guid'];

                
                    $ext = substr(strtolower(strrchr($value['name'], '.')), 1);
                    $file_path_basic = WWW_ROOT.'uploads/'.$folder_name.'_'.$attach_id .'/'. $value['name'];
                    $file_path_default = WWW_ROOT.'uploads/'. $value['name'];
                    $download_link = '';
                    if(isset($value['file_aws_key']) && !empty($value['file_aws_key']))
                    {

                        // if(Aws::awsFileExists($value['file_aws_key']))
                        // { 
                            $file = $value['file_url'];
                            //temporary link set for downloads
                            $bucketdata=['bucket'=>$awsBucket,
                            'keyname'=>$value['file_aws_key']];
                            $download_link = (string)Aws::setPreAssignedUrl($bucketdata);
                        // }
                        $download_link = $download_link;

                    }
                    else if(file_exists($file_path_basic))
                    {
                        //echo "<pre>"; print_r();
                    $file = SITEURL.'uploads/'.$folder_name.'_'.$attach_id .'/'. $value['name'];
                    
                    
                    }elseif(file_exists($file_path_default)) {
                        
                    $file = SITEURL.'uploads/'. $value['name'];
                    }
                    if(empty($download_link)){
                        $download_link = $file;
                    }
                    $fileSize = $value['file_size'];
                    if(isset($fileSize) && !empty($fileSize)){
                        $file_size_covert = CommonComponent::convert_filesize($fileSize);    
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
                    $userDetailsForAttachments = $UsersTable->userDetails($attachment_user_id);

                    $attachement_user_name = '';
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
                        $final_display_name = strlen($display_name) > 30 ? substr($display_name,0,30)."..." : $display_name;
                    }else{
                        $display_name = $value['name'];
                        $final_display_name = strlen($display_name) > 30 ? substr($display_name,0,30)."..." : $display_name;
                    }
                    $fromSection = "'fromAttachentSection'";
                    if($value['status'] == _ID_STATUS_ACTIVE)
                    {
                        $file_url = SITEURL.'attachments/previewAttachments?url='.$download_link;
                        $action = '';                  
                        $action = ['download_link'=>$download_link,'file_url'=>$file_url];                                        
                        $response1 = [
                            'id'=>$value['id'],
                            'contact_id' =>$value['contact_id'],
                            'file' => $display_name,
                            'user' => ucfirst($attachement_user_name) ,
                            'date_added' => $created_file_upload,
                            'action' => $action,
                            //'action' => ['<a>Attach</a>','Download', 'View', 'Delete'],
                            'link' => 'google.com',
							'download_link' => $download_link,
							'file_url' => $file_url,
							'id' => $value['id'],
                            'contact_id' => $value['contact_id'],
                            'attachment_guid' => $attachmentGuid

                        ];
                        
                        array_push($finalResponse,$response1);
                        
                        
                        
                    }
                    $count++;
                }
                
                
            }
            else
            {
                
            }
            
            $return['ContactAttachments.getAttachments'] = [
                $contactId => $finalResponse
            ];
        
            return $return;
        }catch (\Exception $e) {
			
            $txt=date('Y-m-d H:i:s').' :: Attachment Lising Error- '.$e->getMessage();
			fwrite($myfile,$txt.PHP_EOL);
        }

	}
	public function deleteAttachments($attchmentId)
    {
		try
        {
            $myfile = fopen(ROOT."/logs/attachment_error.log", "a") or die("Unable to open file!"); 
			$session = Router::getRequest()->getSession(); 
			$login_agency_id = $session->read("Auth.User.agency_id");
			$login_user_id = $session->read('Auth.User.user_id');
			$ContactAttachments = TableRegistry::getTableLocator()->get('ContactAttachments');
			$attachmentDetails = $ContactAttachments->get($attchmentId);
			if(isset($attachmentDetails) && !empty($attachmentDetails))
			{
				if($ContactAttachments->updateAll(['status'=>_ID_STATUS_DELETED],['id' => $attchmentId]))
				{
					$file_pointer = WWW_ROOT.'uploads/'.$attachmentDetails['name'];
					$response = json_encode(array('status' => _ID_SUCCESS));
				}
				else
				{
					$response = json_encode(array('status' => _ID_FAILED));
				}
			}
		}catch (\Exception $e) {
			
				$txt=date('Y-m-d H:i:s').' :: Attachment not deleted with attchment id- '.$attchmentId. $e->getMessage();
				fwrite($myfile,$txt.PHP_EOL);
		}
		return $response;
    }
     // Rename attachment from VueJs
    public static function updateAttachment($postData)
    {
        try
        {
            $myfile = fopen(ROOT."/logs/updateAttachment.log", "a") or die("Unable to open file!"); 
            if(!empty($postData['attachement_id']) && !empty($postData['name'])){
                $attachement_id=$postData['attachement_id'];
                $name=addslashes($postData['name']);
                $contactAttachment = TableRegistry::getTableLocator()->get('ContactAttachments');
                $contactData = TableRegistry::getTableLocator()->get('ContactAttachments')->find('all')->where(['id' => $postData['attachement_id']])->first();
                //check file name has extenstion or not, if not then add it
                if (!preg_match('/(\.jpg|\.png|\.bmp|\.pdf|\.xls|\.jpeg|\.doc|\.docx|\.eml|\.pst|\.ost|\.wav|\.mp3|\.mp4|\.m4a|\.xlsx)$/i', strtolower($name))) {
                    if(isset($contactData['file_url']) && $contactData['file_url'] != '')
                    {
                        $fileInfo = pathinfo($contactData['file_url']);
                        if(isset($fileInfo['extension']) && $fileInfo['extension'] != '')
                        {
                            $name = $name . '.' .strtolower($fileInfo['extension']);
                        }
                    }
                    
                } 
                //
                $updateArray=array();
                $updateArray['display_name']=$name;
                $ContactAttachments = $contactAttachment->patchEntity($contactData, $updateArray);
                $finalResponse =[];           
                if($contactAttachment->save($ContactAttachments)){               
                    $response1 = [
                        'status'=>_ID_SUCCESS,  
                        'contactAttachmen' =>$contactAttachment,                      
                    ];  
                } else {
                    $response1 = [
                        'status'=>_ID_FAILED,
                        'contactAttachmen' =>$contactAttachment,         
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
	
}



?>