### Chemistry ToolKit

* Programmer:       Michele Mincone
* Main language:    PHP
* Started on:       18 November 2023
* Status:           beta (under development, important features are missing!)

A simple library to work with chemistry and mathematics.

Features.
------------

*   Chemical equations:
    - Chemical equation balanacer [AVAILABLE]
    - Describe a balanced chemical equation with a phrase [NOT-AVAILABLE]
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


Dependencies.
---------------

*   This library uses MathPHP (https://github.com/markrogoyski/math-php) library to solve most of the mathematical problems.

Requirements.
---------------

*   PHP version: 8.2 or greater

Chemical equation samples to work with.
-------------------

NOTE: All the chemical equations are supported.

Here there are some examples you can try:

- S^-2 + I2 = I^- + S                       (single replacement)
- Cl^- = Cl2 + e^-                          (oxydation reaction)
- K^+ + e^- = K                             (reduction reaction)
- Ca(NO3)2 + KCl = KNO3 + CaCl2             (double exchange reaction)
- S + O2 = SO3                              (synthesis reaction)
- Fe + Cl2 = FeCl3                          (synthesis reaction)
- KNO3 = K2O + N2 + O2                      (decomposition reaction)
- KMnO4 + HCl = KCl + MnCl2 + H2O + Cl2
- K4Fe(CN)6 + KMnO4 + H2SO4 = KHSO4 + Fe2(SO4)3 + MnSO4 + HNO3 + CO2 + H2O
- K4Fe(CN)6 + H2SO4 + H2O = K2SO4 + FeSO4 + (NH4)2SO4 + CO
- CuSO4 * 5H2O = CuSO4 + H2O

Syntax rules
-------------------

- Hydration groups must be written after a molecule and must start with asterisk. Then you have to specify the number of water molecules and insert the formula of water.

- Electric charges must be inserted after this symbol: ^ (e.g. Mg^2+ / Cl^- / H^+). Other syntaxes are not supported.

- Electrons must be inserted with this symbol e^-

Unfortunately simbols like MetOH (methanol), EtOH (ethanol) or Ph-OH / PhOH (phenol) are not recognized.

Setup.
--------

1) Put the "chemistry-toolkit" folder inside the "includes" (or "inc") folder of your project (note: the result must be that in your "includes" folder you'll have your files + the "chemistry" folder/library)

2) Then, include the boostrap.php file inside the "chemistry-toolkit" folder inside your index.php page (or elsewhere) or inside your ENDPOINT if you want to build an API.

So, you'll include the bootstrap.php file this way:

```php
// Require the bootstrap file.
require_once 'includes/chemistry/bootstrap.php';
```

3) Once you've done this, you can instantiate a class to work with chemistry with programming. At the moment only the class "ChemicalEquationBalancer" is available.

### EXAMPLE - Instantiate the class to balance an equation.

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

----------------

FEATURES END HERE - MORE OF THEM WILL COME IN THE FUTURE - STAY UPDATED! ###

Last README.md file update: 24 February 2024