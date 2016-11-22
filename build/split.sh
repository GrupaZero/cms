#!/bin/bash
git subsplit init git@github.com:GrupaZero/cms.git
git subsplit publish src/Gzero/Core:git@github.com:GrupaZero/core.git &
git subsplit publish src/Gzero/Entity:git@github.com:GrupaZero/entity.git &
git subsplit publish src/Gzero/Repository:git@github.com:GrupaZero/repository.git &
git subsplit publish src/Gzero/Validator:git@github.com:GrupaZero/validator.git &
wait
rm -rf .subsplit/
