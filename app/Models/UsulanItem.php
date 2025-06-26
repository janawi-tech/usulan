<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsulanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'usulan_id',
        'nama_barang',
        'jumlah',
        'satuan',
        'spesifikasi',
        'perkiraan_harga',
    ];

    /**
     * Mendefinisikan relasi bahwa satu item dimiliki oleh satu Usulan (dokumen induk).
     */
    public function usulan(): BelongsTo
    {
        return $this->belongsTo(Usulan::class);
    }
}
