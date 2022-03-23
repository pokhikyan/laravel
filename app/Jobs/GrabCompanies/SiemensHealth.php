<?php

/**

 Max count which was possible retrieve from endpoint is 500 jobs ( total count is more then 4500)

 */
namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;

class SiemensHealth extends DataScan {

    public $vacancies;

    /**
     * Basf constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_siemensHealth($website);
    }

    public function scan_siemensHealth($website)
    {
                $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);


        $url = $website->url;

        for( $i = 1; $i < 40; $i++ ) {

            $args = array(
                'url' => $url.'&page='.$i,
                'method' => 'GET',
            );
            $results = $this->do_curl($args);
            $results = json_decode($results, 1);

            if( empty($results['jobs']) && $i == 1 ) {
                $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
                break;
            } elseif (empty($results['jobs'])) {
                break;
            }

            foreach ( $results['jobs'] as $jobs ) {
                try {
                    $job = $jobs['data'];
                    if( $job['country'] != 'Germany' ) {
                        continue;
                    }
                    $job_id = $job['meta_data']['ats_id'];

                    $id = Vacancies::select('id')->where([ [ 'job_id', $job_id ], [ 'website_id', $website->id ] ])->value('id');
                    if ( !empty($id) ) {
                        Vacancies::where('id', $id)->update(['updated_at' => date("Y-m-d H:i:s")]);
                        continue;
                    }
                    $this->vacancies = new Vacancies();
                    $this->vacancies->website_id = intval($website->id);
                    $this->vacancies->job_id = $job_id;
                    $this->vacancies->location = $job['city'];
                    $this->vacancies->job_title = $job['title'];
                    $this->vacancies->city = $job['city'];
                    $this->vacancies->job_type = str_replace('-', ' ', $job['meta_data']['job_type']);
                    $this->vacancies->job_category = $job['category'][0];
                    $this->vacancies->job_level = $job['experience_levels'][0];
                    $this->vacancies->job_url = $job['meta_data']['canonical_url'];
                    $this->vacancies->opening_date = date('Y-m-d', strtotime($job['posted_date']));
                    $this->vacancies->job_description = $job['description'];

                    $this->vacancies->save();
                } catch (\Throwable $e) {
                    $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' : ' . $e->getMessage(), 'error' => 1]);

                    continue;
                }
            }

        }

        Log::info($website->company.' Scan Ended');
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan ended']);
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan ended']);
    }
}
