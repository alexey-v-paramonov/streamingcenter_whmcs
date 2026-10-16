<?php
// WHMCS module for the Streaming.Center Internet-Radio control panel.
// https://streaming.center

use Carbon\Carbon;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

require_once __DIR__ . '/lib/extensions.php';

function streamingcenter_MetaData()
{
    return array(
        'DisplayName' => 'Streaming.Center',
        'RequiresServer' => true,
        'DefaultNonSSLPort' => '2345',
        'DefaultSSLPort' => '2345',
        'ServiceSingleSignOnLabel' => 'Login to Streaming.Center Broadcaster interface',
        'AdminSingleSignOnLabel' => 'Login to Streaming.Center Admin area',
        'ListAccountsUniqueIdentifierDisplayName' => 'Username',
        'ListAccountsUniqueIdentifierField' => 'username',
        'ListAccountsProductField' => 'configoption1',
    );
}

function streamingcenter_ConfigOptions() {
    return [
        # configoption1
        "template" => [
            "FriendlyName" => "Radio Account Template",
            "Type"         => "text",
            "Size"         => "50",
            "Loader"       => "streamingcenter_LoadBroadcasterTemplates",
            "Description"  => "<br />Created in Streaming.Center admin area",
            "Default"      => "",
            "SimpleMode"   => true,
        ],
        # configoption2
        "server_type" => [
            "FriendlyName" => "Streaming Server Type",
            "Type"         => "dropdown",
            "Options"      => [
                'icecast-kh' => 'Icecast-KH',
                'icecast' => 'Icecast',
                'shoutcast1' => 'Shoutcast 1',
                'shoutcast2' => 'Shoutcast 2',
                'shoutcast2.6' => 'Shoutcast 2.6',
            ],
            "Description" => "Select the type of streaming server to use for this account. Icecast-KH is recommended for best compatibility.",
            "Default"      => "icecast-kh",
            "SimpleMode"   => true,
        ],
        # configoption3
        "limit_stations" => [
            "FriendlyName" => "Radio stations limit",
            "Type"         => "text",
            "Description"  => "0 For unlimited",
            "Default"      => "0",
            "SimpleMode"   => true,
        ],
        # configoption4
        "limit_streams" => [
            "FriendlyName" => "Radio channels limit",
            "Type"         => "text",
            "Description"  => "0 For unlimited",
            "Default"      => "0",
            "SimpleMode"   => true,
        ],
        # configoption5
        "limit_listeners" => [
            "FriendlyName" => "Listeners limit",
            "Type"         => "text",
            "Description"  => "0 For unlimited",
            "Default"      => "0",
            "SimpleMode"   => true,
        ],

        # configoption6
        "limit_bitrate" => [
            "FriendlyName" => "Maximum bitrate",
            "Type"         => "dropdown",
            "Options"      => [
                '0' => 'Unlimited',
                '24' => '24 kbps',
                '32' => '32 kbps',
                '64' => '64 kbps',
                '96' => '96 kbps',
                '128' => '128 kbps',
                '192' => '192 kbps',
                '256' => '256 kbps',
                '320' => '320 kbps',
            ],
            "Description" => "<br/ > Set the maximum bitrate for this account.",
            "Default"      => "0",
            "SimpleMode"   => true,
        ],
        # configoption7
        "limit_du" => [
            "FriendlyName" => "Disk quota (megabytes)",
            "Type"         => "text",
            "Description"  => "0 For unlimited",
            "Default"      => "0",
            "SimpleMode"   => true,
        ],
        # configoption8
        "limit_traffic" => [
            "FriendlyName" => "Traffic per month (megabytes)",
            "Type"         => "text",
            "Description"  => "0 For unlimited",
            "Default"      => "0",
            "SimpleMode"   => true,
        ],
        # configoption9 — DEPRECATED, replaced by "Social streams limit" (configoption21).
        # Kept only to hold its slot: WHMCS stores options by POSITION, so removing it
        # would shift every later option onto the wrong stored value. Not sent to the
        # server, and out of Simple Mode so it does not show on the default screen.
        "youtube_streaming_enabled" => [
            "FriendlyName" => "(Deprecated) Allow Youtube streaming",
            "Type"         => "yesno",
            "Description"  => "Unused — see Social streams limit",
            "Default"      => "no",
            "SimpleMode"   => false,
        ],
        # configoption10 — DEPRECATED, replaced by "Social streams limit" (configoption21).
        # Kept only to hold its slot: WHMCS stores options by POSITION, so removing it
        # would shift every later option onto the wrong stored value. Not sent to the
        # server, and out of Simple Mode so it does not show on the default screen.
        "fb_streaming_enabled" => [
            "FriendlyName" => "(Deprecated) Allow Facebook streaming",
            "Type"         => "yesno",
            "Description"  => "Unused — see Social streams limit",
            "Default"      => "no",
            "SimpleMode"   => false,
        ],
        # configoption11 — DEPRECATED, replaced by "Social streams limit" (configoption21).
        # Kept only to hold its slot: WHMCS stores options by POSITION, so removing it
        # would shift every later option onto the wrong stored value. Not sent to the
        # server, and out of Simple Mode so it does not show on the default screen.
        "vk_streaming_enabled" => [
            "FriendlyName" => "(Deprecated) Allow VK.com streaming",
            "Type"         => "yesno",
            "Description"  => "Unused — see Social streams limit",
            "Default"      => "no",
            "SimpleMode"   => false,
        ],
        # configoption12 — DEPRECATED, replaced by "Social streams limit" (configoption21).
        # Kept only to hold its slot: WHMCS stores options by POSITION, so removing it
        # would shift every later option onto the wrong stored value. Not sent to the
        # server, and out of Simple Mode so it does not show on the default screen.
        "tg_streaming_enabled" => [
            "FriendlyName" => "(Deprecated) Allow Telegram streaming",
            "Type"         => "yesno",
            "Description"  => "Unused — see Social streams limit",
            "Default"      => "no",
            "SimpleMode"   => false,
        ],
        # configoption13
        "geoblocking_enabled" => [
            "FriendlyName" => "Enable GeoBlocking",
            "Type"         => "yesno",
            "Default"      => "no",
            "SimpleMode"   => true,
        ],

        # configoption14
        "stereo_tool_enabled" => [
            "FriendlyName" => "StereoTool",
            "Type"         => "yesno",
            "Description"  => "Enable StereoTool audio processing",
            "Default"      => "no",
            "SimpleMode"   => true,
        ],

        # configoption15
        "url_branding_enabled" => [
            "FriendlyName" => "Allow URL Branding",
            "Type"         => "yesno",
            "Description"  => "User can set custom URL for the station",
            "Default"      => "no",
            "SimpleMode"   => true,
        ],

        # configoption16
        "website_enabled" => [
            "FriendlyName" => "Enable radio mini-Website",
            "Type"         => "yesno",
            "Description"  => "Radio station starter page",
            "Default"      => "no",
            "SimpleMode"   => true,
        ],

        # configoption17
        "tts_enabled" => [
            "FriendlyName" => "Enable Text-to-Speech",
            "Type"         => "yesno",
            "Description"  => "Convert text to speech for the station, additional costs applies",
            "Default"      => "no",
            "SimpleMode"   => true,
        ],
        
        # configoption18
        "podcasts_enabled" => [
            "FriendlyName" => "Enable Podcasts",
            "Type"         => "yesno",
            "Description"  => "Allow podcasts public page and management",
            "Default"      => "no",
            "SimpleMode"   => true,
        ],
    
        # configoption19
        "save_dj_streams_enabled" => [
            "FriendlyName" => "Enable Live stream recording",
            "Type"         => "yesno",
            "Description"  => "High disk space consumption",
            "Default"      => "no",
            "SimpleMode"   => true,
        ],
        # configoption20
        "hls_enabled" => [
            "FriendlyName" => "Allow HLS streaming",
            "Type"         => "yesno",
            "Description"  => "HLS listeners do not count toward the listener limit",
            "Default"      => "no",
            "SimpleMode"   => true,
        ],
        # configoption21 — replaces the four per-platform switches (9-12).
        # Blank by default ON PURPOSE: blank means "take the template's value" (and
        # leaves an existing account's value alone on a package change). A default of
        # "0" would be sent explicitly and override every template's limit with "off".
        "limit_social_streams" => [
            "FriendlyName" => "Social streams limit",
            "Type"         => "text",
            "Description"  => "Streams to YouTube, Telegram and other platforms, across all stations. 0 = off. Blank = use the template (no template: off).",
            "Default"      => "",
            "SimpleMode"   => true,
        ],
    ];
}

