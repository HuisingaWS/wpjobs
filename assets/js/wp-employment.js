jQuery(document).ready(function ($) {
  $('.wp-employment-details').hide();

  $('.wp-employment-more').click(function () {
    if ($(this).text() !== 'Hide') {
      var toggle = $(this).attr('id');
      $('.wp-employment-details').hide('4000');
      $('.wp-employment-more').text('Show');
      $(this).text('Hide');
      $("." + toggle).show('4000', function () {
        $(this).parent().parent()[0].scrollIntoView(true);
      });
    } else {
      $('.wp-employment-details').hide('4000');
      $('.wp-employment-more').text('Show');
    }

    return false;
  });
});