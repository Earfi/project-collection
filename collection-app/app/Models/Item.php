<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'type',
        'scale',
        'maker',
        'subject_brand',
        'name',
        'description',
        'price',
        'qty',
        'collected_at',
        'image_path',
        'image_focus_x',
        'image_focus_y',
    ];
}
