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
 * @desc        Common use functions for working with arrays, encapsulated in a class.
 */

namespace josecarlosphp\utils;

abstract class Arrays
{
    /**
    * @return array
    * @param $array array
    * @desc Delete from an array those elements that don't have assigned a value.
    */
    static public function defrag($array) //array_Defrag
    {
        if (!isset($array) || sizeof($array) == 0) {
            return $array;
        }

        $arraydefrag = array();
        foreach ($array as $element) {
            if (isset($element)) {
                $arraydefrag[] = $element;
            }
        }

        return $arraydefrag;
    }
    /**
     * @return string
     * @param $array array
     * @param $separator string
     * @param $recursive bool
     * @desc Gets a string representation of an array elements.
     */
    static public function toString($array, $separator=',', $recursive=false) //array_ToString
    {
       $string = '';
       foreach ($array as $item) {
           if ($recursive && is_array($item)) {
               $string .= self::toString($item, $separator, true);
           } else {
               $string .= $item.$separator;
           }
       }

       return mb_substr($string, 0, mb_strlen($string) - mb_strlen($separator));
    }
    /**
     * @return string
     * @param $array array
     * @param $numCols int
     * @param $tableFormat string
     * @param $rowFormat string
     * @param $cellFormat string
     * @param $header string
     * @desc Gets a HTML code string representing the given array level 0 as a table
     */
    static public function toHtmlTableSimple($array, $numCols=0, $tableFormat='', $rowFormat='', $cellFormat='', $header='') //array_ToHTMLTableSimple
    {
        if ($numCols == 0) {
            $numCols = sizeof($array);
        }

        if ($header) {
            $header = "<thead><tr><th colspan=\"$numCols\">$header</th></tr></thead>";
        }

        $html = "<table $tableFormat>$header<tbody><tr $rowFormat>";
        $currentCol = 0;
        foreach ($array as $element)	{
            if (++$currentCol > $numCols) {
                $html .= "</td></tr><tr $rowFormat>";
                $currentCol = 1;
            }
            $html .= "<td $cellFormat>$element</td>";
        }
        $html .= "</tr></tbody></table>";

        return $html;
    }

