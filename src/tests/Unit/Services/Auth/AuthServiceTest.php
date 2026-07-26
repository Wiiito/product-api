<?php

namespace Tests\Unit\Services\Auth;

use App\DTOs\Auth\LoginData;
use App\DTOs\Auth\RegisterData;
use App\Models\User;
use App\Services\Auth\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_registers_a_new_user_and_returns_a_token(): void
    {
        $result = (new AuthService)->register(new RegisterData('Jane Doe', 'jane@example.com', 'password123'));

        $this->assertInstanceOf(User::class, $result['user']);
        $this->assertSame('jane@example.com', $result['user']->email);
        $this->assertNotEmpty($result['token']);
        $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
    }

    #[Test]
    public function it_logs_in_a_user_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'jane@example.com',
            'password' => 'password123',
        ]);

        $result = (new AuthService)->login(new LoginData('jane@example.com', 'password123'));

        $this->assertTrue($user->is($result['user']));
        $this->assertNotEmpty($result['token']);
    }

    #[Test]
    public function it_rejects_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'jane@example.com',
            'password' => 'password123',
        ]);

        $this->expectException(ValidationException::class);

        (new AuthService)->login(new LoginData('jane@example.com', 'wrong-password'));
    }

    #[Test]
    public function it_logs_out_a_user_by_deleting_the_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api');
        $user->withAccessToken($token->accessToken);

        (new AuthService)->logout($user);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
