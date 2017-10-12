#!/bin/bash

# exit if any statement returns a non-true value
set -e

# determine path to this script
readonly SELF_DIR_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/"

# source variables and functions files
source "${SELF_DIR_PATH}inc-variables.bash"
source "${SELF_DIR_PATH}inc-functions.bash"

#
# perform different operations depending on the environment
#
function main()
{
  out_main "Running 'exec-before' operations"

  initialize_prestissimo
}

# go!
main
