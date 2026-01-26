#!/bin/bash

# =============================================================================
# Git Deploy Script
# Pull updates from GitHub repos on SiteGround hosting
# =============================================================================

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

# Script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CONFIG_FILE="$SCRIPT_DIR/sites.json"
ENV_FILE="$SCRIPT_DIR/.env"
LOG_FILE="$SCRIPT_DIR/deploy.log"

# =============================================================================
# Helper Functions
# =============================================================================

print_header() {
    echo -e "\n${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${CYAN}  $1${NC}"
    echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}\n"
}

print_success() { echo -e "${GREEN}✓${NC} $1"; }
print_error() {
    echo -e "${RED}✗${NC} $1"
    log_error "$1"
}
print_warning() {
    echo -e "${YELLOW}!${NC} $1"
    log_warning "$1"
}
print_info() { echo -e "${BLUE}>${NC} $1"; }

# =============================================================================
# Logging Functions
# =============================================================================

log_error() {
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    echo "[$timestamp] ERROR: $1" >> "$LOG_FILE"
}

log_warning() {
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    echo "[$timestamp] WARNING: $1" >> "$LOG_FILE"
}

log_info() {
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    echo "[$timestamp] INFO: $1" >> "$LOG_FILE"
}

log_output() {
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    echo "[$timestamp] OUTPUT:" >> "$LOG_FILE"
    echo "$1" >> "$LOG_FILE"
    echo "" >> "$LOG_FILE"
}

# =============================================================================
# Load Environment
# =============================================================================

load_env() {
    if [ -f "$ENV_FILE" ]; then
        source "$ENV_FILE"
    else
        print_error ".env file not found at $ENV_FILE"
        print_info "Create one with SSH_KEY_PATH variable"
        exit 1
    fi
}

# =============================================================================
# Check Dependencies
# =============================================================================

check_dependencies() {
    if ! command -v jq &> /dev/null; then
        print_error "jq is required. Install with: sudo apt install jq"
        exit 1
    fi
}

# =============================================================================
# Config Helpers
# =============================================================================

get_sites() {
    jq -r '.sites | keys[]' "$CONFIG_FILE"
}

get_site_field() {
    local site="$1"
    local field="$2"
    jq -r ".sites[\"$site\"].$field // empty" "$CONFIG_FILE"
}

get_themes() {
    local site="$1"
    jq -r ".sites[\"$site\"].themes | keys[]" "$CONFIG_FILE"
}

get_theme_branch() {
    local site="$1"
    local theme="$2"
    jq -r ".sites[\"$site\"].themes[\"$theme\"].branch // \"master\"" "$CONFIG_FILE"
}

get_plugins() {
    local site="$1"
    jq -r ".sites[\"$site\"].plugins[]" "$CONFIG_FILE"
}

# =============================================================================
# SSH Functions
# =============================================================================

ssh_cmd() {
    local site="$1"
    local ssh_user=$(get_site_field "$site" "ssh_user")
    local ssh_host=$(get_site_field "$site" "ssh_host")
    ssh -i "$SSH_KEY_PATH" -p "$SSH_PORT" "$ssh_user@$ssh_host"
}

run_remote() {
    local site="$1"
    local command="$2"
    local ssh_user=$(get_site_field "$site" "ssh_user")
    local ssh_host=$(get_site_field "$site" "ssh_host")
    ssh -i "$SSH_KEY_PATH" -p "$SSH_PORT" "$ssh_user@$ssh_host" "$command"
}

test_connection() {
    local site="$1"
    local ssh_user=$(get_site_field "$site" "ssh_user")
    local ssh_host=$(get_site_field "$site" "ssh_host")

    print_info "Testing connection to $site..."

    local output=$(ssh -i "$SSH_KEY_PATH" -p "$SSH_PORT" -o ConnectTimeout=10 -o BatchMode=yes "$ssh_user@$ssh_host" "echo 'ok'" 2>&1)
    local status=$?

    if [ $status -eq 0 ]; then
        print_success "Connected to $site"
        return 0
    else
        print_error "Could not connect to $site"
        log_info "SSH: $ssh_user@$ssh_host:$SSH_PORT"
        log_output "$output"
        return 1
    fi
}

