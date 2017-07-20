FROM wordpress

#ENV http_proxy 'http://10.130.21.8:8080'
#ENV https_proxy 'http://10.130.21.8:8080'
#RUN echo 'Acquire::http::Proxy "http://10.130.21.8:8080";' >  /etc/apt/apt.conf.d/71proxy
RUN apt-get update && apt-get -y install php5-xdebug telnet wget
RUN ln -s /usr/lib/php5/20131226/xdebug.so /usr/local/lib/php/extensions/no-debug-non-zts-20131226

RUN echo "[XDebug]\nxdebug.remote_enable = 1\nxdebug.remote_autostart = 1\nxdebug.remote_port = 9000\nxdebug.remote_host = 172.18.0.1" >> /usr/local/etc/php/conf.d/xdebug.ini
