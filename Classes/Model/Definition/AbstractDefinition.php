<?php
namespace DBSync\Model\Definition;

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

use \DBSync\Model\Column;

/**
 * Abstract column definition
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
abstract class AbstractDefinition {
  
  /**
   * Definition's parent column
   * 
   * @var \DBSync\Model\Column 
   */
  protected $column;
  
  /**
   * Initial value
   * 
   * @var mixed
   */
  protected $value;
  
  /**
   * Constructor
   * 
   * @param string $type
   * @param string $value
   */
  public function __construct($value=NULL) {
    $this->value = $value;
  }
  
  /**
   * Get definition's value
   * 
   * @return mixed
   */
  public function getValue(){
    return $this->value;
  }
  
  /**
   * Set parent column
   * 
   * @param \DBSync\Model\Column $column
   * @return \DBSync\Model\Definition\AbstractDefinition
   */
  public function setColumn(Column $column){
    $this->column = $column;
    return $this;
  }
  
  /**
   * Check if definition is available for database driver
   * 
   * @param string $type
   * @return boolean
   */
  abstract public function isAvailable($type);
  
  /**
   * Get definition rendering for SQL query
   * 
   * @param string $type
   * 
   * @return string
   */
  abstract public function render($type);
  
  
}
