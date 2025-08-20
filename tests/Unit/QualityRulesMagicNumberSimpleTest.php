<?php

use PHPUnit\Framework\TestCase;
use Rafaelogic\CodeSnoutr\Scanners\Rules\QualityRules;

class QualityRulesMagicNumberSimpleTest extends TestCase
{
    protected QualityRules $qualityRules;

    protected function setUp(): void
    {
        parent::setUp();
        $this->qualityRules = new QualityRules();
    }

    /** @test */
    public function it_does_not_flag_html_attributes_as_magic_numbers()
    {
        $content = '
            <img width="24" height="24" src="/icon.png">
            <input maxlength="255" size="30">
            <textarea rows="10" cols="50"></textarea>
            <td colspan="3" rowspan="2">Cell</td>
        ';

        $issues = $this->qualityRules->analyze('test.blade.php', [], $content);

        $magicNumberIssues = array_filter($issues, fn($issue) => $issue['rule_id'] === 'quality.magic_number');
        $this->assertEmpty($magicNumberIssues, 'HTML attributes should not be flagged as magic numbers');
    }

    /** @test */
    public function it_does_not_flag_css_properties_as_magic_numbers()
    {
        $content = '
            <div style="z-index: 9999; font-size: 16px; width: 100px;">Content</div>
            <div style="margin: 10px; padding: 20px; border-radius: 5px;">More content</div>
        ';

        $issues = $this->qualityRules->analyze('test.blade.php', [], $content);

        $magicNumberIssues = array_filter($issues, fn($issue) => $issue['rule_id'] === 'quality.magic_number');
        $this->assertEmpty($magicNumberIssues, 'CSS properties should not be flagged as magic numbers');
    }

    /** @test */
    public function it_does_not_flag_css_units_as_magic_numbers()
    {
        $content = '
            <div style="width: 24px; height: 16em; font-size: 1.5rem; margin: 10%;">Units</div>
        ';

        $issues = $this->qualityRules->analyze('test.blade.php', [], $content);

        $magicNumberIssues = array_filter($issues, fn($issue) => $issue['rule_id'] === 'quality.magic_number');
        $this->assertEmpty($magicNumberIssues, 'CSS units should not be flagged as magic numbers');
    }

    /** @test */
    public function it_does_not_flag_color_values_as_magic_numbers()
    {
        $content = '
            <div style="color: #ff0000; background: rgb(255, 255, 255);">Colors</div>
        ';

        $issues = $this->qualityRules->analyze('test.blade.php', [], $content);

        $magicNumberIssues = array_filter($issues, fn($issue) => $issue['rule_id'] === 'quality.magic_number');
        $this->assertEmpty($magicNumberIssues, 'Color values should not be flagged as magic numbers');
    }

    /** @test */
    public function it_still_flags_php_magic_numbers_in_blade_files()
    {
        $content = '
            @php
                $magicNumber = 42;
                $threshold = 12345;
            @endphp
        ';

        $issues = $this->qualityRules->analyze('test.blade.php', [], $content);

        $magicNumberIssues = array_filter($issues, fn($issue) => $issue['rule_id'] === 'quality.magic_number');
        $this->assertNotEmpty($magicNumberIssues, 'PHP magic numbers in @php blocks should still be flagged');
    }

    /** @test */
    public function it_flags_magic_numbers_in_regular_php_files()
    {
        $content = '
            <?php
            $magicNumber = 42;
            $threshold = 12345;
            if ($value > 999) {
                return true;
            }
        ';

        $issues = $this->qualityRules->analyze('test.php', [], $content);

        $magicNumberIssues = array_filter($issues, fn($issue) => $issue['rule_id'] === 'quality.magic_number');
        $this->assertNotEmpty($magicNumberIssues, 'Magic numbers in PHP files should be flagged');
    }
}
