<?php

/**

 Max count which was possible retrieve from endpoint is 500 jobs ( total count is more then 4500)

 */
namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;
use DateTime;

class Infineon extends DataScan {

    public $vacancies;

    /**
     * Basf constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_infineon($website);
    }

    public function scan_infineon($website)
    {
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);

        $url = $website->url;

        $args =  array(
            'url' => $url,
            'method' => 'POST',
            'header_type' => array(
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded'
            ),
            'postfield' => 'term=&offset=0&max_results=1000&lang=de&country=Deutschland',
        );
        $results = $this->do_curl($args);
        $results = json_decode($results, 1);
        if( isset($results['pages']) && isset($results['pages']['items']) ) {
            $jobs = $results['pages']['items'];
        } else {
            $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
            return;
        }


        foreach ( $jobs as $job ) {
            try {
                $id = Vacancies::select('id')->where([ [ 'job_id', $job['id'] ], [ 'website_id', $website->id ] ])->first();
                if( !empty($id) ) {
                    continue;
                }
                $this->vacancies = new Vacancies();
                $this->vacancies->website_id = intval($website->id);
                $this->vacancies->job_id = $job['id'];
                $this->vacancies->location = $job['location'][0];
                $this->vacancies->job_title = $job['title'];
                $this->vacancies->city = $job['location'][0];
                $this->vacancies->job_type = $job['job_attributes'][1];
                $this->vacancies->contract_type = $job['job_attributes'][0];
                $this->vacancies->job_category = $job['functional_area'];
                $this->vacancies->job_url = 'https://www.infineon.com'.$job['detail_page_url'];
                $this->vacancies->opening_date = $this->convert_to_date($job['creation_date']);


                $this->vacancies->job_description = $job['description'];

                $this->vacancies->save();
            } catch (\Throwable $e) {
                $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' : ' . $e->getMessage(), 'error' => 1]);

                continue;
            }
        }
        Log::info($website->company.' Scan Ended');
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan ended']);
    }

    public function convert_to_date( $open_data ) {

        if( strpos($open_data, 'Monat') > 0 ) { //Months
            $data = explode(' ',$open_data);
            $months = $data[1];

            $date = new DateTime('now');
            $date->modify('-'.$months.' month');
            $date = $date->format('Y-m-d');
            return $date;
        } elseif ( strpos($open_data, 'Woche') > 0 ) { //Weeks
            $data = explode(' ',$open_data);
            $weeks = $data[1];

            $date = new DateTime('now');
            $date->modify('-'.$weeks.' week');
            $date = $date->format('Y-m-d');
            return $date;
        } elseif ( strpos($open_data, 'Tag') > 0 ) { //Days
            $data = explode(' ',$open_data);
            $days = $data[1];

            $date = new DateTime('now');
            $date->modify('-'.$days.' day');
            $date = $date->format('Y-m-d');
            return $date;
        } elseif ( strpos($open_data, 'Stunden') > 0 ) { //Hours
            $data = explode(' ',$open_data);
            $hours = $data[1];

            $date = new DateTime('now');
            $date->modify('-'.$hours.' hour');
            $date = $date->format('Y-m-d');
            return $date;
        } elseif ( strpos($open_data, 'Jahren') > 0 ) { //Years
            $data = explode(' ',$open_data);
            $years = $data[1];

            $date = new DateTime('now');
            $date->modify('-'.$years.' year');
            $date = $date->format('Y-m-d');
            return $date;
        }
    }
}
