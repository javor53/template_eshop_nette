/*
 * Load content from iframe into form textarea
 */

function LoadContent(){
            document.getElementById('textContent').value = document.getElementById('wysiwygTextField').contentWindow.document.getElementById('droppable').innerHTML;
        }
        
$(document).contents().find("body").mousedown(function(event) {
    switch (event.which) {
        case 1:
            console.log("clicked");
            $('#wysiwygTextField').contents().find('#cntnr').hide();

            break;
        case 2:
            $('#wysiwygTextField').contents().find('#cntnr').hide();
            break;
        case 3:
            $('#wysiwygTextField').contents().find('#cntnr').hide();
            break;
        default:
            $('#wysiwygTextField').contents().find('#cntnr').hide();
    }
});