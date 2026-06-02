<?php

namespace app\Models;

use app\Core\Model;

class ExtraService extends Model
{
    protected string $table = 'extra_services';

    protected array $fillable = [
        'titulo',
        'slug',
        'descripcion_corta',
        'descripcion_larga',
        'imagen',
        'activo',
        'orden',
    ];

    protected array $casts = [
        'id' => 'int',
        'activo' => 'bool',
        'orden' => 'int',
    ];

    public static function getActivos(): array
    {
        $rows = static::query()
            ->where('activo', '=', 1)
            ->orderBy('orden', 'ASC')
            ->orderBy('id', 'ASC')
            ->get();

        return array_map(fn($row) => new static($row), $rows);
    }

    public static function findBySlug(string $slug): ?self
    {
        $row = static::query()
            ->where('slug', '=', trim($slug))
            ->where('activo', '=', 1)
            ->first();

        return $row ? new self($row) : null;
    }

    public static function findActiveById(int $id): ?self
    {
        $row = static::query()
            ->where('id', '=', $id)
            ->where('activo', '=', 1)
            ->first();

        return $row ? new self($row) : null;
    }

    public function leadSubject(): string
    {
        $map = [
            'pasaportes-visas' => 'Solicitud de información - Visas',
            'simcards-viajes-exterior' => 'Solicitud de información - Simcards para viajes al exterior',
            'asistencias-medicas' => 'Solicitud de información - Asistencias médicas',
            'receptivo-tours-internos' => 'Solicitud de información - Receptivo y tours internos',
            'curso-idiomas' => 'Solicitud de información - Curso de idiomas',
        ];

        return $map[$this->slug] ?? ('Solicitud de información - ' . $this->titulo);
    }

    public function defaultWhatsAppMessage(?string $name = null): string
    {
        $message = 'Hola, quiero recibir información sobre ' . trim((string) $this->titulo) . '.';

        $name = trim((string) $name);
        if ($name !== '') {
            $message .= ' Mi nombre es ' . $name . '.';
        }

        return $message;
    }
}
