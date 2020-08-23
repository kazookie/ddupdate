# ddupdate

Dynamic DNS client for VALUE DOMAIN

## Usage
### Arch Linux
1. Download PKGBUILD file.
2. Execute makepkg command.
3. Enable ddupdate.service and ddupdate.timer

### Other
1. Download this project.
2. Move to each files as follows.  
/bin/ddupdate.php -> /usr/local/bin/ddupdate  
/services/ddupdate.* -> /etc/systemd/system/ddupdate.*
/conf/ddupdate.json -> /etc/ddupdate.conf
3. Enable ddupdate.service and ddupdate.timer
