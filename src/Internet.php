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
 * @desc        Common use Internet related functions, encapsulated in a class.
 */

namespace josecarlosphp\utils;

abstract class Internet
{
    /**
     * Genera una contraseña aleatoria
     *
     * @param int $minlen
     * @param int $maxlen
     * @param string $validChars
     * @return string
     */
    static public function generateRandomPass($minlen=7, $maxlen=12, $validChars='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789') //GenerateRandomPass
    {
        $len = mt_rand($minlen, $maxlen);
        $max = strlen($validChars)-1;
        $pass = '';
        for ($c=0; $c<$len; $c++) {
            $pass .= substr($validChars, mt_rand(0, $max), 1);
        }

        return $pass;
    }
    /**
     * Genera una clave aleatoria.
     * El parámetro $format es opcional, si se indica se ignoran los demás y se creará una clave siguiendo el formato.
     * El formato es una cadena formada sólo por caracteres A, a, 1
     * A = Una letra mayúscula
     * a = Una letra minúscula
     * 1 = Un dígito
     * [otro] = Un carácter aleatorio (letra mayúscula o minúscula, o dígito)
     * \ = Carácter de escape, hace que el siguiente carácter sea interpretado como constante
     *
     * EJEMPLOS DE FORMATO - RESULTADO:
     * AAA111	-	VUS293
     * AAA-111	-	VUSc293
     * AAA\-111	-	VUS-293
     * AAA\\111	-	VUS\293
     * Aaa1		-	Vus2
     *
     * @param int $longitud
     * @param bool $conletrasmin
     * @param bool $conletrasmay
     * @param bool $connumeros
     * @param string $format
     * @return string
     */
    static public function generateRandomKey($longitud=0, $conletrasmin=true, $conletrasmay=true, $connumeros=true, $format='') //GenerateRandomKey
    {
        $key = '';

        if ($format) {
            $scape = false;

            for ($c=0,$len=strlen($format); $c<$len; $c++) {
                $char = substr($format, $c, 1);

                if ($scape) {
                    $key .= $char;
                    $scape = false;
                } else {
                    switch ($char) {
                        case '\\':
                            $scape = true;
                            break;
                        case '1':
                            $key .= mt_rand(0, 9);
                            break;
                        case 'a':
                            $key .= chr(mt_rand(ord('a'), ord('z')));
                            break;
                        case 'A':
                            $key .= chr(mt_rand(ord('A'), ord('Z')));
                            break;
                        default:
                            $key .= self::generateRandomKey(1);
                            break;
                    }
                }
            }

            return $key;
        }

        if ($longitud <= 0) {
            $longitud = 6;
        }

        $opciones = array();
        if ($connumeros) {
            $opciones[] = 0;
        }
        if ($conletrasmay) {
            $opciones[] = 2;
        }
        if ($conletrasmin) {
            $opciones[] = 1;
        }
        $size = sizeof($opciones);
        if ($size == 0) {
            return null;
        }
        $size--;
        for ($c=0; strlen($key)<$longitud; $c++) {
            switch ($opciones[mt_rand(0, $size)]) {
                case 0:
                    $key .= mt_rand(0, 9);
                    break;
                case 1:
                    $key .= chr(mt_rand(ord('a'), ord('z')));
                    break;
                case 2:
                    $key .= chr(mt_rand(ord('A'), ord('Z')));
                    break;
            }
        }

        return $key;
    }
    /**
     * @return string
     * @desc Gets the client IP address
     */
    static public function getClientIp() //GetClientIP
    {
        $server = isset($_SERVER) ? $_SERVER : $HTTP_SERVER_VARS;

        $ip = $server['REMOTE_ADDR'];

        if (empty($ip)) {
            $ip = getenv('REMOTE_ADDR');
        }

        if (!empty($server['HTTP_CLIENT_IP'])) {
            $ip = $server['HTTP_CLIENT_IP'];
        }

        $tmpip = getenv('HTTP_CLIENT_IP');
        if (!empty($tmpip)) {
            $ip = $tmpip;
        }
        if (!empty($server['HTTP_X_FORWARDED_FOR'])) {
            $ip = preg_replace('/,.*/', '', $server['HTTP_X_FORWARDED_FOR']);
        }

        $tmpip = getenv('HTTP_X_FORWARDED_FOR');
        if (!empty($tmpip)) {
            $ip = preg_replace('/,.*/', '', $tmpip);
        }

        return $ip;
    }
    /**
     * @desc Verify if an url is valid (is online)
     * @param string $url
     * @return bool
     */
    static public function verifyUrl($url) //VerifyUrl
    {
        return @fopen($url, 'r');
    }
    /**
     * @desc Lo mismo que el modificador url_format creado para Smarty, pero con la opción de aplicar o no htmlentities
     */
    static public function urlFormat($url, $shorturl=false, $htmlentities=true, $flags=ENT_COMPAT, $encoding='UTF-8') //UrlFormat
    {
        $url = html_entity_decode($url, $flags, $encoding);

        if ($shorturl) {
            $indexphp_pos = strpos($url, 'index.php?');
            $quest_pos = strpos($url, '?');
            $url = substr($url, 0, $indexphp_pos) . substr($url, $quest_pos+1);
            $sharp_pos = strpos($url, '#');
            if ($sharp_pos) {
                $sharp_txt = substr($url, $sharp_pos);
                $url = substr($url, 0, $sharp_pos);
            }
            $items = split('&', $url);
            $url = '';
            foreach ($items as $item) {
                $url .= substr($item, 0, strpos($item, '=')) . '-';
                $url .= substr($item, strpos($item, '=') + 1) . '-';
            }
            $url = substr($url, 0, strlen($url) - 1) . '.html';
            if ($sharp_pos) {
                $url .= $sharp_txt;
            }
        }

        return $htmlentities ? htmlentities(str_replace(' ', '20%', $url), $flags, $encoding) : str_replace(' ', '20%', $url);
    }
    /**
     * @param string $name
     * @param string $content
     */
    static public function downloadVirtualFile($name, $content) //downloadVirtualFile
    {
        if (isset($_SERVER['HTTP_USER_AGENT']) && mb_strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) {
            header('Content-Type: application/force-download');
        } else {
            header('Content-Type: application/octet-stream');
        }

        if (headers_sent()) {
            die('Ya ha sido enviada otra informaci&oacute;n al navegador, no se puede descargar el archivo.');
        } else {
            header('Content-Length: ' . mb_strlen($content));
            header('Content-disposition: attachment; filename=' . $name);
            die($content);
        }
    }
    /**
     * @return bool
     * @desc Comprueba si el visitante es un cliente navegador de internet (no un bot)
     */
    static public function userAgentIsAnExplorer($useragent=null) //UserAgentIsAnExplorer
    {
        if (is_null($useragent)) {
            $useragent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        }

        return $useragent != ''
            && (strpos($useragent, 'Nav') !== false
                || strpos($useragent, 'Gold') !== false
                || strpos($useragent, 'X11') !== false
                || strpos($useragent, 'Mozilla') !== false
                || strpos($useragent, 'Netscape') !== false
                || strpos($useragent, 'MSIE') !== false
                || strpos($useragent, 'Lynx') !== false
                || strpos($useragent, 'Opera') !== false
                || strpos($useragent, 'Konqueror') !== false)
            && (strpos($useragent, 'Google') === false || strpos($useragent, 'Chrome') !== false)
            && strpos($useragent, 'Yahoo') === false
            && strpos($useragent, 'Crawler') === false
            && strpos($useragent, 'robot') === false
            ;
    }

