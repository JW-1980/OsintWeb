---
name: skill-improver
description: Framework for creating, evaluating, and enhancing Claude Code skills with best practices and quality standards
version: 2.0.1
tags: [meta, quality, documentation, best-practices, framework, restructuring]
trigger_keywords: [sk-skill-improver, skill improvement, improve skill, enhance skill, skill quality, skill best practices, skill framework, skill evaluation]
---

# Skill Improver

This meta-skill provides comprehensive guidance for creating, evaluating, and enhancing Claude Code skills. It establishes best practices, quality standards, and systematic frameworks for skill development.

## When to Use

- Creating new skills from scratch
- Enhancing existing skills
- Auditing skill quality and completeness
- Standardizing skill documentation
- Training team members on skill creation
- Establishing skill governance processes

## Skill Quality Framework

### The 20 Pillars of Excellent Skills

A high-quality skill must include these essential components:

#### Foundation Pillars (1-10)

1. **Clear Purpose** - Well-defined scope and use cases
2. **Practical Examples** - Real, working code examples with context
3. **Troubleshooting** - Common problems and their solutions
4. **Integration Guidance** - How to use with other skills/systems
5. **Verification Checklists** - Step-by-step validation lists
6. **Best Practices** - Industry standards and patterns
7. **Anti-Patterns** - Common mistakes to avoid
8. **Performance Tips** - Optimization recommendations
9. **Security Considerations** - Security-related guidance
10. **Testing Guidance** - How to test related functionality

#### Advanced Pillars (11-20)

11. **Structured Hierarchy** - Logical grouping with clear headings and subheadings
12. **Progressive Disclosure** - Simple concepts first, complexity builds gradually
13. **Cross-References** - Links to related skills, docs, and external resources
14. **Decision Frameworks** - When to use what, with decision trees and flowcharts
15. **Real-World Scenarios** - Case studies from actual project implementations
16. **Version Awareness** - Framework/library version-specific guidance
17. **Dutch Localization** - Netherlands-specific compliance, terminology, and practices
18. **Offline Capability** - Guidance that works without external dependencies
19. **Maintenance Triggers** - Clear indicators of when content needs updating
20. **Measurable Outcomes** - Success criteria and verification methods

### Pillar Descriptions (Advanced)

#### 11. Structured Hierarchy

Skills must have logical organization with clear navigation:

```markdown
## Main Category
Brief introduction to category

### Subcategory 1
Detailed explanation

#### Specific Topic 1.1
- Point with explanation
- Another point with context

#### Specific Topic 1.2
[More detail]

### Subcategory 2
[Pattern continues]
```

**Why it matters:** Users scan skills quickly. Good structure allows finding information in seconds.

#### 12. Progressive Disclosure

Start simple, add complexity gradually:

```markdown
## Quick Start (30 seconds)
Minimal working example

## Basic Usage (5 minutes)
Core concepts with simple examples

## Intermediate Topics (15 minutes)
Configuration, customization, common patterns

## Advanced Usage (30+ minutes)
Edge cases, optimization, complex integrations
```

**Why it matters:** Different users need different depths. Beginners shouldn't wade through advanced content.

#### 13. Cross-References

Link related content explicitly:

```markdown
**Related Skills:**
- `laravel-middleware` - For authentication integration
- `dutch-tax-compliance` - For BTW calculations
- `testing-expert` - For testing patterns

**See Also:**
- [API Documentation](../API.md)
- [Security Guidelines](../SECURITY.md)

**Prerequisites:**
- Complete `laravel-ecosystem` first
- Familiarity with `database-mysql-expert`
```

**Why it matters:** Skills don't exist in isolation. Users need to know what else to learn.

#### 14. Decision Frameworks

Help users choose the right approach:

```markdown
### Choosing the Right Approach

**Use Approach A when:**
- Condition 1 applies
- Performance is critical
- You need feature X

**Use Approach B when:**
- Condition 2 applies
- Simplicity is more important
- You don't need feature X

**Decision Tree:**
```
Start
  │
  ├─ Need real-time? ─→ Yes ─→ Use WebSockets
  │                    │
  │                    └─→ No ─→ Need offline? ─→ Yes ─→ Use Queue
  │                                              │
  │                                              └─→ No ─→ Use HTTP
```
```

**Why it matters:** Many skills offer multiple approaches. Users need guidance on selection.

#### 15. Real-World Scenarios

Include actual project examples:

```markdown
### Case Study: Invoice Processing Pipeline

**Context:** Boekhouder processes 10,000+ invoices/month

**Challenge:** OCR extraction was taking 3+ seconds per document

**Solution Applied:**
1. Implemented batch processing (reduced overhead)
2. Added caching layer (avoided re-processing)
3. Used async queue workers (parallelized)

**Results:**
- Processing time: 3.2s → 0.4s (87% improvement)
- Throughput: 300/hour → 2,500/hour

**Code:**
```php
// Actual implementation used in production
```
```

**Why it matters:** Abstract concepts become concrete through real examples.

#### 16. Version Awareness

Specify version-specific information:

```markdown
### Laravel Version Compatibility

| Feature | Laravel 10 | Laravel 11 | Laravel 12 |
|---------|------------|------------|------------|
| Sanctum | ✅ v3.x | ✅ v4.x | ✅ v4.x |
| Policies | Same | Same | New syntax |
| Middleware | Class-based | Class-based | Closure support |

**For Laravel 12 (Current Project):**
```php
// Laravel 12 specific syntax
```

**If using Laravel 10/11:**
```php
// Legacy syntax still works
```

**Breaking Changes:**
- `artisan route:cache` behavior changed in v12
- Middleware priority changed
```

