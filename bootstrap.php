<?php
/**
 * Get ONLY used Math-PHP classes.
 */

// Include Exceptions of Math-PHP.
require 'markrogoyski/math-php/src/Exception/MathException.php';
require 'markrogoyski/math-php/src/Exception/BadDataException.php';
require 'markrogoyski/math-php/src/Exception/BadParameterException.php';
require 'markrogoyski/math-php/src/Exception/DivisionByZeroException.php';
require 'markrogoyski/math-php/src/Exception/FunctionFailedToConvergeException.php';
require 'markrogoyski/math-php/src/Exception/IncorrectTypeException.php';
require 'markrogoyski/math-php/src/Exception/MatrixException.php';
require 'markrogoyski/math-php/src/Exception/NanException.php';
require 'markrogoyski/math-php/src/Exception/OutOfBoundsException.php';
require 'markrogoyski/math-php/src/Exception/SingularMatrixException.php';
require 'markrogoyski/math-php/src/Exception/VectorException.php';

// Functions.
require 'markrogoyski/math-php/src/Functions/Support.php';
require 'markrogoyski/math-php/src/Functions/Map/Single.php';

// Number.
require 'markrogoyski/math-php/src/Number/ObjectArithmetic.php';
require 'markrogoyski/math-php/src/Number/Complex.php';

// Number theory.
require 'markrogoyski/math-php/src/NumberTheory/Integer.php';

// Algebra.
require 'markrogoyski/math-php/src/Algebra.php';

// Linear algebra.
require 'markrogoyski/math-php/src/LinearAlgebra/Matrix.php';
require 'markrogoyski/math-php/src/LinearAlgebra/MatrixFactory.php';
require 'markrogoyski/math-php/src/LinearAlgebra/MatrixCatalog.php';
require 'markrogoyski/math-php/src/LinearAlgebra/NumericMatrix.php';
require 'markrogoyski/math-php/src/LinearAlgebra/NumericSquareMatrix.php';

// Include RREF Class.
require 'markrogoyski/math-php/src/LinearAlgebra/Reduction/RowEchelonForm.php';
require 'markrogoyski/math-php/src/LinearAlgebra/Reduction/ReducedRowEchelonForm.php';

/**
 * Get CLASSES of chemistry equation balancer.
 */
require 'class-math-utils.php';
require 'class-chemistry.php';
require 'class-chemical-equation.php';
require 'class-chemical-equation-balancer.php';
require 'class-chemical-equation-phrase.php';