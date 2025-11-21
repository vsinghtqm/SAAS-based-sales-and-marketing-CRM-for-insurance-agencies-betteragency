<?php

use ComponentLibrary\Lib\ComponentTools;

?>
<style>
  .footer-extra-tags span.v-btn__content {
    gap: 9px;
    margin-bottom: 1px;
}


.footer-bg .v-input__control {
    box-shadow: 0px 2px 1px -1px rgba(58, 53, 65, 0.2), 0px 1px 1px rgba(58, 53, 65, 0.14), 0px 1px 3px rgba(58, 53, 65, 0.12);
}
.all-attachments {
    display: flex;
    justify-content: end;
    gap: 6px;
    margin-top: 10px;
    flex-wrap: wrap;
}
.all-attachments-reply {
    display: flex;
    justify-content: start;
    gap: 6px;
    margin-top: 10px;
}
    .name-row-padding {
        padding-top: 45px !important;
    }
    .footer-extra-tags{
        padding-top: 5px;
        text-transform: uppercase;
        color: #29AD8E;
        font-weight: 500;
        margin-left: 5px !important;
    }

    .footer-extra-tags .btn-sms-footer{
        box-shadow: none;
        color: #29AD8E;
    }
    /* .v-dialog__content.v-dialog__content--active{
        left: auto;
    } */
    .footer-bg{
        padding-bottom: 0px;
    }
    .btn-templates{
        padding:10px !important; background-color: #29AD8E !important; color: #fff !important; min-width: 40px !IMPORTANT; height: 24px !important; line-height: 24px !important; font-size: 14px !important; letter-spacing: 0.4px !important; font-weight: 500 !important; font-family: 'Roboto', sans-serif !important; border-radius: 5px !important;
    }
    .fix-template-header {
        position: sticky;	top: -1px; width: -webkit-fill-available; z-index: 1; background-color: #e0e0e0;
    }
    .scroll-container { 
        max-height: 600px; 
        overflow-y: auto; 
        overflow-x: hidden;  
        min-height: 600px !important;  
        height: 100%; 
        padding-bottom: 10px; 
    }
    .sms-attachments-chips{
        display: flex; 
        justify-content: end;
        flex-wrap: wrap;
        gap: 6px;
    }

    .sms-attachments-chips .v-chip.v-chip--no-color.theme--dark.v-size--default{
        background: #e0e0e0;
        margin-right: 5px;
    }

    .sms-attachments-chips .v-chip.v-chip--no-color.theme--dark.v-size--default .attach-card-icon.mdi.mdi-paperclip{
        z-index: 1;
    }
.v-card__text.pt-0.pb-1.slim-scroll.template-data {
    margin-bottom: 9%;
}
.v-card__actions.fixed-footer-modal.model-footer-fix.template-data-footer {
    position: absolute !important;
    margin-right: 25px;
}




.text-tab-max-height {
  height: calc(1080px - 575px) !important;
  margin-bottom: 20px;
  max-height: calc(1080px - 575px) !important;
  min-height: calc(1080px - 575px) !important;
}


.footer-bg .v-text-field.v-text-field--enclosed:not(.v-text-field--rounded)>.v-input__control>.v-input__slot {
    height: auto !important;
    
}

.footer-bg .v-text-field.v-text-field--enclosed:not(.v-text-field--rounded)>.v-input__control>.v-input__slot textarea{
    margin: 10px  10px  10px  0px ;
    padding: 10px;
    max-height: 180px !important;
    overflow-y: auto;
}
.v-application--is-ltr .v-textarea.v-text-field--solo .v-input__append-inner {
    margin-top: 15px !important;
}

@media (max-width: 1450px){

.text-tab-max-height {
  height: calc(1080px - 610px) !important;
  max-height: calc(1080px - 610px) !important;
  min-height: calc(1080px - 610px) !important;
}

@media (max-width: 842px){
.text-tab-max-height {
  height: calc(1080px - 646px) !important;
  max-height: calc(1080px - 646px) !important;
  min-height: calc(1080px - 646px) !important;
}


}
}

