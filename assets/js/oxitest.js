var redirectUrl = '?x_reference=72&x_account_id=30199250&x_amount=702.00&x_currency=AUD&x_url_callback=aHR0cDovL2xvY2FsaG9zdDo4MDgwLz9wYWdlX2lkPTI5Jm9yZGVyLXJlY2VpdmVkPTcyJmtleT13Y19vcmRlcl81OTY3MTIwMzUxYWRl&x_url_complete=aHR0cDovL2xvY2FsaG9zdDo4MDgwLz9wYWdlX2lkPTI5Jm9yZGVyLXJlY2VpdmVkPTcyJmtleT13Y19vcmRlcl81OTY3MTIwMzUxYWRl&x_url_cancel=aHR0cDovL2xvY2FsaG9zdDo4MDgwLz9wYWdlX2lkPTI4JmNhbmNlbF9vcmRlcj10cnVlJm9yZGVyPXdjX29yZGVyXzU5NjcxMjAzNTFhZGUmb3JkZXJfaWQ9NzImcmVkaXJlY3QmX3dwbm9uY2U9MjVmZDhiZjM3Mg%3D%3D&x_test=false&x_shop_country=AU&x_shop_name=&x_customer_first_name=Andrew&x_customer_last_name=Mason&x_customer_email=andrew.mason%40oxipay.com.au&x_customer_phone=04077778432&x_customer_billing_country=AU&x_customer_billing_city=1234&x_customer_billing_address1=234234&x_customer_billing_address2=&x_customer_billing_state=SA&x_customer_billing_zip=3214&x_customer_shipping_country=AU&x_customer_shipping_city=1234&x_customer_shipping_address1=234234&x_customer_shipping_address2=&x_customer_shipping_state=SA&x_customer_shipping_zip=3214&gateway_url=aHR0cHM6Ly9zZWN1cmV0ZXN0Lm94aXBheS5jb20uYXUvQ2hlY2tvdXQ%2FcGxhdGZvcm09V29vQ29tbWVyY2U%3D&x_signature=bc88c3a8822f1a736fa4b01d2c6788626ccdc054a16b0c11e087ec6b74a96306';
redirectUrl = redirectUrl.substring(redirectUrl.indexOf('?') + 1);

function extractKeys(redirectUrl) {

    var keyArr = redirectUrl.split('&');

    var keys = {};
    for (var i = 0; i < keyArr.length; i++) {
        var split = keyArr[i].split('=');
        keys[split[0].trim()] = split[1].trim();
    }
    return keys;
}

var response = extractKeys(redirectUrl);
console.log(response);