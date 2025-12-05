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
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('chat_id');
            $table->uuid('user_id')->nullable();
            $table->string('external_message_id', 255)->nullable();

            $table->string('direction', 8); // in | out
            $table->string('type', 32)->default('text');
            $table->string('status', 32)->default('sent'); // queued|sent|delivered|read|failed

            $table->text('text')->nullable();
            $table->json('payload')->nullable();
            $table->uuid('reply_to_message_id')->nullable();

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();

            $table->string('error_code', 64)->nullable();
            $table->string('error_message', 512)->nullable();

            $table->timestamps();

            $table->foreign('chat_id')->references('id')->on('chats')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            $table->index(['chat_id', 'id']);
            $table->index(['direction', 'created_at']);
            $table->index(['status']);
            $table->index(['reply_to_message_id']);
            $table->unique(['chat_id', 'external_message_id'], 'messages_chat_external_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
