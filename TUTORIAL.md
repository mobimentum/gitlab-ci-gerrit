`# HOWTO Setup Gitlab CI with Gerrit

## Setup GitLab CI

Create a `gerrit` user and give it "Maintainer" accesso to a group.

## Setup Gerrit

### Install Gerrit

Download and install Gerrit from https://www.gerritcodereview.com/, see Linux instructions [here](https://gerrit-documentation.storage.googleapis.com/Documentation/2.16.7/linux-quickstart.html).

Configure the database and create an instance as described [here](https://gerrit-documentation.storage.googleapis.com/Documentation/2.16.7/install.html).

Create a `gerrit` system user, assign all instance files to that user and run Gerrit as that user:

```
chown -R gerrit:gerrit gerrit-instance
```

### Configure Gerrit to mirror changes to GitLab

Install the [replication plugin](https://gerrit.googlesource.com/plugins/replication/+doc/master/src/main/resources/Documentation/config.md) then add the following configuration to `etc/replication.config`:

```
[remote "gitlab"]
	url = git@gitlab.example.com:groupname/${name}.git 
	push = +refs/heads/*:refs/heads/*
	push = +refs/tags/*:refs/tags/*
	push = +refs/changes/*:refs/heads/review/*
	replicationDelay = 0
	createMissingRepositories = true 
```
(customize `gitlab.example.com` and `groupname` with a group name your gerrit user has access to).

## Gerrit -> GitLab auth

Create an ssh-key pair in the home of the `gerrit` system user and copy the public key in the GitLab `gerrit` user profile.

## GitLab -> Gerrit auth

Create a user in the Gerrit database and assign it to the `Non-Interactive Users` group, using the ssh-key of `gitlab-runner` user for authentication:

```
cat ~gitlab-runner/.ssh/id_rsa.pub | ssh -p 29418 localhost 'gerrit create-account --group "Administrators" --ssh-key - gitlab'
ssh -p 29418 localhost 'gerrit set-account --add-email gitlab@example.com gitlab'
ssh -p 29418 localhost 'gerrit set-account --full-name GitLab gitlab'
```

Assign the new user to the `Non-Interactive Users` group via Gerrit UI and check the result:

```
ssh -p 29418 localhost 'gerrit ls-members "Non-Interactive Users"'
```
