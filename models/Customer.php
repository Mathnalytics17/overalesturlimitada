<?php

namespace app\Models;

use app\Core\Model;

class Customer extends Model
{
    protected string $table = 'customers';
    protected bool $softDeletes = true;

    protected array $fillable = [
        'uuid',
        'first_name',
        'last_name',
        'full_name',
        'email',
        'phone',
        'whatsapp',
        'document_type',
        'document_number',
        'birth_date',
        'country_id',
        'city_id',
        'address',
        'status',
        'source',
        'notes',
        'accepts_marketing',
    ];

    protected array $casts = [
        'id' => 'int',
        'country_id' => 'int',
        'city_id' => 'int',
        'accepts_marketing' => 'bool',
    ];

    public static function findByEmail(string $email): ?static
    {
        $row = static::query()
            ->where('email', '=', $email)
            ->first();

        return $row ? new static($row) : null;
    }

    public static function findByDocument(string $documentNumber): ?static
    {
        $row = static::query()
            ->where('document_number', '=', $documentNumber)
            ->first();

        return $row ? new static($row) : null;
    }

    public function fullName(): string
    {
        if (!empty($this->full_name)) {
            return $this->full_name;
        }

        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }
}