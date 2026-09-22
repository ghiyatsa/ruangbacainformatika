import type { HLJSApi } from 'highlight.js';

/**
 * Icon markup untuk tombol salin. Ditulis sebagai string SVG karena tombol
 * dibuat lewat DOM API (bukan React) setelah konten HTML di-inject.
 */
const COPY_ICON =
    '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>';

const CHECK_ICON =
    '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';

/**
 * Label ramah untuk header blok kode. Hanya untuk tampilan; deteksi bahasa
 * tetap memakai alias bawaan highlight.js.
 */
const LANGUAGE_LABELS: Record<string, string> = {
    bash: 'Bash',
    shell: 'Shell',
    sh: 'Shell',
    zsh: 'Shell',
    c: 'C',
    'c++': 'C++',
    cpp: 'C++',
    'c#': 'C#',
    csharp: 'C#',
    cs: 'C#',
    css: 'CSS',
    diff: 'Diff',
    go: 'Go',
    graphql: 'GraphQL',
    ini: 'INI',
    toml: 'TOML',
    java: 'Java',
    javascript: 'JavaScript',
    js: 'JavaScript',
    jsx: 'JSX',
    json: 'JSON',
    kotlin: 'Kotlin',
    less: 'Less',
    lua: 'Lua',
    makefile: 'Makefile',
    markdown: 'Markdown',
    md: 'Markdown',
    objectivec: 'Objective-C',
    perl: 'Perl',
    php: 'PHP',
    plaintext: 'Teks',
    text: 'Teks',
    python: 'Python',
    py: 'Python',
    r: 'R',
    ruby: 'Ruby',
    rust: 'Rust',
    scss: 'SCSS',
    sql: 'SQL',
    swift: 'Swift',
    typescript: 'TypeScript',
    ts: 'TypeScript',
    tsx: 'TSX',
    vbnet: 'VB.NET',
    wasm: 'WebAssembly',
    xml: 'HTML/XML',
    html: 'HTML',
    yaml: 'YAML',
    yml: 'YAML',
};

/**
 * Daftar bahasa untuk mode deteksi otomatis. Dibatasi pada bahasa yang lazim
 * dipakai di artikel supaya hasil deteksi lebih akurat (subset penuh membuat
 * cuplikan shell/teks polos sering salah tebak, mis. "C#").
 */
const AUTO_DETECT_LANGUAGES = [
    'bash',
    'shell',
    'javascript',
    'typescript',
    'php',
    'json',
    'xml',
    'css',
    'python',
    'yaml',
    'ini',
    'diff',
    'markdown',
    'sql',
];

/**
 * Penanda perintah baris terminal. Editor artikel tidak menyimpan bahasa pada
 * blok kode, sedangkan auto-detect highlight.js kerap salah untuk cuplikan
 * `git`/`npm`/`artisan` (sering terbaca sebagai C# atau SQL). Heuristik ringan
 * ini memastikan cuplikan perintah diwarnai sebagai shell.
 */
const SHELL_COMMAND_PATTERN =
    /^(?:\$|>)?\s*(?:sudo\s+)?(?:git|npm|npx|pnpm|yarn|composer|php\s+artisan|docker|docker-compose|kubectl|curl|wget|apt|apt-get|brew|chmod|chown|mkdir|cd|ls|rm|cp|mv|cat|echo|grep|sed|awk|ssh|scp|make|python3?|pip3?|node|bun|deno|tar|zip|unzip|systemctl|export|source)\b/;

