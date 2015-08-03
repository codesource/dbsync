<?php
namespace DBSync;

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
 * Description of Autoloader
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class Autoloader {

    public static function register(){
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    public static function autoload($class){
      $nameSpace = explode('\\', $class);
      array_shift($nameSpace);     
      $class = implode('/', $nameSpace);
      require_once $class.'.php';
    }
}
