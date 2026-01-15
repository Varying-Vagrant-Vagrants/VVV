#!/bin/bash

# =============================================================================
# Git Deploy Script
# SSH to SiteGround and git pull themes/plugins from GitHub
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

print_header() {
    echo -e "\n${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${CYAN}  $1${NC}"
    echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}\n"
}

print_success() { echo -e "${GREEN}✓${NC} $1"; }
print_error() { echo -e "${RED}✗${NC} $1"; }
print_warning() { echo -e "${YELLOW}⚠${NC} $1"; }
print_info() { echo -e "${BLUE}ℹ${NC} $1"; }

# Check dependencies
if ! command -v jq &> /dev/null; then
    print_error "jq is required. Install with: sudo apt install jq"
    exit 1
fi

# Get site config
get_site_config() {
    local site="$1" field="$2"
    jq -r ".sites[\"$site\"].$field // empty" "$CONFIG_FILE"
}

get_site_array() {
    local site="$1" field="$2"
    jq -r ".sites[\"$site\"].$field[]? // empty" "$CONFIG_FILE"
}

# Build SSH command
ssh_cmd() {
    local site="$1"
    local ssh_user=$(get_site_config "$site" "ssh_user")
    local ssh_host=$(get_site_config "$site" "ssh_host")
    local ssh_port=$(get_site_config "$site" "ssh_port")
    echo "ssh -p $ssh_port $ssh_user@$ssh_host"
}

# List sites
list_sites() {
    print_header "Configured Sites"
    local sites=$(jq -r '.sites | keys[]' "$CONFIG_FILE")
    for site in $sites; do
        local enabled=$(get_site_config "$site" "enabled")
        local domain=$(get_site_config "$site" "remote_domain")
        if [ "$enabled" = "true" ]; then
            echo -e "  ${GREEN}●${NC} $site → $domain"
        else
            echo -e "  ${RED}○${NC} $site (disabled)"
        fi
    done
    echo ""
}

# Git pull a single theme on remote
deploy_theme() {
    local site="$1" theme="$2"
    local remote_path=$(get_site_config "$site" "remote_path")
    local theme_path="~/$remote_path/wp-content/themes/$theme"

    print_info "Deploying theme: $theme"

    $(ssh_cmd "$site") "cd $theme_path && echo 'Current branch:' && git branch --show-current && echo '' && git pull" 2>&1

    if [ $? -eq 0 ]; then
        print_success "Theme $theme deployed"
    else
        print_error "Failed to deploy theme $theme"
    fi
}

# Git pull a single plugin on remote
deploy_plugin() {
    local site="$1" plugin="$2"
    local remote_path=$(get_site_config "$site" "remote_path")
    local plugin_path="~/$remote_path/wp-content/plugins/$plugin"

    print_info "Deploying plugin: $plugin"

    $(ssh_cmd "$site") "cd $plugin_path && echo 'Current branch:' && git branch --show-current && echo '' && git pull" 2>&1

    if [ $? -eq 0 ]; then
        print_success "Plugin $plugin deployed"
    else
        print_error "Failed to deploy plugin $plugin"
    fi
}

# Deploy all themes for a site
deploy_all_themes() {
    local site="$1"
    print_header "Deploying Themes: $site"

    local themes=$(get_site_array "$site" "sync_themes")
    if [ -z "$themes" ]; then
        print_warning "No themes configured"
        return
    fi

    for theme in $themes; do
        deploy_theme "$site" "$theme"
        echo ""
    done
}

# Deploy all plugins for a site
deploy_all_plugins() {
    local site="$1"
    print_header "Deploying Plugins: $site"

    local plugins=$(get_site_array "$site" "sync_plugins")
    if [ -z "$plugins" ]; then
        print_warning "No plugins configured"
        return
    fi

    for plugin in $plugins; do
        deploy_plugin "$site" "$plugin"
        echo ""
    done
}

# Full deploy
full_deploy() {
    local site="$1"
    local domain=$(get_site_config "$site" "remote_domain")

    print_header "Git Deploy: $site → $domain"

    # Test connection
    print_info "Testing SSH connection..."
    if ! $(ssh_cmd "$site") "echo 'Connected!'" 2>/dev/null; then
        print_error "SSH connection failed"
        return 1
    fi
    print_success "SSH connection OK"
    echo ""

    deploy_all_themes "$site"
    deploy_all_plugins "$site"

    print_header "Deploy Complete!"
}

# Check git status on remote
check_remote_status() {
    local site="$1"
    local remote_path=$(get_site_config "$site" "remote_path")

    print_header "Remote Git Status: $site"

    echo -e "${CYAN}Themes:${NC}"
    for theme in $(get_site_array "$site" "sync_themes"); do
        echo -e "\n${YELLOW}$theme:${NC}"
        $(ssh_cmd "$site") "cd ~/$remote_path/wp-content/themes/$theme 2>/dev/null && git status -sb || echo 'Not a git repo or not found'"
    done

    echo -e "\n${CYAN}Plugins:${NC}"
    for plugin in $(get_site_array "$site" "sync_plugins"); do
        echo -e "\n${YELLOW}$plugin:${NC}"
        $(ssh_cmd "$site") "cd ~/$remote_path/wp-content/plugins/$plugin 2>/dev/null && git status -sb || echo 'Not a git repo or not found'"
    done
}

