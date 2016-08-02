/**
 * Created by Bill on 11/19/2014.
 */

//global variable to hold the value of the Status select dropdown at page load time
var originalRequestStatusValue;

 function goBack(){
	window.history.back();
    return false;
}
	
$(document).ready(function () {

    //Capture how the dropdown Status is set at time page is loaded
    //Remember the element ID is hardcoded so change it required
    if(document.getElementById('Request_Status_t')){
        var statusElement = document.getElementById('Request_Status_t');
        originalRequestStatusValue = statusElement.options[statusElement.selectedIndex].value;
    }

    //Global tooltip enabled for all Bootstrap tooltip message
    $('[data-toggle="tooltip"]').tooltip();

    /** Show message to user after save operation then fade for Request and Deliverable pages **/
    if($('#savemessage').length){
        $('#savemessage').fadeIn("fast").fadeOut(15000);
    }


   /** function to deal with all date fields requiring a Date only format **/
    $('.date_only').datetimepicker({
        weekStart: 1,
        todayBtn: 1,
        autoclose: 1,
        todayHighlight: 1,
        startView: 2,
        minView: 2,
        forceParse: 0
    });

    /** This could be replaced with function above **/
    $('.air_date').datetimepicker({
        weekStart: 1,
        todayBtn: 1,
        autoclose: 1,
        todayHighlight: 1,
        startView: 2,
        minView: 2,
        forceParse: 0
    });

    /** This function formats a time only format **/
    $('.air_time').datetimepicker({
        weekStart: 1,
        todayBtn: 1,
        autoclose: 1,
        todayHighlight: 1,
        startView: 1,
        minView: 0,
        maxView: 1,
        forceParse: 0
    });

    //Method to switch display icons of check and un-check Blue checkbox
    $('#sys_mark_1').click(function(e) {
        console.log("Inside click function");
        if ($(this).hasClass('glyphicon-unchecked')) {
            $(this).removeClass('glyphicon-unchecked');
            $(this).addClass('glyphicon-check');
        } else {
            $(this).addClass('glyphicon-unchecked');
            $(this).removeClass('glyphicon-check');
        }
    });
	
	//Check if form fields changed prior to leaving page in any way
    $('#request-form').areYouSure({
        change: function(){
            document.getElementById('requestsubmit').style.visibility="visible";
        }
    });
	
	//Check if form fields changed on deliverable/meta form in any way
    $('#deliverable-form').areYouSure({
        change: function(){
            document.getElementById('deliverablesubmit').style.visibility='visible';
        }
    });
	
	/* ------ Start of file upload script -------*/
    //TODO - Start - remove all the upload JavaScript as the process is now dynamically generated in image or file meta builder
    var upload_document, uploadButtonDiv, pathDiv;

    /** This method processes images and documents **/
    window.runDocumentPost = function(tragetfile, buttondiv, successdiv){
        upload_document = tragetfile;
        uploadButtonDiv = buttondiv;
        pathDiv = successdiv;

        upload();
    }

    var upload = function () {
        if (upload_document.files.length === 0) {
            console.log('File length is zero');
            return;
        }

        /** turn off the success message when uploading a new document **/
        pathDiv.style.display = 'none';


        var data = new FormData();
        data.append('SelectedFile', upload_document.files[0]);

        /** Add the Meta __pk_ID to post operation (adds to $_POST array) **/
        data.append('pkId', upload_document.id);

        var request = new XMLHttpRequest();
        request.onreadystatechange = function () {
            if (request.readyState == 4) {
                try {
                    var resp = JSON.parse(request.response);
                } catch (e) {
                    var resp = {
                        status: 'error',
                        data: 'Unknown error occurred: [' + request.responseText + ']'
                    };
                }

                /** Add the full upload path to a hidden field **/
                pathDiv.value = resp.data;

                /* Hide the upload button if success */
                uploadButtonDiv.style.display = "none";

                /* Now the file was successfully uploaded so make visible the message */
                pathDiv.style.display = "block";

                /** Clear the input box for the uploaded file by ID Does Not work**/
                upload_document.value = '';

                console.log(pathDiv.value);
            }
        };

        request.open('POST', 'processing/documentuploader.php');
        request.send(data);
    }/* End of script to upload file */
    //TODO - End - remove all the upload JavaScript as the process is now dynamically generated in image or file meta builder

    /*
     * Function detects any click event within the body tag and presents NProgress bar after a click event
     * with exception of TAG (ONAIR-PRO Deliverable page) operations to add or delete tags
     */
    $('body').click(function (e) {
        if(e.target.nodeName == 'SPAN' ||
            e.target.nodeName == 'BUTTON' ||
            e.target.nodeName == 'IMG' ||
            e.target.nodeName == 'A'){

			//test line to write to console what was clicked
			//console.log("Node ID: " + e.target.id + " Node Name: " + e.target.nodeName + " Class Name: " + $(e.target).attr('class'));

            //TODO delete this after a successful test of the skipNprogressHasClassName() method as this is no longer needed
            //load class attribute to be used in the function skipNProgress()
            var classNames = $(e.target).attr('class');

            //test if addRow button was pushed to skip load progress bar
            //span ID must be tested since Chrome and Opera send node click as span and not button
            //TODO delete this after a successful test of the skipNprogressHasClassName() method as this is no longer needed
            //if (e.target.id == 'addRow' || e.target.id == 'addRowSpan' || skipNProgress(classNames)){
            if (e.target.id == 'addRow' || e.target.id == 'addRowSpan' || skipNprogressHasClassName(e)){
                return;
            }else {
                NProgress.start();
            }
        }
    });
});/* End of document.ready */

