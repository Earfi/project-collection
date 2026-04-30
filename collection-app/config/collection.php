<?php

$raw = trim((string) env('WATERMARK_TEXT', 'EARF PICHAYA'));
// Treat legacy placeholder from .env (YOUR_NAME, "YOUR NAME", etc.) as unset.
$compact = strtolower(preg_replace('/[\s_\-]+/u', '', $raw) ?: '');
$watermark = ($raw === '' || $compact === 'yourname')
    ? 'EARF PICHAYA'
    : $raw;

return [

    /*
    |--------------------------------------------------------------------------
    | Image watermark label
    |--------------------------------------------------------------------------
    |
    | Shown on stored images (GD) and as an on-screen overlay on thumbnails.
    | Use config() in app code — not env() — so config caching works.
    |
    */

    'watermark_text' => $watermark,

];
