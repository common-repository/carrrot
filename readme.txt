=== Carrrot ===
Contributors: carrrot
Tags: carrrot, woocommerce, customer tracking, abandoned carts, lead collection, forms, live chat
Requires at least: 4.2.0
Tested up to: 4.9.8
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

[Carrrot](https://www.carrrot.io/?utm_source=woocommerce) combines all instruments for marketing automation, sales and communications. Supports WooCommerce 3.x.

== Description ==
[Carrrot](http://www.carrrot.io/?utm_source=woocommerce) is a tool for your online-store growth. See every visitor of your store. Increase conversions into purchase with retention emails and engagement pop-up forms. And support your customers 24/7 with a live chat.

1. Carrrot tracks real-time customer information (names, emails, phone numbers, viewed products, shopping cart, orders).
2. All information about each customer is stored in the eCRM
3. Carrrot uses the customer data to:
	* Collect leads showing pop-up forms with discount and offers
	* Convert visitors into purchase via personalized chat messages and pop-ups
	* Retain them via sending emails with abandoned carts or recently viewed products
	* Analyze your campaigns efficiency with funnel tracking
4. All communications with customers are combined in one interface. Just add your Facebook page, Mail address, Telegram bot or Viber Public Account

Service provides detailed analytics for all those communications.
As a result we make 30% more additional sales by automated scenarios.


	**Case example:**
	The volume of visitors to the website in a month = 54495 unique visitors

	**Before:**
	Old conversion rate = 1.62%
	Old number of orders per month = 884
	Old revenue = $30 690

	**After:**
	New conversion rate = 1.83%
	New number of orders per month = 996
	New revenue = $36 153

	**Result:** Company revenue increased by 15.09%


[Carrrot](https://www.carrrot.io/?utm_source=woocommerce) won’t lose contact with each of your customers, that helps retail them for the repeat purchase.
* Integrations with popular services (Analytics + Zapier);
* Enriching customer profiles with social networks information (not fishing, only open data);
* Live chat and email are connected to the chat with customers in an easier and quicker way;
* etc.

P.S.
Service has many cool features, which cannot be described in one paragraph. You should try them out.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/carrrot` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Open Settings->Carrrot and set API Key and API Secret values

== Frequently Asked Questions ==

= Where can I get API Key, API Secret and User Auth Key? =

You have to register at [Carrrot](https://www.carrrot.io/?utm_source=woocommerce) then open "Settings" -> "API Keys" and copy needed values.

= What properties and events this plugin collects? =

Events:
* Viewed product:
	* Product name;
	* Product description page link;
	* Product price. Integer;
	* Product image link.
* Added product to cart:
	* Product name;
	* Product description page link;
	* Product price. Integer;
	* Product image link.
* Viewed shopping cart:
	* Products names list;
	* Products description pages links list;
	* Products costs (integer) list;
	* Products image link.
* Started checkout process
* Completed checkout process:
	* Order ID;
	* Order total.

Events, occuring when user authorization is on and order was made by an authorized user:
* Order paid (when status changed to Completed):
	* Order ID;
	* Items;
	* Order total.
* Order refunded:
    * Order ID;
	* Items.
* Order cancelled:
    * Order ID;
	* Items.


Properties:
* Shopping cart total (integer) – updated when cart is viewed or product added to cart
* Viewed products (list of products names) – updated when product is viewed
* Shopping cart (list of product names) - updated when cart is viewed or product added to cart
* Last payment (integer) – updated when order is made
* Total revenue from user (integer, sum of all order totals) – updated when order is made
* Customers name – updated when order is made
* Customers email address – updated when order is made or email address were written in any input field 
* Customers phone number – updated when order is made
* Last order status - updated when user authorization is on and order status was changed


== Screenshots ==

1. Streamline your work in one tool instead of many plugins
2. Keep track the visitors on every step of the funnel and nurture and retain them in real time
3. These are the main features that from complex tool to grow your online sales
4. Make your customers happier and increase their lifetime with multichannel communication
5. Segment your leads by their events and attributes to make up-sales and retain particular customers with personal offers
6. Measure the campaigns performance and choose only profitable
7. Compare the type and content of your messages to find the best that lead to sales
8. Learn how useful your support team is. Track the response time, unanswered and done questions

== Changelog ==

= 1.0.0 =

* First release

= 1.1.0 =

* Authorize users (send User ID to Carrrot)
* Track orders status changes
* Minor bug-fixes