<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once dirname(dirname(dirname(__FILE__))) . "/vendor/autoload.php";
require_once APPPATH . "controllers/Jobs.php";

class SendEmailCampaignsJob extends Jobs
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->input->is_cli_request()) {
            show_error('You don\'t have permission for this action');
            return;
        }
        //specific models classes loaded here
        $this->load->model('report', 'Report');
    }

    /**
     * Default method that will be executed. The arguments will be passed via command line.
     * Based on the args [$limit, $offset], a query will be made to the db and fetched a chunk of records. 
     * and will be parallely executed along with the other set of records via Parallel Processing.
     *
     * @param integer $limit limit of records to be fetched.
     * @param integer $offset offset to be passed in the query.
     *
     * @return void
     */
    public function index($limit = false, $offset = 0)
    {
        // fetching all the records for exeutions but now with an Offset & Limit values set by Main Cron Job
        $campaigns = $this->Campaigns->getCampaignAllbyStatus(['sending', 'upcoming'], 'email', 0, '', array('processed_on' => 1), $limit, $offset);
        $this->_process($campaigns);
    }

    /**
     * Main execution code for the process that will run eventually.
     *
     * @param array $campaigns Array of records that requires to be processed.
     *
     * @return void
     */
    private function _process(array $campaigns = [])
    {
        $i = 0;
        if (!empty($campaigns)) {
            foreach ($campaigns as $value) {
                //execution code goes here
                //....

            }
        }

    }
}
