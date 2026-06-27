<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

test('donor password setup marks email as verified', function () {
    Event::fake([Verified::class]);

    $donor = User::factory()->unverified()->create([
        'role' => 'penderma',
        'account_status' => 'invited',
        'email' => 'donor@example.com',
    ]);
    $token = Password::broker()->createToken($donor);

    $response = $this->post(route('password.store'), [
        'token' => $token,
        'email' => $donor->email,
        'password' => 'Password1!',
        'password_confirmation' => 'Password1!',
    ]);

    $response
        ->assertRedirect(route('login'))
        ->assertSessionHasNoErrors();

    $donor->refresh();

    expect($donor->account_status)->toBe('active')
        ->and($donor->email_verified_at)->not->toBeNull()
        ->and(Hash::check('Password1!', $donor->password))->toBeTrue();

    Event::assertDispatched(Verified::class, fn (Verified $event) => $event->user->is($donor));
});
