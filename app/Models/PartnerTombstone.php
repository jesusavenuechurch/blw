<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerTombstone extends Model
{
    public $timestamps = false;

    protected $fillable = ['partner_id', 'deleted_at'];

    protected $casts = ['deleted_at' => 'datetime'];
}
