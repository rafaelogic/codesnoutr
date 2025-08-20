# AI Features Implementation Documentation

## Overview
CodeSnoutr AI Features provide intelligent code analysis and automated fix suggestions to enhance code quality and development productivity.

## Architecture

### 1. AI Scanning Workflow

#### Scope-Based Analysis
- **File Scan**: Regular Rules + AI Analysis (always enabled)
- **Directory Scan**: Regular Rules + AI Analysis (optional, user choice)
- **Codebase Scan**: Regular Rules + AI Analysis (optional, user choice)

#### Processing Flow
```
User Initiates Scan
    ↓
Queue Background Job
    ↓
Regular Rule Scanning
    ↓
AI Analysis (if enabled)
    ↓
Safety Classification
    ↓
Store Results + Suggestions
    ↓
Notify User (Real-time updates)
```

### 2. Safety Classification System

#### Categories
- **SAFE** (Auto-apply eligible)
  - Code style fixes
  - Import optimizations
  - Simple refactoring
  - Documentation updates
  
- **RISKY** (Suggestion only)
  - Logic changes
  - Algorithm modifications
  - Data flow alterations
  - Breaking changes

#### Risk Assessment Criteria
```php
Risk Factors:
├── Complexity Score (1-10)
├── Impact Scope (file/module/system)
├── Change Type (style/logic/structure)
└── Dependencies Affected
```

### 3. User Configuration

#### Per-Rule Type Settings
```php
Configuration Options:
├── AUTO: Apply safe fixes automatically
├── SUGGEST: Show all suggestions for review
└── SKIP: Disable AI analysis for this rule type

Supported Rule Types:
├── MagicNumberRule: [Auto|Suggest|Skip]
├── BladeRule: [Auto|Suggest|Skip]
├── SecurityRule: [Suggest|Skip] // Never auto
├── PerformanceRule: [Auto|Suggest|Skip]
└── CustomRules: [Auto|Suggest|Skip]
```

#### Global AI Settings
- Enable/Disable AI features
- Default behavior for new rule types
- Auto-apply confidence threshold
- Maximum fixes per scan
- Backup strategy for applied fixes

## Database Schema

### New Tables

