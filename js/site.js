// locale set
var myLocale = 'au';

function getLocation() {
  $.get('https://myip.expert/api/', function(response) {
    var response = JSON.parse(response);
    var country = response.userCountryCode;
    var region = response.userRegion;
    console.log(response);
    $.get('includes/setLocation.php?country=' + country + '&region=' + region, function(res) {
      if (res === 'success') {
        trace('Country/region set to: ' + country + '/' + region);
        // if the US then run modal popup to ask if user wishes to go to the US site
        if (country === 'US') {
          modalDialog(
            'It seems you are located in the United States',
            "<p>This website is for Australian customers of My Will Online.</p><p>Would you like us to take you to our US website <a href='https://mywillonline.io' title='My Will Online USA'>https://mywillonline.io</a>?</p>",
            0,
            "window.location ='https://mywillonline.io';",
            'Take me to My Will Online USA'
          );
        }
      } else {
        trace('Failed to set country and region');
      }
    });
  });
}

// FB vars
var FBloginstatus;

function site_login(fbdata) {
  // is email sent through from FB, if not, deny access
  if (fbdata.email == undefined) {
    // they have not allowed email therefore disallow FB login
    modalMessage(
      'Failed Facebook Login',
      "<p class='alert alert-danger'>You cannot login to My Will Online with Facebook, if you do not allow us access to your email address.</p><p>Alternatively, you may access this website by completing your name, email address and a login password. Thank you.</p>",
      0,
      '',
      'OK',
      'modal-sm'
    );
  } else {
    // check whether user is registered already and log them in if so
    $.post('includes/emailcheck.php', { email: fbdata.email }, function(data) {
      if (data == 'true') {
        // user not already defined so populate fields and submit
        $('#firstname').val(fbdata.first_name);
        $('#surname').val(fbdata.last_name);
        $('#registerEmail').val(fbdata.email);
        $('#fbid').val(fbdata.id);
        // random password if the user evers wants to logon using email and password, they will have to do a Lost Password request
        var randompw = Math.random()
          .toString(36)
          .slice(-8);
        $('#registerPassword').val(randompw);
        $('#registerPassword2').val(randompw);
        // submit the form
        $('#registerForm').submit();
      } else {
        // get pgref to link to another page if passed in to start page
        var pgref = '';
        pgref = $('#pgref').val();
        // user is already defined so log them on
        $.post('includes/loguserin.php', { email: fbdata.email, fbid: fbdata.id }, function(data) {
          if (data == 'true') window.location = pgref > '' ? pgref + '.html' : '/mydocs.html';
        });
      }
    });
  }
  closeSpinner();
}

// current date
var theDate = new Date();
var currentYear = theDate.getFullYear();

// prevent zoom on mobile device
document.addEventListener(
  'touchmove',
  function(event) {
    if (event.scale !== 1) {
      event.preventDefault();
    }
  },
  false
);

// is this a touch device
var isTouchDevice = 'ontouchstart' in document.documentElement;

/* !trace */
function trace(s) {
  try {
    console.log(s);
  } catch (e) {}
}

// centered popup
function popupwindow(url, title, w, h) {
  var left = screen.width / 2 - w / 2;
  var top = screen.height / 2 - h / 2;
  return window.open(url, title, 'width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
}

// test for numeric
function isNumber(theVal) {
  return !isNaN(theVal - 0) && theVal !== null && theVal !== '' && theVal !== false;
}

// reset form
function resetForm(theForm) {
  theForm.find('input, select, textarea').val('');
  theForm
    .find('input:radio, input:checkbox')
    .removeAttr('checked')
    .removeAttr('selected');
}

// function to strip html from a string
function strip(html) {
  var tmp = document.createElement('DIV');
  tmp.innerHTML = html;
  return tmp.textContent || tmp.innerText || '';
}

function checkOrder(orderID) {
  $.ajax({
    url: 'includes/checkorder.php',
    dataType: 'json',
    async: false, // needed so the download works properly
    data: { orderid: orderID },
    success: function(response) {
      switch (response.result) {
        case 'UNPAID':
        case 'EXPIRED':
          location = 'order.html';
          break;
        case 'PAID':
          // OK to download documents
          var theURL = window.location.protocol + '//' + window.location.host + '/download.html';
          var win = window.open(theURL, 'download');
          win.document.title = 'Download';
          break;
        case 'SESSIONENDED':
          alert('Session expired - please login again.');
        default:
          location = '/';
      }
    }
  });
}

function loadFB() {
  const fbappid = $('meta[property="fb:app_id"]').attr('content');
  window.fbAsyncInit = function() {
    FB.init({
      appId      : fbappid,
      cookie     : true,
      xfbml      : true,
      version    : 'v11.0'
    });
      
    FB.AppEvents.logPageView();   
      
  };

  (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = "https://connect.facebook.net/en_US/sdk.js";
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));  
}

// trigger popovers
function triggerPopovers() {
  var triggerType = isTouchDevice ? 'click' : 'hover';
  $('a[data-toggle="popover"]').popover({
    placement: function(context, source) {
      // this will display below if the popover link is in the top half else above
      var offset = $(source).offset();
      var posY = offset.top - $(window).scrollTop();
      var windowHeight = $(window).height();
      if (posY < windowHeight / 2) {
        return 'bottom';
      }
      return 'top';
    },
    html: true,
    trigger: triggerType
  });
}

/* !Spinner */
// preload spinner image used on create documents
new Image().src = '/images/waiting.gif';
function spinner(message, width, height, maskOpacity) {
  // default args
  message = typeof message != 'undefined' ? message : '';
  width = typeof width != 'undefined' ? width : 'auto';
  height = typeof height != 'undefined' ? height : 'auto';
  maskOpacity = typeof maskOpacity != 'undefined' ? maskOpacity : 0.3;

  message = '<img src="images/waiting.gif" width="60" height="60" style="margin: 0 100px 10px 100px;" /><br />' + message;

  var popupHTML =
    '<div id="spinner"><div id="spinnerContent" style="padding: 10px;">' + message + '</div></div><div id="mask"></div>';
  $('body').append(popupHTML);

  //Get the screen height and width
  var maskHeight = $(document).height();
  var maskWidth = $(window).width();

  //Set height and width of mask to fill up the whole screen
  $('#mask').css({ width: maskWidth, height: maskHeight });

  // stop the body scrolling underneath
  $('body').css('overflow', 'hidden');

  //transition effect
  $('#mask').fadeTo(200, maskOpacity);

  //Get the window height and width
  var winH = $(window).height();
  var winW = $(window).width();

  // set css
  $('#spinner').css('width', width);
  $('#spinner').css('height', height);

  //Set the popup window to center
  var top = winH / 2 - $('#spinner').height() / 2; // + $(window).scrollTop();
  var left = winW / 2 - $('#spinner').width() / 2;
  $('#spinner').css('top', top);
  $('#spinner').css('left', left);
  //transition effect
  $('#spinner').fadeIn(200);
}

function closeSpinner() {
  $('body').css('overflow', 'auto');
  $('#mask')
    .fadeOut(200)
    .remove();
  $('#spinner')
    .fadeOut(200)
    .remove();
}

/* form validator default messages and other methods */
// set default required validator message to blank so only the check marks will show - either X or tick
$.extend($.validator.messages, {
  required: 'This field is required',
  email: 'Please enter a valid email address',
  digits: 'Please enter only digits'
});

// Accept a value from a file input based on a required mimetype
$.validator.addMethod(
  'accept',
  function(value, element, param) {
    // Split mime on commas in case we have multiple types we can accept
    var typeParam = typeof param === 'string' ? param.replace(/\s/g, '') : 'image/*',
      optionalValue = this.optional(element),
      i,
      file,
      regex;

    // Element is optional
    if (optionalValue) {
      return optionalValue;
    }

    if ($(element).attr('type') === 'file') {
      // Escape string to be used in the regex
      // see: http://stackoverflow.com/questions/3446170/escape-string-for-use-in-javascript-regex
      // Escape also "/*" as "/.*" as a wildcard
      typeParam = typeParam
        .replace(/[\-\[\]\/\{\}\(\)\+\?\.\\\^\$\|]/g, '\\$&')
        .replace(/,/g, '|')
        .replace('/*', '/.*');

      // Check if the element has a FileList before checking each file
      if (element.files && element.files.length) {
        regex = new RegExp('.?(' + typeParam + ')$', 'i');
        for (i = 0; i < element.files.length; i++) {
          file = element.files[i];

          // Grab the mimetype from the loaded file, verify it matches
          if (!file.type.match(regex)) {
            return false;
          }
        }
      }
    }

    // Either return true because we've validated each file, or because the
    // browser does not support element.files and the FileList feature
    return true;
  },
  $.validator.format('Please select a file of the correct filetype')
);

$.validator.addMethod(
  'filesize',
  function(value, element, param) {
    return this.optional(element) || element.files[0].size <= param;
  },
  'File size must be less than {0} bytes'
);

$.validator.addMethod(
  'dateAU',
  function(value, element) {
    return this.optional(element) || Date.parseExact(value, 'd/M/yyyy');
  },
  'Please enter a valid date in the format dd/mm/yyyy'
);

$.validator.addMethod(
  'ccexpiry',
  function(value, element) {
    var d = new Date();
    var currentYY = d.getFullYear() - 2000;
    var currentMM = d.getMonth() + 1;
    var enteredYY = parseInt(value.substr(3, 2), 10);
    var enteredMM = parseInt(value.substr(0, 2), 10);
    var goodMonth = true;
    if (currentYY == enteredYY && currentMM > enteredMM) {
      goodMonth = false;
    }
    return this.optional(element) || (Date.parseExact(value, 'M/yy') && currentYY <= enteredYY && goodMonth);
  },
  'Please enter a valid future expiry date'
);

$.validator.addMethod(
  'ccv2',
  function(value, element) {
    return this.optional(element) || (value.length == 3 && value > 0);
  },
  'Please enter a 3 digit CCV2 code'
);

// http://jqueryvalidation.org/creditcard-method/
// based on http://en.wikipedia.org/wiki/Luhn_algorithm
$.validator.addMethod(
  'creditcard',
  function(value, element) {
    if (this.optional(element)) {
      return 'dependency-mismatch';
    }

    // Accept only spaces, digits and dashes
    if (/[^0-9 \-]+/.test(value)) {
      return false;
    }

    var nCheck = 0,
      nDigit = 0,
      bEven = false,
      n,
      cDigit;

    value = value.replace(/\D/g, '');

    // Basing min and max length on
    // http://developer.ean.com/general_info/Valid_Credit_Card_Types
    if (value.length < 13 || value.length > 19) {
      return false;
    }

    for (n = value.length - 1; n >= 0; n--) {
      cDigit = value.charAt(n);
      nDigit = parseInt(cDigit, 10);
      if (bEven) {
        if ((nDigit *= 2) > 9) {
          nDigit -= 9;
        }
      }

      nCheck += nDigit;
      bEven = !bEven;
    }

    return nCheck % 10 === 0;
  },
  'Please enter a valid credit card number.'
);

// override jquery validate plugin defaults suitable for bootstrap 3 forms
$.validator.setDefaults({
  /*
      onfocusout: function(element) {
              this.element(element);
      },
  */
  highlight: function(element) {
    $(element)
      .closest('.form-group')
      .addClass('has-error');
  },
  unhighlight: function(element) {
    $(element)
      .closest('.form-group')
      .removeClass('has-error');
    $(element)
      .parents('.form-inline')
      .removeClass('has-error');
  },
  errorElement: 'span',
  errorClass: 'help-block',
  errorPlacement: function(error, element) {
    if (element.parents('.btn-group').length) {
      error.insertAfter(element.parents('.btn-group'));
    } else if (element.parent('.input-group').length) {
      error.insertAfter(element.parent());
    } else if (element.parent('.stars').length) {
      element.parents('.form-group').addClass('has-error');
      error.insertAfter(element.parent());
    } else if (element.parents('.form-inline').length) {
      element.parents('.form-inline').addClass('has-error');
      error.insertAfter(element.parents('.form-inline').find('input[type=submit]'));
    } else {
      error.insertAfter(element);
    }
  }
});

// make Enter key press mimic a TAB instead of submitting form
function enterToTab() {
  $(document).on('keypress', 'input[type!=submit], select, button', function(e) {
    if (e.which == 13) {
      e.preventDefault();
      //find all input, textarea, select and buttons and find index of this and then focus this idx + 1
      var formElements = $('input, textarea, select, button').not(':hidden');
      var cnt = formElements.length;
      var idx = formElements.index(this);
      if (idx < cnt - 1) {
        formElements.get(idx + 1).focus();
      } else {
        formElements.get(0).focus();
      }
      return false;
    }
  });
}

// setup datepicker to use format dd/mm/yyyy by default
//$.fn.datepicker.defaults.format = "dd/mm/yyyy";
//$.fn.datepicker.defaults.autoclose = true;

/* form validator methods */
// add form validator method to prevent PO addresses
$.validator.addMethod(
  'notpobox',
  function(value, element) {
    return this.optional(element) || !value.toLowerCase().match(/\b(box|bag)\s+[a-zA-Z]?\d+/);
  },
  'You must enter a street address here'
);

// add form validator method for Australian Tax File number
// validate an Australian TFN
$.validator.addMethod(
  'tfn',
  function(value, element) {
    var weights = new Array(1, 4, 3, 7, 5, 8, 6, 9, 10);
    value = value.noWhiteSpace();
    if (value.length < 9) {
      // if empty return true
      return true;
    }
    total = 0;
    for (i = 0; i < 9; i++) total += weights[i] * value.charAt(i);
    if (total == 0 || total % 11 != 0) {
      return false;
    } else {
      return true;
    }
  },
  'Please enter a valid 9 digit Australian Tax File Number'
);

// add form validator method for Australian Company number
$.validator.addMethod(
  'acn',
  function(value, element) {
    var weights = new Array(8, 7, 6, 5, 4, 3, 2, 1);
    value = value.noWhiteSpace();
    var checkDigit = value.charAt(8);
    if (value.length != 9) {
      // if empty return true otherwise false
      return this.optional(element);
    }
    var total = 0;
    for (i = 0; i < 8; i++) total += weights[i] * value.charAt(i);
    var calcCheckDigit = 10 - (total % 10);
    calcCheckDigit = calcCheckDigit == 10 ? 0 : calcCheckDigit;
    if (checkDigit != calcCheckDigit) {
      return false;
    } else {
      return true;
    }
  },
  'Enter a valid 9 digit Australia Company Number'
);

