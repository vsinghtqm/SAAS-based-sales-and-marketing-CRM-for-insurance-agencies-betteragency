<?php
namespace App\Controller;
use ComponentLibrary\Lib\ComponentTools;
use App\Controller\FrontEndApi;
?>
<style>

tbody#upcomingCampaginList tr td:last-child {
    color: #F3953F !important;
    font-weight: 500 !important;
    font-size: 13px !important;
    line-height: 22px !important;
    text-transform: uppercase !important;
}
tbody#upcomingCampaginList tr td:last-child span {
    background: #F3953F !important;
    height: 17px;
    width: 17px;
    border-radius: 50%;
    color: #fff;
    display: inline-block;
    line-height: 19px;
    text-align: center;
    margin-right: 4px;
}
:focus-visible {
    outline: -webkit-focus-ring-color auto 0px !important;

}
.fail-alert-icon{color:#FF0000 !important;}
</style>
<script type="text/x-template" id="<?=ComponentTools::templateName(__FILE__)?>">
<div>
        <div class="d-flex custom-grey">
        <h4 class="upcoming-heading">Campaigns</h4>
		<v-btn @click="addToCampaignContactCard" class="ml-auto btn btn-outline-success btn-round btn-position" style="background-color:transparent;" >
			<v-icon class="plus-icon-css">mdi-plus</v-icon><span class="text-success">Add to campaign</span>
               
            </v-btn>  
        </div>
        <div class="scroll-container pl-0 pr-0 pt-0 mt-5 campaign-tab-max-height" style="max-height: 700px;">
			
			<v-simple-table class="table table-striped table-custom-contact ">
				<template>
					<thead>
						<tr>
						<th width="40%" class="text-left font-weight-bold">
							<b>ACTIVE</b>
						</th>
						<th width="15%" class="text-left font-weight-bold">
							TYPE
						</th>
						<th width="15%" class="text-left font-weight-bold">
							OFF/ON
						</th>
						<th width="15%" class="text-left font-weight-bold">
							PAUSE
						</th>
						<th width="15%" class="text-left font-weight-bold">
							PREVIEW
						</th>
						</tr>
					</thead>
					<tbody v-if="Object.keys(campaignsListing).length > 0 ">
						<tr  v-for="campaign in campaignsListing" :key="campaign.id"  >
							<td width="40%" class="text-capitalize">{{campaign.agency_campaign_master.name}} <span v-if="campaign.agency_campaign_master.client_referrer_id">(Referrer Thank You)</span> <v-img :lazy-src='referral_partner_img' max-height="10px" max-width="10px" :src='referral_partner_img' v-if="campaign.agency_campaign_master.referral_partner_user_id"></v-img></td>
						<!-- new added -->
							<!-- <td>{{getCampaignType(campaign.agency_campaign_master.type)}}</td> -->
						<!-- <td v-for="cType in campaignsTypes  ">
							<span v-if="cType['value'] == campaign.agency_campaign_master.type">{{cType['name']}}</span>
							<span v-else="cType['value'] == campaign.agency_campaign_master.type"></span>
						</td> -->
							<td v-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_NEW_LEAD ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_NEW_LEAD)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_RENEWAL ?>">
							<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_RENEWAL)?></td>
							<td v-else-if="campaign.agency_campaign_master.type ==  <?= _CAMPAIGN_TYPE_CROSS_SELL ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_CROSS_SELL)?></td>
							<td v-else-if="campaign.agency_campaign_master.type ==  <?= _CAMPAIGN_TYPE_CLIENT_BIRTHDAY ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_CLIENT_BIRTHDAY)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_POLICY_CANCELLED ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_POLICY_CANCELLED)?></td>
							<td v-else-if="campaign.agency_campaign_master.type ==  <?= _CAMPAIGN_TYPE_CLAIM ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_CLAIM)?></td>
							<td v-else-if="campaign.agency_campaign_master.type ==  <?= _CAMPAIGN_TYPE_CLIENT_WELCOME ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_CLIENT_WELCOME)?></td>
							<td v-else-if="campaign.agency_campaign_master.type ==  <?= _CAMPAIGN_TYPE_PIPELINE ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_PIPELINE)?></td>
							<td v-else-if="campaign.agency_campaign_master.type ==  <?= _CAMPAIGN_TYPE_SERVICE_PIPELINE ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_SERVICE_PIPELINE)?></td>
							<td v-else-if="campaign.agency_campaign_master.type ==  <?= _CAMPAIGN_TYPE_WIN_BACK ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_WIN_BACK)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_MISSED_APPOINTMENT_FOLLOW_UP ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_MISSED_APPOINTMENT_FOLLOW_UP)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_LONG_TERM_NURTURE ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_LONG_TERM_NURTURE)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_X_DATE ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_X_DATE)?>
							</td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_PROSPECT_BIRTHDAY ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_PROSPECT_BIRTHDAY)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_RENEWAL_COMPLETED ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_RENEWAL_COMPLETED)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_REFERRER_THANK_YOU ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_REFERRER_THANK_YOU)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_CLIENT_REFERRAL ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_CLIENT_REFERRAL)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_LOAN_OFFICER_HOME_CONDO_PROPERTY ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_LOAN_OFFICER_HOME_CONDO_PROPERTY)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_GOOGLE_REVIEWS ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_GOOGLE_REVIEWS)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_WIN_BACK_LOST_CLIENTS ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_WIN_BACK_LOST_CLIENTS)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_GENERAL_NEW_LEAD ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_GENERAL_NEW_LEAD)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_POLICY_REVIEW_COMPLETED ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_POLICY_REVIEW_COMPLETED)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_PENDING_CANCELLATION ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_PENDING_CANCELLATION)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_CARRIER_INSOLVENCY ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_CARRIER_INSOLVENCY)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_RENEWAL_OVER_THRESHOLD ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_RENEWAL_OVER_THRESHOLD)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_RENEWAL_UNDER_THRESHOLD ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_RENEWAL_UNDER_THRESHOLD)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_SMS_OPT_IN ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_SMS_OPT_IN)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_WORKING_RENEWAL ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_WORKING_RENEWAL)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_RENEWAL_READY ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_RENEWAL_READY)?></td>
                            <td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_CUSTOM_CAMPAIGN ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_CUSTOM_CAMPAIGN)?></td>
							<td v-else></td>
							<!-- end -->
							<td v-if="!campaignClickedItems.includes(campaign.id)">
							<v-btn v-on:click="contactStopCampaignButton(campaign.id)" class="" color="#5FB322" style="color: #fff;">
							<v-icon small color="#fff">fas fa-toggle-on</v-icon> <span class="ml-1">ON</span>      
							</v-btn></td>
							<td v-else><v-btn class=""  style="color: #fff;" >
								<v-icon small color="#fff">fas fa-toggle-off</v-icon> <span class="ml-1">OFF</span>    
							</v-btn>
							</td>
							<td v-if="!pausedCampaign.includes(campaign.id)"  v-on:click="contactPauseCampaignButton(campaign.id)">
                                <v-btn class="pause-btn-new"  style="color: #fff;" >
                                    <v-icon small color="#fff">mdi-pause</v-icon> <span class="ml-1">PAUSE</span>
                                </v-btn>
                            </td>
                            <td  v-else>
                                <img :src="`${base_url}/img/loader.gif`" style="width: 60px; height: 35px" />
                            </td>
							<td><v-btn class="ml-auto btn btn-outline-success btn-round btn-preview" style="background-color:transparent;" :id="campaign.id" v-on:click="activeCampaigndialog = true;showRunningScheduleCampaigns(campaign.id,contactId);">
							<v-icon small>mdi-eye-outline</v-icon> <span class="ml-1">PREVIEW</span>
							</v-btn></td>
							
						</tr>
						
					</tbody>
					<tbody v-else>
						<tr>
							<td colspan="5" class="text-center">No active campaigns!</td>
						</tr>
					</tbody>
				</template>
			</v-simple-table>
			
			<v-dialog
			  v-if="contactPauseCampaignDialog"
			  v-model="contactPauseCampaignDialog"
			  persistent
			  max-width="600"
			  max-height="184"
			>
				<v-card style="height:184px;">
					<v-card-title class="text-h6 common-title-popup" style="text-transform:capitalize !important;">
					Pause Campaign
					</v-card-title>
					<v-card-text class="campaign-text">Are you sure you want to pause the campaign?</v-card-text>
					<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn 
						color="teal"
						text
						@click="contactPauseCampaignDialog = false"
					>
						CANCEL
					</v-btn>
					<v-btn class="btn-save-create-service" style="width:auto;"
						color="#F65559"
						text
						@click="contactPauseCampaign()"
					>
						Yes, pause campaign
					</v-btn>
					</v-card-actions>
				</v-card>
							</v-dialog>

			<!-- Listing Pause campaign-->
		<v-simple-table class="table table-striped table-custom-contact">
            <template v-slot:default>
				<thead>
					<!-- <tr>
						<th class="text-left font-weight-bold">
							<b>Pause</b>
						</th>
						<th class="text-left font-weight-bold">
							TYPE
						</th>
						<th class="text-left font-weight-bold">
							Days Paused
						</th>
						<th class="text-left font-weight-bold">
							Campaign Progress
						</th>
						<th class="text-left font-weight-bold">
							Resume
						</th>
						<th class="text-left font-weight-bold">
							Preview Messages
						</th>
					</tr> -->
					<tr>

						<th width="40%" class="text-left font-weight-bold">
							<b>Paused</b>
						</th>
						<th width="15%" class="text-left font-weight-bold">
							TYPE
						</th>
						<th width="15%" class="text-left font-weight-bold">
							Days Paused
						</th>
						<th width="15%" class="text-left font-weight-bold">
							Resume
						</th>
						<th width="15%" class="text-left font-weight-bold">
							PREVIEW
						</th>
					</tr>
				</thead>
				<!-- <tbody  v-if="campaignPauseListing.status==1" v-html="campaignPauseListing.list"> -->
				
				<tbody v-if="Object.keys(campaignPauseListing).length > 0 ">
					<tr v-for="campaignPause in campaignPauseListing" :key="campaignPause.id">
						<td width="40%" style="text-transform: capitalize;">{{campaignPause.agency_campaign_master.name}} <span v-if="campaignPause.client_referrer_id">(Referrer Thank You)</span> </td>
						<td v-if="campaignPause.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_NEW_LEAD ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_NEW_LEAD)?></td>
							<td v-else-if="campaignPause.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_RENEWAL ?>">
							<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_RENEWAL)?></td>
							<td v-else-if="campaignPause.agency_campaign_master.type ==  <?= _CAMPAIGN_TYPE_CROSS_SELL ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_CROSS_SELL)?></td>
							<td v-else-if="campaignPause.agency_campaign_master.type ==  <?= _CAMPAIGN_TYPE_CLIENT_BIRTHDAY ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_CLIENT_BIRTHDAY)?></td>
							<td v-else-if="campaignPause.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_POLICY_CANCELLED ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_POLICY_CANCELLED)?></td>
							<td v-else-if="campaignPause.agency_campaign_master.type ==  <?= _CAMPAIGN_TYPE_CLAIM ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_CLAIM)?></td>
							<td v-else-if="campaignPause.agency_campaign_master.type ==  <?= _CAMPAIGN_TYPE_CLIENT_WELCOME ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_CLIENT_WELCOME)?></td>
							<td v-else-if="campaignPause.agency_campaign_master.type ==  <?= _CAMPAIGN_TYPE_PIPELINE ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_PIPELINE)?></td>
							<td v-else-if="campaignPause.agency_campaign_master.type ==  <?= _CAMPAIGN_TYPE_SERVICE_PIPELINE ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_SERVICE_PIPELINE)?></td>
							<td v-else-if="campaignPause.agency_campaign_master.type ==  <?= _CAMPAIGN_TYPE_WIN_BACK ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_WIN_BACK)?></td>
							<td v-else-if="campaignPause.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_MISSED_APPOINTMENT_FOLLOW_UP ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_MISSED_APPOINTMENT_FOLLOW_UP)?></td>
							<td v-else-if="campaignPause.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_LONG_TERM_NURTURE ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_LONG_TERM_NURTURE)?></td>
							<td v-else-if="campaignPause.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_X_DATE ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_X_DATE)?>
							</td>
							<td v-else-if="campaignPause.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_PROSPECT_BIRTHDAY ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_PROSPECT_BIRTHDAY)?></td>
							<td v-else-if="campaignPause.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_RENEWAL_COMPLETED ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_RENEWAL_COMPLETED)?></td>
							<td v-else-if="campaignPause.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_REFERRER_THANK_YOU ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_REFERRER_THANK_YOU)?></td>
							<td v-else-if="campaignPause.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_CLIENT_REFERRAL ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_CLIENT_REFERRAL)?></td>
							<td v-else-if="campaignPause.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_LOAN_OFFICER_HOME_CONDO_PROPERTY ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_LOAN_OFFICER_HOME_CONDO_PROPERTY)?></td>
							<td v-else-if="campaignPause.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_GOOGLE_REVIEWS ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_GOOGLE_REVIEWS)?></td>
							<td v-else-if="campaignPause.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_WIN_BACK_LOST_CLIENTS ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_WIN_BACK_LOST_CLIENTS)?></td>
							<td v-else-if="campaignPause.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_GENERAL_NEW_LEAD ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_GENERAL_NEW_LEAD)?></td>
							<td v-else-if="campaignPause.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_POLICY_REVIEW_COMPLETED ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_POLICY_REVIEW_COMPLETED)?></td>
							<td v-else-if="campaignPause.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_PENDING_CANCELLATION ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_PENDING_CANCELLATION)?></td>
							<td v-else-if="campaignPause.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_CARRIER_INSOLVENCY ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_CARRIER_INSOLVENCY)?></td>
							<td v-else-if="campaignPause.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_RENEWAL_OVER_THRESHOLD ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_RENEWAL_OVER_THRESHOLD)?></td>
							<td v-else-if="campaignPause.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_RENEWAL_UNDER_THRESHOLD ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_RENEWAL_UNDER_THRESHOLD)?></td>
							<td v-else-if="campaignPause.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_SMS_OPT_IN ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_SMS_OPT_IN)?></td>
							<td v-else-if="campaignPause.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_WORKING_RENEWAL ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_WORKING_RENEWAL)?></td>
							<td v-else-if="campaignPause.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_RENEWAL_READY ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_RENEWAL_READY)?></td>
                            <td v-else-if="campaignPause.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_CUSTOM_CAMPAIGN ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_CUSTOM_CAMPAIGN)?></td>
							<td v-else></td>
							
						<!-- <td >
							{{ campaignPause.pause_date,new Date() | noOfDays(campaignPause.pause_date,new Date())}} Days
						</td> -->
						<td> 
							<!-- {{ campaignPause.pause_date,campaignPause.created | noOfDays(campaignPause.pause_date,campaignPause.created)}} -->
							<span :title="campaignPause.pause_date | convertDateToUtcWithShortMonth(campaignPause.pause_date)" style="cursor:pointer;">
							 {{ campaignPause.pause_date,new Date() | noOfDays(campaignPause.pause_date,new Date())}} Days
							 </span>
						</td> 
						
						<td v-if="!resumeCampaign.includes(campaignPause.id)" v-on:click="contactResumeCampaignDialog = true;contactResumeCampaign(campaignPause.id)">
                            <v-btn class="pause-btn-new" style="color: #FFF;" >
                                <v-icon small color="#fff">mdi-resume</v-icon> <span class="mdi mdi-play"></span>
                                <span class="ml-1">Resume</span>
                            </v-btn>
                        </td>
                        <td v-else>
                            <img :src="`${base_url}/img/loader.gif`" style="width: 60px; height: 35px" />
                        </td>
						<td><v-btn class="ml-auto btn btn-outline-success btn-round btn-preview" style="background-color:transparent;" v-on:click="dialog = true;showPausedCampaigns(campaignPause.id,contactId);">
							<v-icon small>mdi-eye-outline</v-icon> <span class="ml-1">PREVIEW</span>
						</v-btn></td>
						
						
							
					</tr>
				</tbody>
				<tbody v-else>
					<tr>
						<td colspan="5" class="text-center" style="color:rgb(58 53 65 / 87%);text-transform: none !important;">No records found!</td>
					</tr>
				</tbody>
				
				<!-- resume campaign Dialog -->
						<v-dialog
							  v-if="contactResumeCampaignDialog"
							  v-model="contactResumeCampaignDialog"
							  persistent
							  max-width="600"
							  max-height="184"
							>
							<v-card style="height:184px;">
								<v-card-title class="text-h6 common-title-popup" style="text-transform:capitalize !important;">
								Resume Campaign
								</v-card-title>
								<v-card-text class="campaign-text">Are you sure you want to Resume the campaign?</v-card-text>
								<v-card-actions>
								<v-spacer></v-spacer>
								<v-btn 
									color="teal"
									text
									@click="contactResumeCampaignDialog = false"
								>
									CANCEL
								</v-btn>
								<v-btn class="btn-save-create-service" style="width:auto;"
									color="#F65559"
									text
									@click="saveContactResumeCampaign()"
								>
									YES,RESUME CAMPAIGN
								</v-btn>
								</v-card-actions>
							</v-card>
						</v-dialog>
							
		    </template>
        </v-simple-table>	
		
		<!----------------- Pause campaign Modal  ------------------->
		<v-dialog v-model="dialog" max-width="50%">
				<v-card style="height:100%;">
					<v-card-title class="text-h5">
						<h5 class="modal-title">Paused Campaigns</h5>
						<div class="cross-icon"><v-btn text @click="dialog = false"><v-icon>mdi-close</v-icon></v-btn></div>
					</v-card-title>

					<v-card-text class="pb-0">
						<div class="modal-body modal-bodyRC" id="body_data" style="padding-top:10px;padding-bottom:0;">
							<div class="panel-content scroll1" style="overflow-y:auto; overflow-x:hidden; max-height: 46vh;">
								<div class="row col-lg-12">
									<div class="col-lg-6">
										<span class="col-lg-6" style="padding-left: 0;">Email/SMS/Task Details</span>
									</div>
								</div>
								<div class="vertical-timeline" id="collapse_stage_1">
									<ul class="list-unstyled" id="paused-campaigns-list-popup" v-html="pausedCampaignListing.listing">
									</ul>
								</div>
							</div>
							<v-card-actions class="my-2">
								<v-spacer></v-spacer>

								<v-btn color="#29AD8E" text @click="dialog = false">
									Close
								</v-btn>
							</v-card-actions>
						</div>
					</v-card-text>
				</v-card>
			</v-dialog> 
			<!----------------- End Pause campaign Modal  ------------------->	

			
		<v-simple-table class="table table-striped table-custom-contact">
            <template v-slot:default>
				<thead>
					<!-- <tr>
						<th class="text-left font-weight-bold">
							<b>Upcoming</b>
						</th>
						<th class="text-left font-weight-bold">
							TYPE
						</th>
						<th class="text-left font-weight-bold">
							Start Date
						</th>
						<th class="text-left font-weight-bold">
							Cancel
						</th>
					</tr> -->
					<tr>
						<th width="40%" class="text-left font-weight-bold">
							<b>Upcoming</b>
						</th>
						<th width="15%" class="text-left font-weight-bold">
							TYPE
						</th>
						<th width="30%" class="text-left font-weight-bold">
							Start Date
						</th>
						<th width="15%" class="text-left font-weight-bold">
							Cancel
						</th>
						
					</tr>
				</thead>
				<tbody v-if="campaignUpcomingListing.status==1" id="upcomingCampaginList" v-html="upcomingCampaginList">
				</tbody>
		    </template>
        </v-simple-table>
			<!-- upcoming dialog box -->
			
			<v-dialog
			  v-if="contactUpcomingCampaignDialog"
			  v-model="contactUpcomingCampaignDialog"
			  persistent
			  max-width="600"
			  max-height="184"
			>
				<v-card style="height:184px;">
					<v-card-title class="text-h6 common-title-popup" style="text-transform:capitalize !important;">
					Cancel Campaign
					</v-card-title>
					<v-card-text class="campaign-text">Are you sure you want to Cancel the campaign?</v-card-text>
					<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn 
						color="teal"
						text
						@click="contactUpcomingCampaignDialog=false"
					>
						CANCEL
					</v-btn>
					<v-btn class="btn-save-create-service" style="width:auto;"
						color="#F65559"
						text
						@click="contactStopUpcomingCampaign()"
					>
						YES,Cancel CAMPAIGN
					</v-btn>
					</v-card-actions>
				</v-card>
			</v-dialog>

		<v-simple-table class="table table-striped table-custom-contact">
            <template v-slot:default>
			 <thead>
			 	<tr>
					<th width="40%"class="text-left font-weight-bold">
						<b>PAST</b>
					</th>
					<th width="15%" class="text-left font-weight-bold">
						TYPE
					</th>
					<th width="45%" class="text-left font-weight-bold">
						Start Date
					</th>
					
				</tr>
            </thead>
            <tbody v-if="Object.keys(campaignHistoryListing).length > 0 ">
						<tr  v-for="campaign in campaignHistoryListing" :key="campaign.id"  >
							<td width="40%" style="text-transform: capitalize;">{{campaign.agency_campaign_master.name}} <span v-if="campaign.agency_campaign_master.client_referrer_id">(Referrer Thank You)</span> <v-img :lazy-src='referral_partner_img' max-height="10px" max-width="10px" :src='referral_partner_img' v-if="campaign.agency_campaign_master.referral_partner_user_id"></v-img></td>
						<!-- new added -->
							<!-- <td>{{getCampaignType(campaign.agency_campaign_master.type)}}</td> -->
						<!-- <td v-for="cType in campaignsTypes  ">
							<span v-if="cType['value'] == campaign.agency_campaign_master.type">{{cType['name']}}</span>
							<span v-else="cType['value'] == campaign.agency_campaign_master.type"></span>
						</td> -->
							<td v-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_NEW_LEAD ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_NEW_LEAD)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_RENEWAL ?>">
							<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_RENEWAL)?></td>
							<td v-else-if="campaign.agency_campaign_master.type ==  <?= _CAMPAIGN_TYPE_CROSS_SELL ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_CROSS_SELL)?></td>
							<td v-else-if="campaign.agency_campaign_master.type ==  <?= _CAMPAIGN_TYPE_CLIENT_BIRTHDAY ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_CLIENT_BIRTHDAY)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_POLICY_CANCELLED ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_POLICY_CANCELLED)?></td>
							<td v-else-if="campaign.agency_campaign_master.type ==  <?= _CAMPAIGN_TYPE_CLAIM ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_CLAIM)?></td>
							<td v-else-if="campaign.agency_campaign_master.type ==  <?= _CAMPAIGN_TYPE_CLIENT_WELCOME ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_CLIENT_WELCOME)?></td>
							<td v-else-if="campaign.agency_campaign_master.type ==  <?= _CAMPAIGN_TYPE_PIPELINE ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_PIPELINE)?></td>
							<td v-else-if="campaign.agency_campaign_master.type ==  <?= _CAMPAIGN_TYPE_SERVICE_PIPELINE ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_SERVICE_PIPELINE)?></td>
							<td v-else-if="campaign.agency_campaign_master.type ==  <?= _CAMPAIGN_TYPE_WIN_BACK ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_WIN_BACK)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_MISSED_APPOINTMENT_FOLLOW_UP ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_MISSED_APPOINTMENT_FOLLOW_UP)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_LONG_TERM_NURTURE ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_LONG_TERM_NURTURE)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_X_DATE ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_X_DATE)?>
							</td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_PROSPECT_BIRTHDAY ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_PROSPECT_BIRTHDAY)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_RENEWAL_COMPLETED ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_RENEWAL_COMPLETED)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_REFERRER_THANK_YOU ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_REFERRER_THANK_YOU)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_CLIENT_REFERRAL ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_CLIENT_REFERRAL)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_LOAN_OFFICER_HOME_CONDO_PROPERTY ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_LOAN_OFFICER_HOME_CONDO_PROPERTY)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_GOOGLE_REVIEWS ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_GOOGLE_REVIEWS)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_WIN_BACK_LOST_CLIENTS ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_WIN_BACK_LOST_CLIENTS)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_GENERAL_NEW_LEAD ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_GENERAL_NEW_LEAD)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_POLICY_REVIEW_COMPLETED ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_POLICY_REVIEW_COMPLETED)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_PENDING_CANCELLATION ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_PENDING_CANCELLATION)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_CARRIER_INSOLVENCY ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_CARRIER_INSOLVENCY)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_RENEWAL_OVER_THRESHOLD ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_RENEWAL_OVER_THRESHOLD)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_RENEWAL_UNDER_THRESHOLD ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_RENEWAL_UNDER_THRESHOLD)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_SMS_OPT_IN ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_SMS_OPT_IN)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_WORKING_RENEWAL ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_WORKING_RENEWAL)?></td>
							<td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_RENEWAL_READY ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_RENEWAL_READY)?></td>
                            <td v-else-if="campaign.agency_campaign_master.type == <?= _CAMPAIGN_TYPE_CUSTOM_CAMPAIGN ?>">
								<?= getEntityDef($err,_CAMPAIGN_TYPE,_CAMPAIGN_TYPE_CUSTOM_CAMPAIGN)?></td>
							<td v-else></td>
							<!-- end -->
							<td v-if="campaign.status == <?=_RUN_SCHEDULE_STATUS_CANCELLED ?>">
							Canceled</td>
							<td v-if="campaign.status != <?=_RUN_SCHEDULE_STATUS_CANCELLED ?>">{{new Date(campaign.created) | convertDateToUtcWithShortMonth(campaign.created)}}</td>
						
						</tr>
						
					</tbody>
            </template>
        </v-simple-table>
        </div>
		<template>
			<v-row justify="center">
				<v-dialog
				v-model="addCampaignDialog"
				max-width="800"
				max-height="453"

				>

				<v-card >
				<div class="v-card__title campaign-title">Add to campaign <div class="cross-icon"><a text @click="addCampaignDialog = false"><i aria-hidden="true" class="v-icon notranslate mdi mdi-close theme--light"></i></a></div></div>



					<v-divider class="m-0"></v-divider>
					<div class="slim-scroll pl-0 pr-0 pt-0 mt-0 model-height-auto" >
					<v-list class = "campaign-list-design">
						<v-list-item v-for="(items, index) in contactAllCampaigns.campaigns">
							<v-list-item-content>
								<v-list-item-title class="table-title">{{index}}</v-list-item-title>

								<v-simple-table class="table-striped table-campaign">
									<template v-slot:default>
									<thead>
										<tr>

										</tr>
									</thead>
									<tbody>
										<tr v-for="item in items">
											<td style="text-transform: capitalize;">{{item.campaign_name}}</td>
											<td v-if="!startCampaignLoader.includes(item.campaign_id)" class="text-right"><v-btn  :id="'left_content_submit_' + item.campaign_id" v-on:click="startCampaignContactCard(item)">
												START CAMPAIGN
											</v-btn></td>
                                            <td v-else class="text-right">
                                                <img :src="`${base_url}/img/loader.gif`" class="text-right" style="width: 60px; height: 35px" />
                                            </td>
										</tr>
									</tbody>
									</template>
								</v-simple-table>
							</v-list-item-content>
						</v-list-item>
					</v-list>
					</div>

					</v-card-text>

				</v-card>
				</v-dialog>
			</v-row>
		</template>

		<template>
			<v-row justify="center">
				<v-dialog
				v-model="appointmentCampaignDialog"
				persistent
				max-width="450"
				>

					<v-card class="popup-child">
						<v-card-title class="modal-heading-sub-popup">
						Appointment Date	
						<div class="cross-icon"><a text @click="appointmentCampaignDialog = false"><i aria-hidden="true" class="v-icon notranslate mdi mdi-close theme--light"></i></a></div>				
					</v-card-title>	




						<v-card-text class="pt-0 pb-0">Select Appointment Date so you can be reminded at the date/time of your appointment (15 min before) as well as keep track of how many appointments you have.</v-card-text>
						 <v-container class="pl-5 pr-5">
						<v-row>
							<v-col cols="12" class="mt-2">
								<v-menu ref="menu1" v-model="menu1" :close-on-content-click="false" max-width="290">
									<template v-slot:activator="{ on, attrs }">
										<v-text-field
											v-model="appoitmentDateFormatted"
											label="Appointment Date"
											v-bind="attrs"
											v-on="on"
											@click:clear="date = null"
											prepend-icon="mdi-calendar"
											outlined
											required
											@keydown.prevent
										></v-text-field>
									</template>
									<v-date-picker
										v-model="date"
										color="#29AD8E"
										no-title
										@change="menu1 = false"
									></v-date-picker>
								</v-menu>
							</v-col>
							<v-col cols="12">
							<v-select v-model="appointment_time" :items="appoitmentTime" label="Appoitment Time" dense outlined item-text="name"
							item-value="value"></v-select>


							</v-col>
						</v-row>
						</v-container>
						<v-card-actions>
							<v-spacer></v-spacer>



							<v-btn							
								depressed
								color="teal"
								dark
								@click="saveAppointmentDateContactCard"
							>
								Save
							</v-btn>


						</v-card-actions>
					</v-card>
				</v-dialog>
			</v-row>
		</template>

		<template>
			<v-row justify="center">
				<v-dialog
				v-model="pendingCancellationCampaignDailog"
				persistent
				max-width="450"
				>

					<v-card class="popup-child">
						<v-card-title class="modal-heading-sub-popup">
						CANCELLATION DATE	
						<div class="cross-icon"><a text @click="pendingCancellationCampaignDailog = false"><i aria-hidden="true" class="v-icon notranslate mdi mdi-close theme--light"></i></a></div>				
					</v-card-title>	




						<v-card-text class="pt-0 pb-0">Enter the Cancellation Date and time. Cancellation Date and Time is required to run the Pending Cancellation Campaign.</v-card-text>
						 <v-container class="pl-5 pr-5">
						<v-row>
							<v-col cols="12" class="mt-2">
								<v-menu ref="menu2" v-model="menu2" :close-on-content-click="false" max-width="290">
									<template v-slot:activator="{ on, attrs }">
										<v-text-field
											v-model="appoitmentDateFormatted"
											label="Cancellation Date"
											v-bind="attrs"
											v-on="on"
											@click:clear="date = null"
											prepend-icon="mdi-calendar"
											outlined
											required
											@keydown.prevent
										></v-text-field>
									</template>
									<v-date-picker
										v-model="date"
										color="#29AD8E"
										no-title
										@change="menu2 = false"
									></v-date-picker>
								</v-menu>
							</v-col>
							<v-col cols="12">
							<v-select v-model="cancellation_time" :items="appoitmentTime" label="Cancellation Time" dense outlined item-text="name"
							item-value="value"></v-select>


							</v-col>
						</v-row>
						</v-container>
						<v-card-actions>
							<v-spacer></v-spacer>



							<v-btn							
								depressed
								color="teal"
								dark
								@click="startPendingCancellationContactCard"
							>
								Save
							</v-btn>


						</v-card-actions>
					</v-card>
				</v-dialog>
			</v-row>
		</template>

		<template>
			<v-row justify="center">
				<v-dialog v-model="runningCampaignList" persistent max-width="950" >
					<v-card class="popup-child">
						<v-card-title class="modal-heading-sub-popup">
							Running
							<div class="cross-icon"><a text @click="runningCampaignList = false"><i aria-hidden="true" class="v-icon notranslate mdi mdi-close theme--light"></i></a></div>				
						</v-card-title>
						
						<v-card>
							<div class="modal-body modal-bodyRC" id="body_data" style="padding-top:10px;">
							<div class="panel-content scroll1" style="overflow-y:auto; overflow-x:hidden; max-height: 46vh;">
							<div class="row col-lg-12">
								<div class="col-lg-6">
									<span class="col-lg-6" style="padding-left: 0;">Email/SMS/Task Details</span>
								</div>
								<div class="col-lg-6 sms_dnd_activated_span" style="text-align: right;color: red;   font-size: 13px;display: none;">
									<span>The contact has enabled sms DND.</span>
								</div>
							</div>
							<div class="vertical-timeline" id="collapse_stage_1">
								<ul class="list-unstyled" id="campaigns-list-popup" v-for="template in list" :key="template.templateId">
									
								<li v-if="template.type == 1">
								<span class="time">{{ template.campaignScheduledTime }}</span>
								<span class="dot">
									<i class="fas fa-sms" style="cursor:pointer;font-size: 18px;"></i>
								</span>
								<div class="content">
									<div :id = "`campaign-sms-view-mode_${template.templateId}`">
										<div class = "row">
											<h3 class="subtitle col-lg-8">{{ template.templateTitle }}</h3>
											<div class="col-lg-4 text-right" :id = "`sms_email_task_templates_${template.runningEmailSmsScheduledId}`">
												<div v-if = "template.optInOutStatus && template.optInOutStatus != '' && template.optInOutStatus == 1 && template.campaignType != 27" >
													<div v-if="!isCampaignExist.includes(template.templateId)">
														<button  class="btn btn-info btn-sm btn-sendnow my-1" style = "left: -3px; color:#fff"  @click="sendRunningCampaignEmailSmsTaskTemplate(template.login_agency_id,template.contact_id,template.template_id,template.runningEmailSmsScheduledId,1,template.campaign_id);">
														<i class="fa fa-paper-plane" aria-hidden="true" style="cursor:pointer;"></i>Send now
														</button>

														<button class="btn btn-danger btn-sm my-1" style="left: -3px; color:#fff" @click="cancelRunningCampaignEmailSmsTaskTemplate(template.login_agency_id,template.contact_id,template.template_id, template.runningEmailSmsScheduledId,1,template.contact_business_id,template.campaign_id);">
														<i class="fa fa-ban" aria-hidden="true" style="cursor:pointer;"></i> Cancel
														</button>
													</div>

													<div v-else-if="buttonStatusCancelled.includes(template.templateId)">
														<button class="btn btn-danger btn-sm my-1" style="left: -3px; color:#fff" > <i class="fa fa-check" aria-hidden="true" style="cursor:pointer;"></i> Cancelled </button>

													</div>
													<div v-else-if="buttonStatusSuccess.includes(template.templateId)">
														<button class="btn btn-success btn-sm my-1" style="left: -3px; color:#fff" > <i class="fa fa-check" aria-hidden="true" style="cursor:pointer;"></i> Success </button>

													</div>

													<div v-else class="text-center" >
														<img :src="`${base_url}/img/loader.gif`" style="width: 60px; height: 35px"/>
													</div>
												</div>

												<div v-else-if="template.campaignType == 27 && optInOutStatus == 0">
													<div v-if="!isCampaignExist.includes(template.templateId)">
														<button class="btn btn-info btn-sm btn-sendnow my-1" style="left: -3px; color:#fff" @click="sendRunningCampaignEmailSmsTaskTemplate(template.login_agency_id,template.contact_id,template.template_id,template.runningEmailSmsScheduledId,1,template.campaign_id);">
														<i class="fa fa-paper-plane" aria-hidden="true" style="cursor:pointer;"></i>Send now
														</button>
														<button class="btn btn-danger btn-sm my-1" style="left: -3px;color:#fff" @click="cancelRunningCampaignEmailSmsTaskTemplate(template.login_agency_id,template.contact_id,template.template_id,template.runningEmailSmsScheduledId,1,template.contact_business_id,template.campaign_id);">
														<i class="fa fa-ban" aria-hidden="true" style="cursor:pointer;"></i> Cancel
														</button>
													</div>

													<div v-else-if="buttonStatusCancelled.includes(template.templateId)">
														<button class="btn btn-danger btn-sm my-1" style="left: -3px; color:#fff" > <i class="fa fa-check" aria-hidden="true" style="cursor:pointer;"></i> Cancelled </button>

													</div>
													<div v-else-if="buttonStatusSuccess.includes(template.templateId)">
														<button class="btn btn-success btn-sm my-1" style="left: -3px; color:#fff" > <i class="fa fa-check" aria-hidden="true" style="cursor:pointer;"></i> Success </button>

													</div>

													<div v-else class="text-center" >
														<img :src="`${base_url}/img/loader.gif`" style="width: 60px; height: 35px"/>
													</div>
												</div>
											</div>
										</div>
										<p>{{ template.content }}</p>
									</div>
								</div>
								</li>
							
								<li v-if="template.type == 2">
									<span class="time">{{ template.campaignScheduledTime }}</span>
									<span class="dot">
										<i class="fas fa-envelope" style="cursor:pointer;font-size: 18px;"></i>
									</span>
									<div class="content">
										<div :id = "`campaign-email-view-mode_${template.templateId}`">
											<div class = "row">
												<h3 class="subtitle col-lg-8">{{ template.templateTitle }}</h3>
												<div class="col-lg-4 text-right" :id = "`sms_email_task_templates_${template.runningEmailSmsScheduledId}`">
													<div v-if="!isCampaignExist.includes(template.templateId)">
														<button  class="btn btn-info btn-sm btn-sendnow my-1" style = "left: -3px; color:#fff"  @click="sendRunningCampaignEmailSmsTaskTemplate(template.login_agency_id,template.contact_id,template.template_id,template.runningEmailSmsScheduledId,2,template.campaign_id);">
														<i class="fa fa-paper-plane" aria-hidden="true" style="cursor:pointer;"></i>Send now
														</button>
													
														<button class="btn btn-danger btn-sm my-1" style="left: -3px; color:#fff" @click="cancelRunningCampaignEmailSmsTaskTemplate(template.login_agency_id,template.contact_id,template.template_id, template.runningEmailSmsScheduledId,2,template.contact_business_id,template.campaign_id);">
														<i class="fa fa-ban" aria-hidden="true" style="cursor:pointer;"></i> Cancel
														</button>
													</div>
													<div v-else-if="buttonStatusCancelled.includes(template.templateId)">
														<button class="btn btn-danger btn-sm my-1" style="left: -3px; color:#fff" > <i class="fa fa-check" aria-hidden="true" style="cursor:pointer;"></i> Cancelled </button>

													</div>
													<div v-else-if="buttonStatusSuccess.includes(template.templateId)">
														<button class="btn btn-success btn-sm my-1" style="left: -3px; color:#fff" > <i class="fa fa-check" aria-hidden="true" style="cursor:pointer;"></i> Success </button>

													</div>
													<div v-else class="text-center" >
														<img :src="`${base_url}/img/loader.gif`" style="width: 60px; height: 35px"/>
													</div>
													
												</div>
											</div>
											<p><strong>{{template.subject}}</strong></p>
											<p>{{template.content}}</p>
										</div>
									</div>
								</li>
								<li v-if="template.type == 3">
									<span class="time">{{ template.campaignScheduledTime }}</span>
									<span class="dot">
										<i class="fa fa-tasks" style="cursor:pointer;font-size: 18px;"></i>
									</span>
									<div class="content">
										<div :id = "`campaign-task-view-mode_${template.templateId}`">
											<div class = "row">
												<h3 class="subtitle col-lg-8">{{ template.templateTitle }}</h3>
												<div class="col-lg-4 text-right" :id = "`sms_email_task_templates_${template.runningEmailSmsScheduledId}`">
													<div v-if="!isCampaignExist.includes(template.templateId)">
														<button  class="btn btn-info btn-sm btn-sendnow my-1" style = "left: -3px; color:#fff"  @click="sendRunningCampaignEmailSmsTaskTemplate(template.login_agency_id,template.contact_id,template.template_id,template.runningEmailSmsScheduledId,3,template.campaign_id);">
														<i class="fa fa-paper-plane" aria-hidden="true" style="cursor:pointer;"></i>Send now
														</button>

														<button class="btn btn-danger btn-sm my-1" style="left: -3px; color:#fff" @click="cancelRunningCampaignEmailSmsTaskTemplate(template.login_agency_id,template.contact_id,template.template_id, template.runningEmailSmsScheduledId,3,template.contact_business_id,template.campaign_id);">
														<i class="fa fa-ban" aria-hidden="true" style="cursor:pointer;"></i> Cancel
														</button>
													</div>

													<div v-else-if="buttonStatusCancelled.includes(template.templateId)">
														<button class="btn btn-danger btn-sm my-1" style="left: -3px; color:#fff" > <i class="fa fa-check" aria-hidden="true" style="cursor:pointer;"></i> Cancelled </button>

													</div>
													<div v-else-if="buttonStatusSuccess.includes(template.templateId)">
														<button class="btn btn-success btn-sm my-1" style="left: -3px; color:#fff" > <i class="fa fa-check" aria-hidden="true" style="cursor:pointer;"></i> Success </button>

													</div>

													<div v-else class="text-center" >
														<img :src="`${base_url}/img/loader.gif`" style="width: 60px; height: 35px"/>
													</div>

												</div>
											</div>
											<p>{{ template.content }}</p>
										</div>
									</div>
								</li>

								<lI v-if="template.type == 4" >
									<span class="time">{{ template.campaignScheduledTime }}</span>
									<span class="dot">
										<i class="fas fa-exchange-alt" style="cursor:pointer;font-size: 18px;"></i>
									</span>
									<div class="content">
										<div :id = "`campaign-task-view-mode_${template.templateId}`">
											<div class = "row">
												<h3 class="subtitle col-lg-8">{{ template.templateTitle }}</h3>
												<div class="col-lg-4 text-right" :id = "`sms_email_task_templates_${template.runningEmailSmsScheduledId}`">
													<div v-if="!isCampaignExist.includes(template.templateId)">
														<button  class="btn btn-info btn-sm btn-sendnow my-1" style = "left: -3px; color:#fff"  @click="sendRunningCampaignEmailSmsTaskTemplate(template.login_agency_id,template.contact_id,template.template_id,template.runningEmailSmsScheduledId,4,template.campaign_id);">
														<i class="fa fa-paper-plane" aria-hidden="true" style="cursor:pointer;"></i>Send now
														</button>

														<button class="btn btn-danger btn-sm my-1" style="left: -3px; color:#fff" @click="cancelRunningCampaignEmailSmsTaskTemplate(template.login_agency_id,template.contact_id,template.template_id, template.runningEmailSmsScheduledId,4,template.contact_business_id,template.campaign_id);">
														<i class="fa fa-ban" aria-hidden="true" style="cursor:pointer;"></i> Cancel
														</button>
													</div>

													<div v-else-if="buttonStatusCancelled.includes(template.templateId)">
														<button class="btn btn-danger btn-sm my-1" style="left: -3px; color:#fff" > <i class="fa fa-check" aria-hidden="true" style="cursor:pointer;"></i> Cancelled </button>

													</div>
													<div v-else-if="buttonStatusSuccess.includes(template.templateId)">
														<button class="btn btn-success btn-sm my-1" style="left: -3px; color:#fff" > <i class="fa fa-check" aria-hidden="true" style="cursor:pointer;"></i> Success </button>

													</div>

													<div v-else class="text-center" >
														<img :src="`${base_url}/img/loader.gif`" style="width: 60px; height: 35px"/>
													</div>
													
												</div>
											</div>
											<p>{{template.transitionTemplateId}}</p>
										</div>
									</div>
								</li>
							
								</ul>
							</div>
							</div>
							<v-card-actions class="my-2">
							<v-spacer></v-spacer>
							<v-btn color="#29AD8E" text @click="runningCampaignList = false">
								Close
							</v-btn>
						</v-card-actions>
						</div>
					</v-card>
					</v-card>
				</v-dialog>
			</v-row>
		</template>
    <v-snackbar class="success-alert snackbarToast" v-model="snackbar" :timeout="timeout">
        <v-icon class="success-alert-icon pr-1">mdi-checkbox-marked-circle</v-icon>
        <span class="success-alert-text" v-if="campaignStartSnackbar">{{ campaignStartText }}</span>
        <span class="success-alert-text" v-if="campaignOffSnackbar">{{ campaignOffText }}</span>
        <span class="success-alert-text" v-if="campaignPauseSnackbar">{{ campaignPauseText }}</span>
        <span class="success-alert-text" v-if="campaignResumeSnackbar">{{ campaignResumeText }}</span>
        <span class="success-alert-text" v-if="campaignSendCancelSnackbar">{{ campaignSendCancelText }}</span>
    </v-snackbar>
	<v-snackbar class="success-alert" v-model="errorSnackbar" :timeout="timeout" color="red">
		<v-icon class="fail-alert-icon pr-1">mdi-close-circle</v-icon>
        <span class="fail-alert-text" v-if="campaignErrorSnackbar">{{ campaignErrorText }}</span>
    </v-snackbar>
    </div>
