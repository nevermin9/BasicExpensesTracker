# Basic Expenses Tracker

A simple **vanilla PHP** web application built for learning PHP fundamentals and refreshing knowledge of **Docker** and **Nginx**.  
The project is configured to run inside a **Dev Container** for a consistent development environment.

## Prerequisites
- [Docker](https://www.docker.com/) installed on your machine.
- Either:
  - Dev Containers extension for your IDE (e.g., VS Code), **or**
  - [devcontainers/cli](https://github.com/devcontainers/cli) installed.

## Running the Application

### Using the Dev Containers Extension
Refer to your IDE’s Dev Containers documentation to open and run the project in a container.

### Using the Dev Containers CLI
Run the following commands from the project root:
```bash
devcontainer build --workspace-folder .
devcontainer up --workspace-folder .
```

Once running, open [http://localhost:8080](http://localhost:8080) in your browser.
