- create server userflow functionality
    - 

- account verification
    - require email
    - oauth
    - orgs

- cashior stripe payments
    - stripe intergration
    - payments
- deploy and ship (branding, going public)


# must have server features
- display metadata
- launch server
    - os images
    - or choose docker image   

- start, delete, pause server
- SSH management (upload, gen, store)
- ssh command
- port management UI HTTP (80), HTTPS (443), SSH (22) – map to SG rules
- elastic IP


# Features titanbox serviers will have
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