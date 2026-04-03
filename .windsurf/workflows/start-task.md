---
description: Start a new development task with proper git workflow
---

# Start New Development Task

This workflow helps you start a new feature, bug fix, or chore with the proper Git workflow.

## When to Use

Use this workflow whenever you need to:
- Start a new feature development
- Fix a bug
- Perform maintenance tasks
- Refactor existing code

## Steps to Start a Task

### 1. Check Current Branch Status
```bash
git status
git branch --show-current
```

### 2. Switch to Development Branch
```bash
git checkout development
git pull origin development
```

### 3. Create Feature Branch
```bash
# For new features
git checkout -b feature/feature-name

# For bug fixes
git checkout -b bug/bug-description

# For maintenance tasks
git checkout -b chore/task-description
```

### 4. Verify Branch Creation
```bash
git branch
git status
```

### 5. Start Development
- Make your changes
- Run tests frequently
- Commit with descriptive messages

## Branch Name Examples

### Features
- `feature/inventory-management`
- `feature/user-authentication`
- `feature/sales-system`
- `feature/barcode-scanning`
- `feature/reports-dashboard`

### Bug Fixes
- `bug/fix-permission-error`
- `bug/resolve-validation-issue`
- `bug/fix-stock-calculation`

### Chores
- `chore/update-dependencies`
- `chore/refactor-tests`
- `chore/cleanup-code`

## Pre-Development Checklist

- [ ] Current branch is not `master`, `staging`, or `development`
- [ ] Latest `development` code is pulled
- [ ] Feature branch follows naming convention
- [ ] Development environment is running (`vendor/bin/sail up -d`)
- [ ] Tests are passing (`vendor/bin/sail artisan test`)

## Development Guidelines

### During Development
1. **Commit Frequently**: Small, focused commits with clear messages
2. **Test Changes**: Run tests before each commit
3. **Update Documentation**: Keep docs in sync with code changes
4. **Push Regularly**: Push to remote branch for backup and collaboration

### Commit Message Format
```
type(scope): description

[optional body]

[optional footer]
```

Examples:
- `feat(auth): add user role management`
- `fix(inventory): resolve stock calculation error`
- `chore(deps): update laravel to v13.3.0`
- `test(users): add permission validation tests`

### Before Merging
1. **All Tests Pass**: `vendor/bin/sail artisan test --coverage`
2. **Code Review**: Request peer review
3. **Documentation**: Update relevant docs
4. **Clean Up**: Remove debug code and comments

## Integration with Project Phases

Based on the current project status, suggested next tasks:

### Phase 2: Core Inventory System
```bash
git checkout development
git pull origin development
git checkout -b feature/inventory-management
```

### Phase 3: Stock Management
```bash
git checkout development
git pull origin development
git checkout -b feature/stock-management
```

## Quick Start Commands

```bash
# Start new feature (example: inventory management)
git checkout development && \
git pull origin development && \
git checkout -b feature/inventory-management

# Start bug fix (example: permission error)
git checkout development && \
git pull origin development && \
git checkout -b bug/fix-permission-error

# Start chore (example: update dependencies)
git checkout development && \
git pull origin development && \
git checkout -b chore/update-dependencies
```

## Post-Task Workflow

After completing your task:

1. **Final Check**: Run full test suite
2. **Push Changes**: `git push origin feature/your-branch`
3. **Pull Request**: Create PR to `development`
4. **Code Review**: Get approval from team
5. **Merge**: Merge to `development` after approval
6. **Cleanup**: Delete feature branch

## Troubleshooting

### If you're on the wrong branch
```bash
git checkout development
git stash  # Save your changes
git checkout -b feature/correct-name
git stash pop  # Restore your changes
```

### If branch already exists
```bash
git checkout feature/existing-branch
git pull origin feature/existing-branch
```

### If you need to start over
```bash
git checkout development
git branch -D feature/wrong-name  # Delete wrong branch
git checkout -b feature/correct-name  # Start fresh
```
