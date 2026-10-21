@props([
  // expect: $thread (App\Models\QaThread) sudah di-pass dari controller
  'thread',
])

<div
  x-data="qaResolve({
    open: false,
    note: '',
    resolved: {{ $thread->status === 'resolved' ? 'true' : 'false' }},
  })"
  x-id="['modal-title']"
  class="inline-flex"
>
  {{-- Badge status --}}
  <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full"
        :class="resolved ? 'ui-status ui-status--success' : 'ui-status ui-status--warning'">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
      <path x-show="resolved" d="M9 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4z"/>
      <path x-show="!resolved" d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 5v6h-2V7h2zm0 8v2h-2v-2h2z"/>
    </svg>
    <span x-text="resolved ? 'Resolved' : 'Open'"></span>
  </span>

  {{-- Tombol buka modal (nonaktif jika sudah resolved) --}}
  <x-ui.button variant="success" type="button"
          class="ml-2 inline-flex items-center gap-2 px-3 py-2 text-sm rounded-lg border transition disabled:opacity-50 disabled:cursor-not-allowed"
          @click="open = true" ::disabled="resolved"
          title="Tandai thread ini sebagai selesai">
    <x-ui.lucide name="circle-check" />
    Tandai Selesai
  </x-ui.button>

  {{-- Backdrop --}}
  <div x-cloak x-show="open"
       x-transition.opacity
       class="fixed inset-0 bg-black/55 backdrop-blur-[2px] z-40"
       @click="open=false" @keydown.escape.window="open=false">
  </div>

  {{-- Modal standard --}}
  <div x-cloak x-show="open"
       x-transition
       role="dialog"
       aria-modal="true"
       :aria-labelledby="$id('modal-title')"
       class="fixed z-50 inset-0 flex items-center justify-center p-4">
    <div class="ui-modal ui-modal--md w-full relative" @click.outside="open=false">
      <form method="POST" action="{{ route('qa.resolve', $thread) }}">
        @csrf
        <div class="ui-modal__header">
          <div class="ui-modal__icon ui-modal__icon--success">
            <x-ui.lucide name="circle-check" />
          </div>
          <div class="ui-modal__header-content">
            <h2 :id="$id('modal-title')" class="ui-modal__title">Selesaikan Thread</h2>
            <p class="ui-modal__description">
              Tulis ringkasan penyelesaian thread ini untuk dokumentasi.
            </p>
          </div>
          <button type="button" class="ui-modal__close" @click="open=false" aria-label="Tutup">
            <x-ui.lucide name="x" />
          </button>
        </div>

        <div class="ui-modal__body">
          <div class="ui-form-group">
            <label class="ui-form-label" for="qa-resolution-note">Catatan penyelesaian</label>
            <textarea id="qa-resolution-note" name="resolution_note" x-model="note" rows="4"
                      class="ui-form-input"
                      placeholder="Contoh: Issue teratasi setelah update konfigurasi X dan sinkronisasi ulang."
            ></textarea>
            <p class="ui-form-helper">Opsional: catatan ini akan disimpan dalam riwayat penanganan thread.</p>
          </div>
        </div>

        <div class="ui-modal__footer">
          <x-ui.button variant="secondary" type="button" @click="open=false">
            Batal
          </x-ui.button>
          <x-ui.button variant="success" type="submit">
            <x-ui.lucide name="circle-check" />
            Selesaikan
          </x-ui.button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Alpine component --}}
<script>
  function qaResolve(state){
    return {
      ...state,
    }
  }
</script>