// add form validator method for Australian Business number
$.validator.addMethod(
  'abn',
  function(value, element) {
    var weights = new Array(10, 1, 3, 5, 7, 9, 11, 13, 15, 17, 19);
    value = value.replace(/\D/g, ''); // remove non digits
    if (value.length != 11) {
      // if empty return true otherwise false
      return this.optional(element);
    }
    var total = 0;
    // subtract 1 from the first digit and multiply by its weight
    total = (value.charAt(0) - 1) * weights[0];
    // then multiply each additional digit by weight and total
    for (i = 1; i < 11; i++) {
      total += weights[i] * value.charAt(i);
    }
    trace(total);
    if (total % 89 != 0) {
      return false;
    } else {
      return true;
    }
  },
  'Enter a valid 11 digit Australia Business Number'
);

$.validator.addMethod(
  'fullphone',
  function(phone_number, element) {
    phone_number = phone_number.replace(/[\(\)\-\s]+/g, '');
    return this.optional(element) || phone_number.match(/^0[23478]{1}[0-9]{8}$/) || phone_number.match(/^1[38]{1}00[0-9]{6}$/) || phone_number.match(/^\+/);
  },
  'Enter a valid phone number including area code'
);
$.validator.addMethod(
  'anyphone',
  function(phone_number, element) {
    phone_number = phone_number.replace(/[\(\)\-\s\+]+/g, '');
    // console.log(phone_number, phone_number.match(/^[0-9]+/));
//     return this.optional(element) || phone_number.match(/^[0-9+]+$/);
    return this.optional(element) || true;
  },
  'Enter a valid phone number including area code'
);
$.validator.addMethod(
  'mobilephone',
  function(phone_number, element) {
    phone_number = phone_number.replace(/[\(\)\-\s]+/g, '');
    return this.optional(element) || phone_number.match(/^04[0-9]{8}$/);
  },
  'Enter a valid mobile number'
);

/* auto case set */
// convert a string to propercase (considers things like MacDonald
function toProperCase(s) {
  var str, lowers, uppers, i;
  // first propercase everything
  str = s.replace(/\b((m)(a?c))?(\w)(\S)/g, function($1, $2, $3, $4, $5, $6) {
    //trace('$1:' + $1 + ' $2:' + $2+ ' $3:' + $3 + ' $4:' + $4 + ' $5:' + $5);
    if ($2) {
      return $3.toUpperCase() + $4 + $5.toUpperCase() + $6;
    }
    return $5.toUpperCase() + $6;
  });
  // then make some words all lowercase
  lowers = [
    'A',
    'An',
    'And',
    'But',
    'Or',
    'For',
    'Nor',
    'As',
    'At',
    'By',
    'For',
    'From',
    'In',
    'Into',
    'Near',
    'Of',
    'On',
    'Onto',
    'To',
    'With'
  ];
  for (i = 0; i < lowers.length; i++)
    str = str.replace(new RegExp('\\s' + lowers[i] + '\\s', 'g'), function(txt) {
      return txt.toLowerCase();
    });
  // if using gpo or po make them all uppercase
  str = str.replace(new RegExp('^g?po ', 'i'), function(txt) {
    return txt.toUpperCase();
  });
  return str;
}

// make any class=propercase fields propercase on change
$(document).on('change', 'input.propercase, textarea.propercase', function() {
  $(this).val(toProperCase($(this).val()));
});

// make any class=lowercase fields lowercase on change
$(document).on('change', 'input.lowercase', function() {
  $(this).val(
    $(this)
      .val()
      .toLowerCase()
  );
});

// make any class=uppercase fields uppercase on change
$(document).on('change', 'input.uppercase', function() {
  $(this).val(
    $(this)
      .val()
      .toUpperCase()
  );
});

/* !AUTOCOMPLETES */
// fill autocomplete arrays on form load
function prefillAutoCompletes() {
  $('input.anyphone').each(function() {
    $(this).blur();
  });
  $('input.email').each(function() {
    $(this).blur();
  });
  $('input.name').each(function() {
    $(this).blur();
  });
  $('input.address').each(function() {
    $(this).blur();
  });
}
// setup phone number fields to autocomplete from earlier persons phone numbers
var phoneList = [];
$(document).on('blur', 'input.anyphone', function() {
  // add the value to phoneList
  if ($(this).val().length != 0) {
    var theValue = $(this).val();
    if ($.inArray(theValue, phoneList) == -1 && theValue.length > 7) phoneList.push(theValue);
  }
});

function phoneAutoComplete() {
  $('input.anyphone').autocomplete({
    source: phoneList,
    autoFocus: true,
    delay: 100,
    minLength: 1
  });
}

// setup email fields to autocomplete from earlier persons email
var emailList = [];
$(document).on('blur', 'input.email', function() {
  // add the value to emailList
  if ($(this).val().length != 0) {
    var theValue = $(this).val();
    if ($.inArray(theValue, emailList) == -1 && theValue.length > 5) emailList.push(theValue);
  }
});

function emailAutoComplete() {
  $('input.email').autocomplete({
    source: emailList,
    autoFocus: true,
    delay: 100,
    minLength: 1
  });
}

/* !State autocomplete */
//var stateList = ["NSW", "New South Wales", "VIC", "Victoria", "QLD", "Queensland", "WA", "Western Australia", "SA", "South Australia", "TAS", "Tasmania", "ACT", "Australian Capital Territory", "NT", "Northern Territory"];
var stateList = ['NSW', 'VIC', 'QLD', 'WA', 'SA', 'TAS', 'ACT', 'NT'];
function stateAutoComplete() {
  $('input.state').autocomplete({
    source: function(request, response) {
      var matches = $.map(stateList, function(tag) {
        if (tag.toUpperCase().indexOf(request.term.toUpperCase()) === 0) {
          return tag;
        }
      });
      //trace("source");
      response(matches);
    },
    search: function(event, ui) {
      var country = $(this)
        .parents('div.address')
        .find('input.country')
        .val();
      if (typeof country != 'undefined' && country != 'Australia') return false;
    },
    select: function(event, ui) {
      if (ui.item == undefined) {
        $(this)
          .val('')
          .focus()
          .autocomplete('search', '');
      }
    },
    change: function(event, ui) {
      var country = $(this)
        .parents('div.address')
        .find('input.country')
        .val();
      if (typeof country != 'undefined' && country != 'Australia') return false;
      if (ui.item === null) {
        // no item from list selected so make '' and re-search
        $(this)
          .val('')
          .focus()
          .autocomplete('search', '');
      }
    },
    autoFocus: true,
    delay: 100,
    minLength: 0
  });
}

/* !Name autocompletes */
var nameList = [];
nameList['FirstName'] = [];
nameList['SurName'] = [];
nameList['OtherName'] = [];
$(document).on('blur', 'input.name', function() {
  // add the value to nameList
  var fieldName = $(this).prop('name');
  var listName = '';
  if (fieldName.indexOf('firstname') != -1) listName = 'FirstName';
  else if (fieldName.indexOf('surname') != -1) listName = 'SurName';
  else listName = 'OtherName';

  if ($(this).val().length > 0) {
    var theValue = $(this).val();
    if ($.inArray(theValue, nameList[listName]) == -1 && theValue.length > 1) nameList[listName].push(theValue);
  }
});

function nameAutoComplete() {
  $('input.name').autocomplete({
    source: function(request, response) {
      var term = request.term;
      var fieldName = this.element.attr('name');
      var listName = '';
      var returnList = [];
      if (fieldName.indexOf('firstname') != -1) listName = 'FirstName';
      else if (fieldName.indexOf('surname') != -1) listName = 'SurName';
      else listName = 'OtherName';
      for (var i = 0; i < nameList[listName].length; i++) {
        var pattern = new RegExp('^' + term, 'i');
        var item = nameList[listName][i];
        if (pattern.test(item)) returnList.push(item);
      }
      response(returnList);
    },
    autoFocus: false,
    delay: 100,
    minLength: 1
  });
}

/* !Address Autocomplete */
var addressList = [];
$(document).on('blur', 'input.address', function() {
  // add the value to addressList
  if ($(this).val().length != 0) {
    var theValue = $(this).val();
    if ($.inArray(theValue, addressList) == -1 && theValue.length > 8) addressList.push(theValue);
  }
  // reset autocompletes
});

function addressAutoComplete() {
  $('input.address').autocomplete({
    source: addressList,
    autoFocus: true,
    delay: 100,
    minLength: 1
  });
}

// city complete for country eq Australia
function cityAutocomplete() {
  $('.cityauto').autocomplete({
    source: 'includes/citylist.php',
    autoFocus: true,
    delay: 100,
    minLength: 3,
    search: function(event, ui) {
      var country = $(this)
        .parents('div.address')
        .find('input.country')
        .val();
      if (typeof country != 'undefined' && country != 'Australia' && country != '') return false;
    },
    select: function(event, ui) {
      if (ui.item != undefined) {
        // something selected so update the state and postcode which must be the next 2 input fields
        var stateField = $(':input:eq(' + ($(':input').index(this) + 1) + ')');
        stateField.val(ui.item.state).valid();
        var pcodeField = $(':input:eq(' + ($(':input').index(this) + 2) + ')');
        pcodeField.val(ui.item.postcode).valid();
        var countryField = $(':input:eq(' + ($(':input').index(this) + 3) + ')');
        // make sure it is a country field
        if (countryField.hasClass('country')) countryField.val('Australia').valid();
        setTimeout("$('#postcode').blur();", 200); // this will trigger an update users where necessary
      } else {
        $(this)
          .val('')
          .focus()
          .autocomplete('search', '');
      }
    }
  });
}

function countryAutoComplete() {
  // country autocomplete
  $('.country').autocomplete({
    source: 'includes/countrylist.php',
    autoFocus: true,
    delay: 100,
    minLength: 1,
    change: function(event, ui) {
      if (!ui.item) {
        // if an item from the list was not selected then empty and refocus
        $(this)
          .val('')
          .focus()
          .autocomplete('search', '');
      }
    }
  });
}

/* !title autocomplete */
function titleAutoComplete() {
  var titles = ['Mr', 'Ms', 'Mrs', 'Miss', 'Dr', 'Prof', 'Master', 'Sir', 'Lady', 'Dame', 'Madam'];
  titles.sort();
  $('.titlefield').autocomplete({
    source: titles,
    autoFocus: true,
    delay: 100,
    minLength: 1
  });
}

/* !Relationship autocomplete */
var relationshipList = [
  'aunt',
  'brother',
  'brother-in-law',
  'cousin',
  'daughter',
  'daughter-in-law',
  'ex-father-in-law',
  'ex-husband',
  'ex-mother-in-law',
  'ex-wife',
  'father',
  'father-in-law',
  'friend',
  'granddaughter',
  'grandfather',
  'grandmother',
  'grandson',
  'husband',
  'mother',
  'mother-in-law',
  'nephew',
  'niece',
  'partner',
  'sister',
  'sister-in-law',
  'son',
  'son-in-law',
  'step-daughter',
  'step-son',
  'step-child',
  'step-grandchild',
  'step-grandson',
  'uncle',
  'wife'
];
function relationshipAutoComplete() {
  $('input.relationship').autocomplete({
    source: function(req, responseFn) {
      var re = $.ui.autocomplete.escapeRegex(req.term);
      var matcher = new RegExp('^' + re, 'i');
      var a = $.grep(relationshipList, function(item, index) {
        return matcher.test(item);
      });
      responseFn(a);
    },
    autoFocus: true,
    delay: 100,
    minLength: 1
  });
}

/* end of autocompletes */

/* Modal Dialog Message */
function modalDialog(title, message, delay, confirmcode, buttonname, size) {
  // default args
  title = typeof title != 'undefined' ? title : 'Message';
  message = typeof message != 'undefined' ? message : '';
  buttonname = typeof buttonname != 'undefined' ? buttonname : 'OK';
  delay = typeof delay != 'undefined' ? delay : 0; // milliseconds 0 is forever
  confirmcode = typeof confirmcode != 'undefined' ? confirmcode : ''; // code to run on close
  size = typeof size != 'undefined' ? size : ''; // size of dialog

  var dialogHTML =
    '<div class="modal fade" id="theModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">' +
    '  <div class="modal-dialog ' +
    size +
    '">' +
    '    <div class="modal-content">' +
    '      <div class="modal-header">' +
    '        <button id="theModalClose" type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
    '        <h4 class="modal-title" id="myModalLabel">' +
    title +
    '</h4>' +
    '      </div>' +
    '      <div class="modal-body">' +
    message +
    '</div>' +
    '      <div class="modal-footer">' +
    '        <button id="confirmDialog" type="button" class="btn btn-default" data-dismiss="modal">' +
    buttonname +
    '</button>' +
    '        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' +
    '      </div>' +
    '    </div>' +
    '  </div>' +
    '</div>';

  $('body').append(dialogHTML);

  $('#theModal').on('hidden.bs.modal', function(e) {
    $(this).remove();
  });

  $('#confirmDialog').click(function(e) {
    $('#theModal').modal('hide');
    eval(confirmcode); // do whatever was asked about
  });

  $('#theModal').modal('show');
  // close on timer
  if (delay != 0) {
    var code = "$('#theModalClose').click()";
    setTimeout(code, delay);
  }
}

/* Modal Box Message */
function modalMessage(title, message, delay, closecode, buttonname, size) {
  // default args
  title = typeof title != 'undefined' ? title : 'Message';
  message = typeof message != 'undefined' ? message : '';
  buttonname = typeof buttonname != 'undefined' ? buttonname : 'OK';
  delay = typeof delay != 'undefined' ? delay : 0; // milliseconds 0 is forever
  closecode = typeof closecode != 'undefined' ? closecode : ''; // code to run on close
  size = typeof size != 'undefined' ? size : ''; // size of dialog

  var dialogHTML =
    '<div class="modal fade" id="theModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">' +
    '  <div class="modal-dialog ' +
    size +
    '">' +
    '    <div class="modal-content">' +
    '      <div class="modal-header">' +
    '        <button id="theModalClose" type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
    '        <h4 class="modal-title" id="myModalLabel">' +
    title +
    '</h4>' +
    '      </div>' +
    '      <div class="modal-body">' +
    message +
    '</div>' +
    '      <div class="modal-footer">' +
    '        <button type="button" class="btn btn-default" data-dismiss="modal">' +
    buttonname +
    '</button>' +
    '      </div>' +
    '    </div>' +
    '  </div>' +
    '</div>';

  $('body').append(dialogHTML);

  $('#theModal').on('hidden.bs.modal', function(e) {
    $(this).remove();
    eval(closecode); // do something...
  });

  $('#theModal').modal('show');
  // close on timer
  if (delay != 0) {
    var code = "$('#theModalClose').click()";
    setTimeout(code, delay);
  }
}

