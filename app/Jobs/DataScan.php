<?php

namespace App\Jobs;

use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Websites;
use App\Models\Vacancies;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Jobs\GrabCompanies\Adidas;
use App\Jobs\GrabCompanies\Allianz;
use App\Jobs\GrabCompanies\Basf;
use App\Jobs\GrabCompanies\Continental;


use App\Http\Controllers;


/**
 * Class DataScan
 * @package App\Jobs
 *          php artisan schedule:test
 */
class DataScan extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var string
     */
    private $logSuffix;
    /**
     * @var array|null
     */
    private $pluginActiveInstallsDataFromApi;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var $default
     */
    protected $default;

    public $model;

    /**
     * @var Websites
     */
    protected $website;

    public $companyName = '';


    /**
     * DataScan constructor.
     *
     */
    public function __construct(Websites $website)
    {
        $this->default = array(
            'website_id' => 1,
            'job_id' => 1,
            'location' => '',
            'job_title' => '',
            'city' => '',
            'job_type' => 'Full time',
            'job_category' => '',
            'job_description' => '',
            'job_url' => '',
            'qualification' => '',
            'opening_date' => date('Y-m-d'),
            'deadline' => '',
            'about_us' => '',
        );
        $this->website = $website;
        $this->companyName = $website->company;
        $this->queue = 'insert_vacancies_data';
    }

    public function handle()
    {
        $this->client = new Client((['http_errors' => false]));
        $this->scan();
    }


    public function scan()
    {
        $company = $this->website->company;
        $className = "App\Jobs\GrabCompanies\\".$company;
        $ob = new $className($this->default, $this->website);
    }

/*    public function do_curl( $url, $data = array(), $method = 'POST', $header_type = '' )*/
    /*
      array(
       'url' => $url,
       'header_type' => '',
       'method' => 'POST',
       'postfield' => ''
      )
      */
    public function do_curl( $args = array() )
    {
        $HTTPHEADER = array();
        if( isset($args['header_type']) ) {
            $HTTPHEADER = $args['header_type'];
        }
        $curl = curl_init();

        $method = isset($args['method']) ? $args['method'] : 'POST';
        curl_setopt($curl, CURLOPT_URL, $args['url']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $HTTPHEADER);
        if( isset($args['postfield']) ) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $args['postfield']);
        }

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    public function xmlToArray($xmlstring){

        $xml = simplexml_load_string($xmlstring);
/*        foreach ($xml->channel->item as $item)
        {
            $xml->channel->item->id = $item->children('g', true)->id;
            $xml->channel->item->locaton = $item->children('g', true)->location;
            $xml->channel->item->description = $item->children('g', true)->description;
        }
*/
        $json = json_encode($xml);
        $array = json_decode($json,TRUE);

        return $array;

    }

}
