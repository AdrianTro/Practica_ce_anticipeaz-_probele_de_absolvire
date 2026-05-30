<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('subcategories') && ! Schema::hasColumn('subcategories', 'image')) {
            Schema::table('subcategories', function (Blueprint $table): void {
                $table->string('image')->nullable()->after('icon');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('subcategories') && Schema::hasColumn('subcategories', 'image')) {
            Schema::table('subcategories', function (Blueprint $table): void {
                $table->dropColumn('image');
            });
        }
    }
};
