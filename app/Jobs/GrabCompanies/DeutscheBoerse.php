<?php
/**

Need to fix

 */

namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;

class DeutscheBoerse extends DataScan {

    public $vacancies;

    /**
     * Basf constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_deutscheBoerse($website);
    }

    public function scan_deutscheBoerse($website)
    {
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);
        $job_links = array();
        for( $i = 0; $i < 500; $i+=25 ) {
            $args =  array(
                'url' => 'https://career.deutsche-boerse.com/search?q=&location=DE&sortDirection=desc&startrow='.$i,
                'method' => 'GET',
            );
            $html = $this->do_curl($args);

            preg_match_all('/<a class="jobTitle-link" href="(.*?)">/s', $html, $matches);
            if( !isset($matches[1]) ) {
                break;
            }
            $job_links = array_merge($job_links, $matches[1]);
        }
        if( empty($job_links) ) {
            $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
            return;
        }

        foreach ( $job_links as $link ) {
            try {
                if( strpos($link,'/eex/') !== FALSE || strpos($link,'/ecc/') !== FALSE )  {
                    continue;
                }
                $args =  array(
                    'url' => "https://career.deutsche-boerse.com".$link,
                    'method' => 'GET',
                );
                $html = $this->do_curl($args);
                preg_match('/jobID\s*:(.*?),/s', $html, $job_id);
                $job_id = trim($job_id[1]);
                $id = Vacancies::select('id')->where([ [ 'job_id', $job_id ], [ 'website_id', $website->id ] ])->first();
                if( !empty($id) ) {
                    continue;
                }

                preg_match('/<span class="jobGeoLocation">(.*?)<\/span>/s', $html, $city);
                if( isset($city[1]) ) {
                    $cityy = explode(',', $city[1]);
                    $city = $cityy[0];
                    $country = $cityy[1];
                }
                if( trim($country) !== 'DE' ) {
                    continue;
                }
                $this->vacancies = new Vacancies();
                $this->vacancies->website_id      = intval($website->id);
                $this->vacancies->job_id          = $job_id;

                $this->vacancies->location = $city;

                preg_match('/<h1 id="job-title" itemprop="title">(.*?)<\/h1>/s', $html, $job_title);
                $this->vacancies->job_title       = trim($job_title[1]);
                $this->vacancies->city            = $city;
                $this->vacancies->job_url = "https://career.deutsche-boerse.com".$link;

                preg_match('/id="job-date">(.*?)<\/p>/s', $html, $opening_date);
                $opening_date = explode('</strong>', $opening_date[1]);
                $opening_date = trim($opening_date[1]);

                $this->vacancies->opening_date = date('Y-m-d', strtotime($opening_date));

                preg_match('/class="jobdescription">(.*?)<\/span>/s', $html, $job_description);
                $this->vacancies->job_description = $job_description[1];

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
