<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;
class DomainsModel extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'domains';
    protected $fillable = ['name', 'created_by', 'status','fb_verify_dns','fb_meta_meta','user_id','ownership_url','ownership_body'];

    public function pages(){
        return $this->hasMany(PagesModel::class, 'domain_id');
    } 

    public function user(){
        return $this->belongsTo(Users::class, 'user_id');
    }

    protected static function boot()
    {
        parent::boot();

        // Automatically generate UUID when creating a new domain
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Uuid::uuid4()->toString();
            }
        });
    }
}
