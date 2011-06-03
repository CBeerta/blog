/* Author:  Claus Beerta

*/


$(document).ready(function() 
{

    /**
    * Load Github projects async, as it might block on the server
    **/
    $.get("/sidebar/github/CBeerta", function(data) {
        json = $.parseJSON(data);
        var append;
        for (var repo in json['user']['repositories']) {
            var r = json['user']['repositories'][repo];
            
            if ( r['watchers'] = 0 ) continue;
            
            append = '';            
            append += '<li><a href="' + r['url'] + '">' + r['name'] + '</a>';
            append += '<p>' + r['description'] + '</p>';
            append += '</li>';
            
            $(".github").append(append);
        }
    });

    /**
    * Load Deviantart Favs
    **/
    $.get("/sidebar/deviantart/favby%3Aamg", function(data) {
        json = $.parseJSON(data);
        var append;
        for (var item in json)
        {
            var r = json[item];

            append = '';            
            append += '<li><a href="' + r['link'] + '">';
            append += '<img src="' + r['small'] + '">';
            append += '</a>';
            //append += '<p>' + r['description'] + '</p>';
            append += '</li>';
        
            $(".deviantart").append(append);
        }
    });

    $('.editable').editable('?/blog/save',
    {
        loadurl     : '?/blog/json_load',
        loadtype    : 'POST',
        type        : "autogrow",
        submit      : "Save",
        indicator   : '<img src="public/img/indicator.gif">',
        tooltip     : 'Click to edit.',
        onblur      : 'cancel',
        width       : 'auto',
        style       : "display: inline",
        autogrow    : {
           lineHeight : 16 /*,
           minHeight  : 100*/
        }
    });


});


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

