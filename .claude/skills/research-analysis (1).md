---
name: research-analysis
description: Expert guidance for systematic research, analysis, report writing, and evidence-based decision making across any domain
version: 2.0.1
tags: [research, analysis, methodology, reports, insights, decision-making, investigation, security, dutch-compliance]
trigger_keywords: [sk-research-analysis, research methodology, systematic analysis, investigation, research report, data analysis, evidence gathering, analytical research]
---

# Research and Analysis Expert Skill

This skill provides comprehensive guidance for conducting systematic research, performing rigorous analysis, and producing actionable insights. It encompasses methodologies used by professional researchers, analysts, and investigators across business, academic, and technical domains.

## When to Use This Skill

- Investigating complex problems requiring systematic exploration
- Conducting market research or competitive analysis
- Performing due diligence on vendors, partners, or technologies
- Writing research reports or analysis documents
- Making data-driven decisions with proper evidence
- Exploring unfamiliar codebases or technologies
- Investigating bugs or performance issues systematically
- Preparing technical assessments or recommendations
- Conducting security audits or compliance reviews
- Answering complex questions requiring multi-source validation

## Scope & Boundaries

**In Scope:**
- Systematic investigation methodologies
- Research planning and execution
- Data collection and analysis techniques
- Report writing and documentation
- Evidence-based decision making
- Qualitative and quantitative research
- Technical and business analysis

**Out of Scope:**
- Statistical modeling (see `data-science-expert`)
- Academic paper writing (different format requirements)
- Legal discovery or forensic investigation
- Market research execution (vs planning)
- Primary data collection at scale (use specialized tools)
- Advanced statistical analysis (use R, Python, specialized tools)

## Auto-Trigger Keywords

This skill should automatically activate when detecting:
- "research", "investigate", "analyze", "study", "explore"
- "find out", "discover", "determine", "assess", "evaluate"
- "compare", "contrast", "benchmark", "measure"
- "why does", "how does", "what causes", "what's the best"
- "pros and cons", "advantages and disadvantages", "trade-offs"
- "deep dive", "thorough analysis", "comprehensive review"
- "evidence", "data", "findings", "conclusions", "recommendations"

## Core Research Frameworks

### The ERP Model (Explore, Refine, Produce)

A practical workflow for any research task:

#### Phase 1: Explore
**Goal:** Gather broad information and identify key themes

```markdown
Exploration Checklist:
- [ ] Define the core question or problem
- [ ] Identify 3-5 key sub-questions
- [ ] List potential information sources
- [ ] Conduct initial broad searches
- [ ] Note emerging patterns and themes
- [ ] Identify gaps in understanding
```

**Techniques:**
- Brainstorm all possible angles
- Use mind mapping to visualize connections
- Cast a wide net before narrowing focus

#### Phase 2: Refine
**Goal:** Filter, prioritize, and deepen analysis

```markdown
Refinement Checklist:
- [ ] Prioritize most relevant sources
- [ ] Cross-validate key findings
- [ ] Resolve contradictions
- [ ] Identify outliers and edge cases
- [ ] Quantify where possible
- [ ] Document methodology
```

**Techniques:**
- Apply critical analysis to each source
- Look for convergence across sources
- Question assumptions and biases

#### Phase 3: Produce
**Goal:** Synthesize findings into actionable output

```markdown
Production Checklist:
- [ ] Structure findings logically
- [ ] Support claims with evidence
- [ ] Highlight uncertainties and limitations
- [ ] Provide clear recommendations
- [ ] Include next steps or action items
- [ ] Review and quality check
```

### Framework Analysis Method

A 5-stage approach for qualitative research:

| Stage | Activity | Output |
|-------|----------|--------|
| **1. Familiarization** | Read and re-read all data | Initial impressions |
| **2. Framework Identification** | Identify key themes and categories | Thematic framework |
| **3. Indexing** | Apply framework systematically to data | Coded data |
| **4. Charting** | Create matrices and summaries | Data charts |
| **5. Mapping & Interpretation** | Identify relationships and meaning | Findings |

### FINERMAPS Research Question Framework

Evaluate research questions against these criteria:

| Criterion | Question to Ask |
|-----------|----------------|
| **F**easible | Can this be answered with available resources? |
| **I**nteresting | Does this matter to stakeholders? |
| **N**ovel | Does this add new knowledge? |
| **E**thical | Can this be investigated ethically? |
| **R**elevant | Does this address a real need? |
| **M**anageable | Is the scope appropriate? |
| **A**ppropriate | Does the methodology fit? |
| **P**otential value | What's the impact of findings? |
| **S**ystematic | Can this be done rigorously? |

## Research Methodologies

### Quantitative Research
**When to use:** Measuring, counting, or statistical analysis

```markdown
Steps:
1. Define hypothesis or research question
2. Design data collection method
3. Collect data systematically
4. Apply statistical analysis
5. Interpret results objectively
6. Report with confidence intervals

Tools:
- Surveys and questionnaires
- A/B testing
- Log analysis
- Performance benchmarks
- Statistical software (R, Python, Excel)
```

**Example: API Performance Analysis**
```php
// ❌ BAD - Insufficient data collection
$avgResponseTime = $this->getAverageResponseTime();
// Problem: No sample size, no variance, no edge cases

// ✅ GOOD - Comprehensive quantitative approach
$metrics = [
    'response_times' => $this->collectResponseTimes(1000), // Sufficient sample
    'error_rates' => $this->calculateErrorRate(),
    'throughput' => $this->measureThroughput(),
    'concurrent_users' => $this->getCurrentConcurrency(),
];

// Statistical analysis with proper context
$stats = [
    'sample_size' => count($metrics['response_times']),
    'mean' => array_sum($metrics['response_times']) / count($metrics['response_times']),
    'median' => $this->median($metrics['response_times']),
    'p95' => $this->percentile($metrics['response_times'], 95),
    'p99' => $this->percentile($metrics['response_times'], 99),
    'std_dev' => $this->standardDeviation($metrics['response_times']),
];
```

**Why the good approach is better:**
- Includes sample size for statistical validity
- Captures both average and edge cases (p95, p99)
- Measures variance (std deviation)
- Considers multiple dimensions (errors, throughput, concurrency)

### Qualitative Research
**When to use:** Understanding experiences, motivations, or context

```markdown
Steps:
1. Define research objectives
2. Select appropriate methods (interviews, observation, analysis)
3. Collect rich, descriptive data
4. Code and categorize themes
5. Interpret patterns and meanings
6. Validate through triangulation

Methods:
- Interviews (structured, semi-structured, unstructured)
- Focus groups
- Content analysis
- Case studies
- Observational studies
```

### Mixed Methods Research
**When to use:** Complex problems requiring both breadth and depth

```markdown
Approaches:
1. Sequential Explanatory: Quantitative → Qualitative
   - First measure, then explore why

2. Sequential Exploratory: Qualitative → Quantitative
   - First explore, then validate at scale

3. Concurrent: Both simultaneously
   - Different aspects of same question
```

## Analyst Daily Operations

### Morning Routine
```markdown
08:00-09:00: Review and Planning
- [ ] Check overnight developments
- [ ] Review pending research tasks
- [ ] Prioritize day's activities
- [ ] Set clear daily objectives

09:00-12:00: Deep Research Work
- [ ] Uninterrupted research time
- [ ] Primary data collection
- [ ] Analysis and synthesis
- [ ] Document findings as you go
```

### Research Session Structure
```markdown
Before Starting:
- Clear objective defined
- Time limit set (45-90 minutes recommended)
- Distractions eliminated
- Note-taking system ready

During Research:
- Stay focused on objective
- Document sources immediately
- Note questions that arise
- Track time spent per source

After Session:
- Summarize key findings
- Update research log
- Identify next steps
- Take a break before next session
```

### Weekly Rhythm
| Day | Focus |
|-----|-------|
| Monday | Review backlog, prioritize, plan |
| Tue-Thu | Deep research and analysis work |
| Friday | Synthesis, report writing, review |
| Weekly | Retrospective on methods and efficiency |

## Efficiency Tools and Techniques

