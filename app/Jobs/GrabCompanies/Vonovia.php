<?php

/**

 Max count which was possible retrieve from endpoint is 500 jobs ( total count is more then 4500)

 */
namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;

class Vonovia extends DataScan {

    public $vacancies;


    public $allowed_cities = array();
    /**
     * Basf constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_vonovia($website);
    }

    public function scan_vonovia($website)
    {
                $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);


        $url = $website->url;
        $datas = array();
        $j = 0;
        for( $i = 0; $i < 1000; $i+=25) {
            $args = array(
                'url' => $url.'&startrow='.$i,
                'method' => 'GET',
            );
            $html = $this->do_curl($args);
            preg_match_all('/<tr class="data-row clickable">(.*?)<\/tr>/s', $html, $matches);

            foreach ($matches[1] as $data) {
                try {
                    preg_match('/<a class="jobTitle-link" href="(.*?)">(.*?)<\/a>/s', $data, $links);
                    $datas[$j]['job_url'] = $links[1];
                    $datas[$j]['title'] = $links[2];

                    $job_id = explode('/', $datas[$j]['job_url']);
                    $ind = count($job_id);
                    $job_id = $job_id[($ind-2)];
                    $datas[$j]['job_id'] = $job_id;

                    preg_match('/<span class="jobLocation">(.*?)<\/span>/s', $data, $city);
                    $city = trim($city[1]);
                    $city = explode(',', $city);
                    $datas[$j]['city'] = trim($city[0]);

                    preg_match('/<span class="jobDepartment">(.*?)<\/span>/s', $data, $category);
                    $datas[$j]['category'] = trim($category[1]);

                    preg_match('/<span class="jobShifttype">(.*?)<\/span>/s', $data, $jobtype);
                    $datas[$j]['jobtype'] = trim($jobtype[1]);

                    $j++;

                } catch (\Throwable $e) {
                    $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' : ' . $e->getMessage(), 'error' => 1]);

                    continue;
                }
            }
        }
        if( empty($datas) ) {
            $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
            return;
        }
        foreach ( $datas as $data ) {
            try {
                $job_id = $data['job_id'];
                $id = Vacancies::select('id')->where([ [ 'job_id', $job_id ], [ 'website_id', $website->id ] ])->value('id');
                if ( !empty($id) ) {
                    Vacancies::where('id', $id)->update(['updated_at' => date("Y-m-d H:i:s")]);
                    continue;
                }
                $this->vacancies = new Vacancies();
                $this->vacancies->website_id = intval($website->id);
                $this->vacancies->job_id = $job_id;
                $this->vacancies->job_title = $data['title'];
                $this->vacancies->job_url = 'https://jobs.vonovia.de'.$data['job_url'];
                $this->vacancies->location = $data['city'];
                $this->vacancies->city = $data['city'];
                $this->vacancies->job_level = $data['jobtype'];
                //$this->vacancies->contract_type = $job['ContractType']['Text'];
                $this->vacancies->job_category = $data['category'];

                $args = array(
                    'url' => 'https://jobs.vonovia.de'.$data['job_url'],
                    'method' => 'GET',
                );
                $html = $this->do_curl($args);

                preg_match('/<meta itemprop="datePosted" content="(.*?)">/s', $html, $date);
                $this->vacancies->opening_date = date('Y-m-d', strtotime($date[1]));

                preg_match('/<span class="jobdescription">(.*?)<\/div>/s', $html, $description);
                if( isset($description[1]) ) {
                    $this->vacancies->job_description = $description[1];
                }

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
