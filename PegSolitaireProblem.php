<?php
/**
 * PegSolitaireProblem Class
 * 
 * Class for the representation of the peg solitaire problem
 * @author Lucas Acosta <lucasmacosta@gmail.com>
 * @version 0.1
 * @package peg_solitaire
 */

  include_once 'Problem.php';
  include_once 'PegSolitaireState.php';

  class PegSolitaireProblem extends Problem
  {
    function __construct(&$initial_state) {
      if (! ($initial_state instanceof PegSolitaireState)) throw new Exception('The initial state must be an instance of PegSolitaireState class');
      parent::__construct($initial_state);
    }

    function isSolution(&$state) {
      # Problem is solved when there's only one peg remaining
      return $state->remainingPegs() == 1;
    }

    function nextStates(&$state) {
      $next_states = array();
      foreach ($state->getRemainingPegs() as $peg) {
        foreach(array('up', 'right', 'down', 'left') as $direction) {
          if ($new_state = $state->makeMove($peg, $direction)) $next_states[] = $new_state;
        }
      }
      return $next_states;
    }

  }

?>
