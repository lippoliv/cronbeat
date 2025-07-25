# cronbeat

The ultimate open-source tool for monitoring your cron jobs. Ensure your automated tasks run smoothly on schedule with minimal effort.

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

The PHP source code is located in the `src` directory. Any changes made to files in this directory will be immediately reflected in the running application.

## PHP Version

This project uses PHP 8.4. The Docker Compose configuration automatically pulls the appropriate PHP image.
