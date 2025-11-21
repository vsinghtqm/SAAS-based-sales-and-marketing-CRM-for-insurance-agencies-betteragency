<?php

use ComponentLibrary\Lib\ComponentTools;

require_once(\ComponentLibrary\Lib\ComponentLibrary::componentPath("ba-contact-activity-email"));


?>

<style>
	#editor-container {
		height: 375px;
	}

	.editor-wrapper {
		position: relative;
	}

	.edittaskeditor {
		height: 150px;
	}

	.divider_line {
		margin-top: -20px;
	}

	.attach-email-icon {
		position: relative;
		top: 38px;
		color: #3A3541 !important;
	}

	.email-popup .v-text-field.v-text-field--enclosed:not(.v-text-field--rounded)> {
		padding: 0 !important;
	}

	.attachment-error-msg {
		color: #F65559;
		padding-left: 22px;
	}

.attachment-position {
    position: relative;
    bottom:25px;
    height: 112px;
}

	.v-card__title.add-attachment-title {
		font-weight: 500 !important;
		font-size: 14px !important;
		line-height: 20px !important;
		color: #3A3541 !important;
	}

	.attach-file-btn {
		font-weight: 500 !important;
		font-size: 14px !important;
		line-height: 24px !important;
		color: rgba(58, 53, 65, 0.26) !important;
	}

	i.laptop-icon {
		color: #29AD8E !important;
		font-size: 20px !important;
	}

	.browse-computer-text {
		font-weight: 500 !important;
		font-size: 14px !important;
		line-height: 24px !important;
		color: #757575 !important;
		text-transform: uppercase !important;
		cursor: pointer;
	}

	.table-attachment .v-list-item__title {
		color: rgba(58, 53, 65, 0.87) !important;
		font-weight: 400 !important;
		font-size: 16px !important;
		line-height: 24px !important;
		white-space: nowrap;
		width: 50px;
		overflow: hidden;
		text-overflow: ellipsis;
	}

	.table-attachment .v-list-item__action {
		margin-right: 0px;
	}

	input.attachment_checkboxes {
		border: 2px solid #959298 !important;
		height: 18px;
		width: 18px;
	}

	.subheader-text {
		font-weight: 500;
		font-size: 12px;
		line-height: 16px;
		text-transform: uppercase;
		color: rgba(58, 53, 65, 0.87) !important;
		padding-left: 3px;
	}

	.table-attachment .v-list-item {
		padding: 0 0px !important;
	}

	.v-application--is-ltr .table-attachment .v-list-item__action:first-child,
	.v-application--is-ltr .table-attachment .v-list-item__icon:first-child {
		margin-right: 5px !important;
	}

	.container-checkbox {
		display: block;
		position: relative;
		padding-left: 24px;
		margin-bottom: 12px;
		cursor: pointer;
		font-size: 22px;
		-webkit-user-select: none;
		-moz-user-select: none;
		-ms-user-select: none;
		user-select: none;
	}

	/* Hide the browser's default checkbox */
	.container-checkbox input {
		position: absolute;
		opacity: 0;
		cursor: pointer;
		height: 0;
		width: 0;
	}

	/* Create a custom checkbox */
	.checkmark {
		position: absolute;
		top: -2px;
		left: 0;
		height: 18px;
		width: 18px;
		border-radius: 3px;
		background: #fff;
		border: 1px solid #88858B;
	}

	/* On mouse-over, add a grey background color */
	.container-checkbox:hover input~.checkmark {
		background-color: #ccc;
	}

	/* When the checkbox is checked, add a blue background */
	.container-checkbox input:checked~.checkmark {
		background-color: #29AD8E;
		border: 1px solid #29AD8E;
	}

	/* Create the checkmark/indicator (hidden when not checked) */
	.checkmark:after {
		content: "";
		position: absolute;
		display: none;
	}

	/* Show the checkmark when checked */
	.container-checkbox input:checked~.checkmark:after {
		display: block;
	}

	/* Style the checkmark/indicator */
	.container-checkbox .checkmark:after {
		left: 5px;
		top: 1px;
		width: 6px;
		height: 11px;
		border: solid white;
		border-width: 0 2px 2px 0;
		-webkit-transform: rotate(45deg);
		-ms-transform: rotate(45deg);
		transform: rotate(45deg);
	}

	.attach-card-icon {
		font-size: 16px !important;
		padding: 5px;
		height: 27px;
		bottom: 0px;
		border-radius: 6px;
	}

	.more-icon {
		font-size: 16px !important;
		background: rgba(58, 53, 65, 0.08);
		border-radius: 16px;
		z-index: 111;
		left: 18px !important;
		bottom: 21px;
		align-items: center;
		padding: 3px 4px;
		width: 42px;
		height: 24px;
	}

	.firstDate {
		position: absolute;
		top: -2px !important;
	}

	p.note-text-email {
		position: relative;
		top: 0px;

	}

	.v-icon-fix {
		bottom: -37px !important;
	}

	i.v-icon.notranslate.attach-card-icon.mdi.mdi-paperclip.theme--light {
		z-index: 1;
	}



p.note-text-email p img {
    max-width: 100%;
    height: 100px;
    object-fit: contain;
    display: block;
}

p.note-text p img {
    max-width: 100%;
    height: 100px;
    object-fit: contain;
    display: block;
}

.emailSubmitBtnNew {
    position: absolute;
    bottom: 0px;
    width: 100%;
	border:1px solid rgba(0,0,0,.12);
}
.email-popup.email-popup-reply .ql-toolbar.ql-snow {
    background: #fff;
    width: 100%;
    bottom: 39px !important;
}
.replyattachmenticon {
    bottom: 4px !important;
}

div#editemaileditor {
    height: 100%;
}
.email-popup .ql-toolbar {
    bottom: -74px;
    z-index: 9;
    left: -2px;
}
.attach-icon{
	bottom: -149px !important;
    left: 273px !important;
}

.new-attahment-reply span{
	white-space: nowrap;
    width: 120px;
    overflow: hidden;
    text-overflow: ellipsis;
    display: inline-block !important;
    padding-top: 3px;
    padding-right: 11px;
}
.new-attahment-reply button {
    position: absolute !important;
    right: 9px !important;
}
.attachment-position span {
    white-space: nowrap;
    width: 120px;
    overflow: hidden;
    text-overflow: ellipsis;
    display: inline-block !important;
    padding-top: 3px;
    padding-right: 11px;
}

