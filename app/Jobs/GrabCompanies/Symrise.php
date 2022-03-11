<?php

/**

 Max count which was possible retrieve from endpoint is 500 jobs ( total count is more then 4500)

 */
namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;

class Symrise extends DataScan {

    public $vacancies;

    /**
     * Basf constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_symrise($website);
    }

    public function scan_symrise($website)
    {
                $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);


        $url = $website->url;

        $args = array(
            'url' => $url,
            'method' => 'POST',
            'header_type' => array(
                'Accept: application/json',
                'Content-Type: application/json',
                'Username: Q7UFK026203F3VBQB68V7V70S:guest:FO',
                'Password: guest'
            ),
            'postfield' => '{"searchCriteria":{"criteria":[{"key":"LOV5","values":["9465"]},{"key":"Resultsperpage","values":["20"]}]}}'
        );
        $results = $this->do_curl($args);
        $results = json_decode($results, 1);
        if( empty($results['jobs']) ) {
            $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
            return;
        }
        foreach ( $results['jobs'] as $jobs ) {
            try {
                $job = $jobs['jobFields'];
                $info = $jobs['customFields'];
                $job_id = $job['id'];

                $id = Vacancies::select('id')->where([ [ 'job_id', $job_id ], [ 'website_id', $website->id ] ])->first();
                if( !empty($id) ) {
                    continue;
                }
                $this->vacancies = new Vacancies();
                $this->vacancies->website_id = intval($website->id);
                $this->vacancies->job_id = $job_id;
                $this->vacancies->location = $job['SLOVLIST6'];
                $this->vacancies->job_title = $job['jobTitle'];
                $this->vacancies->city = $job['SLOVLIST6'];
                $this->vacancies->job_level = $job['CONTRACTTYPLABEL'];
                $this->vacancies->job_category = $job['SLOVLIST7'];
                $this->vacancies->job_url = 'https://www.symrise.com/your-career/search-and-apply/search-and-apply/?jobId='.$job_id;
                $this->vacancies->opening_date = date('Y-m-d');
                $this->vacancies->job_description = isset($info[1]) ? $info[1]['content'] : '';
                $this->vacancies->qualification = isset($info[2]) ? $info[2]['content'] : '';
                $this->vacancies->about_us =isset($info[0]) ? $info[0]['content'] : '';

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
