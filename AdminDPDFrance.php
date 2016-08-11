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
        $isops=array('DE', 'AD', 'AT', 'BE', 'BA', 'BG', 'HR', 'DK', 'ES', 'EE', 'FI', 'FR', 'GB', 'GR', 'GG', 'HU', 'IM', 'IE', 'IT', 'JE', 'LV', 'LI', 'LT', 'LU', 'NO', 'NL', 'PL', 'PT', 'CZ', 'RO', 'RS', 'SK', 'SI', 'SE', 'CH');
        $isoep=array('D', 'AND', 'A', 'B', 'BA', 'BG', 'CRO', 'DK', 'E', 'EST', 'SF', 'F', 'GB', 'GR', 'GG', 'H', 'IM', 'IRL', 'I', 'JE', 'LET', 'LIE', 'LIT', 'L', 'N', 'NL', 'PL', 'P', 'CZ', 'RO', 'RS', 'SK', 'SLO', 'S', 'CH');
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
                    ORDER BY invoice_date ASC';

        $sql15='    SELECT id_order
                    FROM '._DB_PREFIX_.'orders O
                    WHERE `current_state` NOT IN('.(int) Configuration::get('DPDFRANCE_ETAPE_LIVRE', null, null, (int) $id_shop).',0,5,6,7,8) AND O.id_shop '.$id_shop.'
                    ORDER BY invoice_date ASC';

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
    public static function formatGSM($gsm_dest, $code_iso)
    {
        if ($code_iso=='F') {
            $gsm_dest=str_replace(array(' ', '.', '-', ',', ';', '/', '\\', '(', ')'), '', $gsm_dest);
            $gsm_dest=str_replace('+33', '0', $gsm_dest);
            if (Tools::substr($gsm_dest, 0, 2)==33) {
                // Chrome autofill fix
                $gsm_dest=substr_replace($gsm_dest, '0', 0, 2);
            }
            if ((Tools::substr($gsm_dest, 0, 2)==06||Tools::substr($gsm_dest, 0, 2)==07)&&Tools::strlen($gsm_dest)==10) {
                return $gsm_dest;
            } else {
                return false;
            }
        } else {
            return $gsm_dest;
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
            $relay_id=Tools::substr($address_delivery->company, -7, 6);
            $code_pays_dest=self::getIsoCodebyIdCountry((int) $address_delivery->id_country);
            $tel_dest=(($address_delivery->phone_mobile)?$address_delivery->phone_mobile:(($address_invoice->phone_mobile)?$address_invoice->phone_mobile:(($address_delivery->phone)?$address_delivery->phone:(($address_invoice->phone)?$address_invoice->phone:''))));
            $mobile=self::formatGSM($tel_dest, $code_pays_dest);
            if (preg_match('/P\d{5}/i', $relay_id)) {
                $service='REL';
            } elseif ($mobile&&$code_pays_dest=='F'&&$order->id_carrier!=Configuration::get('DPDFRANCE_CLASSIC_CARRIER_ID', null, null, (int) $order->id_shop)) {
                $service='PRE';
            }
        }
        return $service;
    }

    /* Sync order status with parcel status, adds tracking number */
    public function syncShipments($id_shop, $id_employee)
    {
        /* Check if last tracking call is more than 1 hour old */
        if (time() - (int)Configuration::get('DPDFRANCE_LAST_TRACKING') < 3600) {
            die('DPD France parcel tracking update is done once an hour, please try again the next hour.');
        }
        Configuration::updateValue('DPDFRANCE_LAST_TRACKING', time());
        if ($id_shop==0) {
            $id_shop_sql='LIKE "%"';
        } else {
            $id_shop_sql='= '.(int) $id_shop;
        }
        if (Configuration::get('DPDFRANCE_PREDICT_CARRIER_LOG', null, null, (int) $id_shop)) {
            $predict_carrier_log='CA.id_carrier IN ('.implode(',', array_map('intval', explode('|', Tools::substr(Configuration::get('DPDFRANCE_PREDICT_CARRIER_LOG', null, null, (int) $id_shop), 1)))).') OR ';
        }
        if (Configuration::get('DPDFRANCE_CLASSIC_CARRIER_LOG', null, null, (int) $id_shop)) {
            $classic_carrier_log='CA.id_carrier IN ('.implode(',', array_map('intval', explode('|', Tools::substr(Configuration::get('DPDFRANCE_CLASSIC_CARRIER_LOG', null, null, (int) $id_shop), 1)))).') OR ';
        }
        if (Configuration::get('DPDFRANCE_RELAIS_CARRIER_LOG', null, null, (int) $id_shop)) {
            $relais_carrier_log='CA.id_carrier IN ('.implode(',', array_map('intval', explode('|', Tools::substr(Configuration::get('DPDFRANCE_RELAIS_CARRIER_LOG', null, null, (int) $id_shop), 1)))).') OR ';
        }
        $europe_carrier_log='CA.name LIKE "%DPD%"';

        $sql14='SELECT  O.id_order as reference, O.id_carrier as id_carrier, O.id_order as id_order, O.shipping_number as shipping_number
                FROM    '._DB_PREFIX_.'orders AS O, '._DB_PREFIX_.'carrier AS CA
                WHERE   (SELECT id_order_state
                        FROM      '._DB_PREFIX_.'order_history OH
                        WHERE    OH.id_order = O.id_order
                        ORDER BY  date_add DESC, id_order_history DESC
                        LIMIT    1)
                NOT IN  ('.(int) Configuration::get('DPDFRANCE_ETAPE_LIVRE', null, null, (int) $id_shop).',0,5,6,7,8) AND
                        CA.id_carrier=O.id_carrier AND
                        ('.$predict_carrier_log.$classic_carrier_log.$relais_carrier_log.$europe_carrier_log.')';

        $sql15='SELECT  O.reference as reference, O.id_carrier as id_carrier, O.id_order as id_order, O.shipping_number as shipping_number, O.id_shop as id_shop
                FROM    '._DB_PREFIX_.'orders AS O, '._DB_PREFIX_.'carrier AS CA
                WHERE   CA.id_carrier=O.id_carrier AND O.id_shop '.$id_shop_sql.' AND O.current_state
                NOT IN  ('.(int) Configuration::get('DPDFRANCE_ETAPE_LIVRE', null, null, (int) $id_shop).',0,5,6,7,8) AND
                        ('.$predict_carrier_log.$classic_carrier_log.$relais_carrier_log.$europe_carrier_log.')';

        if (_PS_VERSION_<'1.5') {
            $orderlist=Db::getInstance()->ExecuteS($sql14);
        } else {
            $orderlist=Db::getInstance()->ExecuteS($sql15);
        }
        if (!empty($orderlist)) {
            $statuslist=array();
            foreach ($orderlist as $orderinfos) {
                if (Validate::isLoadedObject($order = new Order($orderinfos['id_order']))) {
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
                    if (_PS_VERSION_<'1.5') {
                        $internalref=$order->id;
                        $order->id_shop='';
                    } else {
                        $internalref=$order->reference;
                    }
                    $variables=array(   'customer_center'=>'3',
                                        'customer'=>'1064',
                                        'password'=>'Pr2%5sHg',
                                        'reference'=>$internalref,
                                        'shipping_date'=>'',
                                        'shipmentnumber'=>$order->shipping_number,
                                        'shipping_customer_center'=>$depot_code,
                                        'shipping_customer'=>$compte_chargeur,
                                        'searchmode'=>'SearchMode_Equals',
                                        'language'=>'F'
                                    );
                    $serviceurl='http://webtrace.dpd.fr/dpd-webservices/webtrace_service.asmx?WSDL';
                    try {
                        $client=new SoapClient($serviceurl, array('connection_timeout'=>5, 'cache_wsdl'=>WSDL_CACHE_NONE, 'exceptions'=>true));
                    } catch (Exception$e) {
                        echo '<div class="warnmsg">'.$this->l('Error').' : '.$e->getMessage().'</div>';
                        exit;
                    }
                    // Call WS for traces by Ref
                    $response=$client->getShipmentTraceByReferenceGlobalWithCenterAsArray($variables);
                    $result=$response->getShipmentTraceByReferenceGlobalWithCenterAsArrayResult->clsShipmentTrace;

                    if (!empty($result->LastError)) {
                        echo $this->l('Order').' '.$internalref.' - '.$this->l('Error').' : '.$result->LastError.'<br/>';
                    } else {
                        if (!is_array($result)) {
                            $traces=$result->Traces->clsTrace;
                            if (!is_array($traces)) {
                                $statuslist[$order->id][$result->ShipmentNumber][]=$traces->StatusNumber;
                            } else {
                                foreach ($traces as $status) {
                                    $statuslist[$order->id][$result->ShipmentNumber][]=$status->StatusNumber;
                                }
                            }
                        } else {
                            foreach ($result as $shipment) {
                                $variables2=array(  'customer_center'=>'3',
                                                    'customer'=>'1064',
                                                    'password'=>'Pr2%5sHg',
                                                    'shipmentnumber'=>$shipment->ShipmentNumber
                                                );
                                $response2=$client->getShipmentTrace($variables2);
                                $traces=$response2->getShipmentTraceResult->Traces->clsTrace;
                                if (!is_array($traces)) {
                                    $statuslist[$order->id][$shipment->ShipmentNumber][]=$traces->StatusNumber;
                                } else {
                                    foreach ($traces as $status) {
                                        $statuslist[$order->id][$shipment->ShipmentNumber][]=$status->StatusNumber;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            // Statuslist is done, now update orders
            if ($statuslist) {
                foreach ($statuslist as $id_order => $parcels) {
                    foreach ($parcels as $shipmentnumber => $status) {
                        if (Validate::isLoadedObject($order = new Order((int)$id_order))) {
                            if (_PS_VERSION_ < '1.5') {
                                $internalref = $order->id;
                                $order->id_shop = '';
                            } else {
                                $internalref = $order->reference;
                            }
                            // Update to delivered
                            if ((in_array(40, $status, true) || in_array(400, $status, true)) && $order->current_state != (int)Configuration::get('DPDFRANCE_ETAPE_LIVRE', null, null, (int)$order->id_shop)) {
                                $history = new OrderHistory();
                                $history->id_order = (int)$id_order;
                                $history->id_employee = (int)$id_employee;
                                $history->id_order_state = (int)Configuration::get('DPDFRANCE_ETAPE_LIVRE', null, null, (int)$order->id_shop);
                                $history->changeIdOrderState((int)Configuration::get('DPDFRANCE_ETAPE_LIVRE', null, null, (int)$order->id_shop), $id_order);
                                $history->addWithemail();
                                echo $this->l('Order') . ' ' . $internalref . ' - ' . $this->l('Parcel') . ' ' . $shipmentnumber . ' ' . $this->l('is delivered') . '<br/>';
                                break; // Stop at first parcel
                            } else {
                                // Update to shipped
                                if ((in_array(10, $status, true) || in_array(28, $status, true) || in_array(89, $status, true)) && $order->current_state != (int)Configuration::get('DPDFRANCE_ETAPE_EXPEDIEE', null, null, (int)$order->id_shop) && $order->current_state != (int)Configuration::get('DPDFRANCE_ETAPE_LIVRE', null, null, (int)$order->id_shop)) {
                                    $service = self::getService($order, Context::getContext()->language->id);
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
                                    $carrier = new Carrier((int)$order->id_carrier, (int)Context::getContext()->language->id);
                                    $url = 'http://www.dpd.fr/tracer_' . $internalref . '_' . $depot_code . $compte_chargeur;
                                    $order->shipping_number = $internalref . '_' . $depot_code . $compte_chargeur;
                                    Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'orders SET shipping_number = "' . pSQL($order->shipping_number) . '" WHERE id_order = "' . $id_order . '"');
                                    if (_PS_VERSION_ >= '1.5') {
                                        Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'order_carrier SET tracking_number = "' . pSQL($order->shipping_number) . '" WHERE id_order = "' . $id_order . '"');
                                    }
                                    $order->update();
                                    $history = new OrderHistory();
                                    $history->id_order = (int)$id_order;
                                    $history->id_employee = (int)$id_employee;
                                    $history->id_order_state = (int)Configuration::get('DPDFRANCE_ETAPE_EXPEDIEE', null, null, (int)$order->id_shop);
                                    $history->changeIdOrderState((int)Configuration::get('DPDFRANCE_ETAPE_EXPEDIEE', null, null, (int)$order->id_shop), $id_order);
                                    if (_PS_VERSION_ < '1.5') {
                                        $template_vars = array('{followup}' => $url, '{firstname}' => $customer->firstname, '{lastname}' => $customer->lastname, '{id_order}' => (int)$order->id);
                                    } else {
                                        $template_vars = array('{followup}' => $url, '{firstname}' => $customer->firstname, '{lastname}' => $customer->lastname, '{order_name}' => $order->reference, '{id_order}' => (int)$order->id);
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
                                    Mail::Send((int)$order->id_lang, 'in_transit', $subject, $template_vars, $customer->email, $customer->firstname . ' ' . $customer->lastname);
                                    echo $this->l('Order') . ' ' . $internalref . ' - ' . $this->l('Parcel') . ' ' . $shipmentnumber . ' ' . $this->l('is handled by DPD') . '<br/>';
                                    break; // Stop at first parcel
                                } else {
                                    echo $this->l('Order') . ' ' . $internalref . ' - ' . $this->l('No update for parcel') . ' ' . $shipmentnumber . '<br/>';
                                }
                            }
                        }
                    }
                }
            }
        } else {
            echo $this->l('No orders to update.').'<br/>';
        }
    }

    /* Get eligible orders and builds up display */
    public function display()
    {
        // RSS stream
        $stream='';
        if (_PS_VERSION_<'1.4') {
            $rss=@simplexml_load_string(file_get_contents('http://www.dpd.fr/extensions/rss/flux_info_dpdfr.xml'));
        } else {
            $rss=@simplexml_load_string(Tools::file_get_contents('http://www.dpd.fr/extensions/rss/flux_info_dpdfr.xml'));
        }
        if (!empty($rss)) {
            if (empty($rss->channel->item)) {
                $stream='error';
            } else {
                foreach ($rss->channel->item as $item) {
                    $i=0;
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
                $stream='error';
            }
        } else {
            $stream='error';
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
                                $internalref_cleaned = $order->id;
                                $order->id_shop = '';
                            } else {
                                $internalref_cleaned = $order->reference;
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

                            $url = 'http://www.dpd.fr/tracer_'.$internalref_cleaned.'_'.$depot_code.$compte_chargeur;

                            $customer = new Customer((int)$order->id_customer);

                            $order->shipping_number = $internalref_cleaned.'_'.$depot_code.$compte_chargeur;
                            Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'orders SET shipping_number = "'.pSQL($order->shipping_number).'" WHERE id_order = "'.$id_order.'"');
                            if (_PS_VERSION_ >= '1.5') {
                                Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'order_carrier SET tracking_number = "'.pSQL($order->shipping_number).'" WHERE id_order = "'.$id_order.'"');
                            }
                            $order->update();

                            $history = new OrderHistory();
                            $history->id_order = (int)$id_order;
                            $history->changeIdOrderState(Configuration::get('DPDFRANCE_ETAPE_EXPEDIEE', null, null, (int)$order->id_shop), $id_order);
                            $history->id_employee = (int)Context::getContext()->employee->id;
                            $carrier = new Carrier((int)$order->id_carrier, (int)Context::getContext()->language->id);
                            if (_PS_VERSION_ < '1.5') {
                                $template_vars = array('{followup}' => $url, '{firstname}' => $customer->firstname, '{lastname}' => $customer->lastname, '{id_order}' => (int)$order->id);
                            } else {
                                $template_vars = array('{followup}' => $url, '{firstname}' => $customer->firstname, '{lastname}' => $customer->lastname, '{order_name}' => $order->reference, '{id_order}' => (int)$order->id);
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
                        // File creation
                        require_once(_PS_MODULE_DIR_.'dpdfrance/classes/admin/DPDStation.php');
                        $record=new DPDStation();
                        foreach ($orderlist as $order_var) {
                            // Shipper information retrieval
                            $order          = new Order($order_var['id_order']);
                            $nom_exp        = Configuration::get('DPDFRANCE_NOM_EXP', null, null, (int)$order->id_shop);            // Raison sociale expéditeur
                            $address_exp    = Configuration::get('DPDFRANCE_ADDRESS_EXP', null, null, (int)$order->id_shop);    // Adresse
                            $address2_exp   = Configuration::get('DPDFRANCE_ADDRESS2_EXP', null, null, (int)$order->id_shop);   // Complément d'adresse
                            $cp_exp         = Configuration::get('DPDFRANCE_CP_EXP', null, null, (int)$order->id_shop);             // Code postal
                            $ville_exp      = Configuration::get('DPDFRANCE_VILLE_EXP', null, null, (int)$order->id_shop);      // Ville
                            $code_pays_exp  = 'F';                                                                          // Code pays
                            $tel_exp        = Configuration::get('DPDFRANCE_TEL_EXP', null, null, (int)$order->id_shop);            // Téléphone
                            $email_exp      = Configuration::get('DPDFRANCE_EMAIL_EXP', null, null, (int)$order->id_shop);      // E-mail
                            $gsm_exp        = Configuration::get('DPDFRANCE_GSM_EXP', null, null, (int)$order->id_shop);            // N° GSM

                            // Backwards compatibility PS 1.4 and lower
                            if (_PS_VERSION_ < '1.5') {
                                $order->reference = $order->id;
                            }
                            $customer           = new Customer($order->id_customer);
                            $address_invoice    = new Address($order->id_address_invoice, (int)Context::getContext()->language->id);
                            $address_delivery   = new Address($order->id_address_delivery, (int)Context::getContext()->language->id);
                            $code_pays_dest     = self::getIsoCodebyIdCountry((int)$address_delivery->id_country);
                            $instr_liv_cleaned  = str_replace(array("\r\n", "\n", "\r", "\t"), ' ', $address_delivery->other);
                            $service            = self::getService($order, Context::getContext()->language->id);
                            $relay_id           = Tools::substr($address_delivery->company, -7, 6);
                            $tel_dest           = Db::getInstance()->getValue('SELECT gsm_dest FROM '._DB_PREFIX_.'dpdfrance_shipping WHERE id_cart ="'.$order->id_cart.'"');
                            if ($tel_dest == '') {
                                $tel_dest = (($address_delivery->phone_mobile) ? $address_delivery->phone_mobile : (($address_invoice->phone_mobile) ? $address_invoice->phone_mobile : (($address_delivery->phone) ? $address_delivery->phone : (($address_invoice->phone) ? $address_invoice->phone : ''))));
                            }
                            $mobile = self::formatGSM($tel_dest, $code_pays_dest);
                            if (Tools::strtolower(Configuration::get('PS_WEIGHT_UNIT', null, null, (int) $order->id_shop))=='kg') {
                                $poids=(int)(Tools::getValue('parcelweight')[$order->id]*100);
                            }
                            if (Tools::strtolower(Configuration::get('PS_WEIGHT_UNIT', null, null, (int) $order->id_shop))=='g') {
                                $poids=(int)(Tools::getValue('parcelweight')[$order->id]*0.1);
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

                            // DPD unified interface file structure
                            $record->add($order->reference, 0, 35);                                                     //  Référence client N°1 - Référence Commande Prestashop 1.5
                            $record->add(str_pad((int)$poids, 8, '0', STR_PAD_LEFT), 37, 8);                            //  Poids du colis sur 8 caractères
                            if ($service == 'REL') {
                                $record->add($address_delivery->lastname, 60, 35);                                      //  Nom du destinataire
                                $record->add($address_delivery->firstname, 95, 35);                                     //  Prénom du destinataire
                            } else {
                                $record->add($address_delivery->lastname.' '.$address_delivery->firstname, 60, 35);     //  Nom et prénom du destinataire
                                $record->add($address_delivery->company, 95, 35);                                       //  Complément d'adresse 1
                            }
                            $record->add($address_delivery->address2, 130, 140);                                        //  Complément d’adresse 2 a 5
                            $record->add($address_delivery->postcode, 270, 10);                                         //  Code postal
                            $record->add($address_delivery->city, 280, 35);                                             //  Ville
                            $record->add($address_delivery->address1, 325, 35);                                         //  Rue
                            $record->add('', 360, 10);                                                                  //  Filler
                            $record->add($code_pays_dest, 370, 3);                                                      //  Code Pays destinataire
                            $record->add($tel_dest, 373, 30);                                                           //  Téléphone
                            $record->add($nom_exp, 418, 35);                                                            //  Nom expéditeur
                            $record->add($address2_exp, 453, 35);                                                       //  Complément d’adresse 1
                            $record->add($cp_exp, 628, 10);                                                             //  Code postal
                            $record->add($ville_exp, 638, 35);                                                          //  Ville
                            $record->add($address_exp, 683, 35);                                                        //  Rue
                            $record->add($code_pays_exp, 728, 3);                                                       //  Code Pays
                            $record->add($tel_exp, 731, 30);                                                            //  Tél.
                            $record->add($instr_liv_cleaned, 761, 140);                                                 //  Instructions de livraison
                            $record->add(date('d/m/Y'), 901, 10);                                                       //  Date d'expédition théorique
                            $record->add(str_pad($compte_chargeur, 8, '0', STR_PAD_LEFT), 911, 8);                      //  N° de compte chargeur DPD
                            $record->add($order->id, 919, 35);                                                          //  Code à barres
                            $record->add($order->id, 954, 35);                                                          //  N° de commande - Id Order Prestashop
                            if (Tools::getIsset('advalorem') && in_array($order->id, Tools::getValue('advalorem'))) {
                                $record->add(str_pad(number_format($order->total_paid, 2, '.', ''), 9, '0', STR_PAD_LEFT), 1018, 9); // Montant valeur colis
                            }
                            $record->add($order->id, 1035, 35);                                                         //  Référence client N°2 - Id Order Prestashop
                            $record->add($email_exp, 1116, 80);                                                         //  E-mail expéditeur
                            $record->add($gsm_exp, 1196, 35);                                                           //  GSM expéditeur
                            $record->add($customer->email, 1231, 80);                                                   //  E-mail destinataire
                            $record->add($mobile, 1311, 35);                                                            //  GSM destinataire
                            if ($service == 'REL') {
                                $record->add($relay_id, 1442, 8);                                                       //  Identifiant relais Pickup
                            }
                            if ($service == 'PRE') {
                                $record->add('+', 1568, 1);                                                             //  Flag Predict
                            }
                            $record->add($address_delivery->lastname, 1569, 35);                                        //  Nom de famille du destinataire
                            if (Tools::getIsset('retour') && in_array($order->id, Tools::getValue('retour')) && $retour_option != 0) {
                                $record->add($retour_option, 1834, 1);                                                  //  Flag Retour
                            }
                            $record->addLine();
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
        $order_info = '';
        $statuses_array = array();
        $statuses = OrderState::getOrderStates((int)Context::getContext()->language->id);

        foreach ($statuses as $status) {
            $statuses_array[$status['id_order_state']] = $status['name'];
        }
        $fieldlist = array('O.`id_order`', 'O.`id_cart`', 'AD.`lastname`', 'AD.`firstname`', 'AD.`postcode`', 'AD.`city`', 'CL.`iso_code`', 'C.`email`', 'CA.`name`');
        $orders = AdminDPDFrance::getAllOrders((int)Tools::substr(Context::getContext()->cookie->shopContext, 2));
        $liste_expeditions = 'O.id_order IN ('.implode(',', $orders).')';

        $predict_carrier_log = $classic_carrier_log = $relais_carrier_log = $europe_carrier_log = '';

        if (Configuration::get('DPDFRANCE_PREDICT_CARRIER_LOG', null, null, (int) Context::getContext()->shop->id)) {
            $predict_carrier_log='CA.id_carrier IN ('.implode(',', array_map('intval', explode('|', Tools::substr(Configuration::get('DPDFRANCE_PREDICT_CARRIER_LOG', null, null, (int) Context::getContext()->shop->id), 1)))).') OR ';
        }
        if (Configuration::get('DPDFRANCE_CLASSIC_CARRIER_LOG', null, null, (int) Context::getContext()->shop->id)) {
            $classic_carrier_log='CA.id_carrier IN ('.implode(',', array_map('intval', explode('|', Tools::substr(Configuration::get('DPDFRANCE_CLASSIC_CARRIER_LOG', null, null, (int) Context::getContext()->shop->id), 1)))).') OR ';
        }
        if (Configuration::get('DPDFRANCE_RELAIS_CARRIER_LOG', null, null, (int) Context::getContext()->shop->id)) {
            $relais_carrier_log='CA.id_carrier IN ('.implode(',', array_map('intval', explode('|', Tools::substr(Configuration::get('DPDFRANCE_RELAIS_CARRIER_LOG', null, null, (int) Context::getContext()->shop->id), 1)))).') OR ';
        }
        $europe_carrier_log = 'CA.name LIKE \'%DPD%\'';

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
                            ('.$predict_carrier_log.$classic_carrier_log.$relais_carrier_log.$europe_carrier_log.') AND
                            ('.$liste_expeditions.')
                    ORDER BY id_order DESC';

            $orderlist = Db::getInstance()->ExecuteS($sql);
            if (!empty($orderlist)) {
                foreach ($orderlist as $order_var) {
                    $order = new Order($order_var['id_order']);
                    $address_delivery = new Address($order->id_address_delivery, (int)Context::getContext()->language->id);
                    $orderstate = new OrderHistory($order_var['id_order']);
                    if (_PS_VERSION_ < '1.5') {
                        $current_state_id = ($orderstate->getLastOrderState($order_var['id_order'])->id);
                        $current_state_name = $statuses_array[($orderstate->getLastOrderState($order_var['id_order'])->id)];
                        $order->reference = $order->id;
                        $order->id_shop = '';
                    } else {
                        $current_state_id = $order->current_state;
                        $current_state_name = $statuses_array[$order->current_state];
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
                    $weight = number_format($order->getTotalWeight(), 2, '.', '.');
                    $amount = number_format($order->total_paid, 2, '.', '.').' €';
                    $service=self::getService($order, Context::getContext()->language->id);

                    switch ($service) {
                        case 'PRE':
                            $type = 'Predict<img src="../modules/dpdfrance/views/img/admin/service_predict.png" title="Predict"/>';
                            $compte_chargeur = Configuration::get('DPDFRANCE_PREDICT_SHIPPER_CODE', null, null, (int)$order->id_shop);
                            $depot_code = Configuration::get('DPDFRANCE_PREDICT_DEPOT_CODE', null, null, (int)$order->id_shop);
                            $address = '<a class="popup" href="http://maps.google.com/maps?f=q&hl=fr&geocode=&q='.str_replace(' ', '+', $address_delivery->address1).','.str_replace(' ', '+', $address_delivery->postcode).'+'.str_replace(' ', '+', $address_delivery->city).'&output=embed" target="_blank">'.($address_delivery->company ? $address_delivery->company.'<br/>' : '').$address_delivery->address1.'<br/>'.$address_delivery->postcode.' '.$address_delivery->city.'</a>';
                            break;
                        case 'REL':
                            $type = 'Relais<img src="../modules/dpdfrance/views/img/admin/service_relais.png" title="Relais"/>';
                            $compte_chargeur = Configuration::get('DPDFRANCE_RELAIS_SHIPPER_CODE', null, null, (int)$order->id_shop);
                            $depot_code = Configuration::get('DPDFRANCE_RELAIS_DEPOT_CODE', null, null, (int)$order->id_shop);
                            $address = '<a class="popup" href="http://www.dpd.fr/dpdrelais/id_'.Tools::substr($address_delivery->company, -7, 6).'" target="_blank">'.$address_delivery->company.'<br/>'.$address_delivery->postcode.' '.$address_delivery->city.'</a>';
                            break;
                        default:
                            $code_pays_dest = self::getIsoCodebyIdCountry((int)$address_delivery->id_country);
                            if ($code_pays_dest !== 'F') {
                                $type = 'Intercontinental<img src="../modules/dpdfrance/views/img/admin/service_world.png" title="Intercontinental"/>';
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
                        'reference'             => $order->reference,
                        'date'                  => date('d/m/Y H:i:s', strtotime($order->date_add)),
                        'nom'                   => $address_delivery->firstname.' '.$address_delivery->lastname,
                        'type'                  => $type,
                        'address'               => $address,
                        'id'                    => $order->id,
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
                $order_info = 'error';
            }
        } else {
            $order_info = 'error';
        }

        // Assign smarty variables and fetches template
        Context::getContext()->smarty->assign(array(
            'stream'        => $stream,
            'token'         => $this->token,
            'order_info'    => $order_info,
            'dpdfrance_retour_option' => (int)Configuration::get('DPDFRANCE_RETOUR_OPTION', null, null, (int)Context::getContext()->shop->id),
        ));
        echo $this->fetchTemplate('/views/templates/admin/', 'AdminDPDFrance');
    }
}
