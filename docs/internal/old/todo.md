- server userflow functionality
	- display metadata
		- server page (display detail from request)
		- display elastic IP, public, private ip ect
		- list show data (ip, status, ect)
	- launch server modal
		- name
		- os images (aws linux, ubuntu)
		- server location (US)
		- server type (t2.micro)
		- choose key or generate key
		- security group
		- ~~or choose docker image~~ (out of scope) 
	- start and pause server
		- delete request management
	- key manager (tied to users)
		- auto generate keys
		- key db
		- upload key
	- generate ssh command to ssh into server
	- ~~web tunnel iu~~ (out of scope)
	- port management UI (security group rules abstraction) 
		- what port is public and to who (which ip?, public or for a specific ip?)
		- HTTP (80),
		- HTTPS (443), 
		- SSH (22) 
- pg database product
	- abstraction over servers (deploy a pg image instead of server)


- account and payments
	- require email verification
	- oauth
	- create, invite, manage orgs
	- cashior stripe intergration
	- make payments (billing ui)
	- invoice tracker and mail

- deploy and ship
	- domain name
	- host publically
	- test that people can use the product

- improve and iterate
	- docker, container service, kubernety
	- s3
	- cdn, serverless compute (edge assets, edge compute)
	- serverless db
	- document and redius
	- web ui
	- inrtiajs ui
	- 

  
# Features closurebox serviers will have

    - metadata: name, DNS/IP, region+AZ, instance type, state, system uptime

        public/private IPs

        Static IP (Elastic IP) attachment

        Internal DNS / routing name (e.g., dev-app123.platform.io)

        Port management UI (firewall + security group abstraction)

        start, stop, pause vm

        sleepy vms

        logs

    - web terminal or ssh command generator

    - key-pair management (upload public SSH key or auto generate ephemeral key, store keys)

    - Provisioning Controls: choose OS image, region, instance type, snapshop and cloning,

    - Firewall rules (wrapped around security groups)

    - Git deploy hook

    - file management web ui

    - Service health checks (HTTP probe / ping) and Basic logging console (e.g., tail logs via journald or PM2)

bastion or gateway proxy

    - docker intergration (start with this docker image)