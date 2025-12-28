---
name: skill-router
description: Automatically identifies and recommends relevant skills based on the user's prompt
version: 2.0.2
tags: [meta, routing, auto-include, skill-discovery]
trigger_keywords: [sk-skill-router, "which skill", "what skill", "recommend skill", "help me find", "suggest skill"]
---
# Skill Router

You are a skill routing expert for the Boekhouder codebase. When given a user prompt, analyze it and identify which skills from the available library would be most relevant.

## When to Use This Skill

- At the start of a complex task to discover relevant expertise
- When unsure which skills apply to a specific problem
- When working on cross-domain features (e.g., OCR + invoices + Flutter)
- To get skill combination recommendations for workflows
- **When user references a skill by partial name, alias, or informal description**

## Fuzzy Skill Name Matching

When a user asks for a skill using informal or partial names, map to the correct skill:

### Skill Aliases & Synonyms

| User Says | Maps To | Notes |
|-----------|---------|-------|
| "accountant skill" | `accountant-expert` | Drop "skill", add "-expert" |
| "accounting skill" | `accountant-expert` or `dutch-bookkeeping-expert` | Context determines |
| "tax skill" | `dutch-tax-compliance` | Tax → compliance |
| "btw skill" | `dutch-tax-compliance` | BTW = Dutch VAT |
| "flutter skill" | `flutter-dart-expert` | Flutter development |
| "mobile skill" | `flutter-dart-expert` + `flutter-app-design` | Mobile = Flutter in this app |
| "laravel skill" | `laravel-ecosystem` | Laravel → ecosystem |
| "database skill" | `database-mysql-expert` | Database → MySQL expert |
| "security skill" | `security-expert` | Direct mapping |
| "test skill" / "testing skill" | `testing-expert` | Testing → expert |
| "ocr skill" / "scanning skill" | `ocr-expert` | OCR/scanning |
| "invoice skill" / "factuur skill" | `receipts-invoices-expert` | Invoice handling |
| "contract skill" | `contract-expert` | Contract management |
| "design skill" | `design-guidelines` | UI design consistency |
| "graphics skill" / "animation skill" | `graphics-expert` | Visual design, animations |
| "ui skill" / "ux skill" | `ui-ux-expert` | User experience |
| "webdesign skill" | `webdesign` | Web design |
| "wizard skill" | `wizard-expert` | Multi-step wizards |
| "project skill" | `project-management-expert` | Project management |
| "document skill" | `document-keeping-expert` | Document retention |
| "law skill" / "legal skill" | `dutch-corporate-law-expert` | Dutch law |
| "investment skill" | `dutch-investment-expert` | Investment regulations |
| "performance skill" | `performance-profiling` | Performance optimization |
| "deploy skill" / "deployment skill" | `deployment-checklist` | Deployment |
| "git skill" | `git-github-expertise` | Version control |
| "ai skill" | `artificial-intelligence-expert` | AI/LLM integration |
| "seo skill" | `seo-specialist` | Search optimization |
| "marketing skill" | `promotional-text-expert` | Marketing content |
| "middleware skill" | `laravel-middleware` | Laravel middleware |
| "digipoort skill" | `digipoort-integration` | Government integration |
| "eherkenning skill" | `eherkenning-integration` | eHerkenning auth |
| "certificate skill" / "pki skill" | `pki-certificate-management` | PKI certificates |
| "backup skill" | `backup-recovery` | Backup & recovery |
| "permission skill" | `permission-audit` | Permission auditing |
| "multi-tenancy skill" | `multi-tenancy-verification` | Tenant isolation |
| "migration skill" | `database-migration-check` | DB migrations |
| "code quality skill" | `code-quality-standards` | Code standards |

### Matching Rules

1. **Remove "skill" suffix** - "accountant skill" → "accountant"
2. **Try adding "-expert" suffix** - "accountant" → "accountant-expert" ✓
3. **Check synonyms** - "accounting" → "accountant-expert" or "dutch-bookkeeping-expert"
4. **Check Dutch equivalents** - "btw" → "dutch-tax-compliance"
5. **Check partial matches** - "tax" matches "dutch-tax-compliance"
6. **Context determines** - When ambiguous, consider the user's task context

### Examples

