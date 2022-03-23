<?php

namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;

class Telekom extends DataScan {

    public $vacancies;

    /**
     * Basf constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_telekom($website);
    }

    public function scan_telekom($website)
    {
                $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);


        $url = $website->url;
        $args =  array(
                        'url' => $url,
                        'method' => 'GET',
                        'header_type' => array(
                            'Accept: application/json',
                            'Content-Type: application/json'
                        ),
        );

        $response = $this->do_curl($args);
        $datas = json_decode($response,1);

        if( isset($datas['results']) && isset($datas['results']['jobs'])) {
            $jobs = $datas['results']['jobs'];
        } else {
            $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
            return;
        }
        foreach ( $jobs as $data ) {
            try {
                $job_url = 'https://www.telekom.com/'.$data['url'];
                $id = Vacancies::select('id')->where([ [ 'job_url', $job_url ], [ 'website_id', $website->id ] ])->value('id');
                if ( !empty($id) ) {
                    Vacancies::where('id', $id)->update(['updated_at' => date("Y-m-d H:i:s")]);
                    continue;
                }

                $this->vacancies = new Vacancies();
                $this->vacancies->website_id      = intval($website->id);
                $this->vacancies->location        = str_replace(', Germany', '', $data['locations'][0]);
                $this->vacancies->job_title       = $data['title'];
                $this->vacancies->job_url         = $job_url;
                $this->vacancies->opening_date    = date('Y-m-d', strtotime($data['date']));


                $args =  array(
                    'url' => $this->vacancies->job_url,
                    'method' => 'GET',
                    'header_type' => array(
                        'Accept: application/json',
                        'Content-Type: application/json'
                    ),
                );
                $html = $this->do_curl($args);
                /* Get job id from HTML string */
                preg_match('/<script type="application(.*?)+json">(.*?)<\/script>/s', $html, $matches);

                if(isset($matches[2])) {
                    if(!$this->isJson($matches[2])) {
                        continue;
                    }
                    $job_data = json_decode($matches[2], 1);
                    $this->vacancies->job_type = str_replace("_", " ", $job_data['employmentType'][0]);
                    $this->vacancies->job_description = isset($job_data['description']) ? $job_data['description'] : '';
                    $this->vacancies->deadline = isset($job_data['validThrough']) ? $job_data['validThrough'] : NULL;
                    $this->vacancies->city = $job_data['jobLocation'][0]['address']['addressLocality'];
                }
                /* Get job id from HTML string */
                preg_match('/<span class="jobad-stage__jobid__inner">(.*?)<\/span>/s', $html, $job_ids);
                $job_id = isset($job_ids[1]) ? str_replace('Job-ID: ', '', $job_ids[1]) : NULL;
                $this->vacancies->job_id          = $job_id;
                $this->vacancies->save();

            } catch (\Throwable $e) {
                $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' : ' . $e->getMessage(), 'error' => 1]);

                continue;
            }
        }
        Log::info($website->company.' Scan Ended');
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan ended']);
    }

    function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
