<?php

class ChemicalEquation extends Chemistry {

    /**
     * RegEx for chemical equations.
     * 
     * Deprecated V1.
     * 
     * Added on:        18 November 2023.
     * Deprecated on:   10 Febrary 2024.
     * 
     * Can recognize:
     * - regular chemical equations
     * 
     * Cannot recognize:
     * - hydration groups like *3H2O, *5H2O, *7H2O
     * - ions S^-2, Cl^- or K^+
     * - electrons e^-
     */
    //protected string $equation_regex = "/[A-Z][a-z]?\d*|\((?:[^()]*(?:\(.*\))?[^()]*)+\)\d+/";

    /**
     * New Equation regex.
     * 
     * Deprecated V2. 
     * 
     * Added on:        10 February 2024.
     * Deprecated on:   11 February 2024.
     * 
     * Can recognize:
     * - regular chemical equations
     * - hydration groups like *3H2O, *5H2O, *7H2O
     * 
     * Cannot recognize:
     * - ions S^-2, Cl^- or K^+
     * - electrons e
     */
    // public static string $equation_regex = "/[A-Z][a-z]?\d*|\((?:[^()]*(?:\(.*\))?[^()]*)+\)\d+|([*]\d+([A-Z][a-z]?\d*.))/";

    /**
     * New equation regex.
     * 
     * Added on: 11 February 2024.
     * 
     * Can recognize:
     * - regular chemical equations
     * - hydration groups *5H2O
     * - ions S^-2, Cl^- or K^+
     * - electrons e
     */
    public static string $equation_regex = "/(?:([A-Z][a-z]?\d*)*([\^][-+]?[0-9]?[+-]?))+|[A-Z][a-z]?\d*|\((?:[^()]*(?:\(.*\))?[^()]*)+\)\d+|([*]\d+([A-Z][a-z]?\d*.))+|([e][\^][-])|([e])/";

    /**
     * ReGex to get the single molecules of an equation.
     * 
     * NOTE: This regex does not split the single atoms like the regex inside $equation_regex!
     * 
     * From KCl + NaNO3 = KNO3 + NaCl you get this:
     * 
     * KCl, NaNO3, KNO3, NaCl
     */
    public static string $block_equation_regex = "/(?:([A-Z][a-z]?\d*)*([\^][-+]?[0-9]?[+-]?))+|(?:[A-Z][a-z]?\d*|\((?:[^()]*(?:\(.*\))?[^()]*)+\)\d+|([*]\d+([A-Z][a-z]?\d*.)))+|([e][\^][-])|([e])/";

    /**
     * ReGex to get all the elements of a chemical equation.
     * 
     * From KCl + NaNO3 = KNO3 + NaCl you get this:
     * 
     * KCl, +, NaNO3, =, KNO3, +, NaCl
     */
    public static string $cmplt_equation_regex = "/(?:([A-Z][a-z]?\d*)*([\^][-+]?[0-9]?[+-]?))+|(?:[A-Z][a-z]?\d*|\((?:[^()]*(?:\(.*\))?[^()]*)+\)\d+|([*]\d+([A-Z][a-z]?\d*.)))+|([e][\^][-])|([e])|[=]|[+]/";

    /**
     * Regex for balanced chemical equation.
     * 
     * Matches this equation: 1Ca(NO3)2 + 2NH4OH = 2NH4NO3 + 1Ca(OH)2
     * 
     * Into:  1, Ca, (NO3)2, +, 2, N, H4, N, O3, =, 2, N, H4, N, O3, 1, Ca, (OH)2
     */
    public static string $balanced_equation_regex = "/[0-9]+|(?:([A-Z][a-z]?\d*)*([\^][-+]?[0-9]?[+-]?))+|[A-Z][a-z]?\d*|\((?:[^()]*(?:\(.*\))?[^()]*)+\)\d+|([*]\d+([A-Z][a-z]?\d*.))+|([e][\^][-])|([e])|[=]|[+]/";

