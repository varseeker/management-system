<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuImageFile extends Model
{
    protected $fillable = [
        'path',
        'mime_type',
        'contents',
    ];
}
