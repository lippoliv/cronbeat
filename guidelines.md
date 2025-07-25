# CronBeat Development Guidelines

This document outlines the development guidelines for the CronBeat project. All contributors should follow these guidelines to ensure consistency and quality across the codebase.

## Technical Requirements

### PHP Version
- CronBeat is developed using PHP 8.4
- Code should utilize PHP 8.4 features where appropriate
- Ensure backward compatibility is considered when necessary

### Framework Policy
- **No frameworks allowed**
- CronBeat is built with pure PHP to ensure maximum compatibility with shared hosting environments
- External libraries should be minimized and only used when absolutely necessary

## Code Structure

### Object-Oriented Programming
- All code must follow object-oriented programming principles
- Classes should have a single responsibility
- Use proper encapsulation, inheritance, and polymorphism
- Implement interfaces where appropriate to define contracts

### Routing
- All HTTP requests must be directed to a single PHP file which handles routing
- The router should dispatch requests to the appropriate controllers
- URL patterns should be consistent and follow RESTful principles where applicable

### Clean Code Principles
- Follow clean code principles throughout the codebase
- Use meaningful variable and function names
- Keep functions small and focused on a single task
- Avoid deep nesting of control structures
- Prioritize readability over cleverness

### Comments Policy
- **Readable Code Over Comments**
- Write self-documenting code that doesn't require extensive comments
- Only add comments to explain technical complexity that cannot be simplified
- Do not comment out code - remove it if it's not needed

## Testing Guidelines

### Unit Testing Requirements
- All code must be covered by unit tests
- Tests should verify functionality and edge cases
- Use PHPUnit or a similar lightweight testing framework

### Test Structure
- All tests must follow the Given-When-Then pattern:
  - **Given**: Set up the test data and environment
  - **When**: Perform the action being tested
  - **Then**: Verify the expected outcome

### Test Rules
- In every test, do not use "given", "when", or "then" sections more than once
- The "when" section is optional for small tests
- Any action must happen in Given/When sections, not in Then
- Any assertions must happen in Then section, not in Given/When
- Each test should be independent of other tests
  - Do not rely on state from previous tests
  - Set up all necessary preconditions within the test itself

### Test Naming
- Test names should clearly describe what is being tested
- Use a consistent naming convention for test methods
- Group related tests in appropriately named test classes

## Version Control Practices

### Commit Messages
- Do not use conventional commits during development
- Conventional commit format will only be applied when merging to main branch
- During development, use clear and descriptive commit messages that explain the changes
- Keep commits focused on a single logical change when possible

## Quality Assurance

### Code Reviews
- All code must be reviewed before merging
- Reviews should check for adherence to these guidelines
- Feedback should be constructive and focused on improving code quality

### Continuous Integration
- All tests must pass before code can be merged
- Static analysis tools should be used to catch common issues
- Code coverage should be maintained or improved with new changes

## Deployment

### Compatibility
- Ensure code works on shared hosting environments
- Minimize server requirements beyond PHP 8.4
- Test deployment process regularly to ensure smooth updates

---

These guidelines are designed to ensure CronBeat remains a high-quality, maintainable project that fulfills its promise of being a simple, effective cron job monitoring solution.