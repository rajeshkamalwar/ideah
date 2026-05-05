@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Events') }}</h4>
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
        <a href="#">{{ __('Event Management') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Events') }}</a>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col-lg-4">
              <div class="card-title d-inline-block">{{ __('Events') }}</div>
            </div>
            <div class="col-lg-4 offset-lg-4 mt-2 mt-lg-0 text-right">
              <a href="{{ route('admin.event_management.create_event') }}?language={{ request()->get('language', $defaultLang->code) }}"
                class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> {{ __('Add') }}</a>
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="row">
            <div class="col-lg-12">
              @if (count($events) == 0)
                <h3 class="text-center mt-2">{{ __('NO EVENT FOUND') . '!' }}</h3>
              @else
                <div class="table-responsive">
                  <table class="table table-striped mt-3">
                    <thead>
                      <tr>
                        <th scope="col">{{ __('Title') }}</th>
                        <th scope="col">{{ __('Start') }}</th>
                        <th scope="col">{{ __('Location') }}</th>
                        <th scope="col">{{ __('Status') }}</th>
                        <th scope="col">{{ __('Serial Number') }}</th>
                        <th scope="col">{{ __('Actions') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($events as $event)
                        <tr>
                          <td>{{ \Illuminate\Support\Str::limit($event->title, 60) }}</td>
                          <td>{{ $event->event_start->format('M d, Y H:i') }}</td>
                          <td>{{ $event->location ?? '—' }}</td>
                          <td>{{ $event->status == 1 ? __('Published') : __('Draft') }}</td>
                          <td>{{ $event->serial_number }}</td>
                          <td>
                            <a class="btn btn-secondary btn-sm"
                              href="{{ route('admin.event_management.edit_event', ['id' => $event->id]) }}?language={{ request()->get('language', $defaultLang->code) }}">
                              <i class="fas fa-edit"></i>
                            </a>
                            <form class="d-inline-block"
                              action="{{ route('admin.event_management.delete_event', ['id' => $event->id]) }}"
                              method="post"
                              onsubmit="return confirm('{{ __('Are you sure') }}?');">
                              @csrf
                              <button type="submit" class="btn btn-danger btn-sm">
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
        <div class="card-footer"></div>
      </div>
    </div>
  </div>
@endsection
