#!/bin/bash
###############################
# Tiago França | https://tiagofranca.com
# 2023-07-05
###############################
# TODO:
# Var environments on config
# aws lambda update-function-configuration --function-name my-function --environment Variables={MY_VAR=value}

FILE_PATH=$(readlink -f "${0}")
FILE_DIR=$(dirname "${FILE_PATH}")
BASE_DIR=$(realpath "${FILE_DIR}/../")

# echo "FILE_PATH: ${FILE_PATH}";
# echo "FILE_DIR: ${FILE_DIR}";
echo ""
echo "BASE_DIR: ${BASE_DIR}"
echo ""

cd "${BASE_DIR}"

#####################################################################
## ACTIONS="all" bash ./script.sh
## ACTIONS="tag login" bash ./script.sh
## bash ./script.sh tag login

## build login push tag
ACTIONS=${ACTIONS:-$@}

# if [[ "$ACTIONS" =~ (^| )build($| ) ]] || [[ "$ACTIONS" =~ (^| )all($| ) ]]; then
#   echo "Building..."
#   # build
# fi

if [[ -z $ACTIONS ]]; then
    echo -e "no ACTIONS\n"
    echo -e "Valid ACTIONS values:"
    echo -e "[build login build tag push deploy config all quiet]"
    echo -e "
    build ........... Make build of docker image
    login ........... Login on AWS ECR
    tag ............. Create Docker and ECR tag
    push ............ Push Docker image to AWS ECR
    deploy .......... Make deploy on AWS Lambda function
    config .......... Set lambda configuration [EntryPoint, Command, WorkingDirectory]
    all ............. Run all actions
    quiet ........... Run action quietly
"
    exit 9
fi
#####################################################################

QUIET=0
if [[ "$ACTIONS" =~ (^| )quiet($| ) ]]; then
    QUIET=1
fi

DATE=$(date +%y%m%d%H%M%S)

export LAMBDA_HANDLER_FUNCTION=${LAMBDA_HANDLER_FUNCTION:-php-app/lambdaRunnerFile.handler}
export LAMBDA_ENTRYPOINT=${LAMBDA_ENTRYPOINT}
export PHP_VERSION=${PHP_VERSION:-8.1}
export LAMBDA_NAME=${LAMBDA_NAME}
export LOCAL_IMAGE_NAME=${LOCAL_IMAGE_NAME}
export AWS_IMAGE_NAME=${AWS_IMAGE_NAME}
export ACCOUNT_ID=${ACCOUNT_ID}
export DATE_IMAGE_TAG="${PHP_VERSION}.${DATE}"
export LOCAL_IMAGE_TAG=latest
export AWS_IMAGE_TAG=${AWS_IMAGE_TAG:-${LOCAL_IMAGE_TAG}}
export AWS_CLI_ECR_LOGIN_OPTIONS=${AWS_CLI_ECR_LOGIN_OPTIONS}
export AWS_CLI_LAMBDA_UPDATE_OPTIONS=${AWS_CLI_LAMBDA_UPDATE_OPTIONS}
export AWS_CLI_LAMBDA_UPDATE_CONFIG_OPTIONS=${AWS_CLI_LAMBDA_UPDATE_CONFIG_OPTIONS}
export AWS_PROFILE_NAME=${AWS_PROFILE_NAME}
export AWS_DEFAULT_REGION=${AWS_DEFAULT_REGION}

__DIR__() {
    echo $(dirname $(readlink -f "$0"))
}

get_aws_profile_name() {
    if [ -z $AWS_PROFILE_NAME ]; then
        echo ""
    else
        echo " --profile ${AWS_PROFILE_NAME}"
    fi
}

get_aws_default_region() {
    if [ -z $AWS_DEFAULT_REGION ]; then
        echo ""
    else
        echo " --region ${AWS_DEFAULT_REGION}"
    fi
}

wait_function_updated() {
    echo -e ""
    echo -e "Waiting until function is updated..."
    echo -e ""

    aws lambda wait function-updated ${AWS_CLI_LAMBDA_UPDATE_CONFIG_OPTIONS} --function-name ${LAMBDA_NAME}
}

AWS_CLI_ECR_LOGIN_OPTIONS="${AWS_CLI_ECR_LOGIN_OPTIONS}$(get_aws_profile_name)$(get_aws_default_region)"
AWS_CLI_LAMBDA_UPDATE_OPTIONS="${AWS_CLI_LAMBDA_UPDATE_OPTIONS}$(get_aws_profile_name)$(get_aws_default_region)"
AWS_CLI_LAMBDA_UPDATE_CONFIG_OPTIONS="${AWS_CLI_LAMBDA_UPDATE_CONFIG_OPTIONS}$(get_aws_profile_name)$(get_aws_default_region)"

