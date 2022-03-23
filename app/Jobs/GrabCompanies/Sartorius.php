<?php

/**

 Max count which was possible retrieve from endpoint is 500 jobs ( total count is more then 4500)

 */
namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use DateTime;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class Sartorius extends DataScan {

    public $vacancies;

    /**
     * Bmw constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_sartorius($website);
    }

    public function scan_sartorius($website)
    {
                $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);


        $url = $website->url;
        $links = array();
        for($i = 0; $i < 1000; $i+=100) {
            $args = array(
                'url' => $url.'&offset='.$i.'&limit=100',
                'method' => 'GET',
            );
            $html = $this->do_curl($args);
            preg_match_all('/<a class="bab\-a\-button bab\-a\-button\-\-ghostDark " rel="" href="(.*?)" >/s', $html, $matches);
            if( !isset($matches[1]) ) {
                break;
            }
            $links = array_merge($links, $matches[1]);
        }
        if( empty($links) ) {
            $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
            return;
        }

        foreach ( $links as $link ) {
            try {
                $args = array(
                    'url' => 'https://www.sartorius.com'.$link,
                    'method' => 'GET',
                );
                $html = $this->do_curl($args);


                preg_match('/\<script type\="application\/ld\+json">(.*?)<\/script>/s', $html, $json);
                $data = json_decode($json[1],1);

                $job_id = $data['identifier']['value'];

                $id = Vacancies::select('id')->where([ [ 'job_id', $job_id ], [ 'website_id', $website->id ] ])->value('id');
                if ( !empty($id) ) {
                    Vacancies::where('id', $id)->update(['updated_at' => date("Y-m-d H:i:s")]);
                    continue;
                }
                $this->vacancies = new Vacancies();
                $this->vacancies->website_id = intval($website->id);
                $this->vacancies->job_title = $data['title'];
                $this->vacancies->job_id = $job_id;
                $location = $data['jobLocation']['address']['addressLocality'];
                $this->vacancies->location = $location;
                $this->vacancies->city = $location;
                $this->vacancies->job_url = 'https://www.sartorius.com'.$link;
                $this->vacancies->opening_date = date('Y-m-d');
                $this->vacancies->job_description = $data['description'];
                $this->vacancies->job_type = $data['employmentType'];

                preg_match('/labels\.job\.functionalarea">(.*?)<span class="bab-m-detail__value">(.*?)<\/span>/s', $html, $matches);
                $this->vacancies->job_category = isset($matches[2]) ? trim($matches[2]) : '';


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
