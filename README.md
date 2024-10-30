# Elemetix - Integration for Salesforce and Elementor
This is an elementor addon plugin to connect salesforce to wordpress

# Usage

- Clone or download the repo
- Change folder name to 'productive-salesforce-elementor-integration'
- Zip folder.
- In your WordPress admin dashboard, click plugins and then add new.
- Upload the zip folder you just created
- After successful upload, click activate to activate plugin
- After activation, you will see in the admin dashboard side menu, *Elemetix* as part of the menu
- Click on this menu to access the settings page.
- Enter your salesforce instance url e.g https://my-website.my.salesforce.com, your salesforce username, password, consumer key and customer secret.
- Save settings.
- On successful authentication, Database table will be populated with data from your salesforce instance

# Dependencies
This plugin relies on and uses salesforce API to connect and fetch data. It uses the `/services/oauth2/token`, `/services/data/v55.0/sobjects/` and other endpoints to 
login, get an access token and fetch data from the connected salesforce instance.
Visit [The Salesforce Developer Homepage](https://developer.salesforce.com/) to learn more.
Visit [Salesforce Terms of use](https://www.salesforce.com/company/program-agreement/) and [Privacy Policy](https://www.salesforce.com/company/privacy/)
