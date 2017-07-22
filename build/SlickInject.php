<?php

/*
\| @Author: LegitSoulja
\| @License: MIT
\| @Source: https://github.com/LegitSoulja/SlickInject
*/

namespace {
  
  use \SlickInject\Parser as Parser;
  use \SlickInject\SQLObject as SQLobject;
  
  class SlickInject
  {

      private static $SQLObject;

      /**
       * SlickInject constructor | Can accept database credentials.
       * @return void
       */
      function __construct()
      {
          $args = func_get_args();
          if (count($args) === 4)
              return $this->connect($args[0], $args[1], $args[2], $args[3]);
      }

      /**
       * Connect to database
       * @param string $db_host          Database host
       * @param string $db_user          Database username
       * @param string $db_pass          Database password
       * @param string $db_name          Database name
       * @return void
       */
      public function connect($db_host, $db_user, $db_pass, $db_name)
      {
          if ($this->isConnected()) return;
          self::$SQLObject = new SQLObject($db_host, $db_user, $db_pass, $db_name);
      }

      private function isConnected()
      { return ((self::$SQLObject instanceof SQLObject)) ? true : false; }

      /**
       * Returns SQLObject
       * @return SQLObject
       */
      public function getSQLObject()
      { return self::$SQLObject; }

      /**
       *Close database connection
       * @return void
       */
      public function close()
      { return self::$SQLObject->close(); }

      /**
       * Update database with data
       * @param string $table          Name of table in which you're updating.
       * @param array $object          List of key/values of the data you're updating
       * @param array $where           List of key/values of the where exist
       * @return SQLResponce
       */
      public function UPDATE($table, $object, $where)
      {
          if (!$this->isConnected() || !isset($table) || !isset($object) || !isset($where)) return;
          $update = Parser::UPDATE($table, $object, $where);
          return self::$SQLObject->query($update[0], (isset($update[1])) ? $update[1] : NULL);
      }

      /**
       * Select data from a database
       * @param array $columns              List of specific columns that is being obtained. * = [];
       * @param string $table               Name of table in which you're updating.
       * @param array $where                List of key/values of the where exist
       * @param bool $rr                    Return rows, false returns SQLResponce
       * @return array
       */
      public function SELECT($columns, $table, $where = NULL, $rr = true)
      {
          if (!$this->isConnected() || !isset($columns) || !isset($table)) return;
          $select = Parser::SELECT($columns, $table, $where);
          return self::$SQLObject->query($select[0], (isset($select[1])) ? $select[1] : NULL, $rr);
      }

      /**
       * Insert data into a database
       * @param string $table               Name of table in which you're updating.
       * @param array $object                List of key/values of the data you're inserting
       * @return SQLResponce
       */
      public function INSERT($table, $object)
      {
          if (!$this->isConnected() || !isset($table) || !isset($object)) return;
          $insert = Parser::INSERT($table, $object);
          return self::$SQLObject->query($insert[0], $insert[1]);
      }

      /**
       * Truncate/Delete table
       * @param string $table               Name of table in which you're updating.
       * @return SQLResponce
       */
      public function TRUNCATE($table)
      {
          if (!$this->isConnected() || !isset($table)) return;
          $truncate = Parser::TRUNCATE($table);
          return self::$SQLObject->query($truncate[0]);
      }

      /**
       * Delete a row in a table
       * @param string $table               Name of table in which you're updating.
       * @param array $where                List of key/values that directs which/where table is being deleted
       * @return SQLResponce
       */
      public function DELETE($table, $where = NULL)
      {
          if (!$this->isConnected() || !isset($table) || !isset($object)) return;
          $delete = Parser::DELETE($table, $where);
          return self::$SQLObject->query($delete[0], (isset($delete[1])) ? $delete[1] : NULL);
      }
  }
}


namespace SlickInject {
  
  class SQLResponce
  {

      private static $result;
      private static $rows;
      private static $row;
      private static $stmt;

