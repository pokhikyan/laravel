<?php

namespace App\Http\Controllers;

use App\Models\Vacancies;
use App\Models\Websites;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdidasController extends Controller
{
    public function __construct() {
        $this->scan_adidas();
    }

    public function scan_adidas()
    {
        Log::info($this->website->company.' Scan Started');
        $url = $this->website->url;
        $xml = $this->do_curl($url);
        $xml = $this->xmlToArray($xml);
        $datas = $xml['job'];
        foreach ( $datas as $data ) {
            if( $data['country'] != "Germany" ) {
                continue;
            }

            $vacancies = new Vacancies();
            $id = Vacancies::select('id')->where('job_id', intval($data['referencenumber']))->first();
            if( !empty($id) ) {
                continue;
            }
            $vacancies->website_id      = intval($this->website->id);
            $vacancies->job_id          = $data['referencenumber'];
            $vacancies->location        = $data['city'];
            $vacancies->job_title       = $data['title'];
            $vacancies->city            = $data['city'];
            $vacancies->job_type        = $data['jobtype'];
            $vacancies->job_category    = $data['category'];
            $vacancies->job_description = $data['description'];
            $vacancies->job_url         = $data['url'];
            $vacancies->opening_date    = $data['date'];
            $vacancies->save();
        }
        Log::info($this->website->company.' Scan Ended');
    }

}
