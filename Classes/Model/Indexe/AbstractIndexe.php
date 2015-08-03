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

use \DBSync\Model\Table;

/**
 * Abstract indexe definition
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
abstract class AbstractIndexe {

  const TYPE_NONE = 0;
  const TYPE_BTREE = 1;
  const TYPE_HASH = 2;

  /**
   * Indexe's name
   * 
   * @var string
   */
  protected $name;

  /**
   * Indexe's type
   * 
   * @var integer
   */
  protected $type;

  /**
   * Definition's parent table
   * 
   * @var \DBSync\Model\Table 
   */
  protected $table;

  /**
   * Columns used by indexe
   * 
   * @var array
   */
  protected $columns = array();

  /**
   * Indexe's comment
   * 
   * @var string 
   */
  protected $comment;

  /**
   * Constructor
   * 
   * @param string $name
   * @param array $columns
   */
  public function __construct($name, array $columns, $type = self::TYPE_NONE, $comment = '') {
    $this->name = $name;
    $this->columns = $columns;
    $this->type = $type;
    $this->comment = $comment;
  }

  /**
   * Get indexe's name
   * 
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Get indexe's columns
   * 
   * @return array
   */
  public function getColumns() {
    return $this->columns;
  }

  /**
   * Get indexe's type
   * 
   * @return integer
   */
  public function getType() {
    return $this->type;
  }

  /**
   * Get indexe's comment
   * 
   * @return string
   */
  public function getComment() {
    return $this->comment;
  }

  /**
   * Add a column to the list
   * 
   * @param array $column
   * @param integer $position
   * 
   * @return \DBSync\Model\Indexe\AbstractIndexe
   */
  public function addColumn($column, $position = 0) {
    $columns = array();
    $inserted = FALSE;
    for ($i = 0, $ni = count($this->columns); $i < $ni; $i++) {
      if ($i === $position) {
        $columns[] = $column;
        $inserted = TRUE;
      }
      if ($this->columns[$i]['name'] !== $column['name']) {
        $columns[] = $this->columns[$i];
      }
    }
    if (!$inserted) {
      $columns[] = $column;
    }
    $this->columns = $columns;
    return $this;
  }

  /**
   * Set parent table
   * 
   * @param \DBSync\Model\Table $table
   * @return \DBSync\Model\Definition\AbstractDefinition
   */
  public function setTable(Table $table) {
    $this->table = $table;
    return $this;
  }

  /**
   * Check if given indexe equals current
   * 
   * @param \DBSync\Model\Indexe\AbstractIndexe $indexe
   * 
   * @return boolean
   */
  public function equals(AbstractIndexe $indexe) {
    if (get_class($this) !== get_class($indexe)) {
      return FALSE;
    }
    if ($this->name !== $indexe->getName()) {
      return FALSE;
    }
    if ($this->type !== $indexe->getType()) {
      return FALSE;
    }
    if ($this->comment !== $indexe->getComment()) {
      return FALSE;
    }
    $columns = $indexe->getColumns();
    if (is_array($this->columns)) {
      if (!is_array($columns) || !$this->compareColumns($this->columns, $columns)) {
        return FALSE;
      }
    } elseif (is_array($columns)) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Compare 2 columns
   * 
   * @param array $a
   * @param array $b
   * 
   * @return integer
   */
  public function compareColumns($a, $b) {
    if (!is_array($a) || !is_array($b)) {
      return FALSE;
    }
    if (count($a) !== count($b)) {
      return FALSE;
    }
    foreach ($a as $k => $v) {
      if (!isset($b[$k]) || $b[$k] !== $v) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Check if indexe is available for database driver
   * 
   * @param string $type
   * @return boolean
   */
  abstract public function isAvailable($type);

  /**
   * Render SQL add query for database driver 
   * 
   * @param string $type
   * 
   * @return string
   */
  abstract public function add($type);

  /**
   * Render SQL drop query for database driver 
   * 
   * @param string $type
   * 
   * @return string
   */
  abstract public function drop($type);

  /**
   * Render an indexe's column for SQL query for database driver
   * 
   * @param string $type
   * @param array $column
   * 
   * @return string
   */
  protected function renderColumn($type, array $column) {
    switch ($type) {
      case \DBSync\Driver\Type::MYSQL:
        $order = isset($column['order']) ? strtoupper($column['order']) : '';
        return '`' . $column['name'] . '`' .
                ($column['length'] > 0 ? '(' . $column['length'] . ')' : '') .
                (in_array($order, array('ASC', 'DESC')) ? ' ' . $order : '');
    }
    return '';
  }

}
