<?php

namespace App\Http\Controllers;

use App\Exports\VacanciesExport;
use App\Models\Vacancies;
use App\Models\Websites;
use App\Models\Dashboard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public $ob;
    public $filters = [];
    public function __construct() {
        $this->filters = [
            'companies'         => ['status' => 1, 'title' => 'Companies', 'order' => 1],
            'regions'           => ['status' => 1, 'title' => 'Regions', 'order' => 2],
            'cities'            => ['status' => 1, 'title' => 'Cities', 'order' => 3],
            'categories'        => ['status' => 1, 'title' => 'Categories', 'order' => 4],
            'sub_categories'    => ['status' => 1, 'title' => 'Sub Companies', 'order' => 5],
            'job_type'          => ['status' => 1, 'title' => 'Type', 'order' => 6],
            'job_level'         => ['status' => 1, 'title' => 'Level', 'order' => 7],
            'start_date'        => ['status' => 1, 'title' => 'Start Date', 'order' => 8],
            'end_date'          => ['status' => 1, 'title' => 'End Date', 'order' => 9],
            'active_jobs'       => ['status' => 1, 'title' => 'Active jobs', 'order' => 10],
        ];
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        $columns = DB::getSchemaBuilder()->getColumnListing('vacancies');
        $settings = DB::table('settings')->where('id',1)->value('vacancies_columns');
        $default_filters = $this->filters;
        return view('settings.index', compact('columns', 'settings', 'default_filters'));
    }

    public function update(Request $request)
    {
        $columns = DB::getSchemaBuilder()->getColumnListing('vacancies');
        $settings = DB::table('settings')->where('id',1)->value('vacancies_columns');
        $settings = json_decode($settings, 1);
        $data = [];
        foreach ($columns as $column) {
            $title = $column.'_title';
            $order = $column.'_order';
            $data[$column]['status'] = empty($request->$column) ? 0 : 1;
            $data[$column]['title'] = empty($request->$title) ? $column : $request->$title;
            $data[$column]['order'] = empty($request->$order) ? 0 : $request->$order;
        }

        $settings['columns'] = $data;
        $json_data = json_encode($settings);
        DB::table('settings')->updateOrInsert(['id' => 1], ['vacancies_columns'=>$json_data]);

        return redirect()->route('settings.list')
            ->with('success', 'Website updated successfully');
    }

    public function update_filter(Request $request)
    {
        $settings = DB::table('settings')->where('id',1)->value('vacancies_columns');
        $settings = json_decode($settings, 1);
        $data = [];

        foreach ( $this->filters as $key => $filter ) {
            $filter = $key.'_filter';
            $title = $key.'_title';
            $order = $key.'_order';
            $data[$key]['status'] = empty($request->$filter) ? 0 : 1;
            $data[$key]['title'] = empty($request->$title) ? $title : $request->$title;
            $data[$key]['order'] = empty($request->$order) ? 0 : $request->$order;
        }
        $settings['filters'] = $data;
        $json_data = json_encode($settings);
        DB::table('settings')->updateOrInsert(['id' => 1], ['vacancies_columns'=>$json_data]);

        return redirect()->route('settings.list')
            ->with('success', 'Website updated successfully');
    }




}
