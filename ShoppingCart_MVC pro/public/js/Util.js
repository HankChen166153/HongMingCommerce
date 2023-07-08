// api path (prod)
var apiItemDetailPath = "http://hongmingcommerce.ddns.net/shoppingCart/api/fetchItem";
var apiListPath = "http://hongmingcommerce.ddns.net/shoppingCart/api/itemList";
var apiLoginPath = "http://hongmingcommerce.ddns.net/shoppingCart/api/login";
var apiLogoutPath = "http://hongmingcommerce.ddns.net/shoppingCart/api/logout";
var apiRefreshTokenPath = "http://hongmingcommerce.ddns.net/shoppingCart/api/refreshToken";
var apiRegisterPath = "http://hongmingcommerce.ddns.net/shoppingCart/api/register";
var apiUserOrderPath = "http://hongmingcommerce.ddns.net/shoppingCart/api/userOrder";
var apiCheckOut = "http://hongmingcommerce.ddns.net/shoppingCart/api/checkout";

// api path (dev)
// var apiItemDetailPath = "http://localhost/shoppingCart/api/fetchItem";
// var apiListPath = "http://localhost/shoppingCart/api/itemList";
// var apiLoginPath = "http://localhost/shoppingCart/api/login";
// var apiLogoutPath = "http://localhost/shoppingCart/api/logout";
// var apiRefreshTokenPath = "http://localhost/shoppingCart/api/refreshToken";
// var apiRegisterPath = "http://localhost/shoppingCart/api/register";
// var apiUserOrderPath = "http://localhost/shoppingCart/api/userOrder";
// var apiCheckOut = "http://localhost/shoppingCart/api/checkout";

var STATE = {
    LOGIN: "login",
    REG: "reg",
    LIST: "list",
    INFO: "info",
    TRADE_LIST: "tradeList"
};
var allState = [STATE.LOGIN, STATE.REG, STATE.LIST, STATE.INFO, STATE.TRADE_LIST];

var apiKey = "";
var user = { userName: "", point: "" }; //用戶資訊
var accessToken = "";
var refreshToken = "";
var currentState = STATE.LOGIN; //當前狀態
var ltems_list_all = []; //賣場清單
// var trade_list=[];
var myMap = new Map();

// 分頁參數
var firstPageNum = 1;
var lastPageNum = 1;
var currentPage = 1;
var nextPage = "";
var prevPage = "";

GoToState(STATE.LOGIN);

function register() {
    console.log("------------register------------");
    //UserName Account Password
    let u_name = document.getElementById('username_reg').value;
    let ac_reg = document.getElementById('account_reg').value;
    let pw_reg = document.getElementById('pw_reg').value;
    let email = document.getElementById('email_reg').value;
    console.log("u_name: " + u_name);
    console.log("ac_reg: " + ac_reg);
    console.log("pw_reg: " + pw_reg);
    console.log("email" + email);
    let apipath = apiRegisterPath;
    dataJson = {
        "user_name": u_name,
        "account": ac_reg,
        "pw": pw_reg,
        "email": email
    }
    console.log('apipath: ' + apipath);
    registerCheck(apipath, dataJson);
}

//註冊檢查
function registerCheck(apipath, dataJson) {
    console.log("------------register check------------");
    apiRegister(apipath, dataJson, (res) => {
        console.log("---------registercheck1-----------");
        console.log("[register]result[n]:" + res);
        console.log("[register]result:" + JSON.stringify(res));
        if (res.status == "success") {
            accessToken = res.data.access_token;
            refreshToken = res.data.refresh_token;
            user.userName = res.data.user_name;
            GoToState("login");
            console.log("註冊成功");
            alert("註冊成功!!");
        } else {
            alert("註冊失敗!!");
        }
    })
}

//apiRegister
function apiRegister(path, dataJson, onCompelete) {
    let apiPath = path;

    console.log('dataJson' + dataJson);
    console.log('path: ' + path);
    console.log("----------apiRegister---------");
    $.ajax(
        {
            type: 'post',
            url: apiPath,
            dataType: 'json',
            crossDomain: true,
            contentType: 'application/json; charset=UTF-8',
            data: JSON.stringify(dataJson),
            success: function (data) {
                console.log("data: " + data);
                $.each(data, function (key, val) {
                    console.log("key:" + key + " ,val:" + val);
                    if (key == "data") {
                        $.each(data["data"], function (key, val) {
                            console.log("[data]key:" + key + " ,[data]val:" + val);
                        });
                    }
                }
                );
                onCompelete(data);
            }
        }
    ).fail(function (xhr, status, error) {
        console.log("【APITool】::Post fail!! 「" + apiPath + "」" + error.message);
        alert("【APITool】::Post fail!! 「" + apiPath + "」");
    });
}

