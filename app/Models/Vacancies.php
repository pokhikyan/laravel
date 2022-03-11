<?php

namespace App\Models;

use App\Http\Controllers\VacanciesController;
use App\Http\Controllers\WebsitesController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Vacancies extends Model
{
    use HasFactory;
    protected $table = 'vacancies';
    public $fillable = [
        'job_id',
        'location',
        'job_title',
        'city',
        'job_type',
        'job_level',
        'job_category',
        'job_description',
        'job_url',
        'qualification',
        'opening_date',
        'deadline',
        'about_us',
    ];

    public function websites()
    {
        return $this->belongsTo(Websites::class, 'foreign_key');
    }

    public function get_websites() {
        $websites = DB::select('SELECT * FROM websites WHERE active = 1 ORDER BY company');
        return $websites;
    }


    //array('website_id' => $website_id, 'log' => $log)
    public function create_log( $insert_data = [] ) {
        $created_at = date("Y-m-d H:i:s");
        $insert_data['created_at'] = $created_at;
        DB::table('scan_logs')->insert($insert_data);
    }


}
