<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;

/** Shared set-up: empty in-memory database + the real page content imported from database/seeders/content. */
abstract class CmsTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('uploads');
        $this->artisan('content:import', ['--force' => true, '--yes' => true])->assertSuccessful();
    }

    protected function admin(array $attrs = []): User
    {
        return $this->makeUser(User::ROLE_ADMIN, $attrs);
    }

    protected function editor(array $attrs = []): User
    {
        return $this->makeUser(User::ROLE_EDITOR, $attrs);
    }

    private function makeUser(string $role, array $attrs): User
    {
        static $n = 0;
        $n++;
        $user = new User(['name' => ucfirst($role) . " {$n}", 'email' => "{$role}{$n}@example.test", 'password' => $attrs['password'] ?? 'Correct-Horse-9']);
        $user->role = $role;
        $user->is_active = $attrs['is_active'] ?? true;
        $user->save();
        return $user;
    }

    protected function tearDown(): void
    {
        RateLimiter::clear('x');
        parent::tearDown();
    }
}
