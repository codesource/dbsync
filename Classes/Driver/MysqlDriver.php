<?php

namespace DBSync\Driver;

/*
 * Copyright (C) 2015 Matthias Toscanelli <m.toscanelli@code-source.ch>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

use \DBSync\Model\Table;
use \DBSync\Model\Column;
use \DBSync\Model\Definition;
use \DBSync\Model\Option;
use \DBSync\Model\Indexe;

/**
 * MySQL database driver
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class MysqlDriver extends AbstractDatabaseDriver {
  
  /**
   * Database name
   * 
   * @var string
   */
  protected $database;

  /**
   * Database connexion object
   * 
   * @var \mysqli 
   */
  protected $connexion;

  public function connect($host, $database, $username, $password = '', $port = NULL, $options = array()) {
    if ($port === NULL) {
      $port = 3306;
    }
    $this->connexion = new \mysqli($host, $username, $password, $database, $port);
    if ($this->connexion->connect_errno) {
      throw new Exception\ConnexionException($this->connexion->connect_error, $this->connexion->connect_errno);
    }
    $availableOptions = array(
        'MYSQLI_OPT_CONNECT_TIMEOUT',
        'MYSQLI_OPT_LOCAL_INFILE',
        'MYSQLI_INIT_COMMAND',
        'MYSQLI_READ_DEFAULT_FILE',
        'MYSQLI_READ_DEFAULT_GROUP',
        'MYSQLI_SERVER_PUBLIC_KEY'
    );
    foreach ($options as $option => $value) {
      if (in_array($option, $availableOptions)) {
        if (!$this->connexion->options($option, $value)) {
          throw new Exception\OptionException("Unable to set option '" . $option . "' with the value '" . $value . "'", 1438348640);
        }
      } else {
        throw new Exception\OptionException("Wrong connexion option '" . $option . "'", 1438348641);
      }
    }
    $this->database = $database;
  }
  
  /**
   * Commit change to database
   * NOTE: As rollback only work on non-structural query, it will only work on datas
   * 
   * @params array $queries
   * @params boolean $verbose
   * 
   * @return boolean
   */
  public function commit(array $queries, $verbose=FALSE) {
    if(!$this->connexion->autocommit(FALSE)){
      $this->log('AUTOCOMMIT can\'t be turned off!', 'COMMIT - ', self::VERBOSE_ERROR);
      return FALSE;
    }
    foreach($queries as $query){
      if(strlen($query) > 0){
        if($this->connexion->query($query) === FALSE){
          $this->connexion->rollback();
          if($verbose){
            $this->log($query, 'COMMIT - ', self::VERBOSE_ERROR);
          }
          return FALSE;
        }elseif($verbose){
          $this->log($query, 'COMMIT - ', self::VERBOSE_OK);
        }
      }
    }
    $this->connexion->commit();
    return TRUE;
  }
  
  /**
   * Send a dry run to database
   * NOTE: Queries are only listed because MySQL does not allow rollback on structural query
   * 
   * @params array $queries
   * @params boolean $verbose
   * 
   * @return boolean
   */
  public function dryrun(array $queries, $verbose=FALSE) {
    foreach($queries as $query){
      if(strlen($query) > 0){
        if($verbose){
          $this->log($query, 'DRY RUN - ', self::VERBOSE_OK);
        }
      }
    }
    return TRUE;
  }

  /**
   * Get database driver type
   * 
   * @return string
   */
  public function getType() {
    return Type::MYSQL;
  }

  /**
   * Load all tables name of the database
   * 
   * @return array
   */
  public function listTables() {
    $tables = array();
    $query = "SHOW TABLES";
    if (($result = $this->connexion->query($query))) {
      while (($table = $result->fetch_assoc())) {
        $tables[] = array_pop($table);
      }
      $result->close();
    } else {
      throw new Exception\QueryException("Unable to send query '" . $query . "'", 1438348642);
    }
    return $tables;
  }

  public function loadTableStructure($name) {
    $table = new Table($name);

    // Get table information
    $query = "SHOW TABLE STATUS WHERE Name='" . $table->getName() . "'";
    if (($result = $this->connexion->query($query))) {
      $column = NULL;
      if (($datas = $result->fetch_assoc())) {
        $table->addOption(new Option\Engine($datas['Engine']));
        $table->addOption(new Option\Collation($datas['Collation']));
        if (strlen($datas['Comment']) > 0) {
          $table->addOption(new Option\Comment($this->connexion->escape_string($datas['Comment'])));
        }
        if (strlen($datas['Auto_increment']) > 0) {
          $table->addOption(new Option\AutoIncrement(intval($datas['Auto_increment'])));
        }
      } else {
        throw new Exception\QueryException("Unable to load table informations for '" . $table->getName() . "'", 1438516800);
      }
      $result->close();
    } else {
      throw new Exception\QueryException("Unable to load table informations for '" . $table->getName() . "'", 1438516800);
    }

    // Get columns
    $query = "SHOW FULL COLUMNS FROM `" . $table->getName() . "`";
    if (($result = $this->connexion->query($query))) {
      $column = NULL;
      while (($datas = $result->fetch_assoc())) {
        $column = $this->getColumnFromDatas($datas)->setPrevious($column);
        $table->addColumn($column);
      }
      $result->close();
    }

    // Get indexes
    $indexes = array();
    $query = "SHOW INDEXES FROM `" . $table->getName() . "`";
    if (($result = $this->connexion->query($query))) {
      while (($datas = $result->fetch_assoc())) {
        $column = array(
            'name' => $datas['Column_name'],
            'length' => intval($datas['Sub_part'])
        );
        if($datas['Index_type'] === 'BTREE'){
          $type = Indexe\AbstractIndexe::TYPE_BTREE;
        }elseif($datas['Index_type'] === 'HASH'){
          $type = Indexe\AbstractIndexe::TYPE_HASH;
        }else{
          $type = Indexe\AbstractIndexe::TYPE_NONE;
        }
        if(isset($indexes[$datas['Key_name']])){
          $position = intval($datas['Seq_in_index']);
          $indexes[$datas['Key_name']]->addColumn($column, $position > 0 ? $position - 1 : 0);
        }else{
          if($datas['Key_name'] === 'PRIMARY'){
            $indexes[$datas['Key_name']] = new Indexe\Primary('PRIMARY', array($column), $type, $datas['Index_comment']);
          }elseif($datas['Index_type'] === 'FULLTEXT'){
            $indexes[$datas['Key_name']] = new Indexe\Fulltext($datas['Key_name'], array($column), $type, $datas['Index_comment']);
          }elseif(!$datas['Non_unique']){
            $indexes[$datas['Key_name']] = new Indexe\Unique($datas['Key_name'], array($column), $type, $datas['Index_comment']);
          }else{
            $indexes[$datas['Key_name']] = new Indexe\Key($datas['Key_name'], array($column), $type, $datas['Index_comment']);
          }
        }
      }
      $result->close();
    }
    if($this->connexion->select_db('information_schema')){
      $query = "SELECT * FROM KEY_COLUMN_USAGE AS k "
              . "JOIN REFERENTIAL_CONSTRAINTS AS r ON (k.CONSTRAINT_NAME=r.CONSTRAINT_NAME) "
              . "WHERE k.TABLE_SCHEMA='".$this->database."' AND k.TABLE_NAME='".$table->getName()."'";
      if (($result = $this->connexion->query($query))) {
        while (($datas = $result->fetch_assoc())) {
          $column = array(
              'name' => $datas['COLUMN_NAME'],
              'length' => 0
          );
          if(isset($indexes[$datas['CONSTRAINT_NAME']])){
            $position = intval($datas['ORDINAL_POSITION']);
            $indexes[$datas['CONSTRAINT_NAME']]->addColumn($column, $position > 0 ? $position -1 : 0);
            $rposition = intval($datas['POSITION_IN_UNIQUE_CONSTRAINT']);
            $indexes[$datas['CONSTRAINT_NAME']]->addReferenceColumn($datas['REFERENCED_COLUMN_NAME'], $rposition > 0 ? $rposition - 1 : 0);
          }else{
            $indexes[$datas['CONSTRAINT_NAME']] = new Indexe\Foreign($datas['CONSTRAINT_NAME'], array($column));
            $indexes[$datas['CONSTRAINT_NAME']]->setReference(array(
                'name' => $datas['REFERENCED_TABLE_NAME'],
                'columns' => array($datas['REFERENCED_COLUMN_NAME'])
            ));
            switch($datas['UPDATE_RULE']){
              case 'NO ACTION':
                $indexes[$datas['CONSTRAINT_NAME']]->setOnUpdate(Indexe\Foreign::REFERENCE_OPTION_NO_ACTION);
              case 'CASCADE':
                $indexes[$datas['CONSTRAINT_NAME']]->setOnUpdate(Indexe\Foreign::REFERENCE_OPTION_CASCADE);
              case 'SET NULL':
                $indexes[$datas['CONSTRAINT_NAME']]->setOnUpdate(Indexe\Foreign::REFERENCE_OPTION_SET_NULL);
              case 'RESTRICT':
                $indexes[$datas['CONSTRAINT_NAME']]->setOnUpdate(Indexe\Foreign::REFERENCE_OPTION_RESTICT);
            }
            switch($datas['DELETE_RULE']){
              case 'NO ACTION':
                $indexes[$datas['CONSTRAINT_NAME']]->setOnDelete(Indexe\Foreign::REFERENCE_OPTION_NO_ACTION);
              case 'CASCADE':
                $indexes[$datas['CONSTRAINT_NAME']]->setOnDelete(Indexe\Foreign::REFERENCE_OPTION_CASCADE);
              case 'SET NULL':
                $indexes[$datas['CONSTRAINT_NAME']]->setOnDelete(Indexe\Foreign::REFERENCE_OPTION_SET_NULL);
              case 'RESTRICT':
                $indexes[$datas['CONSTRAINT_NAME']]->setOnDelete(Indexe\Foreign::REFERENCE_OPTION_RESTICT);
            }
          }
        }
        $result->close();
      }
      $this->connexion->select_db($this->database);
    }else{
        throw new Exception\QueryException("Unable to select 'information_schema' database", 1438526800);
    }
    foreach($indexes as $indexe){
      $table->addIndexe($indexe);
    }
    
    return $table;
  }

  public function loadTableDatas($table) {
    return array();
  }

  /**
   * Make sure that connexion is closed on destroy
   */
  public function __destruct() {
    if ($this->connexion) {
      $this->connexion->close();
    }
  }

  /**
   * Parse data from query result and return a column
   * 
   * @param array $datas
   * @return Column
   * @throws Exception\TypeException
   */
  protected function getColumnFromDatas(array $datas) {
    $matches = array();
    if (preg_match('/^([a-z]+)(\(([0-9]+)(,([0-9]+))?\))?$/', $datas['Type'], $matches)) {
      $typeClass = '\\DBSync\\Model\\Type\\' . ucfirst($matches[1]);
      if (class_exists($typeClass)) {
        $attributes = array();
        if (isset($matches[3])) {
          $attributes['length'] = intval($matches[3]);
        }
        if (isset($matches[5])) {
          $attributes['decimal'] = intval($matches[5]);
        }
        if (isset($datas['Collation'])) {
          $attributes['collation'] = $datas['Collation'];
        }
      } else {
        throw new Exception\TypeException("Unable to find type '" . $datas['Type'] . "'", 1438377269);
      }
    } elseif (preg_match('/^([a-z]+)\((\'.*\')\)$/X', $datas['Type'], $matches)) {
      $typeClass = '\\DBSync\\Model\\Type\\' . ucfirst($matches[1]);
      if (class_exists($typeClass)) {
        $substitute = '{!--!}';
        $values = str_replace("''", $substitute, $matches[2]);
        if (preg_match_all('/(?:^|,)(?=[^\']|(\')?)\'?((?(1)[^\']*|[^,\']*))\'?(?=,|$)?/', $values, $matches) !== FALSE) {
          $attributes = array('values' => array());
          foreach (str_replace($substitute, "'", $matches[2]) as $value) {
            $attributes['values'][] = $this->connexion->escape_string($value);
          }
        } else {
          throw new Exception\TypeException("Unable to parse value for type '" . $datas['Type'] . "'", 1438377270);
        }
      } else {
        throw new Exception\TypeException("Unable to find type '" . $datas['Type'] . "'", 1438377269);
      }
    } else {
      throw new Exception\TypeException("Unable to find type '" . $datas['Type'] . "'", 1438377269);
    }

    $column = new Column($datas['Field'], new $typeClass($attributes));

    $nullable = new Definition\Nullable(strtolower($datas['Null']) === 'yes');
    $column->addDefinition($nullable);
    if ($datas['Default'] !== NULL) {
      $column->addDefinition(new Definition\DefaultValue($this->connexion->escape_string($datas['Default'])));
    } elseif ($nullable->getValue()) {
      $column->addDefinition(new Definition\DefaultValue(NULL));
    }
    if($datas['Extra'] === 'auto_increment'){
      $column->addDefinition(new Definition\AutoIncrement());
    }
    $column->addDefinition(new Definition\Comment($this->connexion->escape_string($datas['Comment'])));

    return $column;
  }

}
