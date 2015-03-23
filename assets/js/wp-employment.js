jQuery(document).ready(function ($) {
  $('.wp-employment-details').hide();

  $('.wp-employment-more').click(function () {
    if ($(this).text() !== 'Hide') {
      var toggle = $(this).attr('id');
      $('.wp-employment-details').hide('4000');
      $('.wp-employment-more').text('Show');
      $(this).text('Hide');
      $("." + toggle).show('4000');
    } else {
      $('.wp-employment-details').hide('4000');
      $('.wp-employment-more').text('Show');
    }

    return false;
  });
});