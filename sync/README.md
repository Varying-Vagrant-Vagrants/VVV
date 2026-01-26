# Git Deploy Script

Deploy WordPress themes and plugins to SiteGround hosting via SSH + git pull.

## Setup

1. Ensure your SSH key is set up at `~/.ssh/ulg_siteground`
2. Configure `.env` with your SSH credentials (already created)
3. Make sure `jq` is installed: `sudo apt install jq`

## Files

| File | Purpose |
|------|---------|
| `deploy.sh` | Main deployment script |
| `sites.json` | Site configurations (SSH, paths, themes, plugins) |
| `.env` | SSH credentials (not committed to git) |
| `deploy.log` | Error log (not committed to git) |

## Usage

### Interactive Mode

```bash
./deploy.sh
```

This launches an interactive menu:
1. Select a site from the list
2. Choose what to update:
   - Child theme only
   - Parent theme only
   - All themes
   - All plugins
   - Specific plugin
   - Everything

### CLI Mode

```bash
# List all configured sites
./deploy.sh list

# Pull child theme
./deploy.sh pull <site> child

# Pull parent theme
./deploy.sh pull <site> parent

# Pull all themes
./deploy.sh pull <site> themes

# Pull all plugins
./deploy.sh pull <site> plugins

# Pull everything
./deploy.sh pull <site> all

# Pull specific plugin
./deploy.sh pull <site> plugin <plugin-name>
```

### Examples

```bash
./deploy.sh pull ulg child                    # Update ULG child theme
./deploy.sh pull theloft plugins              # Update all Loft plugins
./deploy.sh pull mabellas all                 # Update everything on Mabellas
./deploy.sh pull ulg plugin pegasus-carousel  # Update specific plugin
```

## Configured Sites

| Key | Site | Domain | Child Theme Branch |
|-----|------|--------|-------------------|
| `ulg` | ULG Website | uptownlifegroup.com | `ulg_theme` |
| `ulg-events` | ULG Events | events.uptownlifegroup.com | `ulg_events_theme` |
| `theloft` | The Loft | theloft.com | `theloft2025_theme` |
| `mabellas` | Mabellas | mabellas.com | `mabellas_theme` |
| `saltcellar` | Salt Cellar | saltcellar.com | `saltcellar_theme` |
| `mixmarket` | The Mix Market | themixmarket.com | `mixmarket_theme` |
| `tommygs` | Tommy Gs | tommygs.com | `tommygs_theme` |

## Workflow

1. Make changes locally in VVV
2. Commit and push to GitHub:
   ```bash
   git add . && git commit -m "your changes" && git push
   ```
3. Run deploy script to pull changes on production:
   ```bash
   ./deploy.sh pull ulg child
   ```

## Error Logging

Errors are logged to `deploy.log` with timestamps. Check this file if something fails:

```bash
cat deploy.log
tail -20 deploy.log  # Last 20 lines
```

Log entries include:
- Timestamp
- Error type (ERROR, WARNING, INFO)
- Relevant details (SSH connection info, paths)
- Command output for failed operations

## Adding a New Site

Edit `sites.json` and add a new entry under `sites`:

```json
"new-site": {
  "name": "New Site",
  "domain": "newsite.com",
  "ssh_user": "uXXXX-username",
  "ssh_host": "gvamXXXX.siteground.biz",
  "remote_path": "/home/uXXXX-username/www/newsite.com/public_html",
  "themes": {
    "pegasus": { "branch": "master" },
    "pegasus-child": { "branch": "newsite_theme" }
  },
  "plugins": [
    "pegasus-carousel",
    "wp-bootstrap-hooks"
  ]
}
```

## Troubleshooting

**Connection failed:**
- Check SSH key exists at path in `.env`
- Verify SSH key is added to SiteGround
- Check passphrase is correct

**Not a git repository:**
- The theme/plugin folder doesn't have `.git` directory
- Clone the repo on the remote server first

**Git pull failed:**
- Check `deploy.log` for details
- Common issues: merge conflicts, uncommitted changes on remote
