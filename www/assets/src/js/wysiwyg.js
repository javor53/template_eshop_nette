var wysiwygTextField = document.getElementById("wysiwygTextField");
var iframe = document.getElementById('wysiwygTextField');

function iBold(){
    document.execCommand('bold',false,null); 
};

function iItalic(){
    document.execCommand('italic',false,null); 
}
function iUnderline(){
	document.execCommand('underline',false,null);
}

function iHorizontalRule(){
	document.execCommand('inserthorizontalrule',false,null);
}
function iUnorderedList(){
	document.execCommand("InsertOrderedList", false,"newOL");
}
function iOrderedList(){
	document.execCommand("InsertUnorderedList", false,"newUL");
}
function iLink(){
	var linkURL = prompt("Vložte odkaz:", "http://"); 
	document.execCommand("CreateLink", false, linkURL);
}
function iUnLink(){
	document.execCommand("Unlink", false, null);
}
function isNumeric(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}


function menuDeleteImg(e){
    var id = e.target.id;
    console.log("test");
    console.log(id);
    $("#" . id).remove();
}

/* When the user clicks on the button, 
toggle between hiding and showing the dropdown content */
function dropdown() {
  document.getElementById("myDropdown").classList.toggle("show");
}

// Close the dropdown if the user clicks outside of it
window.onclick = function(event) {
  if (!event.target.matches('.dropbtn')) {
    var dropdowns = document.getElementsByClassName("dropdown-content");
    var i;
    for (i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
      if (openDropdown.classList.contains('show')) {
        openDropdown.classList.remove('show');
      }
    }
  }
}

/*
$(function() {
    grid_size = 5;

    $(" .boxx ")
      .draggable({ grid: [ grid_size, grid_size ] })

      .resizable({ grid: grid_size * 2 })

            .on("mouseover", function(){
            $( this ).addClass("move-cursor");
            })

            .on("mousedown", function(){
            $( this )
          .removeClass("move-cursor")
          .addClass("grab-cursor")
          .addClass("opac");

            $(" .text ").hide();

            })

            .on("mouseup", function(){
            $( this )
          .removeClass("grab-cursor")
          .removeClass("opac")
          .addClass("move-cursor");  
            });

    $(document).on("mouseup", function() {
         var img = document.getElementById('dropped-image'); 
        //or however you get a handle to the IMG
        var width = img.clientWidth;
        var height = img.clientHeight;
        $(".width").empty().append("Width: " + width);
        $(".height").empty().append("Height: " + height);
        });
});*/


$(function(){
    
    $(".js-fontXS").css('font-size', 9 + 'pt');
    $(".js-fontS").css('font-size', 12 + 'pt');
    $(".js-fontM").css('font-size', 14 + 'pt');
    $(".js-fontL").css('font-size', 18 + 'pt');
    $(".js-fontXL").css('font-size', 24 + 'pt');
    
    $(".js-fontXS").mousedown(function(e){
        
        setFont(9, 'pt');
    });
    $(".js-fontS").mousedown(function(e){
        setFont(12, 'pt');
    });
    $(".js-fontM").mousedown(function(e){
        setFont(14, 'pt');
    });
    $(".js-fontL").mousedown(function(e){
        setFont(18, 'pt');
    });
    $(".js-fontXL").mousedown(function(e){
        setFont(24, 'pt');
    });
    $(".js-textAlignL").mousedown(function(e){
        setAlign("JustifyLeft");
        
    });
    $(".js-textAlignR").mousedown(function(e){
        setAlign("JustifyRight");
        
    });
    $(".js-textAlignC").mousedown(function(e){
        setAlign("JustifyCenter");
    });
    

   
    $(".js-hline").mousedown(function(e){
        
    });
    
    
    $( ".wysiwyg_iContent" ).keypress(function( event ) {
        if ( event.which === 13 ) {
           //code here
           //13 represents Enter key
           $(this).next('div').focus();
           

        }
      });
});






var actualId = "";
    
$(".wysiwyg_iContent").mouseup(function() {
    
    $('.context-menu').bind("contextmenu",function(e){
        e.preventDefault();
        //console.log(e.pageX + "," + e.pageY);
        actualId = $(e.target).attr('id');
        //console.log("idbind" + actualId);
        $("#" + actualId).addClass("active");
        var offset = $("#" + actualId).offset();
        $("#cntnr").css("left",offset.left);
        $("#cntnr").css("top",offset.top);

       // $("#cntnr").hide(100);    

        $("#cntnr").fadeIn(200,startFocusOut());      
    });
});

