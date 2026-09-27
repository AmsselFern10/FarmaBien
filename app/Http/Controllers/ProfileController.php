<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $cambiosAntes = $user->only(['name', 'email']);

        $user->fill($request->validated());

        $emailCambio = $user->isDirty('email');
        if ($emailCambio) {
            $user->email_verified_at = null;
        }

        $user->save();

        AuditLog::log('perfil', 'actualizar_datos', "Perfil de usuario '{$user->name}' actualizado", [
            'user_id'      => $user->id,
            'antes'        => $cambiosAntes,
            'email_cambio' => $emailCambio,
        ]);

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
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

        AuditLog::log('perfil', 'eliminar_cuenta', "Cuenta de usuario '{$user->name}' ({$user->email}) eliminada", [
            'user_id' => $user->id,
            'email'   => $user->email,
        ]);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