    /**
     * Regex for balanced chemical equation.
     * 
     * Matches this equation: 1Ca(NO3)2 + 2NH4OH = 2NH4NO3 + 1Ca(OH)2
     * 
     * Into: 1, Ca(NO3)2, +, 2, NH4OH, =, 2, NH4NO3, +, 1, Ca(OH)2
     */
    public static string $block_balanced_equation_regex = "/[0-9]+|(?:([A-Z][a-z]?\d*)*([\^][-+]?[0-9]?[+-]?))+|(?:[A-Z][a-z]?\d*|\((?:[^()]*(?:\(.*\))?[^()]*)+\)\d+|([*]\d+([A-Z][a-z]?\d*.)))+|([e][\^][-])|([e])|[=]|[+]/";

    /**
     * Recognize a molecule or atom with or without the number after.
     * 
     * Interpret things like: Cu, Na, H2, Br, Cl2, F, H, H2, F2, N2 etc.
     */
    public static string $mol_regex = "/[A-Z][a-z]?\d*/";

    /**
     * Recognize a parenthesis group.
     * 
     * Interpret things like: (ClO3)2, (NO3)3, (SO4)3, (NH4)2 etc.
     */
    public static string $parenthesis_group_regex = "/[(][A-Z][a-z]?.\d*[)]\d*/";

    /**
     * Regex to parse these coefficients placeholders:
     * 
     * {{ a }}, {{ b }} and {{ c }} ecc.
     * 
     * Inside this full equation coefficient string:
     * 
     * {{ a }}CH4 + {{ b }}O2 = {{ c }}CO2 + {{ d }}H2O
     */
    public static string $coefficients_regex = '/({{ [A-Z]?[a-z]?. }})|/';

    /**
     * RegEx to recognize idratation groups like:
     * 
     * - *3H2O
     * - *5H2O
     * - and similar...
     */
    protected static string $hydration_group_regex = "/[*]\d+[A-Z][a-z]?\d*./";

    /**
     * RegEx to parse an idration group
     * 
     * This regex divides this:
     * 
     * *3H2O
     * 
     * Into this:
     * 
     * *, 3, H2, O
     */
    protected static string $parse_hydration_group = "/[*]|\d+|[A-Z][a-z]?\d*|./";

    /**
     * RegEx to recognize atoms / molecules with electric charges.
     * 
     * Here some examples that this RegEx recognizes:
     * 
     * K^+, Cl^-1, H2PO4^-2, Cr2O7^-3, ClO^-, I^-, I^-1, 
     * Mn^+2, Mn^2+, Cr2O7^3-, OH^-, H^+, MnO4^-, HSO3^1-, 
     * CN^-, F^-, H^-, Br^-, SH^-, ClO4^-, NO3^-, CH3COO^-
     */
    protected static string $ion_regex = "/(?:([A-Z][a-z]?\d*)*([\^][-+]?[0-9]?[+-]?))/";

    /**
     * Regex to parse atoms / molecules with electric charges.
     * 
     * This regex parses this:
     * CH3COO^-
     * 
     * into this:
     * C, H3, C, O, O, ^-
     */
    protected static string $parse_ion_regex = "/([A-Z][a-z]?\d*)|([\^][-+]?[0-9]?[+-]?)/";

    /**
     * Regex to match the exponent of charge.
     * 
     * Match "^[charge][number]" or "^[number][charge]" on these examples:
     * 
     * H2PO4^-2, Cr2O7^-3, ClO^-, I^-, I^-1, 
     */
    protected static string $exp_charge_regex = "/[\^][+-]?([0-9]+)?[+-]?/";

    /**
     * Regex to recognize electrons.
     */
    protected static string $electron_regex = "/([e][\^][-])|([e])/";

    /**
     * Chars that are signs in a chemical equation.
     */
    protected static array $equation_signs = ["+", "="];

    /**
     * Build the regex expression to parse a full coefficients equation string like this one:
     * 
     * {{ a }}CH4 + {{ b }}O2 = {{ c }}CO2 + {{ d }}H2O
     */
    public static function full_coefficients_equation_regex(): string {
        return substr( ChemicalEquation::$coefficients_regex, 0, -1 ) . substr( ChemicalEquation::$equation_regex, 1, -1 ) . '|[+]|[=]/';
    }

}