//This is a replacement method for skipNProgress() method to compact the code. All other comments remain true
//This function simply looks for matching element target class attributes which returns one or more
//in this case concern is set for two class groups where NProgess should not to run. The action
//must not invoke the progress bar at the top of any given page. This function is setup to detect a single
//class or multiple classes to determine if NProgress should run (Example class="btn btn-default delgrp" or
//class="delgrp")
function skipNprogressHasClassName(ele){
    var classArray = ['fileinput-remove', 'delgrp', 'remove-cross','upload-btn', 'printBtn','select_status'];
    for(var x = 0; x < classArray.length; x++){
        if($(ele.target).hasClass(classArray[x])){
            return true;
        }
    }
    return false;
}

//TODO: Test the skipNprogressHasClassName() method if not issues are found delete this method
//This function simply looks for matching element target class attributes which returns one or more
//in this case concern is set for two class groups where NProgess should not to run. The action
//must not invoke the progress bar at the top of any given page. This function is setup to detect a single
//class or multiple classes to determine if NProgress should run (Example class="btn btn-default delgrp" or
//class="delgrp")
function skipNProgress(values){
    if(values){
        var itemValues = values.split(' ');
        if(Array.isArray(itemValues)){
            for(var i = 0; i < itemValues.length; i++){
                if((itemValues[i].indexOf('fileinput-remove') == 0) ||
                    (itemValues[i].indexOf('delgrp') == 0) || (itemValues[i].indexOf('remove-cross') == 0) ||
                    (itemValues[i].indexOf('upload-btn') == 0)){
                    return true;
                }
            }
        }else{
            if((itemValues.indexOf('fileinput-remove') == 0)
                || (itemValues.indexOf('delgrp') == 0) || (itemValues[i].indexOf('remove-cross') == 0)||
                (itemValues[i].indexOf('upload-btn') == 0)){
                return true;
            }
        }
    }
    return false;
}

//TODO - remove all the upload JavaScript as the process is now dynamically generated in image or file meta builder
/* function outside of document ready to upload a file */
function fileUpload(imageElement, buttonElement, successPathDiv){
    runDocumentPost(imageElement, buttonElement, successPathDiv);
}

/** Start of function used by operation related to Deliverable Tags **/
/** A better function to add a row to the tags table **/
function cloneTagRow(id){
    var tIndex = document.getElementById('tagIndex').value;
    var dex = document.getElementById('tagIndex').value - 1;
    var tableId = document.getElementById(id).tBodies[0];  //get the table
    var node = tableId.rows[dex].cloneNode(true);    //clone the previous node or row
    var modNode = modifyIdName(node, tIndex); //Call function to rename id and name element for each cloned cell

    tableId.appendChild(modNode);   //add the node or row to the table
    document.getElementById('tagIndex').value = parseInt(document.getElementById('tagIndex').value) + 1; //increment hidden field by plus 1
    console.log("Added Row --> Current tagIndex value: " + document.getElementById('tagIndex').value);
}

/**
 * Method to update added Tag row with new ID value derived from hidden tag index field + one
 * @param rowNode pointer to table row to add children
 * @param index current numeric row index
 * @returns {*} updated row elements with index incremented value plus
 */
