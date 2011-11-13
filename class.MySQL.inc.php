<?php

GLOBAL $query_count;
$query_count["ALL"] = 0;
$query_count["REL"] = 0;


class query {
	private $result = false;
	private $count  = null;

	function __construct($result){
		$this->result = $result;
		$this->count  = count($result);
	}

	/**
	 * Packt den Result in ein Namen-Array
	 *
	 * @return
	 */
	public function fetch_array()
	{
		$array = array();
		if (current($this->result)) {
			foreach (current($this->result) AS $value) {
				$array[] = $value;
			}
			foreach (current($this->result) AS $key => $value) {
				$array[$key] = $value;
			}
		}else {
			$array = FALSE;
		}
		next($this->result);
		return $array;
	}

	/**
	 * Packt den Result in ein Objekt
	 *
	 * @return
	 */
	public function fetch_object()
	{
		$object = null;
		if (current($this->result)) {
			foreach (current($this->result) AS $key => $value) {
				$object->$key = $value;
			}
		}else {
			$object = FALSE;
		}
		next($this->result);
		return $object;
	}

	/**
	 * Zählt die Zeilen vom Query-Result
	 *
	 * @return
	 */
	public function num_rows()
	{
		$num_rows = @count($this->result);
		return $num_rows;
	}

	/**
	 * Packt den Result in ein Zahlen-Array
	 *
	 * @return
	 */
	public function fetch_row()
	{
		$row = array();
		if (current($this->result)) {
			foreach (current($this->result) AS $value) {
				$row[] = $value;
			}
		}else {
			$row = FALSE;
		}
		next($this->result);
		return $row;
	}

	/**
	 * Gibt die länge der letzten gefetchten Array-Keys in einem Array zurück
	 *
	 * @return
	 */
	public function fetch_lengths()
	{
		$lengths = null;
		$prev = prev($this->result);
		if ($prev) {
			foreach ($prev AS $value) {
				$lengths[] = strlen($value);
			}
		}else {
			foreach (end($this->result) AS $value) {
				$lengths[] = strlen($value);
			}
		}
		next($this->result);
		return $lengths;
	}

	/**
	 * Packt den Result in ein Assozatives-Array
	 *
	 * @return
	 */
	public function fetch_assoc()
	{
		$assoc = array();
		if (current($this->result)) {
			foreach (current($this->result) AS $key => $value) {
				$assoc[$key] = $value;
			}
		}else {
			$assoc = FALSE;
		}
		next($this->result);
		return $assoc;
	}

	/**
	 * Liefert die Flags eines Feldes in einem Anfrageergebnis
	 * http://www.php.net/manual/de/function.mysql-field-flags.php
	 *
	 * @return
	 */
	public function field_flags()
	{
		$field_flags = @mysql_field_flags($this->result);
		return $field_flags;
	}
}

class mysql {
	var $query;
	var $queryresult;
	var $querySafe = array();

	/**
	 * Baut eine Verbindung mit dem MySQL-Server auf
	 *
	 * @return
	 */
	public function mysql()
	{
		@mysql_pconnect(MySQL_Host, MySQL_User, MySQL_Pass) or $this->__mysql_error();
		#mysql_connect(MySQL_Host, MySQL_User, MySQL_Pass);
		@mysql_select_db(MySQL_DB) OR $this->__mysql_error();

		GLOBAL $mysql_count;
		$mysql_count++;
	}

	private function __mysql_error()
	{
		echo "<b><font size=\"5\">MySQL detected an Error!</font></b>";
		echo "<hr>";
		echo "<b>MySQL Query:</b> ".$this->query."<br>";
		echo "<b>MySQL Error Number:</b> ".mysql_errno()."<br>";
		echo "<b>MySQL Error:</b> ".mysql_error();
		echo "<hr>";
		echo "<i>Terminating the program...</i>";
		exit;
	}

	/**
	 * Speichert ein Query-Result zwischen
	 *
	 * @param string $sql
	 * @return
	 */
	public function query($sql, $Buffer = FALSE)
	{
		GLOBAL $query_count;

		$objQuery = TRUE;

		if (!$Buffer) {
			$this->query 	= $sql;
			$query 			= mysql_query($sql) OR die($this->__mysql_error($sql));
			//---- result Array erstellen
			$result 		= array();
			//echo "<br>";
			//var_dump($query);
			while(@$row = mysql_fetch_assoc($query)){
				$result[] = $row;
			}
			$objQuery 		= new Query($result);
			$this->querySafe[$sql] = $result;
			$query_count["REL"]++;
		}else {
			if (array_key_exists($sql, $this->querySafe)) {
				$objQuery = new Query($this->querySafe[$sql]);
			}else {
				$this->query 	= $sql;
				$query 			= mysql_query($sql);
				//---- result Array erstellen
				$result 		= array();
				while(@$row = mysql_fetch_assoc($query)){
					$result[] = $row;
				}
				$objQuery 		= new Query($result);
				$this->querySafe[$sql] = $result;
				$query_count["REL"]++;
			}
		}

		$query_count["ALL"]++;

		#echo $query_count;


		if (!$query && !$Buffer) {
			//return $this->__error("Der Query[$sql] konnte nicht erfolgreich ausgeführt werden.");
			$this->__mysql_error($sql);
		} else {
			$this->queryresult = $query;

			return $objQuery;
		}
	}

	/*
	   * Liefert die ID einer vorherigen INSERT-Operation
	   *
	   * @return
	*/

	public function insert_id()
	{
		$insert_id = @mysql_insert_id();
		return $insert_id;
	}


	/*
	   * mysql_change_DB
	   * @return
	*/
	public function select_db($db) {
		@mysql_select_db($db) OR $this->__mysql_error();
		return TRUE;
	}

	/**
	 * Gibt den Belegten Speicher wieder frei und beendet die Verbindung
	 *
	 * @return
	 */
	public function close() {
		if ($this->queryresult != "")
			mysql_free_result($this->queryresult);
		mysql_close();
	}

	/*
	   * FREE RESULT
	   * @return
	*/
	function free_result() {
		mysql_free_result($this->queryresult);
	}

	/**
	 * Gibt die Gesamtzahl der Querys zurück
	 *
	 * @return
	 */
	public function query_count() {
		GLOBAL $query_count;
		GLOBAL $mysql_count;
		$return->query_count = $query_count["ALL"]."(".$query_count["REL"].")";
		$return->mysql_count = $mysql_count;
		return $return;
	}

}

?>