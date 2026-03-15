<?php

use App\Enums\FlagEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('flag')->nullable();
            $table->set('flags_set', FlagEnum::values())->nullable();
            $table->json('flags_json')->nullable();
            $table->json('products')->nullable();
            $table->timestamps();
        });

        FlagEnum::enumConstraint('orders', 'flag');
        FlagEnum::jsonConstraint('orders', 'flags_json');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        FlagEnum::dropConstraint('orders', 'flag');
        FlagEnum::dropConstraint('orders', 'flags_json');
        Schema::drop('orders');
    }
};
