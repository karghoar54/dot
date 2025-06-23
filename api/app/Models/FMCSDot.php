<?php
// app/Models/FMCSDot.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FMCSDot extends Model
{
    protected $connection = 'fmcsa';
    protected $table = 'FMCSADots';
    public $timestamps = false;
    protected $guarded = [];

    public function inspections()
    {
        return $this->hasMany(\App\Models\FMCSAInspection::class, 'DOT', 'DOT');
    }
}
