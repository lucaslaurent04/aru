#!/bin/bash

# Add a symbolic link for the eQual instance listener service
ln -s /root/aru/tapu/host-stats-listener.service /etc/systemd/system/host-stats-listener.service

# Reload daemon
systemctl daemon-reload

# Enable the listener service
systemctl enable host-stats-listener.service

# Start the listener service
systemctl start host-stats-listener.service