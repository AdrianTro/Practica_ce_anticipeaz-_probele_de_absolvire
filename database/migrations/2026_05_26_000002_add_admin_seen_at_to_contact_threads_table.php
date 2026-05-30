<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('contact_threads')) {
            return;
        }

        Schema::table('contact_threads', function (Blueprint $table): void {
            if (! Schema::hasColumn('contact_threads', 'admin_seen_at')) {
                $table->timestamp('admin_seen_at')->nullable()->after('last_message_at');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('contact_threads')) {
            return;
        }

        Schema::table('contact_threads', function (Blueprint $table): void {
            if (Schema::hasColumn('contact_threads', 'admin_seen_at')) {
                $table->dropColumn('admin_seen_at');
            }
        });
    }
};
