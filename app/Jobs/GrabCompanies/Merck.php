<?php

/**

 Max count which was possible retrieve from endpoint is 500 jobs ( total count is more then 4500)

 */
namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;
use DateTime;

class Merck extends DataScan {

    public $vacancies;

    /**
     * Basf constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_merck($website);
    }

    public function scan_merck($website)
    {
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);

        $url = $website->url;

        $args =  array(
            'url' => $url,
            'method' => 'GET',
        );
        $results = $this->do_curl($args);
        $results = json_decode($results, 1);
        if( isset($results['items']) ) {
            $jobs = $results['items'];
        } else {
            $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
            return;
        }


        foreach ( $jobs as $job ) {
            try {
                $id = Vacancies::select('id')->where([ [ 'job_id', $job['jobid'] ], [ 'website_id', $website->id ] ])->value('id');
                if ( !empty($id) ) {
                    Vacancies::where('id', $id)->update(['updated_at' => date("Y-m-d H:i:s")]);
                    continue;
                }
                $this->vacancies = new Vacancies();
                $this->vacancies->website_id = intval($website->id);
                $this->vacancies->job_id = $job['jobid'];
                $this->vacancies->location = $job['city'];
                $this->vacancies->job_title = $job['title'];
                $this->vacancies->city = $job['city'];
                $this->vacancies->job_type = $job['employmentType'];
                $this->vacancies->job_level = $job['careerLevel'];
                $this->vacancies->job_category = isset($job['functionalArea']) ? $job['functionalArea'] : '';
                $this->vacancies->qualification = isset($job['careerLevel']) ? $job['careerLevel'] : '';
                $this->vacancies->job_url = $job['link'];
                $this->vacancies->opening_date = date('Y-m-d', strtotime($job['datePosted']));
                $this->vacancies->job_description = $job['metaDescription'];

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
