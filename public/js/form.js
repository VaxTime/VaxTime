  // focus on first input
  $("#applicationform input:text, #applicationform name").first().focus();

  $(document).ready(function ()
  {  
    $('#name').focus().select();
    $('[data-toggle="tooltip"]').tooltip();
  });


  // Validation
  function callModalErr() {
      jQuery('#ErrorMsgModal').modal({backdrop: 'static', keyboard: false});
    }
    // following code gets executed when the browser back button is pushed: hide the open modal + go one page back in the history state  => http://stackoverflow.com/questions/40314576/bootstrap-3-close-modal-when-pushing-browser-back-button#answer-43919280
    jQuery(window).on('popstate', function() { 
      jQuery("#ErrorMsgModal").modal('hide');
    });

    jQuery('#ErrorMsgModal').on('show.bs.modal', function() {
      // put an parameter in the URL by 'pushState()' method (see also code further down) => https://developer.mozilla.org/en-US/docs/Web/API/History_API
      history.pushState(null, null, '#ErrorMsgModal');
    });


function js_process_string(text){
  var chars = text.split(/&#(?!(\s))|;/g);
  var result='';
  if (chars[0]!=text){
    for(var i = 0; i < chars.length; ++i) {
      if (Number.isInteger(chars[i])) {
        result += String.fromCharCode(chars[i]);
      }
      else {
        result += chars[i];
      }
    }
    return result;
  }else{
    return chars[0];
  }
}

function validate_and_change_1(frm) {
    var ret = validate_basic_applic_1(frm);
}
function validate_and_change_2(frm) {
  var ret = validate_basic_applic_2(frm);
}
 

function validate_basic_applic_1(frm) {
  var value = '';
  var errFlag = new Array();
  _Msg = '';      
  jQuery('.fieldErrMark').each(function(){jQuery(this).removeClass('fieldErrMark');}); /* Remove previous marked fields */

   value = jQuery('#gender-male').is(':checked');
   value2 = jQuery('#gender-female').is(':checked');
   if ((value == false) && (value2 == false) && !errFlag['radio-1'] && !errFlag['radio-2']) {
        errFlag['radio-1'] = true;
        errFlag['radio-2'] = true;
        _Msg = _Msg + '<p>' + js_process_string(error_text_translations.gender_required) + '</p>';
  }

value = document.getElementById('select_day').value;
  if (value == 'day' && !errFlag['select_day']) {
        errFlag['select_day'] = true;
        _Msg = _Msg + '<p>' + js_process_string(error_text_translations.day_required) + '</p>';
  }

 value = document.getElementById('select_month').value;
  if (value == 'month' && !errFlag['select_month']) {
        errFlag['select_month'] = true;
        _Msg = _Msg + '<p>' + js_process_string(error_text_translations.month_required) + '</p>';
  }
  value = document.getElementById('select_year').value;
  if (value == 'year' && !errFlag['select_year']) {
        errFlag['select_year'] = true;
        _Msg = _Msg + '<p>' + js_process_string(error_text_translations.year_required) + '</p>';
  }

  // value = document.getElementById('language').value;
  // if (value == 'language' && !errFlag['language']) {
  //       errFlag['language'] = true;
  //       _Msg = _Msg + '<p>' + js_process_string('Language is a required field. Please try again.') + '</p>';
  // }

  if (_Msg != '') {
        jQuery('#errMsg').html(_Msg);
        var flag_keys = Object.keys(errFlag);
        for (i=0; i < flag_keys.length; i++) {
            jQuery('#' + flag_keys[i]).addClass('fieldErrMark');
        }
        jQuery('#ErrorMsgModal').on('hidden.bs.modal', function (e) {
            if (frm.elements[flag_keys[0]])
                frm.elements[flag_keys[0]].focus();
        });
        callModalErr();
        return false;
  } else {
    $("#applicationform").submit();
  } 
}

// check valid email
var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

function validate_basic_applic_2(frm) {
  var value = '';
  var errFlag = new Array();
  _Msg = '';    
  jQuery('.fieldErrMark').each(function(){jQuery(this).removeClass('fieldErrMark');}); /* Remove previous marked fields */

   value = document.getElementById('name').value;
  if ((value == '' && value.length < 2 || !Number.isNaN(parseInt(value[0]))) && !errFlag['name']) {
    errFlag['name'] = true;
    _Msg = _Msg + '<p>' + js_process_string(error_text_translations.name_required) + '</p>';
  }

  value = document.getElementById('email').value;
  if ((value == '' && value.length < 2 || !Number.isNaN(parseInt(value[0]))) && !errFlag['email']) {
    errFlag['email'] = true;
    _Msg = _Msg + '<p>' + js_process_string(error_text_translations.email_required) + '</p>';
  }

  email = document.getElementById('email').value;
  if(!regex.test(email)  &&  email!='') {
    errFlag['email'] = true;
    _Msg = _Msg + '<p>' + js_process_string(error_text_translations.email_invalid) + '</p>';
     }

  if (_Msg != '') { 
    jQuery('#errMsg').html(_Msg);
    var flag_keys = Object.keys(errFlag);
    for (i=0; i < flag_keys.length; i++) {
      jQuery('#' + flag_keys[i]).addClass('fieldErrMark');
    }
    jQuery('#ErrorMsgModal').on('hidden.bs.modal', function (e) {
      if (frm.elements[flag_keys[0]])
        frm.elements[flag_keys[0]].focus();
    });
    callModalErr();
    return false;
  } else {
    $("#applicationform").submit();
  } 
}


 // style social sharing buttons
 jQuery(".at-share-btn-elements svg").css({ fill: "#337ab7" });
