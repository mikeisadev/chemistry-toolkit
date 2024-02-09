<?php
use MathPHP\LinearAlgebra\Matrix;
use MathPHP\LinearAlgebra\MatrixFactory;

/**
 * This is the main class that gets the equation and parses it.
 * 
 * Do a diagram of this dispatcher on a papersheet.
 */
class ChemicalEquationBalancer extends ChemicalEquation {
    
    /**
     * Current equation.
     */
    private string $equation = '';
    
    /**
     * Parsing result (the parsed equation).
     * 
     * chemicals         
     * signs           
     * full_equation_array
     * full_equation_string
     * splitted_equation
     */
    private array $parsing_results = [];
    
    /**
     * Constructor.
     */
    public function __construct( string $equation = '' ) {

        if ( $this->set_equation( $equation ) ) {
            $this->parse_equation();
        }

    }

    /**
     * Setters.
     */
    private function set_equation( string $equation ): bool {
        if ( empty( $equation ) ) return false;
        if ( !is_string( $equation ) ) return false;

        $this->equation = (string) $equation;
        
        if ( is_string( $this->equation ) && !empty( $this->equation ) ) return true;
    }

    /**
     * Getters.
     * 
     * Get inserted chemical equation.
     */
    public function get_equation(): bool|string {
        return !empty( $this->equation ) && !is_null( $this->equation ) ? $this->equation : false;
    }

    /**
     * Get parsing results.
     */
    public function get_parsing_results(): bool|array {
        return $this->parsing_results ? $this->parsing_results : false;
    }

    /**
     * Check if equation exists.
     */
    private function equation_exists(): bool {
        if ( !isset( $this->equation ) )        return false;

        if ( !is_string( $this->equation ) )    return false;

        return true;
    }

    /**
     * Parsing activity.
     * 
     * This function then will set the parsing results of the parsing activity
     * inside this object property called "parsing_results"
     */
    private function parse_equation() {
        if ( !$this->equation_exists() ) return "No equation set!";

        // Init equation array;
        $full_equation_array    = (array) [];
        $full_equation_string   = (string) '';

        // Get chemicals (reagents and products + num of atoms)
        preg_match_all(
            $this->equation_regex,
            $this->get_equation(),
            $chemicals
        );

        // Get signs (plus, equals)
        $signs = preg_split( 
            $this->equation_regex,
            $this->get_equation(), 
            -1,
            0
        );

        // Build full equation array.
        $full_equation_array    = $this->build_full_equation_array( $chemicals[0], $signs );
        
        // Create full equation string.
        $full_equation_string   = $this->implode_full_equation_string( $full_equation_array );

        // Balance equation.
        $splitted_equation      = $this->split_equation( $full_equation_array );

        // Build parsed equation array
        $parsed_equation = (array) [
            'chemicals'             => $chemicals[0],
            'signs'                 => $signs,
            'full_equation_array'   => $full_equation_array,
            'full_equation_string'  => $full_equation_string,
            'splitted_equation'     => $splitted_equation
        ];

        // Set into this object the parsed equation;
        $this->parsing_results = $parsed_equation;
    }

    /**
     * This function parses the molecule instead of the equation.
     * 
     * After the parsing an ARRAY is RETURNED.
     * 
     * The returned array will have ATOM SYMBOLS as KEYS and ATOM COUNT as VALUE.
     * 
     * If Cr(NO3)3 is passed, this is returned:
     * 
     * Array (
     *  [Cr] => 1,
     *  [N] => 3,
     *  [O] => 9
     * )
     */
    public function parse_molecule( string $molecule ): array {

        // Init.
        $atoms = (array) [];

        /**
         * Parse the molecule.
         * 
         * Get each atom.
         */
        preg_match_all( 
            $this->equation_regex,
            $molecule,
            $parsed_molecule
        );

        /**
         * Count atoms building an array in which:
         * - Atoms are the keys
         * - Atom count are the values
         */
        foreach ($parsed_molecule[0] as $atom) {

            // Check if is PARENTHESIS GROUP.
            if ( $this->is_parenthesis_group( $atom ) ) {

                $parsed_group = $this->parse_parenthesis_group( $atom );

                foreach ( $parsed_group as $atom => $count ) {

                    if ( array_key_exists( $atom, $atoms ) ) {
                        $atoms[$atom] += $count;
                    } else {
                        $atoms[$atom] = $count;
                    }

                }

                continue;

            }

            // Check if ATOM contains a NUMBER or NOT.
            if ( preg_match('/[0-9]+/', $atom, $matches) ) {

                $atom = str_replace( $matches[0], '', $atom);

                if ( array_key_exists( $atom, $atoms ) ) {
                    $atoms[$atom] += $matches[0];
                } else {
                    $atoms[$atom] = $matches[0];
                }

            } else {
                
                if ( array_key_exists( $atom, $atoms ) ) {
                    $atoms[$atom] += 1;
                } else {
                    $atoms[$atom] = 1;
                }

            }
            
        }

        return $atoms;

    }

