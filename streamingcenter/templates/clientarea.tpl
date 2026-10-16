{* Streaming.Center – client area overview (replaces the Overview tab). Texts: lang/*.php *}
{literal}
<style>
.sc {
    --sc-ink: #13233f;
    --sc-ink-2: #1d3157;
    --sc-tally: #e5383b;
    --sc-ok: #1f9d6b;
    --sc-line: #e3e8ef;
    --sc-muted: #5b6676;
    --sc-soft: #f5f7fa;
    --sc-accent: #2f6fde;
    color: #1d2633;
    margin-bottom: 24px;
}
.sc *, .sc *::before, .sc *::after { box-sizing: border-box; }

/* Broadcast deck */
.sc-deck {
    background: var(--sc-ink);
    background-image: linear-gradient(180deg, var(--sc-ink-2) 0, var(--sc-ink) 70%);
    color: #e8eef8;
    border-radius: 14px;
    padding: 22px 24px;
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 18px 24px;
    align-items: center;
}
.sc-tally {
    display: inline-flex; align-items: center; gap: 8px;
    font-size: 13px; font-weight: 600; letter-spacing: .02em;
    padding: 4px 10px 4px 8px; border-radius: 6px;
    background: rgba(255,255,255,.08); color: #b8c4d8;
}
.sc-tally i { width: 9px; height: 9px; border-radius: 50%; background: #6b778a; display: block; }
.sc-tally.is-on { background: var(--sc-tally); color: #fff; }
.sc-tally.is-on i { background: #fff; box-shadow: 0 0 0 3px rgba(255,255,255,.25); }
.sc-name {
    font-size: 30px; line-height: 1.15; font-weight: 700; letter-spacing: -.015em;
    margin: 10px 0 4px; color: #fff; word-break: break-word;
}
.sc-sub { color: #9fb0c9; font-size: 14px; margin: 0; }

.sc-now {
    grid-column: 1 / -1;
    display: flex; align-items: center; gap: 16px;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 10px; padding: 12px;
}
.sc-art {
    width: 64px; height: 64px; border-radius: 8px; flex: 0 0 64px;
    object-fit: cover; background: #0c1830;
}
.sc-track { min-width: 0; flex: 1; }
.sc-track .t { color: #fff; font-weight: 600; font-size: 16px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sc-track .a { color: #9fb0c9; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sc-live { color: #ffb4b5; font-weight: 600; font-size: 13px; }
.sc-play {
    flex: 0 0 auto; display: inline-flex; align-items: center; gap: 10px;
    border: 0; border-radius: 999px; padding: 10px 18px 10px 14px;
    background: #fff; color: var(--sc-ink); font-weight: 600; font-size: 14px; cursor: pointer;
}
.sc-play:hover { background: #e9eef7; }
.sc-play svg { width: 18px; height: 18px; }
.sc-eq { display: none; gap: 2px; align-items: flex-end; height: 14px; }
.sc-eq span { width: 3px; background: var(--sc-ink); border-radius: 1px; height: 4px; }
.sc-play.is-playing .sc-eq { display: inline-flex; }
.sc-play.is-playing .sc-ico-play { display: none; }
@media (prefers-reduced-motion: no-preference) {
    .sc-play.is-playing .sc-eq span { animation: sc-eq .9s ease-in-out infinite; }
    .sc-play.is-playing .sc-eq span:nth-child(2) { animation-delay: -.3s; }
    .sc-play.is-playing .sc-eq span:nth-child(3) { animation-delay: -.6s; }
}
@keyframes sc-eq { 0%,100% { height: 4px; } 50% { height: 14px; } }

.sc-listeners { text-align: right; }
.sc-listeners .n { font-size: 34px; font-weight: 700; color: #fff; line-height: 1; font-variant-numeric: tabular-nums; }
.sc-listeners .l { color: #9fb0c9; font-size: 13px; margin-top: 4px; }

/* Actions */
.sc-actions { display: flex; flex-wrap: wrap; gap: 8px; margin: 16px 0 22px; }
.sc-actions form { margin: 0; }
.sc-btn {
    display: inline-flex; align-items: center; gap: 8px;
    border-radius: 8px; padding: 9px 16px; font-size: 14px; font-weight: 600;
    border: 1px solid var(--sc-line); background: #fff; color: #1d2633; text-decoration: none; cursor: pointer;
}
.sc-btn:hover { background: var(--sc-soft); color: #1d2633; text-decoration: none; }
.sc-btn.is-primary { background: var(--sc-accent); border-color: var(--sc-accent); color: #fff; }
.sc-btn.is-primary:hover { background: #255fc4; color: #fff; }
.sc-btn:focus-visible, .sc-play:focus-visible, .sc-copy:focus-visible, .sc-reveal:focus-visible {
    outline: 3px solid #8fb4ff; outline-offset: 2px;
}

/* Content */
.sc-grid { display: grid; grid-template-columns: minmax(0, 1.55fr) minmax(0, 1fr); gap: 28px; }
@media (max-width: 991px) {
    .sc-grid { grid-template-columns: 1fr; }
}
.sc-sec { margin-bottom: 26px; }
.sc-sec h3 { font-size: 17px; font-weight: 700; margin: 0 0 4px; color: #1d2633; }
.sc-sec .hint { color: var(--sc-muted); font-size: 13px; margin: 0 0 12px; }

.sc-rows { border-top: 1px solid var(--sc-line); }
.sc-row {
    display: grid; grid-template-columns: 150px minmax(0, 1fr) auto; gap: 12px;
    align-items: center; padding: 11px 0; border-bottom: 1px solid var(--sc-line);
}
.sc-row .k { color: var(--sc-muted); font-size: 14px; }
.sc-row .v { font-size: 14px; min-width: 0; overflow-wrap: anywhere; }
.sc-row .v small { color: var(--sc-muted); display: block; }
.sc-mono { font-family: SFMono-Regular, Menlo, Consolas, monospace; font-size: 13px; }
.sc-copy, .sc-reveal {
    border: 1px solid var(--sc-line); background: #fff; border-radius: 6px;
    font-size: 12px; padding: 4px 9px; color: var(--sc-muted); cursor: pointer; white-space: nowrap;
}
.sc-copy:hover, .sc-reveal:hover { color: #1d2633; border-color: #c9d2de; }
.sc-copy.is-done { color: var(--sc-ok); border-color: var(--sc-ok); }
.sc-tools { display: inline-flex; gap: 6px; }
@media (max-width: 575px) {
    .sc-row { grid-template-columns: 1fr auto; }
    .sc-row .k { grid-column: 1 / -1; }
    .sc-deck { grid-template-columns: 1fr; }
    .sc-listeners { text-align: left; }
    .sc-now { flex-wrap: wrap; }
    .sc-name { font-size: 24px; }
}

/* Meters */
.sc-meter { padding: 14px 0; border-bottom: 1px solid var(--sc-line); }
.sc-meter:first-of-type { border-top: 1px solid var(--sc-line); }
.sc-meter .top { display: flex; justify-content: space-between; align-items: baseline; gap: 10px; font-size: 14px; }
.sc-meter .top b { font-variant-numeric: tabular-nums; }
.sc-meter .top span { color: var(--sc-muted); }
.sc-bar { height: 6px; border-radius: 3px; background: #e9edf3; margin-top: 8px; overflow: hidden; }
.sc-bar i { display: block; height: 100%; background: var(--sc-accent); border-radius: 3px; }
.sc-bar.is-warn i { background: #e0902a; }
.sc-bar.is-full i { background: var(--sc-tally); }

.sc-specs { list-style: none; padding: 0; margin: 0; border-top: 1px solid var(--sc-line); }
.sc-specs li { display: flex; justify-content: space-between; gap: 12px; padding: 9px 0; border-bottom: 1px solid var(--sc-line); font-size: 14px; }
.sc-specs li span:first-child { color: var(--sc-muted); }
.sc-specs li span:last-child { text-align: right; overflow-wrap: anywhere; }

.sc-note { background: var(--sc-soft); border-radius: 10px; padding: 14px 16px; font-size: 13px; color: var(--sc-muted); }
.sc-alert { border-radius: 10px; padding: 14px 16px; margin-bottom: 16px; font-size: 14px; }
.sc-alert.is-danger { background: #fdecec; color: #8f1d1f; }
.sc-alert.is-warn { background: #fff5e6; color: #7a4b05; }
.sc-feats { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
.sc-feat { font-size: 13px; padding: 4px 10px; border-radius: 999px; background: #e9f1ff; color: #1f4fa8; }
.sc-ch-meta { color: var(--sc-muted); font-size: 12px; }
</style>
{/literal}

<div class="sc">

{if $error}
    <div class="sc-alert is-warn">{if $status eq 'Pending'}{$L.pending}{else}{$L.load_error}{/if}</div>
{else}

    {if $status eq 'Suspended'}
        <div class="sc-alert is-danger">{$L.suspended}</div>
    {/if}

    {* ---------- Broadcast deck ---------- *}
    <div class="sc-deck">
        <div>
            {if $online}
                <span class="sc-tally is-on"><i></i>{$L.status_on}</span>
            {else}
                <span class="sc-tally"><i></i>{$L.status_off}</span>
            {/if}
            <h2 class="sc-name">{$stationName|escape}</h2>
            <p class="sc-sub">{if $serverTypeLabel}{$serverTypeLabel}{/if}{if $serverTypeLabel && $limits.bitrate} {/if}{if $limits.bitrate}{$L.bitrate_upto|replace:'%s':$limits.bitrate}{/if}</p>
        </div>

        <div class="sc-listeners">
            <div class="n">{$usage.listeners}</div>
            <div class="l">{if $limits.listeners}{$L.listeners_of|replace:'%s':$limits.listeners}{else}{$L.listeners}{/if}</div>
        </div>

        <div class="sc-now">
            {if $now && $now.art}
                <img class="sc-art" src="{$now.art|escape}" alt="" loading="lazy">
            {/if}
            <div class="sc-track">
                {if $now && $now.dj}
                    <div class="sc-live">{$L.live_dj}: {$now.dj|escape}</div>
                {/if}
                {if $now && $now.title}
                    <div class="t">{$now.title|escape}</div>
                    {if $now.artist}<div class="a">{$now.artist|escape}</div>{/if}
                {else}
                    <div class="t">{$L.nothing_playing}</div>
                    <div class="a">{$L.nothing_hint}</div>
                {/if}
            </div>
            {if $listenUrl && $status eq 'Active'}
                <button type="button" class="sc-play" id="sc-play" data-src="{$listenUrl|escape}" aria-pressed="false">
                    <svg class="sc-ico-play" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.5v13a1 1 0 0 0 1.5.86l10.5-6.5a1 1 0 0 0 0-1.72L9.5 4.64A1 1 0 0 0 8 5.5z"/></svg>
                    <span class="sc-eq" aria-hidden="true"><span></span><span></span><span></span></span>
                    <span class="lbl">{$L.listen}</span>
                </button>
            {/if}
        </div>
    </div>

    {* ---------- Actions ---------- *}
    <div class="sc-actions">
        {if $status eq 'Active'}
            <form method="post" action="clientarea.php?action=productdetails&amp;id={$serviceid}" target="_blank">
                <input type="hidden" name="token" value="{$token}">
                <input type="hidden" name="dosinglesignon" value="1">
                <button type="submit" class="sc-btn is-primary">{$L.open_panel}</button>
            </form>
        {/if}
        {if $features.website_enabled && $account.website_url}
            <a class="sc-btn" href="{$account.website_url|escape}" target="_blank" rel="noopener">{$L.website}</a>
        {/if}
    </div>

    <div class="sc-grid">
        <div>
            {* ---------- Listening ---------- *}
            <section class="sc-sec">
                <h3>{$L.listen_title}</h3>
                {if $channels}
                    <p class="hint">{$L.listen_hint}</p>
                    <div class="sc-rows">
                        {foreach $channels as $c}
                        <div class="sc-row">
                            <div class="k">{if $c.name}{$c.name|escape}{else}Stream{/if}<small style="display:block">{$L.listeners_now|replace:'%s':$c.listeners}</small></div>
                            <div class="v sc-mono">{$c.url|escape}</div>
                            <div class="sc-tools"><button type="button" class="sc-copy" data-copy="{$c.url|escape}">{$L.copy}</button></div>
                        </div>
                        {/foreach}
                    </div>
                {else}
                    <p class="hint">{$L.no_channels}</p>
                {/if}
            </section>

            {* ---------- Going live ---------- *}
            <section class="sc-sec">
                <h3>{$L.live_title}</h3>
                <p class="hint">{$L.live_hint}</p>
                <div class="sc-rows">
                    <div class="sc-row">
                        <div class="k">{$L.server}</div>
                        <div class="v sc-mono">{$account.host|escape}</div>
                        <div class="sc-tools"><button type="button" class="sc-copy" data-copy="{$account.host|escape}">{$L.copy}</button></div>
                    </div>
                    {if $serverTypeLabel}
                    <div class="sc-row">
                        <div class="k">{$L.server_type}</div>
                        <div class="v">{$serverTypeLabel}</div><div></div>
                    </div>
                    {/if}
                </div>
            </section>
        </div>

        <aside>
            {* ---------- Usage ---------- *}
            <section class="sc-sec">
                <h3>{$L.usage_title}</h3>
                <p class="hint">{$L.usage_hint}</p>

                <div class="sc-meter">
                    <div class="top"><b>{$usage.listeners}{if $limits.listeners} / {$limits.listeners}{/if}</b><span>{$L.usage_listeners}</span></div>
                    {if $limits.listeners}<div class="sc-bar{if $pct.listeners >= 100} is-full{elseif $pct.listeners >= 80} is-warn{/if}"><i style="width:{$pct.listeners}%"></i></div>{/if}
                </div>
                <div class="sc-meter">
                    <div class="top"><b>{$fmt.du_used}{if $fmt.du_limit} / {$fmt.du_limit}{/if}</b><span>{$L.usage_disk}</span></div>
                    {if $fmt.du_limit}<div class="sc-bar{if $pct.du >= 100} is-full{elseif $pct.du >= 80} is-warn{/if}"><i style="width:{$pct.du}%"></i></div>{/if}
                </div>
                <div class="sc-meter">
                    <div class="top"><b>{$fmt.traffic_used}{if $fmt.traffic_limit} / {$fmt.traffic_limit}{/if}</b><span>{$L.usage_traffic}</span></div>
                    {if $fmt.traffic_limit}<div class="sc-bar{if $pct.traffic >= 100} is-full{elseif $pct.traffic >= 80} is-warn{/if}"><i style="width:{$pct.traffic}%"></i></div>{/if}
                </div>
                {if $limits.stations != 1}
                <div class="sc-meter">
                    <div class="top"><b>{$usage.stations}{if $limits.stations} / {$limits.stations}{/if}</b><span>{$L.usage_stations}</span></div>
                    {if $limits.stations}<div class="sc-bar{if $pct.stations >= 100} is-full{elseif $pct.stations >= 80} is-warn{/if}"><i style="width:{$pct.stations}%"></i></div>{/if}
                </div>
                {/if}
            </section>

            {* ---------- Plan ---------- *}
            <section class="sc-sec">
                <h3>{$L.plan_title}</h3>
                <ul class="sc-specs">
                    <li><span>{$L.plan_listeners}</span><span>{if $limits.listeners}{$limits.listeners}{else}{$L.unlimited}{/if}</span></li>
                    <li><span>{$L.max_bitrate}</span><span>{if $limits.bitrate}{$limits.bitrate} kbps{else}{$L.unlimited}{/if}</span></li>
                    <li><span>{$L.plan_disk}</span><span>{if $fmt.du_limit}{$fmt.du_limit}{else}{$L.unlimited}{/if}</span></li>
                    <li><span>{$L.plan_traffic}</span><span>{if $fmt.traffic_limit}{$fmt.traffic_limit}{else}{$L.unlimited}{/if}</span></li>
                    <li><span>{$L.plan_stations}</span><span>{if $limits.stations}{$limits.stations}{else}{$L.unlimited}{/if}</span></li>
                    <li><span>{$L.plan_streams}</span><span>{if $limits.streams}{$limits.streams}{else}{$L.unlimited}{/if}</span></li>
                    {if $limits.social}<li><span>{$L.plan_social}</span><span>{$limits.social}</span></li>{/if}
                    <li><span>{$L.login_username}</span><span class="sc-mono">{$account.username|escape}</span></li>
                </ul>
                {assign var=anyFeature value=false}
                {foreach $features as $f => $on}{if $on}{assign var=anyFeature value=true}{/if}{/foreach}
                {if $anyFeature}
                    <div class="sc-feats" aria-label="{$L.features_title|escape}">
                        {foreach $features as $f => $on}
                            {if $on}{assign var=fk value="feature_`$f`"}<span class="sc-feat">{$L.$fk}</span>{/if}
                        {/foreach}
                    </div>
                {/if}
            </section>

            {if $account.panel_url}
            <div class="sc-note">
                {$L.panel_note_before} <a href="{$account.panel_url|escape}" target="_blank" rel="noopener">{$account.panel_url|replace:'https://':''|replace:'http://':''|escape}</a> {$L.panel_note_after}
            </div>
            {/if}
        </aside>
    </div>

{/if}
</div>

<script>window.SC_L = {$L_json};</script>
{literal}
<script>
(function () {
    var L = window.SC_L || {};
    document.querySelectorAll('.sc-copy[data-copy]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var text = btn.getAttribute('data-copy');
            var done = function () {
                var t = btn.textContent;
                btn.textContent = L.copied;
                btn.classList.add('is-done');
                setTimeout(function () { btn.textContent = t; btn.classList.remove('is-done'); }, 1500);
            };
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(done);
            } else {
                var ta = document.createElement('textarea');
                ta.value = text; document.body.appendChild(ta); ta.select();
                document.execCommand('copy'); document.body.removeChild(ta); done();
            }
        });
    });

    var play = document.getElementById('sc-play');
    if (play) {
        var audio = null;
        var label = play.querySelector('.lbl');
        play.addEventListener('click', function () {
            if (audio && !audio.paused) {
                audio.pause(); audio.src = ''; audio = null;
                play.classList.remove('is-playing');
                play.setAttribute('aria-pressed', 'false');
                label.textContent = L.listen;
                return;
            }
            audio = new Audio(play.getAttribute('data-src'));
            label.textContent = L.loading;
            audio.play().then(function () {
                play.classList.add('is-playing');
                play.setAttribute('aria-pressed', 'true');
                label.textContent = L.stop;
            }).catch(function () {
                label.textContent = L.cant_play;
                audio = null;
            });
        });
    }
})();
</script>
{/literal}
