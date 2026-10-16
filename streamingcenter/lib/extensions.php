<?php
// WHMCS module for the Streaming.Center Internet-Radio control panel.
// Extensions: custom packages via configurable options, station name from the
// order form, client area overview, usage sync and helpers shared by hooks.php.

use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

const STREAMINGCENTER_LIMITS = ['limit_stations', 'limit_streams', 'limit_listeners', 'limit_bitrate', 'limit_du', 'limit_traffic'];
const STREAMINGCENTER_FLAGS = [
    'geoblocking_enabled', 'stereo_tool_enabled', 'url_branding_enabled', 'website_enabled',
    'tts_enabled', 'podcasts_enabled', 'save_dj_streams_enabled', 'hls_enabled',
];
const STREAMINGCENTER_LEGACY_FLAGS = ['youtube_streaming_enabled', 'fb_streaming_enabled', 'vk_streaming_enabled', 'tg_streaming_enabled'];
const STREAMINGCENTER_BITRATES = [0, 24, 32, 64, 96, 128, 192, 256, 320];
const STREAMINGCENTER_CACHE_SECONDS = 45;

/* --------------------------------------------------------------------------
 * Language (client area): lang/<whmcs language>.php merged over lang/english.php
 * ------------------------------------------------------------------------ */

function streamingcenter_lang(array $params = [])
{
    $name = '';
    try {
        if (class_exists('\Lang') && method_exists('\Lang', 'getName')) {
            $name = (string) \Lang::getName();
        }
    } catch (\Throwable $e) {
    }
    if ($name === '' && !empty($_SESSION['Language'])) {
        $name = (string) $_SESSION['Language'];
    }
    if ($name === '' && !empty($params['clientsdetails']['language'])) {
        $name = (string) $params['clientsdetails']['language'];
    }
    if ($name === '') {
        try {
            $name = (string) Capsule::table('tblconfiguration')->where('setting', 'Language')->value('value');
        } catch (\Throwable $e) {
        }
    }
    return streamingcenter_langFile($name);
}

function streamingcenter_langFile($name)
{
    $name = strtolower(preg_replace('/[^a-z_-]/i', '', (string) $name));
    $aliases = ['slovak' => 'czech', 'ukranian' => 'russian'];
    $name = $aliases[$name] ?? $name;

    static $cache = [];
    if (!isset($cache[$name])) {
        $base = require __DIR__ . '/../lang/english.php';
        $file = __DIR__ . '/../lang/' . $name . '.php';
        $cache[$name] = ($name !== 'english' && $name !== '' && is_file($file)) ? array_merge($base, require $file) : $base;
    }
    return $cache[$name];
}

/* --------------------------------------------------------------------------
 * API helpers
 * ------------------------------------------------------------------------ */

function streamingcenter_toArray($data)
{
    return json_decode(json_encode($data), true) ?: [];
}

/** Account details as an array, or null when the account does not exist. */
function streamingcenter_getDetails(array $params, $username = null)
{
    $response = streamingcenter_makeApiRequest($params, '/api/v1/remote_get_details/', 'POST', [
        'username' => $username ?? $params['username'],
        'password' => $params['password'] ?? '',
    ]);
    if (!$response['success']) {
        return null;
    }
    $d = streamingcenter_toArray($response['data']);
    return (isset($d['username']) || isset($d['pk']) || isset($d['limit_listeners'])) ? $d : null;
}

/** Base URL of the broadcaster's own panel (account port). */
function streamingcenter_panelUrl(array $params, $panelPort)
{
    $host = preg_replace('#^https?://#i', '', rtrim((string) $params['serverhostname'], '/'));
    return $params['serverhttpprefix'] . '://' . $host . ($panelPort ? ':' . (int) $panelPort : '');
}

/** Unauthenticated GET on the broadcaster panel's public API (/api/v2/...). Errors return null. */
function streamingcenter_publicGet(array $params, $panelPort, $path, array $query = [])
{
    if ((int) $panelPort <= 0 || !extension_loaded('curl')) {
        return null;
    }
    $url = streamingcenter_panelUrl($params, $panelPort) . $path . ($query ? '?' . http_build_query($query) : '');
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    ]);
    $raw  = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($raw === false || $code < 200 || $code >= 300) {
        return null;
    }
    $data = json_decode((string) $raw, true);
    return is_array($data) ? $data : null;
}