//回到登入頁
function backHomepage() {
    console.log("--------回到登入頁--------");
    alert("回到登入頁");
    GoToState('login');
}

// Login 
function Login() {
    console.log("------------Login------------");
    let ac = document.getElementById('account').value;
    let pw = document.getElementById('password').value;
    console.log("ac: " + ac);
    console.log("pw: " + pw);
    let apipath = apiLoginPath;
    dataJson = {
        "ac": ac,
        "pw": pw
    }
    console.log('apipath: ' + apipath);
    loginCheck(apipath, dataJson);
}

// 登入檢查
function loginCheck(apipath, dataJson) {
    console.log(dataJson + "aaaaaaaaa");
    apiLogin(apipath, dataJson, (res) => {
        console.log("ggggggggggggggggggggg");
        // console.log(typeof JSON.parse(res));
        // var res = JSON.parse(res);
        console.log("[login]result[n]:" + res);
        console.log("[login]result:" + JSON.stringify(res));

        if (res.status == "success") {
            accessToken = res.data.access_token;
            refreshToken = res.data.refresh_token
            user.userName = res.data.user_name;
            user.point = res.data.points;

            localStorage.setItem("access_token", accessToken);
            localStorage.setItem("refresh_token", refreshToken);
            console.log('user_name: ' + user.userName);
            console.log('point: ' + user.point);
            UpdateUserInfo();
            GoToState("list");
            alert("登入成功!!");
            console.log("登入成功!!");
        }
        else {
            alert("登入失敗!!");
        }
    });
}

function apiLogin(path, dataJson, onCompelete) {
    let apiPath = path;

    console.log('dataJson' + dataJson);
    console.log('path: ' + path);
    console.log("aaaaaaaaaaaaaaaaaaaaaaaaaaaaa");
    $.ajax({
        type: 'POST',
        url: apiPath,
        dataType: 'json',
        crossDomain: true,
        contentType: 'application/json; charset=UTF-8',
        data: JSON.stringify(dataJson),
        success: function (data) {
            console.log("data: " + data);
            $.each(data, function (key, val) {
                console.log("key:" + key + " ,val:" + val);
                if (key == "data") {
                    $.each(data["data"], function (key, val) {
                        console.log("[data]key:" + key + " ,[data]val:" + val);
                    });
                }
            }
            );
            onCompelete(data);
        }
    }).fail(function (xhr, status, error) {
        console.log("【APITool】::Post fail!! 「" + apiPath + "」" + error.message);
        alert("【APITool】::Post fail!! 「" + apiPath + "」");
    });
}

function logout() {
    console.log("------------Logout------------");
    let apipath = apiLogoutPath;
    dataJson = {
        "token": refreshToken
    }
    console.log('apipath: ' + apipath);
    logoutCheck(apipath, dataJson);
}

function logoutCheck(apipath, dataJson) {
    apiLogout(apipath, dataJson, (res) => {
        if (res.status == "success") {
            GoToState('login');

            UpdateUserInfo('login');
            alert("登出成功!!");
        } else {
            alert("登出出了毛病!!")
        }
    });
}

function apiLogout(path, dataJson, onCompelete) {
    let apiPath = path;
    $.ajax({
        type: 'POST',
        url: apiPath,
        dataType: 'json',
        crossDomain: true,
        contentType: 'application/json; charset=UTF-8',
        data: JSON.stringify(dataJson),
        success: function (data) {
            console.log("data: " + data);
            $.each(data, function (key, val) {
                console.log("key:" + key + " ,val:" + val);
                if (key == "data") {
                    $.each(data["data"], function (key, val) {
                        console.log("[data]key:" + key + " ,[data]val:" + val);
                    });
                }
            }
            );
            onCompelete(data);
        }
    }).fail(function (xhr, status, error) {
        console.log("【APITool】::Post fail!! 「" + apiPath + "」" + error.message);
        alert("【APITool】::Post fail!! 「" + apiPath + "」");
    });
}


