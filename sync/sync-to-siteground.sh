#!/bin/bash

# =============================================================================
# SiteGround Sync Script
# Syncs themes and plugins from local VVV to SiteGround hosting
# =============================================================================

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CONFIG_FILE="$SCRIPT_DIR/sites.json"
VVV_ROOT="/home/jim/Projects/vagrant-local"

# =============================================================================
# Helper Functions
# =============================================================================

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

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

# Check if jq is installed
check_dependencies() {
    if ! command -v jq &> /dev/null; then
        print_error "jq is required but not installed. Install with: sudo apt install jq"
        exit 1
    fi
    if ! command -v rsync &> /dev/null; then
        print_error "rsync is required but not installed. Install with: sudo apt install rsync"
        exit 1
    fi
}

# Get site configuration
get_site_config() {
    local site_name="$1"
    local field="$2"
    jq -r ".sites[\"$site_name\"].$field // empty" "$CONFIG_FILE"
}

# Get array from config
get_site_array() {
    local site_name="$1"
    local field="$2"
    jq -r ".sites[\"$site_name\"].$field[]? // empty" "$CONFIG_FILE"
}

# List available sites
list_sites() {
    print_header "Available Sites"
    local sites=$(jq -r '.sites | keys[]' "$CONFIG_FILE")

    for site in $sites; do
        local enabled=$(get_site_config "$site" "enabled")
        local domain=$(get_site_config "$site" "remote_domain")
        local host=$(get_site_config "$site" "ssh_host")

        if [ "$enabled" = "true" ]; then
            echo -e "  ${GREEN}●${NC} $site → $domain ($host)"
        else
            echo -e "  ${RED}○${NC} $site → $domain (disabled)"
        fi
    done
    echo ""
}

# Build exclude arguments for rsync
build_excludes() {
    local site_name="$1"
    local excludes=""

    # Get site-specific excludes
    while IFS= read -r pattern; do
        [ -n "$pattern" ] && excludes="$excludes --exclude='$pattern'"
    done < <(get_site_array "$site_name" "exclude_patterns")

    # Get default excludes if no site-specific ones
    if [ -z "$excludes" ]; then
        while IFS= read -r pattern; do
            [ -n "$pattern" ] && excludes="$excludes --exclude='$pattern'"
        done < <(jq -r '.defaults.exclude_patterns[]? // empty' "$CONFIG_FILE")
    fi

    echo "$excludes"
}

# Test SSH connection
test_connection() {
    local site_name="$1"

    local ssh_user=$(get_site_config "$site_name" "ssh_user")
    local ssh_host=$(get_site_config "$site_name" "ssh_host")
    local ssh_port=$(get_site_config "$site_name" "ssh_port")

    print_info "Testing SSH connection to $site_name..."
    echo -e "    ${CYAN}ssh -p $ssh_port $ssh_user@$ssh_host${NC}"

    if ssh -p "$ssh_port" -o ConnectTimeout=10 -o BatchMode=yes "$ssh_user@$ssh_host" "echo 'Connection successful'" 2>/dev/null; then
        print_success "Connection to $site_name successful!"
        return 0
    else
        print_error "Connection to $site_name failed!"
        print_warning "Make sure your SSH key is added to SiteGround"
        return 1
    fi
}

# Sync a single theme
sync_theme() {
    local site_name="$1"
    local theme_name="$2"
    local dry_run="$3"

    local local_name=$(get_site_config "$site_name" "local_name")
    local ssh_user=$(get_site_config "$site_name" "ssh_user")
    local ssh_host=$(get_site_config "$site_name" "ssh_host")
    local ssh_port=$(get_site_config "$site_name" "ssh_port")
    local remote_path=$(get_site_config "$site_name" "remote_path")

    local local_path="$VVV_ROOT/www/$local_name/public_html/wp-content/themes/$theme_name/"
    local remote_dest="$ssh_user@$ssh_host:~/$remote_path/wp-content/themes/$theme_name/"

    if [ ! -d "$local_path" ]; then
        print_error "Local theme not found: $local_path"
        return 1
    fi

    local excludes=$(build_excludes "$site_name")
    local dry_run_flag=""
    [ "$dry_run" = "true" ] && dry_run_flag="--dry-run"

    echo -e "\n${YELLOW}Theme: $theme_name${NC}"
    echo -e "  Local:  $local_path"
    echo -e "  Remote: $remote_dest"

    local rsync_cmd="rsync -avz --progress $dry_run_flag $excludes -e 'ssh -p $ssh_port' '$local_path' '$remote_dest'"

    if [ "$dry_run" = "true" ]; then
        print_info "DRY RUN - No files will be transferred"
    fi

    eval $rsync_cmd

    if [ $? -eq 0 ]; then
        print_success "Theme $theme_name synced successfully"
    else
        print_error "Failed to sync theme $theme_name"
        return 1
    fi
}

