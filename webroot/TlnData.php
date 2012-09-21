<?php

require_once('functions.php');
require_once('Job.php');

class TlnData {
	
	/**
	 * Constructor of the classes that does all the mysql data import, display and maintenance
	 * issue001 sets a db_version field that can be used to ensure the proper code is in use
	 * @param mysqli object $db
	 */
	public function __construct(&$db) {
		$this->db = $db;
		$this->code_version = 2;
		$this->upgrade_info = array(2 => array(	'Reimport recommended' => 'yes', 
												'Description' => 'The UNIQUE INDEX on the main fact table ' . 
												'causes long pathnames in the source and description fields ' . 
												'to be grouped as duplicates. Data was hidden, ' . 
												'so reimporting the data is strongly recommended.'),
									1 => array(	'Reimport recommended' => 'yes',
												'Description' => 'Initial Tapestry version'));
		$this->maxInsert = 2001;
		$this->page = array('param_name' => 'page',
							'size' => 50,
							'current' => 0,
							'paged' => true);
		$this->datezoom = array(array('name' => 'month',  'datefield' => 'd.month', 'timefield' => '\'.\''),
								array('name' => 'day',    'datefield' => 'd.day', 'timefield' => '\'.\''),
								array('name' => 'hour',   'datefield' => 'd.day', 'timefield' => 't.hour'),
								array('name' => 'minute', 'datefield' => 'd.day', 'timefield' => 't.minute'),
								array('name' => 'second', 'datefield' => 'd.day', 'timefield' => 't.second'));
		$this->datezoom_level = 0;
		date_default_timezone_set("GMT");
		$this->generic_query = array('alter_columns2' =>
				array(	'sql' => 'alter table tln_fact
						add column hash char(40) null',
						'description' => 'altering columns in the \'tln_fact\' table'
				),
				'generate_hash2' =>
				array(	'sql' => 'create temporary table tln_hash as
						select tln_fact_id,
						sha1(concat(short, description, filename)) as hash
						from tln_fact',
						'description' => 'generating hashes of data in the \'tln_fact\' table'
				),
				'fold_hash2' =>
				array(	'sql' => 'update tln_fact, tln_hash
						set tln_fact.hash = tln_hash.hash
						where tln_fact.tln_fact_id = tln_hash.tln_fact_id',
						'description' => 'folding the hashes into the \'tln_fact\' table'
				),
				'set_constraint2' =>
				array(	'sql' => 'alter table tln_fact
						modify column hash char(40) not null,
						add UNIQUE KEY tln_fact_uniq2(tln_date_id, tln_time_id, tln_source_id, inode, hash)',
						'description' => 'setting constraints in the \'tln_fact\' table'
				),
				'remove_constraint2' =>
				array(	'sql' => 'alter table tln_fact
						drop index tln_fact_uniq',
						'description' => 'removing constraints in the \'tln_fact\' table'
				),
				'cleanup2' =>
				array(	'sql' => 'drop table tln_hash',
						'description' => 'clearing temporary table'
				),
				'updatev1tov2' => 
				array(	'sql' => 'update tln_version
								set version = 2',
						'description' => 'updating the database\'s version number'
				)
		);
	}
	/**
	 * The database has a version number, which is a positive integer.
	 * @return the database version with which this code is designed to operate
	 */
	public function get_code_version() {
		return $this->code_version;
	}
	/**
	 * This method notes differences between the code version and the database version
	 * @return true if the code version is greater than the database version
	 */
	public function has_upgrade () {
		$db_version = $this->get_db_version(); 
		return ($db_version > 0 && $db_version < $this->code_version);
	}
	/**
	 * Fetches the database version number by queying the database
	 * @return The database's version number, as stored in tln_version.version
	 */
	public function get_db_version() {
		$sql = 'select version from tln_version';
		if (($stmt = $this->db->prepare($sql))) {
			if ($stmt->execute()) { 
				if ($stmt->bind_result($version)) {
					while ($stmt->fetch())
						;
					return $version;
				}
			}  
		}
		return 0;
	}
	public function has_tables($dbname) {
		$sql = 'select count(*) as \'exists\' from information_schema.tables 
		where table_schema = ? and table_name = \'tln_version\'';
		if (($stmt = $this->db->prepare($sql))) {
			$stmt->bind_param('s', $dbname);
			if ($stmt->execute()) {
				if ($stmt->bind_result($exists)) {
					while ($stmt->fetch())
						;
					return $exists == 1;
				}
			}
		}
		return 0;
	}
	/**
	 * Prints version data for the updates that have yet to be applied to the 
	 * database, as an HTML table
	 * @return an HTML table with information on database updgrades that have 
	 * not been applied
	 */
	public function get_upgrade_info() {
		print '<table>';
		foreach ($this->upgrade_info as $key => $value) {
			foreach ($value as $k => $v) { 
				print '<tr>';
			  	print '<td>' . $key . '</td>';
				print '<td>' . $k . '</td>';
				print '<td>' . $v . '</td>';
			  	print '</tr>';
  			}
		}
		print '</table>';
	}
	private function get_row_count() {	
		return $this->row_count;
	}
	private function set_row_count($n) {
		$this->row_count = $n;
	}
	private function create_import() {
		$starttime = time();
		$sql = 'CREATE TABLE tln_import (
			tln_import_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,	
			date date NOT NULL,
			tick time NOT NULL,
			timezone VARCHAR(16) NOT NULL,
			M CHAR(1) NOT NULL default \'.\',
			A CHAR(1) NOT NULL default \'.\',
			C CHAR(1) NOT NULL default \'.\',
			B CHAR(1) NOT NULL default \'.\',
			source VARCHAR(25) NOT NULL,
			sourcetype VARCHAR(25) NOT NULL,
			type VARCHAR(50) NOT NULL,
			user VARCHAR(50) NOT NULL,
			host VARCHAR(25) NOT NULL,
			short VARCHAR(1000) NOT NULL,
			description VARCHAR(1000) NOT NULL,
			version VARCHAR(7) NOT NULL,
			filename VARCHAR(510) NOT NULL,
			inode VARCHAR(25) NOT NULL,
			notes VARCHAR(255) NOT NULL,
			format VARCHAR(50) NOT NULL,
			extra  VARCHAR(255) NOT NULL,
			tln_concurrency_id BIGINT UNSIGNED NOT NULL,
			PRIMARY KEY (tln_import_id)
		) ENGINE=InnoDB';
		if ($this->db->query($sql) == TRUE) {
			$endtime = time();
			print $this->h1('Created \'tln_import\' table in ' . gmdate('H:i:s', $endtime - $starttime));
			return true;
		} else {
			print $this->h1('Error creating \'tln_import\' table: ' . $this->db->error);
			return false;
		}
	}
	
	private function create_date() {
		$starttime = time();
		$sql = 'CREATE TABLE tln_date (
					tln_date_id BIGINT UNSIGNED DEFAULT 0 NOT NULL,
					date DATE DEFAULT \'0000-00-00\' NOT NULL,
					year VARCHAR(4) NOT NULL,
					month VARCHAR(7) NOT NULL,
					day VARCHAR(10) NOT NULL,
					UNIQUE KEY tln_date_uniq(date, year, month, day),
					PRIMARY KEY (tln_date_id)
				) ENGINE=InnoDB';
		if ($this->db->query($sql) == TRUE) {
			$endtime = time();
			print $this->h1('Created \'tln_date\' table in ' . gmdate('H:i:s', $endtime - $starttime));
			return true;
		} else {
			print $this->h1('Error creating \'tln_date\' table: ' . $this->db->error . "<br />");
			return false;
		}
	}
	function h1($text) {
		return '<p><span style="font-size: 120%;">' . $text . '</span></p>';
	}
	private function create_time() {
		$starttime = time();
		$sql = 'CREATE TABLE tln_time (
					tln_time_id BIGINT UNSIGNED DEFAULT 0 NOT NULL,
					tick TIME DEFAULT \'00:00:00\' NOT NULL,
					hour VARCHAR(2) NOT NULL,
					minute VARCHAR(5) NOT NULL,
					second VARCHAR(8) NOT NULL,
					UNIQUE KEY tln_time_uniq(tick, hour, minute, second),
					PRIMARY KEY (tln_time_id)
				) ENGINE=InnoDB';
		if ($this->db->query($sql) == TRUE) {
			$endtime = time();
			print $this->h1('Created \'tln_time\' table in ' . gmdate('H:i:s', $endtime - $starttime));
			return true;
		} else {
			print $this->h1('Error creating \'tln_time\' table: ' . $this->db->error);
			return false;
		}
		return true;
	}
	private function create_version() {
		$starttime = time();
		$sql = 'CREATE TABLE tln_version (
			version TINYINT UNSIGNED DEFAULT 1 NOT NULL,
			last_modified TIMESTAMP,
			PRIMARY KEY (version)
		) ENGINE=InnoDB';
		if ($this->db->query($sql) == TRUE) {
			$endtime = time();
			print $this->h1('Created \'tln_version\' table in ' . gmdate('H:i:s', $endtime - $starttime));
			return true;
		} else {
			print $this->h1('Error creating \'tln_version\' table: ' . $this->db->error);
			return false;
		}
	}
	
	private function create_source() {
		$starttime = time();
		$sql = 'CREATE TABLE tln_source (
			tln_source_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			tln_concurrency_id BIGINT UNSIGNED NOT NULL,
			source VARCHAR(25) NOT NULL,
			sourcetype VARCHAR(25) NOT NULL,
			type VARCHAR(50) NOT NULL,
			host VARCHAR(25) NOT NULL,
			version VARCHAR(7) NOT NULL,
			format VARCHAR(50) NOT NULL,
			M CHAR(1) NOT NULL default \'.\',
			A CHAR(1) NOT NULL default \'.\',
			C CHAR(1) NOT NULL default \'.\',
			B CHAR(1) NOT NULL default \'.\',
			PRIMARY KEY (tln_source_id),
			UNIQUE (source, sourcetype, type, host, version, format, M, A, C, B)
		) ENGINE=InnoDB';
		if ($this->db->query($sql) == TRUE) {
			$endtime = time();
			print $this->h1('Created \'tln_source\' table in ' . gmdate('H:i:s', $endtime - $starttime));
			return true;
		} else {
			print $this->h1('Error creating \'tln_source\' table: ' . $this->db->error . "<br />");
			return false;
		}
	}
	private function create_fact() {
		$starttime = time();
		$sql = 'CREATE TABLE tln_fact (
			tln_fact_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			tln_date_id BIGINT UNSIGNED NOT NULL,
			tln_time_id BIGINT UNSIGNED NOT NULL,
	    	tln_source_id BIGINT UNSIGNED NOT NULL,
	    	tln_concurrency_id BIGINT UNSIGNED NOT NULL,
	    	count BIGINT UNSIGNED NOT NULL,
			user VARCHAR(50) NOT NULL,
			short VARCHAR(1000) NOT NULL,
			description VARCHAR(1000) NOT NULL,
			filename VARCHAR(1000) NOT NULL,
			inode VARCHAR(25) NOT NULL,
			notes VARCHAR(255) NOT NULL,
			extra  VARCHAR(255) NOT NULL,
			hash CHAR(40) NOT NULL,
			PRIMARY KEY (tln_fact_id),		
			UNIQUE KEY tln_fact_uniq2(tln_date_id, tln_time_id, tln_source_id, inode, hash),
			FOREIGN KEY (tln_date_id) REFERENCES tln_date(tln_date_id),
			FOREIGN KEY (tln_time_id) REFERENCES tln_time(tln_time_id),
			FOREIGN KEY (tln_source_id) REFERENCES tln_source(tln_source_id)
		) ENGINE=InnoDB';
		if ($this->db->query($sql) == TRUE) {
			$endtime = time();
			print $this->h1('Created \'tln_fact\' table in ' . gmdate('H:i:s', $endtime - $starttime));
			return true;
		} else {
			print $this->h1('Error creating \'tln_fact\' table: ' . $this->db->error . "<br />");
			return false;
		}
	}
	/**
	 * Starts the process of upgrading a database.  It calls each upgrade script 
	 * required to advance the database's version number. 
	 */
	public function do_upgrade() {
		for ($i = ($this->get_db_version() + 1); $i <= $this->get_code_version(); $i++) {
			$function = 'do_upgrade' . $i;
			$this->$function();
		}
	}
	
	/**
	 * A parameterized way of executing simple SQL queries 
	 * @param string $op is a key to a generic query
	 */
	private function do_generic_query($op) {
		$starttime = time();
		if (array_key_exists($op, $this->generic_query)) {
			if ($this->db->query($this->generic_query[$op]['sql']) == TRUE) {
				$endtime = time();
				print $this->h1($this->generic_query[$op]['description'] . ' in ' . gmdate('H:i:s', $endtime - $starttime));
				return true;
			} else {
				print $this->h1('Error ' . $this->generic_query[$op]['description'] . ': ' . $this->db->error . "<br />");
				return false;
			}
		} else {
			print $this->h1("Invalid generic query: " . $op);
			return false;
		}
	}
	/**
	 * Upgrades a version 1 database to a version 2 database
	 */
	private function do_upgrade2() {
		if ($this->do_generic_query('alter_columns2')) {
			if ($this->do_generic_query('generate_hash2')) {
				if ($this->do_generic_query('fold_hash2')) {
					if ($this->do_generic_query('set_constraint2')) {
						if ($this->do_generic_query('remove_constraint2')) {
							if ($this->do_generic_query('cleanup2')) {
								if ($this->do_generic_query('updatev1tov2')) {
									print $this->p("All done!");		
								}
							}		
						}		
					}		
				}		
			}	
		}
	}

    private function create_import_word() {
		$starttime = time();
		$sql = 'CREATE TABLE tln_import_word (
			tln_import_word_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			tln_fact_id BIGINT UNSIGNED NOT NULL,
	    	word VARCHAR(255) NOT NULL,
	    	tln_concurrency_id BIGINT UNSIGNED NOT NULL, 
			PRIMARY KEY (tln_import_word_id),		
			UNIQUE KEY tln_import_word_uniq(tln_fact_id, word)
		) ENGINE=InnoDB';
		if ($this->db->query($sql) == TRUE) {
			$endtime = time();
			print $this->h1('Created \'tln_import_word\' table in ' . gmdate('H:i:s', $endtime - $starttime));
			return true;
		} else {
			print $this->h1('Error creating \'tln_import_word\' table: ' . $this->db->error . "<br />");
			return false;
		}
	}
	private function create_word() {
		$starttime = time();
		$sql = 'CREATE TABLE tln_word (
			tln_word_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	    	word VARCHAR(255) NOT NULL,
			UNIQUE KEY tln_word_uniq(word),
	    	PRIMARY KEY (tln_word_id)		
		) ENGINE=InnoDB';
		if ($this->db->query($sql) == TRUE) {
			$endtime = time();
			print $this->h1('Created \'tln_word\' table in ' . gmdate('H:i:s', $endtime - $starttime));
			return true;
		} else {
			print $this->h1('Error creating \'tln_word\' table: ' . $this->db->error . "<br />");
			return false;
		}
	}
	private function create_group() {
		$starttime = time();
		$sql = 'CREATE TABLE tln_group (
			tln_group_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	    	name VARCHAR(25) NOT NULL,
	    	description VARCHAR(1024),
	    	color INT NULL,
			UNIQUE KEY tln_group_uniq(name),
	    	PRIMARY KEY (tln_group_id)		
		) ENGINE=InnoDB';
		if ($this->db->query($sql) == TRUE) {
			$endtime = time();
			print $this->h1('Created \'tln_group\' table in ' . gmdate('H:i:s', $endtime - $starttime));
			return true;
		} else {
			print $this->h1('Error creating \'tln_group\' table: ' . $this->db->error . "<br />");
			return false;
		}
	}
    private function create_fact_word() {
		$starttime = time();
		$sql = 'CREATE TABLE tln_fact_word (
			tln_word_id BIGINT UNSIGNED NOT NULL,
			tln_fact_id BIGINT UNSIGNED NOT NULL,
	    	PRIMARY KEY (tln_word_id, tln_fact_id),		
			FOREIGN KEY (tln_fact_id) REFERENCES tln_fact(tln_fact_id),
			FOREIGN KEY (tln_word_id) REFERENCES tln_word(tln_word_id)
		) ENGINE=InnoDB';
		if ($this->db->query($sql) == TRUE) {
			$endtime = time();
			print $this->h1('Created \'tln_fact_word\' table in ' . gmdate('H:i:s', $endtime - $starttime));
			return true;
		} else {
			print $this->h1('Error creating \'tln_fact_word\' table: ' . $this->db->error . "<br />");
			return false;
		}
	}
	function init_source($concurrency) {
		$starttime = time();
		$sql = 'insert into tln_source (
				tln_concurrency_id,	source,	sourcetype,	type, host,	version, format, M,	A, C, B) values
				(' . $concurrency . ', \'Real World\', \'Real World\', \'Real World\', \'-\', \'1\', \'Tapestry\', \'M\', \'A\', \'C\', \'B\')';
		if (! $this->db->query($sql)) {
			print $this->p('Error filling table: ' . $this->db->error . "<br />");
			return false;
		}
		$endtime = time();
		print $this->p($this->db->affected_rows . ' rows added to \'tln_source\' in ' . gmdate('H:i:s', $endtime - $starttime));
		return true;
	}
	function create_db($job) {
		if ($this->create_date()) {
			if ($this->fill_date()) {
				if ($this->create_time()) {
					if ($this->fill_time()) {
						if ($this->create_version()) {
							if ($this->create_import()) {
								if ($this->create_source()) {
									if ($this->init_source($job->getId())) {
										if ($this->create_fact()) {
											if ($this->create_import_word()) {
												if ($this->create_word()) {
													if ($this->create_fact_word()) {
														if ($this->create_group()) {
															if ($this->create_fact_group()) {
																if ($this->fill_version()) { 
																	print $this->h1('Completed successfully in ' . gmdate("H:i:s", time() - $job->getId()));
																	print $this->h1("Now let's import some data");
																	print $this->p('<img src="1x1.png" onload="setTimeout(\'redirectToIndex()\', 5000);">');
																	print $this->p("You will be redirected in 5 seconds.  If not, please click <a href=\"index.php\" >here</a>.");
																	return true;
																}
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		return false;
	}
	private function create_fact_group() {
		$starttime = time();
		$sql = 'CREATE TABLE tln_fact_group (
			tln_group_id BIGINT UNSIGNED NOT NULL,
			tln_fact_id BIGINT UNSIGNED NOT NULL,
	    	PRIMARY KEY (tln_group_id, tln_fact_id),		
			FOREIGN KEY (tln_fact_id) REFERENCES tln_fact(tln_fact_id),
			FOREIGN KEY (tln_group_id) REFERENCES tln_group(tln_group_id)
		) ENGINE=InnoDB';
		if ($this->db->query($sql) == TRUE) {
			$endtime = time();
			print $this->h1('Created \'tln_fact_group\' table in ' . gmdate('H:i:s', $endtime - $starttime));
			return true;
		} else {
			print $this->h1('Error creating \'tln_fact_group\' table: ' . $this->db->error . "<br />");
			return false;
		}
	}
	private function fill_date() {
		$count = 0;
		$rows = array();
		$starttime = time();
		$sql = "insert into tln_date (tln_date_id, date, year, month, day) values\n";
		$aday = (24 * 60 * 60);
		for ($i = 0; $i < (365 * 100); $i++) {
			$timestamp = $i * $aday;
			$rows[] = ('(' . implode(', ', array($i,
				'\'' . gmdate('Y-m-d', $timestamp) . '\'',
				'\'' . gmdate('Y', $timestamp). '\'',
				'\'' . gmdate('Y-m', $timestamp) . '\'',
				'\'' . gmdate('Y-m-d', $timestamp) . '\'')) . ')');
			if (0 == ($i % 3600)) {
				if (! $this->db->query($sql . implode(",\n", $rows))) {
					print $this->p('Error filling table: ' . $this->db->error);
					return false;
				}
				$count += $this->db->affected_rows;
				$rows = array();
			}
		}
		if (! $this->db->query($sql . implode(",\n", $rows))) {
			print $this->p('Error filling table: ' . $this->db->error);
			return false;
		}
		$endtime = time();
		$count += $this->db->affected_rows;
		print $this->p($count . ' rows added to \'tln_date\' in ' . gmdate('H:i:s', $endtime - $starttime));
		return true;
	}
	function p($text) {
		return '<p>' . $text . '</p>';
	}
	private function fill_time() {
		$count = 0;
		$rows = array();
		$starttime = time();
		$sql = "insert into tln_time (tln_time_id, tick, hour, minute, second) values\n";
		for ($i = 0; $i < (24 * 60 * 60); $i++) {
			$rows[] = ('(' . $i . ', \'' . implode('\', \'', array(
			gmdate('H:i:s', $i),
			gmdate('H', $i),
			gmdate('H:i', $i), 
			gmdate('H:i:s', $i))) . '\')');
			if (0 == ($i % 3600)) {
				if (! $this->db->query($sql . implode(",\n", $rows))) {
					print $this->p('Error filling table: ' . $this->db->error);
					return false;
				}
				$count += $this->db->affected_rows;
				$rows = array();
			}
		}
		if (! $this->db->query($sql . implode(",\n", $rows))) {
			print $this->p('Error filling table: ' . $this->db->error);
			return false;
		}
		$endtime = time();
		$count += $this->db->affected_rows;
		print $this->p($count . ' rows added to \'tln_time\' in ' . gmdate('H:i:s', $endtime - $starttime));
		return true;
	}
	function add_group($name, $description, $color, $entries) {
		if (($id = $this->fill_group($name, $description, $color)) > 0) {
			if ($this->fill_fact_group($id, $entries)) {
				return true;
			}
		}
		return false;
	}
	private function fill_fact_group($gid, $entries) {
		$row = array(); 
		foreach (split(',', $entries) as $fid) {
			$row[] = '(' . $gid . ', ' . $fid . ')';
		}
		$sql = 'insert into tln_fact_group(tln_group_id, tln_fact_id) values '; 
		if ($this->db->query($sql . implode(",\n", $row))) {
			return true;
		}
		return false;
	}
	private function fill_group($name, $description, $color) {
		$sql = 'insert into tln_group(name, description, color) 
				values(\'' . $this->db->real_escape_string($name) . '\', \'' . 
						$this->db->real_escape_string($description) . '\', ' . 
						$color . ')';
		if ($this->db->query($sql)) {
			return $this->db->insert_id;
		} else if ($this->db->errno == 1062) {
			$sql = 'update tln_group
					set description = \'' . $this->db->real_escape_string($description)  . '\',
				        color = ' . $color . '
				    where name = \'' . $this->db->real_escape_string($name) . '\'';
			if ($this->db->query($sql)) {
				$sql = 'select tln_group_id from tln_group where name = \'' . $this->db->real_escape_string($name) . '\'';
				$result = $this->db->query($sql)->fetch_assoc();
				return $result['tln_group_id'];
			}
		}
		return false;
	}
	
	/**
	 * Fills the tln_version table during a clean database install
	 * issue001 
	 */
	private function fill_version() {
		$starttime = time();
		$sql = 'insert into tln_version (version) values(' . $this->code_version . ')';
		if (! $this->db->query($sql)) {
			print $this->p('Error filling table: ' . $this->db->error . "<br />");
			return false;
		}
		$endtime = time();
		print $this->p($this->db->affected_rows . ' rows added to \'tln_version\' in ' . gmdate('H:i:s', $endtime - $starttime));
		return true;
	}
	private function fill_source($concurrency) {
		$starttime = time();
		$sql = 'insert ignore into tln_source (source, sourcetype, type, host, version, format, M, A, C, B)
				select distinct source, sourcetype, type, host, version, format, M, A, C, B
    			from tln_import
    			where tln_concurrency_id = ' . $concurrency;
		if (! $this->db->query($sql)) {
			print ('Error filling table: ' . $this->db->error . "\n");
			return false;
		}
		$endtime = time();
		print $this->db->affected_rows . ' rows added to \'tln_source\' in ' . gmdate('H:i:s', $endtime - $starttime) . "\n";
		return true;
	}
	private function fill_fact($concurrency) {
		$starttime = time();
		$sql = 'insert ignore into tln_fact(tln_date_id, tln_time_id, tln_source_id, 
					tln_concurrency_id, count, user, short, 
					description, filename, inode, notes, extra, hash)
    			select d.tln_date_id, t.tln_time_id, s.tln_source_id, 
    				i.tln_concurrency_id, count(*), i.user, i.short, 
    				i.description, i.filename, i.inode, i.notes, i.extra, 
    				sha1(concat(i.short, i.description, i.filename)) as hash
    			from tln_date d, tln_time t, tln_source s, tln_import i
    			where d.date = i.date and t.tick = i.tick and s.source = i.source and 
    				s.sourcetype = i.sourcetype and 
    				s.M = i.M and s.A = i.A and s.C = i.C and s.B = i.B and 
    				s.type = i.type and s.version = i.version and s.format = i.format and 
    				s.host = i.host and
    				i.tln_concurrency_id = ' . $concurrency . '
    			group by d.tln_date_id, t.tln_time_id, s.tln_source_id, i.inode, hash';
		if (! $this->db->query($sql)) {
			print('Error filling \'tln_fact\' table: ' . $this->db->error . "\n");
			return false;
		}
		$endtime = time();
		print $this->db->affected_rows . ' rows added to \'tln_fact\' in ' . gmdate('H:i:s', $endtime - $starttime) . "\n";
		return true;
	}
	private function fill_fact_word($concurrency) {
		$starttime = time();
		$sql = 'insert ignore into tln_fact_word(tln_fact_id, tln_word_id)
				select f.tln_fact_id, w.tln_word_id
				from tln_fact f, tln_word w, tln_import_word j
				where f.tln_fact_id = j.tln_fact_id and w.word = j.word';
    	if (! $this->db->query($sql)) {
			print('Error filling \'tln_fact_word\' table [' . $this->db->errno . ']: ' . $this->db->error . "\n");
			return false;
		}
		$endtime = time();
		print $this->db->affected_rows . ' rows added to \'tln_fact_word\' in ' . gmdate('H:i:s', $endtime - $starttime) . "\n";
		return true;
	}
	private function fill_word($concurrency) {
		$starttime = time();
		$sql = 'insert ignore into tln_word(word)
    			select word
    			from tln_import_word
    			where tln_concurrency_id = ' . $concurrency;
		if (! $this->db->query($sql)) {
			print('Error filling \'tln_word\' table: ' . $this->db->error . "\n");
			return false;
		}
		$endtime = time();
		print $this->db->affected_rows . ' rows added to \'tln_word\' in ' . gmdate('H:i:s', $endtime - $starttime) . "\n";
		return true;
	}
	private function fill_import_word($concurrency) {
		$starttime = time();
		$inserted = 0;
		$sql = 'select tln_fact_id, short, description 
				from tln_fact 
				where tln_concurrency_id = ' . $concurrency;
		if ($stmt = $this->db->prepare($sql)) {
			$stmt->execute();
			$stmt->store_result();
			$stmt->bind_result($id, $short, $description);
			$words = array();
			while ($stmt->fetch()) {
				preg_match_all('/\w+([-_.@:]\w+)*/', $short . ' ' . $description, $matches); 
				foreach ($matches[0] as $input) {
					$words[] = '(' . implode(',', array(
						$id, 
						$concurrency, 
						'\'' . mysqli_real_escape_string($this->db, strtolower($input)) . '\'')) . ')';
				}			
			}
			$stmt->free_result();
			foreach (array_chunk($words, 5000) as $chunk) {
				$sql = 'insert ignore into tln_import_word (tln_fact_id, tln_concurrency_id, word) values ' . implode(",\n", $chunk);
				if (! $this->db->query($sql)) {
					print 'Error filling \'tln_import_word\' table[' . $this->db->errno . ']: ' . $this->db->error . "\n";
					return false;
				}
				$inserted += $this->db->affected_rows;
			}
			$endtime = time();
			print $inserted . ' rows inserted for words extracted in ' . gmdate('H:i:s', $endtime - $starttime) . "\n";
			return true;	
		} else {
			print 'Error extracting words from \'tln_fact\' table: ' . $this->db->error . "\n";
			return false;
		}
		
	}
	private function fill_import($concurrency, &$text) {
		$starttime = time();
		$inserted = 0;
		$skipped = 0;
		$dup = 0;
		$blank = 0;
		$count = 0;
		$rows = array();
		print "Skipped entries: ";
		foreach(preg_split('/\n/', $text) as $input) {
			// replace nonprintable characters
			$input = trim($input);
			$input = $this->replace_nonprintable($input);
			if (empty($input)) {
				$blank++;
				continue;
			}
			if (! $this->validate_content($input)) {
				print "'" . $input . "', ";
				$skipped++;
				continue;
			}
			$elements = explode(',', $input);
			if ((count($elements)) != 17) {
				// discard this row as a duplicate.  Alternatively, you could:  array_splice($elements, 6, 2);
				$dup++;
				continue;
			}
			// date,tick,timezone,MACB,source,sourcetype,type,user,host,short,desc,version,filename,inode,notes,format,extra
			$tz = new DateTimeZone($elements[2]);
			$time = new DateTime($elements[0] . ' ' . $elements[1], $tz);
			$rows[] = '(\'' . implode('\', \'', array(	
				gmdate('Y-m-d', $time->format('U')),							// date
				gmdate('H:i:s', $time->format('U')),							// tick 
				'GMT',		 													// timezone
				substr($elements[3], 0, 1),										// M
				substr($elements[3], 1, 1),										// A
				substr($elements[3], 2, 1),										// C
				substr($elements[3], 3, 1),										// B
				mysqli_real_escape_string($this->db, $elements[4]),				// source
				mysqli_real_escape_string($this->db, $elements[5]),				// sourcetype
				mysqli_real_escape_string($this->db, $elements[6]),				// type
				mysqli_real_escape_string($this->db, $elements[7]),				// user
				mysqli_real_escape_string($this->db, $elements[8]),				// host
				mysqli_real_escape_string($this->db, substr($elements[9], 0, 1000)),
																				// short
				mysqli_real_escape_string($this->db, substr($elements[10], 0, 1000)),
																				// desc
				mysqli_real_escape_string($this->db, $elements[11]),			// version
				mysqli_real_escape_string($this->db, $elements[12]),			// filename
				mysqli_real_escape_string($this->db, $elements[13]),			// inode
				mysqli_real_escape_string($this->db, $elements[14]),			// notes
				mysqli_real_escape_string($this->db, $elements[15]),			// format
				mysqli_real_escape_string($this->db, $elements[16]))) . '\', ' .	// extra
				$concurrency . ')';												// tln_concurrency_id	
			$count++;
			if (0 == ($count % $this->maxInsert)) {
				if (($status = $this->fill_import_rows($rows)) != -1) {
					$inserted += $status; 
				} else {
					return false;
				}
				// reset the counters
				$count = 0;
				$rows = array();
			}
		}
		if (($status = $this->fill_import_rows($rows)) != -1) {
			$inserted += $status; 
		} else {
			return false;
		}
		$endtime = time();
		print "\n" . $inserted . ' import rows inserted, ' . $skipped . ' skipped, ' . $blank . ' blank, ' . $dup . ' duplicates in ' . gmdate('H:i:s', $endtime - $starttime) . "\n";
		return true;
	}
	private function fill_import_rows(array $rows) {
		if (count($rows) == 0) {
			return 0;
		}
		$sql = "insert into tln_import (date,tick,timezone,M,A,C,B,source,sourcetype,type,user,host,short,description,version,filename,inode,notes,format,extra,tln_concurrency_id) values\n";
		if (! $this->db->query($sql . implode(",\n", $rows))) {
			print 'Error filling \'tln_import\' table: ' . $this->db->error . "\n";
			return -1;
		}
		return $this->db->affected_rows;
	}
	function import(&$text) {
		$job = Job::get_new();
		// START TRANSACTION
		if ($this->begin()) {
			// Insert the uploaded text in the database
			if ($this->fill_import($job->getId(), $text)) {
				// Insert the sources
				if ($this->fill_source($job->getId())) {
					// Insert the fact table rows
					if ($this->fill_fact($job->getId())) {
						// Extract and save words from the data
						if ($this->fill_import_word($job->getId())) {
							// Fill the canonical word list
							if ($this->fill_word($job->getId())) {
								// Fill the snowflake of words
								if ($this->fill_fact_word($job->getId())) {
									// Empty the imported text 
									if ($this->empty_import($job->getId())) {
										// Empty the extracted words
										if ($this->empty_import_word($job->getId())) {
											// COMMIT
											if ($this->commit($job->getId())) {
												print 'All done in ' . gmdate("H:i:s", time() - $job->getId()) . "\n";
												return (0);
											}
										}
									}
								}		
							 }
						}
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
					where tln_concurrency_id = ' . $concurrency;
		if (! $this->db->query($delete)) {
			print 'Error emptying \'tln_import\' table: ' . $this->db->error . "\n";
			return false;
		}
		$inserted = $this->db->affected_rows;
		$endtime = time();
		print $inserted . ' tln_import rows deleted in ' . gmdate('H:i:s', $endtime - $starttime) . "\n";
		return true;
	}
	private function empty_import_word($concurrency) {
		$starttime = time();
		$inserted = 0;
		$delete = 'delete from tln_import_word
					where tln_concurrency_id = ' . $concurrency;
		if (! $this->db->query($delete)) {
			print 'Error emptying \'tln_import_word\' table: ' . $this->db->error . "\n";
			return false;
		}
		$inserted = $this->db->affected_rows;
		$endtime = time();
		print $inserted . ' tln_import_word rows deleted in ' . gmdate('H:i:s', $endtime - $starttime) . "\n";
		return true;
	}
	function get_groups() {
		$sql = 'select tln_group_id as id, name, description, color from tln_group';
		if ($result = $this->db->query($sql)) {
			return $result;
		}
		return false;
	}
	function get_report_view() {
		$sql = 'select g.name, g.description, d.date, t.tick, s.host, s.source, 
					s.sourcetype, s.m, s.a, s.c, s.b, f.count, f.user, f.description, 
					f.inode, f.notes, f.extra, g.color
				from tln_date d inner join (
						tln_time t inner join (
							tln_source s inner join (
								tln_fact f inner join (
									tln_fact_group fg inner join tln_group g on fg.tln_group_id = g.tln_group_id
    							) on f.tln_fact_id = fg.tln_fact_id
    						) on s.tln_source_id = f.tln_source_id
    					) on t.tln_time_id = f. tln_time_id
    				) on d.tln_date_id = f.tln_date_id
    			order by d.date desc, t.tick desc;';
		$result = array();
		if ($stmt = $this->db->prepare($sql)) {
			$stmt->execute();
			$stmt->store_result();
			$stmt->bind_result($grp_name, $grp_description, $date, $tick, $host, $source, 
					$sourcetype, $m, $a, $c, $b, $count, $user, $description, 
					$inode, $notes, $extra, $color);
			while ($stmt->fetch()) {  
				$result[] = array($grp_name, $grp_description, array($m, $a, $c, $b), 
					array($count, $date, $tick), array($host, $source, $sourcetype, $type,
					$user, $description, $inode, $notes, $extra), $color);
			}
			$stmt->free_result();
		}
		return $result;
	}
	function get_detail_view($params){
		$starttime = time();
		$order = 'DESC';
		$word_join = 'tln_fact f';
		if (array_key_exists('go', $params) && $params['go'] == 'forward') {
			$order = '';
		}
		if (array_key_exists('word', $params)) {
			$word_join = '(
							tln_word w inner join (
								tln_fact_word fw inner join tln_fact f on fw.tln_fact_id = f.tln_fact_id
							) on w.tln_word_id = fw.tln_word_id
						)';
		}
		$sql = 'select sum(count) as count, 
					d.date, t.tick, s.source, s.sourcetype, 
					s.m, s.a, s.c, s.b,	f.user, s.host, f.short, f.description, 
					f.filename, f.inode, f.notes, f.extra, s.type, s.version, s.format, f.tln_fact_id, g.color
    			from (tln_date d inner join (
	    		        tln_time t inner join (
	    				  tln_source s inner join ' . $word_join . ' on s.tln_source_id = f.tln_source_id
	    				) on t.tln_time_id = f.tln_time_id
		    	      ) on d.tln_date_id = f.tln_date_id
		    	    ) left join (
				  tln_fact_group fg inner join tln_group g on fg.tln_group_id = g.tln_group_id
				) on f.tln_fact_id = fg.tln_fact_id
    			' . $this->get_where($params) . '
    			group by f.tln_fact_id
    			order by d.date ' . $order . ', t.tick ' . $order . ', f.tln_fact_id
    			limit 1000';
		$result = array();
		if ($stmt = $this->db->prepare($sql)) {
			$stmt->execute();
			$stmt->store_result();
			$this->set_row_count($stmt->num_rows);
			$stmt->bind_result($count, 
					$date, $tick, $source, $sourcetype, $m, $a, $c, $b, 
					$user, $host, $short, $description, $filename,
					$inode, $notes, $extra, $type, $version, $format, $tln_fact_id, $color);
			$macb = array();
			$keys = array();
			while ($stmt->fetch()) {  
				$result[] = array(array($m, $a, $c, $b), array($count, $date, $tick), array($source, $sourcetype, $type,
					$user, $host, $short, $description, $version, $filename,
					$inode, $notes, $format, $extra), $tln_fact_id, $color);
			}
			$stmt->free_result();
		}
		if (array_key_exists('go', $params)) {
			if ($params['go'] == 'forward') {
				return array_reverse($result);
			} 
		}
		return $result;
	}
	private function get_datetime_where($params) {
		if (array_key_exists('datezoom', $params)) { 
			if (! validate_int($params['datezoom'], 0, count($this->datezoom)))
				return false;
			$this->datezoom_level = $params['datezoom'];
			$datefield = $this->datezoom[$this->datezoom_level]['datefield'];
			$timefield = $this->datezoom[$this->datezoom_level]['timefield'];
		}
		$result = array();
		if (array_key_exists('date', $params)) {
			$ymd = explode('-', $params['date']);
			if (array_key_exists(0, $ymd) && ! validate_int($ymd[0], 1970, 2070))
				return false;
			if (array_key_exists(1, $ymd) && ! validate_int($ymd[1], 1, 12))
				return false;			
			if (array_key_exists(2, $ymd) && ! validate_int($ymd[2], 1, 31))
				return false;
			if (array_key_exists('time', $params) && $params['time'] != '.') {
				$hms = explode('-', $params['time']);
				if (array_key_exists(0, $hms) && ! validate_int($hms[0], 0, 23))
					return false;
				if (array_key_exists(1, $hms) && ! validate_int($hms[1], 0, 59))
					return false;			
				if (array_key_exists(2, $hms) && ! validate_int($hms[2], 0, 23))
					return false;
				if ((array_key_exists('view', $params) && $params['view'] == 'detail') ||
				    (array_key_exists('entries', $params))) 
				{ 
					if (array_key_exists('go', $params)) {
						$datefield = 'd.date';
						$timefield = 't.tick';
						if ($params['go'] == 'forward') {
							$params['time'] = $params['time'];
							$op = '>';
							$fudge = '';
						} elseif ($params['go'] == 'backward') {
							$params['time'] = $params['time'];
							$op = '<';
							$fudge = '';
						}
					} else { // into detail view from summary view
						$op = '<'; 
						$fudge = ':99:99';
					}
					$result[] = '(' . $datefield . $op . '\'' . $params['date'] . '\' 
						or (' . $datefield . '=\'' . $params['date'] . '\' and ' . $timefield . $op . '=\'' . $params['time'] . $fudge . '\'))';
					return $result;
				} else {
					$op = '<'; 
					$fudge = ':99:99';
					if (array_key_exists('go', $params)) {
						if ($params['go'] == 'forward') {
							$params['time'] = $params['time'];
							$op = '>';
							$fudge = '';
						} elseif ($params['go'] == 'backward') {
							$params['time'] = $params['time'];
							$op = '<';
							$fudge = '';
						}
						$result[] = '(' . $datefield . $op . '\'' . $params['date'] . '\' 
							or (' . $datefield . '=\'' . $params['date'] . '\' and ' . $timefield . $op . '=\'' . $params['time'] . $fudge . '\'))';
						return $result;
					} else { // datezoom increase
						$result[] = '(' . $datefield . $op . '\'' . $params['date'] . '\' 
							or (' . $datefield . '=\'' . $params['date'] . '\' and ' . $timefield . $op . '=\'' . $params['time'] . $fudge . '\'))';
						return $result;
					}
				}
			}
			$op = '<='; 
			$fudge = '-99'; 
			if (array_key_exists('go', $params)) {
				if ($params['go'] == 'forward') {
					$op = '>=';
					$fudge = '';
				} elseif ($params['go'] == 'backward') {
					$op = '<=';
					$fudge = '-99';
				} 
			} else { // datezoom increase 
				$op = '<='; 
				$fudge = '-99'; 
			}
			$result[] = $datefield . $op . '\'' . $params['date'] . $fudge . '\'';
			return $result;
		} 
	}
	private function get_where($params) {
		$result = $this->get_datetime_where($params);
		if (array_key_exists('word', $params)) 
			$result[] = 'word = \'' . $params['word'] . '\'';
		if (array_key_exists('host', $params)) {
			$result[] = 's.host=\'' . $this->db->real_escape_string($params['host']) . '\'';
		}
		if (array_key_exists('source', $params)) {
			$result[] = 's.source=\'' . $this->db->real_escape_string($params['source']) . '\'';
		}
		if (array_key_exists('sourcetype', $params)) {
			$result[] = 's.sourcetype=\'' . $this->db->real_escape_string($params['sourcetype']) . '\'';
		}
		if (array_key_exists('format', $params)) {
			$result[] = 's.format=\'' . $this->db->real_escape_string($params['format']) . '\'';
		}
		if (array_key_exists('version', $params)) {
			$result[] = 's.version=\'' . $this->db->real_escape_string($params['version']) . '\'';
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
		if (count($result) > 0)
			return 'where ' . implode(' and ', $result);
		else
			return ''; 
	}
	function get_view($params) {
		$starttime = time();
		if (array_key_exists('datezoom', $params)) 
			$this->datezoom_level = $params['datezoom'];
		if (array_key_exists('go', $params) && $params['go'] == 'forward') 
			$order = '';
		else 
			$order = 'desc';
		$datefield = $this->datezoom[$this->datezoom_level]['datefield'];
		$timefield = $this->datezoom[$this->datezoom_level]['timefield'];;
		$sql = 'select ' . $datefield . ', ' . $timefield . ', s.source, s.sourcetype, count(s.M), count(s.A), count(s.C), count(s.B), sum(f.count) as items, s.version, s.format, s.host
    			from tln_date d inner join (
    				tln_time t inner join (
    					tln_source s inner join tln_fact f on s.tln_source_id = f.tln_source_id
    				) on t.tln_time_id = f.tln_time_id
    			) on d.tln_date_id = f.tln_date_id 
    			' . $this->get_where($params) . '
    			group by ' . $datefield . ', ' . $timefield . ', s.source, s.sourcetype, s.version, s.format, s.host
    			order by ' . $datefield . ' ' . $order . ', ' . $timefield . ' ' . $order . ', s.sourcetype, s.sourcetype, s.version, s.format, s.host';
		$result = array();
		$my_params = $params;
		unset($my_params['go']);
		if ($stmt = $this->db->prepare($sql)) {
			$stmt->execute();
			$stmt->store_result();
			$this->set_row_count($stmt->num_rows);
			if ($this->get_row_count() <= 0)
				return $result;
			$stmt->bind_result($year, $month, $source, $sourcetype, $m, $a, $c, $b, $items, $version, $format, $host);
			$columns = array();
			$sourcerows = array();
			$my_params['datezoom'] = $this->datezoom_level;
			$datetime_count = 0;
			while ($stmt->fetch() && $datetime_count < 100) {
				if (( ! isset($oldyear)) || $year == $oldyear) {
					$my_params['date'] = $year;
					if (( ! isset($oldmonth)) || $month == $oldmonth) {
						$my_params['time'] = $month;
						$columns = array();
						$this->columns($columns, $m, $a, $c, $b, $items, $my_params);
						$sourcerows[] = $this->sourcerows($columns, $source, $sourcetype, $version, $format, $host, $my_params);
						$oldmonth = $month;
					} else {
						$result[] = $this->daterows($sourcerows, $year, $oldmonth, $my_params);
						$columns = array();
						$sourcerows = array();
						$my_params['time'] = $month;
						$this->columns($columns, $m, $a, $c, $b, $items, $my_params);
						$sourcerows[] = $this->sourcerows($columns, $source, $sourcetype, $version, $format, $host, $my_params);
						$oldmonth = $month;
						$datetime_count++;
					}
					$oldyear = $year;
				} else {
					$result[] = $this->daterows($sourcerows, $oldyear, $oldmonth, $my_params);
					$columns = array();
					$sourcerows = array();
					$my_params['time'] = $month;
					$my_params['date'] = $year;
					$this->columns($columns, $m, $a, $c, $b, $items, $my_params);
					$sourcerows[] = $this->sourcerows($columns, $source, $sourcetype, $version, $format, $host, $my_params);
					$oldmonth = $month;
					$oldyear = $year;
					$datetime_count++;
				}
			}
			$result[] = $this->daterows($sourcerows, $oldyear, $oldmonth, $my_params);		
			$stmt->free_result();
		} else {
			print $this->p('Error getting detail view: ' . $this->db->error);
			return false;
		}
		if (array_key_exists('go', $params) && $params['go'] == 'forward') 
			return array_reverse($result);
		else 
			return $result;
	}
	private function daterows (&$rows, $year, $month, $params) {
		$zoom_in = $params;
		$zoom_out = $params;
		$details = $params;
		$zoom_in['datezoom'] = ($zoom_in['datezoom'] < (count($this->datezoom) - 1)) ? ($zoom_in['datezoom'] + 1) : ($zoom_in['datezoom']); 			
		$zoom_out['datezoom'] = ($zoom_out['datezoom'] >= 1) ? ($zoom_out['datezoom'] - 1) : ($zoom_out['datezoom']);
		$details['view'] = 'detail';	
		return array(array($year,$month), $rows, array($params, $zoom_in, $zoom_out, $details));
	}
	private function sourcerows (&$columns, $source, $sourcetype, $version, $format, $host, $params) {
		$myparams = $params;
		$result = array();
		$myparams['host'] = $host;
		$result[] = array($host, $myparams);
		$myparams = $params;
		$myparams['source'] = $source;
		$result[] = array($source, $myparams);
		$myparams = $params;
		$myparams['sourcetype'] = $sourcetype;
		$result[] = array($sourcetype, $myparams);
		$myparams = $params;
		$myparams['format'] = $format;
		$result[] = array($format, $myparams);
		$myparams = $params;
		$myparams['version'] = $version;
		$result[] = array($version, $myparams);
		$myparams = $params; 
		return array($columns,$result);
	}
	private function columns(&$columns, $m, $a, $c, $b, $items, $params) {
		$myparams = $params; 
		$myparams['view'] = 'detail';
		$myparams['macb'] = 'M';
		$columns[] = array($m, $myparams);
		$myparams['macb'] = 'A';
		$columns[] = array($a, $myparams);
		$myparams['macb'] = 'C';
		$columns[] = array($c, $myparams);
		$myparams['macb'] = 'B';
		$columns[] = array($b, $myparams);
		unset($myparams['macb']);
		$columns[] = array($items, $myparams);
		return $columns;
	}
	function get_macb($macb) {
		$result = '';
		$result .= $macb[0];
		$result .= $macb[1];
		$result .= $macb[2];
		$result .= $macb[3];
		return $result;
	} 
	function get_selection_properties($list) {
		$starttime = time();
		$sql = 'select min(addtime(d.date, t.tick)) as min,
    				   max(addtime(d.date, t.tick)) as max,
    				   count(f.tln_fact_id) as count
    			from tln_date d inner join (
    				tln_time t inner join tln_fact f on t.tln_time_id = f.tln_time_id
    			) on d.tln_date_id = f.tln_date_id
    			where f.tln_fact_id in (' . $list . ')';
		if ($result = $this->db->query($sql)) {
			if ($row = $result->fetch_assoc()) {
				return $row;
			} 
		}
		return false;
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
		if (! preg_match('@^\d+/\d+/\d+,\d+:\d+:\d+,\w+,[M.][A.][C.][B.],[^,]+,[^,]+,[^,]+,[^,]+,[^,]+,[^,]*,[^,]+,[^,]+,[^,]+,[^,]+,[^,]+,[^,]+,[^,]+$@', $input, $match)) { 
			return false;
		} else { 
			return true;
		}
	}
	
	private function replace_nonprintable($input) {
		$offset = 0;
		while (preg_match('/[^[:print:]]/', $input, $matches, PREG_OFFSET_CAPTURE, $offset)) {
			$input = substr_replace($input, '[0x'. dechex(ord($matches[0][0])) . ']', $matches[0][1], 1);
			$offset = $matches[0][1];
		}
		return $input;
	}
}

?>
