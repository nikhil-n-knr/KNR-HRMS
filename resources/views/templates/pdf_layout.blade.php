@if(!($isWeb ?? false))
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $template->name }}</title>
@endif
    <style>
        /* Common Styles */
        .template-container { font-family: sans-serif; }
        
        /* PDF Specific - Global */
        /* PDF Specific - Global */
        /* Increased top/bottom margins to prevent overlap with standard headers */
        @page { margin: 140px 50px 100px 50px; }
        
        @if(!($isWeb ?? false))
            * { box-sizing: border-box; }
            body { font-family: sans-serif; margin: 0; padding: 0; }
            /* Position Fixed elements in the margin areas */
            header { position: fixed; top: -100px; left: 0px; right: 0px; height: 80px; text-align: center; }
            footer { position: fixed; bottom: -60px; left: 0px; right: 0px; height: 50px; text-align: center; }
            .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 100px; color: rgba(0,0,0,0.1); z-index: -1000; }
        @else
            /* Web Preview Styles - Scoped to .web-template-wrapper */
            .web-template-wrapper { 
                position: relative; 
                width: 210mm; /* A4 width */
                min-height: 297mm; /* A4 height */
                margin: 0 auto 30px auto; 
                padding: 20mm 25mm; /* Exact match to Template Editor margins */
                background: white;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                overflow: hidden;
                
                /* Flexbox Layout for Web Mode */
                display: flex;
                flex-direction: column;
                justify-content: space-between; 
                text-align: left; /* Force Left Alignment (override parent center) */
            }
            .web-template-wrapper header { position: relative; margin-bottom: 20px; }
            .web-template-wrapper footer { position: relative; margin-top: 20px; }
            .web-template-wrapper main { flex: 1; display: flex; flex-direction: column; }
            .web-template-wrapper .watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 100px; color: rgba(0,0,0,0.05); z-index: 0; pointer-events: none; }
            .web-template-wrapper .page-content { position: relative; z-index: 10; flex: 1; }
        @endif

        /* Custom CSS from Template */
        {!! $css !!}
    </style>
@if(!($isWeb ?? false))
</head>
<body>
@endif

@php
    $watermarkType = $layoutConfig['watermarkType'] ?? 'text';
    $headerType = $layoutConfig['headerType'] ?? 'html';
    $footerType = $layoutConfig['footerType'] ?? 'html';

    // Helper for image path
    $imgPath = function($path) use ($isWeb) {
        if (!$path) return '';
        return ($isWeb ?? false) ? asset('storage/' . $path) : public_path('storage/' . $path);
    };

    // Prepare Blocks
    $watermarkBlock = '';
    if($watermarkType === 'image' && !empty($template->watermark_image)) {
        // MATCH EDIT.VUE: No rotation for images, just centered.
        // Web Mode: Override the class rotation. Keep translate(-50%, -50%) for centering if keeping strict absolute pos, 
        // OR switch to inset-0 flex centering if we change the class. 
        // Simplest: Override transform to remove rotation.
        $style = ($isWeb ?? false) 
            ? 'transform: translate(-50%, -50%);' // Web: Center, no rotate
            : 'transform: none; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;'; // PDF: Full spread
            
        $watermarkBlock = '<div class="watermark" style="'. $style .'">
            <img src="'. $imgPath($template->watermark_image) .'" style="width: 50%; opacity: 0.1;">
        </div>';
    } elseif($template->watermark_text) {
        $watermarkBlock = '<div class="watermark">'. $template->watermark_text .'</div>';
    }

    // Capture Header
    ob_start();
@endphp
    <header>
        @if($headerType === 'image' && !empty($template->header_image))
            <img src="{{ $imgPath($template->header_image) }}" style="width: 100%; max-height: 100px; object-fit: contain;">
        @elseif($headerType === 'structured')
            @php
                $layout = $layoutConfig['headerLayout'] ?? 'logo-left';
                $logoHeight = $layoutConfig['logoHeight'] ?? 100;
                $logoUrl = $imgPath($template->header_image);
            @endphp
            
            <table width="100%" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                <tr>
                    @if($layout === 'logo-left' || $layout === 'center-spread')
                        <td style="width: {{ $logoHeight + 20 }}px; vertical-align: middle;">
                            @if($logoUrl)
                                <img src="{{ $logoUrl }}" style="height: {{ $logoHeight }}px; width: auto; display: block;">
                            @endif
                        </td>
                        <td style="vertical-align: middle; text-align: {{ $layout === 'center-spread' ? 'center' : 'right' }};">
                            {!! $header !!}
                        </td>
                    @elseif($layout === 'logo-right')
                        <td style="vertical-align: middle; text-align: left;">
                            {!! $header !!}
                        </td>
                        <td style="width: {{ $logoHeight + 20 }}px; vertical-align: middle; text-align: right;">
                             @if($logoUrl)
                                <img src="{{ $logoUrl }}" style="height: {{ $logoHeight }}px; width: auto; display: block; margin-left: auto;">
                            @endif
                        </td>
                    @endif
                </tr>
            </table>
        @else
            {!! $header !!}
        @endif
    </header>
@php $headerBlock = ob_get_clean(); @endphp

@php
    // Capture Footer
    ob_start();
@endphp
    <footer>
        @if($footerType === 'image' && !empty($template->footer_image))
            <img src="{{ $imgPath($template->footer_image) }}" style="width: 100%; max-height: 100px; object-fit: contain;">
        @elseif($footerType === 'structured')
             @php
                $layout = $layoutConfig['footerLayout'] ?? 'logo-left';
                $logoHeight = $layoutConfig['footerLogoHeight'] ?? 100;
                $logoUrl = $imgPath($template->footer_image);
            @endphp
             <table width="100%" style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <tr>
                    @if($layout === 'logo-left' || $layout === 'center-spread')
                        <td style="width: {{ $logoHeight + 20 }}px; vertical-align: middle;">
                            @if($logoUrl)
                                <img src="{{ $logoUrl }}" style="height: {{ $logoHeight }}px; width: auto; display: block;">
                            @endif
                        </td>
                        <td style="vertical-align: middle; text-align: {{ $layout === 'center-spread' ? 'center' : 'right' }};">
                            {!! $footer !!}
                        </td>
                    @endif
                </tr>
            </table>
        @else
            {!! $footer !!}
        @endif
    </footer>
@php $footerBlock = ob_get_clean(); @endphp

@php
    // Capture Main
    ob_start();
@endphp
    <main>
        @foreach($pages as $page)
            <div class="page-content">
                {!! $page !!}
            </div>
            @if(!$loop->last)
                <div style="page-break-after: always; {{ ($isWeb ?? false) ? 'border-bottom: 2px dashed #eee; margin: 40px 0; padding-bottom: 40px;' : '' }}"></div>
            @endif
        @endforeach
    </main>
@php $mainBlock = ob_get_clean(); @endphp

<div class="{{ ($isWeb ?? false) ? 'web-template-wrapper' : '' }}">
    {!! $watermarkBlock !!}
    
    @if($isWeb ?? false)
        <!-- Web Layout: Header -> Main -> Footer -->
        {!! $headerBlock !!}
        {!! $mainBlock !!}
        {!! $footerBlock !!}
    @else
        <!-- PDF Layout: Header -> Footer -> Main (DomPDF Requirement) -->
        {!! $headerBlock !!}
        {!! $footerBlock !!}
        {!! $mainBlock !!}
    @endif
</div>

@if(!($isWeb ?? false))
</body>
</html>
@endif