# =============================================================================
# Git Pull Functions
# =============================================================================

check_is_git_repo() {
    local site="$1"
    local path="$2"

    run_remote "$site" "[ -d \"$path/.git\" ] && echo 'yes' || echo 'no'"
}

git_pull() {
    local site="$1"
    local path="$2"
    local name="$3"

    print_info "Pulling $name..."

    # Check if it's a git repo
    local is_git=$(check_is_git_repo "$site" "$path")

    if [ "$is_git" != "yes" ]; then
        print_warning "$name is not a git repository at $path"
        return 1
    fi

    # Do the pull
    local output=$(run_remote "$site" "cd \"$path\" && git pull 2>&1")
    local status=$?

    echo "$output"

    if [ $status -eq 0 ]; then
        print_success "$name updated"
        return 0
    else
        print_error "Failed to update $name"
        log_info "Site: $site | Path: $path"
        log_output "$output"
        return 1
    fi
}

# =============================================================================
# Update Functions
# =============================================================================

update_theme() {
    local site="$1"
    local theme="$2"
    local remote_path=$(get_site_field "$site" "remote_path")
    local theme_path="$remote_path/wp-content/themes/$theme"

    git_pull "$site" "$theme_path" "Theme: $theme"
}

update_plugin() {
    local site="$1"
    local plugin="$2"
    local remote_path=$(get_site_field "$site" "remote_path")
    local plugin_path="$remote_path/wp-content/plugins/$plugin"

    git_pull "$site" "$plugin_path" "Plugin: $plugin"
}

update_child_theme() {
    local site="$1"
    update_theme "$site" "pegasus-child"
}

update_parent_theme() {
    local site="$1"
    update_theme "$site" "pegasus"
}

update_all_themes() {
    local site="$1"
    print_header "Updating All Themes"

    for theme in $(get_themes "$site"); do
        update_theme "$site" "$theme"
        echo ""
    done
}

update_all_plugins() {
    local site="$1"
    print_header "Updating All Plugins"

    for plugin in $(get_plugins "$site"); do
        update_plugin "$site" "$plugin"
        echo ""
    done
}

update_everything() {
    local site="$1"
    update_all_themes "$site"
    update_all_plugins "$site"
}

# =============================================================================
# Menu Functions
# =============================================================================

select_site() {
    print_header "Select Site"

    local sites=($(get_sites))
    local i=1

    for site in "${sites[@]}"; do
        local name=$(get_site_field "$site" "name")
        local domain=$(get_site_field "$site" "domain")
        echo -e "  ${GREEN}$i)${NC} $name ${CYAN}($domain)${NC}"
        ((i++))
    done

    echo ""
    read -p "Select site (1-${#sites[@]}) or 'q' to quit: " choice

    if [ "$choice" = "q" ]; then
        echo "Goodbye!"
        exit 0
    fi

    if ! [[ "$choice" =~ ^[0-9]+$ ]] || [ "$choice" -lt 1 ] || [ "$choice" -gt ${#sites[@]} ]; then
        print_error "Invalid selection"
        return 1
    fi

    SELECTED_SITE="${sites[$((choice-1))]}"
    return 0
}

select_action() {
    local site="$1"
    local name=$(get_site_field "$site" "name")
    local domain=$(get_site_field "$site" "domain")

    print_header "$name ($domain)"

    echo -e "${CYAN}What would you like to update?${NC}\n"
    echo "  1) Child theme only (pegasus-child)"
    echo "  2) Parent theme only (pegasus)"
    echo "  3) All themes"
    echo "  4) All plugins"
    echo "  5) Specific plugin"
    echo "  6) Everything (all themes + all plugins)"
    echo "  7) Back to site selection"
    echo ""

    read -p "Select option: " action

    case $action in
        1) update_child_theme "$site" ;;
        2) update_parent_theme "$site" ;;
        3) update_all_themes "$site" ;;
        4) update_all_plugins "$site" ;;
        5) select_plugin "$site" ;;
        6) update_everything "$site" ;;
        7) return 1 ;;
        *) print_error "Invalid option" ;;
    esac

    return 0
}

