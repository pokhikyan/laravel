<?php

namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;

class Qiagen extends DataScan {

    public $vacancies;

    /**
     * Adidas constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_qiagen($website);
    }

    public function scan_qiagen($website)
    {
        $url = $website->url;
        $args =  array(
            'url' => $url,
            'method' => 'POST',
            'header_type' => array(
                'Accept: application/json',
                'Content-Type: application/json',
                'username: PJBFK026203F3VBQB6879LOOB:guest:FO',
                'password: guest',
            ),
            'postfield' => '{"searchCriteria":{"criteria":[{"key":"LOV26","values":["16409"]},{"key":"Resultsperpage","values":["50"]}]}}'
        );

        $json = $this->do_curl($args);
        $results = json_decode($json, 1);

        if( !empty($results['jobs']) ) {
            $jobs = $results['jobs'];
        } else {
            $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
            return;
        }


        foreach ( $jobs as $jobb ) {
            try {
                $job = $jobb['jobFields'];
                $job = array_merge($job, $jobb['customFields']);
                $id = Vacancies::select('id')->where([ [ 'job_id', $job['id'] ], [ 'website_id', $website->id ] ])->first();
                if( !empty($id) ) {
                    continue;
                }
                $this->vacancies = new Vacancies();
                $this->vacancies->website_id = intval($website->id);
                $this->vacancies->job_id = $job['id'];
                $this->vacancies->location = $job['SLOVLIST27'];
                $this->vacancies->job_title = $job['jobTitle'];
                $this->vacancies->city = $job['SLOVLIST27'];
                $this->vacancies->job_url = 'https://www.qiagen.com/us/about-us/careers/jobs/details?jobId='.$job['id'];
                $this->vacancies->job_description = $job[1]['content'];
                $this->vacancies->qualification = $job[2]['content'];
                $this->vacancies->about_us = $job[0]['content'];


                // $this->vacancies->contract_type = $job['job_attributes'][0];

                $args =  array(
                    'url' => 'https://global3.recruitmentplatform.com/fo/rest/jobs/'.$job['id'].'/details',
                    'method' => 'GET',
                    'header_type' => array(
                        "Accept: application/json, text/javascript, */*; q=0.01",
                        'Content-Type: application/json',
                        'username: PJBFK026203F3VBQB6879LOOB:guest:FO',
                        'password: guest',
                        "lumesse-language: EN",
                    ),
                );
                $json = $this->do_curl($args);
                $result = json_decode($json, 1);

                $this->vacancies->job_type = $result['jobFields']['CONTRACTTYPLABEL'];
                $this->vacancies->job_category = $result['jobFields']['SLOVLIST11'];
                $this->vacancies->opening_date = date('Y-m-d');

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
