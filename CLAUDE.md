# VVV Development Environment Instructions for LLM Agents

This document provides critical guidance for AI coding assistants working with Varying Vagrant Vagrants (VVV). **Read and follow these instructions carefully to avoid inappropriate changes.**

## 🚨 Critical Boundaries - NEVER Cross These Lines

### DO NOT Modify These Core Files
- **`.gitignore`** - NEVER modify to allow committing `www/` content or other gitignored paths
- **`Vagrantfile`** - Core Vagrant configuration, do not modify without explicit permission
- **`/provision/`** scripts - Core provisioning logic (only modify for VVV improvements, not site work)
- **Git-tracked files** - Assume files tracked by git are VVV core files unless explicitly told otherwise

### Git Safety Rules - Follow These Strictly
1. **NEVER modify `.gitignore`** to allow committing `www/` content
2. **NEVER force commit** using `--no-verify`, `-f`, or similar flags
3. **ALWAYS check `git check-ignore <path>`** before committing files to verify they should be tracked
4. **If files in `www/` appear in git status** - they should almost certainly NOT be committed (except the defaults listed below)
5. **Ask before committing** if you're uncertain whether a file belongs in the VVV repository

## What is VVV?

VVV (Varying Vagrant Vagrants) is a **WordPress development environment** using Vagrant. It's infrastructure, not a project itself.

**Key Concept:** VVV is like Docker Compose for WordPress development. The `www/` folder is like Docker volumes - it contains local development sites that are **unique to each developer** and **should never be shared** via the VVV repository.

### Architecture
- **`www/` folder** - Contains local development WordPress sites (gitignored, except specific defaults)
- **`config/config.yml`** - User's site configuration (gitignored)
- **`provision/`** - Provisioning scripts that set up the Vagrant environment
- **`database/`** - MySQL data (gitignored)
- **`log/`** - Log files (gitignored)

## Working with WordPress Sites - The Correct Way

### Creating a New Site

When asked to create a new WordPress site:

**✓ CORRECT approach:**
1. Edit `/config/config.yml` (or create it from `config/default-config.yml` template)
2. Add a site configuration:
```yaml
sites:
  my-site-name:
    repo: https://github.com/Varying-Vagrant-Vagrants/custom-site-template.git
    php: 8.2
    hosts:
      - mysite.test
    custom:
      db_name: my_database
      site_title: "My Site"
      admin_user: admin
      admin_password: password
      install_plugins:
        - query-monitor
        - debug-bar
      wpconfig_constants:
        WP_DEBUG: true
        WP_DEBUG_LOG: true
```
3. Instruct user to run: `vagrant up --provision`
4. VVV will automatically create the site in `www/my-site-name/`

**✗ INCORRECT approach:**
- ❌ Creating directories directly in `www/my-site-name/`
- ❌ Downloading and copying WordPress files manually
- ❌ Modifying `.gitignore` to track the new site
- ❌ Committing site files to VVV repository
- ❌ Committing site files to site template repository

### Modifying an Existing Site

When asked to work on a WordPress site (themes, plugins, content):

**✓ CORRECT approach:**
1. Identify which site: "Which site in `www/` are you working on?"
2. Navigate to that site's directory: `www/site-name/public_html/`
3. Work within that site's directory structure:
   - Themes: `www/site-name/public_html/wp-content/themes/`
   - Plugins: `www/site-name/public_html/wp-content/plugins/`
   - Uploads: `www/site-name/public_html/wp-content/uploads/`
4. **DO NOT** offer to commit these changes to VVV repository
5. If user wants version control, suggest initializing git within their theme/plugin folder

**✗ INCORRECT approach:**
- ❌ Committing theme/plugin changes to VVV repository
- ❌ Creating pull requests with site-specific code
- ❌ Modifying VVV core to accommodate site-specific needs

### Site Configuration Options

The `custom:` section in `config/config.yml` supports extensive configuration:

```yaml
custom:
  # WordPress installation
  wp_version: latest              # Or specific version like "6.4.2"
  wp_type: single                 # Or "multisite"
  locale: en_US                   # WordPress language

  # Database
  db_name: my_database
  db_prefix: wp_

  # Admin user
  admin_user: admin
  admin_password: password
  admin_email: admin@local.test
  site_title: "My Site Title"

  # Plugins & themes
  delete_default_plugins: true    # Remove Akismet and Hello Dolly
  install_plugins:
    - query-monitor
    - debug-bar
    - https://github.com/user/plugin-repo/archive/main.zip
  install_themes:
    - twentytwentyfour

  # wp-config.php constants
  wpconfig_constants:
    WP_DEBUG: true
    WP_DEBUG_LOG: true
    WP_DEBUG_DISPLAY: false
    SCRIPT_DEBUG: true
```

## What CAN Be Committed to VVV

Only these types of changes are appropriate for VVV pull requests:
- 🐛 Bug fixes in VVV core provisioning scripts
- 📚 Documentation improvements
- ✨ New features for VVV itself (not sites)
- ⚙️ Default configuration updates
- 🔧 CI/CD improvements
- 🛠️ Improvements to VVV utilities and tools

## Files That ARE Tracked in www/

Only these specific files in `www/` are committed to VVV:
- `www/default/` directory - VVV dashboard and utilities:
  - `www/default/index.php` - VVV dashboard page
  - `www/default/phpinfo/index.php` - PHP version info page
  - `www/default/xdebuginfo/index.php` - Xdebug configuration page

**Everything else in `www/` is gitignored** - this includes all user sites like:
- `www/wordpress-one/`
- `www/wordpress-two/`
- `www/my-custom-site/`
- etc.

