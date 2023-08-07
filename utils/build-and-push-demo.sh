#!/bin/bash
###############################
# Tiago França | https://tiagofranca.com
# 2023-07-05
#
###############################

__DIR__() {
    echo $(dirname $(readlink -f "$0"))
}

export ACTIONS=${ACTIONS:-$@}

export LAMBDA_HANDLER_FUNCTION=php-app/lambdaRunnerFile.handler
export LAMBDA_ENTRYPOINT=
export LAMBDA_NAME=funcaoImport
export LOCAL_IMAGE_NAME=comptrade/import-export-dev
export AWS_IMAGE_NAME=comptrade-import-export-dev
export ACCOUNT_ID=029618464094
export AWS_PROFILE_NAME=tiagodev
export AWS_DEFAULT_REGION=us-east-1
export AWS_CLI_ECR_LOGIN_OPTIONS=
export AWS_CLI_LAMBDA_UPDATE_OPTIONS=

bash "$(__DIR__)/build-and-push-base.sh"