### Information Management

#### Zotero Method (Citation and Source Management)
```markdown
Source Capture:
1. Save source immediately when found
2. Add tags for categorization
3. Highlight key passages
4. Add personal annotations
5. Link related sources

Benefits:
- Never lose a source again
- Easy citation generation
- Builds personal knowledge base
- Enables cross-project reuse
```

#### The PARA Method (Project Organization)
```markdown
Organize information into:
P - Projects (active research tasks)
A - Areas (ongoing responsibilities)
R - Resources (reference materials)
A - Archives (completed work)

Example:
/Projects/api-performance-analysis/
  - research-notes.md
  - sources/
  - findings.md
  - final-report.md

/Resources/research-templates/
  - report-template.md
  - analysis-checklist.md
```

### Search Strategies

#### Boolean Search Operators
```markdown
Operator Examples:
- AND: "Laravel" AND "performance" AND "optimization"
- OR: "cache" OR "caching" OR "cached"
- NOT: "Laravel" NOT "Vue" (exclude Vue results)
- "exact phrase": "N+1 query problem"
- site: site:stackoverflow.com Laravel cache
- filetype: filetype:pdf research methodology
```

#### Progressive Search Refinement
```markdown
Iteration 1: Broad search
  "API performance issues"

Iteration 2: Add specifics
  "Laravel API performance slow response"

Iteration 3: Target solutions
  "Laravel API response time optimization techniques 2024"

Iteration 4: Expert sources
  site:laravel.com OR site:laracasts.com API performance
```

### Time Management

#### The Pomodoro Technique for Research
```markdown
25-minute Focus Block:
- Single research objective
- No interruptions
- Deep concentration

5-minute Break:
- Quick notes on findings
- Mental reset
- Physical movement

After 4 blocks (2 hours):
- 15-30 minute break
- Review and synthesize
- Plan next session
```

## Report Writing and Structure

### IMRaD Format (Scientific/Technical)

```markdown
# Research Title

## Introduction
- Background and context
- Problem statement
- Research objectives
- Scope and limitations

## Methods
- Research approach
- Data collection methods
- Analysis techniques
- Tools used

## Results
- Findings presented objectively
- Data visualization
- Statistical summaries
- Key observations

## Discussion
- Interpretation of results
- Comparison with existing knowledge
- Implications
- Limitations of study

## Conclusions & Recommendations
- Summary of key findings
- Actionable recommendations
- Future research directions
```

### Executive Summary Format (Business)

```markdown
# [Topic] Analysis Report

## Executive Summary
[2-3 paragraphs: problem, key findings, recommendations]

## Key Findings
1. Finding 1 with supporting evidence
2. Finding 2 with supporting evidence
3. Finding 3 with supporting evidence

## Recommendations
| Priority | Recommendation | Impact | Effort |
|----------|---------------|--------|--------|
| High | Action 1 | High | Medium |
| Medium | Action 2 | Medium | Low |

## Detailed Analysis
[Full analysis with data]

## Appendices
- Raw data
- Methodology details
- Additional resources
```

### Technical Investigation Report

```markdown
# [Issue/Topic] Investigation

## Summary
- Issue: [Brief description]
- Root Cause: [One-line summary]
- Resolution: [One-line summary]
- Impact: [Severity and scope]

## Investigation Timeline
| Time | Activity | Finding |
|------|----------|---------|
| 09:00 | Initial report | Symptom observed |
| 09:15 | Log analysis | Error pattern found |
| 09:45 | Root cause | Configuration issue |
| 10:30 | Fix deployed | Issue resolved |

## Detailed Analysis
[Step-by-step investigation process]

## Root Cause
[Technical explanation]

## Resolution
[What was done to fix]

## Prevention
[How to prevent recurrence]
```

## Research Question Formulation

### The PICOT Framework (For Problem Investigation)
```markdown
P - Population: Who/what is affected?
I - Intervention: What action or change?
C - Comparison: Compared to what alternative?
O - Outcome: What result do we measure?
T - Timeframe: Over what period?

Example:
"In Laravel applications (P), does implementing Redis caching (I)
compared to file caching (C) improve API response times (O)
over a 30-day period (T)?"
```

### Question Refinement Process

```markdown
Step 1: Start Broad
"Why is the application slow?"

Step 2: Add Specificity
"Why are API responses slow on the invoice endpoint?"

Step 3: Make Measurable
"What causes invoice endpoint response times to exceed 500ms?"

Step 4: Add Context
"What causes invoice endpoint response times to exceed 500ms
when processing invoices with >10 line items?"

Step 5: Final Question
"What database query patterns cause invoice endpoint response
times to exceed 500ms when processing invoices with more than
10 line items, and what optimization strategies would reduce
this to under 200ms?"
```

### Types of Research Questions

| Type | Purpose | Example |
|------|---------|---------|
| **Descriptive** | What is happening? | "What are the current API response times?" |
| **Comparative** | How do X and Y differ? | "How does Redis compare to Memcached?" |
| **Causal** | What causes X? | "What causes memory leaks in queue workers?" |
| **Exploratory** | What might be possible? | "What caching strategies could improve performance?" |
| **Evaluative** | How good is X? | "How effective is the current authentication system?" |

## Data Collection Methods

### Primary Research (Collecting Original Data)

#### Interviews
```markdown
Preparation:
- [ ] Define interview objectives
- [ ] Prepare question guide
- [ ] Schedule appropriate length (30-60 min)
- [ ] Test recording equipment

Question Types:
- Opening: Easy, rapport-building
- Core: Key research questions
- Probing: "Tell me more about..."
- Closing: "Anything else to add?"

Post-Interview:
- [ ] Transcribe key points
- [ ] Identify themes
- [ ] Note follow-up questions
```

#### Surveys
```markdown
Design Principles:
- Keep surveys short (5-10 min max)
- Use clear, unambiguous language
- Include mix of question types
- Test with small group first

Question Types:
- Multiple choice (quantitative)
- Likert scale (agreement/satisfaction)
- Open-ended (qualitative insights)
- Ranking (priority understanding)
```

#### Observation
```markdown
Structured Observation:
- Define what to observe
- Create observation checklist
- Note time and context
- Minimize observer effect

Application in Code Review:
- Watch how users interact with system
- Observe developer workflow
- Monitor system behavior under load
```

### Secondary Research (Analyzing Existing Data)

#### Source Evaluation (CRAAP Test)
```markdown
C - Currency: When was it published? Is it current?
R - Relevance: Does it address your question?
A - Authority: Who created it? Are they credible?
A - Accuracy: Is it supported by evidence?
P - Purpose: Why was it created? Any bias?

Score each 1-5. Sources scoring <15 need extra validation.
```

#### Cross-Referencing Strategy
```markdown
For any key finding:
1. Find at least 2-3 independent sources
2. Check for recency (prefer <2 years old)
3. Verify author expertise
4. Look for primary source citations
5. Note any contradictions
```

## Security Considerations in Research

Research often involves accessing sensitive data, credentials, or confidential information. Follow these security practices:

### Data Protection During Research

#### Handling Sensitive Information

**Risk:** Exposing customer data, credentials, or business secrets during analysis

**Secure Practices:**
```php
// ❌ BAD - Logging sensitive data
Log::info('User research data', [
    'email' => $user->email,
    'password_hash' => $user->password,
    'bank_account' => $account->iban,
]);

// ✅ GOOD - Redacted logging
Log::info('User research data', [
    'user_id' => $user->id,
    'email_domain' => Str::after($user->email, '@'),
    'has_bank_account' => $account !== null,
]);
```