/**
 * The social-stream limit to send, or null to send none.
 *
 * Blank (the product predates the option, or deliberately takes the template's
 * value) returns null, so the server keeps what it has.
 */
function streamingcenter_socialLimit(array $params) {
    $value = isset($params['configoption21']) ? trim((string)$params['configoption21']) : '';
    if ($value === '' || !ctype_digit($value)) {
        return null;
    }
    return (int)$value;
}

/**
 * The four deprecated per-platform switches, for the request.
 *
 * Still SENT, although the product no longer shows them. This module is upgraded on
 * each provider's WHMCS independently of the admin panel it talks to, and an admin
 * panel that predates the social-streams limit builds its package change from these
 * four values: a request without them reaches its serializer as null, which a
 * boolean field rejects, and the whole package change (or admin-tab save) fails. A
 * current panel stores them and acts only on limit_social_streams.
 *
 * $values: platform flag => truthy/falsy, in the shape the endpoint already got.
 */
function streamingcenter_legacySocialFlags(array $values) {
    $out = [];
    foreach (['youtube_streaming_enabled', 'fb_streaming_enabled',
              'vk_streaming_enabled', 'tg_streaming_enabled'] as $flag) {
        $out[$flag] = $values[$flag];
    }
    return $out;
}

/**
 * An API error as one readable string.
 *
 * The panel answers some failures with a STRING ("username_exists") and validation
 * failures with its serializer's errors — an OBJECT of field => [messages], e.g.
 * {"limit_social_streams": ["reseller_social_limit_exceeded"]}. Callers use the
 * error as an array key and concatenate it into a message, which with an object
 * threw TypeError / Error — neither is an Exception, so the module's own catch
 * blocks did not stop them and provisioning or the package change crashed.
 *
 * A lone non-field error becomes "non_field_errors_<code>", the shape the
 * CreateAccount message table already uses; anything else "field: msg, msg; ...".
 */
function streamingcenter_errorText($error) {
    if (is_string($error)) {
        return $error;
    }
    if (is_scalar($error) || $error === null) {
        return (string)$error;
    }
    $parts = [];
    foreach ((array)$error as $field => $messages) {
        $list = [];
        foreach ((is_array($messages) || is_object($messages)) ? (array)$messages : [$messages] as $m) {
            $list[] = (is_array($m) || is_object($m)) ? streamingcenter_errorText($m) : (string)$m;
        }
        $parts[] = [$field, $list];
    }
    if (count($parts) === 1 && $parts[0][0] === 'non_field_errors' && count($parts[0][1]) === 1) {
        return 'non_field_errors_' . $parts[0][1][0];
    }
    $out = [];
    foreach ($parts as [$field, $list]) {
        $out[] = (is_int($field) ? '' : $field . ': ') . implode(', ', $list);
    }
    return implode('; ', $out);
}

