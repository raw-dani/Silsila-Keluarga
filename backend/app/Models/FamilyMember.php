<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FamilyMember extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'gender',
        'birth_date',
        'death_date',
        'father_id',
        'mother_id',
        'spouse_id',
        'photo',
        'avatar',
        'generation_level',
        'notes'
    ];

    protected $casts = [
        'birth_date' => 'date',
        'death_date' => 'date',
    ];

    // Relasi ke ayah
    public function father(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class, 'father_id');
    }

    // Relasi ke ibu
    public function mother(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class, 'mother_id');
    }

    // Relasi ke pasangan
    public function spouse(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class, 'spouse_id');
    }

    // Relasi ke anak-anak
    public function children()
    {
        return FamilyMember::where('father_id', $this->id)
                          ->orWhere('mother_id', $this->id)
                          ->get();
    }

    // Relasi ke saudara (siblings)
    public function siblings(): HasMany
    {
        return FamilyMember::where(function ($query) {
            $query->where('father_id', $this->father_id)
                  ->orWhere('mother_id', $this->mother_id);
        })->where('id', '!=', $this->id);
    }
}
