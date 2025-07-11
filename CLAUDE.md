# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a VVV (Varying Vagrant Vagrants) local development environment, primarily designed for WordPress development. VVV uses Vagrant with multiple provider options (VirtualBox, Docker, Parallels, VMware, Hyper-V) to create a Linux server environment for building WordPress sites and contributing to WordPress core.

## Core Commands

### Vagrant Operations
- `vagrant up` - Start the VM
- `vagrant up --provision` - Start VM and run provisioning
- `vagrant provision` - Run provisioning on existing VM
- `vagrant halt` - Stop the VM
- `vagrant reload` - Restart the VM
- `vagrant destroy` - Destroy the VM completely
- `vagrant ssh` - SSH into the VM

### Plugin Management
- `vagrant plugin install --local` - Install required plugins locally

### Database Operations (via SSH)
- `vagrant ssh -c "db_backup"` - Backup all databases
- `vagrant ssh -c "db_restore"` - Restore databases from backups

## Architecture

### Configuration System
- **Primary config**: `config/config.yml` - Main configuration file (auto-created from `config/default-config.yml`)
- **Legacy migration**: Old `vvv-custom.yml` files are automatically migrated to `config/config.yml`
- **YAML format**: All configuration uses YAML with significant whitespace

### Site Structure
- **Sites defined in**: `config/config.yml` under `sites:` section
- **Site directories**: `www/[site-name]/` - Each site gets its own directory
- **Site provisioning**: Each site uses `provision/vvv-init.sh` scripts
- **Default template**: Sites typically use `https://github.com/Varying-Vagrant-Vagrants/custom-site-template.git`

### Provisioning System
- **Main provisioner**: `provision/provision.sh` - Primary provisioning script
- **Pre-hook**: `provision/provision-pre.sh` (optional)
- **Post-hook**: `provision/provision-post.sh` (optional)
- **Custom provisioning**: `provision/provision-custom.sh` (replaces default if exists)
- **Modular system**: Core provisioners in `provision/core/` handle specific services (nginx, php, mariadb, etc.)

### Extension System
- **Core extensions**: Located in `provision/extensions/core/`
- **Available extensions**: phpmyadmin, tls-ca, memcached-admin, opcache-status, webgrind, mongodb, tideways, nvm, php74/80/81/83
- **Extension sources**: Configurable in `config/config.yml` under `extension-sources:`

### Directory Structure
- **www/**: WordPress sites and web content
- **config/**: Configuration files for services (nginx, php, mysql, etc.)
- **database/**: Database files and SQL imports
- **certificates/**: SSL/TLS certificates for HTTPS
- **log/**: Service logs (nginx, php, provisioning)
- **provision/**: Provisioning scripts and utilities

### Networking
- **Default IP**: 192.168.56.4 (configurable via `private_network_ip`)
- **Host management**: Automatic via vagrant-goodhosts plugin
- **Dashboard**: Available at http://vvv.test
- **Sites**: Accessible via configured hostnames (e.g., sitename.test)

## Development Workflow

### Adding New Sites
1. Edit `config/config.yml` and add site under `sites:` section
2. Run `vagrant provision` to create the site
3. Site will be available at configured hostname

### Site Configuration Options
- **repo**: Git repository URL for site template
- **hosts**: Array of hostnames for the site
- **custom**: Site-specific configuration (themes, plugins, etc.)
- **php**: PHP version to use (optional)
- **skip_provisioning**: Set to true to skip site setup

### Configuration Changes
- **Important**: After modifying `config/config.yml`, you must run `vagrant provision` or `vagrant up --provision`
- **No exceptions**: Configuration changes require reprovisioning

### Custom Provisioning
- **Site-level**: Add `provision/vvv-init.sh` in site directory
- **Global pre-hook**: Create `provision/provision-pre.sh`
- **Global post-hook**: Create `provision/provision-post.sh`
- **Complete override**: Create `provision/provision-custom.sh`

## Important Notes

- **Never use sudo**: VVV explicitly warns against and prevents sudo usage
- **Provider limitations**: Once a provider is chosen, it requires `vagrant destroy` + `vagrant up` to change
- **Memory recommendations**: 2GB for theme/plugin development, adjust based on available RAM
- **Database sharing**: Can be enabled via `db_share_type: true` in config
- **Customfile support**: Advanced users can add `Customfile` for additional Vagrant configuration (unofficial support)

## File Locations

- **Version info**: `version` file in root
- **WP-CLI config**: `wp-cli.yml` in root
- **Vagrant triggers**: Defined in `Vagrantfile` for up/halt/suspend/destroy events
- **Service configs**: `config/php-config/`, `config/wordpress-config/`, `config/wp-cli/`
- **Site logs**: `www/[site-name]/log/`