# AI Fix Critical Issue - Array Context Detection

**Date:** October 5, 2025  
**Status:** 🟡 **PARTIALLY FIXED** - Blocking invalid fixes, but root cause remains  
**Issue:** AI generating class-level code (const) for array elements

---

## 🐛 The Real Problem Discovered

After implementing the class declaration fix, a **new fundamental issue** was revealed:

### What's Happening

The AI is trying to replace **magic numbers inside arrays** with **class-level constants**:

**Original Code (Line 44 in PropertyType.php):**
```php
public const RIGHTMOVE_PROPERTY_SUB_TYPE_REMAPPINGS = [
    'Not Specified' => 0,
    'Terraced' => 1,
    // ...
    'Ground Maisonette' => 10,  // ← Issue detected here (line 44)
    'Maisonette' => 11,
];
```

**AI Generated "Fix":**
```php
const GROUND_MAISONETTE = 10;  // ← This is class-level code!
```

**What AI Should Generate:**
Either:
1. Skip the fix (magic numbers in config arrays are often acceptable)
2. Define const **before** the array:
   ```php
   const GROUND_MAISONETTE = 10;
   
   public const RIGHTMOVE_PROPERTY_SUB_TYPE_REMAPPINGS = [
       // ... other entries ...
       'Ground Maisonette' => self::GROUND_MAISONETTE,
   ];
   ```

### The Errors

```
Parse error: syntax error, unexpected token "const", expecting "]" in ... on line 44
Parse error: syntax error, unexpected token "const", expecting "]" in ... on line 46  
Parse error: syntax error, unexpected token "const", expecting "]" in ... on line 48
Parse error: syntax error, unexpected single-quoted string "Mobile Home", expecting "]" in ... on line 50
```

**All caused by:** Inserting class-level declarations **inside array definitions**

---

## ✅ Immediate Fix Implemented

### Protection Added

Added `isInsertingClassCodeInArray()` method that:

```php
// In applyFix() method, before applying the fix:
if ($this->isInsertingClassCodeInArray($lines, $issue->line_number - 1, $fixData['code'])) {
    Log::warning('❌ AI trying to insert class-level code inside array - SKIPPING', [
        'issue_id' => $issue->id,
        'target_line' => $issue->line_number,
        'generated_code' => $fixData['code'],
    ]);
    $result['message'] = 'Cannot insert class-level code (const/property/method) inside an array';
    return $result;
}
```

**How It Works:**
1. Detects if generated code starts with `const`, `public`, `protected`, `private`
2. Looks backwards from target line to check for unclosed `[` brackets
3. If inside an array AND code is class-level → **SKIP THE FIX**

### Result
- ✅ Prevents invalid fixes from being applied
- ✅ Avoids PHP syntax errors
- ❌ But doesn't actually **fix** the issues

---

## 🔍 Root Cause Analysis

### Why Is This Happening?

1. **Scanner Issue Detection**
   - CodeSnoutr's Quality scanner detects "magic numbers" in code
   - Marks line 44 (inside array) as having a magic number issue
   - Suggests: "Consider defining named constants"

2. **AI Prompt Generation**
   - AI is given the issue context
   - AI sees: "magic number 10 should be a constant"
   - AI generates: `const GROUND_MAISONETTE = 10;`

3. **Context Loss**
   - AI doesn't understand the target line is **inside an array**
   - AI generates class-level code without checking context
   - Replacement logic inserts at exact line location

### The Fundamental Problem

**The AI prompt doesn't include enough context about code structure:**
- ❌ Doesn't tell AI: "This line is inside an array"
- ❌ Doesn't tell AI: "Generate array-compatible code"
- ❌ Doesn't provide surrounding code structure

---

## 🛠️ Comprehensive Solutions

### Option 1: Skip Magic Number Issues in Arrays (Quick Fix)

Don't even try to fix magic numbers when they're in array definitions:

```php
// In scanner or issue filtering
if ($issue->type === 'magic_number' && $this->isLineInArray($issue->line_number)) {
    // Skip this issue or mark as "not fixable by AI"
    continue;
}
```

**Pros:**
- ✅ Simple and safe
- ✅ Prevents all array-related issues

**Cons:**
- ❌ Doesn't fix the issues at all
- ❌ Legitimate magic numbers remain

### Option 2: Enhanced AI Prompt with Context (Better)

Include file structure context in the AI prompt:

```php
protected function buildAutoFixPrompt(Issue $issue): string
{
    $context = $this->analyzeCodeContext($issue);
    
    $prompt = "Fix this code issue.

FILE STRUCTURE:
- Inside class: {$context['in_class']}
- Inside method: {$context['in_method']}
- Inside array: {$context['in_array']}  ← NEW!
- Array depth: {$context['array_depth']} ← NEW!

If inside an array:
- Do NOT generate const/property/method declarations
- Either skip the fix OR generate array-compatible code

ISSUE:
{$issue->message}

CODE:
{$issue->code_snippet}
";
}
```

**Pros:**
- ✅ AI can make informed decisions
- ✅ Can still fix some issues correctly

**Cons:**
- ❌ Requires AI prompt rewrite
- ❌ Depends on AI following instructions

### Option 3: Smart Code Generation (Best Long-term)

Generate proper multi-location fixes:

