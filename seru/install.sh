#!/bin/bash

# Add a symbolic link for the eQual instance listener service
ln -s /root/aru/tapu/host-admin-listener.service /etc/systemd/system/host-admin-listener.service

# Reload daemon
systemctl daemon-reload

# Enable the listener service
systemctl enable host-admin-listener.service

# Start the listener service
systemctl start host-admin-listener.service