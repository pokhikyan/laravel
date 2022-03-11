<?php

namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;
use phpDocumentor\Reflection\Types\Null_;

class Covestro extends DataScan {

    public $vacancies;

    /**
     * Continental constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_covestro($website);
    }

    public function scan_covestro($website)
    {
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);
        $postfields = array(
            'facets'=> 'Location_Country',
            'Location_Country'=> 'Location_Country::dcc5b7608d8644b3a93716604e78e995',
            'sessionSecureToken'=> '13g9s2t3c4ghrjn8rsqhj0tjmj',
            'clientRequestID'=> '9407de2f518d4713bd1c2358069b44f8',
        );
        $args =  array(
            'url' => 'https://covestro.wd3.myworkdayjobs.com/cov_external/fs/replaceFacet/318c8bb6f553100021d223d9780d30be',
            'postfield' => $postfields
        );

        $json = $this->do_curl($args);
        for( $i = 0; $i < 10; $i++ ) {
            if( $i > 0 ) {
                $page = $i*50;
                $args =  array(
                    'url' => 'https://covestro.wd3.myworkdayjobs.com/cov_external/9/searchPagination/318c8bb6f553100021d223d9780d30be/'.$page.'?facets=Location_Country&Location_Country=Location_Country::dcc5b7608d8644b3a93716604e78e995',
                    'method' => 'GET',
                    'header_type' => array(
                        'Accept: application/json',
                        'Content-Type: application/json'
                    ),
                );

                $json = $this->do_curl($args);
            }
            if( empty($json) && $i == 0 ) {
                $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
                break;
            }
            try {
                $data = json_decode($json, 1);
                if( !isset($data['body']) ) {
                    continue;
                }
                $items = $data['body']['children'][1]['children'][0]['listItems'];
                $german_locations = array(
                    'Leverkusen',
                    'Dormagen',
                    'Uerdingen',
                    'North Rhine-Westphalia',
                    'Germany',
                    'Brunsbüttel',
                    'Schleswig-Holstein',
                    'Meppen',
                    'Dormagen',
                    'Bomlitz',
                    'Lower Saxony',
                    'Berlin'
                );
            } catch (\Throwable $e) {
                $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' : ' . $e->getMessage(), 'error' => 1]);

                $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
                continue;
            }
            foreach ( $items as $item ) {
                try {
                    $location = FALSE;
                    foreach ( $german_locations as $location ) {
                        if ( strpos($location, $item['title']['commandLink']) > 0 ) {
                            $location = TRUE;
                        }
                        break;
                    }
                    if ( !$location ) {
                        continue;
                    }
                    $url = 'https://covestro.wd3.myworkdayjobs.com' . $item['title']['commandLink'];
                    $args =  array(
                        'url' => $url,
                        'method' => 'GET',
                        'header_type' => array(
                            'Accept: application/json',
                            'Content-Type: application/json'
                        ),
                    );

                    $json = $this->do_curl($args);
                    $result = json_decode($json, 1);
                    $id = Vacancies::select('id')->where([ [ 'job_url', $url ], [ 'website_id', $website->id ] ])->first();
                    if ( !empty($id) ) {
                        continue;
                    }
                    $data = json_decode($result['structuredDataAttributes']['data'], 1);
                    if ( trim($data['jobLocation']['address']['addressCountry']) !== 'Germany' && trim($data['jobLocation']['address']['addressCountry']) !== 'Deutschland' ) {
                        continue;
                    }
                    $this->vacancies = new Vacancies();
                    $this->vacancies->website_id = intval($website->id);
                    $this->vacancies->job_id = $data['identifier']['value'];
                    $this->vacancies->location = $data['jobLocation']['address']['addressLocality'];
                    $this->vacancies->job_title = $data['title'];
                    $this->vacancies->city = $data['jobLocation']['address']['addressLocality'];
                    $this->vacancies->job_type = str_replace('_', ' ', $data['employmentType']);
                    $this->vacancies->job_category = NULL;
                    $this->vacancies->job_url = $url;
                    $this->vacancies->opening_date = date("Y-m-d", strtotime($data['datePosted']));
                    $this->vacancies->job_description = $data['description'];
                    $this->vacancies->about_us = $result['body']['children'][1]['children'][1]['children'][3]['children'][1]['text'];
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
