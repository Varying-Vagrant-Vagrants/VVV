#!/bin/bash

. "/srv/provision/provisioners.sh"

provisioner_init
pre_hook
main
dashboard
utility_sources
provision_utilities
sites
post_hook
