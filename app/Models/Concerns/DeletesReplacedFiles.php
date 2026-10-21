<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Hapus file lama di disk public saat kolom file diganti atau record dihapus.
 * Model mendefinisikan: protected array $fileColumns = ['image'];
 */
trait DeletesReplacedFiles
{
    protected static function bootDeletesReplacedFiles(): void
    {
        static::updated(function ($model) {
            foreach ($model->fileColumns as $column) {
                if ($model->wasChanged($column)) {
                    static::deletePublicFile($model->getOriginal($column));
                }
            }
        });

        static::deleted(function ($model) {
            foreach ($model->fileColumns as $column) {
                static::deletePublicFile($model->getAttribute($column));
            }
        });
    }

    protected static function deletePublicFile(?string $path): void
    {
        // URL eksternal (data demo seeder) bukan file kita
        if ($path && ! Str::startsWith($path, 'http')) {
            Storage::disk('public')->delete($path);
        }
    }
}