## Pre-Commit Checklist

**Before creating commits or pull requests, ALWAYS:**

1. ✅ Run `git status` - verify no files from `www/` appear (except the defaults listed above)
2. ✅ Run `git diff` - review ALL changes to ensure nothing from user sites is included
3. ✅ Check `git check-ignore www/my-site` - should confirm the path is ignored
4. ✅ Verify `.gitignore` has not been modified to allow `www/` content
5. ✅ Ask yourself: "Is this change improving VVV itself, or is it site-specific work?"
6. ✅ If uncertain, **ask the user** before committing

## Example Scenarios

### Scenario 1: "Create a new WordPress site called myproject"

**✓ CORRECT Response:**
```
I'll help you create a new site in VVV. You need to add it to your config/config.yml file:

sites:
  myproject:
    repo: https://github.com/Varying-Vagrant-Vagrants/custom-site-template.git
    php: 8.2
    hosts:
      - myproject.test

After adding this, run: vagrant up --provision

This will create the site at www/myproject/ and set it up automatically.
```

**✗ INCORRECT Response:**
- Creating directories in `www/myproject/`
- Downloading WordPress with wget/curl
- Modifying `.gitignore` to track the new site
- Offering to commit the site to VVV

### Scenario 2: "Fix a bug in my site's theme"

**✓ CORRECT Response:**
```
I'll help fix the bug in your theme. Which site in www/ are you working on?

[After user responds: www/myproject/]

Let me look at your theme files in www/myproject/public_html/wp-content/themes/.

[Make the fix]

Note: These theme changes are specific to your site and shouldn't be committed to the VVV repository. If you want to version control your theme, consider initializing a git repository within your theme folder.
```

**✗ INCORRECT Response:**
- Offering to commit theme changes to VVV
- Creating a pull request with site-specific code
- Modifying VVV core to fix a site-specific issue

### Scenario 3: "Improve VVV's provisioning for PHP 8.3"

**✓ CORRECT Response:**
```
I'll help improve VVV's PHP 8.3 provisioning. Let me review the provisioning scripts in the /provision/ directory.

[Review and make improvements to VVV core scripts]

These changes improve VVV itself and are appropriate for a pull request to the VVV repository.
```

This is a legitimate VVV improvement and CAN be committed.

### Scenario 4: "Install the Yoast SEO plugin on my site"

**✓ CORRECT Response:**
```
I'll help you install Yoast SEO. Add this to the site's configuration in config/config.yml:

sites:
  your-site:
    custom:
      install_plugins:
        - wordpress-seo

Then run: vagrant up --provision

Alternatively, you can SSH into the VM and use WP-CLI:
vagrant ssh -c "wp plugin install wordpress-seo --activate --path=/srv/www/your-site/public_html"
```

**✗ INCORRECT Response:**
- Downloading the plugin ZIP file to `www/site/public_html/wp-content/plugins/`
- Offering to commit the plugin to VVV repository

## Site Development Workflow Summary

When asked to work on a WordPress site in VVV:

1. **Identify the site** - Ask which site in `www/` if unclear
2. **Navigate to site directory** - e.g., `www/my-site/public_html/`
3. **Work ONLY within that site** - Themes, plugins, content, etc.
4. **NEVER commit site changes to VVV** - Site work is separate from VVV work
5. **Separate repositories** - If the site has its own git repo, that's completely separate from VVV's git repo

## Advanced: Site Provisioning

Each site can have custom provisioning scripts:

**Site structure:**
```
www/my-site/
├── provision/
│   ├── vvv-init.sh          # Custom provisioning script
│   └── vvv-nginx.conf       # Custom Nginx configuration
├── public_html/             # WordPress root
├── log/                     # Site-specific logs
└── wp-cli.yml              # WP-CLI configuration
```

**Helper functions available in vvv-init.sh:**
- `get_config_value 'key' 'default'` - Get custom config values
- `get_primary_host` - Get the first domain
- `noroot` - Run commands without root
- `wp` - WP-CLI commands

## VVV Extensions

System-level tools are installed via extensions in `config/config.yml`:

```yaml
extensions:
  core:
    - tls-ca              # HTTPS certificates
    - phpmyadmin          # Database management
    - memcached-admin     # Cache management
    - php81               # Additional PHP versions
    - nvm                 # Node Version Manager
```

## When You're Unsure

**If you're uncertain about ANY of these:**
- Whether a file should be committed
- Whether a change affects VVV core or a site
- Whether to modify `.gitignore`
- Whether to modify provisioning scripts

**STOP and ask the user for clarification.** It's better to ask than to make an inappropriate commit.

## Summary of Responsibilities

| Your Responsibility | NOT Your Responsibility |
|-------------------|------------------------|
| Help edit `config/config.yml` | Create files in `www/` directly |
| Suggest proper VVV configuration | Commit site-specific code to VVV |
| Fix bugs in VVV core scripts | Modify `.gitignore` for sites |
| Improve VVV documentation | Force commit gitignored files |
| Guide users on VVV usage | Make changes without understanding impact |

## Questions to Ask Yourself

Before making any changes:
1. ❓ Is this improving VVV infrastructure, or is it site-specific work?
2. ❓ Would this change benefit all VVV users, or just this specific site?
3. ❓ Am I modifying VVV core files when I should be editing user configuration?
4. ❓ Will this change appear in `git status`? Should it?
5. ❓ Have I checked `git check-ignore` for any new files?

---

**Remember:** VVV is infrastructure. Sites are content. Infrastructure changes go to VVV repository. Site changes stay local.
