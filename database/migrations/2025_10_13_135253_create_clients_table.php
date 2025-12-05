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
        Schema::create('clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->bigInteger('external_id')->unsigned()->index();
            $table->string('name', 255);
            $table->string('phone', 255);
            $table->string('avatar', 255);
            $table->string('comment', 512)->nullable();
            $table->uuid('integration_id');
            $table->timestamps();

            $table->foreign('integration_id')->references('id')->on('integrations')->onDelete('cascade');

            $table->unique(['integration_id', 'external_id']);
            $table->index(['phone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
