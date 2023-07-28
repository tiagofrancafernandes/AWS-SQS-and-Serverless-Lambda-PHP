FROM public.ecr.aws/tiago/lambda-laravel-aws-php-base:latest
# FROM public.ecr.aws/v1x3t5p5/lambda-laravel-aws-php-base:latest

ARG LAMBDA_HANDLER_FUNCTION='handler.helloWorld'
ENV LAMBDA_HANDLER_FUNCTION=${LAMBDA_HANDLER_FUNCTION:-handler.helloWorld}

RUN echo "LAMBDA_HANDLER_FUNCTION: ${LAMBDA_HANDLER_FUNCTION}"

## Instale as dependências necessárias
RUN yum update -y && \
    yum install -y php-pdo php-zip bash zsh nano

###### PECL DEPENDENCIES #######
## Se desejar instalar libs pecl, descomente as linhas abaixo
# RUN pecl channel-update pecl.php.net
# RUN pecl update-channels

# RUN yes 'no'|pecl install redis
# RUN yes|pecl install memcache
# RUN pecl install xdebug
# RUN pecl install mongodb

# COPY ./.docker-data/php/custom-options.ini /etc/php.d/20-custom-options.ini

RUN curl 'https://getcomposer.org/download/latest-2.2.x/composer.phar' -o /usr/bin/composer
RUN chmod +x /usr/bin/composer

## Se desejar atualizar o runtime
# COPY ./runtime /var/runtime

# RUN chmod +x /var/runtime/bootstrap

## Copie o código da função Lambda para o diretório de trabalho
COPY . /var/task

################################################
### Configurando o handler da função Lambda
# No docker-compose, essa linha vai em 'command:'
# Se nada for informado no 'command', usara o valor definido aqui
#
# TIP: Pode-se usar a seção do Lambda para informar qual função executar
# https://[REGION].console.aws.amazon.com/lambda/home?region=[REGION]#/functions/[FUNCTION NAME]?tab=image
# Ex:
# https://us-east-1.console.aws.amazon.com/lambda/home?region=us-east-1#/functions/SQSTestePHP8?tab=image
#
# Explicando: php-app/lambdaRunnerFile.handler
# php-app/ -> Pasta onde está o arquivo alvo
# lambdaRunnerFile -> arquivo alvo 'lambdaRunnerFile.php'
# handler -> função definida dentro do arquivo alvo

# CMD [ "php-app/lambdaRunnerFile.handler" ]
CMD [ "handler.helloWorld" ]
################################################
