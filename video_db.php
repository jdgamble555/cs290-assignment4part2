<?php
/* Author: Jonathan Gamble
/* Course: CS290 @Oregon State Spring Term 2015
/* - while all code is mine, basic connecting strategy 
/*   taken from PHP and MySQL for Dynamic Web Sites 
/*   (4th ed.) by Larry Ullman Ch. 9
*/ 

// include login information
require 'login.php';

class video_db {
    // globals
    private $table, $dbh, $query, $fields, $filter;
    public $results;

    public function __construct() {
        // connect to db, set encoding, initiate variables
        $this->dbh = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)
            or die('Could not connect to MySQL: '.mysqli_connect_error());
        mysqli_set_charset($this->dbh, 'utf8');
        $this->table = "video_inventory";
        $this->fields = array();
    }
    public function __destruct() {
        // disconnect from db
        mysqli_close($this->dbh);
    }
    public function get_inventory() {
        // get the video store inventory
        $this->query = "SELECT * from ".$this->table;
        if ($this->filter)
            $this->query .= " WHERE category='".$this->filter."'";
        return $this->get_results();
    }
    public function get_categories() {
        // get the categories
        $this->query = "SELECT category from ".$this->table;
        return $this->get_results();
    }
    private function get_results() {
        // get the results of an sql query
        $result = mysqli_query($this->dbh, $this->query);
        if ($inv = mysqli_num_rows($result)) {
            $this->results = array();
            while ($row = mysqli_fetch_assoc($result)) {
                array_push($this->results, $row);
            }
            mysqli_free_result($result);
        }
        return $inv;
    }
    public function run_query() {
        // run the sql query
        $r = mysqli_query($this->dbh, $this->query);
        if (!$r)
            return mysqli_error($this->dbh);
        return NULL;
    }
    public function remove_all() {
        // remove all videos
        $this->query = "DELETE FROM ".$this->table;
        return $this->run_query();
    }
    public function add($fields) {
        // add a video
        $this->fields = $fields;
        $this->_get_insert_q();
        return $this->run_query();
    }
    public function remove($id) {
        // remove a video
        $this->query = "DELETE FROM ".$this->table." WHERE id=".$id;
        return $this->run_query();
    }
    public function checkin($id) {
        // checkin a video
        $this->query = "UPDATE ".$this->table." SET rented=0 WHERE id=".$id;
        return $this->run_query();
    }
    public function checkout($id) {
        // checkout a video
        $this->query = "UPDATE ".$this->table." SET rented=1 WHERE id=".$id;
        return $this->run_query();
    }
    public function set_filter($filter) {
        // set category filter
        $this->filter = $filter;
    }
    private function _get_insert_q() {
        // create insert query from $this->fields
        $fields = $this->fields;
        $query = "INSERT into ".$this->table." (";
        $names = "";
        $values = "";
        $i = 0;
        foreach ($fields as $k => $v) {
            $names .= $k;
            $values .= "'".$v."'";
            // if last item
            if (++$i !== count($fields)) {
                $names .= ', ';
                $values .= ', ';
            }
        }
        $query .= $names.") VALUES (".$values.")";
        $this->query = $query;
    }
}