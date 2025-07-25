# cronbeat

The ultimate open-source tool for monitoring your cron jobs. Ensure your automated tasks run smoothly on schedule with minimal effort.

## Project Overview

Cronbeat helps you monitor and manage your cron jobs by providing:
- Real-time status monitoring
- Failure notifications
- Execution history
- Performance metrics

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

### Docker Configuration

The `docker-compose.yml` file defines the service configuration:
- No version attribute (as it's deprecated in recent Docker Compose versions)
- No static container name (allowing for multiple instances)
- Automatic restart unless explicitly stopped

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
