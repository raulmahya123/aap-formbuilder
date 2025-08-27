<p>Halo {{ $entry->user->name }},</p>
<p>Status entri untuk form <b>{{ $entry->form->title }}</b> telah berubah menjadi <b>{{ strtoupper($entry->status) }}</b>.</p>
@if($notes)
<p><b>Catatan:</b> {{ $notes }}</p>
@endif
<p>Nomor Entri: #{{ $entry->id }} — Tanggal: {{ $entry->created_at->format('d/m/Y H:i') }}</p>
<p>Terima kasih.</p>
