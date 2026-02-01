#!/bin/bash

# =============================================================================
# Git Status Check Script
# Check status of git repos in local VVV WordPress sites
# =============================================================================

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
MAGENTA='\033[0;35m'
NC='\033[0m'

# Script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
VVV_ROOT="$(dirname "$SCRIPT_DIR")"
CONFIG_FILE="$SCRIPT_DIR/repos.json"
WWW_DIR="$VVV_ROOT/www"

# Options
DO_FETCH="false"

# =============================================================================
# Helper Functions
# =============================================================================

print_header() {
    echo -e "\n${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${CYAN}  $1${NC}"
    echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}\n"
}

print_subheader() {
    echo -e "\n${YELLOW}  ── $1 ──${NC}\n"
}

print_success() { echo -e "  ${GREEN}✓${NC} $1"; }
print_error() { echo -e "  ${RED}✗${NC} $1"; }
print_warning() { echo -e "  ${YELLOW}!${NC} $1"; }
print_info() { echo -e "  ${BLUE}>${NC} $1"; }

# =============================================================================
# Check Dependencies
# =============================================================================

check_dependencies() {
    if ! command -v jq &> /dev/null; then
        echo -e "${RED}✗${NC} jq is required. Install with: sudo apt install jq"
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

# =============================================================================
# Git Status Functions
# =============================================================================

get_behind_count() {
    local path="$1"
    local current_branch="$2"

    # Try upstream first
    local ahead_behind=$(cd "$path" && git rev-list --left-right --count @{upstream}...HEAD 2>/dev/null)

    if [ -n "$ahead_behind" ]; then
        echo "$ahead_behind"
        return 0
    fi

    # Fall back to origin/<branch>
    local remote_branch="origin/$current_branch"
    if cd "$path" && git rev-parse --verify "$remote_branch" >/dev/null 2>&1; then
        ahead_behind=$(cd "$path" && git rev-list --left-right --count "$remote_branch"...HEAD 2>/dev/null)
        if [ -n "$ahead_behind" ]; then
            echo "$ahead_behind"
            return 0
        fi
    fi

    # Try origin/master as last resort
    if cd "$path" && git rev-parse --verify "origin/master" >/dev/null 2>&1; then
        ahead_behind=$(cd "$path" && git rev-list --left-right --count origin/master...HEAD 2>/dev/null)
        if [ -n "$ahead_behind" ]; then
            echo "$ahead_behind"
            return 0
        fi
    fi

    echo ""
}

check_repo_status() {
    local path="$1"
    local name="$2"
    local expected_branch="$3"

    echo -e "  ${MAGENTA}$name${NC}"

    # Check if directory exists
    if [ ! -d "$path" ]; then
        print_error "Directory not found"
        return 1
    fi

    # Check if it's a git repo
    if [ ! -d "$path/.git" ]; then
        print_warning "Not a git repo"
        return 1
    fi

    # Optionally fetch from remote
    if [ "$DO_FETCH" = "true" ]; then
        print_info "Fetching..."
        (cd "$path" && git fetch --quiet 2>/dev/null)
    fi

    # Get current branch
    local current_branch=$(cd "$path" && git rev-parse --abbrev-ref HEAD 2>/dev/null)

    # Check if branch matches expected
    if [ -n "$expected_branch" ] && [ "$current_branch" != "$expected_branch" ]; then
        print_warning "Branch: ${YELLOW}$current_branch${NC} (expected: $expected_branch)"
    else
        print_info "Branch: ${GREEN}$current_branch${NC}"
    fi

    # Get status
    local status_output=$(cd "$path" && git status --porcelain 2>/dev/null)

    if [ -n "$status_output" ]; then
        local modified=$(echo "$status_output" | grep -c "^ M\|^M ")
        local untracked=$(echo "$status_output" | grep -c "^??")
        local staged=$(echo "$status_output" | grep -c "^[MADRC]")

        if [ "$modified" -gt 0 ]; then
            print_warning "${YELLOW}$modified modified${NC}"
        fi
        if [ "$untracked" -gt 0 ]; then
            print_info "${BLUE}$untracked untracked${NC}"
        fi
        if [ "$staged" -gt 0 ]; then
            print_success "${GREEN}$staged staged${NC}"
        fi
    else
        print_success "Clean"
    fi

    # Check ahead/behind
    local ahead_behind=$(get_behind_count "$path" "$current_branch")

    if [ -n "$ahead_behind" ]; then
        local behind=$(echo "$ahead_behind" | cut -f1)
        local ahead=$(echo "$ahead_behind" | cut -f2)

        if [ "$behind" -gt 0 ]; then
            print_warning "${RED}$behind commits behind${NC} remote"
        fi
        if [ "$ahead" -gt 0 ]; then
            print_info "${CYAN}$ahead commits ahead${NC} of remote"
        fi
        if [ "$behind" -eq 0 ] && [ "$ahead" -eq 0 ]; then
            print_success "Up to date with remote"
        fi
    else
        print_info "No remote tracking"
    fi

    echo ""
}

# =============================================================================
# Site Check Functions
# =============================================================================

check_site() {
    local site="$1"
    local name=$(get_site_field "$site" "name")
    local local_dir=$(get_site_field "$site" "local_dir")
    local site_path="$WWW_DIR/$local_dir"

    print_header "$name ($local_dir)"

    # Check if site directory exists
    if [ ! -d "$site_path" ]; then
        print_error "Site directory not found: $site_path"
        return 1
    fi

    # Check themes
    print_subheader "Themes"
    for theme in $(get_themes "$site"); do
        local theme_path="$site_path/public_html/wp-content/themes/$theme"
        local expected_branch=$(get_theme_branch "$site" "$theme")
        check_repo_status "$theme_path" "$theme" "$expected_branch"
    done

    # Check plugins
    print_subheader "Plugins"
    for plugin in $(get_plugins "$site"); do
        local plugin_path="$site_path/public_html/wp-content/plugins/$plugin"
        check_repo_status "$plugin_path" "$plugin" "master"
    done
}

check_all_sites() {
    print_header "Git Status - All Sites"
    echo -e "${BLUE}Checking local VVV repos...${NC}"

    for site in $(get_sites); do
        check_site "$site"
    done
}

# =============================================================================
# Summary Function
# =============================================================================

show_summary() {
    print_header "Summary"

    local total=0
    local git_repos=0
    local not_git=0
    local dirty=0
    local clean=0
    local behind_count=0
    local repos_behind=""

    for site in $(get_sites); do
        local local_dir=$(get_site_field "$site" "local_dir")
        local site_path="$WWW_DIR/$local_dir"

        for theme in $(get_themes "$site"); do
            local path="$site_path/public_html/wp-content/themes/$theme"
            ((total++))
            if [ -d "$path/.git" ]; then
                ((git_repos++))
                local status=$(cd "$path" && git status --porcelain 2>/dev/null)
                if [ -z "$status" ]; then
                    ((clean++))
                else
                    ((dirty++))
                fi

                # Check if behind
                local current_branch=$(cd "$path" && git rev-parse --abbrev-ref HEAD 2>/dev/null)
                local ahead_behind=$(get_behind_count "$path" "$current_branch")
                if [ -n "$ahead_behind" ]; then
                    local behind=$(echo "$ahead_behind" | cut -f1)
                    if [ "$behind" -gt 0 ]; then
                        ((behind_count++))
                        repos_behind="$repos_behind\n    ${YELLOW}$local_dir/$theme${NC} (${RED}$behind behind${NC})"
                    fi
                fi
            else
                ((not_git++))
            fi
        done

        for plugin in $(get_plugins "$site"); do
            local path="$site_path/public_html/wp-content/plugins/$plugin"
            ((total++))
            if [ -d "$path/.git" ]; then
                ((git_repos++))
                local status=$(cd "$path" && git status --porcelain 2>/dev/null)
                if [ -z "$status" ]; then
                    ((clean++))
                else
                    ((dirty++))
                fi

                # Check if behind
                local current_branch=$(cd "$path" && git rev-parse --abbrev-ref HEAD 2>/dev/null)
                local ahead_behind=$(get_behind_count "$path" "$current_branch")
                if [ -n "$ahead_behind" ]; then
                    local behind=$(echo "$ahead_behind" | cut -f1)
                    if [ "$behind" -gt 0 ]; then
                        ((behind_count++))
                        repos_behind="$repos_behind\n    ${YELLOW}$local_dir/$plugin${NC} (${RED}$behind behind${NC})"
                    fi
                fi
            else
                ((not_git++))
            fi
        done
    done

    echo -e "  Total repos tracked:  ${CYAN}$total${NC}"
    echo -e "  Git repos:            ${GREEN}$git_repos${NC}"
    echo -e "  Not git repos:        ${YELLOW}$not_git${NC}"
    echo -e "  Clean:                ${GREEN}$clean${NC}"
    echo -e "  Dirty (changes):      ${YELLOW}$dirty${NC}"
    echo -e "  Behind remote:        ${RED}$behind_count${NC}"

    if [ "$behind_count" -gt 0 ]; then
        echo -e "\n  ${RED}Repos behind remote:${NC}"
        echo -e "$repos_behind"
    fi
    echo ""
}

# =============================================================================
# Usage
# =============================================================================

show_usage() {
    echo "Usage: $0 [OPTIONS] [COMMAND]"
    echo ""
    echo "Options:"
    echo "  --fetch        Fetch from remotes before checking status"
    echo "  -h, --help     Show this help"
    echo ""
    echo "Commands:"
    echo "  (no args)      Check all sites"
    echo "  site <name>    Check specific site"
    echo "  summary        Show summary only"
    echo "  list           List configured sites"
    echo ""
    echo "Examples:"
    echo "  $0                    Check all sites"
    echo "  $0 --fetch            Fetch remotes first, then check all"
    echo "  $0 --fetch site ulg   Fetch and check specific site"
    echo ""
    echo "Sites: $(get_sites | tr '\n' ' ')"
    echo ""
}

# =============================================================================
# Main
# =============================================================================

check_dependencies

if [ ! -f "$CONFIG_FILE" ]; then
    echo -e "${RED}✗${NC} Config file not found: $CONFIG_FILE"
    exit 1
fi

# Parse options
while [[ $# -gt 0 ]]; do
    case $1 in
        --fetch)
            DO_FETCH="true"
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

if [ "$DO_FETCH" = "true" ]; then
    echo -e "${BLUE}> Will fetch from remotes before checking...${NC}"
fi

case ${1:-all} in
    all)
        check_all_sites
        show_summary
        ;;
    site)
        if [ -z "$2" ]; then
            print_error "Site name required"
            show_usage
            exit 1
        fi
        if ! get_sites | grep -q "^$2$"; then
            echo -e "${RED}✗${NC} Unknown site: $2"
            echo "Available: $(get_sites | tr '\n' ' ')"
            exit 1
        fi
        check_site "$2"
        ;;
    summary)
        show_summary
        ;;
    list)
        print_header "Configured Sites"
        for site in $(get_sites); do
            local_dir=$(get_site_field "$site" "local_dir")
            name=$(get_site_field "$site" "name")
            echo -e "  ${GREEN}$site${NC} -> $local_dir ($name)"
        done
        echo ""
        ;;
    *)
        echo -e "${RED}✗${NC} Unknown command: $1"
        show_usage
        exit 1
        ;;
esac
