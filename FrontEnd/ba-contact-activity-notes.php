<?php

use ComponentLibrary\Lib\ComponentTools;
//require_once(\ComponentLibrary\Lib\ComponentLibrary::componentPath("ba-common"));
?>
<link href="<?= SITEURL ?>js/quilljs/quill.snow.css" rel="stylesheet">
<script src="<?= SITEURL ?>js/quilljs/quill.min.js"></script>

<style>
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

	.success-alert-icon {
		color: #5FB322 !important;
	}

	.v-snack {
		left: 39% !important;
	}

	.success-alert-text {
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
		padding: 14px 0px 14px 8px !important;


	}

	.v-card__title.title-common-background span {
		text-transform: none;
	}

	.firstDate {
		position: absolute;
		top: -45px;
	}

	#editor-container {
		height: 375px;
	}

	.editor-wrapper {
		position: relative;
	}

	.ql-toolbar {
		position: absolute;
		bottom: 0;
		width: 100%;
		transform: translateY(100%);
	}

	.addtaskeditor {
		height: 150px;
		width: 848px;
		border-radius: 6px;
	}

	.ql-snow.ql-toolbar button,
	.ql-snow .ql-toolbar button {
		border: 1px solid #E7E7E8 !important;
		border-radius: 5px !important;
		width: 30px !important;
		margin-right: 5px !important;
	}

	.ql-container {
		font-family: 'Roboto' !important;
		font-size: 14px !important;
		letter-spacing: 0.15px !important;
		font-weight: 400 !important;
	}

	.v-menu__content.theme--light.menuable__content__active {
		max-height: 250px !important;
	}

	.v-date-picker-table {  
    height: 202px;
}
	.ql-toolbar.ql-snow .ql-formats {
		margin: 0 !important;
	}

	.ql-toolbar.ql-snow .ql-formats:first-child {
		margin-left: 10px !important;
	}

	.v-slect-label label.v-label.theme--light {
		font-size: 13px;
		font-weight: 500;
	}

	.attach-icon-notes {
		font-size: 15px !important;
		border: 1px solid #E7E7E8;
		padding: 5px;
		border-radius: 6px;
		width: 25px;
		height: 25px;
		z-index: 100;
		left: 425px;
		bottom: -55px;
	}

	.ql-toolbar.ql-snow .ql-formats {
		margin: 0 !important;
	}

	div#quill_editor {
 
    width: 100%;
}
@media screen and (min-width: 764px) and (max-width: 1424px) {
    #quill_editor .ql-editor {
        width: 100% !important;
    }
}

@media screen and (min-width: 1450px) {
	button.v-icon.notranslate.attach-icon-notes.v-icon--link.mdi.mdi-paperclip.theme--light {
		margin-left: -56px;
	}
}
@media screen and (min-width: 1250px) and (max-width: 1450px) {
	button.v-icon.notranslate.attach-icon-notes.v-icon--link.mdi.mdi-paperclip.theme--light {
		left: 113px;
    	bottom: -82px;
	}
}
@media screen and (min-width: 1130px) and (max-width: 1200px) {
	button.v-icon.notranslate.attach-icon-notes.v-icon--link.mdi.mdi-paperclip.theme--light {
		margin-left: -301px;
    	margin-bottom: 2px;
	}
}
@media screen and (min-width: 885px) and (max-width: 1130px) {
	button.v-icon.notranslate.attach-icon-notes.v-icon--link.mdi.mdi-paperclip.theme--light {
		margin-left: -305px;
        margin-bottom: -46px;
	}
}

/* @media screen and (min-width: 764px) and (max-width: 1424px) {
	#quill_editor .ql-editor  {
		width: 480px !important;
	}
} */
	.v-slect-label label.v-label.theme--light {
		font-size: 13px;
		font-weight: 500 !important;
	}

	.note-pin-icon{
		cursor: pointer;
	}
	.note-pin-icon.active{
		color: #29AD8E;
	}
	.pin-note-section{
		margin-top: 60px;
		padding-right: 10px;
	}
	.pin-note-section .pin-note{
		margin-bottom: -25px;
	}
	.pin-note-section .note-section{
		margin-bottom: -75px !important;
	}

	.note-tab .overview-first-date {
		position: sticky;
		top: 0px;
		width: -webkit-fill-available;
		z-index: 1;
		background-color: #e0e0e0;
	}
	.fixHead {
		position: absolute !important;
	}
	.first-card {
		margin-top: 50px;
	}
	.category-notes{ top: 610px !important;	}
    .category-edit-notes{ top: 485px !important;}
.position-unset {
    position: unset !important;
}

.v-select.v-input--dense .v-select__selection--comma {
    margin: 5px 0px 3px 0;
}
.v-text-field.v-text-field--enclosed:not(.v-text-field--rounded)>.v-input__control>.v-input__slot {
    padding: 0px 10px !important;
}

</style>

