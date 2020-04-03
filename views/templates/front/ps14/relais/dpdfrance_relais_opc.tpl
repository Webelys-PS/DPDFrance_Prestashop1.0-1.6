{**
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
 *}

<script>
    var badIE = false;
</script>

<!--[if lt IE 8]>
    <script>badIE = true;</script>
<![endif]-->

<script>
if (badIE == false){
    {literal}
    $(document).ready(function()
    {
        checkedCarrier = $("input[name*='id_carrier']:checked").val();
        if ($("#dpdfrance_div_relais_error").length==0)
        {
            var i;
            var tabInput = document.getElementsByName("dpdfrance_lignerelais");
            var n = 5;

            for (i=0; i<n; i++)
                document.getElementById("dpdfrance_lignerelais").id = 'dpdfrance_lignerelais'+i;

            $('#id_carrier' + {/literal}{$dpdfrance_relais_carrier_id|escape:'javascript':'UTF-8'}{literal}).parent().parent().after("<tr><td colspan='4' style='padding:0; display:none;' id='tr_carrier_dpdfrance_relais'></td></tr>");

            dpdfrance_relais_response = [dpdfrance_div_relais_header,dpdfrance_lignerelais0,dpdfrance_lignerelais1,dpdfrance_lignerelais2,dpdfrance_lignerelais3,dpdfrance_lignerelais4]

            if ($('#id_carrier' + {/literal}{$dpdfrance_relais_carrier_id|escape:'javascript':'UTF-8'}{literal}).attr('checked'))
            {
                $.when($('#dpdfrance_div_relais_header,#dpdfrance_lignerelais0,#dpdfrance_lignerelais1,#dpdfrance_lignerelais2,#dpdfrance_lignerelais3,#dpdfrance_lignerelais4').show('fast')).done(function()
                {
                    $("#tr_carrier_dpdfrance_relais").html(dpdfrance_relais_response);
                    $("#tr_carrier_dpdfrance_relais").fadeIn('fast', function() {
                        dpdfrance_checkPudo();
                    });
                });
            }

            $('#id_carrier' + {/literal}{$dpdfrance_relais_carrier_id|escape:'javascript':'UTF-8'}{literal}).click(function()
            {
                $.when($('#dpdfrance_div_relais_header,#dpdfrance_lignerelais0,#dpdfrance_lignerelais1,#dpdfrance_lignerelais2,#dpdfrance_lignerelais3,#dpdfrance_lignerelais4').show('fast')).done(function()
                {
                    $("#tr_carrier_dpdfrance_relais").html(dpdfrance_relais_response);
                    $("#tr_carrier_dpdfrance_relais").fadeIn('fast', function() {
                        dpdfrance_checkPudo();
                    });
                });
            });

            $("input[name='id_carrier']").change(function()
            {
                if (!$('#id_carrier' + {/literal}{$dpdfrance_relais_carrier_id|escape:'javascript':'UTF-8'}{literal}).attr('checked')){
                    $("#tr_carrier_dpdfrance_relais").fadeOut('fast');
                }
            });
        }
        else
        {
            if ($('#id_carrier' + {/literal}{$dpdfrance_relais_carrier_id|escape:'javascript':'UTF-8'}{literal}).attr('checked'))
                $("#dpdfrance_div_relais_header").fadeIn('fast');

            $('#id_carrier' + {/literal}{$dpdfrance_relais_carrier_id|escape:'javascript':'UTF-8'}{literal}).click(function(){
                $("#dpdfrance_div_relais_header").fadeIn('fast');
            });

            $("input[name='id_carrier']").change(function(){
                if (!$('#id_carrier' + {/literal}{$dpdfrance_relais_carrier_id|escape:'javascript':'UTF-8'}{literal}).attr('checked'))
                    $("#dpdfrance_div_relais_header").fadeOut('fast');
            });
        }
    });
    {/literal}
}
else
{
    {literal}
    $(document).ready(function(){
        checkedCarrier = $("input[name*='id_carrier']:checked").val();
        if ($("#dpdfrance_div_relais_error").length==0){
            var i;
            var tabInput = document.getElementsByName("dpdfrance_lignerelais");
            var n = 5;

            for (i=0; i<n; i++){
                document.getElementById("dpdfrance_lignerelais").id = 'dpdfrance_lignerelais'+i;
            }

            if ($('#id_carrier' + {/literal}{$dpdfrance_relais_carrier_id|escape:'javascript':'UTF-8'}{literal}).attr('checked')){
                document.getElementById('dpdfrance_lignerelais0').style.display = "";
                document.getElementById('dpdfrance_lignerelais1').style.display = "";
                document.getElementById('dpdfrance_lignerelais2').style.display = "";
                document.getElementById('dpdfrance_lignerelais3').style.display = "";
                document.getElementById('dpdfrance_lignerelais4').style.display = "";
                $('#dpdfrance_div_relais_header').fadeIn('fast');
            }else{
                document.getElementById('dpdfrance_lignerelais0').style.display = "none";
                document.getElementById('dpdfrance_lignerelais1').style.display = "none";
                document.getElementById('dpdfrance_lignerelais2').style.display = "none";
                document.getElementById('dpdfrance_lignerelais3').style.display = "none";
                document.getElementById('dpdfrance_lignerelais4').style.display = "none";
                $('#dpdfrance_div_relais_header').fadeOut('fast');
            }

            $('#id_carrier' + {/literal}{$dpdfrance_relais_carrier_id|escape:'javascript':'UTF-8'}{literal}).click(function(){
                document.getElementById('dpdfrance_lignerelais0').style.display = "";
                document.getElementById('dpdfrance_lignerelais1').style.display = "";
                document.getElementById('dpdfrance_lignerelais2').style.display = "";
                document.getElementById('dpdfrance_lignerelais3').style.display = "";
                document.getElementById('dpdfrance_lignerelais4').style.display = "";
                $('#dpdfrance_div_relais_header').fadeIn('fast');
            });

            $("input[name='id_carrier']").change(function(){
                if (!$('#id_carrier' + {/literal}{$dpdfrance_relais_carrier_id|escape:'javascript':'UTF-8'}{literal}).attr('checked')){
                    document.getElementById('dpdfrance_lignerelais0').style.display = "none";
                    document.getElementById('dpdfrance_lignerelais1').style.display = "none";
                    document.getElementById('dpdfrance_lignerelais2').style.display = "none";
                    document.getElementById('dpdfrance_lignerelais3').style.display = "none";
                    document.getElementById('dpdfrance_lignerelais4').style.display = "none";
                    $('#dpdfrance_div_relais_header').fadeOut('fast');
                }
            });
        }
        else
        {
            if ($('#id_carrier' + {/literal}{$dpdfrance_relais_carrier_id|escape:'javascript':'UTF-8'}{literal}).attr('checked')) {
                $("#dpdfrance_div_relais_header").fadeIn('fast', function() {
                    dpdfrance_checkPudo();
                });
            }

            $('#id_carrier' + {/literal}{$dpdfrance_relais_carrier_id|escape:'javascript':'UTF-8'}{literal}).click(function() {
                $("#dpdfrance_div_relais_header").fadeIn('fast', function() {
                    dpdfrance_checkPudo();
                });
            });

            $("input[name='id_carrier']").change(function() {
                if (!$('#id_carrier' + {/literal}{$dpdfrance_relais_carrier_id|escape:'javascript':'UTF-8'}{literal}).attr('checked')) {
                    $("#dpdfrance_div_relais_header").fadeIn('fast', function() {
                        dpdfrance_checkPudo();
                    });
                }
            });
        }
    });
{/literal}
}
</script>