</style>
<script type="text/x-template" id="<?= ComponentTools::templateName(__FILE__) ?>">
    <v-card flat class="text-tab" style="background:#f5f5f5; margin-bottom: 10px;">
       <v-card-text >
        <div>
            <v-row justify="space-around">
                <v-col cols="12 row pl-8 pr-8 pt-5 pb-5" class="contact-name-row">
                <v-avatar class="avtar-intials reply-initials"
                    color="grey lighten-new"
                    size="40"
                    >{{contactName && contactName.slice(0,1)}}{{contactLastName && contactLastName.slice(0,1)}}</v-avatar>

                    <h3 class="mt-2 text-heading-tab">{{contactName || ""}} {{contactLastName || ""}}</h3>

					<!-- <v-row>
                    <v-col cols="12" class="text-right text-tab-right-icon">
					<v-icon> mdi-magnify</v-icon>
                    <v-icon class="ml-auto">mdi-dots-vertical</v-icon>
					</v-col>
					</v-row>    -->
                </v-col>
            </v-row>    

            <v-divider class="mb-5"></v-divider>
			<div class="scroll-container text-tab-max-height" id="scrollDivDown"   @scroll="onScrollSmsList">
                <v-row>    
                <v-col   cols="12" class="pr-3" v-for="(sms,index,key) in smsListing" :key="sms.id" >

                    <div v-if="sms.sms_date && key == 0" class="text-center my-2 font-weight-bold w-100 d-inline-block ">
                       {{sms.sms_date}} 
                    </div>
                   <div v-if="sms.sms_date && key > 0 && Object.values(smsListing)[key-1].sms_date != Object.values(smsListing)[key].sms_date" class="text-center my-2 font-weight-bold w-100 d-inline-block ">
                       {{sms.sms_date}} 
                    </div>
                  
				    <div class="">
                  
					<v-avatar class="avtar-icon avtar-intials float-right mr-5"
                        color="grey lighten-new"
                        size="32"
                        style="margin-bottom: -40px;"
                        v-if="sms.in_out==2 && key == 0 "
                        >{{ sms.user.first_name.slice(0,1)}}{{ sms.user.last_name.slice(0,1)}}</v-avatar>
                        <v-avatar class="avtar-icon avtar-intials float-right mr-5"
                        color="grey lighten-new"
                        size="32"
                        style="margin-bottom: -40px;"
                        v-if="sms.in_out==2 && key > 0 && (Object.values(smsListing)[key-1].in_out != Object.values(smsListing)[key].in_out || Object.values(smsListing)[key-1].sms_date != Object.values(smsListing)[key].sms_date )"
                        >{{ sms.user.first_name.slice(0,1)}}{{ sms.user.last_name.slice(0,1)}}</v-avatar>
                        
                    <v-alert dark class=" chat-color-policy" style="  text-align: right;    width: fit-content;max-width: calc(100% - 100px);display: block;float: right; margin-left: 30px;    margin-right: 70px;" v-if="sms.in_out==2">
                        {{ sms.message.replace(/<\/?[^>]+(>|$)/g, "")}}
                        <div class="all-attachments">
                            <span class="sms-attachments-chips">
                                <v-chip 
                                    v-if="sms.contact_communications_media"
                                    v-for="(smsAttachments, index) in sms.contact_communications_media"
                                    :key="index"
                                >
                                    <a :href="smsAttachments.url" target="_blank"> 
                                        <v-icon class="attach-card-icon">mdi-paperclip</v-icon>
                                            {{smsAttachments.name && smsAttachments.name.substr(0,10)}}
                                    </a>
                                </v-chip>
                            </span>
                        </div>
                        
                    </v-alert>
                    

                    <span style="    float: right;    width: 100%;    text-align: end;" class="date-div-messages" v-if="sms.in_out==2">
                        <span v-if="sms.sent_status == 1">
                            <v-icon class="custom-check-all theme--light">mdi-check-all</v-icon> 
                        </span>
                        <span v-if="sms.sent_status == 0 || sms.sent_remark == 6 || sms.sent_remark == 7">
                            <v-icon class="custom-check-all" style="color: red !important;">mdi-alert</v-icon> 
                        </span>
                        {{ sms.created }}
                    </span>
					</div>
                  
					<div class="row">
                    <div class="col col-12  pl-12 ">
                    <v-avatar class="avtar-icon avtar-intials reply-initials mr-2"
                    color="grey lighten-new"
                        size="32"
                        style="margin-bottom: -40px;"
                        v-if="sms.in_out==1 && key == 0 "
                        >{{contactName.slice(0,1)}}{{contactLastName.slice(0,1)}}</v-avatar>
                        <v-avatar class="avtar-icon avtar-intials reply-initials mr-2"
                        color="grey lighten-new"
                        size="32"
                        style="margin-bottom: -40px;"
                        v-if="sms.in_out==1 && key > 0 && (Object.values(smsListing)[key-1].in_out != Object.values(smsListing)[key].in_out || Object.values(smsListing)[key-1].sms_date != Object.values(smsListing)[key].sms_date  )"
                        >{{contactName.slice(0,1)}}{{contactLastName.slice(0,1)}}
                        </v-avatar>
                        <v-card  
                            class="border-radius-white p-3" style=" text-align: left; margin-left: 65px;width: fit-content;margin-bottom: 6px; max-width: calc(100% - 80px);"  v-if="sms.in_out==1"                        >
                            {{ sms.message.replace(/<\/?[^>]+(>|$)/g, "")}}
                            <div class="all-attachments-reply">
                                <span class="sms-attachments-chips">
                                    <v-chip 
                                        v-if="sms.contact_communications_media"
                                        v-for="(smsAttachments, index) in sms.contact_communications_media"
                                        :key="index"
                                    >
                                        <a :href="smsAttachments.url" target="_blank"> 
                                            <v-icon class="attach-card-icon">mdi-paperclip</v-icon>
                                                {{index + 1}}
                                        </a>
                                    </v-chip>
                                </span>
                            </div>
                        </v-card>
                        <span class="date-div-messages-right" v-if="sms.in_out==1">{{ sms.created }}</span>
                      
                   </div>
				   </div>
                </v-col>
                   <div id="msgScroll"></div>
                    <!-- <div v-if="sms.reply.length > 0" class="reply-section-div">
                        <div class="row" v-for="(communication_reply,index1,key1) in sms.reply">
                            <v-avatar class="avtar-intials reply-initials"
                            color="grey lighten-new"
                            size="32"
                            v-if="Object.keys(communication_reply.message).length > 0 "
                            >
                            {{contactName.slice(0,1)}}{{contactLastName.slice(0,1)}}
                            </v-avatar>

                            <v-card  
                            class="ml-2 col-lg-8 border-radius-white" style="text-align:left;"
                            v-if="Object.keys(communication_reply.message).length > 0 "
                            >
                            {{communication_reply.message.replace(/[^a-zA-Z ]/g, "")}}
                            
                            </v-card>
                            <span class="date-div-messages-right" v-if="index1 != Object.keys(communication_reply).length - 1">{{ communication_reply.created }}</span>
                        </div>
                   </div> -->
                
                
            </v-row>  
        </div>			

    </div>
         
    </v-card-text>
        <!-- <div class="footer-bg"> -->
        <!-- <v-row>
            <v-col cols="3">
                Send to:
            </v-col>
            <v-col cols="7">
            <v-select               
                dense
                outlined
                v-model="smsNumber"
                :items="smsNumbers"
                item-text="name"
                placeholder="Select phone number"
                item-value="id" >
            </v-select>
            </v-col>
        </v-row> -->
        <!-- <v-col class="pb-0 padding-footer"
          cols="12"
          sm="6"
          md="12"
          
        >
          <v-text-field
            v-if = "smsOptIn"
            placeholder="Type your message here..."
            solo
            class="col-lg-12"
            v-model="smsText"
            @keyup="enableSendSmsBtn"
           >           
            <template slot="append"> -->
               <!-- <v-icon>mdi-microphone</v-icon> -->
               <!-- <v-icon @click="showAttachmentsListForEmail">mdi-paperclip</v-icon>
               <v-btn
                v-if="sendSmsBtn"
                small          
                class="float-right send-btn-policy"
                @click="sendSms"  
                >Send</v-btn>
                <v-btn
                v-else
                small          
                class="float-right send-btn-policy"
                disabled
                >Send</v-btn>
            </template>
        </v-text-field>
           <v-text-field
            v-if = "smsOptOut"
            solo
            class="col-lg-12"
            v-model="smsTextone"
           >
            <template slot="append"> -->
               <!-- <v-icon>mdi-microphone</v-icon> -->
               <!-- <v-icon @click="showAttachmentsListForEmail">mdi-paperclip</v-icon> -->
               <!-- <v-btn
                small          
                class="float-right send-btn-policy"
                @click="sendSms"  
                >Send</v-btn>
            </template>
        </v-text-field>
        </v-col>
        </div> -->
        <div class="footer-bg">
        <v-col class="pb-0 padding-footer"
          cols="12"
          md="12"
          
        >
        <v-textarea
            v-model="smsText"
            placeholder="Type your message here..."
            solo
            class="col-lg-12 pt-2 pb-2 text-footer-area"
            auto-grow
            rows="1"
            @keydown.enter.exact.prevent="sendSms"
            @keydown.shift.enter.stop
            autofocus
            >
            <template v-slot:append>
                <v-btn
                v-if="!smsSendLoader"
                small
                class="float-right send-btn-policy"
                style="position:relative; top:-5px;"
                @click="sendSms"
                id="sendSmsScroll"
                >
                Send
                </v-btn>
                <v-btn v-if="smsSendLoader" small text>
                <img :src="`${base_url}/img/loader.gif`" style="width: 60px; height: 35px" />
                </v-btn>
            </template>
        </v-textarea>

        
        </v-col>
            <v-row class="mt-2 ml-8" v-if="attachmentAppendLists && attachmentAppendLists.length > 0">
                <v-chip
                    v-for="(attachmentLists, index,key) in attachmentAppendLists"
                    class="ma-1"
                    close
                    @click:close = "removeAttachment(attachmentLists.upload_id),removeChip(attachmentLists.upload_id)"
                    :key="`${index}`"
                >
                    {{attachmentLists.display_name}}
                </v-chip>
            </v-row>
            <v-row class="footer-extra-tags w-100">
                <v-col cols="12" md="12">
                    <v-row>
                        <v-col class="text-left" cols="12">
                            <v-btn class="btn-sms-footer" @click="showAttachmentsListForSms">
                                <v-icon>mdi-paperclip</v-icon>
                                add attachment
                            </v-btn>
                        
                            <v-btn class="btn-sms-footer" @click = "mergeFieldsDialog = true,initilizeMergeFields()">
                                <v-icon>mdi-call-merge</v-icon>
                                merge field
                            </v-btn>
                        
                            <v-btn class="btn-sms-footer" @click="apendTemplateBtn">
                                <v-icon>mdi-note</v-icon>
                                apply template 
                            </v-btn>
                                         
                            <v-btn class="btn-sms-footer" @click=saveOneOffTemplates(_ONE_OFF_TEMPLATE_TYPE_SMS)>
                                <v-icon>mdi-content-save</v-icon>
                                save as template 
                            </v-btn> 
                        </v-col>
                    </v-row>
                </v-col>
                
            </v-row>
        </div>

        <!----------------- Email Attachment Modal  ------------------->
		<v-dialog v-model="dialog" max-width="50%">
				<v-card>
					<v-card-title class="text-h5">
						<h5 class="modal-title">VIEW ATTACHMENTS</h5>
						<div class="cross-icon"><v-btn text @click="dialog = false"><v-icon>mdi-close</v-icon></v-btn></div>
					</v-card-title>

					<v-card-text>
                        <div id="view-templates" class="tab-pane fade in" style="opacity:1; overflow-x: hidden;max-height: 100px;">
                            <div class="one_off_templates_list_html table-responsive">
                                <table class="table table-striped custom-table-style">
                                    <thead>
                                        <tr> 
                                            <th>Sr.No.</th>
                                            <th>Name</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table_list">
                                        <tr v-html="smsAttachmentListing.attachment_list"></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
					</v-card-text>

					<v-card-actions>
						<v-spacer></v-spacer>

						<v-btn color="#29AD8E" text @click="dialog = false">
							Close
						</v-btn>
					</v-card-actions>
				</v-card>
			</v-dialog> 
			<!----------------- End Email Attachment Modal  ------------------->
            
            <!------------------ Sms Attachment Pop Up  ---------------------->

	<template>
		<v-row justify="center">
			<v-dialog
                v-model="attachmentSmsDialog"
                scrollable
                max-width="600"
			>
			
				<v-card style="height:532px;">
					<v-card-title class="add-attachment-title">Add Attachment</v-card-title>
					<div class="pl-4 pr-4">
					<v-subheader class="subheader-text">Uploaded Files</v-subheader>
					
					<v-simple-table height="355px" class="table table-striped table-attachment">
						<template v-slot:default>

							<tbody v-if="uploadedSmsAttachmentListing.contact_sms_attachment_list == null">
								<tr>
									<td colspan="2" style="text-align:center">
										No Attachments Found!
									</td>
								</tr>
							</tbody>
							<tbody v-else>
								<tr v-for="(contactAttachments, index,key) in uploadedSmsAttachmentListing.contact_sms_attachment_list.contact_attachment_lists">
									<td>
										<v-list-item>
											<v-list-item-action>
											
                                                <label class="container-checkbox">
                                                    <input type="checkbox" class="attachment_checkboxes" :value="contactAttachments.id" @click="enableAttachBtn">
                                                    <span class="checkmark"></span>
                                                </label>
											<!-- <v-checkbox class="checkboxes" :value="contactAttachments.id"></v-checkbox> -->
											</v-list-item-action>
											<v-list-item-content>
											    <v-list-item-title>{{contactAttachments.display_name}}</v-list-item-title>
											</v-list-item-content>
										</v-list-item>
									</td>
								</tr>
							</tbody>
						</template>
					</v-simple-table>
					</div>
					<v-divider class="ma-0"></v-divider>
					
					

					<v-card-actions>
					<v-icon class="laptop-icon mr-1">mdi-laptop</v-icon>  <span class="browse-computer-text" @click="onPickFile">BROWSE COMPUTER</span>
					<input type="file" style="display:none" ref="fileInput" accept="image/*" @change="onFilePicked" id="contact-muiltiple-files-cc" multiple="">
						<v-spacer></v-spacer>
						<v-btn style="height: 42px; "
                            class="cancel-edit-contact-details teal--text"
							color="#757575"
							text
							@click="attachmentSmsDialog = false, cancelAttachment();"
                            :disabled="isCancelAttachDisabled"
						>
						Cancel
						</v-btn>
                        <v-btn
							class="btn-save-create-service attach-file-btn"
							style="width:128px;"
							text
							@click = "appendAttachmentToSms();"
                            v-if="attachfileBtn && !attachfileloader"
						>
							ATTACH FILE
						</v-btn>
						<v-btn  style="width: 128px;height: 42px;"
                            depressed
                            disabled
                            v-else-if="!attachfileBtn && !attachfileloader">
                            ATTACH FILE
                        </v-btn>  
                        <v-btn v-if="attachfileloader" small text>
                            <img :src="`${base_url}/img/loader.gif`" style="width: 60px; height: 35px" /> 
                        </v-btn>
					</v-card-actions>

					
				</v-card>
			</v-dialog>
		</v-row>
	</template>

	<!---------------- End Sms Attachment Pop Up --------------------->

    <!---------------- Reply sms Pop Up --------------------->
    <template>
		<v-row justify="center">
			<v-dialog
			v-model="smsReplyDialog"
			scrollable
			max-width="600"
			>
			
				<v-card style="height:532px;">
					<v-card-title class="add-attachment-title">Send Reply Sms</v-card-title>
                    <v-row>
                    <v-col cols="1">
                            
                        </v-col>
                        <v-col cols="3">
                            Send to:
                        </v-col>
                        <v-col cols="7">
                        <v-select               
                            dense
                            outlined
                            v-model="smsNumber"
                            :items="smsNumbers"
                            item-text="name"
                            placeholder="Select phone number"
                            item-value="id" >
                        </v-select>
                        </v-col>
                    </v-row>
                    
					<v-row>
                    <v-col cols="4">
                            
                        </v-col>
                    <v-col cols="7">
                        <v-textarea class="pt-0 mt-0 ticket-note-mt-0"								
                            placeholder="Type your message here..."
                            solo
                            v-model="smsText"
                            @keyup="enableSendSmsBtn"
                        ></v-textarea>
                    </v-col>
                    </v-row>
                    <v-row>
                        <v-col cols="10">
                            <v-btn
                                v-if="sendSmsBtn"
                                small          
                                class="float-right send-btn-policy"
                                @click="sendSmsReply"  
                                >Send</v-btn>
                                <v-btn
                                v-else
                                small          
                                class="float-right send-btn-policy"
                                disabled
                                >
                                Send
                            </v-btn>
                        </v-col>
                    </v-row> 
					
				</v-card>
			</v-dialog>
		</v-row>
	</template>


 

