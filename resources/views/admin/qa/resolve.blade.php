
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
        :class="resolved ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
      <path x-show="resolved" d="M9 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4z"/>
      <path x-show="!resolved" d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 5v6h-2V7h2zm0 8v2h-2v-2h2z"/>
    </svg>
    <span x-text="resolved ? 'Resolved' : 'Open'"></span>
  </span>

  {{-- Tombol buka modal (nonaktif jika sudah resolved) --}}
  <button type="button"
          class="ml-2 inline-flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-lg border border-coal-200/70 dark:border-coal-700 bg-white dark:bg-coal-900 hover:bg-emerald-50 hover:border-emerald-300 transition disabled:opacity-50 disabled:cursor-not-allowed"
          @click="open = true" :disabled="resolved"
          title="Tandai thread ini sebagai selesai">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.2L4.8 12 3.4 13.4 9 19l12-12-1.4-1.4z"/></svg>
    Tandai Selesai
  </button>

  {{-- Backdrop --}}
  <div x-cloak x-show="open"
       x-transition.opacity
       class="fixed inset-0 bg-black/40 z-40"
       @click="open=false" @keydown.escape.window="open=false">
  </div>

  {{-- Modal --}}
  <div x-cloak x-show="open"
       x-transition
       role="dialog"
       aria-modal="true"
       :aria-labelledby="$id('modal-title')"
       class="fixed z-50 inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-coal-900 shadow-xl border border-coal-200/60 dark:border-coal-800">
      <form method="POST" action="{{ route('qa.resolve', $thread) }}">
        @csrf
        <div class="p-5 border-b border-coal-100 dark:border-coal-800">
          <h2 :id="$id('modal-title')" class="text-lg font-semibold">Selesaikan Thread</h2>
          <p class="mt-1 text-sm text-coal-600 dark:text-coal-300">
            Opsional: tulis ringkasan penyelesaian untuk dokumentasi.
          </p>
        </div>

        <div class="p-5 space-y-3">
          <label class="block text-sm font-medium mb-1">Catatan penyelesaian</label>
          <textarea name="resolution_note" x-model="note" rows="5"
                    class="w-full rounded-xl border border-coal-200 dark:border-coal-700 bg-white dark:bg-coal-950 focus:outline-none focus:ring-2 focus:ring-emerald-400/60 p-3"
                    placeholder="Contoh: Issue teratasi setelah update konfigurasi X dan sinkronisasi ulang."
          ></textarea>
        </div>

        <div class="px-5 pb-5 flex items-center justify-end gap-2">
          <button type="button" @click="open=false"
                  class="px-3 py-1.5 text-sm rounded-lg border border-coal-200 dark:border-coal-700">
            Batal
          </button>
          <button type="submit"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-lg bg-emerald-600 text-white hover:bg-emerald-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.2L4.8 12 3.4 13.4 9 19l12-12-1.4-1.4z"/></svg>
            Selesaikan
          </button>
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
