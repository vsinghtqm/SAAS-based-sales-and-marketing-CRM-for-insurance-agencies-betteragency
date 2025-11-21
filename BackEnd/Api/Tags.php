<?php

namespace App\Lib\ApiProviders;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use App\Classes\CommonFunctions;
use App\Lib\NowCerts\NowCertsApi;


class Tags
{
	/*
    *This funciton is used to get the tag list
    */
    public function getContactTags($contactId)
    {
        try
        { 
            $tagApplied = TableRegistry::getTableLocator()->get('TagApplied');
			$tagList = [];
			if(!empty($contactId))
			{
				$contactAppliedTags = $tagApplied->assignedTagsList($contactId);
				if(isset($contactAppliedTags) && !empty($contactAppliedTags)){
					if(!empty($tagList))
					{
						$counter = 1;
					}
					else
					{
						$counter = 0;
					}
					foreach ($contactAppliedTags as $contactTag) 
					{
						$tagList[$counter]['id'] = $contactTag['id'];
						$tagList[$counter]['tag_id'] = $contactTag['tag_id'];
						$tagList[$counter]['tag_name'] = $contactTag['tag']['name'];
						$counter++;
					}
				}
			}
			$return['Tags.getContactTags'] = [
                $contactId => $tagList
            ];
            return $return;
        }
        catch (\Exception $e) 
        {
			
         
        }
    }

    /*
    *This funciton is used to delete contact tag
    */
    public function deleteContactTag($tagAppliedId)
    {
        try
        { 
            $tagApplied = TableRegistry::getTableLocator()->get('TagApplied');
            $contacts = TableRegistry::getTableLocator()->get('Contacts');
			$tagList = [];
			if(!empty($tagAppliedId))
			{
				$tagAppliedDetail = $tagApplied->tagAppliedDetail($tagAppliedId);
				if(isset($tagAppliedDetail) && !empty($tagAppliedDetail))
				{
					$delete=$tagApplied->deleteAll(array('TagApplied.id' => $tagAppliedId,'contact_business_id IS NULL'));
                	$contacts->updateAll(['order_by_recent'=>date('Y-m-d H:i:s')],['id'=>$tagAppliedDetail['id']]);
                	$tagList['status'] = true;
				}
			}
			if(empty($tagList))
			{
				$tagList['status'] = false;
			}
			$return['Tags.deleteContactTag'] = [
                $tagAppliedId => $tagList
            ];
            return $return;
        }
        catch (\Exception $e) 
        {
			
         
        }
    }

    /*
    *This funciton is used to get all tags
    */
    public function getAllAgencyTags($agencyId)
    {
        try
        { 
            $tags = TableRegistry::getTableLocator()->get('Tags');
			$tagList = [];
			if(!empty($agencyId))
			{
				$allTags = $tags->allActiveAgencyTags($agencyId);
				if(isset($allTags) && !empty($allTags)){
					if(!empty($tagList))
					{
						$counter = 1;
					}
					else
					{
						$counter = 0;
					}
					foreach ($allTags as $tag) 
					{
						$tagList[$counter]['id'] = $tag['id'];
						$tagList[$counter]['name'] = $tag['name'];
						$counter++;
					}
				}
			}
			$return['Tags.getAllAgencyTags'] = [
                $agencyId => $tagList
            ];
            return $return;
        }
        catch (\Exception $e) 
        {
			
         
        }
    }

    /**
    *This function is used to save contact tags
    **/
    public function saveContactTags($objectData)
    { 
        try
        {
            $session = Router::getRequest()->getSession();
            $loginAgencyId =  $session->read("Auth.User.agency_id");
            //$myfile = fopen(ROOT."/logs/contactrail.log", "a") or die("Unable to open file!"); 
            $tagApplied = TableRegistry::getTableLocator()->get('TagApplied');
            $contacts = TableRegistry::getTableLocator()->get('Contacts');
            $returnData = ['tag_ids' => ""];
            $saveFlag = 0;
            if(isset($objectData) && !empty($objectData)){
                $contactId = $objectData['contact_id'];
                $tagIds = $objectData['tag_applied_ids'];
                if(isset($contactId) && !empty($contactId) && isset($tagIds) && !empty($tagIds))
                {
                	foreach ($tagIds as $tagId) 
                	{
                		//check tag is already applied
                		$checkTagApplied = $tagApplied->tagAppliedByContactAndTagId($contactId,$tagId);
                		if(empty($checkTagApplied))
                		{
                			$tagAppliedArr = [];
	                		$tagAppliedArr['contact_id'] = $contactId;
	                		$tagAppliedArr['tag_id'] = $tagId;
	                		$tagAppliedData = $tagApplied->newEntity();
	                		$tagAppliedData = $tagApplied->patchEntity($tagAppliedData,$tagAppliedArr);
	                		$tagAppliedData = $tagApplied->save($tagAppliedData);
	                		$contacts->updateAll(['order_by_recent'=>date('Y-m-d H:i:s')],['id'=>$contactId]);
                		}
                		$saveFlag = 1;
                	}
                }
                if($saveFlag)
                {
                	$returnData = ['tag_ids' => $tagIds];
                }
                
            }
            //nowcerts update contacts details
            NowCertsApi::updateIntoNowcerts($contactId, null, null, null);
            return $returnData;
        }catch (\Exception $e) {
			
            //$txt=date('Y-m-d H:i:s').' :: Contact update Error- '.$e->getMessage();
			//fwrite($myfile,$txt.PHP_EOL);
        }
        
    }
}