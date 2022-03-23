<?php

namespace App\Jobs\GrabCompanies;

use App\Jobs\DataScan;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;

class Eon extends DataScan {

    public $vacancies;

    /**
     * Basf constructor.
     *
     */
    public function __construct( $default, $website )
    {
        $this->vacancies = new Vacancies();
        $this->scan_eon($website);
    }

    public function scan_eon($website)
    {
        $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Scan Started', 'error' => 0]);

        $args =  array(
            'url' => $website->url,
            'method' => 'GET',

        );

        $xml = $this->do_curl($args);
        $xml = simplexml_load_string($xml,'SimpleXMLElement', LIBXML_NOCDATA);
        $datas = array();
        $i = 0;
        if( isset($xml->channel) && isset($xml->channel->item) ) {
            $items = $xml->channel->item;
        } else {
            $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' Request data was empty.', 'error' => 1]);
            return;
        }
        foreach ($items as $item)
        {
            try {
                $namespaces = $item->getNameSpaces(true);
                $g = $item->children($namespaces['g']);
                $location = (string) $g->location;

                $loc = explode(', ', $location);
                $country = 'DE';
                $city = '';
                if( isset($loc[1]) ) {
                    $country = $loc[1];
                    $city = $loc[0];
                }
                if( $country != 'DE') continue;


                $datas[$i]['title'] = (string) $item->title;
                $datas[$i]['description'] = (string) $item->description;
                $datas[$i]['id'] = (string) $g->id;
                $datas[$i]['city'] = $city;
                $datas[$i]['link'] = (string) $item->link;
                $i++;

            } catch (\Throwable $e) {
                unset($datas[$i]);
                $this->vacancies->create_log(['website_id' => $website->id, 'log' => $website->company.' : ' . $e->getMessage(), 'error' => 1]);

                continue;
            }
        }
        foreach ( $datas as $data ) {
            try {
                $job_id = $data['id'];
                $id = Vacancies::select('id')->where([ [ 'job_id', $job_id ], [ 'website_id', $website->id ] ])->value('id');
                if ( !empty($id) ) {
                    Vacancies::where('id', $id)->update(['updated_at' => date("Y-m-d H:i:s")]);
                    continue;
                }
                $this->vacancies = new Vacancies();
                $this->vacancies->website_id      = intval($website->id);
                $this->vacancies->job_id          = $job_id;
                $this->vacancies->location        = $data['city'];
                $this->vacancies->city            = $data['city'];
                $this->vacancies->job_title       = $data['title'];
                $this->vacancies->job_description = $data['description'];
                $this->vacancies->job_url         = $data['link'];

                $args =  array(
                    'url' => $data['link'],
                    'method' => 'GET',
                );

                $html = $this->do_curl($args);

                preg_match('/Beschäftigungsgrad:(.*?)<br/s', $html, $matches);
                $this->vacancies->job_type = isset($matches[1]) ? trim($matches[1]) : NULL;

                preg_match('/Funktionsbereich:(.*?)<br/s', $html, $matches);
                $this->vacancies->job_category = isset($matches[1]) ? trim($matches[1]) : NULL;

                preg_match('/Beschäftigungsart:(.*?)<br/s', $html, $matches);
                $this->vacancies->contract_type = isset($matches[1]) ? trim($matches[1]) : NULL;

                if( empty($this->vacancies->job_type) && empty($this->vacancies->job_category) && empty($this->vacancies->contract_type) ) {
                    $html = trim(preg_replace('/\s\s+/', '', $html));
                    $html = str_replace('> <', '><', $html);

                    preg_match('/\s*\K<p style="text-align:center">(.*?)<\/p>/s', $html, $matches);

                    preg_match('/\s*\K<p style="text-align:center">(.*?)<\/p>/s', $html, $matches);
                    if( isset($matches[1]) ) {
                        $match = strip_tags($matches[1]);
                        if( strpos($match,'|') > 0 ) {
                            $match = explode('|', $match);
                        } else {
                            $match = explode('I', $match);
                        }
                        $this->vacancies->job_type = isset($match[2]) ? $match[2] : NULL;
                        $this->vacancies->contract_type = isset($match[1]) ? $match[1] : NULL;
                    }
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
