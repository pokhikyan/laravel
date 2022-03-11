<?php

/**

 Max count which was possible retrieve from endpoint is 500 jobs ( total count is more then 4500)

 */
namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;

class Mtu extends DataScan {

    public $vacancies;

    /**
     * Basf constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_mtu($website);
    }

    public function scan_mtu($website)
    {
                $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);


        $url = $website->url;

        $args =  array(
            'url' => $url,
            'method' => 'GET',
        );
        $results = $this->do_curl($args);
        $results = json_decode($results, 1);
        if( empty($results) ) {
            $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
            return;
        }
        foreach ( $results as $job ) {
            try {
                $id = Vacancies::select('id')->where([ [ 'job_id', $job['id'] ], [ 'website_id', $website->id ] ])->first();
                if( !empty($id) ) {
                    continue;
                }
                $this->vacancies = new Vacancies();
                $this->vacancies->website_id = intval($website->id);
                $this->vacancies->job_id = $job['id'];
                $this->vacancies->location = $job['jobOpening']['location'];
                $this->vacancies->job_title = $job['jobOpening']['name'];
                $this->vacancies->city = $job['jobOpening']['location'];
                if( isset($job['jobOpening']['workingTimes'][0]) ) {
                    $this->vacancies->job_type = $job['jobOpening']['workingTimes'][0]['name'];
                }

                if( isset($job['jobOpening']['contractPeriod']['name']) ) {
                    $this->vacancies->contract_type = $job['jobOpening']['contractPeriod']['name'];
                }


                $this->vacancies->job_category = $job['jobOpening']['categories'][0]['name'];
                $this->vacancies->job_url = $job['jobPublicationURL'];
                $this->vacancies->opening_date = date('Y-m-d', strtotime($job['jobOpening']['createdDate']));

                $args =  array(
                    'url' => $job['jobPublicationURL'],
                    'method' => 'GET',
                );
                $html = $this->do_curl($args);
                preg_match('/<div id="headerPublication" class="default-design-box">(.*?)<\/div>/s', $html, $matches);
                $this->vacancies->job_description = $matches[1];

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
