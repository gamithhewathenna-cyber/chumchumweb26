<?php
/** Inline SVG icons. */

function icon_arrow(): string
{
    return '<svg viewBox="0 0 24 12" width="24" height="12" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="M1 6h21M17 1.5 22 6l-5 4.5"/></svg>';
}

function icon_chevron(string $dir = 'left'): string
{
    $d = $dir === 'left' ? 'M17 3 5 18l12 15' : 'M5 3l12 15L5 33';
    return '<svg viewBox="0 0 22 36" width="22" height="36" fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"><path d="' . $d . '"/></svg>';
}

function icon_quote(): string
{
    return '<svg viewBox="0 0 54 40" width="54" height="40" fill="currentColor"><path d="M23.3 9.6C14.8 10.9 9.3 16 9.3 22.3c0 .5 0 1 .1 1.4a9 9 0 1 1-8.9 8.4c-.1-.6-.1-1.1-.1-1.7C.4 16.6 9.6 6 22.4 4.4l.9 5.2Zm29.3 0c-8.5 1.3-14 6.4-14 12.7l.1 1.4a9 9 0 1 1-9 8.4l-.1-1.7C29.7 16.6 38.9 6 51.7 4.4l.9 5.2Z"/></svg>';
}

function icon_facebook(): string
{
    return '<svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M13.4 21v-7.6h2.6l.4-3h-3V8.5c0-.9.3-1.5 1.5-1.5h1.6V4.3c-.3 0-1.2-.1-2.3-.1-2.3 0-3.9 1.4-3.9 4v2.2H7.7v3h2.6V21h3.1Z"/></svg>';
}

function icon_instagram(): string
{
    return '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="4.5"/><circle cx="12" cy="12" r="3.6"/><circle cx="16.9" cy="7.1" r=".6" fill="currentColor" stroke="none"/></svg>';
}
