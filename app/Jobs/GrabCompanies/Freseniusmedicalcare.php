<?php

namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;

class Freseniusmedicalcare extends DataScan {

    public $vacancies;

    /**
     * Basf constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_freseniusmedicalcare($website);
    }

    public function scan_freseniusmedicalcare($website)
    {
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);

        $url = $website->url;
        $args =  array(
            'url' => $url,
            'method' => 'GET',
        );

        $html = $this->do_curl($args);

        preg_match('/<script id="__NEXT_DATA__" type="application\/json">(.*?)<\/script>/s', $html, $matches);

        $json = $matches[1];
        $array = json_decode($json,1);
        if( isset($array['props']) && isset($array['props']['pageProps']) && isset($array['props']['pageProps']['initialResults']) && isset($array['props']['pageProps']['initialResults']['jobAds'])) {
            $datas = $array['props']['pageProps']['initialResults']['jobAds'];
            if( empty($datas) ) {
                Log::info($website->company.' Data undefined');
                return;
            }
        } else {
            $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
            return;
        }

        foreach ( $datas as $data ) {
            try {
                $id = Vacancies::select('id')->where([ [ 'job_id', $data['id'] ], [ 'website_id', $website->id ] ])->value('id');
                if ( !empty($id) ) {
                    Vacancies::where('id', $id)->update(['updated_at' => date("Y-m-d H:i:s")]);
                    continue;
                }

                $job_url = 'https://jobs.freseniusmedicalcare.com' . $data['url'];
                $this->vacancies = new Vacancies();
                $this->vacancies->website_id      = intval($website->id);
                $this->vacancies->job_id          = $data['id'];
                $this->vacancies->location        = $data['location'];
                $this->vacancies->job_title       = $data['title'];
                $this->vacancies->city            = $data['location'];
                $this->vacancies->job_url         = $job_url;

                $args =  array(
                    'url' => $job_url,
                    'method' => 'GET',
                );

                $html = $this->do_curl($args);

                preg_match('/<script id="__NEXT_DATA__" type="application\/json">(.*?)<\/script>/s', $html, $matches);

                $json = $matches[1];
                $jobs = json_decode($json,1);
                $jobAd = $jobs['props']['pageProps']['jobAdResponse']['data']['jobAd'];

                $this->vacancies->job_description = $jobAd['description'][0]['value'];

                $ind = $this->find_index_of_attr( 'Publishing Date', $jobAd['attributes'] );
                $this->vacancies->opening_date    = date('Y-m-d', strtotime($jobAd['attributes'][$ind]['value'][0][0]));
                $ind = $this->find_index_of_attr( 'Employment relationship', $jobAd['attributes'] );
                $this->vacancies->job_type        = $jobAd['attributes'][$ind]['value'][1][0];
                $this->vacancies->contract_type   = $jobAd['attributes'][$ind]['value'][0][0];
                $ind = $this->find_index_of_attr( 'Career level', $jobAd['attributes'] );
                $this->vacancies->job_level       = implode(",", $jobAd['attributes'][$ind]['value'][0]);

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
