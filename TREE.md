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
├── cadence-group/ ⭐
│   └── public_html/
│       └── wp-content/
│           ├── themes/
│           │   ├── pegasus/ 🔗🎯 [Visionquest-Development/pegasus]
│           │   ├── pegasus-child/ 🔗🎯 [Visionquest-Development/pegasus-child]
│           │   ├── twentytwentyfive/
│           │   ├── twentytwentyfour/
│           │   ├── twentytwentythree/
│           └── plugins/
│               ├── akismet/
│               ├── better-search-replace/
│               ├── better-wp-security/
│               ├── chatbot/
│               ├── cmb2-conditionals/
│               ├── CMB2_RGBa_Picker-master/
│               ├── duplicate-page/
│               ├── duplicate-post/
│               ├── google-site-kit/
│               ├── gravityforms/
│               ├── header-and-footer-scripts/
│               └── hello.php
│               └── index.php
│               ├── insert-headers-and-footers/
│               ├── js_composer/
│               ├── mainwp-child/
│               ├── page-links-to/
│               ├── page-list/
│               ├── pegasus-accordion/ 📁🎯 [Local folder - no GitHub repo]
│               ├── pegasus-carousel/ 🔗🎯 [Visionquest-Development/pegasus-carousel]
│               ├── pegasus-navmenu/ 📁🎯 [Local folder - no GitHub repo]
│               ├── pegasus-slider/ 🔗🎯 [Visionquest-Development/pegasus-slider]
│               ├── pegasus-tabs/ 📁🎯 [Local folder - no GitHub repo]
│               ├── sendwp/
│               ├── sitemap/
│               ├── siteorigin-panels/
│               ├── so-widgets-bundle/
│               ├── ultimate-social-media-icons/
│               ├── updraftplus/
│               ├── wordpress-seo/
│               ├── worker/
│               ├── wp-bootstrap-hooks/
│               ├── wp-cerber/
│               ├── wp-google-maps/
│               ├── wp-to-buffer/
│
├── default/
│
├── ourpalsplace/ ⭐
│   └── public_html/
│       └── wp-content/
│           ├── themes/
│           │   ├── pegasus/ 🔗🎯 [Visionquest-Development/pegasus]
│           │   ├── pegasus-child/ 🔗🎯 [Visionquest-Development/pegasus-child]
│           │   ├── twentytwentyfive/
│           │   ├── twentytwentyfour/
│           │   ├── twentytwentythree/
│           └── plugins/
│               ├── akismet/
│               ├── black-studio-tinymce-widget/
│               ├── classic-editor/
│               ├── cmb2-conditionals/
│               ├── CMB2_RGBa_Picker-master/
│               ├── custom-facebook-feed/
│               ├── gravityforms/
│               ├── gravityformsstripe/
│               └── hello.php
│               ├── iframe/
│               └── index.php
│               ├── instagram-feed/
│               ├── jetpack/
│               ├── pegasus-accordion/ 📁🎯 [Local folder - no GitHub repo]
│               ├── pegasus-carousel/ 🔗🎯 [Visionquest-Development/pegasus-carousel]
│               ├── pegasus-popup/ 🔗🎯 [Visionquest-Development/pegasus-popup]
│               ├── pegasus-slider/ 🔗🎯 [Visionquest-Development/pegasus-slider]
│               ├── pegasus-toggleslide/ 🔗🎯 [Visionquest-Development/pegasus-toggleslide]
│               ├── siteorigin-panels/
│               ├── so-widgets-bundle/
│               ├── user-registration/
│               ├── woocommerce/
│               ├── woocommerce-advanced-bulk-edit/
│               ├── woocommerce-gateway-stripe/
│               ├── woocommerce-gravityforms-product-addons/
│               ├── woocommerce-payments/
│               ├── woocommerce-paypal-payments/
│               ├── woocommerce-shortcodes/
│               ├── woo-custom-emails-per-product/
│               ├── wp-bootstrap-hooks/ 🔗 [benignware/wp-bootstrap-hooks]
│               ├── wpfront-user-role-editor-personal-pro/
│               ├── wp-mail-smtp/
│
├── ourpalsplacedbt/ ⭐
│   └── public_html/
│       └── wp-content/
│           ├── themes/
│           │   ├── pegasus/ 🔗🎯 [Visionquest-Development/pegasus]
│           │   ├── pegasus-child/ 🔗🎯 [Visionquest-Development/pegasus-child]
│           │   ├── twentytwentyfive/
│           │   ├── twentytwentyfour/
│           │   ├── twentytwentythree/
│           └── plugins/
│               ├── akismet/
│               ├── classic-editor/
│               ├── gravityforms/
│               └── hello.php
│               ├── iframe/
│               └── index.php
│               ├── jetpack/
│               ├── siteorigin-panels/
│               ├── so-widgets-bundle/
│               ├── user-registration/
│               ├── wp-mail-smtp/
│
├── outlawcoffe/ ⭐
│   └── public_html/
│       └── wp-content/
│           ├── themes/
│           │   ├── pegasus/ 📁🎯 [Local folder - no GitHub repo]
│           │   ├── pegasus-child/ 📁🎯 [Local folder - no GitHub repo]
│           │   ├── twentytwentyfive/
│           └── plugins/
│               ├── akismet/
│               ├── all-in-one-wp-migration/
│               ├── all-in-one-wp-migration-unlimited-extension/
│               ├── flexible-shipping-ups-labels/
│               ├── flexible-shipping-ups-pro/
│               ├── header-and-footer-scripts/
│               └── hello.php
│               ├── id-services/
│               └── index.php
│               ├── mass-email-to-users/
│               ├── pegasus-slider/ 📁🎯 [Local folder - no GitHub repo]
│               ├── points-and-rewards-for-woocommerce/
│               ├── printify-for-woocommerce/
│               ├── siteorigin-panels/
│               ├── so-widgets-bundle/
│               ├── theme-my-login/
│               ├── wc-gsheetconnector/
│               ├── wc-gsheetconnector-pro/
│               ├── woocommerce/
│               ├── woocommerce-catalog-visibility-options/
│               ├── woocommerce-square/
│               ├── woo-update-manager/
│               ├── wpforms/
│               ├── wpforms-form-abandonment/
│               ├── wpforms-geolocation/
│               ├── wpforms-user-journey/
│               ├── wpforms-user-registration/
│               ├── wp-mail-smtp/
│               ├── yith-woocommerce-product-add-ons/
│
├── pegasus/ ⭐
│   └── public_html/
│       └── wp-content/
│           ├── themes/
│           │   ├── pegasus/ 🔗🎯 [Visionquest-Development/pegasus]
│           │   ├── pegasus-child/ 🔗🎯 [Visionquest-Development/pegasus-child]
│           │   ├── twentytwentyfive/
│           │   ├── twentytwentyfour/
│           │   ├── twentytwentythree/
│           │   ├── twentytwentytwo/
│           └── plugins/
│               ├── akismet/
│               ├── breadcrumb-navxt/
│               ├── gravityforms/
│               └── hello.php
│               └── index.php
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
│               ├── pegasus-wow/ 🔗🎯 [Visionquest-Development/pegasus-wow]
│               ├── siteorigin-panels/
│               ├── so-widgets-bundle/
│               ├── woocommerce/
│               ├── wordpress-seo/
│
├── pegasustheme/ ⭐
│   └── public_html/
│       └── wp-content/
│           ├── themes/
│           │   ├── divi-child/
│           │   ├── lilith/
│           │   ├── lilith-child/
│           │   ├── old.Divi/
│           │   ├── pegasus/ 🔗🎯 [Visionquest-Development/pegasus]
│           │   ├── pegasus-child/ 🔗🎯 [Visionquest-Development/pegasus-child]
│           │   ├── twentytwentyfive/
│           │   ├── twentytwentyfour/
│           │   ├── twentytwentythree/
│           └── plugins/
│               ├── akismet/
│               ├── classic-editor/
│               └── code-markup.php
│               ├── code-snippet-library/
│               ├── code-snippets/
│               ├── elegant-themes-updater/
│               └── error_log
│               ├── gravityforms/
│               └── hello.php
│               └── index.php
│               ├── mainwp-child/
│               ├── octane-booster/ 🔗 [OctaneAgency/octane-booster]
│               ├── octane-slider/ 🔗 [OctaneAgency/octane-slider]
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
│               ├── pegasus-post-grid/ 🔗🎯 [Visionquest-Development/pegasus-postgrid]
│               ├── pegasus-posts-filter/ 🔗🎯 [Visionquest-Development/pegasus-posts-filter]
│               ├── pegasus-slider/ 🔗🎯 [Visionquest-Development/pegasus-slider]
│               ├── pegasus-tabs/ 🔗🎯 [Visionquest-Development/pegasus-tabs]
│               ├── pegasus-toggleslide/ 🔗🎯 [Visionquest-Development/pegasus-toggleslide]
│               ├── pegasus-wow/ 🔗🎯 [Visionquest-Development/pegasus-wow]
│               ├── siteorigin-panels/
│               ├── so-widgets-bundle/
│               ├── syntaxhighlighter/
│               ├── updraftplus/
│               ├── wordpress-seo/
│
├── pegasustwo/ ⭐
│   └── public_html/
│       └── wp-content/
│           ├── themes/
│           │   ├── pegasus/ 🔗🎯 [Visionquest-Development/pegasus]
│           │   ├── twentytwentyfive/
│           │   ├── twentytwentyfour/
│           │   ├── twentytwentythree/
│           │   ├── twentytwentytwo/
│           └── plugins/
│               ├── akismet/
│               └── hello.php
│               └── index.php
│
├── phpcs/
│
├── qbiqcamp/ ⭐
│   └── public_html/
│       └── wp-content/
│           ├── themes/
│           │   ├── pegasus/ 🔗🎯 [Visionquest-Development/pegasus]
│           │   ├── pegasus-child/ 🔗🎯 [Visionquest-Development/pegasus-child]
│           │   ├── twentytwentyfive/
│           │   ├── twentytwentyfour/
│           │   ├── twentytwentythree/
│           └── plugins/
│               ├── akismet/
│               ├── gravityforms/
│               ├── gravityformsstripe/
│               └── hello.php
│               └── index.php
│               ├── jetpack/
│               ├── pegasus-carousel/ 📁🎯 [Local folder - no GitHub repo]
│               ├── pegasus-onepage/ 📁🎯 [Local folder - no GitHub repo]
│               ├── pegasus-packery/ 📁🎯 [Local folder - no GitHub repo]
│               ├── siteorigin-panels/
│               ├── so-widgets-bundle/
│               ├── wordpress-importer/
│               ├── wordpress-seo/
│               ├── wordpress-seo-premium/
│               ├── wordpress-starter/
│               ├── wp-bootstrap-hooks/ 🔗 [benignware/wp-bootstrap-hooks]
│               ├── wpforms-lite/
│
├── theloft/ ⭐
│   └── public_html/
│       └── wp-content/
│           ├── themes/
│           │   ├── bootstrap-wp/
│           │   ├── CoffeeShop/
│           │   ├── pegasus/ 🔗🎯 [Visionquest-Development/pegasus]
│           │   ├── pegasus-child/ 🔗🎯 [Visionquest-Development/pegasus-child]
│           │   ├── twentytwentyfive/
│           └── plugins/
│               ├── akismet/
│               ├── events-calendar-pro/
│               ├── event-tickets/
│               ├── event-tickets-plus/
│               ├── feed-them-premium/
│               ├── feed-them-social/
│               ├── food-and-drink-menu/
│               ├── google-sitemap-plugin/
│               ├── header-and-footer-scripts/
│               └── hello.php
│               └── index.php
│               ├── siteorigin-panels/
│               ├── so-widgets-bundle/
│               ├── transients-manager/
│               ├── ultimate-coming-soon-page/
│               ├── woocommerce/
│               ├── woocommerce-authorize-net-reporting/
│               ├── woocommerce-custom-order-data/
│               ├── woocommerce-exporter/
│               ├── woocommerce-points-and-rewards/
│               ├── wordpress-importer/
│               ├── wp-cost-estimation-payment-forms-builder/
│               ├── wp-mail-smtp/
│
├── wordpress-one/
│   └── public_html/
│       └── wp-content/
│           ├── themes/
│           │   ├── twentytwentyfive/
│           │   ├── twentytwentyfour/
│           │   ├── twentytwentythree/
│           └── plugins/
│               ├── akismet/
│               └── hello.php
│               └── index.php
│
├── wordpress-two/
│   └── public_html/
│       └── wp-content/
│           ├── themes/
│           │   ├── twentytwentyfive/
│           │   ├── twentytwentyfour/
│           │   ├── twentytwentythree/
│           └── plugins/
│               └── index.php
│               ├── query-monitor/
│
```

---

## Summary Statistics

### **Sites with pegasus-* content:** 9 sites
- cadence-group (4 repositories + 3 local folders)
- ourpalsplace (6 repositories + 1 local folders)
- ourpalsplacedbt (2 repositories)
- outlawcoffe (0 repositories + 3 local folders)
- pegasus (18 repositories)
- pegasustheme (18 repositories)
- pegasustwo (1 repositories)
- qbiqcamp (2 repositories + 3 local folders)
- theloft (2 repositories)

### **Total pegasus-* items found:** 63 items
- **GitHub repositories:** 53 repositories
- **Local folders only:** 10 folders

### **GitHub Organizations:**
- **OctaneAgency:** 2 repositories
- **benignware:** 2 repositories
- **Visionquest-Development:** 53 repositories

### **Repository URLs:**
All Visionquest-Development repositories follow the pattern:
- `git@github.com:Visionquest-Development/[plugin-name].git`
- Main theme: `git@github.com:Visionquest-Development/pegasus.git`
