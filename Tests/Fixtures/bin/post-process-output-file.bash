#!/bin/bash

source "`cd $(dirname ${BASH_SOURCE[0]}) && pwd`/post-process-common.bash"

function main()
{
  local arguments=("${@}")
  local inputFile=""
  local outputFile=""

  # In avifenc: avifenc [options] input output
  # So the last one is output, the one before is input.

  local count=${#arguments[@]}
  outputFile="${arguments[$((count-1))]}"
  inputFile="${arguments[$((count-2))]}"

  local info=$(writeScriptInformation "${inputFile}" "${arguments[@]}")

  # Write the info to the output file
  echo "$info" > "$outputFile"

  writeScriptDebugFile <<< "$info"
}

main "${@}"
