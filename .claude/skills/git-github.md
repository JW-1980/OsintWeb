---
name: git-github
description: Git version control and GitHub workflow expertise for professional development
version: 1.0.2
tags: [git, github, version-control, branching, merging, pr, workflow, ci-cd]
trigger_keywords: [sk-git-github, git, github, branch, merge, pr, commit, rebase, conflict, git-github-expertise]
related_skills: [deployment-checklist, code-quality-standards]
---
# Git and GitHub Expertise

This skill helps with Git version control operations, GitHub workflows, branch management, conflict resolution, and professional development practices.

## When to Use

- Creating and managing Git branches
- Resolving merge conflicts
- Creating and reviewing pull requests
- Setting up GitHub Actions
- Managing releases and tags
- Troubleshooting Git issues
- Implementing Git workflows
- Code collaboration best practices

## Git Fundamentals

### 1. Basic Workflow

**Daily development cycle**:
```bash
# 1. Update your local main branch
git checkout main
git pull origin main

# 2. Create feature branch
git checkout -b feature/add-invoice-export

# 3. Make changes and commit
git add .
git commit -m "feat: add invoice export to PDF functionality"

# 4. Push to remote
git push -u origin feature/add-invoice-export

# 5. Create pull request on GitHub
# (done via GitHub UI or gh CLI)

# 6. After PR is merged, update main
git checkout main
git pull origin main

# 7. Delete merged branch
git branch -d feature/add-invoice-export
git push origin --delete feature/add-invoice-export
```

### 2. Commit Message Convention

**Use conventional commits**:
```bash
# Format: <type>(<scope>): <subject>

# Types:
feat:     # New feature
fix:      # Bug fix
docs:     # Documentation only
style:    # Code style (formatting, semicolons, etc.)
refactor: # Code refactoring
perf:     # Performance improvement
test:     # Adding tests
chore:    # Maintenance (dependencies, build, etc.)

# Examples:
git commit -m "feat(invoices): add PDF export functionality"
git commit -m "fix(auth): resolve session timeout issue"
git commit -m "docs(readme): update installation instructions"
git commit -m "refactor(payments): simplify SEPA validation logic"
git commit -m "chore(deps): update Laravel to 10.43.0"
```

### 3. Git Configuration

**Setup Git properly**:
```bash
# User identity
git config --global user.name "Your Name"
git config --global user.email "your.email@example.com"

# Default branch name
git config --global init.defaultBranch main

# Editor (choose one)
git config --global core.editor "code --wait"  # VS Code
git config --global core.editor "vim"          # Vim

# Line endings (important for cross-platform)
git config --global core.autocrlf input  # Mac/Linux
git config --global core.autocrlf true   # Windows

# Useful aliases
git config --global alias.st status
git config --global alias.co checkout
git config --global alias.br branch
git config --global alias.cm commit
git config --global alias.lg "log --oneline --graph --all --decorate"

# Show all config
git config --list
```

## Branch Management

### 1. Branch Naming Convention

**Structured branch names**:
```bash
# Feature branches
feature/invoice-pdf-export
feature/client-dashboard
feature/vat-report-automation

# Bug fix branches
fix/invoice-calculation-error
fix/login-redirect-issue
hotfix/critical-payment-bug

# Refactoring branches
refactor/payment-service
refactor/database-queries

# Documentation branches
docs/api-documentation
docs/user-guide

# Chore branches
chore/update-dependencies
chore/configure-ci

# Claude Code session branches (automatic)
claude/feature-name-{sessionId}
```

### 2. Branch Operations

**Common branch commands**:
```bash
# List branches
git branch              # Local branches
git branch -r           # Remote branches
git branch -a           # All branches

# Create and switch to new branch
git checkout -b feature/new-feature
# Or (Git 2.23+)
git switch -c feature/new-feature

# Switch branches
git checkout main
# Or
git switch main

# Rename branch
git branch -m old-name new-name
git branch -m new-name  # Rename current branch

# Delete branch
git branch -d feature/completed    # Safe delete (merged only)
git branch -D feature/abandoned    # Force delete

# Delete remote branch
git push origin --delete feature/old-feature

# Track remote branch
git branch --set-upstream-to=origin/feature-branch feature-branch
# Or shorter
git branch -u origin/feature-branch
```

### 3. Branch Strategy

**Git Flow for teams**:
```
main (production)
  ↓
develop (integration)
  ↓
feature/* (individual features)
  ↓
hotfix/* (urgent production fixes)
```

