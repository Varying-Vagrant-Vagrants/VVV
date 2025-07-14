#!/bin/bash

# Markdown Documentation Updater  
# Updates TREE.md and COMMANDS.md with latest repository information

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# File paths
BASE_DIR="/home/jim/Projects/vagrant-local/www"
TREE_FILE="/home/jim/Projects/vagrant-local/TREE.md"
COMMANDS_FILE="/home/jim/Projects/vagrant-local/COMMANDS.md"

# Function to check if directory is a git repository
is_git_repo() {
    local dir="$1"
    [[ -d "$dir/.git" ]]
}

# Function to get remote URL
get_remote_url() {
    local dir="$1"
    if is_git_repo "$dir"; then
        cd "$dir" && git config --get remote.origin.url 2>/dev/null
    else
        echo ""
    fi
}

# Function to get current branch
get_current_branch() {
    local dir="$1"
    if is_git_repo "$dir"; then
        cd "$dir" && git rev-parse --abbrev-ref HEAD 2>/dev/null
    else
        echo ""
    fi
}

# Function to extract GitHub repo info from URL
extract_github_info() {
    local url="$1"
    if [[ "$url" =~ git@github\.com:(.+)/(.+)\.git ]]; then
        echo "${BASH_REMATCH[1]}/${BASH_REMATCH[2]}"
    elif [[ "$url" =~ https://github\.com/(.+)/(.+)\.git ]]; then
        echo "${BASH_REMATCH[1]}/${BASH_REMATCH[2]}"
    elif [[ "$url" =~ https://github\.com/(.+)/(.+) ]]; then
        echo "${BASH_REMATCH[1]}/${BASH_REMATCH[2]}"
    else
        echo "$url"
    fi
}

# Function to scan directory structure
scan_directory() {
    local base_path="$1"
    local current_path="$2"
    local depth="$3"
    
    if [[ ! -d "$current_path" ]]; then
        return
    fi
    
    local indent=""
    for ((i=0; i<depth; i++)); do
        if [[ $i -eq $((depth-1)) ]]; then
            indent="${indent}├── "
        else
            indent="${indent}│   "
        fi
    done
    
    local dir_name="$(basename "$current_path")"
    local remote_url=$(get_remote_url "$current_path")
    local is_pegasus_site=false
    
    # Check if this is a pegasus-related directory
    if [[ "$dir_name" == pegasus* ]] || [[ "$current_path" == *pegasus* ]]; then
        is_pegasus_site=true
    fi
    
    # Check if site contains pegasus content
    local has_pegasus_content=false
    if [[ -d "$current_path/public_html/wp-content/themes" ]]; then
        if find "$current_path/public_html/wp-content/themes" -name "pegasus*" -type d 2>/dev/null | grep -q .; then
            has_pegasus_content=true
        fi
    fi
    if [[ -d "$current_path/public_html/wp-content/plugins" ]]; then
        if find "$current_path/public_html/wp-content/plugins" -name "pegasus*" -type d 2>/dev/null | grep -q .; then
            has_pegasus_content=true
        fi
    fi
    
    # Determine symbols
    local symbols=""
    if [[ -n "$remote_url" ]]; then
        symbols="🔗"
        if [[ "$is_pegasus_site" == true ]]; then
            symbols="${symbols}🎯"
        fi
    else
        symbols="📁"
        if [[ "$is_pegasus_site" == true ]]; then
            symbols="${symbols}🎯"
        fi
    fi
    
    # Add site marker
    if [[ "$has_pegasus_content" == true ]]; then
        symbols=" ⭐"
    fi
    
    # Format output
    local output_line="${indent}${dir_name}/"
    if [[ -n "$remote_url" ]]; then
        local github_info=$(extract_github_info "$remote_url")
        output_line="${output_line} ${symbols} [${github_info}]"
    elif [[ "$symbols" == "📁"* ]]; then
        output_line="${output_line} ${symbols} [Local folder - no GitHub repo]"
    else
        output_line="${output_line}${symbols}"
    fi
    
    echo "$output_line"
}

# Function to build directory tree
build_tree() {
    local base_dir="$1"
    local output_file="$2"
    
    echo "Building directory tree..."
    
    # Header
    cat > "$output_file" << 'EOF'
# WordPress Sites Directory Tree

This document shows the directory structure of all WordPress sites with focus on themes, plugins, and GitHub repositories.

## Legend
- 🔗 = GitHub repository (with active .git folder)
- 📁 = Local folder only (no GitHub repository)
- ⭐ = Site contains pegasus-related content
- 🎯 = pegasus-* pattern match

---

## Directory Tree Structure

```
/home/jim/Projects/vagrant-local/www/
EOF

    # Scan each site directory
    for site_dir in "$base_dir"/*; do
        if [[ -d "$site_dir" ]]; then
            local site_name="$(basename "$site_dir")"
            local has_pegasus_content=false
            
            # Check if site contains pegasus content
            if [[ -d "$site_dir/public_html/wp-content/themes" ]]; then
                if find "$site_dir/public_html/wp-content/themes" -name "pegasus*" -type d 2>/dev/null | grep -q .; then
                    has_pegasus_content=true
                fi
            fi
            if [[ -d "$site_dir/public_html/wp-content/plugins" ]]; then
                if find "$site_dir/public_html/wp-content/plugins" -name "pegasus*" -type d 2>/dev/null | grep -q .; then
                    has_pegasus_content=true
                fi
            fi
            
            # Site header
            if [[ "$has_pegasus_content" == true ]]; then
                echo "├── ${site_name}/ ⭐" >> "$output_file"
            else
                echo "├── ${site_name}/" >> "$output_file"
            fi
            
            # Public HTML
            if [[ -d "$site_dir/public_html" ]]; then
                echo "│   └── public_html/" >> "$output_file"
                
                # WP Content
                if [[ -d "$site_dir/public_html/wp-content" ]]; then
                    echo "│       └── wp-content/" >> "$output_file"
                    
                    # Themes
                    if [[ -d "$site_dir/public_html/wp-content/themes" ]]; then
                        echo "│           ├── themes/" >> "$output_file"
                        for theme_dir in "$site_dir/public_html/wp-content/themes"/*; do
                            if [[ -d "$theme_dir" ]]; then
                                local theme_name="$(basename "$theme_dir")"
                                local remote_url=$(get_remote_url "$theme_dir")
                                local symbols=""
                                
                                if [[ -n "$remote_url" ]]; then
                                    symbols="🔗"
                                    if [[ "$theme_name" == pegasus* ]]; then
                                        symbols="${symbols}🎯"
                                    fi
                                    local github_info=$(extract_github_info "$remote_url")
                                    echo "│           │   ├── ${theme_name}/ ${symbols} [${github_info}]" >> "$output_file"
                                else
                                    if [[ "$theme_name" == pegasus* ]]; then
                                        symbols="📁🎯"
                                        echo "│           │   ├── ${theme_name}/ ${symbols} [Local folder - no GitHub repo]" >> "$output_file"
                                    else
                                        echo "│           │   ├── ${theme_name}/" >> "$output_file"
                                    fi
                                fi
                            fi
                        done
                    fi
                    
                    # Plugins
                    if [[ -d "$site_dir/public_html/wp-content/plugins" ]]; then
                        echo "│           └── plugins/" >> "$output_file"
                        for plugin_item in "$site_dir/public_html/wp-content/plugins"/*; do
                            if [[ -d "$plugin_item" ]]; then
                                local plugin_name="$(basename "$plugin_item")"
                                local remote_url=$(get_remote_url "$plugin_item")
                                local symbols=""
                                
                                if [[ -n "$remote_url" ]]; then
                                    symbols="🔗"
                                    if [[ "$plugin_name" == pegasus* ]]; then
                                        symbols="${symbols}🎯"
                                    fi
                                    local github_info=$(extract_github_info "$remote_url")
                                    echo "│               ├── ${plugin_name}/ ${symbols} [${github_info}]" >> "$output_file"
                                else
                                    if [[ "$plugin_name" == pegasus* ]]; then
                                        symbols="📁🎯"
                                        echo "│               ├── ${plugin_name}/ ${symbols} [Local folder - no GitHub repo]" >> "$output_file"
                                    else
                                        echo "│               ├── ${plugin_name}/" >> "$output_file"
                                    fi
                                fi
                            elif [[ -f "$plugin_item" ]]; then
                                local plugin_name="$(basename "$plugin_item")"
                                echo "│               └── ${plugin_name}" >> "$output_file"
                            fi
                        done
                    fi
                fi
            fi
            echo "│" >> "$output_file"
        fi
    done
    
    echo '```' >> "$output_file"
    echo '' >> "$output_file"
    echo '---' >> "$output_file"
    echo '' >> "$output_file"
    
    # Generate statistics
    generate_statistics >> "$output_file"
}

# Function to generate statistics
generate_statistics() {
    local pegasus_sites=0
    local total_repos=0
    local total_local=0
    
    echo "## Summary Statistics"
    echo ""
    echo "### **Sites with pegasus-* content:** $(count_pegasus_sites) sites"
    
    # Count repositories per site
    for site_dir in "$BASE_DIR"/*; do
        if [[ -d "$site_dir" ]]; then
            local site_name="$(basename "$site_dir")"
            local has_pegasus_content=false
            local repo_count=0
            local local_count=0
            
            # Check themes
            if [[ -d "$site_dir/public_html/wp-content/themes" ]]; then
                for theme_dir in "$site_dir/public_html/wp-content/themes"/*; do
                    if [[ -d "$theme_dir" ]]; then
                        local theme_name="$(basename "$theme_dir")"
                        if [[ "$theme_name" == pegasus* ]]; then
                            has_pegasus_content=true
                            if is_git_repo "$theme_dir"; then
                                repo_count=$((repo_count + 1))
                            else
                                local_count=$((local_count + 1))
                            fi
                        fi
                    fi
                done
            fi
            
            # Check plugins
            if [[ -d "$site_dir/public_html/wp-content/plugins" ]]; then
                for plugin_dir in "$site_dir/public_html/wp-content/plugins"/*; do
                    if [[ -d "$plugin_dir" ]]; then
                        local plugin_name="$(basename "$plugin_dir")"
                        if [[ "$plugin_name" == pegasus* ]]; then
                            has_pegasus_content=true
                            if is_git_repo "$plugin_dir"; then
                                repo_count=$((repo_count + 1))
                            else
                                local_count=$((local_count + 1))
                            fi
                        fi
                    fi
                done
            fi
            
            if [[ "$has_pegasus_content" == true ]]; then
                if [[ $local_count -gt 0 ]]; then
                    echo "- $site_name ($repo_count repositories + $local_count local folders)"
                else
                    echo "- $site_name ($repo_count repositories)"
                fi
                total_repos=$((total_repos + repo_count))
                total_local=$((total_local + local_count))
            fi
        fi
    done
    
    echo ""
    echo "### **Total pegasus-* items found:** $((total_repos + total_local)) items"
    echo "- **GitHub repositories:** $total_repos repositories"
    echo "- **Local folders only:** $total_local folders"
    echo ""
    
    # Generate organization stats
    echo "### **GitHub Organizations:**"
    declare -A org_counts
    for site_dir in "$BASE_DIR"/*; do
        if [[ -d "$site_dir" ]]; then
            # Check themes
            if [[ -d "$site_dir/public_html/wp-content/themes" ]]; then
                for theme_dir in "$site_dir/public_html/wp-content/themes"/*; do
                    if [[ -d "$theme_dir" ]] && is_git_repo "$theme_dir"; then
                        local remote_url=$(get_remote_url "$theme_dir")
                        if [[ -n "$remote_url" ]]; then
                            local github_info=$(extract_github_info "$remote_url")
                            local org="${github_info%/*}"
                            org_counts["$org"]=$((org_counts["$org"] + 1))
                        fi
                    fi
                done
            fi
            
            # Check plugins
            if [[ -d "$site_dir/public_html/wp-content/plugins" ]]; then
                for plugin_dir in "$site_dir/public_html/wp-content/plugins"/*; do
                    if [[ -d "$plugin_dir" ]] && is_git_repo "$plugin_dir"; then
                        local remote_url=$(get_remote_url "$plugin_dir")
                        if [[ -n "$remote_url" ]]; then
                            local github_info=$(extract_github_info "$remote_url")
                            local org="${github_info%/*}"
                            org_counts["$org"]=$((org_counts["$org"] + 1))
                        fi
                    fi
                done
            fi
        fi
    done
    
    for org in "${!org_counts[@]}"; do
        echo "- **$org:** ${org_counts[$org]} repositories"
    done
    
    echo ""
    echo "### **Repository URLs:**"
    echo "All Visionquest-Development repositories follow the pattern:"
    echo "- \`git@github.com:Visionquest-Development/[plugin-name].git\`"
    echo "- Main theme: \`git@github.com:Visionquest-Development/pegasus.git\`"
}

# Function to count pegasus sites
count_pegasus_sites() {
    local count=0
    for site_dir in "$BASE_DIR"/*; do
        if [[ -d "$site_dir" ]]; then
            local has_pegasus_content=false
            
            # Check themes
            if [[ -d "$site_dir/public_html/wp-content/themes" ]]; then
                if find "$site_dir/public_html/wp-content/themes" -name "pegasus*" -type d 2>/dev/null | grep -q .; then
                    has_pegasus_content=true
                fi
            fi
            
            # Check plugins
            if [[ -d "$site_dir/public_html/wp-content/plugins" ]]; then
                if find "$site_dir/public_html/wp-content/plugins" -name "pegasus*" -type d 2>/dev/null | grep -q .; then
                    has_pegasus_content=true
                fi
            fi
            
            if [[ "$has_pegasus_content" == true ]]; then
                count=$((count + 1))
            fi
        fi
    done
    echo "$count"
}

# Function to update COMMANDS.md
update_commands_file() {
    local output_file="$1"
    
    echo "Updating COMMANDS.md..."
    
    # Header
    cat > "$output_file" << 'EOF'
# WordPress Sites Navigation Guide

This document provides easy navigation commands for all WordPress sites configured in your VVV environment.

## Quick Navigation Commands

### WordPress Sites Root Directory
```bash
# Main www directory
cd /home/jim/Projects/vagrant-local/www/
```

## Site-Specific Navigation

EOF

    # Generate navigation for each site
    for site_dir in "$BASE_DIR"/*; do
        if [[ -d "$site_dir" ]]; then
            local site_name="$(basename "$site_dir")"
            
            echo "### $site_name" >> "$output_file"
            echo "**Site:** ${site_name}.test | **Template:** Varying-Vagrant-Vagrants/custom-site-template" >> "$output_file"
            echo '```bash' >> "$output_file"
            echo "# Site root" >> "$output_file"
            echo "cd /home/jim/Projects/vagrant-local/www/$site_name/" >> "$output_file"
            echo "" >> "$output_file"
            echo "# Public HTML directory" >> "$output_file"
            echo "cd /home/jim/Projects/vagrant-local/www/$site_name/public_html/" >> "$output_file"
            echo "" >> "$output_file"
            echo "# WordPress content directory" >> "$output_file"
            echo "cd /home/jim/Projects/vagrant-local/www/$site_name/public_html/wp-content/" >> "$output_file"
            
            # Add GitHub repo navigation
            local has_repos=false
            
            # Check themes
            if [[ -d "$site_dir/public_html/wp-content/themes" ]]; then
                for theme_dir in "$site_dir/public_html/wp-content/themes"/*; do
                    if [[ -d "$theme_dir" ]] && is_git_repo "$theme_dir"; then
                        local theme_name="$(basename "$theme_dir")"
                        if [[ "$has_repos" == false ]]; then
                            echo "" >> "$output_file"
                            echo "# Theme directories (GitHub repos)" >> "$output_file"
                            has_repos=true
                        fi
                        echo "cd /home/jim/Projects/vagrant-local/www/$site_name/public_html/wp-content/themes/$theme_name/" >> "$output_file"
                    fi
                done
            fi
            
            # Check plugins
            if [[ -d "$site_dir/public_html/wp-content/plugins" ]]; then
                local plugin_repos=()
                for plugin_dir in "$site_dir/public_html/wp-content/plugins"/*; do
                    if [[ -d "$plugin_dir" ]] && is_git_repo "$plugin_dir"; then
                        local plugin_name="$(basename "$plugin_dir")"
                        local remote_url=$(get_remote_url "$plugin_dir")
                        local github_info=$(extract_github_info "$remote_url")
                        plugin_repos+=("$plugin_name:$github_info")
                    fi
                done
                
                if [[ ${#plugin_repos[@]} -gt 0 ]]; then
                    echo "" >> "$output_file"
                    echo "# Plugins directory" >> "$output_file"
                    echo "cd /home/jim/Projects/vagrant-local/www/$site_name/public_html/wp-content/plugins/" >> "$output_file"
                    echo "" >> "$output_file"
                    echo "# Plugin GitHub repositories:" >> "$output_file"
                    
                    for repo_info in "${plugin_repos[@]}"; do
                        local plugin_name="${repo_info%:*}"
                        local github_info="${repo_info#*:}"
                        echo "cd /home/jim/Projects/vagrant-local/www/$site_name/public_html/wp-content/plugins/$plugin_name/ # https://github.com/$github_info" >> "$output_file"
                    done
                fi
            fi
            
            echo '```' >> "$output_file"
            echo "" >> "$output_file"
        fi
    done
    
    # Add common patterns and summary
    cat >> "$output_file" << 'EOF'
## Common Directory Patterns

```bash
# For sites with custom themes:
cd /home/jim/Projects/vagrant-local/www/[SITE_NAME]/public_html/wp-content/themes/pegasus/

# For WordPress content:
cd /home/jim/Projects/vagrant-local/www/[SITE_NAME]/public_html/wp-content/

# For plugins:
cd /home/jim/Projects/vagrant-local/www/[SITE_NAME]/public_html/wp-content/plugins/

# For specific plugin:
cd /home/jim/Projects/vagrant-local/www/[SITE_NAME]/public_html/wp-content/plugins/[PLUGIN_NAME]/

# For site root:
cd /home/jim/Projects/vagrant-local/www/[SITE_NAME]/
```

## Site Status Summary

EOF

    # Generate site summary
    local total_sites=$(find "$BASE_DIR" -maxdepth 1 -type d | wc -l)
    total_sites=$((total_sites - 1)) # Subtract base directory
    
    local pegasus_sites=$(count_pegasus_sites)
    local total_repos=$(count_total_repos)
    
    echo "- **Active sites:** $total_sites total sites configured" >> "$output_file"
    echo "- **Sites with custom themes:** $pegasus_sites sites using Pegasus theme" >> "$output_file"
    echo "- **Total GitHub plugin repos:** $total_repos plugin repositories across all sites" >> "$output_file"
}

# Function to count total repos
count_total_repos() {
    local count=0
    for site_dir in "$BASE_DIR"/*; do
        if [[ -d "$site_dir" ]]; then
            # Check themes
            if [[ -d "$site_dir/public_html/wp-content/themes" ]]; then
                for theme_dir in "$site_dir/public_html/wp-content/themes"/*; do
                    if [[ -d "$theme_dir" ]] && is_git_repo "$theme_dir"; then
                        count=$((count + 1))
                    fi
                done
            fi
            
            # Check plugins
            if [[ -d "$site_dir/public_html/wp-content/plugins" ]]; then
                for plugin_dir in "$site_dir/public_html/wp-content/plugins"/*; do
                    if [[ -d "$plugin_dir" ]] && is_git_repo "$plugin_dir"; then
                        count=$((count + 1))
                    fi
                done
            fi
        fi
    done
    echo "$count"
}


# Function to check if directory has pegasus content
has_pegasus_content() {
    local dir="$1"
    
    # Check themes
    if [[ -d "$dir/public_html/wp-content/themes" ]]; then
        if find "$dir/public_html/wp-content/themes" -name "pegasus*" -type d 2>/dev/null | grep -q .; then
            echo "true"
            return
        fi
    fi
    
    # Check plugins
    if [[ -d "$dir/public_html/wp-content/plugins" ]]; then
        if find "$dir/public_html/wp-content/plugins" -name "pegasus*" -type d 2>/dev/null | grep -q .; then
            echo "true"
            return
        fi
    fi
    
    echo "false"
}

# Main execution
echo -e "${BLUE}Updating markdown documentation...${NC}\n"

# Build TREE.md
echo -e "${BLUE}Building TREE.md...${NC}"
build_tree "$BASE_DIR" "$TREE_FILE"

# Build COMMANDS.md
echo -e "${BLUE}Building COMMANDS.md...${NC}"
update_commands_file "$COMMANDS_FILE"

echo -e "\n${GREEN}Documentation updated successfully!${NC}"
echo -e "${GREEN}Files updated:${NC}"
echo -e "  - $TREE_FILE"
echo -e "  - $COMMANDS_FILE"

# Show summary
echo -e "\n${BLUE}Summary:${NC}"
echo -e "  - Total sites: $(find "$BASE_DIR" -maxdepth 1 -type d | wc -l | awk '{print $1-1}')"
echo -e "  - Pegasus sites: $(count_pegasus_sites)"
echo -e "  - Total repositories: $(count_total_repos)"