function showItemList(apiListPath) {
    console.log('---------showList---------');
    console.log('apiListPath: ' + apiListPath);
    console.log("accessToken: " + accessToken);
    let root = document.getElementById("listItems");
    console.log("tmpRoot: " + root);
    while (root.firstChild) {
        root.removeChild(root.lastChild); //將節點消除，使其切頁面返回時不會重複
    }
    apiGet(apiListPath, (res) => {
        ltems_list_all = res.data;
        console.log('---------showList-ApiGet---------');
       
        if (ltems_list_all != undefined) {
            // console.log("type: " + typeof ltems_list_all);
            // console.log(ltems_list_all);
            let tmpIndex = 0;
            ltems_list_all.forEach(element => {
                addItemToDisplay(root, tmpIndex, element['item_id'], element['item_name'], element['img_item'], element['price']);
                tmpIndex++;
            });
        }
    });
}

//添加商品在畫面上
function addItemToDisplay(root, index, id, name, img_url, item_price) {
    console.log();
    let tmpDiv = document.createElement("div");
    let ItemName = document.createElement('div');
    let img = document.createElement('img');
    let price = document.createElement('div');
    let btn = document.createElement('button');
    let cart = document.createElement('i');
    //與css有關
    tmpDiv.classList.add('product-box');
    ItemName.classList.add('product-title');
    img.classList.add('product-img');
    price.classList.add('product-price');
    cart.classList.add("bx", "bx-cart", "add-cart", "add-cart:hover");
    cart.id = "item_id" + id;
    // console.log("cart.id: " + cart.id);
    cart.addEventListener("click", handle_addCartItem);//line 610


    img.src = img_url;
    ItemName.innerText = name;
    tmpDiv.appendChild(img);
    tmpDiv.appendChild(ItemName);
    price.innerText = item_price;

    //創出info按鈕
    btn.innerText = "itemInfo";
    // tmpDiv.appendChild(btn);
    tmpDiv.appendChild(price);
    tmpDiv.appendChild(cart);
    root.appendChild(tmpDiv);
}

//詳細商品資訊
function showItemDetail(apiPath, dataJson) {
    let path = apiPath;
    console.log('---------showDetail---------');
    let root = document.getElementById("itemInfo");
    console.log("tmpRoot: " + root);
    while (root.firstChild) {
        root.removeChild(root.lastChild); //將節點消除
    }
    apiPost(path, dataJson, (res) => {
        item = {};
        console.log('item: ' + item);
        console.log('---------showDetail-ApiPost---------');
        if (res.status == "success") {
            item = res.data;
            console.log(item);
            GoToState('info');
            if (item !== undefined) {

                itemDetailDisplay(root, item);
            }
        } else {
            alert("item error");
        }
    });
}

// 進入單一商品頁
function itemDetailDisplay(root, itemInfo) {
    console.log("進入單一商品");
    let tmpDiv = document.createElement("div");
    let ItemName = document.createElement('div');
    let img = document.createElement('img');
    let price = document.createElement("div");
    let count = document.createElement("div");
    let cart = document.createElement('i');
    //css    
    tmpDiv.classList.add('product-box');
    img.classList.add('product-img');
    price.classList.add('product-price');
    count.classList.add('product-count');
    ItemName.classList.add('product-title');
    cart.classList.add("bx", "bx-cart", "add-cart", "add-cart:hover");
    cart.addEventListener("click", handle_addCartItem);
    //排版
    img.src = itemInfo.img_item;//img_item是參照後端取名
    ItemName.innerText = itemInfo.item_name;
    price.innerText = itemInfo.price;
    count.innerText = itemInfo.count;

    tmpDiv.appendChild(img);
    tmpDiv.appendChild(ItemName);
    tmpDiv.appendChild(price);
    tmpDiv.appendChild(count);
    tmpDiv.appendChild(cart);
    root.appendChild(tmpDiv);
}

//購物車交易
function checkOut(path, dataJson) {

    console.log("---------開始交易----------");
    apiPostWithHeader(path, dataJson, (res, status) => {
        console.log("statusCode: " + status);
        if (res !== undefined || res !== null) {
            console.log(res["message"] + "  22222222222222222");
            if (res['status'] == "success") {
                item = res.data;
                if (item !== undefined) {
                    console.log(item); //這邊item為orderid
                    alert("Your order is placed successfully! :)");
                }
            } else {
                alert(res["message"]);
            }
            console.log("---------結束交易----------");
            clearCart();
        } else {
            console.log("----------checkout----------");
            checkOut(apiPath, dataJson)
        }
    });

}

  
// 更新用戶資訊
function UpdateUserInfo(state) {
    console.log('state:' + currentState);
    if (state != STATE.LOGIN) {
        document.getElementById("userinfo").innerText = "[userName]:" + user.userName + ",[point]:" + user.point;
        document.getElementById("userinfo").style = "display:block;height: 20px;width: 100vw;background-color: black;color: white;";
    }
    else {
        document.getElementById("userinfo").innerText = "";
        document.getElementById("userinfo").style = "display:none;height: 20px;width: 100vw;background-color: black;color: white;";
    }
}

