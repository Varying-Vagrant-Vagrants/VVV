#!/bin/bash

# Postfix
#
# Use debconf-set-selections to specify the selections in the postfix setup. Set
# up as an 'Internet Site' with the host name 'vvv'. Note that if your current
# Internet connection does not allow communication over port 25, you will not be
# able to send mail, even with postfix installed.
echo postfix postfix/main_mailer_type select Internet Site | debconf-set-selections
echo postfix postfix/mailname string vvv | debconf-set-selections