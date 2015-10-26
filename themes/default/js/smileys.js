//
//	Smileys Box by www.Sharetronix.ir
//	Designed by Nipoto - Coded by Guy18iran	
//

// Popup Window 
var win=null;
function NewWindow(mypage,myname,w,h,pos,infocus){
if(pos=="random"){myleft=(screen.width)?Math.floor(Math.random()*(screen.width-w)):100;mytop=(screen.height)?Math.floor(Math.random()*((screen.height-h)-75)):100;}
if(pos=="center"){myleft=(screen.width)?(screen.width-w)/2:100;mytop=(screen.height)?(screen.height-h)/2:100;}
else if((pos!='center' && pos!="random") || pos==null){myleft=0;mytop=20}
settings="width=" + w + ",height=" + h + ",top=" + mytop + ",left=" + myleft + ",scrollbars=yes,location=no,directories=no,status=no,menubar=no,toolbar=no,resizable=yes";win=window.open(mypage,myname,settings);
win.focus();}



// modified by Wooya

function insertText(elname, what, formname) {
    if (formname == undefined) formname = 'inputform';
    if (document.forms[formname].elements[elname].createTextRange) {
        document.forms[formname].elements[elname].focus();
        document.selection.createRange().duplicate().text = what;
    } else if ((typeof document.forms[formname].elements[elname].selectionStart) != 'undefined') {
        // for Mozilla
        var tarea = document.forms[formname].elements[elname];
        var selEnd = tarea.selectionEnd;
        var txtLen = tarea.value.length;
        var txtbefore = tarea.value.substring(0,selEnd);
        var txtafter =  tarea.value.substring(selEnd, txtLen);
        var oldScrollTop = tarea.scrollTop;
        tarea.value = txtbefore + what + txtafter;
        tarea.selectionStart = txtbefore.length + what.length;
        tarea.selectionEnd = txtbefore.length + what.length;
        tarea.scrollTop = oldScrollTop;
        tarea.focus();
    } else {
        document.forms[formname].elements[elname].value += what;
        document.forms[formname].elements[elname].focus();
    }
}

$(document).ready(function(){
    $('img#shrtrnx-smiley').click(function() {
        if ($('#shrtrnx-smiley-container').css('display') == "block") {
            $('#shrtrnx-container').hide('fast', function() {
                $('#shrtrnx-smiley-container').slideUp('slow');
            });
        } else {
            $('#shrtrnx-smiley-container').slideDown('slow', function() {
                $('#shrtrnx-container').show('fast');
            });
        }
    });
    $('img.shrtrnx-smiley-img').mouseover(function() {
        $(this).css('backgroundColor', '#cccccc');
    });
    $('img.shrtrnx-smiley-img').mouseout(function() {
        $(this).css('backgroundColor', '');
    });
    $('img.shrtrnx-smiley-img').click(function() {
        insertText("message", $(this).attr("alt"), "post_form");
    });
});