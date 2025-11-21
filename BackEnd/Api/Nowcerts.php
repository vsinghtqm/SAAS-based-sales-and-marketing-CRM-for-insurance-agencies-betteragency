<?php

namespace App\Lib\ApiProviders;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use App\Classes\LoginModel;

class Nowcerts
{
    public static function generateCode()
    {
        header("Content-Type: text/plain");
        $session = Router::getRequest()->getSession();
        $loginUserId = $session->read("Auth.User.user_id");
        $loginAgencyId = $session->read("Auth.User.agency_id");
        // Create a new instance of LoginModel and set its properties
        $loginModel = new LoginModel();
        // Current date and time in UTC
        $loginModel->createDate = gmdate("Y-m-d\TH:i:s\Z");
        // Your user code
        //$loginModel->userCode = 'production-1';// Your agency code
        //$loginModel->agencyCode = 'production-961';
        $loginModel->userCode = 'production-'.$loginUserId;// Your agency code
        $loginModel->agencyCode = 'production-'.$loginAgencyId;
        //$loginModel->userCode = 'production-6103';// Your agency code
        //$loginModel->agencyCode = 'production-1561';
        // Convert the model to json string
        $rawData = json_encode($loginModel);
        //echo $rawData;
        // Your public key for encryption
        $rawPublicKey = _NOWCERTS_PUBLIC_KEY;
        $publicKey = "-----BEGIN PUBLIC KEY-----\n" . $rawPublicKey . "\n-----END PUBLIC KEY-----";
        // Encrypt the json string using the public key
        if (openssl_public_encrypt($rawData, $encryptedData, $publicKey)) {
            // Combine the public key and the encrypted data to generated the final code
            $code = $rawPublicKey . base64_encode($encryptedData);
        }
        return $code;
    }

    public function nowcertsExternalLogin($objectData)
    {
        $contactId = '';
        $opportunityId = '';
        $nowcertPolicyDatabaseId = '';
        $insuredDatabaseId = '';
        $returnUrl = '';
        if(!empty($objectData))
        {
            if(!empty($objectData['contact_id']))
            {
                $contactId = $objectData['contact_id'];
            }
            if(!empty($objectData['contact_opportunity_id']))
            {
                $opportunityId = $objectData['contact_opportunity_id'];
            }
            if(!empty($objectData['insured_database_id']))
            {
                $insuredDatabaseId = $objectData['insured_database_id'];
            }
            if(!empty($objectData['nowcert_policy_database_id']))
            {
                $nowcertPolicyDatabaseId = $objectData['nowcert_policy_database_id'];
            }
        }
        if(empty($opportunityId) && !empty($contactId) && !empty($insuredDatabaseId))
        {
            $returnUrl = '/AMSINS/Insureds/Details/'.$insuredDatabaseId.'/Information';
        }
        else if(!empty($opportunityId) && !empty($nowcertPolicyDatabaseId))
        {
            $returnUrl = '/AMSINS/Policies/Details/'.$nowcertPolicyDatabaseId.'/Information?part=0';
        } else
        {
            $returnUrl = '/AMSINS/Insureds/List';
        }
        if($objectData['type'] == 'overviewAndCoverages'){
            $returnUrl = '/AMSINS/Policies/Details/'.$nowcertPolicyDatabaseId.'/Information?part=0';
        }
        else if($objectData['type'] == 'acordForms'){
            $returnUrl = '/AMSINS/Policies/Details/'.$nowcertPolicyDatabaseId.'/PdfForms';
        }
        else if($objectData['type'] == 'certificates'){
           $returnUrl = 'CertificateHolders/Details.aspx?TruckingCompanyId='.$insuredDatabaseId.'&PolicyId='.$nowcertPolicyDatabaseId.'&Return=Details';
        }
        else if($objectData['type'] == 'billingOverview'){
            $returnUrl = '/AMSINS/Policies/Details/'.$nowcertPolicyDatabaseId.'/Information?part=1';
        }
        else if($objectData['type'] == 'endorsementsFreesAndTaxes'){
            $returnUrl = '/AMSINS/Policies/Details/'.$nowcertPolicyDatabaseId.'/Endorsements';
        }
        else if($objectData['type'] == 'agencyCommissions'){
            $returnUrl = '/AMSINS/Policies/Details/'.$nowcertPolicyDatabaseId.'/EndorsementAgencyCommissions';
        }
        else if($objectData['type'] == 'invoicesReceiptsAndPayments'){
            $returnUrl = '/AMSINS/Policies/Details/'.$nowcertPolicyDatabaseId.'/InvoicesReceipts';
        }
        

        $code = Nowcerts::generateCode();
        $curl = curl_init();
        $url = _NOWCERTS_EXTERNAL_LOGIN_API . '?returnUrl=' . urlencode($returnUrl);
        $response = json_encode(array('status' => _ID_SUCCESS,'code' => $code,'url'=>$url));
        return $response;
	    die();
    }
}
