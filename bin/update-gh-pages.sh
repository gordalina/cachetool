#!/bin/bash

set -ex

if [ $# -ne 1 ]; then
  echo "Usage: `basename $0` <tag>"
  exit 65
fi

TAG=$1
BRANCH=$(git rev-parse --abbrev-ref HEAD)

#
# Remove dependencies and re-install production composer dependencies
#

rm -fr vendor
composer install --no-dev

#
# Copy Readme
#
SCRATCH=$(mktemp -t tmp.XXXXXXXXXX)
cat README.md > $SCRATCH

#
# Copy executable file into GH pages
#
git checkout gh-pages

# Add index
cat $SCRATCH > index.md
git add index.md

# Add release
cp -f cachetool.phar downloads/cachetool-${TAG}.phar
cp -f cachetool.phar downloads/cachetool.phar
git add downloads/cachetool-${TAG}.phar
git add downloads/cachetool.phar

if [ "$(uname)" == "Darwin" ]; then
    SHA1=$(shasum cachetool.phar | awk '{print $1}')
elif [ "$(expr substr $(uname -s) 1 5)" == "Linux" ]; then
    SHA1=$(openssl sha1 -r cachetool.phar | awk '{print $1}')
fi

set +e
read -r -d '' JSON <<EOF
{
  "name": "cachetool.phar",
  "sha1": "${SHA1}",
  "url": "https://github.com/gordalina/cachetool/releases/download/cachetool-${TAG}.phar",
  "version": "${TAG}"
}
EOF
set -e

#
# Update manifest
#
cat manifest.json | jq -Mr ". |= . + [${JSON}]" > manifest.json.tmp
mv manifest.json.tmp manifest.json
git add manifest.json

git commit -m "Bump version ${TAG}"

#
# Go back to main 
#
git checkout main

git push origin gh-pages

echo "New version created: ${TAG}."
