<?php

use ComponentLibrary\Lib\ComponentTools;

require_once(\ComponentLibrary\Lib\ComponentLibrary::componentPath("ba-field"));
require_once(\ComponentLibrary\Lib\ComponentLibrary::componentPath("ba-contact-activity-email-list"));
require_once(\ComponentLibrary\Lib\ComponentLibrary::componentPath("ba-contact-activity-text"));
require_once(\ComponentLibrary\Lib\ComponentLibrary::componentPath("ba-contact-activity-text-list"));
require_once(\ComponentLibrary\Lib\ComponentLibrary::componentPath("ba-contact-activity-viewtask"));
require_once(\ComponentLibrary\Lib\ComponentLibrary::componentPath("ba-contact-activity-addtask"));
require_once(\ComponentLibrary\Lib\ComponentLibrary::componentPath("ba-contact-activity-snooze"));
require_once(\ComponentLibrary\Lib\ComponentLibrary::componentPath("ba-contact-activity-campaign-list"));
require_once(\ComponentLibrary\Lib\ComponentLibrary::componentPath("ba-contact-activity-notes"));
require_once(\ComponentLibrary\Lib\ComponentLibrary::componentPath("ba-contact-activity-attachments"));
require_once(\ComponentLibrary\Lib\ComponentLibrary::componentPath("ba-contact-activity-tabs-overview"));
require_once(\ComponentLibrary\Lib\ComponentLibrary::componentPath("ba-contact-activity-acords"));
require_once(\ComponentLibrary\Lib\ComponentLibrary::componentPath("ba-contact-activity-logs"));
require_once(\ComponentLibrary\Lib\ComponentLibrary::componentPath("ba-contact-activity-opt-logs"));
?>

<script type="text/x-template" id="<?= ComponentTools::templateName(__FILE__) ?>">
    <div>
    <v-tabs class="mt-5"
        v-model="currentActivityTab"
        align-with-title
        grow
        background-color="transparent"
        color="#24A383"
        @change="changeTab(currentActivityTab)"
    >
        <v-tabs-slider color="#24A383"></v-tabs-slider>

        <v-tab class="overview-class" key="overview">
           <v-icon style="font-size:18px; padding-right:5px;"> mdi-apps</v-icon> Overview
        </v-tab>
        <v-tab class="overview-class" key="emails">
           <v-icon style="font-size:18px; padding-right:5px;"> mdi-email-outline</v-icon> Emails
        </v-tab>
        <v-tab class="overview-class" key="texts">
           <v-icon style="font-size:18px; padding-right:5px;"> mdi-message-outline</v-icon> Texts
        </v-tab>
        <v-tab class="overview-class" key="notes">
           <v-icon style="font-size:18px; padding-right:5px;">  mdi-text</v-icon> Notes
        </v-tab>
        <v-tab class="overview-class" key="tasks">
            <v-icon style="font-size:18px; padding-right:5px;"> mdi-check</v-icon> Tasks
        </v-tab>
        <v-tab class="overview-class" key="campaign" v-if= "secondaryFlag != 1">
            <v-icon style="font-size:18px; padding-right:5px;"> mdi-format-list-bulleted</v-icon> Campaigns
        </v-tab>
        <v-tab class="overview-class" key="attachments">
            <v-icon style="font-size:18px; padding-right:5px;"> mdi-paperclip</v-icon> Attachments
        </v-tab>
        <v-tab class="overview-class" key="acords" v-if= "secondaryFlag != 1">
            <v-icon style="font-size:18px; padding-right:5px;"> mdi-text-box-multiple</v-icon> Acords
        </v-tab>

        <v-tab class="overview-class" key="logs">
            <v-icon style="font-size:18px; padding-right:5px;"> mdi-history</v-icon> Logs
        </v-tab>

        <v-tab class="overview-class" key="optInOutLogs">
            <v-icon style="font-size:18px; padding-right:5px;"> mdi-history</v-icon> Opt in Out Logs
        </v-tab>
    </v-tabs>

    <v-tabs-items v-model="currentActivityTab">
        <v-tab-item key="overview">
            <!-- <v-card flat>
                <v-card-text>
                    Overview
                </v-card-text>
            </v-card>    -->
            <ba-contact-activity-tabs-overview
                    :contact-id="contactId"
                    :tab="overviewTab"
                    :agency-id="agencyId"
                    @show-reply-email-modal="showReplyEmailModal"
            >
            </ba-contact-activity-tabs-overview>
         <!-- <ba-contact-activity-email-list :contact-id="contactId"></ba-contact-activity-email-list> -->
         <!-- <ba-contact-activity-notes :contact-id="contactId"></ba-contact-activity-notes>  -->
        </v-tab-item>
        <v-tab-item key="emails">
         <ba-contact-activity-email-list
                 :contact-id="contactId"
                 :initial-modal="emailModal"
                 @show-new-email-modal="showNewEmailModal"
                 @show-reply-email-modal="showReplyEmailModal"
         >
         </ba-contact-activity-email-list>
        </v-tab-item>
        <v-tab-item key="texts">
            <ba-contact-activity-text-list :contact-id="contactId"></ba-contact-activity-text-list>
         <!-- <ba-contact-activity-text :contact-id="contactId"></ba-contact-activity-text> -->
        </v-tab-item>
        <v-tab-item key="notes">
          <ba-contact-activity-notes :contact-id="contactId" :tab="noteTab" :initial-modal="noteModal">
          </ba-contact-activity-notes> 
        </v-tab-item>
        <v-tab-item key="tasks">
            <ba-contact-activity-viewtask :contact-id="contactId" :api-load = "taskListing" :initial-modal="taskModal">
            </ba-contact-activity-viewtask>
        </v-tab-item>
        <v-tab-item key="campaign" v-if= "secondaryFlag != 1">
          <ba-contact-activity-campaign-list :contact-id="contactId"></ba-contact-activity-campaign-list>
          <!-- <v-card flat>
                <v-card-text>
                 Campaign
                </v-card-text>
            </v-card> -->
        </v-tab-item>
        <v-tab-item key="attachments">
            <ba-contact-activity-attachments :contact-id="contactId">
            </ba-contact-activity-attachments>
        </v-tab-item>
        <v-tab-item key="acords" v-if= "secondaryFlag != 1">
            <ba-contact-activity-acords :contact-id="contactId">
            </ba-contact-activity-acords>
        </v-tab-item>

        <v-tab-item key="logs">
            <ba-contact-activity-logs :contact-id="contactId">
            </ba-contact-activity-logs>
        </v-tab-item>

        <v-tab-item key="optInOutLogs">
            <ba-contact-activity-opt-logs :contact-id="contactId">
            </ba-contact-activity-opt-logs>
        </v-tab-item>
    </v-tabs-items>
    </div>
