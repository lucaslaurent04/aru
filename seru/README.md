# Host admin

<!-- TOC -->
* [Host admin](#host-admin)
  * [Purpose](#purpose)
  * [Features](#features)
  * [Important Note](#important-note)
  * [Installation](#installation)
  * [Usage](#usage)
  * [Routes explanation](#routes-explanation)
<!-- TOC -->

## Purpose
## Features
## Important Note

This repository should be placed in the ``/root`` folder of your server.

## Installation

To install the host admin server, follow these steps:

1. Clone the repository in ``/root`` of your server.
2. execute the `install.sh` script to set up the server and the ``host-admin`` service.

## Usage

The host backup server uses a ``listener.php`` script to handle the admin process.
The script listens for incoming requests from the B2 private cloud instances and initiates the admin process.

To use the host backup server, call it from the B2 private cloud instance using the following command:

```bash
curl -X POST http://<host-admin-server-ip>:8000/<your-desired-route>
```

## Routes explanation