**Why it matters:** Outdated code examples waste time and cause errors.

#### 17. Dutch Localization

Include Netherlands-specific guidance:

```markdown
### Nederlandse Context

**Terminologie:**
| Engels | Nederlands | Gebruik |
|--------|------------|---------|
| Invoice | Factuur | Standaard |
| VAT | BTW | Belasting |
| Chamber of Commerce | KvK | Registratie |

**Compliance:**
- BTW-nummers format: NL123456789B01
- KvK format: 8 digits
- IBAN format: NL99BANK0123456789

**Official Sources:**
- [Belastingdienst](https://www.belastingdienst.nl)
- [KvK](https://www.kvk.nl)
- [RVO](https://www.rvo.nl)

**2025 Rates:**
- BTW standaard: 21%
- BTW verlaagd: 9%
- Vennootschapsbelasting: 19% (< €200k) / 25.8% (> €200k)
```

**Why it matters:** Boekhouder is a Dutch application with Dutch compliance requirements.

#### 18. Offline Capability

Ensure guidance works without internet:

```markdown
### Offline Usage

**Works Offline:**
- All code examples (copy-paste ready)
- Configuration templates
- Checklists and validation steps

**Requires Internet:**
- External documentation links (marked with 🌐)
- API testing
- Package installation

**Offline Alternatives:**
- Instead of live API docs, see `/docs/api-reference.md`
- Package cache: `composer install --prefer-dist`
- Pre-downloaded resources in `/resources/docs/`
```

**Why it matters:** Developers work in various environments, including offline.

#### 19. Maintenance Triggers

Define when to update:

```markdown
### Update Triggers

**Immediate Update Required:**
- [ ] New Laravel major version released
- [ ] Security vulnerability discovered
- [ ] Dutch tax rates changed (check January each year)
- [ ] API endpoint deprecated

**Quarterly Review:**
- [ ] Check all external links
- [ ] Verify code examples still work
- [ ] Review for outdated dependencies
- [ ] Add new discovered patterns

**Version History:**
| Version | Date | Changes |
|---------|------|---------|
| 2.0.0 | 2025-12-20 | Added advanced pillars |
| 1.0.0 | 2025-01-01 | Initial release |

**Last Verified:** 2025-12-20
**Next Review:** 2026-03-20
```

**Why it matters:** Stale documentation is worse than no documentation.

#### 20. Measurable Outcomes

Define success criteria:

```markdown
### Success Metrics

**After completing this skill, you should be able to:**
- [ ] Create a new invoice in under 30 seconds
- [ ] Configure VAT rates without consulting external docs
- [ ] Debug common errors using the troubleshooting section
- [ ] Pass the skill verification checklist

**Verification Test:**
```bash
# Run this to verify your implementation
php artisan test --filter=InvoiceTest
```

**Expected Results:**
- All tests pass
- No deprecation warnings
- Response time < 200ms

**If Tests Fail:**
See Troubleshooting > Test Failures section
```

**Why it matters:** Users need to know when they've successfully learned the skill.

### Skill Structure Template

```markdown
---
name: skill-name
description: Clear one-line description of what this skill does
version: 1.0.0
tags: [category1, category2, domain]
---

# Skill Name

Brief overview paragraph explaining the skill's purpose and value.

## When to Use

- Specific scenario 1
- Specific scenario 2
- Specific scenario 3
- [At least 5-7 scenarios]

## Core Concepts

### Fundamental Concept 1
Explanation with examples

### Fundamental Concept 2
Explanation with examples

## Step-by-Step Guides

### Task 1: [Descriptive Name]

**Prerequisites:**
- Requirement 1
- Requirement 2

**Steps:**
1. Step 1 with command/code
2. Step 2 with command/code
3. Verification step

**Example:**
```[language]
# Working code example with comments
```

**Expected Output:**
```
What success looks like
```

## Code Examples

### Example 1: [Real-World Scenario]

**Context:** When to use this

**Implementation:**
```[language]
// Complete, working code
// With meaningful comments
// That explains the why, not just the what
```

**Explanation:**
- What this code does
- Why it's implemented this way
- Key considerations

### Example 2: Good vs Bad Patterns

```[language]
// ❌ BAD - Why this is wrong
bad_example_code()

