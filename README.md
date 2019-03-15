# GitlLab CI Utils

This repository contains a set of scripts, configurations and templates for integrating Gerrit with GitLab CI.

## CI templates

The `gitlab-ci/build-templates` folder contains gitlab-ci files that can be used to build software developed with various technologies:

- android-gradle.yml: Android apps compiled with Gradle.

### How to use them

Pick the template you need, copy it to the root of the project and rename it to `.gitlab-ci.yml`. Finally, customize it reading the instructions contained in the template itself (look for `XXX` markers).

## Gerrit hooks for GitLab

Gerrit hooks are used to react to Gerrit events and communicate with GitLab to achieve a full CI workflow:

- patchset-created: sends new patchsets to GitLab and waits for the "verified" response
- comment-added: typing "recheck" as comment will trigger a new build

### How to use them

First of all, copy the `gitlab.config.example` to you Gerrit's "etc" directory, rename it to `gitlab.config` and customize it. Then, copy all the files to the `hooks` directory (create it if not already there) and restart your Gerrit installation.

System requirements:
- php-cli
- php-curl

Needed Gerrit plugins:

- [replication](https://gerrit.googlesource.com/plugins/replication/)
- [hooks](https://gerrit.googlesource.com/plugins/hooks/)

Additional Gerrit configuration (`etc/replication.config`):

```
# GitLab cannot read refs/for/*, we must push patchsets as branches
[remote "gitlab"]
	url = gitlab.example.com
	push = +refs/heads/*:refs/heads/*
	push = +refs/tags/*:refs/tags/*
	push = +refs/changes/*:refs/heads/review/*
	replicationDelay = 0
	createMissingRepositories = true
```