if [[ "$ACTIONS" =~ (^| )build($| ) ]] || [[ "$ACTIONS" =~ (^| )all($| ) ]]; then
    AWS_IMAGE_TAG="${AWS_IMAGE_TAG:-${DATE_IMAGE_TAG}}"
else
    AWS_IMAGE_TAG="${AWS_IMAGE_TAG:-latest}"
fi

LOCAL_IMAGE_TAG=${LOCAL_IMAGE_TAG:-latest}

if [ -z $LAMBDA_NAME ]; then
    echo -e "LAMBDA_NAME env is required\n"
    exit 9
fi
echo -e "LAMBDA_NAME: ${LAMBDA_NAME}"

if [ -z $AWS_IMAGE_NAME ]; then
    echo -e "AWS_IMAGE_NAME env is required\n"
    exit 9
fi
echo -e "AWS_IMAGE_NAME: ${AWS_IMAGE_NAME}"

if [ -z $ACCOUNT_ID ]; then
    echo -e "ACCOUNT_ID env is required\n"
    exit 9
fi
echo -e "ACCOUNT_ID: ${ACCOUNT_ID}"

if [ -z $AWS_IMAGE_TAG ]; then
    echo -e "AWS_IMAGE_TAG env is required\n"
    exit 5
fi
echo -e "AWS_IMAGE_TAG: ${AWS_IMAGE_TAG}"

if [ -z $LOCAL_IMAGE_TAG ]; then
    echo -e "LOCAL_IMAGE_TAG env is required\n"
    exit 5
fi
echo -e "LOCAL_IMAGE_TAG: ${LOCAL_IMAGE_TAG}"

#### Define build values
AWS_ECR_URI="${ACCOUNT_ID}.dkr.ecr.us-east-1.amazonaws.com"
AWS_ECR_IMAGE_URI="${AWS_ECR_URI}/${AWS_IMAGE_NAME}"
DOCKER_TAG=${DOCKER_TAG:-latest}
#### END Define build values

## login
if [[ "$ACTIONS" =~ (^| )login($| ) ]] || [[ "$ACTIONS" =~ (^| )all($| ) ]]; then
    echo -e "Login on ECR Docker registry"

    if [ $QUIET -eq 1 ]; then
        aws ecr get-login-password ${AWS_CLI_ECR_LOGIN_OPTIONS} | docker login --username AWS --password-stdin ${AWS_ECR_URI} >/dev/null 2>&1 || echo ''
    else
        aws ecr get-login-password ${AWS_CLI_ECR_LOGIN_OPTIONS} | docker login --username AWS --password-stdin ${AWS_ECR_URI}

        if [ $? -ne 0 ]; then
            echo -e "Fail to login on AWS ECR"
            exit 6
        fi
    fi
fi

## build
if [[ "$ACTIONS" =~ (^| )build($| ) ]] || [[ "$ACTIONS" =~ (^| )all($| ) ]]; then
    echo -e "Building"
    docker build --build-arg "LAMBDA_HANDLER_FUNCTION=${LAMBDA_HANDLER_FUNCTION}" -t "${LOCAL_IMAGE_NAME}:${LOCAL_IMAGE_TAG}" .

    if [ $? -ne 0 ]; then
        echo -e "Fail to build Docker image"
        exit 5
    fi
fi

## tag
if [[ "$ACTIONS" =~ (^| )tag($| ) ]] || [[ "$ACTIONS" =~ (^| )all($| ) ]]; then
    echo -e "Tagging"
    docker tag ${LOCAL_IMAGE_NAME}:${LOCAL_IMAGE_TAG} ${AWS_ECR_IMAGE_URI}:${AWS_IMAGE_TAG}
fi

## push
if [[ "$ACTIONS" =~ (^| )push($| ) ]] || [[ "$ACTIONS" =~ (^| )all($| ) ]]; then
    echo -e "Pushing"
    docker push ${AWS_ECR_IMAGE_URI}:${AWS_IMAGE_TAG}
fi

