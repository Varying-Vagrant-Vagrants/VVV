const express = require('express');
const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');
const browserSync = require('browser-sync').create();

const app = express();
const PORT = 3000;

// Blacklist of WordPress sites to ignore
const WORDPRESS_SITE_BLACKLIST = [
  'wordpress-one',
  'wordpress-two', 
  'phpcs',
  'default',
  'wordpress-trunk'
];

app.use(express.static('public'));

app.get('/', (req, res) => {
  res.send(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>VVV Development Environment</title>
      <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .nav-link { display: inline-block; margin: 10px; padding: 15px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
        .nav-link:hover { background: #0056b3; }
        .nav-link.commands { background: #28a745; }
        .nav-link.commands:hover { background: #218838; }
        h1 { color: #333; }
      </style>
    </head>
    <body>
      <div class="container">
        <h1>VVV Development Environment</h1>
        <h2>Available Pages:</h2>
        <a href="/commands" class="nav-link commands">Commands & Repository Guide</a>
        <a href="/repo-status" class="nav-link">Repository Status</a>
      </div>
    </body>
    </html>
  `);
});

app.get('/commands', (req, res) => {
  try {
    const commandsData = fs.readFileSync('COMMANDS.md', 'utf8');
    const treeData = fs.readFileSync('TREE.md', 'utf8');
    const readmeData = fs.readFileSync('README.md', 'utf8');
    const claudeData = fs.readFileSync('CLAUDE.md', 'utf8');
    const configData = fs.readFileSync('config/config.yml', 'utf8');
    
    res.send(generateCommandsPage(commandsData, treeData, readmeData, claudeData, configData));
  } catch (error) {
    res.status(500).send('Error loading files: ' + error.message);
  }
});


app.get('/repo-status', (req, res) => {
  try {
    const data = JSON.parse(fs.readFileSync('repo-status.json', 'utf8'));
    res.send(generateHtmlForRepoStatus(data));
  } catch (error) {
    res.status(500).send('Error loading repo-status.json');
  }
});


app.get('/api/repo-status', (req, res) => {
  try {
    const data = JSON.parse(fs.readFileSync('repo-status.json', 'utf8'));
    res.json(data);
  } catch (error) {
    res.status(500).json({ error: 'Error loading repo-status.json' });
  }
});


function generateHtmlForRepoStatus(data) {
  // Extract VVV site name from repository path
  function extractVVVSite(path) {
    const match = path.match(/\/home\/jim\/Projects\/vagrant-local\/www\/([^\/]+)\//);
    return match ? match[1] : 'other';
  }
  
  // Group all repositories by VVV site
  const siteRepos = {};
  data.repositories.forEach(repo => {
    const site = extractVVVSite(repo.path);
    // Skip blacklisted sites
    if (WORDPRESS_SITE_BLACKLIST.includes(site)) {
      return;
    }
    if (!siteRepos[site]) {
      siteRepos[site] = [];
    }
    siteRepos[site].push(repo);
  });
  
  // Filter repositories to exclude blacklisted sites
  const filteredRepos = data.repositories.filter(repo => {
    const site = extractVVVSite(repo.path);
    return !WORDPRESS_SITE_BLACKLIST.includes(site);
  });
  
  const okRepos = filteredRepos.filter(repo => repo.status === 'OK');
  const warningRepos = filteredRepos.filter(repo => repo.status === 'WARNING');
  const untrackedRepos = warningRepos.filter(repo => repo.has_untracked_files || repo.has_uncommitted_changes);
  const behindRepos = warningRepos.filter(repo => !repo.has_untracked_files && !repo.has_uncommitted_changes);
  
  return `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Repository Status</title>
      <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .container { max-width: 1400px; margin: 0 auto; }
        .stats { background: #d4edda; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .stat-item { display: inline-block; margin: 0 15px; font-weight: bold; }
        .sites-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; }
        .site-card { border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; background: white; }
        .site-header { margin-bottom: 15px; }
        .site-name { font-size: 1.2em; font-weight: bold; color: #495057; }
        .site-url { color: #007bff; font-size: 0.9em; }
        .section-title { font-weight: bold; color: #6c757d; margin-bottom: 8px; border-bottom: 1px solid #dee2e6; padding-bottom: 4px; }
        .repo-item { padding: 8px 10px; margin: 4px 0; border-radius: 4px; }
        .repo-item.ok { background: #d4edda; border-left: 3px solid #28a745; }
        .repo-item.warning { background: #fff3cd; border-left: 3px solid #ffc107; }
        .repo-item.error { background: #f8d7da; border-left: 3px solid #dc3545; }
        .repo-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 3px; }
        .repo-name { font-family: monospace; font-size: 0.9em; font-weight: bold; color: #495057; }
        .branch-badge { padding: 2px 6px; border-radius: 12px; font-size: 0.75em; font-weight: bold; color: white; }
        .branch-default { background-color: #28a745; }
        .branch-non-default { background-color: #dc3545; }
        .repo-path { font-family: monospace; font-size: 0.8em; color: #6c757d; margin-bottom: 2px; }
        .repo-issues { font-size: 0.8em; color: #856404; margin-top: 2px; }
        .back-link { display: inline-block; margin-bottom: 15px; color: #007bff; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
        h1 { color: #333; }
        h2 { color: #495057; margin-top: 30px; }
      </style>
    </head>
    <body>
      <div class="container">
        <a href="/" class="back-link">← Back to Home</a>
        <h1>Repository Status</h1>
        
        <div class="stats">
          <div class="stat-item">Scan Date: ${new Date(data.scan_date).toLocaleString()}</div>
          <div class="stat-item">Total Sites: ${Object.keys(siteRepos).length}</div>
          <div class="stat-item">Total Repositories: ${filteredRepos.length}</div>
          <div class="stat-item">OK: ${okRepos.length}</div>
          <div class="stat-item">Issues: ${warningRepos.length}</div>
        </div>

        <div class="sites-grid">
          ${Object.keys(siteRepos).sort().map(siteName => {
            const repos = siteRepos[siteName];
            const okCount = repos.filter(r => r.status === 'OK').length;
            const warningCount = repos.filter(r => r.status === 'WARNING').length;
            
            return `
              <div class="site-card">
                <div class="site-header">
                  <div class="site-name">${siteName}</div>
                  <div class="site-url">${okCount} OK, ${warningCount} Issues</div>
                </div>
                
                ${repos.filter(r => r.has_untracked_files || r.has_uncommitted_changes).length > 0 ? `
                  <div class="section-title">Uncommitted Changes</div>
                  ${repos.filter(r => r.has_untracked_files || r.has_uncommitted_changes).map(repo => `
                    <div class="repo-item warning">
                      <div class="repo-header">
                        <span class="repo-name">${repo.path.split('/').pop()}</span>
                        <span class="branch-badge ${repo.current_branch === repo.default_branch ? 'branch-default' : 'branch-non-default'}">${repo.current_branch}</span>
                      </div>
                      <div class="repo-path">${removePathPrefix(repo.path)}</div>
                      <div class="repo-issues">
                        ${repo.has_uncommitted_changes ? '• Uncommitted changes ' : ''}
                        ${repo.has_untracked_files ? '• Untracked files ' : ''}
                        ${repo.behind_remote ? '• Behind remote ' : ''}
                        ${repo.ahead_remote ? '• Ahead of remote' : ''}
                      </div>
                    </div>
                  `).join('')}
                ` : ''}
                
                ${repos.filter(r => r.status === 'WARNING' && !r.has_untracked_files && !r.has_uncommitted_changes).length > 0 ? `
                  <div class="section-title">Behind Remote</div>
                  ${repos.filter(r => r.status === 'WARNING' && !r.has_untracked_files && !r.has_uncommitted_changes).map(repo => `
                    <div class="repo-item warning">
                      <div class="repo-header">
                        <span class="repo-name">${repo.path.split('/').pop()}</span>
                        <span class="branch-badge ${repo.current_branch === repo.default_branch ? 'branch-default' : 'branch-non-default'}">${repo.current_branch}</span>
                      </div>
                      <div class="repo-path">${removePathPrefix(repo.path)}</div>
                      <div class="repo-issues">
                        ${repo.behind_remote ? '• Behind remote ' : ''}
                        ${repo.ahead_remote ? '• Ahead of remote' : ''}
                      </div>
                    </div>
                  `).join('')}
                ` : ''}
                
                ${repos.filter(r => r.status === 'OK').length > 0 ? `
                  <div class="section-title">Clean Repositories</div>
                  ${repos.filter(r => r.status === 'OK').map(repo => `
                    <div class="repo-item ok">
                      <div class="repo-header">
                        <span class="repo-name">${repo.path.split('/').pop()}</span>
                        <span class="branch-badge ${repo.current_branch === repo.default_branch ? 'branch-default' : 'branch-non-default'}">${repo.current_branch}</span>
                      </div>
                      <div class="repo-path">${removePathPrefix(repo.path)}</div>
                    </div>
                  `).join('')}
                ` : ''}
                
                ${repos.length === 0 ? `
                  <div style="color: #6c757d; font-style: italic;">No repositories found</div>
                ` : ''}
              </div>
            `;
          }).join('')}
        </div>
      </div>
    </body>
    </html>
  `;
}

function generateCommandsPage(commandsData, treeData, readmeData, claudeData, configData) {
  // Extract VVV commands from CLAUDE.md
  const vvvCommands = extractVVVCommands(claudeData);
  
  // Try to load repository status data for more accurate repository information
  let repoStatusData = null;
  try {
    repoStatusData = JSON.parse(fs.readFileSync('repo-status.json', 'utf8'));
  } catch (error) {
    console.log('Could not load repo-status.json, using filesystem scan only');
  }
  
  // Extract VVV sites from config and match with navigation commands
  const vvvSites = extractVVVSites(configData, treeData, repoStatusData);
  const vvvSitesWithNavigation = matchSitesWithNavigation(vvvSites, commandsData)
    .sort((a, b) => a.name.localeCompare(b.name));
  
  // Generate random colors for non-master branches
  const branchColors = ['#ff6b6b', '#4ecdc4', '#45b7d1', '#ffa726', '#ab47bc', '#ef5350', '#26a69a', '#42a5f5'];
  
  return `
    <!DOCTYPE html>
    <html>
    <head>
      <title>VVV Commands & Repository Guide</title>
      <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .container { max-width: 1400px; margin: 0 auto; }
        .back-link { display: inline-block; margin-bottom: 15px; color: #007bff; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
        .section { margin-bottom: 30px; padding: 20px; border: 1px solid #dee2e6; border-radius: 5px; }
        .section h2 { color: #495057; margin-top: 0; }
        .command-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 15px; }
        .command-card { background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #007bff; }
        .command-card h3 { margin-top: 0; color: #495057; }
        .command { background: #2d3748; color: #e2e8f0; padding: 8px 12px; border-radius: 4px; font-family: monospace; margin: 5px 0; }
        .sites-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; }
        .site-card { border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; background: white; }
        .site-header { margin-bottom: 15px; }
        .site-name { font-size: 1.2em; font-weight: bold; color: #495057; }
        .site-url { color: #007bff; font-size: 0.9em; }
        .themes-section, .plugins-section { margin-bottom: 15px; }
        .section-title { font-weight: bold; color: #6c757d; margin-bottom: 8px; border-bottom: 1px solid #dee2e6; padding-bottom: 4px; }
        .repo-item { padding: 10px; margin: 6px 0; border-radius: 5px; background: #f8f9fa; border-left: 3px solid #dee2e6; }
        .repo-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; }
        .repo-name { font-family: monospace; font-size: 0.95em; font-weight: bold; color: #495057; }
        .repo-controls { display: flex; align-items: center; gap: 8px; }
        .branch-badge { padding: 3px 8px; border-radius: 12px; font-size: 0.8em; font-weight: bold; color: white; }
        .nav-button { background: #17a2b8; color: white; border: none; padding: 4px 8px; border-radius: 3px; font-size: 0.75em; cursor: pointer; }
        .nav-button:hover { background: #138496; }
        .nav-button:active { background: #0c5460; }
        .full-path { font-family: monospace; font-size: 0.8em; color: #6c757d; margin-top: 3px; word-break: break-all; }
        .nav-item { padding: 8px; margin: 4px 0; border-radius: 4px; background: #fff3cd; border-left: 3px solid #ffc107; }
        .nav-header { display: flex; justify-content: between; align-items: center; }
        .nav-path { font-family: monospace; font-size: 0.85em; color: #856404; flex-grow: 1; }
        .nav-copy { background: #ffc107; color: #212529; border: none; padding: 3px 6px; border-radius: 3px; font-size: 0.7em; cursor: pointer; margin-left: 8px; }
        .nav-copy:hover { background: #e0a800; }
        .branch-master { background-color: #28a745; }
        .branch-main { background-color: #28a745; }
        .folder-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 10px; }
        .folder-item { background: #fff3cd; padding: 10px; border-radius: 4px; border-left: 4px solid #ffc107; }
        .folder-path { font-family: monospace; font-size: 0.9em; color: #856404; }
        h1 { color: #333; }
        .stats { background: #d4edda; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .stat-item { display: inline-block; margin: 0 15px; font-weight: bold; }
        .status-badge { padding: 2px 6px; border-radius: 10px; font-size: 0.7em; font-weight: bold; color: white; }
        .status-badge.status-ok { background-color: #28a745; }
        .status-badge.status-warning { background-color: #ffc107; color: #212529; }
        .repo-item.status-ok { border-left-color: #28a745; }
        .repo-item.status-warning { border-left-color: #ffc107; }
        .repo-status { font-size: 0.8em; color: #856404; margin-top: 3px; }
      </style>
    </head>
    <body>
      <div class="container">
        <a href="/" class="back-link">← Back to Home</a>
        <h1>VVV Commands & Repository Guide</h1>
        
        <div class="stats">
          <div class="stat-item">Sites: ${vvvSitesWithNavigation.length}</div>
          <div class="stat-item">Total Themes: ${vvvSitesWithNavigation.reduce((acc, site) => acc + site.themes.length, 0)}</div>
          <div class="stat-item">Total Plugins: ${vvvSitesWithNavigation.reduce((acc, site) => acc + site.plugins.length, 0)}</div>
          <div class="stat-item">VVV Commands: ${vvvCommands.reduce((acc, cmd) => acc + cmd.commands.length, 0)}</div>
        </div>

        <div class="section">
          <h2>VVV Core Commands</h2>
          <div class="command-grid">
            ${vvvCommands.map(cmd => `
              <div class="command-card">
                <h3>${cmd.category}</h3>
                ${cmd.commands.map(c => `<div class="command">${c}</div>`).join('')}
              </div>
            `).join('')}
          </div>
        </div>

        <div class="section">
          <h2>WordPress Sites - Navigation & Repository Guide</h2>
          <div class="sites-grid">
            ${vvvSitesWithNavigation.map((site, siteIndex) => `
              <div class="site-card">
                <div class="site-header">
                  <div class="site-name">${site.name}</div>
                  <div class="site-url">${site.hosts && site.hosts.length > 0 ? site.hosts.join(', ') : `${site.name}.test`}</div>
                </div>
                
                <div class="themes-section">
                  <div class="section-title">Site Navigation</div>
                  ${site.navigation.map(navCmd => `
                    <div class="nav-item">
                      <div class="nav-header">
                        <span class="nav-path">${removePathPrefix(navCmd.replace('cd ', ''))}</span>
                        <button class="nav-copy" onclick="copyToClipboard('${navCmd.replace(/'/g, "\\'")}')">📁 Copy</button>
                      </div>
                    </div>
                  `).join('')}
                </div>
                
                ${site.themes.length > 0 ? `
                  <div class="themes-section">
                    <div class="section-title">Themes</div>
                    ${site.themes.map((theme, themeIndex) => `
                      <div class="repo-item ${theme.status ? (theme.status === 'OK' ? 'status-ok' : 'status-warning') : ''}">
                        <div class="repo-header">
                          <span class="repo-name">${theme.name}</span>
                          <div class="repo-controls">
                            ${theme.status ? `<span class="status-badge ${theme.status === 'OK' ? 'status-ok' : 'status-warning'}">${theme.status}</span>` : ''}
                            <span class="branch-badge ${getBranchClass(theme.branch)}" style="${!isMainBranch(theme.branch) ? `background-color: ${branchColors[(siteIndex * 10 + themeIndex) % branchColors.length]}` : ''}">${theme.branch}</span>
                            <button class="nav-button" onclick="copyToClipboard('${theme.navCommand.replace(/'/g, "\\'")}')">📁 Copy</button>
                          </div>
                        </div>
                        <div class="full-path">${theme.fullPath}</div>
                        ${theme.hasUncommittedChanges || theme.hasUntrackedFiles || theme.behindRemote || theme.aheadRemote ? `
                          <div class="repo-status">
                            ${theme.hasUncommittedChanges ? '• Uncommitted changes ' : ''}
                            ${theme.hasUntrackedFiles ? '• Untracked files ' : ''}
                            ${theme.behindRemote ? '• Behind remote ' : ''}
                            ${theme.aheadRemote ? '• Ahead of remote ' : ''}
                          </div>
                        ` : ''}
                      </div>
                    `).join('')}
                  </div>
                ` : ''}
                
                ${site.plugins.length > 0 ? `
                  <div class="plugins-section">
                    <div class="section-title">Plugins</div>
                    ${site.plugins.map((plugin, pluginIndex) => `
                      <div class="repo-item ${plugin.status ? (plugin.status === 'OK' ? 'status-ok' : 'status-warning') : ''}">
                        <div class="repo-header">
                          <span class="repo-name">${plugin.name}</span>
                          <div class="repo-controls">
                            ${plugin.status ? `<span class="status-badge ${plugin.status === 'OK' ? 'status-ok' : 'status-warning'}">${plugin.status}</span>` : ''}
                            <span class="branch-badge ${getBranchClass(plugin.branch)}" style="${!isMainBranch(plugin.branch) ? `background-color: ${branchColors[(siteIndex * 100 + pluginIndex) % branchColors.length]}` : ''}">${plugin.branch}</span>
                            <button class="nav-button" onclick="copyToClipboard('${plugin.navCommand.replace(/'/g, "\\'")}')">📁 Copy</button>
                          </div>
                        </div>
                        <div class="full-path">${plugin.fullPath}</div>
                        ${plugin.hasUncommittedChanges || plugin.hasUntrackedFiles || plugin.behindRemote || plugin.aheadRemote ? `
                          <div class="repo-status">
                            ${plugin.hasUncommittedChanges ? '• Uncommitted changes ' : ''}
                            ${plugin.hasUntrackedFiles ? '• Untracked files ' : ''}
                            ${plugin.behindRemote ? '• Behind remote ' : ''}
                            ${plugin.aheadRemote ? '• Ahead of remote ' : ''}
                          </div>
                        ` : ''}
                      </div>
                    `).join('')}
                  </div>
                ` : ''}
                
                ${site.themes.length === 0 && site.plugins.length === 0 ? `
                  <div style="color: #6c757d; font-style: italic;">No custom themes or plugins with repositories</div>
                ` : ''}
              </div>
            `).join('')}
          </div>
        </div>

      </div>
      <script>
        function copyToClipboard(text) {
          navigator.clipboard.writeText(text).then(function() {
            // Show temporary feedback
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '✅ Copied!';
            button.style.backgroundColor = '#28a745';
            setTimeout(() => {
              button.innerHTML = originalText;
              button.style.backgroundColor = '#17a2b8';
            }, 1500);
          }).catch(function(err) {
            console.error('Could not copy text: ', err);
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '✅ Copied!';
            setTimeout(() => {
              button.innerHTML = originalText;
            }, 1500);
          });
        }
      </script>
    </body>
    </html>
  `;
}

function getBranchClass(branch) {
  return isMainBranch(branch) ? `branch-${branch}` : '';
}

function isMainBranch(branch) {
  return branch === 'main' || branch === 'master';
}

function extractVVVSites(configData, treeData, repoStatusData = null) {
  const sites = [];
  const lines = configData.split('\n');
  let currentSite = null;
  let inSitesSection = false;
  let inHostsSection = false;
  let currentIndentLevel = 0;
  
  for (let i = 0; i < lines.length; i++) {
    const line = lines[i];
    const trimmedLine = line.trim();
    const indentLevel = line.length - line.trimStart().length;
    
    // Start of sites section
    if (trimmedLine === 'sites:') {
      inSitesSection = true;
      continue;
    }
    
    // End of sites section
    if (inSitesSection && trimmedLine.startsWith('extensions:')) {
      inSitesSection = false;
      break;
    }
    
    if (!inSitesSection) continue;
    
    // New site definition (e.g., "pegasus:")
    if (trimmedLine.match(/^[a-zA-Z0-9-]+:$/) && indentLevel <= 2) {
      if (currentSite) {
        sites.push(currentSite);
      }
      
      const siteName = trimmedLine.replace(':', '');
      currentSite = {
        name: siteName,
        hosts: [],
        themes: [],
        plugins: []
      };
      inHostsSection = false;
      currentIndentLevel = indentLevel;
      continue;
    }
    
    if (!currentSite) continue;
    
    // Check for hosts section
    if (trimmedLine === 'hosts:' && indentLevel > currentIndentLevel) {
      inHostsSection = true;
      continue;
    }
    
    // Parse hosts (they should be indented more than the hosts: line)
    if (inHostsSection && trimmedLine.startsWith('- ') && indentLevel > currentIndentLevel + 2) {
      const host = trimmedLine.replace(/^-\s*/, '').trim();
      if (host && !host.includes('#')) {
        currentSite.hosts.push(host);
      }
      continue;
    }
    
    // If we hit a non-host item at the same or lower indent level, we're out of hosts
    if (inHostsSection && indentLevel <= currentIndentLevel + 2 && trimmedLine && !trimmedLine.startsWith('- ')) {
      inHostsSection = false;
    }
  }
  
  // Don't forget the last site
  if (currentSite) {
    sites.push(currentSite);
  }
  
  // Now get themes and plugins - use repository status data if available, fallback to filesystem
  sites.forEach(site => {
    if (repoStatusData && repoStatusData.repositories) {
      // Use repository status data for more accurate information
      const siteThemes = getThemesAndPluginsFromRepoStatus(site.name, 'themes', repoStatusData);
      const sitePlugins = getThemesAndPluginsFromRepoStatus(site.name, 'plugins', repoStatusData);
      
      site.themes = siteThemes;
      site.plugins = sitePlugins;
    } else {
      // Fallback to filesystem scanning
      const siteThemes = getThemesAndPlugins(site.name, 'themes');
      const sitePlugins = getThemesAndPlugins(site.name, 'plugins');
      
      site.themes = siteThemes;
      site.plugins = sitePlugins;
    }
  });
  
  // Filter out blacklisted sites AND sites with no themes/plugins
  return sites.filter(site => 
    !WORDPRESS_SITE_BLACKLIST.includes(site.name) && 
    (site.themes.length > 0 || site.plugins.length > 0)
  );
}

function matchSitesWithNavigation(vvvSites, commandsData) {
  // Parse commands data to create a lookup table
  const navigationLookup = {};
  const lines = commandsData.split('\n');
  let currentSite = null;
  
  for (let i = 0; i < lines.length; i++) {
    const line = lines[i].trim();
    
    // Detect site headers (### sitename)
    if (line.startsWith('### ')) {
      currentSite = line.replace('### ', '').trim();
      if (!navigationLookup[currentSite]) {
        navigationLookup[currentSite] = { general: [], themes: {}, plugins: {} };
      }
    }
    
    // Extract cd commands
    const cdMatch = line.match(/cd ([^\n#]+)/);
    if (cdMatch && currentSite && cdMatch[1].includes('/www/')) {
      const fullCommand = cdMatch[1].trim();
      const fullPath = `cd ${fullCommand}`;
      
      if (fullCommand.includes('/themes/')) {
        // Extract theme name from path
        const themeMatch = fullCommand.match(/\/themes\/([^\/]+)/);
        if (themeMatch) {
          const themeName = themeMatch[1];
          navigationLookup[currentSite].themes[themeName] = fullPath;
        }
      } else if (fullCommand.includes('/plugins/')) {
        // Extract plugin name from path
        const pluginMatch = fullCommand.match(/\/plugins\/([^\/]+)/);
        if (pluginMatch) {
          const pluginName = pluginMatch[1];
          navigationLookup[currentSite].plugins[pluginName] = fullPath;
        }
      } else {
        // General site navigation (root, wp-content, etc.)
        navigationLookup[currentSite].general.push(fullPath);
      }
    }
  }
  
  // Now match VVV sites with navigation commands
  return vvvSites.map(site => {
    const siteNav = navigationLookup[site.name] || { general: [], themes: {}, plugins: {} };
    
    // Add navigation commands to themes
    const themesWithNav = site.themes.map(theme => ({
      ...theme,
      navCommand: siteNav.themes[theme.name] || generateNavCommand(site.name, 'themes', theme.name),
      fullPath: removePathPrefix(siteNav.themes[theme.name] ? siteNav.themes[theme.name].replace('cd ', '') : `/home/jim/Projects/vagrant-local/www/${site.name}/public_html/wp-content/themes/${theme.name}/`)
    }));
    
    // Add navigation commands to plugins
    const pluginsWithNav = site.plugins.map(plugin => ({
      ...plugin,
      navCommand: siteNav.plugins[plugin.name] || generateNavCommand(site.name, 'plugins', plugin.name),
      fullPath: removePathPrefix(siteNav.plugins[plugin.name] ? siteNav.plugins[plugin.name].replace('cd ', '') : `/home/jim/Projects/vagrant-local/www/${site.name}/public_html/wp-content/plugins/${plugin.name}/`)
    }));
    
    // Add general navigation commands
    const generalNavigation = siteNav.general.length > 0 ? siteNav.general : [
      `cd /home/jim/Projects/vagrant-local/www/${site.name}/`,
      `cd /home/jim/Projects/vagrant-local/www/${site.name}/public_html/`,
      `cd /home/jim/Projects/vagrant-local/www/${site.name}/public_html/wp-content/`
    ];
    
    return {
      ...site,
      themes: themesWithNav,
      plugins: pluginsWithNav,
      navigation: generalNavigation
    };
  });
}

function generateNavCommand(siteName, type, itemName) {
  // Generate fallback navigation command if not found in COMMANDS.md
  return `cd /home/jim/Projects/vagrant-local/www/${siteName}/public_html/wp-content/${type}/${itemName}/`;
}

function removePathPrefix(path) {
  // Remove the common prefix from paths for cleaner display
  const prefix = '/home/jim/Projects/vagrant-local/www';
  return path.startsWith(prefix) ? path.replace(prefix, '') : path;
}

function getThemesAndPluginsFromRepoStatus(siteName, type, repoStatusData) {
  const items = [];
  const pathPattern = `/home/jim/Projects/vagrant-local/www/${siteName}/public_html/wp-content/${type}/`;
  
  // Filter repositories that match the site and type
  const matchingRepos = repoStatusData.repositories.filter(repo => 
    repo.path.startsWith(pathPattern)
  );
  
  matchingRepos.forEach(repo => {
    const name = repo.path.replace(pathPattern, '');
    // Only include if it's a direct child directory (no subdirectories)
    if (!name.includes('/')) {
      const fullPath = repo.path.replace('/home/jim/Projects/vagrant-local/www', '');
      const navCommand = `cd ${repo.path}`;
      
      items.push({
        name: name,
        branch: repo.current_branch || 'unknown',
        path: repo.path,
        fullPath: fullPath,
        navCommand: navCommand,
        status: repo.status,
        hasUncommittedChanges: repo.has_uncommitted_changes,
        hasUntrackedFiles: repo.has_untracked_files,
        behindRemote: repo.behind_remote,
        aheadRemote: repo.ahead_remote,
        remoteUrl: repo.remote_url
      });
    }
  });
  
  return items;
}

function getThemesAndPlugins(siteName, type) {
  const items = [];
  const basePath = `www/${siteName}/public_html/wp-content/${type}`;
  
  try {
    if (!fs.existsSync(basePath)) {
      return items;
    }
    
    const dirs = fs.readdirSync(basePath, { withFileTypes: true })
      .filter(dirent => dirent.isDirectory())
      .map(dirent => dirent.name);
    
    dirs.forEach(dirName => {
      const dirPath = `${basePath}/${dirName}`;
      const gitPath = `${dirPath}/.git`;
      
      // Check if it's a git repository and has pegasus in the name or is a custom repo
      if (fs.existsSync(gitPath) && (dirName.includes('pegasus') || dirName.includes('octane') || isCustomRepo(dirPath))) {
        const branch = getCurrentBranch(dirPath);
        const fullPath = dirPath.replace('/home/jim/Projects/vagrant-local/www', '');
        const navCommand = `cd /home/jim/Projects/vagrant-local/${dirPath}`;
        
        items.push({
          name: dirName,
          branch: branch || 'unknown',
          path: `/home/jim/Projects/vagrant-local/${dirPath}`,
          fullPath: fullPath,
          navCommand: navCommand
        });
      }
    });
  } catch (error) {
    console.log(`Error reading ${type} for ${siteName}:`, error.message);
  }
  
  return items;
}

function isCustomRepo(dirPath) {
  try {
    const gitConfigPath = `${dirPath}/.git/config`;
    if (fs.existsSync(gitConfigPath)) {
      const gitConfig = fs.readFileSync(gitConfigPath, 'utf8');
      return gitConfig.includes('github.com') && !gitConfig.includes('wordpress.org');
    }
  } catch (error) {
    // Ignore errors
  }
  return false;
}

function getCurrentBranch(dirPath) {
  try {
    const result = execSync('git branch --show-current', { 
      cwd: dirPath, 
      encoding: 'utf8',
      timeout: 5000
    }).trim();
    return result || 'main';
  } catch (error) {
    // Try alternative method
    try {
      const headPath = `${dirPath}/.git/HEAD`;
      if (fs.existsSync(headPath)) {
        const headContent = fs.readFileSync(headPath, 'utf8').trim();
        if (headContent.startsWith('ref: refs/heads/')) {
          return headContent.replace('ref: refs/heads/', '');
        }
      }
    } catch (e) {
      // Ignore
    }
    return 'main';
  }
}

function extractVVVCommands(claudeData) {
  const commands = [
    {
      category: "Vagrant Operations",
      commands: [
        "vagrant up",
        "vagrant up --provision",
        "vagrant provision",
        "vagrant halt",
        "vagrant reload",
        "vagrant destroy",
        "vagrant ssh"
      ]
    },
    {
      category: "Plugin Management",
      commands: [
        "vagrant plugin install --local"
      ]
    },
    {
      category: "Database Operations",
      commands: [
        'vagrant ssh -c "db_backup"',
        'vagrant ssh -c "db_restore"'
      ]
    }
  ];
  
  return commands;
}

function extractWordPressFolders(commandsData) {
  const siteFolders = {};
  const folderRegex = /cd ([^\n#]+)/g;
  
  let match;
  let currentSite = null;
  
  // Parse the commands data line by line to track current site context
  const lines = commandsData.split('\n');
  
  for (let i = 0; i < lines.length; i++) {
    const line = lines[i].trim();
    
    // Detect site headers (### sitename)
    if (line.startsWith('### ')) {
      currentSite = line.replace('### ', '').trim();
      if (!siteFolders[currentSite]) {
        siteFolders[currentSite] = {
          name: currentSite,
          general: [],
          themes: [],
          plugins: []
        };
      }
    }
    
    // Extract cd commands
    const cdMatch = line.match(/cd ([^\n#]+)/);
    if (cdMatch && currentSite && cdMatch[1].includes('/www/')) {
      const folder = cdMatch[1].trim();
      
      // Categorize the folder path
      if (folder.includes('/themes/')) {
        // Extract just the theme-specific part
        const themePath = folder.replace(/.*\/themes\//, '').split('/')[0];
        const fullCommand = `cd ${folder}`;
        if (!siteFolders[currentSite].themes.find(t => t.includes(themePath))) {
          siteFolders[currentSite].themes.push(fullCommand);
        }
      } else if (folder.includes('/plugins/')) {
        // Extract just the plugin-specific part
        const pluginPath = folder.replace(/.*\/plugins\//, '').split('/')[0];
        const fullCommand = `cd ${folder}`;
        if (!siteFolders[currentSite].plugins.find(p => p.includes(pluginPath))) {
          siteFolders[currentSite].plugins.push(fullCommand);
        }
      } else {
        // General site folders (root, wp-content, etc.)
        const fullCommand = `cd ${folder}`;
        if (!siteFolders[currentSite].general.includes(fullCommand)) {
          siteFolders[currentSite].general.push(fullCommand);
        }
      }
    }
  }
  
  // Convert to array, filter blacklisted sites, and sort
  return Object.values(siteFolders)
    .filter(site => !WORDPRESS_SITE_BLACKLIST.includes(site.name))
    .filter(site => site.general.length > 0 || site.themes.length > 0 || site.plugins.length > 0)
    .sort((a, b) => a.name.localeCompare(b.name));
}

const server = app.listen(PORT, () => {
  console.log(`Server running at http://localhost:${PORT}`);
  
  // Initialize BrowserSync
  browserSync.init({
    proxy: `localhost:${PORT}`,
    port: 3001,
    open: true,
    notify: false,
    ui: false,
    logLevel: "silent",
    injectChanges: true,
    reloadDelay: 0,
    ignore: [
      'www/**',
      'node_modules/**',
      '.git/**',
      'log/**',
      'database/**',
      'certificates/**',
      'provision/**'
    ]
  });
  
  // BrowserSync will automatically refresh when nodemon restarts the server
  
  console.log(`BrowserSync running at http://localhost:3001`);
  console.log('Watching files for changes...');
});