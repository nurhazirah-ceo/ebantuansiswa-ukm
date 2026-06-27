<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permohonan;
use App\Models\PermohonanDokumen;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StatusController extends Controller
{
    public function index()
    {
        $permohonan = Permohonan::query()
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return view('pelajar.status-permohonan.status-permohonan-pelajar', compact('permohonan'));
    }

    public function show($id)
    {
        $permohonan = Permohonan::with(['pelajar', 'bantuan', 'dokumens'])
            ->findOrFail($id);

        abort_unless((int) $permohonan->user_id === (int) Auth::id(), 403);

        return view('pelajar.status-permohonan.show', compact('permohonan'));
    }

    public function document(Request $request, PermohonanDokumen $dokumen)
    {
        $dokumen->loadMissing('permohonan');

        $user = $request->user();
        $isOwnerStudent = $user?->role === 'pelajar'
            && (int) $dokumen->permohonan?->user_id === (int) $user->id;
        $isAdmin = $user?->role === 'admin';

        abort_unless($isOwnerStudent || $isAdmin, 403);

        $file = $dokumen->storageFile();
        abort_unless($file !== null, 404);

        $disk = $file['disk'];
        $path = $file['path'];
        $fileName = $dokumen->display_name;
        $safeFileName = str_replace(['"', "\r", "\n"], '_', $fileName);

        if ($request->boolean('download')) {
            return Storage::disk($disk)->download($path, $safeFileName);
        }

        $mimeType = Storage::disk($disk)->mimeType($path) ?: 'application/octet-stream';

        return response(Storage::disk($disk)->get($path), 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="'.$safeFileName.'"',
        ]);
    }
}
