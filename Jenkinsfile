pipeline {
  agent any
  stages {
    stage('Build') {
      environment {
        BUILD_TARGET = 'src/Examples'
      }
      steps {
        sh 'tar -czf artifact.tar.gz $BUILD_TARGET'
        archiveArtifacts(onlyIfSuccessful: true, artifacts: 'artifact.tar.gz')
        stash 'archive.tar.gz'
        sh 'ls -larth'
      }
    }
    stage('Test') {
      agent {
        docker {
          args '--mount type=bind,source=/opt/jenkins-host-storage,target=/opt/jenkins-host-storage'
          image 'devilbox/php-fpm:5.6-work'
        }

      }
      steps {
        unstash 'archive.tar.gz'
        sh '''mkdir build test
tar -xzf artifact.tar.gz -C build

chmod 0600 $GITHUB_SSH_KEY
GIT_SSH_COMMAND="ssh -i $GITHUB_SSH_KEY -o StrictHostKeyChecking=no" \\
    git clone git@github.com:Mediotype/CodeStandard.git test

cd test
composer install

php vendor/bin/phpcs \\
    --no-colors \\
    --standard=src/Rules/Structure/PHP \\
    --report=code \\
    ../build/'''
      }
    }
  }
  environment {
    SLACK_CHANNEL = '#test-webhook'
    COMPOSER_AUTH = '{"http-basic":{"repo.magento.com":{"username":"7be9dd180a9910520ab95cab36eafb0f","password":"f7a1df7121c40bbdbb332dfabcd3afd9"}}}'
    GITHUB_SSH_KEY = '/opt/jenkins-host-storage/github-mediotype-main.pem'
  }
}