// ✅ GOOD - Why this is right
good_example_code()
```

## Integration Guides

### Integration with [Related System/Skill]

**When to integrate:**
- Use case 1
- Use case 2

**How to integrate:**
```bash
# Integration commands
```

**Example workflow:**
1. Step 1
2. Step 2
3. Verification

## Troubleshooting

### Problem 1: [Common Issue]

**Symptoms:**
- What you see when this happens

**Cause:**
- Why this happens

**Solution:**
```bash
# Fix commands
```

**Prevention:**
- How to avoid this in the future

### Problem 2: [Another Common Issue]

[Same structure as above]

## Checklists

### Pre-Implementation Checklist
- [ ] Requirement 1 verified
- [ ] Requirement 2 verified
- [ ] Dependencies installed
- [ ] Configuration validated

### Implementation Checklist
- [ ] Step 1 completed
- [ ] Step 2 completed
- [ ] Tests passing
- [ ] Documentation updated

### Post-Implementation Checklist
- [ ] Functionality verified
- [ ] Performance acceptable
- [ ] Security reviewed
- [ ] Monitoring configured

## Best Practices

### Practice 1: [Name]

**Do:**
- Recommendation 1
- Recommendation 2

**Don't:**
- Anti-pattern 1
- Anti-pattern 2

**Example:**
```[language]
// Demonstration of best practice
```

**Rationale:**
Why this is a best practice

## Common Mistakes & Anti-Patterns

### Mistake 1: [Name]

**The Problem:**
```[language]
// Code showing the mistake
```

**Why It's Wrong:**
- Reason 1
- Reason 2

**The Fix:**
```[language]
// Corrected code
```

**Impact:**
What happens if you don't fix this

## Performance Optimization

### Optimization 1: [Technique]

**Scenario:** When to use this

**Before:**
```[language]
// Slow approach
```

**After:**
```[language]
// Optimized approach
```

**Performance Impact:**
- Metric 1: X% improvement
- Metric 2: Y reduction

**Trade-offs:**
- Consideration 1
- Consideration 2

## Security Considerations

### Security Risk 1: [Name]

**Vulnerability:**
Description of the security risk

**Attack Vector:**
How this could be exploited

**Mitigation:**
```[language]
// Secure implementation
```

**Validation:**
```bash
# How to verify security
```

## Testing Guidance

### Unit Testing

**What to test:**
- Aspect 1
- Aspect 2

**Example tests:**
```[language]
// Test examples
```

### Integration Testing

**Test scenarios:**
1. Scenario 1
2. Scenario 2

**Example:**
```[language]
// Integration test example
```

### Manual Testing

**Test checklist:**
- [ ] Test case 1
- [ ] Test case 2
- [ ] Edge case 1

## Configuration Reference

### Required Configuration

```[format]
# Configuration with explanations
setting1: value  # What this does
setting2: value  # Why this is needed
```

### Optional Configuration

```[format]
# Optional settings
optional1: value  # When to use this
optional2: value  # Trade-offs
```

### Environment Variables

| Variable | Required | Default | Description |
|----------|----------|---------|-------------|
| VAR_1 | Yes | - | Purpose |
| VAR_2 | No | default | Purpose |

## Tools & Commands Quick Reference

```bash
# Most common command
command --common-options

# Debugging command
command --debug

# Verification command
command --verify
```

## Metrics & Monitoring

### Key Metrics to Track

1. **Metric 1**
   - What to measure
   - Target value
   - How to collect

2. **Metric 2**
   - What to measure
   - Target value
   - How to collect

### Monitoring Setup

```[language]
// Monitoring configuration
```

## Resources & Documentation

### Official Documentation
- [Resource 1](https://example.com) - Description
- [Resource 2](https://example.com) - Description

### Related Skills
- `skill-name-1` - When to use together
- `skill-name-2` - Integration points

### External Tools
- Tool 1 - Purpose
- Tool 2 - Purpose

### Further Reading
- Article/Book 1 - Topic
- Article/Book 2 - Topic

### Community Resources
- Forum/Community - Link
- Stack Overflow tags - Relevant tags

## Version History & Updates

### Version 1.0.0 (YYYY-MM-DD)
- Initial release
- Features included

### Known Limitations

1. **Limitation 1**
   - Description
   - Workaround (if any)

2. **Limitation 2**
   - Description
   - Planned resolution

## Appendices

### Appendix A: Glossary

| Term | Definition |
|------|------------|
| Term 1 | Clear definition |
| Term 2 | Clear definition |

### Appendix B: Decision Trees

**Should I use X or Y?**
- If condition A → Use X
- If condition B → Use Y
- If condition C → Consider Z

### Appendix C: Migration Guides

**Migrating from Old Approach:**
1. Backup step
2. Migration step
3. Validation step
4. Rollback plan
```

## Skill Quality Evaluation Checklist

Use this comprehensive checklist to evaluate skill completeness and quality:

### Content Completeness (Score: /100)

**Basic Information (10 points)**
- [ ] (2) Clear, descriptive skill name
- [ ] (3) Concise one-line description
- [ ] (2) Appropriate tags for discoverability
- [ ] (3) Version number and date

**Purpose & Scope (10 points)**
- [ ] (4) Clear explanation of when to use
- [ ] (4) At least 5-7 specific use cases
- [ ] (2) Explicit scope boundaries (what it does NOT cover)

**Code Examples (15 points)**
- [ ] (4) At least 3 complete, working examples
- [ ] (4) Examples include context and explanation
- [ ] (4) Good vs Bad comparisons for key concepts
- [ ] (3) Examples are project-specific and realistic

**Troubleshooting (8 points)**
- [ ] (4) At least 3 common problems documented
- [ ] (2) Each problem has symptoms, cause, and solution
- [ ] (2) Prevention strategies included

**Integration Guidance (7 points)**
- [ ] (3) How to use with related skills/systems
- [ ] (2) Workflow examples showing integration
- [ ] (2) Dependencies clearly stated

**Checklists (7 points)**
- [ ] (3) Pre-implementation checklist
- [ ] (2) Implementation checklist
- [ ] (2) Post-implementation checklist

**Best Practices (8 points)**
- [ ] (4) At least 5 industry-standard best practices
- [ ] (2) Clear do's and don'ts
- [ ] (2) Rationale for each practice

**Anti-Patterns (5 points)**
- [ ] (3) At least 3 common mistakes documented
- [ ] (2) Impact of each mistake explained

