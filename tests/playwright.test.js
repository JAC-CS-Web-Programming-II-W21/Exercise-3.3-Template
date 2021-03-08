const { chromium } = require('playwright-chromium');

let browser;
let page;

beforeAll(async () => {
	browser = await chromium.launch({
		headless: false,
		slowMo: 200,
	});
});

afterAll(async () => {
	await browser.close();
});

beforeEach(async () => {
	page = await browser.newPage();
});

afterEach(async () => {
	await page.close();
});

test('Playwright should work.', async () => {
	// Navigate to a webpage.
	await page.goto('https://www.google.com/');

	// Check the title of the webpage.
	expect(await page.title()).toBe('Google');

	// Type in something into a text box.
	await page.type('.gLFyf', ''); // Type your search term in the second argument.

	// CLick on the submit button.
	await page.click('.gNO89b');

	await page.waitForTimeout(5000);
});
