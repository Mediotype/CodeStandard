pipeline {
  agent any
  stages {
    stage('Build') {
      steps {
        stash(name: 'build-artifact', includes: 'src/Examples')
      }
    }
    stage('Test') {
      agent {
        docker {
          args '--mount type=bind,source=/opt/jenkins-host-storage,target=/opt/jenkins-host-storage'
          image 'devilbox/php-fpm:7.1-work'
        }

      }
      steps {
        unstash 'build-artifact'
        sh '''mkdir _testCS

chmod 0600 $GITHUB_SSH_KEY
GIT_SSH_COMMAND="ssh -i $GITHUB_SSH_KEY -o StrictHostKeyChecking=no" \\
    git clone git@github.com:Mediotype/CodeStandard.git _testCS

cd _testCS
composer install

php vendor/bin/phpcs \\
    --no-colors \\
    --standard=src/Rules/Structure/PHP \\
    --report=code \\
    --ignore=*_testCS/*
    ..'''
      }
    }
  }
  environment {
    SLACK_CHANNEL = '#test-webhook'
    COMPOSER_AUTH = '{"http-basic":{"repo.magento.com":{"username":"7be9dd180a9910520ab95cab36eafb0f","password":"f7a1df7121c40bbdbb332dfabcd3afd9"}}}'
    GITHUB_SSH_KEY = '/opt/jenkins-host-storage/github-mediotype-main.pem'
  }
}