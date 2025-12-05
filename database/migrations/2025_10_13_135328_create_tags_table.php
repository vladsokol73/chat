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
        Schema::create('tags', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('name');
            $table->string('color', 255)->nullable();
            $table->timestamps();

            $table->unique(['name']);
        });

        Schema::create('client_tag', function (Blueprint $table) {
            $table->uuid('client_id');
            $table->uuid('tag_id');

            // Составной первичный ключ
            $table->primary(['client_id', 'tag_id']);

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');

            $table->index(['tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_tag');
        Schema::dropIfExists('tags');
    }
};
