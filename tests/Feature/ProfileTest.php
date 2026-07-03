<?php

use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('student profile faculty can be updated', function () {
    $user = User::factory()->create([
        'role' => 'pelajar',
        'matrik' => 'A123456',
        'email' => 'a123456@siswa.ukm.edu.my',
        'fakulti' => null,
    ]);

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Pelajar Fakulti',
            'matrik' => 'A123456',
            'fakulti' => 'Fakulti Undang-Undang',
            'email' => 'a123456@siswa.ukm.edu.my',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Fakulti Undang-Undang', $user->fakulti);
});

test('student dashboard profile card uses saved faculty and fixed academic session', function () {
    $user = User::factory()->create([
        'role' => 'pelajar',
        'matrik' => 'A123456',
        'email' => 'a123456@siswa.ukm.edu.my',
        'fakulti' => 'Fakulti Undang-Undang',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard.pelajar'))
        ->assertOk()
        ->assertSee('Fakulti Undang-Undang')
        ->assertSee('Sesi Akademik')
        ->assertSee('2025/2026')
        ->assertDontSee('FTSM')
        ->assertDontSee('Sains Komputer')
        ->assertDontSee('Program');
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});