    /**
     * Build coefficient equation.
     * 
     * Transform this:
     * H2 + O2 = H2O
     * 
     * Into this:
     * {{ a }}H2 + {{ b }}O2 = {{ b }}H2O
     * 
     * We need this type of string because then we'll substitute
     * the coefficients in form of letters [(a), (b), (c)] with the 
     * coefficients obtained from the calculation from the MATRIX in the
     * Python program.
     * 
     * This function is REALLY similar to the function:
     * $this->implode_full_equation_string().
     * 
     * This very last function wants to FULL EQUATION ARRAY to build a 
     * FULL EQUATION STRING imploding this last mentioned ARRAY.
     * 
     * In this function [ build_coefficient_equation_string ], we'll do a very
     * similar thing but we'll add a COEFFICIENT PLACEHOLDER {{ letter }} before 
     * each MOLECULE. 
     * 
     * letter, inside {{ letter }}, can be one letter of the alphabet (a, b, c, d, 
     * e, f, g,...) to proceed then, when we'll solve the equations from the Matrix
     * to a substitution, with the got coefficients, to balance the chemical equation.
     */
    private function build_coefficient_equation_string(): string|array {

        // Init.
        $coefficient_equation_string = (string) '';

        // Get parsing results.
        $parsing_results = $this->parsing_results;

        // Get the full equation array.
        $full_equation_array = $parsing_results['full_equation_array'];

        // Counter (_index);
        $_index = 0;

        // Add coefficient boolean.
        $add_coefficient = TRUE;

        // Build the coefficient equation string
        foreach ( $full_equation_array as $chars ) {

            if ( in_array( $chars, $this->equation_signs ) ) {
                $coefficient_equation_string .= ' ' . $chars . ' ';

                $add_coefficient = TRUE;
            } else {

                if ( $add_coefficient ) {
                    $coefficient_equation_string .= '{{ ' . $this->alphabt[$_index] . ' }}' . $chars;

                    // We don't need coefficient. We added. Add when switched back because we passed an equation sign.
                    $add_coefficient = FALSE;

                    // Take count of letters as KEYS for the alphabet.
                    $_index++;
                } else {
                    $coefficient_equation_string .= $chars;
                }

            }

        }

        return $coefficient_equation_string;

    }

    /**
     * Create full equation array.
     */
    private function build_full_equation_array(array $chemicals, array $signs): array {

        // Init.
        $full_equation_array = (array) [];

        /**
         * If, during the chemicals loop, we
         * have a key that corresponts to a key of signs
         * with a non empty value, that sign
         * is next to the chemical.
         * 
         * Sign can be + or =.
         */
        foreach ($chemicals as $key => $chemical) {

            // First check if there is a sign to push in the equation.
            if ( !empty( $signs[$key] ) ) {
                $sign = trim( $signs[$key] );
                
                array_push($full_equation_array, $sign);
            }

            // Push chemical.
            array_push( $full_equation_array, $chemical );

        }

        // Return FEA (Full Equation Array).
        return $full_equation_array;

    }

    /**
     * Implode full equation array into
     * full equation string.
     * 
     * This equation IS NOT balanced.
     */
    private function implode_full_equation_string( array $full_equation_array ): string {
        $full_equation_string = (string) '';

        // Start imploding chars.
        foreach ($full_equation_array as $chars) {

            if ( in_array( trim($chars), $this->equation_signs ) ) {
                $full_equation_string .= ' ' . $chars . ' ';
            } else {
                $full_equation_string .= $chars;
            }

        } 

        return $full_equation_string;
    }