<!----------------  Sms Attachment  Loader Pop Up --------------------->
    <template>
		<v-dialog
                v-model="loader"
                hide-overlay
                persistent
                max-width="370"
			>
			<v-card
				color="#29AD8E"
				dark
			>
				<v-card-text>
				    Please wait while fetching attachments.....
				<v-progress-linear
					indeterminate
					color="white"
					class="mb-0"
				></v-progress-linear>
				</v-card-text>
			</v-card>
		</v-dialog>
    </template>
<!---------------- End Sms Attachment  Loader Pop Up --------------------->

<!---------------- start apply template     ------------------------------->

<template>
            <v-dialog v-model="applyTemplatemodal" scrollable max-width="800" >
                <v-card>
                    <v-card-title class="modal-heading">
                        Templates
                        <div class="cross-icon ">
                            <v-btn class="v-btn v-btn--is-elevated v-btn--has-bg v-size--default send-btn-policy" :href="`${base_url}agency-one-off-templates`"  target="_blank">
                                All Templates
                            </v-btn>
                        </div>
                    </v-card-title>
                    <v-card-text class="pt-0 pb-1 slim-scroll template-data">
                        <v-container class="pl-0 pr-0 p-0 ">
                            <table class="table table-striped">
                                <thead class="fix-template-header">
                                    <tr>
                                        <th>Sr.No.</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th style = "width:160px">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="Object.keys(templateList).length > 0" v-for="(templateList, index) in templateList">
                                        <td>{{ templateList.count }}</td>
                                        <td>
                                            <span> {{ templateList.title }}</span>
                                        </td>
                                        <td>

                                            <span v-if="templateList.description != null && templateList.description != ''">
                                                <span> {{ templateList.description.substring(0, 20) }}</span>
                                                 <span v-if=" templateList.description.length > 20">....</span>
                                            </span>

                                            <span v-else> {{ templateList.description }}</span>
                                        </td>
                                        <td> 
                                            <v-btn small class="btn-templates" :href="`${base_url}agency-one-off-templates/edit/`+templateList.id+`/`+contactId"  target="_blank">
                                                <span class="ml-2"><i class="fa fa-edit" aria-hidden="true"></i></span>
                                            </v-btn>
                                            <v-btn v-if="!appendLoader.includes(templateList.id)"  class="btn-templates" small @click="appendTemplateData(`${templateTypeSms}`,templateList.id);">
                                                <span class="ml-2">Append</span>
                                            </v-btn>
                                            <v-btn v-else small text>
                                                <img :src="`${base_url}/img/loader.gif`" style="width: 60px; height: 35px" /> 
                                            </v-btn>
                                            
                                        </td>
                                    </tr>
                                    <tr v-else>
                                        <td colspan="4" style="text-align:center"> 
                                            No Attachments Found!
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </v-container>
                        <v-card-actions class="fixed-footer-modal model-footer-fix template-data-footer">
                            <v-spacer></v-spacer>
                            <v-btn class="cancel-edit-contact-details teal--text" 
                                text
                                @click="applyTemplatemodal = false"
                            >
                            Cancel
                            </v-btn>
                        </v-card-actions>
                    </v-card-text>
                </v-card>
            </v-dialog>
        </template>


    <!------ Merge fiedls modal---------->
    <template>
		<v-row justify="center">
			<v-dialog 
			v-model="mergeFieldsDialog"
			scrollable content-class="modal-lg">
				<v-card>
					<v-card-title  class="modal-heading add-attachment-title">Merge Fields
                    <div class="cross-icon" ref="contactInfoClose" @click="mergeFieldsDialog = false"><a text=""><i aria-hidden="true" class="v-icon notranslate mdi mdi-close theme--light"></i></a></div>
                    </v-card-title>
                    <v-divider class="ma-0"></v-divider>
                    <v-card-text class="pt-0 pb-1">
                        <div class="slim-scroll model-height-auto pt-3">
                            <ul class="user--list row pl-0" id="tag--list--popup">
                                <p class = "col-lg-5 text-bold">Agency Name    :</p>
                                <p id = "merge-field-aname" class = "col-lg-5">{agency.name}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-aname"><i class = "fa fa-copy"></i></a>
                                <p class = "col-lg-5 text-bold">Agency Email   :</p>
                                <p id = "merge-field-agency-email" class = "col-lg-5">{agency.email}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-agency-email"><i class = "fa fa-copy"></i></a>
                                <p class = "col-lg-5 text-bold">Agency Street Address :</p>
                                <p id = "merge-field-agency-street-address" class = "col-lg-5">{agency.streetAddress}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-agency-street-address"><i class = "fa fa-copy"></i></a>
                                <p class = "col-lg-5 text-bold">Agency City :</p>
                                <p id = "merge-field-agencycity" class = "col-lg-5">{agency.city}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-agencycity"><i class = "fa fa-copy"></i></a>
                                <p class = "col-lg-5 text-bold">Agency State :</p>
                                <p id = "merge-field-astate" class = "col-lg-5">{agency.state}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-astate"><i class = "fa fa-copy"></i></a>
                                <p class = "col-lg-5 text-bold">Agency Phone   :</p>
                                <p id = "merge-field-agency-phone" class = "col-lg-5">{agency.phone}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-agency-phone"><i class = "fa fa-copy"></i></a>
                                <p class = "col-lg-5 text-bold">Agency License :</p>
                                <p id = "merge-field-agency-license" class = "col-lg-5">{agency.license}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-agency-license"><i class = "fa fa-copy"></i></a>
                            </ul>
                            <ul class="user--list row pl-0" id="tag--list--popup">
                                <p class = "col-lg-5 text-bold">Agent Name    :</p>
                                <p id = "merge-field-agent-name" class = "col-lg-5">{agent.name}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-agent-name"><i class = "fa fa-copy"></i></a>
                                <p class = "col-lg-5 text-bold">Agent  Email   :</p>
                                <p id = "merge-field-agent-email" class = "col-lg-5">{agent.email}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-agent-email"><i class = "fa fa-copy"></i></a>
                                <p class = "col-lg-5 text-bold">Agent Phone   :</p>
                                <p id = "merge-field-agent-phone" class = "col-lg-5">{agent.phone}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-agent-phone"><i class = "fa fa-copy"></i></a>
                                <p class = "col-lg-5 text-bold">Agent First Name   :</p>
                                <p id = "merge-field-agent-first-name" class = "col-lg-5">{agent.firstName}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-agent-first-name"><i class = "fa fa-copy"></i></a>
                                <p class = "col-lg-5 text-bold">Agent Last Name   :</p>
                                <p id = "merge-field-agent-last-name" class = "col-lg-5">{agent.lastName}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-agent-last-name"><i class = "fa fa-copy"></i></a>
                                <p class = "col-lg-5 text-bold">Agent Calendar Link   :</p>
                                <p id = "merge-field-agent-calendar" class = "col-lg-5">{agent.calendarLink}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-agent-calendar"><i class = "fa fa-copy"></i></a>

                                <p class = "col-lg-5 text-bold">Video Proposal Link   :</p>
                                <p id = "agency-proposallink-of-agency" class = "col-lg-5">{agency.proposalLink}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#agency-proposallink-of-agency"><i class = "fa fa-copy"></i></a>
                                <p class = "col-lg-5 text-bold">Agency GMB Reviews Link  :</p>
                                <p id = "merge-field-reviewlink" class = "col-lg-5">{agency.reviewlink}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-reviewlink"><i class = "fa fa-copy"></i></a>

                            </ul>
                            <ul class="user--list row pl-0" id="tag--list--popup">
                                <p class = "col-lg-5 text-bold">Contact Name    :</p>
                                <p id = "merge-field-contact-name" class = "col-lg-5">{contact.name}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-contact-name"><i class = "fa fa-copy"></i></a>
                                <p class = "col-lg-5 text-bold">Contact Email   :</p>
                                <p id = "merge-field-contact-email" class = "col-lg-5">{contact.email}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-contact-email"><i class = "fa fa-copy"></i></a>
                                <p class = "col-lg-5 text-bold">Contact Phone   :</p>
                                <p id = "merge-field-contact-phone" class = "col-lg-5">{contact.phone}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-contact-phone"><i class = "fa fa-copy"></i></a>
                                <p class = "col-lg-5 text-bold">Contact First Name   :</p>
                                <p id = "merge-field-contact-first-name" class = "col-lg-5">{contact.firstName}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-contact-first-name"><i class = "fa fa-copy"></i></a>
                                <p class = "col-lg-5 text-bold">Contact Middle Name   :</p>
                                <p id = "merge-field-contact-middle-name" class = "col-lg-5">{contact.middleName}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-contact-middle-name"><i class = "fa fa-copy"></i></a>
                                <p class = "col-lg-5 text-bold">Contact Last Name   :</p>
                                <p id = "merge-field-contact-last-name" class = "col-lg-5">{contact.lastName}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-contact-last-name"><i class = "fa fa-copy"></i></a>
                                <p class = "col-lg-5 text-bold">Contact Preferred Name   :</p>
                                <p id = "merge-field-contact-preferred-name" class = "col-lg-5">{contact.preferredName}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-contact-preferred-name"><i class = "fa fa-copy"></i></a>
                                <p class = "col-lg-5 text-bold">Contact Birthdate :</p>
                                <p id = "merge-field-contact-birthdate" class = "col-lg-5">{contact.birthdate}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-contact-birthdate"><i class = "fa fa-copy"></i></a>

                                <!--Start Contact new merge fields-->
                                <p class = "col-lg-5 text-bold">Contact Address 1 :</p>
                                <p id = "merge-field-contact-address1" class = "col-lg-5">{contact.address1}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-contact-address1"><i class = "fa fa-copy"></i></a>



                                <p class = "col-lg-5 text-bold">Contact Address 2 :</p>
                                <p id = "merge-field-contact-address2" class = "col-lg-5">{contact.address2}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-contact-address2"><i class = "fa fa-copy"></i></a>


                                <p class = "col-lg-5 text-bold">Contact City :</p>
                                <p id = "merge-field-contact-city" class = "col-lg-5">{contact.city}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-contact-city"><i class = "fa fa-copy"></i></a>

                                <p class = "col-lg-5 text-bold">Contact State :</p>
                                <p id = "merge-field-contact-state" class = "col-lg-5">{contact.state}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-contact-state"><i class = "fa fa-copy"></i></a>

                                <p class = "col-lg-5 text-bold">Contact Zip :</p>
                                <p id = "merge-field-contact-zip" class = "col-lg-5">{contact.zip}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-contact-zip"><i class = "fa fa-copy"></i></a>

                                <p class = "col-lg-5 text-bold">Contact Marital Status :</p>
                                <p id = "merge-field-contact-marital-status" class = "col-lg-5">{contact.maritalStatus}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-contact-marital-status"><i class = "fa fa-copy"></i></a>

                                <p class = "col-lg-5 text-bold">Contact Lost Carrier :</p>
                                <p id = "merge-field-contact-lost-carrier" class = "col-lg-5">{contact.lostCarrier}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-contact-lost-carrier"><i class = "fa fa-copy"></i></a>

                                <p class = "col-lg-5 text-bold">Spouse First Name :</p>
                                <p id = "merge-field-contact-spouse-first-name" class = "col-lg-5">{contact.spouseFirstName}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-contact-spouse-first-name"><i class = "fa fa-copy"></i></a>

                                <p class = "col-lg-5 text-bold">Spouse Last Name :</p>
                                <p id = "merge-field-contact-spouse-last-name" class = "col-lg-5">{contact.spouseLastName}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-contact-spouse-last-name"><i class = "fa fa-copy"></i></a>

                                <p class = "col-lg-5 text-bold">Drivers License Number :</p>
                                <p id = "merge-field-contact-driver-license-number" class = "col-lg-5">{contact.driversLicenseNumber}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-contact-driver-license-number"><i class = "fa fa-copy"></i></a>
                                <p class = "col-lg-5 text-bold">Client Referral Link :</p>
                                <p id = "merge-field-client-referral-link" class = "col-lg-5">{clientReferralLink}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-client-referral-link"><i class = "fa fa-copy"></i></a>
                                <p class = "col-lg-5 text-bold">Client Referrer First Name :</p>
                                <p id = "merge-field-client-referral-first-name" class = "col-lg-5">{clientreferrer.firstName}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-client-referral-first-name"><i class = "fa fa-copy"></i></a>
                                <p class = "col-lg-5 text-bold">Client Referrer Last Name :</p>
                                <p id = "merge-field-client-referral-last-name" class = "col-lg-5">{clientreferrer.lastName}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-client-referral-last-name"><i class = "fa fa-copy"></i></a>
                                <p class = "col-lg-5 text-bold">Client Referrer Email :</p>
                                <p id = "merge-field-client-referral-email" class = "col-lg-5">{clientreferrer.email}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-client-referral-email"><i class = "fa fa-copy"></i></a>
                                <p class = "col-lg-5 text-bold">Client Referrer Phone :</p>
                                <p id = "merge-field-client-referral-phone" class = "col-lg-5">{clientreferrer.phone}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-client-referral-phone"><i class = "fa fa-copy"></i></a>
                                <p class = "col-lg-5 text-bold">Referral Partner First Name :</p>
                                <p id = "merge-field-referral-partner-first-name" class = "col-lg-5">{referralpartner.firstName}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-referral-partner-first-name"><i class = "fa fa-copy"></i></a>
                                <p class = "col-lg-5 text-bold">Referral Partner Last Name :</p>
                                <p id = "merge-field-referral-partner-last-name" class = "col-lg-5">{referralpartner.lastName}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-referral-partner-last-name"><i class = "fa fa-copy"></i></a>
                                <p class = "col-lg-5 text-bold">Referral Partner Email :</p>
                                <p id = "merge-field-referral-partner-email" class = "col-lg-5">{referralpartner.email}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-referral-partner-email"><i class = "fa fa-copy"></i></a>
                                <p class = "col-lg-5 text-bold">Referral Partner Phone :</p>
                                <p id = "merge-field-referral-partner-phone" class = "col-lg-5">{referralpartner.phone}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-referral-partner-phone"><i class = "fa fa-copy"></i></a>
                                <p class = "col-lg-5 text-bold">Referral Partner Company :</p>
                                <p id = "merge-field-referral-partner-company" class = "col-lg-5">{referralpartner.company}</p>
                                <a class = "btn col-lg-2 button-clipboard" data-clipboard-target="#merge-field-referral-partner-company"><i class = "fa fa-copy"></i></a>
                                <!--End Contact new merge fields-->
                            </ul>
                            <ul v-for="(agencyCustomField, index,key) in allAgencyCustomFields" class="user--list row pl-0" id="tag--list--popup">
                                <p class = "col-lg-5 text-bold" style="text-transform: capitalize;">{{agencyCustomField.field_name}} :</p>
                                <p :id="'merge-field-custom-' + agencyCustomField.id" class = "col-lg-5">{custom.{{agencyCustomField.field_label}}}</p>
                                <a class = "btn col-lg-2 button-clipboard" :data-clipboard-target="'#merge-field-custom-' + agencyCustomField.id"><i class = "fa fa-copy"></i></a>
                            </ul>       
                        </div>
                        <v-card-actions class="fixed-footer-modal model-footer-fix">
                            <v-spacer></v-spacer>
                            <v-btn class="cancel-edit-contact-details teal--text" 
                                text
                                @click="mergeFieldsDialog = false"
                            >
                            Cancel
                            </v-btn>
                        </v-card-actions>
                    </v-card-text>
                </v-card>
            </v-dialog>
        </v-row>
    </template>
    <!------End Merge fiedls modal---------->

    <v-snackbar 
        class="success-alert"
        v-model="snackbar"
        :timeout="timeout"
    >
        <v-icon class="success-alert-icon pr-1">mdi-checkbox-marked-circle</v-icon>
        <span class="success-alert-text" v-if="ifSendSms">{{ sendSmsMsg }}</span>  
        <span class="success-alert-text" v-else-if="ifSendSmsReply">{{ sendSmsMsg }}</span>    
        <span class="success-alert-text" v-if="ifsmsTemp">{{ saveTemplateMsg }}</span> 
    </v-snackbar> 
    
	<div class="alert-overlay overlay-full-height"></div>
        <v-alert class="custom-alert-for-sms" style="height:108px;"
        colored-border
        type="warning"
        elevation="2"
        v-model="phoneNumberValidationDialog"
        >
            
                <v-card-title class="alert-heading-sms">Cell Phone Required</v-card-title>
                <v-card-text class="alert-heading-text">
                    <p>Add a valid cell phone number to enable texts.</p>
                </v-card-text>
            
        </v-alert> 

    <v-alert class="custom-alert-for-sms"  style="height:auto;"
      colored-border
      type="warning"
      elevation="2"
      v-model="optInRequiredDialog"
    >
    
        <v-card-title class="alert-heading-sms">Opt-in Required</v-card-title>
        <v-card-text class="alert-heading-text">
            <p><strong>{{contactName}}</strong> hasn't agreed to receive text message from you yet!</p><br>
            <p>We want to protect you from potential rist of lawsuits. You can send the pre-formatted message below to get permission.</p>
            <v-textarea class="pt-0 mt-2 opt-in-textarea"								
                placeholder="Type your message here..."
                dense
                v-model="smsTextone"
                rows="3"
                readonly
            ></v-textarea>
        </v-card-text>
        <v-card-actions class="pt-0 pr-0">
        <v-card-text class="opt-in-footer-text text-left">Limited opt-in attempts (1 per day) {{optInCount}}/3 </v-card-text>
            <v-spacer></v-spacer>
            <v-btn
                color="#757575"
                text
            >
            Cancel
            </v-btn>
            <v-btn class="btn-save-create-service v-btn v-btn--text theme--light v-size--default"
                v-if="optInTime"
                justify="space-around"
                depressed                          
                dark
                @click="sendSms()"
            >
                Send
            </v-btn>
            <v-btn
                v-else
                small          
                class="float-right send-btn-policy"
                disabled
                >Send</v-btn>
        </v-card-actions>
        
   
    </v-alert> 

    <v-alert class="custom-alert-for-sms" style="height:200px;"
      colored-border
      type="warning"
      elevation="2"
      v-model="optInNoAttemptDialog"
    >
       
            <v-card-title class="alert-heading-sms">Opt-in Required</v-card-title>
            <v-card-text class="alert-heading-text">
                <p>Sorry, all opt-in attempts have been used. Texting will be disabled for this contact. If the customer texts 'START' to your BA phone number texting will be enabled.</p>
            </v-card-text>
            <v-card-actions class="pt-2 pl-0 pr-0">
            <v-card-text class="opt-in-footer-text text-left">Limited opt-in attempts: 3/3 </v-card-text>
                <v-spacer></v-spacer>
            </v-card-actions>
            
        
    </v-alert> 

    </v-card>
	
	
