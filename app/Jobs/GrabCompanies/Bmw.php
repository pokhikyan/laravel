<?php

/**

 Max count which was possible retrieve from endpoint is 500 jobs ( total count is more then 4500)

 */
namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;
use DateTime;

class Bmw extends DataScan {

    public $vacancies;

    /**
     * Bmw constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_bmw($website);
    }

    public function scan_bmw($website)
    {
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);

        $url = $website->url;

        $args =  array(
            'url' => $url,
            'method' => 'GET',
        );
        $html = $this->do_curl($args);

        preg_match_all('/<a class="grp-popup-lnk grp-popup-jobdescription"(.*?)<\/a>/s', $html, $matches);
        $datas = isset($matches[0]) ? $matches[0] : '';
        if( empty($datas) ) {
            $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
            return;
        }

        foreach ( $datas as $data ) {
            try {
                preg_match('/<div class="grp-jobfinder-cell-refno">(.*?)<\/div>/s', $data, $jobids);
                $jobid = $jobids[1];

                preg_match('/<div class="grp-jobfinder-cell-location">(.*?)<\/div>/s', $data, $locs);
                $city = $locs[1];

                preg_match('/<div class="grp-jobfinder-cell-title">(.*?)<\/div>/s', $data, $title);
                $title = $title[1];

                preg_match('/<div>Veröffentlicht:(.*?)<\/div>/s', $data, $publication);
                $publication = trim($publication[1]);


                $id = Vacancies::select('id')->where([ [ 'job_id', $jobid ], [ 'website_id', $website->id ] ])->first();
                if( !empty($id) ) {
                    continue;
                }

                $job_url = 'https://www.bmwgroup.jobs/de/de/jobfinder/job-description.'.$jobid.'.html';
                $this->vacancies = new Vacancies();
                $this->vacancies->website_id = intval($website->id);
                $this->vacancies->job_id = $jobid;
                $this->vacancies->location = $city;
                $this->vacancies->job_title = $title;
                $this->vacancies->city = $city;
                $this->vacancies->job_url = $job_url;
                $this->vacancies->opening_date = date("Y-m-d", strtotime($publication));

                $args =  array(
                    'url' => $job_url,
                    'method' => 'GET',
                );
                $html = $this->do_curl($args);

                preg_match('/Anstellungsart:(.*?)</s', $html, $contracttype);
                $contracttype = isset($contracttype[1]) ? $contracttype[1] : '';
                $this->vacancies->contract_type = trim($contracttype);

                preg_match('/Arbeitszeit:(.*?)</s', $html, $jobtype);
                $jobtype = isset($jobtype[1]) ? $jobtype[1] : '';
                $this->vacancies->job_type = trim($jobtype);

                preg_match('/<div class="job-description-label">Arbeitsbereich:<\/div>(.*?)<\/div>/s', $html, $jobcat);
                $jobcat = isset($jobcat[1]) ? str_replace('<div class="job-description-item">','',$jobcat[1]) : '';
                $this->vacancies->job_category = trim($jobcat);


                preg_match('/<div class="job-description-textpart ">(.*?)<\/div>\s*<\/div>/s', $html, $descr);
                $descr = $descr[1];
                $this->vacancies->job_description = trim($descr);

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
