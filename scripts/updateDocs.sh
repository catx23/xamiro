#!/bin/sh
git checkout gh_pages
cd docs/daux

php generate.php global.json ../../