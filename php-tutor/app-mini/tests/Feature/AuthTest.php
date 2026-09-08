<?php
declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * 注册/登录/me 功能测试。
 */
final class AuthTest extends TestCase
{
    public function testRegisterReturnsCreatedUserWithoutPassword(): void
    {
        $response = $this->request('POST', '/api/v1/auth/register', [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'password123',
        ]);

        self::assertSame(201, $response->getStatusCode());
        $payload = $this->decode($response);
        self::assertTrue($payload['success']);
        self::assertSame('alice@example.com', $payload['data']['email']);
        self::assertArrayHasKey('id', $payload['data']);
        self::assertArrayNotHasKey('password', $payload['data']);
    }

    public function testRegisterRejectsDuplicateEmailWith422(): void
    {
        $this->registerUser();
        $response = $this->request('POST', '/api/v1/auth/register', [
            'name' => 'Bob',
            'email' => 'alice@example.com',
            'password' => 'password123',
        ]);

        self::assertSame(422, $response->getStatusCode());
        $payload = $this->decode($response);
        self::assertFalse($payload['success']);
        self::assertSame('validation_failed', $payload['error']['code']);
        self::assertArrayHasKey('email', $payload['error']['details']);
    }

    public function testRegisterRejectsInvalidEmailWith422(): void
    {
        $response = $this->request('POST', '/api/v1/auth/register', [
            'name' => 'Alice',
            'email' => 'not-an-email',
            'password' => 'password123',
        ]);

        self::assertSame(422, $response->getStatusCode());
    }

    public function testRegisterRejectsShortPasswordWith422(): void
    {
        $response = $this->request('POST', '/api/v1/auth/register', [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'short',
        ]);

        self::assertSame(422, $response->getStatusCode());
    }

    public function testLoginReturnsBearerTokenAndUser(): void
    {
        $this->registerUser();
        $response = $this->request('POST', '/api/v1/auth/login', [
            'email' => 'alice@example.com',
            'password' => 'password123',
        ]);

        self::assertSame(200, $response->getStatusCode());
        $payload = $this->decode($response);
        self::assertTrue($payload['success']);
        self::assertNotEmpty($payload['data']['token']);
        self::assertSame('alice@example.com', $payload['data']['user']['email']);
    }

    public function testLoginWithWrongPasswordReturns401(): void
    {
        $this->registerUser();
        $response = $this->request('POST', '/api/v1/auth/login', [
            'email' => 'alice@example.com',
            'password' => 'wrong-password',
        ]);

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('unauthorized', $this->decode($response)['error']['code']);
    }

    public function testMeRequiresAuthentication(): void
    {
        $response = $this->request('GET', '/api/v1/auth/me');

        self::assertSame(401, $response->getStatusCode());
    }

    public function testMeReturnsCurrentUserWithValidToken(): void
    {
        $this->registerUser();
        $token = $this->login();
        $response = $this->request('GET', '/api/v1/auth/me', token: $token);

        self::assertSame(200, $response->getStatusCode());
        $payload = $this->decode($response);
        self::assertSame('alice@example.com', $payload['data']['email']);
    }

    public function testMeRejectsTamperedToken(): void
    {
        $this->registerUser();
        $token = $this->login();
        // 篡改 token 使其签名失效
        $response = $this->request('GET', '/api/v1/auth/me', token: $token . 'x');

        self::assertSame(401, $response->getStatusCode());
    }
}
