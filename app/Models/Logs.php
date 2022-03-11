<?php

namespace App\Models;

use App\Http\Controllers\VacanciesController;
use App\Http\Controllers\WebsitesController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Logs extends Model
{
    use HasFactory;
    protected $table = 'scan_logs';
    public $fillable = [
        'log',
        'website_id',
    ];

    public function logs()
    {
        return $this->belongsTo(WebsitesController::class);
    }

    public function get_logs() {
        $logs = DB::select('SELECT * FROM scan_logs');
        return $logs;
    }


}