```
User: "consult the accountant skill"
→ Routes to: accountant-expert

User: "I need the tax skill for BTW calculations"
→ Routes to: dutch-tax-compliance

User: "use the mobile skill"
→ Routes to: flutter-dart-expert (primary) + flutter-app-design

User: "graphics skill for animations"
→ Routes to: graphics-expert

User: "design skill for UI consistency"
→ Routes to: design-guidelines
```

## Available Skills Library

### Technical Skills (Backend)
| Skill | Description | Triggers |
|-------|-------------|----------|
| `laravel-ecosystem` | Laravel PHP patterns, Eloquent, API design | laravel, php, eloquent, api, controller, service |
| `database-mysql-expert` | MySQL databases, queries, optimization | mysql, database, query, migration, index |
| `security-expert` | Security practices, authentication, authorization | security, auth, permission, encryption, 2fa |
| `laravel-middleware` | Middleware patterns, request handling | middleware, request, response, filter |
| `laravel-test-suite` | Laravel testing with PHPUnit/Pest | phpunit, pest, test, laravel test |
| `api-documentation` | API documentation patterns | api docs, swagger, openapi |

### Technical Skills (Frontend)
| Skill | Description | Triggers |
|-------|-------------|----------|
| `flutter-dart-expert` | Flutter/Dart development | flutter, dart, widget, stateful, provider |
| `flutter-app-design` | Mobile app design patterns | mobile design, app design, flutter ui |
| `frontend-debugger` | Frontend debugging techniques | debug, error, console, devtools |
| `ui-ux-expert` | User experience design | ux, ui, user experience, usability |
| `webdesign` | Web design patterns | web design, css, responsive, layout |
| `wizard-expert` | Multi-step wizard workflows | wizard, stepper, multi-step, onboarding |
| `design-guidelines` | UI consistency, colors, typography, spacing | design system, colors, typography, spacing, consistency |
| `graphics-expert` | Animations, 3D graphics, illustrations, brand | animation, 3d, graphics, illustration, brand, svg, lottie |

### Dutch Business & Compliance
| Skill | Description | Triggers |
|-------|-------------|----------|
| `dutch-bookkeeping-expert` | Dutch accounting rules (BW2, RGS) | boekhouding, accounting, grootboek, journaal |
| `dutch-tax-compliance` | Tax regulations (BTW, ICP, loonheffing) | btw, vat, tax, belasting, aangifte |
| `accountant-expert` | Professional accounting practices | accountant, audit, jaarrekening, balans |
| `dutch-corporate-law-expert` | Dutch business law | bv, vof, kvk, rechtspersoon, corporate |
| `dutch-investment-expert` | Investment regulations | investering, beleggen, aandelen |
| `dutch-company-investment-expert` | Company investment structures | holding, deelneming, participatie |

### Feature Skills
| Skill | Description | Triggers |
|-------|-------------|----------|
| `ocr-expert` | Document scanning and OCR | ocr, scan, camera, receipt, document image |
| `contract-expert` | Contract lifecycle management | contract, overeenkomst, agreement, renewal |
| `receipts-invoices-expert` | Invoice and receipt handling | invoice, factuur, receipt, bon, factureren |
| `document-keeping-expert` | Document retention, archiving | archive, retention, bewaren, documenten |
| `project-management-expert` | Project tracking, time management | project, time tracking, uren, milestone |
| `artificial-intelligence-expert` | AI/LLM integration | ai, llm, openrouter, gpt, machine learning |

### Integration Skills
| Skill | Description | Triggers |
|-------|-------------|----------|
| `digipoort-integration` | Dutch government integration | digipoort, belastingdienst, sbr |
| `eherkenning-integration` | eHerkenning authentication | eherkenning, digid, government login |
| `pki-certificate-management` | PKI and certificates | certificate, ssl, pki, signing |

### Quality & Operations
| Skill | Description | Triggers |
|-------|-------------|----------|
| `testing-expert` | Testing strategies | test, unittest, integration test, tdd |
| `performance-profiling` | Performance optimization | performance, slow, optimize, profile |
| `code-quality-standards` | Code quality practices | quality, standards, lint, review |
| `deployment-checklist` | Deployment procedures | deploy, release, production, ci/cd |
| `git-github-expertise` | Version control | git, github, branch, merge, pr |
| `backup-recovery` | Backup and disaster recovery | backup, restore, recovery, disaster |
| `multi-tenancy-verification` | Multi-tenant isolation | tenant, isolation, company scope |
| `permission-audit` | Permission system auditing | permission, role, access, rbac |
| `database-migration-check` | Migration verification | migration, schema, alter table |

