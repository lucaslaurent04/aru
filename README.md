# aru (arû = attic, warehouse)

First things first, arû is a japanese word that means attic/warehouse. 
This repository is dedicated to the installation scripts, APIs and documentation for special hosts intended to support [b2](https://github.com/yesbabylon/b2) hosts.

Support host types:
  - **tapu-backups** to store instance backups
  - **sapu-stats** to retain log history and track instance statistics
  - **seru-admin** to manage the whole ecosystem of b2, backups and stats hosts

## System organization
- **General recap:**
    <div style="text-align:center"><img src="doc/organization.png"  alt="Organization"/></div>

## Host Backups

- **Backup: export & import**
    <div style="text-align:center"><img src="doc/hosts_message_summary_from_b2_to_backup.png"  alt="Backups"/></div>

- **Backup and Restore Process**
    <div style="text-align:center"><img src="doc/backups_process.png"  alt="Backups process"/></div>

## Host Stats
<div style="text-align:center"><img src="doc/hosts_message_summary_from_b2_to_stats.png"  alt="Host messages summary"/></div>

## Host Admin
- **B2 Host administration**
    <div style="text-align:center"><img src="doc/hosts_message_summary_from_b2_to_admin.png"  alt="Host messages summary"/></div>

- **Backup Host administration**
    <div style="text-align:center"><img src="doc/hosts_message_summary_from_admin_to_backups.png"  alt="Admin"/></div>

