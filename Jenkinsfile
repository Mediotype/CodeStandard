pipeline {
  agent any
  stages {
    stage('Build') {
      agent {
        docker {
          image 'devilbox/php-fpm:5.6-work'
        }

      }
      steps {
        sh '''php -v
git clone git@github.com:Mediotype/CodeStandard.git
pwd
ls -larth'''
      }
    }
  }
  environment {
    SLACK_CHANNEL = '#test-webhook'
    COMPOSER_AUTH = '{"http-basic":{"repo.magento.com":{"username":"7be9dd180a9910520ab95cab36eafb0f","password":"f7a1df7121c40bbdbb332dfabcd3afd9"}}}'
  }
}