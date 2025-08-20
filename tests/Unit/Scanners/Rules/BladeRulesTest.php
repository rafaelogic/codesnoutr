<?php

namespace Tests\Unit\Scanners\Rules;

use Tests\TestCase;
use Rafaelogic\CodeSnoutr\Scanners\Rules\BladeRules;

class BladeRulesTest extends TestCase
{
    protected BladeRules $bladeRules;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bladeRules = new BladeRules();
    }

    /** @test */
    public function it_detects_xss_vulnerabilities()
    {
        $content = '
            <h1>{!! $userInput !!}</h1>
            <div>{!! $commentBody !!}</div>
        ';

        $issues = $this->bladeRules->analyze('test.blade.php', [], $content);

        $this->assertCount(2, $issues);
        $this->assertEquals('blade.xss_unescaped', $issues[0]['rule_id']);
        $this->assertEquals('security', $issues[0]['category']);
    }

    /** @test */
    public function it_allows_safe_unescaped_variables()
    {
        $content = '
            <div>{!! $htmlContent !!}</div>
            <div>{!! $markdownRendered !!}</div>
            <div>{!! $svgIcon !!}</div>
        ';

        $issues = $this->bladeRules->analyze('test.blade.php', [], $content);

        // Should not flag these as they are typically safe
        $xssIssues = array_filter($issues, fn($issue) => $issue['rule_id'] === 'blade.xss_unescaped');
        $this->assertEmpty($xssIssues);
    }

    /** @test */
    public function it_detects_missing_csrf_protection()
    {
        $content = '
            <form method="POST" action="/update">
                <input type="text" name="name">
                <button type="submit">Submit</button>
            </form>
        ';

        $issues = $this->bladeRules->analyze('test.blade.php', [], $content);

        $csrfIssues = array_filter($issues, fn($issue) => $issue['rule_id'] === 'blade.missing_csrf');
        $this->assertNotEmpty($csrfIssues);
    }

    /** @test */
    public function it_allows_forms_with_csrf_protection()
    {
        $content = '
            <form method="POST" action="/update">
                @csrf
                <input type="text" name="name">
                <button type="submit">Submit</button>
            </form>
        ';

        $issues = $this->bladeRules->analyze('test.blade.php', [], $content);

        $csrfIssues = array_filter($issues, fn($issue) => $issue['rule_id'] === 'blade.missing_csrf');
        $this->assertEmpty($csrfIssues);
    }

    /** @test */
    public function it_detects_potential_n1_queries()
    {
        $content = '
            @foreach($users as $user)
                <div>{{ $user->posts->count() }}</div>
            @endforeach
        ';

        $issues = $this->bladeRules->analyze('test.blade.php', [], $content);

        $n1Issues = array_filter($issues, fn($issue) => $issue['rule_id'] === 'blade.potential_n1_query');
        $this->assertNotEmpty($n1Issues);
    }

    /** @test */
    public function it_detects_inline_styles()
    {
        $content = '
            <div style="color: red; font-size: 18px;">Styled content</div>
            <style>
                .custom { color: blue; }
            </style>
        ';

        $issues = $this->bladeRules->analyze('test.blade.php', [], $content);

        $styleIssues = array_filter($issues, fn($issue) => 
            $issue['rule_id'] === 'blade.inline_styles' || $issue['rule_id'] === 'blade.style_tags'
        );
        $this->assertNotEmpty($styleIssues);
    }

    /** @test */
    public function it_detects_template_complexity()
    {
        // Create a highly complex template
        $content = '
            @if($condition1)
                @if($condition2)
                    @foreach($items as $item)
                        @if($item->active)
                            @foreach($item->children as $child)
                                @if($child->visible)
                                    @switch($child->type)
                                        @case("type1")
                                            Content 1
                                            @break
                                        @case("type2")
                                            Content 2
                                            @break
                                    @endswitch
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                @endif
            @endif
        ';

        $issues = $this->bladeRules->analyze('test.blade.php', [], $content);

        $complexityIssues = array_filter($issues, fn($issue) => 
            $issue['rule_id'] === 'blade.deep_nesting' || $issue['rule_id'] === 'blade.high_complexity'
        );
        $this->assertNotEmpty($complexityIssues);
    }

    /** @test */
    public function it_detects_php_in_templates()
    {
        $content = '
            @php
                $complexCalculation = 0;
                for($i = 0; $i < 100; $i++) {
                    $complexCalculation += $i;
                }
            @endphp
            
            <?php echo "Bad PHP tag"; ?>
        ';

        $issues = $this->bladeRules->analyze('test.blade.php', [], $content);

        $phpIssues = array_filter($issues, fn($issue) => 
            $issue['rule_id'] === 'blade.php_in_template' || $issue['rule_id'] === 'blade.php_tags'
        );
        $this->assertNotEmpty($phpIssues);
    }

    /** @test */
    public function it_detects_accessibility_issues()
    {
        $content = '
            <img src="/avatar.jpg">
            <input type="text" name="username">
        ';

        $issues = $this->bladeRules->analyze('test.blade.php', [], $content);

        $accessibilityIssues = array_filter($issues, fn($issue) => 
            $issue['rule_id'] === 'blade.missing_alt_text' || $issue['rule_id'] === 'blade.missing_form_label'
        );
        $this->assertNotEmpty($accessibilityIssues);
    }

    /** @test */
    public function it_detects_hardcoded_values()
    {
        $content = '
            <a href="https://external-service.com">External Link</a>
            <p>Contact us at: support@company.com</p>
        ';

        $issues = $this->bladeRules->analyze('test.blade.php', [], $content);

        $hardcodedIssues = array_filter($issues, fn($issue) => 
            $issue['rule_id'] === 'blade.hardcoded_url' || $issue['rule_id'] === 'blade.hardcoded_email'
        );
        $this->assertNotEmpty($hardcodedIssues);
    }

    /** @test */
    public function it_detects_superglobal_usage()
    {
        $content = '
            <p>{{ $_GET["search"] }}</p>
            <p>{{ $_SERVER["HTTP_USER_AGENT"] }}</p>
        ';

        $issues = $this->bladeRules->analyze('test.blade.php', [], $content);

        $superglobalIssues = array_filter($issues, fn($issue) => $issue['rule_id'] === 'blade.superglobal_usage');
        $this->assertNotEmpty($superglobalIssues);
    }

    /** @test */
    public function it_detects_deprecated_syntax()
    {
        $content = '
            {{{ $escapedContent }}}
            {? $questionSyntax ?}
        ';

        $issues = $this->bladeRules->analyze('test.blade.php', [], $content);

        $deprecatedIssues = array_filter($issues, fn($issue) => $issue['rule_id'] === 'blade.deprecated_syntax');
        $this->assertNotEmpty($deprecatedIssues);
    }

    /** @test */
    public function it_only_analyzes_blade_files()
    {
        $content = '{!! $userInput !!}'; // This would normally trigger XSS warning

        $issues = $this->bladeRules->analyze('test.php', [], $content); // Not a .blade.php file

        $this->assertEmpty($issues);
    }

    /** @test */
    public function it_provides_helpful_suggestions()
    {
        $content = '<form method="POST" action="/update"></form>';

        $issues = $this->bladeRules->analyze('test.blade.php', [], $content);

        $csrfIssue = current(array_filter($issues, fn($issue) => $issue['rule_id'] === 'blade.missing_csrf'));
        
        $this->assertNotEmpty($csrfIssue);
        $this->assertStringContains('Add @csrf directive', $csrfIssue['suggestion']);
    }
}
