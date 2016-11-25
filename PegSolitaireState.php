<?php
/**
 * PegSolitaireState Class
 * 
 * Class for the representation of a peg solitaire game state
 * @author Lucas Acosta <lucasmacosta@gmail.com>
 * @version 0.1
 * @package peg_solitaire
 */

  include_once 'State.php';

  class PegSolitaireState extends State
  {
    private $board, $remaining_pegs, $valid_positions, $invalid_positions, $codes;
    private static $rotation_array = NULL, $reflection_array = NULL;

    private $min_code;

    const SIZE = 7, FREE = '.', OCCUPIED = 'o', INVALID = 'x';

    private static $directions = array('up' => -7, 'down' => 7, 'left' => -1, 'right' => 1);

    function __construct($board_string) {
      parent::__construct();
      if (self::$rotation_array == NULL || self::$reflection_array == NULL)
        $this->__buildTransformationArrays();
      $this->__parseBoardString($board_string);
      $this->__countPegs();
      $this->__calculateVariantCodes();
    }

    /**
     * Returns the number of pegs left on the board
     * @return integer
     */
    function remainingPegs() {
      return $this->remaining_pegs;
    }

    /**
     * Returns the number of valid positions on the board (i.e. free and occupied positions)
     * @return integer
     */
    function validPositions() {
      return $this->valid_positions;
    }

    /**
     * Returns the positions of the remaining pegs on the board
     * @return array
     */
    function getRemainingPegs() {
      $result = array();
      $index = 0;
      while (($new_index = gmp_scan1($this->board, $index)) != -1) {
        $result[] = $new_index;
        $index = $new_index + 1;
      }
      return $result;
    }

    /**
     * Moves the given peg on the given direction and returns the new state
     * after that move, if it's valid
     * @param integer $peg
     * @param string $direction
     * @return State|boolean
     */
    function makeMove($peg, $direction) {
      if (! isset(self::$directions[$direction])) throw new Exception('The direction of movement is invalid');
      $inc = self::$directions[$direction];
      # Location of the next peg in the given direction
      $next_peg   = $peg + $inc;
      if ($next_peg < 0 || $next_peg > self::SIZE * self::SIZE - 1 || in_array($next_peg, $this->invalid_positions))
        return FALSE;

      # Location of the free space where the peg will jump to
      $free_space = $peg + $inc * 2;
      if ($free_space < 0 || $free_space > self::SIZE * self::SIZE - 1 || in_array($free_space, $this->invalid_positions))
        return FALSE;

      # For horizontal moves the positions must be on the same row
      if (($direction == 'left' || $direction == 'right') &&
            ((int)($peg / self::SIZE) != (int)($next_peg / self::SIZE) ||
             (int)($peg / self::SIZE) != (int)($free_space / self::SIZE))) return FALSE;

      # Check if there is a peg next to the given peg to jump over it
      $mask = gmp_init(0);
      gmp_setbit($mask, $next_peg);
      if (gmp_cmp(gmp_and($this->board, $mask), gmp_init(0)) == 0) return FALSE;

      # Check if there is a free space to jump to
      $mask = gmp_init(0);
      gmp_setbit($mask, $free_space);
      if (gmp_cmp(gmp_and($this->board, $mask), gmp_init(0)) != 0) return FALSE;

      # Create a new state and do the move
      $new_state = clone $this;
      gmp_clrbit($new_state->board, $peg);
      gmp_clrbit($new_state->board, $next_peg);
      gmp_setbit($new_state->board, $free_space);
      $new_state->__calculateVariantCodes();
      $new_state->remaining_pegs--;
      $new_state->predecessors = array();
      return $new_state;
    }

    function __clone() {
      # Patch to clone the board in the new state
      $this->board = gmp_and($this->board, $this->board);
    }

    function compare(&$other) {
      if(!($other instanceof $this)) return FALSE;
      return gmp_cmp($this->board, $other->board);
    }

    function getCode() {
      return $this->board;
    }

    function getMinCode() {
      return $this->min_code;
    }

    function __toString() {
      $str_rows = array();
      for ($i = 0; $i < self::SIZE * self::SIZE; $i += self::SIZE) {
        $str_row = array();
        for($j = 0; $j < self::SIZE; $j++) {
          $mask = gmp_init(0);
          gmp_setbit($mask, $i + $j);
          if (in_array($i + $j, $this->invalid_positions)) {
            $str_row[] = self::INVALID;
          } elseif (gmp_cmp(gmp_and($this->board, $mask), gmp_init(0)) != 0) {
            $str_row[] = self::OCCUPIED;
          } else {
            $str_row[] = self::FREE;
          }
        }
        $str_rows[] = implode(' ', $str_row);
      }
      return implode("\n", $str_rows);
    }

    /**
     * Calculates the codes for every variation of the board
     */
    private function __calculateVariantCodes() {
      $min = $board = $this->board;
      for($i = 0; $i < 4; $i++) {
        if (gmp_cmp($rotated = $this->__rotateBoard($board), $min) < 0) $min = $rotated;
        if (gmp_cmp($reflected = $this->__reflectBoard($board), $min) < 0) $min = $reflected;
        $board = $rotated;
      }
      $this->min_code = $min;
    }

    /**
     * Builds the arrays used for rotations/reflections
     */
    private function __buildTransformationArrays() {
      self::$rotation_array   = array();
      self::$reflection_array = array();
      for ($i = 0, $n = self::SIZE; $i < $n; $i++) {
        for ($j = 0; $j < $n; $j++) {
          self::$rotation_array[($i*self::SIZE)+$j]   = ($j*self::SIZE) + ($n - $i - 1);
          self::$reflection_array[($i*self::SIZE)+$j] = ($i*self::SIZE) + ($n - $j - 1);
        }
      }
    }

    /**
     * Parses a string that represents a board state
     * @param string $string
     */
    private function __parseBoardString($string) {
      if (!is_string($string)) throw new Exception('The board passed is not a string');
      $this->board = gmp_init(0);
      $this->invalid_positions = array();
      $rows = explode("\n", $string);
      if (count($rows) != self::SIZE) throw new Exception('The number of rows of the board is incorrect');
      foreach ($rows as $i => $row) {
        if (strlen($row) != self::SIZE) throw new Exception('One of the rows of the board is of an incorrect size');
        foreach(str_split($row) as $j => $position) {
          switch ($position) {
            case self::INVALID:
              $this->invalid_positions[] = self::SIZE * $i + $j;
              break;
            case self::OCCUPIED:
              gmp_setbit($this->board, self::SIZE * $i + $j);
              break;
            case self::FREE:
              break;
            default:
              throw new Exception('An invalid character was found while parsing the board');
          }
        }
      }
      # Now the symmetry of the board must be checked
      $test_board = gmp_init(0);
      foreach($this->invalid_positions as $invalid_position)
        gmp_setbit($test_board, $invalid_position);
      for($i = 0; $i < 4; $i++) {
        if (gmp_cmp($rotated = $this->__rotateBoard($test_board), $test_board) != 0 ||
            gmp_cmp($reflected = $this->__reflectBoard($test_board), $test_board) != 0)
              throw new Exception('The board is not symmetric');
        $board = $rotated;
      }
    }

    /**
     * Calculates number of remaining pegs and valid positions. This is done
     * just for performance reasons, as this values could be calculated with
     * the corresponding attributes
     */
    private function __countPegs() {
      $this->remaining_pegs = gmp_popcount($this->board);
      $this->valid_positions = self::SIZE * self::SIZE - count($this->invalid_positions);
    }

    /**
     * Rotates a given board 90º using the corresponding transformation arrays
     * @param resource $board
     * @return resource
     */
    private function __rotateBoard($board) {
      $new_board = gmp_init(0);
      foreach (self::$rotation_array as $index => $new_position) {
        $mask = gmp_init(0);
        gmp_setbit($mask, $index);
        if (gmp_cmp(gmp_and($board, $mask), gmp_init(0)) != 0)
          gmp_setbit($new_board, $new_position);
      }
      return $new_board;
    }

    /**
     * Reflects a given board on the vertical axis using the corresponding transformation arrays
     * @param resource $board
     * @return resource
     */
    private function __reflectBoard($board) {
      $new_board = gmp_init(0);
      foreach (self::$reflection_array as $index => $new_position) {
        $mask = gmp_init(0);
        gmp_setbit($mask, $index);
        if (gmp_cmp(gmp_and($board, $mask), gmp_init(0)) != 0)
          gmp_setbit($new_board, $new_position);
      }
      return $new_board;
    }

  }

?>
