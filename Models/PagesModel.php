<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PagesModel extends Model
{
    use HasFactory,SoftDeletes;
    protected $table= 'pages';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string'; 
    protected $fillable = ['domain_id','name','sub_domain','redirect_url', 'is_root','created_by','user_id','uuid'];
    protected $casts=[
        'is_root' => 'boolean',
    ];

    public function setting(){
        return $this->hasOne(PageSettingModel::class, 'page_id');
    }    
    public function domain(){
        return $this->belongsTo(DomainsModel::class, 'domain_id');
    } 
    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($page) {
            if ($page->setting) {
                $page->setting->delete();
            }
        });

        static::creating(function ($page) {
            if (empty($page->uuid)) {
                $page->uuid = (string) Str::uuid();
            }
        });
    }



}
