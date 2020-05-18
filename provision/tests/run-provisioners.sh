#!/bin/bash

. "/srv/provision/tests/provisioners.sh"

pre_hook
provision_main
provision_dashboard
provision_utility_sources
provision_utilities
provision_sites
post_hook
