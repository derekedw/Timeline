<?php

require_once('Job.php');

class TlnData {
	
	public function __construct(&$db) {
		$this->db = $db;
		$this->maxInsert = 2501;
		$this->page = array('param_name' => 'page',
							'size' => 50,
							'current' => 0,
							'paged' => true);
		date_default_timezone_set("GMT");
	}
	function get_row_count() {
		return $this->row_count;		
	}
	function set_row_count($n) {
		$this->row_count = $n;
	}
	function create_import() {
		$starttime = time();
		$sql = 'CREATE TABLE tln_import (
			tln_import_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,	
			date date NOT NULL,
			tick time NOT NULL,
			timezone VARCHAR(16) NOT NULL,
			MACB CHAR(4) NOT NULL,
			source VARCHAR(25) NOT NULL,
			sourcetype VARCHAR(25) NOT NULL,
			type VARCHAR(50) NOT NULL,
			user VARCHAR(25) NOT NULL,
			host VARCHAR(25) NOT NULL,
			short VARCHAR(1000) NOT NULL,
			description VARCHAR(1000) NOT NULL,
			version VARCHAR(7) NOT NULL,
			filename VARCHAR(510) NOT NULL,
			inode VARCHAR(25) NOT NULL,
			notes VARCHAR(255) NOT NULL,
			format VARCHAR(50) NOT NULL,
			extra  VARCHAR(255) NOT NULL,
			concurrency BIGINT UNSIGNED NOT NULL,
			PRIMARY KEY (tln_import_id)
		)';
		if ($this->db->query($sql) == TRUE) {
			$endtime = time();
			print 'Created \'tln_import\' table in ' . gmdate('H:i:s', $endtime - $starttime) . "<br />";
		} else {
			print ('Error creating \'tln_import\' table: ' . $this->db->error . "<br />");
			return false;
		}
		return true;
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
			print ('Error creating \'tln_date\' table: ' . $this->db->error . "<br />");
			return false;
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
			print ('Error creating \'tln_time\' table: ' . $this->db->error . "<br />");
			return false;
		}
		return true;
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
			print ('Error creating \'tln_version\' table: ' . $this->db->error . "<br />");
			return false;
		}
		return true;
	}
	
	function create_source() {
		$starttime = time();
		$sql = 'CREATE TABLE tln_source (
			tln_source_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			source VARCHAR(25) NOT NULL,
			sourcetype VARCHAR(25) NOT NULL,
			M CHAR(1) NOT NULL default \'.\',
			A CHAR(1) NOT NULL default \'.\',
			C CHAR(1) NOT NULL default \'.\',
			B CHAR(1) NOT NULL default \'.\',
			PRIMARY KEY (tln_source_id),
			UNIQUE (source, sourcetype, M, A, C, B)
		)';
		if ($this->db->query($sql) == TRUE) {
			$endtime = time();
			print 'Created \'tln_source\' table in ' . gmdate('H:i:s', $endtime - $starttime) . "<br />";
		} else {
			print ('Error creating \'tln_source\' table: ' . $this->db->error . "<br />");
			return false;
		}
		return true;
	}
	function create_fact() {
		$starttime = time();
		$sql = 'CREATE TABLE tln_fact (
			tln_fact_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			tln_date_id BIGINT UNSIGNED NOT NULL,
			tln_time_id BIGINT UNSIGNED NOT NULL,
	    	tln_source_id BIGINT UNSIGNED NOT NULL,
	    	count BIGINT UNSIGNED NOT NULL,
			user VARCHAR(25) NOT NULL,
			host VARCHAR(25) NOT NULL,
			short VARCHAR(1000) NOT NULL,
			description VARCHAR(1000) NOT NULL,
			version VARCHAR(7) NOT NULL,
			filename VARCHAR(1000) NOT NULL,
			inode VARCHAR(25) NOT NULL,
			notes VARCHAR(255) NOT NULL,
			format VARCHAR(50) NOT NULL,
			extra  VARCHAR(255) NOT NULL,
			PRIMARY KEY (tln_fact_id),		
			UNIQUE KEY tln_fact_uniq(tln_date_id, tln_time_id, tln_source_id, description(255), filename(255), inode),
			FOREIGN KEY (tln_date_id) REFERENCES tln_date(tln_date_id),
			FOREIGN KEY (tln_time_id) REFERENCES tln_time(tln_time_id),
			FOREIGN KEY (tln_source_id) REFERENCES tln_source(tln_source_id)
		)';
		if ($this->db->query($sql) == TRUE) {
			$endtime = time();
			print 'Created \'tln_fact\' table in ' . gmdate('H:i:s', $endtime - $starttime) . "<br />";
		} else {
			print ('Error creating \'tln_fact\' table: ' . $this->db->error . "<br />");
			return false;
		}
		return true;
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
					print ('Error filling table: ' . $this->db->error . "<br />");
					return false;
				}
				$count += $this->db->affected_rows;
				$rows = array();
			}
		}
		if (! $this->db->query($sql . implode(",\n", $rows))) {
			print ('Error filling table: ' . $this->db->error . "<br />");
			return false;
		}
		$endtime = time();
		$count += $this->db->affected_rows;
		print $count . ' rows added to \'tln_date\' in ' . gmdate('H:i:s', $endtime - $starttime) . "<br />";
		return true;
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
					print('Error filling table: ' . $this->db->error . "<br />");
					return false;
				}
				$count += $this->db->affected_rows;
				$rows = array();
			}
		}
		if (! $this->db->query($sql . implode(",\n", $rows))) {
			print ('Error filling table: ' . $this->db->error . "<br />");
			return false;
		}
		$endtime = time();
		$count += $this->db->affected_rows;
		print $count . ' rows added to \'tln_time\' in ' . gmdate('H:i:s', $endtime - $starttime) . "<br />";
		return true;
	}
	
	function fill_version() {
		$starttime = time();
		$sql = 'insert into tln_version (version) values(1)';
		if (! $this->db->query($sql)) {
			print ('Error filling table: ' . $this->db->error . "<br />");
			return false;
		}
		$endtime = time();
		print $this->db->affected_rows . ' rows added to \'tln_version\' in ' . gmdate('H:i:s', $endtime - $starttime) . "<br />";
		return true;
	}
	private function fill_source() {
		$starttime = time();
		$sql = 'insert ignore into tln_source (source, sourcetype, M, A, C, B)
				select distinct source, sourcetype, mid(macb,1,1) as M, \'.\' as A, \'.\' as C, \'.\' as B
    			from tln_import
    			where macb like \'M___\' 
    			union
    			select distinct source, sourcetype, \'.\' as M, mid(macb,2,1) as A, \'.\' as C, \'.\' as B
    			from tln_import
    			where macb like \'_A__\' 
    			union
    			select distinct source, sourcetype, \'.\' as M, \'.\' as A, mid(macb,3,1) as C, \'.\' as B
    			from tln_import
    			where macb like \'__C_\' 
    			union
    			select distinct source, sourcetype, \'.\' as M, \'.\' as A, \'.\' as C, mid(macb,4,1) as B
    			from tln_import
    			where macb like \'___B\'';
		if (! $this->db->query($sql)) {
			print ('Error filling table: ' . $this->db->error . "\n");
			return false;
		}
		$endtime = time();
		print $this->db->affected_rows . ' rows added to \'tln_source\' in ' . gmdate('H:i:s', $endtime - $starttime) . "\n";
		return true;
	}
	private function fill_fact() {
		$starttime = time();
		$sql = 'insert ignore into tln_fact(tln_date_id, tln_time_id, tln_source_id, count, user, host, short, description, version, filename, inode, notes, format, extra)
    			select d.tln_date_id, t.tln_time_id, s.tln_source_id, count(*), i.user, i.host, i.short, i.description, i.version, i.filename, i.inode, i.notes, i.format, i.extra
    			from tln_date d, tln_time t, tln_source s, tln_import i
    			where d.date = i.date and t.tick = i.tick and s.source = i.source and s.sourcetype = i.sourcetype and s.M = \'M\' and i.macb like \'M___\'
    			group by d.tln_date_id, t.tln_time_id, s.tln_source_id, i.macb, substring(i.description,255), substring(i.filename,255), i.inode
    			union
    			select d.tln_date_id, t.tln_time_id, s.tln_source_id, count(*), i.user, i.host, i.short, i.description, i.version, i.filename, i.inode, i.notes, i.format, i.extra
    			from tln_date d, tln_time t, tln_source s, tln_import i
    			where d.date = i.date and t.tick = i.tick and s.source = i.source and s.sourcetype = i.sourcetype and s.A = \'A\' and i.macb like \'_A__\'
    			group by d.tln_date_id, t.tln_time_id, s.tln_source_id, i.macb, substring(i.description,255), substring(i.filename,255), i.inode
    			union
    			select d.tln_date_id, t.tln_time_id, s.tln_source_id, count(*), i.user, i.host, i.short, i.description, i.version, i.filename, i.inode, i.notes, i.format, i.extra
    			from tln_date d, tln_time t, tln_source s, tln_import i
    			where d.date = i.date and t.tick = i.tick and s.source = i.source and s.sourcetype = i.sourcetype and s.C = \'C\' and i.macb like \'__C_\'
    			group by d.tln_date_id, t.tln_time_id, s.tln_source_id, i.macb, substring(i.description,255), substring(i.filename,255), i.inode
    			union
    			select d.tln_date_id, t.tln_time_id, s.tln_source_id, count(*), i.user, i.host, i.short, i.description, i.version, i.filename, i.inode, i.notes, i.format, i.extra
    			from tln_date d, tln_time t, tln_source s, tln_import i
    			where d.date = i.date and t.tick = i.tick and s.source = i.source and s.sourcetype = i.sourcetype and s.B = \'B\' and i.macb like \'___B\'
    			group by d.tln_date_id, t.tln_time_id, s.tln_source_id, i.macb, substring(i.description,255), substring(i.filename,255), i.inode';
		if (! $this->db->query($sql)) {
			print('Error filling table: ' . $this->db->error . "\n");
			return false;
		}
		$endtime = time();
		print $this->db->affected_rows . ' rows added to \'tln_fact\' in ' . gmdate('H:i:s', $endtime - $starttime) . "\n";
		return true;
	}
	private function fill_import($concurrency, &$text) {
		$starttime = time();
		$inserted = 0;
		$skipped = 0;
		$dup = 0;
		$count = 0;
		$rows = array();
		$sql = "insert into tln_import (date,tick,timezone,MACB,source,sourcetype,type,user,host,short,description,version,filename,inode,notes,format,extra,concurrency) values\n";
		foreach(preg_split('/\n/', $text) as $input) {
			if (! $this->validate_content($input)) {
				print "Invalid entry: '" . $input . "'\n";
				$skipped++;
				continue;
			}
			$elements = explode(',', trim($input));
			if ((count($elements)) != 17) {
				// discard this row as a duplicate.  Alternatively, you could:  array_splice($elements, 6, 2);
				$dup++;
				continue;
			}
			// date,tick,timezone,MACB,source,sourcetype,type,user,host,short,desc,version,filename,inode,notes,format,extra
			$rows[] = '(\'' . implode('\', \'', array(	
				gmdate('Y-m-d', strtotime($elements[0])),						// date
				gmdate('H:i:s', strtotime($elements[1])),						// tick 
				$elements[2], 													// timezone
				$elements[3], 													// MACB
				mysqli_real_escape_string($this->db, $elements[4]),				// source
				mysqli_real_escape_string($this->db, $elements[5]),				// sourcetype
				mysqli_real_escape_string($this->db, $elements[6]),				// type
				mysqli_real_escape_string($this->db, $elements[7]),				// user
				mysqli_real_escape_string($this->db, $elements[8]),				// host
				mysqli_real_escape_string($this->db, $elements[9]),				// short
				mysqli_real_escape_string($this->db, $elements[10]),			// desc
				mysqli_real_escape_string($this->db, $elements[11]),			// version
				mysqli_real_escape_string($this->db, $elements[12]),			// filename
				mysqli_real_escape_string($this->db, $elements[13]),			// inode
				mysqli_real_escape_string($this->db, $elements[14]),			// notes
				mysqli_real_escape_string($this->db, $elements[15]),			// format
				mysqli_real_escape_string($this->db, $elements[16]))) . '\', ' .	// extra
				$concurrency . ')';												// concurrency	
			$count++;
			if (0 == ($count % $this->maxInsert)) {
				if (! $this->db->query($sql . implode(",\n", $rows))) {
					print 'Error filling \'tln_import\' table: ' . $this->db->error . "\n";
					return false;
				}
				$inserted += $this->db->affected_rows;
				// reset the counters
				$count = 0;
				$rows = array();
			}
		}
		if (! $this->db->query($sql . implode(",\n", $rows))) {
			print 'Error filling \'tln_import\' table: ' . $this->db->error . "\n";
			return false;
		}
		$inserted += $this->db->affected_rows;
		$endtime = time();
		print $inserted . ' import rows inserted, ' . $skipped . ' skipped, ' . $dup . ' duplicates in ' . gmdate('H:i:s', $endtime - $starttime) . "\n";
		return true;
	}
	function import(&$text) {
		$job = Job::get_new();
		// START TRANSACTION
		if ($this->begin()) {
			// Insert the uploaded text in the database
			if ($this->fill_import($job->getId(), $text)) {
				// Insert the sources
				if ($this->fill_source()) {
					// Insert the fact table rows
					if ($this->fill_fact()) {
						// Empty the imported text 
						// if ($this->empty_import($job->getId())) {
							// COMMIT
							if ($this->commit($job->getId())) {
								return true;		
							 }
						// }
					}	
				} 
			}
			// If any error occurred, roll back the transaction
			// ROLLBACK
			$this->rollback($job->getId());
		} 
	}
	private function empty_import($concurrency) {
		$starttime = time();
		$inserted = 0;
		$delete = 'delete from tln_import
					where concurrency = ' . $concurrency;
		if (! $this->db->query($delete)) {
			print 'Error emptying \'tln_import\' table: ' . $this->db->error . "\n";
			return false;
		}
		$inserted = $this->db->affected_rows;
		$endtime = time();
		print $inserted . ' tln_import rows deleted in ' . gmdate('H:i:s', $endtime - $starttime) . "\n";
		return true;
	}
	function get_detail_view($params){
		$starttime = time();
		$sql = 'select d.tln_date_id, t.tln_time_id, sum(count) as count, 
					d.date, t.tick, s.source, s.sourcetype, 
					s.m, s.a, s.c, s.b,	f.user, f.host, f.short, f.description, 
					f.version, f.filename, f.inode, f.notes, f.format, f.extra
    			from tln_date d inner join (
    				tln_time t inner join (
    					tln_source s inner join tln_fact f on s.tln_source_id = f.tln_source_id
    				) on t.tln_time_id = f.tln_time_id
    			) on d.tln_date_id = f.tln_date_id
    			where ' . $this->get_where($params) . '
    			group by d.tln_date_id desc, t.tln_time_id desc, s.tln_source_id, f.description, f.filename, f.inode
    			order by d.tln_date_id desc, t.tln_time_id desc, s.tln_source_id, f.description, f.filename, f.inode';
		$result = array();
		if ($stmt = $this->db->prepare($sql)) {
			$stmt->execute();
			$stmt->store_result();
			$this->set_row_count($stmt->num_rows);
			$stmt->bind_result($tln_date_id, $tln_time_id, $count, 
					$date, $tick, $source, $sourcetype, $m, $a, $c, $b, 
					$user, $host, $short, $description, $version, $filename,
					$inode, $notes, $format, $extra);
			$macb = array();
			$keys = array();
			while ($stmt->fetch()) {
				if (( ! isset($oldkeys)) || 
					($tln_date_id == $oldkeys[0] && 
					 $tln_time_id == $oldkeys[1] && 
					 $sourcetype == $oldkeys[2] &&
					 $description == $oldkeys[3] &&
					 $filename == $oldkeys[4] &&
					 $inode == $oldkeys[5])) 
				{
					$this->columns($macb, $m, $a, $c, $b, $count, $params);  
					$oldkeys = array($tln_date_id, $tln_time_id, $sourcetype, $description, $filename, $inode, $date, $tick);
					$oldrow = array($macb, array($count, gmdate("m/d/Y", strtotime($date)), $tick), array($source, $sourcetype,
						$user, $host, $short, $description, $version, $filename,
						$inode, $notes, $format, $extra));
				} else {
					$result[] = $oldrow;
					$this->columns($macb, $m, $a, $c, $b, $count, $params);
					$oldkeys = array($tln_date_id, $tln_time_id, $sourcetype, $description, $filename, $inode, $date, $tick);
					$oldrow = array($macb, array($count, gmdate("m/d/Y", strtotime($date)), $tick), array($source, $sourcetype,
						$user, $host, $short, $description, $version, $filename,
						$inode, $notes, $format, $extra));
				}
			}
			$result[] = $oldrow;
			$stmt->free_result();
		}
		return $result;
	}
	function get_where($params) {
		$result = array();
		if (array_key_exists('year', $params)) {
			$result[] = 'd.year=' . $params['year'];
		}
		if (array_key_exists('month', $params)) {
			$result[] = 'd.month=' . $params['month'];
		}
		if (array_key_exists('source', $params)) {
			$result[] = 's.sourcetype=\'' . $params['source'] . '\'';
		}
		if (array_key_exists('macb', $params)) {
			switch ($params['macb']) {
				case 'M':
					$result[] = 's.m=\'M\'';
					break;
				case 'A':
					$result[] = 's.a=\'A\'';
					break;
				case 'C':
					$result[] = 's.c=\'C\'';
					break;
				case 'B':
					$result[] = 's.b=\'B\'';
					break;
				default:
					;				
			}
		}
		return implode(' and ', $result);
	}
	function get_view() {
		$starttime = time();
		$sql = 'select d.year, d.month, s.sourcetype, s.M, s.A, s.C, s.B, sum(f.count) as items
    			from tln_date d inner join (
    			tln_source s inner join tln_fact f on s.tln_source_id = f.tln_source_id
    			) on d.tln_date_id = f.tln_date_id
    			group by d.year, d.month, s.sourcetype, s.M, s.A, s.C, s.B
    			order by d.year desc, d.month desc, s.sourcetype, s.M, s.A, s.C, s.B';
		$result = array();
		if ($stmt = $this->db->prepare($sql)) {
			$stmt->execute();
			$stmt->store_result();
			$this->set_row_count($stmt->num_rows);
			$stmt->bind_result($year, $month, $source, $m, $a, $c, $b, $items);
			$columns = array();
			$sourcerows = array();
			while ($stmt->fetch()) {
				if (( ! isset($oldyear)) || $year == $oldyear) {
					$params['year'] = $year;
					if (( ! isset($oldmonth)) || $month == $oldmonth) {
						$params['month'] = $month;
						if (( ! isset($oldsource)) || $source == $oldsource) {
							$params['source'] = $source;
							$this->columns($columns, $m, $a, $c, $b, $items, $params);
							$oldsource = $source;
						} else {
							$sourcerows[] = $this->sourcerows($columns, $oldsource, $params);
							$columns = array();
							$params['source'] = $source;
							$this->columns($columns, $m, $a, $c, $b, $items, $params);
							$oldsource = $source;
						}
						$oldmonth = $month;
					} else {
						$sourcerows[] = $this->sourcerows($columns, $oldsource, $params);
						unset($params['source']);
						$result[] = $this->daterows($sourcerows, $year, $oldmonth, $params);
						$columns = array();
						$sourcerows = array();
						$params['source'] = $source;
						$params['month'] = $month;
						$this->columns($columns, $m, $a, $c, $b, $items, $params);
						$oldsource = $source;
						$oldmonth = $month;
					}
					$oldyear = $year;
				} else {
					$sourcerows[] = $this->sourcerows($columns, $oldsource, $params);
					unset($params['source']);
					$result[]  = $this->daterows($sourcerows, $oldyear, $oldmonth, $params);
					$columns = array();
					$sourcerows = array();
					$params['source'] = $source;
					$params['month'] = $month;
					$params['year'] = $year;
					$this->columns($columns, $m, $a, $c, $b, $items, $params);
					$oldsource = $source;
					$oldmonth = $month;
					$oldyear = $year;
				}
			}
			$sourcerows[] = $this->sourcerows($columns, $oldsource, $params);
			unset($params['source']);
			$result[]  = $this->daterows($sourcerows, $oldyear, $oldmonth, $params);
			$stmt->free_result();
		}
		return $result;
	}
	private function daterows (&$rows, $year, $month, $params) {
		return array($month . '/' . $year, $rows, $params);
	}
	private function sourcerows (&$columns, $source, $params) { 
		return array($source, $columns, $params);
	}
	private function columns(&$columns, $m, $a, $c, $b, $items, $params) {
		$myparams = $params; 
		$myparams['view'] = 'detail';
		if ($m == 'M') {
			$myparams['macb'] = 'M';
			$columns['M'] = array($items, $myparams);
		}
		if ($a == 'A') {
			$myparams['macb'] = 'A';
			$columns['A'] = array($items, $myparams);
		}
		if ($c == 'C') {
			$myparams['macb'] = 'C';
			$columns['C'] = array($items, $myparams);
		}
		if ($b == 'B') {
			$myparams['macb'] = 'B';
			$columns['B'] = array($items, $myparams);
		}
		unset($myparams['macb']);
		$columns['total'] = array($columns['total'][0] + $items, $myparams);
		return $columns;
	}
	function get_macb($macb) {
		$result = '';
		if (array_key_exists('M', $macb))
			$result .= 'M';
		else 
			$result .= '.';
		if (array_key_exists('A', $macb))
			$result .= 'A';
		else 
			$result .= '.';
		if (array_key_exists('C', $macb)) 
			$result .= 'C';
		else 
			$result .= '.';
		if (array_key_exists('B', $macb)) 
			$result .= 'B';
		else 
			$result .= '.';
		return $result;
	} 
	function get_proportional() {
		$job = Job::get_new();
		$starttime = time();
		$sql = 'select s.source, d.year, d.month, count(f.count) as items
    			from tln_date d left join (
    			tln_source s inner join tln_fact f on s.tln_source_id = f.tln_source_id
    			) on d.tln_date_id = f.tln_date_id
    			where d.year > 1980 and d.year <= 2011
    			group by s.source, d.year, d.month
    			order by s.source, d.year, d.month';
		if ($stmt = $this->db->prepare($sql)) {
			$stmt->execute();
			$stmt->bind_result($source, $year, $month, $count, $items);
			if ($stmt->fetch()) {
				$job->setTask($task);
				$job->setStatus($status);
				$job->setPercent($percent);
				$job->setSaved(true);
			} else {
				Job::save($this->db, $job);
			}
		}
	}
	private function begin() {
		$insert = 'start transaction';
		if (! $this->db->query($insert)) {
			print 'Error starting a transaction: ' . $this->db->error . "\n";
			return false;
		}
		return true;
	}
	private function rollback($concurrency) {
		$inserted = 0;
		$insert = 'rollback';
		if (! $this->db->query($insert)) {
			print 'Rollback error ' . $this->db->error . "\n";
			return false;
		}
		$inserted = $this->db->affected_rows;
		$endtime = time();
		print 'Rollback! ' . $inserted . ' rows affected in ' . gmdate('H:i:s', $endtime - $concurrency) . "\n";
		return true;
	}
	private function commit($concurrency) {
		$inserted = 0;
		$insert = 'commit';
		if (! $this->db->query($insert)) {
			print 'Commit error ' . $this->db->error . "\n";
			return false;
		}
		$inserted = $this->db->affected_rows;
		$endtime = time();
		print 'Commit! ' . $inserted . ' rows affected in ' . gmdate('H:i:s', $endtime - $concurrency) . "\n";
		return true;
	}
	private function validate_content($input) {
		if (! preg_match('@^\d+/\d+/\d+,\d+:\d+:\d+,\w+,[M.][A.][C.][B.],[^,]+,[^,]+,[^,]+,[^,]+,[^,]+,[^,]+,[^,]+,[^,]+,[^,]+,[^,]+,[^,]+,[^,]+,[^,]+$@', $input, $match)) 
			return false;
		return true;
	}
	function h2q($my_params) {
		foreach ($my_params as $k => $v) {
				$query[] = $k . '=' . $v;
		}
		return '?' . implode('&', $query);		
	}
}
?>