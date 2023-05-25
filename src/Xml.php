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
 * @desc        Common use functions for working with XML, encapsulated in a class.
 */

namespace josecarlosphp\utils;

abstract class Xml
{
    /**
     * Tries to load an XML string an return errors if found.
     * Returns array of \libXMLError objects.
     *
     * @param string $xml
     * @return array
     */
    static public function getXmlErrors($xml)
    {
        libxml_use_internal_errors(true);

        $doc = new \DOMDocument('1.0', 'utf-8');
        $doc->loadXML($xml);

        return libxml_get_errors();
    }

    static public function checkXml($xml, $xmlpath='')
    {
        $errors = self::getXmlErrors($xml);

        if (!empty($errors)) {
            $error = $errors[0];
            if ($error->level >= LIBXML_ERR_FATAL) {
                throw new ErrorException($error->message, $error->code, E_ERROR, $xmlpath, $error->line);
            }
        }

        return true;
    }

    /**
     * Converts XML to array.
     *
     * @param string $url
     * @param int $get_attributes
     * @param string $priority
     * @param string $encoding
     * @return array
     */
    static public function xml2array($url, $get_attributes=1, $priority='tag', $encoding='UTF-8') //xml2array
    {
        if (!function_exists('xml_parser_create')) {
            return array();
        }

        if (substr($url, 0, 1) == '<') {
            $contents = $url;
        } else {
            $contents = '';

            if (($fp = fopen($url, 'rb')) === false) {
                return array();
            }

            while (!feof($fp)) {
                $contents .= fread($fp, 8192);
            }

            fclose($fp);
        }

        $parser = xml_parser_create('');
        $xml_values = null;
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, $encoding);
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, trim($contents), $xml_values);
        xml_parser_free($parser);
        if (!$xml_values) {
            return array(); //Hmm...
        }

        $xml_array = array();
        $parents = array();
        $opened_tags = array();
        $arr = array();
        $current = &$xml_array;
        $repeated_tag_index = array();
        foreach ($xml_values as $data) {
            unset($attributes, $value);
            extract($data);
            $result = array();
            $attributes_data = array();

            if (isset($value)) {
                if ($priority == 'tag') {
                    $result = $value;
                } else {
                    $result['value'] = $value;
                }
            }

            if (isset($attributes) && $get_attributes) {
                foreach ($attributes as $attr=>$val) {
                    if ($priority == 'tag') {
                        $attributes_data[$attr] = $val;
                    } else {
                        $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
                    }
                }
            }

            if ($type == 'open') {
                $parent[$level -1] = & $current;
                if (!is_array($current) || (!in_array($tag, array_keys($current)))) {
                    $current[$tag] = $result;
                    if ($attributes_data) {
                        $current[$tag . '_attr'] = $attributes_data;
                    }
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    $current = &$current[$tag];
                } else {
                    if (isset($current[$tag][0])) {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                        $repeated_tag_index[$tag . '_' . $level]++;
                    } else {
                        $current[$tag] = array($current[$tag], $result);
                        $repeated_tag_index[$tag . '_' . $level] = 2;
                        if (isset($current[$tag . '_attr'])) {
                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                            unset($current[$tag . '_attr']);
                        }
                    }
                    $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                    $current = &$current[$tag][$last_item_index];
                }
            } elseif($type == 'complete') {
                if (!isset($current[$tag])) {
                    $current[$tag] = $result;
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    if ($priority == 'tag' && $attributes_data) {
                        $current[$tag . '_attr'] = $attributes_data;
                    }
                } else {
                    if (isset($current[$tag][0]) && is_array($current[$tag])) {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                        if ($priority == 'tag' && $get_attributes && $attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                        }
                        $repeated_tag_index[$tag . '_' . $level]++;
                    } else {
                        $current[$tag] = array($current[$tag], $result);
                        $repeated_tag_index[$tag . '_' . $level] = 1;
                        if ($priority == 'tag' && $get_attributes) {
                            if (isset($current[$tag . '_attr'])) {
                                $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                                unset($current[$tag . '_attr']);
                            }

                            if ($attributes_data) {
                                $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                            }
                        }
                        $repeated_tag_index[$tag . '_' . $level]++; //0 && 1 index is already taken
                    }
                }
            } elseif($type == 'close') {
                $current = & $parent[$level -1];
            }
        }

        return ($xml_array);
    }

    static public function formatXmlFile($xmlpath, $elementTag, $fieldsTags) //formatearxmlfile
    {
        $xmltmp = $xmlpath . '.' . date('YmdHis') . '.tmp';

        if (copy($xmlpath, $xmltmp)) {
            $xmlfp = fopen($xmltmp, 'r');
            $xmlfor = fopen($xmlpath, 'w');

            if ($xmlfp !== false && $xmlfor != false) {
                $arrA = array();
                $arrB = array();

                $arrA[] = "<{$elementTag}>";
                $arrA[] = "</{$elementTag}>";

                $arrB[] = "<{$elementTag}>\n";
                $arrB[] = "</{$elementTag}>\n";

                foreach ($fieldsTags as $fieldTag) {
                    $arrA[] = "</{$fieldTag}>";

                    $arrB[] = "</{$fieldTag}>\n";
                }

                $line = fgets($xmlfp);

                while ($line !== false) {
                    fwrite($xmlfor, str_replace($arrA, $arrB, $line));

                    $line = fgets($xmlfp);
                }

                return true;
            }

            if (is_resource($xmlfp)) {
                fclose($xmlfp);
            }

            if (is_resource($xmlfor)) {
                fclose($xmlfor);
            }

            unlink($xmltmp);
        }

        return false;
    }
    /**
     * Convierte un archivo XML en un CSV.
     *
     * La función xmlfile2csvfileB() emplea xmlfile2csvfile() si no existe la clase SimpleXMLElement.
     * No podemos hacer que la función xmlfile2csvfile() emplee xmlfile2csvfileB() si existe SimpleXMLElement,
     * porque existe la posibilidad de un comportamiento no esperado.
     * No obstante, siempre procuraremos usar xmlfile2csvfileB() en vez de xmlfile2csvfile().
     *
     * @param string $xmlpath
     * @param string $csvpath
     * @param string $elementTag
     * @param array $fieldsTags
     * @param string $delimiter
     * @param string $enclosure
     * @param bool $trim
     */
    static public function xmlfile2csvfile($xmlpath, $csvpath, $elementTag, $fieldsTags, $delimiter=',', $enclosure='"', $trim=true) //xmlfile2csvfile
    {
        $leidas = 0;

        $xmlfp = fopen($xmlpath, 'r');
        $csvfp = fopen($csvpath, 'w');

        if($xmlfp !== false && $csvfp != false)
        {
            fputcsv($csvfp, $fieldsTags, $delimiter, $enclosure);

            $elementClosed = true;
            $fieldClosed = true;
            $fieldIndex = 0;
            $fieldsCount = sizeof($fieldsTags);

            $data = array();
            $line = fgets($xmlfp); $leidas++;
            while($line !== false)
            {
                if($elementClosed)
                {
                    if(($pos = mb_strpos($line, '<'.$elementTag.'>')) !== false)
                    {
                        //self::Msg('******Nuevo elemento******');

                        $elementClosed = false;

                        $line = mb_substr($line, $pos + mb_strlen('<'.$elementTag.'>'));
                    }
                    elseif(($pos = mb_strpos($line, '<'.$elementTag.' ')) !== false)
                    {
                        //self::Msg('******Nuevo elemento******');

                        $elementClosed = false;

                        $line = mb_substr($line, $pos + mb_strlen('<'.$elementTag.' '));
                        $line = mb_substr($line, mb_strpos($line, '>') + 1);
                    }
                    else
                    {
                        $line = '';
                    }
                }
                else
                {
                    if($fieldClosed)
                    {
                        //self::Msg('------Campo cerrado------');
                        //self::Msg('&lt;'.$fieldsTags[$fieldIndex].'&gt;');

                        if(($pos = mb_strpos($line, '<'.$fieldsTags[$fieldIndex].'>')) !== false)
                        {
                            //Etiqueta abierta normalmente
                            //self::Msg('Etiqueta abierta normalmente');

                            $fieldClosed = false;

                            $line = mb_substr($line, $pos + mb_strlen('<'.$fieldsTags[$fieldIndex].'>'));
                        }
                        elseif(($pos = mb_strpos($line, $closeTag = '<'.$fieldsTags[$fieldIndex].'/>')) !== false || ($pos = mb_strpos($line, $closeTag = '<'.$fieldsTags[$fieldIndex].' />')) !== false)
                        {
                            //Etiqueta autocerrada
                            //self::Msg('Etiqueta autocerrada');

                            $data[$fieldIndex] = '';

                            $line = mb_substr($line, $pos + mb_strlen($closeTag));

                            $fieldIndex++;
                            if($fieldIndex >= $fieldsCount)
                            {
                                $fieldIndex = 0;
                                $elementClosed = true;

                                fputcsv($csvfp, $data, $delimiter, $enclosure);

                                $data = array();
                                $line = '';
                            }
                        }
                        elseif(isset($fieldsTags[$fieldIndex+1]) && (($pos = mb_strpos($line, '<'.$fieldsTags[$fieldIndex+1].'>')) !== false || ($pos = mb_strpos($line, '<'.$fieldsTags[$fieldIndex+1].'/>')) !== false || ($pos = mb_strpos($line, '<'.$fieldsTags[$fieldIndex+1].' />')) !== false))
                        {
                            //Sin Etiqueta porque encuentra la siguiente
                            //self::Msg('No se ha encontrado (A1) la etiqueta '.$fieldsTags[$fieldIndex]);

                            $data[$fieldIndex] = '';

                            $fieldIndex += 1;
                            if($fieldIndex >= $fieldsCount)
                            {
                                $fieldIndex = 0;
                                $elementClosed = true;

                                fputcsv($csvfp, $data, $delimiter, $enclosure);

                                $data = array();
                                $line = '';
                            }
                        }
                        elseif(isset($fieldsTags[$fieldIndex+2]) && (($pos = mb_strpos($line, '<'.$fieldsTags[$fieldIndex+2].'>')) !== false || ($pos = mb_strpos($line, '<'.$fieldsTags[$fieldIndex+2].'/>')) !== false || ($pos = mb_strpos($line, '<'.$fieldsTags[$fieldIndex+2].' />')) !== false))
                        {
                            //Sin Etiqueta porque encuentra la siguiente de la siguiente
                            //self::Msg('No se ha encontrado (A2) la etiqueta '.$fieldsTags[$fieldIndex]);

                            $data[$fieldIndex] = '';
                            $data[$fieldIndex + 1] = '';

                            $fieldIndex += 2;
                            if($fieldIndex >= $fieldsCount)
                            {
                                $fieldIndex = 0;
                                $elementClosed = true;

                                fputcsv($csvfp, $data, $delimiter, $enclosure);

                                $data = array();
                                $line = '';
                            }
                        }
                        elseif(isset($fieldsTags[$fieldIndex+3]) && (($pos = mb_strpos($line, '<'.$fieldsTags[$fieldIndex+3].'>')) !== false || ($pos = mb_strpos($line, '<'.$fieldsTags[$fieldIndex+3].'/>')) !== false || ($pos = mb_strpos($line, '<'.$fieldsTags[$fieldIndex+3].' />')) !== false))
                        {
                            //Sin Etiqueta porque encuentra la siguiente de la siguiente de la siguiente
                            //self::Msg('No se ha encontrado (A3) la etiqueta '.$fieldsTags[$fieldIndex]);

                            $data[$fieldIndex] = '';
                            $data[$fieldIndex + 1] = '';
                            $data[$fieldIndex + 2] = '';

                            $fieldIndex += 3;
                            if($fieldIndex >= $fieldsCount)
                            {
                                $fieldIndex = 0;
                                $elementClosed = true;

                                fputcsv($csvfp, $data, $delimiter, $enclosure);

                                $data = array();
                                $line = '';
                            }
                        }
                        elseif(($pos = mb_strpos($line, '</'.$elementTag.'>')) !== false)
                        {
                            //Sin Etiqueta porque encuentra el cierre de este elemento
                            //self::Msg('No se ha encontrado (B) la etiqueta '.$fieldsTags[$fieldIndex]);

                            for($c = $fieldIndex; $c < $fieldsCount; $c++)
                            {
                                $data[$c] = '';
                            }

                            $fieldIndex = 0;
                            $elementClosed = true;

                            fputcsv($csvfp, $data, $delimiter, $enclosure);

                            $data = array();
                            $line = '';
                        }
                        else
                        {
                            //Para que pase a la siguiente línea
                            //self::Msg(htmlentities($line));

                            $line = '';
                        }
                    }
                    elseif(($pos = mb_strpos($line, '</'.$fieldsTags[$fieldIndex].'>')) !== false)
                    {
                        //Etiqueta cerrada normalmente
                        //self::Msg('Etiqueta cerrada normalmente');

                        $aux = trim(mb_substr($line, 0, $pos));
                        if (mb_substr($aux, 0, 9) == '<![CDATA[' && mb_substr($aux, -3) == ']]>') {
                            $aux = mb_substr($aux, 9, -3);
                        } else {
                            $aux = mb_substr($line, 0, $pos);
                        }

                        if ($trim) {
                            $aux = trim($aux);
                        }

                        $data[$fieldIndex] = html_entity_decode($aux); //Aplico html_entity_decode porque puede venir HTML con &lt;...

                        $line = mb_substr($line, $pos + mb_strlen('</'.$fieldsTags[$fieldIndex].'>'));

                        $fieldClosed = true;

                        $fieldIndex++;
                        if($fieldIndex >= $fieldsCount)
                        {
                            $fieldIndex = 0;
                            $elementClosed = true;

                            fputcsv($csvfp, $data, $delimiter, $enclosure);

                            $data = array();
                            $line = '';
                        }
                    }
                    else
                    {
                        //No se encuentra el cierre en la misma línea,
                        //concatenamos la línea siguiente

                        $line .= "\n".fgets($xmlfp); $leidas++;
                    }
                }

                if(empty($line) || !trim($line))
                {
                    $line = fgets($xmlfp); $leidas++;
                }
            }

            //self::Msg("Líneas leidas: {$leidas}");
        }

        if(is_resource($xmlfp))
        {
            fclose($xmlfp);
        }

        if(is_resource($csvfp))
        {
            fclose($csvfp);
        }

        return $leidas;
    }
    /*
    static public function Msg($str)
    {
        echo $str."<br />\n";
        flush();
    }
    */
    /**
     * Convierte un archivo XML en un CSV.
     *
     * Esta función emplea xmlfile2csvfile() si no existe la clase SimpleXMLElement.
     * Siempre procuraremos esta función en vez de xmlfile2csvfile().
     *
     * @param string $xmlpath
     * @param string $csvpath
     * @param mixed $elementTag
     * @param array $fieldsTags
     * @param string $delimiter
     * @param string $enclosure
     * @param bool $trim
     */
    static public function xmlfile2csvfileB($xmlpath, $csvpath, $elementTag, $fieldsTags=array(), $delimiter=',', $enclosure='"', $tagComb='', $fieldsTagsComb=array(), $xmlTo='xml', $trim=true) //xmlfile2csvfileB
    {
        if(class_exists('SimpleXMLElement'))
        {
            $xmlStr = file_get_contents($xmlpath);
            $csvfp = fopen($csvpath, 'w');

            if ($xmlStr !== false && $csvfp != false && self::checkXml($xmlStr, $xmlpath)) {
                $xml = new \SimpleXMLElement($xmlStr);

                if (is_array($elementTag)) {
                    $aux = $xml;
                    foreach ($elementTag as $tag) {
                        $aux = $aux->$tag;
                    }
                } else {
                    $aux = $xml->$elementTag;
                }

                //printf('<pre>$xml = %s</pre>', var_export($xml, true));

                if (empty($fieldsTags)) {
                    $fieldsTags = isset($aux[0]) && is_object($aux[0]) ? array_keys(get_object_vars($aux[0])) : array();
                }

                if ($tagComb) {
                    foreach ($fieldsTags as $key=>$tag) {
                        if ($tag == $tagComb) {
                            unset($fieldsTags[$key]);
                            break;
                        }
                    }

                    foreach ($fieldsTagsComb as $field) {
                        $fieldsTags[] = $field;
                    }
                }

                //printf('<pre>$fieldTags = %s</pre>', var_export($fieldsTags, true));

                fputcsv($csvfp, $fieldsTags, $delimiter, $enclosure);

                foreach ($aux as $item) {
                    $data = array();
                    $faltan = array();

                    if ($tagComb) {
                        $data[$tagComb] = array();

                        if (isset($item->$tagComb)) {
                            $vars = get_object_vars($item->$tagComb);

                            foreach ($vars as $i=>$val) {
                                if (is_object($val)) {
                                    $val = array($val);
                                }

                                foreach ($val as $obj) {
                                    $data[$tagComb][] = get_object_vars($obj);
                                }
                            }
                        }
                    }

                    foreach ($fieldsTags as $key) {
                        if (isset($item->$key)) {
                            $vars = get_object_vars($item->$key);

                            if (empty($vars) || (phpversion() >= '7.2' && count($vars) == 1 && array_key_exists(0, $vars))) {
                                $data[$key] = $trim ? trim((string)$item->$key) : (string)$item->$key;
                            } else {
                                $data[$key] = '';
                                $alFinal = '';
                                $sep = '';
                                foreach ($vars as $i=>$val) {
                                    if (is_array($val)) {
                                        $sep2 = '';
                                        foreach ($val as $str) {
                                            if (is_string($str) || is_object($str)) {
                                                switch ($xmlTo) {
                                                    case 'html_ul':
                                                        if ($data[$key] == '') {
                                                            $data[$key] = "<ul>\n";
                                                            $alFinal = "\n</ul>";
                                                        }

                                                        $data[$key] .= sprintf('%s<li>%s: %s</li>', $sep2, $i, $str);
                                                        break;
                                                    case 'xml':
                                                    default:
                                                        $data[$key] .= sprintf('%s<%s>%s</%s>', $sep2, $i, $str, $i);
                                                        break;
                                                }
                                                $sep2 = "\n";
                                            }
                                        }

                                        break; //Sólo cojo el primero (no espero más)
                                    } else {
                                        switch ($xmlTo) {
                                            case 'html_ul':
                                                if ($data[$key] == '') {
                                                    $data[$key] = "<ul>\n";
                                                    $alFinal = "\n</ul>";
                                                }

                                                $data[$key] .= sprintf('%s<li>%s: %s</li>', $sep, $i, $val);
                                                break;
                                            case 'xml':
                                            default:
                                                $data[$key] .= sprintf('%s<%s>%s</%s>', $sep, $i, $val, $i);
                                                break;
                                        }
                                        $sep = "\n";

                                        if (!isset($item->$i) && in_array($i, $fieldsTags)) {
                                            $data[$i] = $val;
                                            unset($faltan[$i]);
                                        }
                                    }
                                }

                                if ($alFinal) {
                                    $data[$key] .= $alFinal;
                                }
                            }
                        } elseif (!isset($data[$key])) {
                            $data[$key] = '';
                            $faltan[$key] = $key;
                        }
                    }

                    //echo '<pre>$data = '.var_export($data, true).'</pre>';

                    if ($tagComb) {
                        if (empty($data[$tagComb])) {
                            unset($data[$tagComb]);

                            //Añadir campos combinación (vacíos)
                            if (empty($fieldsTagsComb)) {
                                if (empty($faltan)) {
                                    //Nada
                                } else {
                                    foreach ($faltan as $field) {
                                        $data[$field] = '';
                                    }
                                }
                            } else {
                                foreach ($fieldsTagsComb as $field) {
                                    $data[$field] = '';
                                }
                            }

                            fputcsv($csvfp, $data, $delimiter, $enclosure);
                        } else {
                            //echo '<pre>$data[$tagComb] = '.var_export($data[$tagComb], true).'</pre>';

                            foreach ($data[$tagComb] as $combinacion) {
                                $dataComb = $data;
                                unset($dataComb[$tagComb]);

                                //Añadir campos combinación
                                if (empty($fieldsTagsComb)) {
                                    if (empty($faltan)) {
                                        foreach ($combinacion as $key=>$val) {
                                            $dataComb[$key] = $val;
                                        }
                                    } else {
                                        foreach ($faltan as $field) {
                                            $dataComb[$field] = isset($combinacion[$field]) ? $combinacion[$field] : '';
                                        }
                                    }
                                } else {
                                    foreach ($fieldsTagsComb as $field) {
                                        $dataComb[$field] = isset($combinacion[$field]) ? $combinacion[$field] : '';
                                    }
                                }

                                fputcsv($csvfp, $dataComb, $delimiter, $enclosure);
                            }
                        }
                    } else {
                        fputcsv($csvfp, $data, $delimiter, $enclosure);
                    }
                }
            } else {
                trigger_error('No $xmlStr or no $csvfp', E_USER_ERROR);
            }

            if (is_resource($csvfp)) {
                fclose($csvfp);
            }
        } elseif($tagComb) {
            trigger_error('Class SimpleXMLElement not found', E_USER_ERROR);
        } else {
            self::xmlfile2csvfile($xmlpath, $csvpath, is_array($elementTag) ? $elementTag[sizeof($elementTag)-1] : $elementTag, $fieldsTags, $delimiter, $enclosure);
        }
    }

    static public function xmlfile2header($xmlpath, $elementTag) //xmlfile2cabecera
    {
        if (($xmlStr = file_get_contents($xmlpath)) !== false && self::checkXml($xmlStr)) {
            $xml = new \SimpleXMLElement($xmlStr);

            if (is_array($elementTag)) {
                $aux = $xml;
                foreach ($elementTag as $tag) {
                    $aux = $aux->$tag;
                }
            } else {
                $aux = $xml->$elementTag;
            }

            if (isset($aux[0]) && is_object($aux[0])) {
                return array_keys(get_object_vars($aux[0]));
            }
        }

        return false;
    }
}