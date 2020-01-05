<?php

// Test dancing links

// Test sudoko X (exactly one solution)
$input = array( 
		array(1,2,4),		array(2,4,7),		array(3,1,2),		array(3,2,7),
		array(3,4,4),		array(3,5,3),		array(4,6,8),		array(4,9,9),
		array(5,2,9),		array(5,9,2),		array(6,1,8),		array(6,2,3),
		array(6,3,1),		array(6,5,5),		array(7,1,5),		array(7,2,8),
		array(7,7,2),		array(8,3,2),		array(8,8,6),		array(9,3,3),
		array(9,7,8),		array(9,8,4),       array(9,9,7)	
	);

$nam = new SudokuXDancingLinks (3, $input);

// Print matrix
$n = $nam->search(0);
if ($n == 0)  { 
	echo "No solutions found\n";
} else 
	echo $n, " solutions found\n";
	
?>