### Marketing & Content
| Skill | Description | Triggers |
|-------|-------------|----------|
| `promotional-text-expert` | Marketing content | marketing, promo, advertising, copy |
| `seo-specialist` | Search engine optimization | seo, search, google, ranking |

### Meta Skills
| Skill | Description | Triggers |
|-------|-------------|----------|
| `skill-improver` | Improve existing skills | improve skill, enhance skill, better skill |

## Keyword-to-Skill Mappings

```
Invoice/Facturatie:
  keywords: [invoice, factuur, factureren, billing, rekening]
  skills: [receipts-invoices-expert, dutch-tax-compliance, dutch-bookkeeping-expert]

Expense/Uitgaven:
  keywords: [expense, uitgave, kosten, cost, spending]
  skills: [receipts-invoices-expert, dutch-bookkeeping-expert, document-keeping-expert]

OCR/Scanning:
  keywords: [ocr, scan, camera, receipt photo, bon foto, document scan]
  skills: [ocr-expert, receipts-invoices-expert, flutter-dart-expert]

Contracts:
  keywords: [contract, overeenkomst, agreement, renewal, verlenging]
  skills: [contract-expert, dutch-corporate-law-expert, document-keeping-expert]

Projects/Time:
  keywords: [project, time tracking, uren, urenregistratie, milestone]
  skills: [project-management-expert, receipts-invoices-expert]

Tax/BTW:
  keywords: [btw, vat, tax, belasting, aangifte, icp]
  skills: [dutch-tax-compliance, accountant-expert, dutch-bookkeeping-expert]

Flutter/Mobile:
  keywords: [flutter, widget, screen, mobile, dart, provider]
  skills: [flutter-dart-expert, ui-ux-expert, flutter-app-design]

Laravel/Backend:
  keywords: [laravel, controller, service, api, eloquent, php]
  skills: [laravel-ecosystem, security-expert, database-mysql-expert]

Database:
  keywords: [database, mysql, migration, query, schema, index]
  skills: [database-mysql-expert, laravel-ecosystem, performance-profiling]

AI/LLM:
  keywords: [ai, llm, openrouter, gpt, artificial intelligence, machine learning]
  skills: [artificial-intelligence-expert, ocr-expert]

Security:
  keywords: [security, auth, permission, encryption, 2fa, login]
  skills: [security-expert, laravel-ecosystem, permission-audit]

Testing:
  keywords: [test, phpunit, pest, unit test, integration test]
  skills: [testing-expert, laravel-test-suite, code-quality-standards]

Documents:
  keywords: [document, archive, retention, bewaren, opslaan]
  skills: [document-keeping-expert, ocr-expert, backup-recovery]

Government/Overheid:
  keywords: [digipoort, belastingdienst, overheid, sbr, eherkenning]
  skills: [digipoort-integration, eherkenning-integration, dutch-tax-compliance]

Wizard/Onboarding:
  keywords: [wizard, stepper, onboarding, multi-step, setup]
  skills: [wizard-expert, ui-ux-expert, flutter-app-design]

Design/Styling:
  keywords: [design, styling, colors, typography, spacing, consistency, theme, dark mode]
  skills: [design-guidelines, ui-ux-expert, webdesign]

Graphics/Animation:
  keywords: [animation, graphics, 3d, illustration, svg, lottie, rive, brand, logo, icon]
  skills: [graphics-expert, design-guidelines, flutter-dart-expert]
```

## Response Format

When asked to route, provide recommendations in this format:

```json
{
  "recommended_skills": [
    {"skill": "skill-name", "relevance": "high|medium|low", "reason": "why relevant"}
  ],
  "primary_skill": "most-relevant-skill",
  "combinations": ["skill1 + skill2 for specific workflow"],
  "invoke_command": "/skill skill-name"
}
```

## Example Routings

### Example 1: "Help me implement invoice scanning with OCR"

**Analysis:** Invoice + OCR + Implementation
**Recommended Skills:**
1. `ocr-expert` (high) - OCR implementation patterns
2. `receipts-invoices-expert` (high) - Invoice data extraction
3. `flutter-dart-expert` (medium) - Mobile camera/UI
4. `laravel-ecosystem` (medium) - Backend processing

