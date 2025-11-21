<?php

use ComponentLibrary\Lib\ComponentTools;

?>
<style>
.dropZone {
  width: 100%;
  height: 200px;
  position: relative;
  border: 2px dashed #eee;
}

.dropZone:hover {
  border: 2px solid #2e94c4;
}

.dropZone:hover .dropZone-title {
  color: #1975A0;
}

.dropZone-info {
  color: #A8A8A8;
  position: absolute;
  top: 50%;
  width: 100%;
  transform: translate(0, -50%);
  text-align: center;
}

.dropZone-title {
  color: #787878;
}

.dropZone input {
  position: absolute;
  cursor: pointer;
  top: 0px;
  right: 0;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
}

.dropZone-upload-limit-info {
  display: flex;
  justify-content: flex-start;
  flex-direction: column;
}

.dropZone-over {
  background: #5C5C5C;
  opacity: 0.8;
}

.dropZone-uploaded {
  width: 80%;
  height: 200px;
  position: relative;
  border: 2px dashed #eee;
}

.dropZone-uploaded-info {
  display: flex;
  flex-direction: column;
  align-items: center;
  color: #A8A8A8;
  position: absolute;
  top: 50%;
  width: 100%;
  transform: translate(0, -50%);
  text-align: center;
}

.v-snack__wrapper.theme--dark {
    background-color: #fff !important;
    color: #3A3541 !important;
    font-weight: 400 !important;
    font-size: 16px !important;
    line-height: 21px !important;
    box-shadow: 0px 4px 8px -4px rgb(58 53 65 / 42%) !important;
    border-radius: 5px !important;
    max-width: 265px !important;
    min-width: 265px !important;
}
.success-alert-icon{color:#5FB322 !important;}
.v-snack {
    left: 39% !important;
}
.success-alert-text{
	font-size: 16px !important;
    padding-top: 7px;
    position: relative;
    top: 2px;
    color: #3A3541;
    font-weight: 400;
    font-size: 16px;
    line-height: 21px;

}
.v-snack__content {
    padding: 14px 0px 14px 8px  !important;
}
.v-toolbar--flat{
display: none;
}

.rename-file-popup label.v-label.v-label--active.theme--light {
    top: 16px !important;
}
.v-card__title.text-h6.common-title-popup{color: #3A3541 !important;font-weight: 500 !important;font-size: 14px !important;line-height: 20px !important; text-transform:uppercase !important;}
.v-snack__wrapper.theme--dark {
    background-color: #fff !important;
    color: #3A3541 !important;
    font-weight: 400 !important;
    font-size: 16px !important;
    line-height: 21px !important;
    box-shadow: 0px 4px 8px -4px rgb(58 53 65 / 42%) !important;
    border-radius: 5px !important;
    max-width:304px !important;
    min-width: 300px !important;
}
.display{
  background-color: #b6d9fb;
}
.v-snack {
    left: 33% !important;
}
.v-list--dense .v-list-item .v-list-item__title {
    font-size: 14px !important;
    font-weight: 400 !important;
    line-height: 21px !important;
    color: #3A3541 !important;
}
a.acord-form-name {
    color: rgba(58, 53, 65, 0.87);
}
.acord-form-name:hover {
	text-decoration: underline;
}
.drop-icon .v-icon.v-icon {
    right: 28px;
}
.v-progress-circular.search-loader.v-progress-circular--visible
{
	height: 25px!important;
    width: 25px!important;
    position: absolute;
    right: 19px;
    top: 16px;
	color: #AEAAB2;

}


.theme--light.v-data-table > .v-data-table__wrapper > table > thead > tr > th:first-child {border-radius: 8px 0 0 0 !important;}
.theme--light.v-data-table > .v-data-table__wrapper > table > thead > tr > th {border-radius: 0 !important;}
.theme--light.v-data-table > .v-data-table__wrapper > table > thead > tr > th:last-child {border-radius: 0px 8px 0 0 !important;}

.acord-tab-max-height .v-data-table__wrapper { max-height: unset; overflow-y: unset; overflow-x:auto; min-height: unset; scrollbar-width: thin;}

.dropdown-menu.custom-dropdown.custom-dropdown-attachment {
    left: -40px !important;
    top: 20px !important;
    transform: unset !important;
}
.acord-tab-max-height.attachment-table-main table tbody {
  height: auto !important; 
  max-height: 582px !important; 
  min-height: 300px;
  overflow: unset;
}


.attachment-table-main table {
    min-height: 200px;
}

.acord-action-btn.dropdown-menu.custom-dropdown.show {
    left: -70px !important;
    top: -25px !important;
}

</style>
<script type="text/x-template" id="<?=ComponentTools::templateName(__FILE__)?>">
	<div>

        <div class="d-flex custom-grey">
        <h4 class="upcoming-heading pt-2">ACORD Files</h4>
            <v-btn @click="createAcordDialog" class="ml-auto btn btn-outline-success btn-round btn-position" style="background-color:transparent;" v-if="checkIvansStatus" >
			<v-icon class="plus-icon-css">mdi-plus</v-icon><span class="text-success">Create New Acord</span>
            </v-btn>
        </div>
	<template>
      <v-toolbar
        flat
      >
        <v-dialog
          v-model="dialog"
          max-width="559px"
        >
          <v-card style="min-height:168px;">
		   <v-card-title>
              <span class="link-to-policy-title">New ACORD Form Select</span>
            </v-card-title>
              <v-card-text>
              <v-form
                  ref="form"
                  v-model="valid"
                  lazy-validation
                >

                  <v-row>
                  <v-col cols="12">
				  <v-autocomplete class="mb-3 drop-icon"
					:return-object="true"
					:items="acord_forms"
					item-text="name"
					item-value="id"
					:search-input.sync="searchAcords"
					label="Search"
					v-model="form_id"
					dense
					outlined
					@change = "enableNextBtn();"
					@keyup = "searchAcordForms"
					></v-autocomplete>
					<v-progress-circular class="search-loader" :value="100" v-if="searchInProgress"></v-progress-circular >
                </p>
                    </v-col>

                  </v-row>

                </v-form>
              </v-card-text>

			  <v-card-actions>
				<v-spacer></v-spacer>
				<v-btn
					color="#757575"
					text
				@click="close"
				class="cancel-edit-contact-details"
				>
				Cancel
				</v-btn>
				<v-btn class="btn-save-create-service v-btn v-btn--text theme--light v-size--default"
					justify="space-around"
					depressed
					dark
					v-if="acordFormSelected"
					@click="next()"
				>
					Next
				</v-btn>
				<v-btn
					v-else
					justify="space-around"
					disabled
				>
					Next
				</v-btn>
			</v-card-actions>


          </v-card>
        </v-dialog>
      </v-toolbar>
	   <v-form id = 'open_filled_form' method='POST' target="_blank" :action="computedAction" style="display:none;">

		<textarea
			id="open_acord_form_textarea" name= "PolicyData"
        ></textarea>
		<textarea id="AccessToken" class = "access_token" name="AccessToken" ></textarea>
		<button type="submit" id = 'acord_submit'>Submit</button>
		</v-form>
		<form method="POST" id="download_acord_form" action="/contacts/download-acord-applications" data-hs-cf-bound="true">
		<input type="hidden" id="_csrfToken" name="_csrfToken" autocomplete="off" value="">
	 		<input type="hidden" id="acord_form_id" name="form_id" >

		</form>
    </template>
	<template>
      <v-toolbar
        flat
      >
        <v-dialog
          v-model="linkPolicyDialog"
          max-width="529px"
        >
          <v-card style="height:168px;">

            <v-card-title>
              <span class="link-to-policy-title">Attach ACORD to a policy</span>
            </v-card-title>
              <v-card-text class="pt-0">

                  <v-row>
                 	<v-col cols="12">
					<v-form ref="policyAttachmentform" lazy-validation>
					 <!-- <v-select
							v-model="policy_type"
							label="Select Policy"
							:items="activePolicies"
							item-text="name"
							item-value="id"
							dense
							outlined
							@change = "enableAttachBtn()"
							:menu-props="{ top:false, offsetY:true }"
						></v-select> -->
						<v-autocomplete class="v-slect-green"
							:items="policyTypes"
							item-text="name"
							item-value="id"
							label="Select Policy"
							v-model="policy_type"
							dense
							outlined
							:menu-props="{ top:false, offsetY:true }"
							@change = "enableAttachBtn()"
							>
                            <template v-slot:item="{ item }">
                                 <span class="select-option-height">{{ item.policy_type}} <span class="select-option-policy-number" v-if="item.policy_number"> {{item.policy_number}}</span></span>
                             </template>
                        </v-autocomplete>
					</v-form>
                	</v-col>
                  </v-row>

              </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn class="cancel-edit-contact-details"
			  	color="#757575"
                text
                @click="closeLinkPolicyModal"
              >
                Cancel
              </v-btn>
              <v-btn class="btn-save-create-service v-btn v-btn--text theme--light v-size--default"
				justify="space-around"
				v-if="policySelected"
				depressed
				dark
                @click="attachPolicyToAccord"
              >
                Attach
              </v-btn>
			  <v-btn
					v-else
					justify="space-around"
					disabled
				>
					Attach
			</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-toolbar>
    </template>

	<!----------------- Task cancel Modal  ------------------->
	<v-dialog v-model="closeAttachPolicydialog" max-width="35%">
				<v-card class="close-task-log">
					<v-card-title class="text-h5">
						<h5 class="modal-title cancel-task-model">Close Attach Policy</h5>

					</v-card-title>

					<v-card-text>
						There are unsaved changes. If you would like to save changes,press the 'Keep Editing' button.
					</v-card-text>

					<v-card-actions>
						<v-spacer></v-spacer>
						<v-btn style="background: none;box-shadow: unset; color: #757575;
							font-size: 14px;"
							justify="space-around"
							@click="closeWithoutSave"
						>
							CLOSE WITHOUT SAVING
						</v-btn>
						<v-btn
						justify="space-around"
						depressed
						color="teal"
						dark
						@click="keepEditing"
						>
							kEEP EDITING
						</v-btn>
					</v-card-actions>
				</v-card>
			</v-dialog>

        <div class="mt-5">
            <v-simple-table class="elevation-1 table-custom-contact acord-tab-max-height attachment-table-main">
						<!--<template v-slot:default>-->
						<thead>
							<tr class="fixed-header">
							<th class="text-left" style="color: rgba(58, 53, 65, 0.87);;font-style: normal;font-size: 12px;line-height: 16px;font-weight: 500;font-family: Roboto;">
								FILE NAME
							</th>
							<th class="text-left" style="color: rgba(58, 53, 65, 0.87);;font-style:normal;font-size: 12px;line-height: 16px;font-weight: 500;font-family: Roboto;">
								USER
							</th>
							<th class="text-left" style="color: rgba(58, 53, 65, 0.87);;font-style: normal;font-size: 12px;line-height: 16px;font-weight: 500;font-family: Roboto;">
								DATE ADDED
							</th>
							<th class="text-left" style="color: rgba(58, 53, 65, 0.87);;font-style: normal;font-size: 12px;line-height: 16px;font-weight: 500;font-family: Roboto;">
								ACTION
							</th>
							</tr>
						</thead>
						<tbody>
							<tr  v-for="(item, index) in items" :key="index" v-if = "index != 'checkIvansStatus' && !intialAcordLoader">
							<td style="color: rgba(58, 53, 65, 0.87);font-size: 16px;font-weight: 400; font-family: Roboto;" ><span v-if="item.file != ''"><a class="acord-form-name" :href="`${item.view_url}`" target="_blank">{{ item.file }}</a></span><span v-else>--</span></td>
							<td style="color: rgba(58, 53, 65, 0.87);font-size: 16px;font-weight: 400; font-family: Roboto;"><span v-if="item.user != ''">{{ item.user }}</span><span v-else>--</span></td>
							<td style="color: rgba(58, 53, 65, 0.87);font-size: 16px;font-weight: 400; font-family: Roboto;"><span v-if="item.date_added != '' && item.date_added != null">{{ item.date_added }}</span><span v-else>--</span></td>
							<td><div class="dropdown attachement-dropdown acord-action-btn">
							<i class="fa fa-ellipsis-h " id="dropdownMenuButton" data-toggle="dropdown" ></i>
							<div class="dropdown-menu custom-dropdown d-inline-table custom-dropdown-attachment" aria-labelledby="dropdownMenuButton">
								<a class="dropdown-item" @click="openLinkPolicyDialog(item.id,item.file)">Attach</a>
								<a class="dropdown-item" @click='downloadAcordApplication(item.form_id)'>Download</a>
								<a href="#" icon text class="dropdown-item" x-large @click="postDuplicateAccord(item.id)"   style="" > Duplicate </a>
								<a href="#" icon text class="dropdown-item" x-large @click="editRenameItem(item.id,item.file,items)"   style="" > Rename </a>
								<a class="dropdown-item" :href="`${item.view_url}`" target="_blank">View</a>
								<a class="dropdown-item text-danger mb-2" :id="`${item.id}`" @click="deleteAcord($event)" > Delete </a>
							 </div>
							</div>
							</td>
							</tr>
                            <tr v-if="items.length == 0 && !intialAcordLoader" class="position-center">
                                <td colspan="4" class="text-center">
                                    No records found.
                                </td>
                            </tr>
                            <tr v-if="intialAcordLoader" class="position-center">
                                <td colspan="4" class="text-center">
                                    <img :src="`${base_url}/img/loader.gif`" style="width: 60px; height: 35px" />
                                </td>
                            </tr>
						</tbody>
						<!--</template>-->
					</v-simple-table>
        </div>
		<v-dialog
		v-model="deleteAcordDialog"
		persistent
		max-width="600"
		max-height="184"
		>
		<v-card style="height:184px;">
		<v-card-title class="text-h6 delete-policy-heading">
		Delete Acord
		</v-card-title>
		<v-card-text class="delete-policy-text">Are you sure you want to delete this acord? This action can not be undone.</v-card-text>
		<v-card-actions>
		<v-spacer></v-spacer>
		<v-btn class="btn-cancel"
			color="#757575"
			text
			@click="deleteAcordDialog = false"
		>
			No, Cancel
		</v-btn>
		<v-btn class="btn-delete"
			color="#F65559"
			text
			@click="deleteAcordForm"
		>
			Yes, Delete
		</v-btn>
		</v-card-actions>
		</v-card>
		</v-dialog>

		<v-dialog
          v-model="renameDialog"
          max-width="559px"
        >
          <v-card style="min-height:184px;">
            <v-card-title class="text-h6 common-title-popup">
              Rename Acord
            </v-card-title>
            <v-card-text>
              <v-form
                  ref="form"
                  v-model="valid"
                  lazy-validation
                >
					<div class="rename-file-popup">
						<v-row>
							<v-col cols="12">
								<v-text-field
									id="display_name_or_file_id"
									v-bind:class="{display:show}"
									class="text-no-wrap"
									label="File name"
									v-model="display_name_or_file"
									value="Rename File"
									outlined
									:rules="renameRules"
									autofocus
									required
									@click="keyPressedRemoveBg"
                  					@keypress="keyPressedRemoveBg"
								>
							</v-text-field>
							<!-- <p class="pt-2" v-for="err in error" :key="err.id">
								<span style="color:red" > {{err}} </span>
							</p> -->
							</v-col>
						</v-row>
					</div>
                </v-form>
            </v-card-text>

			<v-card-actions>
				<v-spacer></v-spacer>
				<v-btn
					color="teal"
					text
					@click="closeRenameModal"
					class="cancel-edit-contact-details"
					>
					Cancel
				</v-btn>
				<v-btn class="btn-save-create-service v-btn v-btn--text theme--light v-size--default"
					justify="space-around"
					depressed
					dark
					@click="saveAcord"
					>
					Save
				</v-btn>
			</v-card-actions>


          </v-card>
        </v-dialog>
		<v-snackbar class="success-alert snackbarToast" v-model="snackbar" :timeout="timeout">
		<v-icon class="success-alert-icon pr-1">mdi-checkbox-marked-circle</v-icon>
		<span class="success-alert-text" v-if="deleteAcordStatus">{{ acordMessageText }}</span>
		<span class="success-alert-text" v-if="acordRenameSnackbar">{{ acordMessageText }}</span>
		<span class="success-alert-text">{{ text }}</span>
		</v-snackbar>

	</div>
</script>

<script>
    Vue.component('<?=ComponentTools::componentName(__FILE__)?>', {
        template: '#<?=ComponentTools::templateName(__FILE__)?>',
        props: ['fieldData', 'contactId', 'value'],
        data: function(){
            return {
				base_url:base_url,
				attachListing: '',
				items: {},
				action:'Download',
				headers: [
					{ text: 'FILE NAME', align: 'left', filterable: false, value: 'file' },
					{ text: 'USER', value: 'user' },
					{ text: 'DATE ADDED', value: 'date_added' },
					{ text: 'ACTION', value: 'action', sortable: false},
				],
				page: 1,
				itemsPerPage: 10,
				perPageChoices: [
				//   {text:'5 records/page' , value: 5},
				  {text:'10 records/page' , value: 10},
				  {text:'20 records/page' , value: 20},
				],
				dialog: false,
				linkPolicyDialog: false,
				snackbar:false,
				acord_forms:[],
				searchAcords:'',
				form_id: '',
				policyTypes: [],
				policy_type:'',
				timeout: 3000,
				valid:'',
				text:'',
				checkIvansStatus: false,
				acordFormSelected:false,
				policySelected:false,
				acord_form_id : '',
				download_form_id: '',
				closeAttachPolicydialog: false,
				PolicyData:'',
				opportunity_id:'',
				action_url : _ACORD_APPLICATION_FORM_FILL,
				searchInProgress: false,
				deleteAcordDialog : false,
				acordMessageText: '',
				deleteAttachmentStatus:false,
				acordId:'',
				deleteAcordStatus: false,
				acordRenameSnackbar: false,
				display_name_or_file: '',
				renameRules: [
					v => !!v || 'Rename Acord is required',
				],
				show:false,
				display_name_or_file_id:'',
				renameDialog: false,
				newFileName : '',
				AccessToken : '',
                intialAcordLoader: false,
			}
        },
		computed: {
		  totalRecords() {
			  return this.items.length
		  },
		  pageCount() {
			  return this.totalRecords / this.itemsPerPage
		  },
		  computedAction(){
			  return this.action_url;
		  }

		},
		methods:{
			acordForms: function(data){
                this.intialAcordLoader = false;
				if(data['data']){
					this.items  = (data['data']['data']['Contacts.getAcordForms'][this.contactId]);
				}else{
					this.items  = (data['Contacts.getAcordForms'][this.contactId]);
					if(data['Contacts.getAcordForms'][this.contactId]['checkIvansStatus'] == 1){
					this.checkIvansStatus = true;
				}
				}
				
			},
			createAcordDialog:function()
			{
				DataBridge.get('Contacts.getAcordFormsList', this.contactId, '*', this.populateAcordFormList);
				this.dialog = true;
				this.form_id = '';
			},
			close:function()
			{
				this.dialog = false;
				this.acordFormSelected = false;
			},
			populateLinkPoliciesList:function(data)
			{
				var vm = this;
				vm.policyTypes = (data['Contacts.getPolicyTypes'][this.contactId]);
			},
			populateAcordFormList:function(data)
			{
				var vm = this;
				vm.acord_forms  = (data['Contacts.getAcordFormsList'][this.contactId]);
			},
			openLinkPolicyDialog: function(id,file_name)
			{
				DataBridge.get('Contacts.getPolicyTypes', this.contactId,'*', this.populateLinkPoliciesList);
				this.acord_form_id = id;
				this.policy_type = '';
				this.linkPolicyDialog = true;
			},
			closeLinkPolicyModal: function(data)
			{

				if(this.policy_type != '')
				{
					this.closeAttachPolicydialog = true;
				}else
				{
					this.$refs.policyAttachmentform.reset()
					this.linkPolicyDialog = false;
				}

			},
			enableNextBtn:function()
			{
				if((this.form_id != '' && this.form_id != null) || this.form_id === 0)
				{
					this.acordFormSelected = true;
				}else
				{
					this.acordFormSelected = false;
				}
			},
			accordFormSave(response)
			{

				var result =  JSON.parse(response['data']['data']);
				if(result['status'] == true || result['status'] == '1')
				{	this.dialog = false;
					this.form_id = '';
					this.acordFormSelected = false;
                    this.AccessToken = '';
                    if(result['AccessToken'] != null && result['AccessToken'] != '' && result['AccessToken'] != 'null' && typeof(result['AccessToken']) != 'undefined')
					{
                        this.AccessToken = result['AccessToken'];
                         $('.access_token').val(this.AccessToken);
                    }
					if(result['policyData'] != null && result['policyData'] != '' && result['policyData'] != 'null' && typeof(result['policyData']) != 'undefined')
					{
						this.policyData = JSON.stringify(result['policyData']);
						$('#open_acord_form_textarea').val(this.policyData);

					}else{
						this.policyData = '';
						$('#open_acord_form_textarea').val(this.policyData);
                        $('.AccessToken').val(this.AccessToken);
					}
					if(this.opportunity_id ==''){
						this.action_url = _ACORD_APPLICATION_FORM_FILL;
						 setTimeout(function(){
							 $('#open_filled_form').submit();
						 }, 1000)

					}else{
						if(result['policy_guid'] == 1){
						if(result['create_type'] == 'Certificate'){
							this.action_url =  _REQUEST_POLICY_CERTIFICATE;
							 setTimeout(function(){
							 $('#open_filled_form').submit();
						 }, 1000)
						}else{
							this.action_url = _ACORD_FORM_POLICY_FILL;
							 setTimeout(function(){
							 $('#open_filled_form').submit();
						 }, 1000)
						}
						}else{
							this.action_url = _ACORD_APPLICATION_FORM_FILL;
							 setTimeout(function(){
							 $('#open_filled_form').submit();
						 }, 1000)
						}
					}

				}

			},
			next:function()
			{
				var acord_form = this.form_id.acord_name;
				var postData = {
					'form_id' : this.form_id,
					'contact_id' : this.contactId,
					'acord_form' : acord_form,
				}
				DataBridge.save('Contacts.nextAcordForm',postData,this.accordFormSave);
			},
			enableAttachBtn:function()
			{
				if(this.policy_type != '')
				{
					this.policySelected = true;
				}else
				{
					this.policySelected = false;
				}
			},
			accordFormMap:function(response)
			{
				var result =  JSON.parse(response['data']['data']);
				if(result['status'] == true || result['status'] == '1')
				{
					this.text = 'Acord attached successfully!'
					this.deleteAcordStatus= false;
					this.acordRenameSnackbar= false;
					this.snackbar = true;
					this.linkPolicyDialog = false;
					

				}
			},
			attachPolicyToAccord:function()
			{
				var postData = {
					'contact_id': this.contactId,
					'opportunity_id' : this.policy_type,
					'acord_form_id': this.acord_form_id,
				}
				DataBridge.save('Contacts.mapAcordFormToPolicy',postData,this.accordFormMap);
			},
			accordAppDownload:function(response)
			{
				var result =  JSON.parse(response['data']['data']);
				if(result['status'] == true || result['status'] == '1')
				{
					const blob = new Blob([result['view_url']], { type: 'application/pdf' })
					const link = document.createElement('a')
					link.href = URL.createObjectURL(blob)
					link.download = 'Acord_form_#'+this.download_form_id+'.pdf'
					link.click()
					URL.revokeObjectURL(link.href)
				}
			},
			downloadAcordApplication:function(id)
			{
				var token = $("meta[name='csrf_token']").attr("content");
				$('#_csrfToken').val(token);
				$('#acord_form_id').val(id);
				setTimeout(function(){
					$('#download_acord_form').submit();
				}, 1000)

				// this.download_form_id = id;
				// var postData = {
				// 	'form_id' : id,
				// 	'contact_id' : this.contactId
				// }
				// DataBridge.save('Contacts.downloadAcordApplication',postData,this.accordAppDownload);

			},
			accordDuplicate:function(response)
			{
				var result =  JSON.parse(response['data']['data']);
				if(result['status'] == true || result['status'] == '1')
				{

					this.AccessToken = '';
                    if(result['AccessToken'] != null && result['AccessToken'] != '' && result['AccessToken'] != 'null' && typeof(result['AccessToken']) != 'undefined')
					{
                        this.AccessToken = result['AccessToken'];
                         $('.access_token').val(this.AccessToken);
                    }
					if(result['PostData'] != null && result['PostData'] != '' && result['PostData'] != 'null' && typeof(result['PostData']) != 'undefined')
					{
						this.policyData = JSON.stringify(result['PostData']);
						$('#open_acord_form_textarea').val(this.policyData);
					}else{
						this.policyData = '';
						$('#open_acord_form_textarea').val(this.policyData);
						$('.access_token').val(this.AccessToken);
					}
					this.action_url = _DUPLICATE_ACORD_FORM;
					setTimeout(function(){
					$('#open_filled_form').submit();
				}, 1000)
				}else
				{
					swal({
					title: "warning",
					text: result['response'],
					html: true,
					type: "warning",
					showCancelButton: false,
					confirmButtonColor: "#DD6B55",
					confirmButtonText: "OK",
					closeOnConfirm: true,
					timer: 5000
					});
				}
			},
			postDuplicateAccord:function(id)
			{
				var postData = {
					'acord_form_id' : id,
					'contact_id' : this.contactId
				}
				DataBridge.save('Contacts.duplicateAccordForm',postData,this.accordDuplicate);
			},
			keepEditing:function()
			{
				this.closeAttachPolicydialog = false;
			},
			closeWithoutSave:function()
			{
				this.closeAttachPolicydialog = false;
				this.linkPolicyDialog = false;
			},
			searchAcordForms:function()
			{
				if(this.searchAcords != '')
				{
					this.searchInProgress = true;
				}else
				{
					this.searchInProgress = false;
				}
				var postData = {
					'acord': this.searchAcords
				}
				DataBridge.save('Contacts.getAutocompleteAcordFormsList',postData, this.populateAcordFormsData);
			},
			populateAcordFormsData:function(response){
				this.searchInProgress = false;
				// this.acord_forms =  (response['data']['data']);
				var result =  JSON.parse(response['data']['data']);
				if(result['status'] == 1){
				    this.acord_forms =  (result['acord_forms']);
				}else{
				    this.acord_forms = [];
				}
			},
			deleteAcord: function(event)
			{
				this.acordId=event.currentTarget.id;
				this.deleteAcordDialog = true;
			},
			deleteAcordForm : function()
			{
				var acord = this.acordId;
				DataBridgeContacts.save('Contacts.deleteAcord',acord,this.confirmDeleteAcord);
			},
			confirmDeleteAcord(response)
			{
				let vm = this;
				var result =  JSON.parse(response['data']['data']);
				if(result['status'] == true || result['status'] == '1')
				{
					this.text = '';
					this.deleteAcordDialog = false;
				
					this.deleteAcordStatus=true;
					this.acordRenameSnackbar = false;
					setTimeout(
					function()
					{
						DataBridgeContacts.save('Contacts.getAcordForms', vm.contactId, vm.acordForms,vm.page,vm.itemsPerPage);
						vm.acordMessageText = result['message'];
						vm.snackbar=true;
					}, 2000);
				}

			},
			editRenameItem (id,display_name_or_file,items) {
				this.acordId = id
				this.display_name_or_file = display_name_or_file
				this.items_compare_list = items
				this.renameDialog = true
				this.show=true
				this.deleteAcordStatus=false;
			},
			saveAcord() {
				var displayName = this.display_name_or_file.trim();
				if(displayName == '' || displayName == undefined || displayName == null)
				{
					return false;
				}
				this.newFileName = displayName;
				var updateData = {
				'acord_id':this.acordId,
				'name': displayName
				}
				DataBridge.save('Contacts.updateAcordName',updateData,this.saveAcordName);
				this.closeRenameModal()
			},
			saveAcordName:function(data){
				var result =  JSON.parse(data['data']['data']);
				if(result['status'] == true || result['status'] == '1')
				{
					for(let key in this.items){
						if(this.items[key]['id'] == this.acordId)
						{
							this.items[key]['file'] = this.newFileName
						}
					}
					// this.items = Object.values(this.items).map(item => {
					// 	if(item.id == this.acordId)
					// 		item.file = this.newFileName
					// 	return item;
					// });
					this.text = '';
					this.acordMessageText = result['message'];
					this.snackbar = true;
					this.deleteAcordStatus=false;
					this.acordRenameSnackbar = true;
					this.renameDialog = false;
					// setTimeout(()=> {
					// location.reload();
					//
					// }, 3000)
				}
			},
			keyPressedRemoveBg(){
    		this.show=false
      	 	},
			closeRenameModal(){
				this.renameDialog = false;
			}
		},
	mounted: function(){
        this.intialAcordLoader = true;
		DataBridge.get('Contacts.getAcordForms', this.contactId,'*', this.acordForms,this.page,this.itemsPerPage);
	},
    });
</script>
