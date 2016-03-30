/**
 * Created by Bill on 11/19/2014.
 */
 
 function goBack(){
	window.history.back();
    return false;
}
	
$(document).ready(function () {
    /** Start JQuery methods related to Deliverable Tags **/
    $('.addrow').tooltip(); //tooltip for adding a tag row

    $('.delgrp').tooltip(); //tootip for delete tag row button
    /** End JQuery methods related to Deliverable Tags **/

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

            //load class attribute to be used in the function skipNProgress()
            var classNames = $(e.target).attr('class');

            //test if addRow button was pushed to skip load progress bar
            //span ID must be tested since Chrome and Opera send node click as span and not button
            if (e.target.id == 'addRow' || e.target.id == 'addRowSpan' || skipNProgress(classNames)){
                return;
            }else {
                NProgress.start();
            }
        }
    });
});/* End of document.ready */

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
                if((itemValues[i].indexOf('fileinput-remove') == 0) || (itemValues[i].indexOf('delgrp') == 0)){
                    return true;
                }
            }
        }else{
            if((itemValues.indexOf('fileinput-remove') == 0) || (itemValues.indexOf('delgrp') == 0)){
                return true;
            }
        }
    }
    return false;
}

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

//function to modify cloned row elements name and id values to next avaiable index
function modifyIdName(rowNode, index){
    var sep = "_", desc = "d", house = "h";
    var elements = rowNode.children;

    index = parseInt(index) + 1;
    for(var cellIndex = 0; cellIndex < elements.length; cellIndex++){
        var rowElement = elements[cellIndex].children;
        for(var element = 0; element < rowElement.length; element++){
            var elId = rowElement[element].id;
            if(elId){
                var base = elId.substr(0, elId.indexOf('_'));
                if(cellIndex == 0){
                    elId = base + sep + index;
                }else if(cellIndex == 1){
                    elId = base + sep + desc + index;
                }else if(cellIndex == 2){
                    elId = base + sep + house + index;
                }else if(cellIndex == 3){
                    elId = base + sep + index;
                }

                rowElement[element].id = elId;
                rowElement[element].name = elId;
                rowElement[element].value = "";
            }else{
                console.log("Failure of add row to Tag Table.");
            }
        }
    }
    return rowNode;
}

//function to copy description text from dropdown value to associated input box
function copyRowData(id){
    var td = "d", us = "_";

    element = document.getElementById(id);
    var fullValue = element.value;
    var str = fullValue.substr(fullValue.indexOf(' ') + 1);

    var prefix = id.substr(0, id.indexOf('_'));
    var item = id.substr(id.indexOf('_') + 1);
    var target = prefix + us + td + item;

    document.getElementById(target).value = str;
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