    /**
     * Split chemistry equation into REAGENTS and PRODUCTS.
     * 
     * Return an associative array that will containt:
     * - REAGENTS
     * - PRODUCTS
     * 
     * The associative array will have the following structure:
     * Array [
     *      [reagents] => [
     *          [C] => 1,
     *          [H] => 4,
     *          [plus_sign] => "+",
     *          [O] => 2
     *      ],
     *      [products] => [
     *          [C] => 1,
     *          [O] => 3,
     *          [plus_sign] => "+",
     *          [H] => 2
     *      ]
     * ]
     * 
     * This is an example with CHO (Carbon, Hydrogen, Oxygen) atoms. The reaction was
     * the following: CH4 + O2 = CO2 + H2O.
     * 
     * The row form for a reaction like this A + B = C + D will be:
     * Array [
     *      [reagents] => [
     *          [A] => Xa,
     *          [plus_sign] => "+",
     *          [B] => Xb
     *      ]
     *      [products] => [
     *          [C] => Xc,
     *          [plus_sign] => "+",
     *          [D] => Xd
     *      ]
     * ]    
     */
    public function split_equation( array $full_equation_array ): array {

        // Init array
        $splitted_equation = [
            'reagents' => [],
            'products' => []
        ];

        // Equation selector.
        $equation_selector = 'reagents';

        // Start dispatching chars.
        foreach ($full_equation_array as $chars) {
            
            // Letters and numbers.
            if ( !in_array( $chars, $this->equation_signs ) ) {

                // Check if atom is PARENTHESIS GROUP
                if ( $this->is_parenthesis_group( $chars ) ) {

                    $group = $this->parse_parenthesis_group( $chars );

                    // Loop through the ARRAY GROUP
                    foreach ($group as $atom => $count) {
                        
                        /**
                         * Check if $atom already exists in the equation.
                         * 
                         * If the atom exists sum the new number to the already inserted one.
                         * Otherwise insert the number directly.
                         */
                        if ( array_key_exists( $atom, $splitted_equation[$equation_selector] ) ) {
                            $splitted_equation[$equation_selector][$atom] += $count;
                        } else {
                            $splitted_equation[$equation_selector][$atom] = $count;
                        }

                    }

                    continue; // Continue the LOOP. Force it!

                }

                // Check if atom has number.
                if ( preg_match('/[0-9]+/', $chars, $matches) ) {

                    $atom = str_replace( $matches[0], '', $chars );

                    if ( array_key_exists( $atom, $splitted_equation[$equation_selector] ) ) {
                        $splitted_equation[$equation_selector][$atom] += $matches[0];
                    } else {
                        $splitted_equation[$equation_selector][$atom] = $matches[0];
                    }

                } else {

                    if ( array_key_exists( $chars, $splitted_equation[$equation_selector] ) ) {
                        $splitted_equation[$equation_selector][$chars] += 1;
                    } else {
                        $splitted_equation[$equation_selector][$chars] = 1;
                    }

                }

            }

            // Signs.
            if ( in_array( $chars, $this->equation_signs ) ) {

                switch( $chars ) {
                    case '+':
                        $splitted_equation[$equation_selector]['plus_sign'] = '+';
                        break;
                    case '=':
                        $equation_selector = 'products';
                        break;
                }

            }

        }

        // Return balance equation.
        return $splitted_equation;
    }

    /**
     * Check if a piece of string in a chemical equation has parenthesis.
     * 
     * So, check if the passed string is a "parenthesis group".
     * 
     * Parenthesis groups are taken from molecules like:
     * Ca(NO3)2, Al2(SO4)3 or Cr2(SO4)3
     * 
     * So we want to check the chars structure of "parenthesis groups" of:
     * (NO3)2, (SO4)3 and, the same, (SO4)3.
     * 
     * We return true if we have parenthesis, false if not.
     */
    public function is_parenthesis_group( string $parenthesis_group ): bool {

        /**
         * If we have "(" and then ")"
         */
        if ( str_contains( $parenthesis_group, '(' ) && str_contains( $parenthesis_group, ')' ) ) {
            return true;
        }

        /**
         * A note for later evaluation of the algorithm.
         * 
         * Is needed to check if after the closing bracket we have a number?
         * 
         * For example:
         * (NH4)Al(SO4)2 has (NH4) that does not need multiplication, but we can include
         * this case in the parenthesis group anyway checking in that part if we have numbers.
         * 
         * If we don't have numbers we multiply by 1.
         */

        // Otherwise...
        return false;

    }

