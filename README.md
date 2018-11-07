docker-repcached
================

Heavily based on yrobla/docker-repcached with some changes to demonstrate both networking, docker instances and repcached working.

Base docker image to run a Repcached server

How does it work
-----

As you run a docker image as a container, it will have an IP address setted by Docker's bridge network adapter. You can check  all adapters with `docker network ls`. The ideia behind this experiment is to run two containters in the same machine, so they have different IPs connected by the `bridge` adapter. When binding the `bridge` IP in each repcached process, it's possible to simulate the replication workflow between each instance. 

To make this work, you'll have to run both containers, check given IPs to each container with `docker network inspect bridge`, access each container with `docker exec -it <name> /bin/bash` and manually change the memcached start command to fit any other parameters you want to change, but must important to add the next container IPs in the `-x` parameter.

The reason why I'm not using file `run.sh` is that it would already start with slave address to 127.0.0.1 but since we want to experiment between docker containers, we must add the containers IP by the bridge driver, but at the same time the IP will only be given after containers is up. For this reason, you will start both containers as `idle` with `-it --entrypoint /bin/bash`. 

So far this experiment uses only one direct replication node and ciclic. Broadcasting replication to more than one slave needs to be checked. You can achieve more nodes by chaining them as: `cache3 (slave) -> cache2 (slave) -> cache1 (master)` althought it's unkown the effects of this. In not so distant future, I'll write a paper about it.

Usage is better explained bellow.

Usage
-----

1. Clone this repository somewhere;
2. Inside cloned folder:

       $ docker build -t repcache-experiment .
3. We can now run both images as we want. Before start, make sure you have Docker's `bridge` network driver by running

       $ docker network ls
	 
4. Run container cache1 with idle bash, exposing port 11211 so you can write/read memcached's data with telnet:

       $ docker run -d -p 11211:11211 -it --entrypoint /bin/bash repcache-experiment --name cache1
5. Run container cache2 with idle bash too, attaching now host port 11212 to 11211 container's:

       $ docker run -d -p 11212:11211 -it --entrypoint /bin/bash repcache-experiment --name cache2
6. Check for both IPs with

       $ docker network inspect bridge
       [
           {
               "Name": "bridge",
               "Id": "25b6145365c8ba6fa4c8313fe99f450c414f58553aadc37829725820f40de95f",
	       [...]
               "IPAM": {
                   "Driver": "default",
                   "Options": null,
                   "Config": [
                       {
                           "Subnet": "172.17.0.0/16",
                           "Gateway": "172.17.0.1"
                       }
                   ]
               },
	       [...]
               "ConfigOnly": false,
               "Containers": {
                   "3a5b5ff96c5758b31d131b633b425d6f85c74350dee799e46ce5417ee6118277": {
                       "Name": "cache1",
                       "EndpointID": "1303ac42229f5f7cc1c2d64be3e90569267fe811a445722a1f2223d536d4dd9a",
                       "MacAddress": "02:42:ac:11:00:02",
                       "IPv4Address": "172.17.0.2/16",
                       "IPv6Address": ""
                   },
                   "e0905af53d0432af04ee8632d8fb7f7621c38a21a93d549018f92de02929df66": {
                       "Name": "cache2",
                       "EndpointID": "f8f4863f4eabe0b1459dc55f43246a02a6caadd7c48ec0587f755fb064d464d7",
                       "MacAddress": "02:42:ac:11:00:03",
                       "IPv4Address": "172.17.0.3/16",
                       "IPv6Address": ""
                   }
               },
	       [...]
           }
       ]

7. Now get into a container with:

       $ docker exec -it cache1 /bin/bash
       
8. And start using `-x` parameter with cache2's IP:

       $ memcached -u root -v -p 11211 -U 11211 -x 172.17.0.3 -P /var/run/memcachedrep.pid -c 1024 -m 64 -t 4
       $ exit

9. Do the same with the other container, using cache1's IP:

       $ docker exec -it cache1 /bin/bash
       $ memcached -u root -v -p 11211 -U 11211 -x 172.17.0.2 -P /var/run/memcachedrep.pid -c 1024 -m 64 -t 4
       $ exit

10. Now you can run telnet in both 11211 and 11212 ports of your 127.0.0.1, and check how it behaves. Example taken from: https://www.slideshare.net/gear6memcached/implementing-high-availability-services-for-memcached-1911077

        $ telnet 127.0.0.1 11211
        Trying 127.0.0.1...
        Connected to 127.0.0.1.
        Escape character is '^]'.
	
11. Type in `set` command, you'll get confirmation `STORE`:

        set hello 0 0 5
        test

12. Test if it worked with `get` command, you'll get both `test` and `END` output:

        get hello
        VALUE hello 0 5
        teste
        END

13. Exit telnet with `CTRL + ]` then `quit`
14. Run telnet on the other port and check if the value is there too:

        $ telnet 127.0.0.1 11212
        Trying 127.0.0.1...
        Connected to 127.0.0.1.
        Escape character is '^]'.
        get hello
        VALUE hello 0 5
        teste
        END
	

You can then list your containers with  `docker ps` and stop any of them with `docker stop <container name>` to ensure replication is working as it should.
