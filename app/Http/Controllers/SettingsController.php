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
    public function __construct() {

    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        $columns = DB::getSchemaBuilder()->getColumnListing('vacancies');
        $settings = DB::table('settings')->where('id',1)->value('vacancies_columns');
        return view('settings.index', compact('columns', 'settings'));
    }

    public function update(Request $request)
    {
        $columns = DB::getSchemaBuilder()->getColumnListing('vacancies');
        $data = [];
        foreach ($columns as $column) {
            $title = $column.'_title';
            $order = $column.'_order';
            $data[$column]['status'] = empty($request->$column) ? 0 : 1;
            $data[$column]['title'] = empty($request->$title) ? $column : $request->$title;
            $data[$column]['order'] = empty($request->$order) ? 0 : $request->$order;
        }
        $json_data = json_encode($data);
        DB::table('settings')->updateOrInsert(['id' => 1], ['vacancies_columns'=>$json_data]);

        return redirect()->route('settings.list')
            ->with('success', 'Website updated successfully');
    }




}
