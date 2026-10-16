<?php
// WHMCS module for the Streaming.Center Internet-Radio control panel.
// https://streaming.center

use WHMCS\Database\Capsule;
use WHMCS\View\Menu\Item;
use WHMCS\Product\Product;
use WHMCS\Service\Service;

require_once __DIR__ . '/lib/extensions.php';

if (!function_exists('streamingcenter_hooks_loadModule')) {
    /** Loads the module through the WHMCS module loader, so its functions are never declared twice. */
    function streamingcenter_hooks_loadModule()
    {
        if (function_exists('streamingcenter_makeApiRequest')) {
            return true;
        }
        try {
            if (class_exists('\\WHMCS\\Module\\Server')) {
                (new \WHMCS\Module\Server())->load('streamingcenter');
            }
        } catch (\Throwable $e) {
        }
        if (!function_exists('streamingcenter_makeApiRequest')) {
            require_once __DIR__ . '/streamingcenter.php';
        }
        return function_exists('streamingcenter_makeApiRequest');
    }
}

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

    # Based on the station name entered on the order form ("Station name" / "Radio name" custom field)
    $username = '';
    try {
        $username = preg_replace("/[^a-zA-Z]+/", "", streamingcenter_ascii((string) streamingcenter_stationName($params)));
    } catch (\Throwable $e) {
    }

    # Based on company name
    if(empty($username)){
        $username = preg_replace("/[^a-zA-Z]+/", "", $params['clientsdetails']['companyname']);
    }
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
            $ccoUrl = 'configproducts.php?action=edit&id=' . $productId . '&streamingcenter_create_options=1'
                . (function_exists('generate_token') ? '&token=' . generate_token('plain') : '');
            $ccoMessage = '';
            if (!empty($_SESSION['streamingcenter_cco_message'])) {
                $ccoMessage = json_encode($_SESSION['streamingcenter_cco_message']);
                unset($_SESSION['streamingcenter_cco_message']);
            }
            $ccoUrlJs = json_encode($ccoUrl);
            $ccoMessageJs = $ccoMessage ?: 'null';
            return <<<JAVASCRIPT
<script>
jQuery(document).ready(function(){
    // "Create configurable options" button (custom packages)
    function addCreateOptionsButton() {
        if (jQuery('#streamingcenter-cco').length || !jQuery("[name='packageconfigoption[1]']").length) {
            return;
        }
        var msg = {$ccoMessageJs};
        var box = jQuery('<div id="streamingcenter-cco" style="margin:15px 0"></div>');
        if (msg) {
            box.append(jQuery('<div class="alert"></div>').addClass(msg.ok ? 'alert-success' : 'alert-warning').text(msg.text));
        }
        box.append(jQuery('<a class="btn btn-default"></a>').attr('href', {$ccoUrlJs})
            .text('Create configurable options (custom packages)'));
        box.append(jQuery('<span class="text-muted" style="margin-left:10px"></span>')
            .text('Adds listeners, bitrate, disk, traffic and feature options to this product, priced at 0.'));
        jQuery("[name='packageconfigoption[1]']").closest('table').after(box);
    }
    addCreateOptionsButton();
    jQuery(document).ajaxComplete(function(event, xhr, settings) {
        if (settings.url.includes('configproducts.php')) {
            addCreateOptionsButton();
        }
    });
});
</script>
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

// "Create configurable options" button on the product's Module Settings tab
add_hook('AdminAreaPage', 1, function($vars) {
    if (($vars['filename'] ?? '') !== 'configproducts' || empty($_GET['streamingcenter_create_options']) || empty($_GET['id'])) {
        return [];
    }
    if (function_exists('check_token')) {
        check_token('WHMCS.admin.default');
    }
    $productId = (int) $_GET['id'];
    try {
        [$ok, $text] = streamingcenter_createConfigurableOptions($productId);
    } catch (\Throwable $e) {
        [$ok, $text] = [false, 'Could not create the configurable options: ' . $e->getMessage()];
    }
    $_SESSION['streamingcenter_cco_message'] = ['ok' => $ok, 'text' => $text];
    header('Location: configproducts.php?action=edit&id=' . $productId . '&tab=3');
    exit;
});

