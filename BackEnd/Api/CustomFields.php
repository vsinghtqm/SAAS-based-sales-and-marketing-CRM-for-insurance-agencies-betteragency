<?php

namespace App\Lib\ApiProviders;
use App\Model\Entity\User;
use Cake\ORM\TableRegistry;
use App\Lib\PermissionsCheck;
use Cake\ORM\Locator\LocatorAwareTrait;
use App\Lib\QuickTables\ContactsQuickTable;
use Cake\Http\Exception\UnauthorizedException;
use App\Classes\CommonFunctions;
use Cake\Routing\Router;

class CustomFields
{

  
    public static function getContactCustomFieldsListing($contactId)
    {
        $response = [];
        try
		{

            $session = Router::getRequest()->getSession(); 
            $login_agency_id = $session->read('Auth.User.agency_id');
            $contact_id = $contactId;
            $list="";
            $ContactCustomFields = TableRegistry::getTableLocator()->get('ContactCustomFields');
            $contactCustomFieldsList = $ContactCustomFields->getAllCustomFieldsByContact($contact_id);
            $sortedArray = [];
            usort($contactCustomFieldsList, function ($fieldLabel1, $fieldLabel2) {
                $field1=  str_replace(' ', '_', strtolower($fieldLabel1['field_label']));
                $field2=  str_replace(' ', '_', strtolower($fieldLabel2['field_label']));
                 if ($field1 == $field2) return 0;
                 return $field1 < $field2 ? -1 : 1;
             });
           
            if(isset($contactCustomFieldsList) && !empty($contactCustomFieldsList))
            {
                
           
                // foreach ($contact_custom_fields as $custom_fields)
                // {

                //     if($custom_fields['status'] == _ID_STATUS_ACTIVE)
                //     {
                //         $field_count = $field_count+1;
                //         $field_label = $custom_fields['field_label'];
                //         $custom_field_value = $custom_fields['field_value'];
                //         if(empty($custom_field_value)){
                //             $custom_field_value = "";
                //         }
                //         $custom_field_id = $custom_fields['id'];
                //         if (strpos($field_label, ' ') !== false)
                //         {
                //             $custom_field_label=str_replace(" ", "_", $field_label);
                //         }
                //         else
                //         {
                //             $custom_field_label=$field_label;
                //         }
                //         $resultLabel = str_replace("_", " ", $field_label);
                //         $dataId = 'contact_view_'.$custom_field_label;
                //         $dataInput = 'contact_text_'.$custom_field_label;
                //         $class = "";
                //         $css = "";

                //         $list .='<div class="row  m-t-10">
                //             <div class="col-md-5">
                //                 '.ucfirst($resultLabel).'
                //             </div>
                //             <div class="col-md-5">
                //                 <input type="text" name="cus_field['.$custom_field_id.']" required="required" id="'.$dataInput.'" value="'.$custom_field_value.'"  class="input_text form-control">
                //             </div>
                //             <div class="col-md-2 p-l-0">
                //                 <button type="button" class="btn-outline-warning delete-custom-field" id="delete_'.$dataId.'"><i class="fa fa-trash" aria-hidden="true" style="cursor:pointer;" onclick=deleteCustomField("'.$contact_id.'","'.$custom_field_id.'","'.$custom_field_label.'")></i></button>
                //             </div>
                //         </div>';

                //     }
                // }
            }
            
            $response = json_encode(array('status' => _ID_SUCCESS,'list'=>  $contactCustomFieldsList));
        }catch (\Exception $e) {
			
            $txt=date('Y-m-d H:i:s').' :: taskLising Error- '.$e->getMessage();
			$response = json_encode(array('status' => _ID_FAILED,'list'=> '','error'=>$txt));
        }

        $return['CustomFields.getContactCustomFieldsListing'] = [
            $contactId => $response
        ];
        return $return;
    }

    public static function checkCustomFieldExistOrNot($objectData)
    {

        $response = [];
        $count = 0;
        try
		{

            $session = Router::getRequest()->getSession(); 
            $login_agency_id = $session->read('Auth.User.agency_id');
            $contact_id = $objectData['contact_id'];
            $list="";
            $ContactCustomFields = TableRegistry::getTableLocator()->get('ContactCustomFields');
            $AgencyCustomFields = TableRegistry::getTableLocator()->get('AgencyCustomFields');
            
            if($objectData['applied_for_all_contacts'] == _ID_FAILED)
            {
                $contactCustomFieldsList = $ContactCustomFields->checkContactCustomFieldExistForContact($objectData);
            }
            else
            {
                $contactCustomFieldsList = $AgencyCustomFields->checkAgencyCustomFieldExistOrNot($objectData,$login_agency_id, _PERSONAL_CONTACT);
            }
            if(isset($contactCustomFieldsList) && !empty($contactCustomFieldsList))
            {
               $count = 1;
            }
             $response = json_encode(array('status' => _ID_SUCCESS,'count'=>   $count));
        }catch (\Exception $e) {
			
            $txt=date('Y-m-d H:i:s').' :: taskLising Error- '.$e->getMessage();
			$response = json_encode(array('status' => _ID_FAILED,'list'=> '','error'=>$txt));
        }

        return $response;
    }

   
}