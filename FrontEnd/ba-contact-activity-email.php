
<?php

use ComponentLibrary\Lib\ComponentTools;

?>
<script type="text/x-template" id="<?=ComponentTools::templateName(__FILE__)?>">
    <v-card flat>
        <v-card-text >
            <span class="text-h6">Email</span>
                <!-- Templates dialog box -->
                <v-dialog
                        transition="dialog-top-transition"
                        max-width="600"
                        >
                        <template v-slot:activator="{ on, attrs }">
                        <v-btn
                            rounded
                            class="float-right"
                            color="success"
                            v-bind="attrs"
                            v-on="on"
                        >Templates</v-btn>
                        </template>
                        <template v-slot:default="dialog">
                        <v-card>
                            <v-toolbar elevation="1"
                            class="bolder"><h4>Templates</h4></v-toolbar>
                            <v-card-text>
                            <v-col
                                cols="12"
                                md="12"
                            >
                            <v-simple-table>
                                <template v-slot:default>
                                <thead>
                                    <tr>
                                    <th class="text-left">
                                        Sr.No.
                                    </th>
                                    <th class="text-left">
                                        Title
                                    </th>
                                    <th class="text-left">
                                        Description
                                    </th>
                                    <th class="text-left">
                                        Action
                                    </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                    v-for="item in templates"
                                    :key="item.name" >
                                    <td>1</td>
                                    <td>{{ item.title }}</td>
                                    <td v-html="item.description "></td>
                                    <td class="d-flex"><v-btn
                                        rounded
                                        class="float-right mt-1"
                                        color="success"
                                    ><v-icon small >mdi-pencil</v-icon></v-btn>
                                    <v-btn
                                        rounded
                                        class="float-right mt-1"
                                        color="success"                                                
                                    >Append</v-btn>
                                </td>
                                </tr>
                                </tbody>
                                </template>
                            </v-simple-table>
                            </v-col>
                            </v-card-text>
                            <v-divider></v-divider>
                            <v-card-actions class="justify-end">
                            <v-btn rounded 
                                    color="dark"
                                @click="dialog.value = false"
                            >Close</v-btn>
                            </v-card-actions>
                        </v-card>
                        </template>
                </v-dialog>
                 <!-- <v-btn
                    class="float-right"
                    elevation="0"
                    small
                 >X</v-btn> -->
                <v-divider></v-divider>
                <v-row>
                <v-col
                cols="12"
                lg="2"
                sm="4"
                md="3"
                >  
                    <h4 class="font-weight-bolder">From<span class="text-danger"> *</span></h4>
                </v-col>
                <v-col
                cols="12"
                lg="10"
                sm="6"
                md="3"
                >
                <v-text-field
                    label=""
                    placeholder=""
                    solo
                ></v-text-field>
                </v-col>
                <v-col
                cols="12"
                lg="2"
                sm="4"
                md="3"
                >  
                    <h4 class="font-weight-bolder">To<span class="text-danger"> *</span></h4>
                </v-col>
                <v-col
                cols="12"
                lg="10"
                sm="6"
                md="3"
                >
                <v-select
                    :items="items"
                    item-value="value"
                    item-text="text"
                    label=" "
                    dense
                    solo
                    ></v-select>
                </v-col>
                <v-col
                cols="12"
                lg="2"
                sm="4"
                md="3"
                >  
                <h4 class="font-weight-bolder">Subject<span class="text-danger"> *</span></h4>
                </v-col>
                <v-col
                cols="12"
                lg="10"
                sm="6"
                md="3"
                >
                <v-text-field
                    label=""
                    placeholder="Subject"
                    solo
                ></v-text-field>
                </v-col>
                <!-- <v-col
                cols="12"
                lg="2"
                sm="4"
                md="3"
                >  
                    <h4 class="font-weight-bolder">Subject<span class="text-danger"> *</span></h4>
                </v-col> -->
                <v-col
                    cols="12"
                    md="12"
                >
                <v-textarea
                        rows="5"
                        data-trigger="summernote"
                        name="input-7-4"
                        value="The Woodman set to work at once, and so sharp was his axe that the tree was soon chopped nearly through."
                        ></v-textarea>
                
                    <!-- <v-textarea
                    name="input-7-4"
                    label=""
                    ></v-textarea> -->
                </v-col>
                <v-col
                    cols="12"
                    md="12"
                >
                    <!-- Add Attachments dialog box -->
                    <v-dialog
                        transition="dialog-top-transition"
                        max-width="600"
                        >
                        <template v-slot:activator="{ on, attrs }">
                        <v-btn
                            rounded
                            color="success"
                            v-bind="attrs"
                            v-on="on"
                        >Add Attachments</v-btn>
                        </template>
                        <template v-slot:default="dialog">
                        <v-card>
                            <v-toolbar elevation="1"
                            class="bolder"><h4>ADD ATTACHMENTS FROM</h4></v-toolbar>
                            <v-card-text>
                            <v-col
                                cols="12"
                                md="12"
                            >
                                <v-btn
                                rounded
                                color="info"
                                ><v-icon>mdi-plus</v-icon> Uploaded Attachments</v-btn>
                                <v-btn
                                rounded
                                color="info"
                                ><v-icon>mdi-plus</v-icon> Browse Computer</v-btn>
                            </v-col>
                            </v-card-text>
                            <v-divider></v-divider>
                            <v-card-actions class="justify-end">
                            <v-btn rounded 
                                    color="dark"
                                @click="dialog.value = false"
                            >Close</v-btn>
                            </v-card-actions>
                        </v-card>
                        </template>
                   </v-dialog>
                <!-- Merge Fields dialog box -->
                <v-dialog
                        transition="dialog-top-transition"
                        max-width="600"
                        >
                        <template v-slot:activator="{ on, attrs }">
                        <v-btn
                            rounded
                            color="success"
                            v-bind="attrs"
                            v-on="on"
                        >Merge Fields</v-btn>
                        </template>
                        <template v-slot:default="dialog">
                        <v-card>
                            <v-toolbar elevation="1"
                            class="bolder"><h4>Merge Fields</h4></v-toolbar>
                            <v-card-text>
                            <v-col
                                cols="12"
                                md="12"
                            >
                            <v-simple-table>
                            <template v-slot:default>
                            <!-- <thead>
                                <tr>
                                <th class="text-left">
                                    Description
                                </th>
                                <th class="text-left">
                                    Date/Time
                                </th>
                                <th class="text-left">
                                    User
                                </th>
                                </tr>
                            </thead> -->
                            <tbody>
                                <tr
                                v-for="item in mergeFields"
                                :key="item.name" >
                                <td><span class="font-weight-bold">{{ item.title}}:</span> </td>
                                <td>{{item.field}}</td>
                                <td><v-icon>mdi-card-multiple</v-icon></td>
                            </tr>
                            </tbody>
                            </template>
                            </v-simple-table>
                            </v-col>
                            <!-- <div class="text-h2 pa-12">Hello world!</div> -->
                            </v-card-text>
                            <v-divider></v-divider>
                            <v-card-actions class="justify-end">
                            <v-btn rounded 
                                    color="dark"
                                @click="dialog.value = false"
                            >Close</v-btn>
                            </v-card-actions>
                        </v-card>
                        </template>
                </v-dialog>
                <!-- Add Attachments dialog box -->
                <!-- <v-dialog
                        transition="dialog-top-transition"
                        max-width="600"
                        >
                        <template v-slot:activator="{ on, attrs }">
                        <v-btn
                            rounded
                            color="success"
                            v-bind="attrs"
                            v-on="on"
                        >Add Attachments</v-btn>
                        </template>
                        <template v-slot:default="dialog">
                        <v-card>
                            <v-toolbar elevation="1"
                            class="bolder">Add Note</v-toolbar>
                            <v-card-text>
                            <v-col
                                cols="12"
                                md="12"
                            >
                                <v-textarea
                                name="input-7-4"
                                label="Note"
                                ></v-textarea>
                            </v-col>
                            </v-card-text>
                            <v-divider></v-divider>
                            <v-card-actions class="justify-end">
                            <v-btn rounded 
                                    color="dark"
                                @click="dialog.value = false"
                            >Close</v-btn>
                            <v-btn color="success" rounded
                            >Save</v-btn>
                            </v-card-actions>
                        </v-card>
                        </template>
                </v-dialog> -->
                <v-btn rounded color="success" class=""
                elevation="2"
                >Save as Templates</v-btn>
                <v-btn rounded color="info" class="float-right mb-3"
                elevation="2"
                >Send
                <v-icon>mdi-send</v-icon>
                </v-btn>
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
                desserts: [
                    {
                        name: 'Frozen Yogurt',
                        calories: 159,
                    },
                    {
                        name: 'Frozen Yogurt2',
                        calories: 159,
                    },
                    {
                        name: 'Frozen Yogurt3',
                        calories: 159,
                    },
                ],
                mergeFields:[
                    {
                        title:"Agency Name",
                        field:"{agency.name}",
                    },
                    {
                        title:"Agency Email",
                        field:"{agency.email}",
                    },
                    {
                        title:"Agency Street Address",
                        field:"{agency.streetAddress}",
                    },
                    {
                        title:"Agency City",
                        field:"{agency.city}",
                    },
                    {
                        title:"Agency State",
                        field:"{agency.state}",
                    },
                ],
                templates:[],
                items:["contact@teqmavens.com"],
                attachListing: '',
            }
        },
        methods:{
            
        },
        beforeMount: function(){
           
            // DataBridge.get('contact', '*', this.populateContactData);
        }
    });
</script>