/* Modal Form */
function modalForm(title, html, postURL, successCallback, spinnerMsg, buttonText) {
  // default args
  title = typeof title != 'undefined' ? title : 'Message';
  html = typeof html != 'undefined' ? html : '';
  postURL = typeof postURL != 'undefined' ? postURL : '';
  successCallback = typeof successCallback != 'undefined' ? successCallback : '';
  spinnerMsg = typeof spinnerMsg != 'undefined' ? spinnerMsg : 'Processing...';
  buttonText = typeof buttonText != 'undefined' ? buttonText : 'OK';

  var popupHTML =
    '<div class="modal fade" id="popupForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">' +
    '  <div class="modal-dialog">' +
    '    <div class="modal-content">' +
    '      <form role=form id=theForm>' +
    '        <div class="modal-header">' +
    '          <button id="popupFormClose" type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
    '          <h4 class="modal-title" id="myModalLabel">' +
    title +
    '</h4>' +
    '        </div>' +
    '        <div class="modal-body">' +
    html +
    '<div id="output" class="alert alert-danger" style="display: none;"></div></div>' +
    '        <div class="modal-footer">' +
    '          <input type="submit" class="btn btn-default" value="' +
    buttonText +
    '">' +
    '          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' +
    '        </div>' +
    '      </form>' +
    '    </div>' +
    '  </div>' +
    '</div>';

  $('body').append(popupHTML);
  $('#popupForm').modal('show');
  setTimeout(function() {
    $('#theForm')
      .find('input:first')
      .focus();
  }, 1000);
  $('#popupForm').on('hidden.bs.modal', function(e) {
    $(this).remove();
  });

  // post submit callback
  function handleResponse(responseText, statusText, xhr, form) {
    closeSpinner();
    if (responseText == 'success') {
      $('#popupFormClose').click();
      eval(successCallback);
    } else {
      $('#output')
        .html(responseText)
        .show();
      $('#theForm')
        .find('input:first')
        .focus();
    }
  }

  $('#theForm').validate({
    rules: {},
    submitHandler: function(form) {
      spinner(spinnerMsg);
      $(form).ajaxSubmit({ url: postURL, success: handleResponse });
    }
  });
}

// social sharing code
function socialSharing() {
  loadFB(); // so FB share will work

  // social sharing
  var fbappid = $('#fbappid').attr('content');
  var sitename = $('#fbsite').attr('content');
  var name = $('#fbtitle').attr('content');
  var discount = $('input[name=discount]').val();
  var smsg =
    "I've just used " + sitename + ' to create a Will. Click on this link to get a ' + discount + ' discount when you order.';
  var shortmsg =
    "If you don't have a Will, or it's old and needs updating, then try " +
    sitename +
    ". It's so easy to use, fast and inexpensive. Free Enduring Power of Attorney and Guardianship documents included and unlimited updates to your Will for life. If you use this link, you'll get a " +
    discount +
    ' discount on your Will.';
  var link = $('input[name=referralURL]').val();
  var picture = $('#fbpost').attr('content');
  var desc = $('#fbdesc').attr('content');
  var shortURL = $('input[name=shortURL]').val();
  var message = $('#posttext').val();

  // post story to users FB timeline
  $('#postToFB').click(function(e) {
    e.preventDefault();
    FB.ui(
      {
        method: 'share',
        mobile_iframe: true,
        href: 'https://mywillonline.com.au/'
      },
      function(response) {}
    );
  });

  // post to Twitter
  $('#postToTwitter').click(function(e) {
    e.preventDefault();
    popupwindow('https://twitter.com/share?url=' + shortURL + '&text=' + encodeURIComponent(smsg), 'Twitter', 520, 430);
  });

  // post to LinkedIn
  $('#postToLinkedIn').click(function(e) {
    e.preventDefault();
    popupwindow(
      'https://www.linkedin.com/shareArticle?url=' +
        shortURL +
        '&title=' +
        encodeURIComponent(name) +
        '&mini=true&summary=' +
        encodeURIComponent(shortmsg) +
        '&source=' +
        encodeURIComponent(sitename),
      'LinkedIn',
      520,
      430
    );
  });

  // post to Google+
  $('#postToGooglePlus').click(function(e) {
    e.preventDefault();
    popupwindow('https://plus.google.com/share?url=' + shortURL, 'Google+', 520, 430);
  });

  // select text when clicking in the posttext field
  $('#posttext').focus(function(e) {
    $(this).select();
  });

  // post to email
  $('#postToEmail').click(function(e) {
    e.preventDefault();
    //trace(e);
    location =
      'mailto:?subject=' +
      encodeURIComponent(name) +
      '&body=' +
      encodeURIComponent($('#posttext').val() + '\n\nUse this link to get the ' + discount + ' discount - ' + shortURL);
  });
}

// readorder data
function readOrderData(orderID, href) {
  // will do an ajax call to read the orderdata and then go to href
  var timecode = $.now();
  $.ajax({
    url: 'includes/readorderdata.php?timecode=' + timecode,
    dataType: 'json',
    async: false,
    data: { orderid: orderID },
    success: function(data) {
      switch (data.result) {
        case 'OK':
          location = href + '?uc=' + timecode; // go to url on button
          break;
        case 'ERROR':
          alert('Error reading data');
          location = '/';
          break;
        case 'SESSIONENDED':
          alert('Session expired - please login again.');
          location = '/';
          break;
        default:
          location = '/';
      }
    }
  });
}

function calcTotal() {
  // calculate total in id=percentAllocated
  var percentTotal = 0;
  $('input.estate')
    .not(':first')
    .each(function(i) {
      percentTotal += parseFloat($.parseNumber($(this).val(), { format: '0.00%', locale: myLocale }));
    });
  $('#percentAllocatedAmount').text($.formatNumber(percentTotal, { format: '0%', locale: myLocale }));
  percentTotal = Math.round(percentTotal * 100); // get rid of rounding errors
  if (percentTotal == 100) {
    $('#allocWarning').hide();
    $('#percentAllocated')
      .removeClass('alert-danger')
      .addClass('alert-success');
  } else {
    $('#allocWarning')
      .fadeIn(100)
      .fadeOut(100)
      .fadeIn(100)
      .fadeOut(100)
      .fadeIn(100)
      .fadeOut(100)
      .fadeIn(100);
    $('#percentAllocated')
      .removeClass('alert-success')
      .addClass('alert-danger');
  }
  return percentTotal;
}

// function to save data from the current form using an AJAX call
function saveFormData() {
  // remove data from any field that has the 'error' class set - field failed validation
  var timecode = $.now();
  $('input.error[type="text"]').val('');
  $('form').ajaxSubmit({
    url: 'includes/saveformdata.php?timecode=' + timecode, // override for form's 'action' attribute
    type: 'post', // 'get' or 'post', override for form's 'method' attribute
    dataType: null, // null, 'xml', 'script', or 'json' (expected server response type)
    clearForm: false, // clear all form fields after successful submit
    resetForm: false, // reset the form after successful submit
    cache: false,
    async: false, // block the process until saving is complete
    timeout: 3000,
    success: function(data) {
      if (data != 'success') {
        alert('Session expired - please login again.');
        location = '/';
      }
    }
  });
}

function saveQuickWillFormData(async) {
  var name = $('#testatorFullName').val(); // only save if the name is in there
  if (name.length > 0) {
    if (typeof window.localStorage != 'undefined') {
      var formData = JSON.stringify($('form').serializeArray());
      var encodedData = btoa(formData);
      window.localStorage.setItem('qwtime', $.now());
      window.localStorage.setItem('qw', encodedData);
    }
    async = typeof async != 'undefined' ? async : true;
    // remove data from any field that has the 'error' class set - field failed validation
    var timecode = $.now();
    $('input.error[type="text"]').val('');
    $('form').ajaxSubmit({
      url: 'includes/savequickwillformdata.php?timecode=' + timecode, // override for form's 'action' attribute
      type: 'post', // 'get' or 'post', override for form's 'method' attribute
      dataType: null, // null, 'xml', 'script', or 'json' (expected server response type)
      clearForm: false, // clear all form fields after successful submit
      resetForm: false, // reset the form after successful submit
      cache: false,
      async: async, // block the process until saving is complete IMPORTANT so we can read saved data
      timeout: 3000,
      success: function(data) {
        if (data == 'failure') {
          alert('Session expired');
          location = '/';
        } else {
          // data will be the orderID
          $('#orderid').val(data);
          trace('data saved');
        }
      }
    });
  }
}

/* used to escape characters in a variable when it is to be used in a RegExp */
function escapeRegExp(str) {
  return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, '\\$&');
}

// update user table row with the person address (because no longer done in start.html)
function updateUser() {
  // get the details from the form for the AJAX GET request
  var details = {
    firstname: $('#firstname').val(),
    othername: $('#othername').val(),
    surname: $('#surname').val(),
    address1: $('#address1').val(),
    address2: $('#address2').val(),
    city: $('#city').val(),
    state: $('#state').val(),
    postcode: $('#postcode').val(),
    phone: $('#personphone').val(),
    dob: $('#dob').val()
  };
  //trace("Update user details");
  $.get('includes/updateuserdetails.php', details, function(data) {
    //trace(data);
  });
}

// function to reveal and open the next panel given the current panel
function openNextPanel(currentPanel) {
  var nextPanel = currentPanel.next('.panel');
  nextPanel.show();
  var nextPanelID = nextPanel.prop('id');
  //trace(nextPanelID);
  // wait a bit then click panel heading
  setTimeout("$('#" + nextPanelID + "').find('.panel-heading').click();", 200);
}

function invalidFieldsExist() {
  // look for invalid fields in the expanded panel and flag them and focus first
  $('.panel-collapse.in input.required, .panel-collapse.in textarea.required, .panel-collapse.in select.required').each(function() {
    //trace('Checking field ' + $(this).prop('name') + ' length=' + $(this).val().length + ' hidden=' + $(this).is('[type=hidden]') + ' visible=' + $(this).is(':visible'));
    if (
      !$(this)
        .parent('.form-group')
        .is(':visible')
    )
      return; // return if parent hidden
    $(this)
      .parent('.form-group')
      .removeClass('has-error')
      .find('span.help-block')
      .remove(); // remove any previous error condtion
    if ($(this).val().length == 0 || ($(this).is('[type=checkbox]') && !$(this).is(':checked'))) {
      var id = $(this).prop('id');
      var errorspan = '<span for="' + id + '" class="help-block">This field is required</span>';
      $(this)
        .parent('.form-group')
        .addClass('has-error')
        .append(errorspan);
    }
  });
  var invalidFields = $('.panel-collapse.in').find('.form-group.has-error:visible');
  if (invalidFields.length > 0) {
    var firstInvalidField = invalidFields.first().find('input, textarea, select');
    // the first invalid field could be associated with a btn-group so would be type=hidden
    if (firstInvalidField.attr('type') == 'hidden') {
      firstInvalidField
        .next('.btn-group')
        .find('.btn:first')
        .focus();
    } else {
      firstInvalidField.focus();
    }
    return true;
  }
  // if the panel is estatesharesDetails and the allocated pecentage is < 100%
  if ($('#estatesharesDetails').hasClass('in')) {
    if ($('#percentAllocatedAmount').text() != '100%') return true;
  }
  return false;
}

/* btn-group radio button and checkbox simulation */
// btn-group button groups setup to set value of radio button and optionally hidden input in the same form-group
$(document).on('click', 'div.btn-group button.btn', function(e) {
  e.preventDefault();
  // remove any error classes
  $(this)
    .parent('div.btn-group')
    .parent('div.form-group')
    .removeClass('has-error')
    .find('span.help-block')
    .remove();
  // get the value on the button and the input field
  var newValue = $(this).text();
  // if no text then check for image and get alt attribute of image
  if (!newValue) {
    newValue = $(this)
      .find('img[alt]')
      .attr('alt');
  }
  var theInput = $(this)
    .parent('div.btn-group')
    .parent('div.form-group')
    .find('input[type=hidden]');
  // check for radio simulation or checkbox simulation
  if (
    $(this)
      .parent('.btn-group')
      .hasClass('checkboxgroup')
  ) {
    // checkbox functionality - all can be active
    $(this).toggleClass('active');
    var currentValue = theInput.val();
    // now append the value of the clicked button to the hidden input
    if ($(this).hasClass('active')) {
      // add in the value
      newValue = currentValue.length ? currentValue + ', ' + newValue : newValue;
    } else {
      // remove the value and any comma and space after it
      var re = new RegExp(escapeRegExp(newValue) + ',? *', 'g');
      newValue = currentValue.replace(re, '');
      newValue = newValue.replace(/, *$/, ''); // also strip off trailing comma and space
    }
  } else {
    // radio button functionality - only one can be active
    // remove the active state of all buttons first and remove checked state of all enclosed input[type=radio]
    $(this)
      .parent()
      .find('.btn')
      .removeClass('active');
    $(this).addClass('active');
  }
  // set the value of the input
  theInput.val(newValue);
});

// Add asterisk to each required fields label
function addAsterisk() {
  $('.required').each(function() {
    var theID = $(this).attr('id');
    var theLabel = $('label[for=' + theID + ']');
    theLabel.html(
      theLabel.html() + '<a class="text-danger" data-toggle="tooltip" data-trigger="hover" title="Required field"> *</a>'
    );
  });
}

// add placeholder $ to any currency field
$('.currency').attr('placeholder', '$');

// add placeholder dd/mm/yyyy to any dateAU field
$('.dateAU').attr('placeholder', 'dd/mm/yyyy');

// make persistent popovers dissappear when clicking anywhere on them
$(document).on('click', 'div.popover', function() {
  $(this)
    .parents('div.form-group')
    .find('a.assist')
    .popover('destroy');
});