## deploy
if [[ "$ACTIONS" =~ (^| )deploy($| ) ]] || [[ "$ACTIONS" =~ (^| )all($| ) ]]; then
    echo -e "Deploying lambda"
    # aws lambda update-function-code --function-name ${LAMBDA_NAME} --image-uri "${AWS_ECR_IMAGE_URI}" --region=us-east-1 | jq  ## js is a JSON viewer

    if [ $QUIET -eq 1 ]; then
        aws lambda update-function-code ${AWS_CLI_LAMBDA_UPDATE_OPTIONS} --function-name ${LAMBDA_NAME} --image-uri "${AWS_ECR_IMAGE_URI}:${AWS_IMAGE_TAG}" >/dev/null 2>&1 || echo ''
    else
        aws lambda update-function-code ${AWS_CLI_LAMBDA_UPDATE_OPTIONS} --function-name ${LAMBDA_NAME} --image-uri "${AWS_ECR_IMAGE_URI}:${AWS_IMAGE_TAG}" | jq
    fi
fi

## Waiting if has deployment
if [[ "$ACTIONS" =~ (^| )deploy($| ) ]] || [[ "$ACTIONS" =~ (^| )all($| ) ]]; then
    wait_function_updated
fi

## config
if [[ "$ACTIONS" =~ (^| )config($| ) ]] || [[ "$ACTIONS" =~ (^| )all($| ) ]]; then
    wait_function_updated

    get_lambda_configuration() {
        local _LAMBDA_CONFIGURATION=""

        if [ -n "${LAMBDA_HANDLER_FUNCTION}" ]; then
            _LAMBDA_CONFIGURATION="\"Command\":[\"${LAMBDA_HANDLER_FUNCTION}\"]"
        fi

        if [ -n "${LAMBDA_ENTRYPOINT}" ]; then
            if [ -n $_LAMBDA_CONFIGURATION ]; then
                _LAMBDA_CONFIGURATION="${_LAMBDA_CONFIGURATION},\"EntryPoint\":[\"${LAMBDA_ENTRYPOINT}\"]"
            else
                _LAMBDA_CONFIGURATION="\"EntryPoint\":[\"${LAMBDA_ENTRYPOINT}\"]"
            fi
        fi

        if [ -n "${_LAMBDA_CONFIGURATION}" ]; then
            echo ${_LAMBDA_CONFIGURATION}
        fi
    }

    get_lambda_environments() {
        ### All variable of 'LAMBDA_ENVIRONMENTS' must be finished with , (comma)
        ### Setting a LAMBDA_ENVIRONMENTS variable:
        # LAMBDA_ENVIRONMENTS="
        #    VAR_1=value1,
        #    VAR_2=value2,
        #    VAR_3=${SOME_VAR},
        #    VAR_4=value4,
        # "

        local _LAMBDA_ENVIRONMENTS="${LAMBDA_ENVIRONMENTS}"
        local CURRENT_DATE_ISO_TIME=$(date +"%Y-%m-%dT%H:%M:%S%z")

        if [ -n "$LAMBDA_ENVIRONMENTS" ]; then
            _LAMBDA_ENVIRONMENTS="
                ${LAMBDA_ENVIRONMENTS}
                ENV_CLI_UPDATED_AT=${CURRENT_DATE_ISO_TIME},
            "

            echo ${_LAMBDA_ENVIRONMENTS}
        else
            echo ""
        fi
    }

    export LAMBDA_CONFIGURATION=$(get_lambda_configuration)
    export LAMBDA_ENVIRONMENTS=$(get_lambda_environments)

    if [ -n "$LAMBDA_CONFIGURATION" ]; then
        if [ -n "$LAMBDA_ENVIRONMENTS" ]; then
            aws lambda update-function-configuration ${AWS_CLI_LAMBDA_UPDATE_CONFIG_OPTIONS} --function-name ${LAMBDA_NAME} --image-config "{
                ${LAMBDA_CONFIGURATION}
            }" --environment Variables="{
                ${LAMBDA_ENVIRONMENTS}
            }"
        else
            aws lambda update-function-configuration ${AWS_CLI_LAMBDA_UPDATE_CONFIG_OPTIONS} --function-name ${LAMBDA_NAME} --image-config "{
                ${LAMBDA_CONFIGURATION}
            }"
        fi
    fi
fi

# docker build -t base-aws-php .  #latest
# docker tag ${LOCAL_IMAGE_NAME}:latest ${AWS_ECR_IMAGE_URI}:latest
# docker push ${AWS_ECR_IMAGE_URI}:latest

ACTIONS="${ACTIONS/quiet/}"
notify-send "End of actions: ${ACTIONS}" -u low -t 500 >/dev/null 2>&1 || echo 'Finished'
