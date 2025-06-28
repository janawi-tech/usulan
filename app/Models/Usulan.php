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
        'judul_usulan',
        'lampiran',              // DIPERBAIKI: hanya nama field, bukan casting
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
        'bukti_serah_terima',    // DIPERBAIKI: hanya nama field, bukan casting
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

        // --- DIPERBAIKI: Kedua field JSON harus ada di sini ---
        'lampiran' => 'array',
        'bukti_serah_terima' => 'array',
    ];

    /**
     * Custom accessor untuk bukti_serah_terima
     * Handle jika data tersimpan sebagai string single file
     */
    public function getBuktiSerahTerimaAttribute($value)
    {
        if (empty($value)) {
            return [];
        }

        // Jika sudah array (dari casting), return as is
        if (is_array($value)) {
            return $value;
        }

        // Jika string, coba decode JSON dulu
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if ($decoded && is_array($decoded)) {
                return $decoded;
            }

            // Jika bukan JSON, treat sebagai single file
            return [$value];
        }

        return [];
    }

    /**
     * Relasi ke rincian barang.
     */
    public function items(): HasMany
    {
        return $this->hasMany(UsulanItem::class);
    }

    /**
     * Relasi ke pengguna yang membuat.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke lab.
     */
    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class, 'lab_id');
    }

    /**
     * Relasi ke pemeriksa TAOP.
     */
    public function pemeriksa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diperiksa_oleh_id');
    }

    /**
     * Relasi ke pemeriksa Adum.
     */
    public function adum(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adum_user_id');
    }

    /**
     * Relasi ke pemeriksa Pimpinan.
     */
    public function pimpinan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pimpinan_user_id');
    }

    /**
     * Relasi ke pemroses PPK.
     */
    public function ppk(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ppk_user_id');
    }
}
