# Contributing to SearchWiz

Thank you for your interest in contributing to SearchWiz!

## Getting Started

### Prerequisites

- Git
- Node.js 18+ and npm
- PHP 7.4+
- Composer
- Local WordPress development environment

### Setup

1. Fork the repository on GitHub
2. Clone your fork:
   ```bash
   git clone git@github.com:YOUR_USERNAME/searchwiz-wp.git
   cd searchwiz-wp
   ```

3. Install dependencies:
   ```bash
   npm install
   composer install
   ```

4. Create a feature branch:
   ```bash
   git checkout -b feature/your-feature-name
   ```

## Development Workflow

### Building Assets

```bash
# Build for development
npm run build:dev

# Build for production
npm run build

# Watch for changes
npm run watch
```

### Running Tests

```bash
# PHP unit tests
composer test

# JavaScript tests
npm test

# All tests
npm run test:all
```

### Code Standards

**PHP:** WordPress Coding Standards
```bash
composer lint
composer lint:fix
```

**JavaScript:** ESLint
```bash
npm run lint:js
npm run lint:js:fix
```

**CSS:** Stylelint
```bash
npm run lint:css
npm run lint:css:fix
```

## Commit Guidelines

Use conventional commit format:

```
type(scope): description

[optional body]

[optional footer]
```

**Types:**
- `feat` - New feature
- `fix` - Bug fix
- `docs` - Documentation changes
- `refactor` - Code refactoring
- `test` - Test additions/changes
- `chore` - Maintenance tasks

**Examples:**
```
feat(search): add fuzzy matching option
fix(ajax): handle empty search terms
docs(readme): update installation steps
```

## Pull Request Process

1. Ensure all tests pass
2. Update documentation if needed
3. Add yourself to CONTRIBUTORS.md
4. Create pull request against `master` branch
5. Fill out the PR template completely
6. Wait for review

### PR Checklist

- [ ] Tests pass locally
- [ ] Code follows style guidelines
- [ ] Documentation updated
- [ ] Commit messages follow format
- [ ] No merge conflicts

## Reporting Bugs

Use GitHub Issues with this information:

- WordPress version
- PHP version
- Plugin version
- Steps to reproduce
- Expected behavior
- Actual behavior
- Screenshots if applicable

## Feature Requests

Open a GitHub Issue with:

- Clear description of the feature
- Use case / problem it solves
- Proposed implementation (optional)

## Code of Conduct

- Be respectful and inclusive
- Focus on constructive feedback
- Help others learn and grow
- Follow WordPress community guidelines

## Questions?

- Open a GitHub Issue
- Check existing issues first
- Be patient - maintainers are volunteers

## License

By contributing, you agree that your contributions will be licensed under the GPL v2 license.

