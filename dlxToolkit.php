<?php

//https://www.geeksforgeeks.org/exact-cover-problem-algorithm-x-set-2-implementation-dlx/
ini_set('memory_limit', '512M');

class Node {

  public $left;
  public $right;
  public $up;
  public $down;
  public $column;
  public $data;
  public $rowID = 0;
  public $colID = 0;
  public $nodeCount = 0;

  function __Construct($rowID = 0, $colID = 0, $data="") {
     $this->left  = null;
     $this->right = null;
     $this->up    = null;
     $this->down  = null;
     $this->column = null;
     $this->rowID = $rowID;
     $this->colId = $colID;
     $this->data = $data;
     $this->nodeCount = 0;
  }
}

class DancingLinks {  

    public $header;
    public $nm;
    public $nCol = 0;
    public $nRow = 0;
    public $solutions;
	public $clues = array();
	public $solCount = 0;

    function getRight ($i) { return ($i+1) % $this->nCol; } 
    function getLeft ($i)  { return ($i-1 < 0) ? $this->nCol-1 : $i-1 ; } 
    function getUp ($i)    { return ($i-1 < 0) ? $this->nRow : $i-1 ; }   
    function getDown ($i)  { return ($i+1) % ($this->nRow+1); } 

    function __Construct ($matrix, $nRow, $nCol, $clues) {

        $this->nCol = $nCol;
        $this->nRow = $nRow;
        $this->solutions = new SplStack();
		if ($clues != null) $this->clues = $clues;
		
        // Matrix contains non zero fields to be linked, size nRow by nCol
        // Create the matrix of empty unlinked nodes 
		for($i = 0; $i <= $nRow; $i++)    { 
            for($j = 0; $j < $nCol; $j++) {
    	        $this->nm[$i][$j] = new Node($i,$j, "R".$i."C".$j);
				//echo "Matrix[",$i,"][",$j,"]\n";
            }
    	}

        // Header node, contains pointer to the header node of first column 
        $this->header = new Node (0,-1,"Header");
    
        // One extra row for list header nodes for each column 
        for($i = 0; $i <= $nRow; $i++) {
     
            for($j = 0; $j < $nCol; $j++)  { 
			
				//echo "Matrix[",$i,"][",$j,"]\n";
               
                if ($matrix[$i][$j] != 0) {
    
                    // Increment node count in column header
                    if ($i!=0) $this->nm[0][$j]->nodeCount++;
        
      	            // Add pointer to column header for this node 
     	            $this->nm[$i][$j]->column = $this->nm[0][$j]; 
    
    	            // Link the node with neighbours
                    // Left pointer
                    $a = $i; $b = $j;
                    do { $b = $this->getLeft($b); }
        	        while (($matrix[$a][$b] == 0) && ($b != $j));
                    $this->nm[$i][$j]->left = $this->nm[$i][$b];
    
                    // Right pointer
    	            $a = $i; $b = $j;
    	            do { $b = $this->getRight($b); } 
    	            while (($matrix[$a][$b] == 0) && ($b != $j));
                    $this->nm[$i][$j]->right = $this->nm[$i][$b];
    
    	            // Up pointer
    	            $a = $i; $b = $j;
    	            do { $a = $this->getUp($a); }
    		        while (($matrix[$a][$b] == 0) && ($a != $i));
                    $this->nm[$i][$j]->up = $this->nm[$a][$j];
    
    	            // Down pointer
    	            $a = $i; $b = $j;
    	            do { $a = $this->getDown($a); }
    		        while (($matrix[$a][$b] == 0) && ($a != $i));
                    $this->nm[$i][$j]->down = $this->nm[$a][$j];
                }
            }
        }
    
        //Link header node
        $this->header->right         = $this->nm[0][0];
        $this->header->left          = $this->nm[0][$nCol-1];
        $this->nm[0][0]->left        = $this->header;
        $this->nm[0][$nCol-1]->right = $this->header;
		
		//Process clues (an array of row ids)
		foreach ($clues as $clue) {
			//Find first column for this row
			$col = 0;
			while (($this->nm[$clue][$col]->column === null) && ($col < $this->nCol)) $col++;
			// Cover column if one found (clues might overlap)
			if ($this->nm[$clue][$col]->column !== null) {
				//echo "Clue: ", $clue, "\tColumn: ", $col, "\n";
				$this->cover ($this->nm[0][$col]);
				// Cover column
				$rowNode = $this->nm[$clue][$col];
				// Add row to solution
				$this->solutions->push($rowNode);
				// Traverse all nodes in the current row
				// and cover all columns that they belong to  
				for ($rightNode = $rowNode->right; $rightNode !== $rowNode; $rightNode = $rightNode->right) {
					$this->cover ($rightNode);
				}
			}
		}
    
    } // End of constructor

