<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\VacanciesExport;
use Maatwebsite\Excel\Facades\Excel;

class ExportToExcelController extends Controller
{
/*    public function ExportRecords()
    {
        return Excel::download(new VacanciesExport, 'vacancies.xlsx');
    }*/
}
