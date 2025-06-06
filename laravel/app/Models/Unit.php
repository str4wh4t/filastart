<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Unit extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'code',
        'parent_id',
    ];

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (Unit $unit) {
            // dd($unit);
            if (empty($unit->{$unit->getKeyName()})) {
                $unit->{$unit->getKeyName()} = (string) \Illuminate\Support\Str::uuid();
            }
        });

        static::updating(function (Unit $unit) {
            // dd($unit);
            // update children code if parent code changes
            if ($unit->isDirty('code')) {
                $oldCode = $unit->getOriginal('code');
                $children = Unit::where('parent_id', $unit->id)->get();
                foreach ($children as $child) {
                    $child->code = $unit->code . substr($child->code, strlen($oldCode));
                    $child->save();
                }
            }
        });

        static::deleting(function (Unit $unit) {
            //
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'parent_id');
    }
}
