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
 * Abstract model of column's data type (floating)
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
abstract class AbstractFloatingType extends AbstractType{
  
  /**
   * Type's length
   * 
   * @var integer 
   */
  protected $length;
  
  /**
   * Type's decimal
   * 
   * @var integer 
   */
  protected $decimal;
  
  /**
   * Is type unsigned (used for number)
   * 
   * @var boolean 
   */
  protected $unsigned = FALSE;
  
  /**
   * Is type zero filled (used for number)
   * 
   * @var boolean 
   */
  protected $zerofilled = FALSE;
  
  /**
   * Constructor
   * 
   * @param array $attributes
   */
  public function __construct(array $attributes) {
    $this->decimal = isset($attributes['decimal']) && $attributes['decimal'] !== FALSE ? intval($attributes['decimal']) : FALSE;
    $this->length = isset($attributes['length']) ? intval($attributes['length']) : 0;
    $this->unsigned = isset($attributes['unsigned']) && $attributes['unsigned'];
    $this->zerofilled = isset($attributes['zerofilled']) && $attributes['zerofilled'];
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
   * Get type's decimal
   * 
   * @return string
   */
  public function getDecimal() {
    return $this->decimal;
  }

  /**
   * Is type unsigned?
   * 
   * @return boolean
   */
  public function isUnsigned() {
    return $this->unsigned ? TRUE : FALSE;
  }

  /**
   * Is type zerofilled?
   * 
   * @return boolean
   */
  public function isZerofilled() {
    return $this->unsigned ? TRUE : FALSE;
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
            $this->length === $type->getLength() &&
            $this->decimal === $type->getDecimal() &&
            $this->isUnsigned() === $type->isUnsigned() &&
            $this->isZerofilled() === $type->isZerofilled();
  }
}
