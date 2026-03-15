<?php

namespace App\Models;

use App\Caches\OrderCache;
use App\Casts\Model\AsFlagsJson;
use App\Casts\Model\AsFlagsSet;
use App\Enums\FlagEnum;
use App\Queries\OrderQuery;
use App\States\OrderState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\LaravelData\DataCollection;
use App\Data\ProductData;

/**
 * Summary of Order
 * @mixin OrderQuery
 * @method static OrderQuery query()
 * @property string $name
 * @property Collection<FlagEnum> $flags_set
 * @property DataCollection<ProductData> $products
 */
class Order extends Model
{
    use HasFactory,
        OrderCache,
        OrderState;
    protected $fillable = [
        'name',
        'flag',
        'flags_set',
        'flags_json',
        'products'
    ];

    protected $casts = [
        'flag' => FlagEnum::class,
        'flags_set' => AsFlagsSet::class,
        'flags_json' => AsFlagsJson::class,
        'products' => DataCollection::class . ':' . ProductData::class,
    ];

    public function newEloquentBuilder($query): OrderQuery
    {
        return new OrderQuery($query);
    }
}
