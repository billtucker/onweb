<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 3/29/2016
 * Time: 12:28 PM
 *
 * Function to dynamically build the Pencil link with supporting JavaScript. If changes are made to the Request form
 * and the user clicks the pencil ICON to move to the deliverableView.php the data is saved prior to redirect
 *
 */

include_once dirname(__DIR__) .DIRECTORY_SEPARATOR ."request-config.php";
include_once($utilities ."utility.php");

/**
 * method to dynamically build the Pencil link with supporting JavaScript.
 * @param $itemNum - String numeric value to assign unique ID values
 * @param $deliverablePkId -- String primary key value used in redirect URL
 */
function displayPencilLinkWithJavaScript($itemNum, $deliverablePkId){
    global $log;

    $log->debug("Now building Pencil link Item Number: " .$itemNum ." and PK: " .$deliverablePkId);

    $linkHtmlId = "anchorId_" .$itemNum;
    $deliverableHtmlId = "__pk_ID_" .$itemNum;

echo<<< DIVCODE
    <a href="#" id="$linkHtmlId" name="$linkHtmlId" >
        <span class="input-group-addon tdc-glyphicon-control tdc-cell-spacing icon-red">
            <span class="glyphicon glyphicon-pencil" title="Open and view this record" data-toggle="tooltip"></span>
        </span>
    </a>
    <input type="hidden" name="$deliverableHtmlId" id="$deliverableHtmlId" value="$deliverablePkId">
DIVCODE;

echo<<<JSCODE
 <script>
    $(document).ready(function () {

        //Variable for form ID used in the submit function
        if(document.getElementById('request-form')){
            var formHandle = document.getElementById('request-form');
        }

        //Listener to detect that the form changed
        $('#request-form').on('change keyup keydown', 'input, textarea, select', function (e) {
            $(this).addClass('form-changed');
        });

        // Get a reference to our link element (link/button) then execute on click
        // Note: change add event listener to use single method to process data
        document.getElementById("$linkHtmlId").addEventListener("click", function () {
            console.log("Link 1 was clicked");

            if ($('.form-changed').length) {
                addHiddenLinkFields($itemNum);

                //suppress message from areyousure javascript
                $('#request-form').areYouSure( {'silent':true} );
                    formHandle.submit();
            } else {
                    forwardDeliveriable(document.getElementById('$deliverableHtmlId').value);
            }
        });
    });
 </script>
 <br>
JSCODE;


}