.attachment-email-reply span {
    white-space: nowrap;
    width: 120px;
    overflow: hidden;
    text-overflow: ellipsis;
    display: inline-block !important;
    padding-top: 3px;
    padding-right: 11px;
}
.attachment-email-reply {
    position: relative;
    top: 0;
    left: 12px;
}
.attachment-position button {
    position: absolute !important;
    right: 9px !important;
}
.new-attahment-reply {
    position: relative;
    top: -26px;
    left: 12px;
    background: #fff;
    float: left;
	width:100% !important;
}

.right-0{right:0 !important;}

.email-tab .email-first-date {
	position: sticky;
	top: 0px;
	width: -webkit-fill-available;
	z-index: 1;
	background-color: #e0e0e0;
    margin-left: 0px;
}
.email-fixHead {
	position: absolute !important;
	margin-top: 50px;
}
.email-tab .first-card {
	margin-top: 35px !important;
}

.email-foolter-action .btn-email-footer{
    box-shadow: none;
    color: #29AD8E;
    background: #fff !important;
}

.email-foolter-action .btn-email-footer i.v-icon.notranslate{
	color: #29AD8E !important;
}

.model-height-auto.scroll-email-reply{max-height: 350px !important;min-height: 350px !important;}
.email-popup .theme--light.v-chip:not(.v-chip--active) {
    width: auto;
}
.email-popup .v-select__selection--comma {
    overflow: unset;
    text-overflow: unset;
}
.email-popup .v-select.v-text-field:not(.v-text-field--single-line) input {
    display: none;
}
.del-btn{
	color: #8A8D93 !important;
    /*z-index: 999 !important;*/
}

</style>

