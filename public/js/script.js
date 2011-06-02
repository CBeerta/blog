/* Author:  Claus Beerta

*/


$(document).ready(function() 
{

    /**
    * Load Github projects async, as it might block on the server
    **/
    $.get("?/sidebar/github/CBeerta", function(data) {
        json = $.parseJSON(data);
        var append;
        for (var repo in json['user']['repositories']) {
            r = json['user']['repositories'][repo];
            
            if ( r['watchers'] = 0 ) continue;
            
            append = '';            
            append += '<li><a href="' + r['url'] + '">' + r['name'] + '</a>';
            append += '<p>' + r['description'] + '</p>';
            append += '</li>';
            
            $(".github").append(append);
        }
    });

});


