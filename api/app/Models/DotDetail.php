<?php
// app/Models/DotDetail.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DotDetail extends Model
{
    protected $connection = 'fmcsa';
    protected $table = 'DOTsDetail';
    public $timestamps = false;
    protected $guarded = [];
}
