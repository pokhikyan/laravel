<?php

namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;

class Basf extends DataScan {

    public $vacancies;

    /**
     * Basf constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_basf($website);
    }

    public function scan_basf($website)
    {
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);

        $url = $website->url;
        $args =  array(
            'url' => $url,
            'method' => 'POST',
        );

        $xml = $this->do_curl($args);
        $xml = $this->xmlToArray($xml);

        $datas = $xml['url'];
        if( empty($datas) ) {
            Log::info($website->company.' Sitemap undefined');
            $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);

            return;
        }
        foreach ( $datas as $data ) {
          try {
              if( empty($data["loc"]) ) continue;

              $job_url = $data["loc"];
              $id = Vacancies::select('id')->where([ [ 'job_url', $job_url ], [ 'website_id', $website->id ] ])->value('id');
              if ( !empty($id) ) {
                  Vacancies::where('id', $id)->update(['updated_at' => date("Y-m-d H:i:s")]);
                  continue;
              }
              /*            $html = $this->do_curl($job_url);*/
              $html = file_get_contents($job_url);
              if( $html === false ) {
                continue;
              }
              /* Get location from HTML string */
              preg_match('/<span class="jobGeoLocation">(.*?)<\/span>/s', $html, $matches);
              $location = explode(',', $matches[1]);
              $location = array_map('trim', $location);

              if( !in_array("DE", $location) ) {
                continue;
              }

              /* Get jobID from HTML string */
              preg_match('/"internalId":"(.*?)","email"/s', $html, $matches);
              $jobID = isset($matches[1]) ? trim($matches[1]) : NULL;

              /* Get location from HTML string */
              preg_match('/<title>(.*?)<\/title>/s', $html, $matches);
              $title = isset($matches[1]) ? trim($matches[1]) : NULL;

              /* Get Job type from HTML string */
              preg_match('/<span\s+.*?data-careersite-propertyid="shifttype">(.*?)<\/span>/s', $html, $matches);
              $jobtype = isset($matches[1]) ? trim($matches[1]) : 'Azubi';

              /* Get Job Category from HTML string */
              preg_match('/<span\s+.*?data-careersite-propertyid="dept">(.*?)<\/span>/s', $html, $matches);
              $jobcategory = isset($matches[1]) ? trim($matches[1]) : 'Ausbildung';

              /* Get Job Description from HTML string */
              preg_match('/<span\s+.*?data-careersite-propertyid="description">(.*?)<\/span>/s', $html, $matches);
              $jobdescription = isset($matches[1]) ? trim($matches[1]) : NULL;
              $this->vacancies = new Vacancies();

              $this->vacancies->website_id      = intval($website->id);
              $this->vacancies->job_id          = $jobID;
              $this->vacancies->location        = $location[0];
              $this->vacancies->job_title       = $title;
              $this->vacancies->city            = $location[0];
              $this->vacancies->job_type        = $jobtype;
              $this->vacancies->job_category    = $jobcategory;
              $this->vacancies->job_description = $jobdescription;
              $this->vacancies->job_url         = $job_url;
              $this->vacancies->opening_date    = date("Y-m-d");
              $this->vacancies->save();

          } catch (\Throwable $e) {
              $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' : ' . $e->getMessage(), 'error' => 1]);

          }
        }
        Log::info($website->company.' Scan Ended');
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan ended']);
    }

}
