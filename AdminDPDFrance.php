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

class AdminDPDFrance extends AdminTab
{
    private $module = 'dpdfrance';

    public $controller_type;

    public function __construct()
    {
        $this->name = 'dpdfrance';

        if (version_compare(_PS_VERSION_, '1.5.0.0 ', '>=')) {
            $this->multishop_context = Shop::CONTEXT_ALL | Shop::CONTEXT_GROUP | Shop::CONTEXT_SHOP;
            $this->multishop_context_group = Shop::CONTEXT_GROUP;
        }

        parent::__construct();

        /* Backward compatibility */
        if (_PS_VERSION_ < '1.5') {
            require_once(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
        }
        if (_PS_VERSION_ < '1.4') {
            require_once(_PS_MODULE_DIR_.$this->module.'/'.Language::getIsoById((int)Context::getContext()->language->id).'.php');
        }
    }

    public function fetchTemplate($path, $name)
    {
        if (version_compare(_PS_VERSION_, '1.4', '<')) {
            Context::getContext()->smarty->currentTemplate = $name;
        }
        return Context::getContext()->smarty->fetch(dirname(__FILE__).$path.$name.'.tpl');
    }

    /* Converts country ISO code to DPD Station format */
    public static function getIsoCodebyIdCountry($idcountry)
    {
        $sql='
            SELECT `iso_code`
            FROM `'._DB_PREFIX_.'country`
            WHERE `id_country` = \''.pSQL($idcountry).'\'';
        $result=Db::getInstance('_PS_USE_SQL_SLAVE_')->getRow($sql);
        $isops=array('DE', 'AD', 'AT', 'BE', 'BA', 'BG', 'HR', 'DK', 'ES', 'EE', 'FI', 'FR', 'GB', 'GR', 'GG', 'HU', 'IM', 'IE', 'IT', 'JE', 'LV', 'LI', 'LT', 'LU', 'MC', 'NO', 'NL', 'PL', 'PT', 'CZ', 'RO', 'RS', 'SK', 'SI', 'SE', 'CH');
        $isoep=array('D', 'AND', 'A', 'B', 'BA', 'BG', 'CRO', 'DK', 'E', 'EST', 'SF', 'F', 'GB', 'GR', 'GG', 'H', 'IM', 'IRL', 'I', 'JE', 'LET', 'LIE', 'LIT', 'L', 'F', 'N', 'NL', 'PL', 'P', 'CZ', 'RO', 'RS', 'SK', 'SLO', 'S', 'CH');
        if (in_array($result['iso_code'], $isops)) {
            // If the ISO code is in Europe, then convert it to DPD Station format
            $code_iso=str_replace($isops, $isoep, $result['iso_code']);
        } else {
            // If not, then it will be 'INT' (intercontinental)
            $code_iso=str_replace($result['iso_code'], 'INT', $result['iso_code']);
        }
        return $code_iso;
    }

    /* Get all orders but statuses cancelled, delivered, error */
    public static function getAllOrders($id_shop)
    {
        if ($id_shop==0) {
            $id_shop='LIKE "%"';
        } else {
            $id_shop='= '.(int) $id_shop;
        }
        $sql14='    SELECT id_order
                    FROM '._DB_PREFIX_.'orders O
                    WHERE (
                        SELECT id_order_state
                        FROM   '._DB_PREFIX_.'order_history OH
                        WHERE  OH.id_order = O.id_order
                        ORDER  BY date_add DESC, id_order_history DESC
                        LIMIT  1)
                    NOT IN ('.(int) Configuration::get('DPDFRANCE_ETAPE_LIVRE', null, null, (int) $id_shop).',0,5,6,7,8)
                    ORDER BY id_order DESC
                    LIMIT 1000';

        $sql15='    SELECT id_order
                    FROM '._DB_PREFIX_.'orders O
                    WHERE `current_state` NOT IN('.(int) Configuration::get('DPDFRANCE_ETAPE_LIVRE', null, null, (int) $id_shop).',0,5,6,7,8) AND O.id_shop '.$id_shop.'
                    ORDER BY id_order DESC
                    LIMIT 1000';

        if (_PS_VERSION_<'1.5') {
            $result=Db::getInstance()->ExecuteS($sql14);
        } else {
            $result=Db::getInstance()->ExecuteS($sql15);
        }
        $orders=array();
        if (!empty($result)) {
            foreach ($result as $order) {
                $orders[]=(int) $order['id_order'];
            }
        }
        return $orders;
    }

    /* Formats GSM numbers */
    public static function formatGSM($tel_dest, $code_pays_dest)
    {
        $tel_dest=str_replace(array(' ', '.', '-', ',', ';', '/', '\\', '(', ')'), '', $tel_dest);
        // Chrome autofill fix
        if (Tools::substr($tel_dest, 0, 2)==33) {
            $tel_dest=substr_replace($tel_dest, '0', 0, 2);
        }
        switch ($code_pays_dest) {
            case 'F':
                if (preg_match('/^((\+33|0)[67])(?:[ _.-]?(\d{2})){4}$/', $tel_dest)) {
                    return $tel_dest;
                } else {
                    return false;
                }
                break;

            case 'D':
                if (preg_match('/^(\+|00)49(15|16|17)(\s?\d{8,9})$/', $tel_dest)) {
                    return $tel_dest;
                } else {
                    return false;
                }
                break;

            case 'B':
                if (preg_match('/^(\+|00)324([56789])(\s?\d{7})$/', $tel_dest)) {
                    return $tel_dest;
                } else {
                    return false;
                }
                break;

            case 'AT':
                if (preg_match('/^(\+|00)436([56789])(\s?\d{4,10})$/', $tel_dest)) {
                    return $tel_dest;
                } else {
                    return false;
                }
                break;

            case 'GB':
                if (preg_match('/^(\+|00)447([3456789])(\s?\d{7})$/', $tel_dest)) {
                    return $tel_dest;
                } else {
                    return false;
                }
                break;

            case 'NL':
                if (preg_match('/^(\+|00)316(\s?\d{8})$/', $tel_dest)) {
                    return $tel_dest;
                } else {
                    return false;
                }
                break;

            case 'P':
                if (preg_match('/^(\+|00)3519(\s?\d{7})$/', $tel_dest)) {
                    return $tel_dest;
                } else {
                    return false;
                }
                break;

            case 'IRL':
                if (preg_match('/^(\+|00)3538(\s?\d{8})$/', $tel_dest)) {
                    return $tel_dest;
                } else {
                    return false;
                }
                break;

            case 'E':
                if (preg_match('/^(\+|00)34(6|7)(\s?\d{8})$/', $tel_dest)) {
                    return $tel_dest;
                } else {
                    return false;
                }
                break;

            case 'I':
                if (preg_match('/^(\+|00)393(\s?\d{9})$/', $tel_dest)) {
                    return $tel_dest;
                } else {
                    return false;
                }
                break;

            case 'CH':
                if (preg_match('/^(\+|00)417([56789])(\s?\d{7})$/', $tel_dest)) {
                    return $tel_dest;
                } else {
                    return false;
                }
                break;

            default:
                return $tel_dest;
                break;
        }
    }

    /* Get delivery service for a cart ID & checks if id_carrier matches */
    public static function getService($order, $lang_id)
    {
        $sql=Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'dpdfrance_shipping` WHERE `id_cart` = '.(int) $order->id_cart.' AND `id_carrier` = '.(int) $order->id_carrier);
        $service=$sql['service'];
        // Service override, forcing Relais or Predict shipment on eligible orders
        if (!$service) {
            $address_invoice=new Address($order->id_address_invoice, (int) $lang_id);
            $address_delivery=new Address($order->id_address_delivery, (int) $lang_id);
            $code_pays_dest=self::getIsoCodebyIdCountry((int) $address_delivery->id_country);
            $tel_dest=(($address_delivery->phone_mobile)?$address_delivery->phone_mobile:(($address_invoice->phone_mobile)?$address_invoice->phone_mobile:(($address_delivery->phone)?$address_delivery->phone:(($address_invoice->phone)?$address_invoice->phone:''))));
            $mobile=self::formatGSM($tel_dest, $code_pays_dest);
            if (preg_match('/P\d{5}/i', $address_delivery->company)) {
                $service='REL';
            } elseif ($mobile&&$code_pays_dest!='INT'&&$order->id_carrier!=Configuration::get('DPDFRANCE_CLASSIC_CARRIER_ID', null, null, (int) $order->id_shop)) {
                $service='PRE';
            }
        }
        return $service;
    }

