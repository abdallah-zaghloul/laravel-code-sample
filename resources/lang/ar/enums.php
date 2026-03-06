<?php

use App\Enums\FlagEnum;

return [
    FlagEnum::Amount->transKey() => "الكمية",
    FlagEnum::Tax->transKey() => "الضريبة",
    FlagEnum::Count->transKey() => "العدد",
];