    public static function toHtmlTable2xN($array, $tableFormat='', $rowFormat='', $cellFormat='', $header='') //array_ToHTMLTable2xN
    {
        $html = "<table {$tableFormat}>";
        if ($header) {
            $html .= "<thead><tr><th colspan=\"2\">{$header}</th></tr></thead>";
        }
        $html .= '<tbody>';
        foreach ($array as $key=>$val) {
            $html .= "<tr {$rowFormat}><td {$cellFormat}>{$key}</td><td>".(is_array($val) ? self::toHtmlTable2xN($val) : $val)."</td></tr>";
        }
        $html .= '</tbody></table>';

        return $html;
    }
    /**
     * @return string
     * @param $array array
     * @param $hasHeader bool
     * @param $tableFormat string
     * @param $rowFormat string
     * @param $cellFormat string
     * @param $distributeColumnsUniformly bool
     * @desc Gets a HTML code string representing the given array as a table.
     */
    static public function toHtmlTable($array, $hasHeader=false, $tableFormat='', $rowFormat='', $cellFormat='', $distributeColumnsUniformly=false) //array_ToHTMLTable
    {
        $html = "<table $tableFormat>";
        $isHeader = $hasHeader;
        $html .= $isHeader ? '<thead>' : '<tbody>';
        if (is_array($array)) {
            foreach ($array as $subArray) {
                $html .= self::toHtmlRow($subArray, $isHeader, $rowFormat, $cellFormat, $distributeColumnsUniformly);
                if($isHeader)
                {
                    $html .= '</thead><tbody>';
                    $isHeader = false;
                }
            }
        } else {
            $html .= self::toHtmlRow($array, $isHeader, $rowFormat, $cellFormat);
            $html .= $isHeader ? '</thead>' : '</tbody>';
        }
        $html .= '</table>';

        return $html;
    }
    /**
     * @return string
     * @param $array array
     * @param $isHeader bool
     * @param $rowFormat string
     * @param $cellFormat string
     * @param $distributeColumnsUniformly bool
     * @desc Gets a HTML code string representing the given array as a table row.
     */
    static public function toHtmlRow($array, $isHeader=false, $rowFormat='', $cellFormat='', $distributeColumnsUniformly=false) //array_ToHTMLRow
    {
        $td_width = '';
        if ($distributeColumnsUniformly) {
            $td_width = (100 / sizeof($array[0]));
        }
        $tag = 'td';
        if ($isHeader) {
            $tag = 'th';
        }
        $html = "<tr $rowFormat>";
        if (is_array($array)) {
            foreach ($array as $element) {
                $html .= "<$tag $cellFormat";
                if ($distributeColumnsUniformly && mb_strpos($cellFormat, 'width') === false) {
                    $html .= ' width="' . $td_width . '%" ';
                }
                $html .= ">$element</td>";
            }
        } else {
            $html .= "<$tag $cellFormat>$array</td>";
        }
        $html .= '</tr>';

        return $html;
    }
    /**
     * @return int
     * @param $array array
     * @desc Gets maximum sizeof value from an array and sub arrays
     */
    static public function getMaxSizeof($array) //array_getMaxSizeof
    {
        if (is_array($array)) {
            $len = 0;
            $subLen = 0;
            $maxLen = 0;
            foreach ($array as $element) {
                $len++;
                if (is_array($element)) {
                    $subLen = self::getMaxSizeof($element);
                }
                if ($len > $maxLen) {
                    $maxLen = $len;
                }
                if ($subLen > $maxLen) {
                    $maxLen = $subLen;
                }
            }

            return $maxLen;
        }

        return 1;
    }
    /**
     * @return int
     * @param $array array
     * @desc Gets maximum number of levels from an array
     */
    static public function getMaxLevels($array) //array_getMaxLevels
    {
        $levels = 0;
        if (is_array($array)) {
            $levels = 1;
            foreach ($array as $element) {
                $levelsSub = 1;
                if (is_array($element)) {
                    $levelsSub += self::getMaxLevels($element);
                }
                if ($levelsSub > $levels) {
                    $levels = $levelsSub;
                }
            }
        }

        return $levels;
    }
    /**
     * @return int
     * @param $array array
     * @param $recursive bool
     * @param $includeArrays bool
     * @desc Counts elements from an array. If $recursive, counts elements from sub arrays too. If $includeArrays is false, it doesn't count sub arrays as elements.
     */
    static public function count($array, $recursive=false, $includeArrays=true) //array_count
    {
        $count = 0;
        foreach ($array as $element) {
            if (!is_array($element)) {
                $count++;
            } else {
                if ($recursive) {
                    $count += self::count($element, true, true);
                }
                if ($includeArrays) {
                    $count++;
                }
            }
        }

        return $count;
    }
    /**
     * @return array
     * @param $string string
     * @param $separator string
     * @param $allowduplicates
     * @desc Gets an array from a string.
     */
    static public function stringToArray($string, $separator=';', $allowduplicates=false) //array_stringToArray
    {
        $array = array();
        $tok = strtok($string,$separator);
        while ($tok) {
            if ($allowduplicates) {
                $array[] = $tok;
            } elseif (!in_array($tok,$array)) {
                $array[] = $tok;
            }
            $tok = strtok($separator);
        }

        return $array;
    }
    /**
     * @return string
     * @param $array array
     */
    static public function export($array, $striptags=false, $pre=false) //array_export
    {
        if (!is_array($array) || ($size = sizeof($array)) == 0) {
            return '';
        }

        $keys = array_keys($array);
        $return = '<table>';
        for ($c=0; $c<$size; $c++) {
            $key = $keys[$c];
            $value = $striptags ? strip_tags($array[$key]) : $array[$key];
            if ($pre) {
                $value = '<pre>' . $value . '</pre>';
            }
            $return .= '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
        }

        return $return .= '</table>';
    }
    /**
     * Aplica una función (de usuario o propia de php) sobre todos los elementos de un array
     * OJO por ahora devuelve siempre true, no utilizar este valor de retorno
     *
     * @param array $array
     * @param string $funcname
     * @return bool
     */
    static public function walk(&$array, $funcname) //array_walk2
    {
        $keys = array_keys($array);
        foreach ($keys as $key) {
            $array[$key] = $funcname($array[$key]);
        }

        return true;
    }
    /**
     * Clone an array
     *
     * @param array $array
     * @return array
     */
    static public function clon($array) //array_clone
    {
        $clon = array();
        foreach ($array as $key=>$value) {
            $clon[$key] = $value;
        }

        return $clon;
    }