    static public function userAgentIsBot($useragent=null, $customBots='', $defaultBots='Teoma,alexa,froogle,Gigabot,inktomi,looksmart,URL_Spider_SQL,Firefly,NationalDirectory,AskJeeves,TECNOSEEK,InfoSeek,WebFindBot,girafabot,crawler,www.galaxy.com,Googlebot,Scooter,TechnoratiSnoop,Rankivabot,Mediapartners-Google,Sogouwebspider,WebAltaCrawler,TweetmemeBot,Butterfly,Twitturls,Me.dium,Twiceler') //UserAgentIsBot
    {
        if (is_null($useragent)) {
            $useragent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        }

        if ($useragent == '') {
            return true;
        }

        $bots = explode(',', $defaultBots . ',' . $customBots);
        foreach ($bots as $bot) {
            $bot = trim($bot);
            if ($bot && stripos($useragent, $bot) !== false) {
                return true;
            }
        }

        return false;
    }
    /**
     * Filtra metacaracteres convirtiéndolos a entidades html o eliminando las etiquetas.
     * Si magic_quotes no está activado y $addslashes es true, además añade barras de escape
     *
     * @param mixed $data
     * @param mixed $key
     * @param boolean $striptags
     */
    static public function filterMetachars(&$data, $key=null, $striptags=false, $addslashes=true) //FiltrarMetacaracteres
    {
        if (is_array($data)) {
            array_walk($data, 'filterMetachars', array($striptags, $addslashes));
        } else {
            if (!get_magic_quotes_gpc() && $addslashes) {
                $data = addslashes($data);
            }

            $data = $striptags ? strip_tags($data) : htmlentities($data);
        }
    }
    /**
     * Limpia datos frente a inyección sql y acceso a archivos,
     * no usar para información que permita incluir caracteres como ; , . \ /
     * Puede recibir un array como parámetro, limpiará sus elementos recursivamente
     *
     * @param mixed $data
     * @param array $charsToClean
     * @return mixed
     */
    static public function cleanData($data, $charsToClean=array(';', ',', '.', '\\', '/')) //cleanData
    {
        if (is_array($data)) {
            for ($c=0,$size=sizeof($data); $c<$size; $c++) {
                $data[$c] = self::cleanData($data[$c], $charsToClean);
            }

            return $data;
        }

        return str_replace($charsToClean, '', $data);
    }

