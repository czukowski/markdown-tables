<?php
namespace Cz\Markdown;
use RuntimeException;

/**
 * GridTableParser
 * 
 * This is almost a carbon copy of `GridTableParser` class from the Docutils module.
 * 
 * Parse a grid table using `parse()`.
 * 
 * Here's an example of a grid table::
 * 
 *     +------------------------+------------+----------+----------+
 *     | Header row, column 1   | Header 2   | Header 3 | Header 4 |
 *     +========================+============+==========+==========+
 *     | body row 1, column 1   | column 2   | column 3 | column 4 |
 *     +------------------------+------------+----------+----------+
 *     | body row 2             | Cells may span columns.          |
 *     +------------------------+------------+---------------------+
 *     | body row 3             | Cells may  | - Table cells       |
 *     +------------------------+ span rows. | - contain           |
 *     | body row 4             |            | - body elements.    |
 *     +------------------------+------------+---------------------+
 * 
 * Intersections use '+', row separators use '-' (except for one optional head/body row separator,
 * which uses '='), and column separators use '|'.
 * 
 * Passing the above table to the `parse()` method will result in the following data structure:
 * 
 *     ([24, 12, 10, 10],
 *      [[[0, 0, 1, ['Header row, column 1']],
 *        [0, 0, 1, ['Header 2']],
 *        [0, 0, 1, ['Header 3']],
 *        [0, 0, 1, ['Header 4']]]],
 *      [[[0, 0, 3, ['body row 1, column 1']],
 *        [0, 0, 3, ['column 2']],
 *        [0, 0, 3, ['column 3']],
 *        [0, 0, 3, ['column 4']]],
 *       [[0, 0, 5, ['body row 2']],
 *        [0, 2, 5, ['Cells may span columns.']],
 *        NULL,
 *        NULL],
 *       [[0, 0, 7, ['body row 3']],
 *        [1, 0, 7, ['Cells may', 'span rows.', '']],
 *        [1, 1, 7, ['- Table cells', '- contain', '- body elements.']],
 *        NULL],
 *       [[0, 0, 9, ['body row 4']], NULL, NULL, NULL]])
 * 
 * The first item is a list containing column widths (colspecs). The second item is a list of head
 * rows, and the third is a list of body rows. Each row contains a list of cells. Each cell is
 * either None (for a cell unused because of another cell's span), or a tuple. A cell tuple contains
 * four items: the number of extra rows used by the cell in a vertical span (morerows); the number
 * of extra columns used by the cell in a horizontal span (morecols); the line offset of the first
 * line of the cell contents; and the cell contents, a list of lines of text.
 * 
 * @author  David Goodger  <goodger@python.org>  Original author of Docutils
 * @author  czukowski
 */
class GridTableParser extends RSTTableParser
{
    private $block;
    private $bottom;
    private $right;
    private $headBodySep;
    private $done;
    private $cells;
    private $rowseps;
    private $colseps;

    /**
     * @param  array  $block
     */
    public function __construct(array $block) {
        $this->block = $block;
    }

    /**
     * Analyze the text block and return a table data structure.
     *
     * Given a plaintext-graphic table in `block` (list of lines of text; no whitespace padding),
     * parse the table, construct and return the data necessary to construct a CALS table or
     * equivalent.
     * 
     * Throw `RuntimeException` if there is any problem with the markup.
     * 
     * @return  array
     * @throws  RuntimeException
     */
    public function parse() {
        $this->setup();
        $this->parseTable();
        return $this->structureFromCells();
    }

    /**
     * Prepare some data structures for parsing.
     */
    protected function setup() {
        $this->headBodySep = $this->findHeadBodySep($this->block);

        for ($i = 0; $i < count($this->block); $i++) {
            $this->block[$i] = $this->splitLine($this->block[$i]);
        }

        $this->bottom = count($this->block) - 1;
        $this->right = count($this->block[0]) - 1;
        $this->done = array_fill(0, count($this->block[0]), -1);
        $this->cells = [];
        $this->rowseps = [0 => [0]];
        $this->colseps = [0 => [0]];
    }

