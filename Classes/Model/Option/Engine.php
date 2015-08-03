<?php

namespace DBSync\Model\Option;

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

use DBSync\Driver\Type;

/**
 * Table engine option
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class Engine extends AbstractOption {


  /**
   * Check if option is available for database driver
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
   * Render SQL create query part for database driver
   * 
   * @param string $type
   * 
   * @return string|NULL Return NULL if
   */
  public function create($type) {
    switch ($type) {
      case Type::MYSQL:
        return 'ENGINE='. $this->value;
    }
    return '';
  }

  /**
   * Render SQL alter query for database driver
   * 
   * @param string $type
   * 
   * @return string|NULL Return NULL if
   */
  public function alter($type) {
    switch ($type) {
      case Type::MYSQL:
        return 'ALTER TABLE `' . $this->table->getName() . "` ENGINE = " . $this->value;
    }
    return '';
  }

  /**
   * Render SQL drop query for database driver
   * 
   * @param string $type
   * 
   * @return string|NULL Return NULL if
   */
  public function drop($type) {
    switch ($type) {
      case Type::MYSQL:
        return '';
    }
    return '';
  }

}
