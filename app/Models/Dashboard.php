<?php

namespace App\Models;

use App\Http\Controllers\VacanciesController;
use App\Http\Controllers\WebsitesController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Dashboard extends Model
{
    use HasFactory;

    public function get_jobs_count() {
        $count = DB::select('SELECT count(id) as count FROM vacancies');
        return $count[0]->count;
    }

    public function get_jobs_count_by_company() {
        $query = "SELECT w.id, w.company, count(v.id) as jobCount from websites as w LEFT JOIN vacancies as v on w.id = v.website_id GROUP BY w.id, w.company";
        $result = DB::select($query);
        return $result;
    }


}
