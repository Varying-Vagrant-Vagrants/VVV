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
├── wordpress-one/
│   └── public_html/
│       └── wp-content/
│           ├── themes/
│           │   ├── twentytwentyfive/
│           │   ├── twentytwentyfour/
│           │   └── twentytwentythree/
│           └── plugins/
│               ├── akismet/
│               └── hello.php
│
├── wordpress-two/
│   └── public_html/
│       └── wp-content/
│           ├── themes/
│           │   ├── twentytwentyfive/
│           │   ├── twentytwentyfour/
│           │   └── twentytwentythree/
│           └── plugins/
│               ├── akismet/
│               └── hello.php
│
├── pegasus/ ⭐
│   └── public_html/
│       └── wp-content/
│           ├── themes/
│           │   ├── pegasus/ 🔗🎯 [git@github.com:Visionquest-Development/pegasus.git]
│           │   └── pegasus-child/ 🔗🎯 [git@github.com:Visionquest-Development/pegasus-child.git]
│           └── plugins/
│               ├── pegasus-blog/ 🔗🎯 [Visionquest-Development/pegasus-blog]
│               ├── pegasus-callout/ 🔗🎯 [Visionquest-Development/pegasus-callout]
│               ├── pegasus-carousel/ 🔗🎯 [Visionquest-Development/pegasus-carousel]
│               ├── pegasus-circle-progress/ 🔗🎯 [Visionquest-Development/pegasus-circle-progress]
│               ├── pegasus-countup/ 🔗🎯 [Visionquest-Development/pegasus-countup]
│               ├── pegasus-masonry/ 🔗🎯 [Visionquest-Development/pegasus-masonry]
│               ├── pegasus-navmenu/ 🔗🎯 [Visionquest-Development/pegasus-navmenu]
│               ├── pegasus-onepage/ 🔗🎯 [Visionquest-Development/pegasus-onepage]
│               ├── pegasus-packery/ 🔗🎯 [Visionquest-Development/pegasus-packery]
│               ├── pegasus-popup/ 🔗🎯 [Visionquest-Development/pegasus-popup]
│               ├── pegasus-postgrid/ 🔗🎯 [Visionquest-Development/pegasus-postgrid]
│               ├── pegasus-posts-filter/ 🔗🎯 [Visionquest-Development/pegasus-posts-filter]
│               ├── pegasus-slider/ 🔗🎯 [Visionquest-Development/pegasus-slider]
│               ├── pegasus-tabs/ 🔗🎯 [Visionquest-Development/pegasus-tabs]
│               ├── pegasus-toggleslide/ 🔗🎯 [Visionquest-Development/pegasus-toggleslide]
│               └── pegasus-wow/ 🔗🎯 [Visionquest-Development/pegasus-wow]
│
├── pegasustwo/ ⭐
│   └── public_html/
│       └── wp-content/
│           ├── themes/
│           │   └── pegasus/ 🔗🎯 [git@github.com:Visionquest-Development/pegasus.git]
│           └── plugins/
│               ├── akismet/
│               └── hello.php
│
├── qbiqcamp/ ⭐
│   └── public_html/
│       └── wp-content/
│           ├── themes/
│           │   ├── pegasus/ 🔗🎯 [Visionquest-Development/pegasus]
│           │   └── pegasus-child/ 🔗🎯 [Visionquest-Development/pegasus-child]
│           └── plugins/
│               ├── pegasus-carousel/ 📁🎯 [Local folder - no GitHub repo]
│               ├── pegasus-onepage/ 📁🎯 [Local folder - no GitHub repo]
│               ├── pegasus-packery/ 📁🎯 [Local folder - no GitHub repo]
│               └── wp-bootstrap-hooks/ 🔗 [git@github.com:benignware/wp-bootstrap-hooks.git]
│
├── pegasustheme/ ⭐
│   └── public_html/
│       └── wp-content/
│           ├── themes/
│           │   ├── pegasus/ 🔗🎯 [Visionquest-Development/pegasus]
│           │   └── pegasus-child/ 🔗🎯 [Visionquest-Development/pegasus-child]
│           └── plugins/
│               ├── octane-booster/ 🔗 [git@github.com:OctaneAgency/octane-booster.git]
│               ├── octane-slider/ 🔗 [git@github.com:OctaneAgency/octane-slider.git]
│               ├── pegasus-blog/ 🔗🎯 [Visionquest-Development/pegasus-blog]
│               ├── pegasus-callout/ 🔗🎯 [Visionquest-Development/pegasus-callout]
│               ├── pegasus-carousel/ 🔗🎯 [Visionquest-Development/pegasus-carousel]
│               ├── pegasus-circle-progress/ 🔗🎯 [Visionquest-Development/pegasus-circle-progress]
│               ├── pegasus-countup/ 🔗🎯 [Visionquest-Development/pegasus-countup]
│               ├── pegasus-masonry/ 🔗🎯 [Visionquest-Development/pegasus-masonry]
│               ├── pegasus-navmenu/ 🔗🎯 [Visionquest-Development/pegasus-navmenu]
│               ├── pegasus-onepage/ 🔗🎯 [Visionquest-Development/pegasus-onepage]
│               ├── pegasus-packery/ 🔗🎯 [Visionquest-Development/pegasus-packery]
│               ├── pegasus-popup/ 🔗🎯 [Visionquest-Development/pegasus-popup]
│               ├── pegasus-post-grid/ 🔗🎯 [git@github.com:Visionquest-Development/pegasus-postgrid.git]
│               ├── pegasus-posts-filter/ 🔗🎯 [Visionquest-Development/pegasus-posts-filter]
│               ├── pegasus-slider/ 🔗🎯 [Visionquest-Development/pegasus-slider]
│               ├── pegasus-tabs/ 🔗🎯 [Visionquest-Development/pegasus-tabs]
│               ├── pegasus-toggleslide/ 🔗🎯 [Visionquest-Development/pegasus-toggleslide]
│               └── pegasus-wow/ 🔗🎯 [Visionquest-Development/pegasus-wow]
│
├── cadence-group/ ⭐
│   └── public_html/
│       └── wp-content/
│           ├── themes/
│           │   ├── pegasus/ 🔗🎯 [git@github.com:Visionquest-Development/pegasus.git]
│           │   └── pegasus-child/ 🔗🎯 [Visionquest-Development/pegasus-child]
│           └── plugins/
│               ├── pegasus-accordion/ 🔗🎯 [Visionquest-Development/pegasus-accordion]
│               ├── pegasus-carousel/ 🔗🎯 [git@github.com:Visionquest-Development/pegasus-carousel.git]
│               ├── pegasus-navmenu/ 🔗🎯 [Visionquest-Development/pegasus-navmenu]
│               ├── pegasus-slider/ 🔗🎯 [git@github.com:Visionquest-Development/pegasus-slider.git]
│               └── pegasus-tabs/ 🔗🎯 [Visionquest-Development/pegasus-tabs]
│
├── ourpalsplace/ ⭐
│   └── public_html/
│       └── wp-content/
│           ├── themes/
│           │   ├── pegasus/ 🔗🎯 [Visionquest-Development/pegasus]
│           │   └── pegasus-child/ 🔗🎯 [Visionquest-Development/pegasus-child]
│           └── plugins/
│               ├── pegasus-accordion/ 🔗🎯 [Visionquest-Development/pegasus-accordion]
│               ├── pegasus-carousel/ 🔗🎯 [git@github.com:Visionquest-Development/pegasus-carousel.git]
│               ├── pegasus-popup/ 🔗🎯 [git@github.com:Visionquest-Development/pegasus-popup.git]
│               ├── pegasus-slider/ 🔗🎯 [git@github.com:Visionquest-Development/pegasus-slider.git]
│               ├── pegasus-toggleslide/ 🔗🎯 [git@github.com:Visionquest-Development/pegasus-toggleslide.git]
│               └── wp-bootstrap-hooks/ 🔗 [git@github.com:benignware/wp-bootstrap-hooks.git]
│
├── ourpalsplacedbt/ ⭐
│   └── public_html/
│       └── wp-content/
│           ├── themes/
│           │   ├── pegasus/ 🔗🎯 [Visionquest-Development/pegasus]
│           │   └── pegasus-child/ 🔗🎯 [Visionquest-Development/pegasus-child]
│           └── plugins/
│               ├── akismet/
│               └── hello.php
│
├── theloft/ ⭐
│   └── public_html/
│       └── wp-content/
│           ├── themes/
│           │   ├── pegasus/ 🔗🎯 [Visionquest-Development/pegasus]
│           │   └── pegasus-child/ 🔗🎯 [Visionquest-Development/pegasus-child]
│           └── plugins/
│               ├── akismet/
│               └── hello.php
│
└── outlawcoffe/ ⭐
    └── public_html/
        └── wp-content/
            ├── themes/
            │   ├── pegasus/ 📁🎯 [Local folder - no GitHub repo]
            │   └── pegasus-child/ 📁🎯 [Local folder - no GitHub repo]
            └── plugins/
                ├── pegasus-slider/ 📁🎯 [Local folder - no GitHub repo]
                ├── akismet/
                └── hello.php
