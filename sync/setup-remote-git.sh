#!/bin/bash

# =============================================================================
# Setup Git Repos on SiteGround Remote
# Configures GitHub SSH and clones theme/plugin repositories
# =============================================================================

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CONFIG_FILE="$SCRIPT_DIR/sites.json"

print_header() {
    echo -e "\n${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${CYAN}  $1${NC}"
    echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}\n"
}

print_success() { echo -e "${GREEN}✓${NC} $1"; }
print_error() { echo -e "${RED}✗${NC} $1"; }
print_info() { echo -e "${BLUE}ℹ${NC} $1"; }

get_site_config() {
    local site="$1" field="$2"
    jq -r ".sites[\"$site\"].$field // empty" "$CONFIG_FILE"
}

get_site_array() {
    local site="$1" field="$2"
    jq -r ".sites[\"$site\"].$field[]? // empty" "$CONFIG_FILE"
}

SITE="${1:-uptownlifegroup}"
SSH_USER=$(get_site_config "$SITE" "ssh_user")
SSH_HOST=$(get_site_config "$SITE" "ssh_host")
SSH_PORT=$(get_site_config "$SITE" "ssh_port")
REMOTE_PATH=$(get_site_config "$SITE" "remote_path")

SSH_CMD="ssh -p $SSH_PORT $SSH_USER@$SSH_HOST"

print_header "Setup Git on Remote: $SITE"

echo -e "${CYAN}SSH:${NC} $SSH_USER@$SSH_HOST:$SSH_PORT"
echo -e "${CYAN}Path:${NC} ~/$REMOTE_PATH"
echo ""

# Step 1: Add GitHub to known_hosts
print_header "Step 1: Add GitHub to known_hosts"
print_info "Adding GitHub's SSH key to known_hosts..."

$SSH_CMD "mkdir -p ~/.ssh && ssh-keyscan -t ed25519 github.com >> ~/.ssh/known_hosts 2>/dev/null"

if [ $? -eq 0 ]; then
    print_success "GitHub added to known_hosts"
else
    print_error "Failed to add GitHub to known_hosts"
fi

# Step 2: Check/Generate SSH key on remote
print_header "Step 2: Check SSH Key"

REMOTE_KEY=$($SSH_CMD "cat ~/.ssh/*.pub 2>/dev/null | head -1")

if [ -n "$REMOTE_KEY" ]; then
    print_success "SSH key exists on remote"
    echo ""
    echo -e "${YELLOW}Remote public key (add this to GitHub):${NC}"
    echo ""
    echo "$REMOTE_KEY"
    echo ""
else
    print_info "No SSH key found. Generating one..."
    $SSH_CMD "ssh-keygen -t ed25519 -f ~/.ssh/id_ed25519 -N '' -q"
    REMOTE_KEY=$($SSH_CMD "cat ~/.ssh/id_ed25519.pub")
    echo ""
    echo -e "${YELLOW}Generated public key (add this to GitHub):${NC}"
    echo ""
    echo "$REMOTE_KEY"
    echo ""
fi

echo -e "${CYAN}To add this key to GitHub:${NC}"
echo "  1. Go to: https://github.com/settings/keys"
echo "  2. Click 'New SSH key'"
echo "  3. Title: SiteGround - $SITE"
echo "  4. Paste the key above"
echo ""

read -p "Press Enter once you've added the key to GitHub (or 'skip' to continue anyway): " confirm

# Step 3: Test GitHub connection
print_header "Step 3: Test GitHub Connection"

GITHUB_TEST=$($SSH_CMD "ssh -T git@github.com 2>&1 || true")

if echo "$GITHUB_TEST" | grep -q "successfully authenticated"; then
    print_success "GitHub SSH connection working!"
else
    print_error "GitHub connection issue: $GITHUB_TEST"
    echo ""
    print_info "Make sure the SSH key is added to GitHub"
    read -p "Continue anyway? (y/n): " cont
    [ "$cont" != "y" ] && exit 1