// Merge fields for welcome / product emails: {$radio_*}
add_hook('EmailTplMergeFields', 1, function($vars) {
    return [
        'radio_name'          => 'Streaming.Center: station name',
        'radio_username'      => 'Streaming.Center: login username',
        'radio_panel_url'     => 'Streaming.Center: broadcaster panel URL',
        'radio_server_host'   => 'Streaming.Center: server for live broadcasting',
        'radio_listen_url'    => 'Streaming.Center: main stream URL',
        'radio_streams_html'  => 'Streaming.Center: all stream URLs (HTML)',
        'radio_max_listeners' => 'Streaming.Center: listener limit',
        'radio_max_bitrate'   => 'Streaming.Center: bitrate limit',
        'radio_storage'       => 'Streaming.Center: disk space',
        'radio_traffic'       => 'Streaming.Center: monthly traffic',
        'radio_features'      => 'Streaming.Center: enabled features',
    ];
});

add_hook('EmailPreSend', 1, function($vars) {
    $serviceId = (int) ($vars['relid'] ?? 0);
    if ($serviceId <= 0) {
        return [];
    }
    try {
        // relid is a service id only for Product/Service email templates
        $type = Capsule::table('tblemailtemplates')->where('name', (string) ($vars['messagename'] ?? ''))->value('type');
        if ($type !== 'product') {
            return [];
        }
        $service = Capsule::table('tblhosting')
            ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
            ->where('tblhosting.id', $serviceId)
            ->where('tblproducts.servertype', 'streamingcenter')
            ->select('tblhosting.userid', 'tblhosting.server', 'tblhosting.username', 'tblhosting.password')
            ->first();
        if (!$service || (string) $service->username === '') {
            return [];
        }
        $server = Capsule::table('tblservers')->where('id', (int) $service->server)->first();
        if (!$server || !streamingcenter_hooks_loadModule()) {
            return [];
        }
        $params = [
            'serverid'         => (int) $server->id,
            'serverhostname'   => (string) ($server->hostname ?: $server->ipaddress),
            'serverhttpprefix' => $server->secure ? 'https' : 'http',
            'serverport'       => (string) ($server->port ?: '2345'),
            'serverusername'   => (string) $server->username,
            'serverpassword'   => (string) decrypt($server->password),
            'username'         => (string) $service->username,
            'password'         => (string) decrypt($service->password),
        ];
        $info = streamingcenter_info($params, false);
        if (!$info) {
            return [];
        }

        $language = Capsule::table('tblclients')->where('id', (int) $service->userid)->value('language')
            ?: Capsule::table('tblconfiguration')->where('setting', 'Language')->value('value');
        $L = streamingcenter_langFile($language);

        $streamsHtml = '';
        $listenUrl = '';
        foreach ($info['channels'] as $channel) {
            if ($channel['url'] === '') {
                continue;
            }
            $listenUrl = $listenUrl ?: $channel['url'];
            $streamsHtml .= '<div style="margin:0 0 6px">' . htmlspecialchars($channel['name'] ?: 'Stream') . ': '
                . '<a href="' . htmlspecialchars($channel['url']) . '">' . htmlspecialchars($channel['url']) . '</a></div>';
        }
        $features = [];
        foreach ($info['features'] as $flag => $on) {
            if ($on && isset($L['feature_' . $flag])) {
                $features[] = $L['feature_' . $flag];
            }
        }
        $limits = $info['limits'];

        return [
            'radio_name'          => $info['account']['station_name'] ?: (string) $service->username,
            'radio_username'      => (string) $service->username,
            'radio_panel_url'     => $info['account']['panel_url'],
            'radio_server_host'   => $info['account']['host'],
            'radio_listen_url'    => $listenUrl,
            'radio_streams_html'  => $streamsHtml,
            'radio_max_listeners' => $limits['listeners'] ?: $L['unlimited'],
            'radio_max_bitrate'   => $limits['bitrate'] ? $limits['bitrate'] . ' kbps' : $L['unlimited'],
            'radio_storage'       => streamingcenter_formatMb($limits['du_mb'], $L['unlimited']),
            'radio_traffic'       => streamingcenter_formatMb($limits['traffic_mb'], $L['unlimited']),
            'radio_features'      => implode(', ', $features),
        ];
    } catch (\Throwable $e) {
        logActivity('Streaming.Center: email sent without station details (service #' . $serviceId . '): ' . $e->getMessage());
        return [];
    }
});
