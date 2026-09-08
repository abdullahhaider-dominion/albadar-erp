<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_rate_limited_after_repeated_failures(): void
    {
        RateLimiter::clear('test');

        User::factory()->admin()->create([
            'email' => 'guard@example.com',
            'password' => 'secret-password',
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->from(route('login'))->post(route('login.store'), [
                'email' => 'guard@example.com',
                'password' => 'wrong-password',
            ])->assertRedirect(route('login'));
        }

        $this->from(route('login'))->post(route('login.store'), [
            'email' => 'guard@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertStringContainsString(
            'Too many login attempts',
            session('errors')->first('email')
        );
    }

    public function test_security_headers_and_robots_are_present(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff');

        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /', false);
    }

    public function test_backup_requires_post_and_admin(): void
    {
        $editor = User::factory()->create(['role' => User::ROLE_EDITOR]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($editor)->post(route('settings.backup'))->assertForbidden();
        $this->actingAs($admin)->get(route('settings.backup'))->assertMethodNotAllowed();
    }
}