**Performance (5 points)**
- [ ] (3) Performance optimization tips
- [ ] (2) Trade-offs and considerations

**Security (5 points)**
- [ ] (3) Security considerations documented
- [ ] (2) Secure implementation examples

### Advanced Pillars Checklist (Score: /50)

**Structure & Organization (10 points)**
- [ ] (3) Structured hierarchy with ### and #### headers
- [ ] (4) Progressive disclosure (simple → complex)
- [ ] (3) No flat list longer than 7 items

**Cross-References & Context (10 points)**
- [ ] (3) Links to related skills
- [ ] (3) Decision frameworks for choosing approaches
- [ ] (4) Real-world case studies or scenarios

**Version & Localization (10 points)**
- [ ] (4) Version-specific guidance (Laravel 12, Flutter 3.24)
- [ ] (4) Dutch localization (terminology, compliance)
- [ ] (2) Offline-capable content

**Maintenance & Outcomes (10 points)**
- [ ] (3) Maintenance triggers defined
- [ ] (3) Measurable outcomes/success criteria
- [ ] (2) Last verified date
- [ ] (2) Next review date

**Depth of Content (10 points)**
- [ ] (3) Each item has "what is it" explanation
- [ ] (3) Each item has "when to use" guidance
- [ ] (4) Each actionable item has code example

### Quality Indicators (Bonus Points)

**Documentation Quality**
- [ ] (+5) Includes diagrams or visual aids
- [ ] (+5) Has a quick reference section
- [ ] (+5) Includes real-world case studies
- [ ] (+5) Has version history
- [ ] (+3) Includes metrics/monitoring guidance
- [ ] (+3) Has glossary of terms
- [ ] (+3) Includes migration guides

**Usability**
- [ ] (+5) Has a table of contents (implicit through headers)
- [ ] (+5) Includes configuration templates
- [ ] (+3) Has decision trees or flowcharts
- [ ] (+3) Includes keyboard shortcuts/aliases

**Comprehensiveness**
- [ ] (+5) Links to official documentation
- [ ] (+5) References related skills
- [ ] (+3) Includes further reading resources
- [ ] (+3) Has community resources

## Skill Enhancement Process

### Step 1: Initial Assessment

1. **Read the entire skill** - Understand current state
2. **Score against checklist** - Identify gaps
3. **Identify target score** - Set improvement goals
4. **Prioritize improvements** - Quick wins vs major additions

### Step 2: Content Enhancement

For each missing or weak area:

1. **Research** - Gather accurate, current information
2. **Write** - Create clear, actionable content
3. **Exemplify** - Add concrete code examples
4. **Verify** - Test examples work in the actual project

### Step 3: Quality Review

1. **Accuracy** - All information is correct
2. **Completeness** - All essential topics covered
3. **Clarity** - Writing is clear and unambiguous
4. **Consistency** - Style and terminology consistent
5. **Actionability** - User can immediately apply the knowledge

### Step 4: Integration Check

1. **Cross-references** - Link to related skills
2. **Workflow coverage** - Fits into larger workflows
3. **Tool compatibility** - Works with existing tools
4. **Version alignment** - Matches current project versions

## Restructuring Unstructured Skills

Many existing skills contain valuable information but suffer from poor organization: long numbered lists, minimal descriptions, no grouping, and flat structure. This section provides a systematic methodology to transform these skills.

### Identifying Unstructured Skills

**Red Flags of Unstructured Content:**

```markdown
❌ UNSTRUCTURED EXAMPLE:

## Your Expertise Covers

1. OpenAI GPT Models
2. Anthropic Claude
3. Google Gemini
4. Open Source LLMs
5. Local LLMs
6. OpenRouter.ai
7. Together AI
... (60 more items with no grouping)
```

**Symptoms:**
- Long numbered lists (10+ items) without categories
- Single-line descriptions with no context
- No code examples accompanying concepts
- Flat hierarchy (only `##` headers, no `###` or `####`)
- No "when to use" or "how to" guidance
- Items that logically belong together are scattered

### The LIST-TO-STRUCTURE Transformation Method

#### Step 1: INVENTORY - Catalog All Items

Extract every item from the unstructured content:

```markdown
**Raw Inventory:**
1. OpenAI GPT Models
2. Anthropic Claude
3. Google Gemini
4. Open Source LLMs
5. Local LLMs
6. OpenRouter.ai
7. Together AI
8. Zero-shot Prompting
9. Few-shot Learning
10. Chain-of-Thought
... (continue for all items)
```

#### Step 2: IDENTIFY - Find Natural Categories

Group related items into logical categories:

```markdown
**Identified Categories:**

Category A: LLM Providers (items 1-5)
- OpenAI, Anthropic, Google, Open Source, Local

Category B: Aggregation Platforms (items 6-7)
- OpenRouter, Together AI

Category C: Prompt Engineering (items 8-10)
- Zero-shot, Few-shot, Chain-of-Thought

Category D: [Continue pattern]
```

**Category Discovery Questions:**
- What do these items have in common?
- Would you search for these together?
- Do they share prerequisites?
- Are they used together in workflows?
- Do they represent alternatives to each other?

#### Step 3: STRUCTURE - Create Hierarchy

Transform flat lists into nested structure:

