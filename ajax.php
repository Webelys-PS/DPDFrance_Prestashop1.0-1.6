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

require_once(realpath(dirname(__FILE__).'/../../config/config.inc.php'));
require_once(realpath(dirname(__FILE__).'/../../init.php'));
require_once(dirname(__FILE__).'/dpdfrance.php');

$params = array(
    'address1'          => Tools::getValue('address'),
    'postcode'          => Tools::getValue('zipcode'),
    'city'              => Tools::getValue('city'),
    'pudo_id'           => Tools::getValue('pudo_id'),
    'gsm_dest'          => Tools::getValue('gsm_dest'),
    'action'            => Tools::getValue('action'),
    'dpdfrance_cart_id' => Tools::getValue('dpdfrance_cart_id'),
    'dpdfrance_token'   => urlencode(Tools::getValue('dpdfrance_token')),
);

/* Check security token */
if (Tools::encrypt('dpdfrance/ajax')!=Tools::getValue('dpdfrance_token')||!Module::isInstalled('dpdfrance')) {
    die('Bad token');
}

if (Tools::getValue('action_ajax_dpdfrance')) {
    if (Tools::getValue('action_ajax_dpdfrance') == 'ajaxUpdatePoints') {
        $result = Module::getInstanceByName('dpdfrance')->ajaxUpdatePoints($params);
    }
    if (Tools::getValue('action_ajax_dpdfrance') == 'ajaxRegisterGsm') {
        if (version_compare(_PS_VERSION_, '1.4.2.4', '>=')) {
            $result = Tools::jsonEncode(Module::getInstanceByName('dpdfrance')->ajaxRegisterGsm($params));
        } else {
            $result = json_encode(Module::getInstanceByName('dpdfrance')->ajaxRegisterGsm($params));
        }
    }
    if (Tools::getValue('action_ajax_dpdfrance') == 'ajaxRegisterPudo') {
        if (version_compare(_PS_VERSION_, '1.4.2.4', '>=')) {
            $result = Tools::jsonEncode(Module::getInstanceByName('dpdfrance')->ajaxRegisterPudo($params));
        } else {
            $result = json_encode(Module::getInstanceByName('dpdfrance')->ajaxRegisterPudo($params));
        }
    }
}

echo $result;