    /**
     * Start with a queue of upper-left corners, containing the upper-left corner of the table
     * itself. Trace out one rectangular cell, remember it, and add its upper-right and lower-left
     * corners to the queue of potential upper-left corners of further cells. Process the queue
     * in top-to-bottom order, keeping track of how much of each text column has been seen.
     * 
     * We'll end up knowing all the row and column boundaries, cell positions and their dimensions.
     */
    protected function parseTable() {
        $corners = [[0, 0]];
        while ($corners) {
            list ($top, $left) = array_shift($corners);
            if ($top === $this->bottom || $left === $this->right || $top <= $this->done[$left]) {
                continue;
            }
            $result = $this->scanCell($top, $left);
            if ( ! $result) {
                continue;
            }
            list ($bottom, $right, $rowseps, $colseps) = $result;
            $this->updateDictOfLists($this->rowseps, $rowseps);
            $this->updateDictOfLists($this->colseps, $colseps);
            $this->markDone($top, $left, $bottom, $right);
            $cellblock = $this->getCellBlock($this->block, $top + 1, $left + 1, $bottom, $right);
            $this->cells[] = [$top, $left, $bottom, $right, $cellblock];
            $corners = array_merge($corners, [[$top, $right], [$bottom, $left]]);
            usort($corners, array($this, 'sortTuples'));
        }
        if ( ! $this->checkParseComplete()) {
            throw new RuntimeException('Malformed table, parse incomplete');
        }
    }

    /**
     * For keeping track of how much of each text column has been seen.
     * 
     * @param  integer  $top
     * @param  integer  $left
     * @param  integer  $bottom
     * @param  integer  $right
     */
    private function markDone($top, $left, $bottom, $right) {
        $before = $top - 1;
        $after = $bottom - 1;
        for ($col = $left; $col < $right; $col++) {
            if ($this->done[$col] !== $before) {
                throw new RuntimeException("Expected to see `done[$col]` value to be $before, actual is ".$this->done[$col]);
            }
            $this->done[$col] = $after;
        }
    }