```markdown
✅ STRUCTURED EXAMPLE:

## LLM Providers & Platforms

### Commercial LLM Providers

#### OpenAI GPT Models
**Models:** GPT-4, GPT-4 Turbo, GPT-3.5 Turbo
**Best for:** General tasks, code generation, reasoning
**API Cost:** $$ (moderate)

```php
// Example: OpenAI integration
$response = OpenAI::chat()->create([
    'model' => 'gpt-4-turbo',
    'messages' => [...]
]);
```

#### Anthropic Claude
**Models:** Claude 3 Opus, Claude 3.5 Sonnet, Claude 3 Haiku
**Best for:** Long context, analysis, safety-critical tasks
**API Cost:** $$ (moderate)

```php
// Example: Claude integration
$response = Anthropic::messages()->create([
    'model' => 'claude-3-5-sonnet-20241022',
    'messages' => [...]
]);
```

### Open Source Options

#### Local LLM Deployment
**Tools:** Ollama, LM Studio, vLLM
**Best for:** Privacy-sensitive data, offline use, cost reduction

**When to use local LLMs:**
- Processing sensitive financial data
- No internet connectivity
- Reducing API costs for high-volume tasks

```bash
# Example: Running Llama locally with Ollama
ollama run llama3:8b
```

## Prompt Engineering Techniques

### Basic Techniques

#### Zero-Shot Prompting
**What it is:** Asking the model to perform a task without examples
**When to use:** Simple, well-defined tasks

```
❌ BAD: "Categorize this expense"
✅ GOOD: "Categorize this expense into one of:
         office_supplies, travel, software, marketing, other.
         Expense: Adobe Creative Cloud subscription €54.99"
```

### Advanced Techniques

#### Chain-of-Thought
**What it is:** Asking the model to show its reasoning step-by-step
**When to use:** Complex calculations, multi-step reasoning

```
"Calculate the VAT for this invoice. Think step by step:
1. First identify the VAT rate
2. Then calculate the net amount
3. Finally compute the VAT amount

Invoice total: €121.00, VAT rate: 21%"
```
```

#### Step 4: ENRICH - Add Depth to Each Item

For each item in the new structure, add:

| Element | Purpose | Example |
|---------|---------|---------|
| **Description** | What is it? | "Zero-shot prompting is asking the model to perform a task without providing examples" |
| **When to Use** | Decision criteria | "Use when the task is simple and well-defined" |
| **Code Example** | Working implementation | PHP/Dart code snippet |
| **Best For** | Ideal scenarios | "Quick prototyping, simple extractions" |
| **Limitations** | What to watch out for | "May fail on ambiguous or complex tasks" |
| **Related** | Connected concepts | "See also: Few-shot Learning" |

**Enrichment Template:**

```markdown
#### [Item Name]

**What it is:** One-sentence definition

**When to use:**
- Scenario 1
- Scenario 2

**Example:**
```[language]
// Working code with comments
```

**Best for:** [ideal use cases]

**Limitations:** [what it can't do]

**Tips:**
- Pro tip 1
- Pro tip 2

**Related:** `related-item-1`, `related-item-2`
```

#### Step 5: VERIFY - Ensure Completeness

Run through verification checklist:

```markdown
**Structure Verification:**
- [ ] No list has more than 7 items without subcategories
- [ ] Every item has at least a 2-sentence description
- [ ] Every category has an introductory paragraph
- [ ] Code examples exist for actionable items
- [ ] Cross-references link related concepts

**Depth Verification:**
- [ ] "What is it?" answered for each item
- [ ] "When to use?" answered for each item
- [ ] "How to use?" demonstrated with code
- [ ] "What to avoid?" documented where applicable

**Navigation Verification:**
- [ ] Table of contents reflects structure
- [ ] Heading levels are consistent (##, ###, ####)
- [ ] Related items are near each other or linked
```

### Transformation Examples

#### Example 1: Transforming an Expertise List

**Before (Unstructured):**
```markdown
## Your Expertise Covers

1. Invoice Processing
2. Expense Categorization
3. Anomaly Detection
4. Cash Flow Prediction
5. Fraud Detection
6. Named Entity Recognition
7. Sentiment Analysis
8. Intent Classification
```

**After (Structured):**
```markdown
## Financial AI Applications

AI capabilities specifically designed for bookkeeping and financial operations.

### Document Processing

#### Invoice Processing
Automated extraction and validation of invoice data using AI.

**Extracted Fields:**
- Invoice number, date, due date
- Supplier name, VAT number, IBAN
- Line items, totals, VAT amounts

**Implementation:**
```php
class InvoiceExtractionService
{
    public function extract(string $ocrText): InvoiceData
    {
        return $this->aiService->process($ocrText, [
            'task' => 'invoice_extraction',
            'schema' => InvoiceData::schema(),
        ]);
    }
}
```

**Accuracy:** 95%+ on Dutch invoices with standard formats

#### Expense Categorization
Intelligent classification of expenses into bookkeeping categories.

**Supported Categories:**
| Category | RGS Code | Examples |
|----------|----------|----------|
| Office Supplies | 4301 | Pens, paper, printer ink |
| Travel | 4302 | Train tickets, fuel, parking |
| Software | 4401 | SaaS subscriptions, licenses |

**Implementation:**
```php
$category = $this->aiService->categorize($expense, [
    'description' => $expense->description,
    'merchant' => $expense->merchant_name,
    'amount' => $expense->amount,
]);
```

### Fraud & Anomaly Detection

#### Anomaly Detection
Identifying unusual patterns in financial transactions.

**Detection Types:**
- Unusual transaction amounts (statistical outliers)
- Unexpected vendors (not in historical patterns)
- Timing anomalies (off-hours transactions)

**Alert Thresholds:**
```php
'anomaly_rules' => [
    'amount_deviation' => 3.0,  // Standard deviations
    'new_vendor_threshold' => 1000.00,  // Flag new vendors over this
    'off_hours_start' => '22:00',
    'off_hours_end' => '06:00',
]
```

## Natural Language Processing

NLP capabilities for text understanding and communication analysis.

### Entity Recognition

#### Named Entity Recognition (NER)
Extracting business entities from unstructured text.

**Entity Types:**
- **Organizations:** Company names, legal entities
- **Financial:** Amounts, currencies, account numbers
- **Dutch Specific:** KvK numbers, BTW numbers, BSN (masked)

**Example:**
```php
$entities = $this->nerService->extract(
    "Factuur van Bakkerij de Zonnestraal B.V. (KvK 12345678) voor €250,00"
);
// Returns: [
//   ['type' => 'organization', 'value' => 'Bakkerij de Zonnestraal B.V.'],
//   ['type' => 'kvk_number', 'value' => '12345678'],
//   ['type' => 'amount', 'value' => 250.00, 'currency' => 'EUR'],
// ]
```
```

