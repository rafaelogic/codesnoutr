# Blade Template Rules Documentation

## Overview

The BladeRules engine provides comprehensive analysis of Laravel Blade templates, detecting security vulnerabilities, performance issues, code quality problems, accessibility concerns, and SEO optimization opportunities.

## Rule Categories

### 🔒 Security Rules

#### XSS Vulnerabilities (`blade.xss_unescaped`)
- **Detects**: Unescaped output `{!! !!}` that could lead to XSS attacks
- **Severity**: High
- **Example**:
  ```blade
  {{-- ❌ Dangerous --}}
  <h1>{!! $userInput !!}</h1>
  
  {{-- ✅ Safe --}}
  <h1>{{ $userInput }}</h1>
  <div>{!! $safeHtmlContent !!}</div> {{-- Only for trusted HTML --}}
  ```

#### CSRF Protection (`blade.missing_csrf`)
- **Detects**: Forms with state-changing methods missing CSRF protection
- **Severity**: Critical
- **Example**:
  ```blade
  {{-- ❌ Missing CSRF --}}
  <form method="POST" action="/update">
      <input type="text" name="name">
      <button type="submit">Submit</button>
  </form>
  
  {{-- ✅ With CSRF --}}
  <form method="POST" action="/update">
      @csrf
      <input type="text" name="name">
      <button type="submit">Submit</button>
  </form>
  ```

#### Input Sanitization (`blade.direct_request_input`, `blade.superglobal_usage`)
- **Detects**: Direct use of request data or superglobals
- **Severity**: High
- **Example**:
  ```blade
  {{-- ❌ Dangerous --}}
  <p>{{ request()->input('search') }}</p>
  <p>{{ $_GET['param'] }}</p>
  
  {{-- ✅ Safe --}}
  <p>{{ $searchTerm }}</p> {{-- Validated in controller --}}
  ```

### ⚡ Performance Rules

#### N+1 Query Detection (`blade.potential_n1_query`)
- **Detects**: Relationship access within loops that may cause N+1 queries
- **Severity**: Medium
- **Example**:
  ```blade
  {{-- ❌ Potential N+1 --}}
  @foreach($users as $user)
      <div>{{ $user->posts->count() }}</div>
  @endforeach
  
  {{-- ✅ Eager loaded --}}
  {{-- In controller: $users = User::withCount('posts')->get() --}}
  @foreach($users as $user)
      <div>{{ $user->posts_count }}</div>
  @endforeach
  ```

#### Inline Styles (`blade.inline_styles`, `blade.style_tags`)
- **Detects**: Inline styles and style tags that impact performance
- **Severity**: Info/Medium
- **Example**:
  ```blade
  {{-- ❌ Inline styles --}}
  <div style="color: red; font-size: 18px;">Content</div>
  <style>.custom { color: blue; }</style>
  
  {{-- ✅ External CSS --}}
  <div class="text-red-500 text-lg">Content</div>
  @push('styles')
      <link rel="stylesheet" href="/custom.css">
  @endpush
  ```

#### Performance in Loops (`blade.deeply_nested_loops`, `blade.include_in_loop`)
- **Detects**: Deep nesting and includes in loops
- **Severity**: Medium

### 📝 Code Quality Rules

#### Template Complexity (`blade.high_complexity`, `blade.deep_nesting`)
- **Detects**: Overly complex templates with deep nesting
- **Severity**: Medium
- **Thresholds**: 
  - Complexity score > 15
  - Nesting depth > 5 levels

#### PHP in Templates (`blade.php_in_template`, `blade.php_tags`)
- **Detects**: Complex PHP logic in templates
- **Severity**: Medium/High
- **Example**:
  ```blade
  {{-- ❌ Complex PHP logic --}}
  @php
      $complexCalculation = 0;
      for($i = 0; $i < 100; $i++) {
          $complexCalculation += $i;
      }
  @endphp
  
  {{-- ❌ PHP tags --}}
  <?php echo "Bad practice"; ?>
  
  {{-- ✅ Simple assignment --}}
  @php $activeUsers = $users->where('active', true); @endphp
  
  {{-- ✅ Move to controller --}}
  {{-- Controller: $activeUsers = $users->where('active', true); --}}
  ```

#### Deprecated Syntax (`blade.deprecated_syntax`)
- **Detects**: Old Blade syntax that may be removed
- **Example**:
  ```blade
  {{-- ❌ Deprecated --}}
  {{{ $escapedContent }}}
  
  {{-- ✅ Current syntax --}}
  {{ $escapedContent }}
  ```

#### Code Organization (`blade.unused_section`, `blade.unclosed_section`)
- **Detects**: Structural issues with sections and components

### ♿ Accessibility Rules

#### Missing Alt Text (`blade.missing_alt_text`)
- **Detects**: Images without alt attributes
- **Severity**: Medium
- **Example**:
  ```blade
  {{-- ❌ Missing alt text --}}
  <img src="/avatar.jpg">
  
  {{-- ✅ With alt text --}}
  <img src="/avatar.jpg" alt="User profile picture">
  ```

#### Form Labels (`blade.missing_form_label`)
- **Detects**: Form inputs without proper labels
- **Severity**: Medium
- **Example**:
  ```blade
  {{-- ❌ No label --}}
  <input type="text" name="username">
  
  {{-- ✅ With label --}}
  <label for="username">Username</label>
  <input type="text" id="username" name="username">
  
  {{-- ✅ With aria-label --}}
  <input type="text" name="search" aria-label="Search products">
  ```