**GitHub Flow (simpler)**:
```
main (always deployable)
  ↓
feature/* (short-lived feature branches)
```

## Merge and Rebase

### 1. Merging Branches

**Standard merge**:
```bash
# Update target branch first
git checkout main
git pull origin main

# Merge feature branch
git merge feature/new-feature

# Push merged changes
git push origin main
```

**Fast-forward vs. no-fast-forward**:
```bash
# Fast-forward (clean history, no merge commit)
git merge feature/simple-change

# No fast-forward (preserve branch history)
git merge --no-ff feature/complex-feature

# Always create merge commit
git merge --no-ff feature/documented-work
```

### 2. Rebasing

**Keep history linear**:
```bash
# Update feature branch with latest main
git checkout feature/my-feature
git rebase main

# Interactive rebase (clean up commits)
git rebase -i HEAD~3  # Last 3 commits

# During rebase:
# pick = keep commit
# reword = change commit message
# squash = combine with previous commit
# drop = remove commit

# Continue after resolving conflicts
git rebase --continue

# Abort rebase
git rebase --abort
```

**Rebase vs. Merge**:
```bash
# Use merge for:
# - Preserving complete history
# - Public branches
# - Team collaboration visibility

# Use rebase for:
# - Clean linear history
# - Private feature branches
# - Before creating PR
```

### 3. Cherry-pick

**Apply specific commits**:
```bash
# Apply single commit from another branch
git cherry-pick abc1234

# Apply multiple commits
git cherry-pick abc1234 def5678

# Cherry-pick without committing
git cherry-pick -n abc1234
```

## Merge Conflict Resolution

### 1. Understanding Conflicts

**Conflict markers**:
```
<<<<<<< HEAD
// Your current branch changes
const tax = amount * 0.21;
=======
// Incoming changes from other branch
const vat = amount * 0.21;
>>>>>>> feature/vat-calculation
```

### 2. Resolving Conflicts

**Step-by-step resolution**:
```bash
# 1. Attempt merge
git merge feature/conflicting-branch

# Output:
# Auto-merging src/invoice.js
# CONFLICT (content): Merge conflict in src/invoice.js
# Automatic merge failed; fix conflicts and then commit the result.

# 2. Check status
git status
# Shows conflicted files in red

# 3. Open conflicted file and resolve
# Remove conflict markers, keep desired code

# 4. Mark as resolved
git add src/invoice.js

# 5. Complete merge
git commit

# Or abort merge
git merge --abort
```

**Resolution strategies**:
```bash
# Accept all changes from current branch
git checkout --ours conflicted-file.js

# Accept all changes from incoming branch
git checkout --theirs conflicted-file.js

# Use merge tool
git mergetool
```

### 3. Preventing Conflicts

**Best practices**:
```bash
# 1. Pull frequently
git pull origin main  # Daily

# 2. Keep branches short-lived
# Create PR within 1-2 days

# 3. Communicate with team
# Avoid working on same files

# 4. Use feature flags
# Deploy incomplete features safely
```

## Pull Requests (GitHub)

### 1. Creating Pull Requests

**Using GitHub CLI**:
```bash
# Install gh CLI
# https://cli.github.com/

# Authenticate
gh auth login

# Create PR
gh pr create \
  --title "feat: add invoice PDF export" \
  --body "Implements PDF export functionality for invoices. Closes #123" \
  --base main \
  --head feature/invoice-pdf-export

# Create draft PR
gh pr create --draft

# View PR in browser
gh pr view --web
```

**PR Template** (.github/pull_request_template.md):
```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Unit tests added/updated
- [ ] Manual testing completed
- [ ] All tests passing

## Checklist
- [ ] Code follows project style guidelines
- [ ] Self-review completed
- [ ] Comments added for complex code
- [ ] Documentation updated
- [ ] No new warnings

## Related Issues
Closes #123
```

### 2. Reviewing Pull Requests

**Using gh CLI**:
```bash
# List PRs
gh pr list

# Checkout PR locally
gh pr checkout 123

# Review PR
gh pr review 123 --approve
gh pr review 123 --request-changes --body "Please add tests"
gh pr review 123 --comment --body "Looks good overall"

# Merge PR
gh pr merge 123 --squash  # Squash and merge
gh pr merge 123 --merge   # Create merge commit
gh pr merge 123 --rebase  # Rebase and merge
```