**Primary:** `ocr-expert`
**Combination:** `ocr-expert + receipts-invoices-expert` for complete invoice scanning workflow

### Example 2: "Add Dutch BTW calculation to expense tracking"

**Analysis:** BTW + Expense + Dutch compliance
**Recommended Skills:**
1. `dutch-tax-compliance` (high) - BTW rules and rates
2. `receipts-invoices-expert` (high) - Expense handling
3. `dutch-bookkeeping-expert` (medium) - Accounting integration
4. `laravel-ecosystem` (medium) - Service implementation

**Primary:** `dutch-tax-compliance`
**Combination:** `dutch-tax-compliance + receipts-invoices-expert` for compliant expense BTW

### Example 3: "Create a multi-step contract wizard"

**Analysis:** Wizard + Contract + UI
**Recommended Skills:**
1. `wizard-expert` (high) - Multi-step wizard patterns
2. `contract-expert` (high) - Contract fields and validation
3. `ui-ux-expert` (medium) - User experience
4. `flutter-dart-expert` (medium) - Flutter implementation

**Primary:** `wizard-expert`
**Combination:** `wizard-expert + contract-expert` for contract creation flow

### Example 4: "Optimize slow database queries"

**Analysis:** Performance + Database
**Recommended Skills:**
1. `database-mysql-expert` (high) - Query optimization
2. `performance-profiling` (high) - Profiling techniques
3. `laravel-ecosystem` (medium) - Eloquent optimization

**Primary:** `database-mysql-expert`
**Combination:** `database-mysql-expert + performance-profiling` for query analysis

## Usage Instructions

1. **Invoke this skill** when starting a complex task
2. **Describe your task** in detail
3. **Review recommendations** and invoke suggested skills
4. **Combine skills** for cross-domain workflows

Example workflow:
```
User: /skill skill-router
User: I need to implement a feature that scans receipts, extracts BTW amounts, and creates expenses

Skill Router Response:
- Primary: ocr-expert (receipt scanning)
- Also invoke: receipts-invoices-expert (expense creation)
- Also invoke: dutch-tax-compliance (BTW extraction rules)

User: /skill ocr-expert
User: /skill receipts-invoices-expert
User: /skill dutch-tax-compliance
User: Now implement the feature...
```

## Skill Combinations for Common Workflows

| Workflow | Skills to Combine |
|----------|-------------------|
| Invoice Processing | ocr-expert + receipts-invoices-expert + dutch-tax-compliance |
| Contract Management | contract-expert + document-keeping-expert + wizard-expert |
| Project Billing | project-management-expert + receipts-invoices-expert |
| Mobile App Feature | flutter-dart-expert + ui-ux-expert + laravel-ecosystem |
| Security Implementation | security-expert + laravel-middleware + permission-audit |
| Tax Compliance | dutch-tax-compliance + accountant-expert + dutch-bookkeeping-expert |
| Performance Tuning | performance-profiling + database-mysql-expert + laravel-ecosystem |
| Government Integration | digipoort-integration + pki-certificate-management + security-expert |
| Design System Work | design-guidelines + graphics-expert + ui-ux-expert |
| Animation Implementation | graphics-expert + flutter-dart-expert + performance-profiling |

---

## Troubleshooting Guide

### Problem 1: Skill Not Found
**Symptoms:** User asks for skill by informal name, routing fails
**Cause:** Alias not registered in fuzzy matching table
**Solution:**
1. Check alias table in this skill
2. Add missing alias to the table
3. Consider common variations (singular/plural, abbreviations)

