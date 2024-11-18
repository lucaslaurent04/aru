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

Certainly! Here are the "Purpose" and "Features" sections for your Host admin README.md file:

## Purpose

The Host Admin server is designed to facilitate the management and administration of B2 private cloud instances. It
provides a centralized system to handle various administrative tasks efficiently and securely.

## Features

1. **Centralized Administration**: Allows for the centralized management of multiple B2 private cloud instances,
   streamlining administrative tasks and reducing complexity.

2. **Automated Processes**: Automates common administrative processes, ensuring consistency and reducing the potential
   for human error.

3. **RESTful API**: Utilizes a RESTful API approach, making it easy to integrate with other systems and tools for
   enhanced functionality.

4. **Persistent Service**: Configured to run as a persistent service using systemd, ensuring that the host admin
   listener is always active and ready to handle incoming requests.

5. **Error Handling**: Implements robust error handling mechanisms to manage invalid requests, unknown routes, and other
   potential issues gracefully.

6. **Secure Communication**: Ensures secure communication between the B2 private cloud instances and the Host Admin
   server, safeguarding sensitive administrative data.

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