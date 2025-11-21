<?php

use ComponentLibrary\Lib\ComponentTools;

?>
<script type="text/x-template" id="<?=ComponentTools::templateName(__FILE__)?>">
    <div>
    <div v-if="(address !== '')" style="padding-right: 4px">
        {{ address }}
    </div>
    <div v-if="address2 !== ''" style="padding-right: 4px">
        {{ address2 }}
    </div>
    <div style="padding-right: 4px; width: inherit; white-space: normal;">
        <!-- {{ (city)?(" "+city+","):"," }} {{ state }} {{ zip }} -->
        {{ (city)?(state) ? city + ", " + state + " " : city + " " : (state)?state + " " : "" }}{{ zip }}
        <span v-if="county"><br>{{ county }}</span>

    </div>
    <div v-if="(address == '' || address == null) && (address2 == '' || address2 == null) && (city == '' || city == null) && (zip == '' || zip == null)">
        --
    </div>
    </div>
</script>

<script>
    Vue.component('<?=ComponentTools::componentName(__FILE__)?>', {
        template: '#<?=ComponentTools::templateName(__FILE__)?>',
        props: ['fieldData', 'objectId', 'value'],
        data: function(){
            return {
                fieldValue: '',
                countyInfo: {},
            }
        },
        methods: {
            setCountyName: function(data){
                console.log('setCountyName', data);
                this.countyName = data;
            },
        },
        computed: {
            address: function(){
                if(this.value[this.fieldData.addressMap.address1] !== undefined){
                    const address = this.value[this.fieldData.addressMap.address1];
                    const capitalizedValue = (address) ? address.charAt(0).toUpperCase() + address.slice(1) :'';
                    return capitalizedValue;
                } else {
                    return "";
                }
            },
            address2: function(){
                if(this.value[this.fieldData.addressMap.address2] !== undefined){
                    const address_second = this.value[this.fieldData.addressMap.address2];
                    const capitalizedValue = (address_second)? address_second.charAt(0).toUpperCase() + address_second.slice(1) : '';
                    return capitalizedValue;
                } else {
                    return "";
                }
            },
            city: function(){
                if(this.value[this.fieldData.addressMap.city] !== undefined){
                    const city = this.value[this.fieldData.addressMap.city];
                    const capitalizedValue = (city) ? city.charAt(0).toUpperCase() + city.slice(1) : '';
                    return capitalizedValue;
                } else {
                    return "";
                }
            },
            state: function(){
                if(this.value[this.fieldData.addressMap.stateId] !== undefined){
                    return StateMap.stateIdToStateAbbreviation(this.value[this.fieldData.addressMap.stateId]);
                } else {
                    return "";
                }
            },
            county: function(){
                let countyName = '';
                if(this.value[this.fieldData.addressMap.countyId] !== undefined){
                    let countyId = this.value[this.fieldData.addressMap.countyId];
                    let countyInfo = CountyByIdMap.countyById(countyId);
                    console.log('countyInfo', countyInfo);
                    if(countyInfo !== undefined){
                        //if there isn't City of at the end of the name, add County to the end of the name
                        if(countyInfo.name.indexOf(', City of')){
                            countyName = countyInfo.name + " County";
                        }
                        else{
                            countyName = countyInfo.name;
                        }
                    }
                }
                return countyName;
            },
            zip: function(){
                if(this.value[this.fieldData.addressMap.zip] !== undefined){
                    return this.value[this.fieldData.addressMap.zip];
                } else {
                    return "";
                }
            }
        },
    });
</script>
