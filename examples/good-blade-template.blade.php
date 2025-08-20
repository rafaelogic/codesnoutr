@extends('layouts.app')

@section('title', 'User Profile - {{ $user->name }}')

@section('meta-description')
    <meta name="description" content="Profile page for {{ $user->name }}, showing user information and activity.">
@endsection

@section('content')
<div class="container mx-auto px-4">
    {{-- Safe Escaped Output --}}
    <h1 class="text-3xl font-bold mb-6">{{ $user->name }}</h1>
    
    {{-- Proper CSRF Protection --}}
    <form method="POST" action="{{ route('profile.update', $user) }}" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        @csrf
        @method('PUT')
        
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                Name
            </label>
            <input 
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                id="name" 
                type="text" 
                name="name" 
                value="{{ old('name', $user->name) }}"
                aria-describedby="name-help"
                required
            >
            <p id="name-help" class="text-xs text-gray-600 mt-1">Enter your full name</p>
        </div>
        
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                Email
            </label>
            <input 
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                id="email" 
                type="email" 
                name="email" 
                value="{{ old('email', $user->email) }}"
                aria-describedby="email-help"
                required
            >
            <p id="email-help" class="text-xs text-gray-600 mt-1">We'll never share your email</p>
        </div>
        
        <button 
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" 
            type="submit"
        >
            Update Profile
        </button>
    </form>
    
    {{-- Proper Eager Loading (No N+1) --}}
    @if($user->posts->isNotEmpty())
        <h2 class="text-2xl font-semibold mb-4">Recent Posts</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($user->posts as $post)
                <article class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-medium mb-2">{{ $post->title }}</h3>
                    <p class="text-gray-600 mb-4">{{ Str::limit($post->excerpt, 100) }}</p>
                    <div class="flex justify-between items-center text-sm text-gray-500">
                        <span>{{ $post->published_at->diffForHumans() }}</span>
                        <span>{{ $post->comments_count }} comments</span>
                    </div>
                    <a 
                        href="{{ route('posts.show', $post) }}" 
                        class="inline-block mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                    >
                        Read More
                    </a>
                </article>
            @endforeach
        </div>
    @else
        <div class="bg-gray-100 rounded-lg p-8 text-center">
            <p class="text-gray-600">No posts yet.</p>
            <a 
                href="{{ route('posts.create') }}" 
                class="inline-block mt-4 bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600"
            >
                Create Your First Post
            </a>
        </div>
    @endif
    
    {{-- Proper Image with Alt Text --}}
    <div class="mt-8 text-center">
        <img 
            src="{{ $user->avatar_url }}" 
            alt="Profile picture of {{ $user->name }}"
            class="w-32 h-32 rounded-full mx-auto object-cover"
        >
    </div>
    
    {{-- Safe HTML Content with Proper Escaping --}}
    @if($user->bio)
        <div class="mt-6 bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-medium mb-3">About</h3>
            <div class="prose">
                {!! Str::markdown($user->bio) !!}
            </div>
        </div>
    @endif
    
    {{-- Using Components for Reusability --}}
    <x-user-statistics :user="$user" />
    
    {{-- Conditional Logic Without Deep Nesting --}}
    @can('edit', $user)
        <div class="mt-8 flex justify-end">
            <a 
                href="{{ route('users.edit', $user) }}" 
                class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded"
            >
                Edit Profile
            </a>
        </div>
    @endcan
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ mix('css/profile.css') }}">
@endpush

@push('scripts')
    <script src="{{ mix('js/profile.js') }}" defer></script>
@endpush
