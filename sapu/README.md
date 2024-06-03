# Host stats

<!-- TOC -->
* [Host stats](#host-stats)
  * [Purpose](#purpose)
  * [Features](#features)
  * [Important Note](#important-note)
  * [Installation](#installation)
  * [Usage](#usage)
  * [Routes explanation](#routes-explanation)
    * [``/instance/logs`` :](#instancelogs-)
      * [Purpose](#purpose-1)
      * [Script process task](#script-process-task)
      * [Usage](#usage-1)
        * [Request parameters](#request-parameters)
        * [Example Request](#example-request)
        * [Example Response](#example-response)
    * [``/status`` :](#status-)
      * [Purpose](#purpose-2)
      * [Script process task](#script-process-task-1)
      * [Usage](#usage-2)
        * [Request parameters](#request-parameters-1)
        * [Example Request](#example-request-1)
        * [Example Response](#example-response-1)
<!-- TOC -->

## Purpose

The purpose of the Host Stats server is to provide an efficient and centralized way to manage and monitor the log files
and system status of various user instances within a B2 private cloud environment.
This server allows for the retrieval of log files based on specific criteria,
such as date range, log level, and log layer, as well as providing current
system status information like total and remaining disk space.

By implementing this server, administrators can easily access important logs for troubleshooting and maintenance, and
monitor system resources to ensure optimal performance and uptime of the cloud instances.

## Features

The Host Stats server offers the following features:

1. **Log Retrieval**: Allows administrators to retrieve log files for specific user instances based on various filter
   criteria such as date range, log level, log layer, and keywords.
   This feature helps in effective troubleshooting and monitoring.

2. **System Status Monitoring**: Provides an endpoint to fetch current system status information, particularly focusing
   on disk usage.
   This helps in keeping track of total and remaining disk space to ensure optimal resource management.

3. **Centralized Management**: Enables centralized handling of logs and system status for multiple instances in a B2
   private cloud environment, simplifying the administration and maintenance tasks.

4. **RESTful API**: Utilizes a RESTful API approach with endpoints designed to handle POST requests, ensuring easy
   integration with other systems and tools.

5. **Customizable Filters**: Offers customizable filters for log retrieval, allowing precise and relevant data
   extraction based on specific needs.

6. **Automated Installation and Setup**: Includes an installation script (`install.sh`) to automate the setup process of
   the server and service, making deployment quick and easy.

7. **Persistent Service**: Configured to run as a persistent service using systemd, ensuring that the host stats
   listener is always active and ready to handle incoming requests.

8. **Error Handling**: Implements robust error handling mechanisms to manage invalid requests, unknown routes, and other
   potential issues gracefully.

## Important Note

This repository should be placed in the ``/root`` folder of your server.

## Installation

To install the host stats server, follow these steps:

1. Clone the repository in ``/root`` of your server.
2. execute the `install.sh` script to set up the server and the ``host-stats`` service.

## Usage

The host stats server uses a ``listener.php`` script to handle the stat process.
The script listens for incoming requests from the B2 private cloud instances and initiates the stat process.

To use the host stats server, call it from the B2 private cloud instance using the following command:

```bash
curl -X POST http://<host-stats-server-ip>:8000/<your-desired-route>
```

## Routes explanation

The host stats server provides the following routes:

### ``/instance/logs`` :

#### Purpose

The `instance/logs` endpoint facilitates the retrieval of log files for a specified user instance based on various
filter criteria. This endpoint is designed to handle POST requests containing the necessary data to identify the
instance and apply the desired filters to the log retrieval process.

#### Script process task

The `instance_logs` function, defined within the PHP script associated with this endpoint, implements the logic for
retrieving logs for a user instance based on the provided data and filters. The script performs the following tasks:

1. **Validate Request Data:** Checks if both the `instance` and `filter` keys exist in the input data array. If not, it
   throws an `InvalidArgumentException` indicating a bad request.
2. **Prepare Filters:** Prepares the filters from the input data for the log retrieval process.
3. **Retrieve Logs:** Based on the provided filters, retrieves the relevant log files from the logs directory of the
   specified user instance located at `/home/$instance/export/logs`.
4. **Return Logs:** Returns a response array with a status code `201` and a message containing the filtered logs.

#### Usage

To retrieve logs for a user instance using the `instance/logs` endpoint:

Send a POST request to the endpoint with the identifier of the instance and the desired filters in the request body.
Handle the HTTP response to access the filtered logs of the user instance.

##### Request parameters

| Parameter   | Required |  Type   | Possible values                                          | Description                                                                           |
|-------------|:--------:|:-------:|----------------------------------------------------------|---------------------------------------------------------------------------------------|
| instance    |   true   | string  |                                                          | Identifier of the user instance to retrieve logs for                                  |
| filter      |   true   |  array  |                                                          | Properties belows are contained inside it.<br><br>Filter criteria for retrieving logs |
| date_from   |  false   | string  |                                                          | Start date for filtering logs                                                         |
| date_to     |  false   | string  |                                                          | End date for filtering logs                                                           |
| single_date |   true   | boolean |                                                          | Boolean flag indicating whether to filter logs by a single date                       |
| level       |   true   | string  | ``'All'\|'system'\|'debug'\|'info'\|'warning'\|'error'`` | Log level to filter by                                                                |
| layer       |   true   | string  | ``'PHP'\|'SQL'\|'ORM'\|'APP'\|'API'\|'AAA'\|'NET'``      | Log layer to filter by                                                                |
| keyword     |  false   | string  |                                                          | Keyword to search for within the logs                                                 |

##### Example Request

```http request
POST /instance/logs
Content-Type: application/json

{
  "instance": "test.yb.run",
  "filter": {
    "date_from": "2023-01-01",
    "date_to": "2023-01-31",
    "single_date": false,
    "level": "ERROR",
    "layer": "application",
    "keyword": "database"
  }
}
```

##### Example Response

```http request
HTTP/1.1 201 OK
Content-Type: application/json

{
  "message": [
    "Log entry 1 matching criteria...",
    "Log entry 2 matching criteria..."
  ]
}
```

### ``/status`` :

#### Purpose

The `status` endpoint facilitates the retrieval of the current status of the system, particularly focusing on disk
usage. This endpoint is designed to handle POST requests and returns a summary of the total and remaining disk space.

#### Script process task

The `status` function, defined within the PHP script associated with this endpoint, implements the logic for retrieving
the system status based on the provided data. The script performs the following tasks:

1. **Execute Command:** Executes shell commands to retrieve system status information, particularly focusing on disk
   space usage.
2. **Adapt Units:** Adapts the units of the output from the commands to a more user-friendly format.
3. **Return Status:** Returns a response array with a status code `201` and a message containing the formatted system
   status information.

#### Usage

To retrieve the system status using the `status` endpoint:

Send a POST request to the endpoint. Handle the HTTP response to access the system status information.

##### Request parameters

No request parameters are required for this endpoint.

##### Example Request

```http request
POST /status
Content-Type: application/json

{}
```

##### Example Response

```http request
HTTP/1.1 201 OK
Content-Type: application/json

{
    "config": {
        "disk": "20G",
        "remaining_disk_space": "10G"
    }
}
```
