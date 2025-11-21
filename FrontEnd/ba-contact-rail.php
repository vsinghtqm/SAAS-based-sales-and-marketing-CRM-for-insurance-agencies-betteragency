<!-- BaseInput.vue component -->
<?php use ComponentLibrary\Lib\ComponentTools;

require_once(\ComponentLibrary\Lib\ComponentLibrary::componentPath("ba-field-list"));
require_once(\ComponentLibrary\Lib\ComponentLibrary::componentPath("ba-card-section"));
require_once(\ComponentLibrary\Lib\ComponentLibrary::componentPath("tags/ba-tag-list"));
require_once(\ComponentLibrary\Lib\ComponentLibrary::componentPath("customFields/ba-contact-custom-field-list"));

?>
<style>
.v-dialog.v-dialog--active.v-dialog.v-dialog--active{height:auto !important;}
.v-input__prepend-outer {
    position: absolute;
    right: 0px;
}
.v-application .primary--text {
    color: #817f86 !important;
    caret-color: #817f86 !important;
}
.theme--light.v-text-field--outlined:not(.v-input--is-focused):not(.v-input--has-state):not(.v-input--is-disabled)>.v-input__control>.v-input__slot:hover fieldset {
    color: #d2d1d3 !important;
}
.theme--light.v-text-field--outlined:not(.v-input--is-focused):not(.v-input--has-state)>.v-input__control>.v-input__slot fieldset {
    color: #d2d1d3 !important;
}
label.v-label.v-label--active.theme--light {
    top: 17px;
}
label.v-label.theme--light {
    top: 8px;
}
.v-text-field__details {
    display: none;
}
.v-menu__content.theme--light.v-menu__content--fixed.menuable__content__active {
    margin-top: 36px !important;
}

.v-text-field--filled>.v-input__control>.v-input__slot, .v-text-field--full-width>.v-input__control>.v-input__slot, .v-text-field--outlined>.v-input__control>.v-input__slot {
    min-height: 35px !important;
}
.v-input input {
    max-height: 36px !important;
}

.v-select.v-text-field--outlined:not(.v-text-field--single-line) .v-select__selections {
    padding: 0px 0;
}
.theme--light.v-input, .theme--light.v-input input, .theme--light.v-input textarea {
    font-weight: 400 !important;
    font-size: 16px !important;
    border-width: 1px;
    color: #3a3541;
}
.theme--light.v-label {
    color: rgba(58, 53, 65, 0.68);
    padding-left: 5px;
}
.v-input__icon {
    height: 20px !important;
    min-width: 20px !important;
    width: 20px !important;
}

.theme--light.v-select .v-select__selections {
    color: #3A3541 !important;
}
.v-text-field input {

    padding: 0px 0 !important;

}
.v-select.v-text-field--outlined:not(.v-text-field--single-line) .v-select__selections {
    padding: 0px 0 !important;
}
.v-text-field--enclosed .v-input__append-inner, .v-text-field--enclosed .v-input__append-outer, .v-text-field--enclosed .v-input__prepend-inner, .v-text-field--enclosed .v-input__prepend-outer, .v-text-field--full-width .v-input__append-inner, .v-text-field--full-width .v-input__append-outer, .v-text-field--full-width .v-input__prepend-inner, .v-text-field--full-width .v-input__prepend-outer {
    margin-top: 8px !important;
}
.v-text-field--outlined .v-input__append-outer, .v-text-field--outlined .v-input__prepend-outer {
    margin-top: 9px !important;
}

.v-text-field.v-text-field--enclosed .v-text-field__details, .v-text-field.v-text-field--enclosed:not(.v-text-field--rounded)>.v-input__control>.v-input__slot {
    padding: 0px 8px !important;
    box-shadow: none !important;
    height: 36px !important;
}
.v-card__title.modal-heading {
    padding: 28px 24px !important;
}

.v-input {
    margin-bottom: 0px !important;
}
.v-application .teal {
    background-color: #29AD8E !important;
    border-color: #29AD8E !important;
    box-shadow: 0px 4px 8px -4px rgb(58 53 65 / 42%);
    border-radius: 5px !important;
    width: 79px;
    height: 38px !important;
}
.v-application .teal--text {
    color: #29AD8E!important;
    caret-color: #29AD8E!important;
}
.v-card__actions.pt-0 {
    position: relative;
    top: -7px;
	right:10px;
}
.v-input__slot {
    box-shadow: none !important;
}
.theme--light.v-label {

    padding-left: 5px;
    font-family: 'Roboto';
    font-style: normal;
    font-weight: 400;
    line-height: 20px;
}
.custom-switch .v-label.theme--light {
    font-size: 14px;
    top: 3px;
	color: #3A3541 !important;
}
.heading-rating{
	font-family: 'Roboto';
font-style: normal;
font-weight: 500;
font-size: 14px;
line-height: 20px;
color: #3A3541;
}
.add-mailing-address label { font-family: 'Roboto';
font-style: normal !important;
font-weight: 400 !important;
font-size: 14px !important;
line-height: 20px !important;
color: #3A3541 !important;
top:1px !important;
}
.dropdown-menu.custom-dropdown-action.show {
    left: -23px !important;
}
.dropdown-menu.custom-dropdown.show {
    left: 220px !important;
    top: 20px !important;
}
.dropdown-menu.custom-dropdown-secodary.show {
    left: 6px !important;
    top: 0px !important;
}
.dropdown-menu a {
    font-size: 18px !important;
    color: #3A3541 !important;
    line-height: 28px !important;
    font-weight:400 !important;
    font-family: 'Roboto' !important;
    font-style: normal;
}
.v-list--two-line .v-list-item, .v-list-item--two-line {
    min-height: 58px;
}
.popup-child label.v-label.v-label--active.theme--light {
    top: 9px;
}
.v-input__append-inner i {
    padding-right: 0px !important;
}
.edit-detail-action-button {
    border: 1px solid #29AD8E !Important;

}
.edit-detail-action-button i {
    color: #29AD8E !important;
    background: rgba(41, 173, 142, 0.08);
    height: 30px;
    width: 38px;
    border-left: 1px solid;
    position: absolute;
    right: 0px;
    border-left: 1px solid #29AD8E;
}
.edit-detail-action-button {
    padding: 0px;
    height: 30px !important;
    width: 124px;
}
i.icon-color.mdi.mdi-menu-down.theme--light {
      color: #29AD8E !important;
}

/* width */
.slim-scroll::-webkit-scrollbar, .v-dialog::-webkit-scrollbar {width: 4px;}
.slim-scroll::-moz-scrollbar, .v-dialog::-webkit-scrollbar {width: 4px;}

