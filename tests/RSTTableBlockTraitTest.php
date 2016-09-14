<?php
namespace Cz\Markdown;

/**
 * RSTTableBlockTraitTest
 * 
 * @author  czukowski
 * 
 * @property  RSTTableBlockTrait  $object
 */
class RSTTableBlockTraitTest extends Testcase
{
    /**
     * @dataProvider  provideIdentifyGridTable
     */
    public function testIdentifyGridTable($filename, $line, $expected, $endLine) {
        $lines = $this->loadLinesFromFile($filename);
        $actual = $this->callObjectMethod('identifyGridTable', [$lines[$line], & $lines, $line]);
        $this->assertSame($expected, $actual);
        $gridTableBlocks = $this->getObjectAttribute($this->object, 'gridTableBlocks');
        if ($expected === FALSE) {
            $this->assertEmpty($gridTableBlocks);
        }
        else {
            $this->assertArrayHasKey($line, $gridTableBlocks);
            $this->assertSame($endLine, $gridTableBlocks[$line]);
        }
    }

    public function provideIdentifyGridTable() {
        return [
            ['PartOfReadme.md', 0, FALSE, NULL],
            ['PartOfReadme.md', 29, FALSE, NULL],
            ['PartOfReadme.md', 30, TRUE, 41],
            ['TableEndsLastRow.md', 0, TRUE, 6],
            ['TableMissingBottomLine.md', 0, TRUE, 4],
            ['MalformedTables.md', 0, FALSE, NULL],
            ['MalformedTables.md', 8, FALSE, NULL],
            ['MalformedTables.md', 16, FALSE, NULL],
        ];
    }

    /**
     * @dataProvider  provideRenderValidGridTable
     */
    public function testRenderValidGridTable($neon, $section, $html, $from, $to) {
        $this->object->expects($this->any())
            ->method('renderAbsy')
            ->will($this->returnCallback(function($blocks) {
                return '[inner block: '.implode("\n", $blocks).']';
            }));
        $this->object->expects($this->any())
            ->method('parseBlocks')
            ->will($this->returnArgument(0));
        $expected = implode("\n", $this->loadLinesFromFile($html, $from, $to));
        $block = $this->loadSectionFromNeonFile($neon, $section);
        $actual = $this->callObjectMethod('renderGridTable', [$block]);
        // Compare strings while ignoring whitespace differences before and after blocks.
        $this->assertEquals(trim($expected), trim($actual));
    }

    public function provideRenderValidGridTable() {
        return [
            ['RSTTests.neon', 'oneCellOneLine', 'RSTTests.html', 6, 12],
            ['RSTTests.neon', 'tableTwoColumns', 'RSTTests.html', 14, 21],
            ['RSTTests.neon', 'tableTwoColumnsTwoRows', 'RSTTests.html', 23, 34],
            ['RSTTests.neon', 'tableTreeRowsTwoColumnsSpan', 'RSTTests.html', 36, 52],
            ['RSTTests.neon', 'tableThreeColumnsTwoRowsSpan', 'RSTTests.html', 54, 73],
            ['RSTTests.neon', 'tableWithFunnyStuff', 'RSTTests.html', 75, 94],
            ['RSTTests.neon', 'tableWithWhirlpool', 'RSTTests.html', 96, 118],
            ['RSTTests.neon', 'tableWithHeadSeparator', 'RSTTests.html', 120, 139],
            ['RSTTests.neon', 'emptyTable', 'RSTTests.html', 141, 147],
            ['PartOfReadme.neon', 'gridTablesExample', 'PartOfReadme.html', 6, 55],
        ];
    }

    public function setUp() {
        $this->object = $this->getMockForTrait(__NAMESPACE__.'\RSTTableBlockTrait');
    }

}
