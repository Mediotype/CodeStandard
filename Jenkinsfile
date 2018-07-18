pipeline {
  agent any
  stages {
    stage('Build') {
      agent {
        docker {
          image 'devilbox/php-fpm:5.6-work'
          args '--mount type=bind,source=/opt/jenkins-host-storage,target=/opt/jenkins-host-storage'
        }

      }
      steps {
        sh '''ls -larth /opt/jenkins-host-storage/
GIT_SSH_COMMAND="ssh -i $GITHUB_SSH_KEY" \\
    git clone git@github.com:Mediotype/CodeStandard.git'''
      }
    }
  }
  environment {
    SLACK_CHANNEL = '#test-webhook'
    COMPOSER_AUTH = '{"http-basic":{"repo.magento.com":{"username":"7be9dd180a9910520ab95cab36eafb0f","password":"f7a1df7121c40bbdbb332dfabcd3afd9"}}}'
    GITHUB_SSH_KEY = '/opt/jenkins-host-storage/github-mediotype-main.pem'
  }
}