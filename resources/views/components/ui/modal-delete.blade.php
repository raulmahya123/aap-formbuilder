@props(['id' => 'ui-action-confirm'])
<dialog id="{{ $id }}" class="ui-modal ui-modal--sm ui-modal--delete" aria-labelledby="{{ $id }}-title" aria-describedby="{{ $id }}-desc">
  <div class="ui-modal__header">
    <div class="ui-modal__icon ui-modal__icon--danger">
      <x-ui.lucide name="trash-2" />
    </div>
    <div class="ui-modal__header-content">
      <h2 id="{{ $id }}-title" class="ui-modal__title" data-delete-title>Konfirmasi penghapusan</h2>
      <p id="{{ $id }}-desc" class="ui-modal__description" data-delete-sub style="display:none"></p>
    </div>
    <button type="button" class="ui-modal__close" data-dialog-cancel aria-label="Tutup">
      <x-ui.lucide name="x" />
    </button>
  </div>
  <div class="ui-modal__body">
    <p class="ui-modal__delete-desc" data-delete-desc>Apakah Anda yakin ingin menghapus data ini?</p>
    <div class="ui-modal__delete-warning" data-delete-warning>
      <x-ui.lucide name="alert-triangle" />
      <span>Tindakan ini tidak dapat dibatalkan.</span>
    </div>
    <div data-delete-error style="display:none" class="ui-form-error mt-3" role="alert"></div>
  </div>
  <div class="ui-modal__footer">
    <x-ui.button type="button" data-dialog-cancel>Batal</x-ui.button>
    <x-ui.button variant="danger" type="button" data-dialog-confirm>
      <x-ui.lucide name="trash-2" />
      <span data-delete-btn-text>Hapus</span>
    </x-ui.button>
  </div>
</dialog>
