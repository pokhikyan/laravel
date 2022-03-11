<?php

namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;

class Brenntag extends DataScan {

    public $vacancies;

    /**
     * Adidas constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_brenntag($website);
    }


    public function scan_brenntag($website)
    {
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);
        $url = $website->url;

        for ( $i = 0; $i < 1000; $i+=20 ) {
            $args = array(
                'url' => $url.'&limit=20&offset='.$i,
                'method' => 'GET',
                'header_type' => array(
                    'Accept: application/json',
                    'x-requested-with: XMLHttpRequest'
                ),
            );
            $results = $this->do_curl($args);
            $results = json_decode($results, 1);
            if( empty($results) ) {
                $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
                break;
            }
            $datas = isset($results['items']) ? $results['items'] : array();
            foreach ( $datas as $data ) {
                try {
                    $id = Vacancies::select('id')->where([
                                                             [ 'job_id', $data['id'] ],
                                                             [ 'website_id', $website->id ]
                                                         ])->first();
                    if ( !empty($id)  || strpos($data['company']['name'], 'brenntag') === false ) {
                        continue;
                    }
                    $this->vacancies = new Vacancies();
                    $this->vacancies->website_id = intval($website->id);
                    $this->vacancies->job_id = $data['id'];
                    $this->vacancies->location = $data['location'];
                    $this->vacancies->job_title = $data['title'];
                    $this->vacancies->city = $data['location'];

                    $this->vacancies->job_url = 'https://www.xing.com/jobs/'.$data['slug'];
                    $this->vacancies->opening_date = date("Y-m-d", strtotime($data['activatedAt']));

                    $args = array(
                        'url' => $this->vacancies->job_url,
                        'method' => 'GET',
                    );
                    $html = $this->do_curl($args);

                    preg_match('/\<script data-rh="true" type\="application\/ld\+json">(.*?)<\/script>/s', $html, $json);
                    $data = json_decode($json[1],1);

                    $this->vacancies->job_type = $data['employmentType'];
                    $this->vacancies->job_category = $data['industry'];

                    $this->vacancies->job_description = $data['description'];

                    $this->vacancies->save();

                } catch (\Throwable $e) {
                    $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' : ' . $e->getMessage(), 'error' => 1]);

                    continue;
                }
            }
        }
        Log::info($website->company.' Scan Ended');
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan ended']);
    }

}
