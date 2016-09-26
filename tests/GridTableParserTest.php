<?php
namespace Cz\Markdown;

/**
 * GridTableParserTest
 * 
 * @author  czukowski
 */
class GridTableParserTest extends RSTTablesTestcase
{
    /**
     * @dataProvider  provideSuccessfulParse
     */
    public function testSuccessfulParse($filename, $from, $to, $neon, $section) {
        $expected = $this->loadSectionFromNeonFile($neon, $section);
        $block = $this->loadLinesFromFile($filename, $from, $to);
        $parser = new GridTableParser($block);
        $actual = $parser->parse();
        $this->assertEquals($expected, $actual);
    }

    public function provideSuccessfulParse() {
        return [
            ['RSTTests.md', 6, 8, 'RSTTests.neon', 'oneCellOneLine'],
            ['RSTTests.md', 10, 12, 'RSTTests.neon', 'tableTwoColumns'],
            ['RSTTests.md', 14, 18, 'RSTTests.neon', 'tableTwoColumnsTwoRows'],
            ['RSTTests.md', 20, 27, 'RSTTests.neon', 'tableTreeRowsTwoColumnsSpan'],
            ['RSTTests.md', 35, 39, 'RSTTests.neon', 'tableWithFunnyStuff'],
            ['RSTTests.md', 41, 47, 'RSTTests.neon', 'tableWithWhirlpool'],
            ['RSTTests.md', 55, 63, 'RSTTests.neon', 'tableWithHeadSeparator'],
            ['RSTTests.md', 73, 75, 'RSTTests.neon', 'emptyTable'],
            ['PartOfReadme.md', 30, 41, 'PartOfReadme.neon', 'gridTablesExample'],
            ['TrailingSpaces.md', 0, 7, 'TrailingSpaces.neon', 'trailingSpaces'],
        ];
    }

    /**
     * @dataProvider  provideParseErrors
     */
    public function testParseErrors($filename, $from, $to, $expected) {
        $block = $this->loadLinesFromFile($filename, $from, $to);
        $parser = new GridTableParser($block);
        $this->setExpectedException('RuntimeException', $expected);
        $parser->parse();
    }

    public function provideParseErrors() {
        return [
            ['RSTTests.md', 49, 53, 'Malformed table, parse incomplete'],
            ['RSTTests.md', 65, 71, 'Multiple head/body row separators (table lines 3 and 5), only one allowed'],
        ];
    }
}