      /**
       * SQLResponce constructor
       * @return void
       */
      function __construct($stmt, $rows = array())
      {
          self::$stmt   = $stmt;
          self::$result = $stmt->get_result();
          if (self::$result->num_rows < 1) return;
          while ($row = self::$result->fetch_assoc())
          { array_push($rows, $row); }
          if (count($rows) === 1) return self::$row = $rows;
          return self::$rows = $rows;
      }

      function __destruct(){}

      /**
       * Return mysqli_result object
       * @return object
       */
      public function getResult()
      { return self::$result; }

      /**
       * Check if any rows was affected during execution
       * @return bool
       */
      public function didAffect()
      { return (self::$stmt->affected_rows > 0) ? true : false; }

      /**
       * Check if result hasn't failed
       * @return bool
       */
      public function error()
      { return (self::$result) ? true : false; }

      /**
       * Check if results contain rows
       * @return bool
       */
      public function hasRows()
      { return ((count(self::$rows) > 0) || (count(self::$row) > 0)) ? true : false; }

      /**
       * Return number of rows
       * @return int
       */
      public function num_rows()
      { return (int) self::$result->num_rows; }

      /**
       * Return rows from results
       * @return array
       */
      public function getData()
      { return (count(self::$rows) > 0) ? self::$rows : self::$row; }
  }

  class SQLObject
  {
      private static $con;

      /**
       * SQLObject constructor | Can accept database  credentials
       * @return void
       */
      function __construct()
      {
          $args = func_get_args();
          if (count($args) === 4)
              return $this->connect($args[0], $args[1], $args[2], $args[3]);
      }


      /**
       * Close database connected
       * @return void
       */
      public function close()
      { @\mysqli_close(self::$con); }


      /**
       * Connect to database
       * @param string $db_host          Database host
       * @param string $db_user          Database username
       * @param string $db_pass          Database password
       * @param string $db_name          Database name
       * @return void
       */
      public function connect($db_host, $db_user, $db_pass, $db_name)
      {
          if ($this->isConnected()) return;
          self::$con = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
      }

      /**
       * [Private] Checks if the database connection was ever established.
       * @return bool
       */
      private function isConnected()
      { return (isset(self::$con) && $this->ping()) ? true : false; }

      /**
       * Get connect error status
       * @return int
       */
      public function getConnectionError()
      { return @\mysqli_connect_error(); }

      /**
       * Get last error from a failed prepare, and or execute.
       * @return string
       */
      public function getLastError()
      { return @\mysqli_error(self::$con); }

      /** Deprecated [Useless]
       * Escape string using mysqli
       * @return string
       */
      private function escapeString(&$string)
      { return self::$con->real_escape_string($string); }

      /**
       * Check if connection still live
       * @return bool
       */
      public function ping()
      { return (@self::$con->ping()) ? true : false; }


      /**
       * Send query, in which is processed specially.
       * @param stting $sql                  The query that will be prepared
       * @param array $bind                  Types, and params to be binded before execute
       * @param bool $rr                     Return rows (Array), or returns SQLObject (Object).
       * @return boolean
       */
      public function query($sql, $bind, $rr = false)
      {
          try {
              $prep = self::$con->stmt_init();
              if ($prep->prepare($sql)) {
                  if (isset($bind) && $bind != NULL) call_user_func_array(array($prep, "bind_param" ), $bind);
                  if ($prep->execute()) {
                      $result = new SQLResponce($prep);
                      if ($rr) return ($result->hasRows()) ? $result->getData() : array();
                      return $result;
                  }
              }
              throw new \Exception($this->getLastError());
          }
          catch (\Exception $ex) 
          { die("Error " . $ex->getMessage()); }
      }
  }

  class Parser
  {

