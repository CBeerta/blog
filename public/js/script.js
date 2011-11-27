/* Author:  Claus Beerta

*/

/* google+ buttan */
(function() {
  var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
  po.src = 'http://apis.google.com/js/plusone.js';
  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
})();

/* google analytics */
var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-26492666-1']);
_gaq.push(['_setDomainName', 'beerta.net']);
_gaq.push(['_setAllowLinker', true]);
_gaq.push(['_trackPageview']);

(function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();

$(document).ready(function() 
{

    if (window.jQuery.editable) {
        $('.editable').editable('/blog/save',
        {
            loadurl     : '/blog/json_load',
            loadtype    : 'POST',
            type        : "autogrow",
            submit      : "Save",
            submitdata  : { '_METHOD': 'PUT' },
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
    $('.entry-content a').each(function() {
        if ( !$(this).attr('title') ) {
            $(this).attr('title', basename($(this).attr('href')));
        }
    });

    // Just enable fancybox for all img src inside an href for our posts    
    $('.entry-content a').each(function() {
        if ($(this).html().match(/img.*src/i)) {
            $(this).fancybox({
                'titlePosition': 'over',
                'hideOnContentClick': true,
                'centerOnScroll': true
            });
        }
    });
    
    $("form#post_tags").submit(function() {
        var tags = $('input#post_tags').attr('value'); 
        var postID = $('input#post_ID').attr('value'); 
        $.ajax({  
            type: "POST",
            url: "/blog/save/tags",
            data: { 'value': tags, 'id': postID,'_METHOD': 'PUT' },
            success: function(tags)
            {
                $("input#post_tags").attr('value', tags);
            }
        });                  
        return false;
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
            var toggle = $("img.toggle_publish");
            if (data == 'publish') {
                toggle.attr('src', '/public/img/check_alt_32x32.png')
            } else {
                toggle.attr('src', '/public/img/denied_32x32.png')
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
        data: { 'id': id, '_METHOD': 'DELETE' },
        success: function(data) {
            alert(data);
        }
    });
}

/**
http://wynnnetherland.com/journal/use-javascript-to-put-github-info-on-your-site
**/

jQuery(document).ready(function($){
  $.each($('a.github'), function() {
    console.log($(this));
    var post = $(this).parents(".post");
    var url = $(this).attr('href');
    var segments = url.split('/');
    var repo = segments.pop();
    var username = segments.pop();
    $.getJSON("http://github.com/api/v2/json/repos/show/"+username+"/"+repo+"?callback=?", function(data){
      var repo_data = data.repository;
      if(repo_data) {
        var watchers_link = $('<a>').addClass('watchers').attr('href', url+'/watchers').text(repo_data.watchers);
        var forks_link = $('<a>').addClass('forks').attr('href', url+'/network').text(repo_data.forks);
        var comment_link = post.find('.entry-meta .github');
        post.find('.entry-meta .github #watches').before(watchers_link);
        post.find('.entry-meta .github #forks').before(forks_link);
      }
    });
  });
});

