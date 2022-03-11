<?php

/**

 Max count which was possible retrieve from endpoint is 500 jobs ( total count is more then 4500)

 */
namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;

class Zalando extends DataScan {

    public $vacancies;


    public $allowed_cities = array();
    /**
     * Basf constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->allowed_cities = array(
                            'Ansbach',
                            'Berlin',
                            'Dortmund',
                            'Düsseldorf',
                            'Erfurt',
                            'Frankfurt am Main',
                            'Germany',
                            'Hamburg',
                            'Hannover',
                            'Köln',
                            'Konstanz',
                            'Lahr/Schwarzwald',
                            'Leipzig',
                            'Ludwigsfelde',
                            'Mannheim',
                            'Mönchengladbach',
                            'Münster',
                            'Stuttgart',
                            'Ulm',
                        );
        $this->scan_zalando($website);
    }

    public function scan_zalando($website)
    {
                $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);


        $url = $website->url;

        $args = array(
            'url' => $url,
            'method' => 'GET',
        );
        $results = $this->do_curl($args);
        $results = json_decode($results, 1);

        if( isset($results['data']) ) {
            $jobs = $results['data'];
        } else {
            $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
            return;
        }
        foreach ( $jobs as $job ) {
            try {
                $job_id = $job['id'];

                $id = Vacancies::select('id')->where([ [ 'job_id', $job_id ], [ 'website_id', $website->id ] ])->first();
                if( !empty($id) ) {
                    continue;
                }
                $this->vacancies = new Vacancies();
                $this->vacancies->website_id = intval($website->id);
                $this->vacancies->job_id = $job_id;
                $this->vacancies->job_title = $job['title'];
                $this->vacancies->job_url = 'https://jobs.zalando.com/de/jobs/'.$job_id;
                $this->vacancies->opening_date = date('Y-m-d', strtotime($job['updated_at']));

                $args = array(
                    'url' => 'https://jobs-api.corptech.zalan.do/external/jobs/'.$job_id,
                    'method' => 'GET',
                );

                $job = $this->do_curl($args);

                $job = json_decode($job, 1);
                $city = isset($job['locations'][0]) ? $job['locations'][0] : '';
                if( $city === '' || !in_array( $city, $this->allowed_cities) ) {
                    continue;
                }
                $this->vacancies->location = $city;
                $this->vacancies->city = $city;
                $this->vacancies->job_type = str_replace("-", " ", $job['contract_types'][0]);
                $this->vacancies->job_category = $job['job_categories'][0];
                $this->vacancies->job_level = $job['entry_levels'][0];
                $this->vacancies->job_description = $job['content'];

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
