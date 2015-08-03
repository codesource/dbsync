<?php

namespace DBSync\Model\Indexe;

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
 * Table foreign key model
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class Foreign extends AbstractIndexe {

  const REFERENCE_OPTION_NONE = 0;
  const REFERENCE_OPTION_NO_ACTION = 1;
  const REFERENCE_OPTION_CASCADE = 2;
  const REFERENCE_OPTION_SET_NULL = 3;
  const REFERENCE_OPTION_RESTICT = 4;

  /**
   * Foreign key reference
   * (table + columns)
   * 
   * @var array 
   */
  protected $reference;

  /**
   * Foreign key on delete
   * 
   * @var integer
   */
  protected $onDelete = self::REFERENCE_OPTION_NONE;

  /**
   * Foreign key on update
   * 
   * @var integer
   */
  protected $onUpdate = self::REFERENCE_OPTION_NONE;

  /**
   * Check if given indexe equals current
   * 
   * @param \DBSync\Model\Indexe\AbstractIndexe $indexe
   * 
   * @return boolean
   */
  public function equals(AbstractIndexe $indexe) {
    if (!parent::equals($indexe)) {
      return FALSE;
    }
    $reference = $indexe->getReference();
    if (isset($this->reference['name'])) {
      if (!isset($reference['name']) || $this->reference['name'] !== $reference['name']) {
        return FALSE;
      }
    } elseif (isset($reference['name'])) {
      return FALSE;
    }
    if (is_array($this->reference['columns'])) {
      if (!is_array($reference['columns']) ||
              count(array_diff_assoc($this->reference['columns'], $reference['columns'])) > 0) {
        return FALSE;
      }
    } elseif (is_array($reference['columns'])) {
      return FALSE;
    }
    if($this->onDelete !== $indexe->getOnDelete()){
      return FALSE;
    }
    if($this->onUpdate !== $indexe->getOnUpdate()){
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Get foreign key on delete rule
   * 
   * @return array
   */
  public function getOnDelete() {
    return $this->onDelete;
  }

  /**
   * Get foreign key on update rule
   * 
   * @return array
   */
  public function getOnUpdate() {
    return $this->onUpdate;
  }

  /**
   * Get foreign key reference
   * 
   * @return array
   */
  public function getReference() {
    return $this->reference;
  }

  /**
   * Set foreign key reference
   * 
   * @param array $reference
   * @return \DBSync\Model\Indexe\Foreign
   */
  public function setReference(array $reference) {
    $this->reference = $reference;
    return $this;
  }
  
  /**
   * Add a column to reference
   * 
   * @param string $name
   * @param integer $position
   * @return \DBSync\Model\Indexe\Foreign
   */
  public function addReferenceColumn($name, $position){
    if(is_array($this->reference['columns'])){
      $columns = array();
      $inserted = FALSE;
      for($i=0, $ni=count($this->reference['columns']); $i< $ni; $i++){
        if($i=== $position){
          $columns[] = $name;
          $inserted = TRUE;
        }
        $columns[] = $this->reference['columns'][$i];
      }
      if(!$inserted){
          $columns[] = $name;
      }
      $this->reference['columns'] = $columns;
    }else{
      $this->reference['columns'] = array($name);
    }
    return $this;
  }

  /**
   * Set on delete option
   * 
   * @param integer $option
   * 
   * @return \DBSync\Model\Indexe\Foreign
   */
  public function setOnDelete($option) {
    $this->onDelete = $option;
    return $this;
  }

  /**
   * Set on delete update
   * 
   * @param integer $option
   * 
   * @return \DBSync\Model\Indexe\Foreign
   */
  public function setOnUpdate($option) {
    $this->onDelete = $option;
    return $this;
  }

  /**
   * Check if indexe is available for database driver
   * 
   * @param string $type
   * @return boolean
   */
  public function isAvailable($type) {
    switch ($type) {
      case Type::MYSQL:
        return TRUE;
    }
    return FALSE;
  }

  /**
   * Render SQL add query for database driver 
   * 
   * @param string $type
   * 
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
   * Render SQL drop query for database driver 
   * 
   * @param string $type
   * 
   * @return string
   */
  public function drop($type) {
    switch ($type) {
      case Type::MYSQL:
        return 'ALTER TABLE `' . $this->table->getName() . '` DROP FOREIGN KEY ' . $this->name;
    }
    return '';
  }

  protected function addMysql() {
    if (isset($this->reference['name'])) {
      $result = 'ALTER TABLE `' . $this->table->getName() . '` ADD FOREIGN KEY ' . $this->name;
      $columns = array();
      foreach ($this->columns as $column) {
        $columns[] = '`' . $column['name'] . '`';
      }
      $result .= ' (' . implode(',', $columns) . ')';
      $result .= ' REFERENCES ' . $this->reference['name'];
      $rcolumns = array();
      foreach ($this->reference['columns'] as $column) {
        $rcolumns[] = '`' . $column . '`';
      }
      $result .= '(' . implode(',', $rcolumns) . ')';
      switch ($this->onDelete) {
        case self::REFERENCE_OPTION_NO_ACTION:
          $result .= ' ON DELETE NO ACTION';
          break;
        case self::REFERENCE_OPTION_CASCADE:
          $result .= ' ON DELETE CASCADE';
          break;
        case self::REFERENCE_OPTION_SET_NULL:
          $result .= ' ON DELETE SET NULL';
          break;
        case self::REFERENCE_OPTION_RESTICT:
          $result .= ' ON DELETE RESTRICT';
          break;
      }
      switch ($this->onUpdate) {
        case self::REFERENCE_OPTION_NO_ACTION:
          $result .= ' ON UPDATE NO ACTION';
          break;
        case self::REFERENCE_OPTION_CASCADE:
          $result .= ' ON UPDATE CASCADE';
          break;
        case self::REFERENCE_OPTION_SET_NULL:
          $result .= ' ON UPDATE SET NULL';
          break;
        case self::REFERENCE_OPTION_RESTICT:
          $result .= ' ON UPDATE RESTRICT';
          break;
      }
      return $result;
    }
    return '';
  }

}
