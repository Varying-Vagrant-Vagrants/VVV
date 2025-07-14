#!/bin/bash

# Repository Status Checker
# Checks all GitHub repositories for branch status and uncommitted changes

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# JSON output file
JSON_OUTPUT="/home/jim/Projects/vagrant-local/repo-status.json"

# Initialize JSON structure
echo '{' > "$JSON_OUTPUT"
echo '  "scan_date": "'$(date -Iseconds)'",' >> "$JSON_OUTPUT"
echo '  "repositories": [' >> "$JSON_OUTPUT"

# Counter for comma separation
repo_count=0

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

# Function to get default branch (main/master)
get_default_branch() {
    local dir="$1"
    cd "$dir" && git symbolic-ref refs/remotes/origin/HEAD 2>/dev/null | sed 's@^refs/remotes/origin/@@'
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

# Function to check if branch is ahead of remote
is_ahead_remote() {
    local dir="$1"
    cd "$dir" && git fetch --quiet 2>/dev/null
    local ahead=$(git rev-list --count @{u}..HEAD 2>/dev/null)
    [[ -n "$ahead" && "$ahead" -gt 0 ]]
}

# Function to get remote URL
get_remote_url() {
    local dir="$1"
    cd "$dir" && git config --get remote.origin.url 2>/dev/null
}

# Function to add JSON entry
add_json_entry() {
    local path="$1"
    local current_branch="$2"
    local default_branch="$3"
    local has_changes="$4"
    local has_untracked="$5"
    local behind="$6"
    local ahead="$7"
    local remote_url="$8"
    local status="$9"
    
    if [ $repo_count -gt 0 ]; then
        echo '    },' >> "$JSON_OUTPUT"
    fi
    
    echo '    {' >> "$JSON_OUTPUT"
    printf '      "path": %s,\n' "$(echo "$path" | jq -R .)" >> "$JSON_OUTPUT"
    printf '      "remote_url": %s,\n' "$(echo "$remote_url" | jq -R .)" >> "$JSON_OUTPUT"
    printf '      "current_branch": %s,\n' "$(echo "$current_branch" | jq -R .)" >> "$JSON_OUTPUT"
    printf '      "default_branch": %s,\n' "$(echo "$default_branch" | jq -R .)" >> "$JSON_OUTPUT"
    echo '      "has_uncommitted_changes": '$has_changes',' >> "$JSON_OUTPUT"
    echo '      "has_untracked_files": '$has_untracked',' >> "$JSON_OUTPUT"
    echo '      "behind_remote": '$behind',' >> "$JSON_OUTPUT"
    echo '      "ahead_remote": '$ahead',' >> "$JSON_OUTPUT"
    echo '      "status": "'$status'"' >> "$JSON_OUTPUT"
    
    repo_count=$((repo_count + 1))
}

# Function to check repository status
check_repo() {
    local repo_path="$1"
    local repo_name="$(basename "$repo_path")"
    
    if ! is_git_repo "$repo_path"; then
        echo -e "${RED}✗${NC} $repo_name - Not a git repository"
        return
    fi
    
    local current_branch=$(get_current_branch "$repo_path")
    local default_branch=$(get_default_branch "$repo_path")
    local remote_url=$(get_remote_url "$repo_path")
    
    # If we can't determine default branch, assume main/master
    if [[ -z "$default_branch" ]]; then
        if cd "$repo_path" && git show-ref --verify --quiet refs/heads/main; then
            default_branch="main"
        elif cd "$repo_path" && git show-ref --verify --quiet refs/heads/master; then
            default_branch="master"
        else
            default_branch="unknown"
        fi
    fi
    
    local status="OK"
    local status_msg=""
    
    # Check branch status
    if [[ "$current_branch" != "$default_branch" ]]; then
        status="WARNING"
        status_msg="${YELLOW}⚠${NC} $repo_name - On branch '$current_branch' (not $default_branch)"
    fi
    
    # Check for uncommitted changes
    local has_changes=false
    if has_uncommitted_changes "$repo_path"; then
        has_changes=true
        status="WARNING"
        if [[ -n "$status_msg" ]]; then
            status_msg="$status_msg + uncommitted changes"
        else
            status_msg="${YELLOW}⚠${NC} $repo_name - Has uncommitted changes"
        fi
    fi
    
    # Check for untracked files
    local has_untracked=false
    if has_untracked_files "$repo_path"; then
        has_untracked=true
        status="WARNING"
        if [[ -n "$status_msg" ]]; then
            status_msg="$status_msg + untracked files"
        else
            status_msg="${YELLOW}⚠${NC} $repo_name - Has untracked files"
        fi
    fi
    
    # Check if behind/ahead remote
    local behind=false
    local ahead=false
    if is_behind_remote "$repo_path"; then
        behind=true
        status="WARNING"
        if [[ -n "$status_msg" ]]; then
            status_msg="$status_msg + behind remote"
        else
            status_msg="${YELLOW}⚠${NC} $repo_name - Behind remote"
        fi
    fi
    
    if is_ahead_remote "$repo_path"; then
        ahead=true
        status="WARNING"
        if [[ -n "$status_msg" ]]; then
            status_msg="$status_msg + ahead remote"
        else
            status_msg="${YELLOW}⚠${NC} $repo_name - Ahead of remote"
        fi
    fi
    
    # Print status
    if [[ "$status" == "OK" ]]; then
        echo -e "${GREEN}✓${NC} $repo_name - Clean (on $current_branch)"
    else
        echo -e "$status_msg"
    fi
    
    # Add to JSON
    add_json_entry "$repo_path" "$current_branch" "$default_branch" "$has_changes" "$has_untracked" "$behind" "$ahead" "$remote_url" "$status"
}

# Main execution
echo -e "${BLUE}Checking repository status...${NC}\n"

# Base directory
BASE_DIR="/home/jim/Projects/vagrant-local/www"

# Find all directories with .git folders
echo -e "${BLUE}Scanning for Git repositories...${NC}"
git_repos=$(find "$BASE_DIR" -type d -name ".git" -exec dirname {} \; | sort)

if [[ -z "$git_repos" ]]; then
    echo -e "${RED}No Git repositories found in $BASE_DIR${NC}"
    exit 1
fi

# Check each repository
while IFS= read -r repo_path; do
    check_repo "$repo_path"
done <<< "$git_repos"

# Close JSON structure
if [ $repo_count -gt 0 ]; then
    echo '    }' >> "$JSON_OUTPUT"
fi
echo '  ]' >> "$JSON_OUTPUT"
echo '}' >> "$JSON_OUTPUT"

echo -e "\n${BLUE}Scan complete. Results saved to: $JSON_OUTPUT${NC}"
echo -e "${BLUE}Total repositories checked: $repo_count${NC}"

# Summary
clean_count=$(grep -c '"status": "OK"' "$JSON_OUTPUT")
warning_count=$(grep -c '"status": "WARNING"' "$JSON_OUTPUT")

echo -e "\n${GREEN}Clean repositories: $clean_count${NC}"
echo -e "${YELLOW}Repositories with issues: $warning_count${NC}"

if [[ $warning_count -gt 0 ]]; then
    echo -e "\n${YELLOW}Run with --details to see specific issues${NC}"
fi