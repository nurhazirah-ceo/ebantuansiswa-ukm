<?php

use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

test('admin decision saves editable textarea justification instead of reason dropdown value', function () {
    Notification::fake();

    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);
    $student = User::factory()->create([
        'role' => 'pelajar',
        'matrik' => 'A123456',
        'email' => 'a123456@siswa.ukm.edu.my',
    ]);
    $permohonan = Permohonan::create([
        'user_id' => $student->id,
        'no_kelompok' => 'TEST-'.uniqid(),
        'tarikh_mohon' => now(),
        'jenis_bantuan' => 'bantuan_asas_hidup',
        'status' => 'Sedang Disemak',
        'catatan' => 'Permohonan sedang disemak.',
    ]);
    $editedJustification = 'Permohonan diluluskan selepas semakan manual pentadbir dan catatan ini telah diedit.';

    $response = $this
        ->actingAs($admin)
        ->from(route('admin.permohonan.show', $permohonan))
        ->patch(route('admin.permohonan.keputusan', $permohonan), [
            'keputusan' => 'Diluluskan',
            'decision_reason' => 'Memenuhi syarat kelayakan bantuan',
            'admin_catatan' => $editedJustification,
        ]);

    $response
        ->assertRedirect(route('admin.permohonan.show', $permohonan))
        ->assertSessionHasNoErrors();

    $permohonan->refresh();

    expect($permohonan->status)->toBe('Diluluskan');
    expect($permohonan->catatan)->toBe($editedJustification);
    expect($permohonan->admin_catatan)->toBe($editedJustification);
    expect($permohonan->admin_catatan)->not->toBe('Memenuhi syarat kelayakan bantuan');
});
