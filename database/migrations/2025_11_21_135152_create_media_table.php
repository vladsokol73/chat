<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('message_id')
                ->constrained('messages')
                ->cascadeOnDelete();

            $table->string('type');               // MediaType enum (string)
            $table->string('external_id');        // file_id, media_id, guid
            $table->string('mime_type')->nullable();
            $table->integer('duration')->nullable();

            $table->string('path')->nullable();   // путь в S3
            $table->string('original_name')->nullable();

            $table->json('sizes')->nullable();
            $table->json('thumbnail')->nullable();

            $table->string('title')->nullable();
            $table->string('performer')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
