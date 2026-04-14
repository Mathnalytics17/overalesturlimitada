<?php

namespace app\Models;

use app\Core\Model;

class ExtraServiceContact extends Model
{
    protected string $table = 'extra_service_contacts';

    protected array $fillable = [
        'extra_service_id',
        'nombre',
        'email',
        'telefono',
        'mensaje',
        'estado',
    ];

    protected array $casts = [
        'id' => 'int',
        'extra_service_id' => 'int',
    ];
}