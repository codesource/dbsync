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
 * Databases synchronizer
 *
 * @author Matthias Toscanelli <m.toscanelli@code-source.ch>
 */
class Synchronizer {

  /**
   *
   * @var \DBSync\Driver\AbstractDatabaseDriver 
   */
  protected $source;

  /**
   *
   * @var \DBSync\Driver\AbstractDatabaseDriver 
   */
  protected $destination;

  /**
   * Constructor
   * 
   * @param \DBSync\DBSync\Driver\AbstractDatabaseDriver $source
   * @param \DBSync\DBSync\Driver\AbstractDatabaseDriver $destination
   */
  public function __construct(\DBSync\Driver\AbstractDatabaseDriver $source, \DBSync\Driver\AbstractDatabaseDriver $destination) {
    $this->source = $source;
    $this->destination = $destination;
  }

  public function sync($options = array()) {
    if (!is_array($options)) {
      $options = array();
    }
    $verbose = isset($options['verbose']) && $options['verbose'];
    $time = microtime(true);
    if ($verbose) {
      print("Starting synchronisation...\n");
    }
    $queries = $this->getQueries($verbose);
    if (isset($options['print']) && $options['print']) {
      print("\n");
      foreach ($queries as $query) {
        print($query . ";\n");
      }
      print("\n");
    } elseif (isset($options['dryrun']) && $options['dryrun']) {
      $this->destination->dryrun($queries, $verbose);
    } else {
      $this->destination->commit($queries, $verbose);
    }
    if ($verbose) {
      print("Synchronisation ended (elapsed " . number_format(microtime(true) - $time, 3) . "s)\n");
    }
  }

  /**
   * Get an array of SQL query to sync databases
   * 
   * @params boolean $verbose
   * 
   * @return array
   */
  protected function getQueries($verbose) {
    $sourceTables = array();
    $destinationTables = array();
    foreach ($this->source->listTables() as $name) {
      $table = $this->source->loadTableStructure($name);
      $sourceTables[] = $table;
    }
    if ($verbose) {
      $n = count($sourceTables);
      print($n . " table" . ($n > 1 ? 's' : '') . " found on source database\n");
    }
    foreach ($this->destination->listTables() as $name) {
      $table = $this->destination->loadTableStructure($name);
      $destinationTables[] = $table;
    }
    if ($verbose) {
      $n = count($destinationTables);
      print($n . " table" . ($n > 1 ? 's' : '') . " found on destination database\n");
    }
    $queries = array();
    foreach ($sourceTables as $stable) {
      $found = FALSE;
      foreach ($destinationTables as $dkey => $dtable) {
        if ($stable->getName() === $dtable->getName()) {
          $aqueries = $dtable->alter($this->destination->getType(), $stable);
          if ($verbose) {
            $n = count($aqueries);
            if ($n > 0) {
              print($n . " change" . ($n > 1 ? 's' : '') . " have been found on '" . $stable->getName() . "'\n");
            } else {
              print("Table '" . $stable->getName() . "' is up to date on destination\n");
            }
          }
          $queries = array_merge($queries, $aqueries);
          $found = TRUE;
          unset($destinationTables[$dkey]);
          break;
        }
      }
      if (!$found) {
        if ($verbose) {
          print("Table '" . $stable->getName() . "' has not been found on destination\n");
        }
        $queries = array_merge($queries, $stable->create($this->destination->getType()));
      }
    }
    foreach ($destinationTables as $dtable) {
      $queries = array_merge($queries, $dtable->drop($this->destination->getType()));
    }
    return $queries;
  }

}
