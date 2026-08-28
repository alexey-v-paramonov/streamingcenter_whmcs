<?php
// WHMCS module for the Streaming.Center Internet-Radio control panel.
// https://streaming.center

use WHMCS\Database\Capsule;
use WHMCS\View\Menu\Item;
use WHMCS\Product\Product;
use WHMCS\Service\Service;

add_hook('OverrideModuleUsernameGeneration', 1, function(array $params) {

    $MAX_CHARS = 16;

    if ($params['moduletype'] !== 'streamingcenter') {
        return null;
    }

    // Record the call in WHMCS's module log.
    logModuleCall(
        'streamingcenter',
        __FUNCTION__,
        "Generating username",
        "username",
        "username"
    );

    # Based on company name
    $username = preg_replace("/[^a-zA-Z]+/", "", $params['clientsdetails']['companyname']);
    if(empty($username)){
        # Use user name
        $username = preg_replace("/[^a-zA-Z]+/", "", $params['clientsdetails']['fullname']);
        if(empty($username)){
            $username = "radio";
        }
    }

    logModuleCall(
        'streamingcenter',
        __FUNCTION__,
        "Generating username, base: ".$username,
        "username",
        "username"
    );

    $n = 0;
    while ($n < 1000) {

        $test_username = substr($username, 0, $MAX_CHARS - strlen((string)$n)) . $n;

        if ($n === 0) {
            $test_username = substr($username, 0, $MAX_CHARS);
        }

        if (!Capsule::table('tblhosting')->where('username', '=', $test_username)->exists()) {
            logModuleCall(
                'streamingcenter',
                __FUNCTION__,
                "New username: " . $test_username,
                "username",
                "username"
            );

            return strtolower($test_username);
        }

        $n += 1;
    }

    $length = 8;
    $characters = 'abcdefghijklmnopqrstuvwxyz';
    $string = '';

    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[random_int(0, strlen($characters) - 1)];
    }

    return $string;
});

// Admin area page tweaks
add_hook('AdminAreaFooterOutput', 1, function($vars) {

    // Client's products/services page.
    // Only apply tweaks when the service being viewed uses the streamingcenter
    // module, otherwise the global [name='username'] selector would also
    // lock the username field of services belonging to other modules.
    if ($vars['filename'] === 'clientsservices' && isset($_GET['id'])) {
        $service = Service::find((int) $_GET['id']);
        if (!$service || !$service->product || $service->product->module !== 'streamingcenter') {
            return;
        }
return <<<JAVASCRIPT
<script>
jQuery(document).ready(function(){
    function tweakStreamingInfo() {
        // Guard against the module being switched to a different one on the page.
        if (jQuery("select[name='module']").val() !== 'streamingcenter') {
            return;
        }
        jQuery("[name='username']").attr('readonly','readonly').css("backgroundColor","#eee").css("pointerEvents","none");
        jQuery("[name='server']").attr('readonly','readonly');
        jQuery("[name='streamingcenter_panel_port']").attr('readonly','readonly').css("backgroundColor","#eee").css("pointerEvents","none");
    }

    tweakStreamingInfo();
    jQuery(document).ajaxComplete(function(event, xhr, settings) {
        if (settings.url.includes('clientsservices.php')) {
            tweakStreamingInfo();
        }
    });

});
</script>
JAVASCRIPT;
    }

    // Product configuration page
    if (
        $vars['filename'] === 'configproducts' &&
        isset($_GET['action']) &&
        $_GET['action'] === 'edit' &&
        isset($_GET['id'])
    ) {
        $productId = (int) $_GET['id'];
        $product = Product::find($productId);
        if ($product && $product->module === 'streamingcenter') {    
            return <<<JAVASCRIPT
<script>
jQuery(document).ready(function(){
    var watchSelector = "[name='packageconfigoption[1]']";

    function updateVisibility() {
        var val = jQuery(watchSelector).val();
        var nonTemplateSettins = jQuery(".form.module-settings").find("input, select").not('[name="packageconfigoption[1]"]');
        if (val === 'no_template') {
            nonTemplateSettins.prop('disabled', false);
        } else {
            nonTemplateSettins.prop('disabled', true);
        }
    }


    updateVisibility();
    jQuery(document).on('change', watchSelector, updateVisibility);
    jQuery(document).ajaxComplete(function(event, xhr, settings) {
        if (settings.url.includes('configproducts.php')) {
            updateVisibility();

            // Individual fields setup
            var limit_stations = jQuery('input[name="packageconfigoption[3]"]');
            limit_stations.attr('min', '0').attr('type', 'number');
            if(limit_stations.val() == ''){
                limit_stations.val('0');
            }
            var limit_streams = jQuery('input[name="packageconfigoption[4]"]');
            limit_streams.attr('min', '0').attr('type', 'number');
            if(limit_streams.val() == ''){
                limit_streams.val('0');
            }
            var limit_listeners = jQuery('input[name="packageconfigoption[5]"]');
            limit_listeners.attr('min', '0').attr('type', 'number');
            if(limit_listeners.val() == ''){
                limit_listeners.val('0');
            }
            var limit_du = jQuery('input[name="packageconfigoption[7]"]');
            limit_du.attr('min', '0').attr('type', 'number');
            if(limit_du.val() == ''){
                limit_du.val('0');
            }
            var limit_traffic = jQuery('input[name="packageconfigoption[8]"]');
            limit_traffic.attr('min', '0').attr('type', 'number');
            if(limit_traffic.val() == ''){
                limit_traffic.val('0');
            }
        }
    });    
});
</script>
JAVASCRIPT;
    }
 }
});
