<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PermohonanDokumen extends Model
{
    protected $table = 'permohonan_dokumens';

    protected $fillable = [
        'permohonan_id',
        'jenis_dokumen',
        'file_path',
    ];

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function publicDiskPath(): ?string
    {
        return $this->normalizedStoragePath();
    }

    public function normalizedStoragePath(): ?string
    {
        $path = trim(str_replace('\\', '/', (string) $this->file_path));

        if ($path === '') {
            return null;
        }

        if (Str::contains($path, 'storage/app/public/')) {
            $path = Str::after($path, 'storage/app/public/');
        }

        if (Str::contains($path, 'storage/app/private/')) {
            $path = Str::after($path, 'storage/app/private/');
        }

        if (Str::contains($path, 'public/storage/')) {
            $path = Str::after($path, 'public/storage/');
        }

        $path = ltrim($path, '/');

        foreach (['public/storage/', 'storage/', 'public/'] as $prefix) {
            if (Str::startsWith($path, $prefix)) {
                $path = Str::after($path, $prefix);
                break;
            }
        }

        $path = ltrim($path, '/');

        return $path !== '' ? $path : null;
    }

    public function storageFile(): ?array
    {
        $path = $this->normalizedStoragePath();

        if ($path === null) {
            return null;
        }

        foreach ($this->storageDiskCandidates() as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return [
                    'disk' => $disk,
                    'path' => $path,
                ];
            }
        }

        return null;
    }

    public function storageDiskCandidates(): array
    {
        $rawPath = ltrim(str_replace('\\', '/', (string) $this->file_path), '/');
        $publicFirst = Str::contains($rawPath, ['storage/app/public/', 'public/storage/'])
            || Str::startsWith($rawPath, ['public/', 'storage/', 'dokumen_permohonan/']);

        return $publicFirst ? ['public', 'local'] : ['local', 'public'];
    }

    public function getPublicDiskPathAttribute(): ?string
    {
        return $this->publicDiskPath();
    }

    public function getStoragePathAttribute(): ?string
    {
        return $this->normalizedStoragePath();
    }

    public function getExistsOnStorageAttribute(): bool
    {
        return $this->storageFile() !== null;
    }

    public function getExistsOnPublicDiskAttribute(): bool
    {
        $path = $this->public_disk_path;

        return $path !== null && Storage::disk('public')->exists($path);
    }

    public function getPublicUrlAttribute(): ?string
    {
        return null;
    }

    public function getExtensionAttribute(): string
    {
        return strtolower(pathinfo((string) $this->storage_path, PATHINFO_EXTENSION));
    }

    public function getIsPreviewableAttribute(): bool
    {
        return in_array($this->extension, ['pdf', 'jpg', 'jpeg', 'png'], true);
    }

    public function getIsImageAttribute(): bool
    {
        return in_array($this->extension, ['jpg', 'jpeg', 'png'], true);
    }

    public function getDisplayNameAttribute(): string
    {
        $path = $this->storage_path ?: $this->file_path;

        return $path ? basename($path) : 'Dokumen';
    }
}
