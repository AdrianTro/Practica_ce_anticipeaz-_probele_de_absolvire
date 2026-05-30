<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sessions') || Schema::hasColumn('sessions', 'user_id')) {
            return;
        }

        Schema::table('sessions', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id')->nullable()->index()->after('id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('sessions') || ! Schema::hasColumn('sessions', 'user_id')) {
            return;
        }

        Schema::table('sessions', function (Blueprint $table): void {
            $table->dropColumn('user_id');
        });
    }
};
