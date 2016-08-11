{**
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
 *}

<link rel="stylesheet" type="text/css" href="../modules/dpdfrance/views/css/admin/dpdfrance_config.css"/>
<link rel="stylesheet" type="text/css" href="../modules/dpdfrance/views/js/admin/jquery/plugins/fancybox/jquery.fancybox.css" media="screen"/>
<script type="text/javascript" src="../modules/dpdfrance/views/js/admin/jquery/plugins/fancybox/jquery.fancybox.js"></script>
<script type="text/javascript" src="../modules/dpdfrance/views/js/admin/jquery/plugins/validation/jquery.validate.min.js"></script>

{literal}
<script type="text/javascript">
function dpdfrance_attr_carrier(element) {
    var maxValue = undefined;
    $('option', element).each(function() {
        var val = $(this).attr('value');
        val = parseInt(val, 10);
        if (maxValue === undefined || maxValue < val) {
            maxValue = val;
        }
    });
    element.val(maxValue);
}
</script>
{/literal}

<form action="{$form_submit_url|escape:'htmlall':'UTF-8'}" method="post">
    <fieldset><legend><img src="../modules/dpdfrance/views/img/admin/admin.png" alt="" title="" />{l s='Settings' mod='dpdfrance'}</legend>
    
        <!-- Tabs header -->
        <div id="dpdfrance_menu">
            <ul id="onglets">
                <li style="background-color: #dc0032;"><a id="onglet0" href="javascript:void(0)" onclick="$(&quot;#donnees_exp,#modes_transport,#options_supp,#gestion_exp,#recap&quot;).fadeOut(0, function() {literal}{{/literal}$(&quot;#accueil&quot;).fadeIn(&quot;slow&quot;);$(&quot;#onglet0&quot;).parent().css(&quot;background-color&quot;, &quot;#dc0032&quot;);
        $(&quot;#onglet1,#onglet2,#onglet3,#onglet4,#onglet5&quot;).parent().css(&quot;background-color&quot;, &quot;#808285&quot;);{literal}}{/literal});"> {l s='Start' mod='dpdfrance'} </a></li>
                <li><a id="onglet1" href="javascript:void(0)" onclick="$(&quot;#accueil,#modes_transport,#options_supp,#gestion_exp,#recap&quot;).fadeOut(0, function() {literal}{{/literal}$(&quot;#donnees_exp&quot;).fadeIn(&quot;slow&quot;);$(&quot;#onglet1&quot;).parent().css(&quot;background-color&quot;, &quot;#dc0032&quot;);
        $(&quot;#onglet0,#onglet2,#onglet3,#onglet4,#onglet5&quot;).parent().css(&quot;background-color&quot;, &quot;#808285&quot;);{literal}}{/literal});"> {l s='Your personal data' mod='dpdfrance'} </a></li>
                <li><a id="onglet2" href="javascript:void(0)" onclick="$(&quot;#accueil,#donnees_exp,#options_supp,#gestion_exp,#recap&quot;).fadeOut(0, function() {literal}{{/literal}$(&quot;#modes_transport&quot;).fadeIn(&quot;slow&quot;);$(&quot;#onglet2&quot;).parent().css(&quot;background-color&quot;, &quot;#dc0032&quot;);
        $(&quot;#onglet1,#onglet0,#onglet3,#onglet4,#onglet5&quot;).parent().css(&quot;background-color&quot;, &quot;#808285&quot;);{literal}}{/literal});"> {l s='Delivery services' mod='dpdfrance'} </a></li>
                <li><a id="onglet3" href="javascript:void(0)" onclick="$(&quot;#accueil,#donnees_exp,#modes_transport,#gestion_exp,#recap&quot;).fadeOut(0, function() {literal}{{/literal}$(&quot;#options_supp&quot;).fadeIn(&quot;slow&quot;);$(&quot;#onglet3&quot;).parent().css(&quot;background-color&quot;, &quot;#dc0032&quot;);
        $(&quot;#onglet1,#onglet2,#onglet0,#onglet4,#onglet5&quot;).parent().css(&quot;background-color&quot;, &quot;#808285&quot;);{literal}}{/literal});"> {l s='Advanced settings' mod='dpdfrance'} </a></li>
                <li><a id="onglet4" href="javascript:void(0)" onclick="$(&quot;#accueil,#donnees_exp,#modes_transport,#options_supp,#recap&quot;).fadeOut(0, function() {literal}{{/literal}$(&quot;#gestion_exp&quot;).fadeIn(&quot;slow&quot;);$(&quot;#onglet4&quot;).parent().css(&quot;background-color&quot;, &quot;#dc0032&quot;);
        $(&quot;#onglet1,#onglet2,#onglet3,#onglet0,#onglet5&quot;).parent().css(&quot;background-color&quot;, &quot;#808285&quot;);{literal}}{/literal});"> {l s='Orders management' mod='dpdfrance'} </a></li>
                <li><a id="onglet5" href="javascript:void(0)" onclick="$(&quot;#accueil,#donnees_exp,#modes_transport,#options_supp,#gestion_exp&quot;).fadeOut(0, function() {literal}{{/literal}$(&quot;#recap&quot;).fadeIn(&quot;slow&quot;);$(&quot;#onglet5&quot;).parent().css(&quot;background-color&quot;, &quot;#dc0032&quot;);
        $(&quot;#onglet1,#onglet2,#onglet3,#onglet4,#onglet0&quot;).parent().css(&quot;background-color&quot;, &quot;#808285&quot;);{literal}}{/literal});"> {l s='Summary' mod='dpdfrance'} </a></li>
            </ul>
        </div>

        <!-- Tab Accueil -->
        <div id="accueil" style="display:block;">
            <br/><span class="section_title">{l s='Welcome to DPD' mod='dpdfrance'}</span><br/>
            <div class="notabene" style="font-size:14px;">{l s='You must be a DPD France customer to use this module, if not please get in touch with us at ' mod='dpdfrance'}<a href="http://www.dpd.fr/nous_contacter_prestashop" target="_blank">www.dpd.fr</a></div><br/>
            <div id="accueil_wrap">
                <div id="documentation" href="javascript:void(0)" onclick="window.open(&quot;../modules/dpdfrance/docs/readme_dpdfrance_prestashop.pdf&quot;, &quot;s&quot;, &quot;width= 640, height= 900, left=0, top=0, resizable=yes, toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, copyhistory=no&quot;);"><span class="client_title">{l s='Open documentation' mod='dpdfrance'}</span><div id="documentation_img"></div><span class="client_subtitle">{l s='Please click here first to access the user manual' mod='dpdfrance'}</span></div>
                <div id="client" href="javascript:void(0)" onclick="$(&quot;#onglet1&quot;).click();"><span class="client_title">{l s='I\'m already a customer' mod='dpdfrance'}</span><div id="client_img"></div><span class="client_subtitle">{l s='Proceed to the plugin configuration' mod='dpdfrance'}</span></div>
                <br/>
            </div>
        </div>

        <!-- Tab Vos données expéditeur -->
        <div id="donnees_exp" style="display:none;">
            <br/><span class="section_title">{l s='Your personal data' mod='dpdfrance'}</span><br/><br/>

                <div id="donnees_exp_wrap">
                <label>{l s='Company Name' mod='dpdfrance'}</label><div class="margin-form"><input type="text" size="33" name="nom_exp" value="{$nom_exp|escape:'htmlall':'UTF-8'}" /></div>
                <label>{l s='Address 1' mod='dpdfrance'}</label><div class="margin-form"><input type="text" size="33" name="address_exp" value="{$address_exp|escape:'htmlall':'UTF-8'}" /></div>
                <label>{l s='Address 2' mod='dpdfrance'}</label><div class="margin-form"><input type="text" size="33" name="address2_exp" value="{$address2_exp|escape:'htmlall':'UTF-8'}" /></div>
                <label>{l s='Postal code' mod='dpdfrance'}</label><div class="margin-form"><input type="text" size="33" name="cp_exp" value="{$cp_exp|escape:'htmlall':'UTF-8'}" /></div>
                <label>{l s='City' mod='dpdfrance'}</label><div class="margin-form"><input type="text" size="33" name="ville_exp" value="{$ville_exp|escape:'htmlall':'UTF-8'}" /></div>
                <label>{l s='Telephone' mod='dpdfrance'}</label><div class="margin-form"><input type="text" size="33" name="tel_exp" value="{$tel_exp|escape:'htmlall':'UTF-8'}" /></div>
                <label>{l s='GSM' mod='dpdfrance'}</label><div class="margin-form"><input type="text" size="33" name="gsm_exp" value="{$gsm_exp|escape:'htmlall':'UTF-8'}" /></div>
                <label>{l s='E-mail' mod='dpdfrance'}</label><div class="margin-form"><input type="text" size="33" name="email_exp" value="{$email_exp|escape:'htmlall':'UTF-8'}" /></div>

                <center><a size="6" name="next" class="button" href="javascript:void(0)" onclick="$(&quot;#onglet0&quot;).click();">{l s='Previous' mod='dpdfrance'}</a> 
                <a size="6" name="next" class="button" href="javascript:void(0)" onclick="$(&quot;#onglet2&quot;).click();">{l s='Next' mod='dpdfrance'}</a></center>
                <br/>
            </div>
        </div>

        <!-- Tab Services de transport -->
        <div id="modes_transport" style="display:none;">
            <br/><span class="section_title">{l s='Delivery services' mod='dpdfrance'}</span><br/><br/>
            <div id="modes_transport_wrap">

            <!-- DPD Relais -->
            <div id="service_relais">
                <label>{l s='DPD Relais' mod='dpdfrance'}</label>
                <span>{l s='(France)' mod='dpdfrance'}</span>
                <div id="service_relais_img"></div>

                <span>{l s='Depot code - Contract number' mod='dpdfrance'}<br/>{l s='(i.e.: 013 - 12345)' mod='dpdfrance'}</span><br/><br/>
                <input type="text" size="3" name="relais_depot_code" class="relais_depot_code" value="{$relais_depot_code|escape:'htmlall':'UTF-8'}" /> - 
                <input type="text" size="5" name="relais_shipper_code" class="relais_shipper_code" value="{$relais_shipper_code|escape:'htmlall':'UTF-8'}" /><br/><br/>

                <span>{l s='Carrier assignation' mod='dpdfrance'}</span><br/><br/>
                <select name="dpdfrance_relais_carrier_id"><option value="0">{l s='None - Disable this carrier' mod='dpdfrance'}</option>
                {foreach from=$carriers item=carrier} 
                    {if $carrier.id_carrier == $dpdfrance_relais_carrier_id}
                        <option value="{$carrier.id_carrier|escape:'htmlall':'UTF-8'}" selected>{$carrier.id_carrier|escape:'htmlall':'UTF-8'} - {$carrier.name|escape:'htmlall':'UTF-8'}</option>
                    {else}
                        <option value="{$carrier.id_carrier|escape:'htmlall':'UTF-8'}">{$carrier.id_carrier|escape:'htmlall':'UTF-8'} - {$carrier.name|escape:'htmlall':'UTF-8'}</option>
                    {/if}
                {/foreach}
                </select><br/><br/>

                <span>{l s='Carrier creation' mod='dpdfrance'}</span><br/><br/>
                <input type="submit" name="submitCreateCarrierRelais" value="{l s='Create DPD Relais carrier' mod='dpdfrance'}" class="button"/> 
            </div>

            <!-- DPD Predict -->
            <div id="service_predict">
                <label>{l s='DPD Predict' mod='dpdfrance'}</label>
                <span>{l s='(France)' mod='dpdfrance'}</span>
                <div id="service_predict_img"></div>

                <span>{l s='Depot code - Contract number' mod='dpdfrance'}<br/>{l s='(i.e.: 013 - 12345)' mod='dpdfrance'}</span><br/><br/>
                <input type="text" size="3" name="predict_depot_code" class="predict_depot_code" value="{$predict_depot_code|escape:'htmlall':'UTF-8'}" /> - 
                <input type="text" size="5" name="predict_shipper_code" class="predict_shipper_code" value="{$predict_shipper_code|escape:'htmlall':'UTF-8'}" /><br/><br/>

                <span>{l s='Carrier assignation' mod='dpdfrance'}</span><br/><br/>
                <select name="dpdfrance_predict_carrier_id"><option value="0">{l s='None - Disable this carrier' mod='dpdfrance'}</option>
                {foreach from=$carriers item=carrier} 
                    {if $carrier.id_carrier == $dpdfrance_predict_carrier_id}
                        <option value="{$carrier.id_carrier|escape:'htmlall':'UTF-8'}" selected>{$carrier.id_carrier|escape:'htmlall':'UTF-8'} - {$carrier.name|escape:'htmlall':'UTF-8'}</option>
                    {else}
                        <option value="{$carrier.id_carrier|escape:'htmlall':'UTF-8'}">{$carrier.id_carrier|escape:'htmlall':'UTF-8'} - {$carrier.name|escape:'htmlall':'UTF-8'}</option>
                    {/if}
                {/foreach}
                </select><br/><br/>

                <span>{l s='Carrier creation' mod='dpdfrance'}</span><br/><br/>
                <input type="submit" name="submitCreateCarrierPredict" value="{l s='Create DPD Predict carrier' mod='dpdfrance'}" class="button"/> 
            </div>

            <!-- DPD Classic -->
            <div id="service_classic">
                <label>{l s='DPD Classic' mod='dpdfrance'}</label>
                <label>{l s='Europe & Intercontinental' mod='dpdfrance'}</label>
                <span>{l s='(France : delivery at workplace)' mod='dpdfrance'}</span><br/>

                <div id="service_classic_img"></div>

                <span>{l s='Depot code - Contract number' mod='dpdfrance'}<br/>{l s='(i.e.: 013 - 12345)' mod='dpdfrance'}</span><br/><br/>
                <input type="text" size="3" name="classic_depot_code" class="classic_depot_code" value="{$classic_depot_code|escape:'htmlall':'UTF-8'}" /> - 
                <input type="text" size="5" name="classic_shipper_code" class="classic_shipper_code" value="{$classic_shipper_code|escape:'htmlall':'UTF-8'}" /><br/><br/>

                <span>{l s='Carrier assignation' mod='dpdfrance'}</span><br/><br/>
                <select name="dpdfrance_classic_carrier_id"><option value="0">{l s='None - Disable this carrier' mod='dpdfrance'}</option>
                {foreach from=$carriers item=carrier} 
                    {if $carrier.id_carrier == $dpdfrance_classic_carrier_id}
                        <option value="{$carrier.id_carrier|escape:'htmlall':'UTF-8'}" selected>{$carrier.id_carrier|escape:'htmlall':'UTF-8'} - {$carrier.name|escape:'htmlall':'UTF-8'}</option>
                    {else}
                        <option value="{$carrier.id_carrier|escape:'htmlall':'UTF-8'}">{$carrier.id_carrier|escape:'htmlall':'UTF-8'} - {$carrier.name|escape:'htmlall':'UTF-8'}</option>
                    {/if}
                {/foreach}
                </select><br/><br/>

                <span>{l s='Carrier creation' mod='dpdfrance'}</span><br/><br/>
                <input type="submit" name="submitCreateCarrierClassic" value="{l s='Create DPD Classic carrier' mod='dpdfrance'}" class="button"/> 
                <input type="submit" name="submitCreateCarrierWorld" value="{l s='Create DPD Intercontinental carrier' mod='dpdfrance'}" class="button"/><br/>
            </div>

            <div class="notabene">{l s='Please contact your DPD sales representative to get your contract numbers and depot code.' mod='dpdfrance'}</div><br/><br/>

            <center><a size="6" name="next" class="button" href="javascript:void(0)" onclick="$(&quot;#onglet1&quot;).click();">{l s='Previous' mod='dpdfrance'}</a> 
            <a size="6" name="next" class="button" href="javascript:void(0)" onclick="$(&quot;#onglet3&quot;).click();">{l s='Next' mod='dpdfrance'}</a></center>
            <br/>
            </div>
        </div>

        <!-- Tab Options supplémentaires -->
        <div id="options_supp" style="display:none;">
            <strong><br/>{l s='Advanced settings' mod='dpdfrance'}</strong><br/><br/>
            <label>{l s='DPD Relais WebService URL' mod='dpdfrance'}</label><div class="margin-form"><input type="text" size="48" name="mypudo_url" value="{$mypudo_url|escape:'htmlall':'UTF-8'}" /><br/>{l s='Caution! Critical setting' mod='dpdfrance'}</div>

            {if $ps_version >= '1.4'}
                <label>{l s='Coastal islands & Corsica overcost' mod='dpdfrance'}</label><div class="margin-form"><input type="text" size="3" name="supp_iles" value="{$supp_iles|escape:'htmlall':'UTF-8'}" />{l s=' € (-1 to disable delivery to these areas)' mod='dpdfrance'}</div>
                <label>{l s='Mountain areas overcost' mod='dpdfrance'}</label><div class="margin-form"><input type="text" size="3" name="supp_montagne" value="{$supp_montagne|escape:'htmlall':'UTF-8'}" />{l s=' € (-1 to disable delivery to these areas)' mod='dpdfrance'}</div>
            {/if}

            <label>{l s='Google Maps API Key' mod='dpdfrance'}</label>
            <div class="margin-form">
                <input type="text" size="48" name="google_api_key" value="{$google_api_key|escape:'htmlall':'UTF-8'}" />
                <br/>
                <a href="https://console.developers.google.com/flows/enableapi?apiid=maps_backend,geocoding_backend,directions_backend,distance_matrix_backend,elevation_backend,places_backend&keyType=CLIENT_SIDE&reusekey=true" target="_blank" >{l s='Click here to retrieve your Google API Key' mod='dpdfrance'}</a>
            </div>
            <br/>
            <center><a size="6" name="next" class="button" href="javascript:void(0)" onclick="$(&quot;#onglet2&quot;).click();">{l s='Previous' mod='dpdfrance'}</a> 
            <a size="6" name="next" class="button" href="javascript:void(0)" onclick="$(&quot;#onglet4&quot;).click();">{l s='Next' mod='dpdfrance'}</a></center>
            <br/>
        </div>

        <!-- Tab Gestion des expéditions -->
        <div id="gestion_exp" style="display:none;">
            <strong><br/><br/>{l s='Orders management' mod='dpdfrance'}</strong><br/><br/><br/>

            <label>{l s='Preparation in progress status' mod='dpdfrance'}<br/></label>
                <div class="margin-form">
                <select name="id_expedition">
                {foreach from=$etats_factures item=value} 
                    {if $value.id_order_state == $dpdfrance_etape_expedition}
                        <option value="{$value.id_order_state|escape:'htmlall':'UTF-8'}" selected>{$value.name|escape:'htmlall':'UTF-8'}</option>
                    {else}
                        <option value="{$value.id_order_state|escape:'htmlall':'UTF-8'}">{$value.name|escape:'htmlall':'UTF-8'}</option>
                    {/if}
                {/foreach}
                </select><br/>{l s='Orders in this state will be selected by default for exporting.' mod='dpdfrance'}<br/>
            </div>
            
            <label>{l s='Shipped status' mod='dpdfrance'}<br/></label>
            <div class="margin-form">
                <select name="id_expedie">
                {foreach from=$etats_factures item=value} 
                    {if $value.id_order_state == $dpdfrance_etape_expediee}
                        <option value="{$value.id_order_state|escape:'htmlall':'UTF-8'}" selected>{$value.name|escape:'htmlall':'UTF-8'}</option>
                    {else}
                        <option value="{$value.id_order_state|escape:'htmlall':'UTF-8'}">{$value.name|escape:'htmlall':'UTF-8'}</option>
                    {/if}
                {/foreach}
                </select><br/>{l s='Once parcel trackings are generated, orders will be updated to this state.' mod='dpdfrance'}<br/>
            </div>

            <label>{l s='Delivered status' mod='dpdfrance'}<br/></label>
            <div class="margin-form">
                <select name="id_livre">
                {foreach from=$etats_factures item=value} 
                    {if $value.id_order_state == $dpdfrance_etape_livre}
                        <option value="{$value.id_order_state|escape:'htmlall':'UTF-8'}" selected>{$value.name|escape:'htmlall':'UTF-8'}</option>
                    {else}
                        <option value="{$value.id_order_state|escape:'htmlall':'UTF-8'}">{$value.name|escape:'htmlall':'UTF-8'}</option>
                    {/if}
                {/foreach}
                </select><br/>{l s='Once parcels are delivered, orders will be updated to this state.' mod='dpdfrance'}<br/>
            </div>

            <label>{l s='Parcel insurance service' mod='dpdfrance'}<br/></label>
            <div class="margin-form">
                <select name="ad_valorem">
                {foreach from=$optvd item=option key=key} 
                    {if $key == $dpdfrance_ad_valorem}
                        <option value="{$key|escape:'htmlall':'UTF-8'}" selected>{$option|escape:'htmlall':'UTF-8'}</option>
                    {else}
                        <option value="{$key|escape:'htmlall':'UTF-8'}">{$option|escape:'htmlall':'UTF-8'}</option>
                    {/if}
                {/foreach}
                </select><br/>{l s='Ad Valorem : Please refer to your pricing conditions.' mod='dpdfrance'}<br/>
            </div>

            <label>{l s='DPD Returns service' mod='dpdfrance'}<br/></label>
            <div class="margin-form">
                <select name="retour">
                {foreach from=$optretour item=option key=key} 
                    {if $key == $dpdfrance_retour_option}
                        <option value="{$key|escape:'htmlall':'UTF-8'}" selected>{$option|escape:'htmlall':'UTF-8'}</option>
                    {else}
                        <option value="{$key|escape:'htmlall':'UTF-8'}">{$option|escape:'htmlall':'UTF-8'}</option>
                    {/if}
                {/foreach}
                </select><br/>{l s='DPD Returns options : Please refer to your pricing conditions.' mod='dpdfrance'}<br/>
            </div>

            <center><a size="6" name="next" class="button" href="javascript:void(0)" onclick="$(&quot;#onglet3&quot;).click();">{l s='Previous' mod='dpdfrance'}</a> 
            <a size="6" name="next" class="button" href="javascript:void(0)" onclick="$(&quot;#onglet5&quot;).click();">{l s='Next' mod='dpdfrance'}</a></center>
            <br/>
        </div>

        <!-- Tab Recapitulatif -->
        <div id="recap" style="display:none;">
            <strong><center><br/><br/>{l s='You\'re all set!' mod='dpdfrance'}</center></strong><br/><br/>
            <center><input id="save_settings_button" type="submit" name="submitRcReferer" value="{l s='Save settings' mod='dpdfrance'}" class="button"></center></br>
            <center><a size="6" name="next" class="button" href="javascript:void(0)" onclick="$(&quot;#onglet4&quot;).click();">{l s='Return to configuration' mod='dpdfrance'}</a></center><br/>
            <br/>
        </div>
    </fieldset>
</form>