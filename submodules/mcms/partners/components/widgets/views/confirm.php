<div class="modal fade confirm-modal" id="confirmModal" tabindex="-1" role="dialog" style="display: none" >
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <button type="button" class="close" aria-label="Close"><i class="icon-cancel_4"></i></button>
        <h4 class="modal-title">
          <i class="icon-danger"></i>
          <span id="confirmModalHeader"><?= $defaultHeader ?></span>
        </h4>
      </div>
      <div class="modal-body">
        <p id="confirmModalBody"></p>
      </div>
      <div class="modal-footer">
        <button id="confirmAccept" type="button" class="btn btn-success pull-left"><?= $yes ?></button>
        <button id="confirmDecline" type="button" class="btn btn-default"><?= $no ?></button>
      </div>

    </div>
  </div>
</div>