    /**
     * Count parenthesis molecule groups.
     * 
     * For example if a chemical equation has molecules like:
     * Ca(NO3)2, Al2(SO4)3 or Cr2(SO4)3.
     * 
     * We need to parse the parethesis groups:
     * (NO3)2 and (SO4)3 in the example.
     * 
     * We want to remove parethensis ["(", ")"]. 
     * 
     * Then we SPLIT the group into an array of atoms, where the keys are atoms.
     * 
     * We give to each key a number as value: the count of atoms.
     * 
     * Then we multiply this number by the number after the closing bracket.
     * So, we do a multiplication.
     */
    public function parse_parenthesis_group( string $parenthesis_group ): bool|array|string {

        // Check if is parenthesis group...
        if ( !$this->is_parenthesis_group( $parenthesis_group ) ) {
            return false;
        }

        // Init.
        $group = (string) $parenthesis_group;

        // Init array.
        $atoms = (array) [
            'multiplier'    => 0,
            'atoms'         => []
        ];

        /**
         * First get the "multiplier", the number after closing parenthesis. 
         * But this removing the closing bracket.
         * 
         * Explode the string into an array on the closing bracket.
         * 
         * But check the last element of the array.
         * 
         * If the last element of the array does not contain ANY number but only letters set multiplier as one.
         * Otherwise, if the last element has only numbers, it is the multiplier and set it as multiplier in the array.
         * 
         * Then unset the last element of the array.
         * 
         * At this point implode the array into a string and proceed.
         */
        $group = explode( ')', trim($group) );

        if ( preg_match('/[a-zA-Z]+/', end( $group )) ) {
            $atoms['multiplier'] = 1;
        } else if ( is_int( intval( end($group) ) ) ) {
            $atoms['multiplier'] = end($group);

            // Unset the multiplier from the array (so we remove it from the string later when we'll implode it)
            unset( $group[ count($group) - 1 ] );
        }

        // Implode the array into a string.
        $group = implode('', $group);

        // Remove "(" (opening bracket).
        $group = str_replace('(', '', $group);

        /**
         * Get all atoms of the group.
         * 
         * Now the string is without:
         * - The multiplier at the end of the closing brackets
         * - Opening and closing brackets.
         * 
         * We get atoms as key and number of them as values.
         * 
         * We use PREG_MATCH_ALL to get each atom of the group with numbers
         * */
        preg_match_all(
            $this->equation_regex,
            $group,
            $matches
        );

        /**
         * Loop through each match from PREG_MATCH_ALL.
         * 
         * Then set each ATOM got from the FOREACH loop inside the $atoms ARRAY.
         */
        foreach ( $matches[0] as $atom ) {

            // Check if key exists. Otherwise we'll have a counting problem of the atoms.

            if ( preg_match('/[0-9]+/', $atom, $match) ) {
                $atom = str_replace($match[0], '', $atom);

                // If atom already exists sum. Otherwise not.
                if ( array_key_exists( $atom, $atoms['atoms'] ) ) {
                    $atoms['atoms'][$atom] += $match[0];
                } else {
                    $atoms['atoms'][$atom] = $match[0];
                }
            } else {
                // If atom already exists sum. Otherwise not.
                if ( array_key_exists( $atom, $atoms['atoms'] ) ) {
                    $atoms['atoms'][$atom] += 1;
                } else {
                    $atoms['atoms'][$atom] = 1;
                }
            }

        }

        /**
         * Apply the multiplier.
         * 
         * If the multiplier is equal to 1 do not proceed.
         * 
         * Otherwise yes.
         */
        if ( intval( $atoms['multiplier'] ) > 1 ) {

            foreach ( $atoms['atoms'] as $atom => $count ) {
                $atoms['atoms'][$atom] = $count * $atoms['multiplier'];
            }

        }

        return $atoms['atoms'];
    }

