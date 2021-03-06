<?php

namespace DBSync\Model\Type;

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
 * Binary column type
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class Binary extends AbstractType {

  /**
   * Type's length
   * 
   * @var integer 
   */
  protected $length;

  /**
   * Constructor
   * 
   * @param array $attributes
   */
  public function __construct(array $attributes) {
    $this->length = isset($attributes['length']) ? intval($attributes['length']) : 0;
  }

  /**
   * Get type's length
   * 
   * @return string
   */
  public function getLength() {
    return $this->length;
  }

  /**
   * Check if both type are equals
   * 
   * @param \DBSync\Model\Type\AbstractType $type
   * 
   * @return boolean
   */
  public function equals(AbstractType $type) {
    return get_class($this) === get_class($type) &&
            $this->length === $type->getLength();
  }

  /**
   * Check if type is available for database driver
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
   * Get type rendering for SQL query 
   * 
   * @params string $type
   * 
   * @return string
   */
  public function render($type) {
    switch ($type) {
      case Type::MYSQL:
        return 'BINARY' . ($this->length > 0 ? '(' . $this->length . ')' : '');
    }
    return '';
  }

}