</script>

<script>
    Vue.component('<?= ComponentTools::componentName(__FILE__) ?>', {
        template: '#<?= ComponentTools::templateName(__FILE__) ?>',
        props: ['fields', 'objectId', 'contactId','agencyId','secondaryFlag'],
        data: function() {
            return {
                currentActivityTab: null,
                emailModal: false,
                noteModal: false,
                taskModal: false,
                overviewTab: "",
                noteTab: "",
                taskListing : false,
                resetCheckbox : false,
                replyToEmailId: 0,

            }
        },
        methods: {
            // populateContactData: function(data){
            //     Object.entries(data.Contact).forEach((element, index) => this.Contact[element[0]] = element[1])
            // }
            openAddPopup: function(value) {
                if (value == 1) {
                    this.$root.$emit('openAddEmail', 1);
                }
                if (value == 3) {
                    this.$root.$emit('openAddNote', 3);
                }
                if (value == 4) {
                    this.$root.$emit('openAddTask', 4);
                }
            },
            changeTab: function(currentTab) {
                if(currentTab == 0){
                    this.overviewTab = Math.floor(Math.random() * 100);
                    this.$root.$emit('taskListingOnOverviewTab', 346);
                }

                if(currentTab == 3){
                    this.noteTab = Math.floor(Math.random() * 100);
                }
                if(currentTab == 4){
                    this.$root.$emit('taskListingOnTabChange', 4);
                }
                if(currentTab == 9){
                    this.$root.$emit('optInOutListingOnTabChange', 9);
                }
                if(currentTab == 8){
                    this.$root.$emit('logListingOnTabChange', 8);
                }
            },
            showNewEmailModal: function(){
                //console.log('new email from contact activity tab');
                this.$emit('show-new-email-modal');
            },
            showReplyEmailModal: function(emailId){
                //console.log('reply email from contact activity tab', emailId);
                this.$emit('show-reply-email-modal', emailId);
            }
        },
        beforeMount: function() {
            // DataBridge.get('contact', '*', this.populateContactData);
        },
        mounted: function() {
            this.$root.$on('myEvent', (text) => {
                this.currentActivityTab = text;
                this.openAddPopup(text);
            });

            this.$root.$on('tabEvent', (data) => {
                this.currentActivityTab = data;
                if (data == 1) {

                    this.emailModal = !this.emailModal;
                    this.noteModal = false;
                    this.taskModal = false;
                    this.taskListing = false;
                }
                if (data == 3) {
                    this.noteModal = !this.noteModal;
                    this.emailModal = false;
                    this.taskModal = false;
                    this.taskListing = false;
                }
                if (data == 4) {
                    this.taskModal = !this.taskModal;
                    this.noteModal = false;
                    this.emailModal = false;
                    this.taskListing = false;
                }
               
            })
        }


    });
</script>