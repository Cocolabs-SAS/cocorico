FLUSH PRIVILEGES;
CREATE DATABASE IF NOT EXISTS `cocorico` CHARACTER SET utf8 COLLATE utf8_unicode_ci;
GRANT ALL ON `cocorico`.* TO 'cocorico'@'%' IDENTIFIED BY 'cocorico';
GRANT ALL ON `cocorico`.* TO 'cocorico'@'localhost' IDENTIFIED BY 'cocorico';