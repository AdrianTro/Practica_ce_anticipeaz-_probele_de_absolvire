<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('categories')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table): void {
            if (! Schema::hasColumn('categories', 'carousel_image_ro')) {
                $table->string('carousel_image_ro')->nullable()->after('carousel_image');
            }
            if (! Schema::hasColumn('categories', 'carousel_image_ru')) {
                $table->string('carousel_image_ru')->nullable()->after('carousel_image_ro');
            }
            if (! Schema::hasColumn('categories', 'carousel_image_en')) {
                $table->string('carousel_image_en')->nullable()->after('carousel_image_ru');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('categories')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table): void {
            foreach (['carousel_image_en', 'carousel_image_ru', 'carousel_image_ro'] as $column) {
                if (Schema::hasColumn('categories', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
