# WordPress

[![Build Status](https://travis-ci.org/miya0001/wpdev.svg?branch=doc)](https://travis-ci.org/miya0001/wpdev)

This is a private development repository for WordPress

## How to mirror SVN

```
$ mkdir wpdev && cd wpdev
$ git svn clone -s --prefix=svn/ http://develop.svn.wordpress.org .
$ git remote add origin git@github.com:miya0001/wpdev.git
$ git push origin --all
```

## How to sync

```
$ git checkout master
$ git svn rebase
$ git push origin --all
```

## How to crate patch

Create a working branch:

```
$ git checkout master
$ git checkout -b my-patch
```

Commit:

```
$ git add .
$ git commit -m "Some fix"
```

Create a patch:

```
$ git diff master --no-prefix
```