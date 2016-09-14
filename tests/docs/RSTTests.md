These are test tables from the original Docutils module.

See https://sourceforge.net/p/docutils/code/7970/tree/trunk/docutils/test/test_parsers/test_rst/test_TableParser.py

Expected parse results can be found in `RSTTests.neon` file.

+-------------------------------------+
| A table with one cell and one line. |
+-------------------------------------+

+--------------+--------------+
| A table with | two columns. |
+--------------+--------------+

+--------------+-------------+
| A table with | two columns |
+--------------+-------------+
| and          | two rows.   |
+--------------+-------------+

+--------------------------+
| A table with three rows, |
+------------+-------------+
| and two    | columns.    |
+------------+-------------+
| First and last rows      |
| contain column spans.    |
+--------------------------+

+------------+-------------+---------------+
| A table    | two rows in | and row spans |
| with three +-------------+ to left and   |
| columns,   | the middle, | right.        |
+------------+-------------+---------------+

+------------+-------------+---------------+
| A table |  | two rows in | and funny     |
| with 3  +--+-------------+-+ stuff.      |
| columns,   | the middle, | |             |
+------------+-------------+---------------+

+-----------+-------------------------+
| W/NW cell | N/NE cell               |
|           +-------------+-----------+
|           | Middle cell | E/SE cell |
+-----------+-------------+           |
| S/SE cell               |           |
+-------------------------+-----------+

+--------------+-------------+
| A bad table. |             |
+--------------+             |
| Cells must be rectangles.  |
+----------------------------+

+-------------------------------+
| A table with two header rows, |
+------------+------------------+
| the first  | with a span.     |
+============+==================+
| Two body   | rows,            |
+------------+------------------+
| the second with a span.       |
+-------------------------------+

+-------------------------------+
| A table with two head/body    |
+=============+=================+
| row         | separators.     |
+=============+=================+
| That's bad. |                 |
+-------------+-----------------+

+-------------------------------------+
|                                     |
+-------------------------------------+

