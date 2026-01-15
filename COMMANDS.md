# VVV Development Environment Commands

This document provides commands for managing the VVV local development environment, including the local server, repository management, and site navigation.

---

## Local Server Commands

### Start the Local Development Server
```bash
# Navigate to project root
cd /home/jim/Projects/vagrant-local/

# Start the server with auto-reload (recommended)
npm run dev

# Or start without auto-reload
node server.js
```

**Server URLs:**
- Main server: http://localhost:3000
- BrowserSync (auto-refresh): http://localhost:3001

### Server Pages
- **Home:** http://localhost:3000/
- **Commands & Repository Guide:** http://localhost:3000/commands
- **Repository Status:** http://localhost:3000/repo-status
- **API Endpoint:** http://localhost:3000/api/repo-status

---

## Repository Scanning & JSON Update Commands

### Rescan All Repositories and Update JSON
```bash
# Run the repository status checker (updates repo-status.json)
cd /home/jim/Projects/vagrant-local/
./check-repo-status.sh
```

This script:
- Scans all Git repositories in `/home/jim/Projects/vagrant-local/www/`
- Checks branch status (current vs default branch)
- Identifies uncommitted changes and untracked files
- Determines if repositories are behind or ahead of remote
- Generates a detailed JSON report at `repo-status.json`

### Update Markdown Documentation (TREE.md & COMMANDS.md)
```bash
cd /home/jim/Projects/vagrant-local/
./update-markdown-docs.sh
```

This script:
- Rebuilds TREE.md with current directory structure
- Rebuilds COMMANDS.md with navigation commands
- Scans for GitHub repositories and local folders
- Generates statistics on pegasus-related content

### Plugin Manager (Interactive)
```bash
# Check and update plugins for a specific site
./plugin-manager.sh /home/jim/Projects/vagrant-local/www/[SITE_NAME]/public_html/wp-content/plugins/

# Example:
./plugin-manager.sh /home/jim/Projects/vagrant-local/www/pegasus/public_html/wp-content/plugins/
```

---

## Vagrant & VVV Core Commands

### Vagrant Operations
```bash
vagrant up                    # Start the VM
vagrant up --provision        # Start VM and run provisioning
vagrant provision             # Run provisioning on existing VM
vagrant halt                  # Stop the VM
vagrant reload                # Restart the VM
vagrant destroy               # Destroy the VM completely
vagrant ssh                   # SSH into the VM
```

### Plugin Management
```bash
vagrant plugin install --local    # Install required plugins locally
```

### Database Operations (via SSH)
```bash
vagrant ssh -c "db_backup"        # Backup all databases
vagrant ssh -c "db_restore"       # Restore databases from backups
```

---

## VVV Features & Extensions

