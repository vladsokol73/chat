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
        Schema::create('funnels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->string('api_key', 2048);
            $table->uuid('integration_id');
            $table->timestamps();

            $table->foreign('integration_id')
                ->references('id')
                ->on('integrations')
                ->nullOnDelete();
        });

        Schema::table('chats', function ($table) {
            $table->uuid('conversation_id')->nullable();
        });

        Schema::table('messages', function ($table) {
            $table->decimal('price', 3, 2)->default(0);
        });

        Schema::table('clients', function ($table) {
            $table->decimal('total_price', 15, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funnels');
        Schema::table('chats', function ($table) {
            $table->dropColumn('conversation_id');
        });

        Schema::table('messages', function ($table) {
            $table->dropColumn('price');
        });

        Schema::table('clients', function ($table) {
            $table->dropColumn('total_price');
        });
    }
};
