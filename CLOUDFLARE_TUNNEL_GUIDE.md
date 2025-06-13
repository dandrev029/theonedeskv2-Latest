# Guide: Exposing Your Local Laravel App with Cloudflare Tunnel

This guide will walk you through the process of making your local Laravel application, running on `http://localhost:8000`, publicly accessible via your custom domain, `theonedesk.site`.

### **Prerequisites:**

*   A Cloudflare account.
*   A Namecheap account with the registered domain `theonedesk.site`.
*   Your Laravel application is ready and can be run locally.

---

### **Step 1: Add Your Domain to Cloudflare and Update Nameservers**

First, you need to let Cloudflare manage your domain's DNS.

1.  **Add Domain to Cloudflare:**
    *   Log in to your Cloudflare account.
    *   Click the "**+ Add a Site**" button and enter `theonedesk.site`.
    *   Choose the **Free** plan when prompted.
    *   Cloudflare will scan your existing DNS records. It's okay if it doesn't find any. Click **Continue**.

2.  **Update Nameservers at Namecheap:**
    *   Cloudflare will provide you with two nameservers (e.g., `sue.ns.cloudflare.com` and `tom.ns.cloudflare.com`). Copy these.
    *   Log in to your **Namecheap** account.
    *   Go to your **Domain List**, find `theonedesk.site`, and click "**Manage**".
    *   In the "**NAMESERVERS**" section, select "**Custom DNS**".
    *   Paste the two nameservers you copied from Cloudflare into the fields provided and save your changes.

    > **Note:** DNS propagation can take anywhere from a few minutes to 24 hours. You can proceed with the next steps while you wait. Cloudflare will email you once your site is active.

---

### **Step 2: Install and Authenticate `cloudflared`**

`cloudflared` is the command-line tool that creates the tunnel.

1.  **Download `cloudflared`:**
    *   Download the appropriate version for your operating system from the [Cloudflare Zero Trust downloads page](https://one.dash.cloudflare.com/downloads). For Windows, you'll download the 64-bit `.msi` installer.

2.  **Install `cloudflared`:**
    *   Run the downloaded installer. This will add `cloudflared` to your system's PATH, making it accessible from any terminal.

3.  **Authenticate with Cloudflare:**
    *   Open a new terminal (Command Prompt, PowerShell, or Git Bash).
    *   Run the following command:
        ```bash
        cloudflared tunnel login
        ```
    *   This command will open a browser window asking you to log in to your Cloudflare account and authorize the tunnel for your domain (`theonedesk.site`). Once you've authorized it, you'll see a success page.

---

### **Step 3: Create a Persistent Cloudflare Tunnel**

A persistent tunnel is a named tunnel that you can reuse.

1.  **Create the Tunnel:**
    *   In your terminal, run the following command to create a tunnel named `theonedesk-tunnel`:
        ```bash
        cloudflared tunnel create theonedesk-tunnel
        ```
    *   This command will generate a **credentials file** (e.g., `a-long-guid.json`) in your user's `.cloudflared` directory (e.g., `C:\Users\YourUser\.cloudflared\`). This file authenticates your tunnel.

---

### **Step 4: Configure the Tunnel**

Create a configuration file to tell the tunnel where to send traffic.

1.  **Create `config.yml`:**
    *   In a location of your choice (e.g., your project directory `c:/xampp/htdocs/theonedeskv2`), create a new file named `config.yml`.

2.  **Add the Configuration:**
    *   Open `config.yml` and add the following content. This configuration tells `cloudflared` to route traffic for `theonedesk.site` to your local server at `http://localhost:8000`.

    ```yaml
    tunnel: theonedesk-tunnel
    credentials-file: C:\Users\YourUser\.cloudflared\a-long-guid.json
    ingress:
      - hostname: theonedesk.site
        service: http://localhost:8000
      - service: http_status:404
    ```

    *   **Important:**
        *   Replace `theonedesk-tunnel` with your tunnel's name if you chose a different one.
        *   Replace the `credentials-file` path with the actual path to your `.json` credentials file.
        *   The `service: http_status:404` line is a catch-all to prevent the tunnel from exposing other services.

---

### **Step 5: Set Up DNS Records**

Point your domain to the tunnel so Cloudflare knows where to send the traffic.

1.  **Route DNS Automatically:**
    *   The easiest way is to let `cloudflared` create the CNAME record for you. Run this command:
        ```bash
        cloudflared tunnel route dns theonedesk-tunnel theonedesk.site
        ```
    *   This will create a CNAME record in your Cloudflare DNS settings, pointing `theonedesk.site` to your tunnel's unique ID.

---

### **Step 6: Start Your Local Server and the Tunnel**

Now, it's time to bring everything online.

1.  **Start Your Laravel App:**
    *   In your terminal, navigate to your project directory (`c:/xampp/htdocs/theonedeskv2`) and start the local server:
        ```bash
        php artisan serve
        ```
    *   Keep this terminal window open.

2.  **Run the Cloudflare Tunnel:**
    *   Open a **new** terminal window.
    *   Navigate to the directory where you saved `config.yml`.
    *   Run the tunnel with your configuration:
        ```bash
        cloudflared tunnel --config config.yml run theonedesk-tunnel
        ```
    *   If successful, you will see output indicating that the tunnel is running and connected. Keep this terminal open as well.

---

### **Step 7: Verification**

Your local application should now be accessible to the public.

*   Open any web browser on any device and navigate to:
    **`https://theonedesk.site`**

You should see your local Laravel application being served securely over HTTPS.

***

This concludes the setup. As long as your local server and the `cloudflared` tunnel command are running, your application will remain publicly accessible.
