<?php
namespace Cz\Markdown;
use RuntimeException;

/**
 * RSTTableParser
 * 
 * This is almost a carbon copy of `TableParser` class from the Docutils module.
 * 
 * @author  David Goodger  <goodger@python.org>  Original author of Docutils
 * @author  czukowski
 */
abstract class RSTTableParser
{
    /**
     * Method to parse the table.
     */
    abstract protected function parseTable();

    /**
     * Method to setup initial data structure for the parser.
     */
    abstract protected function setup();

    /**
     * From the data collected by `scanCell()`, convert to the final data structure.
     * 
     * @return  array
     */
    abstract protected function structureFromCells();

    /**
     * Look for a head/body row separator line; store the line index.
     * 
     * @param   araray  $block
     * @return  integer|NULL
     * @throws  RuntimeException
     */
    protected function findHeadBodySep(array & $block) {
        $headBodySep = NULL;
        for ($i = 0; $i < count($block); $i++) {
            $line = $block[$i];
            if (preg_match('#\+=[=+]+=\+ *$#', $line)) {
                if ($headBodySep) {
                    throw new RuntimeException("Multiple head/body row separators (table lines ".($headBodySep + 1)." and ".($i + 1)."), only one allowed");
                }
                else {
                    $headBodySep = $i;
                    $block[$i] = str_replace('=', '-', $line);
                }
            }
        }
        if ($headBodySep === 0 || $headBodySep === count($block) - 1) {
            throw new RuntimeException("The head/body row separator may not be the first or last line of the table");
        }
        return $headBodySep;
    }

    /**
     * @param   array    $block
     * @param   integer  $top
     * @param   integer  $left
     * @param   integer  $bottom
     * @param   integer  $right
     * @param   boolean  $stripIndent
     * @return  array
     */
    protected function getCellBlock(array & $block, $top, $left, $bottom, $right, $stripIndent = TRUE) {
        $data = [];
        $indent = $right;
        for ($i = $top; $i < $bottom; $i++) {
            $data[] = $line = rtrim(implode('', array_slice($block[$i], $left, $right - $left)));
            if ($line) {
                // Splitting line to characters and counting is supposedly faster than `mb_strlen`...
                $indent = min($indent, count($this->splitLine($line)) - count($this->splitLine(ltrim($line))));
            }
        }
        if ($stripIndent && 0 < $indent && $indent < $right) {
            for ($i = 0; $i < count($data); $i++) {
                // Should be safe to use `substr` here because the indent was calculated based on whitespace.
                $data[$i] = substr($data[$i], $indent);
            }
        }
        return $data;
    }

    /**
     * @param   array   $a
     * @param   array   $b
     * @return  integer
     * @throws  RuntimeException
     */
    protected function sortTuples(array $a, array $b) {
        if (count($a) !== count($b)) {
            throw new RuntimeException('Can only sort arrays of same length');
        }
        for ($i = 0; $i < count($a); $i++) {
            if ($a[$i] > $b[$i]) {
                return 1;
            }
            elseif ($a[$i] < $b[$i]) {
                return -1;
            }
        }
        return 0;
    }

    /**
     * @param   string  $string
     * @reutrn  array
     */
    protected function splitLine($string) {
        return preg_split('##u', $string, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Extend the list values of `$master` with those from `$newdata`. Both parameters must be
     * arrays containing array values.
     * 
     * @param  array  $master
     * @param  array  $newdata
     */
    protected function updateDictOfLists( & $master, $newdata) {
        foreach ($newdata as $key => $values) {
            if ( ! isset($master[$key])) {
                $master[$key] = [];
            }
            array_merge($master[$key], $values);
        }
    }
}