# Sync a single plugin
sync_plugin() {
    local site_name="$1"
    local plugin_name="$2"
    local dry_run="$3"

    local local_name=$(get_site_config "$site_name" "local_name")
    local ssh_user=$(get_site_config "$site_name" "ssh_user")
    local ssh_host=$(get_site_config "$site_name" "ssh_host")
    local ssh_port=$(get_site_config "$site_name" "ssh_port")
    local remote_path=$(get_site_config "$site_name" "remote_path")

    local local_path="$VVV_ROOT/www/$local_name/public_html/wp-content/plugins/$plugin_name/"
    local remote_dest="$ssh_user@$ssh_host:~/$remote_path/wp-content/plugins/$plugin_name/"

    if [ ! -d "$local_path" ]; then
        print_error "Local plugin not found: $local_path"
        return 1
    fi

    local excludes=$(build_excludes "$site_name")
    local dry_run_flag=""
    [ "$dry_run" = "true" ] && dry_run_flag="--dry-run"

    echo -e "\n${YELLOW}Plugin: $plugin_name${NC}"
    echo -e "  Local:  $local_path"
    echo -e "  Remote: $remote_dest"

    local rsync_cmd="rsync -avz --progress $dry_run_flag $excludes -e 'ssh -p $ssh_port' '$local_path' '$remote_dest'"

    if [ "$dry_run" = "true" ]; then
        print_info "DRY RUN - No files will be transferred"
    fi

    eval $rsync_cmd

    if [ $? -eq 0 ]; then
        print_success "Plugin $plugin_name synced successfully"
    else
        print_error "Failed to sync plugin $plugin_name"
        return 1
    fi
}

# Sync all configured themes for a site
sync_all_themes() {
    local site_name="$1"
    local dry_run="$2"

    print_header "Syncing Themes for $site_name"

    local themes=$(get_site_array "$site_name" "sync_themes")

    if [ -z "$themes" ]; then
        print_warning "No themes configured for $site_name"
        return 0
    fi

    for theme in $themes; do
        sync_theme "$site_name" "$theme" "$dry_run"
    done
}

# Sync all configured plugins for a site
sync_all_plugins() {
    local site_name="$1"
    local dry_run="$2"

    print_header "Syncing Plugins for $site_name"

    local plugins=$(get_site_array "$site_name" "sync_plugins")

    if [ -z "$plugins" ]; then
        print_warning "No plugins configured for $site_name"
        return 0
    fi

    for plugin in $plugins; do
        sync_plugin "$site_name" "$plugin" "$dry_run"
    done
}

# Full sync for a site
full_sync() {
    local site_name="$1"
    local dry_run="$2"

    local enabled=$(get_site_config "$site_name" "enabled")

    if [ "$enabled" != "true" ]; then
        print_error "Site $site_name is disabled"
        return 1
    fi

    print_header "Full Sync: $site_name"

    local domain=$(get_site_config "$site_name" "remote_domain")
    print_info "Syncing to: $domain"

    # Test connection first
    if ! test_connection "$site_name"; then
        return 1
    fi

    # Sync themes
    sync_all_themes "$site_name" "$dry_run"

    # Sync plugins
    sync_all_plugins "$site_name" "$dry_run"

    print_header "Sync Complete!"
    if [ "$dry_run" = "true" ]; then
        print_warning "This was a DRY RUN - no files were actually transferred"
        print_info "Run without --dry-run to perform actual sync"
    fi
}