<script type="text/x-template" id="<?= ComponentTools::templateName(__FILE__) ?>">
	<div>
        <div class="">
            <div v-if="newNote">
                <v-card>
                    <v-card-subtitle class="d-flex">
                         <h4>New Note</h4>
                         <h4 class="ml-auto" @click="newNoteTab">X</h4>
                    </v-card-subtitle>
                    <v-card-text>
                        <v-textarea
                            name="input-7-1"
                            placeholder="Start typing here..."
                            hint="Hint text"
                            data-trigger=""
                            >
                        </v-textarea>
                        <v-btn color="">
                            Save
                        </v-btn>
                        <v-icon color="grey">mdi-paperclip</v-icon>
                        <template >
                            <v-icon class="float-right p-1" color="grey">mdi-delete</v-icon>
                            <v-icon class="float-right p-1" color="grey">fas fa-thumbtack</v-icon>
                            <v-icon class="float-right p-1" color="grey">fas fa-ellipsis-v</v-icon>
                        </template>

                    </v-card-text>
                </v-card>
            </div>

            <v-col md="12" v-if="editNoteCard" class="edit-note-module-design pl-0">
                <v-card
                elevation="2"
                outlined
                >

                <v-card-title class="title-common-background justify-content-between">
                   <h4 class="m-0"> Edit Note</h4>

                    <span class="w-auto">Editing is limited to changing note and policy selection</span>
                    <div class="cross-icon position-unset" v-if="noteSaveBtn"><a text><v-icon @click="closeEditNote">mdi-close</v-icon></a></div>
                    <div class="cross-icon position-unset" v-else><a text><v-icon @click="closeEditState">mdi-close</v-icon></a></div>

                </v-card-title>
                <v-card-text disabled class="common-edit text">{{editNoteData.new_note}}</v-card-text>
                <v-card-actions class="note_policy_btn">
                <v-col cols="8" sm="8"></v-col>
                    <v-col cols="2" sm="2">
                        <v-select class="v-slect-green v-slect-label category-notes-input"
                        v-model = editNoteData.note_type_id
                        :items = "notesTypeListing"
                        item-text="note_type"
                        item-value="id"
						label="TYPE"
                        dense
                        outlined
                        @change="enableSaveBtn"
                        :menu-props="{ top: false, offsetY: true, contentClass: 'category-edit-notes'}"
                        >
                             <template v-slot:item="{ item }" style="top: 478px;">
                               <span class="select-option-height"> {{ item.note_type}} </span>
                             </template>
                        </v-select>
                    </v-col>

                    <v-col cols="2" sm="2">
                        <v-select class="v-slect-green v-slect-label category-notes-input"
                        v-model = "editNoteData.note_type"
                        :items=policyNotesTypeListing
                        item-text="name"
                        item-value="id"
						label="POLICY"
                        dense
                        outlined
                        @change="enableSaveBtn"
                        :menu-props="{ top: false, offsetY: true, left: true , contentClass: 'category-edit-notes'}"
                        >
                             <template v-slot:item="{ item }">
                                 <span class="select-option-height" >{{ item.policy_type}} <span class="select-option-policy-number"  v-if="item.policy_number"> {{item.policy_number}}</span></span>
                             </template>

                        </v-select>
                    </v-col>

                    <v-spacer></v-spacer>
                </v-card-actions>
                <v-divider class="mb-0"></v-divider>
                <v-card-actions>
                    <v-col md="2" v-if="notesLoader">
                        <img :src="`${base_url}/img/loader.gif`" style="width: 60px; height: 35px" />
                    </v-col>
                    <v-btn v-if = "noteSaveBtn && !notesLoader" class="btn-save-create-service" @click = "saveNote">
                        Save
                    </v-btn>
                    <v-btn v-else-if="!noteSaveBtn && !notesLoader" :disabled="disabled" >
                        Save
                    </v-btn>
                </v-card-actions>
                </v-card>
            </v-col>

            <!-- Add Note Starts-->
            <v-col md="12" v-if="addNoteldialog" class="edit-note-module-design pl-0">
			<p class="error-msg w-100 d-block mt-3 mb-2" style = 'text-align: center;'>{{ imageError }}</p>
                <v-card
                elevation="2"
                outlined
                >

                <v-card-title class="title-common-background">
                    New Note

                    <span></span>
                    <div class="cross-icon"><a text><v-icon @click="closeAddNote">mdi-close</v-icon></a></div>
                </v-card-title>

                <v-row no-gutters align="center">
                <v-col style="padding-bottom: 20px;">
                    <div :id="id" class = "addtaskeditor"  @keyup.delete = "disbleAddNoteBtn();"> </div>
                    <v-icon class="attach-icon-notes" @click="onPickFile">mdi-paperclip</v-icon>
                </v-col>
                </v-row>

                <v-card-actions class="edit-task-bottom-dropdown">
                <v-col cols="2" sm="2" class="p-1">
                    <input type="file" style="display:none" ref="fileInput" accept="file/*" @change="onFilePicked" multiple>
                </v-col>

                <v-col cols="6" sm="6"></v-col>
                    <v-col cols="2" sm="2">
                        <v-select class="v-slect-green v-slect-label category-notes-input"
                        :items="noteTypes"
                        item-text="note_type"
                        item-value="id"
                        v-model="selectedNoteType"
						label="TYPE"
                        dense
                        outlined
                        :menu-props="{ top: false, offsetY: true, contentClass: 'category-notes'}"
                        >

                        </v-select>
                    </v-col>

                    <v-col cols="2" sm="2">
                        <v-select class="v-slect-green v-slect-label category-notes-input"
                        :items="policyTypes"
                        item-text="name"
                        item-value="id"
                        label="POLICY"
                        v-model="selectedPolicyType"
                        dense
                        outlined
                        :menu-props="{ top: false, offsetY: true, left: true, contentClass: 'category-notes'}"
                        >
                            <template v-slot:item="{ item }">
                                <span class="select-option-height">{{ item.policy_type}} <span class="select-option-policy-number" v-if="item.policy_number">{{item.policy_number}}</span></span>
                             </template>
                        </v-select>
                    </v-col>
                </v-card-actions>
                <v-chip
                    v-for="(attachmentLists, index,key) in attachmentAppendLists"
                    class=""
                    close
                    @click:close = "removeChip(index, attachmentLists.upload_id)"
                    >
                    {{attachmentLists.display_name}}
                </v-chip>
                <v-card-actions>
                    <v-col md="2" v-if="notesLoader">
                        <img :src="`${base_url}/img/loader.gif`" style="width: 60px; height: 35px" />
                    </v-col>
                    <v-btn v-if = "noteAddBtn && !notesLoader" class="btn-save-create-service" @click = "addNote">
                        Save
                    </v-btn>
                    <v-btn v-else-if="!noteAddBtn && !notesLoader" :disabled="disabled" >
                        Save
                    </v-btn>
                </v-card-actions>
                </v-card>
            </v-col>
            <!-- Add Note Ends-->

            <v-row >
                <v-col class="ml-auto search-position"
                        cols="13"
                        sm="3"
                        md="3"
                        v-if="note_btn"
                    >
                        <v-text-field
                            class=""
                            v-model = "keyword"
                            :append-icon="keyword!='' && showCross == 1? 'mdi-close' : ''"
                            placeholder="Search Notes"
                            v-on:keyup = "searchNotes"
                            @click:append="clearSearchBar"
                            outlined
                            dense
                        ></v-text-field>
                        <v-progress-circular :value="100" v-if="searchInProgress" class="search-progress"></v-progress-circular>
                    </v-col>
                    <v-btn v-if="note_btn" class="ml-auto btn btn-outline-success btn-round btn-position" style="background-color:transparent;"  @click = "addNewNoteDialog()">
                        <v-icon class="plus-icon-css">mdi-plus</v-icon><span class="text-success">New Note</span>
                    </v-btn>
            </v-row>

            <div class="pin-note-section">
                <v-row v-if="pinnedNotes.length > 0">
                    <!-- <h4 class="contact-date pl-0 mb-5 first-date-position overview-first-date" >Pinned Notes</h4> -->
                    <v-col md="12" class="pin-note note-section" v-for="(note) in pinnedNotes" style="margin-top: 71px;">
                        <v-card class="pa-2 note_list" style="border-left: 8px solid #98DCFF; padding:12px;" :id = "'note_cards_' + note.id" >
                            <v-icon class="custom-fa-icon">mdi-text</v-icon>
                            <span class="ml-3 mt-2 text-policy">
                                <b>{{note.contact_note_type.note_type}} Note</b>
                                <span v-if="note.policy_number !='' && note.opportunity_id != '' ">
                                    <b>, {{ note.policy_number }}</b>
                                </span> by {{ note.user.first_name |capitalizedName(note.user.first_name)}} {{ note.user.last_name|capitalizedName(note.user.last_name)}}
                            </span>
                            <span class="ml-auto email_created" style="float:right">{{note.dateMonth}} at {{note.agencyTime}} {{note.time_zone}}</span>
                            <p class="mt-5 pl-8  text-color-custom" v-html="note.note"> </p>
                            <span class="text-right m-0 " style="float:right"><v-icon class="ml-auto icon-edit-common" small @click = "getNoteEdit(note.id)">mdi-pencil</v-icon></span>
                            <span class="text-right m-0" style="float:right" v-if="note.contact_notes_attachments.length > 0">
                                <v-icon style="font-size:18px; padding-right:5px;" v-on:click="notesdialog = true;showNotesAttachments(note.id);"> mdi-attachment</v-icon>
                            </span>
                            <p class="text-right m-0 pin-icon">
                                <v-icon class="ml-auto note-pin-icon" style="padding:6px" :class="{active: note.pinned_active}" v-on:click="pinNoteTop(note.id)" small>fas fa-thumbtack</v-icon>
                            </p>
                        </v-card>
                    </v-col>
                </v-row>
            </div>

           <div class="custom-grey">

            <div class="scroll-container pt-0 notes-tab-max-height" style="margin-top: 71px;" @scroll="loadMoreNotes($event)" ref="noteLists">


                <v-row no-gutters v-if="!initialNoteLoader" v-for="(notes, index,key) in notesListing" :key="`${index}`" class="note-tab">
                    <header class="overview-first-date w-100" :class="{ 'fixHead': key === 0 }"> <!-- overview-first-date -->
                        <h4 v-if="notes.length > 0" class="contact-date pl-0 mb-5 first-date-position overview-first-date" :class="{ 'firstDate': key === 0 }">{{ index | replace('-',' ') }}</h4> <!-- :class="{ 'firstDate': key === 0 }" -->
                    </header>
                    <v-col
                        md="12"
                        :class="{ 'first-card': note['indexElement'] == 0 }"
                        v-for="(note) in notes"
                        class="col-12"
                    >
                        <v-card
                        class="pa-2 note_list"
                        style="border-left: 8px solid #98DCFF; padding:12px;"
                        :id = "'note_cards_' + note.id"
                        >
                            <v-icon class="custom-fa-icon">mdi-text</v-icon>

                                <span class="ml-3 mt-2 text-policy"><b>{{note.contact_note_type.note_type}} Note</b><span v-if="note.policy_number !='' && note.opportunity_id != '' "><b>, {{ note.policy_number }}</b></span> by {{ note.user.first_name |capitalizedName(note.user.first_name)}} {{ note.user.last_name|capitalizedName(note.user.last_name)}}</span>
                                <span class="ml-auto email_created" style="float:right">{{note.dateMonth}} at {{note.agencyTime}} {{note.time_zone}}</span>
                                <p class="mt-5 pl-8  text-color-custom" v-html="note.note"> </p>
                                <span class="text-right m-0 " style="float:right"><v-icon class="ml-auto icon-edit-common" small @click = "getNoteEdit(note.id)">mdi-pencil</v-icon></span>
                                <span class="text-right m-0" style="float:right" v-if="note.contact_notes_attachments.length > 0"><v-icon style="font-size:18px; padding-right:5px;" v-on:click="notesdialog = true;showNotesAttachments(note.id);notesAttachment = '';"> mdi-attachment</v-icon></span>
                                <p class="text-right m-0 pin-icon"><v-icon class="ml-auto note-pin-icon" style="padding:6px" :class="{active: note.pinned_active}" v-on:click="pinNoteTop(note.id)" small>fas fa-thumbtack</v-icon></p>

                        </v-card>
                    </v-col>
                </v-row>
                <v-row v-if="notesListing===null || notesListing.length===0 && !initialNoteLoader">
                    <v-col
                        md="12"
                        class="mb-5"
                    >

                        <div class="pl-7 pr-7 pt-7 pb-7 v-card v-sheet theme--light"><p class="text-center"> No records found. </p></div>

                    </v-col>

                </v-row>

                <v-row v-if="initialNoteLoader">
                    <v-col md="12">
                        <div class="pl-7 pr-7 pt-7 pb-7 v-card v-sheet theme--light">
                            <p class="text-center">
                                <img :src="`${base_url}/img/loader.gif`" style="width: 60px; height: 35px" />
                            </p>
                        </div>
                    </v-col>
                </v-row>

            </div>
         </div>

            <template>
                <v-row justify="center">
                    <v-dialog
                    v-model="closeDialog"
                    max-width="750"
                    >

                        <v-card style="min-height:210px;">
                            <v-card-title class="text-h5 close-new-note-title">
                                Close New Note
                            </v-card-title>
                            <v-card-text class="close-new-note-text">There are unsaved changes. If you would like to save changes, press the 'Keep Editing' button.</v-card-text>
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
                    <!----------------- note Attachment Modal  ------------------->
                <v-dialog v-model="notesdialog" max-width="50%">
                    <v-card>
                        <v-card-title class="text-h5">
                            <h5 class="modal-title">VIEW ATTACHMENTS</h5>
                            <div class="cross-icon"><v-btn text @click="notesdialog = false; notesAttachment = ''"><v-icon>mdi-close</v-icon></v-btn></div>
                        </v-card-title>

                        <v-card-text>

                                <div id="view-attachment" class="tab-pane fade in"
                                    style="opacity:1; overflow-y: scroll;max-height: 500px;">

                                    <table class="table table-striped custom-table-style attach_notes">
                                        <thead>


                                        <tr id="table_listing">
                                        <th id="multiple_notes_check"></th>
                                            <th>File Name</th>
                                            <th>User</th>
                                            <th>Create Date</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody class="table_list uploaded_files_list_notes" v-html="notesAttachment.contact_notes_attachment_list">
                                        </tbody>
                                    </table>
                                </div>

                        </v-card-text>

                        <v-card-actions>
                            <v-spacer></v-spacer>

                            <v-btn color="#29AD8E" text @click="notesdialog = false; notesAttachment = ''">
                                Close
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>
                <!----------------- End note Attachment Modal  ------------------->


            <v-snackbar class="success-alert"

                    v-model="snackbar"
                    :timeout="timeout"
                >
                    <v-icon class="success-alert-icon pr-1">mdi-checkbox-marked-circle</v-icon>
                    <span class="success-alert-text" v-if="NoteUpdatedStatus">{{ noteUpdateMsg }}</span>
                    <span class="success-alert-text" v-else-if="NoteSavedStatus">{{ NoteSaveSuccessText }}</span>


            </v-snackbar>
        </div>
    </div>
   </div>