**Review checklist**:
```
✅ Code Quality:
- [ ] Follows project conventions
- [ ] No obvious bugs
- [ ] Efficient implementation
- [ ] Proper error handling

✅ Testing:
- [ ] Tests included
- [ ] Edge cases covered
- [ ] All tests passing

✅ Documentation:
- [ ] Code comments for complex logic
- [ ] README updated if needed
- [ ] API docs updated

✅ Security:
- [ ] No secrets in code
- [ ] Input validation present
- [ ] SQL injection prevention
- [ ] XSS prevention
```

### 3. PR Best Practices

**Size and scope**:
```bash
# Good PR:
# - Single focused change
# - < 400 lines changed
# - Clear purpose
# - Well tested

# Bad PR:
# - Multiple unrelated changes
# - > 1000 lines changed
# - Unclear purpose
# - No tests
```

## GitHub Actions

### 1. Basic Workflow

**CI workflow** (.github/workflows/ci.yml):
```yaml
name: CI

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  test:
    runs-on: ubuntu-latest

    steps:
    - uses: actions/checkout@v4

    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        extensions: mbstring, xml, pdo_mysql

    - name: Install Dependencies
      run: composer install --prefer-dist --no-progress

    - name: Copy .env
      run: php -r "file_exists('.env') || copy('.env.example', '.env');"

    - name: Generate key
      run: php artisan key:generate

    - name: Run Tests
      run: php artisan test

    - name: Run PHPStan
      run: vendor/bin/phpstan analyse
```

### 2. Deploy Workflow

**Auto-deploy on merge**:
```yaml
name: Deploy

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest

    steps:
    - uses: actions/checkout@v4

    - name: Deploy to Production
      uses: appleboy/ssh-action@master
      with:
        host: ${{ secrets.HOST }}
        username: ${{ secrets.USERNAME }}
        key: ${{ secrets.SSH_KEY }}
        script: |
          cd /var/www/boekhouder
          git pull origin main
          composer install --no-dev --optimize-autoloader
          php artisan migrate --force
          php artisan config:cache
          php artisan route:cache
          php artisan view:cache
```

### 3. Useful Actions

**Common GitHub Actions**:
```yaml
# PHP testing
- uses: shivammathur/setup-php@v2

# Node.js setup
- uses: actions/setup-node@v4

# Cache dependencies
- uses: actions/cache@v3
  with:
    path: vendor
    key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}

# Upload artifacts
- uses: actions/upload-artifact@v3
  with:
    name: coverage-report
    path: coverage/

# Slack notification
- uses: 8398a7/action-slack@v3
  with:
    status: ${{ job.status }}
    text: 'Deployment completed'
  env:
    SLACK_WEBHOOK_URL: ${{ secrets.SLACK_WEBHOOK }}
```

## Advanced Git Commands

### 1. Stashing Changes

**Temporary storage**:
```bash
# Save current changes
git stash

# Save with message
git stash save "WIP: invoice calculation refactor"

# List stashes
git stash list

# Apply most recent stash
git stash apply

# Apply and remove from stash list
git stash pop

# Apply specific stash
git stash apply stash@{1}

# Delete stash
git stash drop stash@{0}

# Clear all stashes
git stash clear

# Create branch from stash
git stash branch feature/from-stash stash@{0}
```

### 2. History Manipulation

**Amend last commit**:
```bash
# Change last commit message
git commit --amend -m "New commit message"

# Add forgotten files to last commit
git add forgotten-file.js
git commit --amend --no-edit

# Change author
git commit --amend --author="Name <email@example.com>"
```

**Reset commits**:
```bash
# Undo last commit, keep changes
git reset --soft HEAD~1

# Undo last commit, discard changes
git reset --hard HEAD~1

# Undo commits but keep working directory
git reset --mixed HEAD~3
```

**Revert commits** (safer than reset for public branches):
```bash
# Create new commit that undoes changes
git revert abc1234

# Revert multiple commits
git revert abc1234..def5678

# Revert merge commit
git revert -m 1 merge-commit-hash
```

### 3. Finding Issues

**Git bisect** (binary search for bugs):
```bash
# Start bisect
git bisect start

# Mark current commit as bad
git bisect bad

# Mark known good commit
git bisect good abc1234

# Git will checkout middle commit, test it
# Mark as good or bad
git bisect good  # or git bisect bad

# Repeat until bug is found

# End bisect
git bisect reset
```

**Git blame** (find who changed a line):
```bash
# Show who changed each line
git blame filename.js

# Show specific lines
git blame -L 10,20 filename.js

# Ignore whitespace changes
git blame -w filename.js
```