<noscript>
    <tr>
        <td colspan="5"><div class="dpdfrance_relais_error"><strong>{l s='It seems that your browser doesn\'t allow Javascript execution, therefore DPD Relais is not available. Please change browser settings, or try another browser.' mod='dpdfrance'}</strong></div></td>
    </tr><br/>
    <div style="display:none;">
</noscript>

<tr id="dpdfrance_div_relais_header" style="display:none;">
    {if isset($error)}
        <td colspan="4" id="dpdfrance_div_relais_error" class="alert warning"> {$error|escape:'htmlall':'UTF-8'}</td>
            <tr>
                <td colspan="4" style="display:none;">&nbsp;</td>
            </tr>
    {else}
        <td colspan="4">
            <p style="min-width:540px;">
                {l s='Please select your DPD Relais parcelshop among this list' mod='dpdfrance'}
            </p>
        </td>
    {if $dpdfrance_relais_status == 'error'}
        <tr>
            <td colspan="5" style="padding:0px;"><div class="dpdfrance_relais_error"><p>{l s='It seems that you haven\'t selected a DPD Pickup point, please pick one from this list' mod='dpdfrance'}</p></div></td>
        </tr>
    {/if}

    {if isset($dpdfrance_relais_empty)}
        <tr>
            <td colspan="5" style="padding:0px;"><div class="dpdfrance_relais_error"><p>{l s='There are no Pickup points near this address, please modify it.' mod='dpdfrance'}</p></div></td>
        </tr>
    {/if}
