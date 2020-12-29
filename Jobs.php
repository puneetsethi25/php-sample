<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once dirname(dirname(dirname(__FILE__))) . "/vendor/autoload.php";

use Mailgun\Mailgun;
use Twilio\Rest\Client;
use \Curl\MultiCurl;

class Jobs extends CI_Controller
{
    

    public function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        if (!$this->input->is_cli_request()) {
            show_error('You don\'t have permission for this action');
            return;
        }
        $this->load->model('campaigns', 'Campaigns');
    }

    public function executionTestPIDSync($size = 40, $ppid = false){
        $pid = getmypid();
        $path = dirname(dirname(dirname(__FILE__))) . "/application/async/pid.logs";
        $startTime = microtime(true);
        for ($i = 0; $i < $size; $i++) {
            if ($i == ($size - 1) || $i == 0) {
                file_put_contents($path, "INFO[QUEUE]: PID#: {$pid} TIME#: " . microtime(true) . " VALUE#{$i}" . PHP_EOL, FILE_APPEND | LOCK_EX) . "\n";
            }
        }
    }

    public function index($limit = false, $offset = 0){}

    /**
     * all common functions goes here
     */
    public function format_email($subject, $message, $reciever)
    {
        // common functions goes here
        // ...
    }
}
