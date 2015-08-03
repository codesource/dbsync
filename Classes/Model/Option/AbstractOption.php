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

use \DBSync\Model\Table;

/**
 * Abstraction of a table's option
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
abstract class AbstractOption {
  
  /**
   * Column's parent table
   * 
   * @var \DBSync\Model\Table 
   */
  protected $table;
  
  /**
   * Initial value
   * 
   * @var string
   */
  protected $value;
  
  /**
   * Constructor
   * 
   * @param string $type
   * @param string $value
   */
  public function __construct($value) {
    $this->value = $value;
  }
  
  /**
   * Get value for specific database driver 
   * 
   * @param string $type
   * 
   * @return string|NULL Return NULL if no value is found for database driver 
   */
  public function getValue($type=NULL){
    if($this->isAvailable($type)){
      if($type === NULL){
        return $this->value;
      }
    }
    return NULL;
  }
  
  /**
   * Set parent table
   * 
   * @param \DBSync\Model\Table $table
   * @return \DBSync\Model\Option\AbstractOption
   */
  public function setTable(Table $table){
    $this->table = $table;
    return $this;
  }
  
  /**
   * Check if option is available for database driver
   * 
   * @param string $type
   * @return boolean
   */
  abstract public function isAvailable($type);
  
  /**
   * Render SQL create query part for database driver
   * 
   * @param string $type
   * 
   * @return string|NULL Return NULL if
   */
  abstract public function create($type);
  
  /**
   * Render SQL alter query for database driver
   * 
   * @param string $type
   * 
   * @return string|NULL Return NULL if
   */
  abstract public function alter($type);
  
  /**
   * Render SQL drop query for database driver
   * 
   * @param string $type
   * 
   * @return string|NULL Return NULL if
   */
  abstract public function drop($type);
}
