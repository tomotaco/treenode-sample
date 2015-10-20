# treenode-sample
Tree node sample (specified for BBS) with nested interval encoding and string addressing

## How to install

- Copy the entire source tree to your favorite directory.
$ cp -vr treenode-sample /var/www/html/

- Modify DB user and password in sql/tree-node-init-db.sql

- Create DB with sql/tree-node-init-db.sql
$ mysql -u [root db username] -p[root db password] < sql/tree-node-init-db.sql

- Create DB table with sql/tree-node-schema.sql
$ musql -u [your db username] -p[your db password] < sql/tree-node-schema.sql

- Modify $dsn in ajax/config.php for your configuration.
$dsn = "mysql://treenodeuser:treenodepassword@localhost/treenode";

- Modify the following line in js/tree-node.js for your configuration.
var urlApi = "/treenode-sample/ajax/tree-node-request-handler.php";

- Browse index.html on your browser.

- If it doesn't work, please check httpd log or /tmp/TreeNode.log
