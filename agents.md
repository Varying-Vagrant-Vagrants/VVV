# VVV Development Environment - AI Agent Instructions

**Project:** Varying Vagrant Vagrants (VVV)
**Type:** WordPress Development Environment Infrastructure
**Repository:** https://github.com/Varying-Vagrant-Vagrants/VVV

## Project Context

VVV is a **local WordPress development environment** using Vagrant. Think of it like Docker Compose for WordPress - it's infrastructure that hosts multiple WordPress sites, not a WordPress project itself.

**Critical Concept:** The `www/` folder contains local development sites that are unique to each developer and should NEVER be committed to the VVV repository (similar to Docker volumes).

## File Structure

```
vvv-local/
├── Vagrantfile              # Core Vagrant config (DO NOT MODIFY)
├── .gitignore               # Git exclusions (DO NOT MODIFY)
├── config/
│   └── config.yml           # USER SITE CONFIGURATION (gitignored)
├── provision/               # VVV provisioning scripts (modify only for VVV improvements)
├── www/                     # LOCAL DEVELOPMENT SITES (gitignored except defaults)
│   ├── wordpress-one/       # User's site (gitignored)
│   ├── wordpress-two/       # User's site (gitignored)
│   └── default/             # VVV dashboard (tracked in git)
├── database/                # MySQL data (gitignored)
└── log/                     # Logs (gitignored)
```

## Critical Rules

### 🚨 NEVER Do These Things

1. **NEVER modify `.gitignore`** to allow committing `www/` content
2. **NEVER force commit** using `--no-verify`, `-f`, or bypass git hooks
3. **NEVER commit files from `www/`** (except the specific defaults: `www/vvv-hosts`, `www/default/`)
4. **NEVER create files directly in `www/`** - use `config/config.yml` instead
5. **NEVER modify core files** (`.gitignore`, `Vagrantfile`, `/provision/`) without explicit permission
6. **NEVER run `vagrant init`**

### ✅ ALWAYS Do These Things

1. **ALWAYS check `git check-ignore <path>`** before committing files
2. **ALWAYS review `git status` and `git diff`** before commits
3. **ALWAYS ask yourself:** "Is this VVV infrastructure or site-specific work?"
4. **ALWAYS edit `config/config.yml`** for site configuration, never create files directly
5. **ALWAYS ask the user** if uncertain whether something should be committed

## Common Tasks

### Task: Create a New WordPress Site

**Correct approach:**

```yaml
# Edit: config/config.yml
sites:
  my-new-site:
    repo: https://github.com/Varying-Vagrant-Vagrants/custom-site-template.git
    php: 8.2
    hosts:
      - mynewsite.test
    custom:
      db_name: mynewsite_db
      site_title: "My New Site"
      install_plugins:
        - query-monitor
      wpconfig_constants:
        WP_DEBUG: true
```

Then instruct user: `vagrant up --provision`

**Incorrect approach:**
- ❌ Creating `www/my-new-site/` directory manually
- ❌ Downloading WordPress files
- ❌ Modifying `.gitignore` to track the site
- ❌ Modifying files inside the Virtual Machine to track the site

### Task: Modify a WordPress Theme/Plugin

**Correct approach:**

1. Ask: "Which site in `www/` are you working on?"
2. Navigate to: `www/site-name/public_html/wp-content/themes/theme-name/`
3. Make changes within that directory
4. DO NOT commit to VVV repository (site files are separate)
5. Suggest user initializes git within their theme if they want version control

**Incorrect approach:**
- ❌ Committing theme changes to VVV repository
- ❌ Committing theme changes to custom site template repository
- ❌ Creating pull requests with site-specific code

### Task: Improve VVV Provisioning

**Correct approach:**

1. Review files in `/provision/` directory
2. Make improvements to VVV's core scripts
3. These changes CAN be committed to VVV
4. Create pull request with VVV improvements

**When to commit to VVV:**
- Bug fixes in provisioning scripts
- Documentation improvements
- New VVV features
- CI/CD improvements

## Configuration Format

### Site Configuration Schema

