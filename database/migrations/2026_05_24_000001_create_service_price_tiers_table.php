<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_price_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('service_key');      // e.g. 'toposyomka'
            $table->string('sub_service');      // e.g. 'toposyomka', 'qoziq', 'qr_kod', 'akt'
            $table->string('sub_service_label');// e.g. 'Toposyomka'
            $table->string('label');            // e.g. '1 kv.m dan 200 kv.m gacha'
            $table->decimal('price', 15, 2);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_price_tiers');
    }
};
