<?php
/**
 * Problem Class
 * 
 * Abstract class for the representation of a state search problem
 * @author Lucas Acosta <lucasmacosta@gmail.com>
 * @version 0.1
 * @package peg_solitaire
 */

  include_once 'State.php';

  abstract class Problem
  {
    private $initial_state;

    function __construct(&$initial_state) {
      $this->initial_state = $initial_state;
    }

    function getInitialState() {
      return $this->initial_state;
    }

    /**
     * Checks if the state is a solution for this problem
     * @param State $board
     * @return boolean
     */
    abstract function isSolution(&$state);

    /**
     * Returns the next possible states starting from the given state
     * @param State $board
     * @return array
     */
    abstract function nextStates(&$state);
  }

?>
