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
    $.get("?/sidebar/deviantart/favby%3Aamg", function(data) {
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


});


