<?php

namespace App\Http\Responses;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        $user = $request->user();
        $home = $user instanceof User && $user->isAdmin()
            ? route('admin.dashboard')
            : route('dashboard');

        return redirect()->intended($home);
    }
}
