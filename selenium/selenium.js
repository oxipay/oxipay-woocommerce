var webdriver = require('selenium-webdriver'),
    By = webdriver.By,
    until = webdriver.until;

var driver = new webdriver.Builder()
    .forBrowser('firefox')
    .build();

driver.manage().window().maximize();
driver.navigate().to("http://54.252.164.59/");
driver.manage().timeouts().implicitlyWait(5000);			// 5 Seconds

/* Checkout first product on WooCommerce */ 
driver.findElement(By.css('.first')).click();
driver.findElement(By.linkText("Add to cart")).click();
driver.findElement(By.linkText('View cart')).click();

// Ensure Cart total is above Oxipay minimum
checkoutBelowMinimum = driver.findElement(By.css('.order-total .woocommerce-Price-amount')).getText().then(
	function(text) {
		const MINIMUM_FOR_OXIPAY = 20.00;								// Minimum checkout value allowed by Oxipay
		var checkoutS = text.toString();								// Checkout in String format
		var checkoutF = parseFloat(checkoutS.slice(1));					// Checkout total prior to logic execution below in Floating format
		var itemPriceF = checkoutF;										// Price of item being added to checkout

		if (checkoutF < MINIMUM_FOR_OXIPAY) {
			// Division result is greater than one if checkout total is less that the minimum
			while ((MINIMUM_FOR_OXIPAY/checkoutF) > 1.00) {
				driver.navigate().back();
				driver.findElement(By.css('button.single_add_to_cart_button')).click();
				driver.findElement(By.linkText('View cart')).click();
				checkoutF = checkoutF+itemPriceF;
			}
		}
	}
);

var woocommerceCheckout = [
	['#billing_first_name', 'Sameer'],
	['#billing_last_name', 'Al-Khalfa'],
	['#billing_company', 'Certegy Ezi-Pay'],
	['#billing_address_1', '97 Pirie St'],
	['#billing_address_2', 'Level 6'],
	['#billing_city', 'Certegy Ezi-Pay'],
	['.select2-search__field', 'Queensland'],
	['.select2-search__field', 'webdriver.Key.ENTER'],
	['#billing_postcode','5000'],
	['#billing_phone','0407229128'],
	['#billing_email', 'Sam.Al-Khalfa@certegy.com.au']
]

for (var i = 0; i < woocommerceCheckout.length; i++) {
	for (var j = 0; j < woocommerceCheckout[i].length; j++) {
		console.log(woocommerceCheckout[i][j]);
	}
}

function sendKeysBySelector (selector, value) {
	driver.findElement(By.cssSelector(selector).sendKeys(value));
}

function dropdownSelector (selector, value) {
	driver.findElement(By.id('select2-billing_state-container')).click();
	driver.findElement(By.css('.select2-search__field')).sendKeys('Queensland');
	driver.findElement(By.css('.select2-search__field')).sendKeys(webdriver.Key.ENTER);
}

['.select2-search__field', 'Queensland'],
['.select2-search__field', 'webdriver.Key.ENTER'],

driver.findElement(By.linkText('Proceed to checkout')).click();

// Filling out checkout page
driver.findElement(By.id('billing_first_name')).sendKeys('');
driver.findElement(By.id('billing_last_name')).sendKeys('');
driver.findElement(By.id('billing_company')).sendKeys('Certegy Ezi-Pay');

// TO-DO: Support for NZ addresses

driver.findElement(By.id('billing_address_1')).sendKeys('97 Pirie St');
driver.findElement(By.id('billing_address_2')).sendKeys('Level 6');
driver.findElement(By.id('billing_city')).sendKeys('Certegy Ezi-Pay');



driver.findElement(By.id('select2-billing_state-container')).click();
driver.findElement(By.css('.select2-search__field')).sendKeys('Queensland');
driver.findElement(By.css('.select2-search__field')).sendKeys(webdriver.Key.ENTER);


driver.findElement(By.id('billing_postcode')).sendKeys('5000');
driver.findElement(By.id('billing_phone')).sendKeys('');
driver.findElement(By.id('billing_email')).sendKeys('');
driver.findElement(By.css('.wc_payment_method.payment_method_oxipay')).click();

driver.findElement(By.id('place_order')).click();

/* Checkout on Oxipay SPA */ 
driver.findElement(By.id('identity')).clear();
driver.findElement(By.id('identity')).sendKeys('');
driver.findElement(By.id('password')).sendKeys('');
driver.findElement(By.css('.btn-primary')).click();

driver.wait(until.elementIsVisible(driver.findElement(By.css('.btn-default')), 10000));
driver.findElement(By.css('.btn-default')).click();


driver.findElement(By.css('form-input > div:nth-child(2) > input')).sendKeys('123');

// Waits until the "I Agree" modal is not in the DOM
driver.wait(until.stalenessOf(driver.findElement(By.css('.modal-backdrop')), 10000));

// Select the 2 checkboxes using executeScript to avoid "Element is not clickable at point ...." errors
var termsCheckbox 	= driver.findElement(By.css('form > section:nth-child(3) > div:nth-child(1) > checkbox > div > label > input'));
var policyCheckbox 	= driver.findElement(By.css('form > section:nth-child(3) > div:nth-child(2) > checkbox > div > label > input'));

driver.executeScript("arguments[0].click();", termsCheckbox);
driver.executeScript("arguments[0].click();", policyCheckbox);

// Click on "Confirm"
driver.findElement(By.css('.btn-primary')).click();

//Optional to end the script
//driver.quit();
