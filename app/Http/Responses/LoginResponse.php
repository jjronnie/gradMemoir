<?php

namespace App\Http\Responses;

use App\Support\PostLoginRedirector;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        return redirect()->intended(
            PostLoginRedirector::redirectPath($request->user()),
        );
    }
}