function modifyIdName(rowNode, index){
    var sp = "_", deleteRowId = "delRowId", defaultPrefix = "noTagPkId";
    var elements = rowNode.children;

    index = parseInt(index) + 1;
    for(var cellIndex = 0; cellIndex < elements.length; cellIndex++){
        var rowElement = elements[cellIndex].children;
        for(var element = 0; element < rowElement.length; element++){
            var replacementElementId = ""; //just in case declare the variable and reset it to empty
            var currentRowElementId = rowElement[element].id;
            if(currentRowElementId){
                var elementArray = currentRowElementId.split('_');
                var prefixId = elementArray[0];

                var typeId = elementArray[1];

                if(prefixId == deleteRowId) {
                    replacementElementId = prefixId + sp + index;
                }else if(prefixId != defaultPrefix){
                    replacementElementId = defaultPrefix + sp + typeId + sp + index;
                }else {
                    replacementElementId = prefixId + sp + typeId + sp + index;
                }

                console.log("New Relacement ID: " + replacementElementId);
                rowElement[element].id = replacementElementId;
                rowElement[element].name = replacementElementId;
                rowElement[element].value = "";
            }else{
                console.log("Failure of add row to Tag Table.");
            }
        }
    }
    return rowNode;
}


/**
 * function to copy description text from dropdown to associated input box for version list (Tags)
 * @param id string value of id assigned to text input box adjacent the dropdown
 */
function copyRowData(id){
    console.log("New Copy data Javascript method called");
    var td = "d", us = "_";

    var elem = document.getElementById(id);
    descriptionText = elem.options[elem.selectedIndex].innerHTML;

    var prefix = id.substr(0, id.indexOf('_'));
    var item = id.substr(id.indexOf('_') + 1);
    var target = prefix + us + td + item;

    document.getElementById(target).value = descriptionText;
}

//delete a row from the tag table
function delRow(id, index){
    var deleteTag = confirm("Do you want to delete row " + index + "?");
    if(deleteTag == false){
        return false;
    }
    var tableId = document.getElementById(id).tBodies[0];  //get the table
    var actualRow = parseInt(index) - 1;
    var elements = tableId.rows[actualRow].children;
    var rowElement = elements[0].children;
    var elementId = rowElement[0].id;
    var base = elementId.substr(0, elementId.indexOf('_'));

    if(base != 'noTagPkId'){
        console.log("Adding hidden field for pk: " + base);
        addHiddenTagField(base);
        document.getElementById('deliverablesubmit').style.visibility='visible';

    }
    tableId.deleteRow(actualRow);
    document.getElementById('tagIndex').value = parseInt(document.getElementById('tagIndex').value) - 1; //decrement hidden field by minus 1
    console.log("Delete Row --> Current tagIndex value: " + document.getElementById('tagIndex').value);
}

//add a hidden input field with the actual Tag PkId to be deleted at save time
function addHiddenTagField(fieldId){
    var hiddenInput = document.createElement("input");
    hiddenInput.setAttribute("type", "hidden");
    hiddenInput.setAttribute("name", fieldId);
    hiddenInput.setAttribute("id", fieldId);
    hiddenInput.setAttribute("value", "delete");
    //TODO we need to remove this hard coded value for the form ID
    document.getElementById("deliverable-form").appendChild(hiddenInput);
}
/** End of function used by operation related to Deliverable Tags **/


/**
 * Disable Print button from navigation bar when on page spotedit.php and display alert user to use ctrl print
 * to print the page
 * @returns false boolean
 */
function disabledPrintBtnMessage(){
    alert("The PDF Print button is disabled for this page.\n Please use the keyboard to print this page.");
    return false;
}


/**
 * Function to forward from Request pencil link to Deliverable View when form is not dirty (pure anchor link)
 * This function was modified to provide full url to forwarding page
 * @param deliverablePkId - FileMaker Deliverable primary key String
 */
function forwardDeliveriable(deliverablePkId){
    var pathDataUrl = "onrequest/deliverableview.php?pkId=" + deliverablePkId;
    //This code was added for a 'possible' inconsistent forwarding issue within an DMZ
    // (This code builds a full URL to page)
    var hostSiteName = window.location.protocol + "//" + window.location.host;
    var pathArray = window.location.pathname.split( '/' );
    var forwardingUrl = hostSiteName + "/" + pathArray[1] + "/" + pathDataUrl;
    window.location.href = forwardingUrl;
}

