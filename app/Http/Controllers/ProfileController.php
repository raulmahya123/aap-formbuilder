<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\{Department, Site, User};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        
        $user->load(['departments', 'sites']);
        
        $allSites = $user->isSuperAdmin()
            ? Site::orderBy('code')->get(['id', 'code', 'name'])
            : $user->sites()->orderBy('code')->get(['sites.id', 'sites.code', 'sites.name']);

        $allDepartments = Department::orderBy('name')->get(['id', 'name']);

        $userDepartment = $user->departments->first() ?? ($user->department_id ? Department::find($user->department_id) : null);

        return view('profile.edit', [
            'user'           => $user,
            'allSites'       => $allSites,
            'allDepartments' => $allDepartments,
            'userDepartment' => $userDepartment,
        ]);
    }

    /**
     * Update the user's personal profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['avatar']);
        $user = $request->user();

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Dedicated Endpoint: Upload avatar via AJAX (Drag & drop / crop)
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);

        $user = $request->user();

        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar_path' => $path]);

        return response()->json([
            'success'    => true,
            'avatar_url' => asset('storage/' . $path),
            'message'    => 'Foto profil berhasil diperbarui.',
        ]);
    }

    /**
     * Dedicated Endpoint: Delete avatar & return to initials
     */
    public function deleteAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->update(['avatar_path' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Foto profil berhasil dihapus.',
        ]);
    }

    /**
     * Super Admin Only: Update employment information (Site, Position, Department, Role)
     */
    public function updateEmployment(Request $request): JsonResponse
    {
        $currentUser = $request->user();
        abort_unless($currentUser->isSuperAdmin(), 403, 'Hanya Super Admin yang diizinkan mengubah informasi pekerjaan.');

        $data = $request->validate([
            'user_id'       => ['required', 'integer', 'exists:users,id'],
            'position'      => ['nullable', 'string', 'max:100'],
            'division'      => ['nullable', 'string', 'max:100'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'site_ids'      => ['nullable', 'array'],
            'site_ids.*'    => ['integer', 'exists:sites,id'],
            'role'          => ['required', 'in:user,admin_ho,admin_site,super_admin'],
        ]);

        $targetUser = User::findOrFail($data['user_id']);
        if ($targetUser->id === $currentUser->id && !in_array($data['role'], ['super_admin','admin_ho'], true)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['role'=>'Role akun Anda sendiri tidak dapat diturunkan.']);
        }
        $siteIds = $data['site_ids'] ?? $targetUser->sites()->pluck('sites.id')->all();
        if (!in_array($data['role'], ['super_admin','admin_ho'], true) && !$siteIds) {
            throw \Illuminate\Validation\ValidationException::withMessages(['site_ids'=>'Pilih minimal satu site untuk Admin Site atau User.']);
        }

        DB::transaction(function () use ($targetUser, $data) {
            $targetUser->update([
                'position'      => $data['position'] ?? null,
                'division'      => $data['division'] ?? null,
                'department_id' => $data['department_id'] ?? null,
                'role'          => $data['role'],
            ]);

            if (isset($data['site_ids'])) {
                $targetUser->sites()->sync($data['site_ids']);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Informasi pekerjaan berhasil diperbarui.',
        ]);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
