# 分手吧项目api程序

使用lumen框架开发，提供前端和后台所需的api功能

## 依赖

- php >= 7.1.3
- git
- compoer
- redis
- phpredis扩展
- PDO 扩展

## 部署

1. git clone 当前项目
2. composer install
3. 编写配置文件，在当前目录复制一份 `.env.example` 重命名为 `.env` , 根据情况修改.env中的数据库配置和其他配置
4. 本地开发环境执行 `php artisan ide-helper:generate` 生成ide帮助文件

## nginx参考配置

    server {
        listen 80;
        server_name breakup.test;
        root /Users/dawn/Projects/php/breakup/public;
        index index.html index.php;
    
        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }
    
        location ~ \.php$ {
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }
    }

## 框架文档

[Lumen website](http://lumen.laravel.com/docs)

