<?php
// app/Models/FMCSAInspection.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FMCSAInspection extends Model
{
    protected $connection = 'fmcsa';
    protected $table = 'FMCSAInspections';
    public $timestamps = false;
    protected $guarded = [];
}
