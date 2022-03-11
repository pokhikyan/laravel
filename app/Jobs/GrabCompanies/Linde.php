<?php
/*
 *
 * Couldn't get data there is token which has time limit for getting data
 *
 * */

/**

 Max count which was possible retrieve from endpoint is 500 jobs ( total count is more then 4500)

 */
namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;
use DateTime;

class Linde extends DataScan {

    public $vacancies;

    /**
     * Basf constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_linde($website);
    }

    public function scan_linde($website)
    {
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);
        $url = $website->url;

        for ( $i = 0; $i < 1000; $i+=20 ) {
            $args = array(
                'url' => $url.'&limit=20&offset='.$i,
                'method' => 'GET',
                'header_type' => array(
                    'Accept: application/json',
                    'x-requested-with: XMLHttpRequest'
                ),
            );
            $results = $this->do_curl($args);
            $results = json_decode($results, 1);
            if( empty($results) && $i == 0) {
                $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
                break;
            } elseif (empty($results)) {
                break;
            }
            $datas = isset($results['items']) ? $results['items'] : array();
            foreach ( $datas as $data ) {
                try {
                    $id = Vacancies::select('id')->where([
                                                             [ 'job_id', $data['id'] ],
                                                             [ 'website_id', $website->id ]
                                                         ])->first();
                    if ( !empty($id) || strpos($data['company']['name'], 'Linde') === false ) {
                        continue;
                    }
                    $this->vacancies = new Vacancies();
                    $this->vacancies->website_id = intval($website->id);
                    $this->vacancies->job_id = $data['id'];
                    $this->vacancies->location = $data['location'];
                    $this->vacancies->job_title = $data['title'];
                    $this->vacanciescity = $data['location'];
                    $this->vacanciesjob_url = 'https://www.xing.com/jobs/' . $data['slug'];
                    $this->vacanciesopening_date = date("Y-m-d", strtotime($data['activatedAt']));
                    $args = array(
                        'url' => $this->vacanciesjob_url,
                        'method' => 'GET',
                    );
                    $html = $this->do_curl($args);
                    preg_match('/\<script data-rh="true" type\="application\/ld\+json">(.*?)<\/script>/s', $html, $json);
                    $data = json_decode($json[1], 1);
                    $this->vacanciesjob_type = $data['employmentType'];
                    $this->vacanciesjob_category = $data['industry'];
                    $this->vacancies->job_description = $data['description'];
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