function startFocusOut(){
  $(document).contents().find("body").mousedown(function(event) {
        switch (event.which) {
            case 1:
                
                $("#" + actualId).removeClass("draggable draggable-ui");
                $("#" + actualId).removeClass("active");
                $("#cntnr").hide();  
                break;
            case 2:
                $("#" + actualId).removeClass("active");
                $("#cntnr").hide();
                break;
            case 3:
                $("#" + actualId).removeClass("active");
                $("#cntnr").hide();
                break;
            default:
                $("#" + actualId).removeClass("active");
                $("#cntnr").hide();
        }
    });
}
$(function(){

    $(".js-delete").mousedown(function(e){
        console.log("deleting id" + actualId);
       $("#" + actualId).remove(); 
       actualId = "";
    
    });
    $(".js-float-left").mousedown(function(e){
        $("#" + actualId).css("float","left");
        
    });
    $(".js-float-right").mousedown(function(e){
        $("#" + actualId).css("float","right");
        
    });
    $(".js-float-none").mousedown(function(e){
        $("#" + actualId).css("float","none");
    });
    $(".js-resize").mousedown(function(e){
        $("#stopres").removeClass("d-none");
        $("#res").addClass("d-none");
        $(".others").addClass("d-none");
        $("#" + actualId).resizable({
            maxWidth: 560,
            aspectRatio: true,
            grid: [10, 10]
        });
    });
    $(".js-stopresize").mousedown(function(e){
        $("#" + actualId).resizable('destroy'); 
        $("#stopres").addClass("d-none");
        $("#res").removeClass("d-none");
        $(".others").removeClass("d-none");          
    
    });
    $(".js-stopresize").hide;
});

$(document).ready(function(){	
                $(".textContent").richText();

});

$("#items > li").click(function(){
$("#op").text("You have selected "+$(this).text());
});


function align(highlightID, color) {
   if (window.getSelection && window.getSelection().toString()) {
    var node = getSelectionParentElement();
    if (node != null) {
	    var text = getSelectionText();
	    console.log("Selected text: " + text);
	    markFunc(node, text, /*HIGHLIGHT_CLASS + " " + */color);
    } else {
        console.log("Parent nde is null for some reason");
    }
  } else {
  		console.log("tapped without selection");
  }
}





function makeAlign(align) {
    sel = window.getSelection();
    if (sel.rangeCount && sel.getRangeAt) {
        range = sel.getRangeAt(0);
    }
    if (range) {
        sel.removeAllRanges();
        sel.addRange(range);
    }
    // Use HiliteColor since some browsers apply BackColor to the whole block
        document.execCommand(align, false,"");

}

function setAlign(align) {
    var range, sel;
    if (window.getSelection) {
        // IE9 and non-IE
        try {
            
                makeAlign(align);
         
        } catch (ex) {
            makeAlign(align);
        }
    } else if (document.selection && document.selection.createRange) {
        // IE <= 8 case
        range = document.selection.createRange();
    }
}
function makeFont(size,unit) {
    sel = window.getSelection();
    if (sel.rangeCount && sel.getRangeAt) {
        range = sel.getRangeAt(0);
    }
    if (range) {
        sel.removeAllRanges();
        sel.addRange(range);
    }

         document.execCommand('fontSize', false, size);
         jQuery("font[size]", document).removeAttr("size").css("font-size", size + unit);

}

function setFont(size,unit) {
    var range, sel;
    if (window.getSelection) {
        // IE9 and non-IE
        try {
            
                makeFont(size,unit);
         
        } catch (ex) {
            makeFont(size,unit);
        }
    } else if (document.selection && document.selection.createRange) {
        // IE <= 8 case
        range = document.selection.createRange();
    }
}

function makeColor(color) {
    sel = window.getSelection();
    if (sel.rangeCount && sel.getRangeAt) {
        range = sel.getRangeAt(0);
    }
    if (range) {
        sel.removeAllRanges();
        sel.addRange(range);
    }

         document.execCommand('foreColor', false, color);
}

function setColor(color) {
    var range, sel;
    if (window.getSelection) {
        // IE9 and non-IE
        try {
            
                makeColor(color);
         
        } catch (ex) {
            makeColor(color);
        }
    } else if (document.selection && document.selection.createRange) {
        // IE <= 8 case
        range = document.selection.createRange();
    }
}
  
        

 