<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Безопасно удаляем старый уникальный констрейнт, если он есть (PostgreSQL)
        DB::statement('ALTER TABLE messages DROP CONSTRAINT IF EXISTS messages_external_message_id_unique');

        // Создаём составной уникальный индекс, если его ещё нет (PostgreSQL)
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS messages_chat_external_unique ON messages (chat_id, external_message_id)');
    }

    public function down(): void
    {
        // Откатываем составной уникальный индекс (если есть)
        DB::statement('DROP INDEX IF EXISTS messages_chat_external_unique');
        // Возвращаем прежний уникальный констрейнт на external_message_id (если его нет)
        DB::statement('ALTER TABLE messages ADD CONSTRAINT messages_external_message_id_unique UNIQUE (external_message_id)');
    }
};
