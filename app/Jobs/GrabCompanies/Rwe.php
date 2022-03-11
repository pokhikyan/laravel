<?php

/**

 Max count which was possible retrieve from endpoint is 500 jobs ( total count is more then 4500)

 */
namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use DateTime;
use Illuminate\Support\Facades\Log;

class Rwe extends DataScan {

    public $vacancies;

    /**
     * Bmw constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_rwe($website);
    }

    public function scan_rwe($website)
    {

        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);

        $url = $website->url;

        $args =  array(
            'url' => $url,
            'method' => 'POST',
            'header_type' => array(
                'Accept: application/json',
                'Content-Type: application/json',
            ),
            'postfield' => '{"skip":0,"SortType":"Created_tdt desc","take":"1000","LogoContainerId":"0212b60d-b1c6-47b6-8cd4-f75dcaf7a55b","ExperienceLevel":[],"Company":[],"FunctionalArea":[],"Country":["DE"],"City":[],"Keyword":""}',
        );
        $results = $this->do_curl($args);

        $results = json_decode($results, 1);

        if( isset($results['Results'])) {
            $jobs = $results['Results'];
        } else {
            $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
            return;
        }

        foreach ( $jobs as $job ) {
            try {
                $jobid = $job['Id'];
                $id = Vacancies::select('id')->where([ [ 'job_id', $jobid ], [ 'website_id', $website->id ] ])->first();
                if( !empty($id) ) {
                    continue;
                }
                $this->vacancies = new Vacancies();
                $this->vacancies->website_id = intval($website->id);
                $this->vacancies->job_id = $jobid;
                $this->vacancies->location = $job['City'];
                $this->vacancies->job_title = $job['Title'];
                $this->vacancies->city = $job['City'];
                $this->vacancies->job_url = $job['Url'];
                $this->vacancies->opening_date = date('Y-m-d', strtotime($job['Created']));
                $this->vacancies->job_description = $job['TextDescription'];

                preg_match('/Functional area:(.*?)<\/span>/s', $job['Description'], $job_category);
                $job_category = isset($job_category[1]) ? strip_tags($job_category[1]) : '';
                $this->vacancies->job_category = trim($job_category);
                $this->vacancies->job_type = $job['Shift'];
                $this->vacancies->contract_type = $job['ShiftType'];

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
