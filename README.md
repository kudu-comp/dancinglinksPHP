# Dancing links in PHP

#### Description
Implementation of Knuth' dancing links algorithm in PHP. It also contains two examples of using the algorithm to solve a Sudoku and Sudoku X.

The dlxToolkit file contains the Node and DancingLinks classes. The nodes are necessary for the DancingLinks to work.

There is also a Sudoku class, which builds the initial DancingLinks network and then applies all the known constraints. The SudokuX class extends the Sudoku class, it only has an update addConstraints the add the diagonal constraints. If you wish to add other Sudoku types it should be enough to just code the new addConstraints.
