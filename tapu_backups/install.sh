#!/bin/bash

# #memo - This script must be run with root privileges

# Store current directory path
INSTALL_DIR=$(pwd)

# Needed vars
BACKUPS_DISK=""
BACKUPS_DISK_MOUNT=""

# Function to display help
flags_help() {
    echo "Usage: script.sh [options]"
    echo "Options:"
    echo "  --backup_disk,       -d <disk>  Specify backups disk name. (required)"
    echo "  --backup_disk_mount, -m <path>  Specify backups disk mount directory name. (required)"
    echo "  --help, -h                      Show help message."
    [ "$1" = "error" ] && exit 1 || exit 0
}

# Parse options
while [[ "$#" -gt 0 ]]; do
    case $1 in
        --backup_disk|-d )
            BACKUPS_DISK="$2"
            if [[ -z "$BACKUPS_DISK" ]]; then
                echo "Error: --backup_disk requires a value."
                exit 1
            fi
            shift ;;
        --backup_disk_mount|-m )
            BACKUPS_DISK_MOUNT="$2"
            if [[ -z "$BACKUPS_DISK_MOUNT" ]]; then
                echo "Error: --backup_disk_mount requires a value."
                exit 1
            fi
            shift ;;
        --help|-h )
            flags_help ;;
        * )
            echo "Unknown option: $1"
            flags_help error ;;
    esac
    shift
done

# Array of required variables and their descriptions
declare -A required_vars=(
    ["BACKUPS_DISK"]="--backup_disk"
    ["BACKUPS_DISK_MOUNT"]="--backup_disk_mount"
)

# Iterate over the required variables
for var in "${!required_vars[@]}"; do
    if [ -z "${!var}" ]; then
        echo "Missing required ${required_vars[$var]}"
        exit 1
    fi
done


#####################
### Env variables ###
#####################

# Ensure .env.example exists
if [ ! -f "$INSTALL_DIR/.env.example" ]; then
    echo "Error: $INSTALL_DIR/.env.example does not exist."
    exit 1
fi

# Create .env file from example if it does not exist
if [ ! -f "$INSTALL_DIR/.env" ]; then
    cp "$INSTALL_DIR/.env.example" "$INSTALL_DIR/.env"
fi

# List of configuration variables and their values
declare -A configs=(
    ["BACKUPS_DISK"]="$BACKUPS_DISK"
    ["BACKUPS_DISK_MOUNT"]="$BACKUPS_DISK_MOUNT"
)

# Iterate over the configuration variables
for key in "${!configs[@]}"; do
    value="${configs[$key]}"

    # Update or append the configuration in the .env file
    if grep -q "^$key=" "$INSTALL_DIR/.env"; then
        sed -i "s|^$key=.*|$key=$value|" "$INSTALL_DIR/.env"
    else
        echo "$key=$value" >> "$INSTALL_DIR/.env"
    fi
done


#########################
### Mount backup disk ###
#########################

if ! mount | grep -q "on $BACKUPS_DISK_MOUNT "; then
    # Create backups directory
    mkdir $BACKUPS_DISK_MOUNT

    # Format disk to ext filesystem, if it's not already the case
    if ! blkid "$BACKUPS_DISK" | grep -q 'TYPE="ext4"'; then
        mkfs -t ext4 $BACKUPS_DISK
    fi

    # Handle auto mount on startup
    echo "$BACKUPS_DISK	$BACKUPS_DISK_MOUNT	ext4	defaults	0	0" >> /etc/fstab

    # Mount disk
    mount $BACKUPS_DISK_MOUNT
fi


####################
### Install cron ###
####################

apt-get install -y cron

PHP_SCRIPT="cron.php"
CRON_CMD="* * * * * cd /root/aru/tapu_backups && /usr/bin/php $PHP_SCRIPT"

# Check if the cron job already exists
if ! crontab -l | grep -q "$PHP_SCRIPT"; then
    # If not, add the cron job
    (crontab -l 2>/dev/null; echo "$CRON_CMD") | crontab -
fi


##########################
### Install ftp server ###
##########################

# Install ftp service
apt-get install -y vsftpd

# Custom FTP config
mv /etc/vsftpd.conf /etc/vsftpd.conf.orig
cp "$INSTALL_DIR"/conf/etc/vsftpd.conf /etc/vsftpd.conf

# Restart FTP service
systemctl restart vsftpd


###############################
### Install needed packages ###
###############################

apt-get install -y vnstat php-cli


########################
### Install listener ###
########################

# Add a symbolic link for the eQual instance listener service
ln -s /root/aru/tapu_backups/host-backups-listener.service /etc/systemd/system/host-backups-listener.service

# Reload daemon
systemctl daemon-reload

# Enable the listener service
systemctl enable host-backups-listener.service

# Start the listener service
systemctl start host-backups-listener.service