</script>

<script>
    Vue.component('<?=ComponentTools::componentName(__FILE__)?>', {
        template: '#<?=ComponentTools::templateName(__FILE__)?>',
        props: ['fieldData', 'objectId', 'contactId'],
		
        data: function(){
            return {
				base_url:base_url,
                fieldValue: '',
                campaignsListing:[],
                campaignHistoryListing:[],
                campaignUpcomingListing:[],
                campaignPauseListing:[],
				client_referrer_stage_text :'',
				referral_partner_img :this.base_url+'img/suit.png',
				dateTimeZone : '',
				dateTimeZoneStatus : '',
				activeCampaigndialog:false,
				dialog:false,
				activeCampaignId:'',
				pausedCampaignId:'',
				activeCampaignListing:[],
				pausedCampaignListing:[],
				contactPauseCampaignDialog:false,
				contactResumeCampaignDialog:false,
				resumeCampaignId:'',
				upcomingCampaginList:'',
				upcomingCampaignId:'',
				contactUpcomingCampaignDialog:false,
				contactPauseCampaignId:'',
				addCampaignDialog:false,
				contactAllCampaigns:[],
				savedcampaignId:'',
				campaignBtnStatus:false,
				appointment_campaign_start_flag:false,
				appointmentCampaignDialog:false,
				date: null,//(new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString(),
				menu1:false,
				time: null,
				appoitmentTime:[
					{name:"12:00am",value:"12:00am"},
					{name:"12:30am",value:"12:30am"},
					{name:"1:00am",value:"1:00am"},
					{name:"1:30am",value:"1:30am"},
					{name:"2:00am",value:"2:00am"},
					{name:"2:30am",value:"2:30am"},
					{name:"3:00am",value:"3:00am"},
					{name:"3:30am",value:"3:30am"},
					{name:"4:00am",value:"4:00am"},
					{name:"4:30am",value:"4:30am"},
					{name:"5:00am",value:"5:00am"},
					{name:"5:30am",value:"5:30am"},
					{name:"6:00am",value:"6:00am"},
					{name:"6:30am",value:"6:30am"},
					{name:"7:00am",value:"7:00am"},
					{name:"7:30am",value:"7:30am"},
					{name:"8:00am",value:"8:00am"},
					{name:"8:30am",value:"8:30am"},
					{name:"9:00am",value:"9:00am"},
					{name:"9:30am",value:"9:30am"},
					{name:"10:00am",value:"10:00am"},
					{name:"10:30am",value:"10:30am"},
					{name:"11:00am",value:"11:00am"},
					{name:"11:30am",value:"11:30am"},
					{name:"12:00pm",value:"12:00pm"},
					{name:"12:30pm",value:"12:30pm"},
					{name:"1:00pm",value:"1:00pm"},
					{name:"1:30pm",value:"1:30pm"},
					{name:"2:00pm",value:"2:00pm"},
					{name:"2:30pm",value:"2:30pm"},
					{name:"3:00pm",value:"3:00pm"},
					{name:"3:30pm",value:"3:30pm"},
					{name:"4:00pm",value:"4:00pm"},
					{name:"4:30pm",value:"4:30pm"},
					{name:"5:00pm",value:"5:00pm"},
					{name:"5:30pm",value:"5:30pm"},
					{name:"6:00pm",value:"6:00pm"},
					{name:"6:30pm",value:"6:30pm"},
					{name:"7:00pm",value:"7:00pm"},
					{name:"7:30pm",value:"7:30pm"},
					{name:"8:00pm",value:"8:00pm"},
					{name:"8:30pm",value:"8:30pm"},
					{name:"9:00pm",value:"9:00pm"},
					{name:"9:30pm",value:"9:30pm"},
					{name:"10:00pm",value:"10:00pm"},
					{name:"10:30pm",value:"10:30pm"},
					{name:"11:00pm",value:"11:00pm"},
					{name:"11:30pm",value:"11:30pm"},

				],
				appointment_time:'',
				appointment_items:'',
				campaignClickedItems:[],
				startCampaignLoader : [],
				base_url: base_url,
                campaignLoader:false,
                pauseLoader:false,
                pausedCampaign:[],
                resumeCampaign:[],
                snackbar:false,
				campaignStartSnackbar: false,
				campaignOffSnackbar: false,
				campaignPauseSnackbar: false,
				campaignResumeSnackbar: false,
				campaignErrorSnackbar: false,
				campaignErrorText: 'Campaign could not start!',
				errorSnackbar: false,
				campaignStartText:'Campaign started successfully!',
				campaignResumeText:'Campaign started successfully!',
				campaignOffText:'Campaign stopped successfully!',
				campaignPauseText:'Campaign paused successfully!',
                timeout: 3000,
				pendingCancellationCampaignDailog: false,
				menu2:false,
				cancellation_time:'',
				list: [],
				runningCampaignList: false,
				isCampaignExist:[],
				buttonStatusCancelled: [],
				buttonStatusSuccess: [],
				campaignSendCancelText: '',
                campaignSendCancelSnackbar : false,
                campaignTemplateId:'',
            }
        },
		filters: {
			noOfDays: function (date1,date2) 
			{
				
				var first_date = new Date(date1),
				month = '' + (first_date.getMonth() + 1),
				day = '' + first_date.getDate(),
				year = first_date.getFullYear();

				if (month.length < 2) month = '0' + month;
				if (day.length < 2) day = '0' + day;

				var firstDate =  [year, month, day].join(',');


				var second_date = new Date(date2),
				second_month = '' + (second_date.getMonth() + 1),
				second_day = '' + second_date.getDate(),
				second_year = second_date.getFullYear();

				if (second_month.length < 2) second_month = '0' + second_month;
				if (second_day.length < 2) second_day = '0' + second_day;

				var secondDate =  [second_year, second_month, second_day].join(',');

				const oneDay = 24 * 60 * 60 * 1000; // hours*minutes*seconds*milliseconds
				const finalfirstDate = new Date(firstDate);
				const finalsecondDate = new Date(secondDate);

				const diffDays = Math.round(Math.abs((finalfirstDate - finalsecondDate) / oneDay));
				
				return diffDays
			}
		},
		
        methods:{
            contactCampaignsListing: function(data)
			{
                var vm = this;
				if(data['data'])
				{
					vm.campaignsListing = (data['data']['data']['ContactCampaigns.getActiveContactCampaigns'][this.contactId]);
				}else{
					vm.campaignsListing = (data['ContactCampaigns.getActiveContactCampaigns'][this.contactId]);
				}
					
				  
			},
			contactCampaignHistoryListing: function(data)
			{
                var vm = this;
				if(data['data'])
				{
					vm.campaignHistoryListing = (data['data']['data']['ContactCampaigns.getCampaignHistoryContactCard'][this.contactId]);
				}else{
					
					vm.campaignHistoryListing = (data['ContactCampaigns.getCampaignHistoryContactCard'][this.contactId]);
				}
				 //vm.campaignHistoryListing = JSON.parse(data['ContactCampaigns.getCampaignHistoryContactCard'][this.contactId]);	
			},
			contactUpcomingCampaignsListing: function(data)
			{
                let vm = this;
				if(data['data'])
				{
					vm.campaignUpcomingListing = JSON.parse(data['data']['data']['ContactCampaigns.getUpcomingCampaignsContactCard'][this.contactId]);

				}else{
					vm.campaignUpcomingListing = JSON.parse(data['ContactCampaigns.getUpcomingCampaignsContactCard'][this.contactId]);	
				}
				
				 vm.upcomingCampaginList = vm.campaignUpcomingListing.list;
				 this.addTdInTable();
						
				 
				
			},
			contactPauseCampaignsListing: function(data)
			{
				//console.log("campaignPauseListing",data);return false;	 
                var vm = this;
				if(data['data'])
				{
					vm.campaignPauseListing = data['data']['data']['ContactCampaigns.getPausedCampaignListingContactCardNew'][this.contactId];	
				}else{
					vm.campaignPauseListing = data['ContactCampaigns.getPausedCampaignListingContactCardNew'][this.contactId];	
				}
				setTimeout(
					function() 
					{
                        vm.campaignPauseSnackbar = false;
                        vm.campaignOffSnackbar = false;
                        vm.campaignStartSnackbar = false;
                        vm.campaignResumeSnackbar = false;

					}, 3000);
				// vm.campaignPauseListing = JSON.parse(data['ContactCampaigns.getPausedCampaignListingContactCardNew'][this.contactId]);		
				 
			},
			
			showPausedCampaigns: function(campaign_id,contactId)
			{
				
				this.pausedCampaignId = campaign_id;
				DataBridge.get('ContactCampaigns.showPausedCampaigns', this.pausedCampaignId,'*', this.pausedCampaignModalListing);
			},

			pausedCampaignModalListing:function(data){
				var vm = this;
				vm.pausedCampaignListing = JSON.parse(data['ContactCampaigns.showPausedCampaigns'][this.pausedCampaignId]);
			},

			activeCampaignModalListing:function(data)
			{
				var vm = this;
				var campaignList = JSON.parse(data['ContactCampaigns.showRunningScheduleCampaigns'][this.activeCampaignId]);
				vm.list = campaignList.list;
				console.log('list',vm.list);
				vm.runningCampaignList = true;
				vm.activeCampaignListing = JSON.parse(data['ContactCampaigns.showRunningScheduleCampaigns'][this.activeCampaignId]);
			},

			showRunningScheduleCampaigns:function(campaign_id,contactId)
			{
				 console.log("campaign_id",campaign_id);
				// console.log("contactId",contactId);
				this.activeCampaignId = campaign_id;
				DataBridge.get('ContactCampaigns.showRunningScheduleCampaigns', this.activeCampaignId,'*', this.activeCampaignModalListing);
			},
			contactPauseCampaignButton:function(campaign_running_schedule_id,event)
			{
				this.contactPauseCampaignDialog = true;
				this.contactPauseCampaignId ='';
				this.contactPauseCampaignId = campaign_running_schedule_id;
			},
			contactPauseCampaign :function()
			{

                this.pausedCampaign.push(this.contactPauseCampaignId);
				this.contactPauseCampaignDialog = true;
				DataBridge.save('ContactCampaigns.contactPauseCampaign',this.contactPauseCampaignId,this.populateContactPauseCampaign);
			},
			populateContactPauseCampaign:function(response)
			{
				
				let vm = this;
				var result =  JSON.parse(response['data']['data']);
				if(result['status'] == true || result['status'] == 1)
				{
					this.contactPauseCampaignDialog = false;
					setTimeout(
					function() 
					{
						DataBridgeContacts.save('ContactCampaigns.getActiveContactCampaigns', vm.contactId,vm.contactCampaignsListing);
						DataBridgeContacts.save('ContactCampaigns.getCampaignHistoryContactCard', vm.contactId,vm.contactCampaignHistoryListing);
						DataBridgeContacts.save('ContactCampaigns.getUpcomingCampaignsContactCard', vm.contactId, vm.contactUpcomingCampaignsListing);
						DataBridgeContacts.save('ContactCampaigns.getPausedCampaignListingContactCardNew', vm.contactId, vm.contactPauseCampaignsListing);
                        vm.snackbar = true;
                        vm.campaignPauseSnackbar = true;
                        vm.campaignOffSnackbar = false;
                        vm.pausedCampaign=[];

					}, 3000);
					
				}
				else
				{
					swal({
						title: result['msg'] ,
					});
				}
			},
			contactResumeCampaign:function(campaign_resume_id)
			{
				this.contactResumeCampaignDialog = true;
				this.resumeCampaignId = campaign_resume_id;
				
			},
			saveContactResumeCampaign:function()
			{
                this.resumeCampaign.push(this.resumeCampaignId);
				DataBridge.save('ContactCampaigns.contactResumeCampaign',this.resumeCampaignId,this.populateContactResumeCampaign);
			},
			populateContactResumeCampaign:function(response)
			{
				
				let vm = this;
				var result =  JSON.parse(response['data']['data']);
				if(result['status'] == true || result['status'] == 1)
				{
					this.contactResumeCampaignDialog = false;
					setTimeout(
					function() 
					{

						DataBridgeContacts.save('ContactCampaigns.getActiveContactCampaigns', vm.contactId,vm.contactCampaignsListing);
						DataBridgeContacts.save('ContactCampaigns.getCampaignHistoryContactCard', vm.contactId,vm.contactCampaignHistoryListing);
						DataBridgeContacts.save('ContactCampaigns.getUpcomingCampaignsContactCard', vm.contactId, vm.contactUpcomingCampaignsListing);
						DataBridgeContacts.save('ContactCampaigns.getPausedCampaignListingContactCardNew', vm.contactId, vm.contactPauseCampaignsListing);
                        vm.snackbar = true;
                        vm.campaignResumeSnackbar = true;
                        vm.campaignOffSnackbar = false;
                        vm.resumeCampaign = [];
					}, 3000);
					
				}
				else
				{
					swal({
						title: result['msg'] ,
					});
				}
			},

			addTdInTable() 
			{
				let parentThis=this;
				
				var isTrue = 0; 
				setTimeout(
					function() 
					{
						
					const tableRow = document.querySelectorAll('.upcoming_campaign');
					tableRow.forEach(function (el,i) {
					  const td = document.createElement('td');
					  td.addEventListener('click', function handleClick(event) {
						 const tr = td.closest('tr');
						 parentThis.upcomingCampaignId ='';
						 parentThis.dateBirth ='';
						 parentThis.upcomingCampaignId =  tr['id'];
						 parentThis.dateBirth =  tr.getAttribute('data-birth-date');
						 
					 });
					  td.className = 'hidden';
					  td.style.cursor = 'pointer';
					  td.innerHTML = '<span class="mdi mdi-close"></span>cancel';
					  td.setAttribute('id','mm_'+i);
					  el.appendChild(td);
					  td.onclick = function () {
						  parentThis.contactUpcomingCampaignDialog = true;
							isTrue = 0;
					   }
						
					})
					}, 
				2000);
				
			},
			
			contactStopUpcomingCampaign:function(id)
			{
				
				var data = {
					'contact_id' : this.contactId,
					'agency_campaign_id' : this.upcomingCampaignId,
					'coming_birth_year' : this.dateBirth,
				
				};	
				DataBridge.save('ContactCampaigns.contactStopUpcomingCampaign', data ,this.populateContactStopUpcomingCampaign);
			},
			populateContactStopUpcomingCampaign:function(response)
			{
				var result =  JSON.parse(response['data']['data']);
				if(result['status'] == true || result['status'] == 1)
				{
					this.contactUpcomingCampaignDialog = false;
					setTimeout(
					function() 
					{
						let vm = this;
						DataBridgeContacts.save('ContactCampaigns.getActiveContactCampaigns', vm.contactId,vm.contactCampaignsListing);
						DataBridgeContacts.save('ContactCampaigns.getCampaignHistoryContactCard', vm.contactId,vm.contactCampaignHistoryListing);
						DataBridgeContacts.save('ContactCampaigns.getUpcomingCampaignsContactCard', vm.contactId, vm.contactUpcomingCampaignsListing);
						DataBridgeContacts.save('ContactCampaigns.getPausedCampaignListingContactCardNew', vm.contactId, vm.contactPauseCampaignsListing);
						vm.contactUpcomingCampaignDialog = false;
					}, 3000);
					
				}
				else
				{
					swal({
						title: result['msg'] ,
					});
				}
			},
			populateContactStopUpcomingCampaign:function(response)
			{
				var result =  JSON.parse(response['data']['data']);
				if(result['status'] == true || result['status'] == 1)
				{
					this.contactUpcomingCampaignDialog = false;
					setTimeout(
					function() 
					{
						let vm = this;
						DataBridgeContacts.save('ContactCampaigns.getActiveContactCampaigns', vm.contactId,vm.contactCampaignsListing);
						DataBridgeContacts.save('ContactCampaigns.getCampaignHistoryContactCard', vm.contactId,vm.contactCampaignHistoryListing);
						DataBridgeContacts.save('ContactCampaigns.getUpcomingCampaignsContactCard', vm.contactId, vm.contactUpcomingCampaignsListing);
						DataBridgeContacts.save('ContactCampaigns.getPausedCampaignListingContactCardNew', vm.contactId, vm.contactPauseCampaignsListing);
					}, 3000);
					
				}
				else
				{
					swal({
						title: result['msg'] ,
					});
				}
			},

			// add campaign to the contact card
			addToCampaignContactCard:function()
			{

				this.addCampaignDialog = true;
				DataBridge.get('ContactCampaigns.getAllAvailableCampaignsContactCard', this.contactId,'*', this.getAllCampaigns);
			},

			getAllCampaigns:function(response){
				console.log("cam",response);

				var vm = this;
				vm.contactAllCampaigns = JSON.parse(response['ContactCampaigns.getAllAvailableCampaignsContactCard'][this.contactId]);
                var entry = Object.entries(this.contactAllCampaigns.campaigns)
                var lastElement = entry.pop();
                entry.unshift(lastElement)
                this.contactAllCampaigns.campaigns = Object.fromEntries(entry);
			},
			startCampaignContactCard:function(items)
			{
				var campaignParams = {
					'campaign_id' : items.campaign_id,
					'contact_id' : items.contact_id,
					'pipeline_stage_id' : items.pipeline_stage,
					'campaign_type' : items.type,
					'con_cancellation_date' : '',
					'con_cancellation_time':''
				}
				this.appointment_items = campaignParams;
				if(items.type == _CAMPAIGN_TYPE_PENDING_CANCELLATION && this.appointment_campaign_start_flag==false){
					this.pendingCancellationCampaignDailog = true;
				}
				else if(items.type == _CAMPAIGN_TYPE_PIPELINE && items.pipeline_stage == _PIPELINE_STAGE_APPOINTMENT_SCHEDULED && this.appointment_campaign_start_flag==false)
				{ 
					this.appointmentCampaignDialog = true;

				}else{

					DataBridge.save('ContactCampaigns.startCampaignContactCard',campaignParams,this.startCampaign);
				}
			},

			startCampaign:function(response)
			{	
				let vm = this;
				var result =  JSON.parse(response['data']['data']);
                this.startCampaignLoader.push( result['start_campaign_id']);
				if(result['status'] == true)
				{
					
					if(result['message'])
                    {
                        this.campaignStartText = result['message'];
                    }
					this.savedcampaignId = result['start_campaign_id'];
					this.campaignBtnStatus = true;
					this.appointmentCampaignDialog = false;
					$("#left_content_submit_"+result['start_campaign_id']).html('');
					$("#left_content_submit_"+result['start_campaign_id']).removeClass('campaign-started-btn');
					$("#left_content_submit_"+result['start_campaign_id']).addClass('campaign-started-btn');
					$("#left_content_submit_"+result['start_campaign_id']).html('<i aria-hidden="true" class="v-icon notranslate mdi mdi-check theme--light" style="color:#fff"></i> CAMPAIGN STARTED');
					$("#left_content_submit_"+result['start_campaign_id']).attr('disabled',true);
					// DataBridge.get('ContactCampaigns.getActiveContactCampaigns', this.contactId,'*', this.contactCampaignsListing);
					// DataBridge.get('ContactCampaigns.getCampaignHistoryContactCard', this.contactId,'*', this.contactCampaignHistoryListing);
					// DataBridge.get('ContactCampaigns.getUpcomingCampaignsContactCard', this.contactId,'*', this.contactUpcomingCampaignsListing);
					// DataBridge.get('ContactCampaigns.getPausedCampaignListingContactCardNew', this.contactId,'*', this.contactPauseCampaignsListing);

					setTimeout(
					function()
					{

						DataBridgeContacts.save('ContactCampaigns.getActiveContactCampaigns', vm.contactId,vm.contactCampaignsListing);
						DataBridgeContacts.save('ContactCampaigns.getCampaignHistoryContactCard', vm.contactId,vm.contactCampaignHistoryListing);
						DataBridgeContacts.save('ContactCampaigns.getUpcomingCampaignsContactCard', vm.contactId, vm.contactUpcomingCampaignsListing);
						DataBridgeContacts.save('ContactCampaigns.getPausedCampaignListingContactCardNew', vm.contactId, vm.contactPauseCampaignsListing);
						vm.addCampaignDialog = false;
                        vm.snackbar = true;
                        vm.campaignStartSnackbar = true;
                        vm.campaignOffSnackbar = false;
                        vm.startCampaignLoader = [];
					}, 3000);
					
				}else{

					if(result['code'] == '27' || result['code'] == 27)
					{ 
                        var msg="Campaign start once per day";
                    }else{
						console.log("else3");
                        var msg="Failed!";
                        if(result['message'] != undefined && result['message'] != "")
                        { 
                            msg = result['message'];
                        }
                    }
					console.log("mess",msg);
					setTimeout(function()
					{
						vm.addCampaignDialog = false;
						vm.snackbar = false;
						vm.errorSnackbar = true;
						vm.campaignErrorSnackbar = true;
						vm.campaignErrorText = 'Campaign could not start. ' + msg + '!';
						vm.startCampaignLoader = [];
					}, 3000);
					// $("#left_content_submit_"+result['start_campaign_id']).html('');
                    // $("#left_content_submit_"+result['start_campaign_id']).html(msg);
					// $("#left_content_submit_"+result['start_campaign_id']).css({color: 'red'});
					// $("#left_content_submit_"+result['start_campaign_id']).attr('disabled',true);
				}
			},
			startPendingCancellationContactCard: function(){
				this.appointment_items;
				if(this.cancellation_time == "" || this.cancellation_time == undefined || this.cancellation_time == null || this.appoitmentDateFormatted == "" || this.appoitmentDateFormatted == undefined || this.appoitmentDateFormatted == null){
					swal("Warning", "Please choose the cancellation date and time.", "error");
				}else{
					this.appointment_items['con_cancellation_date'] = this.appoitmentDateFormatted;
					this.appointment_items['con_cancellation_time'] = this.cancellation_time;
					this.pendingCancellationCampaignDailog = false;
					DataBridge.save('ContactCampaigns.startCampaignContactCard',this.appointment_items,this.startCampaign);
 				}
			},
			saveAppointmentDateContactCard:function(){

				var appointmentData = {
					'con_appointment_date' : this.date,
					'con_appointment_time' : this.appointment_time,
					'contact_id': this.contactId
				}
				DataBridge.save('ContactCampaigns.saveAppointmentDateContactCard',appointmentData,this.appointmentCampaignResult);

			},

			appointmentCampaignResult:function(response)
			{

				var result =  JSON.parse(response['data']['data']);

				if(result['status'] == true){
					DataBridge.save('ContactCampaigns.startCampaignContactCard',this.appointment_items,this.startCampaign);
				}

			},
			contactStopCampaignButton:function(campaign_running_schedule_id)
			{
				var vm = this;
				swal({
						title: "Are you sure to turn off this campaign?",
						text: "All scheduled campaigns email/sms/task will mark as canceled.",
						type: "warning",
						showCancelButton: true,
						confirmButtonColor: "#DD6B55",
						confirmButtonText: "Yes, change it!",
						cancelButtonText: "Cancel",
						closeOnConfirm: true
					},
					function(isConfirm) {
						if (isConfirm) {
							var campaignStopData = {
								'campaign_running_schedule_id' : campaign_running_schedule_id,
							}
							DataBridge.save('ContactCampaigns.stopCampaignContactCard',campaignStopData,vm.stopContactCardCampaign);
						}
					}
				);

			},
			stopContactCardCampaign:function(response)
			{
				var result =  JSON.parse(response['data']['data']);
				if(result['status'] == true) {
					let vm = this;
					this.campaignClickedItems.push(result['campaign_running_schedule_id']);
					DataBridgeContacts.save('ContactCampaigns.getActiveContactCampaigns', vm.contactId,vm.contactCampaignsListing);
					DataBridgeContacts.save('ContactCampaigns.getCampaignHistoryContactCard', vm.contactId,vm.contactCampaignHistoryListing);
					DataBridgeContacts.save('ContactCampaigns.getUpcomingCampaignsContactCard', vm.contactId, vm.contactUpcomingCampaignsListing);
					DataBridgeContacts.save('ContactCampaigns.getPausedCampaignListingContactCardNew', vm.contactId, vm.contactPauseCampaignsListing);
                            vm.snackbar = true;
                            vm.campaignOffSnackbar = true;
				}
				else
				{
					swal("Warning", "Something went wrong.", "error");
				}
			},
			cancelRunningCampaignEmailSmsTaskTemplate:function(login_agency_id,contact_id,template_id, runningEmailSmsScheduledId,templateType,contact_business_id=null,campaign_id=null){
                this.isCampaignExist.push(template_id);
                this.campaignSendCancelSnackbar = false;
                this.campaignSendCancelText = "";
				var campaignParams = {
					'agency_id' : login_agency_id,
					'contact_id' : contact_id,
					'scheduledEmailSmsID' : runningEmailSmsScheduledId,
					'scheduledEmailSmsType' : templateType,
					'campaign_id' : campaign_id,
				}
				DataBridgeContacts.save('ContactCampaigns.cancelRunningCampaignEmailSmsTaskTemplate', campaignParams,this.updateContactCampaignsListAfterCancel);
                this.campaignTemplateId = template_id;
			},
			sendRunningCampaignEmailSmsTaskTemplate:function(login_agency_id,contact_id,template_id,runningEmailSmsScheduledId,templateType,campaign_id){
				this.isCampaignExist.push(template_id);
                this.campaignSendCancelSnackbar = false;
                this.campaignSendCancelText = "";
				var campaignParams = {
					'agency_id' : login_agency_id,
					'template_id' : template_id,
					'scheduledEmailSmsID' : runningEmailSmsScheduledId,
					'scheduledEmailSmsType' : templateType,
					'campaign_id' : campaign_id,
					'contact_id' : contact_id,
				}
				DataBridgeContacts.save('ContactCampaigns.sendRunningCampaignEmailSmsTaskTemplate', campaignParams,this.updateContactCampaignsListAfterSend);
                this.campaignTemplateId = template_id;
			},
			updateContactCampaignsListAfterSend:function(response){
				let vm = this;
				if(response['data']['status'] == 1){
                    this.buttonStatusSuccess.push(this.campaignTemplateId);
                    this.snackbar = true;
                    vm.campaignSendCancelSnackbar = true;
                    vm.campaignSendCancelText = "Campaign sent successfully.";
                    if(!$('.btn-sendnow').length){
                        window.location.reload();
                    }
				}
                else if(response['data']['status'] == 0){
                    this.snackbar = false;
					this.isCampaignExist.pop();
                    swal("Warning", "SMS capabilities are currently turned off.", "error");
                }
			},
            updateContactCampaignsListAfterCancel:function(response){
                let vm = this;
                if(response['data']['status'] == 1){
                    this.buttonStatusCancelled.push(this.campaignTemplateId);
                    this.snackbar = true;
                    vm.campaignSendCancelSnackbar = true;
                    vm.campaignSendCancelText = "Campaign cancelled successfully.";
				}
                if(!$('.btn-sendnow').length){
                    window.location.reload();
                }
            }
        },

		computed:{
			appoitmentDateFormatted :function () {

				if(this.date == '' || this.date == 'null' || this.date == null){

				}else{
					const appointmentDate = new Date(this.date);

					return `${appointmentDate.toLocaleDateString("en-US", {
					"day": "numeric",
					"year": "numeric",
					"month": "long",
					"timeZone": "UTC" 
				
					})}`;
				}

			}
		},
	 
        beforeMount: function()
		{
			
            DataBridge.get('ContactCampaigns.getActiveContactCampaigns', this.contactId,'*', this.contactCampaignsListing);
            DataBridge.get('ContactCampaigns.getCampaignHistoryContactCard', this.contactId,'*', this.contactCampaignHistoryListing);
            DataBridge.get('ContactCampaigns.getUpcomingCampaignsContactCard', this.contactId,'*', this.contactUpcomingCampaignsListing);

			DataBridge.get('ContactCampaigns.getPausedCampaignListingContactCardNew', this.contactId,'*', this.contactPauseCampaignsListing);
			this.$root.$on('contact_x_date_stop', (data) => {
                if(data == 1)
                {
					DataBridgeContacts.save('ContactCampaigns.getActiveContactCampaigns', this.contactId, this.contactCampaignsListing);
					DataBridgeContacts.save('ContactCampaigns.getCampaignHistoryContactCard', this.contactId, this.contactCampaignHistoryListing);
					DataBridgeContacts.save('ContactCampaigns.getUpcomingCampaignsContactCard', this.contactId, this.contactUpcomingCampaignsListing);
					DataBridgeContacts.save('ContactCampaigns.getPausedCampaignListingContactCardNew', this.contactId, this.contactPauseCampaignsListing);
                }
             });
        }
    });
</script>