---
name: claude-expert
description: Comprehensive expertise on Claude AI ecosystem, prompt engineering, models, tools, and best practices for optimal AI-assisted development
version: 1.0.2
tags: [claude, ai, prompting, models, claude-code, mcp, best-practices]
trigger_keywords: [sk-claude, claude, "claude ai", "prompt engineering", "claude code", mcp, anthropic, "claude expert", claude-expert]
---
# Claude Expert Skill

Comprehensive expertise for working effectively with Anthropic's Claude AI ecosystem, including Claude.ai, Claude Code, the API, and all related tools. This skill helps optimize prompts, select appropriate models, and leverage Claude's capabilities for maximum productivity.

## When to Use This Skill

- Writing or optimizing prompts for Claude
- Selecting the right Claude model for a task
- Using Claude Code effectively
- Implementing MCP integrations
- Troubleshooting Claude-related issues
- Understanding Claude's capabilities and limitations
- Building AI-assisted workflows

---

# PART 1: CLAUDE PRODUCTS & TOOLS

## 1.1 Claude.ai (Web Interface)

**Core Features:**
- **Web Search**: Real-time information retrieval with source citations
- **Artifacts**: Real-time code preview and execution with SVG/website rendering
- **Projects**: Workspace management with context retention (200K-500K tokens)
- **Skills**: Lightweight instruction sets for consistent task execution
- **Extended Thinking**: Toggle between rapid responses and deep reasoning

**Pricing Tiers:**
| Tier | Price | Usage | Features |
|------|-------|-------|----------|
| Free | $0 | 30 msgs/day | Basic features, 5-hour resets |
| Pro | $20/month | 5x Free | All models, unlimited Projects |
| Max | $100-200/month | 5-20x Pro | Extended thinking budget |

## 1.2 Claude Code (CLI Tool)

**Description**: Agentic coding tool integrated with terminal for natural language development.

**Installation:**
```bash
npm install -g @anthropic-ai/claude-code
```

**Key Capabilities:**
- Direct file editing and command execution
- Git workflows through natural language
- GitHub/GitLab integration (issues → code → tests → PRs)
- IDE integration (VS Code, Cursor, Windsurf, JetBrains)
- Background command execution (Ctrl-b)
- Custom subagents and hooks system
- MCP server integration

**Architecture Components:**

| Component | Purpose | Location |
|-----------|---------|----------|
| Skills | Instruction sets for tasks | `.claude/skills/` |
| Hooks | Lifecycle event handlers | `.claude/hooks/` |
| Commands | Custom slash commands | `.claude/commands/` |
| CLAUDE.md | Project context | Project root |

## 1.3 Claude API

**Base URL**: `https://api.anthropic.com/v1/`

**Key Features:**
- RESTful API with SDKs (Python, TypeScript, Java, Go)
- Streaming responses
- Tool use (function calling)
- Vision capabilities (image analysis)
- Extended thinking mode
- Batch API (50% discount for async processing)
- Prompt caching (cache hits at 0.1× cost)

## 1.4 Claude for Enterprise

**Enhanced Features:**
- 500K context window
- SSO, SCIM, audit logs, role-based permissions
- Claude Code integration for premium seats
- Compliance API for usage auditing
- Native GitHub, Jira, Confluence integrations

## 1.5 Model Context Protocol (MCP)

**Definition**: Open standard for secure AI-to-data connections

**Available Integrations:**
- Google Drive, Slack, GitHub, Git
- Postgres, Puppeteer, Chrome DevTools
- Jira, Confluence, Notion
- Custom enterprise tooling

---

# PART 2: CLAUDE MODELS

## 2.1 Model Comparison (December 2025)

| Model | Input $/M | Output $/M | Context | Best For |
|-------|-----------|------------|---------|----------|
| **Opus 4.5** | $5 | $25 | 200K | Enterprise research, complex reasoning |
| **Sonnet 4.5** | $3 | $15 | 200K-1M | Best coding (77.2% SWE-bench), agents |
| **Haiku 4.5** | $1 | $5 | 200K | Fast responses, everyday tasks |

## 2.2 Model Selection Guide