/* --------------------------------------------------------------------------
 * Small cache (WHMCS TransientData) so the client area does not hit the API on every page view
 * ------------------------------------------------------------------------ */

function streamingcenter_cacheGet($key)
{
    try {
        if (class_exists('\WHMCS\TransientData')) {
            $raw = \WHMCS\TransientData::getInstance()->retrieve($key);
            if ($raw) {
                $data = json_decode($raw, true);
                return is_array($data) ? $data : null;
            }
        }
    } catch (\Throwable $e) {
    }
    return null;
}

function streamingcenter_cacheSet($key, array $data, $seconds = STREAMINGCENTER_CACHE_SECONDS)
{
    try {
        if (class_exists('\WHMCS\TransientData')) {
            \WHMCS\TransientData::getInstance()->store($key, json_encode($data), $seconds);
        }
    } catch (\Throwable $e) {
    }
}

function streamingcenter_cacheForget($key)
{
    try {
        if (class_exists('\WHMCS\TransientData')) {
            \WHMCS\TransientData::getInstance()->delete($key);
        }
    } catch (\Throwable $e) {
    }
}

function streamingcenter_cacheKey(array $params)
{
    return 'streamingcenter_info_' . (int) ($params['serverid'] ?? 0) . '_' . md5((string) $params['username']);
}

/* --------------------------------------------------------------------------
 * Text helpers
 * ------------------------------------------------------------------------ */

function streamingcenter_ascii($str)
{
    $map = [
        'á'=>'a','č'=>'c','ď'=>'d','é'=>'e','ě'=>'e','í'=>'i','ľ'=>'l','ĺ'=>'l','ň'=>'n','ó'=>'o','ô'=>'o','ö'=>'o',
        'ř'=>'r','ŕ'=>'r','š'=>'s','ť'=>'t','ú'=>'u','ů'=>'u','ü'=>'u','ý'=>'y','ž'=>'z','ä'=>'a','ß'=>'ss',
        'Á'=>'A','Č'=>'C','Ď'=>'D','É'=>'E','Ě'=>'E','Í'=>'I','Ľ'=>'L','Ĺ'=>'L','Ň'=>'N','Ó'=>'O','Ô'=>'O','Ö'=>'O',
        'Ř'=>'R','Ŕ'=>'R','Š'=>'S','Ť'=>'T','Ú'=>'U','Ů'=>'U','Ü'=>'U','Ý'=>'Y','Ž'=>'Z','Ä'=>'A',
    ];
    $str = strtr((string) $str, $map);
    if (function_exists('iconv')) {
        $conv = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
        if ($conv !== false) {
            $str = $conv;
        }
    }
    return $str;
}

/** Size in MB as text; round values entered as 1000- or 1024-based both display as whole units. */
function streamingcenter_formatMb($mb, $unlimited = '')
{
    $mb = (float) $mb;
    if ($mb <= 0) {
        return $unlimited;
    }
    $fmt = function ($v) {
        return rtrim(rtrim(number_format($v, 1, '.', ''), '0'), '.');
    };
    foreach ([[1000000, 'TB'], [1048576, 'TB'], [1000, 'GB'], [1024, 'GB']] as [$div, $unit]) {
        if ($mb >= $div && fmod($mb, $div) == 0) {
            return $fmt($mb / $div) . ' ' . $unit;
        }
    }
    if ($mb >= 1000 && $mb < 1000000 && fmod($mb, 100) == 0) {
        return $fmt($mb / 1000) . ' GB';
    }
    if ($mb >= 1048576) {
        return $fmt($mb / 1048576) . ' TB';
    }
    if ($mb >= 1024) {
        return $fmt($mb / 1024) . ' GB';
    }
    return round($mb) . ' MB';
}

/* --------------------------------------------------------------------------
 * Station name from the order form
 * ------------------------------------------------------------------------ */

/** All custom field values of the service (name => value), read from the DB if WHMCS did not pass them. */
function streamingcenter_customFields(array $params)
{
    $fields = [];
    foreach (($params['customfields'] ?? []) as $name => $value) {
        $fields[(string) $name] = (string) $value;
    }
    if (!empty($params['serviceid'])) {
        try {
            $rows = Capsule::table('tblcustomfields as f')
                ->join('tblcustomfieldsvalues as v', 'v.fieldid', '=', 'f.id')
                ->where('f.type', 'product')
                ->where('v.relid', (int) $params['serviceid'])
                ->select('f.fieldname', 'v.value')
                ->get();
            foreach ($rows as $row) {
                if (!is_object($row) || !isset($row->fieldname)) {
                    continue;
                }
                $name = explode('|', (string) $row->fieldname)[0];
                if (!isset($fields[$name]) || trim($fields[$name]) === '') {
                    $fields[$name] = (string) $row->value;
                }
            }
        } catch (\Throwable $e) {
        }
    }
    return $fields;
}

