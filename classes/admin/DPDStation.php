<?php
/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    DPD France S.A.S. <support.ecommerce@dpd.fr>
 * @copyright 2018 DPD France S.A.S.
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class DPDStation
{
    public $line;
    public $contenu_fichier;
    public function __construct()
    {
        $this->line=str_pad('', 2247);
        $this->contenu_fichier='';
    }
    public function add($txt, $position, $length)
    {
        $txt=$this->stripAccents($txt);
        $this->line=substr_replace($this->line, str_pad($txt, $length), $position, $length);
    }
    public function addLine()
    {
        if ($this->contenu_fichier!='') {
            $this->contenu_fichier=$this->contenu_fichier."\r\n".$this->line;
            $this->line='';
            $this->line=str_pad('', 2247);
        } else {
            $this->contenu_fichier.=$this->line;
            $this->line='';
            $this->line=str_pad('', 2247);
        }
    }
    public function download()
    {
        while (@ob_end_clean()) {
        }
        header('Content-type: application/dat');
        header('Content-Disposition: attachment; filename="DPDFRANCE_'.date('dmY-His').'.dat"');
        echo '$VERSION=110'."\r\n";
        echo $this->contenu_fichier."\r\n";
        exit;
    }
    public function stripAccents($str)
    {
        $str=preg_replace('/[\x{00C0}\x{00C1}\x{00C2}\x{00C3}\x{00C4}\x{00C5}]/u', 'A', $str);
        $str=preg_replace('/[\x{0105}\x{0104}\x{00E0}\x{00E1}\x{00E2}\x{00E3}\x{00E4}\x{00E5}]/u', 'a', $str);
        $str=preg_replace('/[\x{00C7}\x{0106}\x{0108}\x{010A}\x{010C}]/u', 'C', $str);
        $str=preg_replace('/[\x{00E7}\x{0107}\x{0109}\x{010B}\x{010D}}]/u', 'c', $str);
        $str=preg_replace('/[\x{010E}\x{0110}]/u', 'D', $str);
        $str=preg_replace('/[\x{010F}\x{0111}]/u', 'd', $str);
        $str=preg_replace('/[\x{00C8}\x{00C9}\x{00CA}\x{00CB}\x{0112}\x{0114}\x{0116}\x{0118}\x{011A}\x{20AC}]/u', 'E', $str);
        $str=preg_replace('/[\x{00E8}\x{00E9}\x{00EA}\x{00EB}\x{0113}\x{0115}\x{0117}\x{0119}\x{011B}]/u', 'e', $str);
        $str=preg_replace('/[\x{00CC}\x{00CD}\x{00CE}\x{00CF}\x{0128}\x{012A}\x{012C}\x{012E}\x{0130}]/u', 'I', $str);
        $str=preg_replace('/[\x{00EC}\x{00ED}\x{00EE}\x{00EF}\x{0129}\x{012B}\x{012D}\x{012F}\x{0131}]/u', 'i', $str);
        $str=preg_replace('/[\x{0142}\x{0141}\x{013E}\x{013A}]/u', 'l', $str);
        $str=preg_replace('/[\x{00F1}\x{0148}]/u', 'n', $str);
        $str=preg_replace('/[\x{00D2}\x{00D3}\x{00D4}\x{00D5}\x{00D6}\x{00D8}]/u', 'O', $str);
        $str=preg_replace('/[\x{00F2}\x{00F3}\x{00F4}\x{00F5}\x{00F6}\x{00F8}]/u', 'o', $str);
        $str=preg_replace('/[\x{0159}\x{0155}]/u', 'r', $str);
        $str=preg_replace('/[\x{015B}\x{015A}\x{0161}]/u', 's', $str);
        $str=preg_replace('/[\x{00DF}]/u', 'ss', $str);
        $str=preg_replace('/[\x{0165}]/u', 't', $str);
        $str=preg_replace('/[\x{00D9}\x{00DA}\x{00DB}\x{00DC}\x{016E}\x{0170}\x{0172}]/u', 'U', $str);
        $str=preg_replace('/[\x{00F9}\x{00FA}\x{00FB}\x{00FC}\x{016F}\x{0171}\x{0173}]/u', 'u', $str);
        $str=preg_replace('/[\x{00FD}\x{00FF}]/u', 'y', $str);
        $str=preg_replace('/[\x{017C}\x{017A}\x{017B}\x{0179}\x{017E}]/u', 'z', $str);
        $str=preg_replace('/[\x{00C6}]/u', 'AE', $str);
        $str=preg_replace('/[\x{00E6}]/u', 'ae', $str);
        $str=preg_replace('/[\x{0152}]/u', 'OE', $str);
        $str=preg_replace('/[\x{0153}]/u', 'oe', $str);
        $str=preg_replace('/[\x{0022}\x{0025}\x{0026}\x{0027}\x{00A1}\x{00A2}\x{00A3}\x{00A4}\x{00A5}\x{00A6}\x{00A7}\x{00A8}\x{00AA}\x{00AB}\x{00AC}\x{00AD}\x{00AE}\x{00AF}\x{00B0}\x{00B1}\x{00B2}\x{00B3}\x{00B4}\x{00B5}\x{00B6}\x{00B7}\x{00B8}\x{00BA}\x{00BB}\x{00BC}\x{00BD}\x{00BE}\x{00BF}\x{2019}]/u', ' ', $str);
        return $str;
    }
}
