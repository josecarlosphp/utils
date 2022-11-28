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
 * @desc        Common use functions for working with files, encapsulated in a class.
 */

namespace josecarlosphp\utils;

abstract class Files
{
    /**
     * @return mixed
     * @param $file string
     * @param $evalonerror string
     * @desc If given file exists, includes it, if not, evals given code string ("false" by default).
     */
    static public function include_ifExists($file, $evalonerror='false') //include_ifexists
    {
        return file_exists($file) ? include($file) : eval('return ' . $evalonerror . ';');
    }
    /**
     * @return mixed
     * @param $file string
     * @param $evalonerror string
     * @desc If given file exists, includes_once it, if not, evals given code string ("false" by default).
     */
    static public function include_once_ifExists($file, $evalonerror='false') //include_once_ifexists
    {
        return file_exists($file) ? include_once($file) : eval('return ' . $evalonerror . ';');
    }
    /**
     * @return mixed
     * @param $file string
     * @param $evalonerror string
     * @desc If given file exists, requires it, if not, evals given code string ("false" by default).
     */
    static public function require_ifExists($file, $evalonerror='false') //require_ifexists
    {
        return file_exists($file) ? require($file) : eval('return ' . $evalonerror . ';');
    }
    /**
     * @return mixed
     * @param $file string
     * @param $evalonerror string
     * @desc If given file exists, requires_once it, if not, evals given code string ("false" by default).
     */
    static public function require_once_ifExists($file, $evalonerror='false') //require_once_ifexists
    {
        return file_exists($file) ? require_once($file) : eval('return ' . $evalonerror . ';');
    }
    /**
     * @return array
     * @param $dir string
     * @param $includepath bool
     * @param $recursive bool
     * @param $mask string
     * @desc Gets the names of the dirs existing in a given dir, with dir path if $includepath is true.
     */
    static public function getDirs($dir, $includepath=false, $recursive=false, $mask='') //getDirs
    {
        $pos = strrpos($dir,'/');
        if ($pos == (strlen($dir) - 1)) {
            $dir = substr($dir, 0, $pos);
        }
        $dirs = array();
        $currentdir = getcwd();
        if (chdir($dir)) {
            $handle = opendir('.');
            while (($file = readdir($handle)) !== false) {
                if ($file != '.' && $file != '..' && is_dir($file) && ($mask == '' || mb_strpos($file, $mask) !== false)) {
                    $dirs[] = $includepath ? $dir.'/'.$file : $file;

                    if ($recursive) {
                        if ($includepath) {
                            chdir($currentdir);
                            $dirs = array_merge($dirs, self::getDirs($dir.'/'.$file,true,true));
                            chdir($dir);
                        } else {
                            $dirs = array_merge($dirs, self::getDirs($file,false,true));
                        }
                    }
                }
            }
            chdir($currentdir);
        }
        return $dirs;
    }