// common across all the forms
function formSetup() {
  // save form data every 10 minutes - this will keep the session alive also
  setInterval('saveFormData();', 10 * 60 * 1000);

  // make enter key trigger a blur and not submit form
  enterToTab();

  // setup masked input on dateAU fields
  //$('input.dateAU').mask('99/99/9999');

  // limit number fields to only numbers
  $(document).on('keyup', 'input[type="number"]', function() {
    this.value = this.value.replace(/[^0-9\.]/g, '');
  });

  // form validator setup
  var validator = $('form').validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
      // do nothing here because we never actually submit the form
    }
  }); // end of form validation

  // Add multiple value indicator to each .btn-group.checkboxgroup
  $('.btn-group.checkboxgroup').each(function() {
    var theLabel = $(this)
      .parent('.form-group')
      .find('label')
      .first();
    theLabel.html(theLabel.html() + ' <small><em>(you may select multiple values)</em></small>');
  });

  /* bootstrap accordion code */
  // setup validation check on the blur out of the last required input:not([type="radio"]):not([type="checkbox"])
  /*
    $(document).on('blur', '.panel-collapse.in select.required:not(":hidden"):last, .panel-collapse.in textarea.required:not(":hidden"):last, .panel-collapse.in input.required:not([type="radio"]):not(":hidden"):last', function(e) {
      //trace($(this).prop('name') + ' ' + $(this).valid());
      if ($(this).valid()) {
        // if the last field in the panel is valid, then activate the next panel by changing the data-toggle attribute to collapse
        var panelCount = $('div.panel').length;
        var nextPanelIdx = $(this).parents('div.panel').index() + 1;
        var lastPanelIdx = panelCount - 1;
        if (nextPanelIdx < lastPanelIdx) { 
          // activate the next non-optional panel and any optional ones immediately before it
          for (var i = nextPanelIdx; i <= lastPanelIdx; i++) {
            //trace($('div.panel').eq(i).attr('id'));
            $('div.panel').eq(i).find('div.panel-heading').attr('data-toggle', 'collapse');
            if (!$('div.panel').eq(i).hasClass('optional')) break;
          }
        }
      }
    });
  */

  // accordion
  $('#accordion').on('show.bs.collapse', function(e) {
    var thisIndex = $(e.target.parentNode).index();
    var activePanel = $('.panel-collapse.in').parent();
    var activeIndex = $('.panel-collapse.in')
      .parent()
      .index();
    if (invalidFieldsExist()) {
      if (activeIndex < thisIndex) return false;
    }
    // change glyphicon-plus-sign to glyphicon-minus-sign
    $(e.target.parentNode)
      .find('i.glyphicon-plus-sign')
      .removeClass('glyphicon-plus-sign')
      .addClass('glyphicon-minus-sign');
  });

  // scroll clicked accordion heading to top of window and focus first field
  $('#accordion').on('shown.bs.collapse', function(e) {
    $(e.target)
      .find('input, textarea, button')
      .not(':hidden')
      .first()
      .focus();
    //$('html,body').animate({scrollTop: e.target.parentNode.offsetTop - 5}, 100);
  });

  $('#accordion').on('hide.bs.collapse', function(e) {
    saveFormData();
  });

  $('#accordion').on('hidden.bs.collapse', function(e) {
    // change glyphicon-minus-sign to glyphicon-plus-sign
    $(e.target.parentNode)
      .find('i.glyphicon-minus-sign')
      .removeClass('glyphicon-minus-sign')
      .addClass('glyphicon-plus-sign');
  });
  /* end of bootstrap accordion code */

  // download order
  $('a.downloadOrder').click(function(e) {
    e.preventDefault();
    // get the orderid from the hidden input field called orderid
    var orderID = $('#orderID').val();
    // first check the order to make sure it is paid and unexpired
    checkOrder(orderID);
  });

  // preview order
  $('a#previewOrder').click(function(e) {
    e.preventDefault();
    // save data
    saveFormData();
    // get the orderid from the hidden input field called orderid
    var theURL = window.location.protocol + '//' + window.location.host + '/preview.html';
    //trace(theURL);
    var win = window.open(theURL);
    win.document.title = 'Preview';
  });

  // button to copy address to following address fields
  $(document).on('click', 'button.copyAddress', function(e) {
    e.preventDefault();
    var parent = $(this).parents('fieldset');
    $('input[name*="country"]', parent)
      .val($('#country').val())
      .valid();
    $('input[name*="address1"]', parent)
      .val($('#address1').val())
      .valid();
    $('input[name*="address2"]', parent)
      .val($('#address2').val())
      .valid();
    $('input[name*="city"]', parent)
      .val($('#city').val())
      .valid();
    $('input[name*="state"]', parent)
      .val($('#state').val())
      .valid();
    $('input[name*="postcode"]', parent)
      .val($('#postcode').val())
      .valid();
  });

  // if anything in the name paragraph changes we need to rebuild the fullname field
  //$(document).on('change', 'input[name*="title"]', 'input[name*="firstname"], input[name*="othername"], input[name*="surname"]', function () {
  $(document).on('change', 'input.name', function() {
    //trace('name change in field ' + $(this).prop('name'));
    var parent = $(this).parents('.rowitem');
    var title = $('input[name*="title"]', parent).val() > '' ? $('input[name*="title"]', parent).val() + ' ' : '';
    var firstname = $('input[name*="firstname"]', parent).val();
    var middleName =
      $('input[name*="othername"]', parent).val() > '' ? ' ' + $('input[name*="othername"]', parent).val() + ' ' : ' ';
    var surname = $('input[name*="surname"]', parent).val();
    $('input.fullname', parent).val(title + firstname + middleName + surname);
    if ($('input[name="product[singlewill]"]').val() == 'Yes') rebuildBeneficiarySelect();
  });

  // add placeholder mm/yy to any ccexpiry field
  $('.ccexpiry').attr('placeholder', 'mm/yy');

  // add placeholder mm/yy to any ccexpiry field
  $('.creditcard').attr('placeholder', '9999 9999 9999 9999');

  // Add multiple value indicator to each select[multiple] - help string depends on platform
  $('select[multiple]').each(function() {
    var isMac = navigator.platform.match(/Mac/i) ? true : false;
    var isWindows = navigator.platform.match(/Win/i) ? true : false;
    var keyHelp = isMac ? ' &mdash; use &#8984;-Click' : isWindows ? ' &mdash; use CTRL-Click' : '';
    var theLabel = $(this)
      .parent('.form-group')
      .find('label')
      .first();
    theLabel.html(theLabel.html() + ' <small><em>(you may select multiple values' + keyHelp + ')</em></small>');
  });

  // set the Save & Continue Later button to return to home
  $(document).on('click', 'button#saveButton', function(e) {
    e.preventDefault();
    saveFormData();
    window.location = '/mydocs.html';
  });

  // format currency fields correctly
  $(document).on('blur', 'input.legacy', function() {
    var legacy = $(this).val();
    if (legacy > '') {
/*
      $(this).parseNumber({ format: '$#,##0', locale: myLocale });
      $(this).formatNumber({ format: '$#,##0', locale: myLocale });
*/
    }
  });

  // add a new row on gifts, estate, ....
  $('button.add').click(function(e) {
    e.preventDefault();
    var parent = $('div.rowitems', $(this).parents('fieldset'));
    var currentRowCount = $('.rowitem', parent).length - 1; // subtract 1 for  zero row
    var newCount = currentRowCount + 1;
    var oldID = $('.rowitem:last', parent).prop('id');
    //trace(oldID);
    var newID = oldID.replace(/(\d+)$/, currentRowCount + 1);
    // find the last row, clone it, empty the values or checked status, increment the numbers and append to div
    parent.show();
    var newRow = $('.rowitem:last', parent)
      .clone()
      .removeAttr('disabled')
      .prop('id', newID)
      .show()
      .find('input[type="text"], input[type="number"], input[type="email"], input[type="tel"], input[type="hidden"], textarea')
      .val('')
      .end()
      .find('option')
      .removeAttr('selected')
      .end()
      .find('input, textarea, select')
      .each(function() {
        var fieldName = $(this).prop('name');
        var fieldID = $(this).prop('id');
        var newNumber = parseInt(fieldName.match(/\[(\d+)\]/)[1], 10) + 1;
        $(this).prop('name', fieldName.replace(/\[\d+\]/, '[' + newNumber + ']'));
        $(this).prop('id', fieldID.replace(/\d+$/, newNumber));
      })
      .end()
      .find('button')
      .each(function() {
        //trace($(this));
        var fieldID = $(this).prop('id');
        if (fieldID > '') {
          var newNumber = parseInt(fieldID.match(/(\d+)$/)[1], 10) + 1;
          $(this).prop('id', fieldID.replace(/\d+$/, newNumber));
        }
        // make sure button is showing
        $(this).show();
      })
      .removeClass('active')
      .end()
      .find('label')
      .each(function() {
        var labelFor = $(this).prop('for');
        if (labelFor > '') {
          var numberMatch = labelFor.match(/(\d+)$/);
          if (numberMatch != null) {
            var newNumber = parseInt(labelFor.match(/(\d+)$/)[1], 10) + 1;
            $(this).prop('for', labelFor.replace(/\d+$/, newNumber));
          }
        }
      })
      .end()
      .find('legend > span')
      .text(newCount)
      .end()
      .find('div')
      .each(function() {
        var divID = $(this).prop('id');
        if (divID > '') {
          var newNumber = parseInt(divID.match(/(\d+)$/)[1], 10) + 1;
          $(this).prop('id', divID.replace(/\d+$/, newNumber));
        }
      })
      .end()
      .appendTo(parent)
      .find(':input:visible:first')
      .focus();
    if (parent.prop('id') == 'estate') {
      // only relevant to singlewill form
      // setup the value of the share based upon a total of 100% and the number of rows for estate only
      var eachRowValue = parseFloat(1 / newCount);
      $('input.estate', parent)
        .not(':first')
        .each(function() {
          $(this)
            .val(eachRowValue)
            .formatNumber({ format: '0.00%', locale: myLocale });
        });
      // set the value of span id=percentAllocated
      calcTotal();
      // trigger a change on the first (not 0) recipient select to make sure the new select only has the correct enabled/disabled options
      $('select.recipient:eq(1)', parent).change();
    }

    // some of the adds (eg. beneficiaries will need these run
    prefillAutoCompletes();
    cityAutocomplete();
    titleAutoComplete();
    countryAutoComplete();
    addressAutoComplete();
    stateAutoComplete();
    phoneAutoComplete();
    emailAutoComplete();
    nameAutoComplete();

    // re-activate popovers
    triggerPopovers();
  });

  // delete a row
  $(document).on('click', '.remove', function(e) {
    e.preventDefault();
    // find the id to get the row type and number
    var parent = $(this).parents('.rowitem');
    var parentPanel = parent.parents('div.panel');
    var removeID = $(this).attr('id');
    var re = /^(\w+)Remove(\d*)$/;
    var matchArray;
    if ((matchArray = re.exec(removeID))) {
      var prefix = matchArray[1];
      var suffix = matchArray[2];
    } else {
      // hide button if not correct id format
      $(this).hide();
      return false;
    }
    var currentRowCount = $('div#' + prefix).find('.rowitem').length - 1;
    var newCount = currentRowCount - 1;
    $('#' + prefix + 'Row' + suffix).remove();
    // hide header if no rows
    if (newCount == 0) {
      $('div#' + prefix).hide();
      // focus the add button in the parent above div.rowitems
      $('div#' + prefix)
        .parent()
        .find('button.add')
        .focus();
    } else {
      // renumber all the fields on the rows since we may have deleted a row in the middle somewhere
      $('div#' + prefix)
        .find('.rowitem')
        .each(function(index, element) {
          var rowID = $(this).attr('id');
          var re = /^(\w+)(\d+)$/;
          var matchArray;
          if ((matchArray = re.exec(rowID))) {
            var namepart = matchArray[1];
            var suffixpart = matchArray[2];
            var newsuffix = index;
            // change rowID to new one
            $(this).attr({ id: namepart + newsuffix });
            // now look for all descendant elements and replace their IDs also
            $(this)
              .find('legend > span')
              .text(newsuffix);
            $(this)
              .find('*')
              .each(function() {
                // this should find all elements within a child div, then we just search for ID and change it
                var elementID = $(this).attr('id');
                var re = /^(\w+)(\d+)$/;
                var matchArray;
                if ((matchArray = re.exec(elementID))) {
                  var namepart = matchArray[1];
                  $(this).attr({ id: namepart + newsuffix });
                  var elementName = $(this).attr('name');
                  var re = /^(.+)\[\d+\](.+)$/;
                  var matchArray;
                  if ((matchArray = re.exec(elementName))) {
                    var firstPart = matchArray[1];
                    var lastPart = matchArray[2];
                    $(this).attr({ name: firstPart + '[' + newsuffix + ']' + lastPart });
                  }
                }
              });
          }
        });
      // focus the first field of the first (visible) .rowitem which is index 1 (0 is display: none)
      $('.rowitem', 'div#' + prefix)
        .eq(1)
        .find(':input:visible:first')
        .focus();
    }
    // if it is an estate row and there are remaining rows then recalc others
    if (prefix == 'estate') {
      // setup the value of the share based upon a total of 100% and the number of rows
      var eachRowValue = parseFloat(1 / newCount);
      $('input.estate', 'div#estate')
        .not(':first')
        .each(function() {
          $(this)
            .val(eachRowValue)
            .formatNumber({ format: '0.00%', locale: myLocale });
        });
      // set the value of span id=percentAllocated
      calcTotal();
      // also we need to trigger a change on one of the recipient selects to refresh the options list enabled/disabled state
      $('select.recipient:eq(1)', 'table#estate').change();
    }
    // only needed if we delete a beneficiary row but shouldn't hurt to rerun for others also
    if ($('input[name="product[singlewill]"]').val() == 'Yes') rebuildBeneficiarySelect();
  });

  // addrow and deleterow buttons
  $(document).on('click', '.addrow', function(e) {
    e.preventDefault();
    var theRow = $(this).parents('div.row');
    var parent = theRow.parent();
    var oldID = theRow.attr('rownum');
    var newID = parseInt(oldID + 1, 10);
    var newRow = theRow.clone();
    newRow
      .attr('rownum', newID)
      .find('input, textarea')
      .val('')
      .end()
      .find('option')
      .removeAttr('selected')
      .end()
      .find('input, textarea, select')
      .each(function() {
        $(this).prop(
          'name',
          $(this)
            .prop('name')
            .replace(/\[\d+\]/, '[' + newID + ']')
        );
        $(this).prop(
          'id',
          $(this)
            .prop('id')
            .replace(/\d+$/, newID)
        );
      })
      .end()
      .find('label')
      .each(function() {
        $(this).prop(
          'for',
          $(this)
            .prop('for')
            .replace(/\d+$/, newID)
        );
      })
      .end()
      .find('i.delrow')
      .show()
      .end()
      .appendTo(parent)
      .find(':input:visible:first')
      .focus();
    $(this)
      .parent()
      .hide(); // hide the +- on the clicked row
  });

  // delete a row
  $(document).on('click', '.delrow', function(e) {
    e.preventDefault();
    var theRow = $(this).parents('div.row');
    var rowParent = theRow.parent();
    var rowID = theRow.attr('rownum');
    theRow.remove();
    // now show buttons on prior row but only + if there is only one row
    var rowCount = rowParent.find('div.row').length;
    var newLastRow = rowParent.find('div.row:last');
    newLastRow.find('div.form-group:last').show();
  });

  // update user details from form on change of postcode this will only work the first time and not if the user has a session var for postcode set
  $('#postcode').blur(function() {
    updateUser();
  });

  // activate autocompletes
  cityAutocomplete();
  titleAutoComplete();
  countryAutoComplete();
  addressAutoComplete();
  stateAutoComplete();
  phoneAutoComplete();
  emailAutoComplete();
  nameAutoComplete();
}

