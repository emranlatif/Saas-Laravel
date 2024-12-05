<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaticDnsModel extends Model
{
    use HasFactory;
    protected $table = 'static_domain_dns';
    protected $fillable = ['dns_server_one','dns_server_two', 'created_by'];
}
