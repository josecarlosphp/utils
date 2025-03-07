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
 * @desc        Common use functions for working with dates, encapsulated in a class.
 */

namespace josecarlosphp\utils;

abstract class Dates
{
    protected static $defaultLang = 'es';
    protected static $langs = array(
        'es' => array(
            'Y' => 'año',
            'Ys' => 'años',
            'm' => 'mes',
            'ms' => 'meses',
            'd' => 'día',
            'ds' => 'días',
            'H' => 'hora',
            'Hs' => 'horas',
            'i' => 'minuto',
            'is' => 'minutos',
            's' => 'segundo',
            'ss' => 'segundos',
        ),
        'en' => array(
            'Y' => 'year',
            'Ys' => 'years',
            'm' => 'month',
            'ms' => 'months',
            'd' => 'day',
            'ds' => 'days',
            'H' => 'hour',
            'Hs' => 'hours',
            'i' => 'minute',
            'is' => 'minutes',
            's' => 'second',
            'ss' => 'seconds',
        ),
    );

    static public function defaultLang($lang = null)
    {
        if (!is_null($lang) && in_array($lang, self::$langs)) {
            self::$defaultLang = $lang;
        }

        return self::$defaultLang;
    }

    static public function l($q, $lang = null)
    {
        if (is_null($lang) || !in_array($lang, self::$langs)) {
            $lang = self::$defaultLang;
        }

        return isset(self::$langs[$lang][$q]) ? self::$langs[$lang][$q] : false;
    }

    /**
     * Calculates age in years for a given date.
     *
     * @param int $day
     * @param int $month
     * @param int $year
     * @return int
     */
    static public function calculateAge($day, $month, $year) //calcularEdad
    {
        $age = date('Y') - $year - 1; //-1 porque no sé si ha cumplido años ya este año
        if ($age >= 0)  {
            $difMeses = date('n') - $month;
            if ($difMeses == 0) {
                return ((date('j') - $day) < 0) ? $age : ($age + 1);
            }

            return ($difMeses < 0) ? $age : ($age + 1);
        }

        return false;
    }
    /**
     * Calculates age in years for a given date.
     *
     * @param string $date
     * @param string $format
     * @return int
     */
    static public function calculateAgeStr($date, $format='Y-m-d') //calcularEdadStr
    {
        $arr = self::breakdownDate($date, $format);

        return self::calculateAge($arr[2], $arr[1], $arr[0]);
    }

    static public function breakdownDate($date, $format='Y-m-d') //descomponerFecha
    {
        switch (mb_strtolower($format)) {
            case 'd-m-y':
            case 'd/m/y':
            case 'd-m-a':
            case 'd/m/a':
            case 'dd-mm-yyyy':
            case 'dd/mm/yyyy':
            case 'dd-mm-aaaa':
            case 'dd/mm/aaaa':
                $year = intval(substr($date, 6, 4));
                $month = intval(substr($date, 3, 2));
                $day = intval(substr($date, 0, 2));
                break;
            default:
            case 'y-m-d':
            case 'y/m/d':
            case 'a-m-d':
            case 'a/m/d':
            case 'yyyy-mm-dd':
            case 'yyyy/mm/dd':
            case 'aaaa-mm-dd':
            case 'aaaa/mm/dd':
                $year = intval(substr($date, 0, 4));
                $month = intval(substr($date, 5, 2));
                $day = intval(substr($date, 8, 2));
                break;
        }

        if (strlen($date > 10)) {
            $hora = intval(substr($date, 11, 2));
            $minuto = intval(substr($date, 14, 2));
            $segundo = intval(substr($date, 17, 2));
        } else {
            $hora = 0;
            $minuto = 0;
            $segundo = 0;
        }

        return array($year, $month, $day, $hora, $minuto, $segundo);
    }
    /**
     * Porque se definió así y se usa aún en algún código.
     *
     * @deprecated
     * @param string $datetime
     * @return int
     */
    static public function datetime2time($datetime) //fechayhora2time
    {
        return self::date2time($datetime);
    }

    static public function date2time($date, $format='Y-m-d') //fecha2time
    {
        $arr = self::breakdownDate($date, $format);

        return mktime($arr[3], $arr[4], $arr[5], $arr[1], $arr[2], $arr[0]);
    }

