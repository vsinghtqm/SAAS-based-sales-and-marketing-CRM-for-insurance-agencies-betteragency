<?php

use ComponentLibrary\Lib\ComponentTools;

?>
<style>
    .message-attachment {
   position: absolute;
   z-index: 2;
   bottom: 0;
   text-align: center;
   /* left: 50%; */
   width: 100%;
   padding: 18px 0px;
   line-height:22px;
   }
   .custom-file-label::after {
   top: 16px !important;
   }
   .custom-file-label {line-height: 35px;}
   .custom-file {
   height: 155px;
   border: 2px dashed #ccc;
   border-radius: 6px;
   }
</style>
<script type="text/x-template" id="<?=ComponentTools::templateName(__FILE__)?>">
    <v-card flat>
        <v-card-text >
            <span class="text-h6">Add Task</span>
            <v-btn rounded color="success" class="float-right mb-3"
                elevation="2"
                >View Task</v-btn>
                <v-divider></v-divider>
                <v-row>
                <v-col
                    cols="12"
                    sm="6"
                    md="12"
                    >
                    <v-text-field
                        label="Title"
                        outlined
                    ></v-text-field>
                    </v-col>
                    <v-col
                        cols="12"
                        md="12"
                        style="margin-top: -40px;"
                    >
                        <v-textarea
                        rows="5"
                        data-trigger="summernote"
                        name="input-7-4"
                        value="The Woodman set to work at once, and so sharp was his axe that the tree was soon chopped nearly through."
                        ></v-textarea>
                    </v-col>
                    <v-col cols="12" lg="12">
                    <v-menu
                        :close-on-content-click="false"
                        :nudge-right="40"
                        transition="scale-transition"
                        offset-y
                        min-width="auto"
                    >
                        <template v-slot:activator="{ on, attrs }">
                        <v-text-field
                            label="Picker without buttons"
                            append-icon="mdi-calender"
                            readonly
                            outlined
                            v-bind="attrs"
                            v-on="on"
                        ></v-text-field>
                        </template>
                        <v-date-picker
                        @input="menu2 = false"
                        ></v-date-picker>
                    </v-menu>
                    </v-col>
                    <v-col cols="12" lg="12">
                        <v-select
                        :items="priorityList"
                        item-value="value"
                        item-text="name"
                        label="Select Priority"
                        dense
                        solo
                        @change="getPriority"
                        ></v-select>
                    </v-col>
                    <v-col cols="12" lg="12">
                        <v-select
                        :items="taskCategory"
                        item-value="id"
                        item-text="text"
                        label="Select Task Category"
                        dense
                        solo
                        @change="getTaskCategory"
                        ></v-select>
                    </v-col>
                    <v-col cols="12" lg="12">
                        <v-select
                        :items="policies"
                        item-value="value"
                        item-text="text"
                        label="Select Policies"
                        dense
                        solo
                        @change="sortByValue"
                        ></v-select>
                    </v-col>
                    <v-col cols="12" lg="12">
                        <v-select
                        :items="ownerList"
                        item-value="value"
                        item-text="name"
                        label="Select Owner"
                        dense
                        solo
                        @change="sortByValue"
                        ></v-select>
                    </v-col>
                    <v-col
                        cols="12"
                        sm="6"
                        md="12"                       
                    >
                    <v-textarea
                        small
                        name="input-7-4"
                        placeholder="Notes"
                        value=""
                        ></v-textarea>
                    </v-col>
                    <v-col
                        cols="12"
                        sm="6"
                        md="12"
                    >

                    <div class="row" id="contact_card_add_attachment_box_row">
                                 <div class="col-md-12 col-md-12 border-top p-b-10" >
                                    <label class="custom-file">
                                       <?php echo $this->Form->create('', ['id' => 'upload_multiple_attcement']); ?>
                                       <input id = "contact-muiltiple-files" type="file" class="custom-file-input" name="contact_multiple_files[]" onchange="uploadMultipleReferralAttacements()" multiple >
                                       <span class="custom-file-label" id="add_attach_text">Add</span>
                                       <span id="tab2_loader_attachement" class="tabs_loader_add_attachement" style="display: none;"><img src="<?=SITEURL?>img/loading.gif" style="top:8px;"></span>
                                       <?php echo $this->Form->end(); ?> 
                                       <div class="message-attachment">
                                          Max Upload file size is 8MB.<br/>
                                          Note: Accepted File Format : pdf, xls, png, jpeg, jpg, doc, docx, eml, pst, ost, wav, mp3, m4a, mp4
                                       </div>
                                    </label>
                                 </div>
                              </div>
                        <!-- <v-file-input
                        color="deep-purple accent-4"
                        counter
                        label="File input"
                        multiple
                        placeholder="Select your files"
                        prepend-icon="mdi-paperclip"
                        outlined
                        :show-size="1000"
                        >
                        <template v-slot:selection="{ index, text }">
                        <v-chip
                            v-if="index < 2"
                            color="deep-purple accent-4"
                            dark
                            label
                            small
                        >
                            {{ text }}
                        </v-chip>

                        <span
                            v-else-if="index === 2"
                            class="text-overline grey--text text--darken-3 mx-2"
                        >
                            +{{ files.length - 2 }} File(s)
                        </span>
                        </template>
                        </v-file-input> -->
                    </v-col>
                    <v-col class="float-right">
                        <v-btn rounded color="success" class="float-right mb-3"
                        elevation="2"
                        >Save</v-btn>
                    </v-col>
                </v-row>
                
        </v-card-text>
    </v-card>
