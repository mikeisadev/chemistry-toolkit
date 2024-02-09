<?php

use MathPHP\Algebra;

/**
 * A class with some math functions.
 */
class MathUtils {

    /**
     * Use this function to convert a decimal number into a fraction.
     */
    public static function float2fraction( float $n, float $tolerance = 1.e-6 ): string {
        $negative = (bool) false;

        // Do not convert float numbers that doesn't have the point
        if ( !str_contains( (string) $n, '.' ) ) return $n;

        if ( $n < 0 ) {
            $negative = true;
            $n = (float) abs($n);
        }

        $h1=1; $h2=0;
        $k1=0; $k2=1;
        $b = 1/$n;
        
        do {
            $b = 1/$b;
            $a = floor($b);
            $aux = $h1; $h1 = $a*$h1+$h2; $h2 = $aux;
            $aux = $k1; $k1 = $a*$k1+$k2; $k2 = $aux;
            $b = $b-$a;
        } while (abs($n-$h1/$k1) > $n*$tolerance);

        return (string) $negative ? "-$h1/$k1" : "$h1/$k1";
    }

    /**
     * Find the least common multiple (LCM) from an ARRAY of denominators.
     */
    public static function lcm( array $nums ): int {
        // Initialize result
        $ans = $nums[0];
 
        // ans contains LCM of 
        // arr[0], ..arr[i]
        // after i'th iteration,
        for ($i = 1; $i < sizeof($nums); $i++) {
            $ans = ((($nums[$i] * $ans)) / (Algebra::gcd($nums[$i], $ans)));
        }
 
        return $ans;
    }

}