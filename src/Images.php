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
 * @desc        Common use functions for working with images, encapsulated in a class.
 */

namespace josecarlosphp\utils;

abstract class Images
{
    /**
     * @return bool
     * @param $imagefile string
     * @param $thumbwidth mixed
     * @param $destinydir string
     * @param $newname string
     * @desc Creates a thumbnail file from an image file. $thumbwidth can be an integer (pixels #) or a percentage as "75%" for example.
     */
    static public function createThumbnail($imagefile, $thumbwidth, $destinydir, $newname=null) //createThumbnail
    {
        list($width, $height, $imagetype) = getimagesize($imagefile);
        if(strpos($thumbwidth, '%') == strlen($thumbwidth)-1)
        {
            $percent = substr($thumbwidth, 0, strlen($thumbwidth)-1);
            $newwidth = ($width/100)*$percent;
        }
        else
        {
            $newwidth = $thumbwidth;
        }
        $newheight = (int)(($height * $newwidth) / $width);
        $thumb = imagecreatetruecolor($newwidth,$newheight);
        $destinydir = dirname($destinydir."/x").'/';
        if($newname != null && trim($newname) != '')
        {
            $newname = trim($newname);
        }
        else
        {
            $newname = str_replace(dirname($imagefile), '', $imagefile);
            $newname = str_replace("/", '', $newname);
            $newname = str_replace("\\", '', $newname);
        }

        switch($imagetype)
        {
            case IMAGETYPE_GIF:
                 $function_image_create = 'imagecreatefromgif';
                break;
            case IMAGETYPE_JPEG:
                 $function_image_create = 'imagecreatefromjpeg';
                break;
            case IMAGETYPE_PNG:
                $function_image_create = 'imagecreatefrompng';
                break;
            case IMAGETYPE_WBMP:
                $function_image_create = 'imagecreatefromwbmp';
                break;
            case IMAGETYPE_XBM:
                $function_image_create = 'imagecreatefromxbm';
                break;
            case IMAGETYPE_WEBP:
                $function_image_create = 'imagecreatefromwebp';
                break;
            case IMAGETYPE_SWF:
            case IMAGETYPE_PSD:
            case IMAGETYPE_BMP:
            case IMAGETYPE_TIFF_II:
            case IMAGETYPE_TIFF_MM:
            case IMAGETYPE_JPC:
            case IMAGETYPE_JP2:
            case IMAGETYPE_JPX:
            case IMAGETYPE_JB2:
            case IMAGETYPE_SWC:
            case IMAGETYPE_IFF:
            case IMAGETYPE_ICO:
            default:
                switch(Files::getExtension($imagefile))
                {
                    case 'png':
                       $function_image_create = 'imagecreatefrompng';
                       break;
                    case 'gif':
                       $function_image_create = 'imagecreatefromgif';
                       break;
                    case 'webp':
                       $function_image_create = 'imagecreatefromwebp';
                       break;
                    case 'jpg':
                    case 'jpeg':
                    default:
                       $function_image_create = 'imagecreatefromjpeg';
                       break;
                }
                break;
        }

        switch(Files::getExtension($newname))
        {
            case 'png':
                $function_image_new = 'imagepng';
                break;
            case 'gif':
                $function_image_new = 'imagegif';
                break;
            case 'webp':
                $function_image_new = 'imagewebp';
                break;
            case 'jpg':
            case 'jpeg':
            default:
                $function_image_new = 'imagejpeg';
                break;
        }

        $source = @$function_image_create($imagefile);
        imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        $function_image_new($thumb, $destinydir.$newname);

        return file_exists($destinydir.$newname);
    }
    /**
     * Redimensiona una imagen haciendo que su lado más largo mida $size.
     * Si $size es un array, el nuevo tamaño será $size[0] (ancho) x $size[1] (alto)
     * si ambos elementos tienen valor, si solo uno de ellos está definido, el otro
     * se ajustará proporcionalmente
     *
     * $sindistorsion
     * 0 = Sin ajuste (por defecto)
     * 1 = Ajuste para encajar dentro del nuevo tamaño sin perder imagen (pero pueden aparecer espacios vacíos, se rellenarán con el color de fondo)
     * 2 = Ajuste al alto (se puede perder imagen por los lados)
     * 3 = Ajuste al ancho (se puede perder imagen arriba y abajo)
     *
     * @param string $imagefile
     * @param mixed $size
     * @param string $destinydir
     * @param string $newname
     * @param int $skiptype 0 = no skip (resize always), 1 = resize only if get bigger, 2 = resize only if get smaller
     * @param int $sindistorsion
     * @param array $colordefondo
     * @param bool $extForzada
     * @return bool
     */
    static public function resizeImage($imagefile, $size, $destinydir, $newname=null, $skiptype=0, $sindistorsion=0, $colordefondo=array(255,255,255), $extForzada=false) //resizeImage
    {
        $destinydir = dirname($destinydir."/x")."/";

        if ($newname != null && trim($newname) != '') {
            $newname = trim($newname);
        } else {
            $newname = str_replace("\\", '', str_replace("/", '', str_replace(dirname($imagefile), '', $imagefile)));
        }

        list($width, $height) = getimagesize($imagefile);

        if ($width > 0 && $height > 0) {

            if (is_array($size)) {
                $newwidth = $size[0];
                $newheight = $size[1];

                if (!$newwidth && !$newheight) {
                    $newwidth = $width;
                    $newheight = $height;
                } elseif(!$newwidth) {
                    $newwidth = (int)(($width * $newheight) / $height);
                } elseif(!$newheight) {
                    $newheight = (int)(($height * $newwidth) / $width);
                }
            } else {
                if ($width >= $height) {
                    //Redimensionar conforme al ancho
                    $newwidth = $size;
                    $newheight = (int)(($height * $newwidth) / $width);
                } else {
                    //Redimensionar conforme al alto
                    $newheight = $size;
                    $newwidth = (int)(($width * $newheight) / $height);
                }
            }

            if ($sindistorsion) {
                //Creamos la imagen con el color de fondo
                $imagen_fondo = imagecreatetruecolor($newwidth, $newheight);
                $color = imagecolorAllocate($imagen_fondo, $colordefondo[0], $colordefondo[1], $colordefondo[2]);
                imagefill($imagen_fondo, 0, 0, $color);

                if ($sindistorsion === true) {
                    $sindistorsion = 1;
                }

                switch ($sindistorsion) {
                    case 3: //Anchura
                        $dst_w = $newwidth;
                        $dst_h = (int)(($height * $newwidth) / $width);
                        break;
                    case 2: //Altura
                        $dst_h = $newheight;
                        $dst_w = (int)(($width * $newheight) / $height);
                        break;
                    case 1: //El lado más largo
                    default:
                        if ($newheight > $newwidth) {
                            if ($height >= $width) {
                                $dst_w = $newwidth;
                                $dst_h = (int)(($height * $newwidth) / $width);

                                if ($dst_h > $newheight) {
                                    $dst_h = $newheight;
                                    $dst_w = (int)(($width * $newheight) / $height);
                                }
                            } else {
                                $dst_h = $newheight;
                                $dst_w = (int)(($width * $newheight) / $height);

                                if ($dst_w > $newwidth) {
                                    $dst_w = $newwidth;
                                    $dst_h = (int)(($height * $newwidth) / $width);
                                }
                            }
                        } else {
                            if ($height >= $width) {
                                $dst_h = $newheight;
                                $dst_w = (int)(($width * $newheight) / $height);

                                if ($dst_w > $newwidth) {
                                    $dst_w = $newwidth;
                                    $dst_h = (int)(($height * $newwidth) / $width);
                                }
                            } else {
                                $dst_w = $newwidth;
                                $dst_h = (int)(($height * $newwidth) / $width);

                                if ($dst_h > $newheight) {
                                    $dst_h = $newheight;
                                    $dst_w = (int)(($width * $newheight) / $height);
                                }
                            }
                        }
                        break;
                }

                //Posición de la imagen sobre el fondo
                $dst_x = (int)(($newwidth-$dst_w)/2);
                $dst_y = (int)(($newheight-$dst_h)/2);

                //Creamos una versión redimensionada de la imagen para ponerla sobre el fondo
                $source = imagecreatefromstring(file_get_contents($imagefile));
                $thumb = imagecreatetruecolor($dst_w, $dst_h);

                switch ($extForzada ? $extForzada : getExtension($newname)) {
                    case 'png':
                        imagealphablending($thumb, false);
                        imagesavealpha($thumb, true);
                        $transparent = imagecolorallocatealpha($thumb, $colordefondo[0], $colordefondo[1], $colordefondo[2], 127);
                        imagefilledrectangle($thumb, 0, 0, $dst_w, $dst_h, $transparent);

                        $function_image_new = 'imagepng';
                        break;
                    case 'gif':
                        $colorTransparencia = imagecolortransparent($source);
                        if ($colorTransparencia > -1 && $colorTransparencia < imagecolorstotal($source)) { //Si tiene transparencia y el índice de la transparencia está dentro de la paleta
                            $colorTransparente = imagecolorsforindex($source, $colorTransparencia);
                            $idColorTransparente = imagecolorallocatealpha($thumb, $colorTransparente['red'], $colorTransparente['green'], $colorTransparente['blue'], $colorTransparente['alpha']);
                            imagefill($thumb, 0, 0, $idColorTransparente);
                            imagecolortransparent($thumb, $idColorTransparente);
                        }
                        $function_image_new = 'imagegif';
                        break;
                    case 'webp':
                        //TODO: Transparencia ¿se hará como para png?
                        $function_image_new = 'imagewebp';
                        break;
                    case 'jpg':
                    case 'jpeg':
                    default:
                        $function_image_new = 'imagejpeg';
                        break;
                }

                imagecopyresampled($thumb, $source, 0, 0, 0, 0, $dst_w, $dst_h, $width, $height);

                //Sobrepongo la imagen
                imagecopyresized($imagen_fondo, $thumb, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $dst_w, $dst_h);

                $function_image_new($imagen_fondo, $destinydir.$newname);

                unset($imagen_fondo);
            } else {
                switch ($skiptype) {
                    case 1:
                        if ($width >= $height) {
                            if ($newwidth < $width) {
                                $newwidth = $width;
                                $newheight = $height;
                            }
                        } elseif($newheight < $height) {
                            $newheight = $height;
                            $newwidth = $width;
                        }
                        break;
                    case 2:
                        if ($width >= $height) {
                            if ($newwidth > $width) {
                                $newwidth = $width;
                                $newheight = $height;
                            }
                        } elseif ($newheight > $height) {
                            $newheight = $height;
                            $newwidth = $width;
                        }
                        break;
                    default:
                        //nada
                        break;
                }

                $source = imagecreatefromstring(file_get_contents($imagefile));
                $thumb = imagecreatetruecolor($newwidth,$newheight);
                switch ($extForzada ? $extForzada : Files::getExtension($newname)) {
                    case 'png':
                        imagealphablending($thumb, false);
                        imagesavealpha($thumb, true);
                        $transparent = imagecolorallocatealpha($thumb, $colordefondo[0], $colordefondo[1], $colordefondo[2], 127);
                        imagefilledrectangle($thumb, 0, 0, $newwidth, $newheight, $transparent);

                        $function_image_new = 'imagepng';
                        break;
                    case 'gif':
                        $colorTransparencia = imagecolortransparent($source);
                        if ($colorTransparencia > -1 && $colorTransparencia < imagecolorstotal($source)) { //Si tiene transparencia y el índice de la transparencia está dentro de la paleta
                            $colorTransparente = imagecolorsforindex($source, $colorTransparencia);
                            $idColorTransparente = imagecolorallocatealpha($thumb, $colorTransparente['red'], $colorTransparente['green'], $colorTransparente['blue'], $colorTransparente['alpha']);
                            imagefill($thumb, 0, 0, $idColorTransparente);
                            imagecolortransparent($thumb, $idColorTransparente);
                        }
                        $function_image_new = 'imagegif';
                        break;
                    case 'webp':
                        //TODO: Transparencia ¿se hará como para png?
                        $function_image_new = 'imagewebp';
                        break;
                    case 'jpg':
                    case 'jpeg':
                    default:
                        $function_image_new = 'imagejpeg';
                        break;
                }
                imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
                $function_image_new($thumb, $destinydir.$newname); //TODO: calidad (al menos para jpg)
            }

            unset($source);
            unset($thumb);

            return file_exists($destinydir.$newname);
        }

        return false;
    }