    static public function datetime2screen($date, $format='Y-m-d') //fechayhora2screen
    {
        $arr = self::breakdownDate($date, $format);

        return sprintf('%04s-%02s-%02s <span class="small mini">%02s:%02s</span>', $arr[0], $arr[1], $arr[2], $arr[3], $arr[4]);
    }

    static public function Ymd2dmY($date, $separator='-') //fechaYmd2dmY
    {
        $year = substr($date, 0, 4);
        $month = substr($date, 5, 2);
        $day = substr($date, 8, 2);

        return $day . $separator . $month . $separator . $year;
    }

    static public function Ymd2dma($date, $separator='-') //fechaYmd2dma
    {
        $year = substr($date, 2, 2);
        $month = substr($date, 5, 2);
        $day = substr($date, 8, 2);

        return $day . $separator . $month . $separator . $year;
    }

    static public function dmY2Ymd($date, $separator='-') //fechadmY2Ymd
    {
        $year = substr($date, 6, 4);
        $month = substr($date, 3, 2);
        $day = substr($date, 0, 2);

        return $year . $separator . $month . $separator . $day;
    }
    /**
     * Obtiene el número de días de un mes concreto de un año concreto.
     * Los meses son de 1 a 12 pero admite valores mayores o menores,
     * por ejemplo si ponemos mes 0 año 2010 devolverá los días del mes
     * 12 del año 2009
     *
     * @param mixed $month
     * @param int $year
     * @return int
     */
    static public function getNumDaysOfMonth($month, $year) //getNumDaysOfMonth
    {
        $days = array(
            1 => 31,
            2 => 28,
            3 => 31,
            4 => 30,
            5 => 31,
            6 => 30,
            7 => 31,
            8 => 31,
            9 => 30,
            10 => 31,
            11 => 30,
            12 => 31,
        );

        $month = intval($month);

        while ($month < 1) {
            $month += 12;
            $year--;
        }

        while ($month > 12) {
            $month -= 12;
            $year++;
        }

        return ($month == 2 && checkdate(2, 29, $year)) ? $days[$month] + 1 : $days[$month];
    }

    static public function getLastDayOfMonth($month, $year, $format='Y-m-d') //getLastDayOfMonth
    {
        return date($format, self::date2time(sprintf('%04s-%02s-%02s', $year, $month, self::getNumDaysOfMonth($month, $year))));
    }
    /**
     * Añade días a una fecha
     *
     * @param string $date Fecha en formato yyyy-mm-dd
     * @param int $days
     * @param bool $workonly
     * @return string Fecha en formato yyyy-mm-dd
     */
    static public function addDays($date, $days, $workonly=false) //AddDays
    {
        if ($days < 0) {
            return self::susDays($date, $days, $workonly);
        }

        $month = substr($date,5,2);
        $day = substr($date,8,2);
        $year = substr($date,0,4);
        $time = mktime(0, 0, 0, $month, $day, $year);
        while ($days > 0) {
            $ok = true;
            if ($workonly) {
                $day = date('w', $time);
                if ($day < 1 || $day > 5) {
                    $ok = false;
                }
            }

            if ($ok) {
                $days--;
            }

            $day++;
            $time = mktime(0, 0, 0, $month, $day, $year);
        }

        return date('Y-m-d', $time);
    }
    /**
     * Resta días a una fecha
     *
     * @param string $date Fecha en formato yyyy-mm-dd
     * @param int $days
     * @param bool $workonly
     * @return string Fecha en formato yyyy-mm-dd
     */
    static public function susDays($date, $days, $workonly=false) //SusDays
    {
        $month = substr($date,5,2);
        $day = substr($date,8,2);
        $year = substr($date,0,4);
        $time = mktime(0, 0, 0, $month, $day, $year);
        while ($days < 0) {
            $ok = true;
            if ($workonly) {
                $day = date('w', $time);
                if ($day < 1 || $day > 5) {
                    $ok = false;
                }
            }

            if ($ok) {
                $days++;
            }

            $day--;
            $time = mktime(0, 0, 0, $month, $day, $year);
        }

        return date('Y-m-d', $time);
    }
    /**
     * Añade meses a una fecha
     *
     * @param string $date Fecha en formato yyyy-mm-dd
     * @param int $months
     * @return string Fecha en formato yyyy-mm-dd
     */
    static public function addMonths($date, $months) //AddMonths
    {
        $month = substr($date,5,2);
        $day = substr($date,8,2);
        $year = substr($date,0,4);

        return date('Y-m-d', mktime(0, 0, 0, $month + $months, $day, $year));
    }
    /**
     * Diferencia en días entre dos fechas en formato dd/mm/yyyy
     * También admite fecha y hora en formato Y-m-d H:i:s en tal caso tiene en cuenta la hora
     *
     * @param string $desde
     * @param string $hasta
     * @param string $format
     * @return int
     */
    static public function daysDifference($desde, $hasta, $format='dd/mm/yyyy') //DaysDifference
    {
        return self::daysDifferenceTime(self::date2time($desde, $format), self::date2time($hasta, $format));
    }

