#!/bin/bash

# =============================================================================
# Setup Git Repos Script
# Check local VVV WordPress sites and clone missing theme/plugin repos
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
VVV_ROOT="$(dirname "$SCRIPT_DIR")"
CONFIG_FILE="$SCRIPT_DIR/sites.json"
WWW_DIR="$VVV_ROOT/www"

# =============================================================================
# Whitelist - Approved Git Repos
# =============================================================================

declare -A THEME_REPOS
THEME_REPOS["pegasus"]="git@github.com:Visionquest-Development/pegasus.git"
THEME_REPOS["pegasus-child"]="git@github.com:Visionquest-Development/pegasus-child.git"

declare -A PLUGIN_REPOS
PLUGIN_REPOS["pegasus-carousel"]="git@github.com:Visionquest-Development/pegasus-carousel.git"
PLUGIN_REPOS["pegasus-slider"]="git@github.com:Visionquest-Development/pegasus-slider.git"
PLUGIN_REPOS["pegasus-tabs"]="git@github.com:Visionquest-Development/pegasus-tabs.git"
PLUGIN_REPOS["pegasus-toggleslide"]="git@github.com:Visionquest-Development/pegasus-toggleslide.git"
PLUGIN_REPOS["pegasus-packery"]="git@github.com:Visionquest-Development/pegasus-packery.git"
PLUGIN_REPOS["wp-bootstrap-hooks"]="git@github.com:jimboobrien/wp-bootstrap-hooks.git"

# =============================================================================
# Site Key to Local Directory Mapping
# =============================================================================

declare -A SITE_DIRS
SITE_DIRS["ulg"]="uptownlifegroup"
SITE_DIRS["ulg-events"]="ulgevents"
SITE_DIRS["theloft"]="theloft"
SITE_DIRS["mabellas"]="mabellas"
SITE_DIRS["saltcellar"]="saltcellar"
SITE_DIRS["mixmarket"]="mixmarket"
SITE_DIRS["tommygs"]="tommygs"

# =============================================================================
# Helper Functions
# =============================================================================

print_header() {
    echo -e "\n${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${CYAN}  $1${NC}"
    echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}\n"
}

print_success() { echo -e "${GREEN}✓${NC} $1"; }
print_error() { echo -e "${RED}✗${NC} $1"; }
print_warning() { echo -e "${YELLOW}!${NC} $1"; }
print_info() { echo -e "${BLUE}>${NC} $1"; }
print_skip() { echo -e "${CYAN}-${NC} $1"; }

# =============================================================================
# Check Dependencies
# =============================================================================

check_dependencies() {
    if ! command -v jq &> /dev/null; then
        print_error "jq is required. Install with: sudo apt install jq"
        exit 1
    fi

    if ! command -v git &> /dev/null; then
        print_error "git is required"
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
    jq -r ".sites[\"$site\"].themes | keys[]" "$CONFIG_FILE" 2>/dev/null
}

get_theme_branch() {
    local site="$1"
    local theme="$2"
    jq -r ".sites[\"$site\"].themes[\"$theme\"].branch // \"master\"" "$CONFIG_FILE"
}

get_plugins() {
    local site="$1"
    jq -r ".sites[\"$site\"].plugins[]" "$CONFIG_FILE" 2>/dev/null
}

get_local_dir() {
    local site="$1"
    echo "${SITE_DIRS[$site]:-$site}"
}

# =============================================================================
# Git Functions
# =============================================================================

is_git_repo() {
    local path="$1"
    [ -d "$path/.git" ]
}

clone_repo() {
    local repo_url="$1"
    local target_path="$2"
    local branch="$3"
    local name="$4"

    print_info "Cloning $name..."

    local parent_dir=$(dirname "$target_path")
    if [ ! -d "$parent_dir" ]; then
        print_error "Parent directory does not exist: $parent_dir"
        return 1
    fi

    # Remove existing directory if it exists (non-git)
    if [ -d "$target_path" ]; then
        print_info "Removing existing non-git directory..."
        rm -rf "$target_path"
    fi

    # Clone with specific branch
    if git clone -b "$branch" "$repo_url" "$target_path" 2>&1; then
        print_success "Cloned $name (branch: $branch)"
        return 0
    else
        print_error "Failed to clone $name"
        return 1
    fi
}

