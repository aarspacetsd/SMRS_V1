<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpdSales extends Model
{
  // Anda mungkin perlu menambahkan 'patient_id' ke fillable jika belum ada
  protected $fillable = [
    'doctor_id',
    'patient_id', // <-- Pastikan ini ada
    'opd_name',
    'invoice_id',
    'doctor_fee',
    'opd_charge',
    'status'
  ];

  /**
   * Mendefinisikan relasi ke Doctor.
   */
  public function doctor()
  {
    return $this->belongsTo('App\Models\Doctor');
  }

  /**
   * Mendefinisikan relasi ke Invoice.
   */
  public function invoice()
  {
    return $this->belongsTo('App\Models\Invoice');
  }

  /**
   * --- TAMBAHKAN METHOD INI ---
   * Mendefinisikan relasi ke Patient.
   */
  public function patient()
  {
    // Asumsi: tabel 'opd_sales' memiliki foreign key 'patient_id'
    return $this->belongsTo('App\Models\Patient', 'patient_id');
  }
}
