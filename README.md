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
│   └── index.php         # Main entry point
├── LICENSE
└── README.md
```

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

4. To stop the containers:
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

### Making Changes

The PHP source code is located in the `src` directory. Any changes made to files in this directory will be immediately reflected in the running application due to the volume mapping in Docker Compose.

