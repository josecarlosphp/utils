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
 * @desc        Common use functions for working with strings, encapsulated in a class.
 */

namespace josecarlosphp\utils;

abstract class Strings
{
    /**
     * Trunca una cadena
     *
     * @param string $str
     * @param int $len
     * @param string $ext
     * @param bool $html
     * @param bool $descartarEspacios
     * @return string
     */
    static public function truncate($str, $len, $ext='...', $html=true, $descartarEspacios=false, $flags=ENT_COMPAT, $encoding='UTF-8') //truncar
    {
        if ($html) {
            return htmlentities(self::truncate(html_entity_decode($str), $len, $ext, false, $descartarEspacios), $flags, $encoding);
        }

        return mb_strlen($descartarEspacios ? str_replace(' ', '', $str) : $str) > $len ?
            trim(mb_substr($str, 0, $len - mb_strlen($ext))) . $ext
            :
            $str;
    }
    /**
     * Quita las tildes y símbolos similares de una cadena.
     * Si $modoMayusculas es menor que cero, pasa la cadena a minúsculas,
     * si es mayor que cero la pasa a mayúsculas,
     * y si es cero (por defecto) no hace nada en este sentido.
     *
     * @param string $str
     * @param int $modoMayusculas
     * @return string
     */
    static public function quitarTildes($str, $modoMayusculas=0) //quitarTildes
    {
        $patrones = array();

        if ($modoMayusculas <= 0) {
            if ($modoMayusculas < 0) {
                $str = mb_strtolower($str);
            }
            $patrones[] = array(array('á','à','ä','â'), 'a');
            $patrones[] = array(array('é','è','ë','ê'), 'e');
            $patrones[] = array(array('í','ì','ï','î'), 'i');
            $patrones[] = array(array('ó','ò','ö','ô'), 'o');
            $patrones[] = array(array('ú','ù','ü','û'), 'u');
        }

        if ($modoMayusculas >= 0) {
            if ($modoMayusculas > 0) {
                $str = mb_strtoupper($str);
            }
            $patrones[] = array(array('Á','À','Ä','Â'), 'A');
            $patrones[] = array(array('É','È','Ë','Ê'), 'E');
            $patrones[] = array(array('Í','Ì','Ï','Î'), 'I');
            $patrones[] = array(array('Ó','Ò','Ö','Ô'), 'O');
            $patrones[] = array(array('Ú','Ù','Ü','Û'), 'U');
        }

        foreach ($patrones as $patron) {
            $str = str_replace($patron[0], $patron[1], $str);
        }

        return $str;
    }
    /**
     * "Parte" una cadena en trozos separándolos con $separator siguiendo los $tramos
     *
     * @param string $str
     * @param array $tramos
     * @param string $separator
     * @return string
     */
    static public function partirCadena($str, $tramos, $separator='-') //partirCadena
    {
        $resultado = '';
        $i = 0;
        for ($c=0,$size=sizeof($tramos); $c<$size; $c++) {
            $tramo = (int)$tramos[$c];
            if ($c > 0) {
                $resultado .= $separator;
            }
            $resultado .= mb_substr($str, $i, $tramo);
            $i += $tramo;
        }

        return $resultado;
    }

    static public function formatoCuentaBancaria($str, $separator='-') //formatoCuentaBancaria(
    {
        return $str ? self::partirCadena($str, array(4, 4, 2, 10), $separator) : '';
    }

    static public function stripChars($str, $chars, $modoQuitar=false) //stripChars(
    {
        $aux = '';
        $len = mb_strlen($str);
        for ($c=0; $c<$len; $c++) {
            $char = mb_substr($str, $c, 1);
            if ((!$modoQuitar && mb_strpos($chars, $char) !== false) || ($modoQuitar && mb_strpos($chars, $char) === false)) {
                $aux .= $char;
            }
        }

        return $aux;
    }
    /**
     * Prepara un número de teléfono con un formato estándar
     *
     * @param string $str
     */
    static public function formatoTelefono($str) //formatoTelefono
    {
        $str = str_replace(array('+', '-', ' ', '/' ), '', $str);
        if (mb_strlen($str) == 11 && mb_substr($str, 0, 2) == '34') {
            $str = mb_substr($str, 2);
        }

        $aux = '';
        for ($c=0,$size=mb_strlen($str); $c<$size; $c++) {
            if ($c > 0 && $c%3 == 0 && $c < $size-1) {
                $aux .= ' ';
            }

            $aux .= mb_substr($str, $c, 1);
        }

        return $aux;
    }