/**
 * Function to add 2 elements to a Request form used during PHP save processing. One adds the fact that a link
 * was clicked and the form is dirty so save the data forward to deliverable view
 * Note: This function is related to the pencil link on the request form "Only"
 * @param linkNum The numeric value of the anchor tag clicked
 */
function addHiddenLinkFields(linkNum){

    var fieldName = "__pk_ID_" + linkNum;

    var input = document.createElement("input");
    input.setAttribute("type", "hidden");
    input.setAttribute("name", "saveDataLink");
    input.setAttribute("value", "linkPushed");

    var pkToUse = document.createElement("input");
    pkToUse.setAttribute("type", "hidden");
    pkToUse.setAttribute("name", "pkToUse");
    pkToUse.setAttribute("value", document.getElementById(fieldName).value);

    document.getElementById("request-form").appendChild(input);
    document.getElementById("request-form").appendChild(pkToUse);
}

/**
 * A very simple method to delete a deliverable record from a request using the request item (FM #) number and ajax.
 * This method is generic enough and should not be tied to the dynamic code generation
 * @param deleteIndex -- integer number assigned to deliverable record (ID and name values) to get and then hide
 */
function deleteDeliverable(deleteIndex){
    var fullId = "__pk_ID_" + deleteIndex;
    var pk = document.getElementById(fullId).value;
    console.log("Delete for PK: " + pk);

    $.ajax({
        data: {deleteDelivPk:pk},
        url: 'processing/processRequest.php',
        method: 'POST',
        success: function(msg){
            console.log(msg);
            console.log("Success with delete operation Now hide TR");
            $('#del_deliverable_' + deleteIndex).hide();
        },

        error: function(msg){
            console.log("Error: " + msg);
        }
    });
}

//The following functions were added to support dynamic updates of dropdown fields based on the Division dropdown
//selector or Programming_Type_t

//generic javascript function to clear a dropdown list just pass in element ID
function clearDropdownOptions(dropdownId){
    var elementToClear = document.getElementById(dropdownId);
    var isSelected = elementToClear.options[e.selectedIndex].value;
    //could use text e.options[e.selectedIndex].text
    elementToClear.innerHTML = "";
    return isSelected;
}



/**
 * Generic replace method to replace contents in a drop down by element ID and if the previously selected item is
 * within the return JSON then set the option to selected
 * @param id string ID value
 * @param jsonData JSON data returned by PHP containing the list of key value pairs
 * @param isSelected key value of the previous selected item
 */
function replaceDropDownData(id, jsonData, isSelected){
    var parsedJson = JSON.parse(jsonData);

    //set an empty <option></option> tag if there was no selected item
    document.getElementById(id).insertBefore(new Option('', ''), document.getElementById(id).firstChild);

    //iterate the json data array to validate that the previously selected item is within the replacement list
    //if not then just append the json data item to select loist in the DOM
    $.each(parsedJson, function(i, value){
        if(value == isSelected){
            console.log("We should set a selected in option for " + isSelected);
            $('#' + id).append($('<option selected>').text(value).attr('value', value));
        }else{
            $('#' + id).append($('<option>').text(value).attr('value', value));
        }
    });
}

/**
 * Method call PHP that returns JSON data for a given Programming_Type_t selection
 * @param programmingType Dropdown element associated with the Division selection the UI
 */
function getDropdownPHPJsonData(programmingType, ddType) {
    //at this point we do not need to care what selected from the drop box just send via data on the POST
    var selected = programmingType.value;
    console.log("Now send data to processing page");
    $.ajax({
        url: "processing/dropDownJsonProcessing.php",
        data: {"programmingType": selected,"listType":ddType},
        type: "POST",
        success: function (response) {
            console.log("We have successfull JSON for Type: " + ddType);
            if(ddType == "spottype"){
                var maxCount = document.getElementById('spotindex').value;
                var ddPrefix = "Spot_Type_";

                for(var i = 0; i <= maxCount; i++){
                    emId = ddPrefix + i;
                    if(document.getElementById(emId)){
                        var selectedItem = clearDropdownOptions(emId);
                        replaceDropDownData(emId, response, selectedItem);
                    }
                }
            }else if(ddType == "showcode"){
                var showCodeId = "Show_Code_t";
                clearDropdownOptions(showCodeId);
                replaceDropDownData(showCodeId, response);
            }
        },
        error: function (response) {
            console.log("2. Error: " + response);
        }
    });
}

