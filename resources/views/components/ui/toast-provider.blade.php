@once
@php
    $notifications = [];
    foreach (['success' => ['success', 'ok'], 'error' => ['error'], 'warning' => ['warning'], 'info' => ['info']] as $type => $keys) {
        foreach ($keys as $key) {
            if (session()->has($key)) {
                $notifications[] = ['type' => $type, 'message' => \App\Support\Feedback::message(session($key))];
            }
        }
    }
    $statuses = ['profile-updated' => 'Profil berhasil diperbarui.', 'password-updated' => 'Password berhasil diperbarui.', 'verification-link-sent' => 'Tautan verifikasi telah dikirim.'];
    if (session('status')) $notifications[] = ['type' => 'success', 'message' => \App\Support\Feedback::message($statuses[session('status')] ?? session('status'))];
    if (collect($errors->getBags())->contains(fn ($bag) => $bag->any())) $notifications[] = ['type' => 'error', 'message' => 'Periksa isian yang ditandai sebelum menyimpan kembali.'];
@endphp
<div class="ui-toast-provider" data-toast-provider aria-label="Notifikasi">
    @foreach($notifications as $notification)
        <div class="ui-toast ui-toast--{{ $notification['type'] }}" data-toast data-type="{{ $notification['type'] }}" role="{{ $notification['type'] === 'error' ? 'alert' : 'status' }}" aria-atomic="true">
            <x-ui.lucide :name="$notification['type'] === 'success' ? 'circle-check' : 'alert-triangle'" />
            <p>{{ $notification['message'] }}</p>
            <button type="button" data-toast-close aria-label="Tutup notifikasi"><x-ui.lucide name="x" /></button>
        </div>
    @endforeach
</div>
@php
    $fieldErrors = [];
    foreach ($errors->getBags() as $bag => $messages) {
        foreach ($messages->messages() as $field => $list) {
            $fieldErrors[$bag][$field] = array_map(fn ($message) => \App\Support\Feedback::message($message, 'Periksa kembali nilai pada isian ini.'), $list);
        }
    }
@endphp
<script type="application/json" data-form-errors>@json($fieldErrors)</script>
@endonce
