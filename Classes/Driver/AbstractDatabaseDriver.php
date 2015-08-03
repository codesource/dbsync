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


/**
 * Interface for a database driver
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
abstract class AbstractDatabaseDriver {
  
  const VERBOSE_NONE = 0;
  const VERBOSE_OK = 1;
  const VERBOSE_ERROR = 2;
  const VERBOSE_WARNING = 3;
  
  /**
   * Constructor
   * 
   * @param string $host
   * @param string $port
   * @param string $username
   * @param string $password
   * @param array $options
   */
  public function __construct($host, $database, $username, $password='', $port=NULL, $options=array()){
    $this->connect($host, $database, $username, $password, $port, $options);
  }
  
  /**
   * Get database driver type
   * 
   * @return string
   */
  abstract public function getType();
  
  /**
   * Connect the database
   * 
   * @param string $host
   * @param string $port
   * @param string $username
   * @param string $password
   * @param array $options
   */
  abstract public function connect($host, $database, $username, $password='', $port=NULL, $options=array());
  
  /**
   * Commit change to database
   * 
   * @params array $queries
   * @params boolean $verbose
   * 
   * @return boolean
   */
  abstract public function commit(array $queries, $verbose= FALSE);
  
  /**
   * Send a dry run to database
   * 
   * @params array $queries
   * @params boolean $verbose
   * 
   * @return boolean
   */
  abstract public function dryrun(array $queries, $verbose= FALSE);
  
  /**
   * Load all tables name of the database
   * 
   * @return array
   */
  abstract public function listTables();
  
  /**
   * Load table structure
   * 
   * @param string $name
   * 
   * @return \DBSync\Model\Table
   */
  abstract public function loadTableStructure($name);
  
  /**
   * Load table datas
   * 
   * @param string $name
   * 
   * @return array
   */
  abstract public function loadTableDatas($name);
  
  /**
   * Log action
   * 
   * @param string $text
   * @param string $prefix
   * @param integer $status
   */
  protected function log($text, $prefix='', $status=self::VERBOSE_NONE){
    $status_text = '';
    switch($status){
      case self::VERBOSE_OK:
        $status_text = ' [OK] - ';
        break;
      case self::VERBOSE_ERROR:
        $status_text = ' [ERROR] - ';
        break;
      case self::VERBOSE_WARNING:
        $status_text = ' [WARNING] - ';
        break;
    }
    print(date('[d-m-Y H:i:s]: '). $prefix . $status_text . $text  . "\n");
  }
}
