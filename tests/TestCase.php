<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function withMobileToken(User $user, string $deviceName = 'test-device'): static
    {
        $token = $user->createToken($deviceName, ['mobile', 'role:'.$user->role])->plainTextToken;

        return $this->withHeader('Authorization', 'Bearer '.$token);
    }
}