/* Track */
.slim-scroll::-webkit-scrollbar-track, .v-dialog::-webkit-scrollbar-track {background: #fff;}
.slim-scroll::-moz-scrollbar-track, .v-dialog::--moz-scrollbar-track {background: #fff;}

/* Handle */
.slim-scroll::-webkit-scrollbar-thumb, .v-dialog::-webkit-scrollbar-thumb {background: #BDBDBD;border-radius:5px;}
.slim-scroll::-moz-scrollbar-thumb, .v-dialog::-moz-scrollbar-thumb {background: #BDBDBD;border-radius:5px;}

/* Handle on hover */
.slim-scroll::-webkit-scrollbar-thumb:hover, .v-dialog::-webkit-scrollbar-thumb:hover {background: #ccc;}
.slim-scroll::-moz-scrollbar-thumb:hover, .v-dialog::-moz-scrollbar-thumb:hover {background: #ccc;}

body .v-card__text .primary--text i.v-icon.notranslate {
  color: rgb(41, 173, 142) !important;
}

.contact-delete-button:hover {
    color: red;
}
.v-chip{
    line-height: 18px;
}
.v-chip.v-size--default{
    border-radius:4px ;
    font-size: 13px;
    padding:3px 4px 3px 4px;
}
.theme--light .prospect-chip:not(.prospect-chip--active) {
    background: #e4f2fe;
    border-color: rgba(0,0,0,.12);
    color: #2196F3;
    border-radius: 4px !important;
}
.theme--light .client-chip:not(.client-chip--active) {
    background: #eaf5ea;
    border-color: rgba(0,0,0,.12);
    color: #4CAF50;
    border-radius: 4px !important;
}
.theme--light .inactive-chip:not(.inactive-chip--active) {
    background: #fee8e7;
    border-color: rgba(0,0,0,.12);
    color: #ED3C20;
    border-radius: 4px !important;
    
}
.theme--light .secondary-chip:not(.secondary-chip--active) {
    background: rgba(145, 85, 253, .1);
    color: rgba(130, 90, 196, 1);
    font-size: 13px;
    font-weight: 400;
    border-radius: 4px !important;
}

.delete_new_commercial_contact_btn
{
    background-color: #DE4243;
    color:white;
    margin-top:150px;
    right: 5px;
    font-size: 10px;
    padding: 2px 12px;
}
.delete_new_commercial_contact_btn:hover, .delete_new_commercial_contact_btn:focus {
    color: white;
}

.btn.border-delete-btn {
    background-color: transparent;
    color: #F65559;
    border: 1px solid #8A8D9380;
    padding: 4px 13px;
    font-size: 13px !important;
    text-transform: uppercase;
    font-weight: 500;
    border-radius: 5px;
    line-height: 22px;
}

.custom-padding a.btn.border-delete-btn.btn-sm.delete_new_commercial_contact_btn.delete_business.mt-4 {
    position: absolute;
    left: 20px;
    right: auto;
    bottom: 0;
}
.tooltip-custom .tooltiptext {
     visibility: hidden;
    width: auto;
    background-color: rgba(97, 97, 97, 0.9);
    color: #fff;
    text-align: left;
    border-radius: 6px;
    padding: 4px 9px;
    position: absolute;
    z-index: 1;
    top: 30px;
    font-size: 11px;
    right: 9px;
    margin-left: 0px;
    opacity: 0;
    transition: opacity 0.3s;
    font-weight: 500;
    line-height: 14px;
}

.tooltip-custom:hover .tooltiptext {
  visibility: visible;
  opacity: 1;
}
.v-chip.top-left-badge span.v-chip__content{
    height: auto;
    display: inline-block;
    line-height: 21px;
}
button.switch_dropdown_contact_level {
    color: rgba(41, 173, 142, 1);
}
span.personal-info-name {
    width: auto;
    background: rgba(58, 53, 65, 0.08) !important;
}
.v-chip.top-left-badge{
    display: flex !important;
}

.tooltip-custom.dropdown-item{position: relative;}

.tooltip-custom.dropdown-item .tooltip-custom .tooltiptext {    top: 40px;}

.btn.nowcerts-btn { text-transform: none !important; }
.switch_to_contact_lavel_btn{background: #29AD8E; color: #fff !important; box-shadow: 0px 4px 8px -4px rgb(58 53 65 / 42%); border-radius: 5px !important; font-weight: 500 !important; font-size: 14px !important; line-height: 24px !important;  height: 38px;min-width: 233px;padding: 7px 22px 7px 22px !important;}

.personal-background .v-list-item__title {
    font-size: 12px !important;
}
.personal-background .v-list-item__title.name-title {
    font-size: 16px !important;
    margin: 0;
}
.personal-background .v-list-item__content>:not(:last-child) {
    margin-bottom: 0;
}
.personal-background .v-list-item.v-list-item--two-line.theme--light {
    margin: 30px 0;
}
.bg-transperent{    background-color: transparent;
    height: auto !important;
    min-width: auto !important;
    padding: 0 !important;}
.pb-16px {
    padding-bottom: 16px !important;
}
.px-16px {
    padding-left: 16px !important;
    padding-right: 16px !important;
}
.v-card__title.text-h6.secodarya_lavel {
    padding-top: 16px !important;
}
.v-text-field.v-text-field--enclosed .v-text-field__details, .v-text-field.v-text-field--enclosed:not(.v-text-field--rounded)>.v-input__control>.v-input__slot {
    padding: 0px 16px !important;
 }
.theme--light.v-label {
    padding-left: 0px;}
.btn-secondary-remove-link span.v-btn__content
{
   width: 0;display:inline-block;flex: none;
}
button.btn-remove { font-weight: 500 !important; font-size: 14px !important; line-height: 24px !important;  width: 125px !important; height: 38px !important;}
.tooltip-custom .tooltiptext.right-tooltip {    top: 50px;    right: 6px;}

.v-dialog__content.v-dialog__content--active .v-menu__content.theme--light.menuable__content__active {
max-height: 195px !important;
}
</style>
<script  type="text/x-template" id="<?=ComponentTools::templateName(__FILE__)?>">
    <div style="height:100%; overflow:unset;">
    <v-card style="width: 100%; background-color: #fff; border-radius: 0px; height:100%; overflow:unset;" elevation="0">
        <v-card-text class="pb-2">
            <div class="d-flex justify-content-between">
                <v-chip :class="`${clientStatusChange} top-left-badge`" v-if="clientType" style="font-weight: 500;line-height: 21px;display: inline-block;height: auto">
                    {{clientType}}
                </v-chip>
                <div class="action-buttons">
                    <v-icon
                            @click="showMainContact" small style="color: #3A35418A;">mdi-pencil
                    </v-icon>
                    <span v-if="this.contactData['nowcerts_data'] && Contact.nowcert_database_id != null && this.contactData['nowcerts_data']['access_token']!= null && this.contactData['nowcerts_data']['expires_in']!= null && this.contactData['nowcerts_data']['refresh_token']!= null && this.contactData['nowcerts_data']['token_issued_on']!= null && this.contactData['nowcerts_data']['token_expires_on']!= null">
                    <a @click="openInNowcerts();" target = _blank class="tooltip-custom btn btn-sm nowcerts-btn" data-bs-toggle="tooltip" data-bs-placement="left"><img :src = "`${base_url}/img/nowcertsicon.png`"><span class="tooltiptext right-tooltip">Open contact in NowCerts</span></a>
                    </span>
                    <span v-else-if = "this.contactData['nowcerts_data'] && this.contactData['nowcerts_data']['access_token'] == null && this.contactData['nowcerts_data']['expires_in'] == null && this.contactData['nowcerts_data']['refresh_token'] == null && this.contactData['nowcerts_data']['token_issued_on'] == null && this.contactData['nowcerts_data']['token_expires_on'] == null">
                    <a class="tooltip-custom btn btn-sm nowcerts-btn" data-bs-toggle="tooltip" disabled="disabled" data-bs-placement="left" ><img :src = "`${base_url}/img/nowcertsicon.png`"><span class="tooltiptext right-tooltip">NowCerts features are only <br>available to AMS customers.</span></a>
                    </span>
                    <span v-else-if = "this.contactData['nowcerts_data'] && Contact.nowcert_database_id == null && this.contactData['nowcerts_data']['access_token'] != null && this.contactData['nowcerts_data']['expires_in'] != null && this.contactData['nowcerts_data']['refresh_token'] != null && this.contactData['nowcerts_data']['token_issued_on'] != null && this.contactData['nowcerts_data']['token_expires_on'] != null">
                    <a class="tooltip-custom btn btn-sm nowcerts-btn" data-bs-toggle="tooltip" disabled="disabled" data-bs-placement="left" ><img :src = "`${base_url}/img/nowcertsicon.png`"><span class="tooltiptext right-tooltip">This contact is not <br> synced to NowCerts</span></a>
                    </span>
                </div>
            </div>
        </v-card-text>
    <!-- preferred name does't exits show contact first name and last name -->
        <v-card-title class="contact-card-title-main pt-0 mb-3" style="width: 340px; word-break: break-word;">
            <div v-if="Contact.preferred_name!= null && Contact.preferred_name.trim() != '' " >
			    <span v-if="Contact.preferred_name != ''" >{{ Contact.preferred_name |capitalizedName(Contact.preferred_name)}}</span>
                <span v-if="Contact.last_name != ''" >{{ Contact.last_name |capitalizedName(Contact.last_name)}}</span>
            </div>
            <div v-else>
                <span v-if="Contact.first_name != ''" >{{ Contact.first_name |capitalizedName(Contact.first_name)}}</span>
                <span v-if="Contact.middle_name != ''" >{{ Contact.middle_name |capitalizedName(Contact.middle_name) }}</span>
                <span v-if="Contact.last_name != ''" >{{ Contact.last_name |capitalizedName(Contact.last_name)}}</span>
                <span v-if="Contact.suffix != ''" >{{ Contact.suffix |capitalizedName(Contact.suffix)}}</span>
            </div>
        </v-card-title>
        <v-card-text style="padding-top:0px !important;">
            <div class="header-icon-mdi"> 
                <v-btn elevation="0" small fab @click="showEmailModal">
                    <v-icon
                        v-if = "Contact.is_subscribe == 1">mdi-email-outline
			        </v-icon>
                    <div v-if = "Contact.is_subscribe == 2 || Contact.is_subscribe == 0">
                        <img :src = "`${base_url}/img/emailco.svg`">
                    </div>
                </v-btn>
                <v-btn elevation="0" small fab style="margin-left: 15px;" @click="$root.$emit('tabEvent', 2);">
                    <v-icon
                        v-if = "Contact.is_sms_subscribe == 1">mdi-message-outline
			        </v-icon>
                    <div v-if = "Contact.is_sms_subscribe == 0">
                        <img :src = "`${base_url}/img/msgico.svg`">
                    </div>   
                </v-btn>
                <v-btn elevation="0" small fab style="margin-left: 15px;" @click="$root.$emit('tabEvent', 3);">
                    <v-icon>mdi-text</v-icon>
                </v-btn>
                <v-btn elevation="0" small fab style="margin-left: 15px;" @click="$root.$emit('tabEvent', 4);">
                    <v-icon>mdi-check</v-icon>
                </v-btn>
                <v-btn depressed rounded elevation="0" style="margin-left: 10px;" :href="`${base_url}contacts?id=${contactId}`" v-if = "secondary_contact_flag != 1"
                     class="legecy-btn">
                    Return to Legacy
                </v-btn>
            </div>
        </v-card-text>
        <div class="slim-scroll" style="height:900px; overflow-y:auto;">
		<v-card-text class="content-details" style="padding-top:4px !important;">
            <div class="d-flex mb-3">
                    <div class="left-content-new-card" style="width: 42%; display: inline-block;">
                        Contact Level
                    </div>
                    <div class="right-content-new-card" style="width: 56%; display: inline-block;">
                        <div class="dropdown_switch_contact_level pl-1" @mouseleave="dropdownOpenPrimary = false, dropdownOpenSecondary = false, dropdownOpen = false">
                            <button @click="toggleDropdownSwitchContacts" class="dropdown-button switch_dropdown_contact_level">
                                {{primarySecondaryContactLabel==1?'Secondary Contact':'Primary Contact'}}
                                <i :class="dropdownOpen ? 'mdi mdi-menu-up' : 'mdi mdi-menu-down'" class="fa"></i>
                            </button>
                            <ul class="switch_to_contact_lavel" v-if="dropdownOpenPrimary && primarySecondaryContactLabel == 1" style="list-style-type: none; cursor: pointer;">
                                <li @click="secondaryModalChanges">Switch to Primary</li>
                            </ul>
                            <ul class="switch_to_contact_lavel" v-if="dropdownOpenSecondary && primarySecondaryContactLabel != 1" style="list-style-type: none; cursor: pointer;">
                                <li @click="secondaryModalChanges">Switch to Secondary</li>
                            </ul>
                        </div>
                    </div>
            </div>
		 <ba-card-section style="width: 100%" no-title no-edit edit-string="maincontactinfo">

                <ba-field-list  :fields="summaryFields" :object-id="contactId"  v-if="renderComponent" :isSecondaryContact = "secondary_contact_flag"  :secondaryContactCheck = "secondaryCheck">
                </ba-field-list>

            </ba-card-section>
		</v-card-text>
        <v-card-text class="content-details" style="border-top: 1px solid rgba(58, 53, 65, 0.12); padding-right :0px !important;">
                <ba-card-section  class="heading-text" title="contact details" :contact-id="contactId" edit-string="contactinfo" :contact-data="contactData" :opt-status="smsOptInOut" :contact-additional-email="contactAdditionalEmail" :contact-addtitional-phone="contactAddtitionalPhone" :mailing-address="MailingAddress" @renderContactDetailsleftSideBar="renderContactDetailsleftSideBar" v-if="renderContactDetailsComponent">
                    <ba-field-list :fields="contactInfo" :object-id="contactId" :addressDiff="mailingAddressType"   >
                    </ba-field-list>

                </ba-card-section>
        </v-card-text>
        <v-card-text class="content-details" style="border-top: 1px solid rgba(58, 53, 65, 0.12); padding-right :0px !important;">

                <ba-card-section class="heading-text" title="Personal Details" :contact-id="contactId" edit-string="personalinfo" :contact-data="contactData" @renderPersonalDeatilsleftSideBar="renderPersonalDeatilsleftSideBar" v-if="renderPersonalDeatilsComponent">

                    <ba-field-list :fields="personalDetails" :object-id="contactId" :isSecondaryContact = "secondary_contact_flag" :secondaryContactCheck = "secondaryCheck">
                    </ba-field-list>
                </ba-card-section>
                

                <div class="contact-custom-field-list">
                    <ba-contact-custom-field-list :contact-id="contactId" :object-id="contactId" :agency-id = "agencyId" @renderPersonalDeatilsleftSideBar="renderPersonalDeatilsleftSideBar"  v-if="renderPersonalDeatilsComponent"> </ba-contact-custom-field-list >
                </div>
        </v-card-text>

        <v-card-text class="content-details" style="border-top: 1px solid rgba(58, 53, 65, 0.12); padding-right :0px !important;" v-if=" secondary_contact_flag != 1">

                <ba-card-section class="heading-text" title="Tags" :contact-id="contactId" :agency-id = "agencyId" edit-string="editTagsInfo" :contact-data="contactData"  @renderleftSideBar="renderleftSideBar"  v-if="renderComponent">
                    <ba-tag-list :contact-id="contactId" >
                    </ba-tag-list>

                </ba-card-section>
                <v-icon
				 @click="showDeleteContact" small class = "btn border-delete-btn mt-3 contact-delete-button delete_new_commercial_contact_btn" style="" v-if = "secondary_contact_flag != 1">Delete Contact
			    </v-icon>
        </v-card-text>

        <form id = 'sso_external_login_form' method='POST' target="_blank" :action="action_url" ref="externalLoginForm" style="display:none;" >
        <input type = "hidden" id="sso-code" name= "code" v-model="ssoCode"></input>
        <button type="submit" id = 'sso_submit'>Submit</button>
        </form>
		</div>
        

        <v-dialog v-model="dialog" max-width="800" persistent>
            <template v-slot:default="dialog">
                <v-card >
                    <v-card-title class="modal-heading">
                        Edit Main Details
						<div @click="closeDetailModal();" class="cross-icon"><a text=""><i aria-hidden="true" class="v-icon notranslate mdi mdi-close theme--light"></i></a></div>
                    </v-card-title>
                        <v-card-text class="pt-0 pb-0">
                            <v-container class="pl-0 pr-0  slim-scroll model-height-auto" ref="scrollContainer">
                                <v-row>

                                    <v-col cols="12" sm="4" md="4">

                                        <v-text-field
                                        label="First Name*"
                                        v-model="Contact.first_name"
                                        value="Contact.first_name"
                                        dense
                                        outlined
                                        @change="mainDetailChanged = true;"
                                        @keyup = "enableMainSaveBtn();"
                                        class="str-capitalize"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="2">
                                        <v-text-field
                                            label="Middle Name"
                                            v-model="Contact.middle_name"
                                            value="Contact.middle_name"
                                            dense
                                            outlined
                                            class="str-capitalize"
                                            @keyup = "enableMainSaveBtn();"
                                            @change="mainDetailChanged = true;"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="4">
                                        <v-text-field
                                            label="Last Name*"
                                            v-model="Contact.last_name"
                                            value="Contact.last_name"
                                            dense
                                            outlined
                                            class="str-capitalize"
                                            @change="mainDetailChanged = true;"
                                            @keyup = "enableMainSaveBtn();"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="2">
                                        <v-text-field
                                            label="Suffix"
                                            v-model="Contact.suffix"
                                            value="Contact.suffix"
                                            dense
                                            outlined
                                            class="str-capitalize"
                                            @keyup = "enableMainSaveBtn();"
                                            @change="mainDetailChanged = true;"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="4">
                                        <v-text-field
                                            label="Preferred Name"
                                            v-model="Contact.preferred_name"
                                            value="Contact.preferred_name"
                                            dense
                                            outlined
                                            class="str-capitalize"
                                            @change="mainDetailChanged = true;"
                                            @keyup = "enableMainSaveBtn();"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col class="d-flex" cols="12" sm="4" v-if = "secondary_contact_flag == 1">
                                        <v-select
                                        :items="relationshipStatus"
                                        item-text="name"
                                        item-value="value"
                                        label="Relationship to Primary"
                                        v-model="relationWithPrimary"
                                        :menu-props="{ top: false, offsetY: true }"
                                        @change="mainDetailChanged = true; enableMainSaveBtn();"
                                        dense
                                        outlined
                                        ></v-select>
                                    </v-col>
                                    <v-col class="d-flex" cols="12" sm="4" v-if = "secondary_contact_flag == 1">
                                        <v-select
                                        :items="secondaryContactStatus"
                                        item-text="name"
                                        item-value="value"
                                        v-model="statusWithPrimary"
                                        label="Status"
                                        :menu-props="{ top: false, offsetY: true }"
                                        @change="mainDetailChanged = true; enableMainSaveBtn();"
                                        dense
                                        outlined
                                        ></v-select>
                                    </v-col>
                                    <v-col cols="12" sm="8"></v-col>
                                    <div style="width: 97%;height: 1px;background: #e7e7e8; margin:10px auto;"></div>
                                    <v-col cols="12" sm="3" class="pb-0">
                                        <label class="sub-details mb-4">{{secondaryContactTitle}}</label>
                                    </v-col>

                                    <!-- <v-col cols="12" sm="9" class="text-right pb-0" v-if= "secondary_contact_flag != 1"> -->
                                    <v-col cols="12" sm="9" class="text-right pb-0">
                                        <v-btn class="add-btn-main ml-auto btn btn-outline-success btn-round action-btn-modal edit-detail-action-button" id="actiondropdownMenuButton" data-toggle="dropdown">&nbsp; &nbsp;Add Link &nbsp; &nbsp; <v-icon class="icon-color">mdi-menu-down</v-icon></v-btn>


                                        <div class="dropdown-menu custom-dropdown-action" aria-labelledby="actiondropdownMenuButton" style="min-width:6rem; left:-38px !important;">
                                            <a @click="personalModal" class="dropdown-item" href="#" style = "color: #3A3541;" >Personal</a>
                                            <a @click = "commercialModal"class="dropdown-item" href="#" style = "color: #3A3541;">Commercial</a>

                                        </div>
                                    </v-col>
									</v-row>


									<div class="">

									<v-row class="mt-3">
                                    <v-col style="padding: 12px;max-width: 282px;" v-if= "secondary_contact_flag != 1">
                                        <v-card class="pa-2 personal-background drop-down-show" outlined tile style="padding:25px 15px !important;box-shadow:none !important;">
                                            <v-list-item class="p-0">
                                                <v-list-item-content class="pt-0">
                                                    <v-list-item-title class="manage-account-setting">PERSONAL</v-list-item-title>
                                                </v-list-item-content>
                                            </v-list-item>
                                            <v-list-item class="p-0" two-line v-for="secondaryContact in secondaryContacts" v-if="secondaryContact.status == 1">
                                                <v-list-item-content  class="pt-0 pb-0">
                                                    <v-list-item-title class="name-title">{{secondaryContact.contact.first_name}} {{secondaryContact.contact.middle_name}} {{secondaryContact.contact.last_name}}</v-list-item-title>
												<!-- <span class="ellipsis-iocn"> <i class="fa fa-ellipsis-h"></i></span> -->
                                            
                                                    <div>
                                                        <span class="ellipsis-iocn" id="dropdownMenuButton" data-toggle="dropdown" style="cursor:pointer;"> <i class="fa fa-ellipsis-h"></i></span>
                                                        <div class="dropdown-menu custom-dropdown" aria-labelledby="dropdownMenuButton" style="padding:8px 0">
                                                        <a class="dropdown-item" :href="`${base_url}contacts-v2/viewContact/${secondaryContact.additional_insured_contact_id}`" target="_blank" style="line-height: 24px !important;padding: 8px 16px 8px 16px;">View Contact</a>
                                                        <a v-if = "!secondaryContact.isPrimary" class="dropdown-item" :id="`${secondaryContact.additional_insured_contact_id}`" @click="deleteSecondary(secondaryContact.contact_id,secondaryContact.additional_insured_contact_id)" style="color: #3A3541; line-height: 24px !important;padding: 8px 16px 8px 16px;">Remove Link</a>
                                                        </div>
                                                    </div>
                                                    <v-list-item-subtitle class="name-subtitle">

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_SPOUSE && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_LIVING_IN_HOME">Spouse/Living In Home</element>


                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_SPOUSE && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_AWAY_FROM_HOME">Spouse/Away From Home</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_SPOUSE && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_DECEASED">Spouse/Deceased</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_SPOUSE && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_OTHER">Spouse/Other</element>


                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_CHILD && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_LIVING_IN_HOME">Child/Living In Home</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_CHILD && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_AWAY_FROM_HOME">Child/Away From Home</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_CHILD && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_DECEASED">Child/Deceased</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_CHILD && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_OTHER">Child/Other</element>


                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_RELATIVE && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_LIVING_IN_HOME">Relative/Living In Home</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_RELATIVE && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_AWAY_FROM_HOME">Relative/Away From Home</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_RELATIVE && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_DECEASED">Relative/Deceased</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_RELATIVE && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_OTHER">Relative/Other</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_FRIEND && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_LIVING_IN_HOME">Friend/Living In Home</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_FRIEND && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_AWAY_FROM_HOME">Friend/Away From Home</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==4 && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_DECEASED">Friend/Deceased</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_FRIEND && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_OTHER">Friend/Other</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_OTHER && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_LIVING_IN_HOME">Other/Living In Home</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_OTHER && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_AWAY_FROM_HOME">Other/Away From Home</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_OTHER && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_DECEASED">Other/Relative</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_OTHER && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_OTHER">Other/Other</element>

                                                </v-list-item-subtitle>

                                                </v-list-item-content>
                                            </v-list-item>
                                        </v-card>
                                    </v-col>


                                    <v-col style="padding: 12px;max-width: 272px;" v-if= "secondary_contact_flag != 1">
                                        <v-card class="pa-2 personal-background drop-down-show" outlined tile style="padding:25px 15px !important;box-shadow:none !important;">
                                        <v-list-item class="p-0">
                                                <v-list-item-content class="pt-0">
                                                    <v-list-item-title class="manage-account-setting">COMMERCIAL</v-list-item-title>
                                                </v-list-item-content>
                                            </v-list-item>
                                            <v-list-item class="pl-1 pt-0" two-line v-for="commercialContact in linkedCommercialContacts">
                                                <v-list-item-content  class="pt-0 pb-0">
												<!-- <span class="ellipsis-iocn"> <i class="fa fa-ellipsis-h"></i></span> -->
                                                    <div>
                                                        <span class="ellipsis-iocn" id="dropdownMenuButton" data-toggle="dropdown" style="cursor:pointer;"> <i class="fa fa-ellipsis-h"></i></span>
                                                        <div class="dropdown-menu custom-dropdown" aria-labelledby="dropdownMenuButton">
                                                            <a class="dropdown-item" :href="`${base_url}business?id=${commercialContact.contact_busines.id}`" target="_blank">View</a>
                                                        </div>
                                                    </div>
                                                    <v-list-item-title class="name-title">{{commercialContact.contact_busines.name}}</v-list-item-title>
                                                    <v-list-item-subtitle v-if="commercialContact.relationship_type==1">Spouse</v-list-item-subtitle>
                                                    <v-list-item-subtitle v-if="commercialContact.relationship_type==2">Billing</v-list-item-subtitle>
                                                    <v-list-item-subtitle v-if="commercialContact.relationship_type==3">Partner</v-list-item-subtitle>
                                                    <v-list-item-subtitle v-if="commercialContact.relationship_type==4">Owner</v-list-item-subtitle>
                                                    <v-list-item-subtitle v-if="commercialContact.relationship_type==5">Other</v-list-item-subtitle>

                                                </v-list-item-content>
                                            </v-list-item>
                                        </v-card>
                                    </v-col>
                                    <!-- for primary contact -->
                                    <v-col style="padding: 12px;max-width: 282px;" v-if= "secondary_contact_flag == 1">
                                        <v-card class="pa-2 personal-background drop-down-show" outlined tile style="padding:25px 15px !important;box-shadow:none !important;">
                                            <v-list-item class="p-0">
                                                <v-list-item-content class="pt-0">
                                                    <v-list-item-title class="manage-account-setting">PERSONAL</v-list-item-title>
                                                </v-list-item-content>
                                            </v-list-item>
                                            <v-list-item class="p-0" two-line v-for="secondaryContact in primaryContactName" v-if="secondaryContact.status == 1">
                                                <v-list-item-content  class="pt-0 pb-0">
                                                    <v-list-item-title class="name-title">{{secondaryContact.contact.first_name}} {{secondaryContact.contact.middle_name}} {{secondaryContact.contact.last_name}}</v-list-item-title>
												<!-- <span class="ellipsis-iocn"> <i class="fa fa-ellipsis-h"></i></span> -->
                                            
                                                    <div>
                                                        <span class="ellipsis-iocn" id="dropdownMenuButton" data-toggle="dropdown" style="cursor:pointer;"> <i class="fa fa-ellipsis-h"></i></span>
                                                        <div class="dropdown-menu custom-dropdown" aria-labelledby="dropdownMenuButton" style="padding:8px 0">
                                                        <a class="dropdown-item" :href="`${base_url}contacts-v2/viewContact/${secondaryContact.additional_insured_contact_id}`" target="_blank" style="line-height: 24px !important;padding: 8px 10px 8px 10px;">View Contact</a>
                                                        <a v-if = "!secondaryContact.isPrimary" class="dropdown-item" :id="`${secondaryContact.additional_insured_contact_id}`" @click="deleteSecondary(secondaryContact.contact_id,secondaryContact.additional_insured_contact_id)" style="color: #3A3541; line-height: 24px !important;padding: 8px 10px 8px 10px;">Remove Link</a>
                                                        </div>
                                                    </div>
                                                    <v-list-item-subtitle class="name-subtitle">

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_SPOUSE && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_LIVING_IN_HOME">Spouse/Living In Home</element>


                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_SPOUSE && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_AWAY_FROM_HOME">Spouse/Away From Home</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_SPOUSE && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_DECEASED">Spouse/Deceased</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_SPOUSE && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_OTHER">Spouse/Other</element>


                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_CHILD && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_LIVING_IN_HOME">Child/Living In Home</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_CHILD && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_AWAY_FROM_HOME">Child/Away From Home</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_CHILD && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_DECEASED">Child/Deceased</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_CHILD && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_OTHER">Child/Other</element>


                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_RELATIVE && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_LIVING_IN_HOME">Relative/Living In Home</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_RELATIVE && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_AWAY_FROM_HOME">Relative/Away From Home</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_RELATIVE && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_DECEASED">Relative/Deceased</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_RELATIVE && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_OTHER">Relative/Other</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_FRIEND && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_LIVING_IN_HOME">Friend/Living In Home</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_FRIEND && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_AWAY_FROM_HOME">Friend/Away From Home</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==4 && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_DECEASED">Friend/Deceased</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_FRIEND && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_OTHER">Friend/Other</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_OTHER && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_LIVING_IN_HOME">Other/Living In Home</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_OTHER && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_AWAY_FROM_HOME">Other/Away From Home</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_OTHER && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_DECEASED">Other/Relative</element>

                                                        <element v-if="secondaryContact.relationship_with_contact==_RELATION_TYPE_OTHER && secondaryContact.insured_contact_status==_SECONDARY_CONTACT_STATUS_OTHER">Other/Other</element>

                                                </v-list-item-subtitle>

                                                </v-list-item-content>
                                            </v-list-item>
                                        </v-card>
                                    </v-col>

                                </v-row>
								</div>
                            </v-container>

                        <v-card-actions class="fixed-footer-modal model-footer-fix ">
                            <v-spacer></v-spacer>
                            <v-btn
                                color="teal"
                                text
                                @click="closeDetailModal();"
                            >
                            Cancel
                            </v-btn>

                            <v-btn
                                v-if = "mainDisabled"
                                justify="space-around"
                                depressed
                                color="teal"
                                dark
                                @click="saveMainContact()"
                               >
                                Save
                            </v-btn>

                            <v-btn
                            v-else
                            justify="space-around"
                            depressed
                            disabled
                            >
                            Save
                         </v-btn>

                        </v-card-actions>
                        </v-card-text>
                </v-card>
                <v-dialog  v-model="deleteSecondaryContactDialog" persistent max-width="380" max-height="156">
                    <v-card>
                        <v-card-title class="text-h6 delete-policy-heading" style='padding: 16px !important;'>
                            Remove Link
                        </v-card-title>
                        <v-card-text class="delete-policy-text" style='padding: 0px 16px 16px !important;'>Are you sure you want to remove this link?</v-card-text>
                    <v-card-actions>
                    <v-spacer></v-spacer>
                        <v-btn class="btn-remove btn-secondary-remove-link"
                            color="#757575"
                            text
                            @click="deleteSecondaryContactDialog = false"
                        >
                            Cancel
                        </v-btn>
                        <v-btn class="btn-delete"
                            color="#F65559"
                            text
                            @click="deleteSecondaryContact"
                        >
                            Yes, Remove
                        </v-btn>
                    </v-card-actions>
                    </v-card>
		</v-dialog>
            </template>
        </v-dialog>

        <v-dialog v-model="personaldialog" max-height="280px" max-width="380px">
			<template v-slot:default="personaldialog">
				<v-card class="popup-child">
					<v-card-title class="modal-heading-sub-popup">
						Add Personal Link
					</v-card-title>
					<v-card-text class="pt-0 pb-0">
                    <v-container class="pl-0 pr-0 pt-0">
							<v-row>
								<v-col cols="12" class="search_client_field" md="12">
                                <span class="error_class" :class="[errorFound==1 ? 'd-flex' : 'd-none']">{{error_message}}</span>
                                   <v-autocomplete
									:items = "secondary_contact_arr"
									item-text="name"
									:search-input.sync="searchText"
									item-value="id"
									v-model="secondaryContact"
									dense
									outlined
									required
									@keyup = "getSecondaryContact"
									attach
                                    label="Search clients"
                                   @change="enableSaveLinkBtn"
									></v-autocomplete>
									<v-icon class="sub-popup-search">mdi-magnify</v-icon>
								</v-col>

								<v-col
									class="d-flex"
									cols="12"
									md="12"
								>
									<v-select
									:items="relationshipStatus"
									item-text="name"
									item-value="value"
									label="Select label"
									v-model="relationwithcontact"
                                    :menu-props="{ top: false, offsetY: true }"
									dense
									outlined
									></v-select>
								</v-col>

								<v-col
									class="d-flex"
									cols="12"
									md="12"
								>
									<v-select
									:items="secondaryContactStatus"
									item-text="name"
									item-value="value"
									v-model="secondarystatus"
									label="Select status"
                                    :menu-props="{ top: false, offsetY: true }"
									dense
									outlined
									></v-select>
								</v-col>

								<v-col cols="12" md="12">
									<v-text-field
										placeholder="Search clients"
										outlined
										dense
										label="Secondary Id"
										v-model="secondaryContactId"
										:class="[disabled==0 ? 'd-none' : 'd-none']"
									></v-text-field>

								</v-col>
							</v-row>
						</v-container>
					</v-card-text>
					<v-card-actions>
						<v-spacer></v-spacer>
						<v-btn
							color="teal"
							text
							@click="cancelPersonalDialog();personaldialog.value = false;disabled=1; errorFound = 0; error_message =''"
						>
						Cancel
						</v-btn>
                        <v-btn
							color="teal"
							text
							@click="showAddSecondaryContactDialog();"
						>
						Create New
						</v-btn>
						<v-btn
							depressed
							:disabled="disabled == 1"
							:class="disabled==0 ?'btn_link':''"
							@click="saveSecondaryContact"
							>
							Save
						</v-btn>
					</v-card-actions>
				</v-card>
			</template>
        </v-dialog>

        <v-dialog v-model="commercialdialog" max-height="224px" max-width="380px">
			<template v-slot:default="commercialdialog">
				<v-card class="popup-child">
					<v-card-title class="modal-heading-sub-popup">
						Add Commercial Links
					</v-card-title>
					<v-card-text class="pt-0 pb-0">
                    <v-container class="pl-0 pr-0 pt-0">
							<v-row>
								<v-col cols="12" class="search_client_field" md="12">
                                    <span class="error_class" :class="[errorFound==1 ? 'd-flex' : 'd-none']">{{error_message}}</span>
									 <v-autocomplete
                                        :items = "commercial_contact_arr"
                                        item-text="name"
                                        :search-input.sync="searchCommercial"
                                        item-value="id"
                                        v-model="commercialContacts"
                                        dense
                                        outlined
                                        required
                                        @keyup = "getCommercialContact"
                                        label="Search companies"
                                        @change="enablecommercialSaveLinkBtn"
									></v-autocomplete>
									<v-icon class="sub-popup-search">mdi-magnify</v-icon>
								</v-col>

								<v-col
									class="d-flex mt-1"
									cols="12"
									md="12"
								>
									<v-select
									:items="businessRollTypes"
									item-text="name"
									item-value="value"
									label="Select business role"
                                    v-model="businessRole"
                                    :menu-props="{ top: false, offsetY: true }"
									dense
									outlined
									></v-select>
								</v-col>



								<v-col cols="12" md="12">
									<v-text-field
										placeholder="Search companies"
										outlined
										dense
										label="Business Id"
										v-model="commercialId"
										:class="[disabled==0 ? 'd-none' : 'd-none']"
									></v-text-field>

								</v-col>
							</v-row>
						</v-container>
					</v-card-text>
					<v-card-actions>
						<v-spacer></v-spacer>
						<v-btn
							color="teal"
							text
							@click="cancelCommercialContact();commercialdialog.value = false;disabled=1;"
						>
						Cancel
						</v-btn>
						<v-btn
							depressed
							:disabled="disabled == 1"
							:class="disabled==0 ?'btn_link':''"
							@click="saveCommercialContact()"
							>
							SAVE
						</v-btn>
					</v-card-actions>
				</v-card>
			</template>
        </v-dialog>
        <!-- Add New Secondary Contact Dialog-->
             <v-dialog v-model="addSecondaryContactDialog" max-width="800" >
            <template v-slot:default="dialog">
                  <v-card class="v-sheet theme--light">
                    <v-card-title class="modal-heading">
                        New Link Contact
						<div @click="dialog.value = false" class="cross-icon"><a text=""><i aria-hidden="true" class="v-icon notranslate mdi mdi-close theme--light"></i></a></div>
                    </v-card-title>
                        <div class="slim-scroll" style="height: 100%; overflow: hidden auto; max-height: 58vh; scrollbar-width: thin;">
                        <v-card-text class="pt-0 pb-0">
                            <v-container class="pl-0 pr-0 pt-0 pb-0">
                                <v-row>
                                    <v-col cols="12" sm="12" class="pb-0">
                                        <label class="sub-details">Main Information</label>
                                    </v-col>
                                </v-row>

                                <v-row>
                                 <v-col cols="12" sm="3" md="4">

                                        <v-text-field
                                        label="First Name*"
                                        v-model = "secondaryFirstName"
                                        value=""
                                        dense
                                        outlined
                                        @keyup = "enableSecondarySaveBtn();checkSecondaryContactFormChange();"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="3" md="2">
                                        <v-text-field
                                            label="Middle Name"
                                            v-model = "secondaryMiddleName"
                                            value=""
                                            dense
                                            outlined
                                            @change= "checkSecondaryContactFormChange();"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="3" md="4">
                                        <v-text-field
                                            label="Last Name*"
                                            v-model = "secondaryLastName"
                                            value=""
                                            dense
                                            outlined
                                            @keyup = "enableSecondarySaveBtn();checkSecondaryContactFormChange();"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="3" md="2">
                                        <v-text-field
                                            label="Suffix"
                                            v-model = "secondarySuffix"
                                            value=""
                                            dense
                                            outlined
                                            @change= "checkSecondaryContactFormChange();"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="3" md="4">
                                        <v-text-field
                                            label="Preferred Name"
                                            v-model = "secondaryPreferredName"
                                            value=""
                                            dense
                                            outlined
                                            @change= "checkSecondaryContactFormChange();"
                                        ></v-text-field>
                                    </v-col>

                                    <v-col cols="12" sm="3" md="4">
                                    <v-menu
                                    v-model="menu3"
                                    :close-on-content-click="false"
                                    max-width="290"
                                >
                                    <template v-slot:activator="{ on, attrs }">
                                    <v-text-field
                                        v-model="birthDateFormatted"
                                        :value="birthDateFormatted"
                                        label="Birthday"
                                        readonly
                                        v-bind="attrs"
                                        v-on="on"
                                        @click:clear="date2 = null"
                                        prepend-icon="mdi-calendar"
                                        dense
                                        outlined
                                    ></v-text-field>
                                    </template>
                                    <v-date-picker
                                    v-model="date2"
                                    @change="menu3 = false;checkSecondaryContactFormChange"
									color="#29AD8E"
									no-title
                                    :max="today"
                                    ></v-date-picker>
                                </v-menu>
                                </v-col>
                                    <v-col class="p-0" cols="12"></v-col></v-col>
                                    <v-col cols="12" sm="3" md="4">
                                    <v-select
                                            v-model="secondaryRelationship"
                                            :items="relationshipType"
                                            item-text="name"
                                            item-value="value"
                                            label="Relationship"
                                            @change= "checkSecondaryContactFormChange();"
                                            dense
                                            outlined
                                            :menu-props="{ top: false, offsetY: true }"
                                            attach
                                            >

                                        </v-select>
                                    </v-col>
                                    <v-col cols="12" sm="3" md="4">
                                        <v-select
                                            v-model="secondaryStatusType"
                                            :items="secondaryStatusTypes"
                                            item-text="name"
                                            item-value="value"
                                            label="Status"
                                            @change= "checkSecondaryContactFormChange();"
                                            dense
                                            outlined
                                            :menu-props="{ top: false, offsetY: true }"
                                            attach
                                            >
                                        </v-select>
                                    </v-col>
                                    <v-col class="p-0" cols="12"></v-col></v-col>
                                     <v-col cols="12" sm="12" md="3">
                                    <v-switch class="custom-switch"
                                         v-model="secondaryDoNotContact"
                                        :label="`Do Not Contact`"
                                        @change= "checkSecondaryContactFormChange();"
                                        color="teal"
                                        >
                                    </v-switch>
                                </v-col>

                                    <v-col cols="12" sm="8"></v-col>

                                    <div style="width: 97%;height: 1px;background: #e7e7e8; margin:10px auto;"></div>
                                    <v-col cols="12" sm="3" class="">
                                        <label class="sub-details">Contact Information</label>
                                    </v-col>

									</v-row>
									<v-row>
                                    <v-col cols="12" sm="4" md="4">
                                        <v-text-field
                                        label="Phone"
                                        v-model="secondaryPhone"
                                        value="phone"
                                        dense
                                        outlined
                                        color="teal"
                                        @blur = "validatePhoneNumber($event);checkSecondaryContactFormChange();"
                                        ></v-text-field>
                                        <span style="color:red">{{ phoneNumberValidationText }}</span>
                                    </v-col>
                                  <v-col cols="12" sm="3" md="3">
                                    <v-select
                                    v-model="secondaryPhoneNumberType"
                                    :items="phoneType"
                                    item-text="name"
                                    item-value="value"
                                    label="Type"
                                    dense
                                    outlined
                                    @change= "checkSecondaryContactFormChange();"
                                    :menu-props="{ top: false, offsetY: true }"
                                    attach
                                    ></v-select>
                                </v-col>
                                <v-col class="p-0" cols="12" sm="12" md="12"></v-col>
                                <v-col cols="12" sm="4" md="4">
                                    <v-text-field
                                    label="Email"
                                    v-model="secondaryEmail"
                                    value="email"
                                    dense
                                    outlined
                                    @blur="validateEmail();checkSecondaryContactFormChange();"
                                    ></v-text-field>
                                    <span style="color:red">{{ emailExistText }}</span>
                                </v-col>
                                <v-col cols="12" sm="3" md="3">
                                    <v-select
                                    v-model="secondaryEmailType"
                                    :items="emailType"
                                    item-text="name"
                                    item-value="value"
                                    label="Type"
                                    dense
                                    outlined
                                    @change= "checkSecondaryContactFormChange();"
                                    :menu-props="{ top: false, offsetY: true }"
                                    attach
                                    ></v-select>
                                </v-col>

                                <v-col class="p-0" cols="12" sm="12" md="12"></v-col>
                                <v-col cols="12" sm="4">
                                    <v-select
                                    v-model="secondaryBestTimeToReach"
                                    :items="bestTimeToReach"
                                    item-text="name"
                                    item-value="value"
                                    label="Best Time To Contact"
                                    @change= "checkSecondaryContactFormChange();"
                                    dense
                                    outlined
                                    :menu-props="{ top: false, offsetY: true }"
                                    attach
                                    ></v-select>
                                </v-col>
                                <v-col cols="12" sm="8" md="8"></v-col>
                                    <div style="width: 97%;height: 1px;background: #e7e7e8; margin: 20px auto;"></div>
                                    <v-col cols="12" sm="3" md="3">
                                        <v-text-field
                                        label="Street Address"
                                        v-model="secondaryAddress"
                                        value="address"
                                        @change= "checkSecondaryContactFormChange();"
                                        dense
                                        outlined
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="3" md="3" v-if="contactData.address_line_2 || showAddressLine2">
                                        <v-text-field
                                        label="Address Line 2"
                                        v-model="secondaryAddressLineTwo"
                                        value="address"
                                        @change= "checkSecondaryContactFormChange();"
                                        dense
                                        outlined
                                        ></v-text-field>
                                    </v-col>
                                <v-col cols="12" sm="3" md="2">
                                    <v-text-field
                                    label="City"
                                    v-model="secondaryCity"
                                    value="city"
                                    @change= "checkSecondaryContactFormChange();"
                                    dense
                                    outlined
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="2">
                                    <v-select
                                        label="State"
                                        v-model="secondaryStateId"
                                        :items="states"
                                        item-text="name"
                                        item-value="id"
                                        dense
                                        outlined
                                        @change= "checkSecondaryContactFormChange();"
                                        :menu-props="{ top: false, offsetY: true, contentClass:'min-width-180' }"
                                        attach
                                    ></v-select>
                                </v-col>
                                <v-col cols="12" sm="3" md="2">
                                    <v-text-field
                                    label="Zip"
                                    v-model="secondaryZip"
                                    value="zip"
                                    dense
                                    outlined
                                    @change= "checkSecondaryContactFormChange();"
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="12" :class="[isPrimaryAddressSame ? 'd-none' : 'd-flex add-mailing-address']">
                                    <v-checkbox
                                        v-model="sameAsPrimaryAddress"
                                        :label="`Same as primary address`"
                                        @click="showMailingAddress();checkSecondaryContactFormChange();"
                                        >
                                    </v-checkbox>

                                </v-col>
                                </v-row>

                            </v-container>
                        </v-card-text>
                </div>
                      <v-card-actions class="px-5 py-5">
                            <v-spacer></v-spacer>
                            <v-btn
                                color="teal"
                                text
                                @click="showCancelDailog();"
                            >
                            Cancel
                            </v-btn>

                            <v-btn
                            v-if = "secondaryDisabled"
                            class="btn-save-create-service v-btn v-btn--text theme--light v-size--default"
                            justify="space-around"
                            depressed
                            color="teal"
                            dark
                            @click="addSecondaryContact()"
                             >
                            Save
                         </v-btn>
                         <v-btn
                            v-else
                            justify="space-around"
                            depressed
                            disabled
                            >
                            Save
                         </v-btn>

                        </v-card-actions>
                        </v-card>
            </template>
        </v-dialog>
        <!-- Add New Secondary Contact Dialog-->
        <v-snackbar class="success-alert" v-model="snackbar" :timeout="timeout">
			<v-icon class="success-alert-icon pr-1">mdi-checkbox-marked-circle</v-icon>
				<span class="success-alert-text" v-if="secondaryContactSavedStatus">{{ secondaryContactSavedText }}</span>
                <span class="success-alert-text" >{{ successMessage }}</span>                
		</v-snackbar>
        <v-dialog
        v-if="unsavedDialogSecondaryContact"
        v-model="unsavedDialogSecondaryContact"
        persistent
        max-width="600"
	    max-height="184"
    >
			<v-card style="height:184px;">
				<div class="v-card__title text-h6 delete-policy-heading">
				Close new link contact
				</div>

				<div class="v-card__text delete-policy-text">There are unsaved changes. If you would like to save changes, press the ‘Keep Editing’ button.</div>
				<v-card-actions>
				<v-spacer></v-spacer>
				<v-btn class="btn-cancel cancel-policy-popup" style="width:auto !important;"
				color="#757575"
					text
					@click="closeWithoutSavingSecondaryContact()"
				>Close without saving
				</v-btn>
				<button type="button" class="btn-keep-editing v-btn v-btn--text theme--light v-size--default" @click="keepEdingSecondaryContact()" style="color: rgb(246, 85, 89); caret-color: rgb(246, 85, 89);"><span class="v-btn__content">
					Keep editing
				</span></button>
				</v-card-actions>
			</v-card>
	</v-dialog>

    </v-card>
    <v-dialog
      v-model="canceldialog"
      persistent
      max-width="600"
	  max-height="184"
    >
        <v-card style="height:184px;">
            <div class="v-card__title text-h6 delete-policy-heading">
            Cancel
            </div>

            <div class="v-card__text delete-policy-text">There are unsaved changes. If you would like to save changes, press the ‘Keep Editing’ button.</div>
            <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn class="btn-cancel cancel-policy-popup"
            color="#757575"
                text
                @click="closeWithoutSave();"
                ref="foo"
            >Close without saving
            </v-btn>
            <button type="button" class="btn-keep-editing v-btn v-btn--text theme--light v-size--default" @click="canceldialog=false" style="color: rgb(246, 85, 89); caret-color: rgb(246, 85, 89);"><span class="v-btn__content">
                Keep editing
            </span></button>
            </v-card-actions>
        </v-card>
	</v-dialog>

    <v-dialog  v-model="deleteContactDialog" persistent max-width="600" max-height="184">
        <v-card style="height:184px;">
            <v-card-title class="text-h6 delete-policy-heading" style='padding: 16px !important;'>
            Delete Contact
            </v-card-title>
            <v-card-text class="delete-policy-text" style='padding: 0px 16px 16px !important;'>Are you sure you want to delete this contact? This action can not be undone.</v-card-text>
        <v-card-actions>
        <v-spacer></v-spacer>
            <v-btn class="btn-remove btn-secondary-remove-link"
                color="#757575"
                text
                @click="deleteContactDialog = false"
            >
                Cancel
            </v-btn>
            <v-btn v-if = "confirmDeleteBtnShow" class="btn-delete"
                color="#F65559"
                text
                @click="deleteContact"
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
        <v-dialog
                v-if="secondaryPrimaryModal"
                v-model="secondaryPrimaryModal"
                persistent
                max-width="600"
                max-height="184"
        >
            <v-card :style="{height:primarySecondaryContactLabel!=1?'233px;':'184px;'}">
                <div v-if="switchContactLevelFlage['insuredPolicyCheck'] != false && switchContactLevelFlage['linkedContactChecked'] != false && switchContactLevelFlage['secondaryContactChecked'] != false" class="v-card__title text-h6 delete-policy-heading secodarya_lavel px-16px pt-16px">
                    Confirm Switch to {{primarySecondaryContactLabel!=1?'Secondary Contact':'Primary Contact'}}
                </div>
                <div v-else class="v-card__title text-h6 delete-policy-heading px-16px pt-16px">
                    Action Cannot be Completed
                </div>

                <div v-if="primarySecondaryContactLabel!=1 && switchContactLevelFlage['insuredPolicyCheck'] != false && switchContactLevelFlage['linkedContactChecked'] != false && switchContactLevelFlage['secondaryContactChecked'] != false" class="v-card__text delete-policy-text secodarya_lavel px-16px">Are you sure you want to switch this person to a secondary contact?
                    <br>A secondary contact cannot be the primary insured on a policy or receive broadcasts from your agency.</div>
                <div v-else-if="switchContactLevelFlage['insuredPolicyCheck'] == false" class="v-card__text delete-policy-text px-16px">
                    You cannot change this account to a secondary contact if they are the primary insured on an opportunity or policy.
                </div>
                <div v-else-if="switchContactLevelFlage['insuredPolicyCheck'] != false && switchContactLevelFlage['secondaryContactChecked'] == false" class="v-card__text delete-policy-text secodarya_lavel px-16px">
                    You cannot change this account to a secondary contact if they have secondary contacts.
                </div>
                <div v-else-if="switchContactLevelFlage['linkedContactChecked'] == false && switchContactLevelFlage['insuredPolicyCheck'] != false" class="v-card__text delete-policy-text secodarya_lavel px-16px">
                    You cannot change this account to a secondary contact if they have no primary contacts.
                </div>
                <div v-else class="v-card__text delete-policy-text secodarya_lavel px-16px">Are you sure you want to switch this person to a primary contact?</div>
                <v-card-actions class="pb-16px px-16px">
                    <v-spacer></v-spacer>
                    <v-btn v-if="switchContactLevelFlage['insuredPolicyCheck'] != false && switchContactLevelFlage['linkedContactChecked'] != false && switchContactLevelFlage['secondaryContactChecked'] != false" class="btn-cancel cancel-policy-popup" style="width:auto !important;"
                           color="#757575"
                           text
                           @click="secondaryPrimaryModal = false, dropdownOpenPrimary = false, dropdownOpenSecondary = false, dropdownOpen = false"
                    >
                        CANCEL
                    </v-btn>
                    <v-btn v-else color="teal" text @click="secondaryPrimaryModal = false, dropdownOpenPrimary = false, dropdownOpenSecondary = false, dropdownOpen = false" class="cancel-edit-contact-details bg-transperent" >
                        Close
                    </v-btn>
                    <button type="button" v-if="switchContactLevelFlage['insuredPolicyCheck'] != false && switchContactLevelFlage['linkedContactChecked'] != false && switchContactLevelFlage['secondaryContactChecked'] != false" class="switch_to_contact_lavel_btn v-btn v-btn--text theme--light v-size--default" @click="switchToContactLavel()" style="color: rgb(246, 85, 89); caret-color: rgb(246, 85, 89);"><span class="v-btn__content">
					yes, switch to {{primarySecondaryContactLabel!=1?'secondary':'primary'}}
				</span></button>
                </v-card-actions>
            </v-card>
        </v-dialog>

</div>

</script>

<script>
    Vue.component('<?=ComponentTools::componentName(__FILE__)?>', {
        template: '#<?=ComponentTools::templateName(__FILE__)?>',
        props: ['contactId'],
        data: function(){
            return {
                nameRules: [
                    v => !!v || '',
                    v => (v || '').indexOf(' ') < 0 || 'No spaces are allowed'
                ],
                base_url:base_url,
                dialog:false,
                menu2: false,
				disabled:1,
                date: (new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0, 10),
                secondaryContacts:[],
                linkedCommercialContacts:[],
                personaldialog:false,
                commercialdialog:false,
                Contact: {
                    first_name: '',
                    middle_name: '',
                    suffix: '',
                    preferred_name:'',
                    last_name: '',
                },
                contactData:[],
                smsOptInOut:[],
                MailingAddress:[],
                optInData:[],
                summaryFields: [
                    {
                        label: "Linked Primary",
                        object: "Virtual_ContactSummary",
                        fields: "family_contact_ids",
                        type: "contact-links"
                    },
                    {
                        label: "Linked Secondary",
                        hideIfEmpty:true,
                        object: "Virtual_ContactSummary",
                        fields: "secondary_contact_id",
                        type: "contact-links"
                    },
                    {
                        label: "Linked Businesses",
                        object: "Virtual_ContactSummary",
                        fields: "commercial_contact_ids",
                        type: "contact-links"
                    },
                    {
                        label: "Relationship",
                        object: "Virtual_ContactSummary",
                        fields: "relationship_id",
                        type: "contact-links"
                    },
                    {
                        label: "Status",
                        object: "Virtual_ContactSummary",
                        fields: "status_id",
                        type: "contact-links"
                    },
                ],
                contactInfo: [
                    {
                        label: "Client Since",
                        object: "Contact",
                        fields: "client_since",
                        type: "date-y-mm-dd"
                    },
                    {
                        label: "Total Premium",
                        object: "Virtual_ContactSummary",
                        fields: "total_active_premium",
                        type: "text"
                    },
                    {
                        label: "Do Not Contact?",
                        object: "Contact",
                        fields: "do_not_contact",
                        hideIfEmpty: true,
                        class:"dnc_btn",
                        enumMap: {
                            1: 'DNC',
                        },
                        type: "enum"
                    },
                    {
                        label: "Address",
                        object: "Contact",
                        fields: "address,address_line_2,city,state_id,county_id,zip",
                        addressMap: {
                            address1: 'address',
                            address2: 'address_line_2',
                            city: 'city',
                            stateId: 'state_id',
                            countyId: 'county_id',
                            zip: 'zip'
                        },
                        type: "address"
                    },
                    {
                        label: "County",
                        object: "Virtual_ContactSummary",
                        fields: "",
                        type: "text"
                    },
                    {

                        label: "Mailing Address",
                        object: "ContactsMailingAddress",
                        fields: "mailing_address_1,mailing_address_2,mailing_city,mailing_state_id,county_id,mailing_zip",
                        addressMap: {
                            address1: 'mailing_address_1',
                            address2: 'mailing_address_2',
                            city: 'mailing_city',
                            stateId: 'mailing_state_id',
                            countyId: 'mailing_county_id',
                            zip: 'mailing_zip'
                        },
                        type: "address"
                    },
                    {
                        label: "Dif. Mailing Address?",
                        object: "Contact",
                        fields: "mailing_address_type",
                        hideIfEmpty: true,
                        enumMap: {
                            1: 'Same',
                            2: '--'
                        },
                        type: "enum"
                    },
                    {
                        label: "Phone",
                        object: "Contact",
                        fields: "phone,phone_number_type",
                        phoneMap: {
                            number: 'phone',
                            type: 'phone_number_type',
                            optedInField: 'phone_number_type',
                        },
                        type: "phone"
                    },
                    {
                        label: "Best Time to Contact",
                        object: "Contact",
                        fields: "best_time_to_reach",
                        hideIfEmpty: true,
                        enumMap: {
                            1: 'Morning',
                            2: 'Afternoon',
                            3: 'Evening',
                            4: 'Anytime'
                        },
                        type: "enum"
                    },

                    {
                        label: "Email",
                        object: "Contact",
                        fields: "email,email_type",
                        emailMap: {
                            email: 'email',
                            type: 'email_type',
                            optedInField: 'email_type',
                        },
                        type: "email"
                    },

                ],

                personalDetails: [

                  {
                      label: "Assigned owner",
                      object: "Contact",
                      fields: "user_id",
                      type: "user"
                  },
                  {
                      label: "Creation Date",
                      object: "Contact",
                      fields: "created",
                      type: "date-y-mm-dd"
                  },
                  {
                      label: "Date of Birth",
                      object: "Contact",
                      fields: "birth_date",
                      type: "date-hm-hd-y"
                  },
                  {
                      label: "Social Security Number",
                      object: "ContactDetails",
                      fields: "social_security_number",
                      type: "security-number"
                  },
                  {
                      label: "Driver License Number",
                      object: "ContactDetails",
                      fields: "driver_license_number",
                      type: "text"
                  },
                  {
                      label: "Driver License State",
                      object: "ContactDetails",
                      fields: "license_state_id",
                      type: "state"
                  },
                  {
                      label: "Lead Source",
                      object: "Contact",
                      fields: "lead_source_type",
                      type: "leadsource"
                  },
                  {
                      label: "Marital Status",
                      object: "Contact",
                      fields: "marital_status",
                      enumMap: {
                          1: 'Single',
                          2: 'Engaged',
                          3: 'Married',
                          4: 'Divorced',
                          5: 'Widowed',
                          6: 'DomesticPartnerUnmarried',
                          7: 'Separated',
                          8: 'Fiance',
                          9: 'DivorcedWithChildren',
                          10: 'SeparatedWithChildren',
                          11: 'CivilUnion',
                          12: 'Unknown'
                      },
                      type: "enum"
                  },
                  {
                      label: "Occupation",
                      object: "Contact",
                      fields: "occupation",
                      type: "text"
                  },
                  {
                      label: "Own or Rent?",
                      object: "Contact",
                      fields: "owns_rent",
                      enumMap: {1: 'Rent', 2: 'Own Home', 3: 'Owns Condo/Townhome'},
                      type: "enum"
                  },
                  {
                      label: "X-Date",
                      object: "Contact",
                      fields: "expiration_date",
                      type: "date-hm-hd-y"
                  },
                  {
                      label: "Referral Partner",
                      object: "ReferralPartnerUserContact",
                      fields: "referral_partner_name",
                      hideIfEmpty: true,
                      type: "text",
                      
                  },
                  {
                      label: "X-Date Campaign",
                      object: "Contact",
                      fields: "expiration_date",
                      hideIfEmpty: true,
                      type: "xdate",
                      
                  },
                  

              ],
              	secondaryContact:'',
				relationwithcontact:'',
				secondarystatus:'',
				secondaryContactId:'',
                commercialId:'',
			  	secondaryContactSearch:[],
			  	relationshipStatus: [
                    { name: 'Spouse', value: _RELATION_TYPE_SPOUSE },
                    { name: 'Child', value: _RELATION_TYPE_CHILD },
					{ name: 'Relative', value: _RELATION_TYPE_RELATIVE },
                    { name: 'Friend', value: _RELATION_TYPE_FRIEND },
					{ name: 'Other', value: _RELATION_TYPE_OTHER },
                ],
				secondaryContactStatus: [
                    { name: 'Living In Home', value: _SECONDARY_CONTACT_STATUS_LIVING_IN_HOME },
                    { name: 'Away From Home', value: _SECONDARY_CONTACT_STATUS_AWAY_FROM_HOME },
					{ name: 'Deceased', value: _SECONDARY_CONTACT_STATUS_DECEASED },
                    { name: 'Other', value: _SECONDARY_CONTACT_STATUS_OTHER },
                ],
                commercialContacts:'',
                businessStructures:[],
                businessRole:'',
				errorFound:0,
				error_message:'',
                businessRollTypes: [
                    { name: 'Spouse', value: _RELATION_TYPE_BUSINESS_SPOUSE },
                    { name: 'Billing', value: _RELATION_TYPE_BUSINESS_BILLING },
					{ name: 'Partner', value: _RELATION_TYPE_BUSINESS_PARTNER },
                    { name: 'Owner', value: _RELATION_TYPE_BUSINESS_OWNER },
					{ name: 'Other', value: _RELATION_TYPE_BUSINESS_OTHER }
                ],
                agencyId:'',
                secondary_contact_arr:[],
                searchText: '',
                commercial_contact_arr:[],
                searchCommercial: '',
                addSecondaryContactDialog:false,
                secondaryPhone:'',
                phoneType: [
                    { name: 'Home', value: _ID_HOME },
                    { name: 'Office', value: _ID_OFFICE },
                    { name: 'Cell', value: _ID_CELL },
					{ name: 'Landline', value: _ID_LANDLINE },
					{ name: 'Other', value: _ID_OTHER },
                ],
                secondaryEmailType:'',
                bestTimeToReach: [
                    { name: 'Morning', value: _MORNING },
                    { name: 'Afternoon ', value: _AFTERNOON },
                    { name: 'Evening ', value: _EVENING },
					{ name: 'Anytime ', value: _ANYTIME },
                ],
                secondaryBestTimeToReach:'',
                secondaryPhoneNumberType:'',
                emailType: [
                    { name: 'Personal', value: _EMAIL_TYPE_PERSONAL },
                    { name: 'Work', value: _EMAIL_TYPE_WORK },
                    { name: 'Other', value: _EMAIL_OTHER },
                ],
                secondaryEmail:'',
                secondaryDoNotContact:true,
                secondaryFirstName:'',
                secondaryMiddleName:'',
                secondaryLastName:'',
                secondarySuffix:'',
                secondaryPreferredName:'',
                secondaryRelationship:'',
                secondaryStatusType:'',
                secondaryAddress:'',
                secondaryAddressLineTwo:'',
                secondaryCity:'',
                secondaryStateId:'',
                secondaryZip:'',
                sameAsPrimaryAddress:true,
                states:[],
                secondaryDisabled:false,
                date2:'',
                today:(new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0, 10),
                menu3: false,
                isPrimaryAddressSame:false,
                relationshipType:[
                    {name: 'Spouse', value: 1 },
                    { name: 'Child', value: 2 },
                    { name: 'Friend', value: 3 },
                    { name: 'Other', value: 4 },
                ],
                secondaryStatusTypes:[
                    {name: 'Living In Home', value: 1 },
                    { name: 'Away From Home', value: 2 },
                    { name: 'Deceased', value: 3 },
                    { name: 'Other', value: 4 },
                ],
                snackbar:false,
                secondaryContactSavedStatus:false,
                secondaryContactSavedText:'Contact Added Successfully',
                timeout: 3000,
                emailExistText:'',
                phoneNumberValidationText: '',
                unsavedDialogSecondaryContact:false,
                secondaryContactFieldsChanged:false,
                showAddressLine2:false,
                canceldialog: false,
                mainDetailChanged: false,
                addSecondaryContactDialog:false,
                secondaryPhone:'',
                phoneType: [
                    { name: 'Home', value: _ID_HOME },
                    { name: 'Office', value: _ID_OFFICE },
                    { name: 'Cell', value: _ID_CELL },
					{ name: 'Landline', value: _ID_LANDLINE },
					{ name: 'Other', value: _ID_OTHER },
                ],
                secondaryEmailType:'',
                bestTimeToReach: [
                    { name: 'Morning', value: _MORNING },
                    { name: 'Afternoon ', value: _AFTERNOON },
                    { name: 'Evening ', value: _EVENING },
					{ name: 'Anytime ', value: _ANYTIME },
                ],
                secondaryBestTimeToReach:'',
                secondaryPhoneNumberType:'',
                emailType: [
                    { name: 'Personal', value: _EMAIL_TYPE_PERSONAL },
                    { name: 'Work', value: _EMAIL_TYPE_WORK },
                    { name: 'Other', value: _EMAIL_OTHER },
                ],
                secondaryEmail:'',
                secondaryDoNotContact:true,
                secondaryFirstName:'',
                secondaryMiddleName:'',
                secondaryLastName:'',
                secondarySuffix:'',
                secondaryPreferredName:'',
                secondaryRelationship:'',
                secondaryStatusType:'',
                secondaryAddress:'',
                secondaryAddressLineTwo:'',
                secondaryCity:'',
                secondaryStateId:'',
                secondaryZip:'',
                sameAsPrimaryAddress:true,
                states:[],
                secondaryDisabled:false,
                date2:'',
                today:(new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0, 10),
                menu3: false,
                isPrimaryAddressSame:false,
                relationshipType:[
                    {name: 'Spouse', value: 1 },
                    { name: 'Child', value: 2 },
                    { name: 'Friend', value: 3 },
                    { name: 'Other', value: 4 },
                ],
                secondaryStatusTypes:[
                    {name: 'Living In Home', value: 1 },
                    { name: 'Away From Home', value: 2 },
                    { name: 'Deceased', value: 3 },
                    { name: 'Other', value: 4 },
                ],
                snackbar:false,
                secondaryContactSavedStatus:false,
                secondaryContactSavedText:'Contact Added Successfully',
                timeout: 3000,
                emailExistText:'',
                phoneNumberValidationText: '',
                unsavedDialogSecondaryContact:false,
                secondaryContactFieldsChanged:false,
                showAddressLine2:false,
                mailingAddressType:1,
                mainDisabled:false,
                additionalInsuredContactId:'',
                deleteSecondaryContactDialog: false,
                successMessage:'',
                additionalInsuredId:'',
                renderComponent:true,
                renderContactDetailsComponent:true,
                renderPersonalDeatilsComponent:true,
                deleteContactDialog:false,
                confirmDeleteBtnShow:true,
                isEmailSubscribed:false,
                isSmsSubscribed:false,
                clientType:'',
                clientStatusChange:'',
                secondary_contact_flag : '',
                relationWithPrimary : '',
                statusWithPrimary : '',
                secondaryContactTitle : 'Manage Linked Accounts',
                relationWithStatus : '',
                primaryContactName : [],
                linkedPrimaryContactId : '',
                secondaryCheck : 0,
                action_sso_url : 'https://identity.nowcerts.com/Account/ExternalLogin',
                dropdownOpenPrimary:false,
                dropdownOpenSecondary:false,
                dropdownOpen:false,
                secondaryPrimaryModal:false,
                primarySecondaryContactLabel:'',
                switchContactLevelFlage:[],
                action_url: '',
                ssoCode: '',
                contactAdditionalEmail: [],
                contactAddtitionalPhone:[]
            }
        },

        computed: {
            birthDateFormatted :function () {
                if(this.date2 == '1970-01-01' || this.date2 == '' || this.date2 == 'null'){
                    //this.date2 = new Date().toISOString().substr(0, 7);//(new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0, 10);
					this.date2 = '';
					return this.date2;
                }else{
                    const birthdate = new Date(this.date2);
                    return `${birthdate.toLocaleDateString("en-US", {
                    "day": "numeric",
                    "year": "numeric",
                    "month": "long",
                    "timeZone": "UTC"
                    })}`;
                }

			},
            computedSSOAction(){
                return this.action_sso_url;
            },
        },
        methods: {
            openInNowcerts:function()
            {
                var vm = this;
				var postData = {
					'contact_id' : this.contactId,
                    'insured_database_id' : vm.contactData.nowcert_database_id
				}
				DataBridge.save('Nowcerts.nowcertsExternalLogin',postData,this.nowcertExternalFormSubmit);
            },
            nowcertExternalFormSubmit:function(response)
            {
                this.action_url = '';
                const result = JSON.parse(response.data.data);
                this.action_url = result.url;  // Update action URL
                this.ssoCode = result.code;    // Update the SSO code field
                setTimeout(() => {
                    this.$refs.externalLoginForm.submit();
                }, 500);
            },
            personalModal:function()
            {
                this.searchText = '';
                this.personaldialog=true;
                var secondaryData = {
					'name': this.searchText,
					'id':this.contactId,
                    'type': 1, // For Personal Prospect
				}
                DataBridge.save('Contacts.getSecodaryContact',secondaryData,this.populateSecondryContactData);
            },

            populateBusinessStrutureData:function(response)
            {
               
                this.commercial_contact_arr = (response['data']['data']['Contacts.getSecodaryContact'][this.contactId]);
               
            },
            populateSecondryContactData:function(response){
                this.secondaryContact = '';
                if(response['data']['data'])
                {
                    this.secondary_contact_arr =  (response['data']['data']['Contacts.getSecodaryContact'][this.contactId]);
                }
                else
                {
                    this.secondary_contact_arr = (response['Contacts.getSecodaryContact'][this.contactId]);
                }
			},
            enableSaveLinkBtn: function(){
                if(this.secondaryContact !=''){
                    this.disabled = 0;
                    this.errorFound = 0;
					this.secondaryContactId = this.secondaryContact;
                }
            },
            enablecommercialSaveLinkBtn: function(){
                if(this.commercialContacts !=''){
                  this.disabled = 0;
				  this.errorFound = 0;
                  this.commercialId = this.commercialContacts
                }
            },
            commercialModal:function()
            {
                 var secondaryData = {
					'name': this.searchCommercial,
					'id':this.contactId,
                    'type': 2, // For Commercial Prospect
				}
                DataBridge.save('Contacts.getSecodaryContact',secondaryData,this.populateBusinessStrutureData);
                this.commercialdialog = true;
            },
			dateFormat: function (date1)
			{
               // var date1 =  this.contactInfo.created;
				var first_date = new Date(date1),
				month = '' + (first_date.getMonth() + 1),
				day = '' + first_date.getDate(),
				year = first_date.getFullYear();

				if (month.length < 2) month = '0' + month;
				if (day.length < 2) day = '0' + day;

				var firstDate =  [year, month, day].join('-');
				return firstDate
			},
            populateContactData: function(data)
            {
                var vm = this;
                if(data.data){
                    vm.contactData = data.data.data.Contact[this.contactId];
                }else{
                    vm.contactData = data.Contact[this.contactId];
                }
                if(vm.contactData.is_subscribe != 1){
                    vm.isEmailSubscribed = true
                }
                if(vm.contactData.is_sms_subscribe != 1){
                    vm.isSmsSubscribed = true
                }
                DataBridgeContacts.save('Contacts.getContactPhoneNumbersOptInOutStatus', this.contactId, this.contactPrimaryPhoneOptInStatus);
                this.agencyId = vm.contactData['agency_id'];
                 vm.mailingAddressType = vm.contactData.mailing_address_type;
                Object.entries(vm.contactData).forEach((element, index) => this.Contact[element[0]] = element[1]);
                var Fname ="";
                var Lname = "";
                var Pname = "";
                if(this.Contact.first_name !=='' && this.Contact.first_name !== null)
                {
                    Fname = this.Contact.first_name.charAt(0).toUpperCase() + this.Contact.first_name.slice(1);
                    // this.Contact.first_name = Fname;
                }
                if(this.Contact.last_name != null && this.Contact.last_name != '')
                {
                    Lname = this.Contact.last_name.charAt(0).toUpperCase() + this.Contact.last_name.slice(1);
                    // this.Contact.last_name = Lname;
                }
                //Preferred Name Show in Web title
                if(this.Contact.preferred_name!== null  && this.Contact.preferred_name.trim()  !=='')
                {
                    Pname = this.Contact.preferred_name.charAt(0).toUpperCase() + this.Contact.preferred_name.slice(1);
                    document.title = Pname + " " + Lname;
                }else{
                    document.title = Fname + " " + Lname;
                }
                //prospect client ,inactive and secondary contact title
                if(vm.contactData.additional_insured_flag == 1)
                {
                    this.summaryFields[1].hideIfEmpty = true;
                    this.clientType = 'SECONDARY CONTACT';
                    this.clientStatusChange = 'secondary-chip';
                    this.secondary_contact_flag = vm.contactData.additional_insured_flag;
                   // DataBridge.get('Contacts.getSecondaryContactDetail', this.contactId, '*', this.populateSecondaryContactDetail);
                    var vm = this;
                    DataBridgeContacts.save('Contacts.getSecondaryContactDetail', this.contactId, function(data){vm.populateSecondaryContactDetail(data)});
                    this.secondaryContactTitle = 'Primary Account';
                    this.primarySecondaryContactLabel = 1;
                }
                else
                {   
                    if(vm.contactData.status == 1)
                    { 
                        if(vm.contactData.lead_type == 1)
                        {
                            this.clientType = 'PROSPECT';
                            this.clientStatusChange = 'prospect-chip';
                        } else if(vm.contactData.lead_type == 2)
                        {
                            this.clientType = 'CLIENT';
                            this.clientStatusChange = 'client-chip';
                        }
                    }
                    else if(vm.contactData.status == 2)
                    {
                        this.clientType = 'INACTIVE';
                        this.clientStatusChange = 'inactive-chip';
                    }
                    this.primarySecondaryContactLabel = 0;
                    this.secondary_contact_flag = '';
                    this.summaryFields[1].hideIfEmpty = false;
                    //DataBridge.get('Contacts.getSecondaryContactDetail', this.contactId, '*', this.populateSecondaryContactDetail);
                    var vm = this;
                    DataBridgeContacts.save('Contacts.getSecondaryContactDetail', this.contactId, function(data){vm.populateSecondaryContactDetail(data)});
                }
            },           
            populateSecondaryContactDetail:function(data)
            {               
                let  result = '';
                if(data['data'])
                {
                    result = (data['data']['data']['Contacts.getSecondaryContactDetail'][this.contactId])
                }
                else
                {
                    result = (data['Contacts.getSecondaryContactDetail'][this.contactId]);
                }
                this.primaryContactName = result;
               if(result)
               {
                    this.secondaryCheck = 1;
                    this.relationWithPrimary = result.relationship_with_contact;
                    this.statusWithPrimary = result.insured_contact_status;
                    if(result.Contacts)
                    {
                        this.linkedPrimaryContactId = result.Contacts.id;
                    }
                    this.relationWithStatus = '';
                    if(this.relationWithPrimary == _RELATION_TYPE_SPOUSE)
                    {
                        this.relationWithStatus = 'Spouse';
                    }
                    else if(this.relationWithPrimary == _RELATION_TYPE_CHILD)
                    {
                        this.relationWithStatus = 'Child';
                    }
                    else if(this.relationWithPrimary == _RELATION_TYPE_RELATIVE)
                    {
                        this.relationWithStatus = 'Relative';
                    }
                    else if(this.relationWithPrimary == _RELATION_TYPE_FRIEND)
                    {
                        this.relationWithStatus = 'Friend';
                    }
                    else if(this.relationWithPrimary == _RELATION_TYPE_OTHER)
                    {
                        this.relationWithStatus = 'Other';
                    }
                    if(this.relationWithStatus != '' && this.statusWithPrimary)
                    {
                        this.relationWithStatus = this.relationWithStatus + '/';
                    }

                    if(this.statusWithPrimary == _SECONDARY_CONTACT_STATUS_LIVING_IN_HOME)
                    {
                        this.relationWithStatus = this.relationWithStatus +' Living in home';
                    }
                    else if(this.statusWithPrimary == _SECONDARY_CONTACT_STATUS_AWAY_FROM_HOME)
                    {
                        this.relationWithStatus = this.relationWithStatus +' Away from home';
                    }
                    else if(this.statusWithPrimary == _SECONDARY_CONTACT_STATUS_DECEASED)
                    {
                        this.relationWithStatus = this.relationWithStatus +' Deceased';
                    }
                    else if(this.statusWithPrimary == _SECONDARY_CONTACT_STATUS_OTHER)
                    {
                        this.relationWithStatus = this.relationWithStatus +' Other';
                    }
               }
               else
                {
                    this.secondaryCheck = 0;
                }

            },
            populateMaillingAddressData:function(data){
                var vm = this;
                if(data.data){
                    vm.MailingAddress = data.data.data.ContactsMailingAddress[this.contactId];
                }else{
                    vm.MailingAddress = data.ContactsMailingAddress[this.contactId];
                }
               // this.forceRerender();

            },

            populateSecondaryContactData:function(data)
            {
                var vm = this;
                if(data['data']){
                    vm.secondaryContacts = (data['data']['data']['Contacts.getSecondaryContacts'][this.contactId]);
                }else{
                    vm.secondaryContacts = (data['Contacts.getSecondaryContacts'][this.contactId]);
                }
                this.forceRerender();
            },


            populateCommercialLinkedContactData:function(data)
            {

                var vm = this;
                if(data['data']){
                    vm.linkedCommercialContacts = (data['data']['data']['Contacts.getCommercialLinkContacts'][this.contactId]);
                }else{
                    vm.linkedCommercialContacts = (data['Contacts.getCommercialLinkContacts'][this.contactId]);
                }
                //console.log("secondaryContact"+JSON.stringify(data));
            },



            showMainContact()
			{
                var vm = this;
				if(vm.contactData.client_since !='')
                {
                    this.date4 = this.dateFormat(vm.contactData.client_since);
                }
                if(vm.secondary_contact_flag == 1)
                {
                    DataBridgeContacts.save('Contacts.getSecondaryContactDetail', this.contactId, function(data){vm.populateSecondaryContactDetail(data)});
                    this.secondaryContactTitle = 'Primary Account';

                }
                else
                {
                    DataBridgeContacts.save('Contacts.getSecondaryContacts', this.contactId, function(data){vm.populateSecondaryContactData(data)});
                    DataBridgeContacts.save('Contacts.getCommercialLinkContacts', this.contactId,  function(data){vm.populateCommercialLinkedContactData(data)});
                }
                DataBridgeContacts.save('Contacts.getActivePoliciesAndSecondaryContact', this.contactId, function(data){vm.setToChangeContactLevel(data)});
                this.dialog=true;
               
            },


			searchSecondaryContact:function(response){
				var result =  JSON.parse(response['data']['data']);
				if(result.status==1){
					this.disabled=0;
                    this.errorFound=0;
					this.secondaryContactId = result.secondary_contact_id
				}else{
					this.disabled=1;
                    this.errorFound=1;
					this.error_message="Record not found."
				}
				//console.log("secondaryContactsss"+result.status);


			},

            getSecondaryContact:function()
            {
                if(this.secondaryContact){
                    this.disabled = 1;
                }
				var secondaryData = {
					'name':this.searchText,
					'id':this.contactId,
                    'type': 1, // For Personal Prospect
				}

				DataBridge.save('Contacts.getSecodaryContact',secondaryData,this.populateSecondryContactData);

            },

			secondaryContactSave:function(response){
				//console.log("secondaryContact"+JSON.stringify(data));
				var result =  JSON.parse(response['data']['data']);

				if(result['status']==1){
                    this.showMainContact();
                    swal({
							title: "Saved!",
							text:'Record saved successfully.',
							type: 'success',
							showConfirmButton: false,
                            timer: 1000
							}),(function(value) {
                                
						});
                    this.cancelPersonalDialog();
                    this.personaldialog=false;
                    this.loadComponentData();
                    this.forceRerender();

					//location.reload();
				}else{
                    this.errorFound=1;
					this.error_message = result['message'];
                }

			},
            cancelPersonalDialog:function(){
                this.secondaryContact = '';
                this.relationwithcontact = '';
                this.secondarystatus = '';
                this.secondaryContactId= '';
                this.personaldialog = '';
                return true;
            },
			saveSecondaryContact:function()
			{
                
				var secondarySaveData = {
					'id':this.contactId,
					'name':this.secondaryContact,
					'relationwithcontact':this.relationwithcontact,
					'secondarystatus':this.secondarystatus,
					'secondaryContactId':this.secondaryContactId
				}
				//console.log("secondary",secondarySaveData);
				DataBridgeContacts.save('Contacts.saveSecodaryContact',secondarySaveData,this.secondaryContactSave);
			},
            searchCommercialContact:function(response)
            {
                var result =  JSON.parse(response['data']['data']);
                if(result.status==1){
                    this.disabled=0;
					this.errorFound=0;
                    this.commercialId = result.business_id
                }else{
					this.errorFound=1;
					this.error_message="Record not found."
                    this.disabled=1;
                }
            },

            getCommercialContact:function()
            {

				var commercialData = {
                    'id':this.contactId,
					'name':this.searchCommercial,
                    'type': 2, // For Commercial Prospect
				}

               // console.log("commercial",commercialData);

				DataBridge.save('Contacts.getSecodaryContact',commercialData,this.populateBusinessStrutureData);

            },

            commercialContactSave:function(response){
                var result =  JSON.parse(response['data']['data']);

				if(result['status']==1){
                    swal({
							title: "Saved!",
							text:'Record saved successfully.',
							type: 'success',
							showConfirmButton: false,
                            timer: 1000
							}),(function(value) {
                                
						});
                    this.cancelCommercialContact();
                   
					this.showMainContact();
                    this.commercialdialog = false; 
                    DataBridgeContacts.save('Contacts.getActivePoliciesAndSecondaryContact', this.contactId, function(data){vm.setToChangeContactLevel(data)});
					//location.reload();
				}else{
					this.errorFound=1;
					this.error_message = result['message'];
				}
             
            },
            cancelCommercialContact:function()
            {
                this.commercialContacts = '';
                this.businessRole = '';
                this.commercialId = '';
                this.commercialdialog = false;
                return true;
            },

            saveCommercialContact:function()
			{
				var commercialSaveData = {
					'contact_id':this.contactId,
                    'business_id':this.commercialId,
                    'business_role_type':this.businessRole
				}

				DataBridgeContacts.save('Contacts.saveCommercialContact',commercialSaveData,this.commercialContactSave);
			},



			populatephoneNumbersOptInOutData:function(data)
			{
                var vm = this;
                if(data.data)
                {
                    vm.smsOptInOut = data.data.data.PhoneNumbersOptInOutStatus[this.contactId];
                }else{
                    vm.smsOptInOut = data.PhoneNumbersOptInOutStatus[this.contactId];
                }
              

			},

            mainContactSave:function(response)
            {
                var result =  JSON.parse(response['data']['data']);
                if(result['status']==1){
					//this.showMainContact();
                    swal({
							title: "Saved!",
							text:'Record saved successfully.',
							type: 'success',
							showConfirmButton: false,
                            timer: 1000
							}),(function(value) {
                                
					});
                    var Fname ="";
                    var Lname = "";
                    var Pname = "";
                    if(this.Contact.first_name !=='' && this.Contact.first_name !== null)
                    {
                        Fname = this.Contact.first_name.charAt(0).toUpperCase() + this.Contact.first_name.slice(1);
                        // this.Contact.first_name = Fname;
                    }
                    if(this.Contact.last_name != null && this.Contact.last_name != '')
                    {
                        Lname = this.Contact.last_name.charAt(0).toUpperCase() + this.Contact.last_name.slice(1);
                        // this.Contact.last_name = Lname;
                    }
                    //Preferred Name Show in Web title
                    if(this.Contact.preferred_name !=='' && this.Contact.preferred_name !== null)
                    {
                        Pname = this.Contact.preferred_name.charAt(0).toUpperCase() + this.Contact.preferred_name.slice(1);
                        document.title = Pname + " " + Lname;
                    }else{
                        document.title = Fname + " " + Lname;
                    }
                    if(this.clientSinceDateFormatted!==''){
                     this.contactData.client_since = this.clientSinceDateFormatted;
                    }
					//location.reload();
					this.forceRerender();
                    this.dialog = false;
				}
               // this.forceRerender();
            },
            cancelMainContactDialog:function(){
             
                this.cancelPersonalDialog();
                this.cancelCommercialContact();
                 DataBridge.get('Contact', this.contactId, '*', this.populateContactData);
                 
            },
            saveMainContact:function()
            {
                this.mainDisabled = false;
                if(this.Contact.first_name)
                {
                    this.Contact.first_name = this.Contact.first_name.trim();
                }
                if(this.Contact.middle_name)
                {
                    this.Contact.middle_name = this.Contact.middle_name.trim();
                }
                if(this.Contact.last_name)
                {
                    this.Contact.last_name = this.Contact.last_name.trim();
                }
                if(this.Contact.suffix)
                {
                    this.Contact.suffix = this.Contact.suffix.trim();
                }
                if(this.Contact.preferred_name)
                {
                    this.Contact.preferred_name = this.Contact.preferred_name.trim();
                }
                if(this.relationWithPrimary)
                {
                    this.relationWithPrimary = this.relationWithPrimary;
                }

                if(this.statusWithPrimary)
                {
                    this.statusWithPrimary = this.statusWithPrimary;
                }
                if(this.linkedPrimaryContactId)
                {
                    this.linkedPrimaryContactId = this.linkedPrimaryContactId;
                }
                var mainContactData = {
                    'id':this.contactId,
                    'first_name':this.Contact.first_name,
                    'middle_name':this.Contact.middle_name,
                    'last_name':this.Contact.last_name,
                    'suffix':this.Contact.suffix,
                    'preferred_name':this.Contact.preferred_name,
                    'insured_relation_id': this.relationWithPrimary,
                    'insured_status_id' : this.statusWithPrimary,
                    'primaryContactId' : this.linkedPrimaryContactId,
                }
                DataBridgeContacts.save('Contacts.saveMainContact',mainContactData,this.mainContactSave);
            },
            populateAdditionalEmails: function(data)
            {
                if(data['data']){
                    this.contactData['additional_emails'] = data['data']['data']['Contacts.getAdditionalEmails'][this.contactId];
                }else{
                    this.contactData['additional_emails'] = data['Contacts.getAdditionalEmails'][this.contactId];
                }
                this.contactAdditionalEmail = this.contactData['additional_emails'];
               
            },
            addSecondaryContact:function(){
                this.secondaryDisabled = false;
                if(this.secondaryFirstName)
                {
                    this.secondaryFirstName = this.secondaryFirstName.trim();
                }
                if(this.secondaryMiddleName)
                {
                    this.secondaryMiddleName = this.secondaryMiddleName.trim();
                }
                if(this.secondaryLastName)
                {
                    this.secondaryLastName = this.secondaryLastName.trim();
                }
                if(this.secondarySuffix)
                {
                    this.secondarySuffix = this.secondarySuffix.trim();
                }
                if(this.secondaryPreferredName)
                {
                    this.secondaryPreferredName = this.secondaryPreferredName.trim();
                }
                if(this.secondaryEmail)
                {
                    this.secondaryEmail = this.secondaryEmail.trim();
                }
                if(this.secondaryPhone)
                {
                    this.secondaryPhone = this.secondaryPhone.trim();
                }
                if(this.secondaryAddress)
                {
                    this.secondaryAddress = this.secondaryAddress.trim();
                }
                if(this.secondaryCity)
                {
                    this.secondaryCity = this.secondaryCity.trim();
                }
                if(this.secondaryZip)
                {
                    this.secondaryZip = this.secondaryZip.trim();
                }
                if(this.secondaryAddressLineTwo)
                {
                    this.secondaryAddressLineTwo = this.secondaryAddressLineTwo.trim();
                }
                let secondaryAddData = {
					'primary_contact_id':this.contactId,
					'secondary_contact_first_name':this.secondaryFirstName,
					'secondary_contact_middle_name':this.secondaryMiddleName,
					'secondary_contact_last_name':this.secondaryLastName,
					'secondary_contact_suffix':this.secondarySuffix,
					'secondary_contact_preferred_name':this.secondaryPreferredName,
                    'secondary_contact_emails':this.secondaryEmail,
                    'secondary_email_type_select':this.secondaryEmailType,
                    'secondary_contact_phone':this.secondaryPhone,
                    'contact_type_select_id':this.secondaryPhoneNumberType,
                    'is_address_same':this.sameAsPrimaryAddress,
                    'secondary_contact_address_one':this.secondaryAddress,
                    'secondary_contact_address_two':this.secondaryAddressLineTwo,
                    'secondary_contact_city':this.secondaryCity,
                    'secondary_contact_state':this.secondaryStateId,
                    'secondary_contact_zip':this.secondaryZip,
                    'secondary_contact_relationship_select':this.secondaryRelationship,
                    'secondary_contact_status_select':this.secondaryStatusType,
                    'seccontact_text_birth':this.birthDateFormatted,
                    'secondary_do_not_contact':this.secondaryDoNotContact,
                    'contact_id_n':this.contactId,
                    'is_existing_contact':2,
                    'best_time_to_reach':this.secondaryBestTimeToReach
				}
				//console.log("secondary",secondarySaveData);
				DataBridge.save('Contacts.saveNewSecondaryContact',secondaryAddData,this.savedSecondaryContact);
            },
            savedSecondaryContact:function(response){
                var vm = this;
                var result =  JSON.parse(response['data']['data']);
                if(result['response'] == 200) {
                    this.snackbar = true;
                    this.secondaryContactSavedStatus = true;
					this.addSecondaryContactDialog = false;
                    this.successMessage = '';
                    this.personaldialog = false;
                    DataBridge.save('Contacts.getSecondaryContacts', this.contactId, function(data){vm.populateSecondaryContactData(data)});
				}
            },
            populateStateData: function(data)
            {
                var vm = this;
                if(data['data'])
                {
                    vm.states = (data['data']['data']['States.getStates'][this.contactId]);
                }else{
                    vm.states = (data['States.getStates'][this.contactId]);
                }
				
            },
            populateCountyData: function(data)
            {
                var vm = this;
                if(data['data'])
                {
                    vm.counties = (data['data']['data']['Counties.getAllCounties'][this.contactId]);
                }else{
                    vm.states = (data['Counties.getAllCounties'][this.contactId]);
                }

            },
            enableSecondarySaveBtn: function(){
                if(this.secondaryFirstName.trim() && this.secondaryLastName.trim())
                {
                    this.secondaryDisabled = true;
                }else{
                    this.secondaryDisabled = false;
                }
            },
            validateEmail: function() {
				const validationRegex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,24}))$/;
				if(this.contactId != '')
				{
					if(this.secondaryEmail.trim() != '')
					{
						if (this.secondaryEmail.match(validationRegex)) {
                            let emailObj = {
                                'secondary_contact_emails':this.secondaryEmail,
                                'primaryContactId':this.contactId,
                            };
                            DataBridge.save('Contacts.verifyemailForPrimary',emailObj,this.checkEmailIfExists);
						} else {
							this.emailExistText = 'E-mail must be valid!';
							this.secondaryDisabled = false;
						}
					}
					else
					{
                        this.emailExistText = '';
                        if(this.secondaryFirstName.trim() != '' && this.secondaryLastName.trim() != '')
                        {
						    this.secondaryDisabled = true;
                        }

					}
				}

			},
            checkEmailIfExists: function(response)
            {
                var result =  JSON.parse(response['data']['data']);
                if(result.response){
                    this.emailExistText = '';
                    if(this.secondaryFirstName.trim() != '' && this.secondaryLastName.trim() != '')
                    {
                        this.secondaryDisabled = true;
                    }
                }else{
                    this.emailExistText = 'Email already exists!';
					this.secondaryDisabled = false;
                }
            },
            validatePhoneNumber:function(event)
			{
				let inputValue = event.currentTarget.value;
				var result = inputValue.replace(/[- )(]/g,'');
				const validationRegex = /^\d{10}$/;
				if(result.trim() != '')
				{
					if (result.match(validationRegex)) {
						this.phoneNumberValidationText = '';
						if(this.secondaryFirstName.trim() != '' && this.secondaryLastName.trim() != '')
                        {
                            this.secondaryDisabled = true;
                        }
					} else {
						this.phoneNumberValidationText = 'Phone number not valid!';
						this.secondaryDisabled = false;
					}
				}
				else
				{
                    this.phoneNumberValidationText = '';
                    if(this.secondaryFirstName.trim() != '' && this.secondaryLastName.trim() != '')
                    {
                        this.secondaryDisabled = true;
                    }
				}
			},
            showMailingAddress:function(){

                if(!this.sameAsPrimaryAddress)
                {
                    this.showAddressLine2 =  true;
                    this.secondaryAddress = '';
                    this.secondaryAddressLineTwo = '';
                    this.secondaryCity = '';
                    this.secondaryStateId = '';
                    this.secondaryZip = '';
                }else{
                    let vm = this;
                    DataBridge.get('States.getStates', this.contactId, '*', function(data)
                    {
                            vs.populateStateData(data);
                            vs.populateCountyData(data);
                    });
                    this.secondaryAddress = vm.contactData.address;
                    if(vm.contactData.address_line_2){
                        this.showAddressLine2 =  true;
                    }else{
                        this.showAddressLine2 =  false;
                    }
                    this.secondaryAddressLineTwo  = vm.contactData.address_line_2;
                    this.secondaryCity = vm.contactData.city;
                    this.secondaryStateId = vm.contactData.state_id;
                    this.secondaryZip = vm.contactData.zip;
                    }
            },
            checkSecondaryContactFormChange: function(){
                this.secondaryContactFieldsChanged = true;
            },
            showCancelDailog:function(){
                if(this.secondaryContactFieldsChanged){
                    this.unsavedDialogSecondaryContact = true;
                }else{
                    this.personaldialog = false;
                    this.addSecondaryContactDialog = false;
                }
            },
            closeWithoutSavingSecondaryContact:function()
			{
                this.unsavedDialogSecondaryContact = false;
                this.personaldialog = false;
                this.addSecondaryContactDialog = false;

			},
			keepEdingSecondaryContact:function()
			{
				this.unsavedDialogSecondaryContact = false;
			},
            async forceRerender() {
                // Remove MyComponent from the DOM
                this.renderComponent = false;

                        // Wait for the change to get flushed to the DOM
                await this.$nextTick();

                // Add the component back in
                this.renderComponent = true;
            },
            async forceContactDetailsRerender() {
                // Remove MyComponent from the DOM
                this.renderContactDetailsComponent = false;

                        // Wait for the change to get flushed to the DOM
                await this.$nextTick();

                // Add the component back in
                this.renderContactDetailsComponent = true;
            },

            async forcePersonalDetailsRerender() {
                // Remove MyComponent from the DOM
                this.renderPersonalDeatilsComponent = false;

                        // Wait for the change to get flushed to the DOM
                await this.$nextTick();

                // Add the component back in
                this.renderPersonalDeatilsComponent = true;
            },
            
            
             closeDetailModal:function()
            {
                if(this.mainDetailChanged){
                    this.canceldialog = true;
                }
                else{
                    this.dialog = false;
                    this.$refs.scrollContainer.scrollTop = 0;
                }
            
			},
           
            closeWithoutSave: function()
            {
                this.$refs.scrollContainer.scrollTop = 0;
                this.mainDisabled = false,
                this.canceldialog = false;
                this.dialog = false;
                this.mainDetailChanged = false;
                this.cancelMainContactDialog();
                
            },
            showAddSecondaryContactDialog: function(){
                let vm = this;
				let vs = this;
				DataBridgeContacts.save('States.getStates', this.contactId, function(data)
				{
						vs.populateStateData(data);
						vs.populateCountyData(data);
				});
                this.date2 = '';
                this.secondaryEmail = '';
                this.isPrimaryAddressSame = false;
                this.secondaryEmail = '';
                this.secondaryDoNotContact = true;
                this.secondaryFirstName = '';
                this.secondaryMiddleName = '';
                this.secondaryLastName = '';
                this.secondarySuffix = '';
                this.secondaryPreferredName = '';
                this.secondaryRelationship = '';
                this.secondaryStatusType = '';
                this.secondaryBestTimeToReach = '';
                if(this.sameAsPrimaryAddress && vm.contactData){
                    this.secondaryAddress = vm.contactData.address;
                    this.secondaryAddressLineTwo  = vm.contactData.address_line_2;
                    this.secondaryCity = vm.contactData.city;
                    this.secondaryStateId = vm.contactData.state_id;
                    this.secondaryZip = vm.contactData.zip;
                }
                this.addSecondaryContactDialog = true;
            },
            populatePhoneNumber: function (data)
            {
                let  result = '';
                if(data['data'])
                {
                    result = (data['data']['data']['Contacts.getContactAdditionalNumber'][this.contactId])
                }else{
                    result = (data['Contacts.getContactAdditionalNumber'][this.contactId])
                }
                this.contactAddtitionalPhone = result;
            this.contactData['additionalPhone'] = result;
            },
            contactPrimaryPhoneOptInStatus: function(response)
            {
                let result ='';
                if(response['data']){
                    result = (response['data']['data']['Contacts.getContactPhoneNumbersOptInOutStatus'][this.contactId]);
                }else{
                    result = (response['Contacts.getContactPhoneNumbersOptInOutStatus'][this.contactId]);
                }
             
                if(result == 1)
                {
                    this.contactData['primary_opt_in_status'] = 1;
                }
                else
                {
                    this.contactData['primary_opt_in_status'] = 0;
                }
            },
            enableMainSaveBtn: function()
            {
                if(this.Contact.first_name.trim() && this.Contact.last_name.trim())
                {
                    this.mainDisabled = true;
                }else{
                    this.mainDisabled = false;
                }
            },
            deleteSecondary: function(id,additionalInsuredContactId)
			{
                this.additionalInsuredId = id;
				this.additionalInsuredContactId = additionalInsuredContactId;
				this.deleteSecondaryContactDialog = true;
			},
			deleteSecondaryContact : function()
			{
				var data = {
                    'id':this.additionalInsuredId,
                    'primaryContactId': this.contactId,
                    'additionalInsuredContactId': this.additionalInsuredContactId
                }
				DataBridge.save('Contacts.deleteSecondaryContact',data,this.confirmDeleteSecondaryContact);
			},
            confirmDeleteSecondaryContact(response)
			{   var vm = this;
				var result =  JSON.parse(response['data']['data']);
				if(result['status'] == true || result['status'] == '1' || result['status'] == 1)
				{
                    this.secondaryContactSavedStatus =false;
					this.deleteSecondaryContactDialog = false;
					this.successMessage = result['message'];
					this.snackbar=true;
                    DataBridgeContacts.save('Contacts.getSecondaryContacts', this.contactId, function(data){vm.populateSecondaryContactData(data)});
                    DataBridgeContacts.save('Contacts.getSecondaryContactDetail', this.contactId, function(data){vm.populateSecondaryContactDetail(data)});
                    DataBridgeContacts.save('Contacts.getActivePoliciesAndSecondaryContact', this.contactId, function(data){vm.setToChangeContactLevel(data)});
                    this.loadComponentData();
                    this.forceRerender();
				}

			},            
            renderContactDetailsleftSideBar(renderComponentValue){
                this.forceContactDetailsRerender();
                this.loadComponentData();
            },  
            renderleftSideBar(renderComponentValue){
                this.forceRerender();
                this.loadComponentData();

            },  
            renderPersonalDeatilsleftSideBar(renderComponentValue){
                this.forcePersonalDetailsRerender();
                this.loadComponentData();

            },
            populateSwitchToContactLavel: function (data)
            {
                result = JSON.parse(data['data']['data']);
                var vm = this;
                if(result.status == 1)
                {   
                    this.loadComponentData()
                    this.secondaryPrimaryModal = false;
                    this.dropdownOpen = false;
                }
            },
            loadComponentData(){

                var vm = this;
                let objectData = {
                    'objectId':this.contactId,
                    'objectName':'Contact',
                    'fields': '*'
                 }
                DataBridgeContacts.save('Contacts.getActivePoliciesAndSecondaryContact', this.contactId, function(data){vm.setToChangeContactLevel(data)});
                DataBridgeContacts.save('Contacts.getContactDetailsById', objectData, function(data){vm.populateContactData(data)});
                DataBridgeContacts.save('Contacts.getContactAdditionalNumber', this.contactId, vm.populatePhoneNumber);
 

                let objectPhoneNumbersOptInOutStatus = {
                    'objectId':this.contactId,
                    'objectName':'PhoneNumbersOptInOutStatus',
                    'fields': '*'
                }

                DataBridgeContacts.save('Contacts.getContactDetailsById', objectPhoneNumbersOptInOutStatus, function(data){
                    vm.populatephoneNumbersOptInOutData(data)
                });

                DataBridgeContacts.save('Contacts.getAdditionalEmails', this.contactId, function(data){
                        // console.log("optin"+JSON.stringify(data));
                    vm.populateAdditionalEmails(data)
                });

              //  DataBridge.get('Contacts.getContactPhoneNumbersOptInOutStatus', this.contactId,  this.contactPrimaryPhoneOptInStatus);
            

                let objectContactsMailingAddress = {
                    'objectId':this.contactId,
                    'objectName':'ContactsMailingAddress',
                    'fields': 'mailing_address_1,mailing_address_2,mailing_city,mailing_state_id,mailing_zip'
                }

                DataBridgeContacts.save('Contacts.getContactDetailsById', objectContactsMailingAddress, function(data){
                    vm.populateMaillingAddressData(data)

                });    
            },
            showDeleteContact()
            {
                this.deleteContactDialog = true;
            },
            deleteContact()
            {
                this.confirmDeleteBtnShow = false;
                var vm = this;
                DataBridge.get('Contacts.deleteContact', this.contactId, '*', function(data){vm.populateDeleteContact(data)});
            },
            populateDeleteContact(response)
            {
                var result = response['Contacts.deleteContact'][this.contactId];
                if(result.status == _ID_SUCCESS)
                {
                    this.deleteContactDialog = false;
                    swal("Success!", "Contact deleted successfully.", "success");
                    location.href = `${base_url}` + 'contacts/list';
                }
                else
                {
                    swal("Error!", "Something went wrong!", "error");
                    this.confirmDeleteBtnShow = false;
                }
            },
            showEmailModal: function(){
                //console.log('new email from contact rail');
                this.$emit('show-new-email-modal');
            },
            toggleDropdownSwitchContacts: function(){
                this.dropdownOpen = !this.dropdownOpen;
                if(this.primarySecondaryContactLabel == 1)
                {
                    this.dropdownOpenPrimary = !this.dropdownOpenPrimary;
                }else
                {
                    this.dropdownOpenSecondary = !this.dropdownOpenSecondary;
                }
            },
            secondaryModalChanges:function(){
                this.secondaryPrimaryModal = !this.secondaryPrimaryModal;
            },
            setToChangeContactLevel: function(data){
                var vm = this;
                if(data['data'])
                {
                    vm.switchContactLevelFlage = data['data']['data']['Contacts.getActivePoliciesAndSecondaryContact'][this.contactId];
                }
                else
                {
                    vm.switchContactLevelFlage = data['Contacts.getActivePoliciesAndSecondaryContact'][this.contactId];
                }
                let objectData = {
                    'objectId':this.contactId,
                    'objectName':'Contact',
                    'fields': '*'
                 }
                DataBridgeContacts.save('Contacts.getContactDetailsById', objectData, function(data){vm.populateContactData(data)});
            },
            switchToContactLavel: function ()
            {
                var vm = this;
                let objectData = {
                    'contactId': this.contactId,
                };
                if(this.primarySecondaryContactLabel == 1)
                {
                    DataBridge.save('Contacts.switchContactToPrimary', objectData, function (data){vm.populateSwitchToContactLavel(data)});
                    this.$root.$emit('additionalFlag', 2);
                }
                else
                {
                    DataBridge.save('Contacts.switchSecondaryContact', objectData, function (data){vm.populateSwitchToContactLavel(data)});
                    this.$root.$emit('additionalFlag', 1);
                }
                this.dropdownOpenPrimary = false;
                this.dropdownOpenSecondary = false;
                this.dropdownOpen = false;
            },

        },
        mounted: function()
        {
            var vm = this;
            DataBridge.get('Contacts.getActivePoliciesAndSecondaryContact', vm.contactId, '*', function(data){
                    vm.setToChangeContactLevel(data);
                }
            );
            this.$root.$on('updateClientLabel', (data) => {
                if(data == 1)
                {
                    let objectData = {
                    'objectId':this.contactId,
                    'objectName':'Contact',
                    'fields': '*'
                    }
                    DataBridgeContacts.save('Contacts.getActivePoliciesAndSecondaryContact', this.contactId, function(data){vm.setToChangeContactLevel(data)});
                    DataBridgeContacts.save('Contacts.getContactDetailsById', objectData, function(data){vm.populateContactData(data)});
                }
             });
            //DataBridge.get('Contact', this.contactId, '*', function(data){vm.populateContactData(data)});
            this.$root.$on('contact_lead_type_status', (data) => {
                if(data == 1)
                {
                    DataBridgeContacts.save('Contacts.getSecondaryContactDetail', this.contactId, function(data){vm.populateSecondaryContactDetail(data)});
                }
             });
            DataBridgeContacts.save('Contacts.getContactAdditionalNumber', this.contactId,  this.populatePhoneNumber);
            DataBridge.get('ContactsMailingAddress', this.contactId, '*', function(data){
                vm.populateMaillingAddressData(data)

            });

            DataBridge.get('PhoneNumbersOptInOutStatus', this.contactId, '*', function(data){
                    // console.log("optin"+JSON.stringify(data));
                vm.populatephoneNumbersOptInOutData(data)
            });

            DataBridgeContacts.save('Contacts.getAdditionalEmails', this.contactId, function(data){
                    // console.log("optin"+JSON.stringify(data));
                vm.populateAdditionalEmails(data)
            });

            //DataBridgeContacts.save('Contacts.getContactPhoneNumbersOptInOutStatus', this.contactId, this.contactPrimaryPhoneOptInStatus);
            // DataBridgeContacts.save('Contacts.getSecondaryContacts', this.contactId, function(data){vm.populateSecondaryContactData(data)});
        },
    });
</script>