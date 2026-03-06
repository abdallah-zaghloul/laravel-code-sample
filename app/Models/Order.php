<?php

namespace App\Models;

use App\Casts\AsFlagsJson;
use App\Casts\AsFlagsSet;
use App\Enums\FlagEnum;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Summary of Drink
 * @property string $name
 * @property Collection<FlagEnum> $flags_set
 */
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'flag',
        'flags_set',
        'flags_json',
    ];

    protected $casts = [
        'flag' => FlagEnum::class,
        'flags_set' => AsFlagsSet::class,
        // 'flags_json' => AsEnumCollection::class . ':' . FlagsEnum::class,
        'flags_json' => AsFlagsJson::class,
    ];
}