# Clone repos on remote (initial setup)
setup_remote_repos() {
    local site="$1"
    local remote_path=$(get_site_config "$site" "remote_path")

    print_header "Setup Git Repos on Remote: $site"
    print_warning "This will clone repos that don't exist yet on the remote server"
    echo ""

    read -p "Continue? (y/n) [n]: " confirm
    [ "$confirm" != "y" ] && return

    echo -e "\n${CYAN}Setting up themes...${NC}"
    for theme in $(get_site_array "$site" "sync_themes"); do
        local theme_path="~/$remote_path/wp-content/themes/$theme"
        echo -e "\n${YELLOW}$theme:${NC}"

        # Check if already exists
        if $(ssh_cmd "$site") "[ -d $theme_path/.git ]" 2>/dev/null; then
            print_info "Already a git repo, pulling latest..."
            $(ssh_cmd "$site") "cd $theme_path && git pull"
        else
            print_info "Cloning from GitHub..."
            # You'll need to customize the repo URL pattern
            local repo_url="git@github.com:Visionquest-Development/$theme.git"
            $(ssh_cmd "$site") "cd ~/$remote_path/wp-content/themes && git clone $repo_url"
        fi
    done

    echo -e "\n${CYAN}Setting up plugins...${NC}"
    for plugin in $(get_site_array "$site" "sync_plugins"); do
        local plugin_path="~/$remote_path/wp-content/plugins/$plugin"
        echo -e "\n${YELLOW}$plugin:${NC}"

        if $(ssh_cmd "$site") "[ -d $plugin_path/.git ]" 2>/dev/null; then
            print_info "Already a git repo, pulling latest..."
            $(ssh_cmd "$site") "cd $plugin_path && git pull"
        else
            print_info "Cloning from GitHub..."
            local repo_url="git@github.com:Visionquest-Development/$plugin.git"
            $(ssh_cmd "$site") "cd ~/$remote_path/wp-content/plugins && git clone $repo_url"
        fi
    done
}

# Interactive mode
interactive_mode() {
    print_header "Git Deploy - Interactive Mode"

    echo -e "${CYAN}Available sites:${NC}"
    local sites=$(jq -r '.sites | keys[]' "$CONFIG_FILE")
    local site_array=()
    local i=1

    for site in $sites; do
        local enabled=$(get_site_config "$site" "enabled")
        if [ "$enabled" = "true" ]; then
            local domain=$(get_site_config "$site" "remote_domain")
            echo -e "  ${GREEN}$i)${NC} $site → $domain"
            site_array+=("$site")
            ((i++))
        fi
    done

    echo ""
    read -p "Select site (or 'q' to quit): " choice
    [ "$choice" = "q" ] && exit 0

    local selected="${site_array[$((choice-1))]}"
    [ -z "$selected" ] && { print_error "Invalid selection"; exit 1; }

    echo ""
    echo -e "${CYAN}What do you want to do for $selected?${NC}"
    echo "  1) Deploy all (git pull themes + plugins)"
    echo "  2) Deploy themes only"
    echo "  3) Deploy plugins only"
    echo "  4) Deploy specific theme"
    echo "  5) Deploy specific plugin"
    echo "  6) Check remote git status"
    echo "  7) Setup/clone repos on remote (first time)"
    echo ""
    read -p "Select option: " action

    case $action in
        1) full_deploy "$selected" ;;
        2) deploy_all_themes "$selected" ;;
        3) deploy_all_plugins "$selected" ;;
        4)
            echo ""
            get_site_array "$selected" "sync_themes" | nl
            read -p "Theme name: " theme
            deploy_theme "$selected" "$theme"
            ;;
        5)
            echo ""
            get_site_array "$selected" "sync_plugins" | nl
            read -p "Plugin name: " plugin
            deploy_plugin "$selected" "$plugin"
            ;;
        6) check_remote_status "$selected" ;;
        7) setup_remote_repos "$selected" ;;
        *) print_error "Invalid option" ;;
    esac
}

# Usage
show_usage() {
    echo "Usage: $0 [COMMAND] [OPTIONS]"
    echo ""
    echo "Commands:"
    echo "  list                        List configured sites"
    echo "  deploy <site>               Git pull all themes + plugins"
    echo "  deploy-theme <site> <name>  Git pull specific theme"
    echo "  deploy-plugin <site> <name> Git pull specific plugin"
    echo "  status <site>               Check git status on remote"
    echo "  setup <site>                Clone repos on remote (first time)"
    echo "  interactive                 Interactive mode (default)"
    echo ""
    echo "Examples:"
    echo "  $0 deploy uptownlifegroup"
    echo "  $0 deploy-theme uptownlifegroup pegasus"
    echo "  $0 status uptownlifegroup"
    echo ""
}

# Main
case "${1:-interactive}" in
    list) list_sites ;;
    deploy)
        [ -z "$2" ] && { print_error "Site required"; exit 1; }
        full_deploy "$2"
        ;;
    deploy-theme)
        [ -z "$2" ] || [ -z "$3" ] && { print_error "Site and theme required"; exit 1; }
        deploy_theme "$2" "$3"
        ;;
    deploy-plugin)
        [ -z "$2" ] || [ -z "$3" ] && { print_error "Site and plugin required"; exit 1; }
        deploy_plugin "$2" "$3"
        ;;
    status)
        [ -z "$2" ] && { print_error "Site required"; exit 1; }
        check_remote_status "$2"
        ;;
    setup)
        [ -z "$2" ] && { print_error "Site required"; exit 1; }
        setup_remote_repos "$2"
        ;;
    interactive) interactive_mode ;;
    -h|--help) show_usage ;;
    *) show_usage ;;
esac
