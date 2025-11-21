<?php
namespace App\Controller;

use App\Lib\FrontEndApi;
use App\Lib\ObjectFetcher;
use App\Lib\JSendTools;
use Cake\Event\Event;
use Cake\Http\Exception\NotFoundException;


class FrontEndApiController extends AppController
{
	public $components = array('Common');
    public function beforeFilter(Event $event){
        parent:: beforeFilter($event);
        $this->autoRender = false;
    }


    /**
     * This method is used to send the birthday email and sms to contact
     */
    public function multiGet($requests){
        $requests = json_decode($requests);
        foreach($requests as $request){
            $objectName = $request[0];
            $id = $request[1];
            $fields = isset($request[2]) ? $request[2] : '';
            $results[] = FrontEndApi::get($objectName, $id, $fields);
        }

        return $this->response
            ->withType("application/json")
            ->withStringBody(
                json_encode(
                    JSendTools::success($results)
                )
            );
    }

    public function get($objectName, $objectId, $fields,$start=null,$limit=null){
        $userId=$this->request->getSession()->read('Auth.User.user_id');
        $agencyId=$this->request->getSession()->read('Auth.User.agency_id');
        $result = FrontEndApi::get($objectName, $objectId, $fields,$start,$limit);
        if(empty($result)){
            throw new NotFoundException("Object Not Found Or You're Not Authorized To See It");
        }
        return $this->response
            ->withType("application/json")
            ->withStringBody(
                json_encode(
                    JSendTools::success($result)
                )
            );
    }

    public function save($object, $data){

    }

    public function post(){
        $userId=$this->request->getSession()->read('Auth.User.user_id');
        $agencyId=$this->request->getSession()->read('Auth.User.agency_id');
        if($this->request->is('post'))
        { 
            $data = $this->request->data;
            $objectName = $data['object_name'];
            $objectData = $data['object_data'];
            $result = FrontEndApi::post($objectName, $objectData);
            if(empty($result)){
                throw new NotFoundException("Object Not Found Or You're Not Authorized To See It");
            }
            return $this->response
            ->withType("application/json")
            ->withStringBody(
                json_encode(
                    JSendTools::success($result)
                )
            );
        } 
    }
	
	
	// public function convertUtcDateTimeToUserTimeZone() {
		// $login_agency_id=$this->request->session()->read('Auth.User.agency_id');
		// $time_zone = $this->Common->getTimeZone($login_agency_id);
		// $result = $this->Common->convertUtcDateTimeToUserTimeZone($time_zone,$this->request->data['date']);
			// return $this->response
				// ->withType("application/json")
				// ->withStringBody(
					// json_encode(
						// JSendTools::success($result)
					// )
				// );
	// }
}