    static public function marcaDeAgua($img_original, $img_marcadeagua, $calidad=100, $img_nueva=null, $posicion=null) //marcaDeAgua
    {
        if(is_null($img_nueva))
        {
            $img_nueva = $img_original;
        }
        // obtener datos de la fotografia
        $info_original = getimagesize($img_original);
        $anchura_original = $info_original[0];
        $altura_original = $info_original[1];
        // obtener datos de la "marca de agua"
        $info_marcadeagua = getimagesize($img_marcadeagua);
        $anchura_marcadeagua = $info_marcadeagua[0];
        $altura_marcadeagua = $info_marcadeagua[1];
        if(is_null($posicion))
        {
            // calcular la posición donde debe copiarse la "marca de agua" en la fotografia (centrada)
            $horizmargen = ($anchura_original - $anchura_marcadeagua)/2;
            $vertmargen = ($altura_original - $altura_marcadeagua)/2;
        }
        elseif(is_array($posicion))
        {
            $horizmargen = $posicion[0];
            $vertmargen = $posicion[1];
        }
        else switch($posicion)
        {
            case 'topleft':
                $horizmargen = 0;
                $vertmargen = 0;
                break;
            case 'topright':
                $horizmargen = $anchura_original - $anchura_marcadeagua;
                $vertmargen = 0;
                break;
            case 'bottomleft':
                $horizmargen = 0;
                $vertmargen = $altura_original - $altura_marcadeagua;
                break;
            case 'bottomright':
                $horizmargen = $anchura_original - $anchura_marcadeagua;
                $vertmargen = $altura_original - $altura_marcadeagua;
                break;
        }
        // crear imagen desde el original
        $original = imagecreatefromjpeg($img_original);
        imagealphablending($original, true);
        // crear nueva imagen desde la marca de agua
        $marcadeagua = imagecreatefrompng($img_marcadeagua);
        // copiar la "marca de agua" en la fotografia
        imagecopy($original, $marcadeagua, $horizmargen, $vertmargen, 0, 0, $anchura_marcadeagua, $altura_marcadeagua);
        // guardar la nueva imagen
        imagejpeg($original, $img_nueva, $calidad);
        // cerrar las imágenes
        imagedestroy($original);
        imagedestroy($marcadeagua);

        return is_file($img_nueva);
    }