    /**
     * Ordena aleatoriamente los elementos de un array asociativo.
     *
     * @param array $array
     * @return array
     */
    static public function shuffle_assoc($array) //array_shuffle_assoc
    {
       $keys = array_keys($array);
       shuffle($keys);
       $nuevo = array();
       foreach ($keys as $key) {
           $nuevo[$key] = $array[$key];
       }

       return $nuevo;
    }
    /**
     * Indica si un array es numérico o no
     *
     * @param array $arr
     * @return bool
     */
    static public function is_num($arr) //array_is_num
    {
        if (is_array($arr)) {
            foreach (array_keys($arr) as $k) {
                if (!is_int($k)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }
    /**
     * Comprueba si dos arrays son iguales (índices y elementos)
     *
     * @param array $arr1
     * @param array $arr2
     * @param bool $trim
     * @return bool
     */
    static public function compare($arr1, $arr2, $trim=false, &$i=null) //array_compare
    {
        if (sizeof($arr1) == sizeof($arr2)) {
            foreach ($arr1 as $key=>$value) {
                if (!isset($arr2[$key]) || ($trim ? trim($arr2[$key]) != trim($value) : $arr2[$key] != $value)) {
                    $i = $key;

                    return false;
                }
            }

            return true;
        }

        return false;
    }
    /**
     * Comprueba si el array 1 cumple la definición de tipos de variable del array 2
     *
     * @param array $arr1
     * @param array $arr2
     * @return bool
     */
    static public function compare_type($arr1, $arr2) //array_compare_type
    {
       if (sizeof($arr1) == sizeof($arr2)) {
           foreach ($arr1 as $key=>$value) {
               if (!isset($arr2[$key])) {
                   return false;
               }

               switch ($arr2[$key]) {
                   case 'int':
                       if (!is_int($value) && (!is_numeric($value) || (0+$value) != intval($value))) {
                           return false;
                       }
                       break;
                   case 'float':
                       if (!is_float($value) && (!is_numeric($value) || (0+$value) != floatval($value))) {
                           return false;
                       }
                       break;
                   case 'double':
                       if (!is_double($value) && (!is_numeric($value) || (0+$value) != doubleval($value))) {
                           return false;
                       }
                       break;
                   case 'bool':
                       if (!is_bool($value) && !in_array($value, array('1', '0', 1, 0)) && !in_array(mb_strtolower($value), array('true', 'false'))) {
                           return false;
                       }
                       break;
                   case 'string':
                       if (!is_string($value) && ('' . $value) != strval($value)) {
                           return false;
                       }
                       break;
                   default:
                       return false;
               }
           }

           return true;
       }

       return false;
    }
    /**
     * Obtiene una cadena a partir de un array asociativo,
     * puede ser: js, json, csv, xml, serialize
     *
     * @param array $rows
     * @param string $format
     * @return string
     */
    static public function print_rows($rows, $format=null) //array_print_rows
    {
        switch ($format) {
            case 'js':
                $str = '[';
                $sep = '';
                foreach ($rows as $row) {
                    $str .= $sep . (is_array($row) ? self::print_rows($row, 'js') : sprintf("'%s'", addcslashes($row, "\\'")));
                    $sep = ',';
                }
                $str .= ']';
                break;
            case 'json':
                //php4
                /*
                //hay que haber cargado la clase Services_JSON.class.php
                $json = new Services_JSON();
                $str = $json->encode($rows);
                */

                //php5
                $str = json_encode($rows);
                break;
            case 'csv':
                $str = '';
                foreach ($rows as $row) {
                    $sep = '';
                    foreach ($row as $value) {
                        $str .= sprintf('%s"%s"', $sep, $value);
                        $sep = ';';
                    }

                    $str .= "\n";
                }
                break;
            case 'xml':
                $str = self::var_ToXml($rows, 'row', 'rows', 'row', 'rows');
                break;
            case 'serialize':
            default:
                $str = serialize($rows);
                break;
        }

        return $str;
    }
    /**
     * Convierte un array en una cadena query string
     *
     * @param array $arr
     * @param bool $urlencode
     * @return string
     */
    static public function toQueryString($arr, $urlencode=true) //array_ToQueryString
    {
        $str = '';
        $sep = '';
        foreach ($arr as $key=>$value) {
            if (is_array($value)) {
                foreach ($value as $key2=>$value2) {
                    if (is_array($value2)) {
                        foreach ($value2 as $key3=>$value3) {
                            if (is_array($value3)) {
                                foreach ($value3 as $key4=>$value4) {
                                    $str .= $urlencode ? sprintf('%s%s[%s][%s][%s]=%s', $sep, urlencode($key), urlencode($key2), urlencode($key3), urlencode($key4), urlencode($value4)) : sprintf('%s%s[%s][%s][%s]=%s', $sep, $key, $key2, $key3, $key4, is_bool($value4) ? ($value4 ? '1' : '0') : $value4);
                                    $sep = '&';
                                }
                            } else {
                                $str .= $urlencode ? sprintf('%s%s[%s][%s]=%s', $sep, urlencode($key), urlencode($key2), urlencode($key3), urlencode($value3)) : sprintf('%s%s[%s][%s]=%s', $sep, $key, $key2, $key3, is_bool($value3) ? ($value3 ? '1' : '0') : $value3);
                                $sep = '&';
                            }
                        }
                    } else {
                        $str .= $urlencode ? sprintf('%s%s[%s]=%s', $sep, urlencode($key), urlencode($key2), urlencode($value2)) : sprintf('%s%s[%s]=%s', $sep, $key, $key2, is_bool($value2) ? ($value2 ? '1' : '0') : $value2);
                        $sep = '&';
                    }
                }
            } else {
                $str .= $urlencode ? sprintf('%s%s=%s', $sep, urlencode($key), urlencode($value)) : sprintf('%s%s=%s', $sep, $key, is_bool($value) ? ($value ? '1' : '0') : $value);
            }

            $sep = '&';
        }

        return $str;
    }
    /**
     * Genera XML a partir de una variable
     *
     * @param mixed $var
     * @param string $nombreItem
     * @param string $nombreConjunto
     * @param string $nombreItemGeneral
     * @param string $nombreConjuntoGeneral
     * @param string $encoding
     * @param int $nivel
     * @return string
     */
    static public function var_ToXml($var, $nombreItem='item', $nombreConjunto='array', $nombreItemGeneral='item', $nombreConjuntoGeneral='array', $encoding='UTF-8', $nivel=0) //var_ToXML
    {
        $str = $nivel == 0 ? "<?xml version=\"1.0\" encoding=\"{$encoding}\" ?>\n" : "";

        $tab = '';
        for ($c=0; $c<$nivel; $c++) {
            $tab .= "\t";
        }

        if (is_array($var)) {
            $esAsociativo = !self::is_num($var);

            $str .= sprintf("%s<%s>\n", $tab, $nombreConjunto);
            foreach ($var as $key=>$value) {
                $str .= self::var_ToXml($value, $esAsociativo ? $key : $nombreItemGeneral, $esAsociativo ? $key : $nombreItemGeneral, $nombreItemGeneral, $nombreConjuntoGeneral, $encoding, $nivel+1);
            }
            $str .= sprintf("%s</%s>\n", $tab, $nombreConjunto);
        } else {
            $str .= sprintf("%s<%s>%s</%s>\n", $tab, $nombreItem, is_numeric($var) || empty($var) ? $var : "<![CDATA[{$var}]]>", $nombreItem);
        }

        return $str;
    }

    static public function key_exists_recursive($key, $array, &$element='', &$path=array()) //array_key_exists_recursive
    {
        if (is_array($array)) {
            if (array_key_exists($key, $array)) {
                $element = $array[$key];

                return true;
            }

            foreach ($array as $i=>$subarray) {
                if (self::key_exists_recursive($key, $subarray, $element, $path)) {
                    array_unshift($path, $i);

                    return true;
                }
            }
        }

        return false;
    }

    function reverse_special($arr, $indices=null)
    {
        if (is_null($indices)) {
            $indices = array_keys($arr);
        }

        $rev = array_reverse($indices);
        $aux = array();
        $n = 0;
        foreach ($rev as $i) {
            if ($arr[$i]) {
                $aux[] = $arr[$i];
            } else {
                $n++;
            }
        }

        while ($n) {
            $aux[] = '';
            $n--;
        }

        foreach ($indices as $key=>$i) {
            $arr[$i] = $aux[$key];
        }

        return $arr;
    }
    /**
     * @desc Does the same than parse_str without max_input_vars limitation:
     * Parses $string as if it were the query string passed via a URL and sets variables in the current scope.
     * @param $string array string to parse (not altered like in the original parse_str(), use the second parameter!)
     * @param $result array  If the second parameter is present, variables are stored in this variable as array elements
     * @return bool true or false if $string is an empty string
     *
     * @author rubo77 at https://gist.github.com/rubo77/6821632
     **/
    static public function parse_str($string, &$result) //my_parse_str
    {
        if ($string === '') {
            return false;
        }

        $result = array();
        // find the pairs "name=value"
        $pairs = explode('&', $string);
        foreach ($pairs as $pair) {
            // use the original parse_str() on each element
            $params = array();
            parse_str($pair, $params);
            $k = key($params);
            if (!isset($result[$k])) {
                $result+=$params;
            } else {
                $result[$k] = self::merge_recursive_distinct($result[$k], $params[$k]);
            }
        }
        return true;
    }
    /**
     * @desc Better recursive array merge function listed on the array_merge_recursive PHP page in the comments.
     * @param $array1 array
     * @param $array2 array
     * @return array
     */
    static public function merge_recursive_distinct(array &$array1, array &$array2) //array_merge_recursive_distinct
    {
        $merged = $array1;
        foreach ($array2 as $key=>&$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged [$key] = self::merge_recursive_distinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }
}
