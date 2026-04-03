---
description: Git workflow and environment strategy for the project
---

# Git Workflow & Environment Strategy

## Git Hierarchy Policy

Always adhere to the following branch hierarchy for this project:

1. **Source of Truth**: `master` (production)
2. **Pre-release**: `staging` (merges from development)
3. **Development**: `development` (the main integration branch)
4. **Task Branches**: All new work must branch off `development` using the naming convention `feature/*`, `chore/*`, or `bug/*`

## Workflow Constraints

- Never commit directly to `master`, `staging`, or `development`
- Before starting any code changes, verify the current branch
- If a task is a "new feature," automatically suggest using the `/start-task` workflow

## Branch Naming Conventions

### Feature Branches
- Format: `feature/feature-name`
- Examples: `feature/inventory-management`, `feature/user-authentication`, `feature/sales-system`

### Bug Fix Branches
- Format: `bug/bug-description`
- Examples: `bug/fix-permission-error`, `bug/resolve-validation-issue`

### Chore Branches
- Format: `chore/task-description`
- Examples: `chore/update-dependencies`, `chore/refactor-tests`

## Workflow Steps

### 1. Before Starting Work
```bash
# Check current branch
git branch

# Switch to development if not already there
git checkout development

# Pull latest changes
git pull origin development

# Create feature branch
git checkout -b feature/your-feature-name
```

### 2. During Development
- Commit frequently with descriptive messages
- Push changes to remote feature branch regularly
- Run tests before each commit

### 3. Before Merging
- Ensure all tests pass
- Update documentation if needed
- Create pull request to `development`
- Request code review

### 4. After Merge
- Delete feature branch locally and remotely
- Pull latest `development` changes
- Start next task or help with reviews

## Environment Strategy

### Development Environment
- Uses Laravel Sail for consistency
- Database: MySQL (in Docker)
- Frontend: Vite dev server
- All dependencies managed via Composer and npm

### Testing Environment
- Automated testing in CI/CD pipeline
- Database migrations run automatically
- Test coverage requirements: 90%

### Staging Environment
- Mirror of production setup
- Used for final testing before deployment
- Database: Separate staging database

### Production Environment
- Master branch deployed automatically
- Database backups performed regularly
- Monitoring and logging enabled

## Current Branch Status Check

Before making any changes, always run:
```bash
git branch --show-current
```

If you're on `master`, `staging`, or `development`, switch to the appropriate branch first.

## Integration with Project Phases

Each project phase should have its own feature branch:
- Phase 1: `feature/setup-authentication` ✅
- Phase 2: `feature/inventory-management` 🔄
- Phase 3: `feature/stock-management`
- Phase 4: `feature/sales-transactions`
- Phase 5: `feature/reports-dashboard`
- Phase 6: `feature/advanced-features`

## Git Commit Message Convention

All commit messages must follow the Conventional Commits specification:

### Format
`<type>(<scope>): <description>`

### Types
- `feat`: A new feature
- `fix`: A bug fix
- `docs`: Documentation only changes
- `style`: Changes that do not affect the meaning of the code (white-space, formatting, etc)
- `refactor`: A code change that neither fixes a bug nor adds a feature
- `perf`: A code change that improves performance
- `test`: Adding missing tests or correcting existing tests
- `chore`: Changes to the build process or auxiliary tools and libraries

### Rules
- Use the imperative, present tense ("change" not "changed" or "changes")
- No period at the end of the subject line
- The description must be lowercase

### Examples
```bash
feat(auth): add user role management
fix(inventory): resolve stock calculation error
docs(api): update authentication endpoints
style(users): fix code formatting
refactor(products): simplify product model
perf(reports): optimize database queries
test(users): add permission validation tests
chore(deps): update laravel to v13.3.0
```

### Advanced Format (Optional)
For more detailed commits, you can add a body and footer:
```
feat(auth): add two-factor authentication

Add TOTP support for enhanced security including:
- QR code generation for easy setup
- Backup codes for recovery
- Rate limiting to prevent brute force attacks

Closes #123
```

## Pull Request Requirements

- Clear title and description
- Link to relevant project tasks/phases
- All tests must pass
- Code review approval required
- Documentation updated if needed

## Emergency Hotfixes

For production emergencies:
1. Create `hotfix/issue-description` from `master`
2. Fix the issue
3. Merge to `master` and `development`
4. Tag with version number
5. Deploy immediately