    static public function cogerTrozo($str, $pre, $sig, $inclusive = false) //cogerTrozo
    {
        if (($pos = mb_strpos($str, $pre)) !== false) {
            $pos += mb_strlen($pre);
            $str = mb_substr($str, $pos);

            if (($pos = mb_strpos($str, $sig)) !== false) {
                $str = mb_substr($str, 0, $pos);

                return $inclusive ? $pre . $str . $sig : $str;
            }
        }

        return '';
    }

    static public function quitarTrozo($str, $pre, $sig, $multi=true) //quitarTrozo
    {
        while (($posA = mb_strpos($str, $pre)) !== false) {
            $aux = mb_substr($str, 0, $posA);
            $str = mb_substr($str, $posA + mb_strlen($pre));

            $posB = mb_strpos($str, $sig);
            if ($posB === false) {
                $str = $aux;
                break;
            }

            $str = $aux . mb_substr($str, $posB + mb_strlen($sig));

            if (!$multi) {
                break;
            }
        }

        return $str;
    }

    static public function stripStr($str, $ini, $fin) //stripstr
    {
        while (($pos = mb_strpos($str, $ini)) !== false) {
            $aux = mb_substr($str, $pos + mb_strlen($ini));
            $str = mb_substr($str, 0, $pos);

            if (($pos2 = mb_strpos($aux, $fin)) !== false) {
                $str .= mb_substr($aux, $pos2 + mb_strlen($fin));
            }
        }

        return $str;
    }

    static public function stripiStr($str, $ini, $fin) //stripistr
    {
        while (($pos = mb_stripos($str, $ini)) !== false) {
            $aux = mb_substr($str, $pos + mb_strlen($ini));
            $str = mb_substr($str, 0, $pos);

            if (($pos2 = mb_stripos($aux, $fin)) !== false) {
                $str .= mb_substr($aux, $pos2 + mb_strlen($fin));
            }
        }

        return $str;
    }

    static public function isHash($str, $algo=null) //isHash
    {
        $lengths = array(8, 32, 40, 48, 56, 64, 80, 96, 128);

        switch ($algo) {
            case 'adler32':
            case 'crc32':
            case 'crc32b':
                $length = 8;
                break;
            case 'md2':
            case 'md4':
            case 'md5':
            case 'ripemd128':
            case 'tiger128,3':
            case 'tiger128,4':
            case 'haval128,3':
            case 'haval128,4':
            case 'haval128,5':
                $length = 32;
                break;
            case 'sha1':
            case 'ripemd160':
            case 'tiger160,3':
            case 'tiger160,4':
            case 'haval160,3':
            case 'haval160,4':
            case 'haval160,5':
                $length = 40;
                break;
            case 'tiger192,3':
            case 'tiger192,4':
            case 'haval192,3':
            case 'haval192,4':
            case 'haval192,5':
                $length = 48;
                break;
            case 'haval224,3':
            case 'haval224,4':
            case 'haval224,5':
                $length = 56;
                break;
            case 'sha256':
            case 'ripemd256':
            case 'snefru':
            case 'gost':
            case 'haval256,3':
            case 'haval256,4':
            case 'haval256,5':
                $length = 64;
                break;
            case 'ripemd320':
                $length = 80;
                break;
            case 'sha384':
                $length = 96;
                break;
            case 'sha512':
            case 'whirlpool':
                $length = 128;
                break;
            default:
                $length = null;
        }

        $len = mb_strlen($str);
        if ($len == $length || (is_null($length) && in_array($len, $lengths))) {
            for ($c=0; $c<$len; $c++) {
                if (mb_strpos('0123456789abcdef', mb_substr($str, $c, 1)) === false) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    static public function str_array2keys($str) //str_array2keys
    {
        $str = trim($str);
        $trozos = array();
        $pos = mb_strrpos($str, '=>');
        while ($pos !== false) {
            $str = mb_substr($str, 0, $pos);
            $pos = mb_strrpos($str, ',');
            if ($pos !== false) {
                $trozos[] = mb_substr($str, $pos + 1);
                $str = mb_substr($str, 0, $pos);
            } else {
                $trozos[] = $str;
                break;
            }

            $pos = mb_strrpos($str, '=>');
        }

        return implode("=>'', ", array_reverse($trozos)) . "=>'')";
    }
}