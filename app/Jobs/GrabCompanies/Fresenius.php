<?php

namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;

class Fresenius extends DataScan {

    public $vacancies;

    /**
     * Basf constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_fresenius($website);
    }


    public function scan_fresenius($website)
    {
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);

        $url = $website->url;
        $args =  array(
            'url' => $url,
            'method' => 'GET',
        );

        $xml = $this->do_curl($args);

        $xml = $this->xmlToArray($xml);

        $datas = isset($xml['url']) ? $xml['url'] : array();
        if( empty($datas) ) {
            Log::info($website->company.' Sitemap undefined');
            $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
            return;
        }

        foreach ( $datas as $data ) {
          try {
              if( empty($data["loc"]) ) continue;

              $job_url = $data["loc"];

              if( strpos($job_url, "/de/job/") === false ) {
                continue;
              }
              $job_url = str_replace('https://frontend-production:3000', 'https://karriere.fresenius.de', $job_url);
              $id = Vacancies::select('id')->where([ [ 'job_url', $job_url ], [ 'website_id', $website->id ] ])->first();
              if( !empty($id) ) {
                continue;
              }
              /* $html = $this->do_curl($job_url);*/
              $args =  array(
                'url' => $job_url,
                'method' => 'GET',
              );

              $html = $this->do_curl($args);

              // $html = file_get_contents($job_url);
              if( $html === false ) {
                continue;
              }
              /* Get location from HTML string */
              preg_match('/<script id="__NEXT_DATA__" type="application\/json">(.*?)<\/script>/s', $html, $matches);
              $json = $matches[1];

              $array = json_decode($json,1);
              $jobID = $array['props']['pageProps']['pageContent']['jobAdId'];

              $jobAd = $array['props']['pageProps']['jobAdResponse']['data']['jobAd'];
              $this->vacancies = new Vacancies();
              $this->vacancies->website_id      = intval($website->id);
              $this->vacancies->job_id          = $jobID;
              $ind = $this->find_index_of_attr( 'Standort', $jobAd['attributes'] );
              $this->vacancies->location        = implode(',', $jobAd['attributes'][$ind]['value'][1]);
              $this->vacancies->job_title       = $jobAd['title'];
              $this->vacancies->city            = implode(',', $jobAd['attributes'][$ind]['value'][0]);

              $ind = $this->find_index_of_attr( 'Veröffentlicht am', $jobAd['attributes'] );
              $this->vacancies->opening_date = date('Y-m-d', strtotime($jobAd['attributes'][$ind]['value'][0][0]));
              $ind = $this->find_index_of_attr( 'Arbeitsverhältnis', $jobAd['attributes'] );
              $this->vacancies->job_type        = $jobAd['attributes'][$ind]['value'][1][0];
              $this->vacancies->contract_type   = $jobAd['attributes'][$ind]['value'][0][0];

              //$this->vacancies->job_category    = $jobcategory;
              $this->vacancies->job_description = $jobAd['description'][0]['value'];
              $this->vacancies->job_url         = $job_url;

              $this->vacancies->save();
          } catch (\Throwable $e) {
              $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' : ' . $e->getMessage(), 'error' => 1]);

              continue;
          }

        }
        Log::info($website->company.' Scan Ended');
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan ended']);
    }

    public function find_index_of_attr( $attr_title, $attributes ) {
        foreach ( $attributes as $key => $attr ) {
            if( $attr['title'] == $attr_title ) {
                return $key;
            }
        }
        return -1;
    }

}
