<?php

namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;

class Deliveryhero extends DataScan {

    public $vacancies;

    /**
     * Basf constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_deliveryhero($website);
    }

    public function scan_deliveryhero($website)
    {
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);

        $url = $website->url;
        $args =  array(
                        'url' => $url,
                        'method' => 'POST',
                        'header_type' => array(
                            'Accept: application/json',
                            'Content-Type: application/json'
                        ),
                        'postfield' => '{"lang":"en_global","deviceType":"desktop","country":"global","pageName":"DHSE_search-results","ddoKey":"refineSearch","sortBy":"","subsearch":"","from":0,"jobs":true,"counts":true,"all_fields":["country","city","brand","category","subCategory","jobType"],"size":1000,"clearAll":false,"jdsource":"facets","isSliderEnable":false,"pageId":"page11","siteType":"external","keywords":"","global":true,"selected_fields":{"country":["Germany"]}}'
                    );

        $response = $this->do_curl($args);

        $datas = json_decode($response,1);

        if( isset($datas['refineSearch']) && isset($datas['refineSearch']['data']) && isset($datas['refineSearch']['data']['jobs'])) {
            $datas = $datas['refineSearch']['data']['jobs'];
        } else {
            $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
            return;
        }

        foreach ( $datas as $data ) {
            try {
                $id = Vacancies::select('id')->where([ [ 'job_id', $data['jobId'] ], [ 'website_id', $website->id ] ])->value('id');
                if ( !empty($id) ) {
                    Vacancies::where('id', $id)->update(['updated_at' => date("Y-m-d H:i:s")]);
                    continue;
                }

                $this->vacancies = new Vacancies();
                $this->vacancies->website_id      = intval($website->id);
                $this->vacancies->job_id          = $data['jobId'];
                $this->vacancies->location        = $data['city'];
                $this->vacancies->job_title       = $data['title'];
                $this->vacancies->city            = $data['city'];
                $this->vacancies->job_type        = $data['type'];
                $this->vacancies->job_category    = $data['category'];
                $this->vacancies->job_url         = str_replace('/apply', '', $data['applyUrl']);

                $args =  array(
                    'url' => $this->vacancies->job_url,
                    'method' => 'POST',
                    'header_type' => array(
                        'Accept: application/json',
                        'Content-Type: application/json'
                    ),
                );
                $job_response = $this->do_curl($args);

                $job_data = json_decode($job_response,1);
                $job_data = $job_data['structuredDataAttributes']['data'];
                $job_data = json_decode($job_data,1);

                $this->vacancies->opening_date    = date("Y-m-d", strtotime($job_data['datePosted']));
                $this->vacancies->job_description = $job_data['description'];

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
