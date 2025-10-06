# AI Prompt Refinement - Context-Aware Fix Generation

**Date:** October 6, 2025  
**Issue:** AI generating invalid fixes without understanding code context  
**Solution:** Enhanced prompt with context-awareness rules  
**Status:** ✅ DEPLOYED

---

## 🎯 The Real Problem

The AI was generating technically correct code (e.g., `public $timestamps = true;`) but **without understanding WHERE it was being placed**.

### Examples of Context Failures:

1. **Missing Timestamps Property** - Issue flagged on line 19 (class declaration)
   - AI Generated: `public $timestamps = true;`
   - Problem: Tried to insert property AT the class declaration line
   - Result: Syntax error "unexpected token 'public'"

2. **Array Magic Numbers** - Issue flagged inside array definition
   - AI Generated: `const PARKING_TYPE = 45;`
   - Problem: Tried to insert const declaration INSIDE the array
   - Result: Syntax error "unexpected token 'const', expecting ']'"

---

## ✅ The Solution: Context-Aware AI Prompt

### Added New Prompt Section:

```
CRITICAL CONTEXT-AWARE RULES:
1. BEFORE suggesting a fix, analyze the CODE CONTEXT above to understand WHERE you are:
   - If the flagged line is inside a class declaration (after 'class ClassName {'), you can suggest class members
   - If the flagged line is inside an array (between [ ]), DO NOT suggest class-level code
   - If the flagged line is inside a method body, suggest only valid statements for that context

2. FOR MISSING PROPERTIES (like $timestamps):
   - ONLY suggest adding properties if the flagged line is INSIDE a class body (after opening {)
   - NEVER suggest class properties if the context shows you're inside arrays, method calls, or array definitions
   - If unclear about the context, suggest 'type': 'skip' with explanation

3. CONTEXT DETECTION:
   - Look for array syntax: [ ... ] or array( ... ) around the flagged line
   - Look for class declaration: 'class ClassName extends/implements' 
   - If you see array syntax in context, DO NOT suggest const, public, protected, or private declarations
   - When in doubt, choose 'type': 'skip' and explain why the context is ambiguous

4. SAFE FIX TYPES BY CONTEXT:
   - Inside class body (but not in arrays): properties, methods, constants OK
   - Inside arrays: only array element modifications OK
   - Inside methods: only valid PHP statements OK
   - Inside function calls/parameters: only valid expressions OK
```

### Added 'skip' Type Support:

```json
{
  "type": "skip",
  "code": "",
  "explanation": "Cannot safely add property - context appears to be inside an array definition",
  "confidence": 0,
  "safe_to_automate": false
}
```

---

## 🔧 Code Changes

### 1. AiFixGenerator.php - Enhanced Prompt
- Added CRITICAL CONTEXT-AWARE RULES section
- Added guidance for detecting arrays, classes, methods
- Added 'skip' type for ambiguous contexts
- Instructed AI to analyze context BEFORE suggesting fixes

### 2. AutoFixService.php - Support 'skip' Type
- Added check for `type === 'skip'` after validation
- Logs when AI skips a fix with explanation
- Returns early with skip message
- Updated `validateAiFixData()` to accept 'skip' as valid

---

## 📊 Expected Behavior

### Before Refinement:
```
Issue: Missing Timestamps Property on line 19 (class declaration)
AI: "public $timestamps = true;"
System: Inserts at line 19
Result: ❌ Syntax error - property at class declaration
```

### After Refinement:
```
Issue: Missing Timestamps Property on line 19 (class declaration)
Context: Line shows "class SalePriceData extends Model"
AI Analysis: "This is a class declaration line, not inside class body"
AI Decision: { "type": "skip", "explanation": "..." }
System: ✋ AI skipped fix - context unclear
Result: ✅ No syntax error, clear explanation in logs
```

---

## 🎯 What This Fixes

1. **Context Awareness**: AI now analyzes WHERE the issue is before suggesting code
2. **Array Detection**: AI recognizes array syntax and avoids class-level code
3. **Ambiguity Handling**: When unsure, AI chooses 'skip' instead of guessing
4. **Clear Logging**: Skipped fixes include explanation for debugging
5. **Safety First**: Reduces failed fixes and syntax errors

---

## 📈 Expected Improvements

### Metrics:
- **Reduced Syntax Errors**: 80-90% reduction in "unexpected token" errors
- **Increased Skip Rate**: AI will skip 30-50% of ambiguous cases (this is GOOD!)
- **Better Success Rate**: Remaining fixes should have 60-80% success rate
- **Clearer Failures**: All failures will have explanations

### Example Results:
```
Before: 46 issues processed, 0 fixed, 46 syntax errors
After:  46 issues processed, 15 skipped (context unclear), 18 fixed, 13 failed (other reasons)
```

---

## 🔍 Monitoring

### Look for these log messages:

**Good Signs:**
```
✋ AI skipped fix due to unclear/unsafe context
Explanation: Cannot add property - context shows array definition
```

**Success:**
```
✅ AI fix applied successfully
Type: replace, Line: 25, Confidence: 0.85
```

**Legitimate Failures:**
```
❌ Modified content failed validation
Reason: Method implementation incomplete
```

---

## 🧪 Testing Guide

### 1. Check AI Decision Making
```bash
tail -f storage/logs/laravel.log | grep -E "AI skipped|type.*skip"
```

### 2. Check Success Rate
```bash
tail -f storage/logs/laravel.log | grep -E "fixed_count|failed_count"
```

### 3. Review Skip Reasons
```bash
grep "AI skipped" storage/logs/laravel.log | tail -10
```

---

## 💡 Key Insights

1. **Prevention > Detection**: Better to teach AI to avoid bad fixes than to detect them after
2. **Context is King**: Understanding WHERE you are is more important than WHAT to fix
3. **Skipping is Success**: A skipped fix prevents a syntax error - that's a win!
4. **Clear Communication**: Explanation in skip messages helps improve the system

---

## 🚀 Next Steps

1. **Test with Fix All** - Run on 10-20 issues
2. **Review Skip Messages** - Are they reasonable?
3. **Check Success Rate** - How many actually fixed?
4. **Analyze Patterns** - What contexts still fail?
5. **Iterate Prompt** - Refine based on results

---

## 📝 Files Modified

1. `src/Services/AI/AiFixGenerator.php` - Enhanced prompt with context rules
2. `src/Services/AI/AutoFixService.php` - Added 'skip' type support and validation

---

**Status:** ✅ Deployed to vendor and queue restarted  
**Ready:** ✅ Test with Fix All Issues  
**Expected:** Fewer syntax errors, more skips with explanations, some successful fixes

---

*The best fix is sometimes no fix - when the AI says "I'm not sure", that's intelligence.*
