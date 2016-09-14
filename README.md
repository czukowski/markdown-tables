Grid Tables for cebe/markdown
=============================

This package adds support for reStructuredText's (RST) [Grid Tables][grid-tables] syntax to [cebe's
Markdown][cebe/markdown] implementation.

The motivation behind it is that technical documentation often requires a support for tables that
are complex, can span rows and columns and contain block elements. Grid Tables for RST provide that,
even though they're harder to type than other table implementations in Markdown.

The current implementation is a port of Grid Tables parser from [Docutils][docutils] module
originally written in Python by David Goodger. Note that Docutils's scope is far greater than Grid
Tables and my intention was not to replicate all of it. One considered extension is eventually
adding support for [Simple Tables][simple-tables] syntax, especially since the current code
structure makes it relatively easy task.

Installation
------------

Recommended installation is via [composer][composer] by running:

    composer require czukowski/markdown-tables "~1.0"

Alternatively you may add the following to the `require` section of your project's `composer.json`
manually and then run `composer update` from the command line:

```json
"czukowski/markdown-tables: "~1.0"
```

Usage
-----

This package provides a `RSTTableBlockTrait` that may be used in classes extending the cebe's
original Markdown parsers.

Alternatively, three extensions are provided for easy use (pick one that suits best):

```php
use Cz\Markdown;

$markdownWithRSTTables = new MarkdownWithRSTTables;
$githubMarkdownWithRSTTables = new GithubMarkdownWithRSTTables;
$markdownExtraWithRSTTables = new MarkdownExtraWithRSTTables;
```

For more information refer to the [original Readme file][markdown-usage].

License
-------

The distribution is permitted under the MIT License. See LICENSE.md for details.


  [cebe/markdown]: https://github.com/cebe/markdown
  [markdown-usage]: https://github.com/cebe/markdown#usage
  [docutils]: https://sourceforge.net/projects/docutils/
  [grid-tables]: http://docutils.sourceforge.net/docs/ref/rst/restructuredtext.html#grid-tables
  [simple-tables]: http://docutils.sourceforge.net/docs/ref/rst/restructuredtext.html#simple-tables
