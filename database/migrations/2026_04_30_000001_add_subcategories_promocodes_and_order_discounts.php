<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('subcategories')) {
            Schema::create('subcategories', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('category_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('slug');
                $table->string('icon')->nullable();
                $table->text('description')->nullable();
                $table->json('features')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['category_id', 'slug']);
            });
        }

        if (Schema::hasTable('products') && ! Schema::hasColumn('products', 'subcategory_id')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->foreignId('subcategory_id')
                    ->nullable()
                    ->after('category_id')
                    ->constrained('subcategories')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasTable('promocodes')) {
            Schema::create('promocodes', function (Blueprint $table): void {
                $table->id();
                $table->string('code')->unique();
                $table->decimal('discount_percent', 5, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table): void {
                if (! Schema::hasColumn('orders', 'total_before_discount')) {
                    $table->decimal('total_before_discount', 10, 2)->default(0)->after('customer_email');
                }
                if (! Schema::hasColumn('orders', 'discount_amount')) {
                    $table->decimal('discount_amount', 10, 2)->default(0)->after('total_before_discount');
                }
                if (! Schema::hasColumn('orders', 'discount_percent')) {
                    $table->decimal('discount_percent', 5, 2)->default(0)->after('discount_amount');
                }
                if (! Schema::hasColumn('orders', 'promocode_code')) {
                    $table->string('promocode_code')->nullable()->after('discount_percent');
                }
            });

            if (Schema::hasColumn('orders', 'total_before_discount')) {
                DB::table('orders')
                    ->where(function ($query): void {
                        $query->whereNull('total_before_discount')->orWhere('total_before_discount', 0);
                    })
                    ->update(['total_before_discount' => DB::raw('total')]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table): void {
                foreach (['promocode_code', 'discount_percent', 'discount_amount', 'total_before_discount'] as $column) {
                    if (Schema::hasColumn('orders', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        Schema::dropIfExists('promocodes');

        if (Schema::hasTable('products') && Schema::hasColumn('products', 'subcategory_id')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('subcategory_id');
            });
        }

        Schema::dropIfExists('subcategories');
    }
};
