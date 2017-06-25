# Git Auto-Deploy Script
This is a lite script to automatically pull changes from the remote repository. The action is triggered via a GitHub webhook.

Getting Started
===

## Prerequisites
This script needs the following requirements in order to run correctly.

- Establish SSH key access to the selected git repository
- Generate secret key for webhook (example: `ruby -rsecurerandom -e 'puts SecureRandom.hex(20)'`)
- URL to call the web script (e.g. website.com/_deploy_)
