# Local
```bash
docker-compose -f docker-compose.local.yml up -d

docker ps
docker exec -it {id}  psql -U admin -d app_database
```