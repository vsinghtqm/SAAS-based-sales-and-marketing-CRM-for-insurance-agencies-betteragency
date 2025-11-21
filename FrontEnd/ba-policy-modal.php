<?php use ComponentLibrary\Lib\ComponentTools;

require_once(\ComponentLibrary\Lib\ComponentLibrary::componentPath("ba-policy-modal")); 

?>
<style>
    /* .v-dialog__content{
        z-index: 999 !important;
    } */



</style>
<script  type="text/x-template" id="<?=ComponentTools::templateName(__FILE__)?>">

		<v-card class="custom-policy-card" rounded style="width: 100%;padding: 15px 15px 16px;margin-bottom: 16px;margin-right: 0px;height: 170px;" v-bind:class="{ policySlide: (policyId === activePolicyModal) }">
            <div class="policy-download-icon" v-if="policy.ivans_policy_id || policy.ivans_policy_status">
                <img :src= "`${base_url}/img/policy-download.svg`" alt="">
            </div>
			<v-row class="m-0" style="height: 100%">
			<!-- <v-col cols="2" v-if="policy_icon !='' "> -->
			<!-- <span ><v-img :lazy-src='policy_icon' max-height="12px" max-width="12px" :src='policy_icon'></v-img></span> -->
			<!-- </v-col> -->
<!--            <v-col cols="1" ></v-col>-->
			<v-col cols="12" class="p-0 d-flex flex-column justify-content-between">
			<div class="common-text-class">
			<div style="font-weight: 500;" class="policy-text" :title="policyTypeTitle">{{ policyType }}</div>
            </div>
                <div class="common-text-class">
            
			<div class="policy-text" v-if="policy.status != 0"><span>Carrier </span>
                    <span style="font-weight: 500; text-transform:capitalize;">{{ policy?.carrier?.parent_name || policy?.carrier?.name || "--"}}</span>
<!--                    <span style="font-weight: 500; text-transform:capitalize;" >{{ contact.preferred_name ? contact.preferred_name : contact.first_name+" "+contact.last_name}}</span>-->
<!--                <span style="font-weight: 500;" v-if="contact.preferred_name != ''">{{ contact.preferred_name }}</span>-->
<!--                <span v-else style="font-weight: 500;">{{ contact.first_name }}</span>-->
<!--                <span v-if="contact.middle_name != ''" style="font-weight: 500;"> {{ contact.middle_name }}</span>-->
<!--                <span style="font-weight: 500;">{{ contact.last_name }}</span>-->
            </div>
            <div v-if="policy.status != 0"><span >Premium </span> <span style="font-weight: 500;" v-if="policy.premium_amount">{{ policy.premium_amount | currencyFormat }}</span><span v-else>0.00</span></div>
            <div v-if="policy.status != 0"><span >Policy # </span> <span style="font-weight: 500;" :title= "policy.policy_number">{{ policy.policy_number | trimText(policy.policy_number) }}</span></div>
			<div class="policy-card-date" v-if="policy.status != 0"><span v-if="policy.effective_date !='' && policy.effective_date !=null"> {{new Date(policy.effective_date) | convertDateToUtc(policy.effective_date)}} </span><span v-else>--</span><span v-if="policy.ivans_expiration_date != null && policy.ivans_expiration_date != ''">{{new Date([policy.ivans_expiration_date]) | convertDateToUtc([policy.ivans_expiration_date])}}</span><span v-else = "policy.expiration_date !='' ">- {{new Date(policy.expiration_date) | convertDateToUtc(policy.expiration_date)}}</span></div>
                </div>
                <div class="common-text-class d-flex justify-content-between align-items-end mt-2">
            <div class="policy-sub-status"><span style="font-weight: 400; font-size:13px;" :class="setPolicySubStatusClass(policy)">{{ setPolicyStatus(policy) }}</span></div>
                <span class="fs-10" style="font-weight: 400; font-size:13px;line-height:13px;" v-if="isInsured != 1">{{ policy.is_additional }} PRIMARY</span>
                <span class="fs-10" style="font-weight: 400; font-size:13px;line-height:13px;" v-else> ADDT'L. INSURED</span>

			</div>
            
			</v-col>
			<!-- <div class="pl-0 right-icon-policy-card">
			<v-icon class="policy-check-icon" v-if = "policy.status == _ID_STATUS_ACTIVE">mdi-check</v-icon>
			<v-icon color="red" v-else-if = "policy.status == _ID_STATUS_CANCELLED || policy.status == _ID_STATUS_INACTIVE || policy.status == _ID_STATUS_PENDING">mdi-cancel</v-icon>
			<v-icon color="red" v-else ></v-icon> -->
			<!-- <img  :lazy-src='img_url' max-height="25" max-width="25" :src='img_url'/> -->
			<!-- </div> -->
			</v-row>
		</v-card>
