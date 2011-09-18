<?php

class TlnData {
	
	public function __construct(&$db) {
		$this->db = $db;
	}
	
	function create_import() {
		$starttime = time();
		$sql = 'CREATE TABLE tln_import (
			tln_import_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,	
			date CHAR(10) NOT NULL,
			tick CHAR(8) NOT NULL,
			timezone VARCHAR(16) NOT NULL,
			MACB CHAR(4) NOT NULL,
			source VARCHAR(25) NOT NULL,
			sourcetype VARCHAR(25) NOT NULL,
			type VARCHAR(50) NOT NULL,
			user VARCHAR(25) NOT NULL,
			host VARCHAR(25) NOT NULL,
			short VARCHAR(255) NOT NULL,
			description VARCHAR(255) NOT NULL,
			version VARCHAR(7) NOT NULL,
			filename VARCHAR(255) NOT NULL,
			inode VARCHAR(25) NOT NULL,
			notes VARCHAR(255) NOT NULL,
			format VARCHAR(50) NOT NULL,
			extra  VARCHAR(255) NOT NULL,
			PRIMARY KEY (tln_import_id)
		)';
		if ($this->db->query($sql) == TRUE) {
			$endtime = time();
			print 'Created \'tln_import\' table in ' . gmdate('H:i:s', $endtime - $starttime) . "<br />";
		} else {
			die('Error creating \'tln_import\' table: ' . $this->db->error . "<br />");
		}
	}
	
	function create_date() {
		$starttime = time();
		$sql = 'CREATE TABLE tln_date (
					tln_date_id BIGINT UNSIGNED DEFAULT 0 NOT NULL,
					date DATE DEFAULT \'0000-00-00\' NOT NULL,
					year YEAR DEFAULT 0000 NOT NULL,
					month TINYINT UNSIGNED DEFAULT 0 NOT NULL,
					day TINYINT UNSIGNED DEFAULT 0 NOT NULL,
					PRIMARY KEY (tln_date_id),
					INDEX(date)
				)';
		if ($this->db->query($sql) == TRUE) {
			$endtime = time();
			print 'Created \'tln_date\' table in ' . gmdate('H:i:s', $endtime - $starttime) . "<br />";
		} else {
			die('Error creating \'tln_date\' table: ' . $this->db->error . "<br />");
		}
	}
	function create_time() {
		$starttime = time();
		$sql = 'CREATE TABLE tln_time (
					tln_time_id BIGINT UNSIGNED DEFAULT 0 NOT NULL,
					tick TIME DEFAULT \'00:00:00\' NOT NULL,
					hour TIME DEFAULT \'00:00:00\' NOT NULL,
					tick360 TIME DEFAULT \'00:00:00\' NOT NULL,
					minute TIME DEFAULT \'00:00:00\' NOT NULL,
					tick6 TIME DEFAULT \'00:00:00\' NOT NULL,
					PRIMARY KEY (tln_time_id),
					INDEX(tick)
				)';
		if ($this->db->query($sql) == TRUE) {
			$endtime = time();
			print 'Created \'tln_time\' table in ' . gmdate('H:i:s', $endtime - $starttime) . "<br />";
		} else {
			die('Error creating \'tln_time\' table: ' . $this->db->error . "<br />");
		}
	}
	function create_version() {
		$starttime = time();
		$sql = 'CREATE TABLE tln_version (
			version TINYINT UNSIGNED DEFAULT 1 NOT NULL,
			last_modified TIMESTAMP,
			PRIMARY KEY (version)
		)';
		if ($this->db->query($sql) == TRUE) {
			$endtime = time();
			print 'Created \'tln_version\' table in ' . gmdate('H:i:s', $endtime - $starttime) . "<br />";
		} else {
			die('Error creating \'tln_version\' table: ' . $this->db->error . "<br />");
		}
	}
	
	function create_source() {
		$starttime = time();
		$sql = 'CREATE TABLE tln_source (
			tln_source_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			source VARCHAR(25) NOT NULL,
			sourcetype VARCHAR(25) NOT NULL,
			PRIMARY KEY (tln_source_id),
			UNIQUE (source, sourcetype)
		)';
		if ($this->db->query($sql) == TRUE) {
			$endtime = time();
			print 'Created \'tln_source\' table in ' . gmdate('H:i:s', $endtime - $starttime) . "<br />";
		} else {
			die('Error creating \'tln_source\' table: ' . $this->db->error . "<br />");
		}
	}
	function create_fact() {
		$starttime = time();
		$sql = 'CREATE TABLE tln_fact (
			tln_fact_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			tln_date_id BIGINT UNSIGNED NOT NULL,
			tln_time_id BIGINT UNSIGNED NOT NULL,
	    	tln_source_id BIGINT UNSIGNED NOT NULL,
	    	MACB CHAR(4) NOT NULL,
	    	type VARCHAR(50) NOT NULL,
			user VARCHAR(25) NOT NULL,
			host VARCHAR(25) NOT NULL,
			short VARCHAR(255) NOT NULL,
			description VARCHAR(255) NOT NULL,
			version VARCHAR(7) NOT NULL,
			filename VARCHAR(255) NOT NULL,
			inode VARCHAR(25) NOT NULL,
			notes VARCHAR(255) NOT NULL,
			format VARCHAR(50) NOT NULL,
			extra  VARCHAR(255) NOT NULL,
			PRIMARY KEY (tln_fact_id),		
			UNIQUE KEY tln_fact_uniq(tln_date_id, tln_time_id, tln_source_id),
			FOREIGN KEY (tln_date_id) REFERENCES tln_date(tln_date_id),
			FOREIGN KEY (tln_time_id) REFERENCES tln_time(tln_time_id),
			FOREIGN KEY (tln_source_id) REFERENCES tln_source(tln_source_id)
		)';
		if ($this->db->query($sql) == TRUE) {
			$endtime = time();
			print 'Created \'tln_fact\' table in ' . gmdate('H:i:s', $endtime - $starttime) . "<br />";
		} else {
			die('Error creating \'tln_fact\' table: ' . $this->db->error . "<br />");
		}
	}
	
	function fill_date() {
		$count = 0;
		$rows = array();
		$starttime = time();
		$sql = "insert into tln_date (tln_date_id, date, year, month, day) values\n";
		$aday = (24 * 60 * 60);
		for ($i = 0; $i < (365 * 100); $i++) {
			$timestamp = $i * $aday;
			$rows[] = ('(' . implode(', ', array($i,
				'\'' . gmdate('Y-m-d', $timestamp) . '\'',
				'YEAR(\'' . gmdate('Y-m-d', $timestamp) . '\')',
				'MONTH(\'' . gmdate('Y-m-d', $timestamp) . '\')',
				'DAY(\'' . gmdate('Y-m-d', $timestamp) . '\')')) . ')');
			if (0 == ($i % 3600)) {
				if (! $this->db->query($sql . implode(",\n", $rows))) {
					die('Error filling table: ' . $this->db->error . "<br />");
				}
				$count += $this->db->affected_rows;
				$rows = array();
			}
		}
		if (! $this->db->query($sql . implode(",\n", $rows))) {
			die('Error filling table: ' . $this->db->error . "<br />");
		}
		$endtime = time();
		$count += $this->db->affected_rows;
		print $count . ' rows added to \'tln_date\' in ' . gmdate('H:i:s', $endtime - $starttime) . "<br />";
	}
	
	function fill_time() {
		$count = 0;
		$rows = array();
		$starttime = time();
		$sql = "insert into tln_time (tln_time_id, tick, hour, tick360, minute, tick6) values\n";
		for ($i = 0; $i < (24 * 60 * 60); $i++) {
			$rows[] = ('(' . $i . ', \'' . implode('\', \'', array(gmdate('H:i:s', $i),
			gmdate('H:i:s', intval($i/3600)*3600), gmdate('H:i:s', intval($i/360)*360),
			gmdate('H:i:s', intval($i/60)*60), gmdate('H:i:s', intval($i/6)*6))) . '\')');
			if (0 == ($i % 3600)) {
				if (! $this->db->query($sql . implode(",\n", $rows))) {
					die('Error filling table: ' . $this->db->error . "<br />");
				}
				$count += $this->db->affected_rows;
				$rows = array();
			}
		}
		if (! $this->db->query($sql . implode(",\n", $rows))) {
			die('Error filling table: ' . $this->db->error . "<br />");
		}
		$endtime = time();
		$count += $this->db->affected_rows;
		print $count . ' rows added to \'tln_time\' in ' . gmdate('H:i:s', $endtime - $starttime) . "<br />";
	}
	
	function fill_version($db) {
		$starttime = time();
		$sql = 'insert into tln_version (version) values(1)';
		if (! $db->query($sql)) {
			die('Error filling table: ' . $db->error . "<br />");
		}
		$endtime = time();
		print $db->affected_rows . ' rows added to \'tln_version\' in ' . gmdate('H:i:s', $endtime - $starttime) . "<br />";
	}
}
?>