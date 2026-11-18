# Putting the dooo store online with cPanel

This guide takes you from the zip file to a live store with a working admin dashboard. It takes about 15 minutes. You don't need SSH or any command line.

**What you need:**
- A cPanel hosting account with **PHP 8.2 or newer**
- The file **`dooo-store-cpanel.zip`**. Keep it private: it contains your app's secret key.

---

## Step 1: Choose PHP 8.2+

1. In cPanel, open **MultiPHP Manager** (or **Select PHP Version**).
2. Set your domain to **PHP 8.2** or **8.3**.
3. If you see an extensions list, make sure these are ticked: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `tokenizer`, `xml`, `ctype`, `curl`.

## Step 2: Create the database

1. Open **MySQL® Database Wizard**.
2. Create a database, for example `dooo_store`. cPanel adds a prefix, so it becomes something like `cpuser_dooo_store`.
3. Create a database user with a strong password.
4. On the privileges screen, tick **ALL PRIVILEGES**, then click **Next step**.
5. Write down the **full** database name, user name and password.

## Step 3: Upload the files

Pick **one** of these two layouts.

### Option A (recommended): a folder outside public_html
Use this if you can set the domain's document root. It works for addon domains and subdomains, and in most cPanel versions also for the main domain under **Domains › Manage**.

1. In **File Manager**, open your home folder (the one *above* `public_html`).
2. Upload `dooo-store-cpanel.zip` there, right-click it and choose **Extract**. You now have `/home/cpuser/dooo-store/`.
3. Go to **Domains**, click **Manage** next to your domain, and set the **Document Root** to `dooo-store/public`.

### Option B: everything inside public_html
Use this if you can't change the document root.

1. Open `public_html` in File Manager and delete or move away any old site files (keep `.well-known` if it exists).
2. Upload `dooo-store-cpanel.zip` into `public_html` and **Extract** it.
3. The zip extracts into a `dooo-store` folder. Open it, **select all** files (turn on *Settings › Show Hidden Files* first so `.env` and `.htaccess` are included) and **Move** them up into `public_html`. Then delete the empty `dooo-store` folder.

The included `.htaccess` sends visitors to the `public` folder and blocks access to `.env`, the app source code, logs and `vendor`.

## Step 4: Enter your database details

1. In File Manager, open the project folder (`dooo-store`, or `public_html` for option B).
2. Right-click **`.env`** and choose **Edit**. If you can't see it, enable *Show Hidden Files*.
3. Fill in these lines:

```
APP_URL=https://www.yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=cpuser_dooo_store
DB_USERNAME=cpuser_dooo_user
DB_PASSWORD=your-database-password
```

4. Click **Save**. Don't change `APP_KEY`. It's already set.

## Step 5: Run the installer

1. Visit **`https://www.yourdomain.com/install`**.
2. Every item in the server check should show a green tick. If the database line is red, re-check Step 4.
3. Enter **your name, email and password**. These become your admin login.
4. Click **Install store**. The installer creates the tables, loads all 26 products, the 5 categories and your store settings, then signs you in.

The installer then locks itself and can never run again.

## Step 6: Done

| What | Where |
|---|---|
| Your shop | `https://www.yourdomain.com/` |
| Admin dashboard | `https://www.yourdomain.com/admin` |

**First things to do in the admin:**
- **Settings**: check your WhatsApp number, phone, email, address, social links and announcement bar.
- **Products**: fill in prices for items marked *Price on request* and upload new photos.
- **Team**: add staff accounts. Staff can't change settings or the team.

**Turn on HTTPS:** in cPanel open **SSL/TLS Status** and run **AutoSSL** for your domain. The store works on both `http` and `https`, but customers should always see the padlock.

---

## How orders work

1. A customer adds items to the bag and checks out (cash on delivery, no card needed).
2. The order is **saved in your admin panel** under **Orders** with status *Pending*, and WhatsApp opens with the order details so the customer can send them to you.
3. You confirm with the customer, then update the order: set the price for any *price on request* item, add the delivery fee, change the status (*Confirmed → Processing → Shipped → Delivered*) and add the courier tracking number.
4. Print the **Invoice** to put in the parcel. Use **Export CSV** for your accounts.

Contact-form messages appear under **Messages**, with a one-click **Reply on WhatsApp** button.

---

## Troubleshooting

| Problem | Fix |
|---|---|
| **500 error / blank page** | Make sure PHP is 8.2+ (Step 1). Check that the folders `storage` and `bootstrap/cache` have permission **755** (right-click › Change Permissions). See `storage/logs/laravel.log` for the exact error. |
| **"Cannot connect to the database"** on /install | The database name and user must include the cPanel prefix (`cpuser_…`), and the user must be added to the database with ALL PRIVILEGES. `DB_HOST` is normally `localhost`. |
| **Page expired (419)** after a long time on a form | Refresh the page and submit again. This is a normal security timeout. |
| **Images don't upload** | Make sure `public/uploads/products` exists and has permission **755**. The max size is 5 MB. Your host's PHP `upload_max_filesize` must be at least 6M (cPanel › Select PHP Version › Options). |
| **Styles look old after an update** | In `.env`, change `ASSET_VERSION=1` to `2` so browsers load the new files. |
| **Forgot admin password** | Ask another owner or admin to reset it under **Team**. If you are the only owner, contact your developer. |
| **Want to see detailed errors temporarily** | Set `APP_DEBUG=true` in `.env`, reproduce the problem, then set it back to `false` straight away. Never leave it on. |

## Updating the site later

Upload the changed files over the old ones. Never overwrite `.env`, `storage/` or `public/uploads/`, because those hold your settings, logs and product photos. If an update includes new database changes, ask your developer, or run `php artisan migrate --force` from cPanel **Terminal** if you have it.

## Backups

- **Database:** cPanel › **Backup** › *Download a MySQL Database Backup*, or use phpMyAdmin › Export.
- **Photos:** download the `public/uploads` folder.

Do both regularly, for example weekly.
