#!/bin/bash
git subsplit init git@github.com:GrupaZero/cms.git
git subsplit publish src/Gzero/Entity:git@github.com:GrupaZero/entity.git
rm -rf .subsplit/
