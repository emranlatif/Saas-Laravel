<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagePixelSetupModel extends Model
{
    use HasFactory;
    protected $table = 'pages_pixel_setup';
    protected $fillable = ['setting_id', 'pixel_id','type'];
}
