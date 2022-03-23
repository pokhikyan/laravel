<?php

namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;

class Deutschebank extends DataScan {

    public $vacancies;

    /**
     * Basf constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_deutschebank($website);
    }

    public function scan_deutschebank($website)
    {
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);

        $url = $website->url;
        $args =  array(
                        'url' => $url,
                        'method' => 'POST',
                        'header_type' => array(
                            'Accept: application/json',
                            'Content-Type: application/json'
                        ),
                        'postfield' => '{"LanguageCode":"en","SearchParameters":{"FirstItem":1,"CountItem":1000,"MatchedObjectDescriptor":["Facet:ProfessionCategory","Facet:ProfessionCategoryName","Facet:UserArea.ProDivision","Facet:Profession","Facet:PositionLocation.CountrySubDivision","Facet:PositionOfferingType.Code","Facet:PositionSchedule.Code","Facet:PositionLocation.City","Facet:PositionLocation.Country","Facet:JobCategory.Code","Facet:CareerLevel.Code","Facet:PositionHiringYear","PositionID","PositionTitle","PositionFormattedDescription.Content","PositionURI","ScoreThreshold","OrganizationName","PositionFormattedDescription.Content","PositionLocation.CountryName","PositionLocation.CountrySubDivisionName","PositionLocation.CityName","PositionLocation.Longitude","PositionLocation.Latitude","PositionIndustry.Name","JobCategory.Name","CareerLevel.Name","PositionSchedule.Name","PositionOfferingType.Name","PublicationStartDate","UserArea.GradEduInstCountry","PositionImport","PositionHiringYear","ProfessionCategoryName"],"Sort":[{"Criterion":"PublicationStartDate","Direction":"DESC"}]},"SearchCriteria":[{"CriterionName":"PositionLocation.Country","CriterionValue":46},{"CriterionName":"PositionFormattedDescription.Content"}]}'
                    );

        $response = $this->do_curl($args);
        $datas = json_decode($response,1);
        if( isset($datas['SearchResult']) && isset($datas['SearchResult']['SearchResultItems'])) {
            $datas = $datas['SearchResult']['SearchResultItems'];
        } else {
            $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
            return;
        }
        foreach ( $datas as $data ) {
            try {
                $id = Vacancies::select('id')->where([ [ 'job_id', $data['MatchedObjectDescriptor']['PositionID'] ], [ 'website_id', $website->id ] ])->value('id');
                if ( !empty($id) ) {
                    Vacancies::where('id', $id)->update(['updated_at' => date("Y-m-d H:i:s")]);
                    continue;
                }

                $this->vacancies = new Vacancies();
                $this->vacancies->website_id      = intval($website->id);
                $this->vacancies->job_id          = $data['MatchedObjectDescriptor']['PositionID'];
                $this->vacancies->location        = $data['MatchedObjectDescriptor']['PositionLocation'][0]['CityName'];
                $this->vacancies->job_title       = $data['MatchedObjectDescriptor']['PositionTitle'];
                $this->vacancies->city            = $data['MatchedObjectDescriptor']['PositionLocation'][0]['CityName'];
                $this->vacancies->job_category    = $data['MatchedObjectDescriptor']['ProfessionCategoryName'];
                /*            $this->vacancies->job_url         = $data['MatchedObjectDescriptor']['PositionURI'];*/
                $this->vacancies->opening_date    = date("Y-m-d", strtotime($data['MatchedObjectDescriptor']['PublicationStartDate']));


                $job_url = "https://api-deutschebank.beesite.de/jobhtml/".$data['MatchedObjectDescriptor']['PositionID'];
                $args =  array(
                    'url' => $job_url,
                    'method' => 'GET',
                    'header_type' => array(
                        'Accept: application/json',
                        'Content-Type: application/json'
                    ),
                );
                $job_response = $this->do_curl($args);
                $job_data = json_decode($job_response,1);
                $html = $job_data['html'];

                $this->vacancies->job_url = str_replace("/apply", "", $job_data['apply_uri']);
                preg_match('/<strong>Full\/Part-Time: <\/strong>(.*?)<\/td>/s', $html, $matches);
                if( isset($matches[1]) ) {
                    $this->vacancies->job_type = str_replace("-", " ", $matches[1]);
                }

                preg_match('/<strong>Regular\/Temporary: <\/strong>(.*?)<\/td>/s', $html, $matches);
                if( isset($matches[1]) ) {
                    $this->vacancies->contract_type = str_replace("-", " ", $matches[1]);
                }

                preg_match('/<h2>Position Overview<\/h2>(.*?)<\/div>/s', $html, $matches);
                if( isset($matches[1]) ) {
                    $this->vacancies->job_description = str_replace("-", " ", $matches[1]);
                }

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
