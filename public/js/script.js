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

});

function basename(path) 
{
    return path.replace(/\\/g,'/').replace( /.*\//, '' );
}

