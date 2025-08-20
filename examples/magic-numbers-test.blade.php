@extends('layouts.app')

@section('content')
<div class="container">
    {{-- These should NOT be flagged as magic numbers --}}
    
    {{-- HTML attributes --}}
    <img src="/image.jpg" width="24" height="24" alt="Icon">
    <input type="text" maxlength="255" size="30">
    <textarea rows="10" cols="50"></textarea>
    <table>
        <td colspan="3" rowspan="2">Cell</td>
    </table>
    
    {{-- CSS properties in style attributes --}}
    <div style="z-index: 9999; font-size: 16px; width: 100px; height: 50px;">
        Styled content
    </div>
    
    <div style="margin: 10px; padding: 20px; border-radius: 5px;">
        More styling
    </div>
    
    {{-- CSS units --}}
    <div style="width: 24px; height: 16em; font-size: 1.5rem; margin: 10%;">
        Units
    </div>
    
    {{-- Color values --}}
    <div style="color: #ff0000; background: rgb(255, 255, 255); border: 1px solid rgba(0, 0, 0, 0.5);">
        Colors
    </div>
    
    {{-- Tailwind/Utility classes with numbers --}}
    <div class="w-24 h-16 text-lg p-4 m-2 rounded-lg">
        Utility classes
    </div>
    
    {{-- Data attributes --}}
    <div data-count="100" data-index="42" aria-level="3">
        Data attributes
    </div>
    
    {{-- These SHOULD be flagged as magic numbers (in PHP context) --}}
    @php
        $magicNumber = 42; // This should be flagged
        $threshold = 100; // This might be OK (common value)
        $complexCalculation = 123456; // This should be flagged
    @endphp
    
    {{-- PHP variables with magic numbers --}}
    @if($user->age > 65) {{-- This should be flagged --}}
        Senior discount available
    @endif
    
    {{-- Loop with magic number --}}
    @for($i = 0; $i < 50; $i++) {{-- 50 should be flagged --}}
        <div>Item {{ $i }}</div>
    @endfor
</div>

{{-- CSS in style blocks should not be flagged --}}
<style>
    .custom-element {
        width: 320px;
        height: 240px;
        z-index: 1000;
        font-size: 14px;
        line-height: 1.5;
        border-radius: 8px;
    }
    
    @media (max-width: 768px) {
        .responsive {
            font-size: 12px;
        }
    }
</style>
@endsection