### Enabled Extensions
- **tls-ca** - HTTPS SSL/TLS certificates
- **phpmyadmin** - Web based database client (http://vvv.test/phpmyadmin)

### Available Extensions (can enable in config.yml)
- memcached-admin - Object cache management
- opcache-status - opcache management
- webgrind - PHP Debugging
- mongodb - needed for Tideways/XHGui
- tideways - PHP profiling tool
- nvm - Node Version Manager
- php74, php80, php81, php82, php83, php84, php85 - Additional PHP versions

### VVV Dashboard
- **URL:** http://vvv.test
- **Default IP:** 192.168.56.4

---

## View Repository Status

### JSON Report Queries
```bash
# View the full report
cat repo-status.json

# View formatted JSON
jq '.' repo-status.json

# Check only repositories with issues
jq '.repositories[] | select(.status == "WARNING")' repo-status.json

# Count repositories by status
jq '.repositories | group_by(.status) | map({status: .[0].status, count: length})' repo-status.json

# List repositories behind remote
jq '.repositories[] | select(.behind_remote == true) | .path' repo-status.json

# List repositories with uncommitted changes
jq '.repositories[] | select(.has_uncommitted_changes == true) | .path' repo-status.json
```

---

## Quick Navigation Commands

### WordPress Sites Root Directory
```bash
cd /home/jim/Projects/vagrant-local/www/
```

---

## Site-Specific Navigation

### cadence-group
**Site:** cadence-group.test | **Template:** Varying-Vagrant-Vagrants/custom-site-template
```bash
# Site root
cd /home/jim/Projects/vagrant-local/www/cadence-group/

# Public HTML directory
cd /home/jim/Projects/vagrant-local/www/cadence-group/public_html/

# WordPress content directory
cd /home/jim/Projects/vagrant-local/www/cadence-group/public_html/wp-content/

# Theme directories (GitHub repos)
cd /home/jim/Projects/vagrant-local/www/cadence-group/public_html/wp-content/themes/pegasus/ # https://github.com/Visionquest-Development/pegasus
cd /home/jim/Projects/vagrant-local/www/cadence-group/public_html/wp-content/themes/pegasus-child/ # https://github.com/Visionquest-Development/pegasus-child

# Plugins directory
cd /home/jim/Projects/vagrant-local/www/cadence-group/public_html/wp-content/plugins/

# Plugin GitHub repositories:
cd /home/jim/Projects/vagrant-local/www/cadence-group/public_html/wp-content/plugins/pegasus-carousel/ # https://github.com/Visionquest-Development/pegasus-carousel
cd /home/jim/Projects/vagrant-local/www/cadence-group/public_html/wp-content/plugins/pegasus-slider/ # https://github.com/Visionquest-Development/pegasus-slider
```

### mabellas
**Site:** mabellas.test | **Template:** Varying-Vagrant-Vagrants/custom-site-template
```bash
# Site root
cd /home/jim/Projects/vagrant-local/www/mabellas/

# Public HTML directory
cd /home/jim/Projects/vagrant-local/www/mabellas/public_html/

# WordPress content directory
cd /home/jim/Projects/vagrant-local/www/mabellas/public_html/wp-content/
```

### mamakay
**Site:** mamakay.test | **Template:** Varying-Vagrant-Vagrants/custom-site-template
```bash
# Site root
cd /home/jim/Projects/vagrant-local/www/mamakay/

# Public HTML directory
cd /home/jim/Projects/vagrant-local/www/mamakay/public_html/

# WordPress content directory
cd /home/jim/Projects/vagrant-local/www/mamakay/public_html/wp-content/
```

### mixmarket
**Site:** mixmarket.test | **Template:** Varying-Vagrant-Vagrants/custom-site-template
```bash
# Site root
cd /home/jim/Projects/vagrant-local/www/mixmarket/

# Public HTML directory
cd /home/jim/Projects/vagrant-local/www/mixmarket/public_html/

# WordPress content directory
cd /home/jim/Projects/vagrant-local/www/mixmarket/public_html/wp-content/
```

### ourpalsplace
**Site:** ourpalsplace.test | **Template:** Varying-Vagrant-Vagrants/custom-site-template
```bash
# Site root
cd /home/jim/Projects/vagrant-local/www/ourpalsplace/

# Public HTML directory
cd /home/jim/Projects/vagrant-local/www/ourpalsplace/public_html/

# WordPress content directory
cd /home/jim/Projects/vagrant-local/www/ourpalsplace/public_html/wp-content/

# Theme directories (GitHub repos)
cd /home/jim/Projects/vagrant-local/www/ourpalsplace/public_html/wp-content/themes/pegasus/ # https://github.com/Visionquest-Development/pegasus
cd /home/jim/Projects/vagrant-local/www/ourpalsplace/public_html/wp-content/themes/pegasus-child/ # https://github.com/Visionquest-Development/pegasus-child

# Plugins directory
cd /home/jim/Projects/vagrant-local/www/ourpalsplace/public_html/wp-content/plugins/

# Plugin GitHub repositories:
cd /home/jim/Projects/vagrant-local/www/ourpalsplace/public_html/wp-content/plugins/pegasus-accordion/ # https://github.com/Visionquest-Development/pegasus-accordion
cd /home/jim/Projects/vagrant-local/www/ourpalsplace/public_html/wp-content/plugins/pegasus-carousel/ # https://github.com/Visionquest-Development/pegasus-carousel
cd /home/jim/Projects/vagrant-local/www/ourpalsplace/public_html/wp-content/plugins/pegasus-popup/ # https://github.com/Visionquest-Development/pegasus-popup
cd /home/jim/Projects/vagrant-local/www/ourpalsplace/public_html/wp-content/plugins/pegasus-slider/ # https://github.com/Visionquest-Development/pegasus-slider
cd /home/jim/Projects/vagrant-local/www/ourpalsplace/public_html/wp-content/plugins/pegasus-toggleslide/ # https://github.com/Visionquest-Development/pegasus-toggleslide
cd /home/jim/Projects/vagrant-local/www/ourpalsplace/public_html/wp-content/plugins/wp-bootstrap-hooks/ # https://github.com/benignware/wp-bootstrap-hooks
```

### ourpalsplacedbt
**Site:** ourpalsplacedbt.test | **Template:** Varying-Vagrant-Vagrants/custom-site-template
```bash
# Site root
cd /home/jim/Projects/vagrant-local/www/ourpalsplacedbt/

# Public HTML directory
cd /home/jim/Projects/vagrant-local/www/ourpalsplacedbt/public_html/

# WordPress content directory
cd /home/jim/Projects/vagrant-local/www/ourpalsplacedbt/public_html/wp-content/

# Theme directories (GitHub repos)
cd /home/jim/Projects/vagrant-local/www/ourpalsplacedbt/public_html/wp-content/themes/pegasus/ # https://github.com/Visionquest-Development/pegasus
cd /home/jim/Projects/vagrant-local/www/ourpalsplacedbt/public_html/wp-content/themes/pegasus-child/ # https://github.com/Visionquest-Development/pegasus-child
```

### outlawcoffe
**Site:** outlawcoffe.test | **Template:** Varying-Vagrant-Vagrants/custom-site-template
```bash
# Site root
cd /home/jim/Projects/vagrant-local/www/outlawcoffe/

# Public HTML directory
cd /home/jim/Projects/vagrant-local/www/outlawcoffe/public_html/

# WordPress content directory
cd /home/jim/Projects/vagrant-local/www/outlawcoffe/public_html/wp-content/
```

### outlawcoffee
**Site:** outlawcoffee.test | **Template:** Varying-Vagrant-Vagrants/custom-site-template
```bash
# Site root
cd /home/jim/Projects/vagrant-local/www/outlawcoffee/

# Public HTML directory
cd /home/jim/Projects/vagrant-local/www/outlawcoffee/public_html/

# WordPress content directory
cd /home/jim/Projects/vagrant-local/www/outlawcoffee/public_html/wp-content/
```

### pegasus
**Site:** pegasus.test | **Template:** Varying-Vagrant-Vagrants/custom-site-template
```bash
# Site root
cd /home/jim/Projects/vagrant-local/www/pegasus/

# Public HTML directory
cd /home/jim/Projects/vagrant-local/www/pegasus/public_html/

# WordPress content directory
cd /home/jim/Projects/vagrant-local/www/pegasus/public_html/wp-content/

# Theme directories (GitHub repos)
cd /home/jim/Projects/vagrant-local/www/pegasus/public_html/wp-content/themes/pegasus/ # https://github.com/Visionquest-Development/pegasus
cd /home/jim/Projects/vagrant-local/www/pegasus/public_html/wp-content/themes/pegasus-child/ # https://github.com/Visionquest-Development/pegasus-child

# Plugins directory
cd /home/jim/Projects/vagrant-local/www/pegasus/public_html/wp-content/plugins/

# Plugin GitHub repositories:
cd /home/jim/Projects/vagrant-local/www/pegasus/public_html/wp-content/plugins/pegasus-blog/ # https://github.com/Visionquest-Development/pegasus-blog
cd /home/jim/Projects/vagrant-local/www/pegasus/public_html/wp-content/plugins/pegasus-callout/ # https://github.com/Visionquest-Development/pegasus-callout
cd /home/jim/Projects/vagrant-local/www/pegasus/public_html/wp-content/plugins/pegasus-carousel/ # https://github.com/Visionquest-Development/pegasus-carousel
cd /home/jim/Projects/vagrant-local/www/pegasus/public_html/wp-content/plugins/pegasus-circle-progress/ # https://github.com/Visionquest-Development/pegasus-circle-progress
cd /home/jim/Projects/vagrant-local/www/pegasus/public_html/wp-content/plugins/pegasus-countup/ # https://github.com/Visionquest-Development/pegasus-countup
cd /home/jim/Projects/vagrant-local/www/pegasus/public_html/wp-content/plugins/pegasus-masonry/ # https://github.com/Visionquest-Development/pegasus-masonry
cd /home/jim/Projects/vagrant-local/www/pegasus/public_html/wp-content/plugins/pegasus-navmenu/ # https://github.com/Visionquest-Development/pegasus-navmenu
cd /home/jim/Projects/vagrant-local/www/pegasus/public_html/wp-content/plugins/pegasus-onepage/ # https://github.com/Visionquest-Development/pegasus-onepage
cd /home/jim/Projects/vagrant-local/www/pegasus/public_html/wp-content/plugins/pegasus-packery/ # https://github.com/Visionquest-Development/pegasus-packery
cd /home/jim/Projects/vagrant-local/www/pegasus/public_html/wp-content/plugins/pegasus-popup/ # https://github.com/Visionquest-Development/pegasus-popup
cd /home/jim/Projects/vagrant-local/www/pegasus/public_html/wp-content/plugins/pegasus-postgrid/ # https://github.com/Visionquest-Development/pegasus-postgrid
cd /home/jim/Projects/vagrant-local/www/pegasus/public_html/wp-content/plugins/pegasus-posts-filter/ # https://github.com/Visionquest-Development/pegasus-posts-filter
cd /home/jim/Projects/vagrant-local/www/pegasus/public_html/wp-content/plugins/pegasus-slider/ # https://github.com/Visionquest-Development/pegasus-slider
cd /home/jim/Projects/vagrant-local/www/pegasus/public_html/wp-content/plugins/pegasus-tabs/ # https://github.com/Visionquest-Development/pegasus-tabs
cd /home/jim/Projects/vagrant-local/www/pegasus/public_html/wp-content/plugins/pegasus-toggleslide/ # https://github.com/Visionquest-Development/pegasus-toggleslide
cd /home/jim/Projects/vagrant-local/www/pegasus/public_html/wp-content/plugins/pegasus-wow/ # https://github.com/Visionquest-Development/pegasus-wow
```

### pegasustheme
**Site:** pegasustheme.test | **Template:** Varying-Vagrant-Vagrants/custom-site-template
```bash
# Site root
cd /home/jim/Projects/vagrant-local/www/pegasustheme/

# Public HTML directory
cd /home/jim/Projects/vagrant-local/www/pegasustheme/public_html/

# WordPress content directory
cd /home/jim/Projects/vagrant-local/www/pegasustheme/public_html/wp-content/

# Theme directories (GitHub repos)
cd /home/jim/Projects/vagrant-local/www/pegasustheme/public_html/wp-content/themes/pegasus/ # https://github.com/Visionquest-Development/pegasus
cd /home/jim/Projects/vagrant-local/www/pegasustheme/public_html/wp-content/themes/pegasus-child/ # https://github.com/Visionquest-Development/pegasus-child

# Plugins directory
cd /home/jim/Projects/vagrant-local/www/pegasustheme/public_html/wp-content/plugins/

# Plugin GitHub repositories:
cd /home/jim/Projects/vagrant-local/www/pegasustheme/public_html/wp-content/plugins/octane-booster/ # https://github.com/OctaneAgency/octane-booster
cd /home/jim/Projects/vagrant-local/www/pegasustheme/public_html/wp-content/plugins/octane-slider/ # https://github.com/OctaneAgency/octane-slider
cd /home/jim/Projects/vagrant-local/www/pegasustheme/public_html/wp-content/plugins/pegasus-blog/ # https://github.com/Visionquest-Development/pegasus-blog
cd /home/jim/Projects/vagrant-local/www/pegasustheme/public_html/wp-content/plugins/pegasus-callout/ # https://github.com/Visionquest-Development/pegasus-callout
cd /home/jim/Projects/vagrant-local/www/pegasustheme/public_html/wp-content/plugins/pegasus-carousel/ # https://github.com/Visionquest-Development/pegasus-carousel
cd /home/jim/Projects/vagrant-local/www/pegasustheme/public_html/wp-content/plugins/pegasus-circle-progress/ # https://github.com/Visionquest-Development/pegasus-circle-progress
cd /home/jim/Projects/vagrant-local/www/pegasustheme/public_html/wp-content/plugins/pegasus-countup/ # https://github.com/Visionquest-Development/pegasus-countup
cd /home/jim/Projects/vagrant-local/www/pegasustheme/public_html/wp-content/plugins/pegasus-masonry/ # https://github.com/Visionquest-Development/pegasus-masonry
cd /home/jim/Projects/vagrant-local/www/pegasustheme/public_html/wp-content/plugins/pegasus-navmenu/ # https://github.com/Visionquest-Development/pegasus-navmenu
cd /home/jim/Projects/vagrant-local/www/pegasustheme/public_html/wp-content/plugins/pegasus-onepage/ # https://github.com/Visionquest-Development/pegasus-onepage
cd /home/jim/Projects/vagrant-local/www/pegasustheme/public_html/wp-content/plugins/pegasus-packery/ # https://github.com/Visionquest-Development/pegasus-packery
cd /home/jim/Projects/vagrant-local/www/pegasustheme/public_html/wp-content/plugins/pegasus-popup/ # https://github.com/Visionquest-Development/pegasus-popup
cd /home/jim/Projects/vagrant-local/www/pegasustheme/public_html/wp-content/plugins/pegasus-post-grid/ # https://github.com/Visionquest-Development/pegasus-postgrid
cd /home/jim/Projects/vagrant-local/www/pegasustheme/public_html/wp-content/plugins/pegasus-posts-filter/ # https://github.com/Visionquest-Development/pegasus-posts-filter
cd /home/jim/Projects/vagrant-local/www/pegasustheme/public_html/wp-content/plugins/pegasus-slider/ # https://github.com/Visionquest-Development/pegasus-slider
cd /home/jim/Projects/vagrant-local/www/pegasustheme/public_html/wp-content/plugins/pegasus-tabs/ # https://github.com/Visionquest-Development/pegasus-tabs
cd /home/jim/Projects/vagrant-local/www/pegasustheme/public_html/wp-content/plugins/pegasus-toggleslide/ # https://github.com/Visionquest-Development/pegasus-toggleslide
cd /home/jim/Projects/vagrant-local/www/pegasustheme/public_html/wp-content/plugins/pegasus-wow/ # https://github.com/Visionquest-Development/pegasus-wow
```

### pegasustwo
**Site:** pegasustwo.test | **Template:** Varying-Vagrant-Vagrants/custom-site-template
```bash
# Site root
cd /home/jim/Projects/vagrant-local/www/pegasustwo/

# Public HTML directory
cd /home/jim/Projects/vagrant-local/www/pegasustwo/public_html/

# WordPress content directory
cd /home/jim/Projects/vagrant-local/www/pegasustwo/public_html/wp-content/

# Theme directories (GitHub repos)
cd /home/jim/Projects/vagrant-local/www/pegasustwo/public_html/wp-content/themes/pegasus/ # https://github.com/Visionquest-Development/pegasus
```

### qbiqcamp
**Site:** qbiqcamp.test | **Template:** Varying-Vagrant-Vagrants/custom-site-template
```bash
# Site root
cd /home/jim/Projects/vagrant-local/www/qbiqcamp/

# Public HTML directory
cd /home/jim/Projects/vagrant-local/www/qbiqcamp/public_html/

# WordPress content directory
cd /home/jim/Projects/vagrant-local/www/qbiqcamp/public_html/wp-content/

# Theme directories (GitHub repos)
cd /home/jim/Projects/vagrant-local/www/qbiqcamp/public_html/wp-content/themes/pegasus/ # https://github.com/Visionquest-Development/pegasus
cd /home/jim/Projects/vagrant-local/www/qbiqcamp/public_html/wp-content/themes/pegasus-child/ # https://github.com/Visionquest-Development/pegasus-child

# Plugins directory
cd /home/jim/Projects/vagrant-local/www/qbiqcamp/public_html/wp-content/plugins/

# Plugin GitHub repositories:
cd /home/jim/Projects/vagrant-local/www/qbiqcamp/public_html/wp-content/plugins/wp-bootstrap-hooks/ # https://github.com/benignware/wp-bootstrap-hooks
```

### russelcontracting
**Site:** russelcontracting.test | **Template:** Varying-Vagrant-Vagrants/custom-site-template
```bash
# Site root
cd /home/jim/Projects/vagrant-local/www/russelcontracting/

# Public HTML directory
cd /home/jim/Projects/vagrant-local/www/russelcontracting/public_html/

# WordPress content directory
cd /home/jim/Projects/vagrant-local/www/russelcontracting/public_html/wp-content/
```

### sagecnc
**Site:** sagecnc.test | **Template:** Varying-Vagrant-Vagrants/custom-site-template
```bash
# Site root
cd /home/jim/Projects/vagrant-local/www/sagecnc/

# Public HTML directory
cd /home/jim/Projects/vagrant-local/www/sagecnc/public_html/

# WordPress content directory
cd /home/jim/Projects/vagrant-local/www/sagecnc/public_html/wp-content/

# Theme directories (GitHub repos)
cd /home/jim/Projects/vagrant-local/www/sagecnc/public_html/wp-content/themes/pegasus-child/ # https://github.com/Visionquest-Development/pegasus-child
```

### saltcellar
**Site:** saltcellar.test | **Template:** Varying-Vagrant-Vagrants/custom-site-template
```bash
# Site root
cd /home/jim/Projects/vagrant-local/www/saltcellar/

# Public HTML directory
cd /home/jim/Projects/vagrant-local/www/saltcellar/public_html/

# WordPress content directory
cd /home/jim/Projects/vagrant-local/www/saltcellar/public_html/wp-content/
```

### sugarpeddler
**Site:** sugarpeddler.test | **Template:** Varying-Vagrant-Vagrants/custom-site-template
```bash
# Site root
cd /home/jim/Projects/vagrant-local/www/sugarpeddler/

# Public HTML directory
cd /home/jim/Projects/vagrant-local/www/sugarpeddler/public_html/

# WordPress content directory
cd /home/jim/Projects/vagrant-local/www/sugarpeddler/public_html/wp-content/
```

### theloft
**Site:** theloft.test | **Template:** Varying-Vagrant-Vagrants/custom-site-template
```bash
# Site root
cd /home/jim/Projects/vagrant-local/www/theloft/

# Public HTML directory
cd /home/jim/Projects/vagrant-local/www/theloft/public_html/

# WordPress content directory
cd /home/jim/Projects/vagrant-local/www/theloft/public_html/wp-content/

# Theme directories (GitHub repos)
cd /home/jim/Projects/vagrant-local/www/theloft/public_html/wp-content/themes/pegasus/ # https://github.com/Visionquest-Development/pegasus
cd /home/jim/Projects/vagrant-local/www/theloft/public_html/wp-content/themes/pegasus-child/ # https://github.com/Visionquest-Development/pegasus-child
```

### theloftnew
**Site:** theloftnew.test | **Template:** Varying-Vagrant-Vagrants/custom-site-template
```bash
# Site root
cd /home/jim/Projects/vagrant-local/www/theloftnew/

# Public HTML directory
cd /home/jim/Projects/vagrant-local/www/theloftnew/public_html/

# WordPress content directory
cd /home/jim/Projects/vagrant-local/www/theloftnew/public_html/wp-content/
```

### tommygs
**Site:** tommygs.test | **Template:** Varying-Vagrant-Vagrants/custom-site-template
```bash
# Site root
cd /home/jim/Projects/vagrant-local/www/tommygs/

# Public HTML directory
cd /home/jim/Projects/vagrant-local/www/tommygs/public_html/

# WordPress content directory
cd /home/jim/Projects/vagrant-local/www/tommygs/public_html/wp-content/
```

### ulgevents
**Site:** ulgevents.test | **Template:** Varying-Vagrant-Vagrants/custom-site-template
```bash
# Site root
cd /home/jim/Projects/vagrant-local/www/ulgevents/

# Public HTML directory
cd /home/jim/Projects/vagrant-local/www/ulgevents/public_html/

# WordPress content directory
cd /home/jim/Projects/vagrant-local/www/ulgevents/public_html/wp-content/
```

### uptownlifegroup
**Site:** uptownlifegroup.test | **Template:** Varying-Vagrant-Vagrants/custom-site-template
```bash
# Site root
cd /home/jim/Projects/vagrant-local/www/uptownlifegroup/

# Public HTML directory
cd /home/jim/Projects/vagrant-local/www/uptownlifegroup/public_html/

# WordPress content directory
cd /home/jim/Projects/vagrant-local/www/uptownlifegroup/public_html/wp-content/

# Theme directories (GitHub repos)
cd /home/jim/Projects/vagrant-local/www/uptownlifegroup/public_html/wp-content/themes/pegasus/ # https://github.com/Visionquest-Development/pegasus
cd /home/jim/Projects/vagrant-local/www/uptownlifegroup/public_html/wp-content/themes/pegasus-child/ # https://github.com/Visionquest-Development/pegasus-child

# Plugins directory
cd /home/jim/Projects/vagrant-local/www/uptownlifegroup/public_html/wp-content/plugins/

# Plugin GitHub repositories:
cd /home/jim/Projects/vagrant-local/www/uptownlifegroup/public_html/wp-content/plugins/pegasus-carousel/ # https://github.com/Visionquest-Development/pegasus-carousel
cd /home/jim/Projects/vagrant-local/www/uptownlifegroup/public_html/wp-content/plugins/pegasus-slider/ # https://github.com/Visionquest-Development/pegasus-slider
cd /home/jim/Projects/vagrant-local/www/uptownlifegroup/public_html/wp-content/plugins/pegasus-tabs/ # https://github.com/Visionquest-Development/pegasus-tabs
cd /home/jim/Projects/vagrant-local/www/uptownlifegroup/public_html/wp-content/plugins/pegasus-toggleslide/ # https://github.com/Visionquest-Development/pegasus-toggleslide
cd /home/jim/Projects/vagrant-local/www/uptownlifegroup/public_html/wp-content/plugins/pegasus-wow/ # https://github.com/Visionquest-Development/pegasus-wow
```

### vineandvision
**Site:** vineandvision.test | **Template:** Varying-Vagrant-Vagrants/custom-site-template
```bash
# Site root
cd /home/jim/Projects/vagrant-local/www/vineandvision/

# Public HTML directory
cd /home/jim/Projects/vagrant-local/www/vineandvision/public_html/

# WordPress content directory
cd /home/jim/Projects/vagrant-local/www/vineandvision/public_html/wp-content/
```

### visionquestgoods
**Site:** visionquestgoods.test | **Template:** Varying-Vagrant-Vagrants/custom-site-template
```bash
# Site root
cd /home/jim/Projects/vagrant-local/www/visionquestgoods/

# Public HTML directory
cd /home/jim/Projects/vagrant-local/www/visionquestgoods/public_html/

# WordPress content directory
cd /home/jim/Projects/vagrant-local/www/visionquestgoods/public_html/wp-content/
```

---

## Common Directory Patterns

```bash
# For sites with custom themes:
cd /home/jim/Projects/vagrant-local/www/[SITE_NAME]/public_html/wp-content/themes/pegasus/

# For WordPress content:
cd /home/jim/Projects/vagrant-local/www/[SITE_NAME]/public_html/wp-content/

# For plugins:
cd /home/jim/Projects/vagrant-local/www/[SITE_NAME]/public_html/wp-content/plugins/

# For specific plugin:
cd /home/jim/Projects/vagrant-local/www/[SITE_NAME]/public_html/wp-content/plugins/[PLUGIN_NAME]/

# For site root:
cd /home/jim/Projects/vagrant-local/www/[SITE_NAME]/
```

---

## Directory Tree Structure

```
/home/jim/Projects/vagrant-local/www/
├── cadence-group/
│   └── public_html/wp-content/
│       ├── themes/
│       │   ├── pegasus/ [Visionquest-Development/pegasus]
│       │   └── pegasus-child/ [Visionquest-Development/pegasus-child]
│       └── plugins/
│           ├── pegasus-carousel/ [Visionquest-Development/pegasus-carousel]
│           └── pegasus-slider/ [Visionquest-Development/pegasus-slider]
├── mabellas/
├── mamakay/
├── mixmarket/
├── ourpalsplace/
│   └── public_html/wp-content/
│       ├── themes/
│       │   ├── pegasus/ [Visionquest-Development/pegasus]
│       │   └── pegasus-child/ [Visionquest-Development/pegasus-child]
│       └── plugins/
│           ├── pegasus-accordion/ [Visionquest-Development/pegasus-accordion]
│           ├── pegasus-carousel/ [Visionquest-Development/pegasus-carousel]
│           ├── pegasus-popup/ [Visionquest-Development/pegasus-popup]
│           ├── pegasus-slider/ [Visionquest-Development/pegasus-slider]
│           ├── pegasus-toggleslide/ [Visionquest-Development/pegasus-toggleslide]
│           └── wp-bootstrap-hooks/ [benignware/wp-bootstrap-hooks]
├── ourpalsplacedbt/
│   └── public_html/wp-content/themes/
│       ├── pegasus/ [Visionquest-Development/pegasus]
│       └── pegasus-child/ [Visionquest-Development/pegasus-child]
├── outlawcoffe/
├── outlawcoffee/
├── pegasus/
│   └── public_html/wp-content/
│       ├── themes/
│       │   ├── pegasus/ [Visionquest-Development/pegasus]
│       │   └── pegasus-child/ [Visionquest-Development/pegasus-child]
│       └── plugins/
│           ├── pegasus-blog/ [Visionquest-Development/pegasus-blog]
│           ├── pegasus-callout/ [Visionquest-Development/pegasus-callout]
│           ├── pegasus-carousel/ [Visionquest-Development/pegasus-carousel]
│           ├── pegasus-circle-progress/ [Visionquest-Development/pegasus-circle-progress]
│           ├── pegasus-countup/ [Visionquest-Development/pegasus-countup]
│           ├── pegasus-masonry/ [Visionquest-Development/pegasus-masonry]
│           ├── pegasus-navmenu/ [Visionquest-Development/pegasus-navmenu]
│           ├── pegasus-onepage/ [Visionquest-Development/pegasus-onepage]
│           ├── pegasus-packery/ [Visionquest-Development/pegasus-packery]
│           ├── pegasus-popup/ [Visionquest-Development/pegasus-popup]
│           ├── pegasus-postgrid/ [Visionquest-Development/pegasus-postgrid]
│           ├── pegasus-posts-filter/ [Visionquest-Development/pegasus-posts-filter]
│           ├── pegasus-slider/ [Visionquest-Development/pegasus-slider]
│           ├── pegasus-tabs/ [Visionquest-Development/pegasus-tabs]
│           ├── pegasus-toggleslide/ [Visionquest-Development/pegasus-toggleslide]
│           └── pegasus-wow/ [Visionquest-Development/pegasus-wow]
├── pegasustheme/
│   └── public_html/wp-content/
│       ├── themes/
│       │   ├── pegasus/ [Visionquest-Development/pegasus]
│       │   └── pegasus-child/ [Visionquest-Development/pegasus-child]
│       └── plugins/
│           ├── octane-booster/ [OctaneAgency/octane-booster]
│           ├── octane-slider/ [OctaneAgency/octane-slider]
│           └── pegasus-*/ (18 plugins)
├── pegasustwo/
│   └── public_html/wp-content/themes/pegasus/
├── qbiqcamp/
│   └── public_html/wp-content/
│       ├── themes/
│       │   ├── pegasus/ [Visionquest-Development/pegasus]
│       │   └── pegasus-child/ [Visionquest-Development/pegasus-child]
│       └── plugins/wp-bootstrap-hooks/ [benignware/wp-bootstrap-hooks]
├── russelcontracting/
├── sagecnc/
│   └── public_html/wp-content/themes/pegasus-child/
├── saltcellar/
├── sugarpeddler/
├── theloft/
│   └── public_html/wp-content/themes/
│       ├── pegasus/ [Visionquest-Development/pegasus]
│       └── pegasus-child/ [Visionquest-Development/pegasus-child]
├── theloftnew/
├── tommygs/
├── ulgevents/
├── uptownlifegroup/
│   └── public_html/wp-content/
│       ├── themes/
│       │   ├── pegasus/ [Visionquest-Development/pegasus]
│       │   └── pegasus-child/ [Visionquest-Development/pegasus-child]
│       └── plugins/
│           ├── pegasus-carousel/ [Visionquest-Development/pegasus-carousel]
│           ├── pegasus-slider/ [Visionquest-Development/pegasus-slider]
│           ├── pegasus-tabs/ [Visionquest-Development/pegasus-tabs]
│           ├── pegasus-toggleslide/ [Visionquest-Development/pegasus-toggleslide]
│           └── pegasus-wow/ [Visionquest-Development/pegasus-wow]
├── vineandvision/
└── visionquestgoods/
```

---

## Site Status Summary

- **Total sites:** 27 WordPress sites configured
- **Sites with Pegasus themes:** 12+ sites using Pegasus theme
- **GitHub Organizations:**
  - **Visionquest-Development:** 50+ repositories
  - **OctaneAgency:** 2 repositories
  - **benignware:** 2 repositories (wp-bootstrap-hooks)

---

## Repository Management Scripts

### check-repo-status.sh
Scans all Git repositories for branch status, uncommitted changes, and remote sync status.

```bash
./check-repo-status.sh
```

**Output:** `repo-status.json`

### update-markdown-docs.sh
Updates TREE.md and COMMANDS.md with latest repository information.

```bash
./update-markdown-docs.sh
```

### plugin-manager.sh
Interactive script for managing WordPress plugin repositories.

```bash
./plugin-manager.sh /path/to/plugins/
```

---

## Configuration Files

| File | Purpose |
|------|---------|
| `config/config.yml` | Main VVV configuration (sites, extensions) |
| `repo-status.json` | Repository scan results (auto-generated) |
| `TREE.md` | Directory tree documentation |
| `COMMANDS.md` | This file - command reference |
| `CLAUDE.md` | AI assistant instructions |
| `server.js` | Local development server |
| `package.json` | Node.js dependencies |

---

*Last updated: 2026-01-14*
