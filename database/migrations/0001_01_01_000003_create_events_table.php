<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['tarea', 'recordatorio', 'fecha_importante']);
            $table->timestamp('start_at');
            $table->timestamp('end_at')->nullable();
            $table->string('color', 50)->nullable();
            $table->string('status', 30)->default('pendiente');
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurrence_frequency', ['diaria', 'semanal', 'mensual', 'anual'])->nullable();
            $table->foreignId('recurrence_parent_id')->nullable()->constrained('events')->nullOnDelete();
            $table->integer('reminder_minutes_before')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};