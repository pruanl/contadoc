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
        if (Schema::hasColumn('documents', 'sender_phone')) {
            return;
        }

        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->change();
            $table->string('sender_phone')->nullable()->after('origin');
            $table->string('client_hint')->nullable()->after('sender_phone');
            $table->decimal('match_confidence', 5, 2)->nullable()->after('client_hint');

            $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
            $table->index('sender_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('documents', 'sender_phone')) {
            return;
        }

        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropIndex(['sender_phone']);
            $table->dropColumn(['sender_phone', 'client_hint', 'match_confidence']);
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable(false)->change();
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
        });
    }
};
