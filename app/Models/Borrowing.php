<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Borrowing extends Model
{
    protected $fillable = [
        'user_id',
        'item_id',
        'quantity',
        'borrow_date',
        'expected_return_date',
        'return_date',
        'status',
        'note',
        'description',
        'borrow_image',
        'return_image',
        'approval_note',
        'return_condition',
        'return_note',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
