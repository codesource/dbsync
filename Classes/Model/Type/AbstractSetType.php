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


/**
 * Abstract model of column's data type (text)
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
abstract class AbstractSetType extends AbstractType{
  
  
  /**
   * Type's values
   * 
   * @var $values
   */
  protected $values = array();
  
  /**
   * Type's charset
   * 
   * @var string 
   */
  protected $charset = '';
  
  /**
   * Type's collation
   * 
   * @var string 
   */
  protected $collation = '';
  
  /**
   * Constructor
   * 
   * @param array $attributes
   */
  public function __construct(array $attributes) {
    $this->values = is_array($attributes['values']) ? $attributes['values'] : array();
    $this->charset = isset($attributes['charset']) ? $attributes['charset'] : '';
    $this->collation = isset($attributes['collation']) ? $attributes['collation'] : '';
  }
  
  /**
   * Get type's values
   * 
   * @return array
   */
  public function getValues(){
    return $this->values;
  }
  
  /**
   * Get type's charset
   * 
   * @return string
   */
  public function getCharset(){
    return $this->charset;
  }
  
  /**
   * Get type's collation
   * 
   * @return string
   */
  public function getCollation(){
    return $this->collation;
  }
  
  /**
   * Check if both type are equals
   * 
   * @param \DBSync\Model\Type\AbstractType $type
   * 
   * @return boolean
   */
  public function equals(AbstractType $type){
    return get_class($this) === get_class($type) && 
            $this->charset === $type->getCharset() &&
            $this->collation === $type->getCollation() &&
            count(array_diff_assoc($this->values, $type->getValues())) === 0;
  }
}