//This is a bit crappy to have to call the getDropdownPHPJsonData() function twice
function replaceAllDivisionDropdowns(programmingType){
    var showCodes = "showcode";
    var spotType = "spottype";

    getDropdownPHPJsonData(programmingType, spotType);
    getDropdownPHPJsonData(programmingType, showCodes);
}

/**
 * This method evaluates both the Tag Version and Description fields to determine how the text is the description
 * Note: this is a replacement method for copyRow() TODO: remove that method since its no longer used!!
 * field is modified. If the tag version field is selected with out the descriptor field the text portion is
 * then displayed in the description field. If the tag descriptor field is selected and a tag version appears
 * in the description field the text is modified to now read the what is in the descriptor field and all
 * [TagVer] placeholders are replaced with the text from the version drop down.
 * @param changedField the string element id value of the dropdown changed
 */
function processTagChanges(changedField){

    //1. prefix (could be PK or noPkId - pkPrefix
    //2. id td tv tt th - elementIdentifier
    //3. row index value - numeric row index
    var idElements = changedField.split("_");
    var pkPrefix = idElements[0];
    var elementIdentifier = idElements[1];
    var rowIndex = idElements[2];

    var tagTextFieldID = getTagDescriptionTextFieldId(pkPrefix, rowIndex);

    switch(elementIdentifier){
        case "td": // Tag descriptor field was changed so get tag version id
            var tagCheckTargetId = getTagVersionFieldId(pkPrefix, rowIndex);
            var selectedTagVersionEl = document.getElementById(tagCheckTargetId);
            var selectedTagVersionRawValue = selectedTagVersionEl.options[selectedTagVersionEl.selectedIndex].value;
            var selectedTagVersionValue = selectedTagVersionRawValue.substr(selectedTagVersionRawValue.indexOf(' ') + 1);
            var selectedTagDescriptorEl = document.getElementById(changedField);
            var selectedTagDescriptorRawValue = selectedTagDescriptorEl.options[selectedTagDescriptorEl.selectedIndex].value;
            var selectedTagDescriptorValue = selectedTagDescriptorRawValue.substr(selectedTagDescriptorRawValue.indexOf(' ') + 1);
            if(selectedTagDescriptorValue && selectedTagVersionValue){
                populateDescriptionUsingSplit(selectedTagDescriptorValue, selectedTagVersionValue, tagTextFieldID);
            }
            break;
        case "tv": // tag version was changed so get descriptor field id
            var tagCheckTargetId = getTagDescriptorFieldId(pkPrefix, rowIndex);
            var selectedTagDescriptorEl = document.getElementById(tagCheckTargetId);
            var selectedTagDescriptorRawValue = selectedTagDescriptorEl.options[selectedTagDescriptorEl.selectedIndex].value;
            var selectedTagDescriptorValue = selectedTagDescriptorRawValue.substr(selectedTagDescriptorRawValue.indexOf(' ') + 1);
            var selectedTagVersionEl = document.getElementById(changedField);
            var selectedTagVersionRawValue = selectedTagVersionEl.options[selectedTagVersionEl.selectedIndex].value;
            var selectedTagVersionValue = selectedTagVersionRawValue.substr(selectedTagVersionRawValue.indexOf(' ') + 1);
            if(selectedTagDescriptorValue && selectedTagVersionValue){
                populateDescriptionUsingSplit(selectedTagDescriptorValue, selectedTagVersionValue, tagTextFieldID);
            }else{
                populateDescriptionUsingTagVersionOnly(selectedTagVersionValue, tagTextFieldID);
            }
            break;
        default:
            var tagCheckTargetId = "";
            console.log("Error we have fallen through the case statement");
    }
}

/**
 * Method replace/modify the text that appears in the tag description field
 * @param tagDescriptorValue string value from the tag descriptor dropdown
 * @param tagVersionValue string value from the tag version dropdown
 * @param textFieldId string id of the tag description field
 */
function populateDescriptionUsingSplit(tagDescriptorValue, tagVersionValue, textFieldId){
    var searchWord = '[TagVer]';
    var textFieldEl = document.getElementById(textFieldId);
    if(tagDescriptorValue.indexOf(searchWord) > -1){
        var combinedText = tagDescriptorValue.split('[TagVer]').join(tagVersionValue);
        textFieldEl.value = combinedText.trim();
    }else{
        textFieldEl.value = tagDescriptorValue.trim() + " " + tagVersionValue.trim();
    }
}