    /* Sync order status with parcel status, adds tracking number */
    public function syncShipments($id_employee, $force)
    {
        /* Check if last tracking call is more than 1h old */
        if ((time() - (int)Configuration::get('DPDFRANCE_LAST_TRACKING') < 3600) && $force == 0) {
            die('DPD France parcel tracking update is done once every hour. - Last update on : '.date('d/m/Y - H:i:s', Configuration::get('DPDFRANCE_LAST_TRACKING')));
        }
        Configuration::updateValue('DPDFRANCE_LAST_TRACKING', time());

        $predict_carrier_log = $classic_carrier_log = $relais_carrier_log = $predict_carrier_sql = $classic_carrier_sql = $relais_carrier_sql = '';

        if (Configuration::get('DPDFRANCE_MARKETPLACE_MODE')) {
            $europe_carrier_sql = 'CA.name LIKE \'%%\'';
        } else {
            $europe_carrier_sql = 'CA.name LIKE \'%DPD%\'';
        }

        if (version_compare(_PS_VERSION_, '1.5.0.0', '>=') && Shop::isFeatureActive()) {
            foreach (Shop::getShops(true) as $shop) {
                if (Configuration::get('DPDFRANCE_PREDICT_CARRIER_ID', null, null, $shop['id_shop'])) {
                    $predict_carrier_log.=Configuration::get('DPDFRANCE_PREDICT_CARRIER_ID', null, null, $shop['id_shop']).','.implode(',', array_map('intval', explode('|', Tools::substr(Configuration::get('DPDFRANCE_PREDICT_CARRIER_LOG', null, null, $shop['id_shop']), 1))));
                }
                if (Configuration::get('DPDFRANCE_CLASSIC_CARRIER_ID', null, null, $shop['id_shop'])) {
                    $classic_carrier_log.=Configuration::get('DPDFRANCE_CLASSIC_CARRIER_ID', null, null, $shop['id_shop']).','.implode(',', array_map('intval', explode('|', Tools::substr(Configuration::get('DPDFRANCE_CLASSIC_CARRIER_LOG', null, null, $shop['id_shop']), 1))));
                }
                if (Configuration::get('DPDFRANCE_RELAIS_CARRIER_ID', null, null, $shop['id_shop'])) {
                    $relais_carrier_log.=Configuration::get('DPDFRANCE_RELAIS_CARRIER_ID', null, null, $shop['id_shop']).','.implode(',', array_map('intval', explode('|', Tools::substr(Configuration::get('DPDFRANCE_RELAIS_CARRIER_LOG', null, null, $shop['id_shop']), 1))));
                }
            }
        }
        if (Configuration::get('DPDFRANCE_PREDICT_CARRIER_ID', null, null, null)) {
            $predict_carrier_log.=','.Configuration::get('DPDFRANCE_PREDICT_CARRIER_ID', null, null, null).','.implode(',', array_map('intval', explode('|', Tools::substr(Configuration::get('DPDFRANCE_PREDICT_CARRIER_LOG', null, null, null), 1))));
            $predict_carrier_sql = 'CA.id_carrier IN ('.implode(',', array_unique(explode(',', $predict_carrier_log))).') OR ';
        }
        if (Configuration::get('DPDFRANCE_CLASSIC_CARRIER_ID', null, null, null)) {
            $classic_carrier_log.=','.Configuration::get('DPDFRANCE_CLASSIC_CARRIER_ID', null, null, null).','.implode(',', array_map('intval', explode('|', Tools::substr(Configuration::get('DPDFRANCE_CLASSIC_CARRIER_LOG', null, null, null), 1))));
            $classic_carrier_sql = 'CA.id_carrier IN ('.implode(',', array_unique(explode(',', $classic_carrier_log))).') OR ';
        }
        if (Configuration::get('DPDFRANCE_RELAIS_CARRIER_ID', null, null, null)) {
            $relais_carrier_log.=','.Configuration::get('DPDFRANCE_RELAIS_CARRIER_ID', null, null, null).','.implode(',', array_map('intval', explode('|', Tools::substr(Configuration::get('DPDFRANCE_RELAIS_CARRIER_LOG', null, null, null), 1))));
            $relais_carrier_sql = 'CA.id_carrier IN ('.implode(',', array_unique(explode(',', $relais_carrier_log))).') OR ';
        }

        $sql14='SELECT  O.id_order as reference, O.id_carrier as id_carrier, O.id_order as id_order, O.shipping_number as shipping_number
                FROM    '._DB_PREFIX_.'orders AS O, '._DB_PREFIX_.'carrier AS CA
                WHERE   (SELECT id_order_state
                        FROM      '._DB_PREFIX_.'order_history OH
                        WHERE    OH.id_order = O.id_order
                        ORDER BY  date_add DESC, id_order_history DESC
                        LIMIT    1)
                NOT IN  ('.(int) Configuration::get('DPDFRANCE_ETAPE_LIVRE').',0,5,6,7,8) AND
                        CA.id_carrier=O.id_carrier AND
                        ('.$predict_carrier_sql.$classic_carrier_sql.$relais_carrier_sql.$europe_carrier_sql.')
                ORDER BY id_order DESC
                LIMIT 1000';

        $sql15='SELECT  O.reference as reference, O.id_carrier as id_carrier, O.id_order as id_order, O.shipping_number as shipping_number, O.id_shop as id_shop
                FROM    '._DB_PREFIX_.'orders AS O, '._DB_PREFIX_.'carrier AS CA
                WHERE   CA.id_carrier=O.id_carrier AND O.current_state
                NOT IN  ('.(int) Configuration::get('DPDFRANCE_ETAPE_LIVRE').',0,5,6,7,8) AND
                        ('.$predict_carrier_sql.$classic_carrier_sql.$relais_carrier_sql.$europe_carrier_sql.')
                ORDER BY id_order DESC
                LIMIT 1000';

        if (_PS_VERSION_<'1.5') {
            $orderlist=Db::getInstance()->ExecuteS($sql14);
        } else {
            $orderlist=Db::getInstance()->ExecuteS($sql15);
        }

        if (!empty($orderlist)) {
            echo $this->l('DPD France - Sync started').'<br/>';
            foreach ($orderlist as $orderinfos) {
                $statuslist=array();
                if (Validate::isLoadedObject($order = new Order($orderinfos['id_order']))) {
                    if (_PS_VERSION_ < '1.5') {
                        $internalref = $order->id;
                        $order->id_shop = '';
                    } else {
                        $internalref = $order->reference;
                    }
                    // Check past order states
                    $past_states = 0;
                    $orderhistory = $order->getHistory($order->id_lang);
                    foreach ($orderhistory as $state) {
                        if ($state['id_order_state'] == (int)Configuration::get('DPDFRANCE_ETAPE_EXPEDIEE', null, null, (int)$order->id_shop)) {
                            $past_states = 1;
                        } else {
                            if ($state['id_order_state'] == (int)Configuration::get('DPDFRANCE_ETAPE_LIVRE', null, null, (int)$order->id_shop)) {
                                $past_states = 2;
                                break;
                            }
                        }
                    }

                    // Exclude already delivered orders from sync
                    if ($past_states == 2) {
                        continue;
                    }

                    // Retrieve DPD service
                    $service=self::getService($order, Context::getContext()->language->id);
                    switch ($service) {
                        case 'PRE':
                            $compte_chargeur=Configuration::get('DPDFRANCE_PREDICT_SHIPPER_CODE', null, null, (int) $order->id_shop);
                            $depot_code=Configuration::get('DPDFRANCE_PREDICT_DEPOT_CODE', null, null, (int) $order->id_shop);
                            break;
                        case 'REL':
                            $compte_chargeur=Configuration::get('DPDFRANCE_RELAIS_SHIPPER_CODE', null, null, (int) $order->id_shop);
                            $depot_code=Configuration::get('DPDFRANCE_RELAIS_DEPOT_CODE', null, null, (int) $order->id_shop);
                            break;
                        default:
                            $compte_chargeur=Configuration::get('DPDFRANCE_CLASSIC_SHIPPER_CODE', null, null, (int) $order->id_shop);
                            $depot_code=Configuration::get('DPDFRANCE_CLASSIC_DEPOT_CODE', null, null, (int) $order->id_shop);
                            break;
                    }
                    if (!$compte_chargeur || !$depot_code) {
                        continue;
                    }

                    $variables=array(   'customer_center'=>'3',
                                        'customer'=>'1064',
                                        'password'=>'Pr2%5sHg',
                                        'reference'=>$internalref,
                                        'shipping_date'=>'',
                                        'shipping_customer_center'=>$depot_code,
                                        'shipping_customer'=>$compte_chargeur,
                                        'searchmode'=>'SearchMode_Equals',
                                        'language'=>'F',
                                    );
                    $serviceurl='http://webtrace.dpd.fr/dpd-webservices/webtrace_service.asmx?WSDL';

                    // Call WS for traces by reference
                    try {
                        $client=new SoapClient($serviceurl, array('connection_timeout'=>5, 'exceptions'=>true));
                        $response=$client->getShipmentTraceByReferenceGlobalWithCenterAsArray($variables);
                        $result=$response->getShipmentTraceByReferenceGlobalWithCenterAsArrayResult->clsShipmentTrace;

                        if (!empty($result->LastError)) {
                            echo $this->l('Order').' '.$internalref.' - '.$this->l('Error').' : '.$result->LastError.'<br/>';
                        } else {
                            // Only one parcel per reference
                            if (!is_array($result)) {
                                $traces=$result->Traces->clsTrace;
                                $returned_ref=$result->Reference;
                                if ($internalref == $returned_ref) {
                                    // Parcels with only one status
                                    if (!is_array($traces)) {
                                        // Exclude CEDI-only parcels
                                        if ($traces->StatusNumber != 8) {
                                            $statuslist[$result->ShipmentNumber][]=$traces->StatusNumber;
                                        }
                                    } else {
                                        // Parcel with multiple statuses
                                        foreach ($traces as $status) {
                                            $statuslist[$result->ShipmentNumber][]=$status->StatusNumber;
                                        }
                                    }
                                }
                            } else {
                                // Multiple parcels per reference
                                foreach ($result as $shipment) {
                                    $returned_ref=$shipment->Reference;
                                    if ($internalref == $returned_ref) {
                                        $variables2=array(  'customer_center'=>'3',
                                                            'customer'=>'1064',
                                                            'password'=>'Pr2%5sHg',
                                                            'shipmentnumber'=>$shipment->ShipmentNumber
                                                        );
                                        $response2=$client->getShipmentTrace($variables2);
                                        $traces=$response2->getShipmentTraceResult->Traces->clsTrace;
                                        // Parcels with only one status
                                        if (!is_array($traces)) {
                                            // Exclude CEDI-only parcels
                                            if ($traces->StatusNumber == 8) {
                                                continue;
                                            }
                                            $statuslist[$shipment->ShipmentNumber][]=$traces->StatusNumber;
                                        } else {
                                            // Parcel with multiple statuses
                                            foreach ($traces as $status) {
                                                $statuslist[$shipment->ShipmentNumber][]=$status->StatusNumber;
                                            }
                                        }
                                    }
                                    break; // Stop at first parcel
                                }
                            }

                            if (!empty($statuslist)) {
                                // Check delivery state
                                $tracking_number = (key($statuslist));
                                $delivery_state = 0;
                                foreach ($statuslist as $events) {
                                    // Check if en-route event has been applied
                                    if (array_intersect(array(10, 28, 44, 89), $events)) {
                                        $delivery_state = 1;
                                    }
                                    // Check if delivered event has been applied
                                    if (array_intersect(array(40, 400), $events)) {
                                        $delivery_state = 2;
                                    }
                                }

                                // Add tracking number if empty
                                if (!$order->shipping_number && $delivery_state != 0) {
                                    if (Configuration::get('DPDFRANCE_AUTO_UPDATE') == 2) {
                                        $url = 'http://www.dpd.fr/traces_'.$tracking_number;
                                        $order->shipping_number=$tracking_number;
                                    } else {
                                        $url = 'http://www.dpd.fr/tracex_'.$internalref.'_'.$depot_code.$compte_chargeur;
                                        $order->shipping_number=$internalref.'_'.$depot_code.$compte_chargeur;
                                    }
                                    Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'orders SET shipping_number = "' . pSQL($order->shipping_number) . '" WHERE id_order = "' . (int)$order->id . '"');
                                    if (_PS_VERSION_ >= '1.5') {
                                        Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'order_carrier SET tracking_number = "' . pSQL($order->shipping_number) . '" WHERE id_order = "' . (int)$order->id . '"');
                                    }
                                    $order->update();
                                    echo $this->l('Order') . ' ' . $internalref . ' - ' . $this->l('Tracking number') . ' ' . $tracking_number . ' ' . $this->l('added') . '<br/>';
                                }

                                // Update to delivered status only if parcel is delivered and there is no previous delivered status applied to that order
                                if ($delivery_state == 2 && $past_states != 2) {
                                    $history = new OrderHistory();
                                    $history->id_order = (int)$order->id;
                                    $history->id_employee = (int)$id_employee;
                                    $history->id_order_state = (int)Configuration::get('DPDFRANCE_ETAPE_LIVRE', null, null, (int)$order->id_shop);
                                    $history->changeIdOrderState((int)Configuration::get('DPDFRANCE_ETAPE_LIVRE', null, null, (int)$order->id_shop), $order->id);
                                    $history->addWithemail();
                                    echo $this->l('Order') . ' ' . $internalref . ' - ' . $this->l('Parcel') . ' ' . $tracking_number . ' ' . $this->l('is delivered') . '<br/>';
                                } else {
                                    // Update to shipped status only if parcel is en route and there are no previous shipped or delivered status applied to that order
                                    if ($delivery_state == 1 && $past_states == 0) {
                                        $customer = new Customer((int)$order->id_customer);
                                        $history = new OrderHistory();
                                        $history->id_order = (int)$order->id;
                                        $history->id_employee = (int)$id_employee;
                                        $history->id_order_state = (int)Configuration::get('DPDFRANCE_ETAPE_EXPEDIEE', null, null, (int)$order->id_shop);
                                        $history->changeIdOrderState((int)Configuration::get('DPDFRANCE_ETAPE_EXPEDIEE', null, null, (int)$order->id_shop), $order->id);
                                        if (_PS_VERSION_ < '1.5') {
                                            $template_vars = array('{followup}' => $url, '{firstname}' => $customer->firstname, '{lastname}' => $customer->lastname, '{id_order}' => (int)$order->id);
                                        } else {
                                            $template_vars = array('{followup}' => $url, '{firstname}' => $customer->firstname, '{lastname}' => $customer->lastname, '{order_name}' => $internalref, '{id_order}' => (int)$order->id);
                                        }
                                        switch (Language::getIsoById((int)$order->id_lang)) {
                                            case 'fr':
                                                $subject = 'Votre commande sera livrée par DPD';
                                                break;
                                            case 'en':
                                                $subject = 'Your parcel will be delivered by DPD';
                                                break;
                                            case 'es':
                                                $subject = 'Su pedido será enviado por DPD';
                                                break;
                                            case 'it':
                                                $subject = 'Il vostro pacchetto sará trasportato da DPD';
                                                break;
                                            case 'de':
                                                $subject = 'Ihre Bestellung wird per DPD geliefert werden';
                                                break;
                                        }
                                        $history->addWithemail(true, $template_vars);
                                        Mail::Send((int)$order->id_lang, 'in_transit', $subject, $template_vars, $customer->email, $customer->firstname . ' ' . $customer->lastname);
                                        echo $this->l('Order') . ' ' . $internalref . ' - ' . $this->l('Parcel') . ' ' . $tracking_number . ' ' . $this->l('is handled by DPD') . '<br/>';
                                    } else {
                                        echo $this->l('Order') . ' ' . $internalref . ' - ' . $this->l('No update for parcel') . ' ' . $tracking_number . '<br/>';
                                    }
                                }
                            } else {
                                echo $this->l('Order') . ' ' . $internalref . ' - ' . $this->l('Parcel') . ' ' . $this->l('is found, not yet handled by DPD') . '<br/>';
                            }
                        }
                    } catch (SoapFault $e) {
                        echo $this->l('Order').' '.$internalref.' - '.$this->l('Error').' : '.$e->getMessage().'<br/>';
                        continue;
                    }
                }
            }
            echo $this->l('DPD France - Sync complete.');
        } else {
            echo $this->l('DPD France - No orders to update.');
        }
    }

    /* Get eligible orders and builds up display */
    public function display()
    {
        // RSS stream
        $stream = array();
        if (_PS_VERSION_<'1.4') {
            $rss=@simplexml_load_string(file_get_contents('http://www.dpd.fr/extensions/rss/flux_info_dpdfr.xml'));
        } else {
            $rss=@simplexml_load_string(Tools::file_get_contents('http://www.dpd.fr/extensions/rss/flux_info_dpdfr.xml'));
        }
        if (!empty($rss)) {
            if (empty($rss->channel->item)) {
                $stream['error'] = true;
            } else {
                $i=0;
                foreach ($rss->channel->item as $item) {
                    $stream[$i]=array(  'category'=>(string) $item->category,
                                        'title'=>(string) $item->title,
                                        'description'=>(string) $item->description,
                                        'date'=>strtotime((string) $item->pubDate)
                                    );
                    if (strtotime("-30 day", strtotime(date('d-m-Y')))>$stream[$i]['date']) {
                        unset($stream[$i]);
                    }
                    $i++;
                }
            }
            if (empty($stream)) {
                $stream['error'] = true;
            }
        } else {
            $stream['error'] = true;
        }

        // Update delivered orders
        if (Tools::getIsset('updateDeliveredOrders')) {
            if (Tools::getIsset('checkbox')) {
                $orders=Tools::getValue('checkbox');
                if (is_string($orders)) {
                    $orders = explode(',', $orders);
                }
                if (!empty($orders)) {
                    $sql='SELECT    O.`id_order` AS id_order
                          FROM      '._DB_PREFIX_.'orders AS O,
                                    '._DB_PREFIX_.'carrier AS CA
                          WHERE     CA.id_carrier=O.id_carrier AND
                                    id_order IN ('.implode(',', array_map('intval', $orders)).')';
                    $orderlist=Db::getInstance()->ExecuteS($sql);
                    if (!empty($orderlist)) {
                        // Check if there are DPD orders
                        foreach ($orderlist as $orders) {
                            $id_order=$orders['id_order'];
                            if (Validate::isLoadedObject($order = new Order($id_order))) {
                                if (_PS_VERSION_<'1.5') {
                                    $order->id_shop='';
                                }
                                $history=new OrderHistory();
                                $history->id_order=(int) $id_order;
                                $history->id_order_state=(int) Configuration::get('DPDFRANCE_ETAPE_LIVRE', null, null, (int) $order->id_shop);
                                $history->changeIdOrderState((int) Configuration::get('DPDFRANCE_ETAPE_LIVRE', null, null, (int) $order->id_shop), $id_order);
                                $history->id_employee=(int) Context::getContext()->employee->id;
                                $history->addWithemail();
                            }
                        }
                        echo '<div class="okmsg">'.$this->l('Delivered orders statuses were updated').'</div>';
                    } else {
                        echo '<div class="warnmsg">'.$this->l('No DPD trackings to generate.').'</div>';
                    }
                } else {
                    echo '<div class="warnmsg">'.$this->l('No order selected.').'</div>';
                }
            } else {
                    echo '<div class="warnmsg">'.$this->l('No order selected.').'</div>';
            }
        }

        // Update shipped orders
        if (Tools::getIsset('updateShippedOrders')) {
            if (Tools::getIsset('checkbox')) {
                $orders = Tools::getValue('checkbox');
                if (is_string($orders)) {
                    $orders = explode(',', $orders);
                }
                $sql = 'SELECT  O.`id_order` AS id_order
                        FROM    '._DB_PREFIX_.'orders AS O, 
                                '._DB_PREFIX_.'carrier AS CA 
                        WHERE   CA.id_carrier=O.id_carrier AND 
                                id_order IN ('.implode(',', array_map('intval', $orders)).')';

                $orderlist = Db::getInstance()->ExecuteS($sql);

                // Check if there are DPD orders
                if (!empty($orderlist)) {
                    foreach ($orderlist as $orders) {
                        $id_order = $orders['id_order'];
                        if (Validate::isLoadedObject($order = new Order($id_order))) {
                            if (_PS_VERSION_ < '1.5') {
                                $internalref = $order->id;
                                $order->id_shop = '';
                            } else {
                                $internalref = $order->reference;
                            }
                            $service=self::getService($order, Context::getContext()->language->id);
                            switch ($service) {
                                case 'PRE':
                                    $compte_chargeur = Configuration::get('DPDFRANCE_PREDICT_SHIPPER_CODE', null, null, (int)$order->id_shop);
                                    $depot_code = Configuration::get('DPDFRANCE_PREDICT_DEPOT_CODE', null, null, (int)$order->id_shop);
                                    break;
                                case 'REL':
                                    $compte_chargeur = Configuration::get('DPDFRANCE_RELAIS_SHIPPER_CODE', null, null, (int)$order->id_shop);
                                    $depot_code = Configuration::get('DPDFRANCE_RELAIS_DEPOT_CODE', null, null, (int)$order->id_shop);
                                    break;
                                default:
                                    $compte_chargeur = Configuration::get('DPDFRANCE_CLASSIC_SHIPPER_CODE', null, null, (int)$order->id_shop);
                                    $depot_code = Configuration::get('DPDFRANCE_CLASSIC_DEPOT_CODE', null, null, (int)$order->id_shop);
                                    break;
                            }

                            $customer = new Customer((int)$order->id_customer);
                            if (Configuration::get('DPDFRANCE_AUTO_UPDATE') != 2) {
                                $order->shipping_number = $internalref.'_'.$depot_code.$compte_chargeur;
                                Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'orders SET shipping_number = "'.pSQL($order->shipping_number).'" WHERE id_order = "'.$id_order.'"');
                                if (_PS_VERSION_ >= '1.5') {
                                    Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'order_carrier SET tracking_number = "'.pSQL($order->shipping_number).'" WHERE id_order = "'.$id_order.'"');
                                }
                                $order->update();
                            }
                            $history = new OrderHistory();
                            $history->id_order = (int)$id_order;
                            $history->changeIdOrderState(Configuration::get('DPDFRANCE_ETAPE_EXPEDIEE', null, null, (int)$order->id_shop), $id_order);
                            $history->id_employee = (int)Context::getContext()->employee->id;
                            $carrier = new Carrier((int)$order->id_carrier, (int)Context::getContext()->language->id);
                            $url = 'http://www.dpd.fr/tracex_'.$internalref.'_'.$depot_code.$compte_chargeur;
                            if (_PS_VERSION_ < '1.5') {
                                $template_vars = array('{followup}' => $url, '{firstname}' => $customer->firstname, '{lastname}' => $customer->lastname, '{id_order}' => (int)$order->id);
                            } else {
                                $template_vars = array('{followup}' => $url, '{firstname}' => $customer->firstname, '{lastname}' => $customer->lastname, '{order_name}' => $internalref, '{id_order}' => (int)$order->id);
                            }
                            switch (Language::getIsoById((int)$order->id_lang)) {
                                case 'fr':
                                    $subject = 'Votre commande sera livrée par DPD';
                                    break;
                                case 'en':
                                    $subject = 'Your parcel will be delivered by DPD';
                                    break;
                                case 'es':
                                    $subject = 'Su pedido será enviado por DPD';
                                    break;
                                case 'it':
                                    $subject = 'Il vostro pacchetto sará trasportato da DPD';
                                    break;
                                case 'de':
                                    $subject = 'Ihre Bestellung wird per DPD geliefert werden';
                                    break;
                            }
                            if (!$history->addWithemail(true, $template_vars)) {
                                $this->_errors[] = Tools::displayError('an error occurred while changing status or was unable to send e-mail to the customer');
                            }
                            if (!Validate::isLoadedObject($customer) || !Validate::isLoadedObject($carrier)) {
                                die(Tools::displayError());
                            }
                            Mail::Send((int)$order->id_lang, 'in_transit', $subject, $template_vars, $customer->email, $customer->firstname.' '.$customer->lastname);
                        }
                    }
                    echo '<div class="okmsg">'.$this->l('Shipped orders statuses were updated and tracking numbers added.').'</div>';
                } else {
                    echo '<div class="warnmsg">'.$this->l('No trackings to generate.').'</div>';
                }
            } else {
                echo '<div class="warnmsg">'.$this->l('No order selected.').'</div>';
            }
        }

        // Export selected orders
        if (Tools::getIsset('exportOrders')) {
            $fieldlist = array('O.`id_order`', 'AD.`lastname`', 'AD.`firstname`', 'AD.`postcode`', 'AD.`city`', 'CL.`iso_code`', 'C.`email`');
            if (Tools::getIsset('checkbox')) {
                $orders = Tools::getValue('checkbox');
                if (is_string($orders)) {
                    $orders = explode(',', $orders);
                }
                $liste_expeditions = 'O.id_order IN ('.implode(',', array_map('intval', $orders)).')';

                if (!empty($orders)) {
                    $sql = 'SELECT  '.implode(', ', $fieldlist).'
                            FROM    '._DB_PREFIX_.'orders AS O, 
                                    '._DB_PREFIX_.'carrier AS CA, 
                                    '._DB_PREFIX_.'customer AS C, 
                                    '._DB_PREFIX_.'address AS AD, 
                                    '._DB_PREFIX_.'country AS CL
                            WHERE   O.id_address_delivery=AD.id_address AND
                                    C.id_customer=O.id_customer AND 
                                    CL.id_country=AD.id_country AND 
                                    CA.id_carrier=O.id_carrier AND 
                                    ('.$liste_expeditions.')
                            ORDER BY id_order DESC';

                    $orderlist = Db::getInstance()->ExecuteS($sql);

                    if (!empty($orderlist)) {
                        // Labelling tool interface file creation
                        require_once(_PS_MODULE_DIR_.'dpdfrance/classes/admin/DPDStation.php');
                        $record=new DPDStation();

                        foreach ($orderlist as $order_var) {
                            // Shipper information retrieval
                            $order              = new Order($order_var['id_order']);
                            $nom_exp            = Configuration::get('DPDFRANCE_NOM_EXP', null, null, (int)$order->id_shop);            // Raison sociale expéditeur
                            $address_exp        = Configuration::get('DPDFRANCE_ADDRESS_EXP', null, null, (int)$order->id_shop);        // Adresse
                            $address2_exp       = Configuration::get('DPDFRANCE_ADDRESS2_EXP', null, null, (int)$order->id_shop);       // Complément d'adresse
                            $cp_exp             = Configuration::get('DPDFRANCE_CP_EXP', null, null, (int)$order->id_shop);             // Code postal
                            $ville_exp          = Configuration::get('DPDFRANCE_VILLE_EXP', null, null, (int)$order->id_shop);          // Ville
                            $code_pays_exp      = 'F';                                                                                  // Code pays
                            $tel_exp            = Configuration::get('DPDFRANCE_TEL_EXP', null, null, (int)$order->id_shop);            // Téléphone
                            $email_exp          = Configuration::get('DPDFRANCE_EMAIL_EXP', null, null, (int)$order->id_shop);          // E-mail
                            $gsm_exp            = Configuration::get('DPDFRANCE_GSM_EXP', null, null, (int)$order->id_shop);            // N° GSM

                            if (_PS_VERSION_ < '1.5') {
                                $internalref = $order->id;
                            } else {
                                $internalref = $order->reference;
                            }
                            $customer           = new Customer($order->id_customer);
                            $address_invoice    = new Address($order->id_address_invoice, (int)Context::getContext()->language->id);
                            $address_delivery   = new Address($order->id_address_delivery, (int)Context::getContext()->language->id);
                            $code_pays_dest     = self::getIsoCodebyIdCountry((int)$address_delivery->id_country);

                            // Ireland override
                            if ($code_pays_dest == 'IRL') {
                                if (stripos($address_delivery->city, 'Dublin') !== false) {
                                    $address_delivery->postcode = 1;
                                } else {
                                    $address_delivery->postcode = 2;
                                }
                            }

                            $instr_liv_cleaned  = str_replace(array("\r\n", "\n", "\r", "\t"), ' ', $address_delivery->other);
                            $service            = self::getService($order, Context::getContext()->language->id);
                            $relay_id           = '';
                            preg_match('/P\d{5}/i', $address_delivery->company, $matches, PREG_OFFSET_CAPTURE);
                            if ($matches) {
                                $relay_id=$matches[0][0];
                            }
                            $tel_dest           = Db::getInstance()->getValue('SELECT gsm_dest FROM '._DB_PREFIX_.'dpdfrance_shipping WHERE id_cart ="'.$order->id_cart.'"');
                            if ($tel_dest == '') {
                                $tel_dest = (($address_delivery->phone_mobile) ? $address_delivery->phone_mobile : (($address_invoice->phone_mobile) ? $address_invoice->phone_mobile : (($address_delivery->phone) ? $address_delivery->phone : (($address_invoice->phone) ? $address_invoice->phone : ''))));
                            }
                            $mobile = self::formatGSM($tel_dest, $code_pays_dest);
                            $poids_all = Tools::getValue('parcelweight');
                            if (Tools::strtolower(Configuration::get('PS_WEIGHT_UNIT', null, null, (int) $order->id_shop))=='kg') {
                                $poids=(int)($poids_all[$order->id]*100);
                            }
                            if (Tools::strtolower(Configuration::get('PS_WEIGHT_UNIT', null, null, (int) $order->id_shop))=='g') {
                                $poids=(int)($poids_all[$order->id]*0.1);
                            }
                            $retour_option=(int)Configuration::get('DPDFRANCE_RETOUR_OPTION', null, null, (int)$order->id_shop); /* 2: Inverse, 3: Sur demande, 4: Préparée */
                            switch ($service) {
                                case 'PRE':
                                    $compte_chargeur = Configuration::get('DPDFRANCE_PREDICT_SHIPPER_CODE', null, null, (int)$order->id_shop);
                                    break;
                                case 'REL':
                                    $compte_chargeur = Configuration::get('DPDFRANCE_RELAIS_SHIPPER_CODE', null, null, (int)$order->id_shop);
                                    break;
                                default:
                                    $compte_chargeur = Configuration::get('DPDFRANCE_CLASSIC_SHIPPER_CODE', null, null, (int)$order->id_shop);
                                    break;
                            }
                            
                            // Add shipment data to file
                            $record->formatRow($internalref, $order->id, $service, Tools::getValue('advalorem'), $order->total_paid, Tools::getValue('retour'), $retour_option, $poids, $address_delivery, $code_pays_dest, $mobile, $tel_dest, $relay_id, $customer->email, $nom_exp, $address2_exp, $cp_exp, $ville_exp, $address_exp, $code_pays_exp, $tel_exp, $instr_liv_cleaned, $compte_chargeur, $email_exp, $gsm_exp);
                        }
                        $record->download();
                    } else {
                        echo '<div class="warnmsg">'.$this->l('No orders to export.').'</div>';
                    }
                } else {
                    echo '<div class="warnmsg">'.$this->l('No orders to export.').'</div>';
                }
            } else {
                echo '<div class="warnmsg">'.$this->l('No order selected.').'</div>';
            }
        }

        // Display section
        // Error message if shipper info is missing
        if ((Configuration::get('DPDFRANCE_PARAM') == 0)) {
            echo '<div class="warnmsg">'.$this->l('Warning! Your DPD Depot code and contract number are missing. You must configure the DPD module in order to use the export and tracking features.').'</div>';
            exit;
        }
        // Add jQuery for Prestashop before 1.4
        if (_PS_VERSION_ < '1.4') {
            echo '<script type="text/javascript" src="../modules/'.$this->name.'/views/js/admin/jquery/jquery-1.11.0.min.js"></script>';
        }
        // Calls function to get orders
        $order_info = array();
        $statuses_array = array();
        $statuses = OrderState::getOrderStates((int)Context::getContext()->language->id);

        foreach ($statuses as $status) {
            $statuses_array[$status['id_order_state']] = $status['name'];
        }
        $fieldlist = array('O.`id_order`', 'O.`id_cart`', 'AD.`lastname`', 'AD.`firstname`', 'AD.`postcode`', 'AD.`city`', 'CL.`iso_code`', 'C.`email`', 'CA.`name`');

        $current_shop = (int)Tools::substr(Context::getContext()->cookie->shopContext, 2);
        $orders = self::getAllOrders($current_shop);
        $liste_expeditions = 'O.id_order IN ('.implode(',', $orders).')';

        $predict_carrier_log = $classic_carrier_log = $relais_carrier_log = $predict_carrier_sql = $classic_carrier_sql = $relais_carrier_sql = '';

        if (Configuration::get('DPDFRANCE_MARKETPLACE_MODE')) {
            $europe_carrier_sql = 'CA.name LIKE \'%%\'';
        } else {
            $europe_carrier_sql = 'CA.name LIKE \'%DPD%\'';
        }

        if (version_compare(_PS_VERSION_, '1.5.0.0', '>=') && $current_shop == 0 && Shop::isFeatureActive()) {
            if (Configuration::get('DPDFRANCE_PREDICT_CARRIER_ID', null, null, null)) {
                $predict_carrier_log=Configuration::get('DPDFRANCE_PREDICT_CARRIER_ID', null, null, null).','.implode(',', array_map('intval', explode('|', Tools::substr(Configuration::get('DPDFRANCE_PREDICT_CARRIER_LOG', null, null, null), 1))));
            }
            if (Configuration::get('DPDFRANCE_CLASSIC_CARRIER_ID', null, null, null)) {
                $classic_carrier_log=Configuration::get('DPDFRANCE_CLASSIC_CARRIER_ID', null, null, null).','.implode(',', array_map('intval', explode('|', Tools::substr(Configuration::get('DPDFRANCE_CLASSIC_CARRIER_LOG', null, null, null), 1))));
            }
            if (Configuration::get('DPDFRANCE_RELAIS_CARRIER_ID', null, null, null)) {
                $relais_carrier_log=Configuration::get('DPDFRANCE_RELAIS_CARRIER_ID', null, null, null).','.implode(',', array_map('intval', explode('|', Tools::substr(Configuration::get('DPDFRANCE_RELAIS_CARRIER_LOG', null, null, null), 1))));
            }
            foreach (Shop::getShops(true) as $shop) {
                if (Configuration::get('DPDFRANCE_PREDICT_CARRIER_ID', null, null, $shop['id_shop'])) {
                    $predict_carrier_log.=Configuration::get('DPDFRANCE_PREDICT_CARRIER_ID', null, null, $shop['id_shop']).','.implode(',', array_map('intval', explode('|', Tools::substr(Configuration::get('DPDFRANCE_PREDICT_CARRIER_LOG', null, null, $shop['id_shop']), 1))));
                    $predict_carrier_sql = 'CA.id_carrier IN ('.$predict_carrier_log.') OR ';
                }
                if (Configuration::get('DPDFRANCE_CLASSIC_CARRIER_ID', null, null, $shop['id_shop'])) {
                    $classic_carrier_log.=Configuration::get('DPDFRANCE_CLASSIC_CARRIER_ID', null, null, $shop['id_shop']).','.implode(',', array_map('intval', explode('|', Tools::substr(Configuration::get('DPDFRANCE_CLASSIC_CARRIER_LOG', null, null, $shop['id_shop']), 1))));
                    $classic_carrier_sql = 'CA.id_carrier IN ('.$classic_carrier_log.') OR ';
                }
                if (Configuration::get('DPDFRANCE_RELAIS_CARRIER_ID', null, null, $shop['id_shop'])) {
                    $relais_carrier_log.=Configuration::get('DPDFRANCE_RELAIS_CARRIER_ID', null, null, $shop['id_shop']).','.implode(',', array_map('intval', explode('|', Tools::substr(Configuration::get('DPDFRANCE_RELAIS_CARRIER_LOG', null, null, $shop['id_shop']), 1))));
                    $relais_carrier_sql = 'CA.id_carrier IN ('.$relais_carrier_log.') OR ';
                }
            }
        } else {
            if (Configuration::get('DPDFRANCE_PREDICT_CARRIER_ID', null, null, $current_shop)) {
                $predict_carrier_log=Configuration::get('DPDFRANCE_PREDICT_CARRIER_ID', null, null, $current_shop).','.implode(',', array_map('intval', explode('|', Tools::substr(Configuration::get('DPDFRANCE_PREDICT_CARRIER_LOG', null, null, $current_shop), 1))));
                $predict_carrier_sql = 'CA.id_carrier IN ('.$predict_carrier_log.') OR ';
            }
            if (Configuration::get('DPDFRANCE_CLASSIC_CARRIER_ID', null, null, $current_shop)) {
                $classic_carrier_log=Configuration::get('DPDFRANCE_CLASSIC_CARRIER_ID', null, null, $current_shop).','.implode(',', array_map('intval', explode('|', Tools::substr(Configuration::get('DPDFRANCE_CLASSIC_CARRIER_LOG', null, null, $current_shop), 1))));
                $classic_carrier_sql = 'CA.id_carrier IN ('.$classic_carrier_log.') OR ';
            }
            if (Configuration::get('DPDFRANCE_RELAIS_CARRIER_ID', null, null, $current_shop)) {
                $relais_carrier_log=Configuration::get('DPDFRANCE_RELAIS_CARRIER_ID', null, null, $current_shop).','.implode(',', array_map('intval', explode('|', Tools::substr(Configuration::get('DPDFRANCE_RELAIS_CARRIER_LOG', null, null, $current_shop), 1))));
                $relais_carrier_sql = 'CA.id_carrier IN ('.$relais_carrier_log.') OR ';
            }
        }

        if (!empty($orders)) {
            $sql = 'SELECT  '.implode(', ', $fieldlist).'
                    FROM    '._DB_PREFIX_.'orders AS O, 
                            '._DB_PREFIX_.'carrier AS CA, 
                            '._DB_PREFIX_.'customer AS C, 
                            '._DB_PREFIX_.'address AS AD, 
                            '._DB_PREFIX_.'country AS CL
                    WHERE   O.id_address_delivery=AD.id_address AND
                            C.id_customer=O.id_customer AND 
                            CL.id_country=AD.id_country AND 
                            CA.id_carrier=O.id_carrier AND 
                            ('.$predict_carrier_sql.$classic_carrier_sql.$relais_carrier_sql.$europe_carrier_sql.') AND
                            ('.$liste_expeditions.')
                    ORDER BY id_order DESC';

            $orderlist = Db::getInstance()->ExecuteS($sql);

            if (!empty($orderlist)) {
                foreach ($orderlist as $order_var) {
                    $order = new Order($order_var['id_order']);
                    $address_delivery = new Address($order->id_address_delivery, (int)Context::getContext()->language->id);
                    if (_PS_VERSION_ < '1.5') {
                        $orderstate = new OrderHistory($order_var['id_order']);
                        $current_state_id = ($orderstate->getLastOrderState($order_var['id_order'])->id);
                        $current_state_name = $statuses_array[($orderstate->getLastOrderState($order_var['id_order'])->id)];
                        $internalref = $order->id;
                        $order->id_shop = '';
                    } else {
                        $current_state_id = $order->current_state;
                        $current_state_name = $statuses_array[$order->current_state];
                        $internalref = $order->reference;
                    }

                    switch ($current_state_id) {
                        default:
                            $dernierstatutcolis = '';
                            break;
                        case Configuration::get('DPDFRANCE_ETAPE_LIVRE', null, null, (int)$order->id_shop):
                            $dernierstatutcolis = '<img src="../modules/dpdfrance/views/img/admin/tracking.png" title="Trace du colis"/>';
                            break;
                        case Configuration::get('DPDFRANCE_ETAPE_EXPEDIEE', null, null, (int)$order->id_shop):
                            $dernierstatutcolis = '<img src="../modules/dpdfrance/views/img/admin/tracking.png" title="Trace du colis"/>';
                    }
                    if (_PS_VERSION_ < '1.5.5.0') {
                        $weight = number_format($order->getTotalWeight(), 2, '.', '.');
                    } else {
                        $order_carrier = new OrderCarrier((int)$order->getIdOrderCarrier());
                        if (Validate::isLoadedObject($order_carrier)) {
                            $weight = number_format($order_carrier->weight, 2, '.', '.');
                        }
                    }
                    $amount = number_format($order->total_paid, 2, '.', '.').' €';
                    $service=self::getService($order, Context::getContext()->language->id);
                    $code_pays_dest = self::getIsoCodebyIdCountry((int)$address_delivery->id_country);

                    switch ($service) {
                        case 'PRE':
                            if ($code_pays_dest !== 'F') {
                                $type = 'Predict Export<img src="../modules/dpdfrance/views/img/admin/service_predict.png" title="Predict Export"/>';
                            } else {
                                $type = 'Predict<img src="../modules/dpdfrance/views/img/admin/service_predict.png" title="Predict"/>';
                            }
                            $compte_chargeur = Configuration::get('DPDFRANCE_PREDICT_SHIPPER_CODE', null, null, (int)$order->id_shop);
                            $depot_code = Configuration::get('DPDFRANCE_PREDICT_DEPOT_CODE', null, null, (int)$order->id_shop);
                            $address = '<a class="popup" href="http://maps.google.com/maps?f=q&hl=fr&geocode=&q='.str_replace(' ', '+', $address_delivery->address1).','.str_replace(' ', '+', $address_delivery->postcode).'+'.str_replace(' ', '+', $address_delivery->city).'&output=embed" target="_blank">'.($address_delivery->company ? $address_delivery->company.'<br/>' : '').$address_delivery->address1.'<br/>'.$address_delivery->postcode.' '.$address_delivery->city.'</a>';
                            break;
                        case 'REL':
                            $type = 'Relais<img src="../modules/dpdfrance/views/img/admin/service_relais.png" title="Relais"/>';
                            $compte_chargeur = Configuration::get('DPDFRANCE_RELAIS_SHIPPER_CODE', null, null, (int)$order->id_shop);
                            $depot_code = Configuration::get('DPDFRANCE_RELAIS_DEPOT_CODE', null, null, (int)$order->id_shop);
                            $relay_id='';
                            preg_match('/P\d{5}/i', $address_delivery->company, $matches, PREG_OFFSET_CAPTURE);
                            if ($matches) {
                                $relay_id=$matches[0][0];
                            }
                            $address = '<a class="popup" href="http://www.dpd.fr/dpdrelais/id_'.$relay_id.'" target="_blank">'.$address_delivery->company.'<br/>'.$address_delivery->postcode.' '.$address_delivery->city.'</a>';
                            break;
                        default:
                            if ($code_pays_dest !== 'F') {
                                $type = 'Classic Export<img src="../modules/dpdfrance/views/img/admin/service_world.png" title="Classic Export"/>';
                            } else {
                                $type = 'Classic<img src="../modules/dpdfrance/views/img/admin/service_dom.png" title="Classic"/>';
                            }
                            $compte_chargeur = Configuration::get('DPDFRANCE_CLASSIC_SHIPPER_CODE', null, null, (int)$order->id_shop);
                            $depot_code = Configuration::get('DPDFRANCE_CLASSIC_DEPOT_CODE', null, null, (int)$order->id_shop);
                            $address = '<a class="popup" href="http://maps.google.com/maps?f=q&hl=fr&geocode=&q='.str_replace(' ', '+', $address_delivery->address1).','.str_replace(' ', '+', $address_delivery->postcode).'+'.str_replace(' ', '+', $address_delivery->city).'&output=embed" target="_blank">'.($address_delivery->company ? $address_delivery->company.'<br/>' : '').$address_delivery->address1.'<br/>'.$address_delivery->postcode.' '.$address_delivery->city.'</a>';
                            break;
                    }

                    $order_info[] = array(
                        'checked'               => ($current_state_id == Configuration::get('DPDFRANCE_ETAPE_EXPEDITION', null, null, (int)$order->id_shop) ? 'checked="checked"' : ''),
                        'id'                    => $order->id,
                        'reference'             => $internalref,
                        'date'                  => date('d/m/Y H:i:s', strtotime($order->date_add)),
                        'nom'                   => $address_delivery->firstname.' '.$address_delivery->lastname,
                        'type'                  => $type,
                        'address'               => $address,
                        'poids'                 => $weight,
                        'weightunit'            => Configuration::get('PS_WEIGHT_UNIT', null, null, (int)$order->id_shop),
                        'prix'                  => $amount,
                        'advalorem_checked'     => (Configuration::get('DPDFRANCE_AD_VALOREM', null, null, (int)$order->id_shop) == 1 ? 'checked="checked"' : ''),
                        'retour_checked'        => (Configuration::get('DPDFRANCE_RETOUR_OPTION', null, null, (int)$order->id_shop) != 0 ? 'checked="checked"' : ''),
                        'statut'                => $current_state_name,
                        'depot_code'            => $depot_code,
                        'shipper_code'          => $compte_chargeur,
                        'dernier_statut_colis'  => $dernierstatutcolis,
                    );
                }
            } else {
                $order_info['error'] = true;
            }
        } else {
            $order_info['error'] = true;
        }

        // Assign smarty variables and fetches template
        Context::getContext()->smarty->assign(array(
            'psVer'         => _PS_VERSION_,
            'stream'        => $stream,
            'token'         => $this->token,
            'order_info'    => $order_info,
            'dpdfrance_retour_option' => (int)Configuration::get('DPDFRANCE_RETOUR_OPTION', null, null, $current_shop),
        ));
        echo $this->fetchTemplate('/views/templates/admin/', 'AdminDPDFrance');
    }
}
