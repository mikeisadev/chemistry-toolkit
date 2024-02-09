### Chemistry ToolKit

* Programmer:       Michele Mincone
* Main language:    PHP
* Started on:       18 November 2023
* Status:           beta (under development, important features are missing!)

A simple library to work with chemistry and mathematics.

================================================================

Features.
------------

*   Chemical equations:
    - Chemical equation balanacer [AVAILABLE]
    - Describe a balanced chemical equation with a phrase [AVAILABLE]
    - Calc mols, grams and n. of particles for each molecule in a balanced chemical equation [NOT-AVAILABLE]

*   Molecules:
    - Get the molecular mass of a molecule [NOT-AVAILABLE]
    - Get the % composition of each element in a molecule [NOT-AVAILABLE]

*   pH:
    - Calc the pH of a given solution [NOT-AVAILABLE]

*   Solutions:
    - Calculate the concentration % m/m (mass on mass) of a solution [NOT-AVAILABLE]
    - Calculate the concentration % m/v (mass on volume) of a solution [NOT-AVAILABLE]
    - Calculate the concentration % V/V (volume on volume) of a solution [NOT-AVAILABLE]
    - Calculate molarity of a solution [NOT-AVAILABLE]
    - Calculate molality of a solution [NOT-AVAILABLE]

*   Math:
    - Fibonacci sequence calculator [NOT-AVAILABLE]
    - Summation [NOT-AVAILABLE]
    - Production [NOT-AVAILABLE]

================================================================

Dependencies.
---------------

*   This library uses MathPHP (https://github.com/markrogoyski/math-php) library to solve most of the mathematical problems.

================================================================

Requirements.
---------------

*   PHP version: 8.2 or greater

================================================================

Important notes.
-------------------

NOTE: not all the equations are supported. Now only equations like these are supported:

- Ca(NO3)2 + KCl = KNO3 + CaCl2            (double exchange reaction)
- S + O2 = SO3                             (synthesis reaction)
- Fe + Cl2 = FeCl3                         (synthesis reaction)
- KNO3 = K2O + N2 + O2                     (decomposition reaction)
- KMnO4 + HCl = KCl + MnCl2 + H2O + Cl2

Or more complex like these ones:
- K4Fe(CN)6 + KMnO4 + H2SO4 = KHSO4 + Fe2(SO4)3 + MnSO4 + HNO3 + CO2 + H2O
- K4Fe(CN)6 + H2SO4 + H2O = K2SO4 + FeSO4 + (NH4)2SO4 + CO

Equations like these are NOT supported:
- S^-2 + I2 = I^- + S    (single replacement)
- Cl^- = Cl2 + e^-       (oxydation reaction)
- K^+ + e^- = K          (reduction reaction)

Unfortunately these syntaxes are not supported yet:
- CuSO4 * 5H2O = CuSO4 + H2O
- Or simbols like MeOH (methanol), EtOH (ethanol) or Ph-OH / PhOH (phenol)

================================================================

Setup.
--------

1) Put the "chemistry" folder inside the "includes" (or "inc") folder of your project (note: the result must be that in your "includes" folder you'll have your files + the "chemistry" folder/library)

2) Then, include the boostrap.php file inside the "chemistry" folder inside your index.php page (or elsewhere) or inside your ENDPOINT if you want to build an API.

So, you'll include the bootstrap.php file this way:

```php
// Require the bootstrap file.
require_once 'includes/chemistry/bootstrap.php';
```

3) Once you've done this, you can instantiate one of the classes to work with chemistry with programming. For example you can use the "ChemicalEquationBalancer" class to start balancing chemical equations. Or you can use the "ChemicalEquationPhrase" to build a phrase giving the balanced equation.

# EXAMPLE 1 - Instantiate the class to balance an equation.

```php
// Require the bootstrap file.
require_once 'includes/chemistry/bootstrap.php';

// Balance a chemical equation.
$balanceChemEquation = new ChemicalEquationBalancer( 'S + O2 = SO3' );

// Get the balanced equation this way.
$balancedEquation = $balanceChemEquation->balance_equation_html();

// Show the balanced equation.
echo $balancedEquation;
```

===========================================================

# EXAMPLE 2 - Balance an equation and build a phrase on it.

```php
// Require the bootstrap file.
require_once 'includes/chemistry/bootstrap.php';

// Balance a chemical equation.
$balanceChemEquation = new ChemicalEquationBalancer( 'S + O2 = SO3' );
$chemEquationPhrase  = new ChemicalEquationPhrase();

// Get the balanced equation this way.
$balancedEquation = $balanceChemEquation->balance_equation_html();


echo $balancedEquation;   // Show the balanced equation.
echo '<br>';              // Add a space
echo $chemEquationPhrase->describe_balanced_equation( $balancedEquation );   // Describe balanced equation.
```

### FEATURES END HERE - MORE OF THEM WILL COME IN THE FUTURE - STAY UPDATED! ###

### Last README.md file update: 9 February 2024