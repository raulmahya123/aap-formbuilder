<?php

namespace Tests\Feature;

use App\Models\{DailyNote, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyNoteDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_has_primary_create_button_and_safe_full_description_dialog(): void
    {
        $user = User::factory()->create(['role'=>'super_admin', 'is_active'=>true]);
        $note = DailyNote::create(['user_id'=>$user->id, 'title'=>'Catatan QA', 'content'=>"Baris pertama\n<script>alert('test')</script>", 'note_time'=>now()]);
        $this->actingAs($user)->get(route('user.daily_notes.index'))->assertOk()
            ->assertSee('ui-button--primary', false)->assertSee('Lihat deskripsi lengkap')
            ->assertSee('id="daily-note-'.$note->id.'"', false)
            ->assertSee('&lt;script&gt;', false)->assertDontSee("<script>alert('test')</script>", false)
            ->assertDontSee('descSize', false)->assertDontSee('cdn.jsdelivr.net/npm/alpinejs', false);
    }
}