/** Start of Tag Version/Tag Descriptor modifiedcation field control methods **/
/**
 * Method to replace or modify the Tag Description field with the text from Tag Version dropdown field
 * @param tagVersionValue string value from Tag Version dropdown
 * @param textFieldId string ID value of the Tag Description field
 */
function populateDescriptionUsingTagVersionOnly(tagVersionValue, textFieldId){
    var textFieldEl = document.getElementById(textFieldId);
    textFieldEl.value = tagVersionValue.trim();
}

/**
 * Simple method to build the string ID value of the Tag Version field
 * @param pk string primary key value or noPkId
 * @param index inetger value of row
 * @returns {string} string id dynamic value
 */
function getTagVersionFieldId(pk, index){
    var underscrore = "_";
    var tvSymbol = "tv";
    return pk + underscrore + tvSymbol + underscrore + index;
}

/**
 * Simple method to build the string ID value of the Tag Descriptor field
 * @param pk string primary key value or noPkId
 * @param index inetger value of row
 * @returns {string} string id dynamic value
 */
function getTagDescriptorFieldId(pk, index){
    var underscrore = "_";
    var tdSymbol = "td";
    return pk + underscrore + tdSymbol + underscrore + index;
}


/**
 * Simple method to build the string ID value of the Tag Description field
 * @param pk string primary key value or noPkId
 * @param index inetger value of row
 * @returns {string} string id dynamic value
 */
function getTagDescriptionTextFieldId(pk, index){
    var underscrore = "_";
    var ttSymbol = "tt";
    return pk + underscrore + ttSymbol + underscrore + index;
}

//Start of Javascript to process JSON data, messages, or empty JSON from PHP page call to to RESTfm
/**
 * Simple method to determine if object ius empty
 * @param obj object value top test for length
 * @param (boolean) true if object is empty
 *
 **/
function isEmpty(obj) {
    if(obj.length == 0){
        return true;
    }else{
        return false;
    }
}

/**
 * Method to read Json data for a JSON error element
 * @param jsonErrorData return JSON data
 * @param (boolean) true if error element is set
 **/
function isJsonErrorMessage(jsonErrorData){
    if(jsonErrorData.error){
        console.log("Json constains error message");
        return true;
    }
    return false;
}

/**
 * A method to process json data return from RESTfm and PHP call
 * @param id id value oif the select dropdown for Status
 * @param requestPk The request PK of the html form data
 */
function displayMetaErrors(id, requestPk, homePage){
    var statusElement = document.getElementById(id);
    var currentStatsValue = statusElement.options[statusElement.selectedIndex].value;

    if(currentStatsValue.toLowerCase() == "Submitted".toLowerCase()){ //only run this method with the status changes to submitted
        $.ajax({
            url: 'service/getMetaStatusPerRequest.php',
            type: 'POST',
            data: {'pk':requestPk},
            success: function (response) {
                if(!isEmpty(response)){ //we need to test if the json data array is empty then we know no records were found
                    console.log("response: " + response);
                    var dialogMessages = "";
                    var jData = JSON.parse(response);
                    if(jData){
                        if(isJsonErrorMessage(jData)){ //now test if an error message was inserted indicating a serious FM error
                            var url = homePage + "errors/accessError.php?errorMessage=Serious FileMaker Error&messageTitle=FileMaker Error";
                            window.location = url;
                        }
                        for(var rec in jData){ //passed all test so now display error/incomplete meta records
                            dialogMessages = dialogMessages + jData[rec].Deliverable_Description_ct + " Question " + jData[rec].Order_n + " incomplete\n";
                        }
                        statusElement.value = originalRequestStatusValue; //reset dropdown to original selection if meta errors were found
                        BootstrapDialog.show({
                            title: 'Meta Errors',
                            message: dialogMessages,
                            buttons: [{
                                label: 'Close',
                                cssClass: 'select_status',
                                action: function (thisDialog) {
                                    thisDialog.close();
                                }
                            }]
                        });
                    }
                }else{
                    console.log("No Meta records found skip Message Display");
                }

            },
            error: function(response){ // this needs further definition if an error is returned and with what to do with the error message
                console.log("Ajax returned an error: " + response);
            }
        });
    }
}

