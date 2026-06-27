<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

test('student registration fires registered event for email verification', function () {
    Event::fake([Registered::class]);

    $response = $this->post('/register', [
        'name' => 'Pelajar Test',
        'matrik' => 'A123456',
        'email' => 'a123456@siswa.ukm.edu.my',
        'password' => 'Password1!',
        'password_confirmation' => 'Password1!',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('login'));

    $user = User::where('email', 'a123456@siswa.ukm.edu.my')->first();

    expect($user)->not->toBeNull()
        ->and($user->email_verified_at)->toBeNull();

    Event::assertDispatched(Registered::class, fn (Registered $event) => $event->user->is($user));
});

test('registered event sends verification notification', function () {
    Notification::fake();

    event(new Registered($user = User::factory()->unverified()->create([
        'role' => 'pelajar',
        'account_status' => 'active',
    ])));

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('student can verify email with signed link', function () {
    $user = User::factory()->unverified()->create([
        'role' => 'pelajar',
        'account_status' => 'active',
    ]);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        [
            'id' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ],
    );

    $response = $this
        ->actingAs($user)
        ->get($verificationUrl);

    $response->assertRedirect('/dashboard?verified=1');

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});
