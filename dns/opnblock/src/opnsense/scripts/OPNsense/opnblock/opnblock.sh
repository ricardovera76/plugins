# !/bin/sh
# opnblock, opnblock.sh

# include config-generated blacklist/whitelist, commas replaced with spaces
. /usr/local/opnsense/scripts/OPNsense/opnblock/data.sh

# prep temp storage and conf file
rm '/tmp/hosts.working' '/var/unbound/opnblock.conf'
touch '/tmp/hosts.working'

# curl all the lists!
for url in $blacklist; do
    curl --silent $url >> "/tmp/hosts.working"
done

# sort all the lists and remove any whitelist items!
awk -v whitelist="$whitelist" '$1 ~ /^127\.|^0\./ && $2 !~ whitelist {gsub("\r",""); print tolower($2)}' /tmp/hosts.working | sort | uniq | \
awk '{printf "server:\n", $1; printf "local-data: \"%s A 0.0.0.0\"\n", $1}' > /var/unbound/opnblock.conf

# clear the temp storage!
rm '/tmp/hosts.working'