</script>


<script>
    Vue.component('<?= ComponentTools::componentName(__FILE__) ?>', {
        template: '#<?= ComponentTools::templateName(__FILE__) ?>',
        props: ['fieldData', 'contactId', 'value'],
        data: function() {
            return {
                base_url: base_url,
                contactList: [],
                mergeFields: [{
                        title: "Agency Name",
                        field: "{agency.name}",
                    },
                    {
                        title: "Agency Email",
                        field: "{agency.email}",
                    },
                    {
                        title: "Agency Street Address",
                        field: "{agency.streetAddress}",
                    },
                    {
                        title: "Agency City",
                        field: "{agency.city}",
                    },
                    {
                        title: "Agency State",
                        field: "{agency.state}",
                    },
                ],
                templates: [],
                items: [],
                attachListing: '',
                smsListing: [],
                currentCommunicationId: '',
                smsAttachmentListing: [],
                dialog: false,
                agencyId: '',
                smsText: '',
                smsNumbers: '',
                smsNumber: '',
                sendSmsBtn: false,
                loader: false,
                attachmentSmsDialog: false,
                attachfileBtn: false,
                attachmentAppendLists: [],
                hideAttachmentIds: [],
                browseEmailAttachmentIds: [],
                removedAttachmentIds: [],
                attachment_id_hide: false,
                snackbar: false,
                timeout: 3000,
                ifSendSms: false,
                ifSendSmsReply: false,
                sendSmsMsg: 'Sms sent successfully!',
                userName: '',
                userLastName: '',
                smsOptIn: true,
                smsOptOut: false,
                smsTextone: '',
                contactName: '',
                contactLastName: '',
                userId: '',
                user: [],
                agencyDetails: [],
                companyName: '',
                smsReplyDialog: false,
                replySmsId: '',
                phoneNumberValidationDialog: false,
                optInRequiredDialog: false,
                optInNoAttemptDialog: false,
                optInCount: '',
                todayDate: '',
                optInDate: '',
                optInTime: true,
                timeoutCellRequired: 0,
                timeoutOptInRequired: 0,
                timeoutNotAttempt: 0,
                limit: 50,
				start: 1,
				scrollLoader: false,
				reachEnd: false,
                filterMsg :true,
                uploadedSmsAttachmentListing: [],
                ifsmsTemp : false,
                saveTemplateMsg  : 'Template saved successfully',
                finalAttachmentListing: [],
                attachentsInfoMail: [],
                applyTemplatemodal : false,
                templateList:[],
                templateTypeSms: 2,
                appendLoader: [],
                mergeFieldsDialog : false,
                allAgencyCustomFields : [],
				attachedArray: [],
                attachfileloader: false,
                smsSendLoader: false,
                isCancelAttachDisabled: false,
            }
        },
        filters: {
            formatDate(dateToBeFormatted) {
                const date = new Date(dateToBeFormatted);

                return `${date.toLocaleDateString("en-US", {
                "day": "numeric",
                "year": "numeric",
                "month": "long",

                })}`;
            },
            formatTime(dateToBeFormatted) {
                const date = new Date(dateToBeFormatted);

                return `${date.toLocaleTimeString("en-US", {"hour": "numeric","minute":"numeric"})}`;
            },

        },

        methods: {
            populateSmsCommunicationListing: function(data) {

                var vm = this;
                if(data['data']){
                    vm.smsListing = (data['data']['data']['ContactCommunications.getSmsData'][this.contactId]);
                }
                else{
                    vm.smsListing = (data['ContactCommunications.getSmsData'][this.contactId]);
                }
            },
            // populateAttachListing: function(data){
            // var vm = this;
            // vm.attachListing = (data['TaskListing.getAttachListing'][this.contactId]);

            // },
            populateAgencyData: function(data) {
                var vm = this;
                vm.agencyDetails = (data['AgencyUsers.getAgency'][this.agencyId]);
                this.companyName = vm.agencyDetails.company;
                if (this.smsOptInOut == false) {
                    this.phoneNumberValidationDialog = true;
                    $('.alert-overlay').addClass('overlay-div');

                } else {

                    if (this.smsOptInOut.status == 0 && (this.smsOptInOut.opt_in_count == null || this.smsOptInOut.opt_in_count < 3)) {
                        this.optInRequiredDialog = true;
                        $('.alert-overlay').addClass('overlay-div');
                        if (this.smsOptInOut.opt_in_count == null) {
                            this.optInCount = 0;
                        } else {
                            this.optInCount = this.smsOptInOut.opt_in_count;
                            this.optInDate = this.smsOptInOut.opt_in_date; //

                            const then = new Date(this.smsOptInOut.opt_in_date);
                            const now = new Date();
                            const msBetweenDates = Math.abs(then - now.getTime());
                            const hoursBetweenDates = msBetweenDates / (60 * 60 * 1000);
                            if (hoursBetweenDates < 24) {
                                // alert('date is within 24 hours');
                                this.optInTime = false;

                            } else {
                                //alert('date is NOT within 24 hours');
                                this.optInTime = true;

                            }



                        }
                        this.smsTextone = "Hey " + this.contactName + "! It’s " + this.userName + " from " + this.companyName + ". We’d like to contact you via SMS, but we need your permission first. Reply Yes to confirm or Stop to cancel.";
                    } else if (this.smsOptInOut.status == 0 && this.smsOptInOut.opt_in_count == 3) {
                        this.optInNoAttemptDialog = true;
                        $('.alert-overlay').addClass('overlay-div');
                    }
                }

            },
            populateUserData: function(data) {
                if(data['data']){
                    this.user = (data['data']['data']['Users.getUser'][this.contactId]);  
                }else{
                    this.user = (data['Users.getUser'][this.contactId]);
                }
                
                this.userName = this.user.first_name;
                this.userLastName = this.user.last_name;

            },
            populateContactData: function(data) {
                if(data.data){
                    this.contact = data.data.data.Contact[this.contactId];
                }
                else{
                    this.contact = data.Contact[this.contactId];
                }
                this.agencyId = this.contact.agency_id;
                this.contactName = this.contact.first_name.charAt(0).toUpperCase() + this.contact.first_name.slice(1); //string.charAt(0).toUpperCase() + string.slice(1);
                this.contactLastName = this.contact.last_name;
                this.userId = this.contact.user_id;
                DataBridge.get('AgencyUsers.getAgency', this.agencyId, '*', this.populateAgencyData);

            },
            populatephoneNumbersOptInOutData: function(data) {
                DataBridgeContacts.save('ContactCommunications.getSmsData', this.contactId, this.populateSmsCommunicationListing);
                DataBridgeContacts.save('Contacts.getContactsAllNumbers', this.contactId,  this.populateSmsNumbersListing);

                let objectData = {
                    'objectId':this.contactId,
                    'objectName':'Contact',
                    'fields': '*'
                 }
                DataBridgeContacts.save('Contacts.getContactDetailsById', objectData, this.populateContactData);
                DataBridgeContacts.save('Users.getUser', this.contactId, this.populateUserData);
                var vm = this;
                
                if(data.data){
                    
                    vm.smsOptInOut = data.data.data.PhoneNumbersOptInOutStatus[this.contactId];
                }else{
                    vm.smsOptInOut = data.PhoneNumbersOptInOutStatus[this.contactId];
                }
                //vm.smsOptInOutStatus = this.smsOptInOut.status;

            },

            smsAttachmentModalListing: function(data) {

                var vm = this;
                vm.smsAttachmentListing = JSON.parse(data['ContactCommunications.getSmsAttachments'][this.currentCommunicationId]);
                console.log("email_attachment_listing", vm.smsAttachmentListing);
            },

            showSmsAttachments: function(communication_id) {

                this.currentCommunicationId = communication_id;
                DataBridge.get('ContactCommunications.getSmsAttachments', this.currentCommunicationId, '*', this.smsAttachmentModalListing);
            },
            populateSmsNumbersListing: function(data) {

                var vm = this;
                if(data['data']){
                    vm.smsNumbers = (data['data']['data']['Contacts.getContactsAllNumbers'][this.contactId]);
                }else{
                    vm.smsNumbers = (data['Contacts.getContactsAllNumbers'][this.contactId]);
                }
                // alert(vm.smsNumbers[0]['id']);
                this.smsNumber = vm.smsNumbers[0]['id'];
            },
            enableSendSmsBtn: function() {
                if (this.smsText.trim() != '') {
                    this.sendSmsBtn = true;
                } else {
                    this.sendSmsBtn = false;
                }
            },
            showAttachmentsListForSms: function() {
                this.loader = true;
                this.isCancelAttachDisabled = false;
                DataBridge.get('ContactCommunications.getAllEmailAttachmentLists', this.contactId, '*', this.populateEamilAttachments);
                // this.overlay = true;
            },
            populateEamilAttachments: function(response) {
                this.attachmentSmsDialog = true;

                this.uploadedSmsAttachmentListing = JSON.parse(response['ContactCommunications.getAllEmailAttachmentLists'][this.contactId]);

                if (this.uploadedSmsAttachmentListing.status == 1) {
                    this.attachmentSmsDialog = true;
                    this.loader = false;
                }

            },
            onPickFile: function() {
                this.$refs.fileInput.click()
            },
            onFilePicked: function(event) {
                //var file_data = $('#contact-muiltiple-files-cc').get(0).files;
                const files = event.target.files
                console.log("files", files.length);
                if (files.length > 1) {
                    var file_name = "Files";
                } else {
                    var file_name = "File"
                }
                // var file_data = $('#contact-muiltiple-files-cc').get(0).files;
                // var ext = $("#contact-muiltiple-files-cc").val().split('.').pop();
                var contact_id_n = this.contactId;
                var target_url = base_url + 'attachments/uploadSmsAttachmentsvue';
                var vm  = this;
                if (files != undefined) {
                    swal({
                            title: file_name + " attached to sms",
                            text: "Do you also want to save them to the Contact's attachments section?",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: "Yes",
                            cancelButtonText: "No",
                            closeOnConfirm: true
                        },
                        function (isConfirm) {

                            vm.attachfileloader = true;
                            vm.attachfileBtn = false;
                            vm.isCancelAttachDisabled = true;

                            if (isConfirm) {
                                //this.uploadMultipleAttacementsCc();
                                var file_data = $('#contact-muiltiple-files-cc').get(0).files;
                                var ext = $("#contact-muiltiple-files-cc").val().split('.').pop();
                                var form_data = new FormData();
                                ext = ext.toLowerCase();
                                var _EXTENSIONS_ALLOWED = ['pdf', 'xls', 'jpeg', 'png', 'doc', 'docx', 'jpg', 'eml', 'pst', 'ost', 'mp3', 'm4a', 'mp4', 'wav'];
                                if (_EXTENSIONS_ALLOWED.indexOf(ext) != -1) {
                                    form_data.append('contact_id', contact_id_n);
                                    form_data.append('isContactAttachment', true);
                                    for (var i = 0; i < file_data.length; i++) {
                                        form_data.append('sms_attachments[' + i + ']', file_data[i]);
                                    }

                                    var token = $("meta[name='csrf_token']").attr("content");
                                    //url = base_url + 'attachments/uploadAttacementsCc',

                                    axios.post(target_url, form_data, {
                                        headers: {
                                            'Content-Type': 'multipart/form-data',
                                            'X-CSRF-Token': token
                                        }
                                    }).then((result) => {


                                        if (result.data.status == 1) {
                                            let finalObj = {};
                                            let browseAttachments = result.data.appended_attachments_listing?.append_attachment_list;

                                            var unUploadedAttachments = result.data.unUploadedAttachments;
                                            if(unUploadedAttachments.length > 0)
                                            {
                                                let errortext =  'Below files which size is 0 MB, were unable to be uploaded:\n';
                                                $.each(unUploadedAttachments, function(index, value){
                                                    errortext += value + '\n';
                                                });
                                                if(browseAttachments && browseAttachments.length > 0)
                                                {
                                                     swal('Saved',errortext, "success");
                                                }
                                                else
                                                {
                                                    swal('',errortext, "warning");
                                                }
                                            }

                                            if(browseAttachments)
                                            {
                                                browseAttachments.forEach(item => {
                                                    vm.attachmentAppendLists.push(item);
                                                    vm.uploadedSmsAttachmentListing.contact_sms_attachment_list.contact_attachment_lists.push(item); //.contact_sms_attachment_list.contact_attachment_lists
                                                });
                                            }

                                            // console.log("vm.uploadedSmsAttachmentListing", vm.uploadedSmsAttachmentListing.contact_sms_attachment_list.contact_attachment_lists);

                                            if(result.data.attachents_info_mail && result.data.attachents_info_mail.length > 0){
                                                result.data.attachents_info_mail.forEach(item => {
                                                    vm.attachentsInfoMail.push(item);
                                                });
                                            }
                                            
                                            // if (vm.attachmentAppendLists != '') {
                                            //     for (let i = 0; i < browseAttachments.length; i++) {
                                            //         Object.assign(finalObj, browseAttachments[i]);
                                            //     }
                                            //     vm.attachmentAppendLists.push(finalObj);
                                            //     // console.log("objects",finalObj);
                                            //     // console.log("allAttachments",this.attachmentAppendLists);
                                            // } else {
                                            //     vm.attachmentAppendLists = browseAttachments;
                                            // }
                                            // vm.attachmentAppendLists = browseAttachments;

                                            vm.browseEmailAttachmentIds = JSON.parse("[" + result.data.attachment_ids + "]");
                                        } else {

                                            swal("Warning", "Something went wrong try again.", "error");
                                        }

                                        }).finally(() => {
                                            vm.attachmentSmsDialog = false;
                                            vm.attachfileloader = false;
                                            vm.attachfileBtn = true;
                                            vm.isCancelAttachDisabled = false;

                                            //console.log("finalResponse",finalResponse);
                                        });
                                } else {
                                    swal("Warning", "Please choose only pdf, xls, png, jpeg, doc, eml files to upload.", "error");
                                    $("#upload_multiple_attachment_cc")[0].reset();
                                }

                            }else{
                                var file_data = $('#contact-muiltiple-files-cc').get(0).files;
                                var ext = $("#contact-muiltiple-files-cc").val().split('.').pop();
                                var form_data = new FormData();
                                ext = ext.toLowerCase();
                                var _EXTENSIONS_ALLOWED = ['pdf', 'xls', 'jpeg', 'png', 'doc', 'docx', 'jpg', 'eml', 'pst', 'ost', 'mp3', 'm4a', 'mp4', 'wav'];
                                if (_EXTENSIONS_ALLOWED.indexOf(ext) != -1) {
                                    form_data.append('contact_id', contact_id_n);
                                    form_data.append('isContactAttachment', false);
                                    for (var i = 0; i < file_data.length; i++) {
                                        form_data.append('sms_attachments[' + i + ']', file_data[i]);
                                    }

                                    var token = $("meta[name='csrf_token']").attr("content");
                                    //url = base_url + 'attachments/uploadAttacementsCc',

                                    axios.post(target_url, form_data, {
                                        headers: {
                                            'Content-Type': 'multipart/form-data',
                                            'X-CSRF-Token': token
                                        }
                                    }).then((result) => {

                                        if (result.data.status == 1) {
                                            let finalObj = {};
                                            let browseAttachments = result.data.appended_attachments_listing?.append_attachment_list;
                                            var unUploadedAttachments = result.data.unUploadedAttachments;
                                            if(unUploadedAttachments.length > 0)
                                            {
                                                let errortext =  'Below files which size is 0 MB, were unable to be uploaded:\n';
                                                $.each(unUploadedAttachments, function(index, value){
                                                    errortext += value + '\n';
                                                });
                                                if(browseAttachments && browseAttachments.length > 0)
                                                {
                                                     swal('Saved',errortext, "success");
                                                }
                                                else
                                                {
                                                    swal('',errortext, "warning");
                                                }
                                            }

                                            if(browseAttachments)
                                            {
                                                browseAttachments.forEach(item => {
                                                    vm.attachmentAppendLists.push(item);
                                                    vm.uploadedSmsAttachmentListing.contact_sms_attachment_list.contact_attachment_lists.push(item); //.contact_sms_attachment_list.contact_attachment_lists
                                                });
                                            }

                                            if(result.data.attachents_info_mail && result.data.attachents_info_mail.length > 0){
                                                result.data.attachents_info_mail.forEach(item => {
                                                    vm.attachentsInfoMail.push(item);
                                                });
                                            }
                                            // if (vm.attachmentAppendLists != '') {
                                            //     for (let i = 0; i < browseAttachments.length; i++) {
                                            //         Object.assign(finalObj, browseAttachments[i]);
                                            //     }
                                            //     vm.attachmentAppendLists.push(finalObj);
                                            //     // console.log("objects",finalObj);
                                            //     // console.log("allAttachments",this.attachmentAppendLists);
                                            // } else {
                                            //     vm.attachmentAppendLists = browseAttachments;
                                            // }
                                            // vm.attachmentAppendLists = browseAttachments;

                                            vm.browseEmailAttachmentIds = JSON.parse("[" + result.data.attachment_ids + "]");
                                        } else {

                                            swal("Warning", "Something went wrong try again.", "error");
                                        }

                                    }).finally(() => {
                                        vm.attachmentSmsDialog = false;
                                        vm.attachfileloader = false;
                                        vm.attachfileBtn = true;
                                        vm.isCancelAttachDisabled = false;

                                        //console.log("finalResponse",finalResponse);
                                    });


                                } else {
                                    swal("Warning", "Please choose only pdf, xls, png, jpeg, doc, eml files to upload.", "error");
                                    $("#upload_multiple_email_attachment")[0].reset();
                                    $('#span_browse_computer').text('Browse Computer');

                                }
                            }
                        }
                    );

                }

            },
            enableAttachBtn: function() {
                if ($('input:checkbox[class=attachment_checkboxes]:checked').is(":checked")) {

                    this.attachfileBtn = true;

                } else {
                    this.attachfileBtn = false;
                }
            },
            appendAttachmentToSms: function() {

                var attachment_arr = [];
                // Get checked checkboxes
                this.attachfileloader = true;
                this.attachfileBtn = false;
                this.isCancelAttachDisabled = true;
                $('input:checkbox[class=attachment_checkboxes]:checked').each(function() {
                    if ($(this).is(":checked")) {
                        var attachment_id = $(this).val();
                        attachment_arr.push(attachment_id);
                    }
                });
                if (attachment_arr.length > 0) {
                    attachment_arr = attachment_arr.filter(x => !this.attachedArray.includes(x));
                    if (attachment_arr.length == 0) {
                        this.attachmentSmsDialog = false;
                    }
                    var attachment_ids = attachment_arr.toString();
                    this.hideAttachmentIds = JSON.parse("[" + attachment_ids + "]");
                    //console.log("dhidksd",this.hideAttachmentIds);
                    var checkAttachments = {
                        'attachment_arr': attachment_ids
                    }

                    DataBridge.save('ContactCommunications.appendAttachments', checkAttachments, this.populateAttachments);
                    // this.attachedArray = attachment_arr;

                } else {
                    swal("Warning", "Select at least one attacement to append.", "error");
                }

            },
            populateAttachments: function(response) {

                var contactAttachment = JSON.parse(response['data']['data']);
                this.attachfileloader = false;
                this.isCancelAttachDisabled = false;
                if (contactAttachment.status == 1) {
                    // contactAttachment.appended_attachments_listing.append_attachment_list.forEach(item => {
                    //     if(!this.attachmentAppendLists.includes(item))
                    //         this.attachmentAppendLists.push(item);
                    // })
                    var unUploadedAttachments = contactAttachment['unUploadedAttachments'];
                    if(unUploadedAttachments.length > 0)
                    {
                        this.showSnackbar = false;
                        let errortext =  'Below files which size is 0 MB, were unable to be uploaded:\n';
                        $.each(unUploadedAttachments, function(index, value){
                            errortext += value + '\n';
                        });
                        swal('',errortext, "warning");
                    }
                    this.attachmentAppendLists = contactAttachment.appended_attachments_listing.append_attachment_list;
                    this.attachentsInfoMail = contactAttachment.attachents_info_mail;
                    // if(contactAttachment.attachents_info_mail){
                    //     contactAttachment.attachents_info_mail.forEach(item => {
                    //         this.attachentsInfoMail.push(item);
                    //     })
                    // }

                    this.attachmentSmsDialog = false;
                    // $('input:checkbox[class=attachment_checkboxes]:checked').trigger('click');

                } else {

                    this.attachmentAppendLists = [];
                    this.attachentsInfoMail = [];
                    this.attachedArray = [];
                    if(contactAttachment.message)
					{
						swal("Warning",contactAttachment.message, "error");
					}
					else
					{
						swal("Warning", "Select at least one attacement to append.", "error");

					}
                }

            },
            removeAttachment: function(uploadId) {
                this.hideAttachmentIds.splice(this.hideAttachmentIds.indexOf(uploadId), 1);
                this.browseEmailAttachmentIds.splice(this.browseEmailAttachmentIds.indexOf(uploadId), 1);
            },

            removeChip: function(upload_id) {
                let updatedAttachmentList = this.attachmentAppendLists.filter((el) => el.upload_id !== upload_id);
                this.attachmentAppendLists = updatedAttachmentList;
                //this.attachmentAppendLists.splice(this.attachmentAppendLists.indexOf(index), 1);
                this.attachentsInfoMail = this.attachentsInfoMail.filter((item) => item.uploadId !== upload_id);
            },
            sendSms: function() {
                let vm =this;
                console.log('sms_form_data', 'Front End sendSms started');
                var formData = new FormData();
                formData.append('contact_id_n', this.contactId);
                formData.append('agency_id_n', this.agencyId);
                formData.append('sms_text', this.smsText);
                formData.append('sms_text_opt_out_one', this.smsTextone);
                formData.append('sms_number', this.smsNumber);
                formData.append('custom_attachment', true);
                formData.append('attachments_info_mails', JSON.stringify(this.attachentsInfoMail));
                console.log('sms_form_data form Data: ', Object.fromEntries(formData));
                var token = $("meta[name='csrf_token']").attr("content");
                url = base_url + 'Contacts/sendSms';
                if (this.smsText != '' || this.smsTextone != '') {
                    this.smsSendLoader = true;
                    axios.post(url, formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data',
                            'X-CSRF-Token': token
                        }
                    }).then(function(response) {

                        if (response['data']['status'] == 0) {
                            swal("Warning", response['data']['message'], "error");
                        } else {
                            this.snackbar = true;
                            this.ifSendSms = true;
                            this.smsSendLoader = false;
                        }
                    }).finally(() => {
                        $('input:checkbox[class=attachment_checkboxes]:checked').trigger('click');
                        // this.snackbar = true;
                        // this.ifSendSms = true;
                        setTimeout(
                            function() {
                                let objectData = {
                                    'objectId':this.contactId,
                                    'objectName':'PhoneNumbersOptInOutStatus',
                                    'fields': '*'
                                }
                                DataBridgeContacts.save('Contacts.getContactDetailsById', objectData, vm.populatephoneNumbersOptInOutData);
                                vm.smsText = '';
                                vm.smsSendLoader = false;
                                vm.attachmentAppendLists = [];
                                vm.attachentsInfoMail = [];
                                vm.attachedArray = [];
                                vm.scrollBottomMessages('scrollDown');
                            }, 3000);
                    });
                }
                console.log('sms_form_data', 'Front End sendSms end');

            },
            openReplyModal: function(smsId) {
                this.smsReplyDialog = true;
                this.replySmsId = smsId;
            },
            sendSmsReply: function() {
                var userName = this.userName + ' ' + this.userLastName;
                var formData = new FormData();
                formData.append('pre_record_id', this.replySmsId);
                formData.append('contact_id_n', this.contactId);
                formData.append('agency_id_n', this.agencyId);
                formData.append('reply_sms_text', this.smsText);
                formData.append('reply_sms_number', this.smsNumber);
                formData.append('username', userName);
                var token = $("meta[name='csrf_token']").attr("content");
                url = base_url + 'Contacts/sendReplySms';
                axios.post(url, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                        'X-CSRF-Token': token
                    }
                }).then(function(response) {

                    if (response['data']['status'] == 0) {
                        swal("Warning", response['data']['message'], "error");
                    } else {
                        this.snackbar = true;
                        this.ifSendSms = true;
                    }
                }).finally(() => {
                    // this.snackbar = true;
                    // this.ifSendSms = true;

                });
            },
            scrollBottomMessages: function(id) {
                setTimeout(function() {
                    const el = document.getElementById('msgScroll');
                    el.scrollIntoView({
                        behavior: "smooth"
                    });
                    // $('.contact-name-row').addClass('name-row-padding');
                    $('.contact-name-row').attr('style', 'padding-top: 60px !important');
                }, 1500);

            },
            onScrollSmsList: function({ target: { scrollTop, clientHeight, scrollHeight }}) {
                let vm = this;
				if(scrollTop == 0){
					if(!this.reachEnd){
                      
						this.scrollLoader = true;
						let offSet = this.limit * this.start;
                      
						var data = {
							"contact_id" : this.contactId,
							"offset" : offSet,
							"limit": this.limit,
						}
						DataBridgeContacts.save('ContactCommunications.loadMoreSmsData', data, vm.populateMoreSmsCommunicationListing);
					}
				}
			},

            populateMoreSmsCommunicationListing: function(response){
				let result =  JSON.parse(response['data']['data']);
                this.start++;
                let item = [];
                let keys = Object.keys(result['data']["ContactCommunications.getSmsData"][this.contactId]);
                keys.forEach(key => {
                    item = JSON.parse(JSON.stringify(result['data']["ContactCommunications.getSmsData"][this.contactId]));;
                this.smsListing =  Object.assign(item, this.smsListing)
                    
                })
              
                    
                if(Object.keys(result['data']['ContactCommunications.getSmsData'][this.contactId]).length == 0){
                    this.reachEnd = true;
                }
                this.scrollLoader = false;
              
			},
            saveOneOffTemplates: function(template_type)
            {
                var formData = new FormData();
                if(template_type == _ONE_OFF_TEMPLATE_TYPE_SMS)
                {
                    formData.append('sms_text', this.smsText);
                    formData.append('sms_text_opt_out_one', this.smsTextone);
                    formData.append('sms_number', this.smsNumber);
                    formData.append('template_type', template_type);
                }
                var token = $("meta[name='csrf_token']").attr("content");
                url = base_url + 'agencyOneOffTemplates/saveOneOffTemplates';
                if (this.sms_text != '') {
                    axios.post(url, formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data',
                            'X-CSRF-Token': token
                        }
                    }).then((response) => {

                        if (response['data']['status'] == 0) {
                            swal("Warning", "Something went wrong try again.", "error");
                        } else {
                            this.snackbar = true;
                            this.ifsmsTemp = true;
                        }
                    }).finally(() => {
                        setTimeout(() => {
                            this.snackbar = false;
                            this.ifsmsTemp = false;
                        }, 3000);
                    });
                }
                else
                {
                    swal("Warning", "Something went wrong try again.", "error");
                }

            },

            apendTemplateBtn: function(){
                // alert(2);
                this.applyTemplatemodal = true;
                var templateDetail = {
					'template_type': this.templateTypeSms,
				}
                DataBridgeContacts.save('Contacts.getAgencyOneOffTemplatesByType', templateDetail,this.showTemplateList);
            },
            showTemplateList: function(response) {  
                var result = response['data']['status']
                this.templateList = response['data']['list'];
            },
            appendTemplateData: function(templateType,templateId){
                this.appendLoader.push(templateId);
                var url = base_url + 'agencyOneOffTemplates/getAgencyOneOffTemplateDetail?template_type=' +templateType+'&template_id='+templateId;
                var vm = this;
                this.loading = true;
                axios.get(url)
                .then(
                    function (response) {
                        if(response.data.status == 1){
                            setTimeout(
                                function() 
                                {
                                    vm.appendLoader = [];
                                    vm.applyTemplatemodal = false;
                                    vm.smsText = response.data.content;
                            }, 3000);
                        }
                    }
                );
            },

            initilizeMergeFields: function(response){
                DataBridge.get('Contacts.getAgencyPersonalCustomFields', this.agencyId, '*', this.populateAgencyCustomFields);
            },
            populateAgencyCustomFields: function(response){
                this.allAgencyCustomFields = (response['Contacts.getAgencyPersonalCustomFields'][this.agencyId]);  
                Vue.nextTick(function () {
                    $('[data-clipboard-target]').tooltip({
                        title: 'Copy to Clipboard',
                        placement: 'left',
                    })
                });
            },
            cancelAttachment: function(){
                $('input:checkbox[class=attachment_checkboxes]:checked').trigger('click');
            }

        },
        beforeMount: function() {
            DataBridge.get('PhoneNumbersOptInOutStatus', this.contactId, '*', this.populatephoneNumbersOptInOutData);
            this.scrollBottomMessages('scrollDown')
        },
    });
</script>