fi

# Step 4: Clone repositories
print_header "Step 4: Clone Repositories"

echo -e "${CYAN}Cloning themes...${NC}"
for theme in $(get_site_array "$SITE" "sync_themes"); do
    THEME_PATH="~/$REMOTE_PATH/wp-content/themes/$theme"
    echo ""
    echo -e "${YELLOW}Theme: $theme${NC}"

    # Check if exists
    EXISTS=$($SSH_CMD "[ -d $THEME_PATH ] && echo 'yes' || echo 'no'")

    if [ "$EXISTS" = "yes" ]; then
        # Check if it's a git repo
        IS_GIT=$($SSH_CMD "[ -d $THEME_PATH/.git ] && echo 'yes' || echo 'no'")
        if [ "$IS_GIT" = "yes" ]; then
            print_info "Already a git repo, pulling..."
            $SSH_CMD "cd $THEME_PATH && git pull"
        else
            print_info "Directory exists but not a git repo"
            print_info "Backing up and cloning fresh..."
            $SSH_CMD "mv $THEME_PATH ${THEME_PATH}_backup_$(date +%Y%m%d)"
            $SSH_CMD "cd ~/$REMOTE_PATH/wp-content/themes && git clone git@github.com:Visionquest-Development/$theme.git"
        fi
    else
        print_info "Cloning $theme..."
        $SSH_CMD "cd ~/$REMOTE_PATH/wp-content/themes && git clone git@github.com:Visionquest-Development/$theme.git"
    fi

    if [ $? -eq 0 ]; then
        print_success "$theme ready"
    else
        print_error "Failed to setup $theme"
    fi
done

echo ""
echo -e "${CYAN}Cloning plugins...${NC}"
for plugin in $(get_site_array "$SITE" "sync_plugins"); do
    PLUGIN_PATH="~/$REMOTE_PATH/wp-content/plugins/$plugin"
    echo ""
    echo -e "${YELLOW}Plugin: $plugin${NC}"

    EXISTS=$($SSH_CMD "[ -d $PLUGIN_PATH ] && echo 'yes' || echo 'no'")

    if [ "$EXISTS" = "yes" ]; then
        IS_GIT=$($SSH_CMD "[ -d $PLUGIN_PATH/.git ] && echo 'yes' || echo 'no'")
        if [ "$IS_GIT" = "yes" ]; then
            print_info "Already a git repo, pulling..."
            $SSH_CMD "cd $PLUGIN_PATH && git pull"
        else
            print_info "Directory exists but not a git repo, backing up and cloning..."
            $SSH_CMD "mv $PLUGIN_PATH ${PLUGIN_PATH}_backup_$(date +%Y%m%d)"
            $SSH_CMD "cd ~/$REMOTE_PATH/wp-content/plugins && git clone git@github.com:Visionquest-Development/$plugin.git"
        fi
    else
        print_info "Cloning $plugin..."
        $SSH_CMD "cd ~/$REMOTE_PATH/wp-content/plugins && git clone git@github.com:Visionquest-Development/$plugin.git"
    fi

    if [ $? -eq 0 ]; then
        print_success "$plugin ready"
    else
        print_error "Failed to setup $plugin"
    fi
done

print_header "Setup Complete!"

echo -e "${GREEN}Your git-based deployment workflow:${NC}"
echo ""
echo "  1. Make changes locally in VVV"
echo "  2. Commit and push to GitHub:"
echo "     ${CYAN}git add . && git commit -m 'changes' && git push${NC}"
echo ""
echo "  3. Deploy to production:"
echo "     ${CYAN}./git-deploy.sh deploy $SITE${NC}"
echo ""
echo "  Or deploy a specific theme/plugin:"
echo "     ${CYAN}./git-deploy.sh deploy-theme $SITE pegasus${NC}"
echo "     ${CYAN}./git-deploy.sh deploy-plugin $SITE pegasus-carousel${NC}"
echo ""
