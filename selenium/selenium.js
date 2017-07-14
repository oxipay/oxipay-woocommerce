var webdriver = require('selenium-webdriver'),
    By = webdriver.By,
    until = webdriver.until;

var driver = new webdriver.Builder()
    .forBrowser('opera')
    .build();

driver.manage().timeouts().implicitlyWait(10000);
	
driver.get('http://54.252.165.134/?post_type=product');			// 10 Seconds

/* Checkout first product on WooCommerce */ 

driver.findElement(By.css('.first')).click();
driver.findElement(By.css('button.single_add_to_cart_button')).click();
driver.findElement(By.linkText('View cart')).click();

// Ensure Cart total is above Oxipay minimum
checkoutBelowMinimum = driver.findElement(By.css('.order-total .woocommerce-Price-amount')).getText().then(
	function(text) {
		const MINIMUM_FOR_OXIPAY = 20.00;								// Minimum checkout value allowed by Oxipay
		var checkoutS = text.toString();								// Checkout in String format
		var checkoutF = parseFloat(checkoutS.slice(1));			// Checkout total prior to logic execution below
		var itemPriceF = checkoutF;								// Price of item being added to checkout

		if (checkoutF < MINIMUM_FOR_OXIPAY) {
			// Division greater than one if checkout total is less that the minimum
			while ((MINIMUM_FOR_OXIPAY/checkoutF) > 1.00) {
				driver.navigate().back();
				driver.findElement(By.css('button.single_add_to_cart_button')).click();
				driver.findElement(By.linkText('View cart')).click();
				checkoutF = checkoutF+itemPriceF;
			}
		}
	}
);

driver.findElement(By.linkText('Proceed to checkout')).click();


// Filling out checkout page
driver.findElement(By.id('billing_first_name')).sendKeys('Sam');
driver.findElement(By.id('billing_last_name')).sendKeys('Al-Khalfa');
driver.findElement(By.id('billing_company')).sendKeys('Certegy Ezi-Pay');

// TO-DO: Support for NZ addresses

driver.findElement(By.id('billing_address_1')).sendKeys('97 Pirie St');
driver.findElement(By.id('billing_address_2')).sendKeys('Level 6');
driver.findElement(By.id('billing_city')).sendKeys('Certegy Ezi-Pay');



driver.findElement(By.id('select2-billing_state-container')).click();
driver.findElement(By.css('.select2-search__field')).sendKeys('Queensland');
driver.findElement(By.css('.select2-search__field')).sendKeys(webdriver.Key.ENTER);


driver.findElement(By.id('billing_postcode')).sendKeys('5000');
driver.findElement(By.id('billing_phone')).sendKeys('0407229128');
driver.findElement(By.id('billing_email')).sendKeys('Sam.Al-Khalfa@certegy.com.au');
driver.findElement(By.css('.wc_payment_method.payment_method_oxipay')).click();

driver.findElement(By.id('place_order')).click();

/* Checkout on Oxipay SPA */ 
driver.findElement(By.id('identity')).clear();
driver.findElement(By.id('identity')).sendKeys('0407229128');
driver.findElement(By.id('password')).sendKeys('Password1');
driver.findElement(By.css('.btn-primary')).click();


var user = driver.wait(until.elementLocated(By.css('.btn-default')), 10);
user.click();



/*
agreeButtonPath = '//*[@id="confirm-modal-wrapper"]/div/div/div/div/button';
//Following snippet is stated for making the driver wait till the element is visble.
driver.wait(function() 
{
   return driver.isElementPresent(By.xpath(agreeButtonPath));
}, 10*1000);
driver.findElement(By.xpath(agreeButtonPath)).then(function(elem)
{
    elem.isDisplayed().then(function(stat){
        driver.findElement(By.css('.btn-default')).click();
    });
});




*/



/*



return driver.wait(until.elementLocated(By.css('#confirm-modal-wrapper > div > div > div > div > button')), 5 * 1000).then(el => {
    return el.click();
});

driver.findElement(By.css('')).click();
driver.findElement(By.css('form-input > div:nth-child(2) > input')).click();








*/

























//driver.quit();
