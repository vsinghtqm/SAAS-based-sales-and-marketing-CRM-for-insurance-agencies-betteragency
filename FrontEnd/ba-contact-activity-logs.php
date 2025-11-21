<?php

use ComponentLibrary\Lib\ComponentTools;

?>
<style>

</style>


<script type="text/x-template" id="<?=ComponentTools::templateName(__FILE__)?>">
    <div>
        <div class="d-flex custom-grey">
            <h4 class="upcoming-heading pt-2">View Logs</h4>
        </div>
        <div class="custom-grey mt-5">
            <div class="scroll-container pt-0 logs-tab-max-height" style=""  @scroll="onScrollLogsList">
                <v-row no-gutters v-if="Object.keys(items).length > 0" v-for="(item, index) in items" :key="index">
                   <v-col md="12" class="" >
                       <v-card class="pa-2 note_list" style="border-left: 8px solid #98DCFF; padding:12px;"  >
                           <span class="ml-auto email_created" style="float:right">{{ item.date }}</span>
                           <span v-if="item.logDetail != ''">
							<p class="ml-3 mt-2 text-policy"> {{ item.logDetail }} </p>

							</span>
                           <span v-if="item.first_name != ''">
								   <p class="ml-3 mt-1"> First Name : {{ item.first_name }} </p>
								</span>
								<span v-if="item.last_name != ''">
								   <p class="ml-3 mt-1"> Last Name : {{ item.last_name }} </p>
								</span>
								<span v-if="item.email != ''">
								    <p class="ml-3 mt-1"> Email : {{ item.email }} </p>
								</span>
								<span v-if="item.phone != ''">
								    <p class="ml-3 mt-1"> Phone : {{ item.phone }} </p>
								</span>
                       </v-card>
                   </v-col>
               </v-row>

                <v-row  v-if="items===null || Object.keys(items).length == 0 ">
                    <v-col md="12" class="mb-5" >
                        <div class="pl-7 pr-7 pt-7 pb-7 v-card v-sheet theme--light"><p class="text-center"> No records found. </p></div>
                    </v-col>
                </v-row>
            </div>
        </div>
    </div>
</script>


<script>
    Vue.component('<?=ComponentTools::componentName(__FILE__)?>', {
        template: '#<?=ComponentTools::templateName(__FILE__)?>',
        props: ['fieldData', 'contactId', 'value'],
        data: function(){
            return {
				base_url:base_url,
				logsListing: '',
				items: [],
				action:'Download',
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
				dialog: false,
				snackbar:false,
				timeout: 3000,
				limit: 20,
				start: 1,
                scrollLoader: false,
                reachEnd: false,

			}
        },
		computed: {
		  totalRecords() {
			  return this.items.length
		  },
		  pageCount() {
			  return this.totalRecords / this.itemsPerPage
		  },
		  computedAction(){
			  return this.action_url;
		  }

		},
		methods:{
            logList: function(data){
                const result  = (data['data']['data']['Contacts.getLogsListing'][this.contactId]);
				var logArray = [];
                result.forEach(item => {
                    logArray.push(item);
                })
				this.items = logArray;
			},
            onScrollLogsList: function({ target: { scrollTop, clientHeight, scrollHeight }}) {
                let vm = this;
				if(scrollTop + clientHeight >= scrollHeight){
					if(!this.reachEnd){
						this.scrollLoader = true;
						let offSet = this.limit * this.start;
						var data = {
							"contact_id" : this.contactId,
							"offSet" : offSet,
							"limit": this.limit,
						}
						DataBridge.save('Contacts.loadMoreLogsData', data, this.populateMoreLogsListing);
					}
				}
			},
            populateMoreLogsListing: function(response){
				var result =  JSON.parse(response['data']['data']);
				if(result['status'] == true || result['status'] == '1')
                {
					if(result['data']){
						this.start++;
                        for(let keys in result['data']){
                            this.items.push(result['data'][keys]);
						}
					}
					if(Array.isArray(result['data']) && result['data'].length == 0){
						this.reachEnd = true;
					}
					this.scrollLoader = false;
                }
			},
		},
		beforeMount: function() {
            this.$root.$on('logListingOnTabChange', (data) => {
				if(data == 8){
					DataBridgeContacts.save('Contacts.getLogsListing', this.contactId, this.logList);
				}

			});
        },
        mounted: function(){
            DataBridgeContacts.save('Contacts.getLogsListing', this.contactId, this.logList);
        },
    });
</script>