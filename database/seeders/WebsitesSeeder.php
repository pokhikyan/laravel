<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Websites;

class WebsitesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Websites::create(
            [
            'company' => 'Adidas',
            'url' => 'https://careers.adidas-group.com/jobs?brand=&team=&type=&keywords=&location=[{"country":"Germany"}]&sort=&locale=en',
            ],
            [
            'company' => 'Allianz',
            'url' => 'https://careers.allianz.com/sitemap.xml',
            ],
            [
            'company' => 'Basf',
            'url' => 'https://basf.jobs/sitemap.xml',
            ],
            [
            'company' => 'Continental',
            'url' => 'https://api.continental-jobs.com/search/?data={"LanguageCode":"EN","SearchParameters":{"FirstItem":1,"CountItem":5000,"Sort":[{"Criterion":"PublicationStartDate","Direction":"DESC"}],"MatchedObjectDescriptor":["ID","PositionID","PositionTitle","PositionURI","PositionLocation.CountryName","PositionLocation.CityName","PositionLocation.Longitude","PositionLocation.Latitude","PositionIndustry.Name","JobCategory.Name","PublicationStartDate","VacancyDivision"]},"SearchCriteria":[{"CriterionName":"PositionLocation.Country","CriterionValue":["17"]},{"CriterionName":"PublicationLanguage.Code","CriterionValue":["EN"]},{"CriterionName":"PublicationChannel.Code","CriterionValue":["12"]}]}',
            ],
            [
            'company' => 'Covestro',
            'url' => 'https://covestro.wd3.myworkdayjobs.com/cov_external/0/refreshFacet/318c8bb6f553100021d223d9780d30be',
            ],
            [
            'company' => 'Daimler',
            'url' => 'https://global-jobboard-api.daimler.com/v3/search',
            ],
            [
            'company' => 'Deliveryhero',
            'url' => 'https://careers.deliveryhero.com/widgets',
            ],
            [
            'company' => 'Deutschebank',
            'url' => 'https://api-deutschebank.beesite.de/search',
            ],
            [
            'company' => 'DeutscheBoerse',
            'url' => 'https://careers.deutsche-boerse.com/sitemap.xml',
            ],
            [
            'company' => 'Dhl',
            'url' => 'https://careers.dhl.com/widgets',
            ],
            [
            'company' => 'Telekom',
            'url' => 'https://www.telekom.com/service/globaljobsearch/en/558550?where=Germany&hits_per_page=2000',
            ],
            [
            'company' => 'DeutscheWohnen',
            'url' => 'https://www.deutsche-wohnen.com/en/about-us/career/our-vacancies',
            ],
            [
            'company' => 'Airbus',
            'url' => 'https://ag.wd3.myworkdayjobs.com/wday/cxs/ag/Airbus/jobs',
            ],
            [
            'company' => 'Eon',
            'url' => 'https://jobs.eon.com/siemap.xml',
            ],
            [
            'company' => 'Fresenius',
            'url' => 'https://karriere.fresenius.de/sitemap.xml',
            ],
            [
            'company' => 'Freseniusmedicalcare',
            'url' => 'https://jobs.freseniusmedicalcare.com/job-search?encodedParameters=7b226c616e6775616765223a22656e2d5553222c226c696d6974223a323030302c226f6666736574223a302c22696e636c7564655f7465726d5f636f756e7473223a747275652c227465726d5f696473223a5b2264363833373665642d656462642d356439352d396535662d643863383837386662623565225d7d',
            ],
            [
            'company' => 'Henkel',
            'url' => 'https://www.xing.com/jobs/api/search?filter.country[]=de&keywords=henkel',
            ],
            [
            'company' => 'Infineon',
            'url' => 'https://www.infineon.com/search/jobs/jobs',
            ],
            [
            'company' => 'Linde',
            'url' => 'https://www.xing.com/jobs/api/search?filter.country[]=de&keywords=linde',
            ],
            [
            'company' => 'Merck',
            'url' => 'https://search.merckgroup.com/v1/search?fjc=Germany&q=&l=en&s=1000&f=0&d=global_german&fc=jobs&o=desc',
            ],
            [
            'company' => 'BMW',
            'url' => 'https://www.bmwgroup.jobs/de/de/jobs/_jcr_content/par/layoutcontainer_copy_565949652/layoutcontainercontent/jobfinder30_copy.jobfinder_table.content.html/&filterSearch=location_DE,location_DE/Dortmund,location_DE/Eisenach,location_DE/Landshut,location_DE/Goettingen,location_DE/Saarbruecken,location_DE/Wackersdorf,location_DE/Krefeld,location_DE/Chemnitz,location_DE/Mannheim,location_DE/Bonn,location_DE/Dingolfing,location_DE/Bremen,location_DE/Regensburg,location_DE/Essen,location_DE/Munich,location_DE/Nuremberg,location_DE/Kassel,location_DE/Dresden,location_DE/Stuttgart,location_DE/Ulm,location_DE/Frankfurt,location_DE/Darmstadt,location_DE/Hamburg,location_DE/Hanover,location_DE/Leipzig,location_DE/Duesseldorf,location_DE/Berlin&rowIndex=0&blockCount=500'
            ],
            [
            'company' => 'Porsche',
            'url' => 'https://api-jobs.porsche.com/search/?data={"LanguageCode":"DE","SearchParameters":{"FirstItem":1,"CountItem":1000,"Sort":[{"Criterion":"PublicationStartDate","Direction":"DESC"}],"MatchedObjectDescriptor":["ID","PositionTitle","PositionURI","PositionLocation.CountryName","PositionLocation.CityName","PositionLocation.Longitude","PositionLocation.Latitude","PositionLocation.PostalCode","PositionLocation.StreetName","PositionLocation.BuildingNumber","PositionLocation.Distance","JobCategory.Name","PublicationStartDate","ParentOrganizationName","ParentOrganization","OrganizationShortName","CareerLevel.Name","JobSector.Name","PositionIndustry.Name","PublicationCode","PublicationChannel.Id"]},"SearchCriteria":[{"CriterionName":"PublicationChannel.Code","CriterionValue":["12"]},{"CriterionName":"PositionLocation.Country","CriterionValue":["46"]}]}'
            ],
            [
            'company' => 'Puma',
            'url' => 'https://about.puma.com/api/PUMA/Feature/JobFinder?filter=loc_a940ba508f954b7d9f65ca4a424b43a4&loadMore=1000',
            ],
            [
            'company' => 'Rwe',
            'url' => 'https://www.rwe.com/api/jobborse/entities/v1',
            ],
            [
            'company' => 'Sap',
            'url' => 'https://jobs.sap.com/search/?q=&locationsearch=de',
            ],
            [
            'company' => 'Sartorius',
            'url' => 'https://www.sartorius.com/ajax/filterlist/en/company-de/career-de/vacancies-job-opportunities-de/332842-332842?locationtaxonomy=332394%23397822',
            ],
            [
            'company' => 'Siemens',
            'url' => 'https://jobs.siemens.com/api/jobs?location=Deutschland&limit=10&sortBy=relevance&descending=false&internal=false',
            ],
            [
            'company' => 'SiemensEnergy',
            'url' => 'https://jobs.siemens-energy.com/api/jobs?location=Deutschland&woe=12&stretchUnit=MILES&stretch=0&sortBy=relevance&descending=false&internal=false',
            ],
            [
            'company' => 'SiemensHealth',
            'url' => 'https://jobs.siemens.com/api/jobs?location=Deutschland&woe=12&stretchUnit=MILES&stretch=0&sortBy=relevance&descending=false&internal=false&brand=Siemens%20Healthineers',
            ],
            [
            'company' => 'Volkswagen',
            'url' => 'https://karriere.volkswagen.de/sap/opu/odata/sap/zaudi_ui_open_srv/JobSet?sap-client=100&sap-language=de&$select=JobID,Posting,Title,PostingAge,Location,HierarchyLevel,ContractType,FunctionalArea,Company,TravelRatio,JobDetailsUrl,ZLanguage&$expand=Location,HierarchyLevel,ContractType,FunctionalArea,Company',
            ],
            [
            'company' => 'Zalando',
            'url' => 'https://jobs-api.corptech.zalan.do/external/search/?q=&filters=%7B%7D&offset=0&limit=2000',
            ],
            [
            'company' => 'Bayer',
            'url' => 'https://karriere.bayer.de/de/jobs-search?field_job_career_level=All&field_job_functional_area=All&field_job_country=2136&field_job_location=All&field_job_division=All&search_api_fulltext=',
            ],
            [
            'company' => 'HeidelbergCement',
            'url' => 'https://www.heidelbergcement.com/de/views/ajax',
            ],
            [
            'company' => 'Munichre',
            'url' => 'https://munichre-jobs.com/api/list/Job?template=MunichRe&sortitem=id&sortdirection=DESC&format=cards&lang=de&widget=0&filter[company.id]=[1%2C2%2C3%2C4%2C5%2C6%2C7%2C8%2C9%2C27%2C29%2C31%2C32%2C33%2C51%2C52%2C53%2C54%2C55%2C56%2C57%2C59%2C60%2C61%2C62%2C63%2C64]&filter[display_language]=de&filter[publication_channel]=careersite&filter[city.id]=[39117]&sort[id]=DESC',
            ],
            [
            'company' => 'Qiagen',
            'url' => 'https://global3.recruitmentplatform.com/fo/rest/jobs?firstResult=0&maxResults=150&sortBy=sJobTitle&sortOrder=asc',
            ],
            [
            'company' => 'Vonovia',
            'url' => 'https://jobs.vonovia.de/Vonovia/search/?q=&locationsearch=germany&searchby=location&d=15',
            ],
            [
            'company' => 'MTU',
            'url' => 'https://www.mtu.de/typo3temp/tx_bgmdvinci/de-list.json?language=de',
            ],
            [
            'company' => 'HelloFresh',
            'url' => 'https://careers.hellofresh.com/widgets',
            ],
            [
            'company' => 'Brenntag',
            'url' => 'https://www.xing.com/jobs/api/search?keywords=brenntag&location=Germany',
            ]
        );
    }
}
