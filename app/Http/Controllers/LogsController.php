<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use App\Models\Websites;
use Illuminate\Http\Request;

class LogsController {

    public $date;
    public function index(Request $request) {
        $this->date = $request->has('date') ? strip_tags($request->get('date')) : '';
        $get_data = [ 'date' => $this->date ];

        $logCollection = Logs::where('id', '!=', 0)
            ->when($this->date != '', function( $query ) {
                $query->whereDate('created_at', '=', $this->date);
            })->latest()->paginate(50);

        $websites = Websites::all()->sortBy('company');


        return view('logs.index', compact('logCollection', 'websites', 'get_data'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    public function allLogs() {
        // from PHP documentations
        $logFile = file(storage_path().'/logs/laravel.log');
        $logCollection = [];
        // Loop through an array, show HTML source as HTML source; and line numbers too.
        foreach ($logFile as $key => $line) {
            $data = explode("]", $line);
            $created_at = str_replace('[', '', $data[0]);

            if( !isset($data[0]) || !isset($data[1])) {
                continue;
            }
            $logCollection[$key]['created_at'] = $created_at;
            $logCollection[$key]['log'] = trim($data[1]);
        }

        return view('logs.allLog', compact('logCollection'))
            ->with('i', (request()->input('page', 1) - 1) * 5);

    }

    public function destroy(Request $request)
    {

        $log_id = $request->has('log_id') ? strip_tags($request->get('log_id')) : 0;

        if( $log_id != 0 ) {
            $deleted = Logs::where('id', $log_id)->delete();
        }

        return redirect()->route('logs.list')
            ->withSuccess(__('Log delete successfully.'));
    }
}