      /**
       * Reserved keywords to prevent future errors
       */
      private static $RESERVED_KEYWORDS = array("ADD", "KEYS", "EXTERNAL", "PROCEDURE", "ALL", "FETCH", "PUBLIC", "ALTER", "FILE", "RAISERROR", "AND", "FILLFACTOR", "READ", "ANY", "FOR", "READTEXT", "AS", "FOREIGN", "RECONFIGURE", "ASC", "FREETEXT", "REFERENCES", "AUTHORIZATION", "FREETEXTTABLE", "REPLICATION", "BACKUP", "FROM", "RESTORE", "BEGIN", "FULL", "RESTRICT", "BETWEEN", "FUNCTION", "RETURN", "BREAK", "GOTO", "REVERT", "BROWSE", "GRANT", "REVOKE", "BULK", "GROUP", "RIGHT", "BY", "HAVING", "ROLLBACK", "CASCADE", "HOLDLOCK", "ROWCOUNT", "CASE", "IDENTITY", "ROWGUIDCOL", "CHECK", "IDENTITY_INSERT", "RULE", "CHECKPOINT", "IDENTITYCOL", "SAVE", "CLOSE", "IF", "SCHEMA", "CLUSTERED", "IN", "SECURITYAUDIT", "COALESCE", "INDEX", "SELECT", "COLLATE", "INNER", "SEMANTICKEYPHRASETABLE", "COLUMN", "INSERT", "SEMANTICSIMILARITYDETAILSTABLE", "COMMIT", "INTERSECT", "SEMANTICSIMILARITYTABLE", "COMPUTE", "INTO", "SESSION_USER", "CONSTRAINT", "IS", "SET", "CONTAINS", "JOIN", "SETUSER", "CONTAINSTABLE", "KEY", "SHUTDOWN", "CONTINUE", "KILL", "SOME", "CONVERT", "LEFT", "STATISTICS", "CREATE", "LIKE", "SYSTEM_USER", "CROSS", "LINENO", "TABLE", "CURRENT", "LOAD", "TABLESAMPLE", "CURRENT_DATE", "MERGE", "TEXTSIZE", "CURRENT_TIME", "NATIONAL", "THEN", "CURRENT_TIMESTAMP", "NOCHECK", "TO", "CURRENT_USER", "NONCLUSTERED", "TOP", "CURSOR", "NOT", "TRAN", "DATABASE", "NULL", "TRANSACTION", "DBCC", "NULLIF", "TRIGGER", "DEALLOCATE", "OF", "TRUNCATE", "DECLARE", "OFF", "TRY_CONVERT", "DEFAULT", "OFFSETS", "TSEQUAL", "DELETE", "ON", "UNION", "DENY", "OPEN", "UNIQUE", "DESC", "OPENDATASOURCE", "UNPIVOT", "DISK", "OPENQUERY", "UPDATE", "DISTINCT", "OPENROWSET", "UPDATETEXT", "DISTRIBUTED", "OPENXML", "USE", "DOUBLE", "OPTION", "USER", "DROP", "OR", "VALUES", "DUMP", "ORDER", "VARYING", "ELSE", "OUTER", "VIEW", "END", "OVER", "WAITFOR", "ERRLVL", "PERCENT", "WHEN", "ESCAPE", "PIVOT", "WHERE", "EXCEPT", "PLAN", "WHILE", "EXEC", "PRECISION", "WITH", "EXECUTE", "PRIMARY", "WITHIN", "GROUP", "EXISTS", "PRINT", "WRITETEXT", "EXIT", "PROC");

      final static public function WHERE($arr, $required = false)
      {
          $append = $values = array();
          $flag   = 0;
          foreach ($arr as $k => $v) {
              if (!is_numeric($k) && !empty($v)) {
                  if (in_array(strtoupper($k), self::$RESERVED_KEYWORDS)): array_push($append, "`" . $k . "`=?");
                  else: array_push($append, "" . $k . "=?"); endif;
                  array_push($append, 'AND');
                  array_push($values, $v);
                  $flag = 1;
              } else {
                  if ($flag === 0 && $required === TRUE) throw new \Exception("An error has occured");
                  if ($flag === 1) array_pop($append);
                  array_push($append, $v);
                  $flag = 2;
              }
          }

          if ($flag === 1) 
          { array_pop($append); }

          $types = "";
          foreach ($values as $v) 
          { $types .= self::getType($v); }

          foreach (array_keys($values) as $i) 
          { $values[$i] =& $values[$i]; }

          // fix
          if (!empty($types)) array_unshift($values, $types);
          return array( $append, $values );
      }