    static public function getSubDirs($dir) //getSubDirs
    {
        $len = mb_strlen($dir);
        $dirs = getDirs($dir, true, true);
        sort($dirs);
        for ($c=0,$size=sizeof($dirs); $c<$size; $c++) {
            $dirs[$c] = mb_substr($dirs[$c], $len);
        }

        return $dirs;
    }
    /**
     * @return array
     * @param $dir string
     * @param $excludedextensions array
     * @param $includepath bool
     * @param $recursive bool
     * @desc Gets the names of the files existing in a given dir, with dir path if $includepath is true.
     */
    static public function getFiles($dir, $excludedextensions=array(), $includepath=false, $recursive=false) //getFiles
    {
        $pos = strrpos($dir,'/');
        if ($pos == (strlen($dir) - 1)) {
            $dir = substr($dir, 0, $pos);
        }
        $files = array();
        $currentdir = getcwd();
        if (chdir($dir)) {
            $handle = opendir('.');
            while (($file = readdir($handle)) !== false) {
                if ($recursive && $file != '.' && $file != '..' && is_dir($file)) {
                    if ($includepath) {
                        chdir($currentdir);
                        $files = array_merge($files, self::getFiles($dir.'/'.$file,$excludedextensions,true,true));
                        chdir($dir);
                    } else {
                        $files = array_merge($files, self::getFiles($file,$excludedextensions,false,true));
                    }
                } elseif(is_file($file) && !in_array(self::getExtension($file),$excludedextensions)) {
                    $files[] = $includepath ? $dir . '/' . $file : $file;
                }
            }
            chdir($currentdir);
        }
        return $files;
    }
    /**
     * @return array
     * @param $dir string
     * @param $includedextensions array
     * @param $includepath bool
     * @param $recursive bool
     * @desc Gets the names of the files existing in a given dir wich extension is in $includedextensions array, with dir path if $includepath is true.
     */
    static public function getFilesExt($dir, $includedextensions, $includepath=false, $recursive=false) //getFilesExt
    {
        $pos = strrpos($dir,'/');
        if ($pos == (strlen($dir) - 1)) {
            $dir = substr($dir, 0, $pos);
        }
        $files = array();
        $currentdir = getcwd();
        if (chdir($dir)) {
            $handle = opendir('.');
            while (($file = readdir($handle)) !== false) {
                if ($recursive && $file != '.' && $file != '..' && is_dir($file)) {
                    if ($includepath) {
                        chdir($currentdir);
                        $files = array_merge($files, self::getFilesExt($dir.'/'.$file,$includedextensions,true,true));
                        chdir($dir);
                    } else {
                        $files = array_merge($files, self::getFilesExt($file,$includedextensions,false,true));
                    }
                } elseif (is_file($file) && in_array(self::getExtension($file),$includedextensions)) {
                    $files[] = $includepath ? $dir.'/'.$file : $file;
                }
            }
            chdir($currentdir);
        }

        return $files;
    }

