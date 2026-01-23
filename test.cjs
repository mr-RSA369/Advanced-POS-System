const puppeteer = require("puppeteer");

(async () => {
    const browser = await puppeteer.launch();
    const page = await browser.newPage();

    await page.goto("http://127.0.0.1:8000/test-receipt", {
        waitUntil: "networkidle0", // IMPORTANT
    });

    // 🔴 Wait until the element REALLY exists
    await page.waitForFunction(
        () => {
            return document.querySelector("#simple");
        },
        { timeout: 15000 }
    );

    const div = await page.$("#simple");

    await div.screenshot({
        path: "simple.png",
    });

    await browser.close();
})();
