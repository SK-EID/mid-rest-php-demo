#!/bin/bash
#
# Container entrypoint: create the database tables, then start Apache in the foreground.
set -e

/makeAppTables.sh

exec apache2-foreground
