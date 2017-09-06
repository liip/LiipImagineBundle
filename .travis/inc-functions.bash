#!/bin/bash

#
# output text to console
#
function out_line()
{
  printf "[%d] %s...\n" $(date +%s) "${1}"
}

#
# output main text to console
#
function out_main()
{
  out_line "${1^^}"
}

#
# configure the prestissimo package
#
function configure_prestissimo()
{
  local composer_reqs="${1}"
  local composer_orig="${TRAVIS_BUILD_DIR}/.travis/composer/config.json"
  local composer_dest="${HOME}/.composer/config.json"

  out_line "Configuring '${composer_reqs}' in '${composer_dest}'"
  if [[ ! -d "$(dirname ${composer_dest})" ]]; then
    mkdir "$(dirname ${composer_dest})"
  fi
  cp "${composer_orig}" "${composer_dest}"
}

#
# require the prestissimo package using composer
#
function require_prestissimo()
{
  local composer_reqs="${1}"

  out_line "Requiring '${composer_reqs}'"
  composer global require "${composer_reqs}"
}

#
# initialize the prestissimo package
#
function initialize_prestissimo()
{
  local composer_reqs="hirak/prestissimo:^0.3"

  configure_prestissimo "${composer_reqs}"
  require_prestissimo "${composer_reqs}"
}

#
# send coveralls statistics using the required binary
#
function send_coveralls_statistics()
{
  local coveralls_bin="${TRAVIS_BUILD_DIR}/bin/coveralls"

  out_line "Sending Coveralls coverage using '${coveralls_bin}'"
  ${coveralls_bin} -vvv
}