// this will go through all the beneficiary select elements and rebuild according to the most recently changed information
// only used for singlewill product
function rebuildBeneficiarySelect() {
  var currentBeneficiaries = new Array();
  // this bit takes care of removing options where beneficiary is deleted and updates to names
  $('select[name*="recipient"]').each(function() {
    // rebuild names in Spouse, Children, Other Individuals and Groups - these will have the rebuild attribute on the optgroup
    $(this)
      .find('optgroup[rebuild]')
      .each(function() {
        $(this)
          .find('option')
          .each(function() {
            var value = $(this).attr('value');
            // check that the beneficiary identified by value still exists
            var nameField = $('input[name="' + value.replace(/(\d+)/, '[$1]') + '[fullname]' + '"]');
            if (nameField.length == 0 || nameField.val().length == 0) {
              $(this).remove(); // if the beneficiary is no longer there, remove option element
            } else {
              // rebuild the name
              var relField = $('input[name="' + value.replace(/(\d+)/, '[$1]') + '[relationship]' + '"]');
              value.search(/group/) > -1 || value.search(/spouse/) > -1
                ? $(this).html(nameField.val())
                : $(this).html(nameField.val() + ' (' + relField.val() + ')');
              currentBeneficiaries[value] = true;
            }
          });
        // remove the optgroup if it has no options left
        if ($(this).find('option').length == 0) {
          $(this).remove();
        }
      });
  });
  // this bit will add in new option for beneficiaries just added
  $('input[name*="[fullname]"].beneficiary').each(function() {
    // this will loop through all the beneficiaries that should be in the option list
    var fieldName = $(this).prop('name');
    var theName = $(this).val();
    if (fieldName.search('[0]') > -1) return; // we do not care about the 0 rows
    if ($(this).val().length == 0) return; // we do not want empty names either (like partner when hidden)
    value = fieldName.replace(/\[fullname\]/, ''); // get rid of [fullname]
    value = value.replace(/\[(\d+)\]/, '$1'); // get rid of [n] and replace with just n
    if (!currentBeneficiaries[value]) {
      // not in currentBeneficiaries so this is a new beneficiary so add it into the select in the correct optgroup
      // check that correct optgroup exists and add if not there
      var optgroup = value.replace(/\d+$/, '');
      if ($('select[name*="recipient"]>optgroup[' + optgroup + ']').length == 0) {
        var label = '';
        switch (optgroup) {
          case 'spouse':
            label = 'Partner';
            break;
          case 'child':
            label = 'Children';
            break;
          case 'other':
            label = 'Other Individuals';
            break;
          case 'group':
            label = 'Groups/Organisations';
            break;
          default:
        }
        $('select[name*="recipient"]').append('<optgroup label="' + label + '" ' + optgroup + ' rebuild></optgroup>');
      }
      //  now add in the new option
      //trace('input[name="' + value.replace(/(\d+)/, "\[$1\]") + "\[relationship\]" + '"]');
      var relationship = $('input[name="' + value.replace(/(\d+)/, '[$1]') + '[relationship]' + '"]').val();
      theName = theName + (value.search(/group/) > -1 ? '' : ' (' + relationship + ')');
      //trace(theName);
      var theOptgroup = $('select[name*="recipient"]>optgroup[' + optgroup + ']');
      theOptgroup.append('<option value="' + value + '">' + theName + '</option>');
      currentBeneficiaries[value] = true;
    }
  });
  //trace(currentBeneficiaries);
}

// scroll slowly to next section
$(document).on('click', '.doscroll', function(e) {
  e.preventDefault();
  var whereTo = $($(this).attr('href'));
  $('html,body').animate({ scrollTop: whereTo.offset().top }, 'slow');
});

/* !page scripts */
var pageScripts = new Object();

/* !home */
pageScripts.home = function() {
  /* feedback fadein and out */
  $('div.feedback').each(function() {
    // get the number of rows
    var feedbacks = $(this).find('div.row');
    var lastItem = feedbacks.length - 1;
    setInterval(function nextItem() {
      var activeItem = feedbacks.filter('.active').index();
      feedbacks
        .eq(activeItem)
        .removeClass('active')
        .addClass('inactive');
      if (activeItem == lastItem) activeItem = -1;
      feedbacks
        .eq(activeItem + 1)
        .removeClass('inactive')
        .addClass('active');
    }, 8000);
  });

  // free will form
  $('.freewill').click(function() {
    var sitename = $('#fbsite').attr('content');
    modalForm(
      'Get a Free Basic Will',
      '<p>To receive a copy of our free basic will, please enter your name and email address below and we will email it to you right away.</p>' +
        '<div class="form-group"><input name="firstname" type="text" class="form-control propercase required" placeholder="Firstname..."></div>' +
        '<div class="form-group"><input name="surname" type="text" class="form-control propercase required" placeholder="Surname..."></div>' +
        '<div class="form-group"><input name="emailaddress" type="email" class="form-control required" placeholder="Email address..."></div>' +
        '<p class="small"><em><strong>Please note:</strong> By requesting this Free Basic Will, you consent to receive marketing emails from My Will Online. If you do not consent to receiving marketing emails, please click the CANCEL button below.</em></p>',
      'includes/sendfreewill.php',
      'modalMessage("Email sent", "An email has been sent with your free will attached. Thank you.", 3600);',
      'Sending...',
      'Send my FREE Basic Will'
    );
  });
};

/* !mydocs */
pageScripts.mydocs = function() {
  // users
  $('a.inactive').each(function() {
    // deactivate any anchors that have class inactive and turn off onhover behaviour
    $(this).click(function(e) {
      e.preventDefault();
    });
  });

  // setup the correct order data for the clicked on buttons in the order DIVS
  $('a.editOrder').click(function(e) {
    e.preventDefault();
    // get the orderid from the hidden input field called orderid
    var href = $(this).attr('href');
    var orderID = $('input[name=orderid]', $(this).parents('.orderitem')).val();
    // populate the orderdata using AJAX
    readOrderData(orderID, href);
  });

  // download will
  $('a.downloadOrder').click(function(e) {
    e.preventDefault();
    // get the orderid from the hidden input field called orderid
    var orderID = $('input[name=orderid]', $(this).parents('.orderitem')).val();
    // first check the order to make sure it is paid and unexpired
    checkOrder(orderID);
  });

  // create a new order for given product when createnew button clicked
  $('a.createNewOrder').click(function(e) {
    e.preventDefault();
    var userid = $('input[name=userid]').val();
    var product = $(this).attr('product');
    var timecode = $.now();
    $.get('includes/createneworder.php?timecode=' + timecode, { userid: userid, product: product }, function(data) {
      if (data == 'FAIL') {
        modalMessage('Error', 'Failed to create order', 2700);
      } else if (data == 'SESSIONEXPIRED') {
        alert('Session expired - please login again.');
        location = '/';
      } else {
        var theData = $.parseJSON(data);
        var orderID = theData['id'];
        var formname = theData['formname'];
        readOrderData(orderID, formname + '.html');
      }
    });
  });

  // function to delete an order
  $('a.deleteOrder').click(function(e) {
    e.preventDefault();
    var orderID = $('input[name=orderid]', $(this).parents('.orderitem')).val();
    $.ajax({
      url: 'includes/checksession.php',
      dataType: 'json',
      async: false,
      success: function(data) {
        switch (data.result) {
          case 'SESSIONENDED':
            alert('Session expired - please login again.');
            location = '/mydocs.html';
            break;
          default:
            trace(orderID);
            var formResponse = modalForm(
              'Delete this document?',
              '<p>This document and all entered data will be permanently deleted. Are you sure?</p>',
              'includes/deleteorder.php?orderid=' + orderID,
              'window.location = "/mydocs.html";'
            ); // postURL and successURL
        }
      }
    });
  });

  socialSharing();

  /* scrollto top of page */
  $('a.editOrder:first').focus();
};

/* !faq */
pageScripts.faq = function() {
  $('.question').click(function(e) {
    // find the immediately following paragraph and slide down
    var thisAnswer = $(this).next('div.answer');
    $('div.answer')
      .not(thisAnswer)
      .hide(); // slide all other answers up
    thisAnswer.toggle(); // slide the relevant one down
  });

  $('#accordion').on('show.bs.collapse', function(e) {
    $(e.target.parentNode)
      .find('i.glyphicon-plus-sign')
      .removeClass('glyphicon-plus-sign')
      .addClass('glyphicon-minus-sign');
  });
  $('#accordion').on('hidden.bs.collapse', function(e) {
    $(e.target.parentNode)
      .find('i.glyphicon-minus-sign')
      .removeClass('glyphicon-minus-sign')
      .addClass('glyphicon-plus-sign');
  });
};

/* !feedback */
pageScripts.feedback = function() {
  var referrer;
  // post-submit callback for ajaxSubmits
  function showResponse(responseText, statusText, xhr, $form) {
    closeSpinner();
    modalMessage('Feedback', '<p>' + responseText + '</p>', 2700, 'window.location = "/";');
  }

  $('#feedbackform').validate({
    rules: {
      easeOfUse: 'required',
      valueForMoney: 'required',
      wouldRecommend: 'required'
    },
    submitHandler: function(form) {
      spinner('Sending feedback...');
      $('#feedbackform').ajaxSubmit({ url: 'includes/processfeedback.php', success: showResponse });
    }
  });
};

/* !contact */
pageScripts.contact = function() {
  var referrer;

  // add asterisk to required fields
  addAsterisk();

  // post-submit callback for ajaxSubmits
  function showResponse(responseText, statusText, xhr, $form) {
    closeSpinner();
    modalMessage('Message', '<p>' + responseText + '</p>', 2700, 'window.location="' + referrer + '"');
  }

  $('#contactform').validate({
    rules: {
      contactName: 'required',
      contactEmail: {
        required: true,
        email: true
      },
      contactMessage: 'required',
      contactSum: {
        required: true,
        number: true,
        remote: {
          url: 'includes/antibotcheck.php',
          type: 'POST'
        }
      }
    },
    messages: {
      contactSum: {
        required: 'Please enter the sum of the numbers shown',
        remote: 'Please enter the correct sum of the numbers shown'
      }
    },
    submitHandler: function(form) {
      referrer = $('input[name=referrer]').val();
      spinner('Sending message...');
      $('#contactform').ajaxSubmit({ url: 'includes/sendcontactmessage.php', success: showResponse });
    }
  });

  if ($('#contactName').val() == '') $('#contactName').focus();
  else if ($('#contactEmail').val() == '') $('#contactEmail').focus();
  else if ($('#contactPhone').val() == '') $('#contactPhone').focus();
  else $('#contactMessage').focus();
};

/* !resetpassword */
pageScripts.resetpassword = function() {
  $('#resetPasswordForm').validate({
    rules: {
      password: {
        required: true,
        minlength: 8
      },
      password2: {
        required: true,
        minlength: 8,
        equalTo: '#password'
      }
    },
    messages: {
      password: {
        required: 'Enter a password of at least 8 characters',
        minlength: 'Enter at least 8 characters'
      },
      password2: {
        required: 'Enter a password of at least 8 characters',
        minlength: 'Enter at least 8 characters',
        equalTo: 'Enter the same password as above'
      }
    },

    submitHandler: function(form) {
      //console.log("Submitting password reset");
      form.submit();
    }
  });
};

/* !updateprofile */
pageScripts.updateprofile = function() {
  // post-submit callback for ajaxSubmits
  function showResponse(responseText, statusText, xhr, $form) {
    modalMessage('Message', '<h4>' + responseText + '</h4>', 2700, 'window.location = "/updateprofile.html"');
  }

  // setup validation
  $('#updateProfileForm').validate({
    rules: {
      firstname: 'required',
      surname: 'required',
      email: {
        email: function() {
          $('#emailLabel').removeClass('guidance');
          return true;
        },
        remote: {
          url: 'includes/emailcheck.php',
          type: 'POST'
        }
      },
      email2: {
        required: '#email:filled',
        equalTo: '#email'
      },
      password: {
        minlength: function() {
          $('#passwordLabel').removeClass('guidance');
          return 8;
        }
      },
      password2: {
        required: '#password:filled',
        minlength: 8,
        equalTo: '#password'
      }
    },
    messages: {
      firstname: 'Please enter your first name',
      surname: 'Please enter your family name',
      address: 'Please enter your street or postal address',
      city: 'Please enter your town',
      state: 'Please enter your state',
      postcode: {
        required: 'Please enter your postcode',
        digits: 'Please enter only digits for your postcode'
      },
      email: {
        email: 'Your email address must be in the form something@domain.com',
        remote: jQuery.validator.format('Email "{0}" is already in use')
      },
      email2: {
        required: 'Please confirm your email address',
        equalTo: 'Enter the same email as above'
      },
      password: {
        minlength: jQuery.validator.format('Enter at least {0} characters')
      },
      password2: {
        required: 'Please confirm your password',
        minlength: jQuery.validator.format('Enter at least {0} characters'),
        equalTo: 'Enter the same password as above'
      }
    },

    submitHandler: function(form) {
      //var options = {
      //target:        '#output1',   // target element(s) to be updated with server response
      //beforeSubmit:  showRequest,  // pre-submit callback
      // other available options:
      //url:       url         // override for form's 'action' attribute
      //type:      type        // 'get' or 'post', override for form's 'method' attribute
      //dataType:  null        // 'xml', 'script', or 'json' (expected server response type)
      //clearForm: true        // clear all form fields after successful submit
      //resetForm: true        // reset the form after successful submit
      // $.ajax options can be used here too, for example:
      //timeout:   3000
      //success:       showResponse  // post-submit callback
      //};

      $('#updateProfileForm').ajaxSubmit({ url: 'includes/updateuserprofile.php', success: showResponse });
    }
  });

  // deleteAccount link pressed
  $('a#deleteAccount').click(function(e) {
    e.preventDefault();
    var formResponse = modalForm(
      'Are you sure you wish to permanently remove your account?',
      '<p>You will not be able to recover any previously entered information. It will be permanently deleted. Are you sure?</p>',
      'includes/removeaccount.php',
      'window.location = "logout.html";'
    );
  });

  // disable copy of fields with disablecopy class
  $(document).on('cut copy paste dragstart', 'input.disablecopy', function(e) {
    e.preventDefault();
  });

  // mask the ABN field correctly
  $('input.abn').mask('99 999 999 999');

  // city auto complete
  cityAutocomplete();
};

