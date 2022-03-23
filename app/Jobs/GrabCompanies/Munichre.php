<?php

namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;

class Munichre extends DataScan {

    public $vacancies;

    /**
     * Adidas constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_munichre($website);
    }

    public function scan_munichre($website)
    {
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);

        $url = $website->url;


        for ( $i = 1; $i < 20; $i++ ) {
            $args = array(
                'url' => $url.'&page='.$i,
                'method' => 'GET',
            );
            $html = $this->do_curl($args);
            preg_match_all('/<div class="card"(.*?)card-->/s', $html, $matches);
            $datas = $matches[0];
            if( empty($datas) && $i == 1 ) {
                $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
                break;
            }

            foreach ( $datas as $data ) {
                try {

                    preg_match('/id="job-(.*?)">/s', $data, $job_id);
                    $job_id = $job_id[1];

                    $id = Vacancies::select('id')->where([
                                                             [ 'job_id', $job_id ],
                                                             [ 'website_id', $website->id ]
                                                         ])->value('id');
                    if ( !empty($id) ) {
                        Vacancies::where('id', $id)->update(['updated_at' => date("Y-m-d H:i:s")]);
                        continue;
                    }
                    $this->vacancies = new Vacancies();
                    $this->vacancies->website_id = intval($website->id);
                    $this->vacancies->job_id = $job_id;

                    preg_match('/<span class="card-header__job-place">(.*?)<\/span>/s', $data, $city);
                    $city = $city[1];
                    $this->vacancies->location = $city;

                    preg_match('/<span class="card-header__job-position">(.*?)<\/span>/s', $data, $title);
                    $this->vacancies->job_title = $title[1];
                    $this->vacancies->city = $city;

                    preg_match('/Art der Stelle<\/h5>\s*<span>(.*?)<\/span>/s', $data, $job_type);
                    $this->vacancies->job_type = isset($job_type[1]) ? $job_type[1] : '';

                    preg_match_all('/<a target="_blank" href="(.*?)"/s', $data, $link);
                    $link = $link[1][1];
                    $this->vacancies->job_url = $link;

                    preg_match('/<h5>Einstiegslevel<\/h5>(.*?)<\/span>/s', $data, $job_level);
                    $job_level = isset($job_level[1]) ? strip_tags($job_level[1]) : '';
                    $this->vacancies->job_level = trim($job_level);

                    preg_match('/<h5>Berufsfeld<\/h5>(.*?)<\/span>/s', $data, $job_category);
                    $job_category = isset($job_category[1]) ? strip_tags($job_category[1]) : '';
                    $this->vacancies->job_category = trim($job_category);

                    preg_match('/<p class="text-right">Veröffentlicht am (.*?)<\/p>/s', $data, $opening_date);
                    $this->vacancies->opening_date = date("Y-m-d", strtotime($opening_date[1]));

                    $args = array(
                        'url' => $link,
                        'method' => 'GET',
                    );
                    $html = $this->do_curl($args);
                    preg_match('/<div class="content">(.*?)<\/div>\s*<\/div>/s', $html, $description);
                    $this->vacancies->job_description = $description[1];

                    //$this->vacancies->job_category = $job_category;

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
