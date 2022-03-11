<?php

namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;

class Adidas extends DataScan {

    public $vacancies;

    /**
     * Adidas constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_adidas($website);
    }

    public function scan_adidas($website)
    {
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);

        $url = $website->url;

        for ( $i = 0; $i < 1000; $i+=20 ) {
            $args = array(
                'url' => $url.'&offset='.$i,
                'method' => 'GET',
                'header_type' => array(
                    'Accept: application/json',
                    'x-requested-with: XMLHttpRequest'
                ),
            );
            $results = $this->do_curl($args);
            $results = json_decode($results, 1);
            if( empty($results) ) {
                $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
                break;
            }
            $datas = $results['jobs'];
            foreach ( $datas as $data ) {
                try {
                    $id = Vacancies::select('id')->where([
                                                             [ 'job_id', $data['requisitionid'] ],
                                                             [ 'website_id', $website->id ]
                                                         ])->first();
                    if ( !empty($id) ) {
                        continue;
                    }
                    $this->vacancies = new Vacancies();
                    $this->vacancies->website_id = intval($website->id);
                    $this->vacancies->job_id = $data['requisitionid'];
                    $this->vacancies->location = $data['city'];
                    $this->vacancies->job_title = $data['external_title'];
                    $this->vacancies->city = $data['city'];
                    $this->vacancies->job_category = $data['function'];
                    $this->vacancies->job_url = $data['external_url'];
                    $this->vacancies->opening_date = date("Y-m-d", strtotime($data['dateformatted']));


                    $args = array(
                        'url' => $data['external_url'],
                        'method' => 'GET',
                    );
                    $html = $this->do_curl($args);

                    preg_match('/<span xml\:lang=\"en-US\" lang=\"en-US\" data-careersite-propertyid=\"shifttype\">(.*?)<\/span>/s', $html, $jobtypes);
                    $this->vacancies->job_type = $jobtypes[1];

                    preg_match('/<span class=\"jobdescription\">(.*?)<\/span>/s', $html, $jobdesc);
                    $this->vacancies->job_description = $jobdesc[1];

                    $this->vacancies->save();

                } catch (\Throwable $e) {
                    $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' : ' . $e->getMessage(), 'error' => 1]);
                    continue;
                }
            }
        }
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan ended']);
    }

}
