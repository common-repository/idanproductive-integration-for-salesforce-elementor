=== Elemetix - Integration for Salesforce and Elementor ===
Contributors: idanproductive
Tags: elementor forms, elementor addon,  salesforce
Requires PHP: 7.4
Requires at least: 3.5
Tested up to: 6.6.1
Stable tag: 1.0.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

### IMP : ELEMENTOR IS REQUIRED FOR THIS PLUGIN.

 Upon successful activation of the plugin, you will see in the admin dashboard side menu, *Elemetix* as part of the menu
- Click on this menu to access the settings page.
- Enter your salesforce instance url e.g https://my-website.my.salesforce.com, your salesforce username, password, consumer key and customer secret.
- Save settings.
- Next, configure the Login Table that you would like to use for the Login Widget.

- On successful authentication, Database table will be populated with data from your salesforce instance

# Dependencies
This plugin relies on and uses salesforce API to connect and fetch data. It uses the `/services/oauth2/token`, `/services/data/v55.0/sobjects/` and other endpoints to 
login, get an access token and fetch data from the connected salesforce instance.
Visit [The Salesforce Developer Homepage](https://developer.salesforce.com/) to learn more.
Visit [Salesforce Terms of use](https://www.salesforce.com/company/program-agreement/) and [Privacy Policy](https://www.salesforce.com/company/privacy/)

# Billing
We save usage data including your salesforce username and request type to our servers for pricing and billing. Read about our [privacy policy](https://docs.google.com/document/d/1li649PBtvayQ4Kor1xJXjmYUbFKE5WwRQPEG9Du0KaE/edit) or visit our [homepage](https://sfusers.productivedev.co/plugin)

= 1.0.1 - 10/12/2023 =
*Fix: Minor bug fixes

= 1.0.2 - 15/12/2023 =
* Fix: Broken login and OTP widget

= 1.0.3 - 08/01/2024 =
* Fix: Minor bug fixes
* New: Reports widget

= 1.0.4 - 29/08/2024
* New: Fileds Widget
* Improved: Salesforce Addon is now Table View Widget
* Improved: Orders card widget
* Improved: Reports Widget
* Improved Loading of salesforce data
* Fix: Minor bug fixes
