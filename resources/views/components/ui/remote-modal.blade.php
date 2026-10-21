<x-ui.modal id="ui-remote-modal" title="Memuat…" size="lg" data-dirty-check="true">
    <div data-remote-body><x-ui.form-skeleton /></div>
    <x-slot:footer>
        <x-ui.button type="button" variant="secondary" data-dialog-cancel>Batal</x-ui.button>
        <x-ui.link variant="primary" href="#" data-remote-edit hidden>Edit Data</x-ui.link>
        <x-ui.button type="submit" variant="primary" form="ui-remote-form" data-remote-save hidden>Simpan Data</x-ui.button>
    </x-slot:footer>
</x-ui.modal>
@php($modalRestore = ['url' => old('_ui_modal_url'), 'input' => collect(session()->getOldInput())->except(['_token','password','password_confirmation','current_password'])->all()])
<script type="application/json" data-modal-restore>@json($modalRestore)</script>
