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
        Schema::create('content_logs', function (Blueprint $table) {
            $table->id();
            $table->date('post_date');
            $table->string('platform', 50)->default('Instagram');
            $table->string('content_type', 50);
            $table->string('title');
            $table->string('pillar', 100);
            $table->string('pic_name', 100)->nullable();
            $table->text('evidence_url')->nullable();
            $table->string('evidence_status', 20)->default('Belum');
            $table->date('h30_due_date')->nullable();
            $table->string('data_status', 30)->default('Menunggu H+30');
            $table->unsignedInteger('views_h30')->default(0);
            $table->unsignedInteger('viewers_h30')->default(0);
            $table->unsignedInteger('likes')->default(0);
            $table->unsignedInteger('comments')->default(0);
            $table->unsignedInteger('shares_reposts')->default(0);
            $table->unsignedInteger('saves')->default(0);
            $table->integer('follows')->default(0);
            $table->unsignedInteger('total_interactions')->default(0);
            $table->decimal('engagement_rate', 8, 4)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('post_date');
            $table->index('pillar');
            $table->index('content_type');
            $table->index('data_status');
            $table->index(['post_date', 'data_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_logs');
    }
};
