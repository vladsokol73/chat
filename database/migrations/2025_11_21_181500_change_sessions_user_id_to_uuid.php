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
        // Переcоздаём столбец как UUID, чтобы не тянуть doctrine/dbal и не ловить ошибки кастов
        if (Schema::hasColumn('sessions', 'user_id')) {
            Schema::table('sessions', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }

        Schema::table('sessions', function (Blueprint $table) {
            $table->uuid('user_id')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Возвращаем обратно bigint (как было через foreignId), если нужно откатить
        if (Schema::hasColumn('sessions', 'user_id')) {
            Schema::table('sessions', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }

        Schema::table('sessions', function (Blueprint $table) {
            // Для совместимости с разными драйверами БД используем bigInteger
            $table->unsignedBigInteger('user_id')->nullable()->index();
        });
    }
};