    static public function watermark($imagepath, $watermarkpath, $outputpath, $xAlign='middle', $yAlign='middle', $transparency=60, $proporcionW=0) //watermark
    {
        $Xoffset = $Yoffset = $xpos = $ypos = 0;

        switch(Files::getExtension($imagepath))
        {
            case 'png':
                $function = 'imagecreatefrompng';
                break;
            case 'gif':
                $function = 'imagecreatefromgif';
                break;
            case 'webp':
                $function = 'imagecreatefromwebp';
                break;
            case 'jpg':
            case 'jpeg':
            default:
                $function = 'imagecreatefromjpeg';
                break;
        }

        if(($image = $function($imagepath)))
        {
            switch(Files::getExtension($watermarkpath))
            {
                case 'png':
                    $function = 'imagecreatefrompng';
                    break;
                case 'gif':
                    $function = 'imagecreatefromgif';
                    break;
                case 'webp':
                    $function = 'imagecreatefromwebp';
                    break;
                case 'jpg':
                case 'jpeg':
                default:
                    $function = 'imagecreatefromjpeg';
                    break;
            }

            if(($imagew = $function($watermarkpath)))
            {
                list($watermarkWidth, $watermarkHeight) = getimagesize($watermarkpath);
                list($imageWidth, $imageHeight) = getimagesize($imagepath);

                if($proporcionW && $proporcionW != (100 * $watermarkWidth) / $imageWidth) //round ?
                {
                    $watermarkWidth_new = ($imageWidth * $proporcionW) / 100;
                    $watermarkHeight_new = (int)(($watermarkHeight * $watermarkWidth_new) / $watermarkWidth);
                    $watermarkpath_new = dirname($watermarkpath).'/'.microtime(true).basename($watermarkpath);
                    if(self::resizeImage($watermarkpath, array($watermarkWidth_new, $watermarkHeight_new), dirname($watermarkpath), basename($watermarkpath_new)))
                    {
                        if(($imagew = $function($watermarkpath_new)))
                        {
                            $watermarkWidth = $watermarkWidth_new;
                            $watermarkHeight = $watermarkHeight_new;
                            $watermarkpath = $watermarkpath_new;
                        }

                        unlink($watermarkpath_new);
                    }
                }

                switch(strtolower($xAlign))
                {
                    case 'center':
                    case 'c':
                    case 'middle':
                    case 'm':
                        $xpos = $imageWidth / 2 - $watermarkWidth / 2 + $Xoffset;
                        break;
                    case 'left':
                    case 'l':
                        $xpos = 0 + $Xoffset;
                        break;
                    case 'right':
                    case 'r':
                        $xpos = $imageWidth - $watermarkWidth - $Xoffset;
                        break;
                }

                switch(strtolower($yAlign))
                {
                    case 'center':
                    case 'c':
                    case 'middle':
                    case 'm':
                        $ypos = $imageHeight / 2 - $watermarkHeight / 2 + $Yoffset;
                        break;
                    case 'top':
                    case 'y':
                        $ypos = 0 + $Yoffset;
                        break;
                    case 'bottom':
                    case 'b':
                        $ypos = $imageHeight - $watermarkHeight - $Yoffset;
                        break;
                }

                if($function == 'imagecreatefrompng')
                {
                     //Si la marca de agua es un png lo hacemos sin transparencia, porque nos sale el fondo negro
                    if(imagecopy($image, $imagew, $xpos, $ypos, 0, 0, $watermarkWidth, $watermarkHeight))
                    {
                        return imagejpeg($image, $outputpath, 100);
                    }
                }
                elseif(imagecopymerge($image, $imagew, $xpos, $ypos, 0, 0, $watermarkWidth, $watermarkHeight, $transparency))
                {
                    return imagejpeg($image, $outputpath, 100);
                }
            }
        }

        return false;
    }

