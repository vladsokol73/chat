<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ErpSsoService
{
    public function exchangeToSession(object $decodedJwt): User
    {
        $externalId = (string) ($decodedJwt->sub ?? '');
        $email = (string) ($decodedJwt->email ?? '');
        $name = (string) ($decodedJwt->name ?? '');

        if (! $externalId && ! $email) {
            throw new \InvalidArgumentException('Invalid token payload');
        }

        $user = null;
        if ($email) {
            $user = User::query()->where('email', $email)->first();
        }
        if (! $user) {
            $user = User::query()->create([
                'name' => $name ?: ($email ?: 'ERP User'),
                'email' => $email ?: (uniqid('erp_', true).'@local'),
                'password' => '\\',
            ]);
        }

        Auth::login($user, true);

        return $user;
    }
}
