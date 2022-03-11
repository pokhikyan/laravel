<?php

namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;

class HeidelbergCement extends DataScan {

    public $vacancies;

    /**
     * Adidas constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_heidelbergCement($website);
    }

    public function scan_heidelbergCement($website)
    {
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);
        $url = $website->url;


        for ( $i = 0; $i < 20; $i++ ) {

            $args = array(
                'url' => $url,
                'method' => 'POST',
                'postfield' => 'page='.$i.'&view_name=job_search&view_display_id=search_extended_list'
            );
            $json = $this->do_curl($args);

            $json = json_decode($json,1);
            $html = $json[1]['data'];
            preg_match_all('/<tr(.*?)<\/tr>/s', $html, $matches);
            if( empty($matches[0]) ) {
                break;
            }
            $datas = $matches[0];

            if( strpos( $datas[0], '<a href=' ) === FALSE ) {
                unset($datas[0]);
            }
            if( empty($datas) && $i == 0 ) {
                $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
            }
            foreach ( $datas as $data ) {
                try {
                    preg_match('/<a href=\"(.*?)\" target/s', $data, $link);
                    $link = $link[1];

                    preg_match_all('/<td  class=\"views-field views-field-field-job-offer-location\">(.*?)<\/td>/s', $data, $city);
                    $city = trim($city[1][0]);

                    preg_match_all('/<td  class=\"views-field views-field-field-job-offer-category\">(.*?)<\/td>/s', $data, $category);
                    $job_category = trim($category[1][0]);

                    preg_match_all('/<a .*?>(.*?)<\/a>/', $data, $title);
                    $title = $title[1][0];

                    $args = array(
                        'url' => 'https://www.heidelbergcement.com' . $link,
                        'method' => 'GET',
                    );
                    $html = $this->do_curl($args);
                    preg_match('/\<script type\="application\/ld\+json" data-bbcc="ignore">(.*?)<\/script>/s', $html, $json);
                    $json_data = json_decode($json[1], 1);
                    $json_object = $json_data["@graph"][0];


                    preg_match('/<div class="field field-name-field-job-offer-contract-type field-type-taxonomy-term-reference field-label-inline clearfix field-wrapper\">(.*?)<\/div><\/div>/s', $html, $job_type);
                    $job_type = $job_type[1];
                    $job_type = explode('</div>', $job_type);
                    $job_type = trim(end($job_type));

                    preg_match('/<div class="field field-name-field-job-offer-entry-level field-type-taxonomy-term-reference field-label-inline clearfix field-wrapper\">(.*?)<\/div><div/s', $html, $job_level);

                    $job_level = $job_level[1];
                    $job_level = explode('</div>', $job_level);
                    $job_level = trim(end($job_level));
                    $job_level = explode("/", $job_level);
                    $job_level = $job_level[0];


                    //<div class="field field-name-field,-job-offer-contract-type field-type-taxonomy-term-reference field-label-inline clearfix field-wrapper">

                    $id = Vacancies::select('id')->where([
                                                             [ 'job_title', $title ],
                                                             [ 'website_id', $website->id ]
                                                         ])->first();
                    if ( !empty($id) ) {
                        continue;
                    }
                    $this->vacancies = new Vacancies();
                    $this->vacancies->website_id = intval($website->id);
                    //$this->vacancies->job_id = $job_id;
                    $this->vacancies->location = $city;
                    $this->vacancies->job_title = $title;
                    $this->vacancies->city = $city;

                    $this->vacancies->job_type = $job_type;
                    $this->vacancies->job_level = $job_level;
                    $this->vacancies->job_url = 'https://www.heidelbergcement.com' . $link;
                    $this->vacancies->opening_date = date("Y-m-d", strtotime($json_object['datePosted']));
                    $this->vacancies->job_description = $json_object['description'];
                    // $this->vacancies->deadline = date("Y-m-d", strtotime($data['validThrough']));

                    $this->vacancies->job_category = $job_category;

                    $this->vacancies->save();

                } catch (\Throwable $e) {
                    $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' : ' . $e->getMessage(), 'error' => 1]);

                    continue;
                }
            }
        }
        Log::info($website->company.' Scan Ended');
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan ended']);
    }

}
