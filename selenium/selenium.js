var webdriver = require('selenium-webdriver'),
    By = webdriver.By,
    until = webdriver.until;

var driver = new webdriver.Builder()
    .forBrowser('opera')
    .build();
	
const OXIPAY_MINIMUM = 20.00f; // Minimum checkout value allowed by Oxipay
	
driver.get('http://54.252.165.134/?post_type=product');

/* Checkout first product on WooCommerce */ 

driver.findElement(By.css('.first')).click();
driver.findElement(By.css('button.single_add_to_cart_button')).click();
driver.findElement(By.linkText('View cart')).click();

// Ensure Cart total is above $20
var cartTotal; 			// Current Checkout Total including $ sign




cartTotal = driver.findElement(By.css('.order-total .woocommerce-Price-amount')).getText().then(
	function(text) {
		var priceString = text.toString();
		var priceFloat = parseFloat(priceString.slice(1, -1)).toFixed(2);
		priceFloat2 = priceFloat;
		return priceFloat;
	}
);

var priceFloat2 = cartTotal;


	while ( priceFloat2 < OXIPAY_MINIMUM ) {
		priceFloat++;
		/*	
		driver.navigate().back();
		driver.navigate().back();
		driver.findElement(By.css('button.single_add_to_cart_button')).click();
		driver.findElement(By.linkText('View cart')).click();
		priceFloat++;

		driver.findElement(By.css('button.single_add_to_cart_button')).click();
		driver.findElement(By.linkText('View cart')).click();
		cartTotalValue = driver.findElement(By.css('.woocommerce-Price-amount')).getText();
	*/
	}


driver.findElement(By.linkText('Proceed to checkout')).click();


// Filling out checkout page
driver.findElement(By.id('billing_first_name')).sendKeys(priceFloat2);
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

driver.wait(function () {
	driver.findElement(By.id('identity')).sendKeys('0407229128');
}, 3000);

driver.findElement(By.id('password')).sendKeys('Password1');



/*

driver.getTitle().then(function(title) {
  console.log('Page title is: ' + title);
});

driver.wait(function() {
  return driver.getTitle().then(function(title) {
    return title.toLowerCase().lastIndexOf('cheese!', 0) === 0;
  });
}, 3000);

driver.getTitle().then(function(title) {
  console.log('Page title is: ' + title);
});



driver.quit();

*/