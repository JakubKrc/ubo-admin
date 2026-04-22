<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GitHubController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('github')->redirect();
    }

    public function callback()
    {
        $ghUser = Socialite::driver('github')->user();

        $user = User::firstOrCreate(
            ['email' => $ghUser->getEmail()],
            [
                'name' => $ghUser->getName() ?: $ghUser->getNickname(),
                'password' => bcrypt(Str::random(40)),
                'email_verified_at' => now(),
            ]
        );

        $user->update([
            'github_id' => $ghUser->getId(),
            'github_login' => $ghUser->getNickname(),
        ]);

        Auth::login($user, remember: true);

        return redirect('/dashboard');
    }
}