    static public function getRandomFileExt($dir, $includedextensions, $includepath=false) //getRandomFileExt
    {
        $files = self::getFilesExt($dir, $includedextensions, $includepath, false);

        return $files[mt_rand(0, count($files) - 1)];
    }
    /**
     * Obtiene el árbol de carpetas y archivos de un directorio.
     *
     * @param string $dir
     * @return array
     */
    static public function getTree($dir) //getTree
    {
        $result = array();

        $aux = self::getDirs($dir);
        foreach ($aux as $item) {
            $result[$item] = self::getTree(Internet::ponerBarra($dir) . $item);
        }

        $aux = self::getFiles($dir);
        foreach($aux as $item)
        {
            $result[$item] = $item;
        }

        return $result;
    }
    /**
     * @return int
     * @param $dir string
     * @param $extensions array
     * @param $including bool
     * @desc Gets the number of dirs existing in a given dir, excluding (default) or including only the ones wich extension is in $extensions
     */
    static public function countDirs($dir, $extensions=array(), $including=false) //countDirs
    {
        $currentdir = getcwd();
        chdir($dir);
        $handle = opendir('.');
        $count = 0;
        while (($file = readdir($handle)) !== false) {
            if ($including) {
                if ($file != '.' && $file != '..' && is_dir($file) && in_array(self::getExtension($file), $extensions)) {
                    $count++;
                }
            } elseif ($file != '.' && $file != '..' && is_dir($file) && !in_array(self::getExtension($file), $extensions)) {
                $count++;
            }
        }
        chdir($currentdir);

        return $count;
    }
    /**
     * @return int
     * @param $dir string
     * @param $extensions array
     * @param $including bool
     * @desc Gets the number of files existing in a given dir, excluding (default) or including only the ones wich extension is in $extensions
     */
    static public function countFiles($dir, $extensions=array(), $including=false) //countFiles
    {
        $currentdir = getcwd();
        chdir($dir);
        $handle = opendir('.');
        $count = 0;
        while (($file = readdir($handle)) !== false) {
            if ($including) {
                if (is_file($file) && in_array(self::getExtension($file), $extensions)) {
                    $count++;
                }
            } elseif (is_file($file) && !in_array(self::getExtension($file), $extensions)) {
                $count++;
            }
        }
        chdir($currentdir);

        return $count;
    }
    /**
     * @return string
     * @param $file string
     * @param $toLower bool
     * @desc Gets the extension of a given file, in lowercase if $toLower
     */
    static public function getExtension($file, $toLower=true) //getExtension
    {
        $file = basename($file);
        $pos = strrpos($file, '.');

        if ($file == '' || $pos === false) {
            return '';
        }

        $extension = substr($file, $pos+1);
        if ($toLower) {
            $extension = strtolower($extension);
        }

        return $extension;
    }
    /**
     * @return string
     * @param $file string
     * @desc Gets the name of a given file
     */
    static public function getName($file, $toLower=false) //getName
    {
        $name = ($dotpos = strrpos($file, '.')) ? substr($file, 0, $dotpos) : $file;

        return $toLower ? mb_strtolower($name) : $name;
    }
    /**
     * @return bool
     * @param $file string
     * @desc Deletes a file.
     */
    static public function deleteFile($file) //deleteFile
    {
        if (is_file($file)) {
            return unlink($file);
        }

        return true;
    }
    /**
     * @return bool
     * @param $dir string
     * @param $deleteEvenIfNotEmpty bool
     * @desc Deletes a dir.
     */
    static public function deleteDir($dir, $deleteEvenIfNotEmpty=true) //deleteDir
    {
        if (is_dir($dir)) {
            if (self::is_emptyDir($dir)) {
                return rmdir($dir);
            }

            return $deleteEvenIfNotEmpty ? self::drainDir($dir) && rmdir($dir) : false;
        }

        return true;
    }
    /**
     * @return bool
     * @param $dir string
     * @param $createIfNotExists bool
     * @param $mode int
     * @desc Drain a dir.
     */
    static public function drainDir($dir, $createIfNotExists=true, $mode=0755) //drainDir
    {
        if (is_dir($dir)) {
            $currentdir = getcwd();
            chdir($dir);
            $handle = opendir('.');
            while (($file = readdir($handle)) !== false) {
                if ($file != '.' && $file != '..' && is_dir($file)) {
                    self::deleteDir($file);
                } elseif (is_file($file)) {
                    unlink($file);
                }
            }
            closedir($handle);
            chdir($currentdir);
        } elseif($createIfNotExists) {
            mkdir($dir, $mode);
        }

        return self::is_emptyDir($dir);
    }
    /**
     * @return bool
     * @param $dir string
     * @param $mode int
     * @param $drainIfExists
     * @desc Makes a dir.
     */
    static public function makeDir($dir, $mode=0755, $drainIfExists=false) //makeDir
    {
        if (is_dir($dir)) {
            if ($drainIfExists) {
                self::drainDir($dir);
            }
        } else {
            $padre = dirname($dir);
            if ($padre && self::makeDir($padre)) {
                mkdir($dir, $mode);
            }
        }

        return is_dir($dir);
    }
    /**
     * @return bool
     * @param string
     * @desc Checks if a given path is a dir and is empty.
     */
    static public function is_emptyDir($dir) //is_emptyDir
    {
        if (is_dir($dir)) {
            $isempty = true;
            $currentdir = getcwd();
            chdir($dir);
            $handle = opendir('.');
            while (($file = readdir($handle)) !== false) {
                if (($file != '.' && $file != '..' && is_dir($file)) || is_file($file)) {
                    $isempty = false;
                    break;
                }
            }
            closedir($handle);
            chdir($currentdir);

            return $isempty;
        }

        return false;
    }

