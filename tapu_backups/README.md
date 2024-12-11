# tapu-backups

A tapu-backups host is meant to store backups of [b2](https://github.com/yesbabylon/b2) hosts.

## Install

Designed as the foundational script, `./install.sh` automates the setup process for essential Ubuntu server services.

You'll find a breakdown of the tasks it performs below.

### Prerequisite

This script must be executed with **root privileges**.

### Script steps

1. Checks that script run on correct directory and checks required args
2. Creates .env file from .env.example and add/update BACKUPS_DISK* with command given args
3. Does base server configurations and installs base services that are needed (vnstat, php-cli)
4. Mounts and configures backup disk
5. Installs cron and configure it, it'll start `./cron.php` every minute
6. Installs vsftpd server
7. Installs [API](./README_API.md) service that will listen for requests on port :8000

### Usage

```bash
./install.sh --backup_disk /dev/sdb --backup_disk_mount /mnt/backups
```

**Notes**:
  - You must **execute** the installation script with **root privileges**.
  - The **backup_disk arguments** are needed  to **mount and configure** the disk that will be used to store the backups.