    /**
     * Util functions.
     * 
     * Compare the sum of 'reagents' and 'products' sum.
     * 
     * If the are equal, the reaction is balanced. Otherwise not.
     * 
     * $splitted_equation_array = the splitted equation is that equation that has
     * reagents and products as array key and it contains the default number of
     * atoms.
     */
    public function is_equation_balanced( array $splitted_equation_array, bool $return_array = false ): array|bool {

        // Prepare data.
        $reagents = $splitted_equation_array['reagents'];
        $products = $splitted_equation_array['products'];

        // Sum $reagents and $products from their arrays.
        $reagents_atom_sum = array_sum( $reagents );
        $products_atom_sum = array_sum( $products );

        // Prepare status.
        $is_balanced;
        $balance_label;

        /**
         * Check if:
         * 
         * - reagents > products
         * - reagents < products
         * - reagents = products
         */
        if ( $reagents_atom_sum === $products_atom_sum ) {
            $is_balanced    = true;
            $balance_label  = 'all_balanced';
        } else if ( $reagents_atom_sum > $products_atom_sum ) {
            $is_balanced    = false;
            $balance_label  = 'more_reagents'; 
        } else if ( $reagents_atom_sum < $products_atom_sum ) {
            $is_balanced    = false;
            $balance_label  = 'more_products';
        }
 
        // If user wants to return an array just do it.
        if ( $return_array ) {
            return [
                'reagents_num'          => $reagents_atom_sum,
                'products_num'          => $products_atom_sum,
                'is_equation_balanced'  => $is_balanced ? 'true' : 'false',
                'balance_label'         => $balance_label
            ];
        }

        // Return the bool:
        return $is_balanced;
    }

    /**
     * Get the atoms involved in the chemical equation globally.
     * 
     * For example, in the equation: CH4 + O2 = CO2 + H20
     * 
     * We have three atoms globally: C, H, O
     * 
     * NOTE (IMPORTANT TIPS!): From the splitted equation ($this->split_equation())
     * you will have all the atoms grouped on both side (reagents, products). 
     */
    public function get_atoms_equation_involved(): bool|array {

        if ( empty($this->parsing_results) ) {
            echo "Parsing results is empty! Please enter a chemical equation.";

            return false;
        }

        // Init.
        $all_atoms = (array) [];

        // Get parsing results
        $parsing_results = $this->parsing_results;

        // Get splitted equation.
        $splitted_equation = $parsing_results['splitted_equation'];

        // Get REAGENTS and PRODUCTS
        $reagents = $splitted_equation['reagents'];
        $products = $splitted_equation['products'];

        /**
         * Remove unwanted signs from the ARRAYs by KEY
         * to avoid miscalculations during the parsing process.
         * 
         * Keys to remove:
         * - 'plus_sign'
         */
        unset( $reagents['plus_sign'] );

        unset( $products['plus_sign'] );

        /**
         * Check differences by KEYS
         */
        $differences = array_diff_key( $reagents, $products );

        /**
         * If we don't have differences return 
         * the array with the atoms involved in the equation.
         */
        if ( empty($differences) ) {
            
            foreach ( $reagents as $atom => $count ) {
                array_push( $all_atoms, $atom );
            }

            return $all_atoms;

        }
    }

    /**
     * Get the atoms/molecules involved in the chemical equation.
     * 
     * For example, in the equation: CH4 + O2 = CO2 + H20 
     * 
     * We have 4 molecules: CH4, O2, CO2, H2O
     * 
     * $reagent_product_keys
     */
    public function get_molecules_equation_involved( bool $reagent_product_keys = false ): bool|array {

        if ( empty($this->parsing_results) ) {
            echo "Parsing results is empty! Please enter a chemical equation.";

            return false;
        }

        // Init.
        $all_molecules = (array) [];

        // Get parsing results.
        $parsing_results = $this->parsing_results;

        // Get full equation array
        $full_equation_string = $parsing_results['full_equation_string'];

        // Do first splitting operation -> on equal sign (=)
        $rp_split = explode( '=', $full_equation_string );

        // Split on plus sign.
        $reagents = explode( '+', $rp_split[0] );
        $products = explode( '+', $rp_split[1] );
        
        // Continue the splitting. Get single molecules.
        $count = 0;

        // Insert reagents.
        foreach ( $reagents as $molecule ) {
            
            $reagent_product_keys ? $all_molecules['reagent_' . $count] = trim($molecule) : array_push( $all_molecules, trim($molecule) );

            $count++;

        }

        $count = 0; // Reset counter.

        // Insert products.
        foreach ( $products as $molecule ) {
    
            $reagent_product_keys ? $all_molecules['product_' . $count] = trim($molecule) : array_push( $all_molecules, trim($molecule) );

            $count++;
            
        }

        // Return
        return $all_molecules;

    }

