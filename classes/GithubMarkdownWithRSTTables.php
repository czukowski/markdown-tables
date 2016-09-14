<?php
namespace Cz\Markdown;
use cebe\markdown\GithubMarkdown;

/**
 * GithubMarkdownWithRSTTables
 * 
 * @author  czukowski
 */
class GithubMarkdownWithRSTTables extends GithubMarkdown
{
    use RSTTableBlockTrait;
}
