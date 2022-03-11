<?php

/**

 Max count which was possible retrieve from endpoint is 500 jobs ( total count is more then 4500)

 */
namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use DateTime;
use Illuminate\Support\Facades\Log;

class Porsche extends DataScan {

    public $vacancies;

    /**
     * Bmw constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_porsche($website);
    }

    public function scan_porsche($website)
    {
                $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);


        $url = $website->url;

        $args =  array(
            'url' => $url,
            'method' => 'GET',
        );
        $results = $this->do_curl($args);
        $results = json_decode($results, 1);

        if( isset($results['SearchResult']) && isset($results['SearchResult']['SearchResultItems']) ) {
            $jobs = $results['SearchResult']['SearchResultItems'];
        } else {
            $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
            return;
        }

        foreach ( $jobs as $job ) {
            try {
                $data = $job['MatchedObjectDescriptor'];
                $jobid = $data['ID'];
                $id = Vacancies::select('id')->where([ [ 'job_id', $jobid ], [ 'website_id', $website->id ] ])->first();
                if( !empty($id) ) {
                    continue;
                }

                $city = isset($data['PositionLocation'][0]['CityName']) ? $data['PositionLocation'][0]['CityName'] : '';

                $job_url = $data['PositionURI'];
                $this->vacancies = new Vacancies();
                $this->vacancies->website_id = intval($website->id);
                $this->vacancies->job_id = $jobid;
                $this->vacancies->location = $city;
                $this->vacancies->job_title = $data['PositionTitle'];
                $this->vacancies->city = $city;
                $this->vacancies->job_url = $job_url;
                $this->vacancies->opening_date = date('Y-m-d', strtotime($data['PublicationStartDate']));
                $this->vacancies->job_category = $data['JobCategory'][0]['Name'];
                $this->vacancies->job_level = $data['CareerLevel'][0]['Name'];

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
