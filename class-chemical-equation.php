<?php

class ChemicalEquation extends Chemistry {

    /**
     * RegEx for chemical equations.
     */
    protected string $equation_regex = "/[A-Z][a-z]?\d*|\((?:[^()]*(?:\(.*\))?[^()]*)+\)\d+/";

    /**
     * Chars that are signs in a chemical equation.
     */
    protected array $equation_signs = ["+", "="];

}