    /**
     * Matrix rows are equal to the total number of atoms found in the equation.
     * 
     * In the equation: CH4 + O2 = CO2 + H2O
     * 
     * We have: 3 rows (C, H, O)
     */
    private function get_matrix_rows(): int {
        return count( $this->get_atoms_equation_involved() );
    }

    /**
     * Matrix columns are equal to the total number of molecules of that interacts in the equation.
     * 
     * In the equation: CH4 + O2 = CO2 + H2O
     * 
     * We have: 4 columns (CH4, O2, CO2, H2O)
     */
    private function get_matrix_cols(): int {
        return count( $this->get_molecules_equation_involved() );
    }

    /**
     * Get matrix resolution.
     * 
     * A matrix is made of ROWS and COLUMNS (cols).
     * 
     * In our case, to solve a chemical equation:
     * - ROWS (vertical elements, Y axis) are the count of single ATOMS
     * - COLUMNS (horizontal elements, X axis) are the count of the MOLECULES
     * 
     * The following schema (the same said before):
     * [***] Matrix dimensions:
     * - Rows       = number of atoms from the equation
     * - Columns    = number of molecules from the equation
     * 
     * Return an array with number of ROWS and COLUMNS (cols):
     * return $matrix_resolution = [ (int) n_rows, (int) n_cols ];
     */
    private function get_matrix_resolution(): array {
        $matrix_resolution = (array) [];

        $matrix_rows = $this->get_matrix_rows();
        $matrix_cols = $this->get_matrix_cols();

        $matrix_resolution['rows'] = $matrix_rows;
        $matrix_resolution['cols'] = $matrix_cols;

        return $matrix_resolution;
    }
 
    /**
     * Parse the equation to return an ARRAY MATRIX of the chemical equation.
     * 
     * The ARRAY MATRIX returned will have as many rows as many atoms we globally have in the equation.
     * The columns of the ARRAY MATRIX will have as many columns as many molecules are involved in the equation.
     * 
     * C3H8 + O2 = CO2 + H2O: has 3 atoms and 4 molecules globally.
     * 
     * The matrix will have 3 rows and 4 columns.
     * 
     * This function will return an array which we'll have as KEYS the ATOMS involved in the equation.
     * These keys will have an array wich will count, for each molecule, the count of the atoms in that molecule.
     * 
     * If the following equation is passed in the constructor of this class:
     * C3H8 + O2 = CO2 + H2O
     * 
     * You will have the resulting matrix:
     * [
     *      [C] => [ 3, 0, -1, 0 ],
     *      [H] => [ 8, 0, 0, -2 ],
     *      [O] => [ 0, 2, -2, -1 ]
     * ]
     */
    public function get_equation_matrix(): array {

        // Init equation matrix.
        $equation_matrix = (array) [];

        // Get matrix resolution.
        $matrix_resolution = $this->get_matrix_resolution();

        // Get ROWS and COLS.
        $m_rows = $this->get_atoms_equation_involved();
        $m_cols = $this->get_molecules_equation_involved( true );

        /**
         * Build ROWS first by ATOM symbol.
         * 
         * List of atom symbols from $m_rows.
         * 
         * Atom = $row.
         */
        foreach ($m_rows as $row) {
            $equation_matrix[$row] = [];
        }

        /**
         * Fill the columns.
         */
        foreach ( $m_cols as $chemical_type => $molecule ) {

            // Set chemical type
            $chem_type = (string) '';

            if ( str_contains( $chemical_type, 'reagent' ) ) $chem_type = 'reagent';
            if ( str_contains( $chemical_type, 'product' ) ) $chem_type = 'product';

            // Get atoms from MOLECULES.
            $molecule_atoms = $this->parse_molecule( $molecule );

            // Get missing atoms.
            $missing_atoms = array_diff_key( $equation_matrix, $molecule_atoms );

            // Insert the number of EXISTING atoms.
            foreach ( $molecule_atoms as $atom => $count ) {

                if ( array_key_exists( $atom, $equation_matrix ) ) {
                    $m_num;

                    switch ( $chem_type ) {
                        case 'reagent': $m_num = (int) +$count; break;
                        case 'product': $m_num = (int) -$count; break;
                    }

                    array_push( $equation_matrix[$atom], $m_num );
                }

            }

            // Insert missing ATOMS as ZERO into the MATRIX.
            foreach ( $missing_atoms as $atom => $void ) {

                array_push( $equation_matrix[$atom], 0 );

            }
            
        }

        /**
         * Build Columns.
         */
        return $equation_matrix;

    }

