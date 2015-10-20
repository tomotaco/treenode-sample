DROP TABLE IF EXISTS tree_node_tbl;
CREATE TABLE tree_node_tbl (
node_id serial not null comment '記事ID',
node_index bigint unsigned not null comment '記事番号',
node_depth bigint unsigned not null comment '記事の深さ',
node_path_tree text comment '記事へのパス',
active tinyint(1) comment '有効フラグ',
position_left longtext comment '入れ子区間の下限アドレス',
position_right longtext comment '入れ子区間の上限アドレス',
date_created timestamp not null default current_timestamp comment '作成日時',
date_modified datetime comment '更新日時'
) comment='改変入れ子区間モデルツリー構造ノードテーブル';
