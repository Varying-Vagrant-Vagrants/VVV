#!/bin/bash

# Plugin Repository Manager
# Checks plugin repositories for uncommitted/untracked changes and offers to pull updates

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Function to display usage
usage() {
    echo "Usage: $0 <plugins_directory_path>"
    echo "Example: $0 /home/jim/Projects/vagrant-local/www/pegasus/public_html/wp-content/plugins"
    echo ""
    echo "This script will:"
    echo "  - Check all plugin repositories in the specified directory"
    echo "  - Report uncommitted/untracked changes"
    echo "  - Offer to pull updates for repositories that are behind remote"
    exit 1
}

# Function to check if directory is a git repository
is_git_repo() {
    local dir="$1"
    [[ -d "$dir/.git" ]]
}

# Function to get current branch
get_current_branch() {
    local dir="$1"
    cd "$dir" && git rev-parse --abbrev-ref HEAD 2>/dev/null
}

# Function to check if there are uncommitted changes
has_uncommitted_changes() {
    local dir="$1"
    cd "$dir" && [[ -n "$(git status --porcelain 2>/dev/null)" ]]
}

# Function to check if there are untracked files
has_untracked_files() {
    local dir="$1"
    cd "$dir" && [[ -n "$(git status --porcelain 2>/dev/null | grep '^??')" ]]
}

# Function to check if branch is behind remote
is_behind_remote() {
    local dir="$1"
    cd "$dir" && git fetch --quiet 2>/dev/null
    local behind=$(git rev-list --count HEAD..@{u} 2>/dev/null)
    [[ -n "$behind" && "$behind" -gt 0 ]]
}

# Function to get remote URL
get_remote_url() {
    local dir="$1"
    cd "$dir" && git config --get remote.origin.url 2>/dev/null
}

# Function to get commits behind count
get_commits_behind() {
    local dir="$1"
    cd "$dir" && git rev-list --count HEAD..@{u} 2>/dev/null
}

# Function to perform git pull
do_git_pull() {
    local dir="$1"
    local plugin_name="$2"
    
    echo -e "${CYAN}Pulling updates for $plugin_name...${NC}"
    cd "$dir" && git pull
    
    if [[ $? -eq 0 ]]; then
        echo -e "${GREEN}✓ Successfully updated $plugin_name${NC}"
        return 0
    else
        echo -e "${RED}✗ Failed to update $plugin_name${NC}"
        return 1
    fi
}

# Function to ask for confirmation
ask_confirmation() {
    local prompt="$1"
    local response
    
    while true; do
        read -p "$prompt (y/n/a=yes to all/q=quit): " response
        case $response in
            [Yy]* ) return 0;;
            [Nn]* ) return 1;;
            [Aa]* ) return 2;;  # Yes to all
            [Qq]* ) return 3;;  # Quit
            * ) echo "Please answer y (yes), n (no), a (yes to all), or q (quit).";;
        esac
    done
}