    /**
     * To understand better the CREATION OF THE EQUATION MATRIX, go to read the documentation comment
     * of the function "get_equation_matrix()" of this CLASS.
     * 
     * (this is the equation: C3H8 + O2 = CO2 + H2O)
     * 
     * This function TRANSFORMS this:
     * [
     *      [C] => [ 3, 0, -1, 0 ],
     *      [H] => [ 8, 0, 0, -2 ],
     *      [O] => [ 0, 2, -2, -1 ]
     * ]
     * 
     * Into this:
     * [
     *      [0] => [ 3, 0, -1, 0 ],
     *      [1] => [ 8, 0, 0, -2 ],
     *      [2] => [ 0, 2, -2, -1 ]
     * ]
     * 
     * So we'll convert the associative array, which keys were needed to understand which molecule has which atoms,
     * into a simple array by pushing each element into a new VARIABLE (ARRAY)
     */

    public function get_equation_matrix_keys_free( array $equation_matrix ): array {

        /**
         * wkeys: without keys.
         * 
         * The new matrix won't be associative.
         */
        $equation_matrix_wkeys = (array) [];

        // Rebuild the matrix as SIMPLE ARRAY and NOT ASSOCIATIVE.
        foreach ( $equation_matrix as $row ) {
            array_push( $equation_matrix_wkeys, $row );
        }

        // Return the rebuilt matrix.
        return $equation_matrix_wkeys;

    }

    /**
     * Solve the matrix with RREF method
     * 
     * Pass the equation matrix without alphabetical keys
     */
    public function solve_matrix_rref( array $equation_matrix_keys_free ) {

        // Final matrix with fractions instead of decimals.
        $m_frac = (array) [];

        // This matrix will have decimals instead of fractions.
        $m_dec = MatrixFactory::create( $equation_matrix_keys_free );

        // Solve with RREF method and convert the object into an array
        $rref = (array) $m_dec->rref();

        // Get solved matrix
        foreach ($rref as $el) {
            if (is_array($el)) $m_frac = $el;
        }

        // Convert decimals into fractions.
        foreach ( $m_frac as $r_k => $row ) {
            foreach ( $row as $n_k => $n ) {
                if ( is_float($n) ) {
                    $m_frac[$r_k][$n_k] = MathUtils::float2fraction($n);
                }
            }
        }

        return $m_frac;

    }

    /**
     * Build the system of equation from the solved matrix.
     */
    public function build_equation_system( array $rref_matrix ) {

        // Init.
        $eq_sys     = (array) [];
        $eq         = (string) '';
        $last_row_k = null;

        // Counter.
        $c          = (int) 0;

        // Build the system.
        foreach ( $rref_matrix as $row ) {

            if ( is_null($last_row_k) ) $last_row_k = count($row) - 1; 
            
            foreach ( $row as $n ) {
                if ( $n == 0 ) continue;
                if ( end($row) == $n ) continue;

                array_push( $eq_sys, $n . $this->alphabt[$c] . ' = ' );

                $eq_sys[$c] .= str_replace( '-', '', $row[$last_row_k] ) . $this->alphabt[$last_row_k];

                $c++;
            }

        }

        return $eq_sys;

    }

