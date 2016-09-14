<?php
namespace Cz\Markdown;
use RuntimeException;

/**
 * RSTTableBlockTrait
 * 
 * Heavily inspired by Docutils' RST parser.
 * 
 * @see     https://sourceforge.net/p/docutils/code/HEAD/tree/trunk/docutils/docutils/parsers/rst
 * @author  David Goodger  <goodger@python.org>  Original author of Docutils
 * @author  czukowski
 */
trait RSTTableBlockTrait
{
    /**
     * @var  string  Table attributes may be added here (please include a leading space!).
     */
    public $gridTableAttributes = '';
    /**
     * @var  array  Key-value pairs of starting and ending lines of grid tables blocks.
     */
    private $gridTableBlocks = [];
    /**
     * @var  array  Regex patterns that are used by Grid Table parser.
     */
    private static $gridTablePatterns = [
        'left-edge' => '#^[|+]#',
        'right-edge' => '#[|+]$#',
        'row-separator' => '#\+-[-+]+-\+ *$#',
    ];

    /**
     * Identify Grid Table block. If Grid Table is detected, also finds and stores its ending
     * line for later use.
     * 
     * @see  https://sourceforge.net/p/docutils/code/7970/tree/trunk/docutils/docutils/parsers/rst/states.py#l1675
     * 
     * @param   string   $line
     * @param   array    $lines
     * @param   integer  $current
     * @return  boolean
     */
    protected function identifyGridTable($line, & $lines, $current) {
        if ( ! $this->checkGridTablePattern('row-separator', $line)) {
            // Return FALSE right away if row separator pattern doesn't match input line.
            return FALSE;
        }
        // Calculate line width.
        $width = strlen(trim($line));
        // Calculate lines block bounds of the table.
        $start = $current;
        while ($this->checkGridTableLeftEdge($lines, $current)) {
            $current++;
        }
        $end = $current - 1;
        // Look for table bottom for tables without the bottom line.
        if ( ! $this->checkGridTablePattern('row-separator', $lines[$end])) {
            for ($i = $current - 2; $i > 1; $i--) {
                if ($this->checkGridTablePattern('row-separator', $lines[$i])) {
                    $end = $i;
                    break;
                }
            }
        }
        // Check right edge and row width and return FALSE if anything's wrong.
        for ($i = $start; $i <= $end; $i++) {
            if ( ! $this->checkGridTablePattern('right-edge', rtrim($lines[$i]))
                || mb_strlen($lines[$i]) !== $width
            ) {
                return FALSE;
            }
        }
        $this->gridTableBlocks[$start] = $end;
        return TRUE;
    }

    /**
     * @param   array    $lines
     * @param   integer  $current
     * @return  boolean
     */
    private function checkGridTableLeftEdge( & $lines, $current) {
        if (isset($lines[$current])) {
            $trimmedLine = ltrim($lines[$current]);
            return $trimmedLine && $this->checkGridTablePattern('left-edge', $trimmedLine);
        }
        return FALSE;
    }

    /**
     * @param   string  $pattern
     * @param   string  $line
     * @return  boolean
     */
    private function checkGridTablePattern($pattern, $line) {
        return preg_match(self::$gridTablePatterns[$pattern], $line);
    }

    /**
     * Creates a `GridTableParser` instance to parse a Grid Table block.
     * 
     * @see  https://sourceforge.net/p/docutils/code/7970/tree/trunk/docutils/docutils/parsers/rst/tableparser.py#l147
     * 
     * @param   array    $lines
     * @param   integer  $start
     * @return  array
     * @throws  RuntimeException
     */
    protected function consumeGridTable( & $lines, $start) {
        if ( ! isset($this->gridTableBlocks[$start])) {
            throw new RuntimeException('Grid table block key must have been set in `identifyGridTable()` method for line '.$start);
        }
        $table = [];
        for ($i = $start; $i <= $this->gridTableBlocks[$start]; $i++) {
            $table[] = $lines[$i];
        }
        $parser = new GridTableParser($table);
        try {
            // Try parsing the table.
            $block = $parser->parse();
            array_unshift($block, 'gridTable');
        }
        catch (RuntimeException $e) {
            // Fallback for parsing errors: use unparsed table.
            $block = ['code', 'content' => implode("\n", $table)];
        }
        return [$block, $this->gridTableBlocks[$start]];
    }

    /**
     * @param   mixed  $block
     * @return  string
     */
    protected function renderGridTable($block) {
        return $this->renderRSTTable($block);
    }

    /**
     * @param   mixed  $block
     * @return  string
     */
    private function renderRSTTable($block) {
        list (, , $head, $body) = $block;
        $content = '';
        // Add table heading.
        if ($head) {
            $content .= "\t<thead>\n".$this->renderRSTTableBody($head, 'th')."\t</thead>\n";
        }
        // Add table body.
        if ($body) {
            $content .= "\t<tbody>\n".$this->renderRSTTableBody($body, 'td')."\t</tbody>\n";
        }
        return "<table{$this->gridTableAttributes}>\n$content</table>\n";
    }

    /**
     * @param   array   $rows
     * @param   string  $cellTag
     * @return  string
     */
    private function renderRSTTableBody(array $rows, $cellTag) {
        $content = '';
        foreach ($rows as $row) {
            $content .= "\t\t<tr>\n";
            foreach ($row as $cell) {
                if ($cell === NULL) {
                    continue;
                }
                $content .= "\t\t\t<$cellTag{$this->renderRSTTableCellAttributes($cell)}>"
                    .$this->renderRSTTableCellContent($cell)
                    ."</$cellTag>\n";
            }
            $content .= "\t\t</tr>\n";
        }
        return $content;
    }

    /**
     * @param   array  $cell
     * @return  string
     */
    private function renderRSTTableCellAttributes(array & $cell) {
        list ($morerows, $morecols, ) = $cell;
        $attributes = '';
        if ($morerows) {
            $attributes .= ' rowspan="'.($morerows + 1).'"';
        }
        if ($morecols) {
            $attributes .= ' colspan="'.($morecols + 1).'"';
        }
        return $attributes;
    }

    /**
     * @param   mixed  $cell
     * @reutrn  string
     */
    private function renderRSTTableCellContent(array & $cell) {
        list (, , , $content) = $cell;
        $parsedContent = $this->renderAbsy($this->parseBlocks($content));
        if (count($content) === 1) {
            return $parsedContent;
        }
        return "\n\t\t\t\t".$parsedContent."\n\t\t\t";
    }

    abstract protected function parseBlocks($lines);
    abstract protected function renderAbsy($blocks);
}