</tr>

{foreach from=$dpdfrance_relais_points item=points name=dpdfranceRelaisLoop}
<tr id="dpdfrance_lignerelais" class="dpdfrance_lignepr" style="display:none;" onclick="dpdfrance_registerPudo('{$points.relay_id|escape:'htmlall':'UTF-8'}'); document.getElementById('{$points.relay_id|escape:'htmlall':'UTF-8'}').checked=true;">
    <td align="left" class="dpdfrance_logorelais" id="dpdfrance_logorelais">
    </td>

    <td align="left" class="dpdfrance_adressepr"><b>{$points.shop_name|escape:'htmlall':'UTF-8'}</b><br>{$points.address1|escape:'htmlall':'UTF-8'}<br>{$points.postal_code|escape:'htmlall':'UTF-8'} {$points.city|escape:'htmlall':'UTF-8'}
    </td>

    <td align="right" class="dpdfrance_popinpr">
        <a class="dpdfrance_notfancy" target="_blank" href="javascript:void(0);" target="_blank" onclick="window.open(&quot;http://www.dpd.fr/dpdrelais/id_{$points.relay_id|escape:'htmlall':'UTF-8'}&quot;,&quot;Votre relais Pickup&quot;,&quot;menubar=no, status=no, scrollbars=no, location=no, toolbar=no, width=1024, height=640&quot;);return false;">
            <span onMouseOver="javascript:this.style.cursor='pointer';" onMouseOut="javascript:this.style.cursor='auto';">
                </u>{$points.distance|escape:'htmlall':'UTF-8'} km<br/>{l s='More details' mod='dpdfrance'}</u>
            </span>
        </a>
    </td>

    <td align="right" class="dpdfrance_radiopr">
            {if $dpdfrance_selectedrelay == $points.relay_id}
            <input type="radio" name="dpdfrance_relay_id" onclick="dpdfrance_registerPudo('{$points.relay_id|escape:'htmlall':'UTF-8'}')" id="{$points.relay_id|escape:'htmlall':'UTF-8'}" value="{$points.relay_id|escape:'htmlall':'UTF-8'}" checked="checked">
            {else}
            <input type="radio" name="dpdfrance_relay_id" onclick="dpdfrance_registerPudo('{$points.relay_id|escape:'htmlall':'UTF-8'}')" id="{$points.relay_id|escape:'htmlall':'UTF-8'}" value="{$points.relay_id|escape:'htmlall':'UTF-8'}" {if $smarty.foreach.dpdfranceRelaisLoop.first} checked="checked" {/if}>
            {/if}
            <label for="{$points.relay_id|escape:'htmlall':'UTF-8'}"><span><span></span></span><b>ICI</b></label>
    </td>
</tr>

</td>

{/foreach}

{/if}
</div>
</td>
<noscript></div></noscript>