### 🔍 SEO Rules

#### Missing Meta Elements (`blade.missing_title`, `blade.missing_meta_description`)
- **Detects**: Missing title tags and meta descriptions in layout files
- **Severity**: Medium/Info
- **Example**:
  ```blade
  {{-- ✅ Good SEO structure --}}
  <head>
      <title>{{ $title ?? 'Default Title' }}</title>
      <meta name="description" content="{{ $metaDescription ?? 'Default description' }}">
  </head>
  ```

### 🔧 Maintainability Rules

#### Hardcoded Values (`blade.hardcoded_url`, `blade.hardcoded_email`)
- **Detects**: Hardcoded URLs and email addresses
- **Severity**: Info
- **Example**:
  ```blade
  {{-- ❌ Hardcoded --}}
  <a href="https://external-service.com">Link</a>
  <p>Contact: support@company.com</p>
  
  {{-- ✅ Configurable --}}
  <a href="{{ config('services.external.url') }}">Link</a>
  <p>Contact: {{ config('mail.support_email') }}</p>
  ```

#### Duplicated Code (`blade.duplicated_code`)
- **Detects**: Repeated code blocks that could be extracted
- **Severity**: Info

#### Unused Variables (`blade.unused_variable`)
- **Detects**: Variables assigned but never used
- **Severity**: Info

## Configuration

Enable/disable Blade rules in `config/codesnoutr.php`:

```php
'scanners' => [
    'blade' => [
        'enabled' => true,
        'rules' => [
            'xss_vulnerabilities' => true,
            'csrf_protection' => true,
            'template_complexity' => true,
            'performance_optimization' => true,
            'accessibility_compliance' => true,
            'seo_optimization' => true,
            'best_practices' => true,
            'code_quality' => true,
        ],
    ],
],
```

## Best Practices

### Security Best Practices
1. **Always escape user input** with `{{ }}` unless you're certain it's safe HTML
2. **Include CSRF protection** in all state-changing forms
3. **Validate input in controllers** before passing to views
4. **Avoid superglobals** in templates

### Performance Best Practices
1. **Eager load relationships** to prevent N+1 queries
2. **Use external CSS** instead of inline styles
3. **Minimize complex logic** in templates
4. **Use components** for reusable UI elements

### Code Quality Best Practices
1. **Keep templates simple** - move complex logic to controllers
2. **Use proper template inheritance** with `@extends` and `@section`
3. **Organize code with components** and includes
4. **Follow consistent naming conventions**

### Accessibility Best Practices
1. **Always include alt text** for images
2. **Use proper form labels** and ARIA attributes
3. **Ensure keyboard navigation** works properly
4. **Test with screen readers**

### SEO Best Practices
1. **Include unique page titles** and meta descriptions
2. **Use semantic HTML** structure
3. **Optimize for Core Web Vitals**
4. **Include structured data** when appropriate

## Examples

### Good Blade Template Structure

```blade
@extends('layouts.app')

@section('title', 'User Profile - ' . $user->name)

@section('meta-description')
    <meta name="description" content="Profile page for {{ $user->name }}">
@endsection

@section('content')
<div class="container">
    <h1>{{ $user->name }}</h1>
    
    @can('edit', $user)
        <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
            Edit Profile
        </a>
    @endcan
    
    @if($user->posts->isNotEmpty())
        <x-user-posts :posts="$user->posts" />
    @else
        <p>No posts yet.</p>
    @endif
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ mix('css/profile.css') }}">
@endpush
```

### Common Issues and Solutions

#### Issue: XSS Vulnerability
```blade
{{-- ❌ Problem --}}
<div>{!! $userComment !!}</div>

{{-- ✅ Solution --}}
<div>{{ $userComment }}</div>
{{-- OR for safe HTML: --}}
<div>{!! Purifier::clean($userComment) !!}</div>
```

#### Issue: N+1 Query
```blade
{{-- ❌ Problem --}}
@foreach($posts as $post)
    <p>Author: {{ $post->user->name }}</p>
@endforeach

{{-- ✅ Solution: Eager load in controller --}}
// Controller: $posts = Post::with('user')->get();
@foreach($posts as $post)
    <p>Author: {{ $post->user->name }}</p>
@endforeach
```

#### Issue: Missing Accessibility
```blade
{{-- ❌ Problem --}}
<img src="/photo.jpg">
<input type="text" name="search">

{{-- ✅ Solution --}}
<img src="/photo.jpg" alt="Team photo from company retreat">
<label for="search">Search products</label>
<input type="text" id="search" name="search">
```

## Integration with IDE

Many IDEs can be configured to highlight these issues as you write Blade templates. Consider using:

- **Laravel Blade formatter** extensions
- **Template linting** plugins
- **Accessibility checkers**
- **Security scanning** extensions

## Automated Testing

Include Blade template scanning in your CI/CD pipeline:

```bash
# Scan all Blade templates
php artisan codesnoutr:scan codebase --categories=blade

# Focus on security issues
php artisan codesnoutr:scan directory resources/views --categories=security
```

---

*For more information about CodeSnoutr and its scanning capabilities, see the main [README.md](../README.md) file.*
