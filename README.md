## 快速开始

Encryption365 是由 [Trust Ocean](https://www.trustocean.cn/) 提供的一款TLS证书，可申请免费的IP或域名证书，但官方只提供了[宝塔插件](https://github.com/londry/Encryption365_Baota)以供使用，本工具基于该插件API重构，支持证书的自动化注册、登录、申请、安装、管理与续签。

### 安装命令

输入以下命令执行安装，数据将存放在 `/etc/encryption365` 目录下，输入 `encryption365` 命令即可调用本工具。

```
shell> curl -sL https://raw.githubusercontent.com/dnomd343/Encryption365/master/install.sh | sh

# 国内用户使用以下命令
shell> curl -sL https://raw.fastgit.org/dnomd343/Encryption365/master/install.sh | sh
```

安装时将会自动写入一个crontab任务，每小时自动检查一次证书续签，可输入 `crontab -l` 查看具体配置。

### 使用说明

**注册账号**

证书申请时必须基于环智中诚账号，你可以选择在 [官网](https://page.trustocean.com/websecurity/welcome) 手动注册，或是使用 `encryption365 regist` 命令快速注册，后者仅需提供邮箱地址接收验证码，其余个人信息可选择如实填写或自动生成。

```
shell> encryption365 regist
This process help you regist a Trustocean account, all you need is an email address to receive the verification code.
Email: ...
There will be an email sent to ..., please note checking.
Verification code: ...
Set a password for your account.
Password: ...
Trustocean need your Chinese ID card num, but you can just press ENTER to generate one.
ID card num:
Random ID card num: 510321198611073407
Random ID card info: 四川省 自贡市 荣县 1986-11-07 female
Please enter your real name, or press ENTER to generate one.
Name:
Random name: 林瑶仪
Last step, enter your phone num, or randomly generate one by press ENTER.
Phone:
Random phone: 15915353380
It seems that all the information is complete, press ENTER to register your account...
Regist success
Use "encryption365 login" command to login.
```

**登录账号**

使用账号邮箱与密码登录，

```
shell> encryption365 login
This process need a Trustocean account, if you don't have it currently, there are two methods:
1. Manually register at "https://page.trustocean.com/websecurity/welcome"
2. Use "encryption365 regist" command regist automatically
Email: ...
Password: ...
Login success
Account status: active
Login time: ...
```

或者将邮箱与密码作为参数输入

```
shell> encryption365 login {email} {password}
...
```

**列出证书**

`encryption365 list` 命令用于列出当前申请或申请中的证书，包括证书主域名、ID、包含域名、申请时间、到期时间等信息。

**申请证书**

`encryption365 issue` 命令申请指定域名的证书，后面需跟上证书类型（ECC或RSA），后接一个或多个域名。

```
shell> encryption365 issue ECC example.com 1.2.3.4
···
```

申请时，CA服务器会对域名所有权进行验证，此处使用HTTP方式进行回应，验证请求格式类似于 `http://{doamin}/.well-known/pki-validation/xx...xxx.txt`，由于前置web服务器的存在，我们需要配置其将目标域名的 `/.well-known/pki-validation/` 路径转发给脚本验证，以*Nginx*配置为例。

```
# 若需要验证多个域名，可以统一反向代理到一个socket
server {
    listen 80;
    server_name 1.2.3.4;
    location / {
        root /var/www/home;
        index index.html;
    }
    location /.well-known/pki-validation/ {
        proxy_set_header Host $http_host;
        proxy_pass http://unix:/var/run/encryption365.sock:/;
    }
}

server {
    listen 80;
    server_name example.com;
    location / {
        return 301 https://$server_name$request_uri;
    }
    location /.well-known/pki-validation/ {
        proxy_set_header Host $http_host;
        proxy_pass http://unix:/var/run/encryption365.sock:/;
    }
}

# 127.0.0.1:9000 为php-fpm的fastcgi端口，在Ubuntu/Debian上一般为socket形式
server {
    listen unix:/var/run/encryption365.sock;
    location / {
        include fastcgi_params;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME /etc/encryption365/validation.php;
    }
}
```

执行 `nginx -s reload` 生效。

申请请求发送后，脚本会每隔30s查询是否验证成功，配置无误的情况下，一般两分钟内就能签发，如果处于高峰期可能需要十分钟以上，签发成功后，登录账号的邮箱将会收到一封签发成功的邮件。如果30分钟后仍未签发，则脚本会报错退出，这种情况可以手动执行重新验证。

**重新验证**

申请证书时，验证可能会出现超时失败，使用 `encryption365 reverify {HOST}` 命令可以对指定站点重新验证签发。

**刷新证书**

如果不慎删除证书文件，或在等待签发期间强制退出，可以使用 `encryption365 flash {HOST}` 命令刷新指定站点，检查证书状态并重新下载。

**续签证书**

免费证书一次签发有效期为三个月，执行 `encryption365 renew {HOST}` 命令可续签指定站点的证书，不过该命令大多数情况下无需手动执行，系统配置了crontab任务将会持续运行 `encryption365 autorenew` 指令，自动对即将过期证书进行续签。

**安装证书**

证书申请成功后，执行 `encryption365 install {HOST} [options]` 命令可安装指定站点的证书，可用选项如下：

+ fullchain：完整证书链

+ key：私钥文件

+ cert：证书文件

+ ca：CA证书文件

+ cmd：安装完成后执行的命令

例如安装给*Nginx*使用

```
shell> encryption365 install example.com \
fullchain=/etc/ssl/certs/example.com/fullchain.pem \
key=/etc/ssl/certs/example.com/privkey.pem \
cmd="systemctl force-reload nginx"
Install OK
```

**自动续签**

执行 `encryption365 autorenew` 命令将会检查全部站点，如果发现证书将于十天后过期，将自动执行续签工作，该命令无需手动执行，安装时将会被自动添加至系统crontab定时任务中。