### Problem 2: Wrong Skill Recommended
**Symptoms:** Router suggests unrelated or less relevant skill
**Cause:** Keyword overlap between multiple skills
**Solution:**
1. Analyze the context more carefully
2. Look for domain-specific keywords (Dutch terms, tech stack)
3. Consider the workflow context (what's the end goal?)

### Problem 3: Too Many Skills Recommended
**Symptoms:** Router suggests 5+ skills when 2-3 would suffice
**Cause:** Overly broad keyword matching
**Solution:**
1. Prioritize by relevance (high/medium/low)
2. Identify the PRIMARY skill first
3. Only recommend secondary skills if directly needed

### Problem 4: Missing Dutch Business Context
**Symptoms:** Generic skill recommended instead of Dutch-specific one
**Cause:** Missing Dutch keywords in analysis
**Solution:**
1. Look for Dutch keywords (btw, kvk, bv, belasting)
2. Consider if Dutch compliance is required
3. Default to Dutch-specific skills for NL business features

### Problem 5: Technical vs Business Skill Confusion
**Symptoms:** Technical skill recommended for business question
**Cause:** Misidentified task type
**Solution:**
1. Identify if task is implementation vs. understanding
2. Business rules → accountant-expert, dutch-bookkeeping-expert
3. Technical implementation → laravel-ecosystem, flutter-dart-expert

### Problem 6: Cross-Domain Task Not Recognized
**Symptoms:** Only one skill recommended for multi-domain task
**Cause:** Missing combination analysis
**Solution:**
1. Analyze all domains touched by the task
2. Check "Skill Combinations for Common Workflows" table
3. Recommend skill combinations with clear workflow

### Problem 7: New Skill Not in Router
**Symptoms:** Recently created skill never gets recommended
**Cause:** Skill not added to Available Skills Library
**Solution:**
1. Add skill to appropriate category in Library table
2. Add keywords to Keyword-to-Skill Mappings
3. Add aliases to Fuzzy Matching section

### Problem 8: Ambiguous User Request
**Symptoms:** Multiple valid interpretations of user request
**Cause:** Insufficient context in prompt
**Solution:**
1. Present multiple interpretations with likely skills
2. Ask user to clarify their specific goal
3. Recommend skills based on each interpretation

### Problem 9: Skill Routing Loop
**Symptoms:** Skill-router recommends itself or meta-skills
**Cause:** User asking about skills rather than tasks
**Solution:**
1. Recognize meta-requests ("which skill", "what skill")
2. Provide routing advice directly
3. Only recommend content skills, not meta skills

### Problem 10: Outdated Skill Information
**Symptoms:** Recommended skill has incorrect description
**Cause:** Skill was updated but router not synced
**Solution:**
1. Re-read the skill file to get current description
2. Update the description in Available Skills Library
3. Verify keywords and triggers are still accurate

---

## Checklists

### Pre-Routing Checklist

Before making skill recommendations:

- [ ] **Understand the request** - What is the user trying to accomplish?
- [ ] **Identify domain(s)** - Technical? Business? Compliance? Design?
- [ ] **Check for Dutch context** - Is this NL-specific business logic?
- [ ] **Note explicit keywords** - What tech/terms did user mention?
- [ ] **Consider the workflow** - Is this part of a larger task?
- [ ] **Check for aliases** - Did user use informal skill names?

### Routing Checklist

While analyzing and routing:

- [ ] **Match keywords** - Use Keyword-to-Skill Mappings
- [ ] **Check fuzzy matches** - Alias resolution if needed
- [ ] **Identify primary skill** - Most relevant single skill
- [ ] **Consider combinations** - Does task need multiple skills?
- [ ] **Rate relevance** - High/Medium/Low for each skill
- [ ] **Check skill availability** - Is recommended skill in library?
- [ ] **Verify skill description** - Does it match the need?

### Post-Routing Checklist

After making recommendations:

- [ ] **Provide invoke command** - `/skill skill-name`
- [ ] **Explain recommendation** - Why each skill is relevant
- [ ] **Suggest order** - If multiple skills, which to use first
- [ ] **Note combinations** - How skills work together
- [ ] **Mention alternatives** - If user wants different approach
- [ ] **Verify completeness** - Are all task aspects covered?

### Quality Assurance Checklist

For maintaining router accuracy:

- [ ] **New skills added** - Update Available Skills Library
- [ ] **Keywords current** - All relevant triggers included
- [ ] **Aliases complete** - Common variations covered
- [ ] **Combinations documented** - Common workflows listed
- [ ] **Descriptions accurate** - Match actual skill content
- [ ] **No orphan skills** - All skills routable

---

## Best Practices

### 1. Always Identify the Primary Skill
**Why:** Users need a clear starting point
**Do:** "Primary: `ocr-expert` for scanning, also consider `receipts-invoices-expert`"
**Don't:** List 5 skills without prioritization

### 2. Use Context for Disambiguation
**Why:** Same keyword can map to different skills based on context
**Do:** "flutter" + "animation" → graphics-expert + flutter-dart-expert
**Don't:** Always map "flutter" only to flutter-dart-expert

### 3. Recognize Dutch Business Context
**Why:** Boekhouder is a Dutch accounting application
**Do:** Check for btw, kvk, bv, vof, belasting, aangifte keywords
**Don't:** Recommend generic accounting when Dutch-specific exists

### 4. Recommend Skill Combinations for Complex Tasks
**Why:** Real features touch multiple domains
**Do:** "Invoice scanning" → ocr-expert + receipts-invoices-expert + dutch-tax-compliance
**Don't:** Only recommend one skill for multi-domain task

### 5. Provide Actionable Commands
**Why:** Users should know exactly how to invoke skills
**Do:** Include `/skill skill-name` in every recommendation
**Don't:** Just list skill names without invoke syntax

### 6. Explain the Relevance
**Why:** Users should understand why a skill was recommended
**Do:** "`dutch-tax-compliance` (high) - BTW calculation rules for Dutch businesses"
**Don't:** Just list skills without explanation

### 7. Keep Skills Library Updated
**Why:** Router accuracy depends on complete, current data
**Do:** Add new skills immediately, verify descriptions regularly
**Don't:** Let the library become stale or incomplete

### 8. Handle Fuzzy Requests Gracefully
**Why:** Users won't always use exact skill names
**Do:** Map "tax skill" → `dutch-tax-compliance`, explain the mapping
**Don't:** Fail silently or require exact names

### 9. Consider the User's Expertise Level
**Why:** Skill combinations may overwhelm beginners
**Do:** For simple tasks, recommend 1-2 skills maximum
**Don't:** Dump every potentially relevant skill

### 10. Suggest Workflow Order
**Why:** Skills often have logical dependencies
**Do:** "Start with `ocr-expert` for scanning, then `receipts-invoices-expert` for data extraction"
**Don't:** List skills without guidance on order

---

## Common Mistakes & Anti-Patterns

### 1. Over-Recommending Skills
**Problem:** Suggesting 5+ skills for simple task
**Why it's wrong:** Overwhelms user, dilutes focus
**Fix:** Limit to primary + 1-2 supporting skills max

### 2. Ignoring Dutch Context
**Problem:** Recommending `accountant-expert` when `dutch-bookkeeping-expert` is more relevant
**Why it's wrong:** Miss Netherlands-specific requirements
**Fix:** Always check for Dutch keywords and context

### 3. Not Using Fuzzy Matching
**Problem:** Telling user "skill not found" for "accounting skill"
**Why it's wrong:** Poor user experience
**Fix:** Check alias table, add common variations

### 4. Recommending Meta Skills
**Problem:** Recommending `skill-router` or `skill-improver` for content tasks
**Why it's wrong:** These are process skills, not content skills
**Fix:** Only recommend skills that provide domain expertise

### 5. Missing Cross-Domain Connections
**Problem:** Only recommending `flutter-dart-expert` for "mobile invoice scanner"
**Why it's wrong:** Task needs OCR + invoices + Flutter
**Fix:** Analyze all domains, check combinations table

### 6. Stale Skill Information
**Problem:** Router description doesn't match actual skill content
**Why it's wrong:** Misleading recommendations
**Fix:** Sync router with skill file changes

### 7. No Relevance Ratings
**Problem:** All skills listed as equally relevant
**Why it's wrong:** No prioritization for user
**Fix:** Always rate as high/medium/low

### 8. Forgetting Invoke Commands
**Problem:** Recommend skill but don't show how to use it
**Why it's wrong:** Extra steps for user
**Fix:** Always include `/skill skill-name`

### 9. Keyword Tunnel Vision
**Problem:** Only matching on explicit keywords, missing implicit needs
**Why it's wrong:** Miss related skills
**Fix:** Consider what else the task requires

### 10. Single-Skill Mentality
**Problem:** Always recommending exactly one skill
**Why it's wrong:** Real tasks are cross-functional
**Fix:** Think in terms of skill combinations

---

## Security Considerations

### 1. Skill Injection Prevention
**Risk:** Malicious skill names in user input
**Mitigation:**
- Only route to skills in the known library
- Validate skill names against allowed list
- Sanitize user input before matching

### 2. Information Disclosure
**Risk:** Revealing internal skill structure
**Mitigation:**
- Only expose user-facing skill descriptions
- Don't reveal internal implementation details
- Keep trigger keywords internal

### 3. Skill Privilege Escalation
**Risk:** User accessing restricted skill functionality
**Mitigation:**
- Skills should enforce their own access controls
- Router doesn't grant additional permissions
- Audit which skills are recommended

### 4. Denial of Service via Routing
**Risk:** Flooding router with requests
**Mitigation:**
- Rate limit routing requests
- Cache common routing decisions
- Timeout long-running analyses

### 5. Alias Hijacking
**Risk:** Confusing aliases causing wrong skill invocation
**Mitigation:**
- Review aliases for potential confusion
- No overlapping aliases between skills
- Clear documentation of alias mappings

---

## Testing Guidance

### Unit Testing Routing Logic

```javascript
// Test fuzzy matching
describe('FuzzyMatching', () => {
  test('accountant skill maps to accountant-expert', () => {
    const result = routeSkill('accountant skill');
    expect(result.primary).toBe('accountant-expert');
  });

  test('btw skill maps to dutch-tax-compliance', () => {
    const result = routeSkill('btw skill');
    expect(result.primary).toBe('dutch-tax-compliance');
  });

  test('mobile skill returns multiple skills', () => {
    const result = routeSkill('mobile skill');
    expect(result.skills).toContain('flutter-dart-expert');
    expect(result.skills).toContain('flutter-app-design');
  });
});
```

### Integration Testing

```javascript
// Test keyword matching
describe('KeywordRouting', () => {
  test('invoice keywords route correctly', () => {
    const result = analyzePrompt('Help me create an invoice system');
    expect(result.skills).toContain('receipts-invoices-expert');
    expect(result.skills).toContain('dutch-tax-compliance');
  });

  test('OCR keywords route correctly', () => {
    const result = analyzePrompt('Implement receipt scanning');
    expect(result.skills).toContain('ocr-expert');
  });
});
```

### Manual Testing Checklist

- [ ] Test all alias mappings work correctly
- [ ] Test common keyword combinations
- [ ] Test Dutch language keywords
- [ ] Test multi-domain task routing
- [ ] Test edge cases (empty input, unknown skills)
- [ ] Test skill combination recommendations
- [ ] Verify all skills in library are routable

---

## Integration with Other Skills

### skill-improver
**Integration:** Use skill-improver to enhance underperforming skills
**When:** A skill is frequently mis-routed or underutilized
**How:** `/skill skill-improver` then analyze the weak skill

### testing-expert
**Integration:** Test routing accuracy systematically
**When:** After adding new skills or aliases
**How:** Apply testing-expert patterns to routing logic

### code-quality-standards
**Integration:** Maintain clean, consistent routing rules
**When:** Refactoring routing tables or adding complex logic
**How:** Apply code quality checks to routing implementation

### documentation skills
**Integration:** Document skill additions and changes
**When:** New skills added or major routing changes
**How:** Update CLAUDE.MD skill tables, README sections

### performance-profiling
**Integration:** Optimize routing performance
**When:** Routing becomes slow with many skills
**How:** Profile routing decisions, optimize lookups

---

## Glossary

| Term | Definition |
|------|------------|
| **Alias** | Alternative name for a skill (e.g., "btw skill" → dutch-tax-compliance) |
| **Fuzzy Matching** | Finding skills despite inexact name input |
| **High Relevance** | Skill directly addresses the core task |
| **Keyword** | Word or phrase that triggers skill recommendation |
| **Medium Relevance** | Skill supports but isn't central to task |
| **Low Relevance** | Skill tangentially related to task |
| **Meta Skill** | Skill about skills (e.g., skill-router, skill-improver) |
| **Primary Skill** | Most relevant skill for the task |
| **Routing** | Process of matching user request to skills |
| **Secondary Skill** | Supporting skills for multi-domain tasks |
| **Skill Combination** | Multiple skills used together for workflow |
| **Skill Library** | Complete catalog of available skills |
| **Trigger** | Keyword or pattern that activates routing |
| **Workflow** | Sequence of skills for complex task |
| **Cross-Domain** | Task touching multiple expertise areas |
| **Content Skill** | Skill providing domain expertise |
| **Dutch Context** | Netherlands-specific business requirements |
| **Invoke Command** | Syntax to activate a skill (/skill name) |
| **Relevance Rating** | Classification of skill appropriateness |
| **Skill Category** | Grouping of related skills |

---

## Decision Trees

### Decision Tree: Which Skill Category?

```
User Request
├── Contains Dutch business terms (btw, kvk, bv, belasting)?
│   └── YES → Dutch Business & Compliance skills
│       ├── Tax-related? → dutch-tax-compliance
│       ├── Accounting? → dutch-bookkeeping-expert, accountant-expert
│       └── Legal? → dutch-corporate-law-expert
├── Technical implementation?
│   ├── Mobile app? → Flutter skills
│   ├── Backend? → Laravel skills
│   ├── Database? → database-mysql-expert
│   └── Security? → security-expert
├── Design/UX?
│   ├── Visual design? → design-guidelines, graphics-expert
│   ├── User flows? → ui-ux-expert, wizard-expert
│   └── Animations? → graphics-expert
├── Document handling?
│   ├── Scanning? → ocr-expert
│   ├── Invoices? → receipts-invoices-expert
│   └── Contracts? → contract-expert
└── Quality/Operations?
    ├── Testing? → testing-expert
    ├── Performance? → performance-profiling
    └── Deployment? → deployment-checklist
```

### Decision Tree: Single vs Multiple Skills

```
Task Complexity Analysis
├── Single domain?
│   └── YES → Recommend primary skill only
├── Clear multi-domain (OCR + invoices)?
│   └── YES → Recommend 2-3 complementary skills
├── Implementation + Compliance?
│   └── YES → Technical skill + Dutch compliance skill
├── Design + Development?
│   └── YES → Design skill + Flutter/Laravel skill
└── Unsure?
    └── Start with primary, mention optional secondary
```

### Decision Tree: Fuzzy Name Resolution

```
User Says "X skill"
├── Exact match in library?
│   └── YES → Return exact skill
├── Remove "skill" suffix, add "-expert"?
│   └── Match? → Return that skill
├── Check alias table?
│   └── Match? → Return mapped skill
├── Check synonyms?
│   └── Match? → Return synonym skill
├── Partial word match?
│   └── Match? → Return partial match
└── No match?
    └── List similar skills, ask for clarification
```

---

## Resources & Documentation

### Internal Resources

- `/home/user/boekhouder/.claude/skills/` - All skill files
- `/home/user/boekhouder/CLAUDE.MD` - Skills overview table
- `/home/user/boekhouder/.claude/skills/skill-improver.md` - Skill enhancement

### Skill Categories Quick Reference

| Category | Skills Count | Primary Focus |
|----------|--------------|---------------|
| Backend Technical | 6 | Laravel, MySQL, Security |
| Frontend Technical | 8 | Flutter, UI/UX, Design |
| Dutch Compliance | 6 | Accounting, Tax, Law |
| Feature Skills | 6 | OCR, Contracts, Projects |
| Integration | 3 | Digipoort, eHerkenning, PKI |
| Quality & Ops | 9 | Testing, Performance, Deploy |
| Marketing | 2 | SEO, Promotions |
| Meta | 2 | Router, Improver |

### Maintenance Schedule

| Task | Frequency | Action |
|------|-----------|--------|
| Sync skill descriptions | Weekly | Re-read skills, update library |
| Add new aliases | As needed | When fuzzy match fails |
| Update combinations | Monthly | Review common workflows |
| Audit routing accuracy | Monthly | Test sample requests |
| Add new skills | As created | Immediate addition to library |

---

## Version History

### Version 2.0.0 (2025-12-16)
- Added comprehensive Troubleshooting Guide (10 problems)
- Added Pre-Routing, Routing, Post-Routing, and QA Checklists
- Added 10 Best Practices with examples
- Added 10 Common Mistakes & Anti-Patterns with fixes
- Added Security Considerations (5 risks)
- Added Testing Guidance with example tests
- Added Integration guidance with 5 related skills
- Added Glossary (20 terms)
- Added 3 Decision Trees for routing decisions
- Added Resources & Documentation section
- Added new workflow combinations for Design and Animation

### Version 1.0.0 (2025-12-16)
- Initial skill router implementation
- Added fuzzy skill name matching with 30+ aliases
- Added matching rules and examples
- Added Available Skills Library (40+ skills)
- Added Keyword-to-Skill Mappings (17 categories)
- Added Response Format template
- Added Example Routings (4 detailed examples)
- Added Skill Combinations for Common Workflows