// 開啟狀態:將其他DIV關閉
function OpenState(state) {
    allState.forEach(element => {
        if (element == state) {
            Visable_Elm(element, true);
        }
        else {
            Visable_Elm(element, false);
        }
    });
}

// 更新用戶資訊
function GoToState(state) {
    console.log("state:" + state);
    switch (state) {
        case STATE.LOGIN:
        case STATE.REG:
        case STATE.CART:
        case STATE.SELL:
        case STATE.INFO:
        case STATE.TRADE_LIST:
            OpenState(state);
            break;
        case STATE.LIST:
            OpenState(state);
            showItemList(apiListPath);
            break;
        default:
            break;
    }

    //不等於登入或註冊狀態時
    if (state != STATE.LOGIN && state != STATE.REG) {
        console.log("hide login");
        Visable_Elm("menu", true);
        Visable_Elm("menu_login", false);
    }
    else {
        console.log("show login");
        Visable_Elm("menu", false);
        Visable_Elm("menu_login", true);
    }
}

function GoToPage(stepFlag) {
    if (stepFlag) {
        showItemList(nextPage);
    }
    else {
        showItemList(prevPage);
    }
}

function Visable_Elm(elmID, tag) {
    let visString = tag ? "block" : "none";
    if (document.getElementById(elmID) != undefined) {
        document.getElementById(elmID).style.display = visString;
    }
}

function apiGet(path, onComplete) {
    let apiPath = path;
    console.log("accessToken: " + accessToken);

    let headersObj = {};
    if (accessToken != undefined && accessToken != null && accessToken.length > 0) {
        headersObj = {
            'Authorization': `Bearer ${accessToken}`
        };
    }

    $.ajax({
        type: "GET",
        headers: headersObj,
        url: apiPath,
        dataType: "json",
        crossDomain: true,
        contentType: 'application/json; charset=UTF-8',
        success: function (data) {
            $.each(data, function (key, val) {
                console.log("key:" + key + " ,val:" + val);
                if (key == "data") {
                    $.each(data["data"], function (key, val) {
                        console.log("[data]key:" + key + " ,[data]val:" + val);
                    });
                }
            });
            console.log("-------------- data End --------------");
            onComplete(data);
        }
    }).fail(function () {
        console.log("【APITool】::GET fail!! 「" + apiPath + "」");
        alert("【APITool】::Post fail!! 「" + apiPath + "」");
    });
}


function apiPost(path, dataJson, onComplete) {
    let apiPath = path;

    let headersObj = {};
    if (accessToken != undefined && accessToken != null && accessToken.length > 0) {
        headersObj = {
            'Authorization': `Bearer ${accessToken}`
        };
    }

    $.ajax({
        type: "POST",
        url: apiPath,
        dataType: "json",
        crossDomain: true,
        contentType: 'application/json; charset=UTF-8',
        data: JSON.stringify(dataJson),
        success: function (data) {
            $.each(data, function (key, val) {
                console.log("key:" + key + " ,val:" + val);
            });
            console.log("-------------- data End --------------");
            onComplete(data);
        }
    }).fail(function () {
        console.log("【APITool】::Post fail!! 「" + apiPath + "」");
        alert("【APITool】::Post fail!! 「" + apiPath + "」");
    });

}

