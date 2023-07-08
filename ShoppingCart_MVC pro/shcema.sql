CREATE TABLE users (
    user_id bigint NOT NULL AUTO_INCREMENT,
    account varchar(100) NOT NULL,
    pw varchar(30) NOT NULL,
    user_name varchar(50) NOT NULL,
    email varchar(100),
    points int DEFAULT 100000,
    api_key varchar(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE (account),
    PRIMARY KEY (user_id)
);

CREATE TABLE refresh_token (
    token_hash varchar(64) NOT NULL,
    expires_at int UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (token_hash),
    INDEX (expires_at)
);

CREATE TABLE items (
    item_id bigInt not null AUTO_INCREMENT,
    item_name varchar(100),
    is_active boolean default true,
    is_visible boolean default true,
    price int default 0,
    count int default 0,
    img_item varchar(255),
    des varchar(255),
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (item_id)
);

CREATE TABLE user_orders (
    order_id varchar(255) not null,
    status int not null,
    user_id bigint not null,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (order_id)
);

CREATE TABLE item_trade_orders (
    item_trade_id bigInt not null AUTO_INCREMENT,
    item_id bigint not null,
    count_sum int default 0,
    price_sum int default 0,
    order_id varchar(255) not null,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (item_trade_id)
);



insert into users (account, user_name, pw, email) values ('doraemon','小叮噹','1234','aaa@gmail.com');
insert into users (account, user_name, pw, email) values ('nobita','大雄','5678','bbb@gmail.com');
insert into users (account, user_name, pw, email) values ('sizuka','靜香','9876','ccc@gmail.com');

insert into items (item_name, price, count, img_item, des) value ('anywhere2s', 1299, 5, "https://drive.google.com/uc?export=view&id=1sXcNvo92E4liKw-wJU06Wtdt8qApdy4E", "羅技萬用滑鼠");
insert into items (item_name, price, count, img_item, des) value ('M575', 1499, 5, "https://drive.google.com/uc?export=view&id=1ve00XUUXBdloey_QaVFNeU7Y5PXIaX8b", "羅技軌跡球");
insert into items (item_name, price, count, img_item, des) value ('Atheris', 699, 5, "https://drive.google.com/uc?export=view&id=1rc2nO5o33I-NWCC8tLkl7oLLL2ZLchMO", "雷蛇雙模滑鼠");
insert into items (item_name, price, count, img_item, des) value ('G304', 999, 5, "https://drive.google.com/uc?export=view&id=17SDOJfdM-cloPBoKtp0hVDFqW4Wzs-gJ", "羅技G304滑鼠");
insert into items (item_name, price, count, img_item, des) value ('Kensington Orbital Fusion',2400, 5, "https://drive.google.com/uc?export=view&id=1zY36uR5iz1G0QjIvjHDG8d4h9z8FgCtK", "Kensington軌跡球");
insert into items (item_name, price, count, img_item, des) value ('Filco Ninja', 3899, 5, "https://drive.google.com/uc?export=view&id=1LuvTCkG1oxQ-yZkO9TY-F8pecrBolC9Q", "忍茶鍵盤");
insert into items (item_name, price, count, img_item, des) value ('Leopold FBT660M', 4500, 5, "https://drive.google.com/uc?export=view&id=1VfLWp3oBEfxOgnUkx2S3wksA4ZoDVrSo", "Leopold FBT660M藍芽雙模鍵盤");
insert into items (item_name, price, count, img_item, des) value ('Keychron K6', 2499, 5, "https://drive.google.com/uc?export=view&id=13rERrzpRlMbefjD0mxMXOGoj0DVlgTD-", "Keychron K6機械鍵盤");

