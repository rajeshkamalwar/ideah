'use strict';

$(function () {
  var $form = $('#vendorWizardCreateForm');
  if (!$form.length) {
    return;
  }

  $form.on('submit', function (e) {
    e.preventDefault();
    $('.request-loader').addClass('show');
    var fd = new FormData(this);

    $.ajax({
      url: $form.attr('action'),
      method: 'POST',
      data: fd,
      contentType: false,
      processData: false,
      success: function (data) {
        $('.request-loader').removeClass('show');
        $('#vendorWizardErrors').addClass('dis-none').find('ul').empty();

        if (data.status === 'success' && data.redirect) {
          window.location.href = data.redirect;
          return;
        }
        if (data.status === 'success') {
          window.location.reload();
        }
      },
      error: function (xhr) {
        $('.request-loader').removeClass('show');
        var $box = $('#vendorWizardErrors');
        var $ul = $box.find('ul');
        $ul.empty();

        if (xhr.responseJSON && xhr.responseJSON.errors) {
          $.each(xhr.responseJSON.errors, function (_key, messages) {
            var msg = Array.isArray(messages) ? messages[0] : messages;
            $ul.append(
              $('<li>').append($('<p>').addClass('text-danger mb-0').text(msg))
            );
          });
        } else {
          $ul.append(
            $('<li>').append(
              $('<p>').addClass('text-danger mb-0').text('Request failed')
            )
          );
        }

        $box.removeClass('dis-none').show();
        var top = $box.offset();
        if (top) {
          $('html, body').animate({ scrollTop: top.top - 100 }, 500);
        }
      }
    });
  });
});