</script>

<script>
    Vue.component('<?=ComponentTools::componentName(__FILE__)?>', {
        template: '#<?=ComponentTools::templateName(__FILE__)?>',
        props: ['fieldData', 'contactId', 'value'],
        data: function(){
            return {
                fieldValue: '',
                priorityList:[],
                taskCategory:[],
                policies:[
                    {'text':'Future Effective','value':1},
                    {'text':'Active','value':2},
                    {'text':'Inactive','value':3},
                    {'text':'Agriculture Package dd','value':4},
                ],
                ownerList:[],
            }
        },
        methods:{
            populateTaskCategory: function(data){
                var lm = this;
				lm = (data['TaskListing.getTaskCategory'][this.contactId]);
                for (const data in lm) {
                    for (const key in lm[data]) {
                        this.taskCategory.push({"id":lm[data]['id'],"text": lm[data]['name']});
                    }
                }
                // console.warn(this.taskCategory);
            },
            populatePriorityList: function(data)
            {
                var pl = this;
				pl = (data['TaskListing.getPriorityList'][this.contactId]['priority']);
                for (const data in pl) {
                    for (const key in pl[data]) {
                        this.priorityList.push({"name":pl[data][1],"text": pl[data][2],"value": pl[data][3]});
                    }
                }
                // console.log(this.priorityList);
            },
            populateOwnerList: function(data)
            {
                var ol = this;
                ol = (data['TaskListing.getOwnerList'][this.contactId]);
                for (const data in ol) {
                    for (const key in ol[data]) {
                        this.ownerList.push({"name":ol[data]['first_name']+' '+ol[data]['last_name'],"value": ol[data]['id']});
                    }
                }
                // console.warn(this.ownerList);
            },
            getTaskCategory(val)
            {
                alert(val);
            },
            sortByValue(val)
            {
                alert(val);
            },
            getPriority(val)
            {
                swal("Saved!", "Record saved successfully.", "success");
                alert(val);
            },
            getOwner(val)
            {
                alert(val);
            },
        },
        beforeMount: function(){
            DataBridge.get('TaskListing.getTaskCategory', this.contactId,'*', this.populateTaskCategory);
            DataBridge.get('TaskListing.getPriorityList', this.contactId,'*', this.populatePriorityList);
            DataBridge.get('TaskListing.getOwnerList', this.contactId,'*', this.populateOwnerList);
            // DataBridge.get('contact', '*', this.populateContactData);
        }
    });
</script>
