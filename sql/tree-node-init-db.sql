DROP DATABASE IF EXISTS treenode;
CREATE DATABASE treenode DEFAULT CHARACTER SET utf8;
GRANT ALL ON treenode.* TO treenodeuser@'localhost' IDENTIFIED BY 'treenodepassword';
GRANT ALL ON treenode.* TO treenodeuser@'127.0.0.1' IDENTIFIED BY 'treenodepassword';