/**
 * Station name entered on the order form: a product custom field named "Station name", "Radio name"
 * (or any field whose name contains radio/station, also in Czech/Russian), else the Domain field.
 * Returns null when the customer did not enter one.
 */
function streamingcenter_stationName(array $params)
{
    $norm = function ($name) {
        $name = mb_strtolower(trim(explode('|', (string) $name)[0]));
        $name = strtolower(streamingcenter_ascii($name)) . ' ' . $name;
        return trim(preg_replace('/[^\p{L}\p{N}]+/u', ' ', $name));
    };
    $exact = ['station name', 'radio name', 'name of radio', 'radio station name', 'nazev radia', 'jmeno radia', 'nazev stanice'];
    $fuzzy = '/(radi[ao]|station|stanic|радио|станци)/u';

    $fields = streamingcenter_customFields($params);
    foreach ([true, false] as $strict) {
        foreach ($fields as $name => $value) {
            if (trim($value) === '') {
                continue;
            }
            $n = $norm($name);
            if ($strict ? (bool) array_filter($exact, function ($e) use ($n) { return strpos($n, $e) === 0; }) : preg_match($fuzzy, $n)) {
                return mb_substr(trim($value), 0, 100);
            }
        }
    }
    $domain = trim((string) ($params['domain'] ?? ''));
    return $domain !== '' ? mb_substr($domain, 0, 100) : null;
}

/* --------------------------------------------------------------------------
 * Custom packages: WHMCS configurable options
 *
 * The option name (part before "|") selects the account field, the selected sub-option
 * value (part before "|") is the value. Only the options a product actually has are returned.
 * ------------------------------------------------------------------------ */

/** "5 GB" / "500 MB" / "1 TB" -> MB; a bare number from a configurable option is GB; 0 = unlimited. */
function streamingcenter_toMb($value)
{
    $v = strtolower(trim((string) $value));
    if ($v === '' || $v === '0' || in_array($v, ['unlimited', '∞'], true)) {
        return 0;
    }
    if (is_numeric($v)) {
        return (int) round((float) $v * 1024);
    }
    if (preg_match('/^([\d.,]+)\s*(tb|t|gb|g|mb|m|kb|k)?b?$/', str_replace(' ', '', $v), $m)) {
        $n = (float) str_replace(',', '.', $m[1]);
        switch (($m[2] ?? 'g')[0] ?? 'g') {
            case 't': return (int) round($n * 1048576);
            case 'm': return (int) round($n);
            case 'k': return (int) max(1, round($n / 1024));
            default:  return (int) round($n * 1024);
        }
    }
    return (int) $v;
}

/** Largest bitrate Streaming.Center knows that does not exceed the value (0 = unlimited). */
function streamingcenter_bitrate($value)
{
    $v = (int) $value;
    if ($v <= 0) {
        return 0;
    }
    $best = 24;
    foreach (STREAMINGCENTER_BITRATES as $b) {
        if ($b > 0 && $b <= $v) {
            $best = $b;
        }
    }
    return $best;
}

function streamingcenter_serverType($value)
{
    $v = strtolower(preg_replace('/\s+/', '', (string) $value));
    if ($v === '') {
        return null;
    }
    if (strpos($v, 'shout') !== false) {
        if (strpos($v, '2.6') !== false || strpos($v, '26') !== false) {
            return 'shoutcast2.6';
        }
        if (preg_match('/shoutcast(v)?1/', $v)) {
            return 'shoutcast1';
        }
        return 'shoutcast2';
    }
    if (strpos($v, 'kh') !== false) {
        return 'icecast-kh';
    }
    return strpos($v, 'ice') !== false ? 'icecast' : null;
}

