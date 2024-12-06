#!/bin/bash

# Store current directory path
INSTALL_DIR=$(pwd)

# Needed vars
BACKUP_DISK=""
BACKUP_DISK_MOUNT=""

# Function to display help
flags_help() {
    echo "Usage: script.sh [options]"
    echo "Options:"
    echo "  --backup_disk,       -d <disk>  Specify backups disk name. (required)"
    echo "  --backup_disk_mount, -m <path>  Specify backups disk mount directory name. (required)"
    echo "  --help, -h                Show help message."
    [ "$1" = "error" ] && exit 1 || exit 0
}

# Parse options
while [[ "$#" -gt 0 ]]; do
    case $1 in
        --backup_disk|-d )
            BACKUP_DISK="$2"
            if [[ -z "$BACKUP_DISK" ]]; then
                echo "Error: --backup_disk requires a value."
                exit 1
            fi
            shift ;;
        --backup_disk_mount|-m )
            BACKUP_DISK_MOUNT="$2"
            if [[ -z "$BACKUP_DISK_MOUNT" ]]; then
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

# Exit if missing $BACKUP_DISK
if [ -z "$BACKUP_DISK" ]; then
    echo "Missing required --backup_disk"
    exit 1
fi

# Exit if missing $BACKUP_DISK_MOUNT
if [ -z "$BACKUP_DISK_MOUNT" ]; then
    echo "Missing required --backup_disk_mount"
    exit 1
fi

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

# Update BACKUP_DISK conf
grep -q "^BACKUP_DISK=" "$INSTALL_DIR/.env" && \
sed -i "s|^BACKUP_DISK=.*|BACKUP_DISK=$BACKUP_DISK|" "$INSTALL_DIR/.env" || \
echo "BACKUP_DISK=$BACKUP_DISK" >> "$INSTALL_DIR/.env"

# Update BACKUP_DISK_MOUNT conf
grep -q "^BACKUP_DISK_MOUNT=" "$INSTALL_DIR/.env" && \
sed -i "s|^BACKUP_DISK_MOUNT=.*|BACKUP_DISK_MOUNT=$BACKUP_DISK_MOUNT|" "$INSTALL_DIR/.env" || \
echo "BACKUP_DISK_MOUNT=$BACKUP_DISK_MOUNT" >> "$INSTALL_DIR/.env"


#########################
### Mount backup disk ###
#########################

# Create backups directory
mkdir $BACKUP_DISK_MOUNT

# Format disk to ext filesystem
mkfs -t ext4 $BACKUP_DISK

# Handle auto mount on startup
echo "$BACKUP_DISK	$BACKUP_DISK_MOUNT	ext4	defaults	0	0" >> /etc/fstab

# Mount disk
mount $BACKUP_DISK_MOUNT


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
