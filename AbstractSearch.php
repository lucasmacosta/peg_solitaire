<?php
/**
 * AbstractSearch Class
 * 
 * Abstract class for the representation of a state search algorithm
 * @author Lucas Acosta <lucasmacosta@gmail.com>
 * @version 0.1
 * @package peg_solitaire
 */

  include_once 'Problem.php';
  include_once 'State.php';

  abstract class AbstractSearch
  {
    private $problem, $visited_states = array(), $codes_hash = array();
    protected $options, $already_visited_states = 0, $is_searching = TRUE, $final_states = array();

    function __construct($problem, $options = array()) {
      if (!($problem instanceof Problem)) throw new Exception("The problem must be a subclass of Problem");
      $this->problem = $problem;
      $this->options = array_merge(array('max_solutions' => 2, 'max_states' => 1000, 'debug' => TRUE), $options);
    }

    /**
     * Returns the search problem
     * @return Problem
     */
    function getProblem() {
      return $this->problem;
    }

    /**
     * Return the number of visited states so far
     * @return integer
     */
    function getVisitedStatesCount() {
      return count($this->visited_states);
    }

    /**
     * Return the solution states problem
     * @return array
     */
    function getFinalStates() {
      return $this->final_states;
    }

    /**
     * Checks if a state was already visited
     * @return boolean
     */
    function isVisited(&$state) {
      return $this->__binarySearch($state->getMinCode(), $this->visited_states) !== FALSE;
    }

    /**
     * Set a state as visited.
     * @param State $state
     */
    function setVisited(&$state) {
      # For space reasons, only the numeric representation of the state is stored
      $code = $state->getMinCode();
      # This search takes O(log(n)) when the element is not in the list (wich is
      # always the case for this algorithm)
      $index = $this->__binarySearch($code, $this->visited_states, true);
      # This displacement takes at most O(n)
      for ($i = count($this->visited_states) - 1; $i >= $index; $i--)
        $this->visited_states[$i+1] = $this->visited_states[$i];
      $this->visited_states[$index] = $code;
      # The total time for the above piece of code is O(log(n)+n), wich is better
      # than the O(log(n)*n) of quicksort
/*      $this->visited_states[] = $code;
      usort($this->visited_states, 'gmp_cmp');*/
      $this->codes_hash[gmp_strval($code)] = $state;
    }

    /**
     * Returns a visited state by its code
     * @param resource $code
     * @return State
     */
    protected function __getStateFromCode($code) {
      return $this->codes_hash[gmp_strval($code)];
    }

    /**
     * Returns all the possible paths that leads to the given state
     * by using its predecessors
     * @param State $state
     * @return array
     */
    function getAllPaths(&$state) {
      $all_paths = array();
      if (count($predecessors = $state->getPredecessors()) == 0) { $all_paths[] = array($state); }
      else {
        foreach ($predecessors as $predecessor) {
          $partial_routes = $this->getAllPaths($predecessor);
          foreach ($partial_routes as $partial_route) {
            $partial_route[] = $state;
            $all_paths[] = $partial_route;
          }
        }
      }
      return $all_paths;
    }

    /**
     * Builds all the possible paths that are equivalent to the ones found as solutions
     */
    function buildAllPossiblePaths() {
      foreach($this->final_states as $final_state) {
        foreach($this->getAllPaths($final_state) as $path) {
          $next_level_states = array();
          for($i = 0; $i < count($path) - 1; $i++) {
            $current_level_states = array_merge(array($path[$i]), $next_level_states);
            $next_level_states = array();
            foreach($current_level_states as $state) {
              foreach($this->getProblem()->nextStates($state) as $next_state) {
                if (gmp_cmp($path[$i+1]->getCode(), $next_state->getCode()) == 0) {
                  if ($state === $path[$i]) continue;
                  $next_state = $path[$i+1];
                }
                if (gmp_cmp($path[$i+1]->getMinCode(), $next_state->getMinCode()) == 0) {
                  $next_state->addPredecessor($state);
                  $next_level_states[] = $next_state;
                }
              }
            }
          }
          foreach($next_level_states as $new_final_state) {
            $this->final_states[] = $new_final_state;
          }
        }
      }
    }

    /**
     * Prints debug info for the search
     * @param resource $code
     */
    function debug($state = NULL) {
      echo "Effectively visited states: " . $this->getVisitedStatesCount() . "\n";
      echo "Already visited and discarded states: {$this->already_visited_states}\n";
      if ($state) {
        echo "Current state: \n";
        echo "$state\n\n";
      }
    }

    /**
     * Starts the search
     */
    abstract function doSearch();

    /**
     * Performs a binary search of $needle in $haystack
     * @param resource $needle
     * @param array $haystack
     * @return integer|boolean
     */
    protected function __binarySearch($needle, $haystack, $index_on_false = FALSE) {
      $max = count($haystack) - 1;
      $min = 0;

      while ($min <= $max) {
        $mid = $min + floor(($max - $min) / 2);
        if (($result = gmp_cmp($needle, $haystack[$mid])) > 0) {
            $min = $mid + 1;
        } elseif ($result < 0) {
            $max = $mid - 1;
        } else {
            return $mid;
        }
      }
      if ($index_on_false) {
        if (count($haystack) == 0) return 0;
        return gmp_cmp($needle, $haystack[$mid]) > 0 ? $mid + 1 : $mid;
      } else
        return FALSE;
    }

  }

?>
