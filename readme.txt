=== WooCommerce POS ===
Contributors: Webkul
Version: 5.0.0
Requires at least: 5.0
Tested up to: 6.3
Stable tag: 5.0.0
Requires PHP: 7.3
Tested up to PHP: 8.1
WC requires at least: 6.0
WC tested up to: 7.9

Tags: woocommerce, woo point of sale, pos, webkul, woo pos, woocommerce point of sale, woocommerce pos

License: For more info see license.txt included with plugin.
License URI: https://store.webkul.com/license.html

=== Description

WooCommerce Point of Sale System provides store owners to have the functionality, where they can easily manage their online stores along with the physical stores.
The physical stores will have a POS front-end management system for which the admin can assign a store agent who can easily manage the sales and customers at that particular store.

The POS system can work both online and offline with the feature to synchronize all the sales and customer data of the offline store with the online store whenever going online.
The module plays a vital role in increasing the day-to-day sales by having a management system to manage online stores along with the physical stores.

=== Features
	* Admin can create multiple stores outlets for his/her online store.
	* Admin can create agents for the outlets to manage sales and customers at their end.
	* POS agents can add new customers from their panel.
	* Offline mode for physical stores to work offline in case of poor connectivity.
	* POS agent can open drawer amount for current day and can close the drawer.
	* POS agent can synchronize the data with the online store whenever going online.
	* Effective sales and inventory management system for the admin.
	* Barcode readers can be used to add products to the cart.
	* Hassle-free payment and checkout system.
	* POS agent can apply a coupon to the cart.
	* POS agent can select the currency for the store.
	* POS agent can hold cart to process it after some time.
	* POS agent can see sales history, hold sales, offline sales from their panel.
	* POS agent can see current day sale history, current day cash and card amount from their panel.
	* POS agent can create custom product.
	* POS Full Screen Feature
	* Add Icon or webname on POS screen from POS settings.
	* Pin product feature through which POS agent can pin the product which will show on the top.


=== Installation

1. Upload the woo-point-of-sale folder to the /wp-content/plugins/ directory
2. Activate the plugin through the Plugins menu in WordPress
3. Configure the plugin using the Point of Sale menu

=== Frequently Asked Questions

===No questions asked yet ===

=== Feel free to do so. ===

