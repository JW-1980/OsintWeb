---
name: imagination-idea
description: Creative ideation skill for brainstorming, innovation, feature generation, and creative problem solving - with hallucination prevention and human supervisor requirements
version: 1.0.1
tags: [creativity, ideation, brainstorming, innovation, features, problem-solving]
trigger_keywords: [sk-imagination-idea, brainstorming, creative ideas, ideation session, innovation workshop, feature ideas, creative thinking, problem solving]
requires_human_approval: true
warning: This skill generates creative ideas that may not be factually grounded. All outputs require human supervisor review before implementation.
---

# Imagination and Idea Skill

Creative ideation skill for generating innovative ideas, brainstorming solutions, and creative problem-solving. This skill intentionally relaxes factual constraints to enable creative thinking.

## CRITICAL: Human Supervisor Requirement

**All outputs from this skill require human supervisor approval before implementation.**

This skill:
- Generates creative, speculative, and hypothetical content
- May propose ideas that have not been validated
- Produces concepts that need feasibility verification
- Creates solutions that require expert review

```markdown
## Required Workflow

1. Generate ideas (this skill)
2. Review with human supervisor
3. Validate feasibility (technical, legal, financial)
4. Approve or reject each idea
5. Only then proceed to implementation
```

## When to Use This Skill

- Brainstorming new features
- Solving complex problems creatively
- Generating product ideas
- Creating marketing concepts
- Exploring "what if" scenarios
- Ideating UX improvements
- Finding innovative approaches
- Breaking through creative blocks

## When NOT to Use This Skill

- Writing production code
- Making compliance decisions
- Creating financial reports
- Documenting existing functionality
- Making legal interpretations
- Processing real data

## Hallucination Prevention Framework

### Output Labeling

All outputs MUST be labeled with confidence levels:

```markdown
## Idea Confidence Levels

[CREATIVE] - Pure imagination, no factual basis
[SPECULATIVE] - Based on patterns but not verified
[INSPIRED] - Based on existing solutions, adapted
[GROUNDED] - Based on research, needs verification
```

### Required Disclaimers

Each idea section must include:

```markdown
---
**Supervisor Review Required:** This idea has not been validated for:
- [ ] Technical feasibility
- [ ] Legal/compliance implications
- [ ] Cost and resource requirements
- [ ] Security considerations
- [ ] Integration complexity
---
```

## Ideation Techniques

### 1. SCAMPER Method

```markdown
## SCAMPER Analysis for [Feature/Product]

### Substitute
- What can be replaced?
- What other materials, processes, or approaches?
- Who else can be involved?

### Combine
- What can be merged?
- What features can work together?
- What purposes can be combined?

### Adapt
- What else is like this?
- What ideas can be borrowed?
- What processes can be adapted?

### Modify/Magnify/Minimize
- What can be made larger or smaller?
- What can be enhanced?
- What can be streamlined?

### Put to Other Uses
- What new uses are possible?
- What other users could benefit?
- What other contexts apply?

### Eliminate
- What can be removed?
- What is unnecessary?
- What simplification is possible?

### Rearrange/Reverse
- What order can change?
- What can be inverted?
- What perspective shift helps?
```

### 2. Brainstorming Session

```markdown
## Brainstorming Session: [Topic]

### Rules
1. No criticism during generation
2. Quantity over quality initially
3. Build on others' ideas
4. Wild ideas welcome
5. Time-boxed (15-30 minutes)

### Warm-up Questions
- What if there were no constraints?
- What would a child suggest?
- What if we had unlimited budget?
- What would our competitors fear?
- What do users wish we could do?

### Idea Generation
[Generate 20+ ideas rapidly]

### Clustering
[Group similar ideas into themes]

### Evaluation (AFTER brainstorming)
[Apply feasibility filters with supervisor]
```

### 3. Six Thinking Hats

