<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class GoogleAuthRedirectTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('buyer');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_google_login_remembers_a_local_destination(): void
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('redirect')
            ->once()
            ->andReturn(redirect('https://accounts.google.com'));

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($provider);

        $this->get(route('auth.google', ['redirect' => '/apply']))
            ->assertRedirect('https://accounts.google.com')
            ->assertSessionHas('url.intended', '/apply');
    }

    public function test_google_callback_redirects_to_the_remembered_destination(): void
    {
        $user = User::factory()->create(['email' => 'player@example.com']);
        $googleUser = (object) [
            'email' => $user->email,
            'name' => $user->name,
        ];

        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andReturn($googleUser);

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($provider);

        $this->withSession(['url.intended' => '/apply'])
            ->get(route('auth.google.callback'))
            ->assertRedirect('/apply');

        $this->assertAuthenticatedAs($user);
    }

    public function test_google_login_rejects_an_external_destination(): void
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('redirect')
            ->once()
            ->andReturn(redirect('https://accounts.google.com'));

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($provider);

        $this->get(route('auth.google', ['redirect' => 'https://malicious.example/steal']))
            ->assertSessionHas('url.intended', route('home'));
    }
}
