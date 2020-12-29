<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once dirname(dirname(dirname(__FILE__))) . "/vendor/autoload.php";

use Amp\Parallel\Worker;
use Amp\Promise;

class Cron extends CI_Controller
{

    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     *         http://example.com/index.php/welcome
     *    - or -
     *         http://example.com/index.php/welcome/index
     *    - or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see https://codeigniter.com/user_guide/general/urls.html
     */
    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        $this->load->model('campaigns', 'Campaigns');
    }

    public function index()
    {
        $this->load->view('welcome_message');
    }

    public function testPIDWithoutParallel()
    {
        $pid = getmypid();
        shell_exec("php index.php jobs/executionTestPIDSync 99999999 {$pid}");
    }

    public function testPIDWithParallel()
    {
        $pid = getmypid();
        for ($i = 0; $i < 4; $i++) {
            $command = "php index.php jobs/executionTestPIDSync 25000000 {$pid} > /dev/null 2>/dev/null &";
            $promises[$i] = Worker\enqueueCallable('shell_exec', $command);
        }
        Promise\wait(Promise\all($promises));
    }

    /**
     * Gets the count of the records that needs to be processed.
     * publicly invokable callback.
     *
     * @param integer $count Total Count of records.
     * @param string $controller Controller name that will be called in command. If not passed default Job controller will be called
     * @param string $method Method to be called of a controller.
     * @param string $args Optional arguments that will be passed to the command.
     *
     * @return void
     */
    public function prepareForBackground(integer $count, string $controller = 'Jobs', string $method = 'index', string $args = "")
    {
        if ($count > 0) {
            $values = range(0, $count - 1);
            $parts = ceil(count($values) / 4);
            $allchunks = array_chunk($values, $parts);
            $offset = 0;
            foreach ($allchunks as $k => $val) {
                $limit = count($val);
                $cmd = "php index.php {$controller}/{$method} {$limit} " . ($offset > 0 ? $offset : '') . (!empty($args) ? $args : '') . " > /dev/null 2>/dev/null &";
                $promises[$k] = Worker\enqueueCallable('shell_exec', $cmd);
                $offset += count($val);
            }
            Promise\wait(Promise\all($promises));
        }
    }

    /**
     * Usage |
     *
     * Get count of all records first that needs to exectued.
     * send the count to method - prepareForBackground() for
     * process and excution.
     *
     */
    public function sendEmailCampaignsJob()
    {
        $campaigns = $this->Campaigns->getCampaignAllbyStatus(['sending', 'upcoming'], 'sms', 0, '', array('processed_on' => 1));
        $this->prepareForBackground(count($campaigns), 'SendEmailCampaignsJob');
    }

}