/** Account fields chosen by the customer through configurable options. */
function streamingcenter_overrides(array $params)
{
    $byKey = [];
    foreach (($params['configoptions'] ?? []) as $name => $value) {
        if ($value === '' || $value === null) {
            continue;
        }
        $key = strtolower(trim(explode('|', (string) $name)[0]));
        $byKey[$key] = is_string($value) ? trim(explode('|', $value)[0]) : $value;
    }
    $get = function (array $keys) use ($byKey) {
        foreach ($keys as $k) {
            if (array_key_exists($k, $byKey)) {
                return $byKey[$k];
            }
        }
        return null;
    };
    $yes = function ($v) {
        return in_array(strtolower(trim((string) $v)), ['on', '1', 'yes', 'true'], true);
    };

    $out = [];
    if (($v = $get(['server_type', 'server'])) !== null && ($st = streamingcenter_serverType($v))) {
        $out['server_type'] = $st;
    }
    $ints = [
        'limit_stations'       => ['stations', 'radios'],
        'limit_streams'        => ['channels', 'streams'],
        'limit_listeners'      => ['listeners'],
        'limit_social_streams' => ['social', 'social_streams'],
    ];
    foreach ($ints as $field => $keys) {
        if (($v = $get($keys)) !== null) {
            $out[$field] = max(0, (int) $v);
        }
    }
    if (($v = $get(['bitrate'])) !== null) {
        $out['limit_bitrate'] = streamingcenter_bitrate($v);
    }
    if (($v = $get(['storage', 'disk'])) !== null) {
        $out['limit_du'] = streamingcenter_toMb($v);
    }
    if (($v = $get(['traffic', 'bandwidth'])) !== null) {
        $out['limit_traffic'] = streamingcenter_toMb($v);
    }
    $flags = [
        'geoblocking_enabled'     => ['geoblocking'],
        'stereo_tool_enabled'     => ['stereotool', 'stereo_tool'],
        'url_branding_enabled'    => ['url_branding', 'branding'],
        'website_enabled'         => ['website'],
        'tts_enabled'             => ['tts'],
        'podcasts_enabled'        => ['podcasts'],
        'save_dj_streams_enabled' => ['recordings', 'recording'],
        'hls_enabled'             => ['hls'],
    ];
    foreach ($flags as $field => $keys) {
        if (($v = $get($keys)) !== null) {
            $out[$field] = $yes($v);
        }
    }
    return $out;
}

/**
 * Full remote_update_client payload without a template: the account's current values,
 * then the customer's choices and the station name on top. The four deprecated social
 * flags are carried through unchanged (see streamingcenter_legacySocialFlags()).
 */
function streamingcenter_overlayPayload(array $details, array $overrides, $username, $stationName = null)
{
    $p = ['username' => $username, 'template' => 'no_template'];
    foreach (STREAMINGCENTER_LIMITS as $f) {
        $p[$f] = (int) ($details[$f] ?? 0);
    }
    $p = array_merge($p, streamingcenter_legacySocialFlags(array_combine(
        STREAMINGCENTER_LEGACY_FLAGS,
        array_map(function ($f) use ($details) { return !empty($details[$f]); }, STREAMINGCENTER_LEGACY_FLAGS)
    )));
    foreach (STREAMINGCENTER_FLAGS as $f) {
        $p[$f] = !empty($details[$f]);
    }
    if (isset($details['limit_social_streams']) && $details['limit_social_streams'] !== '' && $details['limit_social_streams'] !== null) {
        $p['limit_social_streams'] = (int) $details['limit_social_streams'];
    }
    foreach (['station_name', 'website_url'] as $f) {
        if (isset($details[$f])) {
            $p[$f] = (string) $details[$f];
        }
    }
    foreach ($overrides as $k => $v) {
        $p[$k] = $v;
    }
    if ($stationName !== null && $stationName !== '') {
        $p['station_name'] = $stationName;
    }
    return $p;
}

/** Apply configurable options (and optionally the station name) to an existing account. Returns error text or null. */
function streamingcenter_applyOverrides(array $params, $stationName = null, $retries = 1)
{
    $overrides = streamingcenter_overrides($params);
    if (!$overrides && ($stationName === null || $stationName === '')) {
        return null;
    }
    $details = null;
    for ($i = 0; $i < $retries && !$details; $i++) {
        if ($i > 0) {
            sleep(3);
        }
        $details = streamingcenter_getDetails($params);
    }
    if (!$details) {
        return 'Could not load account ' . $params['username'] . ' to apply the configurable options.';
    }
    $response = streamingcenter_makeApiRequest($params, '/api/v1/remote_update_client/', 'PATCH',
        streamingcenter_overlayPayload($details, $overrides, $params['username'], $stationName));
    streamingcenter_cacheForget(streamingcenter_cacheKey($params));
    return $response['success'] ? null : $response['error'];
}

