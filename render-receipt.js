const puppeteer = require("puppeteer");

const orderId = process.argv[2];

(async () => {
    const browser = await puppeteer.launch();
    const page = await browser.newPage();

    await page.goto(`http://127.0.0.1:8000/prints/receipt/${orderId}`, {
        waitUntil: "networkidle0",
    });

    const receipt = await page.$("#receipt");
    const buffer = await receipt.screenshot({ type: "png" });

    await browser.close();

    // 🔴 THIS IS REQUIRED
    process.stdout.write(buffer);
})();