select_plugin() {
    local site="$1"

    echo ""
    echo -e "${CYAN}Available plugins:${NC}\n"

    local plugins=($(get_plugins "$site"))
    local i=1

    for plugin in "${plugins[@]}"; do
        echo "  $i) $plugin"
        ((i++))
    done

    echo ""
    read -p "Select plugin (1-${#plugins[@]}): " choice

    if ! [[ "$choice" =~ ^[0-9]+$ ]] || [ "$choice" -lt 1 ] || [ "$choice" -gt ${#plugins[@]} ]; then
        print_error "Invalid selection"
        return 1
    fi

    local selected_plugin="${plugins[$((choice-1))]}"
    update_plugin "$site" "$selected_plugin"
}

# =============================================================================
# Main Interactive Loop
# =============================================================================

interactive_mode() {
    print_header "Git Deploy - SiteGround"

    while true; do
        if ! select_site; then
            continue
        fi

        # Test connection before proceeding
        if ! test_connection "$SELECTED_SITE"; then
            print_warning "Check your SSH key and try again"
            echo ""
            continue
        fi

        while true; do
            if ! select_action "$SELECTED_SITE"; then
                break
            fi

            echo ""
            read -p "Press Enter to continue or 'b' to go back: " cont
            if [ "$cont" = "b" ]; then
                break
            fi
        done
    done
}

# =============================================================================
# CLI Mode
# =============================================================================

show_usage() {
    echo "Usage: $0 [COMMAND] [OPTIONS]"
    echo ""
    echo "Commands:"
    echo "  (no args)                    Interactive mode"
    echo "  list                         List all sites"
    echo "  pull <site> child            Pull child theme"
    echo "  pull <site> parent           Pull parent theme"
    echo "  pull <site> themes           Pull all themes"
    echo "  pull <site> plugins          Pull all plugins"
    echo "  pull <site> all              Pull everything"
    echo "  pull <site> plugin <name>    Pull specific plugin"
    echo ""
    echo "Sites: $(get_sites | tr '\n' ' ')"
    echo ""
}

cli_mode() {
    local cmd="$1"
    local site="$2"
    local target="$3"
    local name="$4"

    case $cmd in
        list)
            print_header "Configured Sites"
            for site in $(get_sites); do
                local name=$(get_site_field "$site" "name")
                local domain=$(get_site_field "$site" "domain")
                echo -e "  ${GREEN}$site${NC} - $name ($domain)"
            done
            echo ""
            ;;
        pull)
            if [ -z "$site" ] || [ -z "$target" ]; then
                print_error "Site and target required"
                show_usage
                exit 1
            fi

            # Verify site exists
            if ! get_sites | grep -q "^$site$"; then
                print_error "Unknown site: $site"
                echo "Available: $(get_sites | tr '\n' ' ')"
                exit 1
            fi

            if ! test_connection "$site"; then
                exit 1
            fi

            case $target in
                child)   update_child_theme "$site" ;;
                parent)  update_parent_theme "$site" ;;
                themes)  update_all_themes "$site" ;;
                plugins) update_all_plugins "$site" ;;
                all)     update_everything "$site" ;;
                plugin)
                    if [ -z "$name" ]; then
                        print_error "Plugin name required"
                        exit 1
                    fi
                    update_plugin "$site" "$name"
                    ;;
                *)
                    print_error "Unknown target: $target"
                    show_usage
                    exit 1
                    ;;
            esac
            ;;
        *)
            show_usage
            ;;
    esac
}

# =============================================================================
# Main
# =============================================================================

check_dependencies
load_env

if [ $# -eq 0 ]; then
    interactive_mode
else
    cli_mode "$@"
fi