    static public function daysDifferenceYmd($desde, $hasta) //DaysDifferenceYmd
    {
        return self::daysDifference($desde, $hasta, 'yyyy-mm-dd');
    }

    static public function daysDifferencedmY($desde, $hasta) //DaysDifferencedmY
    {
        return self::daysDifference($desde, $hasta, 'dd/mm/yyyy');
    }

    static public function daysDifferenceTime($desde, $hasta) //DaysDifferenceTime
    {
        return floor(($hasta - $desde) / 86400);
    }

    static public function GetWeekday($day, $month, $year) //GetDiaSemana
    {
        return date('w', mktime(0, 0, 0, $month, $day, $year));
    }
    /**
     * Convierte una cadena de fecha en formato dd/mm/aaaa ó yyyy-mm-dd en un array(año, mes, día)
     *
     * @param string $date
     * @return array
     */
    static public function datestr2datearr($date) //datestr2datearr
    {
        return !is_numeric(substr($date, 2, 1)) && !is_numeric(substr($date, 5, 1)) ?
            array(substr($date, 6, 4), substr($date, 3, 2), substr($date, 0, 2))
            :
            array(substr($date, 0, 4), substr($date, 5, 2), substr($date, 8, 2));
    }

    static public function time2tiempo($time) //time2tiempo
    {
        $days = floor($time / 86400);
        $time -= ($days * 86400);

        $horas = floor($time / 3600);
        $time -= ($horas * 3600);

        $minutos = floor($time / 60);
        $time -= ($minutos * 60);

        return array(
            'd' => $days,
            'H' => $horas,
            'i' => $minutos,
            's' => $time,
        );
    }

    static public function tiempo2str($tiempo, $lang = null) //tiempo2str
    {
        $r = '';
        $sep = '';

        if ($tiempo['d'] > 0) {
            $r .= sprintf('%u %s', $tiempo['d'], self::l($tiempo['d'] == 1 ? 'd' : 'ds', $lang));
            $sep = ', ';
        }

        if ($tiempo['d'] > 0 || $tiempo['H'] > 0) {
            $r .= sprintf('%s%u %s', $sep, $tiempo['H'], self::l($tiempo['H'] == 1 ? 'H' : 'Hs', $lang));
            $sep = ', ';
        }

        if ($tiempo['d'] > 0 || $tiempo['H'] > 0 || $tiempo['i'] > 0) {
            $r .= sprintf('%s%u %s', $sep, $tiempo['i'], self::l($tiempo['i'] == 1 ? 'i' : 'is', $lang));
            $sep = ', ';
        }

        $r .= sprintf('%s%u %s', $sep, $tiempo['s'], self::l($tiempo['s'] == 1 ? 's' : 'ss', $lang));

        return $r;
    }

    static public function time2str($time)
    {
        return self::tiempo2str(self::time2tiempo($time));
    }

    static public function dateDiff($dateIni, $dateFin, $toStr = false)
    {
        $timeIni = self::date2time($dateIni);
        $timeFin = self::date2time($dateFin);

        $time = $timeFin - $timeIni;

        return $toStr ? self::time2str($time) : $time;
    }
}