function looksLikeShell(source: string): boolean {
    const lines = source
        .split('\n')
        .map((line) => line.trim())
        .filter((line) => line !== '' && !/^(?:#|\/\/)/.test(line));

    if (lines.length === 0) {
        return false;
    }

    const commandLines = lines.filter((line) =>
        SHELL_COMMAND_PATTERN.test(line),
    ).length;

    return commandLines >= Math.max(1, Math.ceil(lines.length * 0.6));
}

let highlighterPromise: Promise<HLJSApi> | null = null;

/**
 * Muat highlight.js (build "common" berisi ~35 bahasa populer) secara lazy
 * supaya tidak membebani bundel awal maupun render SSR.
 */
function loadHighlighter(): Promise<HLJSApi> {
    if (highlighterPromise === null) {
        highlighterPromise = import('highlight.js/lib/common').then(
            (module) => module.default,
        );
    }

    return highlighterPromise;
}

function escapeHtml(value: string): string {
    return value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function resolveLanguage(pre: HTMLElement, code: HTMLElement): string | null {
    const classNames = `${code.className} ${pre.className}`;
    const match = /(?:^|\s)language-([a-z0-9#+._-]+)/i.exec(classNames);
    const language = match?.[1];

    return language ? language.toLowerCase() : null;
}

function formatLanguageLabel(language: string | null): string {
    if (language === null) {
        return 'Kode';
    }

    return LANGUAGE_LABELS[language] ?? language.toUpperCase();
}

async function copyText(text: string): Promise<boolean> {
    if (typeof navigator !== 'undefined' && navigator.clipboard?.writeText) {
        try {
            await navigator.clipboard.writeText(text);

            return true;
        } catch {
            // Lanjut ke jalur fallback di bawah.
        }
    }

    if (typeof document === 'undefined') {
        return false;
    }

    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'fixed';
    textarea.style.top = '-9999px';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();

    try {
        return document.execCommand('copy');
    } catch {
        return false;
    } finally {
        textarea.remove();
    }
}

async function handleCopy(
    button: HTMLButtonElement,
    text: string,
): Promise<void> {
    const copied = await copyText(text);
    const label = button.querySelector('.code-block__copy-label');
    const icon = button.querySelector('.code-block__copy-icon');

    if (!copied) {
        button.classList.add('is-error');

        if (label) {
            label.textContent = 'Gagal';
        }

        window.setTimeout(() => {
            button.classList.remove('is-error');

            if (label) {
                label.textContent = 'Salin';
            }
        }, 2000);

        return;
    }

    button.classList.add('is-copied');

    if (label) {
        label.textContent = 'Tersalin';
    }

    if (icon) {
        icon.innerHTML = CHECK_ICON;
    }

    window.setTimeout(() => {
        button.classList.remove('is-copied');

        if (label) {
            label.textContent = 'Salin';
        }

        if (icon) {
            icon.innerHTML = COPY_ICON;
        }
    }, 2000);
}

function decorateCodeBlock(
    pre: HTMLPreElement,
    source: string,
    language: string | null,
): void {
    const wrapper = document.createElement('div');
    wrapper.className = 'code-block';

    const header = document.createElement('div');
    header.className = 'code-block__header';

    const label = document.createElement('span');
    label.className = 'code-block__lang';
    label.textContent = formatLanguageLabel(language);

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'code-block__copy';
    button.setAttribute('aria-label', 'Salin kode');
    button.title = 'Salin kode';
    button.innerHTML = `<span class="code-block__copy-icon">${COPY_ICON}</span><span class="code-block__copy-label">Salin</span>`;
    button.addEventListener('click', () => {
        void handleCopy(button, source);
    });

    header.append(label, button);
    wrapper.append(header);

    const parent = pre.parentNode;

    if (parent) {
        parent.insertBefore(wrapper, pre);
    }

    wrapper.append(pre);
}

/**
 * Tingkatkan setiap `<pre>` di dalam `root`: beri warna sintaks ala IDE
 * (highlight.js) dan tambahkan tombol salin. Idempoten — blok yang sudah
 * diproses ditandai `data-code-enhanced` dan dilewati pada pemanggilan ulang.
 */
export async function enhanceCodeBlocks(root: HTMLElement): Promise<void> {
    const blocks = Array.from(
        root.querySelectorAll<HTMLPreElement>('pre:not([data-code-enhanced])'),
    );

    if (blocks.length === 0) {
        return;
    }

    let hljs: HLJSApi;

    try {
        hljs = await loadHighlighter();
    } catch {
        return;
    }

    for (const pre of blocks) {
        pre.dataset.codeEnhanced = 'true';

        const code = pre.querySelector('code') ?? pre;
        const source = code.textContent ?? '';

        if (source.trim() === '') {
            continue;
        }

        const requested = resolveLanguage(pre, code);

        let highlighted = escapeHtml(source);
        let resolved: string | null = requested;

        try {
            if (requested !== null && hljs.getLanguage(requested)) {
                highlighted = hljs.highlight(source, {
                    language: requested,
                    ignoreIllegals: true,
                }).value;
            } else if (looksLikeShell(source)) {
                highlighted = hljs.highlight(source, {
                    language: 'bash',
                    ignoreIllegals: true,
                }).value;
                resolved = 'bash';
            } else {
                const auto = hljs.highlightAuto(source, AUTO_DETECT_LANGUAGES);
                highlighted = auto.value;
                resolved = auto.language ?? null;
            }
        } catch {
            highlighted = escapeHtml(source);
        }

        code.innerHTML = highlighted;
        code.classList.add('hljs');

        decorateCodeBlock(pre, source, resolved);
    }
}
