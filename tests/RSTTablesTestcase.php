<?php
namespace Cz\Markdown;
use Nette\Neon\Neon,
    PHPUnit_Framework_TestCase,
    ReflectionMethod;

/**
 * RSTTablesTestcase
 * 
 * @author  czukowski
 */
class RSTTablesTestcase extends PHPUnit_Framework_TestCase
{
    protected $object;

    /**
     * @param   string  $name
     * @param   array   $args
     * @return  mixed
     */
    protected function callObjectMethod($name, array $args = []) {
        $method = new ReflectionMethod($this->object, $name);
        $method->setAccessible(TRUE);
        return $method->invokeArgs($this->object, $args);
    }

    /**
     * @param   string   $filename
     * @param   integer  $from
     * @param   integer  $to
     * @return  array
     */
    protected function loadLinesFromFile($filename, $from = NULL, $to = NULL) {
        $lines = $this->loadAllLinesFromFile($filename);
        if ($from === NULL || $to === NULL) {
            return $lines;
        }
        $block = [];
        for ($i = $from; $i <= $to; $i++) {
            $block[] = $lines[$i];
        }
        return $block;
    }

    /**
     * @param   string  $filename
     * @return  array
     */
    protected function loadAllLinesFromFile($filename) {
        $content = str_replace(["\r\n", "\n\r", "\r"], "\n", $this->loadFileContents($filename));
        return explode("\n", $content);
    }

    /**
     * @param   string  $filename
     * @param   string  $section
     * @return  array
     */
    protected function loadSectionFromNeonFile($filename, $section) {
        $content = Neon::decode($this->loadFileContents($filename));
        return $content[$section];
    }

    /**
     * @param   string  $filename
     * @return  string
     */
    private function loadFileContents($filename) {
        $path = __DIR__.'/docs/'.$filename;
        return file_get_contents($path);
    }
}