```yaml
sites:
  site-name:
    repo: <git-repo-url>                    # Template repository
    branch: master                          # Git branch (optional)
    php: 8.2                                # PHP version
    skip_provisioning: false                # Skip on vagrant up
    hosts:                                  # Domains
      - site.test
      - www.site.test
    custom:
      # WordPress
      wp_version: latest                    # Or specific: "6.4.2"
      wp_type: single                       # Or: multisite
      locale: en_US
      site_title: "Site Title"

      # Database
      db_name: database_name
      db_prefix: wp_

      # Admin
      admin_user: admin
      admin_password: password
      admin_email: admin@local.test

      # Plugins & Themes
      delete_default_plugins: true
      install_plugins:
        - query-monitor
        - debug-bar
        - https://github.com/user/plugin.zip
      install_themes:
        - twentytwentyfour

      # Constants
      wpconfig_constants:
        WP_DEBUG: true
        WP_DEBUG_LOG: true
        SCRIPT_DEBUG: true
```

## Pre-Commit Checklist

Before any commit or pull request:

- [ ] Run `git status` - no `www/` files appear (except defaults)
- [ ] Run `git diff` - review ALL changes
- [ ] Run `git check-ignore www/test-site` - confirms sites are ignored
- [ ] Verify `.gitignore` is unmodified
- [ ] Ask: "Is this improving VVV or is it site-specific?"
- [ ] If uncertain, ask the user

## What IS Tracked in www/

Only these files in `www/` are committed:
- `www/vvv-hosts` - Default hostname mappings
- `www/default/index.php` - VVV dashboard
- `www/default/phpinfo/index.php` - PHP info page
- `www/default/xdebuginfo/index.php` - Xdebug info page

**Everything else in `www/` is gitignored.**

## Technology Stack

- **Infrastructure:** Vagrant, VirtualBox/Docker/Parallels
- **OS:** Ubuntu Linux (in VM)
- **Web Server:** Nginx
- **Database:** MySQL/MariaDB
- **Languages:** PHP (multiple versions: 7.4, 8.0, 8.1, 8.2, 8.3), Bash
- **Tools:** WP-CLI, Composer, Node.js/npm, Git

## Helpful Commands

```bash
# Provision sites
vagrant up --provision

# SSH into VM
vagrant ssh

# WP-CLI in VM
vagrant ssh -c "wp --info"

# Check if path is gitignored
git check-ignore <path>

# View site configuration
cat config/config.yml
```

## Architecture Principles

1. **VVV is infrastructure, not content** - It provides the environment, not the sites
2. **Configuration over files** - Use `config/config.yml`, not manual file creation
3. **Separation of concerns** - VVV code vs. site code are completely separate
4. **Gitignore by default** - User content stays local unless explicitly tracked

## Testing Changes

When making VVV improvements:

1. Test in a local VVV environment
2. Run `vagrant up --provision` to test provisioning changes
3. Verify changes work across different PHP versions
4. Check that existing sites aren't affected
5. Update documentation if adding features

## Questions to Ask Yourself

- ❓ Am I modifying VVV infrastructure or site-specific content?
- ❓ Would this change benefit all VVV users or just one site?
- ❓ Should this file be in git history?
- ❓ Am I creating files manually when config.yml could do it?
- ❓ Have I checked what's actually going to be committed?

## When Uncertain

**If you're unsure about any of the following, STOP and ask the user:**

- Whether a file should be committed
- Whether a change is VVV core or site-specific
- Whether to modify `.gitignore`, `Vagrantfile`, or provisioning scripts
- Whether something should be in `config/config.yml` or created manually

**Better to ask than to make an inappropriate commit.**

## Summary

| DO ✅ | DON'T ❌ |
|------|---------|
| Edit `config/config.yml` for sites | Create files in `www/` directly |
| Commit VVV infrastructure improvements | Commit site-specific code |
| Use `vagrant up --provision` to create sites | Manually download/install WordPress |
| Work within site directories for development | Commit theme/plugin changes to VVV |
| Ask when uncertain | Modify `.gitignore` to track `www/` |
| Check `git status` before committing | Force commit with `--no-verify` |

## Contributing to VVV

See `.github/CONTRIBUTING.md` for contribution guidelines.

VVV uses:
- Google's Shell Style Guide for Bash scripts
- WordPress coding standards for PHP

All PRs should target the `develop` branch.

---

**Remember:** VVV is like Docker Compose. The `www/` folder is like volumes - it's local data, not the application itself. Keep infrastructure changes separate from content changes.
