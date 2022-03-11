<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class DataScanController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');


    }

    public function scan() {
        Artisan::call( 'run_data_scan' );
    }

    /**
     * Clears cache
    **/
/*    public function start_scan()
    {
        $this->scan_adidas();
        return redirect()->back()->with('success', 'Cache and views cleared, config cached and queues restarted');
    }*/

}
