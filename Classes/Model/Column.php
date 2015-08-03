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
use \DBSync\Model\Table;
use \DBSync\Model\Type\AbstractType;
use \DBSync\Model\Definition\AbstractDefinition;

/**
 * Model for table column
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class Column {

  /**
   * Column's name
   * 
   * @var string
   */
  protected $name;

  /**
   * Column's parent table
   * 
   * @var \DBSync\Model\Table 
   */
  protected $table;

  /**
   * Column positionned before current in table
   * 
   * @var \DBSync\Model\Column 
   */
  protected $previous = NULL;

  /**
   * Column's type
   * 
   * @var \DBSync\Model\Type\AbstractType 
   */
  protected $type;

  /**
   * Column's definition
   * 
   * @var array 
   */
  protected $definitions = array();

  /**
   * Constructor
   * @param string $name
   * @param \DBSync\Model\Type\AbstractType $type
   */
  public function __construct($name, AbstractType $type) {
    $this->name = $name;
    $this->type = $type;
  }

  /**
   * Get column's name
   * 
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Set parent table
   * 
   * @param \DBSync\Model\Table $table
   * @return \DBSync\Model\Column
   */
  public function setTable(Table $table) {
    $this->table = $table;
    return $this;
  }

  /**
   * Set previous column in table
   * 
   * @param \DBSync\Model\Column $column
   * @return \DBSync\Model\Column
   */
  public function setPrevious(Column $column = NULL) {
    $this->previous = $column;
    return $this;
  }

  /**
   * Get column's type
   * 
   * @return \DBSync\Model\Type\AbstractType 
   */
  public function getType() {
    return $this->type;
  }

  /**
   * Get previous column in table
   * 
   * @return \DBSync\Model\Column
   */
  public function getPrevious() {
    return $this->previous;
  }

  /**
   * Append a definition to table
   * 
   * @param \DBSync\Model\Definition\AbstractDefinition $definition
   * @return \DBSync\Model\Table
   * 
   */
  public function addDefinition(AbstractDefinition $definition) {
    if (!in_array($definition, $this->definitions)) {
      $definition->setColumn($this);
      $this->definitions[] = $definition;
    }
    return $this;
  }

  /**
   * Remove a definition from table
   * 
   * @param \DBSync\Model\Definition\AbstractDefinition $definition
   * @return \DBSync\Model\Table
   */
  public function removeDefinition(AbstractDefinition $definition) {
    if (($key = array_search($definition, $this->definitions)) !== false) {
      array_splice($this->definitions, $key, 1);
    }
    return $this;
  }

  /**
   * Get table's definitions
   * 
   * @return array
   */
  public function getDefinitions() {
    return $this->definitions;
  }

  /**
   * Check if given column is equals to current
   * 
   * @param \DBSync\Model\Column $column
   * 
   * @return boolean
   */
  public function equals(Column $column) {
    if ($this->previous !== NULL && $column->getPrevious() !== NULL &&
            $this->previous->getName() !== $column->getPrevious()->getName()) {
      return FALSE;
    }
    if(!$this->type->equals($column->getType())){
      return FALSE;
    }
    $definitions = $column->getDefinitions();
    foreach($this->definitions as $definition){
      $definitionClassname = get_class($definition);
      $found = FALSE;
      foreach($definitions as $rkey => $rdefinition){
        if($definitionClassname === get_class($rdefinition)){
          if($definition->getValue() !== $rdefinition->getValue()){
            return FALSE;
          }
          $found = TRUE;
          unset($definitions[$rkey]);
          break;
        }
      }
      if(!$found){
        return FALSE;
      }
    }
    if(count($definitions) > 0){
      return FALSE;
    }
    return TRUE;
  }
  
  /**
   * Check if column has auto increment definition
   * 
   * @return boolean
   */
  public function hasAutoIncrement(){
    foreach ($this->definitions as $definition) {
      if(get_class($definition) !== 'DBSync\\Model\\Definition\\AutoIncrement'){
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Render SQL query part for table creation
   * 
   * @param string $type
   * @return string
   */
  public function create($type) {
    switch ($type) {
      case Type::MYSQL:
        return $this->createMysql();
    }
    return '';
  }

  /**
   * Render SQL query for column change
   * 
   * @param string $type
   * @return string
   */
  public function alter($type) {
    switch ($type) {
      case Type::MYSQL:
        return $this->alterMysql();
    }
    return '';
  }

  /**
   * Render SQL query for column add
   * 
   * @param string $type
   * @return string
   */
  public function add($type) {
    switch ($type) {
      case Type::MYSQL:
        return $this->addMysql();
    }
    return '';
  }

  /**
   * Render SQL query for column drop
   * 
   * @param string $type
   * @return string
   */
  public function drop($type) {
    switch ($type) {
      case Type::MYSQL:
        return $this->dropMysql();
    }
    return '';
  }

  /**
   * Render SQL query for table creation for MySQL
   * 
   * @return string
   */
  protected function createMysql($no_auto_increment=TRUE) {
    $elements = array('`' . $this->name . '`', $this->type->render(Type::MYSQL));
    foreach ($this->definitions as $definition) {
      if(!$no_auto_increment || get_class($definition) !== 'DBSync\\Model\\Definition\\AutoIncrement'){
        $elements[] = $definition->render(Type::MYSQL);
      }
    }
    return implode(' ', array_filter($elements));
  }

  /**
   * Render SQL query for column change for MySQL
   * 
   * @return string
   */
  protected function alterMysql() {
    return 'ALTER TABLE `' . $this->table->getName() . '` '
            . 'CHANGE `' . $this->name . '` '
            . $this->createMysql(FALSE) . ' '
            . ($this->previous === NULL ? 'FIRST' : 'AFTER `' . $this->previous->getName() . '`')
    ;
  }

  /**
   * Render SQL query for column change for MySQL
   * 
   * @return string
   */
  protected function addMysql() {
    return 'ALTER TABLE `' . $this->table->getName() . '` '
            . 'ADD ' . $this->createMysql(FALSE) . ' '
            . ($this->previous === NULL ? 'FIRST' : 'AFTER `' . $this->previous->getName() . '`');
  }

  /**
   * Render SQL query for column change for MySQL
   * 
   * @return string
   */
  protected function dropMysql() {
    return 'ALTER TABLE `' . $this->table->getName() . '` '
            . 'DROP `' . $this->name . '`';
  }

}
