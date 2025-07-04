<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// @TODO: Consider mediumInteger for price and sale_price? Need discussion with product team
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->index();
            $table->string('slug')->unique();
            $table->unsignedInteger('price')->index();
            $table->boolean('on_sale');
            $table->unsignedInteger('sale_price')->nullable();
            $table->string('color')->index();
            $table->string('upc', 20)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
