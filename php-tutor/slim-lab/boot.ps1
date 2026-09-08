# 迁移后：统一入口位于 public/，由 Slim 4 接管全部路由
php -S localhost:5200 -t public -d upload_max_filesize=50M -d post_max_size=50M