#### Example 2: Transforming a Feature List

**Before:**
```markdown
## Features
- OCR scanning
- Multi-language support
- Batch processing
- API access
- Real-time processing
- Error handling
- Caching
- Rate limiting
```

**After:**
```markdown
## Feature Overview

### Core Processing Features

#### OCR Scanning
Convert images and PDFs to machine-readable text.

**Supported Formats:** PDF, PNG, JPG, TIFF
**Languages:** Dutch (primary), English, German
**Accuracy:** 98%+ on printed text, 85%+ on handwritten

```php
$text = $this->ocrService->scan($document->path, [
    'language' => 'nld',  // Dutch
    'enhance' => true,    // Image preprocessing
]);
```

#### Batch Processing
Process multiple documents efficiently.

**Use when:**
- Processing 10+ documents
- Running scheduled jobs
- Importing historical data

```php
ProcessDocumentBatch::dispatch($documents)
    ->onQueue('document-processing')
    ->chain([
        new NotifyUserJob($user),
    ]);
```

### Integration Features

#### API Access
RESTful API for external system integration.

**Endpoints:**
| Method | Endpoint | Purpose |
|--------|----------|---------|
| POST | `/api/v1/documents/scan` | Upload and scan |
| GET | `/api/v1/documents/{id}` | Retrieve results |
| POST | `/api/v1/batch` | Batch processing |

**Authentication:** Bearer token (Laravel Sanctum)

```bash
curl -X POST https://api.boekhouder.nl/api/v1/documents/scan \
  -H "Authorization: Bearer $TOKEN" \
  -F "document=@invoice.pdf"
```

### Reliability Features

#### Error Handling
Graceful handling of processing failures.

**Error Types:**
| Error | Cause | Recovery |
|-------|-------|----------|
| `OCRFailedException` | Unreadable image | Retry with enhancement |
| `RateLimitException` | API quota exceeded | Queue for later |
| `TimeoutException` | Processing too slow | Increase timeout |

```php
try {
    $result = $this->process($document);
} catch (OCRFailedException $e) {
    return $this->retryWithEnhancement($document);
} catch (RateLimitException $e) {
    ProcessDocument::dispatch($document)->delay(now()->addMinutes(5));
}
```

#### Caching
Reduce redundant API calls and processing.

**Cache Strategy:**
- Document fingerprint → cached result (24 hours)
- AI prompt → cached response (1 hour)
- Validation results → cached (until document changes)

```php
$result = Cache::remember(
    "ocr:{$document->fingerprint}",
    now()->addHours(24),
    fn () => $this->ocrService->process($document)
);
```
```

### Quantitative Guidelines

**Maximum List Length Before Restructuring:**
- Flat list: 7 items max
- Nested list: 5 items per level max
- Total items: 20 max before major restructuring

**Minimum Description Length:**
- Item title: 2-5 words
- Brief description: 1-2 sentences
- Full description: 1-2 paragraphs
- With example: 5-15 lines of code

**Required Elements Per Item (by type):**

| Item Type | Description | Example | When to Use | Related |
|-----------|-------------|---------|-------------|---------|
| Concept | ✅ Required | Optional | ✅ Required | Optional |
| Tool | ✅ Required | ✅ Required | ✅ Required | ✅ Required |
| Technique | ✅ Required | ✅ Required | ✅ Required | Optional |
| API/Endpoint | ✅ Required | ✅ Required | Optional | Optional |
| Configuration | ✅ Required | ✅ Required | ✅ Required | Optional |

### Common Restructuring Patterns

#### Pattern A: Technology Stack → Layered Architecture
```
Before: [React, Vue, Laravel, MySQL, Redis, S3, ...]
After:
├── Frontend Layer
│   ├── React
│   └── Vue.js
├── Backend Layer
│   └── Laravel
├── Data Layer
│   ├── MySQL
│   └── Redis
└── Infrastructure Layer
    └── AWS S3
```

#### Pattern B: Feature List → User Journey
```
Before: [Login, Dashboard, Create Invoice, ...]
After:
├── Getting Started
│   ├── Registration
│   └── Login
├── Daily Operations
│   ├── Dashboard Overview
│   ├── Create Invoice
│   └── Record Expense
└── Reporting
    ├── Generate Reports
    └── Export Data
```