    static public function reemplazarCaracteresRaros($str, $ponerEnMinusculas=false) //reemplazarCaracteresRaros
    {
        $patrones = array();

        //No estoy haciendo todavía strtolower
        $patrones[] = array(array('Á','À','Ä','Â'), 'A');
        $patrones[] = array(array('É','È','Ë','Ê'), 'E');
        $patrones[] = array(array('Í','È','Ï','Î'), 'I');
        $patrones[] = array(array('Ó','Ò','Ö','Ô'), 'O');
        $patrones[] = array(array('Ú','Ù','Ü','Û'), 'U');
        $patrones[] = array('Ñ', 'N');
        $patrones[] = array('Ç', 'C');

        $patrones[] = array(array('á','à','ä','â','Ã¡','ª'), 'a');
        $patrones[] = array(array('é','è','ë','ê','Ã©'), 'e');
        $patrones[] = array(array('í','ì','ï','î','Ã­'), 'i');
        $patrones[] = array(array('ó','ò','ö','ô','Ã³','º'), 'o');
        $patrones[] = array(array('ú','ù','ü','û'), 'u');
        $patrones[] = array('ñ', 'n');
        $patrones[] = array('ç', 'c');
        $patrones[] = array('€', 'E');
        $patrones[] = array('$', 'S');
        $patrones[] = array('.', '-');

        foreach ($patrones as $patron) {
            $str = str_replace($patron[0], $patron[1], $str);
        }

        //Si hago el strtolower antes de reemplazar los caracteres, se me pierden las letras estas

        return $ponerEnMinusculas ? mb_strtolower($str) : $str;
    }
    /**
     * Prepara una cadena para que forme parte de una url amigable
     *
     * @param string $str
     * @return string
     */
    static public function string2friendly($str) //string2friendly
    {
        $str = preg_replace('(([^0-9a-zA-Z_\.])+)', '-', self::reemplazarCaracteresRaros($str, true));

        //Quitar los --
        while (mb_strpos($str, '--') !== false) {
            $str = str_replace('--', '-', $str);
        }

        //Quitar los - del final
        $aux = mb_strlen($str) - 1;
        while (mb_substr($str, $aux) == '-') {
            $str = mb_substr($str, 0, $aux);
        }

        //Quitar los - del principio
        while (mb_substr($str, 0, 1) == '-') {
            $str = mb_substr($str, 1);
        }

        return $str;
    }
    /**
     * Obtiene información sobre la localización de una IP concreta, la actual del visitante o la del servidor
     * mediante los datos obtenidos desde geoiptool.com
     *
     * @param string $ip
     * @param string $que
     * @param string $defaultIp
     * @return mixed
     */
    static public function getGeoInfo($ip='', $que='', $defaultIp='clientip') //GetGeoInfo
    {
        if ($ip == '') {
            switch ($defaultIp) {
                case 'clientip':
                    $ip = self::getClientIp();
                    break;
                case 'serverip':
                default:
                    //nada
                    break;
            }
        }

        $html = self::getUrlContents('http://www.geoiptool.com/?IP=' . urlencode($ip));

        switch (strtolower($que)) {
            case 'paisnombre':
            case 'countryname':
                $html = substr($html, strpos($html, 'Country:')+8);
                $html = trim(substr($html, 0, strpos($html, '</a>')));
                break;
            case 'paiscodigo':
            case 'countrycode':
                $html = substr($html, strpos($html, 'Country code:')+13);
                $html = trim(substr($html, 0, strpos($html, '</tr>')));
                break;
            case 'region':
                $html = substr($html, strpos($html, 'Region:')+7);
                $html = trim(substr($html, 0, strpos($html, '</a>')));
                break;
            case 'ciudad':
            case 'city':
                $html = substr($html, strpos($html, 'City')+5);
                $html = trim(substr($html, 0, strpos($html, '</tr>')));
                break;
            case 'todo':
            case 'all':
                $html = substr($html, strpos($html, 'Host Name:'));
                $html = trim(substr($html, 0, strpos($html, 'New tool for your')));
                break;
            case 'resumen':
            case 'summary':
                $aux = $html;
                $aux = substr($aux, strpos($aux, 'Country:')+8);
                $html = trim(substr($aux, 0, strpos($aux, '</a>')));
                $aux = substr($aux, strpos($aux, 'Country code:</span></td>')+26);
                $html .= ' '.trim(substr($aux, 0, strpos($aux, '</td>')));
                $aux = substr($aux, strpos($aux, 'Region:')+7);
                $html .= ' '.trim(substr($aux, 0, strpos($aux, '</a>')));
                $aux = substr($aux, strpos($aux, 'City:</span></td>')+17);
                $html .= ' '.trim(substr($aux, 0, strpos($aux, '</td>')));
                break;
            case 'array':
            default:
                $array = array();
                $aux = $html;
                $aux = substr($aux, strpos($aux, 'Host Name:</span></td>')+22);
                $array['hostname'] = trim(strip_tags(substr($aux, 0, strpos($aux, '</td>'))));
                $aux = substr($aux, strpos($aux, 'IP Address:</span></td>')+23);
                $array['ip'] = trim(strip_tags(substr($aux, 0, strpos($aux, '</td>'))));
                $aux = substr($aux, strpos($aux, 'Country:')+8);
                $array['countryname'] = trim(strip_tags(substr($aux, 0, strpos($aux, '</a>'))));
                $aux = substr($aux, strpos($aux, 'Country code:</span></td>')+26);
                $array['countrycode'] = trim(strip_tags(substr($aux, 0, strpos($aux, '</td>'))));
                $aux = substr($aux, strpos($aux, 'Region:')+7);
                $array['region'] = trim(strip_tags(substr($aux, 0, strpos($aux, '</a>'))));
                $aux = substr($aux, strpos($aux, 'City:</span></td>')+17);
                $array['city'] = trim(strip_tags(substr($aux, 0, strpos($aux, '</td>'))));
                $aux = substr($aux, strpos($aux, 'Postal code:</span></td>')+24);
                $array['postalcode'] = trim(strip_tags(substr($aux, 0, strpos($aux, '</td>'))));
                $aux = substr($aux, strpos($aux, 'Calling code:</span></td>')+25);
                $array['callingcode'] = trim(strip_tags(substr($aux, 0, strpos($aux, '</td>'))));
                $aux = substr($aux, strpos($aux, 'Longitude:</span></td>')+23);
                $array['longitude'] = trim(strip_tags(substr($aux, 0, strpos($aux, '</td>'))));
                $aux = substr($aux, strpos($aux, 'Latitude:</span></td>')+21);
                $array['latitude'] = trim(strip_tags(substr($aux, 0, strpos($aux, '</td>'))));
                return $array;
        }

        return htmlentities(trim(strip_tags($html)));
    }
    /**
     * Como file_get_contents aplicado a una URL
     *
     * @param string $url
     * @param string $useragent
     * @param array $extraOpt
     * @param bool $devolverElError
     * @return string
     */
    static public function getUrlContents($url, $useragent='', $extraOpt=null, $devolverElError=true) //GetURLContents
    {
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            if ($useragent) {
                curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
            }
            if (is_array($extraOpt)) {
                foreach ($extraOpt as $key=>$value) {
                    curl_setopt($ch, $key, $value);
                }
            }
            $str = curl_exec($ch);
            if ($str === false && $devolverElError) {
                $str = 'Error '.curl_errno($ch).': '.curl_error($ch);
            }
            curl_close($ch);
        } else {
            $str = file_get_contents($url);
        }

