@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Bulk Email Campaigns') }}</h4>
    <ul class="breadcrumbs">
      <li class="nav-home">
        <a href="{{ route('admin.dashboard') }}">
          <i class="flaticon-home"></i>
        </a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Users Management') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Bulk Email') }}</a>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col-lg-6">
              <div class="card-title">{{ __('Campaign History') }}</div>
            </div>
            <div class="col-lg-6 text-right">
              <a href="{{ route('admin.bulk_email.compose') }}" class="btn btn-primary btn-sm">
                <i class="fal fa-paper-plane"></i> {{ __('New Campaign') }}
              </a>
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="row">
            <div class="col-lg-12">
              @if ($campaigns->count() == 0)
                <h3 class="text-center mt-2">{{ __('No campaigns found.') }}</h3>
              @else
                <div class="table-responsive">
                  <table class="table table-striped mt-3">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>{{ __('Subject') }}</th>
                        <th>{{ __('Audience') }}</th>
                        <th>{{ __('Preview') }}</th>
                        <th>{{ __('Progress') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Scheduled At') }}</th>
                        <th>{{ __('Created') }}</th>
                        <th>{{ __('Action') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($campaigns as $campaign)
                        @php
                          $pct = $campaign->total_recipients > 0
                            ? round((($campaign->sent_count + ($campaign->failed_count ?? 0)) / $campaign->total_recipients) * 100)
                            : 0;
                          $barClass = $campaign->status === 'completed' ? 'bg-success'
                            : ($campaign->status === 'completed_error' ? 'bg-warning'
                            : ($campaign->status === 'failed' ? 'bg-danger' : 'bg-primary'));
                        @endphp
                        <tr>
                          <td>{{ $loop->iteration + ($campaigns->currentPage() - 1) * $campaigns->perPage() }}</td>
                          <td><strong>{{ Str::limit($campaign->subject, 45) }}</strong></td>
                          <td>
                            @foreach ($campaign->audience as $group)
                              <span class="badge badge-info">{{ ucfirst($group) }}</span>
                            @endforeach
                          </td>
                          <td style="max-width:220px;">
                            <span class="text-muted small">
                              {!! Str::limit(strip_tags($campaign->body), 80) !!}
                            </span>
                            <br>
                            <a href="#"
                              class="badge badge-secondary view-email-btn"
                              data-id="{{ $campaign->id }}"
                              data-subject="{{ $campaign->subject }}"
                              data-body="{{ htmlspecialchars($campaign->body, ENT_QUOTES) }}">
                              {{ __('View full email') }}
                            </a>
                          </td>
                          <td style="min-width:140px;">
                            <div class="progress" style="height:10px;" title="{{ $campaign->sent_count }}/{{ $campaign->total_recipients }}">
                              <div class="progress-bar {{ $barClass }}" role="progressbar"
                                style="width:{{ $pct }}%"
                                aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                              </div>
                            </div>
                            <small class="text-muted">
                              {{ __('Sent') }}: {{ $campaign->sent_count }}
                              @if (($campaign->failed_count ?? 0) > 0)
                                | {{ __('Failed') }}: {{ $campaign->failed_count }}
                              @endif
                              | {{ __('Total') }}: {{ $campaign->total_recipients }}
                            </small>
                          </td>
                          <td>
                            @if ($campaign->status === 'completed')
                              <span class="badge badge-success">{{ __('Completed') }}</span>
                            @elseif ($campaign->status === 'completed_error')
                              <span class="badge badge-warning">{{ __('Completed with errors') }}</span>
                            @elseif ($campaign->status === 'failed')
                              <span class="badge badge-danger">{{ __('Failed') }}</span>
                            @elseif ($campaign->status === 'sending')
                              <span class="badge badge-primary">{{ __('Sending') }}</span>
                            @elseif ($campaign->status === 'queued')
                              <span class="badge badge-warning">{{ __('Queued') }}</span>
                            @else
                              <span class="badge badge-secondary">{{ ucfirst($campaign->status) }}</span>
                            @endif
                          </td>
                          <td>{{ $campaign->scheduled_at ? $campaign->scheduled_at->format('M d, Y H:i') : '—' }}</td>
                          <td>{{ $campaign->created_at->format('M d, Y H:i') }}</td>
                          <td>
                            <form class="deleteForm d-inline-block"
                              action="{{ route('admin.bulk_email.destroy', $campaign->id) }}"
                              method="post">
                              @csrf
                              <button type="submit" class="btn btn-danger btn-sm deleteBtn">
                                <i class="fas fa-trash"></i>
                              </button>
                            </form>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @endif
            </div>
          </div>
        </div>

        <div class="card-footer">
          <div class="d-inline-block mx-auto">
            {{ $campaigns->links() }}
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Email Preview Modal --}}
  <div class="modal fade" id="emailPreviewModal" tabindex="-1" role="dialog" aria-labelledby="emailPreviewTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="emailPreviewTitle">{{ __('Email Preview') }}</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p class="mb-1"><strong>{{ __('Subject:') }}</strong> <span id="previewSubject"></span></p>
          <hr>
          <div id="previewBody"
            style="border:1px solid #e3e3e3; border-radius:6px; padding:20px; min-height:150px; background:#fafafa;">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('script')
<script>
  'use strict';
  $(document).ready(function () {
    $(document).on('click', '.view-email-btn', function (e) {
      e.preventDefault();
      var subject = $(this).data('subject');
      var body    = $(this).data('body');
      $('#previewSubject').text(subject);
      $('#previewBody').html(body);
      $('#emailPreviewModal').modal('show');
    });
  });
</script>
@endsection