#### Pattern C: API Endpoints → Resource Groups
```
Before: [GET /users, POST /users, GET /invoices, ...]
After:
├── Authentication
│   ├── POST /auth/login
│   └── POST /auth/logout
├── User Management
│   ├── GET /users
│   └── POST /users
└── Invoicing
    ├── GET /invoices
    └── POST /invoices
```

#### Pattern D: Compliance Items → Regulatory Domains
```
Before: [GDPR, BTW, KvK, AWR, ...]
After:
├── Privacy & Data (GDPR)
│   ├── Data Processing
│   └── Consent Management
├── Tax Compliance
│   ├── BTW (VAT)
│   └── VPB (Corporate Tax)
└── Business Registration
    ├── KvK Requirements
    └── UBO Registration
```

## Enhancement Patterns

### Pattern 1: Adding Troubleshooting Sections

**Template:**
```markdown
### Problem: [Descriptive Name]

**Symptoms:**
- What the user sees
- Error messages
- Unexpected behavior

**Root Cause:**
Technical explanation of why this happens

**Solution:**
```bash
# Step-by-step fix
```

**Verification:**
```bash
# How to confirm it's fixed
```

**Prevention:**
- Config change to prevent
- Practice to avoid
- Monitoring to detect early
```

### Pattern 2: Adding Integration Guides

**Template:**
```markdown
### Integration with [System/Tool/Skill]

**Use Case:**
When you need to [specific scenario]

**Prerequisites:**
- Requirement 1
- Requirement 2

**Integration Steps:**
1. Configure [System A]
2. Set up [System B]
3. Connect them

**Example Workflow:**
```bash
# Complete working example
```

**Testing:**
```bash
# How to verify integration works
```
```

### Pattern 3: Adding Performance Guidance

**Template:**
```markdown
### Performance: [Aspect]

**Baseline:**
```[language]
// Current/naive approach
// Performance: X operations/second
```

**Optimized:**
```[language]
// Improved approach
// Performance: Y operations/second (Z% improvement)
```

**When to Optimize:**
- Threshold 1
- Threshold 2

**Trade-offs:**
- Complexity increase
- Memory usage
- Maintainability impact
```

### Pattern 4: Adding Security Sections

**Template:**
```markdown
### Security: [Aspect/Vulnerability]

**Risk Level:** High / Medium / Low

**Threat:**
What could go wrong

**Attack Scenario:**
How an attacker could exploit this

**Secure Implementation:**
```[language]
// Hardened code
```

**Validation:**
- [ ] Check 1
- [ ] Check 2

**Compliance:**
- Standard/regulation 1
- Standard/regulation 2
```

## Common Enhancement Needs

### For Laravel Skills

**Must Include:**
- Artisan commands with examples
- Model/Controller/Migration code
- Service layer patterns
- Policy/authorization examples
- Validation rules
- Database query optimization
- Multi-tenancy considerations (company_id scoping)
- Testing examples (PHPUnit/Pest)

### For Flutter Skills

**Must Include:**
- Widget examples
- State management patterns
- API integration code
- Navigation flows
- Error handling
- Platform-specific considerations
- Performance tips (build optimization)
- Testing examples (widget/integration tests)

### For Dutch Compliance Skills

**Must Include:**
- Current year values (2025)
- Legal references (article numbers)
- Official source links (belastingdienst.nl)
- Calculation examples with actual rates
- Deadline information
- Filing requirements
- Penalty information
- Recent law changes

### For Integration Skills

**Must Include:**
- Authentication setup
- API endpoints with examples
- Error handling
- Rate limiting
- Retry logic
- Monitoring
- Test environment setup
- Production checklist

## Skill Maintenance

### Annual Reviews

**Every January:**
- [ ] Update tax rates and thresholds
- [ ] Update dependency versions
- [ ] Review and update code examples
- [ ] Check external links
- [ ] Verify compliance requirements
- [ ] Update version number

### Quarterly Reviews

- [ ] Review for outdated information
- [ ] Add new discovered best practices
- [ ] Update based on user feedback
- [ ] Check for new tool versions

### Continuous Improvement

**When to update:**
- New feature added to project
- Bug pattern discovered
- Performance improvement found
- Security vulnerability patched
- Community feedback received
- Framework/language updated

## Anti-Patterns in Skill Writing

### 1. Too Generic

**Bad:**
```markdown
## Best Practices
- Write clean code
- Test your code
- Document your code
```

**Good:**
```markdown
## Best Practices

### 1. Scope All Queries by Company ID

In our multi-tenant system, ALWAYS scope queries:

```php
// ❌ BAD - Missing tenant scope
$invoices = Invoice::all();

// ✅ GOOD - Properly scoped
$invoices = Invoice::where('company_id', $currentCompany->id)->get();
```

**Why:** Prevents data leakage between companies
**Risk:** GDPR violation, data breach
```

### 2. No Examples

**Bad:**
```markdown
Use dependency injection for better testability.
```

**Good:**
```markdown
### Dependency Injection

**Without DI:**
```php
class InvoiceController {
    public function store() {
        $service = new InvoiceService(); // Hard-coded dependency
        // ...
    }
}
```

**With DI:**
```php
class InvoiceController {
    public function __construct(
        private InvoiceService $service
    ) {}

    public function store() {
        $this->service->create(...); // Injected, testable
    }
}
```

**Benefits:**
- Easy to mock in tests
- Can swap implementations
- Dependencies explicit
```