/**
 * Make API request to Streaming.Center server
 */
function streamingcenter_makeApiRequest($params, $endpoint, $method = 'POST', $postData = []) {
    $url = $params['serverhttpprefix'] . "://" . $params['serverhostname'] . ":" . $params['serverport'] . $endpoint;
    
    if (!extension_loaded('curl')) {
        return [
            'success' => false,
            'error' => 'CURL extension is not installed'
        ];
    }
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERPWD => $params['serverusername'] . ":" . $params['serverpassword'],
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
    ]);
    
    // Add server credentials for API
    $defaultParams = [
        'server_username' => $params['serverusername'],
        'server_password' => $params['serverpassword']
    ];
    
    $allParams = array_merge($defaultParams, $postData);
    
    if ($method === 'GET') {
        $queryString = http_build_query($allParams);
        curl_setopt($ch, CURLOPT_URL, $url . '?' . $queryString);
    } elseif ($method === 'PATCH') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($allParams));
    } else {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($allParams));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    // No-op since PHP 8.0, and deprecated in 8.5, where it logs a notice per call.
    if (PHP_VERSION_ID < 80000) {
        curl_close($ch);
    }
    
    if ($response === false || !empty($curlError)) {
        return [
            'success' => false,
            'error' => 'CURL Error: ' . $curlError
        ];
    }
    
    $data = json_decode($response);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'success' => false,
            'error' => 'Invalid JSON response: ' . json_last_error_msg()
        ];
    }
    
    // Always a string from here on: see streamingcenter_errorText().
    if ($httpCode !== 200) {
        $errorMsg = isset($data->error) ? streamingcenter_errorText($data->error)
                                        : 'HTTP Error ' . $httpCode;
        return [
            'success' => false,
            'error' => $errorMsg
        ];
    }
    
    if (isset($data->error)) {
        return [
            'success' => false,
            'error' => streamingcenter_errorText($data->error)
        ];
    }
    
    return [
        'success' => true,
        'data' => $data
    ];
}

function streamingcenter_ListAccounts(array $params) {
    $accounts = [];
    $endpoint = "/api/v1/remote_get_all_broadcasters/";
    
    try {
        logModuleCall('streamingcenter', __FUNCTION__, $params, "start", "start");
        
        $response = streamingcenter_makeApiRequest($params, $endpoint, 'GET');
        
        if (!$response['success']) {
            return [
                'success' => false,
                'error' => $response['error']
            ];
        }
        
        $data = $response['data'];
        if (isset($data->broadcasters) && is_array($data->broadcasters)) {
            foreach ($data->broadcasters as $account) {
                $joinDate = isset($account->date_joined) ? Carbon::parse($account->date_joined) : Carbon::now();
                
                // Map remote status to WHMCS domain status
                $domainStatus = 'Active';
                if (isset($account->account_status)) {
                    switch ($account->account_status) {
                        case 3:
                            $domainStatus = 'Suspended';
                            break;
                        case 1:
                            $domainStatus = 'Terminated';
                            break;
                    }
                }
                
                $accounts[] = [
                    'email' => $account->email ?? '',
                    'username' => $account->username ?? '',
                    'password' => $account->password_plain ?? '',
                    'uniqueIdentifier' => $account->username ?? '',
                    'product' => '',
                    'primaryip' => '',
                    'created' => $joinDate->format('Y-m-d H:i:s'),
                    'domainstatus' => $domainStatus,
                    'status' => $domainStatus,
                ];
            }
        }
        
        logModuleCall('streamingcenter', __FUNCTION__, "success", count($accounts), count($accounts));
        
        return [
            'success' => true,
            'accounts' => $accounts,
        ];
        
    } catch (Exception $e) {
        logModuleCall('streamingcenter', __FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString());
        return [
            'success' => false,
            'error' => $e->getMessage(),
        ];
    }
}

function streamingcenter_LoadBroadcasterTemplates($params){
    $endpoint = "/api/v1/remote_get_templates/";

    try {
        logModuleCall('streamingcenter', __FUNCTION__, $params, "start", "start");
        
        $response = streamingcenter_makeApiRequest($params, $endpoint, 'POST');
        
        if (!$response['success']) {
            return ['no_template' => 'Error: failed to load templates: ' . $response['error']];
        }
        
        $data = $response['data'];
        $list = [];
        
        if (isset($data->broadcaster_templates)) {
            foreach ($data->broadcaster_templates as $template_name) {
                $list[$template_name] = "Broadcaster: " . $template_name;
            }
        }
        
        if (isset($data->reseller_templates)) {
            foreach ($data->reseller_templates as $template_name) {
                $list[$template_name] = "Reseller: " . $template_name;
            }
        }
        
        $list['no_template'] = 'No Template';
        
        logModuleCall('streamingcenter', __FUNCTION__, "success", count($list), count($list));
        return $list;
        
    } catch (Exception $e) {
        logModuleCall('streamingcenter', __FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString());
        throw new Exception($e->getMessage());
    }
}