function apiPostWithHeader(path, dataJson, onCompelete) {
    let apiPath = path;

    let headersObj = {};
    if (accessToken != undefined && accessToken != null && accessToken.length > 0) {
        headersObj = {
            'Authorization': `Bearer ${accessToken}`
        };
    }

    $.ajax({
        type: "POST",
        headers: headersObj,
        url: apiPath,
        dataType: "json",
        crossDomain: true,
        contentType: 'application/json; charset=UTF-8',
        data: JSON.stringify(dataJson),
        success: function (data) {
            $.each(data, function (key, val) {
                console.log("key:" + key + " ,val:" + val);
            });
            console.log("--------------------Data End--------------------");
        }
    }).done(function (data, textStatus, xhr) {
        console.log("done data:");
        $.each(data, function (key, val) {
            console.log("key:" + key + " ,val:" + val);
        });
        console.log("done xhr_status: " + textStatus);
    }).fail(async function (xhr, textStatus, errorThrown) {
        console.log("status: " + xhr.status);
        if (xhr.status === 401) {
            const response = await fetch(apiRefreshTokenPath, {
                headers: {
                    'Authorization': "Bearer " + localStorage.getItem("access_token")
                },
                method: "POST",
                body: JSON.stringify({
                    token: localStorage.getItem("refresh_token")
                })
            });
            console.log("----------401----------");
            const json = await response.text();
            const obj = JSON.parse(json);
            console.log("access_token: " + obj.data.access_token);
            console.log("refresh_token: " + obj.data.refresh_token);
            localStorage.setItem("access_token", obj.data.access_token);
            localStorage.setItem("refresh_token", obj.data.refresh_token);

            if (response.status === 200) {
                console.log("Got new access token and refresh token");
                console.log(localStorage.getItem("access_token"));

                const response = await fetch(apiPath, {
                    headers: {
                        "Authorization": "Bearer " + localStorage.getItem("access_token")
                    },
                    method: 'POST',
                    body: JSON.stringify(dataJson)
                });

                const json = await response.text();
                const obj = JSON.parse(json);
                if (response.status === 200) {
                    console.log("bbbbbbbbbbbbbbbbbbbbbbbb");
                    onCompelete(obj, response.status);
                }
            }
        } else {
            onCompelete(null, errorThrown.status);
        }
    });
}

//cart
let cartIcon = document.querySelector("#cart-icon");
let cart = document.querySelector(".cart");
let closeCart = document.querySelector("#close-cart");

//open cart
cartIcon.addEventListener('click', () => {
    cart.classList.add("active");
})
//close cart
closeCart.addEventListener('click', () => {
    cart.classList.remove("active");
})
//cart working js
if (document.readyState == "loading") {
    console.log("==========loading==========");
    document.addEventListener("DOMContentLoaded", ready);
} else {
    console.log("==========start==========");
    ready();
}

// start
function ready() {
    console.log("start==========addEvents==========");
    addEvents();
    console.log("start==========Finish==========");
}
// update
function update() {
    console.log("update==========addEvents==========");
    addEvents();
    console.log("update==========updateTotal==========");
    updateTotal();
    console.log("update==========Finish==========");

}
// addEvents
function addEvents() {
    // remove items from cart
    let cartRemove_btns = document.querySelectorAll('.cart-remove');
    console.log(cartRemove_btns);
    console.log("==========cart-remove==========");
    cartRemove_btns.forEach((btn) => {
        btn.addEventListener("click", handle_removeCartItem);
    });

    // change item quantity
    let cartQuantity_inputs = document.querySelectorAll('.cart-quantity');
    console.log("==========cart-quantity==========");
    cartQuantity_inputs.forEach(input => {
        input.addEventListener("change", handle_changeItemQuantity);
    });

    //add item to cart 和video不同
    // let addCart_btns = document.querySelectorAll(".add-cart");
    // console.log("==========add-cart==========");
    // addCart_btns.forEach((btn) =>{
    //     btn.addEventListener("click",handle_addCartItem);
    // });

    //buy order
    const buy_btn = document.querySelector(".btn-buy");
    buy_btn.addEventListener("click", handle_buyOrder);


}

// handle Events functions
let itemsAdded = [];

function handle_addCartItem() {
    console.log("start==========handle_addCartItem==========");
    let product = this.parentElement;
    let title = product.querySelector(".product-title").innerHTML;
    let price = product.querySelector(".product-price").innerHTML;
    let img = product.querySelector(".product-img").src;
    let itemId = product.querySelector('.add-cart').id.replace("item_id", "");

    // console.log("itemId: " + itemId);
    // console.log(title, price, img);

    let newToAdd = {
        title,
        price,
        img,
        itemId,
    };

    //handle item is already exist
    if (itemsAdded.find((el) => el.title == newToAdd.title)) {
        alert("this item is already exist");
        return;
    } else {
        itemsAdded.push(newToAdd);
    }

    //add product to cart
    let cartBoxElement = CartBoxComponent(title, price, img, itemId);
    let newNode = document.createElement("div");
    newNode.innerHTML = cartBoxElement;
    const cartContent = cart.querySelector(".cart-content");
    cartContent.appendChild(newNode);

    update();
    console.log("finish==========handle_addCartItem==========");
}
//移除購物車的商品
function handle_removeCartItem() {
    console.log("start===========handle_removeCartItem===========");
    this.parentElement.remove();
    itemsAdded = itemsAdded.filter(
        (el) =>
            el.title !=
            this.parentElement.querySelector('.cart-product-title').innerHTML
    );

    myMap.clear();

    update();
    console.log("finish===========handle_removeCartItem===========");
}

