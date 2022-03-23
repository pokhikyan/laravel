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

class Sap extends DataScan {

    public $vacancies;

    /**
     * Bmw constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_sap($website);
    }

    public function scan_sap($website)
    {
                $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);


        $url = $website->url;
        $links = array();
        for( $i = 0; $i < 1000; $i+=25 ) {
            $args = array(
                'url' => $url.'&startrow='.$i,
                'method' => 'GET',
            );
            $html = $this->do_curl($args);
            preg_match_all('/<a href="(.*?)" class="jobTitle\-link">/', $html, $matches);
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
                    'url' => 'https://jobs.sap.com'.$link,
                    'method' => 'GET',
                );
                $html = $this->do_curl($args);

                /* job_id */
                preg_match('/data-careersite-propertyid="facility">(.*?)<\/span>/s', $html, $matches);
                $job_id = isset($matches[1]) ? trim($matches[1]) : '';

                $id = Vacancies::select('id')->where([ [ 'job_id', $job_id ], [ 'website_id', $website->id ] ])->value('id');
                if ( !empty($id) ) {
                    Vacancies::where('id', $id)->update(['updated_at' => date("Y-m-d H:i:s")]);
                    continue;
                }
                $this->vacancies = new Vacancies();
                $this->vacancies->website_id = intval($website->id);
                /* Title */
                preg_match('/data-careersite-propertyid="title">(.*?)<\/span>/s', $html, $matches);
                $this->vacancies->job_title = isset($matches[1]) ? $matches[1] : '';

                $this->vacancies->job_id = $job_id;

                /* Title */
                preg_match('/<span class="jobGeoLocation">(.*?)<\/span>/s', $html, $matches);
                $location = isset($matches[1]) ? $matches[1] : '';
                $location = explode(',', $location);
                $this->vacancies->location = trim($location[0]);
                $this->vacancies->city = trim($location[0]);

                $this->vacancies->job_url = 'https://jobs.sap.com'.$link;

                /* Date */
                preg_match('/data-careersite-propertyid="date">(.*?)<\/span>/s', $html, $matches);
                $this->vacancies->opening_date = isset($matches[1]) ? date('Y-m-d', strtotime($matches[1])) : '';

                /* Category */
                preg_match('/data-careersite-propertyid="department">(.*?)<\/span>/s', $html, $matches);
                $this->vacancies->job_category = isset($matches[1]) ? trim($matches[1]) : '';

                /* Job level */
                preg_match('/data-careersite-propertyid="customfield3">(.*?)<\/span>/s', $html, $matches);
                $this->vacancies->job_level = isset($matches[1]) ? trim($matches[1]) : '';

                /* Job type */
                preg_match('/data-careersite-propertyid="shifttype">(.*?)<\/span>/s', $html, $matches);
                if( isset($matches[1]) ) {
                    $type = explode(" ", $matches[1]);
                    $this->vacancies->contract_type = $type[0];

                    unset($type[0]);
                    $this->vacancies->job_type = trim(implode(" ", $type));
                }

                /* Description */
                preg_match('/data-careersite-propertyid="description">(.*?)<\/span>\s*<\/div>/s', $html, $matches);
                $this->vacancies->job_description = isset($matches[1]) ? trim($matches[1]) : '';


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
