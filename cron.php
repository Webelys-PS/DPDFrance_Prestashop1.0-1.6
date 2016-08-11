<?php
/**
 * 2007-2016 PrestaShop
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
 * @copyright 2016 DPD France S.A.S.
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/AdminDPDFrance.php');

class DpdfranceSyncShipmentsCron extends AdminDPDFrance
{
    public function __construct()
    {
        parent::__construct();

        ini_set('max_execution_time', 600);
        ini_set('default_socket_timeout', 5);
        ini_set('display_errors', 'off');

        /* Check security token */
        if (Tools::encrypt('dpdfrance/cron')!=Tools::getValue('token')||!Module::isInstalled('dpdfrance')) {
            die('Bad token');
        }
        /* Check if the requested shop exists */
        $shop_id=0;
        $employee_id=Tools::getValue('employee');
        if (_PS_VERSION_>'1.5') {
            $shops=Db::getInstance()->ExecuteS('SELECT id_shop FROM `'._DB_PREFIX_.'shop`');
            $list_shops=array();
            foreach ($shops as $shop) {
                $list_shops[]=(int) $shop['id_shop'];
            }
            $get_shop=(int) Tools::getValue('shop');
            if ($get_shop != 0 && !in_array($get_shop, $list_shops)) {
                die('Shop not found');
            }
            if ($get_shop != 0) {
                $shop_id = $get_shop;
            }
        }
        $this->syncShipments($shop_id, $employee_id);
    }
}
new DpdfranceSyncShipmentsCron();