/* !order */
pageScripts.order = function() {
  // recalculate total when button id=couponCalc is clicked and redisplay this page
  // uses validate below to update couponcode if it exists
  $('#couponCalc').click(function() {
    if ($('#couponCode').val()) $('#orderForm').attr({ action: 'order.html' });
    else return false;
  });

  $('#orderForm').validate({
    rules: {
      couponCode: {
        remote: {
          url: 'includes/couponcheck.php',
          type: 'POST'
        }
      }
    },
    messages: {
      couponCode: '<span class="error">Please enter a valid coupon code</span>'
    },
    submitHandler: function(form) {
      spinner('Processing...');
      form.submit();
    }
  });

  // cancel the order and return to home
  $('#cancelOrder').click(function(e) {
    // go back to editing the order
    e.preventDefault();
    //var orderID = $('#orderid').text();
    //readOrderData(orderID, 'singlewill.html');
    window.location = '/mydocs.html';
  });

  // couponcode field
  $('#couponCode').focusin(function(e) {
    $('#cc').slideUp();
    $('#poli').slideUp();
  });

  // POLi order
  $('#polibtn').click(function(e) {
    $('#cc').hide();
    $('#paytype').val('poli');
    $('#poli').show(function() {});
  });

  // paypal order so get fields and submit
  $('#paypalbtn').click(function(e) {
    e.preventDefault();
    $('#cc').hide();
    $('#poli').hide();
    $.get(
      'includes/paypalorder.php',
      function(data) {
        $('#orderid').after(data.inputfields);
        spinner('Redirecting to Paypal');
        $('#orderForm')
          .attr('action', data.paypalurl)
          .submit();
      },
      'json'
    );
  });

  // cba order so get fields and submit
  $('#cbabtn').click(function(e) {
    $('#poli').hide();
    $('#paytype').val('commweb');
    $('#cc').show(function() {
      $('input#ccnumber').focus();
    });
  });

  // setup masked input on ccnumber fields
  $('input.ccnumber').mask('9999 9999 9999 9999');
  // setup masked input on ccexpiry
  $('input.ccexpiry').mask('99/99');
  // setup masked input on ccv
  $('input.ccv2').mask('999');
  // add placeholder mm/yy to any ccexpiry field
  $('.ccexpiry').attr('placeholder', 'MMYY');
};

pageScripts.quickwillorder = function() {
  // cancel the order and return to will page
  $('#cancelOrder').click(function(e) {
    // go back to editing the will
    e.preventDefault();
    window.location = '/quickwill.html';
  });

  $('#orderForm').validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
      spinner('Processing payment...');
      form.submit();
    }
  });

  // setup masked input on ccnumber fields
  $('input.ccnumber')
    .mask('9999 9999 9999 9999')
    .attr('placeholder', '9999 9999 9999 9999');
  // setup masked input on ccexpiry
  $('input.ccexpiry')
    .mask('99/99')
    .attr('placeholder', 'MMYY');
  // setup masked input on ccv
  $('input.ccv2')
    .mask('999')
    .attr('placeholder', '999');
};

/* !organisaton */
pageScripts.organisation = function() {
  var alreadySubmitted = false;

  /* orgRegisterForm part */
  $('#orgRegisterForm').validate({
    rules: {
      email: {
        remote: {
          url: 'includes/emailcheck.php',
          type: 'POST'
        }
      },
      email2: {
        equalTo: '#registerEmail'
      },
      password: {
        minlength: 8
      },
      password2: {
        minlength: 8,
        equalTo: '#registerPassword'
      },
      logo: {
        required: true,
        accept: 'image/png,image/jpeg,image/gif',
        filesize: 200000
      }
    },

    messages: {
      email: {
        remote: 'This email address is already registered'
      },
      email2: {
        equalTo: 'This email addresses is different. Please enter the same address to confirm that it is correct'
      },
      logo: {
        accept: 'Please select a PNG, JPG or GIF file only'
      }
    },

    submitHandler: function(form) {
      if (!alreadySubmitted) {
        spinner('Thank you...');
        // now go ahead and submit
        form.submit();
        alreadySubmitted = true;
      }
    }
  });

  $('#orgRegisterButton').click(function(e) {
    e.preventDefault();
    $('#orgRegisterForm').submit();
  });

  // mask the ABN field correctly
  $('input.abn').mask('99 999 999 999');

  // city auto complete
  cityAutocomplete();

  // check logo upload size
  /*
      $(document).on('change', '#logo', function() {
        //this.files[0].size gets the size of your file.
        alert(this.files[0].size);
      });    
  */
};

/* !start */
pageScripts.start = function() {
  var alreadySubmitted = false;

//   loadFB(); // so FB login will work

  /* registerForm part */
  $('#registerForm').validate({
    rules: {
      email: {
        remote: {
          url: 'includes/emailcheck.php',
          type: 'POST'
        }
      },
      email2: {
        equalTo: '#registerEmail'
      },
      password: {
        minlength: 8
      },
      password2: {
        minlength: 8,
        equalTo: '#registerPassword'
      }
    },

    messages: {
      email: {
        remote: 'This email address is already registered'
      },
      email2: {
        equalTo: 'This email addresses is different. Please enter the same address to confirm that it is correct.'
      }
    },

    submitHandler: function(form) {
      if (!alreadySubmitted) {
        spinner('Thank you...');
        // now go ahead and submit
        $('#registerForm').attr('action', 'includes/registeruser.php');
        form.submit();
        alreadySubmitted = true;
      }
    }
  });

  /* loginForm part */
  $('#loginForm').validate({
    rules: {
      email: {
        required: true,
        email: true
      },
      password: {
        required: true,
        remote: {
          url: 'includes/loguserin.php',
          type: 'POST',
          data: {
            email: function() {
              return $('#email').val();
            }
          }
        }
      }
    },
    messages: {
      password: {
        remote: 'Invalid email address or password'
      }
    },
    submitHandler: function() {
      // check for passed in pgref to link to after login
      var pgref = '';
      pgref = $('#pgref').val();
      window.location = pgref > '' ? pgref + '.html' : '/mydocs.html';
    },
    onkeyup: false
  });

  $('#loginButton').click(function(e) {
    e.preventDefault();
    $('#loginForm').submit();
  });

  var email;
  $('#lostpw').click(function(e) {
    e.preventDefault();
    email = $('#email').val();
    formHTML =
      '<div class="form-group"><label>Please enter your email address</label><input class="form-control" type="email" name="emailaddress" placeholder="example@domain.com" value="' +
      email +
      '"></div>';
    var formResponse = modalForm(
      'Password Reset',
      formHTML,
      '/includes/pwreset1.php',
      'modalMessage("Email sent", "An email has been sent with your password reset link. Thank you.", 3600);'
    );
  });

  $('input[type="text"]:first').select();

  /* FB login part */

/*
  $('#FBLoginButton').click(function(e) {
    e.preventDefault();
    if (FBloginstatus !== 'connected') {
      // The person is not logged into Facebook or they have not authorised the app
      FB.login(
        function(response) {
          spinner('Logging in with Facebook...');
          if (response.status === 'connected') {
            FB.api('/me', 'GET', { fields: 'name,birthday,gender,timezone,email' }, function(response) {
              site_login(response);
            });
          } else {
            closeSpinner();
          }
        },
        { scope: 'email' }
      );
    } else {
      FB.api('/me', 'GET', { fields: 'name,birthday,gender,timezone,email' }, function(response) {
        site_login(response);
      });
    }
  });
*/

  // disable copy of fields with disablecopy class
  $(document).on('cut copy paste dragstart', 'input.disablecopy', function(e) {
    e.preventDefault();
  });

  // display message about need for cookies and redirect to home if disabled
  if (!navigator.cookieEnabled) {
    modalMessage(
      'Cookies Required',
      "<p class='large'>This site is not functional when you disable the use of Cookies in your browser.</p>",
      3000,
      "location = '/notice.html';"
    );
  }
};
pageScripts.login = pageScripts.start;

/* affiliate program */
pageScripts.affiliateprogram = function() {
  $('#joinAffiliateProgram').click(function(e) {
    e.preventDefault();
    // call routine to add affiliate joined date to user
    spinner('Enrolling in affiliate program...');
    $.get('includes/joinaffiliateprogram.php', function(data) {
      // if successful then hide the join section and reveal other sections or display message
      closeSpinner();
      if (data == 'SUCCESS') {
        window.location = 'affiliateprogram.html'; // redisplay to show appropriate sections
      } else {
        modalMessage(
          'Error joining program',
          'There was an error joining the affiliate program. Please try again.',
          2700,
          '',
          'OK',
          'modal-sm'
        );
      }
    });
  });

  /* show terms and conditions in modal popup */
  $('#affiliateTermsAndConditions').click(function(e) {
    e.preventDefault();
    var data = $('#affiliateTerms').html();
    modalMessage('Affiliate Program Terms and Conditions', data, 0, '', 'OK', 'modal-lg');
  });

  /* show payment history in modal popup */
  if ($('#paidCount').text() != '0') {
    $('#viewPaymentHistory').removeAttr('disabled');
    $('#viewPaymentHistory').click(function(e) {
      e.preventDefault();
      var data = $('#paymentHistory').html();
      modalMessage('Affiliate Program Payments History', data, 0, '', 'OK', 'modal-lg');
    });
  } else {
    $('#viewPaymentHistory').attr('disabled', 'disabled');
  }

  /* request payment */
  if ($('#payableCount').val() != 0) {
    $('#requestPayment').removeAttr('disabled');
    $('#requestPayment').click(function(e) {
      e.preventDefault();
      var paymentdays = $('#paymentdays').text();
      var paymentAmount = $('#payableAmount').val();
      var formHTML =
        "<h3>Payment Request</h3><div style='text-align: left;'><p>Rewards payments are made using PayPal Send Money. We will use the email address you have on file to send your payment of " +
        paymentAmount +
        ' within ' +
        paymentdays +
        ' business days.</p><p>You will receive a payment notification from PayPal Australia directly once the payment has been processed.</p><p>If you do not receive a payment notification email within 7 business days, please contact us.</p></div>';
      var formResponse = modalForm(
        'Affiliate Program Payment Request',
        formHTML,
        '/includes/paymentrequest.php',
        'modalMessage("Payment Request Successful", "Your payment request has been submitted. You should receive the funds into your Paypal account soon.", 3600, "window.location = \'affiliateprogram.html\'");',
        'Submitting payment request...'
      );
    });
  } else {
    $('#requestPayment').attr('disabled', 'disabled');
  }

  $('#l1lnks').click(function(e) {
    e.preventDefault();
    var data = $('#level1names').html();
    modalMessage('Level 1 Referees', '<strong>Name (number level 2)</strong><br>' + data, 0, '', 'OK', 'modal-md');
  });

  socialSharing();
};

