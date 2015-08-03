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
 * Helping manual
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class Manual {

  /**
   * Column size of output
   * 
   * @var integer
   */
  protected $columns;

  /**
   * Current writing line
   * 
   * @var string 
   */
  protected $line = '';

  /**
   * Store starting indentation line
   * 
   * @var integer 
   */
  protected $indented = 0;

  /**
   * Constructor
   */
  public function __construct($columns = 80) {
    $this->columns = intval($columns) > 80 ? $columns : 80;
  }

  /**
   * Render application manual as array of lines
   * 
   * @param string $separator
   * 
   * @return array
   */
  public function render() {
    $lines = array(
        $this->write('DBSync')->center('General Commands Manual')->right('DBSync')->clear(),
        '',
        
        $this->title('Name')->clear(),
        $this->indent('dbsync - synchronize database\'s structure between two database')->clear(),
        '',
        
        $this->title('Synopsis')->clear(),
        $this->indent('dbsync [-h|--help]')->clear(),
        $this->indent('dbsync')->indent('--host1=<host> --database1=<database> --username1=<username> [--password1=<password>] [--port1=<port>]', 1, 15)->clear(),
        $this->indent('--host2=<host> --database2=<database> --username2=<username> [--password2=<password>] [--port2=<port>]', 15)->clear(),
        $this->indent('[-v|--verbose] [-q|--quiet] [-p|--print] [--dryrun]', 15)->clear(),
        $this->indent('dbsync --file=<file>')->clear(),
        '',
        
        $this->title('Description')->clear(),
        $this->indent('This script allows you to synchronise database structure between two database. Tables, columns, indexes and constraints are compared and SQL are generated to update destination.')->clear(),
        '',
        
        $this->title('General options')->clear(),
        $this->indent('--dryrun')->clear(),
        $this->indent('dry run queries (no modification will be commited to destination)', 16)->clear(),
        $this->indent('-h | --help')->clear(),
        $this->indent('show this help', 16)->clear(),
        $this->indent('-p | --print')->clear(),
        $this->indent('only print all required queries to update destination', 16)->clear(),
        $this->indent('-q | --quiet')->clear(),
        $this->indent('quietly execute queries without processing informations', 16)->clear(),
        $this->indent('-v | --verbose')->clear(),
        $this->indent('verbosely list queries and processing informations', 16)->clear(),
        $this->indent('--file')->clear(),
        $this->indent('use configuration file to set other options', 16)->clear(),
        '',
        $this->title('Source options')->clear(),
        $this->indent('--database1')->clear(),
        $this->indent('source database name', 16)->clear(),
        $this->indent('--host1')->clear(),
        $this->indent('source host server address', 16)->clear(),
        $this->indent('--password1')->clear(),
        $this->indent('user password to connect to source host server', 16)->clear(),
        $this->indent('--port1')->clear(),
        $this->indent('source host server port', 16)->clear(),
        $this->indent('--username1')->clear(),
        $this->indent('username to connect to source host server', 16)->clear(),
        '',
        $this->title('Destination options')->clear(),
        $this->indent('--database2')->clear(),
        $this->indent('destination database name', 16)->clear(),
        $this->indent('--host2')->clear(),
        $this->indent('destination host server address', 16)->clear(),
        $this->indent('--password2')->clear(),
        $this->indent('user password to connect to destination host server', 16)->clear(),
        $this->indent('--port2')->clear(),
        $this->indent('destination host server port', 16)->clear(),
        $this->indent('--username2')->clear(),
        $this->indent('username to connect to destination host server', 16)->clear(),
        '',
        $this->title('Author')->clear(),
        $this->indent('Written by Matthias Toscanelli')->clear(),
        '',
        $this->title('Reporting bugs')->clear(),
        $this->indent('Github repository: <https://github.com/codesource/dbsync/issues>')->clear(),
        '',
        $this->title('Copyright')->clear(),
        $this->indent('Copyright © 2015 Code-Source. License GPLv3+: GNU GPL version 3 or later <http://gnu.org/licenses/gpl.html>.')->clear(),
        $this->indent('This is free software: you are free to change and redistribute it.  There is NO WARRANTY, to the extent permitted by law.')->clear(),
        '',
        $this->title('See also')->clear(),
        $this->indent('Github repository for last version: <https://github.com/codesource/dbsync>')->clear(),
        '',
        $this->center('August 2015')->clear(),
    );
    $return = array();
    foreach ($lines as $line) {
      if (is_array($line)) {
        $return = array_merge($return, $line);
      } else {
        $return[] = $line;
      }
    }
    return $return;
  }

  /**
   * Indent text
   * 
   * @param string $text
   * @param integer $indent
   * @param integer $forceindent
   * 
   * @return \DBSync\Manual
   */
  protected function indent($text, $indent = 8, $forceindent = -1) {
    if (strlen($this->line) === 0) {
      $this->indented = $indent;
    }
    if ($forceindent >= 0) {
      $this->indented = $forceindent;
    }
    $this->line .= str_repeat(' ', $indent) . $text;
    return $this;
  }

  /**
   * Return current line and clear it
   * 
   * @return string
   */
  protected function clear() {
    $line = $this->line;
    $indent = $this->indented;
    $this->line = '';
    $this->indented = 0;
    if (strlen($line) > $this->columns) {
      if ($indent > 0) {
        list($first) = explode('|||', wordwrap($line, $this->columns, '|||', TRUE));
        $lines = array_merge(array($first), explode('|||', wordwrap(substr($line, strlen($first)), $this->columns - $indent, '|||', TRUE)));
        for ($i = 1, $ni = count($lines); $i < $ni; $i++) {
          $lines[$i] = str_repeat(' ', $indent) . trim($lines[$i]);
        }
      } else {
        $lines = explode('|||', wordwrap($line, $this->columns, '|||', TRUE));
      }
      return $lines;
    } else {
      return $line;
    }
  }

  /**
   * Append text to line
   * 
   * @param string $text
   * 
   * @return \DBSync\Manual
   */
  protected function write($text) {
    $this->line .= $text;
    return $this;
  }

  /**
   * Append a title on line
   * 
   * @param string $text
   * 
   * @return \DBSync\Manual
   */
  protected function title($text) {
    $this->line .= strtoupper($text);
    return $this;
  }

  /**
   * Align text center of the line
   * 
   * @param string $text
   * 
   * @return \DBSync\Manual
   */
  protected function center($text) {
    $length = strlen($text);
    $clength = strlen($this->line);
    if ($clength + $length + 1 >= $this->columns) {
      $this->line .= ' ' . $text;
    }
    $this->line .= str_repeat(' ', ($this->columns - $length) / 2 - $clength) . $text;
    return $this;
  }

  /**
   * Align text right of the line
   * 
   * @param string $text
   * 
   * @return \DBSync\Manual
   */
  protected function right($text) {
    $length = strlen($text);
    $clength = strlen($this->line);
    if ($clength + $length + 1 >= $this->columns) {
      $this->line .= ' ' . $text;
    }
    $this->line .= str_repeat(' ', $this->columns - $length - $clength) . $text;
    return $this;
  }

}