/* --------------------------------------------------------------------------
 * Client area / welcome email data
 * ------------------------------------------------------------------------ */

function streamingcenter_info(array $params, $useCache = true)
{
    $key = streamingcenter_cacheKey($params);
    if ($useCache && ($cached = streamingcenter_cacheGet($key))) {
        return $cached;
    }

    $d = streamingcenter_getDetails($params);
    if (!$d) {
        return null;
    }
    $panelPort = (int) ($d['panel_port'] ?? 0);

    $info = [
        'account' => [
            'username'     => (string) $params['username'],
            'station_name' => (string) ($d['station_name'] ?? ''),
            'status'       => (string) ($d['account_status_display'] ?? ''),
            'panel_url'    => streamingcenter_panelUrl($params, $panelPort),
            'host'         => preg_replace('#^https?://#i', '', (string) $params['serverhostname']),
            'website_url'  => (string) ($d['website_url'] ?? ''),
        ],
        'usage' => [
            'listeners'  => (int) ($d['listeners_current'] ?? 0),
            'stations'   => (int) ($d['stations_current'] ?? 0),
            'du_mb'      => (float) ($d['du_current'] ?? 0),
            'traffic_mb' => (float) ($d['traffic_current'] ?? 0),
        ],
        'limits' => [
            'stations'   => (int) ($d['limit_stations'] ?? 0),
            'streams'    => (int) ($d['limit_streams'] ?? 0),
            'listeners'  => (int) ($d['limit_listeners'] ?? 0),
            'bitrate'    => (int) ($d['limit_bitrate'] ?? 0),
            'du_mb'      => (int) ($d['limit_du'] ?? 0),
            'traffic_mb' => (int) ($d['limit_traffic'] ?? 0),
            'social'     => isset($d['limit_social_streams']) ? (int) $d['limit_social_streams'] : 0,
        ],
        'features'     => [],
        'channels'     => [],
        'now'          => null,
        'online'       => false,
        'channel_type' => '',
    ];
    foreach (STREAMINGCENTER_FLAGS as $f) {
        $info['features'][$f] = !empty($d[$f]);
    }

    // Channels (streams) from the broadcaster panel's public API
    $ch = streamingcenter_publicGet($params, $panelPort, '/api/v2/channels/', ['limit' => 20]);
    $serverIds = [];
    foreach ((($ch['results'] ?? null) ?: (is_array($ch) && isset($ch[0]) ? $ch : [])) as $c) {
        $info['channels'][] = [
            'name'      => strtoupper((string) ($c['s_format'] ?? '')) . (!empty($c['bitrate']) ? ' ' . (int) $c['bitrate'] . ' kbps' : ''),
            'url'       => (string) (($c['secure_stream_url'] ?? '') ?: ($c['stream_url'] ?? '')),
            'type'      => (string) ($c['s_type'] ?? ''),
            'listeners' => (int) ($c['listeners_current'] ?? 0),
            'active'    => !empty($c['active']),
        ];
        if ($info['channel_type'] === '' && !empty($c['s_type'])) {
            $info['channel_type'] = (string) $c['s_type'];
        }
        if (isset($c['server'])) {
            $serverIds[(int) $c['server']] = true;
        }
    }

    // Now playing: newest history item, if it is still playing (or started less than 10 minutes ago)
    foreach (array_keys($serverIds) ?: [0] as $sid) {
        $h = streamingcenter_publicGet($params, $panelPort, '/api/v2/history/', array_filter(['limit' => 5, 'server' => $sid ?: null]));
        $latest = null;
        foreach ((($h['results'] ?? null) ?: []) as $it) {
            if (!$latest || (int) ($it['ts'] ?? 0) > (int) ($latest['ts'] ?? 0)) {
                $latest = $it;
            }
        }
        if ($latest && round(microtime(true) * 1000) <= (int) ($latest['ts'] ?? 0) + max((int) ($latest['length'] ?? 0), 0) + 600000) {
            $info['now'] = [
                'title'  => (string) ($latest['title'] ?? ''),
                'artist' => (string) ($latest['author'] ?? ''),
                'art'    => (string) (($latest['img_medium_url'] ?? '') ?: ($latest['img_url'] ?? '')),
                'dj'     => (string) ($latest['dj_name'] ?? ''),
            ];
            break;
        }
    }

    $channelListeners = array_sum(array_column($info['channels'], 'listeners'));
    $info['online'] = $info['now'] !== null || $info['usage']['listeners'] > 0 || $channelListeners > 0;

    streamingcenter_cacheSet($key, $info);
    return $info;
}

