<?php

use App\Models\User;

function activeUser(array $attributes): User
{
    return User::factory()->create(array_merge([
        'account_status' => 'active',
    ], $attributes));
}

test('student can login with matric number only', function () {
    $student = activeUser([
        'name' => 'Pelajar Test',
        'matrik' => 'A209198',
        'email' => 'a209198@siswa.ukm.edu.my',
        'role' => 'pelajar',
    ]);

    $response = $this->post('/login', [
        'identifier' => 'A209198',
        'password' => 'password',
    ]);

    $response->assertRedirect('/dashboard/pelajar');
    $this->assertAuthenticatedAs($student);
});

test('student email login shows matric-only guidance', function () {
    activeUser([
        'name' => 'Pelajar Test',
        'matrik' => 'A209198',
        'email' => 'a209198@siswa.ukm.edu.my',
        'role' => 'pelajar',
    ]);

    $response = $this->post('/login', [
        'identifier' => 'A209198@siswa.ukm.edu.my',
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors([
        'identifier' => 'Untuk pelajar, sila masukkan nombor matrik sahaja tanpa @siswa.ukm.edu.my. Contoh: AXXXXXX',
    ]);
    $this->assertGuest();
});

test('admin can login with email', function () {
    $admin = activeUser([
        'email' => 'admin@example.com',
        'role' => 'admin',
    ]);

    $response = $this->post('/login', [
        'identifier' => 'admin@example.com',
        'password' => 'password',
    ]);

    $response->assertRedirect('/dashboard/admin');
    $this->assertAuthenticatedAs($admin);
});

test('donor can login with email', function () {
    $donor = activeUser([
        'email' => 'donor@example.com',
        'role' => 'penderma',
    ]);

    $response = $this->post('/login', [
        'identifier' => 'donor@example.com',
        'password' => 'password',
    ]);

    $response->assertRedirect('/dashboard/penderma');
    $this->assertAuthenticatedAs($donor);
});

test('wrong password keeps generic login error', function () {
    activeUser([
        'matrik' => 'A209198',
        'email' => 'a209198@siswa.ukm.edu.my',
        'role' => 'pelajar',
    ]);

    $response = $this->post('/login', [
        'identifier' => 'A209198',
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors([
        'identifier' => 'Emel / No Matrik atau kata laluan tidak sah',
    ]);
    $this->assertGuest();
});