For any Query please generate a ticket at [Webkul](https://webkul.com/ticket/)

=== 5.0.0 ===

Fixed: Discount apply validation.
Fixed: Show product meta option condition on order summary.
Fixed: Tax disable issue when the tax setup and tax is disabled.
Fixed: Add/Edit payment from admin for marketplace point of sale.
Fixed: Default customer set issue while updating default customer's data.
Fixed: Customer updated data render issue in customer list.
Enhancement: Seperated the POS general settings based on the settings category.
Enhancement: Improved coding standard of API and functionalities.
Enhancement: Seperated the POS master stock from the woocommerce stock.
Enhancement: Improved notices, errors, alerts popup.
Added: Integrated new report section on the POS and admin end.
Added: Pin product feature.
Added: Product out of stock visibility.
Added: Custom order prefix feature.
Added: Listed woocommerce orders on the POS.

=== 4.3.0 ===

Fixed: Multiple coupon and custom discount apply together.
Fixed: Real time update of pos user details after edit.
Fixed: Print invoice issue on IOS 16.3.
Fixed: Invoice printing issue if there is no invoice assinged for the outlet.
Fixed: Cross site scripting in input fields.
Fixed: Total price issues on summary and invoice page.
Fixed: Inclusive tax issue while creating order.
Fixed: Currency issue while using pos multicurrency plugin.
Fixed: Cashier amount calculation issue for different timezone.
Fixed: Cleared the search result and showed all products by clicking cancel button at the end of the search input.
Fixed: Console error while applying the coupon if there were no customer selected.
Fixed: Console error while printing invoice.
Fixed: Show and create order of products whose price is zero.
Fixed: Cart product price can be set to zero.
Enhancement: Added scroll bar on the header if lots of header menu/icons available.
Enhancement: Added title on the icons while hover on the icons.
Enhancement: Improved pos UI.
Enhancement: Reduced the timing for auto close popups.
Enhancement: Render product search input on only those components where it's need.
Added: Functionality to enable/disable opening drawer popup option for pos.
Added: Functionality to enable/disable print invoice option for pos.
Added: Feature for posuser to change their profile image.
Added: Added icons on admin setting tabs.
Added: Feature to make any customer to default customer for pos from admin end.
Added: Enable/Disable zero price products for the pos.


=== 4.2.0 ===

Fixed: Customer search issue by customer full/partial name.
Fixed: Order search issue by customer full/partial name and order status.
Fixed: Keyboard shortcut issue.
Fixed: Hold cart issue.
Fixed: Permalink flush issue on change endpoint.
Fixed: Admin report filter issue with other payment options like paypal, stripe etc.
Fixed: Offline order and receipt print issue.
Fixed: Time issue with different timezone.
Fixed: Invoice editing feature issue when outlet_country and outlet_postcode variable used.
Fixed: Offline mode issue while accessing other pages.
Fixed: Fixed default customer select issue on large customers list.
Fixed: Customer update issue when no space use in between customer first and last name.
Enhancement: Improved pos UI.
Enhancement: Updated barcode library to create barcode on admin end.
Enhancement: Updated invoice and added some hooks.
Added: Customer last name in customer list.
Added: Order status on pos order list.

=== 4.1.0 ===

1. Add Installation link in setup wizard.
2. Improve barcode add from SKU when POS got translated.
3. Modify the invoice products list table structure and improve the table structure.
4. Fix blank screen issue, while login on pos with other role's user like admin/customer.
5. Added shortcut keys for work via keyboard only.
6. Update customer when add hold cart.
7. POS drawer modify sync drawer with orders and remove the drawer date relations in APIs.
8. Added Light & dark mode setting in admin.
9. Added Light & dark mode switch button in pos panel.
10. Fixed order receipt print issue in safari browser.
11. Modify the admin end pos user add/edit form ui.
12. Modify the admin end outlet add/edit form ui.
13. Added responsiveness to admin end ui.
14. Added barcode configuration in pos settings.

=== 4.0.0 ===

1. Added payment information in cashier today sale section.
2. Added react setup wizard UI.
3. Added youtube POS installation link in setup wizard.
4. Added blog link in setup wizard.
5. Added product border configuration for square, circle, rounded.
6. Added POS Dynamic endpoint for access the pos panel.
7. Added support and services menu in pos admin menu.
8. Fixed screen option issue in tables.
9. Fixed tax issue with coupon and pos discount.

=== 3.6.4 ===

1. Added Total change in close outlet summary.
2. Added Direct product SKU barcode can be add to cart and search in POS.
3. Improved translations & localization of string.
4. Fixed drawer total cash wrong amount issue.
5. Fixed default customer form in setup wizard.
6. Fixed PWA app installation issue.
7. Fixed Tax issue with variation product.
8. Fixed add to cart issue with other language variations issue.

=== 3.6.3 ===

1. Dark Theme Feature.
2. add offline order sync button.
3. improve offline orders in POS.
4. printer setting at admin end for set default printer.
5. barcode print preview setting for portrait and landscape.
6. Order filtering according to the customer at POS end.
7. Add functionality for send the receipt manually via email after order completion.
8. Point of sale compatibility with all addons.
9. Add Extensions menu at admin end.
10. Improve and fixed common issues via client.
11. Add various hooks and filter in POS.



=== 3.6.2 ===

1. Dynamic UI for POS.
2. Use scss for css as per woocommerce.
3. Use code splitting for improving the app performance.
4. Improve POS Report section as per updated woocommerce reports.
5. Add Custom Report Filter in POS like woocommerce.
6. Enqueue Dependecies from woocommerce.
7. Resolved very coomon product fetching issue in POS.
8. Removed unused css and js.



=== 3.6.1 ===

1. Resolved report section issue.
2. Resolved POSUser deactivation issue.
3. Resolved GrandTotal issue when use Tax.
4. Resolved Payment deactivation issue.
5. Add Sync Button in Outlet for Syncing all variable product to POS screen.
6. Added new hooks at POS end.
7. Reduced the size of plugin zip.
8. Enqueued the dependencies from the WooCommerce plugin itself.



=== 3.6.0 ===

1. Added invoice templates.
2. Different invoice can be selected for different outlets.
3. Invoices are fully dynamic.
4. Added invoice API endpoint.
5. Added new hooks at POS end.
6. Updated the build-plugin-zip shell script for developer version which can be used with the command npm run build:release.
7. Resolved the payment option appearing issue at POS end.
8. Removed the external packages dependencies of WooCommerce Admin.
9. Enqueued the dependencies from the WooCommerce plugin itself.
10. Reduced the size of plugin zip.
11. Fixed other security issues.


=== 3.5.0 ===

1. Added reports at POS end.
2. Added some hooks at both PHP and JS end for addons.
3. Added translations in some static strings.
4. Fixed security issues.
6. Update custom product tax will now works as WooCommerce tax Setting
7. Added setting to price pridiction will increase amount on basis of setting amount.
8. Fixed printing issue in android device.
9. Fixed sku barcode generation issue.


=== 3.4.0 ===

1. Added routing at pos-end.
2. Added option in settings to enable/disable mails at pos-end.
3. Added 404 page for wrong URLS at pos-end.
4. Added feature to enable/disable offline order even if system is online for fast process.
5. Added feature to enable/disable all products in outlets by default.
6. Added feature to add custom payment methods at POS end.
7. Added feature to assign same outlet to multiple users.
8. Added translations with i18n in JavaScript.
9. Fixed security issues.


=== 3.3.1 ===

1. Added hooks in back-end and pos-end to modify data.
2. Change the layout of backend menus and settings page.
3. Added compatibility for apache_request_headers undefined issue.
4. Added setting in backend to enable the unit product price feature.
5. Updated namespace domain.
6. Removed unused codes.
7. Added prefix in some functions.
8. Added translations.
9. Fixed other security issues.


=== 3.3.0 ===

1. Added service worker for offline support.
2. Added Progressive Web App for POS.
3. Added admin end wizard to setup pos plugin.
4. Added csv importer for outlets.
5. Updated css for front-end for app.
6. Updated inline product price edit feature.
7. Fixed issue for card payment.
8. Fixed total check for 2 decimal places in case of same tendered amount.
9. Fixed issue of loader.
10. Fixed other security issues.


=== 3.2.0 ===

1. Added inline price edit feature.
2. Added customer based discount feature.
3. Added unit price feature for products.
4. Added pagination in all tables in backend.
5. Fixed POS Discount tax issue.
6. Fixed tax issue for custom created product.
7. Fixed css issues.


=== 3.1.2 ===

1. Fixed in recipt css.
2. Update create barcode by both product-id and sku of product.
3. Fixed initial amount issue on login.
4. Fixed hold issue conflict after order.
5. Fixed indexdb upadte on login.
6. Fixed order create without customer issue.
7. Compatible to booking POS and return POS.
8. Fixed authentication issue on customer edit and delete.


=== 3.1.1 ===

1. New Format of receipt.
2. Fixed Major bug related issue on load product.
3. Fixed update master stock from product edit page.
4. Fixed Discount Tax on in inclusive case.


=== 3.1.0 ===

1. Batch in order and customer also.
2. Fixed tax issue.
3. Fixed issue offline id is not saved.
4. Fixed after reset offline will not remove from data.
5. Bug fix order detail change according to currency.
6. Coupon tax and tax will show in cart.
7. Fixed search issue.
8. Fixed header issue.


=== 3.0.1 ===

1. Introduce Centralise inventory system in POS.
2. Bug fixes related to hold cart.
3. Change mass assign system.
4. Change payment page view in pos.
5. Update invoice css.


=== 3.0.0 ===

1. Update POS in react.
2. Introduce New feature of split payment.


=== 2.2.1 ===

1. Introduce reports of pos sale with two filters(payment and outlet) for pos admin.
2. Now subcategory is also diplayed at pos end.
3. Stop ajax at cashier tab.
4. Fixed loading high number of order issue.
5. Fixed show product wihout managing woocommerce stock.
6. Fixed variable product stock reduce at front end after order.
7. Fixed decimal seprator change according to woocommerece.
8. Fixed mass master stock at setting now.


=== 2.2.0 ===

1. POS agent can create custom product.
2. Add variable product barcode.
3. Fixed validation on coupon on bases of product and category.
4. Fixed special symbol support in product name.
5. Fixed Variable product tax issue.
6. Fixed Same Variation product is not show in receipt and order-summary.


=== 2.1.2 ===

1. Added the percentage coupon support.
2. Fixed security check while changing password.
3. Updated template.
4. Fixed coupon tax issues.
5. Fixed offline order detail issues.
6. Fixed oulet assign issue.


=== 2.1.1 ===

1. Fixed the coupon issue at frontend.
2. Updated the POS product search according to outlet at admin side.
3. Updated Font Awesome Icons.
4. Updated Invoice template.
5. Pagination issue fixed at admin side.
6. Added option to search customer according to phone number.
7. Number dialer accepts decimal price.
8. Multiple tax can be applied in POS.
9. Shipping is calculated on basis of outlet.
10. Fixed issue of assigning outlet.
11. Price related fixes.
12. CSS loads only on poslogin page.
13. Improved and customized POS receipt.
14. Support for woocommerce default shipping methods.
15. Add to cart through barcode scan.


=== 3.0.1 ===

1. Fixed offline order issue
2. discount price validation issue fixed
3. hold cart sync issue fixed
4. product batch system enhanced


=== 3.0.0 ===

1. Whole pos application migrated to react


=== 2.1.0 ===

1. Separate master stock management system introduced for pos outlet products
2. Bulk assign feature for outlet product stock and master stock added
3. variable product issues fixed


=== 2.0.1 ===

1. Cash drawer datetime issue fixed.
2. pos coupon updated for order as wc 3.x+ updated coupon code .
3. Epson TM-T88V thermal printer setting added
4. fontawesome icon updated.
5. apply discount section functionality changed


=== 2.0.0 ===

1. introduced shipping by customer address and tax and shipping tax calculation at frontend.
2. online order summary and order offline summary updated for tax and shipping calculation.
3. invoice updated for tax and shipping calculations
4. Admin cannot assign Outlet with status deactive to pos User fixed


=== 1.2.0 ===

1. create outlet and create pos manager validation fixed and information added
2. introduced transation and included pot file in the plugin
3. Added some more fields in create customer form frontend
4. stock management for products at backend is restricted according to available stock of product.
5. client side validation updates for pos login form


=== 1.1.0 ===

1. Offline order invoice fixed
2. Order summary total, balance issue fixed
3. reponsive issues for pos fixed.
4. various notification messages fixed.
5. pos login form design updated


=== 1.0 ===

Initial release

