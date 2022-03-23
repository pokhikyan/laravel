<?php

namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;

class Daimler extends DataScan {

    public $vacancies;

    /**
     * Basf constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_daimler($website);
    }

    public function scan_daimler($website)
    {
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);

        $url = $website->url;
        $args =  array(
            'url' => $url,
            'method' => 'GET',
            'header_type' => array(
                'Accept: application/json',
                'Content-Type: application/json'
            ),
            'postfield' => '{"LanguageCode":"EN","ScoreThreshold":80,"SearchParameters":{"MatchedObjectDescriptor":["PositionID","PositionTitle","PositionURI","LogoURI","OrganizationName","Organization","Organization.MemberCode","ParentOrganization","ParentOrganizationName","PositionLocation.CityName","PositionLocation.CountryName","PositionLocation.CountryCode","PositionLocation.Longitude","PositionLocation.Latitude","PositionIndustry.Name","JobCategory.Name","JobCategory.Code","CareerLevel.Name","CareerLevel.Code","Facet:ParentOrganization","Facet:ParentOrganizationGenesisID","Facet:ParentOrganizationName","PublicationStartDate","ParentOrganizationGenesisID"],"Sort":[{"Criterion":"PublicationStartDate","Direction":"DESC"}],"FirstItem":1,"CountItem":5000},"SearchCriteria":[{"CriterionName":"PublicationLanguage.Code","CriterionValue":["EN"]},{"CriterionName":"PublicationChannel.Code","CriterionValue":["12"]}]}',
        );

        $response = $this->do_curl( $args );
        $datas = json_decode($response,1);

        if( isset($datas['SearchResult']) && isset($datas['SearchResult']['SearchResultItems'])) {
            $datas = $datas['SearchResult']['SearchResultItems'];
        } else {
            $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
            return;
        }


        foreach ( $datas as $data ) {
            try {
                $data = $data['MatchedObjectDescriptor'];
                if( !isset($data['PositionLocation'][0]['CountryCode']) || (isset($data['PositionLocation'][0]['CountryCode']) && $data['PositionLocation'][0]['CountryCode'] !== 'DE') ) {
                    continue;
                }
                $id = Vacancies::select('id')->where([ [ 'job_id', $data['PositionID'] ], [ 'website_id', $website->id ] ])->value('id');
                if( !empty($id) ) {
                    Vacancies::where('id', $id)->update(['updated_at' => date("Y-m-d H:i:s")]);
                    continue;
                }

                $this->vacancies = new Vacancies();
                $this->vacancies->website_id      = intval($website->id);
                $this->vacancies->job_id          = $data['PositionID'];
                $this->vacancies->location        = $data['PositionLocation'][0]['CityName'];
                $this->vacancies->job_title       = $data['PositionTitle'];
                $this->vacancies->city            = $data['PositionLocation'][0]['CityName'];
                $this->vacancies->job_type        = Null;
                $this->vacancies->job_category    = $data['CareerLevel'][0]['Name'];

                $this->vacancies->job_url         = $data['PositionURI'];
                $this->vacancies->opening_date    = date("Y-m-d", strtotime($data['PublicationStartDate']));

                $args =  array(
                    'url' => $data['PositionURI'],
                );
                $html = $this->do_curl( $args );

                /* Get location from HTML string */
                preg_match_all('/<div class="article-copy body-template-container">(.*?)<\/div>/s', $html, $matches);

                $this->vacancies->job_description = isset($matches[0][0]) ? $matches[0][0] : Null;
                $this->vacancies->qualification = isset($matches[0][1]) ? $matches[0][1] : Null;

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