```markdown
## Six Hats Analysis: [Idea]

### White Hat (Facts)
- What do we know?
- What data exists?
- What information is missing?

### Red Hat (Emotions)
- What is our gut feeling?
- How do users feel about this?
- What emotional reactions arise?

### Black Hat (Caution)
- What are the risks?
- What could go wrong?
- What are the downsides?

### Yellow Hat (Optimism)
- What are the benefits?
- What value is created?
- What is the best case?

### Green Hat (Creativity)
- What alternatives exist?
- What new possibilities?
- What creative approaches?

### Blue Hat (Process)
- What is the next step?
- How do we decide?
- What is the timeline?
```

### 4. Feature Ideation Canvas

```markdown
## Feature Ideation Canvas

### Problem Statement
[Clear description of the problem to solve]

### User Personas Affected
[Who will benefit from this feature?]

### Wild Ideas (no filtering)
1. [Idea 1] [CREATIVE]
2. [Idea 2] [SPECULATIVE]
3. [Idea 3] [INSPIRED]
...

### Adjacent Inspiration
- From other industries: [ideas]
- From competitors: [ideas]
- From other products: [ideas]

### Technology Enablers
- What new tech could help?
- What APIs/services exist?
- What trends are emerging?

### Constraint Removal
- If we had no budget limit: [idea]
- If we had no time constraint: [idea]
- If we had unlimited developers: [idea]
- If regulations didn't exist: [idea]

### Synthesis
[Combine best elements into 3-5 refined concepts]

---
**Supervisor Review Required Before Proceeding**
---
```

## Creative Problem Solving

### Root Cause Ideation

```markdown
## Problem: [Description]

### Traditional Analysis
[Standard problem analysis]

### Creative Reframing
- What if this isn't actually a problem?
- What if this is a symptom of something else?
- What if we're solving the wrong problem?

### Opposite Thinking
- What would make this problem worse?
- If we wanted this to fail, what would we do?
- [Now reverse these for solutions]

### Analogy Mapping
- How does nature solve this?
- How do other industries handle this?
- What historical solutions exist?

### Random Stimulus
[Pick random word/image and force connections]
- Random word: [e.g., "bridge"]
- Connection to problem: [forced connection]
- Resulting idea: [new perspective]
```

### Innovation Sprint Template

```markdown
## Innovation Sprint: [Topic]

### Day 1: Understand
- Map the problem space
- Interview stakeholders
- Identify constraints
- Define success criteria

### Day 2: Diverge
- Generate 50+ ideas
- No filtering
- Use SCAMPER
- Explore extremes

### Day 3: Converge
- Vote on ideas
- Identify themes
- Select top 5
- Sketch concepts

### Day 4: Prototype
- Low-fidelity mockups
- Paper prototypes
- Click-through demos
- User flow sketches

### Day 5: Validate
- User testing
- Stakeholder review
- Feasibility check
- **SUPERVISOR APPROVAL**
```

## Boekhouder-Specific Ideation

### Feature Ideas Framework

```markdown
## Feature Brainstorm: [Area]

### User Pain Points
[What frustrates users?]

### Competitive Analysis
[What do others do better?]

### Dutch Market Specifics
[What unique needs exist for Dutch businesses?]

### Technology Opportunities
[What new tech could we leverage?]

### Ideas by Category

#### Automation Ideas [SPECULATIVE]
- [Idea 1]
- [Idea 2]

#### UX Improvement Ideas [INSPIRED]
- [Idea 1]
- [Idea 2]

#### Integration Ideas [GROUNDED]
- [Idea 1]
- [Idea 2]

#### "Moonshot" Ideas [CREATIVE]
- [Idea 1]
- [Idea 2]

---
**All ideas require validation:**
- [ ] Technical review by dev team
- [ ] Legal review for compliance
- [ ] Business review for ROI
- [ ] User research for demand
---
```

### Sample Idea Categories

```markdown
## Boekhouder Innovation Areas

### Accounting Automation
- AI-powered categorization
- Predictive cash flow
- Automated reconciliation
- Smart invoice matching

### User Experience
- Voice-controlled bookkeeping
- Conversational interface
- Gamified onboarding
- Proactive insights

### Integrations
- Bank API expansions
- E-commerce platforms
- POS systems
- Government portals

### Dutch Compliance
- Automated BTW returns
- KvK data sync
- Belastingdienst integration
- Audit trail export

### Mobile Innovation
- OCR receipt scanning
- Expense photo capture
- Push notifications
- Offline first features

---
**Supervisor Approval Required**
---
```

