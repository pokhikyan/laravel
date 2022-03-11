<?php

namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;

class Bayer extends DataScan {

    public $vacancies;

    /**
     * Adidas constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_bayer($website);
    }

    public function scan_bayer($website)
    {
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);
        $url = $website->url;


        for ( $i = 0; $i < 20; $i++ ) {
            try {
                $args = array(
                    'url' => $url.'&page='.$i,
                    'method' => 'GET',
                );
                $html = $this->do_curl($args);

                preg_match_all('/<td headers="view-nothing-table-column" class="views-field views-field-nothing">(.*?)<\/td>/s', $html, $matches);

                $links = isset($matches[1]) ? $matches[1] : '';
                if( $links == '' && $i == 0 ) {
                    $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
                }
                if( $links == '' ) {
                    break;
                }

            } catch (\Throwable $e) {
                $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' : ' . $e->getMessage(), 'error' => 1]);

                continue;
            }

            foreach ( $links as $link ) {
                try {
                    preg_match_all('/<a href=\"(.*?)\" hreflang=\"de\">(.*?)<\/a>/U', $link, $matches);
                    $job_url = 'https://karriere.bayer.de'.$matches[1][0];

                    $job_id = explode('--SF',$matches[1][0]);
                    $job_id = str_replace('_de_DE', '', $job_id[1]);
                    $args = array(
                        'url' => $job_url,
                        'method' => 'GET',
                    );
                    $html = $this->do_curl($args);
                    preg_match('/\<script type\="application\/ld\+json">(.*?)<\/script>/s', $html, $json);


                    $data = json_decode($json[1],1);
                    $this->vacancies = new Vacancies();
                    $this->vacancies->job_description = $data['description'];

                    $id = Vacancies::select('id')->where([
                                                             [ 'job_id', $job_id ],
                                                             [ 'website_id', $website->id ]
                                                         ])->first();
                    if ( !empty($id) ) {
                        continue;
                    }

                    $this->vacancies->website_id = intval($website->id);
                    $this->vacancies->job_id = $job_id;
                    $this->vacancies->location = $data["jobLocation"]["address"]["addressLocality"];
                    $this->vacancies->job_title = $data['title'];
                    $this->vacancies->city = $data["jobLocation"]["address"]["addressLocality"];

                    $this->vacancies->job_type = $data['employmentType'];
                    $this->vacancies->job_url = $job_url;
                    $this->vacancies->opening_date = date("Y-m-d", strtotime($data['datePosted']));
                    $this->vacancies->deadline = date("Y-m-d", strtotime($data['validThrough']));

/*                    preg_match('/\<span id=\"labelfunctionalArea\">(.*?)<\/span>/s', $html, $job_category);
                    $job_category = isset($job_category[1]) ? html_entity_decode($job_category[1]) : '';*/
                    preg_match('/<b>Division: <\/b><\/span>(.*?)<\/span>/s', $data['description'], $job_cat);
                    if(isset($job_cat[1])) {
                        $job_category = strip_tags($job_cat[1]);
                        $this->vacancies->job_category = trim(str_replace("​               ", "", $job_category));
                    }
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