# Function to check and manage a plugin repository
check_plugin_repo() {
    local plugin_path="$1"
    local plugin_name="$(basename "$plugin_path")"
    local auto_pull="$2"
    
    if ! is_git_repo "$plugin_path"; then
        echo -e "${YELLOW}⚠${NC} $plugin_name - Not a git repository"
        return 0
    fi
    
    local current_branch=$(get_current_branch "$plugin_path")
    local remote_url=$(get_remote_url "$plugin_path")
    
    echo -e "\n${BLUE}Checking: $plugin_name${NC}"
    echo -e "  Path: $plugin_path"
    echo -e "  Branch: $current_branch"
    echo -e "  Remote: $remote_url"
    
    local has_issues=false
    local can_pull=true
    
    # Check for uncommitted changes
    if has_uncommitted_changes "$plugin_path"; then
        echo -e "  ${RED}✗ Has uncommitted changes${NC}"
        has_issues=true
        can_pull=false
    fi
    
    # Check for untracked files
    if has_untracked_files "$plugin_path"; then
        echo -e "  ${RED}✗ Has untracked files${NC}"
        has_issues=true
        can_pull=false
    fi
    
    # Check if behind remote
    if is_behind_remote "$plugin_path"; then
        local commits_behind=$(get_commits_behind "$plugin_path")
        echo -e "  ${YELLOW}⚠ Behind remote by $commits_behind commits${NC}"
        has_issues=true
        
        if [[ "$can_pull" == true ]]; then
            if [[ "$auto_pull" == true ]]; then
                do_git_pull "$plugin_path" "$plugin_name"
                return $?
            else
                echo -e "  ${CYAN}→ Can pull updates${NC}"
                local result
                ask_confirmation "Pull updates for $plugin_name?"
                result=$?
                
                case $result in
                    0) # Yes
                        do_git_pull "$plugin_path" "$plugin_name"
                        return $?
                        ;;
                    1) # No
                        echo -e "  ${YELLOW}Skipped $plugin_name${NC}"
                        return 0
                        ;;
                    2) # Yes to all
                        do_git_pull "$plugin_path" "$plugin_name"
                        return 2  # Signal to auto-pull remaining
                        ;;
                    3) # Quit
                        echo -e "\n${YELLOW}Exiting...${NC}"
                        exit 0
                        ;;
                esac
            fi
        else
            echo -e "  ${RED}→ Cannot pull due to uncommitted/untracked changes${NC}"
            echo -e "  ${CYAN}→ Commit or stash changes first${NC}"
        fi
    fi
    
    if [[ "$has_issues" == false ]]; then
        echo -e "  ${GREEN}✓ Clean and up to date${NC}"
    fi
    
    return 0
}

# Main execution
main() {
    # Check if path argument is provided
    if [[ $# -eq 0 ]]; then
        usage
    fi
    
    local plugins_dir="$1"
    
    # Check if directory exists
    if [[ ! -d "$plugins_dir" ]]; then
        echo -e "${RED}Error: Directory '$plugins_dir' does not exist${NC}"
        exit 1
    fi
    
    echo -e "${BLUE}Plugin Repository Manager${NC}"
    echo -e "${BLUE}Scanning: $plugins_dir${NC}"
    echo -e "${BLUE}$(date)${NC}"
    echo -e "${BLUE}========================${NC}"
    
    local auto_pull=false
    local clean_count=0
    local issue_count=0
    local pulled_count=0
    
    # Find all directories that are git repositories
    local plugin_repos=()
    while IFS= read -r -d '' plugin_path; do
        if is_git_repo "$plugin_path"; then
            plugin_repos+=("$plugin_path")
        fi
    done < <(find "$plugins_dir" -maxdepth 1 -type d -print0)
    
    if [[ ${#plugin_repos[@]} -eq 0 ]]; then
        echo -e "${YELLOW}No git repositories found in $plugins_dir${NC}"
        exit 0
    fi
    
    echo -e "\n${BLUE}Found ${#plugin_repos[@]} git repositories${NC}"
    
    # Check each plugin repository
    for plugin_path in "${plugin_repos[@]}"; do
        local result
        check_plugin_repo "$plugin_path" "$auto_pull"
        result=$?
        
        case $result in
            0) # Normal completion
                if is_git_repo "$plugin_path" && ! has_uncommitted_changes "$plugin_path" && ! has_untracked_files "$plugin_path" && ! is_behind_remote "$plugin_path"; then
                    clean_count=$((clean_count + 1))
                else
                    issue_count=$((issue_count + 1))
                fi
                ;;
            1) # Pull failed
                issue_count=$((issue_count + 1))
                ;;
            2) # Yes to all selected
                auto_pull=true
                pulled_count=$((pulled_count + 1))
                ;;
        esac
    done
    
    # Summary
    echo -e "\n${BLUE}========================${NC}"
    echo -e "${BLUE}Summary:${NC}"
    echo -e "${GREEN}Clean repositories: $clean_count${NC}"
    echo -e "${YELLOW}Repositories with issues: $issue_count${NC}"
    echo -e "${CYAN}Repositories updated: $pulled_count${NC}"
    echo -e "${BLUE}Total repositories: ${#plugin_repos[@]}${NC}"
}

# Run main function with all arguments
main "$@"