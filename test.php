<?php

  include_once 'PegSolitaireState.php';
  include_once 'PegSolitaireProblem.php';
  include_once 'DepthFirstSearch.php';

  $english      = "xxoooxx\nxxoooxx\nooooooo\nooo.ooo\nooooooo\nxxoooxx\nxxoooxx";
  $european     = "xxoooxx\nxooooox\nooooooo\nooo.ooo\nooooooo\nxooooox\nxxoooxx";
  $submarine    = "xx.x.xx\nxx...xx\n...o...\n.ooooo.\n.......\nxx...xx\nxx...xx";
  $greek_cross  = "xx...xx\nxx.o.xx\n...o...\n.ooooo.\n...o...\nxx.o.xx\nxx...xx";
  $square5x5    = "xxxxxxx\nxooooox\nxooooox\nxooooox\nxoo.oox\nxooooox\nxxxxxxx";
  $pyramid      = "xx...xx\nxx.o.xx\n..ooo..\n.ooooo.\nooooooo\nxx...xx\nxx...xx";
  $diamond      = "xx.o.xx\nxxoooxx\n.ooooo.\nooo.ooo\n.ooooo.\nxxoooxx\nxx.o.xx";

  # Initial state
//   $state    = new PegSolitaireState($greek_cross);
//   $state    = new PegSolitaireState($submarine);
  $state    = new PegSolitaireState($english);
//   $state    = new PegSolitaireState($european);
//   $state    = new PegSolitaireState($square5x5);
//   $state    = new PegSolitaireState($diamond);
//   $state    = new PegSolitaireState($pyramid);

  echo "Initial state: \n$state\n\n";

  $problem  = new PegSolitaireProblem($state);

  $search  = new DepthFirstSearch($problem, array('max_solutions' => 1));

  $time_start = microtime(true);

  echo "Start DepthFirstSearch...\n";
  $search_result = $search->doSearch();
  echo "Search is done...\n";

  $time_end = microtime(true);
  $time = $time_end - $time_start;

  echo "Search time was $time seconds\n";

  $search->debug();

  if ($search_result) {
    $search->buildAllPossiblePaths();
    foreach($search->getFinalStates() as $final_state_index => $final_state) {
      echo "=================== Solution " . ($final_state_index + 1) . " =======================\n";
      foreach($search->getAllPaths($final_state) as $path_index => $path) {
        echo "========== Path to solution " . ($path_index + 1) . " ==============\n";
        foreach($path as $node) {
          echo "$node\n\n";
        }
      }
    }
  } else {
    echo "No solution was found.\n";
  }

?>
