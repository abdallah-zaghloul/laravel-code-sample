<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <title>{{ config('app.name') }} – WS Test</title>
</head>

<body>
    <!-- App -->
    <div class="ml-4 text-center text-sm text-gray-500 dark:text-gray-400 sm:text-right sm:ml-0">
        {{ config('app.name') }} v{{ Illuminate\Foundation\Application::VERSION }} (PHP
        v{{ PHP_VERSION }})
    </div>
    <!-- WS -->
    <h3>WebSocket (WS)</h3>
    <label>Host</label>
    <input id="host" value="{{ config('app.host') }}">
    </p>
    <label>Port</label>
    <input id="port" value="{{ config('app.ws_port') }}">
    </p>
    <label>Scheme</label>
    <select id="scheme">
        <option value="http" @selected(config('app.scheme') === 'http')>http</option>
        <option value="https" @selected(config('app.scheme') === 'https')>https</option>
    </select>
    </p>
    @if (filled(config('app.ws_path')))
        <label>WS Path</label>
        <input id="path" value="{{ config('app.ws_path') }}">
        </p>
    @endif
    <label>WS channel</label>
    <input id="channel">
    </p>
    <label>WS Event</label>
    <input id="event">
    </p>
    <button onclick="wsConnect()">WS Connect</button>
    <hr>
    <!-- SSE -->
    <h3>Server Sent Event (SSE)</h3>
    <label>Topic</label>
    <input id="topic">
    <button onclick="sseConnect()">SSE Connect</button>
    <hr>
    <!-- log -->
    <pre id="log" style="margin-top:15px; background:#111; color:#0f0; padding:10px;"></pre>
    <!-- WS -->
    <script src="https://js.pusher.com/8.2/pusher.min.js"></script>
    <!-- JSON Circular Parse -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/json-stringify-safe@5.0.1/stringify.min.js"></script> -->
    <!-- Globals -->
    <script>
        var inputVal = (input) => document.getElementById(input).value
        var safeStringify = (iterable) => JSON.stringify(iterable, null, 2)
        var log = (message) => {
            message = (typeof message === "object" || Array.isArray(message)) ? safeStringify(message) : message
            console.log(message)
            document.getElementById('log').textContent += message + "\n"
        }
    </script>
    <!-- WS -->
    <script>
        function wsConnect() {
            let pusher = null, channel = null
            if (pusher) {
                pusher.disconnect();
                log(`🔌 Disconnected previous connection`);
            }
            pusher = new Pusher("{{ env('WS_KEY', 'local') }}", {
                cluster: "mt1", // required by pusher-js
                wsHost: inputVal('host'),
                wsPort: inputVal('port'),
                @if (filled(config('app.ws_path')))
                    wsPath: inputVal('path'),
                @endif
                forceTLS: inputVal('scheme') === "https",
                enabledTransports: ["ws", "wss"],
                enableStats: false,
            });
        log(`📡 Subscribing to channel: messages`);
        channel = pusher.subscribe(inputVal('channel'));
        channel.bind("pusher:subscription_succeeded", () => log(`✅ Subscribed to messages`));
        channel.bind(inputVal('event'), (data) => log(`🔥 Event received:${safeStringify(data)}`));
        }
    </script>

    <!-- SSE -->
    <script>
        function sseConnect() {
            const SSE_URL = "{{ str(config('app.sse_url'))->append('?topic=') }}"
                + inputVal('topic');
            log(`Connecting to SSE: ${SSE_URL}`);
            const eventSource = new EventSource(SSE_URL)
            eventSource.onopen = () => log("SSE connection opened")
            eventSource.onmessage = (event) => log(`SSE received: ${event.data}`)
            eventSource.onerror = (err) => log(`SSE Error: ${err}`)
        }
    </script>
</body>

</html>