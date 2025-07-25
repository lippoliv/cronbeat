# CronBeat Development Guidelines

This document outlines the development guidelines for the CronBeat project. All contributors should follow these guidelines to ensure consistency and quality across the codebase.

## Build and Configuration Instructions

### Local Development Setup

1. **Prerequisites:**
   - Docker and Docker Compose
   - PHP 8.4 (for local development outside Docker)
   - Composer

2. **Initial Setup:**
   ```bash
   # Clone the repository
   git clone https://github.com/lippoliv/cronbeat.git
   cd cronbeat
   
   # Install dependencies
   composer install
   
   # Start the Docker environment
   docker-compose up -d
   ```

3. **Accessing the Application:**
   - The application will be available at `http://localhost:8080`
   - Any changes to files in the `src` directory will be immediately reflected due to volume mounting

4. **Docker Configuration:**
   - The project uses PHP 8.4 with Apache
   - Source code is mounted from `./src` to `/var/www/html` in the container
   - Error reporting is enabled for development

5. **Stopping the Environment:**
   ```bash
   docker-compose down
   ```

## Testing Information

### Testing Configuration

CronBeat uses PHPUnit for testing. The testing environment is configured in `phpunit.xml` at the project root.

### Running Tests

```bash
# Run all tests
./vendor/bin/phpunit

# Run a specific test file
./vendor/bin/phpunit tests/Unit/Path/To/TestFile.php

# Run a specific test method
./vendor/bin/phpunit --filter testMethodName
```

### Adding New Tests

1. **Test Location:**
   - Unit tests should be placed in the `tests/Unit` directory
   - The directory structure should mirror the `src` directory

2. **Test Naming:**
   - Test classes should be named with the suffix `Test` (e.g., `JobStatusTest`)
   - Test methods should be named descriptively, starting with `test` (e.g., `testJobIsMarkedAsLateWhenExpectedIntervalHasPassed`)

3. **Test Structure:**
   All tests must follow the Given-When-Then pattern:
   ```php
   public function testExample(): void
   {
       // Given
       // Set up the test data and environment
       
       // When
       // Perform the action being tested
       
       // Then
       // Verify the expected outcome
   }
   ```

4. **Test Rules:**
   - In every test, do not use "given", "when", or "then" sections more than once
   - The "when" section is optional for small tests
   - Any action must happen in Given/When sections, not in Then
   - Any assertions must happen in Then section, not in Given/When
   - Each test should be independent of other tests

### Example Test

Here's an example of a test for the `JobStatus` class:

```php
<?php

namespace CronBeat\Tests\Unit\Model;

use CronBeat\Model\JobStatus;
use PHPUnit\Framework\TestCase;

class JobStatusTest extends TestCase
{
    /**
     * Test that a job is marked as late when the expected interval has passed
     */
    public function testJobIsMarkedAsLateWhenExpectedIntervalHasPassed(): void
    {
        // Given
        $jobId = 'daily-backup';
        $expectedInterval = 1; // 1 second for quick testing
        $jobStatus = new JobStatus($jobId, $expectedInterval);
        $jobStatus->checkIn(); // Mark as active
        
        // When
        // Sleep for longer than the expected interval
        sleep(2);
        $status = $jobStatus->getStatus();
        
        // Then
        $this->assertEquals('late', $status, 'Job should be marked as late when the expected interval has passed');
    }
}
```

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

## Additional Development Information

### Project Structure
The project follows a standard structure:
```
cronbeat/
├── .junie/               # Project-specific documentation
├── src/                  # PHP source code
│   ├── Model/            # Domain models
│   ├── Controller/       # Request handlers
│   └── index.php         # Main entry point
├── tests/                # Test files
│   ├── Unit/             # Unit tests
│   └── Integration/      # Integration tests
├── vendor/               # Composer dependencies
├── composer.json         # Composer configuration
├── phpunit.xml           # PHPUnit configuration
├── docker-compose.yml    # Docker configuration
└── README.md             # Project overview
```

### Debugging
- Use the Docker environment with error reporting enabled for development
- For debugging in production-like environments, implement proper logging
- Do not leave debug code or var_dump statements in production code

### Performance Considerations
- Minimize database queries and optimize when necessary
- Consider caching for frequently accessed data
- Be mindful of memory usage, especially for shared hosting environments

---

These guidelines are designed to ensure CronBeat remains a high-quality, maintainable project that fulfills its promise of being a simple, effective cron job monitoring solution.