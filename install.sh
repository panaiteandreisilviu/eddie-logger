#!/bin/bash

cd /tmp
git clone git@github.com:panaiteandreisilviu/eddie-logger.git
cd eddie-logger
bash local_install.sh
rm -rf /tmp/eddie-logger