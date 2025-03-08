<?php

if (!function_exists('generateSignature')) {
    /**
     * Generates the signature string from the provided parameters for verification.
     *
     * @return string
     */
    function generateSignature(array $parameters)
    {
        $filteredParameters = array_intersect_key(
            $parameters,
            array_flip(explode(',', $parameters['signed_field_names']))
        );

        return implode(',', array_map(fn ($key) => "$key=".$filteredParameters[$key], array_keys($filteredParameters)));
    }
}