    static public function dirsize($dir) //dirsize
    {
        $size = 0;
        $pos = strrpos($dir,'/');
        if ($pos == (strlen($dir) - 1)) {
            $dir = substr($dir, 0, $pos);
        }
        $currentdir = getcwd();
        if (chdir($dir)) {
            $handle = opendir('.');
            while (($file = readdir($handle)) !== false) {
                if ($file != '.' && $file != '..' && is_dir($file)) {
                    $size += self::dirsize($file);
                } elseif (is_file($file)) {
                    $size += filesize($file);
                }
            }
            chdir($currentdir);
        }

        return $size;
    }
    /**
     * Elimina los archivos de un directorio.
     *
     * @param string $dir
     * @param int &$c
     * @param mixed $exts
     * @param string $mask
     * @param bool $recursive
     * @param int $antiguedad
     * @param array $excluidos
     * @return bool
     */
    static public function deleteFiles($dir, &$c, $exts=null, $mask='', $recursive=false, $antiguedad=0, $excluidos=array()) //deleteFiles
    {
        $c = 0;
        $ok = true;
        if (is_dir($dir)) {
            $currentdir = getcwd();
            chdir($dir);
            $handle = opendir('.');
            while (($file = readdir($handle)) !== false) {
                if (is_file($file)) {
                    if (($antiguedad == 0 || time() - filemtime($file) > $antiguedad) && !in_array($file, $excluidos)) {
                        $extension = self::getExtension($file);
                        if ((is_null($exts) || (is_array($exts) && in_array($extension, $exts)) || (is_string($exts) && $extension == $exts))
                            &&
                            ($mask == '' || mb_strpos(self::getName($file), $mask) !== false))
                        {
                            if (unlink($file)) {
                                $c++;
                            } else {
                                $ok = false;
                            }
                        }
                    }
                } elseif ($recursive && $file != '.' && $file != '..' && is_dir($file)) {
                    $z = 0;
                    $ok &= self::deleteFiles($dir, $z, $exts, $mask, true);
                    $c += $z;
                }
            }
            closedir($handle);
            chdir($currentdir);
        }

        return $ok;
    }
    /**
     * Copia el contenido de un directorio a otro
     *
     * @param string $src
     * @param string $dst
     * @param octal $mod
     * @return bool
     */
    static public function copyDir($src, $dst, $moddir=0755) //copyDir
    {
        $ok = true;
        $dir = opendir($src);
        self::makeDir($dst, $moddir);
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                if (is_dir($src.'/'.$file)) {
                    $ok &= self::copyDir($src.'/'.$file, $dst.'/'.$file, $moddir);
                } else {
                    $ok &= copy($src.'/'.$file, $dst.'/'.$file);
                }
            }
        }
        closedir($dir);

        return $ok;
    }
    /**
     * @desc Comprime un archivo
     * @param string $nom_arxiu
     * @return string
     * PHP5
    static public function comprimir($filename) //comprimir
    {
        try  {
            $fptr = fopen($filename, 'rb');
            $dump = fread($fptr, filesize($filename));
            fclose($fptr);

            //Comprime al máximo nivel, 9
            $gzbackupData = gzencode($dump,9);

            $fptr = fopen($filename.'.gz', 'wb');
            fwrite($fptr, $gzbackupData);
            fclose($fptr);

            //Devuelve el nombre del archivo comprimido
            return $filename.'.gz';
        } catch(Exception $ex) {
            return false;
        }
    }*/

    static public function isParentDir($dir, $son) //isParentDir
    {
        $ok = false;
        $cwd = getcwd();
        if (chdir($dir)) {
            $dir = getcwd(); //Para que sea ruta absoluta
            chdir($cwd);

            if ($son && is_dir($aux = (is_file($son) ? dirname($son) : $son)) && chdir($aux)) {
                if (mb_substr(getcwd(), 0, mb_strlen($dir)) == $dir) {
                    $ok = true;
                }
                chdir($cwd);
            }
        }

        return $ok;
    }
}