## Evaluation Framework

### Idea Scoring Matrix

```markdown
## Idea Evaluation: [Idea Name]

### Impact (1-5)
- User value: [score]
- Revenue potential: [score]
- Competitive advantage: [score]
- **Average: [x/5]**

### Effort (1-5)
- Development complexity: [score]
- Time to market: [score]
- Resource requirements: [score]
- **Average: [x/5]**

### Risk (1-5)
- Technical risk: [score]
- Compliance risk: [score]
- Market risk: [score]
- **Average: [x/5]**

### Priority Score
Impact / (Effort + Risk) = [score]

### Recommendation
[High/Medium/Low priority]
[Proceed to validation / Park for later / Discard]
```

### Feasibility Checklist

```markdown
## Post-Ideation Validation Checklist

### Technical Validation
- [ ] Architecture review completed
- [ ] Technology stack compatible
- [ ] Security implications assessed
- [ ] Performance impact evaluated
- [ ] Integration complexity understood

### Business Validation
- [ ] ROI analysis completed
- [ ] Resource requirements defined
- [ ] Timeline estimated
- [ ] Stakeholder buy-in obtained
- [ ] Budget approved

### Legal/Compliance Validation
- [ ] Dutch regulations checked
- [ ] GDPR compliance verified
- [ ] Industry standards met
- [ ] Contractual obligations reviewed

### User Validation
- [ ] User research conducted
- [ ] Prototype tested
- [ ] Feedback incorporated
- [ ] Demand confirmed

### SUPERVISOR SIGN-OFF
- [ ] All validations completed
- [ ] Risks accepted
- [ ] Approved for implementation
- Signed: ________________
- Date: ________________
```

## Output Templates

### Idea Proposal Template

```markdown
## Idea Proposal: [Title]

**Confidence Level:** [CREATIVE/SPECULATIVE/INSPIRED/GROUNDED]
**Status:** Awaiting Supervisor Review

### The Idea
[2-3 sentence description]

### Problem It Solves
[What user problem does this address?]

### How It Works
[High-level description - no implementation details]

### Potential Benefits
- [Benefit 1]
- [Benefit 2]
- [Benefit 3]

### Potential Risks
- [Risk 1]
- [Risk 2]

### Open Questions
- [Question requiring expert input]
- [Assumption needing validation]

### Next Steps (if approved)
1. [Validation step 1]
2. [Validation step 2]
3. [Implementation planning]

---
**DISCLAIMER:** This idea has not been validated for technical
feasibility, legal compliance, or business viability. Human supervisor
review is required before any implementation work begins.
---
```

## Best Practices

### DO:
- Label all ideas with confidence levels
- Include disclaimers about validation needs
- Encourage wild thinking during generation
- Separate generation from evaluation
- Document assumptions and questions
- Request supervisor approval explicitly

### DON'T:
- Present ideas as validated solutions
- Skip the human review step
- Implement ideas without verification
- Confuse creativity with accuracy
- Make compliance assumptions
- Promise feasibility of ideas

## Supervisor Review Protocol

```markdown
## Supervisor Review Checklist

Before approving any idea from this skill:

1. [ ] Verify the idea doesn't violate regulations
2. [ ] Check technical feasibility with dev team
3. [ ] Assess security implications
4. [ ] Confirm resource availability
5. [ ] Validate user demand exists
6. [ ] Review cost/benefit analysis
7. [ ] Check competitive landscape
8. [ ] Confirm timeline is realistic

Approval: ☐ Approved for Validation ☐ Needs More Info ☐ Rejected

Supervisor: ________________
Date: ________________
Comments: ________________
```

## Related Skills

- **project-management-expert** - Implementing approved ideas
- **laravel-expert** - Technical feasibility
- **flutter-dart-expert** - Mobile implementation
- **security-expert** - Security review
- **dutch-bookkeeping-expert** - Compliance check