<script type="text/x-template" id="<?= ComponentTools::templateName(__FILE__) ?>">
	<div>

    <v-btn class="ml-auto btn btn-outline-success btn-round btn-position right-0 mb-5" style="background-color:transparent;"  @click="showNewEmailModal">
            <v-icon class="plus-icon-css">mdi-plus</v-icon><span class="text-success">New Email</span>
    </v-btn>

    <div class="scroll-container float-left w-100 email-tab-max-height" style="margin-top: 50px;" @scroll="onScrollEmailList">

		<v-row no-gutters class="email-tab" v-if="!emailLoader"  v-for="(lists, index,key) in emailListing" :key="`${index}`">
		<!-- <h4 class="contact-date pl-0 mb-2 first-date-position" :class="{ '': key === 0 }">{{ index | replace('-',' ') }}</h4> -->

		<header class="email-first-date" :class="{ 'email-fixHead': key == 0 }"> <!-- overview-first-date -->
			<h4 class="contact-date pl-0 mb-2 first-date-position email-first-date" >{{ index | replace('-',' ') }}</h4> <!-- :class="{ 'firstDate': key === 0 }" -->
		</header>

		<v-col
			md="12"
			class="pb-0 col-12"
			:class="{ 'first-card': email.indexElement == 0 }"
			v-if="lists.hasOwnProperty('email_details')"
			v-for="(email, index,key) in lists['email_details']"
            :key= "email.id"
		>

		<v-card
			class="pa-2 email_list"
			style="border-left: 8px solid #98DCFF"
			:id = "'email_cards_' + email.id"
			@click = "viewEmail(email.id,email.communication_id)"
			v-if = "email.parent_communication_id === '' && email.status != 3"
			>
				<div class="d-flex">
                    <div style="width: 31px;">
						<v-icon class="custom-fa-icon">mdi-email-outline</v-icon>
                    </div>
                    <div style="width: 100%">
                        <v-row>
                            <v-col md="9">
							<span class="mt-2" v-if="email.user && email.in_out == 2" ><b>{{email.mail_subject}}</b> from {{getName(email.user.first_name)}} {{getName(email.user.last_name)}}</span>
			  				<span class="mt-2" v-if="email.contact && email.in_out == 1" ><b>{{email.mail_subject}}</b> from {{getName(email.contact.first_name)}} {{getName(email.contact.last_name)}}</span>
							<span v-if="Object.keys(email.contact_communications_media).length > 0"><v-icon class="attach-card-icon" >mdi-paperclip</v-icon><span>{{email.attachment_count}}</span></span>
                            </v-col>
                            <v-col md="3">
								<span class="ml-auto email_created" style="float:right">
								<span v-if="email.sent_status == 1">
									<v-icon class="custom-check-all theme--light">mdi-check-all</v-icon>
								</span>
								<span v-if="email.sent_status == 0 ">
									<v-icon class="custom-check-all" style="color: red !important;" v-if="email.sent_remark == 5" title="Message not sent due to contact marked as DNC">mdi-alert</v-icon>
									<v-icon class="custom-check-all" style="color: red !important;" v-if="email.sent_remark == 6" title="Message not sent due to contact email is not valid.">mdi-alert</v-icon>
									<v-icon class="custom-check-all" style="color: red !important;" v-if="email.sent_remark == 7" title="Contact un-subscribed for emails.">mdi-alert</v-icon>									
								</span>
									{{email.dateMonth}} at {{email.agencyTime}} {{email.time_zone}}
									</span>
                            </v-col>
                        </v-row>
                    </div>
                </div>
			   <p class="note-text"  v-html="email.message"> </p>
			   <v-icon class="custom-fa-icon icon-edit-common" style="float:right; margin-right:5px;font-size:20px !important;" @click.stop = "getEmailReply(email.id,email.communication_id,1)">mdi-reply-outline</v-icon>
                <v-icon v-if = "email.user_type == _ID_AGENCY_ADMIN || email.user_type_flag == _AGENCY_ROLE_ADMIN" class="icon-edit-common del-btn" style="float:right; margin-right:16px; font-size:20px" @click.stop = "showDeleteEmail(email.id)">mdi-delete</v-icon>
		</v-card>

            <v-card
                class="pa-2 email_list"
                style="border-left: 8px solid #98DCFF"
                :id = "'email_cards_' + email.id"
                v-else-if = "email.parent_communication_id  != ''"
                @click = "viewEmail(email.id,email.communication_id);"
                >
                <div class="d-flex">
                        <div style="width: 31px;">
                                <v-icon class="custom-fa-icon">mdi-email-outline</v-icon>
                        </div>
                        <div style="width: 100%">
                            <v-row>
                                <v-col md="9">
                                <span class="mt-2" v-if="email.user && email.in_out == 2" ><b>{{email.mail_subject}}</b> from {{getName(email.user.first_name)}} {{getName(email.user.last_name)}}</span>
                                <span class="mt-2" v-if="email.contact && email.in_out == 1" ><b>{{email.mail_subject}}</b> from {{getName(email.contact.first_name)}} {{getName(email.contact.last_name)}}</span>
                                <span v-if="Object.keys(email.contact_communications_media).length > 0"><v-icon class="attach-card-icon" >mdi-paperclip</v-icon><span>{{email.attachment_count}}</span></span>
                                </v-col>
                                <v-col md="3">
                                    <span class="ml-auto email_created" style="float:right"> {{email.dateMonth}} at {{email.agencyTime}} {{email.time_zone}}</span>
                                </v-col>
                            </v-row>
                        </div>
                    </div>
                   <p class="note-text" v-html="email.message"> </p>
                   <v-icon
                           class="custom-fa-icon icon-edit-common"
                           style="float:right; margin-right:5px;font-size:20px !important;"
                           @click.stop = "getEmailReply(email.id)"
                   >
                       mdi-reply-outline
                   </v-icon>
            </v-card>
            <v-row v-else-if = "email.parent_communication_id == '' && email.deleted_email != ''">
				<v-col md="12" class="mb-5" >
					<div class="" v-for = "(deleted_logs, index,key) in email.deleted_email">
                        <span v-if = "(Object.keys(deleted_logs['contact_communication_logs']).length > 1)">
                            <span class="text-start" v-if="deleted_logs.contact_communication_logs[0].email_type == _ID_SYNCED_EMAIL"><b>Synced Email</b></span>
                            <span class="text-start" v-if="deleted_logs.contact_communication_logs[0].email_type == _ID_CAMPAIGN_EMAIL"><b>Campaign Email</b></span>
                            <span class="text-start" v-if="deleted_logs.contact_communication_logs[0].email_type == _ID_BROADCAST_EMAIL"><b>Broadcast Email</b></span>
                            <span class="text-start">+{{ (Object.keys(deleted_logs['contact_communication_logs']).length)}} others</span>
                            <span>deleted by</span>
                            <span>{{ getName(deleted_logs.contact_communication_logs[0]['communication_logs_user'].first_name) }} {{ getName(deleted_logs.contact_communication_logs[0]['communication_logs_user'].last_name) }}</span>
                            <span>on</span>
                            <span>{{ deleted_logs.contact_communication_logs[0].created}}.</span>
                        </span>

                        <span v-else>
                            <span class="text-start" v-if="deleted_logs.contact_communication_logs[0].email_type == _ID_SYNCED_EMAIL"><b>Synced Email</b></span>
                            <span class="text-start" v-if="deleted_logs.contact_communication_logs[0].email_type == _ID_CAMPAIGN_EMAIL"><b>Campaign Email</b></span>
                            <span class="text-start" v-if="deleted_logs.contact_communication_logs[0].email_type == _ID_BROADCAST_EMAIL"><b>Broadcast Email</b></span>
                            <span>deleted by</span>
                            <span class="text-capitalize">{{ deleted_logs.contact_communication_logs[0].communication_logs_user.first_name }} {{ deleted_logs.contact_communication_logs[0].communication_logs_user.last_name }}</span>
                            <span>on</span>
                                <span>{{ deleted_logs.contact_communication_logs[0].created}}.</span>
                        </span>
					</div>
				</v-col>
			</v-row>
		</v-col>
	</v-row>
	<v-row v-if="emailListing.length==0 && !emailLoader">
		<v-col
			md="12"
			class="mb-5"
		>
			<div class="pl-7 pr-7 pt-7 pb-7 v-card v-sheet theme--light"><p class="text-center"> No records found. </p></div>
		</v-col>

	</v-row>

    <v-row v-if="emailLoader">
        <v-col md="12">
            <div class="pl-7 pr-7 pt-7 pb-7 v-card v-sheet theme--light">
                <p class="text-center">
                    <img :src="`${base_url}/img/loader.gif`" style="width: 60px; height: 35px" />
                </p>
            </div>
        </v-col>
    </v-row>
   </div>

	<!-- See Full Email Box Starts-->

	<v-dialog v-model="fullEmailDialog" max-width="848">
		<template class="custom-modal-figma" v-slot:default="dialog" >

			<v-card class="email_list" style="height:488px; border-left:8px solid rgb(152, 220, 255); padding:10px;" >
                <div class="d-flex">
                    <div style="width: 35px;">
                        <v-icon class="custom-fa-icon">mdi-email-outline</v-icon>
                    </div>
                    <div style="width: 100%">
                        <v-row>
                            <v-col md="9">
                                <span class="" v-if="email.user && email.in_out == 2"><b>{{subject}}</b> from {{email.user.first_name}} {{email.user.last_name}}</span>
                                <span class="" v-if="email.contact && email.in_out == 1"><b>{{subject}}</b> from {{email.contact.first_name}} {{email.contact.last_name}}</span>
                                <span v-if="attachment_count > 0">
                                    <v-icon class="attach-card-icon">mdi-paperclip</v-icon>
                                    <span>{{attachment_count}}</span>
                                </span>
                            </v-col>
                            <v-col md="3">
                                <span class="ml-auto email_created" style="float:right"> {{email.dateMonth}} at {{email.agencyTime}} {{email.time_zone}}</span>
                                <div class="reply-email-icon mt-5">
                                    <v-icon
                                            class="icon-edit-common" style="float:right;margin-right:5px; font-size:20px !important;"
                                            @click="cancelfullEmaildialog();">
                                        mdi-close
                                    </v-icon>
                                    <v-icon
                                            class="custom-fa-icon icon-edit-common"
                                            style="float:right; margin-right:5px;font-size:20px !important;"
                                            @click = "getEmailReply(email.id)"
                                            v-if="!email.parent_communication_id">
                                        mdi-reply-outline
                                    </v-icon>
                                </div>

                            </v-col>
                        </v-row>
                    </div>
                </div>
                <div class="slim-scroll model-height-auto scroll-email-reply" style="margin-top: 20px;">
                    <v-col md="12" class="pb-0">
                        <p class="note-text-email"><b> {{fromName}} </b></p>
                    </v-col>
                    <v-col md="12" class="pb-0">
                        <p class="note-text-email"> to {{contact_name}} </p>
                    </v-col>
                    <v-col md="12" class="pb-0">
                        <p class="note-text-email" v-html = "originalMessage"></p>
                    </v-col>

                </div>
                <div class="d-flex">
                    <v-col md="12" class="pb-0" v-if="attachment_count > 0">
                        <v-chip
                                v-for="(attachments, index,key) in email.contact_communications_media"
                                class="ma-2"
                                :key="attachments.id"
                        >
                            <!-- <a :href="attachments.file_url" target="_blank"> <v-icon class="attach-card-icon">mdi-paperclip</v-icon>{{attachments.name}} </a> -->
                            <a :href="`${base_url}s3/view?type=communication_media&id=${attachments.id}`" target="_blank"> <v-icon class="attach-card-icon">mdi-paperclip</v-icon>{{attachments.name}} </a>
                        </v-chip>
                    </v-col>
                </div>
			</v-card>
		</v-col>
		</template>
	</v-dialog>
	<!-- See Full Email Box-->

	<template>
  		<v-row justify="center">
			<v-dialog
			v-model="closeDialog"
			max-width="750"
			>

				<v-card style="min-height:210px;">
					<v-card-title class="text-h5 close-new-note-title">
						Discard Email
					</v-card-title>
					<v-card-text class="close-new-note-text">There are unsaved changes. This message hasn't been sent yet, would you like to keep editing, or discard this email?</v-card-text>
					<v-card-actions>
						<v-spacer></v-spacer>
						<v-btn
							color="#757575"
							text
							@click="closeWithoutSaving"
						>
						CLOSE WITHOUT SAVING
						</v-btn>
						<v-btn
							class="btn-save-create-service"
							style="width:120px;"
							text
							@click="closeDialog = false"
						>
							KEEP EDITING
						</v-btn>
					</v-card-actions>
				</v-card>
			</v-dialog>
		</v-row>
	</template>



    <v-snackbar class="success-alert"

            v-model="snackbar"
            :timeout="timeout"
        >
            <v-icon class="success-alert-icon pr-1">mdi-checkbox-marked-circle</v-icon>
			<span class="success-alert-text" v-if="replyEmailStatus">{{ sentReplySuccessText }}</span>
			<span class="success-alert-text" v-else-if="emailStatus">{{ emailSuccessMsg }}</span>
			<span class="success-alert-text" v-if="ifEmailTemp">{{ saveTemplateMsg }}</span>
			<span class="success-alert-text" v-if="ifDeleteEmail">{{ deleteEmailMsg }}</span>


    </v-snackbar>

    <v-dialog  v-model="deleteEmailDialog" persistent max-width="600" max-height="184">
        <v-card style="height:184px;">
            <v-card-title class="text-h6 delete-policy-heading">
            Delete Email
            </v-card-title>
            <v-card-text class="delete-policy-text">Are you sure you want to delete this email? This action can not be undone.</v-card-text>
        <v-card-actions>
        <v-spacer></v-spacer>
            <v-btn class="btn-cancel"
                color="#757575"
                text
                @click="deleteEmailDialog = false"
            >
                Cancel
            </v-btn>
            <v-btn v-if = "confirmDeleteBtnShow" class="btn-delete"
                color="#F65559"
                text
                @click="deleteEmail"
            >
                Yes, Delete
            </v-btn>
            <v-btn v-else class="btn-delete"
                color="#F65559"
                text
            >
                <img :src="`${base_url}/img/loader.gif`" style="width: 60px; height: 35px" />
            </v-btn>
        </v-card-actions>
        </v-card>
    </v-dialog>

 </div>

