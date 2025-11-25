<?php

use App\Models\ReportTable;
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
        Schema::create('report_table_title_head', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ReportTable::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(TitleHead::class)->constrained()->onDelete('cascade');
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->unique(['report_table_id', 'title_head_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_table_title_head');
    }
};