**GDPR Compliance:**
- [ ] Minimize data collection (only what's needed)
- [ ] Anonymize/pseudonymize research data
- [ ] Document data processing purpose
- [ ] Respect data retention limits
- [ ] Obtain consent for research use

#### Credential Management

**Never include credentials in research reports:**

```php
// ❌ BAD - Hardcoded credentials in research notes
// API Key: sk-live-1234567890abcdef
// Database: mysql://root:password@localhost

// ✅ GOOD - Reference environment variables
// API Key: Stored in .env as RESEARCH_API_KEY
// Database: Using standard Laravel DB connection
```

### Research Access Control

**Principle of Least Privilege:**
```php
// Only request necessary permissions for research
$researcher = User::find($researcherId);

// ❌ BAD - Full admin access for research
$researcher->assignRole('admin');

// ✅ GOOD - Limited read-only access
$researcher->givePermissionTo('view-analytics');
$researcher->givePermissionTo('export-anonymized-data');
```

### Data Anonymization Techniques

#### For User Research

```php
class ResearchDataService
{
    public function anonymizeUserData($users)
    {
        return $users->map(function ($user) {
            return [
                'id' => hash('sha256', $user->id), // One-way hash
                'age_group' => $this->getAgeGroup($user->birthdate),
                'city' => $user->city, // Geographic ok if not too specific
                'subscription_type' => $user->subscription_type,
                // Exclude: name, email, phone, address
            ];
        });
    }

    private function getAgeGroup($birthdate): string
    {
        $age = Carbon::parse($birthdate)->age;
        if ($age < 25) return '18-24';
        if ($age < 35) return '25-34';
        if ($age < 45) return '35-44';
        if ($age < 55) return '45-54';
        return '55+';
    }
}
```

#### For Financial Research

```php
// ❌ BAD - Actual amounts
$invoices->pluck('total'); // [€1250.00, €890.50, ...]

// ✅ GOOD - Ranges or aggregates
$invoices->groupBy(function ($invoice) {
    if ($invoice->total < 100) return '€0-100';
    if ($invoice->total < 500) return '€100-500';
    if ($invoice->total < 1000) return '€500-1000';
    return '€1000+';
})->map->count();
```

### Secure Research Documentation

**Checklist before sharing research:**
- [ ] No passwords, API keys, or tokens
- [ ] No personally identifiable information (PII)
- [ ] No actual customer names or emails
- [ ] No internal IP addresses or infrastructure details
- [ ] No unredacted screenshots with sensitive data
- [ ] No database connection strings
- [ ] No security vulnerability details (unless for security team)

### Multi-Tenant Research Safety

In Boekhouder's multi-tenant system, always scope research queries:

```php
// ❌ BAD - Cross-tenant data leak
$allInvoices = Invoice::all(); // Accesses ALL companies

// ✅ GOOD - Properly scoped
$companyInvoices = Invoice::where('company_id', $currentCompany->id)->get();

// ✅ BETTER - Use global scope trait
// In Invoice model:
use BelongsToCompany; // Automatically scopes queries
```

### Reporting Security Issues

If research uncovers security vulnerabilities:

1. **Do NOT include details in general reports**
2. Report immediately to security team
3. Use secure communication channel
4. Follow responsible disclosure timeline
5. Document only in secure, access-controlled system

## Dutch Context & Localization

### Nederlandse Onderzoeksbronnen (Dutch Research Sources)

When researching Dutch bookkeeping, tax, or compliance topics, use these authoritative sources:

#### Primary Official Sources

| Organization | Domain | Focus Area |
|--------------|--------|------------|
| **Belastingdienst** | belastingdienst.nl | Tax regulations, BTW, VPB |
| **Kamer van Koophandel (KvK)** | kvk.nl | Business registration, UBO |
| **Autoriteit Financiële Markten (AFM)** | afm.nl | Financial supervision |
| **De Nederlandsche Bank (DNB)** | dnb.nl | Banking regulations |
| **Rijksdienst voor Ondernemend Nederland (RVO)** | rvo.nl | Subsidies, permits |
| **Centraal Bureau voor de Statistiek (CBS)** | cbs.nl | Official statistics |

#### Research Terminology

| English Term | Dutch Term | Context |
|--------------|------------|---------|
| Invoice | Factuur | Official term in NL tax law |
| VAT | BTW (Belasting over de Toegevoegde Waarde) | Always use BTW in Dutch context |
| Tax Return | Aangifte | Quarterly BTW aangifte |
| Chamber of Commerce | Kamer van Koophandel (KvK) | Registration number: 8 digits |
| Corporate Tax | Vennootschapsbelasting (VPB) | For B.V. entities |
| Sole Proprietorship | Eenmanszaak | Common business form |
| Private Limited Company | Besloten Vennootschap (B.V.) | Requires notary |
| General Partnership | Vennootschap onder Firma (V.O.F.) | 2+ partners |
| Annual Report | Jaarrekening | Legal requirement for B.V. |
| Ledger | Grootboek | Accounting records |

### Dutch Compliance Research Checklist

When researching Dutch tax/compliance topics:

- [ ] **Verify year specificity** - Tax rates change annually
- [ ] **Check effective dates** - Laws have specific implementation dates
- [ ] **Confirm legal basis** - Reference specific articles (e.g., "Artikel 15 Wet IB 2001")
- [ ] **Use official sources** - Prioritize .nl government domains
- [ ] **Consider regional variations** - Some rules differ by gemeente
- [ ] **Check European context** - EU directives affect Dutch law

### Example: Researching BTW (VAT) Rates for 2025

```php
/**
 * Research Process for BTW Rates
 *
 * 1. Primary Source: https://www.belastingdienst.nl/btw-tarieven
 * 2. Legal Basis: Wet op de omzetbelasting 1968, Tabel I en II
 * 3. Verification: Cross-check with RVO.nl for specific product categories
 */

class BtwRatesResearch
{
    // ❌ BAD - Hardcoded assumptions
    public const STANDARD_RATE = 0.21; // What year? Source?

    // ✅ GOOD - Documented with source and date
    public const BTW_RATES_2025 = [
        'standard' => [
            'rate' => 0.21,
            'description' => 'Algemeen tarief',
            'source' => 'https://www.belastingdienst.nl/btw-tarieven',
            'legal_basis' => 'Wet OB 1968, art. 9 lid 1',
            'effective_from' => '2019-01-01', // Last change
        ],
        'reduced' => [
            'rate' => 0.09,
            'description' => 'Verlaagd tarief',
            'applies_to' => ['Levensmiddelen', 'Boeken', 'Kunst', 'Geneesmiddelen'],
            'source' => 'https://www.belastingdienst.nl/btw-tarieven',
            'legal_basis' => 'Wet OB 1968, Tabel I',
        ],
        'zero' => [
            'rate' => 0.00,
            'description' => 'Nultarief',
            'applies_to' => ['Export buiten EU', 'Intracommunautaire leveringen'],
            'legal_basis' => 'Wet OB 1968, art. 9 lid 2',
        ],
    ];
}
```

### Dutch Date and Number Formats in Research

**Date Formats:**
- Official documents: `dd-mm-yyyy` (31-12-2025)
- Deadlines: Often use "voor [date]" (before) or "uiterlijk [date]" (at latest)

**Number Formats:**
```php
// Dutch formatting in research output
$amount = 1234.56;

// ❌ BAD - American format
echo '$' . number_format($amount, 2); // $1,234.56

// ✅ GOOD - Dutch format
echo '€ ' . number_format($amount, 2, ',', '.'); // € 1.234,56
```

### Research Keywords by Domain

**Tax Research (Belastingen):**
- "BTW aangifte [year]"
- "Vennootschapsbelasting tarief [year]"
- "Inkomstenbelasting schijven [year]"
- "Aftrekbare kosten ondernemers"

**Business Registration:**
- "KvK inschrijving [business type]"
- "UBO registratie verplichtingen"
- "Handelsregisternummer"

**Compliance:**
- "Bewaarplicht administratie" (Record retention)
- "Kasstelsel versus factuurstelsel" (Cash vs accrual)
- "Fiscale eenheid voorwaarden" (Fiscal unity)

### Seasonal Research Considerations

| Period | Research Focus | Reason |
|--------|----------------|--------|
| **January** | New tax rates, thresholds | Rates change annually |
| **Q1** | Annual report requirements | Deadline for jaarrekening |
| **March** | BTW aangifte Q4 | Quarterly filing |
| **May** | Income tax return (IB) | Deadline 1 May |
| **September** | Budget Day (Prinsjesdag) | New tax proposals announced |

### Dutch Legal Citation Format

When citing Dutch law in research:

```markdown
**Correct Citation Format:**
- Wet op de omzetbelasting 1968, artikel 9 lid 1
- Burgerlijk Wetboek Boek 2, artikel 10
- Uitvoeringsregeling Belastingdienst 2003

**Example in Report:**
> According to article 15 of the Wet op de omzetbelasting 1968,
> entrepreneurs must file their BTW returns within one month
> after the end of each quarter.

**Short form after first use:**
- First: Wet op de omzetbelasting 1968 (Wet OB 1968)
- After: Wet OB 1968, art. 15
```

## Analysis Techniques

### SWOT Analysis
```markdown
| Strengths | Weaknesses |
|-----------|------------|
| Internal positives | Internal negatives |

| Opportunities | Threats |
|---------------|---------|
| External positives | External negatives |
```

### Root Cause Analysis (5 Whys)
```markdown
Problem: API response time is slow

Why 1: Database queries take too long
Why 2: Queries are not optimized
Why 3: Missing indexes on frequently queried columns
Why 4: Indexes were not added during migration
Why 5: No database performance review in deployment process

Root Cause: Missing database performance review step
Solution: Add database performance checklist to deployment
```

### Comparative Analysis Matrix
```markdown
| Criterion | Option A | Option B | Option C | Weight |
|-----------|----------|----------|----------|--------|
| Cost | ⭐⭐⭐ | ⭐⭐ | ⭐ | 30% |
| Performance | ⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐ | 40% |
| Ease of Use | ⭐⭐⭐ | ⭐ | ⭐⭐ | 30% |
| **Weighted Score** | **2.5** | **2.1** | **2.0** | |
```

### Gap Analysis
```markdown
| Aspect | Current State | Desired State | Gap | Action |
|--------|--------------|---------------|-----|--------|
| Response Time | 800ms | 200ms | 600ms | Optimize DB |
| Test Coverage | 45% | 80% | 35% | Add tests |
| Documentation | Minimal | Complete | Major | Document |
```

## Common Research Mistakes

### Mistake 1: Confirmation Bias
**Problem:** Seeking only evidence that supports existing beliefs

**Prevention:**
```markdown
- Actively search for contradicting evidence
- Ask: "What would change my mind?"
- Involve reviewer with different perspective
- Use structured evaluation criteria
```

### Mistake 2: Recency Bias
**Problem:** Overweighting recent information

**Prevention:**
```markdown
- Include historical data in analysis
- Look for long-term trends
- Consider if recent events are anomalies
- Balance recent and historical sources
```

### Mistake 3: Scope Creep
**Problem:** Research expanding beyond original objective

**Prevention:**
```markdown
- Define clear boundaries upfront
- Document scope in writing
- Review scope regularly
- Park interesting tangents for later
```

### Mistake 4: Analysis Paralysis
**Problem:** Over-researching without reaching conclusions

**Prevention:**
```markdown
- Set time limits for research phases
- Define "good enough" criteria
- Make recommendations with available data
- Document uncertainties, don't wait for perfection
```

## Research Blueprint Template

```markdown
# Research Blueprint: [Topic]

## 1. Research Objective
**Primary Question:** [Clear, specific question]
**Sub-Questions:**
1. [Sub-question 1]
2. [Sub-question 2]
3. [Sub-question 3]

## 2. Scope Definition
**In Scope:** [What will be covered]
**Out of Scope:** [What will NOT be covered]
**Constraints:** [Time, resources, access limitations]

## 3. Methodology
**Approach:** [Quantitative/Qualitative/Mixed]
**Data Sources:**
- Primary: [List sources]
- Secondary: [List sources]

**Analysis Methods:**
- [Method 1]
- [Method 2]

## 4. Timeline
| Phase | Duration | Deliverable |
|-------|----------|-------------|
| Exploration | X days | Initial findings |
| Deep Analysis | Y days | Detailed analysis |
| Synthesis | Z days | Draft report |
| Review | N days | Final report |

## 5. Success Criteria
- [ ] Primary question answered
- [ ] Recommendations provided
- [ ] Evidence documented
- [ ] Stakeholder approval

## 6. Risk Mitigation
| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| [Risk 1] | Medium | High | [Action] |
| [Risk 2] | Low | Medium | [Action] |
```

## Real-World Case Studies from Boekhouder

### Case Study 1: Invoice Processing Performance Investigation

**Context:** Users reported slow invoice PDF generation in Boekhouder mobile app (Flutter).

**Research Objective:**
Determine root cause of 5+ second PDF generation time and recommend solutions.

**Methodology:**
1. **Quantitative Analysis** - Profiled 100 invoice generations
2. **Comparative Analysis** - Tested different invoice sizes (1-50 line items)
3. **Technical Investigation** - Analyzed Flutter PDF library performance

**Research Process:**

```dart
// Step 1: Collect baseline metrics
class InvoicePdfResearch {
  Future<Map<String, dynamic>> benchmarkGeneration() async {
    final results = [];

    for (var lineItems in [1, 5, 10, 20, 50]) {
      final invoice = createTestInvoice(lineItems);
      final stopwatch = Stopwatch()..start();

      await generatePdf(invoice);

      results.add({
        'line_items': lineItems,
        'duration_ms': stopwatch.elapsedMilliseconds,
      });
    }

    return {
      'results': results,
      'p50': calculatePercentile(results, 50),
      'p95': calculatePercentile(results, 95),
    };
  }
}

// Step 2: Identify bottleneck
// Found: Font loading took 3.2s on first generation

// Step 3: Test solutions
// Solution A: Cache fonts (reduced to 0.8s)
// Solution B: Async font loading (reduced to 0.5s)
// Solution C: Pre-compiled PDF template (reduced to 0.3s)
```

**Findings:**
1. Font loading accounted for 64% of generation time
2. No caching was implemented
3. Fonts loaded synchronously on every generation

**Recommendations:**
| Priority | Solution | Impact | Effort |
|----------|----------|--------|--------|
| High | Implement font caching | -80% time | Low |
| Medium | Pre-load fonts on app start | -90% time | Medium |
| Low | Use system fonts | -95% time | High (design impact) |

**Outcome:**
Implemented font caching, reduced average generation time from 5.2s to 0.9s (83% improvement).

**Lessons Learned:**
- Profile before optimizing
- Test with realistic data sizes
- Consider user experience impact (0.9s still felt slow)
- Follow-up: Added progress indicator to improve perceived performance

---

### Case Study 2: BTW Declaration Error Rate Investigation

**Context:** 15% of automated BTW declarations failed validation by Digipoort.

**Research Objective:**
Identify common error patterns and prevent future failures.

**Methodology:**
1. **Error Log Analysis** - Analyzed 500 failed submissions
2. **Pattern Recognition** - Categorized failure types
3. **Root Cause Analysis** - 5 Whys for each category
4. **Validation** - Interviewed 3 affected accountants

**Research Data Collection:**

```php
class BtwErrorResearch
{
    public function analyzeFailures()
    {
        // Collect all failures from last quarter
        $failures = BtwSubmission::where('status', 'failed')
            ->where('created_at', '>=', now()->subMonths(3))
            ->with('company', 'errorLogs')
            ->get();

        // Categorize errors
        $categories = $failures->groupBy(function ($submission) {
            return $this->categorizeError($submission->error_message);
        });

        return [
            'total_failures' => $failures->count(),
            'categories' => $categories->map->count(),
            'top_errors' => $categories->sortByDesc('count')->take(5),
            'affected_companies' => $failures->pluck('company_id')->unique()->count(),
        ];
    }
}
```

**Findings:**

| Error Category | Count | % | Root Cause |
|----------------|-------|---|------------|
| Invalid BTW number format | 42 | 28% | Input validation missing |
| Rounding errors in totals | 38 | 25% | Floating point arithmetic |
| Missing required fields | 25 | 17% | Incomplete migration data |
| Invalid period dates | 20 | 13% | Timezone handling |
| Other | 25 | 17% | Various |

**Deep Dive: Rounding Errors**

```php
// ❌ PROBLEM: Floating point precision
$lineTotal = 0;
foreach ($lines as $line) {
    $lineTotal += $line->amount * (1 + $line->vat_rate);
}
// Result: €121.000000000001 vs expected €121.00

// ✅ SOLUTION: Monetary precision
use Brick\Money\Money;

$lineTotal = Money::zero('EUR');
foreach ($lines as $line) {
    $amount = Money::of($line->amount, 'EUR');
    $vatAmount = $amount->multipliedBy($line->vat_rate, RoundingMode::HALF_UP);
    $lineTotal = $lineTotal->plus($amount)->plus($vatAmount);
}
// Result: Exactly €121.00
```

**Recommendations Implemented:**
1. Add input validation for BTW numbers using official format (NL123456789B01)
2. Migrate to Money library for all financial calculations
3. Add pre-submission validation with same rules as Digipoort
4. Implement comprehensive error messages for users

**Outcome:**
Failure rate reduced from 15% to 2.3% within one quarter.

---

### Case Study 3: Flutter App Offline Sync Conflict Analysis

**Context:** Users reported data conflicts when syncing offline changes.

**Research Objective:**
Understand conflict patterns and improve conflict resolution strategy.

**Methodology:**
1. **Data Mining** - Analyzed sync logs from 1,000 active users
2. **User Interviews** - Spoke with 5 users who experienced frequent conflicts
3. **Workflow Analysis** - Mapped common usage patterns
4. **A/B Testing** - Tested 3 conflict resolution strategies

**Key Research Questions:**
1. What types of entities have the most conflicts?
2. When do conflicts occur (time between offline and sync)?
3. What fields are most often in conflict?
4. How do users currently resolve conflicts?

**Data Analysis:**

```dart
// Conflict pattern analysis
class SyncConflictResearch {
  Map<String, dynamic> analyzeConflicts(List<SyncConflict> conflicts) {
    return {
      'by_entity_type': conflicts
          .groupBy((c) => c.entityType)
          .mapValues((list) => list.length),

      'by_field': conflicts
          .expand((c) => c.conflictedFields)
          .groupBy((field) => field)
          .mapValues((list) => list.length),

      'time_offline': conflicts.map((c) {
        return c.serverUpdatedAt.difference(c.localUpdatedAt).inHours;
      }).average(),

      'resolution_methods': conflicts
          .where((c) => c.resolved)
          .groupBy((c) => c.resolutionMethod)
          .mapValues((list) => list.length),
    };
  }
}
```

**Findings:**

**Conflict Frequency by Entity:**
1. Invoices: 45% (most conflicts on line items)
2. Expenses: 30% (conflicts on approval status)
3. Time entries: 20% (concurrent editing)
4. Other: 5%

**Average Time Offline:** 4.2 hours (median: 2.1 hours)

**User Behavior:**
- 68% chose "keep server version"
- 22% chose "keep local version"
- 10% manually merged changes

**Recommendations:**
1. Implement field-level conflict resolution (not entity-level)
2. Auto-merge non-conflicting fields
3. Highlight specific conflicted fields in UI
4. Add "undo sync" feature for 24 hours

**Implementation Example:**

```dart
class SmartConflictResolver {
  ResolvedEntity resolveConflict(LocalEntity local, ServerEntity server) {
    final resolved = server.copy();

    // Auto-merge: If fields don't overlap, merge both
    for (var field in local.modifiedFields) {
      if (!server.modifiedFields.contains(field)) {
        resolved.setField(field, local.getField(field));
      }
    }

    // Conflict: If same field modified, flag for user decision
    final conflictedFields = local.modifiedFields
        .where((f) => server.modifiedFields.contains(f))
        .toList();

    if (conflictedFields.isNotEmpty) {
      return ConflictRequiresResolution(
        resolved: resolved,
        local: local,
        server: server,
        conflictedFields: conflictedFields,
      );
    }

    return AutoResolvedEntity(resolved);
  }
}
```

**Outcome:**
- Conflicts requiring user intervention reduced by 73%
- User satisfaction with sync increased from 6.2 to 8.7/10
- Average conflict resolution time reduced from 3 minutes to 30 seconds

## 25 Additional Research Tips

### Data Quality
1. **Always verify primary sources** - Don't rely on citations; read original sources
2. **Date-stamp all research** - Information ages; know when it was valid
3. **Document null results** - What you didn't find is also valuable

### Efficiency
4. **Use keyboard shortcuts** - Save hours over time with search/navigation shortcuts
5. **Template everything** - Research notes, reports, analysis frameworks
6. **Batch similar tasks** - Group searching, reading, and writing separately
7. **Use RSS/alerts** - Let information come to you for ongoing topics

### Analysis
8. **Visualize data early** - Charts often reveal patterns faster than tables
9. **Challenge every assumption** - Ask "How do we know this is true?"
10. **Consider the counterfactual** - What if the opposite were true?
11. **Quantify uncertainty** - Use confidence levels, not just conclusions

### Communication
12. **Lead with the answer** - Put conclusions first, then supporting evidence
13. **Use the "So what?" test** - Every finding should have clear implications
14. **Write for scanners** - Use headers, bullets, and bold for key points
15. **Include limitations honestly** - Credibility comes from acknowledging gaps

### Methodology
16. **Triangulate findings** - Use multiple methods to verify important conclusions
17. **Keep an audit trail** - Document how you reached each conclusion
18. **Separate facts from interpretations** - Be clear about what's data vs. opinion
19. **Iterate quickly** - Rough drafts with feedback beat perfect isolation

### Tools
20. **Master one tool deeply** - Expertise in one tool beats superficial knowledge of many
21. **Automate repetitive research** - Scripts for common data collection tasks
22. **Use version control** - Track changes to research documents
23. **Backup continuously** - Research is irreplaceable; data loss is preventable

### Mindset
24. **Stay curious, not attached** - Follow evidence, not expectations
25. **Take breaks for insight** - Complex problems often solve in the background

## Measurable Outcomes & Success Criteria

### Research Skill Competency Levels

Use these criteria to assess research competency:

#### Level 1: Basic (Beginner)
**Can you:**
- [ ] Define a clear research question using FINERMAPS criteria
- [ ] Identify at least 3 credible sources for a topic
- [ ] Distinguish between primary and secondary sources
- [ ] Take organized notes during research
- [ ] Complete simple research tasks in under 2 hours

**Verification Test:**
```bash
# Task: Research BTW rates for 2025
# Success: Find official rates, source URL, effective dates
# Time limit: 30 minutes
```

**Expected Outcome:**
Can find and verify basic factual information independently.

#### Level 2: Intermediate (Practitioner)
**Can you:**
- [ ] Apply ERP model (Explore-Refine-Produce) to research projects
- [ ] Use Boolean search operators effectively
- [ ] Evaluate sources using CRAAP test (scoring 15+)
- [ ] Cross-reference findings across 3+ independent sources
- [ ] Write structured research reports using IMRaD format
- [ ] Complete moderate research tasks in 4-8 hours

**Verification Test:**
```bash
# Task: Investigate why invoice PDF generation is slow
# Success: Identify root cause, propose 3 solutions with trade-offs
# Time limit: 4 hours
# Deliverable: 2-page technical report
```

**Expected Outcome:**
Can independently research technical problems and produce actionable reports.

#### Level 3: Advanced (Expert)
**Can you:**
- [ ] Design mixed-methods research combining qualitative and quantitative
- [ ] Conduct root cause analysis using 5 Whys method
- [ ] Perform comparative analysis with weighted scoring matrices
- [ ] Create research blueprints for complex multi-week projects
- [ ] Identify and mitigate research biases (confirmation, recency)
- [ ] Mentor others in research methodology
- [ ] Complete complex research projects in 2-4 weeks

**Verification Test:**
```bash
# Task: Research and recommend caching strategy for Boekhouder API
# Success: Comparative analysis of 3+ approaches, performance projections,
#          implementation roadmap, risk assessment
# Time limit: 1 week
# Deliverable: 10-page strategic recommendation
```

**Expected Outcome:**
Can lead research initiatives and make strategic recommendations with confidence.

### Research Quality Metrics

Track these metrics to measure research effectiveness:

#### Process Metrics

```php
class ResearchMetrics
{
    // Time efficiency
    public function calculateResearchVelocity()
    {
        return [
            'time_to_first_insight' => '< 30 minutes', // Target
            'time_to_draft_report' => '< 80% of estimated time',
            'time_per_source_evaluated' => '< 15 minutes average',
        ];
    }

    // Source quality
    public function assessSourceQuality()
    {
        return [
            'primary_source_ratio' => '> 40%', // Target: 40%+ primary sources
            'avg_source_age' => '< 2 years', // For tech topics
            'avg_craap_score' => '> 18/25', // Quality threshold
            'sources_per_finding' => '> 2', // Cross-validation
        ];
    }

    // Output quality
    public function measureOutputQuality()
    {
        return [
            'report_structure_score' => '> 85%', // Checklist compliance
            'citation_accuracy' => '100%', // All claims cited
            'recommendation_actionability' => '> 90%', // Clear next steps
            'stakeholder_satisfaction' => '> 8/10', // Survey score
        ];
    }
}
```

#### Outcome Metrics

**For Investigation Reports:**
- [ ] Root cause identified: Yes/No
- [ ] Solution implemented within 30 days: Yes/No
- [ ] Problem recurrence rate: < 5%
- [ ] Implementation cost within ±20% of estimate

**For Comparative Analysis:**
- [ ] All evaluation criteria relevant: > 90%
- [ ] Recommendation adopted: Yes/No
- [ ] Actual performance vs. projected: ±15%
- [ ] Decision confidence: > 8/10

**For Compliance Research:**
- [ ] Regulatory accuracy: 100%
- [ ] Implementation passes audit: Yes/No
- [ ] Zero compliance violations: Yes
- [ ] Legal review approval: Yes

### Self-Assessment Checklist

After completing a research project, score yourself:

**Planning (20 points)**
- [ ] (5) Clear research question defined
- [ ] (5) Methodology appropriate for question type
- [ ] (5) Timeline realistic and met
- [ ] (5) Success criteria defined upfront

**Execution (40 points)**
- [ ] (10) Sufficient sources consulted (minimum 5-7)
- [ ] (10) Sources properly evaluated and cited
- [ ] (10) Data collected systematically
- [ ] (10) Analysis methods applied correctly

**Output (40 points)**
- [ ] (10) Report structure clear and logical
- [ ] (10) Findings supported by evidence
- [ ] (10) Recommendations specific and actionable
- [ ] (10) Limitations acknowledged

**Total Score:**
- **80-100:** Excellent research
- **60-79:** Good research, minor improvements needed
- **40-59:** Adequate research, significant gaps
- **< 40:** Poor research, major revision required

### Skill Progression Path

**Beginner → Intermediate (3-6 months)**
- Complete 10+ simple research tasks
- Read 2-3 books on research methodology
- Practice source evaluation on 50+ sources
- Write 5+ structured reports

**Intermediate → Advanced (6-12 months)**
- Lead 5+ research projects independently
- Conduct 2+ mixed-methods studies
- Mentor 2+ junior researchers
- Publish/present research findings

**Practice Exercises:**

1. **Speed Drill:** Research BTW rates for last 5 years (30 min)
2. **Depth Drill:** Investigate Laravel query performance optimization (4 hours)
3. **Breadth Drill:** Compare 5 payment gateway options (8 hours)
4. **Synthesis Drill:** Analyze Boekhouder user feedback trends (2 days)

## Integration with Related Skills

### Integration Workflows

#### Workflow 1: Security Vulnerability Research

**Scenario:** Discovered potential SQL injection vulnerability during code review

**Combined Skills:** `research-analysis` + `security-expert`

**Process:**
1. **Research Phase** (this skill)
   - Define research question: "Is this endpoint vulnerable to SQL injection?"
   - Collect evidence: Test cases, code analysis, similar CVEs
   - Document findings systematically

2. **Security Analysis Phase** (`security-expert`)
   - Apply OWASP testing methodology
   - Perform penetration testing
   - Assess impact and severity (CVSS scoring)

3. **Reporting Phase** (this skill)
   - Write security report using structured format
   - Provide remediation recommendations
   - Document disclosure timeline

**Example Integration:**
```php
// Research: Investigate suspicious query
class SecurityResearch
{
    public function investigateQuery()
    {
        // 1. Research Phase: Collect evidence
        $endpoint = '/api/invoices/search';
        $suspiciousCode = File::get('app/Http/Controllers/InvoiceController.php');

        // 2. Document findings
        $research = [
            'endpoint' => $endpoint,
            'vulnerability_type' => 'Potential SQL Injection',
            'code_location' => 'InvoiceController.php:45',
            'user_input' => '$request->query("search")',
            'query_construction' => 'Raw concatenation detected',
        ];

        // 3. Test hypothesis (from security-expert skill)
        $testCases = [
            "' OR '1'='1",
            "'; DROP TABLE invoices--",
            "' UNION SELECT * FROM users--",
        ];

        // 4. Verify vulnerability
        foreach ($testCases as $payload) {
            $result = $this->testPayload($endpoint, $payload);
            $research['test_results'][] = $result;
        }

        return $research;
    }
}
```

---

#### Workflow 2: Performance Optimization Research

**Scenario:** API response times exceed 500ms, investigate and optimize

**Combined Skills:** `research-analysis` + `performance-profiling` + `laravel-expert`

**Process:**
1. **Baseline Research** (this skill)
   - Collect performance metrics
   - Identify patterns (which endpoints, when, load levels)
   - Formulate hypotheses

2. **Deep Profiling** (`performance-profiling`)
   - Use Laravel Telescope/Debugbar
   - Analyze database queries (N+1 detection)
   - Profile memory usage

3. **Implementation** (`laravel-expert`)
   - Apply Laravel-specific optimizations
   - Implement caching strategies
   - Optimize Eloquent queries

**Example Integration:**
```php
// Step 1: Research - Quantitative analysis
$metrics = [
    'endpoints' => Invoice::getSlowEndpoints(), // > 500ms
    'sample_size' => 1000,
    'time_period' => '7 days',
];

// Step 2: Profile - Identify bottleneck
Telescope::recordQuery(function ($query) {
    if ($query->time > 100) {
        Log::warning('Slow query detected', [
            'sql' => $query->sql,
            'time' => $query->time,
            'bindings' => $query->bindings,
        ]);
    }
});

// Step 3: Implement - Apply Laravel optimization
// Before (N+1 problem):
// $invoices = Invoice::all();
// foreach ($invoices as $invoice) {
//     $invoice->client->name; // N+1 query
// }

// After (eager loading):
$invoices = Invoice::with(['client', 'lineItems'])->get();
```

---

#### Workflow 3: Dutch Compliance Research

**Scenario:** Research new BTW filing requirements for 2025

**Combined Skills:** `research-analysis` + `dutch-bookkeeping-expert` + `dutch-tax-compliance`

**Process:**
1. **Source Identification** (this skill)
   - Belastingdienst official announcements
   - Legal gazettes (Staatscourant)
   - Professional accounting bodies (NBA)

2. **Compliance Analysis** (`dutch-tax-compliance`)
   - Interpret legal requirements
   - Map to existing system capabilities
   - Identify gaps

3. **Implementation Planning** (`dutch-bookkeeping-expert`)
   - Update Boekhouder to comply
   - Modify BTW calculation logic
   - Update Digipoort integration

**Example Integration:**
```php
// Step 1: Research official sources
class BtwComplianceResearch2025
{
    public function researchNewRequirements()
    {
        return [
            'source' => 'Besluit van 12 december 2024, nr. 2024-12345',
            'effective_date' => '2025-01-01',
            'changes' => [
                'new_reporting_fields' => ['transaction_type', 'payment_method'],
                'validation_rules' => ['stricter_rounding', 'mandatory_iban'],
                'submission_format' => 'XML Schema v3.2',
            ],
            'legal_basis' => 'Wet OB 1968, art. 34a (nieuw)',
        ];
    }

    // Step 2: Gap analysis
    public function analyzeGaps()
    {
        $current = $this->getCurrentImplementation();
        $required = $this->researchNewRequirements();

        return [
            'missing_fields' => array_diff(
                $required['changes']['new_reporting_fields'],
                $current['supported_fields']
            ),
            'validation_updates_needed' => true,
            'schema_migration_required' => true,
        ];
    }

    // Step 3: Implementation roadmap
    public function createImplementationPlan()
    {
        return [
            'phase_1' => [
                'duration' => '2 weeks',
                'tasks' => [
                    'Add transaction_type field to invoices table',
                    'Update invoice creation forms (Laravel + Flutter)',
                    'Modify validation rules',
                ],
            ],
            'phase_2' => [
                'duration' => '1 week',
                'tasks' => [
                    'Update Digipoort XML generation',
                    'Add schema v3.2 validation',
                    'Test with Digipoort test environment',
                ],
            ],
            'phase_3' => [
                'duration' => '1 week',
                'tasks' => [
                    'User documentation',
                    'Migration guide for existing data',
                    'Production deployment',
                ],
            ],
        ];
    }
}
```

---

#### Workflow 4: Flutter App Feature Research

**Scenario:** Research offline sync improvements for Flutter app

**Combined Skills:** `research-analysis` + `flutter-expert` + `mobile-sync-patterns`

**Process:**
1. **User Research** (this skill)
   - Analyze sync conflict logs
   - Interview users experiencing issues
   - Benchmark against competitors

2. **Technical Research** (`flutter-expert`)
   - Evaluate Flutter offline packages (drift, hive, isar)
   - Test sync strategies (delta sync, full sync, conflict-free)
   - Performance testing on actual devices

3. **Implementation** (`mobile-sync-patterns`)
   - Design conflict resolution strategy
   - Implement operational transformation
   - Add sync progress indicators

**Example Integration:**
```dart
// Step 1: Research - User behavior analysis
class SyncResearch {
  Future<Map<String, dynamic>> analyzeUserBehavior() async {
    final syncLogs = await database.syncLogs.all();

    return {
      'avg_offline_duration': _calculateAvgOfflineDuration(syncLogs),
      'conflict_rate': _calculateConflictRate(syncLogs),
      'most_synced_entities': _getMostSyncedEntities(syncLogs),
      'peak_sync_times': _getPeakSyncTimes(syncLogs),
    };
  }

  // Step 2: Technical research - Test sync strategies
  Future<void> benchmarkSyncStrategies() async {
    final strategies = [
      DeltaSyncStrategy(),
      FullSyncStrategy(),
      HybridSyncStrategy(),
    ];

    for (var strategy in strategies) {
      final result = await _benchmarkStrategy(strategy);
      print('${strategy.name}: ${result.duration}ms, '
            '${result.dataTransferred}KB transferred');
    }
  }

  // Step 3: Implement chosen strategy
  Future<SyncResult> performSmartSync() async {
    final offlineDuration = DateTime.now().difference(lastSyncTime);

    // Research finding: Use delta sync if offline < 4 hours
    if (offlineDuration.inHours < 4) {
      return DeltaSyncStrategy().sync(since: lastSyncTime);
    } else {
      // Use full sync for longer offline periods
      return FullSyncStrategy().sync();
    }
  }
}
```

---

### Integration Point Summary

| Skill | When to Combine | Primary Use Case |
|-------|----------------|------------------|
| `security-expert` | Security research needed | Vulnerability investigation, threat analysis |
| `testing-expert` | Research testing strategies | Test coverage analysis, QA methodology |
| `performance-profiling` | Performance issues | Bottleneck identification, optimization |
| `dutch-bookkeeping-expert` | Dutch accounting topics | Compliance requirements, RGS codes |
| `dutch-tax-compliance` | Tax law research | BTW/VPB regulations, Digipoort |
| `laravel-expert` | Laravel-specific research | Package evaluation, architecture decisions |
| `flutter-expert` | Mobile app research | Plugin selection, state management |
| `database-mysql-expert` | Database optimization | Query analysis, index optimization |
| `api-design-expert` | API architecture research | REST best practices, versioning strategies |

### Cross-Skill Dependencies

**Prerequisites for effective research:**
- `laravel-ecosystem` - Understand Laravel context for backend research
- `flutter-basics` - Understand Flutter for mobile research
- `dutch-bookkeeping-basics` - Understand domain for compliance research

**Enhanced by:**
- `artificial-intelligence-expert` - Use AI for research acceleration
- `documentation-expert` - Write better research reports
- `project-management` - Plan larger research initiatives

## Version-Specific Research Guidance

### Laravel (Current Version: 10.x, Target: 11.x)

#### Researching Laravel Features

**Version Awareness:**
```php
// Always check Laravel version compatibility
// Boekhouder currently uses Laravel 10.x

// ❌ BAD - No version context
"How do I implement queue batching?"

// ✅ GOOD - Version-specific research
"How to implement queue batching in Laravel 10.x?"
"What changed in Laravel 11 queue batching?"
```

**Research Sources Priority:**
1. **Official Laravel Documentation** (laravel.com/docs/10.x)
   - Always start here for current version
2. **Laravel News** (laravel-news.com)
   - For recent updates and package releases
3. **Laracasts** (laracasts.com)
   - For in-depth tutorials (check video date)
4. **GitHub Issues/Discussions** (github.com/laravel/framework)
   - For known issues and workarounds

**Common Research Scenarios:**

**Scenario 1: Researching a Package**
```bash
# Research checklist for Laravel packages:
# 1. Check Laravel version compatibility
composer show vendor/package | grep "versions"

# 2. Check last update date (prefer < 6 months)
# 3. Check GitHub stars and issues
# 4. Test in local environment before production
# 5. Read CHANGELOG for breaking changes
```

**Scenario 2: Debugging Laravel Issue**
```php
// Research process for Laravel errors
class LaravelDebugResearch
{
    public function researchError(string $errorMessage)
    {
        // Step 1: Search Laravel docs
        $keywords = $this->extractKeywords($errorMessage);
        // e.g., "SQLSTATE[42000]" -> search "Laravel database connection"

        // Step 2: Check Laravel version-specific issues
        $version = app()->version(); // "10.48.10"
        // Search: "Laravel 10 {error}"

        // Step 3: Search GitHub issues
        // https://github.com/laravel/framework/issues?q={error}

        // Step 4: Test with Laravel Debugbar
        if (app()->environment('local')) {
            Debugbar::info('Research data', $context);
        }
    }
}
```

**Laravel Version Migration Research:**
```php
// Researching upgrade from Laravel 10 to 11
class UpgradeResearch
{
    public function researchUpgradePath()
    {
        return [
            'official_guide' => 'https://laravel.com/docs/11.x/upgrade',
            'breaking_changes' => $this->identifyBreakingChanges(),
            'deprecated_features' => $this->findDeprecated(),
            'new_features' => $this->listNewFeatures(),
            'estimated_effort' => $this->estimateUpgradeEffort(),
        ];
    }

    private function identifyBreakingChanges()
    {
        // Research Laravel 11 UPGRADE.md
        return [
            'minimum_php' => '8.2', // Up from 8.1
            'removed_features' => ['route:cache behavior changes'],
            'changed_defaults' => ['session driver', 'queue connection'],
        ];
    }
}
```

---

### Flutter (Current Version: 3.24.x)

#### Researching Flutter Features

**Version Awareness:**
```dart
// Always specify Flutter/Dart versions in research
// Boekhouder Flutter app uses Flutter 3.24.x, Dart 3.5

// ❌ BAD - No version context
"How to implement state management in Flutter?"

// ✅ GOOD - Version-specific
"Best state management for Flutter 3.24 (Provider vs Riverpod)?"
```

**Research Sources Priority:**
1. **Official Flutter Documentation** (flutter.dev/docs)
2. **Flutter API Reference** (api.flutter.dev)
3. **Pub.dev** (pub.dev) - Package documentation with version filtering
4. **Flutter Community** (discord.gg/flutter)
5. **GitHub Issues** (github.com/flutter/flutter)

**Common Research Scenarios:**

**Scenario 1: Package Selection**
```yaml
# Research checklist before adding Flutter package:
# 1. Check Flutter version compatibility
# 2. Check pub points score (prefer 130+)
# 3. Check null safety support
# 4. Check platform support (Android, iOS, Web)
# 5. Read package documentation and examples
# 6. Check last published date (prefer < 6 months)
# 7. Review GitHub issues for critical bugs

# Example: Researching PDF generation packages
# Options: pdf, printing, flutter_html_to_pdf
# Research criteria:
# - Performance (large invoices)
# - Dutch character support (ë, ü, etc.)
# - Custom fonts (Boekhouder branding)
# - Print preview support
```

**Scenario 2: Performance Research**
```dart
// Research Flutter performance issues
class FlutterPerformanceResearch {
  Future<void> researchFrameDrops() async {
    // Step 1: Enable performance overlay
    // In main.dart: showPerformanceOverlay: true

    // Step 2: Use Flutter DevTools timeline
    // flutter run --profile
    // Open DevTools -> Performance tab

    // Step 3: Identify slow builds
    final stopwatch = Stopwatch()..start();
    await buildWidget();
    print('Build time: ${stopwatch.elapsedMilliseconds}ms');

    // Step 4: Research Flutter 3.24 optimizations
    // - Impeller rendering engine (iOS default)
    // - SkSL shader warm-up
    // - Cached images
  }
}
```

**Flutter Version Migration Research:**
```dart
// Researching upgrade from Flutter 3.22 to 3.24
class FlutterUpgradeResearch {
  Map<String, dynamic> researchUpgrade() {
    return {
      'release_notes': 'https://docs.flutter.dev/release/release-notes/release-notes-3.24.0',
      'breaking_changes': identifyBreakingChanges(),
      'new_features': [
        'Impeller on Android (preview)',
        'Material 3 updates',
        'Performance improvements',
      ],
      'deprecated_apis': findDeprecatedAPIs(),
      'package_compatibility': checkPackageCompatibility(),
    };
  }

  List<String> checkPackageCompatibility() {
    // Research each package's Flutter 3.24 compatibility
    return [
      'provider: ^6.1.0 - ✅ Compatible',
      'dio: ^5.4.0 - ✅ Compatible',
      'drift: ^2.16.0 - ✅ Compatible',
      'pdf: ^3.10.0 - ⚠️ Test required',
    ];
  }
}
```

---

### Multi-Platform Research

**Researching for both Laravel and Flutter:**

**Scenario: API Contract Research**
```markdown
# Research API design for invoice endpoint

## Backend (Laravel)
- Resource Controllers vs API Resources
- Pagination: Laravel's built-in vs custom
- Validation: FormRequest vs manual
- Version: Laravel 10.x conventions

## Frontend (Flutter)
- HTTP client: Dio vs http package
- Serialization: json_serializable vs freezed
- State management: Provider pattern
- Version: Flutter 3.24 best practices

## Shared Concerns
- API versioning strategy (/api/v1)
- Error response format (JSON:API vs custom)
- Authentication (Sanctum token format)
- Date format (ISO 8601)
- Dutch locale support (nl_NL)
```

**Version Tracking Template:**
```markdown
# Feature Research Template

## Project Context
- **Laravel Version:** 10.48.x
- **Flutter Version:** 3.24.x
- **Dart Version:** 3.5.x
- **PHP Version:** 8.2
- **Date:** 2025-12-20

## Version Compatibility Check
- [ ] Laravel package supports 10.x
- [ ] Flutter package supports 3.24+
- [ ] Dart package supports 3.5+
- [ ] No deprecated APIs used
- [ ] Migration path documented

## Sources Consulted
1. [Source 1] - Version X.Y - Date
2. [Source 2] - Version X.Y - Date

## Findings
[Version-specific findings]

## Implementation Notes
[Version-specific considerations]
```

## Troubleshooting

### Problem: Can't Find Relevant Information
**Symptoms:** Searches return irrelevant results

**Solutions:**
```markdown
1. Vary search terminology
   - Use synonyms and related terms
   - Try different phrasings

2. Expand source types
   - Academic papers, forums, videos, podcasts
   - Industry reports, case studies

3. Follow citation trails
   - Who cited this source?
   - What sources did this cite?

4. Ask experts directly
   - Stack Overflow, Reddit, Discord communities
   - Professional networks
```

### Problem: Contradictory Information
**Symptoms:** Sources disagree on key points

**Solutions:**
```markdown
1. Check dates - newer may be more accurate
2. Check authority - who is more credible?
3. Check methodology - how was each conclusion reached?
4. Check context - are they answering the same question?
5. Document the contradiction - sometimes truth is nuanced
```

### Problem: Research Taking Too Long
**Symptoms:** Missing deadlines, scope expanding

**Solutions:**
```markdown
1. Set hard time limits per phase
2. Use the 80/20 rule - find key insights first
3. Define "good enough" criteria upfront
4. Park tangents in a "future research" list
5. Get early feedback to validate direction
```

## Checklists

### Pre-Research Checklist
- [ ] Clear research question defined
- [ ] Scope boundaries established
- [ ] Methodology chosen
- [ ] Timeline set
- [ ] Resources identified
- [ ] Success criteria defined

### During Research Checklist
- [ ] Sources documented properly
- [ ] Findings recorded systematically
- [ ] Questions tracked as they arise
- [ ] Time managed effectively
- [ ] Scope reviewed regularly

### Post-Research Checklist
- [ ] All sub-questions addressed
- [ ] Evidence supports conclusions
- [ ] Limitations acknowledged
- [ ] Recommendations actionable
- [ ] Report structured clearly
- [ ] Sources cited properly

## Resources

### Methodology Guides
- [Research Methods Knowledge Base](https://conjointly.com/kb/)
- [Harvard Research Guide](https://guides.library.harvard.edu/research)
- [Google Scholar](https://scholar.google.com/)

### Tools for Researchers
- **Zotero** - Citation and source management
- **Notion/Obsidian** - Knowledge management
- **Tableau/Power BI** - Data visualization
- **Miro/Mural** - Visual collaboration

### Further Reading
- "Research Design" by John Creswell
- "The Craft of Research" by Booth, Colomb, Williams
- "Thinking, Fast and Slow" by Daniel Kahneman

## Version History

### Version 2.0.0 (December 2025)
**Major Enhancement - 20 Pillars Compliance**

**New Sections Added:**
- ✅ Scope & Boundaries - Explicit in/out-of-scope definitions
- ✅ Security Considerations in Research - Data protection, GDPR, multi-tenancy safety
- ✅ Dutch Context & Localization - Nederlandse bronnen, terminology, legal citations
- ✅ Real-World Boekhouder Case Studies - 3 detailed case studies with metrics
- ✅ Measurable Outcomes & Success Criteria - Competency levels, quality metrics
- ✅ Version-Specific Guidance - Laravel 10.x and Flutter 3.24.x research tips
- ✅ Enhanced Integration Workflows - 4 detailed cross-skill workflows

**Improvements:**
- Added good vs bad code comparison examples throughout
- Enhanced integration section with practical workflows
- Added Dutch legal citation format
- Added research quality self-assessment checklist
- Added security checklists for sensitive data handling
- Improved code examples with Laravel/Flutter context

**Code Examples:** 15+ new examples (total: 20+)
**Security Guidance:** New comprehensive section with 7 subsections
**Dutch Localization:** Full section with official sources and terminology

### Version 1.0.0 (December 2025)
- Initial release
- ERP and Framework Analysis methods
- IMRaD and executive report formats
- 25 additional research tips
- Integration with Boekhouder skills
- Auto-trigger keyword detection

### Maintenance Triggers
- [ ] New research methodologies emerge
- [ ] Tool recommendations become outdated
- [ ] User feedback indicates gaps
- [ ] Laravel/Flutter version updates (check quarterly)
- [ ] Dutch tax rate changes (check annually in January)
- [ ] Annual review (December each year)

**Last Verified:** December 2025
**Next Review:** March 2026
