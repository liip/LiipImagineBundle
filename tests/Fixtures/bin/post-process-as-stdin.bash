#!/bin/bash

source "`cd $(dirname ${BASH_SOURCE[0]}) && pwd`/post-process-common.bash"

function main()
{
  local arguments=("${@}")
  local inputFile=""

  for a in "${arguments[@]}"; do
    inputFile="${a}"
  done

  if [[ ! -f "${inputFile}" ]]; then
    inputFile="stdin"
  fi

  writeScriptDebugFile <<< $(writeScriptInformation "stdin" "${arguments[@]}")
}

main "${@}"
