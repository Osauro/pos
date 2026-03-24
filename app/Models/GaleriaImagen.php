<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GaleriaImagen extends Model
{
    protected $table = 'galeria_imagenes';

    protected $fillable = [
        'url',
        'nombre',
        'tags',
        'veces_usado',
        'subido_por',
    ];

    protected $casts = [
        'tags' => 'array',
        'veces_usado' => 'integer',
    ];

    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por');
    }

    public function getPhotoUrlAttribute(): string
    {
        return asset('storage/' . $this->url);
    }

    public function mergeTags(array $nuevos): void
    {
        $existentes = $this->tags ?? [];
        foreach ($nuevos as $tag) {
            $tag = trim((string) $tag);
            if ($tag !== '' && !in_array($tag, $existentes)) {
                $existentes[] = $tag;
            }
        }
        $this->tags = $existentes;
    }
}
