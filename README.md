# CronBeat

Introducing **CronBeat**, the ultimate open-source tool for monitoring your cron jobs. Ensure your automated tasks run smoothly and on schedule with minimal effort.

> **Note:** CronBeat is currently in early development.

## Why CronBeat?
- **Simple Integration:** Just add HTTP requests to your scripts.
- **Customizable Check-ins:** Set intervals and grace periods to avoid false alarms.
- **Real-time Alerts:** Get email notifications if a job fails to check in.
- **Comprehensive Dashboard:** Monitor all your cron jobs in one place. Track health, historical data, and missed deadlines.

## Key Features:
- **PHP Only:** No frameworks, just pure PHP.
- **Easy Updates:** Keep your tool up-to-date effortlessly.
- **Runs on Shared Hosting:** Perfect for any environment.
- **Free and Open Source:** Use it, modify it, share it.

With CronBeat, you can:
- Quickly identify trends and diagnose problems.
- Take corrective actions to maintain reliability.
- Enjoy peace of mind knowing your automated processes are monitored.

Are you ready to elevate your cron job management? Try CronBeat today and experience the difference.

---

**Disclaimer:** This repository is a test project fully developed by "JetBrains Junie (on GitHub)". All code will be reviewed by a senior PHP developer before release.

## Project Setup

This project is set up with PHP 8.4 and Docker Compose for easy local development.

### Prerequisites

- [Docker](https://www.docker.com/get-started)
- [Docker Compose](https://docs.docker.com/compose/install/)

### Directory Structure

```
cronbeat/
├── docker-compose.yml    # Docker configuration
├── src/                  # PHP source code
├── db/                   # Database directory (created on first run)
├── tests/                # Unit tests
├── LICENSE
└── README.md
```

### Database

CronBeat uses SQLite for data storage. The database is automatically created on first run when you set up your admin account.

#### Database Migrations

CronBeat includes a database migration system to handle schema changes between versions:

- **Automatic Checks**: The application automatically checks if your database needs to be updated.
- **Web Interface**: If a migration is needed, you'll be redirected to a migration page with instructions.
- **CLI Support**: Migrations can also be run from the command line.

To update your database:

1. **Web Interface**:
   - When a migration is needed, you'll be automatically redirected to `/migrate`
   - Click the "Update Database" button to run all pending migrations
   - Once complete, you'll be able to continue using the application

2. **Command Line**:
   ```bash
   # Run migrations
   php src/cli.php migrate
   
   # Force migrations even if database is up to date
   php src/cli.php migrate --force
   
   # Show CLI help
   php src/cli.php help
   ```

The migration system ensures that:
- Migrations run in transactions for data safety
- Each migration runs only once
- The database version is tracked and verified

## Getting Started

1. Clone this repository:
   ```bash
   git clone https://github.com/lippoliv/cronbeat.git
   cd cronbeat
   ```

2. Start the Docker containers:
   ```bash
   docker-compose up -d
   ```

3. Access the application in your browser:
   ```
   http://localhost:8080
   ```

4. Set up your account:
   - On first run, you'll see a setup form to create an admin account
   - Enter your username and password
   - After setup, you'll be redirected to the login page

5. To stop the containers:
   ```bash
   docker-compose down
   ```

## Development

### Environment

The development environment is configured using Docker Compose with the following specifications:
- PHP 8.4 with Apache web server
- Port 8080 mapped to container port 80
- Source code mounted from ./src to /var/www/html
- Error reporting enabled for development

### Security Features

CronBeat implements several security features:

- **Password Hashing**: Passwords are hashed using SHA-256 in the browser before being sent to the server
- **Secure Storage**: User credentials are stored securely in the SQLite database
- **Input Validation**: All user input is validated before processing
- **Error Handling**: Detailed error messages are shown only when appropriate

### Logging

CronBeat includes a comprehensive logging system that helps developers and administrators monitor application activity and troubleshoot issues:

- **Log Levels**: Four log levels are available:
  - `DEBUG`: Detailed information for debugging purposes
  - `INFO`: General information about application flow
  - `WARNING`: Warning messages that don't affect application functionality
  - `ERROR`: Error messages that affect application functionality

- **Log Format**: Logs include timestamp, level, message, and optional context data:
  ```
  [2025-08-03 09:15:30] [INFO] Application starting {"controller":"login"}
  [2025-08-03 09:15:31] [DEBUG] Connecting to database at /var/www/html/db/db.sqlite
  [2025-08-03 09:15:31] [INFO] Successfully connected to database
  ```

- **Context Data**: Additional data can be included as JSON to provide context for log messages

- **Output**: Logs are written to stdout, making them accessible in the console and Docker logs

- **Configuration**: The minimum log level can be configured to control verbosity

### Making Changes

The PHP source code is located in the `src` directory. Any changes made to files in this directory will be immediately reflected in the running application due to the volume mapping in Docker Compose.

### Testing

The project includes unit tests for all logic. To run the tests:

```bash
composer install  # Install dependencies if not already done
composer test     # Run all tests
```

Tests follow the Given-When-Then pattern and are located in the `tests` directory.