    function printNodes () {

		echo "Header data:  ", $this->header->data, "\n";
		echo "Header left:  ", $this->header->left->data, "\n";
		echo "Header right: ", $this->header->right->data, "\n\n";
		
		for($j = 0; $j < $this->nCol; $j++)
			echo "Column ", $j, "\tNodes: ", $this->nm[0][$j]->nodeCount, "\n";
		echo "\n";
		
		for($i = 0; $i <= $this->nRow; $i++)    { 
			for($j = 0; $j < $this->nCol; $j++)  { 
				if ($this->nm[$i][$j]->column != null) {
					echo "Node: ", $this->nm[$i][$j]->data, "\n";
					echo "Nodecount: ", $this->nm[$i][$j]->nodeCount, "\n";
					echo "Col: ", $this->nm[$i][$j]->column->data, "\n";
					echo "Left: ", $this->nm[$i][$j]->left->data, "\n";
					echo "Right: ", $this->nm[$i][$j]->right->data, "\n";
					echo "Up: ", $this->nm[$i][$j]->up->data, "\n";
					echo "Down: ", $this->nm[$i][$j]->down->data, "\n\n";
				}
			}
		}
    } // End of printNodes
    
    function cover ($targetNode) {

		// Cover given column completely
		// echo "Cover node: ", $targetNode->data, "\n";
		$colNode =  $targetNode->column;

        // Unlink column header from it's neighbours
        $colNode->left->right = $colNode->right;
        $colNode->right->left = $colNode->left;

    	// Move down the column and remove each row by traversing right 
        for ($row = $colNode->down; $row !== $colNode; $row = $row->down) {
	        for ($rightNode = $row->right; $rightNode !== $row; $rightNode = $rightNode->right) {
				$rightNode->up->down = $rightNode->down;
				$rightNode->down->up = $rightNode->up;
				// Decrement node count in column header
				$this->nm[0][$rightNode->colID]->nodeCount--;
    	    }
    	}    
    } // End of cover
    
    function unCover ($targetNode) {
		
		// Uncover given column completely
		// echo "Uncover node: ", $targetNode->data, "\n";
		$colNode =  $targetNode->column;

        // Move up the column and link back each row by traversing left
        for ($row = $colNode->up; $row !== $colNode; $row = $row->up) {
	        for ($leftNode = $row->left; $leftNode !== $row; $leftNode = $leftNode->left) {
    	    	$leftNode->up->down = $leftNode;
	    	    $leftNode->down->up = $leftNode;
    		    // Increment node count in column header
       		    $this->nm[0][$leftNode->colID]->nodeCount++;
	        }
	    }  

		// Link column header to neighbours
        $colNode->left->right = $colNode;
        $colNode->right->left = $colNode;
        
    } // End of uncover
    
    function getMinColumn () {
      
        $minCol = $this->header->right;
        $h = $minCol->right;
        while ($h !== $this->header) {
            if ($h->nodeCount < $minCol->nodeCount) $minCol = $h;
            $h = $h->right;
        };
        
        return $minCol;
        
    } // End of getMinColumn
    
    function printSolutions () {
        echo "\nSolution: ";
        foreach ($this->solutions as $n) echo $n->rowID, " ";
        echo "\n";
    }
	
    function search ($k) {

        if ($this->header->right === $this->header) {
            $this->printSolutions();
			$this->solCount++;
            return;
        }

        // Choose column
        $column = $this->getMinColumn();
		
    
        // Cover column
        $this->cover ($column);
    
		// Try all rows for the selected column
        for ($rowNode = $column->down; $rowNode !== $column; $rowNode = $rowNode->down) {
    	
    	    $this->solutions->push($rowNode);

			// Traverse all nodes in the current row
			// and cover all columns that they belong to  
    	    for ($rightNode = $rowNode->right; $rightNode !== $rowNode; $rightNode = $rightNode->right) {
    		    $this->cover ($rightNode);
    	    }
        
            // Recursive call to next level
            $this->search ($k+1);
        
            // Backtrack solution
            $this->solutions->pop();
        
            // Uncover columns for selected row
            // $column = $rowNode->column;
            for ($leftNode = $rowNode->left; $leftNode !== $rowNode; $leftNode = $leftNode->left) {
        	    $this->uncover ($leftNode);
            }
        }

		// Uncover column
        $this->uncover($column);
		return ($this->solCount); 
		
    } // End of search
    
}  //End of class DancingLinks


class SudokuDancingLinks extends DancingLinks {
	
	protected $size = 0;
	protected $mat = array();

