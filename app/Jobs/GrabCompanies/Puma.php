<?php

/**

 Max count which was possible retrieve from endpoint is 500 jobs ( total count is more then 4500)

 */
namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use DateTime;
use Illuminate\Support\Facades\Log;

class Puma extends DataScan {

    public $vacancies;

    /**
     * Bmw constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_puma($website);
    }

    public function scan_puma($website)
    {
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);
        $url = $website->url;

        $args =  array(
            'url' => $url,
            'method' => 'GET',
        );
        $results = $this->do_curl($args);
        $results = json_decode($results, 1);

        if( isset($results['teaser']) ) {
            $jobs = $results['teaser'];
        } else {
            $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
            return;
        }

        foreach ( $jobs as $job ) {
            try {
                $jobid = $job['jobitemid'];
                $id = Vacancies::select('id')->where([ [ 'job_id', $jobid ], [ 'website_id', $website->id ] ])->value('id');
                if ( !empty($id) ) {
                    Vacancies::where('id', $id)->update(['updated_at' => date("Y-m-d H:i:s")]);
                    continue;
                }

                $location = explode(',', $job['location']);
                $city = $location[0];
                $job_url = 'https://about.puma.com'.$job['url'];
                $this->vacancies = new Vacancies();
                $this->vacancies->website_id = intval($website->id);
                $this->vacancies->job_id = $jobid;
                $this->vacancies->location = $city;
                $this->vacancies->job_title = $job['title'];
                $this->vacancies->city = $city;
                $this->vacancies->job_url = $job_url;
                $this->vacancies->job_category = $job['team'];

                $args =  array(
                    'url' => $job_url,
                    'method' => 'GET',
                );
                $html = $this->do_curl($args);
                preg_match_all('/<script type="application(.*?)+json">(.*?)<\/script>/s', $html, $matches);
                $json = $matches[2][2];

                $data = json_decode($json,1);

                $this->vacancies->opening_date = date('Y-m-d', strtotime($data['datePosted']));
                $this->vacancies->job_description = $data['description'];
                $this->vacancies->job_type = str_replace('_', ' ', $data['employmentType']);
                $this->vacancies->qualification = $data['qualifications'];

                $this->vacancies->save();

            } catch (\Throwable $e) {
                $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' : ' . $e->getMessage(), 'error' => 1]);

                continue;
            }
        }
        Log::info($website->company.' Scan Ended');
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan ended']);
    }
}
