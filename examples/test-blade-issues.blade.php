@extends('layouts.app')

@section('title', 'User Profile')

@section('content')
<div class="container">
    {{-- XSS Vulnerability Example --}}
    <h1>{!! $userInput !!}</h1>
    
    {{-- Missing CSRF Protection --}}
    <form method="POST" action="/update-profile">
        <input type="hidden" name="user_id" value="{{ $userId }}">
        <input type="text" name="name" value="{{ $user->name }}">
        <button type="submit">Update</button>
    </form>
    
    {{-- N+1 Query Potential --}}
    @foreach($users as $user)
        <div class="user-card">
            <h3>{{ $user->name }}</h3>
            <p>Posts: {{ $user->posts->count() }}</p>
            <p>Comments: {{ $user->comments->count() }}</p>
        </div>
    @endforeach
    
    {{-- PHP in Template --}}
    @php
        $complexCalculation = 0;
        for($i = 0; $i < 100; $i++) {
            $complexCalculation += ($i * 2) + random_int(1, 10);
        }
        $averageScore = $complexCalculation / 100;
    @endphp
    
    {{-- Inline Styles --}}
    <div style="color: red; font-size: 18px; margin: 20px; padding: 15px; background: linear-gradient(45deg, #ff0000, #00ff00);">
        Complex inline styling
    </div>
    
    {{-- Missing Alt Text --}}
    <img src="{{ $user->avatar }}" class="avatar">
    
    {{-- Form Input Without Label --}}
    <input type="email" name="email" placeholder="Enter email">
    
    {{-- Hardcoded URL --}}
    <a href="https://example.com/external-service">External Link</a>
    
    {{-- Deep Nesting --}}
    @if($user->isActive())
        @if($user->hasRole('admin'))
            @foreach($user->permissions as $permission)
                @if($permission->isEnabled())
                    @if($permission->category === 'admin')
                        @foreach($permission->actions as $action)
                            @if($action->isAllowed())
                                <span>{{ $action->name }}</span>
                            @endif
                        @endforeach
                    @endif
                @endif
            @endforeach
        @endif
    @endif
    
    {{-- Unsafe Unescaped Variables --}}
    <div class="content">
        {!! $userGeneratedContent !!}
        {!! $commentBody !!}
    </div>
    
    {{-- Direct Request Input --}}
    <p>Search: {{ request()->input('search') }}</p>
    
    {{-- Superglobal Usage --}}
    <p>User Agent: {{ $_SERVER['HTTP_USER_AGENT'] }}</p>
    
    {{-- Deprecated Syntax --}}
    {{{ $oldStyleEscaping }}}
    
    {{-- Style Tags in Template --}}
    <style>
        .custom-style {
            background: red;
            color: white;
        }
    </style>
</div>

{{-- Unclosed Section --}}
@section('extra-styles')
    <link rel="stylesheet" href="/custom.css">
{{-- Missing @endsection --}}

@endsection