# =============================================================================
# Check and Setup Functions
# =============================================================================

check_theme() {
    local site="$1"
    local theme="$2"
    local local_dir=$(get_local_dir "$site")
    local theme_path="$WWW_DIR/$local_dir/public_html/wp-content/themes/$theme"
    local branch=$(get_theme_branch "$site" "$theme")

    echo -e "\n  ${CYAN}Theme: $theme${NC}"

    # Check if theme is in whitelist
    if [ -z "${THEME_REPOS[$theme]}" ]; then
        print_skip "  Not in whitelist, skipping"
        return 0
    fi

    # Check if path exists
    if [ ! -d "$(dirname "$theme_path")" ]; then
        print_warning "  Themes directory doesn't exist: $(dirname "$theme_path")"
        return 1
    fi

    # Check if it's already a git repo
    if is_git_repo "$theme_path"; then
        print_success "  Already a git repo"

        # Check current branch
        local current_branch=$(cd "$theme_path" && git rev-parse --abbrev-ref HEAD 2>/dev/null)
        if [ "$current_branch" != "$branch" ]; then
            print_warning "  Current branch: $current_branch (expected: $branch)"
        else
            print_info "  Branch: $branch"
        fi
        return 0
    fi

    # Directory exists but not a git repo
    if [ -d "$theme_path" ]; then
        print_warning "  Exists but NOT a git repo"
        if [ "$DRY_RUN" = "true" ]; then
            print_info "  Would clone: ${THEME_REPOS[$theme]} (branch: $branch)"
        else
            clone_repo "${THEME_REPOS[$theme]}" "$theme_path" "$branch" "$theme"
        fi
    else
        print_warning "  Does not exist"
        if [ "$DRY_RUN" = "true" ]; then
            print_info "  Would clone: ${THEME_REPOS[$theme]} (branch: $branch)"
        else
            clone_repo "${THEME_REPOS[$theme]}" "$theme_path" "$branch" "$theme"
        fi
    fi
}

check_plugin() {
    local site="$1"
    local plugin="$2"
    local local_dir=$(get_local_dir "$site")
    local plugin_path="$WWW_DIR/$local_dir/public_html/wp-content/plugins/$plugin"

    echo -e "\n  ${CYAN}Plugin: $plugin${NC}"

    # Check if plugin is in whitelist
    if [ -z "${PLUGIN_REPOS[$plugin]}" ]; then
        print_skip "  Not in whitelist, skipping"
        return 0
    fi

    # Check if path exists
    if [ ! -d "$(dirname "$plugin_path")" ]; then
        print_warning "  Plugins directory doesn't exist: $(dirname "$plugin_path")"
        return 1
    fi

    # Check if it's already a git repo
    if is_git_repo "$plugin_path"; then
        print_success "  Already a git repo"
        return 0
    fi

    # Directory exists but not a git repo
    if [ -d "$plugin_path" ]; then
        print_warning "  Exists but NOT a git repo"
        if [ "$DRY_RUN" = "true" ]; then
            print_info "  Would clone: ${PLUGIN_REPOS[$plugin]} (branch: master)"
        else
            clone_repo "${PLUGIN_REPOS[$plugin]}" "$plugin_path" "master" "$plugin"
        fi
    else
        print_warning "  Does not exist"
        if [ "$DRY_RUN" = "true" ]; then
            print_info "  Would clone: ${PLUGIN_REPOS[$plugin]} (branch: master)"
        else
            clone_repo "${PLUGIN_REPOS[$plugin]}" "$plugin_path" "master" "$plugin"
        fi
    fi
}

