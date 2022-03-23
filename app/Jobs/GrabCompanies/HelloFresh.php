<?php

namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;

class HelloFresh extends DataScan {

    public $vacancies;

    /**
     * Basf constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_hellofresh($website);
    }


    public function custom_curl() {
        // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://careers.hellofresh.com/widgets');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{"lang":"en_global","deviceType":"desktop","country":"global","pageName":"search-results","ddoKey":"refineSearch","sortBy":"","subsearch":"","from":0,"jobs":true,"counts":true,"all_fields":["category","country","city","type"],"size":1000,"clearAll":false,"jdsource":"facets","isSliderEnable":false,"pageId":"page14","siteType":"external","keywords":"","global":true,"selected_fields":{"country":["Germany"]}}"');
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        $headers = array();
        $headers[] = 'Authority: careers.hellofresh.com';
        $headers[] = 'Pragma: no-cache';
        $headers[] = 'Cache-Control: no-cache';
        $headers[] = 'Sec-Ch-Ua: ';
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'X-Csrf-Token: 14ea800a59c343bf87471e140ab98422';
        $headers[] = 'Sec-Ch-Ua-Mobile: ?0';
        $headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36';
        $headers[] = 'Sec-Ch-Ua-Platform: Windows""';
        $headers[] = 'Accept: */*';
        $headers[] = 'Origin: https://careers.hellofresh.com';
        $headers[] = 'Sec-Fetch-Site: same-origin';
        $headers[] = 'Sec-Fetch-Mode: cors';
        $headers[] = 'Sec-Fetch-Dest: empty';
        $headers[] = 'Referer: https://careers.hellofresh.com/global/en/search-results?from=10&s=1';
        $headers[] = 'Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7,hy;q=0.6';
        $headers[] = 'Cookie: VISITED_LANG=en; VISITED_COUNTRY=global; Per_UniqueID=17e2bd1334b314-100200-1634-17e2bd1334c9b9; _ga=GA1.1.1744202580.1641412638; PLAY_SESSION=eyJhbGciOiJIUzI1NiJ9.eyJkYXRhIjp7IkpTRVNTSU9OSUQiOiI3YmEzMDQ0Ni0zNDlkLTRiNmQtODU0MC0xYWQ5YjliYjY3ZjUifSwibmJmIjoxNjQxNDgyMDEyLCJpYXQiOjE2NDE0ODIwMTJ9.d4i7qK1E5-HQVNXRozzmFMObIl0JD0l1YW2bqtoUL14; JSESSIONID=7ba30446-349d-4b6d-8540-1ad9b9bb67f5; __cf_bm=kFamBnCwkVoQ70VWfQs9DkAYrC5GTHYBATiZDce.azU-1641482014-0-AT5fetzbh2HCNpo8E0GMEyFNFaW48TW2mH3RRg3MFL1D2TsEcF8Dq8VsUKz67vOf04Wk5Ysw0PP4QrQs3n6ZpVavZSrTsnrwOQQ1PMM80tyooPU9DqrnyRaMOXF7KhVLZZD6opm+nx3OpnLi9aP93wWiiufipt3mEO7HWRwi7UXL2qzIZSMOE/nB6NkxXjcKvA==; ext_trk=pjid%3D7ba30446-349d-4b6d-8540-1ad9b9bb67f5&uid%3D17e2bd1334b314-100200-1634-17e2bd1334c9b9&p_lang%3Den_global&refNum%3DHELLGLOBAL; _ga_XH9D2HTHKM=GS1.1.1641482013.3.1.1641482267.0';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }

    public function scan_hellofresh($website)
    {
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);

        $data = $this->custom_curl();
        $data = json_decode($data, 1);
        if( empty($data) ) {
            $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
            return;
        }

        if( isset($data['refineSearch']) && isset($data['refineSearch']['data']) && isset($data['refineSearch']['data']['jobs'])) {
            $jobs = $data['refineSearch']['data']['jobs'];
        } else {
            $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
            return;
        }

        foreach ( $jobs as $job ) {
            try {

                $job_id = $job['jobId'];
                $job_url = 'https://careers.hellofresh.com/global/en/job/'.$job_id;
                $id = Vacancies::select('id')->where([ [ 'job_id', $job_id ], [ 'website_id', $website->id ] ])->value('id');
                if ( !empty($id) ) {
                    Vacancies::where('id', $id)->update(['updated_at' => date("Y-m-d H:i:s")]);
                    continue;
                }
                $this->vacancies = new Vacancies();
                $this->vacancies->website_id = intval($website->id);
                $this->vacancies->job_id = $job_id;
                $this->vacancies->job_title = $job['title'];

                $jobType = explode('/',$job['type']);
                $this->vacancies->job_type = trim($jobType[0]);

                $this->vacancies->contract_type = isset($jobType[0]) ? trim($jobType[1]) : '';
                $this->vacancies->job_url = $job_url;
                $this->vacancies->job_category = $job['category'];

                $this->vacancies->opening_date = date("Y-m-d", strtotime($job['postedDate']));

                $this->vacancies->location = $job["city"];
                $this->vacancies->city = $job["city"];

                $this->vacancies->job_description = $job['descriptionTeaser'];

                $this->vacancies->save();

            } catch (\Throwable $e) {
                $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' : ' . $e->getMessage(), 'error' => 1]);

                continue;
            }

        }

        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan ended']);
        Log::info($website->company.' Scan Ended');
    }

}