function handle_changeItemQuantity() {

    if (isNaN(this.value) || this.value < 1) {
        this.value = 1;
    }
    this.value = Math.floor(this.value); //購買數量保持整數
    // console.log("countSum: " + this.value);

    update();
    console.log("finish==========handle_changeItemQuantity==========");
}

function handle_buyOrder() {
    console.log("start-------buyorder-------");

    if (itemsAdded.length <= 0) {
        alert("there is no order to place yet! \nplease make an order first");
        return;
    }

    let path = apiCheckOut;
    let itemsArr = [];

    for (const [key, value] of myMap) { // Using the default iterator (could be `map.entries()` instead)
        console.log("==========foreach==========");
        console.log(`The value for key ${key} is ${value}`);
        console.log("typeof: " + typeof key, typeof value);


        let object = {
            "item_id": parseInt(key),
            "count_sum": parseInt(value)
        };
        itemsArr.push(object);


    };

    itemsArr.forEach(element => {
        console.log("itemId: " + element.item_id);
        console.log("count_sum: " + element.count_sum);
    });

    console.log("cart: " + cart);
    let dataJson = { "cart": itemsArr };
    // let dataJson = {
    //     "cart": [
    //         {
    //             "item_id" : itemId,
    //             "count_sum" : count_sum
    //         }
    //     ]
    // };

    checkOut(path, dataJson);

    console.log("baaaaaaaaaaaaaaaaaaaaaack");
    // console.log("item_id: " + myMap.get("item_Id"));
    // console.log("count_sum: " + myMap.get("countSum"));
    // const cartContent = cart.querySelector(".cart-content");
    // cartContent.innerHTML = '';//清空購物車
    // alert("your order is placed successfully :)");
    // itemsAdded = [];//避免送出後重新加購物車出錯

    update();

    console.log("finish=======handle_buyOrder==========");
}

// update and rerender functions
function updateTotal() {
    console.log("start===========updateTotal===========");
    let cartBoxes = document.querySelectorAll('.cart-box');
    const totalElement = cart.querySelector('.total-price');
    let total = 0;
    cartBoxes.forEach(cartBox => {
        let itemId = cartBox.querySelector('.detail-box').id;
        let priceElement = cartBox.querySelector('.cart-price');
        let price = parseFloat(priceElement.innerHTML.replace("$", ""));
        let quantity = cartBox.querySelector(".cart-quantity").value;
        total += price * quantity;
        console.log("quantity: " + itemId, price, quantity);

        myMap.set(itemId, quantity);

    });

    console.log("updateTotal==========start map==========");
    for (const [key, value] of myMap) { // Using the default iterator (could be `map.entries()` instead)
        console.log("==========foreach==========");
        console.log(`The value for key ${key} is ${value}`);
    };
    console.log("updateTotal==========end map==========");


    //keep 2 digits
    total = total.toFixed(2);
    //or can also
    //total = Math.round(total * 100) / 100;

    totalElement.innerHTML = "$" + total;

    console.log("finish--------updatetotal");
}

function clearCart() {
    console.log("start===========clearCart===========");

    const cartContent = cart.querySelector(".cart-content");
    cartContent.innerHTML = '';//清空購物車
    itemsAdded = [];//避免送出後重新加購物車出錯
    myMap.clear();
    update(); 7
    console.log("finish===========clearCart===========");
}
// ============HTML Components============
function CartBoxComponent(title, price, img, itemId) {
    return `
    <div class="cart-box">
        <img src=${img} alt="" class="cart-img">
        <div class="detail-box" id="${itemId}">
            <div class="cart-product-title">${title}</div>
            <div class="cart-price">${price}</div>
            <input type="number" value="1" class="cart-quantity">
        </div>
        <!-- remove cart -->
        <i class='bx bx-trash cart-remove' ></i>
    </div>`;
}

