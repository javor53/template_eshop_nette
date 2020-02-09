
//drag (image,..) into iframe, when not droppable snape to original position.
$(function() {
            //After frame loaded
            $("#wysiwygTextField").attr('src','../../assets/iframes/iframeTextField.html').load(function() {
                //Activate droppable zones
                $(this).contents().find('.droppable').droppable({
                    drop: function(event, ui) {
                        //ACTION ON DROP HERE
                        //Get value of image element
                        var tempid = $(ui.draggable[0]);
                        var value = tempid.attr("value");
                        //Create <img> element
                        var newImage = doc.createElement("img");
                        newImage.src = "{$basePath|noescape}/" + value;
                        $(newImage).addClass("img-article");
                        $(newImage).addClass("col-12");
                        //Put element into iframe
                        document.getElementById('wysiwygTextField').contentWindow.document.getElementById("droppable").appendChild(newImage);
                        ui.draggable.css({ // Set original position of draggable
                            'left': $("#draggable").data('originalLeft'),
                            'top': $("#draggable").data('origionalTop')
                        });
                        //delete element after drop
                        //ui.draggable.remove();
                    }
                    
                });
            });

            //Activate draggable zones
            $('.draggable').draggable({
                revert:"invalid",    //Returns to original position when not dropped
                iframeFix: true,    //Core jquery ui params needs for fix iframe bug
                iframeScroll: true  //This param needs for activate iframeScroll
                
            });
            $('.draggable').data({ //Original positions data
                'originalLeft': $("#draggable").css('left'),
                'origionalTop': $("#draggable").css('top')
            });

        });