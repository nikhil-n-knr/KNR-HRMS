<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Services\Infrastructure\LoggerService;

class ImpersonationService
{
    protected $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    public function canImpersonate(User $impersonator, User $target): bool
    {
        // Only Super Admins can impersonate
        if (!$impersonator->roles->contains('name', 'Super Admin')) return false;

        // Cannot impersonate yourself
        if ($impersonator->id === $target->id) return false;

        // Cannot impersonate other Super Admins
        if ($target->roles->contains('name', 'Super Admin')) return false;

        return true;
    }

    public function impersonate(User $impersonator, User $target): string
    {
        if (!$this->canImpersonate($impersonator, $target)) {
            throw new \Exception("Unauthorized impersonation attempt.");
        }

        // We use Sanctum/Laravel's `loginUsingId` or generate a token.
        // For the current SPA setup, let's assume we return a specialized token 
        // OR we use the built-in session swap if using stateful guard.
        
        // Since we are likely using Sanctum, let's create a special token with a claim
        $token = $target->createToken('impersonation_token', ['*'], now()->addHours(1))->plainTextToken;

        $this->logger->log('security', 'impersonate', "Admin {$impersonator->email} started impersonating {$target->email}");

        return $token;
    }

    public function stopImpersonating()
    {
        // frontend handles dropping the token.
        // Backend just logs.
        // If strict session, we would revert here.
    }
}
