# Fix for Docker Logger Issue

## Problem
The CronBeat application was failing in Docker with the following error:
```
Fatal error: Uncaught Error: Undefined constant "STDOUT" in /var/www/html/classes/Logger.php:47
```

This occurred because the Logger class was trying to use the `STDOUT` constant, which is not defined in the Docker environment.

## Solution
The fix involved replacing the direct use of the `STDOUT` constant with the more reliable `php://stdout` stream wrapper, which is a standard way to access the standard output stream in PHP.

### Change made:
In `src/classes/Logger.php`, line 47:
- Before: `fwrite(\STDOUT, $formattedMessage);`
- After: `fwrite(fopen('php://stdout', 'w'), $formattedMessage);`

This approach works consistently across different environments, including Docker, as it doesn't rely on the STDOUT constant being defined. The `php://stdout` stream wrapper is a built-in feature of PHP that provides access to the standard output stream regardless of the environment.

## Testing
The fix was tested by creating a simple script that logs messages at all levels (DEBUG, INFO, WARNING, ERROR) with context data. The test confirmed that the logging functionality works correctly with the updated code.

## Conclusion
This change ensures that the CronBeat application's logging system works properly in Docker environments, allowing for consistent logging across different deployment scenarios.