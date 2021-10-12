# Nocturne Web App

Somewhat a personal playground ヽ(´ー｀)ﾉ  

Project that started as a standalone Web GUI for my Kodi music library.
Uses the Kodi WebSocket API to listen for library updates and asynchronously indexes them in elasticsearch.
Also uses the mercure protocol to publish some updates to the frontend.

Based on Symfony 5 and Vue.JS 3

<img src="doc/player.png" width="800" />

<img src="doc/player_mobile.png" width="400" />

<img src="doc/library.png" width="800" />

<img src="doc/albums.png" width="800" />

<img src="doc/artist.png" width="800" />

## Initialize Project

##### Copy parameters.yml.dist
```bash
cp config/parameters.yml.dist config/parameters.yml
```

##### Create logging index
Open [Kibana](http://127.0.0.1:5601/app/kibana#/management/kibana/index_pattern)  
Use `monolog*` as index pattern;  
Click Next  
Use `@timestamp` as `Time Filter field` name  
Visit your [Logs](<http://127.0.0.1:5601/app/kibana#/discover/76f8cac0-e542-11ea-9027-813245d255b0?_a=(columns:!(channel,command.name,level,message),filters:!(),index:'96c3ce20-e53a-11ea-9027-813245d255b0',interval:auto,query:(language:kuery,query:''),sort:!())&_g=(filters:!(),refreshInterval:(pause:!t,value:0),time:(from:now-15m,to:now))>)

##### Create Elastic Index:
```bash
bin/console ongr:es:index:create
```

#### Mercure

 Create run_mercure.sh  
Copy mercure script and edit `JWT_KEY` and `PUBLISH_ALLOWED_ORIGINS` to your needs
```bash
cp run_mercure.sh.dist run_mercure.sh
```
Get the current legacy build from [Github](https://github.com/dunglas/mercure/releases)

Generate a JWT with your key [here](https://jwt.io/#debugger-io?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJtZXJjdXJlIjp7InB1Ymxpc2giOlsiKiJdfX0.iHLdpAEjX4BqCsHJEegxRmO-Y6sMxXwNATrQyRNt3GY)

More Implementation info:  
https://symfony.com/doc/current/mercure.html

Start mercure with 
```bash
./run_mercure
```

### Tasks after a deployment
Clear cache
```bash
bin/console cache:clear
```
Composer install
```bash
composer install
```
Dump env
```bash
composer dump-env prod
```
Compile scripts
```bash
yarn encore prod
```
Restart Kodi Event Server and Symfony Consumers on supervisor Web GUI
[here](http://localhost:9001/)


### Importing elastic index from production
```bash
elasticdump --input=http://[PRODUCTION_IP]:9200/songs --output=http://elasticsearch:9200/songs --type=analyzer
elasticdump --input=http://[PRODUCTION_IP]:9200/songs --output=http://elasticsearch:9200/songs --type=mapping
elasticdump --input=http://[PRODUCTION_IP]:9200/songs --output=http://elasticsearch:9200/songs --type=data

elasticdump --input=http://[PRODUCTION_IP]:9200/playback --output=http://elasticsearch:9200/playback --type=mapping
elasticdump --input=http://[PRODUCTION_IP]:9200/playback --output=http://elasticsearch:9200/playback --type=data

elasticdump --input=http://[PRODUCTION_IP]:9200/songs --output=http://elasticsearch:9200/playlists --type=mapping
elasticdump --input=http://[PRODUCTION_IP]:9200/songs --output=http://elasticsearch:9200/playlists --type=data

bin/console app:kodi:substitute:audio -vvv
```
