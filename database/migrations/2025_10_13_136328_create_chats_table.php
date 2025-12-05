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
        Schema::create('chats', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('client_id');
            $table->uuid('integration_id')->nullable();
            $table->uuid('assigned_user_id')->nullable();

            $table->string('external_id', 255)->nullable();
            $table->string('channel', 64);
            $table->string('status', 32)->default('open');

            $table->uuid('last_message_id')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->unsignedInteger('unread_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('integration_id')->references('id')->on('integrations')->onDelete('set null');
            $table->foreign('assigned_user_id')->references('id')->on('users')->onDelete('set null');

            $table->unique(['integration_id', 'external_id']);
            $table->index(['assigned_user_id', 'status']);
            $table->index(['status', 'last_message_at']);
        });

        // Индекс для keyset-пагинации по активности
        DB::statement(
            'CREATE INDEX IF NOT EXISTS chats_activity_at_id_idx ON chats ((COALESCE(last_message_at, updated_at)), id)'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Чистим индекс прежде чем удалить таблицу (на всякий случай)
        DB::statement('DROP INDEX IF EXISTS chats_activity_at_id_idx');
        Schema::dropIfExists('chats');
    }
};