      /**
       * Get initial of type in which is needed to bind the proper types when executing to the database
       * @param string $type               <T>
       * @return char
       */
      final static private function getType($type)
      {
          switch (gettype($type)) {
              case "string": return "s";
              case "boolean": // bool is recognized as an integer
              case "integer": return "i";
              case "double": return "d";
              default: throw new \Error("Unable to bind params");
          }
      }

      final static public function SELECT($columns, $table, $where, $explain = false)
      {
          $columns = (count($columns) > 0) ? $columns : array("*");

          foreach ($columns as $k => $v) {
              if (in_array(strtoupper($v), self::$RESERVED_KEYWORDS)) 
                  $columns[$k] = "`" . $v . "`";
          }

          $where = (count($where) > 0) ? self::WHERE($where) : NULL;
          $sql   = (($explain) ? "EXPLAIN " : "");

          if (in_array(strtoupper($table), self::$RESERVED_KEYWORDS)) 
          { $table = "`" . $table . "`"; }

          // $sql .= "SELECT [" . join(", ", $columns) . "] FROM " . $table;
          $sql .= "SELECT " . join(", ", $columns) . " FROM " . $table;

          if ($where != NULL && count($where[1]) > 1 && isset($where[0])) 
          { $sql .= " WHERE " . join(" ", $where[0]); } 
          elseif (isset($where[0])) 
          { $sql .= " " . join(" ", $where[0]); }

          return array( $sql, (isset($where[1])) ? $where[1] : NULL );
      }
      final static public function INSERT($table, $object)
      {
          if (in_array(strtoupper($table), self::$RESERVED_KEYWORDS)) 
          { $table = "`" . $table . "`"; }
          $sql     = "INSERT INTO " . $table;
          $names   = array();
          $replace = array();
          $values  = array();
          foreach ($object as $k => $v) {
              if (isset($k) && isset($v)) {
                  if (is_numeric($k)) return;
                  array_push($names, "`" . $k . "`");
                  array_push($replace, "?");
                  array_push($values, $v);
              }
          }
          $sql .= " (" . join(", ", $names) . ") VALUES ";
          $sql .= " (" . join(", ", $replace) . ")";

          $types = "";
          foreach ($values as $v) 
          { $types .= self::getType($v); }

          foreach (array_keys($values) as $i) 
          { $values[$i] =& $values[$i]; }

          array_unshift($values, $types);

          return array( $sql, $values );
      }
      final static public function UPDATE($table, $object, $where)
      {
          if (in_array(strtoupper($table), self::$RESERVED_KEYWORDS)) 
          { $table = "`" . $table . "`"; }

          $insert = array();
          $values = array();
          $where  = (count($where) > 0) ? self::WHERE($where, TRUE) : NULL;
          $sql    = "UPDATE " . $table . " SET";
          foreach ($object as $k => $v) {
              if (isset($k) && isset($v)) {
                  if (is_numeric($k)) continue;
                  if (in_array(strtoupper($k), self::$RESERVED_KEYWORDS)): array_push($insert, "`" . $k . "`=?");
                  else: array_push($insert, "" . $k . "=?"); endif;
                  array_push($values, $v);
              }
          }
          $sql .= " " . join(", ", $insert);
          if ($where != NULL) 
          { $sql .= " WHERE " . join(" ", $where[0]); }

          $types = "";

          foreach ($values as $v) 
          { $types .= self::getType($v); }

          if ($where != NULL) {
              $types .= $where[1][0];
              array_shift($where[1]);
          }

          $ni = count($values);

          foreach ($where[1] as $k => $v) {
              $values[$ni] = $v;
              $ni++;
          }

          foreach (array_keys($values) as $i) 
          { $values[$i] =& $values[$i]; }

          array_unshift($values, $types);
          return array( $sql, $values );
      }
      final static public function TRUNCATE($table)
      {
          $sql = "TRUNCATE TABLE `" . $table . "`";
          return array( $sql );
      }
      final static public function DELETE($table, $where)
      {
          $sql = "DELETE FROM `" . $table . "`";
          if (count($where) > 0) {
              $where = self::WHERE($where);
              $sql .= " WHERE " . join(" ", $where[0]);
          }
          return array( $sql, $where[1] );
      }
  }
  
}
