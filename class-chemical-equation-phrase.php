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
     * Build the phrase that describes the balanced chemical equation.
     */
    public function describe_balanced_equation( string $balanced_equation ): string|array {

        // Init.
        $phrase = (string) '';
        $count = (int) 0;

        $phrase .= '<p>';

        // Explode the equation on = and then on +
        $eq_exp = explode('=', $balanced_equation);

        $reagents = explode( '+', $eq_exp[0] );
        $products = explode( '+', $eq_exp[1] );

        // Count reagents and products.
        $n_reagents = count($reagents);
        $n_products = count($products);

        // Loop reagents
        foreach ($reagents as $r) {

            $c = $this->extract_coefficient($r);
            $mol_t = strlen( $c ) > 1 ? 'mols' : 'mol';
            $_r = preg_replace( '/(<em>[0-9]+<\/em>)/', '', $r );
            $del = ' reacts with ';

            switch ( $n_reagents ) {

                case 1:
                    $phrase .= $c . ' ' . $mol_t . ' of ' . $_r . ' dismutates into ';
                    break;

                case 2:
                    if ( $reagents[$count] === end($reagents) ) {
                        $del = ' to produce ';
                    }

                    $phrase .= $c . ' ' . $mol_t . ' of ' . $_r . $del ;
                    break;

                default:
                    if ( $reagents[$count] === end($reagents) ) {
                        $del = ' react together to produce ';
                    } else if ( $reagents[$count] === $reagents[count($reagents) - 2] ) {
                        $del = ' and ';
                    } else {
                        $del = ', ';
                    }

                    $phrase .= $c . ' ' . $mol_t . ' of ' . trim($_r) . $del;

                    break;

            }
            
            $count++;

        }

        // Loop products and reset the count.
        $count = 0;

        foreach ($products as $p) {

            $c = $this->extract_coefficient($p);
            $mol_t = strlen( $c ) > 1 ? 'mols' : 'mol';
            $_p = preg_replace( '/(<em>[0-9]+<\/em>)/', '', $p );
            $del = '';

            switch ( $n_products ) {

                case 1:
                    $phrase .= $c . ' ' . $mol_t . ' of ' . $_p;
                    break;

                case 2:
                    $del = $products[$count] === end($products) ? '' : ' and ';

                    $phrase .= $c . ' ' . $mol_t . ' of ' . $_p . $del;

                    break;

                default:
                    if ( $products[$count] === end($products) ) {
                        $del = '';
                    } else if ( $products[$count] === $products[count($products) - 2] ) {
                        $del = ' and ';
                    } else {
                        $del = ', ';
                    }

                    $phrase .= $c . ' ' . $mol_t . ' of ' . trim($_p) . $del;

                    break;

            }

            $count++;

        }

        $phrase .= '</p>';

        return $phrase;
    }

    /**
     * Extract coefficient from single molecule/atom
     */
    private function extract_coefficient( string $mol ): string {

        if ( preg_match( '/(\<(\/)?em?\>)/', $mol ) ) {
            $mol_exp = explode( '</em>', $mol );

            return str_replace( '<em>', '', $mol_exp[0] );
        }

        return '1';

    }

}