    /**
     * Each text column should have been completely seen.
     * 
     * @return  boolean
     */
    private function checkParseComplete() {
        $last = $this->bottom - 1;
        for ($col = 0; $col < $this->right; $col++) {
            if ($this->done[$col] !== $last) {
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     * Starting at the top-left corner, start tracing out a cell.
     * 
     * @param   integer  $top
     * @param   integer  $left
     * @return  type
     * @throws  RuntimeException
     */
    private function scanCell($top, $left) {
        if ($this->block[$top][$left] !== '+') {
            throw new RuntimeException("Expected to see '+' at position [$top, $left]");
        }
        return $this->scanRight($top, $left);
    }

    /**
     * Look for the top-right corner of the cell, and make note of all column boundaries ('+').
     * 
     * @param   integer  $top
     * @param   integer  $left
     * @return  array|NULL
     */
    private function scanRight($top, $left) {
        $colseps = [];
        $line = $this->block[$top];
        for ($i = $left + 1; $i <= $this->right; $i++) {
            if ($line[$i] === '+') {
                $colseps[$i] = [$top];
                $result = $this->scanDown($top, $left, $i);
                if ($result) {
                    list($bottom, $rowseps, $newcolseps) = $result;
                    $this->updateDictOfLists($colseps, $newcolseps);
                    return [$bottom, $i, $rowseps, $colseps];
                }
            }
            elseif ($line[$i] !== '-') {
                return NULL;
            }
        }
        return NULL;
    }

    /**
     * Look for the bottom-right corner of the cell, making note of all row boundaries.
     * 
     * @param   integer  $top
     * @param   integer  $left
     * @param   integer  $right
     * @return  array|NULL
     */
    private function scanDown($top, $left, $right) {
        $rowseps = [];
        for ($i = $top + 1; $i <= $this->bottom; $i++) {
            if ($this->block[$i][$right] === '+') {
                $rowseps[$i] = [$right];
                $result = $this->scanLeft($top, $left, $i, $right);
                if ($result) {
                    list ($newrowseps, $colseps) = $result;
                    $this->updateDictOfLists($rowseps, $newrowseps);
                    return [$i, $rowseps, $colseps];
                }
            }
            elseif ($this->block[$i][$right] !== '|') {
                return NULL;
            }
        }
        return NULL;
    }

    /**
     * Noting column boundaries, look for the bottom-left corner of the cell. It must line up with
     * the starting point.
     * 
     * @param   integer  $top
     * @param   integer  $left
     * @param   integer  $bottom
     * @param   integer  $right
     * @return  array|NULL
     */
    private function scanLeft($top, $left, $bottom, $right) {
        $colseps = [];
        $line = $this->block[$bottom];
        for ($i = $right - 1; $i > $left; $i--) {
            if ($line[$i] == '+') {
                $colseps[$i] = [$bottom];
            }
            elseif ($line[$i] != '-') {
                return NULL;
            }
        }
        if ($line[$left] !== '+') {
            return NULL;
        }
        $result = $this->scanUp($top, $left, $bottom, $right);
        if ($result !== NULL) {
            $rowseps = $result;
            return [$rowseps, $colseps];
        }
        return NULL;
    }

    /**
     * Noting row boundaries, see if we can return to the starting point.
     * 
     * @param   integer  $top
     * @param   integer  $left
     * @param   integer  $bottom
     * @param   integer  $right
     * @return  array|NULL
     */
    private function scanUp($top, $left, $bottom, $right) {
        $rowseps = [];
        for ($i = $bottom - 1; $i > $top; $i--) {
            if ($this->block[$i][$left] === '+') {
                $rowseps[$i] = [$left];
            }
            elseif ($this->block[$i][$left] !== '|') {
                return NULL;
            }
        }
        return $rowseps;
    }

    /**
     * From the data collected by `scanCell()`, convert to the final data structure.
     * 
     * @return  array
     */
    protected function structureFromCells() {
        $rowseps = array_keys($this->rowseps);  // List of row boundaries.
        sort($rowseps);
        $rowindex = [];
        for ($i = 0; $i < count($rowseps); $i++) {
            $rowindex[$rowseps[$i]] = $i;       // Row boundary -> row number mapping.
        }
        $colseps = array_keys($this->colseps);  // List of column boundaries.
        sort($colseps);
        $colindex = [];
        for ($i = 0; $i < count($colseps); $i++) {
            $colindex[$colseps[$i]] = $i;       // Column boundary -> col number map.
        }
        $colspecs = [];                         // List of column widths.
        for ($i = 1; $i < count($colseps); $i++) {
            $colspecs[] = $colseps[$i] - $colseps[$i - 1] - 1;
        }
        // Prepare an empty table with the correct number of rows & columns.
        $onerow = array_fill(0, count($colseps) - 1, NULL);
        $rows = array_fill(0, count($rowseps) - 1, $onerow);
        // Keep track of # of cells remaining; should reduce to zero.
        $remaining = (count($rowseps) - 1) * (count($colseps) - 1);
        foreach ($this->cells as $cell) {
            list ($top, $left, $bottom, $right, $block) = $cell;
            $rownum = $rowindex[$top];
            $colnum = $colindex[$left];
            if ($rows[$rownum][$colnum] !== NULL) {
                throw new RuntimeException("Cell (row ".($rownum + 1).", column ".($colnum + 1).") already used");
            }
            $morerows = $rowindex[$bottom] - $rownum - 1;
            $morecols = $colindex[$right] - $colnum - 1;
            $remaining -= ($morerows + 1) * ($morecols + 1);
            // Write the cell into the table.
            $rows[$rownum][$colnum] = [$morerows, $morecols, $top + 1, $block];
        }
        if ($remaining !== 0) {
            throw new RuntimeException("Unused cells remaining");
        }
        if ($this->headBodySep) {
            // Separate head rows from body rows.
            $numheadrows = $rowindex[$this->headBodySep];
            $headrows = array_slice($rows, 0, $numheadrows);
            $bodyrows = array_slice($rows, $numheadrows);
        }
        else {
            $headrows = [];
            $bodyrows = $rows;
        }
        return [$colspecs, $headrows, $bodyrows];
    }
}
