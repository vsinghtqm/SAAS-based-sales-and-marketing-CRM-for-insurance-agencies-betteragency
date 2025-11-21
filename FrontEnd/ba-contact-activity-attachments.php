<?php

use ComponentLibrary\Lib\ComponentTools;

?>
<style>
.drop-attach.dropZone {
  width: 100%;
  height: 160px;
  position: relative;
  border: 1px dashed rgba(58, 53, 65, 0.68)
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

.dropZone .dropZone-title {
    color: rgba(58, 53, 65, 0.87);
    font-size: 1rem;
    font-weight: 600;
    margin: 5px 0;
    display: block;
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
  color: rgba(58, 53, 65, 0.68);
  font-size: 14px;
  font-weight: 400;
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
.fixed-header {
    position: sticky;
    top: 0px;
    width: -webkit-fill-available;
    z-index: 1;
    background-color: #e0e0e0;
}
.attachment-table-main.ivans-attachment tr th, .attachment-table-main.ivans-attachment tr td {
	vertical-align: middle;
}


.ivans-attachment.theme--light table tbody td span a {
    /*text-decoration: underline;*/
    color: rgba(58, 53, 65, 0.87);
    font-size: 1rem;
    text-overflow: ellipsis;
    width: 220px;
    overflow: hidden;
    white-space: nowrap;
    display: block;
}
.ivans-attachment.theme--light .v-data-table__wrapper {
    overflow-y: auto;
}
.ivans-attachment.theme--light table tbody tr td {
    font-size: 1rem !important;
}
/*dropdown-menu.custom-dropdown-attachment.show {
    left: 0px !important;
    top: 20px !important;
}*/
.dropdown-menu.custom-dropdown-attachment {
	left: -40px !important;
    top: 20px !important;
    transform: unset !important;

}
.acord-tab-max-height.attachment-table-main table tbody {
    height: calc(1080px - 430px) !important;
    max-height: calc(1080px - 430px) !important;
}.acord-tab-max-height .v-data-table__wrapper {
    max-height: unset;
    overflow-y: unset;
    min-height: unset;
}

.v-data-table.ivans-attachment.attachment-tab-max-height.theme--light .v-data-table__wrapper {
    min-height: 300px;
    max-height: 477px;
}
.v-data-table.attachment-tab-max-height{
    min-height:300px;
}

</style>
<script type="text/x-template" id="<?=ComponentTools::templateName(__FILE__)?>">
	<div class="mb-5 pb-3">

        <div class="d-flex custom-grey">
        <h4 class="upcoming-heading">Attachments</h4>
<!--            <v-btn @click="onPickFile" class="ml-auto btn btn-outline-success btn-round btn-position" style="background-color:transparent;" >-->
<!--			<v-icon class="plus-icon-css">mdi-plus</v-icon><span class="text-success">Add Attachment</span>-->
<!--            </v-btn>-->
			<input type="file" style="display:none" ref="fileInput" accept="image/*" @change="onFilePicked">
        </div>
		
		<br>
		
		<template>
      <v-toolbar
        flat
      >
        <v-dialog
          v-model="dialog"
          max-width="559px"
        >
          <v-card style="min-height:184px;">
            <v-card-title class="text-h6 common-title-popup">
              Rename Attachment
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
                  @click="keyPressedRemoveBg"
                  @keypress="keyPressedRemoveBg"
									label="File name"
									v-model="display_name_or_file"
									value="Rename File"
									outlined
                                    :rules="renameRules"
									:maxlength="maxAttachmentValue"
                                    autofocus
                                    required
									>
                      </v-text-field>
                      <p class="pt-2" v-for="err in error" :key="err.id">
                   <span style="color:red" > {{err}} </span>
                </p>
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
									@click="close"
									class="cancel-edit-contact-details"
									>
									Cancel
									</v-btn>
									<v-btn v-if="!renameLoader"  class="btn-save-create-service v-btn v-btn--text theme--light v-size--default"
										justify="space-around"
										depressed
										dark
										@click="save()"
									>
										Save
									</v-btn>

                  <div v-else class="text-center" >
                      <img :src="`${base_url}/img/loader.gif`" style="width: 60px; height: 35px"/>
                  </div>
								</v-card-actions>


          </v-card>
        </v-dialog>
      </v-toolbar>
    </template>
	<div class="row">
	<div class="col-12">
		<div id="app">
			<div  :class="['dropZone drop-attach', dragging ? 'dropZone-over' : '']" @dragover="dragover" @dragleave="dragleave" @drop="drop">
				<div v-if="!addAttachmentLoader" class="dropZone-info" @drag="onFilePicked">
				<div><img :src= "`${base_url}/img/vector.svg`" alt=""></div>
				<span class="fa fa-cloud-upload dropZone-title"></span>
				<span class="dropZone-title">Drag and drop file or click to upload</span>
				<div class="dropZone-upload-limit-info">
				<div>docx, xls, xlsx, doc, png, jpeg, pdf, eml, pst, ost, mp4, m4a, wav</div>
				<div>maximum file size 64 MB</div>
				</div>
			</div>
            <div v-else class="text-center" style="margin-top: 80px;">
                <img :src="`${base_url}/img/loader.gif`" style="width: 60px; height: 35px"/>
            </div>
			<input type="file" multiple name="file" id="fileInput" class="hidden-input" @change="onFilePicked" ref="fileInput"/>
			</div>
		</div>
		<p class="error-msg w-100 d-block mt-3" style = 'text-align: center;'>{{ imageError }}</p>
        <div class="mt-5" >
            <v-simple-table class="elevation-1 table-custom-contact ivans-attachment srcollbottom  attachment-tab-max-height">
						<template v-slot:default>
						<thead>
							<tr class="fixed-header">
							<th class="text-left" style="color: #696969;font-style: normal;font-size: 12px;line-height: 16px;font-weight: 500;font-family: Roboto;">
								IVANS
							</th>
							<th class="text-left" style="color: #696969;font-style: normal;font-size: 12px;line-height: 16px;font-weight: 500;font-family: Roboto; width: 220px">
								FILE NAME
							</th>
							<th class="text-left" style="color: #696969;font-style:normal;font-size: 12px;line-height: 16px;font-weight: 500;font-family: Roboto;">
								USER
							</th>
							<th class="text-left" style="color: #696969;font-style: normal;font-size: 12px;line-height: 16px;font-weight: 500;font-family: Roboto;">
								DATE ADDED
							</th>
							<th class="text-left" style="color: #696969;font-style: normal;font-size: 12px;line-height: 16px;font-weight: 500;font-family: Roboto;">
								ACTION
							</th>
							</tr>
						</thead> 
						<tbody ref="attachmentTableBody" >
							<tr v-if="!intialAttachmentLoader" v-for="(item, index) in items" :key="index" >
							<td style="color: #696969;font-style: open sans;font-size: 14px;line-height: 16px;font-weight: 400; font-family: Roboto;" ><span v-if="item.attachment_guid != null"><img :src= "`${base_url}/img/policy-download.svg`" alt=""></span><span v-else></span></td>
							<td style="color: #696969;font-style: open sans;font-size: 14px;line-height: 16px;font-weight: 400; font-family: Roboto;" ><span v-if="item.file != ''"><a class="" style="width: 360px" :href="`${base_url}s3/view?type=contact&id=${item.id}`" target="_blank" :title="`${item.file}`">{{ item.file }}</a> </span><span v-else>--</span></td>
							<td style="color: #696969;font-style: open sans;font-size: 14px;line-height: 16px;font-weight: 400; font-family: Roboto;"><span v-if="item.user != ''">{{ item.user }}</span><span v-else>--</span></td>
							<td style="color: #696969;font-style: open sans;font-size: 14px;line-height: 16px;font-weight: 400; font-family: Roboto;"><span v-if="item.date_added != '' && item.date_added != null">{{ item.date_added }}</span><span v-else>--</span></td>
							<td><div class="dropdown attachement-dropdown">
							<i class="fa fa-ellipsis-h " id="dropdownMenuButton" data-toggle="dropdown" ></i>
							<div class="dropdown-menu custom-dropdown-attachment" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item" :href="`${base_url}s3/download?type=contact&id=${item.id}`">Download</a><a class="dropdown-item" @click="openLinkPolicyDialog(item.id,item.file)">Link to policy</a>
							<!-- <a class="dropdown-item" :href="`${base_url}s3/view?type=contact&id=${item.id}`" target="_blank">View</a> -->
							<a href="#" icon text class="dropdown-item" x-large @click="editRenameItem(item.id,item.contact_id,item.file,items)"   style="" > Rename </a><a class="dropdown-item text-danger mb-2" :id="`${item.id}`" @click="deleteAttachment($event)" >Delete</a>
							 </div>
							</div>
							</td>
							</tr>
                            <tr class="position-center" v-if="items.length == 0 && !intialAttachmentLoader">
                                <td colspan="5" class="text-center">
                                    No records found.
                                </td>
                            </tr>
                            <tr class="position-center" v-if="intialAttachmentLoader">
                                <td colspan="4" class="text-center ">
                                    <img :src="`${base_url}/img/loader.gif`" style="width: 60px; height: 35px" />
                                </td>
                            </tr>
						</tbody>
						</template>
					</v-simple-table>
        </div>
		</div> </div>
		<v-dialog
		v-model="deleteAttachmentDialog"
		persistent
		max-width="600"
		max-height="184"
		>
		<v-card style="height:184px;">
		<v-card-title class="text-h6 delete-policy-heading">
		Delete Attachment
		</v-card-title>
		<v-card-text class="delete-policy-text">Are you sure you want to delete this attachment? This action can not be undone.</v-card-text>
		<v-card-actions>
		<v-spacer></v-spacer>
		<v-btn class="btn-cancel"
			color="#757575"
			text
			@click="deleteAttachmentDialog = false"
		>
			No, Cancel
		</v-btn>
		<v-btn v-if="!deleteAttachmentLoader" class="btn-delete"
			color="#F65559"
			text
			@click="deleteSingleAttachment"
		>
			Yes, Delete
		</v-btn>
            <div v-else class="text-center" >
                <img :src="`${base_url}/img/loader.gif`" style="width: 60px; height: 35px"/>
            </div>
		</v-card-actions>
		</v-card>
		</v-dialog>
		<template>
      <v-toolbar
        flat
      >
        <v-dialog
          v-model="linkPolicyDialog"
          max-width="529px"
        >
          <v-card style="height:216px;">
            <v-card-title>
              <span class="link-to-policy-title">Link to policy</span>
            </v-card-title>
              <v-card-text class="pt-0">

                  <v-row>
                 	<v-col cols="12">
						<p class="label-link pb-2">[{{ attachmentName }}] links to...</p>
					<v-form ref="policyAttachmentform" lazy-validation>
					 <v-select
							v-model="policy_type"
							placeholder="Select line of business..."
							:items="activePolicies"
							item-text="name"
							item-value="id"
							dense
							outlined
							:rules="[v => !!v || 'Item is required']"
							required
							:menu-props="{ top: false, offsetY: true }"
						>
                     <template v-slot:item="{ item }">
                        <span class="select-option-height"> {{ item.policy_type}} <span class="select-option-policy-number" v-if="item.policy_number">{{item.policy_number}}</span></span>
                     </template>
                     </v-select>
					</v-form>
                	</v-col>
                  </v-row>

              </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn class="cancel-edit-contact-details"
			  	color="teal"
                text
                @click="closeLinkPolicyModal"
              >
                Cancel
              </v-btn>
              <v-btn v-if="!policyLoader" class="btn-save-create-service v-btn v-btn--text theme--light v-size--default"
				justify="space-around"
				depressed
				dark
                @click="attachmentPolicySave"
              >
                Save
              </v-btn>
                <div v-else>
                    <img :src="`${base_url}/img/loader.gif`" style="width: 60px; height: 35px"/>
                </div>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-toolbar>
    </template>

		<v-snackbar class="success-alert snackbarToast" v-model="snackbar" :timeout="timeout">
		<v-icon class="success-alert-icon pr-1">mdi-checkbox-marked-circle</v-icon>
			<span class="success-alert-text" v-if="deleteAttachmentStatus">{{ text }}</span>
			<span class="success-alert-text" v-if="attachmentSnackbar">{{ attachmentText }}</span>
			 <span class="success-alert-text" v-if="attachmentRenameSnackbar">{{ renameText }}</span>
			 <span class="success-alert-text" v-if="linkAttachmentSnackbar">{{ linkAttachmentText }}</span>
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
				deleteAttachmentDialog : false,
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
				loading: true,
				image:[],
				imageName:'',
				imageError:'',
				attachmentText: 'Attachment added successfully!',
				fileName: '',
				showHideImageName:false,
				snackbar:false,
				attachmentSnackbar:false,
    			isDragging: false,
				dragging: false,
				timeout : 3000,
				attachmentId:'',
				maxAttachmentValue:75,
				text:'Attachment deleted successfully!',
				deleteAttachmentStatus:false,
				show:false,
				valid: true,
				renameRules: [
					v => !!v || 'Rename Attachment is required',
				],
				display_name_or_file_class:'',
				display_name_or_file_id:'',
				listRenamevalue:[],
				error:[],
				items_compare_list:[],
				id:'',
				contact_id:'',
				display_name_or_file:'',
				dialog: false,
				snackbar:false,
				attachmentRenameSnackbar: false,
				renameText:'Attachment renamed successfully!',
				timeout: 3000,
				activePolicies:[],
				linkPolicyDialog: false,
				linkAttachmentSnackbar :false,
				linkAttachmentText : 'Attachment linked successfully!',
				attachmentName: '',
				attachmentId: '',
				policy_type:'',
                addAttachmentLoader:false,
                policyLoader:false,
                renameLoader:false,
                deleteAttachmentLoader:false,
                intialAttachmentLoader: false,
                showSnackbar : false,
			}
        },
		computed: {
		  totalRecords() {
			  return this.items.length
		  },
		  pageCount() {
			  return this.totalRecords / this.itemsPerPage
		  },
		},
		methods:{
			attachments: function(data){
				var vm = this;
				if(data['data'])
				{
					this.items  = (data['data']['data']['ContactAttachments.getAttachments'][this.contactId]);
				}else{
					this.items  = (data['ContactAttachments.getAttachments'][this.contactId]);
				}
                setTimeout(
                    function()
                    {
                        vm.addAttachmentLoader = false;
                        vm.deleteAttachmentStatus = false;
                        vm.addAttachmentLoader = false;
                        vm.attachmentSnackbar = false;
                        vm.dialog = false;
                        vm.attachmentRenameSnackbar = false;
                        vm.linkAttachmentSnackbar = false;
                        vm.renameLoader = false;
                        vm.deleteAttachmentDialog = false;
                        vm.deleteAttachmentLoader = false;
                    }, 2000);

                this.intialAttachmentLoader = false;

			},
			reloadAttachmentsAfterDelete: function(data){
				var vm = this;
				vm.deleteAttachmentDialog = false;
                vm.deleteAttachmentLoader = false;
				vm.snackbar = true;
				vm.deleteAttachmentStatus = true;
				if(data['data'])
				{
					this.items  = (data['data']['data']['ContactAttachments.getAttachments'][this.contactId]);
				}else{
					this.items  = (data['ContactAttachments.getAttachments'][this.contactId]);
				}
                setTimeout(
                    function()
                    {
						vm.snackbar = false;
                        vm.deleteAttachmentStatus = false;           
                    }, 2000);

			},
			onPickFile:function()
			{
                this.addAttachmentLoader = false;
				this.$refs.fileInput.click()
			},
			dragover(e) {
			e.preventDefault();
			this.isDragging = true;
			},
			dragleave() {
			this.isDragging = false;
			},
			drop(event) {
				event.preventDefault();
				this.$refs.fileInput.files = event.dataTransfer.files;
				this.onFilePicked(event);
      			this.isDragging = false;
    		},
			onFilePicked:function(event)
			{
				const files = event.target.files;
				var validFile = false;
				var formData = new FormData();
				for (var i = 0; i < files.length; i++) {
					let filename = files[i].name;
					this.fileName = files[i].name;
					var re = /(\.pdf|\.docx|\.xls|\.xlsx|\.doc|\.png|\.jpg|\.jpeg|\.eml|\.pst|\.ost|\.mp4|\.mp3|\.m4a|\.wav|\.txt|\.csv|\.msg)$/i;
					if(filename.lastIndexOf('.') <= 0)
					{
						this.showHideImageName = false;
						this.imageError = 'Please select a valid file!';
						this.addAttachmentLoader = false;
						return;
					}else if(files[i].size >64000000)
					{
						this.showHideImageName = false;
						this.imageError = 'File size should be within the specified limits of 0 MB or 64 MB';
						this.addAttachmentLoader = false;
						return;
					}else if (!re.exec(files[i].name)) {
						this.showHideImageName = false;
						this.imageError = 'Please choose only pdf, xls, png, jpeg, doc, eml, pst, ost, wav, mp3, m4a, mp4, csv, msg, txt files to upload!';
						this.addAttachmentLoader = false;
						return;
					}
                    else{
						if(files[i].name){
							this.addAttachmentLoader = true;
						}
						validFile = true;
						this.imageError = '';
						this.imageName = files[i].name;
						const fileReader = new FileReader()
						fileReader.addEventListener('load',() => {
							this.imageUrl = fileReader.result
						})
						fileReader.readAsDataURL(files[i])
						this.image[i] = files[i]
						this.showHideImageName = true;
						formData.append(i, files[i]);
					}
				}
				if(validFile == true){
				formData.append('contact_id', this.contactId);
				var token = $("meta[name='csrf_token']").attr("content");
				url = base_url + 'Attachments/uploadAttachments';
				let vm = this;
				axios.post(url,formData,{
				headers: {
					'Content-Type': 'multipart/form-data',
					'X-CSRF-Token': token
				}
				}).then( (response) => {
					if(response['data']['status'] == 1)
					{
						var unUploadedAttachments = response['data']['unUploadedAttachments'];
                        if(unUploadedAttachments.length > 0)
                        {   
                            this.showSnackbar = false;
                            let errortext =  'These files are empty and could not be uploaded:\n';
                            $.each(unUploadedAttachments, function(index, value){
                                errortext += value + '\n';
                            });
                            swal('Saved',errortext, "success");
							this.addAttachmentLoader = false;
                        }
                        else
                        {
                            this.showSnackbar = true;
                            this.snackbar = true;
                            this.attachmentSnackbar = true;
                        }
					}else{
						var unUploadedAttachments = response['data']['unUploadedAttachments'];
						if(unUploadedAttachments.length > 0)
                        {   
                            this.showSnackbar = false;
                            let errortext =  'These files are empty and could not be uploaded:\n';
                            $.each(unUploadedAttachments, function(index, value){
                                errortext += value + '\n';
                            });
                            swal('',errortext, "warning");
							this.addAttachmentLoader = false;
                        }
						this.snackbar = false;
						this.attachmentSnackbar = false;
						this.errorFound=1;
						this.error_message = result['message'];
					}

					}).finally(() => {                    
                        if(this.showSnackbar === true)
                        {
                            this.snackbar = true;
                            this.attachmentSnackbar = true;
                        }
                        else
                        {
                            this.snackbar = false;
                            this.attachmentSnackbar = false;
                        }
							this.image = '';
							setTimeout(
							function()
							{
								let objectData = {
									'contactId' : vm.contactId,
									'start' : vm.page,
									'limit' : vm.itemsPerPage
								}
								DataBridgeContacts.save('ContactAttachments.getAttachments', objectData, vm.attachments);
							}, 3000);

					});
				}

				
},
			deleteAttachment : function(event)
			{
				this.attachmentId=event.currentTarget.id;
				this.deleteAttachmentDialog = true;
			},
			deleteSingleAttachment : function()
			{
				var currentAttachment = this.attachmentId;
                this.deleteAttachmentLoader = true;
				DataBridge.save('ContactAttachments.deleteAttachments',currentAttachment,this.confirmDeleteAttachment);
			},
			confirmDeleteAttachment(response)
			{

				var result =  JSON.parse(response['data']['data']);
				if(result['status'] == true || result['status'] == '1')
				{
					let vm = this;
					setTimeout(
					function()
					{
						let objectData = {
							'contactId' : vm.contactId,
							'start' : vm.page,
							'limit' : vm.itemsPerPage
						}
						DataBridgeContacts.save('ContactAttachments.getAttachments', objectData, vm.reloadAttachmentsAfterDelete);
					}, 3000);
				}

			},
			keyPressedRemoveBg(){
    		this.show=false
      	 	},
			validate () {
			this.$refs.form.validate()
			},
			editRenameItem (id,contact_id,display_name_or_file,items) {
				this.error=[]
				this.id = id
				this.contact_id = contact_id
				this.display_name_or_file = display_name_or_file
				this.items_compare_list = items
				this.dialog = true
				this.show=true
			},
			close () {
				this.dialog = false
			},
			saveFileName:function(data)
			{
				let vm = this;
				setTimeout(()=> {
					let objectData = {
						'contactId' : vm.contactId,
						'start' : vm.page,
						'limit' : vm.itemsPerPage
					}

					DataBridgeContacts.save('ContactAttachments.getAttachments', objectData, vm.getAttachments);

				}, 3000)
			},
				save() {

                    if(this.display_name_or_file == ""){
                        return;
                    }
					const data1=(this.items_compare_list);
					autofocus = true;
					this.error=[];
					var listRenameId=[];
					var ii =0
					data1.map((value,ii)=>{
					this.listRenamevalue[ii]=value.file;
					listRenameId[ii]=value.id
						ii++
					})
					var alreadyExist = false;
					var existName = '';
						for (var i = 0; i < this.listRenamevalue.length; i++) {
							if(this.listRenamevalue[i] === this.display_name_or_file && listRenameId[i] != this.id) {
								existName = this.listRenamevalue[i];
								alreadyExist = true;
							}
						}
						if(alreadyExist == true)
						{
							this.error.push('Name '+ existName  +' already exist');
						}else if(alreadyExist == false)
						{
							var updateData = {
							'attachement_id':+this.id,
							'contact_id':this.contact_id,
							'name': this.display_name_or_file
							}
							this.renameLoader = true;
							DataBridge.save('ContactAttachments.updateAttachment',updateData,this.saveFileName);
							// this.close()
						}
				},
				populateLinkPoliciesList:function(data)
			{
				var vm = this;
				vm.activePolicies  = (data['ContactOpportunities.getPoliciesToLinkAttachment'][this.contactId]);
				// console.log(vm.activePolicies);
			},
			closeLinkPolicyModal: function(data)
			{
				this.attachmentName = '';
				this.linkPolicyDialog = false;
                this.$refs.policyAttachmentform.reset();
			},
			openLinkPolicyDialog: function(attachment_id,file_name)
			{
				DataBridge.get('ContactOpportunities.getPoliciesToLinkAttachment', this.contactId, '*', this.populateLinkPoliciesList);
				this.attachmentName = file_name;
				this.attachmentId = attachment_id;
				this.linkPolicyDialog = true;
                if(this.$refs.policyAttachmentform != undefined) {
                    this.$refs.policyAttachmentform.reset();
                }
			},
			saveAttachmentPolicy:function(response)
			{
				var result =  JSON.parse(response['data']['data']);
				if(result['status'] == true){
					let vm = this;
                    setTimeout(
					function()
					{
                        vm.policyLoader = false;
						let objectData = {
						'contactId' : vm.contactId,
						'start' : vm.page,
						'limit' : vm.itemsPerPage
					}
						DataBridgeContacts.save('ContactAttachments.getAttachments', objectData, vm.attachments);
						vm.linkPolicyDialog = false;
                        vm.linkAttachmentSnackbar = true;
                        vm.snackbar = true;
					}, 3000);
				}
			},
			attachmentPolicySave: function()
			{
				var linkPolicyData = {
						'policy_type_id': this.policy_type,
						'contact_id': this.contactId,
						'attachment_id' : this.attachmentId
					};
				if(this.$refs.policyAttachmentform.validate())
				{
                    this.policyLoader = true;
					DataBridge.save('ContactOpportunities.savePolicyAttachment',linkPolicyData,this.saveAttachmentPolicy);
				}
			},
			getAttachments: function(data){
				var vm = this;
				vm.dialog = false;
				vm.snackbar = true;
				vm.attachmentRenameSnackbar = true;
				if(data['data'])
				{
					this.items  = (data['data']['data']['ContactAttachments.getAttachments'][this.contactId]);
				}else{
					this.items  = (data['ContactAttachments.getAttachments'][this.contactId]);
				}
                setTimeout(
                    function()
                    {
                        vm.addAttachmentLoader = false;
                        vm.deleteAttachmentStatus = false;
                        vm.attachmentSnackbar = false;
						vm.snackbar = false;
                        vm.attachmentRenameSnackbar = false;
                        vm.linkAttachmentSnackbar = false;
                        vm.renameLoader = false;
                        vm.deleteAttachmentDialog = false;
                        vm.deleteAttachmentLoader = false;
					}, 2000);

			}
		},
	mounted: function(){
        this.intialAttachmentLoader = true;
		let objectData = {
			'contactId' : this.contactId,
			'start' : this.page,
			'limit' : this.itemsPerPage
		}
        DataBridgeContacts.save('ContactAttachments.getAttachments', objectData, this.attachments);
	},
    });
</script>
