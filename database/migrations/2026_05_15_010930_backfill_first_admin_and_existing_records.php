<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $adminExists = DB::table('users')->where('role', 'admin')->exists();
        $firstUser = DB::table('users')->orderBy('id')->first();

        if (! $adminExists && $firstUser) {
            DB::table('users')
                ->where('id', $firstUser->id)
                ->update([
                    'role' => 'admin',
                    'plan' => 'business',
                ]);
        }

        $ownerId = DB::table('users')->where('role', 'admin')->orderBy('id')->value('id');

        if (! $ownerId) {
            return;
        }

        foreach (['clients', 'documents', 'whatsapp_messages'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'user_id')) {
                DB::table($table)->whereNull('user_id')->update(['user_id' => $ownerId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
