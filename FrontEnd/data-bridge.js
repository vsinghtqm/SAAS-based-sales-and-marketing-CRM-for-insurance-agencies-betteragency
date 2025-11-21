DataBridge = function(){
    var ns = {};

    var requestors = {};
    var pendingRequests = {};
    var loadedDataObjects = {};

    var logger = null;
	

    function get(objectName, objectId, fields, callback,start=null,limit=null){

       
        //field is over-ridden to be * right now...
        fields = '*';
		var url = '';
		if(limit == null){
		 url = base_url+'front-end-api/get/' + objectName + '/' + objectId + '/' + fields;
		}else{

		 url = base_url+'front-end-api/get/' + objectName + '/' + objectId + '/' + fields + '/' + start+ '/' + limit;
		}
       
        registerRequestor(objectName, objectId, callback);
       
        //removeCache(objectName);
        if(dataCached(objectName,objectId)){
            
            var objectsToSendBack = {};
            objectsToSendBack[objectName] = {};
            objectsToSendBack[objectName][objectId] = loadedDataObjects[objectName][objectId];

            setTimeout(
                function(){
                    callback(objectsToSendBack)
                },
                0
            );
        } else {
           
            if (!requestPending(objectName, objectId)) {
                
                pendingRequests[objectName][objectId] = true;
                logRequest("GET", url, "");
                axios.get(url)
                    .then(
                        function (response) {
                            //console.log("response"+JSON.stringify(response));
                            handleResponse(response, objectName, objectId);
                        }
                    );
            }
        }
    }

    function dataCached(objectName, objectId){
        if(
            loadedDataObjects.hasOwnProperty(objectName) &&
            loadedDataObjects[objectName].hasOwnProperty(objectId)
        ){
            return loadedDataObjects[objectName][objectId]
        }
        ensureObjectHasProperties(objectName, objectId, loadedDataObjects, false);
        return loadedDataObjects[objectName][objectId];
    }

    function requestPending(objectName, objectId) {
        ensureObjectHasProperties(objectName, objectId, pendingRequests, false);
        return pendingRequests[objectName][objectId];
    }

    function handleResponse(response, objectName, objectId){
        ensureObjectHasProperties(objectName, objectId, loadedDataObjects);
        for(var name in response.data.data){
            
            for(var id in response.data.data[name]){
                if(!loadedDataObjects.hasOwnProperty(name)){
                    loadedDataObjects[name] = {};
                }
                loadedDataObjects[name][id] = response.data.data[name][id]
            }
        }
        //cachedDataFromRequests[objectName][objectId] = response.data.data;


        callCallbacks(objectName, objectId);
        pendingRequests[objectName][objectId] = false;
        console.log("Recieved data for: " + objectName);
        console.log(loadedDataObjects);
    }

    function callCallbacks(objectName, objectId) {
        for(var i in requestors[objectName][objectId]){
            var receiver = requestors[objectName][objectId][i];
            var objectsToSendBack = {}
            objectsToSendBack[objectName] = {};
            objectsToSendBack[objectName][objectId] = {};
            objectsToSendBack[objectName][objectId] = loadedDataObjects[objectName][objectId];
            receiver(objectsToSendBack);
        }
    }

    function registerRequestor(object, objectId, callback){
        ensureObjectHasProperties(object, objectId, requestors);
        requestors[object][objectId].push(callback);
    }

    function ensureObjectHasProperties(objectName, objectId, object, defaultValue){
        if(object[objectName] === undefined){
            object[objectName] = {};
        }
        if(object[objectName][objectId] === undefined){
            object[objectName][objectId] = defaultValue !== undefined ? defaultValue : [];
        }
    }

    function save(objectName, objectData, callback){
        var url = '', objectNameData = {};
        var token = $("meta[name='csrf_token']").attr("content");  
        objectNameData.object_name = objectName;
        objectNameData.object_data = objectData;
        console.log("objectNameData",objectNameData);
        url = base_url+'front-end-api/post/';
        axios.post(url,objectNameData,{
            headers: { 'X-CSRF-Token': token }
        }).then(function (response) {
            console.log(response);
            callback(response);
        }).catch(function (error) {
            console.log(error);
            let errorMessage = handleError(error);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage,
                    confirmButtonColor: '#3085d6'
                });
            } else {
                if (errorMessage.includes("Network Error") || errorMessage.includes("SSL Protocol Error")) {
                    alert(errorMessage);
                }
                console.error('Swal is not defined. Please make sure to include the necessary script. Error Is: '+errorMessage);
            }
        });
    }

    function logRequest(method, url, body){
        if(logger !== null){
            logger(method, url, body);
        }
    }

    function setLogger(newLogger){
        logger = newLogger;
    }


    function removeCache(objectName){
        
        if(objectName == "Contacts.getSecondaryContacts" && objectName == "Contacts.getCommercialLinkContacts" ){
            delete requestors[objectName];
            delete pendingRequests[objectName];
            delete loadedDataObjects[objectName];
        }
       
    }

    /**
     * Handles errors from API requests and provides appropriate error messaging
     * @param {Error} error - The error object from axios
     * @returns {string} Formatted error message for display
     */
    function handleError(error) {
        // Default error message
        let errorMessage = 'An error occurred while processing your request';

        // Check if there's a server response with error details
        if (error.response) {
            // Server responded with error status code
            errorMessage = error.response.data.message || 'Server error: ' + error.response.status;
        } 
        // Check if request was made but no response received
        else if (error.request) {
            errorMessage = 'No response received from server. Please check your connection.';
        }
        // Handle errors in request setup
        else if (error.message) {
            // Handle SSL errors specifically
            if (error.message.includes('ERR_SSL_PROTOCOL_ERROR') || error.message.includes('SSL')) {
                errorMessage = 'Secure connection failed. Please try again.';
            } else {
                errorMessage = error.message;
            }
        }

        return errorMessage;
    }

    ns.get = get;
    ns.save = save;
    ns.setLogger = setLogger;
    return ns;
}();
