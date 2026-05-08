@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Compose Bulk Email') }}</h4>
    <ul class="breadcrumbs">
      <li class="nav-home">
        <a href="{{ route('admin.dashboard') }}">
          <i class="flaticon-home"></i>
        </a>
      </li>
      <li class="separator"><i class="flaticon-right-arrow"></i></li>
      <li class="nav-item"><a href="#">{{ __('Users Management') }}</a></li>
      <li class="separator"><i class="flaticon-right-arrow"></i></li>
      <li class="nav-item"><a href="{{ route('admin.bulk_email.index') }}">{{ __('Bulk Email') }}</a></li>
      <li class="separator"><i class="flaticon-right-arrow"></i></li>
      <li class="nav-item"><a href="#">{{ __('Compose') }}</a></li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="card-title d-inline-block">{{ __('Compose New Campaign') }}</div>
          <a class="btn btn-info btn-sm float-right" href="{{ route('admin.bulk_email.index') }}">
            <span class="btn-label"><i class="fas fa-backward"></i></span> {{ __('Back') }}
          </a>
        </div>

        <form id="sendForm" action="{{ route('admin.bulk_email.send') }}" method="post">
          @csrf
          {{-- Hidden field carries the final (possibly trimmed) recipient list --}}
          <input type="hidden" name="custom_recipients" id="customRecipientsInput">

          <div class="card-body">
            @if ($errors->any())
              <div class="alert alert-danger">
                <ul class="mb-0">
                  @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
              </div>
            @endif

            <div class="row">
              <div class="col-lg-8 offset-lg-2">

                {{-- ── Audience cards ── --}}
                <div class="form-group">
                  <label class="d-block font-weight-bold mb-2">{{ __('1. Select Audience') . '*' }}</label>
                  <div class="row">
                    <div class="col-md-4 mb-2">
                      <div class="card border audience-card h-100" style="cursor:pointer;" onclick="toggleAudience('aud_subscribers')">
                        <div class="card-body py-3 px-3 d-flex align-items-center">
                          <input type="checkbox" class="audience-check mr-3" id="aud_subscribers"
                            name="audience[]" value="subscribers"
                            data-count="{{ $counts['subscribers'] }}"
                            {{ in_array('subscribers', old('audience', [])) ? 'checked' : '' }}
                            onclick="event.stopPropagation()">
                          <div>
                            <div class="font-weight-bold">{{ __('Subscribers') }}</div>
                            <div class="text-muted small">{{ number_format($counts['subscribers']) }} {{ __('contacts') }}</div>
                          </div>
                          <i class="fal fa-envelope ml-auto fa-lg text-info"></i>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4 mb-2">
                      <div class="card border audience-card h-100" style="cursor:pointer;" onclick="toggleAudience('aud_users')">
                        <div class="card-body py-3 px-3 d-flex align-items-center">
                          <input type="checkbox" class="audience-check mr-3" id="aud_users"
                            name="audience[]" value="users"
                            data-count="{{ $counts['users'] }}"
                            {{ in_array('users', old('audience', [])) ? 'checked' : '' }}
                            onclick="event.stopPropagation()">
                          <div>
                            <div class="font-weight-bold">{{ __('Registered Users') }}</div>
                            <div class="text-muted small">{{ number_format($counts['users']) }} {{ __('contacts') }}</div>
                          </div>
                          <i class="fal fa-users ml-auto fa-lg text-success"></i>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4 mb-2">
                      <div class="card border audience-card h-100" style="cursor:pointer;" onclick="toggleAudience('aud_vendors')">
                        <div class="card-body py-3 px-3 d-flex align-items-center">
                          <input type="checkbox" class="audience-check mr-3" id="aud_vendors"
                            name="audience[]" value="vendors"
                            data-count="{{ $counts['vendors'] }}"
                            {{ in_array('vendors', old('audience', [])) ? 'checked' : '' }}
                            onclick="event.stopPropagation()">
                          <div>
                            <div class="font-weight-bold">{{ __('Vendors') }}</div>
                            <div class="text-muted small">{{ number_format($counts['vendors']) }} {{ __('contacts') }}</div>
                          </div>
                          <i class="fal fa-store ml-auto fa-lg text-warning"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                  @error('audience')
                    <p class="mt-1 mb-0 text-danger">{{ $message }}</p>
                  @enderror
                </div>

                {{-- ── Recipient list panel ── --}}
                <div class="form-group" id="recipientPanel" style="display:none;">
                  <label class="d-block font-weight-bold mb-2">
                    {{ __('2. Review & Remove Recipients') }}
                    <span class="badge badge-primary ml-1" id="recipientBadge">0</span>
                  </label>

                  <div class="input-group mb-2">
                    <input type="text" id="recipientSearch" class="form-control form-control-sm"
                      placeholder="{{ __('Filter by email or name…') }}">
                    <div class="input-group-append">
                      <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                  </div>

                  <div id="recipientTableWrap"
                    style="max-height:260px;overflow-y:auto;border:1px solid #e3e3e3;border-radius:4px;">
                    <table class="table table-sm table-striped mb-0" id="recipientTable">
                      <thead style="position:sticky;top:0;background:#f8f9fa;z-index:1;">
                        <tr>
                          <th style="width:40px;">
                            <input type="checkbox" id="checkAll" title="{{ __('Select all') }}">
                          </th>
                          <th>{{ __('Email') }}</th>
                          <th>{{ __('Name') }}</th>
                          <th>{{ __('Group') }}</th>
                          <th style="width:60px;"></th>
                        </tr>
                      </thead>
                      <tbody id="recipientTbody"></tbody>
                    </table>
                  </div>

                  <div class="d-flex align-items-center mt-2">
                    <span class="text-muted small mr-auto">
                      {{ __('Showing') }} <strong id="visibleCount">0</strong>
                      {{ __('of') }} <strong id="totalCount">0</strong> {{ __('recipients') }}
                    </span>
                    <button type="button" class="btn btn-danger btn-sm ml-2" id="removeSelectedBtn" style="display:none;">
                      <i class="fas fa-trash mr-1"></i>{{ __('Remove selected') }}
                    </button>
                  </div>
                </div>

                {{-- ── Subject ── --}}
                <div class="form-group">
                  <label for="subject" class="font-weight-bold">{{ __('3. Subject') . '*' }}</label>
                  <input type="text" id="subject" class="form-control" name="subject"
                    placeholder="{{ __('Enter email subject') }}" value="{{ old('subject') }}">
                  @error('subject')<p class="mt-1 mb-0 text-danger">{{ $message }}</p>@enderror
                </div>

                {{-- ── Body (TinyMCE via .summernote) ── --}}
                <div class="form-group">
                  <label class="font-weight-bold">{{ __('4. Message') . '*' }}</label>
                  <textarea id="bulkEmailBody" class="summernote" name="body"
                    style="width:100%;min-height:350px;">{{ old('body') }}</textarea>
                  @error('body')<p class="mt-1 mb-0 text-danger">{{ $message }}</p>@enderror
                </div>

                {{-- ── Schedule toggle ── --}}
                <div class="form-group">
                  <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="enableSchedule">
                    <label class="custom-control-label" for="enableSchedule">{{ __('Schedule for later') }}</label>
                  </div>
                </div>

                <div class="form-group" id="scheduleField" style="display:none;">
                  <label class="font-weight-bold">{{ __('Send At') . '*' }}</label>
                  {{-- Hidden field submitted to controller, built from date + time below --}}
                  <input type="hidden" id="scheduled_at" name="scheduled_at" value="{{ old('scheduled_at') }}">
                  <div class="row">
                    <div class="col-md-6 mb-2">
                      <div class="input-group">
                        <div class="input-group-prepend">
                          <span class="input-group-text"><i class="fal fa-calendar-alt"></i></span>
                        </div>
                        <input type="text" id="scheduleDatePicker" class="form-control"
                          placeholder="{{ __('Select date') }}" autocomplete="off" readonly>
                      </div>
                    </div>
                    <div class="col-md-6 mb-2">
                      <div class="input-group">
                        <div class="input-group-prepend">
                          <span class="input-group-text"><i class="fal fa-clock"></i></span>
                        </div>
                        <input type="text" id="scheduleTimePicker" class="form-control"
                          placeholder="{{ __('Select time (e.g. 14:30)') }}" autocomplete="off">
                      </div>
                    </div>
                  </div>
                  @error('scheduled_at')<p class="mt-1 mb-0 text-danger">{{ $message }}</p>@enderror
                </div>

              </div>
            </div>
          </div>

          <div class="card-footer">
            <div class="row">
              <div class="col-12 text-center">
                <button type="button" id="sendNowBtn" class="btn btn-success mr-2">
                  <i class="fal fa-paper-plane mr-1"></i>{{ __('Send Now') }}
                </button>
                <button type="button" id="scheduleBtn" class="btn btn-primary" style="display:none;">
                  <i class="fal fa-clock mr-1"></i>{{ __('Schedule') }}
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@section('script')
<script>
  'use strict';

  var recipientsData = [];
  var recipientsUrl  = "{{ route('admin.bulk_email.recipients') }}";
  var uploadImageUrl = "{{ route('admin.bulk_email.upload_image') }}";
  var sendRoute      = "{{ route('admin.bulk_email.send') }}";
  var scheduleRoute  = "{{ route('admin.bulk_email.schedule') }}";
  var uploadInvalidText = @json(__('Image upload failed. Invalid server response.'));
  var uploadFailedText  = @json(__('Image upload failed. Please try again.'));

  // Called from onclick="" on audience cards — jQuery is guaranteed loaded here
  function toggleAudience(id) {
    var cb = document.getElementById(id);
    cb.checked = !cb.checked;
    $('#' + id).trigger('change');
  }

  $(document).ready(function () {
    function initBulkEmailEditor() {
      if (typeof tinymce === 'undefined') {
        return;
      }

      var existing = tinymce.get('bulkEmailBody');
      if (existing) {
        existing.remove();
      }

      tinymce.init({
        selector: '#bulkEmailBody',
        plugins: 'autolink charmap emoticons image link lists media searchreplace table visualblocks wordcount directionality',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat | ltr rtl',
        promotion: false,
        automatic_uploads: true,
        images_file_types: 'jpg,jpeg,png,gif,webp',
        images_upload_handler: function (blobInfo, progress) {
          return new Promise(function (resolve, reject) {
            var formData = new FormData();
            formData.append('image', blobInfo.blob(), blobInfo.filename());

            $.ajax({
              url: uploadImageUrl,
              method: 'POST',
              data: formData,
              processData: false,
              contentType: false,
              xhr: function () {
                var xhr = $.ajaxSettings.xhr();
                if (xhr.upload) {
                  xhr.upload.onprogress = function (e) {
                    if (e.lengthComputable && typeof progress === 'function') {
                      progress((e.loaded / e.total) * 100);
                    }
                  };
                }
                return xhr;
              },
              success: function (response) {
                if (response && response.location) {
                  resolve(response.location);
                } else {
                  reject(uploadInvalidText);
                }
              },
              error: function (xhr) {
                var message = (xhr.responseJSON && xhr.responseJSON.message)
                  ? xhr.responseJSON.message
                  : uploadFailedText;
                reject(message);
              }
            });
          });
        }
      });
    }

    initBulkEmailEditor();

    /* ── Audience card styling + AJAX load ── */
    function onAudienceChange() {
      var checked = [];
      $('.audience-check').each(function () {
        var card = $(this).closest('.audience-card');
        if ($(this).is(':checked')) {
          card.addClass('border-primary').css('background', 'rgba(0,123,255,0.05)');
          checked.push($(this).val());
        } else {
          card.removeClass('border-primary').css('background', '');
        }
      });

      if (checked.length === 0) {
        recipientsData = [];
        renderTable([]);
        $('#recipientPanel').slideUp();
        return;
      }

      // Show loading state immediately
      $('#recipientTbody').html(
        '<tr><td colspan="5" class="text-center py-3">' +
        '<span class="spinner-border spinner-border-sm mr-1" role="status"></span>' +
        ' {{ __('Loading recipients…') }}</td></tr>'
      );
      $('#recipientPanel').slideDown();

      $.get(recipientsUrl, { audience: checked }, function (data) {
        recipientsData = data;
        renderTable(data);
      }).fail(function () {
        $('#recipientTbody').html(
          '<tr><td colspan="5" class="text-center text-danger py-3">' +
          '{{ __('Failed to load recipients. Please try again.') }}</td></tr>'
        );
      });
    }

    $('.audience-check').on('change', function () { onAudienceChange(); });

    /* ── Table rendering ── */
    function escHtml(str) {
      return $('<div>').text(str || '').html();
    }

    function renderTable(list) {
      var html = '';
      $.each(list, function (i, r) {
        html += '<tr data-email="' + escHtml(r.email) + '">' +
          '<td><input type="checkbox" class="row-check"></td>' +
          '<td>' + escHtml(r.email) + '</td>' +
          '<td>' + escHtml(r.name || '—') + '</td>' +
          '<td><span class="badge badge-secondary">' + escHtml(r.group) + '</span></td>' +
          '<td><button type="button" class="btn btn-xs btn-danger remove-one" title="{{ __('Remove') }}">' +
            '<i class="fas fa-times"></i></button></td>' +
          '</tr>';
      });
      $('#recipientTbody').html(
        html || '<tr><td colspan="5" class="text-center text-muted py-3">{{ __('No recipients') }}</td></tr>'
      );
      updateCounts();
      $('#checkAll').prop('checked', false);
      $('#removeSelectedBtn').hide();
    }

    function updateCounts() {
      var total   = recipientsData.length;
      var visible = $('#recipientTbody tr[data-email]').length;
      $('#recipientBadge').text(total);
      $('#totalCount').text(total);
      $('#visibleCount').text(visible);
    }

    /* ── Remove one row ── */
    $(document).on('click', '.remove-one', function () {
      var email = $(this).closest('tr').data('email');
      recipientsData = recipientsData.filter(function (r) { return r.email !== email; });
      renderTable(applySearch());
    });

    /* ── Select all ── */
    $('#checkAll').on('change', function () {
      var isChecked = $(this).is(':checked');
      $('#recipientTbody .row-check').prop('checked', isChecked);
      $('#removeSelectedBtn').toggle(isChecked && $('#recipientTbody .row-check').length > 0);
    });

    /* ── Individual row checkbox ── */
    $(document).on('change', '.row-check', function () {
      var anyChecked = $('#recipientTbody .row-check:checked').length > 0;
      $('#removeSelectedBtn').toggle(anyChecked);
      if (!$(this).is(':checked')) { $('#checkAll').prop('checked', false); }
    });

    /* ── Remove selected ── */
    $('#removeSelectedBtn').on('click', function () {
      var toRemove = [];
      $('#recipientTbody .row-check:checked').each(function () {
        toRemove.push($(this).closest('tr').data('email'));
      });
      toRemove.forEach(function (email) {
        recipientsData = recipientsData.filter(function (r) { return r.email !== email; });
      });
      renderTable(applySearch());
    });

    /* ── Search with 250 ms debounce ── */
    var searchTimer = null;
    $('#recipientSearch').on('input', function () {
      clearTimeout(searchTimer);
      searchTimer = setTimeout(function () { renderTable(applySearch()); }, 250);
    });

    function applySearch() {
      var q = $('#recipientSearch').val().toLowerCase();
      if (!q) { return recipientsData; }
      return recipientsData.filter(function (r) {
        return r.email.toLowerCase().indexOf(q) !== -1 ||
               (r.name || '').toLowerCase().indexOf(q) !== -1;
      });
    }

    /* ── Date / time pickers ── */
    $('#scheduleDatePicker').datepicker({
      format: 'yyyy-mm-dd',
      startDate: 'today',
      autoclose: true,
      todayHighlight: true
    }).on('changeDate', syncScheduledAt);

    $('#scheduleTimePicker').timepicker({
      timeFormat: 'H:i',
      interval: 15,
      minTime: '00:00',
      maxTime: '23:45',
      startTime: '08:00',
      dynamic: false,
      dropdown: true,
      scrollbar: true
    }).on('changeTime', syncScheduledAt);

    function syncScheduledAt() {
      var d = $('#scheduleDatePicker').val();
      var t = $('#scheduleTimePicker').val();
      if (d && t) {
        // Ensure HH:mm format (pad single-digit hour)
        var timePadded = (t.length === 4) ? ('0' + t) : t;
        $('#scheduled_at').val(d + ' ' + timePadded + ':00');
      }
    }

    /* ── Restore schedule UI state after validation redirect ── */
    var oldScheduledAt = @json(old('scheduled_at', ''));
    if (oldScheduledAt) {
      var parts    = oldScheduledAt.split(' ');
      var datePart = parts[0] || '';
      var timePart = parts[1] ? parts[1].substring(0, 5) : '';
      if (datePart) { $('#scheduleDatePicker').datepicker('update', datePart); }
      if (timePart) { $('#scheduleTimePicker').timepicker('setTime', timePart); }
      $('#enableSchedule').prop('checked', true);
      $('#scheduleField').show();
      $('#sendNowBtn').hide();
      $('#scheduleBtn').show();
    }

    /* ── Schedule toggle ── */
    $('#enableSchedule').on('change', function () {
      if ($(this).is(':checked')) {
        $('#scheduleField').slideDown();
        $('#sendNowBtn').hide();
        $('#scheduleBtn').show();
      } else {
        $('#scheduleField').slideUp();
        $('#sendNowBtn').show();
        $('#scheduleBtn').hide();
      }
    });

    /* ── Client-side validation ── */
    function validateForm(isSchedule) {
      if ($('.audience-check:checked').length === 0) {
        alert('{{ __('Please select at least one audience group.') }}');
        return false;
      }
      if (!$.trim($('#subject').val())) {
        alert('{{ __('Please enter a subject.') }}');
        $('#subject').focus();
        return false;
      }
      var bodyContent = (typeof tinymce !== 'undefined' && tinymce.get('bulkEmailBody'))
        ? tinymce.get('bulkEmailBody').getContent({ format: 'text' })
        : $('#bulkEmailBody').val();
      if (!$.trim(bodyContent)) {
        alert('{{ __('Please enter a message body.') }}');
        return false;
      }
      if (isSchedule && !$('#scheduled_at').val()) {
        alert('{{ __('Please select both a date and time to schedule this campaign.') }}');
        return false;
      }
      return true;
    }

    /* ── Submit ── */
    function doSubmit(action, isSchedule) {
      if (!validateForm(isSchedule)) { return; }
      if (typeof tinymce !== 'undefined') { tinymce.triggerSave(); }
      var emails = recipientsData.map(function (r) { return r.email; });
      $('#customRecipientsInput').val(JSON.stringify(emails));
      $('#sendForm').attr('action', action).submit();
    }

    $('#sendNowBtn').on('click', function () { doSubmit(sendRoute, false); });
    $('#scheduleBtn').on('click', function () { doSubmit(scheduleRoute, true); });

    // Restore checked audience card state on page load (after validation redirect)
    onAudienceChange();
  });
</script>
@endsection