**Git log searching**:
```bash
# Search commit messages
git log --grep="invoice"

# Search code changes
git log -S"calculateTax"

# Show commits that changed a file
git log --follow -- path/to/file.js

# Show commits by author
git log --author="John Doe"

# Show commits in date range
git log --since="2 weeks ago" --until="yesterday"

# One-line format
git log --oneline --graph --all
```

## Git Ignore

### 1. .gitignore Patterns

**Common patterns**:
```bash
# .gitignore

# Laravel
/node_modules
/public/hot
/public/storage
/storage/*.key
/vendor
.env
.env.backup
.phpunit.result.cache
Homestead.json
Homestead.yaml
npm-debug.log
yarn-error.log

# IDE
.idea/
.vscode/
*.swp
*.swo
*~

# OS
.DS_Store
Thumbs.db

# Build
/build
/dist

# Logs
*.log

# Certificates (never commit!)
*.pem
*.crt
*.key
*.p12
```

### 2. Global .gitignore

**System-wide ignore**:
```bash
# Create global gitignore
touch ~/.gitignore_global

# Add OS-specific patterns
echo ".DS_Store" >> ~/.gitignore_global
echo "Thumbs.db" >> ~/.gitignore_global
echo ".idea/" >> ~/.gitignore_global

# Configure Git to use it
git config --global core.excludesfile ~/.gitignore_global
```

### 3. Untrack Previously Committed Files

**Remove from Git but keep locally**:
```bash
# Remove from Git, keep local file
git rm --cached filename

# Remove directory recursively
git rm -r --cached directory/

# Commit the removal
git commit -m "chore: remove secrets from version control"
```

## Troubleshooting

### Issue 1: Accidentally Committed to Wrong Branch

**Solution**:
```bash
# 1. Create new branch from current position
git branch feature/correct-branch

# 2. Reset current branch to before commits
git reset --hard HEAD~3  # Go back 3 commits

# 3. Switch to new branch
git checkout feature/correct-branch

# Your commits are now on the correct branch
```

### Issue 2: Need to Undo a Public Commit

**Solution** (use revert, not reset):
```bash
# Don't use: git reset --hard HEAD~1  # Bad for public branches!

# Instead, create reverting commit
git revert HEAD

# Or revert multiple commits
git revert HEAD~3..HEAD
```

### Issue 3: Merge Conflict in Binary File

**Solution**:
```bash
# Choose version from current branch
git checkout --ours path/to/binary-file

# Or choose version from incoming branch
git checkout --theirs path/to/binary-file

# Mark as resolved
git add path/to/binary-file
```

### Issue 4: Large File Accidentally Committed

**Solution**:
```bash
# Remove from history using filter-branch
git filter-branch --tree-filter 'rm -f path/to/large-file' HEAD

# Or use BFG Repo-Cleaner (faster)
# https://rtyley.github.io/bfg-repo-cleaner/

java -jar bfg.jar --delete-files large-file.zip
git reflog expire --expire=now --all && git gc --prune=now --aggressive
```

### Issue 5: Detached HEAD State

**Solution**:
```bash
# Check if you're in detached HEAD
git status
# HEAD detached at abc1234

# Create branch from current position
git checkout -b feature/recovery

# Or discard and return to branch
git checkout main
```

### Issue 6: Push Rejected (Non-Fast-Forward)

**Solution**:
```bash
# Someone else pushed while you were working

# 1. Fetch and rebase (preferred)
git fetch origin
git rebase origin/main

# 2. Or pull with rebase
git pull --rebase origin main

# 3. Resolve any conflicts

# 4. Push
git push origin feature-branch

# Force push (dangerous, avoid if possible)
git push --force-with-lease origin feature-branch
```

## Git Best Practices

### 1. Commit Guidelines

```
✅ Good commits:
- Atomic (single logical change)
- Descriptive message
- Pass all tests
- No merge commits in feature branches

❌ Bad commits:
- Multiple unrelated changes
- Message: "fix" or "update"
- Broken code
- Contain secrets or sensitive data
```

### 2. Branch Protection Rules

**Recommended GitHub settings**:
```
Branch protection for main:
✅ Require pull request reviews (1-2 reviewers)
✅ Require status checks (CI passing)
✅ Require branches to be up to date
✅ Require signed commits (optional)
✅ Include administrators
❌ Allow force pushes
❌ Allow deletions
```

