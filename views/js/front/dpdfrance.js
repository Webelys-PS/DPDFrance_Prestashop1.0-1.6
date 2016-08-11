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

var opc;
var psVer;

if (opc == 1)
{
    $(document).bind('ready ajaxComplete', function()
    {
        $("input[name*='delivery_option[']").change(function() {
            $('[name=dpdfrance_wait]').remove();
            checkedCarrier = $("input[name*='delivery_option[']:checked").val().substr(0,$("input[name*='delivery_option[']:checked").val().indexOf(','));
            if (checkedCarrier == dpdfranceRelaisCarrierId || checkedCarrier == dpdfrancePredictCarrierId)
                $('input[class=delivery_option_radio]:checked').parents('div.delivery_option').after('<div name="dpdfrance_wait"></div>');
        });
    });
}
else
{
    $(document).ready(function()
    {
        $("input[name*='delivery_option[']").change(function() {
            $('[name=dpdfrance_wait]').remove();
            checkedCarrier = $("input[name*='delivery_option[']:checked").val().substr(0,$("input[name*='delivery_option[']:checked").val().indexOf(','));
            if (checkedCarrier == dpdfranceRelaisCarrierId || checkedCarrier == dpdfrancePredictCarrierId) {
                $('input[class=delivery_option_radio]:checked').parents('div.delivery_option').after('<div name="dpdfrance_wait"></div>');
                $('[name=processCarrier]').attr('disabled', 'disabled');
            }
        });
    });
}

/* Soflexibilite bad behaviour correction */
$(document).bind('ready ajaxComplete', function()
{
    if (psVer >= 1.5) {
        if (!$("input[name*='delivery_option[']:checked").val()) {
            if (document.getElementById("dpdfrance_relais_point_table"))
                $("#dpdfrance_relais_point_table").hide();
            if (document.getElementById("tr_carrier_predict"))
                $("#tr_carrier_predict").hide();
            if (document.getElementById("div_dpdfrance_predict_block"))
                $("#div_dpdfrance_predict_block").hide();
        }
    }
});

/* Google Maps */
function initializeDpdfranceGM(mapid,lat,longti,baseurl) {
    var latlng = new google.maps.LatLng(lat, longti);

    var myOptions = {
        zoom      : 16,
        center    : latlng,
        mapTypeId : google.maps.MapTypeId.ROADMAP,
        styles:[{"featureType":"landscape","stylers":[{"visibility":"on"},{"color":"#e6e7e7"}]},{"featureType":"poi.sports_complex","stylers":[{"visibility":"on"}]},{"featureType":"poi.attraction","stylers":[{"visibility":"off"}]},{"featureType":"poi.government","stylers":[{"visibility":"on"}]},{"featureType":"poi.medical","stylers":[{"visibility":"on"}]},{"featureType":"poi.place_of_worship","stylers":[{"visibility":"on"}]},{"featureType":"poi.school","stylers":[{"visibility":"on"}]},{"featureType":"water","elementType":"geometry","stylers":[{"visibility":"on"},{"color":"#d2e4f3"}]},{"featureType":"water","elementType":"labels","stylers":[{"visibility":"on"}]},{"featureType":"transit","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"geometry.fill","stylers":[{"visibility":"on"},{"color":"#ffffff"}]},{"featureType":"road","elementType":"geometry.stroke","stylers":[{"visibility":"on"},{"color":"#e6e7e7"}]},{"elementType":"labels.text.fill","stylers":[{"visibility":"on"},{"color":"#666666"}]},{"featureType":"poi.business","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"poi","elementType":"geometry.fill","stylers":[{"visibility":"on"},{"color":"#dbdbdb"}]},{"featureType":"administrative.locality","elementType":"labels.text.fill","stylers":[{"visibility":"on"},{"color":"#808285"}]},{"featureType":"transit.station","stylers":[{"visibility":"on"}]},{"featureType":"transit","elementType":"geometry","stylers":[{"visibility":"on"},{"color":"#dbdbdb"}]},{"elementType":"labels.icon","stylers":[{"visibility":"on"},{"saturation":-100}]},{"featureType":"road","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"elementType":"labels.text","stylers":[{"visibility":"on"}]},{"featureType":"transit.line","elementType":"labels.text","stylers":[{"visibility":"off"}]}]
    };

    var map = new google.maps.Map(document.getElementById(mapid), myOptions);

    var marker = new google.maps.Marker({
        icon         : baseurl+"/modules/dpdfrance/views/img/front/relais/logo-max-png.png",
        position     : latlng,
        animation    : google.maps.Animation.DROP,
        map          : map
    });
}

function openDpdfranceDialog(id,mapid,lat,longti,baseurl){
    $("#header").css('z-index', 0);
    $("#dpdfrance_relais_filter").fadeIn(150, function() {$("#"+id).fadeIn(150);});
    window.setTimeout(function () {initializeDpdfranceGM(mapid,lat,longti,baseurl)},200);
}