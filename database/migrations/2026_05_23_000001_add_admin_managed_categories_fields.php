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
            if (! Schema::hasColumn('categories', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('description');
            }
            if (! Schema::hasColumn('categories', 'show_in_carousel')) {
                $table->boolean('show_in_carousel')->default(false)->after('is_active');
            }
            if (! Schema::hasColumn('categories', 'carousel_image')) {
                $table->string('carousel_image')->nullable()->after('show_in_carousel');
            }
            if (! Schema::hasColumn('categories', 'carousel_title')) {
                $table->string('carousel_title')->nullable()->after('carousel_image');
            }
            if (! Schema::hasColumn('categories', 'carousel_label')) {
                $table->string('carousel_label')->nullable()->after('carousel_title');
            }
            if (! Schema::hasColumn('categories', 'carousel_text')) {
                $table->text('carousel_text')->nullable()->after('carousel_label');
            }
            if (! Schema::hasColumn('categories', 'carousel_text_position')) {
                $table->string('carousel_text_position', 40)->default('bottom-left')->after('carousel_text');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('categories')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table): void {
            foreach ([
                'carousel_text_position',
                'carousel_text',
                'carousel_label',
                'carousel_title',
                'carousel_image',
                'show_in_carousel',
                'is_active',
            ] as $column) {
                if (Schema::hasColumn('categories', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
