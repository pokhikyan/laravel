<?php

namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;

class DeutscheWohnen extends DataScan {

    public $vacancies;

    /**
     * Basf constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_deutscheWohnen($website);
    }

    public function scan_deutscheWohnen($website)
    {
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);

        $url = $website->url;
        $args =  array(
                        'url' => $url,
                        'method' => 'GET',
                    );

        $html = $this->do_curl($args);

        /* Get Job type from HTML string */
        preg_match('/<filterdata :items=\'(.*?)\'><\/filterdata>/s', $html, $matches);
        //$jobtype = isset($matches[1]) ? trim($matches[1]) : 'Azubi';

        $jsonData = isset($matches[1]) ? $matches[1] : '';

        $datas = json_decode($jsonData,1);
        if( empty($datas) ) {
            $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
            return;
        }

        foreach ( $datas as $data ) {
            try {

                $id = Vacancies::select('id')->where([ [ 'job_id', $data['id'] ], [ 'website_id', $website->id ] ])->first();
                if( !empty($id) ) {
                    continue;
                }
                $this->vacancies = new Vacancies();
                $this->vacancies->website_id      = intval($website->id);
                $this->vacancies->job_id          = $data['id'];
                $this->vacancies->location        = $data['placeOfWork'][0];
                $this->vacancies->job_title       = $data['title'];
                $this->vacancies->city            = $data['placeOfWork'][0];
                $this->vacancies->job_type        = $data['employmentType'];
                $this->vacancies->job_level       = isset($data['entryLevel'][0]) ? $data['entryLevel'][0] : '';
                $this->vacancies->contract_type   = $data['contractType'];
                //$this->vacancies->job_category    = $data['category'];
                $this->vacancies->job_url         = 'https://www.deutsche-wohnen.com'.$data['detailUrl'];
                $this->vacancies->opening_date    = date('Y-m-d', strtotime($data['publicationDate']));
                $this->vacancies->deadline        = $data['employmentDate'];
                $this->vacancies->job_description = $data['publication']['tasks']['content'];
                $this->vacancies->about_us        = $data['publication']['introduction']['content'];
                $this->vacancies->qualification   = $data['publication']['knowledge']['content'];

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