</script>

<script>
	Vue.component('<?= ComponentTools::componentName(__FILE__) ?>', {
		template: '#<?= ComponentTools::templateName(__FILE__) ?>',
		props: ['fieldData', 'objectId', 'contactId', 'value','initialModal', 'tab'],
		data: function() {
			return {
				base_url: base_url,
				noteEdit: {
					note: null,
				},
				newNote: false,
				fieldValue: '',
				notesListing: [],
				editNoteCard: false,
				note_btn: true,
				notesTypeListing: [],
				policyNotesTypeListing: [],
				note_id: '',
				editNoteData: [],
				closeDialog: false,
				disabled: true,
				closeAddDialog: false,
				noteSaveBtn: false,
				snackbar: false,
				timeout: 3000,
				noteUpdateMsg: 'Note edits successfully saved!',
				addNoteldialog: false,
				selectedPolicyType: '',
				selectedNoteType: '',
				noteTypes: [],
				policyTypes: [],
				noteAddBtn: false,
				addedNote: '',
				NoteSavedStatus: false,
				NoteSaveSuccessText: 'Note added successfully saved!',
				NoteUpdatedStatus: false,
				id: 'quill_editor',
				dialog: false,
				showAddNew: true,
				quill: '',
				notesdialog: false,
				notesAttachment: "",
				attachmentNoteId: "",
				attachmentAppendLists: [],
				imageError: '',
				imageUrl: '',
				image: [],
				uploadedAttachmentFiles: [],
				noteId: '',
				notesLoader: false,
				resetEditNoteModal: false,
				resetPolicyTypeVal: [],
				contactIdNext: "",
				pinnedNotes: [],
				keyword: '',
				searchInProgress:false,
				showCross:0,
				offset:0,
				limit:20,
				start:1,
				reachEnd : false,
				scrollLoader:false,
                initialNoteLoader: false,
			}
		},
		computed: {
			virtualValue: {
				get() {
					return this.value
				},
				set(virtualValue) {
					this.$emit('input', virtualValue)
				}
			}
		},
		filters: {
			replace: function(st, rep, repWith) {
				const result = st.split(rep).join(repWith)
				return result;
			}
		},
		methods: {
			populateNotesListing: function(data) {
				var vm = this;
				if(data['data'])
				{
					vm.notesListing = (data['data']['data']['NotesListing.getNotes'][this.contactId]);
				}
				else
				{
					vm.notesListing = (data['NotesListing.getNotes'][this.contactId]);
				}

				this.pinnedNotes = [];
				let i = 0;
				for(let month in this.notesListing) {
					const noteData = [];
					this.notesListing[month].forEach(element => {

						if(element.pinned == 2){
							element.pinned_active = true;
							this.pinnedNotes.push(element);
						}else{
						    element.indexElement = i++;
							noteData.push(element);
						}
					});
					this.notesListing[month] = noteData;
				}
                this.initialNoteLoader = false;
				setTimeout(() => {
					vm.NoteUpdatedStatus = false;
					vm.NoteSavedStatus = false;
				}, 3000);
								
			},
			newNoteTab() {
				this.newNote = !this.newNote;
			},
			getNoteEdit: function(noteId) {
				$("body").scrollTop(0);
				this.noteSaveBtn = false;
				this.note_id = noteId;
				this.resetEditNoteModal = true;
				DataBridge.get('NotesListing.getNoteType', this.contactId, '*', this.populateNoteTypes);
				DataBridge.get('NotesListing.getPolicesNoteType', this.contactId, '*', this.populatePolicyTypes);
				DataBridge.get('NotesListing.getSingleNote', noteId, '*', this.populateSingleNotesListing);

			},
			populateSingleNotesListing: function(response) {

				this.editNoteData = (response['NotesListing.getSingleNote'][this.note_id]);
				if(this.resetEditNoteModal == true)
				{

					this.resetPolicyTypeVal.note_type = this.editNoteData.note_type ;
					this.resetPolicyTypeVal.note_type_id = this.editNoteData.note_type_id;
				}
			},
			populateNoteTypes: function(response) {

				this.notesTypeListing = (response['NotesListing.getNoteType'][this.contactId]);

				this.note_btn = false;
				$(".note_list").show();
				$('#note_cards_' + this.note_id).hide();
				this.editNoteCard = true;
			},
			populatePolicyTypes: function(response) {

				this.policyNotesTypeListing = (response['NotesListing.getPolicesNoteType'][this.contactId]);

			},

			closeEditNote: function()
			{
				this.closeDialog = true;
				if(this.addNoteldialog){
                    this.note_btn = false;
                }else{
                    this.note_btn = true;
                }
			},
			closeWithoutSaving: function() {
				this.note_btn = true;
				this.editNoteCard = false;
				this.closeDialog = false;
				$(".note_list").show();
				this.addNoteldialog = false;
				this.selectedPolicyType = '';
				this.selectedNoteType = '';
				if(this.resetEditNoteModal == true)
				{
					this.editNoteData.note_type = this.resetPolicyTypeVal.note_type ;
					this.editNoteData.note_type_id = this.resetPolicyTypeVal.note_type_id;
					this.resetEditNoteModal == false;
				}


			},
			enableSaveBtn: function() {
				this.noteSaveBtn = true;
			},

			saveNote: function() {
				this.notesLoader = true;
				var NoteData = {
					"note_type_id": this.editNoteData.note_type_id,
					"policy_type_id": this.editNoteData.note_type,
					"note_id": this.note_id
				}

				DataBridge.save('NotesListing.addContactNotes', NoteData, this.savedNotes);
			},

			savedNotes: function(response) {
				let vm = this;
				var result = JSON.parse(response['data']['data']);
				if (result['status'] == true || result['status'] == '1') {
					setTimeout(() => {
						this.notesLoader = false;
						vm.addNoteldialog = false;
						vm.editNoteCard = false;
						vm.note_btn = true;
						$(".note_list").show();
						$('#note_cards_' + vm.note_id).show();
						let objectData = {
						'contact_id' : this.contactId,
						'keyword' : this.keyword
						}
						DataBridgeContacts.save('NotesListing.getNotes', objectData, this.populateNotesListing);
                        vm.snackbar = true;
                        vm.NoteUpdatedStatus = true;
						
					}, 3000);
				}
			},
			populateNotesTypeListing: function(data) {
				var vm = this;
				vm.noteTypes = (data['NotesListing.getNoteTypeList'][this.contactId]);
			},
			populatePolicyTypeListing: function(data) {
				var vm = this;
				vm.policyTypes = (data['NotesListing.getNotesPolicyTypes'][this.contactId]);
				// this.customItemTemplate(vm.policyTypes);
			},
			addNewNoteDialog() {
				this.selectedPolicyType = '';
				this.selectedNoteType = '';
				this.attachmentAppendLists = [];
				this.image = [];
				DataBridge.get('NotesListing.getNoteTypeList', this.contactId, '*', this.populateNotesTypeListing);
				DataBridge.get('NotesListing.getNotesPolicyTypes', this.contactId, '*', this.populatePolicyTypeListing);
				this.addNoteldialog = true;
				this.note_btn = false;
				this.loadQuill();
			},
			addedNotes: function(response) {
				let vm =this;
				var result = JSON.parse(response['data']['data']);
				if (result['status'] == true || result['status'] == '1') {
					this.noteId = result['note_id'];
					this.saveNoteAttachment(this.noteId);
					setTimeout(() => {
						this.notesLoader = false;
						vm.addNoteldialog = false;
						vm.note_btn = true;
						DataBridgeContacts.save('NotesListing.getNotes', vm.contactId, this.populateNotesListing);
                        vm.snackbar = true;
                        vm.NoteSavedStatus = true;
						this.attachmentAppendLists = [];
						this.image = [];
					}, 1000);
				}
			},
			addNote: function() {
				this.notesLoader = true;
				let selectedNoteType = 0;
				if(this.selectedNoteType == ''){
					selectedNoteType = 3;
				}else{
					selectedNoteType = this.selectedNoteType;
				}
				var NoteData = {
					"note_type_id": selectedNoteType,
					"policy_type_id": this.selectedPolicyType,
					"note_text": this.addedNote,
					"contact_id": this.contactId
				}
				DataBridge.save('NotesListing.saveContactNotes', NoteData, this.addedNotes);				
				this.imageError = '';
			},
			enableAddNoteBtn: function() {
				this.addedNote = quill.root.innerHTML;
				if (this.addedNote === '' || this.addedNote === null) {
					this.noteAddBtn = false;
				}
				DataBridge.save('NotesListing.saveContactNotes', NoteData, this.addedNotes);
			},
			enableAddNoteBtn: function() {
				this.addedNote = quill.root.innerHTML;
				if (this.addedNote === '' || this.addedNote === null) {
					this.noteAddBtn = false;
				} else {
					this.noteAddBtn = true;
				}
			},
			disbleAddNoteBtn: function() {
				var text = quill.root.innerHTML;
				if (text == '<p><br></p>') {
					this.addedNote = '';
					this.noteAddBtn = false;
				} else {
					this.noteAddBtn = true;
				}
			},
			closeAddNote: function() {
				this.imageError = '';
				if (this.addedNote != '' || this.selectedPolicyType != '') {
					this.closeDialog = true;
					addNoteldialog = true;
					if(this.editNoteCard){
                        this.note_btn = false;
                    }else{
                        this.note_btn = true;
                    }
				} else {
                    if(this.editNoteCard){
                        this.note_btn = false;
                    }else{
                        this.note_btn = true;
                    }
					this.closeDialog = false;
					this.addNoteldialog = false;
				}
				this.image = [];
				this.attachmentAppendLists = [];
			},
			closeEditState: function() {
				this.editNoteCard = false;
				this.note_btn = true;
				this.editNoteCard = false;
				this.closeDialog = false;
				$(".note_list").show();
				if(this.addNoteldialog){
                    this.note_btn = false;
                }else{
                    this.note_btn = true;
                }
			},
			loadQuill: function() {
				var vm = this;
				setTimeout(
					function() {
						var toolbar = [];
						if (!this.noToolbar) {
							toolbar = [
								['bold', 'italic', 'underline'],
								[{
									align: ''
								}, {
									align: 'center'
								}, {
									align: 'right'
								}, {
									align: 'justify'
								}],
								[{
									'list': 'ordered'
								}, {
									'list': 'bullet'
								}, {
									'list': 'check'
								}],
								// ['image'],
							]
						}
						this.quill = new Quill('#quill_editor', {
							modules: {
								// toolbar: '#' + this.id + '_toolbar-container'
								toolbar: toolbar
							},
							placeholder: 'Start typing here..',
							theme: 'snow'
						});
						//Set the initial value

						if (this.value != undefined && this.value != '') {
							quill.root.innerHTML = this.value;
						}
						this.quill.on('text-change', function() {
							vm.virtualValue = quill.root.innerHTML;
							return vm.enableAddNoteBtn();
						});

					})
			},

			notesAttachmentModalListing: function(data) {
				var vm = this;
				if(data){
					vm.notesAttachment = JSON.parse(data['NotesListing.getNoteAttachments'][this.attachmentNoteId]);
				}
			},

			showNotesAttachments: function(note_id) {
				this.attachmentNoteId = note_id;
				DataBridge.get('NotesListing.getNoteAttachments', this.attachmentNoteId, '*', this.notesAttachmentModalListing);
			},
			onPickFile: function() {
				this.$refs.fileInput.click()
			},
			onFilePicked: function(event) {
				const files = event.target.files
				let filename = files[0].name;
				for (let i = 0; i < files.length; i++) {
					var re = /(\.pdf|\.docx|\.xls|\.xlsx|\.doc|\.png|\.jpg|\.jpeg|\.eml|\.pst|\.ost|\.mp4|\.mp3|\.m4a|\.wav|\.txt|\.csv|\.msg)$/i;
					if (files[i].name.lastIndexOf('.') <= 0) {
						this.imageError = 'Please select a valid file!';
					}else if (files[i].size > 16000000) {
						this.imageError = 'File size should be within the specified limits of 0 MB or 64 MB';
					}
					else if (!re.exec(files[i].name)) {
						this.imageError = 'Please choose only pdf, xls, png, jpeg, doc, eml, pst, ost, wav, mp3, m4a, mp4, txt, csv, msg files to upload!';
					} else {
						this.imageError = '';
						this.imageName = files[i].name;
						const fileReader = new FileReader()
						fileReader.addEventListener('load', () => {
							this.imageUrl = fileReader.result
						})
						fileReader.readAsDataURL(files[i])
						this.image.push(files[i]);
						var attachmentData = {
							'display_name': files[i]['name'],
							'upload_id': i
						}
						this.attachmentAppendLists.push(attachmentData);
					}
				}
			},
			removeChip: function(upload_id, id) {

				let updatedAttachmentList = this.attachmentAppendLists.filter((el) => el.upload_id !== upload_id);
				this.attachmentAppendLists = [];
				for (var i = 0; i < updatedAttachmentList.length; i++) {
					var attachmentData = {
						'display_name': updatedAttachmentList[i]['display_name'],
						'upload_id': i
					}
					this.attachmentAppendLists.push(attachmentData);
				}
				let updatedImageList = this.image.splice(id, 1);
			},

			saveNoteAttachment: function(noteId) {
				var formData = new FormData();
				if (this.image.length !== 0) {
					for (var i = 0; i < this.image.length; i++) {
						formData.append(i, this.image[i]);
					}
				}
				formData.append('contact_id', this.contactId);
				formData.append('note_id', noteId);
				var token = $("meta[name='csrf_token']").attr("content");
				url = base_url + 'Attachments/uploadAttachmentsForNotes';
				axios.post(url, formData, {
					headers: {
						'Content-Type': 'multipart/form-data',
						'X-CSRF-Token': token
					}
				}).then((response) => {

					if (response['data']['status'] == 1) {
                        var unUploadedAttachments = response['data']['unUploadedAttachments'];
						if(unUploadedAttachments.length > 0)
                        {
                            this.showSnackbar = false;
                            let errortext =  'Below files which size is 0 MB, were unable to be uploaded:\n';
                            $.each(unUploadedAttachments, function(index, value){
                                errortext += value + '\n';
                            });
                            swal("Saved!",errortext, "success");
                        }
						var NoteData = {
							"attachment_arr": response['data']['data'],
							"note_id": this.noteId,
							"contact_id": this.contactId,
						}
						// DataBridge.save('NotesListing.uploadAttachmentsNotes', NoteData, this.uploadAttachmentNotes);
					} else {
                        var unUploadedAttachments = response['data']['unUploadedAttachments'];
						if(unUploadedAttachments.length > 0)
                        {
                            this.showSnackbar = false;
                            let errortext =  'Below files which size is 0 MB, were unable to be uploaded:\n';
                            $.each(unUploadedAttachments, function(index, value){
                                errortext += value + '\n';
                            });
                            swal('',errortext, "warning");
                        }
					}
				});
			},
			uploadAttachmentNotes: function(response) {
				this.image = [];
				this.attachmentAppendLists = [];
			},
			pinNoteTop: function(contactNoteId) {

				DataBridge.save('NotesListing.togglePinNotes', {"contact_note_id": contactNoteId, "contact_id": this.contactId}, this.togglePinnedNotes);

			},

			togglePinnedNotes: function(response) {

				var result = JSON.parse(response['data']['data']);

				if (result.status == 1) {

					const num = Math.floor(Math.random() * 100);
					this.contactIdNext = this.contactId+"_"+num;
					DataBridgeSales.get('NotesListing.getNotesNext', this.contactIdNext, "*", this.populateNotesListingNext);

				} else {
					swal("Warning", "Only allows you to pin up to three notes.", "error");
				}
			},

			populateNotesListingNext: function(data) {
				this.notesListing = (data['NotesListing.getNotesNext'][this.contactIdNext]);

				this.pinnedNotes = [];
				let i = 0;
				for(let month in this.notesListing) {
					const noteData = [];
					this.notesListing[month].forEach(element => {
						if(element.pinned == 2){
							element.pinned_active = true;
							this.pinnedNotes.push(element);
						}else{
						    element.indexElement = i++;
							noteData.push(element);
						}
					});
					this.notesListing[month] = noteData;
				}
			},
			populateSearchNotesListing: function(data) {
				
				var vm = this;
				this.resetScrollBar();
				if(data['data'])
				{
					vm.notesListing = (data['data']['data']['NotesListing.searchContactNotes'][this.contactId]);
				}
				else
				{
					vm.notesListing = (data['NotesListing.searchContactNotes'][this.contactId]);
				}
				this.searchInProgress = false;
				this.showCross = 1;
				this.pinnedNotes = [];
				let i = 0;
				for(let month in this.notesListing) {
					const noteData = [];
					this.notesListing[month].forEach(element => {
						// element.indexElement = i++;
						if(element.pinned == 2){
							element.pinned_active = true;
							this.pinnedNotes.push(element);
						}else{
						    element.indexElement = i++;
							noteData.push(element);
						}
					});
					vm.notesListing[month] = noteData;
					vm.NoteUpdatedStatus = false;
					vm.NoteSavedStatus = false;
				}					
			},
			searchNotes(evt){
				this.keyword = evt.target.value;
				this.offset = 0;
				if(this.keyword != ''){
					this.searchInProgress = true;
					this.showCross =  false;
				}else{
					this.searchInProgress = false;
					this.showCross = true;
				}
				let objectData = {
					'contact_id' : this.contactId,
					'keyword' : evt.target.value
				}
				
				DataBridgeContacts.save('NotesListing.searchContactNotes', objectData, this.populateSearchNotesListing);
			},			
			clearSearchBar:function(){
				this.keyword = '';
				this.showCross = 0;
				DataBridge.get('NotesListing.getNotes', this.contactId, '*', this.populateNotesListing);
			},
			loadMoreNotes:function(event){
				this.offsetTop = event.target.scrollTop + 0.5;
				var height = this.offsetTop + event.target.clientHeight;
				height = height.toFixed(0);
				if(height >= event.target.scrollHeight)
				{   
					if(!this.reachEnd){
						this.scrollLoader = true;
						let offset = this.limit*this.start;
						let objectData = {
							'contact_id' : this.contactId,
							'keyword' : this.keyword,
							'offset' : offset,
							'limit' : this.limit
						}
						this.start++;
						DataBridge.save('NotesListing.getNotes', objectData, this.populateLoadMoreNotesListing);
					}
				}
			},
			populateLoadMoreNotesListing:function(data){
				var vm = this;
				if(data['data'])
				{
					var loadMoreNotesListing = (data['data']['data']['NotesListing.getNotes'][this.contactId]);
				}
				else
				{
					var loadMoreNotesListing = (data['NotesListing.getNotes'][this.contactId]);
				}
				// this.pinnedNotes = [];
				let i = 0;
				if(Object.keys(loadMoreNotesListing).length > 0)
				{
					for(let month in loadMoreNotesListing) {
						if(!vm.notesListing[month])
							vm.notesListing[month] = [];

						// const noteData = [];
						loadMoreNotesListing[month].forEach(element => {
							vm.notesListing[month].push(element);
						});
						// console.log(loadMoreNotesListing[month],'loadMoreNotesListing[month]');
						// vm.notesListing[month].push(...loadMoreNotesListing[month]);
					}
					this.reachEnd = false;
					this.scrollLoader = false;
				}else{
					this.reachEnd = true;
				}
			},
			resetScrollBar: function()
			{
				this.$refs.noteLists.scrollTop = 0;
			},

		},
		beforeMount: function() {
            this.initialNoteLoader = true;
			DataBridge.get('NotesListing.getNotes', this.contactId, '*', this.populateNotesListing);
			// DataBridge.get('contact', '*', this.populateContactData);
		},
		mounted: function() {
			this.$root.$on('openAddNote', (text) => {
				this.addNewNoteDialog();
			})
			
			if(this.initialModal){
                setTimeout(() => {
                    this.addNewNoteDialog();
                }, 1000);
            }

            this.$watch('initialModal', (newInitialModal) => {
				if(newInitialModal){
                    setTimeout(() => {
                        this.addNewNoteDialog();
                    }, 1000);
                }
            });
			DataBridge.get('NotesListing.getNoteTypeList', this.contactId, '*', this.populateNotesTypeListing);
		},

		// created: function() {
		// 	this.$watch('tab', (newValue) => {
		// 		if (newValue) {
		// 			const num = Math.floor(Math.random() * 100);
		// 			this.contactIdNext = this.contactId+"_"+num;
		// 			DataBridgeSales.get('NotesListing.getNotesNext', this.contactIdNext, "*", this.populateNotesListingNext);
		// 			this.keyword = '';
		// 		}
		// 	})
		// }
	});
</script>