### 3. Security

**Never commit**:
```bash
# Secrets
.env
credentials.json
private-key.pem

# Database dumps
*.sql
dump.sql

# Compiled binaries
*.exe
*.dll

# Large files
*.zip (> 50MB)
videos/

# Personal configs
.vscode/settings.json (unless team-specific)
```

**Use secrets management**:
```bash
# GitHub Secrets (for Actions)
# Repository → Settings → Secrets → New repository secret

# Access in workflow:
env:
  API_KEY: ${{ secrets.API_KEY }}

# Local development: use .env
# Production: use environment variables or secrets manager
```

## Useful Git Aliases

**Add to ~/.gitconfig**:
```bash
[alias]
    # Status
    st = status -sb

    # Logging
    lg = log --oneline --graph --all --decorate
    ll = log --pretty=format:"%C(yellow)%h%Cred%d\\ %Creset%s%Cblue\\ [%cn]" --decorate --numstat

    # Committing
    cm = commit -m
    ca = commit --amend

    # Branching
    br = branch
    co = checkout
    cob = checkout -b

    # Undoing
    undo = reset --soft HEAD~1
    unstage = reset HEAD --

    # Stashing
    save = stash save
    pop = stash pop

    # Diffing
    df = diff
    dfs = diff --staged

    # Syncing
    sync = !git fetch origin && git rebase origin/main
```

## Resources

- **Git Documentation**: https://git-scm.com/doc
- **GitHub Docs**: https://docs.github.com/
- **GitHub CLI**: https://cli.github.com/
- **Pro Git Book**: https://git-scm.com/book
- **Git Cheat Sheet**: https://education.github.com/git-cheat-sheet-education.pdf
- **Conventional Commits**: https://www.conventionalcommits.org/
- **GitHub Actions**: https://docs.github.com/actions
- **Learn Git Branching**: https://learngitbranching.js.org/

## Quick Reference

```bash
# Most common commands
git status                    # Check status
git add .                     # Stage all changes
git commit -m "message"       # Commit with message
git push                      # Push to remote
git pull                      # Fetch and merge
git checkout -b branch-name   # Create and switch to branch
git merge branch-name         # Merge branch
git log --oneline            # View commit history
git diff                     # View changes

# Emergency commands
git stash                    # Save work temporarily
git reset --hard HEAD        # Discard all changes
git clean -fd                # Remove untracked files
git reflog                   # View all HEAD movements (recovery)
```

## Branching Strategies (Additional)

### Trunk-Based Development

```bash
# Simple strategy for small teams
main (always deployable)
  ↓
short-lived feature branches (< 2 days)
  ↓
merge to main frequently

# Feature flags for incomplete features
if (Feature::enabled('new-invoice-wizard')) {
    // New code
} else {
    // Old code
}
```

### GitHub Flow for Dutch Bookkeeping

```bash
# 1. Create branch from main
git checkout main
git pull origin main
git checkout -b feature/btw-declaration-q1-2025

# 2. Make changes
# - Add VAT calculation for Q1 2025
# - Update tax rates
# - Add validation

# 3. Commit frequently
git add .
git commit -m "feat(vat): add Q1 2025 declaration calculation"

# 4. Push and create PR
git push -u origin feature/btw-declaration-q1-2025
gh pr create --title "Add BTW declaration for Q1 2025" \
  --body "Implements VAT calculation for Q1 2025. Closes #123"

# 5. After approval, merge (squash recommended)
gh pr merge 123 --squash

# 6. Delete branch
git branch -d feature/btw-declaration-q1-2025
```

## PR Workflows (Additional)

### Code Review Standards

```markdown
## Review Checklist

### Functionality
- [ ] Code solves the stated problem
- [ ] Edge cases are handled
- [ ] Error handling is appropriate
- [ ] No obvious bugs

### Dutch Bookkeeping Specifics
- [ ] VAT calculations are correct (21%, 9%, 0%)
- [ ] Multi-tenancy isolation enforced
- [ ] Amounts use DECIMAL(10,2) not FLOAT
- [ ] Dates use proper Dutch format where needed
- [ ] KVK/BTW number validation correct

### Code Quality
- [ ] Follows PSR-12 code style
- [ ] No commented-out code
- [ ] Variables/functions have clear names
- [ ] Complex logic has comments in Dutch or English
- [ ] No hardcoded values (use config)

### Testing
- [ ] Unit tests added
- [ ] Feature tests added
- [ ] All tests pass
- [ ] Test coverage is adequate

### Security
- [ ] No SQL injection vulnerabilities
- [ ] User input is validated
- [ ] Authorization checks present
- [ ] No sensitive data in logs
- [ ] CSRF protection in place

### Performance
- [ ] No N+1 query problems
- [ ] Indexes added where needed
- [ ] Pagination used for large datasets
- [ ] Caching implemented where appropriate
```

