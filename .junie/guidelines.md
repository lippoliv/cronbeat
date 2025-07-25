# CronBeat Development Guidelines

This document outlines the development standards and practices for the CronBeat project, a PHP 8.4 application for monitoring cron jobs.

## Build/Configuration Instructions

### Environment Setup

CronBeat requires PHP 8.4 and can be run either directly or using Docker.

#### Direct Setup

1. **Prerequisites**:
   - PHP 8.4+
   - Composer

2. **Installation**:
   ```bash
   # Clone the repository
   git clone https://github.com/lippoliv/cronbeat.git
   cd cronbeat
   
   # Install dependencies
   composer install
   ```

3. **Running the Application**:
   ```bash
   # Start a local PHP server
   php -S localhost:8080 -t src/
   ```

#### Docker Setup

1. **Prerequisites**:
   - Docker
   - Docker Compose

2. **Installation and Running**:
   ```bash
   # Clone the repository
   git clone https://github.com/lippoliv/cronbeat.git
   cd cronbeat
   
   # Start the Docker container
   docker-compose up -d
   ```

3. **Accessing the Application**:
   - The application will be available at `http://localhost:8080`

### Project Structure

```
cronbeat/
├── src/                  # Source code
│   ├── index.php         # Main entry point for all requests
│   └── Classes/          # PHP classes
├── tests/                # Test files
├── composer.json         # Composer configuration
├── phpunit.xml           # PHPUnit configuration
└── docker-compose.yml    # Docker configuration
```

## Testing Information

### Testing Framework

CronBeat uses PHPUnit for unit testing. All tests must follow the Given-When-Then pattern:

- **Given**: Set up the test data and environment
- **When**: Perform the action being tested
- **Then**: Verify the expected outcome

### Running Tests

```bash
# Install dependencies if not already done
composer install

# Run all tests
vendor/bin/phpunit

# Run a specific test file
vendor/bin/phpunit tests/YourTestFile.php

# Run a specific test method
vendor/bin/phpunit --filter testMethodName
```

### Writing Tests

All tests should:
- Be independent of other tests
- Follow the Given-When-Then pattern
- Not use "given", "when", or "then" twice in a single test
- Perform all actions in Given/When sections, not in Then
- Perform all assertions in Then section, not in Given/When

#### Example Test

Here's an example of a properly structured test:

```php
/**
 * Test that a job becomes late after the expected interval plus grace period
 */
public function testJobBecomesLateAfterExpectedIntervalPlusGracePeriod(): void
{
    // Given a job with a 1-hour interval and 5-minute grace period
    $job = new Job(
        'Hourly Report',
        'hourly-report',
        3600, // 1 hour in seconds
        300   // 5 minutes grace period
    );
    
    // When the job checks in
    $job->checkIn();
    
    // And we mock time to be before the late threshold
    $currentTime = time() + 3600 + 299; // Just before becoming late
    
    // Then the job should not be considered late yet
    $this->assertFalse($this->isJobLateAt($job, $currentTime));
    
    // When time advances past the grace period
    $currentTime = time() + 3600 + 301; // Just after becoming late
    
    // Then the job should be considered late
    $this->assertTrue($this->isJobLateAt($job, $currentTime));
}
```

### Creating New Tests

1. Create a new test file in the `tests/` directory
2. Name the file after the class it tests, e.g., `JobTest.php` for testing `Job.php`
3. Extend `PHPUnit\Framework\TestCase`
4. Write test methods that follow the Given-When-Then pattern
5. Run the tests to ensure they pass

## Additional Development Information

### Coding Standards

- **PHP Version**: Always use PHP 8.4 features and syntax
- **No Frameworks**: Do not use external PHP frameworks
- **Object-Oriented**: All code should be object-oriented
- **Routing**: All requests should go to the same PHP file (index.php) which handles routing
- **Clean Code**: Prioritize readable code over comments
  - Only comment code to explain technical complexity
  - Use descriptive variable and method names
  - Keep methods small and focused on a single responsibility

### Version Control Practices

- Do not use conventional commits during development
- Conventional commits will only be applied when merging to the main branch

### Error Handling

- Use exceptions for error handling
- Catch exceptions at appropriate levels
- Log errors with sufficient context for debugging

### Security Practices

- Validate and sanitize all user input
- Use prepared statements for database queries
- Follow the principle of least privilege
- Do not expose sensitive information in error messages

### Performance Considerations

- Minimize database queries
- Use caching where appropriate
- Optimize loops and data structures for performance
- Consider memory usage for large datasets

### Documentation

- Document public APIs with PHPDoc comments
- Include parameter types and return types
- Explain non-obvious behavior
- Update documentation when changing code