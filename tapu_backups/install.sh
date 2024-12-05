#!/bin/bash

# Default values
BACKUP_DISK="/dev/sdb"

# Function to display help
flags_help() {
    echo "Usage: script.sh [options]"
    echo "Options:"
    echo "  --backup_disk, -w <path>  Specify backups disk name."
    echo "  --help, -h                Show help message."
    exit 0
}

# Parse options
while [[ "$#" -gt 0 ]]; do
    case $1 in
        --backup_disk|-w )
            BACKUP_DISK="$2"
            if [[ -z "BACKUP_DISK" ]]; then
                echo "Error: --backup_disk requires a value."
                exit 1
            fi
            shift ;;  # Skip the value
        --help|-h )
            flags_help ;;
        * )
            echo "Unknown option: $1"
            flags_help ; exit 1 ;;
    esac
    shift
done


##########################
### Mount /mnt/backups ###
##########################

# Create backups directory
mkdir /mnt/backups

# Format disk to ext filesystem
mkfs -t ext4 $BACKUP_DISK

# Handle auto mount on startup
echo "$BACKUP_DISK	/mnt/backups	ext4	defaults	0	0" >> /etc/fstab

# Mount disk
mount /mnt/backups


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

#######################
### Install php-cli ###
#######################

apt-get install -y php-cli


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
