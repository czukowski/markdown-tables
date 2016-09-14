<?php
namespace Cz\Markdown;
use cebe\markdown\MarkdownExtra;

/**
 * MarkdownExtraWithRSTTables
 * 
 * @author  czukowski
 */
class MarkdownExtraWithRSTTables extends MarkdownExtra
{
    use RSTTableBlockTrait;
}
