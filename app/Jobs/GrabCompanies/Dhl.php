<?php

/**

 Max count which was possible retrieve from endpoint is 500 jobs ( total count is more then 4500)

 */
namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;

class Dhl extends DataScan {

    public $vacancies;

    /**
     * Basf constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_dhl($website);
    }

    public function scan_dhl($website)
    {
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);

        $url = $website->url;

        $args =  array(
            'url' => $url,
            'method' => 'POST',
            'header_type' => array(
                'Accept: application/json',
                'Content-Type: application/json',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.45 Safari/537.36',
            ),
            'postfield' => '{"lang":"de_de","deviceType":"desktop","country":"de","pageName":"search-results","ddoKey":"eagerLoadRefineSearch","sortBy":"","subsearch":"","from":0,"jobs":true,"counts":true,"size":5000,"clearAll":false,"jdsource":"facets","isSliderEnable":true,"pageId":"page21","siteType":"external","keywords":"","global":true,"selected_fields":{},"locationData":{"place_id":"ChIJa76xwh5ymkcRW-WRjmtd6HU","sliderRadius":105,"aboveMaxRadius":true,"placeVal":"Germany"},"s":"1"}'
        );
        $results = $this->do_curl($args);
        $results = json_decode($results, 1);

        if( isset($results['eagerLoadRefineSearch']) && isset($results['eagerLoadRefineSearch']['data']) && isset($results['eagerLoadRefineSearch']['data']['jobs'])) {
            $jobs = $results['eagerLoadRefineSearch']['data']['jobs'];
        } else {
            $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
            return;
        }


        foreach ( $jobs as $job ) {
            try {
                $id = Vacancies::select('id')->where([ [ 'job_id', $job['jobId'] ], [ 'website_id', $website->id ] ])->first();
                if( !empty($id) ) {
                    continue;
                }
                $this->vacancies = new Vacancies();
                $this->vacancies->website_id = intval($website->id);
                $this->vacancies->job_id = $job['jobId'];
                $this->vacancies->location = isset($job['cityState']) ? $job['cityState'] : NULL;
                $this->vacancies->job_title = $job['title'];
                $this->vacancies->city = $job['cityState'];
                $this->vacancies->job_type = $job['type'];
                $this->vacancies->job_level = $job['careerLevel'];
                $this->vacancies->contract_type = $job['contractType1'];
                $this->vacancies->job_category = $job['category'];
                $this->vacancies->job_url = 'https://careers.dhl.com/de/de/job/'.$job['jobId'];
                $this->vacancies->opening_date = date('Y-m-d', strtotime($job['postedDate']));
                $this->vacancies->job_description = $job['descriptionTeaser'];

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
