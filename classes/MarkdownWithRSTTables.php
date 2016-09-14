<?php
namespace Cz\Markdown;
use cebe\markdown\Markdown;

/**
 * MarkdownWithRSTTables
 * 
 * @author  czukowski
 */
class MarkdownWithRSTTables extends Markdown
{
    use RSTTableBlockTrait;
}
