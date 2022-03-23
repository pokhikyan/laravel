<?php

namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;

class Allianz extends DataScan {

    public $vacancies;

    /**
     * Adidas constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_allianz($website);
    }

    public function scan_allianz($website)
    {
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);
        $url = $website->url;
        $args =  array(
            'url' => $url,
            'method' => 'POST',
        );

        $xml = $this->do_curl($args);
        $xml = $this->xmlToArray($xml);

        $datas = $xml['url'];
        if( empty($datas) ) {
            Log::info($website->company.' Sitemap undefined');
            $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);

            return;
        }

        foreach ( $datas as $data ) {
            try {
                if( empty($data["loc"]) ) continue;
                $job_url = $data["loc"];
                $id = Vacancies::select('id')->where([ [ 'job_url', $job_url ], [ 'website_id', $website->id ] ])->value('id');
                if ( !empty($id) ) {
                    Vacancies::where('id', $id)->update(['updated_at' => date("Y-m-d H:i:s")]);
                    continue;
                }
                $html = file_get_contents($job_url);
                if( $html === false ) {
                    continue;
                }
                $this->vacancies = new Vacancies();
                $this->vacancies->website_id = intval($website->id);

                preg_match('/data-careersite-propertyid="adcode">(.*?)<\/span>/s', $html, $jobids);
                $jobid = isset($jobids[1]) ? trim($jobids[1]) : '';
                $this->vacancies->job_id = $jobid;

                preg_match('/<span class="jobGeoLocation">(.*?)<\/span>/s', $html, $jobloc);
                $city = '';
                $location = '';
                if( isset($jobloc[1]) ) {
                    $location = explode(',', $jobloc[1]);
                    $location = array_map('trim', $location);
                    $city = $location[0];
                }

                if( !in_array("DE", $location) ) {
                    continue;
                }
                $this->vacancies->location = $city;
                $this->vacancies->city = $city;

                preg_match('/data-careersite-propertyid="title">(.*?)<\/span>/s', $html, $jobtitle);
                $jobtitle = isset($jobtitle[1]) ? trim($jobtitle[1]) : '';
                $this->vacancies->job_title = $jobtitle;

                preg_match('/data-careersite-propertyid="customfield3">(.*?)<\/span>/s', $html, $jobType);
                $jobType = isset($jobType[1]) ? trim($jobType[1]) : '';
                $this->vacancies->job_type = $jobType;

                preg_match('/data-careersite-propertyid="customfield5">(.*?)<\/span>/s', $html, $jobLevel);
                $jobLevel = isset($jobLevel[1]) ? trim($jobLevel[1]) : '';
                $this->vacancies->job_level = $jobLevel;

                preg_match('/data-careersite-propertyid="facility">(.*?)<\/span>/s', $html, $contractType);
                $contractType = isset($contractType[1]) ? trim($contractType[1]) : '';
                $this->vacancies->contract_type = $contractType;

                preg_match('/data-careersite-propertyid="department">(.*?)<\/span>/s', $html, $jobCategory);
                $jobCategory = isset($jobCategory[1]) ? trim($jobCategory[1]) : '';
                $this->vacancies->job_category = $jobCategory;

                $this->vacancies->job_url = $job_url;

                preg_match('/data-careersite-propertyid="customfield1">(.*?)<\/span>/s', $html, $deadline);
                $deadline = isset($deadline[1]) ? trim($deadline[1]) : '';
                $this->vacancies->deadline = $deadline;

                preg_match('/data-careersite-propertyid="description">(.*?)<\/span>/s', $html, $description);
                $description = isset($description[1]) ? trim($description[1]) : '';
                $this->vacancies->job_description = $description;

                preg_match('/<meta itemprop="datePosted" content="(.*?)">/s', $html, $openingDate);
                $openingDate = isset($openingDate[1]) ? trim($openingDate[1]) : date("Y-m-d");
                $this->vacancies->opening_date = date("Y-m-d", strtotime($openingDate));

                $this->vacancies->save();

            } catch (\Throwable $e) {
                $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' : ' . $e->getMessage(), 'error' => 1]);

                continue;
            }
        }

        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan ended']);
    }

}