/* !online will form */
pageScripts.singlewill = function() {
  spinner('Loading document data...', null, null, 0);

  // make enter key trigger a blur and not submit form
  enterToTab();

  // common across all forms
  formSetup();

  // Add asterisk to each required fields label
  addAsterisk();

  // add placeholder $ to any currency field
  $('.currency').attr('placeholder', '$');

  // add placeholder dd/mm/yyyy to any dateAU field
  $('.dateAU').attr('placeholder', 'dd/mm/yyyy');

  // display the children details section and under 18 question if children Yes
  $('#hasChildrenYes').click(function() {
    $('#childrenDetails').show();
    $('#hasChildrenYoungDiv').show();
    $('.onlywithchildren').show();
    $('option[value="TestatorsChildren"]').removeAttr('disabled');
    $('#childCount')
      .val('1')
      .change();
  });
  $('#hasChildrenNo').click(function() {
    $('#childrenDetails')
      .hide()
      .find('input,textarea')
      .val('')
      .end()
      .find('button.active')
      .removeClass('active');
    $('#hasChildrenYoungDiv')
      .hide()
      .find('.btn')
      .removeClass('active')
      .end()
      .find('input')
      .val('');
    $('.onlywithchildren').hide();
    $('option[value="TestatorsChildren"]')
      .removeAttr('selected')
      .attr('disabled', true);
    $('#trustForMinorsSection')
      .hide()
      .find('.btn')
      .removeClass('active')
      .end()
      .find('input')
      .val('');
    $('#childCount')
      .val('0')
      .change();
    $('#guardians')
      .hide()
      .find('input,textarea')
      .val('')
      .end()
      .find('button.active')
      .removeClass('active');
    rebuildBeneficiarySelect();
  });

  // display the trust for minors section if hasChildrenYoung == yes
  $('#hasChildrenYoungYes').click(function() {
    $('#trustForMinorsSection').show();
    $('#trustForMinorsSubsection').show();
    $('#trustForMinors').val('Yes');
    $('#trustForMinorsYes').addClass('active');
    $('#trustForMinorsNo').removeClass('active');
    $('#ageOfInheritance').val('18');
    $('#guardians').show();
  });
  $('#hasChildrenYoungNo').click(function() {
    $('#trustForMinorsSection')
      .hide()
      .find('.btn')
      .removeClass('active')
      .end()
      .find('input')
      .val('');
    $('#trustForMinorsSubsection').hide();
    $('#trustForMinors').val('No');
    $('#trustForMinorsNo').addClass('active');
    $('#trustForMinorsYes').removeClass('active');
    $('#guardians')
      .hide()
      .find('input,textarea')
      .val('')
      .end()
      .find('button.active')
      .removeClass('active');
  });

  // display the ageOfInheritance section if trust for minors
  $('#trustForMinorsYes').click(function() {
    $('#trustForMinorsSubsection').show();
  });
  $('#trustForMinorsNo').click(function() {
    $('#trustForMinorsSubsection').hide();
  });

  // display the provision for pets section if hasPets == yes and retrieve pet carer org info to session
  $('#hasPetsYes').click(function() {
    $('#pets').show();
  });
  $('#hasPetsNo').click(function() {
    $('#pets')
      .hide()
      .find('input,textarea')
      .val('')
      .end()
      .find('button.active')
      .removeClass('active');
  });

  // show the partner for executor question
  $('#spouseYes').click(function() {
    // show the partnerForExecutor question
    $('#partnerForExecutor').show();
    $('#spouseDetails').show();
    $('#spouseRelationship').val('partner');
    // mirrorwill section
    $('#mirrorwill').show();
    $('#mirrorwillYes').click();
    $('.epoapartner').show();
  });
  $('#spouseNo').click(function() {
    // hide the partnerForExecutor question
    $('#partnerForExecutor')
      .hide()
      .find('.btn')
      .removeClass('active')
      .end()
      .find('input')
      .val('');
    $('#spouseDetails')
      .hide()
      .find('input,textarea')
      .val('')
      .end()
      .find('button.active')
      .removeClass('active');
    // mirrorwill section
    $('#mirrorwillNo').click();
    $('#mirrorwill')
      .hide()
      .find('input,textarea')
      .val('')
      .end()
      .find('button.active')
      .removeClass('active');
    $('.epoapartner')
      .hide()
      .find('input,textarea')
      .val('')
      .end()
      .find('button.active')
      .removeClass('active');
    rebuildBeneficiarySelect();
  });

  // Mirrorwill section
  $('#mirrorwillYes').click(function() {
    $('input[name="product[mirrorwill]"]').val('Yes');
    $('.epoapartner').show();
  });
  $('#mirrorwillNo').click(function() {
    $('input[name="product[mirrorwill]"]').val('No');
    $('.epoapartner').hide();
  });

  // EPoA section
  $('#enduringYes').click(function() {
    $('input[name="product[enduring]"]').val('Yes');
    $('#state').blur(); // run the auto epoaState function below
    $('#epoaDetails')
      .show()
      .find('input,textarea')
      .first()
      .focus();
  });
  $('#enduringNo').click(function() {
    $('input[name="product[enduring]"]').val('No');
    $('#epoaDetails')
      .hide()
      .find('input,textarea')
      .val('')
      .end()
      .find('button.active')
      .removeClass('active');
  });

  // set epoaState to testator state if empty
  $('#state').blur(function() {
    if ($('#epoaState').val().length == 0) {
      var theState = $(this).val();
      if (/^(NSW|NT|QLD|VIC|SA|WA|TAS|ACT)$/.test(theState)) {
        $('#epoaState').val($(this).val());
        $('#epoaState').autocomplete({ disabled: true });
        $('#epoaState').blur(); // trigger actions on change of epoaState
        $('#epoaState').autocomplete({ disabled: false });
      }
    }
  });

  // copy partners detail to executor if requested and make the fields readonly
  $('#partnerExecutorYes').click(function() {
    $('#executorFirstname1')
      .val($('#spouseFirstname').val())
      .valid();
    $('#executorOthername1')
      .val($('#spouseOthername').val())
      .valid();
    $('#executorSurname1')
      .val($('#spouseSurname').val())
      .valid();
    $('#executorSurname1').change(); // trigger change to update fullname field
    $('#executorEmail1')
      .val($('#spouseEmail').val())
      .valid();
    $('#executorPhone1')
      .val($('#spousePhone').val())
      .valid();
    $('#executorCountry1')
      .val($('#spouseCountry').val())
      .valid();
    $('#executorAddressA1')
      .val($('#spouseAddressA').val())
      .valid();
    $('#executorAddressB1')
      .val($('#spouseAddressB').val())
      .valid();
    $('#executorCity1')
      .val($('#spouseCity').val())
      .valid();
    $('#executorState1')
      .val($('#spouseState').val())
      .valid();
    $('#executorPostcode1')
      .val($('#spousePostcode').val())
      .valid();
    $('#executorRow1').attr('disabled', true);
    $('#executorRemove1').hide();
  });
  $('#partnerExecutorNo').click(function() {
    $('input', '#executorRow1').val('');
    $('#executorRow1').removeAttr('disabled');
    $('#executorRemove1').show();
  });

  // if there is a partner and the user requested that they have their partner as an executor, then update executor1 details if
  // partner details change
  $('#spouseDetails input').blur(function() {
    if ($('#partnerExecutor').val() == 'Yes') $('#partnerExecutorYes').click();
  });

  $(document).on('change', 'input[name*="[fullname]"], input[name*="relationship"]', function() {
    rebuildBeneficiarySelect();
  });

  // on select of beneficiary
  // if a recipient is selected check for group and disable altrecipient
  $(document).on('change', 'select.recipient', function() {
    // if the type of recipient is a group, then hide altrecipient section
    var thisOptGroup = $(this)
      .find('option:selected')
      .parent()
      .attr('label');
    if (thisOptGroup != undefined) {
      var re = /^(\w+)Recipient(\d*)$/;
      var thisID = $(this).attr('id');
      var matchArray;
      if ((matchArray = re.exec(thisID))) {
        var prefix = matchArray[1];
        var suffix = matchArray[2];
      }
      if (thisOptGroup.substring(0, 5) == 'Group') {
        $('#' + prefix + 'AltRecipient' + suffix)
          .find('option:selected')
          .removeAttr('selected')
          .end()
          .parents('div.form-group')
          .hide();
      } else {
        // auto select the first altrecipient option if there is currently none selected - new rows
        if (
          typeof $('#' + prefix + 'AltRecipient' + suffix) != 'undefined' &&
          $('#' + prefix + 'AltRecipient' + suffix).val() == ''
        ) {
          //$('#' + prefix + 'AltRecipient' + suffix).find("option:eq(1)").attr('selected','selected').parents('div.form-group').show();
        }
      }
    }
    // disable selected option in other selects for estate only
    if (prefix == 'estate') {
      // find all the selected values and enable all but the selected ones in all the selects
      var values = {};
      // get the values
      $('select.recipient', '#estateDetails').each(function() {
        values[$('option:selected', $(this)).val()] = true;
      });
      // now enable all options and then disable the ones in the value array
      $('select.recipient', '#estateDetails').each(function() {
        var re = /^(\w+)Recipient(\d*)$/;
        var thisID = $(this).attr('id');
        var matchArray;
        if ((matchArray = re.exec(thisID))) {
          var prefix = matchArray[1];
          var suffix = matchArray[2];
        }
        if (suffix == 0) return;
        var selectedvalue = $(this).val();
        var cnt = $('option', this).removeAttr('disabled').length;
        for (var i = 0; i < cnt; i++) {
          // do not change the hidden 0th one
          var value = $('option', this)
            .eq(i)
            .val();
          if (value in values && value != selectedvalue && value != '')
            $('option', this)
              .eq(i)
              .attr('disabled', 'disabled');
        }
      });
    }
  });

  // on select of alternate beneficiary
  // if 'other' is selected make 'condition' field required
  $(document).on('change', 'select.altrecipient', function() {
    // if the type of recipient is a group, then hide altrecipient section
    var optSelected = $(this)
      .find('option:selected')
      .val();
    var re = /^(\w+)AltRecipient(\d*)$/;
    var thisID = $(this).attr('id');
    var matchArray;
    if ((matchArray = re.exec(thisID))) {
      var prefix = matchArray[1];
      var suffix = matchArray[2];
    }
    var theConditionID = prefix + 'Condition' + suffix;
    var theLabel = $('label[for=' + theConditionID + ']');
    if (optSelected == 'Other') {
      $('#' + theConditionID).addClass('required');
      theLabel.html(
        theLabel.html() + '<a class="text-danger" data-toggle="tooltip" data-trigger="hover" title="Required field"> *</a>'
      );
    } else {
      $('#' + theConditionID).removeClass('required');
      var theLabelText = theLabel.html();
      theLabel.html(theLabelText.replace(/<a class="text-danger".*>\s\*<\/a>/, ''));
    }
  });

  // on select of pet carer
  $(document).on('change', 'select.petcarer', function() {
    var optSelected = $(this)
      .find('option:selected')
      .val();
    var re = /^petCarer(\d*)$/;
    var thisID = $(this).attr('id');
    var matchArray;
    if ((matchArray = re.exec(thisID))) {
      var suffix = matchArray[1];
    }
    var theCareDetailsID = 'petCareDetails' + suffix;
    var theLegacyAmountDivID = 'petLegacyAmountDiv' + suffix;
    var theLegacyAmountID = 'petLegacyAmount' + suffix;
    var theLegacyAmountLabel = $('label[for=' + theLegacyAmountID + ']');
    var theLabel = $('label[for=' + theCareDetailsID + ']');
    if (optSelected == 'Other') {
      // make pet caredetails required
      $('#' + theCareDetailsID).addClass('required');
      $('#' + theLegacyAmountID).removeClass('required');
      $('#' + theLegacyAmountDivID)
        .find('input')
        .val('')
        .end()
        .hide();
      theLegacyAmountLabel.html(theLegacyAmountLabel.html().replace(/<a class="text-danger".*>\s\*<\/a>/, ''));
      theLabel.html(
        theLabel.html() + '<a class="text-danger" data-toggle="tooltip" data-trigger="hover" title="Required field"> *</a>'
      );
    } else {
      if (isNumber(optSelected) || optSelected.substr(0, 5) == 'group') {
        // this would imply a pet carer org or non-individual so make petLegacyAmount required
        if (!$('#' + theLegacyAmountID).hasClass('required')) {
          // do not add it a second time
          $('#' + theLegacyAmountID).addClass('required');
          theLegacyAmountLabel.html(
            theLegacyAmountLabel.html() +
              '<a class="text-danger" data-toggle="tooltip" data-trigger="hover" title="Required field"> *</a>'
          );
        }
      } else {
        $('#' + theLegacyAmountID).removeClass('required');
        theLegacyAmountLabel.html(theLegacyAmountLabel.html().replace(/<a class="text-danger".*>\s\*<\/a>/, ''));
      }
      $('#' + theCareDetailsID).removeClass('required');
      $('#' + theLegacyAmountDivID).show();
      theLabel.html(theLabel.html().replace(/<a class="text-danger".*>\s\*<\/a>/, ''));
    }
    // if a pet carer org is selected, ie. optSelected is number show info under selection field
    if (isNumber(optSelected)) {
      $.getJSON('includes/getpetcarerinfo.php', { carerid: optSelected }, function(data) {
        $('#petcarerinfo' + suffix).html(data.info);
      });
    } else {
      $('#petcarerinfo' + suffix).html('');
    }
  });

  // copy selected assist clause to associated text area
  $(document).on('click', 'p.assistclause', function() {
    var parentDiv = $(this).parents('div.form-group');
    parentDiv
      .find('textarea')
      .focus()
      .val($(this).text());
  });

  // function to setup activate assist links
  function activateAssists(index, element) {
    var assistID = element.prop('id');
    if (assistID > '') {
      var newNumber = parseInt(assistID.match(/(\d+)$/)[1], 10) + 1;
      element.prop('id', assistID.replace(/\d+$/, newNumber));
    }
    assistID = element.prop('id'); // reget it with the new number
    var re = /^(\w+)Assist(\d*)$/;
    var matchArray;
    if ((matchArray = re.exec(assistID))) {
      var prefix = matchArray[1];
      var suffix = matchArray[2];
    }
    // if there is no div with an id of prefixAssistHTML hide assist link and then return else setup links as popovers with content
    var htmlID = '#' + prefix + 'AssistHTML';
    if ($(htmlID).length > 0) {
      var assistClauses = $(htmlID).html();
      // turn link into a popover
      element.popover('destroy'); // get rid of the old popover because we may be changing content
      element.show().popover({
        html: true,
        placement: function(context, source) {
          // popover will display below if the link is in the top half else above
          var offset = $(source).offset();
          var posY = offset.top - $(window).scrollTop();
          var windowHeight = $(window).height();
          if (posY < windowHeight / 2) {
            return 'bottom';
          }
          return 'top';
        },
        title: 'Select an example clause',
        content: function() {
          return assistClauses;
        },
        trigger: 'click'
      });
    }
  }

  // if the recipient is a group/organisation, then disable Alt Recipient and make its value null
  // check selected recipient and disable altrecipient if group
  $('select.recipient').each(function() {
    var thisOptGroup = $(this)
      .find('option:selected')
      .parent()
      .attr('label');
    if (thisOptGroup != undefined) {
      var re = /^(\w+)Recipient(\d*)$/;
      var thisID = $(this).attr('id');
      var matchArray;
      if ((matchArray = re.exec(thisID))) {
        var prefix = matchArray[1];
        var suffix = matchArray[2];
      }
      if (thisOptGroup.substring(0, 5) == 'Group') {
        $('#' + prefix + 'AltRecipient' + suffix)
          .find('option:selected')
          .removeAttr('selected')
          .end()
          .parents('div.form-group')
          .hide();
      } else {
        // auto select the first altrecipient option if there is currently none selected - new rows
        if (
          typeof $('#' + prefix + 'AltRecipient' + suffix) != 'undefined' &&
          $('#' + prefix + 'AltRecipient' + suffix).val() == ''
        ) {
          $('#' + prefix + 'AltRecipient' + suffix)
            .find('option:eq(1)')
            .attr('selected', 'selected')
            .parents('div.form-group')
            .show();
        }
      }
    }
    // disable selected option in other selects for estate only
    if (prefix == 'estate') {
      // make sure that selected options are disabled in the other selects
      var value = $(this).val();
      var allRecipients = $('select.recipient', '#estateDetails');
      var cnt = allRecipients.length;
      var thisIndex = allRecipients.index(this);
      for (var i = 0; i < cnt; i++) {
        if (i != thisIndex) $("option[value='" + value + "']", allRecipients.get(i)).attr('disabled', 'disabled');
      }
    }
  });

  // Estate shares section

  // format share fields as percentage and update total percentage
  function formatShareFields(element) {
    element.parseNumber({ format: '0.00%', locale: myLocale, round: false });
    element.formatNumber({ format: '0.00%', locale: myLocale });
  }

  // format share fields on startup
  $('input.estate')
    .not(':first')
    .each(function() {
      formatShareFields($(this));
    });
  // and calcTotal
  calcTotal();

  // when changing the share fields format as percentage and recalcTotals
  $(document).on('blur', 'input.estate', function() {
    // add in a % if it is not already there then format as a percentage
    var val = $(this).val();
    if (val.slice(-1) != '$') $(this).val(val + '%');
    formatShareFields($(this));
    calcTotal();
  });

  // disable guardian tab
  if ($('#hasChildrenYoungNo').is(':checked')) disableGuardianTab();

  $(document).on('click', 'button[id^="childRelationBtn"]', function() {
    // set the relationship accordingly
    rebuildBeneficiarySelect();
  });

  // intially activate all the assist links on document load and then at each add
  $(document).on('click', 'a.assist', function(e) {
    e.preventDefault();
    // get rid of all other popovers
    $('a.assist').popover('destroy');
    $('div.popover').remove();
    var selectedRecipient = $(this)
      .parents('.rowitem')
      .find('select.recipient option:selected')
      .text();
    selectedRecipient = selectedRecipient.indexOf('---') == -1 ? selectedRecipient : 'BENEFICIARY';
    var theName = $(this)
      .parents('.rowitem')
      .find('input.name')
      .val();
    theName = typeof theName != 'undefined' ? theName : 'NAME';
    var assistType = $(this).attr('assist');
    // if there is no div with an id of prefixAssistHTML hide assist link and then return else setup links as popovers with content
    var htmlID = '#' + assistType + 'AssistHTML';
    if ($(htmlID).length > 0) {
      var assistClauses = $(htmlID).html();
      // replace [recipient] with selectedRecipient name
      assistClauses = assistClauses.replace(/\[\[Recipient\]\]/g, selectedRecipient);
      assistClauses = assistClauses.replace(/\(.*\) /g, ''); // get rid of (friend), (cousin), etc
      // replace [name] with the name if there
      assistClauses = assistClauses.replace(/\[\[Name\]\]/g, theName);
      // turn link into a popover
      $(this).popover({
        html: true,
        placement: function(context, source) {
          // popover will display below if the link is in the top half else above
          var offset = $(source).offset();
          var posY = offset.top - $(window).scrollTop();
          var windowHeight = $(window).height();
          if (posY < windowHeight / 2) {
            return 'bottom';
          }
          return 'top';
        },
        title: '<div class="close">x</div>Select an example clause',
        content: function() {
          return assistClauses;
        },
        trigger: 'manual'
      });
      $(this).popover('show');
    }
  });

  // if orderpaid = 0 and there is an executor, then show Preview button
  $('#accordion').on('hide.bs.collapse', function(e) {
    if ($('input[name=orderpaid]').val() == 0 && $('#executorName1').val() > '') {
      $('#previewOrder').show();
    }
  });

  // initial build of beneficiary list
  rebuildBeneficiarySelect();

  // show page, save data and close spinner now that all the js is run
  setTimeout("$('#singlewill').css('visibility', 'visible');saveFormData();closeSpinner();", 1000);
}; // end of singlewill form

