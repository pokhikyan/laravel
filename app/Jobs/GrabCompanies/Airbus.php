<?php

namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;

class Airbus extends DataScan {

    public $vacancies;

    /**
     * Basf constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_airbus($website);
    }

    public function scan_airbus($website)
    {
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);

        $url = $website->url;

        $jobs = array();
        for( $i = 0; $i < 100; $i++ ) {
            $args = array(
                'url' => $url,
                'method' => 'POST',
                'header_type' => array(
                    'Accept: application/json',
                    'Content-Type: application/json'
                ),
                'postfield' => '{"limit":20,"offset":' . intval($i*20) . ',"appliedFacets":{"locationCountry":["dcc5b7608d8644b3a93716604e78e995"]},"searchText":""}',
            );
            $data = $this->do_curl($args);

            $data = json_decode($data, 1);

            if( empty($data) && $i == 0 ) {
                $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
            }
            if( $i == 0 ) {
                $total = $data['total'];
            }
            if( count($jobs) >= $total ) {
                break;
            } else {
                $jobs = array_merge($jobs, $data['jobPostings']);
            }
        }

        if( !empty($jobs) ) {
            foreach ( $jobs as $job ) {
                try {

                    $job_url = 'https://ag.wd3.myworkdayjobs.com/en-US/Airbus' . $job['externalPath'];
                    $job_id = $job['bulletFields'][0];
                    $id = Vacancies::select('id')->where([ [ 'job_id', $job_id ], [ 'website_id', $website->id ] ])->value('id');
                    if( !empty($id) ) {
                        Vacancies::where('id', $id)->update(['updated_at' => date("Y-m-d H:i:s")]);
                        continue;
                    }

                    $bullets = explode('/', $job['bulletFields'][1]);
                    $this->vacancies = new Vacancies();
                    $this->vacancies->website_id = intval($website->id);
                    $this->vacancies->job_id = $job_id;
                    $this->vacancies->job_title = $job['title'];
                    $this->vacancies->job_url = $job_url;

                    $this->vacancies->opening_date = $this->convertDate(str_replace("Posted ", "", $job['postedOn']));

                    $args = array(
                        'url' => 'https://ag.wd3.myworkdayjobs.com/wday/cxs/ag/Airbus' . $job['externalPath'],
                        'method' => 'GET',
                    );
                    $data = $this->do_curl($args);

                    $data = json_decode($data, 1);
                    $data = $data['jobPostingInfo'];
                    $this->vacancies->location = $data["location"];
                    $this->vacancies->city = $data["location"];
                    //$this->vacancies->job_type = $data['timeType'];
                    $this->vacancies->job_description = $data['jobDescription'];

                    preg_match('/<b>Contract Type:<\/b><\/p>(.*?)<p style="text-align:inherit">/s', $data['jobDescription'], $matches);
                    $job_type = isset($matches[1]) ? $matches[1] : [];
                    if( !empty($job_type) ) {
                        $job_type_array = explode('/', $job_type);
                        $this->vacancies->job_type = isset($job_type_array[0]) ? trim($job_type_array[0]) :  trim($job_type);
                    }

                    preg_match('/<b>Experience Level:<\/b><\/p>(.*?)<p style="text-align:inherit">/s', $data['jobDescription'], $matches);
                    $job_level = isset($matches[1]) ? $matches[1] : [];
                    if( !empty($job_level) ) {
                        $job_level_array = explode('/', $job_level);
                        $this->vacancies->job_level = isset($job_level_array[0]) ? trim($job_level_array[0]) : trim($job_level);
                    }

                    preg_match('/<b>Job Family:<\/b><\/p>(.*?)<p style="text-align:inherit">/s', $data['jobDescription'], $matches);
                    $job_category = isset($matches[1]) ? $matches[1] : [];
                    if( !empty($job_category) ) {
                        $job_category_array = explode('/', $job_category);
                        $this->vacancies->job_category = isset($job_category_array[0]) ? trim($job_category_array[0]) : trim($job_category);
                    }

                    $this->vacancies->save();

                } catch (\Throwable $e) {
                    $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' : ' . $e->getMessage(), 'error' => 1]);
                }
            }
        }
        Log::info($website->company.' Scan Ended');
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan ended']);
    }

    public function convertDate( $dateString ) {

        if( strpos($dateString, "Today") > -1 ) {
            return date("Y-m-d");
        } elseif ( strpos($dateString, "Yesterday") > -1 ) {
            return date("Y-m-d", strtotime("-1 days"));
        } elseif ( strpos($dateString, "Days Ago") > -1 ) {
            $days = trim(str_replace(array("Posted", "Days Ago"), array("",""), $dateString));
            if( strpos($days, "30+") > -1 ) {
                $days = trim(str_replace("+", "", $days));
            }
            return date("Y-m-d", strtotime("-" . $days . " days"));
        }
    }

}
