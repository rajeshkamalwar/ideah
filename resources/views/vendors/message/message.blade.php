{{-- <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Message') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="col-lg-12">
          <div class="form-group">
            <textarea rows="5" readonly class="form-control" id="in_message"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
          {{ __('Close') }}
        </button>
      </div>
    </div>
  </div>
</div> --}}

<div class="modal fade" id="editBtnForFormBuilderInfo" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel">Product Message Details</h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <p><strong>Name:</strong> <span id="modalName"></span></p>
        <p><strong>Phone:</strong> <span id="modalPhone"></span></p>
        <p><strong>Email:</strong> <span id="modalEmail"></span></p>
        {{-- <p><strong>Message:</strong> <span id="modalMessage"></span></p> --}}
        <hr>
        <div id="modalInformation">
          <h6>Additional Information:</h6>
          <div id="dynamicFields"></div>
        </div>
      </div>

    </div>
  </div>
</div>