/* !quickwill */
pageScripts.quickwill = function() {
  var action = '';

  // trigger initial save if orderid is null and testatorFullName has data and is blurred
  $('#testatorFullName').blur(function() {
    var orderID = $('#orderid').val();
    var name = $(this).val();
    if (orderID.length == 0 && name.length > 0) {
      saveQuickWillFormData(false);
    }
  });

  // make enter key trigger a blur and not submit form
  enterToTab();

  // setup masked input on dateAU fields
  $('input.dateAU').mask('99/99/9999');

  // limit number fields to only numbers
  $(document).on('keyup', 'input[type="number"]', function() {
    this.value = this.value.replace(/[^0-9\.]/g, '');
  });

  $(document).on('blur', 'input.estateshare', function() {
    this.value = this.value.replace(/[^0-9\.]/g, '');
    this.value = this.value + '%';
  });
  $(document).on('focus', 'input.estateshare', function() {
    this.value = this.value.replace(/%/, '');
  });

  // Add asterisk to each required fields label
  addAsterisk();

  // add placeholder $ to any currency field
  $('.currency').attr('placeholder', '$');

  // add placeholder dd/mm/yyyy to any dateAU field
  $('.dateAU').attr('placeholder', 'dd/mm/yyyy');

  function showResponse(responseText, statusText, xhr, $form) {
    closeSpinner();
    modalMessage('Message', '<p>' + responseText + '</p>', 2700);
  }

  // form validator setup
  var validator = $('form').validate({
    rules: {},
    messages: {},
    submitHandler: function(form) {
      saveQuickWillFormData(false);
      if (action == 'preview') {
        trace('will preview');
        var theURL = window.location.protocol + '//' + window.location.host + '/quickwillpreview.html';
        var win = window.open(theURL);
        win.document.title = 'Will Preview';
      }
      if (action == 'purchase') {
        trace('will order');
        window.location = '/quickwillorder.html';
      }
      if (action == 'quickwilldownload') {
        trace('will download');
        var theURL = window.location.protocol + '//' + window.location.host + '/quickwilldownload.html';
        var win = window.open(theURL, 'download');
        win.document.title = 'Will Download';
      }
      if (action == 'quickwillemail') {
        trace('will email');
        $('#testatorFullName').focus();
        spinner('Creating your will..');
        // stay on this page and email via AJAX call
        $(this).ajaxSubmit({
          url: 'includes/quickwillemail.php',
          success: function(response) {
            closeSpinner();
            modalMessage('Email sent', '<p>Thank you. Your will has been sent to ' + $('#testatorEmail').val() + '</p>', 2700);
          }
        });
      }
    }
  }); // end of form validation

  // display the trust for minors section if hasChildrenYoung == yes
  $('#hasChildrenYoungYes').click(function() {
    $('#guardian').show();
  });
  $('#hasChildrenYoungNo').click(function() {
    $('#guardian').hide();
  });

  // clear Will data
  $('#clearWill').click(function(e) {
    e.preventDefault();
    modalDialog(
      'Are you sure you want to Clear the data?',
      '<p>If you are sure you want to clear the current data in this Quickwill form, then click <b>OK</b> otherwise click <b>Cancel</b>.</p><p>You would normally only do this if you want to create a new Quickwill for someone else.</p>',
      0,
      "if (typeof window.localStorage != 'undefined') { window.localStorage.clear(); } window.location ='/quickwill.html?cleardata=true';"
    );
  });

  // preview Will
  $('#previewWill').click(function(e) {
    action = 'preview';
  });

  // purchase Will
  $('#purchaseWill').click(function(e) {
    action = 'purchase';
  });

  // download Will
  $('#downloadWill').click(function(e) {
    // check to see if the user who has already paid for a will is trying to change the name to create a will for someone else
    var testatorFullName = $('#testatorFullName').val();
    var quickWillFor = $('#quickwillfor').val();
    var orderamount = $('#orderamount').val();
    var admin = $('#administrator').val();
    if (admin == 'true') {
      action = 'quickwilldownload';
    } else if (testatorFullName != quickWillFor && orderamount > 0) {
      e.preventDefault();
      modalMessage(
        'Name Change?',
        "<p>You cannot change the testator's name for an already paid for order.</p><p>If you need to change the name due to a mistake in the spelling, then please contact us using the <a href='contact.html' title='Contact Us'>contact form</a> and we can make the correction for you and resend your Will.</p>",
        0,
        "$('#testatorFullName').val($('#quickwillfor').val()).focus();"
      );
    } else {
      action = 'quickwilldownload';
    }
  });

  // email Will
  $('#emailWill').click(function(e) {
    action = 'quickwillemail';
  });

  // turn off autocomplete, autocorrect, autocapitalize and spellcheck on all fields
  $('input, textarea').each(function() {
    $(this).attr('autocorrect', 'off');
    $(this).attr('autocomplete', 'off');
    $(this).attr('autocapitalize', 'off');
    $(this).attr('spellcheck', 'off');
  });

  /* assist clauses */

  // copy selected assist clause to associated text area
  $(document).on('click', 'p.assistclause', function() {
    var parentDiv = $(this).parents('div.form-group');
    var currentValue = parentDiv.find('textarea').val();
    if (currentValue.length) currentValue += '\n';
    parentDiv
      .find('textarea')
      .focus()
      .val(currentValue + $(this).text());
  });

  // intially activate all the assist links on document load and then at each add
  $(document).on('click', 'a.assist', function(e) {
    e.preventDefault();
    // get rid of all other popovers
    $('a.assist').popover('destroy');
    $('div.popover').remove();
    var selectedRecipient = $(this)
      .parents('div.beneficiary')
      .find('input.recipient')
      .val();
    selectedRecipient = typeof selectedRecipient != 'undefined' && selectedRecipient.length > 0 ? selectedRecipient : 'BENEFICIARY';
    var assistType = $(this).attr('assist');
    // if there is no div with an id of prefixAssistHTML hide assist link and then return else setup links as popovers with content
    var htmlID = '#' + assistType + 'AssistHTML';
    if ($(htmlID).length > 0) {
      var assistClauses = $(htmlID).html();
      // replace [recipient] with selectedRecipient name
      assistClauses = assistClauses.replace(/\[\[Recipient\]\]/g, selectedRecipient);
      assistClauses = assistClauses.replace(/\(.*\) /g, ''); // get rid of (friend), (cousin), etc
      // turn link into a popover
      $(this).popover({
        html: true,
        placement: function(context, source) {
          // popover will display below if the link is in the top half else above
          var offset = $(source).offset();
          var posY = offset.top - $(window).scrollTop();
          var windowHeight = $(window).height();
          if (posY < windowHeight / 2) {
            return 'bottom';
          }
          return 'top';
        },
        title: '<div class="close">x</div>Select an example clause',
        content: function() {
          return assistClauses;
        },
        trigger: 'manual'
      });
      $(this).popover('show');
    }
  });

  // open another beneficiary
  $(document).on('click', 'a#addanotherbeneficiary', function(e) {
    var theBeneficiary = $(this).parents('div.beneficiary');
    var theNextBeneficiary = theBeneficiary.next();
    $('#removethisbeneficiary').show();
    // move the buttons to the next beneficiary and then show it
    $('#beneficiarycontrols').insertBefore(theNextBeneficiary.find('hr'));
    theNextBeneficiary.show();
  });

  // remove last beneficiary
  $(document).on('click', 'a#removethisbeneficiary', function(e) {
    var theBeneficiary = $(this).parents('div.beneficiary');
    var whichBeneficiary = $('div.beneficiary').index(theBeneficiary.prev());
    if (whichBeneficiary == 0) $('#removethisbeneficiary').hide();
    $('#beneficiarycontrols').insertBefore(theBeneficiary.prev().find('hr'));
    theBeneficiary.hide();
    theBeneficiary.find('input').each(function(e) {
      $(this).val('');
    });
    theBeneficiary.find('textarea').each(function(e) {
      $(this).text('');
    });
  });

  // initialize
  stateAutoComplete();
  relationshipAutoComplete();

  /* setup localStorage to store form data every 30 seconds and initial load of form */
  if (typeof window.localStorage !== 'undefined') {
    /* if the form is empty, then check for localStorage and fill form */
    (function() {
      var theName = $('#testatorFullName').val();
      if (theName == '') {
        var storedTime = parseInt(window.localStorage.getItem('qwtime'), 10);
        var now = parseInt($.now(), 10);
        if (now < storedTime + 1000 * 60 * 60 * 24) {
          // if the data was saved less than 24 hours ago then redisplay else delete it
          var storedData = window.localStorage.getItem('qw');
          if (storedData != null) {
            var formData = JSON.parse(atob(storedData));
            $.each(formData, function(index, value) {
              var fieldName = value.name;
              var fieldValue = value.value;
              $("[name='" + fieldName + "']").val(fieldValue);
            });
            saveQuickWillFormData(false);
            // now reload the page so everything shows as it should
            window.location = '/quickwill.html';
          }
        } else {
          // past the amount of time to retain the quickwill data on the device so clear it all
          window.localStorage.clear();
        }
      }
    })();
  }

  // save data every 60 seconds
  setInterval(function() {
    saveQuickWillFormData();
  }, 1000 * 60);
}; // end of quickwill form

/* !document ready */
$(document).ready(function() {
  // pad out pagecontent to fill avail window
  var pch = $('#pagecontent').height();
  var bh = $('#foot').height();
  var th = $('#header').height();
  var fill = window.innerHeight - th - bh - pch - 30;
  if (fill > 0) $('#pagecontent').css('padding-bottom', fill + 'px');
  //console.log(pch, th, bh, window.innerHeight, fill);

  // display the FOUC sections by removing class .fouc
  $('html').removeClass('fouc');

  // get the current location and get the page name without extension to index into the page function array and execute the js for that page if it exists
  var re = /^\w+:\/+[^\/\s]+\/([^\.]+)\..*$/;
  var matchArray;
  var menu = 'home';
  if ((matchArray = re.exec(window.location))) {
    var page = matchArray[1];
    menu = page;
    if (pageScripts[page] != undefined) {
      // run the script for that page
      pageScripts[page]();
      //trace("found " + page + " javascript");
    } else {
      //pageScripts['home'](); // home page so run whatever is on there
      //trace("cannot find " + page + " javascript");
    }
  } else {
    pageScripts['home'](); // home page so run whatever is on there
  }

  // return home on logo click
  $('div#logo').click(function() {
    document.location = '/';
  });

  // trigger popovers
  triggerPopovers();

  // close other popovers when a new one opens
  if (isTouchDevice) {
    $('a[data-toggle="popover"]').on('click', function() {
      $('a[data-toggle="popover"]')
        .not(this)
        .popover('hide');
    });
  }

  $('a[data-toggle="tooltip"]').tooltip({ placement: 'top', html: true });

  // set active menu
  $('ul.nav li').removeClass('active');
  $('ul.nav li#' + menu).addClass('active');

  // update visits setting cookiesenabled and javascriptenabled
  if (ckE == 0 || jsE == 0) {
    $.ajax({
      url: 'includes/updatevisits.php?cookieenabled=' + navigator.cookieEnabled + '&sessionid=' + sessID
    });
  }

  // run code on page unload - not fired by ios safari
  window.onbeforeunload = function(e) {};
  window.onpagehide = function(e) {
    $.ajax({
      url: 'includes/updatepagevisits.php?pageID=' + pageID + '&sessID=' + sessID,
      async: true
    });
  };
});
