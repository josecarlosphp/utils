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
 * @desc        Common use functions for validations, encapsulated in a class.
 */

namespace josecarlosphp\utils;

abstract class Validations
{
    /**
     * @return bool
     * @param $var string
     * @param $allowblank bool
     * @desc Validate an email. Returns true if the validation was successful, false otherwise
     */
    static public function validateEmail($var, $allowblank=false) //validateEmail
    {
        //All characters must be 7 bit ascii

        $length = strlen($var);
        if ($length == 0) {
            return $allowblank;
        }

        $idx = 0;
        while ($length--) {
            $c = $var[$idx++];
            if (ord($c) > 127) {
                return false;
            }
        }

        $regexp = '/^(?:[^\s\000-\037\177\(\)<>@,;:\\"\[\]]\.?)+@(?:[^\s\000-\037\177\(\)<>@,;:\\\"\[\]]\.?)+\.[a-z]{2,6}$/Ui';

        return preg_match($regexp,$var);
    }
    /**
     * @return bool
     * @param $var string
     * @desc Validate an url. Returns true if the validation was successful, false otherwise
     */
    static public function validateUrl($var, $allowblank=false) //validateUrl
    {
        //All characters must be 7 bit ascii

        $length = strlen($var);
        if ($length == 0) {
            return $allowblank;
        }

        $idx = 0;
        while ($length--) {
            $c = $var[$idx++];
            if (ord($c) > 127) {
                return false;
            }
        }

        $regexp = '/^([!\$\046-\073=\077-\132_\141-\172~]|(?:%[a-f0-9]{2}))+$/i';
        if (!preg_match($regexp, $var)) {
            return false;
        }

        $url_array = @parse_url($var);

        return empty($url_array) ? false : !empty($url_array['scheme']);
    }
    /**
     * Valida un número de teléfono
     *
     * @param string $var
     * @param bool $allowblank
     * @param string $extraallowedchars
     * @param mixed $inis
     * @return bool
     */
    static public function validatePhone($var, $allowblank=false, $extraallowedchars='', $inis=null) //validatePhone
    {
        $var = str_replace(' ', '', $var);
        $len = mb_strlen($var);
        if ($len < 9 || $len > 20) {
            return $len == 0 ? $allowblank : false;
        }

        if (mb_strrpos($var, '+') > 0) {
            return false;
        }

        $allowedchars = "+0123456789()-$extraallowedchars";
        for ($c=0; $c<$len; $c++) {
            if (mb_strpos($allowedchars, mb_substr($var, $c, 1)) === false) {
                return false;
            }
        }

        if (is_array($inis)) {
            foreach ($inis as $ini) {
                if (mb_substr($var, 0, mb_strlen($ini)) == $ini) {
                    return true;
                }
            }

            return false;
        } elseif (is_string($inis)) {
            return mb_substr($var, 0, mb_strlen($inis)) == $inis;
        }

        return true;
    }
    /**
     * Valida un número de móvil
     *
     * @param string $var
     * @param bool $allowblank
     * @param string $extraallowedchars
     * @return bool
     */
    static public function validateMobile($var, $allowblank=false, $extraallowedchars='') //validateMobile
    {
        return self::validatePhone($var, $allowblank, $extraallowedchars, '6');
    }
    /**
     * Valida un NIF o tarjeta de residencia
     *
     * @param string $dni
     * @param bool $allowblank
     * @return bool
     */
    static public function validateNIFNIE($dni, $allowblank=false) //validateNIFNIE
    {
        return self::validateNIF_NIE($dni, $allowblank);
    }
    /**
     * Valida un NIF o tarjeta de residencia
     *
     * @param string $dni
     * @param bool $allowblank
     * @return bool
     */
    static public function validateNIF_NIE($dni, $allowblank=false) //validateNIF_NIE
    {
        return self::validateNIF($dni, $allowblank) || self::validateNIE($dni, $allowblank);
    }
    /**
     * Valida un NIF
     *
     * @param string $dni
     * @param bool $allowblank
     * @return bool
     */
    static public function validateNIF($dni, $allowblank=false) //validateNIF
    {
        if (strlen($dni) == 9) {
            $arr_letras = array('T','R','W','A','G','M','Y','F','P','D','X','B','N','J','Z','S','Q','V','H','L','C','K','E');
            $dni = strtoupper($dni);

            $num = substr($dni, 0, 8);
            $letra = substr($dni, 8, 1);

            if (is_numeric($num) && in_array($letra, $arr_letras)) {
                $numcalc = $num % 23;

                return $letra == $arr_letras[$numcalc];
            }
        }

        return $allowblank && $dni == '';
    }
    /**
     * Valida un NIE
     *
     * @param string $dni
     * @param bool $allowblank
     * @return bool
     */
    static public function validateNIE($dni, $allowblank=false) //validateNIE
    {
        if (strlen($dni) == 9) {
            $arr_letras = array('T','R','W','A','G','M','Y','F','P','D','X','B','N','J','Z','S','Q','V','H','L','C','K','E');
            $dni = strtoupper($dni);
            switch ($dni[0]) {
                case 'X':
                    $num = substr($dni, 1, 7);
                    break;
                case 'Y':
                    $num = "1".substr($dni, 1, 7);
                    break;
                case 'Z':
                    $num = "2".substr($dni, 1, 7);
                    break;
                default:
                    return false;
            }

            $letra = substr($dni, 8, 1);
            if (is_numeric($num) && in_array($letra, $arr_letras)) {
                $numcalc = $num % 23;

                return $letra == $arr_letras[$numcalc];
            }
        }

        return $allowblank && $dni == '';
    }
    /**
     * Valida un CIF
     *
     * @param string $cif
     * @param bool $allowblank
     * @return bool
     */
    static public function validateCIF($cif, $allowblank=false) //validateCIF
    {
        if (strlen($cif) >= 9) {
            $arrayA_H = array('1','2','3','4','5','6','7','8','9','0');
            $arrayP_S = array('A','B','C','D','E','F','G','H','I','J');
            $cif = strtoupper($cif);
            $cif_solo_numero = substr($cif, 1);
            $letra_cif = substr($cif, 0, 1);
            $ultimo_car = substr($cif, 8, 1);
            $multiplicados = substr($cif_solo_numero, 0, 1).substr($cif_solo_numero, 2, 1).substr($cif_solo_numero, 4, 1).substr($cif_solo_numero, 6, 1);
            $ya_multiplicado = $multiplicados * 2;
            $ya_multiplicado = str_pad($ya_multiplicado, 5, " ", STR_PAD_LEFT);
            $sumandotodos = 0;
            for ($i=0; $i<=4; $i++) {
                $sumandotodos += (int)substr($ya_multiplicado, $i, 1);
            }
            $sumandotodos = $sumandotodos + (int)substr($cif_solo_numero, 1, 1) + (int)substr($cif_solo_numero, 3, 1) + (int)substr($cif_solo_numero, 5, 1);
            $paso3 = (floor($sumandotodos / 10) + 1) * 10;
            $paso4 = $paso3 - $sumandotodos;

            return ($arrayA_H[$paso4 - 1] == $ultimo_car) || ($arrayP_S[$paso4 - 1] == $ultimo_car);
        }

        return $allowblank && $cif == '';
    }
    /**
     * Valida un CIF, NIF o NIE
     *
     * @param string $doc
     * @param bool $allowblank
     * @return bool
     */
    static public function validateCIFNIFNIE($doc, $allowblank=false) //validateCIFNIFNIE
    {
        return self::validateCIF($doc, $allowblank) || self::validateNIF_NIE($doc, $allowblank);
    }
    /**
     * @return bool
     * @param string $str
     */
    static public function validateStringAsNickOrPass($str) //validateStringAsNickOrPass
    {
        $len = strlen($str);
        if (is_null($str) || $len < 4 || $len > 14) {
            return false;
        }

        for ($c=0; $c<$len; $c++) {
            if (!ereg($str[$c], "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789")) {
                return false;
            }
        }

        return true;
    }
    /**
     * Valida más o menos un código postal
     *
     * @param string $cp
     * @param bool $allowblank
     * @return bool
     */
    static public function validateCodigoPostal($cp, $allowblank=false) //validateCodigoPostal
    {
        $len = strlen($cp);
        if ($len == 5) {
            $codprov = substr($cp, 0, 2);

            return ($codprov > 0 && $codprov < 53);
        }

        return $allowblank && $len == 0;
    }
    /**
     * Valida una cadena según una longitud máxima y mínima y un juego de caracteres válido
     *
     * @param string $str
     * @param int $minlen
     * @param int $maxlen
     * @param string $validchars
     * @return bool
     */
    static public function validateString($str, $minlen=9, $maxlen=9, $validchars='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789') //validateString
    {
        if ($minlen < 0) {
            $minlen = 0;
        }

        if ($maxlen < 0) {
            $maxlen = 0;
        }

        $len = strlen($str);
        if ($len < $minlen || $len > $maxlen) {
            return false;
        }

        for ($c=0; $c<$len; $c++) {
            if (strpos($validchars, $str[$c]) === false) {
                return false;
            }
        }

        return true;
    }
    /**
     * Valida un código de cuenta corriente
     *
     * @param string $str
     * @param bool $allowblank
     * @return bool
     */
    static public function validateCCC($str, $allowblank=false) //validateCCC
    {
        if (self::validateString($str, 20, 20, '0123456789')) {
            $pesos = array(1, 2, 4, 8, 5, 10, 9, 7, 3, 6);
            $DC1 = 0;
            $DC2 = 0;

            for ($i=0; $i<=7; $i++) {
                $DC1 += $str[$i] * $pesos[$i+2];
            }

            $DC1 = 11 - ($DC1 % 11);
            if ($DC1 == 11) {
                $DC1 = 0;
            } elseif ($DC1 == 10) {
                $DC1 = 1;
            }

            for ($i=10; $i<=19; $i++) {
                $DC2 += $str[$i] * $pesos[$i-10];
            }

            $DC2 = 11 - ($DC2 % 11);
            if ($DC2 == 11) {
                $DC2 = 0;
            } elseif($DC2 == 10) {
                $DC2 = 1;
            }

            return $str[8] == $DC1 && $str[9] == $DC2;
        }

        return $allowblank && $str == '';
    }
    /**
     * Valida un código de cuenta IBAN
     *
     * @param string $str
     * @param bool $allowblank
     * @return bool
     */
    static public function validateIBAN($str, $allowblank=false) //validateIBAN
    {
        $iban = mb_strtoupper(str_replace(array(' ', '-'), '', trim($str)));

        if (mb_strlen($iban) == 24 && self::validateString(mb_substr($iban, 2), 22, 22, '0123456789')) {
            $letras = array('A'=>10, 'B'=>11, 'C'=>12, 'D'=>13, 'E'=>14, 'F'=>15, 'G'=>16,'H'=>17, 'I'=>18, 'J'=>19, 'K'=>20, 'L'=>21, 'M'=>22, 'N'=>23, 'O'=>24, 'P'=>25, 'Q'=>26, 'R'=>27, 'S'=>28, 'T'=>29, 'U'=>30, 'V'=>31, 'W'=>32, 'X'=>33, 'Y'=>34, 'Z'=>35);

            $valorLetra1 = $letras[mb_substr($iban, 0, 1)];
            $valorLetra2 = $letras[mb_substr($iban, 1, 1)];

            $siguienteNumeros = mb_substr($iban, 2, 2);

            $valor = mb_substr($iban, 4, mb_strlen($iban)).$valorLetra1.$valorLetra2.$siguienteNumeros;

            return (bcmod($valor, 97) == 1);
        }

        return $allowblank && $str == '';
    }
    /**
     * Valida una fecha
     *
     * @param int $year
     * @param int $month
     * @param int $day
     * @param mixed $minYear
     * @param mixed $maxYear
     * @return bool
     */
    static public function validateDatetime($year, $month, $day, $hour=0, $minute=0, $second=0, $minYear=null, $maxYear=null) //validateDatetime
    {
        if (self::validateDate($year, $month, $day, $minYear, $maxYear)) {
            $hour = intval($hour);
            $minute = intval($minute);
            $second = intval($second);

            return !($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59 || $second < 0 || $second > 59);
        }

        return false;
    }
    /**
     * Valida una fecha
     *
     * @param int $year
     * @param int $month
     * @param int $day
     * @param mixed $minYear
     * @param mixed $maxYear
     * @return bool
     */
    static public function validateDate($year, $month, $day, $minYear=null, $maxYear=null) //validateDate
    {
        $year = is_numeric($year) ? intval($year) : false;
        $month = is_numeric($month) ? intval($month) : false;
        $day = is_numeric($day) ? intval($day) : false;

        if ($year === false || $month === false || $day === false || $day < 1 || (!is_null($minYear) && $year < $minYear) || (!is_null($maxYear) && $year > $maxYear)) {
            return false;
        }

        switch ($month) {
            case 1:
            case 3:
            case 5:
            case 7:
            case 8:
            case 10:
            case 12:
                return $day <= 31;
            case 4:
            case 6:
            case 9:
            case 11:
                return $day <= 30;
            case 2:
                return $day <= (($year%4 == 0 && ($year%100 != 0 || $year%400 == 0)) ? 29 : 28);
        }

        return false;
    }
    /**
     * Valida una fecha pasada como cadena (por defecto formato yyyy-mm-dd)
     *
     * @param str $date
     * @param mixed $minYear
     * @param mixed $maxYear
     * @param str $format
     * @return bool
     */
    static public function validateDateStr($date, $minYear=null, $maxYear=null, $format='dd/mm/yyyy') //validateDateStr
    {
        if (strlen($date) != 10) {
            return false;
        }

        switch (strtolower($format)) {
            default:
            case 'yyyy/mm/dd':
            case 'yyyy-mm-dd':
            case 'aaaa/mm/dd':
            case 'aaaa-mm-dd':
                $year = substr($date, 0, 4);
                $month = substr($date, 5, 2);
                $day = substr($date, 8, 2);
                break;
            case 'dd/mm/yyyy':
            case 'dd-mm-yyyy':
            case 'dd/mm/aaaa':
            case 'dd-mm-aaaa':
                $year = substr($date, 6, 4);
                $month = substr($date, 3, 2);
                $day = substr($date, 0, 2);
                break;
        }

        return self::validateDate($year, $month, $day, $minYear, $maxYear);
    }
    /**
     * Valida un código de cuenta de cotización (Seguridad Social)
     *
     * @param string $str
     * @param bool $allowblank
     * @return bool
     */
    static public function validateCCCot($str, $allowblank=false) //validateCCCot
    {
        //$str = str_replace('-', '', $str);

        $len = strlen($str);
        if ($len == 11 && is_numeric($str)) {
            $numero = intval(substr($str, 0, 10));

            return intval(substr($str, 10)) == intval($numero - ($numero / 97) * 97);
        }

        return $len == 0 && $allowblank;
    }
}