<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Meeting Notes Agent Demo</title>
    <style>
        :root {
            --bg: #edf2f8;
            --bg-deep: #dfe8f4;
            --panel: rgba(255, 255, 255, 0.94);
            --panel-strong: #ffffff;
            --surface: #f5f8fc;
            --ink: #0f1728;
            --muted: #58657c;
            --line: #d2dce8;
            --line-strong: #b6c4d6;
            --accent: #1e4ed8;
            --accent-strong: #173ea7;
            --accent-soft: #eaf1ff;
            --success-soft: #ebfaf1;
            --error-bg: #fff3f1;
            --error-line: #f0b7b0;
            --error-ink: #8b2b21;
            --shadow-lg: 0 28px 54px rgba(15, 23, 40, 0.10);
            --shadow-md: 0 12px 28px rgba(15, 23, 40, 0.08);
            --radius-xl: 26px;
            --radius-lg: 18px;
            --radius-md: 14px;
            --radius-sm: 10px;
            --font-sans: Inter, "Segoe UI Variable", "Segoe UI", "SF Pro Text", "Helvetica Neue", Arial, sans-serif;
            --font-mono: ui-monospace, "SFMono-Regular", Menlo, Consolas, monospace;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: var(--font-sans);
            color: var(--ink);
            background:
                radial-gradient(circle at top left, rgba(30, 78, 216, 0.14), transparent 24%),
                radial-gradient(circle at 85% 12%, rgba(14, 116, 144, 0.12), transparent 20%),
                linear-gradient(180deg, #f8fbff 0%, var(--bg) 48%, var(--bg-deep) 100%);
        }

        .page {
            width: min(1240px, calc(100% - 40px));
            margin: 28px auto 52px;
        }

        .hero,
        .panel,
        .meta-card,
        .response,
        .structured-card {
            border: 1px solid rgba(210, 220, 232, 0.86);
            background: var(--panel);
            box-shadow: var(--shadow-md);
            backdrop-filter: blur(14px);
        }

        .hero {
            position: relative;
            overflow: hidden;
            padding: 36px;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            background:
                linear-gradient(135deg, rgba(255, 255, 255, 0.96), rgba(240, 246, 255, 0.94)),
                linear-gradient(135deg, rgba(30, 78, 216, 0.08), rgba(19, 62, 167, 0.03));
        }

        .hero::after {
            content: "";
            position: absolute;
            inset: auto -80px -80px auto;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(30, 78, 216, 0.12), transparent 68%);
            pointer-events: none;
        }

        .eyebrow {
            margin: 0 0 12px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--accent-strong);
        }

        h1, h2, h3, pre { margin: 0; }

        h1 {
            max-width: 11ch;
            font-size: clamp(38px, 5vw, 56px);
            line-height: 0.96;
            letter-spacing: -0.05em;
            font-weight: 850;
        }

        h2 {
            font-size: 28px;
            line-height: 1.1;
            letter-spacing: -0.035em;
            font-weight: 820;
        }

        h3 {
            font-size: 18px;
            line-height: 1.25;
            letter-spacing: -0.02em;
            font-weight: 760;
        }

        .hero p,
        .panel p {
            color: var(--muted);
            line-height: 1.72;
            font-size: 15px;
        }

        .meta,
        .grid {
            display: grid;
            gap: 18px;
        }

        .meta {
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            margin-top: 26px;
        }

        .meta-card,
        .panel {
            padding: 24px;
            border-radius: var(--radius-lg);
        }

        .meta-card {
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(245, 248, 252, 0.98));
        }

        .meta-card strong {
            display: block;
            margin-bottom: 10px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            color: var(--muted);
        }

        .meta-card span {
            font-size: 16px;
            font-weight: 700;
            color: var(--ink);
        }

        .grid {
            grid-template-columns: minmax(0, 1.18fr) minmax(340px, 0.92fr);
            margin-top: 22px;
            align-items: start;
        }

        form {
            display: grid;
            gap: 14px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            color: var(--muted);
        }

        textarea,
        select {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: var(--radius-md);
            background: var(--panel-strong);
            color: var(--ink);
            padding: 15px 16px;
            font-family: var(--font-sans);
            font-size: 15px;
            font-weight: 560;
            line-height: 1.68;
            transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }

        textarea::placeholder {
            color: #8190a7;
            font-weight: 520;
        }

        textarea:focus,
        select:focus {
            outline: none;
            border-color: rgba(30, 78, 216, 0.54);
            box-shadow: 0 0 0 4px rgba(30, 78, 216, 0.11);
            background: #ffffff;
        }

        textarea {
            min-height: 190px;
            resize: vertical;
        }

        .actions,
        .badge-row,
        .check {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .check {
            margin-top: 2px;
            padding: 2px 0;
            font-size: 14px;
            color: var(--ink);
            text-transform: none;
            letter-spacing: 0;
            font-weight: 600;
        }

        .check input {
            width: 16px;
            height: 16px;
            accent-color: var(--accent);
        }

        button,
        .button-link {
            border: 0;
            border-radius: var(--radius-sm);
            background: linear-gradient(180deg, #2a5ff1 0%, #1e4ed8 100%);
            color: white;
            padding: 12px 18px;
            font-family: var(--font-sans);
            font-size: 14px;
            font-weight: 760;
            letter-spacing: -0.01em;
            cursor: pointer;
            text-decoration: none;
            transition: transform 0.12s ease, opacity 0.12s ease, box-shadow 0.18s ease;
            box-shadow: 0 10px 18px rgba(30, 78, 216, 0.18);
        }

        button:hover,
        .button-link:hover {
            transform: translateY(-1px);
        }

        button:disabled {
            opacity: 0.74;
            cursor: wait;
            transform: none;
            box-shadow: none;
        }

        .button-link.secondary {
            background: linear-gradient(180deg, #2f3e56 0%, #223046 100%);
            box-shadow: 0 10px 18px rgba(34, 48, 70, 0.16);
        }

        .response,
        .structured-card {
            padding: 20px;
            border-radius: var(--radius-lg);
        }

        .response {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(245, 248, 252, 0.98));
        }

        .response-text,
        .stream-box {
            white-space: pre-wrap;
            line-height: 1.72;
            font-size: 15px;
            font-weight: 540;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 7px 11px;
            border-radius: 999px;
            background: var(--accent-soft);
            color: var(--accent-strong);
            font-size: 12px;
            font-weight: 760;
            letter-spacing: 0.01em;
        }

        .thinking {
            margin-top: 12px;
            color: var(--accent-strong);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .thinking-placeholder {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            color: var(--muted);
            font-size: 15px;
            font-weight: 620;
        }

        .thinking-dots {
            display: inline-flex;
            gap: 6px;
            align-items: center;
        }

        .thinking-dots span {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: rgba(30, 78, 216, 0.78);
            animation: thinking-bounce 1s infinite ease-in-out;
        }

        .thinking-dots span:nth-child(2) {
            animation-delay: 0.16s;
        }

        .thinking-dots span:nth-child(3) {
            animation-delay: 0.32s;
        }

        .inline-error {
            margin-top: 12px;
            padding: 13px 14px;
            border: 1px solid var(--error-line);
            border-radius: var(--radius-md);
            background: var(--error-bg);
            color: var(--error-ink);
            line-height: 1.6;
            font-size: 14px;
            font-weight: 560;
        }

        .structured-slides {
            display: grid;
            gap: 14px;
            margin-top: 16px;
        }

        .structured-slide {
            padding: 18px;
            border: 1px solid var(--line);
            border-radius: var(--radius-md);
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(247, 250, 254, 0.98));
        }

        .structured-slide strong {
            display: block;
            margin-bottom: 8px;
            font-size: 16px;
            font-weight: 760;
        }

        .tool-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-top: 14px;
        }

        .tool-box {
            padding: 14px;
            border: 1px solid var(--line);
            border-radius: var(--radius-md);
            background: var(--surface);
        }

        .debug-panel {
            display: none;
            margin-top: 14px;
        }

        ul {
            margin: 10px 0 0 20px;
            padding: 0;
            line-height: 1.68;
            color: var(--ink);
        }

        li + li {
            margin-top: 6px;
        }

        pre {
            white-space: pre-wrap;
            word-break: break-word;
            color: #33445f;
            font-size: 13px;
            line-height: 1.72;
            font-family: var(--font-mono);
        }

        .stream-box {
            min-height: 190px;
            padding: 18px;
            border-radius: var(--radius-md);
            background: linear-gradient(180deg, #fbfdff 0%, #f4f7fc 100%);
            border: 1px dashed var(--line-strong);
        }

        code {
            font-family: var(--font-mono);
            font-size: 0.92em;
            background: #eef3fb;
            padding: 2px 6px;
            border-radius: 6px;
            color: #2b3e5f;
        }

        @keyframes thinking-bounce {
            0%, 80%, 100% {
                transform: translateY(0);
                opacity: 0.35;
            }
            40% {
                transform: translateY(-4px);
                opacity: 1;
            }
        }

        @media (max-width: 960px) {
            .grid,
            .tool-grid {
                grid-template-columns: 1fr;
            }

            .page {
                width: min(100% - 20px, 1240px);
                margin: 16px auto 34px;
            }

            .hero,
            .panel,
            .meta-card {
                padding: 18px;
            }

            h1 {
                max-width: none;
                font-size: 38px;
            }

            h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <section class="hero">
            <p class="eyebrow">Laravel AI SDK</p>
            <h1>Meeting Notes Agent Demo</h1>
            <p>
                This page demonstrates prompting, conversation context, tools, structured output, and streaming
                using <code>RemembersConversations</code> with the SDK tables
                <code>agent_conversations</code> and <code>agent_conversation_messages</code>.
            </p>

            <div class="meta">
                <div class="meta-card">
                    <strong>Current Provider</strong>
                    <span>{{ $provider }}</span>
                </div>
                <div class="meta-card">
                    <strong>Conversation ID</strong>
                    <span>{{ $conversationId ?? 'Not started yet' }}</span>
                </div>
                <div class="meta-card">
                    <strong>Tools</strong>
                    <span>meeting notes context tool</span>
                </div>
            </div>
        </section>

        @if ($error)
            <div class="panel" style="margin-top: 18px; border-color: #d4705e; background: #fff3ef; color: #7d2e1e;">
                {{ $error }}
            </div>
        @endif

        <div class="grid">
            <section class="panel">
                <h2>Prompting, Context, Structured Output</h2>
                <p>
                    Run a prompt, then send a follow-up. The agent remembers the conversation automatically and
                    returns structured output through its schema.
                </p>

                @if ($error)
                    <div class="inline-error">{{ $error }}</div>
                @endif

                <form id="agent-form" method="POST" action="{{ route('ai-demo.prompt') }}">
                    @csrf
                    <div>
                        <label for="provider">Provider</label>
                        <select id="provider" name="provider">
                            <option value="openai" @selected($provider === 'openai')>OpenAI</option>
                            <option value="gemini" @selected($provider === 'gemini')>Gemini</option>
                        </select>
                    </div>

                    <div>
                        <label for="prompt">Prompt</label>
                        <textarea id="prompt" name="prompt" placeholder="Paste meeting notes here" required>{{ $lastPrompt }}</textarea>
                    </div>

                    <label class="check">
                        <input type="checkbox" name="start_new" value="1">
                        Start a new conversation
                    </label>

                    <div class="actions">
                        <button id="agent-submit" type="submit">Run Agent</button>
                        <a class="button-link secondary" href="{{ route('ai-demo.clear') }}">Clear Conversation</a>
                    </div>
                </form>

                <div id="agent-thinking" class="thinking" style="display: none;">Agent is handling your meeting notes...</div>

                @if ($structuredResult)
                    <div class="structured-card" style="margin-top: 16px;">
                        <h3>{{ $structuredResult['title'] ?? 'Untitled' }}</h3>
                        <p>{{ $structuredResult['summary'] ?? '' }}</p>
                        <div class="structured-slides">
                            <div class="structured-slide">
                                <strong>Decisions</strong>
                                <ul>
                                    @foreach (($structuredResult['decisions'] ?? []) as $decision)
                                        <li>{{ $decision }}</li>
                                    @endforeach
                                </ul>
                            </div>

                            <div class="structured-slide">
                                <strong>Action Items</strong>
                                <ul>
                                    @foreach (($structuredResult['action_items'] ?? []) as $item)
                                        <li>
                                            <strong>{{ $item['task'] ?? 'Task' }}</strong>
                                            @if (! empty($item['owner']))
                                                - {{ $item['owner'] }}
                                            @endif
                                            @if (! empty($item['deadline']))
                                                ({{ $item['deadline'] }})
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            <div class="structured-slide">
                                <strong>Risks / Open Questions</strong>
                                <ul>
                                    @foreach (($structuredResult['risks'] ?? []) as $risk)
                                        <li>{{ $risk }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <div class="actions" style="margin-top: 14px;">
                            <button id="toggle-debug" type="button">Show JSON</button>
                        </div>

                        <div id="debug-panel" class="debug-panel">
                            <pre>{{ json_encode($structuredResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>

                            <div class="tool-grid">
                                <div class="tool-box">
                                    <h3>Tool Calls</h3>
                                    <pre style="margin-top: 10px;">{{ json_encode($latestToolCalls, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                                <div class="tool-box">
                                    <h3>Tool Results</h3>
                                    <pre style="margin-top: 10px;">{{ json_encode($latestToolResults, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if (! $structuredResult)
                    <div class="response" style="margin-top: 16px;">
                        <div id="agent-empty-state" class="response-text">Run the agent to generate the latest structured meeting summary.</div>
                    </div>
                @endif
            </section>

            <section class="panel">
                <h2>Streaming Translation</h2>
                <p>
                    Stream a translation into the browser in real time. This flow is a translation task,
                    keeps remembered conversation context, and does not use structured output.
                </p>

                <form id="stream-form" action="{{ route('ai-demo.stream') }}" method="POST">
                    @csrf
                    <div>
                        <label for="stream-provider">Provider</label>
                        <select id="stream-provider" name="provider">
                            <option value="openai" @selected($provider === 'openai')>OpenAI</option>
                            <option value="gemini" @selected($provider === 'gemini')>Gemini</option>
                        </select>
                    </div>
                    <div>
                        <label for="target-language">Language</label>
                        <select id="target-language" name="target_language" required>
                            <option value="Vietnamese">Vietnamese</option>
                            <option value="English">English</option>
                            <option value="Japanese" selected>Japanese</option>
                            <option value="Korean">Korean</option>
                            <option value="Chinese (Simplified)">Chinese (Simplified)</option>
                            <option value="French">French</option>
                            <option value="German">German</option>
                        </select>
                    </div>
                    <div>
                        <label for="stream-prompt">Task Description</label>
                        <textarea id="stream-prompt" name="prompt" required>{{ $streamPrompt ?: 'Laravel AI SDK helps developers build agent workflows with structured output and streaming responses.' }}</textarea>
                    </div>

                    <label class="check">
                        <input type="checkbox" name="start_new" value="1">
                        Start a new translation conversation
                    </label>

                    <div class="actions">
                        <button id="stream-submit" type="submit">Start Translation Stream</button>
                    </div>
                </form>

                <div id="stream-thinking" class="thinking" style="display: none;">Agent is thinking...</div>
                <div class="response" style="margin-top: 16px;">
                    <div id="stream-output" class="stream-box"></div>
                </div>
            </section>
        </div>
    </div>

    <script>
        const agentForm = document.getElementById('agent-form');
        const agentSubmit = document.getElementById('agent-submit');
        const agentThinking = document.getElementById('agent-thinking');
        const streamForm = document.getElementById('stream-form');
        const streamOutput = document.getElementById('stream-output');
        const streamThinking = document.getElementById('stream-thinking');
        const streamSubmit = document.getElementById('stream-submit');
        const toggleDebug = document.getElementById('toggle-debug');
        const debugPanel = document.getElementById('debug-panel');
        const agentEmptyState = document.getElementById('agent-empty-state');
        const thinkingMarkup = `
            <div class="thinking-placeholder">
                <span>Agent is thinking</span>
                <span class="thinking-dots" aria-hidden="true">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </div>
        `;

        if (agentForm && agentSubmit && agentThinking) {
            agentForm.addEventListener('submit', () => {
                agentThinking.style.display = 'block';
                agentSubmit.disabled = true;
                agentSubmit.textContent = 'Running...';

                if (agentEmptyState) {
                    agentEmptyState.innerHTML = thinkingMarkup;
                }
            });
        }

        async function handleStream(form, output) {
            output.innerHTML = thinkingMarkup;
            streamThinking.style.display = 'block';
            streamSubmit.disabled = true;
            streamSubmit.textContent = 'Streaming...';
            const chunkQueue = [];
            let streamFinished = false;
            let streamErrored = false;

            const renderQueuedChunks = async () => {
                while (!streamFinished || chunkQueue.length > 0) {
                    if (chunkQueue.length === 0) {
                        await new Promise((resolve) => setTimeout(resolve, 24));
                        continue;
                    }

                    const nextChunk = chunkQueue.shift();
                    if (output.querySelector('.thinking-placeholder')) {
                        output.textContent = '';
                    }

                    output.textContent += nextChunk;
                    await new Promise((resolve) => setTimeout(resolve, 36));
                }
            };

            const renderPromise = renderQueuedChunks();

            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'text/event-stream',
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                },
                body: new FormData(form),
            });

            if (!response.ok || !response.body) {
                const payload = await response.json().catch(() => ({ message: 'Streaming request failed.' }));
                output.textContent = payload.message || 'Streaming request failed.';
                streamThinking.style.display = 'none';
                streamSubmit.disabled = false;
                streamSubmit.textContent = 'Start Translation Stream';
                return;
            }

            const reader = response.body.getReader();
            const decoder = new TextDecoder();
            let buffer = '';

            while (true) {
                const { value, done } = await reader.read();

                if (done) {
                    break;
                }

                buffer += decoder.decode(value, { stream: true });

                const chunks = buffer.split("\n\n");
                buffer = chunks.pop() || '';

                for (const chunk of chunks) {
                    const line = chunk.split("\n").find((entry) => entry.startsWith('data: '));

                    if (!line) {
                        continue;
                    }

                    const payload = JSON.parse(line.slice(6));

                    if (payload.type === 'text_delta') {
                        chunkQueue.push(payload.delta);
                    }

                    if (payload.type === 'done') {
                        streamFinished = true;
                    }

                    if (payload.type === 'error') {
                        streamErrored = true;
                        streamFinished = true;
                        chunkQueue.length = 0;
                        output.textContent = payload.message || 'Streaming request failed.';
                        streamThinking.style.display = 'none';
                        streamSubmit.disabled = false;
                        streamSubmit.textContent = 'Start Translation Stream';
                        return;
                    }
                }
            }

            streamFinished = true;
            await renderPromise;

            if (!streamErrored) {
                streamThinking.style.display = 'none';
                streamSubmit.disabled = false;
                streamSubmit.textContent = 'Start Translation Stream';
            }
        }

        streamForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            await handleStream(streamForm, streamOutput);
        });

        if (toggleDebug && debugPanel) {
            toggleDebug.addEventListener('click', () => {
                const isHidden = debugPanel.style.display === '' || debugPanel.style.display === 'none';
                debugPanel.style.display = isHidden ? 'block' : 'none';
                toggleDebug.textContent = isHidden ? 'Hide JSON' : 'Show JSON';
            });
        }
    </script>
</body>
</html>
