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
    Schema::table('opd_sales', function (Blueprint $table) {
      // --- PERBAIKAN ---
      // Secara eksplisit mendefinisikan kolom sebagai unsignedInteger
      // untuk cocok dengan kolom id() dari versi Laravel yang lebih lama.
      $table->unsignedInteger('patient_id')->nullable()->after('doctor_id');

      // Membuat foreign key constraint secara terpisah
      $table->foreign('patient_id')
        ->references('id')
        ->on('patients')
        ->onDelete('set null');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('opd_sales', function (Blueprint $table) {
      // Hapus foreign key dan kolomnya
      $table->dropForeign(['patient_id']);
      $table->dropColumn('patient_id');
    });
  }
};
