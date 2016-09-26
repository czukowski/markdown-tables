Tables
======

Doctree elements: table, tgroup, colspec, thead, tbody, row, entry.

ReStructuredText provides two syntaxes for delineating table cells: Grid Tables and Simple Tables.

As with other body elements, blank lines are required before and after tables. Tables' left edges
should align with the left edge of preceding text blocks; if indented, the table is considered to
be part of a block quote.

Once isolated, each table cell is treated as a miniature document; the top and bottom cell
boundaries act as delimiting blank lines. Each cell contains zero or more body elements. Cell
contents may include left and/or right margins, which are removed before processing.

Grid Tables
-----------

Grid tables provide a complete table representation via grid-like "ASCII art". Grid tables allow
arbitrary cell contents (body elements), and both row and column spans. However, grid tables can
be cumbersome to produce, especially for simple data sets. The Emacs table mode is a tool that
allows easy editing of grid tables, in Emacs. See Simple Tables for a simpler (but limited)
representation.

Grid tables are described with a visual grid made up of the characters "-", "=", "|", and "+". The
hyphen ("-") is used for horizontal lines (row separators). The equals sign ("=") may be used to
separate optional header rows from the table body (not supported by the Emacs table mode). The
vertical bar ("|") is used for vertical lines (column separators). The plus sign ("+") is used for
intersections of horizontal and vertical lines. Example:

+------------------------+------------+----------+----------+
| Header row, column 1   | Header 2   | Header 3 | Header 4 |
| (header rows optional) |            |          |          |
+========================+============+==========+==========+ 
| body row 1, column 1   | column 2   | column 3 | column 4 |
+------------------------+------------+----------+----------+
| body row 2             | Cells may span columns.          |
+------------------------+------------+---------------------+
| body row 3             | Cells may  | - Table cells       |
+------------------------+ span rows. | - contain           |
| body row 4             |            | - body elements.    |
+------------------------+------------+---------------------+

Some care must be taken with grid tables to avoid undesired interactions with cell text in rare
cases. For example, the following table contains a cell in row 2 spanning from column 2 to column 4:

+--------------+----------+-----------+-----------+
| row 1, col 1 | column 2 | column 3  | column 4  |
+--------------+----------+-----------+-----------+
| row 2        |                                  |
+--------------+----------+-----------+-----------+
| row 3        |          |           |           |
+--------------+----------+-----------+-----------+

If a vertical bar is used in the text of that cell, it could have unintended effects if accidentally
aligned with column boundaries:

+--------------+----------+-----------+-----------+
| row 1, col 1 | column 2 | column 3  | column 4  |
+--------------+----------+-----------+-----------+
| row 2        | Use the command ``ls | more``.   |
+--------------+----------+-----------+-----------+
| row 3        |          |           |           |
+--------------+----------+-----------+-----------+

Several solutions are possible. All that is needed is to break the continuity of the cell outline
rectangle. One possibility is to shift the text by adding an extra space before:

+--------------+----------+-----------+-----------+
| row 1, col 1 | column 2 | column 3  | column 4  |
+--------------+----------+-----------+-----------+
| row 2        |  Use the command ``ls | more``.  |
+--------------+----------+-----------+-----------+
| row 3        |          |           |           |
+--------------+----------+-----------+-----------+

Another possibility is to add an extra line to row 2:

+--------------+----------+-----------+-----------+
| row 1, col 1 | column 2 | column 3  | column 4  |
+--------------+----------+-----------+-----------+
| row 2        | Use the command ``ls | more``.   |
|              |                                  |
+--------------+----------+-----------+-----------+
| row 3        |          |           |           |
+--------------+----------+-----------+-----------+

Simple Tables
-------------

Simple tables provide a compact and easy to type but limited row-oriented table representation for
simple data sets. Cell contents are typically single paragraphs, although arbitrary body elements
may be represented in most cells. Simple tables allow multi-line rows (in all but the first column)
and column spans, but not row spans. See Grid Tables above for a complete table representation.