</script>

<script>
	Vue.component('<?= ComponentTools::componentName(__FILE__) ?>', {
		template: '#<?= ComponentTools::templateName(__FILE__) ?>',
		props: ['fieldData', 'objectId', 'contactId','initialModal'],
		data: function() {
			return {
				base_url: base_url,
				fieldValue: '',
				emailListing: [],
				newEmail: false,
				templates: [],
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
				items: [],
				attachListing: '',
				currentCommunicationId: '',
				emailAttachmentListing: [],
				dialog: false,
				emailDialog: false,
				emailsLists: [],
				setContactId: '',
                user: '',
				user_id: '',
				User: [],
				id: 'editemaileditor',
				addedEmailDesc: '',
				hideEmails: false,
				mail_subject: '',
				signature: '',
				nameRules: [
					v => !!v || '',
					// v => (v && v.length <= 10) || 'Name must be less than 10 characters',
				],
				closeEmailDialog: false,
				saveBtnEnable: true,
				timeout: 3000,
				emailSuccessMsg: 'Email sent successfully!',
				email_attachment_dialog: false,
				EmailAttachmentDialog: false,
				uploadedEmailAttachmentListing: [],
				attachmentIds: [],
				attachmentAppendLists: [],
				overlay: false,
				attachment_id_hide: false,
				removedAttachmentIds: [],
				hideAttachmentIds: [],
				browseEmailAttachmentIds: [],
				attachmentEmailDialog: false,
				loader: false,
				attachfileBtn: false,
				disableEmailSendBtn: false,
				subjectErrorMsg: false,
				changeCrossIcon: false,
				attachListing: '',
				currentCommunicationId: '',
				replyEmaildialog: false,
				new_email_btn: true,
				emailSendBtn: true,
				disabled: true,
				toEmail: '',
				fromEmail: '',
				resubject: '',
				subject: '',
				email: {},
				communication_id: '',
				toName: {},
				fromName: '',
				toEmails: [],
				ccEmails: [],
				bccEmails: [],
                to_email: '',
				reply_mail_from: '',
				reply_mail_to: '',
				reply_mail_subject: '',
				reply_mail_message: '',
				masgid: '',
				comm_type: '',
				communication_in_out: '',
				contact_name: '',
				originalMessage: '',
				sentReplySuccessText: 'Email sent successfully!',
				replyEmailStatus: false,
				closeDialog: false,
				snackbar: false,
				originalId: '',
				clickedId: false,
				message: '',
				email_attachment_dialog: false,
				EmailAttachmentDialog: false,
				attachment_count: '',
				clickedMore: false,
				moreIcon: true,
				emailStatus: false,
				limit: 50,
				start: 1,
				scrollLoader: false,
				reachEnd: false,
				ifEmailTemp: false,
				saveTemplateMsg  : 'Template saved successfully',
                mergeFieldsDialog : false,
				allCustomFields: [],
				applyTemplatemodal: false,
				templateTypeSms: _ONE_OFF_TEMPLATE_TYPE_EMAIL,
                templateList:[],
                appendLoader: [],
                attachedArray: [],
                emailLoader: false,
				sendbtnEnable:true,
				disableEmailReplyBtn:false,
                showCC: false,
                showBCC: false,
                emailModal: false,
                replyToEmailId: 0,
                fullEmailDialog: false,
                in_out: 0,
                deleteEmailDialog: false,
                confirmDeleteBtnShow:true,
                ifDeleteEmail:false,
                deleteEmailMsg:'Email Deleted Successfully.'
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
			replace: function(st, rep, repWith) {
				const result = st.split(rep).join(repWith)
				return result;
			},
		},

		computed: {
			virtualValue: {
				get() {
					return this.value
				},
				set(virtualValue) {
					this.$emit('input', virtualValue)
				}
			},
			 totalCount() {
              let count = 0;
              const temp = {};
              for(const month in this.emailListing){
                     for(const email of this.emailListing[month]['email_details'])
                     {
                         for(const k in email.deleted_email)
                         {
                            temp[k] = email.deleted_email[k]['contact_communication_logs'].length;
                         }

                     }
              }
              return count;
            }

		},


		methods: {
			populateEmailCommunicationListing: function(data) {
				var vm = this;
				if(data['data'])
				{
					vm.emailListing =  data['data']['data']['ContactCommunications.getEmailData'][this.contactId];
				}
				else
				{
					vm.emailListing = (data['ContactCommunications.getEmailData'][this.contactId]);
				}
				// set the index to the email listing
				let i = 0;
				for(let month in this.emailListing) {
					this.emailListing[month]['email_details'].forEach(element => {
						element.indexElement = i++;
					});
				}
			    this.emailLoader = false;
			},

			emailAttachmentModalListing: function(data) {

				var vm = this;
				vm.emailAttachmentListing = JSON.parse(data['ContactCommunications.getEmailAttachments'][this.currentCommunicationId]);
				console.log("email_attachment_listing", vm.emailAttachmentListing);
			},

			showEmailAttachments: function(communication_id) {

				this.currentCommunicationId = communication_id;
				DataBridge.get('ContactCommunications.getEmailAttachments', this.currentCommunicationId, '*', this.emailAttachmentModalListing);
			},

			getEmailAdd: function() {
				this.mail_subject = '';
				this.attachmentAppendLists = [];
				this.hideAttachmentIds = [];
				this.browseEmailAttachmentIds = [];
				$('.attachment_checkboxes').prop('checked', false);
				this.emailDialog = true;
				//DataBridgeContacts.save('ContactCommunications.getContactsAllEmails', this.contactId, this.populateToEmails);
				DataBridge.get('Users.getUser', this.contactId, '*', this.populateUserData);
			},

            populateEmails: function(response) {
				var from_user_data = [];
				var from_user_data1;
				var add_email;

				//console.log('response='+response);
				add_email = (response['data']['data']['ContactCommunications.getContactsAllEmails'][this.contactId]);
                this.toEmails = add_email.emails_list_to;
                console.log('populateEmails - toEmails', this.toEmails);


				this.from_emailsLists = add_email.emails_list_from;
				this.user_id = add_email.emails_list_from.id;
				this.signature = add_email.emails_list_from.signature;
				//console.log("signaturedsfsdfsd",this.signature);
				//var signatureText = "<br></br><br></br>" + this.signature;
				var signatureText = this.signature;

				//this.firstEmail = add_email.emails_list_to[0].email;
				//this.setContactId= add_email.emails_list_to[0].email;//Number(this.contactId)
				this.setContactId = Number(this.contactId);

			},


			showAttachmentPopUp: function() {
				//this.attachmentEmailDialog = true;
				this.email_attachment_dialog = true;
			},

			showAttachmentsListForEmail: function() {
				var vm = this;
                this.attachfileBtn = false;
				this.loader = true;
				DataBridgeContacts.save('ContactCommunications.getAllEmailAttachmentLists', vm.contactId, vm.populateEamilAttachments);
				// this.overlay = true;
			},

			populateEamilAttachments: function(response) {
			
				this.attachmentEmailDialog = true;

				console.log(response);
				this.email_attachment_dialog = false;
				this.uploadedEmailAttachmentListing = JSON.parse(response['data']['data']['ContactCommunications.getAllEmailAttachmentLists'][this.contactId]);

				console.log("Attachment", this.uploadedEmailAttachmentListing);
				if (this.uploadedEmailAttachmentListing.status == 1) {
					this.attachmentEmailDialog = true;
					this.loader = false;
					// this.EmailAttachmentDialog = true;
					//this.overlay = false;
				}


			},

			appendAttachmentToEmail: function() {

				var attachment_arr = [];
				// Get checked checkboxes
				$('input:checkbox[class=attachment_checkboxes]:checked').each(function() {
					if ($(this).is(":checked")) {
						var attachment_id = $(this).val();
						attachment_arr.push(attachment_id);
					}
				});
				if (attachment_arr.length > 0) {
                    attachment_arr = attachment_arr.filter(x => !this.attachedArray.includes(x));

					var attachment_ids = attachment_arr.toString();

					this.attachmentIds = attachment_ids;
					this.hideAttachmentIds = JSON.parse("[" + this.attachmentIds + "]");
					//console.log("dhidksd",this.hideAttachmentIds);
					var checkAttachments = {
						'attachment_arr': this.attachmentIds
					}

					DataBridge.save('ContactCommunications.appendAttachments', checkAttachments, this.populateAttachments);
                    // this.attachedArray = attachment_arr;
				} else {
					swal("Warning", "Select at least one attacement to append.", "error");
				}

			},

			populateAttachments: function(response) {
				var contactAttachment = JSON.parse(response['data']['data']);

				if (contactAttachment.status == 1) {
					// let finalSelectAttachments = {};
					// let selectedAttachments = contactAttachment.appended_attachments_listing.append_attachment_list;
					// console.log("lenght",selectedAttachments);
					// if(this.attachmentAppendLists !=''){
					// 	for(let i = 0; i < selectedAttachments.length; i++ ) {
					// 		Object.assign(finalSelectAttachments, selectedAttachments[i]);
					// 	}
					// 	this.attachmentAppendLists.push(finalSelectAttachments);
					// 	console.log("objects",finalSelectAttachments);
					// 	console.log("allAttachments",this.attachmentAppendLists);
					// }else{
					// 	this.attachmentAppendLists = contactAttachment.appended_attachments_listing.append_attachment_list;
					// }
                    // contactAttachment.appended_attachments_listing.append_attachment_list.forEach(item => {
                    //     if(!this.attachmentAppendLists.includes(item))
                    //         this.attachmentAppendLists.push(item);
                    // });
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


					// this.attachmentAppendLists = contactAttachment.appended_attachments_listing.append_attachment_list;

					this.EmailAttachmentDialog = false;
					this.attachmentEmailDialog = false;

				} else {
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
				console.log("uploadId", uploadId);
                this.hideAttachmentIds = this.hideAttachmentIds.filter((el) => el !== uploadId);
                this.browseEmailAttachmentIds = this.browseEmailAttachmentIds.filter((el) => el !== uploadId);
				// this.hideAttachmentIds.splice(this.hideAttachmentIds.indexOf(uploadId), 1);
				// this.browseEmailAttachmentIds.splice(this.browseEmailAttachmentIds.indexOf(uploadId), 1);
                console.log(this.hideAttachmentIds, this.browseEmailAttachmentIds);

			},

			removeChip: function(upload_id) {

				let updatedAttachmentList = this.attachmentAppendLists.filter((el) => el.upload_id !== upload_id);
				this.attachmentAppendLists = updatedAttachmentList;
				//this.attachmentAppendLists.splice(this.attachmentAppendLists.indexOf(index), 1);
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
				var file_data = $('#contact-muiltiple-files-cc').get(0).files;
				var ext = $("#contact-muiltiple-files-cc").val().split('.').pop();
				var isContactAttachment = 0;
				var contact_id_n = this.contactId;
				var form_data = new FormData();
				var target_url = base_url + 'attachments/uploadEmailAttacementsvue';
				if (files != undefined) {
					swal({
							title: file_name + " attached to email",
							text: "Do you also want to save them to the Contact's attachments section?",
							type: "warning",
							showCancelButton: true,
							confirmButtonColor: "#DD6B55",
							confirmButtonText: "Yes",
							cancelButtonText: "No",
							closeOnConfirm: true
						},
						function(isConfirm) {
							if (isConfirm) {
								//this.uploadMultipleAttacementsCc();


								var file_data = $('#contact-muiltiple-files-cc').get(0).files;
								console.log("file_Date", file_data);

								var ext = $("#contact-muiltiple-files-cc").val().split('.').pop();



								if (file_data != undefined) {
									ext = ext.toLowerCase();
									var _EXTENSIONS_ALLOWED = ['pdf', 'xls', 'jpeg', 'png', 'doc', 'docx', 'jpg', 'eml', 'pst', 'ost', 'mp3', 'm4a', 'mp4', 'wav','txt','csv','msg'];
									if (_EXTENSIONS_ALLOWED.indexOf(ext) != -1) {

										form_data.append('contact_id', contact_id_n);

										for (var i = 0; i < file_data.length; i++) {
											form_data.append('email_attachments[' + i + ']', file_data[i]);
										}

										var token = $("meta[name='csrf_token']").attr("content");
										url = base_url + 'attachments/uploadAttacementsCcMultiple',

											axios.post(url, form_data, {
												headers: {
													'Content-Type': 'multipart/form-data',
													'X-CSRF-Token': token
												}
											}).then(function(response) {
												//var importStatus = JSON.parse(response);
												//var importStatus =  JSON.parse(response['data']['data']);
												if (response.data.status == 1) {
                                                    var browseAttachments = response.data.list;
                                                    var unUploadedAttachments = response.data.unUploadedAttachments;
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
													else if (response.data.message) {
														swal("Warning", response.data.message, "error");
													} else {
														swal("Saved!", "Files uploaded successfully.", "success");
													}
													$('#contact_email_attachment_list').prepend(response.data.list);
												} else {

													swal("Warning", "Something went wrong try again.", "error");

												}
											});
									} else {
										swal("Warning", "Please choose only pdf, xls, png, jpeg, doc, eml, csv, txt, msg files to upload.", "error");
										$("#upload_multiple_attachment_cc")[0].reset();
									}

								}

							}
						}
					);

					ext = ext.toLowerCase();
					var _EXTENSIONS_ALLOWED = ['pdf', 'xls', 'jpeg', 'png', 'doc', 'docx', 'jpg', 'eml', 'pst', 'ost', 'mp3', 'm4a', 'mp4', 'wav', 'txt', 'csv', 'msg'];
					if (_EXTENSIONS_ALLOWED.indexOf(ext) != -1) {
						form_data.append('contact_id', contact_id_n);
						form_data.append('isContactAttachment', isContactAttachment);
						for (var i = 0; i < file_data.length; i++) {
							form_data.append('email_attachments[' + i + ']', file_data[i]);
						}

						var token = $("meta[name='csrf_token']").attr("content");
						//url = base_url + 'attachments/uploadAttacementsCc',

						axios.post(target_url, form_data, {
							headers: {
								'Content-Type': 'multipart/form-data',
								'X-CSRF-Token': token
							}
						}).then(result => {

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
                                        this.attachmentAppendLists.push(item);
                                    });
                                }
								// if (this.attachmentAppendLists != '') {
								// 	for (let i = 0; i < browseAttachments.length; i++) {
								// 		Object.assign(finalObj, browseAttachments[i]);
								// 	}
								// 	this.attachmentAppendLists.push(finalObj);
								// 	// console.log("objects",finalObj);
								// 	// console.log("allAttachments",this.attachmentAppendLists);
								// } else {
								// 	this.attachmentAppendLists = result.data.appended_attachments_listing.append_attachment_list;
								// }
								//this.attachmentAppendLists = result.data.appended_attachments_listing.append_attachment_list;

								// this.browseEmailAttachmentIds = JSON.parse("[" + result.data.attachment_ids + "]");
								this.browseEmailAttachmentIds.push(...result.data.attachment_ids);
								// this.browseEmailAttachmentIds = JSON.parse(this.browseEmailAttachmentIds);
							} else {

								swal("Warning", "Something went wrong try again.", "error");
							}

						}).finally(() => {
							this.attachmentEmailDialog = false;
							this.email_attachment_dialog = false;
							//console.log("finalResponse",finalResponse);
						});


					} else {
						swal("Warning", "Please choose only pdf, xls, png, jpeg, doc, eml, csv, msg, txt files to upload.", "error");
						$("#upload_multiple_email_attachment")[0].reset();
						$('#span_browse_computer').text('Browse Computer');

					}
				}

			},

			enableAttachBtn: function() {
				if ($('input:checkbox[class=attachment_checkboxes]:checked').is(":checked")) {

					this.attachfileBtn = true;

				} else {
					this.attachfileBtn = false;
				}
			},
			getEmailReply: function(communication_id) {
                console.log('communication_id ', communication_id);
				this.replyToEmailId = communication_id;
                this.showReplyEmailModal(this.replyToEmailId);
			},
            showDeleteEmail(emailId)
            {
                this.communication_id = emailId;
                this.deleteEmailDialog = true;
                this.confirmDeleteBtnShow = true;
				this.clickedMore = false;
                this.fullEmaildialog = false;
            },

			deleteEmail()
            {
                this.confirmDeleteBtnShow = false;
                var vm = this;
                DataBridge.get('ContactCommunications.deleteEmail', this.communication_id, '*', function(data){vm.populateDeleteContact(data)});
            },
            populateDeleteContact(response)
            {
                var result = response['ContactCommunications.deleteEmail'][this.communication_id];
                var vm = this;
                if(result.status == _ID_SUCCESS)
                {
                     setTimeout(
						function() {
							DataBridgeContacts.save('ContactCommunications.getEmailData', vm.contactId, vm.populateEmailCommunicationListing);
						}, 1000);
                    // swal("Success!", "Email deleted successfully.", "success");
                    this.snackbar = true;
                    this.ifDeleteEmail = true;
                    this.deleteEmailDialog = false;
                    this.confirmDeleteBtnShow = false;
                    this.communication_id = null;
                }
                else
                {
                    swal("Error!", "Something went wrong!", "error");
                    this.confirmDeleteBtnShow = true;
                    this.communication_id = null;
                }
                setTimeout(() => {
							this.snackbar = false;
							this.ifDeleteEmail = false;
						}, 3000);
            },

			populateContactEmail: function(response) {
				this.EmailList = (response['ContactCommunications.getContactEmails'][this.contactId]);
                this.toEmails.push(this.EmailList.email);
				console.log('this.EmailList', this.EmailList);
			},

			cancelReplyEmailDialog: function() {
				this.clickedId = false;
				if (this.reply_mail_message != '') {
					this.closeDialog = true;
					this.replyEmaildialog = true;
				} else {
					this.closeDialog = false;
					this.replyEmaildialog = false;
				}

			},
			populateEmailCommunication: function(response) {
				this.email = (response['ContactCommunications.getCommunication'][this.communication_id]);
				if (this.email.in_out == 2) {
					this.fromName = this.getName(this.email.user.first_name) + ' ' + this.getName(this.email.user.last_name);
					this.contact_name = this.getName(this.email.contact.first_name) + ' ' + this.getName(this.email.contact.last_name);
				} else if (this.email.in_out == 1) {
					this.fromName = this.getName(this.email.contact.first_name) + ' ' + this.getName(this.email.contact.last_name);
					this.contact_name = this.getName(this.email.user.first_name) + ' ' + this.getName(this.email.user.last_name);
				}
				this.reply_mail_from = this.email.user.email;
				this.resubject = 'Re:' + this.email.mail_subject;
				this.subject = this.email.mail_subject;
				this.reply_mail_subject = this.resubject;
				this.reply_mail_to = this.email.contact.email;
				this.originalMessage = this.email.message;
				this.emailId = this.email.id;
				this.replyId = this.email.communication_id;
				this.reply_mail_message = '';
				this.attachment_count = this.email.attachment_count;
				var signature = '<div style="margin-top: 121px;">' + this.email.user.signature + '</div><div><font-awesome-icon icon="fa-solid fa-ellipsis-vertical" /></div>';
			},
			populateReplyEmailCommunication: function(response) {
				this.email = (response['ContactCommunications.getCommunicationReply'][this.communication_id]);
				if (this.email.in_out == 2) {
					this.fromName = this.getName(this.email.user.first_name) + ' ' + this.getName(this.email.user.last_name);
					this.contact_name = this.getName(this.email.contact.first_name) + ' ' + this.getName(this.email.contact.last_name);
				} else if (this.email.in_out == 1) {
					this.fromName = this.getName(this.email.contact.first_name) + ' ' + this.getName(this.email.contact.last_name);
					this.contact_name = this.getName(this.email.user.first_name) + ' ' + this.getName(this.email.user.last_name);
				}
				this.reply_mail_from = this.email.user.email;
				this.resubject = 'Re:' + this.email.mail_subject;
				this.subject = this.email.mail_subject;
				this.reply_mail_subject = this.resubject;
				this.reply_mail_to = this.email.contact.email;
				this.originalMessage = this.email.message;
				this.replyId = this.email.id;
				this.emailId = this.email.communication_id;
				this.contact_name = this.email.contact.first_name + ' ' + this.email.contact.last_name;
				this.reply_mail_message = '';
				this.attachment_count = this.email.attachment_count;

				var signature = '<div style="margin-top: 121px;">' + this.email.user.signature + '</div><div><font-awesome-icon icon="fa-solid fa-ellipsis-vertical" /></div>';
			},

			viewEmail: function(communication_id, originalEmailId) {
				this.clickedMore = false;
				this.moreIcon = true;
				if (this.clickedId === false) {
					this.replyEmailDialog = false;
					this.fullEmailDialog = true;
					this.emailId = communication_id;
					this.communication_id = communication_id;
					this.originalId = originalEmailId;
					if (originalEmailId === '' || originalEmailId === null) {
						DataBridge.get('ContactCommunications.getCommunication', communication_id, '*', this.populateEmailCommunication);
					} else {
						DataBridge.get('ContactCommunications.getCommunicationReply', communication_id, '*', this.populateReplyEmailCommunication);
					}
				}
			},
			cancelfullEmaildialog: function() {
				this.fullEmailDialog = false;
			},

			closeWithoutSaving: function() {
				this.closeDialog = false;
				this.replyEmaildialog = false;
			},
			convertDateToUtcWithFullMonth: function(created) {
				if (created != '' && created != null) {
					var createdDate = new Date(created);
					return `${createdDate.toLocaleDateString("en-US", {
					"day": "numeric",
					"year": "numeric",
					"month": "long",
					"timeZone": "UTC"

					})}`;
				} else {
					return "";
				}
			},
			formatTimeToUtc: function(timeToBeFormatted) {
				if (timeToBeFormatted != null && timeToBeFormatted != '') {
					const date = new Date(timeToBeFormatted);

					return `${date.toLocaleTimeString("en-US", {
				"hour": "numeric",
				"minute":"numeric",
				"timeZone": "UTC"

				})}`;
				} else {
					return "";
				}
			},
			getName: function(name) {
                if(name)
                {
                    const capitalizedFirst = name[0].toUpperCase();
                    const rest = name.slice(1);
                    return capitalizedFirst + rest;
                }
                else
                {
                    return '';
                }
			},
			showClickedMore: function() {
				if (!this.clickedMore) {
					this.clickedMore = true;
					this.moreIcon = true;
				} else {
					this.clickedMore = false;
					this.moreIcon = true;
				}
			},

			attachmentEmailDialogFun(){
				let uncheckboxEmailAttachments =document.getElementsByClassName("attachment_checkboxes")
				for(let i=0;i<uncheckboxEmailAttachments.length;i++)
				{
					uncheckboxEmailAttachments[i].checked=false;
				}

				this.attachmentEmailDialog = false;
			},
			onScrollEmailList: function({ target: { scrollTop, clientHeight, scrollHeight }}) {
				if(scrollTop + clientHeight >= scrollHeight){
					if(!this.reachEnd){
						this.scrollLoader = true;
						let offSet = this.limit * this.start;
						var data = {
							"contact_id" : this.contactId,
							"offSet" : offSet,
							"limit": this.limit,
						}
						DataBridge.save('ContactCommunications.loadMoreEmailData', data, this.populateMoreEmailCommunicationListing);
					}
				}
			},
			populateMoreEmailCommunicationListing: function(response){
				var result =  JSON.parse(response['data']['data']);
				console.log('emailListing ',this.emailListing);
				if(result['status'] == true || result['status'] == '1')
                {
					if(result['data']){
						this.start++;
						for(let keys in result['data']){
							for(let value in result['data'][keys]){
								result['data'][keys][value].forEach(item => {
									this.emailListing[keys][value].push(item);
								})
							}
						}
					}

					if(Array.isArray(result['data']) && result['data'].length == 0){
						this.reachEnd = true;
					}
					this.scrollLoader = false;
                }
			},
			saveOneOffTemplates: function(template_type)
			{
				var formData = new FormData();
				if(template_type == _ONE_OFF_TEMPLATE_TYPE_EMAIL)
				{
					formData.append('mail_message', this.addedEmailDesc);
					formData.append('mail_subject', this.mail_subject);
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
							this.emailStatus = false;
							this.snackbar = true;
							this.ifEmailTemp = true;
						}
					}).finally(() => {
						setTimeout(() => {
							this.snackbar = false;
							this.ifEmailTemp = false;
						}, 3000);
					});
				}
				else
				{
					swal("Warning", "Something went wrong try again.", "error");
				}
			},
			initilizeMergeFields: function(){
                DataBridge.get('ContactCommunications.getAgencyPersonalCustomFields', this.contactId, '*', this.populateCustomFields);
            },

			populateCustomFields: function(response){
                this.allCustomFields = (response['ContactCommunications.getAgencyPersonalCustomFields'][this.contactId]);
                Vue.nextTick(function () {
                    $('[data-clipboard-target]').tooltip({
                        title: 'Copy to Clipboard',
                        placement: 'left',
                    })
                });
            },


            apendTemplateBtn: function(){
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
                axios.get(url)
                .then(
                    function (response) {
                        if(response.data.status == 1){
                            setTimeout(
                                function()
                                {
                                    vm.appendLoader = [];
                                    vm.applyTemplatemodal = false;
									vm.mail_subject = response.data.subject || "";
									vm.loadQuill(response.data.content);
									vm.addedEmailDesc = response.data.content;
									vm.saveBtnEnable = response.data.subject && response.data.content ? false : true;
                            }, 3000);
                        }
                    }
                );
            },
            showNewEmailModal: function(){
                console.log('new email from email list component');
                this.$emit('show-new-email-modal');
            },
            showReplyEmailModal: function(emailId){
                console.log('reply email from email list component', emailId);
                this.$emit('show-reply-email-modal', emailId);
            }
		},


		beforeMount: function() {
			this.emailLoader = true;
			DataBridge.get('ContactCommunications.getEmailData', this.contactId, '*', this.populateEmailCommunicationListing);
			// DataBridge.get('contact', '*', this.populateContactData);
		},
		mounted: function() {
            this.$root.$on('refreshEmailListing', () => {
                console.log('again',this.contactId);
                DataBridge.save('ContactCommunications.getEmailData', this.contactId, this.populateEmailCommunicationListing);
             });
		},
        watch:{
            initialModal: function(val){
                console.log('initial Modal changed', val);
                this.emailModal = val;
            },
        }
	});
</script>
