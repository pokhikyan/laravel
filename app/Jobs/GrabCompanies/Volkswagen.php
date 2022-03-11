<?php

/**

 Max count which was possible retrieve from endpoint is 500 jobs ( total count is more then 4500)

 */
namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;

class Volkswagen extends DataScan {

    public $vacancies;

    /**
     * Basf constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_volkswagen($website);
    }

    public function scan_volkswagen($website)
    {
                $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);


        $url = $website->url;

        $args = array(
            'url' => $url,
            'method' => 'GET',
            'header_type' => array(
                'Accept: application/json',
                'Content-Type: application/json',
            ),

        );
        $results = $this->do_curl($args);
        $results = json_decode($results, 1);

        if( isset($results['d']) && isset($results['d']['results']) ) {
            $jobs = $results['d']['results'];
        } else {
            $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
            return;
        }
        foreach ( $jobs as $job ) {
            try {
                $job_id = $job['JobID'];

                $id = Vacancies::select('id')->where([ [ 'job_id', $job_id ], [ 'website_id', $website->id ] ])->first();
                if( !empty($id) ) {
                    continue;
                }
                $this->vacancies = new Vacancies();
                $this->vacancies->website_id = intval($website->id);
                $this->vacancies->job_id = $job_id;
                $this->vacancies->location = $job['Location']['Text'];
                $this->vacancies->job_title = $job['Title'];
                $this->vacancies->city = $job['Location']['Text'];
                //$this->vacancies->job_type = str_replace('-', ' ', $job['meta_data']['job_type']);
                $this->vacancies->contract_type = $job['ContractType']['Text'];
                $this->vacancies->job_level = $job['HierarchyLevel']['Text'];
                $this->vacancies->job_category = $job['FunctionalArea']['Text'];
                $this->vacancies->job_url = 'https://karriere.volkswagen.de/sap/bc/bsp/sap/zvw_hcmx_ui_ext/?jobId='.$job_id;
                $this->vacancies->opening_date = date('Y-m-d', strtotime(date("Y-m-d"). ' - '.$job['PostingAge'].' days'));


                $url = "https://karriere.volkswagen.de/sap/opu/odata/sap/zaudi_ui_open_srv/JobSet('".$job_id."')?sap-client=100&sap-language=de&\$expand=Location,HierarchyLevel,ContractType,FunctionalArea,Company";
                $args = array(
                    'url' => $url,
                    'method' => 'GET',
                    'header_type' => array(
                        'Accept: application/json',
                        'Content-Type: application/json',
                    ),

                );
                $results = $this->do_curl($args);

                $results = json_decode($results, 1);
                $jobInfo = $results['d'];

                $this->vacancies->job_description = $jobInfo['TaskDesc'];
                $this->vacancies->qualification = $jobInfo['ProjectDesc'];
                $this->vacancies->about_us = $jobInfo['CompanyDesc'];

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