```

---

## Summary Statistics

### **Sites with pegasus-* content:** 9 sites
- pegasus (18 repositories)
- pegasustwo (1 repository)
- qbiqcamp (2 repositories + 3 local folders)
- pegasustheme (20 repositories)
- cadence-group (7 repositories)
- ourpalsplace (7 repositories)
- ourpalsplacedbt (2 repositories)
- theloft (2 repositories)
- outlawcoffe (0 repositories + 3 local folders)

### **Total pegasus-* items found:** 71 items
- **GitHub repositories:** 65 repositories
- **Local folders only:** 6 folders

### **GitHub Organizations:**
- **Visionquest-Development:** 63 repositories (pegasus theme + plugins)
- **OctaneAgency:** 2 repositories (octane-booster, octane-slider)
- **benignware:** 1 repository (wp-bootstrap-hooks)

### **Most common pegasus-* plugins:**
- pegasus (theme) - 9 instances
- pegasus-child (theme) - 9 instances
- pegasus-carousel - 5 instances
- pegasus-slider - 5 instances
- pegasus-navmenu - 4 instances
- pegasus-tabs - 4 instances

### **Sites not found:**
- sagecnc (does not exist)
- vineandvision (does not exist)
- wordpress-trunk (does not exist)

### **Repository URLs:**
All Visionquest-Development repositories follow the pattern:
- `git@github.com:Visionquest-Development/[plugin-name].git`
- Main theme: `git@github.com:Visionquest-Development/pegasus.git`