check_site() {
    local site="$1"
    local name=$(get_site_field "$site" "name")
    local local_dir=$(get_local_dir "$site")
    local site_path="$WWW_DIR/$local_dir"

    print_header "$name (local: $local_dir)"

    # Check if local site directory exists
    if [ ! -d "$site_path" ]; then
        print_error "Local site directory not found: $site_path"
        return 1
    fi

    print_success "Site directory exists"

    # Check themes
    echo -e "\n${YELLOW}Checking Themes:${NC}"
    for theme in $(get_themes "$site"); do
        check_theme "$site" "$theme"
    done

    # Check plugins
    echo -e "\n${YELLOW}Checking Plugins:${NC}"
    for plugin in $(get_plugins "$site"); do
        check_plugin "$site" "$plugin"
    done
}

# =============================================================================
# Main Functions
# =============================================================================

check_all_sites() {
    print_header "Checking All Sites from sites.json"

    for site in $(get_sites); do
        check_site "$site"
        echo ""
    done
}

show_whitelist() {
    print_header "Whitelisted Repositories"

    echo -e "${YELLOW}Themes:${NC}"
    for theme in "${!THEME_REPOS[@]}"; do
        echo -e "  ${GREEN}$theme${NC}"
        echo -e "    ${CYAN}${THEME_REPOS[$theme]}${NC}"
    done

    echo -e "\n${YELLOW}Plugins:${NC}"
    for plugin in "${!PLUGIN_REPOS[@]}"; do
        echo -e "  ${GREEN}$plugin${NC}"
        echo -e "    ${CYAN}${PLUGIN_REPOS[$plugin]}${NC}"
    done
    echo ""
}

show_site_mapping() {
    print_header "Site Key to Local Directory Mapping"

    for site in "${!SITE_DIRS[@]}"; do
        echo -e "  ${GREEN}$site${NC} -> ${CYAN}${SITE_DIRS[$site]}${NC}"
    done
    echo ""
}

# =============================================================================
# Usage
# =============================================================================

show_usage() {
    echo "Usage: $0 [OPTIONS] [COMMAND]"
    echo ""
    echo "Commands:"
    echo "  (no args)      Check all sites and setup missing repos"
    echo "  check          Check all sites (same as no args)"
    echo "  site <name>    Check specific site only"
    echo "  whitelist      Show whitelisted repositories"
    echo "  mapping        Show site key to local directory mapping"
    echo ""
    echo "Options:"
    echo "  --dry-run      Show what would be done without making changes"
    echo "  -h, --help     Show this help"
    echo ""
    echo "Examples:"
    echo "  $0                    # Check and setup all sites"
    echo "  $0 --dry-run          # Preview changes without making them"
    echo "  $0 site ulg           # Check only the ULG site"
    echo "  $0 whitelist          # Show approved git repos"
    echo ""
}

# =============================================================================
# Main
# =============================================================================

DRY_RUN="false"

# Parse options
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
            break
            ;;
    esac
done

check_dependencies

# Check config file exists
if [ ! -f "$CONFIG_FILE" ]; then
    print_error "Config file not found: $CONFIG_FILE"
    exit 1
fi

if [ "$DRY_RUN" = "true" ]; then
    print_warning "DRY RUN MODE - No changes will be made"
fi

# Handle commands
case ${1:-check} in
    check)
        check_all_sites
        ;;
    site)
        if [ -z "$2" ]; then
            print_error "Site name required"
            show_usage
            exit 1
        fi
        # Verify site exists in config
        if ! get_sites | grep -q "^$2$"; then
            print_error "Unknown site: $2"
            echo "Available sites: $(get_sites | tr '\n' ' ')"
            exit 1
        fi
        check_site "$2"
        ;;
    whitelist)
        show_whitelist
        ;;
    mapping)
        show_site_mapping
        ;;
    *)
        print_error "Unknown command: $1"
        show_usage
        exit 1
        ;;
esac

if [ "$DRY_RUN" = "true" ]; then
    echo ""
    print_info "Run without --dry-run to apply changes"
fi
