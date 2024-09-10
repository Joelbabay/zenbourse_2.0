<?php

namespace App\Trait;

trait NullValueFormatterTrait
{
    /**
     * Retourne une chaîne vide si la valeur est null.
     *
     * @param mixed $value
     * @return string
     */
    public function formatNullValue($value): string
    {
        return ($value) ? $value : ' '; // Remplace les null par une chaîne vide
    }
}