    /**
     * Calc the equation coefficients
     * 
     * From the solved matrix with the RREF method calc the relative
     * coefficients
     */
    public function calc_equation_coefficients( $equation_sys ) {

        // Init.
        $coefficients = (array) [];
        $denominators = (array) [];
        $lcm_n        = (int) 0;
        $lcm_c        = (string) '';

        // Get denominators.
        foreach ($equation_sys as $eq) {
            $eq_splt = explode( '=', $eq );

            // Get the right part of the equation.
            $eq_r = trim($eq_splt[1]);
            $eq_r = preg_replace( '/[a-zA-Z]+/', '', $eq_r );

            if ( !str_contains( $eq_r, '/' ) ) {
                array_push( $denominators, 1 );
            } else {
                $frac_split = explode( '/', $eq_r );
                array_push( $denominators, $frac_split[1] );
            }
        }

        // Calc minimum common denominator.
        $lcm_n = MathUtils::lcm( $denominators );

        // Solve the equations.
        foreach ($equation_sys as $eq) {
            // Init.
            $c = (int) 0;

            // Split the equation.
            $eq_splt = explode( '=', $eq );

            // Get left equation data.
            $eq_left = trim($eq_splt[0]);
            $left_n  = preg_replace( '/[a-zA-Z]+/', '', $eq_left );
            $left_c  = str_replace( '/', '', preg_replace( '/[0-9]+/', '', $eq_left ) );

            // Get right equation data.
            $eq_right = trim($eq_splt[1]);

            if ( str_contains( $eq_right, '/' ) ) {
                $right_n    = preg_replace( '/[a-zA-Z]+/', '', $eq_right );
                $right_c    = str_replace( '/', '', preg_replace( '/[0-9]+/', '', $eq_right ) );

                $frac_arr   = explode( '/', $right_n );

                $frac_n     = $frac_arr[0];
                $frac_d     = $frac_arr[1];

                $c          = $lcm_n / $frac_d;
                $c          = $c * $frac_n;

                $coefficients[$left_c] = $c;
            } else { 
                $right_n    = preg_replace( '/[a-zA-Z]+/', '', $eq_right );
                $right_c    = preg_replace( '/[0-9]+/', '', $eq_right );

                $c = $right_n * $lcm_n;

                $coefficients[$left_c] = $c;
            }

            // Get lcm letter (cofficient)
            if ( empty( $lcm_c ) ) $lcm_c = $right_c;

            // Reset.
            $c = 0;
        }

        // Insert last coefficient (lcm)
        $coefficients[$lcm_c] = $lcm_n;

        // Return coefficients.
        return $coefficients;

    }

    /**
     * Substitute the missing coefficients.
     */
    public function add_coefficients_equation_html( string $coefficient_equation, array $coefficient_array ): string {

        // Init.
        $balanced_equation = (string) '';

        // Re-Build string and add <sub></sub>.
        foreach (str_split( $coefficient_equation ) as $el) {
            if ( !is_numeric($el) ) {
                $balanced_equation .= $el;
            } else {
                $balanced_equation .= '<sub>' . $el . '</sub>';
            }
        }

        // Add coefficients to balance the equation.
        foreach ( $coefficient_array as $c => $n ) {

            if (intval($n) == 1) {
                $balanced_equation = str_replace( "{{ $c }}", "", $balanced_equation );
            } else {
                $balanced_equation = str_replace( "{{ $c }}", "<em>$n</em>", $balanced_equation );
            }

        }

        return $balanced_equation;

    }

    /**
     * Final function. 
     * 
     * Use to get balanced equation.
     */
    public function balance_equation_html(): string {

        $matrix         = $this->get_equation_matrix();

        // Get a MATRIX without chemical elements as keys.
        $matrix_wkeys   = $this->get_equation_matrix_keys_free( $matrix );

        $rref           = $this->solve_matrix_rref( $matrix_wkeys );

        $eq_sys         = $this->build_equation_system( $rref );

        $c_equation     = $this->build_coefficient_equation_string();
        $coefficients   = $this->calc_equation_coefficients( $eq_sys );

        $eq_balanced    = $this->add_coefficients_equation_html( $c_equation, $coefficients );

        return $eq_balanced;

    }

}