### Use Opus 4.5 When:
- Long-horizon autonomous tasks requiring sustained reasoning
- Multi-step execution and complex planning
- Enterprise research with massive context
- Maximum quality required regardless of cost

### Use Sonnet 4.5 When:
- Coding tasks (globally #1 at 77.2% SWE-bench)
- Frontend/UI development
- Agentic workflows and computer use
- Default for most professional tasks

### Use Haiku 4.5 When:
- Quick responses needed
- Cost optimization is priority
- Simple to moderate complexity tasks
- High-volume processing

## 2.3 Extended Context

**Standard**: 200,000 tokens (~150,000 words)
**Extended (Sonnet)**: 1,000,000 tokens
**Output Limit**: Up to 64,000 tokens per response

**Token Estimation**: 1 token ≈ 4 characters or 0.75 words (English)

---

# PART 3: PROMPT ENGINEERING - 25 TIPS

## Core Principles

### Tip 1: Be Clear and Explicit
```
❌ "Write something about invoices"
✅ "Write a 500-word explanation of Dutch invoice requirements for B2B transactions, including BTW rules"
```

### Tip 2: Use XML Tags for Structure
```xml
<task>Analyze the invoice processing code</task>
<context>This is a Laravel application using multi-tenancy</context>
<rules>
- Focus on SOLID principles
- Check for security issues
- Verify company isolation
</rules>
<output_format>Markdown with code examples</output_format>
```

### Tip 3: System Prompts for Roles
```
System: You are a senior Laravel developer with 10 years of experience in Dutch bookkeeping applications. You prioritize security, multi-tenancy isolation, and SOLID principles.
```

### Tip 4: Prefill Response Skeletons
```
Start your response with this JSON structure:
{"analysis": {"issues": [
```
Claude will complete the structure.

### Tip 5: Chain-of-Thought Prompting
```
Think step by step before answering. Structure your thinking in <thinking> tags, then provide your <answer>.
```

## Advanced Techniques

### Tip 6: Few-Shot Examples
Provide 1-3 examples showing desired input-output format:
```
Example 1:
Input: Invoice for €1000 with 21% BTW
Output: {"subtotal": 1000, "btw": 210, "total": 1210}

Now process: Invoice for €500 with 9% BTW
```

### Tip 7: Guide Initial Thinking
```
First analyze the security implications, then consider the performance impact, finally recommend the best approach.
```

### Tip 8: Enable Extended Thinking
For complex reasoning tasks, use extended thinking mode:
- 10/10 effectiveness for complex analysis
- 3/10 for simple queries (overkill)

### Tip 9: Context Placement
Place most important information at the **beginning** or **end** of prompts. Long middle sections receive less attention.

### Tip 10: Specify Output Format
```
Output as JSON with this exact schema:
{
  "status": "success|error",
  "data": {...},
  "errors": []
}
```

## Claude 4.x Specific Tips

### Tip 11: Manage Proactivity
Claude 4.x is efficient but may skip summaries:
```
By default, implement changes rather than only suggesting them.
After making changes, provide a brief summary of what was modified.
```

### Tip 12: Prevent Over-Engineering
```
Use the simplest solution that meets requirements.
Do not create abstractions for one-time operations.
Avoid adding features not explicitly requested.
```

### Tip 13: Enable Parallel Tool Calls
```
If you intend to call multiple tools and there are no dependencies, make all independent tool calls in parallel.
```

### Tip 14: Request Specific Behaviors
Claude 4.x follows instructions literally - explicitly request any "above and beyond" behaviors:
```
After completing the task, also verify the code compiles and run the relevant tests.
```

### Tip 15: Control Verbosity
```
Be concise. Limit explanations to 2-3 sentences maximum.
Focus on actionable information only.
```

## Optimization Tips

### Tip 16: Leverage Prompt Caching
Design prompts to reuse context for 0.1× cost on cache hits.

### Tip 17: Role Prompting
Transform Claude into a virtual expert:
```
You are a Dutch tax accountant specializing in BTW compliance for software companies.
```

### Tip 18: Combine Techniques
Mix XML tags + few-shot + CoT for best results.

### Tip 19: Test Edge Cases
Verify prompts work across boundary conditions.

### Tip 20: Remove Ambiguity
```
By "bank", I mean financial institution (not river bank).
By "period", I mean fiscal quarter (not punctuation).
```

## Quality Optimization

### Tip 21: Allow Uncertainty
```
If you're unsure, say so rather than guessing. It's better to ask for clarification.
```

### Tip 22: Request Source Citations
```
Cite specific files and line numbers for any code references.
```

### Tip 23: Iterative Refinement
Start simple, add complexity only when needed. Test each addition.

### Tip 24: One Task Per Prompt
Break complex requests into sequential steps rather than asking for 5+ things at once.

### Tip 25: Validate Before Submission
Checklist:
- [ ] Clear task definition
- [ ] Appropriate structure
- [ ] Examples provided (if needed)
- [ ] Output format specified
- [ ] Ambiguity removed

---

# PART 4: COMMON ERRORS & SOLUTIONS

## Error 1: Vague Prompts

**Symptom**: Generic, off-target responses

**Cause**: Insufficient detail about topic, format, tone

**Solution**:
```
❌ "Write a blog post"
✅ "Write a 500-word blog post for software developers explaining REST APIs using practical Laravel examples"
```

## Error 2: Multi-Task Overload

**Symptom**: Scattered, unfocused responses

**Cause**: Asking Claude to do 5+ things at once

**Solution**: One prompt = one job. Break complex requests into sequential steps.

## Error 3: No Examples Provided

**Symptom**: Output doesn't match expected style/format

**Cause**: Claude fills gaps with best guesses

**Solution**: Provide 1-3 concrete examples showing desired output.

## Error 4: Missing Output Thinking

**Symptom**: Poor reasoning on complex questions

**Cause**: CoT not explicitly requested

**Solution**: Always request explicit thinking: "Think step by step within `<thinking>` tags"

**Critical**: Without output, no actual thinking occurs!

## Error 5: Over-Reliance as Truth Source

**Symptom**: Hallucinations, confident incorrect answers

**Cause**: Treating Claude as infallible

**Solution**:
- Verify high-stakes information
- Ask Claude to cite sources
- Cross-reference critical facts

## Error 6: Exceeding Context Limits

**Symptom**: Truncation or rejection

**Cause**: Exceeding 200K token limit

**Solution**:
- Summarize content
- Use prompt caching
- Enable extended context for supported models

## Error 7: Rate Limiting (429 Errors)

**Symptom**: Request throttling

**Solution**:
```php
// Implement exponential backoff
$delays = [2, 4, 8, 16]; // seconds
foreach ($delays as $delay) {
    try {
        return $this->makeRequest();
    } catch (RateLimitException $e) {
        sleep($delay);
    }
}
```

## Error 8: Configuration Syntax Errors

**Symptom**: Claude Code auto-execution failures

**Cause**: Invalid JSON/YAML in config files

**Solution**: Validate syntax with linters before use.

## Error 9: Authentication Failures (401)

**Symptom**: Unauthorized errors

**Solution**:
1. Regenerate API key from Claude.ai → Settings → API Keys
2. Verify `CLAUDE_API_KEY` environment variable
3. Never commit API keys to version control

## Error 10: Inconsistent Output Quality

**Symptom**: Variable response quality

**Cause**: Ambiguous or inconsistent prompting

**Solution**:
- Use consistent prompt templates
- Provide examples
- Specify exact requirements

---

# PART 5: CLAUDE CODE BEST PRACTICES

## 5.1 Project Setup

### CLAUDE.md Configuration
```markdown
# Project: Boekhouder

## Architecture
- Laravel backend with Sanctum API
- Flutter mobile app
- Multi-tenant with company isolation

## Conventions
- Use Form Requests for validation
- Services for business logic
- Policies for authorization
- Always check company_id scope

## Testing Requirements
- Minimum 80% coverage
- Run tests before commits
```

### Hooks for Quality Gates

```bash
# .claude/hooks/pre-commit.sh
#!/bin/bash
# Block commits until tests pass

if ! php artisan test --stop-on-failure; then
    echo "❌ Tests failed. Commit blocked."
    exit 1
fi
echo "✅ Tests passed. Proceeding with commit."
```

## 5.2 Effective Commands

```bash
# Background long-running tasks
# Press Ctrl-b to run in background

# Check current tasks
/tasks

# Use custom skills
/skill skill-name

# View status
/statusline
```

## 5.3 MCP Integration

Connect Claude to external tools:
```json
{
  "mcpServers": {
    "github": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-github"]
    }
  }
}
```

## 5.4 Parallel Tool Calls

Explicitly enable in prompts:
```
When making multiple independent operations, execute them in parallel.
For example, read multiple files simultaneously if they don't depend on each other.
```

---

# PART 6: STRENGTHS & LIMITATIONS

## 6.1 What Claude Excels At

### Coding (Best in Class)
- 77.2% SWE-bench Verified (Sonnet 4.5)
- Clean, well-structured code
- Excellent for Laravel/PHP development
- Strong frontend/UI capabilities

### Long Context Processing
- Up to 1M token context window
- Entire codebases in single conversation
- No truncation, maintains context

### Reasoning & Analysis
- Multi-step complex reasoning
- Extended thinking for deep analysis
- Excellent for research and investigation

### Writing Quality
- Natural, human-like text
- Nuanced tone control
- Professional document generation

### Safety & Reliability
- Low hallucination rates
- Constitutional AI training
- High accuracy for business-critical applications

## 6.2 Limitations to Be Aware Of

### No Multi-Modal Generation
- Text output only
- Can analyze images but cannot create them

### Knowledge Boundaries
- Training cutoff dates vary by model
- Web search helps but doesn't replace deep knowledge

### Over-Cautious Responses
- Strict safety protocols sometimes restrictive
- May filter content unnecessarily

### Usage Limits
- Even paid tiers have caps
- Heavy users may hit limits

### Specialized Topics
- Strong on mainstream, weaker on niche domains
- May need expert verification for specialized advice

---

# PART 7: PROMPT TEMPLATES

## 7.1 Code Review Template

```xml
<task>Review this code for quality and issues</task>

<context>
Language: PHP/Laravel
Project: Multi-tenant Dutch bookkeeping application
Key concerns: Security, SOLID principles, multi-tenancy
</context>

<code>
[INSERT CODE HERE]
</code>

<review_focus>
1. Security vulnerabilities
2. SOLID principle violations
3. Multi-tenancy isolation
4. Performance issues
5. Code style consistency
</review_focus>

<output_format>
For each issue found:
- File:Line - Issue description
- Severity: Critical/High/Medium/Low
- Fix: Recommended solution
</output_format>
```

## 7.2 Feature Implementation Template

```xml
<task>Implement the following feature</task>

<requirements>
[DESCRIBE FEATURE]
</requirements>

<constraints>
- Follow existing patterns in codebase
- Maintain multi-tenancy isolation
- Include appropriate tests
- Use Form Requests for validation
- Use Policies for authorization
</constraints>

<context>
Related files: [LIST FILES]
Database tables: [LIST TABLES]
</context>

<output>
Provide:
1. Implementation plan
2. Database migrations (if needed)
3. Model changes
4. Controller/Service code
5. Tests
</output>
```

## 7.3 Bug Investigation Template

```xml
<task>Investigate and fix this bug</task>

<symptoms>
[DESCRIBE WHAT'S HAPPENING]
</symptoms>

<expected_behavior>
[DESCRIBE WHAT SHOULD HAPPEN]
</expected_behavior>

<context>
Environment: [local/staging/production]
Error messages: [PASTE ERRORS]
Recent changes: [LIST RELEVANT CHANGES]
</context>

<instructions>
1. First, identify the root cause
2. Explain why this is happening
3. Propose a fix
4. Consider edge cases
5. Suggest tests to prevent regression
</instructions>
```

---

# PART 8: PROMPT ANALYSIS & RECOMMENDATIONS

## Automatic Prompt Analysis

When this skill detects prompt improvement opportunities, it provides recommendations in this format:

### Analysis Output Template

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📊 PROMPT ANALYSIS & RECOMMENDATIONS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

## Prompt Quality Score: [X/10]

## Issues Detected:
1. [Issue description]
2. [Issue description]

## Recommendations:

### Prompting Improvements:
- [Specific recommendation]
- [Specific recommendation]

### Tool Usage Improvements:
- [Recommended tool/agent]
- [Better approach]

## Estimated Improvement KPIs:

| Metric | Current | Projected | Change |
|--------|---------|-----------|--------|
| Response Quality | X% | Y% | +Z% |
| Task Completion | X% | Y% | +Z% |
| Time Efficiency | X% | Y% | +Z% |
| Token Usage | X | Y | -Z% |
| Error Rate | X% | Y% | -Z% |

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

## KPI Definitions

| KPI | Description | How Measured |
|-----|-------------|--------------|
| Response Quality | How well output matches requirements | Completeness, accuracy, format |
| Task Completion | Percentage of task fully completed | Steps completed / Steps required |
| Time Efficiency | Speed of task completion | Iterations needed, back-and-forth |
| Token Usage | Efficiency of context usage | Tokens used vs. minimum needed |
| Error Rate | Frequency of errors/retries | Errors / Total attempts |

---

# PART 9: QUICK REFERENCE

## Model Selection Matrix

| Task Type | Recommended Model | Reasoning |
|-----------|-------------------|-----------|
| Simple questions | Haiku 4.5 | Fast, cost-effective |
| Code generation | Sonnet 4.5 | Best coding performance |
| Complex analysis | Opus 4.5 | Maximum reasoning |
| Document review | Sonnet 4.5 | Good balance |
| Quick edits | Haiku 4.5 | Speed priority |
| Architecture decisions | Opus 4.5 | Deep thinking |

## Prompt Checklist

Before sending a prompt:
- [ ] Clear, specific task definition
- [ ] Appropriate context provided
- [ ] Output format specified
- [ ] Examples included (if complex)
- [ ] Ambiguity removed
- [ ] One main task per prompt
- [ ] CoT requested (if complex reasoning)

## Common Patterns

### For Code Tasks
```
<task>...</task>
<context>...</context>
<constraints>...</constraints>
<output_format>...</output_format>
```

### For Analysis Tasks
```
<task>...</task>
<data>...</data>
<analysis_focus>...</analysis_focus>
Think step by step in <thinking> tags.
```

### For Writing Tasks
```
<task>...</task>
<audience>...</audience>
<tone>...</tone>
<length>...</length>
<examples>...</examples>
```

---

# PART 10: TROUBLESHOOTING

## Quick Fixes

| Problem | Solution |
|---------|----------|
| Generic responses | Add specific examples |
| Wrong format | Provide output template |
| Missing details | Use XML structure |
| Poor reasoning | Add CoT prompting |
| Over-engineering | Add simplicity constraints |
| Incomplete | Break into smaller tasks |
| Slow responses | Use Haiku for simple tasks |
| Context issues | Summarize and restart |

## Status Check

- **Claude Status**: https://status.claude.com
- **API Status**: Check response headers for rate limits
- **Claude Code**: `/status` command

---

# Resources

**Official Documentation:**
- [Claude Docs](https://docs.claude.com)
- [Claude Code Docs](https://code.claude.com/docs)
- [API Reference](https://docs.anthropic.com)
- [Prompt Engineering Guide](https://docs.claude.com/en/docs/build-with-claude/prompt-engineering)

**Community:**
- [Anthropic Discord](https://discord.gg/anthropic)
- [GitHub Issues](https://github.com/anthropics/claude-code/issues)

**Related Skills:**
- `artificial-intelligence-expert` - General AI/ML concepts
- `skill-router` - Finding the right skill
- `skill-improver` - Enhancing skills

---

## Version History

### Version 1.0.0 (2025-12-21)
- Initial release
- Comprehensive Claude ecosystem coverage
- 25 prompt engineering tips
- 10 common errors with solutions
- Prompt templates and analysis framework
- Model selection guide
- Quick reference section