/* --------------------------------------------------------------------------
 * "Create configurable options" for a product (button on the product's Module Settings tab)
 * ------------------------------------------------------------------------ */

/**
 * Creates a configurable option group for the product with the options this module understands,
 * all priced at 0 in every currency. Prices and choices can then be edited in WHMCS as usual.
 * Returns [success(bool), message(string)].
 */
function streamingcenter_createConfigurableOptions($productId)
{
    $product = Capsule::table('tblproducts')->where('id', (int) $productId)->first();
    if (!$product || $product->servertype !== 'streamingcenter') {
        return [false, 'This product does not use the Streaming.Center module.'];
    }
    $groupName = 'Streaming.Center – ' . $product->name;
    if (Capsule::table('tblproductconfiggroups')->where('name', $groupName)->exists()) {
        return [false, 'The configurable option group "' . $groupName . '" already exists.'];
    }

    // name => [type (1 dropdown, 3 yes/no), sub-options]
    $options = [
        'server_type|Streaming server' => [1, ['icecast-kh|Icecast-KH', 'icecast|Icecast', 'shoutcast2|Shoutcast 2']],
        'listeners|Listeners'          => [1, ['50|50 listeners', '100|100 listeners', '250|250 listeners', '500|500 listeners', '0|Unlimited']],
        'bitrate|Max. bitrate'         => [1, ['64|64 kbps', '128|128 kbps', '192|192 kbps', '320|320 kbps']],
        'storage|Disk space'           => [1, ['1 GB|1 GB', '5 GB|5 GB', '20 GB|20 GB', '50 GB|50 GB']],
        'traffic|Monthly traffic'      => [1, ['100 GB|100 GB', '500 GB|500 GB', '1 TB|1 TB', '0|Unlimited']],
        'podcasts|Podcasts'            => [3, ['Yes']],
        'recordings|Live show recording' => [3, ['Yes']],
        'hls|HLS streaming'            => [3, ['Yes']],
    ];

    $currencies = Capsule::table('tblcurrencies')->pluck('id')->all();
    $zeroPricing = [
        'msetupfee' => 0, 'qsetupfee' => 0, 'ssetupfee' => 0, 'asetupfee' => 0, 'bsetupfee' => 0, 'tsetupfee' => 0,
        'monthly' => 0, 'quarterly' => 0, 'semiannually' => 0, 'annually' => 0, 'biennially' => 0, 'triennially' => 0,
    ];

    Capsule::connection()->transaction(function () use ($groupName, $product, $options, $currencies, $zeroPricing) {
        $gid = Capsule::table('tblproductconfiggroups')->insertGetId([
            'name'        => $groupName,
            'description' => 'Created by the Streaming.Center module. Edit prices and choices as needed.',
        ]);
        Capsule::table('tblproductconfiglinks')->insert(['gid' => $gid, 'pid' => $product->id]);

        $order = 0;
        foreach ($options as $name => [$type, $subs]) {
            $cid = Capsule::table('tblproductconfigoptions')->insertGetId([
                'gid' => $gid, 'optionname' => $name, 'optiontype' => $type,
                'qtyminimum' => 0, 'qtymaximum' => 0, 'order' => $order++, 'hidden' => 0,
            ]);
            foreach ($subs as $i => $sub) {
                $sid = Capsule::table('tblproductconfigoptionssub')->insertGetId([
                    'configid' => $cid, 'optionname' => $sub, 'sortorder' => $i, 'hidden' => 0,
                ]);
                foreach ($currencies as $currency) {
                    Capsule::table('tblpricing')->insert(['type' => 'configoptions', 'currency' => $currency, 'relid' => $sid] + $zeroPricing);
                }
            }
        }
    });

    return [true, 'Configurable option group "' . $groupName . '" was created and assigned to this product. Set the prices under System Settings > Configurable Options.'];
}
