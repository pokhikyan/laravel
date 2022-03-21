<?php

namespace App\Http\Controllers;

use App\Exports\VacanciesExport;
use App\Models\Vacancies;
use App\Models\Websites;
use App\Models\Dashboard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public $ob;
    public function __construct() {
        $this->ob = new Dashboard();
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        $data = $this->get_jobs_count();
        return view('dashboard.index',compact('data'));
    }

    public function get_jobs_count() {
        $data['count'] = $this->ob->get_jobs_count();
        $data['resp'] = $this->ob->get_jobs_count_by_company();
        return $data;
    }



}