    static public function getExtensionImg($file) //getExtensionImg
    {
        switch(exif_imagetype($file))
        {
            case IMAGETYPE_GIF:
                return 'gif';
            case IMAGETYPE_JPEG:
                return 'jpg';
            case IMAGETYPE_PNG:
                return 'png';
            case IMAGETYPE_WEBP:
                return 'webp';
            case IMAGETYPE_SWF:
                return 'swf';
            case IMAGETYPE_PSD:
                return 'psd';
            case IMAGETYPE_BMP:
                return 'bmp';
            case IMAGETYPE_TIFF_II:
            case IMAGETYPE_TIFF_MM:
                return 'tiff';
            case IMAGETYPE_JPC:
                return 'jpc';
            case IMAGETYPE_JP2:
                return 'jp2';
            case IMAGETYPE_JPX:
                return 'jpx';
            case IMAGETYPE_JB2:
                return 'jb2';
            case IMAGETYPE_SWC:
                return 'swc';
            case IMAGETYPE_IFF:
                return 'iff';
            case IMAGETYPE_WBMP:
                return 'wbmp';
            case IMAGETYPE_XBM:
                return 'xbm';
            case IMAGETYPE_ICO:
                return 'ico';
        }

        return Files::getExtension($file);
    }
}

if (!function_exists('ImageCreateFromBmp')) {
    /**
     * Convert BMP to GD
     *
     * @param string $src
     * @param string|bool $dest
     * @return bool
     */
    function bmp2gd($src, $dest = false)
    {
        if(!($src_f = fopen($src, 'rb')))
        {
            return false;
        }

        if(!($dest_f = fopen($dest, 'wb')))
        {
            return false;
        }

        $header = unpack('vtype/Vsize/v2reserved/Voffset', fread( $src_f, 14));

        $info = unpack('Vsize/Vwidth/Vheight/vplanes/vbits/Vcompression/Vimagesize/Vxres/Vyres/Vncolor/Vimportant',
        fread($src_f, 40));

        extract($info);
        extract($header);

        if($type != 0x4D42)
        {
            return false;
        }

        $palette_size = $offset - 54;
        $ncolor = $palette_size / 4;
        $gd_header = '';

        $gd_header .= ($palette_size == 0) ? "\xFF\xFE" : "\xFF\xFF";
        $gd_header .= pack("n2", $width, $height);
        $gd_header .= ($palette_size == 0) ? "\x01" : "\x00";
        if($palette_size)
        {
            $gd_header .= pack("n", $ncolor);
        }

        $gd_header .= "\xFF\xFF\xFF\xFF";

        fwrite($dest_f, $gd_header);

        if($palette_size)
        {
            $palette = fread($src_f, $palette_size);

            $gd_palette = '';
            $j = 0;

            while($j < $palette_size)
            {
                $b = $palette[$j++];
                $g = $palette[$j++];
                $r = $palette[$j++];
                $a = $palette[$j++];

                $gd_palette .= "$r$g$b$a";
            }

            $gd_palette .= str_repeat("\x00\x00\x00\x00", 256 - $ncolor);

            fwrite($dest_f, $gd_palette);
        }

        $scan_line_size = (($bits * $width) + 7) >> 3;
        $scan_line_align = ($scan_line_size & 0x03) ? 4 - ($scan_line_size & 0x03) : 0;

        for($i = 0, $l = $height - 1; $i < $height; $i++, $l--)
        {
            fseek($src_f, $offset + (($scan_line_size + $scan_line_align) * $l));
            $scan_line = fread($src_f, $scan_line_size);
            if($bits == 24)
            {
                $gd_scan_line = '';
                $j = 0;
                while($j < $scan_line_size)
                {
                    $b = $scan_line[$j++];
                    $g = $scan_line[$j++];
                    $r = $scan_line[$j++];
                    $gd_scan_line .= "\x00$r$g$b";
                }
            }
            elseif($bits == 8)
            {
                $gd_scan_line = $scan_line;
            }
            elseif($bits == 4)
            {
                $gd_scan_line = '';
                $j = 0;
                while($j < $scan_line_size)
                {
                    $byte = ord($scan_line[$j++]);
                    $p1 = chr($byte >> 4);
                    $p2 = chr($byte & 0x0F);
                    $gd_scan_line .= "$p1$p2";
                }
                $gd_scan_line = substr($gd_scan_line, 0, $width);
            }
            elseif($bits == 1)
            {
                $gd_scan_line = '';
                $j = 0;
                while($j < $scan_line_size)
                {
                    $byte = ord($scan_line[$j++]);
                    $p1 = chr((int) (($byte & 0x80) != 0));
                    $p2 = chr((int) (($byte & 0x40) != 0));
                    $p3 = chr((int) (($byte & 0x20) != 0));
                    $p4 = chr((int) (($byte & 0x10) != 0));
                    $p5 = chr((int) (($byte & 0x08) != 0));
                    $p6 = chr((int) (($byte & 0x04) != 0));
                    $p7 = chr((int) (($byte & 0x02) != 0));
                    $p8 = chr((int) (($byte & 0x01) != 0));
                    $gd_scan_line .= "$p1$p2$p3$p4$p5$p6$p7$p8";
                }

                $gd_scan_line = substr($gd_scan_line, 0, $width);
            }

            fwrite($dest_f, $gd_scan_line);
        }

        fclose($src_f);
        fclose($dest_f);

        return true;
    }
    /**
     * Create image from BMP image file
     *
     * @param string $filename
     * @return bin string on success
     * @return bool false on failure
     */
    function ImageCreateFromBmp($filename)
    {
        $tmp_name = tempnam(dirname($filename), 'GD');

        if(bmp2gd($filename, $tmp_name))
        {
            $img = imagecreatefromgd($tmp_name);
            unlink($tmp_name);

            return $img;
        }

        return false;
    }
}