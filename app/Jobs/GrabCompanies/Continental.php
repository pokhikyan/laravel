<?php

namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;

class Continental extends DataScan {

    public $vacancies;

    /**
     * Continental constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_continental($website);
    }

    public function scan_continental($website)
    {
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);
        $url = $website->url;

        $args =  array(
            'url' => $url,
            'method' => 'GET',
        );

        $xml = $this->do_curl($args);
        $datas = json_decode($xml,1);

        if( isset($datas['SearchResult']) && isset($datas['SearchResult']['SearchResultItems'])) {
            $datas = $datas['SearchResult']['SearchResultItems'];
        } else {
            $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
        }

        foreach ( $datas as $data ) {
            try {
                $id = Vacancies::select('id')->where([ [ 'job_id', $data['MatchedObjectDescriptor']['PositionID'] ], [ 'website_id', $website->id ] ])->first();
                if( !empty($id) ) {
                    continue;
                }
                $this->vacancies = new Vacancies();
                $this->vacancies->website_id      = intval($website->id);
                $this->vacancies->job_id          = $data['MatchedObjectDescriptor']['PositionID'];
                $this->vacancies->location        = $data['MatchedObjectDescriptor']['PositionLocation'][0]['CityName'];
                $this->vacancies->job_title       = $data['MatchedObjectDescriptor']['PositionTitle'];
                $this->vacancies->city            = $data['MatchedObjectDescriptor']['PositionLocation'][0]['CityName'];
                //$this->vacancies->job_type        = $data['jobtype'];
                $this->vacancies->job_category    = $data['MatchedObjectDescriptor']['JobCategory']['Name'];
                $this->vacancies->job_url         = $data['MatchedObjectDescriptor']['PositionURI'];
                $this->vacancies->opening_date    = date("Y-m-d", strtotime($data['MatchedObjectDescriptor']['PublicationStartDate']));

                $args =  array(
                    'url' => $this->vacancies->job_url,
                );
                $html = $this->do_curl($args);

                preg_match_all('/<div class="panel-body">(.*?)<\/div>/s', $html, $matches);
                $description = $matches[1][0];
                $this->vacancies->job_description = $description;
                $this->vacancies->qualification = $matches[1][1];
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
