<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('key', 128);
            $table->string('name', 255);
            $table->string('entity_type', 64);
            $table->string('type', 32);
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->uuid('integration_id')->nullable();
            $table->timestamps();

            $table->unique(['entity_type', 'integration_id', 'key']);
            $table->index(['integration_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_fields');
    }
};
