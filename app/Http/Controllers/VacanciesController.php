<?php

namespace App\Http\Controllers;

use App\Exports\VacanciesExport;
use App\Models\Vacancies;
use App\Models\Websites;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class VacanciesController extends Controller
{
    public $company;
    public $job_type;
    public $job_level;
    public $city;
    public $region;
    public $region_cities;
    public $job_category;
    public $start_date;
    public $end_date;
    public $search;
    public $export_type;

    public function __construct(Request $request) {
        $this->company = $request->has('company') ? strip_tags($request->get('company')) : 0;
        $this->job_type = $request->has('job_type') ? strip_tags($request->get('job_type')) : 0;
        $this->job_level = $request->has('job_level') ? strip_tags($request->get('job_level')) : 0;
        $this->city = $request->has('city') ? strip_tags($request->get('city')) : 0;
        $this->region = $request->has('region') ? intval($request->get('region')) : 0;
        $this->job_category = $request->has('job_category') ? strip_tags($request->get('job_category')) : 0;
        $this->start_date = $request->has('start_date') ? strip_tags($request->get('start_date')) : '';
        $this->end_date = $request->has('end_date') ? strip_tags($request->get('end_date')) : '';
        $this->search = $request->has('search') ? strip_tags($request->get('search')) : '';
        $this->export_type = $request->has('export_type') ? strip_tags($request->get('export_type')) : 'xlsx';
    }

    public function filter_query( $pagecount = 50 ) {

        if( $this->region != '' && $this->region != 0 ) {
            $cities = DB::table('cities')->select('name')->where('region_id', '=', $this->region)->get()->all();
            foreach ($cities as $city) {
                $this->region_cities[] =  $city->name;
            }
        }

        $vacancies = Vacancies::join('websites', 'websites.id', '=', 'vacancies.website_id')
            ->select(['vacancies.id', 'websites.company','vacancies.job_id','vacancies.city','vacancies.job_title','vacancies.job_type','vacancies.job_level','vacancies.contract_type','vacancies.job_category','vacancies.job_description','vacancies.job_url','vacancies.opening_date','vacancies.created_at'])
            ->where('vacancies.id', '!=', 0)
            ->when($this->company != 0, function( $query ) {
            $query->where('vacancies.website_id', '=', $this->company);
        })->when($this->job_type != 0, function( $query ) {
            $query->where(function( $query ) {
                switch ( $this->job_type ) {
                    case  1:
                        $query->where(function($query) {
                            $query->orwhere('vacancies.job_type', '=', 'Full time')->orwhere('vacancies.job_type', '=', 'Vollzeit');
                        });
                        break;
                    case  2:
                        $query->where(function($query) {
                            $query->orwhere('vacancies.job_type', '=', 'Permanent')->orwhere('vacancies.job_type', '=', 'Unbefristet')->orwhere('vacancies.job_type', '=', 'Voor bepaalde tijd');
                        });
                        break;
                    case  3:
                        $query->where(function($query) {
                            $query->orwhere('vacancies.job_type', '=', 'Temporary')->orwhere('vacancies.job_type', '=', 'Befristet')->orwhere('vacancies.job_type', '=', 'Temp')->orwhere('vacancies.job_type', '=', 'Limited Duration')->orwhere('vacancies.job_type', '=', 'De duración determinada');
                        });
                        break;
                    case  4:
                        $query->where('vacancies.job_type', '=', 'Internship');
                        break;
                    case  5:
                        $query->where('vacancies.job_type', '=', 'Praktikum');
                        break;
                    case  6:
                        $query->where('vacancies.job_type', '=', 'Part time');
                        break;
                }
            });
        })->when($this->job_level != 0, function( $query ) {
            $query->where(function( $query ) {
                switch ( $this->job_level ) {
                    case  1:
                        $query->where(function($query) {
                            $query->orwhere('vacancies.job_level', 'like', '%Professional%')->orwhere('vacancies.job_level', 'like', '%Berufserfahrene%');
                        });
                        break;
                    case  2:
                        $query->where(function($query) {
                            $query->orwhere('vacancies.job_level', 'like', '%Mid-level%')->orwhere('vacancies.job_level', 'like', '%mid%');
                        });
                        break;
                    case  3:
                        $query->where(function($query) {
                            $query->orwhere('vacancies.job_level', 'like', '%Intern%')->orwhere('vacancies.job_level', 'like', '%Apprentice%')->orwhere('vacancies.job_level', 'like', '%Student%');
                        });
                        break;
                    case  4:
                        $query->where(function($query) {
                            $query->orwhere('vacancies.job_level', 'like', '%Praktikum%')->orwhere('vacancies.job_level', 'like', '%Praktikanten%');
                        });
                        break;
                }
            });
        })->when($this->job_category != 0, function( $query ) {
            $query->where('vacancies.job_category', '=', $this->job_category);
        })->when($this->region != 0, function( $query ) {
            //$query->where('city', 'like', $this->region_cities[0]);
            $query->where(function($query) {
                for ( $i = 0; $i < count($this->region_cities); $i++ ) {
                    $query->orwhere('vacancies.city', 'like', $this->region_cities[$i]);
                }
            });
        })->when($this->city != "0", function( $query ) {
            $query->where('vacancies.city', 'like', '%'.$this->city.'%');
        })->when($this->start_date != '', function( $query ) {
            $query->where('vacancies.opening_date', '>=', date("Y-m-d", strtotime($this->start_date)));
        })->when($this->end_date != '', function( $query ) {
            $query->where('vacancies.opening_date', '<', date("Y-m-d", strtotime($this->end_date)));
        })->latest('vacancies.created_at')->paginate($pagecount);

        return $vacancies;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $get_data = [ 'company' => $this->company, 'job_type' => $this->job_type, 'job_level' => $this->job_level, 'start_date' => $this->start_date, 'end_date' => $this->end_date ];


        $vacancies = $this->filter_query();
        //})->toSql();
       // dd($vacancies);
        $websites = Websites::all()->where('active','=',1)->sortBy('company');

        $categories = Vacancies::select('job_category')->where('job_category','<>','')->groupBy('job_category')->get();

        if( empty($this->region_cities) ) {
            //$cities = Vacancies::select('city')->where('city','<>','')->groupBy('city')->get();
            $cities = DB::table('cities')->select('name')->get()->all();

            foreach ( $cities as $city ) {
                $aa[] = $city->name;
            }
            $cities =  $aa;
        } else {
            $cities =  $this->region_cities;
        }

        $regions = DB::select( DB::raw("SELECT * FROM regions") );
        foreach ($categories as $key=>$category) {
            if( empty($category->job_category) ) {
                unset($categories[$key]);
            }
        }

        return view('vacancies.index', compact('vacancies','websites', 'get_data', 'categories', 'cities', 'regions'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Vacancies  $vacancies
     * @return \Illuminate\Http\Response
     */
    public function show(Vacancies $vacancies)
    {
        return view('vacancies.show', compact('vacancies'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Vacancies  $vacancies
     * @return \Illuminate\Http\Response
     */
    public function edit(Vacancies $vacancies)
    {
        return view('vacancies.edit', compact('vacancies'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Websites  $websites
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Vacancies $vacancies)
    {
        $request->validate([
                               'job_title' => 'required',
                               'job_category' => 'required',
                               'job_description' => 'required',
                               'job_url' => 'required'
                           ]);

        $vacancies->update($request->all());

        return redirect()->route('vacancies.index')
            ->with('success', 'Vacancies updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Vacancies  $vacancies
     * @return \Illuminate\Http\Response
     */
    public function destroy(Vacancies $vacancies)
    {
        $vacancies->delete();

        return redirect()->route('vacancies.index')
            ->with('success', 'Vacancies deleted successfully');
    }

    public function export(Request $request)
    {

        $vacancies = $this->filter_query(5000);
        $headings = ['id','company','job_id','city','job_title','job_type','contract_type','job_category','job_level','job_description','job_url','opening_date','created_at'];

        ini_set('memory_limit', '512M');

        if( $this->export_type == 'xlsx') {
            return Excel::download(new VacanciesExport($vacancies, $headings), 'data.xlsx');
        } else {
            return Excel::download(new VacanciesExport($vacancies, $headings), 'data.csv');
        }
    }


}