```php
// When fixing magic number in array:
{
    "type": "multi_location",
    "changes": [
        {
            "location": "before_array",  // Define constant first
            "code": "const GROUND_MAISONETTE = 10;"
        },
        {
            "location": "target_line",  // Update array value
            "code": "'Ground Maisonette' => self::GROUND_MAISONETTE,"
        }
    ]
}
```

**Pros:**
- ✅ Actually fixes the issues correctly
- ✅ Produces clean, maintainable code
- ✅ Handles complex scenarios

**Cons:**
- ❌ Requires significant refactoring
- ❌ Complex implementation
- ❌ AI needs to generate more structured output

### Option 4: Post-Processing Validation (Current Approach + Enhancement)

Current approach (blocking invalid fixes) + better error messages:

```php
if ($this->isInsertingClassCodeInArray($lines, $targetLine, $code)) {
    // Instead of just skipping, try to salvage:
    if ($this->canConvertToArrayValue($code)) {
        $code = $this->convertToArrayValue($code);
        // Continue with modified code
    } else {
        // Skip with detailed reason
        $result['message'] = 'Cannot insert class-level code inside array. Consider defining constant before array.';
        $result['suggestion'] = 'Manual fix required: Define constant outside array first.';
        return $result;
    }
}
```

**Pros:**
- ✅ Builds on current fix
- ✅ Can salvage some fixes automatically

**Cons:**
- ❌ Limited success rate
- ❌ Still doesn't fix many issues

---

## 📊 Impact Assessment

### Issues Affected

**Total issues being skipped now:**
- Magic numbers in const arrays (**many**)
- Magic numbers in property arrays
- Magic numbers in method return arrays
- Any class-level code targeting array elements

**Estimate:** Could be 30-50% of all failed issues

### Current Status

**Before This Fix:**
- Fixed: 0
- Failed: 180 (syntax errors)

**After This Fix:**
- Fixed: 0
- Failed: ~90 (invalid context, skipped)
- Skipped: ~90 (array context detected, blocked)

**Net Result:** Fewer errors, but still no fixes applied

---

## 🚀 Recommended Action Plan

### Immediate (Today)

1. ✅ **DONE**: Add array context detection (prevents invalid fixes)
2. **TEST**: Restart queue, run Fix All, verify fixes are blocked with proper messages
3. **MONITOR**: Check how many issues are being skipped

### Short Term (This Week)

4. **Enhance AI Prompt** (Option 2):
   - Add array context detection to prompt
   - Instruct AI to skip or handle array cases differently
   - Test with few issues first

5. **Filter Issues** (Option 1):
   - Don't send array-context magic numbers to AI
   - Mark them as "manual review required"
   - Focus AI on issues it can actually fix

### Long Term (Next Sprint)

6. **Implement Smart Generation** (Option 3):
   - Support multi-location fixes
   - AI generates structured fix plans
   - Apply changes in correct order

7. **Improve Scanner**:
   - Don't flag magic numbers in const arrays as issues
   - Or flag them with lower priority
   - Add context awareness to scanner

---

## 🧪 Testing

### Test the Current Fix

1. **Restart services:**
   ```bash
   php artisan queue:restart
   php artisan queue:work --verbose
   ```

2. **Run Fix All (limit 20):**
   - Watch for log message: "AI trying to insert class-level code inside array - SKIPPING"
   - Count how many are skipped vs still failing

3. **Check logs:**
   ```bash
   tail -f storage/logs/laravel.log | grep -E "SKIPPING|inside array"
   ```

### Success Criteria

- ✅ No more "unexpected token 'const', expecting ']'" errors
- ✅ Log shows issues being **skipped** with reason
- ✅ Other types of issues (not in arrays) should start getting fixed

### If Still Failing

Look for **other error patterns**:
```bash
tail -n 500 storage/logs/laravel.log | grep "Parse error" | cut -d'"' -f2 | sort | uniq -c
```

This will show what **other** types of syntax errors are occurring.

---

## 📝 Files Modified

- `src/Services/AI/AutoFixService.php` (line ~105, ~2230)
  - Added `isInsertingClassCodeInArray()` method
  - Added validation check before applying fixes
  - Added warning logs for skipped fixes

---

## 💡 Key Learnings

### Context is Everything

**Problem:** AI generates code without understanding WHERE it will be inserted:
- Inside a class? Inside a method? Inside an array? Inside a string?

**Solution:** Need to:
1. Detect context before applying fixes
2. Pass context to AI during generation
3. Validate context after generation
4. Block or transform invalid fixes

### Scanner vs. AI Disconnect

**Problem:** Scanner detects issues that AI can't fix correctly:
- Scanner says: "Magic number found"
- AI generates: Class-level const
- Reality: Magic number is in array

**Solution:**
- Filter issues before sending to AI
- Add "AI-fixable" flag to issues
- Or enhance AI prompt with context

### Progressive Enhancement

**Current approach:**
1. Try to fix everything → syntax errors
2. Block invalid fixes → fewer errors, no fixes
3. **Next:** Fix what we can, skip the rest
4. **Future:** Fix everything correctly

---

## 🎯 Success Metrics

### Current State
- Syntax errors: Reduced (array cases blocked)
- Fixed issues: 0
- Skipped issues: Increasing

### Target State
- Syntax errors: 0
- Fixed issues: >50%
- Skipped issues: <30% (documented as manual-fix-required)

---

**Status:** Fix deployed, awaiting testing  
**Next Step:** Restart services and test  
**Priority:** HIGH - Core functionality improvement