        return $str;
    }
    /**
     * Transforma una cadena en meta keywords
     *
     * @param string $str
     * @param bool $html_entity_decode
     * @param int $maxWords
     * @param int $minLength
     * @param mixed $flags
     * @param string $encoding
     * @return string
     */
    static public function string2keywords($str, $html_entity_decode=false, $maxWords=15, $minLength=3, $flags=ENT_COMPAT, $encoding='UTF-8') //string2keywords
    {
        $str = trim($str);

        if ($str != '') {
            if ($html_entity_decode) {
                $str = html_entity_decode($str, $flags, $encoding);
            }

            //Si hago el strtolower antes de reemplazar los caracteres, se me pierden las letras estas
            //$str = mb_strtolower($str);

            $patrones = array();

            //No estoy haciendo todavía strtolower
            $patrones[] = array(array('Á','À','Ä','Â'), 'A');
            $patrones[] = array(array('É','È','Ë','Ê'), 'E');
            $patrones[] = array(array('Í','È','Ï','Î'), 'I');
            $patrones[] = array(array('Ó','Ò','Ö','Ô'), 'O');
            $patrones[] = array(array('Ú','Ù','Ü','Û'), 'U');
            $patrones[] = array('Ñ', 'n');

            $patrones[] = array(array('á','à','ä','â','Ã¡'), 'a');
            $patrones[] = array(array('é','è','ë','ê','Ã©'), 'e');
            $patrones[] = array(array('í','ì','ï','î','Ã­'), 'i');
            $patrones[] = array(array('ó','ò','ö','ô','Ã³'), 'o');
            $patrones[] = array(array('ú','ù','ü','û'), 'u');
            $patrones[] = array('ñ', 'n');
            $patrones[] = array('€', 'E');
            $patrones[] = array('$', 'S');
            $patrones[] = array('.', ' ');

            foreach ($patrones as $patron) {
                $str = str_replace($patron[0], $patron[1], $str);
            }

            //Ahora hago el strtolower
            $str = mb_strtolower($str);

            $str = trim(preg_replace('(([^0-9a-zA-Z_\.])+)', ' ', $str));

            //Quitar los dobles espacios
            while (mb_strpos($str, '  ') !== false) {
                $str = str_replace('  ', ' ', $str);
            }

            $tmp = array();
            $division = explode(' ', $str);
            foreach ($division AS $trozo) {
                if (mb_strlen($trozo) >= $minLength && !in_array($trozo, $tmp)) {
                    $tmp[] = $trozo;

                    $maxWords--;

                    if ($maxWords <= 0) {
                        break;
                    }
                }
            }

            $str = implode(', ', $tmp);
        }

        return $str;
    }
    /**
     * Quita la '/' final de una cadena (si la tiene)
     *
     * @param string $str
     * @return string
     */
    static public function quitarBarra($str) //quitarBarra
    {
        $aux = mb_strlen($str) - 1;
        if (mb_substr($str, $aux) == '/') {
            $str = mb_substr($str, 0, $aux);
        }

        return $str;
    }
    /**
     * Quita la '/' inicial de una cadena (si la tiene)
     *
     * @param string $str
     * @return string
     */
    static public function quitarBarraIni($str) //quitarBarraIni
    {
        if (mb_substr($str, 0, 1) == '/') {
            $str = mb_substr($str, 1);
        }

        return $str;
    }
    /**
     * Pone la '/' final de una cadena (si no la tiene)
     *
     * @param string $str
     * @return string
     */
    static public function ponerBarra($str) //ponerBarra
    {
        $len = mb_strlen($str);
        if ($len == 0 || mb_substr($str, $len-1) != '/') {
            $str .= '/';
        }

        return $str;
    }
    /**
     * Pone la '/' al inicio de una cadena (si no la tiene)
     *
     * @param string $str
     * @return string
     */
    static public function anteponerBarra($str) //anteponerBarra
    {
        $len = mb_strlen($str);
        if ($len == 0 || mb_substr($str, 0, 1) != '/') {
            $str = '/'.$str;
        }

        return $str;
    }
    /**
     * Obtiene la ruta dentro del dominio según una url
     *
     * @param string $url
     * @return string
     */
    static public function url2path($url) //url2path
    {
        $path = substr($url, strpos($url, '/')+1);
        $path = substr($path, strpos($path, '/')+1);
        $path = substr($path, strpos($path, '/')+1);

        return $path;
    }
    /**
     * Transforma una cadena en meta description
     *
     * @param string $str
     * @param bool $html_entity_decode
     * @param int $maxlen
     * @param int $desviacion
     * @param array $palabritas
     * @param mixed $flags
     * @param string $encoding
     * @param bool $htmlentities
     * @return string
     */
    static public function string2description($str, $html_entity_decode=false, $maxlen=180, $desviacion=20, $palabritas=array('a', 'de', 'por', 'para', 'y', 'sin', 'desde', 'con', 'e', 'o', 'ó', 'sus'), $flags=ENT_COMPAT, $encoding='UTF-8', $htmlentities=true) //string2description
    {
        $str = trim(str_replace(array("\n", "\r"), ' ', $str));

        if ($html_entity_decode) {
            $str = html_entity_decode($str, $flags, $encoding);
        }

        while (mb_strpos($str, '  ') != false) {
            $str = str_replace('  ', ' ', $str);
        }

        $cortada = false;

        if ($maxlen > 0) {
            $i = 0;
            $iMax = 1000;
            $pos = mb_strrpos($str, ' ');
            $aux = mb_substr($str, 0, $pos);
            $max = $maxlen + $desviacion;
            while ($pos !== false && mb_strlen($aux) > $max) {
                $str = $aux;
                $pos = mb_strrpos($str, ' ');
                $aux = mb_substr($str, 0, $pos);

                $cortada = true;

                $i++;
                if ($i > $iMax) {
                    $str = mb_substr($str, 0, $maxlen);
                    break;
                }
            }
        }

        $pos = mb_strrpos($str, ' ');
        $ultimaPalabra = mb_substr($str, $pos+1);
        while (in_array($ultimaPalabra, $palabritas)) {
            $str = mb_substr($str, 0, $pos);
            $pos = mb_strrpos($str, ' ');
            $ultimaPalabra = mb_substr($str, $pos+1);
        }

        $pos = mb_strlen($str) - 1;
        while ($pos && in_array(mb_substr($str, $pos), array(',', ';', '-', '_', '·', '\\', '/', '(', ')', '=', '+', '*', ':', '<'))) { //, '>'
            $str = mb_substr($str, 0, $pos);
            $pos--;
        }

        if ($cortada && mb_substr($str, mb_strlen($str)-1) != '.') {
            $str .= '...';
        }

        return $htmlentities ? htmlentities($str, $flags, $encoding) : $str;
    }

    static public function utf8_encode($str)
    {
        if (version_compare(PHP_VERSION, '8.2.0') >= 0) {
            return mb_convert_encoding($str, 'UTF-8', mb_list_encodings());
        }

        return utf8_encode($str);
    }

    static public function utf8_encode_once($str) //utf8_encode_once
    {
        if (!self::is_utf8($str)) {
            return self::utf8_encode($str);
        }

        return $str;
    }

    static public function utf8_decode($str)
    {
        if (version_compare(PHP_VERSION, '8.2.0') >= 0) {
            return mb_convert_encoding($str, 'ISO-8859-1', 'UTF-8');
        }

        return utf8_decode($str);
    }

    static public function utf8_decode_once($str) //utf8_decode_once
    {
        if (self::is_utf8($str)) {
            return self::utf8_decode($str);
        }

        return $str;
    }

    static public function is_utf8($str) //is_utf8
    {
        if (function_exists('mb_detect_encoding')) {
            return mb_detect_encoding($str.'a', 'UTF-8', true) == 'UTF-8';
        }

        $c = 0;
        $b = 0;
        $bits = 0;
        $len = strlen($str);

        for ($i = 0; $i < $len; $i++) {
            $c = ord($str[$i]);
            if ($c > 128) {
                if (($c >= 254)) {
                    return false;
                }

                if ($c >= 252) {
                    $bits = 6;
                } elseif ($c >= 248) {
                    $bits = 5;
                } elseif ($c >= 240) {
                    $bits = 4;
                } elseif ($c >= 224) {
                    $bits = 3;
                } elseif ($c >= 192) {
                    $bits = 2;
                } else {
                    return false;
                }

                if (($i + $bits) > $len) {
                    return false;
                }

                while ($bits > 1) {
                    $i++;
                    $b = ord($str[$i]);
                    if ($b < 128 || $b > 191) {
                        return false;
                    }

                    $bits--;
                }
            }
        }

        return true;
    }

    static public function utf8_encode_file($origen, $destino, $length=null) //utf8_encode_file
    {
        if (($fpR = fopen($origen, 'r'))) {
            if (($fpW = fopen($destino, 'w'))) {
                while (($line = fgets($fpR, $length))) {
                    if (fputs($fpW, utf8_encode($line), $length) === false) {
                        return false;
                    }
                }

                fclose($fpR);
                fclose($fpW);

                return true;
            }
        }

        return false;
    }

    static public function pingDomain($domain, &$errno=null, &$errstr=null, $timeout=10) //pingDomain
    {
        $starttime = microtime(true);
        $file = fsockopen($domain, 80, $errno, $errstr, $timeout);
        $stoptime = microtime(true);
        $status = 0;

        if (!$file) {
            $status = -1;  // Site is down
        } else {
            fclose($file);
            $status = floor(($stoptime - $starttime) * 1000);
        }

        return $status;
    }
}