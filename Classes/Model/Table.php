<?php

namespace DBSync\Model;

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

use \DBSync\Driver\Type;

/**
 * Table model
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class Table {

  /**
   * Table's name
   * 
   * @var string 
   */
  protected $name;

  /**
   * Table's columns
   * 
   * @var array
   */
  protected $columns = array();

  /**
   * Table's indexes
   * 
   * @var array
   */
  protected $indexes = array();

  /**
   * Table's option
   * 
   * @var array
   */
  protected $options = array();

  /**
   * Constructor
   * 
   * @param string $name
   * @param \DBSync\Driver\AbstractDatabaseDriver $driver
   */
  public function __construct($name) {
    $this->name = $name;
  }

  /**
   * Get table's name
   * 
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Append a column to table
   * 
   * @param \DBSync\Model\Column $column
   * @return \DBSync\Model\Table
   * 
   */
  public function addColumn(Column $column) {
    if (!in_array($column, $this->columns)) {
      $column->setTable($this);
      $this->columns[] = $column;
    }
    return $this;
  }

  /**
   * Remove a column from table
   * 
   * @param \DBSync\Model\Column $column
   * @return \DBSync\Model\Table
   */
  public function removeColumn(Column $column) {
    if (($key = array_search($column, $this->columns)) !== false) {
      array_splice($this->columns, $key, 1);
    }
    return $this;
  }

  /**
   * Get table's columns
   * 
   * @return array
   */
  public function getColumns() {
    return $this->columns;
  }

  /**
   * Append an indexe to table
   * 
   * @param \DBSync\Model\Indexe\AbstractIndexe $indexe
   * @return \DBSync\Model\Table
   * 
   */
  public function addIndexe(Indexe\AbstractIndexe $indexe) {
    if (!in_array($indexe, $this->indexes)) {
      $indexe->setTable($this);
      $this->indexes[] = $indexe;
    }
    return $this;
  }

  /**
   * Remove an indexe from table
   * 
   * @param \DBSync\Model\Indexe $indexe
   * @return \DBSync\Model\Table
   */
  public function removeIndexe(Indexe $indexe) {
    if (($key = array_search($indexe, $this->indexes)) !== false) {
      array_splice($this->indexes, $key, 1);
    }
    return $this;
  }

  /**
   * Get table's indexes
   * 
   * @return array
   */
  public function getIndexes() {
    return $this->indexes;
  }

  /**
   * Append a option to table
   * 
   * @param \DBSync\Model\Option\AbstractOption $option
   * @return \DBSync\Model\Table
   * 
   */
  public function addOption(Option\AbstractOption $option) {
    if (!in_array($option, $this->options)) {
      $option->setTable($this);
      $this->options[] = $option;
    }
    return $this;
  }

  /**
   * Remove a option from table
   * 
   * @param \DBSync\Model\Option\AbstractOption $option
   * @return \DBSync\Model\Table
   */
  public function removeOption(Option\AbstractOption $option) {
    if (($key = array_search($option, $this->options)) !== false) {
      array_splice($this->options, $key, 1);
    }
    return $this;
  }

  /**
   * Get table's options
   * 
   * @return array
   */
  public function getOptions() {
    return $this->options;
  }

  /**
   * Render SQL query for table creation
   * 
   * @param string $type
   * 
   * @return array
   */
  public function create($type) {
    switch ($type) {
      case Type::MYSQL:
        return $this->createMysql();
    }
    return array();
  }

  /**
   * Render SQL query for table destruction
   * 
   * @param string $type
   * @return array
   */
  public function drop($type) {
    switch ($type) {
      case Type::MYSQL:
        return array('DROP TABLE `' . $this->name . '`');
    }
    return array();
  }

  /**
   * Render SQL queries for table alteration based on reference
   * 
   * @param string $type
   * @param \DBSync\Model\Table $reference
   * 
   * @return array
   */
  public function alter($type, Table $reference) {
    switch ($type) {
      case Type::MYSQL:
        return $this->alterMysql($reference);
    }
    return array();
  }

  /**
   * Render SQL query for table creation for MySQL
   * 
   * @return array
   */
  protected function createMysql() {
    $queries = array();
    $result = 'CREATE TABLE `' . $this->name . '` (';
    $columns = array();
    foreach ($this->columns as $column) {
      $columns[] = $column->create(Type::MYSQL);
    }
    $result .= implode(',', array_filter($columns));
    $result .= ')';
    foreach ($this->options as $option) {
      $result .= ' ' . $option->create(Type::MYSQL);
    }
    $queries[] = $result;
    foreach($this->indexes as $indexe){
      $queries[] = $indexe->add(Type::MYSQL);
    }
    foreach ($this->columns as $column) {
      if($column->hasAutoIncrement()){
        $queries[] = $column->alter(Type::MYSQL);
      }
    }
    return $queries;
  }
  
  /**
   * Render SQL queries for table option alteration
   *  
   * @param string $type
   * @param \DBSync\Model\Table $reference
   * 
   * @return array
   */
  protected function alterOptions($type, Table $reference){
    $queries = array();
    $options = $this->getOptions();
    foreach($reference->getOptions() as $option){
      $optionClassname = get_class($option);
      $found = FALSE;
      foreach($options as $rkey => $roption){
        if($optionClassname === get_class($roption)){
          if($option->getValue($type) !== $roption->getValue($type)){
            $queries[] = $option->alter($type);
          }
          $found = TRUE;
          unset($options[$rkey]);
          break;
        }
      }
      if(!$found){
        $queries[] = $option->alter($type);
      }
    }
    foreach($options as $option){
      $queries[] = $option->drop($type);
    }
    return array_filter($queries);
  }
  
  /**
   * Render SQL queries for table column alteration
   *  
   * @param string $type
   * @param \DBSync\Model\Table $reference
   * 
   * @return array
   */
  protected function alterColumns($type, Table $reference){
    $queries = array();
    $columns = $this->getColumns();
    foreach($reference->getColumns() as $column){
      $found = FALSE;
      foreach($columns as $rkey => $rcolumn){
        if($column->getName() === $rcolumn->getName()){
          if(!$column->equals($rcolumn)){
            $queries[] = $column->alter($type);
          }
          $found = TRUE;
          unset($columns[$rkey]);
          break;
        }
      }
      if(!$found){
        $queries[] = $column->add($type);
      }
    }
    foreach($columns as $column){
      $queries[] = $column->drop($type);
    }
    return array_filter($queries);
  }
  
  /**
   * Render SQL queries for table indexe alteration
   *  
   * @param string $type
   * @param \DBSync\Model\Table $reference
   * 
   * @return array
   */
  protected function alterIndexes($type, Table $reference){
    $drop = array();
    $add = array();
    $indexes = $this->getIndexes();
    foreach($reference->getIndexes() as $indexe){
      $found = FALSE;
      foreach($indexes as $rkey => $rindexe){
        if($indexe->equals($rindexe)){
          $found = TRUE;
          unset($indexes[$rkey]);
          break;
        }
      }
      if(!$found){
        $add[] = $indexe->add($type);
      }
    }
    foreach($indexes as $indexe){
        $drop[] = $indexe->drop($type);
    }
    return array_merge(array_filter($drop), array_filter($add));
  }

  /**
   * Render SQL queries for table alteration based on reference for MySQL
   * 
   * @param \DBSync\Model\Table $reference
   * 
   * @return array
   */
  protected function alterMysql(Table $reference) {
    $queries = array();
    if ($this->name !== $reference->getName()) {
      $queries[] = 'RENAME TABLE `' . $this->name . '` TO `' . $reference->getName() . '`';
      $this->name = $reference->getName();
    }
    
    // Alter options
    $queries = array_merge($queries, $this->alterOptions(Type::MYSQL, $reference));
    
    // Alter columns
    $queries = array_merge($queries, $this->alterColumns(Type::MYSQL, $reference));
    
    // Alter indexes
    $queries = array_merge($queries, $this->alterIndexes(Type::MYSQL, $reference));
    
    return array_filter($queries);
  }

}
