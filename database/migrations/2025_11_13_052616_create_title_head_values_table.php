<?php

use App\Models\TitleHead;
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
        Schema::create('title_head_values', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(TitleHead::class)->constrained()->onDelete('cascade');
            $table->string('financial_year');
            $table->enum('type', ['actual', 'budget-grant']);
            $table->string('month')->nullable();
            $table->string('amount');
            $table->timestamps();

            $table->unique(['title_head_id', 'financial_year', 'month'], 'th_t_fy_m');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('title_head_values');
    }
};