# Interactive mode
interactive_mode() {
    print_header "SiteGround Sync - Interactive Mode"

    # List sites
    echo -e "${CYAN}Available sites:${NC}"
    local sites=$(jq -r '.sites | keys[]' "$CONFIG_FILE")
    local site_array=()
    local i=1

    for site in $sites; do
        local enabled=$(get_site_config "$site" "enabled")
        local domain=$(get_site_config "$site" "remote_domain")
        if [ "$enabled" = "true" ]; then
            echo -e "  ${GREEN}$i)${NC} $site → $domain"
            site_array+=("$site")
            ((i++))
        fi
    done

    echo ""
    read -p "Select site number (or 'q' to quit): " site_choice

    if [ "$site_choice" = "q" ]; then
        echo "Goodbye!"
        exit 0
    fi

    local selected_site="${site_array[$((site_choice-1))]}"

    if [ -z "$selected_site" ]; then
        print_error "Invalid selection"
        exit 1
    fi

    echo ""
    echo -e "${CYAN}What do you want to sync for $selected_site?${NC}"
    echo "  1) All themes and plugins"
    echo "  2) Themes only"
    echo "  3) Plugins only"
    echo "  4) Specific theme"
    echo "  5) Specific plugin"
    echo "  6) Test connection only"
    echo ""
    read -p "Select option: " sync_choice

    echo ""
    read -p "Dry run first? (y/n) [y]: " dry_run_choice
    dry_run_choice=${dry_run_choice:-y}

    local dry_run="false"
    [ "$dry_run_choice" = "y" ] && dry_run="true"

    case $sync_choice in
        1)
            full_sync "$selected_site" "$dry_run"
            ;;
        2)
            sync_all_themes "$selected_site" "$dry_run"
            ;;
        3)
            sync_all_plugins "$selected_site" "$dry_run"
            ;;
        4)
            echo ""
            echo -e "${CYAN}Available themes:${NC}"
            get_site_array "$selected_site" "sync_themes" | nl
            echo ""
            read -p "Enter theme name: " theme_name
            sync_theme "$selected_site" "$theme_name" "$dry_run"
            ;;
        5)
            echo ""
            echo -e "${CYAN}Available plugins:${NC}"
            get_site_array "$selected_site" "sync_plugins" | nl
            echo ""
            read -p "Enter plugin name: " plugin_name
            sync_plugin "$selected_site" "$plugin_name" "$dry_run"
            ;;
        6)
            test_connection "$selected_site"
            ;;
        *)
            print_error "Invalid option"
            exit 1
            ;;
    esac
}

# Show usage
show_usage() {
    echo "Usage: $0 [OPTIONS] [COMMAND]"
    echo ""
    echo "Commands:"
    echo "  list                    List all configured sites"
    echo "  sync <site>             Full sync for a site (themes + plugins)"
    echo "  sync-theme <site> <theme>    Sync a specific theme"
    echo "  sync-plugin <site> <plugin>  Sync a specific plugin"
    echo "  test <site>             Test SSH connection to a site"
    echo "  interactive             Interactive mode (default if no args)"
    echo ""
    echo "Options:"
    echo "  --dry-run               Show what would be transferred without actually syncing"
    echo "  -h, --help              Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0 list"
    echo "  $0 sync uptownlifegroup --dry-run"
    echo "  $0 sync-theme uptownlifegroup pegasus"
    echo "  $0 sync-plugin uptownlifegroup pegasus-carousel"
    echo "  $0 test uptownlifegroup"
    echo ""
}

# =============================================================================
# Main Script
# =============================================================================

check_dependencies

# Check if config file exists
if [ ! -f "$CONFIG_FILE" ]; then
    print_error "Config file not found: $CONFIG_FILE"
    exit 1
fi

# Parse arguments
DRY_RUN="false"
COMMAND=""
ARGS=()

while [[ $# -gt 0 ]]; do
    case $1 in
        --dry-run)
            DRY_RUN="true"
            shift
            ;;
        -h|--help)
            show_usage
            exit 0
            ;;
        *)
            ARGS+=("$1")
            shift
            ;;
    esac
done

# Set command and arguments
COMMAND="${ARGS[0]:-interactive}"
SITE="${ARGS[1]:-}"
ITEM="${ARGS[2]:-}"

# Execute command
case $COMMAND in
    list)
        list_sites
        ;;
    sync)
        if [ -z "$SITE" ]; then
            print_error "Site name required"
            show_usage
            exit 1
        fi
        full_sync "$SITE" "$DRY_RUN"
        ;;
    sync-theme)
        if [ -z "$SITE" ] || [ -z "$ITEM" ]; then
            print_error "Site name and theme name required"
            show_usage
            exit 1
        fi
        sync_theme "$SITE" "$ITEM" "$DRY_RUN"
        ;;
    sync-plugin)
        if [ -z "$SITE" ] || [ -z "$ITEM" ]; then
            print_error "Site name and plugin name required"
            show_usage
            exit 1
        fi
        sync_plugin "$SITE" "$ITEM" "$DRY_RUN"
        ;;
    test)
        if [ -z "$SITE" ]; then
            print_error "Site name required"
            show_usage
            exit 1
        fi
        test_connection "$SITE"
        ;;
    interactive)
        interactive_mode
        ;;
    *)
        print_error "Unknown command: $COMMAND"
        show_usage
        exit 1
        ;;
esac
