<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpdateRequest extends Model
{
    protected $fillable = [
        'member_id',
        'target_member_id',
        'change_type',
        'old_data',
        'new_data',
        'status',
        'admin_note'
    ];

    // Relasi ke user yang mengajukan
    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    // Relasi ke anggota keluarga yang akan diubah
    public function targetMember(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class, 'target_member_id');
    }
}
