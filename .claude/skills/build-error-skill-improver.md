---
name: build-error-skill-improver
description: Automatically improves programming skills based on build errors and their solutions
version: 1.0.0
tags: [meta, automation, skills, error-handling, continuous-improvement]
related_skills: [skill-improver, skill-router, git-github-expertise, testing-expert]
trigger_keywords: [build error, compilation error, build failed, improve skill, error log, fix build]
---

# Build Error Skill Improver

Automatically captures build errors and uses them to enhance related programming skills with new troubleshooting entries, solutions, and error patterns.

## When to Use

1. After a build fails and you've identified the solution
2. When you notice recurring error patterns that should be documented
3. After debugging a tricky issue that others might encounter
4. When CI/CD pipelines fail with informative errors
5. To continuously improve skills based on real-world problems

## How It Works

### Error-to-Skill Mapping

The system maps build errors to skills using keyword patterns:

```yaml
# Error Type → Skill Mapping
flutter_dart:
  keywords: [flutter, dart, widget, setState, BuildContext, pubspec, gradle]
  skills: [flutter-dart-expert, flutter-app-design]

laravel_php:
  keywords: [laravel, php, artisan, eloquent, migration, composer, blade]
  skills: [laravel-ecosystem, laravel-middleware, laravel-test-suite]

javascript_vue:
  keywords: [vue, vite, npm, node, javascript, typescript, inertia, pinia]
  skills: [javascript-vuejs-expert, css-tailwind-expert]

database:
  keywords: [mysql, sql, query, index, migration, deadlock, foreign key]
  skills: [database-mysql-expert, database-migration-check]

security:
  keywords: [authentication, authorization, csrf, xss, injection, token]
  skills: [security-expert, laravel-middleware]

testing:
  keywords: [test, phpunit, pest, vitest, mock, assertion, coverage]
  skills: [testing-expert, laravel-test-suite]

deployment:
  keywords: [deploy, ci, cd, github actions, workflow, docker, server]
  skills: [deployment-checklist, git-github-expertise]
```

## Build Error Log Format

When capturing build errors, store them in this format:

```json
{
  "timestamp": "2025-12-17T10:30:00Z",
  "command": "flutter build apk",
  "exit_code": 1,
  "error_type": "flutter_dart",
  "error_summary": "Null check operator used on a null value",
  "error_details": "The following _TypeError was thrown...",
  "file_path": "lib/screens/dashboard_screen.dart",
  "line_number": 245,
  "solution_applied": "Added null check before accessing property",
  "solution_code": "final value = widget.data?.value ?? defaultValue;"
}
```

## Skill Enhancement Process

### Step 1: Analyze the Error

Extract key information from the build error:

```bash
# Example: Parse error from build log
ERROR_FILE=$(grep -oP '(?<=Error in )[\w/._-]+\.dart' build.log | head -1)
ERROR_LINE=$(grep -oP '(?<=line )\d+' build.log | head -1)
ERROR_MESSAGE=$(grep -A 3 "Error:" build.log | head -4)
```

### Step 2: Identify Target Skill

Match error keywords to determine which skill to enhance:

```python
def identify_skill(error_text):
    """Map error text to relevant skill."""
    error_lower = error_text.lower()

    skill_mappings = {
        'flutter-dart-expert': ['flutter', 'dart', 'widget', 'buildcontext', 'setstate'],
        'laravel-ecosystem': ['laravel', 'eloquent', 'artisan', 'blade', 'migration'],
        'javascript-vuejs-expert': ['vue', 'vite', 'npm', 'javascript', 'inertia'],
        'database-mysql-expert': ['mysql', 'sql', 'query', 'index', 'deadlock'],
        'security-expert': ['auth', 'csrf', 'xss', 'injection', 'token'],
        'testing-expert': ['test', 'phpunit', 'pest', 'mock', 'assert'],
    }

    scores = {}
    for skill, keywords in skill_mappings.items():
        scores[skill] = sum(1 for kw in keywords if kw in error_lower)

    return max(scores, key=scores.get) if max(scores.values()) > 0 else None
```

### Step 3: Generate Troubleshooting Entry

Create a new troubleshooting section for the skill:

```markdown
### [ERROR_TITLE]

**Error Message:**
```
[ACTUAL_ERROR_MESSAGE]
```

**Cause:**
[EXPLANATION_OF_WHY_THIS_HAPPENS]

**Solution:**
[STEP_BY_STEP_FIX]

**Code Example:**
```[language]
// Before (problematic)
[PROBLEMATIC_CODE]

// After (fixed)
[FIXED_CODE]
```

**Prevention:**
- [HOW_TO_PREVENT_THIS_IN_FUTURE]
```

### Step 4: Update the Skill File

Insert the new troubleshooting entry into the skill's Troubleshooting section:

```bash
# Find the Troubleshooting section and append
SKILL_FILE=".claude/skills/${TARGET_SKILL}.md"
TROUBLESHOOTING_LINE=$(grep -n "## Troubleshooting" "$SKILL_FILE" | cut -d: -f1)

# Insert new entry after Troubleshooting header
sed -i "${TROUBLESHOOTING_LINE}a\\
\\
### ${ERROR_TITLE}\\
\\
**Error Message:**\\
\`\`\`\\
${ERROR_MESSAGE}\\
\`\`\`\\
\\
**Solution:** ${SOLUTION}\\
" "$SKILL_FILE"
```

