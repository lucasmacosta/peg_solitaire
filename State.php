<?php
/**
 * State Class
 * 
 * Abstract class for the representation of a game state
 * @author Lucas Acosta <lucasmacosta@gmail.com>
 * @version 0.1
 * @package peg_solitaire
 */

  abstract class State
  {
    /**
     * An array with the predecessor of this state
     * @access protected
     * @var array
     */
    protected $predecessors;

    /**
     * Constructor sets up {@link $predecessors}
     */
    function __construct() {
      $this->predecessors = array();
    }

    /**
     * Adds a predecessor to {@link $predecessors}
     * @param State $state
     */
    function addPredecessor(&$state) {
      $this->predecessors[] = $state;
    }

    /**
     * Returns {@link $predecessors}
     * @return array
     */
    function getPredecessors() {
      return $this->predecessors;
    }

    /**
     * Compares this state against another state
     * @param State $other
     * @return boolean
     */
    abstract function compare(&$other);

    /**
     * Returns a string representation of this state
     * @return string
     */
    abstract function __toString();

    /**
     * Returns a numeric representation of this state
     * @return resource
     */
    abstract function getCode();

    /**
     * Returns the mininum numeric representation of this state
     * @return resource
     */
    abstract function getMinCode();
  }

?>
