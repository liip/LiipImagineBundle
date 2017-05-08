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
# disable the php memory limit by setting it to "-1"
#
function disable_php_memory_limit()
{
  if [[ ! -d "$(dirname ${CONF_PATH_PHP7})" ]]; then
    return
  fi

  out_line "Disabling PHP memory limit in '${CONF_PATH_PHP7}'"
  echo "memory_limit = -1" >> "${CONF_PATH_PHP7}";
}

#
# enable the hhvm php7 mode by setting it to "1"
#
function enable_hhvm_php7_mode()
{
  out_line "Enabling HHVM PHP7 mode in '${CONF_PATH_HHVM}'"
  echo "hhvm.php7.all = 1" >> "${CONF_PATH_HHVM}"
  echo "hhvm.php7.scalar_types = 0" >> "${CONF_PATH_HHVM}"
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
