<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageFitModel extends Model
{
    use HasFactory;
    protected $table = 'page_fit';
    protected $fillable = ['setting_id', 'page_element_code', 'new_element_code'];
}
