EC2 Setup steps:
Attach an EBS volume to your EC2 instance
Format and mount it: sudo mkfs -t xfs /dev/xvdf && sudo mkdir /data && sudo mount /dev/xvdf /data
Update the volume path in docker-compose: - /data:/var/lib/postgresql/data
Add to /etc/fstab for persistence: /dev/xvdf /data xfs defaults,nofail 0 2

docker-compose -f docker-compose.prod.yml up -d