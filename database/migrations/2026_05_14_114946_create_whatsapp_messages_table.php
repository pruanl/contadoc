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
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('remote_phone');
            $table->string('remote_jid')->nullable();
            $table->string('direction')->default('incoming');
            $table->text('body')->nullable();
            $table->json('payload');
            $table->timestamp('message_at')->nullable();
            $table->timestamps();

            $table->index(['remote_phone', 'message_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
