const { app, BrowserWindow } = require("electron");

function createWindow() {
    const win = new BrowserWindow({
        width: 400,
        height: 600,
        show: false, // hidden window
    });

    // 🔴 LOAD LARAVEL ROUTE
    win.loadURL("http://127.0.0.1:8000/test-script");

    win.webContents.on("did-finish-load", () => {
        win.webContents.print(
            {
                silent: true,
                printBackground: true,
            },
            () => {
                app.quit();
            }
        );
    });
}

app.whenReady().then(createWindow);
