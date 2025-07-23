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
        Schema::create('instrument_data', function (Blueprint $table) {
            $table->id();
            $table->date('RptDt');
            $table->string('TckrSymb');
            $table->string('MktNm');
            $table->string('SctyCtgyNm');
            $table->string('ISIN');
            $table->string('CrpnNm');

            $table->foreignId('upload_id')->constrained('uploads')->onDelete('cascade');

            $table->timestamps();

            $table->index(['TckrSymb', 'RptDt']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instrument_data');
    }
};
