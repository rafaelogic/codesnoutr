<?php

require 'vendor/autoload.php';

$rules = new Rafaelogic\CodeSnoutr\Scanners\Rules\QualityRules();
$content = file_get_contents('examples/magic-numbers-test.blade.php');
$issues = $rules->analyze('examples/magic-numbers-test.blade.php', [], $content);

$magicNumberIssues = array_filter($issues, function($issue) { 
    return isset($issue['rule_id']) && $issue['rule_id'] === 'quality.magic_number'; 
});

echo "Magic number issues found: " . count($magicNumberIssues) . PHP_EOL;
echo "Total issues found: " . count($issues) . PHP_EOL;

foreach ($magicNumberIssues as $issue) {
    echo "- " . ($issue['description'] ?? 'Magic number detected') . PHP_EOL;
    print_r($issue);
    echo PHP_EOL;
}
