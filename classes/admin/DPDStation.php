<?php
/**
 * 2007-2019 PrestaShop
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
 * @copyright 2019 DPD France S.A.S.
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
        $str=preg_replace('/[\x{2105}]/u', 'c/o', $str);
        $str=preg_replace('/[\x{2116}]/u', 'No', $str);
        $str=preg_replace('/[\x{0022}\x{0025}\x{0026}\x{0027}\x{00A1}\x{00A2}\x{00A3}\x{00A4}\x{00A5}\x{00A6}\x{00A7}\x{00A8}\x{00AA}\x{00AB}\x{00AC}\x{00AD}\x{00AE}\x{00AF}\x{00B0}\x{00B1}\x{00B2}\x{00B3}\x{00B4}\x{00B5}\x{00B6}\x{00B7}\x{00B8}\x{00BA}\x{00BB}\x{00BC}\x{00BD}\x{00BE}\x{00BF}\x{2019}]/u', ' ', $str);
        return $str;
    }

    public function formatRow($internalref, $order_id, $service, $advalorem, $order_total_paid, $retour, $retour_option, $poids, $address_delivery, $code_pays_dest, $mobile, $tel_dest, $relay_id, $customer_email, $nom_exp, $address2_exp, $cp_exp, $ville_exp, $address_exp, $code_pays_exp, $tel_exp, $instr_liv_cleaned, $compte_chargeur, $email_exp, $gsm_exp)
    {
        self::add($internalref, 0, 35);                                                         //  Référence client N°1
        self::add(str_pad((int)$poids, 8, '0', STR_PAD_LEFT), 37, 8);                           //  Poids du colis sur 8 caractères
        if ($service == 'REL') {
            self::add($address_delivery->lastname, 60, 35);                                     //  Nom du destinataire
            self::add($address_delivery->firstname, 95, 35);                                    //  Prénom du destinataire
        } else {
            if ($address_delivery->company) {
                self::add($address_delivery->company, 60, 35);                                  //  Nom société
                self::add($address_delivery->lastname.' '.$address_delivery->firstname, 95, 35);//  Nom et prénom du destinataire
            } else {
                self::add($address_delivery->lastname.' '.$address_delivery->firstname, 60, 35);//  Nom et prénom du destinataire
            }
        }
        self::add($address_delivery->address2, 130, 140);                                       //  Complément d’adresse 2 a 5
        self::add($address_delivery->postcode, 270, 10);                                        //  Code postal
        self::add($address_delivery->city, 280, 35);                                            //  Ville
        self::add($address_delivery->address1, 325, 35);                                        //  Rue
        self::add('', 360, 10);                                                                 //  Filler
        self::add($code_pays_dest, 370, 3);                                                     //  Code Pays destinataire
        self::add($tel_dest, 373, 30);                                                          //  Téléphone
        self::add($nom_exp, 418, 35);                                                           //  Nom expéditeur
        self::add($address2_exp, 453, 35);                                                      //  Complément d’adresse 1
        self::add($cp_exp, 628, 10);                                                            //  Code postal
        self::add($ville_exp, 638, 35);                                                         //  Ville
        self::add($address_exp, 683, 35);                                                       //  Rue
        self::add($code_pays_exp, 728, 3);                                                      //  Code Pays
        self::add($tel_exp, 731, 30);                                                           //  Tél.
        self::add($instr_liv_cleaned, 761, 140);                                                //  Instructions de livraison
        self::add(date('d/m/Y'), 901, 10);                                                      //  Date d'expédition théorique
        self::add(str_pad($compte_chargeur, 8, '0', STR_PAD_LEFT), 911, 8);                     //  N° de compte chargeur DPD
        self::add($order_id, 919, 35);                                                          //  Code à barres
        self::add($order_id, 954, 35);                                                          //  N° de commande - Id Order Prestashop
        if ($advalorem) {
            if (in_array($order_id, $advalorem)) {
                self::add(str_pad(number_format($order_total_paid, 2, '.', ''), 9, '0', STR_PAD_LEFT), 1018, 9); // Montant valeur colis
            }
        }
        self::add($order_id, 1035, 35);                                                         //  Référence client N°2 - Id Order Prestashop
        self::add($email_exp, 1116, 80);                                                        //  E-mail expéditeur
        self::add($gsm_exp, 1196, 35);                                                          //  GSM expéditeur
        self::add($customer_email, 1231, 80);                                                   //  E-mail destinataire
        self::add($mobile, 1311, 35);                                                           //  GSM destinataire
        if ($service == 'REL') {
            self::add($relay_id, 1442, 8);                                                      //  Identifiant relais Pickup
        }
        if ($service == 'PRE') {
            self::add('+', 1568, 1);                                                            //  Flag Predict
        }
        self::add($address_delivery->lastname, 1569, 35);                                       //  Nom de famille du destinataire
        if ($retour) {
            if (in_array($order_id, $retour) && $retour_option != 0) {
                self::add($retour_option, 1834, 1);                                             //  Flag Retour
            }
        }
        self::addLine();
    }
}
