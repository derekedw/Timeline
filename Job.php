<?php
class Job {
	
	private function __construct() {
		$this->jobid = time();
		$this->status = array('status' => 'new', 'percent' => 0, 'task' => '');
		$this->saved = false;
	}
	
	static function create(&$db) {
		$starttime = time();
		$sql = 'CREATE TABLE job (
			job_id BIGINT UNSIGNED NOT NULL,
			task varchar(255) NOT NULL,
			status VARCHAR(10) DEFAULT \'new\' NOT NULL,
			percent TINYINT UNSIGNED DEFAULT 0 NOT NULL,
			last_modified TIMESTAMP NOT NULL,
			PRIMARY KEY (job_id)
		)';
		if ($db->query($sql) == TRUE) {
			$endtime = time();
			print 'Created \'job\' table in ' . gmdate('H:i:s', $endtime - $starttime) . "<br />";
		} else {
			die('Error creating \'job\' table: ' . $db->error . "<br />");
		}
	}
	
	function getId() {
		return $this->jobid;
	}
	
	function setId($jobid) {
		$this->jobid = $jobid;
	}
	
	function isSaved() {
		return $this->saved;
	}
	
	function setSaved($saved) {
		$this->saved = $saved;
	}
	
	function getStatus() {
		return $this->status['status'];
	}
	
	function setStatus($status) {
		$this->status['status'] = $status;
	}
	
	function getPercent() {
		return $this->status['percent'];
	}
	
	function setPercent($percent) {
		$this->status['percent'] = $percent; 
	}
	
	function getTask() {
		return $this->status['task'];
	}
	
	function setTask($task) {
		$this->status['task'] = $task;
	}
	
	function start($task = '') {
		$this->update(0, 100, $task);
		return $this->jobid;
	}
	
	function update($completed, $of = 100, $task = '') {
		if (strlen($task) != 0)
			$this->status['task'] = $task;
		if ($completed >= 0 && $of >= 0 && $completed < $of) {
			$this->status['status'] = 'running'; 
			$this->status['percent'] = intval($completed * 100 / $of);
		} else if ($completed >= 0 && $of >= 0 && $completed >= $of) {
			$this->status['status'] = 'done'; 
			$this->status['percent'] = 100;
		} else {
			// error
		}
		return $this->jobid;
	}
	
	static function get_by_id(&$db, $jid) {
		$job = new Job();
		$job->setId($jid);
		$sql = 'select task, status, percent from job where job_id = ?';
		if ($stmt = $db->prepare($sql)) {
			$stmt->bind_param("s", $jid);
			$stmt->execute();
			$stmt->bind_result($task, $status, $percent);
			if ($stmt->fetch()) {
				$job->setTask($task);
				$job->setStatus($status);
				$job->setPercent($percent);
				$job->setSaved(true);
			} else {
				Job::save($db, $job);
			}
		} 
		return $job;
	}
	
	static function get_new() {
		return new Job();
	}
	
	static function save(&$db, &$job) {
		if ($job->isSaved()) {
			$sql = 'update job 
			        set	task = ?,
					  status = ?,
					  percent = ? 
					where job_id = ?'; 
		} else {
			$sql = 'insert into job (task, status, percent, job_id)
			        values (?, ?, ?, ?)';
		}
		if ($stmt = $db->prepare($sql)) {
			$stmt->bind_param("ssis", $job->status['task'], $job->status['status'], $job->status['percent'], $job->jobid);
			if ($stmt->execute())
				$job->setSaved(true);
		}
	}
}

?>