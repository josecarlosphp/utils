<?php
/**
 * This file is part of josecarlosphp/utils
 *
 * josecarlosphp/utils is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * @see         https://github.com/josecarlosphp/utils
 * @copyright   2022 José Carlos Cruz Parra
 * @license     https://www.gnu.org/licenses/gpl.txt GPL version 3
 * @desc        Common use functions for working with numbers, encapsulated in a class.
 */

namespace josecarlosphp\utils;

abstract class Numbers
{
    static public function medidaStr2Num($str, $unidad=1) //medidaStr2Num
    {
        $num = '';
        $med = '';
        for ($c=0,$len=mb_strlen($str); $c<$len; $c++) {
            $char = mb_substr($str, $c, 1);
            if (mb_stripos('1234567890.', $char) !== false) {
                $num .= $char;
            } elseif (mb_stripos('abcdefghijklmnopqrstuvwxyz', $char) !== false) {
                $med .= $char;
            }
        }

        switch (mb_strtolower($med)) {
            case 'mm':
            case 'gr':
            case 'g':
                $num = ($num * $unidad) / 1000;
                break;
            case 'cm':
            case 'cg':
                $num = ($num * $unidad) / 100;
                break;
            case 'dm':
            case 'dg':
                $num = ($num * $unidad) / 10;
                break;
            case 'm':
            case 'kg':
                $num = $num * $unidad;
                break;
            case 'hm':
                $num = $num * $unidad * 100;
                break;
            case 'km':
            case 't':
                $num = $num * $unidad * 1000;
                break;
        }

        return $num;
    }
    /**
     * Formats a number as currency
     *
     * @param double $amount
     * @param bool $showcurrsymbol
     * @param bool $uselocalcurrencysymbol
     * @return string
     */
    static public function currency($amount, $showcurrsymbol=true, $uselocalcurrencysymbol=true) //currency
    {
        $locale_info = localeconv();
        $number = number_format($amount, 2, $locale_info['mon_decimal_point'], $locale_info['mon_thousands_sep']);

        if ($amount < 0) {
            //TODO: negative values,...
        }

        if ($showcurrsymbol) {
            $curr_symbol = $uselocalcurrencysymbol ? $locale_info['currency_symbol'] : $locale_info['int_curr_symbol'];

            if ($locale_info['p_cs_precedes']) {
                return $locale_info['p_sep_by_space'] ? $curr_symbol . ' ' . $number : $curr_symbol . $number;
            } else {
                return $locale_info['p_sep_by_space'] ? $number . ' ' . $curr_symbol : $number . $curr_symbol;
            }
        }

        return $number;
    }
    /**
     * Aplica un porcentaje a una cantidad dada
     *
     * @param double $importe
     * @param float $porcentaje
     * @param int $base
     * @return double
     */
    static public function aplicarPorcentaje($cantidad, $porcentaje, $base=100) //aplicarPorcentaje
    {
        return $cantidad + (($cantidad / $base) * $porcentaje);
    }
    /**
     * Prepara un número para guardarlo en la base de datos.
     * No valida el número ni comprueba si es válido.
     *
     * @param mixed $n
     * @return mixed
     */
    static public function prepararNumero($n) //prepararNumero
    {
        if (strpos($n, ',') !== false) {
            $n = str_replace(array('.', ','), array('', '.'), $n);
        }

        return $n;
    }
}