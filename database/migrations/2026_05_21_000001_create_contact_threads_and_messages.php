<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('contact_threads')) {
            Schema::create('contact_threads', function (Blueprint $table): void {
                $table->id();
                $table->string('thread_uuid')->unique();
                $table->string('public_token', 80)->unique();
                $table->string('first_name', 120);
                $table->string('last_name', 120);
                $table->string('email', 160);
                $table->string('status')->default('open');
                $table->timestamp('last_message_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('contact_messages')) {
            Schema::create('contact_messages', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('contact_thread_id')->constrained('contact_threads')->cascadeOnDelete();
                $table->string('sender', 24);
                $table->text('body');
                $table->timestamps();

                $table->index(['contact_thread_id', 'created_at']);
                $table->index('sender');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('contact_threads');
    }
};
