<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PageSettingModel extends Model
{
    use HasFactory;
    
    protected $table = 'page_settings';
    // protected $primaryKey = 'uuid';
    // public $incrementing = false; 
    protected $keyType = 'string';
    protected $fillable = ['page_url','presell_url','country_code', 'telephone', 'initial_message', 'telegram_link', 'messenger_link', 'instagram_link', 'cookies', 'tag_head', 'tag_body','page_id','uuid'];

    public function page(){
        return $this->belongsTo(PagesModel::class, 'page_id');
    }

    public function pixel(){
        return $this->hasMany(PagePixelSetupModel::class, 'setting_id');
    } 

    public function pageFit(){
        return $this->hasMany(PageFitModel::class, 'setting_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($setting) {
            $setting->pixel()->delete();
            $setting->pageFit()->delete();
        });

        // static::creating(function ($setting) {
        //     if (empty($setting->uuid)) {
        //         $setting->uuid = (string) Str::uuid();
        //     }
        // });

    }
}
