#!/bin/bash

# =============================================================================
# Add Site to SiteGround Sync Configuration
# Interactive script to add a new site to sites.json
# =============================================================================

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CONFIG_FILE="$SCRIPT_DIR/sites.json"
VVV_ROOT="/home/jim/Projects/vagrant-local"

print_header() {
    echo -e "\n${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${CYAN}  $1${NC}"
    echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}\n"
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

# Check dependencies
if ! command -v jq &> /dev/null; then
    print_error "jq is required but not installed. Install with: sudo apt install jq"
    exit 1
fi

print_header "Add New Site to SiteGround Sync"

# Show available VVV sites
echo -e "${CYAN}Available VVV sites:${NC}"
ls -1 "$VVV_ROOT/www/" | grep -v "^default$\|^wordpress-one$\|^wordpress-two$\|^phpcs$" | nl
echo ""

# Get local site name
read -p "Enter local VVV site name (e.g., uptownlifegroup): " LOCAL_NAME

if [ ! -d "$VVV_ROOT/www/$LOCAL_NAME" ]; then
    print_error "Site not found: $VVV_ROOT/www/$LOCAL_NAME"
    exit 1
fi

# Get remote domain
read -p "Enter remote domain (e.g., uptownlifegroup.com): " REMOTE_DOMAIN

# Get SSH details
echo ""
echo -e "${CYAN}Enter SSH details (from SiteGround Site Tools → Devs → SSH Keys Manager):${NC}"
echo -e "${YELLOW}Example: ssh u2337-sdvymoa8u5vg@gvam1201.siteground.biz -p 18765${NC}"
echo ""

read -p "SSH username (e.g., u2337-sdvymoa8u5vg): " SSH_USER
read -p "SSH host (e.g., gvam1201.siteground.biz): " SSH_HOST
read -p "SSH port [18765]: " SSH_PORT
SSH_PORT=${SSH_PORT:-18765}

# Remote path
echo ""
echo -e "${CYAN}Remote WordPress path (relative to home directory):${NC}"
read -p "Remote path [www/$REMOTE_DOMAIN/public_html]: " REMOTE_PATH
REMOTE_PATH=${REMOTE_PATH:-"www/$REMOTE_DOMAIN/public_html"}

# Get themes to sync
echo ""
echo -e "${CYAN}Available themes in $LOCAL_NAME:${NC}"
ls -1 "$VVV_ROOT/www/$LOCAL_NAME/public_html/wp-content/themes/" 2>/dev/null | nl
echo ""
read -p "Enter themes to sync (comma-separated, e.g., pegasus,pegasus-child): " THEMES_INPUT
IFS=',' read -ra THEMES_ARRAY <<< "$THEMES_INPUT"

# Get plugins to sync
echo ""
echo -e "${CYAN}Available plugins in $LOCAL_NAME (showing first 20):${NC}"
ls -1 "$VVV_ROOT/www/$LOCAL_NAME/public_html/wp-content/plugins/" 2>/dev/null | head -20 | nl
echo ""
echo -e "${YELLOW}Tip: Usually sync custom plugins like pegasus-*, your premium plugins, etc.${NC}"
read -p "Enter plugins to sync (comma-separated): " PLUGINS_INPUT
IFS=',' read -ra PLUGINS_ARRAY <<< "$PLUGINS_INPUT"

# Build JSON arrays
THEMES_JSON=$(printf '%s\n' "${THEMES_ARRAY[@]}" | jq -R . | jq -s .)
PLUGINS_JSON=$(printf '%s\n' "${PLUGINS_ARRAY[@]}" | jq -R . | jq -s .)

# Create the new site entry
NEW_SITE=$(cat <<EOF
{
  "local_name": "$LOCAL_NAME",
  "remote_domain": "$REMOTE_DOMAIN",
  "ssh_user": "$SSH_USER",
  "ssh_host": "$SSH_HOST",
  "ssh_port": $SSH_PORT,
  "remote_path": "$REMOTE_PATH",
  "sync_themes": $THEMES_JSON,
  "sync_plugins": $PLUGINS_JSON,
  "exclude_patterns": [".git", ".gitignore", "node_modules", ".DS_Store", "*.log", ".sass-cache"],
  "enabled": true
}
EOF
)

# Show summary
print_header "Configuration Summary"
echo -e "${CYAN}Site Name:${NC} $LOCAL_NAME"
echo -e "${CYAN}Remote Domain:${NC} $REMOTE_DOMAIN"
echo -e "${CYAN}SSH Connection:${NC} $SSH_USER@$SSH_HOST:$SSH_PORT"
echo -e "${CYAN}Remote Path:${NC} ~/$REMOTE_PATH"
echo -e "${CYAN}Themes:${NC} ${THEMES_ARRAY[*]}"
echo -e "${CYAN}Plugins:${NC} ${PLUGINS_ARRAY[*]}"
echo ""

read -p "Add this site to configuration? (y/n) [y]: " CONFIRM
CONFIRM=${CONFIRM:-y}

if [ "$CONFIRM" != "y" ]; then
    print_info "Cancelled."
    exit 0
fi

# Add to config file
UPDATED_CONFIG=$(jq ".sites[\"$LOCAL_NAME\"] = $NEW_SITE" "$CONFIG_FILE")

if [ $? -eq 0 ]; then
    echo "$UPDATED_CONFIG" > "$CONFIG_FILE"
    print_success "Site '$LOCAL_NAME' added to configuration!"
    echo ""

    # Test connection?
    read -p "Test SSH connection now? (y/n) [y]: " TEST_CONN
    TEST_CONN=${TEST_CONN:-y}

    if [ "$TEST_CONN" = "y" ]; then
        echo ""
        print_info "Testing SSH connection..."
        echo -e "    ${CYAN}ssh -p $SSH_PORT $SSH_USER@$SSH_HOST${NC}"
        echo ""

        if ssh -p "$SSH_PORT" -o ConnectTimeout=10 "$SSH_USER@$SSH_HOST" "echo 'Connection successful! Remote home directory:' && pwd && echo '' && echo 'Checking WordPress path...' && ls -la ~/$REMOTE_PATH/wp-content/ 2>/dev/null | head -5"; then
            print_success "Connection successful!"
        else
            print_error "Connection failed!"
            echo ""
            echo -e "${YELLOW}Troubleshooting tips:${NC}"
            echo "  1. Make sure your SSH key is added to SiteGround"
            echo "  2. Go to Site Tools → Devs → SSH Keys Manager"
            echo "  3. Add your public key (~/.ssh/id_rsa.pub)"
            echo "  4. You can view your public key with: cat ~/.ssh/id_rsa.pub"
        fi
    fi

    echo ""
    print_info "You can now sync with: ./sync-to-siteground.sh sync $LOCAL_NAME"

else
    print_error "Failed to update configuration"
    exit 1
fi