### 3. Missing Context

**Bad:**
```markdown
Run: `php artisan migrate`
```

**Good:**
```markdown
### Running Migrations

**When:** After pulling new code that includes database changes

**Command:**
```bash
cd bookkeeping-app
php artisan migrate
```

**Expected Output:**
```
Migrating: 2025_12_13_create_advertisements_table
Migrated:  2025_12_13_create_advertisements_table (123.45ms)
```

**If it fails:**
See Troubleshooting > Migration Errors below

**Multi-tenant consideration:**
Migrations run globally but add company_id columns for tenant isolation
```

### 4. Outdated Information

**Bad:**
```markdown
VAT rate in Netherlands: 21%
```

**Good:**
```markdown
### Dutch VAT Rates (2025)

- **Standard rate:** 21% (most goods and services)
- **Reduced rate:** 9% (food, books, medicine, hotels)
- **Zero rate:** 0% (exports outside EU)

**Last updated:** January 2025
**Source:** [Belastingdienst](https://www.belastingdienst.nl/btw-tarieven)

**Note:** Review annually as rates may change
```

### 5. No Troubleshooting

**Bad:**
```markdown
Configure eHerkenning integration and it should work.
```

**Good:**
```markdown
### eHerkenning Integration

**Setup:** [steps]

**Common Issues:**

#### Issue 1: Certificate Error
**Error:** "Certificate validation failed"
**Cause:** Certificate not properly installed in keystore
**Fix:**
```bash
# Reinstall certificate
keytool -import -alias eherkenning -file cert.cer -keystore keystore.jks
```

#### Issue 2: Metadata Mismatch
**Error:** "Issuer not recognized"
**Cause:** EntityID in metadata doesn't match configuration
**Fix:** Update config/eherkenning.php with correct EntityID
```

## Skill Templates by Type

### Testing Skill Template

```markdown
## Test Categories
- Unit tests
- Integration tests
- Feature tests
- E2E tests

## Running Tests
[Commands with examples]

## Writing Tests
[Examples with AAA pattern]

## Test Coverage
[Minimum requirements]

## Debugging Tests
[Common issues and solutions]

## CI/CD Integration
[GitHub Actions example]
```

### Integration Skill Template

```markdown
## Overview
[What this integrates]

## Authentication
[How to authenticate]

## API Endpoints
[Available endpoints with examples]

## Error Handling
[Common errors and solutions]

## Rate Limiting
[Limits and how to handle]

## Testing
[Test environment setup]

## Monitoring
[What to monitor]

## Production Checklist
[Pre-deployment verification]
```

### Compliance Skill Template

```markdown
## Legal Requirements
[Laws, regulations, standards]

## Implementation Guide
[How to comply]

## Validation
[How to verify compliance]

## Reporting
[Required reports and deadlines]

## Audit Trail
[What to log]

## Penalties
[Consequences of non-compliance]

## Updates
[How regulations change]

## Official Resources
[Government sources]
```

## Measuring Skill Effectiveness

### Usage Metrics

1. **Frequency** - How often is skill invoked
2. **Success Rate** - How often does it help solve the problem
3. **Time to Resolution** - How quickly user completes task
4. **User Feedback** - Explicit feedback on usefulness

### Quality Metrics

1. **Completeness Score** - Based on checklist (0-100)
2. **Accuracy Rate** - Information correctness
3. **Example Coverage** - % of concepts with examples
4. **Link Validity** - % of external links working
5. **Recency** - Days since last update

### Improvement Targets

- **Completeness:** 80+ points
- **Examples:** At least 5 working examples
- **Troubleshooting:** At least 3 common issues
- **Links:** 100% valid
- **Updates:** Within 90 days for active skills

## Quick Reference

### Skill Enhancement Checklist

For each skill enhancement session:

- [ ] Read entire skill
- [ ] Score against quality checklist
- [ ] Add at least 3 code examples
- [ ] Add troubleshooting section (3+ issues)
- [ ] Add integration guidance
- [ ] Create verification checklist
- [ ] Document best practices (5+)
- [ ] Document anti-patterns (3+)
- [ ] Add performance tips
- [ ] Add security considerations
- [ ] Add testing guidance
- [ ] Link to official documentation
- [ ] Cross-reference related skills
- [ ] Update version number
- [ ] Add/update changelog

### Red Flags in Skills

Watch out for these quality issues:

- No code examples
- Generic advice without context
- Outdated information (>1 year old for tech)
- No troubleshooting section
- No integration guidance
- Missing security considerations
- No testing examples
- Broken external links
- Inconsistent formatting
- No versioning

## Resources

### Writing Resources
- [Technical Writing Guide](https://developers.google.com/tech-writing)
- [Markdown Guide](https://www.markdownguide.org/)
- [Documentation Style Guides](https://google.github.io/styleguide/)

### Skill Development
- Claude Code Documentation
- Anthropic Prompt Engineering Guide
- Industry-specific documentation standards

### Quality Assurance
- Linters for markdown
- Link checkers
- Grammar checkers
- Code example validators

## Conclusion

Great skills are:
- **Specific** - Tailored to exact use cases
- **Actionable** - Users can immediately apply them
- **Complete** - Cover all essential aspects
- **Current** - Updated regularly
- **Tested** - Examples actually work
- **Clear** - Easy to understand and follow
- **Contextual** - Fit into larger workflows

Use this framework systematically to create and maintain skills that truly empower users to accomplish their goals efficiently and correctly.
