#!/bin/bash

HEADS=${1:-master}

git subsplit init git@github.com:GrupaZero/cms.git
git subsplit publish --heads=$HEADS --no-tags src/Gzero/Core:git@github.com:GrupaZero/core.git
git subsplit publish --heads=$HEADS --no-tags src/Gzero/Entity:git@github.com:GrupaZero/entity.git
git subsplit publish --heads=$HEADS --no-tags src/Gzero/Repository:git@github.com:GrupaZero/repository.git
git subsplit publish --heads=$HEADS --no-tags src/Gzero/Validator:git@github.com:GrupaZero/validator.git
rm -rf .subsplit/