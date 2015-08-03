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
 * Table primary key model
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class Primary extends AbstractIndexe {
  
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
        return 'ALTER TABLE `' . $this->table->getName() . '` DROP PRIMARY KEY';
    }
    return '';
  }

  protected function addMysql() {
    $result = 'ALTER TABLE `' . $this->table->getName() . '` ADD PRIMARY KEY';
    if ($this->type === self::TYPE_HASH) {
      $result .= ' USING HASH';
    } elseif ($this->type === self::TYPE_BTREE) {
      $result .= ' USING BTREE';
    }
    $columns = array();
    foreach ($this->columns as $column) {
      $columns[] = $this->renderColumn(Type::MYSQL, $column);
    }
    $result .= ' (' . implode(',', $columns) . ')';
    if (strlen($this->comment) > 0) {
      $result .= " COMMENT '" . $this->comment . "'";
    }
    return $result;
  }

}
