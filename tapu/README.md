# Host backup

<!-- TOC -->
* [Host backup](#host-backup)
  * [Purpose](#purpose)
  * [Features](#features)
  * [Important Note](#important-note)
    * [Developer note](#developer-note)
  * [Installation](#installation)
  * [Usage](#usage)
  * [Routes explanation](#routes-explanation)
    * [``/token`` :](#token-)
      * [Purpose](#purpose-1)
      * [Script process task](#script-process-task)
      * [Usage](#usage-1)
        * [Request parameters](#request-parameters)
        * [Example Request](#example-request)
        * [Example Response](#example-response)
        * [Considerations](#considerations)
    * [``/token-release`` :](#token-release-)
      * [Purpose](#purpose-2)
      * [Script process task](#script-process-task-1)
      * [Usage](#usage-2)
        * [Request parameters](#request-parameters-1)
        * [Example Request](#example-request-1)
        * [Example Response](#example-response-1)
        * [Considerations](#considerations-1)
    * [``/instance/backups`` :](#instancebackups-)
      * [Purpose](#purpose-3)
      * [Script process task](#script-process-task-2)
      * [Usage](#usage-3)
        * [Request parameters](#request-parameters-2)
        * [Example Request](#example-request-2)
        * [Example Response](#example-response-2)
    * [``/status`` :](#status-)
      * [Purpose](#purpose-4)
      * [Script process task](#script-process-task-3)
      * [Usage](#usage-4)
        * [Request parameters](#request-parameters-3)
        * [Example Request](#example-request-3)
        * [Example Response](#example-response-3)
<!-- TOC -->

## Purpose

The repository is a PHP web server for handling backups of B2 private cloud instances.

It is designed to be used in conjunction with the [B2 repository](https://github.com/yesbabylon/b2) for initializing
instances using the eQual framework.

## Features

- **Backup**: The repository provides a PHP web server for handling backups of B2 private cloud instances.
- **Automation**: The server automatically handles the backup process, ensuring that your data is secure and accessible.
- **Security**: The server is designed to be secure, with encryption and authentication mechanisms in place to protect
  your data.
- **Monitoring**: The server provides monitoring tools to track the status of backups and ensure that they are running
  smoothly.

## Important Note

This repository should be placed in the ``/root`` folder of your server.

### Developer note

- Currently, the path list of instances backups is ``'/home/backups/' + instance name``
- The path of the backup JWT is ``/home/status/jwt.txt``
- The path of the register of instance tokens for backups is ``/home/aru/tapu/backup_tokens.json``

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

The host backup server provides the following routes:

### ``/token`` :

#### Purpose

The `/token` endpoint facilitates the creation and retrieval of a backup token for a specified user instance. This
endpoint is designed to handle POST requests containing the necessary data to generate the token and associated
credentials.

#### Script process task

The `token` function, defined within the PHP script associated with this endpoint, implements the logic for creating a
backup token based on the provided data. The script performs the following tasks:

1. **Validate Request Data:** Checks if the `JWT` and `instance` keys exist in the input data array. If not, it throws
   an exception with a status code `400` indicating a bad request.
2. **Read Backup Host JWT:** Reads the backup host JWT from a file located at `/home/status/jwt.txt`. If the file read
   operation fails, it throws an exception with a status code `500` indicating a server error.
3. **Create Backup Token:** Uses the backup host JWT and the provided JWT and instance name to create an encrypted
   backup token using the `AES-256-CBC` encryption method. If the encryption fails, it throws an exception with a status
   code `500`.
4. **Manage Backup Tokens File:** Manages the `backup_tokens.json` file located at `/home/aru/tapu/backup_tokens.json`:
    - Opens the file and acquires an exclusive lock to prevent concurrent access.
    - Read the file content and decode it as JSON.
    - Add the newly created backup token to the list of tokens.
    - Encodes the updated content back to JSON and writes it to the file, truncating the file first to ensure it is
      properly updated.
    - Releases the lock and closes the file.

5. **Create User and Set Credentials:** Escapes the input data to prevent shell injection and executes shell commands to
   create a new user with a `nologin` shell and set the user's password to the created backup token.
6. **Return Response:** Returns a response array with a status code `201` and a message containing the backup token and
   credentials (username and password).

#### Usage

To create and retrieve a backup token for a user instance using the `/token` endpoint:

Send a POST request to the endpoint with the JWT and instance name in the request body. Handle the HTTP response to
access the generated backup token and credentials.

##### Request parameters

| Parameter | Required | Description                      |
|-----------|:--------:|----------------------------------|
| JWT       |   true   | JSON Web Token for authorization |
| instance  |   true   | Identifier of the user instance  |

##### Example Request

```http request
POST /token
Content-Type: application/json

{
  "JWT": "example.jwt.token",
  "instance": "test.yb.run"
}
```

##### Example Response

```http request
HTTP/1.1 201 OK
Content-Type: application/json

{
  "message": {
    "token": "encrypted_backup_token",
    "credentials": {
      "username": "test.yb.run",
      "password": "encrypted_backup_token"
    }
  }
}
```

##### Considerations

Ensure that the specified JWT and instance name are valid and correspond to an existing user instance. Proper
permissions must be set to allow the script to read from and write to the necessary files, and to execute shell commands
for user creation and password setting.

### ``/token-release`` :

#### Purpose

The `token-release` endpoint facilitates the release of a token associated with a specified user instance. This endpoint
is designed to handle POST requests containing the necessary data to identify the instance and its associated token.

#### Script process task

The `tokenRelease` function, defined within the PHP script associated with this endpoint, implements the logic for
releasing a token for a user instance based on the provided data. The script performs the following tasks:

1. **Validate Request Data:** Checks if the `JWT` and `instance` keys exist in the input data array and are properly
   set. If not, it throws a `400` Bad Request exception.
2. **Read Backup Host JWT:** Reads the backup host JWT from a file located at `/home/status/jwt.txt`. If the file read
   operation fails, it throws a `500` Server Error exception.
3. **Create Backup Token:** Uses the `openssl_encrypt` function to create a backup token by encrypting the
   concatenated `JWT` and `instance` values with the backup host JWT. If encryption fails, it throws a `500` Server
   Error exception.
4. **Lock and Read Backup Tokens File:** Attempts to lock the `backup_tokens.json` file for exclusive access. If it
   fails to acquire the lock after a set number of retries, it throws a `500` Server Error exception. Reads the content
   of the file once the lock is acquired.
5. **Update Backup Tokens File:** Parses the JSON content of the file, removes the token associated with the instance,
   and writes the updated content back to the file.
6. **Release File Lock:** Releases the lock on the file and closes the file handle.
7. **Delete FTP Account:** Deletes the FTP account associated with the instance using the `userdel` command.
8. **Return Response:** Returns a response array with a status code `201` and a message indicating that the token was
   released.

#### Usage

To release a token for a user instance using the `token-release` endpoint:

Send a POST request to the endpoint with the `JWT` and `instance` in the request body. Handle the HTTP response to
confirm the success of the token release operation.

##### Request parameters

| Parameter | Required | Description                                                           |
|-----------|:--------:|-----------------------------------------------------------------------|
| JWT       |   true   | JSON Web Token associated with the instance                           |
| instance  |   true   | Identifier of the user instance for which the token is being released |

##### Example Request

```http request
POST /token-release
Content-Type: application/json

{
  "JWT": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "instance": "test.yb.run"
}
```

##### Example Response

```http request
HTTP/1.1 201 OK
Content-Type: application/json

{
  "message": "Token released"
}
```

##### Considerations

Ensure that the specified `JWT` and `instance` correspond to valid values and that the server has the necessary
permissions to read from and write to the `backup_tokens.json` file and to delete the FTP account associated with the
instance.
Proper error handling should be in place to manage potential issues such as file access conflicts or
encryption errors.

### ``/instance/backups`` :

#### Purpose

The `instance/backups` endpoint facilitates the retrieval of a list of backup files for a specified user instance. This
endpoint is designed to handle POST requests containing the necessary data to identify the instance whose backups are to
be retrieved.

#### Script process task

The `instance_backups` function, defined within the PHP script associated with this endpoint, implements the logic for
retrieving a list of backup files for a user instance based on the provided data. The script performs the following
tasks:

1. **Validate Request Data:** Checks if the `instance` key exists in the input data array and is properly set. If not,
   it returns a status code `400` indicating a bad request.
2. **Retrieve Backup Files:** Uses the `scandir` function to retrieve a list of files in the backup directory of the
   specified user instance located at `/home/backups/$instance`.
3. **Remove Unnecessary Entries:** Removes the `.` and `..` entries from the list of files.
4. **Return Backups List:** Returns a response array with a status code `201` and a message containing the list of
   backup files.

#### Usage

To retrieve a list of backups for a user instance using the `instance/backups` endpoint:

Send a POST request to the endpoint with the identifier of the instance in the request body. Handle the HTTP response to
access the list of backup files for the user instance.

##### Request parameters

| Parameter | Required | Description                                             |
|-----------|:--------:|---------------------------------------------------------|
| instance  |   true   | Identifier of the user instance to retrieve backups for |

##### Example Request

```http request
POST /instance/backups
Content-Type: application/json

{
  "instance": "test.yb.run"
}
```

##### Example Response

```http request
HTTP/1.1 201 OK
Content-Type: application/json

{
  "message": [
    "backup1.tar.gz",
    "backup2.tar.gz"
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
