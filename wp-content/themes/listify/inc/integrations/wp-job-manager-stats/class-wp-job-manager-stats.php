<?php
/**
 * WP Job Manager Stats
 */

class Listify_WP_Job_Manager_Stats extends listify_Integration {

	public function __construct() {
		$this->integration = 'wp-job-manager-stats';
		parent::__construct();
	}

	public function setup_actions() {
		
	}

}

$GLOBALS['listify_job_manager_stats'] = new Listify_WP_Job_Manager_Stats();