## CI/CD (Additional)

### Complete GitHub Actions Workflow for Laravel

```yaml
name: Laravel CI/CD

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main, develop]

jobs:
  test:
    runs-on: ubuntu-latest

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: bookkeeping_test
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: mbstring, xml, pdo_mysql, redis
          coverage: xdebug

      - name: Cache Composer packages
        uses: actions/cache@v3
        with:
          path: vendor
          key: ${{ runner.os }}-php-${{ hashFiles('**/composer.lock') }}

      - name: Install dependencies
        run: composer install --no-progress --prefer-dist

      - name: Copy .env
        run: cp .env.ci .env

      - name: Generate key
        run: php artisan key:generate

      - name: Run migrations
        run: php artisan migrate --force

      - name: Run tests
        run: php artisan test --coverage

      - name: Run static analysis
        run: vendor/bin/phpstan analyse

      - name: Check code style
        run: vendor/bin/php-cs-fixer fix --dry-run --diff

  deploy:
    needs: test
    if: github.ref == 'refs/heads/main'
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v4

      - name: Deploy to production
        run: |
          echo "Deploying to production"
          # Add deployment script here
```

## Anti-Patterns (Additional)

### 6. ❌ Committing Generated Files
```bash
# BAD - .gitignore missing common files
vendor/
node_modules/
.env

# GOOD - Comprehensive .gitignore
/vendor
/node_modules
/.env
/.env.backup
/storage/*.key
/public/hot
/public/storage
```

### 7. ❌ Unclear Commit Messages
```bash
# BAD
git commit -m "fix"
git commit -m "updates"
git commit -m "changes to invoice"

# GOOD
git commit -m "fix(invoice): correct VAT calculation for reverse charge"
git commit -m "feat(client): add bulk import from CSV"
git commit -m "refactor(payment): simplify SEPA validation logic"
```

## Best Practices (Additional)

### 7. Branch Protection
- **Require PR reviews** before merging to main
- **Require status checks** (CI must pass)
- **Prevent force pushes** to main/develop
- **Require linear history** (no merge commits)
- **Require signed commits** (optional but recommended)

### 8. Commit Signing
```bash
# Setup GPG key
gpg --full-generate-key

# Configure Git
git config --global user.signingkey YOUR_KEY_ID
git config --global commit.gpgsign true

# Signed commit
git commit -S -m "feat: add signed commit"
```

### 9. Semantic Versioning
```bash
# Tag releases with semantic versioning
git tag -a v1.2.3 -m "Release version 1.2.3"
git push origin v1.2.3

# Format: MAJOR.MINOR.PATCH
# MAJOR: Breaking changes
# MINOR: New features (backward compatible)
# PATCH: Bug fixes
```

## Integration Guidance (Additional)

### Pre-commit Hooks with Husky

```json
// package.json
{
  "husky": {
    "hooks": {
      "pre-commit": "lint-staged",
      "pre-push": "php artisan test"
    }
  },
  "lint-staged": {
    "*.php": [
      "vendor/bin/php-cs-fixer fix",
      "vendor/bin/phpstan analyse"
    ]
  }
}
```

## Checklists (Additional)

### Pull Request Creation Checklist
- [ ] Branch name follows convention (feature/fix/refactor)
- [ ] All tests pass locally
- [ ] Code is self-reviewed
- [ ] No console.log() or dd() statements
- [ ] Migration tested (up and down)
- [ ] Documentation updated if needed
- [ ] Breaking changes clearly noted
- [ ] Screenshots added for UI changes
- [ ] Linked to related issue
- [ ] PR description explains why, not just what

### Repository Setup Checklist
- [ ] .gitignore configured
- [ ] README with setup instructions
- [ ] LICENSE file added
- [ ] Branch protection rules set
- [ ] CI/CD pipeline configured
- [ ] Code owners file created
- [ ] Issue templates added
- [ ] PR template added
- [ ] Security policy defined
- [ ] Dependencies automated updates (Dependabot)

---

**Remember**: Commit often, push regularly, and always create PRs for code review. Never force push to shared branches!
