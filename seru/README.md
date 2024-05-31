# Host backup

## Purpose
## Features
## Important Note

This repository should be placed in the ``/root`` folder of your server.

## Installation

To install the host backup server, follow these steps:

1. Clone the repository in ``/root`` of your server.
2. execute the `install.sh` script to set up the server and the ``host-backup`` service.

## Usage

The host backup server uses a ``listener.php`` script to handle the backup process.
The script listens for incoming requests from the B2 private cloud instances and initiates the backup process.

To use the host backup server, call it from the B2 private cloud instance using the following command:

```bash
curl -X POST http://<host-backup-server-ip>:8000/<your-desired-route>
```

## Routes explanation