#### `ai_scan_results`
```sql
CREATE TABLE ai_scan_results (
    id BIGINT PRIMARY KEY,
    scan_id BIGINT REFERENCES codesnoutr_scans(id),
    issue_id BIGINT REFERENCES codesnoutr_issues(id),
    ai_analysis TEXT,
    confidence_score DECIMAL(3,2),
    safety_classification ENUM('safe', 'risky', 'unknown'),
    processing_time INTEGER,
    ai_model_version VARCHAR(50),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

#### `ai_fix_suggestions`
```sql
CREATE TABLE ai_fix_suggestions (
    id BIGINT PRIMARY KEY,
    ai_scan_result_id BIGINT REFERENCES ai_scan_results(id),
    fix_type VARCHAR(100),
    original_code TEXT,
    suggested_code TEXT,
    explanation TEXT,
    risk_score DECIMAL(3,2),
    auto_applicable BOOLEAN,
    status ENUM('pending', 'applied', 'rejected', 'failed'),
    applied_at TIMESTAMP NULL,
    applied_by VARCHAR(255) NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

#### `ai_settings`
```sql
CREATE TABLE ai_settings (
    id BIGINT PRIMARY KEY,
    user_id VARCHAR(255) NULL, -- For multi-user setups
    rule_type VARCHAR(100),
    behavior ENUM('auto', 'suggest', 'skip'),
    confidence_threshold DECIMAL(3,2),
    auto_apply_enabled BOOLEAN DEFAULT false,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE KEY unique_user_rule (user_id, rule_type)
);
```

#### `ai_fix_history`
```sql
CREATE TABLE ai_fix_history (
    id BIGINT PRIMARY KEY,
    fix_suggestion_id BIGINT REFERENCES ai_fix_suggestions(id),
    action ENUM('applied', 'reverted', 'modified'),
    backup_content TEXT,
    action_metadata JSON,
    performed_by VARCHAR(255),
    performed_at TIMESTAMP
);
```

## Core Services

### 1. AiAnalysisService
```php
class AiAnalysisService
{
    // Analyze code issues using AI
    public function analyzeIssue(Issue $issue): AiScanResult
    
    // Generate fix suggestions
    public function generateFixSuggestions(AiScanResult $result): Collection
    
    // Classify fix safety
    public function classifyFixSafety(AiFixSuggestion $suggestion): string
    
    // Apply fix to file
    public function applyFix(AiFixSuggestion $suggestion): bool
}
```

### 2. AiConfigService
```php
class AiConfigService
{
    // Get user AI preferences
    public function getUserSettings(?string $userId = null): Collection
    
    // Update rule behavior
    public function updateRuleBehavior(string $ruleType, string $behavior): void
    
    // Check if AI should run for scan type
    public function shouldRunAiAnalysis(string $scanType, array $options): bool
}
```

### 3. AiSafetyClassifier
```php
class AiSafetyClassifier
{
    // Assess fix risk level
    public function assessRisk(AiFixSuggestion $suggestion): float
    
    // Determine if fix can be auto-applied
    public function isAutoApplicable(AiFixSuggestion $suggestion): bool
    
    // Get risk factors
    public function getRiskFactors(AiFixSuggestion $suggestion): array
}
```

## Background Jobs

### 1. ProcessAiAnalysisJob
```php
// Processes AI analysis for scan results
class ProcessAiAnalysisJob implements ShouldQueue
{
    public function handle(Scan $scan, array $options = []): void
    {
        // 1. Get scan issues
        // 2. Run AI analysis on each issue
        // 3. Generate fix suggestions
        // 4. Classify safety
        // 5. Store results
        // 6. Notify user if needed
    }
}
```

### 2. ApplyAiFixJob
```php
// Applies AI fixes in background
class ApplyAiFixJob implements ShouldQueue
{
    public function handle(AiFixSuggestion $suggestion): void
    {
        // 1. Create backup
        // 2. Apply fix
        // 3. Verify result
        // 4. Update status
        // 5. Log history
    }
}
```

## API Integration

### AI Provider Interface
```php
interface AiProviderInterface
{
    public function analyzeCode(string $code, string $language, array $context): array;
    public function generateFix(string $issue, string $code, array $context): string;
    public function explainFix(string $originalCode, string $fixedCode): string;
}
```

### Supported Providers
- OpenAI GPT-4
- Claude
- Local LLM integration
- Custom AI endpoints

## User Interface Components

### 1. AI Scan Controls
- Enable/Disable AI analysis
- Choose AI mode (Auto/Suggest)
- Configure per-rule behaviors

### 2. AI Results Display
- AI suggestions with confidence scores
- Fix previews with diff view
- Safety indicators
- Apply/Reject controls

### 3. AI Settings Panel
- Global AI preferences
- Rule-specific configurations
- Performance monitoring
- History and audit trail

## Implementation Phases

### Phase 1: Foundation (Week 1-2)
- [ ] Database migrations
- [ ] Core service interfaces
- [ ] Basic AI provider integration
- [ ] Configuration system

### Phase 2: Analysis Engine (Week 3-4)
- [ ] AI analysis service
- [ ] Safety classification
- [ ] Fix generation
- [ ] Background job processing

### Phase 3: User Interface (Week 5-6)
- [ ] Atomic design implementation
- [ ] AI settings components
- [ ] Results display with AI features
- [ ] Fix preview and application UI

### Phase 4: Integration & Testing (Week 7-8)
- [ ] Full workflow integration
- [ ] Performance optimization
- [ ] Security hardening
- [ ] Comprehensive testing

## Security Considerations

### Code Safety
- Sandbox fix application
- Backup before changes
- Rollback mechanism
- Permission checks

### AI Security
- Input sanitization
- Output validation
- Rate limiting
- Cost monitoring

### Data Privacy
- Code anonymization options
- Local processing preference
- Audit trail maintenance
- Compliance features

## Performance Metrics

### Analysis Metrics
- Processing time per issue
- Accuracy of suggestions
- User acceptance rate
- False positive rate

### System Metrics
- Queue processing time
- Memory usage during AI calls
- API response times
- Background job success rate

## Configuration Examples

### Basic Setup
```php
// config/codesnoutr.php
'ai' => [
    'enabled' => true,
    'provider' => 'openai',
    'model' => 'gpt-4',
    'max_suggestions_per_issue' => 3,
    'confidence_threshold' => 0.7,
    'auto_apply_threshold' => 0.9,
]
```

### Rule-Specific Configuration
```php
'ai_rules' => [
    'magic_numbers' => 'auto',
    'blade_syntax' => 'suggest',
    'security_issues' => 'suggest',
    'performance' => 'auto',
]
```

This documentation provides the foundation for implementing AI features while maintaining code quality and user safety.
