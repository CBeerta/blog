/* Author:  Claus Beerta

*/


$(document).ready(function() 
{

    if (window.jQuery.editable) {
        $('.editable').editable('/blog/save',
        {
            loadurl     : '/blog/json_load',
            loadtype    : 'POST',
            type        : "autogrow",
            submit      : "Save",
            indicator   : '<img src="/public/img/indicator.gif">',
            tooltip     : 'Click to edit.',
            onblur      : 'cancel',
            width       : 'auto',
            style       : "display: inline",
            autogrow    : {
               lineHeight : 16 /*,
               minHeight  : 100*/
            }
        });
    }

    // go through entry links and set a title if theres none.
    // Yes, i'm that lazy. Mainly for fancybox to display pretty footer
    $('.entry a').each(function() {
        if ( !$(this).attr('title') ) {
            $(this).attr('title', basename($(this).attr('href')));
        }
    });

    // Just enable fancybox for all img src inside an href for our posts    
    $('.entry a').each(function() {
        if ($(this).html().match(/img.*src/i)) {
            $(this).fancybox({
                'titlePosition': 'over',
                'hideOnContentClick': true,
                'centerOnScroll': true
            });
        }
    });
    

});

function basename(path) 
{
    return path.replace(/\\/g,'/').replace( /.*\//, '' );
}

function toggle_publish(id)
{
    $.ajax({  
        type: "POST",
        url: "/blog/toggle_publish",
        data: { 'id': id },
        success: function(data) {
            var trash = $("#" + id + " img.toggle_publish");
            if (data == 'publish') {
                trash.attr('src', '/public/img/check_alt_32x32.png')
            } else {
                trash.attr('src', '/public/img/denied_32x32.png')
            }
        }
    });
}

function trash_post(id)
{
    var answer = confirm("You sure you want to Delete it?");
    
    if (!answer) {
        return;
    }
    
    $.ajax({  
        type: "POST",
        url: "/blog/trash",
        data: { 'id': id },
        success: function(data) {
            alert(data);
        }
    });
}

