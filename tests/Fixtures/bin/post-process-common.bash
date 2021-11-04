#!/bin/bash

function writeScriptInformation()
{
  local inputFile="${1}"
  shift
  local arguments=("${@}")
  local iteration=1

  printf 'argument-size:%d\nargument-list:' ${#arguments[@]}
  printf '%s ' "${arguments[@]}"
  printf '\n'

  for a in "${arguments[@]}"; do
    printf 'argument-%04d:%s\n' ${iteration} "${a}"
    iteration=$((${iteration}+1))
  done

  if [[ ! -f "${inputFile}" ]] && [[ "${inputFile}" != "stdin" ]]; then
    printf 'input-type:file\ninput-file:\ncmd-status:error\n'
  elif [[ "${inputFile}" == "stdin" ]]; then
    printf 'input-type:stdin\ninput-file:%s\ncmd-status:success\nfiledumped:\n' "${inputFile}"

    if [[ "${inputFile}" == "stdin" ]]; then
      if read -t 0; then
        cat
      else
        echo "$*"
      fi
    fi
  else
    printf 'input-type:file\ninput-file:%s\ncmd-status:success\nfiledumped:\n' "${inputFile}"
    cat "${inputFile}"
  fi
}

function writeScriptDebugFile()
{
  local debugFile="/tmp/post-process-fixture-bin.log"

  if read -t 0; then
    cat | tee "${debugFile}"
  else
    echo "$*" | tee "${debugFile}"
  fi
}

function exitAsFailed()
{
  exit 255
}