	function __Construct ($size, $input) {
		
		// Sudoku with size n squares and input array of r,c,n
		$this->size = $size;
		$boxSize = $size**2;
		$boardSize = $boxSize ** 2;
		$nRows = $boxSize ** 3;
		$nCols = $boardSize * 4;
		
		// Empty matrix to start with
		// Create column headers in row 0
		for ($i=0; $i<=$nRows; $i++) {
			for ($j=0; $j<$nCols; $j++) {
				$this->mat[$i][$j] = ($i==0 ? 1 : 0);
			}
		}
		
		// Build the constraints
		// echo "Box : ", $boxSize, "\n";
		// echo "Rows: ", $nRows, "\tCols: ", $nCols, "\n";
		for ($i=0; $i<$boxSize; $i++) {
			for ($j=0; $j<$boxSize; $j++) {
				for ($k=0; $k<$boxSize; $k++) {

					//Row n: place number $k+1 in column $j+1 and row $i+1
					//Remember row 0 contains the columnheaders
					$n = $i*$boardSize + $j*$boxSize + $k + 1; 
					
					//RiCj constraint starting at column 0 - ordered R1C1 R1C2 ... R9C9
					$this->mat[$n][$i*$boxSize + $j] = 1;

					//Ri#k constraint starting at column 81 - ordered R1#1 R1#2 ... R9#9
					$this->mat[$n][$boardSize + $i*$boxSize + $k] = 1;

					//Cj#k constraint starting at column 162 - ordered C1#1 C1#2 ... C9#9
					$this->mat[$n][$boardSize*2 + $j*$boxSize + $k] = 1;

					//Bb#k constraint starting at column 243 - ordered B1#1 B1#2 ... B9#9
					$b = $size * intdiv($i,$size) + intdiv($j,$size);
					$this->mat[$n][$boardSize*3 + $b*$boxSize + $k] = 1;
				}
			}
		}
		
		// Add constraints for other sudoku types
		// Function must return the updated number of columns
		$nCols = $this->addConstraints();
		
		// Fill the clues rows are 1..9 columns 1..9
		// e.g. input[1][2][3] means a 3 in row 1 column 2
		$clues = array();
		foreach ($input as $value) {
			$clues[] = ($value[0]-1)*$boardSize + ($value[1]-1)*$boxSize + $value[2];
		}
		
		// Call parent constructor
		parent::__Construct ($this->mat, $nRows, $nCols, $clues);
		
	} // End of overridden constructor
	
	
	function addConstraints () {
		// Do nothing here just return right number of columns
		$boxSize = $this->size**2;
		$boardSize = $boxSize ** 2;
		$nCols = $boardSize * 4;
		return $nCols;
	}
	
	function printSolutions () {
		// Print the solution
		echo "Solution: \n";
		$boxSize = $this->size**2;
		$boardSize = $boxSize ** 2;
		$sol = array();
		// Calculate row, col and n from row id
		// $n = $row * $boardSize + $col * $boxSize + $k + 1;
		foreach ($this->solutions as $n) {
			$row = intdiv($n->rowID-1, $boardSize) + 1;
			$col = intdiv (($n->rowID-1) % $boardSize, $boxSize) + 1;
			$num = ($n->rowID-1) % $boxSize + 1;
			$sol[]= array($row,$col,$num);;
		}
		// Sort resulting array and print it
		sort ($sol);
		foreach ($sol as $s) {
			echo $s[2];
			if ($s[1] == 9) echo "\n";
		}
		echo "\n";
	} // End of overridden printSolutions
	
} // End of SudokuDancingLinks

class SudokuXDancingLinks extends SudokuDancingLinks {

	function addConstraints () {
		// Add two constraints for the diagonals
		$boxSize = $this->size**2;
		$boardSize = $boxSize ** 2;
		$nRows = $boxSize ** 3;
		$nCols = $boardSize * 4 + 2*$boxSize;
		// Append additional columns
		// Empty matrix to start with
		// Create column headers in row 0
		for ($i=0; $i<=$nRows; $i++) {
			for ($j=$boardSize * 4; $j<$nCols; $j++) {
				$this->mat[$i][$j] = ($i==0 ? 1 : 0);
			}
		}
		// Fill the diagonal constraints
		for ($i=0; $i<$boxSize; $i++) {
			for ($k=0; $k<$boxSize; $k++) {
				$n = $i*$boardSize + $i*$boxSize + $k + 1;
				$this->mat[$n][$boardSize*4 + $k] = 1;
				$n = $i*$boardSize + ($boxSize-$i-1)*$boxSize + $k + 1;
				$this->mat[$n][$boardSize*4 + $boxSize + $k] = 1;
			}
		}
		return $nCols;
	}
		
} // End of SudokuXDancingLinksDancingLinks



?>
