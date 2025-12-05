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
        // Обновляем комментарий для поля status, добавляя статус 'sending'
        DB::statement("COMMENT ON COLUMN messages.status IS 'sending|sent|delivered|read|failed'");

        // Изменяем default значение на 'sending' для новых записей
        Schema::table('messages', function (Blueprint $table) {
            $table->string('status', 32)->default('sending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Возвращаем обратно
        DB::statement("COMMENT ON COLUMN messages.status IS 'queued|sent|delivered|read|failed'");

        Schema::table('messages', function (Blueprint $table) {
            $table->string('status', 32)->default('sent')->change();
        });
    }
};