function streamingcenter_ClientArea(array $params)
{
    $L = streamingcenter_lang($params);
    $vars = [
        'L'      => $L,
        'L_json' => json_encode([
            'copy' => $L['copy'], 'copied' => $L['copied'], 'listen' => $L['listen'], 'stop' => $L['stop'],
            'loading' => $L['loading'], 'cant_play' => $L['cant_play'],
        ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
        'error'  => null,
    ];

    try {
        $info = streamingcenter_info($params);
        if (!$info) {
            throw new Exception('Account ' . $params['username'] . ' was not found on the server.');
        }
        $vars = array_merge($vars, $info);
        $vars['stationName'] = $info['account']['station_name'] ?: (streamingcenter_stationName($params) ?: $params['username']);

        // Server type: configurable option, product setting (without template) or the channel type
        $labels = [
            'icecast-kh' => 'Icecast-KH', 'icecast' => 'Icecast', 'shoutcast1' => 'Shoutcast 1',
            'shoutcast2' => 'Shoutcast 2', 'shoutcast2.6' => 'Shoutcast 2.6',
        ];
        $overrides = streamingcenter_overrides($params);
        $type = $overrides['server_type'] ?? ($params['configoption1'] == 'no_template' ? $params['configoption2'] : '');
        if (!$type && $info['channel_type'] !== '') {
            $type = streamingcenter_serverType($info['channel_type']) ?: '';
        }
        $vars['serverTypeLabel'] = $labels[$type] ?? ucfirst((string) $info['channel_type']);

        $pct = function ($used, $limit) {
            return $limit > 0 ? min(100, (int) round($used / $limit * 100)) : 0;
        };
        $vars['pct'] = [
            'listeners' => $pct($info['usage']['listeners'], $info['limits']['listeners']),
            'du'        => $pct($info['usage']['du_mb'], $info['limits']['du_mb']),
            'traffic'   => $pct($info['usage']['traffic_mb'], $info['limits']['traffic_mb']),
            'stations'  => $pct($info['usage']['stations'], $info['limits']['stations']),
        ];
        $vars['fmt'] = [
            'du_used'       => streamingcenter_formatMb($info['usage']['du_mb'], '0 MB'),
            'du_limit'      => streamingcenter_formatMb($info['limits']['du_mb']),
            'traffic_used'  => streamingcenter_formatMb($info['usage']['traffic_mb'], '0 MB'),
            'traffic_limit' => streamingcenter_formatMb($info['limits']['traffic_mb']),
        ];
        $vars['listenUrl'] = '';
        foreach ($info['channels'] as $channel) {
            if ($channel['url'] !== '' && ($channel['active'] || $vars['listenUrl'] === '')) {
                $vars['listenUrl'] = $channel['url'];
                if ($channel['active']) {
                    break;
                }
            }
        }
    } catch (Exception $e) {
        logModuleCall('streamingcenter', __FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString());
        $vars['error'] = $e->getMessage();
    }

    return [
        'tabOverviewReplacementTemplate' => 'templates/clientarea.tpl',
        'templateVariables'              => $vars,
    ];
}

function streamingcenter_ServiceSingleSignOn(array $params, $from_client = false)
{
    $endpoint = "/api/v1/remote_user_login/";
    
    try {
        logModuleCall('streamingcenter', __FUNCTION__, $params, "start", "start");
        
        $postData = [
            'username' => $params['username'],
            'password' => $params['password']
        ];
        
        $response = streamingcenter_makeApiRequest($params, $endpoint, 'POST', $postData);
        
        if (!$response['success']) {
            if($from_client){
                return [];
            }
            return [
                'success' => false,
                'errorMsg' => $response['error']
            ];
        }
        
        $data = $response['data'];
        if (isset($data->url)) {
            logModuleCall('streamingcenter', __FUNCTION__, "success", "redirectTo", $data->url);
            if($from_client){
                // If called from client area, return redirect URL
                return [
                    'url' => $data->url
                ];
            }
            return [
                'success' => true,
                'redirectTo' => $data->url
            ];

        }
        if($from_client){
            return [];
        }
        return [
            'success' => false,
            'errorMsg' => 'No redirect URL received'
        ];        
        
    } catch (Exception $e) {
        logModuleCall('streamingcenter', __FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString());
        if($from_client){
            return [];
        }

        return [
            'success' => false,
            'errorMsg' => $e->getMessage(),
        ];
    }
}

function streamingcenter_AdminSingleSignOn(array $params)
{
    $endpoint = "/api/v1/remote_admin_login/";
    
    try {
        logModuleCall('streamingcenter', __FUNCTION__, $params, "start", "start");
        
        $postData = [
            'username' => $params['serverusername'],
            'password' => $params['serverpassword']
        ];
        
        $response = streamingcenter_makeApiRequest($params, $endpoint, 'POST', $postData);
        
        if (!$response['success']) {
            return [
                'success' => false,
                'errorMsg' => $response['error']
            ];
        }
        
        $data = $response['data'];
        if (isset($data->token)) {
            $redirectUrl = $params['serverhttpprefix'] . "://" . $params['serverhostname'] . ":" . $params['serverport'] . "/login/?token=" . $data->token;
            
            logModuleCall('streamingcenter', __FUNCTION__, "success URL: " . $redirectUrl, "success", "success");
            return [
                'success' => true,
                'redirectTo' => $redirectUrl,
            ];
        }
        
        return [
            'success' => false,
            'errorMsg' => 'No token received'
        ];
        
    } catch (Exception $e) {
        logModuleCall('streamingcenter', __FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString());
        return [
            'success' => false,
            'errorMsg' => $e->getMessage(),
        ];
    }
}

function streamingcenter_CreateAccount(array $params)
{
    $endpoint = "/api/v1/remote_bcaster_create_from_template/";
    
    try {
        logModuleCall('streamingcenter', __FUNCTION__, "Creating account, username: " . $params['username'], "start", "start");
        
        $postData = [
            'username' => $params['username'],
            'email' => $params['clientsdetails']['email'],
            'password' => $params['password']
        ];
        
        // Handle template configuration
        if ($params['configoption1'] != 'no_template') {
            $postData['template'] = $params['configoption1'];
        } else {
            // Create a broadcaster account without a template
            $postData = array_merge($postData, [
                'template' => "no_template",
                'server_type' => $params['configoption2'],
                'limit_stations' => $params['configoption3'],
                'limit_streams' => $params['configoption4'],
                'limit_listeners' => $params['configoption5'],
                'limit_bitrate' => $params['configoption6'],
                'limit_du' => $params['configoption7'],
                'limit_traffic' => $params['configoption8'],
            ], streamingcenter_legacySocialFlags([
                // Raw values ('on' / ''), exactly as this request always sent them.
                'youtube_streaming_enabled' => $params['configoption9'] ?? '',
                'fb_streaming_enabled' => $params['configoption10'] ?? '',
                'vk_streaming_enabled' => $params['configoption11'] ?? '',
                'tg_streaming_enabled' => $params['configoption12'] ?? '',
            ]), [
                'geoblocking_enabled' => $params['configoption13'],
                'stereo_tool_enabled' => $params['configoption14'],
                'url_branding_enabled' => $params['configoption15'],
                'website_enabled' => $params['configoption16'],
                'tts_enabled' => $params['configoption17'],
                'podcasts_enabled' => $params['configoption18'],
                'save_dj_streams_enabled' => $params['configoption19'],
            ]);        
        }
        // HLS only WITHOUT a template. With one, the template decides, as it does for
        // every other feature switch — and the product screen disables this checkbox
        // when a template is chosen (hooks.php), so its value there means nothing.
        // WHMCS passes every option slot, blank when unset, so "send it when the key
        // exists" sent an explicit OFF for every template product and switched HLS
        // off on accounts whose template enables it.
        if ($params['configoption1'] == 'no_template') {
            $postData['hls_enabled'] = ($params['configoption20'] ?? '');
        }
        if (streamingcenter_socialLimit($params) !== null) {
            $postData['limit_social_streams'] = streamingcenter_socialLimit($params);
        }

        // Customer's configurable options (custom package). Without a template they go straight
        // into the create request; with a template they are applied right after creation.
        if ($params['configoption1'] == 'no_template') {
            foreach (streamingcenter_overrides($params) as $key => $value) {
                $postData[$key] = is_bool($value) ? ($value ? 'on' : '') : $value;
            }
        }

        // A retried Create must not fail on "username exists" when the account is the one this
        // service already created (same username and same e-mail): just finish the setup below.
        $existing = streamingcenter_getDetails($params);
        if ($existing && strcasecmp((string) ($existing['email'] ?? ''), (string) $params['clientsdetails']['email']) === 0) {
            $response = ['success' => true];
        } else {
            $response = streamingcenter_makeApiRequest($params, $endpoint, 'POST', $postData);
        }
        
        if (!$response['success']) {
            $messages = [
                'invalid_params' => "Missing required parameters",
                'username_exists' => "Username already exists",
                'empty_username' => "Missing account username",
                'username_unique' => "Username already exists",
                'non_field_errors_licence_no_more_stations' => "Your license does not allow creating more broadcaster accounts. To create more accounts update your license",
            ];
            
            $errorKey = $response['error'];
            $errorMsg = isset($messages[$errorKey]) ? $messages[$errorKey] : $errorKey;
            
            return "Error: " . $errorMsg;
        }

        // Station name from the order form, and configurable options on top of a template.
        // The account is set up in the background, so allow a few attempts to load it.
        $error = streamingcenter_applyOverrides($params, streamingcenter_stationName($params), 5);
        if ($error !== null) {
            return "Error: the account was created, but the custom package / station name could not be applied ("
                . $error . "). Run Create again to finish the setup.";
        }
        
        logModuleCall('streamingcenter', __FUNCTION__, "success", "success", "success");
        return 'success';
        
    } catch (Exception $e) {
        logModuleCall('streamingcenter', __FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString());
        return "Exception: " . $e->getMessage();
    }
}

function streamingcenter_ChangePackage(array $params) {
    $endpoint = "/api/v1/remote_update_client/";
    
    try {
        logModuleCall('streamingcenter', __FUNCTION__, $params, "start", "start");
        
        $postData = [
            'username' => $params['username'],
        ];

        if ($params['configoption1'] != 'no_template') {
            $postData['template'] = $params['configoption1'];
        } else {
            $postData = array_merge($postData, [
                'template' => "no_template",
                'server_type' => $params['configoption2'],
                'limit_stations' => $params['configoption3'],
                'limit_streams' => $params['configoption4'],
                'limit_listeners' => $params['configoption5'],
                'limit_bitrate' => $params['configoption6'],
                'limit_du' => $params['configoption7'],
                'limit_traffic' => $params['configoption8'],
            ], streamingcenter_legacySocialFlags([
                // Booleans, exactly as this request always sent them.
                'youtube_streaming_enabled' => ($params['configoption9'] ?? '') == 'on',
                'fb_streaming_enabled' => ($params['configoption10'] ?? '') == 'on',
                'vk_streaming_enabled' => ($params['configoption11'] ?? '') == 'on',
                'tg_streaming_enabled' => ($params['configoption12'] ?? '') == 'on',
            ]), [
                'geoblocking_enabled' => $params['configoption13'] == 'on' ? true : false,
                'stereo_tool_enabled' => $params['configoption14'] == 'on' ? true : false,
                'url_branding_enabled' => $params['configoption15'] == 'on' ? true : false,
                'website_enabled' => $params['configoption16'] == 'on' ? true : false,
                'tts_enabled' => $params['configoption17'] == 'on' ? true : false,
                'podcasts_enabled' => $params['configoption18'] == 'on' ? true : false,
                'save_dj_streams_enabled' => $params['configoption19'] == 'on' ? true : false,
            ]);        
        }
        // Only without a template; see CreateAccount.
        if ($params['configoption1'] == 'no_template') {
            $postData['hls_enabled'] = ($params['configoption20'] ?? '') == 'on';
        }
        // Blank = the product predates the option or takes the template's value:
        // send nothing and the account keeps its limit.
        if (streamingcenter_socialLimit($params) !== null) {
            $postData['limit_social_streams'] = streamingcenter_socialLimit($params);
        }
        
        $response = streamingcenter_makeApiRequest($params, $endpoint, 'PATCH', $postData);
        
        if (!$response['success']) {
            return "Error: " . $response['error'];
        }

        // Configurable options (custom package) on top of the product settings / template
        $error = streamingcenter_applyOverrides($params);
        if ($error !== null) {
            return "Error: " . $error;
        }
        
        logModuleCall('streamingcenter', __FUNCTION__, "success", "success", "success");
        return 'success';
        
    } catch (Exception $e) {
        logModuleCall('streamingcenter', __FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString());
        return "Exception: " . $e->getMessage();
    }
}

function streamingcenter_SuspendAccount(array $params) {
    $endpoint = "/api/v1/remote_user_suspend/";
    
    try {
        logModuleCall('streamingcenter', __FUNCTION__, $params, "start", "start");
        
        $postData = [
            'username' => $params['username'],
            'password' => $params['password']
        ];
        
        $response = streamingcenter_makeApiRequest($params, $endpoint, 'POST', $postData);
        
        if (!$response['success']) {
            return "Error: " . $response['error'];
        }
        
        logModuleCall('streamingcenter', __FUNCTION__, "success", "success", "success");
        return 'success';
        
    } catch (Exception $e) {
        logModuleCall('streamingcenter', __FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString());
        return "Exception: " . $e->getMessage();
    }
}

function streamingcenter_UnsuspendAccount(array $params)
{
    $endpoint = "/api/v1/remote_user_unsuspend/";
    
    try {
        logModuleCall('streamingcenter', __FUNCTION__, $params, "start", "start");
        
        $postData = [
            'username' => $params['username'],
            'password' => $params['password']
        ];
        
        $response = streamingcenter_makeApiRequest($params, $endpoint, 'POST', $postData);
        
        if (!$response['success']) {
            return 'Error: ' . $response['error'];
        }
        
        logModuleCall('streamingcenter', __FUNCTION__, "success", "success", "success");
        return 'success';
        
    } catch (Exception $e) {
        logModuleCall('streamingcenter', __FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString());
        return [
            'success' => false,
            'errorMsg' => $e->getMessage(),
        ];
    }
}

function streamingcenter_TerminateAccount(array $params)
{
    $endpoint = "/api/v1/remote_user_delete/";
    
    try {
        logModuleCall('streamingcenter', __FUNCTION__, $params, "start", "start");
        
        $postData = [
            'username' => $params['username'],
            'password' => $params['password']
        ];
        
        $response = streamingcenter_makeApiRequest($params, $endpoint, 'POST', $postData);
        
        if (!$response['success']) {
            return "Error: " . $response['error'];
        }
        
        logModuleCall('streamingcenter', __FUNCTION__, "success", "success", "success");
        return 'success';
        
    } catch (Exception $e) {
        logModuleCall('streamingcenter', __FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString());
        return "Exception: " . $e->getMessage();
    }
}

function streamingcenter_AdminServicesTabFields(array $params)
{
    $endpoint = "/api/v1/remote_get_details/";
    
    try {
        logModuleCall('streamingcenter', __FUNCTION__, $params, "start", "start");
        
        $postData = [
            'username' => $params['username'],
            'password' => $params['password']
        ];
        
        $response = streamingcenter_makeApiRequest($params, $endpoint, 'POST', $postData);
        
        if (!$response['success']) {
            logModuleCall('streamingcenter', __FUNCTION__, "error: " . $response['error'], "error", "error");
            return [];
        }
        
        $data = $response['data'];
        
        logModuleCall('streamingcenter', __FUNCTION__, "success", "success", "success");
        
        $bitrates = [0, 24, 32, 64, 96, 128, 192, 256, 320];
        $bitratesOptions = '';
        foreach ($bitrates as $rate) {
            $selected = (isset($data->limit_bitrate) && $data->limit_bitrate == $rate) ? 'selected' : '';
            $label = $rate == 0 ? 'Unlimited' : $rate . ' kbps';
            $bitratesOptions .= '<option value="' . $rate . '" ' . $selected . '>' . $label . '</option>';
        }

        $limit = function ($value, $suffix = '') {
            return (int) $value > 0 ? (int) $value . $suffix : '&infin;';
        };
        $usage = (int) ($data->listeners_current ?? 0) . ' / ' . $limit($data->limit_listeners ?? 0) . ' listeners, '
            . streamingcenter_formatMb($data->du_current ?? 0, '0 MB') . ' / ' . (((int) ($data->limit_du ?? 0)) ? streamingcenter_formatMb($data->limit_du) : '&infin;') . ' disk, '
            . streamingcenter_formatMb($data->traffic_current ?? 0, '0 MB') . ' / ' . (((int) ($data->limit_traffic ?? 0)) ? streamingcenter_formatMb($data->limit_traffic) : '&infin;') . ' traffic this month';

        return [
            'Radio account status' => '<b>'.$data->account_status_display.'</b>',
            'Current usage' => $usage,
            'Port' => '<input type="number" name="streamingcenter_panel_port" value="' . ($data->panel_port ?? '') . '" />',
            'Station Name' => '<input type="text" size=50 name="streamingcenter_station_name" value="' . ($data->station_name ?? '') . '" />',
            'Website' => '<input type="url" size=50 name="streamingcenter_website_url" value="' . ($data->website_url ?? '') . '" />',
            'Radio stations limit' => '<input type="number" name="streamingcenter_limit_stations" min="0" value="' . ($data->limit_stations ?? '') . '" />',
            'Radio channels limit' => '<input type="number" name="streamingcenter_limit_streams" min="0" value="' . ($data->limit_streams ?? '') . '" />',
            'Listeners limit' => '<input type="number" name="streamingcenter_limit_listeners" min="0" value="' . ($data->limit_listeners ?? '') . '" />',
            'Maximum bitrate' => '<select name="streamingcenter_limit_bitrate">'.$bitratesOptions.'</select>',
            'Disk quota (megabytes)' => '<input type="number" name="streamingcenter_limit_du" min="0" value="' . ($data->limit_du ?? '') . '" />',
            'Traffic per month (megabytes)' => '<input type="number" name="streamingcenter_limit_traffic" min="0" value="' . ($data->limit_traffic ?? '') . '" />',
            // The four deprecated switches ride along as hidden inputs with the account's
            // CURRENT values, so the save sends them back unchanged — see
            // streamingcenter_legacySocialFlags() for why they are still sent at all.
            'Social streams limit (0 = off)' => '<input type="number" name="streamingcenter_limit_social_streams" min="0" value="' . ($data->limit_social_streams ?? '') . '" />'
                . (!empty($data->youtube_streaming_enabled) ? '<input type="hidden" name="streamingcenter_youtube_streaming_enabled" value="on" />' : '')
                . (!empty($data->fb_streaming_enabled) ? '<input type="hidden" name="streamingcenter_fb_streaming_enabled" value="on" />' : '')
                . (!empty($data->vk_streaming_enabled) ? '<input type="hidden" name="streamingcenter_vk_streaming_enabled" value="on" />' : '')
                . (!empty($data->tg_streaming_enabled) ? '<input type="hidden" name="streamingcenter_tg_streaming_enabled" value="on" />' : ''),
            'Enable GeoBlocking' => '<input type="checkbox" name="streamingcenter_geoblocking_enabled" value="on" '.($data->geoblocking_enabled ? 'checked' : '').' />',
            'Enable StereoTool audio processing' => '<input type="checkbox" name="streamingcenter_stereo_tool_enabled" value="on" '.($data->stereo_tool_enabled ? 'checked' : '').' />',
            'User can set custom URL for the station' => '<input type="checkbox" name="streamingcenter_url_branding_enabled" value="on" '.($data->url_branding_enabled ? 'checked' : '').' />',
            'Enable radio mini-Website' => '<input type="checkbox" name="streamingcenter_website_enabled" value="on" '.($data->website_enabled ? 'checked' : '').' />',
            'Enable Text-to-Speech' => '<input type="checkbox" name="streamingcentertts_enabled" value="on" '.($data->tts_enabled ? 'checked' : '').' />',
            'Enable Podcasts' => '<input type="checkbox" name="streamingcenter_podcasts_enabled" value="on" '.($data->podcasts_enabled ? 'checked' : '').' />',
            'Enable Live stream recording' => '<input type="checkbox" name="streamingcenter_save_dj_streams_enabled" value="on" '.($data->save_dj_streams_enabled ? 'checked' : '').' />',
            'Allow HLS streaming' => '<input type="checkbox" name="streamingcenter_hls_enabled" value="on" '.(!empty($data->hls_enabled) ? 'checked' : '').' />',
        ];
        
    } catch (Exception $e) {
        logModuleCall('streamingcenter', __FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString());
        return [];
    }
}

function streamingcenter_AdminServicesTabFieldsSave(array $params)
{
    $endpoint = "/api/v1/remote_update_client/";
    
    try {
        
        logModuleCall('streamingcenter', __FUNCTION__, $params, "start", "start");

        $postData = [
            
        ];

        $postData = array_merge($postData, [
            'template' => "no_template",
            'username' => $params['username'],

            'station_name' => $_REQUEST['streamingcenter_station_name'],
            'website_url' => $_REQUEST['streamingcenter_website_url'],

            'limit_stations' => $_REQUEST['streamingcenter_limit_stations'],
            'limit_streams' => $_REQUEST['streamingcenter_limit_streams'],
            'limit_listeners' => $_REQUEST['streamingcenter_limit_listeners'],
            'limit_bitrate' => $_REQUEST['streamingcenter_limit_bitrate'],
            'limit_du' => $_REQUEST['streamingcenter_limit_du'],
            'limit_traffic' => $_REQUEST['streamingcenter_limit_traffic'],
        ], streamingcenter_legacySocialFlags([
            // From the hidden inputs above: the account's current values, unchanged.
            'youtube_streaming_enabled' => isset($_REQUEST['streamingcenter_youtube_streaming_enabled']),
            'fb_streaming_enabled' => isset($_REQUEST['streamingcenter_fb_streaming_enabled']),
            'vk_streaming_enabled' => isset($_REQUEST['streamingcenter_vk_streaming_enabled']),
            'tg_streaming_enabled' => isset($_REQUEST['streamingcenter_tg_streaming_enabled']),
        ]), [
            'geoblocking_enabled' => isset($_REQUEST['streamingcenter_geoblocking_enabled']) ? true : false,
            'stereo_tool_enabled' => isset($_REQUEST['streamingcenter_stereo_tool_enabled']) ? true : false,
            'url_branding_enabled' => isset($_REQUEST['streamingcenter_url_branding_enabled']) ? true : false,
            'website_enabled' => isset($_REQUEST['streamingcenter_website_enabled']) ? true : false,
            'tts_enabled' => isset($_REQUEST['streamingcentertts_enabled']) ? true : false,
            'podcasts_enabled' => isset($_REQUEST['streamingcenter_podcasts_enabled']) ? true : false,
            'save_dj_streams_enabled' => isset($_REQUEST['streamingcenter_save_dj_streams_enabled']) ? true : false,
            'hls_enabled' => isset($_REQUEST['streamingcenter_hls_enabled']),
        ]);
        // Left out when blank, so an empty box never switches social streaming off.
        $socialLimit = isset($_REQUEST['streamingcenter_limit_social_streams'])
            ? trim((string)$_REQUEST['streamingcenter_limit_social_streams']) : '';
        if ($socialLimit !== '' && ctype_digit($socialLimit)) {
            $postData['limit_social_streams'] = (int)$socialLimit;
        }
        $response = streamingcenter_makeApiRequest($params, $endpoint, 'PATCH', $postData);
        
        if (!$response['success']) {
            
            return [
                'success' => false,
                'error' => $response['error'],
            ];
        }
        
        logModuleCall('streamingcenter', __FUNCTION__, "success", "success", "success");
        return 'success';
        
    } catch (Exception $e) {
        logModuleCall('streamingcenter', __FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString());
        return "Exception: " . $e->getMessage();
    }
}

function streamingcenter_TestConnection(array $params)
{
    $endpoint = "/api/v1/remote_admin_login/";
    
    try {
        logModuleCall('streamingcenter', __FUNCTION__, $params, "start", "start");
        
        $postData = [
            'username' => $params['serverusername'],
            'password' => $params['serverpassword']
        ];
        
        $response = streamingcenter_makeApiRequest($params, $endpoint, 'POST', $postData);
        
        if (!$response['success']) {
            return [
                'success' => false,
                'error' =>  $response['error']
            ];
        }
        
        logModuleCall('streamingcenter', __FUNCTION__, "success", "success", "success");
        return [
            'success' => true,
            'error' => "success"
        ];
        
    } catch (Exception $e) {
        logModuleCall('streamingcenter', __FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString());
        return [
            'success' => false,
            'error' => $e->getMessage(),
        ];
    }
}

function streamingcenter_ChangePassword(array $params) {
    $endpoint = "/api/v1/remote_change_password/";
    
    try {
        logModuleCall('streamingcenter', __FUNCTION__, $params, "start", "start");
        
        $postData = [
            'username' => $params['username'],
            'new_password' => $params['password']
        ];
        
        $response = streamingcenter_makeApiRequest($params, $endpoint, 'PATCH', $postData);
        
        if (!$response['success']) {
            return "Error: " . $response['error'];
        }
        
        logModuleCall('streamingcenter', __FUNCTION__, "success", "success", "success");
        return 'success';
        
    } catch (Exception $e) {
        logModuleCall('streamingcenter', __FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString());
        return $e->getMessage();
    }
}

function streamingcenter_AdminCustomButtonArray()
{
    return [
        'Login as client'           => 'loginAsClient',
        'Re-apply package options'  => 'reapplyPackage',
    ];
}

/** Opens the broadcaster interface as the client (Login to Admin area stays the module's SSO). */
function streamingcenter_loginAsClient(array $params)
{
    $result = streamingcenter_ServiceSingleSignOn($params);
    if (!empty($result['success']) && !empty($result['redirectTo'])) {
        header('Location: ' . $result['redirectTo']);
        exit;
    }
    return $result['errorMsg'] ?? 'Error: no login URL received';
}

function streamingcenter_reapplyPackage(array $params)
{
    return streamingcenter_ChangePackage($params);
}

/** Disk and traffic usage (MB) into WHMCS, run by the daily cron. */
function streamingcenter_UsageUpdate(array $params)
{
    try {
        $services = \WHMCS\Database\Capsule::table('tblhosting')
            ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
            ->where('tblproducts.servertype', 'streamingcenter')
            ->where('tblhosting.server', (int) $params['serverid'])
            ->whereIn('tblhosting.domainstatus', ['Active', 'Suspended'])
            ->select('tblhosting.id', 'tblhosting.username')
            ->get();

        foreach ($services as $service) {
            if ((string) $service->username === '') {
                continue;
            }
            $d = streamingcenter_getDetails($params, $service->username);
            if (!$d) {
                continue;
            }
            \WHMCS\Database\Capsule::table('tblhosting')->where('id', (int) $service->id)->update([
                'diskusage'  => (int) round((float) ($d['du_current'] ?? 0)),
                'disklimit'  => (int) ($d['limit_du'] ?? 0),
                'bwusage'    => (int) round((float) ($d['traffic_current'] ?? 0)),
                'bwlimit'    => (int) ($d['limit_traffic'] ?? 0),
                'lastupdate' => \WHMCS\Database\Capsule::raw('now()'),
            ]);
        }
        return 'success';
    } catch (Exception $e) {
        logModuleCall('streamingcenter', __FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString());
        return $e->getMessage();
    }
}
?>
