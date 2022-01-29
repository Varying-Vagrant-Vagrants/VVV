#!/usr/bin/env bash

# TODO: Attempt vvv_info and vvv_error replacements for macOS
source provision/provision-helpers.sh

function testDomain() {
	DOMAIN=$1
	STATUS=$(curl -o /dev/null -s -w "%{http_code}\n" "http://${1}")
	if [[ "200" != "${STATUS}" ]]; then
		echo -e "${RED} ! ${DOMAIN} is unavailable${CRESET}"
		exit 1;
	else
		echo -e "${GREEN} ✔ ${DOMAIN} is accessible${CRESET}"
	fi
}

testDomain "vvv.test"
testDomain "one.wordpress.test"
testDomain "two.wordpress.test"
