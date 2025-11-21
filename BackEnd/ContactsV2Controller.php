<?php
namespace App\Controller;

use Cake\Event\Event;


class ContactsV2Controller extends AppController
{
    public function beforeFilter(Event $event){
        parent:: beforeFilter($event);
        $this->viewBuilder()->setLayout("default-vuetify");

        $this->loadmodel('Contacts');
    }
    /**
     * This method is used to send the birthday email and sms to contact
     */
    public function viewContact($contactId)
    {
        $login_agency_id = $this->request->session()->read('Auth.User.agency_id');
        $agencyId = $login_agency_id;
        $additional_insured_flag = '';
        if(isset($contactId) && !empty($contactId))
        {
            $contacts = $this->Contacts->getContactsByClickOnName($contactId);
            $additional_insured_flag = $contacts[0]['additional_insured_flag'];
            //if user try to modify other agency contact redirect them to listing page
            if(empty($contacts) || (isset($contacts[0]['agency_id']) && $login_agency_id != $contacts[0]['agency_id']))
            {
                 $this->Flash->error(__('You do not have permission to access this page.'));
                 return $this->redirect(['controller' => 'contacts', 'action' => 'list']);
            }
        }

        $this->set(compact('contactId','agencyId','additional_insured_flag'));
    }
}
