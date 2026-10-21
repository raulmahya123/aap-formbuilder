<x-ui.modal-delete id="ui-action-confirm" />
<dialog id="ui-password-reset" class="ui-modal ui-modal--sm" aria-labelledby="ui-reset-title" aria-describedby="ui-reset-description">
    <div class="ui-modal__header">
        <div class="ui-modal__icon"><x-ui.lucide name="key-round" /></div>
        <div class="ui-modal__header-content">
            <h2 id="ui-reset-title" class="ui-modal__title">Reset password</h2>
            <p id="ui-reset-description" class="ui-modal__description">Atur password baru untuk <strong data-reset-name></strong>.</p>
        </div>
        <button type="button" class="ui-modal__close" data-dialog-cancel aria-label="Tutup"><x-ui.lucide name="x" /></button>
    </div>
    <form method="POST" data-reset-form>
        @csrf
        @method('PATCH')
        <div class="ui-modal__body">
            <div class="ui-form-group">
                <label class="ui-form-label" for="ui-reset-password">Password baru <span class="ui-required">*</span></label>
                <input class="ui-form-input" id="ui-reset-password" type="password" name="password" required minlength="4" autocomplete="new-password">
            </div>
            <div class="ui-form-group">
                <label class="ui-form-label" for="ui-reset-confirmation">Konfirmasi password <span class="ui-required">*</span></label>
                <input class="ui-form-input" id="ui-reset-confirmation" type="password" name="password_confirmation" required minlength="4" autocomplete="new-password">
            </div>
        </div>
        <div class="ui-modal__footer">
            <x-ui.button type="button" data-dialog-cancel>Batal</x-ui.button>
            <x-ui.button variant="primary" type="submit">Simpan password</x-ui.button>
        </div>
    </form>
</dialog>
