<?php
/** @var $contactId **/
require_once(\ComponentLibrary\Lib\ComponentLibrary::componentPath('ba-contact-rail'));
require_once(\ComponentLibrary\Lib\ComponentLibrary::componentPath('ba-policy-slide-group'));
require_once(\ComponentLibrary\Lib\ComponentLibrary::componentPath('ba-contact-activity-tabs'));
require_once(\ComponentLibrary\Lib\ComponentLibrary::componentPath("emailComposer/ba-contact-send-email"));
?>
<style>
    .v-slide-group__prev,  .v-slide-group__next{
        /*position: absolute;*/
        /*z-index: 99;*/
    }
</style>
<input type="hidden" name="csrfToken" value="<?= $this->request->getAttribute('csrfToken') ?>">
<div id="app">
    <v-app style="background-color: #E7E7E7">
        <v-main>

            <v-container style="padding: 0px;" class="custom-container-contact">

                <v-row no-gutters>
                    <div class="left-panel mb-5" fill-height>
                        <ba-contact-rail
                                contact-id="<?=htmlspecialchars($contactId)?>"
                                @show-new-email-modal="showEmailModal"
                        >
                        </ba-contact-rail>
                    </div>
                <div class="right-panel" >
                    <div style="padding-left: 20px; padding-right:0px;">
                        <v-row>
                            <v-col cols="12">
                                <ba-policy-slide-group contact-id="<?=htmlspecialchars($contactId)?>"></ba-policy-slide-group>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="12">
                                <ba-contact-activity-tabs
                                        contact-id="<?=htmlspecialchars($contactId)?>"
                                        agency-id="<?=htmlspecialchars($agencyId)?>"
                                        @show-new-email-modal="showEmailModal"
                                        @show-reply-email-modal="showReplyEmailModal"
                                >
                                </ba-contact-activity-tabs>
                            </v-col>
                        </v-row>
                    </div>
                </div>

                </v-row>
            </v-container>
            <v-dialog v-model="emailModal" persistent retain-focus>
                <ba-contact-send-email
                        :contact-id="contactId"
                        :reply-to-email-id="replyToEmailId"
                        :custom-fields="customFields"
                        :user-info="User"
                        @close-email-modal="closeEmailModal"
                        @mail-sent = "mailSent"
                        @mail-disconnected = "mailDisconnected"
                >
                </ba-contact-send-email>
            </v-dialog>
        </v-main>
    </v-app>
     <v-snackbar class="success-alert"
                v-model="snackbar"
                :timeout="timeout"
            >
                <v-icon class="success-alert-icon pr-1">mdi-checkbox-marked-circle</v-icon>
                <span class="success-alert-text" v-if="replyEmailStatus">{{ emailSuccessMsg }}</span>
                <span class="success-alert-text" v-else-if="emailStatus">{{ emailSuccessMsg }}</span>
        </v-snackbar>

        <!-- start campaign dialog -->
		<v-dialog v-model="ReconnectEmailDialog" max-height="346px" max-width="529px">
			<template v-slot:default="ReconnectEmailDialog">
				<v-card class="popup-child pb-3">
					<v-card-title class="modal-heading-sub-popup">
                    Email Integration Disconnected
					</v-card-title>
					<v-card-text class="pt-0 pb-0">
                    <v-container class="pl-0 pr-0 pt-0">
							<v-row>
								<v-col cols="12" md="12">
                                 <p class="appointment-popup-text">Your email integration is disconnected. Click here to reconnect and avoid disruptions.</p>
								</v-col>
							</v-row>
						</v-container>
					</v-card-text>
					
					<v-card-actions class="mt-5 pt-3">
						<v-spacer></v-spacer>
						 <v-btn color="#29AD8E" text @click="doNotConnect" class="cancel-edit-contact-details"> Cancel</v-btn>
						 <v-btn  justify="space-around" depressed dark @click="reconnectEmail();" class=" btn-save-create-service v-btn v-btn--text theme--light v-size--default" >Reconnect Email</v-btn>
			    </router-link>
					</v-card-actions>
				</v-card>
			</template>
        </v-dialog>
		<!-- end -->
</div>

<script>
    new Vue({
        el: '#app',
        vuetify: new Vuetify(),
        data: function(){
            return {
                contactId: <?=$contactId?>,
                agencyId: <?=$agencyId?>,
                emailModal: false,
                User: {},
                replyToEmailId: 0,
                customFields : {},
                emailStatus: false,
                replyEmailStatus: false,
                emailSuccessMsg : 'Email sent successfully!',
                timeout : 3000,
                snackbar : false,
                ReconnectEmailDialog : false,
                nylasUrl: ''
            }
        },
        methods:{
            mailDisconnected: function(){
                this.ReconnectEmailDialog = true
            },
            reconnectEmail:function(){                
                DataBridge.get('Users.redirectToEmailConfigUrlFromBeta', this.contactId, '*', this.redirectToEmailConfigUrl);
            },
            redirectToEmailConfigUrl: function(data)
            {
                this.nylasUrl = (data['Users.redirectToEmailConfigUrlFromBeta'][this.contactId]);
                window.location.href = this.nylasUrl;
            },
            doNotConnect: function(){
                this.replyToEmailId = 0;
                this.ReconnectEmailDialog = false;
                // this.emailModal = false;
            },
            mailSent : function(){
                this.emailStatus = true;
                this.snackbar = true;
                console.log('Mail Sent _---------------------------------');
            },
            closeEmailModal: function(){
                //console.log('closeEmailModal value before', this.emailModal);
                this.replyToEmailId = 0;
                this.emailModal = false;
                //console.log('closeEmailModal value after', this.emailModal);
            },
            showEmailModal: function(){
                //console.log('parent emailModal value before', this.emailModal);
                DataBridge.get('Users.getUser', this.contactId, '*', this.populateUserData);
                DataBridge.get('ContactCommunications.getAgencyPersonalCustomFields', this.contactId, '*', this.populateCustomFields);
                 this.emailModal = true;
                //console.log('parent emailModal value after', this.emailModal);
            },
            populateUserData: function(data) {
                this.User = (data['Users.getUser'][this.contactId]);

               // console.log('populate User Data', this.User);
            },
            populateCustomFields: function(data) {
                 this.customFields = (data['ContactCommunications.getAgencyPersonalCustomFields'][this.contactId]);
                 console.log('populate User Data', this.customFields);
            },
            setReplyToEmailId: function(data){
                this.replyToEmailId = data;
                this.showEmailModal();
            },
            showReplyEmailModal: function(emailId){
                //console.log('parent replyEmailModal value before', this.emailModal);
                this.setReplyToEmailId(emailId);
                DataBridge.get('Users.getUser', this.contactId, '*', this.populateUserData);
                this.emailModal = true;
                //console.log('parent replyEmailModal value after', this.emailModal);
            }
        },
    });

</script>
