<?php

namespace Tests\Feature;

use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class UIFeedbackTest extends TestCase
{
    public function test_invalid_wellness_submission_keeps_backend_validation(): void
    {
        $this->from('/wellness')->post(route('wellness.store'), [])
            ->assertRedirect('/wellness')
            ->assertSessionHasErrors(['name', 'nik', 'position', 'site', 'shift', 'sleep_before_shift', 'health_complaint', 'drowsy_medication', 'work_readiness']);
    }

    public function test_notification_provider_keeps_field_bags_and_sanitizes_diagnostics(): void
    {
        session()->flash('error', 'SQLSTATE[23000] token=secret');
        $errors = (new ViewErrorBag)->put('updatePassword', new MessageBag(['password' => ['Password minimal 8 karakter.']]));
        $html = view('components.ui.toast-provider', compact('errors'))->render();
        $this->assertStringNotContainsString('SQLSTATE', $html);
        $this->assertStringNotContainsString('token=secret', $html);
        $this->assertStringContainsString('updatePassword', $html);
        $this->assertStringContainsString('Password minimal 8 karakter.', $html);
        $this->assertStringContainsString('role="alert"', $html);
    }

    public function test_empty_search_and_filter_states_are_distinct(): void
    {
        $this->assertStringContainsString('Pencarian tidak ditemukan', $this->blade('<x-ui.empty-state kind="search" />')->__toString());
        $this->assertStringContainsString('Tidak ada data yang sesuai', $this->blade('<x-ui.empty-state kind="filter" />')->__toString());
        $this->assertStringNotContainsString('Tambah data', $this->blade('<x-ui.empty-state action-href="/create" />')->__toString());
    }
}
