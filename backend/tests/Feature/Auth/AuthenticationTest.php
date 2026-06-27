<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_and_receives_a_token(): void
    {
        Notification::fake();
        Role::factory()->create(['slug' => 'customer']);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Jane Customer',
            'email' => 'jane@example.com',
            'phone' => '+256700000001',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'device_name' => 'React storefront',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.user.email', 'jane@example.com')
            ->assertJsonPath('data.user.role.slug', 'customer')
            ->assertJsonStructure(['data' => ['token']]);

        $user = User::query()->where('email', 'jane@example.com')->firstOrFail();

        $this->assertNotNull($user->cart);
        $this->assertNotNull($user->wishlist);
        $this->assertCount(1, $user->tokens);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_active_user_can_login_and_logout_current_token(): void
    {
        $user = User::factory()->create(['password' => 'SecurePassword123!']);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'SecurePassword123!',
            'device_name' => 'React storefront',
        ])->assertOk();

        $token = $login->json('data.token');

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'password' => 'SecurePassword123!',
            'status' => 'inactive',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'SecurePassword123!',
            'device_name' => 'React storefront',
        ])->assertUnauthorized();
    }

    public function test_email_can_be_verified_with_a_signed_link(): void
    {
        $user = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)],
        );

        $this->getJson($url)->assertOk();

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_password_reset_link_request_does_not_disclose_account_existence(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->postJson('/api/v1/auth/forgot-password', ['email' => $user->email])->assertOk();
        $this->postJson('/api/v1/auth/forgot-password', ['email' => 'missing@example.com'])->assertOk();

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_user_can_reset_password_and_existing_tokens_are_revoked(): void
    {
        $user = User::factory()->create();
        $user->createToken('old device');
        $token = Password::createToken($user);

        $this->postJson('/api/v1/auth/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ])->assertOk();

        $user->refresh();
        $this->assertTrue(Hash::check('NewSecurePassword123!', $user->password));
        $this->assertCount(0, $user->tokens);
    }
}
