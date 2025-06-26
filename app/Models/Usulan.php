<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Usulan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // KOLOM BARU
        'judul_usulan',
        'lampiran',

        // Kolom lama yang tetap ada
        'user_id',
        'lab_id',
        'tanggal_usulan',
        'status',
        'catatan_revisi',
        'diperiksa_oleh_id',
        'tanggal_pemeriksaan',
        'adum_user_id',
        'adum_approved_at',
        'catatan_adum',
        'pimpinan_user_id',
        'pimpinan_approved_at',
        'catatan_pimpinan',
        'ppk_user_id',
        'ppk_processed_at',
        'catatan_ppk',
        'ditunda_hingga',
        'bukti_serah_terima',
        'catatan_serah_terima',
        'serah_terima_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_usulan' => 'date',
        'tanggal_pemeriksaan' => 'datetime',
        'adum_approved_at' => 'datetime',
        'pimpinan_approved_at' => 'datetime',
        'ppk_processed_at' => 'datetime',
        'ditunda_hingga' => 'date',
        'serah_terima_at' => 'datetime',
    ];

    /**
     * RELASI BARU: Satu Usulan memiliki banyak UsulanItem.
     */
    public function items(): HasMany
    {
        return $this->hasMany(UsulanItem::class);
    }

    /**
     * Get the user that created the usulan.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the lab for this usulan.
     */
    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class, 'lab_id');
    }

    /**
     * Get the TAOP user who checked the usulan.
     */
    public function pemeriksa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diperiksa_oleh_id');
    }

    /**
     * Get the Adum user who approved the usulan.
     */
    public function adum(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adum_user_id');
    }

    /**
     * Get the Pimpinan user who approved the usulan.
     */
    public function pimpinan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pimpinan_user_id');
    }

    /**
     * Get the PPK user who processed the usulan.
     */
    public function ppk(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ppk_user_id');
    }
}
