<?php

class db {

    var $con;
    var $debug = false;

    function db($mysql_host, $mysql_user, $mysql_password, $mysql_database, $debug = false) {

        $this->con = mysql_connect($mysql_host, $mysql_user, $mysql_password);

        if ($this->con == false) {
            die("Could not connect to database engine. Please try later.");
        }

        mysql_select_db($mysql_database, $this->con);

        $this->debug = $debug;
    }

    function __destruct() {
        mysql_close($this->con);
    }

    function select($columns, $table, $where) {
        if ($this->debug) {
            echo("<p>sql = " . "SELECT $columns FROM $table WHERE $where</p>");
        }
        $result = mysql_query("SELECT $columns FROM $table WHERE $where");
        if ($result == false) {
            if ($this->debug) {
                echo("<p>sqlerror = " . mysql_error());
            }
            return false;
        }
        return ($result);
    }

    function insert($table, $values, $columns = "") {
        if ($columns != "") {
            $query = "INSERT INTO $table($columns) VALUES($values)";
        } else {
            $query = "INSERT INTO $table VALUES($values)";
        }
        
        if ($this->debug) {
            echo($query);
        }
        
        return mysql_query($query);
    }

    function delete($table, $where) {
        if ($where == "") {
            /* Never allow a delete all */
            return false;
        } else {
            $query = "DELETE FROM $table WHERE $where";
        }

        if ($this->debug) {
            echo($query);
        }

        return mysql_query($query);
    }

}

?>