</script>

<script>
    Vue.component('<?=ComponentTools::componentName(__FILE__)?>', {
        template: '#<?=ComponentTools::templateName(__FILE__)?>',
        props: ['policyId','activePolicyModal', 'isInsured'],
        data: function(){
            return {
                policy: {},
				dialog:false,
				base_url:base_url,
				policy_icon:'',
				contact_id : '',
				contact:'',
				img_url :base_url+'img/policy-card-p-icon.jpg',
                policyTypeTitle: '',
            }
        },
        methods: {
			populateContactData: function(data){
                if(data.data){
                    this.contact =  data.data.data.Contact[this.contact_id];		
                }else{
                    this.contact =  data.Contact[this.contact_id];		
                }
						
            },
			populatePolicyData: function(data){
                if(data['data']){
                    this.policy = data['data']['data']['ContactOpportunities.getPolicyInformation'][this.policyId]['policy'];
                    this.contact_id = data['data']['data']['ContactOpportunities.getPolicyInformation'][this.policyId]['policy']['contact_id'];
                   
                    
                    let objectData = {
                        'objectId':this.contact_id,
                        'objectName':'Contact',
                        'fields': '*'
                    }
                    DataBridgeContacts.save('Contacts.getContactDetailsById', objectData, this.populateContactData);
                    
                   
                    if(data['data']['data']['ContactOpportunities.getPolicyInformation'][this.policyId]['Icon']['icon'] != null){
                        this.policy_icon = this.base_url+data['data']['data']['ContactOpportunities.getPolicyInformation'][this.policyId]['Icon']['icon'];
				    }
                }else{
                    this.policy = data['ContactOpportunities.getPolicyInformation'][this.policyId]['policy'];
                    this.contact_id = data['ContactOpportunities.getPolicyInformation'][this.policyId]['policy']['contact_id'];
                      
                    let objectData = {
                        'objectId':this.contact_id,
                        'objectName':'Contact',
                        'fields': '*'
                    }
                    DataBridgeContacts.save('Contacts.getContactDetailsById', objectData, this.populateContactData);
                    
                   
                    if(data['ContactOpportunities.getPolicyInformation'][this.policyId]['Icon']['icon'] != null){
                        this.policy_icon = this.base_url+data['ContactOpportunities.getPolicyInformation'][this.policyId]['Icon']['icon'];
                    }
                }
				
                
            },
            setSubStatus: function(policy){
                // return (this.policy.status == _ID_STATUS_ACTIVE && this.policy.active_sub_status == _ID_ACTIVE_SUB_STATUS_ACTIVE) ? "Active" : (this.policy.status == _ID_STATUS_ACTIVE && this.policy.active_sub_status == _ID_ACTIVE_SUB_STATUS_NON_RENEWAL) ? "Non Renewal" : (this.policy.status == _ID_STATUS_ACTIVE && this.policy.active_sub_status == _ID_ACTIVE_SUB_STATUS_PENDING_CANCEL) ? "Pending Cancel" : (this.policy.status == _ID_STATUS_ACTIVE && this.policy.active_sub_status == _ID_ACTIVE_SUB_STATUS_FUTURE_EFFECTIVE) ? "Future Effective" : (this.policy.status == _ID_STATUS_CANCELLED || this.policy.status == _ID_STATUS_INACTIVE) ? (this.policy.inactive_sub_status == _ID_INACTIVE_SUB_STATUS_EXPIRED) ? "Expired" : (this.policy.inactive_sub_status == _ID_INACTIVE_SUB_STATUS_RENEWED) ? "Renewed" : (this.policy.inactive_sub_status == _ID_INACTIVE_SUB_STATUS_REPLACED) ? "Replaced" : (this.policy.inactive_sub_status == _ID_INACTIVE_SUB_STATUS_CANCELLED) ? "Cancelled" : "Inactive" : (this.policy.status == _ID_STATUS_PENDING) ? (this.policy.pending_sub_status == _ID_PENDING_SUB_STATUS_NEW_LEAD) ? "New Lead" : (this.policy.pending_sub_status == _ID_PENDING_SUB_STATUS_QUOTED) ? "Quoted" : (this.policy.pending_sub_status == _ID_PENDING_SUB_STATUS_LOST) ? "Lost" : "Pending" : "";

                // return (policy.status == _ID_STATUS_ACTIVE && policy.active_sub_status == _ID_ACTIVE_SUB_STATUS_ACTIVE) ? "Active" : (policy.status == _ID_STATUS_ACTIVE && policy.active_sub_status == _ID_ACTIVE_SUB_STATUS_NON_RENEWAL) ? "Non Renewal" : (policy.status == _ID_STATUS_ACTIVE && policy.active_sub_status == _ID_ACTIVE_SUB_STATUS_PENDING_CANCEL) ? "Pending Cancel" : (policy.status == _ID_STATUS_ACTIVE && policy.active_sub_status == 4) ? "Future Effective" : (policy.status == _ID_STATUS_CANCELLED || policy.status == _ID_STATUS_INACTIVE) ? (policy.inactive_sub_status == _ID_INACTIVE_SUB_STATUS_EXPIRED) ? "Expired" : (policy.inactive_sub_status == _ID_INACTIVE_SUB_STATUS_RENEWED) ? "Renewed" : (policy.inactive_sub_status == _ID_INACTIVE_SUB_STATUS_REPLACED) ? "Replaced" : (policy.inactive_sub_status == _ID_INACTIVE_SUB_STATUS_CANCELLED) ? "Cancelled" : "Inactive" : (policy.status == _ID_STATUS_PENDING) ? (policy.pending_sub_status == _ID_PENDING_SUB_STATUS_NEW_LEAD) ? "New Lead" : (policy.pending_sub_status == _ID_PENDING_SUB_STATUS_QUOTED) ? "Quoted" : (policy.pending_sub_status == _ID_PENDING_SUB_STATUS_LOST) ? "Lost" : "Pending" : "";
                switch (policy['status']) {
                    case _ID_STATUS_ACTIVE:
                        switch (policy['active_sub_status']) {
                            case _ID_ACTIVE_SUB_STATUS_ACTIVE:
                                return "Active";
                                break;
                            case _ID_ACTIVE_SUB_STATUS_NON_RENEWAL:
                                return "Non Renewal";
                                break;
                            case _ID_ACTIVE_SUB_STATUS_PENDING_CANCEL:
                                return "Pending Cancel";
                                break;
                            case 4:
                                return "Future Effective";
                                break;
                            default:
                                return "Active";
                        }
                        break;
                    case _ID_STATUS_CANCELLED: case _ID_STATUS_INACTIVE:
                        switch (policy['inactive_sub_status']) {
                            case _ID_INACTIVE_SUB_STATUS_EXPIRED:
                                return "Expired";
                                break;
                            case _ID_INACTIVE_SUB_STATUS_RENEWED:
                                return "Renewed";
                                break;
                            case _ID_INACTIVE_SUB_STATUS_REPLACED:
                                return "Replaced";
                                break;
                            case _ID_INACTIVE_SUB_STATUS_CANCELLED:
                                return "Cancelled";
                                break;
                            default:
                                return "Inactive";
                        }
                        break;
                    case _ID_STATUS_PENDING:
                        switch (policy['pending_sub_status']) {
                            case _ID_PENDING_SUB_STATUS_NEW_LEAD:
                                return "New Lead";
                                break;
                            case _ID_PENDING_SUB_STATUS_QUOTED:
                                return "Quoted";
                                break;
                            case _ID_PENDING_SUB_STATUS_LOST:
                                return "Lost";
                                break;
                            default:
                                return "Pending";
                        }
                        break;
                    default:
                        return "";
                }
			},
            setSubStatusClass: function(policy){
                switch (policy['status']) {
                    case _ID_STATUS_ACTIVE:
                        switch (policy['active_sub_status']) {
                            case _ID_ACTIVE_SUB_STATUS_ACTIVE:
                                return 'success-sub-status';
                                break;
                            case _ID_ACTIVE_SUB_STATUS_NON_RENEWAL:
                            case _ID_ACTIVE_SUB_STATUS_PENDING_CANCEL:
                                return 'informative-sub-status';
                                break;
                            case 4:
                                return 'future-effective-sub-status';
                                break;
                            default:
                                return 'success-sub-status';
                        }
                        break;
                    case _ID_STATUS_CANCELLED: case _ID_STATUS_INACTIVE:
                        switch (policy['inactive_sub_status']) {
                            case _ID_INACTIVE_SUB_STATUS_EXPIRED:
                                return 'danger-sub-status';
                                break;
                            case _ID_INACTIVE_SUB_STATUS_RENEWED:
                            case _ID_INACTIVE_SUB_STATUS_REPLACED:
                                return 'informative-sub-status';
                                break;
                            case _ID_INACTIVE_SUB_STATUS_CANCELLED:
                                return 'danger-sub-status';
                                break;
                            default:
                                return 'informative-sub-status';
                        }
                        break;
                    case _ID_STATUS_PENDING:
                        switch (policy['pending_sub_status']) {
                            case _ID_PENDING_SUB_STATUS_NEW_LEAD:
                                return 'new-lead-sub-status';
                                break;
                            case _ID_PENDING_SUB_STATUS_QUOTED:
                                return 'quoted-sub-status';
                                break;
                            case _ID_PENDING_SUB_STATUS_LOST:
                                return 'informative-sub-status';
                                break;
                            default:
                                return 'informative-sub-status';
                        }
                        break;
                    default:
                        return 'informative-sub-status';
                }


            },
            setPolicyStatus: function(policy) {
                switch (policy['status']) {
                    case _ID_STATUS_ACTIVE:
                        var d1 = new Date();
						var d2 = new Date(policy['effective_date']);
						if(d2 > d1)
						{
							return 'Future Effective';
						}else{
							return 'Active';
						}
                        break;
                    case _ID_STATUS_INACTIVE:
						if(policy['inactive_sub_status'] == _ID_INACTIVE_SUB_STATUS_CANCELLED)
						{
							return "Canceled";
						}
						else{
                            return "Inactive";
						}
                        break;
                    case _ID_STATUS_PENDING:
                        switch (policy['pipeline_stage']) {
                            case _PIPELINE_STAGE_NEW_LEAD:
                                return "New Lead";
                                break;
                            case _PIPELINE_STAGE_APPOINTMENT_SCHEDULED:
                                return "Appointment";
                                break;
                            case _PIPELINE_STAGE_WORKING:
                                return "Working";
                                break;
                            case _PIPELINE_STAGE_QUOTE_READY:
                                return "Quote Ready";
                                break;
                            case _PIPELINE_STAGE_QUOTE_SENT:
                                return "Quote Sent";
                                break;
                            case _PIPELINE_STAGE_LOST:
                                return "Lost";
                                break;
                            default:
                                return "Pending";
                        }
                        break;
                    default:
                        return "";
                }
			},
            setPolicySubStatusClass: function(policy){
                switch (policy['status']) {
                    case _ID_STATUS_ACTIVE:
                        return 'success-sub-status';
                        break;
                    case _ID_STATUS_CANCELLED: case _ID_STATUS_INACTIVE:
                        return 'danger-sub-status';
                        break;
                    case _ID_STATUS_PENDING:
                        return 'policy-new-lead-sub-status';
                        break;
                    default:
                        return 'informative-sub-status';
                }

            },
        },
        beforeMount: function(){
            DataBridgeContacts.save('ContactOpportunities.getPolicyInformation', this.policyId, this.populatePolicyData);
        },
        filters: {
            // currencyFormat: function(value){
            //     if(Number.isNaN(value / 100)){
            //         return "--";
            //     }
            //     return '$' + (Math.round(value * 100) / 100).toLocaleString();
            // },
            // formatDate(dateToBeFormatted) {
            //     if(dateToBeFormatted !=null)
			// 	{
            //     const date = new Date(dateToBeFormatted);
                
            //     return `${date.toLocaleDateString("en-GB", {
            //     "day": "numeric",
            //     "year": "numeric",
            //     "month": "short",

            //     })}`;
			// 	}else{
			// 		return '--';
			// 	}
            // },
            // formatTime(dateToBeFormatted) {
            //     const date = new Date(dateToBeFormatted);
                
            //     return `${date.toLocaleTimeString("en-US", {"hour": "numeric","minute":"numeric"})}`;
            // },
        },
        computed: {
            policyType: function(){
                if(this.policy.insurance_type != null){
                    this.policyTypeTitle = (this.policy.sales_title) ? this.policy.sales_title : (this.policy.insurance_type.type) ? this.policy.insurance_type.type :'';
                    return (this.policy.sales_title) ? this.policy.sales_title : (this.policy.insurance_type.type) ? this.policy.insurance_type.type :'';
                } else {
                    this.policyTypeTitle = '';
                    return '--';
                }
            },
			contactName: function(){
               if(this.contact != null){
				   var name = '';
                    if(this.contact.first_name !=null)
					{
						name = this.contact.first_name;
					}
					if(this.contact.last_name !=null)
					{
						name = name+' '+ this.contact.last_name;
					}
					return name;
                } else {
                    return '--';
                }
            },
            policyExpirationDate: function(){
                if(this.policy.effective_date != null && this.policy.term_length!=null && this.policy.term_length !=''){ 
					var dt = new Date(this.policy.effective_date);				
					var term_length  = this.policy.term_length;
					dt.setMonth( dt.getMonth() + parseInt(term_length) );					
					return dt;					
                } else {
                    return '';
                }
            },
            // setSubStatus: function(policy){
            //     // return (this.policy.status == _ID_STATUS_ACTIVE && this.policy.active_sub_status == _ID_ACTIVE_SUB_STATUS_ACTIVE) ? "Active" : (this.policy.status == _ID_STATUS_ACTIVE && this.policy.active_sub_status == _ID_ACTIVE_SUB_STATUS_NON_RENEWAL) ? "Non Renewal" : (this.policy.status == _ID_STATUS_ACTIVE && this.policy.active_sub_status == _ID_ACTIVE_SUB_STATUS_PENDING_CANCEL) ? "Pending Cancel" : (this.policy.status == _ID_STATUS_ACTIVE && this.policy.active_sub_status == _ID_ACTIVE_SUB_STATUS_FUTURE_EFFECTIVE) ? "Future Effective" : (this.policy.status == _ID_STATUS_CANCELLED || this.policy.status == _ID_STATUS_INACTIVE) ? (this.policy.inactive_sub_status == _ID_INACTIVE_SUB_STATUS_EXPIRED) ? "Expired" : (this.policy.inactive_sub_status == _ID_INACTIVE_SUB_STATUS_RENEWED) ? "Renewed" : (this.policy.inactive_sub_status == _ID_INACTIVE_SUB_STATUS_REPLACED) ? "Replaced" : (this.policy.inactive_sub_status == _ID_INACTIVE_SUB_STATUS_CANCELLED) ? "Cancelled" : "Inactive" : (this.policy.status == _ID_STATUS_PENDING) ? (this.policy.pending_sub_status == _ID_PENDING_SUB_STATUS_NEW_LEAD) ? "New Lead" : (this.policy.pending_sub_status == _ID_PENDING_SUB_STATUS_QUOTED) ? "Quoted" : (this.policy.pending_sub_status == _ID_PENDING_SUB_STATUS_LOST) ? "Lost" : "Pending" : "";
            //
            //     return (policy.status == _ID_STATUS_ACTIVE && policy.active_sub_status == _ID_ACTIVE_SUB_STATUS_ACTIVE) ? "Active" : (policy.status == _ID_STATUS_ACTIVE && policy.active_sub_status == _ID_ACTIVE_SUB_STATUS_NON_RENEWAL) ? "Non Renewal" : (policy.status == _ID_STATUS_ACTIVE && policy.active_sub_status == _ID_ACTIVE_SUB_STATUS_PENDING_CANCEL) ? "Pending Cancel" : (policy.status == _ID_STATUS_ACTIVE && policy.active_sub_status == _ID_ACTIVE_SUB_STATUS_FUTURE_EFFECTIVE) ? "Future Effective" : (policy.status == _ID_STATUS_CANCELLED || policy.status == _ID_STATUS_INACTIVE) ? (policy.inactive_sub_status == _ID_INACTIVE_SUB_STATUS_EXPIRED) ? "Expired" : (policy.inactive_sub_status == _ID_INACTIVE_SUB_STATUS_RENEWED) ? "Renewed" : (policy.inactive_sub_status == _ID_INACTIVE_SUB_STATUS_REPLACED) ? "Replaced" : (policy.inactive_sub_status == _ID_INACTIVE_SUB_STATUS_CANCELLED) ? "Cancelled" : "Inactive" : (policy.status == _ID_STATUS_PENDING) ? (policy.pending_sub_status == _ID_PENDING_SUB_STATUS_NEW_LEAD) ? "New Lead" : (policy.pending_sub_status == _ID_PENDING_SUB_STATUS_QUOTED) ? "Quoted" : (policy.pending_sub_status == _ID_PENDING_SUB_STATUS_LOST) ? "Lost" : "Pending" : "";
			// },
        }
    });
</script>
