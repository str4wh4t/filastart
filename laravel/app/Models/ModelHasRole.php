<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role;

class ModelHasRole extends Model
{
    protected $table = 'model_has_roles';

    public $timestamps = false;

    protected $fillable = [
        'role_id',
        'model_type',
        'model_id',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function model()
    {
        return $this->morphTo(null, 'model_type', 'model_id');
    }
}
