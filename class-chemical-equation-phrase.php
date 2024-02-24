<?php
/**
 * The PHP class to build a phrase from a balanced chemical equation.
 * 
 * For example, from this equation:
 * 
 * CH4 + 2 O2 = CO2 + 2 H2O
 * 
 * You get this phrase:
 * 
 * 1 mol of CH4 and 2 mols of O2 reacts to produce 1 mol of CO2 and 2 mols of H2O.
 */

class ChemicalEquationPhrase extends ChemicalEquation {

    /**
     * Inside a chemical equation we can parse different chars that can be:
     * 
     * - a coefficient
     * - a molecule
     * - an ion
     * - an electron
     */
    private static array $char_states = [
        'coefficient',
        'molecule',
        'ion',
        'electron'
    ];

    /**
     * Types of chemical reactions.
     */
    private static array $reaction_types = [
        'double replacement',
        'single replacement',
        'synthesis',
        'decomposition',
        'reduction',
        'oxydation',
        'combustion'
    ];

    /**
     * Build the phrase that describes the balanced chemical equation.
     */
    public function describe_balanced_equation( string $balanced_equation ): string|array {

        // Init.
        $p      = (string) '<p>';   // Init. paragraph.
        $n_r    = (int) 0;          // n. of reagents.
        $n_p    = (int) 0;          // n. of products.

        // Get the equation graph.
        $eq_graph = $this->balanced_equation_graph( $balanced_equation );

        // Count products and reagents.
        $n_r = count( $eq_graph['r'] );
        $n_p = count( $eq_graph['p'] );

        return $eq_graph;
        
    }

    /**
     * Build a graph for the balanced equation.
     */
    private function balanced_equation_graph( string $balanced_equation ): array {

        // Init
        $pre_eq_graph   = (array) []; // Pre equation graph to build the non-pre one.
        $eq_graph       = (array) [
            'r' => [],
            'p' => []
        ]; // Equation graph reagents (r) & products (p).
        
        /**
         * Interpret the balanced equation.
         * 
         * $eq_arr = equation_array 
         */
        preg_match_all(
            ChemicalEquation::$block_balanced_equation_regex,
            $balanced_equation,
            $p_eq
        );
        
        /**
         * Build the PRE equation graph
         * 
         * Transform this balanced equation: 1Ca(NO3)2 + 2NH4OH = 2NH4NO3 + 1Ca(OH)2
         * 
         * Into this:
         * 
         * [
         *  [0] => 'r_Ca(NO3)2_coeff:1'
         *  [1] => 'r_NH4OH_coeff:2'
         *  [2] => 'p_NH4NO3_coeff:2'
         *  [3] => 'p_Ca(OH)2_coeff:1'
         * ]
         */
        
        $state = 'r';   // Track the state.
        
        for ($i = 0; $i < sizeof($p_eq[0]); $i++) {

            $char = $p_eq[0][$i];
            $s = '';
        
            // Skip the "+" sign.
            if ($char === '+') continue;
        
            // Change state on "=" and skip.
            if ($char === '=') {
                $state = 'p';
                continue;
            }
        
            // Add the state.
            $s = $state . '_';
        
            if ( is_numeric($char) ) {
                $s .= $p_eq[0][$i + 1] . '_' . 'coeff:' . $char;
                        
                array_push( $pre_eq_graph, $s );
            }
        
        }
        
        /**
         * Transform the pre equation graph ($pre_eq_graph) into
         * the final equation graph.
         * 
         * Transform this:
         * 
         * [
         *  [0] => 'r_Ca(NO3)2_coeff:1'
         *  [1] => 'r_NH4OH_coeff:2'
         *  [2] => 'p_NH4NO3_coeff:2'
         *  [3] => 'p_Ca(OH)2_coeff:1'
         * ]
         * 
         * Into this:
         * [
         *  [r] => [
         *      [Ca(NO3)2] => 1,
         *      [NH4OH] => 2
         *  ],
         *  [p] => [
         *      [Ca(NO3)2] => 2,
         *      [NH4OH] => 1
         *  ]
         * ]
         */
        $state = 'r';   // Reset the state and track it.

        foreach ( $pre_eq_graph as $s ) {

            // Explode the string.
            $s_arr = explode( '_', $s );

            $state = $s_arr[0]; // Change the state.
            $mol   = $s_arr[1]; // Get the mol.

            $c_arr = explode( ':', $s_arr[2] ); // Explode the string with the coefficient.
            $c = $c_arr[1]; // Get the coefficient.

            $eq_graph[$state][$mol] = $c;

        }

        // Return the equation graph.
        return $eq_graph;

    }

    /**
     * Extract coefficient from single molecule/atom.
     * 
     * NOTE: This only works when the atom or molecule has the coefficient
     * trapped between <em></em> HTML tags!
     */
    private function extract_coefficient_html( string $mol ): string {

        if ( preg_match( '/(\<(\/)?em?\>)/', $mol ) ) {
            $mol_exp = explode( '</em>', $mol );

            return str_replace( '<em>', '', $mol_exp[0] );
        }

        return '1';

    }

}