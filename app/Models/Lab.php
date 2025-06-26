<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lab extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_lab',
        'deskripsi',
    ];

    /**
     * Mendefinisikan relasi bahwa satu Lab bisa memiliki banyak Usulan.
     */
    public function usulans(): HasMany
    {
        return $this->hasMany(Usulan::class);
    }
}
