<?php

namespace app\Models;

use app\Core\Model;

class TravelExperienceImage extends Model
{
    protected string $table = 'travel_experience_images';

    protected array $fillable = [
        'uuid',
        'travel_experience_id',
        'image_path',
        'sort_order',
        'is_cover',
    ];

    protected array $casts = [
        'id' => 'int',
        'travel_experience_id' => 'int',
        'sort_order' => 'int',
        'is_cover' => 'bool',
    ];

    public static function byExperience(int $experienceId): array
    {
        $rows = static::query()
            ->where('travel_experience_id', '=', $experienceId)
            ->orderBy('is_cover', 'DESC')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->get();

        return array_map(fn($row) => new static($row), $rows ?: []);
    }

    public static function coverByExperience(int $experienceId): ?static
    {
        $row = static::query()
            ->where('travel_experience_id', '=', $experienceId)
            ->where('is_cover', '=', 1)
            ->first();

        return $row ? new static($row) : null;
    }
}