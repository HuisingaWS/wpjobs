jQuery(document).ready(function ($) {
  $('#wp-employment-form').submit(function (e) {
    e.preventDefault();

    $('.help-inline').unwrap();
    $('.help-inline').remove();
    var failure = 0;
    var required = ['first', 'last', 'phone', 'address', 'signature'];

    function displayError(field, errortext) {
      $('#' + field).wrap('<div class="control-group error ' + field + '" />');
      $('.' + field).append('<span class="help-inline">' + errortext + '</span>');
      failure = 1;
    }

    $.each(required, function (i, id) {
      if ($('#' + id).val() === '') {
        displayError(id, 'This field is required');
      }
    });

    var emailregex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    if (!emailregex.test($('#email').val())) {
      displayError('email', 'This specified email is invalid');
    }

    if (failure === 1) {
      return false;
    }

    var address = $('#address').val().replace(/\r\n|\r|\n/g, '<br>'),
        experience = $('#experience').val().replace(/\r\n|\r|\n/g, '<br>'),
        skills = $('#skills').val().replace(/\r\n|\r|\n/g, '<br>');
    if ($('#reply').val() !== '') {
      var reply = $(this).val().replace(/\r\n|\r|\n/g, '<br>');
    }

    $('#wp-employment-form').unbind().submit();
  });
});