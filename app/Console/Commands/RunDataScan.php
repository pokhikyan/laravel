<?php
/**
 * Created by PhpStorm.
 * User: Vanush
 * Date: 31.03.2017
 * Time: 15:31
 */

namespace App\Console\Commands;


use App\Jobs\DataScan;
use App\Models\Websites;
use DB;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Log;


class RunDataScan extends Command
{
    use DispatchesJobs;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run_data_scan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gets data from wordpress.org/plugins.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if( isset($_GET['company']) && isset($_GET['action']) && $_GET['action'] == 'run_company_scan') {
            $websites = Websites::all()->where('id', intval($_GET['company']));
        } else {
            $websites = Websites::all()->where('active', 1);
        }
        $this->line('Data Scan started');
        Log::info('Data Scan Started');

       // $websites = Websites::all()->where('id', 1);

        foreach ($websites as $i => $website) {
           dispatch(new DataScan($website));
        }

        $this->line('Data Scan scheduled');
        Log::info('Data Scan scheduled');
    }

}
