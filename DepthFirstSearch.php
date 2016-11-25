<?php
/**
 * DepthFirstSearch Class
 * 
 * Class that implement the depth first search algorithm
 * @author Lucas Acosta <lucasmacosta@gmail.com>
 * @version 0.1
 * @package peg_solitaire
 */

  include_once 'AbstractSearch.php';
  include_once 'PegSolitaireState.php';
  include_once 'PegSolitaireProblem.php';

  class DepthFirstSearch extends AbstractSearch
  {
    function doSearch() {
      $this->__recursiveSearch($this->getProblem()->getInitialState());
      return count($this->final_states) > 0;
    }

    private function __recursiveSearch(&$state) {
      if ($this->getProblem()->isSolution($state)) {
        if (--$this->options['max_solutions'] == 0) {
          # This flag is set in order to cut the recursion
          $this->is_searching = FALSE;
        }
        $this->final_states[] = $state;

      } else {
        # Mark the state as visited
        $this->setVisited($state);
        if ($this->options['debug'] && $this->getVisitedStatesCount() % 100 == 0) {
          $this->debug($state);
        }
        # Recurse into the next possible states
        foreach ($this->getProblem()->nextStates($state) as $next_state) {

          if (!$this->is_searching) {
            # The search is over. The recursion branches are cut down
            break;
          }
          # Add the current state as a predecessor for the next state
          if (! $this->isVisited($next_state)) {
            $next_state->addPredecessor($state);
            $this->__recursiveSearch($next_state);
          } else {
              $this->already_visited_states++;
              # Retrieve the state and add the current state to its predecessors
              $this->__getStateFromCode($next_state->getMinCode())->addPredecessor($state);
          }
        }
      }
    }

  }

?>
