@props([
  /** @var \App\Models\QaThread $thread */
  'thread',
  /** @var \Illuminate\Database\Eloquent\Collection<int,\App\Models\QaMessage> $messages */
  'messages' => collect(),
])

<div
  x-data="qaChat({
    maxLen: 10000,
    isResolved: {{ $thread->status === 'resolved' ? 'true' : 'false' }},
    sending: false,
    text: '',
  })"
  class="w-full"
>
  {{-- Header --}}
  <div class="flex items-center justify-between mb-3">
    <h2 class="text-base font-semibold">Diskusi</h2>
    <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full"
          :class="isResolved ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
        <path x-show="isResolved" d="M9 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4z"/>
        <path x-show="!isResolved" d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 5v6h-2V7h2zm0 8v2h-2v-2h2z"/>
      </svg>
      <span x-text="isResolved ? 'Resolved' : 'Open'"></span>
    </span>
  </div>

  {{-- LIST PESAN --}}
  <div id="qa-message-list"
       class="space-y-3 max-h-[52vh] overflow-auto pr-1 pb-2"
  >
    @forelse($messages as $msg)
      @php
        $isMe = auth()->id() === $msg->user_id;
      @endphp
      <div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }}">
        <div class="max-w-[78%] rounded-2xl px-4 py-2 shadow-sm border
                    {{ $isMe ? 'bg-emerald-600 text-white border-emerald-700' : 'bg-white dark:bg-coal-900 border-coal-200/70 dark:border-coal-800' }}">
          <div class="text-sm leading-relaxed whitespace-pre-line break-words">
            {{ $msg->message }}
          </div>
          <div class="mt-1.5 text-[11px] opacity-80 {{ $isMe ? 'text-white' : 'text-coal-500 dark:text-coal-300' }}">
            {{ $msg->author?->name ?? 'Unknown' }} • {{ $msg->created_at->format('d M Y H:i') }}
          </div>
        </div>
      </div>
    @empty
      <div class="text-sm text-coal-500 dark:text-coal-300 italic">Belum ada pesan.</div>
    @endforelse
  </div>

  {{-- FORM KIRIM PESAN --}}
  <form
    x-ref="form"
    method="POST"
    action="{{ route('admin.qa.messages.store', $thread) }}"
    class="mt-4"
    @submit="sending = true"
  >
    @csrf
    <div class="rounded-2xl border border-coal-200/70 dark:border-coal-800 bg-white dark:bg-coal-900">
      <div class="p-3">
        <textarea
          name="message"
          x-model="text"
          x-ref="textarea"
          x-on:input="autoGrow($el)"
          :maxlength="maxLen"
          rows="2"
          placeholder="Tulis pesan… (Enter = baris baru, Ctrl+Enter = kirim)"
          class="w-full bg-transparent focus:outline-none resize-none text-sm leading-relaxed"
          :disabled="isResolved || sending"
          @keydown.ctrl.enter.prevent="$refs.submitBtn.click()"
        ></textarea>
      </div>
      <div class="px-3 pb-3 flex items-center justify-between text-xs">
        <div class="text-coal-500 dark:text-coal-300">
          <span x-text="`${text.length}/${maxLen}`"></span>
        </div>
        <div class="flex items-center gap-2">
          <button
            type="button"
            class="px-3 py-1.5 rounded-lg border text-xs"
            @click="text=''; $nextTick(()=>autoGrow($refs.textarea))"
            :disabled="isResolved || sending"
          >Bersihkan</button>

          <button
            x-ref="submitBtn"
            type="submit"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs bg-emerald-600 text-white hover:bg-emerald-500 disabled:opacity-50"
            :disabled="isResolved || sending || text.trim().length===0"
          >
            <svg x-show="!sending" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M2 21l21-9L2 3v7l15 2-15 2z"/></svg>
            <svg x-show="sending" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="4" class="opacity-25"/><path d="M4 12a8 8 0 018-8" stroke-width="4" class="opacity-75"/></svg>
            <span x-text="sending ? 'Mengirim…' : 'Kirim'"></span>
          </button>
        </div>
      </div>
    </div>

    @if ($thread->status === 'resolved')
      <p class="mt-2 text-[13px] text-amber-700">Thread sudah ditandai selesai. Pesan baru dinonaktifkan.</p>
    @endif
    @error('message')
      <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
  </form>
</div>

{{-- Alpine helpers --}}
<script>
function qaChat({ maxLen, isResolved, sending, text }) {
  return {
    maxLen, isResolved, sending, text,
    autoGrow(el) {
      // autosize textarea
      el.style.height = 'auto';
      el.style.height = (el.scrollHeight) + 'px';
    },
  }
}
</script>