## Integration with Git Skill

After enhancing a skill, use the git-github-expertise skill to commit the changes:

```bash
# Stage and commit the skill enhancement
git add ".claude/skills/${TARGET_SKILL}.md"
git commit -m "docs(skills): Add troubleshooting for ${ERROR_TYPE}

- Added solution for: ${ERROR_SUMMARY}
- Enhanced ${TARGET_SKILL} skill
- Source: Build error on $(date +%Y-%m-%d)"
```

## Automated Capture via Hook

### Build Error Capture Script

Create a script that captures build errors:

```bash
#!/bin/bash
# ~/.claude/hooks/capture-build-error.sh

BUILD_CMD="$1"
shift
BUILD_ARGS="$@"

# Create error log directory
mkdir -p ~/.claude/build-errors

# Execute build command and capture output
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
ERROR_LOG=~/.claude/build-errors/error_${TIMESTAMP}.log

$BUILD_CMD $BUILD_ARGS 2>&1 | tee "$ERROR_LOG"
EXIT_CODE=${PIPESTATUS[0]}

if [ $EXIT_CODE -ne 0 ]; then
    echo "BUILD_FAILED" >> "$ERROR_LOG"
    echo "EXIT_CODE=$EXIT_CODE" >> "$ERROR_LOG"
    echo "COMMAND=$BUILD_CMD $BUILD_ARGS" >> "$ERROR_LOG"
    echo "---"
    echo "Build failed! Error log saved to: $ERROR_LOG"
    echo "Use '/improve-from-errors' command to enhance related skill."
fi

exit $EXIT_CODE
```

## Usage Examples

### Example 1: Flutter Build Error

**Error Captured:**
```
Error: The argument type 'String?' can't be assigned to the parameter type 'String'.
lib/screens/profile_screen.dart:45:23
```

**Skill Enhanced:** `flutter-dart-expert.md`

**Entry Added:**
```markdown
### Null Safety Type Mismatch

**Error Message:**
```
The argument type 'String?' can't be assigned to the parameter type 'String'.
```

**Cause:**
Attempting to pass a nullable String (String?) where a non-nullable String is expected.

**Solution:**
1. Use null assertion if you're certain the value won't be null: `value!`
2. Provide a default value: `value ?? 'default'`
3. Update the parameter to accept nullable: `String?`

**Code Example:**
```dart
// Before (error)
Text(user.name) // user.name is String?

// After (fixed - with default)
Text(user.name ?? 'Unknown User')

// After (fixed - with null check)
if (user.name != null) Text(user.name!)
```
```

### Example 2: Laravel Migration Error

**Error Captured:**
```
SQLSTATE[42S21]: Column already exists: 1060 Duplicate column name 'status'
```

**Skill Enhanced:** `database-mysql-expert.md`

**Entry Added:**
```markdown
### Duplicate Column in Migration

**Error Message:**
```
SQLSTATE[42S21]: Column already exists: 1060 Duplicate column name 'status'
```

**Cause:**
Running a migration that tries to add a column that already exists, often from:
- Running migrations multiple times
- Missing rollback before re-running
- Conflicting migrations from different branches

**Solution:**
1. Check if column exists before adding:
```php
if (!Schema::hasColumn('table_name', 'status')) {
    $table->string('status')->default('pending');
}
```

2. Or rollback and migrate fresh in development:
```bash
php artisan migrate:rollback
php artisan migrate
```
```

## Best Practices

### DO:
- ✅ Capture the complete error message including stack trace
- ✅ Document the root cause, not just the symptom
- ✅ Include both the problematic and fixed code
- ✅ Add prevention tips to avoid future occurrences
- ✅ Commit skill enhancements with descriptive messages
- ✅ Cross-reference related troubleshooting entries

### DON'T:
- ❌ Add duplicate entries for the same error pattern
- ❌ Include sensitive data (passwords, API keys) in examples
- ❌ Skip the root cause explanation
- ❌ Forget to test the solution before documenting
- ❌ Make entries too specific to one project

## Troubleshooting the Improver

### Error: Cannot identify target skill

**Cause:** Error keywords don't match any skill mapping.

**Solution:**
1. Add new keywords to the mapping
2. Create a new skill if the domain isn't covered
3. Manually specify the target skill

### Error: Duplicate entry detected

**Cause:** Similar error already documented.

**Solution:**
1. Enhance the existing entry instead
2. Add alternative solutions
3. Link to the existing entry

### Error: Skill file not found

**Cause:** Target skill doesn't exist yet.

**Solution:**
1. Create the skill using skill-improver framework
2. Use a related existing skill
3. Add entry to a general skill like `testing-expert`

## Metrics & Tracking

Track skill improvements over time:

```json
{
  "improvements_log": "~/.claude/build-errors/improvements.json",
  "metrics": {
    "total_errors_captured": 0,
    "skills_enhanced": {},
    "most_common_errors": [],
    "last_improvement": null
  }
}
```

## Related Skills

- **skill-improver**: Framework for enhancing skill quality
- **skill-router**: Auto-routing to find relevant skills
- **git-github-expertise**: For committing skill enhancements
- **testing-expert**: For test-related build errors
- **deployment-checklist**: For CI/CD build failures

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | 2025-12-17 | Initial release with error mapping and enhancement process |
