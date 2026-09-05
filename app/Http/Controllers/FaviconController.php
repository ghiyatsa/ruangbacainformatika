<?php

namespace App\Http\Controllers;

use App\Support\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FaviconController extends Controller
{
    public function svg(Request $request, SiteSettings $siteSettings): Response
    {
        $theme = $request->string('theme')->toString() ?: null;
        $color = $siteSettings->paletteHexColor($theme);

        $svg = <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg viewBox="0 0 3400 3400" version="1.1" xmlns="http://www.w3.org/2000/svg">
    <g>
        <path d="M3400,1287.283l0,825.433c0,710.471 -576.812,1287.283 -1287.283,1287.283l-825.433,0c-710.471,0 -1287.283,-576.812 -1287.283,-1287.283l0,-825.433c0,-710.471 576.812,-1287.283 1287.283,-1287.283l825.433,0c710.471,0 1287.283,576.812 1287.283,1287.283Z" fill="{$color}"/>
        <path d="M1063.465,2681.729l1273.07,0" fill="none" stroke="#fff" stroke-width="291.36"/>
        <path d="M1063.465,719.271l1273.07,0" fill="none" stroke="#fff" stroke-width="291.36"/>
        <path d="M732.132,1063.465l0,1273.07" fill="none" stroke="#fff" stroke-width="291.36"/>
        <path d="M2666.868,1063.465l0,1273.07" fill="none" stroke="#fff" stroke-width="291.36"/>
        <path d="M1148.294,2220.218l559.706,-520.218l-559.706,-520.218" fill="none" stroke="#fff" stroke-width="180.11"/>
        <path d="M2262.092,2220.218l-497.554,0" fill="none" stroke="#fff" stroke-width="167.5"/>
        <path d="M2086.034,1179.782l0,342.905" fill="none" stroke="#fff" stroke-opacity="0.79" stroke-width="96.74"/>
        <path d="M2211.284,1179.782l0,342.905" fill="none" stroke="#fff" stroke-opacity="0.51" stroke-width="96.74"/>
        <path d="M1960.783,1179.782l0,342.905" fill="none" stroke="#fff" stroke-width="96.74"/>
        <path d="M2336.535,1179.782l0,342.905" fill="none" stroke="#fff" stroke-opacity="0.22" stroke-width="96.74"/>
    </g>
</svg>
SVG;

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
